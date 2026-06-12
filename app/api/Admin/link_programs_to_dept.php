<?php
/**
 * API/Admin/link_programs_to_dept.php
 * Sets department_id on tblcourse rows to link them to a department.
 * Also clears department_id for programs previously linked to this dept
 * that are no longer in the list (unlink removed ones).
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$data       = json_decode(file_get_contents('php://input'), true) ?? [];
$deptId     = (int)($data['department_id'] ?? 0);
$programIds = $data['program_ids'] ?? [];   // array of int|string IDs to link

if (!$deptId) {
    echo json_encode(['status' => 'error', 'message' => 'department_id is required.']);
    exit;
}

try {
    // 1. Unlink programs that WERE linked to this dept but are NOT in the new list
    if (!empty($programIds)) {
        $safeIds = implode(',', array_map('intval', $programIds));
        $conn->query("
            UPDATE tblcourse
            SET    department_id = NULL
            WHERE  department_id = $deptId
              AND  id NOT IN ($safeIds)
              AND  is_deleted = 0
        ");
    } else {
        // No programs selected — unlink all
        $conn->query("UPDATE tblcourse SET department_id=NULL WHERE department_id=$deptId AND is_deleted=0");
        echo json_encode(['status' => 'success', 'message' => 'All programs unlinked from department.']);
        exit;
    }

    // 2. Link the selected programs to this dept
    $safeIds = implode(',', array_map('intval', $programIds));
    $conn->query("
        UPDATE tblcourse
        SET    department_id = $deptId
        WHERE  id IN ($safeIds)
          AND  is_deleted = 0
    ");

    echo json_encode([
        'status'  => 'success',
        'message' => count($programIds) . ' program(s) linked to department.',
        'linked'  => count($programIds)
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
}
$conn->close();
