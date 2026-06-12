<?php
require_once '_student_guard.php';

$userId = require_student();
$student = get_student_record($conn, $userId);

if (!$student) {
    json_out(['status' => 'error', 'message' => 'Student profile not found.'], 404);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$commentId = trim($input['comment_id'] ?? '');

if ($commentId === '') {
    json_out(['status'=>'error','message'=>'Missing comment_id.'],400);
}

$stmt = $conn->prepare("SELECT post_id FROM tblpostcomment WHERE id=? AND user_id=? AND user_type='student' AND is_deleted=0 LIMIT 1");
$stmt->bind_param('ss', $commentId, $userId);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    json_out(['status'=>'error','message'=>'You can only delete your own comment.'],403);
}

require_post_access($conn, $row['post_id'], (string)$student['id']);

$stmt = $conn->prepare("UPDATE tblpostcomment SET is_deleted=1 WHERE id=? AND user_id=? AND user_type='student'");
$stmt->bind_param('ss', $commentId, $userId);
$stmt->execute();
$stmt->close();

json_out(['status'=>'success']);
?>