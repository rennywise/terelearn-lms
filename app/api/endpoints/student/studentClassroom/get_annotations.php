<?php
require_once '_student_guard.php';

$userId = require_student();
$student = get_student_record($conn, $userId);

if (!$student) {
    json_out(['status' => 'error', 'message' => 'Student profile not found.'], 404);
}

$attachId = trim($_GET['attach_id'] ?? '');
$page = isset($_GET['page_number']) && $_GET['page_number'] !== '' ? (int)$_GET['page_number'] : null;

if ($attachId === '') {
    json_out(['status'=>'error','message'=>'Missing attach_id.'],400);
}

require_attachment_access($conn, $attachId, (string)$student['id']);

if ($page === null) {
    $stmt = $conn->prepare("SELECT * FROM post_annotations WHERE attach_id=? AND user_id=? AND tab='note' AND page_number IS NULL ORDER BY created_at ASC");
    $stmt->bind_param('ss', $attachId, $userId);
} else {
    $stmt = $conn->prepare("SELECT * FROM post_annotations WHERE attach_id=? AND user_id=? AND tab='note' AND page_number=? ORDER BY created_at ASC");
    $stmt->bind_param('ssi', $attachId, $userId, $page);
}

$stmt->execute();

$res = $stmt->get_result();
$annotations = [];

while ($r = $res->fetch_assoc()) {
    $annotations[] = $r;
}

$stmt->close();

json_out([
    'status'=>'success',
    'annotations'=>$annotations
]);
?>