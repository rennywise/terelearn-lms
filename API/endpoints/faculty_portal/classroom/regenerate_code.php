<?php
/**
 * API/facultyUI/classroom/regenerate_code.php
 * Re-rolls the join_code and join_link_token for a class
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

$input      = json_decode(file_get_contents('php://input'), true) ?? [];
$class_id   = trim($input['class_id'] ?? '');
// FIX: Use faculty_id (tblfaculty.id) not user_id (tbluser.id)
$faculty_id = $_SESSION['faculty_id'] ?? $_SESSION['user_id'];

if (!$class_id) { echo json_encode(['status'=>'error','message'=>'class_id required']); exit; }

$s = $conn->prepare("SELECT id FROM tblclass WHERE id=? AND faculty_id=? AND is_deleted=0 LIMIT 1");
$s->bind_param('ss', $class_id, $faculty_id);
$s->execute();
if (!$s->get_result()->fetch_assoc()) {
    echo json_encode(['status'=>'error','message'=>'Access denied']); exit;
}
$s->close();

$newCode  = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 7));
$newToken = bin2hex(random_bytes(24));

$u = $conn->prepare("UPDATE tblclass SET join_code=?, join_link_token=? WHERE id=?");
$u->bind_param('sss', $newCode, $newToken, $class_id);
$u->execute(); $u->close();
$conn->close();

echo json_encode([
    'status'     => 'success',
    'join_code'  => $newCode,
    'join_token' => $newToken,
    'message'    => 'Join code regenerated.',
]);
