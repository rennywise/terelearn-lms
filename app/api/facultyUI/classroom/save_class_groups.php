<?php
/**
 * API/facultyUI/classroom/save_class_groups.php
 * Saves persistent class-level groups.
 *
 * POST JSON:
 * {
 *   "class_id": "uuid",
 *   "groups": [
 *     { "group_number": 1, "student_ids": ["id1","id2"] },
 *     { "group_number": 2, "student_ids": ["id3","id4"] }
 *   ]
 * }
 */
header('Content-Type: application/json');
session_start();
$level=(int)($_SESSION['user_level_id']??$_SESSION['user_type_id']??0);
if(!isset($_SESSION['user_id'])||$level!==2){http_response_code(401);echo json_encode(['status'=>'error','message'=>'Unauthorized']);exit;}
require_once __DIR__ . '/../../../core/db_connect.php';
$conn->query("CREATE TABLE IF NOT EXISTS tblclassgroups (
    id INT NOT NULL AUTO_INCREMENT, class_id VARCHAR(255) NOT NULL,
    group_number INT NOT NULL, student_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_class (class_id), KEY idx_class_grp (class_id, group_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$body=json_decode(file_get_contents('php://input'),true)?:[];
$class_id=trim($body['class_id']??'');
$groups=$body['groups']??[];
if(!$class_id){echo json_encode(['status'=>'error','message'=>'class_id required.']);exit;}
$scid=mysqli_real_escape_string($conn,$class_id);
$conn->query("DELETE FROM tblclassgroups WHERE class_id='$scid'");
if(!empty($groups)){
    $stmt=$conn->prepare("INSERT INTO tblclassgroups (class_id,group_number,student_id) VALUES (?,?,?)");
    foreach($groups as $g){
        $gn=(int)($g['group_number']??0);
        foreach(($g['student_ids']??[]) as $sid){
            $sid=trim($sid);if(!$sid)continue;
            $stmt->bind_param('sis',$class_id,$gn,$sid);$stmt->execute();
        }
    }
    $stmt->close();
}
$conn->close();
echo json_encode(['status'=>'success','message'=>'Class groups saved.']);
