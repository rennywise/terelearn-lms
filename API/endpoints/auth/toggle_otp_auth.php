<?php
/**
 * API/toggle_otp_auth.php
 * Toggles email OTP ON/OFF for a tbluser account.
 */

/* Kill ALL output before JSON */
if (ob_get_level() === 0) ob_start();
error_reporting(0);
ini_set('display_errors', '0');

header('Content-Type: application/json');

/* db_connect is kept in app/api as a compatibility wrapper. */
$dbPaths = [
    __DIR__ . '/../../core/db_connect.php',
];

$connected = false;
foreach ($dbPaths as $path) {
    if (file_exists($path)) {
        include_once $path;
        if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
            $connected = true;
            break;
        }
    }
}

/* Flush any stray output from db_connect before sending JSON */
ob_end_clean();

if (!$connected) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed. Check db_connect.php path.']);
    exit;
}

/* ── Auto-migrate otp_enabled column ── */
$chk = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbluser' AND COLUMN_NAME='otp_enabled' LIMIT 1");
if ($chk && $chk->num_rows === 0) {
    $conn->query("ALTER TABLE tbluser ADD COLUMN otp_enabled TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=OTP on,0=skip'");
}

/* ════════ GET — read status ════════ */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $uid = trim($_GET['user_id'] ?? '');
    if (!$uid) { echo json_encode(['status'=>'error','message'=>'user_id required']); exit; }

    $s = $conn->prepare("SELECT id, first_login, IFNULL(otp_enabled,1) AS otp_enabled FROM tbluser WHERE id=? AND is_deleted=0 LIMIT 1");
    $s->bind_param('s', $uid); $s->execute();
    $row = $s->get_result()->fetch_assoc(); $s->close(); $conn->close();

    if (!$row) { echo json_encode(['status'=>'error','message'=>'User not found']); exit; }
    echo json_encode(['status'=>'success','otp_enabled'=>(int)$row['otp_enabled'],'first_login'=>(int)$row['first_login'],'locked'=>(int)$row['first_login']===1]);
    exit;
}

/* ════════ POST — set on/off ════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $uid     = trim($body['user_id'] ?? '');
    $enabled = isset($body['enabled']) ? (int)$body['enabled'] : null;

    if (!$uid || $enabled === null) {
        echo json_encode(['status'=>'error','message'=>'user_id and enabled (0|1) required']); exit;
    }

    $s = $conn->prepare("SELECT id, first_login FROM tbluser WHERE id=? AND is_deleted=0 LIMIT 1");
    $s->bind_param('s', $uid); $s->execute();
    $user = $s->get_result()->fetch_assoc(); $s->close();

    if (!$user) { $conn->close(); echo json_encode(['status'=>'error','message'=>'User not found']); exit; }

    if ((int)$user['first_login'] === 1) {
        $conn->close();
        echo json_encode(['status'=>'error','message'=>'Cannot toggle — OTP is mandatory for accounts that have not completed first login.','locked'=>true]);
        exit;
    }

    $enabled = $enabled ? 1 : 0;
    $upd = $conn->prepare("UPDATE tbluser SET otp_enabled=?, updated_at=NOW() WHERE id=? AND is_deleted=0");
    $upd->bind_param('is', $enabled, $uid); $upd->execute(); $upd->close();

    /* Audit trail (soft-fail) */
    $ac = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tblaudittrail' LIMIT 1");
    if ($ac && $ac->num_rows > 0) {
        $action = $enabled ? 'OTP ENABLED by admin' : 'OTP DISABLED by admin';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $a = $conn->prepare("INSERT INTO tblaudittrail(user_id,action,ip_address,created_at) VALUES(?,?,?,NOW())");
        if ($a) { $a->bind_param('sss',$uid,$action,$ip); $a->execute(); $a->close(); }
    }
    $conn->close();

    echo json_encode(['status'=>'success','message'=>$enabled?'OTP enabled.':'OTP disabled.','otp_enabled'=>$enabled]);
    exit;
}

echo json_encode(['status'=>'error','message'=>'Invalid request method']);
