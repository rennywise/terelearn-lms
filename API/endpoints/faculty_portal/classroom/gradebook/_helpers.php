<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../../../core/db_connect.php';

function gb_json_fail(string $msg, int $code = 400): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

function gb_require_faculty_user(): string {
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
        gb_json_fail('Unauthorized', 401);
    }
    return (string)$_SESSION['user_id'];
}

function gb_resolve_faculty_id(mysqli $conn, string $user_id): ?string {
    $u = $conn->real_escape_string($user_id);
    $uRes = $conn->query("SELECT username FROM tbluser WHERE id='$u' AND is_deleted=0 LIMIT 1");
    if (!$uRes || !$uRes->num_rows) return null;
    $username = $conn->real_escape_string((string)$uRes->fetch_assoc()['username']);

    $fRes = $conn->query("SELECT id FROM tblfaculty WHERE username='$username' AND is_deleted=0 LIMIT 1");
    if (!$fRes || !$fRes->num_rows) return null;
    return (string)$fRes->fetch_assoc()['id'];
}

function gb_verify_class_owner(mysqli $conn, string $class_id, string $faculty_id): ?array {
    $c = $conn->real_escape_string($class_id);
    $f = $conn->real_escape_string($faculty_id);
    $res = $conn->query("SELECT id, class_code FROM tblclass WHERE id='$c' AND faculty_id='$f' AND is_deleted=0 LIMIT 1");
    if (!$res || !$res->num_rows) return null;
    return $res->fetch_assoc();
}

function gb_load_scheme(mysqli $conn, string $class_id): array {
    $conn->query("CREATE TABLE IF NOT EXISTS tblclassgradingscheme (
        id CHAR(36) PRIMARY KEY,
        class_id CHAR(36) NOT NULL,
        weight_recitation INT NOT NULL DEFAULT 20,
        weight_quiz INT NOT NULL DEFAULT 20,
        weight_activities INT NOT NULL DEFAULT 20,
        weight_exam INT NOT NULL DEFAULT 40,
        count_recitation INT NOT NULL DEFAULT 0,
        count_quiz INT NOT NULL DEFAULT 0,
        count_activities INT NOT NULL DEFAULT 0,
        count_exam INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_tblclassgradingscheme_class (class_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $default = [
        'weight_recitation' => 20,
        'weight_quiz' => 20,
        'weight_activities' => 20,
        'weight_exam' => 40,
        'count_recitation' => 0,
        'count_quiz' => 0,
        'count_activities' => 0,
        'count_exam' => 0,
    ];

    $c = $conn->real_escape_string($class_id);
    $res = $conn->query("SELECT weight_recitation, weight_quiz, weight_activities, weight_exam, count_recitation, count_quiz, count_activities, count_exam FROM tblclassgradingscheme WHERE class_id='$c' LIMIT 1");
    if ($res && $res->num_rows) return array_merge($default, $res->fetch_assoc());
    return $default;
}

function gb_infer_assessment_period(array $post): string {
    $explicit = strtolower(trim((string)($post['lesson_period'] ?? '')));
    if (in_array($explicit, ['prelim', 'midterm', 'finals'], true)) return $explicit;

    $text = strtolower(trim(implode(' ', [
        $post['title'] ?? '',
        $post['post_type'] ?? '',
        $post['type_key'] ?? '',
        $post['type_label'] ?? '',
    ])));
    if (preg_match('/\bpre[\s-]*lim\b|\bprelim\b/', $text)) return 'prelim';
    if (preg_match('/\bmid[\s-]*term\b|\bmidterm\b/', $text)) return 'midterm';
    if (preg_match('/\bfinals?\b|\bfinal[\s-]*exam\b/', $text)) return 'finals';
    return '';
}

function gb_period_for_date(?string $date, array $semester, array $orderedDates = []): string {
    $periods = ['prelim', 'midterm', 'finals'];
    $date = trim((string)$date);
    if ($date === '') return 'prelim';

    if (!empty($semester['start_date']) && !empty($semester['end_date'])) {
        $start = strtotime($semester['start_date']);
        $end = strtotime($semester['end_date']);
        $cur = strtotime($date);
        if ($start && $end && $cur) {
            if ($cur <= $start) return 'prelim';
            if ($cur >= $end) return 'finals';
            $span = max(1, $end - $start);
            $ratio = ($cur - $start) / $span;
            if ($ratio < (1 / 3)) return 'prelim';
            if ($ratio < (2 / 3)) return 'midterm';
            return 'finals';
        }
    }

    $idx = array_search($date, $orderedDates, true);
    if ($idx !== false && count($orderedDates) > 0) {
        $bucket = min(2, (int)floor(($idx / max(1, count($orderedDates))) * 3));
        return $periods[$bucket];
    }
    return 'prelim';
}

function gb_load_attendance_periods(mysqli $conn, string $class_id, array $students): array {
    $periods = ['prelim', 'midterm', 'finals'];
    $out = [];
    foreach ($students as $sid => $_) {
        foreach ($periods as $period) {
            $out[$sid][$period] = [
                'present' => 0,
                'absent' => 0,
                'total' => 0,
                'percentage' => null,
                'equivalent' => null,
            ];
        }
    }
    if (!$students) return $out;

    $c = $conn->real_escape_string($class_id);
    $semester = [];
    $classRes = $conn->query("SELECT semester_setting_id FROM tblclass WHERE id='$c' LIMIT 1");
    $semesterId = ($classRes && $classRes->num_rows) ? (int)($classRes->fetch_assoc()['semester_setting_id'] ?? 0) : 0;
    if ($semesterId > 0) {
        $semRes = $conn->query("SELECT start_date, end_date FROM tblsemestersetting WHERE id=$semesterId AND is_deleted=0 LIMIT 1");
        if ($semRes && $semRes->num_rows) $semester = $semRes->fetch_assoc();
    }

    $dateRes = $conn->query("SELECT attendance_date FROM tblattendance WHERE class_id='$c' AND is_deleted=0 GROUP BY attendance_date ORDER BY attendance_date ASC");
    $orderedDates = [];
    while ($dateRes && $row = $dateRes->fetch_assoc()) $orderedDates[] = (string)$row['attendance_date'];

    $res = $conn->query("
        SELECT a.attendance_date, r.student_id, r.status
        FROM tblattendance a
        INNER JOIN tblattendancerecord r ON r.attendance_id = a.id
        WHERE a.class_id = '$c'
          AND a.is_deleted = 0
        ORDER BY a.attendance_date ASC
    ");
    while ($res && $row = $res->fetch_assoc()) {
        $sid = (string)$row['student_id'];
        if (!isset($out[$sid])) continue;
        $status = strtolower(trim((string)$row['status']));
        if (!in_array($status, ['present', 'absent'], true)) continue;
        $period = gb_period_for_date((string)$row['attendance_date'], $semester, $orderedDates);
        $out[$sid][$period]['total']++;
        if ($status === 'present') $out[$sid][$period]['present']++;
        if ($status === 'absent') $out[$sid][$period]['absent']++;
    }

    foreach ($out as $sid => $periodRows) {
        foreach ($periodRows as $period => $stats) {
            $total = (int)$stats['total'];
            if ($total <= 0) continue;
            $pct = round(((int)$stats['present'] / $total) * 100, 2);
            $out[$sid][$period]['percentage'] = $pct;
            $out[$sid][$period]['equivalent'] = round(($pct * 0.5) + 50, 0);
        }
    }
    return $out;
}

function gb_apply_assessment_period_fallback(array &$assessments): void {
    $periods = ['prelim', 'midterm', 'finals'];
    $ordered = array_values($assessments);
    usort($ordered, function ($a, $b) {
        $da = strtotime((string)($a['due_date'] ?? $a['date_created'] ?? '')) ?: 0;
        $db = strtotime((string)($b['due_date'] ?? $b['date_created'] ?? '')) ?: 0;
        if ($da === $db) return strcmp((string)($a['post_id'] ?? ''), (string)($b['post_id'] ?? ''));
        return $da <=> $db;
    });
    $count = count($ordered);
    foreach ($ordered as $idx => $assessment) {
        $period = strtolower(trim((string)($assessment['grading_period'] ?? '')));
        if (in_array($period, $periods, true)) continue;
        $bucket = min(2, (int)floor(($idx / max(1, $count)) * 3));
        $postId = (string)($assessment['post_id'] ?? '');
        if ($postId !== '' && isset($assessments[$postId])) $assessments[$postId]['grading_period'] = $periods[$bucket];
    }
}

function gb_grade_sheet_weights(): array {
    return [
        'prelim' => ['attendance' => 0.10, 'coursework' => 0.40, 'recitation' => 0.10, 'exam' => 0.40],
        'midterm' => ['attendance' => 0.05, 'coursework' => 0.45, 'recitation' => 0.10, 'exam' => 0.40],
        'finals' => ['attendance' => 0.10, 'coursework' => 0.40, 'recitation' => 0.10, 'exam' => 0.40],
    ];
}

function gb_score_equivalent($score, $max_score) {
    $raw = is_numeric($score) ? (float)$score : null;
    $max = is_numeric($max_score) ? (float)$max_score : 0.0;
    if ($raw === null || $max <= 0) return null;
    return round(($raw / $max) * 50 + 50, 0);
}

function gb_average_or_null(array $values) {
    $valid = [];
    foreach ($values as $value) {
        if (is_numeric($value)) $valid[] = (float)$value;
    }
    if (!$valid) return null;
    return array_sum($valid) / count($valid);
}

function gb_component_for_assessment(array $assessment): string {
    $category = strtolower(trim((string)($assessment['display_category'] ?? $assessment['category'] ?? '')));
    if ($category === 'exam') return 'exam';
    if ($category === 'recitation') return 'recitation';
    return 'coursework';
}

function gb_compute_sheet_period_grades(string $student_id, array $assessments, array $attendance_periods): array {
    $weights = gb_grade_sheet_weights();
    $components = [];
    foreach (array_keys($weights) as $period) {
        $components[$period] = [
            'attendance' => [],
            'coursework' => [],
            'recitation' => [],
            'exam' => [],
        ];
        $attendanceEquivalent = $attendance_periods[$period]['equivalent'] ?? null;
        if (is_numeric($attendanceEquivalent)) $components[$period]['attendance'][] = (float)$attendanceEquivalent;
    }

    $hasAssessment = [];
    foreach ($assessments as $assessment) {
        $period = strtolower(trim((string)($assessment['grading_period'] ?? 'prelim')));
        if (!isset($weights[$period])) $period = 'prelim';
        $scoreRow = $assessment['scores'][$student_id] ?? null;
        if (!$scoreRow || !array_key_exists('score', $scoreRow) || $scoreRow['score'] === null || $scoreRow['score'] === '') continue;
        $equivalent = gb_score_equivalent($scoreRow['score'], $assessment['max_score'] ?? 0);
        if ($equivalent === null) continue;
        $component = gb_component_for_assessment($assessment);
        $components[$period][$component][] = $equivalent;
        $hasAssessment[$period] = true;
    }

    $out = [];
    foreach ($weights as $period => $profile) {
        if (empty($hasAssessment[$period])) {
            $out[$period] = null;
            continue;
        }
        $weighted = 0.0;
        $usedWeight = 0.0;
        foreach ($profile as $component => $weight) {
            $avg = gb_average_or_null($components[$period][$component] ?? []);
            if ($avg === null) continue;
            $weighted += $avg * $weight;
            $usedWeight += $weight;
        }
        $out[$period] = $usedWeight > 0 ? round($weighted / $usedWeight, 2) : null;
    }
    return $out;
}

function gb_sheet_final_grade(array $period_grades) {
    $valid = [];
    foreach (['prelim', 'midterm', 'finals'] as $period) {
        if (isset($period_grades[$period]) && is_numeric($period_grades[$period])) $valid[] = (float)$period_grades[$period];
    }
    if (!$valid) return null;
    return round(array_sum($valid) / count($valid), 2);
}

function gb_grade_equivalent($grade): string {
    $g = is_numeric($grade) ? (float)$grade : 0.0;
    if ($g <= 0) return '-';
    if ($g >= 98) return '1.00';
    if ($g >= 95) return '1.25';
    if ($g >= 92) return '1.50';
    if ($g >= 89) return '1.75';
    if ($g >= 86) return '2.00';
    if ($g >= 83) return '2.25';
    if ($g >= 80) return '2.50';
    if ($g >= 77) return '2.75';
    if ($g >= 75) return '3.00';
    return '5.00';
}

function gb_get_gradebook(mysqli $conn, string $class_id): array {
    $c = $conn->real_escape_string($class_id);
    $scheme = gb_load_scheme($conn, $class_id);

    $students = [];
    $studentRes = $conn->query("SELECT
            s.id AS student_id,
            s.user_id AS student_user_id,
            s.student_number,
            TRIM(CONCAT(s.last_name, ', ', s.first_name)) AS student_name
        FROM (
            SELECT ce.student_id
            FROM tblclassenrollment ce
            WHERE ce.class_id = '$c' AND ce.enrollment_status = 'enrolled'
            UNION
            SELECT cs.student_id
            FROM tblclassstudents cs
            WHERE cs.class_id = '$c' AND cs.is_deleted = 0 AND cs.is_archived = 0
        ) roster
        INNER JOIN tblstudent s
            ON (CAST(s.id AS CHAR) = CAST(roster.student_id AS CHAR) OR s.user_id = roster.student_id)
           AND s.is_deleted = 0
        ORDER BY s.last_name ASC, s.first_name ASC");
    while ($studentRes && $row = $studentRes->fetch_assoc()) {
        $students[$row['student_id']] = [
            'student_id' => $row['student_id'],
            'student_user_id' => (string)($row['student_user_id'] ?? ''),
            'student_number' => $row['student_number'] ?: '-',
            'student_name' => $row['student_name'] ?: 'Unknown',
            'categories' => [
                'recitation' => ['raw' => 0.0, 'max' => 0.0, 'items' => 0],
                'quiz' => ['raw' => 0.0, 'max' => 0.0, 'items' => 0],
                'activities' => ['raw' => 0.0, 'max' => 0.0, 'items' => 0],
                'exam' => ['raw' => 0.0, 'max' => 0.0, 'items' => 0],
            ],
        ];
    }

    $assessments = [];
    $assessmentRes = $conn->query("SELECT p.id, p.title, p.post_type_id, p.post_type, p.points, p.created_at, p.due_date, p.lesson_period,
            pt.type_key, pt.type_label, pt.has_quiz, pt.is_gradable
        FROM tblpost p
        LEFT JOIN tblposttype pt ON pt.id = p.post_type_id
        WHERE p.class_id = '$c' AND p.is_deleted = 0
        ORDER BY p.created_at ASC");

    while ($assessmentRes && $p = $assessmentRes->fetch_assoc()) {
        $typeKey = strtolower(trim((string)($p['type_key'] ?? $p['post_type'] ?? '')));
        $typeLabel = strtolower(trim((string)($p['type_label'] ?? '')));
        $hasQuiz = (int)($p['has_quiz'] ?? 0) === 1;
        $isGradable = isset($p['is_gradable']) ? ((int)$p['is_gradable'] === 1) : true;
        $isQuizLike = $hasQuiz || strpos($typeKey, 'quiz') !== false || strpos($typeLabel, 'quiz') !== false;
        if (!$isGradable && !$isQuizLike) continue;

        $category = 'activities';
        $displayCategory = 'activities';
        if (strpos($typeKey, 'recitation') !== false || strpos($typeLabel, 'recitation') !== false) {
            $category = 'recitation';
            $displayCategory = 'recitation';
        } elseif (strpos($typeKey, 'assignment') !== false || strpos($typeLabel, 'assignment') !== false) {
            $category = 'activities';
            $displayCategory = 'assignment';
        } elseif (strpos($typeKey, 'exam') !== false || strpos($typeLabel, 'exam') !== false) {
            $category = 'exam';
            $displayCategory = 'exam';
        } elseif ($isQuizLike) {
            $category = 'quiz';
            $displayCategory = 'quiz';
        }

        $postId = $conn->real_escape_string($p['id']);
        if ($isQuizLike) {
            $mRes = $conn->query("SELECT COALESCE(SUM(points),0) AS mx FROM tblpostquestion WHERE post_id='$postId' AND is_excluded=0");
            $mx = $mRes ? (float)($mRes->fetch_assoc()['mx'] ?? 0) : 0.0;
            $maxScore = $mx > 0 ? $mx : 100.0;
        } else {
            $pts = (float)($p['points'] ?? 0);
            $maxScore = $pts > 0 ? $pts : 100.0;
        }

        $assessmentTitle = trim((string)($p['title'] ?? ''));
        if ($assessmentTitle === '') $assessmentTitle = ucfirst($category) . ' item';

        $aid = (string)$p['id'];
        $assessments[$aid] = [
            'post_id' => $aid,
            'title' => $assessmentTitle,
            'category' => $category,
            'display_category' => $displayCategory,
            'grading_period' => gb_infer_assessment_period($p),
            'lesson_period' => strtolower(trim((string)($p['lesson_period'] ?? ''))),
            'max_score' => round($maxScore, 2),
            'date_created' => $p['created_at'],
            'due_date' => $p['due_date'],
            'scores' => []
        ];

        foreach ($students as $sid => $_) {
            $esid = $conn->real_escape_string($sid);
            $euid = $conn->real_escape_string((string)($students[$sid]['student_user_id'] ?? ''));
            $score = null;
            $takenAt = null;
            $whereStudent = "student_id='$esid'";
            if ($euid !== '') $whereStudent .= " OR student_id='$euid'";

            if ($isQuizLike) {
                $aRes = $conn->query("SELECT COALESCE(final_score, manual_score, auto_score, score, 0) AS sc, COALESCE(submitted_at, updated_at, created_at) AS taken_at
                    FROM tblquizattempt
                    WHERE post_id='$postId' AND ($whereStudent)
                    ORDER BY COALESCE(attempt_number,0) DESC, COALESCE(updated_at, created_at, submitted_at, started_at) DESC
                    LIMIT 1");
                if ($aRes && $aRes->num_rows) {
                    $ar = $aRes->fetch_assoc();
                    $score = ($ar['sc'] !== null && $ar['sc'] !== '') ? (float)$ar['sc'] : null;
                    $takenAt = $ar['taken_at'];
                }
            } else {
                $sRes = $conn->query("SELECT grade AS sc, submitted_at AS taken_at
                    FROM tblsubmission
                    WHERE post_id='$postId' AND ($whereStudent)
                    ORDER BY submitted_at DESC, id DESC
                    LIMIT 1");
                if ($sRes && $sRes->num_rows) {
                    $sr = $sRes->fetch_assoc();
                    $score = ($sr['sc'] !== null && $sr['sc'] !== '') ? (float)$sr['sc'] : null;
                    $takenAt = $sr['taken_at'];
                }
            }

            if ($score !== null) {
                $students[$sid]['categories'][$category]['raw'] += $score;
                $students[$sid]['categories'][$category]['max'] += $maxScore;
                $students[$sid]['categories'][$category]['items'] += 1;
            }

            $assessments[$aid]['scores'][$sid] = [
                'score' => $score,
                'taken_at' => $takenAt,
                'percentage' => ($score !== null && $maxScore > 0) ? round(($score / $maxScore) * 100, 2) : null,
            ];
        }
    }

    gb_apply_assessment_period_fallback($assessments);
    $attendancePeriods = gb_load_attendance_periods($conn, $class_id, $students);

    $rows = [];
    foreach ($students as $sid => $st) {
        $periodGrades = gb_compute_sheet_period_grades((string)$sid, $assessments, $attendancePeriods[$sid] ?? []);
        $final = gb_sheet_final_grade($periodGrades);
        $catOut = [];
        foreach ($st['categories'] as $cat => $cdata) {
            $pct = ($cdata['max'] > 0) ? (($cdata['raw'] / $cdata['max']) * 100.0) : 0.0;
            $catOut[$cat] = [
                'raw' => round($cdata['raw'], 2),
                'max' => round($cdata['max'], 2),
                'items' => (int)$cdata['items'],
                'percentage' => round($pct, 2),
                'equivalent' => ($cdata['max'] > 0) ? gb_score_equivalent($cdata['raw'], $cdata['max']) : null,
            ];
        }
        $rows[] = [
            'student_id' => $st['student_id'],
            'student_number' => $st['student_number'],
            'student_name' => $st['student_name'],
            'categories' => $catOut,
            'period_grades' => $periodGrades,
            'final_grade' => $final !== null ? round($final, 2) : 0,
            'equivalent' => gb_grade_equivalent($final),
        ];
    }

    usort($rows, function ($a, $b) {
        return strcmp($a['student_name'], $b['student_name']);
    });

    return [
        'scheme' => $scheme,
        'students' => $rows,
        'assessments' => array_values($assessments),
        'attendance_periods' => $attendancePeriods,
        'generated_at' => date('Y-m-d H:i:s'),
    ];
}
