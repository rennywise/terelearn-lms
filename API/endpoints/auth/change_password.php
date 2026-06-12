 <?php
/**
 * API/change_password.php
 * Called by signin.php → saveNewPassword() after OTP verification.
 *
 * Expects JSON body:
 *   { "user_id": "<uuid>", "new_password": "<plain-text>" }
 *
 * What it does:
 *   1. Validates input
 *   2. Enforces the same password policy as the front-end
 *   3. Hashes with password_hash (bcrypt)
 *   4. Updates tbluser  (password + first_login = 0)
 *   5. Updates the linked tblfaculty / tblstudent row so both
 *      tables stay in sync (they store their own password column)
 *   6. Logs to tblaudittrail if the table exists
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

/* ── db connection ── */
require_once __DIR__ . '/../../core/db_connect.php';

/* ── read JSON body ── */
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

$user_id      = trim($body['user_id']      ?? '');
$new_password = trim($body['new_password'] ?? '');

/* ── basic validation ── */
if (!$user_id || !$new_password) {
    echo json_encode(['success' => false, 'message' => 'user_id and new_password are required.']);
    exit;
}

/* ── server-side password policy (mirrors the JS policy) ── */
$errors = [];
if (strlen($new_password) < 12)                          $errors[] = 'at least 12 characters';
if (!preg_match('/[A-Z]/', $new_password))               $errors[] = 'one uppercase letter';
if (!preg_match('/[a-z]/', $new_password))               $errors[] = 'one lowercase letter';
if (!preg_match('/[0-9]/', $new_password))               $errors[] = 'one number';
if (!preg_match('/[!@#$%^&*()\-_=+\[\]{};:\'",.<>?\/\\\\|`~]/', $new_password))
                                                          $errors[] = 'one special character';
if ($errors) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must contain: ' . implode(', ', $errors) . '.'
    ]);
    exit;
}

/* ── fetch user record ── */
$stmt = $conn->prepare("
    SELECT id, email, username, user_level_id
    FROM tbluser
    WHERE id = ? AND is_deleted = 0
    LIMIT 1
");
$stmt->bind_param('s', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

/* ── hash the new password ── */
$hashed = password_hash($new_password, PASSWORD_BCRYPT);

/* ── update tbluser ── */
$upd = $conn->prepare("
    UPDATE tbluser
    SET password    = ?,
        first_login = 0,
        updated_at  = NOW()
    WHERE id = ?
");
$upd->bind_param('ss', $hashed, $user_id);
$upd->execute();
$affected = $upd->affected_rows;
$upd->close();

if ($affected === 0) {
    // Row exists but nothing changed (same hash) — still OK
    // Check it actually exists rather than silently failing
    $chk = $conn->prepare("SELECT id FROM tbluser WHERE id = ? LIMIT 1");
    $chk->bind_param('s', $user_id);
    $chk->execute();
    $exists = $chk->get_result()->num_rows > 0;
    $chk->close();
    if (!$exists) {
        echo json_encode(['success' => false, 'message' => 'User record not found.']);
        exit;
    }
}

/* ── sync password to tblfaculty (level 2) ── */
if ((int)$user['user_level_id'] === 2) {
    $syncFac = $conn->prepare("
        UPDATE tblfaculty
        SET password   = ?,
            updated_at = NOW()
        WHERE (username = ? OR email = ?) AND is_deleted = 0
    ");
    $syncFac->bind_param('sss', $hashed, $user['username'], $user['email']);
    $syncFac->execute();
    $syncFac->close();
}

/* ── sync password to tblstudent (level 3) ── */
if ((int)$user['user_level_id'] === 3) {
    // Check if tblstudent has a password column before updating
    $colCheck = $conn->query("
        SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'tblstudent'
          AND COLUMN_NAME  = 'password'
        LIMIT 1
    ");
    if ($colCheck && $colCheck->num_rows > 0) {
        $syncStu = $conn->prepare("
            UPDATE tblstudent
            SET password   = ?,
                updated_at = NOW()
            WHERE (username = ? OR email = ?) AND is_deleted = 0
        ");
        $syncStu->bind_param('sss', $hashed, $user['username'], $user['email']);
        $syncStu->execute();
        $syncStu->close();
    }
}

/* ── audit trail (soft-fail if table doesn't exist) ── */
$auditCheck = $conn->query("
    SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblaudittrail'
    LIMIT 1
");
if ($auditCheck && $auditCheck->num_rows > 0) {
    $ip     = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $action = 'Password changed (first login)';
    $audit  = $conn->prepare("
        INSERT INTO tblaudittrail (user_id, action, ip_address, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    if ($audit) {
        $audit->bind_param('sss', $user_id, $action, $ip);
        $audit->execute();
        $audit->close();
    }
}

$conn->close();

echo json_encode([
    'success' => true,
    'message' => 'Password updated successfully.'
]);
