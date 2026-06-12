<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');
function send($a){ob_end_clean();echo json_encode($a);exit;}

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3)
    send(['success'=>false,'message'=>'Unauthorized']);

require_once __DIR__ . '/../../../../core/db_connect.php';

$pid = trim($_POST['post_id'] ?? '');
if (!$pid) send(['success'=>false,'message'=>'post_id required']);
$epid = $conn->real_escape_string($pid);

$user_id = $conn->real_escape_string($_SESSION['user_id']);
$uR = $conn->query("SELECT username, email FROM tbluser WHERE id='$user_id' AND is_deleted=0 LIMIT 1");
if (!$uR || !$uR->num_rows) send(['success'=>false,'message'=>'User not found']);
$u  = $uR->fetch_assoc();
$un = $conn->real_escape_string($u['username']);
$em = $conn->real_escape_string($u['email']);
$sR = $conn->query("SELECT id FROM tblstudent WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
if (!$sR || !$sR->num_rows) send(['success'=>false,'message'=>'Student not found']);
$sid = $conn->real_escape_string($sR->fetch_assoc()['id']);

// Check results released (stored on tblpost by release_results.php)
$qR   = $conn->query("SELECT id, title, post_type, results_released_at FROM tblpost WHERE id='$epid' LIMIT 1");
$quiz = $qR ? $qR->fetch_assoc() : null;
if (!$quiz || !$quiz['results_released_at'])
    send(['success'=>false,'message'=>'Results have not been released yet.']);

// Get latest submitted attempt
$aR  = $conn->query("SELECT id, score, submitted_at FROM tblquizattempt WHERE post_id='$epid' AND student_id='$sid' AND status='submitted' ORDER BY attempt_number DESC LIMIT 1");
$att = $aR ? $aR->fetch_assoc() : null;
if (!$att) send(['success'=>false,'message'=>'No submitted attempt found.']);
$eaid = $conn->real_escape_string($att['id']);

// Max score
$mqR      = $conn->query("SELECT COALESCE(SUM(points),0) AS total FROM tblpostquestion WHERE post_id='$epid' AND is_excluded=0");
$max_score = $mqR ? (float)$mqR->fetch_assoc()['total'] : 0;

// Reviewer payload: each question, student's selected answer, and the correct answer.
$review = [];
$qR = $conn->query("
    SELECT q.id, q.question, q.points, q.order_num
    FROM tblpostquestion q
    WHERE q.post_id='$epid' AND q.is_excluded=0
    ORDER BY q.order_num ASC, q.id ASC
");
while ($qR && ($q = $qR->fetch_assoc())) {
    $qid = $conn->real_escape_string($q['id']);
    $choices = [];
    $selected_choice_id = null;
    $selected_text = null;
    $correct_text = null;
    $correct_choice_id = null;
    $is_correct = null;

    $cR = $conn->query("
        SELECT id, choice_text, is_correct, order_num
        FROM tblpostchoice
        WHERE question_id='$qid'
        ORDER BY order_num ASC, id ASC
    ");
    while ($cR && ($c = $cR->fetch_assoc())) {
        $choices[] = [
            'id' => $c['id'],
            'text' => $c['choice_text'],
            'is_correct' => (int)$c['is_correct'] === 1
        ];
        if ((int)$c['is_correct'] === 1) {
            $correct_text = $c['choice_text'];
            $correct_choice_id = $c['id'];
        }
    }

    $aR2 = $conn->query("SELECT selected_choice_id, is_correct FROM tblquizanswer WHERE attempt_id='$eaid' AND question_id='$qid' LIMIT 1");
    $ans = $aR2 ? $aR2->fetch_assoc() : null;
    if ($ans) {
        $selected_choice_id = $ans['selected_choice_id'] ?? null;
        if ($selected_choice_id) {
            foreach ($choices as $ch) {
                if ($ch['id'] === $selected_choice_id) {
                    $selected_text = $ch['text'];
                    break;
                }
            }
        }
        // Compute correctness from authoritative question choices to avoid stale/null flags in tblquizanswer.
        if ($selected_choice_id !== null && $selected_choice_id !== '') {
            $is_correct = ($correct_choice_id !== null && (string)$selected_choice_id === (string)$correct_choice_id);
        } else {
            $is_correct = false;
        }
    }

    $review[] = [
        'id' => $q['id'],
        'question' => $q['question'],
        'points' => (float)$q['points'],
        'selected_choice_id' => $selected_choice_id,
        'selected_text' => $selected_text,
        'correct_text' => $correct_text,
        'is_correct' => $is_correct,
        'choices' => $choices
    ];
}

send([
    'success'      => true,
    'post'         => [
        'id' => $quiz['id'],
        'title' => $quiz['title'] ?: (strtolower((string)$quiz['post_type']) === 'exam' ? 'Exam' : 'Quiz'),
        'post_type' => strtolower((string)$quiz['post_type']) === 'exam' ? 'exam' : 'quiz',
        'results_released_at' => $quiz['results_released_at']
    ],
    'score'        => (float)$att['score'],
    'max_score'    => $max_score,
    'submitted_at' => $att['submitted_at'],
    'review'       => $review
]);
