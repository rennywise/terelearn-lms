<?php
/**
 * API/Admin/toggle_program.php
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$data     = json_decode(file_get_contents('php://input'), true) ?? [];
$id       = (int)($data['id'] ?? 0);
$isActive = (int)($data['is_active'] ?? 0);

if (!$id) { echo json_encode(['status'=>'error','message'=>'Missing ID.']); exit; }

$stmt = $conn->prepare("UPDATE tblcourse SET is_active=? WHERE id=?");
$stmt->bind_param('ii', $isActive, $id);
$stmt->execute();
$label = $isActive ? 'activated' : 'deactivated';
echo json_encode(['status'=>'success','message'=>"Program $label successfully."]);
$conn->close();
