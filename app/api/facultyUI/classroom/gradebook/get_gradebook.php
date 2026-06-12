<?php
header('Content-Type: application/json');
require_once __DIR__ . '/_helpers.php';

$userId = gb_require_faculty_user();
$classId = trim((string)($_GET['class_id'] ?? ''));
if ($classId === '') gb_json_fail('class_id required');

$facultyId = gb_resolve_faculty_id($conn, $userId);
if (!$facultyId) gb_json_fail('Faculty record not found', 404);

$class = gb_verify_class_owner($conn, $classId, $facultyId);
if (!$class) gb_json_fail('Class not found or access denied', 403);

$data = gb_get_gradebook($conn, $classId);
$conn->close();

echo json_encode([
    'status' => 'success',
    'class' => $class,
    'gradebook' => $data,
]);
