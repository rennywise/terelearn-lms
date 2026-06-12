<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    $id = trim($_POST['id'] ?? '');
    if (!$id) throw new Exception('ID required');

    $stmt = $conn->prepare("UPDATE tblsubject SET is_deleted=1 WHERE id=? AND is_deleted=0");
    $stmt->bind_param('s', $id);
    if (!$stmt->execute() || $stmt->affected_rows === 0) throw new Exception('Delete failed');
    $stmt->close();

    $response = ['status' => 'success', 'message' => 'Subject deleted successfully!'];

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}
echo json_encode($response);
$conn->close();
?>
