<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$class_id = trim($input['class_id'] ?? '');
$user_id = $_SESSION['user_id'];

if ($class_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing class ID']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE tblclassstudents
     SET is_archived = 0
     WHERE class_id = ?
       AND student_id = ?
       AND is_deleted = 0
       AND is_archived = 1"
);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param('ss', $class_id, $user_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();
$conn->close();

if ($affected > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Class restored']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Class not found or already active']);
}
