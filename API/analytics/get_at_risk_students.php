<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

if (!$conn) { http_response_code(500); echo json_encode(['status'=>'error','message'=>'DB connection failed']); exit; }
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$classId = trim((string)($_GET['class_id'] ?? ''));
if ($classId==='') { http_response_code(422); echo json_encode(['status'=>'error','message'=>'class_id required']); exit; }
$escClass = $conn->real_escape_string($classId);

$runRes = $conn->query("SELECT id FROM tbl_analytics_run WHERE class_id='$escClass' AND status='success' ORDER BY started_at DESC LIMIT 1");
if (!$runRes || !$runRes->num_rows) { echo json_encode(['status'=>'success','students'=>[]]); exit; }
$runId = $conn->real_escape_string($runRes->fetch_assoc()['id']);

$sql = "SELECT cr.student_id, cr.cluster_label, cr.risk_score, cr.needs_intervention, cr.intervention_reason,
               s.student_number, TRIM(CONCAT(s.last_name, ', ', s.first_name)) AS full_name,
               ss.attendance_rate, ss.submission_punctuality_rate, ss.overall_weighted_grade, ss.engagement_score
        FROM tbl_clustering_result cr
        LEFT JOIN tblstudent s ON s.id = cr.student_id
        LEFT JOIN tbl_student_analytics_snapshot ss ON ss.run_id = cr.run_id AND ss.student_id = cr.student_id
        WHERE cr.run_id='$runId' AND cr.class_id='$escClass' AND cr.cluster_label='at-risk'
        ORDER BY cr.risk_score DESC, full_name ASC";
$res = $conn->query($sql);
$out = [];
while ($res && $r = $res->fetch_assoc()) $out[] = $r;

echo json_encode(['status'=>'success','students'=>$out]);
