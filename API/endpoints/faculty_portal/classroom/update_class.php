<?php
/**
 * API/facultyUI/classroom/update_class.php
 *
 * Updates class metadata from the classRoom.php Edit Class modal.
 *
 * PALETTE RULES:
 *   - When the faculty changes the banner color in the Edit Class modal,
 *     ONLY this specific class row is updated. Other classes that share the
 *     same subject_id keep their own palette unchanged.
 *   - If the subject changes (subject_code resolves to a different subject_id)
 *     AND no explicit palette was sent, we auto-assign the palette for the
 *     new subject (reuse existing if any, otherwise deterministic).
 *
 * Returns updated fields so the frontend can merge without a full reload.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/db_connect.php';
require_once __DIR__ . '/../subject_palette_helper.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$classId      = trim($data['class_id']      ?? '');
$subjectCode  = trim($data['subject_code']  ?? '');
$subjectName  = trim($data['subject_name']  ?? '');
$yearLevel    = trim($data['year_level']    ?? '');
$section      = trim($data['section']       ?? '');
$semester     = trim($data['class_semester']?? '');
$courseCode   = trim($data['course_code']   ?? '');
$classDays    = trim($data['class_days']    ?? '');
$schedule     = trim($data['schedule']      ?? '');
// palette sent from JS — empty string means "no change / auto"
$paletteInput = trim($data['banner_palette']?? '');

if (!$classId) {
    echo json_encode(['status' => 'error', 'message' => 'Class ID is required.']);
    exit;
}

/* ── Load current class row ── */
$stmt = $conn->prepare("
    SELECT c.id, c.faculty_id, c.subject_id, c.course_id, c.banner_palette
    FROM   tblclass c
    WHERE  c.id = ? AND c.is_deleted = 0
    LIMIT  1
");
$stmt->bind_param('s', $classId);
$stmt->execute();
$cls = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cls) {
    echo json_encode(['status' => 'error', 'message' => 'Class not found.']);
    exit;
}

/* ── Permission check ── */
$stmt = $conn->prepare("SELECT id FROM tblfaculty WHERE email = (SELECT email FROM tbluser WHERE id = ? LIMIT 1) LIMIT 1");
$stmt->bind_param('s', $userId);
$stmt->execute();
$faculty = $stmt->get_result()->fetch_assoc();
$stmt->close();

$userLevel = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$isAdmin   = in_array($userLevel, [1, 3]);

if (!$isAdmin && (!$faculty || $faculty['id'] !== $cls['faculty_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You are not authorized to edit this class.']);
    exit;
}

/* ── Resolve subject_id ── */
$subjectId = $cls['subject_id']; // keep current unless we find a new match
if ($subjectCode) {
    $sStmt = $conn->prepare("SELECT id FROM tblsubject WHERE subject_code = ? AND is_deleted = 0 LIMIT 1");
    $sStmt->bind_param('s', $subjectCode);
    $sStmt->execute();
    $sRow = $sStmt->get_result()->fetch_assoc();
    $sStmt->close();
    if ($sRow) $subjectId = $sRow['id'];
}

/* ── Resolve course_id ── */
$courseId = $cls['course_id'];
if ($courseCode) {
    $cStmt = $conn->prepare("SELECT id FROM tblcourse WHERE course_code = ? AND is_Deleted = 0 LIMIT 1");
    $cStmt->bind_param('s', $courseCode);
    $cStmt->execute();
    $cRow = $cStmt->get_result()->fetch_assoc();
    $cStmt->close();
    if ($cRow) $courseId = $cRow['id'];
}

/* ── Determine palette ──
 *
 * Priority order:
 * 1. Faculty explicitly chose a color in the modal → use paletteInput.
 * 2. Subject changed to a different subject_id → auto-assign for new subject.
 * 3. Subject unchanged → keep existing palette.
 */
$subjectChanged = ($subjectId !== $cls['subject_id']);

if ($paletteInput !== '') {
    // Faculty manually picked a color — apply to THIS class only
    $palette = $paletteInput;
} elseif ($subjectChanged && $subjectId) {
    // Subject swapped — auto-assign palette for the new subject
    // Check if any other class already has a palette for this subject
    $pStmt = $conn->prepare("
        SELECT banner_palette
        FROM   tblclass
        WHERE  subject_id = ?
          AND  id != ?
          AND  banner_palette IS NOT NULL
          AND  banner_palette != ''
          AND  is_deleted = 0
        ORDER  BY created_at ASC
        LIMIT  1
    ");
    $pStmt->bind_param('ss', $subjectId, $classId);
    $pStmt->execute();
    $pRow = $pStmt->get_result()->fetch_assoc();
    $pStmt->close();

    $paletteSeed = $subjectName ?: ($subjectCode ?: (string)$subjectId);
    $palette = ($pRow && !empty($pRow['banner_palette']))
        ? $pRow['banner_palette']
        : getPaletteForSubject($conn, $paletteSeed);
} else {
    // Keep whatever is currently stored (or compute if NULL)
    $paletteSeed = $subjectName ?: ($subjectCode ?: (string)$subjectId);
    $palette = $cls['banner_palette'] ?: getPaletteForSubject($conn, $paletteSeed);
}

/* ── Build class_code ── */
$newCode = '';
if ($subjectCode && $yearLevel && $section) {
    $newCode = strtoupper($subjectCode) . '-' . $yearLevel . '-' . $section;
} elseif ($subjectCode) {
    $newCode = strtoupper($subjectCode);
}

/* ── Execute UPDATE ── */
if ($newCode) {
    $stmt = $conn->prepare("
        UPDATE tblclass
        SET subject_id     = ?,
            course_id      = ?,
            year_level     = ?,
            section        = ?,
            class_semester = ?,
            class_days     = ?,
            schedule       = ?,
            banner_palette = ?,
            class_code     = ?,
            updated_at     = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param('ssssssssss',
        $subjectId, $courseId, $yearLevel, $section,
        $semester, $classDays, $schedule, $palette,
        $newCode, $classId
    );
} else {
    $stmt = $conn->prepare("
        UPDATE tblclass
        SET subject_id     = ?,
            course_id      = ?,
            year_level     = ?,
            section        = ?,
            class_semester = ?,
            class_days     = ?,
            schedule       = ?,
            banner_palette = ?,
            updated_at     = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param('sssssssss',
        $subjectId, $courseId, $yearLevel, $section,
        $semester, $classDays, $schedule, $palette,
        $classId
    );
}

if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $err]);
    exit;
}
$stmt->close();
$conn->close();

echo json_encode([
    'status'  => 'success',
    'message' => 'Class updated successfully.',
    'updated' => [
        'subject_code'   => $subjectCode,
        'subject_name'   => $subjectName,
        'year_level'     => $yearLevel,
        'section'        => $section,
        'class_semester' => $semester,
        'course_code'    => $courseCode,
        'class_days'     => $classDays,
        'schedule'       => $schedule,
        'banner_palette' => $palette,
        'class_code'     => $newCode,
    ],
]);
