<?php
/**
 * API/Admin/save_program.php
 * Create or update a program (tblcourse) and optionally link it to a department.
 * When department_id is provided, the course.department_id column is set — 
 * this is what subadmin.php reads to scope subjects/classes/students to the dean's dept.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$data       = json_decode(file_get_contents('php://input'), true) ?? [];
$id         = trim($data['id']            ?? '');
$code       = strtoupper(trim($data['course_code']  ?? ''));
$name       = trim($data['course_name']   ?? '');
$deptId     = isset($data['department_id']) && $data['department_id'] !== '' && $data['department_id'] !== null
              ? (int)$data['department_id'] : null;

if (!$code || !$name) {
    echo json_encode(['status' => 'error', 'message' => 'Code and name are required.']);
    exit;
}

try {
    if ($id) {
        /* UPDATE */
        if ($deptId) {
            $stmt = $conn->prepare("UPDATE tblcourse SET course_code=?, course_name=?, department_id=? WHERE id=?");
            $stmt->bind_param('ssii', $code, $name, $deptId, $id);
        } else {
            $stmt = $conn->prepare("UPDATE tblcourse SET course_code=?, course_name=?, department_id=NULL WHERE id=?");
            $stmt->bind_param('ssi', $code, $name, $id);
        }
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Program updated successfully.']);
    } else {
        /* INSERT */
        // Check for duplicate code
        $chk = $conn->prepare("SELECT id FROM tblcourse WHERE course_code=? AND is_deleted=0 LIMIT 1");
        $chk->bind_param('s', $code);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => "Program code '$code' already exists."]);
            exit;
        }

        if ($deptId) {
            $stmt = $conn->prepare("INSERT INTO tblcourse (course_code, course_name, department_id, is_active, is_deleted) VALUES (?,?,?,1,0)");
            $stmt->bind_param('ssi', $code, $name, $deptId);
        } else {
            $stmt = $conn->prepare("INSERT INTO tblcourse (course_code, course_name, is_active, is_deleted) VALUES (?,?,1,0)");
            $stmt->bind_param('ss', $code, $name);
        }
        $stmt->execute();
        $newId = $conn->insert_id;
        echo json_encode(['status' => 'success', 'message' => 'Program created successfully.', 'id' => $newId]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
}
$conn->close();
