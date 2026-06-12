<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$search = trim($_GET['search'] ?? '');
$where  = "c.is_deleted = 0";

if ($search) {
    $s = $conn->real_escape_string($search);
    $where .= " AND (
          c.class_code        LIKE '%$s%' OR
          s.subject_code      LIKE '%$s%' OR
          s.subject_name      LIKE '%$s%' OR
          f.faculty_number    LIKE '%$s%' OR
          CONCAT(f.first_name,' ',f.last_name) LIKE '%$s%' OR
          cr.course_code      LIKE '%$s%' OR
          cr.course_name      LIKE '%$s%' OR
          c.section           LIKE '%$s%' OR
          c.class_semester    LIKE '%$s%' OR
          c.year_level        LIKE '%$s%' OR
          c.class_days        LIKE '%$s%' OR
          c.schedule          LIKE '%$s%' OR
          c.break_time        LIKE '%$s%'
    )";
}

$sql = "SELECT  c.id,
                c.class_code,
                c.section,
                c.schedule,
                c.class_semester,
                c.year_level,
                c.break_time,
                c.class_days,
                c.is_active,
                s.subject_code,
                s.subject_name,
                cr.course_code,
                cr.course_name,
                CONCAT(COALESCE(f.first_name, 'N/A'), ' ', COALESCE(f.last_name, '')) AS faculty_name
        FROM tblclass c
        LEFT JOIN tblsubject s  ON c.subject_id = s.id
        LEFT JOIN tblcourse cr ON c.course_id  = cr.id
        LEFT JOIN tblfaculty f ON c.faculty_id = f.id
        WHERE $where
        ORDER BY c.class_code, c.section";

$result = $conn->query($sql);

if ($result === false) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error, 'data' => []]);
    $conn->close();
    exit;
}

$list = [];
while ($r = $result->fetch_assoc()) {
    $r['faculty_name'] = trim($r['faculty_name']) ?: 'N/A';
    $r['subject_code'] = $r['subject_code'] ?? 'N/A';
    $r['subject_name'] = $r['subject_name'] ?? 'N/A';
    $r['course_code'] = $r['course_code'] ?? 'N/A';
    $r['class_days'] = $r['class_days'] ?? '';
    $r['schedule'] = $r['schedule'] ?? 'N/A';
    $r['break_time'] = $r['break_time'] ?? 'N/A';
    $list[] = $r;
}

echo json_encode(['status' => 'success', 'data' => $list]);
$conn->close();
?>
