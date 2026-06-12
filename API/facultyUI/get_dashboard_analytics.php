<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/classroom/gradebook/_helpers.php';

function dash_fail(string $message, int $status = 400): void {
    http_response_code($status);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

function dash_semester_label(?array $semester): string {
    if (!$semester) return 'No active semester';
    $term = trim((string)($semester['semester'] ?? ''));
    $year = trim((string)($semester['school_year'] ?? ''));
    return trim($term . ($year !== '' ? ' ' . $year : ''));
}

function dash_initials(string $firstName, string $lastName): string {
    $a = strtoupper(substr(trim($firstName), 0, 1));
    $b = strtoupper(substr(trim($lastName), 0, 1));
    return ($a . $b) !== '' ? ($a . $b) : 'ST';
}

function dash_time_sort_value(?string $value): int {
    if (!$value) return 0;
    $ts = strtotime($value);
    return $ts ?: 0;
}

function dash_student_score_stats(array $gradebook): array {
    $stats = [];
    foreach (($gradebook['assessments'] ?? []) as $assessment) {
        foreach (($assessment['scores'] ?? []) as $studentId => $scoreRow) {
            if (($scoreRow['percentage'] ?? null) === null) {
                continue;
            }
            $key = (string)$studentId;
            if (!isset($stats[$key])) {
                $stats[$key] = ['sum' => 0.0, 'count' => 0];
            }
            $stats[$key]['sum'] += (float)$scoreRow['percentage'];
            $stats[$key]['count']++;
        }
    }
    foreach ($stats as $studentId => $row) {
        $count = (int)($row['count'] ?? 0);
        $stats[$studentId]['average'] = $count > 0 ? round(((float)$row['sum']) / $count, 2) : null;
    }
    return $stats;
}

function dash_resolve_context(mysqli $conn, string $userId): array {
    $ctx = [
        'user_id' => $userId,
        'faculty_id' => $userId,
        'faculty' => [
            'first_name' => 'Faculty',
            'middle_name' => '',
            'last_name' => 'Member',
            'email' => '',
        ],
    ];

    $userCols = ['id', 'username', 'email'];
    $colRes = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tbluser'
          AND COLUMN_NAME IN ('first_name', 'middle_name', 'last_name')");
    if ($colRes) {
        while ($col = $colRes->fetch_assoc()) {
            $userCols[] = $col['COLUMN_NAME'];
        }
    }
    $uStmt = $conn->prepare("SELECT " . implode(', ', $userCols) . " FROM tbluser WHERE id = ? AND is_deleted = 0 LIMIT 1");
    if ($uStmt) {
        $uStmt->bind_param('s', $userId);
        $uStmt->execute();
        $uRow = $uStmt->get_result()->fetch_assoc();
        $uStmt->close();
        if ($uRow) {
            $ctx['faculty']['first_name'] = $uRow['first_name'] ?? $ctx['faculty']['first_name'];
            $ctx['faculty']['middle_name'] = $uRow['middle_name'] ?? '';
            $ctx['faculty']['last_name'] = $uRow['last_name'] ?? $ctx['faculty']['last_name'];
            $ctx['faculty']['email'] = $uRow['email'] ?? '';
            $ctx['username'] = $uRow['username'] ?? '';
            $ctx['email'] = $uRow['email'] ?? '';
        }
    }

    $faculty = null;
    $username = (string)($ctx['username'] ?? '');
    $email = (string)($ctx['email'] ?? '');

    if ($username !== '') {
        $stmt = $conn->prepare("SELECT * FROM tblfaculty WHERE username = ? AND is_deleted = 0 LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $faculty = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }

    if (!$faculty && $email !== '') {
        $stmt = $conn->prepare("SELECT * FROM tblfaculty WHERE email = ? AND is_deleted = 0 LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $faculty = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }

    if (!$faculty && isset($_SESSION['faculty_id'])) {
        $facultyId = (string)$_SESSION['faculty_id'];
        $stmt = $conn->prepare("SELECT * FROM tblfaculty WHERE id = ? AND is_deleted = 0 LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $facultyId);
            $stmt->execute();
            $faculty = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }

    $adminId = null;
    if ($username !== '' || $email !== '') {
        if ($username !== '' && $email !== '') {
            $stmt = $conn->prepare("SELECT id FROM tbladmin WHERE (username = ? OR email = ?) AND is_deleted = 0 LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('ss', $username, $email);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $adminId = $row['id'] ?? null;
            }
        } elseif ($username !== '') {
            $stmt = $conn->prepare("SELECT id FROM tbladmin WHERE username = ? AND is_deleted = 0 LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $adminId = $row['id'] ?? null;
            }
        } else {
            $stmt = $conn->prepare("SELECT id FROM tbladmin WHERE email = ? AND is_deleted = 0 LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $adminId = $row['id'] ?? null;
            }
        }
    }

    if ($faculty) {
        $ctx['faculty_id'] = (string)($faculty['id'] ?? $ctx['faculty_id']);
        $ctx['faculty']['first_name'] = $faculty['first_name'] ?? $ctx['faculty']['first_name'];
        $ctx['faculty']['middle_name'] = $faculty['middle_name'] ?? $ctx['faculty']['middle_name'];
        $ctx['faculty']['last_name'] = $faculty['last_name'] ?? $ctx['faculty']['last_name'];
        $ctx['faculty']['email'] = $faculty['email'] ?? $ctx['faculty']['email'];
    }

    if ($adminId && $adminId !== $ctx['faculty_id']) {
        $countFaculty = 0;
        $countAdmin = 0;

        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM tblclass WHERE faculty_id = ? AND is_deleted = 0");
        if ($stmt) {
            $stmt->bind_param('s', $ctx['faculty_id']);
            $stmt->execute();
            $countFaculty = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
            $stmt->close();
        }

        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM tblclass WHERE faculty_id = ? AND is_deleted = 0");
        if ($stmt) {
            $stmt->bind_param('s', $adminId);
            $stmt->execute();
            $countAdmin = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
            $stmt->close();
        }

        if ($countAdmin > $countFaculty) {
            $ctx['faculty_id'] = $adminId;
        }
    }

    return $ctx;
}

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    dash_fail('Unauthorized', 401);
}

$ctx = dash_resolve_context($conn, (string)$_SESSION['user_id']);
$facultyId = (string)$ctx['faculty_id'];

$semester = null;
$semesterId = 0;
$semRes = $conn->query("SELECT id, school_year, semester FROM tblsemestersetting WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
if ($semRes && $semRes->num_rows) {
    $semester = $semRes->fetch_assoc();
    $semesterId = (int)($semester['id'] ?? 0);
}

$classes = [];
$sql = "
    SELECT
        c.id,
        c.section,
        c.class_code,
        c.class_semester,
        c.year_level,
        c.class_days,
        c.schedule,
        c.created_at,
        s.subject_name,
        s.subject_code,
        co.course_name,
        co.course_code
    FROM tblclass c
    LEFT JOIN tblsubject s ON s.id = c.subject_id
    LEFT JOIN tblcourse co ON co.id = c.course_id
    WHERE c.faculty_id = ?
      AND c.is_deleted = 0
      AND c.is_active = 1
";
if ($semesterId > 0) {
    $sql .= " AND (c.semester_setting_id = ? OR c.semester_setting_id IS NULL)";
}
$sql .= " ORDER BY c.section ASC, c.created_at ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    dash_fail('Could not prepare class query', 500);
}
if ($semesterId > 0) {
    $stmt->bind_param('si', $facultyId, $semesterId);
} else {
    $stmt->bind_param('s', $facultyId);
}
$stmt->execute();
$classes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$validClassIds = array_fill_keys(array_map(static fn(array $row): string => (string)$row['id'], $classes), true);

$summary = [
    'semester_label' => dash_semester_label($semester),
    'section_count' => count($classes),
    'total_students' => 0,
    'high_performers' => 0,
    'at_risk_students' => 0,
];

$passRates = [];
$enrollmentPanels = [];
$recentActivity = [];
$topPerformers = [];
$atRiskStudents = [];
$trendDatasets = [];
$trendLabelOrder = [];
$completionRows = [];
$classPerformanceRows = [];
$perfectScorers = [];
$performanceBuckets = [
    'attendance' => ['label' => 'Attendance', 'sum' => 0.0, 'count' => 0],
    'recitation' => ['label' => 'Recitation', 'sum' => 0.0, 'count' => 0],
    'quiz' => ['label' => 'Quiz', 'sum' => 0.0, 'count' => 0],
    'activities' => ['label' => 'Activities', 'sum' => 0.0, 'count' => 0],
    'exam' => ['label' => 'Exams', 'sum' => 0.0, 'count' => 0],
];
$gradeDistribution = [
    '90-100' => 0,
    '80-89' => 0,
    '70-79' => 0,
    '60-69' => 0,
    '<60' => 0,
];
$statusBreakdown = [
    'passing' => 0,
    'at_risk' => 0,
    'ungraded' => 0,
];

foreach ($classes as $class) {
    $classId = (string)$class['id'];
    $sectionName = trim((string)($class['section'] ?? '')) ?: 'Section';
    $subjectCode = trim((string)($class['subject_code'] ?? $class['class_code'] ?? ''));
    $classDisplayName = trim($sectionName . ($subjectCode !== '' ? ' ' . $subjectCode : ''));
    $className = trim((string)($class['subject_name'] ?? $class['subject_code'] ?? $class['class_code'] ?? 'Class'));

    $enrolledCount = 0;
    $newCount = 0;
    $stmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_enrolled,
            SUM(CASE WHEN joined_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS new_enrolled
        FROM (
            SELECT s.id AS student_id, MAX(src.joined_at) AS joined_at
            FROM (
                SELECT ce.student_id, ce.enrolled_at AS joined_at
                FROM tblclassenrollment ce
                WHERE ce.class_id = ? AND ce.enrollment_status = 'enrolled'
                UNION ALL
                SELECT cs.student_id, cs.date_joined AS joined_at
                FROM tblclassstudents cs
                WHERE cs.class_id = ? AND cs.is_deleted = 0 AND cs.is_archived = 0
            ) src
            INNER JOIN tblstudent s
                ON (CAST(s.id AS CHAR) = CAST(src.student_id AS CHAR) OR s.user_id = src.student_id)
               AND s.is_deleted = 0
            GROUP BY s.id
        ) roster
    ");
    if ($stmt) {
        $stmt->bind_param('ss', $classId, $classId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $enrolledCount = (int)($row['total_enrolled'] ?? 0);
        $newCount = (int)($row['new_enrolled'] ?? 0);
    }

    $summary['total_students'] += $enrolledCount;
    $enrollmentPanels[] = [
        'class_id' => $classId,
        'class_name' => $className,
        'section_name' => $sectionName,
        'display_name' => $classDisplayName,
        'total_enrolled' => $enrolledCount,
        'new_enrolled' => $newCount,
    ];

    $gradebook = gb_get_gradebook($conn, $classId);
    $students = $gradebook['students'] ?? [];
    $scoreStats = dash_student_score_stats($gradebook);
    $passCount = 0;
    $failCount = 0;
    $classStatus = [
        'high' => 0,
        'average' => 0,
        'at_risk' => 0,
        'ungraded' => 0,
    ];
    $classGradeSum = 0.0;
    $classGradeCount = 0;

    foreach ($students as $student) {
        $studentId = (string)($student['student_id'] ?? '');
        $parts = array_map('trim', explode(',', (string)($student['student_name'] ?? 'Unknown, Student'), 2));
        $lastName = $parts[0] ?? 'Unknown';
        $firstName = $parts[1] ?? '';
        $studentDisplayName = trim($lastName . ', ' . $firstName, ', ');

        $perfectCount = 0;
        $gradedCount = 0;
        foreach (($gradebook['assessments'] ?? []) as $assessment) {
            $scoreRow = $assessment['scores'][$studentId] ?? null;
            if (!$scoreRow || ($scoreRow['percentage'] ?? null) === null) {
                continue;
            }
            $gradedCount++;
            if ((float)$scoreRow['percentage'] >= 99.995) {
                $perfectCount++;
            }
        }
        if ($perfectCount > 0) {
            $perfectScorers[] = [
                'student_id' => $studentId,
                'student_name' => $studentDisplayName !== '' ? $studentDisplayName : (string)($student['student_name'] ?? 'Unknown'),
                'section_name' => $sectionName,
                'display_name' => $classDisplayName,
                'class_name' => $className,
                'perfect_count' => $perfectCount,
                'graded_count' => $gradedCount,
                'perfect_rate' => $gradedCount > 0 ? round(($perfectCount / $gradedCount) * 100, 1) : 0,
                'initials' => dash_initials($firstName, $lastName),
            ];
        }

        $grade = $scoreStats[$studentId]['average'] ?? null;
        if ($grade === null) {
            $statusBreakdown['ungraded']++;
            $classStatus['ungraded']++;
            continue;
        }
        $grade = round((float)$grade, 2);
        $classGradeSum += $grade;
        $classGradeCount++;

        if ($grade >= 85) {
            $summary['high_performers']++;
            $classStatus['high']++;
        } elseif ($grade >= 75) {
            $classStatus['average']++;
        } else {
            $summary['at_risk_students']++;
            $classStatus['at_risk']++;
        }

        if ($grade >= 75) {
            $passCount++;
            $statusBreakdown['passing']++;
        } else {
            $failCount++;
            $statusBreakdown['at_risk']++;
        }

        if ($grade >= 90) {
            $gradeDistribution['90-100']++;
        } elseif ($grade >= 80) {
            $gradeDistribution['80-89']++;
        } elseif ($grade >= 70) {
            $gradeDistribution['70-79']++;
        } elseif ($grade >= 60) {
            $gradeDistribution['60-69']++;
        } else {
            $gradeDistribution['<60']++;
        }

        $entry = [
            'student_id' => $studentId,
            'student_name' => $studentDisplayName !== '' ? $studentDisplayName : (string)($student['student_name'] ?? 'Unknown'),
            'section_name' => $sectionName,
            'display_name' => $classDisplayName,
            'class_name' => $className,
            'grade' => $grade,
            'initials' => dash_initials($firstName, $lastName),
        ];
        $topPerformers[] = $entry;
        if ($grade < 75) $atRiskStudents[] = $entry;
    }

    $classPerformanceRows[] = [
        'class_id' => $classId,
        'section_name' => $sectionName,
        'display_name' => $classDisplayName,
        'class_name' => $className,
        'high' => $classStatus['high'],
        'average' => $classStatus['average'],
        'at_risk' => $classStatus['at_risk'],
        'ungraded' => $classStatus['ungraded'],
        'total' => array_sum($classStatus),
        'average_grade' => $classGradeCount > 0 ? round($classGradeSum / $classGradeCount, 1) : null,
    ];

    $totalWithGrades = $passCount + $failCount;
    $passPct = $totalWithGrades > 0 ? round(($passCount / $totalWithGrades) * 100, 1) : 0.0;
    $failPct = $totalWithGrades > 0 ? round(100 - $passPct, 1) : 0.0;
    $tone = $passPct >= 70 ? 'good' : ($passPct >= 60 ? 'warn' : 'risk');

    if ($totalWithGrades > 0) {
        $passRates[] = [
            'class_id' => $classId,
            'section_name' => $sectionName,
            'display_name' => $classDisplayName,
            'class_name' => $className,
            'pass_percentage' => $passPct,
            'fail_percentage' => $failPct,
            'pass_count' => $passCount,
            'fail_count' => $failCount,
            'tone' => $tone,
        ];
    }

    $trendMap = [];
    foreach (($gradebook['assessments'] ?? []) as $assessment) {
        $label = trim((string)($assessment['title'] ?? 'Assessment'));
        if ($label === '') $label = 'Assessment';

        $avg = null;
        $sum = 0.0;
        $count = 0;
        foreach (($assessment['scores'] ?? []) as $scoreRow) {
            if (($scoreRow['percentage'] ?? null) !== null) {
                $sum += (float)$scoreRow['percentage'];
                $count++;
            }
        }
        if ($count > 0) {
            $avg = round($sum / $count, 2);
            $category = (string)($assessment['category'] ?? 'activities');
            if (!isset($performanceBuckets[$category])) {
                $performanceBuckets[$category] = [
                    'label' => ucwords(str_replace('_', ' ', $category)),
                    'sum' => 0.0,
                    'count' => 0,
                ];
            }
            $performanceBuckets[$category]['sum'] += $avg;
            $performanceBuckets[$category]['count']++;
        }

        $trendMap[$label] = $avg;
        $sortValue = dash_time_sort_value((string)($assessment['date_created'] ?? ''));
        if (!isset($trendLabelOrder[$label]) || $sortValue < $trendLabelOrder[$label]) {
            $trendLabelOrder[$label] = $sortValue;
        }
    }

    $trendDatasets[] = [
        'class_id' => $classId,
        'section_name' => $sectionName,
        'display_name' => $classDisplayName,
        'class_name' => $className,
        'points' => $trendMap,
    ];

    $scoredSlots = 0;
    $missingSlots = 0;
    foreach (($gradebook['assessments'] ?? []) as $assessment) {
        foreach (($assessment['scores'] ?? []) as $scoreRow) {
            if (($scoreRow['score'] ?? null) !== null) {
                $scoredSlots++;
            } else {
                $missingSlots++;
            }
        }
    }
    $totalSlots = $scoredSlots + $missingSlots;
    if ($totalSlots > 0) {
        $completionRows[] = [
            'class_id' => $classId,
            'section_name' => $sectionName,
            'display_name' => $classDisplayName,
            'class_name' => $className,
            'scored' => $scoredSlots,
            'missing' => $missingSlots,
            'completion_percentage' => round(($scoredSlots / $totalSlots) * 100, 1),
        ];
    }

    $attendanceSql = "
        SELECT
            SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) AS present_count,
            COUNT(ar.id) AS record_count
        FROM tblattendance a
        JOIN tblattendancerecord ar ON ar.attendance_id = a.id
        WHERE a.class_id = ?
          AND a.is_deleted = 0
    ";
    if ($semesterId > 0) {
        $attendanceSql .= " AND (a.semester_setting_id = ? OR a.semester_setting_id IS NULL)";
    }
    $stmt = $conn->prepare($attendanceSql);
    if ($stmt) {
        if ($semesterId > 0) {
            $stmt->bind_param('si', $classId, $semesterId);
        } else {
            $stmt->bind_param('s', $classId);
        }
        $stmt->execute();
        $attendanceRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $attendanceTotal = (int)($attendanceRow['record_count'] ?? 0);
        if ($attendanceTotal > 0) {
            $attendancePct = round(((int)($attendanceRow['present_count'] ?? 0) / $attendanceTotal) * 100, 2);
            $performanceBuckets['attendance']['sum'] += $attendancePct;
            $performanceBuckets['attendance']['count']++;
        }
    }
}

$performancePoints = [];
$previousTotal = null;
foreach ($performanceBuckets as $bucket) {
    if ((int)($bucket['count'] ?? 0) <= 0) {
        continue;
    }
    $total = round(((float)$bucket['sum']) / (int)$bucket['count'], 2);
    $performancePoints[] = [
        'date' => $bucket['label'],
        'total' => $total,
        'change' => $previousTotal === null ? 0 : round($total - $previousTotal, 2),
    ];
    $previousTotal = $total;
}

$studentSections = $enrollmentPanels;
usort($studentSections, function (array $a, array $b): int {
    $countCompare = ((int)($b['total_enrolled'] ?? 0)) <=> ((int)($a['total_enrolled'] ?? 0));
    if ($countCompare !== 0) {
        return $countCompare;
    }
    return strcmp($a['display_name'] . ' ' . $a['class_name'], $b['display_name'] . ' ' . $b['class_name']);
});
$studentSections = array_slice($studentSections, 0, 5);

usort($completionRows, function (array $a, array $b): int {
    return ((float)($a['completion_percentage'] ?? 0)) <=> ((float)($b['completion_percentage'] ?? 0));
});
$completionRows = array_slice($completionRows, 0, 5);

usort($enrollmentPanels, function (array $a, array $b): int {
    return strcmp($a['section_name'] . ' ' . $a['class_name'], $b['section_name'] . ' ' . $b['class_name']);
});

$joinedRows = [];
$stmt = $conn->prepare("
    SELECT
        x.class_id,
        x.activity_at,
        'joined' AS activity_type,
        s.first_name,
        s.last_name,
        c.section,
        sb.subject_name
    FROM (
        SELECT
            c.id AS class_id,
            s.id AS resolved_student_id,
            MAX(src.activity_at) AS activity_at
        FROM (
            SELECT ce.class_id, ce.student_id, ce.enrolled_at AS activity_at
            FROM tblclassenrollment ce
            WHERE ce.enrollment_status = 'enrolled' AND ce.enrolled_at IS NOT NULL
            UNION ALL
            SELECT cs.class_id, cs.student_id, cs.date_joined AS activity_at
            FROM tblclassstudents cs
            WHERE cs.is_deleted = 0 AND cs.is_archived = 0 AND cs.date_joined IS NOT NULL
        ) src
        INNER JOIN tblclass c ON c.id = src.class_id AND c.faculty_id = ? AND c.is_deleted = 0 AND c.is_active = 1
        INNER JOIN tblstudent s
            ON (CAST(s.id AS CHAR) = CAST(src.student_id AS CHAR) OR s.user_id = src.student_id)
           AND s.is_deleted = 0
        GROUP BY c.id, s.id
    ) x
    INNER JOIN tblclass c ON c.id = x.class_id
    INNER JOIN tblstudent s ON s.id = x.resolved_student_id
    LEFT JOIN tblsubject sb ON sb.id = c.subject_id
");
if ($stmt) {
    $stmt->bind_param('s', $facultyId);
    $stmt->execute();
    $joinedRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$inviteRows = [];
$stmt = $conn->prepare("
    SELECT
        c.id AS class_id,
        COALESCE(i.invited_at, i.created_at) AS activity_at,
        'invited' AS activity_type,
        s.first_name,
        s.last_name,
        c.section,
        sb.subject_name
    FROM tblinvitations i
    INNER JOIN tblclass c ON c.id = i.class_id AND c.faculty_id = ? AND c.is_deleted = 0 AND c.is_active = 1
    INNER JOIN tblstudent s ON s.id = i.student_id AND s.is_deleted = 0
    LEFT JOIN tblsubject sb ON sb.id = c.subject_id
    WHERE i.invitation_status = 'pending'
");
if ($stmt) {
    $stmt->bind_param('s', $facultyId);
    $stmt->execute();
    $inviteRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

foreach (array_merge($joinedRows, $inviteRows) as $row) {
    if ($validClassIds && !isset($validClassIds[(string)($row['class_id'] ?? '')])) {
        continue;
    }
    $firstName = trim((string)($row['first_name'] ?? ''));
    $lastName = trim((string)($row['last_name'] ?? ''));
    $recentActivity[] = [
        'student_name' => trim($lastName . ', ' . $firstName, ', '),
        'section_name' => trim((string)($row['section'] ?? '')) ?: 'Section',
        'class_name' => trim((string)($row['subject_name'] ?? 'Class')),
        'activity_type' => $row['activity_type'] ?? 'joined',
        'activity_at' => $row['activity_at'] ?? null,
        'initials' => dash_initials($firstName, $lastName),
    ];
}

usort($recentActivity, function (array $a, array $b): int {
    return dash_time_sort_value((string)($b['activity_at'] ?? '')) <=> dash_time_sort_value((string)($a['activity_at'] ?? ''));
});
$recentActivity = array_slice($recentActivity, 0, 5);

usort($topPerformers, function (array $a, array $b): int {
    return $b['grade'] <=> $a['grade'];
});
$topPerformers = array_slice($topPerformers, 0, 5);

usort($atRiskStudents, function (array $a, array $b): int {
    return $a['grade'] <=> $b['grade'];
});
$atRiskStudents = array_slice($atRiskStudents, 0, 5);

usort($perfectScorers, function (array $a, array $b): int {
    $perfectCompare = ((int)($b['perfect_count'] ?? 0)) <=> ((int)($a['perfect_count'] ?? 0));
    if ($perfectCompare !== 0) return $perfectCompare;
    $rateCompare = ((float)($b['perfect_rate'] ?? 0)) <=> ((float)($a['perfect_rate'] ?? 0));
    if ($rateCompare !== 0) return $rateCompare;
    return strcmp((string)($a['student_name'] ?? ''), (string)($b['student_name'] ?? ''));
});
$perfectScorers = array_slice($perfectScorers, 0, 8);

usort($classPerformanceRows, function (array $a, array $b): int {
    $riskCompare = ((int)($b['at_risk'] ?? 0)) <=> ((int)($a['at_risk'] ?? 0));
    if ($riskCompare !== 0) return $riskCompare;
    return strcmp((string)($a['display_name'] ?? ''), (string)($b['display_name'] ?? ''));
});

asort($trendLabelOrder);
$trendLabels = array_keys($trendLabelOrder);
$dashPatterns = [
    [],
    [6, 4],
    [2, 3],
    [10, 4],
    [8, 3, 2, 3],
    [3, 2],
];

$scoreTrendDatasets = [];
foreach ($trendDatasets as $index => $dataset) {
    $values = [];
    foreach ($trendLabels as $label) {
        $values[] = array_key_exists($label, $dataset['points']) ? $dataset['points'][$label] : null;
    }
    $scoreTrendDatasets[] = [
        'class_id' => $dataset['class_id'],
        'section_name' => $dataset['section_name'],
        'display_name' => $dataset['display_name'],
        'class_name' => $dataset['class_name'],
        'label' => $dataset['display_name'] ?: $dataset['section_name'],
        'data' => $values,
        'dash_pattern' => $dashPatterns[$index % count($dashPatterns)],
    ];
}

echo json_encode([
    'status' => 'success',
    'summary' => $summary,
    'pass_rates' => $passRates,
    'student_sections' => $studentSections,
    'completion_rates' => $completionRows,
    'grade_distribution' => $gradeDistribution,
    'status_breakdown' => $statusBreakdown,
    'score_trend' => [
        'labels' => $trendLabels,
        'datasets' => $scoreTrendDatasets,
    ],
    'performance_points' => $performancePoints,
    'class_performance' => $classPerformanceRows,
    'perfect_scorers' => $perfectScorers,
    'enrollments_week' => $enrollmentPanels,
    'recent_activity' => $recentActivity,
    'top_performers' => $topPerformers,
    'at_risk_students' => $atRiskStudents,
]);
