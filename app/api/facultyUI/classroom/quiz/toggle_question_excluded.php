
<?php require_once __DIR__.'/_faculty_guard.php';
$qid=trim($_POST['question_id']??'');$v=(int)($_POST['value']??1);if(!$qid)fail('question_id required');
$qr=$conn->query("SELECT post_id FROM tblpostquestion WHERE id='".$conn->real_escape_string($qid)."' LIMIT 1");
$q=$qr?$qr->fetch_assoc():null;if(!$q)fail('Question not found',404);
$post=require_owns_post($conn,$q['post_id'],$user_id);
$s=$conn->prepare("UPDATE tblpostquestion SET is_excluded=? WHERE id=?");$s->bind_param('is',$v,$qid);$s->execute();$s->close();
recompute_attempt_scores($conn,$q['post_id']);
ok(['question_id'=>$qid,'is_excluded'=>$v]);