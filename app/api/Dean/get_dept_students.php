<?php
/**
 * API/Dean/get_dept_students.php
 * Returns students for programs under the logged-in dean's department.
 * Resolves dean via tbladmin (not tblfaculty).
 */
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized','data'=>[]]);
    exit;
}
require_once __DIR__ . '/../../core/db_connect.php';

$uid  = mysqli_real_escape_string($conn, $_SESSION['user_id']);

/* 1. tbluser */
$uRes = $conn->query("SELECT email,username FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
$uRow = $uRes ? $uRes->fetch_assoc() : null;
if (!$uRow) { echo json_encode(['status'=>'error','message'=>'User not found','data'=>[]]); exit; }

$un = mysqli_real_escape_string($conn, $uRow['username']);
$em = mysqli_real_escape_string($conn, $uRow['email']);

/* 2. tbladmin */
$aRes = $conn->query("SELECT id FROM tbladmin WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
$aRow = $aRes ? $aRes->fetch_assoc() : null;
if (!$aRow) { echo json_encode(['status'=>'error','message'=>'No admin account found','data'=>[]]); exit; }

$adminId = mysqli_real_escape_string($conn, $aRow['id']);

/* 3. tbldeanassignment */
$dRes = $conn->query("SELECT department_id FROM tbldeanassignment WHERE faculty_id='$adminId' AND is_active=1 ORDER BY assigned_at DESC LIMIT 1");
$dRow = $dRes ? $dRes->fetch_assoc() : null;
if (!$dRow) { echo json_encode(['status'=>'success','data'=>[]]); exit; }

$deptId = (int)$dRow['department_id'];

/* 4. Programs */
$cRes = $conn->query("SELECT id FROM tblcourse WHERE department_id=$deptId AND is_deleted=0");
$cIds = [];
if ($cRes) while ($r = $cRes->fetch_assoc()) $cIds[] = (int)$r['id'];
if (!$cIds) { echo json_encode(['status'=>'success','data'=>[]]); exit; }

/* 5. Students */
$in   = implode(',', $cIds);
$rows = [];
$res  = $conn->query("
    SELECT st.id, st.student_number, st.first_name, st.middle_name, st.last_name,
           st.email, st.username, st.year_level, st.section, st.is_active,
           st.birthdate, st.course_id,
           c.course_code, c.course_name,
           u.id             AS user_id,
           COALESCE(u.profile_picture, '') AS profile_picture,
           COALESCE(u.otp_enabled, 0) AS otp_enabled,
           COALESCE(u.first_login, 1) AS first_login
    FROM   tblstudent st
    JOIN   tblcourse  c  ON c.id = st.course_id
    LEFT   JOIN tbluser u
           ON (u.email = st.email OR u.username = st.username)
           AND u.is_deleted = 0
    WHERE  st.course_id IN($in) AND st.is_deleted=0
    ORDER  BY c.course_code, st.last_name, st.first_name");
if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode(['status'=>'success','data'=>$rows]);
$conn->close();
