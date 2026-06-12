<?php
/* API/Admin/save_admin_account.php */
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

$id          = $input['id']          ?? null;
$id_number   = trim($input['id_number']   ?? '');
$first_name  = trim($input['first_name']  ?? '');
$middle_name = trim($input['middle_name'] ?? '');
$last_name   = trim($input['last_name']   ?? '');
$suffix      = trim($input['suffix']      ?? '');
$email       = trim($input['email']       ?? '');
$phone       = trim($input['phone']       ?? '');
$username    = trim($input['username']    ?? '');
$birthdate   = $input['birthdate']        ?? null;
$password    = $input['password']         ?? null;

if (!$id_number || !$first_name || !$last_name || !$email || !$username) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
    exit;
}

// Check login credential conflicts in tbluser to avoid creating unusable admin accounts.
if ($id) {
    // For edit: allow existing mapped admin login row (level 1), but block conflicts with other rows.
    $uConflict = $conn->prepare("
        SELECT id, user_level_id
        FROM tbluser
        WHERE (email = ? OR username = ?) AND is_deleted = 0
        LIMIT 1
    ");
    $uConflict->bind_param('ss', $email, $username);
    $uConflict->execute();
    $uRowConflict = $uConflict->get_result()->fetch_assoc();
    $uConflict->close();

    if ($uRowConflict && (int)$uRowConflict['user_level_id'] !== 1) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email/username is already used by another user account. Use unique login credentials for this admin.'
        ]);
        exit;
    }
} else {
    // For create: any existing active login row using same email/username is a hard conflict.
    $uConflict = $conn->prepare("
        SELECT id
        FROM tbluser
        WHERE (email = ? OR username = ?) AND is_deleted = 0
        LIMIT 1
    ");
    $uConflict->bind_param('ss', $email, $username);
    $uConflict->execute();
    $hasConflict = $uConflict->get_result()->num_rows > 0;
    $uConflict->close();

    if ($hasConflict) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email/username already exists in login accounts. Please use unique credentials.'
        ]);
        exit;
    }
}

// Check for duplicate email/username/admin_number (excluding self on edit)
$dupCheck = $conn->prepare(
    "SELECT id FROM tbladmin
     WHERE (email = ? OR username = ? OR admin_number = ?)
      AND id != COALESCE(?, '')"
);
$exclude = $id ?? '';
$dupCheck->bind_param('ssss', $email, $username, $id_number, $exclude);
$dupCheck->execute();
if ($dupCheck->get_result()->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email, username, or ID number already exists.']);
    exit;
}
$dupCheck->close();

// Fetch default user_type_id for admin (adjust UUID to match your tblusertype)
$user_type_id = 1; // tbluserlevel: 1 = Admin

if ($id) {
    // UPDATE
    if ($password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "UPDATE tbladmin SET admin_number=?, first_name=?, middle_name=?, last_name=?, suffix=?,
             email=?, phone=?, username=?, birthdate=?, password=?, updated_at=NOW()
             WHERE id=? AND is_deleted=0"
        );
        $stmt->bind_param('sssssssssss', $id_number, $first_name, $middle_name, $last_name, $suffix,
                          $email, $phone, $username, $birthdate, $hashed, $id);
    } else {
        $stmt = $conn->prepare(
            "UPDATE tbladmin SET admin_number=?, first_name=?, middle_name=?, last_name=?, suffix=?,
             email=?, phone=?, username=?, birthdate=?, updated_at=NOW()
             WHERE id=? AND is_deleted=0"
        );
        $stmt->bind_param('ssssssssss', $id_number, $first_name, $middle_name, $last_name, $suffix,
                          $email, $phone, $username, $birthdate, $id);
    }
    $stmt->execute();
    $stmt->close();

    // Keep admin login row in tbluser in sync (never touch student rows)
    $uSel = $conn->prepare("SELECT id FROM tbluser WHERE (email=? OR username=?) AND is_deleted=0 AND user_level_id=1 LIMIT 1");
    $uSel->bind_param('ss', $email, $username);
    $uSel->execute();
    $uRow = $uSel->get_result()->fetch_assoc();
    $uSel->close();

    if ($uRow) {
        if ($password) {
            $uPwHash = password_hash($password, PASSWORD_DEFAULT);
            $uUpd = $conn->prepare("UPDATE tbluser SET email=?, username=?, password=?, is_active=1, updated_at=NOW() WHERE id=?");
            $uUpd->bind_param('ssss', $email, $username, $uPwHash, $uRow['id']);
        } else {
            $uUpd = $conn->prepare("UPDATE tbluser SET email=?, username=?, is_active=1, updated_at=NOW() WHERE id=?");
            $uUpd->bind_param('sss', $email, $username, $uRow['id']);
        }
        $uUpd->execute();
        $uUpd->close();
    } else {
        // Only create admin login row if no conflicting active login row exists
        $uChk = $conn->prepare("SELECT id, user_level_id FROM tbluser WHERE (email=? OR username=?) AND is_deleted=0 LIMIT 1");
        $uChk->bind_param('ss', $email, $username);
        $uChk->execute();
        $uConflict = $uChk->get_result()->fetch_assoc();
        $uChk->close();

        if (!$uConflict) {
            $uid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
                mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
                mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));
            $uPwHash = $password ? password_hash($password, PASSWORD_DEFAULT) : '$2y$10$placeholderplaceholderplaceholderplaceh';
            $uIns = $conn->prepare("INSERT INTO tbluser (id, email, username, password, user_level_id, is_dean, is_active, is_deleted, first_login, otp_enabled) VALUES (?, ?, ?, ?, 1, 0, 1, 0, 1, 1)");
            $uIns->bind_param('ssss', $uid, $email, $username, $uPwHash);
            $uIns->execute();
            $uIns->close();
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Admin details were updated, but login sync failed because email/username is already used by another login account. Please use unique credentials.',
                'id'      => $id
            ]);
            exit;
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Admin account updated.', 'id' => $id]);
} else {
    // INSERT
    if (!$password) {
        echo json_encode(['status' => 'error', 'message' => 'Password is required for new accounts.']);
        exit;
    }
    $hashed  = password_hash($password, PASSWORD_DEFAULT);
    $new_id  = bin2hex(random_bytes(16));
    // Format as UUID
    $uuid    = sprintf('%s-%s-%s-%s-%s',
        substr($new_id, 0, 8), substr($new_id, 8, 4),
        substr($new_id, 12, 4), substr($new_id, 16, 4),
        substr($new_id, 20)
    );
    $stmt = $conn->prepare(
        "INSERT INTO tbladmin (id, admin_number, first_name, middle_name, last_name, suffix,
         user_type_id, email, phone, birthdate, username, password, is_active, is_superadmin, is_deleted)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, 0)"
    );
    // Correct bind signature: 11 strings + 1 int (user_type_id)
    $stmt->bind_param('ssssssisssss', $uuid, $id_number, $first_name, $middle_name, $last_name, $suffix,
                      $user_type_id, $email, $phone, $birthdate, $username, $hashed);
    $stmt->execute();
    $stmt->close();

    // Create matching login row in tbluser for admin sign-in (if no conflict)
    $uChk = $conn->prepare("SELECT id, user_level_id FROM tbluser WHERE (email=? OR username=?) AND is_deleted=0 LIMIT 1");
    $uChk->bind_param('ss', $email, $username);
    $uChk->execute();
    $uConflict = $uChk->get_result()->fetch_assoc();
    $uChk->close();

    if (!$uConflict) {
        $uid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
            mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
            mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));
        $uIns = $conn->prepare("INSERT INTO tbluser (id, email, username, password, user_level_id, is_dean, is_active, is_deleted, first_login, otp_enabled) VALUES (?, ?, ?, ?, 1, 0, 1, 0, 1, 1)");
        $uIns->bind_param('ssss', $uid, $email, $username, $hashed);
        $uIns->execute();
        $uIns->close();
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Admin row was created, but login row could not be created because email/username already exists. Use unique credentials.',
            'id'      => $uuid
        ]);
        exit;
    }

    echo json_encode(['status' => 'success', 'message' => 'Admin account created.', 'id' => $uuid]);
}
