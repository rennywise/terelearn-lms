<?php
/**
 * API/facultyUI/create_meeting.php
 * Saves or updates a faculty-supplied Google Meet URL for a class.
 */
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

$input    = json_decode(file_get_contents('php://input'), true) ?: [];
$class_id = trim($input['class_id'] ?? '');
$meet_url = trim($input['meet_url'] ?? '');

if (!$class_id) {
    echo json_encode(['status' => 'error', 'message' => 'class_id required']);
    exit;
}

/* FIX: Remove the space after the slash in URL validation */
if ($meet_url !== '' && !str_starts_with($meet_url, 'https://meet.google.com/')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Google Meet URL']);
    exit;
}

function uuidv4(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));
}

/* Check if a record already exists for this class */
$chk = $conn->prepare("SELECT id FROM tblmeeting WHERE class_id = ? LIMIT 1");
$chk->bind_param('s', $class_id);
$chk->execute();
$existing = $chk->get_result()->fetch_assoc();
$chk->close();

/* FIX: If meet_url is empty, DELETE the record instead of updating with empty values */
if ($meet_url === '') {
    if ($existing) {
        $del = $conn->prepare("DELETE FROM tblmeeting WHERE class_id = ?");
        $del->bind_param('s', $class_id);
        $del->execute();
        $del->close();
    }
    $conn->close();
    echo json_encode(['status' => 'success', 'message' => 'Meeting link removed']);
    exit;
}

/* Extract the room code e.g. vwa-yagu-ifu */
$path = parse_url($meet_url, PHP_URL_PATH);
$meeting_code = ltrim($path, '/');
$meeting_code = explode('?', $meeting_code)[0];

if ($existing) {
    $upd = $conn->prepare(
        "UPDATE tblmeeting SET meeting_code = ?, meet_url = ? WHERE class_id = ?"
    );
    $upd->bind_param('sss', $meeting_code, $meet_url, $class_id);
    if (!$upd->execute()) {
        echo json_encode(['status' => 'error', 'message' => $upd->error]);
        $upd->close(); $conn->close(); exit;
    }
    $upd->close();
} else {
    $newId = uuidv4();
    $ins = $conn->prepare(
        "INSERT INTO tblmeeting (id, class_id, meeting_code, meet_url) VALUES (?,?,?,?)"
    );
    $ins->bind_param('ssss', $newId, $class_id, $meeting_code, $meet_url);
    if (!$ins->execute()) {
        echo json_encode(['status' => 'error', 'message' => $ins->error]);
        $ins->close(); $conn->close(); exit;
    }
    $ins->close();
}

$conn->close();

echo json_encode([
    'status'       => 'success',
    'meeting_code' => $meeting_code,
    'meet_url'     => $meet_url,
]);
