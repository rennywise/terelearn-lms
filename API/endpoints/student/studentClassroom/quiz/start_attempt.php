

<?php require_once __DIR__.'/_student_guard.php';
//student/studentClassroom/quiz/start_attempt.php
function _parse_mnl_ts($v){
  if (!$v) return null;
  $v = trim((string)$v);
  if ($v === '') return null;
  $tz = new DateTimeZone('Asia/Manila');
  $d = DateTime::createFromFormat('Y-m-d H:i:s', $v, $tz);
  if ($d instanceof DateTime) return $d->getTimestamp();
  $d = DateTime::createFromFormat('Y-m-d\TH:i', $v, $tz);
  if ($d instanceof DateTime) return $d->getTimestamp();
  $t = strtotime($v);
  return ($t !== false) ? $t : null;
}

$pid=trim($_POST['post_id']??'');if(!$pid)fail('post_id required');
$p=get_quiz_post($conn,$pid,$student_id);
$assessmentLabel = assessment_label($p);
$assessmentLower = strtolower($assessmentLabel);
$epid=$conn->real_escape_string($pid);$esid=$conn->real_escape_string($student_id);
$nowMnlTs = (new DateTime('now', new DateTimeZone('Asia/Manila')))->getTimestamp();
$hasMakeupGrant = false;
$mkTable = $conn->query("SHOW TABLES LIKE 'tblquizmakeupaccess'");
if ($mkTable && $mkTable->num_rows > 0) {
  $mk = $conn->query("SELECT id, valid_until FROM tblquizmakeupaccess WHERE post_id='$epid' AND student_id='$esid' AND is_active=1 AND consumed_at IS NULL ORDER BY granted_at DESC LIMIT 1");
  if ($mk && $mk->num_rows > 0) {
    $m = $mk->fetch_assoc();
    $mUntil = _parse_mnl_ts($m['valid_until'] ?? null);
    if ($mUntil !== null && $mUntil > $nowMnlTs) {
      $hasMakeupGrant = true;
    } else {
      $mid = $conn->real_escape_string($m['id']);
      $conn->query("UPDATE tblquizmakeupaccess SET is_active=0 WHERE id='$mid'");
    }
  }
}
// Resume in-progress?
$r=$conn->query("SELECT * FROM tblquizattempt WHERE post_id='$epid' AND student_id='$esid' AND status='in_progress' ORDER BY attempt_number DESC LIMIT 1");
$att=$r?$r->fetch_assoc():null;
// Even on resume: if faculty ended the live quiz, force-submit now and refuse.
if($att && ($p['quiz_mode']??'due_date')==='live' && !empty($p['live_ended_at'])){
  $aid=$conn->real_escape_string($att['id']);
  $now=date('Y-m-d H:i:s');
  $conn->query("UPDATE tblquizattempt SET status='submitted', submitted_at='".$conn->real_escape_string($now)."' WHERE id='$aid' AND status='in_progress'");
  fail("The live {$assessmentLower} has ended",403);
}
if(!$att){
  $isLiveMode = (($p['quiz_mode'] ?? 'due_date') === 'live');
  if ($isLiveMode) {
    // Live mode requires explicit student join before attempt can start.
    $er=$conn->query("SELECT id FROM tblquizenrollment WHERE post_id='$epid' AND student_id='$esid' AND status='enrolled' LIMIT 1");
    if(!$er || $er->num_rows===0) fail("Join the {$assessmentLower} first before it starts",403);
  }
  if($isLiveMode){
    if(!is_quiz_open($p)) fail("{$assessmentLabel} is not open",403);
    if(empty($p['live_started_at'])) fail("Waiting for the professor to start the live {$assessmentLower}",403);
    if ((int)($p['is_force_open'] ?? 0) !== 1) fail("{$assessmentLabel} is paused by professor",403);
    if(!empty($p['live_ended_at']))  fail("The live {$assessmentLower} has ended",403);
  } else {
    // Due-date mode uses a strict scheduled window.
    if (!$hasMakeupGrant && (int)($p['is_force_closed'] ?? 0) === 1) {
      fail("The {$assessmentLower} is closed by the professor",403);
    }
    $openAtTs = _parse_mnl_ts($p['open_at'] ?? null);
    $closeAtTs = _parse_mnl_ts($p['close_at'] ?? null);
    $dueTs = _parse_mnl_ts($p['due_date'] ?? null);
    if (!$hasMakeupGrant && (int)($p['is_force_open'] ?? 0) !== 1 && $openAtTs !== null && $openAtTs > $nowMnlTs) {
      fail("This {$assessmentLower} is not open yet. Please wait for the start time.",403);
    }
    // Scheduled window gate for due-date mode.
    if (!$hasMakeupGrant && (int)($p['is_force_open'] ?? 0) !== 1 && $closeAtTs !== null && $closeAtTs <= $nowMnlTs) {
      fail("The {$assessmentLower} time window has ended",403);
    }
    // Backward compatibility for older records that still rely on due_date only.
    if (!$hasMakeupGrant && (int)($p['is_force_open'] ?? 0) !== 1 && $closeAtTs === null && $dueTs !== null && $dueTs <= $nowMnlTs) {
      fail('The due date has passed',403);
    }
  }
  // Count existing attempts
  $cr=$conn->query("SELECT COUNT(*) c FROM tblquizattempt WHERE post_id='$epid' AND student_id='$esid'");
  $cnt=$cr?(int)($cr->fetch_assoc()['c']):0;
  $max=(int)$p['max_attempts'];
 if(!$hasMakeupGrant && $max > 0 && $cnt >= $max) fail('No attempts left', 403);
  $aid=uuid4();$an=$cnt+1;$now=date('Y-m-d H:i:s');
  $s=$conn->prepare("INSERT INTO tblquizattempt(id,post_id,student_id,attempt_number,status,started_at) VALUES(?,?,?,?,'in_progress',?)");
  $s->bind_param('sssis',$aid,$pid,$student_id,$an,$now);$s->execute();$s->close();
  if ($hasMakeupGrant && $mkTable && $mkTable->num_rows > 0) {
    $nowS = $conn->real_escape_string((new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s'));
    $conn->query("UPDATE tblquizmakeupaccess SET consumed_at='$nowS', is_active=0 WHERE post_id='$epid' AND student_id='$esid' AND is_active=1 AND consumed_at IS NULL");
  }
  $att=['id'=>$aid,'post_id'=>$pid,'student_id'=>$student_id,'attempt_number'=>$an,'status'=>'in_progress','started_at'=>$now];
}
// Sanitized questions
$qs=[];$qr=$conn->query("SELECT id,question,points,time_limit_seconds,order_num FROM tblpostquestion WHERE post_id='$epid' AND is_excluded=0 ORDER BY order_num,id");
while($qr && $q=$qr->fetch_assoc()){
  $qid=$conn->real_escape_string($q['id']);
  $cs=[];$cr=$conn->query("SELECT id,choice_text,order_num FROM tblpostchoice WHERE question_id='$qid' ORDER BY order_num,id");
  while($cr && $c=$cr->fetch_assoc())$cs[]=$c;
  $q['choices']=$cs;$qs[]=$q;
}
// Existing answers (for resume)
$aid=$conn->real_escape_string($att['id']);$ans=[];
$ar=$conn->query("SELECT question_id,selected_choice_id,answered_at FROM tblquizanswer WHERE attempt_id='$aid'");
while($ar && $a=$ar->fetch_assoc())$ans[$a['question_id']]=$a;
ok(['attempt'=>$att,'post'=>[
  'id'=>$p['id'],
  'title'=>$p['title'],
  'post_type'=>$p['post_type'],
  'time_mode'=>$p['time_mode'],
  'quiz_mode'=>$p['quiz_mode'],
  'time_limit_seconds'=>$p['time_limit_seconds'],
  'close_at'=>$p['close_at'],
  'max_attempts'=>$p['max_attempts'],
  'class_code'=>$p['class_code'] ?? '',
  'class_semester'=>$p['class_semester'] ?? '',
  'section'=>$p['section'] ?? '',
  'year_level'=>$p['year_level'] ?? '',
  'schedule'=>$p['schedule'] ?? '',
  'subject_code'=>$p['subject_code'] ?? '',
  'subject_name'=>$p['subject_name'] ?? '',
  'course_code'=>$p['course_code'] ?? '',
  'course_name'=>$p['course_name'] ?? '',
  'faculty_name'=>$p['faculty_name'] ?? ''
],'questions'=>$qs,'answers'=>$ans]);
