<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0) !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$post_id = trim($data['post_id'] ?? $_POST['post_id'] ?? '');
$user_id = trim($_SESSION['user_id'] ?? '');

if ($post_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'post_id is required']);
    exit;
}

// author_id in tblpost stores tbluser.id directly — no faculty lookup needed
$checkStmt = $conn->prepare("
    SELECT id FROM tblpost
    WHERE id = ? AND author_id = ? AND is_deleted = 0
    LIMIT 1
");
$checkStmt->bind_param("ss", $post_id, $user_id);
$checkStmt->execute();
$post = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if (!$post) {
    echo json_encode(['status' => 'error', 'message' => 'Not found or already deleted.']);
    $conn->close();
    exit;
}

$deleteStmt = $conn->prepare("
    UPDATE tblpost SET is_deleted = 1
    WHERE id = ? AND author_id = ? AND is_deleted = 0
");
$deleteStmt->bind_param("ss", $post_id, $user_id);

if (!$deleteStmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Delete failed: ' . $deleteStmt->error]);
    $deleteStmt->close();
    $conn->close();
    exit;
}

$deleteStmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'message' => 'Post deleted successfully.']);
