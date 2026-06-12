<?php
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$uid    = mysqli_real_escape_string($conn, $userId);

$res = $conn->query("SELECT profile_picture FROM tbluser WHERE id='$uid' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    if (!empty($row['profile_picture'])) {
        $path = dirname(__DIR__, 4) . '/' . ltrim($row['profile_picture'], '/');
        if (file_exists($path)) @unlink($path);
    }
}
$conn->query("UPDATE tbluser SET profile_picture=NULL WHERE id='$uid'");
$conn->close();
echo json_encode(['status'=>'success']);
