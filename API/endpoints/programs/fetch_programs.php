<?php
/* API/fetch_programs.php — returns all active programs as JSON */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$rows = [];
$res  = $conn->query("SELECT id, course_code, course_name FROM tblcourse WHERE is_deleted = 0 ORDER BY course_code");
while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode(['status' => 'success', 'programs' => $rows]);
$conn->close();
