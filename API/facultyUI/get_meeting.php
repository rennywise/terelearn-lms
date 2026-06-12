<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

$class_id = trim($_GET['class_id'] ?? '');
if (!$class_id) {
    echo json_encode(['status' => 'error', 'message' => 'class_id required']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT meeting_code, meet_url FROM tblmeeting WHERE class_id = ? LIMIT 1"
);
$stmt->bind_param('s', $class_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$row || !$row['meet_url']) {
    echo json_encode(['status' => 'none', 'message' => 'No meeting set']);
    exit;
}

echo json_encode([
    'status'       => 'success',
    'meeting_code' => $row['meeting_code'],
    'meet_url'     => $row['meet_url'],
]);
