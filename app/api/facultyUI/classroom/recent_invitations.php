<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/db.php';

$faculty_id = (int)($_SESSION['user_id'] ?? 0);
$class_id   = trim($_GET['class_id'] ?? '');

if (!$faculty_id || !$class_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']); exit;
}

try {
    // Last 5 distinct students this faculty invited (any class), excluding ones
    // already enrolled / invited / requested in the current class
    $sql = "
      SELECT DISTINCT s.student_id AS id,
             CONCAT(s.last_name, ', ', s.first_name,
                    IF(s.middle_name <> '', CONCAT(' ', LEFT(s.middle_name,1), '.'), '')) AS full_name,
             s.student_number, s.course_code, s.year_level, s.section,
             MAX(i.created_at) AS last_invited
        FROM tblinvitations i
        JOIN tblstudents     s ON s.student_id = i.student_id
        JOIN tblclasses      c ON c.class_id   = i.class_id
       WHERE c.faculty_id = ?
         AND i.class_id  <> ?
         AND s.student_id NOT IN (
              SELECT student_id FROM tblclassenrollment WHERE class_id = ?
              UNION
              SELECT student_id FROM tblinvitations    WHERE class_id = ? AND status = 'pending'
         )
       GROUP BY s.student_id
       ORDER BY last_invited DESC
       LIMIT 5";
    $st = $pdo->prepare($sql);
    $st->execute([$faculty_id, $class_id, $class_id, $class_id]);
    echo json_encode(['status' => 'success', 'students' => $st->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
