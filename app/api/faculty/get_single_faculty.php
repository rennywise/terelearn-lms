<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';
$faculty_number = $_GET['faculty_number'] ?? '';
if (!$faculty_number) { echo json_encode(['status'=>'error','message'=>'Missing faculty number']); exit; }

$sql = "SELECT id, faculty_number, first_name, middle_name, last_name, suffix,
               email, phone, birthdate, username, is_active, is_dean
        FROM tblfaculty
        WHERE faculty_number = ? AND is_deleted = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $faculty_number);
$stmt->execute();
$res = $stmt->get_result();
if (!$res->num_rows) { echo json_encode(['status'=>'error','message'=>'Faculty not found']); exit; }

echo json_encode(['status'=>'success','faculty'=>$res->fetch_assoc()]);
$stmt->close(); $conn->close();
?>
