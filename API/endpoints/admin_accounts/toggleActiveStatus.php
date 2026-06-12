<?php
// Include database connection
require_once __DIR__ . '/../../core/db_connect.php';

// Get POST data
$faculty_id = $_POST['faculty_id'] ?? '';
$new_status = $_POST['new_status'] ?? '';

// Validate faculty_id and new_status
if (empty($faculty_id) || !in_array($new_status, [0, 1])) {
    echo "Invalid parameters.";
    exit;
}

// Prepare the SQL query to update the 'is_active' field
$sql = "UPDATE tblfaculty SET is_active = ? WHERE faculty_number = ?";
$stmt = $conn->prepare($sql);

// Check if the statement was prepared successfully
if ($stmt === false) {
    echo "Error preparing the query.";
    exit;
}

// Bind parameters: 'is' means integer and string for the respective parameters
$stmt->bind_param("is", $new_status, $faculty_id);

// Execute the query
if ($stmt->execute()) {
    echo "Status updated successfully!";
} else {
    // Log the error to a file (optional)
    error_log("Error updating status: " . $stmt->error);
    echo "Error updating status. Please try again later.";
}

$stmt->close();
$conn->close();
?>
