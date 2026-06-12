<?php
/* facultyclass.php  –  Tere LEARN | Faculty Class Management */
require_once dirname(__DIR__, 2) . '/config/db_connect.php';
$res = $conn->query("SELECT id,course_code,course_name FROM tblcourse WHERE is_Deleted=0 ORDER BY course_code");
$courseOpts = '';
while($r=$res->fetch_assoc()) $courseOpts .= "<option value='{$r['id']}'>{$r['course_code']} - {$r['course_name']}</option>";
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>

<script>
/* ── Dark mode — standalone, runs before page JS ── */
(function() {
  var KEY = 'tl_dark';
  // Apply immediately on load to prevent flash
  if (localStorage.getItem(KEY) === '1') {
    document.documentElement.classList.add('dark-mode');
  }
  document.addEventListener('DOMContentLoaded', function() {
    var body = document.body;
    // Sync body class (documentElement already set above)
    if (localStorage.getItem(KEY) === '1') body.classList.add('dark-mode');

    var btn  = document.getElementById('dark-mode-toggle');
    if (!btn) return;
    var icon = btn.querySelector('i');
    // Sync icon
    if (icon) icon.className = localStorage.getItem(KEY) === '1' ? 'fas fa-sun' : 'fas fa-lightbulb';

    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopImmediatePropagation(); // block theme-toggle.js handler
      var on = !body.classList.contains('dark-mode');
      body.classList.toggle('dark-mode', on);
      document.documentElement.classList.toggle('dark-mode', on);
      if (icon) icon.className = on ? 'fas fa-sun' : 'fas fa-lightbulb';
      localStorage.setItem(KEY, on ? '1' : '0');
    }, true); // capture phase — fires before theme-toggle.js
  });
})();
</script>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TERELEARN | Faculty Class Management</title>
  <style>a { text-decoration: none !important; }</style>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="dist/css/deansec.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <style>
    /* ══════════════════════════════════════
       CUSTOM SIDEBAR — clean, compact
    ══════════════════════════════════════ */
    :root {
      --sb-w:       240px;
      --sb-mini-w:  56px;
      --sb-bg:      #1e2840;
      --sb-border:  rgba(255,255,255,.06);
      --sb-text:    rgba(255,255,255,.55);
      --sb-hover:   rgba(255,255,255,.07);
      --sb-active:  rgba(255,255,255,.1);
      --sb-accent:  #4ade80;
      --nav-h:      50px;
      --sb-trans:   .2s cubic-bezier(.4,0,.2,1);
    }
    /* Reset AdminLTE sidebar so it doesn't interfere */
    .main-sidebar { display:none !important; }

    #tlSidebar {
      position: fixed; top: 0; left: 0; bottom: 0;
      width: var(--sb-w);
      background: var(--sb-bg);
      display: flex; flex-direction: column;
      z-index: 1050;
      transition: width var(--sb-trans);
      overflow: hidden;
      box-shadow: 2px 0 16px rgba(0,0,0,.22);
    }
    #tlSidebar.collapsed { width: var(--sb-mini-w); }

    .sb-brand {
      display: flex; align-items: center; gap: .65rem;
      padding: 0 .85rem; height: var(--nav-h);
      border-bottom: 1px solid var(--sb-border);
      flex-shrink: 0; overflow: hidden; text-decoration: none;
    }
    .sb-brand img { width: 26px; height: 26px; object-fit: contain; flex-shrink: 0; }
    .sb-brand-text {
      font-size: .88rem; font-weight: 700; color: #fff;
      white-space: nowrap; letter-spacing: .3px;
      transition: opacity var(--sb-trans), width var(--sb-trans);
    }
    #tlSidebar.collapsed .sb-brand-text { opacity: 0; width: 0; }

    .sb-user {
      display: flex; align-items: center; gap: .6rem;
      padding: .65rem .85rem;
      border-bottom: 1px solid var(--sb-border);
      flex-shrink: 0; overflow: hidden;
    }
    .sb-avatar {
      width: 28px; height: 28px; border-radius: 50%;
      border: 1.5px solid rgba(255,255,255,.18);
      object-fit: cover; flex-shrink: 0;
    }
    .sb-user-info { overflow: hidden; transition: opacity var(--sb-trans), width var(--sb-trans); }
    #tlSidebar.collapsed .sb-user-info { opacity: 0; width: 0; }
    .sb-user-name { font-size: .76rem; font-weight: 600; color: rgba(255,255,255,.82); white-space: nowrap; }
    .sb-user-role { font-size: .63rem; color: rgba(255,255,255,.32); white-space: nowrap; }

    .sb-nav { flex: 1; overflow-y: auto; overflow-x: hidden; padding: .4rem 0; }
    .sb-nav::-webkit-scrollbar { width: 3px; }
    .sb-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }

    .sb-section {
      font-size: .59rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: 1px; color: rgba(255,255,255,.22);
      padding: .65rem .85rem .15rem;
      white-space: nowrap;
      transition: opacity var(--sb-trans);
    }
    #tlSidebar.collapsed .sb-section { opacity: 0; }

    .sb-item { position: relative; }

    .sb-link {
      display: flex; align-items: center; gap: .55rem;
      padding: .45rem .8rem;
      margin: 1px .35rem;
      border-radius: 7px;
      color: var(--sb-text);
      text-decoration: none;
      font-size: .8rem; font-weight: 500;
      border-left: 2.5px solid transparent;
      white-space: nowrap; overflow: hidden;
      cursor: pointer;
      user-select: none;
      transition: background var(--sb-trans), color var(--sb-trans),
                  border-color var(--sb-trans), transform var(--sb-trans);
    }
    .sb-link:hover {
      background: var(--sb-hover); color: rgba(255,255,255,.88);
      transform: translateX(2px);
    }
    .sb-link.active {
      background: rgba(74,222,128,.08); color: rgba(255,255,255,.95); font-weight: 600;
      border-left-color: var(--sb-accent); transform: translateX(2px);
    }
    .sb-link i.sb-icon {
      width: 16px; text-align: center; font-size: .78rem;
      flex-shrink: 0; opacity: .6;
      transition: opacity var(--sb-trans);
    }
    .sb-link:hover i.sb-icon, .sb-link.active i.sb-icon { opacity: 1; }
    .sb-link.active i.sb-icon { color: var(--sb-accent); }

    .sb-label { flex: 1; transition: opacity var(--sb-trans), width var(--sb-trans); }
    #tlSidebar.collapsed .sb-label { opacity: 0; width: 0; }
    #tlSidebar.collapsed .sb-link {
      justify-content: center; padding: .45rem;
      border-left-color: transparent !important; transform: none !important;
    }

    .sb-arrow {
      font-size: .58rem; opacity: .35; margin-left: auto; flex-shrink: 0;
      transition: transform var(--sb-trans), opacity var(--sb-trans);
    }
    .sb-item.open > .sb-link .sb-arrow { transform: rotate(90deg); opacity: .65; }
    #tlSidebar.collapsed .sb-arrow { display: none; }

    .sb-sub { overflow: hidden; max-height: 0; transition: max-height .22s ease; background: transparent; }
    .sb-item.open > .sb-sub { max-height: 300px; }
    #tlSidebar.collapsed .sb-sub { max-height: 0 !important; }

    .sb-sub .sb-link {
      padding: .36rem .8rem .36rem 2.2rem;
      font-size: .76rem; border-left-color: transparent;
    }
    .sb-sub .sb-link::before {
      content: ''; width: 4px; height: 4px; border-radius: 50%;
      background: rgba(255,255,255,.18); flex-shrink: 0; margin-right: .45rem;
      transition: background var(--sb-trans);
    }
    .sb-sub .sb-link:hover::before  { background: rgba(255,255,255,.45); }
    .sb-sub .sb-link.active::before { background: var(--sb-accent); }

    /* tooltip on collapsed */
    #tlSidebar.collapsed .sb-item:hover .sb-link::after {
      content: attr(data-tip);
      position: absolute; left: calc(var(--sb-mini-w) + 6px); top: 50%;
      transform: translateY(-50%);
      background: #111; color: #fff;
      font-size: .7rem; font-weight: 600;
      padding: .28rem .6rem; border-radius: 6px;
      white-space: nowrap; z-index: 9999;
      box-shadow: 0 2px 8px rgba(0,0,0,.3);
      pointer-events: none;
    }

    /* Layout shifts */
    .main-header.navbar {
      margin-left: var(--sb-w) !important;
      transition: margin-left var(--sb-trans) !important;
    }
    .content-wrapper, .main-footer {
      margin-left: var(--sb-w) !important;
      transition: margin-left var(--sb-trans) !important;
    }
    body.sb-collapsed .main-header.navbar,
    body.sb-collapsed .content-wrapper,
    body.sb-collapsed .main-footer {
      margin-left: var(--sb-mini-w) !important;
    }

    /* Hamburger */
    #tlMenuToggle {
      width: 32px; height: 32px; border: none; background: transparent;
      cursor: pointer; border-radius: 7px;
      display: flex; align-items: center; justify-content: center;
      color: #6c757d; font-size: .85rem;
      transition: background .15s, color .15s; flex-shrink: 0;
    }
    #tlMenuToggle:hover { background: #f0f1f3; color: #1a2234; }

    /* Mobile overlay */
    #tlOverlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.45); z-index: 1049;
    }
    @media (max-width: 767px) {
      #tlSidebar { transform: translateX(-100%); transition: transform var(--sb-trans); width: var(--sb-w) !important; }
      body.sb-open #tlSidebar { transform: translateX(0); }
      body.sb-open #tlOverlay { display: block; }
      .main-header.navbar, .content-wrapper, .main-footer { margin-left: 0 !important; }
    }
    /* ══════════════════════════════════════
       TOP NAVBAR
    ══════════════════════════════════════ */
    .main-header { display: none !important; }

    .tl-topnav {
      position: fixed; top: 0;
      left: var(--sb-w); right: 0;
      height: var(--nav-h);
      background: #fff;
      border-bottom: 1px solid #e8ecf0;
      display: flex; align-items: center; gap: .6rem;
      padding: 0 1.1rem;
      z-index: 1055;
      box-shadow: 0 1px 6px rgba(0,0,0,.05);
      transition: left var(--sb-trans), background .2s, border-color .2s;
    }
    body.sb-collapsed .tl-topnav { left: var(--sb-mini-w); }
    .dark-mode .tl-topnav { background: #1a2232; border-bottom-color: #2e3849; }

    /* Hamburger — 3 bars */
    #tlMenuToggle {
      width: 32px; height: 32px; border: none; background: transparent;
      cursor: pointer; border-radius: 7px; flex-shrink: 0;
      display: flex; flex-direction: column;
      justify-content: center; align-items: center; gap: 4px;
      transition: background .15s;
    }
    #tlMenuToggle:hover { background: #f0f1f3; }
    .dark-mode #tlMenuToggle:hover { background: rgba(255,255,255,.08); }
    #tlMenuToggle span {
      display: block; width: 16px; height: 2px;
      background: #555; border-radius: 2px;
      transition: transform .2s, opacity .2s, width .2s;
      transform-origin: center;
    }
    body.sb-open #tlMenuToggle span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
    body.sb-open #tlMenuToggle span:nth-child(2) { opacity: 0; width: 0; }
    body.sb-open #tlMenuToggle span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }
    body.sb-collapsed #tlMenuToggle span:nth-child(2) { width: 10px; }

    .tl-brand {
      display: none; /* hidden in navbar — already shown in sidebar */
    }

    .tl-nav-right { margin-left: auto; display: flex; align-items: center; gap: .2rem; }

    .tl-nav-icon {
      width: 32px; height: 32px; border-radius: 7px;
      display: flex; align-items: center; justify-content: center;
      color: #6c757d; font-size: .85rem; text-decoration: none;
      transition: background .15s, color .15s;
    }
    .tl-nav-icon:hover { background: #f0f1f3; color: #1a2234; }
    .dark-mode .tl-nav-icon { color: #8a9ab5; }
    .dark-mode .tl-nav-icon:hover { background: rgba(255,255,255,.08); color: #e2e8f0; }

    /* Content offset */
    .content-wrapper, .main-footer {
      margin-left: var(--sb-w) !important;
      margin-top: var(--nav-h) !important;
      transition: margin-left var(--sb-trans) !important;
    }
    body.sb-collapsed .content-wrapper,
    body.sb-collapsed .main-footer { margin-left: var(--sb-mini-w) !important; }

    @media (max-width: 767px) {
      .tl-topnav { left: 0 !important; }
      .content-wrapper, .main-footer { margin-left: 0 !important; margin-top: var(--nav-h) !important; }
    }

    /* ════════════════════════════════════════
       DARK MODE — sidebar + navbar + content
    ════════════════════════════════════════ */
    body.dark-mode {
      background: #111827 !important;
      color: #e2e8f0 !important;
    }
    /* Sidebar dark — slightly lighter navy, readable */
    body.dark-mode #tlSidebar {
      background: #1e2840 !important;
      box-shadow: 2px 0 16px rgba(0,0,0,.35) !important;
    }
    body.dark-mode .sb-brand { border-bottom-color: rgba(255,255,255,.08) !important; }
    body.dark-mode .sb-user  { border-bottom-color: rgba(255,255,255,.08) !important; }
    body.dark-mode .sb-section { color: rgba(255,255,255,.28) !important; }
    body.dark-mode .sb-link   { color: rgba(255,255,255,.6) !important; }
    body.dark-mode .sb-link:hover { background: rgba(255,255,255,.09) !important; color: rgba(255,255,255,.95) !important; }
    body.dark-mode .sb-link.active { background: rgba(74,222,128,.1) !important; color: #fff !important; }

    /* Navbar dark */
    body.dark-mode .tl-topnav {
      background: #1a2232 !important;
      border-bottom-color: #2a3447 !important;
    }
    body.dark-mode #tlMenuToggle span { background: #94a3b8 !important; }
    body.dark-mode #tlMenuToggle:hover { background: rgba(255,255,255,.08) !important; }
    body.dark-mode .tl-nav-icon { color: #8a9ab5 !important; }
    body.dark-mode .tl-nav-icon:hover { background: rgba(255,255,255,.08) !important; color: #e2e8f0 !important; }

    /* Content area dark */
    body.dark-mode .content-wrapper { background: #111827 !important; }
    body.dark-mode .main-footer { background: #1a2232 !important; border-top-color: #2a3447 !important; color: #8a9ab5 !important; }
    body.dark-mode .bg-white,
    body.dark-mode .card { background: #1e2838 !important; border-color: #2e3849 !important; }
    body.dark-mode h1, body.dark-mode h2, body.dark-mode h3 { color: #e2e8f0 !important; }
    body.dark-mode .breadcrumb-item,
    body.dark-mode .breadcrumb-item a { color: #8a9ab5 !important; }
    body.dark-mode .breadcrumb-item.active { color: #e2e8f0 !important; }
    body.dark-mode .form-control,
    body.dark-mode .form-select { background: #2b3443 !important; border-color: #3d4a5c !important; color: #e2e8f0 !important; }
    body.dark-mode .input-group-text { background: #2b3443 !important; border-color: #3d4a5c !important; color: #94a3b8 !important; }
    body.dark-mode .table { color: #e2e8f0 !important; }
    body.dark-mode .shadow-lg { box-shadow: 0 4px 16px rgba(0,0,0,.4) !important; }

    /* Dark mode toggle icon feedback */
    body.dark-mode #dark-mode-toggle i::before { content: "\f185"; } /* fa-sun */

    /* ═══════════════════════════════════════════
       TABLE — matches faculty.php pattern
    ═══════════════════════════════════════════ */
    #classTable { font-size: .875rem; }
    #classTable thead th {
      background: #2d3a4a; color: #fff; font-size: .76rem; font-weight: 600;
      text-transform: uppercase; letter-spacing: .5px; padding: .72rem 1rem;
      white-space: nowrap; vertical-align: middle; border: none;
    }
    #classTable thead th:first-child { border-radius: 8px 0 0 0; }
    #classTable thead th:last-child  { border-radius: 0 8px 0 0; }
    #classTable tbody td { padding: .72rem 1rem; vertical-align: middle; border-bottom: 1px solid #eef0f5; }
    .dark-mode #classTable tbody td { border-bottom-color: #2e3849; }
    #classTable tbody tr { transition: background .15s ease; }
    #classTable tbody tr:hover            { background: #f0f5ff !important; }
    .dark-mode #classTable tbody tr:hover { background: #2a3547 !important; }
    #classTable tbody tr:nth-child(even)            { background: #fafbfd; }
    .dark-mode #classTable tbody tr:nth-child(even) { background: #1e2838; }

    /* cell helpers */
    .td-code   { font-family: 'Courier New', monospace; font-weight: 700; font-size: .85rem; color: #1e8449; letter-spacing: .3px; }
    .dark-mode .td-code { color: #4ade80; }
    .td-sub    { font-size: .78rem; color: #6c757d; }
    .dark-mode .td-sub { color: #94a3b8; }
    .td-faculty { font-weight: 600; }
    .td-sched  { font-size: .8rem; }
    .td-break  { font-size: .75rem; color: #6c757d; }

    /* status pills */
    .pill-active   { display:inline-block; padding:.28em .75em; border-radius:20px; font-size:.72rem; font-weight:700; background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
    .pill-inactive { display:inline-block; padding:.28em .75em; border-radius:20px; font-size:.72rem; font-weight:700; background:#f1f5f9; color:#64748b; border:1px solid #cbd5e1; }

    /* action buttons — always single row, no wrapping */
    .act-group      { display: flex; gap: 4px; justify-content: center; flex-wrap: nowrap; align-items: center; }
    .act-group .btn { padding: .3rem .55rem; font-size: .78rem; border-radius: 6px; flex-shrink: 0; }

    /* search bar */
    .search-wrap { position: relative; flex: 1; }
    .search-wrap .search-icon {
      position: absolute; left: .8rem; top: 50%; transform: translateY(-50%);
      color: #adb5bd; font-size: .82rem; pointer-events: none; z-index: 4;
    }
    .search-wrap .form-control {
      padding-left: 2.2rem; padding-right: 2.2rem;
      border: 1.5px solid #dde3ed; border-radius: 8px;
      transition: border-color .2s, box-shadow .2s;
    }
    .search-wrap .form-control:focus {
      border-color: #1e8449; box-shadow: 0 0 0 3px rgba(30,132,73,.1); outline: none;
    }
    .search-wrap .search-clear {
      position: absolute; right: .65rem; top: 50%; transform: translateY(-50%);
      width: 20px; height: 20px; border: none; background: #e8ecf0;
      border-radius: 50%; cursor: pointer; color: #6c757d;
      font-size: .65rem; display: none; align-items: center; justify-content: center;
      transition: background .15s, color .15s; z-index: 4;
    }
    .search-wrap .search-clear.visible { display: flex; }
    .search-wrap .search-clear:hover { background: #1e8449; color: #fff; }
    .result-count {
      font-size: .75rem; color: #8a96a3; white-space: nowrap;
      display: flex; align-items: center; gap: .3rem;
    }
    .result-count strong { color: #1e8449; }
    .dark-mode .search-wrap .form-control { background: #2b3443; border-color: #3d4a5c; color: #e2e8f0; }
    .dark-mode .search-wrap .search-clear { background: #3d4a5c; color: #8a96a3; }

    /* ═══════════════════════════════════════════
       CLASS MODAL
    ═══════════════════════════════════════════ */
    #classModal .modal-dialog {
      display: flex; flex-direction: column;
      max-height: calc(100vh - 2rem);
      margin: 1rem auto; max-width: 860px; width: calc(100% - 2rem);
    }
    #classModal .modal-content {
      display: flex; flex-direction: column; max-height: 100%;
      border: none; border-radius: 16px;
      box-shadow: 0 25px 60px rgba(0,0,0,.18); overflow: visible;
    }
    #classModal .modal-header {
      flex-shrink: 0;
      background: linear-gradient(135deg, #134e2e 0%, #1e8449 100%);
      color: #fff; padding: 1.4rem 1.8rem; border-bottom: none;
      border-radius: 16px 16px 0 0;
    }
    #classModal .modal-header .modal-title {
      font-size: 1.15rem; font-weight: 700; letter-spacing: .3px;
      display: flex; align-items: center; gap: 10px;
    }
    #classModal .modal-header .modal-title .header-icon {
      width: 36px; height: 36px; background: rgba(255,255,255,.18);
      border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    #classModal .btn-close { filter: invert(1) brightness(2); opacity: .85; }
    #classModal .modal-body {
      flex: 1 1 auto; overflow-y: auto; overflow-x: hidden;
      padding: 0; background: #f8f9fc;
      scrollbar-width: thin; scrollbar-color: #b8c2d0 transparent;
    }
    #classModal .modal-body::-webkit-scrollbar { width: 5px; }
    #classModal .modal-body::-webkit-scrollbar-thumb { background: #b8c2d0; border-radius: 3px; }
    #classModal .modal-body-inner { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
    #classModal .modal-footer {
      flex-shrink: 0; background: #f8f9fc;
      border-top: 1px solid #e8ecf0; padding: 1rem 1.75rem; gap: 8px;
      border-radius: 0 0 16px 16px;
    }
    #classModal .form-section {
      background: #fff; border-radius: 10px; padding: 1.25rem 1.5rem;
      border: 1px solid #e8ecf0; box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    #classModal .section-label {
      font-size: .7rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: 1px; color: #1e8449; margin-bottom: 1rem;
      padding-bottom: .5rem; border-bottom: 2px solid #d5f5e3;
      display: flex; align-items: center; gap: 7px;
    }
    #classModal .form-label {
      font-size: .78rem; font-weight: 600; color: #4a5568;
      margin-bottom: 5px; text-transform: uppercase; letter-spacing: .4px;
    }
    #classModal .form-control,
    #classModal .form-select {
      border: 1.5px solid #dde3ed; border-radius: 8px; font-size: .9rem;
      padding: .5rem .85rem; transition: border-color .2s, box-shadow .2s; background: #fff;
    }
    #classModal .form-control:focus,
    #classModal .form-select:focus {
      border-color: #1e8449; box-shadow: 0 0 0 3px rgba(30,132,73,.12); outline: none;
    }
    #classModal .form-select:disabled { background: #f1f4f8; color: #8a96a3; cursor: not-allowed; }
    #classModal .input-group-text {
      background: #edfaf3; border: 1.5px solid #dde3ed; color: #1e8449;
      font-size: .82rem; font-weight: 600; border-radius: 8px 0 0 8px;
    }
    #classModal .input-group .form-control { border-radius: 0 8px 8px 0; }

    /* ── Day pills ── */
    #classModal .day-pills { display: flex; flex-wrap: wrap; gap: 8px; }
    #classModal .day-pill { position: relative; }
    #classModal .day-pill input[type="checkbox"] {
      position: absolute; opacity: 0; width: 0; height: 0;
    }
    #classModal .day-pill label {
      display: inline-flex; align-items: center; justify-content: center;
      width: 56px; height: 42px; border-radius: 8px;
      border: 1.5px solid #dde3ed; background: #fff;
      font-size: .82rem; font-weight: 600; color: #4a5568;
      cursor: pointer; transition: all .15s ease; user-select: none;
    }
    #classModal .day-pill input[type="checkbox"]:checked + label {
      background: #1e8449; border-color: #1e8449; color: #fff;
      box-shadow: 0 2px 8px rgba(30,132,73,.3);
    }
    #classModal .day-pill label:hover { border-color: #1e8449; color: #1e8449; }

    /* ── Time divider ── */
    #classModal .time-divider {
      display: flex; align-items: flex-end; justify-content: center;
      font-size: .85rem; font-weight: 700; color: #aaa; padding-bottom: .55rem;
    }
    #classModal .optional-tag {
      font-size: .7rem; font-weight: 500; color: #8a96a3;
      background: #f1f4f8; border-radius: 4px; padding: 1px 6px;
      margin-left: 4px; text-transform: none; letter-spacing: 0;
    }

    /* ── Submit button ── */
    #classModal #modalSubmitBtn {
      background: linear-gradient(135deg, #134e2e, #1e8449);
      border: none; border-radius: 8px; font-weight: 600;
      padding: .5rem 1.6rem; letter-spacing: .3px; color: #fff;
      transition: opacity .2s, transform .15s;
    }
    #classModal #modalSubmitBtn:hover:not(:disabled) { opacity: .9; transform: translateY(-1px); }
    #classModal #modalSubmitBtn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    #classModal .btn-cancel { border-radius: 8px; font-weight: 600; padding: .5rem 1.2rem; }

    /* dark mode */
    body.dark-mode #classModal .modal-content,
    body.dark-mode #classModal .modal-body,
    body.dark-mode #classModal .modal-footer { background: #1e2838; }
    body.dark-mode #classModal .form-section { background: #253044; border-color: #2e3849; }
    body.dark-mode #classModal .form-control,
    body.dark-mode #classModal .form-select { background: #2b3443; border-color: #3d4a5c; color: #e2e8f0; }
    body.dark-mode #classModal .form-label { color: #94a3b8; }
    body.dark-mode #classModal .section-label { border-bottom-color: #1a3a2a; }
    body.dark-mode #classModal .day-pill label { background: #2b3443; border-color: #3d4a5c; color: #94a3b8; }

  </style>
</head>

<div id="tlOverlay"></div>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
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
      <span class="sb-brand-text">TERELEARN</span>
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
          <a href="./faculty.php" class="sb-link">
            <span class="sb-label">Faculty</span>
          </a>
          <a href="./facultyclass.php" class="sb-link active">
            <span class="sb-label">Class</span>
          </a>
          <a href="./facultysubject.php" class="sb-link">
            <span class="sb-label">Subject</span>
          </a>
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
          <a href="./student.php" class="sb-link">
            <span class="sb-label">Manage Accounts</span>
          </a>
        </div>
      </div>
    </nav>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper bg-muted">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0">Class Management</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="analytics.php" class="text-decoration-none text-dark">Management</a></li>
              <li class="breadcrumb-item active">Class Management</li>
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
                    <input type="text" id="globalSearch" class="form-control"
                      placeholder="Search code, subject, faculty, section…" autocomplete="off">
                    <button type="button" class="search-clear" id="searchClearBtn" title="Clear">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <div class="result-count" id="resultCount" style="display:none;">
                    Showing <strong id="resultNum">0</strong> result<span id="resultPlural">s</span>
                  </div>
                  <button class="btn btn-primary" id="btnAdd" style="border-radius:8px;font-weight:600;white-space:nowrap;">
                    <i class="fas fa-plus me-1"></i> Add Class
                  </button>
                </div>
              </div>
            </div>

            <!-- Table — same card wrapper as faculty.php -->
            <div class="row p-2">
              <div class="col-lg-12 shadow-lg p-3 border border-muted rounded bg-white">
                <div class="table-responsive">
                  <table class="table table-hover mb-0" id="classTable">
                    <thead>
                      <tr>
                        <th class="text-center" style="width:50px">#</th>
                        <th style="width:130px">Code</th>
                        <th style="min-width:160px">Faculty</th>
                        <th style="min-width:200px">Schedule</th>
                        <th style="width:140px">Semester</th>
                        <th style="width:110px">Year Level</th>
                        <th class="text-center" style="width:90px">Status</th>
                        <th class="text-center" style="width:120px;min-width:120px;">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr><td colspan="8" class="text-center py-3">
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
</div><!-- /wrapper -->


<!-- ══════════════════════════════════
     CLASS MODAL
══════════════════════════════════ -->
<div class="modal fade" id="classModal" tabindex="-1" aria-labelledby="classModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <form id="classModalForm">

        <div class="modal-header">
          <h5 class="modal-title" id="classModalLabel">
            <span class="header-icon"><i class="fas fa-chalkboard"></i></span>
            Add New Class
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="class_id" id="class_id">
          <div class="modal-body-inner">

            <!-- Section 1: Course & Subject -->
            <div class="form-section">
              <div class="section-label"><i class="fas fa-book-open"></i> Course &amp; Subject</div>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Course <span class="text-danger">*</span></label>
                  <select name="course_id" id="course_id" class="form-select" required>
                    <option value="" selected disabled>Select Course</option>
                    <?= $courseOpts ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Subject <span class="text-danger">*</span></label>
                  <select name="subject_id" id="subject_id" class="form-select" required disabled>
                    <option value="" selected disabled>Select Subject</option>
                  </select>
                  <small class="text-muted" style="font-size:.75rem;">Select a course first</small>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Section <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                    <input type="text" name="section" id="section" class="form-control" placeholder="e.g. A1" required>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section 2: Assignment -->
            <div class="form-section">
              <div class="section-label"><i class="fas fa-user-tie"></i> Assignment</div>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Professor <span class="text-danger">*</span></label>
                  <select name="faculty_id" id="faculty_id" class="form-select" required>
                    <option value="" selected disabled>Select Professor</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Semester <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="class_semester" id="class_semester" class="form-control" placeholder="e.g. 1st Sem 2025" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Year Level <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                    <input type="text" name="year_level" id="year_level" class="form-control" placeholder="e.g. 3rd Year" required>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section 3: Schedule -->
            <div class="form-section">
              <div class="section-label"><i class="fas fa-clock"></i> Class Schedule</div>
              <p class="mb-2" style="font-size:.78rem;font-weight:600;color:#4a5568;text-transform:uppercase;letter-spacing:.4px;">
                Class Time <span class="text-danger">*</span>
              </p>
              <div class="row g-2 align-items-end mb-3">
                <div class="col">
                  <label class="form-label" style="color:#8a96a3;">Start</label>
                  <input type="time" id="start_time" class="form-control" required>
                </div>
                <div class="col-auto time-divider">→</div>
                <div class="col">
                  <label class="form-label" style="color:#8a96a3;">End</label>
                  <input type="time" id="end_time" class="form-control" required>
                </div>
              </div>
              <input type="hidden" name="schedule" id="schedule">
              <p class="mb-2" style="font-size:.78rem;font-weight:600;color:#4a5568;text-transform:uppercase;letter-spacing:.4px;">
                Break Time <span class="optional-tag">Optional</span>
              </p>
              <div class="row g-2 align-items-end">
                <div class="col">
                  <label class="form-label" style="color:#8a96a3;">Break Start</label>
                  <input type="time" id="break_start" class="form-control">
                </div>
                <div class="col-auto time-divider">→</div>
                <div class="col">
                  <label class="form-label" style="color:#8a96a3;">Break End</label>
                  <input type="time" id="break_end" class="form-control">
                </div>
              </div>
              <input type="hidden" name="break_time" id="break_time">
            </div>

            <!-- Section 4: Meeting Days -->
            <div class="form-section">
              <div class="section-label"><i class="fas fa-calendar-week"></i> Meeting Days</div>
              <div class="day-pills">
                <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
                  <div class="day-pill">
                    <input class="form-check-input" type="checkbox" name="class_days[]" value="<?= $d ?>" id="day_<?= $d ?>">
                    <label for="day_<?= $d ?>"><?= $d ?></label>
                  </div>
                <?php endforeach; ?>
              </div>
              <input type="hidden" name="class_days_formatted" id="class_days_formatted">
            </div>

            <!-- Section 5: Grading Scheme -->
            <div class="form-section">
              <div class="section-label"><i class="fas fa-percentage"></i> Grading Scheme (Editable)</div>
              <div class="row g-3">
                <div class="col-md-3">
                  <label class="form-label">Recitation %</label>
                  <input type="number" min="0" max="100" step="1" name="w_recitation" id="w_recitation" class="form-control grade-weight" value="10">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Quiz %</label>
                  <input type="number" min="0" max="100" step="1" name="w_quiz" id="w_quiz" class="form-control grade-weight" value="20">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Activities/Assignments %</label>
                  <input type="number" min="0" max="100" step="1" name="w_activities" id="w_activities" class="form-control grade-weight" value="30">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Exam %</label>
                  <input type="number" min="0" max="100" step="1" name="w_exam" id="w_exam" class="form-control grade-weight" value="40">
                </div>
              </div>
              <div class="row g-3 mt-1">
                <div class="col-md-3">
                  <label class="form-label"># Recitations</label>
                  <input type="number" min="0" step="1" name="cnt_recitation" id="cnt_recitation" class="form-control" value="0">
                </div>
                <div class="col-md-3">
                  <label class="form-label"># Quizzes</label>
                  <input type="number" min="0" step="1" name="cnt_quiz" id="cnt_quiz" class="form-control" value="0">
                </div>
                <div class="col-md-3">
                  <label class="form-label"># Activities/Assignments</label>
                  <input type="number" min="0" step="1" name="cnt_activities" id="cnt_activities" class="form-control" value="0">
                </div>
                <div class="col-md-3">
                  <label class="form-label"># Exams</label>
                  <input type="number" min="0" step="1" name="cnt_exam" id="cnt_exam" class="form-control" value="0">
                </div>
              </div>
              <div id="gradeWeightHint" style="margin-top:.65rem;font-size:.78rem;font-weight:700;color:#0d7a5e;">
                Total Weight: 100%
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light btn-cancel" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i> Cancel
          </button>
          <button type="submit" class="btn" id="modalSubmitBtn">
            <i class="fas fa-check me-1"></i> Save Class
          </button>
        </div>

      </form>
    </div>
  </div>
</div>


<!-- Scripts -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="plugins/chart.js/Chart.min.js"></script>
<script src="plugins/sparklines/sparkline.js"></script>
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="dist/js/adminlte.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
/* ══  TOAST  ══ */
window.showToast = function(msg, type = 'success') {
  Swal.fire({
    toast: true, position: 'top-end',
    icon: { success:'success', error:'error', info:'info', warning:'warning' }[type] || 'info',
    title: msg, showConfirmButton: false, timer: 3500, timerProgressBar: true,
  });
};

/* ══  GLOBALS  ══ */
let dropdownData = { courses: [], subjects: [], faculty: [] };
let classModal;

$(function() {
  classModal = new bootstrap.Modal(document.getElementById('classModal'));

  loadDropdownData();
  loadClassTable();

  $('#btnAdd').on('click', () => showModal('add'));

  // Instant client-side filter
  $('#globalSearch').on('input', function() {
    const q = $(this).val().trim();
    $('#searchClearBtn').toggleClass('visible', q.length > 0);
    renderFilteredClasses(q);
  });

  $('#searchClearBtn').on('click', function() {
    $('#globalSearch').val('').trigger('input').focus();
  });

  $('#classModalForm').on('submit', saveClass);
  $('.grade-weight').on('input', updateGradeWeightHint);
  updateGradeWeightHint();
});

/* ══  DROPDOWNS  ══ */
function loadDropdownData() {
  $.getJSON('API/Class/fetch_dropdown_data_class.php', res => {
    if (res.status === 'success') {
      dropdownData = res.data;
      populateSelect('course_id',  '<option value="" selected disabled>Select Course</option>',    res.data.courses,  'course_code', 'course_name');
      populateSelect('faculty_id', '<option value="" selected disabled>Select Professor</option>', res.data.faculty, 'full_name',  'faculty_number', true);
      $('#course_id').on('change', filterSubjects);
    } else {
      showToast(res.message, 'error');
    }
  });
}

function populateSelect(id, def, arr, f1, f2, combo = false) {
  const $sel = $(`#${id}`);
  $sel.html(def);
  arr.forEach(i => {
    const txt = combo ? `${i[f1]} (${i[f2]})` : `${i[f1]} - ${i[f2]}`;
    $sel.append(`<option value="${i.id}">${txt}</option>`);
  });
}

function filterSubjects() {
  // Always read from the DOM — called via .on('change') event only
  const cid  = $('#course_id').val();
  const $sub = $('#subject_id');
  $sub.html('<option value="" selected disabled>Select Subject</option>').prop('disabled', true);
  if (!cid) return;
  const list = dropdownData.subjects.filter(s => String(s.course_id) === String(cid));
  if (!list.length) {
    $sub.html('<option value="" selected disabled>No subjects for this course</option>');
    return;
  }
  list.forEach(s => $sub.append(`<option value="${s.id}">${s.subject_code} - ${s.subject_name}</option>`));
  $sub.prop('disabled', false);
}

/* ══  TABLE  ══ */
let allClasses = []; // cached — filter client-side instantly

function loadClassTable() {
  const $body = $('#classTable tbody');
  $body.html('<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading…</td></tr>');
  $.getJSON('API/Class/fetch_classes_new.php', res => {
    if (res.status === 'success') {
      allClasses = res.data || [];
      renderFilteredClasses($('#globalSearch').val().trim());
    } else {
      showToast(res.message, 'error');
      $body.html(`<tr><td colspan="8" class="text-center text-danger py-3">${res.message}</td></tr>`);
    }
  });
}

function applyClassFilter(q) {
  if (!q) return allClasses;
  const lq = q.toLowerCase();
  return allClasses.filter(c =>
    (c.class_code    || '').toLowerCase().includes(lq) ||
    (c.subject_name  || '').toLowerCase().includes(lq) ||
    (c.subject_code  || '').toLowerCase().includes(lq) ||
    (c.faculty_name  || '').toLowerCase().includes(lq) ||
    (c.course_code   || '').toLowerCase().includes(lq) ||
    (c.section       || '').toLowerCase().includes(lq) ||
    (c.class_semester|| '').toLowerCase().includes(lq) ||
    (c.year_level    || '').toLowerCase().includes(lq) ||
    (c.class_days    || '').toLowerCase().includes(lq)
  );
}

function renderFilteredClasses(q) {
  const list  = applyClassFilter(q);
  const $body = $('#classTable tbody');

  // update result count badge
  if (q) {
    $('#resultNum').text(list.length);
    $('#resultPlural').text(list.length === 1 ? '' : 's');
    $('#resultCount').show();
  } else {
    $('#resultCount').hide();
  }

  if (!list.length) {
    $body.html('<tr><td colspan="8" class="text-center text-muted py-3">No classes match your search.</td></tr>');
    return;
  }
  displayClasses(list);
}

function fmt12(t) {
  if (!t) return '';
  const [h, m] = t.split(':');
  const hr = parseInt(h, 10);
  return `${hr % 12 || 12}:${m} ${hr >= 12 ? 'PM' : 'AM'}`;
}

function displayClasses(list) {
  const $body = $('#classTable tbody');
  $body.empty();
  list.forEach((c, i) => {
    const active  = parseInt(c.is_active) === 1;
    const pill    = active
      ? '<span class="pill-active">Active</span>'
      : '<span class="pill-inactive">Inactive</span>';
    const sched   = c.schedule   ? c.schedule.split(' - ').map(t => fmt12(t)).join(' – ')    : '—';
    const brk     = c.break_time ? c.break_time.split('-').map(t => fmt12(t.trim())).join(' – ') : '';
    const subCode = (c.class_code || '').split('-')[0];

    $body.append(`
      <tr>
        <td class="text-center">${i + 1}</td>
        <td>
          <span class="td-code">${subCode}</span><br>
          <span class="td-sub">${c.course_code || ''} · ${c.section || ''}</span>
        </td>
        <td class="td-faculty">${c.faculty_name || '—'}</td>
        <td class="td-sched">
          ${c.class_days || '—'}<br>
          <small class="text-muted">${sched}</small>
          ${brk ? `<br><small class="td-break">Break: ${brk}</small>` : ''}
        </td>
        <td>${c.class_semester || '—'}</td>
        <td>${c.year_level || '—'}</td>
        <td class="text-center">${pill}</td>
        <td class="text-center">
          <div class="act-group">
            <button class="btn btn-sm ${active ? 'btn-danger' : 'btn-success'}"
              onclick="toggleClassStatus('${c.id}', ${active ? 0 : 1})"
              title="${active ? 'Deactivate' : 'Activate'}">
              <i class="fas ${active ? 'fa-ban' : 'fa-check-circle'}"></i>
            </button>
            <button class="btn btn-sm btn-warning" onclick="showModal('edit','${c.id}')" title="Edit">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-dark" onclick="deleteClass('${c.id}')" title="Delete">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </td>
      </tr>`);
  });
}

/* ══  MODAL  ══ */
function showModal(mode, id = null) {
  $('#classModalForm')[0].reset();
  $('#class_id').val('');
  $('#subject_id').html('<option value="" selected disabled>Select Subject</option>').prop('disabled', true);
  $('input[name="class_days[]"]').prop('checked', false);
  $('#w_recitation').val(10);
  $('#w_quiz').val(20);
  $('#w_activities').val(30);
  $('#w_exam').val(40);
  $('#cnt_recitation').val(0);
  $('#cnt_quiz').val(0);
  $('#cnt_activities').val(0);
  $('#cnt_exam').val(0);
  updateGradeWeightHint();
  $('#classModalLabel').html(
    `<span class="header-icon"><i class="fas fa-chalkboard"></i></span>
     ${mode === 'add' ? 'Add New Class' : 'Edit Class'}`
  );
  if (mode === 'edit' && id) populateEdit(id);
  classModal.show();
}

function populateEdit(id) {
  $.getJSON('API/class/get_class_detail.php', { id }, res => {
    if (res.status !== 'success') { showToast(res.message, 'error'); return; }
    const c = res.class;

    // Basic fields
    $('#class_id').val(c.id);
    $('#section').val(c.section           || '');
    $('#faculty_id').val(c.faculty_id     || '');
    $('#class_semester').val(c.class_semester || '');
    $('#year_level').val(c.year_level     || '');

    // Course -> repopulate subjects synchronously -> set subject value
    $('#course_id').val(c.course_id || '');
    const $sub = $('#subject_id');
    $sub.html('<option value="" selected disabled>Select Subject</option>');
    if (c.course_id) {
      const list = dropdownData.subjects.filter(s => String(s.course_id) === String(c.course_id));
      if (list.length) {
        list.forEach(s => $sub.append(`<option value="${s.id}">${s.subject_code} - ${s.subject_name}</option>`));
        $sub.prop('disabled', false);
      } else {
        $sub.prop('disabled', true);
      }
    } else {
      $sub.prop('disabled', true);
    }
    $sub.val(c.subject_id || ''); // set AFTER options are inserted

    // Schedule split on " - " (not bare "-" which hits time colons)
    if (c.schedule) {
      const p = c.schedule.split(' - ');
      if (p.length >= 2) { $('#start_time').val(p[0].trim()); $('#end_time').val(p[1].trim()); }
    }

    // Break time — handle both "HH:MM - HH:MM" and "HH:MM-HH:MM"
    if (c.break_time) {
      const sep = c.break_time.includes(' - ') ? ' - ' : '-';
      const b   = c.break_time.split(sep);
      if (b.length >= 2) { $('#break_start').val(b[0].trim()); $('#break_end').val(b[1].trim()); }
    }

    // Days
    if (c.class_days) {
      c.class_days.split(',').map(d => d.trim()).forEach(d => $(`#day_${d}`).prop('checked', true));
    }

    // Grading scheme
    const gs = c.grading_scheme || {};
    $('#w_recitation').val(gs.weight_recitation ?? 10);
    $('#w_quiz').val(gs.weight_quiz ?? 20);
    $('#w_activities').val(gs.weight_activities ?? 30);
    $('#w_exam').val(gs.weight_exam ?? 40);
    $('#cnt_recitation').val(gs.count_recitation ?? 0);
    $('#cnt_quiz').val(gs.count_quiz ?? 0);
    $('#cnt_activities').val(gs.count_activities ?? 0);
    $('#cnt_exam').val(gs.count_exam ?? 0);
    updateGradeWeightHint();
  });
}

function updateGradeWeightHint() {
  const wr = parseInt($('#w_recitation').val() || 0, 10);
  const wq = parseInt($('#w_quiz').val() || 0, 10);
  const wa = parseInt($('#w_activities').val() || 0, 10);
  const we = parseInt($('#w_exam').val() || 0, 10);
  const total = wr + wq + wa + we;
  const ok = total === 100;
  $('#gradeWeightHint')
    .text(`Total Weight: ${total}% ${ok ? '✓' : '(must be 100%)'}`)
    .css('color', ok ? '#0d7a5e' : '#d93025');
  return ok;
}

/* ══  SAVE  ══ */
function saveClass(e) {
  e.preventDefault();
  const btn        = $('#modalSubmitBtn');
  const startTime  = $('#start_time').val();
  const endTime    = $('#end_time').val();
  const breakStart = $('#break_start').val();
  const breakEnd   = $('#break_end').val();
  const days       = [];
  $('input[name="class_days[]"]:checked').each(function() { days.push($(this).val()); });

  if (!updateGradeWeightHint()) {
    showToast('Grading weights must total exactly 100%.', 'error');
    return;
  }

  const formData = new FormData(this);
  formData.set('schedule',             startTime && endTime ? `${startTime} - ${endTime}` : '');
  formData.set('break_time',           breakStart && breakEnd ? `${breakStart}-${breakEnd}` : '');
  formData.set('class_days_formatted', days.join(', '));

  btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving…');
  $.ajax({
    url: 'API/class/saveclass.php', type: 'POST',
    data: formData, processData: false, contentType: false, dataType: 'json',
    success(res) {
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Save Class');
      if (res.status === 'success') { classModal.hide(); showToast(res.message, 'success'); loadClassTable(); }
      else showToast(res.message, 'error');
    },
    error(xhr) {
      btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Save Class');
      showToast('Server error: ' + xhr.status, 'error');
    }
  });
}

/* ══  TOGGLE STATUS  ══ */
window.toggleClassStatus = function(id, newVal) {
  Swal.fire({
    title: 'Toggle Status?',
    text: newVal === 1 ? 'This will activate the class.' : 'This will deactivate the class.',
    icon: 'question', showCancelButton: true,
    confirmButtonColor: newVal === 1 ? '#28a745' : '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: newVal === 1 ? 'Yes, activate' : 'Yes, deactivate',
    cancelButtonText: 'Cancel'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.post('API/Class/toggleActiveClass.php', { class_id: id, is_active: newVal }, res => {
      if (res.status === 'success') { showToast(res.message, 'success'); loadClassTable(); }
      else showToast(res.message, 'error');
    }, 'json');
  });
};

/* ══  DELETE  ══ */
window.deleteClass = function(id) {
  Swal.fire({
    title: 'Delete this class?',
    text: 'This action cannot be undone.',
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.post('API/Class/deleteclass.php', { id }, res => {
      if (res.status === 'success') { showToast(res.message, 'success'); loadClassTable(); }
      else showToast(res.message, 'error');
    }, 'json');
  });
};
</script>

<script>
(function() {
  var sidebar = document.getElementById('tlSidebar');
  var overlay = document.getElementById('tlOverlay');
  var body    = document.body;
  var KEY     = 'tl_sb';
  if (window.innerWidth >= 768 && localStorage.getItem(KEY) === 'c') {
    sidebar.classList.add('collapsed');
    body.classList.add('sb-collapsed');
  }
  document.getElementById('tlMenuToggle').addEventListener('click', function() {
    if (window.innerWidth < 768) {
      body.classList.toggle('sb-open');
    } else {
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

</html>
