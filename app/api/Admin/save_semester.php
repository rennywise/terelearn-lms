<?php
/**
 * API/Admin/save_semester.php
 *
 * Changes the active semester/school year.
 *
 * HOW ARCHIVING WORKS:
 * ─────────────────────
 * - Each tblclass row has semester_setting_id = the semester it was CREATED IN.
 * - Dashboard shows: classes WHERE semester_setting_id = active semester id.
 * - Archive shows:   classes WHERE semester_setting_id != active semester id.
 *
 * When admin changes semester:
 * - We deactivate the old semester row.
 * - We INSERT or REACTIVATE the new semester row.
 * - We do NOT touch tblclass at all — classes stay linked to their creation semester.
 * - This means classes from 2026-2027 automatically appear in archive
 *   when the active semester changes to 2027-2028.
 *
 * NOTE: Empty school year folders in archive happen when tblsemestersetting
 * has rows with no classes. The archive API only shows groups that have classes,
 * so empty folders never appear.
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../core/db_connect.php';

$data        = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$school_year = trim($data['school_year'] ?? '');
$semester    = trim($data['semester']    ?? '');
$admin_id    = trim($data['admin_id']   ?? '');

if (!$school_year || !$semester) {
    echo json_encode(['status' => 'error', 'message' => 'School year and semester are required.']);
    exit;
}

/* ── Get currently active semester ── */
$cur = $conn->query(
    "SELECT id, school_year, semester FROM tblsemestersetting WHERE is_active = 1 ORDER BY id DESC LIMIT 1"
)->fetch_assoc();

/* ── Skip if nothing changed ── */
if ($cur && $cur['school_year'] === $school_year && $cur['semester'] === $semester) {
    echo json_encode(['status' => 'info', 'message' => 'No changes — that semester is already active.']);
    exit;
}

$conn->begin_transaction();
try {
    /* 1. Deactivate ALL current semester rows */
    $conn->query("UPDATE tblsemestersetting SET is_active = 0");

    /* 2. Check if this school_year + semester combo already exists */
    $exists = $conn->prepare(
        "SELECT id FROM tblsemestersetting WHERE school_year = ? AND semester = ? LIMIT 1"
    );
    $exists->bind_param('ss', $school_year, $semester);
    $exists->execute();
    $existingRow = $exists->get_result()->fetch_assoc();
    $exists->close();

    if ($existingRow) {
        /* Reactivate the existing row — keeps the same ID so archive links stay intact */
        $reactivate = $conn->prepare(
            "UPDATE tblsemestersetting SET is_active = 1, updated_at = NOW() WHERE id = ?"
        );
        $reactivate->bind_param('i', $existingRow['id']);
        $reactivate->execute();
        $reactivate->close();
        $new_sem_id = $existingRow['id'];
    } else {
        /* Insert brand new semester row */
        $ins = $conn->prepare(
            "INSERT INTO tblsemestersetting (school_year, semester, is_active, set_by, created_at, updated_at)
             VALUES (?, ?, 1, ?, NOW(), NOW())"
        );
        $ins->bind_param('sss', $school_year, $semester, $admin_id);
        $ins->execute();
        $new_sem_id = $conn->insert_id;
        $ins->close();
    }

    /* 3. Log the change */
    $old_sy  = $cur['school_year'] ?? null;
    $old_sem = $cur['semester']    ?? null;
    $log = $conn->prepare(
        "INSERT INTO tblsemesterlog
           (old_school_year, old_semester, new_school_year, new_semester, changed_by, changed_at)
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $log->bind_param('sssss', $old_sy, $old_sem, $school_year, $semester, $admin_id);
    $log->execute();
    $log->close();

    $conn->commit();

    echo json_encode([
        'status'         => 'success',
        'message'        => "Semester changed to {$semester} — {$school_year}. Faculty dashboards will now show a fresh start.",
        'new_semester_id' => $new_sem_id,
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
}
$conn->close();
