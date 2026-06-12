<?php
/**
 * API/Admin/fetch_dean_assignments.php
 * Returns all active dean/secretary → department assignments.
 * Joins tbladmin (not tblfaculty) for person details.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$rows = [];
$res  = $conn->query("
    SELECT
        da.id,
        da.faculty_id    AS admin_account_id,
        da.department_id,
        da.role,
        da.is_active,
        da.assigned_at,
        CONCAT(a.first_name, ' ', a.last_name) AS dean_name,
        a.admin_number   AS id_number,
        a.email,
        d.dept_code,
        d.dept_name
    FROM   tbldeanassignment da
    JOIN   tbladmin      a  ON a.id  = da.faculty_id   AND a.is_deleted  = 0
    JOIN   tbldepartment d  ON d.id  = da.department_id
    WHERE  da.is_active = 1
    ORDER  BY d.dept_code, a.last_name
");

if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode(['status' => 'success', 'data' => $rows]);
$conn->close();
