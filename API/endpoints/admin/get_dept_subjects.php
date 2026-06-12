<?php
/* API/Admin/get_dept_subjects.php
   GET ?dept_ids=1,2
   Returns subjects whose course_id belongs to programs in these departments.
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
if (!$progIds) { echo json_encode(['status'=>'success','subjects'=>[]]); exit; }
$progIn = implode(',', $progIds);

$sql = "
  SELECT s.id, s.subject_code, s.subject_name, s.is_active,
         c.id AS course_id, c.course_code, c.course_name, c.department_id
  FROM tblsubject s
  JOIN tblcourse c ON c.id = s.course_id
  WHERE s.course_id IN ($progIn) AND s.is_deleted = 0
  ORDER BY c.course_code, s.subject_code
";

$res      = $conn->query($sql);
$subjects = [];
while ($r = $res->fetch_assoc()) $subjects[] = $r;
$conn->close();
echo json_encode(['status'=>'success','subjects'=>$subjects]);
