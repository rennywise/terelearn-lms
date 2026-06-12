<?php
/* API/Admin/get_semester.php */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

/* Active setting */
$res     = $conn->query("SELECT * FROM tblsemestersetting WHERE is_active=1 ORDER BY id DESC LIMIT 1");
$current = $res->fetch_assoc();

/* Last 5 log entries for undo display */
$log = [];
$logRes = $conn->query("SELECT * FROM tblsemesterlog ORDER BY id DESC LIMIT 5");
while ($r = $logRes->fetch_assoc()) $log[] = $r;

$conn->close();
echo json_encode(['status'=>'success','current'=>$current,'log'=>$log]);
