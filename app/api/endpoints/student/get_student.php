<?php
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['status' => 'error', 'message' => 'id required']); exit; }

$stmt = $conn->prepare("
    SELECT s.id, s.student_number, s.first_name, s.middle_name, s.last_name, s.suffix,
           s.email, s.username, s.birthdate, s.course_id, s.is_active, s.user_id,
           c.course_code, c.course_name
    FROM   tblstudent s
    LEFT JOIN tblcourse c ON c.id = s.course_id
    WHERE  s.id = ? AND s.is_deleted = 0 LIMIT 1
");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close(); $conn->close();

if ($row) echo json_encode(['status' => 'success', 'student' => $row]);
else      echo json_encode(['status' => 'error',   'message' => 'Student not found.']);
