<?php
/* analytics.php — Tere LEARN | Admin Dashboard */
session_start();
require_once dirname(__DIR__, 2) . '/config/db_connect.php';

// Real stats from DB
$stu = $conn->query("SELECT COUNT(*) c FROM tblstudent WHERE is_deleted=0 AND is_active=1")->fetch_assoc()['c'];
$fac = $conn->query("SELECT COUNT(*) c FROM tblfaculty WHERE is_deleted=0 AND is_active=1 AND is_dean=0")->fetch_assoc()['c'];
$cls = $conn->query("SELECT COUNT(*) c FROM tblclass   WHERE is_deleted=0 AND is_active=1")->fetch_assoc()['c'];
$sub = $conn->query("SELECT COUNT(*) c FROM tblsubject  WHERE is_deleted=0 AND is_active=1")->fetch_assoc()['c'];
$crs = $conn->query("SELECT COUNT(*) c FROM tblcourse   WHERE is_deleted=0")->fetch_assoc()['c'];
$dep = $conn->query("SELECT COUNT(*) c FROM tbldepartment WHERE is_active=1")->fetch_assoc()['c'];

$deptProgramRows = [];
$deptProgramRes = $conn->query("
    SELECT
        d.id          AS department_id,
        d.dept_code   AS department_code,
        d.dept_name   AS department_name,
        c.id          AS program_id,
        c.course_code AS program_code,
        c.course_name AS program_name,
        COUNT(st.id)  AS student_count
    FROM tbldepartment d
    LEFT JOIN tblcourse c
           ON c.department_id = d.id
          AND c.is_deleted = 0
    LEFT JOIN tblstudent st
           ON st.course_id = c.id
          AND st.is_deleted = 0
          AND st.is_active = 1
    WHERE d.is_active = 1
      AND d.is_deleted = 0
    GROUP BY d.id, d.dept_code, d.dept_name, c.id, c.course_code, c.course_name
    ORDER BY d.dept_name ASC, c.course_code ASC
");
if ($deptProgramRes) {
    while ($row = $deptProgramRes->fetch_assoc()) $deptProgramRows[] = $row;
}

$departmentAnalytics = [];
foreach ($deptProgramRows as $row) {
    $deptId = (string)($row['department_id'] ?? '');
    if ($deptId === '') continue;
    if (!isset($departmentAnalytics[$deptId])) {
        $departmentAnalytics[$deptId] = [
            'id' => $deptId,
            'code' => $row['department_code'] ?: '—',
            'name' => $row['department_name'] ?: 'Unassigned Department',
            'total_students' => 0,
            'programs' => [],
        ];
    }

    $count = (int)($row['student_count'] ?? 0);
    $departmentAnalytics[$deptId]['total_students'] += $count;

    if (!empty($row['program_id'])) {
        $departmentAnalytics[$deptId]['programs'][] = [
            'id' => (string)$row['program_id'],
            'code' => $row['program_code'] ?: '—',
            'name' => $row['program_name'] ?: 'Unnamed Program',
            'student_count' => $count,
        ];
    }
}

foreach ($departmentAnalytics as &$deptRow) {
    usort($deptRow['programs'], static function ($a, $b) {
        $countCompare = $b['student_count'] <=> $a['student_count'];
        if ($countCompare !== 0) return $countCompare;
        return strcmp($a['code'], $b['code']);
    });
}
unset($deptRow);

$departmentAnalytics = array_values($departmentAnalytics);
usort($departmentAnalytics, static function ($a, $b) {
    $countCompare = $b['total_students'] <=> $a['total_students'];
    if ($countCompare !== 0) return $countCompare;
    return strcmp($a['name'], $b['name']);
});

$topDepartment = $departmentAnalytics[0] ?? null;

// Recent faculty (last 5)
$recentFac = [];
$rf = $conn->query("SELECT first_name,last_name,email,created_at FROM tblfaculty WHERE is_deleted=0 AND is_dean=0 ORDER BY created_at DESC LIMIT 5");
if ($rf) while ($r=$rf->fetch_assoc()) $recentFac[]=$r;

// Recent students (last 5)
$recentStu = [];
$rs = $conn->query("SELECT first_name,last_name,email,created_at FROM tblstudent WHERE is_deleted=0 ORDER BY created_at DESC LIMIT 5");
if ($rs) while ($r=$rs->fetch_assoc()) $recentStu[]=$r;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>
(function(){
  var K='tl_dark';
  if(localStorage.getItem(K)==='1') document.documentElement.classList.add('dark-mode');
  document.addEventListener('DOMContentLoaded',function(){
    var b=document.body;
    if(localStorage.getItem(K)==='1') b.classList.add('dark-mode');
    var btn=document.getElementById('dark-mode-toggle');
    if(!btn) return;
    var ic=btn.querySelector('i');
    if(ic) ic.className=localStorage.getItem(K)==='1'?'fas fa-sun':'fas fa-lightbulb';
    btn.addEventListener('click',function(e){
      e.preventDefault(); e.stopImmediatePropagation();
      var on=!b.classList.contains('dark-mode');
      b.classList.toggle('dark-mode',on);
      document.documentElement.classList.toggle('dark-mode',on);
      if(ic) ic.className=on?'fas fa-sun':'fas fa-lightbulb';
      localStorage.setItem(K,on?'1':'0');
    },true);
  });
})();
</script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>TERELEARN | Admin Dashboard</title>
<style>a{text-decoration:none!important;}</style>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="dist/css/adminlte.min.css">
<link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<style>
/* ══ ROOT ══ */
:root{
  --sb-w:240px;--sb-mini-w:56px;--sb-bg:#1e2840;
  --sb-border:rgba(255,255,255,.06);--sb-text:rgba(255,255,255,.55);
  --sb-hover:rgba(255,255,255,.07);--sb-accent:#4ade80;
  --nav-h:50px;--sb-trans:.2s cubic-bezier(.4,0,.2,1);
  --green:#1e8449;--green-dark:#134e2e;
}

/* ══ KILL ADMINLTE SIDEBAR/HEADER ══ */
.main-sidebar,.main-header{display:none!important;}

/* ══ SIDEBAR ══ */
#tlSidebar{position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);background:var(--sb-bg);display:flex;flex-direction:column;z-index:1050;transition:width var(--sb-trans);overflow:hidden;box-shadow:2px 0 16px rgba(0,0,0,.22);}
#tlSidebar.collapsed{width:var(--sb-mini-w);}
.sb-brand{display:flex;align-items:center;gap:.65rem;padding:0 .85rem;height:var(--nav-h);border-bottom:1px solid var(--sb-border);flex-shrink:0;overflow:hidden;}
.sb-brand img{width:26px;height:26px;object-fit:contain;flex-shrink:0;}
.sb-brand-text{font-size:.88rem;font-weight:700;color:#fff;white-space:nowrap;letter-spacing:.3px;transition:opacity var(--sb-trans),width var(--sb-trans);}
#tlSidebar.collapsed .sb-brand-text{opacity:0;width:0;}
.sb-user{display:flex;align-items:center;gap:.6rem;padding:.65rem .85rem;border-bottom:1px solid var(--sb-border);flex-shrink:0;overflow:hidden;}
.sb-av{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#b45309,#f59e0b);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.62rem;font-weight:700;flex-shrink:0;}
.sb-user-info{overflow:hidden;transition:opacity var(--sb-trans),width var(--sb-trans);}
#tlSidebar.collapsed .sb-user-info{opacity:0;width:0;}
.sb-user-name{font-size:.76rem;font-weight:600;color:rgba(255,255,255,.82);white-space:nowrap;}
.sb-user-role{font-size:.63rem;color:rgba(255,255,255,.32);white-space:nowrap;}
.sb-nav{flex:1;overflow-y:auto;overflow-x:hidden;padding:.4rem 0;}
.sb-nav::-webkit-scrollbar{width:3px;}
.sb-nav::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:2px;}
.sb-section{font-size:.59rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.22);padding:.65rem .85rem .15rem;white-space:nowrap;transition:opacity var(--sb-trans);}
#tlSidebar.collapsed .sb-section{opacity:0;}
.sb-item{position:relative;}
.sb-link{display:flex;align-items:center;gap:.55rem;padding:.45rem .8rem;margin:1px .35rem;border-radius:7px;color:var(--sb-text);font-size:.8rem;font-weight:500;border-left:2.5px solid transparent;white-space:nowrap;overflow:hidden;cursor:pointer;user-select:none;transition:background var(--sb-trans),color var(--sb-trans),border-color var(--sb-trans),transform var(--sb-trans);}
.sb-link:hover{background:var(--sb-hover);color:rgba(255,255,255,.88);transform:translateX(2px);}
.sb-link.active{background:rgba(74,222,128,.08);color:rgba(255,255,255,.95);font-weight:600;border-left-color:var(--sb-accent);transform:translateX(2px);}
.sb-link i.sb-icon{width:16px;text-align:center;font-size:.78rem;flex-shrink:0;opacity:.6;transition:opacity var(--sb-trans);}
.sb-link:hover i.sb-icon,.sb-link.active i.sb-icon{opacity:1;}
.sb-link.active i.sb-icon{color:var(--sb-accent);}
.sb-label{flex:1;transition:opacity var(--sb-trans),width var(--sb-trans);}
#tlSidebar.collapsed .sb-label{opacity:0;width:0;}
#tlSidebar.collapsed .sb-link{justify-content:center;padding:.45rem;border-left-color:transparent!important;transform:none!important;}
.sb-arrow{font-size:.58rem;opacity:.35;margin-left:auto;flex-shrink:0;transition:transform var(--sb-trans),opacity var(--sb-trans);}
.sb-item.open>.sb-link .sb-arrow{transform:rotate(90deg);opacity:.65;}
#tlSidebar.collapsed .sb-arrow{display:none;}
.sb-sub{overflow:hidden;max-height:0;transition:max-height .22s ease;}
.sb-item.open>.sb-sub{max-height:300px;}
#tlSidebar.collapsed .sb-sub{max-height:0!important;}
.sb-sub .sb-link{padding:.36rem .8rem .36rem 2.2rem;font-size:.76rem;border-left-color:transparent;}
.sb-sub .sb-link::before{content:'';width:4px;height:4px;border-radius:50%;background:rgba(255,255,255,.18);flex-shrink:0;margin-right:.45rem;transition:background var(--sb-trans);}
.sb-sub .sb-link:hover::before{background:rgba(255,255,255,.45);}
.sb-sub .sb-link.active::before{background:var(--sb-accent);}
#tlSidebar.collapsed .sb-item:hover .sb-link::after{content:attr(data-tip);position:absolute;left:calc(var(--sb-mini-w) + 6px);top:50%;transform:translateY(-50%);background:#111;color:#fff;font-size:.7rem;font-weight:600;padding:.28rem .6rem;border-radius:6px;white-space:nowrap;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,.3);pointer-events:none;}
#tlOverlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1049;}
@media(max-width:767px){
  #tlSidebar{transform:translateX(-100%);transition:transform var(--sb-trans);width:var(--sb-w)!important;}
  body.sb-open #tlSidebar{transform:translateX(0);}
  body.sb-open #tlOverlay{display:block;}
}

/* ══ TOPNAV ══ */
.tl-topnav{position:fixed;top:0;left:var(--sb-w);right:0;height:var(--nav-h);background:#fff;border-bottom:1px solid #e8ecf0;display:flex;align-items:center;gap:.6rem;padding:0 1.1rem;z-index:1055;box-shadow:0 1px 6px rgba(0,0,0,.05);transition:left var(--sb-trans),background .2s,border-color .2s;}
body.sb-collapsed .tl-topnav{left:var(--sb-mini-w);}
.dark-mode .tl-topnav{background:#1a2232;border-bottom-color:#2e3849;}
#tlMenuToggle{width:32px;height:32px;border:none;background:transparent;cursor:pointer;border-radius:7px;flex-shrink:0;display:flex;flex-direction:column;justify-content:center;align-items:center;gap:4px;transition:background .15s;}
#tlMenuToggle:hover{background:#f0f1f3;}
.dark-mode #tlMenuToggle:hover{background:rgba(255,255,255,.08);}
#tlMenuToggle span{display:block;width:16px;height:2px;background:#555;border-radius:2px;transition:transform .2s,opacity .2s,width .2s;transform-origin:center;}
.dark-mode #tlMenuToggle span{background:#94a3b8!important;}
body.sb-open #tlMenuToggle span:nth-child(1){transform:translateY(6px) rotate(45deg);}
body.sb-open #tlMenuToggle span:nth-child(2){opacity:0;width:0;}
body.sb-open #tlMenuToggle span:nth-child(3){transform:translateY(-6px) rotate(-45deg);}
body.sb-collapsed #tlMenuToggle span:nth-child(2){width:10px;}
.tl-nav-right{margin-left:auto;display:flex;align-items:center;gap:.3rem;}
.tl-nav-icon{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;color:#6c757d;font-size:.85rem;transition:background .15s,color .15s;}
.tl-nav-icon:hover{background:#f0f1f3;color:#1a2234;}
.dark-mode .tl-nav-icon{color:#8a9ab5;}
.dark-mode .tl-nav-icon:hover{background:rgba(255,255,255,.08);color:#e2e8f0;}

/* ══ LAYOUT ══ */
.content-wrapper,.main-footer{margin-left:var(--sb-w)!important;margin-top:var(--nav-h)!important;transition:margin-left var(--sb-trans)!important;}
body.sb-collapsed .content-wrapper,body.sb-collapsed .main-footer{margin-left:var(--sb-mini-w)!important;}
@media(max-width:767px){.tl-topnav{left:0!important;}.content-wrapper,.main-footer{margin-left:0!important;margin-top:var(--nav-h)!important;}}

/* ══ DARK MODE ══ */
body.dark-mode{background:#111827!important;color:#e2e8f0!important;}
body.dark-mode #tlSidebar{background:#1e2840!important;}
body.dark-mode .tl-topnav{background:#1a2232!important;border-bottom-color:#2a3447!important;}
body.dark-mode .content-wrapper{background:#111827!important;}
body.dark-mode .main-footer{background:#1a2232!important;border-top-color:#2a3447!important;color:#8a9ab5!important;}
body.dark-mode .bg-white,.dark-mode .card{background:#1e2838!important;border-color:#2e3849!important;}
body.dark-mode h1,body.dark-mode h2,body.dark-mode h3{color:#e2e8f0!important;}
body.dark-mode .form-control{background:#2b3443!important;border-color:#3d4a5c!important;color:#e2e8f0!important;}
body.dark-mode .table{color:#e2e8f0!important;}
body.dark-mode .shadow-lg{box-shadow:0 4px 16px rgba(0,0,0,.4)!important;}
body.dark-mode #dark-mode-toggle i::before{content:"\f185";}

/* ══ STAT CARDS ══ */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.9rem;margin-bottom:1.5rem;}
.stat-card{background:#fff;border:1px solid #e8ecf0;border-radius:12px;padding:1rem 1.15rem;display:flex;align-items:center;gap:.8rem;box-shadow:0 2px 8px rgba(0,0,0,.05);transition:transform .15s,box-shadow .15s;}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 5px 16px rgba(0,0,0,.09);}
.dark-mode .stat-card{background:#1e2838!important;border-color:#2e3849!important;}
.stat-ic{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;}
.si-g{background:#d1fae5;color:#065f46;}.si-b{background:#dbeafe;color:#1e40af;}
.si-o{background:#fef3c7;color:#92400e;}.si-p{background:#ede9fe;color:#5b21b6;}
.si-t{background:#e0f7fa;color:#00838f;}.si-r{background:#fce4ec;color:#c62828;}
.stat-num{font-size:1.8rem;font-weight:700;line-height:1;color:#1e2840;}
.dark-mode .stat-num{color:#e2e8f0!important;}
.stat-lbl{font-size:.7rem;color:#6c757d;margin-top:.12rem;}

/* ══ TABLE ══ */
.tl-th{background:#2d3a4a;color:#fff;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .85rem;white-space:nowrap;vertical-align:middle;border:none;}
.tl-th:first-child{border-radius:8px 0 0 0;}.tl-th:last-child{border-radius:0 8px 0 0;}
.tl-td{padding:.6rem .85rem;vertical-align:middle;border-bottom:1px solid #eef0f5;font-size:.82rem;}
.dark-mode .tl-td{border-bottom-color:#2e3849!important;}
.tl-tr:hover{background:#f0f5ff!important;}.dark-mode .tl-tr:hover{background:#2a3547!important;}
.tl-tr:nth-child(even){background:#fafbfd;}.dark-mode .tl-tr:nth-child(even){background:#1e2838!important;}

/* ══ QUICK NAV CARDS ══ */
.qnav-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.85rem;margin-bottom:1.5rem;}
.qnav-card{background:#fff;border:1px solid #e8ecf0;border-radius:12px;padding:1.1rem .9rem;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.05);cursor:pointer;transition:transform .15s,box-shadow .15s,border-color .15s;}
.qnav-card:hover{transform:translateY(-3px);box-shadow:0 6px 18px rgba(0,0,0,.1);border-color:#1e8449;}
.dark-mode .qnav-card{background:#1e2838!important;border-color:#2e3849!important;}
.dark-mode .qnav-card:hover{border-color:#4ade80!important;}
.qnav-ic{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1rem;margin:0 auto .55rem;}
.qnav-lbl{font-size:.78rem;font-weight:700;color:#1e2840;}
.dark-mode .qnav-lbl{color:#e2e8f0!important;}
.qnav-sub{font-size:.67rem;color:#6c757d;margin-top:.1rem;}

/* ════ ANALYTICS ════ */
.analytics-card{background:#fff;border:1px solid #e8ecf0;border-radius:16px;padding:1.1rem 1.1rem 1rem;box-shadow:0 2px 10px rgba(15,23,42,.05);height:100%;}
.dark-mode .analytics-card{background:#1e2838!important;border-color:#2e3849!important;}
.analytics-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;}
.analytics-title{font-size:1rem;font-weight:800;color:#1e2840;line-height:1.2;}
.dark-mode .analytics-title{color:#e2e8f0!important;}
.analytics-sub{font-size:.74rem;color:#6c757d;margin-top:.18rem;max-width:44ch;}
.analytics-filter{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap;}
.analytics-filter label{font-size:.72rem;font-weight:700;color:#6c757d;text-transform:uppercase;letter-spacing:.08em;}
.analytics-select{min-width:220px;border:1px solid #d7dee6;border-radius:10px;padding:.5rem .8rem;font-size:.82rem;font-weight:600;background:#fff;color:#1f2937;outline:none;}
.dark-mode .analytics-select{background:#111827;border-color:#334155;color:#e2e8f0;}
.analytics-canvas-wrap{position:relative;height:320px;}
.analytics-meta-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem;margin-top:1rem;}
.analytics-meta{background:#f8fafc;border:1px solid #e6edf5;border-radius:12px;padding:.8rem .9rem;}
.dark-mode .analytics-meta{background:#16202d;border-color:#2e3849;}
.analytics-meta-label{font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6c757d;margin-bottom:.25rem;}
.analytics-meta-value{font-size:1.2rem;font-weight:800;color:#1e2840;line-height:1.1;}
.dark-mode .analytics-meta-value{color:#e2e8f0!important;}
.analytics-meta-note{font-size:.74rem;color:#6c757d;margin-top:.18rem;}
.analytics-program-list{display:grid;gap:.7rem;margin-top:1rem;}
.analytics-program-item{display:flex;align-items:center;justify-content:space-between;gap:.9rem;padding:.8rem .9rem;border:1px solid #e8ecf0;border-radius:12px;background:#fbfdff;}
.dark-mode .analytics-program-item{background:#16202d;border-color:#2e3849;}
.analytics-program-main{min-width:0;}
.analytics-program-code{display:inline-flex;align-items:center;padding:.18rem .48rem;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-size:.68rem;font-weight:800;margin-bottom:.35rem;}
.analytics-program-name{font-size:.84rem;font-weight:700;color:#1f2937;line-height:1.25;}
.dark-mode .analytics-program-name{color:#e2e8f0!important;}
.analytics-program-count{font-size:1.08rem;font-weight:800;color:#16a34a;white-space:nowrap;}
.analytics-empty{padding:2rem 1rem;text-align:center;color:#6c757d;font-size:.82rem;border:1px dashed #d7dee6;border-radius:12px;background:#fbfdff;}
.dark-mode .analytics-empty{background:#16202d;border-color:#334155;}
@media(max-width:767px){
  .analytics-canvas-wrap{height:280px;}
  .analytics-meta-grid{grid-template-columns:1fr;}
  .analytics-select{min-width:100%;}
}
</style>
</head>

<div id="tlOverlay"></div>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<!-- ══ TOPNAV ══ -->
<nav class="tl-topnav">
  <button id="tlMenuToggle" title="Toggle sidebar"><span></span><span></span><span></span></button>
  <span style="font-size:.82rem;font-weight:600;color:#1e8449;margin-left:.3rem;">
    <i class="fas fa-layer-group me-1"></i>Admin Dashboard
  </span>
  <div class="tl-nav-right">
    <a class="tl-nav-icon" id="dark-mode-toggle" href="#" title="Theme"><i class="fas fa-lightbulb"></i></a>
    <a class="tl-nav-icon" data-widget="fullscreen" href="#" title="Fullscreen"><i class="fas fa-expand-arrows-alt"></i></a>
    <a class="tl-nav-icon" href="admin.php" title="Super Admin"><i class="fas fa-shield-alt" style="color:#1e8449;"></i></a>
    <a class="tl-nav-icon" href="signin.php" title="Sign Out" onclick="return confirm('Sign out?')"><i class="fas fa-sign-out-alt" style="color:#dc3545;"></i></a>
  </div>
</nav>

<!-- ══ SIDEBAR ══ -->
<aside id="tlSidebar">
  <a href="analytics.php" class="sb-brand">
    <img src="dist/img/terelearn.png" alt="Logo">
    <span class="sb-brand-text">TERE LEARN</span>
  </a>
  <div class="sb-user">
    <div class="sb-av">A</div>
    <div class="sb-user-info">
      <div class="sb-user-name">Administrator</div>
      <div class="sb-user-role">Super Admin</div>
    </div>
  </div>
  <nav class="sb-nav">
    <div class="sb-section">Main</div>
    <div class="sb-item">
      <a href="analytics.php" class="sb-link active" data-tip="Dashboard">
        <i class="fas fa-layer-group sb-icon"></i>
        <span class="sb-label">Dashboard</span>
      </a>
    </div>
    <div class="sb-item">
      <a href="admin.php" class="sb-link" data-tip="Super Admin Panel">
        <i class="fas fa-shield-alt sb-icon"></i>
        <span class="sb-label">Super Admin Panel</span>
      </a>
    </div>
    <div class="sb-section">Management</div>
    <div class="sb-item open" id="sbManagement">
      <div class="sb-link" onclick="sbToggle('sbManagement')" data-tip="Management">
        <i class="fas fa-cogs sb-icon"></i>
        <span class="sb-label">Management</span>
        <i class="fas fa-chevron-right sb-arrow"></i>
      </div>
      <div class="sb-sub">
        <a href="faculty.php"        class="sb-link"><span class="sb-label">Faculty</span></a>
        <a href="facultyclass.php"   class="sb-link"><span class="sb-label">Classes</span></a>
        <a href="facultysubject.php" class="sb-link"><span class="sb-label">Subjects</span></a>
      </div>
    </div>
    <div class="sb-item" id="sbStudents">
      <div class="sb-link" onclick="sbToggle('sbStudents')" data-tip="Students">
        <i class="fas fa-user-graduate sb-icon"></i>
        <span class="sb-label">Students</span>
        <i class="fas fa-chevron-right sb-arrow"></i>
      </div>
      <div class="sb-sub">
        <a href="student.php" class="sb-link"><span class="sb-label">Manage Accounts</span></a>
      </div>
    </div>
  </nav>
</aside>

<!-- ══ CONTENT ══ -->
<div class="content-wrapper bg-muted">
  <div class="content-header"><div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1 class="m-0">Dashboard</h1></div>
      <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item active">Dashboard</li></ol></div>
    </div>
  </div></div>

  <section class="content"><div class="container-fluid">

    <!-- Stat cards -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-ic si-b"><i class="fas fa-user-graduate"></i></div>
        <div><div class="stat-num"><?= number_format($stu) ?></div><div class="stat-lbl">Students</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-ic si-g"><i class="fas fa-chalkboard-teacher"></i></div>
        <div><div class="stat-num"><?= number_format($fac) ?></div><div class="stat-lbl">Faculty</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-ic si-o"><i class="fas fa-chalkboard"></i></div>
        <div><div class="stat-num"><?= number_format($cls) ?></div><div class="stat-lbl">Classes</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-ic si-p"><i class="fas fa-book"></i></div>
        <div><div class="stat-num"><?= number_format($sub) ?></div><div class="stat-lbl">Subjects</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-ic si-t"><i class="fas fa-graduation-cap"></i></div>
        <div><div class="stat-num"><?= number_format($crs) ?></div><div class="stat-lbl">Programs</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-ic si-r"><i class="fas fa-university"></i></div>
        <div><div class="stat-num"><?= number_format($dep) ?></div><div class="stat-lbl">Departments</div></div>
      </div>
    </div>

    <div class="row p-1 mb-2">
      <div class="col-12">
        <div class="bg-white shadow-lg p-3 border border-muted rounded">
          <div class="analytics-head">
            <div>
              <div class="analytics-title"><i class="fas fa-chart-bar me-2 text-success"></i>Student Distribution Analytics</div>
              <div class="analytics-sub">Monitor how many students each department has, then drill down into the programs under that department.</div>
            </div>
            <div class="analytics-filter">
              <label for="departmentAnalyticsSelect">View Programs</label>
              <select id="departmentAnalyticsSelect" class="analytics-select">
                <?php foreach ($departmentAnalytics as $deptRow): ?>
                  <option value="<?= htmlspecialchars($deptRow['id']) ?>"><?= htmlspecialchars(($deptRow['code'] ?: '—') . ' - ' . $deptRow['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-lg-7">
              <div class="analytics-card">
                <div class="analytics-title">Students Per Department</div>
                <div class="analytics-sub">Click a bar to inspect that department’s program-level student counts.</div>
                <div class="analytics-canvas-wrap">
                  <canvas id="departmentStudentsChart"></canvas>
                </div>
              </div>
            </div>

            <div class="col-lg-5">
              <div class="analytics-card">
                <div class="analytics-head" style="margin-bottom:.7rem;">
                  <div>
                    <div class="analytics-title" id="programAnalyticsTitle">Programs in <?= htmlspecialchars($topDepartment['name'] ?? 'Department') ?></div>
                    <div class="analytics-sub" id="programAnalyticsSubtitle">Program-level student count inside the selected department.</div>
                  </div>
                </div>
                <div class="analytics-canvas-wrap" style="height:260px;">
                  <canvas id="programStudentsChart"></canvas>
                </div>
                <div class="analytics-meta-grid">
                  <div class="analytics-meta">
                    <div class="analytics-meta-label">Department Total</div>
                    <div class="analytics-meta-value" id="selectedDeptStudentTotal"><?= number_format((int)($topDepartment['total_students'] ?? 0)) ?></div>
                    <div class="analytics-meta-note">Active students in the selected department</div>
                  </div>
                  <div class="analytics-meta">
                    <div class="analytics-meta-label">Programs</div>
                    <div class="analytics-meta-value" id="selectedDeptProgramCount"><?= number_format(isset($topDepartment['programs']) ? count($topDepartment['programs']) : 0) ?></div>
                    <div class="analytics-meta-note">Programs linked to this department</div>
                  </div>
                  <div class="analytics-meta">
                    <div class="analytics-meta-label">Largest Program</div>
                    <div class="analytics-meta-value" id="selectedDeptTopProgram"><?= htmlspecialchars($topDepartment['programs'][0]['code'] ?? '—') ?></div>
                    <div class="analytics-meta-note" id="selectedDeptTopProgramCount"><?= isset($topDepartment['programs'][0]) ? number_format((int)$topDepartment['programs'][0]['student_count']) . ' students' : 'No program data yet' ?></div>
                  </div>
                </div>
                <div class="analytics-program-list" id="departmentProgramList"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick navigation -->
    <div class="row p-1 mb-2">
      <div class="col-12">
        <div class="bg-white shadow-lg p-3 border border-muted rounded">
          <h6 class="fw-bold mb-3"><i class="fas fa-bolt me-2 text-success"></i>Quick Navigation</h6>
          <div class="qnav-grid">
            <a href="faculty.php" class="qnav-card">
              <div class="qnav-ic si-g"><i class="fas fa-chalkboard-teacher"></i></div>
              <div class="qnav-lbl">Faculty</div>
              <div class="qnav-sub">Manage accounts</div>
            </a>
            <a href="facultyclass.php" class="qnav-card">
              <div class="qnav-ic si-o"><i class="fas fa-chalkboard"></i></div>
              <div class="qnav-lbl">Classes</div>
              <div class="qnav-sub">Manage classes</div>
            </a>
            <a href="facultysubject.php" class="qnav-card">
              <div class="qnav-ic si-p"><i class="fas fa-book"></i></div>
              <div class="qnav-lbl">Subjects</div>
              <div class="qnav-sub">Manage subjects</div>
            </a>
            <a href="student.php" class="qnav-card">
              <div class="qnav-ic si-b"><i class="fas fa-user-graduate"></i></div>
              <div class="qnav-lbl">Students</div>
              <div class="qnav-sub">Manage accounts</div>
            </a>
            <a href="admin.php" class="qnav-card">
              <div class="qnav-ic si-r"><i class="fas fa-shield-alt"></i></div>
              <div class="qnav-lbl">Super Admin</div>
              <div class="qnav-sub">Dean &amp; dept mgmt</div>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent activity tables -->
    <div class="row p-1">
      <!-- Recent Faculty -->
      <div class="col-lg-6 mb-3">
        <div class="bg-white shadow-lg p-3 border border-muted rounded h-100">
          <h6 class="fw-bold mb-3"><i class="fas fa-user-plus me-2 text-success"></i>Recently Added Faculty</h6>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead><tr>
                <th class="tl-th">Name</th>
                <th class="tl-th">Email</th>
                <th class="tl-th">Added</th>
              </tr></thead>
              <tbody>
                <?php if (empty($recentFac)): ?>
                  <tr><td colspan="3" class="text-center text-muted py-3">No faculty yet.</td></tr>
                <?php else: foreach($recentFac as $f): ?>
                  <tr class="tl-tr">
                    <td class="tl-td fw-semibold"><?= htmlspecialchars($f['first_name'].' '.$f['last_name']) ?></td>
                    <td class="tl-td" style="font-size:.76rem;color:#6c757d;"><?= htmlspecialchars($f['email']??'—') ?></td>
                    <td class="tl-td" style="font-size:.74rem;color:#6c757d;"><?= substr($f['created_at']??'',0,10) ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
          <div class="mt-2 text-end"><a href="faculty.php" class="btn btn-sm btn-outline-success" style="border-radius:7px;font-size:.76rem;">View All Faculty →</a></div>
        </div>
      </div>
      <!-- Recent Students -->
      <div class="col-lg-6 mb-3">
        <div class="bg-white shadow-lg p-3 border border-muted rounded h-100">
          <h6 class="fw-bold mb-3"><i class="fas fa-user-graduate me-2 text-success"></i>Recently Added Students</h6>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead><tr>
                <th class="tl-th">Name</th>
                <th class="tl-th">Email</th>
                <th class="tl-th">Added</th>
              </tr></thead>
              <tbody>
                <?php if (empty($recentStu)): ?>
                  <tr><td colspan="3" class="text-center text-muted py-3">No students yet.</td></tr>
                <?php else: foreach($recentStu as $s): ?>
                  <tr class="tl-tr">
                    <td class="tl-td fw-semibold"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
                    <td class="tl-td" style="font-size:.76rem;color:#6c757d;"><?= htmlspecialchars($s['email']??'—') ?></td>
                    <td class="tl-td" style="font-size:.74rem;color:#6c757d;"><?= substr($s['created_at']??'',0,10) ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
          <div class="mt-2 text-end"><a href="student.php" class="btn btn-sm btn-outline-success" style="border-radius:7px;font-size:.76rem;">View All Students →</a></div>
        </div>
      </div>
    </div>

  </div></section>
</div>

<footer class="main-footer">
  <strong>Copyright &copy; 2025-2026 <a href="#">TERE LEARN</a>.</strong> All rights reserved.
  <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0.0</div>
</footer>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="dist/js/adminlte.js"></script>
<script>
(function(){
  var sb=document.getElementById('tlSidebar'),ov=document.getElementById('tlOverlay'),b=document.body,K='tl_sb';
  if(window.innerWidth>=768&&localStorage.getItem(K)==='c'){sb.classList.add('collapsed');b.classList.add('sb-collapsed');}
  document.getElementById('tlMenuToggle').addEventListener('click',function(){
    if(window.innerWidth<768){b.classList.toggle('sb-open');ov.style.display=b.classList.contains('sb-open')?'block':'none';}
    else{var c=sb.classList.toggle('collapsed');b.classList.toggle('sb-collapsed',c);localStorage.setItem(K,c?'c':'e');}
  });
  ov.addEventListener('click',function(){b.classList.remove('sb-open');ov.style.display='none';});
  window.addEventListener('resize',function(){if(window.innerWidth>=768)b.classList.remove('sb-open');});
})();
function sbToggle(id){document.getElementById(id).classList.toggle('open');}

const departmentAnalytics = <?= json_encode($departmentAnalytics, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const departmentSelect = document.getElementById('departmentAnalyticsSelect');
const programList = document.getElementById('departmentProgramList');
const programTitle = document.getElementById('programAnalyticsTitle');
const programSubtitle = document.getElementById('programAnalyticsSubtitle');
const selectedDeptStudentTotal = document.getElementById('selectedDeptStudentTotal');
const selectedDeptProgramCount = document.getElementById('selectedDeptProgramCount');
const selectedDeptTopProgram = document.getElementById('selectedDeptTopProgram');
const selectedDeptTopProgramCount = document.getElementById('selectedDeptTopProgramCount');

const deptLabels = departmentAnalytics.map(d => d.code ? `${d.code}` : d.name);
const deptTotals = departmentAnalytics.map(d => Number(d.total_students || 0));
const deptBackground = departmentAnalytics.map((_, idx) => idx === 0 ? 'rgba(22, 163, 74, 0.85)' : 'rgba(34, 197, 94, 0.45)');
const deptBorder = departmentAnalytics.map((_, idx) => idx === 0 ? 'rgba(21, 128, 61, 1)' : 'rgba(22, 163, 74, 0.9)');

let activeDepartmentId = departmentSelect?.value || (departmentAnalytics[0]?.id ?? null);

const departmentChart = new Chart(document.getElementById('departmentStudentsChart'), {
  type: 'bar',
  data: {
    labels: deptLabels,
    datasets: [{
      label: 'Students',
      data: deptTotals,
      borderRadius: 10,
      borderSkipped: false,
      backgroundColor: deptBackground,
      borderColor: deptBorder,
      borderWidth: 1.5,
      maxBarThickness: 52
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
            const i = items[0]?.dataIndex ?? 0;
            const row = departmentAnalytics[i];
            return row ? `${row.code} - ${row.name}` : '';
          },
          label(ctx) {
            return `${ctx.raw} active students`;
          }
        }
      }
    },
    scales: {
      x: {
        beginAtZero: true,
        ticks: { precision: 0 },
        grid: { color: 'rgba(148, 163, 184, 0.18)' }
      },
      y: {
        grid: { display: false }
      }
    },
    onClick(_, elements) {
      if (!elements.length) return;
      const idx = elements[0].index;
      const dept = departmentAnalytics[idx];
      if (!dept) return;
      activeDepartmentId = dept.id;
      if (departmentSelect) departmentSelect.value = dept.id;
      updateDepartmentChartHighlight();
      renderProgramAnalytics(dept.id);
    }
  }
});

const programChart = new Chart(document.getElementById('programStudentsChart'), {
  type: 'bar',
  data: {
    labels: [],
    datasets: [{
      label: 'Students',
      data: [],
      backgroundColor: 'rgba(30, 132, 73, 0.82)',
      borderColor: 'rgba(20, 83, 45, 1)',
      borderWidth: 1.5,
      borderRadius: 10,
      borderSkipped: false,
      maxBarThickness: 48
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          title(items) {
            return items[0]?.label || '';
          },
          label(ctx) {
            return `${ctx.raw} active students`;
          }
        }
      }
    },
    scales: {
      x: {
        grid: { display: false }
      },
      y: {
        beginAtZero: true,
        ticks: { precision: 0 },
        grid: { color: 'rgba(148, 163, 184, 0.18)' }
      }
    }
  }
});

function updateDepartmentChartHighlight() {
  departmentChart.data.datasets[0].backgroundColor = departmentAnalytics.map(d =>
    d.id === activeDepartmentId ? 'rgba(22, 163, 74, 0.85)' : 'rgba(34, 197, 94, 0.45)'
  );
  departmentChart.data.datasets[0].borderColor = departmentAnalytics.map(d =>
    d.id === activeDepartmentId ? 'rgba(21, 128, 61, 1)' : 'rgba(22, 163, 74, 0.9)'
  );
  departmentChart.update();
}

function renderProgramAnalytics(departmentId) {
  const dept = departmentAnalytics.find(row => String(row.id) === String(departmentId)) || departmentAnalytics[0];
  if (!dept) return;

  activeDepartmentId = dept.id;
  programTitle.textContent = `Programs in ${dept.name}`;
  programSubtitle.textContent = `${dept.code} department student counts by program`;
  selectedDeptStudentTotal.textContent = new Intl.NumberFormat().format(Number(dept.total_students || 0));
  selectedDeptProgramCount.textContent = new Intl.NumberFormat().format((dept.programs || []).length);

  const topProgram = (dept.programs || [])[0] || null;
  selectedDeptTopProgram.textContent = topProgram ? topProgram.code : '—';
  selectedDeptTopProgramCount.textContent = topProgram ? `${new Intl.NumberFormat().format(Number(topProgram.student_count || 0))} students` : 'No program data yet';

  const labels = (dept.programs || []).map(program => program.code);
  const values = (dept.programs || []).map(program => Number(program.student_count || 0));
  programChart.data.labels = labels.length ? labels : ['No programs'];
  programChart.data.datasets[0].data = values.length ? values : [0];
  programChart.update();

  if (!(dept.programs || []).length) {
    programList.innerHTML = '<div class="analytics-empty">No programs are linked to this department yet.</div>';
  } else {
    programList.innerHTML = dept.programs.map(program => `
      <div class="analytics-program-item">
        <div class="analytics-program-main">
          <div class="analytics-program-code">${escapeHtml(program.code || '—')}</div>
          <div class="analytics-program-name">${escapeHtml(program.name || 'Unnamed Program')}</div>
        </div>
        <div class="analytics-program-count">${new Intl.NumberFormat().format(Number(program.student_count || 0))}</div>
      </div>
    `).join('');
  }
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

departmentSelect?.addEventListener('change', function() {
  activeDepartmentId = this.value;
  updateDepartmentChartHighlight();
  renderProgramAnalytics(this.value);
});

if (departmentAnalytics.length) {
  updateDepartmentChartHighlight();
  renderProgramAnalytics(activeDepartmentId);
} else if (programList) {
  programList.innerHTML = '<div class="analytics-empty">No department analytics data is available yet.</div>';
}
</script>
</body>
</html>
