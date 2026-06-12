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
    $assessmentRes = $conn->query("SELECT p.id, p.title, p.post_type_id, p.post_type, p.points, p.created_at, p.due_date,
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
        if (strpos($typeKey, 'recitation') !== false || strpos($typeLabel, 'recitation') !== false) {
            $category = 'recitation';
        } elseif (strpos($typeKey, 'assignment') !== false || strpos($typeLabel, 'assignment') !== false) {
            $category = 'activities';
        } elseif (strpos($typeKey, 'exam') !== false || strpos($typeLabel, 'exam') !== false) {
            $category = 'exam';
        } elseif ($isQuizLike) {
            $category = 'quiz';
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

    $weights = [
        'recitation' => (float)$scheme['weight_recitation'],
        'quiz' => (float)$scheme['weight_quiz'],
        'activities' => (float)$scheme['weight_activities'],
        'exam' => (float)$scheme['weight_exam'],
    ];

    $rows = [];
    foreach ($students as $sid => $st) {
        $final = 0.0;
        $catOut = [];
        foreach ($st['categories'] as $cat => $cdata) {
            $pct = ($cdata['max'] > 0) ? (($cdata['raw'] / $cdata['max']) * 100.0) : 0.0;
            $weighted = $pct * ($weights[$cat] / 100.0);
            $final += $weighted;
            $catOut[$cat] = [
                'raw' => round($cdata['raw'], 2),
                'max' => round($cdata['max'], 2),
                'items' => (int)$cdata['items'],
                'percentage' => round($pct, 2),
                'weight' => $weights[$cat],
                'weighted' => round($weighted, 2),
            ];
        }
        $rows[] = [
            'student_id' => $st['student_id'],
            'student_number' => $st['student_number'],
            'student_name' => $st['student_name'],
            'categories' => $catOut,
            'final_grade' => round($final, 2),
        ];
    }

    usort($rows, function ($a, $b) {
        return strcmp($a['student_name'], $b['student_name']);
    });

    return [
        'scheme' => $scheme,
        'students' => $rows,
        'assessments' => array_values($assessments),
        'generated_at' => date('Y-m-d H:i:s'),
    ];
}
