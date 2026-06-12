<?php
/**
 * API/facultyUI/classroom/save_comment.php
 * POST  JSON { "post_id": "...", "comment_text": "..." }
 *
 * Faculty (level 2)  — must be the author of the class that owns the post
 * Student (level 3)  — must be enrolled (status='enrolled') in that class
 */
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || !in_array($level, [2, 3])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$body    = json_decode(file_get_contents('php://input'), true) ?? [];
$post_id = trim($body['post_id']      ?? '');
$text    = trim($body['comment_text'] ?? '');

if (!$post_id) {
    echo json_encode(['status' => 'error', 'message' => 'post_id is required']);
    exit;
}
if (!$text) {
    echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']);
    exit;
}
if (mb_strlen($text) > 2000) {
    echo json_encode(['status' => 'error', 'message' => 'Comment is too long (max 2000 characters)']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

try {
    // 1. Resolve post → class
    $ps = $pdo->prepare("
        SELECT p.id, p.class_id, p.author_id
        FROM   tblpost p
        WHERE  p.id = :pid AND p.is_deleted = 0
        LIMIT  1
    ");
    $ps->execute([':pid' => $post_id]);
    $post = $ps->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo json_encode(['status' => 'error', 'message' => 'Post not found']);
        exit;
    }

    $class_id  = $post['class_id'];
    $user_type = ($level === 2) ? 'faculty' : 'student';

    // 2. Authorization gate
    if ($level === 3) {
        // Student: must be enrolled
        $enr = $pdo->prepare("
            SELECT id FROM tblclassenrollment
            WHERE  class_id          = :cid
              AND  student_id        = :sid   -- tblstudent.user_id (char36)
              AND  enrollment_status = 'enrolled'
            LIMIT  1
        ");
        $enr->execute([':cid' => $class_id, ':sid' => $user_id]);
        if (!$enr->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'You are not enrolled in this class']);
            exit;
        }
    } else {
        // Faculty: must be the class author (author_id on the post = faculty user_id)
        // tblpost.author_id is a char(36) that should match the faculty's PK or user_id
        // If your tblfaculty uses a different PK column, adjust the comparison below.
        if ($post['author_id'] !== $user_id) {
            // Also allow if faculty owns the class via tblclass.faculty_id
            $own = $pdo->prepare("
                SELECT id FROM tblclass
                WHERE  id         = :cid
                  AND  faculty_id = :fid
                LIMIT  1
            ");
            $own->execute([':cid' => $class_id, ':fid' => $user_id]);
            if (!$own->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'You are not the teacher of this class']);
                exit;
            }
        }
    }

    // 3. Insert comment
    // Generate a UUID v4-like ID without extensions
    $new_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $ins = $pdo->prepare("
        INSERT INTO tblpostcomment (id, post_id, user_id, user_type, comment_text)
        VALUES (:id, :post_id, :user_id, :user_type, :text)
    ");
    $ins->execute([
        ':id'        => $new_id,
        ':post_id'   => $post_id,
        ':user_id'   => $user_id,
        ':user_type' => $user_type,
        ':text'      => $text,
    ]);

    // 4. Return the full comment row with author name (for instant UI append)
    $fetch = $pdo->prepare("
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
               ON  c.user_type = 'faculty' AND c.user_id = f.id
        LEFT  JOIN tblstudent s
               ON  c.user_type = 'student' AND c.user_id = s.user_id
        WHERE c.id = :id
        LIMIT 1
    ");
    $fetch->execute([':id' => $new_id]);
    $comment = $fetch->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Comment posted.',
        'comment' => $comment,
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
}
