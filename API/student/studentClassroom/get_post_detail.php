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

$sRes = $conn->query("SELECT id,user_id FROM tblstudent WHERE (user_id='$esc_uid' OR username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
if (!$sRes || $sRes->num_rows === 0) {
    echo json_encode(['status'=>'error','message'=>'Student record not found']); exit;
}
$sRow       = $sRes->fetch_assoc();
$student_id = (string)$sRow['id'];
$student_uid = (string)($sRow['user_id'] ?? $user_id);
$esc_sid    = $conn->real_escape_string($student_id);
$esc_suid   = $conn->real_escape_string($student_uid);

/* ── Fetch post ── */
$pRes = $conn->query("
    SELECT p.*,
           CONCAT(f.first_name, ' ', f.last_name) AS author_name
    FROM   tblpost p
    LEFT JOIN tblfaculty f ON f.id = p.author_id
    WHERE  p.id = '$esc_pid' AND p.is_deleted = 0
    LIMIT 1
");
if (!$pRes || $pRes->num_rows === 0) {
    echo json_encode(['status'=>'error','message'=>'Post not found']); exit;
}
$post = $pRes->fetch_assoc();

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
]);
