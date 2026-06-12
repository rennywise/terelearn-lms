<?php
/* API/Admin/remove_dean.php */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$id   = (int)($data['id'] ?? 0);

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing assignment ID.']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM tbldeanassignment WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$conn->close();
echo json_encode(['status' => 'success', 'message' => 'Assignment removed.']);
