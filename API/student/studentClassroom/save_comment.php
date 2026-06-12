<?php
require_once '_student_guard.php';

$userId = require_student();
$student = get_student_record($conn, $userId);

if (!$student) {
    json_out(['status' => 'error', 'message' => 'Student profile not found.'], 404);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$postId = trim($input['post_id'] ?? '');
$text = trim($input['comment_text'] ?? '');

if ($postId === '' || $text === '') {
    json_out(['status'=>'error','message'=>'Missing comment.'],400);
}

require_post_access($conn, $postId, (string)$student['id']);

$id = uuidv4();
$type = 'student';

$stmt = $conn->prepare("INSERT INTO tblpostcomment (id, post_id, user_id, user_type, comment_text) VALUES (?,?,?,?,?)");
$stmt->bind_param('sssss', $id, $postId, $userId, $type, $text);

if (!$stmt->execute()) {
    json_out(['status'=>'error','message'=>'Failed to save comment.'],500);
}

$stmt->close();

json_out([
    'status'=>'success',
    'comment'=>[
        'id'=>$id,
        'post_id'=>$postId,
        'user_id'=>$userId,
        'user_type'=>'student',
        'comment_text'=>$text,
        'author_name'=>student_display_name($student),
        'created_at'=>date('Y-m-d H:i:s'),
        'can_delete'=>true
    ]
]);
?>