<?php
/**
 * API/facultyUI/classroom/attendance/get_attendance_detail.php
 * GET ?class_id=UUID&date=YYYY-MM-DD
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}
require_once __DIR__ . '/../../../../core/db_connect.php';
require_once __DIR__ . '/_helpers.php';

$class_id = trim($_GET['class_id'] ?? '');
$date     = trim($_GET['date']     ?? '');
if (!$class_id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['status'=>'error','message'=>'class_id and valid date required']); exit;
}

$faculty_id = att_resolve_faculty_id($conn, $_SESSION['user_id']);
if (!$faculty_id) { echo json_encode(['status'=>'error','message'=>'Faculty record not found']); exit; }

$class = att_verify_class_owner($conn, $class_id, $faculty_id);
if (!$class) { echo json_encode(['status'=>'error','message'=>'Class not found or access denied']); exit; }

$sem = att_class_semester($conn, $class['semester_setting_id'] ? (int)$class['semester_setting_id'] : null);
if (!$sem) { echo json_encode(['status'=>'error','message'=>'No semester configured']); exit; }

$can_edit = att_date_allowed($date, $sem);

$c = $conn->real_escape_string($class_id);
$d = $conn->real_escape_string($date);

/* Existing session? */
$sessRes = $conn->query("
    SELECT a.id, a.created_at, a.updated_at,
           cu.username AS created_by_username,
           uu.username AS updated_by_username
    FROM   tblattendance a
    LEFT JOIN tbluser cu ON cu.id = a.created_by
    LEFT JOIN tbluser uu ON uu.id = a.updated_by
    WHERE  a.class_id='$c' AND a.attendance_date='$d' AND a.is_deleted=0
    LIMIT  1
");
$session = ($sessRes && $sessRes->num_rows > 0) ? $sessRes->fetch_assoc() : null;

/* Saved statuses (if session exists) */
$saved = [];
if ($session) {
    $aid = $conn->real_escape_string($session['id']);
    $rRes = $conn->query("SELECT student_id, status FROM tblattendancerecord WHERE attendance_id='$aid'");
    if ($rRes) while ($row = $rRes->fetch_assoc()) $saved[$row['student_id']] = $row['status'];
}

/* Enrolled students (mirrors get_classroom.php) */
$pSQL = "
    SELECT s.id,
           TRIM(CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',s.last_name)) AS full_name,
           s.first_name, s.middle_name, s.last_name,
           s.student_number, s.year_level, s.section
    FROM   tblclassenrollment ce
    JOIN   tblstudent s ON s.id = ce.student_id AND s.is_deleted=0
    WHERE  ce.class_id='$c' AND ce.enrollment_status='enrolled'
    ORDER  BY s.last_name ASC, s.first_name ASC
";
$pRes = $conn->query($pSQL);
$students = [];
if ($pRes) {
    while ($row = $pRes->fetch_assoc()) {
        $sid = (string)$row['id'];
        $row['status'] = $saved[$sid] ?? 'present';   // default unmarked = present
        $students[] = $row;
    }
}

$conn->close();
echo json_encode([
    'status'   => 'success',
    'date'     => $date,
    'can_edit' => $can_edit,
    'session'  => $session,        // null if no record yet
    'students' => $students,
]);
