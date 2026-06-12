<?php
/* API/Admin/toggle_department.php */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$id     = intval($data['id']     ?? 0);
$action = trim($data['action']   ?? 'toggle'); // 'toggle' | 'delete'

if (!$id) { echo json_encode(['status'=>'error','message'=>'Invalid department ID.']); exit; }

if ($action === 'delete') {
    $stmt = $conn->prepare("UPDATE tbldepartment SET is_deleted=1 WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['status'=>'success','message'=>'Department removed.']);
} else {
    /* Toggle is_active */
    $stmt = $conn->prepare("UPDATE tbldepartment SET is_active = 1 - is_active WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['status'=>'success','message'=>'Department status updated.']);
}
$conn->close();
