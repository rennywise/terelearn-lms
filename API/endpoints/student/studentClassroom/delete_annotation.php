<?php
require_once '_student_guard.php';

$userId = require_student();
$student = get_student_record($conn, $userId);

if (!$student) {
    json_out(['status' => 'error', 'message' => 'Student profile not found.'], 404);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$id = trim($input['annotation_id'] ?? '');

if ($id === '') {
    json_out(['status'=>'error','message'=>'Missing annotation_id.'],400);
}

$stmt = $conn->prepare("SELECT attach_id FROM post_annotations WHERE id=? AND user_id=? AND tab='note' LIMIT 1");
$stmt->bind_param('ss', $id, $userId);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    json_out(['status'=>'error','message'=>'You can only delete your own note.'],403);
}

require_attachment_access($conn, $row['attach_id'], (string)$student['id']);

$stmt = $conn->prepare("DELETE FROM post_annotations WHERE id=? AND user_id=? AND tab='note'");
$stmt->bind_param('ss', $id, $userId);
$stmt->execute();
$stmt->close();

json_out(['status'=>'success']);
?>