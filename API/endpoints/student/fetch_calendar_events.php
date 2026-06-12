<?php
/**
 * fetch_calendar_events.php  (student-facing)
 * Returns dated items (quizzes, activities, exams, assignments) for the logged-in
 * student in the requested month — only for classes the student is enrolled in.
 *
 * Query: year=YYYY, month=1..12 (defaults: current month)
 * Response: { status:'success', data:[ { id,title,post_type,due_date,subject_name,faculty_name,class_id } ] }
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

$userId = mysqli_real_escape_string($conn, $_SESSION['user_id']);

// Resolve student.id from user → tblstudent (match by username/email like student.php does)
$studentId = null;
if ($u = $conn->query("SELECT email, username FROM tbluser WHERE id='$userId' AND is_deleted=0 LIMIT 1")) {
    if ($r = $u->fetch_assoc()) {
        $un = mysqli_real_escape_string($conn, $r['username']);
        $em = mysqli_real_escape_string($conn, $r['email']);
        if ($s = $conn->query("SELECT id FROM tblstudent WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1")) {
            if ($sr = $s->fetch_assoc()) $studentId = (int)$sr['id'];
        }
    }
}
if (!$studentId) { echo json_encode(['status'=>'success','data'=>[]]); exit; }

$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
if ($month < 1 || $month > 12) $month = (int)date('n');

$start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
$end   = date('Y-m-t 23:59:59', strtotime($start));

$sql = "
  SELECT
    p.id,
    p.title,
    pt.type_name              AS post_type,
    p.due_date,
    s.subject_name            AS subject_name,
    CONCAT(f.first_name,' ',f.last_name) AS faculty_name,
    p.class_id
  FROM tblpost p
  LEFT JOIN tblposttype pt ON pt.id = p.posttype_id
  LEFT JOIN tblclass    c  ON c.id  = p.class_id
  LEFT JOIN tblsubject  s  ON s.id  = c.subject_id
  LEFT JOIN tblfaculty  f  ON f.id  = c.faculty_id
  INNER JOIN tblclassstudents cs ON cs.class_id = p.class_id
  WHERE cs.student_id = ?
    AND p.due_date IS NOT NULL
    AND p.due_date BETWEEN ? AND ?
    AND (p.is_deleted = 0 OR p.is_deleted IS NULL)
  ORDER BY p.due_date ASC
";

$out = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('iss', $studentId, $start, $end);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r;
    $stmt->close();
}
$conn->close();

echo json_encode(['status' => 'success', 'data' => $out]);
