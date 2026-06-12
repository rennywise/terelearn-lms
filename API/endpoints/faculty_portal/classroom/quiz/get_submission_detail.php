<?php require_once __DIR__.'/_faculty_guard.php';
$pid=trim($_GET['post_id']??'');$sid=trim($_GET['student_id']??'');
if(!$pid||!$sid)fail('post_id+student_id required');
require_owns_post($conn,$pid,$user_id);
$epid=$conn->real_escape_string($pid);$esid=$conn->real_escape_string($sid);
// Resolve: faculty may pass tbluser.id — convert to tblstudent.id
$stChk=$conn->query("SELECT id FROM tblstudent WHERE id='$esid' AND is_deleted=0 LIMIT 1");
if(!$stChk||!$stChk->num_rows){
    $uRr=$conn->query("SELECT username,email FROM tbluser WHERE id='$esid' AND is_deleted=0 LIMIT 1");
    if($uRr&&$uRr->num_rows){
        $uu=$uRr->fetch_assoc();
        $uun=$conn->real_escape_string($uu['username']);
        $uem=$conn->real_escape_string($uu['email']);
        $sRr=$conn->query("SELECT id FROM tblstudent WHERE (username='$uun' OR email='$uem') AND is_deleted=0 LIMIT 1");
        if($sRr&&$sRr->num_rows) $esid=$conn->real_escape_string($sRr->fetch_assoc()['id']);
    }
}
$a=$conn->query("SELECT * FROM tblquizattempt WHERE post_id='$epid' AND student_id='$esid' ORDER BY attempt_number DESC LIMIT 1");
$attempt=$a?$a->fetch_assoc():null;
if(!$attempt)ok(['attempt'=>null,'questions'=>[]]);
$aid=$conn->real_escape_string($attempt['id']);
$qs=[];$qr=$conn->query("SELECT * FROM tblpostquestion WHERE post_id='$epid' ORDER BY order_num,id");
while($qr && $q=$qr->fetch_assoc()){
  $qid=$conn->real_escape_string($q['id']);
  $cs=[];$cr=$conn->query("SELECT * FROM tblpostchoice WHERE question_id='$qid' ORDER BY order_num,id");
  while($cr && $c=$cr->fetch_assoc()){$c['is_correct']=(int)$c['is_correct'];$cs[]=$c;}
  $q['choices']=$cs;
  $ar=$conn->query("SELECT * FROM tblquizanswer WHERE attempt_id='$aid' AND question_id='$qid' LIMIT 1");
  $q['answer']=$ar?$ar->fetch_assoc():null;
  $qs[]=$q;
}
ok(['attempt'=>$attempt,'questions'=>$qs]);