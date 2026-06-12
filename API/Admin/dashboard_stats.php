<?php
/* API/Admin/dashboard_stats.php */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$stats = [];
$stats['total_departments'] = (int)$conn->query("SELECT COUNT(*) FROM tbldepartment WHERE is_deleted=0")->fetch_row()[0];
$stats['total_programs']    = (int)$conn->query("SELECT COUNT(*) FROM tblcourse WHERE is_Deleted=0")->fetch_row()[0];
$stats['total_faculty']     = (int)$conn->query("SELECT COUNT(*) FROM tblfaculty WHERE is_deleted=0 AND (is_dean IS NULL OR is_dean=0)")->fetch_row()[0];
$stats['total_students']    = (int)$conn->query("SELECT COUNT(*) FROM tblstudent WHERE is_deleted=0")->fetch_row()[0];
$stats['total_subjects']    = (int)$conn->query("SELECT COUNT(*) FROM tblsubject WHERE is_deleted=0")->fetch_row()[0];
$stats['total_classes']     = (int)$conn->query("SELECT COUNT(*) FROM tblclass WHERE is_deleted=0")->fetch_row()[0];
$stats['active_deans']      = (int)$conn->query("SELECT COUNT(*) FROM tbldeanassignment WHERE is_active=1")->fetch_row()[0];

$conn->close();
echo json_encode(['status' => 'success', 'stats' => $stats]);
