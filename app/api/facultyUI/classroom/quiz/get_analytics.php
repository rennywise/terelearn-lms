<?php require_once __DIR__.'/_faculty_guard.php';
$pid=trim($_GET['post_id']??'');if(!$pid)fail('post_id required');
$post=require_owns_post($conn,$pid,$user_id);
$epid=$conn->real_escape_string($pid);$ecid=$conn->real_escape_string($post['class_id']);
$enr=0;$er=$conn->query("SELECT COUNT(*) c FROM tblclassenrollment WHERE class_id='$ecid' AND enrollment_status='enrolled'");if($er){$x=$er->fetch_assoc();$enr=(int)$x['c'];}
// Latest attempt per student
$lat=[];$lr=$conn->query("
  SELECT a.* FROM tblquizattempt a
  INNER JOIN (SELECT student_id,MAX(attempt_number) mx FROM tblquizattempt WHERE post_id='$epid' GROUP BY student_id) m
  ON a.student_id=m.student_id AND a.attempt_number=m.mx WHERE a.post_id='$epid' AND a.status<>'in_progress'");
while($lr && $r=$lr->fetch_assoc())$lat[]=$r;
$sub=count($lat);
$scores=array_map(fn($a)=>(float)$a['final_score'],$lat);
$max=array_map(fn($a)=>(float)$a['max_score'],$lat);
$pcts=[];for($i=0;$i<$sub;$i++)$pcts[]=$max[$i]>0?($scores[$i]/$max[$i]*100):0;
sort($pcts);
$avgPct=$sub?array_sum($pcts)/$sub:0;$hi=$sub?max($pcts):0;$lo=$sub?min($pcts):0;
$med=0;if($sub){$mid=intdiv($sub,2);$med=$sub%2?$pcts[$mid]:(($pcts[$mid-1]+$pcts[$mid])/2);}
$thr=$post['passing_threshold']!==null?(float)$post['passing_threshold']:null;
$pass=null;if($thr!==null&&$sub){$p=count(array_filter($pcts,fn($x)=>$x>=$thr));$pass=$p/$sub*100;}
$avgRaw=$sub?array_sum($scores)/$sub:0;
// Per-question correct %
$pq=[];$qr=$conn->query("SELECT id,question,order_num,is_excluded FROM tblpostquestion WHERE post_id='$epid' ORDER BY order_num");
while($qr && $q=$qr->fetch_assoc()){
  $qid=$conn->real_escape_string($q['id']);$tot=0;$cor=0;
  foreach($lat as $a){$aid=$conn->real_escape_string($a['id']);
    $ar=$conn->query("SELECT is_correct,is_correct_override FROM tblquizanswer WHERE attempt_id='$aid' AND question_id='$qid' LIMIT 1");
    if($ar && $an=$ar->fetch_assoc()){$tot++;$eff=$an['is_correct_override']!==null?(int)$an['is_correct_override']:(int)$an['is_correct'];if($eff===1)$cor++;}
  }
  $q['answered']=$tot;$q['correct']=$cor;$q['pct']=$tot?($cor/$tot*100):0;$pq[]=$q;
}
// Histogram (10-pct buckets)
$hist=array_fill(0,10,0);foreach($pcts as $p){$b=min(9,(int)floor($p/10));$hist[$b]++;}
// Time spent average (seconds)
$times=[];foreach($lat as $a){if($a['submitted_at']&&$a['started_at']){$d=strtotime($a['submitted_at'])-strtotime($a['started_at']);if($d>0)$times[]=$d;}}
$timeAvg=count($times)?array_sum($times)/count($times):0;
ok(['enrolled'=>$enr,'submissions'=>$sub,'avg_pct'=>$avgPct,'avg_raw'=>$avgRaw,'high'=>$hi,'low'=>$lo,'median'=>$med,'pass_rate'=>$pass,'threshold'=>$thr,'per_question'=>$pq,'histogram'=>$hist,'time_avg_seconds'=>$timeAvg]);