<?php
/**
 * API/facultyUI/get_class_detail.php
 * Returns full detail of a single class — only if it belongs to the logged-in faculty.
 */
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../core/db_connect.php';

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$class_id = trim($_GET['id'] ?? '');
if (!$class_id) {
    echo json_encode(['status' => 'error', 'message' => 'Class ID required.']);
    exit;
}

$user_id = $_SESSION['user_id'];

/* ── Resolve faculty_id (same 4-strategy chain) ── */
$uStmt = $conn->prepare(
    "SELECT username, email FROM tbluser WHERE id = ? AND is_deleted = 0 LIMIT 1"
);
$uStmt->bind_param('s', $user_id);
$uStmt->execute();
$tblUser = $uStmt->get_result()->fetch_assoc();
$uStmt->close();

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
    $col = $conn->query(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblfaculty' AND COLUMN_NAME = 'user_id' LIMIT 1"
    );
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
    exit;
}
$faculty_id = $faculty['id'];

/* ── Fetch class (only if owned by this faculty) ── */
$stmt = $conn->prepare("
    SELECT
        c.*,
        s.subject_code, s.subject_name,
        co.course_code, co.course_name
    FROM tblclass c
    LEFT JOIN tblsubject s  ON s.id  = c.subject_id
    LEFT JOIN tblcourse  co ON co.id = c.course_id
    WHERE c.id = ?
      AND c.faculty_id = ?
      AND c.is_deleted = 0
    LIMIT 1
");
$stmt->bind_param('ss', $class_id, $faculty_id);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$class) {
    echo json_encode(['status' => 'error', 'message' => 'Class not found or access denied.']);
    exit;
}

echo json_encode(['status' => 'success', 'class' => $class]);
