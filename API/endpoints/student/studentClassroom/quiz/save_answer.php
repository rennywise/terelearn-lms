<?php require_once __DIR__.'/_student_guard.php';

//student/studentClassroom/quiz/save_answer.php
$aid=trim($_POST['attempt_id']??'');$qid=trim($_POST['question_id']??'');$cid=trim($_POST['choice_id']??'');
if(!$aid||!$qid)fail('attempt_id+question_id required');
$eaid=$conn->real_escape_string($aid);$esid=$conn->real_escape_string($student_id);
$ar=$conn->query("SELECT * FROM tblquizattempt WHERE id='$eaid' AND student_id='$esid' AND status='in_progress' LIMIT 1");
$at=$ar?$ar->fetch_assoc():null;if(!$at)fail('Invalid attempt',404);
$choice=$cid===''?null:$cid;$now=date('Y-m-d H:i:s');
$er=$conn->query("SELECT id FROM tblquizanswer WHERE attempt_id='$eaid' AND question_id='".$conn->real_escape_string($qid)."' LIMIT 1");
$ex=$er?$er->fetch_assoc():null;
if($ex){
  $s=$conn->prepare("UPDATE tblquizanswer SET selected_choice_id=?,answered_at=? WHERE id=?");
  $s->bind_param('sss',$choice,$now,$ex['id']);$s->execute();$s->close();
}else{
  $nid=uuid4();
  $s=$conn->prepare("INSERT INTO tblquizanswer(id,attempt_id,question_id,selected_choice_id,answered_at) VALUES(?,?,?,?,?)");
  $s->bind_param('sssss',$nid,$aid,$qid,$choice,$now);$s->execute();$s->close();
}
ok();