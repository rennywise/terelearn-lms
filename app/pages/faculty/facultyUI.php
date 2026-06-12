<?php
/**
 * facultyUI.php - Tere LEARN | Faculty Dashboard
 * Updated: semester footer from admin settings + nested archive section
 *          + role-switcher toggle for faculty who are also Dean/Secretary
 *          + Google Meet button on every active class card
 */
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php');
    exit;
}
require_once dirname(__DIR__, 2) . '/config/db_connect.php';

$userId = $_SESSION['user_id'];

$isAlsoDean  = false;
$deanRoleLabel = 'Dean';

$uid  = mysqli_real_escape_string($conn, $userId);
$uRes = $conn->query("SELECT email, username FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
$uRow = $uRes ? $uRes->fetch_assoc() : null;

if ($uRow) {
    $un = mysqli_real_escape_string($conn, $uRow['username']);
    $em = mysqli_real_escape_string($conn, $uRow['email']);
    $aRes = $conn->query("SELECT id FROM tbladmin WHERE (username='$un' OR email='$em') AND is_deleted=0 LIMIT 1");
    if ($aRes && $aRes->num_rows > 0) {
        $aRow = $aRes->fetch_assoc();
        $aid  = mysqli_real_escape_string($conn, $aRow['id']);
        $daRes = $conn->query("SELECT role FROM tbldeanassignment WHERE faculty_id='$aid' AND is_active=1 LIMIT 1");
        if ($daRes && $daRes->num_rows > 0) {
            $isAlsoDean    = true;
            $daRow         = $daRes->fetch_assoc();
            $deanRoleLabel = ucfirst($daRow['role'] ?? 'Dean');
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TERELEARN - Faculty Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

  <style>
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
      --fd-pass-good:   #1f73db;
      --fd-pass-warn:   #f59e0b;
      --fd-pass-risk:   #d93025;
      --fd-badge-muted: #94a3b8;
      --fd-risk-soft:   rgba(217,48,37,.12);
      --fd-invite-soft: rgba(245,124,0,.14);
      --fd-line-1:      #1f73db;
      --fd-line-2:      #10b981;
      --fd-line-3:      #ef4444;
      --fd-line-4:      #f59e0b;
      --fd-line-5:      #8b5cf6;
      --fd-line-6:      #14b8a6;
    }

    .sort-btn {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .28rem .7rem; border-radius: 20px;
  border: 1.5px solid var(--border); background: none;
  font-size: .72rem; font-weight: 600; font-family: inherit;
  color: var(--text-muted); cursor: pointer;
  transition: all var(--transition);
}
.sort-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
.sort-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
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
      --fd-badge-muted: #64748b;
      --fd-risk-soft:   rgba(217,48,37,.16);
      --fd-invite-soft: rgba(245,124,0,.18);
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

    /* â”€â”€ TOP NAV â”€â”€ */
    .topnav {
      position: fixed; inset: 0 0 auto 0; height: var(--nav-h);
      background: var(--surface); border-bottom: 1px solid var(--border);
      display: flex; align-items: center; padding: 0 1.5rem; gap: .75rem;
      z-index: 200; transition: background var(--transition), border-color var(--transition);
    }
    .nav-brand { display: flex; align-items: center; gap: .6rem; font-size: 1.15rem; font-weight: 700; color: var(--text); text-decoration: none; white-space: nowrap; }
    .brand-logo { width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem; box-shadow: 0 2px 8px rgba(26,158,120,.35); }
    .nav-actions { margin-left: auto; display: flex; align-items: center; gap: .4rem; }
    .icon-btn { width: 38px; height: 38px; border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: 1rem; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all var(--transition); }
    .icon-btn:hover { background: var(--border); color: var(--text); }
    .nav-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .85rem; cursor: pointer; border: 2px solid transparent; transition: border-color var(--transition); overflow: hidden; }    .nav-avatar:hover { border-color: var(--primary); }
    .menu-btn { width: 38px; height: 38px; border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: 1.1rem; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all var(--transition); }
    .menu-btn:hover { background: var(--border); color: var(--text); }

    /* â”€â”€ ROLE SWITCHER â”€â”€ */
    .role-switcher { display: inline-flex; align-items: center; gap: .55rem; background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1.5px solid #fcd34d; border-radius: 20px; padding: .28rem .85rem .28rem .55rem; cursor: pointer; transition: all var(--transition); white-space: nowrap; user-select: none; }
    .role-switcher:hover { background: linear-gradient(135deg, #fef3c7, #fde68a); border-color: var(--warning); box-shadow: 0 2px 10px rgba(245,124,0,.2); }
    body.dark .role-switcher { background: rgba(245,124,0,.1); border-color: rgba(245,124,0,.4); }
    body.dark .role-switcher:hover { background: rgba(245,124,0,.18); }
    .role-switcher-track { width: 34px; height: 18px; background: var(--primary-light); border-radius: 18px; position: relative; flex-shrink: 0; transition: background var(--transition); }
    .role-switcher-track::after { content: ''; position: absolute; width: 13px; height: 13px; border-radius: 50%; background: var(--primary); top: 2.5px; left: 3px; transition: transform var(--transition); box-shadow: 0 1px 4px rgba(0,0,0,.2); }
    .role-switcher-label { font-size: .7rem; font-weight: 700; color: var(--warning); letter-spacing: .3px; display: flex; align-items: center; gap: .3rem; }
    body.dark .role-switcher-label { color: #ffb74d; }

    .sidebar-role-switcher { margin: .5rem .75rem 0; background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1.5px solid #fcd34d; border-radius: 10px; padding: .7rem 1rem; display: flex; align-items: center; gap: .75rem; cursor: pointer; transition: all var(--transition); }
    .sidebar-role-switcher:hover { background: linear-gradient(135deg, #fef3c7, #fde68a); box-shadow: 0 2px 10px rgba(245,124,0,.15); }
    body.dark .sidebar-role-switcher { background: rgba(245,124,0,.08); border-color: rgba(245,124,0,.35); }
    body.dark .sidebar-role-switcher:hover { background: rgba(245,124,0,.15); }
    .srs-icon { width: 32px; height: 32px; background: #fef3c7; color: var(--warning); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0; }
    body.dark .srs-icon { background: rgba(245,124,0,.15); }
    .srs-text { flex: 1; overflow: hidden; }
    .srs-label { font-size: .78rem; font-weight: 700; color: var(--warning); white-space: nowrap; }
    body.dark .srs-label { color: #ffb74d; }
    .srs-sub { font-size: .67rem; color: var(--text-muted); margin-top: .07rem; white-space: nowrap; }
    .srs-arrow { color: var(--warning); font-size: .75rem; flex-shrink: 0; }
    body.dark .srs-arrow { color: #ffb74d; }
    .sidebar.collapsed .srs-text, .sidebar.collapsed .srs-arrow { display: none; }
    .sidebar.collapsed .sidebar-role-switcher { justify-content: center; padding: .6rem; margin: .5rem .4rem 0; }
    .sidebar.collapsed .srs-icon { width: 36px; height: 36px; }

    /* â”€â”€ SIDEBAR â”€â”€ */
    .sidebar { position: fixed; top: var(--nav-h); left: 0; bottom: var(--footer-h); width: var(--sidebar-w); background: var(--surface); border-right: 1px solid var(--border); padding: 1rem 0 1rem; overflow-y: auto; overflow-x: hidden; z-index: 150; transition: transform var(--transition), width var(--transition), background var(--transition), border-color var(--transition); }
    .sidebar.collapsed { width: 70px; }
    @media (max-width: 768px) { .sidebar { transform: translateX(-100%); width: var(--sidebar-w) !important; } .sidebar.open { transform: translateX(0); } }
    .sidebar-faculty { display: flex; align-items: center; gap: .8rem; padding: .85rem 1.2rem 1rem; border-bottom: 1px solid var(--border); margin-bottom: .5rem; overflow: hidden; }
    .s-avatar { width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 1rem; flex-shrink: 0; overflow: hidden; }    .s-name { font-weight: 600; font-size: .88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
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


    /* â”€â”€ VIEW TOGGLE â”€â”€ */
.view-toggle { display: inline-flex; border: 0.5px solid var(--border); border-radius: 8px; overflow: hidden; }
.view-toggle-btn { width: 32px; height: 32px; border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: .82rem; display: flex; align-items: center; justify-content: center; transition: all var(--transition); }
.view-toggle-btn.active { background: var(--primary); color: #fff; }
.view-toggle-btn:not(.active):hover { background: var(--primary-light); color: var(--primary); }

/* â”€â”€ CLASS TABLE â”€â”€ */
.class-table-wrap { border-radius: var(--radius); border: 1px solid var(--border); background: var(--surface); overflow: visible; }
.tbl-toolbar { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--border); flex-wrap: wrap; }
.tbl-search { flex: 1; min-width: 160px; display: flex; align-items: center; gap: 8px; background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0 10px; height: 32px; }
.tbl-search input { border: none; background: transparent; outline: none; font-size: .8rem; color: var(--text); width: 100%; font-family: inherit; }
.tbl-search i { color: var(--text-muted); font-size: .75rem; flex-shrink: 0; }
.tbl-pills { display: flex; gap: 5px; flex-shrink: 0; flex-wrap: wrap; }
.tbl-pill { font-size: .7rem; font-weight: 600; padding: .22rem .65rem; border-radius: 20px; border: 1px solid var(--border); background: transparent; color: var(--text-muted); cursor: pointer; transition: all var(--transition); font-family: inherit; }
.tbl-pill:hover, .tbl-pill.active { background: var(--primary-light); color: var(--primary); border-color: var(--primary); }
.tbl-count { font-size: .72rem; color: var(--text-muted); flex-shrink: 0; }
.class-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.class-table thead tr { border-bottom: 1px solid var(--border); }
.class-table thead th { padding: .6rem .9rem; text-align: left; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--text-muted); white-space: nowrap; cursor: pointer; user-select: none; transition: color var(--transition); }
.class-table thead th:hover { color: var(--primary); }
.class-table thead th.tbl-sorted { color: var(--primary); }
.class-table thead th .sort-ic { opacity: .3; margin-left: 3px; font-size: .6rem; transition: opacity var(--transition); }
.class-table thead th.tbl-sorted .sort-ic { opacity: 1; }
.class-table tbody tr { border-bottom: 1px solid var(--border); cursor: pointer; transition: background var(--transition); }
.class-table tbody tr:last-child { border-bottom: none; }
.class-table tbody tr:hover { background: var(--primary-light); }
.class-table tbody tr:hover .tbl-row-actions { opacity: 1; }
.class-table td { padding: .7rem .9rem; vertical-align: middle; }
.tbl-schedule-day { font-weight: 700; color: var(--text); font-size: .78rem; line-height: 1.15; }
.tbl-schedule-time { color: var(--text-muted); font-size: .75rem; margin-top: .15rem; }
.tbl-students { text-align: center; font-weight: 700; color: var(--text); }
.tbl-kebab-wrap { position: relative; display: inline-flex; justify-content: flex-end; width: 100%; }
.tbl-kebab-btn { width: 30px; height: 30px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface); color: var(--text-muted); cursor: pointer; }
.tbl-kebab-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
.tbl-kebab-menu {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  min-width: 168px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 10px;
  box-shadow: var(--shadow-lg);
  padding: .35rem;
  z-index: 60;
  display: none;
}
.tbl-kebab-menu.open { display: block; }
.tbl-kebab-item { width: 100%; border: none; background: transparent; text-align: left; padding: .48rem .55rem; border-radius: 8px; color: var(--text); font-size: .8rem; font-weight: 600; display: flex; align-items: center; gap: .5rem; cursor: pointer; }
.tbl-kebab-item:hover { background: var(--bg); }
.tbl-kebab-item.danger { color: var(--danger); }
.tbl-name-cell { display: flex; align-items: center; gap: 9px; }
.tbl-name-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.tbl-name-main { font-weight: 600; font-size: .82rem; color: var(--text); }
.tbl-name-sub { font-size: .7rem; color: var(--text-muted); margin-top: 1px; }
.tbl-muted { font-size: .78rem; color: var(--text-muted); }
.tbl-badge { display: inline-flex; align-items: center; gap: 3px; font-size: .67rem; font-weight: 700; padding: .18rem .5rem; border-radius: 20px; }
.tbl-badge.src-admin { background: var(--accent-light); color: var(--accent); }
.tbl-badge.src-mine { background: var(--primary-light); color: var(--primary); }
.tbl-badge.src-off { background: #fff3e0; color: var(--warning); }
body.dark .tbl-badge.src-off { background: rgba(245,124,0,.12); }
.tbl-row-actions { display: flex; gap: 3px; opacity: 0; transition: opacity var(--transition); }
.tbl-act { width: 26px; height: 26px; border: none; background: none; cursor: pointer; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: .75rem; transition: all var(--transition); flex-shrink: 0; }
.tbl-act:hover { background: var(--primary-light); color: var(--primary); }
.tbl-act.del:hover { background: #fdecea; color: var(--danger); }
.tbl-act.pause:hover { background: #fff3e0; color: var(--warning); }
.tbl-empty { text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); font-size: .82rem; }
.tbl-empty i { font-size: 1.6rem; opacity: .25; display: block; margin-bottom: .5rem; }

.faculty-dash { display:grid; grid-template-columns:minmax(0,1fr); gap:6px; align-items:start; }
.faculty-dash-main { display:grid; gap:6px; min-width:0; }
.faculty-dash-side { min-width:0; }
.fd-card, .fd-side-stack { background:var(--surface); border:0.5px solid var(--border); border-radius:8px; }
.fd-card { padding:8px; box-shadow:var(--shadow); }
.fd-welcome-title { font-size:18px; font-weight:700; color:var(--text); line-height:1.15; }
.fd-welcome-meta { font-size:11px; color:var(--text-muted); margin-top:2px; }
.fd-stat-grid { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:6px; }
.fd-stat-card { border:0.5px solid var(--border); border-radius:8px; padding:8px; background:var(--surface); min-width:0; }
.fd-stat-card.is-good { background:color-mix(in srgb, var(--primary-light) 70%, var(--surface)); }
.fd-stat-card.is-risk { background:color-mix(in srgb, var(--fd-risk-soft) 60%, var(--surface)); }
.fd-stat-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); }
.fd-stat-value { font-size:18px; font-weight:700; color:var(--text); margin-top:3px; line-height:1; }
.fd-card-head { display:flex; align-items:center; justify-content:space-between; gap:8px; font-size:13px; font-weight:700; color:var(--text); margin-bottom:8px; }
.fd-card-sub { font-size:10px; color:var(--text-muted); font-weight:600; }
.fd-pass-list { display:grid; gap:6px; }
.fd-pass-row { display:grid; grid-template-columns:92px minmax(0,1fr) 44px; align-items:center; gap:8px; }
.fd-pass-name { font-size:10px; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.fd-pass-bar { height:8px; border-radius:999px; background:var(--bg); overflow:hidden; display:flex; }
.fd-pass-fill, .fd-pass-rest { height:100%; }
.fd-pass-fill.tone-good { background:var(--fd-pass-good); }
.fd-pass-fill.tone-warn { background:var(--fd-pass-warn); }
.fd-pass-fill.tone-risk { background:var(--fd-pass-risk); }
.fd-pass-rest { background:color-mix(in srgb, var(--fd-pass-risk) 35%, var(--surface)); }
.fd-pass-pct { font-size:11px; font-weight:700; text-align:right; }
.fd-pass-pct.tone-good { color:var(--fd-pass-good); }
.fd-pass-pct.tone-warn { color:var(--fd-pass-warn); }
.fd-pass-pct.tone-risk { color:var(--fd-pass-risk); }
.fd-section-chart-wrap { position:relative; height:112px; }
.fd-chart-grid { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:6px; }
.fd-mini-chart-wrap { position:relative; height:138px; }
.fd-trend-wrap { position:relative; height:120px; }
.fd-trend-empty, .fd-list-empty { font-size:11px; color:var(--text-muted); padding:10px 0; text-align:center; }
.fd-side-stack { overflow:hidden; }
.fd-side-panel { padding:8px; }
.fd-side-panel + .fd-side-panel { border-top:0.5px solid var(--border); }
.fd-side-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.55px; margin-bottom:8px; color:var(--text-muted); }
.fd-enroll-list, .fd-person-list { display:grid; gap:6px; }
.fd-enroll-row { display:grid; grid-template-columns:minmax(0,1fr) auto auto; gap:6px; align-items:center; font-size:11px; }
.fd-enroll-name { min-width:0; }
.fd-enroll-main { font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.fd-enroll-sub { font-size:10px; color:var(--text-muted); margin-top:1px; }
.fd-enroll-total { font-size:11px; font-weight:700; color:var(--text-muted); }
.fd-badge { display:inline-flex; align-items:center; justify-content:center; min-width:28px; padding:1px 6px; border-radius:999px; font-size:10px; font-weight:700; border:0.5px solid var(--border); }
.fd-badge-new { background:color-mix(in srgb, var(--accent-light) 75%, var(--surface)); color:var(--accent); }
.fd-badge-zero { background:color-mix(in srgb, var(--border) 55%, var(--surface)); color:var(--fd-badge-muted); }
.fd-person-row { display:grid; grid-template-columns:24px minmax(0,1fr) auto; gap:7px; align-items:center; }
.fd-avatar { width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; background:var(--primary-light); color:var(--primary); }
.fd-person-main { min-width:0; }
.fd-person-name { font-size:11px; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.fd-person-meta { font-size:10px; color:var(--text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.fd-status { display:inline-flex; align-items:center; justify-content:center; padding:1px 6px; border-radius:999px; font-size:10px; font-weight:700; }
.fd-status-joined { background:color-mix(in srgb, var(--primary-light) 82%, var(--surface)); color:var(--primary); }
.fd-status-invited { background:color-mix(in srgb, var(--fd-invite-soft) 75%, var(--surface)); color:var(--warning); }
.fd-grade-good { font-size:11px; font-weight:700; color:var(--primary); }
.fd-grade-risk { font-size:11px; font-weight:700; color:var(--danger); }
.fd-points-card { padding:14px; border-radius:14px; }
.fd-points-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px; }
.fd-points-title { font-size:16px; font-weight:800; color:var(--text); display:flex; align-items:center; gap:8px; }
.fd-points-title i { color:var(--primary); }
.fd-points-note { font-size:11px; color:var(--text-muted); font-weight:700; }
.fd-points-chart { position:relative; min-height:292px; border:0.5px solid var(--border); border-radius:14px; background:var(--bg); padding:12px; }
.fd-points-chart svg { width:100%; height:230px; display:block; overflow:visible; }
.fd-points-axis { display:flex; justify-content:space-between; padding:0 6px; color:var(--text-muted); font-size:11px; font-weight:700; }
.fd-points-tooltip { position:absolute; display:none; min-width:112px; background:var(--surface); border:0.5px solid var(--border); border-radius:10px; box-shadow:var(--shadow-md); padding:8px 10px; pointer-events:none; z-index:4; }
.fd-points-tooltip-day { font-size:11px; color:var(--text-muted); margin-bottom:3px; }
.fd-points-tooltip-total { font-size:13px; font-weight:800; color:var(--text); }
.fd-points-tooltip-change { font-size:11px; color:var(--text-muted); margin-top:2px; }
.fd-dashboard-question { color:var(--text-muted); font-size:13px; line-height:1.55; padding:12px 14px; }
.fd-monitor-grid { display:grid; grid-template-columns:minmax(0,1.55fr) minmax(280px,.65fr); gap:8px; align-items:start; }
.fd-graph-stack, .fd-leader-stack { display:grid; gap:8px; min-width:0; }
.fd-class-bars { display:grid; gap:10px; }
.fd-class-row { display:grid; gap:5px; }
.fd-class-meta { display:flex; align-items:center; justify-content:space-between; gap:8px; font-size:11px; }
.fd-class-name { font-weight:800; color:var(--text); min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.fd-class-avg { color:var(--text-muted); font-weight:700; flex-shrink:0; }
.fd-stackbar { display:flex; height:14px; border-radius:999px; overflow:hidden; background:var(--bg); border:0.5px solid var(--border); }
.fd-stackseg { min-width:0; height:100%; }
.fd-stackseg.high { background:var(--primary); }
.fd-stackseg.average { background:var(--fd-pass-warn); }
.fd-stackseg.risk { background:var(--danger); }
.fd-stackseg.ungraded { background:var(--fd-badge-muted); }
.fd-stack-legend { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; font-size:10px; color:var(--text-muted); font-weight:700; }
.fd-legend-item { display:inline-flex; align-items:center; gap:4px; }
.fd-legend-dot { width:8px; height:8px; border-radius:999px; display:inline-block; }
.fd-leader-list { display:grid; gap:7px; }
.fd-leader-row { display:grid; grid-template-columns:26px minmax(0,1fr) auto; gap:8px; align-items:center; padding:7px; border:0.5px solid var(--border); border-radius:10px; background:var(--bg); }
.fd-leader-rank { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:var(--primary-light); color:var(--primary); font-size:11px; font-weight:800; }
.fd-leader-main { min-width:0; }
.fd-leader-name { font-size:12px; font-weight:800; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.fd-leader-meta { font-size:10px; color:var(--text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:1px; }
.fd-leader-score { text-align:right; font-weight:800; color:var(--primary); font-size:12px; white-space:nowrap; }
.fd-leader-score.risk { color:var(--danger); }
.fd-leader-sub { display:block; font-size:10px; color:var(--text-muted); font-weight:700; margin-top:1px; }
body.dark .fd-stat-card.is-good { background:rgba(46,204,154,.1); }
body.dark .fd-stat-card.is-risk { background:var(--fd-risk-soft); }
body.dark .fd-pass-rest { background:rgba(217,48,37,.22); }
body.dark .fd-badge-zero { background:rgba(100,116,139,.18); }
body.dark .fd-status-invited { background:var(--fd-invite-soft); }
@media (max-width: 1024px) {
  .faculty-dash { grid-template-columns:1fr; }
  .fd-chart-grid { grid-template-columns:1fr; }
  .fd-monitor-grid { grid-template-columns:1fr; }
}
@media (max-width: 640px) {
  .fd-stat-grid { grid-template-columns:repeat(2, minmax(0,1fr)); }
  .fd-pass-row { grid-template-columns:80px minmax(0,1fr) 40px; }
}

    .arch-sort-btn {
      display: inline-flex; align-items: center; gap: .35rem;
      padding: .32rem .8rem; border-radius: 20px;
      border: 1.5px solid var(--border); background: var(--bg);
      font-size: .75rem; font-weight: 600; font-family: inherit;
      color: var(--text-muted); cursor: pointer; transition: all var(--transition);
    }
    .arch-sort-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
    .arch-sort-btn.active { background: var(--primary); color: #fff; border-color: var(--primary);
      box-shadow: 0 2px 8px rgba(26,158,120,.28); }
    .arch-sort-btn i { font-size: .68rem; }
    /* â”€â”€ OVERLAY â”€â”€ */
    .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 140; }
    .overlay.show { display: block; }

    /* â”€â”€ MAIN â”€â”€ */
    .main { margin-left: var(--sidebar-w); margin-top: var(--nav-h); padding: 2rem 2rem calc(var(--footer-h) + 1.5rem); min-height: calc(100vh - var(--nav-h)); transition: margin-left var(--transition); }
    .main.collapsed { margin-left: 70px; }
    @media (max-width: 768px) { .main { margin-left: 0; padding: 1rem 1rem calc(var(--footer-h) + 1rem); } }

    /* â”€â”€ FOOTER â”€â”€ */
    .faculty-footer { position: fixed; bottom: 0; left: 0; right: 0; height: var(--footer-h); background: var(--surface); border-top: 1px solid var(--border); display: grid; grid-template-columns: 1fr auto auto; align-items: center; padding: 0 1.5rem; z-index: 190; font-size: .78rem; color: var(--text-muted); transition: background var(--transition), border-color var(--transition); column-gap: .7rem; }
    .footer-copyright { display: flex; align-items: center; gap: .4rem; justify-self: start; }
    .footer-sem-badge { display: inline-flex; align-items: center; gap: .45rem; background: var(--primary-light); color: var(--primary); border: 1.5px solid var(--primary); border-radius: 8px; padding: .22rem .8rem; font-weight: 700; font-size: .72rem; letter-spacing: .3px; white-space: nowrap; justify-self: end; }
    .footer-sem-badge i { font-size: .65rem; opacity: .8; }
    .footer-brand { margin-left: 0; justify-self: end; font-weight: 800; letter-spacing: .5px; color: var(--text); font-size: .74rem; }
    body.dark .footer-sem-badge { border-color: var(--primary); }

    /* â”€â”€ PAGES â”€â”€ */
    .page { display: none; animation: fadeUp .28s ease; }
    .page.active { display: block; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .page-header { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; }
    .page-title { font-size: 1.65rem; font-weight: 700; }
    .page-subtitle { color: var(--text-muted); font-size: .88rem; margin-top: .2rem; }

    /* â”€â”€ STATS â”€â”€ */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.2rem 1.4rem; box-shadow: var(--shadow); display: flex; align-items: center; gap: .9rem; transition: transform var(--transition), box-shadow var(--transition); }
    .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0; }
    .si-green  { background: var(--primary-light); color: var(--primary); }
    .si-blue   { background: var(--accent-light);  color: var(--accent); }
    .si-orange { background: #fff3e0; color: var(--warning); }
    .si-red    { background: #fdecea; color: var(--danger); }
    .stat-val  { font-size: 1.85rem; font-weight: 700; line-height: 1; }
    .stat-lbl  { font-size: .73rem; color: var(--text-muted); margin-top: .18rem; }

    /* â”€â”€ SECTION DIVIDER â”€â”€ */
    .section-divider { display: flex; align-items: center; gap: .75rem; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .9px; color: var(--text-muted); margin: 1.75rem 0 1rem; }
    .section-divider .sd-icon { width: 28px; height: 28px; border-radius: 8px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: .8rem; }
    .section-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

    /* â”€â”€ CLASS GRID â”€â”€ */
    .class-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 1.1rem; }
    .class-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: visible; box-shadow: var(--shadow); cursor: pointer; transition: all var(--transition); display: flex; flex-direction: column; position: relative; }
    .class-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
    .card-banner { height: 90px; padding: 1rem; display: flex; flex-direction: column; justify-content: flex-end; position: relative; overflow: hidden; border-top-left-radius: var(--radius); border-top-right-radius: var(--radius); }
    .card-banner::after { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,.15); pointer-events: none; }
    .banner-title { font-size: 1rem; font-weight: 700; color: #fff; position: relative; z-index: 1; line-height: 1.3; }
    .banner-code  { font-size: .75rem; color: rgba(255,255,255,.82); position: relative; z-index: 1; margin-top: .1rem; }
    .b-forest  { background: linear-gradient(135deg,#1a9e78,#0a5c45); }
    .b-ocean   { background: linear-gradient(135deg,#1f73db,#0d47a1); }
    .b-sunset  { background: linear-gradient(135deg,#f57c00,#bf360c); }
    .b-plum    { background: linear-gradient(135deg,#7b1fa2,#4a148c); }
    .b-teal    { background: linear-gradient(135deg,#00838f,#00474d); }
    .b-rose    { background: linear-gradient(135deg,#c62828,#880e4f); }
    .b-slate   { background: linear-gradient(135deg,#455a64,#263238); }
    .b-indigo  { background: linear-gradient(135deg,#3949ab,#1a237e); }
    .src-badge { position: absolute; top: .6rem; right: .6rem; z-index: 2; font-size: .62rem; font-weight: 700; padding: .18rem .5rem; border-radius: 20px; letter-spacing: .4px; text-transform: uppercase; backdrop-filter: blur(6px); }
    .card-kebab-wrap { position: absolute; top: .5rem; right: .5rem; z-index: 40; }
    .card-kebab-btn {
      width: 32px; height: 32px; border: 1px solid rgba(255,255,255,.4);
      border-radius: 999px; background: rgba(0,0,0,.25); color: #fff;
      display: inline-flex; align-items: center; justify-content: center;
      cursor: pointer; transition: all var(--transition); pointer-events: auto;
    }
    .card-kebab-btn:hover { background: rgba(0,0,0,.45); transform: translateY(-1px); }
    .card-kebab-menu {
      position: absolute; top: calc(100% + 8px); right: 0;
      min-width: 170px; background: var(--surface); border: 1px solid var(--border);
      border-radius: 10px; box-shadow: var(--shadow-lg); padding: .35rem; display: none; z-index: 220;
    }
    .card-kebab-menu.open { display: block; }
    .card-kebab-item {
      width: 100%; border: none; background: transparent; color: var(--text);
      display: flex; align-items: center; gap: .55rem; text-align: left;
      font-size: .82rem; font-weight: 600; border-radius: 8px; padding: .5rem .6rem; cursor: pointer;
    }
    .card-kebab-item:hover { background: var(--bg); }
    .card-kebab-item.danger { color: var(--danger); }
    .src-admin   { background: rgba(255,255,255,.2); color: #fff; }
    .src-faculty { background: rgba(26,158,120,.55); color: #fff; }
    .card-body { padding: .9rem 1.1rem; flex: 1; display: flex; flex-direction: column; }
    .card-chips { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .75rem; }
    .chip { font-size: .7rem; font-weight: 500; padding: .18rem .55rem; border-radius: 20px; background: var(--bg); color: var(--text-muted); border: 1px solid var(--border); display: flex; align-items: center; gap: .25rem; }
    .chip i { font-size: .62rem; }
    .card-foot { display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border); padding-top: .65rem; margin-top: auto; }
    .student-count { font-size: .78rem; color: var(--text-muted); display: flex; align-items: center; gap: .3rem; }
    .card-actions { display: flex; gap: .2rem; }
    .c-action-btn { width: 28px; height: 28px; border: none; background: none; cursor: pointer; color: var(--text-muted); border-radius: 7px; font-size: .78rem; display: flex; align-items: center; justify-content: center; transition: all var(--transition); }
    .c-action-btn:hover { background: var(--primary-light); color: var(--primary); }
    .c-action-btn.del:hover { background: #fdecea; color: var(--danger); }
    .c-action-btn.toggle-off:hover { background: #fff3e0; color: var(--warning); }
    .c-action-btn.toggle-on:hover  { background: var(--primary-light); color: var(--primary); }
    .class-card.deactivated { opacity: .72; }
    .class-card.deactivated .card-banner { filter: grayscale(.55); }
    .pending-req-badge { display: inline-flex; align-items: center; gap: .3rem; background: #fef3c7; color: #92400e; border: 1.5px solid #fcd34d; border-radius: 20px; font-size: .68rem; font-weight: 700; padding: .18rem .6rem; white-space: nowrap; }
    @keyframes pending-pop { 0%,100%{transform:scale(1);}40%{transform:scale(1.15);}70%{transform:scale(0.95);} }
    .pending-req-badge.pop { animation: pending-pop .4s ease-out; }
    .card-action-bar{display:flex;gap:.4rem;padding:.55rem 1.1rem .6rem;border-top:1px solid var(--border);background:var(--bg);}
    .ca-btn{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:.38rem .5rem;border-radius:8px;border:1.5px solid var(--border);background:none;font-size:.72rem;font-weight:700;font-family:inherit;color:var(--text-muted);cursor:pointer;transition:all var(--transition);white-space:nowrap;}
    .ca-btn i{font-size:.68rem;}
    .ca-edit:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .ca-toggle:hover{border-color:var(--warning);color:var(--warning);background:#fff3e0;}
    .ca-delete:hover{border-color:var(--danger);color:var(--danger);background:#fdecea;}
    .deact-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; padding: .22rem .6rem; border-radius: 20px; background: #fff3e0; color: var(--warning); border: 1px solid #ffe0b2; }

    /* â”€â”€ ADD CARD â”€â”€ */
    .add-card { border: 2px dashed var(--border) !important; background: transparent !important; box-shadow: none !important; align-items: center; justify-content: center; min-height: 190px; }
    .add-card:hover { border-color: var(--primary) !important; background: var(--primary-light) !important; }
    .add-card-body { display: flex; flex-direction: column; align-items: center; gap: .45rem; padding: 2rem; }
    .add-icon { width: 50px; height: 50px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: all var(--transition); }
    .add-card:hover .add-icon { background: var(--primary); color: #fff; transform: scale(1.08); }
    .add-label { font-size: .85rem; font-weight: 600; color: var(--text-muted); }

    /* â”€â”€ EMPTY STATE â”€â”€ */
    .empty-state { padding: 2.5rem 1rem; text-align: center; color: var(--text-muted); grid-column: 1 / -1; }
    .empty-icon { font-size: 2.5rem; margin-bottom: .75rem; opacity: .35; }
    .empty-title { font-size: 1rem; font-weight: 600; margin-bottom: .3rem; }

    /* â”€â”€ ARCHIVE STYLES â”€â”€ */
    .archive-sy-group { margin-bottom: 1.75rem; }
    .archive-sy-header { display: flex; align-items: center; gap: .75rem; padding: .7rem 1.1rem; border-radius: 11px; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; margin-bottom: .85rem; cursor: pointer; box-shadow: 0 2px 10px rgba(26,158,120,.25); transition: opacity var(--transition); }
    .archive-sy-header:hover { opacity: .92; }
    .archive-sy-icon { font-size: .85rem; opacity: .85; }
    .archive-sy-label { font-size: .95rem; font-weight: 700; flex: 1; }
    .archive-sy-count { font-size: .7rem; background: rgba(255,255,255,.2); border-radius: 20px; padding: .15rem .55rem; }
    .archive-sy-chevron { font-size: .7rem; opacity: .75; transition: transform var(--transition); }
    .archive-sy-group.collapsed .archive-sy-chevron { transform: rotate(-90deg); }
    .archive-sy-group.collapsed .archive-sy-body { display: none; }
    .archive-sem-group { margin-bottom: 1.1rem; }
    .archive-sem-header { display: flex; align-items: center; gap: .6rem; padding: .55rem 1rem; border-radius: 9px; background: var(--bg); border: 1px solid var(--border); margin-bottom: .75rem; cursor: pointer; transition: background var(--transition); }
    .archive-sem-header:hover { background: var(--border); }
    .archive-sem-label { font-size: .82rem; font-weight: 700; flex: 1; color: var(--text); }
    .archive-sem-count { font-size: .68rem; color: var(--text-muted); background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: .1rem .48rem; }
    .archive-sem-chevron { font-size: .65rem; color: var(--text-muted); transition: transform var(--transition); }
    .archive-sem-group.collapsed .archive-sem-chevron { transform: rotate(-90deg); }
    .archive-sem-group.collapsed .archive-sem-body { display: none; }
    .archive-card { opacity: .84; }
    .archive-card .card-banner { filter: grayscale(.35) brightness(.9); }
    .archive-card:hover { opacity: 1; }
    .archive-card:hover .card-banner { filter: none; }
    .archived-chip { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; font-size: .62rem; }

    /* â”€â”€ PROFILE CARD â”€â”€ */
    .profile-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; max-width: 100%; }
    .profile-banner { height: 160px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); }
    .profile-body { padding: 0 2rem 2rem; }
    .profile-avatar { width: 100px; height: 100px; border-radius: 50%; border: 4px solid var(--surface); background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; font-weight: 700; margin-top: -50px; margin-bottom: .4rem; }
    .profile-name { font-size: 1.35rem; font-weight: 700; }
    .profile-sub  { font-size: .85rem; color: var(--text-muted); margin-top: .15rem; }
    .profile-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: .75rem; margin-top: 1.25rem; text-align: center; }
    .ps { border-right: 1px solid var(--border); }
    .ps:last-child { border-right: none; }
    .ps-val { font-size: 1.55rem; font-weight: 700; color: var(--primary); }
    .ps-lbl { font-size: .72rem; color: var(--text-muted); }

    /* â”€â”€ MODAL â”€â”€ */
    .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 500; align-items: center; justify-content: center; }
    .modal-backdrop.show { display: flex; }
    .modal-box { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); border-radius: 16px; width: 100%; max-width: 640px; max-height: calc(100vh - 2rem); overflow: hidden; margin: 1rem; box-shadow: 0 30px 80px rgba(0,0,0,.22); animation: popIn .22s ease; display: flex; flex-direction: column; position: relative; background-clip: padding-box; }
    .modal-box form { display: flex; flex-direction: column; min-height: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.96) translateY(8px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.modal-header { background: transparent; color: #fff; padding: 1.35rem 1.6rem; display: flex; align-items: center; justify-content: space-between; border-radius: 0; overflow: hidden; margin-top: 0; }    .modal-title { font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: .6rem; }
    .modal-title-icon { width: 30px; height: 30px; background: rgba(255,255,255,.18); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .85rem; }
    .modal-close { width: 30px; height: 30px; border: none; background: rgba(255,255,255,.18); color: #fff; border-radius: 8px; cursor: pointer; font-size: .85rem; display: flex; align-items: center; justify-content: center; transition: background var(--transition); }
    .modal-close:hover { background: rgba(255,255,255,.32); }
    .modal-progress { padding: .9rem 1.6rem 1rem; background: #f7f9fc; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
    .stepper { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); align-items: start; }
    .stepper-line { position: absolute; top: 19px; left: calc(12.5% + 20px); right: calc(12.5% + 20px); height: 3px; background: #dde2e9; border-radius: 999px; z-index: 0; }
    .stepper-line-fill { height: 100%; width: 0%; background: var(--primary); border-radius: 999px; transition: width var(--transition); }
    .stepper-step { position: relative; z-index: 1; text-align: center; }
    .stepper-dot { width: 40px; height: 40px; margin: 0 auto; border-radius: 50%; border: 3px solid #dde2e9; background: #fff; color: #6b7280; display: flex; align-items: center; justify-content: center; font-size: .9rem; transition: all var(--transition); }
    .stepper-label { margin-top: .42rem; font-size: .68rem; letter-spacing: 1px; font-weight: 700; color: #4b5563; text-transform: uppercase; }
    .stepper-step.active .stepper-dot,
    .stepper-step.done .stepper-dot { background: var(--primary); border-color: #b9efe0; color: #fff; }
    .stepper-step.active .stepper-label,
    .stepper-step.done .stepper-label { color: var(--primary-dark); }
    @media (max-width: 768px) {
      .stepper-dot { width: 34px; height: 34px; font-size: .8rem; }
      .stepper-line { top: 16px; left: calc(12.5% + 17px); right: calc(12.5% + 17px); }
      .stepper-label { font-size: .62rem; letter-spacing: .7px; }
    }
    .modal-body { padding: 1.1rem 1.6rem; overflow-y: auto; overflow-x: visible; max-height: calc(100vh - 290px); background: var(--surface); }
    .modal-body::-webkit-scrollbar { width: 8px; }
    .modal-body::-webkit-scrollbar-track { background: transparent; }
    .modal-body::-webkit-scrollbar-thumb { background: color-mix(in srgb, var(--border) 75%, var(--text-muted) 25%); border-radius: 999px; }
    .form-section { background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 1.1rem; margin-bottom: .9rem; }
    .form-section-label { font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary); margin-bottom: .8rem; padding-bottom: .45rem; border-bottom: 2px solid var(--border); display: flex; align-items: center; gap: .4rem; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
    .form-grid.g3 { grid-template-columns: 1fr 1fr 1fr; }
    .form-grid.g1 { grid-template-columns: 1fr; }
    .form-group label { display: block; font-size: .73rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: .3rem; }
    .form-group input,
    .form-group select {
      width: 100%;
      padding: .5rem .85rem;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      font-size: .88rem;
      font-family: inherit;
      background: var(--surface);
      color: var(--text);
      transition: border-color var(--transition), box-shadow var(--transition);
    }
    .form-group input:focus,
    .form-group select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-mid);
      outline: none;
    }
    #fYearHint, #fSectionHint {
      min-height: 18px;
      line-height: 1.25;
      font-size: .72rem !important;
      margin-top: .22rem !important;
    }
    .schedule-actions { display: flex; gap: .45rem; flex-wrap: wrap; margin: .1rem 0 .55rem; }
    .schedule-chip { border: 1px solid var(--border); background: var(--surface); color: var(--text-muted); font-size: .72rem; font-weight: 600; border-radius: 999px; padding: .26rem .65rem; cursor: pointer; }
    .schedule-chip:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
    input[type="time"] { min-height: 44px; }
    .time-picker {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: .3rem;
      min-height: 44px;
    }
    .time-picker:focus-within {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-mid);
    }
    .tp-part {
      border: none;
      background: #f1f5f9;
      color: #111827;
      border-radius: 7px;
      font-size: 1.65rem;
      font-weight: 700;
      line-height: 1;
      padding: .35rem .4rem;
      min-width: 3.05rem;
      text-align: center;
      font-family: 'DM Mono', monospace;
      appearance: none;
      -webkit-appearance: none;
      cursor: pointer;
    }
    .tp-part.tp-ap {
      font-size: .78rem;
      font-weight: 700;
      min-width: 2.8rem;
      padding: .54rem .35rem;
      font-family: inherit;
    }
    .tp-sep { font-size: 1.55rem; font-weight: 800; color: #4b5563; margin: 0 .02rem; }
    .time-hidden { position: absolute; opacity: 0; pointer-events: none; width: 0; height: 0; }
    .day-pills { display: grid; grid-template-columns: repeat(7, minmax(0,1fr)); gap: .45rem; margin-top: .2rem; }
    .day-pill { position: relative; }
    .day-pill input { position: absolute; opacity: 0; pointer-events: none; }
    .day-pill label {
      display: flex; align-items: center; justify-content: center;
      width: 100%; min-height: 38px;
      border-radius: 10px; border: 1.5px solid var(--border);
      background: var(--surface); color: var(--text-muted);
      font-size: .78rem; font-weight: 700; letter-spacing: .3px;
      cursor: pointer; transition: all var(--transition);
      text-transform: uppercase;
    }
    .day-pill input:checked + label {
      background: var(--primary); border-color: var(--primary); color: #fff;
      box-shadow: 0 3px 10px rgba(26,158,120,.28);
    }
    .day-pill label:hover { border-color: var(--primary); color: var(--primary); }
    @media (max-width: 768px) {
      .day-pills { grid-template-columns: repeat(4, minmax(0,1fr)); }
    }
    .course-combo { position: relative; z-index: 40; }
    #fCourseDisplay { width: 100%; padding: .5rem .85rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: .88rem; font-family: inherit; background: var(--surface); color: var(--text); }
    #fCourseDisplay:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-mid); outline: none; }
    .course-menu { display: none; position: fixed; z-index: 1200; max-height: 320px; overflow-y: auto; background: var(--surface); border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 12px 24px rgba(0,0,0,.14); }
    .course-menu.open { display: block; }
    .course-option { padding: .55rem .75rem; font-size: .84rem; cursor: pointer; border-bottom: 1px solid var(--border); }
    .course-option:last-child { border-bottom: none; }
    .course-option:hover, .course-option.active { background: var(--primary-light); color: var(--primary-dark); }
    .course-option .code { font-weight: 700; margin-right: .35rem; }
    .wizard-step { display: none; }
    .wizard-step.active { display: block; animation: fadeUp .2s ease; }
.review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .7rem; }
    .review-item { border: 1px solid var(--border); border-radius: 10px; padding: .6rem .75rem; background: var(--bg); }
    .review-item.full { grid-column: 1 / -1; }
    .review-label { font-size: .65rem; text-transform: uppercase; letter-spacing: .7px; color: var(--text-muted); font-weight: 700; margin-bottom: .2rem; }
    .review-value { font-size: .84rem; font-weight: 600; color: var(--text); word-break: break-word; }
    .modal-footer { padding: .9rem 1.6rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: .6rem; background: var(--surface); }
    .btn { padding: .5rem 1.3rem; border-radius: var(--radius-sm); font-size: .86rem; font-weight: 600; font-family: inherit; cursor: pointer; border: none; transition: all var(--transition); display: inline-flex; align-items: center; gap: .4rem; }
    .btn-primary { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; box-shadow: 0 2px 10px rgba(26,158,120,.3); }
    .btn-primary:hover:not(:disabled) { opacity: .9; transform: translateY(-1px); }
    .btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; }
    .btn-ghost { background: var(--bg); color: var(--text); border: 1.5px solid var(--border); }
    .btn-ghost:hover { border-color: var(--primary); color: var(--primary); }

    /* â”€â”€ SPINNER / SKELETON â”€â”€ */
    .skeleton { background: linear-gradient(90deg, var(--border) 25%, var(--bg) 50%, var(--border) 75%); background-size: 200% 100%; animation: shimmer 1.3s infinite; border-radius: 8px; }
    @keyframes shimmer { to { background-position: -200% 0; } }
    .sk-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; height: 190px; }
    .spin { display: inline-block; width: 13px; height: 13px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: sp .7s linear infinite; }
    @keyframes sp { to { transform: rotate(360deg); } }

    /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       GOOGLE MEET BUTTON ON CARD
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
    .meet-bar {
      padding: .55rem 1.1rem .65rem;
      border-top: 1px solid var(--border);
      background: var(--bg);
      display: flex; align-items: center; gap: .5rem;
      flex-wrap: wrap;
    }
    .meet-btn {
      display: inline-flex; align-items: center; gap: .45rem;
      background: #1a73e8; color: #fff;
      border: none; border-radius: 8px;
      padding: .38rem .85rem;
      font-size: .76rem; font-weight: 700;
      cursor: pointer; font-family: inherit;
      transition: all var(--transition);
      text-decoration: none; white-space: nowrap;
      box-shadow: 0 2px 6px rgba(26,115,232,.35);
    }
    .meet-btn:hover { background: #1558b0; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(26,115,232,.4); }
    .meet-btn svg { width: 13px; height: 13px; flex-shrink: 0; }
    .meet-btn-refresh {
      display: inline-flex; align-items: center; gap: .35rem;
      background: none; border: 1.5px solid var(--border);
      color: var(--text-muted); border-radius: 8px;
      padding: .34rem .7rem;
      font-size: .72rem; font-weight: 600;
      cursor: pointer; font-family: inherit;
      transition: all var(--transition); white-space: nowrap;
    }
    .meet-btn-refresh:hover { border-color: #1a73e8; color: #1a73e8; background: #e8f0fe; }
    .meet-btn-refresh.spinning i { animation: sp .7s linear infinite; }
    .meet-code-pill {
      font-size: .68rem; color: var(--text-muted);
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 6px; padding: .18rem .5rem;
      font-family: 'DM Mono', monospace; letter-spacing: .5px;
      cursor: pointer; transition: all var(--transition);
      white-space: nowrap;
    }
    .meet-code-pill:hover { border-color: #1a73e8; color: #1a73e8; }
    .meet-loading {
      font-size: .72rem; color: var(--text-muted);
      display: flex; align-items: center; gap: .35rem;
    }
    body.dark .meet-bar { background: rgba(0,0,0,.15); }
    body.dark .meet-code-pill { background: var(--bg); }

    @media (max-width: 900px) { .stats-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 768px) {
      :root { --nav-h: 56px; --footer-h: 72px; }
      .topnav { padding: 0 1rem; }
      .stats-grid { grid-template-columns: 1fr 1fr; gap: .65rem; }
      .class-grid { grid-template-columns: 1fr; }
      .profile-stats { grid-template-columns: 1fr; }
      .ps { border-right: none; border-bottom: 1px solid var(--border); padding-bottom: .6rem; }
      .ps:last-child { border-bottom: none; }
      .modal-backdrop { align-items: center; justify-content: center; }
      .modal-box { margin: .8rem; border-radius: 16px; max-height: calc(100vh - 1.6rem); }
      .form-grid, .form-grid.g3, .form-grid.g1 { grid-template-columns: 1fr; }
      .faculty-footer {
        height: var(--footer-h);
        padding: .45rem .9rem .5rem;
        display: grid;
        grid-template-columns: 1fr auto;
        grid-template-areas:
          "copy brand"
          "badge badge";
        align-items: center;
        row-gap: .3rem;
        column-gap: .5rem;
      }
      .footer-copyright {
        grid-area: copy;
        font-size: .68rem;
        line-height: 1.1;
        opacity: .92;
      }
      .footer-sem-badge {
        grid-area: badge;
        width: 100%;
        justify-content: center;
        padding: .24rem .65rem;
        font-size: .69rem;
      }
      .footer-brand {
        grid-area: brand;
        justify-self: end;
        font-size: .72rem;
      }
      .role-switcher { display: none; }
    }

/* â”€â”€ AVATAR ACTION DROPDOWN â”€â”€ */
.avatar-menu-wrap { position: relative; display: inline-block; }
.avatar-action-menu {
  position: absolute; top: calc(100% + 10px); left: 50%;
  transform: translateX(-50%) translateY(-6px);
  background: var(--surface); border: 1.5px solid var(--border);
  border-radius: 14px; box-shadow: var(--shadow-lg);
  min-width: 220px; z-index: 300; overflow: hidden;
  opacity: 0; pointer-events: none;
  transition: opacity .2s ease, transform .2s ease;
}
.avatar-action-menu.show {
  opacity: 1; pointer-events: auto;
  transform: translateX(-50%) translateY(0);
}
.avatar-action-menu::before {
  content: ''; position: absolute; top: -6px; left: 50%;
  transform: translateX(-50%); width: 12px; height: 12px;
  background: var(--surface); border-top: 1.5px solid var(--border);
  border-left: 1.5px solid var(--border); rotate: 45deg;
}
.aa-item {
  display: flex; align-items: center; gap: .75rem;
  padding: .72rem 1.1rem; width: 100%;
  border: none; background: none; font-family: inherit;
  font-size: .86rem; font-weight: 500; color: var(--text);
  cursor: pointer; text-align: left;
  transition: background var(--transition);
}
.aa-item:hover { background: var(--bg); }
.aa-item.danger { color: var(--danger); }
.aa-item.danger:hover { background: #fdecea; }
.aa-icon {
  width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: .88rem;
}
.aa-sep { height: 1px; background: var(--border); margin: 0 .85rem; }


.notif-panel{position:fixed;top:calc(var(--nav-h) + 8px);right:1rem;width:320px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-lg);z-index:300;display:none;overflow:hidden;max-height:420px;overflow-y:auto;}
.notif-panel.show{display:block;animation:popIn .18s ease;}
.notif-panel-header{padding:.75rem 1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--surface);z-index:1;}
.notif-panel-title{font-weight:700;font-size:.88rem;}
.notif-item{display:flex;align-items:flex-start;gap:.65rem;padding:.75rem 1rem;border-bottom:1px solid var(--border);transition:background var(--transition);}
.notif-item:last-child{border-bottom:none;}
.notif-item:hover{background:var(--primary-light);}
.notif-item.unread{background:rgba(26,158,120,.04);}
.notif-dot2{width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:.35rem;}
.notif-dot2.read{background:transparent;border:1.5px solid var(--border);}
.notif-text{font-size:.8rem;line-height:1.5;}
.notif-ts{font-size:.68rem;color:var(--text-muted);margin-top:.15rem;}

/* â”€â”€ VIEW TOGGLE â”€â”€ */
.view-toggle { display: inline-flex; border: 0.5px solid var(--border); border-radius: 8px; overflow: hidden; }
.view-toggle-btn { width: 32px; height: 32px; border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: .82rem; display: flex; align-items: center; justify-content: center; transition: all var(--transition); }
.view-toggle-btn.active { background: var(--primary); color: #fff; }
.view-toggle-btn:not(.active):hover { background: var(--primary-light); color: var(--primary); }

.sort-btn { display: inline-flex; align-items: center; gap: .3rem; padding: .28rem .7rem; border-radius: 20px; border: 1.5px solid var(--border); background: none; font-size: .72rem; font-weight: 600; font-family: inherit; color: var(--text-muted); cursor: pointer; transition: all var(--transition); }
.sort-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
.sort-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* â”€â”€ CLASS TABLE â”€â”€ */
.class-table-wrap { border-radius: var(--radius); border: 1px solid var(--border); background: var(--surface); overflow: visible; box-shadow: var(--shadow); }
.tbl-toolbar { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--border); flex-wrap: wrap; }
.tbl-search { flex: 1; min-width: 160px; display: flex; align-items: center; gap: 8px; background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0 10px; height: 32px; }
.tbl-search input { border: none; background: transparent; outline: none; font-size: .8rem; color: var(--text); width: 100%; font-family: inherit; }
.tbl-search i { color: var(--text-muted); font-size: .75rem; flex-shrink: 0; }
.tbl-count { font-size: .72rem; color: var(--text-muted); flex-shrink: 0; }
.class-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.class-table thead tr { border-bottom: 1px solid var(--border); }
.class-table thead th { padding: .6rem .9rem; text-align: left; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--text-muted); white-space: nowrap; cursor: pointer; user-select: none; transition: color var(--transition); }
.class-table thead th:hover { color: var(--primary); }
.class-table thead th.tbl-sorted { color: var(--primary); }
.class-table thead th .sort-ic { opacity: .3; margin-left: 3px; font-size: .6rem; transition: opacity var(--transition); }
.class-table thead th.tbl-sorted .sort-ic { opacity: 1; }
.class-table tbody tr { border-bottom: 1px solid var(--border); cursor: pointer; transition: background var(--transition); }
.class-table tbody tr:last-child { border-bottom: none; }
.class-table tbody tr:hover { background: var(--primary-light); }
.class-table td { padding: .7rem .9rem; vertical-align: middle; }
.tbl-name-cell { display: flex; align-items: center; gap: 9px; }
.tbl-name-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.tbl-name-main { font-weight: 600; font-size: .82rem; color: var(--text); }
.tbl-name-sub { font-size: .7rem; color: var(--text-muted); margin-top: 1px; }
.tbl-muted { font-size: .78rem; color: var(--text-muted); }
.tbl-empty { text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); font-size: .82rem; }
.tbl-empty i { font-size: 1.6rem; opacity: .25; display: block; margin-bottom: .5rem; }
</style>
</head>
<body>

<!-- TOP NAV -->
<nav class="topnav">
  <button class="menu-btn" id="menuToggle" title="Toggle sidebar">
    <i class="fas fa-bars"></i>
  </button>
  <a href="#" class="nav-brand">
    <div class="brand-logo"><i class="fas fa-book-open"></i></div>
    TERELEARN
  </a>
  <div class="nav-actions">
    <?php if ($isAlsoDean): ?>
    <div class="role-switcher" onclick="switchToDeanView()" title="Switch to <?= htmlspecialchars($deanRoleLabel) ?> View">
      <div class="role-switcher-track"></div>
      <div class="role-switcher-label">
        <i class="fas fa-user-tie" style="font-size:.65rem;"></i>
        Switch to <?= htmlspecialchars($deanRoleLabel) ?>
      </div>
    </div>
    <?php endif; ?>
    <button class="icon-btn" id="darkToggle" title="Dark mode"><i class="fas fa-moon"></i></button>
    <button class="icon-btn" id="notifBtn" title="Notifications">
  <i class="fas fa-bell"></i>
  <span class="notif-dot" id="notifDot" style="display:none;position:absolute;top:6px;right:6px;width:8px;height:8px;background:var(--danger);border-radius:50%;border:2px solid var(--surface);"></span>
</button>
        <div class="nav-avatar" id="navAvatar" title="Profile" onclick="document.querySelector('.nav-item[data-page=\'profile\']').click()" style="cursor:pointer;">F</div>
  </div>
  <!-- NOTIF PANEL -->
<div class="notif-panel" id="notifPanel">
  <div class="notif-panel-header">
    <span class="notif-panel-title">Notifications</span>
    <button style="font-size:.72rem;font-weight:600;color:var(--primary);background:none;border:none;cursor:pointer;"
            onclick="fetch('API/facultyUI/mark_notifications_read.php',{method:'POST'}).then(()=>{loadFacultyNotifs();document.getElementById('notifDot').style.display='none';})">
      Mark all read
    </button>
  </div>
  <div id="notifList">
    <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.82rem;">
      <i class="fas fa-spinner fa-spin"></i> Loading...
    </div>
  </div>
</div>
</nav>

<div class="overlay" id="overlay"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-faculty">
        <div class="s-avatar" id="sidebarAvatar" onclick="document.querySelector('.nav-item[data-page=\'profile\']').click()" style="cursor:pointer;">F</div>
    <div>
      <div class="s-name" id="sidebarName">Loading...</div>
      <div class="s-role">Faculty Member<?php if ($isAlsoDean): ?> - <span style="color:var(--warning);font-weight:700;"><?= htmlspecialchars($deanRoleLabel) ?></span><?php endif; ?></div>
    </div>
  </div>
  <div class="nav-section-label">Main</div>
  <button class="nav-item active" data-page="dashboard"><i class="fas fa-th-large"></i><span>Dashboard</span></button>
  <button class="nav-item" data-page="classes"><i class="fas fa-chalkboard"></i><span>My Classes</span><span class="nav-badge" id="classBadge">1</span></button>
  <button class="nav-item" data-page="archive"><i class="fas fa-archive"></i><span>Archive</span><span class="nav-badge" id="archiveBadge" style="background:#94a3b8;">0</span></button>
  <div class="nav-section-label" style="margin-top:.75rem;">Account</div>
  <button class="nav-item" data-page="profile"><i class="fas fa-user-circle"></i><span>Profile</span></button>
  <button class="nav-item" data-page="settings"><i class="fas fa-sliders-h"></i><span>Settings</span></button>

  <?php if ($isAlsoDean): ?>
  <div class="nav-section-label" style="margin-top:.75rem;">Switch View</div>
  <div class="sidebar-role-switcher" onclick="switchToDeanView()">
    <div class="srs-icon"><i class="fas fa-user-tie"></i></div>
    <div class="srs-text">
      <div class="srs-label"><?= htmlspecialchars($deanRoleLabel) ?> Dashboard</div>
      <div class="srs-sub">Switch to your <?= htmlspecialchars(strtolower($deanRoleLabel)) ?> view</div>
    </div>
    <i class="fas fa-arrow-right srs-arrow"></i>
  </div>
  <?php endif; ?>

  <div class="sidebar-footer-inner">
    <a href="signin.php" class="signout-btn"><i class="fas fa-sign-out-alt"></i><span>Sign Out</span></a>
  </div>
</aside>

<!-- MAIN -->
<main class="main" id="main">

  <!-- DASHBOARD -->
  <div class="page active" data-page="dashboard">
    <div class="faculty-dash">
      <div class="faculty-dash-main">
        <div class="fd-card">
          <div class="fd-welcome-title" id="welcomeTitle">Welcome back, Faculty!</div>
          <div class="fd-welcome-meta" id="fdWelcomeMeta">No active semester • 0 active sections • 0 students</div>
        </div>

        <div class="fd-stat-grid">
          <div class="fd-stat-card">
            <div class="fd-stat-label">Active Sections</div>
            <div class="fd-stat-value" id="statClasses">0</div>
          </div>
          <div class="fd-stat-card">
            <div class="fd-stat-label">Total Students</div>
            <div class="fd-stat-value" id="statStudents">0</div>
          </div>
          <div class="fd-stat-card is-good">
            <div class="fd-stat-label">High Performers</div>
            <div class="fd-stat-value" id="statHighPerformers">0</div>
          </div>
          <div class="fd-stat-card is-risk">
            <div class="fd-stat-label">At-Risk Students</div>
            <div class="fd-stat-value" id="statAtRisk">0</div>
          </div>
        </div>

        <div class="fd-monitor-grid">
          <div class="fd-graph-stack">
            <div class="fd-card fd-points-card">
              <div class="fd-points-head">
                <div class="fd-points-title"><i class="fas fa-chart-line"></i> Student Performance</div>
                <div class="fd-points-note">Average by assessment type</div>
              </div>
              <div class="fd-points-chart" id="fdPointsChart"></div>
            </div>

            <div class="fd-card">
              <div class="fd-card-head">
                <span>Class Performance Monitor</span>
                <span class="fd-card-sub">High / Average / At-risk</span>
              </div>
              <div class="fd-class-bars" id="fdClassPerformanceBars"></div>
            </div>
          </div>

          <div class="fd-leader-stack">
            <div class="fd-card">
              <div class="fd-card-head">
                <span>Top Perfect Scorers</span>
                <span class="fd-card-sub">Perfect scores</span>
              </div>
              <div class="fd-leader-list" id="fdPerfectLeaderboard"></div>
            </div>

            <div class="fd-card">
              <div class="fd-card-head">
                <span>At-Risk Watchlist</span>
                <span class="fd-card-sub">Below 75%</span>
              </div>
              <div class="fd-leader-list" id="fdRiskLeaderboard"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MY CLASSES -->
  <div class="page" data-page="classes">
    <div class="page-header">
      <div>
        <div class="page-title">My Classes</div>
        <div class="page-subtitle">Admin-assigned and self-created classes</div>
      </div>
      <button class="btn btn-primary" id="btnNewClass"><i class="fas fa-plus"></i> New Class</button>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;margin-bottom:.5rem;">
  <div class="section-divider" style="margin:0;flex:1;">
    <div class="sd-icon"><i class="fas fa-user-edit"></i></div>
    My Created Classes
  </div>
  <div style="display:flex;align-items:center;gap:.4rem;flex-shrink:0;position:relative;">
    <span style="font-size:.72rem;color:var(--text-muted);font-weight:600;">Sort:</span>
    <button class="sort-btn active" id="mainSortDirBtn2" onclick="toggleMainSort(this)">
      <i class="fas fa-sort-alpha-down"></i> A-Z
    </button>
    <div style="position:relative;">
      <button class="sort-btn active" id="sortFieldDropBtn2" onclick="toggleSortDropdown2(event)" style="gap:.35rem;">
        <i class="fas fa-filter"></i> Course <i class="fas fa-chevron-down" style="font-size:.6rem;"></i>
      </button>
      <div id="sortFieldMenu2" style="display:none;position:absolute;top:calc(100% + 6px);right:0;background:var(--surface);border:1.5px solid var(--border);border-radius:10px;box-shadow:var(--shadow-md);min-width:140px;z-index:999;overflow:hidden;">
        <button onclick="setMainSortField('subject_code')" style="display:flex;align-items:center;gap:.55rem;width:100%;padding:.55rem .9rem;border:none;background:none;font-size:.82rem;font-weight:600;font-family:inherit;color:var(--text);cursor:pointer;" onmouseover="this.style.background='var(--primary-light)';this.style.color='var(--primary)'" onmouseout="this.style.background='none';this.style.color='var(--text)'">
          <i class="fas fa-book-open" style="font-size:.72rem;color:var(--primary);"></i> Course Code
        </button>
        <div style="height:1px;background:var(--border);margin:0 .6rem;"></div>
        <button onclick="setMainSortField('section')" style="display:flex;align-items:center;gap:.55rem;width:100%;padding:.55rem .9rem;border:none;background:none;font-size:.82rem;font-weight:600;font-family:inherit;color:var(--text);cursor:pointer;" onmouseover="this.style.background='var(--primary-light)';this.style.color='var(--primary)'" onmouseout="this.style.background='none';this.style.color='var(--text)'">
          <i class="fas fa-layer-group" style="font-size:.72rem;color:var(--primary);"></i> Section
        </button>
      </div>
    </div>
    <div class="view-toggle" title="Toggle view">
      <button class="view-toggle-btn active" id="clsViewCards" onclick="setClsView('cards')" title="Card view"><i class="fas fa-th-large"></i></button>
      <button class="view-toggle-btn" id="clsViewTable" onclick="setClsView('table')" title="Table view"><i class="fas fa-table"></i></button>
    </div>
  </div>
</div>
<div class="class-grid" id="myClassGrid"></div>
<div class="class-table-wrap" id="myClassTable" style="display:none;"></div>

<div class="section-divider" style="margin-top:2rem;">
  <div class="sd-icon"><i class="fas fa-shield-alt"></i></div>
  Assigned by Admin
</div>
<div class="class-grid" id="adminClassGrid"><div class="sk-card skeleton"></div></div>
<div class="class-table-wrap" id="adminClassTable" style="display:none;"></div>
  </div>

  <!-- ARCHIVE -->
  <div class="page" data-page="archive">
    <div class="page-header">
      <div>
        <div class="page-title">Archive</div>
        <div class="page-subtitle">Classes from previous semesters, grouped by school year and semester.</div>
      </div>
    </div>
    <div id="archiveEmpty" class="empty-state" style="display:none;">
      <div class="empty-icon"><i class="fas fa-archive"></i></div>
      <div class="empty-title">No archived classes yet</div>
    </div>
    <div id="archiveContainer"></div>
  </div>

  <!-- PROFILE -->
  <div class="page" data-page="profile">
    <div class="page-header"><div><div class="page-title">Profile</div><div class="page-subtitle">Your faculty information</div></div></div>
    <div class="profile-card">
      <div class="profile-banner"></div>
      <div class="profile-body">
        <div class="avatar-menu-wrap">
          <div class="profile-avatar" id="profileAvatar" title="Change photo" onclick="toggleAvatarMenu(event)">
            <img src="" alt="profile photo" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;display:block;">
            <div class="avatar-upload-overlay"><i class="fas fa-camera"></i></div>
          </div>
          <div class="avatar-action-menu" id="avatarActionMenu">
            <button class="aa-item" onclick="triggerPickPhoto()">
              <div class="aa-icon" style="background:var(--primary-light);color:var(--primary);"><i class="fas fa-image"></i></div>
              <div><div style="font-weight:600;">Set new profile photo</div><div style="font-size:.73rem;color:var(--text-muted);margin-top:.05rem;">Upload from your device</div></div>
            </button>
            <div class="aa-sep"></div>
            <button class="aa-item danger" id="menuRemoveBtn" onclick="removeProfilePicture()" style="display:none;">
              <div class="aa-icon" style="background:#fdecea;color:var(--danger);"><i class="fas fa-trash-alt"></i></div>
              <div><div style="font-weight:600;">Delete current photo</div><div style="font-size:.73rem;color:inherit;opacity:.75;margin-top:.05rem;">Revert to default avatar</div></div>
            </button>
          </div>
          <input type="file" id="picInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none" onchange="uploadProfilePicture(this)">
        </div>
        <button onclick="toggleAvatarMenu(event)" style="display:inline-flex;align-items:center;gap:.4rem;margin-bottom:.6rem;background:var(--primary-light);color:var(--primary);border:1.5px solid var(--primary);border-radius:8px;padding:.3rem .9rem;font-size:.78rem;font-weight:600;font-family:inherit;cursor:pointer;transition:all var(--transition);">
          Change Profile Picture
        </button>
        <div class="profile-name" id="profileName">Loading...</div>
        <div class="profile-sub"  id="profileEmail">-</div>
        <?php if ($isAlsoDean): ?>
        <div style="margin-top:.5rem;">
          <span style="display:inline-flex;align-items:center;gap:.35rem;background:#fff3e0;color:var(--warning);border:1.5px solid #ffd180;border-radius:20px;padding:.22rem .75rem;font-size:.72rem;font-weight:700;">
            <i class="fas fa-user-tie" style="font-size:.6rem;"></i> Also a <?= htmlspecialchars($deanRoleLabel) ?>
          </span>
        </div>
        <?php endif; ?>
        <div class="profile-stats">
          <div class="ps"><div class="ps-val" id="psClasses">-</div><div class="ps-lbl">Classes</div></div>
          <div class="ps"><div class="ps-val" id="psStudents">-</div><div class="ps-lbl">Students</div></div>
          <div class="ps"><div class="ps-val" id="psFacNo">-</div><div class="ps-lbl">Faculty No.</div></div>
        </div>
      </div>
    </div>
  </div>

  <!-- SETTINGS -->
  <div class="page" data-page="settings">
    <div class="page-header"><div><div class="page-title">Settings</div><div class="page-subtitle">Customize your dashboard preferences</div></div></div>
    <div class="profile-card" style="max-width:480px;">
      <div style="padding:1.1rem 1.6rem;display:flex;flex-direction:column;">
        <div style="padding:1rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
          <div><div style="font-weight:600;font-size:.9rem;"><i class="fas fa-moon" style="margin-right:.5rem;color:var(--text-muted);"></i>Dark Mode</div><div style="font-size:.8rem;color:var(--text-muted);margin-top:.15rem;">Toggle light / dark theme</div></div>
          <button class="icon-btn" id="settingsDark"><i class="fas fa-moon"></i></button>
        </div>
        <?php if ($isAlsoDean): ?>
        <div style="padding:1rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
          <div>
            <div style="font-weight:600;font-size:.9rem;"><i class="fas fa-user-tie" style="margin-right:.5rem;color:var(--warning);"></i><?= htmlspecialchars($deanRoleLabel) ?> View</div>
            <div style="font-size:.8rem;color:var(--text-muted);margin-top:.15rem;">Switch to your <?= htmlspecialchars(strtolower($deanRoleLabel)) ?> dashboard</div>
          </div>
          <button class="icon-btn" onclick="switchToDeanView()" style="color:var(--warning);"><i class="fas fa-arrow-right"></i></button>
        </div>
        <?php endif; ?>
        <div style="padding:1rem 0;display:flex;align-items:center;justify-content:space-between;">
          <div><div style="font-weight:600;font-size:.9rem;"><i class="fas fa-key" style="margin-right:.5rem;color:var(--text-muted);"></i>Change Password</div><div style="font-size:.8rem;color:var(--text-muted);margin-top:.15rem;">Update your account password</div></div>
          <i class="fas fa-chevron-right" style="color:var(--text-muted);font-size:.8rem;"></i>
        </div>
      </div>
    </div>
  </div>

</main>

<!-- FIXED FOOTER -->
<footer class="faculty-footer">
  <span class="footer-copyright p-2">Copyright &copy; 2025-2026</span>
  <span class="footer-sem-badge ms-2" id="footerSemBadge">
    <i class="fas fa-calendar-alt"></i>
    <span id="footerSemText">Loading...</span>
  </span>
  <span class="footer-brand">TERELEARN</span>
</footer>

<!-- CLASS MODAL -->
<div class="modal-backdrop" id="classModal">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">
        <div class="modal-title-icon"><i class="fas fa-chalkboard"></i></div>
        <span id="modalHeading">Create New Class</span>
      </div>
      <button class="modal-close" id="modalClose"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-progress">
      <div class="stepper">
        <div class="stepper-line"><div class="stepper-line-fill" id="classProgressFill"></div></div>
        <div class="stepper-step active" data-step-index="1">
          <div class="stepper-dot"><i class="fas fa-tag"></i></div>
          <div class="stepper-label">Type</div>
        </div>
        <div class="stepper-step" data-step-index="2">
          <div class="stepper-dot"><i class="fas fa-id-card"></i></div>
          <div class="stepper-label">Basic Info</div>
        </div>
        <div class="stepper-step" data-step-index="3">
          <div class="stepper-dot"><i class="fas fa-at"></i></div>
          <div class="stepper-label">Schedule</div>
        </div>
        <div class="stepper-step" data-step-index="4">
          <div class="stepper-dot"><i class="fas fa-check"></i></div>
          <div class="stepper-label">Review</div>
        </div>
      </div>
    </div>
    <form id="classForm" novalidate>
      <input type="hidden" id="editClassId">
      <div class="modal-body">
        <div class="wizard-step active" data-step="1">
          <div class="form-section">
            <div class="form-section-label"><i class="fas fa-tag"></i> Class Type</div>
            <div class="form-grid g1" style="margin-bottom:.7rem;">
              <div class="form-group">
                <label>Course / Program <span class="required">*</span></label>
                <div class="course-combo">
                  <input type="text" id="fCourseDisplay" placeholder="Select course" autocomplete="off">
                  <div id="fCourseMenu" class="course-menu"></div>
                </div>
                <input type="hidden" id="fCourse" value="">
              </div>
            </div>
          </div>
        </div>

        <div class="wizard-step" data-step="2">
          <div class="form-section">
            <div class="form-section-label"><i class="fas fa-id-card"></i> Basic Information</div>
            <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-bottom:.7rem;">
              <div class="form-group">
                <label>Year Level <span class="required">*</span></label>
                <select id="fYearLevel" disabled onchange="onYearChange()">
                  <option value="">Select program first</option>
                </select>
                <div id="fYearHint" style="font-size:.72rem;color:var(--text-muted);margin-top:.18rem;"></div>
              </div>
              <div class="form-group" id="fSectionWrap">
                <label>Section <span class="required">*</span></label>
                <select id="fSection" disabled>
                  <option value="">Select year level first</option>
                </select>
                <div id="fSectionHint" style="font-size:.72rem;color:var(--text-muted);margin-top:.18rem;"></div>
              </div>
            </div>
            <div class="form-grid g1" style="margin-bottom:.7rem;">
              <div class="form-group">
                <label>Course <span class="required">*</span></label>
                <select id="fSubject" disabled onchange="autoFillClassName()">
                  <option value="">- Select Program first -</option>
                </select>
              </div>
            </div>
            <div class="form-grid g1" style="margin-bottom:.7rem;">
              <div class="form-group">
                <label style="display:flex;align-items:center;justify-content:space-between;">
                  <span>Semester</span>
                  <span style="font-size:.68rem;font-weight:500;color:var(--primary);background:var(--primary-light);border-radius:6px;padding:.15rem .55rem;display:inline-flex;align-items:center;gap:.3rem;">
                    <i class="fas fa-lock" style="font-size:.6rem;"></i> Set by Admin
                  </span>
                </label>
                <input type="hidden" id="fSemester">
                <div id="fSemesterDisplay" style="width:100%;padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.88rem;background:var(--bg);color:var(--text-muted);display:flex;align-items:center;gap:.5rem;">
                  <i class="fas fa-calendar-alt" style="color:var(--primary);font-size:.8rem;"></i>
                  <span id="fSemesterText">Loading...</span>
                </div>
              </div>
            </div>
            <div class="form-grid g1">
              <div class="form-group">
                <label style="display:flex;align-items:center;justify-content:space-between;">
                  <span>Class / Folder Name <span style="font-size:.68rem;font-weight:400;color:var(--text-muted);text-transform:none;letter-spacing:0;">(optional - auto-filled)</span></span>
                  <button type="button" id="btnAutoName" style="font-size:.7rem;font-weight:600;color:var(--primary);background:var(--primary-light);border:none;border-radius:6px;padding:.15rem .5rem;cursor:pointer;">Auto</button>
                </label>
                <input type="text" id="fClassName" placeholder="Auto-generated: 1-1 Application Development 1">
              </div>
            </div>
          </div>
        </div>

        <div class="wizard-step" data-step="3">
          <div class="form-section">
            <div class="form-section-label"><i class="fas fa-clock"></i> Schedule</div>
            <div class="form-grid" style="margin-bottom:.75rem;">
              <div class="form-group">
                <label>Class Start Time *</label>
                <div class="time-picker" data-time-target="fStartTime">
                  <select id="tpStartHour" class="tp-part" aria-label="Start hour"></select>
                  <span class="tp-sep">:</span>
                  <select id="tpStartMin" class="tp-part" aria-label="Start minute"></select>
                  <select id="tpStartAp" class="tp-part tp-ap" aria-label="Start AM/PM">
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                  </select>
                </div>
                <input type="time" id="fStartTime" class="time-hidden" step="900">
              </div>
              <div class="form-group">
                <label>Class End Time *</label>
                <div class="time-picker" data-time-target="fEndTime">
                  <select id="tpEndHour" class="tp-part" aria-label="End hour"></select>
                  <span class="tp-sep">:</span>
                  <select id="tpEndMin" class="tp-part" aria-label="End minute"></select>
                  <select id="tpEndAp" class="tp-part tp-ap" aria-label="End AM/PM">
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                  </select>
                </div>
                <input type="time" id="fEndTime" class="time-hidden" step="900">
              </div>
            </div>
            <div class="form-grid" style="margin-bottom:.75rem;">
              <div class="form-group">
                <label>Break Start (Optional)</label>
                <select id="fBreakStart" aria-label="Break start time">
                  <option value="">No break start</option>
                </select>
              </div>
              <div class="form-group">
                <label>Break End (Optional)</label>
                <select id="fBreakEnd" aria-label="Break end time">
                  <option value="">No break end</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label>Class Days</label>
              <div class="schedule-actions">
                <button type="button" class="schedule-chip" onclick="setDaysWeekdays()">Weekdays</button>
                <button type="button" class="schedule-chip" onclick="setDaysAll()">All Days</button>
                <button type="button" class="schedule-chip" onclick="clearDays()">Clear</button>
              </div>
              <div class="day-pills">
                <div class="day-pill"><input type="checkbox" id="day_Mon" value="Mon"><label for="day_Mon">Mon</label></div>
                <div class="day-pill"><input type="checkbox" id="day_Tue" value="Tue"><label for="day_Tue">Tue</label></div>
                <div class="day-pill"><input type="checkbox" id="day_Wed" value="Wed"><label for="day_Wed">Wed</label></div>
                <div class="day-pill"><input type="checkbox" id="day_Thu" value="Thu"><label for="day_Thu">Thu</label></div>
                <div class="day-pill"><input type="checkbox" id="day_Fri" value="Fri"><label for="day_Fri">Fri</label></div>
                <div class="day-pill"><input type="checkbox" id="day_Sat" value="Sat"><label for="day_Sat">Sat</label></div>
                <div class="day-pill"><input type="checkbox" id="day_Sun" value="Sun"><label for="day_Sun">Sun</label></div>
              </div>
            </div>
          </div>
        </div>

        <div class="wizard-step" data-step="4">
          <div class="form-section">
            <div class="form-section-label"><i class="fas fa-check-circle"></i> Review</div>
            <div id="classReviewGrid" class="review-grid"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" id="modalCancel">Cancel</button>
        <button type="button" class="btn btn-ghost" id="modalPrev"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="button" class="btn btn-primary" id="modalNext">Next <i class="fas fa-arrow-right"></i></button>
        <button type="submit" class="btn btn-primary" id="modalSubmit"><i class="fas fa-check"></i> Save Class</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
/* â•â• STATE â•â• */
let facultyData    = null;
let allClasses     = [];
let archivedGroups = {};
let pendingCounts  = {};
let dropdowns      = { courses: [], subjects: [] };
let dashboardAnalytics = null;
let dashboardTrendChart = null;
let dashboardStudentsChart = null;
let dashboardCompletionChart = null;
let dashboardGradeDistributionChart = null;
let dashboardStatusBreakdownChart = null;
let sidebarCollapsed = false;
let userEditedName   = false;
let currentSort      = 'az'; // 'az' or 'za'

const IS_ALSO_DEAN  = <?php echo $isAlsoDean ? 'true' : 'false'; ?>;
const DEAN_ROLE_LBL = <?php echo json_encode($deanRoleLabel); ?>;
const PALETTES = ['b-forest','b-ocean','b-sunset','b-plum','b-teal','b-rose','b-slate','b-indigo'];

/* â•â• TOAST â•â• */
function toast(msg, type = 'success') {
  Swal.fire({ toast:true, position:'top-end', icon:type, title:msg, showConfirmButton:false, timer:3200, timerProgressBar:true });
}
let currentSortField = 'subject_code'; // 'subject_code' | 'section'

function toggleMainSort(btn) {
  currentSort = currentSort === 'az' ? 'za' : 'az';
  const isAZ  = currentSort === 'az';
  const html  = `<i class="fas fa-sort-alpha-${isAZ ? 'down' : 'up'}"></i> ${isAZ ? 'A-Z' : 'Z-A'}`;
  ['mainSortDirBtn','mainSortDirBtn2'].forEach(id => {
    const b = document.getElementById(id);
    if (b) b.innerHTML = html;
  });
  renderDashGrid();
  renderClassesPage();
}

function setMainSortField(field) {
  currentSortField = field;
  const labels = { subject_code: ' Course', section: 'Section' };
  const icon   = field === 'subject_code' ? 'fa-book-open' : 'fa-layer-group';
  const html   = `<i class="fas fa-filter"></i> ${labels[field]} <i class="fas fa-chevron-down" style="font-size:.6rem;"></i>`;
  ['sortFieldDropBtn','sortFieldDropBtn2'].forEach(id => {
    const btn = document.getElementById(id);
    if (btn) btn.innerHTML = html;
  });
  closeSortDropdown();
  renderDashGrid();
  renderClassesPage();
}

function toggleSortDropdown(e) {
  e.stopPropagation();
  const menu  = document.getElementById('sortFieldMenu');
  const menu2 = document.getElementById('sortFieldMenu2');
  if (!menu) return;
  const isOpen = menu.style.display === 'block';
  if (menu2) menu2.style.display = 'none'; // close the other
  menu.style.display = isOpen ? 'none' : 'block';
}

function toggleSortDropdown2(e) {
  e.stopPropagation();
  const menu  = document.getElementById('sortFieldMenu');
  const menu2 = document.getElementById('sortFieldMenu2');
  if (!menu2) return;
  const isOpen = menu2.style.display === 'block';
  if (menu) menu.style.display = 'none'; // close the other
  menu2.style.display = isOpen ? 'none' : 'block';
}

function closeSortDropdown() {
  ['sortFieldMenu','sortFieldMenu2'].forEach(id => {
    const m = document.getElementById(id);
    if (m) m.style.display = 'none';
  });
}

document.addEventListener('click', () => closeSortDropdown());

function sortClasses(arr) {
  return [...arr].sort((a, b) => {
    let va = '', vb = '';
    if (currentSortField === 'section') {
      va = (a.section || '').toLowerCase();
      vb = (b.section || '').toLowerCase();
    } else {
      va = (a.subject_name || a.subject_code || a.class_code || '').toLowerCase();
      vb = (b.subject_name || b.subject_code || b.class_code || '').toLowerCase();
    }
    return currentSort === 'az' ? va.localeCompare(vb) : vb.localeCompare(va);
  });
}

/* â•â• VIEW TOGGLE STATE â•â• */
let dashView = 'cards'; // 'cards' | 'table'
let clsView  = 'cards';

function setDashView(v) {
  dashView = v;
  document.getElementById('dashViewCards').classList.toggle('active', v === 'cards');
  document.getElementById('dashViewTable').classList.toggle('active', v === 'table');
  renderDashGrid();
}

function setClsView(v) {
  clsView = v;
  document.getElementById('clsViewCards').classList.toggle('active', v === 'cards');
  document.getElementById('clsViewTable').classList.toggle('active', v === 'table');
  renderClassesPage();
}
/* â•â• TABLE STATE â•â• */
const tblState = {
  dash:  { filter:'all', search:'', sortCol:null, sortDir:1 },
  my:    { filter:'all', search:'', sortCol:null, sortDir:1 },
  admin: { filter:'all', search:'', sortCol:null, sortDir:1 },
};

const PALETTE_COLORS = {
  'b-forest':'#1a9e78','b-ocean':'#1f73db','b-sunset':'#f57c00',
  'b-plum':'#7b1fa2','b-teal':'#00838f','b-rose':'#c62828',
  'b-slate':'#455a64','b-indigo':'#3949ab'
};

function buildTable(classes, editable, tableId) {
  const st = tblState[tableId] || { filter:'all', search:'', sortCol:null, sortDir:1 };

  let rows = [...classes];

  /* search */
  if (st.search) {
    const q = st.search.toLowerCase();
    rows = rows.filter(c =>
      (c.subject_name||'').toLowerCase().includes(q) ||
      (c.subject_code||'').toLowerCase().includes(q) ||
      (c.section||'').toLowerCase().includes(q) ||
      (c.class_code||'').toLowerCase().includes(q)
    );
  }

  /* filter pill */
  if (st.filter === 'active') rows = rows.filter(c => +c.is_active === 1);
  if (st.filter === 'admin')  rows = rows.filter(c => (c.source||'').toLowerCase() !== 'faculty');
  if (st.filter === 'mine')   rows = rows.filter(c => (c.source||'').toLowerCase() === 'faculty');

  /* sort */
  if (st.sortCol) {
    rows.sort((a, b) => {
      let va = '', vb = '';
      if (st.sortCol === 'name')     { va = (a.subject_name||a.subject_code||a.class_code||'').toLowerCase(); vb = (b.subject_name||b.subject_code||b.class_code||'').toLowerCase(); }
      if (st.sortCol === 'section')  { va = (a.section||'').toLowerCase(); vb = (b.section||'').toLowerCase(); }
      if (st.sortCol === 'year')     { va = (a.year_level||''); vb = (b.year_level||''); }
      if (st.sortCol === 'sem')      { va = (a.class_semester||'').toLowerCase(); vb = (b.class_semester||'').toLowerCase(); }
      if (st.sortCol === 'days')     { va = (a.class_days||'').toLowerCase(); vb = (b.class_days||'').toLowerCase(); }
      if (st.sortCol === 'students') { va = +a.student_count||0; vb = +b.student_count||0; }
      return va < vb ? -st.sortDir : va > vb ? st.sortDir : 0;
    });
  }

  const thSort = (col, label) => {
    const active = st.sortCol === col ? ' tbl-sorted' : '';
    const ic = st.sortCol === col ? (st.sortDir === 1 ? '&uarr;' : '&darr;') : '&udarr;';
    return `<th class="${active}" onclick="tblSort('${tableId}','${col}')">${label} <span class="sort-ic">${ic}</span></th>`;
  };

  const emptyRow = `<tr><td colspan="5"><div class="tbl-empty"><i class="fas fa-inbox"></i>No classes match your search.</div></td></tr>`;

  const bodyRows = rows.length ? rows.map(cls => {
    const isAdmin  = (cls.source||'').toLowerCase() !== 'faculty';
    const isActive = +cls.is_active === 1;
    const palKey   = cls.subject_name || cls.subject_code || cls.course_name || cls.class_code || '';
    const pal      = paletteFor(palKey);
    const dot      = PALETTE_COLORS[pal] || '#1a9e78';
    const name     = cls.subject_name || cls.subject_code || cls.class_code || 'Class';
    const sub      = cls.course_name || '';
    const sched    = cls.schedule ? cls.schedule.split('-').map(t => fmt12(t.trim())).join(' - ') : '-';
    const dayText  = cls.class_days || '-';

    let actions = '';
    if (editable && !isAdmin) {
      actions = `<div class="tbl-kebab-wrap" onclick="event.stopPropagation()">
        <button class="tbl-kebab-btn" onclick="toggleTableRowMenu('${tableId}','${esc(cls.id)}', event, this)" title="Manage class">
          <i class="fas fa-ellipsis-v"></i>
        </button>
        <div class="tbl-kebab-menu" id="tblmenu-${esc(cls.id)}">
          <button class="tbl-kebab-item" onclick="openEdit('${esc(cls.id)}')"><i class="fas fa-pencil-alt"></i> Edit</button>
          <button class="tbl-kebab-item" onclick="toggleStatus('${esc(cls.id)}')"><i class="fas ${isActive ? 'fa-toggle-on' : 'fa-toggle-off'}"></i> ${isActive ? 'Deactivate' : 'Reactivate'}</button>
          <button class="tbl-kebab-item danger" onclick="confirmDelete('${esc(cls.id)}')"><i class="fas fa-trash"></i> Delete</button>
        </div>
      </div>`;
    } else {
      actions = `<span style="color:var(--text-muted);">-</span>`;
    }

    return `<tr onclick="openClass('${esc(cls.id)}')">
      <td><div class="tbl-name-cell">
        <span class="tbl-name-dot" style="background:${dot}"></span>
        <div><div class="tbl-name-main">${esc(name)}</div>${sub ? `<div class="tbl-name-sub">${esc(sub)}</div>` : ''}</div>
      </div></td>
      <td class="tbl-muted">${esc(cls.section||'-')}</td>
      <td>
        <div class="tbl-schedule-day">${esc(dayText)}</div>
        <div class="tbl-schedule-time">${esc(sched)}</div>
      </td>
      <td class="tbl-students">${+cls.student_count||0}</td>
      <td onclick="event.stopPropagation()">${actions}</td>
    </tr>`;
  }).join('') : emptyRow;

  const count = rows.length;
  const pills = ['all','active','admin','mine'].map(f =>
    `<button class="tbl-pill${st.filter===f?' active':''}" onclick="tblFilter('${tableId}','${f}')">${f.charAt(0).toUpperCase()+f.slice(1)}</button>`
  ).join('');

  return `<div class="tbl-toolbar">
    <div class="tbl-search">
      <i class="fas fa-search"></i>
      <input placeholder="Search..." value="${esc(st.search)}" oninput="tblSearch('${tableId}',this.value)">
    </div>
    <div class="tbl-pills">${pills}</div>
    <span class="tbl-count">${count} class${count!==1?'es':''}</span>
  </div>
  <div style="overflow-x:auto;">
    <table class="class-table">
      <thead><tr>
        ${thSort('name','Class')}
        ${thSort('section','Section')}
        <th>Schedule</th>
        ${thSort('students','Students')}
        <th></th>
      </tr></thead>
      <tbody>${bodyRows}</tbody>
    </table>
  </div>`;
}

/* table interaction handlers */
function tblFilter(id, filter) {
  if (!tblState[id]) tblState[id] = { filter:'all', search:'', sortCol:null, sortDir:1 };
  tblState[id].filter = filter;
  rerenderTable(id);
}
function tblSearch(id, val) {
  if (!tblState[id]) tblState[id] = { filter:'all', search:'', sortCol:null, sortDir:1 };
  tblState[id].search = val;
  rerenderTable(id);
}
function tblSort(id, col) {
  if (!tblState[id]) tblState[id] = { filter:'all', search:'', sortCol:null, sortDir:1 };
  const st = tblState[id];
  if (st.sortCol === col) st.sortDir *= -1; else { st.sortCol = col; st.sortDir = 1; }
  rerenderTable(id);
}
function rerenderTable(id) {
  const el = document.getElementById(id==='dash' ? 'dashClassTable' : id==='my' ? 'myClassTable' : 'adminClassTable');
  if (!el || el.style.display === 'none') return;
  const editable = id === 'my';
  const classes  = id === 'dash'
    ? allClasses.filter(c => +c.is_active === 1).slice(0,6)
    : id === 'my'
    ? allClasses.filter(c => (c.source||'').toLowerCase() === 'faculty')
    : allClasses.filter(c => (c.source||'').toLowerCase() !== 'faculty' && +c.is_active === 1);
  el.innerHTML = buildTable(classes, editable, id);
}
/* â•â• ROLE SWITCHER â•â• */
function switchToDeanView() {
  Swal.fire({
    title: `Switch to ${DEAN_ROLE_LBL} View?`,
    html: `<div style="font-size:.9rem;color:#5f6368;">You'll be taken to your <strong>${DEAN_ROLE_LBL} Dashboard</strong>.<br>Your faculty session stays active - just switch back anytime.</div>`,
    icon: 'info', showCancelButton: true,
    confirmButtonColor: '#f57c00', cancelButtonColor: '#6c757d',
    confirmButtonText: `<i class="fas fa-user-tie" style="margin-right:.4rem;"></i> Yes, switch`,
    cancelButtonText: 'Stay here',
  }).then(result => { if (result.isConfirmed) window.location.href = 'subadmin.php'; });
}

/* â•â• INIT â•â• */
document.addEventListener('DOMContentLoaded', () => {
  initNav(); initSidebar(); initDarkMode(); initModal();
  maybeResumeClassDraft();
  startMeetAutoRefresh();
  loadAll(); loadSemesterFooter();
  document.getElementById('fClassName').addEventListener('input', () => {
    userEditedName = document.getElementById('fClassName').value.trim() !== buildAutoName();
  });
  setInterval(loadClasses, 25000);
  setInterval(loadFacultyNotifs, 20000);
  setInterval(loadPendingCounts, 22000);
  loadPendingCounts();
  document.addEventListener('visibilitychange', function() {
    if (!document.hidden) { loadClasses(); loadFacultyNotifs(); loadPendingCounts(); }
  });
  document.addEventListener('click', (e) => {
    const inCardMenu = e.target.closest('.card-kebab-wrap') || e.target.closest('.card-kebab-menu');
    const inTblMenu  = e.target.closest('.tbl-kebab-wrap')  || e.target.closest('.tbl-kebab-menu');
    if (!inCardMenu) closeAllCardMenus();
    if (!inTblMenu)  closeAllTableRowMenus();
  });
});

/* â•â• SEMESTER FOOTER â•â• */
let activeSemesterLabel = '';
function loadSemesterFooter() {
  fetch('API/Admin/get_semester_footer.php').then(r => r.json()).then(d => {
    const sem = d.semester || ''; const sy = d.school_year || '';
    document.getElementById('footerSemText').textContent = (sem || '-') + ' - ' + (sy || '-');
    activeSemesterLabel = (sem && sy) ? `${sem} ${sy}` : (sem || sy || '');
    const displayEl = document.getElementById('fSemesterText');
    const hiddenEl  = document.getElementById('fSemester');
    if (displayEl) displayEl.textContent = activeSemesterLabel || 'No active semester set';
    if (hiddenEl)  hiddenEl.value        = activeSemesterLabel;
  }).catch(() => { document.getElementById('footerSemText').textContent = '-'; });
}

/* â•â• LOAD ALL â•â• */
async function loadAll() {
  // Run palette load separately - don't let its failure block classes
  loadPaletteMap().catch(() => {});
  await Promise.all([loadClasses(), loadDropdowns(), loadArchive(), loadDashboardAnalytics()]);
}

async function loadClasses() {
  try {
    const res  = await fetch('API/facultyUI/get_faculty_classes.php');
    const data = await res.json();
    if (data.status !== 'success') { toast(data.message || 'Could not load data', 'error'); return; }
    facultyData = data.faculty; allClasses = data.classes;
    renderAll();
  } catch (e) { toast('Network error - could not load data', 'error'); }
}

async function loadDashboardAnalytics() {
  try {
    const res  = await fetch('API/facultyUI/get_dashboard_analytics.php');
    const data = await res.json();
    if (data.status !== 'success') return;
    dashboardAnalytics = data;
    renderDashboard();
  } catch (e) {}
}

let _pendingReady = false, _prevTotalPending = 0;
async function loadPendingCounts() {
  try {
    const res  = await fetch('API/facultyUI/get_pending_counts.php');
    const data = await res.json();
    if (data.status !== 'success') return;
    const newTotal = Object.values(data.counts).reduce((a, b) => a + b, 0);
    if (_pendingReady && newTotal > _prevTotalPending) {
      const n = newTotal - _prevTotalPending;
      toast(n === 1 ? 'A student is requesting to join one of your classes!' : `${n} new join requests across your classes!`, 'info');
    }
    _prevTotalPending = newTotal;
    _pendingReady = true;
    pendingCounts = data.counts;
    renderAll();
  } catch (e) {}
}

async function loadArchive() {
  try {
    const res  = await fetch('API/facultyUI/get_faculty_archive.php');
    const data = await res.json();
    if (data.status !== 'success') { toast(data.message || 'Could not load archive', 'error'); return; }
    archivedGroups = data.archived_groups || {};
    renderArchivePage();
  } catch (e) { toast('Network error loading archive', 'error'); }
}

async function loadDropdowns() {
  try {
    const res  = await fetch('API/facultyUI/get_dropdowns.php');
    const data = await res.json();
    if (data.status === 'success') { dropdowns = data; buildCourseSelect(); }
  } catch (e) {}
}
/* â”€â”€ DEFAULT AVATAR SVG â”€â”€ */
const DEFAULT_AVATAR_SVG = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='50' fill='%23c8d0d8'/><circle cx='50' cy='38' r='21' fill='%23fff'/><path d='M10 101 Q10 67 50 67 Q90 67 90 101Z' fill='%23fff'/></svg>";

function setAvatarEl(el, picUrl, initials) {
  if (!el) return;
  const src = picUrl || DEFAULT_AVATAR_SVG;
  el.style.position = 'relative';
  el.style.overflow  = 'hidden';
  el.innerHTML = `<img src="${src}" alt="avatar" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:inherit;">`;
  el.style.background = 'transparent';
}

function setProfileAvatar(picUrl, initials) {
  const el = document.getElementById('profileAvatar');
  if (!el) return;
  const src = picUrl || DEFAULT_AVATAR_SVG;
  el.style.position = 'relative';
  el.style.overflow  = 'hidden';
  el.innerHTML = `<img src="${src}" alt="profile photo" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:inherit;"><div class="avatar-upload-overlay"><i class="fas fa-camera"></i></div>`;
  el.style.background = 'transparent';
  const removeBtn = document.getElementById('menuRemoveBtn');
  if (removeBtn) removeBtn.style.display = picUrl ? 'flex' : 'none';
}

function toggleAvatarMenu(e) {
  e.stopPropagation();
  const hasPic = !!(facultyData && facultyData.profile_picture);
  if (!hasPic) {
    document.getElementById('picInput').click();
    return;
  }
  const menu = document.getElementById('avatarActionMenu');
  if (!menu) return;
  menu.classList.toggle('show');
}

function triggerPickPhoto() {
  const menu = document.getElementById('avatarActionMenu');
  if (menu) menu.classList.remove('show');
  document.getElementById('picInput').click();
}

document.addEventListener('click', function(e) {
  const menu = document.getElementById('avatarActionMenu');
  const wrap = document.querySelector('.avatar-menu-wrap');
  if (menu && wrap && !wrap.contains(e.target)) {
    menu.classList.remove('show');
  }
});

async function uploadProfilePicture(input) {
  const file = input.files[0];
  if (!file) return;
  const form = new FormData();
  form.append('picture', file);
  try {
    toast('Uploading photo...', 'info');
    const res  = await fetch('API/facultyUI/update_profile_picture.php', { method: 'POST', body: form });
    const data = await res.json();
    if (data.status !== 'success') { toast(data.message || 'Upload failed', 'error'); return; }
    const f = facultyData;
    const initials = ((f.first_name?.[0] ?? '') + (f.last_name?.[0] ?? '')).toUpperCase() || 'F';
    const url = data.url + '?v=' + Date.now();
    setAvatarEl(document.getElementById('navAvatar'),     url, initials);
    setAvatarEl(document.getElementById('sidebarAvatar'), url, initials);
    setProfileAvatar(url, initials);
    if (facultyData) facultyData.profile_picture = data.url;
    toast('Profile photo updated!', 'success');
  } catch (e) { toast('Network error during upload', 'error'); }
  input.value = '';
}

async function removeProfilePicture() {
  const menu = document.getElementById('avatarActionMenu');
  if (menu) menu.classList.remove('show');
  const confirmed = await Swal.fire({
    title: 'Delete profile photo?',
    text: 'Your photo will be deleted and the default avatar will be shown.',
    icon: 'warning', showCancelButton: true,
    confirmButtonText: 'Delete', confirmButtonColor: '#d93025',
    cancelButtonText: 'Cancel'
  });
  if (!confirmed.isConfirmed) return;
  try {
    const res  = await fetch('API/facultyUI/remove_profile_picture.php', { method: 'POST' });
    const data = await res.json();
    if (data.status !== 'success') { toast(data.message || 'Could not remove photo', 'error'); return; }
    const f = facultyData;
    const initials = ((f.first_name?.[0] ?? '') + (f.last_name?.[0] ?? '')).toUpperCase() || 'F';
    setAvatarEl(document.getElementById('navAvatar'),     null, initials);
    setAvatarEl(document.getElementById('sidebarAvatar'), null, initials);
    setProfileAvatar(null, initials);
    if (facultyData) facultyData.profile_picture = null;
    toast('Profile photo deleted', 'success');
  } catch (e) { toast('Network error', 'error'); }
}

function renderAll() {
  if (!facultyData) return;
  const f = facultyData;
  const fullName = [f.first_name, f.middle_name, f.last_name].filter(Boolean).join(' ');
  const initials = ((f.first_name?.[0] ?? '') + (f.last_name?.[0] ?? '')).toUpperCase() || 'F';

  setAvatarEl(document.getElementById('navAvatar'),     f.profile_picture, initials);
  setAvatarEl(document.getElementById('sidebarAvatar'), f.profile_picture, initials);
  document.getElementById('sidebarName').textContent   = fullName;
  setProfileAvatar(f.profile_picture, initials);
  document.getElementById('profileName').textContent   = fullName;
  document.getElementById('profileEmail').textContent  = f.email || '-';
  document.getElementById('psFacNo').textContent       = f.faculty_number || '-';

  const active   = allClasses.filter(c => +c.is_active === 1);
  const students = allClasses.reduce((n, c) => n + (+c.student_count || 0), 0);

  document.getElementById('statClasses').textContent  = active.length;
  document.getElementById('statStudents').textContent = students;
  document.getElementById('classBadge').textContent   = allClasses.length;
  document.getElementById('psClasses').textContent    = allClasses.length;
  document.getElementById('psStudents').textContent   = students;

  renderDashboard();
  renderClassesPage();
}

/* â•â• SORT STATE â•â• */
let archiveSortField = 'subject_code';
let archiveSortDir   = 1; // 1 = A-Z/oldest, -1 = Z-A/newest

function toggleArchiveDir() {
  archiveSortDir = archiveSortDir === 1 ? -1 : 1;
  renderArchivePage();
}

function setArchiveSortField(field) {
  archiveSortField = field;
  renderArchivePage();
}

function renderArchivePage() {
  const container   = document.getElementById('archiveContainer');
  const emptyEl     = document.getElementById('archiveEmpty');
  const schoolYears = Object.keys(archivedGroups).sort((a, b) => b.localeCompare(a));
  let totalArchived = 0;
  schoolYears.forEach(sy => {
    const sems = archivedGroups[sy].semesters || {};
    Object.values(sems).forEach(classes => { totalArchived += classes.length; });
  });
  const statArchived = document.getElementById('statArchived');
  if (statArchived) statArchived.textContent = totalArchived;
  document.getElementById('archiveBadge').textContent  = totalArchived;
  if (!schoolYears.length) { emptyEl.style.display = ''; container.innerHTML = ''; return; }
  emptyEl.style.display = 'none';

  const sortBarHtml = `
    <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;
                margin-bottom:1.25rem;padding:.6rem .85rem;
                background:var(--surface);border:1px solid var(--border);
                border-radius:10px;box-shadow:var(--shadow);">
      <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                   letter-spacing:.7px;color:var(--text-muted);margin-right:.1rem;">
        <i class="fas fa-sort-amount-down" style="margin-right:.3rem;"></i>Sort
      </span>

      <!-- Button 1: Toggle A-Z / Z-A direction -->
      <button class="arch-sort-btn active" id="archDirBtn"
              onclick="toggleArchiveDir()"
              title="Toggle sort direction">
        <i class="fas fa-sort-alpha-${archiveSortDir===1?'down':'up'}"></i>
        ${archiveSortDir===1?'A-Z':'Z-A'}
      </button>

      <div style="width:1px;height:18px;background:var(--border);flex-shrink:0;"></div>

      <!-- Button 2: Pick sort field -->
      <button class="arch-sort-btn ${archiveSortField==='subject_code'?'active':''}"
              id="archFieldBtn_subject_code"
              onclick="setArchiveSortField('subject_code')">
        <i class="fas fa-book-open"></i> Course
      </button>
      <button class="arch-sort-btn ${archiveSortField==='section'?'active':''}"
              id="archFieldBtn_section"
              onclick="setArchiveSortField('section')">
        <i class="fas fa-layer-group"></i> Section
      </button>
      <button class="arch-sort-btn ${archiveSortField==='created_at'?'active':''}"
              id="archFieldBtn_created_at"
              onclick="setArchiveSortField('created_at')">
        <i class="fas fa-calendar-alt"></i> Date
      </button>
    </div>`;

  let html = sortBarHtml;

  schoolYears.forEach(sy => {
    const syData  = archivedGroups[sy];
    const sems    = syData.semesters || {};
    const semKeys = Object.keys(sems);
    let syTotal = 0;
    semKeys.forEach(sem => { syTotal += sems[sem].length; });
    if (syTotal === 0) return;
    const syId = 'sy_' + sy.replace(/[^a-zA-Z0-9]/g, '_');
    html += `<div class="archive-sy-group" id="${syId}">
      <div class="archive-sy-header" onclick="toggleSYGroup('${syId}')">
        <i class="fas fa-folder-open archive-sy-icon"></i>
        <span class="archive-sy-label">${esc(sy)}</span>
        <span class="archive-sy-count">${syTotal} class${syTotal !== 1 ? 'es' : ''}</span>
        <i class="fas fa-chevron-down archive-sy-chevron"></i>
      </div>
      <div class="archive-sy-body">`;
    semKeys.forEach(sem => {
      const rawClasses = sems[sem];
      if (!rawClasses || rawClasses.length === 0) return;

      const classes = [...rawClasses].sort((a, b) => {
      let va = '', vb = '';
      if (archiveSortField === 'subject_code') {
        va = (a.subject_code || a.subject_name || '').toLowerCase();
        vb = (b.subject_code || b.subject_name || '').toLowerCase();
        return va.localeCompare(vb) * archiveSortDir;
      } else if (archiveSortField === 'section') {
        va = (a.section || '').toLowerCase();
        vb = (b.section || '').toLowerCase();
        return va.localeCompare(vb) * archiveSortDir;
      } else if (archiveSortField === 'created_at') {
        const da = new Date(a.created_at || 0);
        const db = new Date(b.created_at || 0);
        return (da - db) * archiveSortDir;
      }
      return 0;
    });

      const semId = syId + '_sem_' + sem.replace(/[^a-zA-Z0-9]/g, '_');
      html += `<div class="archive-sem-group" id="${semId}">
        <div class="archive-sem-header" onclick="toggleSemGroup('${semId}')">
          <i class="fas fa-folder" style="color:var(--primary);font-size:.78rem;"></i>
          <span class="archive-sem-label">${esc(sem)}</span>
          <span class="archive-sem-count">${classes.length} class${classes.length !== 1 ? 'es' : ''}</span>
          <i class="fas fa-chevron-down archive-sem-chevron"></i>
        </div>
        <div class="archive-sem-body">
          <div class="class-grid">${classes.map(c => buildCard(c, false, true)).join('')}</div>
        </div>
      </div>`;
    });
    html += `</div></div>`;
  });

  container.innerHTML = html;
}

function toggleSYGroup(id)  { document.getElementById(id).classList.toggle('collapsed'); }
function toggleSemGroup(id) { document.getElementById(id).classList.toggle('collapsed'); }

/* â•â• HELPERS â•â• */
/* Subject â†’ palette map loaded from server. Fallback to hash if not ready. */
let subjectPaletteMap = {};
let paletteMapLoaded  = false;
 
/** Normalise a subject name the same way the PHP helper does. */
function normalisePaletteKey(str) {
  return String(str ?? '')
    .replace(/^\d+[--]\d+(?:[--]\d+)?\s+/, '')  // strip leading "3-1 " or "1-1-7 "
    .toLowerCase().trim();
}
 
/** Fallback hash (used only before the server map is loaded) */
function hashPalette(str) {
  const PALETTES = ['b-forest','b-ocean','b-sunset','b-plum','b-teal','b-rose','b-slate','b-indigo'];
  let h = 0;
  for (const ch of normalisePaletteKey(str)) h = ((h << 5) - h) + ch.charCodeAt(0);
  return PALETTES[Math.abs(h) % PALETTES.length];
}
 
/**
 * Main palette lookup.
 * Returns the server-assigned colour if the map is loaded,
 * otherwise falls back to the deterministic hash.
 */
function paletteFor(str) {
  const key = normalisePaletteKey(str);
  if (paletteMapLoaded && subjectPaletteMap[key]) {
    return subjectPaletteMap[key];
  }
  return hashPalette(str);
}
 
/**
 * Load the full subjectâ†’palette map from the server.
 * Called once on init (and again after saving a new class).
 */
async function loadPaletteMap() {
  try {
    const res  = await fetch('API/facultyUI/get_subject_pallete.php');
    const data = await res.json();
    if (data.status === 'success') {
      subjectPaletteMap = data.map || {};
      paletteMapLoaded  = true;
    }
  } catch (e) {
    // Silent - hash fallback will be used
  }
}
 
/**
 * Ensure a palette is registered for a subject name on the server,
 * then update the local map.
 * Call this after a class is successfully saved.
 */
async function registerSubjectPalette(subjectName) {
  if (!subjectName) return;
  try {
    const res  = await fetch('API/facultyUI/get_subject_pallete.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ subject_name: subjectName }),
    });
    const data = await res.json();
    if (data.status === 'success') {
      subjectPaletteMap = data.map || {};
      paletteMapLoaded  = true;
    }
  } catch (e) { /* silent */ }
}
function fmt12(t) {
  if (!t) return '';
  const [h, m] = t.split(':').map(Number);
  return `${h % 12 || 12}:${String(m).padStart(2,'0')} ${h >= 12 ? 'PM' : 'AM'}`;
}
function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function relTime(value) {
  if (!value) return '';
  const date = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return '';
  const diff = Math.max(0, Date.now() - date.getTime());
  const mins = Math.floor(diff / 60000);
  if (mins < 1) return 'Just now';
  if (mins < 60) return mins + 'm ago';
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return hrs + 'h ago';
  const days = Math.floor(hrs / 24);
  if (days < 7) return days + 'd ago';
  return date.toLocaleDateString();
}

function openClass(classId) {
  window.location.href = 'classRoom.php?class_id=' + encodeURIComponent(classId);
}

/* â•â• GOOGLE MEET CACHE â•â• */
/* â•â• COURSE â†’ YEAR â†’ SECTION CASCADE â•â• */

function onCourseChange() {
    const courseId = document.getElementById('fCourse').value;
    resetYearField();
    resetSectionField();
    filterSubjects();
    autoFillClassName();
    updateClassProgress();
    if (!courseId) return;
    loadYearLevelsForCourse(courseId);
}

function onYearChange() {
    const yearLevel = document.getElementById('fYearLevel').value;
    resetSectionField();
    filterSubjects();
    autoFillClassName();
    updateClassProgress();
    if (!yearLevel) return;
    const courseId = document.getElementById('fCourse').value;
    loadSectionsForYear(courseId, yearLevel);
}

function loadYearLevelsForCourse(courseId) {
    const yearSel  = document.getElementById('fYearLevel');
    const yearHint = document.getElementById('fYearHint');
    yearSel.disabled = true;
    yearSel.innerHTML = '<option value="">Loading...</option>';

    fetch('API/Admin/fetch_year_section_config.php?course_id=' + encodeURIComponent(courseId))
        .then(r => r.json())
        .then(res => {
            const configs = res.data || [];
            const YL = {1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year',5:'5th Year'};
            if (!configs.length) {
                yearSel.innerHTML = '<option value="">- Select Year Level -</option>'
                    + '<option value="1">1st Year</option>'
                    + '<option value="2">2nd Year</option>'
                    + '<option value="3">3rd Year</option>'
                    + '<option value="4">4th Year</option>';
                yearSel.disabled = false;
                if (yearHint) yearHint.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:var(--warning);"></i> <span style="color:var(--warning);">No section config - showing all year levels.</span>';
            } else {
                const opts = configs.map(c =>
                    `<option value="${c.year_level}">${YL[c.year_level] || c.year_level} (${c.section_count} section${c.section_count != 1 ? 's' : ''})</option>`
                ).join('');
                yearSel.innerHTML = '<option value="">- Select Year Level -</option>' + opts;
                yearSel.disabled = false;
                if (yearHint) yearHint.innerHTML = `<i class="fas fa-check-circle" style="color:var(--primary);"></i> <span style="color:var(--primary);">${configs.length} year level${configs.length !== 1 ? 's' : ''} configured.</span>`;
            }
        })
        .catch(() => {
            yearSel.innerHTML = '<option value="">- Select Year Level -</option>'
                + '<option value="1">1st Year</option>'
                + '<option value="2">2nd Year</option>'
                + '<option value="3">3rd Year</option>'
                + '<option value="4">4th Year</option>';
            yearSel.disabled = false;
            if (yearHint) yearHint.innerHTML = '<span style="color:var(--danger);">Could not load - showing fallback.</span>';
        });
}

function loadSectionsForYear(courseId, yearLevel) {
    const wrap = document.getElementById('fSectionWrap');
    wrap.innerHTML = `
        <label>Section <span class="required">*</span></label>
        <select id="fSection" disabled>
            <option value="">Loading...</option>
        </select>
        <div id="fSectionHint" style="font-size:.72rem;color:var(--text-muted);margin-top:.18rem;"></div>`;

    const params = new URLSearchParams({ year_level: yearLevel });
    if (courseId) params.append('course_id', courseId);

    fetch('API/Admin/get_sections_for_year.php?' + params.toString())
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.sections && res.sections.length) {
                const opts = res.sections.map(s =>
                    `<option value="${s}">${s}</option>`
                ).join('');
                wrap.innerHTML = `
                    <label>Section <span class="required">*</span></label>
                    <select id="fSection" style="width:100%;padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);">
                        <option value="">- Select Section -</option>
                        ${opts}
                    </select>
                    <div id="fSectionHint" style="font-size:.72rem;color:var(--primary);margin-top:.18rem;">
                        <i class="fas fa-check-circle"></i> ${res.sections.length} section${res.sections.length !== 1 ? 's' : ''} available
                    </div>`;
                document.getElementById('fSection').addEventListener('change', autoFillClassName);
                updateClassProgress();
            } else {
                wrap.innerHTML = `
                    <label>Section <span class="required">*</span></label>
                    <input type="text" id="fSection" placeholder="e.g. A"
                        style="width:100%;padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);">
                    <div id="fSectionHint" style="font-size:.72rem;color:var(--warning);margin-top:.18rem;">
                        <i class="fas fa-exclamation-triangle"></i> No section config for this year - type manually.
                    </div>`;
                document.getElementById('fSection').addEventListener('input', autoFillClassName);
                updateClassProgress();
            }
        })
        .catch(() => {
            wrap.innerHTML = `
                <label>Section <span class="required">*</span></label>
                <input type="text" id="fSection" placeholder="e.g. A"
                    style="width:100%;padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);">
                <div id="fSectionHint" style="font-size:.72rem;color:var(--danger);margin-top:.18rem;">
                    <i class="fas fa-times-circle"></i> Could not load sections - type manually.
                </div>`;
            document.getElementById('fSection').addEventListener('input', autoFillClassName);
            updateClassProgress();
        });
}

function resetYearField() {
    const yearSel  = document.getElementById('fYearLevel');
    const yearHint = document.getElementById('fYearHint');
    yearSel.innerHTML = '<option value="">Select program first</option>';
    yearSel.disabled  = true;
    yearSel.value     = '';
    if (yearHint) yearHint.innerHTML = '';
    updateClassProgress();
}

function resetSectionField() {
    document.getElementById('fSectionWrap').innerHTML = `
        <label>Section <span class="required">*</span></label>
        <select id="fSection" disabled>
            <option value="">Select year level first</option>
        </select>
        <div id="fSectionHint" style="font-size:.72rem;color:var(--text-muted);margin-top:.18rem;"></div>`;
    updateClassProgress();
}

async function restoreSectionField(courseId, yearLevel, existingSection) {
    if (!courseId || !yearLevel) return;

    document.getElementById('fCourse').value = courseId;
    syncCourseDisplayFromSelection();
    filterSubjects();

    await new Promise(resolve => {
        fetch('API/Admin/fetch_year_section_config.php?course_id=' + encodeURIComponent(courseId))
            .then(r => r.json())
            .then(res => {
                const configs = res.data || [];
                const YL = {1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year',5:'5th Year'};
                const yearSel = document.getElementById('fYearLevel');
                if (configs.length) {
                    const opts = configs.map(c =>
                        `<option value="${c.year_level}">${YL[c.year_level] || c.year_level} (${c.section_count} section${c.section_count != 1 ? 's' : ''})</option>`
                    ).join('');
                    yearSel.innerHTML = '<option value="">- Select Year Level -</option>' + opts;
                } else {
                    yearSel.innerHTML = '<option value="">- Select Year Level -</option>'
                        + '<option value="1">1st Year</option>'
                        + '<option value="2">2nd Year</option>'
                        + '<option value="3">3rd Year</option>'
                        + '<option value="4">4th Year</option>';
                }
                yearSel.disabled = false;
                yearSel.value    = yearLevel;
                resolve();
            })
            .catch(() => resolve());
    });

    await new Promise(resolve => {
        const params = new URLSearchParams({ year_level: yearLevel, course_id: courseId });
        fetch('API/Admin/get_sections_for_year.php?' + params.toString())
            .then(r => r.json())
            .then(res => {
                const wrap = document.getElementById('fSectionWrap');
                if (res.status === 'success' && res.sections && res.sections.length) {
                    const opts = res.sections.map(s =>
                        `<option value="${s}">${s}</option>`
                    ).join('');
                    wrap.innerHTML = `
                        <label>Section <span class="required">*</span></label>
                        <select id="fSection" style="width:100%;padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);">
                            <option value="">- Select Section -</option>
                            ${opts}
                        </select>
                        <div id="fSectionHint" style="font-size:.72rem;color:var(--primary);margin-top:.18rem;">
                            <i class="fas fa-check-circle"></i> ${res.sections.length} section${res.sections.length !== 1 ? 's' : ''} available
                        </div>`;
                    document.getElementById('fSection').value = existingSection || '';
                    document.getElementById('fSection').addEventListener('change', autoFillClassName);
                } else {
                    wrap.innerHTML = `
                        <label>Section <span class="required">*</span></label>
                        <input type="text" id="fSection" value="${existingSection || ''}"
                            style="width:100%;padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);">`;
                    document.getElementById('fSection').addEventListener('input', autoFillClassName);
                }
                resolve();
            })
            .catch(() => resolve());
    });
}
const meetCache = {};
let meetAutoRefreshTimer = null;


/* â•â• BUILD CARD â•â• */
function buildCard(cls, editable = false, archived = false) {
  const isAdmin  = (cls.source || '').toLowerCase() !== 'faculty';
  const isActive = +cls.is_active === 1;
  const yearSec  = [cls.year_level, cls.section].filter(Boolean).join('-');
  const subCode  = cls.subject_code || '';
const name = [cls.section, cls.subject_code].filter(Boolean).join(' ') || cls.class_code || 'Class';
  const code     = cls.subject_name || cls.subject_code || cls.class_code || '';
   /* Use subject/course name as the colour key so same subject = same colour */
  const palKey   = cls.subject_name || cls.subject_code || cls.course_name || name;
  const pal      = paletteFor(palKey);

  let schedHtml = '';
  if (cls.schedule) {
    const parts = cls.schedule.split('-').map(t => fmt12(t.trim()));
    schedHtml = `<span class="chip"><i class="fas fa-clock"></i>${esc(parts.join(' - '))}</span>`;
  }

  const archiveChip = archived ? `<span class="chip archived-chip"><i class="fas fa-archive"></i>Archived</span>` : '';
  const deactBadge  = (!isAdmin && !isActive && !archived)
    ? `<span class="deact-badge"><i class="fas fa-pause-circle"></i> Deactivated</span>` : '';

  let actionBarHtml = '';

  const srcLabel = isAdmin ? 'Admin' : 'Me';
  const srcClass = isAdmin ? 'src-admin' : 'src-faculty';
  const canManageFromMenu = !isAdmin;
  const cardMenuHtml = canManageFromMenu ? `
    <div class="card-kebab-wrap" onclick="event.stopPropagation()">
      <button class="card-kebab-btn" onclick="toggleCardMenu('${esc(cls.id)}', event, this)" title="Manage class">
        <i class="fas fa-ellipsis-v"></i>
      </button>
      <div class="card-kebab-menu" id="cardmenu-${esc(cls.id)}">
        <button class="card-kebab-item" onclick="openEdit('${esc(cls.id)}')"><i class="fas fa-pencil-alt"></i> Edit</button>
        <button class="card-kebab-item" onclick="toggleStatus('${esc(cls.id)}')"><i class="fas ${isActive ? 'fa-toggle-on' : 'fa-toggle-off'}"></i> ${isActive ? 'Deactivate' : 'Reactivate'}</button>
        <button class="card-kebab-item danger" onclick="confirmDelete('${esc(cls.id)}')"><i class="fas fa-trash"></i> Delete</button>
      </div>
    </div>` : `<span class="src-badge ${srcClass}">${srcLabel}</span>`;

  /* â”€â”€ Meet bar - only for active, non-archived classes â”€â”€ */
  const showMeet = !archived && isActive;
  const meetBarHtml = showMeet ? `
    <div class="meet-bar" id="meetbar-${esc(cls.id)}" onclick="event.stopPropagation()">
      <div class="meet-loading" id="meetloading-${esc(cls.id)}">
        <i class="fas fa-circle-notch fa-spin" style="font-size:.7rem;color:#1a73e8;"></i>
        <span style="font-size:.7rem;">Preparing Meet...</span>
      </div>
      <div id="meetready-${esc(cls.id)}" style="display:none;align-items:center;gap:.5rem;flex-wrap:wrap;">
        <a id="meetlink-${esc(cls.id)}" href="#" target="_blank" class="meet-btn" onclick="event.stopPropagation()">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4.5l-5 3.5V5a2 2 0 00-2-2H4a2 2 0 00-2 2v14a2 2 0 002 2h9a2 2 0 002-2v-3l5 3.5A1 1 0 0022 18V6a1 1 0 00-2-1.5z"/></svg>
          Join Meet
        </a>
        <span class="meet-code-pill" id="meetcodepill-${esc(cls.id)}"
              onclick="event.stopPropagation();copyMeetCode('${esc(cls.id)}')"
              title="Click to copy meet code">-</span>
      </div>
    </div>` : '';

  return `
    <div class="class-card${archived ? ' archive-card' : ''}${!isActive && !isAdmin && !archived ? ' deactivated' : ''}"
         onclick="openClass('${esc(cls.id)}')">
      <div class="card-banner ${pal}">
        ${cardMenuHtml}
        <div class="banner-title">${esc(name)}</div>
        <div class="banner-code">${esc(code)}</div>
      </div>
      <div class="card-body">
        <div class="card-chips">
          ${archiveChip}${deactBadge}
          ${cls.section        ? `<span class="chip"><i class="fas fa-layer-group"></i>${esc(cls.section)}</span>` : ''}
          ${cls.year_level     ? `<span class="chip"><i class="fas fa-graduation-cap"></i>${esc(cls.year_level)}</span>` : ''}
          ${cls.class_semester ? `<span class="chip"><i class="fas fa-calendar-alt"></i>${esc(cls.class_semester)}</span>` : ''}
          ${cls.class_days     ? `<span class="chip"><i class="fas fa-calendar-week"></i>${esc(cls.class_days)}</span>` : ''}
          ${schedHtml}
        </div>
        <div class="card-foot">
          <div class="student-count">
            <i class="fas fa-users"></i>
            ${+cls.student_count || 0} student${+cls.student_count !== 1 ? 's' : ''}
          </div>
          ${pendingCounts[cls.id] ? `<span class="pending-req-badge"><i class="fas fa-user-clock"></i>${pendingCounts[cls.id]} waiting</span>` : ''}
        </div>
      </div>
      ${meetBarHtml}
      ${actionBarHtml}
    </div>`;
}

function buildAddCard() {
  return `<div class="class-card add-card" onclick="openCreate()">
    <div class="add-card-body">
      <div class="add-icon"><i class="fas fa-plus"></i></div>
      <div class="add-label">Create New Class</div>
    </div>
  </div>`;
}
function buildEmpty(msg) {
  return `<div class="empty-state"><div class="empty-icon"><i class="fas fa-inbox"></i></div><div class="empty-title">${msg}</div></div>`;
}

/* â•â• RENDER GRIDS â•â• */
function renderDashGrid() {
  renderDashboard();
}

function fdPct(value) {
  const num = Number(value || 0);
  return Number.isInteger(num) ? `${num}%` : `${num.toFixed(1)}%`;
}

function fdCssVar(name) {
  const styles = getComputedStyle(document.body);
  return styles.getPropertyValue(name).trim() || getComputedStyle(document.documentElement).getPropertyValue(name).trim();
}

function renderFacultyPointsChart(points) {
  const el = document.getElementById('fdPointsChart');
  if (!el) return;
  const data = Array.isArray(points) ? points.filter(row => row && row.total !== null && row.total !== undefined) : [];
  if (!data.length) {
    el.innerHTML = '<div class="fd-list-empty" style="padding:5rem 1rem;">No graded performance or attendance data yet.</div>';
    return;
  }
  const width = 640;
  const height = 230;
  const padX = 54;
  const padY = 20;
  const max = 100;
  const grid = [0, 25, 50, 75, 100];
  const xStep = data.length > 1 ? (width - padX - 18) / (data.length - 1) : 0;
  const yFor = value => height - padY - (value / max) * (height - padY * 2);
  const coords = data.map((row, index) => ({
    ...row,
    x: padX + index * xStep,
    y: yFor(row.total)
  }));
  const path = coords.map((p, index) => `${index ? 'L' : 'M'} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' ');
  el.innerHTML = `
    <div class="fd-points-tooltip" id="fdPointsTooltip"></div>
    <svg viewBox="0 0 ${width} ${height}" role="img" aria-label="Your points chart">
      ${grid.map(value => `<line x1="${padX}" y1="${yFor(value).toFixed(1)}" x2="${width - 18}" y2="${yFor(value).toFixed(1)}" stroke="var(--border)" stroke-dasharray="4 4"></line><text x="${padX - 12}" y="${(yFor(value) + 4).toFixed(1)}" text-anchor="end" fill="var(--text-muted)" font-size="12">${value}%</text>`).join('')}
      ${coords.map(p => `<line x1="${p.x.toFixed(1)}" y1="${padY}" x2="${p.x.toFixed(1)}" y2="${height - padY}" stroke="var(--border)" stroke-dasharray="4 4"></line>`).join('')}
      <path d="${path}" fill="none" stroke="var(--text)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
      ${coords.map(p => `<circle class="fd-points-dot" data-date="${esc(p.date)}" data-total="${p.total}" data-change="${p.change}" cx="${p.x.toFixed(1)}" cy="${p.y.toFixed(1)}" r="5" fill="var(--text)" stroke="var(--surface)" stroke-width="2"></circle>`).join('')}
    </svg>
    <div class="fd-points-axis">${data.map(row => `<span>${esc(row.date)}</span>`).join('')}</div>`;
  const tip = document.getElementById('fdPointsTooltip');
  el.querySelectorAll('.fd-points-dot').forEach(dot => {
    dot.addEventListener('mouseenter', () => {
      const change = Number(dot.dataset.change || 0);
      tip.innerHTML = `<div class="fd-points-tooltip-day">${esc(dot.dataset.date)}</div><div class="fd-points-tooltip-total">Average ${fdPct(Number(dot.dataset.total || 0))}</div><div class="fd-points-tooltip-change">${change > 0 ? '+' : ''}${fdPct(change)}</div>`;
      tip.style.display = 'block';
      tip.style.left = `${Math.min(el.clientWidth - 125, Math.max(8, dot.cx.baseVal.value / width * el.clientWidth + 10))}px`;
      tip.style.top = `${Math.max(8, dot.cy.baseVal.value / height * 230 - 12)}px`;
    });
    dot.addEventListener('mouseleave', () => { tip.style.display = 'none'; });
  });
}

function renderClassPerformanceBars(rows) {
  const el = document.getElementById('fdClassPerformanceBars');
  if (!el) return;
  const data = Array.isArray(rows) ? rows.filter(row => Number(row.total || 0) > 0) : [];
  if (!data.length) {
    el.innerHTML = '<div class="fd-list-empty">No class performance data yet.</div>';
    return;
  }
  el.innerHTML = data.map(row => {
    const total = Math.max(1, Number(row.total || 0));
    const high = Number(row.high || 0);
    const average = Number(row.average || 0);
    const risk = Number(row.at_risk || 0);
    const ungraded = Number(row.ungraded || 0);
    const seg = value => `${Math.max(0, (value / total) * 100)}%`;
    return `<div class="fd-class-row">
      <div class="fd-class-meta">
        <div class="fd-class-name">${esc(row.display_name || row.class_name || 'Class')}</div>
        <div class="fd-class-avg">${row.average_grade === null || row.average_grade === undefined ? 'No avg' : fdPct(Number(row.average_grade))}</div>
      </div>
      <div class="fd-stackbar" title="High ${high}, Average ${average}, At-risk ${risk}, Ungraded ${ungraded}">
        <span class="fd-stackseg high" style="width:${seg(high)}"></span>
        <span class="fd-stackseg average" style="width:${seg(average)}"></span>
        <span class="fd-stackseg risk" style="width:${seg(risk)}"></span>
        <span class="fd-stackseg ungraded" style="width:${seg(ungraded)}"></span>
      </div>
    </div>`;
  }).join('') + `<div class="fd-stack-legend">
    <span class="fd-legend-item"><span class="fd-legend-dot" style="background:var(--primary);"></span>High 85%+</span>
    <span class="fd-legend-item"><span class="fd-legend-dot" style="background:var(--fd-pass-warn);"></span>Average 75-84%</span>
    <span class="fd-legend-item"><span class="fd-legend-dot" style="background:var(--danger);"></span>At-risk &lt;75%</span>
    <span class="fd-legend-item"><span class="fd-legend-dot" style="background:var(--fd-badge-muted);"></span>Ungraded</span>
  </div>`;
}

function renderPerfectLeaderboard(rows) {
  const el = document.getElementById('fdPerfectLeaderboard');
  if (!el) return;
  const data = Array.isArray(rows) ? rows : [];
  if (!data.length) {
    el.innerHTML = '<div class="fd-list-empty">No perfect scores yet.</div>';
    return;
  }
  el.innerHTML = data.map((row, index) => `
    <div class="fd-leader-row">
      <div class="fd-leader-rank">${index + 1}</div>
      <div class="fd-leader-main">
        <div class="fd-leader-name">${esc(row.student_name || 'Student')}</div>
        <div class="fd-leader-meta">${esc(row.display_name || row.section_name || 'Class')}</div>
      </div>
      <div class="fd-leader-score">${Number(row.perfect_count || 0)}<span class="fd-leader-sub">${fdPct(Number(row.perfect_rate || 0))}</span></div>
    </div>
  `).join('');
}

function renderRiskLeaderboard(rows) {
  const el = document.getElementById('fdRiskLeaderboard');
  if (!el) return;
  const data = Array.isArray(rows) ? rows : [];
  if (!data.length) {
    el.innerHTML = '<div class="fd-list-empty">No at-risk students right now.</div>';
    return;
  }
  el.innerHTML = data.map(row => `
    <div class="fd-leader-row">
      <div class="fd-leader-rank">${esc(row.initials || 'ST')}</div>
      <div class="fd-leader-main">
        <div class="fd-leader-name">${esc(row.student_name || 'Student')}</div>
        <div class="fd-leader-meta">${esc(row.display_name || row.section_name || 'Class')} • Needs support</div>
      </div>
      <div class="fd-leader-score risk">${fdPct(Number(row.grade || 0))}<span class="fd-leader-sub">avg</span></div>
    </div>
  `).join('');
}

function renderDashboardList(containerId, itemsHtml, emptyText) {
  const el = document.getElementById(containerId);
  if (!el) return;
  el.innerHTML = itemsHtml || `<div class="fd-list-empty">${esc(emptyText)}</div>`;
}

function renderStudentSectionsChart(rows) {
  const canvas = document.getElementById('fdStudentSectionsChart');
  const emptyEl = document.getElementById('fdStudentSectionsEmpty');
  if (!canvas || !emptyEl) return;

  if (dashboardStudentsChart) {
    dashboardStudentsChart.destroy();
    dashboardStudentsChart = null;
  }

  const sections = Array.isArray(rows)
    ? rows.filter(row => Number(row.total_enrolled || 0) > 0).slice(0, 5)
    : [];

  if (!sections.length) {
    canvas.style.display = 'none';
    emptyEl.style.display = '';
    return;
  }

  canvas.style.display = '';
  emptyEl.style.display = 'none';

  const labels = sections.map(row => row.display_name || row.section_name || 'Section');
  const values = sections.map(row => Number(row.total_enrolled || 0));
  const maxValue = Math.max(...values, 1);

  dashboardStudentsChart = new Chart(canvas.getContext('2d'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Students',
        data: values,
        borderWidth: 0,
        borderRadius: 6,
        barThickness: 12,
        backgroundColor: fdCssVar('--primary'),
        hoverBackgroundColor: fdCssVar('--accent')
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.parsed.x || 0} students`
          }
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          suggestedMax: maxValue + 1,
          ticks: {
            precision: 0,
            color: fdCssVar('--text-muted'),
            font: { size: 10, family: 'DM Sans' }
          },
          grid: { color: fdCssVar('--border') },
          border: { color: fdCssVar('--border') }
        },
        y: {
          ticks: {
            color: fdCssVar('--text'),
            font: { size: 10, family: 'DM Sans', weight: '700' }
          },
          grid: { color: 'transparent' },
          border: { color: 'transparent' }
        }
      }
    }
  });
}

function renderCompletionChart(rows) {
  const canvas = document.getElementById('fdCompletionChart');
  const emptyEl = document.getElementById('fdCompletionEmpty');
  if (!canvas || !emptyEl) return;

  if (dashboardCompletionChart) {
    dashboardCompletionChart.destroy();
    dashboardCompletionChart = null;
  }

  const sections = Array.isArray(rows)
    ? rows.filter(row => (Number(row.scored || 0) + Number(row.missing || 0)) > 0).slice(0, 5)
    : [];

  if (!sections.length) {
    canvas.style.display = 'none';
    emptyEl.style.display = '';
    return;
  }

  canvas.style.display = '';
  emptyEl.style.display = 'none';

  const labels = sections.map(row => row.display_name || row.section_name || 'Section');
  const scoredCounts = sections.map(row => Number(row.scored || 0));
  const missingCounts = sections.map(row => Number(row.missing || 0));
  const scoredPct = sections.map((row, index) => {
    const total = scoredCounts[index] + missingCounts[index];
    return total > 0 ? Math.round((scoredCounts[index] / total) * 1000) / 10 : 0;
  });
  const missingPct = scoredPct.map(value => Math.max(0, Math.round((100 - value) * 10) / 10));

  dashboardCompletionChart = new Chart(canvas.getContext('2d'), {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Scored',
          data: scoredPct,
          rawCounts: scoredCounts,
          backgroundColor: fdCssVar('--primary'),
          borderWidth: 0,
          borderRadius: 5,
          barThickness: 12
        },
        {
          label: 'Missing',
          data: missingPct,
          rawCounts: missingCounts,
          backgroundColor: 'rgba(217,48,37,.28)',
          borderWidth: 0,
          borderRadius: 5,
          barThickness: 12
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 10, color: fdCssVar('--text-muted'), font: { size: 10, family: 'DM Sans' } }
        },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.dataset.label}: ${ctx.dataset.rawCounts?.[ctx.dataIndex] || 0} (${fdPct(ctx.parsed.x || 0)})`
          }
        }
      },
      scales: {
        x: {
          stacked: true,
          min: 0,
          max: 100,
          ticks: { color: fdCssVar('--text-muted'), font: { size: 10, family: 'DM Sans' }, callback: value => `${value}%` },
          grid: { color: fdCssVar('--border') },
          border: { color: fdCssVar('--border') }
        },
        y: {
          stacked: true,
          ticks: { color: fdCssVar('--text'), font: { size: 10, family: 'DM Sans', weight: '700' } },
          grid: { color: 'transparent' },
          border: { color: 'transparent' }
        }
      }
    }
  });
}

function renderGradeDistributionChart(distribution) {
  const canvas = document.getElementById('fdGradeDistributionChart');
  const emptyEl = document.getElementById('fdGradeDistributionEmpty');
  if (!canvas || !emptyEl) return;

  if (dashboardGradeDistributionChart) {
    dashboardGradeDistributionChart.destroy();
    dashboardGradeDistributionChart = null;
  }

  const labels = ['90-100', '80-89', '70-79', '60-69', '<60'];
  const values = labels.map(label => Number(distribution?.[label] || 0));
  if (!values.some(value => value > 0)) {
    canvas.style.display = 'none';
    emptyEl.style.display = '';
    return;
  }

  canvas.style.display = '';
  emptyEl.style.display = 'none';

  dashboardGradeDistributionChart = new Chart(canvas.getContext('2d'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Students',
        data: values,
        backgroundColor: [
          fdCssVar('--primary'),
          fdCssVar('--accent'),
          '#f59e0b',
          '#f97316',
          fdCssVar('--danger')
        ],
        borderWidth: 0,
        borderRadius: 5,
        barThickness: 18
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.parsed.y || 0} students`
          }
        }
      },
      scales: {
        x: {
          ticks: { color: fdCssVar('--text'), font: { size: 10, family: 'DM Sans', weight: '700' } },
          grid: { color: 'transparent' },
          border: { color: 'transparent' }
        },
        y: {
          beginAtZero: true,
          ticks: { precision: 0, color: fdCssVar('--text-muted'), font: { size: 10, family: 'DM Sans' } },
          grid: { color: fdCssVar('--border') },
          border: { color: fdCssVar('--border') }
        }
      }
    }
  });
}

function renderStatusBreakdownChart(status) {
  const canvas = document.getElementById('fdStatusBreakdownChart');
  const emptyEl = document.getElementById('fdStatusBreakdownEmpty');
  if (!canvas || !emptyEl) return;

  if (dashboardStatusBreakdownChart) {
    dashboardStatusBreakdownChart.destroy();
    dashboardStatusBreakdownChart = null;
  }

  const labels = ['Passing', 'At-risk', 'Ungraded'];
  const values = [
    Number(status?.passing || 0),
    Number(status?.at_risk || 0),
    Number(status?.ungraded || 0)
  ];
  if (!values.some(value => value > 0)) {
    canvas.style.display = 'none';
    emptyEl.style.display = '';
    return;
  }

  canvas.style.display = '';
  emptyEl.style.display = 'none';

  dashboardStatusBreakdownChart = new Chart(canvas.getContext('2d'), {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: [fdCssVar('--primary'), fdCssVar('--danger'), fdCssVar('--fd-badge-muted')],
        borderColor: fdCssVar('--surface'),
        borderWidth: 2,
        hoverOffset: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      cutout: '68%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 10, color: fdCssVar('--text-muted'), font: { size: 10, family: 'DM Sans' } }
        },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.label}: ${ctx.parsed || 0} students`
          }
        }
      }
    }
  });
}

function renderTrendChart(trend) {
  const canvas = document.getElementById('fdTrendChart');
  const emptyEl = document.getElementById('fdTrendEmpty');
  if (!canvas || !emptyEl) return;

  if (dashboardTrendChart) {
    dashboardTrendChart.destroy();
    dashboardTrendChart = null;
  }

  const labels = Array.isArray(trend?.labels) ? trend.labels : [];
  const datasets = Array.isArray(trend?.datasets) ? trend.datasets : [];
  if (!labels.length || !datasets.length) {
    canvas.style.display = 'none';
    emptyEl.style.display = '';
    return;
  }

  canvas.style.display = '';
  emptyEl.style.display = 'none';

  const palette = ['--fd-line-1','--fd-line-2','--fd-line-3','--fd-line-4','--fd-line-5','--fd-line-6'];
  dashboardTrendChart = new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels,
      datasets: datasets.map((set, index) => ({
        label: set.label,
        data: Array.isArray(set.data) ? set.data : [],
        borderColor: fdCssVar(palette[index % palette.length]),
        backgroundColor: 'transparent',
        borderWidth: 1.7,
        borderDash: Array.isArray(set.dash_pattern) ? set.dash_pattern : [],
        tension: 0.28,
        spanGaps: true,
        pointRadius: 2,
        pointHoverRadius: 3,
      }))
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
          align: 'start',
          labels: {
            boxWidth: 10,
            boxHeight: 2,
            color: fdCssVar('--text-muted'),
            font: { size: 10, family: 'DM Sans' },
            usePointStyle: false,
          }
        },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.dataset.label}: ${fdPct(ctx.parsed.y || 0)}`
          }
        }
      },
      scales: {
        x: {
          ticks: { color: fdCssVar('--text-muted'), font: { size: 10, family: 'DM Sans' }, maxRotation: 0, autoSkip: true },
          grid: { color: 'transparent' },
          border: { color: fdCssVar('--border') }
        },
        y: {
          min: 40,
          max: 100,
          ticks: {
            stepSize: 20,
            color: fdCssVar('--text-muted'),
            font: { size: 10, family: 'DM Sans' },
            callback: value => `${value}%`
          },
          grid: { color: fdCssVar('--border') },
          border: { color: fdCssVar('--border') }
        }
      }
    }
  });
}

function renderDashboard() {
  if (!facultyData) return;

  const summary = dashboardAnalytics?.summary || {};
  const firstName = facultyData.first_name || 'Faculty';
  const semesterLabel = summary.semester_label || 'No active semester';
  const activeSections = Number(summary.section_count ?? allClasses.filter(c => +c.is_active === 1).length);
  const totalStudents = Number(summary.total_students ?? allClasses.reduce((n, c) => n + (+c.student_count || 0), 0));
  const highPerformers = Number(summary.high_performers ?? 0);
  const atRisk = Number(summary.at_risk_students ?? 0);

  const welcomeTitle = document.getElementById('welcomeTitle');
  const welcomeMeta = document.getElementById('fdWelcomeMeta');
  if (welcomeTitle) welcomeTitle.textContent = `Welcome back, ${firstName}!`;
  if (welcomeMeta) welcomeMeta.textContent = `${semesterLabel} • ${activeSections} active section${activeSections === 1 ? '' : 's'} • ${totalStudents} students`;

  const statClasses = document.getElementById('statClasses');
  const statStudents = document.getElementById('statStudents');
  const statHigh = document.getElementById('statHighPerformers');
  const statRisk = document.getElementById('statAtRisk');
  if (statClasses) statClasses.textContent = activeSections;
  if (statStudents) statStudents.textContent = totalStudents;
  if (statHigh) statHigh.textContent = highPerformers;
  if (statRisk) statRisk.textContent = atRisk;

  renderFacultyPointsChart(dashboardAnalytics?.performance_points || []);
  renderClassPerformanceBars(dashboardAnalytics?.class_performance || []);
  renderPerfectLeaderboard(dashboardAnalytics?.perfect_scorers || []);
  renderRiskLeaderboard(dashboardAnalytics?.at_risk_students || []);
}

function renderClassesPage() {
  const adminGrid  = document.getElementById('adminClassGrid');
  const myGrid     = document.getElementById('myClassGrid');
  const adminTable = document.getElementById('adminClassTable');
  const myTable    = document.getElementById('myClassTable');
  const adminCls   = sortClasses(allClasses.filter(c => (c.source||'').toLowerCase() !== 'faculty' && +c.is_active === 1));
  const myCls      = allClasses.filter(c => (c.source||'').toLowerCase() === 'faculty');
  const myAll      = [...sortClasses(myCls.filter(c => +c.is_active===1)), ...sortClasses(myCls.filter(c => +c.is_active!==1))];
  if (clsView === 'table') {
    myGrid.style.display = adminGrid.style.display = 'none';
    myTable.style.display = adminTable.style.display = '';
    myTable.innerHTML    = buildTable(myAll, true, 'my');
    adminTable.innerHTML = buildTable(adminCls, false, 'admin');
  } else {
    myTable.style.display = adminTable.style.display = 'none';
    myGrid.style.display = adminGrid.style.display = '';
    myGrid.innerHTML    = myAll.map(c => buildCard(c,true)).join('') + buildAddCard();
    adminGrid.innerHTML = adminCls.length ? adminCls.map(c => buildCard(c,false)).join('') : buildEmpty('No admin-assigned classes yet.');
    loadMeetForVisibleCards();
  }
}

/* â•â• NAVIGATION â•â• */
function initNav() {
  const validPages = new Set(['dashboard','classes','archive','profile','settings']);

  function activatePage(page) {
    const target = validPages.has(page) ? page : 'dashboard';
    const btn = document.querySelector(`.nav-item[data-page="${target}"]`);
    const pane = document.querySelector(`.page[data-page="${target}"]`);
    if (!btn || !pane) return;

    document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    pane.classList.add('active');

    localStorage.setItem('faculty_active_page', target);

    if (target === 'archive') loadArchive();
    if (target === 'classes') loadMeetForVisibleCards();
  }

  document.querySelectorAll('.nav-item[data-page]').forEach(btn => {
    btn.addEventListener('click', () => {
      activatePage(btn.dataset.page);
      if (window.innerWidth <= 768) closeMobileSidebar();
    });
  });

  const fromStorage = localStorage.getItem('faculty_active_page') || '';
  activatePage(fromStorage);

  document.getElementById('btnNewClass').addEventListener('click', openCreate);
  /* â”€â”€ FACULTY NOTIFICATIONS â”€â”€ */
(function(){
    const btn   = document.getElementById('notifBtn');
    const panel = document.getElementById('notifPanel');
    if (!btn || !panel) return;

    btn.addEventListener('click', e => {
        e.stopPropagation();
        panel.classList.toggle('show');
        if (panel.classList.contains('show')) loadFacultyNotifs();
    });
    document.addEventListener('click', e => {
        if (!panel.contains(e.target) && !btn.contains(e.target))
            panel.classList.remove('show');
    });
})();

async function loadFacultyNotifs() {
    const list = document.getElementById('notifList');
    if (!list) return;
    try {
        const res  = await fetch('API/facultyUI/get_faculty_notifications.php');
        const data = await res.json();
        const items = data.notifications || [];
        const unread = items.filter(n => !n.is_read).length;
        const dot = document.getElementById('notifDot');
        if (dot) dot.style.display = unread ? 'block' : 'none';
        if (!items.length) {
            list.innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.82rem;">No notifications.</div>';
            return;
        }
        list.innerHTML = items.map(n => `
            <div class="notif-item${n.is_read?'':' unread'}" style="cursor:pointer;"
                 onclick="goToClassPeople('${esc(n.class_id)}')">
                <div class="notif-dot2${n.is_read?' read':''}"></div>
                <div>
                    <div class="notif-text" style="font-weight:600;">${esc(n.title)}</div>
                    <div class="notif-text">${esc(n.message)}</div>
                    <div class="notif-ts">${relTime(n.created_at)}</div>
                </div>
            </div>`).join('');
    } catch { }
}

function goToClassPeople(classId) {
    if (classId) window.location.href = `classRoom.php?class_id=${encodeURIComponent(classId)}&tab=people`;
}

function relTime(ts) {
    if (!ts) return '';
    const d = Math.floor((Date.now() - new Date(ts)) / 1000);
    if (d < 60)    return 'Just now';
    if (d < 3600)  return Math.floor(d/60) + 'm ago';
    if (d < 86400) return Math.floor(d/3600) + 'h ago';
    return Math.floor(d/86400) + 'd ago';
}
}

/* â•â• SIDEBAR â•â• */
function initSidebar() {
  const sidebar = document.getElementById('sidebar');
  const mainEl  = document.getElementById('main');
  const overlay = document.getElementById('overlay');
  document.getElementById('menuToggle').addEventListener('click', () => {
    if (window.innerWidth <= 768) { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); }
    else { sidebarCollapsed = !sidebarCollapsed; sidebar.classList.toggle('collapsed', sidebarCollapsed); mainEl.classList.toggle('collapsed', sidebarCollapsed); }
  });
  overlay.addEventListener('click', closeMobileSidebar);
  window.addEventListener('resize', () => { if (window.innerWidth > 768) closeMobileSidebar(); });
}
function closeMobileSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('show');
}

/* â•â• DARK MODE â•â• */
function initDarkMode() {
  const body = document.body;
  const btns = [document.getElementById('darkToggle'), document.getElementById('settingsDark')].filter(Boolean);
  if (localStorage.getItem('tl_dark') === '1') { body.classList.add('dark'); btns.forEach(b => b.querySelector('i').className = 'fas fa-sun'); }
  btns.forEach(btn => {
    btn.addEventListener('click', () => {
      const dark = body.classList.toggle('dark');
      localStorage.setItem('tl_dark', dark ? '1' : '0');
      btns.forEach(b => b.querySelector('i').className = dark ? 'fas fa-sun' : 'fas fa-moon');
      renderDashboard();
    });
  });
}

/* â•â• MODAL â•â• */
let currentModalStep = 1;
const CLASS_DRAFT_KEY = 'faculty_class_draft_v1';

function saveClassDraft() {
  const modalOpen = document.getElementById('classModal')?.classList.contains('show');
  if (!modalOpen) return;
  const sectionEl = document.getElementById('fSection');
  const draft = {
    ts: Date.now(),
    step: currentModalStep,
    courseId: document.getElementById('fCourse')?.value || '',
    courseDisplay: document.getElementById('fCourseDisplay')?.value || '',
    yearLevel: document.getElementById('fYearLevel')?.value || '',
    section: sectionEl ? (sectionEl.value || '') : '',
    subjectId: document.getElementById('fSubject')?.value || '',
    className: document.getElementById('fClassName')?.value || '',
    startTime: document.getElementById('fStartTime')?.value || '',
    endTime: document.getElementById('fEndTime')?.value || '',
    breakStart: document.getElementById('fBreakStart')?.value || '',
    breakEnd: document.getElementById('fBreakEnd')?.value || '',
    days: [...document.querySelectorAll('[id^="day_"]:checked')].map(el => el.value),
    userEditedName
  };
  localStorage.setItem(CLASS_DRAFT_KEY, JSON.stringify(draft));
}

function clearClassDraft() {
  localStorage.removeItem(CLASS_DRAFT_KEY);
}

async function restoreClassDraft(draft) {
  if (!draft) return;
  await openCreate();

  const hiddenCourse = document.getElementById('fCourse');
  const displayCourse = document.getElementById('fCourseDisplay');
  if (hiddenCourse && draft.courseId) {
    hiddenCourse.value = String(draft.courseId);
    if (displayCourse && draft.courseDisplay) displayCourse.value = draft.courseDisplay;
    onCourseChange();
  }

  if (draft.courseId && draft.yearLevel) {
    await restoreSectionField(draft.courseId, draft.yearLevel, draft.section || '');
  }

  filterSubjects();
  if (draft.subjectId) {
    const subjSel = document.getElementById('fSubject');
    // Rebuild once more after year/section restoration to ensure subject options are populated.
    filterSubjects();
    await new Promise(r => setTimeout(r, 60));
    if (subjSel) {
      subjSel.value = String(draft.subjectId);
      // If the option isn't currently present (rare async race), inject it from dropdown cache.
      if (subjSel.value !== String(draft.subjectId)) {
        const subj = (dropdowns.subjects || []).find(s => String(s.id) === String(draft.subjectId));
        if (subj) {
          const opt = document.createElement('option');
          opt.value = String(subj.id);
          opt.textContent = `${subj.subject_code} - ${subj.subject_name}`;
          subjSel.appendChild(opt);
          subjSel.value = String(subj.id);
        }
      }
    }
  }
  if (draft.className) document.getElementById('fClassName').value = draft.className;
  document.getElementById('fStartTime').value = draft.startTime || '';
  document.getElementById('fEndTime').value = draft.endTime || '';
  syncBreakTimeBounds();
  document.getElementById('fBreakStart').value = draft.breakStart || '';
  document.getElementById('fBreakEnd').value = draft.breakEnd || '';
  syncBreakTimeBounds();
  sync12hPickersFromHidden();

  document.querySelectorAll('[id^="day_"]').forEach(el => { el.checked = (draft.days || []).includes(el.value); });
  userEditedName = !!draft.userEditedName;
  setModalStep(Number(draft.step) || 1);
  updateClassProgress();
}

async function maybeResumeClassDraft() {
  const raw = localStorage.getItem(CLASS_DRAFT_KEY);
  if (!raw) return;
  let draft = null;
  try { draft = JSON.parse(raw); } catch { clearClassDraft(); return; }
  if (!draft || !draft.ts) { clearClassDraft(); return; }

  const r = await Swal.fire({
    title: 'Resume unsaved class draft?',
    text: 'You have unsaved class inputs from your last session.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Resume',
    cancelButtonText: 'Discard'
  });
  if (r.isConfirmed) await restoreClassDraft(draft);
  else clearClassDraft();
}

function initModal() {
  const modal = document.getElementById('classModal');
  const form  = document.getElementById('classForm');
  [document.getElementById('modalClose'), document.getElementById('modalCancel')]
    .forEach(b => b.addEventListener('click', closeModal));
  modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
  const courseDisplay = document.getElementById('fCourseDisplay');
  if (courseDisplay) {
    const menu = document.getElementById('fCourseMenu');
    if (menu && menu.parentElement !== document.body) document.body.appendChild(menu);
    courseDisplay.addEventListener('input', () => renderCourseMenu(courseDisplay.value));
    courseDisplay.addEventListener('focus', () => renderCourseMenu(courseDisplay.value));
    courseDisplay.addEventListener('keydown', handleCourseKeydown);
    courseDisplay.addEventListener('blur', () => setTimeout(() => closeCourseMenu(true), 120));
  }
  document.getElementById('fSubject').addEventListener('change', autoFillClassName);
  document.getElementById('fYearLevel').addEventListener('change', onYearChange);
  document.getElementById('fSection').addEventListener('input', autoFillClassName);
  document.getElementById('fSection').addEventListener('change', autoFillClassName);
  document.getElementById('fStartTime').addEventListener('change', syncBreakTimeBounds);
  document.getElementById('fEndTime').addEventListener('change', syncBreakTimeBounds);
  init12hPicker('tpStart', 'fStartTime');
  init12hPicker('tpEnd', 'fEndTime');
  document.getElementById('btnAutoName').addEventListener('click', forceAutoName);
  document.getElementById('modalPrev').addEventListener('click', prevModalStep);
  document.getElementById('modalNext').addEventListener('click', nextModalStep);
  form.addEventListener('submit', saveClass);
  form.addEventListener('input', updateClassProgress);
  form.addEventListener('change', updateClassProgress);
  form.addEventListener('input', saveClassDraft);
  form.addEventListener('change', saveClassDraft);
  syncBreakTimeBounds();
  setModalStep(1);
}

function setModalStep(step) {
  currentModalStep = Math.max(1, Math.min(4, step));
  document.querySelectorAll('.wizard-step').forEach(el => {
    const n = Number(el.getAttribute('data-step'));
    el.classList.toggle('active', n === currentModalStep);
  });

  const prev = document.getElementById('modalPrev');
  const next = document.getElementById('modalNext');
  const save = document.getElementById('modalSubmit');
  if (prev) prev.style.display = currentModalStep > 1 ? 'inline-flex' : 'none';
  if (next) next.style.display = currentModalStep < 4 ? 'inline-flex' : 'none';
  if (save) save.style.display = currentModalStep === 4 ? 'inline-flex' : 'none';

  if (currentModalStep === 4) fillReview();
  updateClassProgress();
  saveClassDraft();
}

function nextModalStep() {
  if (!validateCurrentStep()) return;
  setModalStep(currentModalStep + 1);
}

function prevModalStep() {
  setModalStep(currentModalStep - 1);
}

function setDaysWeekdays() {
  const weekdays = new Set(['Mon','Tue','Wed','Thu','Fri']);
  document.querySelectorAll('[id^="day_"]').forEach(el => { el.checked = weekdays.has(el.value); });
  updateClassProgress();
}

function setDaysAll() {
  document.querySelectorAll('[id^="day_"]').forEach(el => { el.checked = true; });
  updateClassProgress();
}

function clearDays() {
  document.querySelectorAll('[id^="day_"]').forEach(el => { el.checked = false; });
  updateClassProgress();
}

function to24h(h12, mm, ap) {
  let h = Number(h12) % 12;
  if (String(ap).toUpperCase() === 'PM') h += 12;
  return `${String(h).padStart(2,'0')}:${String(mm).padStart(2,'0')}`;
}

function to12hParts(t24) {
  if (!t24 || !String(t24).includes(':')) return { hh: '08', mm: '00', ap: 'AM' };
  const [hRaw, mRaw] = String(t24).split(':');
  let h = Number(hRaw);
  const mm = String(Number(mRaw || 0)).padStart(2,'0');
  const ap = h >= 12 ? 'PM' : 'AM';
  h = h % 12; if (h === 0) h = 12;
  return { hh: String(h).padStart(2,'0'), mm, ap };
}

function init12hPicker(prefix, hiddenId) {
  const hSel = document.getElementById(`${prefix}Hour`);
  const mSel = document.getElementById(`${prefix}Min`);
  const aSel = document.getElementById(`${prefix}Ap`);
  const hidden = document.getElementById(hiddenId);
  if (!hSel || !mSel || !aSel || !hidden) return;

  if (!hSel.options.length) {
    for (let h = 1; h <= 12; h++) {
      const v = String(h).padStart(2,'0');
      const o = document.createElement('option'); o.value = v; o.textContent = v;
      hSel.appendChild(o);
    }
  }
  if (!mSel.options.length) {
    for (let m = 0; m < 60; m += 5) {
      const v = String(m).padStart(2,'0');
      const o = document.createElement('option'); o.value = v; o.textContent = v;
      mSel.appendChild(o);
    }
  }

  const pushToHidden = () => {
    hidden.value = to24h(hSel.value, mSel.value, aSel.value);
    syncBreakTimeBounds();
    updateClassProgress();
    saveClassDraft();
  };

  const setFromHidden = () => {
    const p = to12hParts(hidden.value);
    hSel.value = p.hh; mSel.value = p.mm; aSel.value = p.ap;
  };

  hSel.onchange = pushToHidden;
  mSel.onchange = pushToHidden;
  aSel.onchange = pushToHidden;

  setFromHidden();
}

function sync12hPickersFromHidden() {
  const startHidden = document.getElementById('fStartTime');
  const endHidden = document.getElementById('fEndTime');
  const s = to12hParts(startHidden?.value || '');
  const e = to12hParts(endHidden?.value || '');
  const sh = document.getElementById('tpStartHour'); const sm = document.getElementById('tpStartMin'); const sa = document.getElementById('tpStartAp');
  const eh = document.getElementById('tpEndHour'); const em = document.getElementById('tpEndMin'); const ea = document.getElementById('tpEndAp');
  if (sh && sm && sa) { sh.value = s.hh; sm.value = s.mm; sa.value = s.ap; }
  if (eh && em && ea) { eh.value = e.hh; em.value = e.mm; ea.value = e.ap; }
}

function syncBreakTimeBounds() {
  const start = document.getElementById('fStartTime')?.value || '';
  const end   = document.getElementById('fEndTime')?.value || '';
  const bStart = document.getElementById('fBreakStart');
  const bEnd   = document.getElementById('fBreakEnd');
  if (!bStart || !bEnd) return;

  const prevStart = bStart.value || '';
  const prevEnd = bEnd.value || '';

  bStart.innerHTML = '<option value="">No break start</option>';
  bEnd.innerHTML = '<option value="">No break end</option>';

  if (!(start && end && start < end)) return;

  // Build 5-minute slots within class schedule (inclusive endpoints).
  const toMin = t => {
    const [h, m] = String(t).split(':').map(Number);
    return (h * 60) + m;
  };
  const toTime = mins => {
    const h = String(Math.floor(mins / 60)).padStart(2,'0');
    const m = String(mins % 60).padStart(2,'0');
    return `${h}:${m}`;
  };

  const sMin = toMin(start);
  const eMin = toMin(end);
  for (let m = sMin; m <= eMin; m += 5) {
    const val = toTime(m);
    const lbl = fmt12(val);
    const o1 = document.createElement('option');
    o1.value = val; o1.textContent = lbl;
    const o2 = document.createElement('option');
    o2.value = val; o2.textContent = lbl;
    bStart.appendChild(o1);
    bEnd.appendChild(o2);
  }

  if (prevStart && toMin(prevStart) >= sMin && toMin(prevStart) <= eMin) bStart.value = prevStart;
  if (prevEnd && toMin(prevEnd) >= sMin && toMin(prevEnd) <= eMin) bEnd.value = prevEnd;
}

function validateCurrentStep() {
  if (currentModalStep === 1) {
    if (!document.getElementById('fCourse')?.value) { toast('Please select a program first.', 'warning'); return false; }
  }
  if (currentModalStep === 2) {
    const sectionEl = document.getElementById('fSection');
    if (!document.getElementById('fYearLevel')?.value || !sectionEl || !String(sectionEl.value || '').trim() || !document.getElementById('fSubject')?.value) {
      toast('Please complete Year, Section, and Course.', 'warning');
      return false;
    }
    if (!String(document.getElementById('fClassName')?.value || '').trim()) {
      forceAutoName();
    }
  }
  if (currentModalStep === 3) {
    const st = document.getElementById('fStartTime').value;
    const et = document.getElementById('fEndTime').value;
    const bst = document.getElementById('fBreakStart').value;
    const bet = document.getElementById('fBreakEnd').value;
    const hasDays = !!document.querySelector('[id^="day_"]:checked');
    if (!(st < et)) {
      toast('End time must be later than start time.', 'warning');
      return false;
    }
    if ((bst && !bet) || (!bst && bet)) {
      toast('Please set both break start and break end.', 'warning');
      return false;
    }
    if (bst && bet) {
      if (!(bst < bet)) {
        toast('Break end must be later than break start.', 'warning');
        return false;
      }
      if (bst < st || bet > et) {
        toast('Break time must be within the class schedule.', 'warning');
        return false;
      }
    }
    if (!(st && et && hasDays)) {
      toast('Please set start/end time and at least one class day.', 'warning');
      return false;
    }
  }
  return true;
}

function fillReview() {
  const subjSel = document.getElementById('fSubject');
  const courseIdEl = document.getElementById('fCourse');
  const courseDisplayEl = document.getElementById('fCourseDisplay');
  const sectionEl = document.getElementById('fSection');
  const days = [...document.querySelectorAll('[id^="day_"]:checked')].map(el => el.value).join(', ') || 'Not set';
  const selectedCourse = (dropdowns.courses || []).find(c => String(c.id) === String(courseIdEl?.value || ''));
  const courseText = (courseDisplayEl?.value || '').trim() || (selectedCourse ? courseLabel(selectedCourse) : 'Not set');
  const subjectText = subjSel && subjSel.selectedIndex > 0 ? subjSel.options[subjSel.selectedIndex].textContent : 'Not set';
  const schedule = (document.getElementById('fStartTime').value && document.getElementById('fEndTime').value)
    ? `${fmt12(document.getElementById('fStartTime').value)} - ${fmt12(document.getElementById('fEndTime').value)}`
    : 'Not set';
  const breakTime = (document.getElementById('fBreakStart').value && document.getElementById('fBreakEnd').value)
    ? `${fmt12(document.getElementById('fBreakStart').value)} - ${fmt12(document.getElementById('fBreakEnd').value)}`
    : 'None';

  const review = document.getElementById('classReviewGrid');
  if (!review) return;
  review.innerHTML = `
    <div class="review-item"><div class="review-label">Program</div><div class="review-value">${esc(courseText)}</div></div>
    <div class="review-item"><div class="review-label">Year Level</div><div class="review-value">${esc(document.getElementById('fYearLevel').value || 'Not set')}</div></div>
    <div class="review-item"><div class="review-label">Section</div><div class="review-value">${esc(sectionEl ? (sectionEl.value || 'Not set') : 'Not set')}</div></div>
    <div class="review-item"><div class="review-label">Course</div><div class="review-value">${esc(subjectText)}</div></div>
    <div class="review-item full"><div class="review-label">Class Name</div><div class="review-value">${esc(document.getElementById('fClassName').value || 'Not set')}</div></div>
    <div class="review-item"><div class="review-label">Semester</div><div class="review-value">${esc(document.getElementById('fSemesterText').textContent || 'Not set')}</div></div>
    <div class="review-item"><div class="review-label">Schedule</div><div class="review-value">${esc(schedule)}</div></div>
    <div class="review-item"><div class="review-label">Break</div><div class="review-value">${esc(breakTime)}</div></div>
    <div class="review-item full"><div class="review-label">Class Days</div><div class="review-value">${esc(days)}</div></div>
  `;
}

function updateClassProgress() {
  const sectionEl = document.getElementById('fSection');
  const step1 = !!document.getElementById('fCourse')?.value;
  const step2 = !!document.getElementById('fYearLevel')?.value && !!(sectionEl && String(sectionEl.value || '').trim()) && !!document.getElementById('fSubject')?.value && !!String(document.getElementById('fClassName')?.value || '').trim();
  const step3 = !!(document.getElementById('fStartTime')?.value && document.getElementById('fEndTime')?.value && document.querySelector('[id^="day_"]:checked'));
  const step4 = step1 && step2 && step3;
  const doneStates = [step1, step2, step3, step4];

  const fill = document.getElementById('classProgressFill');
  const fillPct = ((Math.max(1, currentModalStep) - 1) / 3) * 100;
  if (fill) fill.style.width = `${fillPct}%`;

  const steps = [...document.querySelectorAll('.stepper-step')];
  steps.forEach((el, idx) => {
    const n = idx + 1;
    el.classList.toggle('done', doneStates[idx]);
    el.classList.toggle('active', n === currentModalStep);
  });
}

function buildAutoName() {
  const sectionEl = document.getElementById('fSection');
  const section   = sectionEl ? sectionEl.value.trim() : '';
  const subjSel   = document.getElementById('fSubject');
  const subjText  = subjSel && subjSel.selectedIndex > 0
    ? subjSel.options[subjSel.selectedIndex].textContent.split('-').pop().trim()
    : '';
  const parts = [];
  if (section) parts.push(section);
  if (subjText) parts.push(subjText);
  return parts.join(' ');
}
function autoFillClassName() { if (userEditedName) return; const n = buildAutoName(); if (n) document.getElementById('fClassName').value = n; updateClassProgress(); }
function forceAutoName() { userEditedName = false; document.getElementById('fClassName').value = buildAutoName() || ''; updateClassProgress(); }
function closeModal() { document.getElementById('classModal').classList.remove('show'); setModalStep(1); clearClassDraft(); }

async function openCreate() {
  userEditedName = false;
  document.getElementById('editClassId').value = '';
  document.getElementById('modalHeading').textContent = 'Create New Class';
  document.getElementById('classForm').reset();
  document.getElementById('fClassName').value = '';
  document.getElementById('fCourse').value = '';
  const courseDisplay = document.getElementById('fCourseDisplay');
  if (courseDisplay) courseDisplay.value = '';
  resetYearField();
  resetSectionField();
  document.getElementById('fSubject').disabled = true;
  document.getElementById('fSubject').innerHTML = '<option value="">- Select Year Level first -</option>';
  document.getElementById('fSemester').value           = activeSemesterLabel;
  document.getElementById('fSemesterText').textContent = activeSemesterLabel || 'No active semester set';
  document.getElementById('classModal').classList.add('show');
  setModalStep(1);
  // Always re-fetch dropdowns so newly added subjects appear immediately
  await loadDropdowns();
  syncBreakTimeBounds();
  sync12hPickersFromHidden();
  updateClassProgress();
  saveClassDraft();
}

async function openEdit(id) {
  try {
    const res  = await fetch(`API/facultyUI/get_class_detail.php?id=${encodeURIComponent(id)}`);
    const data = await res.json();
    if (data.status !== 'success') { toast(data.message || 'Could not load class', 'error'); return; }
    const c = data.class;
    document.getElementById('editClassId').value = c.id;
    const storedSem = c.class_semester || activeSemesterLabel || '';
    document.getElementById('fSemester').value           = storedSem;
    document.getElementById('fSemesterText').textContent = storedSem || 'No semester set';
    filterSubjects();
    await new Promise(r => setTimeout(r, 100));
    document.getElementById('fSubject').value = c.subject_id || '';
    syncCourseDisplayFromSelection();
    await restoreSectionField(c.course_id, c.year_level, c.section);
    await new Promise(r => setTimeout(r, 50));
    const storedName = c.subject_name || c.class_code || '';
    document.getElementById('fClassName').value = storedName;
    userEditedName = storedName !== buildAutoName();
    if (c.schedule) { const p = c.schedule.split(' - ').map(s => s.trim()); document.getElementById('fStartTime').value = p[0]||''; document.getElementById('fEndTime').value = p[1]||''; }
    syncBreakTimeBounds();
    sync12hPickersFromHidden();
    if (c.break_time) { const b = c.break_time.split('-').map(s => s.trim()); document.getElementById('fBreakStart').value = b[0]||''; document.getElementById('fBreakEnd').value = b[1]||''; }
    syncBreakTimeBounds();
    document.querySelectorAll('[id^="day_"]').forEach(el => el.checked = false);
    if (c.class_days) c.class_days.split(',').map(d => d.trim()).forEach(d => { const el = document.getElementById(`day_${d}`); if (el) el.checked = true; });
    document.getElementById('modalHeading').textContent = 'Edit Class';
    document.getElementById('classModal').classList.add('show');
  setModalStep(1);
  } catch (e) { toast('Could not load class details', 'error'); }
}

async function saveClass(e) {
  e.preventDefault();
  if (currentModalStep !== 4) { nextModalStep(); return; }
  const btn    = document.getElementById('modalSubmit');
  const nameEl = document.getElementById('fClassName');
  if (!nameEl.value.trim()) { const auto = buildAutoName(); if (auto) nameEl.value = auto; }
  const sectionEl = document.getElementById('fSection');
const section   = sectionEl ? sectionEl.value.trim() : '';
  const yearLevel = document.getElementById('fYearLevel').value.trim();
  const semester  = activeSemesterLabel || document.getElementById('fSemester').value.trim();
  const className = nameEl.value.trim();
  if (!section || !yearLevel) { toast('Year Level and Section are required.', 'error'); return; }
  if (!semester) { toast('No active semester is set.', 'warning'); return; }
  if (!className) { toast('Could not generate a class name.', 'error'); return; }
  btn.disabled = true; btn.innerHTML = '<span class="spin"></span> Saving...';
  const startTime  = document.getElementById('fStartTime').value;
  const endTime    = document.getElementById('fEndTime').value;
  const breakStart = document.getElementById('fBreakStart').value;
  const breakEnd   = document.getElementById('fBreakEnd').value;
  if (startTime && endTime && !(startTime < endTime)) { toast('End time must be later than start time.', 'error'); return; }
  if ((breakStart && !breakEnd) || (!breakStart && breakEnd)) { toast('Please set both break start and break end.', 'error'); return; }
  if (breakStart && breakEnd) {
    if (!(breakStart < breakEnd)) { toast('Break end must be later than break start.', 'error'); return; }
    if (breakStart < startTime || breakEnd > endTime) { toast('Break time must be within the class schedule.', 'error'); return; }
  }
  const days       = [...document.querySelectorAll('[id^="day_"]:checked')].map(el => el.value).join(', ');
  const payload = {
    class_id: document.getElementById('editClassId').value || null,
    class_name: className, course_id: document.getElementById('fCourse').value || null,
    subject_id: document.getElementById('fSubject').value || null, section, year_level: yearLevel,
    class_semester: semester, schedule: (startTime && endTime) ? `${startTime} - ${endTime}` : '',
    break_time: (breakStart && breakEnd) ? `${breakStart} - ${breakEnd}` : '', class_days: days,
  };
  try {
    const res  = await fetch('API/facultyUI/save_faculty_class.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const data = await res.json();
        if (data.status === 'success') {
          // Register/refresh palette for the saved subject so colour is correct
          const subjSel  = document.getElementById('fSubject');
          const subjName = subjSel && subjSel.selectedIndex > 0
            ? subjSel.options[subjSel.selectedIndex].textContent.split('-').pop().trim()
            : document.getElementById('fClassName').value.trim();
          await registerSubjectPalette(subjName);
          clearClassDraft();
          closeModal();
          toast(data.message, 'success');
          await loadClasses();
        }
    else toast(data.message || 'Save failed', 'error');
  } catch { toast('Network error - please try again', 'error'); }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Save Class'; }
}

function toggleStatus(id) {
  const cls = allClasses.find(c => c.id === id);
  const isActive = cls ? +cls.is_active === 1 : true;
  Swal.fire({
    title: `${isActive ? 'Deactivate' : 'Reactivate'} this class?`,
    text: isActive ? 'Students will not be able to access this class until you reactivate it.' : 'This class will be accessible again.',
    icon: isActive ? 'warning' : 'question', showCancelButton: true,
    confirmButtonColor: isActive ? '#f57c00' : '#1a9e78', cancelButtonColor: '#6c757d',
    confirmButtonText: `Yes, ${isActive ? 'deactivate' : 'reactivate'}`, cancelButtonText: 'Cancel',
  }).then(async result => {
    if (!result.isConfirmed) return;
    try {
      const res  = await fetch('API/facultyUI/toggle_class_status.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({class_id: id}) });
      const data = await res.json();
      if (data.status === 'success') { toast(data.message, 'success'); await loadClasses(); }
      else toast(data.message || 'Could not update status', 'error');
    } catch { toast('Network error', 'error'); }
  });
}

function confirmDelete(id) {
  Swal.fire({
    title: 'Remove this class?', text: 'This cannot be undone.',
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#d93025', cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, remove', cancelButtonText: 'Cancel',
  }).then(async result => {
    if (!result.isConfirmed) return;
    try {
      const res  = await fetch('API/facultyUI/delete_faculty_class.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({class_id: id}) });
      const data = await res.json();
      if (data.status === 'success') { toast(data.message, 'success'); await loadClasses(); }
      else toast(data.message || 'Could not remove class', 'error');
    } catch { toast('Network error', 'error'); }
  });
}

/* == DROPDOWNS == */
let activeCourseIndex = -1;

function courseLabel(c) {
  return `${c.course_code} - ${c.course_name}`;
}

function scoreCourseMatch(c, q) {
  const code = String(c.course_code || '').toLowerCase();
  const name = String(c.course_name || '').toLowerCase();
  if (!q) return 0;
  if (code === q) return 100;
  if (code.startsWith(q)) return 90;
  if (code.includes(q)) return 75;
  if (name.startsWith(q)) return 60;
  if (name.includes(q)) return 40;
  return 0;
}

function getFilteredCourses(query) {
  const q = String(query || '').trim().toLowerCase();
  if (!q) {
    return [...dropdowns.courses]
      .sort((a, b) => String(a.course_code || '').localeCompare(String(b.course_code || '')));
  }
  return dropdowns.courses
    .map(c => ({ c, score: scoreCourseMatch(c, q) }))
    .filter(x => x.score > 0)
    .sort((a, b) => b.score - a.score || String(a.c.course_code).localeCompare(String(b.c.course_code)))
    .map(x => x.c)
    .slice(0, 50);
}

function renderCourseMenu(query) {
  const menu = document.getElementById('fCourseMenu');
  const display = document.getElementById('fCourseDisplay');
  if (!menu || !display) return;
  const rect = display.getBoundingClientRect();
  menu.style.left = `${rect.left}px`;
  menu.style.top = `${rect.bottom + 4}px`;
  menu.style.width = `${rect.width}px`;
  menu.style.maxHeight = `${Math.max(180, window.innerHeight - rect.bottom - 16)}px`;

  const list = getFilteredCourses(query);
  if (!list.length) {
    menu.classList.remove('open');
    menu.innerHTML = '';
    activeCourseIndex = -1;
    return;
  }

  activeCourseIndex = 0;
  menu.innerHTML = list.map((c, i) =>
    `<div class="course-option ${i === 0 ? 'active' : ''}" data-id="${c.id}"><span class="code">${esc(c.course_code)}</span>${esc(c.course_name)}</div>`
  ).join('');

  menu.querySelectorAll('.course-option').forEach((el, i) => {
    el.addEventListener('mouseenter', () => setActiveCourseIndex(i));
    el.addEventListener('mousedown', (e) => {
      e.preventDefault();
      pickCourseById(el.getAttribute('data-id'));
    });
  });

  menu.classList.add('open');
}

function setActiveCourseIndex(i) {
  const items = [...document.querySelectorAll('#fCourseMenu .course-option')];
  if (!items.length) return;
  activeCourseIndex = Math.max(0, Math.min(i, items.length - 1));
  items.forEach((el, idx) => el.classList.toggle('active', idx === activeCourseIndex));
  items[activeCourseIndex].scrollIntoView({ block: 'nearest' });
}

function handleCourseKeydown(e) {
  const items = [...document.querySelectorAll('#fCourseMenu .course-option')];
  if (!items.length) return;
  if (e.key === 'ArrowDown') { e.preventDefault(); setActiveCourseIndex(activeCourseIndex + 1); }
  else if (e.key === 'ArrowUp') { e.preventDefault(); setActiveCourseIndex(activeCourseIndex - 1); }
  else if (e.key === 'Enter') {
    e.preventDefault();
    const id = items[Math.max(0, activeCourseIndex)]?.getAttribute('data-id');
    if (id) pickCourseById(id);
  }
}

function pickCourseById(id) {
  const c = dropdowns.courses.find(x => String(x.id) === String(id));
  if (!c) return;
  const hidden = document.getElementById('fCourse');
  const display = document.getElementById('fCourseDisplay');
  const prev = hidden.value;
  hidden.value = String(c.id);
  display.value = courseLabel(c);
  closeCourseMenu(false);
  if (String(prev) !== String(c.id)) onCourseChange();
}

function closeCourseMenu(strictClear) {
  const menu = document.getElementById('fCourseMenu');
  const hidden = document.getElementById('fCourse');
  const display = document.getElementById('fCourseDisplay');
  if (menu) menu.classList.remove('open');
  if (!display || !hidden) return;
  display.value = String(display.value || '').replace(/[\uFFFD\u25C6\u25CA\u25C7]/g, '');

  if (!strictClear) return;

  const q = display.value.trim().toLowerCase();
  const exact = dropdowns.courses.find(c => courseLabel(c).toLowerCase() === q || String(c.course_code || '').toLowerCase() === q);
  if (exact) {
    if (String(hidden.value) !== String(exact.id)) {
      hidden.value = String(exact.id);
      onCourseChange();
    }
    display.value = courseLabel(exact);
    return;
  }

  if (!hidden.value) display.value = '';
}

function syncCourseDisplayFromSelection() {
  const courseId = document.getElementById('fCourse').value;
  const display = document.getElementById('fCourseDisplay');
  if (!display) return;
  const found = dropdowns.courses.find(c => String(c.id) === String(courseId));
  display.value = found ? courseLabel(found) : '';
}

function buildCourseSelect() {
  syncCourseDisplayFromSelection();
}
function filterSubjects() {
  const courseId  = document.getElementById('fCourse').value;
  const yearLevel = document.getElementById('fYearLevel').value;
  const sel       = document.getElementById('fSubject');
  sel.innerHTML   = '<option value="">- Select Course -</option>';
  if (!courseId) { sel.disabled = true; return; }
  if (!yearLevel) {
    sel.disabled = true;
    sel.innerHTML = '<option value="">- Select Year Level first -</option>';
    return;
  }
  const list = dropdowns.subjects.filter(s => {
    const courseMatch = String(s.course_id) === String(courseId);
    const yearMatch   = String(s.year_level || '') === String(yearLevel);
    return courseMatch && yearMatch;
  });
  list.forEach(s => {
    const opt = document.createElement('option');
    opt.value = s.id; opt.textContent = `${s.subject_code} - ${s.subject_name}`;
    sel.appendChild(opt);
  });
  sel.disabled = list.length === 0;
  if (!list.length && yearLevel && courseId) {
    sel.innerHTML = '<option value="">- No courses for this Year Level & Semester -</option>';
  }
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   GOOGLE MEET FUNCTIONS
   meet.google.com/{code}?authuser={faculty_email}
   - authuser= pre-selects the faculty's Google account
   - No OAuth needed - faculty just needs to be signed into
     their registered Google account in the browser
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */

/** Load meet data for every visible active card */
function loadMeetForVisibleCards() {
  const bars = document.querySelectorAll('[id^="meetbar-"]');
  bars.forEach(bar => {
    const classId = bar.id.replace('meetbar-', '');
    if (meetCache[classId]) {
      applyMeetToCard(classId, meetCache[classId]);
    }
    // Always refresh from server so cards auto-update without manual reload.
    fetchMeet(classId);
  });
}

async function fetchMeet(classId) {
  try {
    const res  = await fetch(`API/facultyUI/get_meeting.php?class_id=${encodeURIComponent(classId)}`);
    const data = await res.json();
    if (data.status === 'success') {
      meetCache[classId] = { meeting_code: data.meeting_code, meet_url: data.meet_url };
      applyMeetToCard(classId, meetCache[classId]);
    } else {
      /* No meeting yet - auto-create one silently */
      createMeetSilent(classId);
    }
  } catch (e) {
    createMeetSilent(classId);
  }
}

function startMeetAutoRefresh() {
  if (meetAutoRefreshTimer) clearInterval(meetAutoRefreshTimer);
  meetAutoRefreshTimer = setInterval(() => {
    if (document.hidden) return;
    loadMeetForVisibleCards();
  }, 15000);
}

async function createMeetSilent(classId) {
  try {
    const res  = await fetch('API/facultyUI/create_meeting.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ class_id: classId })
    });
    const data = await res.json();
    if (data.status === 'success') {
      meetCache[classId] = { meeting_code: data.meeting_code, meet_url: data.meet_url };
      applyMeetToCard(classId, meetCache[classId]);
    } else {
      hideMeetLoading(classId);
    }
  } catch (e) {
    hideMeetLoading(classId);
  }
}

function applyMeetToCard(classId, meet) {
  const loadingEl = document.getElementById(`meetloading-${classId}`);
  const readyEl   = document.getElementById(`meetready-${classId}`);
  const linkEl    = document.getElementById(`meetlink-${classId}`);
  const pillEl    = document.getElementById(`meetcodepill-${classId}`);
  if (!loadingEl || !readyEl) return;
  if (meet && meet.meeting_code) {
    if (linkEl)  linkEl.href        = meet.meet_url;
    if (pillEl)  pillEl.textContent = meet.meeting_code;
    loadingEl.style.display = 'none';
    readyEl.style.display   = 'flex';
  } else {
    hideMeetLoading(classId);
  }
}

function hideMeetLoading(classId) {
  const el = document.getElementById(`meetloading-${classId}`);
  if (el) el.innerHTML = '<span style="font-size:.7rem;color:var(--text-muted);">Meet unavailable</span>';
}

async function refreshMeet(classId) {
  const btn = document.getElementById(`meetrefresh-${classId}`);
  if (btn) { btn.classList.add('spinning'); btn.disabled = true; }
  try {
    const res  = await fetch('API/facultyUI/create_meeting.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ class_id: classId })
    });
    const data = await res.json();
    if (data.status === 'success') {
      meetCache[classId] = { meeting_code: data.meeting_code, meet_url: data.meet_url };
      applyMeetToCard(classId, meetCache[classId]);
      toast('New Meet link generated!', 'success');
    } else {
      toast(data.message || 'Could not refresh link', 'error');
    }
  } catch (e) {
    toast('Network error refreshing Meet link', 'error');
  } finally {
    if (btn) { btn.classList.remove('spinning'); btn.disabled = false; }
  }
}

function copyMeetCode(classId) {
  const meet = meetCache[classId];
  if (!meet) return;
  navigator.clipboard.writeText(meet.meeting_code).then(() => {
    toast(`Copied: ${meet.meeting_code}`, 'success');
  }).catch(() => {
    const ta = document.createElement('textarea');
    ta.value = meet.meeting_code;
    document.body.appendChild(ta); ta.select();
    document.execCommand('copy'); ta.remove();
    toast(`Copied: ${meet.meeting_code}`, 'success');
  });
}

function closeAllCardMenus() {
  document.querySelectorAll('.card-kebab-menu').forEach(el => {
    el.classList.remove('open');
    el.style.position = '';
    el.style.top = '';
    el.style.left = '';
    el.style.right = '';
    el.style.zIndex = '';
    el.style.width = '';
    el.style.maxWidth = '';
  });
}

function toggleCardMenu(classId, ev, btnEl) {
  if (ev) ev.stopPropagation();
  const menu = document.getElementById(`cardmenu-${classId}`);
  if (!menu) return;
  const willOpen = !menu.classList.contains('open');
  closeAllCardMenus();
  closeAllTableRowMenus();
  if (willOpen) menu.classList.add('open');
}

function closeAllTableRowMenus() {
  document.querySelectorAll('.tbl-kebab-menu').forEach(el => {
    el.classList.remove('open');
    el.style.position = '';
    el.style.top = '';
    el.style.left = '';
    el.style.right = '';
    el.style.zIndex = '';
    el.style.width = '';
    el.style.maxWidth = '';
  });
}

function toggleTableRowMenu(tableId, classId, ev, btnEl) {
  if (ev) ev.stopPropagation();
  const menu = document.getElementById(`tblmenu-${classId}`);
  if (!menu) return;
  const willOpen = !menu.classList.contains('open');
  closeAllTableRowMenus();
  closeAllCardMenus();
  if (willOpen) menu.classList.add('open');
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   facultyUI.php - JS patches for dynamic Year/Section dropdown

   PATCH 1: Add fetchFacultySections() anywhere in the <script> block.

   PATCH 2: Inside initModal(), find:
     document.getElementById('fYearLevel').addEventListener('input', autoFillClassName);
   REPLACE that line with:
     document.getElementById('fYearLevel').addEventListener('change', function() {
       fetchFacultySections();
       autoFillClassName();
     });

   PATCH 3: Inside openCreate(), find:
     ['cCourse','cSection','cYear','cStart','cEnd','cBreakStart','cBreakEnd'].forEach(...)
   That won't exist in facultyUI - instead find the modal reset block and add:
     resetSectionField();
   right after the modal fields are cleared.

   PATCH 4: Inside openEdit(id), find the line:
     document.getElementById('fSection').value    = c.section        || '';
   REPLACE with:
     await restoreSectionField(c.year_level, c.section);

   PATCH 5: Inside buildAutoName(), find:
     const section = document.getElementById('fSection').value.trim();
   This already works - fSection is now a <select> whose .value gives the selected section string.
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */



/* â”€â”€ PATCH 3 helper: reset section field on modal open â”€â”€ */
function resetSectionField() {
  document.getElementById('fSectionWrap').innerHTML = `
    <label>Section <span class="required">*</span></label>
    <select id="fSection" disabled>
      <option value="">Select year level first</option>
    </select>
    <div id="fSectionHint" style="font-size:.72rem;color:var(--text-muted);margin-top:.18rem;"></div>`;
  updateClassProgress();
}

/*
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
  SUMMARY OF ALL CHANGES TO MAKE IN facultyUI.php JS:

  1. Add the 4 functions above anywhere in <script>.

  2. In initModal(), replace:
       document.getElementById('fYearLevel').addEventListener('input', autoFillClassName);
     with:
       document.getElementById('fYearLevel').addEventListener('change', function() {
         fetchFacultySections();
         autoFillClassName();
       });

  3. In openCreate(), after the line:
       document.getElementById('fClassName').value = '';
     add:
       document.getElementById('fYearLevel').value = '';
       resetSectionField();

  4. In openEdit(id), replace:
       document.getElementById('fSection').value    = c.section        || '';
     with:
       await restoreSectionField(c.year_level, c.section);
     NOTE: The line above document.getElementById('fYearLevel').value = c.year_level || '';
     should be REMOVED since restoreSectionField sets it internally.

  5. In saveClass(), the line:
       const section = document.getElementById('fSection').value.trim();
     already works correctly - both <select> and <input> expose .value.
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
*/
</script>

<script>
/* â”€â”€ AUTO-REFRESH POLLING ENGINE â”€â”€ */
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
































