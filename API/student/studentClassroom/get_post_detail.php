<?php
/**
 * API/student/studentClassroom/get_post_detail.php
 * Returns full post details + student's existing submission for a given post_id.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}

$user_id = $_SESSION['user_id'];
$post_id = trim($_POST['post_id'] ?? '');

if (!$post_id) {
    echo json_encode(['status'=>'error','message'=>'post_id required']); exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

$esc_uid = $conn->real_escape_string($user_id);
$esc_pid = $conn->real_escape_string($post_id);

/* ── Resolve student record ── */
$uRes = $conn->query("SELECT username, email FROM tbluser WHERE id='$esc_uid' AND is_deleted=0 LIMIT 1");
if (!$uRes || $uRes->num_rows === 0) {
    echo json_encode(['status'=>'error','message'=>'User not found']); exit;
}
$uRow = $uRes->fetch_assoc();
$un   = $conn->real_escape_string($uRow['username']);
$em   = $conn->real_escape_string($uRow['email']);

$sRes = $conn->query("SELECT id,user_id,first_name,last_name FROM tblstudent WHERE (user_id='$esc_uid' OR username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
if (!$sRes || $sRes->num_rows === 0) {
    echo json_encode(['status'=>'error','message'=>'Student record not found']); exit;
}
$sRow       = $sRes->fetch_assoc();
$student_id = (string)$sRow['id'];
$student_uid = (string)($sRow['user_id'] ?? $user_id);
$esc_sid    = $conn->real_escape_string($student_id);
$esc_suid   = $conn->real_escape_string($student_uid);
$student_initials = strtoupper(substr((string)($sRow['first_name'] ?? ''), 0, 1) . substr((string)($sRow['last_name'] ?? ''), 0, 1));
if ($student_initials === '') {
    $student_initials = 'ST';
}

/* ── Fetch post ── */
$pRes = $conn->query("
    SELECT p.*,
           CONCAT(f.first_name, ' ', f.last_name) AS author_name,
           pt.type_key,
           pt.type_label,
           pt.icon AS type_icon,
           pt.color_bg AS type_color_bg,
           pt.color_text AS type_color_text,
           pt.is_gradable AS type_is_gradable,
           pt.has_quiz AS type_has_quiz,
           qz.quiz_mode AS quiz_mode_db,
           qz.max_attempts AS quiz_max_attempts,
           qz.time_limit_seconds AS quiz_time_limit_seconds,
           qz.due_date AS quiz_due_date,
           qz.live_started_at AS quiz_live_started_at,
           qz.live_ended_at AS quiz_live_ended_at,
           qz.is_force_closed AS quiz_is_force_closed,
           qz.results_released_at AS quiz_results_released_at,
           qz.is_published AS quiz_is_published
    FROM   tblpost p
    LEFT JOIN tblfaculty f ON f.id = p.author_id
    LEFT JOIN tblposttype pt ON pt.id = p.post_type_id
    LEFT JOIN tblquiz qz ON qz.post_id = p.id
    WHERE  p.id = '$esc_pid' AND p.is_deleted = 0
    LIMIT 1
");
if (!$pRes || $pRes->num_rows === 0) {
    echo json_encode(['status'=>'error','message'=>'Post not found']); exit;
}
$post = $pRes->fetch_assoc();
$post['type_key'] = $post['type_key'] ?: ($post['post_type'] ?: 'post');
$post['type_label'] = $post['type_label'] ?: ucfirst((string)$post['type_key']);
$post['type_is_gradable'] = (int)($post['type_is_gradable'] ?? 0);
$post['type_has_quiz'] = (int)($post['type_has_quiz'] ?? 0);
if ($post['type_has_quiz'] === 1 || in_array(strtolower((string)$post['post_type']), ['quiz', 'exam'], true)) {
    $modeFromQuiz = strtolower(trim((string)($post['quiz_mode_db'] ?? '')));
    $modeFromPost = strtolower(trim((string)($post['quiz_mode'] ?? '')));
    if ($modeFromQuiz === 'live' || $modeFromQuiz === 'due_date') {
        $post['quiz_mode'] = $modeFromQuiz;
    } elseif ($modeFromPost === 'live' || $modeFromPost === 'due_date') {
        $post['quiz_mode'] = $modeFromPost;
    } else {
        $post['quiz_mode'] = 'due_date';
    }
    $post['is_published'] = isset($post['quiz_is_published']) ? (int)$post['quiz_is_published'] : (int)($post['is_published'] ?? 0);
    $post['is_force_closed'] = isset($post['quiz_is_force_closed']) ? (int)$post['quiz_is_force_closed'] : (int)($post['is_force_closed'] ?? 0);
    $post['live_started_at'] = $post['quiz_live_started_at'] ?: ($post['live_started_at'] ?? null);
    $post['live_ended_at'] = $post['quiz_live_ended_at'] ?: ($post['live_ended_at'] ?? null);
    $post['results_released_at'] = $post['quiz_results_released_at'] ?: ($post['results_released_at'] ?? null);
    if (!empty($post['quiz_due_date'])) {
        $post['due_date'] = $post['quiz_due_date'];
    }
    if (!empty($post['quiz_max_attempts'])) {
        $post['max_attempts'] = $post['quiz_max_attempts'];
    }
    if (!empty($post['quiz_time_limit_seconds'])) {
        $post['time_limit_seconds'] = $post['quiz_time_limit_seconds'];
    }
}

/* ── Verify enrollment ── */
$cid  = $conn->real_escape_string($post['class_id']);
$eRes = $conn->query("
    SELECT id FROM tblclassenrollment
    WHERE class_id='$cid' AND (student_id='$esc_sid' OR student_id='$esc_suid') AND enrollment_status='enrolled'
    LIMIT 1
");
if (!$eRes || $eRes->num_rows === 0) {
    echo json_encode(['status'=>'error','message'=>'You are not enrolled in this class']); exit;
}

/* ── Faculty attachments ── */
$attRes = $conn->query("SELECT * FROM tblpostattachment WHERE post_id='$esc_pid' ORDER BY id ASC");
$post['attachments'] = [];
if ($attRes) while ($a = $attRes->fetch_assoc()) $post['attachments'][] = $a;

$metaRes = $conn->query("
    SELECT COUNT(*) AS question_count, COALESCE(SUM(points),0) AS question_points
    FROM tblpostquestion
    WHERE post_id='$esc_pid' AND (is_excluded=0 OR is_excluded IS NULL)
");
if ($metaRes && $metaRes->num_rows > 0) {
    $meta = $metaRes->fetch_assoc();
    $post['question_count'] = (int)($meta['question_count'] ?? 0);
    $post['question_points'] = (float)($meta['question_points'] ?? 0);
}

/* ── Existing submission ── */
$submission = null;
$subRes = $conn->query("
    SELECT s.*
    FROM   tblsubmission s
    WHERE  s.post_id='$esc_pid' AND (s.student_id='$esc_sid' OR s.student_id='$esc_suid')
    ORDER  BY s.submitted_at DESC
    LIMIT 1
");
if ($subRes && $subRes->num_rows > 0) {
    $submission = $subRes->fetch_assoc();

    // Submission files
    $sfRes = $conn->query("
        SELECT * FROM tblpostattachment
        WHERE post_id='$esc_pid' AND (student_id='$esc_sid' OR student_id='$esc_suid')
        ORDER BY id ASC
    ");
    $submission['files'] = [];
    if ($sfRes) while ($sf = $sfRes->fetch_assoc()) $submission['files'][] = $sf;
}

$conn->close();

echo json_encode([
    'status'     => 'success',
    'post'       => $post,
    'submission' => $submission,
    'student_initials' => $student_initials,
]);
