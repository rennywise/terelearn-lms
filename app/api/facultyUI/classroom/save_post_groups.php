<?php
/**
 * API/facultyUI/classroom/save_post_groups.php
 */
header('Content-Type: application/json');
session_start();
$level = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
if (!isset($_SESSION['user_id']) || $level !== 2) {
    http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}
require_once __DIR__ . '/../../../core/db_connect.php';
$conn->query("CREATE TABLE IF NOT EXISTS tblpostgroups (
    id INT NOT NULL AUTO_INCREMENT, post_id VARCHAR(255) NOT NULL,
    group_number INT NOT NULL, student_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_post (post_id), KEY idx_post_grp (post_id, group_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$chk=$conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tblpost' AND COLUMN_NAME='submission_mode' LIMIT 1");
if($chk&&$chk->num_rows===0) $conn->query("ALTER TABLE tblpost ADD COLUMN submission_mode VARCHAR(20) NOT NULL DEFAULT 'individual' AFTER points");
$body=json_decode(file_get_contents('php://input'),true)?:[];
$post_id=trim($body['post_id']??'');
$submission_mode=trim($body['submission_mode']??'individual');
$groups=$body['groups']??[];
if(!$post_id){echo json_encode(['status'=>'error','message'=>'post_id required.']);exit;}
$spid=mysqli_real_escape_string($conn,$post_id);
$smode=mysqli_real_escape_string($conn,$submission_mode);
$conn->query("UPDATE tblpost SET submission_mode='$smode' WHERE id='$spid'");
$conn->query("DELETE FROM tblpostgroups WHERE post_id='$spid'");
if($submission_mode==='group'&&!empty($groups)){
    $stmt=$conn->prepare("INSERT INTO tblpostgroups (post_id,group_number,student_id) VALUES (?,?,?)");
    foreach($groups as $g){
        $gn=(int)($g['group_number']??0);
        foreach(($g['student_ids']??[]) as $sid){
            $sid=trim($sid); if(!$sid)continue;
            $stmt->bind_param('sis',$post_id,$gn,$sid); $stmt->execute();
        }
    }
    $stmt->close();
}
$conn->close();
echo json_encode(['status'=>'success','message'=>'Groups saved.']);
