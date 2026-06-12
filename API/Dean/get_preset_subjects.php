<?php
/**
 * API/Dean/get_preset_subjects.php
 * Returns subjects for a program, flagged as preset or not,
 * for a given school_year + semester.
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../core/db_connect.php';

$course_id   = $_GET['course_id']   ?? '';
$school_year = $_GET['school_year'] ?? '';
$semester    = $_GET['semester']    ?? '';

if (!$course_id || !$school_year || !$semester) {
    echo json_encode(['status'=>'error','message'=>'Missing parameters']);
    exit;
}

$cid = mysqli_real_escape_string($conn, $course_id);
$sy  = mysqli_real_escape_string($conn, $school_year);
$sem = mysqli_real_escape_string($conn, $semester);

$sql = "
    SELECT 
        s.id,
        s.subject_code,
        s.subject_name,
        s.is_active,
        CASE WHEN sp.id IS NOT NULL THEN 1 ELSE 0 END AS is_preset,
        sp.id AS preset_id
    FROM tblsubject s
    LEFT JOIN tblsubjectpreset sp 
        ON sp.subject_id = s.id 
        AND sp.school_year = '$sy' 
        AND sp.semester = '$sem'
    WHERE s.course_id = '$cid'
      AND s.is_deleted = 0
    ORDER BY s.subject_code ASC
";

$res  = $conn->query($sql);
$data = [];
while ($r = $res->fetch_assoc()) $data[] = $r;

$conn->close();
echo json_encode(['status'=>'success','data'=>$data]);
