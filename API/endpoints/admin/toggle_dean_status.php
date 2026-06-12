<?php
/**
 * API/Admin/toggle_dean_status.php
 * Toggles is_active on tbladmin. Also mirrors to tbluser if exists.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';
$data     = json_decode(file_get_contents('php://input'), true) ?? [];
$id       = trim($data['id']        ?? '');
$isActive = (int)($data['is_active'] ?? 0);
if (!$id) { echo json_encode(['status'=>'error','message'=>'Missing ID.']); exit; }
try {
    $stmt = $conn->prepare("UPDATE tbladmin SET is_active=? WHERE id=?");
    $stmt->bind_param('is', $isActive, $id);
    $stmt->execute();
    // Mirror to tbluser if exists
    $aRes = $conn->prepare("SELECT email, username FROM tbladmin WHERE id=? LIMIT 1");
    $aRes->bind_param('s', $id); $aRes->execute();
    $a = $aRes->get_result()->fetch_assoc();
    if ($a) {
        $stmt2 = $conn->prepare("UPDATE tbluser SET is_active=? WHERE (email=? OR username=?) AND is_deleted=0 AND user_level_id <> 3");
        $stmt2->bind_param('iss', $isActive, $a['email'], $a['username']);
        $stmt2->execute();
    }
    $label = $isActive ? 'activated' : 'deactivated';
    echo json_encode(['status'=>'success','message'=>"Account $label successfully."]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
$conn->close();
