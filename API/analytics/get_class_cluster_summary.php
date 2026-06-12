<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

if (!$conn) { http_response_code(500); echo json_encode(['status'=>'error','message'=>'DB connection failed']); exit; }
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$classId = trim((string)($_GET['class_id'] ?? ''));
if ($classId==='') { http_response_code(422); echo json_encode(['status'=>'error','message'=>'class_id required']); exit; }
$escClass = $conn->real_escape_string($classId);

$runRes = $conn->query("SELECT id, started_at, finished_at, status FROM tbl_analytics_run WHERE class_id='$escClass' AND status='success' ORDER BY started_at DESC LIMIT 1");
if (!$runRes || !$runRes->num_rows) { echo json_encode(['status'=>'success','summary'=>['total'=>0,'high'=>0,'average'=>0,'at_risk'=>0],'run'=>null]); exit; }
$run = $runRes->fetch_assoc();
$runId = $conn->real_escape_string($run['id']);

$dist = ['high-performing'=>0,'average-performing'=>0,'at-risk'=>0];
$r = $conn->query("SELECT cluster_label, COUNT(*) c FROM tbl_clustering_result WHERE run_id='$runId' GROUP BY cluster_label");
while ($r && $row = $r->fetch_assoc()) { $dist[$row['cluster_label']] = (int)$row['c']; }
$total = array_sum($dist);

echo json_encode([
  'status'=>'success',
  'run'=>$run,
  'summary'=>[
    'total'=>$total,
    'high'=>$dist['high-performing'] ?? 0,
    'average'=>$dist['average-performing'] ?? 0,
    'at_risk'=>$dist['at-risk'] ?? 0,
  ]
]);
