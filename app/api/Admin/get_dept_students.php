<?php
/* API/Admin/get_dept_students.php
   GET ?dept_ids=1,2
   Returns students whose course_id belongs to programs in these departments.
*/
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$raw     = trim($_GET['dept_ids'] ?? '');
$deptIds = array_filter(array_map('intval', explode(',', $raw)));
if (!$deptIds) { echo json_encode(['status'=>'error','message'=>'dept_ids required.']); exit; }
$in = implode(',', $deptIds);

/* Get program IDs */
$pRows = $conn->query("SELECT id FROM tblcourse WHERE department_id IN ($in) AND is_Deleted=0");
$progIds = [];
while ($r = $pRows->fetch_row()) $progIds[] = $r[0];
if (!$progIds) { echo json_encode(['status'=>'success','students'  =>[]]); exit; }
$progIn = implode(',', $progIds);

$sql = "
  SELECT s.id, s.student_number, s.first_name, s.middle_name, s.last_name,
         s.email, s.year_level, s.section, s.is_active,
         c.course_code, c.course_name, c.department_id
  FROM tblstudent s
  JOIN tblcourse c ON c.id = s.course_id
  WHERE s.course_id IN ($progIn) AND s.is_deleted = 0
  ORDER BY s.last_name, s.first_name
";

$res      = $conn->query($sql);
$students = [];
while ($r = $res->fetch_assoc()) $students[] = $r;
$conn->close();
echo json_encode(['status'=>'success','students'=>$students]);
