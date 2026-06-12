<?php
ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function send($arr) { ob_end_clean(); echo json_encode($arr); exit; }

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    send(['success' => false, 'message' => 'Unauthorized']);
}

require_once __DIR__ . '/../../../../core/db_connect.php';

$post_id = trim($_POST['post_id'] ?? '');
if ($post_id === '') send(['success' => false, 'message' => 'post_id required']);

$pid     = $conn->real_escape_string($post_id);
$user_id = $conn->real_escape_string($_SESSION['user_id']);

$uRes = $conn->query("SELECT email, username FROM tbluser WHERE id = '$user_id' AND is_deleted = 0 LIMIT 1");
if (!$uRes || $uRes->num_rows === 0) send(['success' => false, 'message' => 'User not found']);
$uRow = $uRes->fetch_assoc();
$un   = $conn->real_escape_string($uRow['username']);
$em   = $conn->real_escape_string($uRow['email']);

$sRes = $conn->query("SELECT id FROM tblstudent WHERE (username = '$un' OR email = '$em') AND is_deleted = 0 LIMIT 1");
if (!$sRes || $sRes->num_rows === 0) send(['success' => false, 'message' => 'Student not found']);
$student_id = $conn->real_escape_string($sRes->fetch_assoc()['id']);

$quiz = null; $quiz_id = null;
$colCheck = $conn->query("SHOW COLUMNS FROM tblquiz LIKE 'post_id'");
if ($colCheck && $colCheck->num_rows > 0) {
    $qRes = $conn->query("SELECT q.id AS quiz_id, q.is_published, q.quiz_mode, q.is_force_closed, q.live_started_at, q.live_ended_at, q.due_date, q.max_attempts, q.time_limit_seconds, p.post_type, p.is_force_open, p.open_at, p.close_at, COALESCE(p.results_released_at, q.results_released_at) AS results_released_at FROM tblpost p INNER JOIN tblquiz q ON q.post_id = p.id WHERE p.id = '$pid' LIMIT 1");
} else {
    $qRes = $conn->query("SELECT q.id AS quiz_id, q.is_published, q.quiz_mode, q.is_force_closed, q.live_started_at, q.live_ended_at, q.due_date, q.max_attempts, q.time_limit_seconds, p.post_type, p.is_force_open, p.open_at, p.close_at, COALESCE(p.results_released_at, q.results_released_at) AS results_released_at FROM tblpost p INNER JOIN tblquiz q ON q.id = p.id WHERE p.id = '$pid'  LIMIT 1");
}
if ($qRes && $qRes->num_rows > 0) { $quiz = $qRes->fetch_assoc(); $quiz_id = $quiz['quiz_id']; }

if (!$quiz || (int)($quiz['is_published'] ?? 0) !== 1) {
    send(['success' => true, 'data' => ['quiz' => null, 'is_enrolled' => false, 'attempt_status' => null]]);
}

$qid  = $conn->real_escape_string($quiz_id);
// Explicit join flow: treat as enrolled when ANY active enrolled row exists.
$is_enrolled = false;
$eRes = $conn->query("SELECT COUNT(*) c FROM tblquizenrollment WHERE post_id = '$pid' AND student_id = '$student_id' AND LOWER(COALESCE(status,''))='enrolled'");
if ($eRes && $eRes->num_rows > 0) {
    $is_enrolled = ((int)$eRes->fetch_assoc()['c']) > 0;
}

$attempt_status = null;
$subColP = $conn->query("SHOW COLUMNS FROM tblquizattempt LIKE 'post_id'");
if ($subColP && $subColP->num_rows > 0) {
    $aRes = $conn->query("SELECT status FROM tblquizattempt WHERE post_id = '$pid' AND student_id = '$student_id' ORDER BY attempt_number DESC LIMIT 1");
    if ($aRes && $aRes->num_rows > 0) $attempt_status = $aRes->fetch_assoc()['status'];
} else {
    $subColQ = $conn->query("SHOW COLUMNS FROM tblquizattempt LIKE 'quiz_id'");
    if ($subColQ && $subColQ->num_rows > 0) {
        $aRes = $conn->query("SELECT status FROM tblquizattempt WHERE quiz_id = '$qid' AND student_id = '$student_id' ORDER BY id DESC LIMIT 1");
        if ($aRes && $aRes->num_rows > 0) $attempt_status = $aRes->fetch_assoc()['status'];
    }
}

$has_makeup_access = false;
$makeup_valid_until = null;
$mkTbl = $conn->query("SHOW TABLES LIKE 'tblquizmakeupaccess'");
if ($mkTbl && $mkTbl->num_rows > 0) {
    $mk = $conn->query("SELECT valid_until FROM tblquizmakeupaccess WHERE post_id='$pid' AND student_id='$student_id' AND is_active=1 AND consumed_at IS NULL ORDER BY granted_at DESC LIMIT 1");
    if ($mk && $mk->num_rows > 0) {
        $row = $mk->fetch_assoc();
        $makeup_valid_until = $row['valid_until'] ?? null;
        if ($makeup_valid_until && strtotime($makeup_valid_until) > time()) $has_makeup_access = true;
    }
}

$conn->close();
send([
    'success' => true,
    'data' => [
        'quiz' => [
            'quiz_id' => $quiz['quiz_id'], 'quiz_mode' => $quiz['quiz_mode'] ?? 'due_date',
            'post_type' => $quiz['post_type'] ?? 'quiz',
            'is_published' => (int)($quiz['is_published'] ?? 0),
            'is_force_closed' => (int)($quiz['is_force_closed'] ?? 0),
            'is_force_open' => (int)($quiz['is_force_open'] ?? 0),
            'live_started_at' => $quiz['live_started_at'] ?? null,
            'live_ended_at' => $quiz['live_ended_at'] ?? null,
            'due_date' => $quiz['due_date'] ?? null,
            'open_at' => $quiz['open_at'] ?? null,
            'close_at' => $quiz['close_at'] ?? null,
            'max_attempts' => (int)($quiz['max_attempts'] ?? 0),
            'time_limit_seconds' => $quiz['time_limit_seconds'] ?? null,
            'results_released_at' => $quiz['results_released_at'] ?? null,
        ],
        'post_type' => $quiz['post_type'] ?? 'quiz',
        'is_enrolled' => $is_enrolled,
        'attempt_status' => $attempt_status,
        'has_makeup_access' => $has_makeup_access,
        'makeup_valid_until' => $makeup_valid_until,
    ],
]);
