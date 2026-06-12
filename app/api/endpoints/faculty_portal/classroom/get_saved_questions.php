<?php
session_start();
header('Content-Type: application/json');

$level = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$userId = $_SESSION['user_id'] ?? '';
$classId = trim($_GET['class_id'] ?? '');

if (!$userId || $level !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($classId === '') {
    echo json_encode(['status' => 'error', 'message' => 'class_id is required']);
    exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';
require_once __DIR__ . '/saved_questions_helper.php';

$conn->set_charset('utf8mb4');
sqb_sync_existing_posts($conn, $classId, $userId);
$sources = sqb_fetch_sources($conn, $classId, $userId);
$total = 0;
foreach ($sources as $source) {
    $total += (int)$source['question_count'];
}

$conn->close();

echo json_encode([
    'status' => 'success',
    'total_questions' => $total,
    'sources' => $sources,
]);
