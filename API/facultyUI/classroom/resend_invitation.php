<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}
require_once __DIR__ . '/../../../core/db_connect.php';

$body       = json_decode(file_get_contents('php://input'), true) ?? [];
$class_id   = trim($body['class_id']   ?? '');
$student_id = trim($body['student_id'] ?? '');
$faculty_id = $_SESSION['faculty_id'] ?? $_SESSION['user_id'];

if (!$class_id || !$student_id) {
    echo json_encode(['status'=>'error','message'=>'Missing data']); exit;
}

$ec = $conn->real_escape_string($class_id);
$es = $conn->real_escape_string($student_id);
$ef = $conn->real_escape_string($faculty_id);

$chk = $conn->query("SELECT id FROM tblclass WHERE id='$ec' AND faculty_id='$ef' AND is_deleted=0 LIMIT 1");
if (!$chk || !$chk->num_rows) { echo json_encode(['status'=>'error','message'=>'Access denied']); exit; }

$conn->query("UPDATE tblinvitations
              SET invitation_status='pending', invited_at=NOW(), responded_at=NULL
              WHERE class_id='$ec' AND student_id='$es'");

/* Refresh notification */
$nid = bin2hex(random_bytes(16));
$conn->query("INSERT IGNORE INTO tblnotifications (id, user_id, type, class_id, message, is_read, created_at)
              SELECT '$nid', s.user_id, 'invitation', '$ec',
                     'You have been re-invited to join a class.', 0, NOW()
              FROM tblstudent s WHERE s.id='$es' AND s.user_id IS NOT NULL LIMIT 1");

$conn->close();
echo json_encode(['status'=>'success','message'=>'Invitation resent.']);
