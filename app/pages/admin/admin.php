<?php
/* admin.php â€” Tere LEARN | Super Admin Panel */
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 1) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php');
    exit;
}

require_once dirname(__DIR__, 2) . '/config/db_connect.php';

$sessionUserId = $_SESSION['user_id'];
$adminId = $sessionUserId;
$adminName = 'Administrator';
$adminRole = 'System Administrator';
$adminInitials = 'AD';
$adminPic = '';

$uidEsc = mysqli_real_escape_string($conn, $sessionUserId);
$uRes = $conn->query("SELECT email, username, profile_picture FROM tbluser WHERE id='$uidEsc' AND is_deleted=0 LIMIT 1");
$uRow = $uRes ? $uRes->fetch_assoc() : null;

if ($uRow) {
    $emailEsc = mysqli_real_escape_string($conn, $uRow['email']);
    $userEsc  = mysqli_real_escape_string($conn, $uRow['username']);

    $aRes = $conn->query("
        SELECT id, first_name, middle_name, last_name, is_superadmin
        FROM tbladmin
        WHERE (email='$emailEsc' OR username='$userEsc') AND is_deleted=0
        LIMIT 1
    ");
    $aRow = $aRes ? $aRes->fetch_assoc() : null;

    if ($aRow) {
        $adminId = $aRow['id'];
        $first = trim((string)($aRow['first_name'] ?? ''));
        $last  = trim((string)($aRow['last_name'] ?? ''));
        $full  = trim($first . ' ' . $last);
        if ($full !== '') $adminName = $full;
        $adminRole = ((int)($aRow['is_superadmin'] ?? 0) === 1) ? 'Super Admin' : 'Administrator';
    }

    $adminPic = trim((string)($uRow['profile_picture'] ?? ''));
}

$parts = preg_split('/\s+/', trim($adminName));
if (!empty($parts)) {
    $firstCh = strtoupper(substr($parts[0], 0, 1));
    $lastCh = strtoupper(substr($parts[count($parts) - 1], 0, 1));
    $adminInitials = $firstCh . $lastCh;
}

$adminNameEsc = htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8');
$adminNameTitleEsc = htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8');
$isRootAdmin = (strtolower($adminRole) === 'super admin');
$adminRoleEsc = htmlspecialchars($adminRole, ENT_QUOTES, 'UTF-8');
$adminPicEsc = htmlspecialchars($adminPic, ENT_QUOTES, 'UTF-8');
$hasAdminPic = $adminPicEsc !== '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>
(function() {
  var KEY = 'tl_dark';
  if (localStorage.getItem(KEY) === '1') document.documentElement.classList.add('dark');
  document.addEventListener('DOMContentLoaded', function() {
    var body = document.body;
    if (localStorage.getItem(KEY) === '1') body.classList.add('dark');
    var btn = document.getElementById('darkToggle');
    if (!btn) return;
    var icon = btn.querySelector('i');
    if (icon) icon.className = localStorage.getItem(KEY) === '1' ? 'fas fa-sun' : 'fas fa-moon';
    btn.addEventListener('click', function(e) {
      e.preventDefault(); e.stopImmediatePropagation();
      var on = !body.classList.contains('dark');
      body.classList.toggle('dark', on);
      document.documentElement.classList.toggle('dark', on);
      if (icon) icon.className = on ? 'fas fa-sun' : 'fas fa-moon';
      localStorage.setItem(KEY, on ? '1' : '0');
    }, true);
  });
})();
</script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TERELEARN | <?= $adminNameTitleEsc ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<!-- Keep AdminLTE for compatibility but suppress its layout -->
<link rel="stylesheet" href="dist/css/adminlte.min.css">

<style>
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   TERELEARN ADMIN â€” facultyUI aesthetic
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
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
  overflow: hidden;
}
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
a { text-decoration: none; }

/* â”€â”€ suppress AdminLTE layout â”€â”€ */
.main-sidebar, .main-header { display: none !important; }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   TOP NAV
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.topnav {
  position: fixed; inset: 0 0 auto 0; height: var(--nav-h);
  background: var(--surface); border-bottom: 1px solid var(--border);
  display: flex; align-items: center; padding: 0 1.5rem; gap: .75rem;
  z-index: 200; transition: background var(--transition), border-color var(--transition);
}
.nav-brand {
  display: flex; align-items: center; gap: .6rem;
  font-size: 1.15rem; font-weight: 700; color: var(--text);
  text-decoration: none; white-space: nowrap;
}
.brand-logo {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  border-radius: 10px; display: flex; align-items: center;
  justify-content: center; color: #fff; font-size: .95rem;
  box-shadow: 0 2px 8px rgba(26,158,120,.35);
}
.tl-sem-chip {
  display: inline-flex; align-items: center; gap: .4rem;
  background: var(--primary-light); color: var(--primary);
  border: 1.5px solid var(--primary); border-radius: 8px;
  padding: .22rem .8rem; font-weight: 700; font-size: .72rem;
  letter-spacing: .3px; white-space: nowrap; cursor: pointer;
  transition: background var(--transition);
}
.tl-sem-chip:hover { background: #d2f0e8; }
body.dark .tl-sem-chip { border-color: var(--primary); }

.nav-actions { margin-left: auto; display: flex; align-items: center; gap: .4rem; }
.icon-btn {
  width: 38px; height: 38px; border: none; background: none;
  cursor: pointer; color: var(--text-muted); font-size: 1rem;
  display: flex; align-items: center; justify-content: center;
  border-radius: 10px; transition: all var(--transition); text-decoration: none;
}
.icon-btn:hover { background: var(--border); color: var(--text); }
.nav-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: linear-gradient(135deg, #b45309, #f59e0b);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: .85rem; cursor: pointer;
  border: 2px solid transparent; transition: border-color var(--transition);
  overflow: hidden;
}
.nav-avatar:hover { border-color: var(--primary); }
.nav-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.menu-btn {
  width: 38px; height: 38px; border: none; background: none;
  cursor: pointer; color: var(--text-muted); font-size: 1.1rem;
  display: flex; align-items: center; justify-content: center;
  border-radius: 10px; transition: all var(--transition);
}
.menu-btn:hover { background: var(--border); color: var(--text); }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   SIDEBAR
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.sidebar {
  position: fixed; top: var(--nav-h); left: 0; bottom: var(--footer-h);
  width: var(--sidebar-w); background: var(--surface);
  border-right: 1px solid var(--border); padding: 1rem 0 1rem;
  overflow-y: auto; overflow-x: hidden; z-index: 150;
  transition: transform var(--transition), width var(--transition),
              background var(--transition), border-color var(--transition);
}
.sidebar.collapsed { width: 70px; }
@media (max-width: 768px) {
  .dept-meta-line { display: flex; }
  .sidebar { transform: translateX(-100%); width: var(--sidebar-w) !important; }
  .sidebar.open { transform: translateX(0); }
}

/* User slot */
.sidebar-user {
  display: flex; align-items: center; gap: .8rem;
  padding: .85rem 1.2rem 1rem; border-bottom: 1px solid var(--border);
  margin-bottom: .5rem; overflow: hidden;
}
.s-avatar {
  width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, #b45309, #f59e0b);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: 1rem;
  overflow: hidden;
}
.s-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.s-name { font-weight: 600; font-size: .88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.s-role { font-size: .72rem; color: var(--text-muted); }
.sidebar.collapsed .s-name, .sidebar.collapsed .s-role { display: none; }
.s-role-chip {
  display: inline-flex; align-items: center; gap: .22rem;
  font-size: .58rem; font-weight: 700; padding: .1rem .38rem;
  border-radius: 20px; text-transform: uppercase;
  background: rgba(245,158,11,.18); color: #f59e0b; margin-left: .25rem;
}

/* Nav items */
.nav-section-label {
  font-size: .65rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 1.2px; color: var(--text-muted);
  padding: .7rem 1.2rem .3rem; white-space: nowrap;
  overflow: hidden; transition: opacity var(--transition);
}
.sidebar.collapsed .nav-section-label { opacity: 0; }

.nav-item {
  display: flex; align-items: center; gap: .85rem;
  padding: .6rem 1.2rem; color: var(--text-muted);
  text-decoration: none; border: none; background: none;
  width: 100%; font-size: .88rem; font-weight: 500; font-family: inherit;
  border-left: 3px solid transparent; cursor: pointer;
  white-space: nowrap; overflow: hidden;
  transition: all var(--transition);
}
.nav-item i { width: 18px; text-align: center; font-size: .95rem; flex-shrink: 0; }
.nav-item:hover { background: var(--primary-light); color: var(--primary); }
.nav-item.active { background: var(--primary-light); color: var(--primary); border-left-color: var(--primary); font-weight: 600; }
.sidebar.collapsed .nav-item { justify-content: center; padding: .6rem; }
.sidebar.collapsed .nav-item .sb-label { display: none; }

/* Sub-nav */
.nav-sub { overflow: hidden; max-height: 0; transition: max-height .22s ease; }
.nav-sub-item.open > .nav-sub { max-height: 400px; }
.sidebar.collapsed .nav-sub { max-height: 0 !important; }
.nav-sub .nav-item {
  padding: .45rem 1.2rem .45rem 2.6rem;
  font-size: .84rem; border-left-color: transparent;
}
.nav-sub .nav-item::before {
  content: ''; width: 5px; height: 5px; border-radius: 50%;
  background: var(--border); flex-shrink: 0; margin-right: .5rem;
  transition: background var(--transition);
}
.nav-sub .nav-item:hover::before { background: var(--primary-light); }
.nav-sub .nav-item.active::before { background: var(--primary); }
.nav-sub-toggle {
  display: flex; align-items: center; gap: .85rem;
  padding: .6rem 1.2rem; color: var(--text-muted);
  border: none; background: none; width: 100%;
  font-size: .88rem; font-weight: 500; font-family: inherit;
  border-left: 3px solid transparent; cursor: pointer;
  white-space: nowrap; overflow: hidden;
  transition: all var(--transition);
}
.nav-sub-toggle i { width: 18px; text-align: center; font-size: .95rem; flex-shrink: 0; }
.nav-sub-toggle:hover { background: var(--primary-light); color: var(--primary); }
.nav-sub-arrow { margin-left: auto; font-size: .65rem; opacity: .4; transition: transform var(--transition); }
.nav-sub-item.open > .nav-sub-toggle .nav-sub-arrow { transform: rotate(90deg); opacity: .7; }
.sidebar.collapsed .nav-sub-toggle { justify-content: center; padding: .6rem; }
.sidebar.collapsed .nav-sub-toggle .sb-label,
.sidebar.collapsed .nav-sub-toggle .nav-sub-arrow { display: none; }

/* Signout */
.sidebar-footer-inner { padding: .75rem; border-top: 1px solid var(--border); }
.signout-btn {
  display: flex; align-items: center; gap: .85rem;
  padding: .55rem 1rem; border-radius: var(--radius-sm);
  color: var(--danger); text-decoration: none;
  font-size: .88rem; font-weight: 600;
  transition: background var(--transition);
  width: 100%; border: none; background: none; cursor: pointer;
  font-family: inherit; white-space: nowrap; overflow: hidden;
}
.signout-btn i { width: 18px; text-align: center; flex-shrink: 0; }
.signout-btn:hover { background: #fdecea; }
.sidebar.collapsed .signout-btn { justify-content: center; padding: .55rem; }
.sidebar.collapsed .signout-btn span { display: none; }

/* Overlay */
.overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 140; }
.overlay.show { display: block; }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   MAIN + FOOTER
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.content-wrapper, .main-footer { display: none !important; }  /* suppress AdminLTE */

.main {
  margin-left: var(--sidebar-w); margin-top: var(--nav-h);
  padding: 2rem 2rem calc(var(--footer-h) + 1.5rem);
  height: calc(100vh - var(--nav-h));
  overflow-y: auto;
  overflow-x: hidden;
  transition: margin-left var(--transition);
}
.main.collapsed { margin-left: 70px; }
@media (max-width: 768px) {
  .dept-meta-line { display: flex; }
  .main {
    margin-left: 0;
    padding: 1rem 1rem calc(var(--footer-h) + 1rem);
    height: calc(100vh - var(--nav-h));
  }
}

.tl-footer {
  position: fixed; bottom: 0; left: 0; right: 0;
  height: var(--footer-h); background: var(--surface);
  border-top: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 1.5rem; z-index: 190;
  font-size: .78rem; color: var(--text-muted);
  transition: background var(--transition), border-color var(--transition);
}
.footer-sem-badge {
  display: inline-flex; align-items: center; gap: .45rem;
  background: var(--primary-light); color: var(--primary);
  border: 1.5px solid var(--primary); border-radius: 8px;
  padding: .22rem .8rem; font-weight: 700; font-size: .72rem;
  letter-spacing: .3px; white-space: nowrap;
}
.footer-sem-badge i { font-size: .65rem; opacity: .8; }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   SECTIONS (pages)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.admin-section { display: none; animation: fadeUp .28s ease; }
.admin-section.active { display: block; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;
}
.page-title { font-size: 1.65rem; font-weight: 700; }
.page-subtitle { color: var(--text-muted); font-size: .88rem; margin-top: .2rem; }

/* Breadcrumb */
.tl-breadcrumb {
  display: flex; align-items: center; gap: .35rem;
  font-size: .78rem; color: var(--text-muted);
}
.tl-breadcrumb a { color: var(--text-muted); text-decoration: none; transition: color var(--transition); }
.tl-breadcrumb a:hover { color: var(--primary); }
.tl-breadcrumb .sep { opacity: .4; }
.tl-breadcrumb .cur { color: var(--text); font-weight: 600; }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   STAT CARDS
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.stat-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
  gap: .85rem; margin-bottom: 1.5rem;
}
.stat-card {
  position: relative; overflow: hidden;
  background:
    linear-gradient(180deg, rgba(255,255,255,.34), rgba(255,255,255,0)),
    var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px; padding: .9rem .95rem .85rem;
  box-shadow: var(--shadow);
  min-height: 134px;
  display: grid; grid-template-rows: auto 1fr auto; gap: .65rem;
  transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
}
.stat-card::before {
  content: '';
  position: absolute; inset: 0 auto 0 0; width: 4px;
  background: linear-gradient(180deg, var(--stat-accent, var(--primary)), transparent 82%);
  opacity: .95;
}
.stat-card::after {
  content: '';
  position: absolute; inset: -35% -30% auto auto;
  width: 110px; height: 110px; border-radius: 999px;
  background: var(--stat-soft, var(--primary-light));
  opacity: .55; filter: blur(8px); pointer-events: none;
}
.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
  border-color: var(--stat-accent, var(--primary));
}
.stat-card-head,
.stat-card-main,
.stat-card-foot {
  position: relative; z-index: 1;
}
.stat-card-head {
  display: flex; align-items: flex-start; justify-content: space-between; gap: .7rem;
}
.stat-mini {
  display: inline-flex; align-items: center; gap: .35rem;
  width: fit-content;
  font-size: .64rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
  color: var(--stat-accent, var(--primary));
  background: var(--stat-soft, var(--primary-light));
  border: 1px solid rgba(255,255,255,.3);
  border-radius: 999px; padding: .28rem .52rem;
}
.stat-icon {
  width: 42px; height: 42px; border-radius: 13px;
  display: flex; align-items: center; justify-content: center;
  font-size: .98rem; flex-shrink: 0;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.45);
}
.stat-card.sc-g,
.si-g  { --stat-accent: var(--primary); --stat-soft: var(--primary-light); background: var(--primary-light); color: var(--primary); }
.stat-card.sc-b,
.si-b  { --stat-accent: var(--accent); --stat-soft: var(--accent-light); background: var(--accent-light);  color: var(--accent); }
.stat-card.sc-o,
.si-o  { --stat-accent: var(--warning); --stat-soft: #fff3e0; background: #fff3e0; color: var(--warning); }
.stat-card.sc-p,
.si-p  { --stat-accent: #7b1fa2; --stat-soft: #f3e5f5; background: #f3e5f5; color: #7b1fa2; }
.stat-card.sc-t,
.si-t  { --stat-accent: #00838f; --stat-soft: #e0f7fa; background: #e0f7fa; color: #00838f; }
.stat-card.sc-r,
.si-r  { --stat-accent: #c62828; --stat-soft: #fce4ec; background: #fce4ec; color: #c62828; }
.stat-card.sc-s,
.si-s  { --stat-accent: #475569; --stat-soft: #f1f5f9; background: #f1f5f9; color: #475569; }
.stat-card .stat-icon.si-g,
.stat-card .stat-icon.si-b,
.stat-card .stat-icon.si-o,
.stat-card .stat-icon.si-p,
.stat-card .stat-icon.si-t,
.stat-card .stat-icon.si-r,
.stat-card .stat-icon.si-s {
  background: var(--stat-soft);
  color: var(--stat-accent);
}
.stat-card-main {
  display: grid; gap: .18rem;
}
.stat-val {
  font-size: 2rem; font-weight: 800; line-height: .95;
  letter-spacing: -.04em; color: var(--text);
}
.stat-lbl {
  font-size: .83rem; font-weight: 700; color: var(--text);
}
.stat-card-foot {
  display: flex; align-items: center; justify-content: space-between; gap: .5rem;
}
.stat-foot-note {
  display: inline-flex; align-items: center; gap: .38rem;
  font-size: .68rem; color: var(--text-muted); white-space: nowrap;
}
.stat-foot-note i {
  font-size: .46rem; color: var(--stat-accent, var(--primary));
}
.stat-foot-arrow {
  width: 28px; height: 28px; border-radius: 10px;
  display: inline-flex; align-items: center; justify-content: center;
  background: var(--stat-soft, var(--primary-light));
  color: var(--stat-accent, var(--primary));
  transition: transform var(--transition), background var(--transition), color var(--transition);
}
.stat-card:hover .stat-foot-arrow {
  transform: translate(2px, -2px);
}
body.dark .stat-card {
  background:
    linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,0)),
    var(--surface);
}
body.dark .stat-card::after {
  opacity: .22;
}
body.dark .stat-mini {
  border-color: rgba(255,255,255,.08);
}

.dash-analytics-grid {
  display:grid; grid-template-columns:1.15fr .85fr; gap:1rem; margin-bottom:1.25rem;
}
.dash-analytics-stack {
  display:grid; gap:1rem;
}
.analytics-card {
  background:var(--surface); border:1px solid var(--border); border-radius:var(--radius);
  box-shadow:var(--shadow); padding:1.1rem 1.2rem;
}
.analytics-card-head {
  display:flex; align-items:flex-start; justify-content:space-between; gap:.8rem; margin-bottom:.95rem;
}
.analytics-card-title {
  font-size:.92rem; font-weight:800; color:var(--text); line-height:1.2;
}
.analytics-card-sub {
  font-size:.74rem; color:var(--text-muted); margin-top:.18rem;
}
.analytics-chart-wrap {
  position:relative; height:300px;
}
.analytics-chart-wrap.compact {
  height:255px;
}
.analytics-kicker {
  display:inline-flex; align-items:center; gap:.35rem;
  font-size:.68rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase;
  color:var(--primary); background:var(--primary-light); border-radius:999px; padding:.3rem .55rem;
}
.top-program-list {
  display:grid; gap:.65rem; margin-top:1rem;
}
.top-program-item {
  display:flex; align-items:center; justify-content:space-between; gap:.8rem;
  padding:.75rem .85rem; border:1px solid var(--border); border-radius:12px; background:var(--bg);
}
.top-program-main { min-width:0; }
.top-program-title {
  font-size:.82rem; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.top-program-meta {
  font-size:.72rem; color:var(--text-muted); margin-top:.14rem;
}
.top-program-count {
  font-size:1rem; font-weight:800; color:var(--primary); white-space:nowrap;
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   SECTION DIVIDER
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.section-divider {
  display: flex; align-items: center; gap: .75rem;
  font-size: .75rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .9px; color: var(--text-muted);
  margin: 1.75rem 0 1rem;
}
.section-divider .sd-icon {
  width: 28px; height: 28px; border-radius: 8px;
  background: var(--primary-light); color: var(--primary);
  display: flex; align-items: center; justify-content: center; font-size: .8rem;
}
.section-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   CONTENT CARD (replaces Bootstrap card/shadow-lg)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.tl-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius); box-shadow: var(--shadow);
  padding: 1.4rem 1.6rem; margin-bottom: 1.25rem;
}
.tl-card-title {
  font-size: .88rem; font-weight: 700;
  display: flex; align-items: center; gap: .5rem;
  margin-bottom: 1.1rem; color: var(--text);
}
.tl-card-title i { color: var(--primary); }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   DEPARTMENT CARDS
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.dept-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px,1fr)); gap: 1rem; }
.dept-card {
  background: var(--surface); border: 1.5px solid var(--border);
  border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);
  transition: transform var(--transition), box-shadow var(--transition);
}
.dept-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.dept-header {
  padding: .85rem 1rem; display: flex; align-items: center; gap: .75rem;
  border-bottom: 1px solid var(--border);
}
.dept-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.dept-code-badge {
  font-size: .65rem; font-weight: 700; padding: .18rem .5rem;
  border-radius: 20px; text-transform: uppercase; letter-spacing: .5px;
}
.dept-name { font-size: .88rem; font-weight: 700; flex: 1; }
.dept-body { padding: .9rem 1rem; }
.dept-photo {
  width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
  object-fit: cover; border: 1px solid rgba(255,255,255,.35);
  box-shadow: 0 6px 18px rgba(15,23,42,.12);
}
.dept-photo-fallback {
  width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: .95rem; font-weight: 800;
  box-shadow: 0 6px 18px rgba(15,23,42,.12);
}
.dean-slot {
  display: flex; align-items: center; gap: .6rem;
  padding: .45rem .65rem; border-radius: 8px;
  background: var(--bg); margin-bottom: .35rem;
  border: 1px solid var(--border);
}
.role-badge {
  font-size: .6rem; font-weight: 700; padding: .15rem .42rem;
  border-radius: 20px; text-transform: uppercase; letter-spacing: .3px; flex-shrink: 0;
}
.rb-dean  { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.rb-sec   { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
.dean-name { font-size: .8rem; font-weight: 600; flex: 1; }
.dean-sub  { font-size: .7rem; color: var(--text-muted); }
.btn-rm-dean {
  width: 22px; height: 22px; border: none; background: none; cursor: pointer;
  border-radius: 5px; color: #adb5bd; font-size: .68rem;
  display: flex; align-items: center; justify-content: center;
  transition: all .12s; flex-shrink: 0;
}
.btn-rm-dean:hover { background: #fdecea; color: var(--danger); }
.no-dean { font-size: .78rem; color: #adb5bd; text-align: center; padding: .45rem 0; font-style: italic; }
.dept-footer {
  padding: .6rem 1rem; border-top: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  gap: .4rem; background: var(--bg);
}
.prog-pills { display: flex; flex-wrap: wrap; gap: .3rem; }
/* â”€â”€ PROG PILLS (enhanced) â”€â”€ */
.prog-pill {
  font-size:.62rem; padding:.18rem .52rem; border-radius:20px;
  background:var(--primary-light); color:var(--primary);
  border:1px solid rgba(26,158,120,.3); font-weight:600;
  display:inline-flex; align-items:center; gap:.25rem;
}
.prog-pill-remove {
  border:none; background:none; cursor:pointer;
  color:rgba(26,158,120,.5); font-size:.6rem; padding:0 0 0 .2rem;
  line-height:1; transition:color .12s;
}
.prog-pill-remove:hover { color:var(--danger); }

/* â”€â”€ DEPT CARD v2 (richer layout) â”€â”€ */
.dept-card-v2 {
  background:var(--surface); border:1.5px solid var(--border);
  border-radius:var(--radius); overflow:hidden; box-shadow:var(--shadow);
  display:flex; flex-direction:column;
  transition:transform var(--transition), box-shadow var(--transition), border-color var(--transition);
}
.dept-card-v2:hover {
  transform:translateY(-2px); box-shadow:var(--shadow-md);
  border-color:rgba(26,158,120,.3);
}
.dept-card-v2.inactive-dept { opacity:.55; }

/* Coloured left-accent bar */
.dept-card-v2 .dc-accent {
  height:4px; width:100%;
}
.dept-card-v2 .dc-head {
  padding:.85rem 1.1rem .65rem;
  display:flex; align-items:flex-start; gap:.7rem;
  border-bottom:1px solid var(--border);
}
.dc-icon {
  width:40px; height:40px; border-radius:11px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  font-size:.95rem; font-weight:700; color:#fff;
}
.dc-head-info { flex:1; min-width:0; }
.dc-head-info .dc-code {
  font-size:.62rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.6px; padding:.15rem .5rem; border-radius:20px;
  display:inline-block; margin-bottom:.22rem;
}
.dc-head-info .dc-name {
  font-size:.9rem; font-weight:700;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.dc-head-info .dc-desc {
  font-size:.72rem; color:var(--text-muted); margin-top:.12rem;
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}
.dc-head-actions {
  display:flex; gap:.3rem; flex-shrink:0;
}
.dc-head-actions .ic {
  width:30px; height:30px; border-radius:8px; border:1.5px solid var(--border);
  background:none; cursor:pointer; font-size:.72rem; color:var(--text-muted);
  display:flex; align-items:center; justify-content:center;
  transition:all .14s;
}
.dc-head-actions .ic:hover { background:var(--primary-light); color:var(--primary); border-color:var(--primary); }
.dc-head-actions .ic.danger:hover { background:#fdecea; color:var(--danger); border-color:var(--danger); }

/* Personnel strip */
.dc-personnel {
  padding:.6rem 1.1rem;
  display:flex; flex-direction:column; gap:.35rem;
  border-bottom:1px solid var(--border);
  min-height:70px;
}
.dc-role-row {
  display:flex; align-items:center; gap:.55rem;
  padding:.38rem .6rem; border-radius:8px;
  border:1px solid var(--border); background:var(--bg);
}
.dc-role-badge {
  font-size:.58rem; font-weight:800; padding:.15rem .45rem;
  border-radius:20px; text-transform:uppercase; letter-spacing:.3px; flex-shrink:0;
}
.rb-dean { background:#fef3c7; color:#92400e; border:1px solid #fcd34d; }
.rb-sec  { background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe; }
.dc-role-info { flex:1; min-width:0; }
.dc-role-name { font-size:.8rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.dc-role-sub  { font-size:.68rem; color:var(--text-muted); }
.dc-role-rm {
  width:22px; height:22px; border:none; background:none; cursor:pointer;
  border-radius:5px; color:#adb5bd; font-size:.65rem;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
  transition:all .12s;
}
.dc-role-rm:hover { background:#fdecea; color:var(--danger); }

/* Empty state */
.dc-empty-slot {
  display:flex; align-items:center; justify-content:space-between;
  padding:.38rem .65rem; border-radius:8px;
  border:1.5px dashed var(--border); background:var(--bg);
  font-size:.76rem; color:#adb5bd; font-style:italic;
}
.dc-empty-slot .assign-quick-btn {
  font-size:.68rem; font-weight:700; color:var(--primary);
  background:var(--primary-light); border:1.5px solid rgba(26,158,120,.3);
  border-radius:6px; padding:.18rem .5rem; cursor:pointer;
  white-space:nowrap; font-family:inherit; transition:all .14s;
}
.dc-empty-slot .assign-quick-btn:hover { background:var(--primary); color:#fff; }

/* Programs area */
.dc-progs {
  padding:.6rem 1.1rem; flex:1;
  border-bottom:1px solid var(--border);
  display:flex; flex-wrap:wrap; gap:.3rem; align-content:flex-start;
  min-height:42px;
}
.dc-progs .no-progs {
  font-size:.75rem; color:#adb5bd; font-style:italic;
  display:flex; align-items:center; gap:.35rem;
}

/* Footer actions */
.dc-footer {
  padding:.55rem 1.1rem;
  display:flex; align-items:center; justify-content:space-between;
  background:var(--bg); gap:.4rem; flex-wrap:wrap;
}
.dc-footer .dc-prog-count {
  font-size:.72rem; color:var(--text-muted); display:flex; align-items:center; gap:.3rem;
}
.dc-footer .dc-footer-btns { display:flex; gap:.3rem; }

/* â”€â”€ PROGRAM TABLE v2 â”€â”€ */
.prog-dept-badge {
  display:inline-flex; align-items:center; gap:.3rem;
  font-size:.68rem; font-weight:700; padding:.18rem .55rem;
  border-radius:20px; white-space:nowrap;
}
.prog-dept-cell {
  display:flex; flex-direction:column; align-items:flex-start; gap:.18rem;
}
.prog-dept-name {
  font-size:.78rem; line-height:1.2;
}
.prog-program-cell {
  display:flex; flex-direction:column; align-items:flex-start; gap:.18rem;
}
.prog-program-name {
  font-size:.83rem; font-weight:600; line-height:1.2;
}
.prog-no-dept {
  font-size:.72rem; color:#adb5bd; font-style:italic;
}

/* â”€â”€ ASSIGN MODAL v2 (refined) â”€â”€ */
.person-option-card {
  display:flex; align-items:center; gap:.75rem;
  padding:.6rem .8rem; border-radius:10px;
  border:1.5px solid var(--border); background:var(--surface);
  cursor:pointer; margin-bottom:.4rem; font-size:.85rem;
  position:relative;
  transition: border-color .18s ease, background .18s ease,
              transform .18s ease, box-shadow .18s ease;
}
.person-option-card:hover {
  border-color:var(--primary); background:var(--primary-light);
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(26,158,120,.12);
}
.person-option-card.selected {
  border-color:var(--primary); background:var(--primary-light);
  box-shadow: inset 0 0 0 1px var(--primary), 0 4px 14px rgba(26,158,120,.18);
}
.person-option-card.selected::after {
  content:"\f00c"; font-family:"Font Awesome 6 Free"; font-weight:900;
  position:absolute; right:.75rem; top:50%; transform:translateY(-50%);
  color:var(--primary); font-size:.85rem;
}
.poc-avatar {
  width:36px; height:36px; border-radius:50%; flex-shrink:0;
  background:linear-gradient(135deg,#b45309,#f59e0b);
  display:flex; align-items:center; justify-content:center;
  color:#fff; font-weight:700; font-size:.78rem;
  box-shadow: 0 2px 6px rgba(180,83,9,.25);
}
.poc-name { font-weight:600; font-size:.86rem; color:var(--text); line-height:1.15; }
.poc-sub  { font-size:.72rem; color:var(--text-muted); margin-top:2px; }

/* Person list scroll area */
.person-list-wrap {
  max-height:230px; overflow-y:auto;
  border:1.5px solid var(--border); border-radius:11px;
  padding:.5rem; background:var(--bg);
  box-shadow: inset 0 1px 3px rgba(0,0,0,.04);
}
.person-list-wrap::-webkit-scrollbar { width:6px; }
.person-list-wrap::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }
.person-list-wrap::-webkit-scrollbar-thumb:hover { background:var(--primary); }

/* â”€â”€ Locked department card (when modal opens from a dept card) â”€â”€ */
.dept-locked-card {
  display:flex; align-items:center; gap:.75rem;
  padding:.7rem .85rem; border-radius:12px;
  background:linear-gradient(135deg, var(--primary-light), rgba(26,158,120,.04));
  border:1.5px solid rgba(26,158,120,.35);
  box-shadow: 0 3px 12px rgba(26,158,120,.08);
  animation: tlFadeUp .3s cubic-bezier(.16,1,.3,1) both;
}
.dlc-icon {
  width:40px; height:40px; flex-shrink:0; border-radius:11px;
  background:var(--primary); color:#fff;
  display:flex; align-items:center; justify-content:center;
  font-size:1rem; box-shadow: 0 4px 12px rgba(26,158,120,.35);
}
.dlc-info { flex:1; min-width:0; }
.dlc-label {
  font-size:.62rem; font-weight:700; letter-spacing:.8px;
  text-transform:uppercase; color:var(--primary);
}
.dlc-name {
  font-size:.92rem; font-weight:700; color:var(--text);
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.dlc-change {
  border:1.5px solid var(--border); background:var(--surface);
  color:var(--text-muted); border-radius:8px;
  padding:.34rem .65rem; font-size:.72rem; font-weight:600;
  cursor:pointer; flex-shrink:0;
  transition: all .18s ease;
}
.dlc-change:hover {
  border-color:var(--primary); color:var(--primary);
  background:var(--primary-light); transform:translateY(-1px);
  box-shadow: 0 3px 8px rgba(26,158,120,.18);
}
.dept-locked-tag {
  display:inline-flex; align-items:center; gap:.25rem;
  font-size:.6rem; font-weight:700; padding:.12rem .5rem;
  border-radius:20px; background:var(--primary-light);
  color:var(--primary); margin-left:.4rem; letter-spacing:.4px;
}

/* Compact dept-card status pill (replaces redundant Assign/Edit buttons) */
.dc-status-pill {
  display:inline-flex; align-items:center; gap:.32rem;
  font-size:.7rem; font-weight:700; letter-spacing:.3px;
  padding:.24rem .6rem; border-radius:20px; border:1px solid;
}
.dc-status-pill i { font-size:.68rem; }
.dc-status-pill.ok   { background:var(--primary-light); color:var(--primary); border-color:rgba(26,158,120,.3); }
.dc-status-pill.warn { background:#fff3cd; color:#b45309; border-color:rgba(245,158,11,.35); }
.dc-status-pill.miss { background:#fdecea; color:var(--danger); border-color:rgba(217,48,37,.3); }

/* â”€â”€ Smoother assign modal motion + interactive polish â”€â”€ */
#assignModal .modal-content {
  border:none; border-radius:16px; overflow:hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,.18);
}
#assignModal.fade .modal-dialog {
  transition: transform .34s cubic-bezier(.16,1,.3,1), opacity .26s ease;
  transform: translateY(16px) scale(.985); opacity:0;
}
#assignModal.show .modal-dialog {
  transform: translateY(0) scale(1); opacity:1;
}
#assignModal .modal-header {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color:#fff; border-bottom:none; padding:1rem 1.25rem;
}
#assignModal .modal-header .modal-title { color:#fff; font-weight:700; font-size:1rem; }
#assignModal .modal-header .btn-close {
  filter: invert(1) brightness(2); opacity:.85;
  transition: transform .25s ease, opacity .2s ease;
}
#assignModal .modal-header .btn-close:hover { opacity:1; transform:rotate(90deg); }

#assignModal .form-section { animation: tlFadeUp .35s cubic-bezier(.16,1,.3,1) both; }
#assignModal .form-section:nth-of-type(1) { animation-delay:.02s; }
#assignModal .form-section:nth-of-type(2) { animation-delay:.08s; }
#assignModal .form-section:nth-of-type(3) { animation-delay:.14s; }
@keyframes tlFadeUp {
  from { opacity:0; transform: translateY(8px); }
  to   { opacity:1; transform: translateY(0); }
}

#assignSubmitBtn {
  border-radius:10px; padding:.55rem 1.15rem;
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}
#assignSubmitBtn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(26,158,120,.32);
}

.role-pick-card {
  transition: transform .2s ease, box-shadow .2s ease,
              border-color .2s ease, background .2s ease;
}
.role-pick-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.07); }
.role-pick-card:has(input:checked) {
  box-shadow: inset 0 0 0 1.5px var(--primary), 0 6px 16px rgba(26,158,120,.18);
  transform: translateY(-1px);
}

/* Conflict badge */
.conflict-badge {
  display:inline-flex; align-items:center; gap:.3rem;
  background:#fff3e0; color:var(--warning); border:1px solid #f59e0b;
  border-radius:6px; padding:.2rem .55rem; font-size:.7rem; font-weight:700;
}

/* â”€â”€ DEPT SEARCH & FILTER BAR â”€â”€ */
.dept-filter-bar {
  display:flex; align-items:center; gap:.55rem; flex-wrap:wrap; margin-bottom:1.1rem;
}
.dept-filter-bar .search-wrap { flex:1; min-width:180px; }
.dept-filter-bar .dept-sort-sel { flex-shrink:0; }
.dept-filter-bar .filter-chip { flex-shrink:0; font-size:.72rem; padding:.28rem .65rem; }

@media (max-width: 768px) {
  .dept-meta-line { display: flex; }
  .dept-filter-bar { gap:.4rem; }
  /* Row 1: search fills full width */
  .dept-filter-bar .search-wrap { flex:1 1 100%; order:0; min-width:0; }
  /* Row 2: sort + chips + add button in one line */
  .dept-filter-bar .dept-sort-sel { order:1; font-size:.74rem; padding:.3rem .5rem; }
  .dept-filter-bar .filter-chip { order:2; font-size:.68rem; padding:.24rem .55rem; }
  /* Add Dept button is in its own row above â€” no override needed */
}
.dept-filter-bar .filter-chip {
  display:inline-flex; align-items:center; gap:.3rem;
  font-size:.73rem; font-weight:600; padding:.3rem .75rem;
  border-radius:20px; border:1.5px solid var(--border);
  background:var(--surface); color:var(--text-muted);
  cursor:pointer; transition:all .14s; white-space:nowrap;
}
.dept-filter-bar .filter-chip:hover,
.dept-filter-bar .filter-chip.active {
  border-color:var(--primary); background:var(--primary-light); color:var(--primary);
}

/* â”€â”€ DEPT STATS BAR â”€â”€ */
.dept-stats-bar {
  display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;
  margin-bottom:1rem; font-size:.78rem; color:var(--text-muted);
}
.dept-stats-bar .ds-chip {
  display:inline-flex; align-items:center; gap:.3rem;
  background:var(--bg); border:1px solid var(--border);
  border-radius:20px; padding:.22rem .75rem; font-weight:600;
}
.dept-stats-bar .ds-chip i { font-size:.65rem; color:var(--primary); }

/* â”€â”€ DEPT SORT SELECT â”€â”€ */
.dept-sort-sel {
  font-size:.78rem; border:1.5px solid var(--border);
  border-radius:8px; padding:.32rem .65rem;
  background:var(--surface); color:var(--text);
  cursor:pointer; outline:none;
  transition:border-color .15s;
}
.dept-sort-sel:focus { border-color:var(--primary); }

/* â”€â”€ PROGRAM QUICK-ADD inline in dept section â”€â”€ */
.prog-quick-form {
  display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;
  padding:.7rem .85rem; background:var(--primary-light);
  border:1.5px dashed rgba(26,158,120,.4); border-radius:10px;
  margin-top:.6rem;
}
.prog-quick-form input, .prog-quick-form select {
  border:1.5px solid var(--border); border-radius:7px; font-size:.84rem;
  padding:.38rem .65rem; background:var(--surface); color:var(--text);
  flex:1; min-width:120px;
}
.prog-quick-form input:focus, .prog-quick-form select:focus {
  border-color:var(--primary); outline:none;
  box-shadow:0 0 0 3px var(--primary-mid);
}

/* â”€â”€ VALIDATION FEEDBACK â”€â”€ */
.field-error {
  font-size:.72rem; color:var(--danger); margin-top:.2rem;
  display:flex; align-items:center; gap:.25rem;
}
.field-error i { font-size:.65rem; }
.input-error {
  border-color:var(--danger) !important;
  box-shadow:0 0 0 3px rgba(217,48,37,.12) !important;
}

/* â”€â”€ CONFIRM TOAST (replacement for alert on delete) â”€â”€ */
.dep-has-data-warning {
  background:#fdecea; border:1.5px solid rgba(217,48,37,.3);
  border-radius:9px; padding:.75rem 1rem; margin-top:.6rem;
  font-size:.8rem; color:var(--danger);
  display:flex; align-items:flex-start; gap:.5rem;
}
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   SEMESTER PANEL
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.semester-panel {
  background: var(--surface); border: 1.5px solid var(--primary);
  border-radius: var(--radius); padding: 1.3rem 1.5rem;
  box-shadow: var(--shadow); margin-bottom: 1.5rem;
}
.sem-panel-title {
  font-size: .7rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 1px; color: var(--primary);
  display: flex; align-items: center; gap: .5rem;
  margin-bottom: 1rem; padding-bottom: .55rem;
  border-bottom: 2px solid var(--primary-light);
}
.sem-current {
  display: flex; align-items: center; gap: .75rem; margin-bottom: 1rem;
  background: var(--primary-light); border: 1px solid rgba(26,158,120,.2);
  border-radius: 10px; padding: .8rem .95rem;
}
.sem-icon {
  width: 38px; height: 38px;
  background: linear-gradient(135deg, var(--primary-dark), var(--primary));
  border-radius: 10px; display: flex; align-items: center;
  justify-content: center; color: #fff; font-size: .9rem; flex-shrink: 0;
}
.sem-label { font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--primary); margin-bottom: .08rem; }
.sem-value { font-size: .95rem; font-weight: 700; }
.undo-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  padding: .3rem .72rem; border: 1.5px solid var(--primary);
  background: var(--primary-light); border-radius: 7px;
  color: var(--primary); font-size: .72rem; font-weight: 700;
  cursor: pointer; transition: all .15s;
}
.undo-btn:hover { background: var(--primary); color: #fff; }
.undo-btn:disabled { opacity: .35; cursor: not-allowed; }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   TABLES
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.tl-table { font-size: .8rem; width: 100%; }
.tl-table thead th {
  background: #1c2a3a; color: #e4ecf7; font-size: .68rem;
  font-weight: 600; text-transform: uppercase; letter-spacing: .5px;
  padding: .55rem .75rem; border: none; white-space: nowrap;
}
.tl-table thead th.sticky-head {
  position: sticky;
  top: 0;
  z-index: 2;
}
.tl-table thead th:first-child { border-radius: 8px 0 0 0; }
.tl-table thead th:last-child  { border-radius: 0 8px 0 0; }
.tl-table tbody td { padding: .55rem .75rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
body.dark .tl-table tbody td { border-bottom-color: var(--border); }
.tl-table tbody tr { transition: background var(--transition); }
.tl-table tbody tr:hover { background: var(--primary-light) !important; }
.tl-table tbody tr:nth-child(even) { background: var(--bg); }
.pill-active   { display:inline-block;padding:.2em .6em;border-radius:20px;font-size:.68rem;font-weight:700;background:#d1fae5;color:#065f46;border:1px solid #6ee7b7; }
.pill-inactive { display:inline-block;padding:.2em .6em;border-radius:20px;font-size:.68rem;font-weight:700;background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1; }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   DEAN SECTION TABS
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.dean-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--border); margin-bottom: 1.25rem; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.dean-tab { white-space: nowrap; flex-shrink: 0; }
.dean-tab {
  padding: .6rem 1.25rem; border: none; background: none;
  font-size: .85rem; font-weight: 600; font-family: inherit;
  color: var(--text-muted); cursor: pointer;
  border-bottom: 3px solid transparent; margin-bottom: -2px;
  transition: all var(--transition);
}
.dean-tab:hover { color: var(--primary); }
.dean-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
.tab-count-badge {
  display: inline-block; background: var(--primary); color: #fff;
  font-size: .6rem; font-weight: 700; padding: .06rem .38rem;
  border-radius: 20px; margin-left: .3rem;
}
.dean-tab-panel { display: none; }
.dean-tab-panel.active { display: block; }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   ACTION BUTTONS
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.btn-act {
  display: inline-flex; align-items: center; gap: .25rem;
  padding: .22rem .52rem; border-radius: 6px; font-size: .7rem;
  font-weight: 700; cursor: pointer; transition: all .15s;
  border: 1.5px solid; font-family: inherit;
}
.btn-act-edit   { border-color:rgba(26,158,120,.35);background:var(--primary-light);color:var(--primary); }
.btn-act-edit:hover   { background:var(--primary);color:#fff;border-color:var(--primary); }
.btn-act-assign { border-color:rgba(31,115,219,.35);background:var(--accent-light);color:var(--accent); }
.btn-act-assign:hover { background:var(--accent);color:#fff;border-color:var(--accent); }
.btn-act-on     { border-color:rgba(26,158,120,.35);background:var(--primary-light);color:var(--primary); }
.btn-act-on:hover     { background:var(--primary);color:#fff; }
.btn-act-off    { border-color:rgba(217,48,37,.25);background:#fdecea;color:var(--danger); }
.btn-act-off:hover    { background:var(--danger);color:#fff; }
.btn-act-del    { border-color:rgba(217,48,37,.25);background:#fdecea;color:var(--danger); }
.btn-act-del:hover    { background:var(--danger);color:#fff; }
.btn-act-rm     { border-color:rgba(217,48,37,.25);background:#fdecea;color:var(--danger); }
.btn-act-rm:hover     { background:var(--danger);color:#fff; }

/* 3-dot dropdown for dept/table rows */
.btn-dots {
  width:32px; height:32px; border:1.5px solid var(--border);
  background:var(--surface); border-radius:8px; cursor:pointer;
  color:var(--text-muted); font-size:.82rem;
  display:inline-flex; align-items:center; justify-content:center;
  transition:all .14s; line-height:1;
}
.btn-dots:hover { background:var(--primary-light); color:var(--primary); border-color:var(--primary); }
.dept-person-cell {
  display:flex; align-items:center; gap:.5rem; min-width:0;
}
.dept-person-avatar {
  width:28px; height:28px; border-radius:50%; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  font-size:.62rem; font-weight:700; color:#fff; letter-spacing:0;
}
.dept-person-name { font-size:.82rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.dept-person-sub  { font-size:.68rem; color:var(--text-muted); }
.dept-assign-link {
  display:inline-flex; align-items:center; gap:.3rem;
  font-size:.74rem; font-weight:600; color:var(--primary);
  background:var(--primary-light); border:1.5px solid rgba(26,158,120,.28);
  border-radius:6px; padding:.22rem .6rem; cursor:pointer;
  white-space:nowrap; font-family:inherit; transition:all .14s;
}
.dept-assign-link:hover { background:var(--primary); color:#fff; border-color:var(--primary); }

/* show compact dept meta only on mobile */
.dept-meta-line { display: none; }
/* show compact dept meta only on mobile */

/* show compact dept meta only on mobile */
.dept-meta-line { display: none; }
.dept-meta-dot {
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: #c8cdd5;
  display: inline-block;
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   SEARCH
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.search-wrap { position: relative; flex: 1; }
.search-wrap .s-icon {
  position: absolute; left: .8rem; top: 50%; transform: translateY(-50%);
  color: #adb5bd; font-size: .82rem; pointer-events: none; z-index: 4;
}
.search-wrap .form-control {
  padding-left: 2.2rem; border: 1.5px solid var(--border);
  border-radius: 8px; transition: border-color .2s, box-shadow .2s;
}
.search-wrap .form-control:focus {
  border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-mid); outline: none;
}
body.dark .search-wrap .form-control { background: #253044; border-color: var(--border); color: var(--text); }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   OTP TOGGLE
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.otp-toggle-wrap { display: inline-flex; align-items: center; gap: 7px; cursor: pointer; user-select: none; }
.otp-toggle-wrap.locked { opacity: .42; cursor: not-allowed; pointer-events: none; }
.otp-switch { position: relative; width: 38px; height: 21px; flex-shrink: 0; }
.otp-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
.otp-slider { position: absolute; inset: 0; background: #ced4da; border-radius: 21px; transition: background .22s; cursor: pointer; }
.otp-slider::before { content: ''; position: absolute; width: 15px; height: 15px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: transform .22s; box-shadow: 0 1px 4px rgba(0,0,0,.25); }
.otp-switch input:checked + .otp-slider { background: var(--primary); }
.otp-switch input:checked + .otp-slider::before { transform: translateX(17px); }
.otp-switch input:disabled + .otp-slider { cursor: not-allowed; opacity: .6; }
.otp-label { font-size: .68rem; font-weight: 700; white-space: nowrap; }
.otp-label.on  { color: var(--primary); }
.otp-label.off { color: #adb5bd; }
.otp-label.locked-msg { color: var(--warning); font-style: italic; font-weight: 500; }

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   MODALS â€” facultyUI style
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.tl-modal .modal-content { border: none; border-radius: var(--radius); overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,.18); }
.tl-modal .modal-header {
  background: linear-gradient(135deg, var(--primary-dark), var(--primary));
  color: #fff; padding: 1.35rem 1.6rem; border-bottom: none;
  border-radius: var(--radius) var(--radius) 0 0;
}
.tl-modal .modal-title { font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 9px; }
.tl-modal .btn-close { filter: invert(1) brightness(2); opacity: .85; }
.tl-modal .modal-body { padding: 0; background: var(--bg); }
.tl-modal .modal-body-inner { padding: 1.35rem; }
.tl-modal .form-section {
  background: var(--surface); border-radius: 10px;
  padding: 1.05rem 1.3rem; margin-bottom: .85rem;
  border: 1px solid var(--border); box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.tl-modal .form-section:last-of-type { margin-bottom: 0; }
.tl-modal .section-label {
  font-size: .67rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 1px; margin-bottom: .85rem; padding-bottom: .4rem;
  border-bottom: 2px solid; display: flex; align-items: center; gap: 6px;
}
.tl-modal .section-label.green { color: var(--primary); border-bottom-color: var(--primary-light); }
.tl-modal .form-label { font-size: .74rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .4px; }
.tl-modal .form-control, .tl-modal .form-select {
  border: 1.5px solid var(--border); border-radius: 8px; font-size: .9rem;
  padding: .48rem .82rem; transition: border-color .2s, box-shadow .2s;
  background: var(--surface); color: var(--text);
}
.tl-modal .form-control:focus, .tl-modal .form-select:focus {
  border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-mid); outline: none;
}
.tl-modal .input-group-text {
  background: var(--primary-light); border: 1.5px solid var(--border);
  color: var(--primary); font-size: .8rem; font-weight: 600;
  border-radius: 8px 0 0 8px;
}
.tl-modal .input-group .form-control { border-radius: 0 8px 8px 0; }
.tl-modal .modal-footer {
  background: var(--bg); border-top: 1px solid var(--border);
  padding: .85rem 1.3rem; gap: 7px;
}

/* Program list in dept modal */
.prog-list-item {
  display: flex; align-items: center; justify-content: space-between;
  padding: .42rem .65rem; border-radius: 7px; border: 1px solid var(--border);
  background: var(--bg); margin-bottom: .32rem; font-size: .82rem;
}
.prog-code-tag {
  font-size: .67rem; font-weight: 700; color: var(--primary);
  background: var(--primary-light); border: 1px solid rgba(26,158,120,.2);
  border-radius: 5px; padding: .1rem .38rem;
}
.prog-rm-btn {
  border: none; background: none; cursor: pointer; color: #adb5bd;
  font-size: .72rem; padding: .15rem .35rem; border-radius: 5px; transition: all .12s;
}
.prog-rm-btn:hover { background: #fdecea; color: var(--danger); }

/* Role pick cards in assign modal */
.role-pick-card {
  flex: 1; display: flex; align-items: flex-start; gap: .65rem;
  padding: .75rem .85rem; border: 2px solid var(--border);
  border-radius: 10px; cursor: pointer; background: var(--surface);
  transition: border-color .18s, background .18s;
}
.role-pick-card:has(input:checked) { border-color: var(--primary); background: var(--primary-light); }
.rpc-icon { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }
.rpc-label { font-size: .84rem; font-weight: 700; margin-bottom: .1rem; }
.rpc-sub   { font-size: .7rem; color: var(--text-muted); line-height: 1.4; }

/* Auto-pw button */
.auto-pw-btn {
  font-size: .7rem; font-weight: 600; color: var(--primary);
  background: var(--primary-light); border: none; border-radius: 6px;
  padding: .12rem .45rem; cursor: pointer; transition: background .15s;
}
.auto-pw-btn:hover { background: var(--primary); color: #fff; }
.auto-pw-btn.active { background: var(--primary); color:#fff; }

/* Info note */
.info-note {
  background: var(--primary-light); border: 1px solid rgba(26,158,120,.2);
  border-radius: 8px; padding: .65rem 1rem; margin-bottom: 1rem;
  font-size: .8rem; color: var(--primary); display: flex; align-items: center; gap: .5rem;
}

/* Program link section */
.prog-link-section {
  background: var(--bg); border: 1.5px dashed var(--border);
  border-radius: 9px; padding: .75rem 1rem; margin-top: .5rem;
}
.prog-link-section .prog-list-empty { font-size: .78rem; color: #adb5bd; font-style: italic; padding: .2rem 0; }
.dept-image-upload {
  display:grid; grid-template-columns: 88px minmax(0, 1fr); gap: .9rem; align-items: center;
}
.dept-image-preview {
  width: 88px; height: 88px; border-radius: 16px; overflow: hidden;
  border: 1.5px dashed var(--border); background: var(--bg);
  display:flex; align-items:center; justify-content:center;
}
.dept-image-preview img {
  width:100%; height:100%; object-fit:cover; display:block;
}
.dept-image-fallback {
  width:100%; height:100%; display:flex; align-items:center; justify-content:center;
  font-size:1.4rem; font-weight:800; color:#fff;
}
.dept-image-hint {
  font-size:.73rem; color:var(--text-muted); margin-top:.35rem;
}

/* Semester log & info */
.sem-info-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius); box-shadow: var(--shadow); padding: 1.1rem 1.4rem;
}
.sem-info-card h6 { font-size: .88rem; font-weight: 700; display: flex; align-items: center; gap: .5rem; margin-bottom: .75rem; }
.sem-info-card h6 i { color: var(--primary); }
.sem-info-card ul { font-size: .82rem; line-height: 1.8; color: var(--text-muted); padding-left: 1.1rem; }

@media (max-width: 768px) {
  .dept-meta-line { display: flex; }
  .stat-grid { grid-template-columns: 1fr 1fr; gap: .65rem; }
  .stat-card {
    min-height: 122px;
    padding: .82rem .82rem .76rem;
    border-radius: 16px;
  }
  .stat-val { font-size: 1.72rem; }
  .stat-lbl { font-size: .78rem; }
  .stat-foot-note { font-size: .64rem; }
  .dash-analytics-grid { grid-template-columns:1fr; }
  .analytics-chart-wrap { height:260px; }
  .analytics-chart-wrap.compact { height:240px; }
  .dept-grid { grid-template-columns: 1fr; }
  .topnav { padding: 0 1rem; }
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   CALENDAR (matches facultyUI aesthetic)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
.cal-toolbar { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; margin-bottom:1rem; }
.cal-nav-btn {
  width:34px; height:34px; border-radius:10px; border:1.5px solid var(--border);
  background:var(--surface); color:var(--text-muted); cursor:pointer;
  display:inline-flex; align-items:center; justify-content:center;
  transition:all var(--transition);
}
.cal-nav-btn:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-light); }
.cal-month-label { font-size:1.2rem; font-weight:700; min-width:200px; }
.cal-today-btn {
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.4rem .85rem; border-radius:20px;
  border:1.5px solid var(--border); background:var(--surface);
  font-size:.78rem; font-weight:600; color:var(--text-muted); cursor:pointer;
  transition:all var(--transition); font-family:inherit;
}
.cal-today-btn:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-light); }
.cal-legend { display:flex; gap:.65rem; flex-wrap:wrap; margin-left:auto; }
.cal-legend-item { display:inline-flex; align-items:center; gap:.35rem; font-size:.72rem; color:var(--text-muted); font-weight:600; }
.cal-legend-dot { width:10px; height:10px; border-radius:50%; }

.cal-card {
  background:var(--surface); border:1px solid var(--border);
  border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden;
}
.cal-weekdays {
  display:grid; grid-template-columns:repeat(7,1fr);
  background:var(--bg); border-bottom:1px solid var(--border);
}
.cal-weekday {
  text-align:center; padding:.6rem .3rem;
  font-size:.7rem; font-weight:700; color:var(--text-muted);
  text-transform:uppercase; letter-spacing:.8px;
}
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); }
.cal-day {
  min-height:108px; border-right:1px solid var(--border); border-bottom:1px solid var(--border);
  padding:.45rem .5rem; display:flex; flex-direction:column; gap:.3rem;
  cursor:pointer; transition:background var(--transition); position:relative;
}
.cal-day:nth-child(7n) { border-right:none; }
.cal-day:hover { background:var(--primary-light); }
.cal-day.other-month { background:#fafbfc; opacity:.55; }
body.dark .cal-day.other-month { background:rgba(255,255,255,.02); }
.cal-day.today .cal-day-num {
  background:var(--primary); color:#fff;
  width:26px; height:26px; border-radius:50%;
  display:inline-flex; align-items:center; justify-content:center;
}
.cal-day.selected { background:var(--accent-light); box-shadow:inset 0 0 0 2px var(--accent); }
.cal-day-num { font-size:.82rem; font-weight:700; color:var(--text); }
.cal-events { display:flex; flex-direction:column; gap:2px; overflow:hidden; }
.cal-event-pill {
  font-size:.65rem; font-weight:600; padding:2px 6px;
  border-radius:5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  border-left:3px solid;
}
.cal-event-pill.t-quiz       { background:#fdecea; color:#c0392b; border-color:#d93025; }
.cal-event-pill.t-activity   { background:#e8f0fe; color:#1f73db; border-color:#1f73db; }
.cal-event-pill.t-exam       { background:#f3e8ff; color:#7b1fa2; border-color:#7b1fa2; }
.cal-event-pill.t-assignment { background:#fff3cd; color:#b45309; border-color:#f59e0b; }
.cal-event-pill.t-other      { background:#e6f7f2; color:#0d7a5e; border-color:#1a9e78; }
body.dark .cal-event-pill { background:rgba(255,255,255,.06); }
.cal-event-more { font-size:.62rem; color:var(--text-muted); font-weight:700; padding:1px 4px; }

.cal-day-detail {
  background:var(--surface); border:1px solid var(--border);
  border-radius:var(--radius); padding:1rem 1.2rem; margin-top:1rem;
  box-shadow:var(--shadow);
}
.cal-detail-title { font-size:1rem; font-weight:700; margin-bottom:.65rem; display:flex; align-items:center; gap:.5rem; }
.cal-detail-empty { color:var(--text-muted); font-size:.85rem; padding:.5rem 0; }
.cal-detail-item {
  display:flex; align-items:flex-start; gap:.7rem;
  padding:.6rem .75rem; border:1px solid var(--border); border-radius:var(--radius-sm);
  margin-bottom:.4rem; transition:all var(--transition);
}
.cal-detail-item:hover { border-color:var(--primary); box-shadow:var(--shadow); }
.cal-detail-icon {
  width:34px; height:34px; border-radius:9px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center; font-size:.85rem;
}
.cal-detail-meta { flex:1; min-width:0; }
.cal-detail-name { font-weight:600; font-size:.88rem; }
.cal-detail-sub { font-size:.72rem; color:var(--text-muted); margin-top:.15rem; }
.cal-detail-time {
  font-family:'DM Mono',monospace; font-size:.72rem; font-weight:600;
  color:var(--text-muted); padding:.2rem .5rem; border:1px solid var(--border);
  border-radius:6px; flex-shrink:0;
}

@media (max-width:768px) {
  .cal-day { min-height:72px; padding:.3rem; }
  .cal-event-pill { font-size:.58rem; padding:1px 4px; }
}

/* â”€â”€ WIZARD PROGRESS BAR â”€â”€ */
.wiz-steps {
  display:flex; align-items:center; gap:0;
  padding:1.1rem 1.5rem .85rem; background:var(--bg);
  border-bottom:1px solid var(--border);
  position:sticky; top:0; z-index:10;
}
.wiz-step {
  display:flex; flex-direction:column; align-items:center;
  flex:1; position:relative; cursor:default; user-select:none;
}
.wiz-step:not(:last-child)::after {
  content:''; position:absolute;
  top:16px; left:calc(50% + 16px); right:calc(-50% + 16px);
  height:2px; background:var(--border);
  transition:background .35s ease; z-index:0;
}
.wiz-step.done:not(:last-child)::after,
.wiz-step.active:not(:last-child)::after { background:var(--primary); }
.wiz-dot {
  width:32px; height:32px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:.78rem; font-weight:800; flex-shrink:0;
  border:2.5px solid var(--border);
  background:var(--surface); color:var(--text-muted);
  transition:all .28s cubic-bezier(.4,0,.2,1);
  z-index:1; position:relative;
}
.wiz-step.active .wiz-dot {
  border-color:var(--primary); background:var(--primary);
  color:#fff; box-shadow:0 0 0 4px rgba(26,158,120,.18);
  transform:scale(1.08);
}
.wiz-step.done .wiz-dot {
  border-color:var(--primary); background:var(--primary-light);
  color:var(--primary);
}
.wiz-label {
  font-size:.65rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.55px; color:var(--text-muted); margin-top:.38rem;
  text-align:center; line-height:1.25; white-space:nowrap;
}
.wiz-step.active .wiz-label { color:var(--primary); }
.wiz-step.done  .wiz-label  { color:var(--primary-dark); }

/* â”€â”€ WIZARD PANELS â”€â”€ */
.wiz-panel { display:none; animation:tlFadeUp .26s ease both; }
.wiz-panel.active { display:block; }

/* â”€â”€ TYPE CARDS â”€â”€ */
.wiz-type-grid {
  display:grid; grid-template-columns:repeat(3,1fr); gap:.85rem;
  padding:.85rem 0;
}
.wiz-type-card {
  border:2.5px solid var(--border); border-radius:14px;
  padding:1.1rem .85rem .95rem; text-align:center; cursor:pointer;
  background:var(--surface); transition:all .22s cubic-bezier(.4,0,.2,1);
  position:relative; overflow:hidden;
}
.wiz-type-card::before {
  content:''; position:absolute; inset:0;
  background:var(--primary-light); opacity:0;
  transition:opacity .22s ease;
  z-index:0;
}
.wiz-type-card:hover { border-color:var(--primary); transform:translateY(-2px); box-shadow:var(--shadow-md); }
.wiz-type-card:hover::before { opacity:1; }
.wiz-type-card.selected {
  border-color:var(--primary); background:var(--primary-light);
  box-shadow:inset 0 0 0 1px var(--primary), 0 6px 20px rgba(26,158,120,.18);
  transform:translateY(-2px);
}
.wiz-type-card.selected::before { opacity:0; }
.wiz-type-card > * {
  position:relative;
  z-index:1;
}
.wiz-type-card .wiz-check {
  position:absolute; top:.55rem; right:.55rem;
  width:20px; height:20px; border-radius:50%;
  background:var(--primary); color:#fff;
  font-size:.65rem; display:none;
  align-items:center; justify-content:center;
  box-shadow:0 2px 6px rgba(26,158,120,.35);
}
.wiz-type-card.selected .wiz-check { display:flex; }
.wiz-type-icon {
  width:48px; height:48px; border-radius:14px;
  display:flex; align-items:center; justify-content:center;
  font-size:1.2rem; margin:0 auto .65rem;
  transition:transform .22s ease;
}
.wiz-type-card:hover .wiz-type-icon,
.wiz-type-card.selected .wiz-type-icon { transform:scale(1.1); }
.wiz-type-name { font-size:.9rem; font-weight:800; margin-bottom:.2rem; }
.wiz-type-desc { font-size:.72rem; color:var(--text-muted); line-height:1.4; }
.wiz-type-card:hover .wiz-type-name,
.wiz-type-card.selected .wiz-type-name { color:var(--text) !important; }
.wiz-type-card:hover .wiz-type-desc,
.wiz-type-card.selected .wiz-type-desc { color:var(--text-muted); }

/* â”€â”€ REVIEW PANEL â”€â”€ */
.review-row {
  display:flex; align-items:flex-start; gap:.65rem;
  padding:.52rem .75rem; border-radius:8px;
  border:1px solid var(--border); background:var(--bg);
  margin-bottom:.38rem; font-size:.83rem;
}
.review-label {
  font-size:.64rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.5px; color:var(--text-muted); min-width:90px; flex-shrink:0;
  padding-top:.12rem;
}
.review-val { font-weight:600; color:var(--text); flex:1; word-break:break-all; }

#wizReviewContent {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .42rem .52rem;
}
#wizReviewContent .review-row {
  margin-bottom: 0;
  min-height: 48px;
}
@media (max-width: 768px) {
  #wizReviewContent {
    grid-template-columns: 1fr;
  }
}

/* â”€â”€ WIZARD FOOTER NAV â”€â”€ */
.wiz-footer {
  display:flex; align-items:center; justify-content:space-between;
  gap:.65rem; padding:.85rem 1.3rem;
  background:var(--bg); border-top:1px solid var(--border);
}
.wiz-footer-right { display:flex; gap:.5rem; }
.btn-wiz-back {
  display:inline-flex; align-items:center; gap:.38rem;
  padding:.52rem 1.1rem; border-radius:10px;
  border:1.5px solid var(--border); background:var(--surface);
  font-size:.85rem; font-weight:700; color:var(--text);
  cursor:pointer; transition:all .18s; font-family:inherit;
}
.btn-wiz-back:hover {
  border-color:var(--primary); color:var(--primary); 
  background:var(--primary-light);
}
.btn-wiz-back:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-light); }
.btn-wiz-next {
  display:inline-flex; align-items:center; gap:.38rem;
  padding:.52rem 1.3rem; border-radius:10px;
  border:none; background:var(--primary); color:#fff;
  font-size:.85rem; font-weight:700; cursor:pointer;
  transition:all .18s; font-family:inherit;
  box-shadow:0 4px 12px rgba(26,158,120,.25);
}
.btn-wiz-next:hover { background:var(--primary-dark); transform:translateY(-1px); box-shadow:0 6px 18px rgba(26,158,120,.32); }
.btn-wiz-next:disabled { opacity:.45; cursor:not-allowed; transform:none; box-shadow:none; }

/* â”€â”€ ACCT TABLE EMAIL COL â”€â”€ */
.acct-email-cell {
  font-size:.76rem; color:var(--text-muted); display:flex;
  align-items:center; gap:.3rem;
}
.acct-email-cell i { font-size:.65rem; opacity:.55; flex-shrink:0; }




/* â•â• ACCOUNT MANAGEMENT v2 â•â• */

/* Toolbar row â€” single line: [search] [Add Account] [Filter] */
.acct-toolbar {
  display:flex; align-items:center; gap:.65rem;
  flex-wrap:nowrap; margin-bottom:1rem;
}
.acct-toolbar .search-wrap {
  flex:1; min-width:0;
}
.acct-select-cancel {
  display:none;
  align-items:center;
  gap:.35rem;
  flex-shrink:0;
  border-radius:10px;
  border:0 solid transparent;
  background:transparent;
  color:var(--text-muted);
  font-size:.8rem;
  font-weight:700;
  padding:.52rem 0;
  max-width:0;
  opacity:0;
  transform:translateY(-3px);
  overflow:hidden;
  pointer-events:none;
  cursor:pointer;
  font-family:inherit;
  white-space:nowrap;
  transition:max-width .28s cubic-bezier(.16,1,.3,1), opacity .2s ease, transform .24s ease, padding .24s ease, border-color .24s ease, background .24s ease;
}
#section-accounts.acct-select-mode .acct-select-cancel {
  display:inline-flex;
  border:1.5px solid var(--border);
  background:var(--surface);
  max-width:140px;
  opacity:1;
  transform:translateY(0);
  padding:.52rem .85rem;
  pointer-events:auto;
}
#section-accounts .acct-filter-cancel {
  display:none;
  align-items:center;
  gap:.35rem;
  flex-shrink:0;
  border-radius:10px;
  color:var(--text-muted);
  font-size:.8rem;
  font-weight:700;
  cursor:pointer;
  font-family:inherit;
  white-space:nowrap;
  max-width:0;
  opacity:0;
  transform:translateY(-3px);
  padding:.52rem 0;
  border:0 solid transparent;
  background:transparent;
  overflow:hidden;
  pointer-events:none;
  transition:max-width .28s cubic-bezier(.16,1,.3,1), opacity .2s ease, transform .24s ease, padding .24s ease, border-color .24s ease, background .24s ease;
}
#section-accounts.filter-open .acct-filter-cancel {
  display:inline-flex;
  border:1.5px solid var(--border);
  background:var(--surface);
  max-width:140px;
  opacity:1;
  transform:translateY(0);
  padding:.52rem .85rem;
  pointer-events:auto;
}
#section-accounts.acct-select-mode .acct-filter-cancel {
  max-width:0 !important;
  opacity:0 !important;
  padding:.52rem 0 !important;
  border:0 solid transparent !important;
  pointer-events:none !important;
}
#section-departments .acct-filter-cancel {
  display:none;
  align-items:center;
  gap:.35rem;
  flex-shrink:0;
  border-radius:10px;
  color:var(--text-muted);
  font-size:.8rem;
  font-weight:700;
  cursor:pointer;
  font-family:inherit;
  white-space:nowrap;
  max-width:0;
  opacity:0;
  transform:translateY(-3px);
  padding:.52rem 0;
  border:0 solid transparent;
  background:transparent;
  overflow:hidden;
  pointer-events:none;
  transition:max-width .28s cubic-bezier(.16,1,.3,1), opacity .2s ease, transform .24s ease, padding .24s ease, border-color .24s ease, background .24s ease;
}
#section-programs .acct-filter-cancel {
  display:none;
  align-items:center;
  gap:.35rem;
  flex-shrink:0;
  border-radius:10px;
  color:var(--text-muted);
  font-size:.8rem;
  font-weight:700;
  cursor:pointer;
  font-family:inherit;
  white-space:nowrap;
  max-width:0;
  opacity:0;
  transform:translateY(-3px);
  padding:.52rem 0;
  border:0 solid transparent;
  background:transparent;
  overflow:hidden;
  pointer-events:none;
  transition:max-width .28s cubic-bezier(.16,1,.3,1), opacity .2s ease, transform .24s ease, padding .24s ease, border-color .24s ease, background .24s ease;
}
#section-programs.filter-open .acct-filter-cancel {
  display:inline-flex;
  border:1.5px solid var(--border);
  background:var(--surface);
  max-width:140px;
  opacity:1;
  transform:translateY(0);
  padding:.52rem .85rem;
  pointer-events:auto;
}
#section-programs.prog-select-mode .acct-filter-cancel {
  max-width:0 !important;
  opacity:0 !important;
  padding:.52rem 0 !important;
  border:0 solid transparent !important;
  pointer-events:none !important;
}
#section-departments.filter-open .acct-filter-cancel {
  display:inline-flex;
  border:1.5px solid var(--border);
  background:var(--surface);
  max-width:140px;
  opacity:1;
  transform:translateY(0);
  padding:.52rem .85rem;
  pointer-events:auto;
}
#section-departments.dept-select-mode .acct-filter-cancel {
  max-width:0 !important;
  opacity:0 !important;
  padding:.52rem 0 !important;
  border:0 solid transparent !important;
  pointer-events:none !important;
}
#section-departments .acct-select-cancel,
#section-programs .acct-select-cancel { display:none; }
#section-departments.dept-select-mode .acct-select-cancel,
#section-programs.prog-select-mode .acct-select-cancel {
  display:inline-flex; align-items:center; gap:.35rem;
}
.acct-ctrl-wrap { position:relative; flex-shrink:0; }
.acct-ctrl-btn {
  flex-shrink:0;
  width:46px;
  height:42px;
  border-radius:12px;
  border:1.5px solid var(--border);
  background:var(--surface);
  color:var(--text-muted);
  display:inline-flex;
  align-items:center;
  justify-content:center;
  cursor:pointer;
  transition:all .18s;
  font-size:.95rem;
  position:relative;
}
.acct-ctrl-btn:hover,
.acct-ctrl-btn.active {
  border-color:var(--primary);
  color:var(--primary);
  background:var(--primary-light);
}
.acct-ctrl-menu {
  position:absolute; top:calc(100% + 8px); left:0;
  min-width:242px; background:var(--surface);
  border:1.5px solid var(--border); border-radius:14px;
  box-shadow:0 10px 26px rgba(0,0,0,.18);
  z-index:350; overflow:hidden;
  opacity:0;
  transform:translateY(-6px) scale(.98);
  transform-origin:top left;
  visibility:hidden;
  pointer-events:none;
  transition:opacity .18s ease, transform .22s cubic-bezier(.16,1,.3,1), visibility 0s linear .22s;
}
.acct-ctrl-menu.open {
  opacity:1;
  transform:translateY(0) scale(1);
  visibility:visible;
  pointer-events:auto;
  transition:opacity .18s ease, transform .22s cubic-bezier(.16,1,.3,1), visibility 0s;
}
.acct-ctrl-item {
  width:100%; border:none; background:none; text-align:left;
  display:flex; align-items:center; gap:.6rem;
  padding:.74rem 1rem; font-size:.92rem; font-weight:700;
  color:var(--text); cursor:pointer; font-family:inherit;
}
.acct-ctrl-item i { width:14px; text-align:center; opacity:.72; }
.acct-ctrl-item + .acct-ctrl-item { border-top:1px solid var(--border); }
.acct-ctrl-item:hover { background:var(--primary-light); color:var(--primary); }
.acct-ctrl-item.active { background:rgba(26,158,120,.18); color:var(--primary); }
.acct-toolbar .btn,
.filter-toggle-btn,
.acct-ctrl-btn {
  flex-shrink:0; white-space:nowrap;
}

/* Filter toggle button */
.filter-toggle-btn {
  display:inline-flex; align-items:center; gap:.45rem;
  justify-content:center;
  width:62px; height:52px; padding:0; border-radius:14px;
  border:1.5px solid var(--border); background:var(--surface);
  font-size:.8rem; font-weight:700; color:var(--text-muted);
  cursor:pointer; white-space:nowrap; font-family:inherit;
  transition:all .18s; position:relative; flex-shrink:0;
}
.filter-toggle-btn:hover,
.filter-toggle-btn.active {
  border-color:var(--primary); color:var(--primary);
  background:var(--primary-light);
}
.filter-toggle-btn .fbadge,
.acct-ctrl-btn .fbadge {
  display:none; position:absolute; top:-6px; right:-6px;
  width:16px; height:16px; border-radius:50%;
  background:var(--primary); color:#fff;
  font-size:.6rem; font-weight:800;
  align-items:center; justify-content:center;
}
.filter-toggle-btn.has-filter .fbadge,
.acct-ctrl-btn.has-filter .fbadge { display:flex; }

/* Filter panel (slides from right) */
.acct-filter-panel {
  position:fixed; top:var(--nav-h); right:0;
  width:280px; height:calc(100vh - var(--nav-h) - var(--footer-h));
  background:var(--surface); border-left:1px solid var(--border);
  box-shadow:-8px 0 32px rgba(0,0,0,.10);
  z-index:300; padding:1.1rem;
  transform:translateX(105%);
  opacity:0;
  pointer-events:none;
  transition:transform .38s cubic-bezier(.16,1,.3,1), opacity .22s ease;
  overflow-y:auto; display:flex; flex-direction:column; gap:.85rem;
}
.acct-filter-panel.open { transform:translateX(0); opacity:1; pointer-events:auto; }

/* Account Management / Dean Accounts: left filter panel + compressed table layout */
#section-accounts .acct-content-layout,
#section-deans .acct-content-layout,
#section-programs .acct-content-layout {
  display:block;
}
#section-accounts.filter-open .acct-content-layout,
#section-deans.filter-open #panelAccounts .acct-content-layout,
#section-departments.filter-open .acct-content-layout,
#section-programs.filter-open .acct-content-layout {
  display:grid;
  grid-template-columns: 420px minmax(0, 1fr);
  gap:1rem;
  align-items:stretch;
}
#section-accounts .acct-content-main,
#section-deans .acct-content-main,
#section-departments .acct-content-main,
#section-programs .acct-content-main {
  min-width:0;
}
#section-accounts .acct-filter-panel,
#section-deans .acct-filter-panel,
#section-departments .acct-filter-panel,
#section-programs .acct-filter-panel {
  display:none;
}
#section-accounts.filter-open .acct-filter-panel,
#section-deans.filter-open #panelAccounts .acct-filter-panel,
#section-departments.filter-open .acct-filter-panel,
#section-programs.filter-open .acct-filter-panel {
  position:relative;
  top:auto;
  right:auto;
  width:auto;
  height:auto;
  max-height:none;
  border:1.5px solid var(--border);
  border-radius:16px;
  box-shadow:none;
  padding:1rem;
  transform:none;
  opacity:1;
  pointer-events:auto;
  z-index:1;
  overflow:visible;
  display:flex;
  animation: acctPanelFadeIn .26s ease both;
}
/* Departments: explicit 20/80 split when filter panel is open */
#section-departments.filter-open .acct-content-layout {
  grid-template-columns: 20% 80%;
}
#section-programs.filter-open .acct-content-layout {
  grid-template-columns: 20% 80%;
}
/* Keep dept table compressed and prevent horizontal overflow in 80% pane */
#section-departments.filter-open .acct-table-wrap {
  overflow-x: hidden;
}
#section-programs.filter-open .acct-table-wrap {
  overflow-x: hidden;
}
#section-departments.filter-open #deptTable {
  min-width: 0 !important;
  width: 100% !important;
  table-layout: fixed;
}
#section-departments.filter-open #deptTable th,
#section-departments.filter-open #deptTable td {
  min-width: 0 !important;
}
#section-programs.filter-open #progTable {
  min-width: 0 !important;
  width: 100% !important;
  table-layout: fixed;
}
#section-programs.filter-open #progTable th,
#section-programs.filter-open #progTable td {
  min-width: 0 !important;
}
@keyframes acctPanelFadeIn {
  from { opacity:0; transform:translateX(-8px); }
  to { opacity:1; transform:translateX(0); }
}
.afp-title {
  font-size:.72rem; font-weight:800; text-transform:uppercase;
  letter-spacing:1px; color:var(--text-muted);
  display:flex; align-items:center; justify-content:space-between;
  padding-bottom:.6rem; border-bottom:1px solid var(--border);
}
.afp-close {
  border:none; background:none; cursor:pointer; color:var(--text-muted);
  font-size:.95rem; padding:.18rem .35rem; border-radius:6px; transition:all .14s;
}
.afp-close:hover { background:var(--border); color:var(--text); }
/* Account filter close should look like cancel action */
#section-accounts .afp-close {
  display:inline-flex;
  align-items:center;
  gap:.35rem;
  border-radius:10px;
  border:1.5px solid var(--border);
  background:var(--surface);
  color:var(--text-muted);
  font-size:.8rem;
  font-weight:700;
  padding:.44rem .75rem;
  font-family:inherit;
}
#section-accounts .afp-close:hover {
  border-color:var(--primary);
  color:var(--primary);
  background:var(--primary-light);
}
.afp-group { display:flex; flex-direction:column; gap:.4rem; }
.afp-label {
  font-size:.68rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.8px; color:var(--text-muted); margin-bottom:.1rem;
}
.afp-chips { display:flex; flex-wrap:wrap; gap:.35rem; }
.afp-chip {
  padding:.32rem .75rem; border-radius:20px;
  border:1.5px solid var(--border); background:var(--surface);
  font-size:.74rem; font-weight:600; color:var(--text-muted);
  cursor:pointer; transition:all .14s; font-family:inherit;
}
.afp-chip:hover,
.afp-chip.active {
  border-color:var(--primary); background:var(--primary-light); color:var(--primary);
}
.afp-reset {
  margin-top:auto; padding:.52rem; border-radius:9px;
  border:1.5px solid var(--border); background:var(--surface);
  font-size:.78rem; font-weight:700; color:var(--text-muted);
  cursor:pointer; width:100%; font-family:inherit;
  transition:all .16s;
}
.afp-reset:hover { border-color:var(--danger); color:var(--danger); background:#fdecea; }



/* Bulk action bar */
.bulk-action-bar {
  display:none; align-items:center; gap:.45rem;
  padding:.5rem .75rem; border-radius:11px;
  background:linear-gradient(135deg,var(--primary-dark),var(--primary));
  color:#fff; margin-bottom:.6rem;
  animation:tlFadeUp .22s ease;
}
.bulk-action-bar.show { display:flex; }
.bulk-count {
  font-size:.76rem; font-weight:700;
  background:rgba(255,255,255,.2); border-radius:16px;
  padding:.14rem .58rem; line-height:1.1;
}
.bulk-action-bar .bulk-btns { display:flex; gap:.32rem; margin-left:auto; flex-wrap:wrap; justify-content:flex-end; }
.bulk-btn {
  display:inline-flex; align-items:center; gap:.3rem;
  padding:.28rem .58rem; border-radius:8px;
  border:1.5px solid rgba(255,255,255,.4); background:rgba(255,255,255,.12);
  color:#fff; font-size:.72rem; font-weight:700;
  cursor:pointer; font-family:inherit; transition:all .16s;
  line-height:1.05;
}
.bulk-btn:hover { background:rgba(255,255,255,.25); }
.bulk-btn.danger:hover { background:#d93025; border-color:#d93025; }
.bulk-desel {
  border:none; background:none; color:rgba(255,255,255,.7);
  font-size:.8rem; cursor:pointer; padding:.14rem .28rem;
  border-radius:6px; transition:all .14s;
}
.bulk-desel:hover { background:rgba(255,255,255,.15); color:#fff; }

/* Table with checkboxes */
#acctTable thead th:first-child { width:42px; text-align:center; }
.acct-cb {
  appearance:none; -webkit-appearance:none;
  width:18px; height:18px; border-radius:50%;
  border:2px solid #8d98ab; background:#fff; cursor:pointer;
  display:inline-block; vertical-align:middle;
  transition:all .14s ease; box-shadow:inset 0 0 0 2px #fff;
}
.acct-cb:hover { border-color:var(--text-muted); }
.acct-cb:checked {
  border-color:#0b1220; background-color:#0b1220;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='white' d='M4.7 9.4L1.6 6.3l1-1 2.1 2.1 4.7-4.7 1 1z'/%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:center; box-shadow:none;
}
.acct-cb:indeterminate {
  border-radius:6px; border-color:var(--primary); background-color:var(--primary);
  background-image:linear-gradient(#fff,#fff); background-repeat:no-repeat;
  background-size:9px 2px; background-position:center;
}
body.dark .acct-cb { background:#1d2636; border-color:#607089; box-shadow:inset 0 0 0 2px #1d2636; }
body.dark .acct-cb:checked {
  border-color:#fff; background-color:#fff;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%230b1220' d='M4.7 9.4L1.6 6.3l1-1 2.1 2.1 4.7-4.7 1 1z'/%3E%3C/svg%3E");
}
#section-accounts .acct-select-col {
  width:0 !important;
  min-width:0 !important;
  max-width:0 !important;
  padding-left:0 !important;
  padding-right:0 !important;
  opacity:0;
  overflow:hidden;
  pointer-events:none;
  transition:width .26s cubic-bezier(.16,1,.3,1), min-width .26s cubic-bezier(.16,1,.3,1), max-width .26s cubic-bezier(.16,1,.3,1), padding .2s ease, opacity .2s ease;
}
#section-accounts.acct-select-mode .acct-select-col {
  width:42px !important;
  min-width:42px !important;
  max-width:42px !important;
  padding-left:.65rem !important;
  padding-right:.55rem !important;
  opacity:1;
  pointer-events:auto;
}
#section-accounts .acct-select-col .acct-cb {
  transform:scale(.9);
  opacity:0;
  transition:transform .2s ease, opacity .2s ease;
}
#section-accounts.acct-select-mode .acct-select-col .acct-cb {
  transform:scale(1);
  opacity:1;
}
#section-accounts:not(.acct-select-mode) #acctBulkBar { display:none !important; }
#section-departments .dept-select-col { display:none; }
#section-departments.dept-select-mode .dept-select-col { display:table-cell; }
#section-departments:not(.dept-select-mode) #deptBulkBar { display:none !important; }
#section-programs .prog-select-col { display:none; }
#section-programs.prog-select-mode .prog-select-col { display:table-cell; }
#section-programs:not(.prog-select-mode) #progBulkBar { display:none !important; }

/* 3-dot menu */
.dot-menu-wrap { position:relative; display:inline-block; }
.dot-menu-btn {
  width:28px; height:28px; border-radius:6px; border:none;
  background:none; cursor:pointer; color:var(--text-muted);
  display:flex; align-items:center; justify-content:center;
  font-size:.8rem; transition:all .14s; margin:0 auto;
}
.dot-menu-btn:hover { background:var(--border); color:var(--text); }
.dot-menu-btn.open { background:var(--primary-light); color:var(--primary); }

#acctTable td:last-child,
#acctTable th:last-child {
  padding-left: 6px;
  padding-right: 10px;
  text-align: center;
  white-space: nowrap;
}

.dot-menu {
  position:absolute; right:0; top:calc(100% + 6px);
  background:var(--surface); border:1.5px solid var(--border);
  border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,.14);
  min-width:160px; z-index:500; overflow:hidden;
  transform-origin:top right;
  transform:scale(.85) translateY(-8px); opacity:0;
  transition:transform .2s cubic-bezier(.16,1,.3,1), opacity .15s ease;
  pointer-events:none;
}
.dot-menu.open {
  transform:scale(1) translateY(0); opacity:1; pointer-events:all;
}
.dot-menu-item {
  display:flex; align-items:center; gap:.65rem;
  padding:.6rem .95rem; font-size:.82rem; font-weight:600;
  color:var(--text); cursor:pointer; border:none; background:none;
  width:100%; font-family:inherit; text-align:left;
  transition:background .12s; white-space:nowrap;
}
.dot-menu-item i { width:14px; text-align:center; font-size:.78rem; opacity:.65; }
.dot-menu-item:hover { background:var(--primary-light); color:var(--primary); }
.dot-menu-item:hover i { opacity:1; }
.dot-menu-item.danger { color:var(--danger); }
.dot-menu-item.danger:hover { background:#fdecea; color:var(--danger); }
.dot-menu-divider { height:1px; background:var(--border); margin:.2rem 0; }

/* Row selected highlight */

#deptGrid tr.row-selected { background:var(--primary-light) !important; }
#deptTable .btn-dots { width:28px; height:28px; border-radius:6px; margin:0 auto; }

/* â•â• ACCOUNT MANAGEMENT â€” MOBILE RESPONSIVE â•â• */

@media (max-width: 768px) {
  .dept-meta-line { display: flex; }

  /* Toolbar: stack search full-width, buttons row below */
  .acct-toolbar {
    flex-wrap: wrap;
    gap: .5rem;
  }
  /* tablet 481â€“768: keep one row, just shrink */
  .acct-toolbar { flex-wrap:nowrap; gap:.5rem; }
  .acct-toolbar .search-wrap { flex:1; min-width:0; }
  .acct-toolbar .btn { font-size:.78rem; padding:.48rem .85rem; }
  .filter-toggle-btn { padding:.48rem .75rem; font-size:.78rem; }

  /* Filter panel: full-width bottom sheet on mobile */
  .acct-filter-panel {
    top: auto;
    bottom: var(--footer-h);
    left: 0;
    right: 0;
    width: 100%;
    height: auto;
    max-height: 65vh;
    border-left: none;
    border-top: 1.5px solid var(--border);
    border-radius: 18px 18px 0 0;
    box-shadow: 0 -8px 32px rgba(0,0,0,.14);
    transform: translateY(110%);
    transition: transform .3s cubic-bezier(.16,1,.3,1);
    flex-direction: column;
    gap: .75rem;
  }
  .acct-filter-panel.open {
    transform: translateY(0);
  }
  #section-accounts.filter-open .acct-content-layout,
  #section-deans.filter-open #panelAccounts .acct-content-layout,
  #section-departments.filter-open .acct-content-layout,
  #section-programs.filter-open .acct-content-layout { display:block; }
  #section-accounts.filter-open .acct-filter-panel,
  #section-deans.filter-open #panelAccounts .acct-filter-panel,
  #section-departments.filter-open .acct-filter-panel,
  #section-programs.filter-open .acct-filter-panel {
    position:fixed;
    top:auto;
    right:0;
    left:0;
    bottom:var(--footer-h);
    width:100%;
    height:auto;
    max-height:65vh;
    border-left:none;
    border-top:1.5px solid var(--border);
    border-radius:18px 18px 0 0;
    box-shadow:0 -8px 32px rgba(0,0,0,.14);
    transform: translateY(0);
    z-index:300;
  }
  /* Drag handle indicator */
  .acct-filter-panel::before {
    content: '';
    display: block;
    width: 36px; height: 4px;
    border-radius: 4px;
    background: var(--border);
    margin: 0 auto -.2rem;
    flex-shrink: 0;
  }
  .afp-chips { gap: .4rem; }
  .afp-chip  { font-size: .76rem; padding: .36rem .8rem; }

  /* Bulk bar: stack on mobile */
  .bulk-action-bar {
    flex-wrap: wrap;
    gap: .5rem;
    padding: .6rem .85rem;
    border-radius: 10px;
  }
  .bulk-action-bar .bulk-btns {
    width: 100%;
    margin-left: 0;
    justify-content: flex-start;
  }
  .bulk-btn { font-size: .72rem; padding: .34rem .65rem; }
  .bulk-count { font-size: .76rem; }

  /* Table: hide less-critical columns, scroll horizontally */
  #acctTable { font-size: .76rem; }
  #acctTable thead th,
  #acctTable tbody td { padding: .45rem .55rem; }

  /* Hide department column on small screens */
  #acctTable th:nth-child(5),
  #acctTable td:nth-child(5) { display: none; }

  /* Name cell: show email as sub-line instead */
  .acct-name-cell .acct-sub-email {
    display: block;
    font-size: .7rem;
    color: var(--text-muted);
    margin-top: 2px;
  }

  /* Dot menu: open upward if near bottom */
  .dot-menu {
    right: 0;
    left: auto;
    min-width: 145px;
  }

  /* OTP column label hidden, just show toggle */
  #acctTable th:nth-child(7) { font-size: 0; }
  #acctTable th:nth-child(7)::after { content: 'OTP'; font-size: .68rem; }

  /* Status pill smaller */
  .pill-active, .pill-inactive { font-size: .62rem; padding: .15em .45em; }
}

@media (max-width: 480px) {
  /* Extra small: also hide OTP column */
  #acctTable th:nth-child(7),
  #acctTable td:nth-child(7) { display: none; }

  /* Tighten type badge */
  #acctTable th:nth-child(4),
  #acctTable td:nth-child(4) { width: 70px; }

  #addAcctBtn span { display: none; }
  #addAcctBtn i { margin: 0 !important; }
  #addAcctBtn { padding: .45rem .75rem !important; }
}

/* â•â• STICKY LAST COLUMN + SCROLL WRAPPER â•â• */
.acct-table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-gutter: auto;
  border-radius: var(--radius);
  border: 1px solid var(--border);
  box-shadow: var(--shadow);
  background: var(--surface);
  width: 100%;
  display: block;
  overflow-y: visible;
}

.acct-table-wrap.acct-rows-scroll {
  max-height: min(42vh, 390px);
  overflow-y: auto;
}

#acctTable { min-width: 580px; width: 100%; table-layout: auto; }
#deptTable  { min-width: 680px; width: 100%; table-layout: auto; }
#progTable  { min-width: 500px; width: 100%; table-layout: auto; }

/* When account filter panel is open, force table compression (no horizontal scroll) */
#section-accounts.filter-open .acct-table-wrap {
  overflow-x: hidden;
}
#section-accounts.filter-open #acctTable {
  min-width: 0 !important;
  width: 100% !important;
  table-layout: fixed;
}
#section-accounts.filter-open #acctTable th,
#section-accounts.filter-open #acctTable td {
  min-width: 0 !important;
}
#section-accounts.filter-open #acctTable th:nth-child(1),
#section-accounts.filter-open #acctTable td:nth-child(1) { width: 34px; }
#section-accounts.filter-open #acctTable th:nth-child(2),
#section-accounts.filter-open #acctTable td:nth-child(2) { width: 23%; }
#section-accounts.filter-open #acctTable th:nth-child(3),
#section-accounts.filter-open #acctTable td:nth-child(3) { width: 96px; }
#section-accounts.filter-open #acctTable th:nth-child(4),
#section-accounts.filter-open #acctTable td:nth-child(4) { width: 86px; }
#section-accounts.filter-open #acctTable th:nth-child(6),
#section-accounts.filter-open #acctTable td:nth-child(6) { width: 86px; }
#section-accounts.filter-open #acctTable th:nth-child(8),
#section-accounts.filter-open #acctTable td:nth-child(8) { width: 44px; }
#section-accounts.filter-open #acctTable th:nth-child(5),
#section-accounts.filter-open #acctTable td:nth-child(5),
#section-accounts.filter-open #acctTable th:nth-child(7),
#section-accounts.filter-open #acctTable td:nth-child(7) {
  display:none;
}
#section-accounts.filter-open #acctTable td:nth-child(5),
#section-accounts.filter-open #acctTable td:nth-child(2) {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}


/* Fade hint removed â€” was clipping dot-menu column on mobile */


body.dark #acctTable td:last-child  { background: var(--surface); }
body.dark #acctTable thead th:last-child { background: #1c2a3a; }

/* Dot menu opens upward on mobile so it's never clipped */
@media (max-width: 768px) {
  .dept-meta-line { display: flex; }
  #acctTable { min-width: unset !important; width: 100% !important; }
  .acct-table-wrap { overflow-x: hidden; overflow-y: visible; }

  /* Hide department column */
  #acctTable th:nth-child(5),
  #acctTable td:nth-child(5) { display: none; }

  /* Hide ID No. col on small screens â€” name cell already shows it */
  #acctTable th:nth-child(3),
  #acctTable td:nth-child(3) { display: none; }

  /* Force dots column visible */
  #acctTable th:last-child,
  #acctTable td:last-child {
    display: table-cell !important;
    width: 44px !important;
    min-width: 44px !important;
    max-width: 44px !important;
    padding: 0 4px !important;
    overflow: visible !important;
    position: relative !important;
  }

  .dot-menu {
    position: fixed !important;
    top: auto !important;
    bottom: 90px !important;
    right: 8px !important;
    left: auto !important;
    transform-origin: bottom right;
    min-width: 160px;
    z-index: 99999 !important;
    box-shadow: 0 8px 32px rgba(0,0,0,.22);
  }
  .dot-menu.open { transform: scale(1) translateY(0); opacity: 1; pointer-events: all; }
}

@media (max-width: 480px) {
  #acctTable th:nth-child(7),
  #acctTable td:nth-child(7) { display: none; }
  .acct-toolbar .btn span { display: none; }
  .acct-toolbar .btn i { margin: 0 !important; }
}



/* â•â• PAGINATION â•â• */
.acct-pagination {
  display:flex; align-items:center; justify-content:space-between;
  flex-wrap:wrap; gap:.65rem;
  padding:.75rem 1rem;
  background:var(--surface);
  border-top:1px solid var(--border);
  border-radius:0 0 var(--radius) var(--radius);
}
.apg-info {
  font-size:.76rem; color:var(--text-muted); font-weight:600;
}
.apg-controls {
  display:flex; align-items:center; gap:.35rem; flex-wrap:wrap;
}
.apg-size-sel {
  font-size:.74rem; border:1.5px solid var(--border);
  border-radius:8px; padding:.3rem .6rem;
  background:var(--surface); color:var(--text);
  cursor:pointer; outline:none; font-family:inherit;
  transition:border-color .15s;
}
.apg-size-sel:focus { border-color:var(--primary); }
.apg-btn {
  width:30px; height:30px; border-radius:8px;
  border:1.5px solid var(--border); background:var(--surface);
  color:var(--text-muted); font-size:.72rem; cursor:pointer;
  display:inline-flex; align-items:center; justify-content:center;
  transition:all .15s; font-family:inherit;
}
.apg-btn:hover:not(:disabled) {
  border-color:var(--primary); color:var(--primary);
  background:var(--primary-light);
}
.apg-btn:disabled { opacity:.35; cursor:not-allowed; }
.apg-pages { display:flex; gap:.25rem; }
.apg-page-btn {
  min-width:30px; height:30px; padding:0 .45rem;
  border-radius:8px; border:1.5px solid var(--border);
  background:var(--surface); color:var(--text-muted);
  font-size:.76rem; font-weight:600; cursor:pointer;
  display:inline-flex; align-items:center; justify-content:center;
  transition:all .15s; font-family:inherit;
}
.apg-page-btn:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-light); }
.apg-page-btn.active {
  background:var(--primary); border-color:var(--primary);
  color:#fff; font-weight:700;
}
.apg-ellipsis {
  min-width:30px; height:30px; display:inline-flex;
  align-items:center; justify-content:center;
  font-size:.76rem; color:var(--text-muted);
}

@media (max-width:480px) {
  .acct-pagination { flex-direction:column; align-items:flex-start; gap:.5rem; }
  .apg-controls { width:100%; justify-content:space-between; }
}

/* â•â• DEPT TABLE â€” RESPONSIVE (matches acct/prog pattern) â•â• */
@media (max-width: 768px) {
  .dept-meta-line { display: flex; }
  #deptTable { font-size: .76rem; min-width: unset !important; width: 100% !important; }
  #deptTable thead th, #deptTable tbody td { padding: .42rem .5rem; }

  /* Hide Dean, Secretary, Programs, Status columns â€” shown inline in name cell */
    #deptTable th:nth-child(5), #deptTable td:nth-child(5),
  #deptTable th:nth-child(6), #deptTable td:nth-child(6),
  #deptTable th:nth-child(7), #deptTable td:nth-child(7),
  #deptTable th:nth-child(8), #deptTable td:nth-child(8) { display: none; }

    #deptTable td:nth-child(4) { max-width: unset; white-space: normal; }

  /* Force actions col visible */
  #deptTable th:last-child, #deptTable td:last-child {
    display: table-cell !important;
    width: 44px !important; min-width: 44px !important;
    padding: 0 4px !important;
    overflow: visible !important; position: relative !important;
  }
}
@media (max-width: 480px) {
  /* Hide Code col on extra small â€” code badge already in name cell sub-line */
    #deptTable th:nth-child(3), #deptTable td:nth-child(3) { display: none; }
}

/* â•â• YS TABLES â€” RESPONSIVE â•â• */
#section-academic .acad-sub { max-width: 780px; }
.ys-config-card,
.ys-summary-card {
  padding: 0;
  overflow: hidden;
}
.ys-open-btn {
  display:inline-flex; align-items:center; gap:.45rem;
  padding:.6rem 1rem; border-radius:12px; border:none;
  background:var(--primary); color:#fff; font-size:.84rem; font-weight:800;
  font-family:inherit; box-shadow:0 8px 18px rgba(26,158,120,.18);
  transition:all var(--transition); white-space:nowrap;
}
.ys-open-btn:hover { background:var(--primary-dark); transform:translateY(-1px); color:#fff; }
.ys-card-head {
  display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;
  padding:1.25rem 1.35rem .9rem;
  border-bottom:1px solid var(--border);
  background:linear-gradient(180deg, rgba(26,158,120,.04), transparent);
}
.ys-card-head-main { min-width:0; }
.ys-card-title {
  display:flex; align-items:center; gap:.65rem;
  font-size:1rem; font-weight:800; color:var(--text);
}
.ys-card-title i { color:var(--primary); }
.ys-card-subtitle {
  margin-top:.3rem; font-size:.8rem; color:var(--text-muted);
}
.ys-card-body { padding:1.2rem 1.35rem 1.25rem; }
.ys-form-grid {
  display:grid; grid-template-columns:1.2fr 1.6fr .9fr .55fr auto;
  gap:.85rem; align-items:end;
}
.ys-field label {
  display:block; font-size:.74rem; font-weight:700; color:var(--text-muted);
  text-transform:uppercase; letter-spacing:.5px; margin-bottom:.38rem;
}
.ys-field .form-select,
.ys-field .form-control {
  border-radius:10px; border:1.5px solid var(--border);
  background:var(--surface); color:var(--text);
  min-height:50px; font-size:.9rem;
}
.ys-field .form-select:focus,
.ys-field .form-control:focus {
  border-color:var(--primary);
  box-shadow:0 0 0 3px var(--primary-mid);
}
.ys-actions {
  display:flex; gap:.55rem; align-items:stretch; justify-content:flex-end;
}
.ys-save-btn,
.ys-clear-btn {
  min-height:50px; border-radius:12px; font-weight:800;
  font-size:.9rem; font-family:inherit; white-space:nowrap;
  display:inline-flex; align-items:center; justify-content:center; gap:.45rem;
  transition:all var(--transition);
}
.ys-save-btn {
  border:none; background:var(--primary); color:#fff;
  padding:0 1.2rem; box-shadow:0 8px 18px rgba(26,158,120,.18);
}
.ys-save-btn:hover { background:var(--primary-dark); transform:translateY(-1px); }
.ys-clear-btn {
  width:50px; border:1.5px solid var(--border);
  background:var(--surface); color:var(--text-muted);
}
.ys-clear-btn:hover { border-color:var(--danger); color:var(--danger); background:#fdecea; }
.ys-inline-note {
  margin-top:1rem; display:flex; align-items:flex-start; gap:.7rem;
  background:var(--primary-light); border:1px solid rgba(26,158,120,.18);
  border-radius:12px; padding:.8rem .95rem; color:var(--primary);
}
.ys-inline-note i { margin-top:.1rem; }
.ys-inline-note strong { color:var(--primary-dark); }
.ys-current-program {
  display:inline-flex; align-items:center; gap:.45rem;
  padding:.3rem .75rem; border-radius:20px;
  background:var(--accent-light); color:var(--accent);
  border:1px solid rgba(31,115,219,.22); font-size:.72rem; font-weight:800;
}
.ys-overview-title {
  display:flex; align-items:center; justify-content:space-between; gap:1rem;
}
.ys-program-chip {
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.18rem .62rem; border-radius:20px;
  background:rgba(31,115,219,.12); color:var(--accent);
  border:1px solid rgba(31,115,219,.22); font-size:.68rem; font-weight:800;
}
.ys-level-pill {
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.22rem .68rem; border-radius:20px; font-size:.75rem; font-weight:800;
  background:var(--accent-light); color:var(--accent); border:1px solid rgba(31,115,219,.22);
}
.ys-count-pill {
  display:inline-flex; align-items:center; justify-content:center;
  min-width:34px; padding:.2rem .62rem; border-radius:16px;
  background:var(--primary-light); color:var(--primary);
  font-size:.78rem; font-weight:800;
}
.ys-empty-pill {
  color:#adb5bd; font-size:.9rem; font-weight:700;
}
.ys-config-count {
  display:flex; align-items:center; gap:.45rem; justify-content:center;
}
.ys-config-count strong { font-size:1.05rem; color:var(--text); }
.ys-config-count span { font-size:.76rem; color:var(--text-muted); }
.ys-empty-state {
  text-align:center; padding:2rem 1rem; color:var(--text-muted);
}
.ys-empty-state i {
  display:block; margin-bottom:.5rem; font-size:1.35rem; opacity:.35;
}
.ys-modal .modal-dialog { max-width:760px; }
.ys-modal .modal-content { border-radius:18px; overflow:hidden; }
.ys-modal-grid {
  display:grid; grid-template-columns:1fr 1.2fr; gap:.85rem;
}
.ys-modal-grid .ys-field.full { grid-column:1 / -1; }
#ysTable        { min-width: 320px; width: 100%; table-layout: auto; }
#ysOverviewTable { min-width: 600px; width: 100%; table-layout: auto; }

@media (max-width: 768px) {
  .dept-meta-line { display: flex; }
  .ys-card-head,
  .ys-card-body { padding:1rem; }
  .ys-overview-title { align-items:flex-start; flex-direction:column; }
  .ys-form-grid { grid-template-columns:1fr; gap:.7rem; }
  .ys-actions { justify-content:stretch; }
  .ys-save-btn { flex:1; }
  .ys-modal-grid { grid-template-columns:1fr; }
  #ysTable { min-width: unset !important; width: 100% !important; font-size: .76rem; }
  #ysTable thead th, #ysTable tbody td { padding: .42rem .5rem; }

  #ysOverviewTable { font-size: .72rem; }
  #ysOverviewTable thead th, #ysOverviewTable tbody td { padding: .4rem .45rem; }

  /* Hide 4th year on small screens; keep actions visible */
  #ysOverviewTable th:nth-child(7),
  #ysOverviewTable td:nth-child(7) { display: none; }

  /* Force dots col visible on ysTable */
  #ysTable th:last-child, #ysTable td:last-child {
    display: table-cell !important;
    width: 44px !important; min-width: 44px !important;
    padding: 0 4px !important;
    overflow: visible !important; position: relative !important;
  }
}

@media (max-width: 480px) {
  .ys-actions { gap:.45rem; }
  .ys-save-btn { padding:0 .95rem; font-size:.84rem; }
  /* On very small screens also hide 3rd year */
  #ysOverviewTable th:nth-child(6),
  #ysOverviewTable td:nth-child(6),
  #ysOverviewTable th:nth-child(7),
  #ysOverviewTable td:nth-child(7) { display: none; }

  /* Show only Code, Name, 1st, 2nd year */
  #ysOverviewTable th:nth-child(2),
  #ysOverviewTable td:nth-child(2) { width: 60px !important; min-width: 60px !important; }
}

/* â•â• PROG TABLE â€” RESPONSIVE â•â• */
@media (max-width: 768px) {
  .dept-meta-line { display: flex; }
  #progTable { font-size: .76rem; min-width: unset !important; width: 100% !important; table-layout: fixed; }
  #progTable thead th, #progTable tbody td { padding: .42rem .5rem; }
  /* Hide Department column */
  #progTable th:nth-child(4), #progTable td:nth-child(4) { display: none; }
  /* Checkbox col */
  #progTable th:nth-child(1), #progTable td:nth-child(1) { width: 36px !important; min-width: 36px !important; max-width: 36px !important; padding-left: .5rem !important; }
  /* Row number col */
  #progTable th:nth-child(2), #progTable td:nth-child(2) { width: 42px !important; min-width: 42px !important; max-width: 42px !important; }
  /* Status col */
  #progTable th:nth-child(5), #progTable td:nth-child(5) { width: 62px !important; min-width: 62px !important; max-width: 62px !important; }
  /* Dots col â€” forced visible */
  #progTable th:last-child, #progTable td:last-child {
    display: table-cell !important;
    width: 44px !important;
    min-width: 44px !important;
    max-width: 44px !important;
    padding: 0 4px !important;
    overflow: visible !important;
    position: relative !important;
    text-align: center !important;
  }
  /* Program col gets remaining space */
  #progTable th:nth-child(3), #progTable td:nth-child(3) {
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
@media (max-width: 480px) {
  #progTable th:nth-child(3), #progTable td:nth-child(3) { max-width: 100px; }
}

/* dept summary line: mobile only */
.dept-meta-line{display:none!important;}
@media (max-width: 768px){.dept-meta-line{display:flex!important;}}

/* Departments mobile parity with Account Management */
@media (max-width: 768px) {
  #deptTable thead th,
  #deptTable tbody td { padding: .45rem .55rem !important; }

  #deptTable th:nth-child(1),
  #deptTable td:nth-child(1) {
    width: 42px !important;
    min-width: 42px !important;
    text-align: center;
    padding-left: .4rem !important;
    padding-right: .35rem !important;
  }

  #deptTable th:nth-child(2),
  #deptTable td:nth-child(2) {
    width: 34px !important;
    min-width: 34px !important;
    color: var(--text-muted);
  }

  #deptTable td:nth-child(4) { line-height: 1.25; }
  #deptTable td:nth-child(4) > div:first-child { margin-bottom: .08rem; }

  #deptTable th:last-child,
  #deptTable td:last-child {
    width: 44px !important;
    min-width: 44px !important;
    max-width: 44px !important;
    padding: 0 4px !important;
    text-align: center !important;
  }

  #deptTable .btn-dots {
    width: 28px !important;
    height: 28px !important;
    border-radius: 8px !important;
    margin: 0 auto !important;
  }

  #deptTable .dept-meta-line {
    display: flex !important;
    align-items: center;
    gap: .35rem;
    margin-top: .1rem;
    font-size: .7rem;
    color: var(--text-muted);
  }
}

/* Account modal professional polish for edit mode */
#acctModal .modal-dialog { max-width: 940px; }
#acctModal .modal-content {
  border-radius: 18px !important;
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 2rem);
}
#acctModal .modal-body {
  flex: 1 1 auto;
  overflow-y: auto;
}
#acctModal .modal-header { display:flex; align-items:center; justify-content:space-between; }
#acctModal .modal-header .btn-close {
  border-radius: 8px;
  padding: .5rem;
  opacity: .92;
  transition: transform .16s ease, opacity .16s ease, background .16s ease;
}
#acctModal .modal-header .btn-close:hover { opacity: 1; transform: rotate(90deg); background: rgba(255,255,255,.12); }
#acctModal .wiz-panel { margin-bottom: .45rem; }
#acctModal .form-section {
  border-radius: 12px !important;
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
#acctModal .wiz-footer {
  position: sticky;
  bottom: 0;
  z-index: 3;
  flex-shrink: 0;
  backdrop-filter: blur(6px);
  background: color-mix(in srgb, var(--bg) 88%, transparent);
}

/* Edit-specific compact visual hierarchy */
#acctModal.acct-edit-mode .modal-header {
  background: linear-gradient(135deg, #0f766e, #1a9e78);
}
#acctModal.acct-edit-mode .modal-title { font-size: 1.06rem; letter-spacing: .2px; }
#acctModal.acct-edit-mode .modal-body > div { padding-top: 1rem !important; }
#acctModal.acct-edit-mode .form-section { margin-bottom: .65rem !important; }
#acctModal.acct-edit-mode .section-label {
  font-size: .66rem !important;
  margin-bottom: .65rem !important;
  padding-bottom: .34rem !important;
}
#acctModal.acct-edit-mode .form-label {
  font-size: .68rem !important;
  letter-spacing: .55px !important;
  margin-bottom: 3px !important;
}
#acctModal.acct-edit-mode .form-control,
#acctModal.acct-edit-mode .form-select {
  font-size: .86rem;
  padding: .44rem .72rem;
}
#acctModal.acct-edit-mode #wizPanel1 .row.g-3,
#acctModal.acct-edit-mode #wizPanel2 .row.g-3 {
  --bs-gutter-x: .65rem;
  --bs-gutter-y: .55rem;
}

@media (max-width: 768px) {
  #acctModal .modal-dialog { max-width: 100%; margin: .45rem; }
  #acctModal .modal-header { padding: .78rem .95rem !important; }
  #acctModal .modal-title { font-size: .96rem; }
  #acctModal.acct-edit-mode .form-control,
  #acctModal.acct-edit-mode .form-select { font-size: .84rem; }
}

/* Edit Account: structured 2-column desktop layout */
@media (min-width: 992px) {
  #acctModal.acct-edit-mode #wizPanel1,
  #acctModal.acct-edit-mode #wizPanel2 {
    display: grid !important;
    grid-template-columns: 1fr 1fr;
    gap: .7rem;
    align-items: start;
  }

  #acctModal.acct-edit-mode #wizPanel1 .form-section,
  #acctModal.acct-edit-mode #wizPanel2 .form-section {
    margin-bottom: 0 !important;
    height: 100%;
  }

  #acctModal.acct-edit-mode #wizPanel1 .form-section:first-child,
  #acctModal.acct-edit-mode #wizPanel2 .form-section:first-child {
    box-shadow: 0 2px 8px rgba(26,158,120,.08);
  }

  #acctModal.acct-edit-mode .modal-body > div {
    padding-right: 1.15rem !important;
    padding-left: 1.15rem !important;
  }
}

@media (max-width: 991.98px) {
  #acctModal.acct-edit-mode #wizPanel1,
  #acctModal.acct-edit-mode #wizPanel2 {
    display: block !important;
  }
}

/* Compact Academic Structure panel */
.sr-only {
  position:absolute; width:1px; height:1px; padding:0; margin:-1px;
  overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0;
}
.acad-page {
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:12px;
  overflow:hidden;
  box-shadow:var(--shadow);
}
.acad-head {
  padding:18px 20px 14px;
  border-bottom:1px solid var(--border);
  background:linear-gradient(180deg,rgba(26,158,120,.045),transparent),var(--surface);
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:1rem;
}
.acad-title { font-size:1.15rem; font-weight:800; color:var(--text); line-height:1.2; }
.acad-sub { font-size:.82rem; color:var(--text-muted); margin-top:.2rem; }
.acad-crumb { font-size:.76rem; color:var(--text-muted); white-space:nowrap; }
.acad-tab-bar {
  display:flex;
  gap:2px;
  padding:10px 20px 0;
  background:var(--surface);
  border-bottom:1px solid var(--border);
  overflow-x:auto;
}
.acad-tab {
  font-size:.8rem;
  padding:8px 14px;
  cursor:pointer;
  border:none;
  background:transparent;
  color:var(--text-muted);
  border-bottom:2px solid transparent;
  font-family:inherit;
  transition:all .12s;
  display:flex;
  align-items:center;
  gap:7px;
  border-radius:8px 8px 0 0;
  white-space:nowrap;
}
.acad-tab:hover { color:var(--text); background:var(--bg); }
.acad-tab.on { color:var(--primary-dark); border-bottom-color:var(--primary-dark); font-weight:800; background:var(--bg); }
.acad-tab .cnt {
  font-size:.68rem;
  background:var(--bg);
  border:1px solid var(--border);
  border-radius:999px;
  padding:1px 7px;
  color:var(--text-muted);
  line-height:1.35;
}
.acad-tab.on .cnt { background:var(--primary-light); border-color:rgba(26,158,120,.28); color:var(--primary-dark); }
.acad-panel { display:none; padding:18px 20px 20px; }
.acad-panel.on { display:block; }
.acad-toolbar {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:.75rem;
  margin-bottom:14px;
  flex-wrap:wrap;
}
.acad-section-label {
  font-size:.67rem;
  font-weight:900;
  color:var(--text-muted);
  letter-spacing:.08em;
  text-transform:uppercase;
}
.acad-active-banner,
.acad-info-banner {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:.8rem;
  background:var(--primary-light);
  border:1px solid rgba(26,158,120,.22);
  border-radius:10px;
  padding:10px 14px;
  margin-bottom:14px;
}
.acad-info-banner {
  justify-content:flex-start;
  color:var(--primary-dark);
  font-size:.78rem;
}
.acad-active-main { display:flex; align-items:center; gap:.45rem; min-width:0; color:var(--text-muted); font-size:.82rem; }
.acad-active-dot {
  width:8px; height:8px; border-radius:50%;
  background:var(--primary-dark);
  box-shadow:0 0 0 3px rgba(26,158,120,.16);
  flex-shrink:0;
}
.acad-active-val { color:var(--primary-dark); font-weight:800; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.acad-page .btn-primary { background:var(--primary-dark); border-color:var(--primary-dark); }
.acad-page .btn-ghost { background:var(--bg); border-color:var(--border); color:var(--text); }
.acad-page .btn-sm { padding:.32rem .72rem; font-size:.74rem; }
.acad-page .sem-row {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:1rem;
  padding:12px 14px;
  border:1px solid var(--border);
  border-radius:10px;
  margin-bottom:8px;
  background:var(--surface);
  transition:background .12s,border-color .12s,box-shadow .12s;
}
.acad-page .sem-row:hover { background:var(--bg); box-shadow:var(--shadow); }
.acad-page .sem-row.active-row { border-color:var(--primary-dark); background:linear-gradient(180deg,rgba(26,158,120,.05),transparent),var(--surface); }
.acad-page .sem-name { font-size:.88rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
.acad-page .sem-date { font-size:.75rem; color:var(--text-muted); margin-top:3px; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.acad-page .sem-actions { display:flex; align-items:center; justify-content:flex-end; gap:6px; flex-wrap:wrap; }
.acad-badge {
  display:inline-flex;
  align-items:center;
  gap:4px;
  font-size:.68rem;
  font-weight:800;
  padding:2px 8px;
  border-radius:999px;
  white-space:nowrap;
}
.acad-badge-active { background:var(--primary-light); color:var(--primary-dark); border:1px solid rgba(26,158,120,.32); }
.acad-badge-inactive { background:var(--bg); color:var(--text-muted); border:1px solid var(--border); }
.acad-badge-warning { background:#fff3cd; color:#92400e; border:1px solid #f59e0b; }
.acad-linked-badge { background:var(--accent-light); color:var(--accent); border:1px solid rgba(31,115,219,.18); }
.acad-table-wrap { border:1px solid var(--border); border-radius:10px; overflow:auto; }
.prog-tbl {
  width:100%;
  border-collapse:collapse;
  font-size:.8rem;
  table-layout:fixed;
  min-width:760px;
}
.prog-tbl thead tr { background:var(--primary-dark); }
.prog-tbl thead th {
  padding:8px 10px;
  text-align:left;
  font-size:.65rem;
  font-weight:800;
  color:#fff;
  letter-spacing:.06em;
  text-transform:uppercase;
  border:none;
}
.prog-tbl thead th:not(:first-child) { text-align:center; }
.prog-tbl tbody td { padding:10px; border-bottom:1px solid var(--border); color:var(--text-muted); vertical-align:middle; }
.prog-tbl tbody tr:last-child td { border-bottom:none; }
.prog-tbl tbody tr:hover td { background:var(--bg); }
.ys-program-chip,
.prog-code {
  background:var(--bg);
  border:1px solid var(--border);
  border-radius:6px;
  padding:2px 8px;
  font-size:.7rem;
  font-weight:800;
  color:var(--text);
  white-space:nowrap;
}
.ys-count-pill {
  background:var(--primary-light);
  color:var(--primary-dark);
  border:1px solid rgba(26,158,120,.26);
  border-radius:999px;
  font-size:.78rem;
  font-weight:900;
  padding:3px 11px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:34px;
  cursor:pointer;
}
.ys-count-pill:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
.ys-empty-pill { color:var(--text-muted); opacity:.7; }
.ys-empty-state { text-align:center; color:var(--text-muted); padding:1.5rem!important; }
.ys-empty-state i { display:block; font-size:1.35rem; opacity:.28; margin-bottom:.35rem; }
.acad-help { font-size:.75rem; color:var(--text-muted); margin-top:8px; }
.acad-weeks-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
.acad-week-card { background:var(--surface); border:1px solid var(--border); border-radius:10px; overflow:hidden; }
.acad-week-card-head {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:.75rem;
  padding:10px 13px;
  border-bottom:1px solid var(--border);
  background:var(--bg);
}
.acad-week-card-title { font-size:.82rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
.acad-week-card-body { padding:10px 13px; }
.acad-week-input-row {
  display:flex;
  align-items:center;
  gap:8px;
  padding-bottom:10px;
  border-bottom:1px solid var(--border);
  margin-bottom:10px;
  flex-wrap:wrap;
}
.acad-week-input-row label { font-size:.75rem; color:var(--text-muted); white-space:nowrap; }
.acad-week-input-row input {
  width:68px;
  border:1px solid var(--border);
  border-radius:8px;
  padding:5px 8px;
  font-size:.8rem;
  color:var(--text);
  background:var(--surface);
  font-family:inherit;
  outline:none;
}
.acad-week-input-row input:focus { border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-mid); }
.acad-week-summary { display:flex; align-items:center; gap:8px; font-size:.75rem; color:var(--text-muted); flex-wrap:wrap; }
.acad-week-summary strong { color:var(--text); }
.acad-week-row { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:5px 0; border-bottom:1px solid var(--border); }
.acad-week-row:last-child { border-bottom:none; }
.acad-week-num { font-size:.75rem; color:var(--text); font-weight:800; }
.acad-week-dates { font-size:.7rem; color:var(--text-muted); }
.acad-toast {
  display:none;
  align-items:center;
  gap:8px;
  background:var(--primary-light);
  border:1px solid rgba(26,158,120,.25);
  border-radius:10px;
  padding:9px 13px;
  margin-bottom:12px;
  font-size:.8rem;
  color:var(--primary-dark);
}
@media (max-width:900px) {
  .acad-head { flex-direction:column; }
  .acad-crumb { white-space:normal; }
  .acad-weeks-grid { grid-template-columns:1fr; }
}
@media (max-width:640px) {
  .acad-panel { padding:14px; }
  .acad-tab-bar { padding-left:14px; padding-right:14px; }
  .acad-page .sem-row { align-items:flex-start; flex-direction:column; }
  .acad-page .sem-actions { width:100%; justify-content:flex-start; }
}
</style>

</head>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     BODY
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<body class="hold-transition">

<div class="overlay" id="tlOverlay"></div>

<!-- TOP NAV -->
<nav class="topnav">
  <button class="menu-btn" id="menuToggle">
    <i class="fas fa-bars"></i>
  </button>
  <a href="#" class="nav-brand">
    <div class="brand-logo"><i class="fas fa-book-open"></i></div>
    TERELEARN
  </a>
  <div class="nav-actions">
    <button class="icon-btn" id="darkToggle" title="Toggle dark mode"><i class="fas fa-moon"></i></button>
    <a class="icon-btn" href="signin.php" onclick="return confirm('Sign out?')"><i class="fas fa-sign-out-alt"></i></a>
    <div class="nav-avatar" title="<?= $adminNameEsc ?>">
      <?php if ($hasAdminPic): ?>
        <img src="<?= $adminPicEsc ?>" alt="<?= $adminNameEsc ?>">
      <?php else: ?>
        <?= htmlspecialchars($adminInitials, ENT_QUOTES, 'UTF-8') ?>
      <?php endif; ?>
    </div>
  </div>
</nav>

<aside class="sidebar" id="tlSidebar">
  <div class="sidebar-user">
    <div class="s-avatar">
      <?php if ($hasAdminPic): ?>
        <img src="<?= $adminPicEsc ?>" alt="<?= $adminNameEsc ?>">
      <?php else: ?>
        <?= htmlspecialchars($adminInitials, ENT_QUOTES, 'UTF-8') ?>
      <?php endif; ?>
    </div>
    <div>
      <div class="s-name">
        <?= $adminNameEsc ?>
        <span class="s-role-chip"><i class="fas fa-shield-alt"></i> <?= $isRootAdmin ? 'Root' : 'Admin' ?></span>
      </div>
      <div class="s-role"><?= $adminRoleEsc ?></div>
    </div>
  </div>

  <div class="nav-section-label">Overview</div>
  <button class="nav-item" data-section="dashboard" onclick="showSection('dashboard')">
    <i class="fas fa-th-large"></i><span class="sb-label">Dashboard</span>
  </button>

  <div class="nav-section-label">Accounts</div>
  <button class="nav-item" data-section="accounts" onclick="showSection('accounts')">
    <i class="fas fa-users-cog"></i><span class="sb-label">Account Management</span>
  </button>

  <div class="nav-section-label">Academic Structure</div>
  <button class="nav-item" data-section="departments" onclick="showSection('departments')">
    <i class="fas fa-university"></i><span class="sb-label">Departments</span>
  </button>
  <button class="nav-item" data-section="programs" onclick="showSection('programs')">
    <i class="fas fa-graduation-cap"></i><span class="sb-label">Programs</span>
  </button>
  <button class="nav-item" data-section="academic" onclick="showSection('academic')">
    <i class="fas fa-layer-group"></i><span class="sb-label">Academic Structure</span>
  </button>

  <div class="nav-section-label">Settings</div>
  <button class="nav-item" data-section="calendar" onclick="showSection('calendar')">
    <i class="fas fa-calendar-day"></i><span class="sb-label">Calendar</span>
  </button>

  <div class="sidebar-footer-inner">
    <a href="signin.php" class="signout-btn" onclick="return confirm('Sign out?')">
      <i class="fas fa-sign-out-alt"></i><span>Sign Out</span>
    </a>
  </div>
</aside>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     MAIN CONTENT
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<main class="main" id="tlMain">

  <!-- â”€â”€ DASHBOARD â”€â”€ -->
  <div id="section-dashboard" class="admin-section">
    <div class="page-header">
      <div>
        <div class="page-title">Admin Dashboard</div>
        <div class="page-subtitle">System overview and department summary</div>
      </div>
      <div class="tl-breadcrumb"><span class="cur">Dashboard</span></div>
    </div>

    <div class="stat-grid">
      <div class="stat-card sc-g">
        <div class="stat-card-head">
          <div class="stat-mini"><i class="fas fa-signal"></i> Live</div>
          <div class="stat-icon si-g"><i class="fas fa-university"></i></div>
        </div>
        <div class="stat-card-main">
          <div class="stat-val" id="st-depts">-</div>
          <div class="stat-lbl">Departments</div>
        </div>
        <div class="stat-card-foot">
          <div class="stat-foot-note"><i class="fas fa-circle"></i> Academic units</div>
          <div class="stat-foot-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
      </div>
      <div class="stat-card sc-b">
        <div class="stat-card-head">
          <div class="stat-mini"><i class="fas fa-signal"></i> Live</div>
          <div class="stat-icon si-b"><i class="fas fa-graduation-cap"></i></div>
        </div>
        <div class="stat-card-main">
          <div class="stat-val" id="st-progs">-</div>
          <div class="stat-lbl">Programs</div>
        </div>
        <div class="stat-card-foot">
          <div class="stat-foot-note"><i class="fas fa-circle"></i> Course offerings</div>
          <div class="stat-foot-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
      </div>
      <div class="stat-card sc-o">
        <div class="stat-card-head">
          <div class="stat-mini"><i class="fas fa-signal"></i> Live</div>
          <div class="stat-icon si-o"><i class="fas fa-chalkboard-teacher"></i></div>
        </div>
        <div class="stat-card-main">
          <div class="stat-val" id="st-faculty">-</div>
          <div class="stat-lbl">Faculty</div>
        </div>
        <div class="stat-card-foot">
          <div class="stat-foot-note"><i class="fas fa-circle"></i> Teaching staff</div>
          <div class="stat-foot-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
      </div>
      <div class="stat-card sc-p">
        <div class="stat-card-head">
          <div class="stat-mini"><i class="fas fa-signal"></i> Live</div>
          <div class="stat-icon si-p"><i class="fas fa-user-graduate"></i></div>
        </div>
        <div class="stat-card-main">
          <div class="stat-val" id="st-students">-</div>
          <div class="stat-lbl">Students</div>
        </div>
        <div class="stat-card-foot">
          <div class="stat-foot-note"><i class="fas fa-circle"></i> Current records</div>
          <div class="stat-foot-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
      </div>
      <div class="stat-card sc-t">
        <div class="stat-card-head">
          <div class="stat-mini"><i class="fas fa-signal"></i> Live</div>
          <div class="stat-icon si-t"><i class="fas fa-book-open"></i></div>
        </div>
        <div class="stat-card-main">
          <div class="stat-val" id="st-subjects">-</div>
          <div class="stat-lbl">Subjects</div>
        </div>
        <div class="stat-card-foot">
          <div class="stat-foot-note"><i class="fas fa-circle"></i> Catalog entries</div>
          <div class="stat-foot-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
      </div>
      <div class="stat-card sc-r">
        <div class="stat-card-head">
          <div class="stat-mini"><i class="fas fa-signal"></i> Live</div>
          <div class="stat-icon si-r"><i class="fas fa-chalkboard"></i></div>
        </div>
        <div class="stat-card-main">
          <div class="stat-val" id="st-classes">-</div>
          <div class="stat-lbl">Classes</div>
        </div>
        <div class="stat-card-foot">
          <div class="stat-foot-note"><i class="fas fa-circle"></i> Active sections</div>
          <div class="stat-foot-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
      </div>
      <div class="stat-card sc-s">
        <div class="stat-card-head">
          <div class="stat-mini"><i class="fas fa-signal"></i> Live</div>
          <div class="stat-icon si-s"><i class="fas fa-user-tie"></i></div>
        </div>
        <div class="stat-card-main">
          <div class="stat-val" id="st-deans">-</div>
          <div class="stat-lbl">Active Deans</div>
        </div>
        <div class="stat-card-foot">
          <div class="stat-foot-note"><i class="fas fa-circle"></i> Department leads</div>
          <div class="stat-foot-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
      </div>
    </div>

    <div class="dash-analytics-grid">
      <div class="dash-analytics-stack">
        <div class="analytics-card">
          <div class="analytics-card-head">
            <div>
              <div class="analytics-card-title">Growth Trend</div>
              <div class="analytics-card-sub">Recent student, faculty, and program additions across the whole system.</div>
            </div>
            <div class="analytics-kicker"><i class="fas fa-chart-line"></i> 6 Months</div>
          </div>
          <div class="analytics-chart-wrap">
            <canvas id="adminGrowthChart"></canvas>
          </div>
        </div>

        <div class="analytics-card">
          <div class="analytics-card-head">
            <div>
              <div class="analytics-card-title">Department Academic Footprint</div>
              <div class="analytics-card-sub">Compare how many programs, subjects, and classes each department is carrying.</div>
            </div>
            <div class="analytics-kicker"><i class="fas fa-layer-group"></i> Department Scope</div>
          </div>
          <div class="analytics-chart-wrap compact">
            <canvas id="departmentFootprintChart"></canvas>
          </div>
        </div>
      </div>

      <div class="dash-analytics-stack">
        <div class="analytics-card">
          <div class="analytics-card-head">
            <div>
              <div class="analytics-card-title">Students Per Department</div>
              <div class="analytics-card-sub">Quick view of where enrollment is concentrated right now.</div>
            </div>
            <div class="analytics-kicker"><i class="fas fa-users"></i> Enrollment</div>
          </div>
          <div class="analytics-chart-wrap compact">
            <canvas id="departmentStudentsChart"></canvas>
          </div>
        </div>

        <div class="analytics-card">
          <div class="analytics-card-head">
            <div>
              <div class="analytics-card-title">Top Programs by Enrollment</div>
              <div class="analytics-card-sub">Programs with the highest number of students across all departments.</div>
            </div>
            <div class="analytics-kicker"><i class="fas fa-graduation-cap"></i> Top 8</div>
          </div>
          <div class="top-program-list" id="topProgramList">
            <div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</div>
          </div>
        </div>
      </div>
    </div>

    <div class="section-divider">
      <div class="sd-icon"><i class="fas fa-university"></i></div>
      Department Overview
    </div>
    <div class="dept-grid" id="dashDeptGrid">
      <div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</div>
    </div>
  </div>
  


<!-- â”€â”€ ACCOUNT MANAGEMENT v2 â”€â”€ -->
<div id="section-accounts" class="admin-section">
  <div class="page-header">
    <div>
      <div class="page-title">Account Management</div>
      <div class="page-subtitle">Manage Admin, Dean, and Secretary accounts</div>
    </div>
    <div class="tl-breadcrumb">
      <a href="#" onclick="showSection('dashboard');return false;">Dashboard</a>
      <span class="sep">/</span><span class="cur">Account Management</span>
    </div>
  </div>

  <div class="acct-toolbar" style="display:flex;align-items:center;gap:.55rem;width:100%;margin-bottom:1rem;">
    <div class="acct-ctrl-wrap">
      <button class="acct-ctrl-btn" id="acctFilterToggle" onclick="acctToggleControlMenu(event)">
        <i class="fas fa-sliders-h"></i>
        <span class="fbadge" id="acctFilterBadge">0</span>
      </button>
      <div class="acct-ctrl-menu" id="acctCtrlMenu">
        <button class="acct-ctrl-item" onclick="acctEnterSelectMode()">
          <i class="fas fa-list-check"></i> Select users
        </button>
        <button class="acct-ctrl-item" onclick="acctOpenFilters()">
          <i class="fas fa-sliders-h"></i> Filters
        </button>
      </div>
    </div>
    <button class="acct-select-cancel" id="acctSelectCancelBtn" onclick="acctExitSelectMode()">
      <i class="fas fa-xmark"></i> Cancel
    </button>
    <button class="acct-filter-cancel" id="acctFilterCancelBtn" onclick="acctToggleFilter()">
      <i class="fas fa-xmark"></i> Cancel
    </button>
    <div class="search-wrap" style="flex:1;min-width:0;">
      <i class="fas fa-search s-icon"></i>
      <input type="text" id="acctSearch" class="form-control"
        placeholder="Search name, ID, or email..." autocomplete="off"
        oninput="acctApplyAll()">
    </div>
    <button id="addAcctBtn" onclick="openAddAcctModal()"
      style="flex-shrink:0;border-radius:8px;padding:.45rem 1rem;
             border:1.5px solid var(--primary);background:var(--primary-light);
             color:var(--primary);font-size:.82rem;font-weight:700;
             display:inline-flex;align-items:center;gap:.4rem;
             transition:all .18s;white-space:nowrap;cursor:pointer;font-family:inherit;"
      onmouseover="this.style.background='var(--primary)';this.style.color='#fff'"
      onmouseout="this.style.background='var(--primary-light)';this.style.color='var(--primary)'">
      <i class="fas fa-user-plus" style="font-size:.78rem;"></i>
      <span>Add Account</span>
    </button>
   
    
  </div>

  <!-- Bulk action bar -->
  <div class="bulk-action-bar" id="acctBulkBar">
    <span class="bulk-count" id="acctBulkCount">0 selected</span>
    <div class="bulk-btns">
      <button class="bulk-btn" onclick="bulkAcctActivate()"><i class="fas fa-check-circle"></i> Activate</button>
      <button class="bulk-btn" onclick="bulkAcctDeactivate()"><i class="fas fa-ban"></i> Deactivate</button>
      <button class="bulk-btn danger" onclick="bulkAcctDelete()"><i class="fas fa-trash"></i> Delete</button>
    </div>
    <button class="bulk-desel" onclick="acctClearSelection()" title="Clear selection">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="acct-content-layout">
    <!-- Filter panel -->
    <div class="acct-filter-panel" id="acctFilterPanel">
      <div class="afp-title">
        Filters
      </div>
      <div class="afp-group">
        <div class="afp-label">Account Type</div>
        <div class="afp-chips">
          <button class="afp-chip active" data-filter="type" data-val="" onclick="acctSetFilter('type','',this)">All</button>
          <button class="afp-chip" data-filter="type" data-val="admin" onclick="acctSetFilter('type','admin',this)"><i class="fas fa-shield-alt me-1"></i>Admin</button>
          <button class="afp-chip" data-filter="type" data-val="dean" onclick="acctSetFilter('type','dean',this)"><i class="fas fa-user-tie me-1"></i>Dean</button>
          <button class="afp-chip" data-filter="type" data-val="secretary" onclick="acctSetFilter('type','secretary',this)"><i class="fas fa-id-badge me-1"></i>Secretary</button>
        </div>
      </div>
      <div class="afp-group">
        <div class="afp-label">Status</div>
        <div class="afp-chips">
          <button class="afp-chip active" data-filter="status" data-val="" onclick="acctSetFilter('status','',this)">All</button>
          <button class="afp-chip" data-filter="status" data-val="1" onclick="acctSetFilter('status','1',this)"><i class="fas fa-circle me-1" style="color:#16a34a;font-size:.55rem;"></i>Active</button>
          <button class="afp-chip" data-filter="status" data-val="0" onclick="acctSetFilter('status','0',this)"><i class="fas fa-circle me-1" style="color:#adb5bd;font-size:.55rem;"></i>Inactive</button>
        </div>
      </div>
      <button class="afp-reset" onclick="acctResetFilters()"><i class="fas fa-rotate-left me-1"></i> Reset Filters</button>
    </div>

    <div class="acct-content-main">
      <!-- Table -->
      <div class="acct-table-wrap acct-rows-scroll">
        <table class="table table-hover mb-0 tl-table" id="acctTable">
          <thead><tr>
            <th class="acct-select-col sticky-head" style="width:36px;text-align:center;padding-left:.65rem;">
              <input type="checkbox" class="acct-cb" id="acctSelectAll" onchange="acctToggleAll(this)">
            </th>
            <th class="sticky-head" style="min-width:140px">Full Name</th>
            <th class="sticky-head" style="width:100px">Employee ID</th>
            <th class="sticky-head" style="width:90px">Type</th>
            <th class="sticky-head" style="min-width:150px">Department</th>
            <th class="sticky-head text-center" style="width:72px">Status</th>
            <th class="sticky-head text-center" style="width:80px">OTP</th>
            <th class="sticky-head" style="width:44px;padding:0;text-align:center;"></th>
          </tr></thead>
          <tbody id="acctTableBody">
            <tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="acct-pagination" id="acctPagination">
        <div class="apg-info" id="acctPgInfo">Showing 1-5 of 0</div>
        <div class="apg-controls">
          <select class="apg-size-sel" id="acctPageSize" onchange="acctChangePageSize(this.value)">
            <option value="5">5 / page</option>
            <option value="10" selected>10 / page</option>
            <option value="25">25 / page</option>
          </select>
          <button class="apg-btn" id="acctPgFirst"  onclick="acctGoPage(1)"                title="First"><i class="fas fa-angles-left"></i></button>
          <button class="apg-btn" id="acctPgPrev"   onclick="acctGoPage(acctPage-1)"       title="Previous"><i class="fas fa-angle-left"></i></button>
          <div class="apg-pages" id="acctPgPages"></div>
          <button class="apg-btn" id="acctPgNext"   onclick="acctGoPage(acctPage+1)"       title="Next"><i class="fas fa-angle-right"></i></button>
          <button class="apg-btn" id="acctPgLast"   onclick="acctGoPage(acctTotalPages||1)" title="Last"><i class="fas fa-angles-right"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- â”€â”€ DEPARTMENTS â”€â”€ -->
<div id="section-departments" class="admin-section">
  <div class="page-header">
    <div>
      <div class="page-title">Departments</div>
      <div class="page-subtitle">Manage academic departments, their programs, and assigned personnel</div>
    </div>
    <div class="tl-breadcrumb">
      <a href="#" onclick="showSection('dashboard');return false;">Dashboard</a>
      <span class="sep">/</span><span class="cur">Departments</span>
    </div>
  </div>



  <!-- Toolbar -->
  <div style="display:flex;flex-direction:column;gap:.55rem;margin-bottom:1.1rem;">

    <!-- Row 1: Control + Search + Add -->
    <div style="display:flex;align-items:center;gap:.55rem;">
      <div class="acct-ctrl-wrap">
        <button class="acct-ctrl-btn" id="deptFilterToggle" onclick="deptToggleControlMenu(event)"
          aria-label="Department controls">
          <i class="fas fa-sliders-h"></i>
        </button>
        <div class="acct-ctrl-menu" id="deptCtrlMenu">
          <button class="acct-ctrl-item" id="deptSelectRowsBtn" onclick="deptEnterSelectMode()">
            <i class="fas fa-list-check"></i> Select rows
          </button>
          <button class="acct-ctrl-item" onclick="deptToggleFilterBar()">
            <i class="fas fa-sliders-h"></i> Filters
          </button>
        </div>
      </div>
      <button class="acct-select-cancel" id="deptSelectCancelBtn" onclick="deptExitSelectMode()">
        <i class="fas fa-xmark"></i> Cancel
      </button>
      <button class="acct-filter-cancel" id="deptFilterCancelBtn" onclick="deptToggleFilterBar()">
        <i class="fas fa-xmark"></i> Cancel
      </button>
      <div class="search-wrap" style="flex:1;min-width:0;">
        <i class="fas fa-search s-icon"></i>
        <input type="text" id="deptSearch" class="form-control" placeholder="Search by code or name..." autocomplete="off">
      </div>
      <button onclick="openDeptModal()"
        style="flex-shrink:0;border-radius:8px;padding:.45rem 1rem;border:1.5px solid var(--primary);
               background:var(--primary-light);color:var(--primary);font-size:.82rem;font-weight:700;
               display:inline-flex;align-items:center;gap:.4rem;transition:all .18s;
               white-space:nowrap;cursor:pointer;font-family:inherit;"
        onmouseover="this.style.background='var(--primary)';this.style.color='#fff'"
        onmouseout="this.style.background='var(--primary-light)';this.style.color='var(--primary)'">
        <i class="fas fa-plus" style="font-size:.78rem;"></i>
        <span class="d-none d-sm-inline">Add Department</span>
        <span class="d-inline d-sm-none">Add</span>
      </button>
    </div>

  </div>

    <div class="bulk-action-bar" id="deptBulkBar">
    <span class="bulk-count" id="deptBulkCount">0 selected</span>
    <div class="bulk-btns">
      <button class="bulk-btn" onclick="deptClearSelection()"><i class="fas fa-xmark"></i> Clear Selection</button>
    </div>
  </div>

  <div class="acct-content-layout">
    <div class="acct-filter-panel dept-filter-bar" id="deptFilterBar">
      <div class="afp-title">Filters</div>
      <div class="afp-group">
        <div class="afp-label">Sort</div>
        <select class="dept-sort-sel" id="deptSortSel" onchange="applyDeptFilter()"
          style="font-size:.74rem;padding:.28rem .6rem;border-radius:8px;border:1.5px solid var(--border);
                 background:var(--surface);color:var(--text);cursor:pointer;outline:none;">
          <option value="name">Name A-Z</option>
          <option value="code">Code A-Z</option>
          <option value="progs">Most Programs</option>
        </select>
      </div>
      <div class="afp-group">
        <div class="afp-label">Filter</div>
        <div class="afp-chips">
          <button class="afp-chip active filter-chip" id="fcAll" onclick="setDeptFilter('all')">All</button>
          <button class="afp-chip filter-chip" id="fcDean" onclick="setDeptFilter('dean')"><i class="fas fa-user-tie"></i> Has Dean</button>
          <button class="afp-chip filter-chip" id="fcNoDean" onclick="setDeptFilter('nodean')"><i class="fas fa-exclamation-circle"></i> No Dean</button>
        </div>
      </div>
    </div>
    <div class="acct-content-main">
      <div class="acct-table-wrap">
      <table class="table table-hover mb-0 tl-table" id="deptTable">
        <thead><tr>
          <th class="dept-select-col" style="width:42px;text-align:center;"><input type="checkbox" class="acct-cb" id="deptSelectAll" onchange="deptToggleAll(this)"></th>
          <th style="width:40px">#</th>
          <th>Department</th>
          <th style="min-width:150px">Dean</th>
          <th style="min-width:150px">Secretary</th>
          <th class="text-center" style="width:90px">Programs</th>
          <th class="text-center" style="width:80px">Status</th>
          <th class="text-center" style="width:44px;min-width:44px;padding:0 4px;"></th>
        </tr></thead>
        <tbody id="deptGrid"><tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr></tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<div id="section-programs" class="admin-section">
  <div class="page-header">
    <div>
      <div class="page-title">Programs / Courses</div>
      <div class="page-subtitle">Academic programs grouped by department</div>
    </div>
    <div class="tl-breadcrumb">
      <a href="#" onclick="showSection('dashboard');return false;">Dashboard</a>
      <span class="sep">/</span><span class="cur">Programs</span>
    </div>
  </div>

  <!-- Stats bar -->
  <div class="dept-stats-bar" id="progStatsBar">
    <span class="ds-chip"><i class="fas fa-graduation-cap"></i> <span id="psTotal">â€”</span> total</span>
    <span class="ds-chip"><i class="fas fa-check-circle" style="color:#16a34a;"></i> <span id="psActive">â€”</span> active</span>
    <span class="ds-chip"><i class="fas fa-ban" style="color:#adb5bd;"></i> <span id="psInactive">â€”</span> inactive</span>
    <span class="ds-chip"><i class="fas fa-university"></i> <span id="psDepts">â€”</span> departments</span>
  </div>

  <!-- Toolbar -->
  <div class="acct-toolbar" style="display:flex;align-items:center;gap:.55rem;width:100%;margin-bottom:1rem;">
    <div class="acct-ctrl-wrap">
      <button class="acct-ctrl-btn" id="progFilterToggle" onclick="progToggleControlMenu(event)"
        title="Controls" aria-label="Program controls">
        <i class="fas fa-sliders-h"></i>
        <span class="fbadge" id="progFilterBadge">0</span>
      </button>
      <div class="acct-ctrl-menu" id="progCtrlMenu">
        <button class="acct-ctrl-item" id="progSelectRowsBtn" onclick="progEnterSelectMode()">
          <i class="fas fa-list-check"></i> Select programs
        </button>
        <button class="acct-ctrl-item" onclick="progOpenFilters()">
          <i class="fas fa-sliders-h"></i> Filters
        </button>
      </div>
    </div>
    <button class="acct-select-cancel" id="progSelectCancelBtn" onclick="progExitSelectMode()">
      <i class="fas fa-xmark"></i> Cancel
    </button>
    <button class="acct-filter-cancel" id="progFilterCancelBtn" onclick="progToggleFilterBar()">
      <i class="fas fa-xmark"></i> Cancel
    </button>
    <div class="search-wrap" style="flex:1;min-width:0;">
      <i class="fas fa-search s-icon"></i>
      <input type="text" id="progSearch" class="form-control"
        placeholder="Search by code, name, or department..." autocomplete="off"
        oninput="applyProgFilter()">
    </div>
    <button onclick="openAddProgModal()"
      style="flex-shrink:0;border-radius:8px;padding:.45rem 1rem;border:1.5px solid var(--primary);background:var(--primary-light);color:var(--primary);font-size:.82rem;font-weight:700;display:inline-flex;align-items:center;gap:.4rem;transition:all .18s;white-space:nowrap;cursor:pointer;font-family:inherit;"
      onmouseover="this.style.background='var(--primary)';this.style.color='#fff'"
      onmouseout="this.style.background='var(--primary-light)';this.style.color='var(--primary)'">
      <i class="fas fa-user-plus" style="font-size:.78rem;"></i><span>Add Program</span>
    </button>
  </div>

  <!-- Bulk action bar -->
  <div class="bulk-action-bar" id="progBulkBar">
    <span class="bulk-count" id="progBulkCount">0 selected</span>
    <div class="bulk-btns">
      <button class="bulk-btn" onclick="bulkProgActivate()"><i class="fas fa-check-circle"></i> Activate</button>
      <button class="bulk-btn" onclick="bulkProgDeactivate()"><i class="fas fa-ban"></i> Deactivate</button>
      <button class="bulk-btn danger" onclick="bulkProgDelete()"><i class="fas fa-trash"></i> Delete</button>
    </div>
    <button class="bulk-desel" onclick="progClearSelection()" title="Clear selection">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="acct-content-layout">
    <!-- Filter panel -->
    <div class="acct-filter-panel" id="progFilterPanel">
    <div class="afp-title">
      Filters
      <button class="afp-close" onclick="progToggleFilterBar()"><i class="fas fa-times"></i></button>
    </div>
    <div class="afp-group">
      <div class="afp-label">Status</div>
      <div class="afp-chips" id="progStatusChips">
        <button class="afp-chip active" data-val="" onclick="progSetFilterChip('status','',this,'progStatusChips')">All</button>
        <button class="afp-chip" data-val="active" onclick="progSetFilterChip('status','active',this,'progStatusChips')"><i class="fas fa-circle me-1" style="color:#16a34a;font-size:.55rem;"></i>Active</button>
        <button class="afp-chip" data-val="inactive" onclick="progSetFilterChip('status','inactive',this,'progStatusChips')"><i class="fas fa-circle me-1" style="color:#adb5bd;font-size:.55rem;"></i>Inactive</button>
      </div>
    </div>
    <div class="afp-group">
      <div class="afp-label">Department</div>
      <select id="progDeptChipFilter" class="form-select"
        style="border-radius:8px;border:1.5px solid var(--border);font-size:.8rem;background:var(--surface);color:var(--text);"
        onchange="applyProgFilter();progUpdateFilterBadge()">
        <option value="">All Departments</option>
      </select>
    </div>
    <div class="afp-group">
      <div class="afp-label">Sort</div>
      <select id="progSortSel" class="form-select"
        style="border-radius:8px;border:1.5px solid var(--border);font-size:.8rem;background:var(--surface);color:var(--text);"
        onchange="applyProgFilter()">
        <option value="name">Name A-Z</option>
        <option value="code">Code A-Z</option>
        <option value="dept">Department</option>
      </select>
    </div>
    <button class="afp-reset" onclick="progResetFilters()"><i class="fas fa-rotate-left me-1"></i> Reset Filters</button>
    </div>

    <div class="acct-content-main">
      <!-- Table -->
      <div class="acct-table-wrap">
        <table class="table table-hover mb-0 tl-table" id="progTable">
      <thead><tr>
        <th class="prog-select-col" style="width:36px;text-align:center;padding-left:.65rem;">
          <input type="checkbox" class="acct-cb" id="progSelectAll" onchange="progToggleAll(this)">
        </th>
        <th style="width:52px">#</th>
        <th style="min-width:220px">Program</th>
        <th style="min-width:160px">Department</th>
        <th class="text-center" style="width:80px">Status</th>
        <th style="width:44px;padding:0;text-align:center;"></th>
      </tr></thead>
      <tbody id="progTableBody">
        <tr><td colspan="6" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
      </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Pagination -->
  <div class="acct-pagination" id="progPagination">
    <div class="apg-info" id="progPgInfo">Showing 1-10 of 0</div>
    <div class="apg-controls">
      <select class="apg-size-sel" id="progPageSize" onchange="progChangePageSize(this.value)">
        <option value="5">5 / page</option>
        <option value="10" selected>10 / page</option>
        <option value="25">25 / page</option>
      </select>
      <button class="apg-btn" id="progPgFirst" onclick="progGoPage(1)" title="First"><i class="fas fa-angles-left"></i></button>
      <button class="apg-btn" id="progPgPrev"  onclick="progGoPage(progPage-1)" title="Previous"><i class="fas fa-angle-left"></i></button>
      <div class="apg-pages" id="progPgPages"></div>
      <button class="apg-btn" id="progPgNext"  onclick="progGoPage(progPage+1)" title="Next"><i class="fas fa-angle-right"></i></button>
      <button class="apg-btn" id="progPgLast"  onclick="progGoPage(progTotalPages||1)" title="Last"><i class="fas fa-angles-right"></i></button>
    </div>
  </div>

  <!-- Hidden compat selects -->
  <select id="progDeptFilter" style="display:none;"></select>
  <select id="progStatusFilter" style="display:none;"></select>
  <!-- Hidden card grid kept so renderProgCards doesn't break if called -->
  <div id="progCardGrid" style="display:none;"></div>
</div>

<div id="section-deans" class="admin-section">
  <div class="page-header">
    <div>
      <div class="page-title">Dean Management</div>
      <div class="page-subtitle">Dean and secretary accounts, and department assignments</div>
    </div>
    <div class="tl-breadcrumb">
      <a href="#" onclick="showSection('dashboard');return false;">Dashboard</a>
      <span class="sep">/</span><span class="cur">Dean Management</span>
    </div>
  </div>
  <div class="tl-card">
    <div class="dean-tabs">
      <button class="dean-tab active" id="tabBtnAccounts" onclick="switchTab('accounts')">
        <i class="fas fa-id-card me-1"></i> Dean &amp; Secretary Accounts
        <span class="tab-count-badge" id="tabCntAcc">0</span>
      </button>
      <button class="dean-tab" id="tabBtnAssign" onclick="switchTab('assignments')">
        <i class="fas fa-sitemap me-1"></i> Department Assignments
        <span class="tab-count-badge" id="tabCntAssign">0</span>
      </button>
    </div>

    <!-- TAB 1 â€” Accounts -->
    <div class="dean-tab-panel active" id="panelAccounts">
      <div class="info-note mb-3">
        <i class="fas fa-info-circle"></i>
        <span>Create an account here first. Then go to the <strong>Department Assignments</strong> tab to assign them to a department.</span>
      </div>
      <!-- Toolbar -->
      <div class="acct-toolbar">
        <div class="search-wrap">
          <i class="fas fa-search s-icon"></i>
          <input type="text" id="accSearch" class="form-control" placeholder="Search by name, ID, or email..." autocomplete="off" oninput="applyAccFilter()">
        </div>
        <button class="filter-toggle-btn" id="accFilterToggle" onclick="tlToggleFilterPanel('accFilterPanel','accFilterToggle')">
          <i class="fas fa-sliders-h"></i>
          <span class="fbadge" id="accFilterBadge">0</span>
        </button>
        <button onclick="openAddDeanModal()"
          style="flex-shrink:0;border-radius:8px;padding:.45rem 1rem;border:1.5px solid var(--primary);background:var(--primary-light);color:var(--primary);font-size:.82rem;font-weight:700;display:inline-flex;align-items:center;gap:.4rem;transition:all .18s;white-space:nowrap;cursor:pointer;font-family:inherit;"
          onmouseover="this.style.background='var(--primary)';this.style.color='#fff'"
          onmouseout="this.style.background='var(--primary-light)';this.style.color='var(--primary)'">
          <i class="fas fa-user-plus" style="font-size:.78rem;"></i><span>Add Dean / Secretary</span>
        </button>
      </div>

      <div class="acct-content-layout">
        <!-- Sliding filter panel -->
        <div class="acct-filter-panel" id="accFilterPanel">
          <div class="afp-title">Filters
            <button class="afp-close" onclick="tlToggleFilterPanel('accFilterPanel','accFilterToggle')"><i class="fas fa-times"></i></button>
          </div>
          <div class="afp-group">
            <div class="afp-label">Role</div>
            <div class="afp-chips" id="accRoleChips">
              <button class="afp-chip active" onclick="tlSetHiddenFilter('accRoleFilter','',this,'accRoleChips','accFilterBadge',['accRoleFilter','accStatusFilter'],applyAccFilter)">All</button>
              <button class="afp-chip" onclick="tlSetHiddenFilter('accRoleFilter','dean',this,'accRoleChips','accFilterBadge',['accRoleFilter','accStatusFilter'],applyAccFilter)"><i class="fas fa-user-tie me-1" style="font-size:.7rem;"></i>Dean only</button>
              <button class="afp-chip" onclick="tlSetHiddenFilter('accRoleFilter','secretary',this,'accRoleChips','accFilterBadge',['accRoleFilter','accStatusFilter'],applyAccFilter)"><i class="fas fa-id-badge me-1" style="font-size:.7rem;"></i>Secretary only</button>
            </div>
            <select id="accRoleFilter" style="display:none;">
              <option value="">All Roles</option><option value="dean">Dean only</option><option value="secretary">Secretary only</option>
            </select>
          </div>
          <div class="afp-group">
            <div class="afp-label">Status</div>
            <div class="afp-chips" id="accStatusChips">
              <button class="afp-chip active" onclick="tlSetHiddenFilter('accStatusFilter','',this,'accStatusChips','accFilterBadge',['accRoleFilter','accStatusFilter'],applyAccFilter)">All</button>
              <button class="afp-chip" onclick="tlSetHiddenFilter('accStatusFilter','1',this,'accStatusChips','accFilterBadge',['accRoleFilter','accStatusFilter'],applyAccFilter)"><i class="fas fa-circle me-1" style="color:#16a34a;font-size:.55rem;"></i>Active</button>
              <button class="afp-chip" onclick="tlSetHiddenFilter('accStatusFilter','0',this,'accStatusChips','accFilterBadge',['accRoleFilter','accStatusFilter'],applyAccFilter)"><i class="fas fa-circle me-1" style="color:#adb5bd;font-size:.55rem;"></i>Inactive</button>
            </div>
            <select id="accStatusFilter" style="display:none;">
              <option value="">All Status</option><option value="1">Active</option><option value="0">Inactive</option>
            </select>
          </div>
          <button class="afp-reset" onclick="tlResetPanel('accFilterPanel','accFilterToggle',['accRoleFilter','accStatusFilter'],'accFilterBadge',['accRoleChips','accStatusChips'],applyAccFilter)">
            <i class="fas fa-rotate-left me-1"></i>Reset Filters
          </button>
        </div>

        <div class="acct-content-main">
          <!-- Table -->
          <div class="acct-table-wrap">
            <table class="table table-hover mb-0 tl-table" id="deanAccTable">
          <thead><tr>
            <th style="width:40px">#</th>
            <th style="min-width:160px">Full Name</th>
            <th style="min-width:200px">Assigned Department</th>
            <th class="text-center" style="width:85px">Role</th>
            <th class="text-center" style="width:80px">Status</th>
            <th class="text-center" style="width:115px">
              Email OTP <i class="fas fa-question-circle ms-1" style="cursor:help;font-size:.65rem;opacity:.55;" title="Locked until first login is complete"></i>
            </th>
            <th class="text-center" style="min-width:200px">Actions</th>
          </tr></thead>
          <tbody><tr><td colspan="7" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr></tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="acct-pagination" id="deanAccPagination">
            <div class="apg-info" id="deanAccPgInfo">Showing 0-0 of 0</div>
            <div class="apg-controls">
              <select class="apg-size-sel" id="deanAccPageSize" onchange="tlPgChangeSize('deanAcc',+this.value)">
                <option value="10" selected>10 / page</option>
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
              </select>
              <button class="apg-btn" id="deanAccPgFirst" onclick="tlPgGo('deanAcc',1)" title="First"><i class="fas fa-angles-left"></i></button>
              <button class="apg-btn" id="deanAccPgPrev"  onclick="tlPgGo('deanAcc',tlPgState.deanAcc.page-1)" title="Prev"><i class="fas fa-angle-left"></i></button>
              <div class="apg-pages" id="deanAccPgPages"></div>
              <button class="apg-btn" id="deanAccPgNext"  onclick="tlPgGo('deanAcc',tlPgState.deanAcc.page+1)" title="Next"><i class="fas fa-angle-right"></i></button>
              <button class="apg-btn" id="deanAccPgLast"  onclick="tlPgGo('deanAcc',tlPgState.deanAcc.total)" title="Last"><i class="fas fa-angles-right"></i></button>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- TAB 2 â€” Assignments -->
    <div class="dean-tab-panel" id="panelAssignments">
      <div class="info-note mb-3">
        <i class="fas fa-info-circle"></i>
        <span>Each department can have <strong>one Dean</strong> and optionally one Secretary. Create an account first in the Accounts tab, then assign here.</span>
      </div>
      <!-- Toolbar -->
      <div class="acct-toolbar">
        <div class="search-wrap">
          <i class="fas fa-search s-icon"></i>
          <input type="text" id="assignSearch" class="form-control" placeholder="Search by name or department..." autocomplete="off" oninput="applyAssignFilter()">
        </div>
        <button class="filter-toggle-btn" id="assignFilterToggle" onclick="tlToggleFilterPanel('assignFilterPanel','assignFilterToggle')">
          <i class="fas fa-sliders-h"></i>
          <span class="fbadge" id="assignFilterBadge">0</span>
        </button>
        <button onclick="openAssignModal(null)"
          style="flex-shrink:0;border-radius:8px;padding:.45rem 1rem;border:1.5px solid var(--primary);background:var(--primary-light);color:var(--primary);font-size:.82rem;font-weight:700;display:inline-flex;align-items:center;gap:.4rem;transition:all .18s;white-space:nowrap;cursor:pointer;font-family:inherit;"
          onmouseover="this.style.background='var(--primary)';this.style.color='#fff'"
          onmouseout="this.style.background='var(--primary-light)';this.style.color='var(--primary)'">
          <i class="fas fa-sitemap" style="font-size:.78rem;"></i><span>Assign to Department</span>
        </button>
      </div>

      <!-- Sliding filter panel -->
      <div class="acct-filter-panel" id="assignFilterPanel">
        <div class="afp-title">Filters
          <button class="afp-close" onclick="tlToggleFilterPanel('assignFilterPanel','assignFilterToggle')"><i class="fas fa-times"></i></button>
        </div>
        <div class="afp-group">
          <div class="afp-label">Role</div>
          <div class="afp-chips" id="assignRoleChips">
            <button class="afp-chip active" onclick="tlSetHiddenFilter('assignRoleFilter','',this,'assignRoleChips','assignFilterBadge',['assignRoleFilter'],applyAssignFilter)">All</button>
            <button class="afp-chip" onclick="tlSetHiddenFilter('assignRoleFilter','dean',this,'assignRoleChips','assignFilterBadge',['assignRoleFilter'],applyAssignFilter)"><i class="fas fa-user-tie me-1" style="font-size:.7rem;"></i>Dean</button>
            <button class="afp-chip" onclick="tlSetHiddenFilter('assignRoleFilter','secretary',this,'assignRoleChips','assignFilterBadge',['assignRoleFilter'],applyAssignFilter)"><i class="fas fa-id-badge me-1" style="font-size:.7rem;"></i>Secretary</button>
          </div>
          <select id="assignRoleFilter" style="display:none;">
            <option value="">All Roles</option><option value="dean">Dean</option><option value="secretary">Secretary</option>
          </select>
        </div>
        <button class="afp-reset" onclick="tlResetPanel('assignFilterPanel','assignFilterToggle',['assignRoleFilter'],'assignFilterBadge',['assignRoleChips'],applyAssignFilter)">
          <i class="fas fa-rotate-left me-1"></i>Reset Filters
        </button>
      </div>

      <!-- Table -->
      <div class="acct-table-wrap">
        <table class="table table-hover mb-0 tl-table" id="deanAssignTable">
          <thead><tr>
            <th style="width:40px">#</th>
            <th style="min-width:150px">Name</th>
            <th style="width:110px">ID No.</th>
            <th style="width:90px">Role</th>
            <th style="min-width:220px">Department</th>
            <th style="width:100px">Assigned</th>
            <th class="text-center" style="width:150px">Actions</th>
          </tr></thead>
          <tbody><tr><td colspan="7" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr></tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="acct-pagination" id="assignPagination">
        <div class="apg-info" id="assignPgInfo">Showing 0-0 of 0</div>
        <div class="apg-controls">
          <select class="apg-size-sel" id="assignPageSize" onchange="tlPgChangeSize('assign',+this.value)">
            <option value="10" selected>10 / page</option>
            <option value="25">25 / page</option>
            <option value="50">50 / page</option>
          </select>
          <button class="apg-btn" id="assignPgFirst" onclick="tlPgGo('assign',1)" title="First"><i class="fas fa-angles-left"></i></button>
          <button class="apg-btn" id="assignPgPrev"  onclick="tlPgGo('assign',tlPgState.assign.page-1)" title="Prev"><i class="fas fa-angle-left"></i></button>
          <div class="apg-pages" id="assignPgPages"></div>
          <button class="apg-btn" id="assignPgNext"  onclick="tlPgGo('assign',tlPgState.assign.page+1)" title="Next"><i class="fas fa-angle-right"></i></button>
          <button class="apg-btn" id="assignPgLast"  onclick="tlPgGo('assign',tlPgState.assign.total)" title="Last"><i class="fas fa-angles-right"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ACADEMIC STRUCTURE PAGE -->
<div id="section-academic" class="admin-section">
  <h2 class="sr-only">TERELEARN admin - unified Academic Structure settings</h2>
  <div class="acad-page">
    <div class="acad-head">
      <div>
        <div class="acad-title">Academic structure</div>
        <div class="acad-sub">Manage semesters, year levels, sections, and academic week labels from one compact panel.</div>
      </div>
      <span class="acad-crumb">Dashboard / Academic structure</span>
    </div>
    <div class="acad-tab-bar" role="tablist" aria-label="Academic structure settings">
      <button class="acad-tab on" type="button" data-acad-tab="sem" onclick="switchAcademicTab('sem',this)">
        <i class="fas fa-calendar-alt"></i> Semesters <span class="cnt" id="acadSemCount">0</span>
      </button>
      <button class="acad-tab" type="button" data-acad-tab="sec" onclick="switchAcademicTab('sec',this)">
        <i class="fas fa-layer-group"></i> Year and sections <span class="cnt" id="acadYsCount">0</span>
      </button>
      <button class="acad-tab" type="button" data-acad-tab="wk" onclick="switchAcademicTab('wk',this)">
        <i class="fas fa-clock"></i> Academic weeks <span class="cnt" id="acadWeekCount">0</span>
      </button>
    </div>
    <div class="acad-panel on" id="acad-tab-sem">
  <!-- Due-date alert banner (hidden until triggered) -->
  <div id="semDueBanner" style="display:none;background:linear-gradient(135deg,#fff3cd,#ffeaa7);border:1px solid #f59e0b;border-radius:10px;padding:10px 14px;margin-bottom:14px;align-items:center;gap:10px;font-size:.82rem;">
    <i class="fas fa-exclamation-triangle" style="color:#b45309;flex-shrink:0;"></i>
    <div style="flex:1;">
      <strong style="color:#78350f;">Semester ended</strong>
      <span id="semDueBannerText" style="color:#92400e;margin-left:6px;"></span>
    </div>
    <button class="btn btn-primary btn-sm" type="button" onclick="semOpenTransitionWizard()">
      <i class="fas fa-arrow-right"></i> Start transition
    </button>
    <button onclick="document.getElementById('semDueBanner').style.display='none'" style="border:none;background:none;color:#92400e;cursor:pointer;font-size:1rem;padding:0 4px;line-height:1;">Ã—</button>
  </div>

  <!-- Active banner -->
  <div id="semActiveBanner" class="acad-active-banner">
    <div class="acad-active-main">
      <span class="acad-active-dot"></span>
      Active period:
      <strong id="semActiveBannerText" class="acad-active-val">-</strong>
    </div>
    <button class="btn btn-primary btn-sm" type="button" onclick="openSemAdd()">
      <i class="fas fa-plus"></i> Add period
    </button>
  </div>

  <!-- Period list -->
  <div id="semPeriodList" style="display:flex;flex-direction:column;gap:10px;">
    <div style="text-align:center;padding:2rem;color:var(--text-muted);">
      <i class="fas fa-spinner fa-spin"></i> Loading...
    </div>
  </div>
    </div>

    <div class="acad-panel" id="acad-tab-sec">
      <div class="acad-toolbar">
        <div>
          <div class="acad-section-label">All programs overview</div>
          <div class="acad-sub">Click a configured section count or the row menu to edit a program.</div>
        </div>
        <button class="btn btn-primary btn-sm" type="button" onclick="openAddYsModal()">
          <i class="fas fa-plus"></i> Add config
        </button>
      </div>

      <div class="acad-table-wrap">
        <table class="prog-tbl" id="ysOverviewTable">
          <thead>
            <tr>
              <th style="width:42px">#</th>
              <th style="width:82px">Code</th>
              <th>Program name</th>
              <th style="width:90px;text-align:center">1st year</th>
              <th style="width:90px;text-align:center">2nd year</th>
              <th style="width:90px;text-align:center">3rd year</th>
              <th style="width:90px;text-align:center">4th year</th>
              <th style="width:42px"></th>
            </tr>
          </thead>
          <tbody id="ysOverviewBody">
            <tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
          </tbody>
        </table>
      </div>
      <p class="acad-help">Dash means no sections are configured for that year level in the active setup.</p>
    </div>

    <div class="acad-panel" id="acad-tab-wk">
      <div class="acad-info-banner">
        <i class="fas fa-info-circle"></i>
        <span>One academic week count applies to every semester and every year level.</span>
      </div>

      <div class="acad-weeks-grid" id="academicWeeksGrid">
        <div style="grid-column:1/-1;text-align:center;padding:2rem;color:var(--text-muted);">
          <i class="fas fa-spinner fa-spin"></i> Loading week setting...
        </div>
      </div>

      <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
        <button class="btn btn-primary" type="button" onclick="saveAcademicWeeks()">
          <i class="fas fa-save"></i> Save week configuration
        </button>
      </div>
      <div id="wk-toast" class="acad-toast" style="margin-top:10px"><i class="fas fa-check-circle"></i> Week configuration saved.</div>
    </div>
  </div>
</div>

<!-- SEM ADD/EDIT MODAL -->
<div class="modal fade tl-modal" id="semModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-calendar-alt me-2"></i><span id="semModalTitle">Add Period</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><div class="modal-body-inner">
        <input type="hidden" id="semEditId">
        <div class="form-section">
          <div class="section-label green"><i class="fas fa-calendar"></i> Period Info</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">School Year <span class="text-danger">*</span></label>
              <input type="text" id="semYear" class="form-control" placeholder="e.g. 2026-2027">
            </div>
            <div class="col-md-6">
              <label class="form-label">Semester <span class="text-danger">*</span></label>
              <select id="semSem" class="form-select">
                <option value="">- Select -</option>
                <option value="1st Semester">1st Semester</option>
                <option value="2nd Semester">2nd Semester</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label">Start Date</label>
              <input type="date" id="semStart" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">End Date <small class="text-muted fw-normal" style="text-transform:none;letter-spacing:0;">(due date trigger)</small></label>
              <input type="date" id="semEnd" class="form-control">
            </div>
          </div>
          <div class="mt-3 d-flex align-items-center gap-2">
            <input type="checkbox" id="semSetActive" class="form-check-input" style="width:18px;height:18px;accent-color:var(--primary);cursor:pointer;">
            <label for="semSetActive" class="form-label mb-0" style="cursor:pointer;font-size:.85rem;">Set as active period</label>
          </div>
        </div>
        <!-- Duplicate warning -->
        <div id="semDupeWarning" style="display:none;background:#fdecea;border:1.5px solid rgba(217,48,37,.3);border-radius:8px;padding:.65rem 1rem;font-size:.8rem;color:var(--danger);display:none;align-items:center;gap:.5rem;">
          <i class="fas fa-exclamation-circle"></i>
          <span>A period with this school year and semester already exists.</span>
        </div>
      </div></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
        <button type="button" class="btn btn-success text-white fw-bold" id="semSaveBtn" onclick="saveSemPeriod()">
          <i class="fas fa-check me-1"></i> Save
        </button>
      </div>
    </div>
  </div>
</div>

<!-- SEMESTER TRANSITION WIZARD MODAL -->
<div class="modal fade tl-modal" id="semTransitionModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:540px;">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#b45309,#f59e0b);">
        <h5 class="modal-title" style="color:#fff;"><i class="fas fa-arrow-right me-2"></i> Semester Transition</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><div class="modal-body-inner">
        <div style="background:var(--primary-light);border:1px solid rgba(26,158,120,.2);border-radius:10px;padding:.85rem 1.1rem;margin-bottom:1rem;font-size:.82rem;color:var(--primary);display:flex;align-items:center;gap:.5rem;">
          <i class="fas fa-info-circle"></i>
          <span>The current semester has ended. Here is the auto-generated next period.</span>
        </div>
        <div class="form-section">
          <div class="section-label green"><i class="fas fa-forward"></i> Next Suggested Period</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">School Year <span class="text-danger">*</span></label>
              <input type="text" id="transYear" class="form-control" placeholder="e.g. 2026-2027">
            </div>
            <div class="col-md-6">
              <label class="form-label">Semester <span class="text-danger">*</span></label>
              <select id="transSem" class="form-select">
                <option value="1st Semester">1st Semester</option>
                <option value="2nd Semester">2nd Semester</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label">Start Date</label>
              <input type="date" id="transStart" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">End Date</label>
              <input type="date" id="transEnd" class="form-control">
            </div>
          </div>
          <div class="mt-3 d-flex align-items-center gap-2">
            <input type="checkbox" id="transSetActive" class="form-check-input" checked style="width:18px;height:18px;accent-color:var(--primary);cursor:pointer;">
            <label for="transSetActive" class="form-label mb-0" style="cursor:pointer;font-size:.85rem;">Set new period as active immediately</label>
          </div>
        </div>
      </div></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
        <button type="button" class="btn btn-success text-white fw-bold" id="transConfirmBtn" onclick="confirmSemTransition()">
          <i class="fas fa-check me-1"></i> Confirm &amp; Create
        </button>
      </div>
    </div>
  </div>
</div>

<!-- â”€â”€ YEAR & SECTIONS â”€â”€ -->
<div class="modal fade tl-modal ys-modal" id="ysModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i><span id="ysModalTitle">Add Year Level Config</span></h5>
          <div id="ysModalContext" style="font-size:.76rem;opacity:.88;margin-top:.18rem;">Choose a department and program.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><div class="modal-body-inner">
        <input type="hidden" id="ysEditId">
        <div class="form-section">
          <div class="section-label green"><i class="fas fa-diagram-project"></i> Configuration Details</div>
          <div class="ys-modal-grid">
            <div class="ys-field">
              <label for="ysDept">Department <span class="text-danger">*</span></label>
              <select id="ysDept" class="form-select" onchange="ysOnDeptChange(this.value)">
                <option value="">- Select Department -</option>
              </select>
            </div>
            <div class="ys-field">
              <label for="ysCourse">Program <span class="text-danger">*</span></label>
              <select id="ysCourse" class="form-select" onchange="ysOnCourseChange(this.value)">
                <option value="">- Select Program -</option>
              </select>
            </div>
            <div class="ys-field">
              <label for="ysYear">Year Level <span class="text-danger">*</span></label>
              <select id="ysYear" class="form-select">
                <option value="">- Select Year Level -</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
              </select>
            </div>
            <div class="ys-field">
              <label for="ysSectionCount">Sections <span class="text-danger">*</span></label>
              <input type="number" id="ysSectionCount" class="form-control" min="1" max="30" placeholder="4">
            </div>
          </div>
        </div>
      </div></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" onclick="ysResetForm()" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
        <button type="button" class="btn btn-light" onclick="ysResetForm()"><i class="fas fa-undo me-1"></i> Reset</button>
        <button type="button" class="btn btn-success text-white fw-bold" id="ysSaveBtn" onclick="saveYsConfig()">
          <i class="fas fa-save me-1"></i> <span id="ysSaveBtnText">Save Config</span>
        </button>
      </div>
    </div>
  </div>
</div>

<div id="section-calendar" class="admin-section">
  <div class="page-header">
    <div>
      <div class="page-title"><i class="fas fa-calendar-day me-2" style="color:var(--primary);"></i>Calendar</div>
      <div class="page-subtitle">Quizzes, activities, exams and assignments by due date.</div>
    </div>
  </div>

  <div class="cal-toolbar">
    <button class="cal-nav-btn" onclick="calPrevMonth()" title="Previous month"><i class="fas fa-chevron-left"></i></button>
    <div class="cal-month-label" id="calMonthLabel">â€”</div>
    <button class="cal-nav-btn" onclick="calNextMonth()" title="Next month"><i class="fas fa-chevron-right"></i></button>
    <button class="cal-today-btn" onclick="calGoToday()"><i class="fas fa-dot-circle"></i> Today</button>
    <div class="cal-legend">
      <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#d93025;"></span>Quiz</span>
      <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#1f73db;"></span>Activity</span>
      <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#7b1fa2;"></span>Exam</span>
      <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#f59e0b;"></span>Assignment</span>
    </div>
  </div>

  <div class="cal-card">
    <div class="cal-weekdays">
      <div class="cal-weekday">Sun</div><div class="cal-weekday">Mon</div>
      <div class="cal-weekday">Tue</div><div class="cal-weekday">Wed</div>
      <div class="cal-weekday">Thu</div><div class="cal-weekday">Fri</div>
      <div class="cal-weekday">Sat</div>
    </div>
    <div class="cal-grid" id="calGrid">
      <div class="cal-day" style="grid-column:span 7;text-align:center;color:var(--text-muted);min-height:60px;">
        <i class="fas fa-spinner fa-spin me-2"></i>Loading...
      </div>
    </div>
  </div>

  <div class="cal-day-detail" id="calDayDetail" style="display:none;">
    <div class="cal-detail-title"><i class="fas fa-list" style="color:var(--primary);"></i> <span id="calDayDetailTitle">Selected day</span></div>
    <div id="calDayDetailList"></div>
  </div>
</div>

  <div id="section-students" class="admin-section">
    <div class="page-header">
      <div>
        <div class="page-title">Students</div>
        <div class="page-subtitle">All student accounts across all programs</div>
      </div>
      <div class="tl-breadcrumb">
        <a href="#" onclick="showSection('dashboard');return false;">Dashboard</a>
        <span class="sep">/</span><span class="cur">Students</span>
      </div>
    </div>
    <div class="tl-card">
      <!-- Toolbar -->
      <div class="acct-toolbar">
        <div class="search-wrap">
          <i class="fas fa-search s-icon"></i>
          <input type="text" id="allStuSearch" class="form-control" placeholder="Search by name, ID, email, course..." autocomplete="off" oninput="applyAllStuFilter()">
        </div>
        <button class="filter-toggle-btn" id="stuFilterToggle" onclick="tlToggleFilterPanel('stuFilterPanel','stuFilterToggle')">
          <i class="fas fa-sliders-h"></i>
          <span class="fbadge" id="stuFilterBadge">0</span>
        </button>
        <button onclick="openAddAllStuModal()"
          style="flex-shrink:0;border-radius:8px;padding:.45rem 1rem;border:1.5px solid var(--primary);background:var(--primary-light);color:var(--primary);font-size:.82rem;font-weight:700;display:inline-flex;align-items:center;gap:.4rem;transition:all .18s;white-space:nowrap;cursor:pointer;font-family:inherit;"
          onmouseover="this.style.background='var(--primary)';this.style.color='#fff'"
          onmouseout="this.style.background='var(--primary-light)';this.style.color='var(--primary)'">
          <i class="fas fa-user-graduate" style="font-size:.78rem;"></i><span>Add Student</span>
        </button>
      </div>

      <!-- Sliding filter panel -->
      <div class="acct-filter-panel" id="stuFilterPanel">
        <div class="afp-title">Filters
          <button class="afp-close" onclick="tlToggleFilterPanel('stuFilterPanel','stuFilterToggle')"><i class="fas fa-times"></i></button>
        </div>
        <div class="afp-group">
          <div class="afp-label">Program</div>
          <select id="allStuCourseFilter" class="form-select" style="border-radius:8px;border:1.5px solid var(--border);font-size:.8rem;background:var(--surface);color:var(--text);"
            onchange="applyAllStuFilter();tlUpdateBadge('stuFilterBadge',['allStuCourseFilter','allStuStatusFilter'])">
            <option value="">All Programs</option>
          </select>
        </div>
        <div class="afp-group">
          <div class="afp-label">Status</div>
          <div class="afp-chips" id="stuStatusChips">
            <button class="afp-chip active" onclick="tlSetHiddenFilter('allStuStatusFilter','',this,'stuStatusChips','stuFilterBadge',['allStuCourseFilter','allStuStatusFilter'],applyAllStuFilter)">All</button>
            <button class="afp-chip" onclick="tlSetHiddenFilter('allStuStatusFilter','1',this,'stuStatusChips','stuFilterBadge',['allStuCourseFilter','allStuStatusFilter'],applyAllStuFilter)"><i class="fas fa-circle me-1" style="color:#16a34a;font-size:.55rem;"></i>Active</button>
            <button class="afp-chip" onclick="tlSetHiddenFilter('allStuStatusFilter','0',this,'stuStatusChips','stuFilterBadge',['allStuCourseFilter','allStuStatusFilter'],applyAllStuFilter)"><i class="fas fa-circle me-1" style="color:#adb5bd;font-size:.55rem;"></i>Inactive</button>
          </div>
          <select id="allStuStatusFilter" style="display:none;">
            <option value="">All Status</option><option value="1">Active</option><option value="0">Inactive</option>
          </select>
        </div>
        <button class="afp-reset" onclick="tlResetPanel('stuFilterPanel','stuFilterToggle',['allStuCourseFilter','allStuStatusFilter'],'stuFilterBadge',['stuStatusChips'],applyAllStuFilter)">
          <i class="fas fa-rotate-left me-1"></i>Reset Filters
        </button>
      </div>

      <!-- Table -->
      <div class="acct-table-wrap">
        <table class="table table-hover mb-0 tl-table" id="allStuTable">
          <thead><tr>
            <th style="width:40px">#</th>
            <th style="width:105px">Student ID</th>
            <th style="min-width:160px">Name / Email</th>
            <th style="width:90px">Course</th>
            <th class="text-center" style="width:55px">Year</th>
            <th class="text-center" style="width:65px">Section</th>
            <th class="text-center" style="width:80px">Status</th>
            <th class="text-center" style="width:115px">Email OTP <i class="fas fa-question-circle ms-1" style="cursor:help;font-size:.65rem;opacity:.55;" title="Locked until first login is complete"></i></th>
            <th class="text-center" style="min-width:180px">Actions</th>
          </tr></thead>
          <tbody><tr><td colspan="9" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr></tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="acct-pagination" id="stuPagination">
        <div class="apg-info" id="stuPgInfo">Showing 0-0 of 0</div>
        <div class="apg-controls">
          <select class="apg-size-sel" id="stuPageSize" onchange="tlPgChangeSize('stu',+this.value)">
            <option value="10" selected>10 / page</option>
            <option value="25">25 / page</option>
            <option value="50">50 / page</option>
          </select>
          <button class="apg-btn" id="stuPgFirst" onclick="tlPgGo('stu',1)" title="First"><i class="fas fa-angles-left"></i></button>
          <button class="apg-btn" id="stuPgPrev"  onclick="tlPgGo('stu',tlPgState.stu.page-1)" title="Prev"><i class="fas fa-angle-left"></i></button>
          <div class="apg-pages" id="stuPgPages"></div>
          <button class="apg-btn" id="stuPgNext"  onclick="tlPgGo('stu',tlPgState.stu.page+1)" title="Next"><i class="fas fa-angle-right"></i></button>
          <button class="apg-btn" id="stuPgLast"  onclick="tlPgGo('stu',tlPgState.stu.total)" title="Last"><i class="fas fa-angles-right"></i></button>
        </div>
      </div>
    </div>
  </div>

</main>

<!-- FIXED FOOTER -->
<footer class="tl-footer">
  <span class="p-2">Copyright &copy; 2025-2026 <strong style="margin-left:.3rem;">TERELEARN</strong></span>
  <span class="footer-sem-badge">
    <i class="fas fa-calendar-alt"></i>
    <span id="footerSem">Loading...</span>
  </span>
</footer>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     MODALS (unchanged from original)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->

<!-- DEPARTMENT MODAL -->
<div class="modal fade tl-modal" id="deptModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-university me-2"></i><span id="deptModalTitle">Add Department</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="deptForm"><input type="hidden" id="dept_id">
          <div class="modal-body-inner">
            <div class="form-section">
              <div class="section-label green"><i class="fas fa-id-card"></i> Department Info</div>
              <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Code <span class="text-danger">*</span></label><input type="text" class="form-control" id="dept_code" placeholder="e.g. SIT" maxlength="20" required></div>
                <div class="col-md-9"><label class="form-label">Department Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="dept_name" placeholder="e.g. School of Information Technology" required></div>
              </div>
              <div class="row g-3 mt-1"><div class="col-12"><label class="form-label">Description</label><textarea class="form-control" id="dept_desc" rows="2" placeholder="Brief description..."></textarea></div></div>
              <div class="row g-3 mt-1">
                <div class="col-12">
                  <label class="form-label">Department Photo</label>
                  <div class="dept-image-upload">
                    <div class="dept-image-preview" id="deptImagePreview"></div>
                    <div>
                      <input type="file" class="form-control" id="dept_image_file" accept="image/png,image/jpeg,image/webp,image/gif">
                      <div class="dept-image-hint">Upload a department image or logo. Accepted: JPG, PNG, WEBP, GIF. Max 5 MB.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-section">
              <div class="section-label green"><i class="fas fa-graduation-cap"></i> Link Programs to this Department
                <small class="ms-auto fw-normal text-muted" style="text-transform:none;letter-spacing:0;font-size:.7rem;">Programs scoped to dean login</small>
              </div>
              <div class="row g-2 mb-3">
                <div class="col">
                  <select class="form-select form-select-sm" id="progSelectAdd" style="border-radius:7px;border:1.5px solid var(--border);">
                    <option value="">- Select a program to link -</option>
                  </select>
                </div>
                <div class="col-auto">
                  <button type="button" class="btn btn-sm btn-success fw-bold px-3" onclick="addProgToDept()" style="border-radius:7px;">
                    <i class="fas fa-plus me-1"></i> Link
                  </button>
                </div>
              </div>
              <div class="prog-link-section" id="deptProgList">
                <div class="prog-list-empty">No programs linked yet. Select from the dropdown above.</div>
              </div>
              <small class="text-muted d-block mt-2" style="font-size:.72rem;">
                <i class="fas fa-info-circle me-1"></i>
                When a Dean of this department logs in, they can only create subjects, classes, and students under the programs linked here.
              </small>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
        <button type="button" class="btn btn-success text-white fw-bold" onclick="submitDept()"><i class="fas fa-check me-1"></i> Save Department</button>
      </div>
    </div>
  </div>
</div>

<!-- ADD / EDIT DEAN MODAL -->
<div class="modal fade tl-modal" id="addDeanModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i><span id="addDeanModalTitle">Add Dean / Secretary Account</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="addDeanForm"><input type="hidden" id="dean_edit_id">
          <div class="modal-body-inner">
            <div class="form-section">
              <div class="section-label green"><i class="fas fa-id-card"></i> Basic Information</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Faculty / Staff ID No. <span class="text-danger">*</span></label>
                  <div class="input-group"><span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                    <input type="text" class="form-control" id="dean_id_number" placeholder="00-00000" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Account Type</label>
                  <div style="display:inline-flex;align-items:center;gap:6px;background:var(--primary-light);color:var(--primary-dark);border:1.5px solid rgba(26,158,120,.3);border-radius:8px;padding:.5rem .85rem;font-size:.85rem;font-weight:600;width:100%;">
                    <i class="fas fa-user-tie"></i> Dean / Secretary
                  </div>
                </div>
              </div>
            </div>
            <div class="form-section">
              <div class="section-label green"><i class="fas fa-user"></i> Full Name</div>
              <div class="row g-3">
                <div class="col-md-3"><label class="form-label">First Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="dean_first_name" placeholder="First name" required></div>
                <div class="col-md-3"><label class="form-label">Middle Name</label><input type="text" class="form-control" id="dean_middle_name" placeholder="Leave blank if none"></div>
                <div class="col-md-3"><label class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="dean_last_name" placeholder="Last name" required></div>
                <div class="col-md-3"><label class="form-label">Suffix</label><input type="text" class="form-control" id="dean_suffix" placeholder="Leave blank if none"></div>
              </div>
            </div>
            <div class="form-section">
              <div class="section-label green"><i class="fas fa-address-book"></i> Contact &amp; Login Credentials</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Email Address <span class="text-danger">*</span></label>
                  <div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="dean_email" placeholder="admin@gmail.com" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone Number</label>
                  <div class="input-group"><span class="input-group-text">+63</span>
                    <input type="text" class="form-control" id="dean_phone"
                      placeholder="000-000-0000" maxlength="12" inputmode="numeric" autocomplete="tel">
                  </div>
                </div>
              </div>
              <div class="row g-3 mt-0">
                <div class="col-md-3">
                  <label class="form-label">Username <span class="text-danger">*</span></label>
                  <div class="input-group"><span class="input-group-text">@</span>
                    <input type="text" class="form-control" id="dean_username" placeholder="username" required>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Birthdate <span class="text-danger">*</span></label>
                  <div class="input-group"><span class="input-group-text"><i class="fas fa-calendar"></i></span>
                    <input type="date" class="form-control" id="dean_birthdate" required>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Password <span class="text-danger">*</span>
                    <button type="button" class="auto-pw-btn ms-1" onclick="autoDeanPassword()">â†º Auto</button>
                  </label>
                  <div class="input-group"><span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="text" class="form-control" id="dean_password" placeholder="Set a password">
                  </div>
                  <small class="text-muted" style="font-size:.72rem;" id="pwHintEdit">Leave blank to keep current password</small>
                </div>
              </div>
            </div>
            <div class="form-section">
              <div class="section-label green"><i class="fas fa-chalkboard-teacher"></i> Faculty Role</div>
              <div style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1.5px solid #fcd34d;border-radius:10px;padding:.85rem 1.1rem;display:flex;align-items:center;gap:14px;">
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" id="dean_is_also_faculty" style="width:2.2em;height:1.2em;cursor:pointer;border-color:#f59e0b;">
                </div>
                <div>
                  <label for="dean_is_also_faculty" style="font-size:.88rem;font-weight:700;color:#78350f;cursor:pointer;margin:0;">
                    <i class="fas fa-user-tie me-1"></i> This person also teaches as a <strong>Professor</strong>
                  </label>
                  <div style="font-size:.72rem;color:#92400e;margin-top:.18rem;">
                    Enabling this will also add/update their record in the Faculty list with the Dean flag turned on.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
        <button type="button" class="btn btn-light" onclick="document.getElementById('addDeanForm').reset();document.getElementById('dean_is_also_faculty').checked=false;"><i class="fas fa-undo me-1"></i> Reset</button>
        <button type="button" class="btn btn-success text-white fw-bold" id="addDeanSubmitBtn" onclick="submitDeanAccount()">
          <i class="fas fa-check me-1"></i> <span id="addDeanSubmitLabel">Save Account</span>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade tl-modal" id="assignModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-sitemap me-2"></i><span id="assignModalTitle">Assign to Department</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><div class="modal-body-inner">
        <input type="hidden" id="assign_edit_id">

        <!-- Person selector (hidden on edit) -->
        <div class="form-section" id="assignPersonSection">
          <div class="section-label green"><i class="fas fa-user-tie"></i> Select Dean / Secretary</div>
          <div style="position:relative;margin-bottom:.6rem;">
            <i class="fas fa-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:#adb5bd;font-size:.78rem;pointer-events:none;z-index:2;"></i>
            <input type="text" id="assignPersonSearch" class="form-control" placeholder="Type name or ID to filter..."
              style="padding-left:2.1rem;border:1.5px solid var(--border);border-radius:8px;font-size:.88rem;" autocomplete="off">
          </div>
          <div class="person-list-wrap" id="assignPersonList">
            <div class="text-center text-muted py-2" style="font-size:.82rem;"><i class="fas fa-spinner fa-spin me-1"></i> Loading...</div>
          </div>
          <input type="hidden" id="assignPersonId">
          <!-- Preview -->
          <div id="personPreview" style="display:none;margin-top:.5rem;padding:.5rem .75rem;
               background:var(--primary-light);border:1.5px solid rgba(26,158,120,.25);border-radius:8px;
               font-size:.82rem;color:var(--primary);font-weight:600;align-items:center;gap:.5rem;">
            <i class="fas fa-check-circle"></i><span id="personPreviewName">â€”</span>
          </div>
        </div>

        <!-- Dept selector -->
        <div class="form-section" id="assignDeptSection">
          <div class="section-label green"><i class="fas fa-university"></i> Department <span class="text-danger">*</span></div>
          <select class="form-select" id="assignDeptId" style="border:1.5px solid var(--border);border-radius:8px;" onchange="checkDeptConflict()">
            <option value="">- Select Department -</option>
          </select>
          <!-- Conflict warning -->
          <div id="deptConflictWarn" style="display:none;margin-top:.55rem;" class="dep-has-data-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="deptConflictMsg">This department already has a Dean assigned. Saving will replace the existing assignment.</span>
          </div>
        </div>

        <!-- Role -->
        <div class="form-section" id="assignRoleSection">
          <div class="section-label green"><i class="fas fa-shield-alt"></i> Role</div>
          <div class="d-flex gap-3">
            <label class="role-pick-card" for="role_dean" style="flex:1;">
              <input class="form-check-input visually-hidden" type="radio" name="assign_role" id="role_dean" value="dean" checked onchange="checkDeptConflict()">
              <div class="rpc-icon" style="background:#fffbeb;color:#b45309;"><i class="fas fa-user-graduate"></i></div>
              <div><div class="rpc-label">Dean</div><div class="rpc-sub">Full sub-admin access to department</div></div>
            </label>
            <label class="role-pick-card" for="role_sec" style="flex:1;">
              <input class="form-check-input visually-hidden" type="radio" name="assign_role" id="role_sec" value="secretary" onchange="checkDeptConflict()">
              <div class="rpc-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-id-badge"></i></div>
              <div><div class="rpc-label">Secretary</div><div class="rpc-sub">Limited view access</div></div>
            </label>
          </div>
        </div>

        <!-- Edit summary (shown on edit mode) -->
        <div id="assignEditSummary" style="display:none;"></div>
      </div></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
        <button type="button" class="btn btn-success text-white fw-bold" id="assignSubmitBtn" onclick="submitAssign()">
          <i class="fas fa-check me-1"></i> <span id="assignSubmitLabel">Assign</span>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade tl-modal" id="progModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-graduation-cap me-2"></i><span id="progModalTitle">Add Program</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><div class="modal-body-inner">
        <input type="hidden" id="prog_edit_id">
        <div class="form-section">
          <div class="section-label green"><i class="fas fa-id-card"></i> Program Info</div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="prog_code" placeholder="e.g. BSIT" maxlength="20"
                oninput="prog_code.value=prog_code.value.toUpperCase()">
              <div id="prog_code_err" class="field-error" style="display:none;"></div>
            </div>
            <div class="col-md-8">
              <label class="form-label">Program Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="prog_name" placeholder="e.g. Bachelor of Science in Information Technology">
              <div id="prog_name_err" class="field-error" style="display:none;"></div>
            </div>
          </div>
        </div>
        <div class="form-section">
          <div class="section-label green"><i class="fas fa-university"></i> Department <span class="text-danger">*</span></div>
          <select class="form-select" id="prog_dept_id" style="border:1.5px solid var(--border);border-radius:8px;" onchange="progDeptChanged()">
            <option value="">â€” Select Department â€”</option>
          </select>
          <div id="prog_dept_err" class="field-error" style="display:none;"></div>
          <small class="text-muted d-block mt-1" style="font-size:.72rem;">
            <i class="fas fa-info-circle me-1"></i>
            Programs must belong to a department. You can reassign them later.
          </small>
          <!-- Same-dept duplicate warning -->
          <div id="progDupeWarn" style="display:none;margin-top:.5rem;" class="dep-has-data-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>A program with this code already exists in the selected department.</span>
          </div>
        </div>
      </div></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
        <button type="button" class="btn btn-success text-white fw-bold" id="progSubmitBtn" onclick="submitProgram()">
          <i class="fas fa-check me-1"></i> <span id="progSubmitLabel">Save Program</span>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade tl-modal" id="acctModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:16px;overflow:hidden;">

      <div class="modal-header" style="padding:.9rem 1.4rem;">
        <h5 class="modal-title">
          <i class="fas fa-user-plus me-2"></i>
          <span id="acctModalTitle">Add Account</span>
        </h5> 
      </div>

      <div class="wiz-steps" id="wizStepsBar">
        <div class="wiz-step active" id="wizStep0">
          <div class="wiz-dot"><i class="fas fa-tag"></i></div>
          <div class="wiz-label">Type</div>
        </div>
        <div class="wiz-step" id="wizStep1">
          <div class="wiz-dot"><i class="fas fa-id-card"></i></div>
          <div class="wiz-label">Basic Info</div>
        </div>
        <div class="wiz-step" id="wizStep2">
          <div class="wiz-dot"><i class="fas fa-at"></i></div>
          <div class="wiz-label">Credentials</div>
        </div>
        <div class="wiz-step" id="wizStep3">
          <div class="wiz-dot"><i class="fas fa-check"></i></div>
          <div class="wiz-label">Review</div>
        </div>
      </div>

      <div class="modal-body" style="padding:0;background:var(--bg);">
        <div style="padding:1.1rem 1.35rem 1.35rem;">
          <input type="hidden" id="acct_edit_id">
          <input type="hidden" id="acct_edit_table">

          <div class="wiz-panel active" id="wizPanel0">
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.55rem;">
              Select the account type to create:
            </div>
            <div class="wiz-type-grid">
              <div class="wiz-type-card" id="wt_admin" onclick="wizSelectType('admin', { autoAdvance: true })">
                <div class="wiz-check"><i class="fas fa-check"></i></div>
                <div class="wiz-type-icon" style="background:#e0f2fe;color:#0284c7;">
                  <i class="fas fa-shield-alt"></i>
                </div>
                <div class="wiz-type-name" style="color:#0284c7;">Admin</div>
                <div class="wiz-type-desc">Full system access &amp; configuration</div>
              </div>
              <div class="wiz-type-card" id="wt_dean" onclick="wizSelectType('dean', { autoAdvance: true })">
                <div class="wiz-check"><i class="fas fa-check"></i></div>
                <div class="wiz-type-icon" style="background:#fef3c7;color:#b45309;">
                  <i class="fas fa-user-tie"></i>
                </div>
                <div class="wiz-type-name" style="color:#b45309;">Dean</div>
                <div class="wiz-type-desc">Department head - manages programs &amp; faculty</div>
              </div>
              <div class="wiz-type-card" id="wt_secretary" onclick="wizSelectType('secretary', { autoAdvance: true })">
                <div class="wiz-check"><i class="fas fa-check"></i></div>
                <div class="wiz-type-icon" style="background:#dbeafe;color:#1d4ed8;">
                  <i class="fas fa-id-badge"></i>
                </div>
                <div class="wiz-type-name" style="color:#1d4ed8;">Secretary</div>
                <div class="wiz-type-desc">Limited view access for the department</div>
              </div>
            </div>
             <div id="wizAlsoFacWrap" style="display:none;margin-top:.6rem;background:var(--primary-light);border:1.5px solid rgba(26,158,120,.3);border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;gap:12px;">
        <label class="otp-toggle-wrap" style="margin:0;flex-shrink:0;">
          <div class="otp-switch">
            <input type="checkbox" id="acct_is_also_faculty">
            <span class="otp-slider"></span>
          </div>
        </label>
        <div style="flex:1;">
          <div style="font-weight:700;color:var(--primary);">This person also teaches as Faculty</div>
          <div style="font-size:.78rem;color:var(--text-muted);">This account will also appear in Faculty list and have teaching privileges</div>
        </div>
      </div>
          </div>

          <div class="wiz-panel" id="wizPanel1">
            <div class="form-section" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:1.1rem 1.3rem;margin-bottom:.85rem;">
              <div class="section-label green" style="font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--primary);border-bottom:2px solid var(--primary-light);padding-bottom:.4rem;margin-bottom:.85rem;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-id-card"></i> Basic Information
              </div>
              <label class="form-label">Employee ID <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text" style="background:var(--primary-light);border:1.5px solid var(--border);color:var(--primary);font-size:.8rem;">
                  <i class="fas fa-id-badge"></i>
                </span>
                <input type="text" class="form-control" id="acct_id_number"
                  placeholder="00-00000"
                  style="border:1.5px solid var(--border);border-radius:0 8px 8px 0;">
              </div>
              <div id="err_id_number" class="field-error" style="display:none;"></div>
            </div>
            <div class="form-section" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:1.1rem 1.3rem;">
              <div class="section-label green" style="font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--primary);border-bottom:2px solid var(--primary-light);padding-bottom:.4rem;margin-bottom:.85rem;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-user"></i> Full Name
              </div>
              <div class="row g-3">
                <div class="col-md-3">
                  <label class="form-label" style="font-size:.74rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="acct_first_name"
                    placeholder="e.g. Juan"
                    style="border:1.5px solid var(--border);border-radius:8px;">
                  <div id="err_first_name" class="field-error" style="display:none;"></div>
                </div>
                <div class="col-md-3">
                  <label class="form-label" style="font-size:.74rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">Middle Name</label>
                  <input type="text" class="form-control" id="acct_middle_name"
                    placeholder="Leave blank if none"
                    style="border:1.5px solid var(--border);border-radius:8px;">
                </div>
                <div class="col-md-3">
                  <label class="form-label" style="font-size:.74rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="acct_last_name"
                    placeholder="e.g. Dela Cruz"
                    style="border:1.5px solid var(--border);border-radius:8px;">
                  <div id="err_last_name" class="field-error" style="display:none;"></div>
                </div>
                <div class="col-md-3">
                  <label class="form-label" style="font-size:.74rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">Suffix</label>
                  <input type="text" class="form-control" id="acct_suffix"
                    placeholder="Leave blank if none"
                    style="border:1.5px solid var(--border);border-radius:8px;">
                </div>
              </div>
            </div>
          </div>

          <div class="wiz-panel" id="wizPanel2">
            <div class="form-section" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:1.1rem 1.3rem;margin-bottom:.85rem;">
              <div class="section-label green" style="font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--primary);border-bottom:2px solid var(--primary-light);padding-bottom:.4rem;margin-bottom:.85rem;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-envelope"></i> Contact
              </div>
              <div class="row g-3">
                <div class="col-md-7">
                  <label class="form-label" style="font-size:.74rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">Email <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text" style="background:var(--primary-light);border:1.5px solid var(--border);color:var(--primary);border-radius:8px 0 0 8px;">
                      <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control" id="acct_email"
                      placeholder="admin@gmail.com"
                      style="border:1.5px solid var(--border);border-radius:0 8px 8px 0;">
                  </div>
                  <div id="err_email" class="field-error" style="display:none;"></div>
                </div>
                <div class="col-md-5">
                  <label class="form-label" style="font-size:.74rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">Phone</label>
                  <div class="input-group">
                    <span class="input-group-text" style="background:var(--primary-light);border:1.5px solid var(--border);color:var(--primary);border-radius:8px 0 0 8px;">+63</span>
                    <input type="text" class="form-control" id="acct_phone"
                      placeholder="000-000-0000" maxlength="12" inputmode="numeric" autocomplete="tel"
                      style="border:1.5px solid var(--border);border-radius:0 8px 8px 0;">
                  </div>
                  <div id="err_phone" class="field-error" style="display:none;"></div>
                </div>
              </div>
            </div>
            <div class="form-section" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:1.1rem 1.3rem;">
              <div class="section-label green" style="font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--primary);border-bottom:2px solid var(--primary-light);padding-bottom:.4rem;margin-bottom:.85rem;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-lock"></i> Login Credentials
              </div>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label" style="font-size:.74rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">Username <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text" style="background:var(--primary-light);border:1.5px solid var(--border);color:var(--primary);border-radius:8px 0 0 8px;">@</span>
                    <input type="text" class="form-control" id="acct_username"
                      style="border:1.5px solid var(--border);border-radius:0 8px 8px 0;">
                  </div>
                  <div id="err_username" class="field-error" style="display:none;"></div>
                </div>
                <div class="col-md-4">
                  <label class="form-label" style="font-size:.74rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">Birthdate <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text" style="background:var(--primary-light);border:1.5px solid var(--border);color:var(--primary);border-radius:8px 0 0 8px;">
                      <i class="fas fa-calendar"></i>
                    </span>
                    <input type="text" class="form-control" id="acct_birthdate" placeholder="MM/DD/YYYY" inputmode="numeric" maxlength="10"
                      style="border:1.5px solid var(--border);border-radius:0 8px 8px 0;">
                  </div>
                  <div id="err_birthdate" class="field-error" style="display:none;"></div>
                </div>
                <div class="col-md-4">
                  <label class="form-label" style="font-size:.74rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">
                    Password <span class="text-danger" id="acct_pw_req">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text" style="background:var(--primary-light);border:1.5px solid var(--border);color:var(--primary);border-radius:8px 0 0 8px;">
                      <i class="fas fa-lock"></i>
                    </span>
                    <input type="text" class="form-control" id="acct_password"
                      style="border:1.5px solid var(--border);border-radius:0 8px 8px 0;">
                  </div>
                  <small class="text-muted" id="acct_pw_hint" style="display:none;font-size:.72rem;">Leave blank to keep current</small>
                  <div id="err_password" class="field-error" style="display:none;"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="wiz-panel" id="wizPanel3">
            <div style="background:var(--primary-light);border:1.5px solid rgba(26,158,120,.2);border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.82rem;color:var(--primary);display:flex;align-items:center;gap:.5rem;">
              <i class="fas fa-eye"></i>
              <span>Review the details below before saving. Click <strong>Back</strong> to make changes.</span>
            </div>
            <div id="wizReviewContent"></div>
          </div>

        </div>
      </div>

      <div class="wiz-footer" id="wizFooter">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="wizCancelBtn">
          <i class="fas fa-times me-1"></i> Cancel
        </button>
        <div class="wiz-footer-right">
          <button type="button" class="btn-wiz-back" id="wizBackBtn" onclick="wizBack()" style="display:none;">
            <i class="fas fa-arrow-left"></i> Back
          </button>
          <button type="button" class="btn-wiz-next" id="wizNextBtn" onclick="wizNext()">
            Next <i class="fas fa-arrow-right ms-1"></i>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ADD STUDENT MODAL -->
<div class="modal fade tl-modal" id="addAllStuModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-user-graduate me-2"></i><span id="addAllStuModalTitle">Add Student</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><form id="addAllStuForm"><div class="modal-body-inner">
        <div class="form-section">
          <div class="section-label green"><i class="fas fa-id-card"></i> Basic Info</div>
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Student ID <span class="text-danger">*</span></label><input type="text" name="student_number" id="aas_sn" class="form-control" placeholder="00-00000" required></div>
            <div class="col-md-2"><label class="form-label">Year <span class="text-danger">*</span></label>
              <select name="year_level" class="form-select"><option value="1">1st</option><option value="2">2nd</option><option value="3">3rd</option><option value="4">4th</option></select>
            </div>
            <div class="col-md-2"><label class="form-label">Section</label><input type="text" name="section" class="form-control" placeholder="A"></div>
            <div class="col-md-4"><label class="form-label">Program <span class="text-danger">*</span></label>
              <select name="course_id" id="aas_course" class="form-select" required><option value="">â€” Select â€”</option></select>
            </div>
          </div>
        </div>
        <div class="form-section">
          <div class="section-label green"><i class="fas fa-user"></i> Full Name</div>
          <div class="row g-3">
            <div class="col-md-3"><label class="form-label">First Name <span class="text-danger">*</span></label><input type="text" name="first_name" id="aas_first" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Middle Name</label><input type="text" name="middle_name" class="form-control" placeholder="Leave blank if none"></div>
            <div class="col-md-3"><label class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" name="last_name" id="aas_last" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Suffix</label><input type="text" name="suffix" id="aas_suffix" class="form-control" placeholder="Leave blank if none"></div>
          </div>
        </div>
        <div class="form-section">
          <div class="section-label green"><i class="fas fa-address-book"></i> Contact &amp; Account</div>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label>
              <div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span><input type="email" name="email" class="form-control" placeholder="admin@gmail.com" required></div>
            </div>
            <div class="col-md-6"><label class="form-label">Username <span class="text-danger">*</span></label>
              <div class="input-group"><span class="input-group-text">@</span><input type="text" name="username" class="form-control" required></div>
            </div>
          </div>
          <div class="row g-3 mt-0">
            <div class="col-md-6"><label class="form-label">Birthdate <span class="text-danger">*</span></label>
              <div class="input-group"><span class="input-group-text"><i class="fas fa-calendar"></i></span><input type="date" name="birthdate" id="aas_birth" class="form-control" required></div>
            </div>
            <div class="col-md-6"><label class="form-label">Password <span class="text-danger" id="aas_pw_req">*</span>
              <button type="button" class="auto-pw-btn ms-1" onclick="autoAllStuPw()">â†º Auto</button>
            </label>
              <input type="text" name="password" id="aas_pass" class="form-control">
              <small class="text-muted" id="aas_pw_hint" style="display:none;">Leave blank to keep current password</small>
            </div>
          </div>
        </div>
      </div></form></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
        <button type="button" class="btn btn-light" onclick="document.getElementById('addAllStuForm').reset()"><i class="fas fa-undo me-1"></i> Reset</button>
        <button type="button" class="btn btn-success text-white fw-bold" id="addAllStuBtn" onclick="saveAllStudent()">
          <i class="fas fa-check me-1"></i> <span id="addAllStuBtnLabel">Save Student</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     SCRIPTS
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="adminlte1/plugins/chart.js/Chart.min.js"></script>

<script id="patch7-js">
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   DEPT v2 â€” FILTER STATE
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
let deptFilterMode = 'all';



/* Department checkbox selection (mirrors account behavior) */
let deptSelected = new Set();
let deptSelectMode = false;

function deptToggleControlMenu(ev) {
  ev?.stopPropagation?.();
  const m = document.getElementById('deptCtrlMenu');
  if (!m) return;
  m.classList.toggle('open');
}
function deptCloseControlMenu() {
  document.getElementById('deptCtrlMenu')?.classList.remove('open');
}
function deptEnterSelectMode() {
  deptCloseControlMenu();
  if (deptSelectMode) { deptExitSelectMode(); return; }
  document.getElementById('deptFilterBar')?.classList.remove('open');
  document.getElementById('section-departments')?.classList.remove('filter-open');
  deptSelectMode = true;
  document.getElementById('section-departments')?.classList.add('dept-select-mode');
  document.getElementById('deptFilterToggle')?.classList.add('active');
  document.getElementById('deptSelectRowsBtn')?.classList.add('active');
}
function deptExitSelectMode() {
  deptSelectMode = false;
  document.getElementById('section-departments')?.classList.remove('dept-select-mode');
  document.getElementById('deptFilterToggle')?.classList.remove('active');
  document.getElementById('deptSelectRowsBtn')?.classList.remove('active');
  deptClearSelection();
}

function deptToggleAll(cb) {
  if (!deptSelectMode) return;
  document.querySelectorAll('.dept-row-cb').forEach(c => {
    c.checked = cb.checked;
    const id = c.dataset.id;
    if (cb.checked) deptSelected.add(id); else deptSelected.delete(id);
    c.closest('tr')?.classList.toggle('row-selected', cb.checked);
  });
  deptUpdateBulkBar();
}

function deptToggleRow(cb) {
  if (!deptSelectMode) { cb.checked = false; return; }
  const id = cb.dataset.id;
  if (cb.checked) deptSelected.add(id); else deptSelected.delete(id);
  cb.closest('tr')?.classList.toggle('row-selected', cb.checked);

  const allCbs = document.querySelectorAll('.dept-row-cb');
  const allSel = allCbs.length > 0 && [...allCbs].every(c => c.checked);
  const selAll = document.getElementById('deptSelectAll');
  if (selAll) selAll.checked = allSel;

  deptUpdateBulkBar();
}

function deptClearSelection() {
  deptSelected.clear();
  document.querySelectorAll('.dept-row-cb').forEach(c => { c.checked = false; });
  const selAll = document.getElementById('deptSelectAll');
  if (selAll) selAll.checked = false;
  document.querySelectorAll('#deptGrid tr').forEach(r => r.classList.remove('row-selected'));
  deptUpdateBulkBar();
}

function deptUpdateBulkBar() {
  const bar = document.getElementById('deptBulkBar');
  const cnt = document.getElementById('deptBulkCount');
  if (!bar || !cnt) return;
  bar.classList.toggle('show', deptSelected.size > 0);
  cnt.textContent = deptSelected.size + ' selected';
}

function deptToggleFilterBar() {
  deptCloseControlMenu();
  const bar = document.getElementById('deptFilterBar');
  const btn = document.getElementById('deptFilterToggle');
  const section = document.getElementById('section-departments');
  if (!bar || !btn || !section) return;
  if (!bar.classList.contains('open') && deptSelectMode) deptExitSelectMode();
  const open = bar.classList.toggle('open');
  btn.classList.toggle('active', open);
  section.classList.toggle('filter-open', open);
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('#section-departments .acct-ctrl-wrap')) deptCloseControlMenu();
});

function setDeptFilter(mode) {
    deptFilterMode = mode;
    document.querySelectorAll('.filter-chip[id^="fc"]').forEach(el => el.classList.remove('active'));
    const idMap = { all: 'fcAll', dean: 'fcDean', nodean: 'fcNoDean' };
    const el = document.getElementById(idMap[mode]);
    if (el) el.classList.add('active');
    applyDeptFilter();
}

function applyDeptFilter() {
    const q    = (document.getElementById('deptSearch')?.value || '').trim().toLowerCase();
    const sort = document.getElementById('deptSortSel')?.value || 'name';
    let list   = [...allDepts];

    // text filter
    if (q) list = list.filter(d => (d.dept_code + d.dept_name).toLowerCase().includes(q));

    // mode filter
    if (deptFilterMode === 'dean') {
        list = list.filter(d => allAssignments.some(a => a.department_id == d.id && a.role === 'dean'));
    } else if (deptFilterMode === 'nodean') {
        list = list.filter(d => !allAssignments.some(a => a.department_id == d.id && a.role === 'dean'));
    }

    // sort
    list.sort((a, b) => {
        if (sort === 'code')  return a.dept_code.localeCompare(b.dept_code);
        if (sort === 'progs') return (parseInt(b.program_count) || 0) - (parseInt(a.program_count) || 0);
        return a.dept_name.localeCompare(b.dept_name);
    });

     renderDeptGridV2('deptGrid', list, true);
}

function updateDeptStatsBar() { /* stats bar removed */ }

/* Override the original deptSearch handler â€” remove old one and use this */
$(document).off('input', '#deptSearch').on('input', '#deptSearch', applyDeptFilter);

function deptInitials(name) {
    return (name || '?').split(' ').slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?';
}

function getDeptColorById(id) {
    const idx = allDepts.findIndex(d => String(d.id) === String(id));
    return DEPT_COLORS[(idx >= 0 ? idx : 0) % DEPT_COLORS.length];
}

function buildDeptImageMarkup(dept, color, sizeClass = 'dept-photo') {
    const path = String(dept?.dept_image || '').trim();
    if (path) return `<img src="${esc(path)}" alt="${esc(dept?.dept_name || 'Department')}" class="${sizeClass}">`;
    return `<div class="${sizeClass}-fallback dept-photo-fallback" style="background:${color};">${esc(deptInitials(dept?.dept_name || 'Department'))}</div>`;
}

function renderDeptImagePreview(path = '', deptName = '') {
    const preview = document.getElementById('deptImagePreview');
    if (!preview) return;
    const color = getDeptColorById(editingDeptId);
    if (path) {
        preview.innerHTML = `<img src="${esc(path)}" alt="${esc(deptName || 'Department')}">`;
        return;
    }
    preview.innerHTML = `<div class="dept-image-fallback" style="background:${color};">${esc(deptInitials(deptName || document.getElementById('dept_name')?.value || 'Department'))}</div>`;
}

function uploadDepartmentImage(departmentId) {
    return new Promise((resolve, reject) => {
        const fileInput = document.getElementById('dept_image_file');
        const file = fileInput?.files?.[0];
        if (!file) { resolve(currentDeptImagePath || ''); return; }
        const formData = new FormData();
        formData.append('department_id', departmentId);
        formData.append('department_image', file);
        $.ajax({
            url: 'API/Admin/upload_department_image.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success(res) {
                if (res.status !== 'success') return reject(new Error(res.message || 'Image upload failed.'));
                currentDeptImagePath = res.image_path || '';
                deptImageChanged = false;
                resolve(currentDeptImagePath);
            },
            error() { reject(new Error('Department image upload failed.')); }
        });
    });
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   DEPT v2 â€” CARD RENDERER
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function renderDeptGridV2(targetId, depts, editable) {
    const el = document.getElementById(targetId);
    deptSelected.clear();
    deptUpdateBulkBar();
    const deptSelectAll = document.getElementById('deptSelectAll');
    if (deptSelectAll) deptSelectAll.checked = false;

    if (!depts.length) {
        el.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-folder-open" style="font-size:1.5rem;opacity:.25;display:block;margin-bottom:.5rem;"></i>No departments match your filter.</td></tr>';
        return;
    }

    el.innerHTML = depts.map((d, idx) => {
        const color = DEPT_COLORS[idx % DEPT_COLORS.length];
        const inactive = parseInt(d.is_active) !== 1;
        const deanList = allAssignments.filter(a => a.department_id == d.id);
        const deanRow = deanList.find(a => a.role === 'dean');
        const secRow = deanList.find(a => a.role === 'secretary');
        const progs = allPrograms.filter(p => p.department_id == d.id);

        const personCell = (row, assignRole, labelText) => {
            if (row) {
                return `<div class="dept-person-cell">
                  <div class="dept-person-avatar" style="background:${color};">${deptInitials(row.dean_name)}</div>
                  <div style="min-width:0;">
                    <div class="dept-person-name">${esc(row.dean_name)}</div>
                    <div class="dept-person-sub">${esc(row.id_number || '—')}${row.dept_code ? ` - ${esc(row.dept_code)}` : ''}</div>
                  </div>
                  ${editable ? `<button class="dc-role-rm ms-auto flex-shrink-0" onclick="removeDean(${row.id})" title="Remove"><i class="fas fa-times"></i></button>` : ''}
                </div>`;
            }
            return editable
                ? `<button class="dept-assign-link" onclick="openAssignFromDept(${d.id},'${assignRole}')"><i class="fas fa-plus"></i>${labelText}</button>`
                : '<span style="color:#adb5bd;font-size:.75rem;font-style:italic;">—</span>';
        };

        const dropdown = editable ? `
        <div class="dropdown">
          <button class="btn-dots dept-row-dots" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
            <i class="fas fa-ellipsis-v"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:170px;font-size:.83rem;">
            <li><a class="dropdown-item" href="#" onclick="editDept(${d.id});return false;">
              <i class="fas fa-pen me-2 text-primary" style="opacity:.7;width:14px;"></i>Edit Department</a></li>
            <li><a class="dropdown-item" href="#" onclick="openAssignFromDept(${d.id},'dean');return false;">
              <i class="fas fa-user-tie me-2" style="color:#b45309;opacity:.8;width:14px;"></i>${deanRow ? 'Change Dean' : 'Assign Dean'}</a></li>
            <li><a class="dropdown-item" href="#" onclick="openAssignFromDept(${d.id},'secretary');return false;">
              <i class="fas fa-user me-2" style="color:#1d4ed8;opacity:.8;width:14px;"></i>${secRow ? 'Change Sec.' : 'Assign Sec.'}</a></li>
            <li><hr class="dropdown-divider my-1"></li>
            <li><a class="dropdown-item" href="#" onclick="toggleDept(${d.id});return false;">
              <i class="fas fa-${inactive ? 'check-circle' : 'ban'} me-2" style="color:${inactive ? '#1a9e78' : '#f57c00'};opacity:.85;width:14px;"></i>${inactive ? 'Activate' : 'Deactivate'}</a></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="deleteDeptSafe(${d.id});return false;">
              <i class="fas fa-trash-alt me-2" style="width:14px;"></i>Delete</a></li>
          </ul>
        </div>` : '';

        const summaryLine = `<div class="dept-meta-line"><span>${deanRow ? 'Dean assigned' : 'No dean assigned'}</span><span class="dept-meta-dot"></span><span>${progs.length} program${progs.length !== 1 ? 's' : ''}</span></div>`;

        return `<tr class="${inactive ? 'opacity-50' : ''}">
          <td class="dept-select-col" style="width:36px;text-align:center;padding-left:.65rem;">
            <input type="checkbox" class="acct-cb dept-row-cb" data-id="${esc(d.id)}" onchange="deptToggleRow(this)">
          </td>
          <td style="font-size:.78rem;color:var(--text-muted);">${idx + 1}</td>
          <td>
            <div style="display:flex;align-items:flex-start;gap:.8rem;">
              ${buildDeptImageMarkup(d, color)}
              <div style="display:flex;flex-direction:column;align-items:flex-start;gap:.28rem;min-width:0;">
                <span class="dept-code-badge" style="background:${color}22;color:${color};border:1px solid ${color}44;">${esc(d.dept_code)}</span>
                <div style="font-weight:600;font-size:.88rem;line-height:1.25;">${esc(d.dept_name)}</div>
              </div>
            </div>
            ${d.description ? `<div style="font-size:.72rem;color:var(--text-muted);margin-top:.1rem;">${esc(d.description)}</div>` : ''}
            ${summaryLine}
          </td>
          <td>${personCell(deanRow, 'dean', 'Assign Dean')}</td>
          <td>${personCell(secRow, 'secretary', 'Assign Sec.')}</td>
          <td class="text-center"><span class="prog-pill">${progs.length}</span></td>
          <td class="text-center">${inactive ? '<span class="pill-inactive">Inactive</span>' : '<span class="pill-active">Active</span>'}</td>
          <td style="width:44px;min-width:44px;padding:0 4px;text-align:center;position:relative;overflow:visible;">${dropdown}</td>
        </tr>`;
    }).join('');

    el.querySelectorAll('.dept-row-dots').forEach(btn => {
        bootstrap.Dropdown.getOrCreateInstance(btn, {
            popperConfig: { strategy: 'fixed' }
        });
    });

    const tbl = document.getElementById('deptTable');
    if (tbl) {
        const isMobile = window.innerWidth <= 768;
        tbl.style.minWidth = isMobile ? '' : '680px';
    }
}
/* â”€â”€ Safe delete: check dependencies first â”€â”€ */
function deleteDeptSafe(id) {
    const d = allDepts.find(x => x.id == id);
    if (!d) return;

    const hasProgs = allPrograms.filter(p => p.department_id == id).length > 0;
    const hasAssign = allAssignments.filter(a => a.department_id == id).length > 0;

    if (hasProgs || hasAssign) {
        let deps = [];
        if (hasProgs)  deps.push(`<strong>${allPrograms.filter(p=>p.department_id==id).length}</strong> linked program(s)`);
        if (hasAssign) deps.push(`<strong>${allAssignments.filter(a=>a.department_id==id).length}</strong> assigned personnel`);
        Swal.fire({
            title: 'Cannot Delete Department',
            html: `<div style="font-size:.9rem;text-align:left;">
              <p>This department still has ${deps.join(' and ')}.</p>
              <p style="margin-top:.6rem;color:var(--text-muted);">Please remove all programs and unassign all personnel before deleting.</p>
            </div>`,
            icon: 'warning', confirmButtonColor: '#1a9e78', confirmButtonText: 'Understood'
        });
        return;
    }
    deleteDept(id);
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   PROGRAM TABLE v2
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */

/* â•â• PROGRAMS â€” card grid renderer (mirrors dept v2) â•â• */
let progFilterMode = 'all';

function setProgFilter(mode) {
    progFilterMode = mode;
    ['pfAll','pfActive','pfInactive'].forEach(id => document.getElementById(id)?.classList.remove('active'));
    const map = { all:'pfAll', active:'pfActive', inactive:'pfInactive' };
    document.getElementById(map[mode])?.classList.add('active');
    applyProgFilter();
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   PROGRAMS â€” table renderer (mirrors dept/acct)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
let progSelected      = new Set();
let progSelectMode    = false;
let progPage          = 1;
let progPageSize      = 10;
let progTotalPages    = 1;
let progFilteredList  = [];
const progFilters     = { status: '', dept: '' };

function progToggleControlMenu(ev) {
    ev?.stopPropagation?.();
    const m = document.getElementById('progCtrlMenu');
    if (!m) return;
    m.classList.toggle('open');
}
function progCloseControlMenu() {
    document.getElementById('progCtrlMenu')?.classList.remove('open');
}
function progEnterSelectMode() {
    progCloseControlMenu();
    if (progSelectMode) { progExitSelectMode(); return; }
    document.getElementById('progFilterPanel')?.classList.remove('open');
    document.getElementById('section-programs')?.classList.remove('filter-open');
    progSelectMode = true;
    document.getElementById('section-programs')?.classList.add('prog-select-mode');
    document.getElementById('progFilterToggle')?.classList.add('active');
    document.getElementById('progSelectRowsBtn')?.classList.add('active');
}
function progExitSelectMode() {
    progSelectMode = false;
    document.getElementById('section-programs')?.classList.remove('prog-select-mode');
    document.getElementById('progFilterToggle')?.classList.remove('active');
    document.getElementById('progSelectRowsBtn')?.classList.remove('active');
    progClearSelection();
}
function progOpenFilters() {
    progCloseControlMenu();
    progToggleFilterBar();
}
function progToggleFilterBar() {
    progCloseControlMenu();
    const panel = document.getElementById('progFilterPanel');
    const btn = document.getElementById('progFilterToggle');
    const section = document.getElementById('section-programs');
    if (!panel || !btn || !section) return;
    if (!panel.classList.contains('open') && progSelectMode) progExitSelectMode();
    const open = panel.classList.toggle('open');
    btn.classList.toggle('active', open);
    section.classList.toggle('filter-open', open);
}

/* Stats bar update */
function updateProgStatsBar() {
    const all      = allPrograms2 || [];
    const activeN  = all.filter(p => parseInt(p.is_active) !== 0).length;
    const inactive = all.length - activeN;
    const depts    = new Set(all.map(p => p.department_id).filter(Boolean)).size;
    document.getElementById('psTotal').textContent    = all.length;
    document.getElementById('psActive').textContent   = activeN;
    document.getElementById('psInactive').textContent = inactive;
    document.getElementById('psDepts').textContent    = depts;
}

/* Render one page slice into tbody */
function renderProgTablePage(list) {
    const tbody = document.getElementById('progTableBody');
    if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-graduation-cap" style="font-size:1.5rem;opacity:.25;display:block;margin-bottom:.5rem;"></i>No programs match your filter.</td></tr>';
        return;
    }
    const DEPT_COLORS = ['#1a9e78','#1f73db','#f57c00','#7b1fa2','#c62828','#00838f','#455a64','#3949ab'];
    tbody.innerHTML = list.map((p, rowIndex) => {
        const active   = parseInt(p.is_active) !== 0;
        const deptIdx  = allDepts.findIndex(d => String(d.id) === String(p.department_id));
        const color    = deptIdx >= 0 ? DEPT_COLORS[deptIdx % DEPT_COLORS.length] : '#1a9e78';
        const sel      = progSelected.has(String(p.id));
        const rowNo    = ((progPage - 1) * progPageSize) + rowIndex + 1;
        return `<tr${sel ? ' class="row-selected"' : ''}>
          <td class="prog-select-col" style="width:36px;text-align:center;padding-left:.65rem;">
            <input type="checkbox" class="acct-cb prog-row-cb"
              data-id="${esc(p.id)}" ${sel ? 'checked' : ''}
              onchange="progToggleRow(this)">
          </td>
          <td style="font-size:.78rem;color:var(--text-muted);">${rowNo}</td>
          <td>
            <div class="prog-program-cell">
              <span style="display:inline-flex;align-items:center;padding:.18em .55em;border-radius:20px;
                font-size:.7rem;font-weight:700;background:${color}22;color:${color};
                border:1px solid ${color}44;white-space:nowrap;">${esc(p.course_code)}</span>
              <span class="prog-program-name">${esc(p.course_name)}</span>
            </div>
          </td>
          <td>
            ${p.dept_name
              ? `<div class="prog-dept-cell">
                   <span class="prog-dept-badge" style="background:var(--primary-light);color:var(--primary);border:1px solid rgba(26,158,120,.25);">${esc(p.dept_code||'')}</span>
                   <span class="prog-dept-name">${esc(p.dept_name)}</span>
                 </div>`
              : `<span class="prog-no-dept">No department</span>`}
          </td>
          <td class="text-center">
            <span class="${active ? 'pill-active' : 'pill-inactive'}">${active ? 'Active' : 'Inactive'}</span>
          </td>
          <td style="width:44px;min-width:44px;padding:0 4px;text-align:center;position:relative;overflow:visible;">
            <div class="dot-menu-wrap" style="position:relative;display:inline-block;">
              <button class="dot-menu-btn" onclick="openProgDotMenu('${esc(p.id)}',this)" title="Actions">
                <i class="fas fa-ellipsis-v"></i>
              </button>
            </div>
          </td>
        </tr>`;
    }).join('');
    document.getElementById('progSelectAll').checked = false;
}

/* Pagination render */
function progRenderPage() {
    const total = progFilteredList.length;
    progTotalPages = Math.max(1, Math.ceil(total / progPageSize));
    progPage = Math.min(progPage, progTotalPages);
    const start = (progPage - 1) * progPageSize;
    const end   = Math.min(start + progPageSize, total);
    renderProgTablePage(progFilteredList.slice(start, end));

    const info = document.getElementById('progPgInfo');
    if (info) info.textContent = total ? `Showing ${start + 1}-${end} of ${total}` : 'No results';

    document.getElementById('progPgFirst').disabled = progPage <= 1;
    document.getElementById('progPgPrev').disabled  = progPage <= 1;
    document.getElementById('progPgNext').disabled  = progPage >= progTotalPages;
    document.getElementById('progPgLast').disabled  = progPage >= progTotalPages;

    const pages = document.getElementById('progPgPages');
    if (!pages) return;
    let nums;
    const range = (a, b) => Array.from({ length: b - a + 1 }, (_, i) => a + i);
    if (progTotalPages <= 7) {
        nums = range(1, progTotalPages);
    } else if (progPage <= 4) {
        nums = [...range(1, 5), 'â€¦', progTotalPages];
    } else if (progPage >= progTotalPages - 3) {
        nums = [1, 'â€¦', ...range(progTotalPages - 4, progTotalPages)];
    } else {
        nums = [1, 'â€¦', progPage - 1, progPage, progPage + 1, 'â€¦', progTotalPages];
    }
    pages.innerHTML = nums.map(n =>
        n === 'â€¦'
          ? `<span class="apg-ellipsis">â€¦</span>`
          : `<button class="apg-page-btn${n === progPage ? ' active' : ''}" onclick="progGoPage(${n})">${n}</button>`
    ).join('');
}

function progGoPage(n) {
    progPage = Math.max(1, Math.min(n, progTotalPages));
    progRenderPage();
}
function progChangePageSize(val) {
    progPageSize = parseInt(val); progPage = 1; progRenderPage();
}

/* Filter apply */
function applyProgFilter() {
    const q    = (document.getElementById('progSearch')?.value || '').trim().toLowerCase();
    const did  = document.getElementById('progDeptChipFilter')?.value || '';
    const sort = document.getElementById('progSortSel')?.value || 'name';
    let list   = [...(allPrograms2 || [])];

    if (q)   list = list.filter(p => (p.course_code + p.course_name + (p.dept_name || '')).toLowerCase().includes(q));
    if (did) list = list.filter(p => String(p.department_id) === String(did));
    if (progFilters.status === 'active')   list = list.filter(p => parseInt(p.is_active) !== 0);
    if (progFilters.status === 'inactive') list = list.filter(p => parseInt(p.is_active) === 0);

    list.sort((a, b) => {
        if (sort === 'code') return a.course_code.localeCompare(b.course_code);
        if (sort === 'dept') return (a.dept_name || '').localeCompare(b.dept_name || '');
        return a.course_name.localeCompare(b.course_name);
    });

    progFilteredList = list;
    progPage = 1;
    progRenderPage();
    updateProgStatsBar();
}

/* Filter chip helper */
function progSetFilterChip(key, val, btn, chipsId) {
    progFilters[key] = val;
    document.getElementById(chipsId)?.querySelectorAll('.afp-chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    applyProgFilter();
    progUpdateFilterBadge();
}

function progUpdateFilterBadge() {
    const count = (progFilters.status ? 1 : 0) +
                  (document.getElementById('progDeptChipFilter')?.value ? 1 : 0);
    const badge = document.getElementById('progFilterBadge');
    const btn   = document.getElementById('progFilterToggle');
    if (badge) badge.textContent = count;
    if (btn)   btn.classList.toggle('has-filter', count > 0);
}

function progResetFilters() {
    progFilters.status = '';
    const deptSel = document.getElementById('progDeptChipFilter');
    if (deptSel) deptSel.value = '';
    const sortSel = document.getElementById('progSortSel');
    if (sortSel) sortSel.value = 'name';
    document.getElementById('progSearch').value = '';
    document.getElementById('progStatusChips')?.querySelectorAll('.afp-chip').forEach((c, i) => c.classList.toggle('active', i === 0));
    applyProgFilter();
    progUpdateFilterBadge();
}

/* Checkbox */
function progToggleAll(cb) {
    if (!progSelectMode) return;
    document.querySelectorAll('.prog-row-cb').forEach(c => {
        c.checked = cb.checked;
        if (cb.checked) progSelected.add(c.dataset.id); else progSelected.delete(c.dataset.id);
        c.closest('tr').classList.toggle('row-selected', cb.checked);
    });
    progUpdateBulkBar();
}
function progToggleRow(cb) {
    if (!progSelectMode) { cb.checked = false; return; }
    if (cb.checked) progSelected.add(cb.dataset.id); else progSelected.delete(cb.dataset.id);
    cb.closest('tr').classList.toggle('row-selected', cb.checked);
    const all = document.querySelectorAll('.prog-row-cb');
    document.getElementById('progSelectAll').checked = [...all].every(c => c.checked);
    progUpdateBulkBar();
}
function progClearSelection() {
    progSelected.clear();
    document.querySelectorAll('.prog-row-cb').forEach(c => { c.checked = false; });
    document.getElementById('progSelectAll').checked = false;
    document.querySelectorAll('#progTableBody tr').forEach(r => r.classList.remove('row-selected'));
    progUpdateBulkBar();
}
function progUpdateBulkBar() {
    const bar = document.getElementById('progBulkBar');
    const cnt = document.getElementById('progBulkCount');
    bar.classList.toggle('show', progSelected.size > 0);
    cnt.textContent = progSelected.size + ' selected';
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('#section-programs .acct-ctrl-wrap')) progCloseControlMenu();
});

/* 3-dot portal menu */
(function() {
    let _pPortal = null;
    function getPPortal() {
        if (!_pPortal) {
            _pPortal = document.createElement('div');
            _pPortal.id = 'progDotMenuPortal';
            _pPortal.style.cssText = 'position:fixed;z-index:99999;display:none;';
            document.body.appendChild(_pPortal);
        }
        return _pPortal;
    }
    function closePPortal() {
        const p = getPPortal();
        p.style.display = 'none'; p.innerHTML = '';
        document.querySelectorAll('.dot-menu-btn.open').forEach(b => b.classList.remove('open'));
    }
    document.addEventListener('click', e => {
        if (!e.target.closest('.dot-menu-btn') && !e.target.closest('#progDotMenuPortal')) closePPortal();
    });
    window.openProgDotMenu = function(id, btnEl) {
        closePPortal();
        btnEl.classList.add('open');
        const p = allPrograms2.find(x => String(x.id) === String(id));
        const active = p ? parseInt(p.is_active) !== 0 : true;
        const portal = getPPortal();
        portal.innerHTML = `
          <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:12px;
                      box-shadow:0 8px 32px rgba(0,0,0,.22);overflow:hidden;min-width:165px;">
            <button onclick="editProgram('${esc(id)}');document.getElementById('progDotMenuPortal').style.display='none';"
              style="display:flex;align-items:center;gap:.65rem;padding:.65rem 1rem;font-size:.84rem;
                     font-weight:600;color:var(--text);background:none;border:none;width:100%;
                     text-align:left;cursor:pointer;font-family:inherit;"
              onmouseover="this.style.background='var(--primary-light)';this.style.color='var(--primary)'"
              onmouseout="this.style.background='none';this.style.color='var(--text)'">
              <i class="fas fa-pen" style="width:14px;font-size:.78rem;opacity:.65;"></i> Edit
            </button>
            <button onclick="toggleProg('${esc(id)}',${active ? 0 : 1});document.getElementById('progDotMenuPortal').style.display='none';"
              style="display:flex;align-items:center;gap:.65rem;padding:.65rem 1rem;font-size:.84rem;
                     font-weight:600;color:var(--text);background:none;border:none;width:100%;
                     text-align:left;cursor:pointer;font-family:inherit;"
              onmouseover="this.style.background='var(--primary-light)';this.style.color='var(--primary)'"
              onmouseout="this.style.background='none';this.style.color='var(--text)'">
              <i class="fas fa-${active ? 'ban' : 'check-circle'}" style="width:14px;font-size:.78rem;opacity:.65;"></i>
              ${active ? 'Deactivate' : 'Activate'}
            </button>
            <div style="height:1px;background:var(--border);margin:.2rem 0;"></div>
            <button onclick="deleteProg('${esc(id)}');document.getElementById('progDotMenuPortal').style.display='none';"
              style="display:flex;align-items:center;gap:.65rem;padding:.65rem 1rem;font-size:.84rem;
                     font-weight:600;color:var(--danger);background:none;border:none;width:100%;
                     text-align:left;cursor:pointer;font-family:inherit;"
              onmouseover="this.style.background='#fdecea'"
              onmouseout="this.style.background='none'">
              <i class="fas fa-trash" style="width:14px;font-size:.78rem;opacity:.65;"></i> Delete
            </button>
          </div>`;
        const rect = btnEl.getBoundingClientRect();
        const menuH = 130;
        const spaceBelow = window.innerHeight - rect.bottom;
        portal.style.display = 'block';
        portal.style.right   = '8px';
        portal.style.left    = 'auto';
        portal.style.minWidth = '165px';
        if (spaceBelow < menuH) {
            portal.style.top    = 'auto';
            portal.style.bottom = (window.innerHeight - rect.top + 6) + 'px';
        } else {
            portal.style.top    = (rect.bottom + 6) + 'px';
            portal.style.bottom = 'auto';
        }
    };
})();

/* Bulk operations */
function bulkProgActivate() {
    const ids = [...progSelected];
    Swal.fire({ title: `Activate ${ids.length} program(s)?`, icon: 'question', showCancelButton: true, confirmButtonColor: '#1a9e78', confirmButtonText: 'Yes' }).then(r => {
        if (!r.isConfirmed) return;
        Promise.all(ids.map(id => $.ajax({ url: 'API/Admin/toggle_program.php', type: 'POST', contentType: 'application/json', data: JSON.stringify({ id, is_active: 1 }), dataType: 'json' })))
            .then(() => { showToast('Programs activated.', 'success'); progClearSelection(); loadProgramsSection(); loadDeptGrid('deptGrid', true); })
            .catch(() => { showToast('One or more activations failed. Please refresh.', 'error'); loadProgramsSection(); });
    });
}
function bulkProgDeactivate() {
    const ids = [...progSelected];
    Swal.fire({ title: `Deactivate ${ids.length} program(s)?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#f57c00', confirmButtonText: 'Yes' }).then(r => {
        if (!r.isConfirmed) return;
        Promise.all(ids.map(id => $.ajax({ url: 'API/Admin/toggle_program.php', type: 'POST', contentType: 'application/json', data: JSON.stringify({ id, is_active: 0 }), dataType: 'json' })))
            .then(() => { showToast('Programs deactivated.', 'success'); progClearSelection(); loadProgramsSection(); loadDeptGrid('deptGrid', true); })
            .catch(() => { showToast('One or more deactivations failed. Please refresh.', 'error'); loadProgramsSection(); });
    });
}
function bulkProgDelete() {
    const ids = [...progSelected];
    Swal.fire({ title: `Delete ${ids.length} program(s)?`, text: 'This cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d93025', confirmButtonText: 'Delete All' }).then(r => {
        if (!r.isConfirmed) return;
        Promise.all(ids.map(id => $.ajax({ url: 'API/Admin/delete_program.php', type: 'POST', contentType: 'application/json', data: JSON.stringify({ id }), dataType: 'json' })))
            .then(() => { showToast('Programs deleted.', 'success'); progClearSelection(); loadProgramsSection(); loadDeptGrid('deptGrid', true); })
            .catch(() => { showToast('One or more deletions failed. Please refresh.', 'error'); loadProgramsSection(); });
    });
}

/* loadProgramsSection â€” populate dept filter + render table */
function loadProgramsSection() {
    $.getJSON('API/Admin/fetch_programs_full.php', res => {
        if (res.status !== 'success') return;
        allPrograms2 = res.data || [];

        const sel = document.getElementById('progDeptChipFilter');
        if (sel) {
            const cur = sel.value;
            sel.innerHTML = '<option value="">All Departments</option>';
            const seen = new Map();
            allPrograms2.forEach(p => { if (p.department_id && !seen.has(p.department_id)) seen.set(p.department_id, p); });
            seen.forEach((p, id) => {
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = `${p.dept_code} â€” ${p.dept_name}`;
                sel.appendChild(opt);
            });
            if (cur) sel.value = cur;
        }
        applyProgFilter();
    });
}

/* Keep renderProgCards as no-op so old call sites don't crash */
window.renderProgTable = function(list) { progFilteredList = list; progPage = 1; progRenderPage(); };
function renderProgCards(list) { progFilteredList = list; progPage = 1; progRenderPage(); }

$(document).off('input', '#progSearch').on('input', '#progSearch', applyProgFilter);
$(document).off('change', '#progDeptChipFilter').on('change', '#progDeptChipFilter', function() { applyProgFilter(); progUpdateFilterBadge(); });
$(document).off('change', '#progSortSel').on('change', '#progSortSel', applyProgFilter);

/* Edit / toggle / delete prog helpers */
window.editProgram = function(id) {
    const p = allPrograms2.find(x => String(x.id) === String(id));
    if (!p) return;
    $('#prog_edit_id').val(p.id);
    $('#progModalTitle').text('Edit Program');
    $('#progSubmitLabel').text('Save Changes');
    $('#prog_code').val(p.course_code);
    $('#prog_name').val(p.course_name);
    loadDeptDropdown(); // populate first
    setTimeout(() => $('#prog_dept_id').val(p.department_id || ''), 200);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('progModal')).show();
};
window.toggleProg = function(id, newVal) {
    Swal.fire({ title: newVal ? 'Activate program?' : 'Deactivate program?', icon: 'question', showCancelButton: true, confirmButtonColor: '#1a9e78', confirmButtonText: 'Yes' }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: 'API/Admin/toggle_program.php', type: 'POST', contentType: 'application/json', data: JSON.stringify({ id, is_active: newVal }), dataType: 'json',
            success(res) { showToast(res.message, res.status); loadProgramsSection(); loadDeptGrid('deptGrid', true); },
            error() { showToast('Server error.', 'error'); }
        });
    });
};
window.deleteProg = function(id) {
    Swal.fire({ title: 'Delete program?', text: 'This cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d93025', confirmButtonText: 'Yes, delete' }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: 'API/Admin/delete_program.php', type: 'POST', contentType: 'application/json', data: JSON.stringify({ id }), dataType: 'json',
            success(res) { showToast(res.message, res.status); loadProgramsSection(); loadDeptGrid('deptGrid', true); },
            error() { showToast('Server error.', 'error'); }
        });
    });
};

/* Duplicate check in prog modal */
function progDeptChanged() {
    checkProgDupe();
}
function checkProgDupe() {
    const code  = $('#prog_code').val().trim().toUpperCase();
    const deptId = $('#prog_dept_id').val();
    const editId = $('#prog_edit_id').val();
    const dupe   = code && deptId && allPrograms2.some(p =>
        p.course_code.toUpperCase() === code &&
        String(p.department_id) === String(deptId) &&
        String(p.id) !== String(editId)
    );
    $('#progDupeWarn').toggle(dupe);
    if (dupe) $('#prog_code').addClass('input-error'); else $('#prog_code').removeClass('input-error');
    return dupe;
}
$(document).on('input', '#prog_code', checkProgDupe);
$(document).on('change', '#prog_dept_id', checkProgDupe);

/* Override submitProgram to enforce dept required */
const _origSubmitProgram = window.submitProgram;
window.submitProgram = function() {
    // Clear errors
    ['#prog_code', '#prog_name', '#prog_dept_id'].forEach(id => $(id).removeClass('input-error'));
    $('#prog_code_err, #prog_name_err, #prog_dept_err').hide();

    const code   = $('#prog_code').val().trim();
    const name   = $('#prog_name').val().trim();
    const deptId = $('#prog_dept_id').val();

    let valid = true;
    if (!code) { $('#prog_code').addClass('input-error'); $('#prog_code_err').html('<i class="fas fa-exclamation-circle"></i> Code is required.').show(); valid = false; }
    if (!name) { $('#prog_name').addClass('input-error'); $('#prog_name_err').html('<i class="fas fa-exclamation-circle"></i> Name is required.').show(); valid = false; }
    if (!deptId) { $('#prog_dept_id').addClass('input-error'); $('#prog_dept_err').html('<i class="fas fa-exclamation-circle"></i> Please select a department.').show(); valid = false; }

    if (!valid) return;
    if (checkProgDupe()) { showToast('Duplicate program code in this department.', 'error'); return; }

    // Continue with original logic
    const editId = $('#prog_edit_id').val();
    const btn = document.getElementById('progSubmitBtn');
    spin(btn, 'Savingâ€¦');
    $.ajax({
        url: 'API/Admin/save_program.php', type: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id: editId || null, course_code: code, course_name: name, department_id: deptId }),
        dataType: 'json',
        success(r) {
            unspin(btn, '<i class="fas fa-check me-1"></i> <span id="progSubmitLabel">Save Program</span>');
            if (r.status === 'success') {
                showToast(r.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('progModal')).hide();
                loadProgramsSection();
                loadProgramsDropdown();
                loadDeptGrid('deptGrid', true);
            } else showToast(r.message, 'error');
        },
        error() { unspin(btn, '<i class="fas fa-check me-1"></i> Save Program'); showToast('Server error.', 'error'); }
    });
};

/* Override openAddProgModal */
window.openAddProgModal = function() {
    $('#prog_edit_id').val('');
    $('#progModalTitle').text('Add Program');
    $('#progSubmitLabel').text('Save Program');
    $('#prog_code').val(''); $('#prog_name').val('');
    ['#prog_code', '#prog_name', '#prog_dept_id'].forEach(id => $(id).removeClass('input-error'));
    $('#prog_code_err, #prog_name_err, #prog_dept_err, #progDupeWarn').hide();
    loadDeptDropdown();
    setTimeout(() => $('#prog_dept_id').val(''), 250);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('progModal')).show();
    setTimeout(() => document.getElementById('prog_code').focus(), 200);
};

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   ASSIGN MODAL v2 â€” person card list
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
let assignSelectedPersonId = null;
let assignStrictContext = false;
let assignStrictDeptId = '';

/* Render person cards */
function renderPersonCards(q) {
    const wrap = document.getElementById('assignPersonList');
    if (!wrap) return;
    const lq   = (q || '').toLowerCase();
    const role = document.querySelector('input[name="assign_role"]:checked')?.value || 'dean';
    const deptId = assignStrictContext ? String(assignStrictDeptId || '') : (document.getElementById('assignDeptId')?.value || '');

    const byPersonRoleAssign = new Map();
    allAssignments.forEach(a => {
      if (!a || !a.admin_account_id) return;
      const key = String(a.admin_account_id);
      if (!byPersonRoleAssign.has(key)) byPersonRoleAssign.set(key, []);
      byPersonRoleAssign.get(key).push(a);
    });

    const inferRole = (d) => {
      const explicit = String(d.admin_role || d.type || d.role || d.account_type || d.user_type || '').toLowerCase();
      if (explicit === 'dean' || explicit === 'secretary') return explicit;
      const asg = byPersonRoleAssign.get(String(d.id)) || [];
      const firstRole = String(asg[0]?.role || '').toLowerCase();
      if (firstRole === 'dean' || firstRole === 'secretary') return firstRole;
      return '';
    };

    const baseList = allDeanAccounts
      .filter(d => parseInt(d.is_active) === 1)
      .filter(d => inferRole(d) === role)
      .filter(d => !lq || (d.first_name + ' ' + d.last_name + ' ' + (d.id_number || '')).toLowerCase().includes(lq));

    const available = [];
    const unavailable = [];
    baseList.forEach(d => {
      const personAssigns = byPersonRoleAssign.get(String(d.id)) || [];
      const sameRoleAssigns = personAssigns.filter(a => String(a.role || '').toLowerCase() === role);
      const assignedElsewhere = sameRoleAssigns.some(a => String(a.department_id) !== String(deptId || ''));
      if (!assignedElsewhere || String(d.id) === String(assignSelectedPersonId || '')) {
        available.push(d);
      } else {
        unavailable.push({ person: d, assigns: sameRoleAssigns });
      }
    });

    if (!available.length && !unavailable.length) {
      wrap.innerHTML = '<div class="text-center text-muted py-2" style="font-size:.8rem;">No matching accounts.</div>';
      return;
    }

    const personCardHtml = (d, clickable = true) => {
        const name    = [d.last_name, d.first_name].filter(Boolean).join(', ');
        const initials = ((d.first_name || '')[0] || '') + ((d.last_name || '')[0] || '');
        const sel     = String(d.id) === String(assignSelectedPersonId);
        return `<div class="person-option-card${sel ? ' selected' : ''}" ${clickable ? `onclick="selectPerson('${esc(d.id)}','${esc(name)}')"` : ''} style="${clickable ? '' : 'opacity:.65;cursor:not-allowed;'}">
          <div class="poc-avatar">${esc(initials.toUpperCase())}</div>
          <div>
            <div class="poc-name">${esc(name)}</div>
            <div class="poc-sub"><i class="fas fa-id-badge me-1" style="opacity:.5;"></i>${esc(d.id_number || '—')}</div>
          </div>
        </div>`;
    };

    const unavailHtml = unavailable.map(item => {
      const d = item.person;
      const deptText = item.assigns.map(a => `${a.dept_code || ''} - ${a.dept_name || ''}`.trim()).filter(Boolean).join(', ');
      return `${personCardHtml(d, false)}
        <div style="margin:-6px 0 10px 56px;font-size:.74rem;color:#9a5b12;">
          <i class="fas fa-lock me-1" style="opacity:.7;"></i>
          Assigned as ${role === 'dean' ? 'Dean' : 'Secretary'} in: ${esc(deptText || 'another department')}
        </div>`;
    }).join('');

    wrap.innerHTML = `
      ${available.length ? `<div style="font-size:.73rem;font-weight:700;color:var(--text-muted);margin:.1rem 0 .45rem;text-transform:uppercase;letter-spacing:.5px;">Available ${role === 'dean' ? 'Deans' : 'Secretaries'}</div>${available.map(d => personCardHtml(d, true)).join('')}` : '<div class="text-center text-muted py-2" style="font-size:.8rem;">No available accounts for this role.</div>'}
      ${unavailable.length ? `<div style="font-size:.73rem;font-weight:700;color:#9a5b12;margin:.75rem 0 .45rem;text-transform:uppercase;letter-spacing:.5px;">Unavailable</div>${unavailHtml}` : ''}
    `;
}

function selectPerson(id, name) {
    assignSelectedPersonId = id;
    document.getElementById('assignPersonId').value = id;
    // update UI
    document.querySelectorAll('#assignPersonList .person-option-card').forEach(el => el.classList.remove('selected'));
    const target = [...document.querySelectorAll('#assignPersonList .person-option-card')].find(el => el.querySelector('.poc-name')?.textContent === name);
    if (target) target.classList.add('selected');
    document.getElementById('personPreviewName').textContent = name;
    document.getElementById('personPreview').style.display = 'flex';
    checkDeptConflict();
}

function checkDeptConflict() {
    const deptId = assignStrictContext ? String(assignStrictDeptId || '') : document.getElementById('assignDeptId')?.value;
    const role   = document.querySelector('input[name="assign_role"]:checked')?.value;
    const editId = document.getElementById('assign_edit_id')?.value;
    const warn   = document.getElementById('deptConflictWarn');
    const msg    = document.getElementById('deptConflictMsg');
    if (!deptId || !role || editId) { warn.style.display = 'none'; return; }

    const existing = allAssignments.filter(a => a.department_id == deptId && a.role === role);
    if (existing.length) {
        msg.textContent = `This department already has a ${role === 'dean' ? 'Dean' : 'Secretary'} (${existing.map(a => a.dean_name).join(', ')}). Saving will replace the existing assignment.`;
        warn.style.display = 'flex';
    } else {
        warn.style.display = 'none';
    }
    renderPersonCards(document.getElementById('assignPersonSearch')?.value || '');
}

/* Override openAssignModal */
const _origOpenAssignModal = window.openAssignModal;
window.openAssignModal = function(prePersonId, preRole, opts = {}) {
    assignStrictContext = !!opts.strictContext;
    const lockedDeptId = opts.deptId ? String(opts.deptId) : '';
    assignStrictDeptId = lockedDeptId;
    assignSelectedPersonId = prePersonId || null;
    document.getElementById('assign_edit_id').value = '';
    const initialRole = preRole || 'dean';
    document.getElementById('assignModalTitle').textContent = initialRole === 'secretary' ? 'Assign Secretary' : 'Assign Dean';
    document.getElementById('assignSubmitLabel').textContent = 'Assign';
    document.getElementById('assignPersonSection').style.display = '';
    document.getElementById('assignDeptSection').style.display = assignStrictContext ? 'none' : '';
    document.getElementById('assignRoleSection').style.display = assignStrictContext ? 'none' : '';
    document.getElementById('assignEditSummary').style.display = 'none';
    document.getElementById('personPreview').style.display = 'none';
    document.getElementById('assignPersonId').value = prePersonId || '';
    document.getElementById('deptConflictWarn').style.display = 'none';
    document.getElementById('assignPersonSearch').value = '';

    if (preRole) {
        const radio = document.querySelector(`input[name="assign_role"][value="${preRole}"]`);
        if (radio) radio.checked = true;
    } else {
        document.querySelector('input[name="assign_role"][value="dean"]').checked = true;
    }

    loadDeptDropdown();
    if (lockedDeptId) {
        setTimeout(() => {
            const sel = document.getElementById('assignDeptId');
            if (sel) sel.value = lockedDeptId;
        }, 180);
    }
    if (allDeanAccounts.length) {
        renderPersonCards('');
    } else {
        loadDeanAccounts().then ? loadDeanAccounts() : null;
        setTimeout(() => renderPersonCards(''), 600);
    }
    bootstrap.Modal.getOrCreateInstance(document.getElementById('assignModal')).show();
};

/* Override openAssignFromDept â€” lock dept when launched from a dept card */
window.openAssignFromDept = function(deptId, role) {
    openAssignModal(null, role || 'dean', { strictContext: true, deptId });
};

/* Restore select when user clicks Change */
window.unlockAssignDept = function() {
    const sel  = document.getElementById('assignDeptId');
    const card = document.getElementById('assignDeptLockedCard');
    if (card) card.remove();
    if (sel)  { sel.style.display = ''; sel.disabled = false; sel.dataset.locked = ''; sel.value = ''; }
    loadDeptDropdown();
};

/* Restore the original department <select> when user clicks "Change" */
window.unlockAssignDept = function() {
    const sect = document.getElementById('assignDeptSection');
    if (!sect) return;
    sect.innerHTML = `
      <div class="section-label green"><i class="fas fa-university"></i> Department <span class="text-danger">*</span></div>
      <select class="form-select" id="assignDeptId" style="border:1.5px solid var(--border);border-radius:8px;" onchange="checkDeptConflict()">
        <option value="">â€” Select Department â€”</option>
      </select>
      <div id="deptConflictWarn" style="display:none;margin-top:.55rem;" class="dep-has-data-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <span id="deptConflictMsg"></span>
      </div>`;
    loadDeptDropdown();
};

/* Override the person search */
$(document).off('input', '#assignPersonSearch').on('input', '#assignPersonSearch', function() {
    renderPersonCards(this.value.trim());
});
$(document).off('change', 'input[name="assign_role"]').on('change', 'input[name="assign_role"]', function() {
  const role = document.querySelector('input[name="assign_role"]:checked')?.value || 'dean';
  const titleEl = document.getElementById('assignModalTitle');
  if (titleEl) titleEl.textContent = role === 'secretary' ? 'Assign Secretary' : 'Assign Dean';
  checkDeptConflict();
  renderPersonCards(document.getElementById('assignPersonSearch')?.value || '');
});

/* Override submitAssign to read assignPersonId */
const _origSubmitAssign = window.submitAssign;
window.submitAssign = function() {
    const btn    = document.getElementById('assignSubmitBtn');
    const editId = document.getElementById('assign_edit_id').value;
    const role   = document.querySelector('input[name="assign_role"]:checked')?.value;

    if (editId) {
        // Edit mode â€” just update role
        spin(btn, 'Savingâ€¦');
        $.ajax({ url: 'API/Admin/assign_dean.php', type: 'POST', contentType: 'application/json',
            data: JSON.stringify({ edit_id: editId, role }),
            dataType: 'json',
            success(r) {
                unspin(btn, '<i class="fas fa-check me-1"></i> Save Changes');
                if (r.status === 'success') {
                    showToast(r.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
                    loadAssignments(); loadDeptGrid('deptGrid', true); loadDeptGrid('dashDeptGrid', false);
                } else showToast(r.message, 'error');
            },
            error() { unspin(btn, '<i class="fas fa-check me-1"></i> Save Changes'); showToast('Server error.', 'error'); }
        });
    } else {
        const personId = document.getElementById('assignPersonId').value || document.getElementById('assignPersonSelect')?.value;
        const deptId   = assignStrictContext ? String(assignStrictDeptId || '') : document.getElementById('assignDeptId').value;
        if (!personId) { showToast('Please select a person.', 'error'); return; }
        if (!deptId)   { showToast('Please select a department.', 'error'); return; }
        spin(btn, 'Assigningâ€¦');
        $.ajax({ url: 'API/Admin/assign_dean.php', type: 'POST', contentType: 'application/json',
            data: JSON.stringify({ dean_account_id: personId, department_id: deptId, role }),
            dataType: 'json',
            success(r) {
                unspin(btn, '<i class="fas fa-check me-1"></i> Assign');
                if (r.status === 'success') {
                    showToast(r.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
                    loadAssignments(); loadDeptGrid('deptGrid', true); loadDeptGrid('dashDeptGrid', false);
                } else showToast(r.message, 'error');
            },
            error() { unspin(btn, '<i class="fas fa-check me-1"></i> Assign'); showToast('Server error.', 'error'); }
        });
    }
};

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   DEAN ACCOUNTS â€” role/status filter
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function applyAccFilter() {
    const q    = $('#accSearch').val().trim().toLowerCase();
    const role = $('#accRoleFilter').val();
    const stat = $('#accStatusFilter').val();
    let list   = allDeanAccounts;
    if (q)    list = list.filter(d => (d.first_name + d.last_name + d.id_number + d.email).toLowerCase().includes(q));
    if (role) list = list.filter(d => {
        const asgn = allAssignments.filter(a => a.admin_account_id == d.id);
        return asgn.some(a => a.role === role);
    });
    if (stat !== '') list = list.filter(d => String(parseInt(d.is_active) === 1 ? 1 : 0) === stat);
    renderAccTable(list);
}
$(document).off('change', '#accRoleFilter, #accStatusFilter').on('change', '#accRoleFilter, #accStatusFilter', applyAccFilter);
$(document).off('input', '#accSearch').on('input', '#accSearch', applyAccFilter);

/* Assign filter */
function applyAssignFilter() {
    const q    = $('#assignSearch').val().trim().toLowerCase();
    const role = $('#assignRoleFilter').val();
    let list   = allAssignments;
    if (q)    list = list.filter(a => (a.dean_name + a.dept_name + a.dept_code + a.id_number).toLowerCase().includes(q));
    if (role) list = list.filter(a => a.role === role);
    renderAssignTable(list);
}
$(document).off('change', '#assignRoleFilter').on('change', '#assignRoleFilter', applyAssignFilter);
$(document).off('input', '#assignSearch').on('input', '#assignSearch', applyAssignFilter);

/* Refresh person cards after accounts are loaded */
const _origRenderAccTable = window.renderAccTable;
window.renderAccTable = function(list) {
    _origRenderAccTable(list);
    // also refresh person cards if assign modal is open
    if (document.getElementById('assignModal')?.classList.contains('show')) {
        renderPersonCards(document.getElementById('assignPersonSearch')?.value || '');
    }
};

/* Re-render person list when assign modal opens */
document.addEventListener('shown.bs.modal', function(e) {
    if (e.target.id === 'assignModal') {
        renderPersonCards(document.getElementById('assignPersonSearch')?.value || '');
    }
});
document.addEventListener('hidden.bs.modal', function(e) {
    if (e.target.id === 'assignModal') {
        assignSelectedPersonId = null;
        assignStrictContext = false;
        assignStrictDeptId = '';
        document.getElementById('personPreview').style.display = 'none';
    }
});
</script>
<script>
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   GLOBALS (identical to original)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
const ADMIN_ID    = '<?= $adminId ?>';
const DEPT_COLORS = ['#1a9e78','#1f73db','#f57c00','#7b1fa2','#c62828','#00838f','#455a64','#3949ab'];

let allDepts        = [];
let allAssignments  = [];
let allDeanAccounts = [];
let allPrograms     = [];
let allPrograms2    = [];
let currentSemLog   = [];
let deptProgLinked  = [];
let editingDeptId   = null;
let deptImageChanged = false;
let currentDeptImagePath = '';
let dashboardGrowthChart = null;
let dashboardDeptStudentsChart = null;
let dashboardDeptFootprintChart = null;

const TL_LAST_SECTION_KEY = 'tl_admin_last_section';
function getSavedAdminSection() {
  try {
    const saved = localStorage.getItem(TL_LAST_SECTION_KEY);
    if (!saved) return null;
    const normalized = (saved === 'semester' || saved === 'yearsections') ? 'academic' : saved;
    return document.getElementById('section-' + normalized) ? normalized : null;
  } catch (_) {
    return null;
  }
}
function saveAdminSection(name) {
  try { localStorage.setItem(TL_LAST_SECTION_KEY, name); } catch (_) {}
}

const esc    = s => String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
const spin   = (btn,lbl)  => { btn.disabled=true;  btn.innerHTML=`<i class="fas fa-spinner fa-spin me-1"></i>${lbl}`; };
const unspin = (btn,html) => { btn.disabled=false; btn.innerHTML=html; };

window.showToast = (msg, type='success') => Swal.fire({
  toast:true, position:'top-end', icon:type, title:msg,
  showConfirmButton:false, timer:3500, timerProgressBar:true
});


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   CALENDAR
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
let calCursor      = new Date();   // first of currently-shown month
let calEvents      = [];           // events for current month
let calSelectedKey = null;
const CAL_MONTHS   = ['January','February','March','April','May','June','July','August','September','October','November','December'];

function calKey(d) {
  const y = d.getFullYear(), m = String(d.getMonth()+1).padStart(2,'0'), day = String(d.getDate()).padStart(2,'0');
  return y+'-'+m+'-'+day;
}
function calClassifyType(t) {
  const s = String(t||'').toLowerCase();
  if (s.includes('quiz'))      return 'quiz';
  if (s.includes('exam'))      return 'exam';
  if (s.includes('assign'))    return 'assignment';
  if (s.includes('activity'))  return 'activity';
  return 'other';
}
function calIcon(type) {
  return { quiz:'fas fa-question-circle', exam:'fas fa-file-signature',
           assignment:'fas fa-clipboard-list', activity:'fas fa-tasks',
           other:'fas fa-bookmark' }[type] || 'fas fa-bookmark';
}

function loadCalendar() {
  calCursor.setDate(1);
  fetchCalEvents();
}

function fetchCalEvents() {
  const y = calCursor.getFullYear(), m = calCursor.getMonth()+1;
  $.getJSON('API/Admin/fetch_calendar_events.php', { year:y, month:m })
    .done(res => {
      calEvents = (res && res.status === 'success') ? (res.data || []) : [];
      renderCalendar();
    })
    .fail(() => { calEvents = []; renderCalendar(); });
}

function renderCalendar() {
  const cur = calCursor;
  document.getElementById('calMonthLabel').textContent = CAL_MONTHS[cur.getMonth()] + ' ' + cur.getFullYear();

  const firstDow  = new Date(cur.getFullYear(), cur.getMonth(), 1).getDay();
  const daysInMo  = new Date(cur.getFullYear(), cur.getMonth()+1, 0).getDate();
  const daysInPrv = new Date(cur.getFullYear(), cur.getMonth(),   0).getDate();
  const today     = new Date(); const todayKey = calKey(today);

  // group events by date key
  const byDay = {};
  calEvents.forEach(e => {
    const k = (e.due_date || '').substring(0,10);
    if (!k) return;
    (byDay[k] = byDay[k] || []).push(e);
  });

  let cells = '';
  // leading days from prev month
  for (let i = firstDow - 1; i >= 0; i--) {
    const d = daysInPrv - i;
    const dt = new Date(cur.getFullYear(), cur.getMonth()-1, d);
    cells += calCellHtml(dt, true, byDay[calKey(dt)] || [], todayKey);
  }
  // current month
  for (let d = 1; d <= daysInMo; d++) {
    const dt = new Date(cur.getFullYear(), cur.getMonth(), d);
    cells += calCellHtml(dt, false, byDay[calKey(dt)] || [], todayKey);
  }
  // trailing to fill 42 cells (6 rows)
  const used = firstDow + daysInMo;
  const tail = (Math.ceil(used/7)*7) - used;
  for (let d = 1; d <= tail; d++) {
    const dt = new Date(cur.getFullYear(), cur.getMonth()+1, d);
    cells += calCellHtml(dt, true, byDay[calKey(dt)] || [], todayKey);
  }

  document.getElementById('calGrid').innerHTML = cells;

  if (calSelectedKey) renderCalDayDetail(calSelectedKey);
}

function calCellHtml(dt, otherMonth, evts, todayKey) {
  const k       = calKey(dt);
  const isToday = k === todayKey;
  const isSel   = k === calSelectedKey;
  const max     = 3;
  const pills   = evts.slice(0, max).map(e => {
    const t = calClassifyType(e.post_type || e.type);
    return '<div class="cal-event-pill t-'+t+'" title="'+esc(e.title||'')+'">'+esc(e.title||'(untitled)')+'</div>';
  }).join('');
  const more = evts.length > max ? '<div class="cal-event-more">+'+(evts.length-max)+' more</div>' : '';
  return '<div class="cal-day'+(otherMonth?' other-month':'')+(isToday?' today':'')+(isSel?' selected':'')+
         '" onclick="calSelectDay(\''+k+'\')">'+
         '<div class="cal-day-num">'+dt.getDate()+'</div>'+
         '<div class="cal-events">'+pills+more+'</div>'+
         '</div>';
}

function calSelectDay(key) {
  calSelectedKey = key;
  renderCalendar();
  renderCalDayDetail(key);
}

function renderCalDayDetail(key) {
  const wrap = document.getElementById('calDayDetail');
  const list = document.getElementById('calDayDetailList');
  const ttl  = document.getElementById('calDayDetailTitle');
  const items = calEvents.filter(e => (e.due_date||'').substring(0,10) === key);

  const [y,m,d] = key.split('-').map(Number);
  const dt = new Date(y, m-1, d);
  ttl.textContent = CAL_MONTHS[dt.getMonth()] + ' ' + dt.getDate() + ', ' + dt.getFullYear();
  wrap.style.display = '';

  if (!items.length) {
    list.innerHTML = '<div class="cal-detail-empty"><i class="fas fa-inbox me-2"></i>No items due on this day.</div>';
    return;
  }
  list.innerHTML = items.map(e => {
    const t = calClassifyType(e.post_type || e.type);
    const time = (e.due_date || '').length > 10 ? (e.due_date.substring(11,16)) : '';
    const colorMap = { quiz:'#d93025', activity:'#1f73db', exam:'#7b1fa2', assignment:'#f59e0b', other:'#1a9e78' };
    const c = colorMap[t];
    return '<div class="cal-detail-item">'+
      '<div class="cal-detail-icon" style="background:'+c+'22;color:'+c+';"><i class="'+calIcon(t)+'"></i></div>'+
      '<div class="cal-detail-meta">'+
        '<div class="cal-detail-name">'+esc(e.title||'(untitled)')+'</div>'+
        '<div class="cal-detail-sub">'+
          '<span style="text-transform:capitalize;font-weight:700;color:'+c+';">'+esc(t)+'</span>'+
          (e.subject_name ? ' Â· '+esc(e.subject_name) : '')+
          (e.faculty_name ? ' Â· by '+esc(e.faculty_name) : '')+
        '</div>'+
      '</div>'+
      (time ? '<div class="cal-detail-time"><i class="fas fa-clock me-1"></i>'+esc(time)+'</div>' : '')+
    '</div>';
  }).join('');
}

function calPrevMonth() { calCursor = new Date(calCursor.getFullYear(), calCursor.getMonth()-1, 1); fetchCalEvents(); }
function calNextMonth() { calCursor = new Date(calCursor.getFullYear(), calCursor.getMonth()+1, 1); fetchCalEvents(); }
function calGoToday()   { const t = new Date(); calCursor = new Date(t.getFullYear(), t.getMonth(), 1); calSelectedKey = calKey(t); fetchCalEvents(); }


/* â”€â”€ OTP TOGGLE â”€â”€ */
async function toggleOtpAuth(uid, cb) {
  const en  = cb.checked ? 1 : 0;
  const lbl = document.getElementById('otplbl-' + uid);
  cb.disabled = true;
  if (lbl) { lbl.textContent = 'Saving...'; lbl.className = 'otp-label'; }
  try {
    const d = await (await fetch('API/toggle_otp_auth.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({user_id: uid, enabled: en})
    })).json();
    if (d.status === 'success') {
      if (lbl) { lbl.textContent = en ? 'OTP On' : 'OTP Off'; lbl.className = 'otp-label ' + (en ? 'on' : 'off'); }
      showToast(d.message, 'success');
    } else {
      cb.checked = !en;
      if (lbl) { lbl.innerHTML = d.locked ? '<i class="fas fa-lock me-1"></i>Pending' : (!en ? 'OTP On' : 'OTP Off'); lbl.className = 'otp-label ' + (d.locked ? 'locked-msg' : (!en ? 'on' : 'off')); }
      showToast(d.message || 'Toggle failed', 'warning');
    }
  } catch(e) {
    cb.checked = !en;
    if (lbl) { lbl.textContent = !en ? 'OTP On' : 'OTP Off'; lbl.className = 'otp-label ' + (!en ? 'on' : 'off'); }
    showToast('Request failed', 'error');
  } finally { cb.disabled = false; }
}

function otpHtml(uid, oe, fl) {
  if (!uid) return '<span class="otp-label locked-msg">No account</span>';
  const lk = +fl === 1, on = +oe === 1;
  const lt = lk ? '<i class="fas fa-lock me-1"></i>Pending' : (on ? 'OTP On' : 'OTP Off');
  const lc = lk ? 'locked-msg' : (on ? 'on' : 'off');
  return `<label class="otp-toggle-wrap${lk?' locked':''}" title="${lk?'Locked - first login pending':''}">
    <div class="otp-switch">
      <input type="checkbox" ${on?'checked':''} ${lk?'disabled':''} onchange="toggleOtpAuth('${esc(uid)}',this)">
      <span class="otp-slider"></span>
    </div>
    <span class="otp-label ${lc}" id="otplbl-${esc(uid)}">${lt}</span>
  </label>`;
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   SECTION NAV â€” now uses .active class instead of style display
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function showSection(name) {
    if (name === 'semester' || name === 'yearsections') name = 'academic';
    saveAdminSection(name);
    document.querySelectorAll('.admin-section').forEach(el => el.classList.remove('active'));
    const target = document.getElementById('section-' + name);
    if (target) target.classList.add('active');
 
    document.querySelectorAll('.nav-item[data-section], .nav-sub .nav-item[data-section]')
        .forEach(l => l.classList.remove('active'));
    const activeLink = document.querySelector(`[data-section="${name}"]`);
    if (activeLink) activeLink.classList.add('active');
 
    /* lazy-load each section */
    if (name === 'dashboard')    loadDashboard();
    if (name === 'departments')  loadDeptSection();
    if (name === 'programs')     loadProgramsSection();
    if (name === 'deans')        loadDeanSection();
    if (name === 'accounts')     loadAccountsSection();
    if (name === 'academic')     loadAcademicSection();
    if (name === 'students')     loadStudentsSection();
    if (name === 'calendar')     loadCalendar();
 
    if (window.innerWidth <= 768) closeMobileSidebar();
}

function switchAcademicTab(id, el) {
  document.querySelectorAll('.acad-tab').forEach(tab => tab.classList.remove('on'));
  document.querySelectorAll('.acad-panel').forEach(panel => panel.classList.remove('on'));
  const btn = el || document.querySelector(`.acad-tab[data-acad-tab="${id}"]`);
  const panel = document.getElementById('acad-tab-' + id);
  if (btn) btn.classList.add('on');
  if (panel) panel.classList.add('on');
  if (id === 'wk') loadAcademicWeekSetting();
  if (id === 'sec') loadAcademicYearSections();
}

function loadAcademicSection() {
  loadSemesterSection();
  loadAcademicYearSections();
  renderAcademicWeeks();
  loadAcademicWeekSetting();
}

function loadAcademicYearSections() {
  const doInit = () => {
    populateYsDeptDropdown();
    populateYsCourseDropdown();
    loadYsAllOverview();
  };
  if (allPrograms.length && allDepts.length) {
    doInit();
    return;
  }
  $.when(
    allPrograms.length
      ? $.Deferred().resolve().promise()
      : $.getJSON('API/Admin/fetch_programs_full.php', res => {
          if (res.status === 'success') {
            allPrograms  = res.data || [];
            allPrograms2 = res.data || [];
            buildProgSelectAdd();
          }
        }),
    allDepts.length
      ? $.Deferred().resolve().promise()
      : $.getJSON('API/Admin/fetch_departments.php', data => {
          allDepts = Array.isArray(data) ? data : [];
        })
  ).always(doInit);
}

const TL_ACAD_WEEK_KEY = 'tl_admin_academic_weeks';
let tlGlobalAcademicWeeks = null;
let tlAcademicWeeksLoading = false;
function getGlobalAcademicWeeks() {
  if (tlGlobalAcademicWeeks !== null) return tlGlobalAcademicWeeks;
  try {
    const raw = localStorage.getItem(TL_ACAD_WEEK_KEY);
    if (!raw) return 18;
    const parsed = JSON.parse(raw);
    if (typeof parsed === 'number') return Math.max(1, Math.min(30, parsed || 18));
    if (parsed && typeof parsed === 'object') {
      if (parsed.global) return Math.max(1, Math.min(30, parseInt(parsed.global, 10) || 18));
      const first = Object.values(parsed).find(v => parseInt(v, 10) > 0);
      if (first) return Math.max(1, Math.min(30, parseInt(first, 10) || 18));
    }
  } catch (_) {
    const n = parseInt(localStorage.getItem(TL_ACAD_WEEK_KEY), 10);
    if (n > 0) return Math.max(1, Math.min(30, n));
  }
  return 18;
}
function setGlobalAcademicWeeks(weeks) {
  tlGlobalAcademicWeeks = Math.max(1, Math.min(30, parseInt(weeks, 10) || 18));
  try { localStorage.setItem(TL_ACAD_WEEK_KEY, JSON.stringify({ global: tlGlobalAcademicWeeks })); } catch (_) {}
}
async function loadAcademicWeekSetting() {
  if (tlAcademicWeeksLoading) return;
  tlAcademicWeeksLoading = true;
  try {
    const res = await fetch('API/Admin/academic_week_setting.php?_t=' + Date.now(), { cache: 'no-store' });
    const data = await res.json();
    if (data.status === 'success') {
      setGlobalAcademicWeeks(data.week_count || 18);
      renderAcademicWeeks();
      renderSemPeriods();
    }
  } catch (_) {
    renderAcademicWeeks();
  } finally {
    tlAcademicWeeksLoading = false;
  }
}
function renderAcademicWeeks() {
  const wrap = document.getElementById('academicWeeksGrid');
  if (!wrap) return;
  const weeks = getGlobalAcademicWeeks();
  const countEl = document.getElementById('acadWeekCount');
  if (countEl) countEl.textContent = String(weeks);
  const semOrder = { '1st Semester': 0, '2nd Semester': 1 };
  const periods = [...semPeriods].sort((a, b) => {
    if (b.school_year !== a.school_year) return String(b.school_year || '').localeCompare(String(a.school_year || ''));
    return (semOrder[a.semester] ?? 9) - (semOrder[b.semester] ?? 9);
  });
  const periodList = periods.length
    ? periods.slice(0, 8).map(p => {
        const active = +p.is_active === 1;
        return `<div class="acad-week-row"><span class="acad-week-num">${esc(p.school_year || '')} - ${esc(p.semester || '')}</span><span class="acad-badge ${active ? 'acad-badge-active' : 'acad-badge-inactive'}">${active ? 'Active' : 'Inactive'}</span></div>`;
      }).join('') + (periods.length > 8 ? `<div class="acad-week-row"><span class="acad-week-dates">+${periods.length - 8} more semesters</span><span class="acad-week-dates">${weeks} weeks each</span></div>` : '')
    : '<div class="acad-week-row"><span class="acad-week-dates">No semester periods yet</span><span class="acad-week-dates">The global count will apply when periods are added.</span></div>';
  wrap.innerHTML = `<div class="acad-week-card" style="grid-column:1/-1;">
    <div class="acad-week-card-head">
      <div class="acad-week-card-title">
        <i class="fas fa-clock" style="color:var(--primary)"></i>
        Global academic weeks
        <span class="acad-badge acad-badge-active">Applies to all</span>
      </div>
    </div>
    <div class="acad-week-card-body">
      <div class="acad-week-input-row">
        <label>Total weeks:</label>
        <input type="number" id="acadGlobalWeeks" min="1" max="30" value="${weeks}" oninput="renderAcademicWeekPreview()">
        <span style="font-size:.75rem;color:var(--text-muted)">for every semester and year level</span>
      </div>
      <div class="acad-week-summary">Configured: <strong id="acadGlobalWeekCount">${weeks} week${weeks === 1 ? '' : 's'}</strong> | Week 1 to Week ${weeks}</div>
      <div id="acadGlobalWeekList" style="margin-top:10px;max-height:200px;overflow-y:auto"></div>
      <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--border);">
        <div class="acad-section-label" style="margin-bottom:8px">Coverage</div>
        ${periodList}
        <div class="acad-week-row"><span class="acad-week-num">Year levels</span><span class="acad-week-dates">1st to 4th year use ${weeks} weeks</span></div>
      </div>
    </div>
  </div>`;
  renderAcademicWeekPreview();
}
function renderAcademicWeekPreview() {
  const input = document.getElementById('acadGlobalWeeks');
  const list = document.getElementById('acadGlobalWeekList');
  const count = document.getElementById('acadGlobalWeekCount');
  if (!input || !list || !count) return;
  const weeks = Math.max(1, Math.min(30, parseInt(input.value, 10) || 1));
  input.value = weeks;
  count.textContent = `${weeks} week${weeks === 1 ? '' : 's'}`;
  const tabCount = document.getElementById('acadWeekCount');
  if (tabCount) tabCount.textContent = String(weeks);
  if (weeks > 8) {
    list.innerHTML = `<div style="font-size:.75rem;color:var(--text-muted);padding:4px 0">Week 1 through Week ${weeks} - labels generated automatically.</div>`;
    return;
  }
  let html = '';
  for (let i = 1; i <= weeks; i++) {
    html += `<div class="acad-week-row"><span class="acad-week-num">Week ${i}</span><span class="acad-week-dates">Label: Week ${i}</span></div>`;
  }
  list.innerHTML = html;
}
async function saveAcademicWeeks() {
  const input = document.getElementById('acadGlobalWeeks');
  const weeks = Math.max(1, Math.min(30, parseInt(input?.value, 10) || 18));
  try {
    const res = await fetch('API/Admin/academic_week_setting.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ week_count: weeks })
    });
    const data = await res.json();
    if (data.status !== 'success') throw new Error(data.message || 'Save failed');
    setGlobalAcademicWeeks(data.week_count || weeks);
    renderAcademicWeeks();
    renderSemPeriods();
    const toast = document.getElementById('wk-toast');
    if (toast) {
      toast.style.display = 'flex';
      setTimeout(() => { toast.style.display = 'none'; }, 2800);
    }
    showToast(`Academic weeks set to ${weeks} for all semesters and year levels.`, 'success');
  } catch (err) {
    setGlobalAcademicWeeks(weeks);
    renderAcademicWeeks();
    renderSemPeriods();
    showToast((err && err.message) || 'Saved locally only. Server sync failed.', 'warning');
  }
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   INIT
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
$(function() {
  loadSemesterDisplay();
  setAcctBirthdateBounds();
  loadProgramsDropdown();
  document.getElementById('ysModal')?.addEventListener('hidden.bs.modal', () => ysResetForm());

  const startSection = getSavedAdminSection() || 'dashboard';
  showSection(startSection);

  const sb = document.getElementById('tlSidebar');
  if (sb) sb.scrollTop = 0;
  if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
  window.scrollTo(0,0);
  setInterval(loadDashboard, 30000);
  document.addEventListener('visibilitychange', function() {
    if (!document.hidden) loadDashboard();
  });

  $('#accSearch').on('input',    function() { const q=this.value.trim().toLowerCase(); renderAccTable(q?allDeanAccounts.filter(d=>(d.first_name+d.last_name+d.id_number+d.email).toLowerCase().includes(q)):allDeanAccounts); });
  $('#assignSearch').on('input', function() { const q=this.value.trim().toLowerCase(); renderAssignTable(q?allAssignments.filter(a=>(a.dean_name+a.dept_name+a.dept_code+a.id_number).toLowerCase().includes(q)):allAssignments); });
  $('#deptSearch').on('input',   function() { const q=this.value.trim().toLowerCase(); renderDeptGrid('deptGrid',q?allDepts.filter(d=>(d.dept_code+d.dept_name).toLowerCase().includes(q)):allDepts,true); });
  $('#progSearch').on('input',   function() { const q=this.value.trim().toLowerCase(); renderProgTable(q?allPrograms2.filter(p=>(p.course_code+p.course_name+(p.dept_name||'')).toLowerCase().includes(q)):allPrograms2); });

  $(document).on('input', '#assignPersonSearch', function() { fillPersonSelect(this.value.trim()); $('#assignPersonSelect').val(''); $('#personPreview').hide(); });
  $(document).on('change','#assignPersonSelect', function() {
    const id = this.value; if (!id) { $('#personPreview').hide(); return; }
    const d = allDeanAccounts.find(x => x.id == id); if (!d) return;
    $('#personPreviewName').text([d.last_name,d.first_name].filter(Boolean).join(', ') + ' â€” ' + d.id_number);
    $('#personPreview').css('display','flex');
  });

  $(document).on('hidden.bs.modal','#assignModal', () => {
    $('#assignPersonSection,#assignDeptSection').show();
    $('#assignEditSummary').hide();
    $('#assign_edit_id').val('');
  });

  $(document).on('change','#dean_birthdate', () => {
    if ($('#dean_first_name').val() && $('#dean_last_name').val()) autoDeanPassword();
  });
});

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   SIDEBAR TOGGLE
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
(function() {
  const sidebar = document.getElementById('tlSidebar');
  const mainEl  = document.getElementById('tlMain');
  const overlay = document.getElementById('tlOverlay');
  const KEY     = 'tl_sb';
  if (window.innerWidth >= 768 && localStorage.getItem(KEY) === 'c') {
    sidebar.classList.add('collapsed'); mainEl.classList.add('collapsed');
  }
  document.getElementById('menuToggle').addEventListener('click', function() {
    if (window.innerWidth < 768) {
      sidebar.classList.toggle('open'); overlay.classList.toggle('show');
    } else {
      const c = sidebar.classList.toggle('collapsed');
      mainEl.classList.toggle('collapsed', c);
      localStorage.setItem(KEY, c ? 'c' : 'e');
    }
  });
  if (overlay) overlay.addEventListener('click', closeMobileSidebar);
  window.addEventListener('resize', () => { if (window.innerWidth >= 768) closeMobileSidebar(); });
})();

function closeMobileSidebar() {
  document.getElementById('tlSidebar').classList.remove('open');
  document.getElementById('tlOverlay').classList.remove('show');
}

function sbToggle(id) {
  document.getElementById(id).classList.toggle('open');
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   DEAN TAB SWITCH
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function switchTab(tab) {
  document.querySelectorAll('.dean-tab,.dean-tab-panel').forEach(el => el.classList.remove('active'));
  document.getElementById('tabBtn' + (tab==='accounts'?'Accounts':'Assign')).classList.add('active');
  document.getElementById('panel' + (tab==='accounts'?'Accounts':'Assignments')).classList.add('active');
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   SEMESTER
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function loadSemesterDisplay() {
  $.getJSON('API/Admin/get_semester.php', res => {
    if (res.current) {
      const txt = res.current.semester + ' - ' + res.current.school_year;
      $('#navSemDisplay,#semDisplayFull').text(txt);
      $('#footerSem').text(res.current.semester + ' · ' + res.current.school_year);
      $('#selSchoolYear').val(res.current.school_year);
      $('#selSemester').val(res.current.semester);
    } else { $('#navSemDisplay').text('Not Set'); }
    currentSemLog = res.log || [];
    renderSemLog();
    $('#undoBtn').prop('disabled', !currentSemLog.length || !currentSemLog[0].old_school_year);
  });
}
function loadSemesterSection() { loadSemesterDisplay(); loadSemPeriods(); }
function renderSemLog() {
  if (!currentSemLog.length) { $('#semLogTable tbody').html('<tr><td colspan="4" class="text-center text-muted py-2">No changes yet.</td></tr>'); return; }
  $('#semLogTable tbody').html(currentSemLog.map((l,i) => `<tr><td>${i+1}</td><td>${l.old_semester?esc(l.old_semester)+' '+esc(l.old_school_year):'<em class="text-muted">-</em>'}</td><td>${esc(l.new_semester)} ${esc(l.new_school_year)}</td><td style="font-size:.72rem;color:var(--text-muted);">${esc((l.changed_at||'').substring(0,16).replace('T',' '))}</td></tr>`).join(''));
}
function confirmSaveSemester() {
  const sy = $('#selSchoolYear').val(), sem = $('#selSemester').val();
  if (!sy || !sem) { showToast('Select school year and semester.','error'); return; }
  Swal.fire({title:'Change Active Semester?',html:`Set to: <strong>${sem} - ${sy}</strong>`,icon:'warning',showCancelButton:true,confirmButtonColor:'#1a9e78',confirmButtonText:'Yes, apply'}).then(r => {
    if (!r.isConfirmed) return;
    $.ajax({url:'API/Admin/save_semester.php',type:'POST',contentType:'application/json',data:JSON.stringify({school_year:sy,semester:sem,admin_id:ADMIN_ID}),dataType:'json',
      success(res){showToast(res.message,res.status==='success'?'success':'info');if(res.status==='success')loadSemesterDisplay();},
      error(){showToast('Server error.','error');}
    });
  });
}
function undoSemester() {
  if (!currentSemLog.length) { showToast('Nothing to undo.','info'); return; }
  const last = currentSemLog[0];
  if (!last.old_school_year) { showToast('No previous state.','info'); return; }
  Swal.fire({title:'Undo Semester?',html:`Revert to: <strong>${last.old_semester} - ${last.old_school_year}</strong>`,icon:'question',showCancelButton:true,confirmButtonColor:'#1a9e78',confirmButtonText:'Yes, undo'}).then(r => {
    if (!r.isConfirmed) return;
    $.ajax({url:'API/Admin/undo_semester.php',type:'POST',contentType:'application/json',data:JSON.stringify({log_id:last.id,admin_id:ADMIN_ID}),dataType:'json',
      success(res){showToast(res.message,res.status);if(res.status==='success')loadSemesterDisplay();},
      error(){showToast('Undo failed.','error');}
    });
  });
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   DASHBOARD
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function loadDashboard() {
  $.getJSON('API/Admin/dashboard_stats.php', res => {
    if (res.status !== 'success') return;
    const s = res.stats;
    $('#st-depts').text(s.total_departments);
    $('#st-progs').text(s.total_programs);
    $('#st-faculty').text(s.total_faculty);
    $('#st-students').text(s.total_students);
    $('#st-subjects').text(s.total_subjects);
    $('#st-classes').text(s.total_classes);
    $('#st-deans').text(s.active_deans);
    renderAdminDashboardCharts(res.analytics || {});
  }).then(() => loadDeptGrid('dashDeptGrid', false));
}

function renderAdminDashboardCharts(analytics) {
  if (typeof Chart === 'undefined') return;

  const departments = Array.isArray(analytics.departments) ? analytics.departments : [];
  const topPrograms = Array.isArray(analytics.top_programs) ? analytics.top_programs : [];
  const trend = Array.isArray(analytics.trend) ? analytics.trend : [];

  renderDashboardGrowthChart(trend);
  renderDashboardDepartmentStudentsChart(departments);
  renderDashboardDepartmentFootprintChart(departments);
  renderDashboardTopPrograms(topPrograms);
}

function renderDashboardGrowthChart(rows) {
  const ctx = document.getElementById('adminGrowthChart');
  if (!ctx) return;
  const labels = rows.map(row => row.label || '');
  const students = rows.map(row => Number(row.students || 0));
  const faculty = rows.map(row => Number(row.faculty || 0));
  const programs = rows.map(row => Number(row.programs || 0));

  if (dashboardGrowthChart) dashboardGrowthChart.destroy();
  dashboardGrowthChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Students',
          data: students,
          borderColor: '#1a9e78',
          backgroundColor: 'rgba(26,158,120,.14)',
          fill: true,
          borderWidth: 3,
          pointRadius: 3,
          pointHoverRadius: 4,
          tension: .35
        },
        {
          label: 'Faculty',
          data: faculty,
          borderColor: '#1f73db',
          backgroundColor: 'rgba(31,115,219,.08)',
          fill: false,
          borderWidth: 2.5,
          pointRadius: 3,
          pointHoverRadius: 4,
          tension: .35
        },
        {
          label: 'Programs',
          data: programs,
          borderColor: '#f57c00',
          backgroundColor: 'rgba(245,124,0,.08)',
          fill: false,
          borderWidth: 2.5,
          borderDash: [6, 6],
          pointRadius: 3,
          pointHoverRadius: 4,
          tension: .3
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'top', align: 'start', labels: { boxWidth: 12, usePointStyle: true } }
      },
      scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148,163,184,.16)' } }
      }
    }
  });
}

function renderDashboardDepartmentStudentsChart(rows) {
  const ctx = document.getElementById('departmentStudentsChart');
  if (!ctx) return;
  const sorted = [...rows].sort((a, b) => Number(b.students || 0) - Number(a.students || 0));
  const labels = sorted.map(row => row.code || row.name || '—');
  const values = sorted.map(row => Number(row.students || 0));

  if (dashboardDeptStudentsChart) dashboardDeptStudentsChart.destroy();
  dashboardDeptStudentsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Students',
        data: values,
        backgroundColor: ['#1a9e78', '#1f73db', '#f57c00', '#7b1fa2', '#c62828', '#00838f', '#455a64', '#3949ab'],
        borderRadius: 10,
        borderSkipped: false,
        maxBarThickness: 28
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            title(items) {
              const row = sorted[items[0]?.dataIndex ?? 0];
              return row ? `${row.code} - ${row.name}` : '';
            },
            label(ctx) { return `${ctx.raw} students`; }
          }
        }
      },
      scales: {
        x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148,163,184,.16)' } },
        y: { grid: { display: false } }
      }
    }
  });
}

function renderDashboardDepartmentFootprintChart(rows) {
  const ctx = document.getElementById('departmentFootprintChart');
  if (!ctx) return;
  const sorted = [...rows].sort((a, b) => Number(b.students || 0) - Number(a.students || 0));
  const labels = sorted.map(row => row.code || row.name || '—');

  if (dashboardDeptFootprintChart) dashboardDeptFootprintChart.destroy();
  dashboardDeptFootprintChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Programs',
          data: sorted.map(row => Number(row.programs || 0)),
          backgroundColor: 'rgba(31,115,219,.88)',
          borderRadius: 8,
          borderSkipped: false
        },
        {
          label: 'Subjects',
          data: sorted.map(row => Number(row.subjects || 0)),
          backgroundColor: 'rgba(245,124,0,.82)',
          borderRadius: 8,
          borderSkipped: false
        },
        {
          label: 'Classes',
          data: sorted.map(row => Number(row.classes || 0)),
          backgroundColor: 'rgba(26,158,120,.82)',
          borderRadius: 8,
          borderSkipped: false
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'top', align: 'start', labels: { boxWidth: 12, usePointStyle: true } }
      },
      scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148,163,184,.16)' } }
      }
    }
  });
}

function renderDashboardTopPrograms(rows) {
  const el = document.getElementById('topProgramList');
  if (!el) return;
  if (!rows.length) {
    el.innerHTML = '<div class="text-center text-muted py-3">No program analytics available yet.</div>';
    return;
  }

  el.innerHTML = rows.map(row => `
    <div class="top-program-item">
      <div class="top-program-main">
        <div class="top-program-title">${esc(row.code || '—')} - ${esc(row.name || 'Unnamed Program')}</div>
        <div class="top-program-meta">${esc(row.department_code || '—')} • ${esc(row.department_name || 'No Department')}</div>
      </div>
      <div class="top-program-count">${Number(row.students || 0)}</div>
    </div>
  `).join('');
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   DEPARTMENTS
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function loadDeptSection() { loadDeptGrid('deptGrid', true); }

function loadDeptGrid(targetId, editable) {
  $.getJSON('API/Admin/fetch_departments.php', data => {
    allDepts = Array.isArray(data) ? data : [];
    $.getJSON('API/Admin/fetch_dean_assignments.php', res => {
      if (res.status === 'success') allAssignments = res.data;
      if (targetId === 'deptGrid') {
        renderDeptGridV2(targetId, allDepts, editable);
      } else {
        renderDeptGrid(targetId, allDepts, editable);
      }
    });
  });
}

function renderDeptGrid(targetId, depts, editable) {
  const el = document.getElementById(targetId);
  deptSelected.clear();
    deptUpdateBulkBar();
    const deptSelectAll = document.getElementById('deptSelectAll');
    if (deptSelectAll) deptSelectAll.checked = false;

    if (!depts.length) { el.innerHTML='<div class="text-muted text-center py-4">No departments found.</div>'; return; }
  el.innerHTML = depts.map((d, idx) => {
    const color    = DEPT_COLORS[idx % DEPT_COLORS.length];
    const inactive = parseInt(d.is_active) !== 1;
    const deanList = allAssignments.filter(a => a.department_id == d.id);
    const deanHtml = deanList.length
      ? deanList.map(a => `<div class="dean-slot"><span class="role-badge ${a.role==='dean'?'rb-dean':'rb-sec'}">${a.role==='dean'?'Dean':'Sec.'}</span><div style="flex:1;"><div class="dean-name">${esc(a.dean_name)}</div><div class="dean-sub">${esc(a.id_number||'')} Â· ${esc(a.dept_code)}</div></div>${editable?`<button class="btn-rm-dean" onclick="removeDean(${a.id})"><i class="fas fa-times"></i></button>`:''}</div>`).join('')
      : '<div class="no-dean">No dean / secretary assigned</div>';
    const editBtns = editable ? `
      <button class="btn btn-sm btn-outline-success" onclick="editDept(${d.id})" title="Edit"><i class="fas fa-edit"></i></button>
      <button class="btn btn-sm btn-outline-${parseInt(d.is_active)?'secondary':'success'}" onclick="toggleDept(${d.id})"><i class="fas fa-${parseInt(d.is_active)?'ban':'check-circle'}"></i></button>
      <button class="btn btn-sm btn-outline-danger" onclick="deleteDept(${d.id})"><i class="fas fa-trash-alt"></i></button>
      <button class="btn btn-sm btn-success text-white" onclick="openAssignFromDept(${d.id})" title="Assign dean"><i class="fas fa-user-plus"></i></button>` : '';
    return `<div class="dept-card${inactive?' opacity-50':''}">
      <div class="dept-header" style="border-left:4px solid ${color};">
        ${buildDeptImageMarkup(d, color)}
        <div class="dept-dot" style="background:${color};"></div>
        <span class="dept-code-badge" style="background:${color}22;color:${color};border:1px solid ${color}44;">${esc(d.dept_code)}</span>
        <div class="dept-name">${esc(d.dept_name)}</div>
        ${inactive?'<span class="badge bg-secondary ms-1" style="font-size:.58rem;">Inactive</span>':''}
      </div>
      <div class="dept-body">${deanHtml}</div>
      <div class="dept-footer">
        <div class="prog-pills"><span class="prog-pill"><i class="fas fa-graduation-cap me-1"></i>${d.program_count} program${d.program_count!=1?'s':''}</span></div>
        ${editable?`<div class="d-flex gap-1">${editBtns}</div>`:''}
      </div>
    </div>`;
  }).join('');
}

function openDeptModal() {
  editingDeptId = null; deptProgLinked = [];
  currentDeptImagePath = ''; deptImageChanged = false;
  $('#deptModalTitle').text('Add Department');
  $('#deptForm')[0].reset(); $('#dept_id').val('');
  const fileInput = document.getElementById('dept_image_file'); if (fileInput) fileInput.value = '';
  renderDeptProgList();
  renderDeptImagePreview('', '');
  bootstrap.Modal.getOrCreateInstance(document.getElementById('deptModal')).show();
}
function editDept(id) {
  const d = allDepts.find(x => x.id == id); if (!d) return;
  editingDeptId = id; $('#deptModalTitle').text('Edit Department');
  $('#dept_id').val(d.id); $('#dept_code').val(d.dept_code);
  $('#dept_name').val(d.dept_name); $('#dept_desc').val(d.description || '');
  currentDeptImagePath = d.dept_image || '';
  deptImageChanged = false;
  const fileInput = document.getElementById('dept_image_file'); if (fileInput) fileInput.value = '';
  deptProgLinked = allPrograms.filter(p => p.department_id == id);
  renderDeptProgList();
  renderDeptImagePreview(currentDeptImagePath, d.dept_name || '');
  bootstrap.Modal.getOrCreateInstance(document.getElementById('deptModal')).show();
}
document.getElementById('dept_image_file')?.addEventListener('change', function() {
  const file = this.files && this.files[0];
  if (!file) {
    renderDeptImagePreview(currentDeptImagePath, document.getElementById('dept_name')?.value || '');
    return;
  }
  deptImageChanged = true;
  const reader = new FileReader();
  reader.onload = e => renderDeptImagePreview(String(e.target?.result || ''), document.getElementById('dept_name')?.value || '');
  reader.readAsDataURL(file);
});
document.getElementById('dept_name')?.addEventListener('input', function() {
  const fileInput = document.getElementById('dept_image_file');
  if (fileInput?.files?.length) return;
  renderDeptImagePreview(currentDeptImagePath, this.value || '');
});
function renderDeptProgList() {
  const el = document.getElementById('deptProgList');
  if (!deptProgLinked.length) { el.innerHTML = '<div class="prog-list-empty">No programs linked yet. Select from the dropdown above.</div>'; return; }
  el.innerHTML = deptProgLinked.map(p => `<div class="prog-list-item"><span><span class="prog-code-tag">${esc(p.course_code)}</span><span class="ms-2" style="font-size:.82rem;">${esc(p.course_name)}</span></span><button class="prog-rm-btn" onclick="removeProgFromDept('${esc(p.id)}')"><i class="fas fa-times"></i></button></div>`).join('');
}
function addProgToDept() {
  const sel = document.getElementById('progSelectAdd');
  const pid = sel.value; if (!pid) { showToast('Select a program first.','info'); return; }
  const prog = allPrograms.find(p => String(p.id) === String(pid)); if (!prog) return;
  if (deptProgLinked.find(p => String(p.id) === String(pid))) { showToast('Already linked.','info'); return; }
  deptProgLinked.push(prog); renderDeptProgList(); sel.value = '';
}
function removeProgFromDept(id) { deptProgLinked = deptProgLinked.filter(p => String(p.id) !== String(id)); renderDeptProgList(); }

function submitDept() {
  const code = $('#dept_code').val().trim(), name = $('#dept_name').val().trim();
  if (!code || !name) { showToast('Code and name are required.','error'); return; }
  const fileInput = document.getElementById('dept_image_file');
  const file = fileInput?.files?.[0];
  if (file && file.size > 5 * 1024 * 1024) { showToast('Department image must be 5 MB or smaller.','error'); return; }
  $.ajax({
    url:'API/Admin/save_department.php', type:'POST', contentType:'application/json',
    data:JSON.stringify({id:$('#dept_id').val()||null,dept_code:code,dept_name:name,description:$('#dept_desc').val().trim()}),
    dataType:'json',
    success(r) {
      if (r.status !== 'success') { showToast(r.message,'error'); return; }
      const savedDeptId = parseInt(r.id) || parseInt($('#dept_id').val()) || editingDeptId;
      const finishSave = () => {
        if (savedDeptId) {
          $.ajax({
            url:'API/Admin/link_programs_to_dept.php', type:'POST', contentType:'application/json',
            data:JSON.stringify({department_id:savedDeptId,program_ids:deptProgLinked.map(p=>parseInt(p.id))}),
            dataType:'json',
            success(lr){const linked=lr.linked||0;showToast(linked?`${r.message} (${linked} program${linked!==1?'s':''} linked)`:r.message,'success');},
            error(){showToast(r.message+' - but program linking failed.','warning');}
          });
        } else { showToast(r.message,'success'); }
        bootstrap.Modal.getInstance(document.getElementById('deptModal')).hide();
        loadDeptGrid('deptGrid',true); loadDeptGrid('dashDeptGrid',false); loadProgramsDropdown();
      };

      if (savedDeptId && file) {
        uploadDepartmentImage(savedDeptId)
          .then(() => finishSave())
          .catch(err => {
            showToast((err && err.message) ? `${r.message} - ${err.message}` : `${r.message} - image upload failed.`, 'warning');
            bootstrap.Modal.getInstance(document.getElementById('deptModal')).hide();
            loadDeptGrid('deptGrid',true); loadDeptGrid('dashDeptGrid',false); loadProgramsDropdown();
          });
        return;
      }

      finishSave();
    },
    error(){showToast('Server error.','error');}
  });
}
function toggleDept(id) {
  Swal.fire({title:'Toggle Status?',icon:'question',showCancelButton:true,confirmButtonColor:'#1a9e78',confirmButtonText:'Yes'}).then(r => {
    if (!r.isConfirmed) return;
    $.ajax({url:'API/Admin/toggle_department.php',type:'POST',contentType:'application/json',data:JSON.stringify({id,action:'toggle'}),dataType:'json',
      success(res){showToast(res.message,res.status);loadDeptGrid('deptGrid',true);loadDeptGrid('dashDeptGrid',false);},error(){showToast('Server error.','error');}});
  });
}
function deleteDept(id) {
  Swal.fire({title:'Delete Department?',text:'Existing data is preserved.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Yes, delete'}).then(r => {
    if (!r.isConfirmed) return;
    $.ajax({url:'API/Admin/toggle_department.php',type:'POST',contentType:'application/json',data:JSON.stringify({id,action:'delete'}),dataType:'json',
      success(res){showToast(res.message,res.status);loadDeptGrid('deptGrid',true);loadDeptGrid('dashDeptGrid',false);},error(){showToast('Server error.','error');}});
  });
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   DEAN SECTION
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function loadDeanSection() {
  $.getJSON('API/Admin/fetch_dean_assignments.php', res => {
    if (res.status === 'success') { allAssignments = res.data || []; renderAssignTable(allAssignments); $('#tabCntAssign').text(allAssignments.length); }
    loadDeanAccounts();
  });
}
function loadDeanAccounts() {
  $.getJSON('API/Admin/fetch_dean_accounts.php', res => {
    if (res.status === 'success') { allDeanAccounts = res.data || []; renderAccTable(allDeanAccounts); $('#tabCntAcc').text(allDeanAccounts.length); fillPersonSelect(''); }
  });
}
function renderAccTable(list) {
  if (!list.length) { $('#deanAccTable tbody').html('<tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-user-plus me-2"></i>No accounts yet.</td></tr>'); return; }
  $('#deanAccTable tbody').html(list.map((d,i) => {
    const name = [d.last_name,d.first_name].filter(Boolean).join(', ');
    const active = parseInt(d.is_active)===1, alsoFac = parseInt(d.is_also_faculty)===1;
    const facBadge = alsoFac?`<span style="background:#dbeafe;color:#1d4ed8;border:1px solid #bfdbfe;padding:.15em .5em;border-radius:20px;font-size:.65rem;font-weight:700;margin-left:.25rem;"><i class="fas fa-chalkboard-teacher me-1"></i>Also Faculty</span>`:'';
    const asgn = allAssignments.filter(a=>a.admin_account_id==d.id);
    const deptTxt = asgn.length?asgn.map(a=>`<span style="background:var(--primary-light);color:var(--primary);border:1px solid rgba(26,158,120,.25);padding:.12em .45em;border-radius:20px;font-size:.68rem;font-weight:700;">${esc(a.dept_code)}</span> <span style="font-size:.78rem;">${esc(a.dept_name)}</span>`).join('<br>'):'<span style="font-size:.76rem;color:#adb5bd;font-style:italic;">Not assigned</span>';
    const roleTxt = asgn.length?`<span class="role-badge ${asgn[0].role==='dean'?'rb-dean':'rb-sec'}">${asgn[0].role==='dean'?'Dean':'Sec.'}</span>`:'â€”';
    return `<tr><td>${i+1}</td><td><div class="fw-semibold" style="font-size:.83rem;">${esc(name)}${facBadge}</div><div style="font-size:.7rem;font-family:monospace;font-weight:700;color:var(--primary);margin-top:.1rem;"><i class="fas fa-id-badge me-1" style="opacity:.6;"></i>${esc(d.id_number)}</div></td><td>${deptTxt}</td><td class="text-center">${roleTxt}</td><td class="text-center"><span class="${active?'pill-active':'pill-inactive'}">${active?'Active':'Inactive'}</span></td><td class="text-center">${otpHtml(d.user_id,d.otp_enabled,d.first_login)}</td><td class="text-center"><div class="d-flex gap-1 justify-content-center flex-wrap"><button class="btn-act btn-act-edit" onclick="editDeanAccount('${esc(d.id)}')"><i class="fas fa-pen"></i> Edit</button><button class="btn-act btn-act-assign" onclick="openAssignForPerson('${esc(d.id)}')"><i class="fas fa-sitemap"></i> Assign</button><button class="btn-act ${active?'btn-act-off':'btn-act-on'}" onclick="toggleDeanStatus('${esc(d.id)}',${active?0:1})"><i class="fas fa-${active?'ban':'check-circle'}"></i> ${active?'Deactivate':'Activate'}</button><button class="btn-act btn-act-del" onclick="deleteDeanAccount('${esc(d.id)}')"><i class="fas fa-trash"></i></button></div></td></tr>`;
  }).join(''));
}
function openAddDeanModal() {
  $('#dean_edit_id').val(''); $('#addDeanModalTitle').text('Add Dean / Secretary Account');
  $('#addDeanSubmitLabel').text('Save Account'); $('#pwHintEdit').hide();
  $('#addDeanForm')[0].reset(); document.getElementById('dean_is_also_faculty').checked=false;
  bootstrap.Modal.getOrCreateInstance(document.getElementById('addDeanModal')).show();
  setTimeout(()=>document.getElementById('dean_id_number').focus(),120);
}
function autoDeanPassword() {
  const fn=$('#dean_first_name').val().trim(),ln=$('#dean_last_name').val().trim(),bd=$('#dean_birthdate').val();
  if(!fn||!ln||!bd){showToast('Fill First Name, Last Name and Birthdate first.','info');return;}
  const p=bd.split('-'); if(p.length!==3)return;
  $('#dean_password').val(fn[0].toLowerCase()+p[1]+p[2]+p[0]+ln[0].toUpperCase());
}
function submitDeanAccount() {
  const btn=document.getElementById('addDeanSubmitBtn'),editId=$('#dean_edit_id').val();
  const idNo=$('#dean_id_number').val().trim(),fn=$('#dean_first_name').val().trim(),ln=$('#dean_last_name').val().trim();
  const email=$('#dean_email').val().trim(),uname=$('#dean_username').val().trim(),pw=$('#dean_password').val().trim();
  const isAlsoFaculty=document.getElementById('dean_is_also_faculty').checked?1:0;
  if(!idNo||!fn||!ln||!email||!uname||(!editId&&!pw)){showToast('Please fill all required fields.','error');return;}
  spin(btn,'Savingâ€¦');
  $.ajax({url:'API/Admin/save_dean_account.php',type:'POST',contentType:'application/json',
    data:JSON.stringify({id:editId||null,id_number:idNo,first_name:fn,middle_name:$('#dean_middle_name').val().trim(),last_name:ln,suffix:$('#dean_suffix').val().trim(),email,phone:$('#dean_phone').val().trim(),username:uname,password:editId?(pw||null):pw,birthdate:$('#dean_birthdate').val(),is_also_faculty:isAlsoFaculty}),
    dataType:'json',
    success(r){unspin(btn,'<i class="fas fa-check me-1"></i> <span id="addDeanSubmitLabel">Save Account</span>');if(r.status==='success'){showToast(r.message,'success');bootstrap.Modal.getInstance(document.getElementById('addDeanModal')).hide();loadDeanAccounts();}else showToast(r.message,'error');},
    error(){unspin(btn,'<i class="fas fa-check me-1"></i> Save Account');showToast('Server error.','error');}
  });
}
function editDeanAccount(id) {
  const d=allDeanAccounts.find(x=>x.id==id);if(!d)return;
  $('#dean_edit_id').val(d.id);$('#addDeanModalTitle').text('Edit Dean / Secretary Account');$('#addDeanSubmitLabel').text('Save Changes');$('#pwHintEdit').show();
  $('#dean_id_number').val(d.id_number||'');$('#dean_first_name').val(d.first_name||'');$('#dean_middle_name').val(d.middle_name||'');$('#dean_last_name').val(d.last_name||'');$('#dean_suffix').val(d.suffix||'');
  $('#dean_email').val(d.email||'');$('#dean_phone').val(d.phone||'');$('#dean_username').val(d.username||'');$('#dean_birthdate').val(d.birthdate||'');$('#dean_password').val('');
  document.getElementById('dean_is_also_faculty').checked=parseInt(d.is_also_faculty)===1;
  bootstrap.Modal.getOrCreateInstance(document.getElementById('addDeanModal')).show();
}
function toggleDeanStatus(id,newVal) {
  Swal.fire({title:newVal?'Activate account?':'Deactivate account?',icon:'question',showCancelButton:true,confirmButtonColor:'#1a9e78',confirmButtonText:'Yes'}).then(r=>{
    if(!r.isConfirmed)return;
    $.ajax({url:'API/Admin/toggle_dean_status.php',type:'POST',contentType:'application/json',data:JSON.stringify({id,is_active:newVal}),dataType:'json',
      success(res){showToast(res.message,res.status);loadDeanAccounts();},error(){showToast('Server error.','error');}});
  });
}
function deleteDeanAccount(id) {
  Swal.fire({title:'Delete this account?',text:'All department assignments will also be removed.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Yes, delete'}).then(r=>{
    if(!r.isConfirmed)return;
    $.ajax({url:'API/Admin/delete_dean_account.php',type:'POST',contentType:'application/json',data:JSON.stringify({id}),dataType:'json',
      success(res){showToast(res.message,res.status);loadDeanAccounts();loadAssignments();},error(){showToast('Server error.','error');}});
  });
}

/* â”€â”€ Assignments â”€â”€ */
function loadAssignments() {
  $.getJSON('API/Admin/fetch_dean_assignments.php', res => {
    if(res.status==='success'){allAssignments=res.data||[];renderAssignTable(allAssignments);$('#tabCntAssign').text(allAssignments.length);if(allDeanAccounts.length)renderAccTable(allDeanAccounts);}
  });
}
function renderAssignTable(list) {
  if(!list.length){$('#deanAssignTable tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">No assignments yet.</td></tr>');return;}
  $('#deanAssignTable tbody').html(list.map((a,i)=>`<tr><td>${i+1}</td><td class="fw-semibold">${esc(a.dean_name)}</td><td style="font-family:monospace;font-size:.78rem;color:var(--primary);">${esc(a.id_number||'')}</td><td><span class="role-badge ${a.role==='dean'?'rb-dean':'rb-sec'}">${a.role==='dean'?'Dean':'Secretary'}</span></td><td><span style="background:var(--primary-light);color:var(--primary);border:1px solid rgba(26,158,120,.3);padding:.15rem .5rem;border-radius:20px;font-size:.7rem;font-weight:700;">${esc(a.dept_code)}</span> ${esc(a.dept_name)}</td><td style="font-size:.76rem;color:var(--text-muted);">${esc((a.assigned_at||'').substring(0,10))}</td><td class="text-center"><div class="d-flex gap-1 justify-content-center"><button class="btn-act btn-act-edit" onclick="editAssign(${a.id})"><i class="fas fa-pen"></i> Edit</button><button class="btn-act btn-act-rm" onclick="removeDean(${a.id})"><i class="fas fa-times"></i> Remove</button></div></td></tr>`).join(''));
}
function openAssignForPerson(personId){ switchTab('assignments'); openAssignModal(personId); }

function editAssign(id) {
  const a = allAssignments.find(x => x.id == id); if (!a) return;
  $('#assign_edit_id').val(a.id);
  $('#assignModalTitle').text('Edit Assignment');
  $('#assignSubmitLabel').text('Save Changes');
  $('#assignPersonSection,#assignDeptSection').hide();
  $('#assignEditSummary').html(
    `<div style="padding:.65rem .85rem;background:var(--primary-light);
                 border:1.5px solid rgba(26,158,120,.2);border-radius:9px;
                 margin-bottom:.5rem;font-size:.84rem;">
       <div style="font-weight:700;color:var(--primary);">${esc(a.dean_name)}</div>
       <div style="font-size:.76rem;color:var(--text-muted);">${esc(a.dept_code)} â€” ${esc(a.dept_name)}</div>
     </div>`
  ).show();
  $(`input[name="assign_role"][value="${a.role}"]`).prop('checked', true);
  bootstrap.Modal.getOrCreateInstance(document.getElementById('assignModal')).show();
}
function removeDean(id) {
  Swal.fire({title:'Remove this assignment?',text:'The person loses their role for this department.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Yes, remove'}).then(r=>{
    if(!r.isConfirmed)return;
    $.ajax({url:'API/Admin/remove_dean.php',type:'POST',contentType:'application/json',data:JSON.stringify({id}),dataType:'json',
      success(res){showToast(res.message,res.status);loadAssignments();loadDeptGrid('deptGrid',true);loadDeptGrid('dashDeptGrid',false);},error(){showToast('Server error.','error');}});
  });
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   PROGRAMS DROPDOWN
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function loadProgramsDropdown() {
  $.getJSON('API/Admin/fetch_programs_full.php', res => {
    if(res.status==='success'){allPrograms=res.data||[];buildProgSelectAdd();loadDeptDropdown();}
  }).fail(()=>{
    $.getJSON('API/fetch_programs.php', res=>{if(res.status==='success'){allPrograms=res.programs||[];buildProgSelectAdd();loadDeptDropdown();}});
  });
}
function buildProgSelectAdd() {
  const sel=$('#progSelectAdd');sel.html('<option value="">- Select a program to link -</option>');
  allPrograms.forEach(p=>{const dh=p.dept_name?` [${p.dept_code}]`:' [unlinked]';sel.append(`<option value="${esc(p.id)}">${esc(p.course_code)} - ${esc(p.course_name)}${dh}</option>`);});
}
function loadDeptDropdown() {
  $.getJSON('API/Admin/fetch_departments.php', data=>{
    const depts=Array.isArray(data)?data:[];if(depts.length)allDepts=depts;
    const active=depts.filter(d=>parseInt(d.is_active));
    ['#assignDeptId','#prog_dept_id'].forEach(function(id){var sel=$(id);if(!sel.length)return;var cur=sel.val();sel.html('<option value="">- Select Department -</option>');active.forEach(function(d){sel.append('<option value="'+d.id+'">'+esc(d.dept_code)+' - '+esc(d.dept_name)+'</option>');});if(cur)sel.val(cur);});
  });
}


/* â•â•â•â• SEMESTER SETTINGS â•â•â•â• */
let semPeriods = [];

async function loadSemPeriods() {
  const listEl = document.getElementById('semPeriodList');
  if (listEl) {
    listEl.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
  }
  try {
    const r = await (await fetch('API/Admin/get_all_semesters.php')).json();
    semPeriods = r.data || [];
    const semCount = document.getElementById('acadSemCount');
    if (semCount) semCount.textContent = String(semPeriods.length);
    renderSemPeriods();
    renderAcademicWeeks();
    checkSemesterDue();
  } catch { showToast('Failed to load periods', 'error'); }
}

function fmtDate(d) {
  if (!d) return '';
  const [y, m, day] = d.split('-');
  const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return mn[+m - 1] + ' ' + (+day) + ', ' + y;
}

/* Check if active semester's end date has passed â†’ show banner */
function checkSemesterDue() {
  const active = semPeriods.find(p => +p.is_active === 1);
  const banner = document.getElementById('semDueBanner');
  if (!active || !active.end_date) { banner.style.display = 'none'; return; }
  const endMs  = new Date(active.end_date).setHours(23,59,59,999);
  if (Date.now() > endMs) {
    document.getElementById('semDueBannerText').textContent =
      `${active.semester} - ${active.school_year} ended on ${fmtDate(active.end_date)}.`;
    banner.style.display = 'flex';
  } else {
    banner.style.display = 'none';
  }
}

function renderSemPeriods() {
  const active = semPeriods.find(p => +p.is_active === 1);
  document.getElementById('semActiveBannerText').textContent =
    active ? `${active.semester} - ${active.school_year}` : 'None set';

  // Update nav chip + footer
  if (active) {
    const txt = `${active.semester} - ${active.school_year}`;
    const navEl = document.getElementById('navSemDisplay');
    const ftEl  = document.getElementById('footerSem');
    if (navEl) navEl.textContent = txt;
    if (ftEl)  ftEl.textContent  = `${active.semester} · ${active.school_year}`;
  }

  const list = document.getElementById('semPeriodList');
  if (!semPeriods.length) {
    list.innerHTML = `<div style="text-align:center;padding:3rem;color:var(--text-muted);font-size:.88rem;">
      <i class="fas fa-calendar-times" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
      No periods yet. Click <strong>Add Period</strong> to get started.
    </div>`;
    return;
  }

  const semOrder = { '1st Semester': 0, '2nd Semester': 1 };
  const sorted = [...semPeriods].sort((a, b) => {
    if (b.school_year !== a.school_year) return b.school_year.localeCompare(a.school_year);
    return (semOrder[a.semester] ?? 9) - (semOrder[b.semester] ?? 9);
  });

  list.innerHTML = sorted.map(p => {
    const isActive  = +p.is_active === 1;
    const isExpired = p.end_date && Date.now() > new Date(p.end_date).setHours(23,59,59,999);
    const dateStr   = (p.start_date || p.end_date)
      ? `${fmtDate(p.start_date)} - ${fmtDate(p.end_date)}`
      : 'No dates set';

    let statusBadge = '<span class="acad-badge acad-badge-inactive">Inactive</span>';
    if (isActive && isExpired) {
      statusBadge = '<span class="acad-badge acad-badge-warning"><i class="fas fa-exclamation-triangle"></i> Ended</span>';
    } else if (isActive) {
      statusBadge = '<span class="acad-badge acad-badge-active"><span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;"></span> Active</span>';
    }

    const setActiveBtnHtml = !isActive ? `
      <button class="btn btn-ghost btn-sm" onclick="semSetActive(${p.id})">
        <i class="fas fa-check" style="font-size:.65rem;"></i> Set active
      </button>` : '';

    const deleteDisabled = isActive
      ? `disabled title="Deactivate this semester first before deleting" style="opacity:.35;cursor:not-allowed;"`
      : `onclick="semOpenDelete(${p.id})" title="Delete"`;

    return `<div class="sem-row${isActive ? ' active-row' : ''}">
      <div style="flex:1;min-width:0;">
        <div class="sem-name">
          ${esc(p.school_year)} - ${esc(p.semester)}
          ${statusBadge}
        </div>
        <div class="sem-date">
          <i class="fas fa-calendar me-1" style="opacity:.5;"></i>${dateStr}
          <span class="acad-badge acad-linked-badge">${getGlobalAcademicWeeks()} weeks</span>
        </div>
      </div>
      <div class="sem-actions">
        ${setActiveBtnHtml}
        <button class="btn-act btn-act-edit" onclick="semOpenEdit(${p.id})" title="Edit">
          <i class="fas fa-pencil-alt"></i> Edit
        </button>
        <button class="btn-act btn-act-del" ${deleteDisabled}>
          <i class="fas fa-trash"></i>
        </button>
      </div>
    </div>`;
  }).join('');
}

/* Open Add modal â€” guide user if no semesters exist */
function openSemAdd() {
  document.getElementById('semEditId').value = '';
  document.getElementById('semModalTitle').textContent = 'Add Period';
  ['semYear','semStart','semEnd'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('semSem').value = '';
  document.getElementById('semSetActive').checked = !semPeriods.length; // auto-check if first one
  document.getElementById('semDupeWarning').style.display = 'none';
  bootstrap.Modal.getOrCreateInstance(document.getElementById('semModal')).show();
  setTimeout(() => document.getElementById('semYear').focus(), 200);
}

/* Detect duplicate as user types */
(function() {
  function checkDupe() {
    const sy = (document.getElementById('semYear')?.value || '').trim();
    const sm = document.getElementById('semSem')?.value || '';
    const editId = document.getElementById('semEditId')?.value || '';
    const dupe = sy && sm && semPeriods.some(p =>
      p.school_year === sy && p.semester === sm && String(p.id) !== String(editId)
    );
    const el = document.getElementById('semDupeWarning');
    if (el) el.style.display = dupe ? 'flex' : 'none';
  }
  document.addEventListener('input',  e => { if (e.target?.id === 'semYear') checkDupe(); });
  document.addEventListener('change', e => { if (e.target?.id === 'semSem')  checkDupe(); });
})();

function semOpenEdit(id) {
  const p = semPeriods.find(x => x.id == id); if (!p) return;
  document.getElementById('semEditId').value = p.id;
  document.getElementById('semModalTitle').textContent = 'Edit Period';
  document.getElementById('semYear').value  = p.school_year;
  document.getElementById('semSem').value   = p.semester;
  document.getElementById('semStart').value = p.start_date || '';
  document.getElementById('semEnd').value   = p.end_date   || '';
  document.getElementById('semSetActive').checked = !!+p.is_active;
  document.getElementById('semDupeWarning').style.display = 'none';
  bootstrap.Modal.getOrCreateInstance(document.getElementById('semModal')).show();
}

async function saveSemPeriod() {
  const btn = document.getElementById('semSaveBtn');
  const id  = document.getElementById('semEditId').value;
  const sy  = document.getElementById('semYear').value.trim();
  const sem = document.getElementById('semSem').value;
  const st  = document.getElementById('semStart').value;
  const en  = document.getElementById('semEnd').value;
  const act = document.getElementById('semSetActive').checked ? 1 : 0;

  if (!sy || !sem) { showToast('School year and semester are required.', 'error'); return; }

  // Validate no duplicate
  const dupe = semPeriods.some(p =>
    p.school_year === sy && p.semester === sem && String(p.id) !== String(id)
  );
  if (dupe) { showToast('This semester already exists.', 'error'); return; }

  // Validate date range
  if (st && en && new Date(st) >= new Date(en)) {
    showToast('End date must be after start date.', 'error'); return;
  }

  spin(btn, 'Savingâ€¦');
  try {
    const d = await (await fetch('API/Admin/save_semester_period.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id || null, school_year: sy, semester: sem, start_date: st, end_date: en, is_active: act })
    })).json();

    if (d.status === 'success') {
      bootstrap.Modal.getInstance(document.getElementById('semModal')).hide();
      showToast(d.message, 'success');
      await loadSemPeriods();
      loadSemesterDisplay();
  setAcctBirthdateBounds();
    } else showToast(d.message, 'error');
  } catch { showToast('Server error', 'error'); }
  finally { unspin(btn, '<i class="fas fa-check me-1"></i> Save'); }
}

async function semSetActive(id) {
  const p = semPeriods.find(x => x.id == id); if (!p) return;
  const r = await Swal.fire({
    title: 'Set as active?',
    html: `This will deactivate the current period and set <strong>${esc(p.semester)} - ${esc(p.school_year)}</strong> as active.`,
    icon: 'question', showCancelButton: true,
    confirmButtonColor: '#1a9e78', confirmButtonText: 'Yes, set active'
  });
  if (!r.isConfirmed) return;
  try {
    const d = await (await fetch('API/Admin/save_semester.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ school_year: p.school_year, semester: p.semester })
    })).json();
    showToast(d.message, d.status);
    if (d.status === 'success') { await loadSemPeriods(); loadSemesterDisplay(); }
  } catch { showToast('Server error', 'error'); }
}

function semOpenDelete(id) {
  const p = semPeriods.find(x => x.id == id); if (!p) return;
  if (+p.is_active === 1) {
    showToast('Cannot delete the active semester. Set another as active first.', 'warning');
    return;
  }
  Swal.fire({
    title: 'Delete period?',
    html: `Remove <strong>${esc(p.semester)} - ${esc(p.school_year)}</strong>? Classes linked to it are unaffected.`,
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#d93025', confirmButtonText: 'Delete'
  }).then(async r => {
    if (!r.isConfirmed) return;
    try {
      const d = await (await fetch('API/Admin/delete_semester_period.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      })).json();
      showToast(d.message, d.status);
      if (d.status === 'success') { await loadSemPeriods(); loadSemesterDisplay(); }
    } catch { showToast('Server error', 'error'); }
  });
}

/* â”€â”€ TRANSITION WIZARD â”€â”€ */
function semOpenTransitionWizard() {
  const active = semPeriods.find(p => +p.is_active === 1);
  if (!active) { showToast('No active semester found.', 'warning'); return; }

  // Auto-generate next semester
  let nextSem, nextYear;
  if (active.semester === '1st Semester') {
    nextSem  = '2nd Semester';
    nextYear = active.school_year;
  } else if (active.semester === '2nd Semester') {
    nextSem  = '1st Semester';
    // Increment year: "2026-2027" â†’ "2027-2028"
    const parts = active.school_year.split('-');
    if (parts.length === 2) {
      const y1 = parseInt(parts[0]) + 1;
      const y2 = parseInt(parts[1]) + 1;
      nextYear = `${y1}-${y2}`;
    } else { nextYear = active.school_year; }
  } else {
    nextSem  = '1st Semester';
    nextYear = active.school_year;
  }

  document.getElementById('transYear').value = nextYear;
  document.getElementById('transSem').value  = nextSem;
  document.getElementById('transStart').value = '';
  document.getElementById('transEnd').value   = '';
  document.getElementById('transSetActive').checked = true;

  // Hide the due banner
  document.getElementById('semDueBanner').style.display = 'none';
  bootstrap.Modal.getInstance(document.getElementById('semModal'))?.hide();
  bootstrap.Modal.getOrCreateInstance(document.getElementById('semTransitionModal')).show();
}

async function confirmSemTransition() {
  const btn   = document.getElementById('transConfirmBtn');
  const sy    = document.getElementById('transYear').value.trim();
  const sem   = document.getElementById('transSem').value;
  const st    = document.getElementById('transStart').value;
  const en    = document.getElementById('transEnd').value;
  const act   = document.getElementById('transSetActive').checked ? 1 : 0;

  if (!sy || !sem) { showToast('School year and semester are required.', 'error'); return; }

  const dupe = semPeriods.some(p => p.school_year === sy && p.semester === sem);
  if (dupe) { showToast('This semester already exists - edit it instead.', 'warning'); return; }

  if (st && en && new Date(st) >= new Date(en)) {
    showToast('End date must be after start date.', 'error'); return;
  }

  spin(btn, 'Creating...');
  try {
    const d = await (await fetch('API/Admin/save_semester_period.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: null, school_year: sy, semester: sem, start_date: st, end_date: en, is_active: act })
    })).json();
    if (d.status === 'success') {
      bootstrap.Modal.getInstance(document.getElementById('semTransitionModal')).hide();
      showToast(`Transition complete - ${sem} ${sy} created!`, 'success');
      await loadSemPeriods();
      loadSemesterDisplay();
    } else showToast(d.message, 'error');
  } catch { showToast('Server error', 'error'); }
  finally { unspin(btn, '<i class="fas fa-check me-1"></i> Confirm &amp; Create'); }
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   STUDENTS
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
let allStudents=[], allStudentsRaw=[], stuEditId=null;
const YML={1:'1st',2:'2nd',3:'3rd',4:'4th'};

function loadStudentsSection(){loadAllStudents();populateStuCourseFilter();populateStuCourseDropdown();}
function loadAllStudents(){
  $('#allStuTable tbody').html('<tr><td colspan="9" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');
  $.getJSON('API/student/fetch_all_students.php',res=>{if(res.status==='success'){allStudentsRaw=res.data||[];allStudents=allStudentsRaw;renderAllStuTable(allStudents);}}).fail(()=>showToast('Failed to load students','error'));
}
function populateStuCourseFilter(){$.getJSON('API/Admin/fetch_programs_full.php',res=>{if(res.status!=='success')return;const sel=$('#allStuCourseFilter');sel.html('<option value="">All Programs</option>');(res.data||[]).forEach(p=>sel.append(`<option value="${esc(p.id)}">${esc(p.course_code)} â€” ${esc(p.course_name)}</option>`));});}
function populateStuCourseDropdown(){$.getJSON('API/Admin/fetch_programs_full.php',res=>{if(res.status!=='success')return;const sel=$('#aas_course');sel.html('<option value="">- Select Program -</option>');(res.data||[]).forEach(p=>sel.append(`<option value="${esc(p.id)}">${esc(p.course_code)} - ${esc(p.course_name)}</option>`));});}
$('#allStuSearch').on('input',function(){filterAllStudents();});
$('#allStuCourseFilter').on('change',function(){filterAllStudents();});
function filterAllStudents(){const q=$('#allStuSearch').val().trim().toLowerCase(),cid=$('#allStuCourseFilter').val();let list=allStudentsRaw;if(cid)list=list.filter(s=>String(s.course_id)===String(cid));if(q)list=list.filter(s=>(s.student_number+s.first_name+s.last_name+s.email+s.username).toLowerCase().includes(q));renderAllStuTable(list);}
function renderAllStuTable(list){
  if(!list.length){$('#allStuTable tbody').html('<tr><td colspan="9" class="text-center text-muted py-4">No students found.</td></tr>');return;}
  $('#allStuTable tbody').html(list.map((s,i)=>{const name=[s.last_name,s.first_name].filter(Boolean).join(', '),active=parseInt(s.is_active)===1;
    return`<tr><td>${i+1}</td><td style="font-family:monospace;font-size:.78rem;color:var(--primary);font-weight:700;">${esc(s.student_number)}</td><td><div class="fw-semibold" style="font-size:.82rem;">${esc(name)}</div><div style="font-size:.73rem;color:var(--text-muted);">${esc(s.email||'â€”')}</div></td><td><span style="background:var(--primary-light);color:var(--primary);border:1px solid rgba(26,158,120,.25);padding:.12em .42em;border-radius:20px;font-size:.68rem;font-weight:700;">${esc(s.course_code||'â€”')}</span></td><td class="text-center"><span style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;padding:.18em .45em;border-radius:20px;font-size:.68rem;font-weight:700;">${YML[s.year_level]||s.year_level||'â€”'}</span></td><td class="text-center" style="font-size:.78rem;">${esc(s.section||'â€”')}</td><td class="text-center"><span class="${active?'pill-active':'pill-inactive'}">${active?'Active':'Inactive'}</span></td><td class="text-center">${otpHtml(s.user_id,s.otp_enabled,s.first_login)}</td><td class="text-center"><div class="d-flex gap-1 justify-content-center flex-wrap"><button class="btn-act btn-act-edit" onclick="editAllStudent('${esc(s.id)}')"><i class="fas fa-pen"></i> Edit</button><button class="btn-act ${active?'btn-act-off':'btn-act-on'}" onclick="toggleAllStudent('${esc(s.id)}',${active?0:1})"><i class="fas fa-${active?'ban':'check-circle'}"></i> ${active?'Deactivate':'Activate'}</button><button class="btn-act btn-act-del" onclick="deleteAllStudent('${esc(s.id)}')"><i class="fas fa-trash"></i></button></div></td></tr>`;}).join(''));
}
function openAddAllStuModal(){stuEditId=null;$('#addAllStuModalTitle').text('Add Student');$('#addAllStuBtnLabel').text('Save Student');$('#addAllStuForm')[0].reset();$('#aas_pw_hint').hide();$('#aas_pw_req').show();bootstrap.Modal.getOrCreateInstance(document.getElementById('addAllStuModal')).show();setTimeout(()=>document.getElementById('aas_sn').focus(),120);}
function editAllStudent(id){
  $.getJSON('API/student/get_student.php',{id},res=>{if(res.status!=='success'){showToast(res.message,'error');return;}const s=res.student;stuEditId=s.id;$('#addAllStuModalTitle').text('Edit Student');$('#addAllStuBtnLabel').text('Save Changes');$('#aas_pw_hint').show();$('#aas_pw_req').hide();$('[name="student_number"]','#addAllStuForm').val(s.student_number||'');$('[name="year_level"]','#addAllStuForm').val(s.year_level||'1');$('[name="section"]','#addAllStuForm').val(s.section||'');$('#aas_course').val(s.course_id||'');$('[name="first_name"]','#addAllStuForm').val(s.first_name||'');$('[name="middle_name"]','#addAllStuForm').val(s.middle_name||'');$('[name="last_name"]','#addAllStuForm').val(s.last_name||'');$('[name="suffix"]','#addAllStuForm').val(s.suffix||'');$('[name="email"]','#addAllStuForm').val(s.email||'');$('[name="username"]','#addAllStuForm').val(s.username||'');$('#aas_birth').val(s.birthdate||'');$('#aas_pass').val('');bootstrap.Modal.getOrCreateInstance(document.getElementById('addAllStuModal')).show();});
}
function autoAllStuPw(){const fn=$('#aas_first').val().trim(),ln=$('#aas_last').val().trim(),bd=$('#aas_birth').val();if(!fn||!ln||!bd){showToast('Fill First Name, Last Name and Birthdate first.','info');return;}const p=bd.split('-');if(p.length!==3)return;$('#aas_pass').val(fn[0].toLowerCase()+p[1]+p[2]+p[0]+ln[0].toUpperCase());}
function saveAllStudent(){
  const btn=document.getElementById('addAllStuBtn');spin(btn,'Savingâ€¦');
  const formData=$('#addAllStuForm').serialize()+(stuEditId?'&id='+encodeURIComponent(stuEditId):'');
  const url=stuEditId?'API/student/edit_student.php':'API/student/save_student.php';
  $.ajax({url,type:'POST',data:formData,dataType:'json',
    success(r){unspin(btn,'<i class="fas fa-check me-1"></i> <span id="addAllStuBtnLabel">Save Student</span>');if(r.status==='success'){showToast(r.message,'success');bootstrap.Modal.getInstance(document.getElementById('addAllStuModal')).hide();loadAllStudents();}else showToast(r.message,'error');},
    error(){unspin(btn,'<i class="fas fa-check me-1"></i> Save Student');showToast('Server error.','error');}
  });
}
function toggleAllStudent(id,newVal){Swal.fire({title:newVal?'Activate student?':'Deactivate student?',icon:'question',showCancelButton:true,confirmButtonColor:'#1a9e78',confirmButtonText:'Yes'}).then(r=>{if(!r.isConfirmed)return;$.ajax({url:'API/student/toggle_status.php',type:'POST',data:{id,is_active:newVal},dataType:'json',success(res){showToast(res.message,res.status);loadAllStudents();},error(){showToast('Server error.','error');}});});}
function deleteAllStudent(id){Swal.fire({title:'Delete student?',text:'This cannot be undone.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Yes, delete'}).then(r=>{if(!r.isConfirmed)return;$.ajax({url:'API/student/delete_student.php',type:'POST',contentType:'application/json',data:JSON.stringify({id}),dataType:'json',success(res){showToast(res.message,res.status);loadAllStudents();},error(){showToast('Server error.','error');}});});}

/* â•â• YEAR & SECTIONS â•â• */
let allYsConfigs    = [];
let ysCurrentCourse = null;
let ysSelected      = new Set();
const YL_LABELS     = { 1:'1st Year', 2:'2nd Year', 3:'3rd Year', 4:'4th Year' };

function populateYsCourseDropdown() {
    const sel = document.getElementById('ysCourse');
    const cur = sel.value;
    sel.innerHTML = '<option value="">- Select Program -</option>';
    allPrograms.forEach(p => {
        const opt = document.createElement('option');
        opt.value       = p.id;
        opt.textContent = `${p.course_code} â€” ${p.course_name}`;
        sel.appendChild(opt);
    });
    if (cur) sel.value = cur;
}

function loadYsForCourse(courseId) {
    ysCurrentCourse = courseId || null;
    ysResetForm();
    const card  = document.getElementById('ysTableCard');
    const label = document.getElementById('ysTableProgramLabel');
    if (!courseId) { card.style.display = 'none'; return; }
    const prog = allPrograms.find(p => String(p.id) === String(courseId));
    label.textContent = prog ? `${prog.course_code} â€” ${prog.course_name}` : '';
    card.style.display = '';
    loadYsTable(courseId);
}

function loadYsTable(courseId) {
    const tbody = document.querySelector('#ysTable tbody');
    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';
    $.getJSON(`API/Admin/fetch_year_section_config.php?course_id=${courseId}`, res => {
        allYsConfigs = res.data || [];
        renderYsTable(allYsConfigs);
    }).fail(() => showToast('Failed to load configs', 'error'));
}

function renderYsTable(list) {
    const tbody = document.querySelector('#ysTable tbody');
    if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No configs yet. Add one above.</td></tr>';
        return;
    }
    tbody.innerHTML = list.map((r, i) => `
      <tr>
        <td style="font-size:.78rem;color:var(--text-muted);">${i + 1}</td>
        <td>
          <span style="background:var(--accent-light);color:var(--accent);border:1px solid rgba(31,115,219,.2);
               padding:.2rem .6rem;border-radius:20px;font-size:.75rem;font-weight:700;">
            ${YL_LABELS[r.year_level] || r.year_level}
          </span>
        </td>
        <td style="text-align:center;">
          <span style="font-size:1.25rem;font-weight:700;color:var(--primary);">${r.section_count}</span>
          <span style="font-size:.72rem;color:var(--text-muted);"> section${r.section_count != 1 ? 's' : ''}</span>
        </td>
        <td style="width:44px;min-width:44px;padding:0 4px;text-align:center;position:relative;overflow:visible;">
          <div class="dot-menu-wrap" style="position:relative;display:inline-block;">
            <button class="dot-menu-btn" onclick="openYsDotMenu('${r.id}',this)" title="Actions">
              <i class="fas fa-ellipsis-v"></i>
            </button>
          </div>
        </td>
      </tr>`).join('');
}

(function() {
    let _ysPortal = null;
    function getYsPortal() {
        if (!_ysPortal) {
            _ysPortal = document.createElement('div');
            _ysPortal.id = 'ysDotMenuPortal';
            _ysPortal.style.cssText = 'position:fixed;z-index:99999;display:none;';
            document.body.appendChild(_ysPortal);
        }
        return _ysPortal;
    }
    function closeYsPortal() {
        const p = getYsPortal();
        p.style.display = 'none'; p.innerHTML = '';
        document.querySelectorAll('#ysTable .dot-menu-btn.open').forEach(b => b.classList.remove('open'));
    }
    document.addEventListener('click', e => {
        if (!e.target.closest('#ysTable .dot-menu-btn') && !e.target.closest('#ysDotMenuPortal')) closeYsPortal();
    });
    window.openYsDotMenu = function(id, btnEl) {
        closeYsPortal();
        btnEl.classList.add('open');
        const p = getYsPortal();
        p.innerHTML = `
          <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:12px;
                      box-shadow:0 8px 32px rgba(0,0,0,.22);overflow:hidden;min-width:145px;">
            <button onclick="ysEdit('${id}');document.getElementById('ysDotMenuPortal').style.display='none';"
              style="display:flex;align-items:center;gap:.65rem;padding:.65rem 1rem;font-size:.84rem;
                     font-weight:600;color:var(--text);background:none;border:none;width:100%;
                     text-align:left;cursor:pointer;font-family:inherit;"
              onmouseover="this.style.background='var(--primary-light)';this.style.color='var(--primary)'"
              onmouseout="this.style.background='none';this.style.color='var(--text)'">
              <i class="fas fa-pen" style="width:14px;font-size:.78rem;opacity:.65;"></i> Edit
            </button>
            <div style="height:1px;background:var(--border);margin:.2rem 0;"></div>
            <button onclick="ysDel('${id}');document.getElementById('ysDotMenuPortal').style.display='none';"
              style="display:flex;align-items:center;gap:.65rem;padding:.65rem 1rem;font-size:.84rem;
                     font-weight:600;color:var(--danger);background:none;border:none;width:100%;
                     text-align:left;cursor:pointer;font-family:inherit;"
              onmouseover="this.style.background='#fdecea'"
              onmouseout="this.style.background='none'">
              <i class="fas fa-trash" style="width:14px;font-size:.78rem;opacity:.65;"></i> Delete
            </button>
          </div>`;
        const rect = btnEl.getBoundingClientRect();
        const menuH = 100;
        const spaceBelow = window.innerHeight - rect.bottom;
        p.style.display = 'block';
        p.style.right   = Math.max(8, window.innerWidth - rect.right) + 'px';
        p.style.left    = 'auto';
        if (spaceBelow < menuH) {
            p.style.bottom = (window.innerHeight - rect.top + 6) + 'px';
            p.style.top    = 'auto';
        } else {
            p.style.top    = (rect.bottom + 6) + 'px';
            p.style.bottom = 'auto';
        }
    };
})();

function ysEdit(id) {
    const r = allYsConfigs.find(x => String(x.id) === String(id));
    if (!r) return;
    $('#ysEditId').val(r.id);
    $('#ysCourse').val(r.course_id);
    $('#ysYear').val(r.year_level);
    $('#ysSectionCount').val(r.section_count);
    document.getElementById('section-academic')?.scrollIntoView({ behavior: 'smooth' });
    showToast('Loaded for editing - change values and click Save Config.', 'info');
}

function ysResetForm() {
    $('#ysEditId').val('');
    $('#ysYear').val('');
    $('#ysSectionCount').val('');
    ysSetMode(false);
}

function ysSetMode(editing) {
    const modal = document.getElementById('ysModal');
    const title = document.getElementById('ysModalTitle');
    const btnText = document.getElementById('ysSaveBtnText');
    if (modal) modal.dataset.mode = editing ? 'edit' : 'add';
    if (title) title.textContent = editing ? 'Edit Year Level Config' : 'Add Year Level Config';
    if (btnText) btnText.textContent = editing ? 'Update Config' : 'Save Config';
    ysUpdateModalHeading();
}

function ysUpdateModalHeading() {
    const modal = document.getElementById('ysModal');
    const title = document.getElementById('ysModalTitle');
    const context = document.getElementById('ysModalContext');
    const deptId = $('#ysDept').val();
    const courseId = $('#ysCourse').val();
    const mode = modal?.dataset.mode === 'edit' ? 'Edit' : 'Add';
    const dept = (allDepts || []).find(d => String(d.id) === String(deptId || ''));
    const prog = (allPrograms || []).find(p => String(p.id) === String(courseId || ''));

    if (title) {
        title.textContent = dept ? `${mode} Config - ${dept.dept_name}` : `${mode} Year Level Config`;
    }
    if (context) {
        if (prog) {
            context.textContent = `${prog.course_code} - ${prog.course_name}`;
        } else if (dept) {
            context.textContent = `Department selected: ${dept.dept_code} - ${dept.dept_name}`;
        } else {
            context.textContent = 'Choose a department and program.';
        }
    }
}

function saveYsConfig() {
    const courseId = parseInt($('#ysCourse').val());
    const year     = parseInt($('#ysYear').val());
    const count    = parseInt($('#ysSectionCount').val());
    const editId   = parseInt($('#ysEditId').val()) || null;
    if (!courseId)                          { showToast('Select a program.', 'error'); return; }
    if (!year)                              { showToast('Select a year level.', 'error'); return; }
    if (!count || count < 1 || count > 30) { showToast('Enter a valid section count (1-30).', 'error'); return; }
    $.ajax({
        url: 'API/Admin/save_year_section_config.php',
        type: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id: editId, course_id: courseId, year_level: year, section_count: count }),
        dataType: 'json',
        success(r) {
            showToast(r.message, r.status);
            if (r.status === 'success') {
                ysResetForm();
                loadYsTable(courseId);
                loadYsAllOverview();
            }
        },
        error() { showToast('Server error.', 'error'); }
    });
}

function ysDel(id) {
    Swal.fire({
        title: 'Delete this config?',
        text: 'This cannot be undone.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#d93025', confirmButtonText: 'Yes, delete'
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({
            url: 'API/Admin/delete_year_section_config.php',
            type: 'POST', contentType: 'application/json',
            data: JSON.stringify({ id }), dataType: 'json',
            success(res) {
                showToast(res.message, res.status);
                if (ysCurrentCourse) loadYsTable(ysCurrentCourse);
                loadYsAllOverview();
            },
            error() { showToast('Server error.', 'error'); }
        });
    });
}
function loadYsAllOverview() {
    const tbody = document.getElementById('ysOverviewBody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';
    $.getJSON('API/Admin/fetch_year_section_config.php')
      .done(res => {
        const all = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
        if (!all.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-layer-group" style="font-size:1.5rem;opacity:.25;display:block;margin-bottom:.5rem;"></i>No configurations set yet.</td></tr>';
            return;
        }
        const COLORS = ['#1a9e78','#1f73db','#f57c00','#7b1fa2','#c62828','#00838f','#455a64','#3949ab'];
        const grouped = {};
        all.forEach(r => {
            const key = String(r.course_id);
            if (!grouped[key]) grouped[key] = { code: r.course_code || 'â€”', name: r.course_name || 'â€”', secs: {} };
            grouped[key].secs[String(r.year_level)] = parseInt(r.section_count);
        });
        const rows = Object.values(grouped);
        const cell = (g, yr) => {
            const n = g.secs[String(yr)];
            if (!n) return `<td class="text-center" style="color:#adb5bd;font-size:.8rem;vertical-align:middle;">â€”</td>`;
            return `<td class="text-center" style="vertical-align:middle;">
              <span style="font-size:1.05rem;font-weight:700;color:var(--primary);">${n}</span>
              <span style="font-size:.65rem;color:var(--text-muted);display:block;line-height:1.3;">${n===1?'section':'sections'}</span>
            </td>`;
        };
        tbody.innerHTML = rows.map((g, i) => {
            const color = COLORS[i % COLORS.length];
            return `<tr>
              <td style="font-size:.78rem;color:var(--text-muted);vertical-align:middle;">${i+1}</td>
              <td style="vertical-align:middle;">
                <span style="background:${color}22;color:${color};border:1px solid ${color}44;
                     padding:.18em .55em;border-radius:20px;font-size:.7rem;font-weight:700;white-space:nowrap;">
                  ${esc(g.code)}
                </span>
              </td>
              <td style="font-size:.83rem;font-weight:600;vertical-align:middle;">${esc(g.name)}</td>
              ${cell(g,1)}${cell(g,2)}${cell(g,3)}${cell(g,4)}${cell(g,5)}
            </tr>`;
        }).join('');
      })
      .fail(() => {
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-3"><i class="fas fa-exclamation-circle me-1"></i>Failed to load. Check API endpoint.</td></tr>';
      });
}

/* Called by showSection('academic') */
function initYsSection() {
    populateYsDeptDropdown();
    populateYsCourseDropdown(document.getElementById('ysDept')?.value || '');
    loadYsAllOverview(document.getElementById('ysDept')?.value || '');
}

function populateYsDeptDropdown() {
    const sel = document.getElementById('ysDept');
    if (!sel) return;
    const cur = sel.value;
    sel.innerHTML = '<option value="">- Select Department -</option>';

    (allDepts || []).filter(d => parseInt(d.is_active ?? 1) !== 0).forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = `${d.dept_code} - ${d.dept_name}`;
        sel.appendChild(opt);
    });

    if (cur && [...sel.options].some(o => o.value === cur)) sel.value = cur;
}

function populateYsCourseDropdown(deptId = '') {
    const sel = document.getElementById('ysCourse');
    if (!sel) return;
    const cur = sel.value;
    sel.innerHTML = '<option value="">- Select Program -</option>';

    let programs = [...(allPrograms || [])];
    if (deptId) programs = programs.filter(p => String(p.department_id || '') === String(deptId));

    programs.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = `${p.course_code} - ${p.course_name}`;
        sel.appendChild(opt);
    });

    if (cur && [...sel.options].some(o => o.value === cur)) sel.value = cur;
}

function ysOnDeptChange(deptId) {
    const currentYear = $('#ysYear').val();
    const currentCount = $('#ysSectionCount').val();
    const editId = $('#ysEditId').val();
    populateYsCourseDropdown(deptId || '');
    $('#ysCourse').val('');
    $('#ysEditId').val(editId);
    $('#ysYear').val(currentYear);
    $('#ysSectionCount').val(currentCount);
    ysUpdateModalHeading();
}

function loadYsForCourse(courseId) {
    ysCurrentCourse = courseId || null;
    return courseId;
}

function ysOnCourseChange(courseId) {
    ysCurrentCourse = courseId || null;
    ysUpdateModalHeading();
}

function loadYsTable(courseId) {
    ysSelected.clear();
    $('#ysTable tbody').html(
        '<tr><td colspan="5" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>'
    );
    $.getJSON(`API/Admin/fetch_year_section_config.php?course_id=${courseId}`, res => {
        allYsConfigs = res.data || [];
        renderYsTable(allYsConfigs);
    }).fail(() => showToast('Failed to load configs', 'error'));
}

function renderYsTable(list) {
    const selectAll = document.getElementById('ysSelectAll');
    if (selectAll) selectAll.checked = false;

    if (!list.length) {
        $('#ysTable tbody').html(
            '<tr><td colspan="5" class="ys-empty-state"><i class="fas fa-layer-group"></i>No configs yet for this program. Add one above.</td></tr>'
        );
        return;
    }

    $('#ysTable tbody').html(list.map((r, i) => {
        const checked = ysSelected.has(String(r.id));
        return `<tr${checked ? ' class="row-selected"' : ''}>
          <td style="width:36px;text-align:center;padding-left:.65rem;">
            <input type="checkbox" class="acct-cb ys-row-cb"
              data-id="${esc(r.id)}" ${checked ? 'checked' : ''}
              onchange="ysToggleRow(this)">
          </td>
          <td>${i + 1}</td>
          <td><span class="ys-level-pill">${YL_LABELS[r.year_level] || r.year_level}</span></td>
          <td class="text-center"><div class="ys-config-count"><strong>${r.section_count}</strong>
              <span>section${r.section_count != 1 ? 's' : ''}</span></div></td>
          <td style="width:44px;min-width:44px;padding:0 4px;text-align:center;position:relative;overflow:visible;">
            <div class="dropdown">
              <button class="btn-dots" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                <i class="fas fa-ellipsis-v"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:140px;font-size:.83rem;">
                <li><a class="dropdown-item" href="#" onclick="ysEdit(${r.id});return false;">
                  <i class="fas fa-pen me-2 text-primary" style="opacity:.7;width:14px;"></i>Edit</a></li>
                <li><a class="dropdown-item text-danger" href="#" onclick="ysDel(${r.id});return false;">
                  <i class="fas fa-trash-alt me-2" style="width:14px;"></i>Delete</a></li>
              </ul>
            </div>
          </td>
        </tr>`;
    }).join(''));

    document.querySelectorAll('#ysTable [data-bs-toggle="dropdown"]').forEach(btn => {
        bootstrap.Dropdown.getOrCreateInstance(btn, { popperConfig: { strategy: 'fixed' } });
    });
}

function ysToggleAll(cb) {
    document.querySelectorAll('.ys-row-cb').forEach(c => {
        c.checked = cb.checked;
        if (cb.checked) ysSelected.add(c.dataset.id); else ysSelected.delete(c.dataset.id);
        c.closest('tr').classList.toggle('row-selected', cb.checked);
    });
}

function ysToggleRow(cb) {
    if (cb.checked) ysSelected.add(cb.dataset.id); else ysSelected.delete(cb.dataset.id);
    cb.closest('tr').classList.toggle('row-selected', cb.checked);
    const all = document.querySelectorAll('.ys-row-cb');
    const selectAll = document.getElementById('ysSelectAll');
    if (selectAll) selectAll.checked = all.length > 0 && [...all].every(c => c.checked);
}

function ysEdit(id) {
    const r = allYsConfigs.find(x => x.id == id);
    if (!r) return;
    const prog = (allPrograms || []).find(p => String(p.id) === String(r.course_id));
    if (prog?.department_id) {
        $('#ysDept').val(String(prog.department_id));
        populateYsCourseDropdown(String(prog.department_id));
    }
    $('#ysEditId').val(r.id);
    $('#ysCourse').val(r.course_id);
    $('#ysYear').val(r.year_level);
    $('#ysSectionCount').val(r.section_count);
    ysSetMode(true);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('ysModal')).show();
}

function ysResetForm() {
    $('#ysEditId').val('');
    $('#ysDept').val('');
    $('#ysCourse').val('');
    $('#ysYear').val('');
    $('#ysSectionCount').val('');
    ysSetMode(false);
}

function openAddYsModal() {
    ysResetForm();
    populateYsDeptDropdown();
    populateYsCourseDropdown('');
    ysUpdateModalHeading();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('ysModal')).show();
    setTimeout(() => document.getElementById('ysDept')?.focus(), 120);
}

function ysOpenFromOverview(courseId) {
    const prog = (allPrograms || []).find(p => String(p.id) === String(courseId));
    openAddYsModal();
    if (prog?.department_id) {
        $('#ysDept').val(String(prog.department_id));
        ysOnDeptChange(String(prog.department_id));
        $('#ysCourse').val(String(courseId));
        ysOnCourseChange(String(courseId));
    }
}

function saveYsConfig() {
    const deptId      = parseInt($('#ysDept').val());
    const courseId    = parseInt($('#ysCourse').val());
    const year        = parseInt($('#ysYear').val());
    const count       = parseInt($('#ysSectionCount').val());
    const editId      = parseInt($('#ysEditId').val()) || null;

    if (!deptId)                             { showToast('Select a department.', 'error'); return; }
    if (!courseId)                           { showToast('Select a program.', 'error'); return; }
    if (!year)                               { showToast('Select a year level.', 'error'); return; }
    if (!count || count < 1 || count > 30)  { showToast('Enter a valid section count (1-30).', 'error'); return; }

    const selectedProgram = (allPrograms || []).find(p => String(p.id) === String(courseId));
    if (!selectedProgram || String(selectedProgram.department_id || '') !== String(deptId)) {
        showToast('Selected program does not belong to the selected department.', 'error');
        return;
    }

    $.ajax({
        url: 'API/Admin/save_year_section_config.php',
        type: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id: editId, course_id: courseId, year_level: year, section_count: count, department_id: deptId }),
        dataType: 'json',
        success(r) {
            showToast(r.message, r.status);
            if (r.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('ysModal'))?.hide();
                ysCurrentCourse = courseId;
                ysResetForm();
                loadYsAllOverview();
            }
        },
        error() { showToast('Server error.', 'error'); }
    });
}

function ysDel(id) {
    Swal.fire({
        title: 'Delete this config?',
        text: 'The section dropdown for this year level will fall back to a text input.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#d93025', confirmButtonText: 'Yes, delete'
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({
            url: 'API/Admin/delete_year_section_config.php',
            type: 'POST', contentType: 'application/json',
            data: JSON.stringify({ id }), dataType: 'json',
            success(res) {
                showToast(res.message, res.status);
                loadYsAllOverview();
            },
            error() { showToast('Server error.', 'error'); }
        });
    });
}

function loadYsAllOverview(deptId = '') {
    const tbody = document.getElementById('ysOverviewBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';

    $.getJSON('API/Admin/fetch_year_section_config.php', res => {
        let all = res.data || [];
        allYsConfigs = all;
        if (deptId) {
            const courseIds = new Set((allPrograms || [])
                .filter(p => String(p.department_id || '') === String(deptId))
                .map(p => String(p.id)));
            all = all.filter(r => courseIds.has(String(r.course_id)));
        }

        if (!all.length) {
            const count = document.getElementById('acadYsCount');
            if (count) count.textContent = '0';
            tbody.innerHTML = '<tr><td colspan="8" class="ys-empty-state"><i class="fas fa-layer-group"></i>No configurations set yet.</td></tr>';
            return;
        }

        const grouped = {};
        all.forEach(r => {
            const key = String(r.course_id);
            if (!grouped[key]) {
                grouped[key] = {
                    course_id: r.course_id,
                    course_code: r.course_code,
                    course_name: r.course_name,
                    levels: {1:null,2:null,3:null,4:null}
                };
            }
            grouped[key].levels[r.year_level] = {
                id: r.id,
                count: parseInt(r.section_count) || 0
            };
        });

        const rows = Object.values(grouped).sort((a,b) => a.course_code.localeCompare(b.course_code));
        const count = document.getElementById('acadYsCount');
        if (count) count.textContent = String(rows.length);
        tbody.innerHTML = rows.map((g, i) => {
            const pill = cfg => cfg && cfg.count > 0
              ? `<button type="button" class="ys-count-pill" onclick="ysEdit('${esc(cfg.id)}')" title="Edit this year level">${cfg.count}</button>`
              : '<span class="ys-empty-pill">-</span>';
            return `<tr>
              <td>${i + 1}</td>
              <td><span class="ys-program-chip">${esc(g.course_code)}</span></td>
              <td style="font-weight:600;">${esc(g.course_name)}</td>
              <td class="text-center">${pill(g.levels[1])}</td>
              <td class="text-center">${pill(g.levels[2])}</td>
              <td class="text-center">${pill(g.levels[3])}</td>
              <td class="text-center">${pill(g.levels[4])}</td>
              <td style="width:44px;min-width:44px;padding:0 4px;text-align:center;position:relative;overflow:visible;">
                <div class="dropdown">
                  <button class="btn-dots" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:150px;font-size:.83rem;">
                    <li><a class="dropdown-item" href="#" onclick="ysOpenFromOverview('${esc(g.course_id || '')}');return false;">
                      <i class="fas fa-pen me-2 text-primary" style="opacity:.7;width:14px;"></i>Edit Config</a></li>
                  </ul>
                </div>
              </td>
            </tr>`;
        }).join('');
        document.querySelectorAll('#ysOverviewTable [data-bs-toggle="dropdown"]').forEach(btn => {
            bootstrap.Dropdown.getOrCreateInstance(btn, { popperConfig: { strategy: 'fixed' } });
        });
    }).fail(() => {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-3"><i class="fas fa-exclamation-circle me-1"></i>Failed to load overview.</td></tr>';
    });
}</script>

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

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   ACCOUNT MANAGEMENT
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
let allAccounts = [];

function loadAccountsSection() {
  const tbody = document.getElementById('acctTableBody');
  tbody.innerHTML = '<tr><td colspan="9" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';

  Promise.all([
    $.getJSON('API/Admin/fetch_dean_accounts.php'),
    $.getJSON('API/Admin/fetch_departments.php'),
    $.getJSON('API/Admin/fetch_dean_assignments.php')
  ]).then(([deanRes, deptData, assignRes]) => {
    const deans   = deanRes.status === 'success' ? deanRes.data || [] : [];
    const depts   = Array.isArray(deptData) ? deptData : [];
    const assigns = assignRes.status === 'success' ? assignRes.data || [] : [];

    // Admins come from tbladmin â€” fetch separately
    $.getJSON('API/Admin/fetch_admin_accounts.php', adminRes => {
      const admins = adminRes.status === 'success' ? adminRes.data || [] : [];

      const adminRows = admins.map(a => ({
        id:          a.id,
        _table:      'admin',
        id_number:   a.admin_number,
        first_name:  a.first_name,
        middle_name: a.middle_name,
        last_name:   a.last_name,
        suffix:      a.suffix,
        email:       a.email,
        phone:       a.phone,
        username:    a.username || '',
        birthdate:   a.birthdate || '',
        is_active:   a.is_active,
        user_id:     a.user_id || null,
        otp_enabled: Number(a.otp_enabled ?? 1),
        first_login: Number(a.first_login ?? 1),
        type:        'admin',
        dept_name:   'â€”',
        dept_code:   'â€”',
      }));

      const deanRows = deans.map(d => {
        const asgn = assigns.filter(a => a.admin_account_id == d.id);
        const dept = asgn.length ? depts.find(dp => dp.id == asgn[0].department_id) : null;
        return {
          id:          d.id,
          _table:      'dean',
          id_number:   d.id_number,
          first_name:  d.first_name,
          middle_name: d.middle_name,
          last_name:   d.last_name,
          suffix:      d.suffix,
          email:       d.email,
          phone:       d.phone,
          username:    d.username || '',
          birthdate:   d.birthdate || '',
          is_active:   d.is_active,
          user_id:     d.user_id,
          otp_enabled: d.otp_enabled,
          first_login: d.first_login,
          admin_role:  d.admin_role || '',
          type:        d.admin_role || (asgn.length ? asgn[0].role : 'dean'),
          dept_name:   dept ? dept.dept_name : 'â€”',
          dept_code:   dept ? dept.dept_code : 'â€”',
        };
      });

      // Prevent duplicate people from appearing as both Admin and Dean/Secretary.
      // If a person exists in deanRows, hide their adminRows entry in this combined view.
      const deanKeys = new Set(
        deanRows.map(r => `${String(r.id_number || '').trim().toLowerCase()}|${String(r.email || '').trim().toLowerCase()}`)
      );
      const adminRowsFiltered = adminRows.filter(r => {
        const key = `${String(r.id_number || '').trim().toLowerCase()}|${String(r.email || '').trim().toLowerCase()}`;
        return !deanKeys.has(key);
      });

      allAccounts = [...adminRowsFiltered, ...deanRows];
      acctFilteredList = allAccounts;
      acctRenderPage();
    }).fail(() => {
      allAccounts = [];
      renderAccountTable([]);
    });
  });
}

function renderAccountTable(list) {
  const tbody = document.getElementById('acctTableBody');
  if (!list.length) {
    tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No accounts found.</td></tr>';
    return;
  }

  const typeBadge = t => {
    const m = {
      admin:     ['#e0f2fe','#0284c7','Admin'],
      dean:      ['#fef3c7','#b45309','Dean'],
      secretary: ['#dbeafe','#1d4ed8','Secretary'],
    };
    const [bg, color, lbl] = m[t] || ['#f1f5f9','#64748b', t];
    return `<span style="display:inline-block;padding:.18em .55em;border-radius:20px;font-size:.68rem;font-weight:700;background:${bg};color:${color};">${lbl}</span>`;
  };

  tbody.innerHTML = list.map((a, i) => {
    const name   = [a.last_name, a.first_name].filter(Boolean).join(', ');
    const active = parseInt(a.is_active) === 1;
    const isSelfAdmin = (a._table === 'admin' && String(a.id) === String(ADMIN_ID));
    const otpCell = (a._table === 'dean' || a._table === 'admin')
      ? (a.user_id ? otpHtml(a.user_id, a.otp_enabled, a.first_login) : '<span style="font-size:.72rem;color:#adb5bd;">N/A</span>')
      : '<span style="font-size:.72rem;color:#adb5bd;">N/A</span>';
    const deptCell = a._table === 'dean' && a.dept_name !== 'â€”'
      ? `<span style="background:var(--primary-light);color:var(--primary);border:1px solid rgba(26,158,120,.25);padding:.12em .45em;border-radius:20px;font-size:.68rem;font-weight:700;">${esc(a.dept_code)}</span> <span style="font-size:.78rem;">${esc(a.dept_name)}</span>`
      : `<span style="font-size:.76rem;color:#adb5bd;font-style:italic;">${a._table === 'admin' ? 'System-wide' : 'Not assigned'}</span>`;

    const emailCell = a.email
      ? `<div class="acct-email-cell"><i class="fas fa-envelope"></i>${esc(a.email)}</div>`
      : `<span style="font-size:.74rem;color:#adb5bd;font-style:italic;">â€”</span>`;

    return `<tr>
      <td>${i + 1}</td>
      <td>
        <div class="fw-semibold" style="font-size:.83rem;">${esc(name)}</div>
        <div style="font-size:.7rem;font-family:monospace;font-weight:700;color:var(--primary);margin-top:.1rem;">${esc(a.id_number || 'â€”')}</div>
      </td>
      <td style="font-family:monospace;font-size:.78rem;color:var(--primary);font-weight:700;">${esc(a.id_number || 'â€”')}</td>
      <td>${typeBadge(a.type)}</td>
      <td>${emailCell}</td>
      <td>${deptCell}</td>
      <td class="text-center"><span class="${active ? 'pill-active' : 'pill-inactive'}">${active ? 'Active' : 'Inactive'}</span></td>
      <td class="text-center">${otpCell}</td>
      <td class="text-center">
        <div class="d-flex gap-1 justify-content-center flex-wrap">
          <button class="btn-act btn-act-edit" onclick="editAcctModal('${esc(a.id)}','${esc(a._table)}')"><i class="fas fa-pen"></i> Edit</button>
          <button class="btn-act ${active ? 'btn-act-off' : 'btn-act-on'}" onclick="toggleAcctStatus('${esc(a.id)}','${esc(a._table)}',${active ? 0 : 1})">
            <i class="fas fa-${active ? 'ban' : 'check-circle'}"></i> ${active ? 'Deactivate' : 'Activate'}
          </button>
          <button class="btn-act btn-act-del" ${isSelfAdmin ? 'disabled title="You cannot delete the account currently logged in."' : ''} onclick="${isSelfAdmin ? '' : `deleteAcct('${esc(a.id)}','${esc(a._table)}')`}"><i class="fas fa-trash"></i></button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

function applyAcctFilters() {
  const q    = (document.getElementById('acctSearch')?.value || '').trim().toLowerCase();
  const type = document.getElementById('acctTypeFilter')?.value || '';
  const stat = document.getElementById('acctStatusFilter')?.value || '';
  let list   = allAccounts;
  if (q)    list = list.filter(a => (a.first_name + a.last_name + a.id_number + a.email).toLowerCase().includes(q));
  if (type) list = list.filter(a => a.type === type);
  if (stat !== '') list = list.filter(a => String(parseInt(a.is_active) === 1 ? 1 : 0) === stat);
  renderAccountTable(list);
}

$(document).on('input',  '#acctSearch',       applyAcctFilters);
$(document).on('change', '#acctTypeFilter',    applyAcctFilters);
$(document).on('change', '#acctStatusFilter',  applyAcctFilters);
$(document).on('input change', '#acctModal input, #acctModal select, #acctModal textarea', function() {
  if (!wizIsEdit) saveAcctDraft();
});
window.addEventListener('beforeunload', function() {
  if (!wizIsEdit) saveAcctDraft();
});
function formatPhLocalPhone(v) {
  let d = String(v || '').replace(/\D/g, '');
  if (d.startsWith('63')) d = d.slice(2);
  if (d.startsWith('0')) d = d.slice(1);
  d = d.slice(0, 10);
  if (d.length <= 3) return d;
  if (d.length <= 6) return d.slice(0,3) + '-' + d.slice(3);
  return d.slice(0,3) + '-' + d.slice(3,6) + '-' + d.slice(6);
}

$(document).on('input', '#acct_phone', function() {
  this.value = formatPhLocalPhone(this.value);
  const e = document.getElementById('err_phone');
  if (e) e.style.display = 'none';
  this.classList.remove('input-error');
  if (!wizIsEdit) saveAcctDraft();
});

function validateAcctBirthdateLive() {
  const el = document.getElementById('acct_birthdate');
  const err = document.getElementById('err_birthdate');
  if (!el || !err) return;

  const val = (el.value || '').trim();
  if (!val) {
    err.style.display = 'none';
    el.classList.remove('input-error');
    return;
  }

  const iso = toIsoDate(val);
  if (!iso) {
    err.style.display = 'none';
    el.classList.remove('input-error');
    return;
  }
  const bd = new Date(iso + 'T00:00:00');
  if (Number.isNaN(bd.getTime())) {
    err.style.display = 'none';
    el.classList.remove('input-error');
    return;
  }
  const now = new Date();
  const min = new Date('1900-01-01T00:00:00');
  const age = now.getFullYear() - bd.getFullYear() - ((now.getMonth() < bd.getMonth() || (now.getMonth() === bd.getMonth() && now.getDate() < bd.getDate())) ? 1 : 0);

  let msg = '';
  if (bd > now) msg = 'Birthdate cannot be in the future.';
  else if (bd < min) msg = 'Birthdate is too far in the past.';
  else if (age < 16) msg = 'Account holder must be at least 16 years old.';
  else if (age > 100) msg = 'Please enter a valid birthdate.';

  if (msg) {
    err.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + msg;
    err.style.display = 'flex';
    el.classList.add('input-error');
  } else {
    err.style.display = 'none';
    el.classList.remove('input-error');
  }
}

// While typing/selecting date, keep UI calm; run strict validation on change/blur.
$(document).on('input', '#acct_birthdate', function() {
  this.value = formatUsDateInput(this.value);
  const err = document.getElementById('err_birthdate');
  if (err) err.style.display = 'none';
  this.classList.remove('input-error');
});

$(document).on('change blur', '#acct_birthdate', function() {
  this.value = formatUsDateInput(this.value);
  validateAcctBirthdateLive();
  if (!wizIsEdit) saveAcctDraft();
});

function formatUsDateInput(v){
  const d = String(v||'').replace(/\D/g,'').slice(0,8);
  if(!d) return '';
  if(d.length<=2) return d;
  if(d.length<=4) return `${d.slice(0,2)}/${d.slice(2)}`;
  return `${d.slice(0,2)}/${d.slice(2,4)}/${d.slice(4)}`;
}
function toIsoDate(us){
  const m = String(us||'').trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  if(!m) return '';
  const mm=Number(m[1]), dd=Number(m[2]), yyyy=Number(m[3]);
  if(mm<1||mm>12||dd<1||dd>31||yyyy<1000) return '';
  const dt = new Date(`${yyyy}-${String(mm).padStart(2,'0')}-${String(dd).padStart(2,'0')}T00:00:00`);
  if(Number.isNaN(dt.getTime())) return '';
  return `${yyyy}-${String(mm).padStart(2,'0')}-${String(dd).padStart(2,'0')}`;
}
function isoToUsDate(iso){
  const m = String(iso||'').match(/^(\d{4})-(\d{2})-(\d{2})$/);
  return m ? `${m[2]}/${m[3]}/${m[1]}` : (iso||'');
}

/* â”€â”€ WIZARD STATE â”€â”€ */
let wizCurrentStep  = 0;
const WIZ_STEPS     = 4;
let wizSelectedType = '';
let wizIsEdit       = false;

const TL_ACCT_DRAFT_KEY = 'tl_acct_wizard_draft';
function collectAcctDraft() {
  return {
    step: wizCurrentStep,
    type: wizSelectedType || 'admin',
    fields: {
      acct_id_number: $('#acct_id_number').val() || '',
      acct_first_name: $('#acct_first_name').val() || '',
      acct_middle_name: $('#acct_middle_name').val() || '',
      acct_last_name: $('#acct_last_name').val() || '',
      acct_suffix: $('#acct_suffix').val() || '',
      acct_email: $('#acct_email').val() || '',
      acct_phone: $('#acct_phone').val() || '',
      acct_username: $('#acct_username').val() || '',
      acct_birthdate: $('#acct_birthdate').val() || '',
      acct_password: $('#acct_password').val() || '',
      acct_is_also_faculty: $('#acct_is_also_faculty').is(':checked') ? 1 : 0
    }
  };
}
function saveAcctDraft() {
  if (wizIsEdit) return;
  try { localStorage.setItem(TL_ACCT_DRAFT_KEY, JSON.stringify(collectAcctDraft())); } catch (_) {}
}
function clearAcctDraft() {
  try { localStorage.removeItem(TL_ACCT_DRAFT_KEY); } catch (_) {}
}
function loadAcctDraft() {
  try {
    const raw = localStorage.getItem(TL_ACCT_DRAFT_KEY);
    if (!raw) return null;
    const d = JSON.parse(raw);
    return d && d.fields ? d : null;
  } catch (_) {
    return null;
  }
}
function hasMeaningfulAcctDraft(draft) {
  if (!draft || !draft.fields) return false;
  const f = draft.fields || {};
  const textIds = [
    'acct_id_number','acct_first_name','acct_middle_name','acct_last_name','acct_suffix',
    'acct_email','acct_phone','acct_username','acct_birthdate','acct_password'
  ];
  const hasTypedText = textIds.some(id => String(f[id] || '').trim() !== '');
  const hasCheckedFlag = !!f.acct_is_also_faculty;
  return hasTypedText || hasCheckedFlag;
}
function applyAcctDraft(draft) {
  if (!draft || !draft.fields) return;
  const f = draft.fields;
  ['acct_id_number','acct_first_name','acct_middle_name','acct_last_name','acct_suffix','acct_email','acct_phone','acct_username','acct_birthdate','acct_password']
    .forEach(id => { if (f[id] !== undefined) $('#' + id).val(f[id]); });
  const bdDraft = $('#acct_birthdate').val();
  if (/^\d{4}-\d{2}-\d{2}$/.test(bdDraft)) $('#acct_birthdate').val(isoToUsDate(bdDraft));
  $('#acct_is_also_faculty').prop('checked', !!f.acct_is_also_faculty);
  wizSelectType(draft.type || 'admin', { fromDraft: true });
  const step = Math.max(0, Math.min(parseInt(draft.step || 0, 10), WIZ_STEPS - 1));
  wizSetStep(step);
}

function wizClearTypeSelection() {
  wizSelectedType = '';
  ['admin','dean','secretary'].forEach(t =>
    document.getElementById('wt_' + t)?.classList.remove('selected')
  );
  const fac = document.getElementById('wizAlsoFacWrap');
  if (fac) fac.style.display = 'none';
}

function wizSelectType(type, opts = {}) {
  const prevType = wizSelectedType;
  const fromDraft = !!opts.fromDraft;
  const autoAdvance = !!opts.autoAdvance;

  wizSelectedType = type;
  ['admin','dean','secretary'].forEach(t =>
    document.getElementById('wt_' + t)?.classList.toggle('selected', t === type)
  );

  const fac = document.getElementById('wizAlsoFacWrap');
  if (fac) fac.style.display = (type === 'dean' || type === 'secretary') ? 'flex' : 'none';

  if (!wizIsEdit && wizCurrentStep === 0 && !fromDraft && autoAdvance) {
    if (type === 'admin') {
      setTimeout(() => wizSetStep(1), 120);
    } else if (prevType === type) {
      // Dean/Secretary: toggle faculty first if needed, then click same card again to continue.
      wizSetStep(1);
    }
  }

  if (!wizIsEdit) saveAcctDraft();
}

function wizSetStep(step) {
  if (!wizIsEdit) saveAcctDraft();
  wizCurrentStep = step;
  for (let i = 0; i < WIZ_STEPS; i++) {
    document.getElementById('wizPanel' + i)?.classList.toggle('active', i === step);
    const el = document.getElementById('wizStep' + i);
    if (!el) continue;
    el.classList.remove('active','done');
    if (i < step)  el.classList.add('done');
    if (i === step) el.classList.add('active');
  }
  const back = document.getElementById('wizBackBtn');
  const next = document.getElementById('wizNextBtn');
  if (back) back.style.display = (step > 0 && !wizIsEdit) ? '' : 'none';
  if (next) {
    next.style.display = (!wizIsEdit && step === 0) ? 'none' : '';
    next.innerHTML = step === WIZ_STEPS - 1
      ? '<i class="fas fa-check me-1"></i> Save Account'
      : 'Next <i class="fas fa-arrow-right ms-1"></i>';
  }
  if (step === WIZ_STEPS - 1) wizBuildReview();
}

function wizNext() {
  if (!wizIsEdit) saveAcctDraft();
  if (wizIsEdit) { submitAcctModal(); return; }
  if (!wizValidateStep(wizCurrentStep)) return;
  if (wizCurrentStep === WIZ_STEPS - 1) { submitAcctModal(); return; }
  wizSetStep(wizCurrentStep + 1);
}

function wizBack() {
  if (!wizIsEdit) saveAcctDraft();
  if (wizCurrentStep > 0) wizSetStep(wizCurrentStep - 1);
}

function wizValidateStep(step) {
  ['id_number','first_name','last_name','email','phone','username','birthdate','password']
    .forEach(f => {
      document.getElementById('err_' + f)?.setAttribute('style','display:none');
      document.getElementById('acct_' + f)?.classList.remove('input-error');
    });
  let valid = true;
  const err = (fieldId, msg) => {
    const el  = document.getElementById('err_' + fieldId);
    const inp = document.getElementById('acct_' + fieldId);
    if (el)  { el.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + msg; el.style.display = 'flex'; }
    if (inp) inp.classList.add('input-error');
    valid = false;
  };
  if (step === 0) {
    if (!wizSelectedType) {
      showToast('Please choose an account type first.', 'error');
      valid = false;
    }
  }
  if (step === 1) {
    if (!$('#acct_id_number').val().trim())  err('id_number',  'ID number is required.');
    if (!$('#acct_first_name').val().trim()) err('first_name', 'First name is required.');
    if (!$('#acct_last_name').val().trim())  err('last_name',  'Last name is required.');
  }
  if (step === 2) {
    if (!$('#acct_email').val().trim())    err('email',    'Email is required.');

    const phoneVal = ($('#acct_phone').val() || '').trim();
    if (phoneVal && !/^9\d{2}-\d{3}-\d{4}$/.test(phoneVal)) {
      err('phone','Use PH format: 919-624-9611.');
    }

    if (!$('#acct_username').val().trim()) err('username', 'Username is required.');

    const bdValRaw = $('#acct_birthdate').val();
    const bdVal = toIsoDate(bdValRaw);
    if (!bdVal) {
      err('birthdate', bdValRaw ? 'Use MM/DD/YYYY format.' : 'Birthdate is required.');
    } else {
      const bd  = new Date(bdVal + 'T00:00:00');
      const now = new Date();
      const min = new Date('1900-01-01T00:00:00');
      const age = now.getFullYear() - bd.getFullYear() - ((now.getMonth() < bd.getMonth() || (now.getMonth() === bd.getMonth() && now.getDate() < bd.getDate())) ? 1 : 0);

      if (bd > now) err('birthdate','Birthdate cannot be in the future.');
      else if (bd < min) err('birthdate','Birthdate is too far in the past.');
      else if (age < 16) err('birthdate','Account holder must be at least 16 years old.');
      else if (age > 100) err('birthdate','Please enter a valid birthdate.');
    }

    if (!wizIsEdit && !$('#acct_password').val().trim()) err('password', 'Password is required for new accounts.');
  }
  return valid;
}

function wizBuildReview() {
  const type = wizIsEdit ? ($('#acct_edit_table').val() || wizSelectedType) : wizSelectedType;
  const typeLabels = { admin:'Admin', dean:'Dean', secretary:'Secretary' };
  const pw = $('#acct_password').val().trim();
  const bdVal = $('#acct_birthdate').val() || 'â€”';
  const rows = [
    { label:'Type',       val: `<span style="font-weight:800;color:var(--primary);">${typeLabels[type]||type}</span>` },
    { label:'ID Number',  val: $('#acct_id_number').val() || 'â€”' },
    { label:'First Name', val: $('#acct_first_name').val() || 'â€”' },
    { label:'Middle Name',val: $('#acct_middle_name').val() || 'N/A' },
    { label:'Last Name',  val: $('#acct_last_name').val() || 'â€”' },
    { label:'Suffix',     val: $('#acct_suffix').val() || 'N/A' },
    { label:'Email',      val: $('#acct_email').val() || 'â€”' },
    { label:'Phone',      val: $('#acct_phone').val() ? '+63 ' + $('#acct_phone').val() : 'â€”' },
    { label:'Username',   val: '@' + ($('#acct_username').val() || 'â€”') },
    { label:'Birthdate',  val: bdVal },
    { label:'Password',   val: pw ? 'â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢' : (wizIsEdit ? '(unchanged)' : 'â€”') },
  ];
  if (type !== 'admin') rows.push({ label:'Also Faculty', val: $('#acct_is_also_faculty').is(':checked') ? 'âœ… Yes' : 'âŒ No' });
  document.getElementById('wizReviewContent').innerHTML = rows.map(r =>
    `<div class="review-row"><div class="review-label">${r.label}</div><div class="review-val">${r.val}</div></div>`
  ).join('');
}

window.openAddAcctModal = function() {
  wizIsEdit = false;
  setAcctAutoPwMode(true);
  $('#acct_edit_id,#acct_edit_table').val('');
  $('#acctModalTitle').text('Add Account');
  $('#acctModalTitleIcon').attr('class','fas fa-user-plus me-2');
  $('#acctModal').removeClass('acct-edit-mode');
  $('#wizStepsBar').show();
  $('#acct_pw_req').show();
  $('#acct_pw_hint').hide();

  const draft = loadAcctDraft();
  const openModal = () => bootstrap.Modal.getOrCreateInstance(document.getElementById('acctModal')).show();

  resetAcctModal();
  document.getElementById('wizBackBtn').style.display = 'none';

  if (!draft || !hasMeaningfulAcctDraft(draft)) {
    if (draft && !hasMeaningfulAcctDraft(draft)) clearAcctDraft();
    wizClearTypeSelection();
    wizSetStep(0);
    refreshAutoAcctPassword();
    openModal();
    return;
  }

  Swal.fire({
    title: 'Resume previous draft?',
    text: 'We found unsaved Add Account data from your last session.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#1a9e78',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Resume draft',
    cancelButtonText: 'Start fresh',
    reverseButtons: true
  }).then(result => {
    if (result.isConfirmed) {
      applyAcctDraft(draft);
      refreshAutoAcctPassword();
    } else {
      clearAcctDraft();
      wizClearTypeSelection();
      wizSetStep(0);
      refreshAutoAcctPassword();
    }
    openModal();
  });
};

window.acctTypeChanged = function() {};

window.resetAcctModal = function() {
  ['acct_id_number','acct_first_name','acct_middle_name','acct_last_name','acct_suffix',
   'acct_email','acct_phone','acct_username','acct_birthdate','acct_password']
    .forEach(id => $('#' + id).val(''));
  $('#acct_is_also_faculty').prop('checked', false);
  ['id_number','first_name','last_name','email','phone','username','birthdate','password']
    .forEach(f => {
      document.getElementById('err_' + f)?.setAttribute('style','display:none');
      document.getElementById('acct_' + f)?.classList.remove('input-error');
    });
  setAcctAutoPwMode(true);
};

let acctPwAutoMode = true;
let _acctPwProgrammatic = false;

function buildAutoAcctPassword() {
  const fn = $('#acct_first_name').val().trim();
  const ln = $('#acct_last_name').val().trim();
  const bd = toIsoDate($('#acct_birthdate').val());
  if (!fn || !ln || !bd) return '';
  const p = bd.split('-');
  if (p.length !== 3) return '';
  return fn[0].toLowerCase() + p[1] + p[2] + p[0] + ln[0].toUpperCase();
}

function setAcctAutoPwMode(on) {
  acctPwAutoMode = !!on;
}

// Compatibility no-op (older calls still exist in other modal flows).
function setAcctBirthdateBounds() {}

function refreshAutoAcctPassword() {
  if (wizIsEdit || !acctPwAutoMode) return;
  const pw = buildAutoAcctPassword();
  if (!pw) return;
  _acctPwProgrammatic = true;
  $('#acct_password').val(pw);
  _acctPwProgrammatic = false;
}

$(document).on('input change', '#acct_first_name,#acct_last_name,#acct_birthdate,#acct_username', function() {
  refreshAutoAcctPassword();
});

$(document).on('input', '#acct_password', function() {
  if (_acctPwProgrammatic || wizIsEdit) return;
  const current = $('#acct_password').val().trim();
  const expected = buildAutoAcctPassword();
  if (current && expected && current !== expected) setAcctAutoPwMode(false);
});

window.editAcctModal = function(id, table) {
  const a = allAccounts.find(x => x.id == id && x._table === table);
  if (!a) return;
  wizIsEdit = true;
  setAcctAutoPwMode(false);
  wizSelectedType = table;
  $('#acct_edit_id').val(a.id);
  $('#acct_edit_table').val(a._table);
  $('#acctModalTitle').text('Edit Account');
  $('#acctModalTitleIcon').attr('class','fas fa-user-pen me-2');
  $('#acctModal').addClass('acct-edit-mode');
  $('#wizStepsBar').hide();
  $('#acct_pw_req').hide();
  $('#acct_pw_hint').show();
  $('#wizAlsoFacWrap').toggle(a._table === 'dean' || a._table === 'secretary');
  $('#acct_id_number').val(a.id_number || '');
  $('#acct_first_name').val(a.first_name || '');
  $('#acct_middle_name').val(a.middle_name || '');
  $('#acct_last_name').val(a.last_name || '');
  $('#acct_suffix').val(a.suffix || '');
  $('#acct_email').val(a.email || '');
  $('#acct_phone').val(formatPhLocalPhone(a.phone || ''));
  $('#acct_username').val(a.username || '');
  $('#acct_birthdate').val(isoToUsDate(a.birthdate || ''));
  $('#acct_password').val('');
  for (let i = 0; i < WIZ_STEPS; i++)
    document.getElementById('wizPanel' + i)?.classList.remove('active');
  document.getElementById('wizPanel1').classList.add('active');
  document.getElementById('wizPanel2').classList.add('active');
  document.getElementById('wizBackBtn').style.display = 'none';
  const nxt = document.getElementById('wizNextBtn');
  nxt.innerHTML = '<i class="fas fa-check me-1"></i> Save Changes';
  bootstrap.Modal.getOrCreateInstance(document.getElementById('acctModal')).show();
};

window.submitAcctModal = function() {
  if (wizIsEdit) {
    if (!wizValidateStep(1) | !wizValidateStep(2)) return;
  }
  const btn    = document.getElementById('wizNextBtn');
  const editId = $('#acct_edit_id').val();
  const type   = editId ? ($('#acct_edit_table').val() || wizSelectedType) : wizSelectedType;
  const idNo   = $('#acct_id_number').val().trim();
  const fn     = $('#acct_first_name').val().trim();
  const ln     = $('#acct_last_name').val().trim();
  const email  = $('#acct_email').val().trim();
  const uname  = $('#acct_username').val().trim();
  const pw     = $('#acct_password').val().trim();
  const isAlso = $('#acct_is_also_faculty').is(':checked') ? 1 : 0;
  if (!idNo || !fn || !ln || !email || !uname || (!editId && !pw)) {
    showToast('Please fill all required fields.', 'error'); return;
  }
  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Savingâ€¦';
  const url  = type === 'admin' ? 'API/Admin/save_admin_account.php' : 'API/Admin/save_dean_account.php';
  const data = {
    id: editId || null,
    id_number: idNo,
    first_name: fn,
    middle_name: $('#acct_middle_name').val().trim(),
    last_name: ln,
    suffix: $('#acct_suffix').val().trim(),
    email, phone: formatPhLocalPhone($('#acct_phone').val().trim()),
    username: uname,
    password: editId ? (pw || null) : pw,
    birthdate: toIsoDate($('#acct_birthdate').val()),
    role: type === 'admin' ? null : type,
    is_also_faculty: type !== 'admin' ? isAlso : 0,
  };
  $.ajax({
    url, type: 'POST', contentType: 'application/json',
    data: JSON.stringify(data), dataType: 'json',
    success(r) {
      btn.disabled = false; btn.innerHTML = origHtml;
      if (r.status === 'success') {
        showToast(r.message, 'success');
        if (!editId) clearAcctDraft();
        bootstrap.Modal.getInstance(document.getElementById('acctModal')).hide();
        loadAccountsSection();
      } else showToast(r.message, 'error');
    },
    error() {
      btn.disabled = false; btn.innerHTML = origHtml;
      showToast('Server error.', 'error');
    }
  });
};

document.addEventListener('hidden.bs.modal', function(e) {
  if (e.target?.id === 'acctModal') {
    wizIsEdit = false; wizCurrentStep = 0;
    for (let i = 0; i < WIZ_STEPS; i++)
      document.getElementById('wizPanel' + i)?.classList.remove('active');
    document.getElementById('wizPanel0')?.classList.add('active');
    $('#acctModalTitleIcon').attr('class','fas fa-user-plus me-2');
    $('#acctModal').removeClass('acct-edit-mode');
  }
});

function toggleAcctStatus(id, table, newVal) {
  Swal.fire({
    title: newVal ? 'Activate account?' : 'Deactivate account?',
    icon: 'question', showCancelButton: true,
    confirmButtonColor: '#1a9e78', confirmButtonText: 'Yes'
  }).then(r => {
    if (!r.isConfirmed) return;
    const url = table === 'admin'
      ? 'API/Admin/toggle_admin_status.php'
      : 'API/Admin/toggle_dean_status.php';
    $.ajax({
      url, type: 'POST', contentType: 'application/json',
      data: JSON.stringify({ id, is_active: newVal }), dataType: 'json',
      success(res) { showToast(res.message, res.status); loadAccountsSection(); },
      error() { showToast('Server error.', 'error'); }
    });
  });
}

function deleteAcct(id, table) {
  Swal.fire({
    title: 'Delete this account?',
    text: table === 'dean' ? 'All department assignments will also be removed.' : 'This cannot be undone.',
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#d93025', confirmButtonText: 'Yes, delete'
  }).then(r => {
    if (!r.isConfirmed) return;
    const url = table === 'admin'
      ? 'API/Admin/delete_admin_account.php'
      : 'API/Admin/delete_dean_account.php';
    $.ajax({
      url, type: 'POST', contentType: 'application/json',
      data: JSON.stringify({ id }), dataType: 'json',
      success(res) { showToast(res.message, res.status); loadAccountsSection(); },
      error() { showToast('Server error.', 'error'); }
    });
  });
}

/* â•â• ACCOUNT MANAGEMENT v2 â€” filter + 3-dot + bulk â•â• */

const acctFilters = { type: '', status: '' };
let acctSelected    = new Set();
let openDotMenuId   = null;
let acctPage        = 1;
let acctPageSize    = 10;
let acctTotalPages  = 1;
let acctFilteredList = [];
let acctSelectMode  = false;

function acctToggleControlMenu(ev) {
  ev?.stopPropagation();
  const m = document.getElementById('acctCtrlMenu');
  m?.classList.toggle('open');
}
function acctCloseControlMenu() {
  document.getElementById('acctCtrlMenu')?.classList.remove('open');
}
function acctOpenFilters() {
  acctCloseControlMenu();
  if (acctSelectMode) acctExitSelectMode();
  acctToggleFilter();
}
function acctEnterSelectMode() {
  acctCloseControlMenu();
  const panel = document.getElementById('acctFilterPanel');
  const btn   = document.getElementById('acctFilterToggle');
  panel?.classList.remove('open');
  btn?.classList.remove('active');
  document.getElementById('section-accounts')?.classList.remove('filter-open');
  acctSelectMode = true;
  document.getElementById('section-accounts')?.classList.add('acct-select-mode');
  document.getElementById('acctFilterToggle')?.classList.add('active');
  acctUpdateBulkBar();
}
function acctExitSelectMode() {
  acctSelectMode = false;
  document.getElementById('section-accounts')?.classList.remove('acct-select-mode');
  document.getElementById('acctFilterToggle')?.classList.remove('active');
  acctClearSelection();
}

/* Filter panel toggle */
function acctToggleFilter() {
  const panel = document.getElementById('acctFilterPanel');
  const btn   = document.getElementById('acctFilterToggle');
  const section = document.getElementById('section-accounts');
  if (!panel || !btn) return;
  if (!panel.classList.contains('open') && acctSelectMode) {
    acctExitSelectMode();
  }
  panel.classList.toggle('open');
  const isOpen = panel.classList.contains('open');
  btn.classList.toggle('active', isOpen);
  section?.classList.toggle('filter-open', isOpen);
}

/* Close filter panel on outside click */
document.addEventListener('click', e => {
  const panel = document.getElementById('acctFilterPanel');
  const btn   = document.getElementById('acctFilterToggle');
  const inAcctControls = !!e.target.closest('.acct-ctrl-wrap');
  if (panel?.classList.contains('open') && !panel.contains(e.target) && !btn.contains(e.target) && !inAcctControls) {
    panel.classList.remove('open');
    btn.classList.remove('active');
    document.getElementById('section-accounts')?.classList.remove('filter-open');
  }
  if (!e.target.closest('.acct-ctrl-wrap')) acctCloseControlMenu();
  // close any open dot menu
  if (!e.target.closest('.dot-menu-wrap')) {
    document.querySelectorAll('.dot-menu.open').forEach(m => m.classList.remove('open'));
    document.querySelectorAll('.dot-menu-btn.open').forEach(b => b.classList.remove('open'));
    openDotMenuId = null;
  }
});

function acctSetFilter(key, val, el) {
  acctFilters[key] = val;
  el.closest('.afp-chips').querySelectorAll('.afp-chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  acctApplyAll();
  acctUpdateFilterBadge();
}

function acctResetFilters() {
  acctFilters.type = ''; acctFilters.status = '';
  document.querySelectorAll('.afp-chip').forEach(c => {
    c.classList.toggle('active', c.dataset.val === '');
  });
  document.getElementById('acctSearch').value = '';
  acctApplyAll();
  acctUpdateFilterBadge();
}

function acctUpdateFilterBadge() {
  const count = (acctFilters.type ? 1 : 0) + (acctFilters.status !== '' ? 1 : 0);
  const badge = document.getElementById('acctFilterBadge');
  const btn   = document.getElementById('acctFilterToggle');
  badge.textContent = count;
  btn.classList.toggle('has-filter', count > 0);
}

function acctApplyAll() {
  const q = (document.getElementById('acctSearch')?.value || '').trim().toLowerCase();
  let list = allAccounts;
  if (q) list = list.filter(a =>
    (a.first_name + a.last_name + a.id_number + a.email).toLowerCase().includes(q)
  );
  if (acctFilters.type) list = list.filter(a => a.type === acctFilters.type);
  if (acctFilters.status !== '') list = list.filter(a =>
    String(parseInt(a.is_active) === 1 ? 1 : 0) === acctFilters.status
  );
  acctFilteredList = list;
  acctPage = 1; // reset to first page on filter change
  acctRenderPage();
}

function acctRenderPage() {
  const total = acctFilteredList.length;
  acctTotalPages = Math.max(1, Math.ceil(total / acctPageSize));
  acctPage = Math.min(acctPage, acctTotalPages);
  const start = (acctPage - 1) * acctPageSize;
  const end   = Math.min(start + acctPageSize, total);
  const pageList = acctFilteredList.slice(start, end);

  renderAccountTable(pageList);

  // Info
  const info = document.getElementById('acctPgInfo');
  if (info) info.textContent = total
    ? `Showing ${start + 1}-${end} of ${total}`
    : 'No results';

  // Prev / Next / First / Last
  document.getElementById('acctPgFirst').disabled = acctPage <= 1;
  document.getElementById('acctPgPrev').disabled  = acctPage <= 1;
  document.getElementById('acctPgNext').disabled  = acctPage >= acctTotalPages;
  document.getElementById('acctPgLast').disabled  = acctPage >= acctTotalPages;

  // Page number buttons
  const pages = document.getElementById('acctPgPages');
  if (!pages) return;
  const btns = [];
  const range = (a, b) => Array.from({ length: b - a + 1 }, (_, i) => a + i);
  let nums;
  if (acctTotalPages <= 7) {
    nums = range(1, acctTotalPages);
  } else if (acctPage <= 4) {
    nums = [...range(1, 5), '...', acctTotalPages];
  } else if (acctPage >= acctTotalPages - 3) {
    nums = [1, '...', ...range(acctTotalPages - 4, acctTotalPages)];
  } else {
    nums = [1, '...', acctPage - 1, acctPage, acctPage + 1, '...', acctTotalPages];
  }
  pages.innerHTML = nums.map(n =>
    n === '...'
      ? `<span class="apg-ellipsis">...</span>`
      : `<button class="apg-page-btn${n === acctPage ? ' active' : ''}" onclick="acctGoPage(${n})">${n}</button>`
  ).join('');
}

function acctGoPage(n) {
  acctPage = Math.max(1, Math.min(n, acctTotalPages));
  acctRenderPage();
}

function acctChangePageSize(val) {
  acctPageSize = parseInt(val);
  acctPage = 1;
  acctRenderPage();
}

/* Checkbox selection */
function acctToggleAll(cb) {
  if (!acctSelectMode) return;
  document.querySelectorAll('.acct-row-cb').forEach(c => {
    c.checked = cb.checked;
    const id  = c.dataset.id;
    const tbl = c.dataset.table;
    const key = id + '_' + tbl;
    if (cb.checked) acctSelected.add(key); else acctSelected.delete(key);
    c.closest('tr').classList.toggle('row-selected', cb.checked);
  });
  acctUpdateBulkBar();
}

function acctToggleRow(cb) {
  if (!acctSelectMode) { cb.checked = false; return; }
  const id  = cb.dataset.id;
  const tbl = cb.dataset.table;
  const key = id + '_' + tbl;
  if (cb.checked) acctSelected.add(key); else acctSelected.delete(key);
  cb.closest('tr').classList.toggle('row-selected', cb.checked);
  const allCbs  = document.querySelectorAll('.acct-row-cb');
  const allSel  = [...allCbs].every(c => c.checked);
  document.getElementById('acctSelectAll').checked = allSel;
  acctUpdateBulkBar();
}

function acctClearSelection() {
  acctSelected.clear();
  document.querySelectorAll('.acct-row-cb').forEach(c => { c.checked = false; });
  const sa = document.getElementById('acctSelectAll');
  if (sa) { sa.checked = false; sa.indeterminate = false; }
  document.querySelectorAll('#acctTableBody tr').forEach(r => r.classList.remove('row-selected'));
  acctUpdateBulkBar();
}

function acctUpdateBulkBar() {
  const bar = document.getElementById('acctBulkBar');
  const cnt = document.getElementById('acctBulkCount');
  bar.classList.toggle('show', acctSelectMode && acctSelected.size > 0);
  cnt.textContent = acctSelected.size + ' selected';
}

/* 3-dot menu */
/* â”€â”€ PORTAL DOT MENU (mobile-safe, no clipping) â”€â”€ */
(function() {
  let _portal = null;
  function getPortal() {
    if (!_portal) {
      _portal = document.createElement('div');
      _portal.id = 'dotMenuPortal';
      _portal.style.cssText = 'position:fixed;z-index:99999;display:none;';
      document.body.appendChild(_portal);
    }
    return _portal;
  }
  function closePortal() {
    const p = getPortal();
    p.style.display = 'none';
    p.innerHTML = '';
    document.querySelectorAll('.dot-menu-btn.open').forEach(b => b.classList.remove('open'));
    openDotMenuId = null;
  }
  document.addEventListener('click', e => {
    if (!e.target.closest('.dot-menu-btn') && !e.target.closest('#dotMenuPortal')) closePortal();
  });

  window.openDotMenu = function(id, tbl, btnEl) {
    const key = id + '_' + tbl;
    if (openDotMenuId === key) { closePortal(); return; }
    closePortal();
    openDotMenuId = key;
    btnEl.classList.add('open');

    const active = window.allAccounts?.find(a => String(a.id) === String(id) && a._table === tbl);
    const isActive = active ? parseInt(active.is_active) === 1 : true;

    const p = getPortal();
    p.innerHTML = `
      <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:12px;
                  box-shadow:0 8px 32px rgba(0,0,0,.22);overflow:hidden;min-width:165px;">
        <button onclick="editAcctModal('${esc(id)}','${esc(tbl)}');document.getElementById('dotMenuPortal').style.display='none';"
          style="display:flex;align-items:center;gap:.65rem;padding:.65rem 1rem;font-size:.84rem;
                 font-weight:600;color:var(--text);background:none;border:none;width:100%;
                 text-align:left;cursor:pointer;font-family:inherit;"
          onmouseover="this.style.background='var(--primary-light)';this.style.color='var(--primary)'"
          onmouseout="this.style.background='none';this.style.color='var(--text)'">
          <i class="fas fa-pen" style="width:14px;text-align:center;font-size:.78rem;opacity:.65;"></i> Edit
        </button>
        <button onclick="toggleAcctStatus('${esc(id)}','${esc(tbl)}',${isActive ? 0 : 1});document.getElementById('dotMenuPortal').style.display='none';"
          style="display:flex;align-items:center;gap:.65rem;padding:.65rem 1rem;font-size:.84rem;
                 font-weight:600;color:var(--text);background:none;border:none;width:100%;
                 text-align:left;cursor:pointer;font-family:inherit;"
          onmouseover="this.style.background='var(--primary-light)';this.style.color='var(--primary)'"
          onmouseout="this.style.background='none';this.style.color='var(--text)'">
          <i class="fas fa-${isActive ? 'ban' : 'check-circle'}" style="width:14px;text-align:center;font-size:.78rem;opacity:.65;"></i>
          ${isActive ? 'Deactivate' : 'Activate'}
        </button>
        <div style="height:1px;background:var(--border);margin:.2rem 0;"></div>
        <button onclick="deleteAcct('${esc(id)}','${esc(tbl)}');document.getElementById('dotMenuPortal').style.display='none';"
          style="display:flex;align-items:center;gap:.65rem;padding:.65rem 1rem;font-size:.84rem;
                 font-weight:600;color:var(--danger);background:none;border:none;width:100%;
                 text-align:left;cursor:pointer;font-family:inherit;"
          onmouseover="this.style.background='#fdecea'"
          onmouseout="this.style.background='none'">
          <i class="fas fa-trash" style="width:14px;text-align:center;font-size:.78rem;opacity:.65;"></i> Delete
        </button>
      </div>`;

    const rect = btnEl.getBoundingClientRect();
    const menuH = 145;
    const spaceBelow = window.innerHeight - rect.bottom;
    p.style.display = 'block';
    p.style.right   = Math.max(8, window.innerWidth - rect.right) + 'px';
    p.style.left    = 'auto';
    if (spaceBelow < menuH) {
      p.style.bottom = (window.innerHeight - rect.top + 6) + 'px';
      p.style.top    = 'auto';
    } else {
      p.style.top    = (rect.bottom + 6) + 'px';
      p.style.bottom = 'auto';
    }
  };
})();

function acctToolbarFocus(focused) {
  const btn = document.getElementById('addAcctBtn');
  if (!btn) return;
  if (focused) {
    btn.style.display = 'inline-flex';
  } else {
    setTimeout(() => {
      if (!document.activeElement?.closest('#addAcctBtn')) {
        btn.style.display = 'none';
      }
    }, 200);
  }
}

/* Bulk operations */
function bulkAcctActivate() {
  const ids = [...acctSelected];
  Swal.fire({ title: `Activate ${ids.length} account(s)?`, icon: 'question', showCancelButton: true, confirmButtonColor: '#1a9e78', confirmButtonText: 'Yes' }).then(r => {
    if (!r.isConfirmed) return;
    Promise.all(ids.map(k => {
      const [id, tbl] = k.split('_');
      const url = tbl === 'admin' ? 'API/Admin/toggle_admin_status.php' : 'API/Admin/toggle_dean_status.php';
      return $.ajax({ url, type: 'POST', contentType: 'application/json', data: JSON.stringify({ id, is_active: 1 }), dataType: 'json' });
    })).then(() => { showToast('Accounts activated.', 'success'); acctClearSelection(); loadAccountsSection(); })
      .catch(() => { showToast('One or more activations failed. Please refresh.', 'error'); loadAccountsSection(); });
  });
}

function bulkAcctDeactivate() {
  const ids = [...acctSelected];
  Swal.fire({ title: `Deactivate ${ids.length} account(s)?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#f57c00', confirmButtonText: 'Yes' }).then(r => {
    if (!r.isConfirmed) return;
    Promise.all(ids.map(k => {
      const [id, tbl] = k.split('_');
      const url = tbl === 'admin' ? 'API/Admin/toggle_admin_status.php' : 'API/Admin/toggle_dean_status.php';
      return $.ajax({ url, type: 'POST', contentType: 'application/json', data: JSON.stringify({ id, is_active: 0 }), dataType: 'json' });
    })).then(() => { showToast('Accounts deactivated.', 'success'); acctClearSelection(); loadAccountsSection(); })
      .catch(() => { showToast('One or more deactivations failed. Please refresh.', 'error'); loadAccountsSection(); });
  });
}

function bulkAcctDelete() {
  const ids = [...acctSelected];
  Swal.fire({ title: `Delete ${ids.length} account(s)?`, text: 'This cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d93025', confirmButtonText: 'Delete All' }).then(r => {
    if (!r.isConfirmed) return;
    Promise.all(ids.map(k => {
      const [id, tbl] = k.split('_');
      const url = tbl === 'admin' ? 'API/Admin/delete_admin_account.php' : 'API/Admin/delete_dean_account.php';
      return $.ajax({ url, type: 'POST', contentType: 'application/json', data: JSON.stringify({ id }), dataType: 'json' });
    })).then(() => { showToast('Accounts deleted.', 'success'); acctClearSelection(); loadAccountsSection(); })
      .catch(() => { showToast('One or more deletions failed. Please refresh.', 'error'); loadAccountsSection(); });
  });
}
window.renderAccountTable = function(list) {
  acctSelected.clear();
  acctUpdateBulkBar();
  document.getElementById('acctSelectAll').checked = false;

  const tbody = document.getElementById('acctTableBody');
  if (!list.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-5" style="font-size:.85rem;color:var(--text-muted);">No accounts found.</td></tr>';
    return;
  }

  const typeBadge = t => {
    const m = {
      admin:     ['#e0f2fe','#0284c7','Admin'],
      dean:      ['#fef3c7','#b45309','Dean'],
      secretary: ['#dbeafe','#1d4ed8','Secretary'],
    };
    const [bg, color, lbl] = m[t] || ['#f1f5f9','#64748b', t];
    return `<span style="display:inline-flex;align-items:center;padding:.18em .55em;border-radius:20px;font-size:.67rem;font-weight:700;background:${bg};color:${color};border:1px solid ${color}33;white-space:nowrap;">${lbl}</span>`;
  };

  tbody.innerHTML = list.map(a => {
    const name   = [a.last_name, a.first_name].filter(Boolean).join(', ');
    const active = parseInt(a.is_active) === 1;
    const otpCell = (a._table === 'dean' || a._table === 'admin')
      ? (a.user_id
          ? otpHtml(a.user_id, a.otp_enabled, a.first_login)
          : '<span style="font-size:.72rem;color:#adb5bd;">No account</span>')
      : '<span style="font-size:.72rem;color:#adb5bd;">â€”</span>';
    const deptCell = a._table === 'dean' && a.dept_name !== 'â€”'
      ? `<div style="display:flex;flex-direction:column;align-items:flex-start;gap:.22rem;">
          <span style="background:var(--primary-light);color:var(--primary);border:1px solid rgba(26,158,120,.25);padding:.1em .42em;border-radius:20px;font-size:.67rem;font-weight:700;line-height:1;">${esc(a.dept_code)}</span>
          <span style="font-size:.78rem;line-height:1.2;">${esc(a.dept_name)}</span>
        </div>`
      : `<span style="font-size:.74rem;color:#adb5bd;font-style:italic;">${a._table === 'admin' ? 'System-wide' : 'Not assigned'}</span>`;

    return `<tr>
      <td class="acct-select-col" style="width:36px;text-align:center;padding-left:.65rem;">
        <input type="checkbox" class="acct-cb acct-row-cb"
          data-id="${esc(a.id)}" data-table="${esc(a._table)}"
          onchange="acctToggleRow(this)">
      </td>
      <td style="min-width:0;">
        <div style="font-weight:600;font-size:.83rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;">${esc(name)}</div>
        <div style="font-size:.69rem;color:var(--text-muted);margin-top:1px;">${esc(a.email || 'â€”')}</div>
      </td>
      <td style="font-family:monospace;font-size:.78rem;color:var(--primary);font-weight:700;white-space:nowrap;">${esc(a.id_number || 'â€”')}</td>
      <td>${typeBadge(a.type)}</td>
      <td>${deptCell}</td>
      <td class="text-center"><span class="${active ? 'pill-active' : 'pill-inactive'}">${active ? 'Active' : 'Inactive'}</span></td>
      <td class="text-center">${otpCell}</td>
      <td style="width:44px;min-width:44px;padding:0 4px;text-align:center;position:relative;overflow:visible;">
        <div class="dot-menu-wrap" style="position:relative;display:inline-block;">
          <button class="dot-menu-btn" onclick="openDotMenu('${esc(a.id)}','${esc(a._table)}',this)" title="Actions">
            <i class="fas fa-ellipsis-v"></i>
          </button>
          <div class="dot-menu" id="dm_${esc(a.id)}_${esc(a._table)}">
            <button class="dot-menu-item" onclick="editAcctModal('${esc(a.id)}','${esc(a._table)}')">
              <i class="fas fa-pen"></i> Edit
            </button>
            <button class="dot-menu-item" onclick="toggleAcctStatus('${esc(a.id)}','${esc(a._table)}',${active ? 0 : 1})">
              <i class="fas fa-${active ? 'ban' : 'check-circle'}"></i> ${active ? 'Deactivate' : 'Activate'}
            </button>
            <div class="dot-menu-divider"></div>
            <button class="dot-menu-item danger" onclick="deleteAcct('${esc(a.id)}','${esc(a._table)}')">
              <i class="fas fa-trash"></i> Delete
            </button>
          </div>
        </div>
      </td>
    </tr>`;
  }).join('');
};
</script>

<script id="tl-filter-pagination-helpers">
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   TL FILTER PANEL + PAGINATION HELPERS
   Shared by: Programs, Dean Accounts, Dean Assignments, Students
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */

/* â”€â”€ Filter panel toggle â”€â”€ */
function tlToggleFilterPanel(panelId, toggleId) {
  const panel  = document.getElementById(panelId);
  const toggle = document.getElementById(toggleId);
  if (!panel) return;
  const open = panel.classList.toggle('open');
  if (toggle) toggle.classList.toggle('active', open);
  const section = panel.closest('.admin-section');
  section?.classList.toggle('filter-open', open);
}

/* â”€â”€ Update badge count (number of active filters) â”€â”€ */
function tlUpdateBadge(badgeId, filterIds) {
  const badge = document.getElementById(badgeId);
  if (!badge) return;
  const count = filterIds.filter(id => {
    const el = document.getElementById(id);
    return el && el.value !== '';
  }).length;
  badge.textContent = count;
  badge.style.display = count > 0 ? 'inline-flex' : 'none';
}

/* â”€â”€ Set a hidden <select> via chip click, update active chip, badge, run filter â”€â”€ */
function tlSetHiddenFilter(selectId, val, clickedBtn, chipsContainerId, badgeId, filterIds, filterFn) {
  const sel = document.getElementById(selectId);
  if (sel) { sel.value = val; }
  /* update chip active state */
  const chips = document.getElementById(chipsContainerId);
  if (chips) {
    chips.querySelectorAll('.afp-chip').forEach(c => c.classList.remove('active'));
    clickedBtn.classList.add('active');
  }
  tlUpdateBadge(badgeId, filterIds);
  if (typeof filterFn === 'function') filterFn();
}

/* â”€â”€ Reset all filters in a panel â”€â”€ */
function tlResetPanel(panelId, toggleId, filterIds, badgeId, chipsContainerIds, filterFn) {
  filterIds.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  /* reset all chip groups â€” activate first chip */
  chipsContainerIds.forEach(cid => {
    const chips = document.getElementById(cid);
    if (!chips) return;
    chips.querySelectorAll('.afp-chip').forEach((c, i) => c.classList.toggle('active', i === 0));
  });
  tlUpdateBadge(badgeId, filterIds);
  if (typeof filterFn === 'function') filterFn();
}

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   PAGINATION ENGINE
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
const tlPgState = {
  prog:    { page: 1, size: 10, total: 1, data: [] },
  deanAcc: { page: 1, size: 10, total: 1, data: [] },
  assign:  { page: 1, size: 10, total: 1, data: [] },
  stu:     { page: 1, size: 10, total: 1, data: [] },
};

const tlPgConfig = {
  prog: {
    infoId: 'progPgInfo', pagesId: 'progPgPages',
    firstId: 'progPgFirst', prevId: 'progPgPrev', nextId: 'progPgNext', lastId: 'progPgLast',
    render: function(slice) { _tlProgRenderSlice(slice); }
  },
  deanAcc: {
    infoId: 'deanAccPgInfo', pagesId: 'deanAccPgPages',
    firstId: 'deanAccPgFirst', prevId: 'deanAccPgPrev', nextId: 'deanAccPgNext', lastId: 'deanAccPgLast',
    render: function(slice) { _tlDeanAccRenderSlice(slice); }
  },
  assign: {
    infoId: 'assignPgInfo', pagesId: 'assignPgPages',
    firstId: 'assignPgFirst', prevId: 'assignPgPrev', nextId: 'assignPgNext', lastId: 'assignPgLast',
    render: function(slice) { _tlAssignRenderSlice(slice); }
  },
  stu: {
    infoId: 'stuPgInfo', pagesId: 'stuPgPages',
    firstId: 'stuPgFirst', prevId: 'stuPgPrev', nextId: 'stuPgNext', lastId: 'stuPgLast',
    render: function(slice) { _tlStuRenderSlice(slice); }
  },
};

function tlPgRefresh(key) {
  const st  = tlPgState[key];
  const cfg = tlPgConfig[key];
  if (!st || !cfg) return;

  const total   = st.data.length;
  const pages   = Math.max(1, Math.ceil(total / st.size));
  st.total      = pages;
  st.page       = Math.min(Math.max(1, st.page), pages);

  const start   = (st.page - 1) * st.size;
  const slice   = st.data.slice(start, start + st.size);
  cfg.render(slice);

  /* info text */
  const infoEl = document.getElementById(cfg.infoId);
  if (infoEl) {
    const from = total === 0 ? 0 : start + 1;
    const to   = Math.min(start + st.size, total);
    infoEl.textContent = `Showing ${from}-${to} of ${total}`;
  }

  /* page buttons */
  const pagesEl = document.getElementById(cfg.pagesId);
  if (pagesEl) {
    let html = '';
    const MAX = 5;
    let lo = Math.max(1, st.page - 2), hi = Math.min(pages, lo + MAX - 1);
    lo = Math.max(1, hi - MAX + 1);
    if (lo > 1) html += `<button class="apg-btn" onclick="tlPgGo('${key}',1)">1</button>${lo > 2 ? '<span class="apg-ellipsis">\u2026</span>' : ''}`;
    for (let i = lo; i <= hi; i++) {
      html += `<button class="apg-btn${i === st.page ? ' active' : ''}" onclick="tlPgGo('${key}',${i})">${i}</button>`;
    }
    if (hi < pages) html += `${hi < pages - 1 ? '<span class="apg-ellipsis">\u2026</span>' : ''}<button class="apg-btn" onclick="tlPgGo('${key}',${pages})">${pages}</button>`;
    pagesEl.innerHTML = html;
  }

  /* disable/enable nav buttons */
  const setDis = (id, dis) => { const el = document.getElementById(id); if (el) el.disabled = dis; };
  setDis(cfg.firstId, st.page <= 1);
  setDis(cfg.prevId,  st.page <= 1);
  setDis(cfg.nextId,  st.page >= pages);
  setDis(cfg.lastId,  st.page >= pages);
}

function tlPgGo(key, page) {
  const st = tlPgState[key];
  if (!st) return;
  const clamped = Math.min(Math.max(1, page), st.total);
  if (clamped === st.page) return;
  st.page = clamped;
  tlPgRefresh(key);
}

function tlPgChangeSize(key, size) {
  const st = tlPgState[key];
  if (!st) return;
  st.size = size;
  st.page = 1;
  tlPgRefresh(key);
}

function tlPgLoad(key, fullList) {
  const st = tlPgState[key];
  if (!st) return;
  st.data = fullList;
  st.page = 1;
  tlPgRefresh(key);
}

/* â”€â”€ Slice renderers: call original tbody fillers â”€â”€ */
function _tlProgRenderSlice(slice) {
  /* reuse the existing row-builder logic but only render the page slice */
  /* renderProgTable builds rows for the whole array passed in; pass the slice */
  _tlProgOrigRender(slice);
}
function _tlDeanAccRenderSlice(slice) { _tlDeanAccOrigRender(slice); }
function _tlAssignRenderSlice(slice)  { _tlAssignOrigRender(slice); }
function _tlStuRenderSlice(slice)     { _tlStuOrigRender(slice); }

/* â”€â”€ Hook originals after page load â”€â”€ */
let _tlProgOrigRender    = null;
let _tlDeanAccOrigRender = null;
let _tlAssignOrigRender  = null;
let _tlStuOrigRender     = null;

document.addEventListener('DOMContentLoaded', function() {
  /* Programs */
  if (typeof window.renderProgTable === 'function') {
    _tlProgOrigRender = window.renderProgTable;
    window.renderProgTable = function(list) {
      tlPgLoad('prog', list || []);
    };
  }

  /* Dean Accounts */
  if (typeof window.renderAccTable === 'function') {
    _tlDeanAccOrigRender = window.renderAccTable;
    const _prev = window.renderAccTable;
    window.renderAccTable = function(list) {
      /* keep existing override behaviour (person cards refresh) by calling prev first stub */
      tlPgLoad('deanAcc', list || []);
      /* still refresh person cards if assign modal is open */
      if (document.getElementById('assignModal')?.classList.contains('show')) {
        renderPersonCards(document.getElementById('assignPersonSearch')?.value || '');
      }
    };
  }

  /* Dean Assignments */
  if (typeof window.renderAssignTable === 'function') {
    _tlAssignOrigRender = window.renderAssignTable;
    window.renderAssignTable = function(list) {
      tlPgLoad('assign', list || []);
    };
  }

  /* Students */
  if (typeof window.renderAllStuTable === 'function') {
    _tlStuOrigRender = window.renderAllStuTable;
    window.renderAllStuTable = function(list) {
      tlPgLoad('stu', list || []);
    };
  }
}, { once: true });

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   STUDENTS â€” applyAllStuFilter (adds status filter on top of existing)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function applyAllStuFilter() {
  const q    = $('#allStuSearch').val().trim().toLowerCase();
  const cid  = $('#allStuCourseFilter').val();
  const stat = $('#allStuStatusFilter').val();
  let list   = (typeof allStudentsRaw !== 'undefined') ? allStudentsRaw : [];
  if (cid)  list = list.filter(s => String(s.course_id) === String(cid));
  if (q)    list = list.filter(s => (s.student_number + s.first_name + s.last_name + s.email + (s.username||'')).toLowerCase().includes(q));
  if (stat !== '') list = list.filter(s => String(parseInt(s.is_active) === 1 ? 1 : 0) === stat);
  if (typeof window.renderAllStuTable === 'function') window.renderAllStuTable(list);
}
/* Prevent double-binding the original student search handlers since we now use oninput */
$(document).off('input', '#allStuSearch');
$(document).off('change', '#allStuCourseFilter');
</script>
</body>
</html>
