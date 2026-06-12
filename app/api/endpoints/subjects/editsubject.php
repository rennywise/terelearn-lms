<?php
/**
 * API/editsubject.php
 *
 * Updates an existing subject in tblsubject, then upserts its
 * tblsubjectpreset row for the active school year + chosen semester + year level.
 *
 * POST fields (application/x-www-form-urlencoded):
 *   subject_id    string  required
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
    $id        = trim($_POST['subject_id']   ?? '');
    $code      = trim($_POST['subject_code'] ?? '');
    $name      = trim($_POST['subject_name'] ?? '');
    $course    = trim($_POST['course_id']    ?? '');
    $yearLevel = (int)($_POST['year_level']  ?? 0);
    $semester  = trim($_POST['semester']     ?? '');
    $shareScope = sharedCourseNormalizeScope($_POST['share_scope'] ?? 'department_only');
    $sharedPrograms = sharedCourseParseProgramIds($_POST['shared_program_ids'] ?? []);

    /* ── Validation ── */
    if (!$id || !$code || !$name || !$course) {
        throw new Exception('All fields are required.');
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

    /* ── Update tblsubject ── */
    $upd = $conn->prepare(
        "UPDATE tblsubject
         SET subject_code = ?, subject_name = ?, course_id = ?
         WHERE id = ? AND is_deleted = 0"
    );
    $upd->bind_param('ssss', $code, $name, $course, $id);
    if (!$upd->execute()) {
        throw new Exception('Update failed: ' . $conn->error);
    }
    if ($upd->affected_rows === 0) {
        /* 0 affected rows could mean no change — that is fine, not an error */
    }
    $upd->close();

    /* ── Fetch active school year ── */
    $semRes = $conn->query(
        "SELECT school_year FROM tblsemestersetting
         WHERE is_active = 1 AND is_deleted = 0
         ORDER BY id DESC LIMIT 1"
    );
    if (!$semRes || $semRes->num_rows === 0) {
        throw new Exception('No active semester. Ask the administrator to set one.');
    }
    $schoolYear = $semRes->fetch_assoc()['school_year'];
    $targetPrograms = sharedCourseTargetPrograms($conn, $course, $shareScope, $sharedPrograms);

    /* ── Upsert tblsubjectpreset ──
       If a preset already exists for this subject + course + school_year + semester,
       update its year_level. Otherwise insert a new row.
    ── */
    $setBy = $_SESSION['user_id'] ?? null;

    /* Check existing preset for this exact combination */
    $chkPre = $conn->prepare(
        "SELECT id FROM tblsubjectpreset
         WHERE subject_id = ? AND course_id = ? AND school_year = ? AND semester = ?
         LIMIT 1"
    );
    $chkPre->bind_param('ssss', $id, $course, $schoolYear, $semester);
    $chkPre->execute();
    $preRow = $chkPre->get_result()->fetch_assoc();
    $chkPre->close();
    $excludePresetId = $preRow ? (int)$preRow['id'] : null;

    $dupPreset = sharedCourseFindDuplicatePreset(
        $conn,
        $code,
        $schoolYear,
        $semester,
        $yearLevel,
        $targetPrograms,
        $excludePresetId
    );
    if ($dupPreset) {
        throw new Exception("A course with code '{$code}' already exists for one of the selected programs in {$semester} / {$schoolYear}.");
    }

    if ($preRow) {
        /* UPDATE existing preset row */
        $updPre = $conn->prepare(
            "UPDATE tblsubjectpreset
             SET course_id = ?, owner_course_id = ?, year_level = ?, share_scope = ?, allow_cross_program_adoption = 1, set_by = ?
             WHERE id = ?"
        );
        $presetId = (int)$preRow['id'];
        $updPre->bind_param('ssissi', $course, $course, $yearLevel, $shareScope, $setBy, $presetId);
        if (!$updPre->execute()) {
            throw new Exception('Subject updated but preset update failed: ' . $conn->error);
        }
        $updPre->close();
        sharedCourseSyncPrograms($conn, $presetId, $course, $shareScope, $sharedPrograms, $setBy);
    } else {
        /* INSERT new preset row */
        $insPre = $conn->prepare(
            "INSERT INTO tblsubjectpreset
                (subject_id, course_id, owner_course_id, school_year, semester, year_level, share_scope, allow_cross_program_adoption, set_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)"
        );
        $insPre->bind_param('sssssiss', $id, $course, $course, $schoolYear, $semester, $yearLevel, $shareScope, $setBy);
        if (!$insPre->execute()) {
            throw new Exception('Subject updated but preset insert failed: ' . $conn->error);
        }
        $presetId = (int)$insPre->insert_id;
        $insPre->close();
        sharedCourseSyncPrograms($conn, $presetId, $course, $shareScope, $sharedPrograms, $setBy);
    }

    $response = [
        'status'  => 'success',
        'message' => 'Subject updated successfully!',
    ];

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

$conn->close();
echo json_encode($response);
