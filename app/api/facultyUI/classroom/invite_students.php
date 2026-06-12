<?php
/**
 * API/facultyUI/classroom/invite_students.php
 *
 * Sends invitations to students by inserting into:
 *   - tblinvitations (invitation_status = 'pending')
 *   - tblclassenrollment (enrollment_status = 'pending') ← THIS IS THE FIX
 *
 * The student will appear in the People > Pending section immediately.
 * When they accept (or faculty approves), status becomes 'enrolled'.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

$body        = json_decode(file_get_contents('php://input'), true) ?? [];
$class_id    = trim($body['class_id']   ?? '');
$student_ids = $body['student_ids']     ?? [];

// FIX: Use faculty_id from session (tblfaculty.id), not user_id (tbluser.id)
$faculty_id  = $_SESSION['faculty_id'] ?? $_SESSION['user_id'];

if (!$class_id || empty($student_ids)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']); exit;
}

$esc_class   = $conn->real_escape_string($class_id);
$esc_faculty = $conn->real_escape_string($faculty_id);

/* Verify faculty owns this class */
$chk = $conn->query("SELECT id FROM tblclass WHERE id='$esc_class' AND faculty_id='$esc_faculty' AND is_deleted=0 LIMIT 1");
if (!$chk || $chk->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
}

function gen_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
    );
}

$invited = 0;
$skipped = 0;

foreach ($student_ids as $sid) {
    $sid = $conn->real_escape_string(trim($sid));
    if (!$sid) continue;

    /* Skip if already enrolled (active roster) */
    $chk2 = $conn->query("
        SELECT id FROM tblclassenrollment
        WHERE class_id='$esc_class' AND student_id='$sid'
          AND enrollment_status='enrolled'
        LIMIT 1
    ");
    if ($chk2 && $chk2->num_rows > 0) { $skipped++; continue; }

    /* Also skip if already in tblclassstudents (enrolled directly) */
    $chk3 = $conn->query("
        SELECT id FROM tblclassstudents
        WHERE class_id='$esc_class' AND student_id='$sid' AND is_deleted=0 LIMIT 1
    ");
    if ($chk3 && $chk3->num_rows > 0) { $skipped++; continue; }

    /* 1. Insert into tblinvitations */
    $invExist = $conn->query("
        SELECT id FROM tblinvitations
        WHERE class_id='$esc_class' AND student_id='$sid' LIMIT 1
    ");
    if ($invExist && $invExist->num_rows > 0) {
        /* Reset a previously declined invitation */
        $conn->query("
            UPDATE tblinvitations
            SET invitation_status='pending', invited_at=NOW(), responded_at=NULL
            WHERE class_id='$esc_class' AND student_id='$sid'
        ");
    } else {
        $invId = gen_uuid();
        $conn->query("
            INSERT INTO tblinvitations (id, class_id, student_id, invited_by, invitation_status)
            VALUES ('$invId','$esc_class','$sid','$esc_faculty','pending')
        ");
    }

    /* 2. Upsert into tblclassenrollment as 'pending' */
    $enrollExist = $conn->query("
        SELECT id FROM tblclassenrollment
        WHERE class_id='$esc_class' AND student_id='$sid' LIMIT 1
    ");
    if ($enrollExist && $enrollExist->num_rows > 0) {
        $conn->query("
            UPDATE tblclassenrollment
            SET enrollment_status='pending', source='invite', initiated_by='faculty', enrolled_at=NOW()
            WHERE class_id='$esc_class' AND student_id='$sid'
        ");
    } else {
        $enrollId = gen_uuid();
        $conn->query("
            INSERT INTO tblclassenrollment (id, class_id, student_id, enrollment_status, source, initiated_by, enrolled_at)
                VALUES ('$enrollId','$esc_class','$sid','pending','invite','faculty',NOW())
        ");
    }

    /* 3. Create notification for the student */
    $notifId = gen_uuid();
    $conn->query("
        INSERT INTO tblnotifications
            (id, user_id, type, title, message, related_id, is_read, created_at)
        SELECT '$notifId', s.user_id, 'invitation',
               'New class invitation',
               'You have been invited to join a class. Open your invitations to accept or decline.',
               '$esc_class', 0, NOW()
        FROM   tblstudent s
        WHERE  s.id='$sid' AND s.user_id IS NOT NULL
        LIMIT 1
    ");

    $invited++;
}

$conn->close();
echo json_encode([
    'status'  => 'success',
    'message' => "$invited student(s) invited. $skipped already enrolled or pending.",
    'invited' => $invited,
    'skipped' => $skipped,
]);
?>
