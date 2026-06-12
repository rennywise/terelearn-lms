<?php
/**
 * API/Faculty/fetch_faculty.php
 * Original logic preserved exactly.
 * Only the HTML output is enhanced with CSS helper classes
 * that are defined in faculty.php's <head>.
 */
require_once __DIR__ . '/../../core/db_connect.php';

/* ── Auto-migrate otp_enabled if missing ── */
$colCheck = $conn->query("
    SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'tbluser'
      AND COLUMN_NAME  = 'otp_enabled'
    LIMIT 1
");
if ($colCheck && $colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE tbluser ADD COLUMN otp_enabled TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=OTP required,0=skip OTP'");
}

$sql = "
    SELECT
        f.id            AS faculty_id,
        f.faculty_number,
        f.first_name, f.middle_name, f.last_name,
        f.email         AS faculty_email,
        f.phone, f.birthdate, f.is_active, f.is_dean,
        f.username      AS faculty_username,
        u.id            AS user_id,
        IFNULL(u.otp_enabled, 1) AS otp_enabled,
        IFNULL(u.first_login, 1) AS first_login
    FROM tblfaculty f
    LEFT JOIN tbluser u
        ON (u.username = f.username OR u.email = f.email)
       AND u.is_deleted = 0
    WHERE f.is_deleted = 0
    ORDER BY f.last_name, f.first_name
";

$result = $conn->query($sql);
$rows   = [];
if ($result) {
    while ($r = $result->fetch_assoc()) $rows[] = $r;
}
$conn->close();

if (empty($rows)) {
    echo '<tr><td colspan="9" class="text-center text-muted py-3">No faculty records found.</td></tr>';
    exit;
}

foreach ($rows as $i => $f) {
    /* ── name (original logic) ── */
    $fullName  = trim($f['first_name'] . ' ' . ($f['middle_name'] ? $f['middle_name'] . ' ' : '') . $f['last_name']);

    /* ── status (enhanced pill instead of plain badge) ── */
    $status = (int)$f['is_active'] === 1
        ? '<span class="pill-active">● Active</span>'
        : '<span class="pill-inactive">● Inactive</span>';

    /* ── dean badge (original) ── */
    $dean = (int)$f['is_dean'] === 1
        ? ' <span class="badge bg-warning text-dark ms-1" style="font-size:.68rem;">Dean</span>'
        : '';

    /* ── birthdate (original) ── */
    $birthdate = $f['birthdate'] ? date('M d, Y', strtotime($f['birthdate'])) : '—';

    /* ── OTP toggle (original logic, same class names as before) ── */
    $userId     = $f['user_id']     ?? '';
    $otpEnabled = $f['otp_enabled'] ?? 1;
    $firstLogin = $f['first_login'] ?? 1;

    $locked     = (int)$firstLogin  === 1;
    $enabled    = (int)$otpEnabled  === 1;
    $lockClass  = $locked  ? 'locked'     : '';
    $labelClass = $locked  ? 'locked-msg' : ($enabled ? 'on' : 'off');
    $labelText  = $locked  ? '⚠ Pending'  : ($enabled ? 'OTP On' : 'OTP Off');
    $checked    = $enabled ? 'checked'    : '';
    $disabled   = ($locked || !$userId) ? 'disabled' : '';
    $title      = $locked
        ? 'Cannot toggle: account has not completed first login'
        : 'Toggle email OTP for this faculty';

    $otpToggle = $userId ? "
        <label class='otp-toggle-wrap {$lockClass}' title='{$title}'>
          <div class='otp-switch'>
            <input type='checkbox' {$checked} {$disabled}
                   onchange=\"toggleOtpAuth('{$userId}', this)\">
            <span class='otp-slider'></span>
          </div>
          <span class='otp-label {$labelClass}' id='otp-label-{$userId}'>{$labelText}</span>
        </label>"
        : '<span class="text-muted small">No user account</span>';

    /* ── row output — enhanced cell classes for readability ── */
    echo "
    <tr>
      <td class='text-center' style='color:#9ca3af;font-size:.8rem;'>" . ($i + 1) . "</td>
      <td><span class='td-id'>" . htmlspecialchars($f['faculty_number']) . "</span></td>
      <td>
        <span class='td-name'>" . htmlspecialchars($fullName) . "</span>{$dean}
      </td>
      <td><span class='td-email'>" . htmlspecialchars($f['faculty_email'] ?? '—') . "</span></td>
      <td class='td-phone'>" . htmlspecialchars($f['phone'] ?? '—') . "</td>
      <td class='td-date'>{$birthdate}</td>
      <td class='text-center'>{$status}</td>
      <td class='text-center'>{$otpToggle}</td>
      <td class='text-center'>
        <div class='act-group'>
          <button class='btn btn-sm btn-warning' onclick=\"editFaculty('" . htmlspecialchars($f['faculty_number']) . "')\" title='Edit'>
            <i class='fas fa-edit'></i>
          </button>
          <button class='btn btn-sm btn-danger' onclick=\"deleteFaculty('" . htmlspecialchars($f['faculty_id']) . "')\" title='Delete'>
            <i class='fas fa-trash-alt'></i>
          </button>
          <button class='btn btn-sm btn-secondary' onclick=\"toggleFacultyStatus('" . htmlspecialchars($f['faculty_number']) . "')\" title='Toggle Status'>
            <i class='fas fa-power-off'></i>
          </button>
        </div>
      </td>
    </tr>";
}
