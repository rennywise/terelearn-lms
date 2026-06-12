<?php
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || !in_array($level, [2, 3])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

$body    = json_decode(file_get_contents('php://input'), true) ?? [];
$post_id = trim($body['post_id']      ?? '');
$text    = trim($body['comment_text'] ?? '');

if (!$post_id) { echo json_encode(['status'=>'error','message'=>'post_id is required']); exit; }
if (!$text)    { echo json_encode(['status'=>'error','message'=>'Comment cannot be empty']); exit; }
if (mb_strlen($text) > 2000) { echo json_encode(['status'=>'error','message'=>'Comment too long (max 2000 chars)']); exit; }

require_once __DIR__ . '/../../../core/db_connect.php';

/* ── 1. Get post ── */
$ps = $conn->prepare("SELECT id, class_id, author_id FROM tblpost WHERE id = ? AND is_deleted = 0 LIMIT 1");
$ps->bind_param("s", $post_id);
$ps->execute();
$post = $ps->get_result()->fetch_assoc();
$ps->close();

if (!$post) {
    echo json_encode(['status' => 'error', 'message' => 'Post not found']);
    $conn->close(); exit;
}

$class_id  = $post['class_id'];
$user_type = ($level === 2) ? 'faculty' : 'student';

/* ── 2. Auth ── */
if ($level === 3) {
    /* Student: check enrollment */
    $enr = $conn->prepare("
        SELECT id FROM tblclassenrollment
        WHERE class_id = ? AND student_id = ? AND enrollment_status = 'enrolled'
        LIMIT 1
    ");
    $enr->bind_param("ss", $class_id, $user_id);
    $enr->execute();
    $enrolled = $enr->get_result()->fetch_assoc();
    $enr->close();

    if (!$enrolled) {
        echo json_encode(['status' => 'error', 'message' => 'You are not enrolled in this class']);
        $conn->close(); exit;
    }

    /* For insert, student user_id stays as tbluser.id (matches tblstudent.user_id) */
    $insert_user_id = $user_id;

} else {
    /* Faculty: resolve tblfaculty.id from tbluser.id */
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
    if (!$faculty) {
        $col = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblfaculty'
            AND COLUMN_NAME = 'user_id' LIMIT 1");
        if ($col && $col->num_rows > 0) {
            $s = $conn->prepare("SELECT id FROM tblfaculty WHERE user_id = ? AND is_deleted = 0 LIMIT 1");
            $s->bind_param('s', $user_id); $s->execute();
            $faculty = $s->get_result()->fetch_assoc(); $s->close();
        }
    }
    if (!$faculty && isset($_SESSION['faculty_id'])) {
        $s = $conn->prepare("SELECT id FROM tblfaculty WHERE id = ? AND is_deleted = 0 LIMIT 1");
        $s->bind_param('s', $_SESSION['faculty_id']); $s->execute();
        $faculty = $s->get_result()->fetch_assoc(); $s->close();
    }

    if (!$faculty) {
        echo json_encode(['status' => 'error', 'message' => 'Faculty profile not found.']);
        $conn->close(); exit;
    }

    $faculty_id = $faculty['id']; // ← real tblfaculty.id

    /* Verify faculty owns or is assigned to this class */
    $own = $conn->prepare("SELECT id FROM tblclass WHERE id = ? AND faculty_id = ? AND is_deleted = 0 LIMIT 1");
    $own->bind_param("ss", $class_id, $faculty_id);
    $own->execute();
    $owns = $own->get_result()->fetch_assoc();
    $own->close();

    if (!$owns) {
        echo json_encode(['status' => 'error', 'message' => 'You are not the teacher of this class']);
        $conn->close(); exit;
    }

    /* For insert, store tblfaculty.id so the fetch JOIN works correctly */
    $insert_user_id = $faculty_id;
}

/* ── 3. UUID ── */
$new_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
    mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
    mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
);

/* ── 4. Insert ── */
$ins = $conn->prepare("INSERT INTO tblpostcomment (id, post_id, user_id, user_type, comment_text) VALUES (?, ?, ?, ?, ?)");
$ins->bind_param("sssss", $new_id, $post_id, $insert_user_id, $user_type, $text);
if (!$ins->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save: ' . $ins->error]);
    $ins->close(); $conn->close(); exit;
}
$ins->close();

/* ── 5. Fetch inserted row with name ── */
$fetch = $conn->prepare("
    SELECT c.id, c.post_id, c.user_id, c.user_type, c.comment_text, c.created_at,
        CASE
            WHEN c.user_type = 'faculty'
                THEN TRIM(CONCAT(COALESCE(f.first_name,''),' ',COALESCE(f.last_name,'')))
            ELSE TRIM(CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')))
        END AS full_name
    FROM tblpostcomment c
    LEFT JOIN tblfaculty f ON c.user_type = 'faculty' AND c.user_id = f.id
    LEFT JOIN tblstudent s ON c.user_type = 'student' AND c.user_id = s.user_id
    WHERE c.id = ? LIMIT 1
");
$fetch->bind_param("s", $new_id);
$fetch->execute();
$comment = $fetch->get_result()->fetch_assoc();
$fetch->close();
$conn->close();

echo json_encode(['status' => 'success', 'message' => 'Comment posted.', 'comment' => $comment]);
