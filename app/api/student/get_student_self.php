<?php
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$uid = mysqli_real_escape_string($conn, $_SESSION['user_id']);

// Get email/username + profile_picture from tbluser
$uRes = $conn->query("SELECT email, username, profile_picture FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
$uRow = $uRes ? $uRes->fetch_assoc() : null;
if (!$uRow) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    $conn->close(); exit;
}

$un = mysqli_real_escape_string($conn, $uRow['username']);
$em = mysqli_real_escape_string($conn, $uRow['email']);

$sRes = $conn->query("
    SELECT s.id, s.student_number, s.first_name, s.middle_name, s.last_name,
           s.email, s.username, s.birthdate, s.course_id, s.year_level, s.section,
           s.is_active, s.user_id,
           c.course_code, c.course_name
    FROM   tblstudent s
    LEFT JOIN tblcourse c ON c.id = s.course_id
    WHERE  (s.username='$un' OR s.email='$em') AND s.is_deleted=0 LIMIT 1
");
$row = $sRes ? $sRes->fetch_assoc() : null;
$conn->close();

if (!$row) {
    echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    exit;
}

$row['profile_picture'] = $uRow['profile_picture'];
echo json_encode(['status' => 'success', 'student' => $row]);
