<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

// Get faculty ID from the URL parameter
$faculty_id = $_GET['id'] ?? '';

if (!$faculty_id) {
    echo json_encode(['status' => 'error', 'message' => 'Faculty ID not specified!']);
    exit;
}

// Prepare the SQL query to mark as deleted
$sql = "UPDATE tblfaculty SET is_deleted = 1 WHERE faculty_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $faculty_id);

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Faculty deleted successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error deleting faculty: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
