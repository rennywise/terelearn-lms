<?php
/**
 * API/facultyUI/classroom/attendance/save_attendance.php
 * POST  JSON: { class_id, date, records: [{student_id, status}] }
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}
require_once __DIR__ . '/../../../../core/db_connect.php';
require_once __DIR__ . '/_helpers.php';

$body     = json_decode(file_get_contents('php://input'), true) ?: [];
$class_id = trim($body['class_id'] ?? '');
$date     = trim($body['date']     ?? '');
$records  = $body['records'] ?? [];

if (!$class_id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !is_array($records)) {
    echo json_encode(['status'=>'error','message'=>'class_id, date, records required']); exit;
}

$user_id    = $_SESSION['user_id'];
$faculty_id = att_resolve_faculty_id($conn, $user_id);
if (!$faculty_id) { echo json_encode(['status'=>'error','message'=>'Faculty record not found']); exit; }

$class = att_verify_class_owner($conn, $class_id, $faculty_id);
if (!$class) { echo json_encode(['status'=>'error','message'=>'Class not found or access denied']); exit; }

$sem = att_class_semester($conn, $class['semester_setting_id'] ? (int)$class['semester_setting_id'] : null);
if (!$sem) { echo json_encode(['status'=>'error','message'=>'No semester configured']); exit; }

if (!att_date_allowed($date, $sem)) {
    echo json_encode(['status'=>'error','message'=>'Date is outside the active semester or is in the future']); exit;
}

$c   = $conn->real_escape_string($class_id);
$d   = $conn->real_escape_string($date);
$u   = $conn->real_escape_string($user_id);
$sid = (int)$sem['id'];

$conn->begin_transaction();
try {
    /* Find or create the session row */
    $r = $conn->query("SELECT id FROM tblattendance WHERE class_id='$c' AND attendance_date='$d' AND is_deleted=0 LIMIT 1");
    $existing = ($r && $r->num_rows > 0);
    $isCreate = !$existing;
    $oldStatuses = [];

    if ($existing) {
        $attendance_id = $r->fetch_assoc()['id'];
        $aidEsc = $conn->real_escape_string($attendance_id);
        $oldRes = $conn->query("SELECT student_id, status FROM tblattendancerecord WHERE attendance_id='$aidEsc'");
        if ($oldRes) while ($row = $oldRes->fetch_assoc()) $oldStatuses[$row['student_id']] = $row['status'];
        $conn->query("UPDATE tblattendance SET updated_by='$u', semester_setting_id=$sid WHERE id='$aidEsc'");
    } else {
        $attendance_id = att_uuid();
        $aidEsc = $conn->real_escape_string($attendance_id);
        $conn->query("
            INSERT INTO tblattendance (id, class_id, attendance_date, semester_setting_id, created_by, updated_by)
            VALUES ('$aidEsc','$c','$d',$sid,'$u','$u')
        ");
    }

    /* Validate every student belongs to this class */
    $validIds = [];
    $vRes = $conn->query("SELECT student_id FROM tblclassenrollment WHERE class_id='$c' AND enrollment_status='enrolled'");
    if ($vRes) while ($row = $vRes->fetch_assoc()) $validIds[$row['student_id']] = true;

    /* Upsert each record */
    $insertedCount = 0; $changedCount = 0;
    foreach ($records as $rec) {
        $stuId  = (string)($rec['student_id'] ?? '');
        $status = ($rec['status'] ?? '') === 'absent' ? 'absent' : 'present';
        if ($stuId === '' || !isset($validIds[$stuId])) continue;

        $stuEsc = $conn->real_escape_string($stuId);
        $oldStatus = $oldStatuses[$stuId] ?? null;

        if ($oldStatus === null) {
            $rid = att_uuid();
            $ridEsc = $conn->real_escape_string($rid);
            $conn->query("
                INSERT INTO tblattendancerecord (id, attendance_id, student_id, status)
                VALUES ('$ridEsc','$aidEsc','$stuEsc','$status')
                ON DUPLICATE KEY UPDATE status='$status'
            ");
            $insertedCount++;
        } elseif ($oldStatus !== $status) {
            $conn->query("
                UPDATE tblattendancerecord
                SET    status='$status', updated_at=current_timestamp()
                WHERE  attendance_id='$aidEsc' AND student_id='$stuEsc'
            ");
            $changedCount++;
        }
    }

    /* Audit */
    $code = $class['class_code'];
    if ($isCreate) {
        att_audit($conn, $user_id, "Created attendance for class $code on $date ($insertedCount records)");
    } elseif ($changedCount > 0 || $insertedCount > 0) {
        $msg = "Edited attendance for class $code on $date";
        if ($changedCount > 0) $msg .= " ($changedCount changed";
        if ($insertedCount > 0) $msg .= ($changedCount > 0 ? ", $insertedCount added)" : " ($insertedCount added)");
        elseif ($changedCount > 0) $msg .= ")";
        att_audit($conn, $user_id, $msg);
    }

    $conn->commit();

    /* Return fresh summary */
    $sumRes = $conn->query("
        SELECT COUNT(*) AS total,
               SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) AS present_count,
               SUM(CASE WHEN status='absent'  THEN 1 ELSE 0 END) AS absent_count
        FROM tblattendancerecord WHERE attendance_id='$aidEsc'
    ");
    $sum = $sumRes->fetch_assoc();
    $total = (int)$sum['total'];
    $present = (int)$sum['present_count'];
    $absent  = (int)$sum['absent_count'];
    $pct = $total > 0 ? round(($present / $total) * 100) : 0;
    $tier = $pct >= 80 ? 'high' : ($pct >= 50 ? 'mid' : 'low');

    $conn->close();
    echo json_encode([
        'status'      => 'success',
        'attendance_id' => $attendance_id,
        'created'     => $isCreate,
        'changed'     => $changedCount,
        'inserted'    => $insertedCount,
        'summary'     => [
            'date'       => $date,
            'present'    => $present,
            'absent'     => $absent,
            'total'      => $total,
            'percentage' => $pct,
            'tier'       => $tier,
        ],
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    $conn->close();
    echo json_encode(['status'=>'error','message'=>'Save failed: '.$e->getMessage()]);
}
