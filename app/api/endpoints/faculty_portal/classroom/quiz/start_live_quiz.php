<?php
// API/facultyUI/classroom/quiz/start_live_quiz.php
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

session_start();
header('Content-Type: application/json; charset=utf-8');

function send_j($arr){ ob_end_clean(); echo json_encode($arr); exit; }
function fail_q($m){ send_j(['success'=>false,'message'=>$m]); }
function ok_q($m,$x=[]){ send_j(array_merge(['success'=>true,'message'=>$m],$x)); }

$level = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
if (!isset($_SESSION['user_id']) || $level !== 2) fail_q('Unauthorized');

require_once __DIR__ . '/../../../../core/db_connect.php';

$pid    = trim($_POST['post_id'] ?? '');
$action = trim($_POST['action']  ?? 'start');
if (!$pid) fail_q('post_id required');

$esc_pid = $conn->real_escape_string($pid);

$qR = $conn->query("SELECT * FROM tblquiz WHERE post_id='$esc_pid' LIMIT 1");
if (!$qR || !($quiz = $qR->fetch_assoc())) fail_q('Quiz not found');

$now     = date('Y-m-d H:i:s');
$esc_now = $conn->real_escape_string($now);
$mode    = $quiz['quiz_mode'] ?? 'live';

if ($action === 'start') {
    if ($mode !== 'live')                    fail_q('Only Live mode quizzes can be started');
    if (!empty($quiz['live_started_at']))     fail_q('Quiz is already live');
    if (!empty($quiz['live_ended_at'])
     || (int)$quiz['is_force_closed'] === 1) fail_q('Quiz has already ended');

    $conn->query("UPDATE tblquiz SET live_started_at='$esc_now', is_force_closed=0 WHERE post_id='$esc_pid'");
    $conn->query("UPDATE tblpost SET live_started_at='$esc_now', is_force_open=1, is_force_closed=0 WHERE id='$esc_pid'");
    ok_q('Quiz started!', ['started_at' => $now]);
}

if ($action === 'end') {
    if ($mode !== 'live')                    fail_q('Only Live mode quizzes can be ended this way');
    if (empty($quiz['live_started_at']))      fail_q('Quiz has not been started yet');
    if (!empty($quiz['live_ended_at'])
     || (int)$quiz['is_force_closed'] === 1) fail_q('Quiz has already ended');

    $conn->query("UPDATE tblquiz SET live_ended_at='$esc_now', is_force_closed=1 WHERE post_id='$esc_pid'");
    $conn->query("UPDATE tblpost SET live_ended_at='$esc_now', is_force_closed=1, is_force_open=0 WHERE id='$esc_pid'");
    ok_q('Quiz ended.', ['ended_at' => $now]);
}

if ($action === 'pause') {
    if ($mode !== 'live')                    fail_q('Only Live mode quizzes can be paused');
    if (empty($quiz['live_started_at']))     fail_q('Quiz has not been started yet');
    if (!empty($quiz['live_ended_at']) || (int)$quiz['is_force_closed'] === 1) fail_q('Quiz has already ended');
    $conn->query("UPDATE tblpost SET is_force_open=0, is_force_closed=0 WHERE id='$esc_pid'");
    ok_q('Quiz paused. Students are moved to waiting screen.');
}

if ($action === 'resume') {
    if ($mode !== 'live')                    fail_q('Only Live mode quizzes can be resumed');
    if (empty($quiz['live_started_at']))     fail_q('Quiz has not been started yet');
    if (!empty($quiz['live_ended_at']) || (int)$quiz['is_force_closed'] === 1) fail_q('Quiz has already ended');
    $conn->query("UPDATE tblpost SET is_force_open=1, is_force_closed=0 WHERE id='$esc_pid'");
    ok_q('Quiz resumed.');
}

if ($action === 'force_close') {
    $isEnded = !empty($quiz['live_ended_at']) || (int)$quiz['is_force_closed'] === 1;
    if ($isEnded) fail_q('Quiz is already closed');

    $conn->query("UPDATE tblquiz SET is_force_closed=1 WHERE post_id='$esc_pid'");
    $conn->query("UPDATE tblpost SET is_force_closed=1, is_force_open=0 WHERE id='$esc_pid'");
    ok_q('Quiz force-closed.');
}

if ($action === 'release_results') {
    if (!empty($quiz['results_released_at'])) fail_q('Results already released');

    // Security/fairness: lock attempts before releasing results so students
    // who have not taken/submitted cannot view/share answers first.
    $conn->query("UPDATE tblquiz SET results_released_at='$esc_now', is_force_closed=1 WHERE post_id='$esc_pid'");
    $conn->query("UPDATE tblpost SET results_released_at='$esc_now', is_force_closed=1, is_force_open=0, live_ended_at=COALESCE(live_ended_at,'$esc_now') WHERE id='$esc_pid'");

    // Auto-submit all in-progress attempts and compute scores.
    $aR = $conn->query("SELECT id FROM tblquizattempt WHERE post_id='$esc_pid' AND status='in_progress'");
    $autoSubmitted = 0;
    if ($aR) {
        while ($att = $aR->fetch_assoc()) {
            $eaid = $conn->real_escape_string($att['id']);
            $score = 0.0;
            $qR = $conn->query("
                SELECT q.points, c.is_correct
                FROM   tblpostquestion q
                LEFT JOIN tblquizanswer ans ON ans.question_id = q.id AND ans.attempt_id = '$eaid'
                LEFT JOIN tblpostchoice c   ON c.id = ans.selected_choice_id
                WHERE  q.post_id = '$esc_pid' AND q.is_excluded = 0
            ");
            while ($qR && $qRow = $qR->fetch_assoc()) {
                if ((int)$qRow['is_correct'] === 1) $score += (float)($qRow['points'] ?? 1);
            }
            $conn->query("UPDATE tblquizattempt SET status='submitted', submitted_at='$esc_now', score=$score WHERE id='$eaid'");
            $autoSubmitted++;
        }
    }

    // Also assign zero to enrolled students who never started an attempt.
    $maxScore = 0.0;
    $mRes = $conn->query("SELECT COALESCE(SUM(points),0) AS total FROM tblpostquestion WHERE post_id='$esc_pid' AND is_excluded=0");
    if ($mRes && $mRes->num_rows > 0) {
        $maxScore = (float)($mRes->fetch_assoc()['total'] ?? 0);
    }
    $maxEsc = (float)$maxScore;
    $conn->query("
        INSERT INTO tblquizattempt
            (id, post_id, student_id, attempt_number, status, started_at, submitted_at, score, auto_score, final_score, max_score, is_late, late_seconds, faculty_comment)
        SELECT
            UUID(),
            '$esc_pid',
            e.student_id,
            1,
            'submitted',
            '$esc_now',
            '$esc_now',
            0,
            0,
            0,
            $maxEsc,
            1,
            0,
            'AUTO_ZERO_MISSED'
        FROM tblquizenrollment e
        LEFT JOIN tblquizattempt a
            ON a.post_id = '$esc_pid' AND a.student_id = e.student_id
        WHERE e.post_id = '$esc_pid'
          AND (e.status IS NULL OR e.status != 'withdrawn')
          AND a.id IS NULL
    ");

    ok_q('Results released! Quiz is now locked to protect answer integrity.', ['released_at' => $now, 'auto_submitted' => $autoSubmitted]);
}

fail_q('Unknown action');
