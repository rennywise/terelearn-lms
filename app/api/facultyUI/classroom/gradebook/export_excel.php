<?php
require_once __DIR__ . '/_helpers.php';

$userId = gb_require_faculty_user();
$classId = trim((string)($_GET['class_id'] ?? ''));
if ($classId === '') gb_json_fail('class_id required');

$facultyId = gb_resolve_faculty_id($conn, $userId);
if (!$facultyId) gb_json_fail('Faculty record not found', 404);

$class = gb_verify_class_owner($conn, $classId, $facultyId);
if (!$class) gb_json_fail('Class not found or access denied', 403);

$data = gb_get_gradebook($conn, $classId);
$rows = $data['students'] ?? [];
$assessments = $data['assessments'] ?? [];

$filename = 'gradebook_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', ($class['class_code'] ?? $classId)) . '_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $filename);

echo "\xEF\xBB\xBF"; // UTF-8 BOM
$out = fopen('php://output', 'w');

fputcsv($out, ['GRADEBOOK SUMMARY']);
fputcsv($out, ['Generated At', $data['generated_at'] ?? date('Y-m-d H:i:s')]);
fputcsv($out, []);
fputcsv($out, ['Student ID', 'Name', 'Recitation %', 'Quiz %', 'Activities %', 'Exam %', 'Final Grade']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['student_number'] ?? '',
        $r['student_name'] ?? '',
        $r['categories']['recitation']['percentage'] ?? 0,
        $r['categories']['quiz']['percentage'] ?? 0,
        $r['categories']['activities']['percentage'] ?? 0,
        $r['categories']['exam']['percentage'] ?? 0,
        $r['final_grade'] ?? 0,
    ]);
}

fputcsv($out, []);
fputcsv($out, ['DETAILED RECORDS (with date taken and score)']);
fputcsv($out, ['Student ID', 'Student Name', 'Category', 'Assessment', 'Score', 'Max Score', 'Percentage', 'Date Taken']);

$studentById = [];
foreach ($rows as $r) $studentById[$r['student_id']] = $r;

foreach ($assessments as $a) {
    $scores = $a['scores'] ?? [];
    foreach ($scores as $sid => $sv) {
        if (!isset($studentById[$sid])) continue;
        if (!isset($sv['score']) || $sv['score'] === null) continue;
        fputcsv($out, [
            $studentById[$sid]['student_number'] ?? '',
            $studentById[$sid]['student_name'] ?? '',
            ucfirst((string)($a['category'] ?? '')),
            $a['title'] ?? '',
            $sv['score'],
            $a['max_score'] ?? 0,
            $sv['percentage'] ?? 0,
            $sv['taken_at'] ?? '',
        ]);
    }
}

fclose($out);
$conn->close();
exit;
