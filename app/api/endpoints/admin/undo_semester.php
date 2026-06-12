<?php
/* API/Admin/undo_semester.php */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;
$log_id   = intval($data['log_id']   ?? 0);
$admin_id = trim($data['admin_id']   ?? '');

if (!$log_id) {
    echo json_encode(['status'=>'error','message'=>'No log entry to undo.']);
    exit;
}

/* Get the log entry */
$stmt = $conn->prepare("SELECT * FROM tblsemesterlog WHERE id=?");
$stmt->bind_param('i', $log_id);
$stmt->execute();
$log = $stmt->get_result()->fetch_assoc();

if (!$log || !$log['old_school_year']) {
    echo json_encode(['status'=>'error','message'=>'Cannot undo — no previous state recorded.']);
    exit;
}

$conn->begin_transaction();
try {
    /* Restore old setting */
    $conn->query("UPDATE tblsemestersetting SET is_active=0");

    $ins = $conn->prepare("INSERT INTO tblsemestersetting (school_year, semester, is_active, set_by) VALUES (?,?,1,?)");
    $ins->bind_param('sss', $log['old_school_year'], $log['old_semester'], $admin_id);
    $ins->execute();

    /* Log the undo itself */
    $logStmt = $conn->prepare("INSERT INTO tblsemesterlog
        (old_school_year, old_semester, new_school_year, new_semester, changed_by)
        VALUES (?,?,?,?,?)");
    $logStmt->bind_param('sssss',
        $log['new_school_year'], $log['new_semester'],
        $log['old_school_year'], $log['old_semester'],
        $admin_id
    );
    $logStmt->execute();

    /* Remove the log entry that was undone so it can't be undone twice */
    $del = $conn->prepare("DELETE FROM tblsemesterlog WHERE id=?");
    $del->bind_param('i', $log_id);
    $del->execute();

    $conn->commit();
    echo json_encode([
        'status'  => 'success',
        'message' => 'Reverted to '.$log['old_semester'].' '.$log['old_school_year'].'.',
        'restored'=> ['school_year'=>$log['old_school_year'],'semester'=>$log['old_semester']]
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status'=>'error','message'=>'Undo failed: '.$e->getMessage()]);
}
$conn->close();
