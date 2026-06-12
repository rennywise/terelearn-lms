<?php
/**
 * API/facultyUI/classroom/get_annotations.php
 * GET ?attach_id=&tab=note|comment&page_number=
 * Returns only THIS user's rows — 100% private per user.
 */
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || !in_array($level, [2, 3])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

$attach_id   = trim($_GET['attach_id'] ?? '');
$tab         = in_array($_GET['tab'] ?? '', ['note', 'comment']) ? $_GET['tab'] : 'note';
$page_number = isset($_GET['page_number']) ? (int)$_GET['page_number'] : null;

if (!$attach_id) {
    echo json_encode(['status' => 'error', 'message' => 'attach_id is required.']); exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

if ($page_number !== null) {
    $stmt = $conn->prepare("
        SELECT id, attach_id, tab, page_number, note_text, created_at
        FROM tblannotation
        WHERE attach_id   = ?
          AND user_id     = ?
          AND tab         = ?
          AND page_number = ?
        ORDER BY created_at ASC
    ");
    $stmt->bind_param("sssi", $attach_id, $user_id, $tab, $page_number);
} else {
    $stmt = $conn->prepare("
        SELECT id, attach_id, tab, page_number, note_text, created_at
        FROM tblannotation
        WHERE attach_id = ?
          AND user_id   = ?
          AND tab       = ?
        ORDER BY created_at ASC
    ");
    $stmt->bind_param("sss", $attach_id, $user_id, $tab);
}

$stmt->execute();
$result      = $stmt->get_result();
$annotations = [];
while ($row = $result->fetch_assoc()) {
    $annotations[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'annotations' => $annotations]);
