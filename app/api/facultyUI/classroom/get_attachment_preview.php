<?php
session_start();

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    http_response_code(401);
    exit('Unauthorized');
}

$attachmentId = trim($_GET['attach_id'] ?? '');
if ($attachmentId === '') {
    http_response_code(400);
    exit('Attachment ID is required.');
}

require_once __DIR__ . '/../../../core/db_connect.php';

$userId = (string)$_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT a.file_name, a.file_path
    FROM tblpostattachment a
    INNER JOIN tblpost p ON p.id = a.post_id AND p.is_deleted = 0
    INNER JOIN tblclass c ON c.id = p.class_id AND c.is_deleted = 0
    INNER JOIN tblfaculty f ON f.id = c.faculty_id AND f.is_deleted = 0
    INNER JOIN tbluser u ON u.id = ? AND u.is_deleted = 0
    WHERE a.id = ?
      AND (f.username = u.username OR f.email = u.email)
    LIMIT 1
");
$stmt->bind_param('ss', $userId, $attachmentId);
$stmt->execute();
$attachment = $stmt->get_result()->fetch_assoc();

if (!$attachment) {
    http_response_code(404);
    exit('Attachment not found.');
}

$extension = strtolower(pathinfo((string)$attachment['file_name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['ppt', 'pptx'], true)) {
    http_response_code(415);
    exit('Preview conversion is only available for PowerPoint files.');
}

$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
$sourcePath = realpath($documentRoot . DIRECTORY_SEPARATOR . ltrim((string)$attachment['file_path'], '/\\'));
if (!$documentRoot || !$sourcePath || !is_file($sourcePath) || strpos($sourcePath, $documentRoot) !== 0) {
    http_response_code(404);
    exit('Source file not found.');
}

$previewDir = dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'previews';
if (!is_dir($previewDir) && !mkdir($previewDir, 0775, true) && !is_dir($previewDir)) {
    http_response_code(500);
    exit('Unable to create preview directory.');
}

$cacheKey = hash('sha256', $attachmentId . '|' . filemtime($sourcePath) . '|' . filesize($sourcePath));
$cacheDir = $previewDir . DIRECTORY_SEPARATOR . $cacheKey;
if (!is_dir($cacheDir) && !mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
    http_response_code(500);
    exit('Unable to create preview cache.');
}

$pdfPath = $cacheDir . DIRECTORY_SEPARATOR . pathinfo($sourcePath, PATHINFO_FILENAME) . '.pdf';
if (!is_file($pdfPath)) {
    $soffice = 'C:\Program Files\LibreOffice\program\soffice.exe';
    if (!is_file($soffice)) {
        http_response_code(503);
        exit('PowerPoint preview service is unavailable.');
    }

    $profileDir = $cacheDir . DIRECTORY_SEPARATOR . 'lo_profile';
    if (!is_dir($profileDir) && !mkdir($profileDir, 0775, true) && !is_dir($profileDir)) {
        http_response_code(500);
        exit('Unable to initialize preview service.');
    }
    $profileUrl = 'file:///' . str_replace('\\', '/', $profileDir);
    $command = escapeshellarg($soffice)
        . ' -env:UserInstallation=' . escapeshellarg($profileUrl)
        . ' --headless --convert-to pdf --outdir '
        . escapeshellarg($cacheDir) . ' '
        . escapeshellarg($sourcePath) . ' 2>&1';
    $descriptorSpec = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];
    $process = proc_open($command, $descriptorSpec, $pipes);
    if (!is_resource($process)) {
        http_response_code(500);
        exit('Unable to start PowerPoint preview service.');
    }

    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $startedAt = microtime(true);
    $exitCode = null;
    while (true) {
        $status = proc_get_status($process);
        if (!$status['running']) {
            $exitCode = $status['exitcode'];
            break;
        }
        if (microtime(true) - $startedAt > 45) {
            exec('taskkill /PID ' . (int)$status['pid'] . ' /T /F 2>NUL');
            proc_terminate($process);
            $exitCode = 124;
            break;
        }
        usleep(200000);
    }
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    if (!is_file($pdfPath) || filesize($pdfPath) === 0) {
        http_response_code(500);
        exit('Unable to generate PowerPoint preview.');
    }
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
header('Content-Length: ' . filesize($pdfPath));
header('Cache-Control: private, max-age=86400');
readfile($pdfPath);
