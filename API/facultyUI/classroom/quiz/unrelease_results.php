<?php require_once __DIR__.'/_faculty_guard.php';
$pid=trim($_POST['post_id']??'');if(!$pid)fail('post_id required');
require_owns_post($conn,$pid,$user_id);
$s=$conn->prepare("UPDATE tblpost SET results_released_at=NULL WHERE id=?");$s->bind_param('s',$pid);$s->execute();$s->close();
ok(['post_id'=>$pid]);