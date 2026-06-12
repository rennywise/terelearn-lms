<?php
/**
 * API/facultyUI/classroom/attendance/_helpers.php
 * Shared helpers for attendance endpoints.
 */

/**
 * Resolve the logged-in tbluser.id to a tblfaculty.id via username.
 * Returns faculty_id (CHAR(36)) or null.
 */
function att_resolve_faculty_id(mysqli $conn, string $user_id): ?string {
    $u = $conn->real_escape_string($user_id);
    $r = $conn->query("SELECT username FROM tbluser WHERE id='$u' AND is_deleted=0 LIMIT 1");
    if (!$r || $r->num_rows === 0) return null;
    $username = $conn->real_escape_string($r->fetch_assoc()['username']);
    $f = $conn->query("SELECT id FROM tblfaculty WHERE username='$username' AND is_deleted=0 LIMIT 1");
    if (!$f || $f->num_rows === 0) return null;
    return $f->fetch_assoc()['id'];
}

/**
 * Verify a faculty owns a class. Returns class row or null.
 */
function att_verify_class_owner(mysqli $conn, string $class_id, string $faculty_id): ?array {
    $c = $conn->real_escape_string($class_id);
    $f = $conn->real_escape_string($faculty_id);
    $r = $conn->query("
        SELECT id, class_code, semester_setting_id, faculty_id
        FROM   tblclass
        WHERE  id='$c' AND faculty_id='$f' AND is_deleted=0
        LIMIT  1
    ");
    if (!$r || $r->num_rows === 0) return null;
    return $r->fetch_assoc();
}

/**
 * Fetch semester bounds for a class. Falls back to active semester if class has no semester_setting_id.
 * Returns ['id'=>int, 'semester'=>str, 'school_year'=>str, 'start_date'=>'Y-m-d', 'end_date'=>'Y-m-d'] or null.
 */
function att_class_semester(mysqli $conn, ?int $semester_setting_id): ?array {
    if ($semester_setting_id) {
        $sid = (int)$semester_setting_id;
        $r = $conn->query("SELECT id, semester, school_year, start_date, end_date FROM tblsemestersetting WHERE id=$sid AND is_deleted=0 LIMIT 1");
        if ($r && $r->num_rows > 0) return $r->fetch_assoc();
    }
    // Fallback: active semester
    $r = $conn->query("SELECT id, semester, school_year, start_date, end_date FROM tblsemestersetting WHERE is_active=1 AND is_deleted=0 ORDER BY id DESC LIMIT 1");
    if ($r && $r->num_rows > 0) return $r->fetch_assoc();
    return null;
}

/**
 * Validate a date string is within semester bounds AND not in the future.
 */
function att_date_allowed(string $date, array $sem): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
    $today = date('Y-m-d');
    if ($date > $today) return false;
    if (!empty($sem['start_date']) && $date < $sem['start_date']) return false;
    if (!empty($sem['end_date'])   && $date > $sem['end_date'])   return false;
    return true;
}

/** Generate UUID v4 */
function att_uuid(): string {
    $d = random_bytes(16);
    $d[6] = chr(ord($d[6]) & 0x0f | 0x40);
    $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

/** Insert audit trail row. */
function att_audit(mysqli $conn, string $user_id, string $action): void {
    $id  = att_uuid();
    $u   = $conn->real_escape_string($user_id);
    $a   = $conn->real_escape_string($action);
    $conn->query("INSERT INTO tblaudittrail (id, user_id, action) VALUES ('$id','$u','$a')");
}