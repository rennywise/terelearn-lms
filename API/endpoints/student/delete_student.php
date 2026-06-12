<?php
/**
 * API/student/delete_student.php — soft delete
 */
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['status'=>'error','message'=>'id required']); exit; }

$stmt = $conn->prepare("UPDATE tblstudent SET is_deleted=1 WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close(); $conn->close();

echo json_encode($affected
    ? ['status'=>'success','message'=>'Student deleted.']
    : ['status'=>'error','message'=>'Student not found.']);
