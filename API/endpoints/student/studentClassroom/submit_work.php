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
$comment = trim($_POST['comment'] ?? '');

if ($postId === '') {
    json_out(['status' => 'error', 'message' => 'post_id is required.'], 400);
}

require_once __DIR__ . '/../../../core/db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    json_out(['status' => 'error', 'message' => 'Database connection unavailable.'], 500);
}

// Resolve proper student identifier used in class enrollment/submission tables.
$studentId = $studentSessionId;
$studentStmt = $conn->prepare("SELECT id FROM tblstudent WHERE user_id = ? AND is_deleted = 0 LIMIT 1");
if ($studentStmt) {
    $studentStmt->bind_param('s', $studentSessionId);
    $studentStmt->execute();
    $studentRow = $studentStmt->get_result()->fetch_assoc();
    $studentStmt->close();
    if ($studentRow && !empty($studentRow['id'])) {
        $studentId = (string)$studentRow['id'];
    }
}

// Verify post exists and student is enrolled.
$enrollStmt = $conn->prepare(
    "SELECT p.id
     FROM tblpost p
     INNER JOIN tblclassenrollment ce
       ON ce.class_id = p.class_id
      AND (ce.student_id = ? OR ce.student_id = ?)
      AND ce.enrollment_status = 'enrolled'
     WHERE p.id = ?
       AND p.is_deleted = 0
     LIMIT 1"
);
if (!$enrollStmt) {
    json_out(['status' => 'error', 'message' => 'Enrollment check failed.'], 500);
}
$enrollStmt->bind_param('sss', $studentSessionId, $studentId, $postId);
$enrollStmt->execute();
$post = $enrollStmt->get_result()->fetch_assoc();
$enrollStmt->close();

if (!$post) {
    json_out(['status' => 'error', 'message' => 'Post not found or not enrolled.'], 403);
}

// Check existing submission.
$existingStmt = $conn->prepare(
    "SELECT id, status
     FROM tblsubmission
     WHERE post_id = ?
       AND (student_id = ? OR student_id = ?)
     LIMIT 1"
);
if (!$existingStmt) {
    json_out(['status' => 'error', 'message' => 'Submission lookup failed.'], 500);
}
$existingStmt->bind_param('sss', $postId, $studentSessionId, $studentId);
$existingStmt->execute();
$existing = $existingStmt->get_result()->fetch_assoc();
$existingStmt->close();

if ($existing && in_array($existing['status'], ['graded'], true)) {
    json_out(['status' => 'error', 'message' => 'Already graded - cannot change.'], 409);
}

// Handle optional file upload.
$fileName = null;
$filePath = null;
$mimeType = null;
$fileSize = null;

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['file']['size'] > 20 * 1024 * 1024) {
        json_out(['status' => 'error', 'message' => 'File too large. Max 20 MB.'], 413);
    }

    $origName = basename($_FILES['file']['name']);
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
    $uploadDir = dirname(__DIR__, 5) . '/uploads/submissions/' . $postId . '/';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        json_out(['status' => 'error', 'message' => 'Failed to prepare upload folder.'], 500);
    }

    $uniqueName = uniqid('', true) . '_' . $safeName;
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $uniqueName)) {
        json_out(['status' => 'error', 'message' => 'Failed to save file.'], 500);
    }

    $fileName = $origName;
    $filePath = 'uploads/submissions/' . $postId . '/' . $uniqueName;
    $fileSize = (int)$_FILES['file']['size'];
    $mimeType = (string)($_FILES['file']['type'] ?? 'application/octet-stream');
}

$submissionId = null;

if ($existing) {
    $submissionId = $existing['id'];
    $updateStmt = $conn->prepare(
        "UPDATE tblsubmission
         SET comment = ?,
             file_name = COALESCE(?, file_name),
             file_path = COALESCE(?, file_path),
             file_size = COALESCE(?, file_size),
             mime_type = COALESCE(?, mime_type),
             grade = NULL,
             status = 'submitted',
             submitted_at = CURRENT_TIMESTAMP
         WHERE id = ?"
    );
    if (!$updateStmt) {
        json_out(['status' => 'error', 'message' => 'Failed to prepare update.'], 500);
    }
    $updateStmt->bind_param('sssiss', $comment, $fileName, $filePath, $fileSize, $mimeType, $submissionId);
    if (!$updateStmt->execute()) {
        $dbErr = $updateStmt->error ?: 'Database error.';
        $updateStmt->close();
        json_out(['status' => 'error', 'message' => $dbErr], 500);
    }
    $updateStmt->close();
} else {
    $submissionId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $insertStmt = $conn->prepare(
        "INSERT INTO tblsubmission
         (id, post_id, student_id, comment, file_name, file_path, file_size, mime_type, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'submitted')"
    );
    if (!$insertStmt) {
        json_out(['status' => 'error', 'message' => 'Failed to prepare insert.'], 500);
    }
    $insertStmt->bind_param('ssssssis', $submissionId, $postId, $studentId, $comment, $fileName, $filePath, $fileSize, $mimeType);
    if (!$insertStmt->execute()) {
        $dbErr = $insertStmt->error ?: 'Database error.';
        $insertStmt->close();
        json_out(['status' => 'error', 'message' => $dbErr], 500);
    }
    $insertStmt->close();
}

json_out([
    'status' => 'success',
    'message' => 'Submitted.',
    'post_id' => $postId,
    'submission' => [
        'submission_id' => $submissionId,
        'submitted_at' => date('c'),
        'file_name' => $fileName,
        'is_late' => 0
    ]
]);
