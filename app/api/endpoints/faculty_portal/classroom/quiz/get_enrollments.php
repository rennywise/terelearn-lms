<?php
ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function send($arr) {
    ob_end_clean();
    echo json_encode($arr);
    exit;
}

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    send(['success' => false, 'message' => 'Unauthorized']);
}

require_once __DIR__ . '/../../../../core/db_connect.php';

$post_id = trim($_POST['post_id'] ?? '');
if ($post_id === '') {
    send(['success' => false, 'message' => 'post_id required']);
}

$pid = $conn->real_escape_string($post_id);

$post_title = 'Quiz';
$post_force_open = 0;
$ptRes = $conn->query("SELECT title FROM tblpost WHERE id = '$pid' LIMIT 1");
if ($ptRes && $ptRes->num_rows > 0) {
    $pt = $ptRes->fetch_assoc();
    $post_title = trim((string)($pt['title'] ?? 'Quiz')) ?: 'Quiz';
}
$foRes = $conn->query("SELECT is_force_open FROM tblpost WHERE id = '$pid' LIMIT 1");
if ($foRes && $foRes->num_rows > 0) {
    $post_force_open = (int)($foRes->fetch_assoc()['is_force_open'] ?? 0);
}

$question_count = 0;
$total_points = 0.0;
$qMeta = $conn->query("SELECT COUNT(*) AS qc, COALESCE(SUM(points),0) AS tp FROM tblpostquestion WHERE post_id='$pid' AND is_excluded=0");
if ($qMeta && $qMeta->num_rows > 0) {
    $m = $qMeta->fetch_assoc();
    $question_count = (int)($m['qc'] ?? 0);
    $total_points   = (float)($m['tp'] ?? 0);
}

$quiz    = null;
$quiz_id = null;

$colCheck = $conn->query("SHOW COLUMNS FROM tblquiz LIKE 'post_id'");
if ($colCheck && $colCheck->num_rows > 0) {
    $qRes = $conn->query(
        "SELECT id AS quiz_id, quiz_mode, is_published, is_force_closed,
                live_started_at, live_ended_at, due_date, max_attempts,
                time_limit_seconds, results_released_at
         FROM tblquiz WHERE post_id = '$pid' LIMIT 1"
    );
} else {
    $qRes = $conn->query(
        "SELECT id AS quiz_id, quiz_mode, is_published, is_force_closed,
                live_started_at, live_ended_at, due_date, max_attempts,
                time_limit_seconds, results_released_at
         FROM tblquiz WHERE id = '$pid' LIMIT 1"
    );
}

if ($qRes && $qRes->num_rows > 0) {
    $quiz    = $qRes->fetch_assoc();
    $quiz_id = $quiz['quiz_id'];
}

if (!$quiz) {
    $quiz = [
        'quiz_id' => null, 'quiz_mode' => 'live', 'is_published' => 0,
        'is_force_closed' => 0, 'live_started_at' => null, 'live_ended_at' => null,
        'due_date' => null, 'max_attempts' => 0, 'time_limit_seconds' => null, 'results_released_at' => null,
    ];
}

// Due-date mode policy: once cutoff is reached, enrolled students who never attempted
// should be recorded as submitted with zero score.
if (($quiz['quiz_mode'] ?? 'live') === 'due_date' && !empty($quiz['due_date'])) {
    $dueTs = strtotime($quiz['due_date']);
    if ($dueTs !== false && $dueTs <= time()) {
        $now = date('Y-m-d H:i:s');
        $nowEsc = $conn->real_escape_string($now);
        $maxScore = 0.0;
        $mRes = $conn->query("SELECT COALESCE(SUM(points),0) AS total FROM tblpostquestion WHERE post_id='$pid' AND is_excluded=0");
        if ($mRes && $mRes->num_rows > 0) {
            $maxScore = (float)($mRes->fetch_assoc()['total'] ?? 0);
        }
        $maxEsc = (float)$maxScore;
        $conn->query("
            INSERT INTO tblquizattempt
                (id, post_id, student_id, attempt_number, status, started_at, submitted_at, score, auto_score, final_score, max_score, is_late, late_seconds, faculty_comment)
            SELECT
                UUID(),
                '$pid',
                e.student_id,
                1,
                'submitted',
                '$nowEsc',
                '$nowEsc',
                0,
                0,
                0,
                $maxEsc,
                1,
                0,
                'AUTO_ZERO_MISSED'
            FROM tblquizenrollment e
            LEFT JOIN tblquizattempt a
                ON a.post_id = '$pid' AND a.student_id = e.student_id
            WHERE e.post_id = '$pid'
              AND (e.status IS NULL OR e.status != 'withdrawn')
              AND a.id IS NULL
        ");
    }
}

$enrollments = [];
$submitted_scores = [];
$hasMakeupTable = false;
$tm = $conn->query("SHOW TABLES LIKE 'tblquizmakeupaccess'");
if ($tm && $tm->num_rows > 0) $hasMakeupTable = true;

// Only select columns guaranteed to exist — student_number pulled separately if available
$eRes = $conn->query(
    "SELECT e.id AS enrollment_id, e.student_id, e.enrolled_at,
            s.first_name, s.last_name
     FROM tblquizenrollment e
     LEFT JOIN tblstudent s ON s.id = e.student_id
     WHERE e.post_id = '$pid' AND (e.status IS NULL OR e.status != 'withdrawn')
     ORDER BY e.enrolled_at ASC"
);

// Check if student_number column exists in tblstudent
$snCheck = $conn->query("SHOW COLUMNS FROM tblstudent LIKE 'student_number'");
$hasStudentNumber = ($snCheck && $snCheck->num_rows > 0);

if ($eRes) {
    while ($r = $eRes->fetch_assoc()) {
        $snum = '';
        if ($hasStudentNumber) {
            $sid_e = $conn->real_escape_string($r['student_id']);
            $snRes = $conn->query("SELECT student_number FROM tblstudent WHERE id = '$sid_e' LIMIT 1");
            if ($snRes && $snRes->num_rows > 0) $snum = $snRes->fetch_assoc()['student_number'] ?? '';
        }
        $sid_a = $conn->real_escape_string($r['student_id']);
        $aRes  = $conn->query(
            "SELECT status, score, faculty_comment FROM tblquizattempt
             WHERE post_id = '$pid' AND student_id = '$sid_a'
             ORDER BY id DESC LIMIT 1"
        );
        $att = ($aRes && $aRes->num_rows > 0) ? $aRes->fetch_assoc() : null;
        $isAutoZeroMissed = false;
        if ($att) {
            $attStatus = strtolower(trim((string)($att['status'] ?? '')));
            $attScore  = isset($att['score']) ? (float)$att['score'] : null;
            $attNote   = trim((string)($att['faculty_comment'] ?? ''));
            $isAutoZeroMissed = ($attStatus === 'submitted' && $attScore === 0.0 && $attNote === 'AUTO_ZERO_MISSED');
        }
        $enrollments[] = [
            'enrollment_id'  => $r['enrollment_id'],
            'student_id'     => $r['student_id'],
            'first_name'     => $r['first_name'] ?? '',
            'last_name'      => $r['last_name']  ?? '',
            'student_number' => $snum,
            'enrolled_at'    => $r['enrolled_at'] ?? '',
            'attempt_status' => $att['status'] ?? null,
            'score'          => isset($att['score']) ? (float)$att['score'] : null,
            'is_auto_zero_missed' => $isAutoZeroMissed,
            'makeup_valid_until' => null,
            'makeup_note' => null,
        ];
        if ($hasMakeupTable) {
            $lastIdx = count($enrollments) - 1;
            $mkRes = $conn->query("SELECT valid_until, note FROM tblquizmakeupaccess WHERE post_id='$pid' AND student_id='$sid_a' AND is_active=1 AND consumed_at IS NULL ORDER BY granted_at DESC LIMIT 1");
            if ($mkRes && $mkRes->num_rows > 0) {
                $mk = $mkRes->fetch_assoc();
                $enrollments[$lastIdx]['makeup_valid_until'] = $mk['valid_until'] ?? null;
                $enrollments[$lastIdx]['makeup_note'] = $mk['note'] ?? null;
            }
        }
        if ($att && strtolower((string)($att['status'] ?? '')) === 'submitted' && isset($att['score']) && $att['score'] !== null) {
            $submitted_scores[] = (float)$att['score'];
        }
    }
}

$avg_score = null;
if (count($submitted_scores) > 0) {
    $avg_score = array_sum($submitted_scores) / count($submitted_scores);
}

$conn->close();

send([
    'success' => true,
    'data'    => [
        'quiz' => [
            'quiz_id'             => $quiz['quiz_id'],
            'quiz_mode'           => $quiz['quiz_mode']           ?? 'live',
            'is_published'        => (int)($quiz['is_published']    ?? 0),
            'is_force_closed'     => (int)($quiz['is_force_closed'] ?? 0),
            'is_force_open'       => $post_force_open,
            'live_started_at'     => $quiz['live_started_at']     ?? null,
            'live_ended_at'       => $quiz['live_ended_at']       ?? null,
            'due_date'            => $quiz['due_date']            ?? null,
            'max_attempts'        => (int)($quiz['max_attempts']    ?? 0),
            'time_limit_seconds'  => $quiz['time_limit_seconds']  ?? null,
            'results_released_at' => $quiz['results_released_at'] ?? null,
        ],
        'post_title' => $post_title,
        'question_count' => $question_count,
        'total_points' => $total_points,
        'avg_score' => $avg_score,
        'enrollments' => $enrollments,
        'count'       => count($enrollments),
    ],
]);
