<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    $id = trim($_GET['id'] ?? '');
    if (!$id) throw new Exception('ID required');

    $stmt = $conn->prepare("SELECT id,subject_code,subject_name,course_id FROM tblsubject WHERE id=? AND is_deleted=0");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res->num_rows) throw new Exception('Subject not found');
    $response = ['status' => 'success', 'subject' => $res->fetch_assoc()];
    $stmt->close();

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}
echo json_encode($response);
$conn->close();
?>
