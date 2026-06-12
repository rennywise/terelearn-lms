<?php
ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function send($arr) { ob_end_clean(); echo json_encode($arr); exit; }

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    send(['status' => 'error', 'message' => 'Unauthorized']);
}

require_once __DIR__ . '/../../../../core/db_connect.php';

$post_id        = trim($_POST['post_id']          ?? '');
$quiz_mode      = trim($_POST['quiz_mode']         ?? '');
$max_attempts   = (int)($_POST['max_attempts']     ?? 0);
$time_limit_raw = trim($_POST['time_limit_seconds'] ?? '');
$due_date_raw   = trim($_POST['due_date']          ?? '');
$open_at_raw    = trim($_POST['open_at']           ?? '');

if ($post_id === '') send(['status' => 'error', 'message' => 'post_id required']);

$quiz_mode      = in_array($quiz_mode, ['live', 'due_date']) ? $quiz_mode : 'live';
$max_attempts   = max(0, min(2, $max_attempts));
$time_limit_sec = ($time_limit_raw !== '') ? (int)$time_limit_raw : null;

$due_date = null;
 $open_at = null;
if ($quiz_mode === 'due_date' && $due_date_raw !== '') {
    $ts = strtotime($due_date_raw);
    if ($ts !== false) $due_date = date('Y-m-d H:i:s', $ts);
}
if ($quiz_mode === 'due_date' && $open_at_raw !== '') {
    $ts = strtotime($open_at_raw);
    if ($ts !== false) $open_at = date('Y-m-d H:i:s', $ts);
}
if ($quiz_mode === 'due_date') {
    if (!$open_at || !$due_date) send(['status' => 'error', 'message' => 'Start and end datetime are required for due-date mode']);
    if (strtotime($due_date) <= strtotime($open_at)) send(['status' => 'error', 'message' => 'End datetime must be later than start datetime']);
}

$pid = $conn->real_escape_string($post_id);

$pRes = $conn->query("SELECT id FROM tblpost WHERE id = '$pid' LIMIT 1");
if (!$pRes || $pRes->num_rows === 0) {
    send(['status' => 'error', 'message' => 'Post not found. post_id received: ' . htmlspecialchars($post_id)]);
}

$quiz_id = null;
$has_col = false;
$colCheck = $conn->query("SHOW COLUMNS FROM tblquiz LIKE 'post_id'");
if ($colCheck && $colCheck->num_rows > 0) {
    $has_col = true;
    $qRes = $conn->query("SELECT id FROM tblquiz WHERE post_id = '$pid' LIMIT 1");
    if ($qRes && $qRes->num_rows > 0) $quiz_id = $qRes->fetch_assoc()['id'];
}

$qmode_esc = $conn->real_escape_string($quiz_mode);
$due_esc   = $due_date ? ("'" . $conn->real_escape_string($due_date) . "'") : 'NULL';
$open_esc  = $open_at  ? ("'" . $conn->real_escape_string($open_at) . "'")  : 'NULL';
$tls_esc   = ($time_limit_sec !== null) ? (int)$time_limit_sec : 'NULL';
$max_e     = (int)$max_attempts;

if ($quiz_id) {
    $qid_e = $conn->real_escape_string($quiz_id);
    if ($quiz_mode !== '') {
        $upd = $conn->query("UPDATE tblquiz SET is_published=1, quiz_mode='$qmode_esc', max_attempts=$max_e, time_limit_seconds=$tls_esc, due_date=$due_esc, is_force_closed=0, live_started_at=NULL, live_ended_at=NULL, results_released_at=NULL, published_at=NOW() WHERE id='$qid_e'");
    } else {
        $upd = $conn->query("UPDATE tblquiz SET is_published=1, published_at=NOW() WHERE id='$qid_e'");
    }
    if (!$upd) send(['status' => 'error', 'message' => 'DB error updating quiz: ' . $conn->error]);

} elseif ($has_col) {
    // Check if id needs to be supplied (non-auto-increment)
    $idType = 'auto';
    $idCol  = $conn->query("SHOW COLUMNS FROM tblquiz LIKE 'id'");
    if ($idCol && $idCol->num_rows > 0) {
        $idRow = $idCol->fetch_assoc();
        if (stripos($idRow['Extra'] ?? '', 'auto_increment') === false) $idType = 'manual';
    }

    if ($idType === 'manual') {
        $new_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
            mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
            mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));
        $nid = $conn->real_escape_string($new_id);
        $ins = $conn->query("INSERT INTO tblquiz (id,post_id,is_published,quiz_mode,max_attempts,time_limit_seconds,due_date,is_force_closed,live_started_at,live_ended_at,results_released_at,published_at) VALUES ('$nid','$pid',1,'$qmode_esc',$max_e,$tls_esc,$due_esc,0,NULL,NULL,NULL,NOW())");
    } else {
        $ins = $conn->query("INSERT INTO tblquiz (post_id,is_published,quiz_mode,max_attempts,time_limit_seconds,due_date,is_force_closed,live_started_at,live_ended_at,results_released_at,published_at) VALUES ('$pid',1,'$qmode_esc',$max_e,$tls_esc,$due_esc,0,NULL,NULL,NULL,NOW())");
    }
    if (!$ins) send(['status' => 'error', 'message' => 'DB error inserting quiz: ' . $conn->error]);

} else {
    $conn->query("UPDATE tblquiz SET is_published=1, quiz_mode='$qmode_esc', max_attempts=$max_e, time_limit_seconds=$tls_esc, due_date=$due_esc, is_force_closed=0, live_started_at=NULL, live_ended_at=NULL, results_released_at=NULL, published_at=NOW() WHERE id='$pid'");
}

$conn->query("UPDATE tblpost SET is_published = 1, is_force_closed = 0, is_force_open = 0, live_started_at = NULL, live_ended_at = NULL, results_released_at = NULL WHERE id = '$pid'");
if ($quiz_mode === 'due_date') {
    $conn->query("UPDATE tblpost SET open_at = $open_esc, close_at = $due_esc WHERE id = '$pid'");
} else {
    $conn->query("UPDATE tblpost SET open_at = NULL, close_at = NULL WHERE id = '$pid'");
}
$conn->close();

send(['status' => 'success', 'message' => 'Quiz published successfully.', 'data' => ['post_id' => $post_id, 'quiz_mode' => $quiz_mode, 'published' => true]]);
