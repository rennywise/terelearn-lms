<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$conn->query("
    CREATE TABLE IF NOT EXISTS tblacademicsettings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(80) NOT NULL UNIQUE,
        setting_value VARCHAR(255) NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$key = 'global_academic_weeks';

function get_week_count(mysqli $conn, string $key): int {
    $stmt = $conn->prepare("SELECT setting_value FROM tblacademicsettings WHERE setting_key = ? LIMIT 1");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $weeks = isset($row['setting_value']) ? (int)$row['setting_value'] : 18;
    return max(1, min(30, $weeks ?: 18));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $weeks = isset($body['week_count']) ? (int)$body['week_count'] : 0;

    if ($weeks < 1 || $weeks > 30) {
        echo json_encode(['status' => 'error', 'message' => 'Week count must be between 1 and 30.']);
        $conn->close();
        exit;
    }

    $value = (string)$weeks;
    $stmt = $conn->prepare("
        INSERT INTO tblacademicsettings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();

    echo json_encode([
        'status' => 'success',
        'message' => 'Academic weeks updated.',
        'week_count' => $weeks
    ]);
    $conn->close();
    exit;
}

$weeks = get_week_count($conn, $key);
echo json_encode(['status' => 'success', 'week_count' => $weeks]);
$conn->close();
