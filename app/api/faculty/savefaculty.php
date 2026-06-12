<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

/* ---------- incoming ---------- */
$faculty_number = trim($_POST['faculty_number'] ?? '');
$first_name     = trim($_POST['first_name'] ?? '');
$middle_name    = trim($_POST['middle_name'] ?? '');
$last_name      = trim($_POST['last_name'] ?? '');
$suffix         = trim($_POST['suffix'] ?? '');
$user_type_id   = trim($_POST['user_type_id'] ?? '2');
$email          = trim($_POST['email'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$birthdate      = trim($_POST['birthdate'] ?? '');
$username       = trim($_POST['username'] ?? '');
$password_raw   = $_POST['password'] ?? '';
$is_dean        = !empty($_POST['is_dean']) ? 1 : 0;

/* ---------- basic validation ---------- */
if (empty($faculty_number) || empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password_raw)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled!']); exit;
}

$password = password_hash($password_raw, PASSWORD_DEFAULT);

/* ---------- birthdate YYYY-MM-DD (from HTML date input) ---------- */
$birthdate_db = null;
if (!empty($birthdate)) {
    // HTML <input type="date"> always sends YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
        $birthdate_db = $birthdate; // already correct format
    } else {
        // fallback: handle legacy MM/DD/YYYY
        $parts = explode('/', $birthdate);
        if (count($parts) === 3) {
            $birthdate_db = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
        }
    }
}

/* ---------- UUID ---------- */
$faculty_uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

/* ---------- transaction ---------- */
$conn->begin_transaction();
try {
    /* duplicate checks */
    $chk = $conn->prepare("SELECT id FROM tblfaculty WHERE is_deleted=0 AND (faculty_number=? OR email=? OR username=?)");
    $chk->bind_param('sss', $faculty_number, $email, $username); $chk->execute();
    if ($chk->get_result()->num_rows) throw new Exception('Faculty number, email, or username already exists!');
    $chk->close();

    $chk = $conn->prepare("SELECT id FROM tbluser WHERE is_deleted=0 AND (email=? OR username=?)");
    $chk->bind_param('ss', $email, $username); $chk->execute();
    if ($chk->get_result()->num_rows) throw new Exception('Email or username already exists in user accounts!');
    $chk->close();

    /* insert faculty */
    $sql = "INSERT INTO tblfaculty
            (id, faculty_number, first_name, middle_name, last_name, suffix, user_type_id,
             email, phone, birthdate, username, password, is_active, is_dean, is_deleted)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssssssi',
        $faculty_uuid, $faculty_number, $first_name, $middle_name, $last_name,
        $suffix, $user_type_id, $email, $phone, $birthdate_db, $username, $password, $is_dean);
    $stmt->execute(); $stmt->close();

    /* insert user (user_level_id = 2 → faculty)  INCLUDING is_dean */
    $sql = "INSERT INTO tbluser
            (id, email, username, password, user_level_id, is_dean, is_active, is_deleted, first_login)
            VALUES (?, ?, ?, ?, 2, ?, 1, 0, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssi', $faculty_uuid, $email, $username, $password, $is_dean);
    $stmt->execute(); $stmt->close();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Faculty registered successfully!']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
$conn->close();
?>
