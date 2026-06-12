<?php
/**
 * API/Faculty/fetch_faculty_json.php
 * Returns all non-deleted faculty as a JSON array.
 * Used by faculty.php to render the table client-side
 * (same pattern as API/student/fetch_students.php).
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../core/db_connect.php';

/* ── Auto-migrate otp_enabled if missing ── */
$colCheck = $conn->query("
    SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'tbluser'
      AND COLUMN_NAME  = 'otp_enabled'
    LIMIT 1
");
if ($colCheck && $colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE tbluser ADD COLUMN otp_enabled TINYINT(1) NOT NULL DEFAULT 1");
}

$whereScope = "f.is_deleted = 0";
$currentAdminId = '';
$currentAdminEmail = '';
$currentAdminUsername = '';
$currentDeptId = 0;
if (isset($_SESSION['user_id'])) {
    $uid = mysqli_real_escape_string($conn, (string)$_SESSION['user_id']);
    $uRes = $conn->query("SELECT email, username FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
    $uRow = $uRes ? $uRes->fetch_assoc() : null;
    if ($uRow) {
        $un = mysqli_real_escape_string($conn, (string)$uRow['username']);
        $em = mysqli_real_escape_string($conn, (string)$uRow['email']);
        $aRes = $conn->query("SELECT id, email, username FROM tbladmin WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
        $aRow = $aRes ? $aRes->fetch_assoc() : null;
        if ($aRow && isset($aRow['id'])) {
            $currentAdminId = mysqli_real_escape_string($conn, (string)$aRow['id']);
            $currentAdminEmail = mysqli_real_escape_string($conn, (string)($aRow['email'] ?? ''));
            $currentAdminUsername = mysqli_real_escape_string($conn, (string)($aRow['username'] ?? ''));
            $dRes = $conn->query("SELECT department_id FROM tbldeanassignment WHERE faculty_id='$currentAdminId' AND is_active=1 ORDER BY assigned_at DESC LIMIT 1");
            $dRow = $dRes ? $dRes->fetch_assoc() : null;
            if ($dRow && isset($dRow['department_id'])) $currentDeptId = (int)$dRow['department_id'];
        }
    }
}

/* Show normal faculty, plus dean/secretary accounts only if they belong to the same department (or self). */
$whereScope .= "
    AND (
        (f.id = '$currentAdminId')
        OR ('$currentAdminEmail' <> '' AND f.email = '$currentAdminEmail')
        OR ('$currentAdminUsername' <> '' AND f.username = '$currentAdminUsername')
        OR f.is_dean = 0
        OR EXISTS (
            SELECT 1
            FROM tbldeanassignment da
            JOIN tbladmin a ON a.id = da.faculty_id AND a.is_deleted = 0
            WHERE da.is_active = 1
              AND da.department_id = $currentDeptId
              AND (
                  a.id = f.id
                  OR (a.email IS NOT NULL AND a.email <> '' AND a.email = f.email)
                  OR (a.username IS NOT NULL AND a.username <> '' AND a.username = f.username)
              )
        )
    )
";

$sql = "
    SELECT
        f.id,
        f.faculty_number,
        f.first_name,
        f.middle_name,
        f.last_name,
        f.email,
        f.phone,
        f.birthdate,
        f.username,
        f.is_active,
        f.is_dean,
        (
            SELECT da.role
            FROM tbldeanassignment da
            JOIN tbladmin a2 ON a2.id = da.faculty_id AND a2.is_deleted = 0
            WHERE da.is_active = 1
              AND (
                  a2.id = f.id
                  OR (a2.email IS NOT NULL AND a2.email <> '' AND a2.email = f.email)
                  OR (a2.username IS NOT NULL AND a2.username <> '' AND a2.username = f.username)
              )
            ORDER BY da.assigned_at DESC
            LIMIT 1
        ) AS admin_role,
        (
            SELECT d.dept_code
            FROM tbldeanassignment da
            JOIN tbladmin a2 ON a2.id = da.faculty_id AND a2.is_deleted = 0
            JOIN tbldepartment d ON d.id = da.department_id
            WHERE da.is_active = 1
              AND (
                  a2.id = f.id
                  OR (a2.email IS NOT NULL AND a2.email <> '' AND a2.email = f.email)
                  OR (a2.username IS NOT NULL AND a2.username <> '' AND a2.username = f.username)
              )
            ORDER BY da.assigned_at DESC
            LIMIT 1
        ) AS assigned_dept_code,
        (
            SELECT d.dept_name
            FROM tbldeanassignment da
            JOIN tbladmin a2 ON a2.id = da.faculty_id AND a2.is_deleted = 0
            JOIN tbldepartment d ON d.id = da.department_id
            WHERE da.is_active = 1
              AND (
                  a2.id = f.id
                  OR (a2.email IS NOT NULL AND a2.email <> '' AND a2.email = f.email)
                  OR (a2.username IS NOT NULL AND a2.username <> '' AND a2.username = f.username)
              )
            ORDER BY da.assigned_at DESC
            LIMIT 1
        ) AS assigned_dept_name,
        COALESCE(u.profile_picture, '') AS profile_picture,
        u.id            AS user_id,
        IFNULL(u.otp_enabled, 1) AS otp_enabled,
        IFNULL(u.first_login,  1) AS first_login
    FROM tblfaculty f
    LEFT JOIN tbluser u
        ON (u.username = f.username OR u.email = f.email)
       AND u.is_deleted = 0
    WHERE $whereScope
    ORDER BY f.last_name, f.first_name
";

$result = $conn->query($sql);
$rows   = [];
if ($result) {
    while ($r = $result->fetch_assoc()) {
        $rows[] = [
            'id'             => $r['id'],
            'faculty_number' => $r['faculty_number'],
            'first_name'     => $r['first_name'],
            'middle_name'    => $r['middle_name'] ?? '',
            'last_name'      => $r['last_name'],
            'email'          => $r['email'] ?? '',
            'phone'          => $r['phone'] ?? '',
            'birthdate'      => $r['birthdate'] ?? '',
            'username'       => $r['username'] ?? '',
            'is_active'      => (int)$r['is_active'],
            'is_dean'        => (int)$r['is_dean'],
            'admin_role'     => $r['admin_role'] ?? null,
            'assigned_dept_code' => $r['assigned_dept_code'] ?? '',
            'assigned_dept_name' => $r['assigned_dept_name'] ?? '',
            'profile_picture' => $r['profile_picture'] ?? '',
            'user_id'        => $r['user_id'] ?? '',
            'otp_enabled'    => (int)$r['otp_enabled'],
            'first_login'    => (int)$r['first_login'],
        ];
    }
}
$conn->close();
echo json_encode($rows);
