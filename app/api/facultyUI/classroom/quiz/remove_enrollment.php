<?php
session_start();
$level = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
if (!isset($_SESSION['user_id']) || $level !== 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../../../core/db_connect.php';

$post_id    = trim($_POST['post_id']    ?? '');
$student_id = trim($_POST['student_id'] ?? '');

if (!$post_id || !$student_id) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE tblquizenrollment
     SET status = 'withdrawn', withdrawn_at = NOW()
     WHERE post_id = ? AND student_id = ?"
);
$stmt->bind_param('ss', $post_id, $student_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No enrollment found or already withdrawn']);
}
$stmt->close();
