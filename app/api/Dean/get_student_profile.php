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
    echo json_encode(['status' => 'error', 'message' => 'Missing student id']);
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
$aRes = $conn->query("SELECT id FROM tbladmin WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
$aRow = $aRes ? $aRes->fetch_assoc() : null;
if (!$aRow) {
    echo json_encode(['status' => 'error', 'message' => 'Dean account not found']);
    exit;
}

$adminId = mysqli_real_escape_string($conn, (string)$aRow['id']);
$dRes = $conn->query("SELECT department_id FROM tbldeanassignment WHERE faculty_id='$adminId' AND is_active=1 ORDER BY assigned_at DESC LIMIT 1");
$dRow = $dRes ? $dRes->fetch_assoc() : null;
if (!$dRow) {
    echo json_encode(['status' => 'error', 'message' => 'No active department assignment found']);
    exit;
}

$deptId = (int)$dRow['department_id'];
$pRes = $conn->query("SELECT id FROM tblcourse WHERE department_id=$deptId AND is_deleted=0");
$programIds = [];
if ($pRes) while ($p = $pRes->fetch_assoc()) $programIds[] = (int)$p['id'];
if (!$programIds) {
    echo json_encode(['status' => 'error', 'message' => 'No programs linked to your department']);
    exit;
}

$in = implode(',', $programIds);
$tid = mysqli_real_escape_string($conn, (string)$targetId);
$profileRes = $conn->query("
    SELECT
        st.id, st.user_id, st.student_number, st.first_name, st.middle_name, st.last_name,
        st.email, st.username, st.course_id, st.year_level, st.section, st.is_active,
        c.course_code, c.course_name,
        st.created_at AS profile_created_at,
        (
            SELECT u.profile_picture
            FROM tbluser u
            WHERE u.is_deleted = 0
              AND (
                  u.id = st.user_id
                  OR (u.email IS NOT NULL AND u.email <> '' AND u.email = st.email)
                  OR (u.username IS NOT NULL AND u.username <> '' AND u.username = st.username)
              )
            ORDER BY (u.id = st.user_id) DESC, u.created_at DESC
            LIMIT 1
        ) AS profile_picture,
        (
            SELECT u.created_at
            FROM tbluser u
            WHERE u.is_deleted = 0
              AND (
                  u.id = st.user_id
                  OR (u.email IS NOT NULL AND u.email <> '' AND u.email = st.email)
                  OR (u.username IS NOT NULL AND u.username <> '' AND u.username = st.username)
              )
            ORDER BY (u.id = st.user_id) DESC, u.created_at DESC
            LIMIT 1
        ) AS account_created_at
    FROM tblstudent st
    JOIN tblcourse c ON c.id = st.course_id
    WHERE st.id = '$tid'
      AND st.is_deleted = 0
      AND st.course_id IN ($in)
    LIMIT 1
");
$profile = $profileRes ? $profileRes->fetch_assoc() : null;
if (!$profile) {
    echo json_encode(['status' => 'error', 'message' => 'Student not found or not accessible']);
    exit;
}

$classRows = [];
$studentUserId = mysqli_real_escape_string($conn, (string)($profile['user_id'] ?? ''));
$classRes = $conn->query("
    SELECT
        cl.class_code,
        cl.class_semester,
        c.course_code,
        s.subject_code,
        s.subject_name,
        cs.date_joined AS enrolled_at
    FROM tblclassstudents cs
    JOIN tblclass cl ON cl.id = cs.class_id
    JOIN tblcourse c ON c.id = cl.course_id
    JOIN tblsubject s ON s.id = cl.subject_id
    WHERE (
            cs.student_id = '$tid'
            " . ($studentUserId !== '' ? "OR cs.student_id = '$studentUserId'" : '') . "
          )
      AND cs.is_deleted = 0
      AND cl.is_deleted = 0
      AND cl.course_id IN ($in)
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
