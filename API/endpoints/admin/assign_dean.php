<?php
/**
 * API/Admin/assign_dean.php
 *
 * Assigns a tbladmin account to a department.
 *
 * On NEW assignment:
 *   1. Inserts into tbldeanassignment
 *   2. Creates tbluser row (if not exists) so the person can log in
 *
 * On EDIT (role change only):
 *   1. Updates role in tbldeanassignment
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $data   = json_decode(file_get_contents('php://input'), true) ?? [];
    $editId = trim($data['edit_id'] ?? '');
    $role   = strtolower(trim($data['role']    ?? 'dean'));
    if (!in_array($role, ['dean', 'secretary'], true)) {
        $role = 'dean';
    }
    $hasAdminRole = false;
    $colRes = $conn->query("SHOW COLUMNS FROM tbladmin LIKE 'admin_role'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasAdminRole = true;
    }

    /* ════════════════════
       EDIT — role change only
    ════════════════════ */
    if ($editId) {
        $stmt = $conn->prepare("UPDATE tbldeanassignment SET role=? WHERE id=?");
        $stmt->bind_param('si', $role, $editId);
        $stmt->execute();
        if ($hasAdminRole) {
            $syncRole = $conn->prepare("
                UPDATE tbladmin a
                JOIN tbldeanassignment da ON da.faculty_id = a.id
                SET a.admin_role = ?
                WHERE da.id = ?
            ");
            $syncRole->bind_param('si', $role, $editId);
            $syncRole->execute();
        }
        echo json_encode(['status' => 'success', 'message' => 'Assignment role updated.']);
        exit;
    }

    /* ════════════════════
       NEW ASSIGNMENT
    ════════════════════ */
    $adminAccountId = trim($data['dean_account_id'] ?? '');
    $deptId         = (int)($data['department_id']   ?? 0);

    if (!$adminAccountId || !$deptId) {
        echo json_encode(['status' => 'error', 'message' => 'dean_account_id and department_id are required.']);
        exit;
    }

    /* Get the tbladmin row */
    $aStmt = $conn->prepare("SELECT * FROM tbladmin WHERE id=? AND is_deleted=0 LIMIT 1");
    $aStmt->bind_param('s', $adminAccountId);
    $aStmt->execute();
    $admin = $aStmt->get_result()->fetch_assoc();
    if (!$admin) {
        echo json_encode(['status' => 'error', 'message' => 'Account not found in tbladmin.']);
        exit;
    }

    /* Deactivate any existing active assignment for this person in this dept */
    $deact = $conn->prepare("
        UPDATE tbldeanassignment SET is_active=0
        WHERE  faculty_id=? AND department_id=? AND is_active=1");
    $deact->bind_param('si', $adminAccountId, $deptId);
    $deact->execute();

    /* Insert new assignment */
    $ins = $conn->prepare("
        INSERT INTO tbldeanassignment (faculty_id, department_id, role, is_active, assigned_at)
        VALUES (?, ?, ?, 1, NOW())");
    $ins->bind_param('sis', $adminAccountId, $deptId, $role);
    $ins->execute();
    if ($hasAdminRole) {
        $syncRole = $conn->prepare("UPDATE tbladmin SET admin_role=? WHERE id=?");
        $syncRole->bind_param('ss', $role, $adminAccountId);
        $syncRole->execute();
    }

    /* Create tbluser row if it doesn't exist yet */
    $email    = $admin['email'];
    $username = $admin['username'];
    $password = $admin['password'];

    $chk = $conn->prepare("
        SELECT id FROM tbluser
        WHERE (email=? OR username=?) AND is_deleted=0 AND user_level_id <> 3 LIMIT 1");
    $chk->bind_param('ss', $email, $username);
    $chk->execute();
    $existingUser = $chk->get_result()->fetch_assoc();

    if (!$existingUser) {
        $uid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0,0xffff), mt_rand(0,0xffff),
            mt_rand(0,0xffff),
            mt_rand(0,0x0fff)|0x4000,
            mt_rand(0,0x3fff)|0x8000,
            mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));

        $userLevelId = 2;
        $isDean      = 1;

        $uIns = $conn->prepare("
            INSERT INTO tbluser
              (id, email, username, password, user_level_id,
               is_dean, is_active, is_deleted, first_login, otp_enabled)
            VALUES (?, ?, ?, ?, ?, ?, 1, 0, 1, 0)
        ");
        $uIns->bind_param('ssssii',
            $uid, $email, $username, $password,
            $userLevelId, $isDean);
        $uIns->execute();

        echo json_encode([
            'status'       => 'success',
            'message'      => "Assigned as {$role}. Login account created — they can now sign in.",
            'user_created' => true
        ]);
    } else {
        $upd = $conn->prepare("UPDATE tbluser SET is_dean=1 WHERE id=?");
        $upd->bind_param('s', $existingUser['id']);
        $upd->execute();

        echo json_encode([
            'status'       => 'success',
            'message'      => "Assigned as {$role}. Existing login account updated.",
            'user_created' => false
        ]);
    }

} catch (mysqli_sql_exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
