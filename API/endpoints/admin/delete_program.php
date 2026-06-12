<?php
/**
 * API/Admin/delete_program.php
 * Soft-deletes a program and unlinks it from its department.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$id   = (int)($data['id'] ?? 0);

if (!$id) { echo json_encode(['status'=>'error','message'=>'Missing ID.']); exit; }

// Soft delete and unlink from department
$stmt = $conn->prepare("UPDATE tblcourse SET is_deleted=1, department_id=NULL WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();

echo json_encode(['status'=>'success','message'=>'Program deleted and unlinked from department.']);
$conn->close();
