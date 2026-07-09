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

function tl_student_column_exists(mysqli $conn, string $table, string $column): bool {
    $stmt = $conn->prepare("
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
        LIMIT 1
    ");
    if (!$stmt) return false;
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

function tl_student_attach_recent_students(mysqli $conn, array $classes, bool $hasProfilePicture): array {
    if (empty($classes)) return $classes;

    $hasEnrollmentDate = tl_student_column_exists($conn, 'tblclassenrollment', 'enrolled_at');
    $hasClassStudentDate = tl_student_column_exists($conn, 'tblclassstudents', 'date_joined');
    $photoSelect = $hasProfilePicture ? "COALESCE(u.profile_picture, '') AS profile_picture" : "'' AS profile_picture";
    $photoGroupBy = $hasProfilePicture ? ",\n            u.profile_picture" : '';
    $enrolledAtSelect = $hasEnrollmentDate ? 'ce.enrolled_at' : 'NOW()';
    $joinedAtSelect = $hasClassStudentDate ? 'cs.date_joined' : 'NOW()';

    $sql = "
        SELECT
            s.id,
            s.user_id,
            TRIM(CONCAT(s.first_name, ' ', COALESCE(s.middle_name, ''), ' ', s.last_name)) AS full_name,
            s.first_name,
            s.last_name,
            s.student_number,
            {$photoSelect},
            MAX(src.joined_at) AS joined_at
        FROM (
            SELECT ce.class_id, ce.student_id AS student_ref, {$enrolledAtSelect} AS joined_at
            FROM tblclassenrollment ce
            WHERE ce.enrollment_status = 'enrolled'
            UNION ALL
            SELECT cs.class_id, cs.student_id AS student_ref, {$joinedAtSelect} AS joined_at
            FROM tblclassstudents cs
            WHERE cs.is_deleted = 0
        ) src
        JOIN tblstudent s
          ON (CAST(s.id AS CHAR) = src.student_ref OR s.user_id = src.student_ref)
         AND s.is_deleted = 0
        LEFT JOIN tbluser u
          ON (u.id = s.user_id OR u.email = s.email OR u.username = s.username)
         AND u.is_deleted = 0
        WHERE src.class_id = ?
        GROUP BY
            s.id,
            s.user_id,
            s.first_name,
            s.middle_name,
            s.last_name,
            s.student_number
            {$photoGroupBy}
        ORDER BY MAX(src.joined_at) DESC, s.last_name ASC, s.first_name ASC
        LIMIT 3
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        foreach ($classes as &$class) $class['recent_students'] = [];
        unset($class);
        return $classes;
    }

    foreach ($classes as &$class) {
        $classId = (string)($class['id'] ?? '');
        if ($classId === '') {
            $class['recent_students'] = [];
            continue;
        }
        $stmt->bind_param('s', $classId);
        $stmt->execute();
        $class['recent_students'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    unset($class);
    $stmt->close();

    return $classes;
}

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
$deptImageSelect = tl_student_column_exists($conn, 'tbldepartment', 'dept_image')
    ? 'd.dept_image'
    : 'NULL AS dept_image';

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
        d.dept_code,
        d.dept_name,
        {$deptImageSelect},
        f.id AS faculty_id,
        f.first_name AS faculty_first_name,
        f.middle_name AS faculty_middle_name,
        f.last_name AS faculty_last_name,
        f.email AS faculty_email,
        f.phone AS faculty_phone,
        f.is_dean AS faculty_is_dean,
        f.faculty_number AS faculty_number,
        CONCAT(TRIM(f.first_name), ' ', TRIM(f.last_name)) AS faculty_name,
        u.profile_picture AS faculty_profile_picture,
        (
            SELECT COUNT(DISTINCT roster.student_ref)
            FROM (
                SELECT ce2.class_id, ce2.student_id AS student_ref
                FROM tblclassenrollment ce2
                WHERE ce2.enrollment_status = 'enrolled'
                UNION ALL
                SELECT cs2.class_id, cs2.student_id AS student_ref
                FROM tblclassstudents cs2
                WHERE cs2.is_deleted = 0
            ) roster
            WHERE roster.class_id = c.id
        ) AS student_count
    FROM tblclassenrollment ce
    JOIN tblclass c ON c.id = ce.class_id AND c.is_deleted = 0
    LEFT JOIN tblsubject s ON s.id = c.subject_id
    LEFT JOIN tblcourse co ON co.id = c.course_id
    LEFT JOIN tbldepartment d ON d.id = co.department_id AND d.is_deleted = 0
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
$classes = tl_student_attach_recent_students(
    $conn,
    $classes,
    tl_student_column_exists($conn, 'tbluser', 'profile_picture')
);

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
