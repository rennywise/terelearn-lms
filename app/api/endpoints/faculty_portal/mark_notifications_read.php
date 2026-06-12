<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}
require_once __DIR__ . '/../../core/db_connect.php';

$uid = $conn->real_escape_string($_SESSION['user_id']);
$conn->query("UPDATE tblnotifications SET is_read=1 WHERE user_id='$uid' AND type='general'");
$conn->close();
echo json_encode(['status'=>'success']);
