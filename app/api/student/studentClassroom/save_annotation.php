<?php
require_once '_student_guard.php';

$userId = require_student();
$student = get_student_record($conn, $userId);

if (!$student) {
    json_out(['status' => 'error', 'message' => 'Student profile not found.'], 404);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$attachId = trim($input['attach_id'] ?? '');
$text = trim($input['note_text'] ?? '');
$page = isset($input['page_number']) && $input['page_number'] !== '' ? (int)$input['page_number'] : null;

if ($attachId === '' || $text === '') {
    json_out(['status'=>'error','message'=>'Missing note.'],400);
}

require_attachment_access($conn, $attachId, (string)$student['id']);

$tab = 'note';

if ($page === null) {
    $stmt = $conn->prepare("INSERT INTO post_annotations (attach_id, user_id, tab, page_number, note_text) VALUES (?,?,?,NULL,?)");
    $stmt->bind_param('ssss', $attachId, $userId, $tab, $text);
} else {
    $stmt = $conn->prepare("INSERT INTO post_annotations (attach_id, user_id, tab, page_number, note_text) VALUES (?,?,?,?,?)");
    $stmt->bind_param('sssis', $attachId, $userId, $tab, $page, $text);
}

if (!$stmt->execute()) {
    json_out([
        'status'=>'error',
        'message'=>'Failed to save note. Run database_fix_for_private_notes.sql if this mentions attach_id type.'
    ],500);
}

$id = $conn->insert_id;
$stmt->close();

json_out([
    'status'=>'success',
    'annotation'=>[
        'id'=>$id,
        'attach_id'=>$attachId,
        'user_id'=>$userId,
        'tab'=>'note',
        'page_number'=>$page,
        'note_text'=>$text,
        'created_at'=>date('Y-m-d H:i:s')
    ]
]);
?>