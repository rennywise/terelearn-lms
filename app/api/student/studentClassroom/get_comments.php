<?php
require_once '_student_guard.php';

$userId = require_student();
$student = get_student_record($conn, $userId);

if (!$student) {
    json_out(['status' => 'error', 'message' => 'Student profile not found.'], 404);
}

$postId = trim($_GET['post_id'] ?? '');

if ($postId === '') {
    json_out(['status'=>'error','message'=>'Missing post_id.'],400);
}

require_post_access($conn, $postId, (string)$student['id']);

$stmt = $conn->prepare("SELECT pc.*, CASE WHEN pc.user_type='student' THEN TRIM(CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,''))) ELSE TRIM(CONCAT(COALESCE(f.first_name,''),' ',COALESCE(f.last_name,''))) END AS author_name FROM tblpostcomment pc LEFT JOIN tbluser u ON u.id=pc.user_id LEFT JOIN tblstudent s ON pc.user_type='student' AND (s.email=u.email OR s.username=u.username) LEFT JOIN tblfaculty f ON pc.user_type='faculty' AND f.id=pc.user_id WHERE pc.post_id=? AND pc.is_deleted=0 ORDER BY pc.created_at ASC");
$stmt->bind_param('s', $postId);
$stmt->execute();

$res = $stmt->get_result();
$comments = [];

while ($r = $res->fetch_assoc()) {
    $r['can_delete'] = ($r['user_type'] === 'student' && $r['user_id'] === $userId);
    $r['author_name'] = trim($r['author_name'] ?? '') ?: ($r['user_type'] === 'faculty' ? 'Faculty' : 'Student');
    $comments[] = $r;
}

$stmt->close();

json_out([
    'status'=>'success',
    'comments'=>$comments
]);
?>