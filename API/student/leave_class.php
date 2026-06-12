<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$class_id = trim($input['class_id'] ?? '');
$user_id = $_SESSION['user_id'];

if ($class_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing class ID']);
    exit;
}

require_once __DIR__ . '/../../core/db_connect.php';

$student_ids = [$user_id];

$stmtFind = $conn->prepare("
    SELECT s.id
    FROM tblstudent s
    INNER JOIN tbluser u ON (u.username = s.username OR u.email = s.email)
    WHERE u.id = ? AND s.is_deleted = 0
    LIMIT 1
");
$stmtFind->bind_param('s', $user_id);
$stmtFind->execute();
$resFind = $stmtFind->get_result();
if ($row = $resFind->fetch_assoc()) {
    $student_ids[] = $row['id'];
}
$stmtFind->close();

$conn->begin_transaction();

try {
    $affected = 0;

    foreach (array_unique($student_ids) as $sid) {
        $stmt = $conn->prepare("
            UPDATE tblclassstudents
            SET is_deleted = 1
            WHERE class_id = ?
              AND student_id = ?
              AND is_deleted = 0
        ");
        $stmt->bind_param('ss', $class_id, $sid);
        $stmt->execute();
        $affected += $stmt->affected_rows;
        $stmt->close();

        $stmt2 = $conn->prepare("
            UPDATE tblclassenrollment
            SET enrollment_status = 'dropped', dropped_at = NOW()
            WHERE class_id = ?
              AND student_id = ?
              AND enrollment_status <> 'dropped'
        ");
        $stmt2->bind_param('ss', $class_id, $sid);
        $stmt2->execute();
        $affected += $stmt2->affected_rows;
        $stmt2->close();
    }

    $conn->commit();

    echo json_encode(
        $affected > 0
            ? ['status' => 'success', 'message' => 'Left class successfully']
            : ['status' => 'error', 'message' => 'Class not found or already left']
    );
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
