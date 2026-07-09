<?php
/**
 * API/facultyUI/classroom/report_ajax.php
 * Accomplishment report data and draft saving for one classroom.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';
require_once __DIR__ . '/attendance/_helpers.php';
require_once __DIR__ . '/gradebook/_helpers.php';

function ar_json(array $payload): void {
    global $conn;
    if ($conn instanceof mysqli) $conn->close();
    echo json_encode($payload);
    exit;
}

function ar_table_exists(mysqli $conn, string $table): bool {
    $table = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    return $res && $res->num_rows > 0;
}

function ar_column_exists(mysqli $conn, string $table, string $column): bool {
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $res && $res->num_rows > 0;
}

function ar_ensure_table(mysqli $conn): bool {
    if (ar_table_exists($conn, 'accomplishment_reports')) {
        if (ar_column_exists($conn, 'accomplishment_reports', 'class_id')) {
            if (!ar_column_exists($conn, 'accomplishment_reports', 'photo_documentation')) {
                $conn->query("ALTER TABLE accomplishment_reports ADD COLUMN photo_documentation LONGTEXT NULL AFTER lab_activities");
            }
            return true;
        }

        $countRes = $conn->query("SELECT COUNT(*) AS total FROM accomplishment_reports");
        $count = ($countRes && ($row = $countRes->fetch_assoc())) ? (int)$row['total'] : 0;
        if ($count > 0) return false;
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        $conn->query("DROP TABLE accomplishment_reports");
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
    }

    return (bool)$conn->query("
        CREATE TABLE accomplishment_reports (
          id INT AUTO_INCREMENT PRIMARY KEY,
          class_id CHAR(36) NOT NULL,
          subject_id CHAR(36) NULL,
          section_label VARCHAR(50) NULL,
          faculty_id CHAR(36) NOT NULL,
          session_id CHAR(36) NOT NULL,
          academic_week VARCHAR(10) NULL,
          units INT NULL,
          date_covered VARCHAR(80) NULL,
          time_conducted VARCHAR(50) NULL,
          duration VARCHAR(30) NULL,
          topics_covered TEXT NULL,
          sync_activities TEXT NULL,
          async_activities TEXT NULL,
          lab_activities TEXT NULL,
          photo_documentation LONGTEXT NULL,
          faculty_signature VARCHAR(120) NULL,
          dean_name VARCHAR(100) NULL,
          date_submitted DATE NULL,
          hrd_received_date DATE NULL,
          status ENUM('draft','submitted') DEFAULT 'draft',
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          UNIQUE KEY uniq_accomplishment_session (session_id),
          KEY idx_accomplishment_class (class_id),
          KEY idx_accomplishment_subject (subject_id),
          KEY idx_accomplishment_faculty (faculty_id),
          CONSTRAINT fk_accomplishment_class FOREIGN KEY (class_id) REFERENCES tblclass(id) ON DELETE CASCADE,
          CONSTRAINT fk_accomplishment_subject FOREIGN KEY (subject_id) REFERENCES tblsubject(id) ON DELETE SET NULL,
          CONSTRAINT fk_accomplishment_faculty FOREIGN KEY (faculty_id) REFERENCES tblfaculty(id) ON DELETE CASCADE,
          CONSTRAINT fk_accomplishment_session FOREIGN KEY (session_id) REFERENCES tblattendance(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
}

function ar_class_size(mysqli $conn, string $classId): int {
    $c = $conn->real_escape_string($classId);
    $res = $conn->query("SELECT COUNT(*) AS total FROM tblclassenrollment WHERE class_id='$c' AND enrollment_status='enrolled'");
    return ($res && ($row = $res->fetch_assoc())) ? (int)$row['total'] : 0;
}

function ar_faculty_status(mysqli $conn, string $facultyId): string {
    foreach (['employment_status', 'employment_type', 'faculty_status', 'status'] as $column) {
        if (!ar_column_exists($conn, 'tblfaculty', $column)) continue;
        $f = $conn->real_escape_string($facultyId);
        $col = str_replace('`', '', $column);
        $res = $conn->query("SELECT `$col` AS value FROM tblfaculty WHERE id='$f' LIMIT 1");
        if ($res && ($row = $res->fetch_assoc())) {
            $value = trim((string)($row['value'] ?? ''));
            if ($value !== '') return $value;
        }
    }
    return 'Not set';
}

function ar_clean_name(string $name): string {
    $name = preg_replace('/\b(n\/a|na|none|null)\b/i', ' ', $name);
    return trim(preg_replace('/\s+/', ' ', (string)$name));
}

function ar_dean_name(mysqli $conn, ?int $departmentId): string {
    if (!$departmentId) return '';
    $dept = (int)$departmentId;
    $res = $conn->query("
        SELECT TRIM(CONCAT(a.first_name, ' ', COALESCE(NULLIF(a.middle_name, ''), ''), CASE WHEN COALESCE(NULLIF(a.middle_name, ''), '') <> '' THEN ' ' ELSE '' END, a.last_name)) AS dean_name
        FROM tbldeanassignment da
        JOIN tbladmin a ON a.id = da.faculty_id AND a.is_deleted = 0
        WHERE da.department_id = $dept
          AND da.role = 'dean'
          AND da.is_active = 1
        ORDER BY da.assigned_at DESC
        LIMIT 1
    ");
    if ($res && ($row = $res->fetch_assoc())) return ar_clean_name((string)$row['dean_name']);
    return '';
}

function ar_section_label(array $context): string {
    $course = trim((string)($context['course_code'] ?? ''));
    $section = trim((string)($context['section'] ?? ''));
    if ($course !== '' && $section !== '' && stripos($section, $course) === false) return $course . ' ' . $section;
    return $section !== '' ? $section : $course;
}

function ar_class_context(mysqli $conn, string $classId, string $facultyId): ?array {
    $c = $conn->real_escape_string($classId);
    $f = $conn->real_escape_string($facultyId);
    $res = $conn->query("
        SELECT c.id AS class_id, c.class_code, c.subject_id, c.course_id, c.faculty_id,
               c.section, c.year_level, c.class_semester, c.semester_setting_id,
               s.subject_code, s.subject_name,
               co.course_code, co.course_name,
               d.id AS department_id, d.dept_code, d.dept_name,
               TRIM(CONCAT(fa.first_name, ' ', COALESCE(NULLIF(fa.middle_name, ''), ''), CASE WHEN COALESCE(NULLIF(fa.middle_name, ''), '') <> '' THEN ' ' ELSE '' END, fa.last_name)) AS faculty_name
        FROM tblclass c
        LEFT JOIN tblsubject s ON s.id = c.subject_id
        LEFT JOIN tblcourse co ON co.id = c.course_id
        LEFT JOIN tbldepartment d ON d.id = co.department_id AND d.is_deleted = 0
        LEFT JOIN tblfaculty fa ON fa.id = c.faculty_id
        WHERE c.id = '$c' AND c.faculty_id = '$f' AND c.is_deleted = 0
        LIMIT 1
    ");
    if (!$res || $res->num_rows === 0) return null;
    $ctx = $res->fetch_assoc();
    $ctx['faculty_name'] = ar_clean_name((string)($ctx['faculty_name'] ?? ''));
    $ctx['class_size'] = ar_class_size($conn, $classId);
    $ctx['section_label'] = ar_section_label($ctx);
    $ctx['employment_status'] = ar_faculty_status($conn, (string)$ctx['faculty_id']);
    $ctx['dean_name'] = ar_dean_name($conn, $ctx['department_id'] !== null ? (int)$ctx['department_id'] : null);
    return $ctx;
}

function ar_semester_label(?array $sem): string {
    if (!$sem) return '';
    return trim((string)$sem['semester'] . ' ' . (string)$sem['school_year']);
}

function ar_date_long(?string $date): string {
    if (!$date) return '';
    $ts = strtotime($date);
    return $ts ? date('F j, Y', $ts) : $date;
}

function ar_sessions(mysqli $conn, string $classId, int $classSize): array {
    $c = $conn->real_escape_string($classId);
    $res = $conn->query("
        SELECT a.id, a.attendance_date,
               COUNT(r.id) AS total_records,
               SUM(CASE WHEN r.status='present' THEN 1 ELSE 0 END) AS present_count,
               SUM(CASE WHEN r.status='absent' THEN 1 ELSE 0 END) AS absent_count,
               ar.id AS report_id,
               ar.status AS report_status
        FROM tblattendance a
        LEFT JOIN tblattendancerecord r ON r.attendance_id = a.id
        LEFT JOIN accomplishment_reports ar ON ar.session_id = a.id
        WHERE a.class_id = '$c' AND a.is_deleted = 0
        GROUP BY a.id, a.attendance_date, ar.id, ar.status
        ORDER BY a.attendance_date DESC
    ");
    $sessions = [];
    if (!$res) return $sessions;
    while ($row = $res->fetch_assoc()) {
        $present = (int)$row['present_count'];
        $total = $classSize > 0 ? $classSize : (int)$row['total_records'];
        $absent = max(0, $total - $present);
        $pct = $total > 0 ? round(($present / $total) * 100) : 0;
        $sessions[] = [
            'id' => (string)$row['id'],
            'attendance_date' => (string)$row['attendance_date'],
            'date_label' => ar_date_long((string)$row['attendance_date']),
            'present' => $present,
            'absent' => $absent,
            'class_size' => $total,
            'percentage' => $pct,
            'report_id' => $row['report_id'] !== null ? (int)$row['report_id'] : null,
            'report_status' => $row['report_status'] ?? null,
        ];
    }
    return $sessions;
}

function ar_report_for_session(mysqli $conn, string $sessionId): ?array {
    $s = $conn->real_escape_string($sessionId);
    $res = $conn->query("SELECT * FROM accomplishment_reports WHERE session_id='$s' LIMIT 1");
    return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
}

function ar_session_detail(mysqli $conn, string $classId, string $sessionId, array $ctx, ?array $sem): ?array {
    $c = $conn->real_escape_string($classId);
    $s = $conn->real_escape_string($sessionId);
    $sessionRes = $conn->query("SELECT id, attendance_date FROM tblattendance WHERE id='$s' AND class_id='$c' AND is_deleted=0 LIMIT 1");
    if (!$sessionRes || $sessionRes->num_rows === 0) return null;
    $session = $sessionRes->fetch_assoc();

    $studentsRes = $conn->query("
        SELECT s.id,
               TRIM(CONCAT(s.first_name, ' ', COALESCE(NULLIF(s.middle_name, ''), ''), CASE WHEN COALESCE(NULLIF(s.middle_name, ''), '') <> '' THEN ' ' ELSE '' END, s.last_name)) AS full_name,
               s.student_number,
               COALESCE(r.status, 'absent') AS attendance_status
        FROM tblclassenrollment ce
        JOIN tblstudent s ON s.id = ce.student_id AND s.is_deleted = 0
        LEFT JOIN tblattendancerecord r ON r.attendance_id = '$s' AND r.student_id = s.id
        WHERE ce.class_id = '$c' AND ce.enrollment_status = 'enrolled'
        ORDER BY s.last_name ASC, s.first_name ASC
    ");
    $present = [];
    $absent = [];
    if ($studentsRes) {
        while ($row = $studentsRes->fetch_assoc()) {
            $student = [
                'id' => (string)$row['id'],
                'full_name' => ar_clean_name((string)$row['full_name']),
                'student_number' => (string)($row['student_number'] ?? ''),
            ];
            if ($row['attendance_status'] === 'present') $present[] = $student;
            else $absent[] = $student;
        }
    }

    $classSize = count($present) + count($absent);
    $attendees = count($present);
    $report = ar_report_for_session($conn, $sessionId);

    return [
        'session' => [
            'id' => (string)$session['id'],
            'attendance_date' => (string)$session['attendance_date'],
            'date_label' => ar_date_long((string)$session['attendance_date']),
            'percentage' => $classSize > 0 ? round(($attendees / $classSize) * 100) : 0,
        ],
        'auto' => [
            'faculty_name' => (string)($ctx['faculty_name'] ?? ''),
            'subject_name' => (string)($ctx['subject_name'] ?: $ctx['subject_code'] ?: ''),
            'section_label' => (string)($ctx['section_label'] ?? ''),
            'department_name' => (string)($ctx['dept_name'] ?? ''),
            'employment_status' => (string)($ctx['employment_status'] ?? 'Not set'),
            'class_size' => $classSize,
            'attendees_count' => $attendees,
            'absent_count' => max(0, $classSize - $attendees),
            'date_conducted' => ar_date_long((string)$session['attendance_date']),
            'date_conducted_raw' => (string)$session['attendance_date'],
            'semester_ay' => ar_semester_label($sem),
            'date_of_affectivity' => $sem && !empty($sem['start_date']) ? ar_date_long((string)$sem['start_date']) : ar_date_long(date('Y-m-d')),
            'subject_id' => (string)($ctx['subject_id'] ?? ''),
            'faculty_id' => (string)($ctx['faculty_id'] ?? ''),
            'class_id' => (string)($ctx['class_id'] ?? ''),
        ],
        'present' => $present,
        'absent' => $absent,
        'report' => $report,
        'analytics' => ar_analytics($conn, $classId),
    ];
}

function ar_analytics(mysqli $conn, string $classId): array {
    $c = $conn->real_escape_string($classId);
    $sessionRes = $conn->query("SELECT COUNT(*) AS total FROM tblattendance WHERE class_id='$c' AND is_deleted=0");
    $sessionCount = ($sessionRes && ($row = $sessionRes->fetch_assoc())) ? (int)$row['total'] : 0;

    $attendance = [];
    $attRes = $conn->query("
        SELECT s.id,
               SUM(CASE WHEN r.status='present' THEN 1 ELSE 0 END) AS present_count,
               SUM(CASE WHEN r.status='absent' THEN 1 ELSE 0 END) AS absent_count
        FROM tblclassenrollment ce
        JOIN tblstudent s ON s.id = ce.student_id AND s.is_deleted = 0
        LEFT JOIN tblattendance a ON a.class_id = '$c' AND a.is_deleted = 0
        LEFT JOIN tblattendancerecord r ON r.attendance_id = a.id AND r.student_id = s.id
        WHERE ce.class_id = '$c' AND ce.enrollment_status = 'enrolled'
        GROUP BY s.id
    ");
    if ($attRes) {
        while ($row = $attRes->fetch_assoc()) {
            $attendance[(string)$row['id']] = [
                'present' => (int)$row['present_count'],
                'absent' => (int)$row['absent_count'],
            ];
        }
    }

    $gradebook = gb_get_gradebook($conn, $classId);
    $rows = [];
    foreach (($gradebook['students'] ?? []) as $student) {
        $sid = (string)($student['student_id'] ?? '');
        $present = $attendance[$sid]['present'] ?? 0;
        $absent = $attendance[$sid]['absent'] ?? 0;
        $attendanceRate = $sessionCount > 0 ? round(($present / $sessionCount) * 100, 1) : 0.0;
        $gradeAvg = round((float)($student['final_grade'] ?? 0), 1);
        $gradedItems = 0;
        foreach (($student['categories'] ?? []) as $cat) {
            $gradedItems += (int)($cat['items'] ?? 0);
        }
        $rows[] = [
            'student_id' => $sid,
            'student_number' => (string)($student['student_number'] ?? ''),
            'student_name' => ar_clean_name((string)($student['student_name'] ?? 'Unknown')),
            'attendance_rate' => $attendanceRate,
            'grade_average' => $gradeAvg,
            'graded_items' => $gradedItems,
            'absent_count' => $absent,
            'cluster' => 'Average',
            'cluster_key' => 'average',
            'remarks' => 'Monitor progress',
        ];
    }

    if (!$rows) return [];

    $centroids = [
        'high' => ['attendance' => 90.0, 'grade' => 90.0],
        'average' => ['attendance' => 75.0, 'grade' => 75.0],
        'at_risk' => ['attendance' => 55.0, 'grade' => 55.0],
    ];
    $labels = ['high' => 'High', 'average' => 'Average', 'at_risk' => 'At Risk'];

    for ($iter = 0; $iter < 10; $iter++) {
        foreach ($rows as &$row) {
            $bestKey = 'average';
            $bestDistance = INF;
            foreach ($centroids as $key => $centroid) {
                $distance = sqrt(
                    pow((float)$row['attendance_rate'] - $centroid['attendance'], 2)
                    + pow((float)$row['grade_average'] - $centroid['grade'], 2)
                );
                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestKey = $key;
                }
            }
            $row['cluster_key'] = $bestKey;
            $row['cluster'] = $labels[$bestKey];
        }
        unset($row);

        foreach (array_keys($centroids) as $key) {
            $group = array_values(array_filter($rows, fn($row) => $row['cluster_key'] === $key));
            if (!$group) continue;
            $centroids[$key] = [
                'attendance' => array_sum(array_column($group, 'attendance_rate')) / count($group),
                'grade' => array_sum(array_column($group, 'grade_average')) / count($group),
            ];
        }
    }

    foreach ($rows as &$row) {
        if ($row['graded_items'] <= 0) {
            $row['remarks'] = 'Needs grading data';
        } elseif ($row['cluster_key'] === 'high') {
            $row['remarks'] = 'Good standing';
        } elseif ($row['cluster_key'] === 'average') {
            $row['remarks'] = 'Monitor progress';
        } else {
            $row['remarks'] = 'Needs intervention';
        }
    }
    unset($row);

    $rank = ['high' => 0, 'average' => 1, 'at_risk' => 2];
    usort($rows, fn($a, $b) => [($rank[$a['cluster_key']] ?? 9), $a['student_name']] <=> [($rank[$b['cluster_key']] ?? 9), $b['student_name']]);
    return $rows;
}

function ar_clean_string(array $data, string $key, int $max = 0): ?string {
    $value = trim((string)($data[$key] ?? ''));
    if ($value === '') return null;
    return $max > 0 ? substr($value, 0, $max) : $value;
}

function ar_clean_photo_documentation(array $data): ?string {
    $value = trim((string)($data['photo_documentation'] ?? ''));
    if ($value === '') return null;
    if (strlen($value) > 2500000) return null;
    return preg_match('/^data:image\/(png|jpe?g|webp);base64,[A-Za-z0-9+\/=]+$/', $value) ? $value : null;
}

if (!$conn) ar_json(['status' => 'error', 'message' => 'Database connection failed']);
if (!ar_ensure_table($conn)) ar_json(['status' => 'error', 'message' => 'accomplishment_reports table needs migration']);

$rawInput = file_get_contents('php://input');
if (PHP_SAPI === 'cli') {
    $stdinInput = stream_get_contents(STDIN);
    if (trim($stdinInput) !== '') $rawInput = $stdinInput;
}
$rawInput = preg_replace('/^\xEF\xBB\xBF/', '', (string)$rawInput);
$payload = json_decode($rawInput, true) ?: [];
$action = $_GET['action'] ?? $payload['action'] ?? '';
$classId = trim((string)($_GET['class_id'] ?? $payload['class_id'] ?? ''));
if ($classId === '') ar_json(['status' => 'error', 'message' => 'class_id required']);

$facultyId = att_resolve_faculty_id($conn, (string)$_SESSION['user_id']);
if (!$facultyId) ar_json(['status' => 'error', 'message' => 'Faculty record not found']);

$ctx = ar_class_context($conn, $classId, $facultyId);
if (!$ctx) ar_json(['status' => 'error', 'message' => 'Class not found or access denied']);

$sem = att_class_semester($conn, $ctx['semester_setting_id'] ? (int)$ctx['semester_setting_id'] : null);
$sessions = ar_sessions($conn, $classId, (int)$ctx['class_size']);

if ($action === 'bootstrap') {
    ar_json([
        'status' => 'success',
        'context' => [
            'class_id' => (string)$ctx['class_id'],
            'class_code' => (string)$ctx['class_code'],
            'faculty_name' => (string)$ctx['faculty_name'],
            'subject_name' => (string)($ctx['subject_name'] ?: $ctx['subject_code'] ?: ''),
            'section_label' => (string)$ctx['section_label'],
            'department_name' => (string)($ctx['dept_name'] ?? ''),
            'employment_status' => (string)$ctx['employment_status'],
            'class_size' => (int)$ctx['class_size'],
            'semester_ay' => ar_semester_label($sem),
            'dean_name' => (string)$ctx['dean_name'],
            'date_of_affectivity' => $sem && !empty($sem['start_date']) ? ar_date_long((string)$sem['start_date']) : ar_date_long(date('Y-m-d')),
        ],
        'sessions' => $sessions,
        'analytics' => ar_analytics($conn, $classId),
        'today' => date('Y-m-d'),
    ]);
}

if ($action === 'get_session') {
    $sessionId = trim((string)($_GET['session_id'] ?? $payload['session_id'] ?? ''));
    if ($sessionId === '') ar_json(['status' => 'error', 'message' => 'session_id required']);
    $detail = ar_session_detail($conn, $classId, $sessionId, $ctx, $sem);
    if (!$detail) ar_json(['status' => 'error', 'message' => 'Attendance session not found']);
    ar_json(['status' => 'success'] + $detail);
}

if ($action === 'save_report') {
    $sessionId = trim((string)($payload['session_id'] ?? ''));
    if ($sessionId === '') ar_json(['status' => 'error', 'message' => 'Select a session first']);
    $detail = ar_session_detail($conn, $classId, $sessionId, $ctx, $sem);
    if (!$detail) ar_json(['status' => 'error', 'message' => 'Attendance session not found']);

    $subjectId = $ctx['subject_id'] ? (string)$ctx['subject_id'] : null;
    $sectionLabel = ar_section_label($ctx);
    $status = (($payload['status'] ?? 'draft') === 'submitted') ? 'submitted' : 'draft';
    $unitsRaw = trim((string)($payload['units'] ?? ''));
    $units = $unitsRaw === '' ? null : (int)$unitsRaw;
    $dateSubmitted = ar_clean_string($payload, 'date_submitted', 20);
    $hrdReceived = ar_clean_string($payload, 'hrd_received_date', 20);
    $photoDocumentation = ar_clean_photo_documentation($payload);

    $existing = ar_report_for_session($conn, $sessionId);
    if ($existing) {
        $stmt = $conn->prepare("
            UPDATE accomplishment_reports
            SET academic_week=?, units=?, date_covered=?, time_conducted=?, duration=?,
                topics_covered=?, sync_activities=?, async_activities=?, lab_activities=?,
                photo_documentation=?, faculty_signature=?, dean_name=?, date_submitted=?, hrd_received_date=?, status=?
            WHERE session_id=?
        ");
        $academicWeek = ar_clean_string($payload, 'academic_week', 10);
        $dateCovered = ar_clean_string($payload, 'date_covered', 80);
        $timeConducted = ar_clean_string($payload, 'time_conducted', 50);
        $duration = ar_clean_string($payload, 'duration', 30);
        $topics = ar_clean_string($payload, 'topics_covered');
        $sync = ar_clean_string($payload, 'sync_activities');
        $async = ar_clean_string($payload, 'async_activities');
        $lab = ar_clean_string($payload, 'lab_activities');
        $facultySignature = ar_clean_string($payload, 'faculty_signature', 120);
        $deanName = ar_clean_string($payload, 'dean_name', 100);
        $stmt->bind_param(
            'sissssssssssssss',
            $academicWeek,
            $units,
            $dateCovered,
            $timeConducted,
            $duration,
            $topics,
            $sync,
            $async,
            $lab,
            $photoDocumentation,
            $facultySignature,
            $deanName,
            $dateSubmitted,
            $hrdReceived,
            $status,
            $sessionId
        );
        $ok = $stmt->execute();
        $reportId = (int)$existing['id'];
    } else {
        $stmt = $conn->prepare("
            INSERT INTO accomplishment_reports
              (class_id, subject_id, section_label, faculty_id, session_id, academic_week, units,
               date_covered, time_conducted, duration, topics_covered, sync_activities,
               async_activities, lab_activities, photo_documentation, faculty_signature, dean_name,
               date_submitted, hrd_received_date, status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $academicWeek = ar_clean_string($payload, 'academic_week', 10);
        $dateCovered = ar_clean_string($payload, 'date_covered', 80);
        $timeConducted = ar_clean_string($payload, 'time_conducted', 50);
        $duration = ar_clean_string($payload, 'duration', 30);
        $topics = ar_clean_string($payload, 'topics_covered');
        $sync = ar_clean_string($payload, 'sync_activities');
        $async = ar_clean_string($payload, 'async_activities');
        $lab = ar_clean_string($payload, 'lab_activities');
        $facultySignature = ar_clean_string($payload, 'faculty_signature', 120);
        $deanName = ar_clean_string($payload, 'dean_name', 100);
        $stmt->bind_param(
            'ssssssisssssssssssss',
            $classId,
            $subjectId,
            $sectionLabel,
            $facultyId,
            $sessionId,
            $academicWeek,
            $units,
            $dateCovered,
            $timeConducted,
            $duration,
            $topics,
            $sync,
            $async,
            $lab,
            $photoDocumentation,
            $facultySignature,
            $deanName,
            $dateSubmitted,
            $hrdReceived,
            $status
        );
        $ok = $stmt->execute();
        $reportId = (int)$conn->insert_id;
    }

    if (!$ok) ar_json(['status' => 'error', 'message' => 'Save failed: ' . $stmt->error]);
    $saved = ar_report_for_session($conn, $sessionId);
    ar_json([
        'status' => 'success',
        'message' => 'Draft saved',
        'report_id' => $reportId,
        'report' => $saved,
        'sessions' => ar_sessions($conn, $classId, (int)$ctx['class_size']),
    ]);
}

ar_json(['status' => 'error', 'message' => 'Unknown action']);
