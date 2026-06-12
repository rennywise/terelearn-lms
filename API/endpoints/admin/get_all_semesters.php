<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

/* FIX: removed WHERE is_deleted=0 — that column does not exist in tblsemestersetting */
$res  = $conn->query(
    "SELECT * FROM tblsemestersetting ORDER BY school_year DESC, id DESC"
);
$data = [];
while ($r = $res->fetch_assoc()) {
    $data[] = $r;
}

echo json_encode(['status' => 'success', 'data' => $data]);
$conn->close();
