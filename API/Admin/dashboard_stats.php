<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$deptImageColRes = $conn->query("SHOW COLUMNS FROM tbldepartment LIKE 'dept_image'");
$hasDeptImage = $deptImageColRes && $deptImageColRes->num_rows > 0;
$facultyCreatedColRes = $conn->query("SHOW COLUMNS FROM tblfaculty LIKE 'created_at'");
$hasFacultyCreatedAt = $facultyCreatedColRes && $facultyCreatedColRes->num_rows > 0;
$studentCreatedColRes = $conn->query("SHOW COLUMNS FROM tblstudent LIKE 'created_at'");
$hasStudentCreatedAt = $studentCreatedColRes && $studentCreatedColRes->num_rows > 0;
$programCreatedColRes = $conn->query("SHOW COLUMNS FROM tblcourse LIKE 'created_at'");
$hasProgramCreatedAt = $programCreatedColRes && $programCreatedColRes->num_rows > 0;

$stats = [];
$stats['total_departments'] = (int)$conn->query("SELECT COUNT(*) FROM tbldepartment WHERE is_deleted=0")->fetch_row()[0];
$stats['total_programs']    = (int)$conn->query("SELECT COUNT(*) FROM tblcourse WHERE is_deleted=0")->fetch_row()[0];
$stats['total_faculty']     = (int)$conn->query("SELECT COUNT(*) FROM tblfaculty WHERE is_deleted=0 AND (is_dean IS NULL OR is_dean=0)")->fetch_row()[0];
$stats['total_students']    = (int)$conn->query("SELECT COUNT(*) FROM tblstudent WHERE is_deleted=0")->fetch_row()[0];
$stats['total_subjects']    = (int)$conn->query("SELECT COUNT(*) FROM tblsubject WHERE is_deleted=0")->fetch_row()[0];
$stats['total_classes']     = (int)$conn->query("SELECT COUNT(*) FROM tblclass WHERE is_deleted=0")->fetch_row()[0];
$stats['active_deans']      = (int)$conn->query("SELECT COUNT(*) FROM tbldeanassignment WHERE is_active=1")->fetch_row()[0];

$departmentRows = [];
$deptRes = $conn->query("
    SELECT
        d.id,
        d.dept_code,
        d.dept_name,
        " . ($hasDeptImage ? "COALESCE(d.dept_image, '')" : "''") . " AS dept_image,
        COUNT(DISTINCT c.id)   AS program_count,
        COUNT(DISTINCT st.id)  AS student_count,
        COUNT(DISTINCT sb.id)  AS subject_count,
        COUNT(DISTINCT cl.id)  AS class_count
    FROM tbldepartment d
    LEFT JOIN tblcourse c
           ON c.department_id = d.id
          AND c.is_deleted = 0
    LEFT JOIN tblstudent st
           ON st.course_id = c.id
          AND st.is_deleted = 0
    LEFT JOIN tblsubject sb
           ON sb.course_id = c.id
          AND sb.is_deleted = 0
    LEFT JOIN tblclass cl
           ON cl.course_id = c.id
          AND cl.is_deleted = 0
    WHERE d.is_deleted = 0
    GROUP BY d.id, d.dept_code, d.dept_name" . ($hasDeptImage ? ", d.dept_image" : "") . "
    ORDER BY d.dept_name ASC
");
if ($deptRes) {
    while ($row = $deptRes->fetch_assoc()) {
        $departmentRows[] = [
            'id' => (string)$row['id'],
            'code' => $row['dept_code'] ?: '—',
            'name' => $row['dept_name'] ?: 'Unnamed Department',
            'image' => $row['dept_image'] ?: '',
            'programs' => (int)$row['program_count'],
            'students' => (int)$row['student_count'],
            'subjects' => (int)$row['subject_count'],
            'classes' => (int)$row['class_count'],
        ];
    }
}

$programRows = [];
$programRes = $conn->query("
    SELECT
        c.id,
        c.course_code,
        c.course_name,
        d.dept_code,
        d.dept_name,
        COUNT(st.id) AS student_count
    FROM tblcourse c
    LEFT JOIN tbldepartment d
           ON d.id = c.department_id
          AND d.is_deleted = 0
    LEFT JOIN tblstudent st
           ON st.course_id = c.id
          AND st.is_deleted = 0
    WHERE c.is_deleted = 0
    GROUP BY c.id, c.course_code, c.course_name, d.dept_code, d.dept_name
    ORDER BY student_count DESC, c.course_code ASC
    LIMIT 8
");
if ($programRes) {
    while ($row = $programRes->fetch_assoc()) {
        $programRows[] = [
            'id' => (string)$row['id'],
            'code' => $row['course_code'] ?: '—',
            'name' => $row['course_name'] ?: 'Unnamed Program',
            'department_code' => $row['dept_code'] ?: '—',
            'department_name' => $row['dept_name'] ?: 'No Department',
            'students' => (int)$row['student_count'],
        ];
    }
}

$months = [];
$monthKeys = [];
for ($i = 5; $i >= 0; $i--) {
    $stamp = strtotime("-{$i} month");
    $key = date('Y-m', $stamp);
    $monthKeys[] = $key;
    $months[$key] = [
        'label' => date('M Y', $stamp),
        'students' => 0,
        'faculty' => 0,
        'programs' => 0,
    ];
}

if ($hasStudentCreatedAt) {
    $studentTrendRes = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, COUNT(*) AS total
        FROM tblstudent
        WHERE is_deleted = 0
          AND created_at IS NOT NULL
          AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ");
    if ($studentTrendRes) {
        while ($row = $studentTrendRes->fetch_assoc()) {
            $key = $row['month_key'];
            if (isset($months[$key])) $months[$key]['students'] = (int)$row['total'];
        }
    }
}

if ($hasFacultyCreatedAt) {
    $facultyTrendRes = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, COUNT(*) AS total
        FROM tblfaculty
        WHERE is_deleted = 0
          AND created_at IS NOT NULL
          AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ");
    if ($facultyTrendRes) {
        while ($row = $facultyTrendRes->fetch_assoc()) {
            $key = $row['month_key'];
            if (isset($months[$key])) $months[$key]['faculty'] = (int)$row['total'];
        }
    }
}

if ($hasProgramCreatedAt) {
    $programTrendRes = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, COUNT(*) AS total
        FROM tblcourse
        WHERE is_deleted = 0
          AND created_at IS NOT NULL
          AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ");
    if ($programTrendRes) {
        while ($row = $programTrendRes->fetch_assoc()) {
            $key = $row['month_key'];
            if (isset($months[$key])) $months[$key]['programs'] = (int)$row['total'];
        }
    }
}

$trendRows = array_values($months);

$conn->close();

echo json_encode([
    'status' => 'success',
    'stats' => $stats,
    'analytics' => [
        'departments' => $departmentRows,
        'top_programs' => $programRows,
        'trend' => $trendRows,
    ],
]);
