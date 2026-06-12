<?php
/* API/fetch_programs.php */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$programs = [];
$res = $conn->query("SELECT id, course_code, course_name, department_id FROM tblcourse WHERE is_Deleted=0 ORDER BY course_code ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) $programs[] = $r;
}
$conn->close();
echo json_encode(['status' => 'success', 'programs' => $programs]);
