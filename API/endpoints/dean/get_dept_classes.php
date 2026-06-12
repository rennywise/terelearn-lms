<?php
/**
 * API/Dean/get_dept_classes.php
 * Returns classes for programs under the logged-in dean's department.
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

/* 5. Classes */
$in   = implode(',', $cIds);
$rows = [];
$res  = $conn->query("
    SELECT cl.id, cl.class_code, cl.section, cl.class_semester, cl.year_level,
           cl.schedule, cl.break_time, cl.class_days, cl.is_active,
           cl.course_id, cl.subject_id, cl.faculty_id,
           c.course_code, c.course_name,
           s.subject_code, s.subject_name,
           CONCAT(f.first_name,' ',f.last_name) AS faculty_name,
           f.faculty_number,
           u.profile_picture,
           (
               SELECT COUNT(*)
               FROM tblclassstudents cs
               WHERE cs.class_id = cl.id
                 AND cs.is_deleted = 0
           ) AS student_count,
           (
               SELECT da.role
               FROM tbldeanassignment da
               JOIN tbladmin a ON a.id = da.faculty_id AND a.is_deleted = 0
               WHERE da.is_active = 1
                 AND (
                     a.id = f.id
                     OR (a.email IS NOT NULL AND a.email <> '' AND a.email = f.email)
                     OR (a.username IS NOT NULL AND a.username <> '' AND a.username = f.username)
                 )
               ORDER BY da.assigned_at DESC
               LIMIT 1
           ) AS admin_role
    FROM   tblclass    cl
    JOIN   tblcourse   c  ON c.id  = cl.course_id
    JOIN   tblsubject  s  ON s.id  = cl.subject_id
    JOIN   tblfaculty  f  ON f.id  = cl.faculty_id
    LEFT JOIN tbluser  u  ON (u.username = f.username OR u.email = f.email) AND u.is_deleted=0
    WHERE  cl.course_id IN($in) AND cl.is_deleted=0
    ORDER  BY c.course_code, cl.class_code");
if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode(['status'=>'success','data'=>$rows]);
$conn->close();
