<?php
/**
 * API/Dean/get_dept_subjects.php
 * Returns subjects for programs under the logged-in dean's department.
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

/* 1. tbluser -> email/username */
$uRes = $conn->query("SELECT email,username FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
$uRow = $uRes ? $uRes->fetch_assoc() : null;
if (!$uRow) { echo json_encode(['status'=>'error','message'=>'User not found','data'=>[]]); exit; }

$un = mysqli_real_escape_string($conn, $uRow['username']);
$em = mysqli_real_escape_string($conn, $uRow['email']);

/* 2. tbladmin -> admin id */
$aRes = $conn->query("SELECT id FROM tbladmin WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
$aRow = $aRes ? $aRes->fetch_assoc() : null;
if (!$aRow) { echo json_encode(['status'=>'error','message'=>'No admin account found','data'=>[]]); exit; }

$adminId = mysqli_real_escape_string($conn, $aRow['id']);

/* 3. tbldeanassignment -> department */
$dRes = $conn->query("SELECT department_id FROM tbldeanassignment WHERE faculty_id='$adminId' AND is_active=1 ORDER BY assigned_at DESC LIMIT 1");
$dRow = $dRes ? $dRes->fetch_assoc() : null;
if (!$dRow) { echo json_encode(['status'=>'success','data'=>[]]); exit; }

$deptId = (int)$dRow['department_id'];

/* 4. Programs in this dept */
$cRes = $conn->query("SELECT id FROM tblcourse WHERE department_id=$deptId AND is_deleted=0");
$cIds = [];
if ($cRes) while ($r = $cRes->fetch_assoc()) $cIds[] = (string)$r['id'];
if (!$cIds) { echo json_encode(['status'=>'success','data'=>[]]); exit; }

$quotedCourseIds = implode(',', array_map(
    static fn($id) => "'" . mysqli_real_escape_string($conn, $id) . "'",
    $cIds
));

/* 5. Subjects accessible to those programs */
$rows = [];
$res  = $conn->query("
    SELECT s.id, s.subject_code, s.subject_name, s.course_id, s.is_active,
           sp.year_level, sp.semester, sp.school_year, sp.share_scope,
           sp.owner_course_id,
           c.course_code, c.course_name,
           owner_c.course_code AS owner_course_code,
           owner_c.course_name AS owner_course_name,
           CASE
             WHEN sp.owner_course_id IN($quotedCourseIds) OR sp.course_id IN($quotedCourseIds) THEN 1
             ELSE 0
           END AS can_edit,
           (
             SELECT GROUP_CONCAT(DISTINCT spp.program_id ORDER BY spp.program_id SEPARATOR ',')
             FROM tblsubjectpreset_programs spp
             WHERE spp.preset_id = sp.id
           ) AS shared_program_ids,
           (
             SELECT GROUP_CONCAT(DISTINCT CONCAT(tc.course_code, ' - ', tc.course_name) ORDER BY tc.course_code SEPARATOR '||')
             FROM tblsubjectpreset_programs spp
             JOIN tblcourse tc ON tc.id = spp.program_id
             WHERE spp.preset_id = sp.id
               AND tc.id <> COALESCE(sp.owner_course_id, sp.course_id)
           ) AS shared_program_labels,
           (
             SELECT COUNT(*)
             FROM tblclass cl
             LEFT JOIN tblsubject sx ON sx.id = cl.subject_id
             WHERE cl.course_id IN (
                    SELECT DISTINCT spp2.program_id
                    FROM tblsubjectpreset_programs spp2
                    WHERE spp2.preset_id = sp.id
                    UNION
                    SELECT COALESCE(sp.owner_course_id, sp.course_id)
                  )
               AND cl.is_deleted = 0
               AND TRIM(COALESCE(sx.subject_name, '')) = TRIM(COALESCE(s.subject_name, ''))
               AND (
                    sp.semester IS NULL
                    OR sp.semester = ''
                    OR cl.class_semester LIKE CONCAT(sp.semester, '%')
               )
           ) AS class_count
    FROM   tblsubject s
    JOIN   tblcourse  c ON c.id = s.course_id
    LEFT JOIN tblsemestersetting ss
           ON ss.is_active = 1 AND ss.is_deleted = 0
    LEFT JOIN tblsubjectpreset sp
           ON sp.id = (
                SELECT sp2.id
                FROM tblsubjectpreset sp2
                WHERE sp2.subject_id = s.id
                ORDER BY
                    (sp2.school_year = ss.school_year AND sp2.semester = ss.semester) DESC,
                    sp2.id DESC
                LIMIT 1
           )
    LEFT JOIN tblcourse owner_c
           ON owner_c.id = COALESCE(sp.owner_course_id, sp.course_id)
    WHERE  s.is_deleted=0
      AND sp.id IS NOT NULL
      AND (
            COALESCE(sp.owner_course_id, sp.course_id) IN($quotedCourseIds)
            OR sp.share_scope = 'all_programs'
            OR EXISTS (
                SELECT 1
                FROM tblsubjectpreset_programs sppx
                WHERE sppx.preset_id = sp.id
                  AND sppx.program_id IN($quotedCourseIds)
            )
          )
    ORDER  BY c.course_code, s.subject_code");
if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode(['status'=>'success','data'=>$rows]);
$conn->close();
