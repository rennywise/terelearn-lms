<?php
/**
 * API/student/get_student_classes.php
 * Returns enrolled classes and pending join-code admission requests.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$uid = $conn->real_escape_string($_SESSION['user_id']);
$sid = null;

$r = $conn->query("SELECT id FROM tblstudent WHERE user_id='$uid' AND is_deleted=0 LIMIT 1");
if ($r && $r->num_rows > 0) {
    $sid = $r->fetch_assoc()['id'];
}

if (!$sid) {
    $ur = $conn->query("SELECT username, email FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
    if ($ur && $urow = $ur->fetch_assoc()) {
        $un = $conn->real_escape_string($urow['username'] ?? '');
        $em = $conn->real_escape_string($urow['email'] ?? '');
        $r2 = $conn->query("SELECT id FROM tblstudent
                            WHERE (username='$un' OR email='$em')
                              AND is_deleted=0 LIMIT 1");
        if ($r2 && $r2->num_rows > 0) {
            $sid = $r2->fetch_assoc()['id'];
        }
    }
}

if (!$sid) {
    echo json_encode(['status' => 'success', 'classes' => [], 'pending_requests' => []]);
    exit;
}

$esc_sid = $conn->real_escape_string($sid);

$sql = "
    SELECT
        c.id,
        c.class_code,
        c.join_code,
        c.class_semester,
        c.class_days,
        c.schedule,
        c.year_level,
        c.section,
        c.banner_palette,
        s.subject_code,
        s.subject_name,
        co.course_code,
        co.course_name,
        f.id AS faculty_id,
        f.first_name AS faculty_first_name,
        f.middle_name AS faculty_middle_name,
        f.last_name AS faculty_last_name,
        f.email AS faculty_email,
        f.phone AS faculty_phone,
        f.is_dean AS faculty_is_dean,
        f.faculty_number AS faculty_number,
        CONCAT(TRIM(f.first_name), ' ', TRIM(f.last_name)) AS faculty_name,
        u.profile_picture AS faculty_profile_picture
    FROM tblclassenrollment ce
    JOIN tblclass c ON c.id = ce.class_id AND c.is_deleted = 0
    LEFT JOIN tblsubject s ON s.id = c.subject_id
    LEFT JOIN tblcourse co ON co.id = c.course_id
    LEFT JOIN tblfaculty f ON f.id = c.faculty_id AND f.is_deleted = 0
    LEFT JOIN tbladmin a ON (a.email = f.email OR a.username = f.username) AND a.is_deleted = 0
    LEFT JOIN tbluser u ON (u.email = a.email OR u.username = a.username) AND u.is_deleted = 0
    WHERE ce.student_id = '$esc_sid'
      AND ce.enrollment_status = 'enrolled'
      AND c.is_active = 1
    ORDER BY c.class_semester DESC, s.subject_name ASC
";

$res = $conn->query($sql);
$classes = [];
if ($res) {
    while ($row = $res->fetch_assoc()) $classes[] = $row;
}

$pendingSql = "
    SELECT
        c.id AS class_id,
        c.class_code,
        c.join_code,
        c.class_semester,
        c.section,
        c.year_level,
        ce.enrolled_at AS requested_at,
        s.subject_code,
        s.subject_name,
        CONCAT(TRIM(f.first_name), ' ', TRIM(f.last_name)) AS faculty_name
    FROM tblclassenrollment ce
    JOIN tblclass c ON c.id = ce.class_id AND c.is_deleted = 0
    LEFT JOIN tblsubject s ON s.id = c.subject_id
    LEFT JOIN tblfaculty f ON f.id = c.faculty_id AND f.is_deleted = 0
    WHERE ce.student_id = '$esc_sid'
      AND ce.enrollment_status = 'pending'
      AND c.is_active = 1
    ORDER BY ce.enrolled_at DESC
";

$pendingRes = $conn->query($pendingSql);
$pending = [];
if ($pendingRes) {
    while ($row = $pendingRes->fetch_assoc()) $pending[] = $row;
}

$conn->close();
echo json_encode(['status' => 'success', 'classes' => $classes, 'pending_requests' => $pending]);
