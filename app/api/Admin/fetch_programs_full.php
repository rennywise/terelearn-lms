<?php
/* API/Admin/fetch_programs_full.php
   Returns all programs with department name for the Programs section table.
*/
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$rows = [];
$res = $conn->query("
    SELECT
        c.id,
        c.course_code,
        c.course_name,
        c.department_id,
        c.is_active,
        d.dept_code,
        d.dept_name
    FROM tblcourse c
    LEFT JOIN tbldepartment d ON d.id = c.department_id AND d.is_deleted = 0
    WHERE c.is_Deleted = 0
    ORDER BY c.course_code ASC
");
if ($res) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
}
$conn->close();
echo json_encode(['status' => 'success', 'data' => $rows]);
