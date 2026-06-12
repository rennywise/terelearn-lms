<?php
error_reporting(0);
ini_set('display_errors', '0');
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');

function send_u($arr){ ob_end_clean(); echo json_encode($arr); exit; }

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    send_u(['status' => 'error', 'message' => 'Unauthorized']);
}

require_once __DIR__ . '/../../../../core/db_connect.php';

$postId = trim($_POST['post_id'] ?? '');
if ($postId === '') {
    send_u(['status' => 'error', 'message' => 'post_id required']);
}
$hardReset = (int)($_POST['hard_reset'] ?? 1) === 1;

$ePostId = $conn->real_escape_string($postId);

// Verify post exists and is quiz-capable
$rs = $conn->query("
    SELECT p.id, COALESCE(pt.has_quiz,0) AS has_quiz
    FROM tblpost p
    LEFT JOIN tblposttype pt ON pt.id = p.post_type_id
    WHERE p.id = '$ePostId' AND p.is_deleted = 0
    LIMIT 1
");
if (!$rs || !$rs->num_rows) {
    send_u(['status' => 'error', 'message' => 'Post not found']);
}
$row = $rs->fetch_assoc();
if ((int)$row['has_quiz'] !== 1) {
    send_u(['status' => 'error', 'message' => 'Post is not a quiz']);
}

$conn->begin_transaction();
try {
    // Reset publish-related flags on post
    $ok1 = $conn->query("
        UPDATE tblpost
        SET is_published = 0,
            is_force_open = 0,
            is_force_closed = 0,
            live_started_at = NULL,
            live_ended_at = NULL,
            results_released_at = NULL
        WHERE id = '$ePostId'
    ");
    if (!$ok1) throw new Exception('Failed updating post publish state');

    // Reset on tblquiz if linked either by post_id or id style linkage
    $ok2 = $conn->query("
        UPDATE tblquiz
        SET is_published = 0,
            is_force_closed = 0,
            live_started_at = NULL,
            live_ended_at = NULL,
            results_released_at = NULL
        WHERE post_id = '$ePostId' OR id = '$ePostId'
    ");
    if ($ok2 === false) throw new Exception('Failed updating quiz publish state');

    // Optional hard reset: only when explicitly requested by caller.
    if ($hardReset) {
        $ok3 = $conn->query("
            DELETE qa
            FROM tblquizanswer qa
            INNER JOIN tblquizattempt ta ON ta.id = qa.attempt_id
            WHERE ta.post_id = '$ePostId'
        ");
        if ($ok3 === false) throw new Exception('Failed deleting quiz answers');

        $ok4 = $conn->query("DELETE FROM tblquizattempt WHERE post_id = '$ePostId'");
        if ($ok4 === false) throw new Exception('Failed deleting quiz attempts');

        $ok5 = $conn->query("DELETE FROM tblquizenrollment WHERE post_id = '$ePostId'");
        if ($ok5 === false) throw new Exception('Failed deleting quiz enrollments');

        $mkTbl = $conn->query("SHOW TABLES LIKE 'tblquizmakeupaccess'");
        if ($mkTbl && $mkTbl->num_rows > 0) {
            $ok6 = $conn->query("
                UPDATE tblquizmakeupaccess
                SET is_active = 0, consumed_at = NOW()
                WHERE post_id = '$ePostId' AND is_active = 1
            ");
            if ($ok6 === false) throw new Exception('Failed clearing make-up access');
        }
    }

    $conn->commit();
    send_u([
        'status' => 'success',
        'message' => $hardReset ? 'Quiz unpublished and records reset' : 'Quiz unpublished',
        'post_id' => $postId
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    send_u(['status' => 'error', 'message' => $e->getMessage()]);
}
