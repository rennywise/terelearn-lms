<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$colRes = $conn->query("SHOW COLUMNS FROM tbldepartment LIKE 'dept_image'");
if ($colRes && $colRes->num_rows === 0) {
    @$conn->query("ALTER TABLE tbldepartment ADD COLUMN dept_image varchar(255) DEFAULT NULL AFTER description");
}

$departmentId = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 0;
if ($departmentId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid department ID.']);
    exit;
}

if (empty($_FILES['department_image']) || !is_uploaded_file($_FILES['department_image']['tmp_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'No image file received.']);
    exit;
}

$file = $_FILES['department_image'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Image upload failed.']);
    exit;
}

if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'Image must be 5 MB or smaller.']);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']) ?: '';
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
];

if (!isset($allowed[$mime])) {
    echo json_encode(['status' => 'error', 'message' => 'Only JPG, PNG, WEBP, and GIF images are allowed.']);
    exit;
}

$uploadDir = dirname(__DIR__, 4) . '/public/assets/img/departments';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
    echo json_encode(['status' => 'error', 'message' => 'Could not create upload directory.']);
    exit;
}

$stmt = $conn->prepare("SELECT dept_image FROM tbldepartment WHERE id=? LIMIT 1");
$stmt->bind_param('i', $departmentId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
if (!$existing) {
    echo json_encode(['status' => 'error', 'message' => 'Department not found.']);
    exit;
}

$ext = $allowed[$mime];
$filename = sprintf('department-%d-%s.%s', $departmentId, bin2hex(random_bytes(6)), $ext);
$targetPath = $uploadDir . '/' . $filename;
$publicPath = 'public/assets/img/departments/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded image.']);
    exit;
}

$update = $conn->prepare("UPDATE tbldepartment SET dept_image=? WHERE id=?");
$update->bind_param('si', $publicPath, $departmentId);
$update->execute();

$oldPath = trim((string)($existing['dept_image'] ?? ''));
if ($oldPath !== '' && str_starts_with($oldPath, 'public/assets/img/departments/')) {
    $oldFile = dirname(__DIR__, 4) . '/' . $oldPath;
    if (is_file($oldFile) && realpath($oldFile) !== realpath($targetPath)) {
        @unlink($oldFile);
    }
}

echo json_encode([
    'status' => 'success',
    'message' => 'Department image uploaded successfully.',
    'image_path' => $publicPath,
]);

$conn->close();
