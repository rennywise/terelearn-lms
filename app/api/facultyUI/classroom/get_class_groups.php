<?php
/**
 * API/facultyUI/classroom/get_class_groups.php
 * GET ?class_id=UUID
 * Returns persistent class groups with student names.
 */
header('Content-Type: application/json');
session_start();
$level=(int)($_SESSION['user_level_id']??$_SESSION['user_type_id']??0);
if(!isset($_SESSION['user_id'])||$level!==2){http_response_code(401);echo json_encode(['status'=>'error','message'=>'Unauthorized']);exit;}
require_once __DIR__ . '/../../../core/db_connect.php';
$class_id=trim($_GET['class_id']??'');
if(!$class_id){echo json_encode(['status'=>'error','message'=>'class_id required.']);exit;}
$scid=mysqli_real_escape_string($conn,$class_id);
$res=$conn->query("
    SELECT cg.group_number, cg.student_id,
           CONCAT(UPPER(s.last_name),', ',s.first_name,' ',IFNULL(CONCAT(LEFT(s.middle_name,1),'.'),'')) AS display_name
    FROM tblclassgroups cg
    LEFT JOIN tblstudent s ON s.id=cg.student_id
    WHERE cg.class_id='$scid'
    ORDER BY cg.group_number ASC, s.last_name ASC
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
$has_groups=!empty($groups);
echo json_encode(['status'=>'success','has_groups'=>$has_groups,'groups'=>$groups]);
