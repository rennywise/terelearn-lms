<?php
/**
 * API/authenticate.php — OTP-aware login
 * Sets PHP session + returns JSON for signin.php routing.
 */
ob_start();
error_reporting(0);
ini_set('display_errors', '0');

session_start();
header('Content-Type: application/json');

/* db_connect is kept in app/api as a compatibility wrapper. */
$dbPaths = [
    __DIR__ . '/../../core/db_connect.php',
];
foreach ($dbPaths as $p) {
    if (file_exists($p)) { include_once $p; break; }
}
while (ob_get_level() > 0) ob_end_clean();

if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

$identifier = trim($body['username'] ?? '');
$password   = trim($body['password'] ?? '');
$identifierEmail = strtolower($identifier);
$isEmailInput = (bool) filter_var($identifier, FILTER_VALIDATE_EMAIL);

if (!$identifier || !$password) {
    echo json_encode(['success' => false, 'message' => 'Username or registered email and password are required.']);
    exit;
}

/* ── Ensure otp_enabled column exists ── */
$colCheck = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbluser' AND COLUMN_NAME='otp_enabled' LIMIT 1");
if ($colCheck && $colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE tbluser ADD COLUMN otp_enabled TINYINT(1) NOT NULL DEFAULT 1");
}

/* ── Look up user ── */
$stmt = $isEmailInput
    ? $conn->prepare("
        SELECT id, email, username, password, user_level_id, is_dean,
               is_active, is_deleted, first_login, failed_attempts,
               IFNULL(otp_enabled, 1) AS otp_enabled
        FROM tbluser
        WHERE LOWER(email) = LOWER(?) AND is_deleted = 0
        LIMIT 1
    ")
    : $conn->prepare("
        SELECT id, email, username, password, user_level_id, is_dean,
               is_active, is_deleted, first_login, failed_attempts,
               IFNULL(otp_enabled, 1) AS otp_enabled
        FROM tbluser
        WHERE (username = ? OR LOWER(email) = LOWER(?)) AND is_deleted = 0
        LIMIT 1
    ");
if ($isEmailInput) {
    $stmt->bind_param('s', $identifierEmail);
} else {
    $stmt->bind_param('ss', $identifier, $identifierEmail);
}
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* Fallback: if admin exists but no tbluser login row matched, sync/create tbluser so OTP flow is still used */
if (!$user) {
    $aStmt = $conn->prepare("
        SELECT id, email, username, password, is_active
        FROM tbladmin
        WHERE (username = ? OR email = ?) AND is_deleted = 0
        LIMIT 1
    ");
    $aStmt->bind_param('ss', $identifier, $identifier);
    $aStmt->execute();
    $admin = $aStmt->get_result()->fetch_assoc();
    $aStmt->close();

    if ($admin) {
        $okAdmin = (str_starts_with($admin['password'], '$2y$') || str_starts_with($admin['password'], '$2b$'))
            ? password_verify($password, $admin['password'])
            : ($password === $admin['password']);
        if (!$okAdmin) {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
            exit;
        }
        if ((int)$admin['is_active'] !== 1) {
            echo json_encode(['success' => false, 'message' => 'Account deactivated. Contact your administrator.']);
            exit;
        }

        // Reuse an existing non-student login row if possible
        $uFind = $conn->prepare("
            SELECT id
            FROM tbluser
            WHERE (email = ? OR username = ?) AND is_deleted = 0 AND user_level_id <> 3
            LIMIT 1
        ");
        $uFind->bind_param('ss', $admin['email'], $admin['username']);
        $uFind->execute();
        $existing = $uFind->get_result()->fetch_assoc();
        $uFind->close();

        if ($existing) {
            $uSync = $conn->prepare("
                UPDATE tbluser
                SET email = ?, username = ?, password = ?, user_level_id = 1, is_dean = 0, is_active = 1, otp_enabled = 1, updated_at = NOW()
                WHERE id = ?
            ");
            $uSync->bind_param('ssss', $admin['email'], $admin['username'], $admin['password'], $existing['id']);
            $uSync->execute();
            $uSync->close();
        } else {
            $newUserId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
                mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
                mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
            );
            $uIns = $conn->prepare("
                INSERT INTO tbluser (id, email, username, password, user_level_id, is_dean, is_active, is_deleted, first_login, otp_enabled)
                VALUES (?, ?, ?, ?, 1, 0, 1, 0, 0, 1)
            ");
            $uIns->bind_param('ssss', $newUserId, $admin['email'], $admin['username'], $admin['password']);
            $uIns->execute();
            $uIns->close();
        }

        // Reload user so it goes through the same OTP-aware flow as everyone else
        $stmt = $conn->prepare("
            SELECT id, email, username, password, user_level_id, is_dean,
                   is_active, is_deleted, first_login, failed_attempts,
                   IFNULL(otp_enabled, 1) AS otp_enabled
            FROM tbluser
            WHERE (username = ? OR email = ?) AND is_deleted = 0
            LIMIT 1
        ");
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    exit;
}

if ((int)$user['is_active'] !== 1) {
    echo json_encode(['success' => false, 'message' => 'Account deactivated. Contact your administrator.']);
    exit;
}

if ((int)$user['failed_attempts'] >= 5) {
    echo json_encode(['success' => false, 'message' => 'Account locked. Contact your administrator.', 'owner_email' => $user['email']]);
    exit;
}

/* ── Verify password (bcrypt or legacy plain-text) ── */
$ok = (str_starts_with($user['password'], '$2y$') || str_starts_with($user['password'], '$2b$'))
    ? password_verify($password, $user['password'])
    : ($password === $user['password']);

if (!$ok) {
    $upd = $conn->prepare("UPDATE tbluser SET failed_attempts = failed_attempts + 1 WHERE id = ?");
    $upd->bind_param('s', $user['id']); $upd->execute(); $upd->close();
    $left = max(0, 5 - ((int)$user['failed_attempts'] + 1));
    echo json_encode(['success' => false, 'message' => "Invalid credentials. {$left} attempt(s) remaining.", 'owner_email' => $user['email']]);
    exit;
}

/* ── Reset failed attempts ── */
$rst = $conn->prepare("UPDATE tbluser SET failed_attempts = 0, logged_start = NOW() WHERE id = ?");
$rst->bind_param('s', $user['id']); $rst->execute(); $rst->close();

/* ── Set session ── */
session_regenerate_id(true);
$_SESSION['user_id']       = $user['id'];
$_SESSION['username']      = $user['username'];
$_SESSION['email']         = $user['email'];
$_SESSION['user_level_id'] = (int)$user['user_level_id'];
$_SESSION['is_dean']       = (int)$user['is_dean'];
$_SESSION['first_login']   = (int)$user['first_login'];
$_SESSION['otp_enabled']   = (int)$user['otp_enabled'];

/* ── Store faculty_id in session (level 2) ── */
if ((int)$user['user_level_id'] === 2) {
   $fStmt = $conn->prepare("SELECT id FROM tblfaculty WHERE (username=? OR email=?) AND is_deleted=0 LIMIT 1");
    $fStmt->bind_param('ss', $user['username'], $user['email']);
    $fStmt->execute();
    $fRow = $fStmt->get_result()->fetch_assoc();
    $fStmt->close();
    if ($fRow) $_SESSION['faculty_id'] = $fRow['id'];
}

/* ── Store student_id in session (level 3) ── */
if ((int)$user['user_level_id'] === 3) {
    $sStmt = $conn->prepare("SELECT id FROM tblstudent WHERE (username=? OR email=?) AND is_deleted=0 LIMIT 1");
    $sStmt->bind_param('ss', $user['username'], $user['email']);
    $sStmt->execute();
    $sRow = $sStmt->get_result()->fetch_assoc();
    $sStmt->close();
    if ($sRow) $_SESSION['student_id'] = $sRow['id'];
}

/* ── Audit trail ── */
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ac = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tblaudittrail' LIMIT 1");
if ($ac && $ac->num_rows > 0) {
    $aud = $conn->prepare("INSERT INTO tblaudittrail(user_id,action,ip_address,created_at) VALUES(?,'login',?,NOW())");
    if ($aud) { $aud->bind_param('ss',$user['id'],$ip); $aud->execute(); $aud->close(); }
}

$conn->close();

/* ── otp_required logic ── */
/* first_login=1 → ALWAYS require OTP regardless of otp_enabled setting */
$otpRequired = ((int)$user['first_login'] === 1) || ((int)$user['otp_enabled'] === 1);

echo json_encode([
    'success'      => true,
    'first_login'  => (int)$user['first_login'],
    'otp_required' => $otpRequired,
    'user' => [
        'id'            => $user['id'],
        'username'      => $user['username'],
        'email'         => $user['email'],
        'user_level_id' => (int)$user['user_level_id'],
        'is_dean'       => (int)$user['is_dean'],
        'first_login'   => (int)$user['first_login'],
        'otp_enabled'   => (int)$user['otp_enabled'],
    ]
]);
