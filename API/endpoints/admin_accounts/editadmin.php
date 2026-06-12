<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

try {
    $user_id = trim($_POST['user_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password_raw = trim($_POST['password'] ?? '');
    $user_level_id = (int)($_POST['user_level_id'] ?? 4);

    // Validate required fields
    if (empty($user_id) || empty($email) || empty($username)) {
        echo json_encode(['status' => 'error', 'message' => 'User ID, email, and username are required']);
        exit;
    }

    // Validate email format
    $emailPattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    if (!preg_match($emailPattern, $email)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit;
    }

    // Validate username length
    if (strlen($username) < 4) {
        echo json_encode(['status' => 'error', 'message' => 'Username must be at least 4 characters long']);
        exit;
    }

    // Validate user_level_id (only allow 1 for Admin or 4 for Sub-Admin)
    if ($user_level_id !== 1 && $user_level_id !== 4) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user level']);
        exit;
    }

    // ============ CHECK & UPDATE tbluser ============
    
    // Check if user exists in tbluser
    $check_tbluser_sql = "SELECT id FROM tbluser WHERE id = ? AND is_deleted = 0";
    $stmt_check = $conn->prepare($check_tbluser_sql);
    if (!$stmt_check) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt_check->bind_param("s", $user_id);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found in system']);
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();

    // Check for duplicate email (excluding current user)
    $check_email_sql = "SELECT id FROM tbluser WHERE email = ? AND id != ? AND is_deleted = 0";
    $stmt_check_email = $conn->prepare($check_email_sql);
    if (!$stmt_check_email) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt_check_email->bind_param("ss", $email, $user_id);
    $stmt_check_email->execute();
    if ($stmt_check_email->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        $stmt_check_email->close();
        exit;
    }
    $stmt_check_email->close();

    // Check for duplicate username (excluding current user)
    $check_user_sql = "SELECT id FROM tbluser WHERE username = ? AND id != ? AND is_deleted = 0";
    $stmt_check_user = $conn->prepare($check_user_sql);
    if (!$stmt_check_user) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt_check_user->bind_param("ss", $username, $user_id);
    $stmt_check_user->execute();
    if ($stmt_check_user->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
        $stmt_check_user->close();
        exit;
    }
    $stmt_check_user->close();

    // ============ UPDATE tbluser ============
    if (!empty($password_raw)) {
        // Update with new password
        $password = password_hash($password_raw, PASSWORD_DEFAULT);
        $sql_tbluser = "UPDATE tbluser SET email = ?, username = ?, password = ?, user_level_id = ?, updated_at = NOW() 
                        WHERE id = ? AND is_deleted = 0";
        $stmt = $conn->prepare($sql_tbluser);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ssssi", $email, $username, $password, $user_level_id, $user_id);
    } else {
        // Update without password
        $sql_tbluser = "UPDATE tbluser SET email = ?, username = ?, user_level_id = ?, updated_at = NOW() 
                        WHERE id = ? AND is_deleted = 0";
        $stmt = $conn->prepare($sql_tbluser);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("sssi", $email, $username, $user_level_id, $user_id);
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to update user: ' . $stmt->error);
    }
    
    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    if ($affected_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No changes made']);
    } else {
        $userLevelLabel = ($user_level_id == 1) ? 'Admin' : 'Sub-Admin';
        echo json_encode(['status' => 'success', 'message' => "$userLevelLabel updated successfully!"]);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
