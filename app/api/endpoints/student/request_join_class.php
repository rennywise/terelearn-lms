<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}
require_once __DIR__ . '/../../core/db_connect.php';

$body = json_decode(file_get_contents('php://input'), true);
$join_code = trim($body['join_code'] ?? '');
if (!$join_code) { echo json_encode(['status'=>'error','message'=>'Enter a class code.']); exit; }

$uid = $conn->real_escape_string($_SESSION['user_id']);

// Resolve student_id
$sid = null;
$r = $conn->query("SELECT id FROM tblstudent WHERE user_id='$uid' AND is_deleted=0 LIMIT 1");
if ($r && $r->num_rows) { $sid = $r->fetch_assoc()['id']; }
if (!$sid) {
    $ur = $conn->query("SELECT username,email FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
    if ($ur && $urow = $ur->fetch_assoc()) {
        $un = $conn->real_escape_string($urow['username']);
        $em = $conn->real_escape_string($urow['email']);
        $r2 = $conn->query("SELECT id FROM tblstudent WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
        if ($r2 && $r2->num_rows) $sid = $r2->fetch_assoc()['id'];
    }
}
if (!$sid) { echo json_encode(['status'=>'error','message'=>'Student record not found.']); exit; }

$jc  = $conn->real_escape_string($join_code);
$esc = $conn->real_escape_string($sid);

// Find class
$cr = $conn->query("SELECT id, faculty_id, class_code, section, year_level, class_semester,
                           semester_setting_id, is_active, subject_id
                    FROM tblclass WHERE join_code='$jc' AND is_deleted=0 LIMIT 1");
if (!$cr || !$cr->num_rows) { echo json_encode(['status'=>'error','message'=>'Class code not found.']); exit; }
$cls = $cr->fetch_assoc();

$activeSemRes = $conn->query("SELECT id FROM tblsemestersetting WHERE is_active=1 AND is_deleted=0 ORDER BY id DESC LIMIT 1");
$activeSemRow = $activeSemRes ? $activeSemRes->fetch_assoc() : null;
$activeSemId  = (int)($activeSemRow['id'] ?? 0);
$classSemId   = (int)($cls['semester_setting_id'] ?? 0);
$isArchived   = $classSemId > 0 && ($activeSemId <= 0 || $classSemId !== $activeSemId);

if (!(int)$cls['is_active'] || $isArchived) {
    echo json_encode(['status'=>'error','message'=>"This class isn't available yet."]);
    exit;
}

$class_id   = $conn->real_escape_string($cls['id']);
$faculty_id = $conn->real_escape_string($cls['faculty_id']);

// If an invitation already exists, the student should accept that instead of opening a join request.
$invChk = $conn->query("SELECT invitation_status FROM tblinvitations
                        WHERE class_id='$class_id' AND student_id='$esc' LIMIT 1");
if ($invChk && $invChk->num_rows) {
    $st = $invChk->fetch_assoc()['invitation_status'];
    if ($st === 'pending')  { echo json_encode(['status'=>'error','message'=>'You have a pending invitation to this class — please accept it from your invitations list.']); exit; }
    if ($st === 'accepted') { echo json_encode(['status'=>'error','message'=>'You are already enrolled in this class.']); exit; }
}

// Duplicate check
$chk = $conn->query("SELECT id, enrollment_status FROM tblclassenrollment
                     WHERE class_id='$class_id' AND student_id='$esc' LIMIT 1");
if ($chk && $chk->num_rows) {
    $row    = $chk->fetch_assoc();
    $status = $row['enrollment_status'];

    if ($status === 'enrolled') {
        echo json_encode(['status' => 'error', 'message' => 'You are already enrolled in this class.']); exit;
    }
    if ($status === 'pending') {
        echo json_encode(['status' => 'error', 'message' => 'You already have a pending request for this class. Please wait for the instructor to approve it.']); exit;
    }
    if ($status === 'dropped') {
        // Allow re-join by resetting their enrollment to pending
        $rid = $conn->real_escape_string($row['id']);
        $conn->query("UPDATE tblclassenrollment
                      SET enrollment_status='pending', source='join_request', enrolled_at=NOW(), dropped_at=NULL
                      WHERE id='$rid'");
        // Continue to notification below — don't exit
    }
}

// Insert enrollment request
$eid = $conn->real_escape_string(bin2hex(random_bytes(16)));
$conn->query("INSERT INTO tblclassenrollment 
  (id, class_id, student_id, enrollment_status, source, enrolled_at)
  VALUES ('$eid', '$class_id', '$esc', 'pending', 'join_request', NOW())");

// Get subject name for notification
$sub_name = $cls['class_code'];
$sr = $conn->query("SELECT subject_name FROM tblsubject WHERE id='{$conn->real_escape_string($cls['subject_id'])}' LIMIT 1");
if ($sr && $sr->num_rows) $sub_name = $sr->fetch_assoc()['subject_name'];

// Get student name
$st_name = 'A student';
$snr = $conn->query("SELECT CONCAT(TRIM(first_name),' ',TRIM(last_name)) AS n FROM tblstudent WHERE id='$esc' LIMIT 1");
if ($snr && $snr->num_rows) $st_name = $snr->fetch_assoc()['n'];

// Get faculty user_id for notification target
$fac_user_id = null;
$fur = $conn->query("SELECT a.id AS admin_id, u.id AS user_id
                     FROM tblfaculty f
                     JOIN tbladmin a ON (a.email=f.email OR a.username=f.username) AND a.is_deleted=0
                     JOIN tbluser u ON (u.email=a.email OR u.username=a.username) AND u.is_deleted=0
                     WHERE f.id='$faculty_id' AND f.is_deleted=0 LIMIT 1");
if ($fur && $fur->num_rows) $fac_user_id = $fur->fetch_assoc()['user_id'];

if ($fac_user_id) {
    $nid  = $conn->real_escape_string(bin2hex(random_bytes(16)));
    $fuid = $conn->real_escape_string($fac_user_id);
    $title   = $conn->real_escape_string("Join Request: $sub_name");
    $message = $conn->real_escape_string("$st_name wants to join your class ({$cls['section']} · {$cls['class_semester']}).");
    $conn->query("INSERT INTO tblnotifications (id,user_id,type,title,message,related_id,is_read,created_at)
                  VALUES ('$nid','$fuid','general','$title','$message','$class_id',0,NOW())");
}

$conn->close();
echo json_encode(['status'=>'success','message'=>'Join request sent! Waiting for professor approval.']);
