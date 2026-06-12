<?php
/**
 * API/facultyUI/classroom/attendance/get_semester_range.php
 * GET ?class_id=UUID
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}
require_once __DIR__ . '/../../../../core/db_connect.php';
require_once __DIR__ . '/_helpers.php';

$class_id = trim($_GET['class_id'] ?? '');
if (!$class_id) { echo json_encode(['status'=>'error','message'=>'class_id required']); exit; }

$faculty_id = att_resolve_faculty_id($conn, $_SESSION['user_id']);
if (!$faculty_id) { echo json_encode(['status'=>'error','message'=>'Faculty record not found']); exit; }

$class = att_verify_class_owner($conn, $class_id, $faculty_id);
if (!$class) { echo json_encode(['status'=>'error','message'=>'Class not found or access denied']); exit; }

$sem = att_class_semester($conn, $class['semester_setting_id'] ? (int)$class['semester_setting_id'] : null);
if (!$sem) { echo json_encode(['status'=>'error','message'=>'No semester configured']); exit; }

$conn->close();
echo json_encode([
    'status'     => 'success',
    'today'      => date('Y-m-d'),
    'semester'   => [
        'id'          => (int)$sem['id'],
        'label'       => $sem['semester'] . ' ' . $sem['school_year'],
        'semester'    => $sem['semester'],
        'school_year' => $sem['school_year'],
        'start_date'  => $sem['start_date'],
        'end_date'    => $sem['end_date'],
    ],
    'class_code' => $class['class_code'],
]);
