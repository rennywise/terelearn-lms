<?php
/**
 * API/student/get_student_tasks.php
 * Returns a student-facing work timeline across enrolled classes.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

function stw_column_exists(mysqli $conn, string $table, string $column): bool {
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
    $ok = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $ok;
}

function stw_table_exists(mysqli $conn, string $table): bool {
    $safe = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '$safe'");
    return $res && $res->num_rows > 0;
}

function stw_student_id(mysqli $conn, string $userId): ?string {
    $uid = $conn->real_escape_string($userId);
    $res = $conn->query("SELECT id FROM tblstudent WHERE user_id='$uid' AND is_deleted=0 LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) return (string)$row['id'];

    $userRes = $conn->query("SELECT username, email FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
    if (!$userRes || !($user = $userRes->fetch_assoc())) return null;

    $username = $conn->real_escape_string($user['username'] ?? '');
    $email = $conn->real_escape_string($user['email'] ?? '');
    $res = $conn->query("SELECT id FROM tblstudent WHERE (username='$username' OR email='$email') AND is_deleted=0 LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) return (string)$row['id'];
    return null;
}

function stw_post_kind(array $row): string {
    $raw = strtolower(trim((string)($row['type_key'] ?? $row['post_type'] ?? $row['type_label'] ?? '')));
    if (strpos($raw, 'quiz') !== false) return 'quiz';
    if (strpos($raw, 'exam') !== false) return 'exam';
    if (strpos($raw, 'assign') !== false) return 'assignment';
    if (strpos($raw, 'activity') !== false || strpos($raw, 'task') !== false) return 'activity';
    if (strpos($raw, 'lesson') !== false) return 'lesson';
    return $raw !== '' ? $raw : 'activity';
}

function stw_date_value(?string $value): int {
    if (!$value) return 0;
    $ts = strtotime($value);
    return $ts ?: 0;
}

function stw_score_text(array $attempt, array $post): ?string {
    foreach (['final_score', 'score', 'auto_score', 'manual_score'] as $key) {
        if (isset($attempt[$key]) && $attempt[$key] !== '' && $attempt[$key] !== null) {
            $score = rtrim(rtrim((string)$attempt[$key], '0'), '.');
            $max = $post['points'] ?? '';
            return $max !== '' && $max !== null ? $score . '/' . $max : $score;
        }
    }
    return null;
}

$userId = (string)$_SESSION['user_id'];
$studentId = stw_student_id($conn, $userId);

if (!$studentId) {
    echo json_encode(['status' => 'success', 'pending' => [], 'completed' => [], 'items' => []]);
    exit;
}

$postTypeJoin = '';
$postTypeSelect = "NULL AS type_key, NULL AS type_label, NULL AS type_icon, NULL AS type_color_bg, NULL AS type_color_text";
if (stw_table_exists($conn, 'tblposttype')) {
    if (stw_column_exists($conn, 'tblpost', 'post_type_id')) {
        $postTypeJoin = "LEFT JOIN tblposttype pt ON pt.id = p.post_type_id";
    } elseif (stw_column_exists($conn, 'tblpost', 'posttype_id')) {
        $postTypeJoin = "LEFT JOIN tblposttype pt ON pt.id = p.posttype_id";
    }
    if ($postTypeJoin !== '') {
        $postTypeSelect = "
            pt.type_key,
            pt.type_label,
            pt.icon AS type_icon,
            pt.color_bg AS type_color_bg,
            pt.color_text AS type_color_text
        ";
    }
}

$quizJoin = '';
$quizSelect = "
    NULL AS quiz_mode,
    NULL AS quiz_due_date,
    NULL AS quiz_time_limit_seconds,
    NULL AS quiz_is_published,
    NULL AS quiz_live_started_at,
    NULL AS quiz_live_ended_at,
    NULL AS quiz_results_released_at
";
if (stw_table_exists($conn, 'tblquiz')) {
    $quizJoin = "LEFT JOIN tblquiz q ON q.post_id = p.id OR q.id = p.id";
    $quizSelect = "
        q.quiz_mode,
        q.due_date AS quiz_due_date,
        q.time_limit_seconds AS quiz_time_limit_seconds,
        q.is_published AS quiz_is_published,
        q.live_started_at AS quiz_live_started_at,
        q.live_ended_at AS quiz_live_ended_at,
        q.results_released_at AS quiz_results_released_at
    ";
}

$groupFilter = '';
if (stw_table_exists($conn, 'tblpostgroups') && stw_column_exists($conn, 'tblpost', 'submission_mode')) {
    $groupFilter = "
      AND (
        COALESCE(p.submission_mode, 'individual') <> 'group'
        OR EXISTS (
          SELECT 1
          FROM tblpostgroups pg
          WHERE pg.post_id = p.id
            AND (pg.student_id = ? OR pg.student_id = ?)
        )
      )
    ";
}

$orderParts = [];
if (stw_column_exists($conn, 'tblpost', 'due_date')) $orderParts[] = 'p.due_date';
if (stw_column_exists($conn, 'tblpost', 'close_at')) $orderParts[] = 'p.close_at';
if (stw_column_exists($conn, 'tblpost', 'created_at')) $orderParts[] = 'p.created_at';
$orderExpr = $orderParts ? 'COALESCE(' . implode(', ', $orderParts) . ')' : 'p.id';

$sql = "
    SELECT
        p.*,
        c.class_code,
        c.class_semester,
        c.section,
        c.year_level,
        s.subject_code,
        s.subject_name,
        co.course_code,
        co.course_name,
        TRIM(CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, ''))) AS faculty_name,
        $postTypeSelect,
        $quizSelect
    FROM tblpost p
    INNER JOIN tblclassenrollment ce
        ON ce.class_id = p.class_id
       AND ce.enrollment_status = 'enrolled'
       AND ce.student_id = ?
    INNER JOIN tblclass c
        ON c.id = p.class_id
       AND COALESCE(c.is_deleted, 0) = 0
       AND COALESCE(c.is_active, 1) = 1
    LEFT JOIN tblsubject s ON s.id = c.subject_id
    LEFT JOIN tblcourse co ON co.id = c.course_id
    LEFT JOIN tblfaculty f ON f.id = c.faculty_id
    $postTypeJoin
    $quizJoin
    WHERE COALESCE(p.is_deleted, 0) = 0
    $groupFilter
    ORDER BY $orderExpr ASC, p.id DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare work timeline.']);
    exit;
}

if ($groupFilter !== '') {
    $stmt->bind_param('sss', $studentId, $studentId, $userId);
} else {
    $stmt->bind_param('s', $studentId);
}

$stmt->execute();
$res = $stmt->get_result();
$posts = [];
$postIds = [];

while ($row = $res->fetch_assoc()) {
    $kind = stw_post_kind($row);
    if (!in_array($kind, ['activity', 'assignment', 'quiz'], true)) {
        continue;
    }
    $row['_kind'] = $kind;
    $row['_due'] = $row['quiz_due_date'] ?: ($row['due_date'] ?? ($row['close_at'] ?? null));

    if ($kind === 'quiz') {
        $quizMode = strtolower(str_replace('_', '-', (string)($row['quiz_mode'] ?? '')));
        $hasDueDate = stw_date_value($row['_due']) > 0;
        $isLiveQuiz = strpos($quizMode, 'live') !== false
            || (!empty($row['quiz_live_started_at']) && strpos($quizMode, 'self') === false);

        if (!$hasDueDate || $isLiveQuiz) {
            continue;
        }
    }

    $row['_class_name'] = trim(($row['section'] ? $row['section'] . ' ' : '') . ($row['subject_code'] ?: $row['class_code']));
    $row['_class_subtitle'] = $row['subject_name'] ?: ($row['course_name'] ?: '');
    $posts[$row['id']] = $row;
    $postIds[] = $row['id'];
}
$stmt->close();

$submissions = [];
$attempts = [];

if ($postIds && stw_table_exists($conn, 'tblsubmission')) {
    $placeholders = implode(',', array_fill(0, count($postIds), '?'));
    $types = str_repeat('s', count($postIds)) . 'ss';
    $args = array_merge($postIds, [$studentId, $userId]);
    $stmt = $conn->prepare("
        SELECT *
        FROM tblsubmission
        WHERE post_id IN ($placeholders)
          AND (student_id = ? OR student_id = ?)
        ORDER BY submitted_at DESC
    ");
    if ($stmt) {
        $stmt->bind_param($types, ...$args);
        $stmt->execute();
        $subRes = $stmt->get_result();
        while ($row = $subRes->fetch_assoc()) {
            if (!isset($submissions[$row['post_id']])) $submissions[$row['post_id']] = $row;
        }
        $stmt->close();
    }
}

if ($postIds && stw_table_exists($conn, 'tblquizattempt')) {
    $placeholders = implode(',', array_fill(0, count($postIds), '?'));
    $types = str_repeat('s', count($postIds)) . 'ss';
    $args = array_merge($postIds, [$studentId, $userId]);
    $stmt = $conn->prepare("
        SELECT *
        FROM tblquizattempt
        WHERE post_id IN ($placeholders)
          AND (student_id = ? OR student_id = ?)
        ORDER BY attempt_number DESC, submitted_at DESC, started_at DESC
    ");
    if ($stmt) {
        $stmt->bind_param($types, ...$args);
        $stmt->execute();
        $attRes = $stmt->get_result();
        while ($row = $attRes->fetch_assoc()) {
            if (!isset($attempts[$row['post_id']])) $attempts[$row['post_id']] = $row;
        }
        $stmt->close();
    }
}

$items = [];
$now = time();

foreach ($posts as $postId => $post) {
    $kind = $post['_kind'];
    $submission = $submissions[$postId] ?? null;
    $attempt = $attempts[$postId] ?? null;
    $attemptStatus = strtolower((string)($attempt['status'] ?? ''));
    $submissionStatus = strtolower((string)($submission['status'] ?? ''));
    $done = false;
    $statusLabel = 'Not started';
    $statusTone = 'pending';
    $statusAt = null;
    $scoreText = null;

    if (in_array($kind, ['quiz', 'exam'], true)) {
        if (in_array($attemptStatus, ['submitted', 'completed', 'finished', 'graded'], true)) {
            $done = true;
            $statusLabel = $kind === 'quiz' ? 'Taken' : 'Submitted';
            $statusTone = 'done';
            $statusAt = $attempt['submitted_at'] ?? null;
            $scoreText = stw_score_text($attempt, $post);
        } elseif ($attemptStatus === 'in_progress') {
            $statusLabel = 'In progress';
            $statusTone = 'active';
        }
    } elseif ($submission) {
        $done = true;
        $statusLabel = $submissionStatus === 'graded' ? 'Graded' : 'Submitted';
        $statusTone = 'done';
        $statusAt = $submission['submitted_at'] ?? null;
        if (($submission['grade'] ?? '') !== '' && $submission['grade'] !== null) {
            $scoreText = (string)$submission['grade'];
        }
    }

    $dueTs = stw_date_value($post['_due']);
    $isLate = !$done && $dueTs > 0 && $dueTs < $now;
    if ($isLate) {
        $statusLabel = in_array($kind, ['quiz', 'exam'], true) ? 'Not taken' : 'Missing';
        $statusTone = 'late';
    }

    $quizMode = strtolower(str_replace('_', '-', (string)($post['quiz_mode'] ?? '')));
    $modeLabel = null;
    if ($kind === 'quiz') {
        if (strpos($quizMode, 'self') !== false) $modeLabel = 'Self-paced';
        elseif (strpos($quizMode, 'live') !== false) $modeLabel = 'Live';
    }

    $items[] = [
        'id' => $postId,
        'class_id' => $post['class_id'],
        'title' => $post['title'] ?: ($post['topic'] ?: ucfirst($kind)),
        'type' => $kind,
        'type_label' => $kind === 'quiz' ? 'Quiz' : ($kind === 'exam' ? 'Exam' : ($kind === 'assignment' ? 'Assignment' : 'Activity')),
        'class_name' => trim($post['_class_name']) ?: ($post['class_code'] ?? 'Class'),
        'class_subtitle' => $post['_class_subtitle'],
        'class_semester' => $post['class_semester'] ?? '',
        'faculty_name' => trim($post['faculty_name'] ?? '') ?: 'Faculty',
        'created_at' => $post['created_at'] ?? null,
        'due_date' => $post['_due'],
        'status' => $statusLabel,
        'status_tone' => $statusTone,
        'status_at' => $statusAt,
        'is_completed' => $done,
        'is_late' => $isLate,
        'points' => $post['points'] ?? null,
        'score_text' => $scoreText,
        'mode_label' => $modeLabel,
        'time_limit_seconds' => $kind === 'quiz' ? ($post['quiz_time_limit_seconds'] ?? null) : null,
        'submission_id' => $submission['id'] ?? ($submission['submission_id'] ?? null),
        'attempt_status' => $attemptStatus,
        'submission_status' => $submissionStatus
    ];
}

usort($items, function ($a, $b) {
    $ad = stw_date_value($a['due_date']) ?: stw_date_value($a['created_at']);
    $bd = stw_date_value($b['due_date']) ?: stw_date_value($b['created_at']);
    return $ad <=> $bd;
});

$pending = array_values(array_filter($items, fn($item) => empty($item['is_completed'])));
$completed = array_values(array_filter($items, fn($item) => !empty($item['is_completed'])));

usort($completed, function ($a, $b) {
    $ad = stw_date_value($a['status_at']) ?: stw_date_value($a['created_at']);
    $bd = stw_date_value($b['status_at']) ?: stw_date_value($b['created_at']);
    return $bd <=> $ad;
});

echo json_encode([
    'status' => 'success',
    'pending' => $pending,
    'completed' => $completed,
    'items' => $items
]);
?>
