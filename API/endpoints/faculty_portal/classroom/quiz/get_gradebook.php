<?php require_once __DIR__.'/_faculty_guard.php';
$pid=trim($_GET['post_id']??'');if(!$pid)fail('post_id required');
$post=require_owns_post($conn,$pid,$user_id);$cid=$post['class_id'];
$ecid=$conn->real_escape_string($cid);$epid=$conn->real_escape_string($pid);
$rows=[];
$res=$conn->query("
  SELECT s.id AS student_id,
         TRIM(CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',s.last_name)) AS full_name,
         s.student_number,s.email
  FROM tblclassenrollment ce
  JOIN tblstudent s ON s.id=ce.student_id AND s.is_deleted=0
  WHERE ce.class_id='$ecid' AND ce.enrollment_status='enrolled'
  ORDER BY s.last_name,s.first_name");
while($res && $r=$res->fetch_assoc()){
  $sid=$conn->real_escape_string($r['student_id']);
  $a=$conn->query("SELECT * FROM tblquizattempt WHERE post_id='$epid' AND student_id='$sid' ORDER BY attempt_number DESC LIMIT 1");
  $latest=$a?$a->fetch_assoc():null;
  $cnt=0;$cr=$conn->query("SELECT COUNT(*) c FROM tblquizattempt WHERE post_id='$epid' AND student_id='$sid'");
  if($cr){$cc=$cr->fetch_assoc();$cnt=(int)$cc['c'];}
  $r['attempt']=$latest;$r['attempt_count']=$cnt;$rows[]=$r;
}
$mqr=$conn->query("SELECT COALESCE(SUM(points),0) AS total FROM tblpostquestion WHERE post_id='$epid' AND is_excluded=0");
$max_score=$mqr?(float)$mqr->fetch_assoc()['total']:0;
ok(['post'=>$post,'students'=>$rows,'max_score'=>$max_score]);