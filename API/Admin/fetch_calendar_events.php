<?php
/* API/Admin/fetch_calendar_events.php
   Returns all post due dates for a given year+month.
   Joins tblpost (or equivalent) with tblsubject and tblfaculty.
   Adjust table/column names to match your actual schema.
*/
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

// Build date range for the requested month
$start = sprintf('%04d-%02d-01', $year, $month);
$end   = date('Y-m-t', strtotime($start)); // last day of month

/*
   Adjust the query below to match your actual table names:
   - tblpost (or tblactivity / tblquiz / tblexam / tblassignment)
   - Columns: title, post_type (quiz|activity|exam|assignment), due_date, subject_id
   - tblsubject: id, subject_name, faculty_id
   - tblfaculty: id, first_name, last_name
*/
$sql = "
    SELECT
        p.id,
        p.title,
        p.post_type,
        p.due_date,
        COALESCE(s.subject_name, '') AS subject_name,
        CONCAT(f.first_name, ' ', f.last_name) AS faculty_name
    FROM tblpost p
    LEFT JOIN tblsubject  s ON s.id = p.subject_id
    LEFT JOIN tblfaculty  f ON f.id = s.faculty_id
    WHERE p.due_date BETWEEN ? AND ?
      AND (p.is_deleted = 0 OR p.is_deleted IS NULL)
    ORDER BY p.due_date ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Graceful fallback if tblpost doesn't exist yet
    echo json_encode(['status' => 'success', 'data' => [], 'note' => 'tblpost not found or query failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ss', $start, $end);
$stmt->execute();
$result = $stmt->get_result();
$data   = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $data]);
