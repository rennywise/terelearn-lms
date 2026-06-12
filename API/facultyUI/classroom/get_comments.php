<?php
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || !in_array($level, [2, 3])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

$post_id = trim($_GET['post_id'] ?? '');
if (!$post_id) {
    echo json_encode(['status' => 'error', 'message' => 'post_id is required']); exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

$ps = $conn->prepare("SELECT id, class_id FROM tblpost WHERE id = ? AND is_deleted = 0 LIMIT 1");
$ps->bind_param("s", $post_id);
$ps->execute();
$post = $ps->get_result()->fetch_assoc();
$ps->close();

if (!$post) {
    echo json_encode(['status' => 'error', 'message' => 'Post not found']);
    $conn->close(); exit;
}

$class_id = $post['class_id'];

if ($level === 3) {
    $enr = $conn->prepare("SELECT id FROM tblclassenrollment WHERE class_id = ? AND student_id = ? AND enrollment_status = 'enrolled' LIMIT 1");
    $enr->bind_param("ss", $class_id, $user_id);
    $enr->execute();
    $enrolled = $enr->get_result()->fetch_assoc();
    $enr->close();
    if (!$enrolled) {
        echo json_encode(['status' => 'error', 'message' => 'Not enrolled in this class']);
        $conn->close(); exit;
    }
}

$stmt = $conn->prepare("
    SELECT c.id, c.post_id, c.user_id, c.user_type, c.comment_text, c.created_at,
        CASE
            WHEN c.user_type = 'faculty'
                THEN TRIM(CONCAT(COALESCE(f.first_name,''),' ',COALESCE(f.last_name,'')))
            ELSE TRIM(CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')))
        END AS full_name
    FROM tblpostcomment c
    LEFT JOIN tblfaculty f ON c.user_type = 'faculty' AND c.user_id = f.id
    LEFT JOIN tblstudent s ON c.user_type = 'student' AND c.user_id = s.user_id
    WHERE c.post_id = ? AND c.is_deleted = 0
    ORDER BY c.created_at ASC
");
$stmt->bind_param("s", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = [];
while ($row = $result->fetch_assoc()) $comments[] = $row;
$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'comments' => $comments]);
