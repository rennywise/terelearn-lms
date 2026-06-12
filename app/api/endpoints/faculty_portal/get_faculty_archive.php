<?php
/**
 * API/facultyUI/get_faculty_archive.php
 *
 * Returns this faculty's classes from PAST semesters, grouped as:
 *
 *   archived_groups: {
 *     "2025-2026": {
 *       "2nd Semester": [ ...classes ],
 *       "1st Semester": [ ...classes ]
 *     },
 *     "2024-2025": { ... }
 *   }
 *
 * A class is archived when its semester_setting_id points to a
 * tblsemestersetting row that is NOT the currently active one.
 */
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../core/db_connect.php';

/* ── Auth guard ── */
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

/* ── Get tbluser record ── */
$uStmt = $conn->prepare(
    "SELECT id, username, email FROM tbluser WHERE id = ? AND is_deleted = 0 LIMIT 1"
);
$uStmt->bind_param('s', $user_id);
$uStmt->execute();
$tblUser = $uStmt->get_result()->fetch_assoc();
$uStmt->close();

if (!$tblUser) {
    echo json_encode(['status' => 'error', 'message' => 'User session invalid.']);
    exit;
}

$username = $tblUser['username'];
$email    = $tblUser['email'];
$faculty  = null;

/* ── 4-strategy faculty lookup ── */
if (!$faculty && $username) {
    $s = $conn->prepare("SELECT * FROM tblfaculty WHERE username = ? AND is_deleted = 0 LIMIT 1");
    $s->bind_param('s', $username); $s->execute();
    $faculty = $s->get_result()->fetch_assoc(); $s->close();
}
if (!$faculty && $email) {
    $s = $conn->prepare("SELECT * FROM tblfaculty WHERE email = ? AND is_deleted = 0 LIMIT 1");
    $s->bind_param('s', $email); $s->execute();
    $faculty = $s->get_result()->fetch_assoc(); $s->close();
}
if (!$faculty) {
    $col = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblfaculty'
        AND COLUMN_NAME = 'user_id' LIMIT 1");
    if ($col && $col->num_rows > 0) {
        $s = $conn->prepare("SELECT * FROM tblfaculty WHERE user_id = ? AND is_deleted = 0 LIMIT 1");
        $s->bind_param('s', $user_id); $s->execute();
        $faculty = $s->get_result()->fetch_assoc(); $s->close();
    }
}
if (!$faculty && isset($_SESSION['faculty_id'])) {
    $s = $conn->prepare("SELECT * FROM tblfaculty WHERE id = ? AND is_deleted = 0 LIMIT 1");
    $s->bind_param('s', $_SESSION['faculty_id']); $s->execute();
    $faculty = $s->get_result()->fetch_assoc(); $s->close();
}

if (!$faculty) {
    echo json_encode(['status' => 'success', 'archived_groups' => []]);
    exit;
}

$faculty_id = $faculty['id'];

/* ── Get ACTIVE semester id so we can EXCLUDE it ── */
$semRes        = $conn->query("SELECT id FROM tblsemestersetting WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$activeSemRow  = $semRes ? $semRes->fetch_assoc() : null;
$active_sem_id = (int)($activeSemRow['id'] ?? 0);

/* ── Fetch classes from PAST semesters only ──
 *
 *  We JOIN tblsemestersetting to get school_year + semester for grouping.
 *  Conditions:
 *    - semester_setting_id IS NOT NULL      (stamped by new system)
 *    - semester_setting_id != active id     (not the current semester)
 *    - is_deleted = 0
 */
$cStmt = $conn->prepare("
    SELECT
        c.id,
        c.class_code,
        c.section,
        c.class_semester,
        c.semester_setting_id,
        c.year_level,
        c.schedule,
        c.break_time,
        c.class_days,
        c.is_active,
        c.created_at,
        c.course_id,
        IFNULL(c.source, 'admin') AS source,
        sub.subject_code,
        sub.subject_name,
        sub.id AS subject_id,
        crs.course_code,
        crs.course_name,
        sem.school_year AS sem_school_year,
        sem.semester    AS sem_semester,
        (
            SELECT COUNT(*) FROM tblclassstudents cs
            WHERE cs.class_id = c.id AND cs.is_deleted = 0
        ) AS student_count
    FROM tblclass c
    INNER JOIN tblsemestersetting sem ON sem.id = c.semester_setting_id
    LEFT JOIN  tblsubject sub ON sub.id = c.subject_id
    LEFT JOIN  tblcourse  crs ON crs.id = c.course_id
    WHERE c.faculty_id            = ?
      AND c.is_deleted             = 0
      AND c.semester_setting_id   IS NOT NULL
      AND c.semester_setting_id   != ?
    ORDER BY sem.school_year DESC, sem.id DESC, c.created_at DESC
");
$cStmt->bind_param('si', $faculty_id, $active_sem_id);
$cStmt->execute();
$rows = $cStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$cStmt->close();
$conn->close();

/* ── Group: school_year → { label, semesters: { semName: [classes] } } ── */
/* JS in facultyUI.php expects: archivedGroups[sy].semesters[sem] = [...classes]  */
$archived_groups = [];

foreach ($rows as $cls) {
    $sy  = $cls['sem_school_year']; // e.g. "2025-2026"
    $sem = $cls['sem_semester'];    // e.g. "2nd Semester"

    if (!isset($archived_groups[$sy])) {
        $archived_groups[$sy] = [
            'label'     => $sy,
            'semesters' => []
        ];
    }
    if (!isset($archived_groups[$sy]['semesters'][$sem])) {
        $archived_groups[$sy]['semesters'][$sem] = [];
    }

    unset($cls['sem_school_year'], $cls['sem_semester']);
    $archived_groups[$sy]['semesters'][$sem][] = $cls;
}

/* Sort school years descending (most recent first) */
krsort($archived_groups);

/* Sort semesters within each year: 2nd Sem first, then 1st Sem, then Summer */
$semOrder = ['2nd Semester' => 0, '1st Semester' => 1, 'Summer' => 2];
foreach ($archived_groups as $sy => &$syData) {
    uksort($syData['semesters'], function($a, $b) use ($semOrder) {
        return ($semOrder[$a] ?? 99) - ($semOrder[$b] ?? 99);
    });
}
unset($syData);

echo json_encode([
    'status'          => 'success',
    'archived_groups' => $archived_groups,
]);
