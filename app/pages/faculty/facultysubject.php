<?php
/* facultysubject.php  –  Tere LEARN | Course / Subject Management */
require_once dirname(__DIR__, 2) . '/config/db_connect.php';
$res = $conn->query("SELECT id,course_code,course_name FROM tblcourse WHERE is_deleted=0 ORDER BY course_code");
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
  if (localStorage.getItem(KEY) === '1') {
    document.documentElement.classList.add('dark-mode');
  }
  document.addEventListener('DOMContentLoaded', function() {
    var body = document.body;
    if (localStorage.getItem(KEY) === '1') body.classList.add('dark-mode');
    var btn  = document.getElementById('dark-mode-toggle');
    if (!btn) return;
    var icon = btn.querySelector('i');
    if (icon) icon.className = localStorage.getItem(KEY) === '1' ? 'fas fa-sun' : 'fas fa-lightbulb';
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
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
  <title>TERELEARN | Subject Management</title>
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

    .main-header.navbar { margin-left: var(--sb-w) !important; transition: margin-left var(--sb-trans) !important; }
    .content-wrapper, .main-footer { margin-left: var(--sb-w) !important; transition: margin-left var(--sb-trans) !important; }
    body.sb-collapsed .main-header.navbar,
    body.sb-collapsed .content-wrapper,
    body.sb-collapsed .main-footer { margin-left: var(--sb-mini-w) !important; }

    #tlMenuToggle {
      width: 32px; height: 32px; border: none; background: transparent;
      cursor: pointer; border-radius: 7px; flex-shrink: 0;
      display: flex; flex-direction: column;
      justify-content: center; align-items: center; gap: 4px;
      transition: background .15s;
    }
    #tlMenuToggle:hover { background: #f0f1f3; }

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

    /* ══ TOP NAVBAR ══ */
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

    #tlMenuToggle span {
      display: block; width: 16px; height: 2px;
      background: #555; border-radius: 2px;
      transition: transform .2s, opacity .2s, width .2s;
      transform-origin: center;
    }
    .dark-mode #tlMenuToggle:hover { background: rgba(255,255,255,.08); }
    .dark-mode #tlMenuToggle span { background: #94a3b8 !important; }
    body.sb-open #tlMenuToggle span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
    body.sb-open #tlMenuToggle span:nth-child(2) { opacity: 0; width: 0; }
    body.sb-open #tlMenuToggle span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }
    body.sb-collapsed #tlMenuToggle span:nth-child(2) { width: 10px; }

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

    /* ══ DARK MODE ══ */
    body.dark-mode { background: #111827 !important; color: #e2e8f0 !important; }
    body.dark-mode #tlSidebar { background: #1e2840 !important; box-shadow: 2px 0 16px rgba(0,0,0,.35) !important; }
    body.dark-mode .sb-brand { border-bottom-color: rgba(255,255,255,.08) !important; }
    body.dark-mode .sb-user  { border-bottom-color: rgba(255,255,255,.08) !important; }
    body.dark-mode .sb-section { color: rgba(255,255,255,.28) !important; }
    body.dark-mode .sb-link   { color: rgba(255,255,255,.6) !important; }
    body.dark-mode .sb-link:hover { background: rgba(255,255,255,.09) !important; color: rgba(255,255,255,.95) !important; }
    body.dark-mode .sb-link.active { background: rgba(74,222,128,.1) !important; color: #fff !important; }
    body.dark-mode .tl-topnav { background: #1a2232 !important; border-bottom-color: #2a3447 !important; }
    body.dark-mode .tl-nav-icon { color: #8a9ab5 !important; }
    body.dark-mode .tl-nav-icon:hover { background: rgba(255,255,255,.08) !important; color: #e2e8f0 !important; }
    body.dark-mode .content-wrapper { background: #111827 !important; }
    body.dark-mode .main-footer { background: #1a2232 !important; border-top-color: #2a3447 !important; color: #8a9ab5 !important; }
    body.dark-mode .bg-white, body.dark-mode .card { background: #1e2838 !important; border-color: #2e3849 !important; }
    body.dark-mode h1, body.dark-mode h2, body.dark-mode h3 { color: #e2e8f0 !important; }
    body.dark-mode .breadcrumb-item, body.dark-mode .breadcrumb-item a { color: #8a9ab5 !important; }
    body.dark-mode .breadcrumb-item.active { color: #e2e8f0 !important; }
    body.dark-mode .form-control, body.dark-mode .form-select { background: #2b3443 !important; border-color: #3d4a5c !important; color: #e2e8f0 !important; }
    body.dark-mode .input-group-text { background: #2b3443 !important; border-color: #3d4a5c !important; color: #94a3b8 !important; }
    body.dark-mode .table { color: #e2e8f0 !important; }
    body.dark-mode .shadow-lg { box-shadow: 0 4px 16px rgba(0,0,0,.4) !important; }
    body.dark-mode #dark-mode-toggle i::before { content: "\f185"; }

    /* ══ SEARCH BAR ══ */
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
      border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.1); outline: none;
    }
    .search-wrap .search-clear {
      position: absolute; right: .65rem; top: 50%; transform: translateY(-50%);
      width: 20px; height: 20px; border: none; background: #e8ecf0;
      border-radius: 50%; cursor: pointer; color: #6c757d;
      font-size: .65rem; display: none; align-items: center; justify-content: center;
      transition: background .15s, color .15s; z-index: 4;
    }
    .search-wrap .search-clear.visible { display: flex; }
    .search-wrap .search-clear:hover { background: #7c3aed; color: #fff; }
    .result-count {
      font-size: .75rem; color: #8a96a3; white-space: nowrap;
      display: flex; align-items: center; gap: .3rem;
    }
    .result-count strong { color: #7c3aed; }
    .dark-mode .search-wrap .form-control { background: #2b3443; border-color: #3d4a5c; color: #e2e8f0; }
    .dark-mode .search-wrap .search-clear { background: #3d4a5c; color: #8a96a3; }

    /* ══ TABLE ══ */
    #subjectTable { font-size: .875rem; }
    #subjectTable thead th {
      background: #2d3a4a; color: #fff; font-size: .76rem; font-weight: 600;
      text-transform: uppercase; letter-spacing: .5px; padding: .72rem 1rem;
      white-space: nowrap; vertical-align: middle; border: none;
    }
    #subjectTable thead th:first-child { border-radius: 8px 0 0 0; }
    #subjectTable thead th:last-child  { border-radius: 0 8px 0 0; }
    #subjectTable tbody td { padding: .72rem 1rem; vertical-align: middle; border-bottom: 1px solid #eef0f5; }
    .dark-mode #subjectTable tbody td { border-bottom-color: #2e3849; }
    #subjectTable tbody tr { transition: background .15s ease; }
    #subjectTable tbody tr:hover { background: #f0f5ff !important; }
    .dark-mode #subjectTable tbody tr:hover { background: #2a3547 !important; }
    #subjectTable tbody tr:nth-child(even) { background: #fafbfd; }
    .dark-mode #subjectTable tbody tr:nth-child(even) { background: #1e2838; }
    .td-code { font-family: 'Courier New', monospace; font-weight: 700; font-size: .85rem; color: #7c3aed; letter-spacing: .3px; }
    .dark-mode .td-code { color: #a78bfa; }
    .td-name { font-weight: 600; }
    .td-prog { font-size: .8rem; color: #6c757d; }
    .dark-mode .td-prog { color: #94a3b8; }
    .pill-active   { display:inline-block; padding:.28em .75em; border-radius:20px; font-size:.72rem; font-weight:700; background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
    .pill-inactive { display:inline-block; padding:.28em .75em; border-radius:20px; font-size:.72rem; font-weight:700; background:#f1f5f9; color:#64748b; border:1px solid #cbd5e1; }
    .act-group      { display: flex; gap: 4px; justify-content: center; flex-wrap: nowrap; align-items: center; }
    .act-group .btn { padding: .3rem .55rem; font-size: .78rem; border-radius: 6px; flex-shrink: 0; }

    /* ══ SUBJECT MODAL ══ */
    #subjectModal .modal-content { border:none; border-radius:16px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,.18); }
    #subjectModal .modal-header { background:linear-gradient(135deg,#4a1a7a 0%,#7c3aed 100%); color:#fff; padding:1.4rem 1.8rem; border-bottom:none; }
    #subjectModal .modal-header .modal-title { font-size:1.15rem; font-weight:700; letter-spacing:.3px; display:flex; align-items:center; gap:10px; }
    #subjectModal .modal-header .modal-title .header-icon { width:36px; height:36px; background:rgba(255,255,255,.18); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; }
    #subjectModal .btn-close { filter:invert(1) brightness(2); opacity:.85; }
    #subjectModal .modal-body { padding:0; background:#f8f9fc; }
    #subjectModal .modal-body-inner { padding:1.5rem; display:flex; flex-direction:column; gap:1rem; }
    #subjectModal .form-section { background:#fff; border-radius:10px; padding:1.25rem 1.5rem; border:1px solid #e8ecf0; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    #subjectModal .section-label { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#7c3aed; margin-bottom:1rem; padding-bottom:.5rem; border-bottom:2px solid #ede9fe; display:flex; align-items:center; gap:7px; }
    #subjectModal .form-label { font-size:.78rem; font-weight:600; color:#4a5568; margin-bottom:5px; text-transform:uppercase; letter-spacing:.4px; }
    #subjectModal .form-control, #subjectModal .form-select { border:1.5px solid #dde3ed; border-radius:8px; font-size:.9rem; padding:.5rem .85rem; transition:border-color .2s,box-shadow .2s; background:#fff; }
    #subjectModal .form-control:focus, #subjectModal .form-select:focus { border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,.12); outline:none; }
    #subjectModal .input-group-text { background:#f5f3ff; border:1.5px solid #dde3ed; color:#7c3aed; font-size:.82rem; font-weight:600; border-radius:8px 0 0 8px; }
    #subjectModal .input-group .form-control { border-radius:0 8px 8px 0; }
    #subjectModal .modal-footer { background:#f8f9fc; border-top:1px solid #e8ecf0; padding:1rem 1.5rem; gap:8px; }
    #subjectModal #modalSubmitBtn { background:linear-gradient(135deg,#4a1a7a,#7c3aed); border:none; border-radius:8px; font-weight:600; padding:.5rem 1.6rem; letter-spacing:.3px; color:#fff; transition:opacity .2s,transform .15s; }
    #subjectModal #modalSubmitBtn:hover:not(:disabled) { opacity:.9; transform:translateY(-1px); }
    #subjectModal #modalSubmitBtn:disabled { opacity:.6; cursor:not-allowed; }
    #subjectModal .btn-cancel { border-radius:8px; font-weight:600; padding:.5rem 1.2rem; }
    .dark-mode #subjectModal .form-section { background:#343a40; border-color:#4a5568; }
    .dark-mode #subjectModal .modal-body, .dark-mode #subjectModal .modal-footer { background:#2b3443; }
    .dark-mode #subjectModal .form-control, .dark-mode #subjectModal .form-select { background:#3d4655; border-color:#4a5568; color:#e2e8f0; }

    /* ══ MANAGE PROGRAMS MODAL ══ */
    #manageProgramsModal .modal-content { border:none; border-radius:16px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,.18); }
    #manageProgramsModal .modal-header { background:linear-gradient(135deg,#1a3c5e 0%,#2563a8 100%); color:#fff; padding:1.4rem 1.8rem; border-bottom:none; }
    #manageProgramsModal .btn-close { filter:invert(1) brightness(2); opacity:.85; }
    #manageProgramsModal .modal-body { padding:0; background:#f8f9fc; min-height:460px; display:flex; flex-direction:column; }
    #manageProgramsModal .modal-footer { background:#f8f9fc; border-top:1px solid #e8ecf0; padding:.85rem 1.25rem; }

    /* toolbar */
    .prog-toolbar { display:flex; align-items:center; gap:.6rem; padding:1rem 1.25rem .75rem; border-bottom:1px solid #e8ecf0; background:#fff; }
    .prog-search-wrap { position:relative; flex:1; }
    .prog-search-wrap i { position:absolute; left:.75rem; top:50%; transform:translateY(-50%); color:#adb5bd; font-size:.82rem; pointer-events:none; }
    .prog-search-input { width:100%; padding:.45rem .75rem .45rem 2.1rem; border:1.5px solid #dde3ed; border-radius:8px; font-size:.88rem; font-family:inherit; transition:border-color .2s,box-shadow .2s; background:#f8f9fc; }
    .prog-search-input:focus { border-color:#2563a8; box-shadow:0 0 0 3px rgba(37,99,168,.1); outline:none; }
    .prog-add-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem 1rem; border-radius:8px; border:none; background:linear-gradient(135deg,#1a3c5e,#2563a8); color:#fff; font-size:.82rem; font-weight:600; cursor:pointer; white-space:nowrap; transition:opacity .2s,transform .15s; }
    .prog-add-btn:hover { opacity:.9; transform:translateY(-1px); }

    /* two-column layout */
    .prog-panel { display:flex; flex:1; min-height:0; overflow:hidden; }
    .prog-list-col { flex:1; overflow-y:auto; padding:.85rem 1rem; display:flex; flex-direction:column; gap:.5rem; }
    .prog-list-col::-webkit-scrollbar { width:4px; }
    .prog-list-col::-webkit-scrollbar-thumb { background:#c7d9f5; border-radius:3px; }

    /* inline form */
    .prog-form-col { width:0; overflow:hidden; border-left:1px solid #e8ecf0; background:#fff; transition:width .28s cubic-bezier(.4,0,.2,1); flex-shrink:0; }
    .prog-form-col.open { width:290px; }
    .prog-form-inner { padding:1.1rem 1.25rem; width:290px; }
    .prog-form-title { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#2563a8; margin-bottom:1rem; padding-bottom:.5rem; border-bottom:2px solid #e8f0fe; display:flex; align-items:center; justify-content:space-between; }
    .prog-form-close { width:22px; height:22px; border:none; background:#f1f5fb; border-radius:6px; cursor:pointer; color:#6c757d; font-size:.75rem; display:flex; align-items:center; justify-content:center; transition:background .15s; }
    .prog-form-close:hover { background:#e8f0fe; color:#2563a8; }
    .prog-field-label { font-size:.72rem; font-weight:600; color:#4a5568; text-transform:uppercase; letter-spacing:.4px; margin-bottom:.3rem; display:block; }
    .prog-field { width:100%; padding:.45rem .75rem; border:1.5px solid #dde3ed; border-radius:8px; font-size:.88rem; font-family:inherit; transition:border-color .2s,box-shadow .2s; margin-bottom:.75rem; }
    .prog-field:focus { border-color:#2563a8; box-shadow:0 0 0 3px rgba(37,99,168,.1); outline:none; }
    .prog-save-btn { width:100%; padding:.5rem; border:none; border-radius:8px; background:linear-gradient(135deg,#1a3c5e,#2563a8); color:#fff; font-size:.85rem; font-weight:600; cursor:pointer; transition:opacity .2s,transform .15s; margin-top:.25rem; }
    .prog-save-btn:hover:not(:disabled) { opacity:.9; transform:translateY(-1px); }
    .prog-save-btn:disabled { opacity:.55; cursor:not-allowed; transform:none; }

    /* program cards */
    .program-card { background:#fff; border:1.5px solid #e8ecf0; border-radius:10px; padding:.75rem 1rem; display:flex; align-items:center; gap:.75rem; transition:border-color .15s,box-shadow .15s,background .15s; animation:cardIn .2s ease forwards; }
    @keyframes cardIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
    .program-card:hover { border-color:#2563a8; box-shadow:0 2px 8px rgba(37,99,168,.08); }
    .program-card.editing { border-color:#2563a8; background:#f0f5ff; }
    .prog-badge { width:38px; height:38px; border-radius:10px; background:linear-gradient(135deg,#1a3c5e,#2563a8); color:#fff; font-size:.72rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; text-transform:uppercase; letter-spacing:.3px; text-align:center; line-height:1.2; }
    .prog-info { flex:1; min-width:0; }
    .prog-code { font-size:.72rem; font-weight:700; color:#2563a8; text-transform:uppercase; letter-spacing:.5px; }
    .prog-name { font-size:.88rem; font-weight:600; color:#1a202c; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .prog-subcount { font-size:.7rem; color:#8a96a3; margin-top:.1rem; }
    .prog-actions { display:flex; gap:4px; flex-shrink:0; }
    .prog-actions .btn { padding:.25rem .5rem; font-size:.72rem; border-radius:6px; }
    .programs-empty { text-align:center; padding:3rem 1rem; color:#8a96a3; font-size:.88rem; }
    .programs-empty i { font-size:2.2rem; display:block; margin-bottom:.65rem; color:#c7d9f5; }
    .prog-count-badge { display:inline-block; background:#e8f0fe; color:#2563a8; font-size:.65rem; font-weight:700; padding:.1rem .45rem; border-radius:20px; margin-left:.4rem; }

    /* dark mode — programs modal */
    .dark-mode .prog-toolbar     { background:#1e2838; border-bottom-color:#2e3849; }
    .dark-mode .prog-search-input { background:#2b3443; border-color:#3d4a5c; color:#e2e8f0; }
    .dark-mode .prog-form-col    { background:#1e2838; border-left-color:#2e3849; }
    .dark-mode .prog-field       { background:#2b3443; border-color:#3d4a5c; color:#e2e8f0; }
    .dark-mode .program-card     { background:#1e2838; border-color:#2e3849; }
    .dark-mode .program-card.editing { background:#1a2540; border-color:#2563a8; }
    .dark-mode .prog-name        { color:#e2e8f0; }
    .dark-mode .prog-form-title  { border-bottom-color:#1e3a6e; }
    .dark-mode .prog-form-close  { background:#2b3443; color:#8a96a3; }
    .dark-mode .prog-form-close:hover { background:#1e3a6e; color:#60a5fa; }
    .dark-mode #manageProgramsModal .modal-body,
    .dark-mode #manageProgramsModal .modal-footer { background:#2b3443; }
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
          <a href="./facultyclass.php" class="sb-link">
            <span class="sb-label">Class</span>
          </a>
          <a href="./facultysubject.php" class="sb-link active">
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
          <div class="col-sm-6"><h1 class="m-0">Subject Management</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="analytics.php" class="text-decoration-none text-dark">Management</a></li>
              <li class="breadcrumb-item active">Subject Management</li>
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
                      placeholder="Search code, name, program…" autocomplete="off">
                    <button type="button" class="search-clear" id="searchClearBtn" title="Clear">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <div class="result-count" id="resultCount" style="display:none;">
                    Showing <strong id="resultNum">0</strong> result<span id="resultPlural">s</span>
                  </div>
                  <button class="btn btn-outline-primary" id="btnManagePrograms" style="border-radius:8px;font-weight:600;white-space:nowrap;">
                    <i class="fas fa-university me-1"></i> Programs
                  </button>
                  <button class="btn btn-primary" id="btnAdd" style="border-radius:8px;font-weight:600;white-space:nowrap;">
                    <i class="fas fa-plus me-1"></i> Add Subject
                  </button>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="row p-2">
              <div class="col-lg-12 shadow-lg p-3 border border-muted rounded bg-white">
                <div class="table-responsive">
                  <table class="table table-hover mb-0" id="subjectTable">
                    <thead>
                      <tr>
                        <th class="text-center" style="width:50px">#</th>
                        <th style="width:120px">Code</th>
                        <th style="min-width:200px">Subject Name</th>
                        <th style="min-width:180px">Program</th>
                        <th class="text-center" style="width:100px">Status</th>
                        <th class="text-center" style="width:140px">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr><td colspan="6" class="text-center py-3">
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
    <strong>Copyright &copy; 2025-2026 <a href="#">TERELEARN</a>.</strong> All rights reserved.
    <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0.0</div>
  </footer>
</div><!-- /wrapper -->


<!-- ══════════════════════════════════
     ADD / EDIT SUBJECT MODAL
══════════════════════════════════ -->
<div class="modal fade" id="subjectModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form id="subjectModalForm">
        <div class="modal-header">
          <h5 class="modal-title" id="subjectModalLabel">
            <span class="header-icon"><i class="fas fa-book"></i></span>
            Add New Subject
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="subject_id" id="subject_id">
          <div class="modal-body-inner">

            <div class="form-section">
              <div class="section-label"><i class="fas fa-tag"></i> Identification</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Subject Code <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                    <input type="text" name="subject_code" id="subject_code" class="form-control"
                      placeholder="e.g. ITELECT1" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Program <span class="text-danger">*</span></label>
                  <select name="course_id" id="course_id" class="form-select" required>
                    <option value="">— Select Program —</option>
                    <?= $courseOpts ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="section-label"><i class="fas fa-align-left"></i> Subject Details</div>
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-pen"></i></span>
                    <input type="text" name="subject_name" id="subject_name" class="form-control"
                      placeholder="e.g. IT Elective 1" required>
                  </div>
                  <small class="text-muted" style="font-size:.75rem;">Enter the full descriptive name of the subject.</small>
                </div>
              </div>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light btn-cancel" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i> Cancel
          </button>
          <button type="button" class="btn btn-outline-secondary" id="btnResetSubject">
            <i class="fas fa-undo me-1"></i> Reset
          </button>
          <button type="submit" class="btn" id="modalSubmitBtn">
            <i class="fas fa-check me-1"></i> Save Subject
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════
     MANAGE PROGRAMS MODAL
══════════════════════════════════ -->
<div class="modal fade" id="manageProgramsModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width:720px;">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" style="font-size:1.1rem;font-weight:700;display:flex;align-items:center;gap:10px;">
          <span style="width:34px;height:34px;background:rgba(255,255,255,.18);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.95rem;">
            <i class="fas fa-university"></i>
          </span>
          Manage Programs
          <span class="prog-count-badge" id="progCountBadge">0</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="prog-toolbar">
        <div class="prog-search-wrap">
          <i class="fas fa-search"></i>
          <input type="text" class="prog-search-input" id="progSearch" placeholder="Search programs…" autocomplete="off">
        </div>
        <button type="button" class="prog-add-btn" id="btnAddProgram">
          <i class="fas fa-plus"></i> Add Program
        </button>
      </div>

      <div class="modal-body" style="padding:0;min-height:420px;">
        <div class="prog-panel">
          <div class="prog-list-col" id="programsList">
            <div class="programs-empty"><i class="fas fa-spinner fa-spin"></i><br>Loading…</div>
          </div>
          <div class="prog-form-col" id="progFormCol">
            <div class="prog-form-inner">
              <div class="prog-form-title">
                <span id="progFormTitle">Add Program</span>
                <button type="button" class="prog-form-close" id="progFormClose" title="Close form">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <form id="programInlineForm" novalidate>
                <input type="hidden" id="prog_id_inline">
                <label class="prog-field-label">Code <span style="color:#d93025">*</span></label>
                <input type="text" class="prog-field" id="prog_code_inline" placeholder="e.g. BSIT"
                  autocomplete="off" maxlength="20" required>
                <small style="font-size:.7rem;color:#8a96a3;display:block;margin-top:-.5rem;margin-bottom:.75rem;">Short abbreviation</small>
                <label class="prog-field-label">Name <span style="color:#d93025">*</span></label>
                <input type="text" class="prog-field" id="prog_name_inline"
                  placeholder="e.g. Bachelor of Science in IT" autocomplete="off" required>
                <button type="submit" class="prog-save-btn" id="progSaveBtn">
                  <i class="fas fa-check me-1"></i> Save
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" style="border-radius:8px;font-weight:600;" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i> Close
        </button>
      </div>

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
    icon: { success:'success', error:'error', info:'info' }[type] || 'info',
    title: msg, showConfirmButton: false, timer: 3500, timerProgressBar: true,
  });
};

$(function() {
  const subjectModal = new bootstrap.Modal(document.getElementById('subjectModal'));
  const $tblBody     = $('#subjectTable tbody');

  let allSubjects = [];

  /* ── 1. LOAD TABLE ── */
  window.loadTable = function() {
    $tblBody.html('<tr><td colspan="6" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading…</td></tr>');
    $.ajax({
      url: 'API/fetchsubject.php', type: 'GET', dataType: 'text',
      success: function(raw) {
        let parsed = null;
        try { parsed = JSON.parse(raw); } catch(e) {}
        if (parsed && Array.isArray(parsed)) {
          allSubjects = parsed;
          renderSubjectTable(applySubjectFilter($('#globalSearch').val().trim()));
        } else if (parsed && parsed.status === 'success' && Array.isArray(parsed.data)) {
          allSubjects = parsed.data;
          renderSubjectTable(applySubjectFilter($('#globalSearch').val().trim()));
        } else {
          allSubjects = [];
          $tblBody.html(raw);
          updateResultCount(-1);
        }
      },
      error: function(xhr) {
        $tblBody.html('<tr><td colspan="6" class="text-center text-danger py-3">Failed to load subjects (HTTP ' + xhr.status + ').</td></tr>');
      }
    });
  };
  loadTable();

  /* ── 2. CLIENT-SIDE FILTER ── */
  function applySubjectFilter(q) {
    if (!q) return allSubjects;
    const lq = q.toLowerCase();
    return allSubjects.filter(s =>
      (s.subject_code || '').toLowerCase().includes(lq) ||
      (s.subject_name || '').toLowerCase().includes(lq) ||
      (s.course_code  || '').toLowerCase().includes(lq) ||
      (s.course_name  || '').toLowerCase().includes(lq)
    );
  }

  function renderSubjectTable(list) {
    updateResultCount(list.length);
    if (!list.length) {
      $tblBody.html('<tr><td colspan="6" class="text-center text-muted py-3">No subjects match your search.</td></tr>');
      return;
    }
    let html = '';
    list.forEach((s, i) => {
      const active = parseInt(s.is_active) === 1;
      const pill   = active ? '<span class="pill-active">Active</span>' : '<span class="pill-inactive">Inactive</span>';
      html += `
        <tr>
          <td class="text-center">${i + 1}</td>
          <td><span class="td-code">${esc(s.subject_code)}</span></td>
          <td class="td-name">${esc(s.subject_name)}</td>
          <td><span class="td-prog">${esc(s.course_code)} – ${esc(s.course_name)}</span></td>
          <td class="text-center">${pill}</td>
          <td class="text-center">
            <div class="act-group">
              <button class="btn btn-sm ${active ? 'btn-danger' : 'btn-success'}"
                onclick="toggleStatus('${s.id}')" title="${active ? 'Deactivate' : 'Activate'}">
                <i class="fas ${active ? 'fa-ban' : 'fa-check-circle'}"></i>
              </button>
              <button class="btn btn-sm btn-warning" onclick="editSubj('${s.id}')" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-dark" onclick="deleteSubj('${s.id}')" title="Delete">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </td>
        </tr>`;
    });
    $tblBody.html(html);
  }

  function updateResultCount(n) {
    if (n < 0 || !$('#globalSearch').val().trim()) { $('#resultCount').hide(); return; }
    $('#resultNum').text(n);
    $('#resultPlural').text(n === 1 ? '' : 's');
    $('#resultCount').show();
  }

  function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  /* ── 3. SEARCH ── */
  let searchTimer;
  $('#globalSearch').on('input', function() {
    const q = $(this).val().trim();
    $('#searchClearBtn').toggleClass('visible', q.length > 0);
    if (allSubjects.length > 0) {
      renderSubjectTable(applySubjectFilter(q));
    } else if (q === '') {
      loadTable();
    } else {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function() {
        $tblBody.html('<tr><td colspan="6" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Searching…</td></tr>');
        $.ajax({
          url: 'API/fetchsubject.php', type: 'GET', data: { search: q }, dataType: 'text',
          success: function(raw) { $tblBody.html(raw); },
          error: function() { $tblBody.html('<tr><td colspan="6" class="text-center text-danger py-3">Search failed.</td></tr>'); }
        });
      }, 350);
    }
  });

  $('#searchClearBtn').on('click', function() {
    $('#globalSearch').val('').trigger('input').focus();
  });

  /* ── 4. ADD SUBJECT ── */
  $('#btnAdd').on('click', function() {
    $('#subjectModalLabel').html('<span class="header-icon"><i class="fas fa-book"></i></span> Add New Subject');
    $('#subjectModalForm')[0].reset();
    $('#subject_id').val('');
    subjectModal.show();
  });
  $('#btnResetSubject').on('click', function() { $('#subjectModalForm')[0].reset(); });

  /* ── 5. EDIT SUBJECT ── */
  window.editSubj = function(id) {
    $.getJSON('API/get_subject_detail.php', { id }, function(json) {
      if (json.status === 'success') {
        $('#subjectModalLabel').html('<span class="header-icon"><i class="fas fa-edit"></i></span> Edit Subject');
        $('#subject_id').val(json.subject.id);
        $('#subject_code').val(json.subject.subject_code);
        $('#subject_name').val(json.subject.subject_name);
        $('#course_id').val(json.subject.course_id);
        subjectModal.show();
      } else showToast(json.message, 'error');
    });
  };

  /* ── 6. TOGGLE STATUS ── */
  window.toggleStatus = function(id) {
    Swal.fire({
      title: 'Toggle Status?', text: 'This will activate or deactivate the subject.',
      icon: 'question', showCancelButton: true,
      confirmButtonColor: '#3085d6', cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, toggle it', cancelButtonText: 'Cancel'
    }).then(result => {
      if (!result.isConfirmed) return;
      $.post('API/toggleActiveSubject.php', { id }, function(json) {
        if (json.status === 'success') { showToast(json.message, 'success'); loadTable(); }
        else showToast(json.message, 'error');
      }, 'json');
    });
  };

  /* ── 7. DELETE SUBJECT ── */
  window.deleteSubj = function(id) {
    Swal.fire({
      title: 'Delete permanently?', text: 'This action cannot be undone.',
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel'
    }).then(result => {
      if (!result.isConfirmed) return;
      $.post('API/deletesubject.php', { id }, function(json) {
        if (json.status === 'success') { showToast(json.message, 'success'); loadTable(); }
        else showToast(json.message, 'error');
      }, 'json');
    });
  };

  /* ── 8. SAVE SUBJECT ── */
  $('#subjectModalForm').on('submit', function(e) {
    e.preventDefault();
    const id  = $('#subject_id').val();
    const url = id ? 'API/editsubject.php' : 'API/savesubject.php';
    const btn = $('#modalSubmitBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving…');
    $.ajax({
      url, type: 'POST', data: $(this).serialize(), dataType: 'json',
      success(json) {
        btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Save Subject');
        if (json.status === 'success') { subjectModal.hide(); loadTable(); showToast(json.message, 'success'); }
        else showToast(json.message, 'error');
      },
      error() {
        btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Save Subject');
        showToast('Server error — please try again.', 'error');
      }
    });
  });

  /* ══ PROGRAM MANAGEMENT ══ */
  const manageProgramsModal = new bootstrap.Modal(document.getElementById('manageProgramsModal'));
  let allPrograms = [];

  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/'/g,"&#39;").replace(/"/g,"&quot;").replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function openProgForm(mode, id = '', code = '', name = '') {
    $('#prog_id_inline').val(id);
    $('#prog_code_inline').val(code);
    $('#prog_name_inline').val(name);
    $('#progFormTitle').text(mode === 'edit' ? 'Edit Program' : 'Add Program');
    $('#progSaveBtn').html('<i class="fas fa-check me-1"></i> Save').prop('disabled', false);
    $('#progFormCol').addClass('open');
    $('.program-card').removeClass('editing');
    if (id) $(`.program-card[data-id="${id}"]`).addClass('editing');
    setTimeout(() => $('#prog_code_inline').focus(), 280);
  }

  function closeProgForm() {
    $('#progFormCol').removeClass('open');
    $('.program-card').removeClass('editing');
    $('#programInlineForm')[0].reset();
    $('#prog_id_inline').val('');
  }

  $('#btnManagePrograms').on('click', function() {
    closeProgForm();
    loadProgramsList();
    manageProgramsModal.show();
  });

  $('#progFormClose').on('click', closeProgForm);
  $('#btnAddProgram').on('click', () => openProgForm('add'));

  $('#progSearch').on('input', function() {
    const q = $(this).val().trim().toLowerCase();
    renderProgramCards(q ? allPrograms.filter(p =>
      p.course_code.toLowerCase().includes(q) || p.course_name.toLowerCase().includes(q)
    ) : allPrograms);
  });

  document.getElementById('manageProgramsModal').addEventListener('hidden.bs.modal', closeProgForm);

  function loadProgramsList() {
    $('#programsList').html('<div class="programs-empty"><i class="fas fa-spinner fa-spin"></i><br>Loading…</div>');
    $.getJSON('API/fetch_programs.php', function(json) {
      if (json.status === 'success') {
        allPrograms = json.programs || [];
        $('#progCountBadge').text(allPrograms.length);
        renderProgramCards(allPrograms);
      } else {
        $('#programsList').html('<div class="programs-empty text-danger"><i class="fas fa-exclamation-circle"></i><br>Failed to load.</div>');
      }
    }).fail(() => {
      $('#programsList').html('<div class="programs-empty text-danger"><i class="fas fa-exclamation-circle"></i><br>Failed to load.</div>');
    });
  }

  function renderProgramCards(list) {
    if (!list.length) {
      $('#programsList').html('<div class="programs-empty"><i class="fas fa-university"></i><br>No programs found.</div>');
      return;
    }
    let html = '';
    list.forEach((p, i) => {
      const abbr     = p.course_code.substring(0, 4);
      const subCount = p.subject_count != null ? `${p.subject_count} subject${p.subject_count != 1 ? 's' : ''}` : '';
      html += `
        <div class="program-card" data-id="${p.id}" style="animation-delay:${i * 35}ms">
          <div class="prog-badge">${escHtml(abbr)}</div>
          <div class="prog-info">
            <div class="prog-code">${escHtml(p.course_code)}</div>
            <div class="prog-name" title="${escHtml(p.course_name)}">${escHtml(p.course_name)}</div>
            ${subCount ? `<div class="prog-subcount">${subCount}</div>` : ''}
          </div>
          <div class="prog-actions">
            <button class="btn btn-sm btn-outline-warning"
              onclick="openProgForm('edit','${p.id}','${escHtml(p.course_code)}','${escHtml(p.course_name)}')" title="Edit">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteProg(${p.id})" title="Delete">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </div>`;
    });
    $('#programsList').html(html);
  }

  window.openProgForm = openProgForm;

  window.deleteProg = function(id) {
    Swal.fire({
      title: 'Delete this program?', text: 'Subjects linked to this program may be affected.',
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel'
    }).then(result => {
      if (!result.isConfirmed) return;
      $.post('API/delete_program.php', { id }, function(json) {
        if (json.status === 'success') {
          showToast(json.message, 'success');
          if ($('#prog_id_inline').val() == id) closeProgForm();
          loadProgramsList();
          refreshCourseDropdown();
        } else showToast(json.message, 'error');
      }, 'json').fail(() => showToast('Server error.', 'error'));
    });
  };

  $('#programInlineForm').on('submit', function(e) {
    e.preventDefault();
    const id   = $('#prog_id_inline').val().trim();
    const code = $('#prog_code_inline').val().trim();
    const name = $('#prog_name_inline').val().trim();
    if (!code || !name) { showToast('Code and Name are required.', 'error'); return; }
    const btn = $('#progSaveBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>');
    $.ajax({
      url: id ? 'API/edit_program.php' : 'API/save_program.php',
      type: 'POST', data: { program_id: id, course_code: code, course_name: name }, dataType: 'json',
      success(json) {
        btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Save');
        if (json.status === 'success') {
          showToast(json.message, 'success');
          closeProgForm(); loadProgramsList(); refreshCourseDropdown();
        } else showToast(json.message, 'error');
      },
      error() {
        btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Save');
        showToast('Server error.', 'error');
      }
    });
  });

  $('#prog_code_inline, #prog_name_inline').on('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); $('#programInlineForm').trigger('submit'); }
  });

  function refreshCourseDropdown() {
    $.getJSON('API/fetch_programs.php', function(json) {
      if (json.status === 'success') {
        let opts = '<option value="">— Select Program —</option>';
        json.programs.forEach(p => { opts += `<option value="${p.id}">${p.course_code} - ${p.course_name}</option>`; });
        $('#course_id').html(opts);
      }
    });
  }
});
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

</body>
</html>