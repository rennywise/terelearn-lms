<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

try {
    $user_id = trim($_POST['user_id'] ?? '');
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : null;

    // Validate required fields
    if (empty($user_id) || $is_active === null) {
        echo json_encode(['status' => 'error', 'message' => 'User ID and status are required']);
        exit;
    }

    // Ensure is_active is either 0 or 1
    $is_active = ($is_active == 1) ? 1 : 0;

    // Check if user exists and is not deleted
    $check_sql = "SELECT id FROM tbluser WHERE id = ? AND is_deleted = 0";
    $stmt_check = $conn->prepare($check_sql);
    if (!$stmt_check) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt_check->bind_param("s", $user_id);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();

    // Start transaction
    $conn->begin_transaction();

    try {
        // ============ UPDATE tbluser ============
        $sql_tbluser = "UPDATE tbluser SET is_active = ?, updated_at = NOW() WHERE id = ? AND is_deleted = 0";
        $stmt = $conn->prepare($sql_tbluser);

        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }

        // FIXED: bind_param order - type string first, then parameters in SQL order
        $stmt->bind_param("is", $is_active, $user_id);

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $affected_rows_tbluser = $stmt->affected_rows;
        $stmt->close();

        // ============ UPDATE tbladmin ============
        $sql_tbladmin = "UPDATE tbladmin SET is_active = ?, updated_at = NOW() WHERE email = (SELECT email FROM tbluser WHERE id = ?) AND is_deleted = 0";
        $stmt_admin = $conn->prepare($sql_tbladmin);

        if (!$stmt_admin) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }

        $stmt_admin->bind_param("is", $is_active, $user_id);

        if (!$stmt_admin->execute()) {
            throw new Exception('Execute failed: ' . $stmt_admin->error);
        }

        $affected_rows_tbladmin = $stmt_admin->affected_rows;
        $stmt_admin->close();

        // Commit transaction
        $conn->commit();

        if ($affected_rows_tbluser > 0) {
            $statusText = $is_active ? 'activated' : 'deactivated';
            echo json_encode(['status' => 'success', 'message' => "User $statusText successfully!"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No user was updated']);
        }

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
