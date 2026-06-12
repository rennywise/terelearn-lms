<?php
/**
 * API/Admin/fetch_dean_accounts.php
 * Returns all dean/secretary accounts from tbladmin.
 * Also returns whether each account has a tbluser row (is_assigned)
 * and whether they are also in tblfaculty (is_also_faculty).
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$rows = [];
$hasAdminRole = false;
$colRes = $conn->query("SHOW COLUMNS FROM tbladmin LIKE 'admin_role'");
if ($colRes && $colRes->num_rows > 0) {
    $hasAdminRole = true;
}
$adminRoleSelect = $hasAdminRole ? "a.admin_role" : "NULL AS admin_role";

$res  = $conn->query("
    SELECT
        a.id,
        a.admin_number   AS id_number,
        a.first_name,
        a.middle_name,
        a.last_name,
        a.suffix,
        a.email,
        a.phone,
        a.birthdate,
        a.username,
        a.user_type_id,
        $adminRoleSelect,
        a.is_active,
        a.is_superadmin,
        a.created_at,
        /* Has tbluser row = has been assigned to a dept at least once */
        CASE WHEN u.id IS NOT NULL THEN 1 ELSE 0 END AS is_assigned,
        u.id            AS user_id,
        u.otp_enabled,
        u.first_login,
        /* Is also a professor in tblfaculty */
        CASE WHEN f.id IS NOT NULL THEN 1 ELSE 0 END AS is_also_faculty
    FROM   tbladmin a
    LEFT   JOIN tbluser    u
           ON (u.email = a.email OR u.username = a.username)
          AND u.is_deleted = 0
          AND u.user_level_id <> 3
    LEFT   JOIN tblfaculty f ON (f.email = a.email OR f.username = a.username) AND f.is_deleted = 0
    WHERE  a.is_deleted = 0
      AND  a.user_type_id <> 1
    ORDER  BY a.last_name, a.first_name
");

if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode(['status' => 'success', 'data' => $rows]);
$conn->close();
