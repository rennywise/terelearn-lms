<?php
ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function send($arr) {
    ob_end_clean();
    echo json_encode($arr);
    exit;
}

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    send(['success' => false, 'message' => 'Unauthorized']);
}

require_once __DIR__ . '/../../../../core/db_connect.php';

$post_id = trim($_POST['post_id'] ?? '');
if ($post_id === '') {
    send(['success' => false, 'message' => 'post_id required']);
}

$pid     = $conn->real_escape_string($post_id);
$user_id = $conn->real_escape_string($_SESSION['user_id']);

$uRes = $conn->query("SELECT email, username FROM tbluser WHERE id = '$user_id' AND is_deleted = 0 LIMIT 1");
if (!$uRes || $uRes->num_rows === 0) send(['success' => false, 'message' => 'User not found']);
$uRow = $uRes->fetch_assoc();
$un   = $conn->real_escape_string($uRow['username']);
$em   = $conn->real_escape_string($uRow['email']);

$sRes = $conn->query("SELECT id FROM tblstudent WHERE (username = '$un' OR email = '$em') AND is_deleted = 0 LIMIT 1");
if (!$sRes || $sRes->num_rows === 0) send(['success' => false, 'message' => 'Student record not found']);
$student_id = $conn->real_escape_string($sRes->fetch_assoc()['id']);

$quiz_id  = null;
$quiz_row = null;
$colCheck = $conn->query("SHOW COLUMNS FROM tblquiz LIKE 'post_id'");
if ($colCheck && $colCheck->num_rows > 0) {
    $qRes = $conn->query("SELECT q.id AS quiz_id, q.is_published, q.is_force_closed, q.live_ended_at, q.quiz_mode, p.post_type FROM tblpost p INNER JOIN tblquiz q ON q.post_id = p.id WHERE p.id = '$pid' LIMIT 1");
} else {
    $qRes = $conn->query("SELECT q.id AS quiz_id, q.is_published, q.is_force_closed, q.live_ended_at, q.quiz_mode, p.post_type FROM tblpost p INNER JOIN tblquiz q ON q.id = p.id WHERE p.id = '$pid' LIMIT 1");
}
if ($qRes && $qRes->num_rows > 0) { $quiz_row = $qRes->fetch_assoc(); $quiz_id = $quiz_row['quiz_id']; }
if (!$quiz_row) send(['success' => false, 'message' => 'Assessment not found for this post']);
$assessmentLabel = strtolower((string)($quiz_row['post_type'] ?? '')) === 'exam' ? 'Exam' : 'Quiz';
$assessmentLower = strtolower($assessmentLabel);
if ((int)($quiz_row['is_published'] ?? 0) !== 1) send(['success' => false, 'message' => "{$assessmentLabel} not published yet"]);
if ((int)($quiz_row['is_force_closed'] ?? 0) === 1 || !empty($quiz_row['live_ended_at'])) send(['success' => false, 'message' => "{$assessmentLabel} has ended"]);

$qid = $conn->real_escape_string($quiz_id);
// Use post_id as the enrollment key (matches the actual tblquizenrollment schema)
// Only block when there is an ACTIVE enrolled row.
$active = $conn->query("
    SELECT id FROM tblquizenrollment
    WHERE post_id = '$pid' AND student_id = '$student_id' AND LOWER(COALESCE(status,'')) = 'enrolled'
    LIMIT 1
");
if ($active && $active->num_rows > 0) send(['success' => false, 'message' => "Already joined this {$assessmentLower}"]);

// If any old row exists (e.g., withdrawn/reset), reactivate it instead of failing with duplicate constraints.
$existing = $conn->query("SELECT id FROM tblquizenrollment WHERE post_id = '$pid' AND student_id = '$student_id' LIMIT 1");
if ($existing && $existing->num_rows > 0) {
    $eid = $conn->real_escape_string($existing->fetch_assoc()['id']);
    $upd = $conn->query("
        UPDATE tblquizenrollment
        SET status = 'enrolled', enrolled_at = NOW(), withdrawn_at = NULL
        WHERE id = '$eid'
        LIMIT 1
    ");
    if (!$upd) send(['success' => false, 'message' => 'Failed to enroll: ' . $conn->error]);
} else {
    $ins = $conn->query("INSERT INTO tblquizenrollment (id, post_id, student_id, status, enrolled_at) VALUES (UUID(), '$pid', '$student_id', 'enrolled', NOW())");
    if (!$ins) send(['success' => false, 'message' => 'Failed to enroll: ' . $conn->error]);
}

$conn->close();
send(['success' => true, 'message' => "Joined {$assessmentLower} successfully.", 'data' => ['quiz_id' => $quiz_id, 'quiz_mode' => $quiz_row['quiz_mode'] ?? 'live', 'post_type' => $quiz_row['post_type'] ?? 'quiz']]);
