<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}
require_once __DIR__ . '/../../core/db_connect.php';

$uid = $conn->real_escape_string($_SESSION['user_id']);

$sql = "
    SELECT n.id, n.type, n.title, n.message, n.related_id AS class_id,
           n.is_read, n.created_at
    FROM tblnotifications n
    WHERE n.user_id = '$uid'
      AND n.type = 'general'
    ORDER BY n.is_read ASC, n.created_at DESC
    LIMIT 30
";

$res = $conn->query($sql);
$notifications = [];
if ($res) while ($row = $res->fetch_assoc()) $notifications[] = $row;

$unread = array_sum(array_column($notifications, 'is_read') ? 
    array_map(fn($n) => (int)!$n['is_read'], $notifications) : [0]);

$conn->close();
echo json_encode(['status'=>'success','notifications'=>$notifications,'unread'=>$unread]);
