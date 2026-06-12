<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$courseId  = isset($_GET['course_id'])  ? (int)$_GET['course_id']  : 0;
$yearLevel = isset($_GET['year_level']) ? (int)$_GET['year_level'] : 0;

if (!$yearLevel) {
    echo json_encode(['status' => 'error', 'sections' => []]);
    exit;
}

$where = "year_level = $yearLevel AND is_deleted = 0";
if ($courseId) $where .= " AND course_id = $courseId";

$res = $conn->query("SELECT section_count FROM tblyearsectionconfig WHERE $where LIMIT 1");

if ($res && $res->num_rows) {
    $count    = (int)$res->fetch_assoc()['section_count'];
    $sections = [];
    for ($i = 1; $i <= $count; $i++) $sections[] = "$yearLevel-$i";
    echo json_encode(['status' => 'success', 'sections' => $sections, 'count' => $count]);
} else {
    echo json_encode(['status' => 'success', 'sections' => [], 'count' => 0]);
}
$conn->close();
