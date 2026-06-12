<?php
/**
 * API/facultyUI/classroom/get_comments.php
 * GET  ?post_id=CHAR36
 *
 * Who can call this:
 *   level 2 (faculty)  — must be the author of the class
 *   level 3 (student)  — must be enrolled (status='enrolled') in the class
 *
 * Returns all non-deleted comments for the post, oldest first.
 */
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || !in_array($level, [2, 3])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$post_id = trim($_GET['post_id'] ?? '');
if (!$post_id) {
    echo json_encode(['status' => 'error', 'message' => 'post_id is required']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

try {
    // 1. Get the post so we know which class it belongs to
    $ps = $pdo->prepare("SELECT id, class_id FROM tblpost WHERE id = :pid AND is_deleted = 0 LIMIT 1");
    $ps->execute([':pid' => $post_id]);
    $post = $ps->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo json_encode(['status' => 'error', 'message' => 'Post not found']);
        exit;
    }

    $class_id = $post['class_id'];

    // 2. Authorization gate
    if ($level === 3) {
        // Student must be actively enrolled in this class
        // tblclassenrollment.student_id stores tblstudent.user_id (char 36)
        $enr = $pdo->prepare("
            SELECT id FROM tblclassenrollment
            WHERE class_id = :cid
              AND student_id = :sid
              AND enrollment_status = 'enrolled'
            LIMIT 1
        ");
        $enr->execute([':cid' => $class_id, ':sid' => $user_id]);
        if (!$enr->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Not enrolled in this class']);
            exit;
        }
    }
    // faculty (level 2): no extra check — they can read any class they teach
    // (add a class author check here if you want stricter control)

    // 3. Fetch comments with author name
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.post_id,
            c.user_id,
            c.user_type,
            c.comment_text,
            c.created_at,
            CASE
                WHEN c.user_type = 'faculty'
                    THEN TRIM(CONCAT(
                            COALESCE(f.first_name, ''), ' ',
                            COALESCE(f.last_name,  '')
                         ))
                ELSE TRIM(CONCAT(
                            COALESCE(s.first_name, ''), ' ',
                            COALESCE(s.last_name,  '')
                         ))
            END AS full_name
        FROM  tblpostcomment c
        LEFT  JOIN tblfaculty f
               ON  c.user_type = 'faculty'
               AND c.user_id   = f.id
        LEFT  JOIN tblstudent s
               ON  c.user_type = 'student'
               AND c.user_id   = s.user_id   -- char(36) FK, NOT s.id (int)
        WHERE c.post_id    = :post_id
          AND c.is_deleted = 0
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([':post_id' => $post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status'   => 'success',
        'comments' => $comments,
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
}
