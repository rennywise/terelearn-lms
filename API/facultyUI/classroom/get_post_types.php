<?php
/**
 * API/facultyUI/classroom/get_post_types.php
 * Returns system presets + this faculty's custom types
 */
session_start();
header('Content-Type: application/json');

$level = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
if (!isset($_SESSION['user_id']) || $level !== 2) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';
$faculty_id = trim((string)($_SESSION['faculty_id'] ?? ''));
if ($faculty_id === '') {
    $user_id = (string)$_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT f.id
        FROM tblfaculty f
        INNER JOIN tbluser u ON u.id = ?
        WHERE f.is_deleted = 0
          AND u.is_deleted = 0
          AND (f.username = u.username OR f.email = u.email)
        LIMIT 1
    ");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $faculty = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $faculty_id = (string)($faculty['id'] ?? $user_id);
}

$stmt = $conn->prepare("
    SELECT id, faculty_id, type_key, type_label, icon,
           color_bg, color_text, is_gradable, has_quiz, has_file, sort_order
    FROM   tblposttype
    WHERE  is_deleted = 0
      AND  (faculty_id IS NULL OR faculty_id = ?)
    ORDER  BY sort_order ASC, type_label ASC
");
$stmt->bind_param('s', $faculty_id);
$stmt->execute();
$types = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

echo json_encode(['status'=>'success','types'=>$types]);
