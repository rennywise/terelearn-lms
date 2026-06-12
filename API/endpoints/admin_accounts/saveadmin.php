<?php
header('Content-Type: application/json');

// Start output buffering to catch any unexpected output
ob_start();

try {
    // Check if db_connect exists
    if (!file_exists(__DIR__ . '/../../core/db_connect.php')) {
        throw new Exception('db_connect.php file not found');
    }
    
    require_once __DIR__ . '/../../core/db_connect.php';
    
    // Check if connection exists
    if (!isset($conn) || $conn === null) {
        throw new Exception('Database connection failed - $conn is null');
    }

    /* ---------- incoming ---------- */
    $email          = trim($_POST['email']          ?? '');
    $username       = trim($_POST['username']       ?? '');
    $password_raw   = $_POST['password']            ?? '';
    $user_level_id  = (int)($_POST['user_level_id'] ?? 4);

    $first_name     = trim($_POST['first_name']  ?? '');
    $middle_name    = trim($_POST['middle_name'] ?? '');
    $last_name      = trim($_POST['last_name']   ?? '');
    $phone          = trim($_POST['phone']       ?? '');
    $birthdate      = trim($_POST['birthdate']   ?? '');
    $is_superadmin  = (int)($_POST['is_superadmin'] ?? 0);

    /* ---------- validation ---------- */
    if (empty($email) || empty($username) || empty($first_name) || empty($last_name)) {
        throw new Exception('Email, username, first name, and last name are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (strlen($username) < 4) {
        throw new Exception('Username must be at least 4 characters long');
    }

    if (!in_array($user_level_id, [1, 4], true)) {
        throw new Exception('Invalid user level');
    }

    /* ---------- default password generation ---------- */
    if (empty($password_raw)) {
        if (empty($birthdate)) {
            throw new Exception('Birthdate is required for auto-generating password');
        }

        $birthdate_parts = explode('-', $birthdate);
        if (count($birthdate_parts) !== 3) {
            throw new Exception('Invalid birthdate format. Expected YYYY-MM-DD');
        }

        $year = $birthdate_parts[0];
        $month = $birthdate_parts[1];
        $day = $birthdate_parts[2];

        $first_initial = strtolower(substr($first_name, 0, 1));
        $last_initial = strtoupper(substr($last_name, 0, 1));
        $birthdate_mmddyyyy = $month . $day . $year;

        $password_raw = $first_initial . $birthdate_mmddyyyy . $last_initial;
    }

    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    /* ---------- duplicate checks ---------- */
    $chk = $conn->prepare("SELECT id FROM tbluser WHERE email = ? AND is_deleted = 0");
    if (!$chk) {
        throw new Exception('Prepare failed (email check): ' . $conn->error);
    }
    $chk->bind_param('s', $email);
    $chk->execute();
    if ($chk->get_result()->num_rows) {
        throw new Exception('Email already exists');
    }
    $chk->close();

    $chk = $conn->prepare("SELECT id FROM tbluser WHERE username = ? AND is_deleted = 0");
    if (!$chk) {
        throw new Exception('Prepare failed (username check): ' . $conn->error);
    }
    $chk->bind_param('s', $username);
    $chk->execute();
    if ($chk->get_result()->num_rows) {
        throw new Exception('Username already exists');
    }
    $chk->close();

    /* ---------- UUIDs ---------- */
    $user_id  = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    $admin_id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));

    $admin_number = 'ADM-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

    /* ---------- transaction ---------- */
    $conn->begin_transaction();
    try {
        /* tbluser */
        $sql = "INSERT INTO tbluser (id, email, username, password, user_level_id, is_active, is_deleted, first_login)
                VALUES (?, ?, ?, ?, ?, 1, 0, 1)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed (tbluser insert): ' . $conn->error);
        }
        $stmt->bind_param('ssssi', $user_id, $email, $username, $password, $user_level_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed (tbluser insert): ' . $stmt->error);
        }
        $stmt->close();

        /* tbladmin */
        $user_type_id = 2;
        $sql = "INSERT INTO tbladmin
                (id, admin_number, first_name, middle_name, last_name, user_type_id,
                 email, phone, birthdate, username, password, is_superadmin, is_active, is_deleted)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed (tbladmin insert): ' . $conn->error);
        }
        $stmt->bind_param('sssssissssii',
            $admin_id, $admin_number, $first_name, $middle_name, $last_name,
            $user_type_id, $email, $phone, $birthdate, $username, $password,
            $is_superadmin);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed (tbladmin insert): ' . $stmt->error);
        }
        $stmt->close();

        $conn->commit();

        ob_end_clean();
        $label = $user_level_id === 1 ? 'Admin' : 'Sub-Admin';
        echo json_encode([
            'status'  => 'success',
            'message' => "$label $first_name $last_name created successfully!",
            'user_id'     => $user_id,
            'admin_id'    => $admin_id,
            'admin_number'=> $admin_number
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
