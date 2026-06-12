<!DOCTYPE html>
<html lang="en">
<head>

<script>
/* ── Dark mode — standalone, runs before page JS ── */
(function() {
  var KEY = 'tl_dark';
  if (localStorage.getItem(KEY) === '1') document.documentElement.classList.add('dark-mode');
  document.addEventListener('DOMContentLoaded', function() {
    var body = document.body;
    if (localStorage.getItem(KEY) === '1') body.classList.add('dark-mode');
    var btn  = document.getElementById('dark-mode-toggle');
    if (!btn) return;
    var icon = btn.querySelector('i');
    if (icon) icon.className = localStorage.getItem(KEY) === '1' ? 'fas fa-sun' : 'fas fa-lightbulb';
    btn.addEventListener('click', function(e) {
      e.preventDefault(); e.stopImmediatePropagation();
      var on = !body.classList.contains('dark-mode');
      body.classList.toggle('dark-mode', on);
      document.documentElement.classList.toggle('dark-mode', on);
      if (icon) icon.className = on ? 'fas fa-sun' : 'fas fa-lightbulb';
      localStorage.setItem(KEY, on ? '1' : '0');
    }, true);
  });
})();
</script>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tere LEARN | Faculty Management</title>
  <style>a { text-decoration: none !important; }</style>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="dist/css/deansec.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <style>
    /* ══ SIDEBAR ══ */
    :root {
      --sb-w:240px;--sb-mini-w:56px;--sb-bg:#1e2840;--sb-border:rgba(255,255,255,.06);
      --sb-text:rgba(255,255,255,.55);--sb-hover:rgba(255,255,255,.07);
      --sb-active:rgba(255,255,255,.1);--sb-accent:#4ade80;--nav-h:50px;
      --sb-trans:.2s cubic-bezier(.4,0,.2,1);
    }
    .main-sidebar{display:none !important;}
    #tlSidebar{position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);background:var(--sb-bg);
      display:flex;flex-direction:column;z-index:1050;transition:width var(--sb-trans);
      overflow:hidden;box-shadow:2px 0 16px rgba(0,0,0,.22);}
    #tlSidebar.collapsed{width:var(--sb-mini-w);}
    .sb-brand{display:flex;align-items:center;gap:.65rem;padding:0 .85rem;height:var(--nav-h);
      border-bottom:1px solid var(--sb-border);flex-shrink:0;overflow:hidden;text-decoration:none;}
    .sb-brand img{width:26px;height:26px;object-fit:contain;flex-shrink:0;}
    .sb-brand-text{font-size:.88rem;font-weight:700;color:#fff;white-space:nowrap;letter-spacing:.3px;
      transition:opacity var(--sb-trans),width var(--sb-trans);}
    #tlSidebar.collapsed .sb-brand-text{opacity:0;width:0;}
    .sb-user{display:flex;align-items:center;gap:.6rem;padding:.65rem .85rem;
      border-bottom:1px solid var(--sb-border);flex-shrink:0;overflow:hidden;}
    .sb-avatar{width:28px;height:28px;border-radius:50%;border:1.5px solid rgba(255,255,255,.18);
      object-fit:cover;flex-shrink:0;}
    .sb-user-info{overflow:hidden;transition:opacity var(--sb-trans),width var(--sb-trans);}
    #tlSidebar.collapsed .sb-user-info{opacity:0;width:0;}
    .sb-user-name{font-size:.76rem;font-weight:600;color:rgba(255,255,255,.82);white-space:nowrap;}
    .sb-user-role{font-size:.63rem;color:rgba(255,255,255,.32);white-space:nowrap;}
    .sb-nav{flex:1;overflow-y:auto;overflow-x:hidden;padding:.4rem 0;}
    .sb-nav::-webkit-scrollbar{width:3px;}
    .sb-nav::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:2px;}
    .sb-section{font-size:.59rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;
      color:rgba(255,255,255,.22);padding:.65rem .85rem .15rem;white-space:nowrap;
      transition:opacity var(--sb-trans);}
    #tlSidebar.collapsed .sb-section{opacity:0;}
    .sb-item{position:relative;}
    .sb-link{display:flex;align-items:center;gap:.55rem;padding:.45rem .8rem;margin:1px .35rem;
      border-radius:7px;color:var(--sb-text);text-decoration:none;font-size:.8rem;font-weight:500;
      border-left:2.5px solid transparent;white-space:nowrap;overflow:hidden;cursor:pointer;
      user-select:none;transition:background var(--sb-trans),color var(--sb-trans),
      border-color var(--sb-trans),transform var(--sb-trans);}
    .sb-link:hover{background:var(--sb-hover);color:rgba(255,255,255,.88);transform:translateX(2px);}
    .sb-link.active{background:rgba(74,222,128,.08);color:rgba(255,255,255,.95);font-weight:600;
      border-left-color:var(--sb-accent);transform:translateX(2px);}
    .sb-link i.sb-icon{width:16px;text-align:center;font-size:.78rem;flex-shrink:0;opacity:.6;
      transition:opacity var(--sb-trans);}
    .sb-link:hover i.sb-icon,.sb-link.active i.sb-icon{opacity:1;}
    .sb-link.active i.sb-icon{color:var(--sb-accent);}
    .sb-label{flex:1;transition:opacity var(--sb-trans),width var(--sb-trans);}
    #tlSidebar.collapsed .sb-label{opacity:0;width:0;}
    #tlSidebar.collapsed .sb-link{justify-content:center;padding:.45rem;
      border-left-color:transparent !important;transform:none !important;}
    .sb-arrow{font-size:.58rem;opacity:.35;margin-left:auto;flex-shrink:0;
      transition:transform var(--sb-trans),opacity var(--sb-trans);}
    .sb-item.open>.sb-link .sb-arrow{transform:rotate(90deg);opacity:.65;}
    #tlSidebar.collapsed .sb-arrow{display:none;}
    .sb-sub{overflow:hidden;max-height:0;transition:max-height .22s ease;}
    .sb-item.open>.sb-sub{max-height:300px;}
    #tlSidebar.collapsed .sb-sub{max-height:0 !important;}
    .sb-sub .sb-link{padding:.36rem .8rem .36rem 2.2rem;font-size:.76rem;border-left-color:transparent;}
    .sb-sub .sb-link::before{content:'';width:4px;height:4px;border-radius:50%;
      background:rgba(255,255,255,.18);flex-shrink:0;margin-right:.45rem;
      transition:background var(--sb-trans);}
    .sb-sub .sb-link:hover::before{background:rgba(255,255,255,.45);}
    .sb-sub .sb-link.active::before{background:var(--sb-accent);}
    #tlSidebar.collapsed .sb-item:hover .sb-link::after{content:attr(data-tip);
      position:absolute;left:calc(var(--sb-mini-w) + 6px);top:50%;transform:translateY(-50%);
      background:#111;color:#fff;font-size:.7rem;font-weight:600;padding:.28rem .6rem;
      border-radius:6px;white-space:nowrap;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,.3);
      pointer-events:none;}
    #tlOverlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1049;}
    @media(max-width:767px){
      #tlSidebar{transform:translateX(-100%);transition:transform var(--sb-trans);width:var(--sb-w) !important;}
      body.sb-open #tlSidebar{transform:translateX(0);}
      body.sb-open #tlOverlay{display:block;}
    }

    /* ══ TOP NAVBAR ══ */
    .main-header{display:none !important;}
    .tl-topnav{position:fixed;top:0;left:var(--sb-w);right:0;height:var(--nav-h);
      background:#fff;border-bottom:1px solid #e8ecf0;display:flex;align-items:center;
      gap:.6rem;padding:0 1.1rem;z-index:1055;box-shadow:0 1px 6px rgba(0,0,0,.05);
      transition:left var(--sb-trans),background .2s,border-color .2s;}
    body.sb-collapsed .tl-topnav{left:var(--sb-mini-w);}
    .dark-mode .tl-topnav{background:#1a2232;border-bottom-color:#2e3849;}
    #tlMenuToggle{width:32px;height:32px;border:none;background:transparent;cursor:pointer;
      border-radius:7px;flex-shrink:0;display:flex;flex-direction:column;
      justify-content:center;align-items:center;gap:4px;transition:background .15s;}
    #tlMenuToggle:hover{background:#f0f1f3;}
    .dark-mode #tlMenuToggle:hover{background:rgba(255,255,255,.08);}
    #tlMenuToggle span{display:block;width:16px;height:2px;background:#555;border-radius:2px;
      transition:transform .2s,opacity .2s,width .2s;transform-origin:center;}
    .dark-mode #tlMenuToggle span{background:#94a3b8 !important;}
    body.sb-open #tlMenuToggle span:nth-child(1){transform:translateY(6px) rotate(45deg);}
    body.sb-open #tlMenuToggle span:nth-child(2){opacity:0;width:0;}
    body.sb-open #tlMenuToggle span:nth-child(3){transform:translateY(-6px) rotate(-45deg);}
    body.sb-collapsed #tlMenuToggle span:nth-child(2){width:10px;}
    .tl-nav-right{margin-left:auto;display:flex;align-items:center;gap:.2rem;}
    .tl-nav-icon{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;
      justify-content:center;color:#6c757d;font-size:.85rem;text-decoration:none;
      transition:background .15s,color .15s;}
    .tl-nav-icon:hover{background:#f0f1f3;color:#1a2234;}
    .dark-mode .tl-nav-icon{color:#8a9ab5;}
    .dark-mode .tl-nav-icon:hover{background:rgba(255,255,255,.08);color:#e2e8f0;}
    .content-wrapper,.main-footer{margin-left:var(--sb-w) !important;margin-top:var(--nav-h) !important;
      transition:margin-left var(--sb-trans) !important;}
    body.sb-collapsed .content-wrapper,body.sb-collapsed .main-footer{margin-left:var(--sb-mini-w) !important;}
    @media(max-width:767px){
      .tl-topnav{left:0 !important;}
      .content-wrapper,.main-footer{margin-left:0 !important;margin-top:var(--nav-h) !important;}
    }

    /* ══ DARK MODE ══ */
    body.dark-mode{background:#111827 !important;color:#e2e8f0 !important;}
    body.dark-mode #tlSidebar{background:#1e2840 !important;}
    body.dark-mode .sb-brand{border-bottom-color:rgba(255,255,255,.08) !important;}
    body.dark-mode .sb-user{border-bottom-color:rgba(255,255,255,.08) !important;}
    body.dark-mode .sb-section{color:rgba(255,255,255,.28) !important;}
    body.dark-mode .sb-link{color:rgba(255,255,255,.6) !important;}
    body.dark-mode .sb-link:hover{background:rgba(255,255,255,.09) !important;color:rgba(255,255,255,.95) !important;}
    body.dark-mode .sb-link.active{background:rgba(74,222,128,.1) !important;color:#fff !important;}
    body.dark-mode .tl-topnav{background:#1a2232 !important;border-bottom-color:#2a3447 !important;}
    body.dark-mode .content-wrapper{background:#111827 !important;}
    body.dark-mode .main-footer{background:#1a2232 !important;border-top-color:#2a3447 !important;color:#8a9ab5 !important;}
    body.dark-mode .bg-white,.dark-mode .card{background:#1e2838 !important;border-color:#2e3849 !important;}
    body.dark-mode h1,body.dark-mode h2,body.dark-mode h3{color:#e2e8f0 !important;}
    body.dark-mode .breadcrumb-item,body.dark-mode .breadcrumb-item a{color:#8a9ab5 !important;}
    body.dark-mode .breadcrumb-item.active{color:#e2e8f0 !important;}
    body.dark-mode .form-control,body.dark-mode .form-select{background:#2b3443 !important;border-color:#3d4a5c !important;color:#e2e8f0 !important;}
    body.dark-mode .input-group-text{background:#2b3443 !important;border-color:#3d4a5c !important;color:#94a3b8 !important;}
    body.dark-mode .table{color:#e2e8f0 !important;}
    body.dark-mode .shadow-lg{box-shadow:0 4px 16px rgba(0,0,0,.4) !important;}
    body.dark-mode #dark-mode-toggle i::before{content:"\f185";}

    /* ══ SEARCH BAR ══ */
    .search-wrap{position:relative;flex:1;}
    .search-wrap .search-icon{position:absolute;left:.8rem;top:50%;transform:translateY(-50%);
      color:#adb5bd;font-size:.82rem;pointer-events:none;z-index:4;}
    .search-wrap .form-control{padding-left:2.2rem;padding-right:2.2rem;border:1.5px solid #dde3ed;
      border-radius:8px;transition:border-color .2s,box-shadow .2s;}
    .search-wrap .form-control:focus{border-color:#2563a8;box-shadow:0 0 0 3px rgba(37,99,168,.1);outline:none;}
    .search-wrap .search-clear{position:absolute;right:.65rem;top:50%;transform:translateY(-50%);
      width:20px;height:20px;border:none;background:#e8ecf0;border-radius:50%;cursor:pointer;
      color:#6c757d;font-size:.65rem;display:none;align-items:center;justify-content:center;
      transition:background .15s,color .15s;z-index:4;}
    .search-wrap .search-clear.visible{display:flex;}
    .search-wrap .search-clear:hover{background:#2563a8;color:#fff;}
    .result-count{font-size:.75rem;color:#8a96a3;white-space:nowrap;display:flex;align-items:center;gap:.3rem;}
    .result-count strong{color:#2563a8;}
    .dark-mode .search-wrap .form-control{background:#2b3443;border-color:#3d4a5c;color:#e2e8f0;}
    .dark-mode .search-wrap .search-clear{background:#3d4a5c;color:#8a96a3;}

    /* ══ TABLE ══ */
    #facultyTable{font-size:.8rem;width:100%;table-layout:fixed;}
    #facultyTable thead th{background:#2d3a4a;color:#fff;font-size:.7rem;font-weight:600;
      text-transform:uppercase;letter-spacing:.5px;padding:.6rem .6rem;white-space:nowrap;
      vertical-align:middle;border:none;overflow:hidden;}
    #facultyTable thead th:first-child{border-radius:8px 0 0 0;}
    #facultyTable thead th:last-child{border-radius:0 8px 0 0;}
    #facultyTable tbody td{padding:.6rem .6rem;vertical-align:middle;border-bottom:1px solid #eef0f5;
      overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .dark-mode #facultyTable tbody td{border-bottom-color:#2e3849;}
    #facultyTable tbody tr{transition:background .15s ease;}
    #facultyTable tbody tr:hover{background:#f0f5ff !important;}
    .dark-mode #facultyTable tbody tr:hover{background:#2a3547 !important;}
    #facultyTable tbody tr:nth-child(even){background:#fafbfd;}
    .dark-mode #facultyTable tbody tr:nth-child(even){background:#1e2838;}
    .td-id{font-family:'Courier New',monospace;font-weight:700;font-size:.78rem;color:#2563a8;letter-spacing:.3px;}
    .dark-mode .td-id{color:#60a5fa;}
    .td-name{font-weight:600;font-size:.82rem;}
    .td-email{font-size:.76rem;color:#6c757d;overflow:hidden;text-overflow:ellipsis;}
    .dark-mode .td-email{color:#94a3b8;}
    .td-sub{font-size:.75rem;color:#6c757d;}
    .dark-mode .td-sub{color:#94a3b8;}
    .pill-active{display:inline-block;padding:.18em .5em;border-radius:20px;font-size:.68rem;font-weight:700;background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
    .pill-inactive{display:inline-block;padding:.18em .5em;border-radius:20px;font-size:.68rem;font-weight:700;background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;}
    .pill-dean{display:inline-block;padding:.18em .45em;border-radius:20px;font-size:.68rem;font-weight:700;background:#fef3c7;color:#92400e;border:1px solid #fcd34d;}
    .act-group{display:flex;gap:3px;justify-content:center;flex-wrap:nowrap;}
    .act-group .btn{padding:.22rem .45rem;font-size:.72rem;border-radius:5px;}

    /* ══ OTP TOGGLE ══ */
    .otp-toggle-wrap{display:inline-flex;align-items:center;gap:7px;cursor:pointer;user-select:none;}
    .otp-toggle-wrap.locked{opacity:.42;cursor:not-allowed;pointer-events:none;}
    .otp-switch{position:relative;width:38px;height:21px;flex-shrink:0;}
    .otp-switch input{position:absolute;opacity:0;width:0;height:0;}
    .otp-slider{position:absolute;inset:0;background:#ced4da;border-radius:21px;transition:background .22s;cursor:pointer;}
    .otp-slider::before{content:'';position:absolute;width:15px;height:15px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:transform .22s;box-shadow:0 1px 4px rgba(0,0,0,.25);}
    .otp-switch input:checked + .otp-slider{background:#1e8449;}
    .otp-switch input:checked + .otp-slider::before{transform:translateX(17px);}
    .otp-switch input:disabled + .otp-slider{cursor:not-allowed;opacity:.6;}
    .otp-label{font-size:.68rem;font-weight:700;white-space:nowrap;}
    .otp-label.on{color:#1e8449;}.otp-label.off{color:#adb5bd;}
    .otp-label.locked-msg{color:#f57c00;font-style:italic;font-weight:500;}

    /* ══ MODALS ══ */
    #addFacultyModal .modal-content,
    #editFacultyModal .modal-content{border:none;border-radius:16px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.18);}
    #addFacultyModal .modal-header,
    #editFacultyModal .modal-header{background:linear-gradient(135deg,#1a3c5e 0%,#2563a8 100%);color:#fff;padding:1.4rem 1.8rem;border-bottom:none;}
    #addFacultyModal .modal-header .modal-title,
    #editFacultyModal .modal-header .modal-title{font-size:1.1rem;font-weight:700;display:flex;align-items:center;gap:10px;}
    #addFacultyModal .btn-close,
    #editFacultyModal .btn-close{filter:invert(1) brightness(2);opacity:.85;}
    #addFacultyModal .modal-body,
    #editFacultyModal .modal-body{padding:0;background:#f8f9fc;}
    #addFacultyModal .modal-body-inner,
    #editFacultyModal .modal-body-inner{padding:1.5rem;}
    #addFacultyModal .form-section,
    #editFacultyModal .form-section{background:#fff;border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1rem;border:1px solid #e8ecf0;box-shadow:0 1px 4px rgba(0,0,0,.04);}
    #addFacultyModal .form-section:last-of-type,
    #editFacultyModal .form-section:last-of-type{margin-bottom:0;}
    #addFacultyModal .section-label,
    #editFacultyModal .section-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#2563a8;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid #e8f0fe;display:flex;align-items:center;gap:7px;}
    #addFacultyModal .form-label,
    #editFacultyModal .form-label{font-size:.78rem;font-weight:600;color:#4a5568;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;}
    #addFacultyModal .form-control,#addFacultyModal .form-select,
    #editFacultyModal .form-control,#editFacultyModal .form-select{border:1.5px solid #dde3ed;border-radius:8px;font-size:.9rem;padding:.5rem .85rem;transition:border-color .2s,box-shadow .2s;background:#fff;}
    #addFacultyModal .form-control:focus,#addFacultyModal .form-select:focus,
    #editFacultyModal .form-control:focus,#editFacultyModal .form-select:focus{border-color:#2563a8;box-shadow:0 0 0 3px rgba(37,99,168,.12);outline:none;}
    #addFacultyModal .input-group-text,
    #editFacultyModal .input-group-text{background:#f1f5fb;border:1.5px solid #dde3ed;color:#2563a8;font-size:.82rem;font-weight:600;border-radius:8px 0 0 8px;}
    #addFacultyModal .input-group .form-control,
    #editFacultyModal .input-group .form-control{border-radius:0 8px 8px 0;}
    #addFacultyModal .modal-footer,
    #editFacultyModal .modal-footer{background:#f8f9fc;border-top:1px solid #e8ecf0;padding:1rem 1.5rem;gap:8px;}
    .dean-toggle-wrap{background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1.5px solid #fcd34d;border-radius:10px;padding:.9rem 1.1rem;display:flex;align-items:center;gap:12px;}
    .dean-toggle-wrap .form-check-input{width:2.2em;height:1.2em;cursor:pointer;border-color:#f59e0b;}
    .dean-toggle-wrap .form-check-input:checked{background-color:#f59e0b;border-color:#f59e0b;}
    .dean-toggle-wrap label{font-size:.88rem;font-weight:600;color:#78350f;cursor:pointer;margin:0;}
  </style>
</head>

<div id="tlOverlay"></div>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- NAVBAR -->
  <nav class="tl-topnav">
    <button id="tlMenuToggle" title="Toggle sidebar">
      <span></span><span></span><span></span>
    </button>
    <div class="tl-nav-right">
      <a class="tl-nav-icon" id="dark-mode-toggle" href="#" title="Theme"><i class="fas fa-lightbulb"></i></a>
      <a class="tl-nav-icon" data-widget="fullscreen" href="#" title="Fullscreen"><i class="fas fa-expand-arrows-alt"></i></a>
    </div>
  </nav>

  <!-- SIDEBAR -->
  <aside id="tlSidebar">
    <a href="analytics.php" class="sb-brand">
      <img src="dist/img/terelearn.png" alt="Logo">
      <span class="sb-brand-text">TERE LEARN</span>
    </a>
    <div class="sb-user">
      <img src="dist/img/user2-160x160.jpg" class="sb-avatar" alt="User">
      <div class="sb-user-info">
        <div class="sb-user-name">Renwel Lucero</div>
        <div class="sb-user-role">Administrator</div>
      </div>
    </div>
    <nav class="sb-nav">
      <div class="sb-section">Main</div>
      <div class="sb-item">
        <a href="./analytics.php" class="sb-link" data-tip="Dashboard">
          <i class="fas fa-layer-group sb-icon"></i>
          <span class="sb-label">Dashboard</span>
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
          <a href="./faculty.php" class="sb-link active"><span class="sb-label">Faculty</span></a>
          <a href="./facultyclass.php" class="sb-link"><span class="sb-label">Class</span></a>
          <a href="./facultysubject.php" class="sb-link"><span class="sb-label">Subject</span></a>
        </div>
      </div>
      <div class="sb-section">Students</div>
      <div class="sb-item" id="sbStudents">
        <div class="sb-link" onclick="sbToggle('sbStudents')" data-tip="Student">
          <i class="fas fa-user-graduate sb-icon"></i>
          <span class="sb-label">Student</span>
          <i class="fas fa-chevron-right sb-arrow"></i>
        </div>
        <div class="sb-sub">
          <a href="./student.php" class="sb-link"><span class="sb-label">Manage Accounts</span></a>
        </div>
      </div>
    </nav>
  </aside>

  <!-- CONTENT -->
  <div class="content-wrapper bg-muted">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0">Faculty Management</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="analytics.php" class="text-decoration-none text-dark">Management</a></li>
              <li class="breadcrumb-item active">Faculty</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <section class="col-12 pt-2">

            <!-- Search bar -->
            <div class="row p-2">
              <div class="col-lg-12 shadow-lg p-3 border border-muted rounded bg-white">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <div class="search-wrap flex-grow-1" style="min-width:200px;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="form-control"
                      placeholder="Search by ID, Name, Email, or Username…" autocomplete="off">
                    <button type="button" class="search-clear" id="searchClearBtn" title="Clear">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <div class="result-count" id="resultCount" style="display:none;">
                    Showing <strong id="resultNum">0</strong> result<span id="resultPlural">s</span>
                  </div>
                  <button class="btn btn-primary" id="addFacultyBtn"
                    style="border-radius:8px;font-weight:600;white-space:nowrap;">
                    <i class="fas fa-plus me-1"></i> Add Faculty
                  </button>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="row p-2">
              <div class="col-lg-12 shadow-lg p-3 border border-muted rounded bg-white">
                <div class="table-responsive">
                  <table class="table table-hover mb-0" id="facultyTable">
                    <thead>
                      <tr>
                        <th class="text-center" style="width:36px">#</th>
                        <th style="width:100px">Faculty ID</th>
                        <th style="width:165px">Faculty Name</th>
                        <th style="width:170px">Email</th>
                        <th style="width:110px">Phone</th>
                        <th style="width:95px">Birthdate</th>
                        <th class="text-center" style="width:65px">Status</th>
                        <th class="text-center" style="width:55px">Dean</th>
                        <th class="text-center" style="width:105px">
                          Email OTP
                          <i class="fas fa-question-circle ms-1" style="cursor:help;font-size:.68rem;opacity:.55;"
                             title="Locked (⚠ Pending) for new accounts until first login is complete."></i>
                        </th>
                        <th class="text-center" style="width:95px">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr><td colspan="10" class="text-center py-3">
                        <i class="fas fa-spinner fa-spin me-2"></i>Loading…
                      </td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </section>
        </div>
      </div>
    </section>
  </div>

  <footer class="main-footer">
    <strong>Copyright &copy; 2025-2026 <a href="#">TERE LEARN</a>.</strong> All rights reserved.
    <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0.0</div>
  </footer>
</div>

<!-- ══ ADD FACULTY MODAL ══ -->
<div class="modal fade" id="addFacultyModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <span style="width:34px;height:34px;background:rgba(255,255,255,.18);border-radius:8px;display:inline-flex;align-items:center;justify-content:center;">
            <i class="fas fa-user-plus"></i>
          </span>
          &nbsp;Faculty Registration
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="addFacultyForm">
          <div class="modal-body-inner">

            <div class="form-section">
              <div class="section-label"><i class="fas fa-id-card"></i> Basic Information</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Faculty ID <span class="text-danger">*</span></label>
                  <input type="text" name="faculty_number" id="add_faculty_number" class="form-control" placeholder="e.g. 00-00000" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Account Type</label>
                  <div style="display:inline-flex;align-items:center;gap:6px;background:#e8f0fe;color:#1a3c5e;border:1.5px solid #c7d9f5;border-radius:8px;padding:.5rem .85rem;font-size:.85rem;font-weight:600;width:100%;">
                    <i class="fas fa-chalkboard-teacher"></i> Faculty Member
                  </div>
                  <input type="hidden" name="user_type_id" value="2">
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="section-label"><i class="fas fa-user"></i> Full Name</div>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">First Name <span class="text-danger">*</span></label>
                  <input type="text" name="first_name" id="add_first_name" class="form-control" placeholder="First name" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Middle Name</label>
                  <input type="text" name="middle_name" class="form-control" placeholder="N/A if not applicable">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Last Name <span class="text-danger">*</span></label>
                  <input type="text" name="last_name" id="add_last_name" class="form-control" placeholder="Last name" required>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="section-label"><i class="fas fa-address-book"></i> Contact & Account</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Email Address <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">+63</span>
                    <input type="text" name="phone" class="form-control" placeholder="9XX-XXXX-XXX" required>
                  </div>
                </div>
              </div>
              <div class="row g-3 mt-0">
                <div class="col-md-4">
                  <label class="form-label">Username <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">@</span>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                  </div>
                  <small class="text-muted" style="font-size:.75rem;">Minimum 4 characters</small>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Birthdate <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                    <input type="date" name="birthdate" id="add_birthdate" class="form-control" required>
                  </div>
                  <small class="text-muted" style="font-size:.75rem;">Used to generate default password</small>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Password <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="text" name="password" id="add_password" class="form-control" placeholder="Default password" required>
                  </div>
                  <small class="text-muted" style="font-size:.75rem;">
                    Auto-fill: <button type="button" onclick="autoPassword()" style="border:none;background:none;color:#2563a8;font-size:.75rem;cursor:pointer;padding:0;font-weight:600;">Generate ↺</button>
                  </small>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="section-label"><i class="fas fa-star"></i> Role Assignment</div>
              <div class="dean-toggle-wrap">
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" id="is_dean_add" name="is_dean" value="1">
                </div>
                <label for="is_dean_add">
                  <i class="fas fa-user-tie me-1"></i> This professor is also a <strong>Dean</strong>
                </label>
              </div>
            </div>

          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('addFacultyForm').reset()"><i class="fas fa-undo me-1"></i> Reset</button>
        <button type="button" class="btn btn-primary text-white" id="submitFacultyBtn" onclick="submitAddFaculty()"><i class="fas fa-check me-1"></i> Submit</button>
      </div>
    </div>
  </div>
</div>

<!-- ══ EDIT FACULTY MODAL ══ -->
<div class="modal fade" id="editFacultyModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <span style="width:34px;height:34px;background:rgba(255,255,255,.18);border-radius:8px;display:inline-flex;align-items:center;justify-content:center;">
            <i class="fas fa-user-edit"></i>
          </span>
          &nbsp;Edit Faculty
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editFacultyForm">
          <input type="hidden" id="edit_faculty_id" name="faculty_id">
          <div class="modal-body-inner">

            <div class="form-section">
              <div class="section-label"><i class="fas fa-id-card"></i> Basic Information</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Faculty ID</label>
                  <input type="text" class="form-control" id="edit_faculty_number" name="faculty_number" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Username</label>
                  <div class="input-group">
                    <span class="input-group-text">@</span>
                    <input type="text" class="form-control" id="edit_username" name="username" required>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="section-label"><i class="fas fa-user"></i> Full Name</div>
              <div class="row g-3">
                <div class="col-md-4"><label class="form-label">First Name</label><input type="text" class="form-control" id="edit_first_name" name="first_name" required></div>
                <div class="col-md-4"><label class="form-label">Middle Name</label><input type="text" class="form-control" id="edit_middle_name" name="middle_name"></div>
                <div class="col-md-4"><label class="form-label">Last Name</label><input type="text" class="form-control" id="edit_last_name" name="last_name" required></div>
              </div>
            </div>

            <div class="form-section">
              <div class="section-label"><i class="fas fa-address-book"></i> Contact & Account</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="edit_email" name="email" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <div class="input-group">
                    <span class="input-group-text">+63</span>
                    <input type="text" class="form-control" id="edit_phone" name="phone">
                  </div>
                </div>
              </div>
              <div class="row g-3 mt-0">
                <div class="col-md-6">
                  <label class="form-label">Birthdate</label>
                  <input type="date" class="form-control" id="edit_birthdate" name="birthdate">
                </div>
                <div class="col-md-6">
                  <label class="form-label">New Password <span class="text-muted" style="text-transform:none;letter-spacing:0;font-weight:400;">(leave blank to keep)</span></label>
                  <input type="password" class="form-control" id="edit_password" name="password" placeholder="Leave blank to keep current">
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="section-label"><i class="fas fa-star"></i> Role Assignment</div>
              <div class="dean-toggle-wrap">
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" id="is_dean_edit" name="is_dean" value="1">
                </div>
                <label for="is_dean_edit">
                  <i class="fas fa-user-tie me-1"></i> This professor is also a <strong>Dean</strong>
                </label>
              </div>
            </div>

          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('editFacultyForm').reset()"><i class="fas fa-undo me-1"></i> Reset</button>
        <button type="button" class="btn btn-success" id="submitEditBtn" onclick="submitEditFaculty()"><i class="fas fa-check me-1"></i> Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts — identical order to student.php -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="dist/js/adminlte.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
window.showToast = function(msg, type='success') {
  Swal.fire({ toast:true,position:'top-end',icon:{success:'success',error:'error',info:'info',warning:'warning'}[type]||'info',title:msg,showConfirmButton:false,timer:3500,timerProgressBar:true });
};

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

let allFaculty = [];

/* ── Load Table ── */
function loadFaculty() {
  $('#facultyTable tbody').html('<tr><td colspan="10" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading…</td></tr>');
  $.getJSON('API/Faculty/fetch_faculty_json.php', function(data) {
    allFaculty = Array.isArray(data) ? data : [];
    renderTable(applyFilter($('#searchInput').val().trim()));
  }).fail(function() {
    $('#facultyTable tbody').html('<tr><td colspan="10" class="text-center text-danger py-3">Failed to load faculty. Make sure fetch_faculty_json.php exists.</td></tr>');
  });
}

function applyFilter(q) {
  if (!q) return allFaculty;
  const lq = q.toLowerCase();
  return allFaculty.filter(f =>
    (f.faculty_number||'').toLowerCase().includes(lq) ||
    (f.first_name||'').toLowerCase().includes(lq) ||
    (f.last_name||'').toLowerCase().includes(lq) ||
    (f.email||'').toLowerCase().includes(lq) ||
    (f.username||'').toLowerCase().includes(lq) ||
    (f.phone||'').toLowerCase().includes(lq)
  );
}

function renderTable(list) {
  const n = list.length;
  if ($('#searchInput').val().trim()) {
    $('#resultNum').text(n);
    $('#resultPlural').text(n===1?'':'s');
    $('#resultCount').show();
  } else { $('#resultCount').hide(); }

  if (!n) {
    $('#facultyTable tbody').html('<tr><td colspan="10" class="text-center text-muted py-3">No faculty found.</td></tr>');
    return;
  }

  let html = '';
  list.forEach((f, i) => {
    const active   = parseInt(f.is_active) === 1;
    const isDean   = parseInt(f.is_dean) === 1;
    const pill     = active ? '<span class="pill-active">Active</span>' : '<span class="pill-inactive">Inactive</span>';
    const deanBadge = isDean ? '<span class="pill-dean">Dean</span>' : '<span class="text-muted" style="font-size:.72rem;">—</span>';
    const name     = [f.last_name, f.first_name].filter(Boolean).join(', ');
    const hasUser  = f.user_id && f.user_id !== '';
    /* first_login=1 means account not yet fully set up — lock OTP toggle */
    const isLocked = !hasUser || parseInt(f.first_login) === 1;
    const otpOn    = parseInt(f.otp_enabled) === 1;
    const otpLabelTxt = isLocked ? '⚠ Pending' : (otpOn ? 'OTP On' : 'OTP Off');
    const otpLabelCls = isLocked ? 'locked-msg' : (otpOn ? 'on' : 'off');
    const otpHtml = hasUser
      ? `<label class="otp-toggle-wrap ${isLocked ? 'locked' : ''}" title="${isLocked ? 'Locked — faculty has not completed first login' : ''}">
           <div class="otp-switch">
             <input type="checkbox" ${otpOn ? 'checked' : ''} ${isLocked ? 'disabled' : ''}
               onchange="toggleOtpAuth('${esc(f.user_id)}', this)">
             <span class="otp-slider"></span>
           </div>
           <span class="otp-label ${otpLabelCls}" id="otp-label-${esc(f.user_id)}">${otpLabelTxt}</span>
         </label>`
      : `<span class="otp-label locked-msg">No account</span>`;

    html += `<tr>
      <td class="text-center">${i+1}</td>
      <td><span class="td-id">${esc(f.faculty_number)}</span></td>
      <td class="td-name">${esc(name)}</td>
      <td><div class="td-email">${esc(f.email||'—')}</div></td>
      <td><span class="td-sub">${esc(f.phone||'—')}</span></td>
      <td><span class="td-sub">${esc(f.birthdate||'—')}</span></td>
      <td class="text-center">${pill}</td>
      <td class="text-center">${deanBadge}</td>
      <td class="text-center">${otpHtml}</td>
      <td class="text-center">
        <div class="act-group">
          <button class="btn btn-sm ${active?'btn-danger':'btn-success'}"
            onclick="toggleFacultyStatus('${esc(f.faculty_number)}')"
            title="${active?'Deactivate':'Activate'}">
            <i class="fas ${active?'fa-ban':'fa-check-circle'}"></i>
          </button>
          <button class="btn btn-sm btn-warning" onclick="editFaculty('${esc(f.faculty_number)}')" title="Edit">
            <i class="fas fa-edit"></i>
          </button>
          <button class="btn btn-sm btn-dark" onclick="deleteFaculty('${esc(f.id)}')" title="Delete">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      </td>
    </tr>`;
  });
  $('#facultyTable tbody').html(html);
}

/* ── Search ── */
let searchTimer;
$('#searchInput').on('input', function() {
  const q = $(this).val().trim();
  $('#searchClearBtn').toggleClass('visible', q.length > 0);
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => renderTable(applyFilter(q)), 200);
});
$('#searchClearBtn').on('click', function() { $('#searchInput').val('').trigger('input').focus(); });

/* ── Auto password ── */
function autoPassword() {
  const fn = $('#add_first_name').val().trim();
  const ln = $('#add_last_name').val().trim();
  const bd = $('#add_birthdate').val(); // YYYY-MM-DD
  if (!fn || !ln || !bd) { showToast('Fill in First Name, Last Name and Birthdate first.', 'info'); return; }
  const parts = bd.split('-'); // [YYYY, MM, DD]
  if (parts.length !== 3) return;
  /* format: firstInitial + MM + DD + YYYY + lastInitial  e.g. r08272004L */
  $('#add_password').val(fn.charAt(0).toLowerCase() + parts[1] + parts[2] + parts[0] + ln.charAt(0).toUpperCase());
}

/* ── Init ── */
$(function() {
  /* Add Faculty button */
  $('#addFacultyBtn').on('click', function() {
    document.getElementById('addFacultyForm').reset();
    $('#is_dean_add').prop('checked', false);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('addFacultyModal')).show();
  });

  /* Auto-generate password when birthdate changes */
  $(document).on('change', '#add_birthdate', function() {
    if ($('#add_first_name').val().trim() && $('#add_last_name').val().trim()) autoPassword();
  });

  loadFaculty();
});

/* ── Submit Add Faculty → savefaculty.php ── */
function submitAddFaculty() {
  const btn = $('#submitFacultyBtn');
  btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing…');
  $.ajax({
    url: 'API/Faculty/savefaculty.php',
    type: 'POST',
    data: $('#addFacultyForm').serialize(),
    dataType: 'json',
    success(r) {
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Submit');
      if (r.status === 'success') {
        showToast(r.message, 'success');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addFacultyModal')).hide();
        document.getElementById('addFacultyForm').reset();
        loadFaculty();
      } else showToast(r.message, 'error');
    },
    error() {
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Submit');
      showToast('Server error. Please try again.', 'error');
    }
  });
}

/* ── Edit Faculty → get_single_faculty.php ── */
function editFaculty(facultyNumber) {
  $.getJSON('API/Faculty/get_single_faculty.php', { faculty_number: facultyNumber }, function(r) {
    if (r.status !== 'success') { showToast(r.message, 'error'); return; }
    const f = r.faculty;
    $('#edit_faculty_id').val(f.id);
    $('#edit_faculty_number').val(f.faculty_number);
    $('#edit_first_name').val(f.first_name);
    $('#edit_middle_name').val(f.middle_name || '');
    $('#edit_last_name').val(f.last_name);
    $('#edit_email').val(f.email);
    $('#edit_phone').val(f.phone || '');
    $('#edit_birthdate').val(f.birthdate || '');
    $('#edit_username').val(f.username);
    $('#edit_password').val('');
    $('#is_dean_edit').prop('checked', parseInt(f.is_dean) === 1);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editFacultyModal')).show();
  });
}

/* ── Submit Edit Faculty → editfaculty.php ── */
function submitEditFaculty() {
  const btn = $('#submitEditBtn');
  btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>');
  $.ajax({
    url: 'API/Faculty/editfaculty.php',
    type: 'POST',
    data: $('#editFacultyForm').serialize(),
    dataType: 'json',
    success(r) {
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Save Changes');
      if (r.status === 'success') {
        showToast(r.message, 'success');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editFacultyModal')).hide();
        loadFaculty();
      } else showToast(r.message, 'error');
    },
    error() {
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Save Changes');
      showToast('Server error. Please try again.', 'error');
    }
  });
}

/* ── Toggle Status → toggleStatusFaculty.php ── */
function toggleFacultyStatus(facultyNumber) {
  Swal.fire({
    title: 'Toggle Status?', text: 'This will activate or deactivate the faculty account.',
    icon: 'question', showCancelButton: true,
    confirmButtonColor: '#3085d6', cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, toggle', cancelButtonText: 'Cancel'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.post('API/Faculty/toggleStatusFaculty.php', { faculty_number: facultyNumber }, function(d) {
      if (d.status === 'success') { showToast(d.message, 'success'); loadFaculty(); }
      else showToast(d.message, 'error');
    }, 'json');
  });
}

/* ── Delete Faculty → deleteFaculty.php ── */
function deleteFaculty(id) {
  Swal.fire({
    title: 'Delete this faculty?', text: 'This cannot be undone.',
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.get('API/Faculty/deleteFaculty.php', { id: id }, function(d) {
      if (d.status === 'success') { showToast(d.message, 'success'); loadFaculty(); }
      else showToast(d.message, 'error');
    }, 'json');
  });
}

/* ── OTP Toggle → toggle_otp_auth.php ── */
async function toggleOtpAuth(userId, checkbox) {
  const enabled = checkbox.checked ? 1 : 0;
  const label   = document.getElementById('otp-label-' + userId);
  checkbox.disabled = true;
  if (label) { label.textContent = 'Saving…'; label.className = 'otp-label'; }
  try {
    const res  = await fetch('API/toggle_otp_auth.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId, enabled })
    });
    let data; const raw = await res.text();
    try { data = JSON.parse(raw); } catch(e) { throw new Error('Server error. Check PHP logs.'); }
    if (data.status === 'success') {
      if (label) { label.textContent = enabled ? 'OTP On' : 'OTP Off'; label.className = 'otp-label ' + (enabled ? 'on' : 'off'); }
      showToast(data.message, 'success');
    } else {
      checkbox.checked = !enabled;
      if (label) { label.textContent = data.locked ? '⚠ Pending' : (!enabled ? 'OTP On' : 'OTP Off'); label.className = 'otp-label ' + (data.locked ? 'locked-msg' : (!enabled ? 'on' : 'off')); }
      showToast(data.message || 'Toggle failed', 'warning');
    }
  } catch(e) {
    checkbox.checked = !enabled;
    if (label) { label.textContent = !enabled ? 'OTP On' : 'OTP Off'; label.className = 'otp-label ' + (!enabled ? 'on' : 'off'); }
    showToast(e.message || 'Request failed', 'error');
  } finally { checkbox.disabled = false; }
}
</script>

<!-- Sidebar JS — identical to student.php -->
<script>
(function() {
  var sidebar = document.getElementById('tlSidebar');
  var overlay = document.getElementById('tlOverlay');
  var body    = document.body;
  var KEY     = 'tl_sb';
  if (window.innerWidth >= 768 && localStorage.getItem(KEY) === 'c') {
    sidebar.classList.add('collapsed'); body.classList.add('sb-collapsed');
  }
  document.getElementById('tlMenuToggle').addEventListener('click', function() {
    if (window.innerWidth < 768) { body.classList.toggle('sb-open'); }
    else {
      var c = sidebar.classList.toggle('collapsed');
      body.classList.toggle('sb-collapsed', c);
      localStorage.setItem(KEY, c ? 'c' : 'e');
    }
  });
  if (overlay) overlay.addEventListener('click', function() { body.classList.remove('sb-open'); });
  window.addEventListener('resize', function() { if (window.innerWidth >= 768) body.classList.remove('sb-open'); });
})();
function sbToggle(id) { document.getElementById(id).classList.toggle('open'); }
</script>

</body>
</html>