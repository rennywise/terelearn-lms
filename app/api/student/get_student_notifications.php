<?php
/**
 * API/student/get_student_notifications.php
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

$uid = $conn->real_escape_string($_SESSION['user_id']);

/* ── Resolve tblstudent.id (int) from session user_id (UUID) ── */
$studentId = null;

// Strategy 1: direct user_id column match
$r = $conn->query("SELECT id FROM tblstudent WHERE user_id='$uid' AND is_deleted=0 LIMIT 1");
if ($r && $r->num_rows > 0) {
    $studentId = (int)$r->fetch_assoc()['id'];
}

// Strategy 2: fallback via username / email
if (!$studentId) {
    $ur = $conn->query("SELECT username, email FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
    if ($ur && $urow = $ur->fetch_assoc()) {
        $un = $conn->real_escape_string($urow['username'] ?? '');
        $em = $conn->real_escape_string($urow['email']    ?? '');
        $r2 = $conn->query("SELECT id FROM tblstudent
                            WHERE (username='$un' OR email='$em')
                              AND is_deleted=0 LIMIT 1");
        if ($r2 && $r2->num_rows > 0) {
            $studentId = (int)$r2->fetch_assoc()['id'];
        }
    }
}

if (!$studentId) {
    echo json_encode(['status' => 'success', 'notifications' => [], 'invite_count' => 0]);
    exit;
}

/* ── Fetch pending invitations ── */
$invSql = "
    SELECT
        ce.id,
        ce.class_id,
        ce.enrolled_at                              AS created_at,
        'invitation'                                AS type,
        CONCAT(
            COALESCE(s.subject_code, ''),
            CASE WHEN s.subject_code IS NOT NULL AND s.subject_name IS NOT NULL
                 THEN ' — ' ELSE '' END,
            COALESCE(s.subject_name, c.class_code, 'Class')
        )                                           AS class_name,
        CONCAT(f.first_name, ' ', f.last_name)      AS faculty_name,
        c.class_semester,
        0                                           AS is_read
    FROM   tblclassenrollment ce
    JOIN   tblclass    c ON c.id  = ce.class_id
    LEFT JOIN tblsubject  s ON s.id  = c.subject_id
    LEFT JOIN tblfaculty  f ON f.id  = c.faculty_id
WHERE  ce.student_id = $studentId
      AND  ce.enrollment_status = 'pending'
      AND  ce.initiated_by = 'faculty'
      AND  c.is_deleted = 0
    ORDER  BY ce.enrolled_at DESC
";

$invRes  = $conn->query($invSql);
$invites = [];
if ($invRes) {
    while ($row = $invRes->fetch_assoc()) {
        $invites[] = $row;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . $conn->error]);
    $conn->close();
    exit;
}

$conn->close();

echo json_encode([
    'status'        => 'success',
    'notifications' => $invites,
    'invite_count'  => count($invites),
]);
