<?php
if (ob_get_level() === 0) {
    ob_start();
}
// API/student/studentClassroom/get_classroom.php
ini_set('display_errors', 0);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();

    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'status' => 'error',
            'message' => 'Fatal error: ' . $error['message']
        ]);
        exit;
    }
});

require_once '_student_guard.php';

$userId = require_student();
$student = get_student_record($conn, $userId);

if (!$student) {
    json_out(['status' => 'error', 'message' => 'Student profile not found.'], 404);
}

$classId = trim($_GET['class_id'] ?? '');

if ($classId === '') {
    json_out(['status' => 'error', 'message' => 'Missing class_id.'], 400);
}

$studentDbId = (string)($student['id'] ?? '');
$studentUserId = (string)($student['user_id'] ?? '');
$studentId = $studentUserId !== '' ? $studentUserId : $studentDbId;

require_enrolled($conn, $classId, $studentId, $studentDbId);

$deptImageSelect = table_has_column($conn, 'tbldepartment', 'dept_image')
    ? 'd.dept_image'
    : 'NULL AS dept_image';

$stmt = $conn->prepare("
    SELECT c.*,
           s.subject_code,
           s.subject_name,
           co.course_code,
           co.course_name,
           d.dept_code,
           d.dept_name,
           {$deptImageSelect},
           f.first_name,
           f.middle_name,
           f.last_name,
           f.email AS faculty_email,
           f.is_dean AS faculty_is_dean,
           COALESCE(u.profile_picture, '') AS faculty_profile_picture,
           (
               SELECT da.role
               FROM tbldeanassignment da
               JOIN tbladmin a ON a.id = da.faculty_id AND a.is_deleted = 0
               WHERE da.is_active = 1
                 AND (
                     a.id = f.id
                     OR (a.email IS NOT NULL AND a.email <> '' AND a.email = f.email)
                     OR (a.username IS NOT NULL AND a.username <> '' AND a.username = f.username)
                 )
               ORDER BY da.assigned_at DESC
               LIMIT 1
           ) AS faculty_admin_role,
           (
               SELECT d.dept_name
               FROM tbldeanassignment da
               JOIN tbladmin a ON a.id = da.faculty_id AND a.is_deleted = 0
               JOIN tbldepartment d ON d.id = da.department_id
               WHERE da.is_active = 1
                 AND (
                     a.id = f.id
                     OR (a.email IS NOT NULL AND a.email <> '' AND a.email = f.email)
                     OR (a.username IS NOT NULL AND a.username <> '' AND a.username = f.username)
                 )
               ORDER BY da.assigned_at DESC
               LIMIT 1
           ) AS faculty_department_name,
           (
               SELECT d.dept_code
               FROM tbldeanassignment da
               JOIN tbladmin a ON a.id = da.faculty_id AND a.is_deleted = 0
               JOIN tbldepartment d ON d.id = da.department_id
               WHERE da.is_active = 1
                 AND (
                     a.id = f.id
                     OR (a.email IS NOT NULL AND a.email <> '' AND a.email = f.email)
                     OR (a.username IS NOT NULL AND a.username <> '' AND a.username = f.username)
                 )
               ORDER BY da.assigned_at DESC
               LIMIT 1
           ) AS faculty_department_code
    FROM tblclass c
    LEFT JOIN tblsubject s ON s.id = c.subject_id
    LEFT JOIN tblcourse co ON co.id = c.course_id
    LEFT JOIN tbldepartment d ON d.id = co.department_id AND COALESCE(d.is_deleted, 0) = 0
    LEFT JOIN tblfaculty f ON f.id = c.faculty_id
    LEFT JOIN tbluser u
        ON (u.username = f.username OR u.email = f.email)
       AND COALESCE(u.is_deleted, 0) = 0
    WHERE c.id = ?
      AND COALESCE(c.is_deleted, 0) = 0
    LIMIT 1
");
$stmt->bind_param('s', $classId);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$class) {
    json_out(['status' => 'error', 'message' => 'Class not found.'], 404);
}

$postTypes = [];

if (table_has_column($conn, 'tblposttype', 'type_key')) {
    $res = $conn->query("
        SELECT id, type_key, type_label, icon, color_bg, color_text, is_gradable, has_quiz
        FROM tblposttype
        WHERE COALESCE(is_deleted, 0) = 0
        ORDER BY sort_order ASC, id ASC
    ");

    while ($row = $res->fetch_assoc()) {
        $postTypes[] = $row;
    }
}

if (!$postTypes) {
    $postTypes = [
        ['id' => null, 'type_key' => 'lesson', 'type_label' => 'Lesson', 'icon' => 'fa-book-open', 'color_bg' => '#e6f7f2', 'color_text' => '#1a9e78', 'is_gradable' => 0, 'has_quiz' => 0],
        ['id' => null, 'type_key' => 'activity', 'type_label' => 'Activity', 'icon' => 'fa-tools', 'color_bg' => '#e8f0fe', 'color_text' => '#1f73db', 'is_gradable' => 1, 'has_quiz' => 0],
        ['id' => null, 'type_key' => 'quiz', 'type_label' => 'Quiz', 'icon' => 'fa-question-circle', 'color_bg' => '#ede9fe', 'color_text' => '#7c3aed', 'is_gradable' => 1, 'has_quiz' => 1],
        ['id' => null, 'type_key' => 'assignment', 'type_label' => 'Assignment', 'icon' => 'fa-clipboard-list', 'color_bg' => '#fff3e0', 'color_text' => '#f57c00', 'is_gradable' => 1, 'has_quiz' => 0],
        ['id' => null, 'type_key' => 'exam', 'type_label' => 'Exam', 'icon' => 'fa-file-signature', 'color_bg' => '#fdecea', 'color_text' => '#d93025', 'is_gradable' => 1, 'has_quiz' => 1]
    ];
}

$posts = [];

$stmt = $conn->prepare("
    SELECT p.*,
           qz.quiz_mode AS quiz_mode_db,
           qz.time_limit_seconds AS quiz_time_limit_seconds,
           qz.due_date AS quiz_due_date,
           qz.is_published AS quiz_is_published,
           qz.live_started_at AS quiz_live_started_at,
           qz.live_ended_at AS quiz_live_ended_at,
           qz.is_force_closed AS quiz_is_force_closed,
           qz.results_released_at AS quiz_results_released_at,
           TRIM(CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, ''))) AS author_name,
           (
               SELECT COUNT(*)
               FROM tblpostcomment pc
               WHERE pc.post_id = p.id
                 AND COALESCE(pc.is_deleted, 0) = 0
           ) AS comment_count
    FROM tblpost p
    LEFT JOIN tblfaculty f ON f.id = p.author_id
    LEFT JOIN tblquiz qz ON qz.post_id = p.id
    WHERE p.class_id = ?
      AND COALESCE(p.is_deleted, 0) = 0
      AND (
          COALESCE(p.submission_mode, 'individual') <> 'group'
          OR EXISTS (
              SELECT 1
              FROM tblpostgroups pg
              WHERE pg.post_id = p.id
                AND (pg.student_id = ? OR pg.student_id = ?)
          )
      )
    ORDER BY p.created_at DESC
");
$stmt->bind_param('sss', $classId, $studentId, $studentDbId);
$stmt->execute();
$res = $stmt->get_result();

while ($post = $res->fetch_assoc()) {
    if (isset($post['quiz_mode_db']) && $post['quiz_mode_db'] !== null && $post['quiz_mode_db'] !== '') {
        $post['quiz_mode'] = $post['quiz_mode_db'];
    }
    if (isset($post['quiz_time_limit_seconds']) && $post['quiz_time_limit_seconds'] !== null) {
        $post['time_limit_seconds'] = $post['quiz_time_limit_seconds'];
    }
    if (!empty($post['quiz_due_date'])) {
        $post['due_date'] = $post['quiz_due_date'];
    }
    if (isset($post['quiz_is_published'])) {
        $post['is_published'] = (int)$post['quiz_is_published'];
    }
    if (isset($post['quiz_is_force_closed'])) {
        $post['is_force_closed'] = (int)$post['quiz_is_force_closed'];
    }
    if (!empty($post['quiz_live_started_at'])) {
        $post['live_started_at'] = $post['quiz_live_started_at'];
    }
    if (!empty($post['quiz_live_ended_at'])) {
        $post['live_ended_at'] = $post['quiz_live_ended_at'];
    }
    if (!empty($post['quiz_results_released_at'])) {
        $post['results_released_at'] = $post['quiz_results_released_at'];
    }

    if (trim($post['author_name'] ?? '') === '') {
        $post['author_name'] = 'Faculty';
    }

    $post['attachments'] = [];
    $post['questions'] = [];
    $post['groups'] = [];

    $posts[$post['id']] = $post;
}

$stmt->close();

if ($posts) {
    $ids = array_keys($posts);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('s', count($ids));

    $stmt = $conn->prepare("
        SELECT *
        FROM tblpostattachment
        WHERE post_id IN ($placeholders)
        ORDER BY id ASC
    ");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($attachment = $res->fetch_assoc()) {
        if (isset($posts[$attachment['post_id']])) {
            $posts[$attachment['post_id']]['attachments'][] = $attachment;
        }
    }

    $stmt->close();

    $hasPostQuestion = $conn->query("SHOW TABLES LIKE 'tblpostquestion'");
    if ($hasPostQuestion && $hasPostQuestion->num_rows > 0) {
        $questionSelect = "id, post_id, question, points, order_num";
        if (table_has_column($conn, 'tblpostquestion', 'time_limit_seconds')) {
            $questionSelect .= ", time_limit_seconds";
        }

        $stmt = $conn->prepare("
            SELECT $questionSelect
            FROM tblpostquestion
            WHERE post_id IN ($placeholders)
            ORDER BY post_id ASC, order_num ASC, id ASC
        ");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $postId = $row['post_id'];
            if (!isset($posts[$postId])) {
                continue;
            }
            $posts[$postId]['questions'][] = [
                'id' => $row['id'],
                'question' => $row['question'] ?? '',
                'question_text' => $row['question'] ?? '',
                'points' => $row['points'] ?? 0,
                'time_limit_seconds' => $row['time_limit_seconds'] ?? null,
                'choices' => []
            ];
        }

        $stmt->close();
    } else {
        $questionLinkColumn = table_has_column($conn, 'tblquestions', 'post_id') ? 'post_id' : 'quiz_id';

        $stmt = $conn->prepare("
            SELECT q.*,
                   c.id AS choice_id,
                   c.choice_text,
                   c.is_correct,
                   c.sort_order AS choice_sort
            FROM tblquestions q
            LEFT JOIN tblchoices c ON c.question_id = q.id
            WHERE q.$questionLinkColumn IN ($placeholders)
              AND COALESCE(q.is_deleted, 0) = 0
            ORDER BY q.$questionLinkColumn ASC, q.id ASC, c.sort_order ASC, c.id ASC
        ");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $questionMap = [];

        while ($row = $res->fetch_assoc()) {
            $postId = $row[$questionLinkColumn];
            $questionId = $row['id'];

            if (!isset($questionMap[$postId])) {
                $questionMap[$postId] = [];
            }

            if (!isset($questionMap[$postId][$questionId])) {
                $questionMap[$postId][$questionId] = [
                    'id' => $questionId,
                    'question' => $row['question_text'] ?? '',
                    'question_text' => $row['question_text'] ?? '',
                    'points' => $row['points'] ?? 0,
                    'time_limit_seconds' => $row['time_limit_seconds'] ?? null,
                    'choices' => []
                ];
            }

            if (!empty($row['choice_id'])) {
                $questionMap[$postId][$questionId]['choices'][] = [
                    'id' => $row['choice_id'],
                    'choice_text' => $row['choice_text'],
                    'is_correct' => (int)($row['is_correct'] ?? 0)
                ];
            }
        }

        $stmt->close();

        foreach ($questionMap as $postId => $questions) {
            if (isset($posts[$postId])) {
                $posts[$postId]['questions'] = array_values($questions);
            }
        }
    }

    $stmt = $conn->prepare("
        SELECT pg.post_id,
               pg.group_number,
               pg.student_id,
               TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))) AS display_name
        FROM tblpostgroups pg
        LEFT JOIN tblstudent s ON s.id = pg.student_id OR s.user_id = pg.student_id
        WHERE pg.post_id IN ($placeholders)
        ORDER BY pg.post_id ASC, pg.group_number ASC
    ");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($group = $res->fetch_assoc()) {
        if (!isset($posts[$group['post_id']])) {
            continue;
        }

        $groupNumber = (string)($group['group_number'] ?? '1');

        if (!isset($posts[$group['post_id']]['groups'][$groupNumber])) {
            $posts[$group['post_id']]['groups'][$groupNumber] = [];
        }

        $posts[$group['post_id']]['groups'][$groupNumber][] = [
            'student_id' => $group['student_id'],
            'display_name' => trim($group['display_name'] ?? '') ?: 'Student'
        ];
    }

    $stmt->close();

    $submStmt = $conn->prepare("
        SELECT post_id,
               id AS submission_id,
               status,
               grade,
               submitted_at,
               file_name,
               comment
        FROM tblsubmission
        WHERE post_id IN ($placeholders)
          AND (student_id = ? OR student_id = ?)
        ORDER BY submitted_at DESC
    ");

    $subTypes = $types . 'ss';
    $subArgs = array_merge($ids, [$studentId, $studentDbId]);

    $submStmt->bind_param($subTypes, ...$subArgs);
    $submStmt->execute();
    $subRes = $submStmt->get_result();

    while ($submission = $subRes->fetch_assoc()) {
        $postId = $submission['post_id'];

        if (isset($posts[$postId]) && !isset($posts[$postId]['submission'])) {
            $posts[$postId]['submission'] = [
                'submission_id' => $submission['submission_id'],
                'status' => $submission['status'],
                'grade' => $submission['grade'],
                'submitted_at' => $submission['submitted_at'],
                'file_name' => $submission['file_name'],
                'comment' => $submission['comment']
            ];
        }
    }

    $submStmt->close();
}

$meeting = null;

if ($conn->query("SHOW TABLES LIKE 'tblmeeting'")->num_rows > 0) {
    $stmt = $conn->prepare("
        SELECT meet_url, meeting_code, meeting_topic
        FROM tblmeeting
        WHERE class_id = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param('s', $classId);
    $stmt->execute();
    $meeting = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$semester = null;

if ($conn->query("SHOW TABLES LIKE 'tblsemestersetting'")->num_rows > 0) {
    $semRes = $conn->query("
        SELECT semester, school_year
        FROM tblsemestersetting
        WHERE is_active = 1
          AND COALESCE(is_deleted, 0) = 0
        LIMIT 1
    ");

    $semester = $semRes ? $semRes->fetch_assoc() : null;
}

json_out([
    'status' => 'success',
    'class' => $class,
    'student' => [
        'id' => $studentId,
        'db_id' => $studentDbId,
        'user_id' => $studentUserId,
        'name' => student_display_name($student),
        'student_number' => $student['student_number'] ?? ''
    ],
    'post_types' => $postTypes,
    'posts' => array_values($posts),
    'meeting' => $meeting,
    'semester' => $semester
]);
