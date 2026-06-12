<?php
/**
 * API/student/toggle_status.php
 */
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['status'=>'error','message'=>'id required']); exit; }

$stmt = $conn->prepare("UPDATE tblstudent SET is_active = 1 - is_active WHERE id=? AND is_deleted=0");
$stmt->bind_param('i', $id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected) {
    $rs = $conn->query("SELECT is_active FROM tblstudent WHERE id=$id");
    $row = $rs->fetch_assoc();
    $conn->close();
    $label = $row['is_active'] ? 'activated' : 'deactivated';
    echo json_encode(['status'=>'success','message'=>"Student $label successfully.",'is_active'=>(int)$row['is_active']]);
} else {
    $conn->close();
    echo json_encode(['status'=>'error','message'=>'Student not found.']);
}
