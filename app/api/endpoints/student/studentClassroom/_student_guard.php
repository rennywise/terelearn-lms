<?php
if (ob_get_level() === 0) {
    ob_start();
}
session_start();
require_once __DIR__ . '/../../../core/db_connect.php';

function json_out($arr, $code = 200) {
    http_response_code($code);
    echo json_encode($arr);
    exit;
}

function require_student() {
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
        json_out(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }
    return (string)$_SESSION['user_id'];
}

function uuidv4() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function table_has_column($conn, $table, $column) {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $columnEsc = $conn->real_escape_string($column);
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$columnEsc'");
    return $res && $res->num_rows > 0;
}

function get_student_record($conn, $userId) {
    if (table_has_column($conn, 'tblstudent', 'user_id')) {
        $stmt = $conn->prepare("SELECT s.*, c.course_code, c.course_name FROM tblstudent s LEFT JOIN tblcourse c ON c.id=s.course_id WHERE s.user_id=? AND s.is_deleted=0 LIMIT 1");
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $s = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($s) return $s;
    }

    $stmt = $conn->prepare("SELECT email, username FROM tbluser WHERE id=? AND is_deleted=0 LIMIT 1");
    $stmt->bind_param('s', $userId);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$u) return null;

    $stmt = $conn->prepare("SELECT s.*, c.course_code, c.course_name FROM tblstudent s LEFT JOIN tblcourse c ON c.id=s.course_id WHERE (s.username=? OR s.email=?) AND s.is_deleted=0 LIMIT 1");
    $stmt->bind_param('ss', $u['username'], $u['email']);
    $stmt->execute();
    $s = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $s ?: null;
}

function require_enrolled($conn, $classId, $studentId, $studentDbId = '') {
    $stmt = $conn->prepare("
        SELECT id
        FROM tblclassenrollment
        WHERE class_id = ?
          AND enrollment_status = 'enrolled'
          AND (student_id = ? OR student_id = ?)
        LIMIT 1
    ");

    if (!$stmt) {
        json_out(['status' => 'error', 'message' => 'Enrollment check failed: ' . $conn->error], 500);
    }

    $studentDbId = $studentDbId !== '' ? $studentDbId : $studentId;
    $stmt->bind_param('sss', $classId, $studentId, $studentDbId);
    $stmt->execute();
    $ok = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if (!$ok) {
        json_out(['status' => 'error', 'message' => 'You are not enrolled in this class.'], 403);
    }
}

function require_post_access($conn, $postId, $studentId) {
    $stmt = $conn->prepare("SELECT p.class_id, p.submission_mode FROM tblpost p INNER JOIN tblclassenrollment e ON e.class_id=p.class_id AND e.student_id=? AND e.enrollment_status='enrolled' WHERE p.id=? AND p.is_deleted=0 LIMIT 1");
    $stmt->bind_param('ss', $studentId, $postId);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$post) json_out(['status' => 'error', 'message' => 'Post not found.'], 404);

    if (($post['submission_mode'] ?? 'individual') === 'group') {
        $stmt = $conn->prepare("SELECT id FROM tblpostgroups WHERE post_id=? AND student_id=? LIMIT 1");
        $stmt->bind_param('ss', $postId, $studentId);
        $stmt->execute();
        $ok = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$ok) {
            json_out(['status' => 'error', 'message' => 'This group activity is not assigned to you.'], 403);
        }
    }

    return $post;
}

function require_attachment_access($conn, $attachId, $studentId) {
    $stmt = $conn->prepare("SELECT a.id, a.post_id FROM tblpostattachment a INNER JOIN tblpost p ON p.id=a.post_id INNER JOIN tblclassenrollment e ON e.class_id=p.class_id AND e.student_id=? AND e.enrollment_status='enrolled' WHERE a.id=? AND p.is_deleted=0 LIMIT 1");
    $stmt->bind_param('ss', $studentId, $attachId);
    $stmt->execute();
    $a = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$a) json_out(['status' => 'error', 'message' => 'Attachment not found.'], 404);

    require_post_access($conn, $a['post_id'], $studentId);
    return $a;
}

function student_display_name($student) {
    $name = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
    return $name !== '' ? $name : 'Student';
}
?>
