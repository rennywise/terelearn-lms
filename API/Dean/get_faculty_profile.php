<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../core/db_connect.php';

$targetId = $_GET['id'] ?? '';
if ($targetId === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing faculty id']);
    exit;
}

$uid = mysqli_real_escape_string($conn, (string)$_SESSION['user_id']);
$uRes = $conn->query("SELECT email, username FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
$uRow = $uRes ? $uRes->fetch_assoc() : null;
if (!$uRow) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$un = mysqli_real_escape_string($conn, (string)$uRow['username']);
$em = mysqli_real_escape_string($conn, (string)$uRow['email']);
$aRes = $conn->query("SELECT id, email, username FROM tbladmin WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
$aRow = $aRes ? $aRes->fetch_assoc() : null;
if (!$aRow) {
    echo json_encode(['status' => 'error', 'message' => 'Dean account not found']);
    exit;
}

$currentAdminId = mysqli_real_escape_string($conn, (string)$aRow['id']);
$currentAdminEmail = mysqli_real_escape_string($conn, (string)($aRow['email'] ?? ''));
$currentAdminUsername = mysqli_real_escape_string($conn, (string)($aRow['username'] ?? ''));
$currentDeptId = 0;
$dRes = $conn->query("SELECT department_id FROM tbldeanassignment WHERE faculty_id='$currentAdminId' AND is_active=1 ORDER BY assigned_at DESC LIMIT 1");
$dRow = $dRes ? $dRes->fetch_assoc() : null;
if ($dRow && isset($dRow['department_id'])) $currentDeptId = (int)$dRow['department_id'];
$tid = mysqli_real_escape_string($conn, (string)$targetId);

$profileRes = $conn->query("
    SELECT
        f.id, f.faculty_number, f.first_name, f.middle_name, f.last_name,
        f.email, f.phone, f.username, f.birthdate, f.is_active,
        f.created_at AS profile_created_at,
        (
            SELECT u.profile_picture
            FROM tbluser u
            WHERE u.is_deleted = 0
              AND (
                  u.id = f.id
                  OR (u.email IS NOT NULL AND u.email <> '' AND u.email = f.email)
                  OR (u.username IS NOT NULL AND u.username <> '' AND u.username = f.username)
              )
            ORDER BY (u.id = f.id) DESC, u.created_at DESC
            LIMIT 1
        ) AS profile_picture,
        (
            SELECT u.created_at
            FROM tbluser u
            WHERE u.is_deleted = 0
              AND (
                  u.id = f.id
                  OR (u.email IS NOT NULL AND u.email <> '' AND u.email = f.email)
                  OR (u.username IS NOT NULL AND u.username <> '' AND u.username = f.username)
              )
            ORDER BY (u.id = f.id) DESC, u.created_at DESC
            LIMIT 1
        ) AS account_created_at
    FROM tblfaculty f
    WHERE f.id = '$tid'
      AND f.is_deleted = 0
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
    LIMIT 1
");
$profile = $profileRes ? $profileRes->fetch_assoc() : null;
if (!$profile) {
    echo json_encode(['status' => 'error', 'message' => 'Faculty not found or not accessible']);
    exit;
}

$classRows = [];
$classRes = $conn->query("
    SELECT
        cl.class_code,
        cl.class_semester,
        c.course_code,
        s.subject_code,
        s.subject_name,
        (
            SELECT COUNT(*)
            FROM tblclassstudents cs
            WHERE cs.class_id = cl.id
              AND cs.is_deleted = 0
        ) AS student_count
    FROM tblclass cl
    JOIN tblcourse c ON c.id = cl.course_id
    JOIN tblsubject s ON s.id = cl.subject_id
    WHERE cl.faculty_id = '$tid'
      AND cl.is_deleted = 0
    ORDER BY cl.class_code
");
if ($classRes) {
    while ($r = $classRes->fetch_assoc()) $classRows[] = $r;
}

echo json_encode([
    'status'  => 'success',
    'profile' => $profile,
    'classes' => $classRows
]);
$conn->close();
