<?php require_once __DIR__.'/_faculty_guard.php';
$pid=trim($_POST['post_id']??'');
$sid=trim($_POST['student_id']??'');
$minutes=(int)($_POST['minutes']??120);
$note=trim($_POST['note']??'');
if(!$pid||!$sid)fail('post_id+student_id required');
require_owns_post($conn,$pid,$user_id);
$minutes=max(5,min(1440,$minutes));

$conn->query("
CREATE TABLE IF NOT EXISTS tblquizmakeupaccess (
  id VARCHAR(36) PRIMARY KEY,
  post_id VARCHAR(36) NOT NULL,
  student_id VARCHAR(36) NOT NULL,
  granted_by VARCHAR(36) NOT NULL,
  note VARCHAR(255) NULL,
  granted_at DATETIME NOT NULL,
  valid_until DATETIME NOT NULL,
  consumed_at DATETIME NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  INDEX idx_qma_post_student (post_id, student_id),
  INDEX idx_qma_active (is_active, valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$epid=$conn->real_escape_string($pid);
$esid=$conn->real_escape_string($sid);
$euid=$conn->real_escape_string($user_id);
$enote=$conn->real_escape_string($note);

$now = new DateTime('now', new DateTimeZone('Asia/Manila'));
$until = clone $now; $until->modify('+' . $minutes . ' minutes');
$nowS = $conn->real_escape_string($now->format('Y-m-d H:i:s'));
$untilS = $conn->real_escape_string($until->format('Y-m-d H:i:s'));
$id = uuid4();
$eid = $conn->real_escape_string($id);

// Deactivate previous active grants for same student+quiz
$conn->query("UPDATE tblquizmakeupaccess SET is_active=0 WHERE post_id='$epid' AND student_id='$esid' AND is_active=1");
$ins=$conn->query("INSERT INTO tblquizmakeupaccess (id,post_id,student_id,granted_by,note,granted_at,valid_until,consumed_at,is_active) VALUES ('$eid','$epid','$esid','$euid','$enote','$nowS','$untilS',NULL,1)");
if(!$ins) fail('Failed to grant make-up access: '.$conn->error,500);

ok(['post_id'=>$pid,'student_id'=>$sid,'valid_until'=>$until->format('Y-m-d H:i:s'),'minutes'=>$minutes]);

