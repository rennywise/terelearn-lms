<?php
/**
 * API/facultyUI/classroom/manage_enrollment.php
 * Faculty approves or removes pending/admitted students.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

function gen_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
    );
}

$input      = json_decode(file_get_contents('php://input'), true) ?? [];
$class_id   = trim($input['class_id'] ?? '');
$student_id = trim($input['student_id'] ?? '');
$action     = trim($input['action'] ?? '');
$faculty_id = $_SESSION['user_id'];

if (!$class_id || !$student_id || !in_array($action, ['approve', 'remove'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

$esc_class   = $conn->real_escape_string($class_id);
$esc_student = $conn->real_escape_string($student_id);
$esc_faculty = $conn->real_escape_string($faculty_id);

// Resolve tblfaculty.id from session user_id (tbluser.id)
$fac_id = null;
$fur = $conn->query("SELECT f.id FROM tblfaculty f
    JOIN tbluser u ON (u.email = f.email OR u.username = f.username)
    WHERE u.id = '$esc_faculty' AND f.is_deleted = 0 LIMIT 1");
if ($fur && $fur->num_rows > 0) {
    $fac_id = $conn->real_escape_string($fur->fetch_assoc()['id']);
}
if (!$fac_id) {
    echo json_encode(['status' => 'error', 'message' => 'Faculty record not found']);
    exit;
}

$chk = $conn->query("SELECT id FROM tblclass
                    WHERE id='$esc_class'
                      AND faculty_id='$fac_id'
                      AND is_deleted=0
                    LIMIT 1");
if (!$chk || $chk->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

if ($action === 'approve') {
    /* Only approve student-initiated join requests. Invitations are accepted by the student, not the instructor. */
    $enrChk = $conn->query("SELECT id FROM tblclassenrollment
                           WHERE class_id='$esc_class' AND student_id='$esc_student'
                             AND source='join_request'
                             AND enrollment_status='pending'
                           LIMIT 1");
    if (!$enrChk || $enrChk->num_rows === 0) {
        echo json_encode(['status'=>'error','message'=>'No pending join request to approve.']);
        exit;
    }

    $conn->query("UPDATE tblclassenrollment
                  SET enrollment_status='enrolled', dropped_at=NULL
                  WHERE class_id='$esc_class' AND student_id='$esc_student'
                    AND source='join_request'");
    /* Do NOT touch tblinvitations here — that's a separate flow. */

    $csChk = $conn->query("SELECT id FROM tblclassstudents
                          WHERE class_id='$esc_class'
                            AND student_id='$esc_student'
                            AND is_deleted=0
                          LIMIT 1");
    if (!$csChk || $csChk->num_rows === 0) {
        $csId = $conn->real_escape_string(gen_uuid());
        $conn->query("INSERT INTO tblclassstudents (id, class_id, student_id)
                      VALUES ('$csId', '$esc_class', '$esc_student')");
    }

    $conn->query("UPDATE tblnotifications
                  SET is_read=1
                  WHERE user_id='$esc_faculty'
                    AND related_id='$esc_class'
                    AND title='Student admission request'");

    $conn->close();
    echo json_encode(['status' => 'success', 'message' => 'Student approved and enrolled.']);
    exit;
}

$conn->query("UPDATE tblclassenrollment
              SET enrollment_status='dropped', dropped_at=NOW()
              WHERE class_id='$esc_class' AND student_id='$esc_student'");

$conn->query("UPDATE tblinvitations
              SET invitation_status='declined', responded_at=NOW()
              WHERE class_id='$esc_class' AND student_id='$esc_student'");

$conn->query("UPDATE tblclassstudents
              SET is_deleted=1
              WHERE class_id='$esc_class' AND student_id='$esc_student'");

$conn->query("UPDATE tblnotifications
              SET is_read=1
              WHERE user_id='$esc_faculty'
                AND related_id='$esc_class'
                AND title='Student admission request'");

$conn->close();
echo json_encode(['status' => 'success', 'message' => 'Student removed from class.']);
