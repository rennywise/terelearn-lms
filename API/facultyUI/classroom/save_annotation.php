<?php
/**
 * API/facultyUI/classroom/save_annotation.php
 * POST { attach_id, note_text, tab: 'note'|'comment', page_number? }
 * Saves with user_id = session user — private by design.
 */
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || !in_array($level, [2, 3])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

$body        = json_decode(file_get_contents('php://input'), true) ?? [];
$attach_id   = trim($body['attach_id']  ?? '');
$note_text   = trim($body['note_text']  ?? '');
$tab         = in_array($body['tab'] ?? '', ['note', 'comment']) ? $body['tab'] : 'note';
$page_number = isset($body['page_number']) ? (int)$body['page_number'] : null;

if (!$attach_id) { echo json_encode(['status' => 'error', 'message' => 'attach_id is required.']); exit; }
if (!$note_text) { echo json_encode(['status' => 'error', 'message' => 'Note cannot be empty.']); exit; }
if (mb_strlen($note_text) > 3000) { echo json_encode(['status' => 'error', 'message' => 'Note too long (max 3000 chars).']); exit; }

require_once __DIR__ . '/../../../core/db_connect.php';

// Generate UUID
$new_id = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

$ins = $conn->prepare("
    INSERT INTO tblannotation (id, attach_id, user_id, tab, page_number, note_text)
    VALUES (?, ?, ?, ?, ?, ?)
");
$ins->bind_param("ssssis", $new_id, $attach_id, $user_id, $tab, $page_number, $note_text);

if (!$ins->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save: ' . $ins->error]);
    $ins->close(); $conn->close(); exit;
}
$ins->close();

// Fetch back the saved row
$fetch = $conn->prepare("
    SELECT id, attach_id, tab, page_number, note_text, created_at
    FROM tblannotation
    WHERE id = ? LIMIT 1
");
$fetch->bind_param("s", $new_id);
$fetch->execute();
$annotation = $fetch->get_result()->fetch_assoc();
$fetch->close();
$conn->close();

echo json_encode(['status' => 'success', 'message' => 'Saved.', 'annotation' => $annotation]);
