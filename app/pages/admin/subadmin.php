<?php
/**
 * subadmin.php — Tere LEARN | Dean / Secretary Dashboard
 * UI redesigned to match facultyUI.php design system
 * + role-switcher toggle for Dean/Secretary who are also Faculty
 */
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php');
    exit;
}
require_once dirname(__DIR__, 2) . '/config/db_connect.php';

$userId     = $_SESSION['user_id'];
$admin      = null;
$assignment = null;
$deptId     = null;
$programs   = [];

$uid  = mysqli_real_escape_string($conn, $userId);
$uRes = $conn->query  ("SELECT email, username FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
$uRow = $uRes ? $uRes->fetch_assoc() : null;

if ($uRow) {
    $un = mysqli_real_escape_string($conn, $uRow['username']);
    $em = mysqli_real_escape_string($conn, $uRow['email']);
    $r  = $conn->query("SELECT * FROM tbladmin WHERE username='$un' AND is_deleted=0 LIMIT 1");
    if ($r && $r->num_rows) {
        $admin = $r->fetch_assoc();
    } else {
        $r2 = $conn->query("SELECT * FROM tbladmin WHERE email='$em' AND is_deleted=0 LIMIT 1");
        if ($r2 && $r2->num_rows) $admin = $r2->fetch_assoc();
    }
}

if ($admin) {
    $aid  = mysqli_real_escape_string($conn, $admin['id']);
    $aRes = $conn->query("
        SELECT da.*, d.dept_code, d.dept_name
        FROM   tbldeanassignment da
        JOIN   tbldepartment d ON d.id = da.department_id
        WHERE  da.faculty_id = '$aid' AND da.is_active = 1
        ORDER  BY da.assigned_at DESC LIMIT 1");
    $assignment = $aRes ? $aRes->fetch_assoc() : null;
    if ($assignment) {
        $deptId = (int)$assignment['department_id'];
        $pRes   = $conn->query("SELECT id, course_code, course_name FROM tblcourse WHERE department_id=$deptId AND is_deleted=0 ORDER BY course_code");
        if ($pRes) while ($r = $pRes->fetch_assoc()) $programs[] = $r;
    }
}

// ── CHECK if this dean/secretary is ALSO a Faculty (tbluser level_id=2) ──
$isAlsoFaculty = false;
if ($uRow) {
    $un2 = mysqli_real_escape_string($conn, $uRow['username']);
    $em2 = mysqli_real_escape_string($conn, $uRow['email']);
    $fRes = $conn->query("SELECT id FROM tbluser WHERE (username='$un2' OR email='$em2') AND user_level_id=2 AND is_deleted=0 LIMIT 1");
    if ($fRes && $fRes->num_rows > 0) {
        $isAlsoFaculty = true;
    }
}

$allPrograms    = [];
$courseOptsAll  = '';
$courseOptsDept = '';
$allCRes = $conn->query("SELECT id,course_code,course_name FROM tblcourse WHERE is_deleted=0 ORDER BY course_code");
if ($allCRes) while ($r = $allCRes->fetch_assoc()) {
    $allPrograms[] = $r;
    $courseOptsAll .= "<option value='{$r['id']}'>{$r['course_code']} - {$r['course_name']}</option>";
}
foreach ($programs as $p)
    $courseOptsDept .= "<option value='{$p['id']}'>{$p['course_code']} - {$p['course_name']}</option>";

$stats = ['subjects' => 0, 'classes' => 0, 'students' => 0, 'faculty' => 0];
if ($deptId && $programs) {
    $in = implode(',', array_map('intval', array_column($programs, 'id')));
    $r  = $conn->query("SELECT COUNT(*) c FROM tblsubject WHERE course_id IN($in) AND is_deleted=0");
    if ($r) $stats['subjects'] = (int)$r->fetch_assoc()['c'];
    $r  = $conn->query("SELECT COUNT(*) c FROM tblclass WHERE course_id IN($in) AND is_deleted=0");
    if ($r) $stats['classes']  = (int)$r->fetch_assoc()['c'];
    $r  = $conn->query("SELECT COUNT(DISTINCT cs.student_id) c FROM tblclassstudents cs JOIN tblclass cl ON cl.id=cs.class_id WHERE cl.course_id IN($in) AND cl.is_deleted=0 AND cs.is_deleted=0");
    if ($r) $stats['students'] = (int)$r->fetch_assoc()['c'];
}
$aidEsc = $admin ? mysqli_real_escape_string($conn, (string)$admin['id']) : '';
$aemEsc = $admin ? mysqli_real_escape_string($conn, (string)($admin['email'] ?? '')) : '';
$aunEsc = $admin ? mysqli_real_escape_string($conn, (string)($admin['username'] ?? '')) : '';
$r  = $conn->query("
    SELECT COUNT(DISTINCT f.id) c
    FROM tblfaculty f
    WHERE f.is_deleted=0
      AND f.is_active=1
      AND (
          (f.id = '$aidEsc')
          OR ('$aemEsc' <> '' AND f.email = '$aemEsc')
          OR ('$aunEsc' <> '' AND f.username = '$aunEsc')
          OR f.is_dean = 0
          OR EXISTS (
              SELECT 1
              FROM tbldeanassignment da
              JOIN tbladmin a ON a.id = da.faculty_id AND a.is_deleted = 0
              WHERE da.is_active = 1
                AND da.department_id = " . (int)$deptId . "
                AND (
                    a.id = f.id
                    OR (a.email IS NOT NULL AND a.email <> '' AND a.email = f.email)
                    OR (a.username IS NOT NULL AND a.username <> '' AND a.username = f.username)
                )
          )
      )
");
if ($r) $stats['faculty'] = (int)$r->fetch_assoc()['c'];
$conn->close();

$fullName  = trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '')) ?: 'Dean';
$initials  = strtoupper(substr($admin['first_name'] ?? 'D', 0, 1) . substr($admin['last_name'] ?? '', 0, 1));
$roleLbl   = ucfirst($assignment['role'] ?? 'Dean');
$deptCode  = $assignment['dept_code'] ?? '—';
$deptName  = $assignment['dept_name'] ?? 'No Department Assigned';
$hName     = htmlspecialchars($fullName);
$hInit     = htmlspecialchars($initials);
$hRole     = htmlspecialchars($roleLbl);
$hDeptCode = htmlspecialchars($deptCode);
$hDeptName = htmlspecialchars($deptName);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TERELEARN — Dean Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

  <style>
    /* ══ EXACT SAME DESIGN TOKENS AS facultyUI.php ══ */
    :root {
      --primary:        #1a9e78;
      --primary-dark:   #0d7a5e;
      --primary-light:  #e6f7f2;
      --primary-mid:    rgba(26,158,120,.15);
      --accent:         #1f73db;
      --accent-light:   #e8f0fe;
      --danger:         #d93025;
      --warning:        #f57c00;
      --border:         #e8eaed;
      --text:           #1c2027;
      --text-muted:     #5f6368;
      --bg:             #f4f6f9;
      --surface:        #ffffff;
      --sidebar-w:      265px;
      --nav-h:          64px;
      --footer-h:       46px;
      --radius:         14px;
      --radius-sm:      8px;
      --shadow:         0 2px 12px rgba(0,0,0,.07);
      --shadow-md:      0 4px 20px rgba(0,0,0,.10);
      --shadow-lg:      0 10px 40px rgba(0,0,0,.14);
      --transition:     .22s cubic-bezier(.4,0,.2,1);
    }
    body.dark {
      --primary:        #2ecc9a;
      --primary-dark:   #1a9e78;
      --primary-light:  rgba(46,204,154,.12);
      --primary-mid:    rgba(46,204,154,.10);
      --accent:         #4d90e2;
      --accent-light:   rgba(77,144,226,.14);
      --border:         #2e3849;
      --text:           #e4ecf7;
      --text-muted:     #8a9ab5;
      --bg:             #0f1724;
      --surface:        #182030;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg); color: var(--text);
      transition: background var(--transition), color var(--transition);
      overflow-x: hidden;
    }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* ── TOP NAV ── */
    .topnav {
      position: fixed; inset: 0 0 auto 0; height: var(--nav-h);
      background: var(--surface); border-bottom: 1px solid var(--border);
      display: flex; align-items: center; padding: 0 1.5rem; gap: .75rem;
      z-index: 200; transition: background var(--transition), border-color var(--transition);
    }
    .nav-brand { display: flex; align-items: center; gap: .6rem; font-size: 1.15rem; font-weight: 700; color: var(--text); text-decoration: none; white-space: nowrap; }
    .brand-logo { width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem; box-shadow: 0 2px 8px rgba(26,158,120,.35); }
    .nav-actions { margin-left: auto; display: flex; align-items: center; gap: .4rem; }
    .nav-dept-badge { display: inline-flex; align-items: center; gap: .35rem; background: var(--primary-light); color: var(--primary); border: 1.5px solid var(--primary); border-radius: 20px; padding: .22rem .75rem; font-size: .7rem; font-weight: 700; white-space: nowrap; }
    .nav-role-badge { display: inline-flex; align-items: center; gap: .35rem; background: #fff3e0; color: var(--warning); border: 1.5px solid #ffd180; border-radius: 20px; padding: .22rem .75rem; font-size: .7rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
    body.dark .nav-dept-badge { background: var(--primary-light); border-color: var(--primary); }
    body.dark .nav-role-badge { background: rgba(245,124,0,.12); border-color: rgba(245,124,0,.4); color: #ffb74d; }
    .icon-btn { width: 38px; height: 38px; border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: 1rem; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all var(--transition); }
    .icon-btn:hover { background: var(--border); color: var(--text); }
    .nav-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .85rem; cursor: pointer; border: 2px solid transparent; transition: border-color var(--transition); }
    .nav-avatar:hover { border-color: var(--primary); }
    .menu-btn { width: 38px; height: 38px; border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: 1.1rem; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all var(--transition); }
    .menu-btn:hover { background: var(--border); color: var(--text); }

    /* ── ROLE SWITCHER (Dean → Faculty) ── */
    .role-switcher {
      display: inline-flex; align-items: center; gap: .55rem;
      background: linear-gradient(135deg, #e6f7f2, #d0f0e6);
      border: 1.5px solid var(--primary); border-radius: 20px;
      padding: .28rem .85rem .28rem .55rem;
      cursor: pointer; transition: all var(--transition);
      white-space: nowrap; user-select: none;
    }
    .role-switcher:hover {
      background: linear-gradient(135deg, #d0f0e6, #b8e8d8);
      border-color: var(--primary-dark); box-shadow: 0 2px 10px rgba(26,158,120,.2);
    }
    body.dark .role-switcher {
      background: rgba(26,158,120,.1); border-color: rgba(46,204,154,.4);
    }
    body.dark .role-switcher:hover { background: rgba(26,158,120,.18); }
    .role-switcher-track {
      width: 34px; height: 18px; background: var(--primary-light); border-radius: 18px;
      position: relative; flex-shrink: 0; transition: background var(--transition);
    }
    .role-switcher-track::after {
      content: ''; position: absolute;
      width: 13px; height: 13px; border-radius: 50%;
      background: var(--primary); top: 2.5px; right: 3px;
      /* knob on the RIGHT = currently in "Dean" mode */
      transition: transform var(--transition); box-shadow: 0 1px 4px rgba(0,0,0,.2);
    }
    .role-switcher-label {
      font-size: .7rem; font-weight: 700; color: var(--primary); letter-spacing: .3px;
      display: flex; align-items: center; gap: .3rem;
    }
    body.dark .role-switcher-label { color: #2ecc9a; }

    /* Sidebar role switcher */
    .sidebar-role-switcher {
      margin: .5rem .75rem 0;
      background: linear-gradient(135deg, #e6f7f2, #d0f0e6);
      border: 1.5px solid var(--primary); border-radius: 10px;
      padding: .7rem 1rem; display: flex; align-items: center; gap: .75rem;
      cursor: pointer; transition: all var(--transition);
    }
    .sidebar-role-switcher:hover {
      background: linear-gradient(135deg, #d0f0e6, #b8e8d8);
      box-shadow: 0 2px 10px rgba(26,158,120,.15);
    }
    body.dark .sidebar-role-switcher {
      background: rgba(26,158,120,.08); border-color: rgba(46,204,154,.35);
    }
    body.dark .sidebar-role-switcher:hover { background: rgba(26,158,120,.15); }
    .srs-icon { width: 32px; height: 32px; background: var(--primary-light); color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0; }
    body.dark .srs-icon { background: rgba(26,158,120,.2); }
    .srs-text { flex: 1; overflow: hidden; }
    .srs-label { font-size: .78rem; font-weight: 700; color: var(--primary); white-space: nowrap; }
    body.dark .srs-label { color: #2ecc9a; }
    .srs-sub { font-size: .67rem; color: var(--text-muted); margin-top: .07rem; white-space: nowrap; }
    .srs-arrow { color: var(--primary); font-size: .75rem; flex-shrink: 0; }
    body.dark .srs-arrow { color: #2ecc9a; }
    .sidebar.collapsed .srs-text, .sidebar.collapsed .srs-arrow { display: none; }
    .sidebar.collapsed .sidebar-role-switcher { justify-content: center; padding: .6rem; margin: .5rem .4rem 0; }
    .sidebar.collapsed .srs-icon { width: 36px; height: 36px; }

    /* ── SIDEBAR ── */
    .sidebar {
      position: fixed; top: var(--nav-h); left: 0; bottom: var(--footer-h);
      width: var(--sidebar-w); background: var(--surface);
      border-right: 1px solid var(--border); padding: 1rem 0 1rem;
      overflow-y: auto; overflow-x: hidden; z-index: 150;
      transition: transform var(--transition), width var(--transition), background var(--transition), border-color var(--transition);
    }
    .sidebar.collapsed { width: 70px; }
    @media (max-width: 768px) { .sidebar { transform: translateX(-100%); width: var(--sidebar-w) !important; } .sidebar.open { transform: translateX(0); } }
    .sidebar-user { display: flex; align-items: center; gap: .8rem; padding: .85rem 1.2rem 1rem; border-bottom: 1px solid var(--border); margin-bottom: .5rem; overflow: hidden; }
    .s-avatar { width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 1rem; flex-shrink: 0; }
    .s-name { font-weight: 600; font-size: .88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .s-role { font-size: .72rem; color: var(--text-muted); }
    .sidebar.collapsed .s-name, .sidebar.collapsed .s-role { display: none; }
    .nav-section-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: var(--text-muted); padding: .7rem 1.2rem .3rem; white-space: nowrap; overflow: hidden; transition: opacity var(--transition); }
    .sidebar.collapsed .nav-section-label { opacity: 0; }
    .nav-item { display: flex; align-items: center; gap: .85rem; padding: .6rem 1.2rem; color: var(--text-muted); text-decoration: none; border: none; background: none; width: 100%; font-size: .88rem; font-weight: 500; font-family: inherit; border-left: 3px solid transparent; cursor: pointer; white-space: nowrap; overflow: hidden; transition: all var(--transition); }
    .nav-item i { width: 18px; text-align: center; font-size: .95rem; flex-shrink: 0; }
    .nav-item:hover { background: var(--primary-light); color: var(--primary); }
    .nav-item.active { background: var(--primary-light); color: var(--primary); border-left-color: var(--primary); font-weight: 600; }
    .sidebar.collapsed .nav-item { justify-content: center; padding: .6rem; }
    .sidebar.collapsed .nav-item span { display: none; }
    .nav-badge { margin-left: auto; background: var(--primary); color: #fff; font-size: .65rem; font-weight: 700; padding: .1rem .45rem; border-radius: 20px; flex-shrink: 0; }
    .sidebar.collapsed .nav-badge { display: none; }
    .sidebar-footer-inner { padding: .75rem; border-top: 1px solid var(--border); margin-top: auto; }
    .signout-btn { display: flex; align-items: center; gap: .85rem; padding: .55rem 1rem; border-radius: var(--radius-sm); color: var(--danger); text-decoration: none; font-size: .88rem; font-weight: 600; transition: background var(--transition); width: 100%; border: none; background: none; cursor: pointer; font-family: inherit; white-space: nowrap; overflow: hidden; }
    .signout-btn i { width: 18px; text-align: center; flex-shrink: 0; }
    .signout-btn:hover { background: #fdecea; }
    .sidebar.collapsed .signout-btn { justify-content: center; padding: .55rem; }
    .sidebar.collapsed .signout-btn span { display: none; }

    /* ── OVERLAY ── */
    .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 140; }
    .overlay.show { display: block; }

    /* ── MAIN ── */
    .main {
      margin-left: var(--sidebar-w); margin-top: var(--nav-h);
      padding: 2rem 2rem calc(var(--footer-h) + 1.5rem);
      min-height: calc(100vh - var(--nav-h));
      transition: margin-left var(--transition);
    }
    .main.collapsed { margin-left: 70px; }
    @media (max-width: 768px) { .main { margin-left: 0; padding: 1rem 1rem calc(var(--footer-h) + 1rem); } }

    /* ── FOOTER ── */
    .page-footer {
      position: fixed; bottom: 0; left: 0; right: 0;
      height: var(--footer-h);
      background: var(--surface); border-top: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 1.5rem; z-index: 190;
      font-size: .78rem; color: var(--text-muted);
      transition: background var(--transition), border-color var(--transition);
    }
    .footer-dept-badge { display: inline-flex; align-items: center; gap: .45rem; background: var(--primary-light); color: var(--primary); border: 1.5px solid var(--primary); border-radius: 8px; padding: .22rem .8rem; font-weight: 700; font-size: .72rem; letter-spacing: .3px; white-space: nowrap; }
    .footer-dept-badge i { font-size: .65rem; opacity: .8; }

    /* ── PAGES ── */
    .page { display: none; animation: fadeUp .28s ease; }
    .page.active { display: block; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .page-header { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; }
    .page-title { font-size: 1.65rem; font-weight: 700; }
    .page-subtitle { color: var(--text-muted); font-size: .88rem; margin-top: .2rem; }

    /* ── DEPT BANNER ── */
    .dept-banner {
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
      border-radius: var(--radius); padding: 1.25rem 1.5rem;
      color: #fff; margin-bottom: 1.75rem;
      display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .8rem;
      box-shadow: 0 4px 20px rgba(26,158,120,.25);
    }
    .dept-banner-lbl { font-size: .63rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; opacity: .75; }
    .dept-banner-title { font-size: 1.2rem; font-weight: 700; margin-top: .12rem; }
    .dept-progs { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .5rem; }
    .prog-pill { background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.22); border-radius: 20px; padding: .13rem .55rem; font-size: .7rem; font-weight: 600; }
    .dept-role-chip { background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.22); border-radius: 8px; padding: .3rem .85rem; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }

    /* ── STATS ── */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.2rem 1.4rem; box-shadow: var(--shadow); display: flex; align-items: center; gap: .9rem; transition: transform var(--transition), box-shadow var(--transition); }
    .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0; }
    .si-green  { background: var(--primary-light); color: var(--primary); }
    .si-blue   { background: var(--accent-light);  color: var(--accent); }
    .si-orange { background: #fff3e0; color: var(--warning); }
    .si-purple { background: #ede9fe; color: #7c3aed; }
    .stat-val  { font-size: 1.85rem; font-weight: 700; line-height: 1; }
    .stat-lbl  { font-size: .73rem; color: var(--text-muted); margin-top: .18rem; }

    /* ── SECTION DIVIDER ── */
    .section-divider { display: flex; align-items: center; gap: .75rem; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .9px; color: var(--text-muted); margin: 1.75rem 0 1rem; }
    .section-divider .sd-icon { width: 28px; height: 28px; border-radius: 8px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: .8rem; }
    .section-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

    /* ── CARD / TABLE WRAPPER ── */
    .card-box { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; margin-bottom: 1.25rem; }
    .card-box-header { display: flex; align-items: center; gap: .75rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); flex-wrap: wrap; }
    .card-box-body { padding: 0; overflow-x: auto; }
    /* Year & Sections modal form */
    .ys-modal-grid {
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:.75rem;
      align-items:end;
    }

    /* ── SEARCH ── */
    .search-wrap { position: relative; flex: 1; min-width: 200px; }
    .search-wrap .search-icon { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: .8rem; pointer-events: none; z-index: 4; }
    .search-input { width: 100%; padding: .5rem .85rem .5rem 2.4rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: .88rem; font-family: inherit; background: var(--surface); color: var(--text); transition: border-color var(--transition), box-shadow var(--transition); }
    .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-mid); outline: none; }
    .search-clear { position: absolute; right: .65rem; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; border: none; background: var(--border); border-radius: 50%; cursor: pointer; color: var(--text-muted); font-size: .65rem; display: none; align-items: center; justify-content: center; transition: background var(--transition), color var(--transition); z-index: 4; }
    .search-clear.visible { display: flex; }
    .search-clear:hover { background: var(--primary); color: #fff; }
    .result-count { font-size: .75rem; color: var(--text-muted); white-space: nowrap; display: flex; align-items: center; gap: .3rem; }
    .result-count strong { color: var(--primary); }

    /* ── TABLE ── */
    .tl-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
    .tl-th { background: #1c2a3a; color: #e4ecf7; font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; padding: .55rem .75rem; white-space: nowrap; vertical-align: middle; border: none; }
    .tl-th:first-child { border-radius: 8px 0 0 0; }
    .tl-th:last-child  { border-radius: 0 8px 0 0; }
    .tl-td { padding: .55rem .75rem; vertical-align: middle; border-bottom: 1px solid var(--border); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text); }
    #stuTbl .tl-th,
    #stuTbl .tl-td {
      text-align: left !important;
    }
    #stuTbl .select-col,
    #stuTbl .tl-th:last-child,
    #stuTbl .tl-td:last-child {
      text-align: center !important;
    }
    #stuTbl .name-with-avatar {
      justify-content: flex-start;
    }

    /* ── ADMIN-STYLE TOOLBAR + TABLE WRAP ── */
    .acct-toolbar { display:flex; align-items:center; gap:.55rem; width:100%; margin-bottom:.85rem; }
    .acct-toolbar .search-wrap { flex:1; min-width:0; }
    .tl-ctrl-wrap { position:relative; flex-shrink:0; z-index:120; }
    .tl-ctrl-menu {
      position:absolute; top:calc(100% + 8px); left:0; min-width:180px;
      background:var(--surface); border:1.5px solid var(--border); border-radius:12px;
      box-shadow:0 8px 22px rgba(15,23,36,.12); z-index:250; overflow:hidden;
      display:block; opacity:0; pointer-events:none;
      transform:translateY(-4px);
      transform-origin:top left;
      transition:transform .24s cubic-bezier(.4,0,.2,1), opacity .22s ease, box-shadow .22s ease;
    }
    .tl-ctrl-menu.open { opacity:1; pointer-events:auto; transform:translateY(0); box-shadow:0 12px 28px rgba(15,23,36,.16); }
    .tl-ctrl-item {
      width:100%; border:none; background:none; text-align:left; cursor:pointer;
      display:flex; align-items:center; gap:.55rem; padding:.6rem .85rem;
      font-size:.8rem; font-weight:600; color:var(--text); font-family:inherit;
      transition:background .18s cubic-bezier(.4,0,.2,1), color .18s cubic-bezier(.4,0,.2,1);
    }
    .tl-ctrl-item i { width:14px; text-align:center; opacity:.72; }
    .tl-ctrl-item + .tl-ctrl-item { border-top:1px solid var(--border); }
    .tl-ctrl-item:hover { background:var(--primary-light); color:var(--primary); }
    .tl-ctrl-item:hover i { opacity:1; }
    .tl-select-cancel {
      display:none; flex-shrink:0; border-radius:10px; border:1.5px solid var(--border);
      background:var(--surface); color:var(--text-muted); font-size:.8rem; font-weight:700;
      padding:.52rem .85rem; cursor:pointer; font-family:inherit;
    }
    .page.select-mode .tl-select-cancel { display:inline-flex; align-items:center; gap:.35rem; }
    .page .select-col { display:none; }
    .page.select-mode .select-col { display:table-cell; }
    .page:not(.select-mode) .bulk-action-bar { display:none !important; }
    /* Keep action (kebab) column visible during select mode */
    .page.select-mode .acct-table .tl-th:last-child,
    .page.select-mode .acct-table .tl-td:last-child {
      position: sticky;
      right: 0;
      z-index: 4;
      background: var(--surface);
    }
    .page.select-mode .acct-table .tl-th:last-child {
      z-index: 6;
    }
    .page.select-mode .acct-table .tl-tr:nth-child(even) .tl-td:last-child {
      background: var(--bg);
    }
    body.dark .page.select-mode .acct-table .tl-td:last-child {
      background: #232734;
    }
    body.dark .page.select-mode .acct-table .tl-tr:nth-child(even) .tl-td:last-child {
      background: rgba(255,255,255,.02);
    }
    .tl-ctrl-btn { flex-shrink:0; width:46px; height:42px; border-radius:12px; border:1.5px solid var(--border); background:var(--surface); color:var(--text-muted); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:background .22s cubic-bezier(.4,0,.2,1), color .22s cubic-bezier(.4,0,.2,1), border-color .22s cubic-bezier(.4,0,.2,1), box-shadow .22s cubic-bezier(.4,0,.2,1); font-size:.95rem; position:relative; }
    .tl-ctrl-btn:hover,
    .tl-ctrl-btn.active { border-color:var(--primary); color:var(--primary); background:var(--primary-light); box-shadow:0 4px 12px rgba(26,158,120,.10); }
    .acct-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; border-radius:var(--radius); border:1px solid var(--border); box-shadow:var(--shadow); background:var(--surface); width:100%; display:block; overflow-y:visible; position:relative; z-index:1; margin-bottom:1.25rem; }
    .tl-add-btn { flex-shrink:0; border-radius:12px; padding:.52rem 1rem; border:2px solid var(--primary); background:#fff; color:var(--primary); font-size:.88rem; font-weight:800; display:inline-flex; align-items:center; gap:.48rem; transition:all .18s; white-space:nowrap; cursor:pointer; font-family:inherit; }
    .tl-add-btn:hover { background:var(--primary); color:#fff; box-shadow:0 8px 22px rgba(26,158,120,.22); }
    @media (max-width:480px) { .tl-add-btn span { display:none; } .tl-add-btn { padding:.45rem .75rem; } }
    body.dark .tl-td { border-bottom-color: var(--border); }
    .tl-tr { transition: background var(--transition); }
    .tl-tr:hover { background: var(--primary-light) !important; }
    .tl-tr:nth-child(even) { background: var(--bg); }
    body.dark .tl-tr:nth-child(even) { background: rgba(255,255,255,.02); }
    .td-id { font-family: 'DM Mono', monospace; font-weight: 700; font-size: .78rem; color: var(--primary); letter-spacing: .3px; }
    .td-name { font-weight: 600; }
    .td-sub { font-size: .76rem; color: var(--text-muted); }
    .mini-avatar { width:28px; height:28px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:700; color:#fff; background:linear-gradient(135deg,var(--primary),var(--accent)); overflow:hidden; flex-shrink:0; }
    .mini-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
    .name-with-avatar { display:flex; align-items:center; gap:.5rem; min-width:0; }
    .table-pager {
      display:flex; align-items:center; justify-content:space-between; gap:.7rem;
      margin:-.15rem 0 1.1rem; padding:0 .15rem;
      color:var(--text-muted); font-size:.78rem;
    }
    .pager-meta { white-space:nowrap; }
    .pager-controls { display:flex; align-items:center; gap:.35rem; flex-wrap:wrap; justify-content:flex-end; }
    .pager-btn {
      min-width:30px; height:30px; border:1.5px solid var(--border); background:var(--surface); color:var(--text);
      border-radius:8px; font-size:.76rem; font-weight:700; padding:0 .55rem; cursor:pointer; font-family:inherit;
      transition:all .16s;
    }
    .pager-btn:hover:not(:disabled) { border-color:var(--primary); color:var(--primary); background:var(--primary-light); }
    .pager-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); }
    .pager-btn:disabled { opacity:.45; cursor:not-allowed; }

    /* ── PILLS ── */
    .pill { display: inline-block; padding: .18em .5em; border-radius: 20px; font-size: .68rem; font-weight: 700; }
    .pill-active   { background: var(--primary-light); color: var(--primary); border: 1px solid var(--primary); }
    .pill-inactive { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }
    body.dark .pill-inactive { background: rgba(255,255,255,.06); color: var(--text-muted); border-color: var(--border); }
    .pill-code   { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }
    .pill-year   { background: var(--accent-light); color: var(--accent); border: 1px solid #bfdbfe; }
    .pill-dean   { background: #fff3e0; color: var(--warning); border: 1px solid #ffd180; }
    .pill-secretary { background: #e8f0fe; color: #1f73db; border: 1px solid #bfdbfe; }
    .pill-faculty { background:#e6f8f2;color:#0f8f73;border:1px solid #9fe0cc; }
    .dept-chip { display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:.15rem .48rem;border-radius:999px;background:#e6f8f2;border:1px solid #b7e8d7;color:#0b7a62;font-size:.66rem;font-weight:800;line-height:1;align-self:flex-start; }
    .dept-txt { display:block;margin-top:.35rem;font-size:.84rem;line-height:1.3; }
    .dept-stack { display:flex;flex-direction:column;min-width:0; }
    #facTbl .tl-th, #facTbl .tl-td { padding:.52rem .6rem; }
    #facTbl .td-name, #facTbl .fac-dept-cell { white-space:normal; }
    #facTbl .name-with-avatar { align-items:flex-start; }
    #facTbl .fac-email { display:block; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    #facTbl .fac-dept-cell { min-width:0; }
    #facTbl .fac-dept-cell .dept-txt { font-size:.76rem; line-height:1.2; white-space:normal; overflow-wrap:anywhere; }
    @media (max-width: 1280px) {
      #facTbl .tl-th, #facTbl .tl-td { padding:.48rem .5rem; }
      #facTbl { font-size:.79rem; }
      #facTbl .fac-email { max-width:180px; }
      #facTbl .fac-dept-cell .dept-txt { font-size:.72rem; }
    }
    .acct-add-steps{display:flex;align-items:center;gap:0;padding:.2rem 0 .95rem;margin-bottom:.55rem;border-bottom:1px solid var(--border);}
    .acct-step{flex:1;display:flex;flex-direction:column;align-items:center;position:relative}
    .acct-step:not(:last-child)::after{content:'';position:absolute;top:16px;left:calc(50% + 16px);right:calc(-50% + 16px);height:2px;background:var(--border)}
    .acct-step.done:not(:last-child)::after,.acct-step.active:not(:last-child)::after{background:var(--primary)}
    .acct-step-dot{width:32px;height:32px;border-radius:50%;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:800;color:var(--text-muted);background:var(--surface);z-index:1}
    .acct-step.active .acct-step-dot{background:var(--primary);border-color:var(--primary);color:#fff}
    .acct-step.done .acct-step-dot{background:var(--primary-light);border-color:var(--primary);color:var(--primary)}
    .acct-step-label{font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.55px;color:var(--text-muted);margin-top:.38rem}
    .acct-step.active .acct-step-label,.acct-step.done .acct-step-label{color:var(--primary)}
    body.dark .pill-code { background: rgba(124,58,237,.18); color: #a78bfa; border-color: rgba(124,58,237,.3); }
    body.dark .pill-year { background: var(--accent-light); color: var(--accent); border-color: rgba(77,144,226,.3); }
    body.dark .pill-secretary { background: rgba(31,115,219,.2); color: #9fc5ff; border-color: rgba(77,144,226,.35); }

    /* ── ACTION BUTTONS ── */
    .act-group { display: flex; gap: 4px; justify-content: center; }
    .act-btn { width: 28px; height: 28px; border: none; border-radius: 7px; font-size: .75rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all var(--transition); }
    .act-btn-danger  { background: #fdecea; color: var(--danger); }
    .act-btn-success { background: var(--primary-light); color: var(--primary); }
    .act-btn-warn    { background: #fff3e0; color: var(--warning); }
    .act-btn-dark    { background: var(--bg); color: var(--text-muted); border: 1px solid var(--border); }
    .act-btn:hover { transform: scale(1.12); }
    .act-btn-danger:hover  { background: var(--danger); color: #fff; }
    .act-btn-success:hover { background: var(--primary); color: #fff; }
    .act-btn-warn:hover    { background: var(--warning); color: #fff; }
    .act-btn-dark:hover    { background: var(--text-muted); color: #fff; }

    /* ── BUTTONS ── */
    .btn { padding: .5rem 1.3rem; border-radius: var(--radius-sm); font-size: .86rem; font-weight: 600; font-family: inherit; cursor: pointer; border: none; transition: all var(--transition); display: inline-flex; align-items: center; gap: .4rem; }
    .btn-primary { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; box-shadow: 0 2px 10px rgba(26,158,120,.3); }
    .btn-primary:hover:not(:disabled) { opacity: .9; transform: translateY(-1px); }
    .btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; }
    .btn-ghost { background: var(--bg); color: var(--text); border: 1.5px solid var(--border); }
    .btn-ghost:hover { border-color: var(--primary); color: var(--primary); }

    /* ── OTP TOGGLE ── */
    .otp-toggle-wrap { display: inline-flex; align-items: center; gap: 7px; cursor: pointer; user-select: none; }
    .otp-toggle-wrap.locked { opacity: .42; cursor: not-allowed; pointer-events: none; }
    .otp-switch { position: relative; width: 38px; height: 21px; flex-shrink: 0; }
    .otp-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
    .otp-slider { position: absolute; inset: 0; background: var(--border); border-radius: 21px; transition: background .22s; cursor: pointer; }
    .otp-slider::before { content: ''; position: absolute; width: 15px; height: 15px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: transform .22s; box-shadow: 0 1px 4px rgba(0,0,0,.25); }
    .otp-switch input:checked + .otp-slider { background: var(--primary); }
    .otp-switch input:checked + .otp-slider::before { transform: translateX(17px); }
    .otp-switch input:disabled + .otp-slider { cursor: not-allowed; opacity: .6; }
    .otp-lbl { font-size: .68rem; font-weight: 700; white-space: nowrap; }
    .otp-lbl.on  { color: var(--primary); }
    .otp-lbl.off { color: var(--text-muted); }
    .otp-lbl.locked-msg { color: var(--warning); font-style: italic; font-weight: 500; }
    .course-merge-cell { display:flex; flex-direction:column; gap:.18rem; min-width:0; }
    .course-code-row { display:flex; align-items:center; gap:.42rem; flex-wrap:wrap; }
    .course-merge-cell .pill-code { width:fit-content; }
    #subjTbl { font-size: .8rem; }
    #subjTbl .pill { font-size: .67rem; font-weight: 700; }
    #subjTbl .course-title {
      font-size: .83rem;
      font-weight: 600;
      color: var(--text);
      line-height: 1.2;
      letter-spacing: 0;
    }
    #subjTbl .course-meta { display:flex; flex-wrap:wrap; align-items:center; gap:.42rem; color:var(--text-muted); font-size:.7rem; }
    #subjTbl .tl-td { font-size: .8rem; }
    .course-meta .meta-dot { width:4px; height:4px; border-radius:50%; background:var(--border); display:inline-block; }
    .course-row-clickable { cursor:pointer; }
    .course-row-clickable:hover .course-title { color:var(--primary); }
    .status-toggle-wrap { opacity:.72; pointer-events:none; }

    /* ── SKELETON / SPINNER ── */
    .skeleton { background: linear-gradient(90deg, var(--border) 25%, var(--bg) 50%, var(--border) 75%); background-size: 200% 100%; animation: shimmer 1.3s infinite; border-radius: 8px; }
    @keyframes shimmer { to { background-position: -200% 0; } }
    .spin { display: inline-block; width: 13px; height: 13px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: sp .7s linear infinite; }
    @keyframes sp { to { transform: rotate(360deg); } }

    /* ── MODAL ── */
    .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 500; align-items: center; justify-content: center; }
    .modal-backdrop.show { display: flex; }
    .modal-box { background: var(--surface); border-radius: 16px; width: 100%; max-width: 680px; max-height: 90vh; overflow: hidden; margin: 1rem; box-shadow: 0 30px 80px rgba(0,0,0,.22); animation: popIn .22s ease; display: flex; flex-direction: column; }
    .modal-box .modal-body { overflow-y: auto; flex: 1; }
    .modal-box.modal-xl { max-width: 900px; }
    @keyframes popIn { from { opacity: 0; transform: scale(.96) translateY(8px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .modal-header { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; padding: 1.35rem 1.6rem; display: flex; align-items: center; justify-content: space-between; border-radius: 16px 16px 0 0; position: sticky; top: 0; z-index: 1; }
    .modal-title { font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: .6rem; }
    .modal-title-icon { width: 30px; height: 30px; background: rgba(255,255,255,.18); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .85rem; }
    .modal-close { width: 30px; height: 30px; border: none; background: rgba(255,255,255,.18); color: #fff; border-radius: 8px; cursor: pointer; font-size: .85rem; display: flex; align-items: center; justify-content: center; transition: background var(--transition); }
    .modal-close:hover { background: rgba(255,255,255,.32); }
    .modal-body { padding: 1.4rem 1.6rem; }
    .form-section { background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 1.1rem; margin-bottom: .9rem; }
    .form-section-label { font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary); margin-bottom: .8rem; padding-bottom: .45rem; border-bottom: 2px solid var(--border); display: flex; align-items: center; gap: .4rem; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
    .form-grid.g3 { grid-template-columns: 1fr 1fr 1fr; }
    .form-grid.g1 { grid-template-columns: 1fr; }
    .form-group label { display: block; font-size: .73rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: .3rem; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: .5rem .85rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: .88rem; font-family: inherit; background: var(--surface); color: var(--text); transition: border-color var(--transition), box-shadow var(--transition); }
    .form-group input:focus, .form-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-mid); outline: none; }
    .form-group .input-prefix { display: flex; border: 1.5px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; }
    .form-group .input-prefix span { padding: .5rem .75rem; background: var(--bg); color: var(--text-muted); font-size: .82rem; font-weight: 600; border-right: 1.5px solid var(--border); white-space: nowrap; }
    .form-group .input-prefix input { border: none; border-radius: 0; box-shadow: none; }
    .form-group .input-prefix:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-mid); }
    .required { color: var(--danger); }
    .auto-btn { font-size: .7rem; font-weight: 600; color: var(--primary); background: var(--primary-light); border: none; border-radius: 6px; padding: .15rem .5rem; cursor: pointer; margin-left: .4rem; }
    .auto-btn:hover { background: var(--primary); color: #fff; }
    .sem-info-bar { background: var(--primary-light); border: 1px solid rgba(26,158,120,.2); border-radius: var(--radius-sm); padding: .5rem .85rem; font-size: .78rem; color: var(--primary); display: flex; align-items: center; gap: .5rem; margin-top: .7rem; }
    .dean-toggle { background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1.5px solid #fcd34d; border-radius: 10px; padding: .85rem 1.1rem; display: flex; align-items: center; gap: .85rem; }
    .dean-toggle input[type=checkbox] { width: 2.2em; height: 1.2em; accent-color: var(--warning); cursor: pointer; }
    .dean-toggle label { font-size: .88rem; font-weight: 600; color: #78350f; cursor: pointer; }
    .modal-footer { padding: .9rem 1.6rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: .6rem; position: sticky; bottom: 0; background: var(--surface); z-index: 1; }
    .day-pills { display: flex; flex-wrap: wrap; gap: .45rem; margin-top: .35rem; }
    .day-pill { position: relative; }
    .day-pill input { position: absolute; opacity: 0; width: 0; }
    .day-pill label { display: flex; align-items: center; justify-content: center; width: 46px; height: 36px; border-radius: var(--radius-sm); border: 1.5px solid var(--border); background: var(--surface); font-size: .75rem; font-weight: 600; color: var(--text-muted); cursor: pointer; transition: all var(--transition); }
    .day-pill input:checked + label { background: var(--primary); border-color: var(--primary); color: #fff; box-shadow: 0 2px 6px rgba(26,158,120,.3); }
    .day-pill label:hover { border-color: var(--primary); color: var(--primary); }
        /* ── 3-DOT MENU ── */
    .dot-menu-wrap { position:relative; display:inline-block; }
    .dot-menu-btn { width:28px; height:28px; border-radius:6px; border:none; background:none; cursor:pointer; color:var(--text-muted); display:flex; align-items:center; justify-content:center; font-size:.85rem; transition:all .14s; margin:0 auto; }
    .dot-menu-btn:hover { background:var(--border); color:var(--text); }
    .dot-menu-btn.open { background:var(--primary-light); color:var(--primary); }
        /* ── BULK ACTION BAR (Courses) ── */
    .bulk-action-bar { display:none; align-items:center; gap:.45rem; padding:.5rem .75rem; border-radius:11px; background:linear-gradient(135deg,var(--primary-dark),var(--primary)); color:#fff; margin-bottom:.6rem; animation:fadeUp .22s ease; }
    .bulk-action-bar.show { display:flex; }
    .bulk-count { font-size:.76rem; font-weight:700; background:rgba(255,255,255,.2); border-radius:16px; padding:.14rem .58rem; line-height:1.1; }
    .bulk-action-bar .bulk-btns { display:flex; gap:.32rem; margin-left:auto; flex-wrap:wrap; justify-content:flex-end; }
    .bulk-btn { display:inline-flex; align-items:center; gap:.25rem; padding:.28rem .58rem; border-radius:8px; border:1.5px solid rgba(255,255,255,.38); background:rgba(255,255,255,.1); color:#fff; font-size:.72rem; font-weight:700; cursor:pointer; font-family:inherit; transition:all .16s; line-height:1.05; }
    .bulk-btn:hover { background:rgba(255,255,255,.25); }
    .bulk-btn.danger:hover { background:#d93025; border-color:#d93025; }
    .bulk-btn i { font-size:.68rem; }

    /* Students bulk bar spacing tweak */
    #stuBulkBar { margin-bottom:.65rem; }
    .bulk-desel { border:none; background:none; color:rgba(255,255,255,.7); font-size:.8rem; cursor:pointer; padding:.14rem .28rem; border-radius:6px; transition:all .14s; }
    .bulk-desel:hover { background:rgba(255,255,255,.15); color:#fff; }

    /* Students bulk bar: extra compact so it doesn't consume toolbar/table space */
    #stuBulkBar { gap:.38rem; padding:.45rem .62rem; }
    #stuBulkBar .bulk-btn { padding:.24rem .5rem; font-size:.7rem; }
    #stuBulkBar .bulk-count { font-size:.74rem; }
    .subj-cb {
      appearance: none;
      -webkit-appearance: none;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      border: 2px solid #8d98ab;
      background: #fff;
      cursor: pointer;
      display: inline-block;
      vertical-align: middle;
      transition: all .14s ease;
      box-shadow: inset 0 0 0 2px #fff;
    }
    .subj-cb:hover { border-color: var(--text-muted); }
    .subj-cb:checked {
      border-color: #0b1220;
      background-color: #0b1220;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='white' d='M4.7 9.4L1.6 6.3l1-1 2.1 2.1 4.7-4.7 1 1z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: center;
      box-shadow: none;
    }
    .subj-cb:indeterminate {
      border-radius: 6px;
      border-color: var(--primary);
      background-color: var(--primary);
      background-image: linear-gradient(#fff,#fff);
      background-repeat: no-repeat;
      background-size: 9px 2px;
      background-position: center;
      box-shadow: none;
    }
    body.dark .subj-cb { background: #1d2636; border-color: #607089; box-shadow: inset 0 0 0 2px #1d2636; }
    body.dark .subj-cb:checked { border-color: #ffffff; background-color: #ffffff; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%230b1220' d='M4.7 9.4L1.6 6.3l1-1 2.1 2.1 4.7-4.7 1 1z'/%3E%3C/svg%3E"); }
    /* ── WIZARD STEPS ── */
    .wiz-steps { display:flex; align-items:center; gap:0; padding:1.1rem 1.5rem .85rem; background:var(--bg); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:10; }
    .wiz-step { display:flex; flex-direction:column; align-items:center; flex:1; position:relative; cursor:default; user-select:none; }
    .wiz-step:not(:last-child)::after { content:''; position:absolute; top:16px; left:calc(50% + 16px); right:calc(-50% + 16px); height:2px; background:var(--border); transition:background .35s ease; z-index:0; }
    .wiz-step.done:not(:last-child)::after, .wiz-step.active:not(:last-child)::after { background:var(--primary); }
    .wiz-dot { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:800; flex-shrink:0; border:2.5px solid var(--border); background:var(--surface); color:var(--text-muted); transition:all .28s cubic-bezier(.4,0,.2,1); z-index:1; position:relative; }
    .wiz-step.active .wiz-dot { border-color:var(--primary); background:var(--primary); color:#fff; box-shadow:0 0 0 4px rgba(26,158,120,.18); transform:scale(1.08); }
    .wiz-step.done .wiz-dot { border-color:var(--primary); background:var(--primary-light); color:var(--primary); }
    .wiz-label { font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.55px; color:var(--text-muted); margin-top:.38rem; text-align:center; line-height:1.25; white-space:nowrap; }
    .wiz-step.active .wiz-label { color:var(--primary); }
    .wiz-step.done .wiz-label { color:var(--primary-dark); }
    .wiz-panel { display:none; animation:fadeUp .26s ease both; }
    .wiz-panel.active { display:block; }
    .wiz-footer { display:flex; align-items:center; justify-content:space-between; gap:.65rem; padding:.85rem 1.3rem; background:var(--bg); border-top:1px solid var(--border); position:sticky; bottom:0; z-index:1; }
    .wiz-footer-right { display:flex; gap:.5rem; }
    .btn-wiz-back { display:inline-flex; align-items:center; gap:.38rem; padding:.52rem 1.1rem; border-radius:10px; border:1.5px solid var(--border); background:var(--surface); font-size:.85rem; font-weight:700; color:var(--text); cursor:pointer; transition:all .18s; font-family:inherit; }
    .btn-wiz-back:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-light); }
    .btn-wiz-next { display:inline-flex; align-items:center; gap:.38rem; padding:.52rem 1.3rem; border-radius:10px; border:none; background:var(--primary); color:#fff; font-size:.85rem; font-weight:700; cursor:pointer; transition:all .18s; font-family:inherit; box-shadow:0 4px 12px rgba(26,158,120,.25); }
    .btn-wiz-next:hover { background:var(--primary-dark); transform:translateY(-1px); }
    .btn-wiz-next:disabled { opacity:.45; cursor:not-allowed; transform:none; box-shadow:none; }
    .review-row { display:flex; align-items:flex-start; gap:.65rem; padding:.52rem .75rem; border-radius:8px; border:1px solid var(--border); background:var(--bg); margin-bottom:.38rem; font-size:.83rem; }
    .review-label { font-size:.64rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); min-width:90px; flex-shrink:0; padding-top:.12rem; }
    .review-val { font-weight:600; color:var(--text); flex:1; }
    #subjModal,
    #subjModal * {
      font-family: 'DM Sans', sans-serif;
    }
    #subjModal .fas,
    #subjModal .far {
      font-family: "Font Awesome 5 Free" !important;
    }
    #subjModal .fas {
      font-weight: 900 !important;
    }
    #subjModal .far {
      font-weight: 400 !important;
    }
    #subjModal .fab {
      font-family: "Font Awesome 5 Brands" !important;
    }
    #subjModal .modal-title {
      font-size: 1rem;
      font-weight: 700;
      letter-spacing: 0;
    }
    #subjModal .modal-body {
      font-size: .88rem;
      color: var(--text);
    }
    #subjModal .form-section-label {
      font-size: .66rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    #subjModal .form-group label {
      font-size: .74rem;
      font-weight: 600;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: .4px;
    }
    #subjModal .form-group input,
    #subjModal .form-group select {
      min-height: 54px;
      font-size: .9rem;
      font-weight: 500;
      line-height: 1.35;
    }
    #subjModal .form-group input::placeholder {
      color: var(--text-muted);
      font-weight: 500;
      opacity: 1;
    }
    #subjModal .sem-info-bar { font-size: .82rem; }
    #subjModal .wiz-label { font-size: .67rem; letter-spacing: .6px; }
    #subjModal .btn-wiz-back,
    #subjModal .btn-wiz-next { font-size: .86rem; }
    #subjModal .review-row { font-size: .85rem; padding: .7rem .9rem; }
    #subjModal .review-label { font-size: .67rem; min-width: 108px; }
    #subjModal .review-val { font-size: .9rem; font-weight: 600; }
    #subjModal .form-grid.tight-3 {
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:.85rem;
    }
    #subjModal .share-mode-select {
      display:none;
    }
    #subjModal .share-mode-buttons {
      display:grid;
      grid-template-columns:repeat(3, minmax(0, 1fr));
      gap:.75rem;
    }
    #subjModal .share-mode-btn {
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      gap:.45rem;
      min-height:82px;
      padding:.8rem .75rem;
      border-radius:14px;
      border:1.5px solid var(--border);
      background:var(--surface);
      color:var(--text);
      font-size:.78rem;
      font-weight:700;
      letter-spacing:.15px;
      cursor:pointer;
      transition:all .18s ease;
      font-family:inherit;
      text-align:center;
      line-height:1.25;
    }
    #subjModal .share-mode-btn i {
      width:34px;
      height:34px;
      border-radius:11px;
      display:flex;
      align-items:center;
      justify-content:center;
      background:var(--primary-light);
      color:var(--primary);
      font-size:.95rem;
      flex:0 0 auto;
    }
    #subjModal .share-mode-btn span {
      display:block;
      max-width:150px;
      color:var(--text);
    }
    #subjModal .share-mode-btn:hover {
      border-color:var(--primary);
      transform:translateY(-1px);
      box-shadow:0 8px 20px rgba(0,0,0,.08);
    }
    #subjModal .share-mode-btn.active {
      border-color:var(--primary);
      background:var(--primary-light);
      box-shadow:inset 0 0 0 1px rgba(26,158,120,.18), 0 8px 22px rgba(26,158,120,.12);
    }
    #subjModal .share-mode-btn.active i {
      background:var(--primary);
      color:#fff;
    }
    #subjModal .share-picker {
      border:1px solid var(--border);
      border-radius:14px;
      background:var(--surface);
      overflow:hidden;
    }
    #subjModal .share-picker-search {
      display:flex;
      align-items:center;
      gap:.55rem;
      padding:.75rem .85rem;
      border-bottom:1px solid var(--border);
    }
    #subjModal .share-picker-search input {
      flex:1;
      min-width:0;
      min-height:42px;
      border:1px solid var(--border);
      border-radius:10px;
      padding:.6rem .8rem;
      font:inherit;
      background:var(--surface);
      color:var(--text);
    }
    #subjModal .share-picker-list {
      max-height:220px;
      overflow:auto;
    }
    #subjModal .form-group label.share-picker-item {
      display:flex;
      align-items:center;
      gap:.65rem;
      padding:.8rem .95rem;
      border-bottom:1px solid var(--border);
      cursor:pointer;
      color:var(--text);
      font-size:.82rem;
      font-weight:500;
      letter-spacing:0;
      line-height:1.3;
      text-transform:none;
    }
    #subjModal .form-group label.share-picker-item:last-child {
      border-bottom:none;
    }
    #subjModal .form-group label.share-picker-item.disabled {
      opacity:.55;
      cursor:not-allowed;
    }
    #subjModal .form-group .share-picker-item input[type="checkbox"] {
      appearance:auto;
      -webkit-appearance:auto;
      width:16px;
      height:16px;
      min-height:0;
      margin:0;
      padding:0;
      flex-shrink:0;
      accent-color:var(--primary);
    }
    #subjModal .share-picker-meta {
      flex:1;
      min-width:0;
      display:flex;
      align-items:baseline;
      gap:.4rem;
    }
    #subjModal .share-picker-code {
      font-size:.83rem;
      font-weight:800;
      line-height:1.25;
      white-space:nowrap;
    }
    #subjModal .share-picker-name {
      font-size:.78rem;
      color:var(--text-muted);
      line-height:1.25;
      min-width:0;
      overflow:hidden;
      text-overflow:ellipsis;
      white-space:nowrap;
    }
    #subjModal .share-picker-help {
      padding:.65rem .9rem;
      border-top:1px solid var(--border);
      font-size:.74rem;
      color:var(--text-muted);
    }
    #subjModal .share-picker-empty {
      padding:1rem;
      text-align:center;
      color:var(--text-muted);
      font-size:.78rem;
    }
    @media (max-width: 900px) {
      #subjModal .share-mode-buttons,
      #subjModal .form-grid.tight-3 {
        grid-template-columns:1fr;
      }
    }
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:.7rem; }
    .info-item { background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:.55rem .7rem; }
    .info-k { font-size:.64rem; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); font-weight:700; }
    .info-v { margin-top:.15rem; font-size:.84rem; font-weight:600; color:var(--text); word-break:break-word; }
    .info-list { margin-top:.75rem; border:1px solid var(--border); border-radius:10px; overflow:hidden; }
    .info-list-row { display:grid; grid-template-columns:1.1fr 1.2fr .9fr .9fr; gap:.5rem; padding:.55rem .7rem; border-bottom:1px solid var(--border); font-size:.78rem; }
    .info-list-head { background:#1c2a3a; color:#e4ecf7; font-size:.67rem; text-transform:uppercase; letter-spacing:.5px; font-weight:700; }
    .info-list-row:last-child { border-bottom:none; }
    .row-clickable { cursor:pointer; }
    .acct-detail-layout { display:grid; grid-template-columns:160px 1fr; gap:1rem; }
    .acct-detail-side { display:flex; flex-direction:column; align-items:center; gap:.8rem; padding:.35rem 0; }
    .acct-detail-avatar {
      width:128px; height:128px; border-radius:50%;
      display:flex; align-items:center; justify-content:center;
      background:linear-gradient(135deg,#dff6e7,#c6edd4);
      border:3px solid rgba(255,255,255,.55);
      box-shadow:0 10px 24px rgba(11,122,98,.14);
      color:#0f7d68; font-size:2.1rem; font-weight:800;
      overflow:hidden;
    }
    .acct-detail-avatar img { width:100%; height:100%; object-fit:cover; }
    .acct-detail-badges { display:flex; flex-direction:column; gap:.55rem; width:100%; align-items:center; }
    .acct-detail-pill {
      display:inline-flex; align-items:center; justify-content:center; gap:.38rem;
      min-width:112px; padding:.38rem .85rem; border-radius:999px;
      background:#ecfdf5; border:1px solid #b7e8d7; color:#0b7a62;
      font-size:.74rem; font-weight:800; line-height:1;
    }
    .acct-detail-pill.secondary {
      background:#e0f2fe; border-color:#bae6fd; color:#0369a1;
    }
    .acct-detail-pill.warn {
      background:#fff7ed; border-color:#fed7aa; color:#c2410c;
    }
    .acct-detail-main { min-width:0; }
    .acct-detail-cards { display:grid; grid-template-columns:1fr 1fr; gap:.8rem; }
    .acct-detail-card {
      background:#f7f8fb;
      border:1px solid #e4e7ec;
      border-radius:14px;
      padding:.9rem 1rem;
      min-height:74px;
      box-shadow:inset 0 1px 0 rgba(255,255,255,.65);
    }
    .acct-detail-label {
      font-size:.68rem; text-transform:uppercase; letter-spacing:.6px;
      color:#6b7280; font-weight:800;
    }
    .acct-detail-value {
      margin-top:.28rem; font-size:.86rem; font-weight:700; color:var(--text);
      line-height:1.3; word-break:break-word;
    }
    .acct-detail-value.mono { font-family:'DM Mono', monospace; color:var(--primary); }
    .acct-detail-section {
      margin-top:1.15rem; padding-top:.9rem; border-top:1px solid var(--border);
    }
    .acct-detail-section-title {
      display:flex; align-items:center; gap:.5rem;
      font-size:.75rem; font-weight:800; letter-spacing:.55px;
      text-transform:uppercase; color:var(--text-muted);
      margin-bottom:.8rem;
    }
    .acct-detail-table {
      border:1px solid var(--border); border-radius:14px; overflow:hidden; background:#fff;
    }
    .acct-detail-table-head,
    .acct-detail-table-row {
      display:grid; grid-template-columns:1fr 1.35fr 1fr .9fr; gap:.75rem;
      padding:.78rem .95rem;
    }
    .acct-detail-table-head {
      background:#1f2d3d; color:#ecf3fb;
      font-size:.67rem; font-weight:800; text-transform:uppercase; letter-spacing:.55px;
    }
    .acct-detail-table-row {
      border-top:1px solid var(--border);
      font-size:.8rem; color:var(--text);
      align-items:start;
    }
    .acct-detail-table-row:first-of-type { border-top:none; }
    .acct-detail-table-cell strong { display:block; font-size:.82rem; font-weight:800; color:var(--text); }
    .acct-detail-table-sub { margin-top:.14rem; font-size:.72rem; color:var(--text-muted); }
    .acct-detail-empty {
      padding:2.4rem 1rem; text-align:center; color:var(--text-muted);
      border-top:1px solid var(--border);
    }
    .acct-detail-empty i { font-size:2rem; opacity:.32; display:block; margin-bottom:.55rem; }

    /* ── EMPTY STATE ── */
    .empty-state { padding: 2.5rem 1rem; text-align: center; color: var(--text-muted); }
    .empty-icon { font-size: 2.5rem; margin-bottom: .75rem; opacity: .35; }
    .empty-title { font-size: 1rem; font-weight: 600; margin-bottom: .3rem; }

    /* ── SECTION PILL (for table section col) ── */
    .pill-section { background: #f3e8ff; color: #7c3aed; border: 1px solid #ddd6fe; }
    body.dark .pill-section { background: rgba(124,58,237,.18); color: #a78bfa; border-color: rgba(124,58,237,.3); }

    @media (max-width: 900px) { .stats-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 768px) {
      :root { --nav-h: 56px; }
      .topnav { padding: 0 1rem; }
      .stats-grid { grid-template-columns: 1fr 1fr; gap: .65rem; }
      .modal-backdrop { align-items: flex-end; }
      .modal-box { margin: 0; border-radius: 16px 16px 0 0; max-height: 92vh; }
      .form-grid, .form-grid.g3 { grid-template-columns: 1fr; }
      .ys-modal-grid { grid-template-columns: 1fr; }
      .info-grid { grid-template-columns:1fr; }
      .acct-detail-layout { grid-template-columns:1fr; }
      .acct-detail-side { padding-top:0; }
      .acct-detail-avatar { width:104px; height:104px; font-size:1.8rem; }
      .acct-detail-cards { grid-template-columns:1fr; }
      .acct-detail-table-head { display:none; }
      .acct-detail-table-row { grid-template-columns:1fr; gap:.35rem; }
      .info-list-row { grid-template-columns:1fr; gap:.2rem; }
      .info-list-head { display:none; }
      .page-footer { padding: 0 1rem; }
      .nav-dept-badge, .nav-role-badge { display: none; }
      .role-switcher { display: none; }
    }

    /* prevent dashboard flash before remembered tab is restored */
    body.nav-booting .page { display: none !important; }
  </style>
</head>
<body class="nav-booting">

<!-- TOP NAV -->
<nav class="topnav">
  <button class="menu-btn" id="menuToggle" title="Toggle sidebar">
    <i class="fas fa-bars"></i>
  </button>
  <a href="#" class="nav-brand">
    <div class="brand-logo"><i class="fas fa-book-open"></i></div>
    TERELEARN
  </a>
  <div style="display:flex;align-items:center;gap:.5rem;margin-left:.5rem;">
    <span class="nav-dept-badge"><i class="fas fa-university" style="font-size:.6rem;"></i> <?= $hDeptCode ?></span>
    <span class="nav-role-badge"><i class="fas fa-user-tie" style="font-size:.6rem;"></i> <?= $hRole ?></span>
  </div>
  <div class="nav-actions">
    <?php if ($isAlsoFaculty): ?>
    <!-- ROLE SWITCHER — only visible when user is also a Faculty member -->
    <div class="role-switcher" onclick="switchToFacultyView()" title="Switch to Faculty View">
      <div class="role-switcher-track"></div>
      <div class="role-switcher-label">
        <i class="fas fa-chalkboard-teacher" style="font-size:.65rem;"></i>
        Switch to Faculty
      </div>
    </div>
    <?php endif; ?>
    <button class="icon-btn" id="darkToggle" title="Dark mode"><i class="fas fa-moon"></i></button>
    <button class="icon-btn" title="Notifications"><i class="fas fa-bell"></i></button>
    <div class="nav-avatar" title="Profile"><?= $hInit ?></div>
  </div>
</nav>

<div class="overlay" id="overlay"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-user">
    <div class="s-avatar"><?= $hInit ?></div>
    <div>
      <div class="s-name"><?= $hName ?></div>
      <div class="s-role"><?= $hRole ?> · <?= $hDeptCode ?></div>
    </div>
  </div>

  <div class="nav-section-label">Main</div>
  <button class="nav-item" data-page="dashboard">
    <i class="fas fa-th-large"></i><span>Dashboard</span>
  </button>

  <div class="nav-section-label" style="margin-top:.75rem;">Department</div>
  <button class="nav-item" data-page="subjects">
    <i class="fas fa-book"></i><span>Courses</span>
    <span class="nav-badge" id="subjectBadge" style="background:#7c3aed;">0</span>
  </button>
  <button class="nav-item" data-page="classes">
    <i class="fas fa-chalkboard"></i><span>Classes</span>
    <span class="nav-badge" id="classBadge">0</span>
  </button>

  <div class="nav-section-label" style="margin-top:.75rem;">People</div>
  <button class="nav-item" data-page="faculty">
    <i class="fas fa-chalkboard-teacher"></i><span>Faculty</span>
    <span class="nav-badge" id="facultyBadge" style="background:#1f73db;">0</span>
  </button>
  <button class="nav-item" data-page="students">
    <i class="fas fa-user-graduate"></i><span>Students</span>
    <span class="nav-badge" id="studentBadge" style="background:#f57c00;">0</span>
  </button>
  <button class="nav-item" data-page="yearsections">
  <i class="fas fa-layer-group"></i><span>Year &amp; Sections</span>
</button>
  <!-- ── SUBJECT PRESETS ── -->
  <div class="page" data-page="subjectpresets">
    <div class="page-header">
      <div>
        <div class="page-title">Subject Presets</div>
        <div class="page-subtitle">Set which subjects are offered per semester for your department's programs</div>
      </div>
    </div>

    <!-- Active SY banner -->
    <div class="dept-banner" id="presetSYBanner" style="margin-bottom:1.25rem;">
      <div>
        <div class="dept-banner-lbl">Active School Year (set by Admin)</div>
        <div class="dept-banner-title" id="presetSYTitle">Loading…</div>
      </div>
      <span class="dept-role-chip" id="presetActiveSemChip">—</span>
    </div>

    <!-- Semester picker -->
    <div class="card-box" style="padding:1.25rem 1.5rem;margin-bottom:1rem;">
      <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--primary);margin-bottom:.85rem;display:flex;align-items:center;gap:.45rem;">
        <i class="fas fa-calendar-alt" style="font-size:.68rem;"></i> Choose Semester to Configure
      </div>
      <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <button class="preset-sem-btn active" data-sem="1st Semester" onclick="selectPresetSem(this)">
          <i class="fas fa-1"></i> 1st Semester
        </button>
        <button class="preset-sem-btn" data-sem="2nd Semester" onclick="selectPresetSem(this)">
          <i class="fas fa-2"></i> 2nd Semester
        </button>
      </div>
    </div>

    <!-- Per-program subject checklist -->
    <div id="presetProgramsContainer">
      <div style="text-align:center;padding:2rem;color:var(--text-muted);">
        <i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…
      </div>
    </div>
  </div>

  <?php if ($isAlsoFaculty): ?>
  <!-- SIDEBAR ROLE SWITCHER — only shown when user is also a Faculty member -->
  <div class="nav-section-label" style="margin-top:.75rem;">Switch View</div>
  <div class="sidebar-role-switcher" onclick="switchToFacultyView()" title="Switch to Faculty Dashboard">
    <div class="srs-icon"><i class="fas fa-chalkboard-teacher"></i></div>
    <div class="srs-text">
      <div class="srs-label">Faculty Dashboard</div>
      <div class="srs-sub">Switch to your faculty view</div>
    </div>
    <i class="fas fa-arrow-right srs-arrow"></i>
  </div>
  <?php endif; ?>

  <div class="sidebar-footer-inner">
    <a href="signin.php" class="signout-btn" onclick="return confirm('Sign out?')">
      <i class="fas fa-sign-out-alt"></i><span>Sign Out</span>
    </a>
  </div>
</aside>

<!-- MAIN -->
<main class="main" id="main">

  <!-- ── DASHBOARD ── -->
  <div class="page" data-page="dashboard">
    <div class="page-header">
      <div>
        <div class="page-title">Welcome, <?= $hName ?>! 👋</div>
        <div class="page-subtitle">Here's an overview of your department</div>
      </div>
    </div>
    <div class="dept-banner">
      <div>
        <div class="dept-banner-lbl">Your Department</div>
        <div class="dept-banner-title"><?= $hDeptName ?></div>
        <div class="dept-progs">
          <?php foreach ($programs as $p): ?>
            <span class="prog-pill"><?= htmlspecialchars($p['course_code']) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <span class="dept-role-chip"><i class="fas fa-user-tie" style="margin-right:.3rem;"></i><?= $hRole ?></span>
    </div>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon si-purple"><i class="fas fa-book"></i></div><div><div class="stat-val"><?= $stats['subjects'] ?></div><div class="stat-lbl">Dept Courses</div></div></div>
      <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-chalkboard"></i></div><div><div class="stat-val"><?= $stats['classes'] ?></div><div class="stat-lbl">Dept Classes</div></div></div>
      <div class="stat-card"><div class="stat-icon si-blue"><i class="fas fa-user-graduate"></i></div><div><div class="stat-val"><?= $stats['students'] ?></div><div class="stat-lbl">Dept Students</div></div></div>
      <div class="stat-card"><div class="stat-icon si-orange"><i class="fas fa-chalkboard-teacher"></i></div><div><div class="stat-val"><?= $stats['faculty'] ?></div><div class="stat-lbl">Total Faculty</div></div></div>
    </div>
  </div>

  <!-- ── SUBJECTS ── -->
  <div class="page" data-page="subjects">
    <div class="page-header">
      <div>
        <div class="page-title">Courses</div>
        <div class="page-subtitle">Manage courses under your department programs</div>
      </div>
    </div>
    <div class="acct-toolbar">
      <div class="tl-ctrl-wrap">
        <button class="tl-ctrl-btn" id="sFilterBtn" title="Filter / Controls" onclick="toggleCtrlMenu('sCtrlMenu', event)"><i class="fas fa-sliders-h"></i></button>
        <div class="tl-ctrl-menu" id="sCtrlMenu">
          <button class="tl-ctrl-item" onclick="enterSelectMode('subjects')"><i class="fas fa-list-check"></i> Select courses</button>
          <button class="tl-ctrl-item" onclick="toast('Course filter controls will be added here.', 'info')"><i class="fas fa-sliders-h"></i> Filters</button>
        </div>
      </div>
      <button class="tl-select-cancel" onclick="exitSelectMode('subjects')"><i class="fas fa-xmark"></i> Cancel</button>
      <div class="search-wrap">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="sSearch" class="search-input" placeholder="Search courses…" autocomplete="off">
        <button type="button" class="search-clear" id="sClear"><i class="fas fa-times"></i></button>
      </div>
      <button class="tl-add-btn" onclick="openSubjModal()"><i class="fas fa-plus"></i> <span>Course</span></button>
    </div>
    <!-- Bulk action bar -->
    <div class="bulk-action-bar" id="subjBulkBar">
      <span class="bulk-count" id="subjBulkCount">0 selected</span>
      <div class="bulk-btns">
        <button class="bulk-btn" onclick="bulkSubjActivate()"><i class="fas fa-check-circle"></i> Activate</button>
        <button class="bulk-btn" onclick="bulkSubjDeactivate()"><i class="fas fa-ban"></i> Deactivate</button>
        <button class="bulk-btn danger" onclick="bulkSubjDelete()"><i class="fas fa-trash"></i> Delete</button>
      </div>
      <button class="bulk-desel" onclick="subjClearSelection()" title="Clear selection"><i class="fas fa-times"></i></button>
    </div>
    <div class="acct-table-wrap">
      <table class="tl-table" id="subjTbl">
        <thead><tr>
          <th class="tl-th select-col" style="width:36px;text-align:center;padding-left:.65rem;"><input type="checkbox" class="subj-cb" id="subjSelectAll" onchange="subjToggleAll(this)"></th>
          <th class="tl-th" style="width:42px">#</th>
          <th class="tl-th">Course</th>
          <th class="tl-th" style="width:130px;text-align:left;">Year Level</th>
          <th class="tl-th" style="width:170px;text-align:left;">Semester</th>
          <th class="tl-th" style="width:120px;text-align:left;">Classes</th>
          <th class="tl-th text-center" style="width:150px">Status</th>
          <th class="tl-th" style="width:44px;padding:0;text-align:center;"></th>
        </tr></thead>
        <tbody><tr><td colspan="8" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…</td></tr></tbody>
      </table>
    </div>
    <div class="table-pager" id="subjPager"></div>
  </div>

  <!-- ── CLASSES ── -->
  <div class="page" data-page="classes">
    <div class="page-header">
      <div>
        <div class="page-title">Classes</div>
        <div class="page-subtitle">Manage classes across your department programs</div>
      </div>
    </div>
    <div class="acct-toolbar">
      <div class="tl-ctrl-wrap">
        <button class="tl-ctrl-btn" id="cFilterBtn" title="Filter / Controls" onclick="toggleCtrlMenu('cCtrlMenu', event)"><i class="fas fa-sliders-h"></i></button>
        <div class="tl-ctrl-menu" id="cCtrlMenu">
          <button class="tl-ctrl-item" onclick="enterSelectMode('classes')"><i class="fas fa-list-check"></i> Select classes</button>
          <button class="tl-ctrl-item" onclick="toast('Class filter controls will be added here.', 'info')"><i class="fas fa-sliders-h"></i> Filters</button>
        </div>
      </div>
      <button class="tl-select-cancel" onclick="exitSelectMode('classes')"><i class="fas fa-xmark"></i> Cancel</button>
      <div class="search-wrap">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="cSearch" class="search-input" placeholder="Search classes…" autocomplete="off">
        <button type="button" class="search-clear" id="cClear"><i class="fas fa-times"></i></button>
      </div>
      <button class="tl-add-btn" onclick="openClassModal()"><i class="fas fa-plus"></i> <span>Class</span></button>
    </div>
    <div class="bulk-action-bar" id="clsBulkBar">
      <span class="bulk-count" id="clsBulkCount">0 selected</span>
      <div class="bulk-btns">
        <button class="bulk-btn" onclick="bulkClsActivate()"><i class="fas fa-check-circle"></i> Activate</button>
        <button class="bulk-btn" onclick="bulkClsDeactivate()"><i class="fas fa-ban"></i> Deactivate</button>
        <button class="bulk-btn danger" onclick="bulkClsDelete()"><i class="fas fa-trash"></i> Delete</button>
      </div>
      <button class="bulk-desel" onclick="clsClearSelection()" title="Clear selection"><i class="fas fa-times"></i></button>
    </div>
    <div class="acct-table-wrap">
      <table class="tl-table" id="clsTbl">
        <thead><tr>
          <th class="tl-th select-col" style="width:36px;text-align:center;padding-left:.65rem;"><input type="checkbox" class="subj-cb" id="clsSelectAll" onchange="clsToggleAll(this)"></th>
          <th class="tl-th" style="width:42px">#</th>
          <th class="tl-th" style="min-width:180px">Professor</th>
          <th class="tl-th" style="min-width:130px">Course</th>
          <th class="tl-th" style="min-width:190px">Schedule</th>
          <th class="tl-th text-center" style="width:85px">Status</th>
          <th class="tl-th text-center" style="width:95px">Students</th>
          <th class="tl-th" style="width:44px;padding:0;text-align:center;"></th>
        </tr></thead>
        <tbody><tr><td colspan="8" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…</td></tr></tbody>
      </table>
    </div>
    <div class="table-pager" id="clsPager"></div>
  </div>

  <!-- ── FACULTY ── -->
  <div class="page" data-page="faculty">
    <div class="page-header">
      <div>
        <div class="page-title">Faculty</div>
        <div class="page-subtitle">Manage faculty accounts and OTP settings</div>
      </div>
    </div>
    <div class="acct-toolbar">
      <div class="tl-ctrl-wrap">
        <button class="tl-ctrl-btn" id="fFilterBtn" title="Filter / Controls" onclick="toggleCtrlMenu('fCtrlMenu', event)"><i class="fas fa-sliders-h"></i></button>
        <div class="tl-ctrl-menu" id="fCtrlMenu">
          <button class="tl-ctrl-item" onclick="enterSelectMode('faculty')"><i class="fas fa-list-check"></i> Select faculty</button>
          <button class="tl-ctrl-item" onclick="toast('Faculty filter controls will be added here.', 'info')"><i class="fas fa-sliders-h"></i> Filters</button>
        </div>
      </div>
      <button class="tl-select-cancel" onclick="exitSelectMode('faculty')"><i class="fas fa-xmark"></i> Cancel</button>
      <div class="search-wrap">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="fSearch" class="search-input" placeholder="Search faculty…" autocomplete="off">
        <button type="button" class="search-clear" id="fClear"><i class="fas fa-times"></i></button>
      </div>
      <button class="tl-add-btn" onclick="openFacModal()"><i class="fas fa-user-plus"></i> <span>Add Account</span></button>
    </div>
    <div class="bulk-action-bar" id="facBulkBar">
      <span class="bulk-count" id="facBulkCount">0 selected</span>
      <div class="bulk-btns">
        <button class="bulk-btn" onclick="bulkFacActivate()"><i class="fas fa-check-circle"></i> Activate</button>
        <button class="bulk-btn" onclick="bulkFacDeactivate()"><i class="fas fa-ban"></i> Deactivate</button>
        <button class="bulk-btn danger" onclick="bulkFacDelete()"><i class="fas fa-trash"></i> Delete</button>
      </div>
      <button class="bulk-desel" onclick="facClearSelection()" title="Clear selection"><i class="fas fa-times"></i></button>
    </div>
    <div class="acct-table-wrap">
      <table class="tl-table" id="facTbl">
        <thead><tr>
          <th class="tl-th select-col" style="width:36px;text-align:center;padding-left:.65rem;"><input type="checkbox" class="subj-cb" id="facSelectAll" onchange="facToggleAll(this)"></th>
          <th class="tl-th" style="width:38px">#</th>
          <th class="tl-th" style="min-width:220px">Full Name</th>
          <th class="tl-th" style="width:112px">Employee ID</th>
          <th class="tl-th" style="min-width:160px">Department</th>
          <th class="tl-th text-center" style="width:70px">Status</th>
          <th class="tl-th text-center" style="width:98px">Email OTP</th>
          <th class="tl-th" style="width:44px;padding:0;text-align:center;"></th>
        </tr></thead>
        <tbody><tr><td colspan="8" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…</td></tr></tbody>
      </table>
    </div>
    <div class="table-pager" id="facPager"></div>
  </div>

  <!-- ── STUDENTS ── -->
  <div class="page" data-page="students">
    <div class="page-header">
      <div>
        <div class="page-title">Students</div>
        <div class="page-subtitle">Manage students enrolled in your department</div>
      </div>
    </div>
    <div class="acct-toolbar">
      <div class="tl-ctrl-wrap">
        <button class="tl-ctrl-btn" id="stFilterBtn" title="Filter / Controls" onclick="toggleCtrlMenu('stCtrlMenu', event)"><i class="fas fa-sliders-h"></i></button>
        <div class="tl-ctrl-menu" id="stCtrlMenu">
          <button class="tl-ctrl-item" onclick="enterSelectMode('students')"><i class="fas fa-list-check"></i> Select students</button>
          <button class="tl-ctrl-item" onclick="toast('Student filter controls will be added here.', 'info')"><i class="fas fa-sliders-h"></i> Filters</button>
        </div>
      </div>
      <button class="tl-select-cancel" onclick="exitSelectMode('students')"><i class="fas fa-xmark"></i> Cancel</button>
      <div class="search-wrap">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="stSearch" class="search-input" placeholder="Search students…" autocomplete="off">
        <button type="button" class="search-clear" id="stClear"><i class="fas fa-times"></i></button>
      </div>
      <button class="tl-add-btn" id="btnAddStudent"><i class="fas fa-user-plus"></i> <span>Add Account</span></button>
    </div>
    <div class="bulk-action-bar" id="stuBulkBar">
      <span class="bulk-count" id="stuBulkCount">0 selected</span>
      <div class="bulk-btns">
        <button class="bulk-btn" onclick="bulkStuActivate()"><i class="fas fa-check-circle"></i> Activate</button>
        <button class="bulk-btn" id="stuOtpOnBtn" onclick="bulkStuOtpOn()"><i class="fas fa-shield-alt"></i> OTP On</button>
        <button class="bulk-btn" id="stuOtpOffBtn" onclick="bulkStuOtpOff()"><i class="fas fa-shield-alt"></i> OTP Off</button>
        <button class="bulk-btn" onclick="bulkStuDeactivate()"><i class="fas fa-ban"></i> Deactivate</button>
        <button class="bulk-btn danger" onclick="bulkStuDelete()"><i class="fas fa-trash"></i> Delete</button>
      </div>
      <button class="bulk-desel" onclick="stuClearSelection()" title="Clear selection"><i class="fas fa-times"></i></button>
    </div>
    <div class="acct-table-wrap">
      <table class="tl-table" id="stuTbl">
        <thead><tr>
          <th class="tl-th select-col" style="width:36px;text-align:center;padding-left:.65rem;"><input type="checkbox" class="subj-cb" id="stuSelectAll" onchange="stuToggleAll(this)"></th>
          <th class="tl-th" style="width:38px">#</th>
          <th class="tl-th" style="min-width:190px">Full Name</th>
          <th class="tl-th" style="width:110px">Student ID</th>
          <th class="tl-th" style="width:100px">Course</th>
          <th class="tl-th text-center" style="width:70px">Status</th>
          <th class="tl-th text-center" style="width:110px">Email OTP</th>
          <th class="tl-th" style="width:44px;padding:0;text-align:center;"></th>
        </tr></thead>
        <tbody><tr><td colspan="8" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…</td></tr></tbody>
      </table>
    </div>
    <div class="table-pager" id="stuPager"></div>
  </div>

<!-- ── YEAR & SECTIONS ── -->
<div class="page" data-page="yearsections">
  <div class="page-header">
    <div>
      <div class="page-title">Year &amp; Sections</div>
      <div class="page-subtitle">Configure sections per year level for your department's programs</div>
    </div>
  </div>

  <!-- PROGRAM SELECTOR -->
  <div id="ysProgramSelectorCard" class="card-box" style="padding:1.25rem 1.5rem;margin-bottom:1rem;<?= count($programs) === 1 ? 'display:none;' : '' ?>">
    <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--primary);display:flex;align-items:center;gap:.45rem;margin-bottom:.85rem;">
      <i class="fas fa-graduation-cap" style="font-size:.68rem;"></i> Select Program
    </div>
    <div class="acct-toolbar" style="margin-bottom:0;">
      <div class="tl-ctrl-wrap">
        <button class="tl-ctrl-btn" id="ysFilterBtn" title="Filter / Controls" onclick="toggleCtrlMenu('ysCtrlMenu', event)"><i class="fas fa-sliders-h"></i></button>
        <div class="tl-ctrl-menu" id="ysCtrlMenu">
          <button class="tl-ctrl-item" onclick="toast('Year & Sections select mode will be added here.', 'info'); closeAllCtrlMenus();"><i class="fas fa-list-check"></i> Select rows</button>
          <button class="tl-ctrl-item" onclick="toast('Year & Sections filter controls will be added here.', 'info'); closeAllCtrlMenus();"><i class="fas fa-sliders-h"></i> Filters</button>
        </div>
      </div>
      <div class="form-group" style="margin:0;max-width:420px;flex:1;min-width:220px;">
        <select id="ysProgSelect" onchange="ysOnProgramChange(this.value)"
          style="width:100%;padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);">
          <option value="">— Select a Program —</option>
          <?php foreach ($programs as $p): ?>
            <option value="<?= htmlspecialchars($p['id']) ?>">
              <?= htmlspecialchars($p['course_code']) ?> — <?= htmlspecialchars($p['course_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

    <!-- CONFIG TABLE (hidden until program selected) -->
    <div class="card-box" id="ysTableCard" style="display:none;padding:1rem 1rem .35rem;margin-bottom:1.25rem;">
      <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:.55rem;display:flex;align-items:center;gap:.4rem;">
        <i class="fas fa-table" style="font-size:.65rem;"></i>
        Config for: <span id="ysTableLabel" style="color:var(--primary);margin-left:.15rem;text-transform:none;font-size:.8rem;"></span>
        <button class="tl-add-btn" style="margin-left:auto;" onclick="ysOpenModal()">
          <i class="fas fa-plus"></i> <span>Add Config</span>
        </button>
      </div>
      <div class="acct-table-wrap" style="margin-bottom:.6rem;">
        <table class="tl-table" id="ysTable">
          <thead><tr>
            <th class="tl-th" style="width:42px">#</th>
            <th class="tl-th">Year Level</th>
            <th class="tl-th text-center" style="width:140px">No. of Sections</th>
            <th class="tl-th" style="width:44px;padding:0;text-align:center;"></th>
          </tr></thead>
          <tbody>
            <tr><td colspan="4" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);">
              Select a program above to view its config.
            </td></tr>
          </tbody>
        </table>
      </div>
      <div class="table-pager" id="ysPager"></div>
    </div>

  <!-- ALL PROGRAMS OVERVIEW -->
  <div id="ysOverviewWrap" style="margin-bottom:1.25rem;<?= count($programs) < 2 ? 'display:none;' : '' ?>">
    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:.55rem;display:flex;align-items:center;gap:.4rem;">
      <i class="fas fa-layer-group" style="font-size:.65rem;"></i> All Programs Overview
    </div>
    <div class="acct-table-wrap">
      <table class="tl-table" id="ysOverviewTable">
        <thead><tr>
          <th class="tl-th" style="width:42px">#</th>
          <th class="tl-th" style="width:90px">Code</th>
          <th class="tl-th">Program Name</th>
          <th class="tl-th text-center" style="width:90px">1st Year</th>
          <th class="tl-th text-center" style="width:90px">2nd Year</th>
          <th class="tl-th text-center" style="width:90px">3rd Year</th>
          <th class="tl-th text-center" style="width:90px">4th Year</th>
        </tr></thead>
        <tbody id="ysOverviewBody">
          <tr><td colspan="7" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);">
            <i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…
          </td></tr>
        </tbody>
      </table>
    </div>
    <div class="table-pager" id="ysOverviewPager"></div>
  </div>
</div>

  <!-- ── SUBJECT PRESETS ── -->
  <div class="page" data-page="subjectpresets">
    <div class="page-header">
      <div>
        <div class="page-title">Subject Presets</div>
        <div class="page-subtitle">Set which subjects are offered per semester for your department's programs</div>
      </div>
    </div>

    <!-- Active SY banner -->
    <div class="dept-banner" id="presetSYBanner" style="margin-bottom:1.25rem;">
      <div>
        <div class="dept-banner-lbl">Active School Year (set by Admin)</div>
        <div class="dept-banner-title" id="presetSYTitle">Loading…</div>
      </div>
      <span class="dept-role-chip" id="presetActiveSemChip">—</span>
    </div>

    <!-- Semester picker -->
    <div class="card-box" style="padding:1.25rem 1.5rem;margin-bottom:1rem;">
      <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--primary);margin-bottom:.85rem;display:flex;align-items:center;gap:.45rem;">
        <i class="fas fa-calendar-alt" style="font-size:.68rem;"></i> Choose Semester to Configure
      </div>
      <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <button class="preset-sem-btn active" data-sem="1st Semester" onclick="selectPresetSem(this)">
          <i class="fas fa-1"></i> 1st Semester
        </button>
        <button class="preset-sem-btn" data-sem="2nd Semester" onclick="selectPresetSem(this)">
          <i class="fas fa-2"></i> 2nd Semester
        </button>
      </div>
    </div>

    <!-- Per-program subject checklist -->
    <div id="presetProgramsContainer">
      <div style="text-align:center;padding:2rem;color:var(--text-muted);">
        <i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…
      </div>
    </div>
  </div>

</main>

<!-- FOOTER -->
<footer class="page-footer">
  <span>Copyright &copy; 2025–2026 <strong>TERELEARN</strong></span>
  <span class="footer-dept-badge">
    <i class="fas fa-university"></i>
    <?= $hDeptCode ?> — <?= $hRole ?>
  </span>
</footer>

<!-- ════════════════════════════════════════════
     MODALS
════════════════════════════════════════════ -->

<!-- SUBJECT MODAL — 3-step wizard -->
<div class="modal-backdrop" id="subjModal">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">
        <div class="modal-title-icon"><i class="fas fa-book"></i></div>
        <span id="sMTitle">Add Course</span>
      </div>
      <button class="modal-close" onclick="closeModal('subjModal');wizReset()"><i class="fas fa-times"></i></button>
    </div>
    <input type="hidden" id="sEditId">
    <!-- Step bar -->
    <div class="wiz-steps">
      <div class="wiz-step active" id="sWizStep0" style="<?= count($programs) === 1 ? 'display:none;' : '' ?>">
        <div class="wiz-dot"><i class="fas fa-graduation-cap"></i></div>
        <div class="wiz-label">Program</div>
      </div>
      <div class="wiz-step" id="sWizStep1">
        <div class="wiz-dot"><i class="fas fa-tag"></i></div>
        <div class="wiz-label">Details</div>
      </div>
      <div class="wiz-step" id="sWizStep2">
        <div class="wiz-dot"><i class="fas fa-check"></i></div>
        <div class="wiz-label">Review</div>
      </div>
    </div>
    <!-- Panels -->
    <div class="modal-body" style="padding:1.25rem 1.5rem;background:var(--bg);">
      <!-- Panel 0: Program -->
      <div class="wiz-panel active" id="sWizPanel0" style="<?= count($programs) === 1 ? 'display:none;' : '' ?>">
        <div class="form-section">
          <div class="form-section-label"><i class="fas fa-university"></i> Select Program</div>
          <div class="form-group">
            <label>Program <span class="required">*</span></label>
            <select id="sCourse" onchange="syncCourseShareScope()"><option value="">— Select Program —</option><?= $courseOptsDept ?></select>
          </div>
        </div>
      </div>
      <!-- Panel 1: Details -->
      <div class="wiz-panel" id="sWizPanel1">
        <div class="form-section">
          <div class="form-section-label"><i class="fas fa-tag"></i> Course Details</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Course Code <span class="required">*</span></label>
              <input type="text" id="sCode" placeholder="e.g. ITELEC1">
            </div>
            <div class="form-group">
              <label>Course Name <span class="required">*</span></label>
              <input type="text" id="sName" placeholder="e.g. IT Elective 1">
            </div>
          </div>
          <div class="form-grid" style="margin-top:.75rem;">
            <div class="form-group">
              <label>Year Level <span class="required">*</span></label>
              <select id="sYearLevel">
                <option value="">— Select Year Level —</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
                <option value="5">5th Year</option>
              </select>
            </div>
            <div class="form-group">
              <label>Semester <span class="required">*</span></label>
              <select id="sSemester">
                <option value="">— Select Semester —</option>
                <option value="1st Semester">1st Semester</option>
                <option value="2nd Semester">2nd Semester</option>
              </select>
            </div>
          </div>
          <div class="form-grid tight-3" style="margin-top:.75rem;">
            <div class="form-group" style="grid-column:1 / -1;">
              <label>Availability <span class="required">*</span></label>
              <select id="sShareScope" class="share-mode-select" onchange="syncCourseShareScope()">
                <option value="department_only">Only my program</option>
                <option value="selected_programs">Share with selected programs</option>
                <option value="all_programs">Open to all programs</option>
              </select>
              <div class="share-mode-buttons">
                <button type="button" class="share-mode-btn" data-scope="department_only" onclick="setCourseShareScope('department_only')">
                  <i class="fas fa-building"></i><span>Department Only</span>
                </button>
                <button type="button" class="share-mode-btn" data-scope="selected_programs" onclick="setCourseShareScope('selected_programs')">
                  <i class="fas fa-share-alt"></i><span>Selected Programs</span>
                </button>
                <button type="button" class="share-mode-btn" data-scope="all_programs" onclick="setCourseShareScope('all_programs')">
                  <i class="fas fa-globe"></i><span>All Programs</span>
                </button>
              </div>
            </div>
            <div class="form-group" id="sSharedProgramsWrap" style="display:none;grid-column:1 / -1;">
              <label>Shared Programs <span class="required">*</span></label>
              <select id="sSharedPrograms" multiple size="6" hidden><?= $courseOptsAll ?></select>
              <div class="share-picker">
                <div class="share-picker-search">
                  <i class="fas fa-search"></i>
                  <input type="text" id="sSharedProgramsSearch" placeholder="Search programs..." oninput="renderSharedProgramsPicker()">
                </div>
                <div class="share-picker-list" id="sSharedProgramsList"></div>
                <div class="share-picker-help">
                  <i class="fas fa-info-circle"></i> Click to select one or more programs
                </div>
              </div>
            </div>
          </div>
          <div class="sem-info-bar" style="margin-top:.7rem;">
            <i class="fas fa-calendar-alt" style="flex-shrink:0;"></i>
            <span>School Year: <strong id="sSYDisplay">—</strong>
            <span style="margin-left:.35rem;font-size:.7rem;opacity:.7;">(auto-applied, set by admin)</span></span>
          </div>
        </div>
      </div>
      <!-- Panel 2: Review -->
      <div class="wiz-panel" id="sWizPanel2">
        <div style="background:var(--primary-light);border:1.5px solid rgba(26,158,120,.2);border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.82rem;color:var(--primary);display:flex;align-items:center;gap:.5rem;">
          <i class="fas fa-eye"></i>
          <span>Review the details below before saving. Click <strong>Back</strong> to make changes.</span>
        </div>
        <div id="sWizReview"></div>
      </div>
    </div>
    <!-- Wizard footer -->
    <div class="wiz-footer">
      <button class="btn-wiz-back" id="sWizBtnCancel" onclick="closeModal('subjModal');wizReset()">
        <i class="fas fa-times"></i> Cancel
      </button>
      <div class="wiz-footer-right">
        <button class="btn-wiz-back" id="sWizBtnBack" onclick="wizBack()" style="display:none;">
          <i class="fas fa-arrow-left"></i> Back
        </button>
        <button class="btn-wiz-next" id="sWizBtnNext" onclick="wizNext()">
          Next <i class="fas fa-arrow-right"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- CLASS MODAL -->
<div class="modal-backdrop" id="classModal">
  <div class="modal-box modal-xl">
    <div class="modal-header">
      <div class="modal-title">
        <div class="modal-title-icon"><i class="fas fa-chalkboard"></i></div>
        <span id="cMTitle">Add Class</span>
      </div>
      <button class="modal-close" onclick="closeModal('classModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="cEditId">
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-book-open"></i> Course &amp; Course</div>
        <div class="form-grid g3">
          <div class="form-group">
            <label>Course <span class="required">*</span></label>
            <select id="cCourse"><option value="">— Select —</option><?= $courseOptsDept ?></select>
          </div>
          <div class="form-group">
            <label>Course <span class="required">*</span></label>
            <select id="cSubject" disabled><option value="">Select course first</option></select>
          </div>
          <div class="form-group">
            <label>Section <span class="required">*</span></label>
            <input type="text" id="cSection" placeholder="e.g. A1">
          </div>
        </div>
      </div>
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-user-tie"></i> Assignment</div>
        <div class="form-grid">
          <div class="form-group">
            <label>Professor <span class="required">*</span></label>
            <select id="cFaculty"><option value="">Loading…</option></select>
          </div>
          <div class="form-group">
            <label>Year Level <span class="required">*</span></label>
            <select id="cYear">
              <option value="">— Select Year Level —</option>
              <option value="1">1st Year</option>
              <option value="2">2nd Year</option>
              <option value="3">3rd Year</option>
              <option value="4">4th Year</option>
            </select>
          </div>
        </div>
        <div class="sem-info-bar">
          <i class="fas fa-calendar-alt" style="flex-shrink:0;"></i>
          <span>Active Semester: <strong id="cSemDisplay">—</strong> &nbsp;·&nbsp; School Year: <strong id="cSYDisplay">—</strong>
          <span style="margin-left:.35rem;font-size:.7rem;opacity:.7;">(auto-applied)</span></span>
        </div>
      </div>
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-clock"></i> Schedule</div>
        <div style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted);margin-bottom:.5rem;">Class Time <span class="required">*</span></div>
        <div class="form-grid" style="margin-bottom:.75rem;">
          <div class="form-group"><label>Start</label><input type="time" id="cStart"></div>
          <div class="form-group"><label>End</label><input type="time" id="cEnd"></div>
        </div>
        <div style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted);margin-bottom:.5rem;">Break Time <span style="font-size:.7rem;font-weight:400;text-transform:none;">(optional)</span></div>
        <div class="form-grid">
          <div class="form-group"><label>Break In</label><input type="time" id="cBreakStart"></div>
          <div class="form-group"><label>Break Out</label><input type="time" id="cBreakEnd"></div>
        </div>
      </div>
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-calendar-week"></i> Meeting Days</div>
        <div class="day-pills">
          <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
            <div class="day-pill"><input type="checkbox" id="cd_<?= $d ?>" value="<?= $d ?>"><label for="cd_<?= $d ?>"><?= $d ?></label></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('classModal')">Cancel</button>
      <button class="btn btn-primary" id="cSaveBtn" onclick="saveClass()"><i class="fas fa-check"></i> Save Class</button>
    </div>
  </div>
</div>

<!-- YEAR & SECTION CONFIG MODAL -->
<div class="modal-backdrop" id="ysModal">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">
        <div class="modal-title-icon"><i class="fas fa-layer-group"></i></div>
        <span id="ysFormTitle">Add Year Level Config</span>
      </div>
      <button class="modal-close" onclick="closeModal('ysModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-sliders-h"></i> Configuration</div>
        <div class="ys-modal-grid">
          <div class="form-group" style="margin:0;">
            <label>Year Level <span class="required">*</span></label>
            <select id="ysYearLevel">
              <option value="">— Select Year Level —</option>
              <option value="1">1st Year</option>
              <option value="2">2nd Year</option>
              <option value="3">3rd Year</option>
              <option value="4">4th Year</option>
            </select>
          </div>
          <div class="form-group" style="margin:0;">
            <label>No. of Sections <span class="required">*</span></label>
            <input type="number" id="ysSectionCount" min="1" max="30" placeholder="e.g. 3">
          </div>
        </div>
        <input type="hidden" id="ysEditId">
        <div style="margin-top:.85rem;background:var(--primary-light);border:1px solid rgba(26,158,120,.2);border-radius:8px;padding:.55rem .85rem;font-size:.78rem;color:var(--primary);display:flex;align-items:center;gap:.45rem;">
          <i class="fas fa-info-circle" style="flex-shrink:0;"></i>
          Example: <strong style="margin:0 .25rem;">3rd Year → 3 sections</strong> gives sections
          <strong style="margin-left:.25rem;">3-1, 3-2, 3-3</strong> when a faculty creates a class.
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="ysResetForm()">Reset</button>
      <button class="btn btn-ghost" onclick="closeModal('ysModal')">Cancel</button>
      <button class="btn btn-primary" onclick="ysSaveConfig()"><i class="fas fa-check"></i> Save Config</button>
    </div>
  </div>
</div>

<!-- FACULTY MODAL -->
<div class="modal-backdrop" id="facModal">
  <div class="modal-box modal-xl">
    <div class="modal-header">
      <div class="modal-title">
        <div class="modal-title-icon"><i class="fas fa-user-plus"></i></div>
        <span id="fMTitle">Add Faculty</span>
      </div>
      <button class="modal-close" onclick="closeModal('facModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="acct-add-steps" id="facSteps">
        <div class="acct-step active" data-step="1"><div class="acct-step-dot">1</div><div class="acct-step-label">Basic Info</div></div>
        <div class="acct-step" data-step="2"><div class="acct-step-dot">2</div><div class="acct-step-label">Contact</div></div>
        <div class="acct-step" data-step="3"><div class="acct-step-dot"><i class="fas fa-check"></i></div><div class="acct-step-label">Review</div></div>
      </div>
      <input type="hidden" id="fEditId">
      <div class="wiz-panel active" data-fstep="1">
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-id-card"></i> Basic Info</div>
        <div class="form-grid">
<div class="form-group"><label>Faculty ID <span class="required">*</span></label><input type="text" id="fNum" placeholder="00-00000" autocomplete="off"></div>          <div class="form-group">
            <label>Username <span class="required">*</span></label>
            <div class="input-prefix"><span>@</span><input type="text" id="fUser" placeholder="username"></div>
          </div>
        </div>
        <div class="form-grid g3" style="margin-top:.7rem;">
          <div class="form-group"><label>First Name <span class="required">*</span></label><input type="text" id="fFirst"></div>
          <div class="form-group"><label>Middle Name</label><input type="text" id="fMid" placeholder="Leave blank if none"></div>
          <div class="form-group"><label>Last Name <span class="required">*</span></label><input type="text" id="fLast"></div>
          <div class="form-group"><label>Suffix</label><input type="text" id="fSuffix" placeholder="Leave blank if none"></div>
        </div>
      </div>
      </div>
      <div class="wiz-panel" data-fstep="2">
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-address-book"></i> Contact &amp; Login</div>
        <div class="form-grid">
          <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <div class="input-prefix"><span><i class="fas fa-envelope"></i></span><input type="email" id="fEmail" placeholder="admin@gmail.com"></div>
          </div>
          <div class="form-group">
            <label>Phone <span class="required">*</span></label>
            <div class="input-prefix"><span>+63</span><input type="text" id="fPhone" placeholder="000-000-0000" maxlength="12" inputmode="numeric"></div>
          </div>
        </div>
        <div class="form-grid" style="margin-top:.7rem;">
          <div class="form-group">
            <label>Birthdate <span class="required">*</span></label>
            <input type="date" id="fBirth" max="<?= date('Y-m-d') ?>">
          </div>
          <div class="form-group">
            <label>Password <button type="button" class="auto-btn" onclick="autoFacPw()">↺ Auto</button></label>
            <input type="text" id="fPass" placeholder="Set password">
            <div id="fPwHint" style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem;"></div>
          </div>
        </div>
      </div>
      </div>
      <div class="wiz-panel" data-fstep="3">
        <div class="form-section">
          <div class="form-section-label"><i class="fas fa-clipboard-check"></i> Review</div>
          <div id="facReviewInfo" class="info-grid"></div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="fBackBtn" onclick="facPrevStep()" style="display:none;"><i class="fas fa-arrow-left"></i> Back</button>
      <button class="btn btn-ghost" onclick="document.querySelectorAll('#fNum,#fUser,#fFirst,#fMid,#fLast,#fSuffix,#fEmail,#fPhone,#fBirth,#fPass').forEach(e=>e.value='');document.getElementById('fPwHint').textContent='';updateFacultyInputProgress();setFacWizardStep(1);">Reset</button>
      <button class="btn btn-ghost" onclick="closeModal('facModal')">Cancel</button>
      <button class="btn btn-primary" id="fNextBtn" onclick="facNextStep()">Next <i class="fas fa-arrow-right"></i></button>
      <button class="btn btn-primary" id="fSaveBtn" onclick="saveFaculty()" style="display:none;"><i class="fas fa-check"></i> Save</button>
    </div>
  </div>
</div>

<!-- ADD STUDENT MODAL -->
<div class="modal-backdrop" id="addStuModal">
  <div class="modal-box modal-xl">
    <div class="modal-header">
      <div class="modal-title">
        <div class="modal-title-icon"><i class="fas fa-user-graduate"></i></div>
        Student Registration
      </div>
      <button class="modal-close" onclick="closeModal('addStuModal')"><i class="fas fa-times"></i></button>
    </div>
    <form id="addStuForm">
    <div class="modal-body">
      <div class="acct-add-steps" id="stuSteps">
        <div class="acct-step active" data-step="1"><div class="acct-step-dot">1</div><div class="acct-step-label">Basic Info</div></div>
        <div class="acct-step" data-step="2"><div class="acct-step-dot">2</div><div class="acct-step-label">Profile</div></div>
        <div class="acct-step" data-step="3"><div class="acct-step-dot"><i class="fas fa-check"></i></div><div class="acct-step-label">Credentials</div></div>
      </div>
      <div id="asInputProgressWrap" style="margin-bottom:.8rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem;">
          <small id="asInputProgressText" style="color:var(--text-muted);font-weight:600;">Form completion</small>
          <small id="asInputProgressPct" style="color:var(--text-muted);font-weight:700;">0%</small>
        </div>
        <div style="height:8px;background:var(--surface-muted);border-radius:999px;overflow:hidden;border:1px solid var(--border);">
          <div id="asInputProgressBar" style="height:100%;width:0%;background:linear-gradient(90deg,#67d3b5,#14b88a);transition:width .2s ease;"></div>
        </div>
      </div>
      <div class="wiz-panel active" data-sstep="1">
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-id-card"></i> Basic Info</div>
        <div class="form-grid">
          <div class="form-group"><label>Student ID <span class="required">*</span></label><input type="text" name="student_number" id="asSN" placeholder="00-00000" required></div>
          <div class="form-group"><label>Course <span class="required">*</span></label>
            <select name="course_id" required><option value="">— Select —</option><?= $courseOptsDept ?></select>
          </div>
        </div>
      </div>
      </div>
      <div class="wiz-panel" data-sstep="2">
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-user"></i> Full Name</div>
        <div class="form-grid g3">
          <div class="form-group"><label>First Name <span class="required">*</span></label><input type="text" name="first_name" id="asFirst" required></div>
          <div class="form-group"><label>Middle Name</label><input type="text" name="middle_name" placeholder="Leave blank if none"></div>
          <div class="form-group"><label>Last Name <span class="required">*</span></label><input type="text" name="last_name" id="asLast" required></div>
          <div class="form-group"><label>Suffix</label><input type="text" name="suffix" id="asSuffix" placeholder="Leave blank if none"></div>
        </div>
      </div>
      </div>
      <div class="wiz-panel" data-sstep="3">
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-address-book"></i> Contact &amp; Account</div>
        <div class="form-grid">
          <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <div class="input-prefix"><span><i class="fas fa-envelope"></i></span><input type="email" name="email" placeholder="admin@gmail.com" required></div>
          </div>
          <div class="form-group">
            <label>Username <span class="required">*</span></label>
            <div class="input-prefix"><span>@</span><input type="text" name="username" required></div>
          </div>
        </div>
        <div class="form-grid" style="margin-top:.7rem;">
          <div class="form-group"><label>Birthdate <span class="required">*</span></label><input type="date" name="birthdate" id="asBirth" max="<?= date('Y-m-d') ?>" required></div>
          <div class="form-group">
            <label>Password <button type="button" class="auto-btn" onclick="autoStuPw()">↺ Auto</button></label>
            <input type="text" name="password" id="asPass" required>
          </div>
        </div>
      </div>
      </div>
    </div>
    <div class="modal-footer">
      <div id="asSubmitProgressWrap" style="display:none;width:100%;margin-bottom:.6rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem;">
          <small id="asSubmitProgressText" style="color:var(--text-muted);font-weight:600;">Preparing…</small>
          <small id="asSubmitProgressPct" style="color:var(--text-muted);font-weight:700;">0%</small>
        </div>
        <div style="height:8px;background:var(--surface-muted);border-radius:999px;overflow:hidden;border:1px solid var(--border);">
          <div id="asSubmitProgressBar" style="height:100%;width:0%;background:linear-gradient(90deg,#14b88a,#0f8f73);transition:width .25s ease;"></div>
        </div>
      </div>
      <button type="button" class="btn btn-ghost" id="sBackBtn" onclick="stuPrevStep()" style="display:none;"><i class="fas fa-arrow-left"></i> Back</button>
      <button type="button" class="btn btn-ghost" onclick="document.getElementById('addStuForm').reset();updateStudentInputProgress();setStuWizardStep(1);">Reset</button>
      <button type="button" class="btn btn-ghost" onclick="closeModal('addStuModal')">Cancel</button>
      <button type="button" class="btn btn-primary" id="sNextBtn" onclick="stuNextStep()">Next <i class="fas fa-arrow-right"></i></button>
      <button type="button" class="btn btn-primary" id="asSaveBtn" onclick="saveStudent()" style="display:none;"><i class="fas fa-check"></i> Submit</button>
    </div>
    </form>
  </div>
</div>

<!-- EDIT STUDENT MODAL -->
<div class="modal-backdrop" id="editStuModal">
  <div class="modal-box modal-xl">
    <div class="modal-header">
      <div class="modal-title">
        <div class="modal-title-icon"><i class="fas fa-user-edit"></i></div>
        Edit Student
      </div>
      <button class="modal-close" onclick="closeModal('editStuModal')"><i class="fas fa-times"></i></button>
    </div>
    <form id="editStuForm">
    <input type="hidden" id="esId" name="id">
    <div class="modal-body">
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-id-card"></i> Basic Info</div>
        <div class="form-grid">
          <div class="form-group"><label>Student ID <span class="required">*</span></label><input type="text" name="student_number" id="esNum" required></div>
          <div class="form-group"><label>Course <span class="required">*</span></label>
            <select name="course_id" id="esCourse" required><option value="">— Select —</option><?= $courseOptsDept ?></select>
          </div>
        </div>
      </div>
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-user"></i> Full Name</div>
        <div class="form-grid g3">
          <div class="form-group"><label>First Name <span class="required">*</span></label><input type="text" name="first_name" id="esFirst" required></div>
          <div class="form-group"><label>Middle Name</label><input type="text" name="middle_name" id="esMid" placeholder="Leave blank if none"></div>
          <div class="form-group"><label>Last Name <span class="required">*</span></label><input type="text" name="last_name" id="esLast" required></div>
          <div class="form-group"><label>Suffix</label><input type="text" name="suffix" id="esSuffix" placeholder="Leave blank if none"></div>
        </div>
      </div>
      <div class="form-section">
        <div class="form-section-label"><i class="fas fa-address-book"></i> Contact &amp; Account</div>
        <div class="form-grid">
          <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <div class="input-prefix"><span><i class="fas fa-envelope"></i></span><input type="email" name="email" id="esEmail" placeholder="admin@gmail.com" required></div>
          </div>
          <div class="form-group">
            <label>Username <span class="required">*</span></label>
            <div class="input-prefix"><span>@</span><input type="text" name="username" id="esUser" required></div>
          </div>
        </div>
        <div class="form-grid" style="margin-top:.7rem;">
          <div class="form-group"><label>Birthdate</label><input type="date" name="birthdate" id="esBirth" max="<?= date('Y-m-d') ?>"></div>
          <div class="form-group">
            <label>New Password <span style="font-size:.68rem;font-weight:400;text-transform:none;">(blank = keep)</span></label>
            <input type="password" name="password" id="esPass">
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" onclick="closeModal('editStuModal')">Cancel</button>
      <button type="button" class="btn btn-primary" id="esSaveBtn" onclick="submitEditStu()"><i class="fas fa-check"></i> Save</button>
    </div>
    </form>
  </div>
</div>

<!-- ACCOUNT DETAILS MODAL -->
<div class="modal-backdrop" id="acctInfoModal">
  <div class="modal-box modal-xl">
    <div class="modal-header">
      <div class="modal-title">
        <div class="modal-title-icon"><i class="fas fa-id-badge" id="acctInfoIcon"></i></div>
        <span id="acctInfoTitle">Account Details</span>
      </div>
      <button class="modal-close" onclick="closeModal('acctInfoModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="acctInfoBody" style="color:var(--text-muted);padding:.4rem 0;">
        Select an account to view details.
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('acctInfoModal')">Close</button>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
/* ════ STATE ════ */
let allS=[], allC=[], allF=[], allSt=[];

let clsDrop = { subjects: [], faculty: [] };

let sLoaded=false, cLoaded=false, fLoaded=false, stLoaded=false;
const selectModes = { subjects:false, classes:false, faculty:false, students:false };
let sidebarCollapsed = false;
const pagerState = {
  subj: { page: 1, perPage: 10 },
  cls: { page: 1, perPage: 10 },
  fac: { page: 1, perPage: 10 },
  stu: { page: 1, perPage: 10 },
  ys:  { page: 1, perPage: 10 },
  yso: { page: 1, perPage: 10 }
};
const DEPT_PROGRAM_IDS = <?php echo json_encode(array_column($programs,'id')); ?>.map(String);
const DEPT_PROGRAM_COUNT = <?php echo (int)count($programs); ?>;
const DEPT_SINGLE_PROGRAM_ID = <?php echo count($programs) === 1 ? json_encode((string)$programs[0]['id']) : 'null'; ?>;
const DEPT_HAS_MULTI_PROGRAMS = DEPT_PROGRAM_COUNT > 1;
const ALL_PROGRAMS = <?php echo json_encode($allPrograms); ?>;
console.log('DEPT_PROGRAM_IDS:', DEPT_PROGRAM_IDS);
const YM = {1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year'};
const IS_ALSO_FACULTY = <?php echo $isAlsoFaculty ? 'true' : 'false'; ?>;

function closeAllCtrlMenus() {
  document.querySelectorAll('.tl-ctrl-menu.open').forEach(m => m.classList.remove('open'));
  document.querySelectorAll('.tl-ctrl-btn.active').forEach(b => b.classList.remove('active'));
}
function toggleCtrlMenu(id, ev) {
  ev?.stopPropagation();
  const m = document.getElementById(id);
  if (!m) return;
  const open = !m.classList.contains('open');
  closeAllCtrlMenus();
  if (open) {
    m.classList.add('open');
    ev?.currentTarget?.classList.add('active');
  }
}
function enterSelectMode(pageKey) {
  selectModes[pageKey] = true;
  document.querySelector(`.page[data-page="${pageKey}"]`)?.classList.add('select-mode');
  closeAllCtrlMenus();
  if (pageKey === 'subjects') subjUpdateBulk();
  if (pageKey === 'classes') clsUpdateBulk();
  if (pageKey === 'faculty') facUpdateBulk();
  if (pageKey === 'students') stuUpdateBulk();
}
function exitSelectMode(pageKey) {
  selectModes[pageKey] = false;
  document.querySelector(`.page[data-page="${pageKey}"]`)?.classList.remove('select-mode');
  if (pageKey === 'subjects') subjClearSelection();
  if (pageKey === 'classes') clsClearSelection();
  if (pageKey === 'faculty') facClearSelection();
  if (pageKey === 'students') stuClearSelection();
}

function paginateRows(key, list) {
  const st = pagerState[key];
  const total = list.length;
  const totalPages = Math.max(1, Math.ceil(total / st.perPage));
  if (st.page > totalPages) st.page = totalPages;
  if (st.page < 1) st.page = 1;
  const start = (st.page - 1) * st.perPage;
  const rows = list.slice(start, start + st.perPage);
  return { rows, total, totalPages, page: st.page, start };
}

function renderPager(elId, key, total, totalPages, page, rerenderFn) {
  const el = document.getElementById(elId);
  if (!el) return;
  if (!total) { el.innerHTML = ''; return; }

  const start = (page - 1) * pagerState[key].perPage + 1;
  const end = Math.min(total, page * pagerState[key].perPage);
  const mkBtn = (label, disabled, cb, active=false) =>
    `<button class="pager-btn${active ? ' active' : ''}" ${disabled ? 'disabled' : ''} onclick="${cb}">${label}</button>`;

  const pages = [];
  const from = Math.max(1, page - 1);
  const to = Math.min(totalPages, page + 1);
  for (let p = from; p <= to; p++) {
    pages.push(mkBtn(p, false, `${cbName(key)}(${p})`, p === page));
  }

  el.innerHTML = `
    <div class="pager-meta">Showing <strong>${start}</strong>-${end} of <strong>${total}</strong></div>
    <div class="pager-controls">
      ${mkBtn('Prev', page <= 1, `${cbName(key)}(${page - 1})`)}
      ${pages.join('')}
      ${mkBtn('Next', page >= totalPages, `${cbName(key)}(${page + 1})`)}
    </div>`;

  window.__pagerRerender = window.__pagerRerender || {};
  window.__pagerRerender[key] = rerenderFn;
}

function cbName(key) { return `__gotoPage_${key}`; }
['subj','cls','fac','stu','ys','yso'].forEach(key => {
  window[cbName(key)] = function(p) {
    pagerState[key].page = p;
    if (window.__pagerRerender && typeof window.__pagerRerender[key] === 'function') {
      window.__pagerRerender[key]();
    }
  };
});

/* ════ 3-DOT MENU PORTAL ════ */
(function() {
  let _portal = null, _openId = null;
  function getPortal() {
    if (!_portal) {
      _portal = document.createElement('div');
      _portal.id = 'subDotMenuPortal';
      _portal.style.cssText = 'position:fixed;z-index:99999;display:none;';
      document.body.appendChild(_portal);
    }
    return _portal;
  }
  function closePortal() {
    const p = getPortal();
    p.style.display = 'none'; p.innerHTML = '';
    document.querySelectorAll('.dot-menu-btn.open').forEach(b => b.classList.remove('open'));
    _openId = null;
  }
  document.addEventListener('click', e => {
    if (!e.target.closest('.dot-menu-btn') && !e.target.closest('#subDotMenuPortal')) closePortal();
  });

  function btn(label, icon, onclick, isDanger) {
    const color = isDanger ? 'var(--danger)' : 'var(--text)';
    const hoverBg = isDanger ? '#fdecea' : 'var(--primary-light)';
    const hoverColor = isDanger ? 'var(--danger)' : 'var(--primary)';
    return `<button onclick="${onclick};document.getElementById('subDotMenuPortal').style.display='none';"
      style="display:flex;align-items:center;gap:.65rem;padding:.6rem 1rem;font-size:.83rem;font-weight:600;
             color:${color};background:none;border:none;width:100%;text-align:left;cursor:pointer;font-family:inherit;"
      onmouseover="this.style.background='${hoverBg}';this.style.color='${hoverColor}'"
      onmouseout="this.style.background='none';this.style.color='${color}'">
      <i class="fas fa-${icon}" style="width:14px;text-align:center;font-size:.78rem;opacity:.7;"></i> ${label}
    </button>`;
  }
  function divider() { return `<div style="height:1px;background:var(--border);margin:.2rem 0;"></div>`; }

  window.openSubDotMenu = function(type, id, btnEl) {
    const key = type + '_' + id;
    if (_openId === key) { closePortal(); return; }
    closePortal(); _openId = key; btnEl.classList.add('open');

    let items = '';
    if (type === 'subj') {
      const s = allS.find(x => String(x.id) === String(id));
      const ac = s ? +s.is_active === 1 : true;
      const canEdit = s ? +s.can_edit === 1 : true;
      items = (canEdit ? btn('Edit', 'pencil-alt', `editSubj('${id}')`) : btn('View Owner Only', 'lock', `openSubjectRow('${id}')`)) +
              btn(ac ? 'Deactivate' : 'Activate', ac ? 'ban' : 'check-circle', `toggleSubj('${id}')`) +
              (canEdit ? divider() + btn('Delete', 'trash', `delSubj('${id}')`, true) : '');
    } else if (type === 'cls') {
      const c = allC.find(x => String(x.id) === String(id));
      const ac = c ? +c.is_active === 1 : true;
      items = btn('Edit', 'pencil-alt', `editClass('${id}')`) +
              btn(ac ? 'Deactivate' : 'Activate', ac ? 'ban' : 'check-circle', `toggleCls('${id}',${ac?0:1})`) +
              divider() +
              btn('Delete', 'trash', `delClass('${id}')`, true);
    } else if (type === 'fac') {
      const f = allF.find(x => String(x.id) === String(id));
      const ac = f ? +f.is_active === 1 : true;
      const fn = f ? f.faculty_number : id;
      items = btn('Edit', 'pencil-alt', `editFac('${fn}')`) +
              btn(ac ? 'Deactivate' : 'Activate', ac ? 'ban' : 'check-circle', `toggleFac('${fn}')`) +
              divider() +
              btn('Delete', 'trash', `delFac('${id}')`, true);
    } else if (type === 'stu') {
      const s = allSt.find(x => String(x.id) === String(id));
      const ac = s ? +s.is_active === 1 : true;
      items = btn('Edit', 'pencil-alt', `editStu(${id})`) +
              btn(ac ? 'Deactivate' : 'Activate', ac ? 'ban' : 'check-circle', `toggleStu(${id})`) +
              divider() +
              btn('Delete', 'trash', `delStu(${id})`, true);
    }

    const p = getPortal();
    p.innerHTML = `<div style="background:var(--surface);border:1.5px solid var(--border);border-radius:12px;
                    box-shadow:0 8px 32px rgba(0,0,0,.22);overflow:hidden;min-width:165px;">${items}</div>`;
    const rect = btnEl.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    p.style.display = 'block';
    p.style.right = Math.max(8, window.innerWidth - rect.right) + 'px';
    p.style.left  = 'auto';
    p.style.top   = spaceBelow > 160 ? (rect.bottom + 6) + 'px' : 'auto';
    p.style.bottom = spaceBelow <= 160 ? (window.innerHeight - rect.top + 6) + 'px' : 'auto';
  };
})();
/* ════ TOAST ════ */
function toast(msg, type='success') {
  Swal.fire({ toast:true, position:'top-end', icon:type, title:msg, showConfirmButton:false, timer:3200, timerProgressBar:true });
}

/* ════ ROLE SWITCHER ════ */
function switchToFacultyView() {
  Swal.fire({
    title: 'Switch to Faculty View?',
    html: `<div style="font-size:.9rem;color:#5f6368;">You'll be taken to your <strong>Faculty Dashboard</strong>.<br>Your dean session stays active — just switch back anytime.</div>`,
    icon: 'info',
    showCancelButton: true,
    confirmButtonColor: '#1a9e78',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-chalkboard-teacher" style="margin-right:.4rem;"></i> Yes, switch',
    cancelButtonText: 'Stay here',
  }).then(result => {
    if (result.isConfirmed) {
      window.location.href = 'facultyUI.php';
    }
  });
}

/* ════ INIT ════ */
document.addEventListener('DOMContentLoaded', () => {
  initNav();
  initSidebarToggle();
  initDarkMode();
  document.getElementById('btnAddStudent').addEventListener('click', () => openModal('addStuModal'));
  document.querySelector('.nav-avatar')?.addEventListener('click', viewDeanProfile);
  document.querySelector('.sidebar-user')?.addEventListener('click', viewDeanProfile);
  syncDeanProfileSilently();
  fetchActiveSem();

  // Pre-fill badges from PHP stats
  document.getElementById('subjectBadge').textContent = <?= $stats['subjects'] ?>;
  document.getElementById('classBadge').textContent   = <?= $stats['classes'] ?>;
  document.getElementById('studentBadge').textContent = <?= $stats['students'] ?>;
  document.getElementById('facultyBadge').textContent = <?= $stats['faculty'] ?>;

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.tl-ctrl-wrap')) closeAllCtrlMenus();
  });
});

function initSidebarToggle() {
  const menuBtn = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  const main    = document.getElementById('main');
  const overlay = document.getElementById('overlay');
  if (!menuBtn || !sidebar || !main || !overlay) return;

  menuBtn.addEventListener('click', () => {
    if (window.innerWidth <= 768) {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('show');
      return;
    }

    sidebarCollapsed = !sidebarCollapsed;
    sidebar.classList.toggle('collapsed', sidebarCollapsed);
    main.classList.toggle('collapsed', sidebarCollapsed);
  });

  overlay.addEventListener('click', closeMobileSidebar);
  window.addEventListener('resize', () => {
    if (window.innerWidth > 768) closeMobileSidebar();
  });
}

/* ════ NAV ════ */
function initNav() {
  function openPage(pg, persist = true) {
    document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    const btn = document.querySelector(`.nav-item[data-page="${pg}"]`);
    if (!btn) return;
    btn.classList.add('active');
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.querySelector(`.page[data-page="${pg}"]`)?.classList.add('active');
    if (persist) localStorage.setItem('subadmin_last_page', pg);
    if (pg === 'yearsections' && !ysLoaded) { loadYsSectionDean(); ysLoaded = true; }
    if (pg === 'subjects' && !sLoaded) { loadSubjects(); sLoaded = true; }
    if (pg === 'classes'  && !cLoaded) { loadClassDrop(); loadClasses(); cLoaded = true; }
    if (pg === 'faculty'  && !fLoaded) { loadFaculty(); fLoaded = true; }
    if (pg === 'students' && !stLoaded) { loadStudents(); stLoaded = true; }
    if (pg === 'subjectpresets' && !presetLoaded) { initPresets(); presetLoaded = true; }
    if (window.innerWidth <= 768) closeMobileSidebar();
  }

  document.querySelectorAll('.nav-item[data-page]').forEach(btn => {
    btn.addEventListener('click', () => {
      openPage(btn.dataset.page, true);
    });
  });

  const remembered = localStorage.getItem('subadmin_last_page') || '';
  const valid = new Set(['dashboard','subjects','classes','faculty','students','yearsections','subjectpresets']);
  if (valid.has(remembered) && remembered !== 'dashboard') openPage(remembered, false);
  document.body.classList.remove('nav-booting');
}

/* ════ YEAR & SECTIONS (Dean — full CRUD, dept programs only) ════ */
let ysLoaded       = false;
let ysConfigs      = [];
let ysCurrentCourse = null;
const YL_LABELS    = {1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year',5:'5th Year'};

function loadYsSectionDean() {
    if (DEPT_PROGRAM_COUNT >= 2) ysLoadAllOverview();
    if (DEPT_PROGRAM_COUNT === 1 && DEPT_SINGLE_PROGRAM_ID) {
        const sel = document.getElementById('ysProgSelect');
        if (sel) sel.value = DEPT_SINGLE_PROGRAM_ID;
        ysOnProgramChange(DEPT_SINGLE_PROGRAM_ID);
    }
}

function ysOnProgramChange(courseId) {
    ysCurrentCourse = courseId || null;
    ysResetForm();

    const tableCard = document.getElementById('ysTableCard');
    const label     = document.getElementById('ysTableLabel');

    if (!courseId) {
        tableCard.style.display = 'none';
        return;
    }

    // Find program label from PHP-injected list
    const sel  = document.getElementById('ysProgSelect');
    const opt  = sel.options[sel.selectedIndex];
    label.textContent   = opt ? opt.textContent.trim() : courseId;
    tableCard.style.display = '';

    ysLoadTable(courseId);
}

function ysLoadTable(courseId) {
    const tb = document.querySelector('#ysTable tbody');
    tb.innerHTML = '<tr><td colspan="4" class="tl-td text-center" style="padding:1.5rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…</td></tr>';

    fetch('API/Admin/fetch_year_section_config.php?course_id=' + encodeURIComponent(courseId))
        .then(r => r.json())
        .then(res => {
            ysConfigs = res.data || [];
            ysRenderTable(ysConfigs);
        })
        .catch(() => toast('Failed to load configs', 'error'));
}

function ysRenderTable(list) {
    const tb = document.querySelector('#ysTable tbody');
    if (!list.length) {
        tb.innerHTML = '<tr><td colspan="4" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);">No configs yet for this program. Add one above.</td></tr>';
        renderPager('ysPager','ys',0,1,1,() => ysRenderTable(ysConfigs));
        return;
    }
    const pg = paginateRows('ys', list);
    tb.innerHTML = pg.rows.map((r, i) => {
        return `<tr class="tl-tr">
          <td class="tl-td">${pg.start + i + 1}</td>
          <td class="tl-td"><span class="pill pill-year">${YL_LABELS[r.year_level] || r.year_level}</span></td>
          <td class="tl-td">
            <strong style="font-size:1.05rem;">${r.section_count}</strong>
            <span style="color:var(--text-muted);font-size:.78rem;"> section${r.section_count != 1 ? 's' : ''}</span>
          </td>
          <td class="tl-td" style="text-align:center;padding:0 4px;">
            <div class="dot-menu-wrap"><button class="dot-menu-btn" onclick="openYsDotMenu(${r.id},this)" title="Actions"><i class="fas fa-ellipsis-v"></i></button></div>
          </td>
        </tr>`;
    }).join('');
    renderPager('ysPager','ys',pg.total,pg.totalPages,pg.page,() => ysRenderTable(ysConfigs));
}
/* ── YS 3-DOT MENU PORTAL ── */
(function() {
    let _p = null, _openId = null;
    function getP() {
        if (!_p) { _p = document.createElement('div'); _p.id = 'ysDotMenuPortal'; _p.style.cssText = 'position:fixed;z-index:99999;display:none;'; document.body.appendChild(_p); }
        return _p;
    }
    function closeP() {
        const p = getP(); p.style.display = 'none'; p.innerHTML = '';
        document.querySelectorAll('#ysTable .dot-menu-btn.open').forEach(b => b.classList.remove('open'));
        _openId = null;
    }
    document.addEventListener('click', e => {
        if (!e.target.closest('#ysTable .dot-menu-btn') && !e.target.closest('#ysDotMenuPortal')) closeP();
    });
    window.openYsDotMenu = function(id, btnEl) {
        if (_openId == id) { closeP(); return; }
        closeP(); _openId = id; btnEl.classList.add('open');
        const p = getP();
        p.innerHTML = `<div style="background:var(--surface);border:1.5px solid var(--border);border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.22);overflow:hidden;min-width:145px;">
          <button onclick="ysEditRow(${id});document.getElementById('ysDotMenuPortal').style.display='none';"
            style="display:flex;align-items:center;gap:.65rem;padding:.6rem 1rem;font-size:.83rem;font-weight:600;color:var(--text);background:none;border:none;width:100%;text-align:left;cursor:pointer;font-family:inherit;"
            onmouseover="this.style.background='var(--primary-light)';this.style.color='var(--primary)'"
            onmouseout="this.style.background='none';this.style.color='var(--text)'">
            <i class="fas fa-pen" style="width:14px;font-size:.78rem;opacity:.65;"></i> Edit
          </button>
          <div style="height:1px;background:var(--border);margin:.2rem 0;"></div>
          <button onclick="ysDelRow(${id});document.getElementById('ysDotMenuPortal').style.display='none';"
            style="display:flex;align-items:center;gap:.65rem;padding:.6rem 1rem;font-size:.83rem;font-weight:600;color:var(--danger);background:none;border:none;width:100%;text-align:left;cursor:pointer;font-family:inherit;"
            onmouseover="this.style.background='#fdecea'"
            onmouseout="this.style.background='none'">
            <i class="fas fa-trash" style="width:14px;font-size:.78rem;opacity:.65;"></i> Delete
          </button>
        </div>`;
        const rect = btnEl.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        p.style.display = 'block';
        p.style.right = Math.max(8, window.innerWidth - rect.right) + 'px';
        p.style.left  = 'auto';
        p.style.top   = spaceBelow > 105 ? (rect.bottom + 6) + 'px' : 'auto';
        p.style.bottom = spaceBelow <= 105 ? (window.innerHeight - rect.top + 6) + 'px' : 'auto';
    };
})();

function ysEditRow(id) {
    const r = ysConfigs.find(x => x.id == id);
    if (!r) return;
    document.getElementById('ysEditId').value      = r.id;
    document.getElementById('ysYearLevel').value   = r.year_level;
    document.getElementById('ysSectionCount').value = r.section_count;
    document.getElementById('ysFormTitle').textContent = 'Edit Year Level Config';
    openModal('ysModal');
}

function ysResetForm() {
    document.getElementById('ysEditId').value       = '';
    document.getElementById('ysYearLevel').value    = '';
    document.getElementById('ysSectionCount').value = '';
    document.getElementById('ysFormTitle').textContent = 'Add Year Level Config';
}

function ysOpenModal() {
    if (!ysCurrentCourse) { toast('Select a program first.', 'error'); return; }
    ysResetForm();
    openModal('ysModal');
}

async function ysSaveConfig() {
    const courseId    = ysCurrentCourse;
    const year        = parseInt(document.getElementById('ysYearLevel').value);
    const count       = parseInt(document.getElementById('ysSectionCount').value);
    const editId      = parseInt(document.getElementById('ysEditId').value) || null;

    if (!courseId)                          { toast('Select a program first.', 'error'); return; }
    if (!year)                              { toast('Select a year level.', 'error'); return; }
    if (!count || count < 1 || count > 30) { toast('Enter a valid section count (1–30).', 'error'); return; }

    try {
        const d = await (await fetch('API/Admin/save_year_section_config.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: editId, course_id: courseId, year_level: year, section_count: count})
        })).json();
        toast(d.message, d.status);
        if (d.status === 'success') {
            closeModal('ysModal');
            ysResetForm();
            ysLoadTable(courseId);
            if (DEPT_PROGRAM_COUNT >= 2) ysLoadAllOverview();
        }
    } catch { toast('Server error.', 'error'); }
}

function ysDelRow(id) {
    Swal.fire({
        title: 'Delete this config?',
        text: 'The section dropdown for this year level will be removed.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#d93025', confirmButtonText: 'Yes, delete'
    }).then(async r => {
        if (!r.isConfirmed) return;
        try {
            const d = await (await fetch('API/Admin/delete_year_section_config.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id})
            })).json();
            toast(d.message, d.status);
            if (d.status === 'success') {
                if (ysCurrentCourse) ysLoadTable(ysCurrentCourse);
                if (DEPT_PROGRAM_COUNT >= 2) ysLoadAllOverview();
            }
        } catch { toast('Server error.', 'error'); }
    });
}

async function ysLoadAllOverview() {
    if (DEPT_PROGRAM_COUNT < 2) return;
    const tbody   = document.getElementById('ysOverviewBody');
    const progIds = DEPT_PROGRAM_IDS;
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="7" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…</td></tr>';
    try {
        const res  = await fetch('API/Admin/fetch_year_section_config.php');
        const data = await res.json();
        const all  = (data.data || []).filter(r => progIds.includes(String(r.course_id)));
        if (!all.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);">No section configs set yet. Select a program above and add one.</td></tr>';
            renderPager('ysOverviewPager','yso',0,1,1,() => ysLoadAllOverview());
            return;
        }
        const grouped = {};
        all.forEach(r => {
            if (!grouped[r.course_id]) grouped[r.course_id] = {code: r.course_code, name: r.course_name, years: {}};
            grouped[r.course_id].years[r.year_level] = r.section_count;
        });
        const numCell = v => v
            ? `<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:var(--primary-light);color:var(--primary);font-weight:700;font-size:.85rem;">${v}</span>`
            : `<span style="color:var(--border);font-size:1rem;">◇</span>`;
        const ovList = Object.values(grouped);
        const pg = paginateRows('yso', ovList);
        tbody.innerHTML = pg.rows.map((g, i) => `
          <tr class="tl-tr">
            <td class="tl-td">${pg.start + i + 1}</td>
            <td class="tl-td"><span class="pill pill-code" style="font-family:monospace;">${esc(g.code)}</span></td>
            <td class="tl-td td-name">${esc(g.name)}</td>
            <td class="tl-td text-center">${numCell(g.years[1])}</td>
            <td class="tl-td text-center">${numCell(g.years[2])}</td>
            <td class="tl-td text-center">${numCell(g.years[3])}</td>
            <td class="tl-td text-center">${numCell(g.years[4])}</td>
          </tr>`).join('');
        renderPager('ysOverviewPager','yso',pg.total,pg.totalPages,pg.page,() => ysLoadAllOverview());
    } catch {
        tbody.innerHTML = '<tr><td colspan="7" class="tl-td text-center" style="padding:1.5rem;color:var(--text-muted);">Failed to load overview.</td></tr>';
        renderPager('ysOverviewPager','yso',0,1,1,() => ysLoadAllOverview());
    }
}
function closeMobileSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('show');
}

/* ════ DARK MODE ════ */
function initDarkMode() {
  const body = document.body;
  const btn  = document.getElementById('darkToggle');
  if (localStorage.getItem('tl_dark') === '1') { body.classList.add('dark'); btn.querySelector('i').className = 'fas fa-sun'; }
  btn.addEventListener('click', () => {
    const dark = body.classList.toggle('dark');
    localStorage.setItem('tl_dark', dark ? '1' : '0');
    btn.querySelector('i').className = dark ? 'fas fa-sun' : 'fas fa-moon';
  });
}

/* ════ MODAL ════ */
function openModal(id) {
  document.getElementById(id).classList.add('show');
  if (id === 'addStuModal') setTimeout(() => { setStuWizardStep(1); updateStudentInputProgress(); }, 0);
  if (id === 'facModal') setTimeout(() => { setFacWizardStep(1); updateFacultyInputProgress(); }, 0);
}
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.addEventListener('click', e => {
  document.querySelectorAll('.modal-backdrop.show').forEach(m => {
    if (e.target === m) m.classList.remove('show');
  });
});

/* ════ ACTIVE SEMESTER ════ */
function fetchActiveSem() {
  fetch('API/Admin/get_semester.php')
    .then(r => r.json())
    .then(res => {
      if (res && res.current) {
        window.activeSemester   = res.current.semester    || '';
        window.activeSchoolYear = res.current.school_year || '';
        document.getElementById('cSemDisplay').textContent = window.activeSemester   || '—';
        document.getElementById('cSYDisplay').textContent  = window.activeSchoolYear || '—';
      }
    }).catch(() => {});
}

/* ════ SEARCH HELPER ════ */
function wireSearch(inpId, clrId, cntId, numId, getArr, filterFn, renderFn) {
  const inp = document.getElementById(inpId);
  const clr = document.getElementById(clrId);
  if (!inp || !clr) return;
  inp.addEventListener('input', function() {
    const q = this.value.trim();
    clr.classList.toggle('visible', q.length > 0);
    const list = filterFn(getArr(), q);
    const cntEl = cntId ? document.getElementById(cntId) : null;
    const numEl = numId ? document.getElementById(numId) : null;
    if (q) {
      if (numEl && !/^(INPUT|TEXTAREA|SELECT)$/.test(numEl.tagName)) numEl.textContent = list.length;
      if (cntEl) cntEl.style.display = '';
    } else {
      if (cntEl) cntEl.style.display = 'none';
    }
    renderFn(list);
  });
  clr.addEventListener('click', () => { inp.value = ''; inp.dispatchEvent(new Event('input')); inp.focus(); });
}
function fg(arr, q, fields) {
  if (!q) return arr;
  const lq = q.toLowerCase();
  return arr.filter(i => fields.some(f => String(i[f] || '').toLowerCase().includes(lq)));
}
function getFilteredSubjects() {
  const q = (document.getElementById('sSearch')?.value || '').trim();
  return fg(allS, q, ['subject_code','subject_name','course_code','course_name','year_level']);
}
function getFilteredClasses() {
  const q = (document.getElementById('cSearch')?.value || '').trim();
  return fg(allC, q, ['class_code','subject_name','subject_code','faculty_name','class_semester','section']);
}
function getFilteredFaculty() {
  const q = (document.getElementById('fSearch')?.value || '').trim();
  return fg(allF, q, ['faculty_number','first_name','last_name','email','username']);
}
function getFilteredStudents() {
  const q = (document.getElementById('stSearch')?.value || '').trim();
  return fg(allSt, q, ['student_number','first_name','last_name','email','course_code']);
}
function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmt12(t) { if (!t) return ''; const [h, m] = t.split(':').map(Number); return `${h % 12 || 12}:${String(m).padStart(2,'0')} ${h >= 12 ? 'PM' : 'AM'}`; }
function picUrl(p) {
  const v = String(p || '').trim();
  if (!v) return '';
  if (/^https?:\/\//i.test(v)) return v;
  return v.replace(/^\/+/, '');
}
function avatarChip(name, photo, cls='mini-avatar') {
  const url = picUrl(photo);
  const initials = (String(name || '').trim().split(/\s+/).slice(0,2).map(x => x[0] || '').join('') || 'U').toUpperCase();
  return url
    ? `<span class="${cls}"><img src="${esc(url)}" alt="${esc(name || 'Avatar')}"></span>`
    : `<span class="${cls}">${esc(initials)}</span>`;
}
function fmtDT(v) {
  if (!v) return '—';
  const d = new Date(v.replace(' ', 'T'));
  if (isNaN(d.getTime())) return esc(v);
  return d.toLocaleString();
}
function rowClickGuard(ev) {
  return !!ev.target.closest('button, a, input, label, .dot-menu-wrap, .otp-toggle-wrap');
}
function openAcctInfoModal(title, html) {
  document.getElementById('acctInfoTitle').textContent = title;
  document.getElementById('acctInfoBody').innerHTML = html;
  openModal('acctInfoModal');
}
function openAcctInfoModalWithIcon(title, html, icon) {
  const iconEl = document.getElementById('acctInfoIcon');
  if (iconEl) iconEl.className = `fas ${icon || 'fa-id-badge'}`;
  openAcctInfoModal(title, html);
}
function detailInitials(parts) {
  const tokens = (parts || []).filter(Boolean).map(v => String(v).trim()).filter(Boolean);
  if (!tokens.length) return 'NA';
  const first = tokens[0]?.[0] || '';
  const last = tokens.length > 1 ? (tokens[tokens.length - 1]?.[0] || '') : (tokens[0]?.[1] || '');
  return (first + last).toUpperCase() || 'NA';
}
function detailCard(label, value, extraClass = '') {
  return `<div class="acct-detail-card"><div class="acct-detail-label">${esc(label)}</div><div class="acct-detail-value ${extraClass}">${esc(value || '—')}</div></div>`;
}
function detailTableSection(title, icon, headers, rowsHtml, emptyText) {
  return `
    <div class="acct-detail-section">
      <div class="acct-detail-section-title"><i class="fas fa-${icon}"></i> ${esc(title)}</div>
      <div class="acct-detail-table">
        <div class="acct-detail-table-head">${headers.map(h => `<div>${esc(h)}</div>`).join('')}</div>
        ${rowsHtml || `<div class="acct-detail-empty"><i class="far fa-folder-open"></i>${esc(emptyText)}</div>`}
      </div>
    </div>`;
}
async function viewDeanProfile(ev) {
  if (ev && rowClickGuard(ev)) return;
  openAcctInfoModal('Dean Profile', '<div style="padding:1rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.45rem;"></i>Loading profile…</div>');
  try {
    const d = await (await fetch('API/Dean/get_my_profile.php')).json();
    if (d.status !== 'success') { toast(d.message || 'Failed to load profile', 'error'); return; }
    const p = d.profile || {};
    applyDeanProfileToUI(p);
    const linkedTxt = +p.faculty_linked === 1
      ? `Yes${p.faculty_number ? ` (${esc(p.faculty_number)})` : ''}`
      : 'No';
    const html = `
      <div class="info-grid">
        <div class="info-item"><div class="info-k">Full Name</div><div class="info-v">${esc(p.name || '—')}</div></div>
        <div class="info-item"><div class="info-k">Role</div><div class="info-v">${esc(p.role || 'Dean')}</div></div>
        <div class="info-item"><div class="info-k">Department</div><div class="info-v">${esc((p.department_code || '—') + ' - ' + (p.department_name || ''))}</div></div>
        <div class="info-item"><div class="info-k">Username</div><div class="info-v">@${esc(p.username || '—')}</div></div>
        <div class="info-item"><div class="info-k">Email</div><div class="info-v">${esc(p.email || '—')}</div></div>
        <div class="info-item"><div class="info-k">Phone</div><div class="info-v">${esc(p.phone || '—')}</div></div>
        <div class="info-item"><div class="info-k">Assigned As Dean</div><div class="info-v">${fmtDT(p.assigned_at)}</div></div>
        <div class="info-item"><div class="info-k">Account Created</div><div class="info-v">${fmtDT(p.account_created_at)}</div></div>
        <div class="info-item"><div class="info-k">Linked Faculty Account</div><div class="info-v">${linkedTxt}</div></div>
        <div class="info-item"><div class="info-k">Faculty Account Created</div><div class="info-v">${fmtDT(p.faculty_created_at)}</div></div>
      </div>`;
    openAcctInfoModal('Dean Profile', html);
  } catch {
    toast('Failed to load profile', 'error');
  }
}
function applyDeanProfileToUI(p) {
  if (!p) return;
  const fullName = p.name || 'Dean';
  const role = p.role || 'Dean';
  const deptCode = p.department_code || '—';
  const initials = (p.first_name?.[0] || 'D').toUpperCase() + (p.last_name?.[0] || '').toUpperCase();
  const photo = picUrl(p.profile_picture);
  document.querySelectorAll('.nav-avatar').forEach(el => {
    el.innerHTML = photo ? `<img src="${esc(photo)}" alt="Dean" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">` : initials;
  });
  const sAvatar = document.querySelector('.s-avatar');
  if (sAvatar) sAvatar.innerHTML = photo ? `<img src="${esc(photo)}" alt="Dean" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">` : initials;
  const sName = document.querySelector('.s-name'); if (sName) sName.textContent = fullName;
  const sRole = document.querySelector('.s-role'); if (sRole) sRole.textContent = `${role} · ${deptCode}`;
  const navDept = document.querySelector('.nav-dept-badge'); if (navDept) navDept.innerHTML = `<i class="fas fa-university" style="font-size:.6rem;"></i> ${esc(deptCode)}`;
  const navRole = document.querySelector('.nav-role-badge'); if (navRole) navRole.innerHTML = `<i class="fas fa-user-tie" style="font-size:.6rem;"></i> ${esc(role)}`;
  const footer = document.querySelector('.footer-dept-badge'); if (footer) footer.innerHTML = `<i class="fas fa-university"></i> ${esc(deptCode)} — ${esc(role)}`;
}
async function syncDeanProfileSilently() {
  try {
    const d = await (await fetch('API/Dean/get_my_profile.php')).json();
    if (d.status === 'success') applyDeanProfileToUI(d.profile || {});
  } catch {}
}
async function viewFacultyProfile(id, ev) {
  if (ev && rowClickGuard(ev)) return;
  openAcctInfoModalWithIcon('Faculty Details', '<div style="padding:1rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.45rem;"></i>Loading details…</div>', 'fa-id-card');
  try {
    const d = await (await fetch(`API/Dean/get_faculty_profile.php?id=${encodeURIComponent(id)}`)).json();
    if (d.status !== 'success') { toast(d.message || 'Failed to load details', 'error'); return; }
    const p = d.profile || {};
    const fullName = [p.first_name,p.middle_name,p.last_name,p.suffix].filter(Boolean).join(' ');
    const initials = detailInitials([p.first_name, p.last_name]);
    const photo = picUrl(p.profile_picture);
    const rows = (d.classes || []).map(c => `
      <div class="acct-detail-table-row">
        <div class="acct-detail-table-cell"><strong>${esc(c.class_code || '—')}</strong></div>
        <div class="acct-detail-table-cell"><strong>${esc(c.subject_code || '—')}</strong><div class="acct-detail-table-sub">${esc(c.subject_name || '—')}</div></div>
        <div class="acct-detail-table-cell">${esc(c.class_semester || '—')}</div>
        <div class="acct-detail-table-cell">${esc(String(c.student_count ?? 0))}</div>
      </div>`).join('');
    const html = `
      <div class="acct-detail-layout">
        <div class="acct-detail-side">
          <div class="acct-detail-avatar">${photo ? `<img src="${esc(photo)}" alt="${esc(fullName || 'Faculty')}">` : esc(initials)}</div>
          <div class="acct-detail-badges">
            <span class="acct-detail-pill"><i class="fas fa-graduation-cap"></i> Faculty</span>
            <span class="acct-detail-pill ${+p.is_active === 1 ? '' : 'warn'}"><i class="fas fa-circle" style="font-size:.45rem;"></i> ${+p.is_active === 1 ? 'Active' : 'Inactive'}</span>
          </div>
        </div>
        <div class="acct-detail-main">
          <div class="acct-detail-cards">
            ${detailCard('Faculty ID', p.faculty_number || '—', 'mono')}
            ${detailCard('Full Name', fullName || '—')}
            ${detailCard('Email', p.email || '—')}
            ${detailCard('Phone', p.phone || '—')}
            ${detailCard('Username', p.username ? '@' + p.username : '—')}
            ${detailCard('Created At', fmtDT(p.profile_created_at || p.account_created_at))}
          </div>
        </div>
      </div>
      ${detailTableSection('Classes Handled', 'school', ['Class', 'Subject', 'Semester', 'Students'], rows, 'No classes found.')}`;
    openAcctInfoModalWithIcon('Faculty Details', html, 'fa-id-card');
  } catch {
    toast('Failed to load details', 'error');
  }
}
async function viewStudentProfile(id, ev) {
  if (ev && rowClickGuard(ev)) return;
  openAcctInfoModalWithIcon('Student Details', '<div style="padding:1rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.45rem;"></i>Loading details…</div>', 'fa-user-graduate');
  try {
    const d = await (await fetch(`API/Dean/get_student_profile.php?id=${encodeURIComponent(id)}`)).json();
    if (d.status !== 'success') { toast(d.message || 'Failed to load details', 'error'); return; }
    const p = d.profile || {};
    const fullName = [p.first_name,p.middle_name,p.last_name,p.suffix].filter(Boolean).join(' ');
    const initials = detailInitials([p.first_name, p.last_name]);
    const photo = picUrl(p.profile_picture);
    const yearNum = parseInt(p.year_level, 10);
    const yearLabel = !yearNum ? 'Student'
      : yearNum === 1 ? '1st Year'
      : yearNum === 2 ? '2nd Year'
      : yearNum === 3 ? '3rd Year'
      : yearNum === 4 ? '4th Year'
      : `${yearNum}th Year`;
    const rows = (d.classes || []).map(c => `
      <div class="acct-detail-table-row">
        <div class="acct-detail-table-cell"><strong>${esc(c.class_code || '—')}</strong></div>
        <div class="acct-detail-table-cell"><strong>${esc(c.subject_code || '—')}</strong><div class="acct-detail-table-sub">${esc(c.subject_name || '—')}</div></div>
        <div class="acct-detail-table-cell">${esc(c.class_semester || '—')}</div>
        <div class="acct-detail-table-cell">${esc(fmtDT(c.enrolled_at))}</div>
      </div>`).join('');
    const html = `
      <div class="acct-detail-layout">
        <div class="acct-detail-side">
          <div class="acct-detail-avatar">${photo ? `<img src="${esc(photo)}" alt="${esc(fullName || 'Student')}">` : esc(initials)}</div>
          <div class="acct-detail-badges">
            <span class="acct-detail-pill secondary"><i class="fas fa-user-graduate"></i> ${esc(yearLabel)}</span>
            <span class="acct-detail-pill ${+p.is_active === 1 ? '' : 'warn'}"><i class="fas fa-circle" style="font-size:.45rem;"></i> ${+p.is_active === 1 ? 'Active' : 'Inactive'}</span>
          </div>
        </div>
        <div class="acct-detail-main">
          <div class="acct-detail-cards">
            ${detailCard('Student ID', p.student_number || '—', 'mono')}
            ${detailCard('Full Name', fullName || '—')}
            ${detailCard('Email', p.email || '—')}
            ${detailCard('Program', (p.course_code || '—') + (p.course_name ? ' - ' + p.course_name : ''))}
            ${detailCard('Username', p.username ? '@' + p.username : '—')}
            ${detailCard('Created At', fmtDT(p.profile_created_at || p.account_created_at))}
          </div>
        </div>
      </div>
      ${detailTableSection('Enrolled Classes', 'book-open', ['Class', 'Subject', 'Semester', 'Joined'], rows, 'No enrolled classes found.')}`;
    openAcctInfoModalWithIcon('Student Details', html, 'fa-user-graduate');
  } catch {
    toast('Failed to load details', 'error');
  }
}

/* ════ OTP TOGGLE ════ */
async function toggleOtpAuth(uid, cb) {
  const en = cb.checked ? 1 : 0;
  const lbl = document.getElementById('otplbl-' + uid);
  cb.disabled = true;
  if (lbl) { lbl.textContent = 'Saving…'; lbl.className = 'otp-lbl'; }
  try {
    const d = await (await fetch('API/toggle_otp_auth.php', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ user_id: uid, enabled: en })
    })).json();
    if (d.status === 'success') {
      if (lbl) { lbl.textContent = en ? 'OTP On' : 'OTP Off'; lbl.className = 'otp-lbl ' + (en ? 'on' : 'off'); }
      toast(d.message, 'success');
    } else {
      cb.checked = !en;
      if (lbl) { lbl.textContent = d.locked ? '⚠ Pending' : (!en ? 'OTP On' : 'OTP Off'); lbl.className = 'otp-lbl ' + (d.locked ? 'locked-msg' : (!en ? 'on' : 'off')); }
      toast(d.message || 'Toggle failed', 'warning');
    }
  } catch(e) {
    cb.checked = !en;
    if (lbl) { lbl.textContent = !en ? 'OTP On' : 'OTP Off'; lbl.className = 'otp-lbl ' + (!en ? 'on' : 'off'); }
    toast('Request failed', 'error');
  } finally { cb.disabled = false; }
}
function otpHtml(uid, oe, fl) {
  if (!uid) return '<span class="otp-lbl locked-msg">No account</span>';
  const lk = +fl === 1, on = +oe === 1;
  const lt = lk ? '⚠ Pending' : (on ? 'OTP On' : 'OTP Off');
  const lc = lk ? 'locked-msg' : (on ? 'on' : 'off');
  return `<label class="otp-toggle-wrap${lk ? ' locked' : ''}" title="${lk ? 'Locked — first login pending' : ''}">
    <div class="otp-switch">
      <input type="checkbox" ${on ? 'checked' : ''} ${lk ? 'disabled' : ''} onchange="toggleOtpAuth('${esc(uid)}',this)">
      <span class="otp-slider"></span>
    </div>
    <span class="otp-lbl ${lc}" id="otplbl-${esc(uid)}">${lt}</span>
  </label>`;
}

/* ════ NO-FLICKER TABLE REFRESH HELPERS ════ */
const tableHashes = { subj: '', cls: '', fac: '', stu: '' };
function stableHashRows(rows) {
  try { return JSON.stringify(rows || []); } catch { return ''; }
}
function tableHasRealRows(selector) {
  const tb = document.querySelector(selector);
  return !!(tb && tb.querySelector('tr') && !tb.textContent.includes('Loading…'));
}

/* ════ SUBJECTS ════ */
function loadSubjects() {
  const tb = document.querySelector('#subjTbl tbody');
  if (!tableHasRealRows('#subjTbl tbody')) {
    tb.innerHTML = '<tr><td colspan="6" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…</td></tr>';
  }
  fetch('API/Dean/get_dept_subjects.php')
    .then(r => r.json())
    .then(r => {
      const next = r.data || [];
      const h = stableHashRows(next);
      document.getElementById('subjectBadge').textContent = next.length;
      if (h === tableHashes.subj) return;
      tableHashes.subj = h;
      allS = next;
      renderSubjects(allS);
    })
    .catch(() => toast('Failed to load subjects', 'error'));
}
wireSearch('sSearch','sClear','sCnt','sNum', ()=>allS, (a,q)=>fg(a,q,['subject_code','subject_name','course_code','course_name']), renderSubjects);

function renderSubjects(list) {
  const tb = document.querySelector('#subjTbl tbody');
  if (!list.length) {
    tb.innerHTML = `<tr><td colspan="8" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);">No courses found.</td></tr>`;
    renderPager('subjPager','subj',0,1,1,() => renderSubjects(getFilteredSubjects()));
    return;
  }
  const pg = paginateRows('subj', list);
  tb.innerHTML = pg.rows.map((s, i) => `<tr class="tl-tr course-row-clickable" onclick="openSubjectRow('${esc(s.id)}')">
    <td class="tl-td select-col" style="text-align:center;" onclick="event.stopPropagation()"><input type="checkbox" class="subj-cb subj-row-cb" data-id="${esc(s.id)}" onchange="subjUpdateBulk()"></td>
    <td class="tl-td">${pg.start + i + 1}</td>
    <td class="tl-td">
      <div class="course-merge-cell">
        <div class="course-code-row">
          <span class="pill pill-code">${esc(s.subject_code)}</span>
        </div>
        <div class="course-title">${esc(s.subject_name)}</div>
      </div>
    </td>
    <td class="tl-td"><span class="pill pill-year">${esc(YL_LABELS[s.year_level] || s.year_level || '—')}</span></td>
    <td class="tl-td">${esc(s.semester || '—')}</td>
    <td class="tl-td text-center">${esc(String(s.class_count ?? 0))}</td>
    <td class="tl-td text-center" onclick="event.stopPropagation()">
      ${subjStatusHtml(s.id, s.is_active)}
    </td>
    <td class="tl-td" style="text-align:center;padding:0 4px;" onclick="event.stopPropagation()">
      <div class="dot-menu-wrap"><button class="dot-menu-btn" onclick="openSubDotMenu('subj','${esc(s.id)}',this)" title="Actions"><i class="fas fa-ellipsis-v"></i></button></div>
    </td>
  </tr>`).join('');
  renderPager('subjPager','subj',pg.total,pg.totalPages,pg.page,() => renderSubjects(getFilteredSubjects()));
  subjUpdateBulk();
}
function subjStatusHtml(id, active) {
  const on = +active === 1;
  return `<label class="otp-toggle-wrap" id="subj-status-wrap-${esc(id)}">
    <div class="otp-switch">
      <input type="checkbox" ${on ? 'checked' : ''} onchange="toggleSubjStatus('${esc(id)}', this)">
      <span class="otp-slider"></span>
    </div>
    <span class="otp-lbl ${on ? 'on' : 'off'}" id="subj-status-lbl-${esc(id)}">${on ? 'Active' : 'Inactive'}</span>
  </label>`;
}
function openSubjectRow(id) {
  const s = allS.find(x => String(x.id) === String(id));
  if (!s) return;
  const scopeMap = {
    department_only: 'Only my program',
    selected_programs: 'Share with selected programs',
    all_programs: 'Open to all programs'
  };
  const yearNum = parseInt(s.year_level, 10);
  const yearLabel = !yearNum ? '—'
    : yearNum === 1 ? '1st Year'
    : yearNum === 2 ? '2nd Year'
    : yearNum === 3 ? '3rd Year'
    : yearNum === 4 ? '4th Year'
    : `${yearNum}th Year`;
  const statusLabel = +s.is_active === 1 ? 'Active' : 'Inactive';
  const sharedPrograms = String(s.shared_program_labels || '').split('||').filter(Boolean).join(', ');
  const html = `
    <div class="info-grid">
      <div class="info-item"><div class="info-k">Course Code</div><div class="info-v">${esc(s.subject_code || '—')}</div></div>
      <div class="info-item"><div class="info-k">Course Name</div><div class="info-v">${esc(s.subject_name || '—')}</div></div>
      <div class="info-item"><div class="info-k">Program</div><div class="info-v">${esc((s.course_code || '—') + (s.course_name ? ' - ' + s.course_name : ''))}</div></div>
      <div class="info-item"><div class="info-k">Owner Program</div><div class="info-v">${esc((s.owner_course_code || s.course_code || '—') + ((s.owner_course_name || s.course_name) ? ' - ' + (s.owner_course_name || s.course_name) : ''))}</div></div>
      <div class="info-item"><div class="info-k">Year Level</div><div class="info-v">${esc(yearLabel)}</div></div>
      <div class="info-item"><div class="info-k">Semester</div><div class="info-v">${esc(s.semester || '—')}</div></div>
      <div class="info-item"><div class="info-k">Availability</div><div class="info-v">${esc(scopeMap[s.share_scope] || 'Only my program')}</div></div>
      ${s.share_scope === 'selected_programs' ? `<div class="info-item"><div class="info-k">Shared Programs</div><div class="info-v">${esc(sharedPrograms || '—')}</div></div>` : ''}
      <div class="info-item"><div class="info-k">School Year</div><div class="info-v">${esc(s.school_year || window.activeSchoolYear || '—')}</div></div>
      <div class="info-item"><div class="info-k">Status</div><div class="info-v">${esc(statusLabel)}</div></div>
    </div>`;
  openAcctInfoModal('Course Details', html);
}
function subjSelectedIds() {
  return [...document.querySelectorAll('.subj-row-cb:checked')].map(cb => cb.dataset.id);
}
function subjUpdateBulk() {
  const all  = document.querySelectorAll('.subj-row-cb');
  const chk  = document.querySelectorAll('.subj-row-cb:checked');
  const bar  = document.getElementById('subjBulkBar');
  const lbl  = document.getElementById('subjBulkCount');
  const selAll = document.getElementById('subjSelectAll');
  bar.classList.toggle('show', selectModes.subjects && chk.length > 0);
  lbl.textContent = chk.length + ' selected';
  selAll.indeterminate = chk.length > 0 && chk.length < all.length;
  selAll.checked = all.length > 0 && chk.length === all.length;
}
function subjToggleAll(cb) {
  if (!selectModes.subjects) return;
  document.querySelectorAll('.subj-row-cb').forEach(c => c.checked = cb.checked);
  subjUpdateBulk();
}
function subjClearSelection() {
  document.querySelectorAll('.subj-row-cb').forEach(c => c.checked = false);
  const selAll = document.getElementById('subjSelectAll');
  if (selAll) { selAll.checked = false; selAll.indeterminate = false; }
  subjUpdateBulk();
}
async function bulkSubjActivate() {
  const ids = subjSelectedIds();
  if (!ids.length) return;
  const r = await Swal.fire({ title:`Activate ${ids.length} course(s)?`, icon:'question', showCancelButton:true, confirmButtonText:'Activate' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/toggleActiveSubject.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}&force=active` })));
  subjClearSelection(); sLoaded = false; loadSubjects(); toast(`${ids.length} course(s) activated.`, 'success');
}
async function bulkSubjDeactivate() {
  const ids = subjSelectedIds();
  if (!ids.length) return;
  const r = await Swal.fire({ title:`Deactivate ${ids.length} course(s)?`, icon:'warning', showCancelButton:true, confirmButtonText:'Deactivate' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/toggleActiveSubject.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}&force=inactive` })));
  subjClearSelection(); sLoaded = false; loadSubjects(); toast(`${ids.length} course(s) deactivated.`, 'success');
}
async function bulkSubjDelete() {
  const ids = subjSelectedIds();
  if (!ids.length) return;
  const r = await Swal.fire({ title:`Delete ${ids.length} course(s)?`, text:'This cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d93025', confirmButtonText:'Delete' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/deletesubject.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}` })));
  subjClearSelection(); sLoaded = false; loadSubjects(); toast(`${ids.length} course(s) deleted.`, 'success');
}
/* ════ COURSE WIZARD ════ */
let sWizStep = 0;
const sWizSteps = ['sWizPanel0','sWizPanel1','sWizPanel2'];
const sWizDots  = ['sWizStep0','sWizStep1','sWizStep2'];
const sWizFirstStep = DEPT_HAS_MULTI_PROGRAMS ? 0 : 1;

function getSelectedSharePrograms() {
  return Array.from(document.getElementById('sSharedPrograms')?.selectedOptions || []).map(o => String(o.value)).filter(Boolean);
}

function setCourseShareScope(scope) {
  const sel = document.getElementById('sShareScope');
  if (!sel) return;
  sel.value = scope;
  syncCourseShareScope();
}

function renderSharedProgramsPicker() {
  const list = document.getElementById('sSharedProgramsList');
  const sel = document.getElementById('sSharedPrograms');
  const q = (document.getElementById('sSharedProgramsSearch')?.value || '').trim().toLowerCase();
  const ownerId = String(document.getElementById('sCourse')?.value || DEPT_SINGLE_PROGRAM_ID || '');
  if (!list || !sel) return;

  const selected = new Set(getSelectedSharePrograms());
  const rows = ALL_PROGRAMS.filter(program => {
    const text = `${program.course_code || ''} ${program.course_name || ''}`.toLowerCase();
    return !q || text.includes(q);
  });

  if (!rows.length) {
    list.innerHTML = `<div class="share-picker-empty">No programs found.</div>`;
    return;
  }

  list.innerHTML = rows.map(program => {
    const id = String(program.id);
    const isOwner = id === ownerId;
    const checked = selected.has(id);
    return `
      <label class="share-picker-item ${isOwner ? 'disabled' : ''}">
        <input type="checkbox" ${checked ? 'checked' : ''} ${isOwner ? 'disabled' : ''} onchange="toggleSharedProgram('${esc(id)}', this.checked)">
        <span class="share-picker-meta">
          <span class="share-picker-code">${esc(program.course_code || '—')}</span>
          <span class="share-picker-name">${esc(program.course_name || '—')}</span>
        </span>
      </label>
    `;
  }).join('');
}

function toggleSharedProgram(id, checked) {
  const sel = document.getElementById('sSharedPrograms');
  if (!sel) return;
  Array.from(sel.options).forEach(opt => {
    if (String(opt.value) === String(id)) opt.selected = !!checked;
  });
  renderSharedProgramsPicker();
}

function syncCourseShareScope() {
  const scope = document.getElementById('sShareScope')?.value || 'department_only';
  const wrap = document.getElementById('sSharedProgramsWrap');
  const ownerId = String(document.getElementById('sCourse')?.value || DEPT_SINGLE_PROGRAM_ID || '');
  const sel = document.getElementById('sSharedPrograms');
  document.querySelectorAll('#subjModal .share-mode-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.scope === scope);
  });
  if (!wrap || !sel) return;

  Array.from(sel.options).forEach(opt => {
    const isOwner = String(opt.value) === ownerId;
    opt.disabled = isOwner;
    if (isOwner) opt.selected = false;
  });

  wrap.style.display = scope === 'selected_programs' ? '' : 'none';
  if (scope !== 'selected_programs') {
    Array.from(sel.options).forEach(opt => opt.selected = false);
  }
  renderSharedProgramsPicker();
}

function wizReset() {
  sWizStep = sWizFirstStep;
  sWizSteps.forEach((id,i) => {
    document.getElementById(id).classList.toggle('active', i===sWizStep);
  });
  sWizDots.forEach((id,i) => {
    const el = document.getElementById(id);
    el.classList.toggle('active', i===sWizStep);
    el.classList.remove('done');
  });
  document.getElementById('sWizBtnBack').style.display = 'none';
  const nxt = document.getElementById('sWizBtnNext');
  nxt.innerHTML = 'Next <i class="fas fa-arrow-right"></i>';
  nxt.disabled  = false;
}

function wizSyncDots() {
  sWizDots.forEach((id,i) => {
    const el = document.getElementById(id);
    el.classList.toggle('active', i === sWizStep);
    el.classList.toggle('done',   i < sWizStep);
  });
  sWizSteps.forEach((id,i) => {
    document.getElementById(id).classList.toggle('active', i === sWizStep);
  });
  document.getElementById('sWizBtnBack').style.display = sWizStep > sWizFirstStep ? '' : 'none';
  const nxt = document.getElementById('sWizBtnNext');
  if (sWizStep === sWizSteps.length - 1) {
    nxt.innerHTML = '<i class="fas fa-check"></i> Save Course';
  } else {
    nxt.innerHTML = 'Next <i class="fas fa-arrow-right"></i>';
  }
}

function wizNext() {
  if (sWizStep === 0) {
    if (!document.getElementById('sCourse').value) { toast('Please select a program.','error'); return; }
  } else if (sWizStep === 1) {
    const cd = document.getElementById('sCode').value.trim();
    const nm = document.getElementById('sName').value.trim();
    const yl = document.getElementById('sYearLevel').value;
    const sm = document.getElementById('sSemester').value;
    const scope = document.getElementById('sShareScope').value;
    if (!cd || !nm) { toast('Course code and name are required.','error'); return; }
    if (!yl)        { toast('Please select a Year Level.','error'); return; }
    if (!sm)        { toast('Please select a Semester.','error'); return; }
    if (scope === 'selected_programs' && !getSelectedSharePrograms().length) { toast('Please select at least one shared program.','error'); return; }
    buildWizReview();
  } else if (sWizStep === sWizSteps.length - 1) {
    saveSubject(); return;
  }
  sWizStep++;
  wizSyncDots();
}

function wizBack() {
  if (sWizStep > 0) { sWizStep--; wizSyncDots(); }
}

function buildWizReview() {
  const sel = document.getElementById('sCourse');
  const progText = sel.options[sel.selectedIndex]?.text || '—';
  const scope = document.getElementById('sShareScope').value || 'department_only';
  const scopeMap = {
    department_only: 'Only my program',
    selected_programs: 'Share with selected programs',
    all_programs: 'Open to all programs'
  };
  const sharedProgramTexts = getSelectedSharePrograms()
    .map(id => {
      const row = ALL_PROGRAMS.find(p => String(p.id) === String(id));
      return row ? `${row.course_code} - ${row.course_name}` : id;
    })
    .join(', ');
  const ylMap = {1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year',5:'5th Year'};
  const yl = document.getElementById('sYearLevel').value;
  document.getElementById('sWizReview').innerHTML = `
    ${DEPT_HAS_MULTI_PROGRAMS ? `<div class="review-row"><span class="review-label">Program</span><span class="review-val">${esc(progText)}</span></div>` : ''}
    <div class="review-row"><span class="review-label">Code</span><span class="review-val">${esc(document.getElementById('sCode').value.trim())}</span></div>
    <div class="review-row"><span class="review-label">Name</span><span class="review-val">${esc(document.getElementById('sName').value.trim())}</span></div>
    <div class="review-row"><span class="review-label">Year Level</span><span class="review-val">${esc(ylMap[yl]||yl)}</span></div>
    <div class="review-row"><span class="review-label">Semester</span><span class="review-val">${esc(document.getElementById('sSemester').value)}</span></div>
    <div class="review-row"><span class="review-label">Availability</span><span class="review-val">${esc(scopeMap[scope] || 'Only my program')}</span></div>
    ${scope === 'selected_programs' ? `<div class="review-row"><span class="review-label">Shared Programs</span><span class="review-val">${esc(sharedProgramTexts || '—')}</span></div>` : ''}
    <div class="review-row"><span class="review-label">School Year</span><span class="review-val">${esc(window.activeSchoolYear||'—')} <span style="font-size:.7rem;color:var(--text-muted);">(auto)</span></span></div>
  `;
}

function openSubjModal() {
  document.getElementById('sEditId').value = '';
  document.getElementById('sMTitle').textContent = 'Add Course';
  ['sCourse','sCode','sName','sYearLevel','sSemester'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('sShareScope').value = 'department_only';
  Array.from(document.getElementById('sSharedPrograms').options).forEach(opt => opt.selected = false);
  const shareSearch = document.getElementById('sSharedProgramsSearch'); if (shareSearch) shareSearch.value = '';
  if (!DEPT_HAS_MULTI_PROGRAMS && DEPT_SINGLE_PROGRAM_ID) document.getElementById('sCourse').value = DEPT_SINGLE_PROGRAM_ID;
  document.getElementById('sSYDisplay').textContent = window.activeSchoolYear || '—';
  syncCourseShareScope();
  wizReset();
  openModal('subjModal');
  // Auto-advance if only one program exists
  const sel = document.getElementById('sCourse');
  const realOpts = Array.from(sel.options).filter(o => o.value !== '');
  if (realOpts.length === 1 && DEPT_HAS_MULTI_PROGRAMS) {
    sel.value = realOpts[0].value;
    sWizStep = 1;
    wizSyncDots();
  }
}
function editSubj(id) {
  const s = allS.find(x => x.id === id); if (!s) return;
  if (+s.can_edit !== 1) { toast('Only the owner program can edit this shared course.','error'); return; }
  document.getElementById('sEditId').value        = s.id;
  document.getElementById('sMTitle').textContent  = 'Edit Course';
  document.getElementById('sCourse').value        = s.course_id;
  document.getElementById('sCode').value          = s.subject_code;
  document.getElementById('sName').value          = s.subject_name;
  document.getElementById('sYearLevel').value     = s.year_level || '';
  document.getElementById('sSemester').value      = s.semester   || '';
  document.getElementById('sShareScope').value    = s.share_scope || 'department_only';
  Array.from(document.getElementById('sSharedPrograms').options).forEach(opt => {
    const ids = String(s.shared_program_ids || '').split(',').map(v => v.trim()).filter(Boolean);
    opt.selected = ids.includes(String(opt.value));
  });
  const shareSearch = document.getElementById('sSharedProgramsSearch'); if (shareSearch) shareSearch.value = '';
  document.getElementById('sSYDisplay').textContent = s.school_year || window.activeSchoolYear || '—';
  syncCourseShareScope();
  wizReset();
  openModal('subjModal');
}
async function saveSubject() {
  const nxt = document.getElementById('sWizBtnNext');
  const eid = document.getElementById('sEditId').value;
  const co  = document.getElementById('sCourse').value || DEPT_SINGLE_PROGRAM_ID || '';
  const cd  = document.getElementById('sCode').value.trim();
  const nm  = document.getElementById('sName').value.trim();
  const yl  = document.getElementById('sYearLevel').value;
  const sem = document.getElementById('sSemester').value;
  const sy  = window.activeSchoolYear || '';
  const scope = document.getElementById('sShareScope').value || 'department_only';
  const sharedIds = getSelectedSharePrograms();
  nxt.disabled = true; nxt.innerHTML = '<span class="spin"></span> Saving…';
  const url  = eid ? 'API/editsubject.php' : 'API/savesubject.php';
  const body = eid
    ? `subject_id=${eid}&course_id=${encodeURIComponent(co)}&subject_code=${encodeURIComponent(cd)}&subject_name=${encodeURIComponent(nm)}&year_level=${encodeURIComponent(yl)}&semester=${encodeURIComponent(sem)}&school_year=${encodeURIComponent(sy)}&share_scope=${encodeURIComponent(scope)}&shared_program_ids=${encodeURIComponent(sharedIds.join(','))}`
    : `course_id=${encodeURIComponent(co)}&subject_code=${encodeURIComponent(cd)}&subject_name=${encodeURIComponent(nm)}&year_level=${encodeURIComponent(yl)}&semester=${encodeURIComponent(sem)}&school_year=${encodeURIComponent(sy)}&share_scope=${encodeURIComponent(scope)}&shared_program_ids=${encodeURIComponent(sharedIds.join(','))}`;
  try {
    const d = await (await fetch(url, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body })).json();
    if (d.status === 'success') { closeModal('subjModal'); wizReset(); toast(d.message,'success'); sLoaded = false; loadSubjects(); }
    else toast(d.message,'error');
  } catch { toast('Server error','error'); }
  finally { nxt.disabled = false; nxt.innerHTML = '<i class="fas fa-check"></i> Save Course'; }
}
function toggleSubj(id) {
  Swal.fire({ title:'Toggle Status?', icon:'question', showCancelButton:true, confirmButtonText:'Yes' }).then(async r => {
    if (!r.isConfirmed) return;
    const d = await (await fetch('API/toggleActiveSubject.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}` })).json();
    if (d.status === 'success') { toast(d.message, 'success'); sLoaded = false; loadSubjects(); }
    else toast(d.message, 'error');
  });
}
async function toggleSubjStatus(id, cb) {
  const wrap = document.getElementById(`subj-status-wrap-${id}`);
  const lbl = document.getElementById(`subj-status-lbl-${id}`);
  const wanted = cb.checked ? 1 : 0;
  if (wrap) wrap.classList.add('busy');
  cb.disabled = true;
  if (lbl) {
    lbl.textContent = wanted ? 'Activating…' : 'Deactivating…';
    lbl.className = 'otp-lbl';
  }
  try {
    const d = await (await fetch('API/toggleActiveSubject.php', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`id=${encodeURIComponent(id)}&force=${wanted ? 'active' : 'inactive'}`
    })).json();
    if (d.status === 'success') {
      if (lbl) {
        lbl.textContent = wanted ? 'Active' : 'Inactive';
        lbl.className = 'otp-lbl ' + (wanted ? 'on' : 'off');
      }
      toast(d.message, 'success');
      sLoaded = false;
      loadSubjects();
      return;
    }
    throw new Error(d.message || 'Status update failed');
  } catch (e) {
    cb.checked = !wanted;
    if (lbl) {
      lbl.textContent = !wanted ? 'Active' : 'Inactive';
      lbl.className = 'otp-lbl ' + (!wanted ? 'on' : 'off');
    }
    toast(e.message || 'Status update failed', 'error');
  } finally {
    cb.disabled = false;
    if (wrap) wrap.classList.remove('busy');
  }
}
function delSubj(id) {
  Swal.fire({ title:'Delete course?', text:'Cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d93025', confirmButtonText:'Delete' }).then(async r => {
    if (!r.isConfirmed) return;
    const d = await (await fetch('API/deletesubject.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}` })).json();
    if (d.status === 'success') { toast(d.message, 'success'); sLoaded = false; loadSubjects(); }
    else toast(d.message, 'error');
  });
}

/* ════ CLASSES ════ */
function loadClassDrop() {
  fetch('API/Class/fetch_dropdown_data_class.php')
    .then(r => r.json())
    .then(r => {
      if (r.status !== 'success') return;
      clsDrop = r.data;
      const sel = document.getElementById('cFaculty');
      sel.innerHTML = '<option value="">Select Professor</option>';
      (r.data.faculty || []).forEach(f => { const o = document.createElement('option'); o.value = f.id; o.textContent = `${f.full_name} (${f.faculty_number})`; sel.appendChild(o); });
    });
  document.getElementById('cCourse').addEventListener('change', function() {
    const cid = this.value;
    const sel  = document.getElementById('cSubject');
    sel.innerHTML = '<option value="">Select Course</option>';
    sel.disabled = true;
    if (!cid) return;
    const list = (clsDrop.subjects || []).filter(s => String(s.course_id) === String(cid) && DEPT_PROGRAM_IDS.includes(String(s.course_id)));
    if (!list.length) { sel.innerHTML = '<option value="">No subjects for this program</option>'; return; }
    list.forEach(s => { const o = document.createElement('option'); o.value = s.id; o.textContent = `${s.subject_code} – ${s.subject_name}`; sel.appendChild(o); });
    sel.disabled = false;
  });
}
function loadClasses() {
  const tb = document.querySelector('#clsTbl tbody');
  if (!tableHasRealRows('#clsTbl tbody')) {
    tb.innerHTML = '<tr><td colspan="7" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…</td></tr>';
  }
  fetch('API/Dean/get_dept_classes.php')
    .then(async (res) => {
      const raw = await res.text();
      let r;
      try { r = JSON.parse(raw); }
      catch { throw new Error(raw.slice(0, 140) || 'Invalid JSON response'); }
      if (r.status && r.status !== 'success') throw new Error(r.message || 'Request failed');
      const next = r.data || [];
      const h = stableHashRows(next);
      document.getElementById('classBadge').textContent = next.length;
      if (h === tableHashes.cls) return;
      tableHashes.cls = h;
      allC = next;
      renderClasses(allC);
    })
    .catch((e) => {
      console.error('loadClasses error:', e);
      toast('Failed to load classes: ' + (e.message || 'Server error'), 'error');
    });
}
wireSearch('cSearch','cClear','cCnt','cNum', ()=>allC, (a,q)=>fg(a,q,['class_code','subject_name','subject_code','faculty_name','class_semester','section']), renderClasses);

function renderClasses(list) {
  const tb = document.querySelector('#clsTbl tbody');
  if (!list.length) {
    tb.innerHTML = '<tr><td colspan="8" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);">No classes found.</td></tr>';
    renderPager('clsPager','cls',0,1,1,() => renderClasses(getFilteredClasses()));
    return;
  }
  const pg = paginateRows('cls', list);
  tb.innerHTML = pg.rows.map((c, i) => {
    const sch = c.schedule ? c.schedule.split(' - ').map(t => fmt12(t.trim())).join(' – ') : '—';
    const brk = c.break_time ? `<div style="font-size:.68rem;color:var(--warning);margin-top:.1rem;">Break: ${fmt12(c.break_time.split(/[-–]/)[0].trim())}–${fmt12(c.break_time.split(/[-–]/).pop().trim())}</div>` : '';
    const fName = c.faculty_name || '—';
    const fId = c.faculty_number || '—';
    const role = (c.admin_role || '').toString().trim();
    const rolePill = role
      ? `<span class="pill ${role.toLowerCase() === 'secretary' ? 'pill-secretary' : 'pill-dean'}" style="margin-left:.42rem;padding:.12rem .4rem;font-size:.6rem;line-height:1.1;">${esc(role)}</span>`
      : '';
    return `<tr class="tl-tr">
      <td class="tl-td select-col" style="text-align:center;"><input type="checkbox" class="subj-cb cls-row-cb" data-id="${esc(c.id)}" data-active="${+c.is_active}" onchange="clsUpdateBulk()"></td>
      <td class="tl-td">${pg.start + i + 1}</td>
      <td class="tl-td td-name">
        <div class="name-with-avatar">
          ${avatarChip(fName, c.profile_picture)}
          <div style="display:flex;flex-direction:column;min-width:0;">
            <span>${esc(fName)}${rolePill}</span>
            <span class="td-sub" style="font-size:.72rem;">${esc(fId)}</span>
          </div>
        </div>
      </td>
      <td class="tl-td">
        <span class="td-id" style="font-size:.74rem;">${esc(c.subject_code||'—')}</span>
        <br><span class="td-sub" style="font-size:.72rem;">${esc(c.subject_name||'—')}</span>
        <br><span class="td-sub" style="font-size:.68rem;">Section: ${esc(c.section || c.class_code || '—')}</span>
      </td>
      <td class="tl-td td-sub">
        <div style="display:inline-flex;align-items:center;gap:.35rem;background:var(--primary-light);border:1px solid var(--primary-mid);color:var(--primary-dark);padding:.18rem .5rem;border-radius:999px;font-size:.7rem;font-weight:700;">
          <i class="fas fa-clock" style="font-size:.62rem;"></i>${sch}
        </div>
        <div style="margin-top:.15rem;">${esc(c.class_days||'—')}</div>
        ${brk}
      </td>
      <td class="tl-td text-center"><span class="pill ${+c.is_active ? 'pill-active' : 'pill-inactive'}">${+c.is_active ? 'Active' : 'Inactive'}</span></td>
      <td class="tl-td text-center" style="vertical-align:middle;">
        <div style="display:flex;align-items:center;justify-content:center;min-height:44px;">
          <span style="font-size:1rem;font-weight:800;line-height:1;color:var(--text);">${esc(String(c.student_count ?? 0))}</span>
        </div>
      </td>
      <td class="tl-td" style="text-align:center;padding:0 4px;">
        <div class="dot-menu-wrap"><button class="dot-menu-btn" onclick="openSubDotMenu('cls','${esc(c.id)}',this)" title="Actions"><i class="fas fa-ellipsis-v"></i></button></div>
      </td>
    </tr>`;
  }).join('');
  renderPager('clsPager','cls',pg.total,pg.totalPages,pg.page,() => renderClasses(getFilteredClasses()));
  clsUpdateBulk();
}
function clsSelectedIds() { return [...document.querySelectorAll('.cls-row-cb:checked')].map(c => c.dataset.id); }
function clsUpdateBulk() {
  const all = document.querySelectorAll('.cls-row-cb');
  const chk = document.querySelectorAll('.cls-row-cb:checked');
  const bar = document.getElementById('clsBulkBar');
  const lbl = document.getElementById('clsBulkCount');
  const selAll = document.getElementById('clsSelectAll');
  bar.classList.toggle('show', selectModes.classes && chk.length > 0);
  lbl.textContent = chk.length + ' selected';
  selAll.indeterminate = chk.length > 0 && chk.length < all.length;
  selAll.checked = all.length > 0 && chk.length === all.length;
}
function clsToggleAll(cb) { if (!selectModes.classes) return; document.querySelectorAll('.cls-row-cb').forEach(c => c.checked = cb.checked); clsUpdateBulk(); }
function clsClearSelection() {
  document.querySelectorAll('.cls-row-cb').forEach(c => c.checked = false);
  const s = document.getElementById('clsSelectAll'); if (s) { s.checked = false; s.indeterminate = false; }
  clsUpdateBulk();
}
async function bulkClsActivate() {
  const ids = clsSelectedIds(); if (!ids.length) return;
  const r = await Swal.fire({ title:`Activate ${ids.length} class(es)?`, icon:'question', showCancelButton:true, confirmButtonText:'Activate' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/Class/toggleActiveClass.php', { method:'POST', body: new URLSearchParams({ class_id: id, is_active: 1 }) })));
  clsClearSelection(); cLoaded = false; loadClasses(); toast(`${ids.length} class(es) activated.`, 'success');
}
async function bulkClsDeactivate() {
  const ids = clsSelectedIds(); if (!ids.length) return;
  const r = await Swal.fire({ title:`Deactivate ${ids.length} class(es)?`, icon:'warning', showCancelButton:true, confirmButtonText:'Deactivate' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/Class/toggleActiveClass.php', { method:'POST', body: new URLSearchParams({ class_id: id, is_active: 0 }) })));
  clsClearSelection(); cLoaded = false; loadClasses(); toast(`${ids.length} class(es) deactivated.`, 'success');
}
async function bulkClsDelete() {
  const ids = clsSelectedIds(); if (!ids.length) return;
  const r = await Swal.fire({ title:`Delete ${ids.length} class(es)?`, text:'This cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d93025', confirmButtonText:'Delete' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/Class/deleteclass.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}` })));
  clsClearSelection(); cLoaded = false; loadClasses(); toast(`${ids.length} class(es) deleted.`, 'success');
}
function openClassModal() {
  document.getElementById('cEditId').value = '';
  document.getElementById('cMTitle').textContent = 'Add Class';
  ['cCourse','cSection','cYear','cStart','cEnd','cBreakStart','cBreakEnd'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('cSubject').innerHTML = '<option value="">Select course first</option>';
  document.getElementById('cSubject').disabled = true;
  document.querySelectorAll('[id^="cd_"]').forEach(el => el.checked = false);
  openModal('classModal');
}
async function editClass(id) {
  try {
    const r = await (await fetch(`API/class/get_class_detail.php?id=${encodeURIComponent(id)}`)).json();
    if (r.status !== 'success') { toast(r.message, 'error'); return; }
    const c = r.class;
    document.getElementById('cEditId').value = c.id;
    document.getElementById('cMTitle').textContent = 'Edit Class';
    document.getElementById('cCourse').value = c.course_id;
    document.getElementById('cCourse').dispatchEvent(new Event('change'));
    await new Promise(r => setTimeout(r, 200));
    document.getElementById('cSubject').value = c.subject_id;
    document.getElementById('cSection').value = c.section || '';
    document.getElementById('cFaculty').value = c.faculty_id || '';
    document.getElementById('cYear').value    = c.year_level || '';
    if (c.schedule) { const p = c.schedule.split(' - '); document.getElementById('cStart').value = p[0]?.trim()||''; document.getElementById('cEnd').value = p[1]?.trim()||''; }
    if (c.break_time) { const b = c.break_time.split(c.break_time.includes(' - ')?' - ':'-'); document.getElementById('cBreakStart').value = b[0]?.trim()||''; document.getElementById('cBreakEnd').value = b[1]?.trim()||''; }
    document.querySelectorAll('[id^="cd_"]').forEach(el => el.checked = false);
    if (c.class_days) c.class_days.split(',').map(d => d.trim()).forEach(d => { const el = document.getElementById(`cd_${d}`); if (el) el.checked = true; });
    openModal('classModal');
  } catch { toast('Could not load class', 'error'); }
}
async function saveClass() {
  const btn = document.getElementById('cSaveBtn');
  const eid = document.getElementById('cEditId').value;
  const days = [...document.querySelectorAll('[id^="cd_"]:checked')].map(c => c.value).join(', ');
  const p = {
    class_id: eid, course_id: document.getElementById('cCourse').value,
    subject_id: document.getElementById('cSubject').value,
    section: document.getElementById('cSection').value.trim(),
    faculty_id: document.getElementById('cFaculty').value,
    class_semester: window.activeSemester || '', school_year: window.activeSchoolYear || '',
    year_level: document.getElementById('cYear').value,
    schedule: (document.getElementById('cStart').value && document.getElementById('cEnd').value) ? `${document.getElementById('cStart').value} - ${document.getElementById('cEnd').value}` : '',
    break_time: (document.getElementById('cBreakStart').value && document.getElementById('cBreakEnd').value) ? `${document.getElementById('cBreakStart').value} - ${document.getElementById('cBreakEnd').value}` : '',
    class_days_formatted: days
  };
  if (!p.course_id || !p.subject_id || !p.section || !p.faculty_id) { toast('Fill required fields.', 'error'); return; }
  btn.disabled = true; btn.innerHTML = '<span class="spin"></span> Saving…';
  try {
    const d = await (await fetch('API/class/saveclass.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(p) })).json();
    if (d.status === 'success') { closeModal('classModal'); toast(d.message, 'success'); cLoaded = false; loadClasses(); }
    else toast(d.message, 'error');
  } catch { toast('Server error', 'error'); }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Save Class'; }
}
function toggleCls(id, v) {
  Swal.fire({ title:'Toggle Status?', icon:'question', showCancelButton:true, confirmButtonText:'Yes' }).then(async r => {
    if (!r.isConfirmed) return;
    const body = new URLSearchParams({ class_id: id, is_active: v });
    const d = await (await fetch('API/Class/toggleActiveClass.php', { method:'POST', body })).json();
    if (d.status === 'success') { toast(d.message, 'success'); cLoaded = false; loadClasses(); }
    else toast(d.message, 'error');
  });
}
function delClass(id) {
  Swal.fire({ title:'Delete class?', text:'Cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d93025', confirmButtonText:'Delete' }).then(async r => {
    if (!r.isConfirmed) return;
    const d = await (await fetch('API/Class/deleteclass.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}` })).json();
    if (d.status === 'success') { toast(d.message, 'success'); cLoaded = false; loadClasses(); }
    else toast(d.message, 'error');
  });
}

/* ════ FACULTY ════ */
function loadFaculty() {
  const tb = document.querySelector('#facTbl tbody');
  if (!tableHasRealRows('#facTbl tbody')) {
    tb.innerHTML = '<tr><td colspan="10" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…</td></tr>';
  }
  fetch('API/Faculty/fetch_faculty_json.php')
    .then(async (res) => {
      const raw = await res.text();
      let data;
      try { data = JSON.parse(raw); }
      catch { throw new Error(raw.slice(0, 140) || 'Invalid JSON response'); }
      const next = Array.isArray(data) ? data : (data.data || []);
      const h = stableHashRows(next);
      document.getElementById('facultyBadge').textContent = next.length;
      if (h === tableHashes.fac) return;
      tableHashes.fac = h;
      allF = next;
      renderFaculty(allF);
    })
    .catch((e) => {
      console.error('loadFaculty error:', e);
      toast('Failed to load faculty: ' + (e.message || 'Server error'), 'error');
    });
}
wireSearch('fSearch','fClear','fCnt','fNum', ()=>allF, (a,q)=>fg(a,q,['faculty_number','first_name','last_name','email','username']), renderFaculty);

function renderFaculty(list) {
  const tb = document.querySelector('#facTbl tbody');
  if (!list.length) {
    tb.innerHTML = '<tr><td colspan="8" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);">No faculty found.</td></tr>';
    renderPager('facPager','fac',0,1,1,() => renderFaculty(getFilteredFaculty()));
    return;
  }
  const pg = paginateRows('fac', list);
  const deptCode = <?php echo json_encode($deptCode ?? 'DEPT'); ?>;
  const deptName = <?php echo json_encode($deptName ?? 'Department'); ?>;
  tb.innerHTML = pg.rows.map((f, i) => {
    const baseName = [f.last_name, f.first_name].filter(Boolean).join(', ');
    const nm = f.suffix ? `${baseName} ${f.suffix}` : baseName;
    const ac = +f.is_active === 1;
    const rowDeptCode = (f.assigned_dept_code || '').toString().trim() || deptCode;
    const rowDeptName = (f.assigned_dept_name || '').toString().trim() || deptName;
    return `<tr class="tl-tr row-clickable" onclick="viewFacultyProfile('${esc(f.id)}', event)" title="Click to view account details">
      <td class="tl-td select-col" style="text-align:center;"><input type="checkbox" class="subj-cb fac-row-cb" data-id="${esc(f.id)}" onchange="facUpdateBulk()"></td>
      <td class="tl-td text-center">${pg.start + i + 1}</td>
      <td class="tl-td td-name">
        <div class="name-with-avatar">
          ${avatarChip(nm, f.profile_picture)}
          <div style="display:flex;flex-direction:column;min-width:0;">
            <span style="min-width:0;">${esc(nm)}</span>
            <span class="td-sub fac-email" style="font-size:.72rem;">${esc(f.email||'—')}</span>
          </div>
        </div>
      </td>
      <td class="tl-td"><span class="td-id">${esc(f.faculty_number || '—')}</span></td>
      <td class="tl-td fac-dept-cell"><div class="dept-stack"><span class="dept-chip">${esc(rowDeptCode)}</span><span class="dept-txt">${esc(rowDeptName)}</span></div></td>
      <td class="tl-td text-center"><span class="pill ${ac ? 'pill-active' : 'pill-inactive'}">${ac ? 'Active' : 'Inactive'}</span></td>
      <td class="tl-td text-center">${otpHtml(f.user_id, f.otp_enabled, f.first_login)}</td>
      <td class="tl-td" style="text-align:center;padding:0 4px;">
        <div class="dot-menu-wrap"><button class="dot-menu-btn" onclick="openSubDotMenu('fac','${esc(f.id)}',this)" title="Actions"><i class="fas fa-ellipsis-v"></i></button></div>
      </td>
    </tr>`;
  }).join('');
  renderPager('facPager','fac',pg.total,pg.totalPages,pg.page,() => renderFaculty(getFilteredFaculty()));
  facUpdateBulk();
}
function facSelectedIds() { return [...document.querySelectorAll('.fac-row-cb:checked')].map(c => c.dataset.id); }
function facUpdateBulk() {
  const all = document.querySelectorAll('.fac-row-cb');
  const chk = document.querySelectorAll('.fac-row-cb:checked');
  const bar = document.getElementById('facBulkBar');
  const lbl = document.getElementById('facBulkCount');
  const selAll = document.getElementById('facSelectAll');
  bar.classList.toggle('show', selectModes.faculty && chk.length > 0);
  lbl.textContent = chk.length + ' selected';
  selAll.indeterminate = chk.length > 0 && chk.length < all.length;
  selAll.checked = all.length > 0 && chk.length === all.length;
}
function facToggleAll(cb) { if (!selectModes.faculty) return; document.querySelectorAll('.fac-row-cb').forEach(c => c.checked = cb.checked); facUpdateBulk(); }
function facClearSelection() {
  document.querySelectorAll('.fac-row-cb').forEach(c => c.checked = false);
  const s = document.getElementById('facSelectAll'); if (s) { s.checked = false; s.indeterminate = false; }
  facUpdateBulk();
}
async function bulkFacActivate() {
  const ids = facSelectedIds(); if (!ids.length) return;
  const r = await Swal.fire({ title:`Activate ${ids.length} faculty?`, icon:'question', showCancelButton:true, confirmButtonText:'Activate' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/Faculty/toggle_status.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}&force=active` })));
  facClearSelection(); fLoaded = false; loadFaculty(); toast(`${ids.length} faculty activated.`, 'success');
}
async function bulkFacDeactivate() {
  const ids = facSelectedIds(); if (!ids.length) return;
  const r = await Swal.fire({ title:`Deactivate ${ids.length} faculty?`, icon:'warning', showCancelButton:true, confirmButtonText:'Deactivate' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/Faculty/toggle_status.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}&force=inactive` })));
  facClearSelection(); fLoaded = false; loadFaculty(); toast(`${ids.length} faculty deactivated.`, 'success');
}
async function bulkFacDelete() {
  const ids = facSelectedIds(); if (!ids.length) return;
  const r = await Swal.fire({ title:`Delete ${ids.length} faculty?`, text:'This cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d93025', confirmButtonText:'Delete' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch(`API/Faculty/deleteFaculty.php?id=${encodeURIComponent(id)}`)));
  facClearSelection(); fLoaded = false; loadFaculty(); toast(`${ids.length} faculty deleted.`, 'success');
}
function openFacModal() {
  document.getElementById('fEditId').value = '';
  document.getElementById('fMTitle').textContent = 'Add Faculty';
  ['fNum','fUser','fFirst','fMid','fLast','fSuffix','fEmail','fPhone','fBirth','fPass'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('fPwHint').textContent = '';
  openModal('facModal');
}
async function editFac(fn) {
  try {
    const r = await (await fetch(`API/Faculty/get_single_faculty.php?faculty_number=${encodeURIComponent(fn)}`)).json();
    if (r.status !== 'success') { toast(r.message, 'error'); return; }
    const f = r.faculty;
    document.getElementById('fEditId').value = f.id;
    document.getElementById('fMTitle').textContent = 'Edit Faculty';
    document.getElementById('fNum').value   = f.faculty_number;
    document.getElementById('fUser').value  = f.username;
    document.getElementById('fFirst').value = f.first_name;
    document.getElementById('fMid').value   = f.middle_name || '';
    document.getElementById('fLast').value  = f.last_name;
    document.getElementById('fSuffix').value = f.suffix || '';
    document.getElementById('fEmail').value = f.email;
    document.getElementById('fPhone').value = f.phone || '';
    document.getElementById('fBirth').value = f.birthdate || '';
    document.getElementById('fPass').value  = '';
    document.getElementById('fPwHint').textContent = 'Leave blank to keep current password.';
    openModal('facModal');
  } catch { toast('Could not load faculty', 'error'); }
}
function autoFacPw() {
  const fn = document.getElementById('fFirst').value.trim();
  const ln = document.getElementById('fLast').value.trim();
  const bd = document.getElementById('fBirth').value;
  if (!fn || !ln || !bd) { toast('Fill name and birthdate first.', 'info'); return; }
  const p = bd.split('-'); if (p.length !== 3) return;
  document.getElementById('fPass').value = fn[0].toLowerCase() + p[1] + p[2] + p[0] + ln[0].toUpperCase();
  updateFacultyInputProgress();
}
function applyStepState(containerId, activeStep){
  const wrap = document.getElementById(containerId);
  if (!wrap) return;
  wrap.querySelectorAll('.acct-step').forEach(step => {
    const s = Number(step.getAttribute('data-step') || 1);
    step.classList.remove('active','done');
    if (s < activeStep) step.classList.add('done');
    else if (s === activeStep) step.classList.add('active');
  });
}
function normalizePhPhone(raw){
  const digits = String(raw || '').replace(/\D/g, '');
  return digits.slice(0, 10);
}
function isValidPhPhone(raw){
  const v = normalizePhPhone(raw);
  return /^9\d{9}$/.test(v);
}
function validBirthdateValue(value, minAge = 12, maxAge = 100){
  if (!value) return false;
  const d = new Date(value + 'T00:00:00');
  if (Number.isNaN(d.getTime())) return false;
  const now = new Date();
  if (d > now) return false;
  let age = now.getFullYear() - d.getFullYear();
  const m = now.getMonth() - d.getMonth();
  if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--;
  return age >= minAge && age <= maxAge;
}
let facWizardStep = 1;
let stuWizardStep = 1;
function setFacWizardStep(step){
  facWizardStep = Math.min(3, Math.max(1, Number(step || 1)));
  document.querySelectorAll('#facModal [data-fstep]').forEach(p => p.classList.remove('active'));
  document.querySelector(`#facModal [data-fstep="${facWizardStep}"]`)?.classList.add('active');
  applyStepState('facSteps', facWizardStep);
  document.getElementById('fBackBtn').style.display = facWizardStep > 1 ? 'inline-flex' : 'none';
  document.getElementById('fNextBtn').style.display = facWizardStep < 3 ? 'inline-flex' : 'none';
  document.getElementById('fSaveBtn').style.display = facWizardStep === 3 ? 'inline-flex' : 'none';
  if (facWizardStep === 3) refreshFacultyReview();
}
function facStepValid(step){
  const checks = {
    1: [['fNum','Faculty ID'],['fUser','Username'],['fFirst','First Name'],['fLast','Last Name']],
    2: [['fEmail','Email'],['fPhone','Phone'],['fBirth','Birthdate'],['fPass','Password']]
  };
  const req = checks[step] || [];
  const missing = req.filter(([id]) => !String(document.getElementById(id)?.value || '').trim()).map(([,lbl]) => lbl);
  if (missing.length) {
    toast('Missing: ' + missing.join(', '), 'error');
    return false;
  }
  if (step === 2) {
    const phone = document.getElementById('fPhone').value;
    if (!isValidPhPhone(phone)) {
      toast('Phone must be PH mobile format: +63 9XX-XXX-XXXX', 'error');
      return false;
    }
    if (!validBirthdateValue(document.getElementById('fBirth').value)) {
      toast('Birthdate must be valid (age 12 to 100, not future).', 'error');
      return false;
    }
  }
  return true;
}
function facNextStep(){
  if (!facStepValid(facWizardStep)) return;
  setFacWizardStep(facWizardStep + 1);
}
function facPrevStep(){ setFacWizardStep(facWizardStep - 1); }
function refreshFacultyReview(){
  const out = document.getElementById('facReviewInfo');
  if (!out) return;
  const map = [
    ['Faculty ID', document.getElementById('fNum').value || '—'],
    ['Username', document.getElementById('fUser').value ? '@' + document.getElementById('fUser').value : '—'],
    ['Name', [document.getElementById('fFirst').value, document.getElementById('fMid').value, document.getElementById('fLast').value, document.getElementById('fSuffix').value].filter(Boolean).join(' ') || '—'],
    ['Email', document.getElementById('fEmail').value || '—'],
    ['Phone', document.getElementById('fPhone').value || '—'],
    ['Birthdate', document.getElementById('fBirth').value || '—']
  ];
  out.innerHTML = map.map(([k,v]) => `<div class="info-item"><div class="info-k">${esc(k)}</div><div class="info-v">${esc(v)}</div></div>`).join('');
}
function updateFacultyInputProgress(){
  if (facWizardStep === 3) refreshFacultyReview();
}
async function saveFaculty() {
  const btn = document.getElementById('fSaveBtn');
  const eid = document.getElementById('fEditId').value;

  // Scope all reads inside the modal to avoid duplicate ID conflicts
  const facModal = document.getElementById('facModal');
  const p = {
    faculty_number: facModal.querySelector('#fNum').value.trim(),
    first_name:     facModal.querySelector('#fFirst').value.trim(),
    middle_name:    facModal.querySelector('#fMid').value.trim(),
    last_name:      facModal.querySelector('#fLast').value.trim(),
    suffix:         facModal.querySelector('#fSuffix').value.trim(),
    email:          facModal.querySelector('#fEmail').value.trim(),
    phone:          normalizePhPhone(facModal.querySelector('#fPhone').value.trim()),
    username:       facModal.querySelector('#fUser').value.trim(),
    birthdate:      facModal.querySelector('#fBirth').value,
    password:       facModal.querySelector('#fPass').value.trim(),
    is_dean:        '0',
    user_type_id:   '2'
  };

  const missing = [];
  if (!p.faculty_number) missing.push('Faculty ID');
  if (!p.first_name)     missing.push('First Name');
  if (!p.last_name)      missing.push('Last Name');
  if (!p.email)          missing.push('Email');
  if (!p.username)       missing.push('Username');
  if (!eid && !p.password) missing.push('Password');
  if (missing.length) { toast('Missing: ' + missing.join(', '), 'error'); return; }
  if (!isValidPhPhone(p.phone)) { toast('Phone must be PH mobile format: +63 9XX-XXX-XXXX', 'error'); return; }
  if (!validBirthdateValue(p.birthdate)) { toast('Birthdate must be valid (age 12 to 100, not future).', 'error'); return; }

  if (eid) p.faculty_id = eid;
  btn.disabled = true; btn.innerHTML = '<span class="spin"></span> Saving…';
  try {
    const res = await fetch(eid ? 'API/Faculty/editfaculty.php' : 'API/Faculty/savefaculty.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams(p).toString()
    });
    const text = await res.text();
    let d;
    try { d = JSON.parse(text); }
    catch { toast('Server error: ' + text.substring(0, 120), 'error'); return; }
    if (d.status === 'success') { closeModal('facModal'); toast(d.message, 'success'); fLoaded = false; loadFaculty(); }
    else toast(d.message || 'Save failed', 'error');
  } catch (err) { toast('Network error: ' + err.message, 'error'); }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Save'; }
}
function delFac(id) {
  Swal.fire({ title:'Delete faculty?', text:'Cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d93025', confirmButtonText:'Delete' }).then(async r => {
    if (!r.isConfirmed) return;
    const d = await (await fetch(`API/Faculty/deleteFaculty.php?id=${encodeURIComponent(id)}`)).json();
    if (d.status === 'success') { toast(d.message, 'success'); fLoaded = false; loadFaculty(); }
    else toast(d.message, 'error');
  });
}
/* ── SUBADMIN AUTO-REFRESH ── */
(function() {
  function pollData() {
    loadStudents();
    loadFaculty();
  }
  setInterval(pollData, 30000);
  document.addEventListener('visibilitychange', function() {
    if (!document.hidden) pollData();
  });
})();

/* ════ STUDENTS ════ */
function loadStudents() {
  const tb = document.querySelector('#stuTbl tbody');
  if (!tableHasRealRows('#stuTbl tbody')) {
    tb.innerHTML = '<tr><td colspan="9" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="margin-right:.5rem;"></i>Loading…</td></tr>';
  }
  fetch('API/Dean/get_dept_students.php')
    .then(r => r.json())
    .then(r => {
      const next = r.data || [];
      const h = stableHashRows(next);
      document.getElementById('studentBadge').textContent = next.length;
      if (h === tableHashes.stu) return;
      tableHashes.stu = h;
      allSt = next;
      renderStudents(allSt);
    })
    .catch(() => toast('Failed to load students', 'error'));
}
wireSearch('stSearch','stClear','stCnt','stNum', ()=>allSt, (a,q)=>fg(a,q,['student_number','first_name','last_name','email','course_code']), renderStudents);

function renderStudents(list) {
  const tb = document.querySelector('#stuTbl tbody');
  if (!list.length) {
    tb.innerHTML = '<tr><td colspan="8" class="tl-td text-center" style="padding:2rem;color:var(--text-muted);">No students found.</td></tr>';
    renderPager('stuPager','stu',0,1,1,() => renderStudents(getFilteredStudents()));
    return;
  }
  const pg = paginateRows('stu', list);
  tb.innerHTML = pg.rows.map((s, i) => {
    const baseName = [s.last_name, s.first_name].filter(Boolean).join(', ');
    const nm = s.suffix ? `${baseName} ${s.suffix}` : baseName, ac = +s.is_active === 1;
    return `<tr class="tl-tr row-clickable" onclick="viewStudentProfile('${esc(s.id)}', event)" title="Click to view account details">
      <td class="tl-td select-col" style="text-align:center;"><input type="checkbox" class="subj-cb stu-row-cb" data-id="${s.id}" data-user-id="${esc(s.user_id||'')}" data-otp="${+s.otp_enabled===1?1:0}" onchange="stuUpdateBulk()"></td>
      <td class="tl-td text-center">${pg.start + i + 1}</td>
      <td class="tl-td td-name">
        <div class="name-with-avatar">
          ${avatarChip(nm, s.profile_picture)}
          <div style="display:flex;flex-direction:column;min-width:0;">
            <span>${esc(nm)}</span>
            <span class="td-sub" style="font-size:.72rem;max-width:260px;overflow:hidden;text-overflow:ellipsis;">${esc(s.email||'—')}</span>
          </div>
        </div>
      </td>
      <td class="tl-td"><span class="td-id">${esc(s.student_number)}</span></td>
      <td class="tl-td td-sub">${esc(s.course_code||'—')}</td>
      <td class="tl-td text-center"><span class="pill ${ac ? 'pill-active' : 'pill-inactive'}">${ac ? 'Active' : 'Inactive'}</span></td>
      <td class="tl-td text-center">${otpHtml(s.user_id, s.otp_enabled, s.first_login)}</td>
      <td class="tl-td" style="text-align:center;padding:0 4px;">
        <div class="dot-menu-wrap"><button class="dot-menu-btn" onclick="openSubDotMenu('stu','${s.id}',this)" title="Actions"><i class="fas fa-ellipsis-v"></i></button></div>
      </td>
    </tr>`;
  }).join('');
  renderPager('stuPager','stu',pg.total,pg.totalPages,pg.page,() => renderStudents(getFilteredStudents()));
  stuUpdateBulk();
}
function stuSelectedIds() { return [...document.querySelectorAll('.stu-row-cb:checked')].map(c => c.dataset.id); }
function stuSelectedMeta() {
  return [...document.querySelectorAll('.stu-row-cb:checked')].map(c => ({
    id: c.dataset.id || '',
    user_id: c.dataset.userId || '',
    otp_enabled: +c.dataset.otp === 1 ? 1 : 0
  }));
}
function stuUpdateBulk() {
  const all = document.querySelectorAll('.stu-row-cb');
  const chk = document.querySelectorAll('.stu-row-cb:checked');
  const bar = document.getElementById('stuBulkBar');
  const lbl = document.getElementById('stuBulkCount');
  const selAll = document.getElementById('stuSelectAll');
  const onBtn = document.getElementById('stuOtpOnBtn');
  const offBtn = document.getElementById('stuOtpOffBtn');
  bar.classList.toggle('show', selectModes.students && chk.length > 0);
  lbl.textContent = chk.length + ' selected';
  selAll.indeterminate = chk.length > 0 && chk.length < all.length;
  selAll.checked = all.length > 0 && chk.length === all.length;

  const selectedRows = stuSelectedMeta();
  const canOn = selectedRows.filter(s => s.user_id && +s.otp_enabled === 0).length;
  const canOff = selectedRows.filter(s => s.user_id && +s.otp_enabled === 1).length;

  if (onBtn) {
    onBtn.disabled = canOn === 0;
    onBtn.style.opacity = canOn === 0 ? '.5' : '1';
    onBtn.style.pointerEvents = canOn === 0 ? 'none' : 'auto';
  }
  if (offBtn) {
    offBtn.innerHTML = `<i class="fas fa-shield-alt"></i> OTP Off`;
    offBtn.disabled = canOff === 0;
    offBtn.style.opacity = canOff === 0 ? '.5' : '1';
    offBtn.style.pointerEvents = canOff === 0 ? 'none' : 'auto';
  }
}
function stuToggleAll(cb) { if (!selectModes.students) return; document.querySelectorAll('.stu-row-cb').forEach(c => c.checked = cb.checked); stuUpdateBulk(); }
function stuClearSelection() {
  document.querySelectorAll('.stu-row-cb').forEach(c => c.checked = false);
  const s = document.getElementById('stuSelectAll'); if (s) { s.checked = false; s.indeterminate = false; }
  stuUpdateBulk();
}
async function bulkStuActivate() {
  const ids = stuSelectedIds(); if (!ids.length) return;
  const r = await Swal.fire({ title:`Activate ${ids.length} student(s)?`, icon:'question', showCancelButton:true, confirmButtonText:'Activate' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/student/toggle_status.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}&force=active` })));
  stuClearSelection(); stLoaded = false; loadStudents(); toast(`${ids.length} student(s) activated.`, 'success');
}
async function bulkStuOtpOff() {
  const picked = stuSelectedMeta();
  if (!picked.length) return;
  const users = picked.map(s => s.user_id).filter(Boolean);
  if (!users.length) { toast('Selected students have no linked account.', 'warning'); return; }
  const toTurnOff = picked.filter(s => s.user_id && +s.otp_enabled === 1).map(s => s.user_id);
  if (!toTurnOff.length) { toast('Selected accounts are already OTP Off.', 'info'); return; }
  const r = await Swal.fire({
    title: `Turn OTP Off for ${toTurnOff.length} student account(s)?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Turn Off'
  });
  if (!r.isConfirmed) return;
  await Promise.all(toTurnOff.map(uid => fetch('API/toggle_otp_auth.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ user_id: uid, enabled: 0 })
  })));
  stuClearSelection(); stLoaded = false; loadStudents(); toast(`OTP turned off for ${toTurnOff.length} account(s).`, 'success');
}
async function bulkStuOtpOn() {
  const picked = stuSelectedMeta();
  if (!picked.length) return;
  const users = picked.map(s => s.user_id).filter(Boolean);
  if (!users.length) { toast('Selected students have no linked account.', 'warning'); return; }
  const toTurnOn = picked.filter(s => s.user_id && +s.otp_enabled === 0).map(s => s.user_id);
  if (!toTurnOn.length) { toast('Selected accounts are already OTP On.', 'info'); return; }
  const r = await Swal.fire({
    title: `Turn OTP On for ${toTurnOn.length} student account(s)?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Turn On'
  });
  if (!r.isConfirmed) return;
  await Promise.all(toTurnOn.map(uid => fetch('API/toggle_otp_auth.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ user_id: uid, enabled: 1 })
  })));
  stuClearSelection(); stLoaded = false; loadStudents(); toast(`OTP turned on for ${toTurnOn.length} account(s).`, 'success');
}
async function bulkStuDeactivate() {
  const ids = stuSelectedIds(); if (!ids.length) return;
  const r = await Swal.fire({ title:`Deactivate ${ids.length} student(s)?`, icon:'warning', showCancelButton:true, confirmButtonText:'Deactivate' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/student/toggle_status.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}&force=inactive` })));
  stuClearSelection(); stLoaded = false; loadStudents(); toast(`${ids.length} student(s) deactivated.`, 'success');
}
async function bulkStuDelete() {
  const ids = stuSelectedIds(); if (!ids.length) return;
  const r = await Swal.fire({ title:`Delete ${ids.length} student(s)?`, text:'This cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d93025', confirmButtonText:'Delete' });
  if (!r.isConfirmed) return;
  await Promise.all(ids.map(id => fetch('API/student/delete_student.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}` })));
  stuClearSelection(); stLoaded = false; loadStudents(); toast(`${ids.length} student(s) deleted.`, 'success');
}
function autoStuPw() {
  const fn = document.getElementById('asFirst').value.trim();
  const ln = document.getElementById('asLast').value.trim();
  const bd = document.getElementById('asBirth').value;
  if (!fn || !ln || !bd) { toast('Fill name and birthdate first.', 'info'); return; }
  const p = bd.split('-'); if (p.length !== 3) return;
  document.getElementById('asPass').value = fn[0].toLowerCase() + p[1] + p[2] + p[0] + ln[0].toUpperCase();
  updateStudentInputProgress();
}
function setStuWizardStep(step){
  stuWizardStep = Math.min(3, Math.max(1, Number(step || 1)));
  document.querySelectorAll('#addStuModal [data-sstep]').forEach(p => p.classList.remove('active'));
  document.querySelector(`#addStuModal [data-sstep="${stuWizardStep}"]`)?.classList.add('active');
  applyStepState('stuSteps', stuWizardStep);
  document.getElementById('sBackBtn').style.display = stuWizardStep > 1 ? 'inline-flex' : 'none';
  document.getElementById('sNextBtn').style.display = stuWizardStep < 3 ? 'inline-flex' : 'none';
  document.getElementById('asSaveBtn').style.display = stuWizardStep === 3 ? 'inline-flex' : 'none';
}
function stuStepValid(step){
  const form = document.getElementById('addStuForm');
  const checks = {
    1: [['input[name="student_number"]','Student ID'],['select[name="course_id"]','Course']],
    2: [['input[name="first_name"]','First Name'],['input[name="last_name"]','Last Name']],
    3: [['input[name="email"]','Email'],['input[name="username"]','Username'],['input[name="birthdate"]','Birthdate'],['input[name="password"]','Password']]
  };
  const req = checks[step] || [];
  const missing = req.filter(([sel]) => !String(form.querySelector(sel)?.value || '').trim()).map(([,lbl]) => lbl);
  if (missing.length) {
    toast('Missing: ' + missing.join(', '), 'error');
    return false;
  }
  if (step === 3 && !validBirthdateValue(document.getElementById('asBirth').value)) {
    toast('Birthdate must be valid (age 12 to 100, not future).', 'error');
    return false;
  }
  return true;
}
function stuNextStep(){
  if (!stuStepValid(stuWizardStep)) return;
  setStuWizardStep(stuWizardStep + 1);
}
function stuPrevStep(){ setStuWizardStep(stuWizardStep - 1); }
document.getElementById('asBirth')?.addEventListener('change', function() {
  if (document.getElementById('asFirst').value && document.getElementById('asLast').value) autoStuPw();
});
function updateStudentInputProgress() {
  const form = document.getElementById('addStuForm');
  if (!form) return;
  const requiredEls = [
    form.querySelector('input[name="student_number"]'),
    form.querySelector('select[name="course_id"]'),
    form.querySelector('input[name="first_name"]'),
    form.querySelector('input[name="last_name"]'),
    form.querySelector('input[name="email"]'),
    form.querySelector('input[name="username"]'),
    form.querySelector('input[name="birthdate"]'),
    form.querySelector('input[name="password"]')
  ].filter(Boolean);
  const filled = requiredEls.filter(el => String(el.value || '').trim() !== '').length;
  const total = requiredEls.length || 1;
  const pct = Math.round((filled / total) * 100);

  const bar = document.getElementById('asInputProgressBar');
  const pctEl = document.getElementById('asInputProgressPct');
  const txtEl = document.getElementById('asInputProgressText');
  if (bar) bar.style.width = `${pct}%`;
  if (pctEl) pctEl.textContent = `${pct}%`;
  if (txtEl) txtEl.textContent = filled === total ? 'Ready to submit' : `Form completion (${filled}/${total} required)`;
}
document.getElementById('addStuForm')?.addEventListener('input', updateStudentInputProgress);
document.getElementById('addStuForm')?.addEventListener('change', updateStudentInputProgress);
['fNum','fUser','fFirst','fMid','fLast','fSuffix','fEmail','fPhone','fBirth','fPass'].forEach(id => {
  document.getElementById(id)?.addEventListener('input', updateFacultyInputProgress);
  document.getElementById(id)?.addEventListener('change', updateFacultyInputProgress);
});
document.getElementById('fPhone')?.addEventListener('input', function(){
  this.value = normalizePhPhone(this.value);
});
['fBirth','asBirth','esBirth'].forEach(id => {
  const el = document.getElementById(id);
  if (!el) return;
  const today = new Date().toISOString().slice(0,10);
  el.max = today;
  el.addEventListener('change', function(){
    if (this.value && !validBirthdateValue(this.value)) {
      toast('Birthdate must be valid (age 12 to 100, not future).', 'warning');
    }
  });
});
async function saveStudent() {
  const form = document.getElementById('addStuForm');
  const btn = document.getElementById('asSaveBtn');
  const missing = [];
  const checks = [
    ['input[name="student_number"]', 'Student ID'],
    ['select[name="course_id"]', 'Course'],
    ['input[name="first_name"]', 'First Name'],
    ['input[name="last_name"]', 'Last Name'],
    ['input[name="email"]', 'Email'],
    ['input[name="username"]', 'Username'],
    ['input[name="birthdate"]', 'Birthdate'],
    ['input[name="password"]', 'Password']
  ];
  checks.forEach(([sel, label]) => {
    const el = form.querySelector(sel);
    const val = (el?.value || '').toString().trim();
    if (!val) missing.push(label);
  });
  if (missing.length) {
    toast('Missing: ' + missing.join(', '), 'error');
    form.reportValidity();
    return;
  }

  const progWrap = document.getElementById('asSubmitProgressWrap');
  const progBar = document.getElementById('asSubmitProgressBar');
  const progPct = document.getElementById('asSubmitProgressPct');
  const progText = document.getElementById('asSubmitProgressText');
  const setProg = (pct, text) => {
    if (!progWrap || !progBar || !progPct || !progText) return;
    progWrap.style.display = 'block';
    progBar.style.width = `${pct}%`;
    progPct.textContent = `${pct}%`;
    progText.textContent = text;
  };

  setProg(15, 'Validating form…');
  btn.disabled = true; btn.innerHTML = '<span class="spin"></span> Saving…';
  try {
    setProg(45, 'Creating account…');
    const res = await fetch('API/student/save_student.php', {
      method: 'POST', body: new FormData(document.getElementById('addStuForm'))
    });
    const text = await res.text();
    let d;
    try { d = JSON.parse(text); }
    catch {
      setProg(100, 'Failed');
      toast('Server error: ' + text.substring(0, 120), 'error');
      return;
    }
    if (d.status === 'success') {
      setProg(100, 'Done');
      closeModal('addStuModal');
      document.getElementById('addStuForm').reset();
      updateStudentInputProgress();
      toast(d.message, 'success');
      stLoaded = false;
      loadStudents();
    } else {
      setProg(100, 'Failed');
      toast(d.message || 'Save failed', 'error');
    }
  } catch (err) {
    setProg(100, 'Failed');
    toast('Network error: ' + (err?.message || 'Request failed'), 'error');
  }
  finally {
    setTimeout(() => {
      if (progWrap) progWrap.style.display = 'none';
      if (progBar) progBar.style.width = '0%';
      if (progPct) progPct.textContent = '0%';
      if (progText) progText.textContent = 'Preparing…';
    }, 800);
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Submit';
  }
}
document.getElementById('addStuModal')?.addEventListener('click', function(e){
  if (e.target && e.target.id === 'addStuModal') setTimeout(updateStudentInputProgress, 0);
});
async function editStu(id) {
  try {
    const r = await (await fetch(`API/student/get_student.php?id=${id}`)).json();
    if (r.status !== 'success') { toast(r.message, 'error'); return; }
    const s = r.student;
    document.getElementById('esId').value     = s.id;
    document.getElementById('esNum').value    = s.student_number;
    document.getElementById('esFirst').value  = s.first_name;
    document.getElementById('esMid').value    = s.middle_name || '';
    document.getElementById('esSuffix').value = s.suffix || '';
    document.getElementById('esLast').value   = s.last_name;
    document.getElementById('esEmail').value  = s.email;
    document.getElementById('esUser').value   = s.username;
    document.getElementById('esBirth').value  = s.birthdate || '';
    document.getElementById('esCourse').value = s.course_id;
    document.getElementById('esPass').value   = '';
    openModal('editStuModal');
  } catch { toast('Could not load student', 'error'); }
}
async function submitEditStu() {
  const btn = document.getElementById('esSaveBtn');
  btn.disabled = true; btn.innerHTML = '<span class="spin"></span> Saving…';
  try {
    const d = await (await fetch('API/student/edit_student.php', {
      method: 'POST', body: new FormData(document.getElementById('editStuForm'))
    })).json();
    if (d.status === 'success') { closeModal('editStuModal'); toast(d.message, 'success'); stLoaded = false; loadStudents(); }
    else toast(d.message, 'error');
  } catch { toast('Server error', 'error'); }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Save'; }
}
function toggleStu(id) {
  Swal.fire({ title:'Toggle Status?', icon:'question', showCancelButton:true, confirmButtonText:'Yes' }).then(async r => {
    if (!r.isConfirmed) return;
    const d = await (await fetch('API/student/toggle_status.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}` })).json();
    if (d.status === 'success') { toast(d.message, 'success'); stLoaded = false; loadStudents(); }
    else toast(d.message, 'error');
  });
}

function delStu(id) {
  Swal.fire({ title:'Delete student?', text:'Cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d93025', confirmButtonText:'Delete' }).then(async r => {
    if (!r.isConfirmed) return;
    const d = await (await fetch(`API/student/delete_student.php?id=${id}`)).json();
    if (d.status === 'success') { toast(d.message, 'success'); stLoaded = false; loadStudents(); }
    else toast(d.message, 'error');
  });
}
</script>

<script>
/* ── AUTO-REFRESH POLLING ENGINE ── */
const _polls = {};
function startPoll(key, fn, intervalMs = 20000) {
  stopPoll(key);
  fn(); // run immediately
  _polls[key] = setInterval(fn, intervalMs);
}
function stopPoll(key) {
  if (_polls[key]) { clearInterval(_polls[key]); delete _polls[key]; }
}
// Pause polling when tab is hidden, resume when visible
document.addEventListener('visibilitychange', () => {
  Object.values(_polls).forEach(id => {
    if (document.hidden) clearInterval(id);
  });
  if (!document.hidden) {
    // re-trigger registered polls
    Object.keys(_polls).forEach(key => _polls[key] && clearInterval(_polls[key]));
    _pollsRestart && _pollsRestart();
  }
});
</script>
</body>
</html>
