<?php
/**
 * API/savesubject.php
 *
 * Creates a new subject in tblsubject, then immediately registers it
 * in tblsubjectpreset for the active semester + chosen year level.
 *
 * POST fields (application/x-www-form-urlencoded):
 *   subject_code  string  required
 *   subject_name  string  required
 *   course_id     string  required
 *   year_level    int     required  (1–5)
 *   semester      string  required  ("1st Semester" | "2nd Semester")
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/shared_course_helper.php';

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    $code      = trim($_POST['subject_code'] ?? '');
    $name      = trim($_POST['subject_name'] ?? '');
    $course    = trim($_POST['course_id']    ?? '');
    $yearLevel = (int)($_POST['year_level']  ?? 0);
    $semester  = trim($_POST['semester']     ?? '');
    $shareScope = sharedCourseNormalizeScope($_POST['share_scope'] ?? 'department_only');
    $sharedPrograms = sharedCourseParseProgramIds($_POST['shared_program_ids'] ?? []);

    /* ── Validation ── */
    if (!$code || !$name || !$course) {
        throw new Exception('Subject code, name, and program are required.');
    }
    if ($yearLevel < 1 || $yearLevel > 5) {
        throw new Exception('Please select a valid year level.');
    }
    $validSems = ['1st Semester', '2nd Semester'];
    if (!in_array($semester, $validSems, true)) {
        throw new Exception('Please select a valid semester.');
    }
    if ($shareScope === 'selected_programs' && !$sharedPrograms) {
        throw new Exception('Please select at least one shared program.');
    }

    /* ── Duplicate code check ── */
    $chk = $conn->prepare(
        "SELECT id FROM tblsubject WHERE subject_code = ? AND course_id = ? AND is_deleted = 0 LIMIT 1"
    );
    $chk->bind_param('ss', $code, $course);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        throw new Exception("Subject code '{$code}' already exists.");
    }
    $chk->close();

    /* ── Fetch active school year from tblsemestersetting ── */
    $semRes = $conn->query(
        "SELECT school_year, semester FROM tblsemestersetting
         WHERE is_active = 1 AND is_deleted = 0
         ORDER BY id DESC LIMIT 1"
    );
    if (!$semRes || $semRes->num_rows === 0) {
        throw new Exception('No active semester found. Ask the administrator to set one.');
    }
    $semRow     = $semRes->fetch_assoc();
    $schoolYear = $semRow['school_year'];
    $targetPrograms = sharedCourseTargetPrograms($conn, $course, $shareScope, $sharedPrograms);

    $dupPreset = sharedCourseFindDuplicatePreset(
        $conn,
        $code,
        $schoolYear,
        $semester,
        $yearLevel,
        $targetPrograms
    );
    if ($dupPreset) {
        throw new Exception("A course with code '{$code}' already exists for one of the selected programs in {$semester} / {$schoolYear}.");
    }

    /* ── INSERT into tblsubject ── */
    $uuid = bin2hex(random_bytes(18));
    $ins  = $conn->prepare(
        "INSERT INTO tblsubject (id, subject_code, subject_name, course_id, is_active, is_deleted)
         VALUES (?, ?, ?, ?, '1', '0')"
    );
    $ins->bind_param('ssss', $uuid, $code, $name, $course);
    if (!$ins->execute()) {
        throw new Exception('Failed to save subject: ' . $conn->error);
    }
    $ins->close();

    /* ── INSERT into tblsubjectpreset ── */
    /* Use session user id as set_by if available, otherwise NULL */
    $setBy = $_SESSION['user_id'] ?? null;
    $pre = $conn->prepare(
        "INSERT INTO tblsubjectpreset
            (subject_id, course_id, owner_course_id, school_year, semester, year_level, share_scope, allow_cross_program_adoption, set_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)"
    );
    $pre->bind_param('sssssiss', $uuid, $course, $course, $schoolYear, $semester, $yearLevel, $shareScope, $setBy);
    if (!$pre->execute()) {
        /* Roll back the subject row so data stays consistent */
        $conn->query("UPDATE tblsubject SET is_deleted=1 WHERE id='$uuid'");
        throw new Exception('Subject saved but preset failed: ' . $conn->error);
    }
    $presetId = (int)$pre->insert_id;
    $pre->close();
    sharedCourseSyncPrograms($conn, $presetId, $course, $shareScope, $sharedPrograms, $setBy);

    $response = [
        'status'  => 'success',
        'message' => 'Subject added and preset for ' . $semester . ' · ' . $schoolYear . '!',
    ];

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

$conn->close();
echo json_encode($response);
