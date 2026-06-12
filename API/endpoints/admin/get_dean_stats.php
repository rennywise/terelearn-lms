<?php
/* API/Admin/get_dean_stats.php
   GET ?dept_ids=1,2,3
   Returns counts scoped to the given department IDs.
*/
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$raw     = trim($_GET['dept_ids'] ?? '');
$deptIds = array_filter(array_map('intval', explode(',', $raw)));
if (!$deptIds) { echo json_encode(['status'=>'error','message'=>'dept_ids required.']); exit; }

$in = implode(',', $deptIds);

/* Programs in these depts */
$totalPrograms = $conn->query("SELECT COUNT(*) FROM tblcourse WHERE department_id IN ($in) AND is_Deleted=0")->fetch_row()[0];

/* Program IDs for further scoping */
$progIds = [];
$pr = $conn->query("SELECT id FROM tblcourse WHERE department_id IN ($in) AND is_Deleted=0");
while ($r = $pr->fetch_row()) $progIds[] = $r[0];
$progIn = $progIds ? implode(',', $progIds) : '0';

/* Students via course_id */
$totalStudents = $conn->query("SELECT COUNT(*) FROM tblstudent WHERE course_id IN ($progIn) AND is_deleted=0")->fetch_row()[0];

/* Subjects */
$totalSubjects = $conn->query("SELECT COUNT(*) FROM tblsubject WHERE course_id IN ($progIn) AND is_deleted=0")->fetch_row()[0];

/* Classes via course_id */
$totalClasses = $conn->query("SELECT COUNT(*) FROM tblclass WHERE course_id IN ($progIn) AND is_deleted=0 AND is_active=1")->fetch_row()[0];

/* Faculty = all active (shared pool) */
$totalFaculty = $conn->query("SELECT COUNT(*) FROM tblfaculty WHERE is_deleted=0 AND is_active=1")->fetch_row()[0];

$conn->close();
echo json_encode([
    'status' => 'success',
    'stats'  => [
        'total_programs' => $totalPrograms,
        'total_students' => $totalStudents,
        'total_subjects' => $totalSubjects,
        'total_classes'  => $totalClasses,
        'total_faculty'  => $totalFaculty,
    ]
]);
