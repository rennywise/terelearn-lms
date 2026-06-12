<?php
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$id             = (int)($_POST['id']             ?? 0);
$student_number = trim($_POST['student_number'] ?? '');
$first_name     = trim($_POST['first_name']     ?? '');
$middle_name    = trim($_POST['middle_name']    ?? '');
$last_name      = trim($_POST['last_name']      ?? '');
$suffix         = trim($_POST['suffix']         ?? '');
$email          = trim($_POST['email']          ?? '');
$username       = trim($_POST['username']       ?? '');
$password       = trim($_POST['password']       ?? '');
$course_id      = trim($_POST['course_id']      ?? '');
$birthdate      = trim($_POST['birthdate']      ?? '');
$birthdateVal   = $birthdate ?: null;

if (!$id || !$student_number || !$first_name || !$last_name || !$email || !$username || !$course_id) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']); exit;
}

$chk = $conn->prepare("SELECT id FROM tblstudent WHERE (student_number=? OR email=? OR username=?) AND id!=? LIMIT 1");
$chk->bind_param('sssi', $student_number, $email, $username, $id);
$chk->execute();
if ($chk->get_result()->fetch_assoc()) {
    echo json_encode(['status' => 'error', 'message' => 'Student number, email, or username already taken.']); exit;
}
$chk->close();

// Also check tbluser for duplicate email/username (exclude own user_id)
$self = $conn->query("SELECT user_id FROM tblstudent WHERE id=$id")->fetch_assoc();
$selfUid = $self['user_id'] ?? '';

if ($selfUid) {
    $chk3 = $conn->prepare("SELECT id FROM tbluser WHERE (email=? OR username=?) AND id!=? AND is_deleted=0 LIMIT 1");
    $chk3->bind_param('sss', $email, $username, $selfUid);
    $chk3->execute();
    if ($chk3->get_result()->fetch_assoc()) {
        echo json_encode(['status' => 'error', 'message' => 'Email or username already taken in user accounts.']); exit;
    }
    $chk3->close();
}

$conn->begin_transaction();
try {
    if ($password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE tblstudent
            SET student_number=?, first_name=?, middle_name=?, last_name=?, suffix=?,
                email=?, username=?, password=?, course_id=?, birthdate=?
            WHERE id=?
        ");
        $stmt->bind_param('ssssssssssi',
            $student_number, $first_name, $middle_name, $last_name, $suffix,
            $email, $username, $hashed, $course_id, $birthdateVal, $id
        );
    } else {
        $stmt = $conn->prepare("
            UPDATE tblstudent
            SET student_number=?, first_name=?, middle_name=?, last_name=?, suffix=?,
                email=?, username=?, course_id=?, birthdate=?
            WHERE id=?
        ");
        $stmt->bind_param('sssssssssi',
            $student_number, $first_name, $middle_name, $last_name, $suffix,
            $email, $username, $course_id, $birthdateVal, $id
        );
    }
    $stmt->execute();
    $stmt->close();

    // Sync tbluser email/username/password
    if ($selfUid) {
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $u = $conn->prepare("UPDATE tbluser SET email=?, username=?, password=? WHERE id=?");
            $u->bind_param('ssss', $email, $username, $hashed, $selfUid);
        } else {
            $u = $conn->prepare("UPDATE tbluser SET email=?, username=? WHERE id=?");
            $u->bind_param('sss', $email, $username, $selfUid);
        }
        $u->execute();
        $u->close();
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Student updated successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
$conn->close();
