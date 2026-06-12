<?php
/* API/edit_program.php — updates an existing program */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$id   = intval($_POST['program_id'] ?? 0);
$code = trim($_POST['course_code']  ?? '');
$name = trim($_POST['course_name']  ?? '');

if (!$id || !$code || !$name) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

// Check duplicate code on a different record
$check = $conn->prepare("SELECT id FROM tblcourse WHERE course_code = ? AND is_deleted = 0 AND id != ?");
$check->bind_param('si', $code, $id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => "Program code '{$code}' is already used by another program."]);
    $check->close(); $conn->close(); exit;
}
$check->close();

$stmt = $conn->prepare("UPDATE tblcourse SET course_code = ?, course_name = ? WHERE id = ? AND is_deleted = 0");
$stmt->bind_param('ssi', $code, $name, $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => "Program updated successfully."]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No changes made or program not found.']);
}

$stmt->close();
$conn->close();
