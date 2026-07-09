<?php
require_once '_student_guard.php';

function preview_fail($message, $code = 400) {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

function resolve_attachment_file($filePath) {
    $root = realpath(dirname(__DIR__, 5));
    if (!$root) {
        return null;
    }

    $rawPath = trim((string)$filePath);
    if ($rawPath === '') {
        return null;
    }

    $path = parse_url($rawPath, PHP_URL_PATH);
    $path = $path !== null && $path !== false ? $path : $rawPath;
    $path = rawurldecode(str_replace('\\', '/', $path));

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $appBase = preg_replace('#/API/.*$#', '', $scriptName);
    $appBase = rtrim((string)$appBase, '/');

    $cleanPath = ltrim($path, '/');
    if ($appBase !== '' && strncmp('/' . $cleanPath, $appBase . '/', strlen($appBase) + 1) === 0) {
        $cleanPath = ltrim(substr('/' . $cleanPath, strlen($appBase)), '/');
    }

    $candidates = [
        $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath),
    ];

    foreach (['uploads_files', 'uploads', 'storage'] as $assetDir) {
        $marker = '/' . $assetDir . '/';
        $pos = strpos('/' . $cleanPath, $marker);
        if ($pos !== false) {
            $relativeAssetPath = substr('/' . $cleanPath, $pos + 1);
            $candidates[] = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeAssetPath);
        }
    }

    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($documentRoot) {
        $candidates[] = $documentRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));
    }

    foreach ($candidates as $candidate) {
        $resolved = realpath($candidate);
        if ($resolved && is_file($resolved) && strncmp($resolved, $root, strlen($root)) === 0) {
            return $resolved;
        }
    }

    return null;
}

$userId = require_student();
$student = get_student_record($conn, $userId);
if (!$student) {
    preview_fail('Student profile not found.', 404);
}

$attachmentId = trim($_GET['attach_id'] ?? '');
if ($attachmentId === '') {
    preview_fail('Attachment ID is required.', 400);
}

$studentId = (string)($student['id'] ?? '');
$studentUserId = (string)($student['user_id'] ?? $userId);

$stmt = $conn->prepare("
    SELECT a.id, a.post_id, a.file_name, a.file_path, a.mime_type, p.submission_mode
    FROM tblpostattachment a
    INNER JOIN tblpost p ON p.id = a.post_id AND p.is_deleted = 0
    INNER JOIN tblclassenrollment e
        ON e.class_id = p.class_id
       AND e.enrollment_status = 'enrolled'
       AND (e.student_id = ? OR e.student_id = ?)
    WHERE a.id = ?
    LIMIT 1
");
if (!$stmt) {
    preview_fail('Unable to check attachment access.', 500);
}
$stmt->bind_param('sss', $studentId, $studentUserId, $attachmentId);
$stmt->execute();
$attachment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$attachment) {
    preview_fail('Attachment not found.', 404);
}

if (($attachment['submission_mode'] ?? 'individual') === 'group') {
    $stmt = $conn->prepare("
        SELECT id
        FROM tblpostgroups
        WHERE post_id = ? AND (student_id = ? OR student_id = ?)
        LIMIT 1
    ");
    if (!$stmt) {
        preview_fail('Unable to check group access.', 500);
    }
    $stmt->bind_param('sss', $attachment['post_id'], $studentId, $studentUserId);
    $stmt->execute();
    $hasGroupAccess = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if (!$hasGroupAccess) {
        preview_fail('This material is not assigned to you.', 403);
    }
}

$extension = strtolower(pathinfo((string)$attachment['file_name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['ppt', 'pptx'], true)) {
    preview_fail('Preview conversion is only available for PowerPoint files.', 415);
}

$sourcePath = resolve_attachment_file($attachment['file_path'] ?? '');
if (!$sourcePath) {
    preview_fail('Source file not found.', 404);
}

$previewDir = dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'previews';
if (!is_dir($previewDir) && !mkdir($previewDir, 0775, true) && !is_dir($previewDir)) {
    preview_fail('Unable to create preview directory.', 500);
}

$cacheKey = hash('sha256', $attachmentId . '|' . filemtime($sourcePath) . '|' . filesize($sourcePath));
$cacheDir = $previewDir . DIRECTORY_SEPARATOR . $cacheKey;
if (!is_dir($cacheDir) && !mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
    preview_fail('Unable to create preview cache.', 500);
}

$pdfPath = $cacheDir . DIRECTORY_SEPARATOR . pathinfo($sourcePath, PATHINFO_FILENAME) . '.pdf';
if (!is_file($pdfPath)) {
    set_time_limit(60);

    $soffice = 'C:\Program Files\LibreOffice\program\soffice.exe';
    if (!is_file($soffice)) {
        preview_fail('PowerPoint preview service is unavailable.', 503);
    }

    $profileDir = $cacheDir . DIRECTORY_SEPARATOR . 'lo_profile';
    if (!is_dir($profileDir) && !mkdir($profileDir, 0775, true) && !is_dir($profileDir)) {
        preview_fail('Unable to initialize preview service.', 500);
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
        preview_fail('Unable to start PowerPoint preview service.', 500);
    }

    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $startedAt = microtime(true);
    while (true) {
        $status = proc_get_status($process);
        if (!$status['running']) {
            break;
        }
        if (microtime(true) - $startedAt > 45) {
            exec('taskkill /PID ' . (int)$status['pid'] . ' /T /F 2>NUL');
            proc_terminate($process);
            break;
        }
        usleep(200000);
    }

    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    if (!is_file($pdfPath) || filesize($pdfPath) === 0) {
        preview_fail('Unable to generate PowerPoint preview.', 500);
    }
}

if (ob_get_length()) {
    ob_clean();
}

$downloadName = pathinfo((string)$attachment['file_name'], PATHINFO_FILENAME) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . str_replace('"', "'", $downloadName) . '"');
header('Content-Length: ' . filesize($pdfPath));
header('Cache-Control: private, max-age=86400');
readfile($pdfPath);
