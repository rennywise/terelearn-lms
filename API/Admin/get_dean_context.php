<?php
/* API/Admin/get_dean_context.php
   GET ?user_id=<tbluser.id>
   Returns the departments this faculty member is dean/secretary of,
   along with their name and linked programs.
*/
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$user_id = trim($_GET['user_id'] ?? '');
if (!$user_id) { echo json_encode(['status'=>'error','message'=>'user_id required.']); exit; }

/* Get faculty record via email match with tbluser */
$fac = $conn->prepare("
    SELECT f.id AS faculty_id,
           CONCAT(f.first_name,' ',f.last_name) AS faculty_name,
           f.faculty_number
    FROM tblfaculty f
    JOIN tbluser u ON u.email = f.email
    WHERE u.id = ? AND f.is_deleted = 0
    LIMIT 1
");
$fac->bind_param('s', $user_id);
$fac->execute();
$facRow = $fac->get_result()->fetch_assoc();

if (!$facRow) {
    echo json_encode(['status'=>'error','message'=>'Faculty record not found.']);
    exit;
}

$faculty_id = $facRow['faculty_id'];

/* Get their dept assignments */
$stmt = $conn->prepare("
    SELECT da.id, da.department_id, da.role,
           d.dept_code, d.dept_name, d.description
    FROM tbldeanassignment da
    JOIN tbldepartment d ON d.id = da.department_id
    WHERE da.faculty_id = ? AND da.is_active = 1 AND d.is_deleted = 0
    ORDER BY da.role, d.dept_code
");
$stmt->bind_param('s', $faculty_id);
$stmt->execute();
$depts = [];
$res   = $stmt->get_result();
while ($r = $res->fetch_assoc()) $depts[] = $r;

/* For each dept, get its linked programs */
foreach ($depts as &$d) {
    $ps = $conn->prepare("SELECT id, course_code, course_name FROM tblcourse WHERE department_id=? AND is_Deleted=0");
    $ps->bind_param('i', $d['department_id']);
    $ps->execute();
    $programs = [];
    $pres = $ps->get_result();
    while ($p = $pres->fetch_assoc()) $programs[] = $p;
    $d['programs'] = $programs;
}
unset($d);

$conn->close();
echo json_encode([
    'status'       => 'success',
    'faculty_id'   => $faculty_id,
    'faculty_name' => $facRow['faculty_name'],
    'depts'        => $depts,
]);
