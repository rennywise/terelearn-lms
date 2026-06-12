<?php
/* API/Admin/delete_admin_account.php */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../core/db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$id    = $input['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Account ID is required.']);
    exit;
}

// Block deleting the currently logged-in admin account.
$sessionUserId = $_SESSION['user_id'] ?? null;
if ($sessionUserId) {
    $self = $conn->prepare("
        SELECT a.id
        FROM tbladmin a
        JOIN tbluser u ON u.is_deleted = 0
        WHERE u.id = ?
          AND a.is_deleted = 0
          AND (a.email = u.email OR a.username = u.username)
        LIMIT 1
    ");
    $self->bind_param('s', $sessionUserId);
    $self->execute();
    $selfRow = $self->get_result()->fetch_assoc();
    $self->close();

    if ($selfRow && $selfRow['id'] === $id) {
        echo json_encode(['status' => 'error', 'message' => 'You cannot delete the account currently logged in.']);
        exit;
    }
}

// Safety: don't allow deleting the last active super admin
$safeCheck = $conn->query(
    "SELECT COUNT(*) AS cnt FROM tbladmin WHERE is_superadmin = 1 AND is_deleted = 0 AND is_active = 1"
)->fetch_assoc();

$targetRow = $conn->prepare(
    "SELECT is_superadmin FROM tbladmin WHERE id = ? AND is_deleted = 0"
);
$targetRow->bind_param('s', $id);
$targetRow->execute();
$target = $targetRow->get_result()->fetch_assoc();
$targetRow->close();

if (!$target) {
    echo json_encode(['status' => 'error', 'message' => 'Account not found.']);
    exit;
}

if ($target['is_superadmin'] && (int)$safeCheck['cnt'] <= 1) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot delete the last super admin account.']);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE tbladmin SET is_deleted = 1, is_active = 0, updated_at = NOW() WHERE id = ?"
);
$stmt->bind_param('s', $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Admin account deleted.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Delete failed or account not found.']);
}
$stmt->close();
