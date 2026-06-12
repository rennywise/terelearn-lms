<?php
// API/student/studentClassroom/quiz/_student_guard.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../core/db_connect.php';

function fail($m,$c=400){http_response_code($c);echo json_encode(['status'=>'error','message'=>$m]);exit;}
function ok($d=[]){echo json_encode(array_merge(['status'=>'success'],$d));exit;}
function uuid4(){return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));}
function assessment_label(array $post): string {
    return strtolower(trim((string)($post['post_type'] ?? ''))) === 'exam' ? 'Exam' : 'Quiz';
}

$_uid = $_SESSION['user_id'] ?? '';
$lvl  = (int)($_SESSION['user_level_id'] ?? 0);
if (!$_uid || $lvl !== 3) fail('Unauthorized', 401);

// Resolve tblstudent.id (used by all enrollment/attempt tables)
$eu  = $conn->real_escape_string($_uid);
$uR  = $conn->query("SELECT username, email FROM tbluser WHERE id='$eu' AND is_deleted=0 LIMIT 1");
$uRow = $uR ? $uR->fetch_assoc() : null;
if (!$uRow) fail('User not found', 401);

$eun = $conn->real_escape_string($uRow['username']);
$eem = $conn->real_escape_string($uRow['email']);
$sR  = $conn->query("SELECT id FROM tblstudent WHERE (username='$eun' OR email='$eem') AND is_deleted=0 LIMIT 1");
$sRow = $sR ? $sR->fetch_assoc() : null;
if (!$sRow) fail('Student record not found', 401);

$student_id = $sRow['id'];   // correct tblstudent.id used everywhere

function get_quiz_post(mysqli $conn, string $pid, string $sid): array {
    $epid = $conn->real_escape_string($pid);
    $esid = $conn->real_escape_string($sid);
    $hasPostId = false;
    $col = $conn->query("SHOW COLUMNS FROM tblquiz LIKE 'post_id'");
    if ($col && $col->num_rows > 0) $hasPostId = true;
    $joinQuiz = $hasPostId ? "q.post_id = p.id" : "q.id = p.id";
    $r = $conn->query("
        SELECT
            p.*,
            q.quiz_mode,
            q.max_attempts,
            q.time_limit_seconds,
            q.due_date AS quiz_due_date,
            q.live_started_at,
            q.live_ended_at
        FROM tblpost p
        INNER JOIN tblquiz q ON $joinQuiz
        INNER JOIN tblclassenrollment ce
            ON ce.class_id = p.class_id
           AND ce.student_id = '$esid'
           AND ce.enrollment_status = 'enrolled'
        WHERE p.id = '$epid'
          AND p.is_deleted = 0
          AND LOWER(COALESCE(p.post_type, '')) IN ('quiz', 'exam')
        LIMIT 1
    ");
    $p = $r ? $r->fetch_assoc() : null;
    if (!$p) fail('Assessment not found or not enrolled', 404);
    if (!(int)$p['is_published']) fail('Assessment not published yet', 403);
    // Use quiz due date as canonical due cutoff when available.
    if (!empty($p['quiz_due_date'])) $p['due_date'] = $p['quiz_due_date'];
    return $p;
}

function is_quiz_open(array $p): bool {
    if ((int)$p['is_force_closed']) return false;
    if ((int)$p['is_force_open'])   return true;
    $now = time();
    if ($p['open_at']  && $now < strtotime($p['open_at']))  return false;
    if ($p['close_at'] && $now > strtotime($p['close_at'])) return false;
    return true;
}
