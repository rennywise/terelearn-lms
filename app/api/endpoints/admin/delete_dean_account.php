<?php
/**
 * API/Admin/delete_dean_account.php
 * Soft-deletes the tbladmin account and cascades to tbluser + assignments.
 * Optionally removes from tblfaculty if they were also a professor.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';
$data = json_decode(file_get_contents('php://input'), true) ?? [];
$id   = trim($data['id'] ?? '');
if (!$id) { echo json_encode(['status'=>'error','message'=>'Missing ID.']); exit; }
try {
    // Get email/username first
    $aRes = $conn->prepare("SELECT email, username FROM tbladmin WHERE id=? LIMIT 1");
    $aRes->bind_param('s', $id); $aRes->execute();
    $a = $aRes->get_result()->fetch_assoc();

    // Soft-delete tbladmin
    $stmt = $conn->prepare("UPDATE tbladmin SET is_deleted=1, is_active=0 WHERE id=?");
    $stmt->bind_param('s', $id); $stmt->execute();

    if ($a) {
        // Soft-delete tbluser
        $stmt2 = $conn->prepare("UPDATE tbluser SET is_deleted=1, is_active=0 WHERE (email=? OR username=?) AND is_deleted=0 AND user_level_id <> 3");
        $stmt2->bind_param('ss', $a['email'], $a['username']); $stmt2->execute();

        // Deactivate all assignments
        $stmt3 = $conn->prepare("UPDATE tbldeanassignment SET is_active=0 WHERE faculty_id=?");
        $stmt3->bind_param('s', $id); $stmt3->execute();

        // Remove dean flag from tblfaculty (but keep faculty record — they may still teach)
        $stmt4 = $conn->prepare("UPDATE tblfaculty SET is_dean=0 WHERE (email=? OR username=?) AND is_deleted=0");
        $stmt4->bind_param('ss', $a['email'], $a['username']); $stmt4->execute();
    }

    echo json_encode(['status'=>'success','message'=>'Account deleted and all assignments removed.']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
$conn->close();
