<?php
/**
 * API/facultyUI/classroom/get_post_groups.php
 * GET ?post_id=UUID
 * Returns groups with student names for a post.
 */
header('Content-Type: application/json');
session_start();
$level=(int)($_SESSION['user_level_id']??$_SESSION['user_type_id']??0);
if(!isset($_SESSION['user_id'])||$level!==2){http_response_code(401);echo json_encode(['status'=>'error','message'=>'Unauthorized']);exit;}
require_once __DIR__ . '/../../../core/db_connect.php';
$post_id=trim($_GET['post_id']??'');
if(!$post_id){echo json_encode(['status'=>'error','message'=>'post_id required.']);exit;}
$spid=mysqli_real_escape_string($conn,$post_id);
/* Get submission_mode */
$mr=$conn->query("SELECT submission_mode FROM tblpost WHERE id='$spid' LIMIT 1");
$mode=($mr&&$mr->num_rows>0)?$mr->fetch_assoc()['submission_mode']:'individual';
/* Get groups with names */
$res=$conn->query("
    SELECT pg.group_number, pg.student_id,
           CONCAT(UPPER(s.last_name),', ',s.first_name,' ',IFNULL(CONCAT(LEFT(s.middle_name,1),'.'),'')) AS display_name
    FROM tblpostgroups pg
    LEFT JOIN tblstudent s ON s.id=pg.student_id
    WHERE pg.post_id='$spid'
    ORDER BY pg.group_number ASC, s.last_name ASC
");
$groups=[];
if($res){
    while($row=$res->fetch_assoc()){
        $gn=(int)$row['group_number'];
        if(!isset($groups[$gn]))$groups[$gn]=[];
        $groups[$gn][]=['student_id'=>$row['student_id'],'display_name'=>trim($row['display_name'])];
    }
}
$conn->close();
echo json_encode(['status'=>'success','submission_mode'=>$mode,'groups'=>$groups]);
