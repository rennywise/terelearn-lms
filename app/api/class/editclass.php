<?php
// ==================== API/class/editclass.php ====================

header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$class_id = trim($_POST['class_id'] ?? '');
$subject_id = trim($_POST['subject_id'] ?? '');
$faculty_id = trim($_POST['faculty_id'] ?? '');
$course_id = trim($_POST['course_id'] ?? '');
$section = trim($_POST['section'] ?? '');
$schedule = trim($_POST['schedule'] ?? '');

if (!$class_id || !$subject_id || !$faculty_id || !$course_id || !$section || !$schedule) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Get subject_code to generate class_code
$subject_sql = "SELECT subject_code FROM tblsubject WHERE id = ? AND is_deleted = 0";
$stmt_subject = $conn->prepare($subject_sql);

if ($stmt_subject === false) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt_subject->bind_param("s", $subject_id);
$stmt_subject->execute();
$subject_result = $stmt_subject->get_result();

if ($subject_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Subject not found']);
    $stmt_subject->close();
    exit;
}

$subject_row = $subject_result->fetch_assoc();
$class_code = $subject_row['subject_code'] . '-' . $section;
$stmt_subject->close();

// Update tblclass - all IDs are strings (UUIDs or numeric strings)
$sql = "UPDATE tblclass 
        SET subject_id = ?, faculty_id = ?, course_id = ?, section = ?, schedule = ?, class_code = ? 
        WHERE id = ?";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    exit;
}

// Bind all as strings
$stmt->bind_param("sssssss", $subject_id, $faculty_id, $course_id, $section, $schedule, $class_code, $class_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Class updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Class not found or no changes made']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
