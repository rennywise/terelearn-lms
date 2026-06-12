<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    $id = trim($_POST['id'] ?? '');
    $force = trim($_POST['force'] ?? '');
    if (!$id) throw new Exception('ID required');

    $stmt = $conn->prepare("SELECT is_active FROM tblsubject WHERE id=? AND is_deleted=0");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->bind_result($current);
    if (!$stmt->fetch()) throw new Exception('Subject not found');
    $stmt->close();

    if ($force === 'active') {
        $new = 1;
    } elseif ($force === 'inactive') {
        $new = 0;
    } else {
        $new = $current ? 0 : 1;
    }
    $stmt = $conn->prepare("UPDATE tblsubject SET is_active=? WHERE id=?");
    $stmt->bind_param('is', $new, $id);
    if (!$stmt->execute()) throw new Exception('Toggle failed');
    $stmt->close();

    $response = [
        'status'  => 'success',
        'message' => 'Subject ' . ($new ? 'activated' : 'de-activated') . '!',
        'new_is_active' => $new
    ];

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}
echo json_encode($response);
$conn->close();
?>
