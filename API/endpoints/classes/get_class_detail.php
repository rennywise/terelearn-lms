<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$id = trim($_GET['id'] ?? '');
if (!$id) { echo json_encode(['status'=>'error','message'=>'ID required']); exit; }

$sql = "SELECT * FROM tblclass WHERE id=? AND is_deleted=0";
$stmt = $conn->prepare($sql); $stmt->bind_param("s",$id); $stmt->execute();
$res = $stmt->get_result();
if (!$res->num_rows) { echo json_encode(['status'=>'error','message'=>'Class not found']); exit; }
$class = $res->fetch_assoc();

$conn->query("CREATE TABLE IF NOT EXISTS tblclassgradingscheme (
  id VARCHAR(36) NOT NULL,
  class_id VARCHAR(36) NOT NULL,
  weight_recitation INT NOT NULL DEFAULT 10,
  weight_quiz INT NOT NULL DEFAULT 20,
  weight_activities INT NOT NULL DEFAULT 30,
  weight_exam INT NOT NULL DEFAULT 40,
  count_recitation INT NOT NULL DEFAULT 0,
  count_quiz INT NOT NULL DEFAULT 0,
  count_activities INT NOT NULL DEFAULT 0,
  count_exam INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_class_id (class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$g = [
  'weight_recitation' => 10,
  'weight_quiz' => 20,
  'weight_activities' => 30,
  'weight_exam' => 40,
  'count_recitation' => 0,
  'count_quiz' => 0,
  'count_activities' => 0,
  'count_exam' => 0
];
$gs = $conn->prepare("SELECT weight_recitation, weight_quiz, weight_activities, weight_exam, count_recitation, count_quiz, count_activities, count_exam FROM tblclassgradingscheme WHERE class_id=? LIMIT 1");
if ($gs) {
  $gs->bind_param("s", $id);
  $gs->execute();
  $gr = $gs->get_result();
  if ($gr && $gr->num_rows) $g = array_merge($g, $gr->fetch_assoc());
  $gs->close();
}
$class['grading_scheme'] = $g;
echo json_encode(['status'=>'success','class'=>$class]);
$stmt->close(); $conn->close();
?>
