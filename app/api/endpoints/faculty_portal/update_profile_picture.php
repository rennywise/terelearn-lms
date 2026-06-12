<?php
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../core/db_connect.php';

header('Content-Type: application/json');

if (empty($_FILES['picture'])) {
    echo json_encode(['status'=>'error','message'=>'No file received']);
    exit;
}

$file = $_FILES['picture'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status'=>'error','message'=>'Upload error code: '.$file['error']]);
    exit;
}

// Validate MIME type
$allowed = ['image/jpeg','image/png','image/gif','image/webp'];
$finfo   = finfo_open(FILEINFO_MIME_TYPE);
$mime    = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime, $allowed)) {
    echo json_encode(['status'=>'error','message'=>'Invalid file type: '.$mime]);
    exit;
}

// Max 5 MB
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['status'=>'error','message'=>'File too large (max 5 MB)']);
    exit;
}

$uploadDir = dirname(__DIR__, 4) . '/uploads/profile_pictures/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$userId = $_SESSION['user_id'];
$uid    = mysqli_real_escape_string($conn, $userId);

// Delete old picture file if it exists
$oldRes = $conn->query("SELECT profile_picture FROM tbluser WHERE id='$uid' LIMIT 1");
if ($oldRes && $row = $oldRes->fetch_assoc()) {
    if (!empty($row['profile_picture'])) {
        $oldPath = dirname(__DIR__, 4) . '/' . ltrim($row['profile_picture'], '/');
        if (file_exists($oldPath)) @unlink($oldPath);
    }
}

// Save new file
$ext      = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'][$mime];
$filename = 'user_' . $userId . '_' . time() . '.' . $ext;
$destPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['status'=>'error','message'=>'Could not save file']);
    exit;
}

$relUrl = 'uploads/profile_pictures/' . $filename;
$esc    = mysqli_real_escape_string($conn, $relUrl);
$conn->query("UPDATE tbluser SET profile_picture='$esc' WHERE id='$uid'");
$conn->close();

echo json_encode(['status'=>'success','url'=>$relUrl]);
