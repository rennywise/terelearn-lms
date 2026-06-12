<?php require_once __DIR__.'/_faculty_guard.php';
$pid=trim($_POST['post_id']??'');$sid=trim($_POST['student_id']??'');
if(!$pid||!$sid)fail('post_id+student_id required');
require_owns_post($conn,$pid,$user_id);
$epid=$conn->real_escape_string($pid);$esid=$conn->real_escape_string($sid);
$a=$conn->query("SELECT id FROM tblquizattempt WHERE post_id='$epid' AND student_id='$esid' ORDER BY attempt_number DESC LIMIT 1");
$at=$a?$a->fetch_assoc():null;if(!$at)fail('No attempt');
$aid=$at['id'];
if(isset($_POST['manual_score'])){
  $ms=$_POST['manual_score']===''?null:(float)$_POST['manual_score'];
  $s=$conn->prepare("UPDATE tblquizattempt SET manual_score=? WHERE id=?");$s->bind_param('ds',$ms,$aid);$s->execute();$s->close();
}
if(isset($_POST['comment'])){
  $cm=trim($_POST['comment']);
  $s=$conn->prepare("UPDATE tblquizattempt SET faculty_comment=? WHERE id=?");$s->bind_param('ss',$cm,$aid);$s->execute();$s->close();
}
$ovr=json_decode($_POST['answer_overrides']??'[]',true)?:[];
foreach($ovr as $o){
  $qid=$o['question_id']??'';$v=isset($o['is_correct_override'])&&$o['is_correct_override']!==null?(int)$o['is_correct_override']:null;
  if(!$qid)continue;
  $s=$conn->prepare("UPDATE tblquizanswer SET is_correct_override=? WHERE attempt_id=? AND question_id=?");
  $s->bind_param('iss',$v,$aid,$qid);$s->execute();$s->close();
}
recompute_attempt_scores($conn,$pid);
ok(['attempt_id'=>$aid]);