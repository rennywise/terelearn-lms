<?php
session_start();
if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
require_once __DIR__ . '/../../../core/db_connect.php';

$class_id = trim($_GET['class_id'] ?? '');
$q        = trim($_GET['q'] ?? '');

if (!$class_id) { echo json_encode(['status'=>'error','message'=>'Missing class_id']); exit; }

$esc_q   = $conn->real_escape_string($q);
$esc_cid = $conn->real_escape_string($class_id);

// Get course_id of the class so we only show relevant students
$clRes = $conn->query("SELECT course_id FROM tblclass WHERE id='$esc_cid' AND is_deleted=0 LIMIT 1");
$clRow = $clRes ? $clRes->fetch_assoc() : null;
$courseFilter = '';
if ($clRow && $clRow['course_id']) {
    $cid = (int)$clRow['course_id'];
    $courseFilter = "AND s.course_id = $cid";
}

$search = $esc_q ? "AND (s.first_name LIKE '%$esc_q%' OR s.last_name LIKE '%$esc_q%' OR s.student_number LIKE '%$esc_q%' OR s.email LIKE '%$esc_q%')" : '';

$sql = "
    SELECT s.id, s.student_number, s.first_name, s.middle_name, s.last_name,
           s.email, s.year_level, s.section,
           c.course_code,
           -- check if already enrolled or pending
           (SELECT ce.enrollment_status FROM tblclassenrollment ce 
            WHERE ce.class_id='$esc_cid' AND ce.student_id=s.id 
            ORDER BY ce.enrolled_at DESC LIMIT 1) AS enrollment_status,
           (SELECT cs.id FROM tblclassstudents cs 
            WHERE cs.class_id='$esc_cid' AND cs.student_id=s.id AND cs.is_deleted=0 LIMIT 1) AS in_class,
           (SELECT inv.invitation_status FROM tblinvitations inv
            WHERE inv.class_id='$esc_cid' AND inv.student_id=s.id LIMIT 1) AS invitation_status
    FROM tblstudent s
    LEFT JOIN tblcourse c ON c.id = s.course_id
    WHERE s.is_deleted=0 AND s.is_active=1
    $courseFilter
    $search
    ORDER BY s.last_name, s.first_name
    LIMIT 30
";

$res = $conn->query($sql);
$students = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $students[] = [
            'id'                => $row['id'],
            'student_number'    => $row['student_number'],
            'full_name'         => trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'].' ' : '') . $row['last_name']),
            'email'             => $row['email'] ?? '',
            'section'           => $row['section'] ?? '',
            'course_code'       => $row['course_code'] ?? '',
            'enrollment_status' => $row['enrollment_status'], // null, pending, enrolled, dropped
            'in_class'          => !empty($row['in_class']),
            'invitation_status' => $row['invitation_status'], // null, pending, accepted, declined
        ];
    }
}
echo json_encode(['status'=>'success','students'=>$students]);
$conn->close();
