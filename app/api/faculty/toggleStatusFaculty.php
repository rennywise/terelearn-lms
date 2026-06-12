<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

// Get faculty_number from GET or POST
$faculty_number = trim($_GET['faculty_number'] ?? $_POST['faculty_number'] ?? '');

if (empty($faculty_number)) {
    echo json_encode(['status' => 'error', 'message' => 'Faculty number is required!']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get current status and UUID
    $check_sql = "SELECT id, is_active FROM tblfaculty WHERE faculty_number = ? AND is_deleted = 0";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("s", $faculty_number);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Faculty member not found!');
    }
    
    $row = $result->fetch_assoc();
    $faculty_id = $row['id'];
    $current_status = $row['is_active'];
    $stmt_check->close();
    
    // Toggle status (1 becomes 0, 0 becomes 1)
    $new_status = $current_status ? 0 : 1;
    
    // Update tblfaculty
    $update_faculty = "UPDATE tblfaculty SET is_active = ? WHERE faculty_number = ?";
    $stmt_faculty = $conn->prepare($update_faculty);
    $stmt_faculty->bind_param("is", $new_status, $faculty_number);
    
    if (!$stmt_faculty->execute()) {
        throw new Exception('Failed to update faculty status: ' . $stmt_faculty->error);
    }
    $stmt_faculty->close();
    
    // Update tbluser (using the same UUID as id)
    $update_user = "UPDATE tbluser SET is_active = ? WHERE id = ?";
    $stmt_user = $conn->prepare($update_user);
    $stmt_user->bind_param("is", $new_status, $faculty_id);
    
    if (!$stmt_user->execute()) {
        throw new Exception('Failed to update user status: ' . $stmt_user->error);
    }
    $stmt_user->close();
    
    // Commit transaction
    $conn->commit();
    
    $statusText = $new_status ? 'activated' : 'de-activated';
echo json_encode([
    'status'  => 'success',
    'message' => 'Faculty member ' . $statusText . ' successfully!'
]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();


?>
