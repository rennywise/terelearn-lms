<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

if (!$conn) { http_response_code(500); echo json_encode(['status'=>'error','message'=>'DB connection failed']); exit; }
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$classId = trim((string)($_GET['class_id'] ?? ''));
$limit = (int)($_GET['limit'] ?? 10);
if ($limit < 1 || $limit > 50) $limit = 10;
if ($classId==='') { http_response_code(422); echo json_encode(['status'=>'error','message'=>'class_id required']); exit; }
$escClass = $conn->real_escape_string($classId);

$runs = $conn->query("SELECT id, started_at FROM tbl_analytics_run WHERE class_id='$escClass' AND status='success' ORDER BY started_at DESC LIMIT $limit");
$items = [];
while ($runs && $run = $runs->fetch_assoc()) {
    $rid = $conn->real_escape_string($run['id']);
    $dist = ['high-performing'=>0,'average-performing'=>0,'at-risk'=>0];
    $dRes = $conn->query("SELECT cluster_label, COUNT(*) c FROM tbl_clustering_result WHERE run_id='$rid' GROUP BY cluster_label");
    while ($dRes && $d = $dRes->fetch_assoc()) $dist[$d['cluster_label']] = (int)$d['c'];
    $items[] = [
        'run_id' => $run['id'],
        'started_at' => $run['started_at'],
        'high' => $dist['high-performing'] ?? 0,
        'average' => $dist['average-performing'] ?? 0,
        'at_risk' => $dist['at-risk'] ?? 0,
    ];
}

echo json_encode(['status'=>'success','items'=>array_reverse($items)]);
