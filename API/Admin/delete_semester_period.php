<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$d  = json_decode(file_get_contents('php://input'), true);
$id = intval($d['id'] ?? 0);

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing ID.']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM tblsemestersetting WHERE id = ? AND is_active = 0");
$stmt->bind_param('i', $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Period deleted.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Cannot delete the active period. Set another period as active first.']);
}
$stmt->close();
$conn->close(); 
