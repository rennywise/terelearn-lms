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

header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Gradebook Export</title>
<style>
body{font-family:Arial,sans-serif;padding:24px;color:#111}
h1{font-size:22px;margin:0 0 6px}
.meta{color:#555;margin-bottom:16px}
table{border-collapse:collapse;width:100%;margin-top:10px}
th,td{border:1px solid #d9d9d9;padding:8px;font-size:12px;text-align:left}
th{background:#f3f4f6;font-weight:700}
.section{margin-top:22px}
@media print{button{display:none}}
</style>
</head>
<body>
<button onclick="window.print()">Print / Save as PDF</button>
<h1>Gradebook - <?= htmlspecialchars($class['class_code'] ?? $classId) ?></h1>
<div class="meta">Generated: <?= htmlspecialchars($data['generated_at'] ?? date('Y-m-d H:i:s')) ?></div>

<div class="section">
  <h3>Summary</h3>
  <table>
    <thead><tr><th>Student ID</th><th>Name</th><th>Recitation %</th><th>Quiz %</th><th>Activities %</th><th>Exam %</th><th>Final Grade</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['student_number'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['student_name'] ?? '') ?></td>
        <td><?= htmlspecialchars((string)($r['categories']['recitation']['percentage'] ?? 0)) ?></td>
        <td><?= htmlspecialchars((string)($r['categories']['quiz']['percentage'] ?? 0)) ?></td>
        <td><?= htmlspecialchars((string)($r['categories']['activities']['percentage'] ?? 0)) ?></td>
        <td><?= htmlspecialchars((string)($r['categories']['exam']['percentage'] ?? 0)) ?></td>
        <td><strong><?= htmlspecialchars((string)($r['final_grade'] ?? 0)) ?></strong></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="section">
  <h3>Detailed Records</h3>
  <table>
    <thead><tr><th>Student ID</th><th>Name</th><th>Category</th><th>Assessment</th><th>Score</th><th>Max</th><th>%</th><th>Date Taken</th></tr></thead>
    <tbody>
    <?php
    $studentById = [];
    foreach ($rows as $r) $studentById[$r['student_id']] = $r;
    foreach ($assessments as $a):
      $scores = $a['scores'] ?? [];
      foreach ($scores as $sid => $sv):
        if (!isset($studentById[$sid])) continue;
        if (!isset($sv['score']) || $sv['score'] === null) continue;
    ?>
      <tr>
        <td><?= htmlspecialchars($studentById[$sid]['student_number'] ?? '') ?></td>
        <td><?= htmlspecialchars($studentById[$sid]['student_name'] ?? '') ?></td>
        <td><?= htmlspecialchars(ucfirst((string)($a['category'] ?? ''))) ?></td>
        <td><?= htmlspecialchars((string)($a['title'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string)$sv['score']) ?></td>
        <td><?= htmlspecialchars((string)($a['max_score'] ?? 0)) ?></td>
        <td><?= htmlspecialchars((string)($sv['percentage'] ?? 0)) ?></td>
        <td><?= htmlspecialchars((string)($sv['taken_at'] ?? '')) ?></td>
      </tr>
    <?php endforeach; endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
<?php $conn->close(); ?>
