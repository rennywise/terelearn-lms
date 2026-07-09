<?php
require_once '_student_guard.php';

$userId = require_student();
$student = get_student_record($conn, $userId);

if (!$student) {
    json_out(['status' => 'error', 'message' => 'Student profile not found.'], 404);
}

$postId = trim($_GET['post_id'] ?? '');

if ($postId === '') {
    json_out(['status' => 'error', 'message' => 'Missing post_id.'], 400);
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

$stmt = $conn->prepare("
    SELECT
        pc.id,
        pc.post_id,
        pc.student_id,
        pc.user_id,
        pc.user_type,
        pc.comment_text,
        pc.created_at,
        CASE
            WHEN pc.user_type = 'student' THEN TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')))
            ELSE TRIM(CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, '')))
        END AS author_name
    FROM tblpostprivatecomment pc
    LEFT JOIN tbluser u ON u.id = pc.user_id
    LEFT JOIN tblstudent s ON pc.user_type = 'student' AND (s.id = pc.student_id OR s.email = u.email OR s.username = u.username)
    LEFT JOIN tblfaculty f ON pc.user_type = 'faculty' AND f.id = pc.user_id
    WHERE pc.post_id = ?
      AND pc.student_id = ?
      AND pc.is_deleted = 0
    ORDER BY pc.created_at ASC
");
$stmt->bind_param('ss', $postId, $student['id']);
$stmt->execute();

$res = $stmt->get_result();
$comments = [];

while ($row = $res->fetch_assoc()) {
    $row['author_name'] = trim($row['author_name'] ?? '') ?: ($row['user_type'] === 'faculty' ? 'Faculty' : student_display_name($student));
    $row['can_delete'] = ($row['user_type'] === 'student' && $row['user_id'] === $userId);
    $comments[] = $row;
}

$stmt->close();

json_out([
    'status' => 'success',
    'comments' => $comments
]);
?>
