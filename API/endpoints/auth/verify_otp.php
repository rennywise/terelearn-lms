<?php
/**
 * API/verify_otp.php
 * Verifies the submitted OTP against the hashed value in tbluser.
 *
 * POST body (JSON):
 *   { user_id: "uuid", code: "123456" }
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$body    = json_decode(file_get_contents('php://input'), true);
$user_id = $body['user_id'] ?? null;
$code    = $body['code']    ?? '';

if (!$user_id || strlen($code) !== 6) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT otp_hash, otp_expiry
        FROM tbluser
        WHERE id = ? AND is_deleted = 0 AND is_active = 1
        LIMIT 1
    ");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !$row['otp_hash']) {
        echo json_encode(['success' => false, 'message' => 'No OTP found. Please request a new one.']);
        exit;
    }

    // Check expiry
    if (strtotime($row['otp_expiry']) < time()) {
        echo json_encode(['success' => false, 'message' => 'Code has expired. Please request a new one.']);
        exit;
    }

    // Check code
    if (!password_verify($code, $row['otp_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect code. Please try again.']);
        exit;
    }

    // Invalidate OTP after successful use
    $clr = $conn->prepare("UPDATE tbluser SET otp_hash = NULL, otp_expiry = NULL WHERE id = ?");
    $clr->bind_param('s', $user_id);
    $clr->execute();
    $clr->close();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
