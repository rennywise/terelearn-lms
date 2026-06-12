<?php
/**
 * API/facultyUI/classroom/delete_attachment.php
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

$input      = json_decode(file_get_contents('php://input'), true) ?? [];
$attach_id  = trim($input['attach_id'] ?? '');
$faculty_id = $_SESSION['user_id'];

if (!$attach_id) { echo json_encode(['status'=>'error','message'=>'attach_id required']); exit; }

// Verify ownership via post author
$stmt = $conn->prepare("
    SELECT a.file_path FROM tblpostattachment a
    JOIN tblpost p ON p.id = a.post_id
    WHERE a.id=? AND p.author_id=?
    LIMIT 1
");
$stmt->bind_param('ss', $attach_id, $faculty_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) { echo json_encode(['status'=>'error','message'=>'Not found or access denied.']); exit; }

// Delete physical file if it exists
if ($row['file_path']) {
    $abs = dirname(__DIR__, 5) . '/' . ltrim($row['file_path'], '/\\');
    if (file_exists($abs)) unlink($abs);
}

$del = $conn->prepare("DELETE FROM tblpostattachment WHERE id=?");
$del->bind_param('s', $attach_id);
$del->execute(); $del->close();
$conn->close();

echo json_encode(['status'=>'success','message'=>'Attachment removed.']);
