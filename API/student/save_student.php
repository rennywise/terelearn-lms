<?php
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$student_number = trim($_POST['student_number'] ?? '');
$first_name     = trim($_POST['first_name']     ?? '');
$middle_name    = trim($_POST['middle_name']    ?? '');
$last_name      = trim($_POST['last_name']      ?? '');
$suffix         = trim($_POST['suffix']         ?? '');
$email          = trim($_POST['email']          ?? '');
$username       = trim($_POST['username']       ?? '');
$password_raw   = trim($_POST['password']       ?? '');
$course_id      = (int)($_POST['course_id']     ?? 0);
$birthdate      = trim($_POST['birthdate']      ?? '');
$user_type_id   = 3;
$year_level     = 0;

if (!$student_number || !$first_name || !$last_name || !$email || !$username || !$password_raw || !$course_id || !$birthdate) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields. Please complete all required student details.']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']); exit;
}
if (strlen($username) < 4) {
    echo json_encode(['status' => 'error', 'message' => 'Username must be at least 4 characters.']); exit;
}

$student_uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
    mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
    mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
);

$hashed       = password_hash($password_raw, PASSWORD_DEFAULT);
$birthdateVal = $birthdate ?: null;

$conn->begin_transaction();
try {
    // Check duplicates in tblstudent
    $chk = $conn->prepare("SELECT id FROM tblstudent WHERE is_deleted=0 AND (student_number=? OR email=? OR username=?) LIMIT 1");
    $chk->bind_param('sss', $student_number, $email, $username);
    $chk->execute();
    if ($chk->get_result()->fetch_assoc())
        throw new Exception('Student number, email, or username already exists.');
    $chk->close();

    // Check duplicates in tbluser
    $chk2 = $conn->prepare("SELECT id FROM tbluser WHERE is_deleted=0 AND (email=? OR username=?) LIMIT 1");
    $chk2->bind_param('ss', $email, $username);
    $chk2->execute();
    if ($chk2->get_result()->fetch_assoc())
        throw new Exception('Email or username already exists in user accounts.');
    $chk2->close();

    // ── INSERT tbluser FIRST (parent table) ──
    $stmt2 = $conn->prepare("
        INSERT INTO tbluser
          (id, email, username, password, user_level_id,
           is_dean, is_active, is_deleted, first_login)
        VALUES (?,?,?,?,3, 0,1,0,1)
    ");
    $stmt2->bind_param('ssss', $student_uuid, $email, $username, $hashed);
    $stmt2->execute();
    $stmt2->close();

    // ── INSERT tblstudent SECOND (child table — references tbluser.id) ──
    $stmt = $conn->prepare("
        INSERT INTO tblstudent
          (student_number, first_name, middle_name, last_name, suffix,
           email, username, password, course_id, year_level,
           section, birthdate, user_type_id, is_active, is_deleted, user_id)
        VALUES (?,?,?,?,?, ?,?,?,?,?, NULL,?,?,1,0,?)
    ");
    $stmt->bind_param(
        'ssssssssiiiss',
        $student_number,
        $first_name,
        $middle_name,
        $last_name,
        $suffix,
        $email,
        $username,
        $hashed,
        $course_id,
        $year_level,
        $birthdateVal,
        $user_type_id,
        $student_uuid
    );
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Student account created successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
$conn->close();
