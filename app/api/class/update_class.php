<?php
/**
 * API/facultyUI/classroom/update_class.php
 * POST  JSON {
 *   "class_id", "subject_code", "subject_name", "year_level",
 *   "section", "class_semester", "course_code",
 *   "class_days", "schedule", "banner_palette"
 * }
 *
 * Only the faculty who owns the class may update it.
 */
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || $level !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$class_id      = trim($body['class_id']      ?? '');
$subject_code  = trim($body['subject_code']  ?? '');
$subject_name  = trim($body['subject_name']  ?? '');
$year_level    = trim($body['year_level']    ?? '');
$section       = trim($body['section']       ?? '');
$class_semester= trim($body['class_semester']?? '');
$course_code   = trim($body['course_code']   ?? '');
$class_days    = trim($body['class_days']    ?? '');
$schedule      = trim($body['schedule']      ?? '');
$banner_palette= trim($body['banner_palette']?? '');

if (!$class_id) {
    echo json_encode(['status' => 'error', 'message' => 'class_id is required']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

try {
    // Verify ownership
    $chk = $pdo->prepare("SELECT id FROM tblclass WHERE id = :cid AND faculty_id = :fid LIMIT 1");
    $chk->execute([':cid' => $class_id, ':fid' => $user_id]);
    if (!$chk->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Class not found or access denied']);
        exit;
    }

    // Build dynamic SET clause — only update columns that exist in your table.
    // Remove any column your tblclass doesn't have.
    $upd = $pdo->prepare("
        UPDATE tblclass SET
            subject_code   = :subject_code,
            subject_name   = :subject_name,
            year_level     = :year_level,
            section        = :section,
            class_semester = :class_semester,
            course_code    = :course_code,
            class_days     = :class_days,
            schedule       = :schedule,
            banner_palette = :banner_palette
        WHERE id = :class_id
    ");

    $upd->execute([
        ':subject_code'   => $subject_code,
        ':subject_name'   => $subject_name,
        ':year_level'     => $year_level,
        ':section'        => $section,
        ':class_semester' => $class_semester,
        ':course_code'    => $course_code,
        ':class_days'     => $class_days,
        ':schedule'       => $schedule,
        ':banner_palette' => $banner_palette,
        ':class_id'       => $class_id,
    ]);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Class updated successfully.',
        'updated' => [
            'subject_code'   => $subject_code,
            'subject_name'   => $subject_name,
            'year_level'     => $year_level,
            'section'        => $section,
            'class_semester' => $class_semester,
            'course_code'    => $course_code,
            'class_days'     => $class_days,
            'schedule'       => $schedule,
            'banner_palette' => $banner_palette,
        ],
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
}
