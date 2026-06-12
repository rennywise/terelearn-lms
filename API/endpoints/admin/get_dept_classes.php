<?php
/* API/Admin/get_dept_classes.php
   GET ?dept_ids=1,2
   Returns classes whose course_id belongs to programs in these departments.
*/
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$raw     = trim($_GET['dept_ids'] ?? '');
$deptIds = array_filter(array_map('intval', explode(',', $raw)));
if (!$deptIds) { echo json_encode(['status'=>'error','message'=>'dept_ids required.']); exit; }
$in = implode(',', $deptIds);

/* Get program IDs */
$pRows   = $conn->query("SELECT id FROM tblcourse WHERE department_id IN ($in) AND is_Deleted=0");
$progIds = [];
while ($r = $pRows->fetch_row()) $progIds[] = $r[0];
if (!$progIds) { echo json_encode(['status'=>'success','classes'=>[]]); exit; }
$progIn = implode(',', $progIds);

$sql = "
  SELECT
    cl.id, cl.class_code, cl.class_semester, cl.year_level, cl.section,
    cl.schedule, cl.is_active, cl.course_id,
    sub.subject_code, sub.subject_name,
    CONCAT(f.last_name, ', ', f.first_name) AS faculty_name,
    c.course_code, c.department_id,
    (SELECT COUNT(*) FROM tblclassstudents cs WHERE cs.class_id = cl.id AND cs.is_deleted = 0) AS student_count
  FROM tblclass cl
  LEFT JOIN tblsubject sub ON sub.id = cl.subject_id
  LEFT JOIN tblfaculty  f  ON f.id   = cl.faculty_id
  JOIN tblcourse c         ON c.id   = cl.course_id
  WHERE cl.course_id IN ($progIn) AND cl.is_deleted = 0
  ORDER BY cl.class_semester DESC, c.course_code, cl.year_level
";

$res     = $conn->query($sql);
$classes = [];
while ($r = $res->fetch_assoc()) $classes[] = $r;
$conn->close();
echo json_encode(['status'=>'success','classes'=>$classes]);
