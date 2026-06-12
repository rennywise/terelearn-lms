<?php
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

$where = "y.is_deleted = 0";
if ($courseId) $where .= " AND y.course_id = " . $courseId;

$sql = "SELECT y.id, y.course_id, c.course_code, c.course_name, y.year_level, y.section_count
        FROM tblyearsectionconfig y
        JOIN tblcourse c ON c.id = y.course_id
        WHERE " . $where . "
        ORDER BY c.course_code, y.year_level";

$res = $conn->query($sql);

if (!$res) {
    echo json_encode(array('status' => 'error', 'message' => $conn->error));
    exit;
}

$data = array();
while ($r = $res->fetch_assoc()) {
    $r['id']            = (int)$r['id'];
    $r['course_id']     = (int)$r['course_id'];
    $r['year_level']    = (int)$r['year_level'];
    $r['section_count'] = (int)$r['section_count'];
    $data[] = $r;
}

echo json_encode(array('status' => 'success', 'data' => $data));
$conn->close();
