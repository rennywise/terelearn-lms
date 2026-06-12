<?php
/**
 * API/otp_debug.php
 * TEMPORARY — visit this URL to see exactly what the OTP API returns.
 * DELETE after fixing.
 *
 * Visit: http://localhost/finalbackup1/TERELEARN/API/otp_debug.php
 */

// Simulate a POST to toggle_otp_auth.php and show raw output
ob_start();

$_SERVER['REQUEST_METHOD'] = 'POST';
$raw_input = json_encode(['user_id' => 'test-id-that-wont-exist', 'enabled' => 1]);

// Capture what toggle_otp_auth would output
echo "=== RAW RESPONSE FROM toggle_otp_auth.php ===\n\n";

// Actually just include and capture
ob_end_clean();

// Show PHP info that might cause issues
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "error_reporting: " . error_reporting() . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n\n";

// Test db_connect.php directly
echo "=== Testing db_connect.php output ===\n";
ob_start();
$path = __DIR__ . '/../../core/db_connect.php';
if (file_exists($path)) {
    include $path;
    $stray = ob_get_clean();
    if ($stray) {
        echo "STRAY OUTPUT from db_connect.php:\n";
        echo htmlspecialchars($stray) . "\n";
        echo "Length: " . strlen($stray) . " bytes\n";
    } else {
        echo "db_connect.php: No stray output. Good.\n";
    }
    if (isset($conn) && $conn) {
        echo "DB Connection: OK\n";
        $conn->close();
    } else {
        echo "DB Connection: FAILED\n";
    }
} else {
    ob_end_clean();
    echo "db_connect.php NOT FOUND at: $path\n";
}

// Check if toggle_otp_auth.php exists
echo "\n=== Checking toggle_otp_auth.php ===\n";
$toggle_path = __DIR__ . '/toggle_otp_auth.php';
if (file_exists($toggle_path)) {
    echo "File exists: YES\n";
    echo "File size: " . filesize($toggle_path) . " bytes\n";
    // Show first 5 lines
    $lines = file($toggle_path);
    echo "First line: " . htmlspecialchars($lines[0] ?? '(empty)');
} else {
    echo "File exists: NO — toggle_otp_auth.php is MISSING!\n";
    echo "Expected at: $toggle_path\n";
}

echo "</pre>";
