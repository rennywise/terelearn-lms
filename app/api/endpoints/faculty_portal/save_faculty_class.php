<?php
/**
 * API/facultyUI/save_faculty_class.php
 *
 * Creates or updates a faculty-owned class.
 * On INSERT: stamps semester_setting_id with the current active semester id
 *            AND assigns banner_palette deterministically from subject_id
 *            (same subject = same color across all classes with that subject).
 * On UPDATE: does NOT change semester_setting_id or banner_palette
 *            (palette is only changed via the Edit Class modal in classRoom.php).
 */
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/subject_palette_helper.php';

function generateJoinCode(mysqli $conn): string {
    for ($attempt = 0; $attempt < 20; $attempt++) {
        $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 7));
        $chk = $conn->prepare("SELECT id FROM tblclass WHERE join_code = ? LIMIT 1");
        $chk->bind_param('s', $code);
        $chk->execute();
        $exists = $chk->get_result()->fetch_assoc();
        $chk->close();
        if (!$exists) return $code;
    }
    return strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 7));
}

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

/* ── 4-strategy faculty lookup ── */
$uStmt = $conn->prepare("SELECT username, email FROM tbluser WHERE id = ? AND is_deleted = 0 LIMIT 1");
$uStmt->bind_param('s', $user_id); $uStmt->execute();
$tblUser = $uStmt->get_result()->fetch_assoc(); $uStmt->close();

$username = $tblUser['username'] ?? '';
$email    = $tblUser['email']    ?? '';
$faculty  = null;

if (!$faculty && $username) {
    $s = $conn->prepare("SELECT id FROM tblfaculty WHERE username = ? AND is_deleted = 0 LIMIT 1");
    $s->bind_param('s', $username); $s->execute();
    $faculty = $s->get_result()->fetch_assoc(); $s->close();
}
if (!$faculty && $email) {
    $s = $conn->prepare("SELECT id FROM tblfaculty WHERE email = ? AND is_deleted = 0 LIMIT 1");
    $s->bind_param('s', $email); $s->execute();
    $faculty = $s->get_result()->fetch_assoc(); $s->close();
}
if (!$faculty) {
    $col = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblfaculty'
        AND COLUMN_NAME = 'user_id' LIMIT 1");
    if ($col && $col->num_rows > 0) {
        $s = $conn->prepare("SELECT id FROM tblfaculty WHERE user_id = ? AND is_deleted = 0 LIMIT 1");
        $s->bind_param('s', $user_id); $s->execute();
        $faculty = $s->get_result()->fetch_assoc(); $s->close();
    }
}
if (!$faculty && isset($_SESSION['faculty_id'])) {
    $s = $conn->prepare("SELECT id FROM tblfaculty WHERE id = ? AND is_deleted = 0 LIMIT 1");
    $s->bind_param('s', $_SESSION['faculty_id']); $s->execute();
    $faculty = $s->get_result()->fetch_assoc(); $s->close();
}

if (!$faculty) {
    echo json_encode(['status' => 'error', 'message' => 'Faculty profile not found.']);
    exit;
}
$faculty_id = $faculty['id'];

/* ── Parse body ── */
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$class_id       = trim($data['class_id']       ?? '');
$class_name     = trim($data['class_name']     ?? '');
$subject_id     = trim($data['subject_id']     ?? '') ?: null;
$course_id      = trim($data['course_id']      ?? '') ?: null;
$section        = trim($data['section']        ?? '');
$class_semester = trim($data['class_semester'] ?? '');
$year_level     = trim($data['year_level']     ?? '');
$schedule       = trim($data['schedule']       ?? '');
$break_time     = trim($data['break_time']     ?? '');
$class_days     = trim($data['class_days']     ?? '');

/* ── Validate required fields ── */
if (!$class_name || !$section || !$class_semester || !$year_level) {
    echo json_encode(['status' => 'error', 'message' => 'Class Name, Section, Semester, and Year Level are required.']);
    exit;
}

if (!$course_id) {
    echo json_encode(['status' => 'error', 'message' => 'Please select a Course — it is required to create a class.']);
    exit;
}

if ($class_id) {
    /* ── UPDATE ── verify ownership, do NOT change palette here */
    $chk = $conn->prepare("SELECT id, source FROM tblclass WHERE id = ? AND faculty_id = ? AND is_deleted = 0 LIMIT 1");
    $chk->bind_param('ss', $class_id, $faculty_id); $chk->execute();
    $chkRow = $chk->get_result()->fetch_assoc(); $chk->close();

    if (!$chkRow) {
        echo json_encode(['status' => 'error', 'message' => 'Class not found or access denied.']);
        exit;
    }
    if (strtolower($chkRow['source'] ?? 'admin') !== 'faculty') {
        echo json_encode(['status' => 'error', 'message' => 'Admin-assigned classes cannot be edited.']);
        exit;
    }

    /* Do NOT update semester_setting_id or banner_palette — those are managed elsewhere */
    $upd = $conn->prepare("
        UPDATE tblclass SET
            subject_id     = ?,
            course_id      = ?,
            section        = ?,
            class_semester = ?,
            year_level     = ?,
            schedule       = ?,
            break_time     = ?,
            class_days     = ?,
            updated_at     = NOW()
        WHERE id = ? AND faculty_id = ?
    ");
    $upd->bind_param('ssssssssss',
        $subject_id, $course_id, $section, $class_semester,
        $year_level, $schedule, $break_time, $class_days,
        $class_id, $faculty_id
    );

    if (!$upd->execute()) {
        $err = $upd->error;
        $upd->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update class: ' . $err]);
        exit;
    }
    $upd->close();

    echo json_encode(['status' => 'success', 'message' => 'Class updated successfully.']);

} else {
    /* ── INSERT ── stamp with current active semester_setting_id and auto-assign palette */

    /* Get active semester id */
    $semRes = $conn->query("SELECT id FROM tblsemestersetting WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $semRow = $semRes ? $semRes->fetch_assoc() : null;
    $semester_setting_id = $semRow ? (int)$semRow['id'] : null;

    if (!$semester_setting_id) {
        echo json_encode(['status' => 'error', 'message' => 'No active semester is configured. Please ask your admin to set an active semester before creating a class.']);
        exit;
    }

    /*
     * ── PALETTE ASSIGNMENT LOGIC ──
     *
     * Rule: Same subject_id → same palette gradient.
     *       Different subject_id → deterministically different color.
     *
     * 1. Check if any existing class with the same subject_id already has
     *    a banner_palette saved in the DB. If yes, reuse it (handles the
     *    case where someone edited a class's palette manually via Edit Class
     *    and we want new classes of the same subject to match).
     *
     * 2. If no existing class has a palette for this subject, compute it
     *    deterministically from the subject_id UUID using paletteForSubject().
     */
    $banner_palette = null;

    if ($subject_id) {
        // Step 1: Look for an existing palette for this subject
        $pStmt = $conn->prepare("
            SELECT banner_palette
            FROM   tblclass
            WHERE  subject_id = ?
              AND  banner_palette IS NOT NULL
              AND  banner_palette != ''
              AND  is_deleted = 0
            ORDER  BY created_at ASC
            LIMIT  1
        ");
        $pStmt->bind_param('s', $subject_id);
        $pStmt->execute();
        $pRow = $pStmt->get_result()->fetch_assoc();
        $pStmt->close();

        if ($pRow && !empty($pRow['banner_palette'])) {
            // Reuse the palette from the oldest class with this subject
            $banner_palette = $pRow['banner_palette'];
        } else {
            // Step 2: Compute deterministically from subject_id
            $banner_palette = paletteForSubject($subject_id);
        }
    } else {
        // No subject selected — compute from class_name as fallback
        $banner_palette = paletteForSubject('', $class_name);
    }

    /* Build class_code from name */
    $prefix     = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $class_name), 0, 4));
    if (!$prefix) $prefix = 'CLS';
    $rand       = strtoupper(substr(md5(uniqid('', true)), 0, 5));
    $class_code = $prefix . '-' . $rand;
    $join_code = generateJoinCode($conn);
    $join_link_token = bin2hex(random_bytes(24));

    /* UUID v4 */
    $new_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $ins = $conn->prepare("
        INSERT INTO tblclass
            (id, class_code, subject_id, course_id, section, class_semester,
             semester_setting_id, year_level, schedule, break_time, class_days,
             faculty_id, source, banner_palette, join_code, join_link_token, is_active, is_deleted)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'faculty', ?, ?, ?, 1, 0)
    ");
    $ins->bind_param('ssssssissssssss',
        $new_id, $class_code, $subject_id, $course_id, $section,
        $class_semester, $semester_setting_id, $year_level,
        $schedule, $break_time, $class_days, $faculty_id,
        $banner_palette, $join_code, $join_link_token
    );

    if (!$ins->execute()) {
        $err = $ins->error;
        $ins->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to create class: ' . $err]);
        exit;
    }
    $ins->close();

    echo json_encode([
        'status'          => 'success',
        'message'         => 'Class created successfully.',
        'class_id'        => $new_id,
        'banner_palette'  => $banner_palette,
        'join_code'       => $join_code,
        'join_token'      => $join_link_token,
    ]);
}

$conn->close();
