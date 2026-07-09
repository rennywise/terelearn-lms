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
    json_out(['status' => 'error', 'message' => 'Missing private comment.'], 400);
}

require_post_access($conn, $postId, (string)$student['id']);

$conn->query("
    CREATE TABLE IF NOT EXISTS tblpostprivatecomment (
        id VARCHAR(36) NOT NULL PRIMARY KEY,
        post_id VARCHAR(36) NOT NULL,
        student_id VARCHAR(36) NOT NULL,
        user_id VARCHAR(36) NOT NULL,
        user_type VARCHAR(20) NOT NULL DEFAULT 'student',
        comment_text TEXT NOT NULL,
        is_deleted TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_private_comment_post_student (post_id, student_id, is_deleted, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$id = uuidv4();
$type = 'student';

$stmt = $conn->prepare("
    INSERT INTO tblpostprivatecomment (id, post_id, student_id, user_id, user_type, comment_text)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param('ssssss', $id, $postId, $student['id'], $userId, $type, $text);

if (!$stmt->execute()) {
    json_out(['status' => 'error', 'message' => 'Failed to save private comment.'], 500);
}

$stmt->close();

json_out([
    'status' => 'success',
    'comment' => [
        'id' => $id,
        'post_id' => $postId,
        'student_id' => $student['id'],
        'user_id' => $userId,
        'user_type' => 'student',
        'comment_text' => $text,
        'author_name' => student_display_name($student),
        'created_at' => date('Y-m-d H:i:s'),
        'can_delete' => true
    ]
]);
?>
