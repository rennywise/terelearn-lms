<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

function arc_col(mysqli $conn, string $table, string $column): bool {
    $stmt = $conn->prepare("
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
        LIMIT 1
    ");
    if (!$stmt) return false;
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $ok = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $ok;
}

$userId = (string)$_SESSION['user_id'];
$ids = [$userId];

$stmt = $conn->prepare("
    SELECT s.id
    FROM tblstudent s
    LEFT JOIN tbluser u ON u.id = ? OR u.username = s.username OR u.email = s.email
    WHERE (s.user_id = ? OR u.id = ?)
      AND COALESCE(s.is_deleted, 0) = 0
    LIMIT 1
");
if ($stmt) {
    $stmt->bind_param('sss', $userId, $userId, $userId);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) $ids[] = (string)$row['id'];
    $stmt->close();
}

$ids = array_values(array_unique(array_filter($ids)));
$classes = [];

if ($ids) {
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('s', count($ids));
    $deptImageSelect = arc_col($conn, 'tbldepartment', 'dept_image') ? 'd.dept_image' : 'NULL AS dept_image';

    $sql = "
        SELECT DISTINCT
            c.id,
            c.class_code,
            c.join_code,
            c.class_semester,
            c.class_days,
            c.schedule,
            c.year_level,
            c.section,
            c.banner_palette,
            s.subject_code,
            s.subject_name,
            co.course_code,
            co.course_name,
            d.dept_code,
            d.dept_name,
            {$deptImageSelect},
            f.id AS faculty_id,
            f.first_name AS faculty_first_name,
            f.last_name AS faculty_last_name,
            CONCAT(TRIM(f.first_name), ' ', TRIM(f.last_name)) AS faculty_name
        FROM tblclassstudents cs
        JOIN tblclass c ON c.id = cs.class_id AND COALESCE(c.is_deleted, 0) = 0
        LEFT JOIN tblsubject s ON s.id = c.subject_id
        LEFT JOIN tblcourse co ON co.id = c.course_id
        LEFT JOIN tbldepartment d ON d.id = co.department_id AND COALESCE(d.is_deleted, 0) = 0
        LEFT JOIN tblfaculty f ON f.id = c.faculty_id AND COALESCE(f.is_deleted, 0) = 0
        WHERE cs.student_id IN ($ph)
          AND COALESCE(cs.is_deleted, 0) = 0
          AND COALESCE(cs.is_archived, 0) = 1
        ORDER BY c.class_semester DESC, s.subject_name ASC
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $classes[] = $row;
        $stmt->close();
    }
}

echo json_encode(['status' => 'success', 'classes' => $classes]);
?>
