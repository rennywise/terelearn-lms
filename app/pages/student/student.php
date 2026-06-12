<?php
/**
 * student.php — Tere LEARN | Student Dashboard
 * Matches the exact design system of facultyUI.php & subadmin.php
 */
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php');
    exit;
}
require_once dirname(__DIR__, 2) . '/config/db_connect.php';

$userId = $_SESSION['user_id'];
$uid    = mysqli_real_escape_string($conn, $userId);

// Fetch student record linked to this user
$studentData = null;
$uRes = $conn->query("SELECT email, username FROM tbluser WHERE id='$uid' AND is_deleted=0 LIMIT 1");
$uRow = $uRes ? $uRes->fetch_assoc() : null;
if ($uRow) {
    $un  = mysqli_real_escape_string($conn, $uRow['username']);
    $em  = mysqli_real_escape_string($conn, $uRow['email']);
    $sRes = $conn->query("SELECT s.*, c.course_code, c.course_name
        FROM tblstudent s
        LEFT JOIN tblcourse c ON c.id = s.course_id
        WHERE (s.username='$un' OR s.email='$em') AND s.is_deleted=0 LIMIT 1");
    if ($sRes && $sRes->num_rows > 0) $studentData = $sRes->fetch_assoc();
}
$conn->close();

$fullName = $studentData ? trim(($studentData['first_name'] ?? '') . ' ' . ($studentData['last_name'] ?? '')) : 'Student';
$initials = strtoupper(
    substr($studentData['first_name'] ?? 'S', 0, 1) .
    substr($studentData['last_name']  ?? '', 0, 1)
) ?: 'ST';
$courseCode = htmlspecialchars($studentData['course_code'] ?? '');
$courseName = htmlspecialchars($studentData['course_name'] ?? '');
$yearLevel  = $studentData['year_level'] ?? '';
$section    = htmlspecialchars($studentData['section'] ?? '');
$studentNum = htmlspecialchars($studentData['student_number'] ?? '');
$hName      = htmlspecialchars($fullName);
$hInit      = htmlspecialchars($initials);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TERELEARN — Student Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); transition: background var(--transition), color var(--transition); overflow-x: hidden; }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* â”€â”€ TOP NAV â”€â”€ */
    .topnav { position: fixed; inset: 0 0 auto 0; height: var(--nav-h); background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 1.5rem; gap: .75rem; z-index: 200; transition: background var(--transition), border-color var(--transition); }
    .nav-brand { display: flex; align-items: center; gap: .6rem; font-size: 1.15rem; font-weight: 700; color: var(--text); text-decoration: none; white-space: nowrap; }
    .brand-logo { width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem; box-shadow: 0 2px 8px rgba(26,158,120,.35); }
    .nav-actions { margin-left: auto; display: flex; align-items: center; gap: .4rem; }
    .icon-btn { width: 38px; height: 38px; border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: 1rem; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all var(--transition); position: relative; }
    .icon-btn:hover { background: var(--border); color: var(--text); }
    .notif-dot { position: absolute; top: 6px; right: 6px; width: 8px; height: 8px; background: var(--danger); border-radius: 50%; border: 2px solid var(--surface); }
    @keyframes notifPulse { 0%,100%{transform:scale(1);box-shadow:0 0 0 0 rgba(217,48,37,.55);} 50%{transform:scale(1.5);box-shadow:0 0 0 7px rgba(217,48,37,0);} }
    .notif-dot.pop { animation: notifPulse .5s ease-out 3; }
    .nav-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .85rem; cursor: pointer; border: 2px solid transparent; transition: border-color var(--transition); overflow:hidden; }
    .nav-avatar:hover { border-color: var(--primary); }
    .nav-avatar img { width:100%; height:100%; object-fit:cover; display:block; border-radius:50%; }
    .menu-btn { width: 38px; height: 38px; border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: 1.1rem; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all var(--transition); }
    .menu-btn:hover { background: var(--border); color: var(--text); }
    .nav-student-badge { display: inline-flex; align-items: center; gap: .35rem; background: var(--accent-light); color: var(--accent); border: 1.5px solid rgba(31,115,219,.3); border-radius: 20px; padding: .22rem .75rem; font-size: .7rem; font-weight: 700; white-space: nowrap; }

    /* â”€â”€ SIDEBAR â”€â”€ */
    .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 140; }
    .overlay.show { display: block; }
    .sidebar { position: fixed; top: var(--nav-h); left: 0; bottom: var(--footer-h); width: var(--sidebar-w); background: var(--surface); border-right: 1px solid var(--border); padding: 1rem 0 1rem; overflow-y: auto; overflow-x: hidden; z-index: 150; transition: transform var(--transition), width var(--transition), background var(--transition), border-color var(--transition); }
    .sidebar.collapsed { width: 70px; }
    @media (max-width: 768px) { .sidebar { transform: translateX(-100%); width: var(--sidebar-w) !important; } .sidebar.open { transform: translateX(0); } }
    .sidebar-student { display: flex; align-items: center; gap: .8rem; padding: .85rem 1.2rem 1rem; border-bottom: 1px solid var(--border); margin-bottom: .5rem; overflow: hidden; }
    .s-avatar { width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 1rem; flex-shrink: 0; overflow:hidden; }
    .s-avatar img { width:100%; height:100%; object-fit:cover; display:block; border-radius:50%; }
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

    /* â”€â”€ MAIN â”€â”€ */
    .main { margin-left: var(--sidebar-w); margin-top: var(--nav-h); padding: 2rem 2rem calc(var(--footer-h) + 1.5rem); min-height: calc(100vh - var(--nav-h)); transition: margin-left var(--transition); }
    .main.collapsed { margin-left: 70px; }
    @media (max-width: 768px) { .main { margin-left: 0; padding: 1rem 1rem calc(var(--footer-h) + 1rem); } }

    /* â”€â”€ FOOTER â”€â”€ */
    .student-footer { position: fixed; bottom: 0; left: 0; right: 0; height: var(--footer-h); background: var(--surface); border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; z-index: 190; font-size: .78rem; color: var(--text-muted); transition: background var(--transition), border-color var(--transition); }
    .footer-sem-badge { display: inline-flex; align-items: center; gap: .45rem; background: var(--primary-light); color: var(--primary); border: 1.5px solid var(--primary); border-radius: 8px; padding: .22rem .8rem; font-weight: 700; font-size: .72rem; letter-spacing: .3px; white-space: nowrap; }

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
    .si-purple { background: #ede9fe; color: #7c3aed; }
    .stat-val { font-size: 1.85rem; font-weight: 700; line-height: 1; }
    .stat-lbl { font-size: .73rem; color: var(--text-muted); margin-top: .18rem; }




      /* â”€â”€ CARD ACTION BAR â”€â”€ */
  .card-action-bar {
    display: flex;
    gap: .4rem;
    padding: .55rem 1.1rem .6rem;
    border-top: 1px solid var(--border);
    background: var(--bg);
  }
  .ca-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    padding: .38rem .5rem;
    border-radius: 8px;
    border: 1.5px solid var(--border);
    background: none;
    font-size: .72rem;
    font-weight: 700;
    font-family: inherit;
    color: var(--text-muted);
    cursor: pointer;
    transition: all var(--transition);
    white-space: nowrap;
  }
  .ca-btn i { font-size: .68rem; }
  .ca-edit:hover    { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
  .ca-toggle:hover  { border-color: var(--warning);  color: var(--warning);  background: #fff3e0; }
  .ca-delete:hover  { border-color: var(--danger);   color: var(--danger);   background: #fdecea; }
  .ca-archive:hover { border-color: var(--warning);  color: var(--warning);  background: #fff3e0; }
  .ca-leave:hover   { border-color: var(--danger);   color: var(--danger);   background: #fdecea; }
  body.dark .ca-toggle:hover  { background: rgba(245,124,0,.12); }
  body.dark .ca-delete:hover  { background: rgba(217,48,37,.12); }
  body.dark .ca-archive:hover { background: rgba(245,124,0,.12); }
  body.dark .ca-leave:hover   { background: rgba(217,48,37,.12); }


    /* â”€â”€ SECTION DIVIDER â”€â”€ */
    .section-divider { display: flex; align-items: center; gap: .75rem; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .9px; color: var(--text-muted); margin: 1.75rem 0 1rem; }
    .section-divider .sd-icon { width: 28px; height: 28px; border-radius: 8px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: .8rem; }
    .section-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

    /* â”€â”€ CLASS CARDS â”€â”€ */
    .class-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 1.1rem; }
    .class-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: visible; box-shadow: var(--shadow); cursor: pointer; transition: all var(--transition); display: flex; flex-direction: column; position: relative; }
    .class-card.kebab-open { z-index: 40; }
    .class-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
    .class-tools{display:grid;grid-template-columns:minmax(260px,1fr) auto;gap:.65rem;align-items:center;margin-bottom:1rem;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.65rem .75rem;box-shadow:var(--shadow);}
    .class-tools-left{display:flex;align-items:center;gap:.55rem;min-width:0;}
    .class-tools-right{display:flex;align-items:center;justify-content:flex-end;gap:.45rem;flex-wrap:wrap;}
    .class-search{flex:1;min-width:190px;display:flex;align-items:center;gap:.5rem;background:var(--bg);border:1.5px solid transparent;border-radius:10px;padding:.42rem .7rem;transition:all var(--transition);}
    .class-search:focus-within{background:var(--surface);border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);}
    .class-search i{color:var(--text-muted);font-size:.82rem;}
    .class-search input{border:none;outline:none;background:transparent;color:var(--text);font-size:.86rem;width:100%;}
    .class-search-clear{display:none;width:24px;height:24px;border:none;border-radius:50%;background:var(--border);color:var(--text-muted);cursor:pointer;align-items:center;justify-content:center;font-size:.68rem;transition:all var(--transition);}
    .class-search.has-value .class-search-clear{display:flex;}
    .class-search-clear:hover{background:var(--primary-light);color:var(--primary);}
    .class-result-count{font-size:.72rem;font-weight:700;color:var(--text-muted);white-space:nowrap;}
    .class-filter{display:inline-flex;gap:0;border:1px solid var(--border);border-radius:10px;overflow:hidden;background:var(--bg);}
    .class-filter-btn{border:none;border-right:1px solid var(--border);background:transparent;color:var(--text-muted);height:34px;padding:0 .75rem;font-size:.74rem;font-weight:700;cursor:pointer;transition:all var(--transition);font-family:inherit;white-space:nowrap;}
    .class-filter-btn:last-child{border-right:none;}
    .class-filter-btn:hover{background:var(--primary-light);color:var(--primary);}
    .class-filter-btn.active{background:var(--primary);color:#fff;}
    .class-sort-tools{display:contents;}
    .sort-btn{display:inline-flex;align-items:center;gap:.35rem;height:34px;padding:0 .72rem;border-radius:10px;border:1.5px solid var(--border);background:var(--surface);font-size:.75rem;font-weight:700;font-family:inherit;color:var(--text-muted);cursor:pointer;transition:all var(--transition);white-space:nowrap;}
    .sort-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .sort-btn.active{background:var(--primary);color:#fff;border-color:var(--primary-dark);}
    .sort-menu-wrap{position:relative;}
    .sort-menu{display:none;position:absolute;top:calc(100% + 6px);right:0;min-width:150px;background:var(--surface);border:1.5px solid var(--border);border-radius:10px;box-shadow:var(--shadow-md);z-index:90;overflow:hidden;}
    .sort-menu.open{display:block;}
    .sort-menu button{display:flex;align-items:center;gap:.55rem;width:100%;padding:.55rem .9rem;border:none;background:none;font-size:.82rem;font-weight:600;font-family:inherit;color:var(--text);cursor:pointer;text-align:left;}
    .sort-menu button:hover{background:var(--primary-light);color:var(--primary);}
    .sort-menu button.is-selected{background:var(--primary-light);color:var(--primary);}
    .sort-menu button i{width:14px;font-size:.72rem;color:var(--primary);text-align:center;}
    .view-toggle{display:inline-flex;border:1px solid var(--border);border-radius:10px;overflow:hidden;background:var(--surface);}
    .view-toggle-btn{width:34px;height:34px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:.82rem;display:flex;align-items:center;justify-content:center;transition:all var(--transition);}
    .view-toggle-btn.active{background:var(--primary);color:#fff;}
    .view-toggle-btn:not(.active):hover{background:var(--primary-light);color:var(--primary);}
    .class-table-wrap{border-radius:var(--radius);border:1px solid var(--border);background:var(--surface);overflow:visible;box-shadow:var(--shadow);}
    .tbl-toolbar{display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap;}
    .tbl-count{font-size:.72rem;color:var(--text-muted);flex-shrink:0;}
    .class-table{width:100%;border-collapse:collapse;font-size:.82rem;}
    .class-table thead tr{border-bottom:1px solid var(--border);}
    .class-table thead th{padding:.6rem .9rem;text-align:left;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);white-space:nowrap;cursor:pointer;user-select:none;transition:color var(--transition);}
    .class-table thead th:hover,.class-table thead th.tbl-sorted{color:var(--primary);}
    .class-table thead th .sort-ic{opacity:.35;margin-left:3px;font-size:.6rem;}
    .class-table thead th.tbl-sorted .sort-ic{opacity:1;}
    .class-table tbody tr{border-bottom:1px solid var(--border);cursor:pointer;transition:background var(--transition);}
    .class-table tbody tr:last-child{border-bottom:none;}
    .class-table tbody tr:hover{background:var(--primary-light);}
    .class-table td{padding:.7rem .9rem;vertical-align:middle;}
    .tbl-name-cell{display:flex;align-items:center;gap:.65rem;min-width:210px;}
    .tbl-name-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
    .tbl-name-main{font-weight:700;font-size:.86rem;color:var(--text);}
    .tbl-name-sub{font-size:.72rem;color:var(--text-muted);margin-top:.08rem;}
    .tbl-muted{color:var(--text-muted);font-size:.78rem;}
    .tbl-prof{display:flex;align-items:center;gap:.55rem;min-width:170px;}
    .tbl-prof-avatar{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.68rem;font-weight:700;overflow:hidden;flex-shrink:0;}
    .tbl-prof-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
    .tbl-empty{display:flex;align-items:center;justify-content:center;gap:.45rem;color:var(--text-muted);padding:1.5rem;}
    .tbl-actions{display:flex;justify-content:flex-end;gap:.35rem;}
    .tbl-icon-btn{width:30px;height:30px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text-muted);cursor:pointer;transition:all var(--transition);}
    .tbl-icon-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .tbl-icon-btn.danger:hover{border-color:var(--danger);color:var(--danger);background:#fdecea;}
    .card-banner { height: 90px; padding: 1rem; display: flex; flex-direction: column; justify-content: flex-end; position: relative; overflow: hidden; border-top-left-radius: var(--radius); border-top-right-radius: var(--radius); }
    .card-banner::after { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,.15); }
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
    .card-body { padding: .9rem 1.1rem; flex: 1; display: flex; flex-direction: column; }
    .card-chips { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .75rem; }
    .chip { font-size: .7rem; font-weight: 500; padding: .18rem .55rem; border-radius: 20px; background: var(--bg); color: var(--text-muted); border: 1px solid var(--border); display: flex; align-items: center; gap: .25rem; }
    .chip i { font-size: .62rem; }
    .card-foot { display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border); padding-top: .65rem; margin-top: auto; }
    .class-info { font-size: .78rem; color: var(--text-muted); display: flex; align-items: center; gap: .3rem; }

.card-kebab-wrap{position:absolute;top:.55rem;right:.55rem;z-index:25;}
.card-kebab-btn{width:32px;height:32px;border-radius:50%;border:none;background:rgba(255,255,255,.22);color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.88rem;transition:background .18s;backdrop-filter:blur(4px);touch-action:manipulation;}
.card-kebab-btn:hover{background:rgba(255,255,255,.42);}
.card-kebab-menu{position:absolute;top:calc(100% + 6px);right:0;background:var(--surface);border:1.5px solid var(--border);border-radius:10px;box-shadow:var(--shadow-lg);min-width:160px;z-index:9999;overflow:hidden;pointer-events:auto;}
.ckm-item{display:flex;align-items:center;gap:.5rem;width:100%;padding:.52rem .9rem;border:none;background:none;font-size:.82rem;font-weight:600;font-family:inherit;color:var(--text);cursor:pointer;transition:background .14s;text-align:left;}
.ckm-item i{width:14px;text-align:center;font-size:.72rem;}
.ckm-item.warn:hover{background:#fff3e0;color:var(--warning);}
.ckm-item.danger:hover{background:#fdecea;color:var(--danger);}
.ckm-sep{height:1px;background:var(--border);margin:0 .65rem;}
    /* â”€â”€ MEET BAR â”€â”€ */
    .meet-bar { padding: .55rem 1.1rem .65rem; border-top: 1px solid var(--border); background: var(--bg); display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .meet-btn { display: inline-flex; align-items: center; gap: .45rem; background: #1a73e8; color: #fff; border: none; border-radius: 8px; padding: .38rem .85rem; font-size: .76rem; font-weight: 700; cursor: pointer; font-family: inherit; transition: all var(--transition); text-decoration: none; white-space: nowrap; box-shadow: 0 2px 6px rgba(26,115,232,.35); }
    .meet-btn:hover { background: #1558b0; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(26,115,232,.4); }
    .meet-btn svg { width: 13px; height: 13px; flex-shrink: 0; }
    .meet-code-pill { font-size: .68rem; color: var(--text-muted); background: var(--surface); border: 1px solid var(--border); border-radius: 6px; padding: .18rem .5rem; font-family: 'DM Mono', monospace; letter-spacing: .5px; cursor: pointer; transition: all var(--transition); white-space: nowrap; }
    .meet-code-pill:hover { border-color: #1a73e8; color: #1a73e8; }
    .meet-loading { font-size: .72rem; color: var(--text-muted); display: flex; align-items: center; gap: .35rem; }
    body.dark .meet-bar { background: rgba(0,0,0,.15); }
    body.dark .meet-code-pill { background: var(--bg); }

    /* â”€â”€ EMPTY STATE â”€â”€ */
    .empty-state { padding: 2.5rem 1rem; text-align: center; color: var(--text-muted); grid-column: 1 / -1; }
    .empty-icon { font-size: 2.5rem; margin-bottom: .75rem; opacity: .35; }
    .empty-title { font-size: 1rem; font-weight: 600; margin-bottom: .3rem; }

    /* â”€â”€ PROFILE CARD â”€â”€ */
    .profile-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; box-shadow: var(--shadow-md); overflow: hidden; max-width: 760px; }
    .profile-banner { height: 160px; background: linear-gradient(135deg, #1aa78b 0%, #1f73db 100%); position: relative; }
    .profile-cover-action { position: absolute; right: 12px; bottom: 12px; display: inline-flex; align-items: center; gap: .35rem; padding: .36rem .72rem; border-radius: 10px; border: 1px solid rgba(255,255,255,.65); background: rgba(0,0,0,.28); color: #fff; font-size: .74rem; font-weight: 700; backdrop-filter: blur(4px); }
    .profile-body { padding: 0 1.75rem 1.75rem; }
    .profile-avatar-wrap { position:relative; width:96px; margin-top:-48px; margin-bottom:.7rem; }
    .profile-avatar { width: 96px; height: 96px; border-radius: 50%; border: 4px solid var(--surface); background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.65rem; font-weight: 700; overflow:hidden; box-shadow: 0 6px 18px rgba(0,0,0,.24); }
    .profile-avatar img { width:100%; height:100%; object-fit:cover; display:block; border-radius:50%; }
    .profile-avatar-actions { position:absolute; right:-10px; bottom:2px; display:flex; gap:.35rem; }
    .pa-btn { width:31px; height:31px; border:none; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:.76rem; box-shadow:0 3px 12px rgba(0,0,0,.24); }
    .pa-btn.upload { background:var(--primary); color:#fff; }
    .pa-btn.remove { background:#fff; color:var(--danger); border:1px solid #fecaca; }
    .profile-photo-label { margin-top:.35rem; font-size:.74rem; font-weight:700; color:var(--primary); letter-spacing:.2px; display:inline-flex; align-items:center; gap:.3rem; cursor:pointer; }
    .profile-photo-label:hover { text-decoration:underline; }
    .profile-name { font-size: 1.35rem; font-weight: 700; }
    .profile-sub  { font-size: .85rem; color: var(--text-muted); margin-top: .15rem; }
    .profile-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
    .profile-head-meta { flex: 1; min-width: 0; }
    .profile-stats { display: grid; grid-template-columns: repeat(2,1fr); gap: .75rem; margin-top: 1.25rem; text-align: center; }
    .ps { border-right: 1px solid var(--border); }
    .ps:nth-child(2n) { border-right: none; }
    .ps-val { font-size: 1.55rem; font-weight: 700; color: var(--primary); }
    .ps-lbl { font-size: .72rem; color: var(--text-muted); }
    .profile-info-list { margin-top:1.25rem; display:grid; grid-template-columns:1fr 1fr; gap:.65rem; }
    .pi-card { border:1px solid var(--border); border-radius:12px; padding:.72rem .82rem; background:var(--bg); }
    .pi-label { font-size:.66rem; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:var(--text-muted); margin-bottom:.2rem; }
    .pi-value { font-size:.88rem; font-weight:600; color:var(--text); line-height:1.35; }
    .pi-value.muted { font-size:.82rem; color:var(--text-muted); font-weight:500; }

    /* â”€â”€ ACTIVITY / ANNOUNCEMENTS â”€â”€ */
    .feed-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; margin-bottom: 1rem; }
    .feed-header { display: flex; align-items: center; gap: .75rem; padding: .85rem 1.1rem; border-bottom: 1px solid var(--border); }
    .feed-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
    .feed-meta { flex: 1; }
    .feed-author { font-size: .88rem; font-weight: 600; }
    .feed-time { font-size: .72rem; color: var(--text-muted); }
    .feed-class-badge { font-size: .65rem; font-weight: 700; padding: .15rem .5rem; border-radius: 20px; background: var(--primary-light); color: var(--primary); border: 1px solid rgba(26,158,120,.2); }
    .feed-body { padding: .85rem 1.1rem; font-size: .88rem; line-height: 1.65; }
    .feed-type-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .65rem; font-weight: 700; padding: .18rem .5rem; border-radius: 20px; margin-bottom: .5rem; }
    .feed-type-quiz   { background: #ede9fe; color: #7c3aed; border: 1px solid #ddd6fe; }
    .feed-type-assign { background: var(--accent-light); color: var(--accent); border: 1px solid #bfdbfe; }
    .feed-type-annc   { background: var(--primary-light); color: var(--primary); border: 1px solid rgba(26,158,120,.2); }
    .feed-type-meet   { background: #e8f0fe; color: #1a73e8; border: 1px solid #c5d7f5; }

    /* â”€â”€ SCHEDULE / UPCOMING â”€â”€ */
    .schedule-item { display: flex; align-items: flex-start; gap: .9rem; padding: .85rem 1.1rem; border-bottom: 1px solid var(--border); transition: background var(--transition); }
    .schedule-item:last-child { border-bottom: none; }
    .schedule-item:hover { background: var(--primary-light); }
    .schedule-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: .3rem; }
    .schedule-info { flex: 1; }
    .schedule-title { font-size: .88rem; font-weight: 600; }
    .schedule-sub { font-size: .75rem; color: var(--text-muted); margin-top: .1rem; }
    .schedule-time { font-size: .72rem; font-family: 'DM Mono', monospace; color: var(--text-muted); white-space: nowrap; }

    /* â”€â”€ GRADES TABLE â”€â”€ */
    .grades-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
    .grades-toolbar { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--border); flex-wrap: wrap; }
    .grades-search { flex: 1; min-width: 160px; display: flex; align-items: center; gap: 8px; background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0 10px; height: 32px; }
    .grades-search input { border: none; background: transparent; outline: none; font-size: .8rem; color: var(--text); width: 100%; font-family: inherit; }
    .grades-search i { color: var(--text-muted); font-size: .75rem; flex-shrink: 0; }
    .grades-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
    .grades-table thead tr { border-bottom: 1px solid var(--border); }
    .grades-table thead th { padding: .6rem .9rem; text-align: left; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--text-muted); }
    .grades-table tbody tr { border-bottom: 1px solid var(--border); transition: background var(--transition); }
    .grades-table tbody tr:last-child { border-bottom: none; }
    .grades-table tbody tr:hover { background: var(--primary-light); }
    .grades-table td { padding: .7rem .9rem; vertical-align: middle; }
    .grade-pill { display: inline-flex; align-items: center; font-size: .75rem; font-weight: 700; padding: .2rem .6rem; border-radius: 20px; }
    .gp-pass { background: var(--primary-light); color: var(--primary); border: 1px solid rgba(26,158,120,.2); }
    .gp-fail { background: #fdecea; color: var(--danger); border: 1px solid #fecaca; }
    .gp-pending { background: #fff3e0; color: var(--warning); border: 1px solid #ffe0b2; }
    .gp-na { background: var(--bg); color: var(--text-muted); border: 1px solid var(--border); }

    /* â”€â”€ SKELETON â”€â”€ */
    .skeleton { background: linear-gradient(90deg, var(--border) 25%, var(--bg) 50%, var(--border) 75%); background-size: 200% 100%; animation: shimmer 1.3s infinite; border-radius: 8px; }
    @keyframes shimmer { to { background-position: -200% 0; } }
    .sk-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; height: 190px; }
    .spin { display: inline-block; width: 13px; height: 13px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: sp .7s linear infinite; }
    @keyframes sp { to { transform: rotate(360deg); } }

    /* â”€â”€ NOTIFICATION PANEL â”€â”€ */
    .notif-panel { position: fixed; top: calc(var(--nav-h) + 8px); right: 1rem; width: 320px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-lg); z-index: 300; display: none; overflow: hidden; }
    .notif-panel.show { display: block; animation: fadeUp .18s ease; }
    .notif-panel-header { padding: .75rem 1rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .notif-panel-title { font-weight: 700; font-size: .88rem; }
    .notif-item { display: flex; align-items: flex-start; gap: .65rem; padding: .75rem 1rem; border-bottom: 1px solid var(--border); transition: background var(--transition); cursor: pointer; }
    .notif-item:last-child { border-bottom: none; }
    .notif-item:hover { background: var(--primary-light); }
    .notif-item.unread { background: rgba(26,158,120,.04); }
    .notif-dot2 { width: 8px; height: 8px; border-radius: 50%; background: var(--primary); flex-shrink: 0; margin-top: .35rem; }
    .notif-dot2.read { background: transparent; border: 1.5px solid var(--border); }
    .notif-text { font-size: .8rem; line-height: 1.5; }
    .notif-ts { font-size: .68rem; color: var(--text-muted); margin-top: .15rem; }

    /* â”€â”€ BTN â”€â”€ */
    .btn { padding: .5rem 1.3rem; border-radius: var(--radius-sm); font-size: .86rem; font-weight: 600; font-family: inherit; cursor: pointer; border: none; transition: all var(--transition); display: inline-flex; align-items: center; gap: .4rem; }
    .btn-primary { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; box-shadow: 0 2px 10px rgba(26,158,120,.3); }
    .btn-primary:hover { opacity: .9; transform: translateY(-1px); }
    .btn-ghost { background: var(--bg); color: var(--text); border: 1.5px solid var(--border); }
    .btn-ghost:hover { border-color: var(--primary); color: var(--primary); }

    /* â”€â”€ ATTENDANCE BAR â”€â”€ */
    .attend-bar-wrap { margin: .5rem 0 .25rem; }
    .attend-bar-bg { height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; }
    .attend-bar-fill { height: 100%; border-radius: 3px; transition: width .6s ease; }
    .attend-pct { font-size: .7rem; font-weight: 700; margin-top: .2rem; }

    @media (max-width: 900px) { .stats-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 768px) {
      :root { --nav-h: 56px; }
      .topnav { padding: 0 1rem; }
      .stats-grid { grid-template-columns: 1fr 1fr; gap: .65rem; }
      .class-grid { grid-template-columns: 1fr; }
      .class-tools{grid-template-columns:1fr;padding:.6rem;}
      .class-tools-left,.class-tools-right{width:100%;}
      .class-tools-left{flex-wrap:wrap;}
      .class-tools-right{justify-content:flex-start;}
      .class-search{min-width:100%;}
      .class-filter{max-width:100%;overflow-x:auto;}
      .profile-stats { grid-template-columns: 1fr; }
      .ps { border-right: none; border-bottom: 1px solid var(--border); padding-bottom: .6rem; }
      .ps:last-child { border-bottom: none; }
      .profile-banner { height: 132px; }
      .profile-avatar-wrap { width:84px; margin-top:-42px; }
      .profile-avatar { width:84px; height:84px; font-size:1.45rem; }
      .profile-body { padding: 0 1rem 1.15rem; }
      .profile-head { flex-direction: column; align-items: center; text-align: center; gap: .55rem; }
      .profile-head-meta { width: 100%; }
      .profile-photo-label { justify-content: center; }
      .profile-info-list { grid-template-columns: 1fr; }
      .student-footer { padding: 0 1rem; }
      .notif-panel { width: calc(100vw - 2rem); right: 1rem; }
    }
  
    /* â”€â”€ CALENDAR â”€â”€ */
    .cal-toolbar { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; margin-bottom:1rem; }
    .cal-nav-btn { width:34px; height:34px; border-radius:10px; border:1.5px solid var(--border); background:var(--surface); color:var(--text-muted); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:all var(--transition); }
    .cal-nav-btn:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-light); }
    .cal-month-label { font-size:1.2rem; font-weight:700; min-width:200px; }
    .cal-today-btn { display:inline-flex; align-items:center; gap:.35rem; padding:.4rem .85rem; border-radius:20px; border:1.5px solid var(--border); background:var(--surface); font-size:.78rem; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all var(--transition); font-family:inherit; }
    .cal-today-btn:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-light); }
    .cal-legend { display:flex; gap:.65rem; flex-wrap:wrap; margin-left:auto; }
    .cal-legend-item { display:inline-flex; align-items:center; gap:.35rem; font-size:.72rem; color:var(--text-muted); font-weight:600; }
    .cal-legend-dot { width:10px; height:10px; border-radius:50%; }
    .cal-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; }
    .cal-weekdays { display:grid; grid-template-columns:repeat(7,1fr); background:var(--bg); border-bottom:1px solid var(--border); }
    .cal-weekday { text-align:center; padding:.6rem .3rem; font-size:.7rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.8px; }
    .cal-grid { display:grid; grid-template-columns:repeat(7,1fr); }
    .cal-day { min-height:108px; border-right:1px solid var(--border); border-bottom:1px solid var(--border); padding:.45rem .5rem; display:flex; flex-direction:column; gap:.3rem; cursor:pointer; transition:background var(--transition); position:relative; }
    .cal-day:nth-child(7n) { border-right:none; }
    .cal-day:hover { background:var(--primary-light); }
    .cal-day.other-month { background:#fafbfc; opacity:.55; }
    body.dark .cal-day.other-month { background:rgba(255,255,255,.02); }
    .cal-day.today .cal-day-num { background:var(--primary); color:#fff; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; }
    .cal-day.selected { background:var(--accent-light); box-shadow:inset 0 0 0 2px var(--accent); }
    .cal-day-num { font-size:.82rem; font-weight:700; color:var(--text); }
    .cal-events { display:flex; flex-direction:column; gap:2px; overflow:hidden; }
    .cal-event-pill { font-size:.65rem; font-weight:600; padding:2px 6px; border-radius:5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; border-left:3px solid; }
    .cal-event-pill.t-quiz       { background:#fdecea; color:#c0392b; border-color:#d93025; }
    .cal-event-pill.t-activity   { background:#e8f0fe; color:#1f73db; border-color:#1f73db; }
    .cal-event-pill.t-exam       { background:#f3e8ff; color:#7b1fa2; border-color:#7b1fa2; }
    .cal-event-pill.t-assignment { background:#fff3cd; color:#b45309; border-color:#f59e0b; }
    .cal-event-pill.t-other      { background:#e6f7f2; color:#0d7a5e; border-color:#1a9e78; }
    body.dark .cal-event-pill { background:rgba(255,255,255,.06); }
    .cal-event-more { font-size:.62rem; color:var(--text-muted); font-weight:700; padding:1px 4px; }
    .cal-day-detail { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:1rem 1.2rem; margin-top:1rem; box-shadow:var(--shadow); }
    .cal-detail-title { font-size:1rem; font-weight:700; margin-bottom:.65rem; display:flex; align-items:center; gap:.5rem; }
    .cal-detail-empty { color:var(--text-muted); font-size:.85rem; padding:.5rem 0; }
    .cal-detail-item { display:flex; align-items:flex-start; gap:.7rem; padding:.6rem .75rem; border:1px solid var(--border); border-radius:var(--radius-sm); margin-bottom:.4rem; transition:all var(--transition); }
    .cal-detail-item:hover { border-color:var(--primary); box-shadow:var(--shadow); }
    .cal-detail-icon { width:34px; height:34px; border-radius:9px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:.85rem; }
    .cal-detail-meta { flex:1; min-width:0; }
    .cal-detail-name { font-weight:600; font-size:.88rem; }
    .cal-detail-sub { font-size:.72rem; color:var(--text-muted); margin-top:.15rem; }
    .cal-detail-time { font-family:'DM Mono',monospace; font-size:.72rem; font-weight:600; color:var(--text-muted); padding:.2rem .5rem; border:1px solid var(--border); border-radius:6px; flex-shrink:0; }
    .cal-detail-countdown { display:inline-block; font-size:.65rem; font-weight:700; padding:.1rem .45rem; border-radius:20px; margin-left:.3rem; }
    .cd-overdue { background:#fdecea; color:#c0392b; }
    .cd-today   { background:#fff3cd; color:#b45309; }
    .cd-soon    { background:#e8f0fe; color:#1f73db; }
    @media (max-width:768px) { .cal-day { min-height:72px; padding:.3rem; } .cal-event-pill { font-size:.58rem; padding:1px 4px; } }
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
    <button class="icon-btn" id="darkToggle" title="Dark mode"><i class="fas fa-moon"></i></button>
    <button class="icon-btn" id="notifBtn" title="Notifications">
      <i class="fas fa-bell"></i>
      <span class="notif-dot" id="notifDot" style="display:none;"></span>
    </button>
    <div class="nav-avatar" id="navAvatar" title="Profile"><?= $hInit ?></div>
  </div>
</nav>

<!-- NOTIFICATION PANEL -->
<div class="notif-panel" id="notifPanel">
  <div class="notif-panel-header">
    <span class="notif-panel-title">Notifications</span>
    <button style="font-size:.72rem;font-weight:600;color:var(--primary);background:none;border:none;cursor:pointer;" onclick="markAllRead()">Mark all read</button>
  </div>
  <div id="notifList">
    <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.82rem;">
      <i class="fas fa-spinner fa-spin" style="margin-right:.4rem;"></i>Loading…
    </div>
  </div>
</div>

<div class="overlay" id="overlay"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-student">
    <div class="s-avatar" id="sidebarAvatar"><?= $hInit ?></div>
    <div>
      <div class="s-name" id="sidebarName"><?= $hName ?></div>
      <div class="s-role">Student</div>
    </div>
  </div>

  <div class="nav-section-label">Main</div>
  <button class="nav-item active" data-page="dashboard"><i class="fas fa-th-large"></i><span>Dashboard</span></button>
  <button class="nav-item" data-page="classes"><i class="fas fa-chalkboard"></i><span>My Classes</span><span class="nav-badge" id="classBadge">0</span></button>
  <button class="nav-item" data-page="activity"><i class="fas fa-stream"></i><span>Activity Feed</span><span class="nav-badge" id="activityBadge" style="background:#7c3aed;">0</span></button>
  
  <div class="nav-section-label" style="margin-top:.75rem;">Academics</div>
  <button class="nav-item" data-page="grades"><i class="fas fa-chart-bar"></i><span>Grades</span></button>
  <button class="nav-item" data-page="schedule"><i class="fas fa-calendar-week"></i><span>Schedule</span></button>
  <button class="nav-item" data-page="calendar"><i class="fas fa-calendar-day"></i><span>Calendar</span></button>
  <button class="nav-item" data-page="quizzes"><i class="fas fa-tasks"></i><span>Quizzes &amp; Tasks</span><span class="nav-badge" id="quizBadge" style="background:var(--warning);">0</span></button>

  <div class="nav-section-label" style="margin-top:.75rem;">Account</div>
  <button class="nav-item" data-page="profile"><i class="fas fa-user-circle"></i><span>Profile</span></button>
  <button class="nav-item" data-page="settings"><i class="fas fa-sliders-h"></i><span>Settings</span></button>

  <div class="sidebar-footer-inner">
    <a href="signin.php" class="signout-btn"><i class="fas fa-sign-out-alt"></i><span>Sign Out</span></a>
  </div>
</aside>

<!-- MAIN -->
<main class="main" id="main">

  <!-- â”€â”€ DASHBOARD â”€â”€ -->
  <div class="page active" data-page="dashboard">
    <div class="page-header">
      <div>
        <div class="page-title" id="welcomeTitle">Welcome back! 👋</div>
        <div class="page-subtitle">Here's what's happening in your classes</div>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-chalkboard"></i></div><div><div class="stat-val" id="statClasses">—</div><div class="stat-lbl">Enrolled Classes</div></div></div>
      <div class="stat-card"><div class="stat-icon si-blue"><i class="fas fa-tasks"></i></div><div><div class="stat-val" id="statPending">—</div><div class="stat-lbl">Pending Tasks</div></div></div>
      <div class="stat-card"><div class="stat-icon si-orange"><i class="fas fa-check-circle"></i></div><div><div class="stat-val" id="statSubmitted">—</div><div class="stat-lbl">Submitted</div></div></div>
      <div class="stat-card"><div class="stat-icon si-purple"><i class="fas fa-star"></i></div><div><div class="stat-val" id="statAvgGrade">—</div><div class="stat-lbl">Avg Grade</div></div></div>
    </div>
    <!-- JOIN CLASS BAR -->
<div style="display:flex;gap:.6rem;margin-bottom:1.5rem;">
  <div style="flex:1;display:flex;align-items:center;gap:.6rem;background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:.45rem .9rem;box-shadow:var(--shadow);">
    <i class="fas fa-chalkboard" style="color:var(--primary);font-size:.88rem;flex-shrink:0;"></i>
    <input type="text" id="joinCodeInput" placeholder="Enter class code to join…"
      style="flex:1;border:none;background:transparent;font-size:.88rem;font-family:inherit;color:var(--text);outline:none;"
      onkeydown="if(event.key==='Enter')submitJoinCode()">
  </div>
  <button onclick="submitJoinCode()" class="btn btn-primary" style="padding:.45rem 1.1rem;font-size:.84rem;">
    <i class="fas fa-arrow-right"></i> Join
  </button>
</div>

    <div class="section-divider">
      <div class="sd-icon"><i class="fas fa-chalkboard"></i></div>
      Current Semester Classes
    </div>
    <div class="class-grid" id="dashClassGrid">
      <div class="sk-card skeleton"></div>
      <div class="sk-card skeleton"></div>
      <div class="sk-card skeleton"></div>
    </div>

    <!-- Upcoming -->
    <div class="section-divider" style="margin-top:2rem;">
      <div class="sd-icon"><i class="fas fa-clock"></i></div>
      Upcoming This Week
    </div>
    <div class="profile-card" style="max-width:100%;" id="upcomingCard">
      <div id="upcomingList">
        <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem;">
          <i class="fas fa-spinner fa-spin" style="margin-right:.4rem;"></i>Loading…
        </div>
      </div>
    </div>
  </div>

  <!-- â”€â”€ MY CLASSES â”€â”€ -->
  <div class="page" data-page="classes">
    <div class="page-header">
      <div>
        <div class="page-title">My Classes</div>
        <div class="page-subtitle">All your enrolled classes this semester</div>
      </div>
    </div>
    <div class="class-tools" aria-label="Class tools">
      <div class="class-tools-left">
        <div class="class-search">
          <i class="fas fa-search"></i>
          <input type="text" id="myClassSearch" placeholder="Search classes...">
          <button type="button" class="class-search-clear" id="myClassSearchClear" title="Clear search"><i class="fas fa-times"></i></button>
        </div>
        <span class="class-result-count" id="myClassResultCount">0 classes</span>
      </div>
      <div class="class-tools-right">
        <div class="class-filter" id="myClassFilter" aria-label="Meet filter">
          <button type="button" class="class-filter-btn active" data-filter="all"><i class="fas fa-border-all"></i> All</button>
          <button type="button" class="class-filter-btn" data-filter="with_meet"><i class="fas fa-video"></i> Meet</button>
          <button type="button" class="class-filter-btn" data-filter="no_meet"><i class="fas fa-video-slash"></i> No Meet</button>
        </div>
        <button type="button" class="sort-btn active" id="studentSortDirBtn" onclick="toggleStudentSortDir()" title="Reverse order">
          <i class="fas fa-sort-alpha-down"></i> A-Z
        </button>
        <div class="sort-menu-wrap">
          <button type="button" class="sort-btn active" id="studentSortFieldBtn" onclick="toggleStudentSortMenu(event)" title="Sort classes by">
            <i class="fas fa-filter"></i> Course <i class="fas fa-chevron-down" style="font-size:.6rem;"></i>
          </button>
          <div class="sort-menu" id="studentSortMenu">
            <button type="button" class="is-selected" data-sort-field="course" onclick="setStudentSortField('course')"><i class="fas fa-book-open"></i> Course Code</button>
            <button type="button" data-sort-field="section" onclick="setStudentSortField('section')"><i class="fas fa-layer-group"></i> Section</button>
            <button type="button" data-sort-field="professor" onclick="setStudentSortField('professor')"><i class="fas fa-chalkboard-teacher"></i> Professor</button>
          </div>
        </div>
        <div class="sort-menu-wrap">
          <button type="button" class="sort-btn" id="studentSectionBtn" onclick="toggleStudentSectionMenu(event)" title="Filter by section">
            <i class="fas fa-layer-group"></i> All Sections <i class="fas fa-chevron-down" style="font-size:.6rem;"></i>
          </button>
          <div class="sort-menu" id="studentSectionMenu"></div>
        </div>
        <div class="view-toggle" title="Toggle view">
          <button type="button" class="view-toggle-btn active" id="studentViewCards" onclick="setStudentClassView('cards')" title="Card view"><i class="fas fa-th-large"></i></button>
          <button type="button" class="view-toggle-btn" id="studentViewTable" onclick="setStudentClassView('table')" title="Table view"><i class="fas fa-table"></i></button>
        </div>
      </div>
    </div>
    <div class="class-grid" id="myClassGrid">
      <div class="sk-card skeleton"></div>
      <div class="sk-card skeleton"></div>
    </div>
    <div class="class-table-wrap" id="myClassTable" style="display:none;"></div>
  </div>

  <!-- â”€â”€ ACTIVITY FEED â”€â”€ -->
  <div class="page" data-page="activity">
    <div class="page-header">
      <div>
        <div class="page-title">Activity Feed</div>
        <div class="page-subtitle">Announcements, quizzes, and updates from your professors</div>
      </div>
    </div>
    <div id="activityFeed">
      <div style="text-align:center;padding:3rem;color:var(--text-muted);">
        <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;margin-bottom:.75rem;display:block;"></i>Loading feed…
      </div>
    </div>
  </div>

  <!-- â”€â”€ GRADES â”€â”€ -->
  <div class="page" data-page="grades">
    <div class="page-header">
      <div>
        <div class="page-title">Grades</div>
        <div class="page-subtitle">Your academic performance across all classes</div>
      </div>
    </div>
    <div class="grades-wrap">
      <div class="grades-toolbar">
        <div class="grades-search">
          <i class="fas fa-search"></i>
          <input type="text" id="gradesSearch" placeholder="Search by subject…" autocomplete="off">
        </div>
        <span style="font-size:.72rem;color:var(--text-muted);" id="gradesCnt"></span>
      </div>
      <div style="overflow-x:auto;">
        <table class="grades-table" id="gradesTable">
          <thead><tr>
            <th>Subject</th>
            <th>Professor</th>
            <th>Semester</th>
            <th>Quizzes</th>
            <th>Assignments</th>
            <th>Participation</th>
            <th>Final Grade</th>
            <th>Status</th>
          </tr></thead>
          <tbody><tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">
            <i class="fas fa-spinner fa-spin" style="margin-right:.4rem;"></i>Loading grades…
          </td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- â”€â”€ SCHEDULE â”€â”€ -->
  <div class="page" data-page="schedule">
    <div class="page-header">
      <div>
        <div class="page-title">Schedule</div>
        <div class="page-subtitle">Your weekly class timetable</div>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;" id="scheduleGrid">
      <div style="text-align:center;padding:3rem;color:var(--text-muted);grid-column:1/-1;">
        <i class="fas fa-spinner fa-spin" style="margin-right:.4rem;"></i>Loading schedule…
      </div>
    </div>
  </div>


  <!-- â”€â”€ CALENDAR â”€â”€ -->
  <div class="page" data-page="calendar">
    <div class="page-header">
      <div>
        <div class="page-title"><i class="fas fa-calendar-day me-2" style="color:var(--primary);"></i>Calendar</div>
        <div class="page-subtitle">See when your quizzes, activities and exams are due — start early!</div>
      </div>
    </div>

    <div class="cal-toolbar">
      <button class="cal-nav-btn" onclick="calPrevMonth()" title="Previous month"><i class="fas fa-chevron-left"></i></button>
      <div class="cal-month-label" id="calMonthLabel">—</div>
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
          <i class="fas fa-spinner fa-spin me-2"></i>Loading…
        </div>
      </div>
    </div>

    <div class="cal-day-detail" id="calDayDetail" style="display:none;">
      <div class="cal-detail-title"><i class="fas fa-list" style="color:var(--primary);"></i> <span id="calDayDetailTitle">Selected day</span></div>
      <div id="calDayDetailList"></div>
    </div>
  </div>

  <!-- â”€â”€ QUIZZES & TASKS â”€â”€ -->
  <div class="page" data-page="quizzes">
    <div class="page-header">
      <div>
        <div class="page-title">Quizzes &amp; Tasks</div>
        <div class="page-subtitle">Track your pending and completed work</div>
      </div>
    </div>

    <!-- Pending -->
    <div class="section-divider">
      <div class="sd-icon"><i class="fas fa-clock"></i></div>
      Pending
    </div>
    <div id="pendingTasks">
      <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem;">
        <i class="fas fa-spinner fa-spin" style="margin-right:.4rem;"></i>Loading…
      </div>
    </div>

    <!-- Completed -->
    <div class="section-divider" style="margin-top:2rem;">
      <div class="sd-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-check"></i></div>
      Completed
    </div>
    <div id="completedTasks">
      <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem;">Loading…</div>
    </div>
  </div>

  <!-- â”€â”€ PROFILE â”€â”€ -->
  <div class="page" data-page="profile">
    <div class="page-header"><div><div class="page-title">Profile</div><div class="page-subtitle">Your student information</div></div></div>
    <div class="profile-card">
      <div class="profile-banner">
        <div class="profile-cover-action"><i class="fas fa-camera"></i>Cover</div>
      </div>
      <div class="profile-body">
        <div class="profile-head">
          <div class="profile-avatar-wrap">
            <div class="profile-avatar" id="profileAvatar"><?= $hInit ?></div>
            <div class="profile-avatar-actions">
              <button type="button" class="pa-btn upload" title="Upload photo" onclick="openStudentPicPicker()"><i class="fas fa-camera"></i></button>
              <button type="button" class="pa-btn remove" title="Remove photo" onclick="removeStudentProfilePicture()"><i class="fas fa-trash"></i></button>
            </div>
            <input type="file" id="studentPicInput" accept="image/png,image/jpeg,image/webp,image/gif" style="display:none;" onchange="uploadStudentProfilePicture(this)">
          </div>
          <div class="profile-head-meta">
            <div class="profile-photo-label" onclick="openStudentPicPicker()">Change Profile Picture</div>
            <div class="profile-name" id="profileName"><?= $hName ?></div>
        <div class="profile-sub"  id="profileStudentId">—</div>
            <div style="margin-top:.5rem;display:flex;flex-wrap:wrap;gap:.4rem;" id="profileBadges">
              <?php if ($courseCode): ?>
              <span style="display:inline-flex;align-items:center;gap:.3rem;background:var(--primary-light);color:var(--primary);border:1.5px solid rgba(26,158,120,.3);border-radius:20px;padding:.22rem .75rem;font-size:.72rem;font-weight:700;">
                <i class="fas fa-graduation-cap" style="font-size:.6rem;"></i> <?= $courseCode ?>
              </span>
              <?php endif; ?>
              <?php if ($yearLevel): ?>
              <span style="display:inline-flex;align-items:center;gap:.3rem;background:var(--accent-light);color:var(--accent);border:1.5px solid rgba(31,115,219,.3);border-radius:20px;padding:.22rem .75rem;font-size:.72rem;font-weight:700;">
                Year <?= $yearLevel ?>
              </span>
              <?php endif; ?>
              <?php if ($section): ?>
              <span style="display:inline-flex;align-items:center;gap:.3rem;background:#ede9fe;color:#7c3aed;border:1.5px solid #ddd6fe;border-radius:20px;padding:.22rem .75rem;font-size:.72rem;font-weight:700;">
                Section <?= $section ?>
              </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="profile-stats" style="margin-top:1.25rem;">
          <div class="ps"><div class="ps-val" id="psClasses">—</div><div class="ps-lbl">Classes</div></div>
          <div class="ps"><div class="ps-val" id="psGrade">—</div><div class="ps-lbl">Avg Grade</div></div>
        </div>

        <!-- Info rows -->
        <div class="profile-info-list">
          <div class="pi-card">
            <div class="pi-label">Student Number</div>
            <div class="pi-value" id="piStuNo"><?= $studentNum ?></div>
          </div>
          <div class="pi-card">
            <div class="pi-label">Program</div>
            <div class="pi-value" id="piCourse"><?= $courseCode ?> — <?= $courseName ?></div>
          </div>
          <div class="pi-card">
            <div class="pi-label">Email</div>
            <div class="pi-value muted" id="piEmail">—</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- â”€â”€ SETTINGS â”€â”€ -->
  <div class="page" data-page="settings">
    <div class="page-header"><div><div class="page-title">Settings</div><div class="page-subtitle">Manage your account preferences</div></div></div>
    <div class="profile-card" style="max-width:480px;">
      <div style="padding:1.1rem 1.6rem;display:flex;flex-direction:column;">
        <div style="padding:1rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
          <div><div style="font-weight:600;font-size:.9rem;"><i class="fas fa-moon" style="margin-right:.5rem;color:var(--text-muted);"></i>Dark Mode</div><div style="font-size:.8rem;color:var(--text-muted);margin-top:.15rem;">Toggle light / dark theme</div></div>
          <button class="icon-btn" id="settingsDark"><i class="fas fa-moon"></i></button>
        </div>
        <div style="padding:1rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
          <div><div style="font-weight:600;font-size:.9rem;"><i class="fas fa-bell" style="margin-right:.5rem;color:var(--text-muted);"></i>Notifications</div><div style="font-size:.8rem;color:var(--text-muted);margin-top:.15rem;">Activity feed alerts</div></div>
          <label style="position:relative;width:38px;height:21px;cursor:pointer;">
            <input type="checkbox" id="notifToggle" checked style="position:absolute;opacity:0;width:0;">
            <span id="notifSlider" style="position:absolute;inset:0;background:var(--primary);border-radius:21px;transition:background .22s;"></span>
            <span id="notifKnob" style="position:absolute;width:15px;height:15px;background:#fff;border-radius:50%;top:3px;left:3px;transition:transform .22s;box-shadow:0 1px 4px rgba(0,0,0,.25);transform:translateX(17px);"></span>
          </label>
        </div>
        <div style="padding:1rem 0;display:flex;align-items:center;justify-content:space-between;">
          <div><div style="font-weight:600;font-size:.9rem;"><i class="fas fa-key" style="margin-right:.5rem;color:var(--text-muted);"></i>Change Password</div><div style="font-size:.8rem;color:var(--text-muted);margin-top:.15rem;">Update your account password</div></div>
          <i class="fas fa-chevron-right" style="color:var(--text-muted);font-size:.8rem;"></i>
        </div>
      </div>
    </div>
  </div>

</main>

<!-- FOOTER -->
<footer class="student-footer">
  <span class="p-2">Copyright &copy; 2025-2026 <strong style="margin-left:.3rem;">TERELEARN</strong></span>
  <span class="footer-sem-badge" id="footerSemBadge">
    <i class="fas fa-calendar-alt"></i>
    <span id="footerSemText">Loading…</span>
  </span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>

  async function submitJoinCode() {
    const input = document.getElementById('joinCodeInput');
    const code  = input.value.trim().toUpperCase();
    if (!code) { toast('Enter a class code.', 'error'); return; }
    input.disabled = true;
    try {
        const res  = await fetch('API/student/request_join_class.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ join_code: code })
        });
        const data = await res.json();
        if (data.status === 'success') {
            toast(data.message, 'success');
            input.value = '';
        } else {
            toast(data.message, 'error');
        }
    } catch { toast('Network error.', 'error'); }
    finally { input.disabled = false; }
}

/* â•â• STATE â•â• */
let studentInfo    = null;
let enrolledClasses = [];
let myClassSearchQuery = '';
let myClassFilterType = 'all';
let myClassSectionFilter = 'all';
let myClassSortField = 'course';
let myClassSortDir = 'az';
let myClassView = 'cards';
let myClassTableSort = { col: null, dir: 1 };
let activityItems  = [];
let gradesData     = [];
let scheduleDays   = {};
let pendingItems   = [];
let completedItems = [];
let notifications  = [];
let sidebarCollapsed = false;
const PALETTES = ['b-forest','b-ocean','b-sunset','b-plum','b-teal','b-rose','b-slate','b-indigo'];
const PALETTE_COLORS = {
  'b-forest':'#1a9e78',
  'b-ocean':'#1f73db',
  'b-sunset':'#f57c00',
  'b-plum':'#7b1fa2',
  'b-teal':'#00838f',
  'b-rose':'#c62828',
  'b-slate':'#455a64',
  'b-indigo':'#3949ab'
};

/* â•â• TOAST â•â• */
function toast(msg, type = 'success') {
  const s = String(msg || '').toLowerCase();
  let title = 'Success.';
  if (type === 'error') {
    title = s.includes('network') ? 'Action failed. Check connection.' : 'Action failed.';
  } else if (type === 'info') {
    title = 'Update received.';
  } else if (s.includes('post')) {
    title = 'Successfully posted!';
  } else if (s.includes('create') || s.includes('created')) {
    title = 'Successfully created!';
  } else if (s.includes('save') || s.includes('saved') || s.includes('update') || s.includes('updated')) {
    title = 'Successfully saved!';
  } else if (s.includes('delete') || s.includes('removed')) {
    title = 'Successfully removed!';
  } else if (s.includes('upload')) {
    title = 'Successfully uploaded!';
  }
  Swal.fire({
    toast: true,
    position: 'bottom-end',
    icon: type,
    title,
    showConfirmButton: false,
    timer: 2200,
    timerProgressBar: true,
    showClass: { popup: 'animate__animated animate__fadeInUp' },
    hideClass: { popup: 'animate__animated animate__fadeOutDown' },
    didOpen: (el) => {
      el.style.cursor = 'pointer';
      el.addEventListener('click', () => Swal.close());
      el.addEventListener('mouseenter', Swal.stopTimer);
      el.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
}

/* â•â• HELPERS â•â• */
function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmt12(t) { if (!t) return ''; const [h, m] = t.split(':').map(Number); return `${h%12||12}:${String(m).padStart(2,'0')} ${h>=12?'PM':'AM'}`; }
function paletteFor(str) {
  let h = 0;
  for (const c of String(str||'').toLowerCase()) h = ((h<<5)-h)+c.charCodeAt(0);
  return PALETTES[Math.abs(h) % PALETTES.length];
}
function getClassMeet(cls) {
  const cached = meetCache[String(cls.id || '')] || {};
  return {
    meeting_code: cls.meeting_code || cached.meeting_code || '',
    meet_url: cls.google_meet_link || cls.meet_url || cached.meet_url || ''
  };
}
function hasClassMeet(cls) {
  const meet = getClassMeet(cls);
  return !!(meet.meeting_code || meet.meet_url);
}
function getFacultyName(cls) {
  const first = cls.faculty_first_name || '';
  const last = cls.faculty_last_name || '';
  return (first || last) ? `${first} ${last}`.trim() : (cls.faculty_name || '');
}
function classSearchText(cls) {
  return [
    cls.subject_name, cls.subject_code, cls.class_code, cls.class_semester,
    cls.class_days, cls.section, getFacultyName(cls)
  ].filter(Boolean).join(' ').toLowerCase();
}
function relTime(ts) {
  if (!ts) return '';
  const d = Math.floor((Date.now() - new Date(ts)) / 1000);
  if (d < 60)    return 'Just now';
  if (d < 3600)  return Math.floor(d/60) + 'm ago';
  if (d < 86400) return Math.floor(d/3600) + 'h ago';
  return Math.floor(d/86400) + 'd ago';
}
const YL_LABELS = {1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year'};

/* â”€â”€ SMART NOTIFICATION POLL â”€â”€ */
let _notifReady = false, _lastNotifCount = 0;
const _waitStartNotified = new Set();
async function pollNotifications() {
  const before = _lastNotifCount;
  await loadNotifications();
  const after = document.querySelectorAll('#notifList .notif-item.unread').length;
  _lastNotifCount = after;
  if (_notifReady && after > before) {
    const n = after - before;
    toast(n === 1 ? 'You have a new notification!' : `You have ${n} new notifications!`, 'info');
    const dot = document.getElementById('notifDot');
    if (dot) { dot.classList.remove('pop'); void dot.offsetWidth; dot.classList.add('pop'); }
  }
  _notifReady = true;
}

async function watchStartedFromWaitingOnDashboard() {
  try {
    const keys = [];
    for (let i = 0; i < sessionStorage.length; i++) {
      const k = sessionStorage.key(i) || '';
      if (k.startsWith('quiz_waiting_left_') && sessionStorage.getItem(k) === '1') keys.push(k);
    }
    for (const key of keys) {
      const postId = key.replace('quiz_waiting_left_', '');
      if (!postId || _waitStartNotified.has(postId)) continue;
      const fd = new FormData();
      fd.append('post_id', postId);
      const res = await fetch('API/student/studentClassroom/quiz/get_my_quiz_state.php', {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const j = await res.json();
      const data = (j && (j.data || j)) || {};
      const quiz = data.quiz || null;
      const attemptStatus = String(data.attempt_status || '').toLowerCase();
      const isSubmittedAttempt = ['submitted','completed','finished','graded'].includes(attemptStatus);
      if (!quiz || !data.is_enrolled || !quiz.live_started_at || parseInt(quiz.is_force_open || 0, 10) !== 1 || isSubmittedAttempt) continue;
      _waitStartNotified.add(postId);
      try { sessionStorage.removeItem(key); } catch(e) {}
      const r = await Swal.fire({
        icon: 'success',
        title: 'Quiz Started',
        text: 'Your faculty started the quiz. You can take it now.',
        showCancelButton: true,
        confirmButtonText: 'Take Quiz Now',
        cancelButtonText: 'Later'
      });
      if (r.isConfirmed) {
        window.location.href = 'takequiz.php?post_id=' + encodeURIComponent(postId);
        return;
      }
    }
  } catch(e) {}
}

/* â”€â”€ AUTO-REFRESH POLLING â”€â”€ */
const _stPolls = {};
function startPoll(key, fn, ms) {
  if (_stPolls[key]) clearInterval(_stPolls[key]);
  _stPolls[key] = setInterval(fn, ms);
}
function stopPoll(key) {
  if (_stPolls[key]) { clearInterval(_stPolls[key]); delete _stPolls[key]; }
}
document.addEventListener('visibilitychange', () => {
  if (document.hidden) {
    ['st_cls','st_act','st_tasks','st_notif','st_waitstart'].forEach(stopPoll);
  } else {
    startPoll('st_cls',   loadClasses,       20000);
    startPoll('st_act',   loadActivity,      25000);
    startPoll('st_tasks', loadTasks,         30000);
    startPoll('st_notif', pollNotifications, 8000);
    startPoll('st_waitstart', watchStartedFromWaitingOnDashboard, 4000);
  }
});

/* â•â• INIT â•â• */
function safeRun(fn) {
  try { return fn(); } catch (e) { console.error('[student-ui]', e); return null; }
}
document.addEventListener('DOMContentLoaded', () => {
  safeRun(() => loadAll());
  safeRun(() => initMyClassTools());
  safeRun(() => loadSemesterFooter());
  safeRun(() => initNav());
  safeRun(() => initSidebar());
  safeRun(() => initDarkMode());
  safeRun(() => initNotifications());
  safeRun(() => pollNotifications());
  safeRun(() => watchStartedFromWaitingOnDashboard());
  safeRun(() => startPoll('st_cls',   loadClasses,       20000));
  safeRun(() => startPoll('st_act',   loadActivity,      25000));
  safeRun(() => startPoll('st_tasks', loadTasks,         30000));
  safeRun(() => startPoll('st_notif', pollNotifications, 8000));
  safeRun(() => startPoll('st_waitstart', watchStartedFromWaitingOnDashboard, 4000));
});

/* â•â• SEMESTER FOOTER â•â• */
function loadSemesterFooter() {
  fetch('API/Admin/get_semester_footer.php')
    .then(r => r.json())
    .then(d => {
      const sem = d.semester || '', sy = d.school_year || '';
      document.getElementById('footerSemText').textContent = (sem || '—') + ' · ' + (sy || '—');
    })
    .catch(() => { document.getElementById('footerSemText').textContent = '—'; });
}

/* â•â• LOAD ALL â•â• */
async function loadAll() {
  await Promise.all([loadStudentInfo(), loadClasses()]);
  loadActivity();
  loadGrades();
  loadSchedule();
  loadTasks();
}

/* â•â• STUDENT INFO â•â• */
async function loadStudentInfo() {
  try {
    const res  = await fetch('API/student/get_student_self.php');
    const data = await res.json();
    if (data.status !== 'success') return;
    studentInfo = data.student;
    renderStudentInfo();
  } catch(e) {}
}

function renderStudentInfo() {
  if (!studentInfo) return;
  const s = studentInfo;
  const fullName = [s.first_name, s.last_name].filter(Boolean).join(' ');
  const initials = ((s.first_name?.[0] ?? '') + (s.last_name?.[0] ?? '')).toUpperCase() || 'ST';
  const pic = s.profile_picture ? `${s.profile_picture}${s.profile_picture.includes('?') ? '&' : '?'}t=${Date.now()}` : '';
  const avatarHtml = pic ? `<img src="${esc(pic)}" alt="Student">` : esc(initials);
  document.getElementById('navAvatar').innerHTML = avatarHtml;
  document.getElementById('sidebarAvatar').innerHTML = avatarHtml;
  document.getElementById('sidebarName').textContent = fullName;
  document.getElementById('welcomeTitle').textContent = `Welcome back, ${s.first_name}! `;
  document.getElementById('profileAvatar').innerHTML = avatarHtml;
  document.getElementById('profileName').textContent = fullName;
  document.getElementById('profileStudentId').textContent = s.student_number || '—';
  document.getElementById('piEmail').textContent = s.email || '—';
  document.getElementById('piStuNo').textContent = s.student_number || '—';
  if (s.course_code && s.course_name)
    document.getElementById('piCourse').textContent = s.course_code + ' — ' + s.course_name;
}

function openStudentPicPicker() {
  document.getElementById('studentPicInput')?.click();
}
async function uploadStudentProfilePicture(input) {
  const file = input?.files?.[0];
  if (!file) return;
  try {
    const croppedBlob = await cropImageToSquareBlob(file, 640);
    const previewUrl = URL.createObjectURL(croppedBlob);
    const ok = await Swal.fire({
      title: 'Use this profile photo?',
      imageUrl: previewUrl,
      imageAlt: 'Profile preview',
      imageWidth: 220,
      imageHeight: 220,
      imageClass: 'swal2-img-square-preview',
      showCancelButton: true,
      confirmButtonText: 'Upload',
      cancelButtonText: 'Choose another'
    });
    URL.revokeObjectURL(previewUrl);
    if (!ok.isConfirmed) return;

    const fd = new FormData();
    fd.append('picture', new File([croppedBlob], `avatar_${Date.now()}.jpg`, { type:'image/jpeg' }));
    const res = await fetch('API/student/update_profile_picture.php', { method:'POST', body: fd });
    const data = await res.json();
    if (data.status !== 'success') { toast(data.message || 'Upload failed', 'error'); return; }
    toast('Profile picture updated.', 'success');
    await loadStudentInfo();
  } catch {
    toast('Upload failed.', 'error');
  } finally {
    input.value = '';
  }
}

function cropImageToSquareBlob(file, outputSize = 640) {
  return new Promise((resolve, reject) => {
    const fr = new FileReader();
    fr.onload = () => {
      const img = new Image();
      img.onload = () => {
        const srcW = img.naturalWidth || img.width;
        const srcH = img.naturalHeight || img.height;
        const side = Math.min(srcW, srcH);
        const sx = Math.floor((srcW - side) / 2);
        const sy = Math.floor((srcH - side) / 2);

        const canvas = document.createElement('canvas');
        canvas.width = outputSize;
        canvas.height = outputSize;
        const ctx = canvas.getContext('2d');
        if (!ctx) return reject(new Error('Canvas unavailable'));
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(img, sx, sy, side, side, 0, 0, outputSize, outputSize);
        canvas.toBlob((blob) => {
          if (!blob) return reject(new Error('Image conversion failed'));
          resolve(blob);
        }, 'image/jpeg', 0.92);
      };
      img.onerror = () => reject(new Error('Invalid image file'));
      img.src = String(fr.result || '');
    };
    fr.onerror = () => reject(new Error('Could not read file'));
    fr.readAsDataURL(file);
  });
}
async function removeStudentProfilePicture() {
  const ok = await Swal.fire({ title:'Remove profile picture?', icon:'warning', showCancelButton:true, confirmButtonText:'Remove', confirmButtonColor:'#d93025' });
  if (!ok.isConfirmed) return;
  try {
    const res = await fetch('API/student/remove_profile_picture.php', { method:'POST' });
    const data = await res.json();
    if (data.status !== 'success') { toast(data.message || 'Remove failed', 'error'); return; }
    toast('Profile picture removed.', 'success');
    await loadStudentInfo();
  } catch {
    toast('Remove failed.', 'error');
  }
}

/* â•â• CLASSES â•â• */
const meetCache = {};

async function loadClasses() {
  try {
    const res  = await fetch('API/student/get_student_classes.php');
    const data = await res.json();
    if (data.status !== 'success') { renderClassGrids([]); return; }
    enrolledClasses = data.classes || [];
    document.getElementById('classBadge').textContent = enrolledClasses.length;
    document.getElementById('statClasses').textContent = enrolledClasses.length;
    document.getElementById('psClasses').textContent  = enrolledClasses.length;
    renderClassGrids(enrolledClasses);
  } catch(e) { renderClassGrids([]); }
}

function renderClassGrids(classes) {
  const dashGrid = document.getElementById('dashClassGrid');
  const myGrid   = document.getElementById('myClassGrid');
  const myTable  = document.getElementById('myClassTable');
  const resultCount = document.getElementById('myClassResultCount');
  if (!classes.length) {
    const empty = `<div class="empty-state"><div class="empty-icon"><i class="fas fa-chalkboard"></i></div><div class="empty-title">No enrolled classes yet</div><div style="font-size:.82rem;">Ask your professor or registrar to enroll you.</div></div>`;
    dashGrid.innerHTML = empty;
    myGrid.innerHTML   = empty;
    if (myTable) myTable.innerHTML = '';
    if (resultCount) resultCount.textContent = '0 classes';
    return;
  }
  const dashCards = classes.map(c => buildClassCard(c)).join('');
  const filtered = getFilteredStudentClasses(classes);
  const myCards = filtered.map(c => buildClassCard(c)).join('');
  dashGrid.innerHTML = dashCards;
  myGrid.innerHTML   = myCards || `<div class="empty-state"><div class="empty-icon"><i class="fas fa-filter"></i></div><div class="empty-title">No classes matched</div><div style="font-size:.82rem;">Try another keyword or filter.</div></div>`;
  if (myTable) myTable.innerHTML = buildStudentClassTable(filtered);
  myGrid.style.display = myClassView === 'cards' ? 'grid' : 'none';
  if (myTable) myTable.style.display = myClassView === 'table' ? 'block' : 'none';
  if (resultCount) {
    const shown = filtered.length;
    const total = classes.length;
    resultCount.textContent = shown === total
      ? `${total} class${total !== 1 ? 'es' : ''}`
      : `${shown} of ${total}`;
  }
  renderStudentSectionMenu(classes);
  loadMeetForCards();
}

function getFilteredStudentClasses(classes) {
  const filtered = classes.filter(c => {
    if (myClassFilterType === 'with_meet' && !hasClassMeet(c)) return false;
    if (myClassFilterType === 'no_meet' && hasClassMeet(c)) return false;
    if (myClassSectionFilter !== 'all' && String(c.section || '').trim() !== myClassSectionFilter) return false;
    if (!myClassSearchQuery) return true;
    return classSearchText(c).includes(myClassSearchQuery);
  });

  return filtered.sort((a, b) => {
    let va = '';
    let vb = '';
    if (myClassSortField === 'section') {
      va = String(a.section || '').toLowerCase();
      vb = String(b.section || '').toLowerCase();
    } else if (myClassSortField === 'professor') {
      va = getFacultyName(a).toLowerCase();
      vb = getFacultyName(b).toLowerCase();
    } else {
      va = String(a.subject_code || a.subject_name || a.class_code || '').toLowerCase();
      vb = String(b.subject_code || b.subject_name || b.class_code || '').toLowerCase();
    }
    return myClassSortDir === 'az' ? va.localeCompare(vb) : vb.localeCompare(va);
  });
}

function renderStudentSectionMenu(classes) {
  const menu = document.getElementById('studentSectionMenu');
  const btn = document.getElementById('studentSectionBtn');
  if (!menu || !btn) return;
  const sections = [...new Set(classes.map(c => String(c.section || '').trim()).filter(Boolean))].sort((a, b) => a.localeCompare(b));
  menu.innerHTML = [
    `<button type="button" class="${myClassSectionFilter === 'all' ? 'is-selected' : ''}" onclick="setStudentSectionFilter('all')"><i class="fas fa-layer-group"></i> All Sections</button>`,
    ...sections.map(section => `<button type="button" class="${myClassSectionFilter === section ? 'is-selected' : ''}" onclick="setStudentSectionFilter(${esc(JSON.stringify(section))})"><i class="fas fa-layer-group"></i> ${esc(section)}</button>`)
  ].join('');
  btn.classList.toggle('active', myClassSectionFilter !== 'all');
  btn.innerHTML = `<i class="fas fa-layer-group"></i> ${myClassSectionFilter === 'all' ? 'All Sections' : esc(myClassSectionFilter)} <i class="fas fa-chevron-down" style="font-size:.6rem;"></i>`;
}

function buildStudentClassTable(classes) {
  let rows = [...classes];
  if (myClassTableSort.col) {
    rows.sort((a, b) => {
      let va = '';
      let vb = '';
      if (myClassTableSort.col === 'class') { va = String(a.subject_code || a.subject_name || '').toLowerCase(); vb = String(b.subject_code || b.subject_name || '').toLowerCase(); }
      if (myClassTableSort.col === 'section') { va = String(a.section || '').toLowerCase(); vb = String(b.section || '').toLowerCase(); }
      if (myClassTableSort.col === 'professor') { va = getFacultyName(a).toLowerCase(); vb = getFacultyName(b).toLowerCase(); }
      if (myClassTableSort.col === 'schedule') { va = String(a.class_days || a.schedule || '').toLowerCase(); vb = String(b.class_days || b.schedule || '').toLowerCase(); }
      if (myClassTableSort.col === 'meet') { va = hasClassMeet(a) ? 1 : 0; vb = hasClassMeet(b) ? 1 : 0; }
      return va < vb ? -myClassTableSort.dir : va > vb ? myClassTableSort.dir : 0;
    });
  }
  const thSort = (col, label) => {
    const active = myClassTableSort.col === col ? ' tbl-sorted' : '';
    const icon = myClassTableSort.col === col ? (myClassTableSort.dir === 1 ? '&uarr;' : '&darr;') : '&udarr;';
    return `<th class="${active}" onclick="studentTblSort('${col}')">${label} <span class="sort-ic">${icon}</span></th>`;
  };
  const bodyRows = rows.length ? rows.map(cls => {
    const sectionText = String(cls.section || '').trim();
    const hasValidSection = !!sectionText && sectionText !== '0';
    const className = [hasValidSection ? sectionText : '', cls.subject_code].filter(Boolean).join(' ') || cls.class_code || 'Class';
    const classSub = cls.subject_name || cls.class_semester || '';
    const pal = paletteFor(cls.subject_name || cls.subject_code || cls.class_code || '');
    const dot = PALETTE_COLORS[pal] || '#1a9e78';
    const sched = cls.schedule ? cls.schedule.split('-').map(t => fmt12(t.trim())).join(' - ') : '';
    const professor = getFacultyName(cls);
    const profInit = professor ? professor.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : '?';
    const meet = hasClassMeet(cls) ? 'With Meet' : 'No Meet';
    return `<tr onclick="openClass('${esc(cls.id)}')">
      <td><div class="tbl-name-cell">
        <span class="tbl-name-dot" style="background:${dot}"></span>
        <div><div class="tbl-name-main">${esc(className)}</div>${classSub ? `<div class="tbl-name-sub">${esc(classSub)}</div>` : ''}</div>
      </div></td>
      <td class="tbl-muted">${esc(sectionText || '-')}</td>
      <td><div class="tbl-prof">
        <div class="tbl-prof-avatar" style="background:${dot};">${cls.faculty_profile_picture ? `<img src="${esc(cls.faculty_profile_picture)}" alt="faculty">` : esc(profInit)}</div>
        <div><div class="tbl-name-main" style="font-size:.82rem;">${professor ? esc(professor) : 'No professor'}</div><div class="tbl-name-sub">${cls.faculty_is_dean == 1 ? 'Dean' : 'Professor'}</div></div>
      </div></td>
      <td class="tbl-muted"><div>${esc(cls.class_days || '-')}</div><div style="font-size:.72rem;">${esc(sched || '-')}</div></td>
      <td class="tbl-muted">${esc(meet)}</td>
      <td onclick="event.stopPropagation()"><div class="tbl-actions">
        <button type="button" class="tbl-icon-btn" title="Archive" onclick="confirmArchiveClass('${esc(cls.id)}')"><i class="fas fa-archive"></i></button>
        <button type="button" class="tbl-icon-btn danger" title="Leave Class" onclick="confirmLeaveClass('${esc(cls.id)}')"><i class="fas fa-sign-out-alt"></i></button>
      </div></td>
    </tr>`;
  }).join('') : `<tr><td colspan="6"><div class="tbl-empty"><i class="fas fa-filter"></i>No classes matched.</div></td></tr>`;

  return `<div class="tbl-toolbar">
    <span class="tbl-count">${rows.length} class${rows.length !== 1 ? 'es' : ''}</span>
  </div>
  <div style="overflow-x:auto;">
    <table class="class-table">
      <thead><tr>
        ${thSort('class','Class')}
        ${thSort('section','Section')}
        ${thSort('professor','Professor')}
        ${thSort('schedule','Schedule')}
        ${thSort('meet','Meet')}
        <th></th>
      </tr></thead>
      <tbody>${bodyRows}</tbody>
    </table>
  </div>`;
}

function initMyClassTools() {
  const search = document.getElementById('myClassSearch');
  const clearSearch = document.getElementById('myClassSearchClear');
  const filterWrap = document.getElementById('myClassFilter');
  if (search) {
    search.addEventListener('input', () => {
      myClassSearchQuery = search.value.trim().toLowerCase();
      search.closest('.class-search')?.classList.toggle('has-value', !!myClassSearchQuery);
      renderClassGrids(enrolledClasses || []);
    });
  }
  if (clearSearch && search) {
    clearSearch.addEventListener('click', () => {
      search.value = '';
      myClassSearchQuery = '';
      search.closest('.class-search')?.classList.remove('has-value');
      search.focus();
      renderClassGrids(enrolledClasses || []);
    });
  }
  if (filterWrap) {
    filterWrap.querySelectorAll('.class-filter-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        myClassFilterType = btn.dataset.filter || 'all';
        filterWrap.querySelectorAll('.class-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderClassGrids(enrolledClasses || []);
      });
    });
  }
}

function closeStudentClassMenus() {
  document.getElementById('studentSortMenu')?.classList.remove('open');
  document.getElementById('studentSectionMenu')?.classList.remove('open');
}

function toggleStudentSortMenu(event) {
  event.stopPropagation();
  document.getElementById('studentSectionMenu')?.classList.remove('open');
  document.getElementById('studentSortMenu')?.classList.toggle('open');
}

function toggleStudentSectionMenu(event) {
  event.stopPropagation();
  document.getElementById('studentSortMenu')?.classList.remove('open');
  document.getElementById('studentSectionMenu')?.classList.toggle('open');
}

function toggleStudentSortDir() {
  myClassSortDir = myClassSortDir === 'az' ? 'za' : 'az';
  const btn = document.getElementById('studentSortDirBtn');
  const isAz = myClassSortDir === 'az';
  if (btn) btn.innerHTML = `<i class="fas fa-sort-alpha-${isAz ? 'down' : 'up'}"></i> ${isAz ? 'A-Z' : 'Z-A'}`;
  renderClassGrids(enrolledClasses || []);
}

function setStudentSortField(field) {
  myClassSortField = field;
  const labels = { course: 'Course', section: 'Section', professor: 'Professor' };
  const icons = { course: 'fa-book-open', section: 'fa-layer-group', professor: 'fa-chalkboard-teacher' };
  const btn = document.getElementById('studentSortFieldBtn');
  if (btn) btn.innerHTML = `<i class="fas ${icons[field] || 'fa-filter'}"></i> ${labels[field] || 'Course'} <i class="fas fa-chevron-down" style="font-size:.6rem;"></i>`;
  document.querySelectorAll('#studentSortMenu [data-sort-field]').forEach(item => {
    item.classList.toggle('is-selected', item.dataset.sortField === field);
  });
  closeStudentClassMenus();
  renderClassGrids(enrolledClasses || []);
}

function setStudentSectionFilter(section) {
  myClassSectionFilter = section || 'all';
  closeStudentClassMenus();
  renderClassGrids(enrolledClasses || []);
}

function setStudentClassView(view) {
  myClassView = view;
  document.getElementById('studentViewCards')?.classList.toggle('active', view === 'cards');
  document.getElementById('studentViewTable')?.classList.toggle('active', view === 'table');
  renderClassGrids(enrolledClasses || []);
}

function studentTblSort(col) {
  if (myClassTableSort.col === col) myClassTableSort.dir *= -1;
  else myClassTableSort = { col, dir: 1 };
  renderClassGrids(enrolledClasses || []);
}

document.addEventListener('click', closeStudentClassMenus);


function buildClassCard(cls) {
  const normYear = Number(cls.year_level || 0);
  const hasValidYear = Number.isFinite(normYear) && normYear > 0;
  const sectionText = String(cls.section || '').trim();
  const hasValidSection = !!sectionText && sectionText !== '0';
  const palKey = cls.subject_name || cls.subject_code || cls.class_code || '';
  const pal    = paletteFor(palKey);
  const name   = [hasValidSection ? sectionText : '', cls.subject_code].filter(Boolean).join(' ') || cls.class_code || 'Class';
  const code   = cls.subject_name || cls.subject_code || '';
 
  let schedHtml = '';
  if (cls.schedule) {
    const parts = cls.schedule.split('-').map(t => fmt12(t.trim()));
    schedHtml = `<span class="chip"><i class="fas fa-clock"></i>${esc(parts.join(' \u2013 '))}</span>`;
  }
 
  /* â”€â”€ Faculty data â”€â”€ */
  const facFirst = cls.faculty_first_name  || '';
  const facLast  = cls.faculty_last_name   || '';
  const facName  = (facFirst || facLast)
    ? `${facFirst} ${facLast}`.trim()
    : (cls.faculty_name || '');
  const facEmail = cls.faculty_email || '';
  const facPhone = cls.faculty_phone || '';
  const isDean   = cls.faculty_is_dean == 1;
 
  /* Initials */
  const facInit = facName
    ? ((facFirst[0] || '') + (facLast[0] || '')).toUpperCase() || facName.substring(0,2).toUpperCase()
    : '?';
 
  /* Avatar color — consistent per name */
  const avatarColors = [
    '#1a9e78','#1f73db','#7b1fa2','#00838f',
    '#c62828','#f57c00','#455a64','#3949ab'
  ];
  let h = 0;
  for (const c of facName) h = ((h << 5) - h) + c.charCodeAt(0);
  const avatarBg = facName ? avatarColors[Math.abs(h) % avatarColors.length] : '#94a3b8';
 
  /* Professor subtitle line */
  let facSubHtml = '';
  if (!facName) {
    facSubHtml = `<span style="font-size:.7rem;color:var(--text-muted);font-style:italic;">No professor assigned</span>`;
  } else if (isDean) {
    facSubHtml = `<span style="display:inline-flex;align-items:center;gap:.22rem;
      font-size:.63rem;font-weight:600;padding:.1rem .42rem;border-radius:20px;
      background:var(--accent-light);color:var(--accent);
      border:1px solid rgba(31,115,219,.25);">
      <i class="fas fa-user-tie" style="font-size:.56rem;"></i> Dean
    </span>`;
  } else if (facEmail) {
    facSubHtml = `<span style="font-size:.7rem;color:var(--text-muted);
      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:160px;">
      ${esc(facEmail)}
    </span>`;
  } else if (facPhone) {
    facSubHtml = `<span style="font-size:.7rem;color:var(--text-muted);">${esc(facPhone)}</span>`;
  } else {
    facSubHtml = `<span style="font-size:.7rem;color:var(--text-muted);">Professor</span>`;
  }
 
  return `
    <div class="class-card" onclick="openClass('${esc(cls.id)}')">
 
      <!-- â”€â”€ Banner â”€â”€ -->
      <div class="card-banner ${pal}" style="position:relative;">
        <div class="card-kebab-wrap">
          <button class="card-kebab-btn" id="kebab-btn-${esc(cls.id)}" onclick="toggleKebab('${esc(cls.id)}', event)">            <i class="fas fa-ellipsis-v"></i>
          </button>
          <div class="card-kebab-menu" id="kebab-menu-${esc(cls.id)}" style="display:none;">
            <button class="ckm-item warn" onclick="event.stopPropagation();confirmArchiveClass('${esc(cls.id)}')">
              <i class="fas fa-archive"></i> Archive
            </button>
            <div class="ckm-sep"></div>
            <button class="ckm-item danger" onclick="event.stopPropagation();confirmLeaveClass('${esc(cls.id)}')">
              <i class="fas fa-sign-out-alt"></i> Leave Class
            </button>
          </div>
        </div>
        <div class="banner-title">${esc(name)}</div>
        <div class="banner-code">${esc(code)}</div>
      </div>
 
      <!-- â”€â”€ Info chips â”€â”€ -->
      <div class="card-body">
        <div class="card-chips">
          ${hasValidSection ? `<span class="chip"><i class="fas fa-layer-group"></i>${esc(sectionText)}</span>` : ''}
          ${hasValidYear ? `<span class="chip"><i class="fas fa-graduation-cap"></i>${esc(normYear)}</span>` : ''}
          ${cls.class_semester ? `<span class="chip"><i class="fas fa-calendar-alt"></i>${esc(cls.class_semester)}</span>` : ''}
          ${cls.class_days     ? `<span class="chip"><i class="fas fa-calendar-week"></i>${esc(cls.class_days)}</span>` : ''}
          ${schedHtml}
        </div>
      </div>
 
      <!-- â”€â”€ Professor strip (above meet bar, full-width, flush) â”€â”€ -->
      <div style="
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .6rem 1.1rem;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        background: var(--bg);
        min-width: 0;
      ">
        <!-- Avatar -->
        <div style="
          width: 34px; height: 34px;
          border-radius: 50%;
          flex-shrink: 0;
          background: ${facName ? avatarBg : 'var(--border)'};
          display: flex; align-items: center; justify-content: center;
          color: #fff; font-size: .75rem; font-weight: 700;
          box-shadow: 0 1px 4px rgba(0,0,0,.15);
          overflow: hidden; position: relative;
        ">${cls.faculty_profile_picture
          ? `<img src="${esc(cls.faculty_profile_picture)}" alt="faculty" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:50%;">`
          : facInit}</div>
 
        <!-- Name + subtitle -->
        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; gap: .1rem;">
          <span style="
            font-size: .84rem; font-weight: 600;
            color: var(--text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            line-height: 1.2;
          ">${facName ? esc(facName) : 'No professor assigned'}</span>
          <div style="line-height: 1.2;">${facSubHtml}</div>
        </div>
 
        <!-- Dean/faculty icon badge -->
        <div style="flex-shrink: 0; display: flex; align-items: center;">
          <i class="fas fa-chalkboard-teacher"
             style="font-size: .82rem; color: var(--primary); opacity: .55;"></i>
        </div>
      </div>
 
      <!-- â”€â”€ Meet bar â”€â”€ -->
      <div class="meet-bar" id="meetbar-${esc(cls.id)}" onclick="event.stopPropagation()">
        <div class="meet-loading" id="meetloading-${esc(cls.id)}">
          <i class="fas fa-circle-notch fa-spin" style="font-size:.7rem;color:#1a73e8;"></i>
          <span style="font-size:.7rem;">Loading Meet\u2026</span>
        </div>
        <div id="meetready-${esc(cls.id)}"
             style="display:none;align-items:center;gap:.5rem;flex-wrap:wrap;">
          <a id="meetlink-${esc(cls.id)}" href="#" target="_blank"
             class="meet-btn" onclick="event.stopPropagation()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
              <path d="M20 4.5l-5 3.5V5a2 2 0 00-2-2H4a2 2 0 00-2 2v14a2 2 0 002 2h9a2 2 0 002-2v-3l5 3.5A1 1 0 0022 18V6a1 1 0 00-2-1.5z"/>
            </svg>
            Join Meet
          </a>
          <span class="meet-code-pill" id="meetcodepill-${esc(cls.id)}"
                onclick="event.stopPropagation();copyMeetCode('${esc(cls.id)}')"
                title="Click to copy">\u2014</span>
        </div>
      </div>
 
    </div>`;
}
function loadMeetForCards() {
  document.querySelectorAll('[id^="meetbar-"]').forEach(bar => {
    const classId = bar.id.replace('meetbar-', '');
    if (meetCache[classId]) applyMeet(classId, meetCache[classId]);
    else fetchMeet(classId);
  });
}

async function fetchMeet(classId) {
  try {
    const res  = await fetch(`API/student/get_meeting.php?class_id=${encodeURIComponent(classId)}`);
    const data = await res.json();
    if (data.status === 'success') {
      meetCache[classId] = { meeting_code: data.meeting_code, meet_url: data.meet_url };
      const cls = enrolledClasses.find(c => String(c.id) === String(classId));
      if (cls) {
        cls.meeting_code = data.meeting_code;
        cls.meet_url = data.meet_url;
      }
      applyMeet(classId, meetCache[classId]);
      if (myClassFilterType !== 'all' || myClassView === 'table' || myClassTableSort.col === 'meet') {
        renderClassGrids(enrolledClasses || []);
      }
    } else hideMeetLoad(classId);
  } catch { hideMeetLoad(classId); }
}

function applyMeet(classId, meet) {
  const loadEl  = document.getElementById(`meetloading-${classId}`);
  const readyEl = document.getElementById(`meetready-${classId}`);
  const linkEl  = document.getElementById(`meetlink-${classId}`);
  const pillEl  = document.getElementById(`meetcodepill-${classId}`);
  if (!loadEl || !readyEl) return;
  if (meet && meet.meeting_code) {
    if (linkEl) linkEl.href = meet.meet_url;
    if (pillEl) pillEl.textContent = meet.meeting_code;
    loadEl.style.display  = 'none';
    readyEl.style.display = 'flex';
  } else hideMeetLoad(classId);
}

function hideMeetLoad(classId) {
  const el = document.getElementById(`meetloading-${classId}`);
  if (el) el.innerHTML = '<span style="font-size:.7rem;color:var(--text-muted);">Meet unavailable</span>';
}

function copyMeetCode(classId) {
  const meet = meetCache[classId];
  if (!meet) return;
  navigator.clipboard.writeText(meet.meeting_code).then(() => toast(`Copied: ${meet.meeting_code}`, 'success')).catch(() => {
    const ta = document.createElement('textarea'); ta.value = meet.meeting_code;
    document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove();
    toast(`Copied: ${meet.meeting_code}`, 'success');
  });
}
let _kebabActiveId = null;
let _kebabMenuEl = null;
let _kebabMenuHost = null;
let _kebabMenuNext = null;

function closeAllKebabMenus() {
  if (_kebabMenuEl) {
    _kebabMenuEl.style.display = 'none';
    _kebabMenuEl.style.position = '';
    _kebabMenuEl.style.top = '';
    _kebabMenuEl.style.left = '';
    _kebabMenuEl.style.right = '';
    _kebabMenuEl.style.zIndex = '';
    if (_kebabMenuHost) {
      _kebabMenuHost.insertBefore(_kebabMenuEl, _kebabMenuNext);
    }
    _kebabMenuEl = null;
    _kebabMenuHost = null;
    _kebabMenuNext = null;
  }
  document.querySelectorAll('.card-kebab-menu').forEach(menu => { menu.style.display = 'none'; });
  document.querySelectorAll('.class-card.kebab-open').forEach(card => card.classList.remove('kebab-open'));
  _kebabActiveId = null;
}

function toggleKebab(classId, ev) {
  if (ev) ev.stopPropagation();
  const menu = document.getElementById('kebab-menu-' + classId);
  const btn  = document.getElementById('kebab-btn-' + classId);
  const card = menu ? menu.closest('.class-card') : null;
  if (!menu || !btn) return;

  if (_kebabActiveId === classId && menu.style.display === 'block') {
    closeAllKebabMenus();
    return;
  }

  closeAllKebabMenus();
  _kebabActiveId = classId;
  const r = btn.getBoundingClientRect();
  _kebabMenuEl = menu;
  _kebabMenuHost = menu.parentNode;
  _kebabMenuNext = menu.nextSibling;
  document.body.appendChild(menu);
  menu.style.position = 'fixed';
  menu.style.top = `${Math.round(r.bottom + 6)}px`;
  menu.style.left = `${Math.round(Math.max(8, r.right - 160))}px`;
  menu.style.right = 'auto';
  menu.style.zIndex = '100000';
  menu.style.display = 'block';
  if (card) card.classList.add('kebab-open');
}

document.addEventListener('click', (e) => {
  const withinKebab = e.target.closest('.card-kebab-wrap') || e.target.closest('.card-kebab-menu');
  if (!withinKebab) closeAllKebabMenus();
});
document.addEventListener('touchstart', (e) => {
  const withinKebab = e.target.closest('.card-kebab-wrap') || e.target.closest('.card-kebab-menu');
  if (!withinKebab) closeAllKebabMenus();
}, { passive: true });
window.addEventListener('scroll', closeAllKebabMenus, true);

function openClass(classId) {
  window.location.href = 'studentClassRoom.php?class_id=' + encodeURIComponent(classId);
}

function confirmArchiveClass(classId) {
  Swal.fire({
    title: 'Archive this class?',
    html: '<div style="font-size:.9rem;color:#5f6368;">It will be moved to your archive and hidden from your active classes.</div>',
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#f57c00', cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-archive" style="margin-right:.4rem;"></i> Archive',
    cancelButtonText: 'Cancel'
  }).then(async r => {
    if (!r.isConfirmed) return;
    try {
      const res  = await fetch('API/student/archive_class.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ class_id: classId }) });
      const data = await res.json();
      if (data.status === 'success') { toast('Class archived', 'success'); loadClasses(); }
      else toast(data.message || 'Could not archive class', 'error');
    } catch { toast('Network error', 'error'); }
  });
}

function confirmLeaveClass(classId) {
  Swal.fire({
    title: 'Leave this class?',
    html: '<div style="font-size:.9rem;color:#5f6368;">You will be unenrolled. Your progress and grades may be affected.</div>',
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#d93025', cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-sign-out-alt" style="margin-right:.4rem;"></i> Leave Class',
    cancelButtonText: 'Cancel'
  }).then(async r => {
    if (!r.isConfirmed) return;
    try {
      const res  = await fetch('API/student/leave_class.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ class_id: classId }) });
      const data = await res.json();
      if (data.status === 'success') { toast('You have left the class', 'success'); loadClasses(); }
      else toast(data.message || 'Could not leave class', 'error');
    } catch { toast('Network error', 'error'); }
  });
}

/* â•â• ACTIVITY FEED â•â• */
async function loadActivity() {
  try {
    const res  = await fetch('API/student/get_student_activity.php');
    const data = await res.json();
    if (data.status !== 'success') { renderActivity([]); return; }
    activityItems = data.items || [];
    document.getElementById('activityBadge').textContent = activityItems.filter(i => !i.seen).length || '0';
    document.getElementById('activityBadge').style.background = activityItems.filter(i => !i.seen).length ? '#7c3aed' : '#94a3b8';
    renderActivity(activityItems);
  } catch { renderActivity([]); }
}

function renderActivity(items) {
  const feed = document.getElementById('activityFeed');
  if (!items.length) {
    feed.innerHTML = `<div class="empty-state"><div class="empty-icon"><i class="fas fa-stream"></i></div><div class="empty-title">No activity yet</div><div style="font-size:.82rem;">Your professors' posts and quizzes will appear here.</div></div>`;
    return;
  }
  const typeMap = {
    quiz:       ['feed-type-quiz',   'fa-tasks',       'Quiz'],
    assignment: ['feed-type-assign', 'fa-file-alt',    'Assignment'],
    meeting:    ['feed-type-meet',   'fa-video',       'Meeting'],
    post:       ['feed-type-annc',   'fa-bullhorn',    'Announcement'],
    default:    ['feed-type-annc',   'fa-info-circle', 'Update'],
  };
  feed.innerHTML = items.map(item => {
    const [cls, icon, label] = typeMap[item.type] || typeMap.default;
    const pal = paletteFor(item.class_name || '');
    const colorMap = { 'b-forest':'#1a9e78','b-ocean':'#1f73db','b-sunset':'#f57c00','b-plum':'#7b1fa2','b-teal':'#00838f','b-rose':'#c62828','b-slate':'#455a64','b-indigo':'#3949ab' };
    const avatarBg = colorMap[pal] || '#1a9e78';
    return `<div class="feed-card">
      <div class="feed-header">
        <div class="feed-avatar" style="background:${avatarBg};">${esc((item.faculty_name||'P').split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase())}</div>
        <div class="feed-meta">
          <div class="feed-author">${esc(item.faculty_name || 'Professor')}</div>
          <div class="feed-time">${relTime(item.created_at)} · ${esc(item.class_semester||'')}</div>
        </div>
        <span class="feed-class-badge">${esc(item.class_name || item.class_code || '')}</span>
      </div>
      <div class="feed-body">
        <div class="${cls} feed-type-badge"><i class="fas ${icon}" style="font-size:.62rem;"></i>${label}</div>
        <div style="font-weight:600;font-size:.92rem;margin-bottom:.3rem;">${esc(item.title || '')}</div>
        <div style="color:var(--text-muted);font-size:.85rem;line-height:1.65;">${esc(item.description || item.body || '')}</div>
        ${item.due_date ? `<div style="margin-top:.6rem;font-size:.75rem;color:var(--warning);font-weight:600;"><i class="fas fa-clock" style="margin-right:.3rem;"></i>Due: ${esc(item.due_date)}</div>` : ''}
      </div>
    </div>`;
  }).join('');
}

/* â•â• UPCOMING â•â• */
async function loadSchedule() {
  try {
    const res  = await fetch('API/student/get_student_schedule.php');
    const data = await res.json();
    if (data.status !== 'success') { renderSchedule([]); renderUpcoming([]); return; }
    renderSchedule(data.schedule || []);
    renderUpcoming(data.upcoming || []);
  } catch { renderSchedule([]); renderUpcoming([]); }
}

function renderUpcoming(items) {
  const el = document.getElementById('upcomingList');
  if (!items.length) {
    el.innerHTML = `<div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem;"><i class="fas fa-check-circle" style="margin-right:.4rem;color:var(--primary);"></i>All clear — nothing due soon!</div>`;
    return;
  }
  const colors = ['#1a9e78','#1f73db','#f57c00','#7b1fa2','#00838f','#c62828'];
  el.innerHTML = items.map((item, i) => `
    <div class="schedule-item">
      <div class="schedule-dot" style="background:${colors[i % colors.length]};"></div>
      <div class="schedule-info">
        <div class="schedule-title">${esc(item.title)}</div>
        <div class="schedule-sub">${esc(item.class_name || '')} ${item.type ? '· ' + item.type : ''}</div>
      </div>
      <div class="schedule-time">${esc(item.due_date || item.date || '')}</div>
    </div>`).join('');
}

function renderSchedule(classes) {
  const grid = document.getElementById('scheduleGrid');
  const days  = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
  const grouped = {};
  days.forEach(d => grouped[d] = []);
  (classes || enrolledClasses).forEach(cls => {
    if (!cls.class_days) return;
    cls.class_days.split(',').map(d => d.trim()).forEach(d => {
      if (grouped[d]) grouped[d].push(cls);
    });
  });
  const activeDays = days.filter(d => grouped[d].length > 0);
  if (!activeDays.length) {
    grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;"><div class="empty-icon"><i class="fas fa-calendar-week"></i></div><div class="empty-title">No schedule data</div></div>`;
    return;
  }
  const dayFull = {Mon:'Monday',Tue:'Tuesday',Wed:'Wednesday',Thu:'Thursday',Fri:'Friday',Sat:'Saturday',Sun:'Sunday'};
  grid.innerHTML = activeDays.map(day => {
    const items = grouped[day];
    return `<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);">
      <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;padding:.65rem 1rem;font-weight:700;font-size:.85rem;">${dayFull[day]}</div>
      ${items.map(cls => {
        const sched = cls.schedule ? cls.schedule.split('-').map(t=>fmt12(t.trim())).join('–') : '—';
        const pal = paletteFor(cls.subject_name || cls.subject_code || '');
        const colors2 = {'b-forest':'#1a9e78','b-ocean':'#1f73db','b-sunset':'#f57c00','b-plum':'#7b1fa2','b-teal':'#00838f','b-rose':'#c62828','b-slate':'#455a64','b-indigo':'#3949ab'};
        const bc = colors2[pal] || '#1a9e78';
        return `<div style="padding:.7rem 1rem;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:.65rem;">
          <div style="width:4px;min-height:36px;border-radius:2px;background:${bc};flex-shrink:0;margin-top:.1rem;"></div>
          <div>
            <div style="font-size:.82rem;font-weight:600;">${esc(cls.subject_code || cls.class_code || '')}</div>
            <div style="font-size:.72rem;color:var(--text-muted);">${esc(cls.section||'')} · ${sched}</div>
            <div style="font-size:.7rem;color:var(--text-muted);margin-top:.1rem;">${esc(cls.faculty_name||'')}</div>
          </div>
        </div>`;
      }).join('')}
    </div>`;
  }).join('');
}

/* â•â• GRADES â•â• */
async function loadGrades() {
  try {
    const res  = await fetch('API/student/get_student_grades.php');
    const data = await res.json();
    if (data.status !== 'success') { renderGrades([]); return; }
    gradesData = data.grades || [];
    renderGrades(gradesData);
    // Avg grade stat
    const numeric = gradesData.filter(g => g.final_grade && !isNaN(parseFloat(g.final_grade)));
    if (numeric.length) {
      const avg = (numeric.reduce((sum, g) => sum + parseFloat(g.final_grade), 0) / numeric.length).toFixed(1);
      document.getElementById('statAvgGrade').textContent = avg;
      document.getElementById('psGrade').textContent = avg;
    } else {
      document.getElementById('statAvgGrade').textContent = 'N/A';
      document.getElementById('psGrade').textContent = 'N/A';
    }
  } catch { renderGrades([]); }
}

function renderGrades(list) {
  const tbody = document.querySelector('#gradesTable tbody');
  document.getElementById('gradesCnt').textContent = `${list.length} subject${list.length!==1?'s':''}`;
  if (!list.length) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No grade records yet.</td></tr>';
    return;
  }
  tbody.innerHTML = list.map(g => {
    let gpClass = 'gp-na', gpText = 'N/A';
    const fg = parseFloat(g.final_grade);
    if (!isNaN(fg)) {
      if (fg >= 75) { gpClass = 'gp-pass'; gpText = fg.toFixed(1); }
      else          { gpClass = 'gp-fail'; gpText = fg.toFixed(1); }
    } else if (g.final_grade === 'INC') { gpClass = 'gp-pending'; gpText = 'INC'; }
    else if (g.final_grade) { gpClass = 'gp-pass'; gpText = g.final_grade; }
    return `<tr>
      <td><div style="font-weight:600;font-size:.82rem;">${esc(g.subject_name||g.subject_code||'—')}</div><div style="font-size:.7rem;color:var(--text-muted);">${esc(g.subject_code||'')}</div></td>
      <td style="font-size:.78rem;color:var(--text-muted);">${esc(g.faculty_name||'—')}</td>
      <td style="font-size:.78rem;color:var(--text-muted);">${esc(g.class_semester||'—')}</td>
      <td style="font-size:.78rem;text-align:center;">${(g.quiz_grade !== null && g.quiz_grade !== undefined && g.quiz_grade !== '') ? esc(String(g.quiz_grade)) : '—'}</td>
      <td style="font-size:.78rem;text-align:center;">${(g.assignment_grade !== null && g.assignment_grade !== undefined && g.assignment_grade !== '') ? esc(String(g.assignment_grade)) : '—'}</td>
      <td style="font-size:.78rem;text-align:center;">${(g.participation_grade !== null && g.participation_grade !== undefined && g.participation_grade !== '') ? esc(String(g.participation_grade)) : '—'}</td>
      <td style="text-align:center;"><span class="grade-pill ${gpClass}">${esc(gpText)}</span></td>
      <td style="text-align:center;"><span class="grade-pill ${fg >= 75 || g.final_grade === 'PASS' ? 'gp-pass' : (!g.final_grade ? 'gp-na' : 'gp-fail')}">${fg >= 75 || g.final_grade === 'PASS' ? 'Passed' : (!g.final_grade ? '—' : 'Failed')}</span></td>
    </tr>`;
  }).join('');
}

// Grades search
document.getElementById('gradesSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  const filtered = gradesData.filter(g => (g.subject_name||'').toLowerCase().includes(q) || (g.subject_code||'').toLowerCase().includes(q));
  renderGrades(filtered);
});

/* â•â• TASKS â•â• */
async function loadTasks() {
  try {
    const res  = await fetch('API/student/get_student_tasks.php');
    const data = await res.json();
    if (data.status !== 'success') { renderTasks([], []); return; }
    pendingItems   = data.pending   || [];
    completedItems = data.completed || [];
    document.getElementById('statPending').textContent   = pendingItems.length;
    document.getElementById('statSubmitted').textContent = completedItems.length;
    document.getElementById('quizBadge').textContent     = pendingItems.length || '0';
    document.getElementById('quizBadge').style.background = pendingItems.length ? 'var(--warning)' : '#94a3b8';
    renderTasks(pendingItems, completedItems);
  } catch { renderTasks([], []); }
}

function taskCard(item, pending = true) {
  const typeMap = { quiz:'fa-tasks', assignment:'fa-file-alt', activity:'fa-pen', default:'fa-clipboard' };
  const icon = typeMap[item.type] || typeMap.default;
  const pal = paletteFor(item.class_name || '');
  const colorMap = {'b-forest':'#1a9e78','b-ocean':'#1f73db','b-sunset':'#f57c00','b-plum':'#7b1fa2','b-teal':'#00838f','b-rose':'#c62828','b-slate':'#455a64','b-indigo':'#3949ab'};
  const bc = colorMap[pal] || '#1a9e78';
  return `<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:.9rem 1.1rem;display:flex;align-items:flex-start;gap:.85rem;margin-bottom:.65rem;box-shadow:var(--shadow);transition:all var(--transition);" onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow='var(--shadow)'">
    <div style="width:40px;height:40px;border-radius:10px;background:${pending?'var(--accent-light)':'var(--primary-light)'};color:${pending?'var(--accent)':'var(--primary)'};display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;">
      <i class="fas ${icon}"></i>
    </div>
    <div style="flex:1;">
      <div style="font-weight:600;font-size:.88rem;">${esc(item.title||'')}</div>
      <div style="font-size:.75rem;color:var(--text-muted);margin-top:.1rem;">${esc(item.class_name||'')} · ${esc(item.class_semester||'')}</div>
      ${item.due_date ? `<div style="font-size:.72rem;color:${pending?'var(--warning)':'var(--primary)'};margin-top:.25rem;font-weight:600;"><i class="fas fa-clock" style="margin-right:.25rem;"></i>${pending?'Due':'Submitted'}: ${esc(item.due_date)}</div>` : ''}
    </div>
    ${pending ? `<button class="btn btn-primary" style="padding:.35rem .85rem;font-size:.75rem;" onclick="openTask('${esc(item.id)}','${esc(item.class_id)}')"><i class="fas fa-arrow-right"></i> Open</button>` : `<span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:700;padding:.22rem .65rem;border-radius:20px;background:var(--primary-light);color:var(--primary);"><i class="fas fa-check" style="font-size:.6rem;"></i>Done</span>`}
  </div>`;
}

function renderTasks(pending, completed) {
  document.getElementById('pendingTasks').innerHTML = pending.length ? pending.map(i => taskCard(i, true)).join('') : `<div class="empty-state"><div class="empty-icon"><i class="fas fa-check-circle"></i></div><div class="empty-title">All caught up!</div></div>`;
  document.getElementById('completedTasks').innerHTML = completed.length ? completed.map(i => taskCard(i, false)).join('') : `<div class="empty-state"><div class="empty-icon"><i class="fas fa-inbox"></i></div><div class="empty-title">No completed tasks yet</div></div>`;
}

function openTask(taskId, classId) {
  window.location.href = `studentClassRoom.php?class_id=${encodeURIComponent(classId)}&task_id=${encodeURIComponent(taskId)}`;
}

/* â•â• NOTIFICATIONS â•â• */
function initNotifications() {
  document.getElementById('notifBtn').addEventListener('click', e => {
    e.stopPropagation();
    const panel = document.getElementById('notifPanel');
    panel.classList.toggle('show');
    if (panel.classList.contains('show')) loadNotifications();
  });
  document.addEventListener('click', e => {
    const panel = document.getElementById('notifPanel');
    if (!panel.contains(e.target) && !document.getElementById('notifBtn').contains(e.target))
      panel.classList.remove('show');
  });
}

async function loadNotifications() {
  try {
    const res  = await fetch('API/student/get_student_notifications.php');
    const data = await res.json();
    notifications = data.notifications || [];
    renderNotifications(notifications);
    const unread = notifications.filter(n => !n.is_read || n.type === 'invitation' || n.type === 'class_invitation').length;
    document.getElementById('notifDot').style.display = unread ? 'block' : 'none';
  } catch { document.getElementById('notifList').innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.82rem;">Could not load notifications.</div>'; }
}

function renderNotifications(list) {
  const el = document.getElementById('notifList');
  if (!list.length) {
    el.innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.82rem;">No notifications yet.</div>';
    return;
  }
  el.innerHTML = list.slice(0, 10).map(n => {
    if (n.type === 'invitation' || n.type === 'class_invitation') {
      return `
        <div class="notif-item unread" id="invite_${esc(n.class_id)}" style="flex-direction:column;align-items:stretch;gap:.5rem;cursor:default;">
          <div style="display:flex;align-items:flex-start;gap:.65rem;">
            <div class="notif-dot2"></div>
            <div style="flex:1;">
              <div style="display:flex;align-items:center;gap:.35rem;margin-bottom:.2rem;">
                <span style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
                             padding:.12rem .45rem;border-radius:20px;background:#e8f0fe;color:#1f73db;
                             border:1px solid rgba(31,115,219,.25);">
                  <i class="fas fa-graduation-cap" style="font-size:.58rem;"></i> Class Invitation
                </span>
              </div>
              <div class="notif-text" style="font-weight:600;">${esc(n.class_name)}</div>
              <div class="notif-text" style="color:var(--text-muted);">by ${esc(n.faculty_name || 'Professor')}</div>
              ${n.class_semester ? `<div class="notif-text" style="color:var(--text-muted);font-size:.72rem;">${esc(n.class_semester)}</div>` : ''}
              <div class="notif-ts">${relTime(n.created_at)}</div>
            </div>
          </div>
          <div style="display:flex;gap:.5rem;padding-left:1.3rem;">
            <button onclick="respondInvitation('${esc(n.class_id)}','accept')"
              style="flex:1;padding:.38rem .5rem;border-radius:8px;border:none;
                     background:linear-gradient(135deg,var(--primary-dark),var(--primary));
                     color:#fff;font-size:.78rem;font-weight:700;font-family:inherit;
                     cursor:pointer;transition:opacity .2s;"
              onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
              <i class="fas fa-check" style="font-size:.7rem;"></i> Accept
            </button>
            <button onclick="respondInvitation('${esc(n.class_id)}','decline')"
              style="flex:1;padding:.38rem .5rem;border-radius:8px;
                     border:1.5px solid var(--border);background:var(--bg);
                     color:var(--text-muted);font-size:.78rem;font-weight:700;
                     font-family:inherit;cursor:pointer;transition:all .2s;"
              onmouseover="this.style.borderColor='var(--danger)';this.style.color='var(--danger)'"
              onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
              <i class="fas fa-times" style="font-size:.7rem;"></i> Decline
            </button>
          </div>
        </div>`;
    }
    return `
      <div class="notif-item ${n.is_read ? '' : 'unread'}">
        <div class="notif-dot2 ${n.is_read ? 'read' : ''}"></div>
        <div>
          <div class="notif-text">${esc(n.message || n.title || '')}</div>
          <div class="notif-ts">${relTime(n.created_at)}</div>
        </div>
      </div>`;
  }).join('');
}

async function respondInvitation(classId, action) {
  const card = document.getElementById('invite_' + classId);
  const btns = card ? card.querySelectorAll('button') : [];
  btns.forEach(b => { b.disabled = true; b.style.opacity = '.5'; });

  try {
    const res  = await fetch('API/student/respond_invitation.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ class_id: classId, action })
    });
    const data = await res.json();
    if (data.status === 'success') {
      toast(data.message, 'success');
      // Remove this invite card from the panel
      if (card) {
        card.style.transition = 'opacity .25s, max-height .3s';
        card.style.opacity = '0';
        card.style.overflow = 'hidden';
        card.style.maxHeight = card.offsetHeight + 'px';
        setTimeout(() => { card.style.maxHeight = '0'; card.style.padding = '0'; }, 50);
        setTimeout(() => { card.remove(); refreshNotifDot(); }, 380);
      }
      // If accepted, reload class list so the new class appears
      if (action === 'accept') {
        setTimeout(() => loadClasses(), 500);
      }
    } else {
      toast(data.message || 'Something went wrong.', 'error');
      btns.forEach(b => { b.disabled = false; b.style.opacity = '1'; });
    }
  } catch (e) {
    toast('Network error', 'error');
    btns.forEach(b => { b.disabled = false; b.style.opacity = '1'; });
  }
}

function refreshNotifDot() {
  const remaining = document.querySelectorAll('#notifList .notif-item.unread').length;
  document.getElementById('notifDot').style.display = remaining ? 'block' : 'none';
}

async function markAllRead() {
  try {
    await fetch('API/student/mark_notifications_read.php', { method: 'POST' });
    notifications.forEach(n => n.is_read = 1);
    renderNotifications(notifications);
    document.getElementById('notifDot').style.display = 'none';
  } catch {}
}

/* â•â• NAVIGATION â•â• */
function initNav() {
  document.querySelectorAll('.nav-item[data-page]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
      document.querySelector(`.page[data-page="${btn.dataset.page}"]`).classList.add('active');
      if (btn.dataset.page === 'classes') loadMeetForCards();
      if (btn.dataset.page === 'calendar') loadCalendar();
      if (window.innerWidth <= 768) closeMobileSidebar();
    });
  });
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
    });
  });
  // Notification toggle visual
  const nt = document.getElementById('notifToggle');
  const ns = document.getElementById('notifSlider');
  const nk = document.getElementById('notifKnob');
  if (nt) nt.addEventListener('change', () => {
    ns.style.background = nt.checked ? 'var(--primary)' : 'var(--border)';
    nk.style.transform  = nt.checked ? 'translateX(17px)' : 'translateX(0)';
  });
}
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

/* â•â• CALENDAR â•â• */
let calCursor = new Date();
let calEvents = [];
let calSelectedKey = null;
const CAL_MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const _calEsc = s => String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

function calKey(d) { return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); }
function calClassifyType(t) {
  const s = String(t||'').toLowerCase();
  if (s.includes('quiz')) return 'quiz';
  if (s.includes('exam')) return 'exam';
  if (s.includes('assign')) return 'assignment';
  if (s.includes('activity')) return 'activity';
  return 'other';
}
function calIcon(type) { return { quiz:'fas fa-question-circle', exam:'fas fa-file-signature', assignment:'fas fa-clipboard-list', activity:'fas fa-tasks', other:'fas fa-bookmark' }[type] || 'fas fa-bookmark'; }

function loadCalendar() { calCursor.setDate(1); fetchCalEvents(); }

function fetchCalEvents() {
  const y = calCursor.getFullYear(), m = calCursor.getMonth()+1;
  fetch('API/student/fetch_calendar_events.php?year='+y+'&month='+m, { credentials:'same-origin' })
    .then(r => r.json())
    .then(res => { calEvents = (res && res.status === 'success') ? (res.data || []) : []; renderCalendar(); })
    .catch(() => { calEvents = []; renderCalendar(); });
}

function renderCalendar() {
  const cur = calCursor;
  document.getElementById('calMonthLabel').textContent = CAL_MONTHS[cur.getMonth()] + ' ' + cur.getFullYear();
  const firstDow  = new Date(cur.getFullYear(), cur.getMonth(), 1).getDay();
  const daysInMo  = new Date(cur.getFullYear(), cur.getMonth()+1, 0).getDate();
  const daysInPrv = new Date(cur.getFullYear(), cur.getMonth(),   0).getDate();
  const todayKey  = calKey(new Date());

  const byDay = {};
  calEvents.forEach(e => { const k = (e.due_date||'').substring(0,10); if (k) (byDay[k] = byDay[k] || []).push(e); });

  let cells = '';
  for (let i = firstDow - 1; i >= 0; i--) {
    const dt = new Date(cur.getFullYear(), cur.getMonth()-1, daysInPrv - i);
    cells += calCellHtml(dt, true, byDay[calKey(dt)] || [], todayKey);
  }
  for (let d = 1; d <= daysInMo; d++) {
    const dt = new Date(cur.getFullYear(), cur.getMonth(), d);
    cells += calCellHtml(dt, false, byDay[calKey(dt)] || [], todayKey);
  }
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
  const k = calKey(dt), isToday = k === todayKey, isSel = k === calSelectedKey, max = 3;
  const pills = evts.slice(0, max).map(e => {
    const t = calClassifyType(e.post_type || e.type);
    return '<div class="cal-event-pill t-'+t+'" title="'+_calEsc(e.title||'')+'">'+_calEsc(e.title||'(untitled)')+'</div>';
  }).join('');
  const more = evts.length > max ? '<div class="cal-event-more">+'+(evts.length-max)+' more</div>' : '';
  return '<div class="cal-day'+(otherMonth?' other-month':'')+(isToday?' today':'')+(isSel?' selected':'')+
         '" onclick="calSelectDay(\''+k+'\')">'+
         '<div class="cal-day-num">'+dt.getDate()+'</div>'+
         '<div class="cal-events">'+pills+more+'</div></div>';
}

function calSelectDay(key) { calSelectedKey = key; renderCalendar(); renderCalDayDetail(key); }

function renderCalDayDetail(key) {
  const wrap = document.getElementById('calDayDetail');
  const list = document.getElementById('calDayDetailList');
  const ttl  = document.getElementById('calDayDetailTitle');
  const items = calEvents.filter(e => (e.due_date||'').substring(0,10) === key);
  const [y,m,d] = key.split('-').map(Number);
  const dt = new Date(y, m-1, d);
  ttl.textContent = CAL_MONTHS[dt.getMonth()] + ' ' + dt.getDate() + ', ' + dt.getFullYear();
  wrap.style.display = '';
  if (!items.length) { list.innerHTML = '<div class="cal-detail-empty"><i class="fas fa-inbox me-2"></i>Nothing due today — enjoy the breather!</div>'; return; }

  const today0 = new Date(); today0.setHours(0,0,0,0);
  list.innerHTML = items.map(e => {
    const t = calClassifyType(e.post_type || e.type);
    const time = (e.due_date||'').length > 10 ? (e.due_date.substring(11,16)) : '';
    const c = { quiz:'#d93025', activity:'#1f73db', exam:'#7b1fa2', assignment:'#f59e0b', other:'#1a9e78' }[t];
    const due = new Date(e.due_date.replace(' ','T'));
    const diffDays = Math.floor((due - today0) / 86400000);
    let cd = '';
    if (diffDays < 0)       cd = '<span class="cal-detail-countdown cd-overdue">Overdue</span>';
    else if (diffDays === 0)cd = '<span class="cal-detail-countdown cd-today">Due today</span>';
    else if (diffDays <= 3) cd = '<span class="cal-detail-countdown cd-soon">In '+diffDays+' day'+(diffDays>1?'s':'')+'</span>';
    return '<div class="cal-detail-item">'+
      '<div class="cal-detail-icon" style="background:'+c+'22;color:'+c+';"><i class="'+calIcon(t)+'"></i></div>'+
      '<div class="cal-detail-meta">'+
        '<div class="cal-detail-name">'+_calEsc(e.title||'(untitled)')+cd+'</div>'+
        '<div class="cal-detail-sub">'+
          '<span style="text-transform:capitalize;font-weight:700;color:'+c+';">'+_calEsc(t)+'</span>'+
          (e.subject_name ? ' · '+_calEsc(e.subject_name) : '')+
          (e.faculty_name ? ' · by '+_calEsc(e.faculty_name) : '')+
        '</div>'+
      '</div>'+
      (time ? '<div class="cal-detail-time"><i class="fas fa-clock me-1"></i>'+_calEsc(time)+'</div>' : '')+
    '</div>';
  }).join('');
}

function calPrevMonth() { calCursor = new Date(calCursor.getFullYear(), calCursor.getMonth()-1, 1); fetchCalEvents(); }
function calNextMonth() { calCursor = new Date(calCursor.getFullYear(), calCursor.getMonth()+1, 1); fetchCalEvents(); }
function calGoToday()   { const t = new Date(); calCursor = new Date(t.getFullYear(), t.getMonth(), 1); calSelectedKey = calKey(t); fetchCalEvents(); }

</script>
</body>
</html>



