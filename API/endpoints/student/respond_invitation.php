<?php
/**
 * API/student/respond_invitation.php
 * Student accepts or declines a class invitation.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

$body     = json_decode(file_get_contents('php://input'), true);
$class_id = trim($body['class_id'] ?? '');
$action   = trim($body['action']   ?? ''); // 'accept' | 'decline'
$uid      = $conn->real_escape_string($_SESSION['user_id']);

if (!$class_id || !in_array($action, ['accept', 'decline'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$esc_cid = $conn->real_escape_string($class_id);

/* ── Resolve tblstudent row from session user_id ── */
$sid        = null; // integer PK from tblstudent
$studentUuid = null; // user_id (UUID) from tblstudent

// Strategy 1: direct user_id match
$sRes = $conn->query("SELECT id, user_id FROM tblstudent WHERE user_id='$uid' AND is_deleted=0 LIMIT 1");
if ($sRes && $sRes->num_rows > 0) {
    $sRow        = $sRes->fetch_assoc();
    $sid         = (int)$sRow['id'];
    $studentUuid = $sRow['user_id'];
}

// Strategy 2: fallback via username / email
if (!$sid) {
    $ur = $conn->query("SELECT username, email FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
    if ($ur && $urow = $ur->fetch_assoc()) {
        $un = $conn->real_escape_string($urow['username'] ?? '');
        $em = $conn->real_escape_string($urow['email']    ?? '');
        $r2 = $conn->query("SELECT id, user_id FROM tblstudent
                            WHERE (username='$un' OR email='$em')
                              AND is_deleted=0 LIMIT 1");
        if ($r2 && $r2->num_rows > 0) {
            $sRow2       = $r2->fetch_assoc();
            $sid         = (int)$sRow2['id'];
            $studentUuid = $sRow2['user_id'];
        }
    }
}

if (!$sid) {
    echo json_encode(['status' => 'error', 'message' => 'Student record not found']);
    exit;
}

/*
 * ── Find the invitation ──
 *
 * invite_students.php stores student_id in tblinvitations as the value
 * from tblstudent.id (an integer, stored as a string/varchar).
 * We try matching on both the integer id AND the UUID to be safe.
 */
$chk = null;

// Try matching on integer student id first (how invite_students.php stores it)
$chk = $conn->query("SELECT id FROM tblinvitations
                     WHERE class_id='$esc_cid'
                       AND student_id='$sid'
                       AND invitation_status='pending'
                     LIMIT 1");

// If not found and we have a UUID, try that too
if ((!$chk || $chk->num_rows === 0) && $studentUuid) {
    $escUuid = $conn->real_escape_string($studentUuid);
    $chk = $conn->query("SELECT id FROM tblinvitations
                         WHERE class_id='$esc_cid'
                           AND student_id='$escUuid'
                           AND invitation_status='pending'
                         LIMIT 1");
}

if (!$chk || $chk->num_rows === 0) {
    // Also check tblclassenrollment for a pending invite-sourced row
    // (covers cases where only enrollment was inserted without tblinvitations)
    $fallback = $conn->query("SELECT id FROM tblclassenrollment
                              WHERE class_id='$esc_cid'
                                AND student_id='$sid'
                                AND enrollment_status='pending'
                                AND source='invite'
                              LIMIT 1");
    if (!$fallback || $fallback->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No pending invitation found for this class']);
        exit;
    }
    // Proceed without tblinvitations row — handle below
    $invFoundInEnrollmentOnly = true;
} else {
    $invFoundInEnrollmentOnly = false;
}

function gen_uuid() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

if ($action === 'accept') {
    $classRes = $conn->query("
        SELECT c.is_active, c.semester_setting_id, active_sem.id AS active_semester_id
        FROM tblclass c
        LEFT JOIN (
            SELECT id
            FROM tblsemestersetting
            WHERE is_active=1 AND is_deleted=0
            ORDER BY id DESC
            LIMIT 1
        ) active_sem ON 1=1
        WHERE c.id='$esc_cid' AND c.is_deleted=0
        LIMIT 1
    ");
    $classRow = $classRes ? $classRes->fetch_assoc() : null;
    $classSemId  = (int)($classRow['semester_setting_id'] ?? 0);
    $activeSemId = (int)($classRow['active_semester_id'] ?? 0);
    $isArchived  = $classSemId > 0 && ($activeSemId <= 0 || $classSemId !== $activeSemId);

    if (!$classRow || !(int)$classRow['is_active'] || $isArchived) {
        echo json_encode(['status' => 'error', 'message' => "This class isn't available yet."]);
        exit;
    }

    /* 1. Mark invitation accepted in tblinvitations (try both id and uuid) */
    if (!$invFoundInEnrollmentOnly) {
        $conn->query("UPDATE tblinvitations
                      SET invitation_status='accepted', responded_at=NOW()
                      WHERE class_id='$esc_cid'
                        AND (student_id='$sid'" .
                        ($studentUuid ? " OR student_id='" . $conn->real_escape_string($studentUuid) . "'" : "") .
                      ")");
    }

    /* 2. Upsert enrollment as enrolled */
    $enrChk = $conn->query("SELECT id FROM tblclassenrollment
                            WHERE class_id='$esc_cid' AND student_id='$sid' LIMIT 1");
    if ($enrChk && $enrChk->num_rows > 0) {
        $conn->query("UPDATE tblclassenrollment
                      SET enrollment_status='enrolled', source='invitation',
                          initiated_by='student', dropped_at=NULL, enrolled_at=NOW()
                      WHERE class_id='$esc_cid' AND student_id='$sid'");
    } else {
        $newEid = gen_uuid();
        $conn->query("INSERT INTO tblclassenrollment
                          (id, class_id, student_id, enrollment_status, source, initiated_by, enrolled_at)
                      VALUES ('$newEid','$esc_cid','$sid','enrolled','invitation','student',NOW())");
    }

    /* 3. Mirror to tblclassstudents (legacy roster) using UUID if available */
    if ($studentUuid) {
        $escUuid2 = $conn->real_escape_string($studentUuid);
        $chk2 = $conn->query("SELECT id FROM tblclassstudents
                              WHERE class_id='$esc_cid'
                                AND student_id='$escUuid2'
                                AND is_deleted=0 LIMIT 1");
        if (!$chk2 || $chk2->num_rows === 0) {
            $newId = gen_uuid();
            $conn->query("INSERT INTO tblclassstudents (id, class_id, student_id)
                          VALUES ('$newId','$esc_cid','$escUuid2')");
        }
    }

    $conn->close();
    echo json_encode(['status' => 'success', 'message' => 'You have joined the class!']);

} else {

    /* Decline */
    if (!$invFoundInEnrollmentOnly) {
        $conn->query("UPDATE tblinvitations
                      SET invitation_status='declined', responded_at=NOW()
                      WHERE class_id='$esc_cid'
                        AND (student_id='$sid'" .
                        ($studentUuid ? " OR student_id='" . $conn->real_escape_string($studentUuid) . "'" : "") .
                      ")");
    }

    /* Remove the pending enrollment row so it doesn't linger */
    $conn->query("UPDATE tblclassenrollment
                  SET enrollment_status='declined'
                  WHERE class_id='$esc_cid'
                    AND student_id='$sid'
                    AND enrollment_status='pending'");

    $conn->close();
    echo json_encode(['status' => 'success', 'message' => 'Invitation declined.']);
}
