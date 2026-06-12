<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';
/* ---------- incoming ---------- */
$faculty_id     = trim($_POST['faculty_id'] ?? '');
$faculty_number = trim($_POST['faculty_number'] ?? '');
$first_name     = trim($_POST['first_name'] ?? '');
$middle_name    = trim($_POST['middle_name'] ?? '');
$last_name      = trim($_POST['last_name'] ?? '');
$suffix         = trim($_POST['suffix'] ?? '');
$email          = trim($_POST['email'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$birthdate      = trim($_POST['birthdate'] ?? '');
$username       = trim($_POST['username'] ?? '');
$password_raw   = $_POST['password'] ?? '';
$is_dean        = !empty($_POST['is_dean']) ? 1 : 0;

/* ---------- basic validation ---------- */
if (!$faculty_number || !$first_name || !$last_name || !$email || !$username) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields!']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format!']); exit;
}

/* ---------- birthdate ---------- */
$birthdate_db = !empty($birthdate) ? date('Y-m-d', strtotime($birthdate)) : null;

/* ---------- duplicate check (ignore self) ---------- */
$chk = $conn->prepare("SELECT id FROM tblfaculty WHERE (email=? OR username=?) AND faculty_number!=?");
$chk->bind_param('sss', $email, $username, $faculty_number); $chk->execute();
if ($chk->get_result()->num_rows) { echo json_encode(['status'=>'error','message'=>'Email or username already exists!']); exit; }
$chk->close();

/* ---------- two paths : with vs without password ---------- */
if (empty($password_raw)) {
    $sql = "UPDATE tblfaculty
            SET first_name=?, middle_name=?, last_name=?, suffix=?, email=?,
                phone=?, birthdate=?, username=?, is_dean=?
            WHERE faculty_number=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssis', $first_name, $middle_name, $last_name, $suffix, $email,
                      $phone, $birthdate_db, $username, $is_dean, $faculty_number);
} else {
    $password = password_hash($password_raw, PASSWORD_DEFAULT);
    $sql = "UPDATE tblfaculty
            SET first_name=?, middle_name=?, last_name=?, suffix=?, email=?,
                phone=?, birthdate=?, username=?, password=?, is_dean=?
            WHERE faculty_number=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssssis', $first_name, $middle_name, $last_name, $suffix, $email,
                      $phone, $birthdate_db, $username, $password, $is_dean, $faculty_number);
}

/* ---------- run faculty update ---------- */
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]); exit;
}
$stmt->close();

/* ---------- keep tbluser in sync ---------- */
$sync = $conn->prepare("UPDATE tbluser SET is_dean=? WHERE id=?");
$sync->bind_param('is', $is_dean, $faculty_id);
$sync->execute(); $sync->close();

/* ---------- reply ---------- */
$msg = $conn->affected_rows > 0 ? 'Faculty updated successfully!' : 'No changes were made';
echo json_encode(['status' => 'success', 'message' => $msg]);
$conn->close();
?>
