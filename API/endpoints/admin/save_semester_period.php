<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$d   = json_decode(file_get_contents('php://input'), true);
$id  = intval($d['id']          ?? 0);
$sy  = trim($d['school_year']   ?? '');
$sem = trim($d['semester']      ?? '');
$st  = trim($d['start_date']    ?? '') ?: null;
$en  = trim($d['end_date']      ?? '') ?: null;
$act = intval($d['is_active']   ?? 0);

if (!$sy || !$sem) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields missing.']);
    exit;
}

/* If this period is being set active, deactivate all others first */
if ($act) {
    $conn->query("UPDATE tblsemestersetting SET is_active = 0");
}

if ($id) {
    /* UPDATE existing period */
    $stmt = $conn->prepare(
        "UPDATE tblsemestersetting
         SET school_year=?, semester=?, start_date=?, end_date=?, is_active=?, updated_at=NOW()
         WHERE id=?"
    );
    $stmt->bind_param('ssssii', $sy, $sem, $st, $en, $act, $id);  // FIX: was 'ssssi i' (had a space)
} else {
    /* INSERT new period */
    $stmt = $conn->prepare(
        "INSERT INTO tblsemestersetting (school_year, semester, start_date, end_date, is_active, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
    );
    $stmt->bind_param('ssssi', $sy, $sem, $st, $en, $act);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => $id ? 'Period updated.' : 'Period added.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
