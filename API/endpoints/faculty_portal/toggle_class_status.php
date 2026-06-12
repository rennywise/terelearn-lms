<?php
/**
 * API/facultyUI/toggle_class_status.php
 * Toggles is_active for a faculty-created class ONLY.
 * Admin-assigned classes cannot be toggled from the faculty side.
 */
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../core/db_connect.php';

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$class_id = trim($data['class_id'] ?? '');

if (!$class_id) {
    echo json_encode(['status' => 'error', 'message' => 'Class ID required.']);
    exit;
}

$user_id = $_SESSION['user_id'];

/* ── 4-strategy faculty lookup ── */
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

/* ── Fetch the class — must belong to this faculty and be faculty-created ── */
$chk = $conn->prepare(
    "SELECT id, is_active, source FROM tblclass
     WHERE id = ? AND faculty_id = ? AND is_deleted = 0 LIMIT 1"
);
$chk->bind_param('ss', $class_id, $faculty_id);
$chk->execute();
$row = $chk->get_result()->fetch_assoc();
$chk->close();

if (!$row) {
    echo json_encode(['status' => 'error', 'message' => 'Class not found or access denied.']);
    exit;
}

if (strtolower($row['source'] ?? 'admin') !== 'faculty') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Admin-assigned classes can only be deactivated from the admin panel.'
    ]);
    exit;
}

/* ── Toggle ── */
$newStatus = $row['is_active'] ? 0 : 1;
$upd = $conn->prepare(
    "UPDATE tblclass SET is_active = ?, updated_at = NOW()
     WHERE id = ? AND faculty_id = ?"
);
$upd->bind_param('iss', $newStatus, $class_id, $faculty_id);
$upd->execute();
$upd->close();
$conn->close();

$label = $newStatus ? 'activated' : 'deactivated';
echo json_encode([
    'status'    => 'success',
    'message'   => "Class {$label} successfully.",
    'is_active' => $newStatus,
]);
