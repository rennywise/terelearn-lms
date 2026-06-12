<?php
/**
 * API/Admin/save_dean_account.php
 * Saves Dean / Secretary account to tbladmin.
 * tbluser is created only after department assignment (assign_dean.php).
 * Optional: if is_also_faculty=1, also adds/updates tblfaculty with is_dean=1.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $data          = json_decode(file_get_contents('php://input'), true) ?? [];
    $editId        = trim($data['id']              ?? '');
    $idNumber      = trim($data['id_number']       ?? '');
    $firstName     = trim($data['first_name']      ?? '');
    $midName       = trim($data['middle_name']     ?? '');
    $lastName      = trim($data['last_name']       ?? '');
    $suffix        = trim($data['suffix']          ?? '');
    $email         = trim($data['email']           ?? '');
    $phone         = trim($data['phone']           ?? '');
    $username      = trim($data['username']        ?? '');
    $password      = trim($data['password']        ?? '');
    $birthdate     = trim($data['birthdate']       ?? '');
    $role          = strtolower(trim($data['role'] ?? 'dean'));
    if (!in_array($role, ['dean', 'secretary'], true)) {
        $role = 'dean';
    }
    $isAlsoFaculty = (int)($data['is_also_faculty'] ?? 0);
    $isSuperAdmin  = (int)($data['is_superadmin']   ?? 0);
    $hasAdminRole  = false;
    $colRes = $conn->query("SHOW COLUMNS FROM tbladmin LIKE 'admin_role'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasAdminRole = true;
    }

    /* Validation */
    if (!$idNumber || !$firstName || !$lastName || !$email || !$username) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
        exit;
    }
    if (!$editId && !$password) {
        echo json_encode(['status' => 'error', 'message' => 'Password is required for new accounts.']);
        exit;
    }

    /* Duplicate check — exclude self when editing */
    if ($editId) {
        $chk = $conn->prepare("SELECT id FROM tbladmin WHERE (email=? OR username=? OR admin_number=?) AND id!=? LIMIT 1");
        $chk->bind_param('ssss', $email, $username, $idNumber, $editId);
    } else {
        $chk = $conn->prepare("SELECT id FROM tbladmin WHERE (email=? OR username=? OR admin_number=?) LIMIT 1");
        $chk->bind_param('sss', $email, $username, $idNumber);
    }
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email, username or ID number already exists.']);
        exit;
    }

    /* ══════════════════
       UPDATE
    ══════════════════ */
    if ($editId) {
        if ($password) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            // 11 strings: idNumber,firstName,midName,lastName,email,phone,username,birthdate,hashed + 1 int: isSuperAdmin + 1 string: editId
            $roleSql = $hasAdminRole ? ", admin_role=?" : "";
            $stmt = $conn->prepare("
                UPDATE tbladmin
                SET admin_number=?, first_name=?, middle_name=?, last_name=?, suffix=?,
                    email=?, phone=?, username=?, birthdate=?, password=?, is_superadmin=?$roleSql
                WHERE id=?");
            if ($hasAdminRole) {
                $stmt->bind_param('ssssssssssiss',
                    $idNumber, $firstName, $midName, $lastName,
                    $suffix, $email, $phone, $username, $birthdate,
                    $hashed, $isSuperAdmin, $role, $editId);
            } else {
                $stmt->bind_param('ssssssssssis',
                    $idNumber, $firstName, $midName, $lastName,
                    $suffix, $email, $phone, $username, $birthdate,
                    $hashed, $isSuperAdmin, $editId);
            }
        } else {
            $roleSql = $hasAdminRole ? ", admin_role=?" : "";
            $stmt = $conn->prepare("
                UPDATE tbladmin
                SET admin_number=?, first_name=?, middle_name=?, last_name=?, suffix=?,
                    email=?, phone=?, username=?, birthdate=?, is_superadmin=?$roleSql
                WHERE id=?");
            if ($hasAdminRole) {
                $stmt->bind_param('sssssssssiss',
                    $idNumber, $firstName, $midName, $lastName,
                    $suffix, $email, $phone, $username, $birthdate,
                    $isSuperAdmin, $role, $editId);
            } else {
                $stmt->bind_param('sssssssssis',
                    $idNumber, $firstName, $midName, $lastName,
                    $suffix, $email, $phone, $username, $birthdate,
                    $isSuperAdmin, $editId);
            }
        }
        $stmt->execute();

        /* Sync tbluser email/username if exists (exclude student rows) */
        $sync = $conn->prepare("UPDATE tbluser SET username=?, email=? WHERE (email=? OR username=?) AND is_deleted=0 AND user_level_id <> 3 LIMIT 1");
        $sync->bind_param('ssss', $username, $email, $email, $username);
        $sync->execute();

        /* Sync password in tbluser if changed */
        if ($password) {
            $hashed = $hashed ?? password_hash($password, PASSWORD_BCRYPT);
            $syncPw = $conn->prepare("UPDATE tbluser SET password=? WHERE (email=? OR username=?) AND is_deleted=0 AND user_level_id <> 3 LIMIT 1");
            $syncPw->bind_param('sss', $hashed, $email, $username);
            $syncPw->execute();
        }

        /* Handle faculty toggle */
        _syncFaculty($conn, $editId, $firstName, $midName, $lastName, $suffix,
            $email, $phone, $birthdate, $username,
            $password ? password_hash($password, PASSWORD_BCRYPT) : null,
            $idNumber, $isAlsoFaculty);

        echo json_encode(['status' => 'success', 'message' => 'Account updated successfully.', 'id' => $editId]);
        exit;
    }

    /* ══════════════════
       INSERT
    ══════════════════ */
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $newId  = _uuid();

    $typeRes    = $conn->query("SELECT user_type_id FROM tblfaculty WHERE is_deleted=0 AND user_type_id IS NOT NULL AND user_type_id!='' LIMIT 1");
    $typeRow    = $typeRes ? $typeRes->fetch_assoc() : null;
    $userTypeId = $typeRow['user_type_id'] ?? '00000000-0000-0000-0000-000000000000';

    if ($hasAdminRole) {
        $stmt = $conn->prepare("
            INSERT INTO tbladmin
              (id, admin_number, first_name, middle_name, last_name, suffix,
               user_type_id, admin_role, email, phone, birthdate, username, password,
               is_active, is_superadmin, is_deleted)
            VALUES (?,?,?,?,?,?, ?,?,?,?,?,?,?, 1,?,0)
        ");
        $stmt->bind_param('sssssssssssssi',
            $newId, $idNumber, $firstName, $midName, $lastName,
            $suffix, $userTypeId, $role, $email, $phone, $birthdate, $username, $hashed,
            $isSuperAdmin);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO tbladmin
              (id, admin_number, first_name, middle_name, last_name, suffix,
               user_type_id, email, phone, birthdate, username, password,
               is_active, is_superadmin, is_deleted)
            VALUES (?,?,?,?,?,?, ?,?,?,?,?,?, 1,?,0)
        ");
        $stmt->bind_param('ssssssssssssi',
            $newId, $idNumber, $firstName, $midName, $lastName,
            $suffix, $userTypeId, $email, $phone, $birthdate, $username, $hashed,
            $isSuperAdmin);
    }
    $stmt->execute();

    _syncFaculty($conn, $newId, $firstName, $midName, $lastName, $suffix,
        $email, $phone, $birthdate, $username, $hashed,
        $idNumber, $isAlsoFaculty);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Dean / Secretary account created successfully.' . ($isAlsoFaculty ? ' Also added as faculty.' : ''),
        'id'      => $newId
    ]);

} catch (mysqli_sql_exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();

/* ── Helpers ── */
function _uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));
}

function _syncFaculty($conn, $adminId, $fn, $mn, $ln, $suffix, $email, $phone,
                       $birthdate, $username, $hashed, $idNumber, $isAlsoFaculty) {
    $chk = $conn->prepare("SELECT id, is_deleted FROM tblfaculty WHERE (email=? OR username=?) LIMIT 1");
    $chk->bind_param('ss', $email, $username);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();

    if ($isAlsoFaculty) {
        if ($existing) {
            $upd = $conn->prepare("UPDATE tblfaculty SET is_dean=1, is_deleted=0, is_active=1 WHERE id=?");
            $upd->bind_param('s', $existing['id']);
            $upd->execute();
        } else {
            $fid  = _uuid();
            $tres = $conn->query("SELECT user_type_id FROM tblfaculty WHERE is_deleted=0 AND user_type_id IS NOT NULL LIMIT 1");
            $trow = $tres ? $tres->fetch_assoc() : null;
            $utid = $trow['user_type_id'] ?? '00000000-0000-0000-0000-000000000000';
            $pw   = $hashed ?? '$2y$10$placeholder';
            $ins  = $conn->prepare("
                INSERT INTO tblfaculty
                  (id, faculty_number, first_name, middle_name, last_name, suffix,
                   user_type_id, email, phone, birthdate, username, password,
                   is_active, is_dean, is_deleted)
                VALUES (?,?,?,?,?,?, ?,?,?,?,?,?, 1,1,0)
            ");
            $ins->bind_param('ssssssssssss',
                $fid, $idNumber, $fn, $mn, $ln,
                $suffix, $utid, $email, $phone, $birthdate, $username, $pw);
            $ins->execute();
        }
    } else {
        if ($existing) {
            $upd = $conn->prepare("UPDATE tblfaculty SET is_dean=0 WHERE id=?");
            $upd->bind_param('s', $existing['id']);
            $upd->execute();
        }
    }
}
