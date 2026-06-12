<?php
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || !in_array($level, [2, 3])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

$body       = json_decode(file_get_contents('php://input'), true) ?? [];
$comment_id = trim($body['comment_id'] ?? '');

if (!$comment_id) {
    echo json_encode(['status' => 'error', 'message' => 'comment_id is required']); exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

/* ── Resolve tblfaculty.id from tbluser.id ── */
$faculty_id = null;
if ($level === 2) {
    $uStmt = $conn->prepare("SELECT username, email FROM tbluser WHERE id = ? AND is_deleted = 0 LIMIT 1");
    $uStmt->bind_param('s', $user_id); $uStmt->execute();
    $tblUser = $uStmt->get_result()->fetch_assoc(); $uStmt->close();

    $username = $tblUser['username'] ?? '';
    $email    = $tblUser['email']    ?? '';
    $faculty  = null;

    if (!$faculty && $username) {
        $s = $conn->prepare("SELECT id FROM tblfaculty WHERE username = ? AND is_deleted = 0 LIMIT 1");
        $s->bind_param('s', $username); $s->execute();
        $faculty = $s->get_result()->fetch_assoc(); $s->close();
    }
    if (!$faculty && $email) {
        $s = $conn->prepare("SELECT id FROM tblfaculty WHERE email = ? AND is_deleted = 0 LIMIT 1");
        $s->bind_param('s', $email); $s->execute();
        $faculty = $s->get_result()->fetch_assoc(); $s->close();
    }
    if (!$faculty && isset($_SESSION['faculty_id'])) {
        $s = $conn->prepare("SELECT id FROM tblfaculty WHERE id = ? AND is_deleted = 0 LIMIT 1");
        $s->bind_param('s', $_SESSION['faculty_id']); $s->execute();
        $faculty = $s->get_result()->fetch_assoc(); $s->close();
    }
    $faculty_id = $faculty['id'] ?? null;
}

/* ── Fetch comment + class ── */
$chk = $conn->prepare("
    SELECT c.id, c.user_id, c.user_type, c.post_id, p.class_id
    FROM tblpostcomment c
    JOIN tblpost p ON p.id = c.post_id
    WHERE c.id = ? AND c.is_deleted = 0 LIMIT 1
");
$chk->bind_param("s", $comment_id);
$chk->execute();
$comment = $chk->get_result()->fetch_assoc();
$chk->close();

if (!$comment) {
    echo json_encode(['status' => 'error', 'message' => 'Comment not found']);
    $conn->close(); exit;
}

/* ── Permission check ──
   Faculty: must be the teacher of the class this comment belongs to
   Student: can only delete their own comments
── */
$allowed = false;

if ($level === 2 && $faculty_id) {
    /* Check tblclass.faculty_id = resolved tblfaculty.id */
    $own = $conn->prepare("SELECT id FROM tblclass WHERE id = ? AND faculty_id = ? LIMIT 1");
    $own->bind_param("ss", $comment['class_id'], $faculty_id);
    $own->execute();
    $allowed = (bool)$own->get_result()->fetch_assoc();
    $own->close();
} elseif ($level === 3) {
    $allowed = ($comment['user_id'] === $user_id && $comment['user_type'] === 'student');
}

if (!$allowed) {
    echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
    $conn->close(); exit;
}

$del = $conn->prepare("UPDATE tblpostcomment SET is_deleted = 1 WHERE id = ?");
$del->bind_param("s", $comment_id);
$del->execute();
$del->close();
$conn->close();

echo json_encode(['status' => 'success', 'message' => 'Comment deleted.']);
