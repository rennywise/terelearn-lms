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

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$body    = json_decode(file_get_contents('php://input'), true);
$user_id = $body['user_id'] ?? null;
$code    = $body['code']    ?? '';
$context = $body['context'] ?? 'login';

define('RECOVERY_MAX_OTP_ATTEMPTS', 5);

if (!$user_id || strlen($code) !== 6) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

try {
    if ($context === 'recovery') {
        $recoveryState = $_SESSION['recovery_otp'][$user_id] ?? ['attempts' => 0, 'locked_until' => 0];
        if (!empty($recoveryState['locked_until']) && $recoveryState['locked_until'] > time()) {
            echo json_encode([
                'success' => false,
                'session_expired' => true,
                'message' => 'Recovery session expired. Please request a new code.'
            ]);
            exit;
        }
    }

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
        if ($context === 'recovery') {
            $recoveryState['attempts'] = ($recoveryState['attempts'] ?? 0) + 1;

            if ($recoveryState['attempts'] >= RECOVERY_MAX_OTP_ATTEMPTS) {
                $recoveryState['locked_until'] = time() + 300;
                $_SESSION['recovery_otp'][$user_id] = $recoveryState;

                $clr = $conn->prepare("UPDATE tbluser SET otp_hash = NULL, otp_expiry = NULL WHERE id = ?");
                $clr->bind_param('s', $user_id);
                $clr->execute();
                $clr->close();

                echo json_encode([
                    'success' => false,
                    'session_expired' => true,
                    'message' => 'Too many incorrect attempts. Recovery session expired. Please request a new code.'
                ]);
                exit;
            }

            $_SESSION['recovery_otp'][$user_id] = $recoveryState;

            echo json_encode([
                'success' => false,
                'remaining_attempts' => RECOVERY_MAX_OTP_ATTEMPTS - $recoveryState['attempts'],
                'message' => 'Incorrect code. Please try again.'
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Incorrect code. Please try again.']);
        exit;
    }

    // Invalidate OTP after successful use
    $clr = $conn->prepare("UPDATE tbluser SET otp_hash = NULL, otp_expiry = NULL WHERE id = ?");
    $clr->bind_param('s', $user_id);
    $clr->execute();
    $clr->close();

    if ($context === 'recovery') {
        unset($_SESSION['recovery_otp'][$user_id]);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
