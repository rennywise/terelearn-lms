<?php
/**
 * API/facultyUI/get_faculty_classes.php
 * FIXED: Dynamically checks for profile_picture column before selecting it,
 *        so the query never fails on missing columns.
 */
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../core/db_connect.php';

/* ── Auth guard ── */
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized — not logged in as faculty.']);
    exit;
}

$user_id = $_SESSION['user_id'];

/* ── Step 1: Check which optional columns exist in tbluser ── */
$hasPicCol = false;
$colCheck = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbluser' AND COLUMN_NAME='profile_picture' LIMIT 1");
if ($colCheck && $colCheck->num_rows > 0) {
    $hasPicCol = true;
}
$picSelect = $hasPicCol ? ', profile_picture' : '';

/* ── Step 2: Get tbluser record ── */
$uStmt = $conn->prepare(
    "SELECT id, username, email{$picSelect} FROM tbluser WHERE id = ? AND is_deleted = 0 LIMIT 1"
);
if (!$uStmt) {
    echo json_encode(['status' => 'error', 'message' => 'DB prepare failed: ' . $conn->error]);
    exit;
}
$uStmt->bind_param('s', $user_id);
$uStmt->execute();
$tblUser = $uStmt->get_result()->fetch_assoc();
$uStmt->close();

if (!$tblUser) {
    echo json_encode(['status' => 'error', 'message' => 'User session invalid — user not found in tbluser.']);
    exit;
}

$username        = $tblUser['username']         ?? '';
$email           = $tblUser['email']            ?? '';
$profile_picture = $tblUser['profile_picture']  ?? null;
$faculty         = null;

/* ── Step 3: Find tblfaculty row (multiple strategies) ── */

// A: direct username
if (!$faculty && $username) {
    $s = $conn->prepare("SELECT * FROM tblfaculty WHERE username = ? AND is_deleted = 0 LIMIT 1");
    if ($s) { $s->bind_param('s', $username); $s->execute(); $faculty = $s->get_result()->fetch_assoc(); $s->close(); }
}
// B: direct email
if (!$faculty && $email) {
    $s = $conn->prepare("SELECT * FROM tblfaculty WHERE email = ? AND is_deleted = 0 LIMIT 1");
    if ($s) { $s->bind_param('s', $email); $s->execute(); $faculty = $s->get_result()->fetch_assoc(); $s->close(); }
}
// C: tblfaculty.user_id column (if exists)
if (!$faculty) {
    $cc = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tblfaculty' AND COLUMN_NAME='user_id' LIMIT 1");
    if ($cc && $cc->num_rows > 0) {
        $s = $conn->prepare("SELECT * FROM tblfaculty WHERE user_id = ? AND is_deleted = 0 LIMIT 1");
        if ($s) { $s->bind_param('s', $user_id); $s->execute(); $faculty = $s->get_result()->fetch_assoc(); $s->close(); }
    }
}
// D: via tbladmin bridge (username)
if (!$faculty && $username) {
    $s = $conn->prepare("
        SELECT f.* FROM tblfaculty f
        INNER JOIN tbladmin a ON a.id = f.id
        WHERE a.username = ? AND f.is_deleted = 0 LIMIT 1
    ");
    if ($s) { $s->bind_param('s', $username); $s->execute(); $faculty = $s->get_result()->fetch_assoc(); $s->close(); }
}
// E: via tbladmin bridge (email)
if (!$faculty && $email) {
    $s = $conn->prepare("
        SELECT f.* FROM tblfaculty f
        INNER JOIN tbladmin a ON a.id = f.id
        WHERE a.email = ? AND f.is_deleted = 0 LIMIT 1
    ");
    if ($s) { $s->bind_param('s', $email); $s->execute(); $faculty = $s->get_result()->fetch_assoc(); $s->close(); }
}
// F: session faculty_id fallback
if (!$faculty && isset($_SESSION['faculty_id'])) {
    $s = $conn->prepare("SELECT * FROM tblfaculty WHERE id = ? AND is_deleted = 0 LIMIT 1");
    if ($s) { $s->bind_param('s', $_SESSION['faculty_id']); $s->execute(); $faculty = $s->get_result()->fetch_assoc(); $s->close(); }
}

/* ── Step 4: Get tbladmin.id ── */
$admin_id = null;
if ($username || $email) {
    if ($username && $email) {
        $s = $conn->prepare("SELECT id FROM tbladmin WHERE (username=? OR email=?) AND is_deleted=0 LIMIT 1");
        if ($s) { $s->bind_param('ss', $username, $email); $s->execute(); $aRow = $s->get_result()->fetch_assoc(); $s->close(); if ($aRow) $admin_id = $aRow['id']; }
    } elseif ($username) {
        $s = $conn->prepare("SELECT id FROM tbladmin WHERE username=? AND is_deleted=0 LIMIT 1");
        if ($s) { $s->bind_param('s', $username); $s->execute(); $aRow = $s->get_result()->fetch_assoc(); $s->close(); if ($aRow) $admin_id = $aRow['id']; }
    } else {
        $s = $conn->prepare("SELECT id FROM tbladmin WHERE email=? AND is_deleted=0 LIMIT 1");
        if ($s) { $s->bind_param('s', $email); $s->execute(); $aRow = $s->get_result()->fetch_assoc(); $s->close(); if ($aRow) $admin_id = $aRow['id']; }
    }
}

/* ── Step 5: Synthetic faculty if not found ── */
if (!$faculty) {
    $nameCols = [];
    $nc = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbluser'
        AND COLUMN_NAME IN ('first_name','last_name','middle_name')");
    if ($nc) { while ($r = $nc->fetch_assoc()) $nameCols[] = $r['COLUMN_NAME']; }

    $selectCols = 'id, username, email';
    foreach (['first_name','middle_name','last_name'] as $c) {
        if (in_array($c, $nameCols)) $selectCols .= ", $c";
    }
    $uF = $conn->prepare("SELECT $selectCols FROM tbluser WHERE id=? LIMIT 1");
    $uFR = [];
    if ($uF) { $uF->bind_param('s', $user_id); $uF->execute(); $uFR = $uF->get_result()->fetch_assoc() ?? []; $uF->close(); }

    $faculty = [
        'id'             => $admin_id ?? $user_id,
        'faculty_number' => '',
        'first_name'     => $uFR['first_name']  ?? $username ?? 'Faculty',
        'middle_name'    => $uFR['middle_name'] ?? '',
        'last_name'      => $uFR['last_name']   ?? 'Member',
        'email'          => $email,
        'phone'          => '',
        'username'       => $username,
        'is_dean'        => 0,
        'is_active'      => 1,
        'profile_picture'=> $profile_picture,
    ];
}

/* ── Step 6: Determine correct faculty_id for tblclass ── */
$faculty_id = $faculty['id'];

if ($admin_id && $admin_id !== $faculty_id) {
    $c1 = 0; $c2 = 0;
    $t = $conn->prepare("SELECT COUNT(*) as cnt FROM tblclass WHERE faculty_id=? AND is_deleted=0");
    if ($t) { $t->bind_param('s', $faculty_id); $t->execute(); $c1 = (int)($t->get_result()->fetch_assoc()['cnt'] ?? 0); $t->close(); }
    $t2 = $conn->prepare("SELECT COUNT(*) as cnt FROM tblclass WHERE faculty_id=? AND is_deleted=0");
    if ($t2) { $t2->bind_param('s', $admin_id); $t2->execute(); $c2 = (int)($t2->get_result()->fetch_assoc()['cnt'] ?? 0); $t2->close(); }
    if ($c2 > $c1) $faculty_id = $admin_id;
}

/* ── Step 7: Active semester ── */
$activeSem     = null;
$active_sem_id = 0;
$semRes = $conn->query("SELECT id, school_year, semester FROM tblsemestersetting WHERE is_active=1 ORDER BY id DESC LIMIT 1");
if ($semRes) {
    $activeSem     = $semRes->fetch_assoc();
    $active_sem_id = (int)($activeSem['id'] ?? 0);
}

/* ── Step 8: Fetch classes ── */
$classes = [];

if ($active_sem_id > 0) {
    $cStmt = $conn->prepare("
        SELECT
            c.id, c.class_code, c.section, c.class_semester,
            c.semester_setting_id, c.year_level, c.schedule,
            c.break_time, c.class_days, c.is_active, c.created_at, c.course_id,
            IFNULL(c.source, 'admin') AS source,
            s.subject_code, s.subject_name, s.id AS subject_id,
            co.course_code, co.course_name,
            (SELECT COUNT(*) FROM tblclassstudents cs
             WHERE cs.class_id = c.id AND cs.is_deleted = 0) AS student_count
        FROM tblclass c
        LEFT JOIN tblsubject s  ON s.id  = c.subject_id
        LEFT JOIN tblcourse  co ON co.id = c.course_id
        WHERE c.faculty_id = ?
          AND c.is_deleted = 0
          AND (c.semester_setting_id = ? OR c.semester_setting_id IS NULL)
          AND (
              (IFNULL(c.source,'admin') <> 'faculty' AND c.is_active = 1)
              OR (IFNULL(c.source,'admin') = 'faculty')
          )
        ORDER BY c.is_active DESC, c.created_at DESC
    ");
    if ($cStmt) {
        $cStmt->bind_param('si', $faculty_id, $active_sem_id);
        $cStmt->execute();
        $classes = $cStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $cStmt->close();
    }
}

if (empty($classes)) {
    $cStmt2 = $conn->prepare("
        SELECT
            c.id, c.class_code, c.section, c.class_semester,
            c.semester_setting_id, c.year_level, c.schedule,
            c.break_time, c.class_days, c.is_active, c.created_at, c.course_id,
            IFNULL(c.source, 'admin') AS source,
            s.subject_code, s.subject_name, s.id AS subject_id,
            co.course_code, co.course_name,
            (SELECT COUNT(*) FROM tblclassstudents cs
             WHERE cs.class_id = c.id AND cs.is_deleted = 0) AS student_count
        FROM tblclass c
        LEFT JOIN tblsubject s  ON s.id  = c.subject_id
        LEFT JOIN tblcourse  co ON co.id = c.course_id
        WHERE c.faculty_id = ?
          AND c.is_deleted = 0
        ORDER BY c.is_active DESC, c.created_at DESC
    ");
    if ($cStmt2) {
        $cStmt2->bind_param('s', $faculty_id);
        $cStmt2->execute();
        $classes = $cStmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $cStmt2->close();
    }
}

$conn->close();

$pic = $profile_picture ?? ($faculty['profile_picture'] ?? null);

echo json_encode([
    'status'          => 'success',
    'faculty'         => [
        'id'             => $faculty['id'],
        'faculty_number' => $faculty['faculty_number'] ?? '',
        'first_name'     => $faculty['first_name']     ?? '',
        'middle_name'    => $faculty['middle_name']    ?? '',
        'last_name'      => $faculty['last_name']      ?? '',
        'email'          => $faculty['email']          ?? $email,
        'phone'          => $faculty['phone']          ?? '',
        'username'       => $faculty['username']       ?? $username,
        'is_dean'        => $faculty['is_dean']        ?? 0,
        'is_active'      => $faculty['is_active']      ?? 1,
        'profile_picture'=> $pic,
    ],
    'classes'         => $classes,
    'active_semester' => $activeSem,
    'total'           => count($classes),
]);
