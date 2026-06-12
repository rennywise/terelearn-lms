<?php
ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function send($arr) {
    ob_end_clean();
    echo json_encode($arr);
    exit;
}

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    send(['success' => false, 'message' => 'Unauthorized']);
}

require_once __DIR__ . '/../../../../core/db_connect.php';

$post_id = trim($_POST['post_id'] ?? '');
if ($post_id === '') {
    send(['success' => false, 'message' => 'post_id required']);
}

$pid     = $conn->real_escape_string($post_id);
$user_id = $conn->real_escape_string($_SESSION['user_id']);

// Resolve student_id from session user
$uRes = $conn->query("SELECT email, username FROM tbluser WHERE id = '$user_id' AND is_deleted = 0 LIMIT 1");
if (!$uRes || $uRes->num_rows === 0) send(['success' => false, 'message' => 'User not found']);
$uRow = $uRes->fetch_assoc();
$un   = $conn->real_escape_string($uRow['username']);
$em   = $conn->real_escape_string($uRow['email']);

$sRes = $conn->query("SELECT id FROM tblstudent WHERE (username = '$un' OR email = '$em') AND is_deleted = 0 LIMIT 1");
if (!$sRes || $sRes->num_rows === 0) send(['success' => false, 'message' => 'Student record not found']);
$student_id = $conn->real_escape_string($sRes->fetch_assoc()['id']);

// Make sure they are actually enrolled before removing
$chk = $conn->query("SELECT id FROM tblquizenrollment WHERE post_id = '$pid' AND student_id = '$student_id' LIMIT 1");
if (!$chk || $chk->num_rows === 0) {
    send(['success' => false, 'message' => 'Not enrolled in this quiz']);
}

// Remove enrollment
$del = $conn->query("DELETE FROM tblquizenrollment WHERE post_id = '$pid' AND student_id = '$student_id' LIMIT 1");
if (!$del) {
    send(['success' => false, 'message' => 'Failed to withdraw: ' . $conn->error]);
}

$conn->close();
send(['success' => true, 'message' => 'Withdrawn successfully.']);
