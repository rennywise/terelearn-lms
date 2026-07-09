<?php
if (ob_get_level() === 0) {
    ob_start();
}
session_start();
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

function json_out(array $payload, int $code = 200): void {
    http_response_code($code);
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    json_out(['status' => 'error', 'message' => 'Unauthorised.'], 401);
}

$studentSessionId = (string)$_SESSION['user_id'];
$postId = trim($_POST['post_id'] ?? '');

if ($postId === '') {
    json_out(['status' => 'error', 'message' => 'post_id is required.'], 400);
}

require_once __DIR__ . '/../../../core/db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    json_out(['status' => 'error', 'message' => 'Database connection unavailable.'], 500);
}

$studentId = $studentSessionId;
$userStmt = $conn->prepare("SELECT username, email FROM tbluser WHERE id = ? AND is_deleted = 0 LIMIT 1");
$username = '';
$email = '';
if ($userStmt) {
    $userStmt->bind_param('s', $studentSessionId);
    $userStmt->execute();
    $userRow = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();
    $username = (string)($userRow['username'] ?? '');
    $email = (string)($userRow['email'] ?? '');
}

$studentStmt = $conn->prepare("
    SELECT id
    FROM tblstudent
    WHERE is_deleted = 0
      AND (user_id = ? OR username = ? OR email = ?)
    LIMIT 1
");
if ($studentStmt) {
    $studentStmt->bind_param('sss', $studentSessionId, $username, $email);
    $studentStmt->execute();
    $studentRow = $studentStmt->get_result()->fetch_assoc();
    $studentStmt->close();
    if ($studentRow && !empty($studentRow['id'])) {
        $studentId = (string)$studentRow['id'];
    }
}

$stmt = $conn->prepare("
    SELECT s.id, s.status
    FROM tblsubmission s
    INNER JOIN tblpost p ON p.id = s.post_id AND p.is_deleted = 0
    INNER JOIN tblclassenrollment ce
      ON ce.class_id = p.class_id
     AND ce.enrollment_status = 'enrolled'
     AND (ce.student_id = ? OR ce.student_id = ?)
    WHERE s.post_id = ?
      AND (s.student_id = ? OR s.student_id = ?)
    ORDER BY s.submitted_at DESC
    LIMIT 1
");
if (!$stmt) {
    json_out(['status' => 'error', 'message' => 'Submission lookup failed.'], 500);
}

$stmt->bind_param('sssss', $studentSessionId, $studentId, $postId, $studentSessionId, $studentId);
$stmt->execute();
$submission = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$submission) {
    json_out(['status' => 'error', 'message' => 'Submission not found.'], 404);
}

if (strtolower((string)($submission['status'] ?? '')) === 'graded') {
    json_out(['status' => 'error', 'message' => 'Graded submissions cannot be deleted.'], 409);
}

$deleteStmt = $conn->prepare("DELETE FROM tblsubmission WHERE id = ? LIMIT 1");
if (!$deleteStmt) {
    json_out(['status' => 'error', 'message' => 'Failed to prepare delete.'], 500);
}

$deleteStmt->bind_param('s', $submission['id']);
$ok = $deleteStmt->execute();
$deleteStmt->close();

if (!$ok) {
    json_out(['status' => 'error', 'message' => 'Failed to delete submission.'], 500);
}

json_out(['status' => 'success', 'message' => 'Submission deleted.']);
