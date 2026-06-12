<?php
/**
 * API/facultyUI/classroom/delete_annotation.php
 * POST { annotation_id }
 * Only deletes if the row belongs to the session user — server enforced.
 */
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || !in_array($level, [2, 3])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

$body          = json_decode(file_get_contents('php://input'), true) ?? [];
$annotation_id = trim($body['annotation_id'] ?? '');

if (!$annotation_id) {
    echo json_encode(['status' => 'error', 'message' => 'annotation_id is required.']); exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

// WHERE user_id = session user prevents anyone deleting someone else's note
$stmt = $conn->prepare("DELETE FROM tblannotation WHERE id = ? AND user_id = ?");
$stmt->bind_param("ss", $annotation_id, $user_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();
$conn->close();

if ($affected === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Not found or access denied.']); exit;
}

echo json_encode(['status' => 'success', 'message' => 'Deleted.']);
