<?php
/**
 * manageQuiz.php — Tere LEARN | Quiz Management Page
 * ?post_id=UUID&class_id=UUID
 */
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
$level = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
if (!isset($_SESSION['user_id']) || $level !== 2) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php'); exit;
}
$post_id  = trim($_GET['post_id']  ?? '');
$class_id = trim($_GET['class_id'] ?? '');
if (!$post_id || !$class_id) { header('Location: ' . TERELEARN_BASE_URL . 'facultyUI.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tere Learn — Manage Quiz</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    :root{--primary:#1a9e78;--primary-dark:#0d7a5e;--primary-light:#e6f7f2;--primary-mid:rgba(26,158,120,.15);--accent:#1f73db;--accent-light:#e8f0fe;--danger:#d93025;--warning:#f57c00;--border:#e8eaed;--text:#1c2027;--text-muted:#5f6368;--bg:#f4f6f9;--surface:#ffffff;--nav-h:60px;--radius:14px;--radius-sm:8px;--shadow:0 2px 12px rgba(0,0,0,.07);--shadow-md:0 4px 20px rgba(0,0,0,.10);--shadow-lg:0 10px 40px rgba(0,0,0,.14);--trans:.22s cubic-bezier(.4,0,.2,1);}
    body.dark{--primary:#2ecc9a;--primary-dark:#1a9e78;--primary-light:rgba(46,204,154,.12);--primary-mid:rgba(46,204,154,.10);--accent:#4d90e2;--accent-light:rgba(77,144,226,.14);--border:#2e3849;--text:#e4ecf7;--text-muted:#8a9ab5;--bg:#0f1724;--surface:#182030;}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);transition:background var(--trans),color var(--trans);}
    ::-webkit-scrollbar{width:5px;}::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
    .swal2-container{z-index:99999 !important;}

    /* ── TOPBAR ── */
    .topbar{position:fixed;inset:0 0 auto 0;height:var(--nav-h);background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.75rem;padding:0 1.4rem;z-index:200;box-shadow:var(--shadow);}
    .back-btn{width:36px;height:36px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:1rem;display:flex;align-items:center;justify-content:center;border-radius:10px;transition:all var(--trans);text-decoration:none;flex-shrink:0;}
    .back-btn:hover{background:var(--border);color:var(--text);}
    .topbar-brand{display:flex;align-items:center;gap:.55rem;font-size:.95rem;font-weight:700;color:var(--text);text-decoration:none;white-space:nowrap;}
    .blogo{width:32px;height:32px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:9px;color:#fff;font-size:.82rem;display:flex;align-items:center;justify-content:center;}
    .topbar-sep{color:var(--border);font-size:1.1rem;margin:0 .1rem;}
    .topbar-title{font-size:.9rem;font-weight:600;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:380px;}
    .topbar-right{margin-left:auto;display:flex;align-items:center;gap:.5rem;}
    .icon-btn{width:36px;height:36px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:1rem;display:flex;align-items:center;justify-content:center;border-radius:10px;transition:all var(--trans);}
    .icon-btn:hover{background:var(--border);color:var(--text);}

    /* ── HERO HEADER ── */
    .mq-hero{margin-top:var(--nav-h);background:linear-gradient(135deg,#1a9e78 0%,#0d47a1 100%);padding:1.75rem 2rem 1.5rem;position:relative;overflow:hidden;}
    .mq-hero::before{content:'';position:absolute;inset:0;background:rgba(0,0,0,.18);}
    .mq-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;display:flex;align-items:flex-start;gap:1.25rem;flex-wrap:wrap;}
    .mq-hero-icon{width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.18);border:1.5px solid rgba(255,255,255,.3);color:#fff;font-size:1.3rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .mq-hero-text{flex:1;min-width:0;}
    .mq-hero-title{font-size:1.55rem;font-weight:700;color:#fff;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .mq-hero-sub{font-size:.85rem;color:rgba(255,255,255,.8);margin-top:.3rem;}
    .mq-hero-chips{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.75rem;}
    .mq-hero-chip{font-size:.72rem;font-weight:600;padding:.22rem .65rem;border-radius:20px;background:rgba(255,255,255,.18);color:#fff;display:flex;align-items:center;gap:.3rem;}
    .mq-status-badge{font-size:.7rem;font-weight:700;padding:.22rem .7rem;border-radius:20px;text-transform:uppercase;letter-spacing:.5px;display:inline-flex;align-items:center;gap:.35rem;}
    .mq-status-badge.is-draft{background:rgba(255,255,255,.15);color:rgba(255,255,255,.9);}
    .mq-status-badge.is-open{background:#d1fae5;color:#065f46;}
    .mq-status-badge.is-live{background:#fdecea;color:#d93025;animation:livePulse 1.4s ease-in-out infinite;}
    .mq-status-badge.is-closed{background:#fef3c7;color:#92400e;}
    .mq-status-badge.is-released{background:var(--accent-light);color:var(--accent);}
    @keyframes livePulse{0%,100%{box-shadow:0 0 0 0 rgba(217,48,37,.5);}50%{box-shadow:0 0 0 6px rgba(217,48,37,0);}}

    /* ── TABS ── */
    .tabs-bar{background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 1.5rem;position:sticky;top:var(--nav-h);z-index:100;overflow-x:auto;}
    .tab-btn{padding:1rem 1.25rem;border:none;background:none;font-size:.88rem;font-weight:600;color:var(--text-muted);cursor:pointer;border-bottom:3px solid transparent;transition:all var(--trans);white-space:nowrap;display:inline-flex;align-items:center;gap:.45rem;}
    .tab-btn:hover,.tab-btn.active{color:var(--primary);}
    .tab-btn.active{border-bottom-color:var(--primary);}
    .tab-badge{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:20px;background:var(--primary);color:#fff;font-size:.65rem;font-weight:700;}
    .tab-pane{display:none;}
    .tab-pane.active{display:block;}

    /* ── PAGE BODY ── */
    .mq-page{max-width:1100px;margin:0 auto;padding:1.75rem 1.5rem;}

    /* ── LOADING / EMPTY ── */
    .mq-loading{display:flex;align-items:center;justify-content:center;gap:.55rem;padding:3rem 1rem;color:var(--text-muted);font-size:.88rem;}
    .mq-loading i{color:var(--primary);}
    .mq-empty{text-align:center;padding:3rem 1rem;color:var(--text-muted);}
    .mq-empty i{font-size:2.5rem;opacity:.18;display:block;margin-bottom:.6rem;}
    .mq-empty h4{font-size:.95rem;font-weight:600;color:var(--text);margin-bottom:.3rem;}
    .mq-empty p{font-size:.82rem;}

    /* ── SETTINGS TAB ── */
    .mq-settings-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.1rem;}
    @media(max-width:600px){.mq-settings-grid{grid-template-columns:1fr;}}
    .mq-settings-section{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.1rem 1.25rem;box-shadow:var(--shadow);}
    .mq-settings-section--full{grid-column:1/-1;}
    .mq-settings-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);margin-bottom:.85rem;}
    .mq-settings-row{display:flex;justify-content:space-between;align-items:center;padding:.45rem 0;border-bottom:1px solid var(--border);font-size:.85rem;}
    .mq-settings-row:last-child{border-bottom:none;}
    .mq-settings-row span{color:var(--text-muted);}
    .mq-settings-row strong{color:var(--text);font-weight:600;}
    .mq-ctrl-row{display:flex;flex-wrap:wrap;gap:.65rem;align-items:center;}
    .mq-ctrl-started-info{font-size:.84rem;color:var(--text-muted);display:flex;align-items:center;gap:.5rem;}
    .mq-ctrl-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.6rem 1.1rem;border-radius:10px;font-family:inherit;font-size:.84rem;font-weight:700;border:none;cursor:pointer;transition:all .18s;}
    .mq-ctrl-btn:disabled{opacity:.5;cursor:not-allowed;}
    .mq-ctrl-btn-primary{background:var(--primary);color:#fff;box-shadow:0 2px 10px rgba(26,158,120,.3);}
    .mq-ctrl-btn-primary:hover:not(:disabled){background:var(--primary-dark);transform:translateY(-1px);}
    .mq-ctrl-btn-danger{background:#fdecea;color:var(--danger);border:1.5px solid #f5c2c7;}
    .mq-ctrl-btn-danger:hover:not(:disabled){background:var(--danger);color:#fff;border-color:var(--danger);}
    .mq-ctrl-btn-accent{background:var(--accent-light);color:var(--accent);border:1.5px solid rgba(31,115,219,.25);}
    .mq-ctrl-btn-accent:hover:not(:disabled){background:var(--accent);color:#fff;}

    /* ── SUBMISSIONS TAB ── */
    .mq-sub-toolbar{display:flex;align-items:center;gap:.65rem;margin-bottom:1.1rem;flex-wrap:wrap;}
    .mq-sub-search-wrap{flex:1;min-width:180px;position:relative;display:flex;align-items:center;}
    .mq-sub-search-wrap i{position:absolute;left:.75rem;color:var(--text-muted);font-size:.82rem;pointer-events:none;}
    .mq-sub-search{width:100%;padding:.55rem .85rem .55rem 2.1rem;border:1.5px solid var(--border);border-radius:10px;background:var(--surface);font-family:inherit;font-size:.84rem;color:var(--text);outline:none;transition:border-color .15s;}
    .mq-sub-search:focus{border-color:var(--primary);}
    .mq-live-toggle{display:inline-flex;align-items:center;gap:.45rem;padding:.5rem .9rem;border-radius:10px;background:var(--bg);border:1.5px solid var(--border);cursor:pointer;font-size:.82rem;font-weight:600;color:var(--text-muted);user-select:none;transition:all .18s;}
    .mq-live-toggle input{display:none;}
    .mq-live-dot{width:9px;height:9px;border-radius:50%;background:var(--text-muted);transition:background .2s;}
    .mq-live-toggle.active{border-color:#d93025;color:#d93025;background:#fdecea;}
    .mq-live-toggle.active .mq-live-dot{background:#d93025;animation:liveDot 1.1s ease-in-out infinite;}
    @keyframes liveDot{0%,100%{transform:scale(1);}50%{transform:scale(.7);opacity:.5;}}
    .mq-refresh-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem .9rem;border-radius:10px;border:1.5px solid var(--border);background:var(--surface);font-family:inherit;font-size:.82rem;font-weight:600;color:var(--text-muted);cursor:pointer;transition:all .18s;}
    .mq-refresh-btn:hover{border-color:var(--primary);color:var(--primary);}
    .mq-table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);}
    .mq-table{width:100%;border-collapse:collapse;}
    .mq-table th{text-align:left;padding:.75rem 1rem;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);background:var(--bg);border-bottom:1.5px solid var(--border);}
    .mq-table td{padding:.85rem 1rem;font-size:.83rem;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle;}
    .mq-table tr:last-child td{border-bottom:none;}
    .mq-table tr:hover td{background:var(--primary-light);}
    .mq-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;font-size:.78rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .mq-chip{display:inline-flex;align-items:center;gap:.3rem;padding:.18rem .6rem;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
    .mq-chip-wait{background:var(--bg);color:var(--text-muted);border:1px solid var(--border);}
    .mq-chip-prog{background:#fff8e8;color:#92400e;border:1px solid #fcd34d;}
    .mq-chip-done{background:var(--primary-light);color:var(--primary-dark);border:1px solid rgba(26,158,120,.3);}
    .mq-row-btn{width:32px;height:32px;border-radius:8px;border:1.5px solid var(--border);background:var(--surface);color:var(--text-muted);cursor:pointer;font-size:.8rem;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;}
    .mq-row-btn:disabled{opacity:.4;cursor:not-allowed;}
    .mq-row-btn-view:hover:not(:disabled){border-color:var(--accent);color:var(--accent);background:var(--accent-light);}
    .mq-row-btn-remove:hover:not(:disabled){border-color:var(--danger);color:var(--danger);background:#fdecea;}

    /* ── CONTROLS REDESIGN ── */
    .mq-ctrl-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.85rem;}
    .mq-ctrl-card{background:var(--surface);border:1.5px solid var(--border);border-radius:14px;padding:1.1rem 1.15rem;display:flex;flex-direction:column;gap:.6rem;transition:box-shadow var(--trans),border-color var(--trans);}
    .mq-ctrl-card:hover{box-shadow:var(--shadow-md);}
    .mq-ctrl-card--primary{border-color:rgba(26,158,120,.35);background:linear-gradient(145deg,var(--primary-light),var(--surface));}
    .mq-ctrl-card--danger{border-color:rgba(217,48,37,.2);background:linear-gradient(145deg,#fff5f5,var(--surface));}
    body.dark .mq-ctrl-card--danger{background:linear-gradient(145deg,rgba(217,48,37,.07),var(--surface));}
    .mq-ctrl-card--accent{border-color:rgba(31,115,219,.25);background:linear-gradient(145deg,var(--accent-light),var(--surface));}
    body.dark .mq-ctrl-card--accent{background:linear-gradient(145deg,rgba(31,115,219,.08),var(--surface));}
    .mq-ctrl-card--muted{opacity:.6;}
    .mq-ctrl-card-icon{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;}
    .mq-ctrl-card-icon--green{background:var(--primary);color:#fff;}
    .mq-ctrl-card-icon--red{background:var(--danger);color:#fff;}
    .mq-ctrl-card-icon--blue{background:var(--accent);color:#fff;}
    .mq-ctrl-card-icon--gray{background:var(--border);color:var(--text-muted);}
    .mq-ctrl-card-title{font-size:.9rem;font-weight:700;color:var(--text);line-height:1.2;}
    .mq-ctrl-card-desc{font-size:.77rem;color:var(--text-muted);line-height:1.45;}
    .mq-ctrl-card-act{margin-top:auto;padding-top:.45rem;}
    .mq-ctrl-action-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.52rem 1rem;border-radius:9px;font-family:inherit;font-size:.82rem;font-weight:700;border:none;cursor:pointer;transition:all .18s;width:100%;justify-content:center;min-height:42px;line-height:1;box-sizing:border-box;}
    .mq-ctrl-action-btn:disabled{opacity:.5;cursor:not-allowed;}
    .mq-ctrl-action-btn--green{background:var(--primary);color:#fff;box-shadow:0 2px 8px rgba(26,158,120,.3);}
    .mq-ctrl-action-btn--green:hover:not(:disabled){background:var(--primary-dark);transform:translateY(-1px);}
    .mq-ctrl-action-btn--red{background:var(--danger);color:#fff;box-shadow:0 2px 8px rgba(217,48,37,.25);}
    .mq-ctrl-action-btn--red:hover:not(:disabled){background:#b71c1c;transform:translateY(-1px);}
    .mq-ctrl-action-btn--blue{background:var(--accent);color:#fff;box-shadow:0 2px 8px rgba(31,115,219,.25);}
    .mq-ctrl-action-btn--blue:hover:not(:disabled){background:#1557b0;transform:translateY(-1px);}
    .mq-ctrl-live-badge{display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .85rem;border-radius:9px;font-size:.82rem;font-weight:600;background:var(--primary-light);color:var(--primary-dark);border:1.5px solid rgba(26,158,120,.25);}
    .mq-ctrl-live-dot{width:8px;height:8px;border-radius:50%;background:var(--primary);animation:liveDot 1.1s ease-in-out infinite;}

    /* ── ANALYTICS TAB ── */
    .mq-an-wrap{display:flex;flex-direction:column;gap:1.1rem;}
    .mq-an-engage{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem 1.4rem;box-shadow:var(--shadow);}
    .mq-an-engage-header{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:.9rem;}
    .mq-an-engage-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);}
    .mq-an-engage-pct{font-size:1.5rem;font-weight:700;color:var(--primary);font-family:'DM Mono',monospace;}
    .mq-an-engage-pct span{font-size:.72rem;font-weight:600;color:var(--text-muted);margin-left:.25rem;}
    .mq-an-bar{display:flex;height:14px;border-radius:10px;overflow:hidden;gap:2px;background:var(--border);}
    .mq-an-bar-seg{height:100%;transition:width .6s cubic-bezier(.4,0,.2,1);min-width:0;}
    .mq-an-bar-seg--done{background:#1a9e78;}
    .mq-an-bar-seg--prog{background:#f59e0b;}
    .mq-an-bar-seg--wait{background:#cbd5e1;}
    .mq-an-legend{display:flex;flex-wrap:wrap;gap:.65rem;margin-top:.7rem;}
    .mq-an-legend-item{display:flex;align-items:center;gap:.35rem;font-size:.76rem;color:var(--text-muted);}
    .mq-an-legend-dot{width:9px;height:9px;border-radius:50%;}
    .mq-an-stats-row{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;}
    @media(max-width:540px){.mq-an-stats-row{grid-template-columns:1fr;}}
    .mq-an-stat-block{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.1rem 1.25rem;box-shadow:var(--shadow);}
    .mq-an-stat-block-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem;}
    .mq-an-stat-row{display:flex;justify-content:space-between;align-items:center;padding:.45rem 0;border-bottom:1px solid var(--border);font-size:.85rem;}
    .mq-an-stat-row:last-child{border-bottom:none;}
    .mq-an-stat-row span{color:var(--text-muted);display:flex;align-items:center;gap:.4rem;}
    .mq-an-stat-row strong{font-weight:700;font-family:'DM Mono',monospace;}

    /* ── DETAIL MODAL (student submission) ── */
    @keyframes mqFade{from{opacity:0;}to{opacity:1;}}
    @keyframes mqPop{from{opacity:0;transform:translate(-50%,-50%) scale(.94);}to{opacity:1;transform:translate(-50%,-50%) scale(1);}}
    .mq-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.52);z-index:900;}
    .mq-overlay.show{display:block;animation:mqFade .2s ease;}
    .mq-detail-modal{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:min(560px,94vw);max-height:88vh;background:var(--surface);border-radius:18px;box-shadow:var(--shadow-lg);z-index:901;flex-direction:column;overflow:hidden;}
    .mq-detail-modal.show{display:flex;animation:mqPop .25s cubic-bezier(.4,0,.2,1);}
    .mq-detail-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.1rem 1.25rem;border-bottom:1px solid var(--border);flex-shrink:0;background:linear-gradient(135deg,var(--primary-light),rgba(31,115,219,.08));}
    .mq-detail-head-left{display:flex;align-items:center;gap:.75rem;}
    .mq-detail-icon{width:40px;height:40px;border-radius:12px;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;}
    .mq-detail-title{font-size:.95rem;font-weight:700;}
    .mq-detail-sub{font-size:.75rem;color:var(--text-muted);margin-top:.05rem;}
    .mq-detail-close{width:32px;height:32px;border:none;background:var(--bg);color:var(--text-muted);border-radius:50%;cursor:pointer;font-size:.85rem;display:flex;align-items:center;justify-content:center;transition:all .15s;}
    .mq-detail-close:hover{background:#fdecea;color:var(--danger);}
    .mq-detail-body{padding:1.25rem;overflow-y:auto;flex:1;}
    .mq-detail-row{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.85rem;}
    .mq-detail-row:last-child{border-bottom:none;}
    .mq-detail-row span{color:var(--text-muted);}
    .mq-detail-score{display:flex;justify-content:space-between;align-items:center;background:var(--primary-light);border:1.5px solid rgba(26,158,120,.25);border-radius:12px;padding:.8rem 1rem;margin-bottom:.75rem;font-size:.9rem;}
    .mq-detail-score strong{font-size:1.3rem;color:var(--primary);font-family:'DM Mono',monospace;}
  </style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
  <a href="classRoom.php?class_id=<?= urlencode($class_id) ?>" class="back-btn" title="Back to classroom">
    <i class="fa-solid fa-arrow-left"></i>
  </a>
  <a href="facultyUI.php" class="topbar-brand">
    <div class="blogo"><i class="fa-solid fa-book-open-reader"></i></div>
    Tere Learn
  </a>
  <span class="topbar-sep">/</span>
  <span class="topbar-title" id="pageTopbarTitle">Manage Quiz</span>
  <div class="topbar-right">
    <button class="icon-btn" onclick="toggleDark()" title="Toggle dark mode"><i class="fa-solid fa-moon" id="darkIcon"></i></button>
  </div>
</header>

<!-- HERO HEADER -->
<div class="mq-hero">
  <div class="mq-hero-inner">
    <div class="mq-hero-icon"><i class="fa-solid fa-clipboard-question"></i></div>
    <div class="mq-hero-text">
      <div class="mq-hero-title" id="heroTitle">Loading…</div>
      <div class="mq-hero-sub" id="heroSub">—</div>
      <div class="mq-hero-chips">
        <span class="mq-status-badge" id="heroStatusBadge"><i class="fa-solid fa-circle-half-stroke"></i> <span id="heroStatusText">—</span></span>
      </div>
    </div>
  </div>
</div>

<!-- TABS -->
<div class="tabs-bar">
  <button class="tab-btn active" data-tab="settings" onclick="switchTab('settings')">
    <i class="fa-solid fa-sliders"></i> Settings &amp; Controls
  </button>
  <button class="tab-btn" data-tab="submissions" onclick="switchTab('submissions')">
    <i class="fa-solid fa-users"></i> Submissions <span class="tab-badge" id="subBadge">0</span>
  </button>
  <button class="tab-btn" data-tab="analytics" onclick="switchTab('analytics')">
    <i class="fa-solid fa-chart-column"></i> Analytics
  </button>
</div>

<!-- TAB CONTENT -->

<!-- SETTINGS -->
<div class="tab-pane active" id="pane-settings">
  <div class="mq-page">
    <div class="mq-loading" id="settingsLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
    <div id="settingsContent" style="display:none;"></div>
  </div>
</div>

<!-- SUBMISSIONS -->
<div class="tab-pane" id="pane-submissions">
  <div class="mq-page">
    <div class="mq-sub-toolbar">
      <div class="mq-sub-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" class="mq-sub-search" id="subSearch" placeholder="Search by name or student #…" oninput="filterSubmissions()">
      </div>
      <label class="mq-live-toggle" id="liveLabel">
        <input type="checkbox" id="liveToggle" onchange="toggleLiveMonitor()">
        <span class="mq-live-dot"></span>
        <span>Live</span>
      </label>
      <button class="mq-refresh-btn" onclick="loadSubmissions(true)"><i class="fa-solid fa-rotate"></i> Refresh</button>
    </div>
    <div class="mq-loading" id="subLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
    <div class="mq-table-wrap" id="subTableWrap" style="display:none;">
      <table class="mq-table">
        <thead>
          <tr>
            <th>Student</th>
            <th>Status</th>
            <th>Score</th>
            <th>Enrolled</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="subTbody"></tbody>
      </table>
    </div>
    <div id="subEmpty" style="display:none;" class="mq-empty">
      <i class="fa-solid fa-users-slash"></i>
      <h4>No enrolled students</h4>
      <p>Once students enroll, they'll appear here.</p>
    </div>
  </div>
</div>

<!-- ANALYTICS -->
<div class="tab-pane" id="pane-analytics">
  <div class="mq-page">
    <div class="mq-loading" id="anLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
    <div id="anContent" style="display:none;"></div>
  </div>
</div>

<!-- STUDENT DETAIL MODAL -->
<div class="mq-overlay" id="detailOverlay" onclick="if(event.target===this)closeDetail()"></div>
<div class="mq-detail-modal" id="detailModal" role="dialog" aria-modal="true">
  <div class="mq-detail-head">
    <div class="mq-detail-head-left">
      <div class="mq-detail-icon"><i class="fa-solid fa-user"></i></div>
      <div>
        <div class="mq-detail-title" id="detailTitle">Student</div>
        <div class="mq-detail-sub" id="detailSub">—</div>
      </div>
    </div>
    <button class="mq-detail-close" onclick="closeDetail()" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="mq-detail-body" id="detailBody">
    <div class="mq-loading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
const POST_ID  = <?= json_encode($post_id) ?>;
const CLASS_ID = <?= json_encode($class_id) ?>;

let allRows        = [];
let quizData       = {};
let livePollTimer  = null;
let currentTab     = 'settings';
let lastTableSig   = '';

/* ── helpers ── */
function esc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function fmtDate(s) {
  if (!s) return '—';
  try { return new Date(s.replace(' ','T')).toLocaleString([],{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}); }
  catch(e){ return s; }
}
function fmtWhole(n) {
  if (n === null || n === undefined || n === '') return '—';
  const v = Number(n);
  if (!Number.isFinite(v)) return '—';
  return String(Math.round(v));
}
function toggleDark() {
  document.body.classList.toggle('dark');
  document.getElementById('darkIcon').className = document.body.classList.contains('dark')
    ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
}
if (localStorage.getItem('theme') === 'dark') {
  document.body.classList.add('dark');
  document.getElementById('darkIcon').className = 'fa-solid fa-sun';
}

/* ── TAB SWITCHING ── */
function switchTab(tab) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.toggle('active', p.id === 'pane-' + tab));
  currentTab = tab;
  if (tab === 'settings')    loadSettings();
  if (tab === 'submissions') loadSubmissions(false);
  if (tab === 'analytics')   loadAnalytics();
  if (tab !== 'submissions' && livePollTimer) {
    clearInterval(livePollTimer); livePollTimer = null;
    const lbl = document.getElementById('liveLabel');
    const tog = document.getElementById('liveToggle');
    if (lbl) lbl.classList.remove('active');
    if (tog) tog.checked = false;
  }
}

/* ── DERIVE STATUS ── */
function deriveStatus(quiz) {
  const pub     = parseInt(quiz.is_published || 0, 10) === 1;
  const closed  = parseInt(quiz.is_force_closed || 0, 10) === 1;
  const started = !!quiz.live_started_at;
  const paused  = started && parseInt(quiz.is_force_open || 0, 10) !== 1 && !quiz.live_ended_at && !closed;
  const ended   = !!quiz.live_ended_at || closed;
  const released= !!quiz.results_released_at;
  if (!pub)      return { key:'draft',    label:'Draft',            cls:'is-draft',    icon:'fa-pencil' };
  if (released)  return { key:'released', label:'Results Released', cls:'is-released', icon:'fa-flag-checkered' };
  if (ended)     return { key:'closed',   label:'Closed',           cls:'is-closed',   icon:'fa-lock' };
  if (paused)    return { key:'paused',   label:'Paused',           cls:'is-closed',   icon:'fa-pause' };
  if (started)   return { key:'live',     label:'Live',             cls:'is-live',     icon:'fa-circle-dot' };
  return          { key:'open',    label:'Open',             cls:'is-open',     icon:'fa-circle-play' };
}

/* ── UPDATE HERO ── */
function updateHero(quiz, postMeta) {
  const title = postMeta.title || quiz.title || 'Quiz';
  document.title = 'Manage Quiz - ' + title;
  document.getElementById('pageTopbarTitle').textContent = title;
  document.getElementById('heroTitle').textContent = title;

  const qCount   = Number(postMeta.question_count || 0);
  const totalPts = Number(postMeta.total_points || 0);
  const avgScore = (postMeta.avg_score == null || Number.isNaN(Number(postMeta.avg_score))) ? null : Number(postMeta.avg_score);
  const mode     = (quiz.quiz_mode || 'live');
  const modeLabel = (mode === 'due_date') ? 'Scheduled Window' : (mode.charAt(0).toUpperCase()+mode.slice(1) + ' mode');
  const scorePart = avgScore == null ? '' : ` | Avg ${fmtWhole(avgScore)}/${fmtWhole(totalPts || 0)}`;
  document.getElementById('heroSub').textContent =
    qCount + ' question' + (qCount!==1?'s':'') + ' | ' + totalPts + ' pts | ' + modeLabel + scorePart;

  const st = deriveStatus(quiz);
  const badge = document.getElementById('heroStatusBadge');
  badge.className = 'mq-status-badge ' + st.cls;
  document.getElementById('heroStatusText').textContent = st.label;
  const icon = badge.querySelector('i');
  if (icon) icon.className = 'fa-solid ' + st.icon;
}

/* ── FETCH ENROLLMENTS (shared) ── */
async function fetchEnrollments() {
  const fd = new FormData();
  fd.append('post_id', POST_ID);
  const res  = await fetch('API/facultyUI/classroom/quiz/get_enrollments.php', { method:'POST', body:fd, credentials:'same-origin' });
  const json = await res.json();
  if (!json.success) throw new Error(json.message || 'Failed');
  return json.data || {};
}

/* ══ SETTINGS TAB ══ */
async function loadSettings() {
  const loading = document.getElementById('settingsLoading');
  const content = document.getElementById('settingsContent');
  loading.style.display = 'flex';
  content.style.display = 'none';

  try {
    const data = await fetchEnrollments();
    const quiz  = data.quiz || {};
    const rows  = data.enrollments || [];
    quizData    = quiz;

    const postMeta = {
      title: data.post_title || 'Quiz',
      question_count: Number(data.question_count || 0),
      total_points: Number(data.total_points || 0),
      avg_score: data.avg_score
    };
    const isPublished   = parseInt(quiz.is_published   || 0, 10) === 1;
    const isForceClosed = parseInt(quiz.is_force_closed|| 0, 10) === 1;
    const isStarted     = !!quiz.live_started_at;
    const isEnded       = !!quiz.live_ended_at || isForceClosed;
    const isPaused      = isStarted && parseInt(quiz.is_force_open || 0, 10) !== 1 && !isEnded;
    const isReleased    = !!quiz.results_released_at;

    const waitingCount    = rows.filter(e => !e.attempt_status).length;
    const inProgressCount = rows.filter(e => e.attempt_status === 'in_progress').length;
    const submittedCount  = rows.filter(e => e.attempt_status === 'submitted').length;
    const count = rows.length;

    const mode = quiz.quiz_mode || 'live';

    let controlCards = '';
    if (isPublished && mode === 'live') {
      if (!isEnded) {
        if (!isStarted) {
          const dis = count === 0 ? 'disabled' : '';
          const disNote = count === 0 ? '<p style="font-size:.72rem;color:#f59e0b;margin-top:.3rem;display:flex;align-items:center;gap:.3rem;"><i class="fa-solid fa-triangle-exclamation"></i> No students enrolled yet</p>' : '';
          controlCards += `
            <div class="mq-ctrl-card mq-ctrl-card--primary">
              <div class="mq-ctrl-card-icon mq-ctrl-card-icon--green"><i class="fa-solid fa-play"></i></div>
              <div>
                <div class="mq-ctrl-card-title">Start Quiz</div>
                <div class="mq-ctrl-card-desc">Open the quiz for all enrolled students. They'll be able to start answering immediately.</div>
                ${disNote}
              </div>
              <div class="mq-ctrl-card-act">
                <button class="mq-ctrl-action-btn mq-ctrl-action-btn--green" onclick="quizAction('start')" ${dis}>
                  <i class="fa-solid fa-play"></i> Start Quiz
                </button>
              </div>
            </div>`;
        } else {
          controlCards += `
            <div class="mq-ctrl-card mq-ctrl-card--primary">
              <div class="mq-ctrl-card-icon mq-ctrl-card-icon--green"><i class="fa-solid fa-circle-dot"></i></div>
              <div>
                <div class="mq-ctrl-card-title">${isPaused ? 'Quiz is Paused' : 'Quiz is Live'}</div>
                <div class="mq-ctrl-card-desc">Started at <strong>${fmtDate(quiz.live_started_at)}</strong>. ${isPaused ? 'Students are waiting for resume.' : 'Students can currently take the quiz.'}</div>
              </div>
              <div class="mq-ctrl-card-act">
                <div class="mq-ctrl-live-badge">${isPaused ? '<i class="fa-solid fa-pause"></i> Paused' : '<span class="mq-ctrl-live-dot"></span> Live Now'}</div>
              </div>
            </div>`;
        }
        if (isStarted && !isEnded) {
          controlCards += `
            <div class="mq-ctrl-card mq-ctrl-card--accent">
              <div class="mq-ctrl-card-icon mq-ctrl-card-icon--blue"><i class="fa-solid ${isPaused ? 'fa-play' : 'fa-pause'}"></i></div>
              <div>
                <div class="mq-ctrl-card-title">${isPaused ? 'Resume Quiz' : 'Pause Quiz'}</div>
                <div class="mq-ctrl-card-desc">${isPaused ? 'Let waiting students continue answering.' : 'Temporarily pause all active students and move them to waiting.'}</div>
              </div>
              <div class="mq-ctrl-card-act">
                <button class="mq-ctrl-action-btn mq-ctrl-action-btn--blue" onclick="quizAction('${isPaused ? 'resume' : 'pause'}')">
                  <i class="fa-solid ${isPaused ? 'fa-play' : 'fa-pause'}"></i> ${isPaused ? 'Resume Quiz' : 'Pause Quiz'}
                </button>
              </div>
            </div>`;
        }
        if (isStarted) {
          controlCards += `
            <div class="mq-ctrl-card mq-ctrl-card--danger">
              <div class="mq-ctrl-card-icon mq-ctrl-card-icon--red"><i class="fa-solid fa-stop"></i></div>
              <div>
                <div class="mq-ctrl-card-title">End Quiz</div>
                <div class="mq-ctrl-card-desc">Immediately close the quiz. Students still in progress will be cut off and auto-submitted.</div>
              </div>
              <div class="mq-ctrl-card-act">
                <button class="mq-ctrl-action-btn mq-ctrl-action-btn--red" onclick="quizAction('end')">
                  <i class="fa-solid fa-stop"></i> End Quiz Now
                </button>
              </div>
            </div>`;
        }
      }
      if (!isReleased && submittedCount > 0) {
        controlCards += `
          <div class="mq-ctrl-card mq-ctrl-card--accent">
            <div class="mq-ctrl-card-icon mq-ctrl-card-icon--blue"><i class="fa-solid fa-flag-checkered"></i></div>
            <div>
              <div class="mq-ctrl-card-title">Release Results</div>
              <div class="mq-ctrl-card-desc">Make scores visible to all enrolled students. This cannot be undone.</div>
            </div>
            <div class="mq-ctrl-card-act">
              <button class="mq-ctrl-action-btn mq-ctrl-action-btn--blue" onclick="quizAction('release_results')">
                <i class="fa-solid fa-flag-checkered"></i> Release Results
              </button>
            </div>
          </div>`;
      }
      if (isReleased) {
        controlCards += `
          <div class="mq-ctrl-card mq-ctrl-card--primary mq-ctrl-card--muted">
            <div class="mq-ctrl-card-icon mq-ctrl-card-icon--green"><i class="fa-solid fa-check-circle"></i></div>
            <div>
              <div class="mq-ctrl-card-title">Results Released</div>
              <div class="mq-ctrl-card-desc">Students can now view their scores. Released at <strong>${fmtDate(quiz.results_released_at)}</strong>.</div>
            </div>
          </div>`;
      }
    } else if (isPublished && mode === 'due_date') {
      if (!isEnded) {
        controlCards += `
          <div class="mq-ctrl-card mq-ctrl-card--danger">
            <div class="mq-ctrl-card-icon mq-ctrl-card-icon--red"><i class="fa-solid fa-lock"></i></div>
            <div>
              <div class="mq-ctrl-card-title">Force Close</div>
              <div class="mq-ctrl-card-desc">Close this quiz before the due date. No new submissions will be accepted.</div>
            </div>
            <div class="mq-ctrl-card-act">
              <button class="mq-ctrl-action-btn mq-ctrl-action-btn--red" onclick="quizAction('force_close')">
                <i class="fa-solid fa-lock"></i> Force Close
              </button>
            </div>
          </div>`;
      }
      if (!isReleased && submittedCount > 0) {
        controlCards += `
          <div class="mq-ctrl-card mq-ctrl-card--accent">
            <div class="mq-ctrl-card-icon mq-ctrl-card-icon--blue"><i class="fa-solid fa-flag-checkered"></i></div>
            <div>
              <div class="mq-ctrl-card-title">Release Results</div>
              <div class="mq-ctrl-card-desc">Make scores visible to all enrolled students. This cannot be undone.</div>
            </div>
            <div class="mq-ctrl-card-act">
              <button class="mq-ctrl-action-btn mq-ctrl-action-btn--blue" onclick="quizAction('release_results')">
                <i class="fa-solid fa-flag-checkered"></i> Release Results
              </button>
            </div>
          </div>`;
      }
      if (isReleased) {
        controlCards += `
          <div class="mq-ctrl-card mq-ctrl-card--primary mq-ctrl-card--muted">
            <div class="mq-ctrl-card-icon mq-ctrl-card-icon--green"><i class="fa-solid fa-check-circle"></i></div>
            <div>
              <div class="mq-ctrl-card-title">Results Released</div>
              <div class="mq-ctrl-card-desc">Students can now view their scores. Released at <strong>${fmtDate(quiz.results_released_at)}</strong>.</div>
            </div>
          </div>`;
      }
    }
    if (!controlCards) {
      controlCards = `<div style="color:var(--text-muted);font-size:.85rem;padding:.5rem 0;display:flex;align-items:center;gap:.5rem;"><i class="fa-solid fa-circle-info"></i> No controls available for this quiz.</div>`;
    }

    content.innerHTML = `
      <div class="mq-settings-grid">
        <div class="mq-settings-section">
          <div class="mq-settings-label">Quiz Info</div>
          <div class="mq-settings-row"><span>Mode</span><strong>${esc(mode.charAt(0).toUpperCase()+mode.slice(1))}</strong></div>
          ${quiz.time_limit_seconds ? `<div class="mq-settings-row"><span>Time Limit</span><strong>${Math.floor(quiz.time_limit_seconds/60)} min</strong></div>` : ''}
          ${quiz.due_date ? `<div class="mq-settings-row"><span>Due Date</span><strong>${fmtDate(quiz.due_date)}</strong></div>` : ''}
          ${quiz.max_attempts ? `<div class="mq-settings-row"><span>Max Attempts</span><strong>${esc(String(quiz.max_attempts))}</strong></div>` : ''}
          ${quiz.live_started_at ? `<div class="mq-settings-row"><span>Started At</span><strong>${fmtDate(quiz.live_started_at)}</strong></div>` : ''}
          ${quiz.live_ended_at   ? `<div class="mq-settings-row"><span>Ended At</span><strong>${fmtDate(quiz.live_ended_at)}</strong></div>` : ''}
          ${quiz.results_released_at ? `<div class="mq-settings-row"><span>Results Released</span><strong>${fmtDate(quiz.results_released_at)}</strong></div>` : ''}
        </div>
        <div class="mq-settings-section">
          <div class="mq-settings-label">Enrollment Snapshot</div>
          <div class="mq-settings-row"><span>Total Enrolled</span><strong>${count}</strong></div>
          <div class="mq-settings-row"><span>Waiting</span><strong style="color:var(--text-muted);">${waitingCount}</strong></div>
          <div class="mq-settings-row"><span>In Progress</span><strong style="color:#f59e0b;">${inProgressCount}</strong></div>
          <div class="mq-settings-row"><span>Submitted</span><strong style="color:#1a9e78;">${submittedCount}</strong></div>
        </div>
        <div class="mq-settings-section mq-settings-section--full">
          <div class="mq-settings-label" style="margin-bottom:1rem;">Controls</div>
          <div class="mq-ctrl-cards">${controlCards}</div>
        </div>
      </div>`;

    updateHero(quiz, postMeta);
    loading.style.display = 'none';
    content.style.display = 'block';

  } catch(e) {
    loading.innerHTML = `<span style="color:#d93025;font-size:.85rem;">Failed to load. <button onclick="loadSettings()" style="background:none;border:none;color:#1a9e78;cursor:pointer;font-weight:700;">Retry</button></span>`;
    console.error('loadSettings', e);
  }
}

async function quizAction(action) {
  const labels = {
    start:           { title:'Start the quiz?',        text:'Only enrolled students will be able to take it.', confirmText:'Yes, Start',  color:'#1a9e78' },
    pause:           { title:'Pause the quiz?',        text:'Students in progress will be moved to waiting and timers will stop.', confirmText:'Pause Quiz', color:'#1f73db' },
    resume:          { title:'Resume the quiz?',       text:'Students in waiting can continue taking the quiz.', confirmText:'Resume Quiz', color:'#1a9e78' },
    end:             { title:'End the quiz now?',       text:'Students in progress will be cut off.',           confirmText:'Yes, End',    color:'#d93025' },
    release_results: { title:'Release results and lock quiz?', text:'Only students with submitted attempts will see scores/reviewer. In-progress attempts will be auto-submitted.', confirmText:'Release & Lock', color:'#1a9e78' },
    force_close:     { title:'Force close this quiz?', text:'No more submissions will be accepted.',           confirmText:'Force Close', color:'#d93025' },
  };
  const lbl = labels[action] || { title:'Confirm', text:'', confirmText:'OK', color:'#1a9e78' };
  const ok = await Swal.fire({ icon:'question', title:lbl.title, text:lbl.text, showCancelButton:true, confirmButtonText:lbl.confirmText, confirmButtonColor:lbl.color });
  if (!ok.isConfirmed) return;
  try {
    const fd = new FormData();
    fd.append('post_id', POST_ID);
    fd.append('action', action);
    const res  = await fetch('API/facultyUI/classroom/quiz/start_live_quiz.php', { method:'POST', body:fd, credentials:'same-origin' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Action failed');
    Swal.fire({ icon:'success', title: json.message || 'Done', timer:1800, showConfirmButton:false });
    loadSettings();
  } catch(e) {
    Swal.fire({ icon:'error', title:'Error', text: String(e.message||e) });
  }
}

/* ══ SUBMISSIONS TAB ══ */
async function loadSubmissions(forceRefresh) {
  const loading = document.getElementById('subLoading');
  const wrap    = document.getElementById('subTableWrap');
  const empty   = document.getElementById('subEmpty');
  const badge   = document.getElementById('subBadge');

  const isInitial = !wrap.dataset.loaded;
  if (isInitial) {
    loading.style.display = 'flex';
    wrap.style.display    = 'none';
    empty.style.display   = 'none';
  }

  try {
    const data = await fetchEnrollments();
    const rows = data.enrollments || [];
    allRows    = rows;
    quizData   = data.quiz || quizData;
    if (badge) badge.textContent = rows.length;

    loading.style.display = 'none';

    if (rows.length === 0) {
      empty.style.display = 'flex';
      wrap.dataset.loaded = '1';
      return;
    }
    renderTable(rows);
    wrap.style.display = 'block';
    wrap.dataset.loaded = '1';

  } catch(e) {
    loading.innerHTML = `<span style="color:#d93025;font-size:.85rem;">Failed. <button onclick="loadSubmissions(true)" style="background:none;border:none;color:#1a9e78;cursor:pointer;font-weight:700;">Retry</button></span>`;
    console.error('loadSubmissions', e);
  }
}

async function refreshSubmissionsBadge() {
  try {
    const data = await fetchEnrollments();
    const rows = data.enrollments || [];
    allRows = rows;
    quizData = data.quiz || quizData;
    const badge = document.getElementById('subBadge');
    if (badge) badge.textContent = rows.length;

    // If user is already on Submissions tab, keep rows live without extra click.
    if (currentTab === 'submissions') {
      const loading = document.getElementById('subLoading');
      const wrap = document.getElementById('subTableWrap');
      const empty = document.getElementById('subEmpty');
      if (rows.length === 0) {
        if (loading) loading.style.display = 'none';
        if (wrap) wrap.style.display = 'none';
        if (empty) empty.style.display = 'flex';
      } else {
        renderTable(rows);
        if (loading) loading.style.display = 'none';
        if (empty) empty.style.display = 'none';
        if (wrap) wrap.style.display = 'block';
      }
    }
  } catch (e) {
    console.error('refreshSubmissionsBadge', e);
  }
}

function renderTable(rows) {
  const tbody = document.getElementById('subTbody');
  if (!tbody) return;
  const sig = rows.map(r => [
    r.student_id || '',
    r.attempt_status || '',
    (r.score === null || r.score === undefined) ? '' : Number(r.score).toFixed(3),
    r.enrolled_at || '',
    r.makeup_valid_until || ''
  ].join('|')).join('||');
  if (sig === lastTableSig) return;
  lastTableSig = sig;
  tbody.innerHTML = rows.map(function(r) {
    const name    = ((r.first_name||'') + ' ' + (r.last_name||'')).trim() || 'Student';
    const snum    = r.student_number || '—';
    const initStr = ((r.first_name||'').charAt(0) + (r.last_name||'').charAt(0)).toUpperCase() || '?';

    let statusHtml = '<span class="mq-chip mq-chip-wait">Enrolled</span>';
    if (r.attempt_status === 'in_progress') statusHtml = '<span class="mq-chip mq-chip-prog"><span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#f59e0b;animation:liveDot 1.1s infinite;margin-right:.25rem;"></span>In Progress</span>';
    if (r.attempt_status === 'submitted')   statusHtml = '<span class="mq-chip mq-chip-done"><i class="fa-solid fa-check" style="font-size:.62rem;"></i> Submitted</span>';

    const scoreHtml   = (r.score !== null && r.score !== undefined) ? fmtWhole(r.score) + ' pts' : '—';
    const enrolledAt  = fmtDate(r.enrolled_at);
    const canRemove   = r.attempt_status !== 'in_progress';
    const viewDis     = (r.score === null || r.score === undefined) ? 'disabled' : '';

    const viewBtn   = `<button class="mq-row-btn mq-row-btn-view" ${viewDis} title="View submission" onclick="openDetail('${esc(r.student_id)}')"><i class="fa-solid fa-eye"></i></button>`;
    const canMakeup = (String(quizData.quiz_mode || '') === 'due_date');
    const makeupActive = !!r.makeup_valid_until;
    const makeupBtn = canMakeup
      ? `<button class="mq-row-btn" title="${makeupActive ? ('Make-up active until ' + esc(fmtDate(r.makeup_valid_until))) : 'Allow make-up access'}" onclick="grantMakeup('${esc(r.student_id)}','${esc(name)}')"><i class="fa-solid ${makeupActive ? 'fa-user-check' : 'fa-user-clock'}"></i></button>`
      : '';
    const removeBtn = canRemove
      ? `<button class="mq-row-btn mq-row-btn-remove" title="Remove student" onclick="removeStudent('${esc(r.student_id)}','${esc(name)}')"><i class="fa-solid fa-user-minus"></i></button>`
      : `<button class="mq-row-btn" disabled title="Cannot remove while in progress"><i class="fa-solid fa-user-minus"></i></button>`;

    return `<tr data-student-id="${esc(r.student_id)}" data-name="${esc(name.toLowerCase())} ${esc(snum.toLowerCase())}">
      <td>
        <div style="display:flex;align-items:center;gap:.6rem;">
          <div class="mq-av">${esc(initStr)}</div>
          <div><div style="font-weight:600;">${esc(name)}</div><div style="font-size:.73rem;color:var(--text-muted);">${esc(snum)}</div></div>
        </div>
      </td>
      <td>${statusHtml}</td>
      <td style="font-weight:600;color:${r.score!==null&&r.score!==undefined?'#1a9e78':'#bbb'};">${esc(scoreHtml)}</td>
      <td style="font-size:.78rem;color:var(--text-muted);">${esc(enrolledAt)}</td>
      <td style="white-space:nowrap;display:flex;gap:.35rem;">${viewBtn} ${makeupBtn} ${removeBtn}</td>
    </tr>`;
  }).join('');
}

async function grantMakeup(studentId, name) {
  const { value: formValues } = await Swal.fire({
    title: 'Allow make-up attempt',
    html:
      `<div style="text-align:left;font-size:.85rem;color:var(--text-muted);margin-bottom:.55rem;">Grant a temporary make-up window for <strong>${esc(name)}</strong>.</div>` +
      `<label style="display:block;text-align:left;font-size:.78rem;margin-bottom:.25rem;">Duration (minutes)</label>` +
      `<input id="mk-min" type="number" min="5" max="1440" value="120" class="swal2-input" style="margin:.2rem 0 .6rem;">` +
      `<label style="display:block;text-align:left;font-size:.78rem;margin-bottom:.25rem;">Reason / Note (optional)</label>` +
      `<textarea id="mk-note" class="swal2-textarea" placeholder="Excuse letter approved" style="margin:.2rem 0 0;"></textarea>`,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Grant Access',
    preConfirm: () => {
      const mins = parseInt(document.getElementById('mk-min').value, 10);
      const note = (document.getElementById('mk-note').value || '').trim();
      if (!Number.isFinite(mins) || mins < 5 || mins > 1440) {
        Swal.showValidationMessage('Minutes must be between 5 and 1440');
        return false;
      }
      return { mins, note };
    }
  });
  if (!formValues) return;

  try {
    const fd = new FormData();
    fd.append('post_id', POST_ID);
    fd.append('student_id', studentId);
    fd.append('minutes', String(formValues.mins));
    fd.append('note', formValues.note || '');
    const res = await fetch('API/facultyUI/classroom/quiz/grant_makeup_access.php', { method:'POST', body:fd, credentials:'same-origin' });
    const json = await res.json();
    if (!json || json.status !== 'success') throw new Error(json?.message || 'Failed');
    Swal.fire({ icon:'success', title:'Make-up access granted', text:`Valid until ${fmtDate(json.valid_until)}`, timer:1800, showConfirmButton:false });
    loadSubmissions(true);
  } catch (e) {
    Swal.fire({ icon:'error', title:'Could not grant access', text:String(e.message||e) });
  }
}

function filterSubmissions() {
  const q = (document.getElementById('subSearch')?.value || '').toLowerCase().trim();
  document.querySelectorAll('#subTbody tr').forEach(function(tr) {
    tr.style.display = (tr.dataset.name || '').includes(q) ? '' : 'none';
  });
}

function toggleLiveMonitor() {
  const on  = document.getElementById('liveToggle')?.checked;
  const lbl = document.getElementById('liveLabel');
  if (on) {
    lbl.classList.add('active');
    if (livePollTimer) clearInterval(livePollTimer);
    livePollTimer = setInterval(function(){ loadSubmissions(true); }, 4000);
  } else {
    lbl.classList.remove('active');
    if (livePollTimer) { clearInterval(livePollTimer); livePollTimer = null; }
  }
}

async function removeStudent(studentId, name) {
  const ok = await Swal.fire({
    icon:'warning', title:'Remove student?',
    html:`<span style="font-size:.88rem;">Remove <strong>${esc(name)}</strong> from this quiz?<br>They can re-enroll if the quiz is still open.</span>`,
    showCancelButton:true, confirmButtonText:'Yes, Remove', confirmButtonColor:'#d93025',
  });
  if (!ok.isConfirmed) return;
  try {
    const fd = new FormData();
    fd.append('post_id', POST_ID);
    fd.append('student_id', studentId);
    const res  = await fetch('API/facultyUI/classroom/quiz/remove_enrollment.php', { method:'POST', body:fd, credentials:'same-origin' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Remove failed');
    Swal.fire({ icon:'success', title:'Removed', timer:1200, showConfirmButton:false });
    loadSubmissions(true);
  } catch(e) {
    Swal.fire({ icon:'error', title:'Could not remove', text: String(e.message||e) });
  }
}

/* ── STUDENT DETAIL MODAL ── */
function openDetail(studentId) {
  const row = allRows.find(r => String(r.student_id) === String(studentId));
  if (!row) return;
  const name = ((row.first_name||'') + ' ' + (row.last_name||'')).trim() || 'Student';
  document.getElementById('detailTitle').textContent = name;
  document.getElementById('detailSub').textContent   = row.student_number || '—';

  const scoreHtml = (row.score !== null && row.score !== undefined)
    ? `<div class="mq-detail-score"><span>Score</span><strong>${fmtWhole(row.score)} pts</strong></div>`
    : '<p style="color:#999;font-size:.85rem;margin-bottom:.75rem;">No score recorded yet.</p>';

  const statusMap = {
    'in_progress': '<span class="mq-chip mq-chip-prog">In Progress</span>',
    'submitted':   '<span class="mq-chip mq-chip-done">Submitted</span>',
  };
  const statusHtml = statusMap[row.attempt_status] || '<span class="mq-chip mq-chip-wait">Enrolled — Not started</span>';

  document.getElementById('detailBody').innerHTML = `
    <div style="margin-bottom:.75rem;">${statusHtml}</div>
    ${scoreHtml}
    <div class="mq-detail-row"><span>Student #</span><strong>${esc(row.student_number||'—')}</strong></div>
    <div class="mq-detail-row"><span>Enrolled At</span><strong>${fmtDate(row.enrolled_at)}</strong></div>`;

  document.getElementById('detailOverlay').classList.add('show');
  document.getElementById('detailModal').classList.add('show');
}

function closeDetail() {
  document.getElementById('detailOverlay').classList.remove('show');
  document.getElementById('detailModal').classList.remove('show');
}

/* ══ ANALYTICS TAB ══ */
async function loadAnalytics() {
  const loading = document.getElementById('anLoading');
  const content = document.getElementById('anContent');
  loading.style.display = 'flex';
  content.style.display = 'none';

  try {
    const data        = await fetchEnrollments();
    const enrollments = data.enrollments || [];
    const quiz        = data.quiz || {};

    const total     = enrollments.length;
    const submitted = enrollments.filter(e => e.attempt_status === 'submitted').length;
    const inProg    = enrollments.filter(e => e.attempt_status === 'in_progress').length;
    const waiting   = total - submitted - inProg;
    const scores    = enrollments.map(e => e.score).filter(s => s !== null && s !== undefined);
    const avg       = scores.length ? (scores.reduce((a,b)=>a+b,0)/scores.length).toFixed(1) : '—';
    const highest   = scores.length ? Math.max(...scores).toFixed(1) : '—';
    const lowest    = scores.length ? Math.min(...scores).toFixed(1) : '—';
    const pct       = total ? Math.round((submitted/total)*100) : 0;

    const doneW  = total ? (submitted/total*100).toFixed(1) : 0;
    const progW  = total ? (inProg/total*100).toFixed(1)   : 0;
    const waitW  = total ? (waiting/total*100).toFixed(1)  : 100;

    content.innerHTML = `
      <div class="mq-an-wrap">

        <!-- Engagement bar -->
        <div class="mq-an-engage">
          <div class="mq-an-engage-header">
            <div class="mq-an-engage-title"><i class="fa-solid fa-users" style="color:var(--primary);"></i> &nbsp;Engagement</div>
            <div class="mq-an-engage-pct">${pct}%<span>completion</span></div>
          </div>
          <div class="mq-an-bar">
            <div class="mq-an-bar-seg mq-an-bar-seg--done" style="width:${doneW}%;"></div>
            <div class="mq-an-bar-seg mq-an-bar-seg--prog" style="width:${progW}%;"></div>
            <div class="mq-an-bar-seg mq-an-bar-seg--wait" style="width:${waitW}%;"></div>
          </div>
          <div class="mq-an-legend">
            <div class="mq-an-legend-item"><div class="mq-an-legend-dot" style="background:#1a9e78;"></div> Submitted — <strong>${submitted}</strong></div>
            <div class="mq-an-legend-item"><div class="mq-an-legend-dot" style="background:#f59e0b;"></div> In Progress — <strong>${inProg}</strong></div>
            <div class="mq-an-legend-item"><div class="mq-an-legend-dot" style="background:#cbd5e1;"></div> Waiting — <strong>${waiting}</strong></div>
            <div class="mq-an-legend-item" style="margin-left:auto;"><i class="fa-solid fa-users" style="font-size:.72rem;"></i> Total enrolled: <strong>${total}</strong></div>
          </div>
        </div>

        <!-- Stat blocks -->
        <div class="mq-an-stats-row">
          <div class="mq-an-stat-block">
            <div class="mq-an-stat-block-title"><i class="fa-solid fa-chart-bar" style="color:var(--primary);"></i> Participation</div>
            <div class="mq-an-stat-row">
              <span><i class="fa-solid fa-users"></i> Total Enrolled</span>
              <strong style="font-size:1.1rem;">${total}</strong>
            </div>
            <div class="mq-an-stat-row">
              <span><i class="fa-solid fa-check-circle" style="color:#1a9e78;"></i> Submitted</span>
              <strong style="color:#1a9e78;">${submitted}</strong>
            </div>
            <div class="mq-an-stat-row">
              <span><i class="fa-solid fa-spinner" style="color:#f59e0b;"></i> In Progress</span>
              <strong style="color:#f59e0b;">${inProg}</strong>
            </div>
            <div class="mq-an-stat-row">
              <span><i class="fa-solid fa-clock" style="color:var(--text-muted);"></i> Not Started</span>
              <strong style="color:var(--text-muted);">${waiting}</strong>
            </div>
          </div>
          <div class="mq-an-stat-block">
            <div class="mq-an-stat-block-title"><i class="fa-solid fa-star" style="color:#f59e0b;"></i> Scores</div>
            <div class="mq-an-stat-row">
              <span><i class="fa-solid fa-calculator"></i> Average</span>
              <strong style="font-size:1.1rem;">${avg}</strong>
            </div>
            <div class="mq-an-stat-row">
              <span><i class="fa-solid fa-arrow-up" style="color:#1a9e78;"></i> Highest</span>
              <strong style="color:#1a9e78;">${highest}</strong>
            </div>
            <div class="mq-an-stat-row">
              <span><i class="fa-solid fa-arrow-down" style="color:#d93025;"></i> Lowest</span>
              <strong style="color:#d93025;">${lowest}</strong>
            </div>
            <div class="mq-an-stat-row">
              <span><i class="fa-solid fa-file-alt"></i> Scored Submissions</span>
              <strong>${scores.length}</strong>
            </div>
          </div>
        </div>

      </div>`;

    loading.style.display = 'none';
    content.style.display = 'block';

  } catch(e) {
    loading.innerHTML = '<span style="color:#d93025;font-size:.85rem;">Failed to load analytics.</span>';
    console.error('loadAnalytics', e);
  }
}

/* ── BOOT ── */
loadSettings();
refreshSubmissionsBadge();
setInterval(refreshSubmissionsBadge, 6000);
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') refreshSubmissionsBadge();
});
window.addEventListener('focus', refreshSubmissionsBadge);

const AUTO_REFRESH_MS = 8000;
let autoRefreshTimer = null;

function autoRefreshCurrentTab() {
  // Keep background counts fresh, but do not re-render the currently open pane.
  // This prevents scroll jumps while the user is reading/scrolled down.
  refreshSubmissionsBadge();
  // Submissions live updates are handled by toggleLiveMonitor() only.
}

function startAutoRefresh() {
  if (autoRefreshTimer) clearInterval(autoRefreshTimer);
  autoRefreshTimer = setInterval(autoRefreshCurrentTab, AUTO_REFRESH_MS);
}

startAutoRefresh();
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') autoRefreshCurrentTab();
});
window.addEventListener('focus', autoRefreshCurrentTab);
</script>
</body>
</html>


