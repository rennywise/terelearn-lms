<?php
/* API/Admin/fetch_departments.php
   Returns a plain JSON ARRAY (not wrapped object) so admin.php's
   Array.isArray(data) check passes correctly.
*/
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$colRes = $conn->query("SHOW COLUMNS FROM tbldepartment LIKE 'dept_image'");
if ($colRes && $colRes->num_rows === 0) {
    @$conn->query("ALTER TABLE tbldepartment ADD COLUMN dept_image varchar(255) DEFAULT NULL AFTER description");
}

$rows = [];
$res = $conn->query("
    SELECT
        d.id,
        d.dept_code,
        d.dept_name,
        d.description,
        d.dept_image,
        d.is_active,
        d.is_deleted,
        COUNT(c.id) AS program_count
    FROM tbldepartment d
    LEFT JOIN tblcourse c
           ON c.department_id = d.id
          AND c.is_Deleted = 0
    WHERE d.is_deleted = 0
    GROUP BY d.id
    ORDER BY d.dept_name ASC
");
if ($res) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
}
$conn->close();
echo json_encode($rows);
