<?php
// API/facultyUI/classroom/quiz/end_quiz.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}
require_once __DIR__ . '/../../../../core/db_connect.php';

function fail_q($m){ echo json_encode(['success'=>false,'message'=>$m]); exit; }

$pid     = trim($_POST['post_id'] ?? '');
$user_id = $_SESSION['user_id'];
if (!$pid) fail_q('post_id required');

// ── Resolve faculty_id ──
$eu = $conn->real_escape_string($user_id);
$uR = $conn->query("SELECT username FROM tbluser WHERE id='$eu' AND is_deleted=0 LIMIT 1");
if (!$uR || !($uRow = $uR->fetch_assoc())) fail_q('User not found');
$ef = $conn->real_escape_string($uRow['username']);
$fR = $conn->query("SELECT id FROM tblfaculty WHERE username='$ef' AND is_deleted=0 LIMIT 1");
if (!$fR || !($fRow = $fR->fetch_assoc())) fail_q('Faculty not found');

$esc_fid = $conn->real_escape_string($fRow['id']);
$esc_pid = $conn->real_escape_string($pid);

// ── Verify ownership ──
$pR = $conn->query("SELECT id FROM tblpost WHERE id='$esc_pid' AND author_id='$esc_fid' AND is_deleted=0 LIMIT 1");
if (!$pR || $pR->num_rows === 0) fail_q('Post not found or not yours');

$now = $conn->real_escape_string(date('Y-m-d H:i:s'));

// ── Close the quiz ──
$conn->query("
    UPDATE tblpost SET
        live_ended_at   = '$now',
        is_force_closed = 1,
        is_force_open   = 0
    WHERE id = '$esc_pid'
");

// ── Auto-submit all in-progress attempts ──
$aR = $conn->query("
    SELECT id FROM tblquizattempt
    WHERE post_id='$esc_pid' AND status='in_progress'
");
$autoSubmitted = 0;
if ($aR) {
    while ($att = $aR->fetch_assoc()) {
        $eaid = $conn->real_escape_string($att['id']);

        // Score: sum points for correct answers
        $score = 0.0;
        $qR = $conn->query("
            SELECT q.points, c.is_correct
            FROM   tblpostquestion q
            LEFT JOIN tblquizanswer  ans ON ans.question_id = q.id AND ans.attempt_id = '$eaid'
            LEFT JOIN tblpostchoice  c   ON c.id = ans.selected_choice_id
            WHERE  q.post_id = '$esc_pid' AND q.is_excluded = 0
        ");
        while ($qR && $qRow = $qR->fetch_assoc()) {
            if ((int)$qRow['is_correct'] === 1) $score += (float)($qRow['points'] ?? 1);
        }

        $conn->query("
            UPDATE tblquizattempt SET
                status       = 'submitted',
                submitted_at = '$now',
                score        = $score
            WHERE id = '$eaid'
        ");
        $autoSubmitted++;
    }
}

echo json_encode([
    'success'        => true,
    'status'         => 'success',
    'ended_at'       => $now,
    'auto_submitted' => $autoSubmitted,
]);
