<?php
/**
 * API/facultyUI/classroom/delete_class.php
 * POST  JSON { "class_id": "UUID" }
 *
 * Hard-deletes the class and all related rows:
 *   tblpost, tblpostattachment, tblpostquestion, tblpostchoice,
 *   tblpostcomment, tblclassenrollment, tblclass
 *
 * Only the faculty who owns the class may delete it.
 *
 * ⚠️  Adjust table/column names to match your actual schema.
 */
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || $level !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$class_id = trim($body['class_id'] ?? '');

if (!$class_id) {
    echo json_encode(['status' => 'error', 'message' => 'class_id is required']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

try {
    // Verify ownership
    $chk = $pdo->prepare("SELECT id FROM tblclass WHERE id = :cid AND faculty_id = :fid LIMIT 1");
    $chk->execute([':cid' => $class_id, ':fid' => $user_id]);
    if (!$chk->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Class not found or access denied']);
        exit;
    }

    $pdo->beginTransaction();

    // 1. Get all post IDs for this class
    $postIds = $pdo->prepare("SELECT id FROM tblpost WHERE class_id = :cid");
    $postIds->execute([':cid' => $class_id]);
    $ids = $postIds->fetchAll(PDO::FETCH_COLUMN);

    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // 2. Delete quiz choices
        $pdo->prepare("DELETE FROM tblpostchoice WHERE question_id IN (
            SELECT id FROM tblpostquestion WHERE post_id IN ($placeholders)
        )")->execute($ids);

        // 3. Delete quiz questions
        $pdo->prepare("DELETE FROM tblpostquestion WHERE post_id IN ($placeholders)")->execute($ids);

        // 4. Delete attachments
        $pdo->prepare("DELETE FROM tblpostattachment WHERE post_id IN ($placeholders)")->execute($ids);

        // 5. Delete comments
        $pdo->prepare("DELETE FROM tblpostcomment WHERE post_id IN ($placeholders)")->execute($ids);

        // 6. Delete posts
        $pdo->prepare("DELETE FROM tblpost WHERE class_id = ?")->execute([$class_id]);
    }

    // 7. Delete enrollments
    $pdo->prepare("DELETE FROM tblclassenrollment WHERE class_id = ?")->execute([$class_id]);

    // 8. Delete the class itself
    $pdo->prepare("DELETE FROM tblclass WHERE id = ?")->execute([$class_id]);

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Class deleted successfully.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
}
