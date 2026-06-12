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

$conn->close();

if ($row) {
    echo json_encode([
        'status'      => 'success',
        'semester'    => $row['semester'],
        'school_year' => $row['school_year'],
    ]);
} else {
    echo json_encode([
        'status'      => 'success',
        'semester'    => 'Not Set',
        'school_year' => '—',
    ]);
}
