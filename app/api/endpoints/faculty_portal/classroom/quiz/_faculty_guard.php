<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../core/db_connect.php';
function fail($m,$c=400){http_response_code($c);echo json_encode(['status'=>'error','message'=>$m]);exit;}
function ok($d=[]){echo json_encode(array_merge(['status'=>'success'],$d));exit;}
function uuid4(){return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));}
$user_id=$_SESSION['user_id']??'';$lvl=(int)($_SESSION['user_level_id']??0);
if(!$user_id||$lvl!==2)fail('Unauthorized',401);
function require_owns_post(mysqli $conn,string $post_id,string $user_id):array{
  $s=$conn->prepare("SELECT * FROM tblpost WHERE id=? AND author_id=? AND is_deleted=0 LIMIT 1");
  $s->bind_param('ss',$post_id,$user_id);$s->execute();$r=$s->get_result()->fetch_assoc();$s->close();
  if(!$r)fail('Post not found or not yours',404);
  return $r;
}
function recompute_attempt_scores(mysqli $conn,string $post_id):void{
  // Recompute auto_score and final_score for ALL attempts of this quiz
  // accounting for excluded questions and per-answer overrides
  $qs=[];$qr=$conn->query("SELECT id,points,is_excluded FROM tblpostquestion WHERE post_id='".$conn->real_escape_string($post_id)."'");
  if($qr)while($r=$qr->fetch_assoc())$qs[$r['id']]=$r;
  $max=0;foreach($qs as $q)if(!$q['is_excluded'])$max+=(float)$q['points'];
  $ar=$conn->query("SELECT id,manual_score FROM tblquizattempt WHERE post_id='".$conn->real_escape_string($post_id)."'");
  $attempts=[];if($ar)while($r=$ar->fetch_assoc())$attempts[]=$r;
  foreach($attempts as $a){
    $aid=$conn->real_escape_string($a['id']);$auto=0;
    $anr=$conn->query("SELECT question_id,is_correct,is_correct_override FROM tblquizanswer WHERE attempt_id='$aid'");
    if($anr)while($an=$anr->fetch_assoc()){
      $qid=$an['question_id'];if(!isset($qs[$qid])||$qs[$qid]['is_excluded'])continue;
      $eff=$an['is_correct_override']!==null?(int)$an['is_correct_override']:(int)$an['is_correct'];
      if($eff===1)$auto+=(float)$qs[$qid]['points'];
    }
    $final=$a['manual_score']!==null?(float)$a['manual_score']:$auto;
    $u=$conn->prepare("UPDATE tblquizattempt SET auto_score=?,final_score=?,max_score=? WHERE id=?");
    $u->bind_param('ddds',$auto,$final,$max,$a['id']);$u->execute();$u->close();
    // Update points_awarded per answer
    $anr2=$conn->query("SELECT id,question_id,is_correct,is_correct_override FROM tblquizanswer WHERE attempt_id='$aid'");
    if($anr2)while($an=$anr2->fetch_assoc()){
      $qid=$an['question_id'];$pts=0;
      if(isset($qs[$qid])&&!$qs[$qid]['is_excluded']){
        $eff=$an['is_correct_override']!==null?(int)$an['is_correct_override']:(int)$an['is_correct'];
        if($eff===1)$pts=(float)$qs[$qid]['points'];
      }
      $up=$conn->prepare("UPDATE tblquizanswer SET points_awarded=? WHERE id=?");
      $up->bind_param('ds',$pts,$an['id']);$up->execute();$up->close();
    }
  }
}
