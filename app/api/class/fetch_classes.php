<?php
// ==================== API/fetch_classes.php ====================

header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.', 'data' => []];

try {
    // Check what type subject_id values are in tblclass
   $sql = "
    SELECT 
        c.id,
        c.class_code,
        c.section,
        c.schedule,
        c.class_days,
        c.break_time,
        c.class_semester,
        c.year_level,
        c.is_active,
        s.subject_code,
        CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, '')) AS faculty_name,
        cr.course_code
    FROM tblclass c
    LEFT JOIN tblsubject s ON c.subject_id = s.id
    LEFT JOIN tblfaculty f ON c.faculty_id = f.id
    LEFT JOIN tblcourse cr ON c.course_id = cr.id
    WHERE c.is_deleted = 0
    ORDER BY c.class_code, c.section
";

    $result = $conn->query($sql);

    if ($result === false) {
        $response['message'] = 'Query failed: ' . $conn->error;
        echo json_encode($response);
        $conn->close();
        exit;
    }

    $classes = [];
    while ($row = $result->fetch_assoc()) {
        // Handle null values gracefully
        $row['subject_code'] = $row['subject_code'] ?? 'N/A';
        $row['subject_name'] = $row['subject_name'] ?? 'N/A';
        $row['faculty_name'] = trim($row['faculty_name']) === 'N/A' ? 'N/A' : $row['faculty_name'];
        $row['course_code'] = $row['course_code'] ?? 'N/A';
        $row['course_name'] = $row['course_name'] ?? 'N/A';
        
        $classes[] = $row;
    }

    $response['status'] = 'success';
    $response['message'] = 'Classes fetched successfully.';
    $response['data'] = $classes;

} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
