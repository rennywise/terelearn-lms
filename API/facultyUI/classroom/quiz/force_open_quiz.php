<?php require_once __DIR__.'/_faculty_guard.php';
$pid=trim($_POST['post_id']??'');$v=(int)($_POST['value']??1);if(!$pid)fail('post_id required');
require_owns_post($conn,$pid,$user_id);
$s=$conn->prepare("UPDATE tblpost SET is_force_open=?,is_force_closed=0 WHERE id=?");$s->bind_param('is',$v,$pid);$s->execute();$s->close();
ok(['post_id'=>$pid,'is_force_open'=>$v]);