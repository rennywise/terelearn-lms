<?php
/* API/delete_program.php — soft-deletes a program */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$id = intval($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid program ID.']);
    exit;
}

$stmt = $conn->prepare("UPDATE tblcourse SET is_deleted = 1 WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Program deleted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Program not found or already deleted.']);
}

$stmt->close();
$conn->close();
