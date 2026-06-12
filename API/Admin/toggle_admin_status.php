<?php
/* API/Admin/toggle_admin_status.php */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$input     = json_decode(file_get_contents('php://input'), true);
$id        = $input['id']        ?? null;
$is_active = isset($input['is_active']) ? (int)$input['is_active'] : null;

if (!$id || $is_active === null) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE tbladmin SET is_active = ?, updated_at = NOW() WHERE id = ? AND is_deleted = 0"
);
$stmt->bind_param('is', $is_active, $id);
$stmt->execute();

if ($stmt->affected_rows >= 0) {
    $label = $is_active ? 'activated' : 'deactivated';
    echo json_encode(['status' => 'success', 'message' => "Admin account {$label}."]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Account not found or no change made.']);
}
$stmt->close();
