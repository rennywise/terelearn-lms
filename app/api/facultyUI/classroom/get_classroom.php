<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
/**
 * API/facultyUI/classroom/get_classroom.php
 * FIXED: reads questions from tblpostquestion + tblpostchoice (same tables save_post.php writes to)
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); 
    exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';

$class_id = trim($_GET['class_id'] ?? '');
$user_id  = $_SESSION['user_id'];

if (!$class_id) {
    echo json_encode(['status' => 'error', 'message' => 'class_id required']); 
    exit;
}

$esc_class_id = $conn->real_escape_string($class_id);
$esc_user_id  = $conn->real_escape_string($user_id);

$userRes = $conn->query("SELECT username FROM tbluser WHERE id = '$esc_user_id' AND is_deleted = 0 LIMIT 1");
if (!$userRes || $userRes->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User record not found']); exit;
}
$username     = $userRes->fetch_assoc()['username'];
$esc_username = $conn->real_escape_string($username);

$facultyRes = $conn->query("SELECT id FROM tblfaculty WHERE username = '$esc_username' AND is_deleted = 0 LIMIT 1");
if (!$facultyRes || $facultyRes->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Faculty record not found for this user']); exit;
}
$faculty_id     = $facultyRes->fetch_assoc()['id'];
$esc_faculty_id = $conn->real_escape_string($faculty_id);

$classRes = $conn->query("
    SELECT c.*, s.subject_code, s.subject_name, co.course_code, co.course_name,
           f.first_name, f.middle_name, f.last_name, f.email AS faculty_email
    FROM   tblclass c
    LEFT JOIN tblsubject  s  ON s.id  = c.subject_id
    LEFT JOIN tblcourse   co ON co.id = c.course_id
    LEFT JOIN tblfaculty  f  ON f.id  = c.faculty_id
    WHERE  c.id = '$esc_class_id' AND c.faculty_id = '$esc_faculty_id' AND c.is_deleted = 0
    LIMIT 1
");
if (!$classRes || $classRes->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Class not found or access denied']); exit;
}
$classData = $classRes->fetch_assoc();

/* ── POSTS ── */
$postsRes = $conn->query("
    SELECT p.*,
           qz.quiz_mode AS quiz_mode_db,
           qz.time_limit_seconds AS quiz_time_limit_seconds,
           qz.due_date AS quiz_due_date,
           qz.is_published AS quiz_is_published,
           qz.live_started_at AS quiz_live_started_at,
           qz.live_ended_at AS quiz_live_ended_at,
           qz.is_force_closed AS quiz_is_force_closed,
           qz.results_released_at AS quiz_results_released_at,
           CONCAT(f.first_name,' ',f.last_name) AS author_name,
           (SELECT COUNT(*) FROM tblpostcomment pc WHERE pc.post_id=p.id) AS comment_count
    FROM   tblpost p
    LEFT JOIN tblfaculty f ON f.id = p.author_id
    LEFT JOIN tblquiz qz ON qz.post_id = p.id
    WHERE  p.class_id = '$esc_class_id' AND p.is_deleted = 0
    ORDER  BY p.created_at DESC
");
$posts = [];
$has_cognitive_level = false;
$has_q_time_limit = false;
$colRes = $conn->query("SHOW COLUMNS FROM tblpostquestion LIKE 'cognitive_level'");
if ($colRes && $colRes->num_rows > 0) $has_cognitive_level = true;
$tlRes = $conn->query("SHOW COLUMNS FROM tblpostquestion LIKE 'time_limit_seconds'");
if ($tlRes && $tlRes->num_rows > 0) $has_q_time_limit = true;
if ($postsRes) {
    while ($row = $postsRes->fetch_assoc()) {
        $pid = $conn->real_escape_string($row['id']);

        // Normalize quiz fields from tblquiz (authoritative) for UI labels/chips.
        if (isset($row['quiz_mode_db']) && $row['quiz_mode_db'] !== null && $row['quiz_mode_db'] !== '') {
            $row['quiz_mode'] = $row['quiz_mode_db'];
        }
        if (!empty($row['quiz_due_date'])) $row['due_date'] = $row['quiz_due_date'];
        if (isset($row['quiz_is_published'])) $row['is_published'] = (int)$row['quiz_is_published'];
        if (isset($row['quiz_is_force_closed'])) $row['is_force_closed'] = (int)$row['quiz_is_force_closed'];
        if (!empty($row['quiz_live_started_at'])) $row['live_started_at'] = $row['quiz_live_started_at'];
        if (!empty($row['quiz_live_ended_at'])) $row['live_ended_at'] = $row['quiz_live_ended_at'];
        if (!empty($row['quiz_results_released_at'])) $row['results_released_at'] = $row['quiz_results_released_at'];

        // Attachments
        $attRes = $conn->query("SELECT * FROM tblpostattachment WHERE post_id='$pid' ORDER BY id ASC");
        $row['attachments'] = [];
        if ($attRes) while ($a = $attRes->fetch_assoc()) $row['attachments'][] = $a;

        // ── QUESTIONS: tblpostquestion + tblpostchoice (matches what save_post.php writes) ──
        $qSelect = "id, post_id, question, answer_key, points, order_num";
        if ($has_cognitive_level) $qSelect .= ", cognitive_level";
        if ($has_q_time_limit)    $qSelect .= ", time_limit_seconds";
        $qRes = $conn->query("
            SELECT $qSelect
            FROM   tblpostquestion
            WHERE  post_id = '$pid'
            ORDER  BY order_num ASC, id ASC
        ");
        $row['questions'] = [];
        if ($qRes) {
            while ($q = $qRes->fetch_assoc()) {
                $qid  = $conn->real_escape_string($q['id']);
                $cRes = $conn->query("
                    SELECT id, question_id, choice_text, choice_text AS text, is_correct, order_num
                    FROM   tblpostchoice
                    WHERE  question_id = '$qid'
                    ORDER  BY order_num ASC, id ASC
                ");
                $q['choices'] = [];
                if ($cRes) {
                    while ($c = $cRes->fetch_assoc()) {
                        $c['is_correct'] = (int)$c['is_correct'];
                        $q['choices'][] = $c;
                    }
                }
                $row['questions'][] = $q;
            }
        }

        // Groups
        $row['groups'] = [];
        $row['submission_mode'] = 'individual';
        $pgRes = $conn->query("SELECT * FROM tblpostgroups WHERE post_id='$pid' ORDER BY group_number ASC");
        if ($pgRes) {
            while ($pg = $pgRes->fetch_assoc()) {
                $row['submission_mode'] = $pg['submission_mode'] ?? 'individual';
                if ($pg['submission_mode'] === 'group') {
                    $gn = (int)$pg['group_number'];
                    if (!isset($row['groups'][$gn])) $row['groups'][$gn] = [];
                    if ($pg['student_id']) {
                        $sRes2 = $conn->query("SELECT CONCAT(first_name,' ',last_name) AS display_name FROM tblstudent WHERE id='{$conn->real_escape_string($pg['student_id'])}' LIMIT 1");
                        if ($sRes2 && $srow = $sRes2->fetch_assoc()) {
                            $row['groups'][$gn][] = ['student_id' => $pg['student_id'], 'display_name' => $srow['display_name']];
                        }
                    }
                }
            }
        }
        $posts[] = $row;
    }
}

/* ── ENROLLED STUDENTS ── */
$peopleRes = $conn->query("
    SELECT s.id, TRIM(CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',s.last_name)) AS full_name,
           s.email, s.student_number, s.year_level, s.section, co.course_code, co.course_name,
           COALESCE(u.profile_picture, '') AS profile_picture
    FROM   tblclassenrollment ce
    JOIN   tblstudent s  ON s.id = ce.student_id AND s.is_deleted = 0
    LEFT JOIN tblcourse co ON co.id = s.course_id
    LEFT JOIN tbluser u
           ON (u.email = s.email OR u.username = s.username)
          AND u.is_deleted = 0
    WHERE  ce.class_id = '$esc_class_id' AND ce.enrollment_status = 'enrolled'
    ORDER  BY s.last_name ASC, s.first_name ASC
");
$people = [];
if ($peopleRes) while ($row = $peopleRes->fetch_assoc()) $people[] = $row;

/* ── JOIN REQUESTS ── */
$jrRes = $conn->query("
    SELECT s.id, TRIM(CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',s.last_name)) AS full_name,
           s.email, s.student_number, s.year_level, s.section, co.course_code, co.course_name,
           ce.enrolled_at AS requested_at
    FROM   tblclassenrollment ce
    JOIN   tblstudent s  ON s.id = ce.student_id AND s.is_deleted = 0
    LEFT JOIN tblcourse co ON co.id = s.course_id
    WHERE  ce.class_id = '$esc_class_id' AND ce.enrollment_status = 'pending' AND ce.source = 'join_request'
    ORDER  BY ce.enrolled_at DESC
");
$joinRequests = [];
if ($jrRes) while ($row = $jrRes->fetch_assoc()) $joinRequests[] = $row;

/* ── INVITATIONS ── */
$invRes = $conn->query("
    SELECT s.id, TRIM(CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',s.last_name)) AS full_name,
           s.email, s.student_number, s.year_level, s.section, co.course_code, co.course_name,
           inv.invitation_status, inv.invited_at, inv.responded_at
    FROM   tblinvitations inv
    JOIN   tblstudent s  ON s.id = inv.student_id AND s.is_deleted = 0
    LEFT JOIN tblcourse co ON co.id = s.course_id
    WHERE  inv.class_id = '$esc_class_id' AND inv.invitation_status IN ('pending','declined')
      AND  s.id NOT IN (SELECT student_id FROM tblclassenrollment WHERE class_id='$esc_class_id' AND enrollment_status='enrolled')
    ORDER  BY inv.invited_at DESC
");
$invitations = [];
if ($invRes) while ($row = $invRes->fetch_assoc()) $invitations[] = $row;

/* ── SEMESTER ── */
$semRes  = $conn->query("SELECT semester, school_year FROM tblsemestersetting WHERE is_active=1 LIMIT 1");
$semData = $semRes ? $semRes->fetch_assoc() : null;

$conn->close();

echo json_encode([
    'status'       => 'success',
    'class'        => $classData,
    'posts'        => $posts,
    'people'       => $people,
    'joinRequests' => $joinRequests,
    'invitations'  => $invitations,
    'pending'      => $joinRequests,
    'semester'     => $semData,
]);
