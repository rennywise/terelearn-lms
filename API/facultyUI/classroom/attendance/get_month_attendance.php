<?php
/**
 * API/facultyUI/classroom/attendance/get_month_attendance.php
 * GET ?class_id=UUID&year=2026&month=4
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}
require_once __DIR__ . '/../../../../core/db_connect.php';
require_once __DIR__ . '/_helpers.php';

$class_id = trim($_GET['class_id'] ?? '');
$year     = (int)($_GET['year']  ?? 0);
$month    = (int)($_GET['month'] ?? 0);
if (!$class_id || $year < 1900 || $month < 1 || $month > 12) {
    echo json_encode(['status'=>'error','message'=>'class_id, year, month required']); exit;
}

$faculty_id = att_resolve_faculty_id($conn, $_SESSION['user_id']);
if (!$faculty_id) { echo json_encode(['status'=>'error','message'=>'Faculty record not found']); exit; }

$class = att_verify_class_owner($conn, $class_id, $faculty_id);
if (!$class) { echo json_encode(['status'=>'error','message'=>'Class not found or access denied']); exit; }

$c       = $conn->real_escape_string($class_id);
$ymStart = sprintf('%04d-%02d-01', $year, $month);
$ymEnd   = date('Y-m-t', strtotime($ymStart));

$sql = "
    SELECT a.attendance_date,
           COUNT(r.id) AS total,
           SUM(CASE WHEN r.status='present' THEN 1 ELSE 0 END) AS present_count,
           SUM(CASE WHEN r.status='absent'  THEN 1 ELSE 0 END) AS absent_count
    FROM   tblattendance a
    LEFT JOIN tblattendancerecord r ON r.attendance_id = a.id
    WHERE  a.class_id        = '$c'
      AND  a.is_deleted      = 0
      AND  a.attendance_date BETWEEN '$ymStart' AND '$ymEnd'
    GROUP  BY a.attendance_date
    ORDER  BY a.attendance_date ASC
";
$res  = $conn->query($sql);
$days = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $total   = (int)$row['total'];
        $present = (int)$row['present_count'];
        $absent  = (int)$row['absent_count'];
        $pct     = $total > 0 ? round(($present / $total) * 100) : 0;
        $tier    = $pct >= 80 ? 'high' : ($pct >= 50 ? 'mid' : 'low');
        $days[$row['attendance_date']] = [
            'present'    => $present,
            'absent'     => $absent,
            'total'      => $total,
            'percentage' => $pct,
            'tier'       => $tier,
        ];
    }
}
$conn->close();

echo json_encode(['status'=>'success', 'days'=>$days]);
