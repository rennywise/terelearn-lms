<?php
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);

$id           = isset($body['id']) ? (int)$body['id'] : 0;
$courseId     = isset($body['course_id']) ? (int)$body['course_id'] : 0;
$yearLevel    = isset($body['year_level']) ? (int)$body['year_level'] : 0;
$sectionCount = isset($body['section_count']) ? (int)$body['section_count'] : 0;

if (!$courseId || !$yearLevel || $sectionCount < 1 || $sectionCount > 30) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    exit;
}

if ($id) {
    $stmt = $conn->prepare("UPDATE tblyearsectionconfig SET section_count = ?, is_deleted = 0 WHERE id = ?");
    $stmt->bind_param('ii', $sectionCount, $id);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Config updated.']);
    $conn->close();
    exit;
}

$check = $conn->prepare("SELECT id, is_deleted FROM tblyearsectionconfig WHERE course_id = ? AND year_level = ? LIMIT 1");
$check->bind_param('ii', $courseId, $yearLevel);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    $stmt = $conn->prepare("UPDATE tblyearsectionconfig SET section_count = ?, is_deleted = 0 WHERE id = ?");
    $stmt->bind_param('ii', $sectionCount, $existing['id']);
    $stmt->execute();

    echo json_encode([
        'status' => 'success',
        'message' => ((int)($existing['is_deleted'] ?? 0) === 1)
            ? 'Config restored and updated.'
            : 'Config updated (already existed).'
    ]);
} else {
    $stmt = $conn->prepare("INSERT INTO tblyearsectionconfig (course_id, year_level, section_count) VALUES (?, ?, ?)");
    $stmt->bind_param('iii', $courseId, $yearLevel, $sectionCount);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Config saved.', 'id' => $conn->insert_id]);
}

$conn->close();
