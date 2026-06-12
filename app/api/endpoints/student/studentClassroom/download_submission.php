<?php
session_start();

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    http_response_code(401); exit('Unauthorised.');
}

$studentSessionId = (string)$_SESSION['user_id'];
$submissionId = trim($_GET['id'] ?? '');

if ($submissionId === '') { http_response_code(400); exit('Missing id.'); }

require_once __DIR__ . '/../../../core/db_connect.php';

$studentDbId = $studentSessionId;
$stRes = $conn->query("SELECT id FROM tblstudent WHERE user_id='" . $conn->real_escape_string($studentSessionId) . "' AND is_deleted=0 LIMIT 1");
if ($stRes && $stRes->num_rows > 0) {
    $studentDbId = (string)$stRes->fetch_assoc()['id'];
}

$stmt = $conn->prepare(
    "SELECT file_name, file_path, mime_type FROM tblsubmission
     WHERE id = ? AND (student_id = ? OR student_id = ?) LIMIT 1"
);
$stmt->bind_param('sss', $submissionId, $studentSessionId, $studentDbId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row)              { http_response_code(404); exit('Not found.'); }
if (!$row['file_path']) { http_response_code(404); exit('No file attached.'); }

$docRoot = realpath(dirname(__DIR__, 5)) . '/';
$absPath = realpath($docRoot . $row['file_path']);

if (!$absPath || strpos($absPath, realpath($docRoot . 'uploads/submissions')) !== 0) {
    http_response_code(403); exit('Access denied.');
}
if (!is_file($absPath)) { http_response_code(404); exit('File missing.'); }

header('Content-Type: ' . ($row['mime_type'] ?: 'application/octet-stream'));
$mime = strtolower((string)($row['mime_type'] ?? ''));
$isInline = (
    strpos($mime, 'image/') === 0 ||
    $mime === 'application/pdf' ||
    strpos($mime, 'text/') === 0
);
$dispType = $isInline ? 'inline' : 'attachment';
header('Content-Disposition: ' . $dispType . '; filename="' . addslashes($row['file_name'] ?: basename($absPath)) . '"');
header('Content-Length: '       . filesize($absPath));
header('Cache-Control: private, no-cache');
readfile($absPath);
