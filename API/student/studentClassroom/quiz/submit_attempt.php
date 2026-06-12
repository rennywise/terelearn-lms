<?php
// API/student/studentClassroom/quiz/submit_attempt.php
require_once __DIR__ . '/_student_guard.php';

$aid = trim($_POST['attempt_id'] ?? '');
if (!$aid) fail('attempt_id required');

$eaid = $conn->real_escape_string($aid);
$esid = $conn->real_escape_string($student_id);

$ar  = $conn->query("SELECT * FROM tblquizattempt WHERE id='$eaid' AND student_id='$esid' LIMIT 1");
$att = $ar ? $ar->fetch_assoc() : null;
if (!$att) fail('Attempt not found', 404);

$pid  = trim($_POST['post_id'] ?? $att['post_id'] ?? '');
if (!$pid) fail('post_id required');
$epid = $conn->real_escape_string($pid);

$mqr       = $conn->query("SELECT COALESCE(SUM(points),0) AS total FROM tblpostquestion WHERE post_id='$epid' AND is_excluded=0");
$max_score = $mqr ? (float)$mqr->fetch_assoc()['total'] : 0;

if ($att['status'] === 'submitted') {
    ok([
        'attempt'    => $att,
        'score'      => (float)($att['score'] ?? 0),
        'auto_score' => (float)($att['score'] ?? 0),
        'max_score'  => $max_score,
        'is_late'    => false,
    ]);
}

if ($att['status'] !== 'in_progress') fail('Attempt is not in progress', 403);

$now   = date('Y-m-d H:i:s');
$score = 0.0;
$qr    = $conn->query("
    SELECT q.id, q.points, a.selected_choice_id, c.is_correct
    FROM   tblpostquestion q
    LEFT JOIN tblquizanswer a ON a.question_id = q.id AND a.attempt_id = '$eaid'
    LEFT JOIN tblpostchoice c ON c.id = a.selected_choice_id
    WHERE  q.post_id = '$epid' AND q.is_excluded = 0
");
while ($qr && $row = $qr->fetch_assoc()) {
    if ((int)$row['is_correct'] === 1) $score += (float)($row['points'] ?? 1);
}

$conn->query("UPDATE tblquizattempt SET status='submitted', submitted_at='$now', score=$score WHERE id='$eaid' AND student_id='$esid'");

$updated = $conn->query("SELECT * FROM tblquizattempt WHERE id='$eaid' LIMIT 1")->fetch_assoc();

ok([
    'attempt'    => $updated,
    'score'      => $score,
    'auto_score' => $score,
    'max_score'  => $max_score,
    'is_late'    => false,
]);