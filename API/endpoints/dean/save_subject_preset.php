<?php
/**
 * API/Dean/save_subject_preset.php
 * Toggle a subject preset on/off for a school_year + semester.
 * POST JSON: { subject_id, course_id, school_year, semester, action: 'add'|'remove' }
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../core/db_connect.php';

$body      = json_decode(file_get_contents('php://input'), true);
$action    = $body['action']      ?? '';
$subjectId = $body['subject_id']  ?? '';
$courseId  = $body['course_id']   ?? '';
$sy        = $body['school_year'] ?? '';
$sem       = $body['semester']    ?? '';
$setBy     = $_SESSION['user_id'] ?? null;

if (!$action || !$subjectId || !$courseId || !$sy || !$sem) {
    echo json_encode(['status'=>'error','message'=>'Missing parameters']);
    exit;
}

$sid   = mysqli_real_escape_string($conn, $subjectId);
$cid   = mysqli_real_escape_string($conn, $courseId);
$sy    = mysqli_real_escape_string($conn, $sy);
$sem   = mysqli_real_escape_string($conn, $sem);
$setBy = $setBy ? mysqli_real_escape_string($conn, $setBy) : 'NULL';

if ($action === 'add') {
    $sql = "
        INSERT IGNORE INTO tblsubjectpreset 
            (subject_id, course_id, school_year, semester, set_by)
        VALUES ('$sid','$cid','$sy','$sem'," . ($setBy === 'NULL' ? 'NULL' : "'$setBy'") . ")
    ";
    $conn->query($sql);
    echo json_encode(['status'=>'success','message'=>'Subject added to preset']);
} elseif ($action === 'remove') {
    $sql = "
        DELETE FROM tblsubjectpreset 
        WHERE subject_id='$sid' AND school_year='$sy' AND semester='$sem'
    ";
    $conn->query($sql);
    echo json_encode(['status'=>'success','message'=>'Subject removed from preset']);
} else {
    echo json_encode(['status'=>'error','message'=>'Invalid action']);
}

$conn->close();
