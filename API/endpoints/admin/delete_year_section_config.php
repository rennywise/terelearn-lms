<?php
/**
 * API/Admin/delete_year_section_config.php
 * Soft-deletes a tblyearsectionconfig row.
 * Expects JSON body: { id }
 */
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$id   = isset($body['id']) ? (int)$body['id'] : 0;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
    exit;
}

$stmt = $conn->prepare("UPDATE tblyearsectionconfig SET is_deleted = 1 WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Config deleted.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
}

$conn->close();
