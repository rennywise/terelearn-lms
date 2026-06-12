<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

try {
    $class_id  = trim($_POST['class_id'] ?? '');
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : null;

    if (!$class_id || $is_active === null) {
        echo json_encode(['status'=>'error','message'=>'Class ID and status required']);
        exit;
    }

    $sql = "UPDATE tblclass SET is_active = ?, updated_at = NOW() WHERE id = ? AND is_deleted = 0";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("is", $is_active, $class_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        $statusText = $is_active ? 'activated' : 'deactivated';
        echo json_encode(['status'=>'success','message'=>"Class $statusText successfully!"]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Class not found']);
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>'Error: ' . $e->getMessage()]);
}
?>
