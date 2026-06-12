<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

function out($arr, $code = 200){ http_response_code($code); echo json_encode($arr); exit; }
function uuid4(){ $d=random_bytes(16); $d[6]=chr((ord($d[6])&0x0f)|0x40); $d[8]=chr((ord($d[8])&0x3f)|0x80); return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d),4)); }

if (!$conn) out(['status'=>'error','message'=>'DB connection failed'],500);
if (!isset($_SESSION['user_id'])) out(['status'=>'error','message'=>'Unauthorized'],401);

$userId = (string)$_SESSION['user_id'];
$userLevel = (int)($_SESSION['user_level_id'] ?? 0);
$classId = trim((string)($_POST['class_id'] ?? $_GET['class_id'] ?? ''));
$runType = trim((string)($_POST['run_type'] ?? $_GET['run_type'] ?? 'manual'));
if (!in_array($runType, ['manual','scheduled'], true)) $runType='manual';
if ($classId==='') out(['status'=>'error','message'=>'class_id required'],422);

// role guard: only admin(1), dean/faculty(2)
if (!in_array($userLevel, [1,2], true)) out(['status'=>'error','message'=>'Access denied'],403);

$escClass = $conn->real_escape_string($classId);
$escUser  = $conn->real_escape_string($userId);

// Ensure class exists
$classRes = $conn->query("SELECT id, semester_setting_id, faculty_id FROM tblclass WHERE id='$escClass' AND is_deleted=0 LIMIT 1");
if (!$classRes || !$classRes->num_rows) out(['status'=>'error','message'=>'Class not found'],404);
$class = $classRes->fetch_assoc();

// If faculty-level user, ensure ownership
if ($userLevel === 2) {
    $uRes = $conn->query("SELECT username FROM tbluser WHERE id='$escUser' AND is_deleted=0 LIMIT 1");
    if (!$uRes || !$uRes->num_rows) out(['status'=>'error','message'=>'User record not found'],404);
    $uname = $conn->real_escape_string($uRes->fetch_assoc()['username']);
    $fRes = $conn->query("SELECT id FROM tblfaculty WHERE username='$uname' AND is_deleted=0 LIMIT 1");
    if (!$fRes || !$fRes->num_rows) out(['status'=>'error','message'=>'Faculty record not found'],404);
    $facultyId = $fRes->fetch_assoc()['id'];
    if ((string)$class['faculty_id'] !== (string)$facultyId) out(['status'=>'error','message'=>'Class access denied'],403);
}

$semesterId = (int)($class['semester_setting_id'] ?? 0);
if ($semesterId <= 0) {
    $sem = $conn->query("SELECT id FROM tblsemestersetting WHERE is_active=1 AND is_deleted=0 ORDER BY id DESC LIMIT 1");
    $semesterId = ($sem && $sem->num_rows) ? (int)$sem->fetch_assoc()['id'] : 0;
}

// Prevent concurrent run for same class/semester
$semWhere = $semesterId > 0 ? "semester_setting_id=".$semesterId : "semester_setting_id IS NULL";
$runChk = $conn->query("SELECT id FROM tbl_analytics_run WHERE class_id='$escClass' AND $semWhere AND status='running' ORDER BY started_at DESC LIMIT 1");
if ($runChk && $runChk->num_rows) out(['status'=>'error','message'=>'Analytics run already in progress for this class'],409);

$runId = uuid4();
$escRun = $conn->real_escape_string($runId);
$escType = $conn->real_escape_string($runType);
$semSql = $semesterId>0 ? (string)$semesterId : 'NULL';

$ins = "INSERT INTO tbl_analytics_run (id, class_id, semester_setting_id, run_type, k_value, status, started_at, created_by)
        VALUES ('$escRun','$escClass',$semSql,'$escType',3,'running',NOW(),'$escUser')";
if (!$conn->query($ins)) out(['status'=>'error','message'=>'Failed to create analytics run: '.$conn->error],500);

$pyScript = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'analytics' . DIRECTORY_SEPARATOR . 'run_kmeans.py';
if (!file_exists($pyScript)) {
    $conn->query("UPDATE tbl_analytics_run SET status='failed', finished_at=NOW(), error_message='Python script not found' WHERE id='$escRun'");
    out(['status'=>'error','message'=>'Python runner not found'],500);
}

$python = 'python';
$cmd = escapeshellcmd($python) . ' ' . escapeshellarg($pyScript)
     . ' --run_id=' . escapeshellarg($runId)
     . ' --class_id=' . escapeshellarg($classId)
     . ' --semester_setting_id=' . escapeshellarg((string)$semesterId);

$output = [];
$returnCode = 0;
@exec($cmd . ' 2>&1', $output, $returnCode);
$raw = trim(implode("\n", $output));

if ($returnCode !== 0) {
    $err = $conn->real_escape_string(substr($raw ?: 'Python execution failed', 0, 4000));
    $conn->query("UPDATE tbl_analytics_run SET status='failed', finished_at=NOW(), error_message='$err' WHERE id='$escRun'");
    out(['status'=>'error','message'=>'Analytics failed','run_id'=>$runId,'debug'=>$raw],500);
}

$conn->query("UPDATE tbl_analytics_run SET status='success', finished_at=NOW(), error_message=NULL WHERE id='$escRun' AND status='running'");
out(['status'=>'success','message'=>'Analytics completed','run_id'=>$runId,'output'=>$raw]);
