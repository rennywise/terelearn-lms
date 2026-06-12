<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

try {
    $user_id = trim($_POST['user_id'] ?? '');

    // Validate required field
    if (empty($user_id)) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        exit;
    }

    // Check if user exists and is not already deleted
    $check_sql = "SELECT id FROM tbluser WHERE id = ? AND is_deleted = 0";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("s", $user_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();

    // Soft delete - mark as deleted
    $sql = "UPDATE tbluser SET is_deleted = 1, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $user_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to delete user: ' . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No user was deleted']);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
