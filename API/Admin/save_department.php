<?php
/**
 * API/Admin/save_department.php
 * Create or update a department.
 * IMPORTANT: Returns 'id' in the response so the caller can immediately
 * use it to link programs via link_programs_to_dept.php.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$id   = isset($data['id']) && $data['id'] ? (int)$data['id'] : null;
$code = strtoupper(trim($data['dept_code']  ?? ''));
$name = trim($data['dept_name']   ?? '');
$desc = trim($data['description'] ?? '');

if (!$code || !$name) {
    echo json_encode(['status' => 'error', 'message' => 'Code and name are required.']);
    exit;
}

try {
    if ($id) {
        /* UPDATE */
        $stmt = $conn->prepare(
            "UPDATE tbldepartment SET dept_code=?, dept_name=?, description=? WHERE id=?"
        );
        $stmt->bind_param('sssi', $code, $name, $desc, $id);
        $stmt->execute();
        echo json_encode([
            'status'  => 'success',
            'message' => 'Department updated successfully.',
            'id'      => $id
        ]);
    } else {
        /* CHECK DUPLICATE */
        $chk = $conn->prepare("SELECT id FROM tbldepartment WHERE dept_code=? AND is_active=1 LIMIT 1");
        $chk->bind_param('s', $code);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => "Department code '$code' already exists."]);
            exit;
        }

        /* INSERT */
        $stmt = $conn->prepare(
            "INSERT INTO tbldepartment (dept_code, dept_name, description, is_active) VALUES (?,?,?,1)"
        );
        $stmt->bind_param('sss', $code, $name, $desc);
        $stmt->execute();
        $newId = (int)$conn->insert_id;
        echo json_encode([
            'status'  => 'success',
            'message' => 'Department created successfully.',
            'id'      => $newId   // ← caller uses this to link programs
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
}
$conn->close();
