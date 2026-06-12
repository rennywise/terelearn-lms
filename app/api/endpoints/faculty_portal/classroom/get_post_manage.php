<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$post_id  = trim($_GET['post_id'] ?? '');
$class_id = trim($_GET['class_id'] ?? '');
if ($post_id === '' || $class_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'post_id and class_id required']);
    exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

function infer_cognitive_level(string $question): string {
    $text = strtolower(trim($question));
    if ($text === '') return 'remembering';

    $creating = ['propose', 'design', 'create', 'develop a strategy', 'formulate', 'plan'];
    $evaluating = ['evaluate', 'justify', 'critique', 'assess', 'most critical', 'best approach', 'recommend'];
    $analyzing = ['compare', 'distinguish', 'trade-off', 'analyze', 'influence', 'why might', 'how might', 'when comparing', 'difference'];
    $applying = ['scenario', 'a developer wants', 'a company is', 'would be most suitable', 'would most directly', 'imagine you are', 'which type', 'which challenge'];
    $understanding = ['why is', 'how does', 'main objective', 'key characteristic', 'significant advantage', 'important for'];

    foreach ($creating as $needle) {
        if (strpos($text, $needle) !== false) return 'creating';
    }
    foreach ($evaluating as $needle) {
        if (strpos($text, $needle) !== false) return 'evaluating';
    }
    foreach ($analyzing as $needle) {
        if (strpos($text, $needle) !== false) return 'analyzing';
    }
    foreach ($applying as $needle) {
        if (strpos($text, $needle) !== false) return 'applying';
    }
    foreach ($understanding as $needle) {
        if (strpos($text, $needle) !== false) return 'understanding';
    }

    if (preg_match('/^(what is|which|who|when|where|in what year)\b/', $text)) {
        return 'remembering';
    }

    return 'understanding';
}

$uid = $conn->real_escape_string($_SESSION['user_id']);
$pid = $conn->real_escape_string($post_id);
$cid = $conn->real_escape_string($class_id);

// Resolve faculty record from logged-in user.
$uRes = $conn->query("SELECT username,email FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
if (!$uRes || $uRes->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}
$uRow = $uRes->fetch_assoc();
$un = $conn->real_escape_string($uRow['username']);
$em = $conn->real_escape_string($uRow['email']);

$fRes = $conn->query("SELECT id,first_name,last_name FROM tblfaculty WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
if (!$fRes || $fRes->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Faculty record not found']);
    exit;
}
$fRow = $fRes->fetch_assoc();
$faculty_id = $conn->real_escape_string($fRow['id']);

// Authorize class ownership.
$cRes = $conn->query("
    SELECT c.id,c.class_code,c.section,c.class_days,c.schedule,c.class_semester,c.course_id,c.subject_id,
           s.subject_code,s.subject_name,co.course_code,co.course_name
    FROM tblclass c
    LEFT JOIN tblsubject s ON s.id=c.subject_id
    LEFT JOIN tblcourse co ON co.id=c.course_id
    WHERE c.id='$cid' AND c.is_deleted=0 AND c.faculty_id='$faculty_id'
    LIMIT 1
");
if (!$cRes || $cRes->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Class not found or access denied']);
    exit;
}
$class = $cRes->fetch_assoc();

$pRes = $conn->query("
    SELECT p.*,
           pt.type_key,
           pt.type_label,
           pt.icon AS type_icon,
           pt.color_bg AS type_color_bg,
           pt.color_text AS type_color_text,
           pt.is_gradable AS type_is_gradable,
           pt.has_quiz AS type_has_quiz,
           qz.quiz_mode AS quiz_mode_db,
           qz.max_attempts AS quiz_max_attempts,
           qz.time_limit_seconds AS quiz_time_limit_seconds,
           qz.due_date AS quiz_due_date,
           qz.live_started_at AS quiz_live_started_at,
           qz.live_ended_at AS quiz_live_ended_at,
           qz.is_force_closed AS quiz_is_force_closed,
           qz.results_released_at AS quiz_results_released_at,
           qz.is_published AS quiz_is_published
    FROM tblpost p
    LEFT JOIN tblposttype pt ON pt.id=p.post_type_id
    LEFT JOIN tblquiz qz ON qz.post_id=p.id
    WHERE p.id='$pid' AND p.class_id='$cid' AND p.is_deleted=0
    LIMIT 1
");
if (!$pRes || $pRes->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Post not found']);
    exit;
}
$post = $pRes->fetch_assoc();

$post['type_key'] = $post['type_key'] ?: ($post['post_type'] ?: 'custom');
$post['type_label'] = $post['type_label'] ?: ucfirst($post['type_key']);
$post['type_is_gradable'] = (int)($post['type_is_gradable'] ?? 0);
$post['type_has_quiz'] = (int)($post['type_has_quiz'] ?? 0);
if ($post['type_has_quiz'] === 1) {
    $modeFromQuiz = strtolower(trim((string)($post['quiz_mode_db'] ?? '')));
    $modeFromPost = strtolower(trim((string)($post['quiz_mode'] ?? '')));
    $timeMode = strtolower(trim((string)($post['time_mode'] ?? '')));
    if ($modeFromQuiz === 'live' || $modeFromQuiz === 'due_date') {
        $post['quiz_mode'] = $modeFromQuiz;
    } elseif ($modeFromPost === 'live' || $modeFromPost === 'due_date') {
        $post['quiz_mode'] = $modeFromPost;
    } elseif ($timeMode === 'live') {
        $post['quiz_mode'] = 'live';
    } else {
        $post['quiz_mode'] = 'due_date';
    }
    $post['is_published'] = isset($post['quiz_is_published']) ? (int)$post['quiz_is_published'] : (int)($post['is_published'] ?? 0);
    $post['is_force_closed'] = isset($post['quiz_is_force_closed']) ? (int)$post['quiz_is_force_closed'] : (int)($post['is_force_closed'] ?? 0);
    $post['live_started_at'] = $post['quiz_live_started_at'] ?: ($post['live_started_at'] ?? null);
    $post['live_ended_at'] = $post['quiz_live_ended_at'] ?: ($post['live_ended_at'] ?? null);
    $post['results_released_at'] = $post['quiz_results_released_at'] ?: ($post['results_released_at'] ?? null);
    if (!empty($post['quiz_due_date'])) $post['due_date'] = $post['quiz_due_date'];
}

$questions = [];
$has_cognitive_level = false;
$has_q_time_limit = false;
$colRes = $conn->query("SHOW COLUMNS FROM tblpostquestion LIKE 'cognitive_level'");
if ($colRes && $colRes->num_rows > 0) $has_cognitive_level = true;
$tlRes = $conn->query("SHOW COLUMNS FROM tblpostquestion LIKE 'time_limit_seconds'");
if ($tlRes && $tlRes->num_rows > 0) $has_q_time_limit = true;
$qSelect = "id,question,answer_key,points,order_num,is_excluded";
if ($has_cognitive_level) $qSelect .= ", cognitive_level";
if ($has_q_time_limit) $qSelect .= ", time_limit_seconds";
$qRes = $conn->query("
    SELECT $qSelect
    FROM tblpostquestion
    WHERE post_id='$pid'
    ORDER BY order_num ASC, id ASC
");
if ($qRes) {
    while ($q = $qRes->fetch_assoc()) {
        $qid = $conn->real_escape_string($q['id']);
        $storedLevel = strtolower(trim((string)($q['cognitive_level'] ?? '')));
        $inferredLevel = infer_cognitive_level((string)($q['question'] ?? ''));
        $allowedLevels = ['remembering','understanding','applying','analyzing','evaluating','creating'];
        if (!in_array($storedLevel, $allowedLevels, true) || ($storedLevel === 'remembering' && $inferredLevel !== 'remembering')) {
            $q['cognitive_level'] = $inferredLevel;
        } else {
            $q['cognitive_level'] = $storedLevel;
        }
        $q['choices'] = [];
        $cRes = $conn->query("
            SELECT id, choice_text, is_correct, order_num
            FROM tblpostchoice
            WHERE question_id='$qid'
            ORDER BY order_num ASC, id ASC
        ");
        if ($cRes) {
            while ($ch = $cRes->fetch_assoc()) {
                $ch['is_correct'] = (int)($ch['is_correct'] ?? 0);
                $q['choices'][] = $ch;
            }
        }
        $questions[] = $q;
    }
}

$attachments = [];
$aRes = $conn->query("
    SELECT id,attach_type,file_name,file_path,mime_type,url,created_at
    FROM tblpostattachment
    WHERE post_id='$pid'
    ORDER BY id ASC
");
if ($aRes) {
    while ($a = $aRes->fetch_assoc()) {
        $attachments[] = $a;
    }
}

$summary = [
    'question_count' => 0,
    'question_points' => 0.0,
    'enrolled_count' => 0,
    'submission_count' => 0,
    'graded_count' => 0,
    'average_score' => null
];

$metaRes = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(points),0) t FROM tblpostquestion WHERE post_id='$pid' AND is_excluded=0");
if ($metaRes && $metaRes->num_rows) {
    $m = $metaRes->fetch_assoc();
    $summary['question_count'] = (int)$m['c'];
    $summary['question_points'] = (float)$m['t'];
}

$eRes = $conn->query("SELECT COUNT(*) c FROM tblclassenrollment WHERE class_id='$cid' AND enrollment_status='enrolled'");
if ($eRes && $eRes->num_rows) $summary['enrolled_count'] = (int)$eRes->fetch_assoc()['c'];

$submissions = [];
if ((int)$post['type_has_quiz'] === 1) {
    $quizMaxScore = (float)($summary['question_points'] ?? 0);
    $sRes = $conn->query("
        SELECT base.student_id,
               qa.status,
               qa.submitted_at,
               COALESCE(qa.final_score, qa.manual_score, qa.auto_score, qa.score) AS final_score,
               COALESCE(qa.max_score, {$quizMaxScore}) AS max_score,
               COALESCE(ls.live_score, 0) AS live_score,
               st.student_number,
               CONCAT(st.last_name, ', ', st.first_name) AS student_name
        FROM (
            SELECT ce.student_id
            FROM tblclassenrollment ce
            WHERE ce.class_id='$cid' AND ce.enrollment_status='enrolled'
            UNION
            SELECT qe.student_id
            FROM tblquizenrollment qe
            WHERE qe.post_id='$pid' AND (qe.status IS NULL OR qe.status!='withdrawn')
        ) base
        LEFT JOIN tblstudent st ON st.id=base.student_id
        LEFT JOIN tblquizattempt qa ON qa.id = (
            SELECT qa2.id
            FROM tblquizattempt qa2
            WHERE qa2.post_id = '$pid' AND qa2.student_id = base.student_id
            ORDER BY
              COALESCE(qa2.attempt_number, 0) DESC,
              COALESCE(qa2.updated_at, qa2.created_at, qa2.started_at, qa2.submitted_at) DESC
            LIMIT 1
        )
        LEFT JOIN (
            SELECT a.id AS attempt_id,
                   COALESCE(SUM(
                     CASE
                       WHEN pc.is_correct = 1 THEN COALESCE(q.points, 0)
                       ELSE 0
                     END
                   ), 0) AS live_score
            FROM tblquizattempt a
            LEFT JOIN tblquizanswer ans ON ans.attempt_id = a.id
            LEFT JOIN tblpostchoice pc ON pc.id = ans.selected_choice_id
            LEFT JOIN tblpostquestion q ON q.id = ans.question_id
            WHERE a.post_id='$pid'
            GROUP BY a.id
        ) ls ON ls.attempt_id = qa.id
        ORDER BY student_name ASC
    ");
    if ($sRes) {
        $scoreSum = 0.0;
        $scoreN = 0;
        while ($r = $sRes->fetch_assoc()) {
            $submissions[] = $r;
            if (!empty($r['status']) && strtolower((string)$r['status']) !== 'not_started') {
                $summary['submission_count']++;
            }
            if (in_array(strtolower((string)($r['status'] ?? '')), ['submitted','graded','returned'], true) || $r['final_score'] !== null) {
                $summary['graded_count']++;
            }
            if ($r['final_score'] !== null) {
                $scoreSum += (float)$r['final_score'];
                $scoreN++;
            }
        }
        if ($scoreN > 0) $summary['average_score'] = round($scoreSum / $scoreN, 2);
    }
} else {
    // Build from enrolled roster first so non-quiz posts always show students,
    // then attach latest submission (supports student_id saved as tblstudent.id OR tblstudent.user_id).
    $sRes = $conn->query("
        SELECT
            ce.student_id AS enrolled_student_id,
            st.id AS student_db_id,
            st.user_id AS student_user_id,
            st.student_number,
            CONCAT(st.last_name, ', ', st.first_name) AS student_name,
            s.id AS submission_id,
            s.student_id AS submission_student_id,
            s.status,
            s.submitted_at,
            s.grade AS final_score,
            p.points AS max_score,
            s.file_name,
            s.file_path,
            s.comment
        FROM tblclassenrollment ce
        LEFT JOIN tblstudent st
          ON (st.id = ce.student_id OR st.user_id = ce.student_id) AND st.is_deleted=0
        LEFT JOIN tblpost p ON p.id = '$pid'
        LEFT JOIN tblsubmission s ON s.id = (
            SELECT s2.id
            FROM tblsubmission s2
            WHERE s2.post_id = '$pid'
              AND (
                s2.student_id = ce.student_id
                OR (st.id IS NOT NULL AND st.id <> '' AND s2.student_id = st.id)
                OR (st.user_id IS NOT NULL AND st.user_id <> '' AND s2.student_id = st.user_id)
              )
            ORDER BY s2.submitted_at DESC, s2.id DESC
            LIMIT 1
        )
        WHERE ce.class_id = '$cid' AND ce.enrollment_status = 'enrolled'
        ORDER BY student_name ASC
    ");
    if ($sRes) {
        $scoreSum = 0.0;
        $scoreN = 0;
        while ($r = $sRes->fetch_assoc()) {
            $r['student_id'] = $r['student_db_id'] ?: ($r['enrolled_student_id'] ?? ($r['submission_student_id'] ?? ''));
            $submissions[] = $r;
            if (!empty($r['submission_id'])) {
                $summary['submission_count']++;
            }
            if ($r['status'] === 'graded' || $r['final_score'] !== null) $summary['graded_count']++;
            if ($r['final_score'] !== null) {
                $scoreSum += (float)$r['final_score'];
                $scoreN++;
            }
        }
        if ($scoreN > 0) $summary['average_score'] = round($scoreSum / $scoreN, 2);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to load submissions: ' . $conn->error]);
        exit;
    }

    // Fallback: some student uploads are stored in tblpostattachment with student_id.
    // Merge these files into rows even when tblsubmission is missing.
    $attByStudent = [];
    $sfRes = $conn->query("
        SELECT student_id, file_name, file_path, created_at
        FROM tblpostattachment
        WHERE post_id='$pid'
          AND student_id IS NOT NULL
          AND student_id <> ''
          AND (file_path IS NOT NULL OR file_name IS NOT NULL)
        ORDER BY created_at DESC, id DESC
    ");
    if ($sfRes) {
        while ($sf = $sfRes->fetch_assoc()) {
            $sid = (string)($sf['student_id'] ?? '');
            if ($sid === '') continue;
            if (!isset($attByStudent[$sid])) $attByStudent[$sid] = $sf;
        }
    }
    if (!empty($attByStudent)) {
        foreach ($submissions as &$subRow) {
            $sidDb = (string)($subRow['student_db_id'] ?? $subRow['enrolled_student_id'] ?? $subRow['student_id'] ?? '');
            $sidUser = (string)($subRow['student_user_id'] ?? '');
            $att = null;
            if ($sidDb !== '' && isset($attByStudent[$sidDb])) $att = $attByStudent[$sidDb];
            if (!$att && $sidUser !== '' && isset($attByStudent[$sidUser])) $att = $attByStudent[$sidUser];
            if (!$att && !empty($subRow['enrolled_student_id']) && isset($attByStudent[(string)$subRow['enrolled_student_id']])) {
                $att = $attByStudent[(string)$subRow['enrolled_student_id']];
            }
            if (!$att) continue;
            $hasSubmissionFile = !empty($subRow['file_path']) || !empty($subRow['file_name']);
            if (!$hasSubmissionFile) {
                $subRow['file_name'] = $att['file_name'] ?? $subRow['file_name'];
                $subRow['file_path'] = $att['file_path'] ?? $subRow['file_path'];
                if (empty($subRow['submitted_at'])) {
                    $subRow['submitted_at'] = $att['created_at'] ?? null;
                }
                if (empty($subRow['submission_id'])) {
                    // Attachment-only upload fallback: mark as submitted for UI purposes.
                    $subRow['status'] = $subRow['status'] ?: 'submitted';
                    $summary['submission_count']++;
                }
            }
        }
        unset($subRow);
    }
}

echo json_encode([
    'status' => 'success',
    'class' => $class,
    'post' => $post,
    'summary' => $summary,
    'questions' => $questions,
    'attachments' => $attachments,
    'submissions' => $submissions
]);
