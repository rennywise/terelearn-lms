<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function out($ok, $msg, $extra = []) {
    echo json_encode(array_merge(['status' => $ok ? 'success' : 'error', 'message' => $msg], $extra));
    exit;
}

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    out(false, 'Unauthorized');
}

require_once __DIR__ . '/../../../core/db_connect.php';

$submissionId = trim($_POST['submission_id'] ?? '');
$gradeRaw = trim((string)($_POST['grade'] ?? ''));
if ($submissionId === '' || $gradeRaw === '') out(false, 'submission_id and grade are required');
if (!is_numeric($gradeRaw)) out(false, 'Grade must be numeric');

$grade = (float)$gradeRaw;
if ($grade < 0) out(false, 'Grade cannot be negative');

$uid = $conn->real_escape_string($_SESSION['user_id']);
$sid = $conn->real_escape_string($submissionId);

// Resolve faculty from logged-in user.
$uRes = $conn->query("SELECT username,email FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
if (!$uRes || !$uRes->num_rows) out(false, 'User not found');
$u = $uRes->fetch_assoc();
$un = $conn->real_escape_string($u['username'] ?? '');
$em = $conn->real_escape_string($u['email'] ?? '');

$fRes = $conn->query("SELECT id FROM tblfaculty WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
if (!$fRes || !$fRes->num_rows) out(false, 'Faculty record not found');
$facultyId = $conn->real_escape_string($fRes->fetch_assoc()['id']);

// Ensure submission belongs to a post inside a class owned by this faculty.
$chk = $conn->query("
    SELECT s.id, s.post_id
    FROM tblsubmission s
    INNER JOIN tblpost p ON p.id = s.post_id AND p.is_deleted = 0
    INNER JOIN tblclass c ON c.id = p.class_id AND c.is_deleted = 0
    WHERE s.id = '$sid' AND c.faculty_id = '$facultyId'
    LIMIT 1
");
if (!$chk || !$chk->num_rows) out(false, 'Submission not found or access denied');

// Some deployments do not have tblsubmission.updated_at, so update safely.
$hasUpdatedAt = false;
$colRes = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tblsubmission'
      AND COLUMN_NAME = 'updated_at'
    LIMIT 1
");
if ($colRes && $colRes->num_rows > 0) {
    $hasUpdatedAt = true;
}

$sql = $hasUpdatedAt
    ? "UPDATE tblsubmission SET grade = ?, status = 'graded', updated_at = NOW() WHERE id = ? LIMIT 1"
    : "UPDATE tblsubmission SET grade = ?, status = 'graded' WHERE id = ? LIMIT 1";

$st = $conn->prepare($sql);
if (!$st) out(false, 'Prepare failed: '.$conn->error);
$st->bind_param('ds', $grade, $sid);
if (!$st->execute()) out(false, 'Failed to save grade: '.$st->error);
$st->close();

out(true, 'Grade saved');
