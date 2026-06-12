<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../core/db_connect.php';

$uid = mysqli_real_escape_string($conn, (string)$_SESSION['user_id']);
$uRes = $conn->query("SELECT email, username, profile_picture, created_at FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
$uRow = $uRes ? $uRes->fetch_assoc() : null;
if (!$uRow) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$un = mysqli_real_escape_string($conn, (string)$uRow['username']);
$em = mysqli_real_escape_string($conn, (string)$uRow['email']);
$aRes = $conn->query("SELECT * FROM tbladmin WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
$aRow = $aRes ? $aRes->fetch_assoc() : null;
if (!$aRow) {
    echo json_encode(['status' => 'error', 'message' => 'Dean account not found']);
    exit;
}

$aid = mysqli_real_escape_string($conn, (string)$aRow['id']);
$dRes = $conn->query("
    SELECT da.role, da.assigned_at, da.department_id, d.dept_code, d.dept_name
    FROM tbldeanassignment da
    JOIN tbldepartment d ON d.id = da.department_id
    WHERE da.faculty_id='$aid' AND da.is_active=1
    ORDER BY da.assigned_at DESC
    LIMIT 1
");
$dRow = $dRes ? $dRes->fetch_assoc() : null;

$linkedFaculty = null;
$fRes = $conn->query("
    SELECT id, faculty_number, first_name, middle_name, last_name, email, phone, username, created_at
    FROM tblfaculty
    WHERE is_deleted = 0
      AND (
          id = '$aid'
          OR (email IS NOT NULL AND email <> '' AND email = '" . mysqli_real_escape_string($conn, (string)($aRow['email'] ?? '')) . "')
          OR (username IS NOT NULL AND username <> '' AND username = '" . mysqli_real_escape_string($conn, (string)($aRow['username'] ?? '')) . "')
      )
    ORDER BY (id = '$aid') DESC
    LIMIT 1
");
if ($fRes && $fRes->num_rows > 0) $linkedFaculty = $fRes->fetch_assoc();

$firstName = $linkedFaculty['first_name'] ?? ($aRow['first_name'] ?? '');
$middleName = $linkedFaculty['middle_name'] ?? ($aRow['middle_name'] ?? '');
$lastName = $linkedFaculty['last_name'] ?? ($aRow['last_name'] ?? '');
$emailOut = $linkedFaculty['email'] ?? ($aRow['email'] ?? '');
$usernameOut = $linkedFaculty['username'] ?? ($aRow['username'] ?? '');
$phoneOut = $linkedFaculty['phone'] ?? ($aRow['phone'] ?? '');
$accountCreatedAt = $uRow['created_at'] ?? ($aRow['created_at'] ?? null);

$profile = [
    'id'               => $aRow['id'],
    'first_name'       => $firstName,
    'middle_name'      => $middleName,
    'last_name'        => $lastName,
    'name'             => trim($firstName . ' ' . $lastName),
    'email'            => $emailOut,
    'phone'            => $phoneOut,
    'username'         => $usernameOut,
    'role'             => ucfirst($dRow['role'] ?? 'Dean'),
    'department_code'  => $dRow['dept_code'] ?? '—',
    'department_name'  => $dRow['dept_name'] ?? '',
    'assigned_at'      => $dRow['assigned_at'] ?? null,
    'account_created_at' => $accountCreatedAt,
    'profile_picture'  => $uRow['profile_picture'] ?? null,
    'faculty_linked'   => $linkedFaculty ? 1 : 0,
    'faculty_number'   => $linkedFaculty['faculty_number'] ?? null,
    'faculty_created_at' => $linkedFaculty['created_at'] ?? null
];

echo json_encode(['status' => 'success', 'profile' => $profile]);
$conn->close();
