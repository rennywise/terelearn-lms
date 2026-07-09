<?php
/**
 * API/Admin/get_semester_footer.php
 * Returns active semester info for the faculty dashboard footer badge.
 */
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../core/db_connect.php';

$res = $conn->query(
    "SELECT school_year, semester
     FROM tblsemestersetting
     WHERE is_active = 1
     ORDER BY id DESC LIMIT 1"
);
$row = $res ? $res->fetch_assoc() : null;

$weekCount = 18;
$settingsTable = $conn->query(
    "SELECT 1
     FROM INFORMATION_SCHEMA.TABLES
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'tblacademicsettings'
     LIMIT 1"
);
if ($settingsTable && $settingsTable->num_rows > 0) {
    $weekRes = $conn->query(
        "SELECT setting_value
         FROM tblacademicsettings
         WHERE setting_key = 'global_academic_weeks'
         LIMIT 1"
    );
    $weekRow = $weekRes ? $weekRes->fetch_assoc() : null;
    $weekCount = max(1, min(30, (int)($weekRow['setting_value'] ?? 18) ?: 18));
}

$conn->close();

if ($row) {
    echo json_encode([
        'status'      => 'success',
        'semester'    => $row['semester'],
        'school_year' => $row['school_year'],
        'week_count'  => $weekCount,
    ]);
} else {
    echo json_encode([
        'status'      => 'success',
        'semester'    => 'Not Set',
        'week_count'  => $weekCount,
        'school_year' => '—',
    ]);
}
