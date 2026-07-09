<?php
/**
 * classRoom.php — Tere LEARN | Class Workspace
 * ?class_id=UUID
 */
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
$level = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
if (!isset($_SESSION['user_id']) || $level !== 2) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php'); exit;
}
$class_id = trim($_GET['class_id'] ?? '');
if (!$class_id) { header('Location: ' . TERELEARN_BASE_URL . 'facultyUI.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tere Learn — Classroom</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    :root{--primary:#1a9e78;--primary-dark:#0d7a5e;--primary-light:#e6f7f2;--primary-mid:rgba(26,158,120,.15);--accent:#1f73db;--accent-light:#e8f0fe;--danger:#d93025;--warning:#f57c00;--border:#e8eaed;--text:#1c2027;--text-muted:#5f6368;--bg:#f4f6f9;--surface:#ffffff;--nav-h:41px;--class-max:1024px;--radius:12px;--radius-sm:8px;--shadow:0 1px 3px rgba(0,0,0,.08),0 1px 2px -1px rgba(0,0,0,.06);--shadow-md:0 4px 12px rgba(0,0,0,.08),0 2px 4px -1px rgba(0,0,0,.04);--shadow-lg:0 8px 24px rgba(0,0,0,.10),0 4px 8px -2px rgba(0,0,0,.06);--trans:.22s cubic-bezier(.4,0,.2,1);--tl-ui-background:hsl(210 20% 98%);--tl-ui-foreground:hsl(215 25% 12%);--tl-ui-card:hsl(0 0% 100%);--tl-ui-card-border:hsl(214 20% 90%);--tl-ui-muted:hsl(210 15% 96%);--tl-ui-muted-foreground:hsl(215 15% 50%);--tl-ui-primary:hsl(145 61% 26%);--tl-ui-primary-foreground:hsl(0 0% 100%);--tl-ui-secondary:hsl(145 30% 94%);--tl-ui-accent:hsl(145 40% 94%);--tl-ui-destructive:hsl(0 72% 51%);--tl-ui-ring:hsl(145 61% 26%);--tl-ui-chart-1:hsl(145 61% 26%);--tl-ui-chart-2:hsl(38 95% 55%);--tl-ui-chart-3:hsl(0 72% 51%);--tl-ui-chart-4:hsl(217 91% 60%);--tl-ui-chart-5:hsl(270 65% 60%);--tl-ui-radius:.5rem;}
    body.dark{--primary:#2ecc9a;--primary-dark:#1a9e78;--primary-light:rgba(46,204,154,.12);--primary-mid:rgba(46,204,154,.10);--accent:#4d90e2;--accent-light:rgba(77,144,226,.14);--border:#2e3849;--text:#e4ecf7;--text-muted:#8a9ab5;--bg:#0f1724;--surface:#182030;--tl-ui-background:hsl(215 28% 10%);--tl-ui-foreground:hsl(210 20% 94%);--tl-ui-card:hsl(215 25% 13%);--tl-ui-card-border:hsl(215 20% 24%);--tl-ui-muted:hsl(215 20% 16%);--tl-ui-muted-foreground:hsl(215 15% 55%);--tl-ui-primary:hsl(145 55% 40%);--tl-ui-primary-foreground:hsl(0 0% 100%);--tl-ui-secondary:hsl(145 25% 20%);--tl-ui-accent:hsl(145 30% 18%);--tl-ui-destructive:hsl(0 65% 55%);--tl-ui-ring:hsl(145 55% 40%);--tl-ui-chart-1:hsl(145 55% 45%);--tl-ui-chart-2:hsl(38 90% 55%);--tl-ui-chart-3:hsl(0 65% 55%);--tl-ui-chart-4:hsl(217 85% 65%);--tl-ui-chart-5:hsl(270 60% 65%);}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);transition:background var(--trans),color var(--trans);}
    ::-webkit-scrollbar{width:5px;}::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}

/* ── FIX: SweetAlert always above file viewer (z-index 2000) ── */
    .swal2-container { z-index: 99999 !important; }


    /* ══ LIVE QUIZ LOBBY ══ */
@keyframes lq-overlay-in{from{opacity:0;}to{opacity:1;}}
@keyframes lq-drawer-in{from{transform:translateX(100%);opacity:0;}to{transform:translateX(0);opacity:1;}}
@keyframes lq-drawer-out{from{transform:translateX(0);opacity:1;}to{transform:translateX(100%);opacity:0;}}
@keyframes lq-pulse{0%,100%{transform:scale(1);}50%{transform:scale(.85);opacity:.55;}}
@keyframes lq-fade{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}

.lq-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.48);z-index:500;}
.lq-overlay.show{display:block;animation:lq-overlay-in .22s ease;}
.lq-drawer{position:fixed;top:0;right:0;bottom:0;width:440px;max-width:96vw;background:var(--surface);box-shadow:-10px 0 50px rgba(0,0,0,.2);z-index:501;display:none;flex-direction:column;}
.lq-drawer.open{display:flex;animation:lq-drawer-in .28s cubic-bezier(.4,0,.2,1);}
.lq-drawer.closing{display:flex;animation:lq-drawer-out .22s cubic-bezier(.4,0,.2,1) forwards;}

.lq-head{padding:1.1rem 1.25rem;border-bottom:1.5px solid var(--border);display:flex;align-items:center;gap:.75rem;flex-shrink:0;background:linear-gradient(135deg,var(--primary-light),rgba(31,115,219,.08));}
.lq-head-icon{width:42px;height:42px;border-radius:12px;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.lq-head-info{flex:1;min-width:0;}
.lq-title{font-size:1rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:.5rem;}
.lq-sub{font-size:.74rem;color:var(--text-muted);margin-top:.1rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.lq-mode-pill{font-size:.62rem;font-weight:700;padding:.18rem .55rem;border-radius:20px;text-transform:uppercase;letter-spacing:.4px;}
.lq-mode-live{background:#fdecea;color:var(--danger);}
.lq-mode-due{background:var(--accent-light);color:var(--accent);}
.lq-close{width:34px;height:34px;border:none;background:var(--bg);color:var(--text-muted);border-radius:50%;cursor:pointer;font-size:.85rem;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;}
.lq-close:hover{background:#fdecea;color:var(--danger);}

.lq-status{padding:.85rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.6rem;background:var(--bg);font-size:.78rem;font-weight:600;color:var(--text-muted);}
.lq-status .lq-dot{width:9px;height:9px;border-radius:50%;background:var(--text-muted);}
.lq-status.is-waiting .lq-dot{background:var(--warning);animation:lq-pulse 1.6s ease-in-out infinite;}
.lq-status.is-live .lq-dot{background:var(--danger);animation:lq-pulse 1.2s ease-in-out infinite;}
.lq-status.is-ended .lq-dot{background:var(--text-muted);}
.lq-status.is-waiting{color:var(--warning);}
.lq-status.is-live{color:var(--danger);}

.lq-stats{padding:1rem 1.25rem;display:flex;gap:.75rem;border-bottom:1px solid var(--border);}
.lq-stat{flex:1;background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:.7rem .85rem;}
.lq-stat-label{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);}
.lq-stat-val{font-size:1.4rem;font-weight:700;color:var(--text);margin-top:.1rem;font-family:'DM Mono',monospace;}

.lq-list{flex:1;overflow-y:auto;padding:.75rem 1rem;}
.lq-list::-webkit-scrollbar{width:4px;}
.lq-list::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
.lq-empty{text-align:center;padding:2.5rem 1rem;color:var(--text-muted);}
.lq-empty i{font-size:2.6rem;opacity:.18;margin-bottom:.6rem;display:block;}
.lq-empty h4{font-size:.92rem;font-weight:600;margin-bottom:.25rem;color:var(--text);}
.lq-empty p{font-size:.78rem;}

.lq-row{display:flex;align-items:center;gap:.7rem;padding:.6rem .7rem;border:1px solid var(--border);border-radius:10px;margin-bottom:.45rem;background:var(--surface);animation:lq-fade .2s ease;}
.lq-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;font-size:.78rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.lq-row-info{flex:1;min-width:0;}
.lq-row-name{font-size:.85rem;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.lq-row-meta{font-size:.7rem;color:var(--text-muted);margin-top:.05rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.lq-row-tag{font-size:.62rem;font-weight:700;padding:.15rem .5rem;border-radius:20px;background:var(--bg);color:var(--text-muted);border:1px solid var(--border);text-transform:uppercase;letter-spacing:.3px;flex-shrink:0;}
.lq-row-tag.is-progress{background:var(--primary-light);color:var(--primary);border-color:rgba(26,158,120,.25);}
.lq-row-tag.is-submitted{background:var(--accent-light);color:var(--accent);border-color:rgba(31,115,219,.25);}

.lq-foot{padding:.9rem 1.1rem;border-top:1.5px solid var(--border);display:flex;gap:.55rem;flex-shrink:0;background:var(--bg);}
.lq-btn{flex:1;padding:.7rem;border-radius:10px;border:none;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;transition:all .18s;display:flex;align-items:center;justify-content:center;gap:.45rem;}
.lq-btn:disabled{opacity:.55;cursor:not-allowed;}
.lq-btn-start{background:var(--primary);color:#fff;box-shadow:0 2px 10px rgba(26,158,120,.3);}
.lq-btn-start:hover:not(:disabled){background:var(--primary-dark);transform:translateY(-1px);}
.lq-btn-end{background:#fdecea;color:var(--danger);border:1.5px solid #f5c2c7;}
.lq-btn-end:hover:not(:disabled){background:var(--danger);color:#fff;border-color:var(--danger);}

/* The trigger button you place on the quiz card */
.lq-trigger{display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .85rem;border-radius:20px;border:1.5px solid var(--primary);background:var(--primary-light);color:var(--primary);font-family:inherit;font-size:.76rem;font-weight:700;cursor:pointer;transition:all .18s;}
.lq-trigger:hover{background:var(--primary);color:#fff;}
.lq-trigger .lq-trigger-count{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;border-radius:20px;background:var(--primary);color:#fff;font-size:.62rem;font-weight:700;}
.lq-trigger:hover .lq-trigger-count{background:#fff;color:var(--primary);}

    /* ── INVITE MODAL ANIMATIONS ── */
    @keyframes invCheckPop {
      0% { transform: scale(0); opacity: 0; }
      60% { transform: scale(1.4); opacity: 1; }
      100% { transform: scale(1); opacity: 1; }
    }
    @keyframes invSlideIn {
      from { opacity: 0; transform: translateX(-8px); }
      to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes invChipPop {
      0% { transform: scale(0.6); opacity: 0; }
      70% { transform: scale(1.08); }
      100% { transform: scale(1); opacity: 1; }
    }
    @keyframes invFloat {
      0%,100% { transform: translateY(0); }
      50% { transform: translateY(-4px); }
    }
    .inv-chip-anim { animation: invChipPop .25s ease; }

    /* ══ WAITLIST NOTIFICATION BAR ══ */
    @keyframes wl-slide-in{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}
    @keyframes wl-pulse-ring{0%{transform:scale(1);opacity:1;}70%{transform:scale(2.4);opacity:0;}100%{opacity:0;}}
    @keyframes wl-pulse-dot{0%,100%{transform:scale(1);}50%{transform:scale(.8);opacity:.6;}}
    @keyframes wl-drawer-in{from{transform:translateX(100%);opacity:0;}to{transform:translateX(0);opacity:1;}}
    @keyframes wl-drawer-out{from{transform:translateX(0);opacity:1;}to{transform:translateX(100%);opacity:0;}}
    @keyframes wl-overlay-in{from{opacity:0;}to{opacity:1;}}

    .wl-bar{background:linear-gradient(135deg,#fffbf0,#fff8e8);border-bottom:1.5px solid #fcd34d;padding:.65rem 1.5rem;display:none;align-items:center;gap:.85rem;animation:wl-slide-in .3s ease;position:sticky;top:calc(var(--nav-h) + 44px);z-index:90;}
    .wl-bar.visible{display:flex;}
    body.dark .wl-bar{background:linear-gradient(135deg,rgba(250,238,218,.07),rgba(250,199,117,.04));border-bottom-color:rgba(250,199,117,.22);}
    .wl-pulse-wrap{position:relative;width:24px;height:24px;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .wl-pulse-ring{position:absolute;inset:0;border-radius:50%;border:2px solid #f59e0b;animation:wl-pulse-ring 2.2s ease-out infinite;}
    .wl-pulse-dot{width:11px;height:11px;border-radius:50%;background:#f59e0b;animation:wl-pulse-dot 2s ease-in-out infinite;}
    .wl-bar-icon{width:36px;height:36px;border-radius:50%;background:#faeeda;border:2px solid #fcd34d;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .wl-bar-text{flex:1;min-width:0;}
    .wl-bar-title{font-size:.86rem;font-weight:700;color:#92400e;display:flex;align-items:center;gap:.45rem;}
    body.dark .wl-bar-title{color:#fcd34d;}
    .wl-bar-sub{font-size:.73rem;color:#b45309;margin-top:.05rem;}
    body.dark .wl-bar-sub{color:#fac775;}
    .wl-bar-btn{display:inline-flex;align-items:center;gap:.55rem;padding:.5rem 1.15rem;border-radius:20px;background:#f59e0b;color:#451a03;border:none;font-family:inherit;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .18s;white-space:nowrap;box-shadow:0 2px 10px rgba(245,158,11,.38);}
    .wl-bar-btn:hover{background:#d97706;box-shadow:0 4px 14px rgba(245,158,11,.52);transform:translateY(-1px);}
    .wl-bar-badge{min-width:20px;height:20px;border-radius:20px;background:#451a03;color:#fef3c7;font-size:.68rem;font-weight:700;padding:0 5px;display:inline-flex;align-items:center;justify-content:center;}
        .wl-tab-dot{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 4px;border-radius:20px;background:#f59e0b;color:#451a03;font-size:.6rem;font-weight:700;margin-left:.3rem;animation:wl-pulse-dot 2s ease-in-out infinite;}
    @keyframes wl-tab-pop{0%,100%{transform:scale(1);}30%{transform:scale(1.7);}60%{transform:scale(0.9);}}
    .wl-tab-dot.pop{animation:wl-tab-pop .5s ease-out!important;}


.pc-start-quiz-btn {
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .4rem .85rem; border-radius: 10px; font-size: .78rem;
  font-weight: 700; font-family: inherit; cursor: pointer;
  background: #1a9e78; color: #fff; border: none;
  box-shadow: 0 2px 8px rgba(26,158,120,.35);
  transition: all .18s;
}
.pc-start-quiz-btn:hover:not(:disabled) {
  background: #0d7a5e; transform: translateY(-1px);
}
.pc-start-quiz-btn:disabled { opacity: .55; cursor: not-allowed; }
.pc-start-quiz-btn--live {
  background: #d93025;
  box-shadow: 0 2px 8px rgba(217,48,37,.35);
}
.pc-start-quiz-btn--live:hover:not(:disabled) { background: #b1271c; }
     
    

    /* ══ WAITLIST DRAWER ══ */
    .wl-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.48);z-index:500;}
    .wl-overlay.show{display:block;animation:wl-overlay-in .22s ease;}
    .wl-drawer{position:fixed;top:0;right:0;bottom:0;width:420px;max-width:96vw;background:var(--surface);box-shadow:-10px 0 50px rgba(0,0,0,.2);z-index:501;display:none;flex-direction:column;}
    .wl-drawer.open{display:flex;animation:wl-drawer-in .28s cubic-bezier(.4,0,.2,1);}
    .wl-drawer.closing{display:flex;animation:wl-drawer-out .22s cubic-bezier(.4,0,.2,1) forwards;}
    .wl-drawer-head{padding:1.1rem 1.25rem 1rem;border-bottom:1.5px solid var(--border);display:flex;align-items:center;gap:.75rem;flex-shrink:0;background:linear-gradient(135deg,#fffbf0,#fff8e8);}
    body.dark .wl-drawer-head{background:linear-gradient(135deg,rgba(250,238,218,.06),rgba(250,199,117,.03));}
    .wl-drawer-head-icon{width:42px;height:42px;border-radius:50%;background:#faeeda;border:2px solid #fcd34d;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .wl-drawer-head-info{flex:1;}
    .wl-drawer-title{font-size:1rem;font-weight:700;color:#92400e;display:flex;align-items:center;gap:.5rem;}
    body.dark .wl-drawer-title{color:#fcd34d;}
    .wl-drawer-sub{font-size:.73rem;color:#b45309;margin-top:.1rem;}
    body.dark .wl-drawer-sub{color:#fac775;}
    .wl-drawer-badge{min-width:22px;height:22px;border-radius:20px;background:#f59e0b;color:#451a03;font-size:.68rem;font-weight:700;padding:0 6px;display:inline-flex;align-items:center;justify-content:center;}
    .wl-drawer-close{width:34px;height:34px;border:none;background:rgba(245,158,11,.14);color:#92400e;border-radius:50%;cursor:pointer;font-size:.88rem;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;}
    body.dark .wl-drawer-close{color:#fcd34d;}
    .wl-drawer-close:hover{background:#fde68a;color:#451a03;}
    .wl-drawer-search-row{padding:.65rem 1rem;border-bottom:1px solid var(--border);background:var(--bg);flex-shrink:0;display:flex;align-items:center;gap:.5rem;}
    .wl-search-input{flex:1;padding:.4rem .85rem;border:1.5px solid var(--border);border-radius:20px;font-size:.8rem;font-family:inherit;background:var(--surface);color:var(--text);transition:border-color .15s,box-shadow .15s;}
    .wl-search-input:focus{border-color:#f59e0b;outline:none;box-shadow:0 0 0 3px rgba(245,158,11,.14);}
    .wl-sort-select{padding:.38rem .6rem;border:1.5px solid var(--border);border-radius:10px;font-size:.73rem;font-family:inherit;background:var(--surface);color:var(--text);}
    .wl-list{flex:1;overflow-y:auto;padding:.85rem 1rem;}
    .wl-list::-webkit-scrollbar{width:4px;}
    .wl-list::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
    .wl-empty-state{text-align:center;padding:3rem 1rem;color:var(--text-muted);}
    .wl-empty-state .wl-ei{font-size:2.8rem;opacity:.18;margin-bottom:.65rem;}
    .wl-empty-state h3{font-size:.95rem;font-weight:600;margin-bottom:.3rem;}
    .wl-empty-state p{font-size:.82rem;}
    .wl-card{background:var(--surface);border:1.5px solid var(--border);border-radius:14px;padding:.9rem 1rem;margin-bottom:.65rem;transition:border-color .18s,box-shadow .18s;animation:fadeUp .2s ease;}
    .wl-card:hover{border-color:#fcd34d;box-shadow:0 2px 14px rgba(245,158,11,.1);}
    .wl-card-top{display:flex;align-items:center;gap:.75rem;margin-bottom:.65rem;}
    .wl-av{width:42px;height:42px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.88rem;font-weight:700;border:2px solid;}
    .wl-av-0{background:#e1f5ee;color:#085041;border-color:#5dcaa5;}
    .wl-av-1{background:#e6f1fb;color:#0c447c;border-color:#85b7eb;}
    .wl-av-2{background:#eeedfe;color:#3c3489;border-color:#afa9ec;}
    .wl-av-3{background:#faeeda;color:#633806;border-color:#fac775;}
    .wl-av-4{background:#fbeaf0;color:#72243e;border-color:#ed93b1;}
    .wl-card-info{flex:1;min-width:0;}
    .wl-card-name{font-size:.88rem;font-weight:700;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .wl-card-email{font-size:.72rem;color:var(--text-muted);margin-top:.05rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .wl-card-time{font-size:.64rem;color:var(--text-muted);flex-shrink:0;white-space:nowrap;}
    .wl-card-tags{display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.75rem;}
    .wl-tag{font-size:.65rem;font-weight:600;padding:.15rem .52rem;border-radius:20px;display:flex;align-items:center;gap:.28rem;}
    .wl-tag-id{background:var(--bg);color:var(--text-muted);border:1px solid var(--border);}
    .wl-tag-course{background:#e6f1fb;color:#0c447c;border:1px solid #85b7eb;}
    .wl-tag-yr{background:var(--primary-light);color:var(--primary);border:1px solid rgba(26,158,120,.22);}
    .wl-card-actions{display:flex;gap:.5rem;}
    .wl-admit-btn{flex:1;padding:.5rem;border-radius:10px;background:var(--primary);color:#fff;border:none;font-family:inherit;font-size:.8rem;font-weight:700;cursor:pointer;transition:all .18s;display:flex;align-items:center;justify-content:center;gap:.38rem;box-shadow:0 2px 8px rgba(26,158,120,.28);}
    .wl-admit-btn:hover{background:var(--primary-dark);transform:translateY(-1px);box-shadow:0 3px 12px rgba(26,158,120,.42);}
    .wl-decline-btn{padding:.5rem .88rem;border-radius:10px;background:var(--bg);color:var(--text-muted);border:1.5px solid var(--border);font-family:inherit;font-size:.8rem;font-weight:600;cursor:pointer;transition:all .18s;display:flex;align-items:center;gap:.3rem;}
    .wl-decline-btn:hover{background:#fdecea;color:var(--danger);border-color:#f5c2c7;}
    .wl-drawer-footer{padding:.9rem 1.1rem;border-top:1.5px solid var(--border);display:none;gap:.6rem;flex-shrink:0;background:var(--bg);}
    .wl-drawer-footer.visible{display:flex;}
    .wl-admit-all-btn{flex:1;padding:.62rem;border-radius:10px;background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;border:none;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;transition:all .18s;display:flex;align-items:center;justify-content:center;gap:.45rem;box-shadow:0 2px 10px rgba(26,158,120,.3);}
    .wl-admit-all-btn:hover{opacity:.9;transform:translateY(-1px);}
    .wl-decline-all-btn{padding:.62rem 1rem;border-radius:10px;background:var(--surface);color:var(--text-muted);border:1.5px solid var(--border);font-family:inherit;font-size:.82rem;font-weight:600;cursor:pointer;transition:all .18s;display:flex;align-items:center;gap:.35rem;}
    .wl-decline-all-btn:hover{background:#fdecea;color:var(--danger);border-color:#f5c2c7;}


    @keyframes mq-fade { from { opacity: 0; } to{ opacity: 1; } }
    @keyframes mq-pop  { from { opacity: 0; transform: translate(-50%, -48%) scale(.96); } to { opacity: 1; transform: translate(-50%, -50%) scale(1); } }
    @keyframes mq-pulse-dot { 0%,100% { transform: scale(1); opacity: 1; } 50% { transform: scale(.7); opacity: .5; } }

    .mq-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:900; }
    .mq-overlay.show { display:block; animation: mq-fade .2s ease; }
    .mq-overlay-2 { z-index:1000; }

    .mq-modal { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
      width:min(960px,94vw); max-height:90vh; background:var(--surface); border-radius:18px;
      box-shadow:var(--shadow-lg); z-index:901; flex-direction:column; overflow:hidden; }
    .mq-modal.show { display:flex; animation: mq-pop .25s cubic-bezier(.4,0,.2,1); }
    .mq-modal-detail { width:min(820px,94vw); z-index:1001; }

    .mq-head { display:flex; align-items:center; justify-content:space-between; gap:1rem;
      padding:1rem 1.25rem; border-bottom:1.5px solid var(--border);
      background:linear-gradient(135deg,var(--primary-light),rgba(31,115,219,.08)); flex-shrink:0; }
    body.dark .mq-head { background:linear-gradient(135deg,rgba(46,204,154,.12),rgba(77,144,226,.08)); }
    .mq-head-left { display:flex; align-items:center; gap:.85rem; min-width:0; }
    .mq-head-icon { width:42px; height:42px; border-radius:12px; background:var(--primary);
      color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.05rem; flex-shrink:0; }
    .mq-head-icon-detail { background:var(--accent); }
    .mq-head-title { font-size:1.05rem; font-weight:700; color:var(--text); }
    .mq-head-sub { font-size:.78rem; color:var(--text-muted); margin-top:.1rem;
      white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:520px; }
    .mq-head-right { display:flex; align-items:center; gap:.6rem; flex-shrink:0; }
    .mq-status-pill { display:inline-flex; align-items:center; gap:.4rem; padding:.3rem .75rem;
      border-radius:20px; font-size:.72rem; font-weight:700; background:var(--bg);
      color:var(--text-muted); border:1.5px solid var(--border); }
    .mq-status-pill i { font-size:.55rem; }
    .mq-status-pill.is-open    { background:#e1f5ee; color:#085041; border-color:#5dcaa5; }
    .mq-status-pill.is-closed  { background:#fdecea; color:#a91409; border-color:#f5c2c7; }
    .mq-status-pill.is-draft   { background:#faeeda; color:#633806; border-color:#fac775; }
    .mq-status-pill.is-released{ background:#eeedfe; color:#3c3489; border-color:#afa9ec; }
    .mq-close { width:34px; height:34px; border:none; background:rgba(0,0,0,.06); color:var(--text-muted);
      border-radius:50%; cursor:pointer; font-size:.9rem; transition:all .15s; }
    .mq-close:hover { background:var(--danger); color:#fff; }
    body.dark .mq-close { background:rgba(255,255,255,.06); }

    .mq-tabs { display:flex; gap:.25rem; padding:0 1.25rem; border-bottom:1.5px solid var(--border);
      background:var(--surface); flex-shrink:0; overflow-x:auto; }
    .mq-tab { display:inline-flex; align-items:center; gap:.5rem; padding:.85rem 1.1rem;
      border:none; background:none; font-family:inherit; font-size:.85rem; font-weight:600;
      color:var(--text-muted); cursor:pointer; border-bottom:3px solid transparent;
      transition:all .18s; white-space:nowrap; }
    .mq-tab:hover { color:var(--primary); }
    .mq-tab.active { color:var(--primary); border-bottom-color:var(--primary); }
    .mq-tab-badge { display:inline-flex; align-items:center; justify-content:center; min-width:20px;
      height:20px; padding:0 6px; border-radius:20px; background:var(--primary); color:#fff;
      font-size:.65rem; font-weight:700; }

    .mq-body { flex:1; overflow-y:auto; padding:1.25rem 1.5rem; }
    .mq-body::-webkit-scrollbar { width:6px; }
    .mq-body::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }
    .mq-pane { display:none; }
    .mq-pane.active { display:block; animation: mq-fade .2s ease; }
    .mq-loading { text-align:center; padding:3rem 1rem; color:var(--text-muted); font-size:.88rem; }
    .mq-loading i { margin-right:.5rem; color:var(--primary); }
    .mq-empty { text-align:center; padding:2.5rem 1rem; color:var(--text-muted); }
    .mq-empty i { font-size:2.4rem; opacity:.25; margin-bottom:.5rem; }
    .mq-empty h4 { font-size:.95rem; font-weight:600; margin-bottom:.3rem; color:var(--text); }
    .mq-empty p { font-size:.8rem; }

    /* ── Settings tab ── */
    .mq-section { margin-bottom:1.5rem; }
    .mq-section-title { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px;
      color:var(--text-muted); margin-bottom:.65rem; display:flex; align-items:center; gap:.4rem; }
    .mq-info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
      gap:.75rem; }
    .mq-info-card { padding:.85rem 1rem; background:var(--bg); border:1px solid var(--border);
      border-radius:10px; }
    .mq-info-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
      color:var(--text-muted); margin-bottom:.25rem; }
    .mq-info-value { font-size:.92rem; font-weight:600; color:var(--text); }
    .mq-info-value.muted { color:var(--text-muted); font-weight:500; }

    .mq-action-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
      gap:.75rem; }
    .mq-action-card { padding:1rem; background:var(--surface); border:1.5px solid var(--border);
      border-radius:12px; display:flex; flex-direction:column; gap:.6rem; transition:all .18s; }
    .mq-action-card:hover { border-color:var(--primary); box-shadow:var(--shadow); }
    .mq-action-head { display:flex; align-items:flex-start; gap:.7rem; }
    .mq-action-icon { width:36px; height:36px; border-radius:10px; background:var(--primary-light);
      color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:.95rem; flex-shrink:0; }
    .mq-action-icon.warn { background:#faeeda; color:#b45309; }
    .mq-action-icon.danger { background:#fdecea; color:var(--danger); }
    .mq-action-icon.purple { background:#eeedfe; color:#3c3489; }
    .mq-action-info { flex:1; min-width:0; }
    .mq-action-title { font-size:.88rem; font-weight:700; color:var(--text); }
    .mq-action-desc { font-size:.74rem; color:var(--text-muted); margin-top:.15rem; line-height:1.45; }

    .mq-btn { display:inline-flex; align-items:center; justify-content:center; gap:.45rem;
      padding:.55rem 1rem; border-radius:9px; border:1.5px solid var(--border); background:var(--surface);
      font-family:inherit; font-size:.82rem; font-weight:700; color:var(--text); cursor:pointer;
      transition:all .15s; white-space:nowrap; }
    .mq-btn:hover { border-color:var(--primary); color:var(--primary); }
    .mq-btn:disabled { opacity:.55; cursor:not-allowed; }
    .mq-btn-primary { background:var(--primary); color:#fff; border-color:var(--primary); }
    .mq-btn-primary:hover { background:var(--primary-dark); color:#fff; border-color:var(--primary-dark); }
    .mq-btn-warn { background:var(--warning); color:#fff; border-color:var(--warning); }
    .mq-btn-warn:hover { background:#d96a00; color:#fff; border-color:#d96a00; }
    .mq-btn-danger { background:var(--danger); color:#fff; border-color:var(--danger); }
    .mq-btn-danger:hover { opacity:.9; color:#fff; }
    .mq-btn-purple { background:#5b4fc4; color:#fff; border-color:#5b4fc4; }
    .mq-btn-purple:hover { background:#4940a3; color:#fff; border-color:#4940a3; }
    .mq-btn-ghost { background:transparent; border-color:var(--border); color:var(--text-muted); }
    .mq-btn-ghost:hover { background:var(--bg); color:var(--text); }

    /* ── Submissions tab ── */
    .mq-sub-toolbar { display:flex; align-items:center; gap:.6rem; margin-bottom:1rem; flex-wrap:wrap; }
    .mq-sub-search-wrap { flex:1; min-width:200px; position:relative; }
    .mq-sub-search-wrap i { position:absolute; left:.85rem; top:50%; transform:translateY(-50%);
      color:var(--text-muted); font-size:.85rem; }
    .mq-sub-search { width:100%; padding:.55rem .85rem .55rem 2.2rem; border:1.5px solid var(--border);
      border-radius:20px; font-family:inherit; font-size:.82rem; background:var(--surface); color:var(--text);
      transition:border-color .15s, box-shadow .15s; }
    .mq-sub-search:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-mid); }
    .mq-live-toggle { display:inline-flex; align-items:center; gap:.5rem; padding:.45rem .85rem;
      background:var(--bg); border:1.5px solid var(--border); border-radius:20px; cursor:pointer;
      font-size:.78rem; font-weight:700; color:var(--text-muted); transition:all .15s; user-select:none; }
    .mq-live-toggle:hover { border-color:var(--danger); color:var(--danger); }
    .mq-live-toggle input { display:none; }
    .mq-live-toggle .mq-live-dot { width:9px; height:9px; border-radius:50%; background:var(--text-muted); }
    .mq-live-toggle.active { background:#fdecea; color:var(--danger); border-color:var(--danger); }
    .mq-live-toggle.active .mq-live-dot { background:var(--danger); animation: mq-pulse-dot 1.4s ease-in-out infinite; }
    .mq-refresh-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .85rem;
      border:1.5px solid var(--border); background:var(--surface); border-radius:20px;
      font-family:inherit; font-size:.78rem; font-weight:600; color:var(--text-muted); cursor:pointer;
      transition:all .15s; }
    .mq-refresh-btn:hover { border-color:var(--primary); color:var(--primary); }

    .mq-table-wrap { background:var(--surface); border:1px solid var(--border); border-radius:12px;
      overflow:hidden; }
    .mq-table { width:100%; border-collapse:collapse; }
    .mq-table th { text-align:left; padding:.75rem 1rem; font-size:.7rem; font-weight:700;
      text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted);
      background:var(--bg); border-bottom:1.5px solid var(--border); }
    .mq-table td { padding:.85rem 1rem; font-size:.83rem; border-bottom:1px solid var(--border);
      color:var(--text); vertical-align:middle; }
    .mq-table tr:last-child td { border-bottom:none; }
    .mq-table tr:hover td { background:var(--primary-light); }
    .mq-row-name { font-weight:700; }
    .mq-row-sub { font-size:.7rem; color:var(--text-muted); margin-top:.1rem; }
    .mq-row-actions { text-align:right; }

    .mq-stat-badge { display:inline-flex; align-items:center; gap:.35rem; padding:.2rem .6rem;
      border-radius:20px; font-size:.7rem; font-weight:700; }
    .mq-stat-badge i { font-size:.5rem; }
    .mq-stat-not { background:var(--bg); color:var(--text-muted); border:1px solid var(--border); }
    .mq-stat-prog { background:#faeeda; color:#633806; border:1px solid #fac775; }
    .mq-stat-done { background:#e1f5ee; color:#085041; border:1px solid #5dcaa5; }
    .mq-stat-late { background:#fdecea; color:#a91409; border:1px solid #f5c2c7; }

    /* ── Analytics tab ── */
    .mq-an-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr));
      gap:.75rem; margin-bottom:1.5rem; }
    .mq-an-card { padding:1rem; background:var(--surface); border:1.5px solid var(--border);
      border-radius:12px; }
    .mq-an-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
      color:var(--text-muted); margin-bottom:.4rem; }
    .mq-an-value { font-size:1.5rem; font-weight:700; color:var(--text); line-height:1; }
    .mq-an-sub { font-size:.7rem; color:var(--text-muted); margin-top:.25rem; }

    .mq-hist { display:flex; align-items:flex-end; gap:.4rem; height:140px; padding:.5rem 0;
      border-bottom:1.5px solid var(--border); margin-bottom:.4rem; }
    .mq-hist-bar { flex:1; background:linear-gradient(180deg,var(--primary),var(--primary-dark));
      border-radius:4px 4px 0 0; min-height:2px; position:relative; transition:opacity .15s; }
    .mq-hist-bar:hover { opacity:.85; }
    .mq-hist-bar-count { position:absolute; top:-18px; left:50%; transform:translateX(-50%);
      font-size:.65rem; font-weight:700; color:var(--text); }
    .mq-hist-labels { display:flex; gap:.4rem; }
    .mq-hist-label { flex:1; text-align:center; font-size:.6rem; color:var(--text-muted); font-weight:600; }

    .mq-pq-row { display:flex; align-items:center; gap:.75rem; padding:.65rem .8rem; background:var(--bg);
      border:1px solid var(--border); border-radius:9px; margin-bottom:.45rem; }
    .mq-pq-row.excluded { opacity:.55; background:var(--bg); }
    .mq-pq-num { width:26px; height:26px; border-radius:50%; background:var(--primary); color:#fff;
      font-size:.72rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .mq-pq-text { flex:1; min-width:0; font-size:.82rem; font-weight:600; color:var(--text);
      overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .mq-pq-bar-wrap { width:140px; height:8px; background:var(--border); border-radius:4px; overflow:hidden; flex-shrink:0; }
    .mq-pq-bar-fill { height:100%; background:linear-gradient(90deg,var(--primary),var(--primary-dark));
      border-radius:4px; transition:width .3s ease; }
    .mq-pq-pct { width:48px; text-align:right; font-size:.78rem; font-weight:700; color:var(--text); flex-shrink:0; }
    .mq-pq-toggle { padding:.3rem .65rem; border:1.5px solid var(--border); background:var(--surface);
      border-radius:6px; font-family:inherit; font-size:.68rem; font-weight:700; color:var(--text-muted);
      cursor:pointer; transition:all .15s; flex-shrink:0; }
    .mq-pq-toggle:hover { border-color:var(--danger); color:var(--danger); }
    .mq-pq-toggle.on { background:var(--danger); color:#fff; border-color:var(--danger); }

    /* ── Detail sub-modal ── */
    .mq-detail-meta { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr));
      gap:.6rem; margin-bottom:1.25rem; }
    .mq-detail-q { padding:.85rem 1rem; background:var(--bg); border:1px solid var(--border);
      border-radius:10px; margin-bottom:.65rem; }
    .mq-detail-q.correct { border-left:3px solid var(--primary); }
    .mq-detail-q.wrong   { border-left:3px solid var(--danger); }
    .mq-detail-q.unanswered { border-left:3px solid var(--text-muted); }
    .mq-detail-q-head { display:flex; align-items:center; gap:.6rem; margin-bottom:.5rem; }
    .mq-detail-q-num { width:24px; height:24px; border-radius:50%; background:var(--primary); color:#fff;
      font-size:.68rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .mq-detail-q-text { flex:1; font-size:.86rem; font-weight:600; color:var(--text); }
    .mq-detail-q-pts { font-size:.72rem; font-weight:700; color:var(--text-muted); }
    .mq-detail-choices { display:grid; gap:.3rem; margin-bottom:.5rem; }
        .mq-settings-grid{display:flex;flex-direction:column;gap:1.1rem;padding:.1rem 0;}
    .mq-settings-section{background:#f8f9fa;border-radius:10px;padding:.85rem 1rem;}
    .mq-settings-section--full{width:100%;}
    .mq-settings-label{font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#888;margin-bottom:.55rem;}
    .mq-settings-row{display:flex;justify-content:space-between;align-items:center;font-size:.84rem;padding:.22rem 0;border-bottom:1px solid #eee;}
    .mq-settings-row:last-child{border-bottom:none;}
    .mq-settings-row span{color:#666;}.mq-settings-row strong{color:#222;}
    .mq-ctrl-row{display:flex;flex-wrap:wrap;gap:.55rem;align-items:center;}
    .mq-ctrl-btn{font-size:.83rem;padding:.45rem 1rem;border-radius:8px;font-weight:600;cursor:pointer;border:none;}
    .mq-ctrl-btn:disabled{opacity:.45;cursor:not-allowed;}
    .mq-ctrl-started-info{font-size:.83rem;color:#1a9e78;display:flex;align-items:center;gap:.35rem;}
    .mq-chip{display:inline-block;padding:.2rem .55rem;border-radius:20px;font-size:.72rem;font-weight:700;}
    .mq-chip-wait{background:#f0f0f0;color:#666;}
    .mq-chip-prog{background:#fff7e0;color:#b45309;}
    .mq-chip-done{background:#d1fae5;color:#065f46;}
    .mq-row-btn{background:none;border:1.5px solid #ddd;border-radius:7px;padding:.22rem .42rem;font-size:.78rem;cursor:pointer;color:#555;margin-right:.2rem;transition:.15s;}
    .mq-row-btn:hover:not(:disabled){border-color:#aaa;color:#222;}
    .mq-row-btn:disabled{opacity:.35;cursor:not-allowed;}
    .mq-row-btn-remove{border-color:#f5c2c7;color:#d93025;}
    .mq-row-btn-remove:hover:not(:disabled){background:#d93025;color:#fff;border-color:#d93025;}
    .mq-row-btn-view{border-color:#bce0fd;color:#1a73e8;}
    .mq-row-btn-view:hover:not(:disabled){background:#1a73e8;color:#fff;border-color:#1a73e8;}
    .mq-detail-score{display:flex;justify-content:space-between;font-size:.9rem;padding:.6rem .9rem;background:#d1fae5;border-radius:9px;margin-bottom:.75rem;}
    .mq-detail-score strong{color:#065f46;font-size:1.05rem;}
    .mq-detail-row{display:flex;justify-content:space-between;font-size:.84rem;padding:.28rem 0;border-bottom:1px solid #f0f0f0;color:#555;}
    .mq-detail-row strong{color:#222;}
    .mq-av{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#1a9e78,#0e6655);color:#fff;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .mq-analytics-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:.75rem;padding:.25rem 0;}
    .mq-an-card{background:#f8f9fa;border-radius:10px;padding:.8rem .7rem;text-align:center;}
    .mq-an-val{font-size:1.5rem;font-weight:800;color:#222;line-height:1;}
    .mq-an-lbl{font-size:.72rem;color:#888;margin-top:.3rem;font-weight:600;}
    
        /* ── Steps 12-14 extra ── */
    .mq-settings-grid { display:flex; flex-direction:column; gap:1.1rem; padding:.1rem 0; }
    .mq-settings-section { background:#f8f9fa; border-radius:10px; padding:.85rem 1rem; }
    .mq-settings-section--full { width:100%; }
    .mq-settings-label { font-size:.72rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#888; margin-bottom:.55rem; }
    .mq-settings-row { display:flex; justify-content:space-between; align-items:center; font-size:.84rem; padding:.22rem 0; border-bottom:1px solid #eee; }
    .mq-settings-row:last-child { border-bottom:none; }
    .mq-settings-row span { color:#666; } .mq-settings-row strong { color:#222; }
    .mq-ctrl-row { display:flex; flex-wrap:wrap; gap:.55rem; align-items:center; }
    .mq-ctrl-btn { font-size:.83rem; padding:.45rem 1rem; border-radius:8px; font-weight:600; cursor:pointer; border:none; }
    .mq-ctrl-btn:disabled { opacity:.45; cursor:not-allowed; }
    .mq-ctrl-started-info { font-size:.83rem; color:#1a9e78; display:flex; align-items:center; gap:.35rem; }
    .mq-chip { display:inline-block; padding:.2rem .55rem; border-radius:20px; font-size:.72rem; font-weight:700; }
    .mq-chip-wait { background:#f0f0f0; color:#666; }
    .mq-chip-prog { background:#fff7e0; color:#b45309; }
    .mq-chip-done { background:#d1fae5; color:#065f46; }
    .mq-row-btn { background:none; border:1.5px solid #ddd; border-radius:7px; padding:.22rem .42rem; font-size:.78rem; cursor:pointer; color:#555; margin-right:.2rem; transition:.15s; }
    .mq-row-btn:hover:not(:disabled) { border-color:#aaa; color:#222; }
    .mq-row-btn:disabled { opacity:.35; cursor:not-allowed; }
    .mq-row-btn-remove { border-color:#f5c2c7; color:#d93025; }
    .mq-row-btn-remove:hover:not(:disabled) { background:#d93025; color:#fff; border-color:#d93025; }
    .mq-row-btn-view { border-color:#bce0fd; color:#1a73e8; }
    .mq-row-btn-view:hover:not(:disabled) { background:#1a73e8; color:#fff; border-color:#1a73e8; }
    .mq-detail-score { display:flex; justify-content:space-between; font-size:.9rem; padding:.6rem .9rem; background:#d1fae5; border-radius:9px; margin-bottom:.75rem; }
    .mq-detail-score strong { color:#065f46; font-size:1.05rem; }
    .mq-detail-row { display:flex; justify-content:space-between; font-size:.84rem; padding:.28rem 0; border-bottom:1px solid #f0f0f0; color:#555; }
    .mq-detail-row strong { color:#222; }
    .mq-av { width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg,#1a9e78,#0e6655); color:#fff; font-size:.7rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .mq-analytics-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(110px,1fr)); gap:.75rem; padding:.25rem 0; }
    .mq-an-card { background:#f8f9fa; border-radius:10px; padding:.8rem .7rem; text-align:center; }
    .mq-an-val { font-size:1.5rem; font-weight:800; color:#222; line-height:1; }
    .mq-an-lbl { font-size:.72rem; color:#888; margin-top:.3rem; font-weight:600; }
    .mq-loading { display:flex; align-items:center; justify-content:center; gap:.5rem; padding:2.5rem; color:#888; font-size:.88rem; }
    .mq-detail-choice { padding:.45rem .7rem; border-radius:7px; background:var(--surface);
      border:1px solid var(--border); font-size:.78rem; display:flex; align-items:center; gap:.5rem; }
    .mq-detail-choice.is-correct  { background:#e1f5ee; border-color:#5dcaa5; color:#085041; font-weight:600; }
    .mq-detail-choice.is-selected { box-shadow:0 0 0 2px var(--accent) inset; }
    .mq-detail-choice.is-selected.is-correct { box-shadow:0 0 0 2px var(--primary) inset; }
    .mq-detail-choice .mq-choice-mark { font-size:.72rem; margin-left:auto; font-weight:700; }
    .mq-detail-q-controls { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;
      margin-top:.5rem; padding-top:.5rem; border-top:1px dashed var(--border); }
    .mq-override-toggle { display:inline-flex; align-items:center; gap:.4rem; padding:.3rem .65rem;
      border:1.5px solid var(--border); background:var(--surface); border-radius:6px;
      font-family:inherit; font-size:.7rem; font-weight:700; color:var(--text-muted); cursor:pointer;
      transition:all .15s; }
    .mq-override-toggle:hover { border-color:var(--primary); color:var(--primary); }
    .mq-override-toggle.on-correct { background:var(--primary); color:#fff; border-color:var(--primary); }
    .mq-override-toggle.on-wrong   { background:var(--danger);  color:#fff; border-color:var(--danger); }

    .mq-override-row { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-top:1rem;
      padding:1rem; background:var(--bg); border:1px solid var(--border); border-radius:10px; }
    .mq-override-field label { display:block; font-size:.7rem; font-weight:700; text-transform:uppercase;
      letter-spacing:.5px; color:var(--text-muted); margin-bottom:.35rem; }
    .mq-override-field input, .mq-override-field textarea { width:100%; padding:.55rem .75rem;
      border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:.85rem;
      background:var(--surface); color:var(--text); }
    .mq-override-field input:focus, .mq-override-field textarea:focus { outline:none;
      border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-mid); }
    .mq-override-field textarea { min-height:70px; resize:vertical; }
    .mq-override-field-full { grid-column:1 / -1; }

    .mq-detail-footer { padding:.85rem 1.25rem; border-top:1.5px solid var(--border);
      display:flex; gap:.5rem; justify-content:flex-end; background:var(--bg); flex-shrink:0; }

    @media (max-width:640px) {
      .mq-modal { width:96vw; max-height:94vh; border-radius:14px; }
      .mq-body { padding:1rem; }
      .mq-override-row { grid-template-columns:1fr; }
      .mq-pq-row { flex-wrap:wrap; }
      .mq-pq-bar-wrap { width:100%; order:5; }
    }
    @media(max-width:600px){.wl-bar{padding:.55rem 1rem;}.wl-drawer{width:100vw;}}

    .topbar{position:sticky;top:0;height:var(--nav-h);background:var(--class-gradient,hsl(145 61% 26%));color:#fff;border-bottom:0;display:flex;align-items:center;gap:.75rem;padding:.625rem 1rem;z-index:200;box-shadow:0 1px 3px rgba(0,0,0,.12);transition:background var(--trans),border-color var(--trans);}
    .back-btn{width:28px;height:28px;border:none;background:transparent;cursor:pointer;color:#fff;font-size:.9rem;display:flex;align-items:center;justify-content:center;border-radius:8px;transition:all var(--trans);text-decoration:none;flex-shrink:0;}
    .back-btn:hover{background:rgba(255,255,255,.15);color:#fff;}
    .topbar-brand,.topbar-sep{display:none;}
    .topbar-class-name{font-size:.875rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;min-width:0;}
    .topbar-right{margin-left:auto;display:flex;align-items:center;gap:.35rem;}
    .icon-btn{width:28px;height:28px;border:none;background:transparent;cursor:pointer;color:#fff;font-size:.88rem;display:flex;align-items:center;justify-content:center;border-radius:8px;transition:all var(--trans);}
    .icon-btn:hover{background:rgba(255,255,255,.15);color:#fff;}
    .icon-btn:active{transform:scale(.94);background:rgba(255,255,255,.22);}
    .class-banner{height:auto;min-height:126px;position:relative;overflow:hidden;display:block;background:var(--class-gradient,linear-gradient(135deg,hsl(145 61% 16%) 0%,hsl(145 55% 26%) 60%,hsl(160 50% 32%) 100%));}
    .banner-bg{position:absolute;inset:0;background:var(--class-gradient,linear-gradient(135deg,hsl(145 61% 16%) 0%,hsl(145 55% 26%) 60%,hsl(160 50% 32%) 100%))!important;}
    .banner-overlay{display:none;}
    .banner-pattern{position:absolute;inset:0;z-index:0;overflow:hidden;pointer-events:none;opacity:.18;mix-blend-mode:multiply;}
    .banner-pattern-icon{position:absolute;display:flex;align-items:center;justify-content:center;color:rgba(0,0,0,.42);background:rgba(255,255,255,.10);border-radius:18px;filter:blur(.1px);transform:rotate(var(--r,0deg));}
    .banner-pattern-icon i{font-size:inherit;line-height:1;}
    .banner-pattern-icon.is-ghost{background:transparent;color:rgba(255,255,255,.26);}
    .banner-content{position:relative;z-index:1;padding:1.75rem 1.5rem 1.25rem;color:#fff;width:100%;max-width:var(--class-max);margin:0 auto;}
    .banner-title{font-size:1.25rem;font-weight:700;line-height:1.25;}
    .banner-sub{font-size:.75rem;opacity:.7;margin-top:.15rem;}
    .banner-chips{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:.75rem;color:rgba(255,255,255,.8);}
    .banner-chip{font-size:.75rem;font-weight:500;padding:.25rem .625rem;border-radius:999px;background:rgba(255,255,255,.10);backdrop-filter:none;color:rgba(255,255,255,.82);display:flex;align-items:center;gap:.35rem;}
    .banner-edit-btn{display:none;}
    .tabs-bar{background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:0;padding-left:max(1.5rem,calc((100vw - var(--class-max))/2 + 1.5rem));padding-right:max(1.5rem,calc((100vw - var(--class-max))/2 + 1.5rem));position:sticky;top:var(--nav-h);z-index:100;transition:background var(--trans),border-color var(--trans);}
    .tab-btn{padding:.75rem 1rem;border:none;background:none;font-size:.875rem;font-weight:500;font-family:inherit;color:var(--text-muted);cursor:pointer;border-bottom:2px solid transparent;transition:all var(--trans);white-space:nowrap;display:flex;align-items:center;gap:.375rem;}
    .tab-btn:hover{color:var(--text);}
    .tab-btn.active{color:var(--primary);border-bottom-color:var(--primary);}
    .tab-count{display:inline-flex;align-items:center;justify-content:center;background:var(--primary);color:#fff;font-size:.625rem;font-weight:600;min-width:18px;height:18px;padding:0 .375rem;border-radius:999px;margin-left:.1rem;}
    .cr-layout{max-width:var(--class-max);margin:0 auto;padding:1.25rem 0;display:grid;grid-template-columns:minmax(0,1fr) 16rem;gap:0;}
    .cr-main{min-width:0;padding:0 1.25rem;}
    .cr-side{border-left:1px solid var(--border);padding:0 1rem;}
    @media(max-width:860px){.cr-layout{grid-template-columns:1fr;padding:1rem;}.cr-main{padding:0;}.cr-side{order:2;border-left:0;padding:1rem 0 0;}}
    @media(max-width:600px){.class-banner{min-height:118px;}.banner-content{padding:1.35rem 1.25rem 1rem;}.banner-title{font-size:1.15rem;}.tabs-bar{overflow-x:auto;padding-left:1rem;padding-right:1rem;}.tab-btn{padding:.75rem .85rem;}}
    /* Gradebook should use full horizontal space; side cards move below to avoid table compression */
    #tab-grades .cr-layout{
      max-width:var(--class-max);
      grid-template-columns:1fr;
      gap:0;
    }
    #tab-grades .cr-main{
      min-width:0;
      width:100%;
    }
    #tab-grades .cr-side{
      width:100%;
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
      gap:.85rem;
      order:2;
      border-left:0;
      padding:0 1.25rem 1.25rem;
    }
    #tab-grades .cr-side .side-card{
      margin-bottom:0;
    }
    #tab-report .cr-layout{
      max-width:var(--class-max);
      grid-template-columns:1fr;
      gap:0;
    }
    #tab-report .cr-main{min-width:0;}
    #tab-report .cr-side{
      display:grid;
      gap:.9rem;
      align-content:start;
      order:2;
      border-left:0;
      padding:0 1.25rem 1.25rem;
    }
    #tab-report .cr-side .side-card{margin-bottom:0;}
    .side-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:1rem;box-shadow:none;margin-bottom:1rem;}
    .side-card-title{font-size:.625rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:.75rem;display:flex;align-items:center;justify-content:space-between;}
    .code-display{font-family:'DM Mono',monospace;font-size:1.5rem;font-weight:800;color:var(--primary);letter-spacing:.12em;text-align:center;padding:.5rem 0;cursor:pointer;transition:opacity var(--trans);}
    .code-display:hover{opacity:.75;}
    .join-code-row{display:flex;align-items:center;justify-content:center;gap:.55rem;padding:.2rem 0 .55rem;}
    .join-code-row .code-display{padding:.35rem 0;}
    .join-code-refresh{width:34px;height:34px;border-radius:10px;border:1.5px solid var(--border);background:var(--surface);color:var(--primary);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all var(--trans);box-shadow:var(--shadow);}
    .join-code-refresh:hover{background:var(--primary-light);border-color:var(--primary);transform:translateY(-1px);}
    .join-code-refresh.is-spinning i{animation:sp .7s linear infinite;}
    .join-code-refresh-title{width:26px;height:26px;border-radius:8px;font-size:.72rem;box-shadow:none;}
    .code-actions{display:flex;gap:.5rem;margin-top:.6rem;}
    .code-btn{flex:1;padding:.45rem;border-radius:var(--radius-sm);border:1.5px solid var(--border);background:none;font-size:.78rem;font-weight:600;font-family:inherit;color:var(--text-muted);cursor:pointer;transition:all var(--trans);display:flex;align-items:center;justify-content:center;gap:.35rem;}
    .code-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .code-btn.active{background:var(--primary);border-color:var(--primary);color:#fff;}
    .link-row{display:flex;align-items:center;gap:.5rem;padding:.5rem .7rem;border-radius:var(--radius-sm);background:var(--bg);border:1px solid var(--border);font-size:.78rem;color:var(--text-muted);margin-top:.5rem;overflow:hidden;}
    .link-row .link-text{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .link-copy-btn{width:26px;height:26px;border:none;background:none;cursor:pointer;color:var(--primary);font-size:.8rem;display:flex;align-items:center;justify-content:center;border-radius:6px;flex-shrink:0;transition:all var(--trans);}
    .link-copy-btn:hover{background:var(--primary-light);}
    .compose-box{background:var(--surface);border:1px solid var(--border);border-radius:12px;box-shadow:none;margin-bottom:1rem;overflow:hidden;}
    .compose-trigger{display:flex;align-items:center;gap:.75rem;padding:1rem;cursor:pointer;}
    .compose-avatar{width:36px;height:36px;border-radius:50%;background:#0f766e;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:.78rem;flex-shrink:0;}
    .compose-placeholder{flex:1;padding:.625rem .75rem;border-radius:8px;border:0;background:var(--bg);font-size:.875rem;color:var(--text-muted);cursor:pointer;transition:all var(--trans);font-family:inherit;text-align:left;}
    .compose-placeholder:hover{background:rgba(95,99,104,.10);color:var(--text);}
    .compose-section-title{font-size:.69rem;font-weight:800;text-transform:uppercase;letter-spacing:.55px;color:var(--text-muted);padding:0 1.1rem .38rem;}
    .type-picker-row{display:flex;flex-wrap:wrap;gap:.375rem;padding:.75rem 1rem;border-top:1px solid var(--border);}
    .stream-filter-row{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;padding:0 0 1rem;margin-bottom:0;background:transparent;}
    .stream-filter-label{font-size:.75rem;font-weight:700;color:var(--text-muted);display:inline-flex;align-items:center;gap:.35rem;margin-right:.12rem;}
    .stream-filter-ctl{height:36px;border:1px solid var(--border);background:var(--surface);border-radius:8px;color:var(--text);font-size:.8rem;font-weight:500;padding:0 .75rem;font-family:inherit;}
    .stream-filter-ctl:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);}
    .stream-filter-search{min-width:220px;flex:1;}
    .lesson-period-filter{display:none;align-items:center;gap:.42rem;flex-wrap:wrap;padding:0 1.1rem .9rem;background:var(--bg);}
    .lesson-period-filter.show{display:flex;}
    .lesson-period-filter-label{font-size:.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.45px;margin-right:.16rem;}
    .lesson-period-filter-option{display:inline-flex;align-items:center;gap:.32rem;padding:.34rem .68rem;border:1.5px solid var(--border);border-radius:999px;background:var(--surface);color:var(--text-muted);font-size:.72rem;font-weight:700;cursor:pointer;transition:all var(--trans);}
    .lesson-period-filter-option:has(input:checked){border-color:var(--primary);background:var(--primary-light);color:var(--primary-dark);}
    .lesson-period-filter-option input{accent-color:var(--primary);margin:0;}
    .type-pill{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;border-radius:8px;border:1px solid var(--border);background:var(--bg);font-size:.75rem;font-weight:600;font-family:inherit;cursor:pointer;transition:all var(--trans);color:var(--text-muted);}
    .type-pill:hover,.type-pill.active{color:#fff;border-color:transparent;}
    .type-pill i{font-size:.72rem;}
    .type-pill-add{border-style:dashed;color:var(--primary);background:var(--primary-light);border-color:var(--primary);}
    .type-pill-add:hover{background:var(--primary);color:#fff;}

    /* ══════════════════════════════════════════
       GOOGLE CLASSROOM STYLE POST MODAL
    ══════════════════════════════════════════ */
    @keyframes slideInRight{from{transform:translateX(100%);opacity:0;}to{transform:translateX(0);opacity:1;}}
    @keyframes slideOutRight{from{transform:translateX(0);opacity:1;}to{transform:translateX(100%);opacity:0;}}

    .gc-modal-overlay{
      display:none;position:fixed;inset:0;
      background:rgba(0,0,0,.45);z-index:400;
    }
    .gc-modal-overlay.show{display:block;}

    .gc-modal-panel{
      position:fixed;inset:0;z-index:401;
      background:var(--surface);
      display:flex;flex-direction:column;
      animation:slideInRight .28s cubic-bezier(.4,0,.2,1);
      overflow:hidden;
    }
    .gc-modal-panel.closing{animation:slideOutRight .22s cubic-bezier(.4,0,.2,1) forwards;}

    /* Top bar */
    .gc-topbar{
      display:flex;align-items:center;gap:.75rem;
      padding:.7rem 1.25rem;
      border-bottom:2px solid var(--border);
      background:var(--surface);flex-shrink:0;
      min-height:56px;
    }
    .gc-close-btn{
      width:36px;height:36px;border:none;background:none;cursor:pointer;
      color:var(--text-muted);font-size:1.05rem;border-radius:10px;
      display:flex;align-items:center;justify-content:center;
      transition:all var(--trans);flex-shrink:0;
    }
    .gc-close-btn:hover{background:#fdecea;color:var(--danger);}
    .gc-type-icon-lg{
      width:36px;height:36px;border-radius:10px;
      display:flex;align-items:center;justify-content:center;
      font-size:1rem;flex-shrink:0;
    }
    .gc-modal-heading{font-size:1.1rem;font-weight:700;flex:1;}
    .gc-topbar-actions{display:flex;gap:.5rem;margin-left:auto;}

    /* Body layout: left form + right settings panel */
    .gc-body{display:flex;flex:1;min-height:0;overflow:hidden;}

    /* Left: type nav + form */
    .gc-left{display:flex;flex:1;min-width:0;overflow:hidden;}

    /* Type nav sidebar */
    .gc-type-nav{
      width:200px;flex-shrink:0;
      border-right:1px solid var(--border);
      padding:.6rem .5rem;
      background:var(--bg);
      overflow-y:auto;
      display:flex;flex-direction:column;gap:.15rem;
    }
    .gc-type-nav-label{
      font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;
      color:var(--text-muted);padding:.5rem .75rem .25rem;
    }
    .gc-type-nav-item{
      display:flex;align-items:center;gap:.7rem;
      padding:.6rem .85rem;border-radius:10px;
      border:none;background:none;font-family:inherit;
      font-size:.85rem;font-weight:500;color:var(--text-muted);
      cursor:pointer;transition:all var(--trans);text-align:left;width:100%;
    }
    .gc-type-nav-item:hover{background:var(--border);color:var(--text);}
    .gc-type-nav-item.active{font-weight:700;}
    .gc-type-nav-item .gc-nav-icon{
      width:32px;height:32px;border-radius:9px;
      display:flex;align-items:center;justify-content:center;
      font-size:.82rem;flex-shrink:0;
    }
    .gc-type-nav-divider{height:1px;background:var(--border);margin:.4rem .5rem;}
    .gc-type-nav-add{
      display:flex;align-items:center;gap:.7rem;
      padding:.6rem .85rem;border-radius:10px;
      border:1.5px dashed var(--primary);background:var(--primary-light);
      font-family:inherit;font-size:.82rem;font-weight:600;
      color:var(--primary);cursor:pointer;transition:all var(--trans);
      text-align:left;width:100%;margin-top:.25rem;
    }
    .gc-type-nav-add:hover{background:var(--primary);color:#fff;}

    /* Form area */
    .gc-form-area{
      flex:1 1 auto;overflow-y:auto;
      padding:1.5rem 1.75rem 1.75rem;
      min-width:0;
      max-width:none;
    }
    .gc-form-area::-webkit-scrollbar{width:5px;}
    .gc-form-area::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}

    .gc-form-section-title{
      font-size:.68rem;font-weight:700;text-transform:uppercase;
      letter-spacing:1px;color:var(--primary);
      margin-bottom:1rem;padding-bottom:.5rem;
      border-bottom:2px solid var(--primary-light);
      display:flex;align-items:center;gap:.4rem;
    }
    .gc-field{margin-bottom:1.25rem;}
    .gc-label{
      display:block;font-size:.72rem;font-weight:700;
      text-transform:uppercase;letter-spacing:.5px;
      color:var(--text-muted);margin-bottom:.35rem;
    }
    .gc-input,.gc-textarea,.gc-select{
      width:100%;padding:.6rem 1rem;
      border:1.5px solid var(--border);border-radius:var(--radius-sm);
      font-size:.9rem;font-family:inherit;
      background:var(--surface);color:var(--text);
      transition:border-color var(--trans),box-shadow var(--trans);
    }
    .gc-input:focus,.gc-textarea:focus,.gc-select:focus{
      border-color:var(--primary);
      box-shadow:0 0 0 3px var(--primary-mid);outline:none;
    }
    .gc-textarea{resize:vertical;min-height:100px;line-height:1.6;}
    .gc-input-lg{font-size:1.1rem;font-weight:600;padding:.75rem 1rem;}
    .gc-lesson-heading-row{display:grid;grid-template-columns:1fr;gap:.85rem;}
    .gc-lesson-heading-row.lesson-mode{grid-template-columns:minmax(0,1fr) minmax(0,1fr);}
    .gc-lesson-heading-row .gc-field{margin-bottom:1.25rem;}
    .gc-week-picker-wrap{display:flex;align-items:stretch;gap:.5rem;}
    .gc-week-picker-wrap .gc-input{min-width:0;flex:1;}
    .gc-week-picker-wrap.lesson-week-active .gc-input{cursor:pointer;background:var(--bg);}
    .gc-week-pick-btn{display:none;align-items:center;justify-content:center;gap:.35rem;min-width:112px;padding:.6rem .9rem;border:1.5px solid var(--primary);border-radius:var(--radius-sm);background:var(--primary-light);color:var(--primary);font-size:.76rem;font-weight:800;text-transform:uppercase;letter-spacing:.35px;font-family:inherit;cursor:pointer;transition:all .16s;white-space:nowrap;}
    .gc-week-pick-btn:hover{background:var(--primary);color:#fff;box-shadow:0 4px 14px rgba(26,158,120,.22);}
    .gc-week-hint{margin-top:.35rem;font-size:.72rem;color:var(--text-muted);line-height:1.45;}
    .gc-period-options{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.45rem;}
    .gc-period-option{display:flex;align-items:center;justify-content:center;gap:.32rem;min-height:48px;padding:.5rem .45rem;border:1.5px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text-muted);font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.35px;cursor:pointer;transition:all .16s;}
    .gc-period-option:has(input:checked){border-color:var(--primary);background:var(--primary-light);color:var(--primary-dark);}
    .gc-period-option input{accent-color:var(--primary);margin:0;}
    @media(max-width:720px){.gc-lesson-heading-row.lesson-mode{grid-template-columns:1fr;}.gc-week-picker-wrap{flex-direction:column;}.gc-week-pick-btn{width:100%;}}
    .gc-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}

    /* Right settings panel */
    .gc-settings-panel{
      width:220px;flex-shrink:0;
      border-left:1px solid var(--border);
      background:var(--bg);
      overflow-y:auto;
      padding:1.25rem 1rem;
      display:flex;flex-direction:column;gap:1rem;
    }
    .gc-settings-panel::-webkit-scrollbar{width:4px;}
    .gc-settings-panel::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
    .gc-setting-card{
      background:var(--surface);border:1px solid var(--border);
      border-radius:12px;padding:.9rem 1rem;
    }
    .gc-setting-label{
      font-size:.68rem;font-weight:700;text-transform:uppercase;
      letter-spacing:.8px;color:var(--text-muted);margin-bottom:.6rem;
      display:flex;align-items:center;gap:.35rem;
    }
    .gc-setting-label i{font-size:.62rem;}
    .gc-setting-val{
      font-size:.88rem;font-weight:600;color:var(--text);
      padding:.42rem .75rem;
      background:var(--bg);border:1.5px solid var(--border);
      border-radius:var(--radius-sm);
      display:flex;align-items:center;gap:.4rem;
    }
    .gc-setting-val i{color:var(--primary);font-size:.78rem;}

    /* Attach bar */
    .gc-attach-bar{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.75rem;}
    .gc-attach-btn{
      display:flex;align-items:center;gap:.4rem;
      padding:.4rem .9rem;border-radius:20px;
      border:1.5px solid var(--border);background:none;
      font-size:.78rem;font-weight:600;font-family:inherit;
      color:var(--text-muted);cursor:pointer;transition:all var(--trans);
    }
    .gc-attach-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .gc-attach-previews{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.75rem;}
    .attach-chip{display:flex;align-items:center;gap:.4rem;padding:.3rem .7rem .3rem .55rem;border-radius:20px;background:var(--bg);border:1px solid var(--border);font-size:.75rem;max-width:260px;}
    .attach-chip i{font-size:.72rem;color:var(--primary);flex-shrink:0;}
    .attach-chip .chip-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;}
    .attach-chip .chip-rm{width:18px;height:18px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:.68rem;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:all var(--trans);flex-shrink:0;}
    .attach-chip .chip-rm:hover{background:#fdecea;color:var(--danger);}
    #fileInput{display:none;}

    /* ── SUBMISSION MODE TOGGLE ──────────────────────────────────── */
.sub-mode-row{display:flex;gap:.5rem;margin-bottom:1.25rem;}
.sub-mode-btn{flex:1;padding:.55rem .5rem;border-radius:10px;border:2px solid var(--border);
  background:var(--bg);font-size:.82rem;font-weight:600;font-family:inherit;
  color:var(--text-muted);cursor:pointer;transition:all var(--trans);
  display:flex;align-items:center;justify-content:center;gap:.4rem;}
.sub-mode-btn:hover{border-color:var(--primary);color:var(--primary);}
.sub-mode-btn.active{border-color:var(--primary);background:var(--primary-light);color:var(--primary);}
 
/* ── GROUP BUILDER ─────────────────────────────────────────── */
.gb-wrap{margin-top:.75rem;background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:1rem;}
.gb-controls{display:flex;align-items:center;gap:.6rem;margin-bottom:.85rem;flex-wrap:wrap;}
.gb-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);}
.gb-num-input{width:70px;padding:.38rem .6rem;border:1.5px solid var(--border);border-radius:8px;
  font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);text-align:center;}
.gb-num-input:focus{border-color:var(--primary);outline:none;box-shadow:0 0 0 3px var(--primary-mid);}
.gb-btn{display:inline-flex;align-items:center;gap:.35rem;padding:.38rem .85rem;border-radius:8px;
  border:1.5px solid var(--border);background:none;font-size:.76rem;font-weight:600;
  font-family:inherit;cursor:pointer;color:var(--text-muted);transition:all var(--trans);}
.gb-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
.gb-btn.primary{background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 2px 8px rgba(26,158,120,.28);}
.gb-btn.primary:hover{opacity:.88;}
.gb-btn.danger{border-color:#fca5a5;color:var(--danger);}
.gb-btn.danger:hover{background:#fdecea;}
.gb-load-hint{font-size:.72rem;color:var(--text-muted);font-style:italic;}
 
.gb-columns{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.65rem;margin-top:.25rem;}
.gb-group-card{background:var(--surface);border:2px solid var(--border);border-radius:10px;
  padding:.6rem .75rem;min-height:80px;transition:border-color var(--trans);}
.gb-group-card.drag-over{border-color:var(--primary);background:var(--primary-light);}
.gb-group-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.45rem;}
.gb-group-title{font-size:.75rem;font-weight:700;color:var(--primary);display:flex;align-items:center;gap:.3rem;}
.gb-group-count{font-size:.62rem;color:var(--text-muted);background:var(--bg);
  border:1px solid var(--border);border-radius:20px;padding:.06rem .38rem;}
.gb-member-list{display:flex;flex-direction:column;gap:.3rem;min-height:28px;}
.gb-member{display:flex;align-items:center;gap:.35rem;padding:.28rem .5rem;border-radius:7px;
  background:var(--bg);border:1px solid var(--border);font-size:.76rem;cursor:grab;
  transition:all var(--trans);user-select:none;}
.gb-member:hover{border-color:var(--primary);background:var(--primary-light);color:var(--primary);}
.gb-member.dragging{opacity:.4;cursor:grabbing;}
.gb-member-name{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.gb-member-drag-icon{color:var(--text-muted);font-size:.65rem;flex-shrink:0;}
.gb-member-move{width:18px;height:18px;border:none;background:none;cursor:pointer;
  color:var(--text-muted);font-size:.62rem;border-radius:4px;display:flex;align-items:center;
  justify-content:center;flex-shrink:0;transition:all var(--trans);}
.gb-member-move:hover{background:#fdecea;color:var(--danger);}
 
/* Unassigned pool */
.gb-unassigned{background:var(--surface);border:2px dashed var(--border);border-radius:10px;
  padding:.6rem .75rem;margin-bottom:.65rem;}
.gb-unassigned.drag-over{border-color:var(--warning);background:#fff3e0;}
.gb-unassigned-title{font-size:.72rem;font-weight:700;color:var(--warning);margin-bottom:.4rem;
  display:flex;align-items:center;gap:.3rem;}
 
/* ── POST CARD GROUP CHIPS ──────────────────────────────────── */
.pc-groups{padding:.1rem 1.1rem .85rem;display:flex;flex-wrap:wrap;gap:.4rem;}
.group-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.28rem .7rem;
  border-radius:20px;background:var(--primary-light);border:1.5px solid var(--primary);
  font-size:.72rem;font-weight:600;color:var(--primary);cursor:pointer;transition:all var(--trans);}
.group-chip:hover{background:var(--primary);color:#fff;}
.group-chip i{font-size:.62rem;}
.group-chip-solo{background:#fff3e0;border-color:#fcd34d;color:#92400e;}
.group-chip-solo:hover{background:#fcd34d;color:#78350f;}
.sub-mode-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.65rem;font-weight:700;
  text-transform:uppercase;letter-spacing:.4px;padding:.18rem .55rem;border-radius:20px;
  margin-right:.35rem;}
.sub-mode-individual{background:#e0f7fa;color:#00695c;border:1px solid #b2dfdb;}
.sub-mode-group{background:var(--primary-light);color:var(--primary);border:1px solid var(--primary);}
 
/* ── GROUP DETAIL POPOVER ───────────────────────────────────── */
.grp-popover{position:fixed;background:var(--surface);border:1px solid var(--border);
  border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.16);z-index:600;
  padding:.75rem 1rem;min-width:220px;max-width:300px;animation:popIn .15s ease;}
.grp-popover-title{font-size:.75rem;font-weight:700;color:var(--primary);
  margin-bottom:.5rem;display:flex;align-items:center;gap:.3rem;}
.grp-popover-list{display:flex;flex-direction:column;gap:.28rem;}
.grp-popover-member{font-size:.8rem;display:flex;align-items:center;gap:.35rem;
  padding:.22rem .4rem;border-radius:6px;}
.grp-popover-member:nth-child(odd){background:var(--bg);}
.grp-popover-close{position:absolute;top:.45rem;right:.5rem;width:22px;height:22px;
  border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:.72rem;
  border-radius:6px;display:flex;align-items:center;justify-content:center;}
.grp-popover-close:hover{background:#fdecea;color:var(--danger);}
 
/* ── CLASS GROUPS PANEL ─────────────────────────────────────── */
.cg-panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
  box-shadow:var(--shadow);padding:1.1rem 1.3rem;}
.cg-panel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;}
.cg-panel-title{font-size:.95rem;font-weight:700;display:flex;align-items:center;gap:.5rem;}
.cg-empty{text-align:center;padding:2.5rem 1rem;color:var(--text-muted);}
.cg-empty-icon{font-size:2.2rem;opacity:.25;margin-bottom:.6rem;}
 

    /* ── AI QUIZ GENERATOR MODAL ── */
    @keyframes aqModalIn{from{opacity:0;transform:scale(.96) translateY(10px);}to{opacity:1;transform:scale(1) translateY(0);}}
    .aq-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1200;align-items:center;justify-content:center;padding:1rem;}
    .aq-backdrop.show{display:flex;}
    .aq-modal{background:var(--surface);border-radius:18px;width:100%;max-width:980px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 30px 80px rgba(0,0,0,.25);animation:aqModalIn .22s cubic-bezier(.4,0,.2,1);overflow:hidden;}
    .aq-header{display:flex;align-items:center;gap:.75rem;padding:1rem 1.35rem;border-bottom:1.5px solid var(--border);flex-shrink:0;background:linear-gradient(135deg,#f0fdf8,#e8f4fd);}
    body.dark .aq-header{background:linear-gradient(135deg,rgba(46,204,154,.07),rgba(77,144,226,.07));}
    .aq-header-icon{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;flex-shrink:0;box-shadow:0 3px 12px rgba(26,158,120,.3);}
    .aq-header-text h3{font-size:1rem;font-weight:800;margin:0;}
    .aq-header-text p{font-size:.74rem;color:var(--text-muted);margin:.1rem 0 0;}
    .aq-close{margin-left:auto;width:32px;height:32px;border:none;background:var(--bg);color:var(--text-muted);border-radius:9px;cursor:pointer;font-size:.9rem;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;}
    .aq-close:hover{background:#fdecea;color:var(--danger);}
    .aq-body{flex:1;overflow-y:auto;padding:1.25rem 1.35rem;display:flex;flex-direction:column;gap:1.1rem;}
    .aq-source-tabs{display:flex;gap:.65rem;flex-wrap:wrap;}
    .aq-source-tab{display:inline-flex;align-items:center;gap:.45rem;padding:.58rem .95rem;border-radius:999px;border:1.5px solid var(--border);background:var(--surface);color:var(--text-muted);font-size:.82rem;font-weight:700;font-family:inherit;cursor:pointer;transition:all .15s;}
    .aq-source-tab:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .aq-source-tab.active{border-color:var(--primary);background:var(--primary-light);color:var(--primary);}
    .aq-layout{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(280px,.9fr);gap:1.15rem;align-items:start;}
    .aq-col{min-width:0;}
    .aq-side-card{border:1.5px solid var(--border);border-radius:14px;background:var(--surface);padding:1rem;display:flex;flex-direction:column;gap:1rem;}
    .aq-body::-webkit-scrollbar{width:4px;}.aq-body::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
    .aq-section-label{font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.9px;color:var(--primary);display:flex;align-items:center;gap:.4rem;margin-bottom:.55rem;}
    .aq-section-label::after{content:'';flex:1;height:1.5px;background:var(--primary-light);}
    /* File search */
    .aq-search-wrap{position:relative;}
    .aq-search-input{width:100%;padding:.6rem 1rem .6rem 2.4rem;border:1.5px solid var(--border);border-radius:10px;font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);transition:border-color .15s,box-shadow .15s;}
    .aq-search-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);outline:none;}
    .aq-search-icon{position:absolute;left:.78rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.82rem;pointer-events:none;}
    .aq-search-clear{position:absolute;right:.65rem;top:50%;transform:translateY(-50%);width:22px;height:22px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:.72rem;border-radius:6px;display:flex;align-items:center;justify-content:center;}
    .aq-search-clear:hover{background:var(--border);}
    .aq-file-list{max-height:220px;overflow-y:auto;border:1.5px solid var(--border);border-radius:10px;margin-top:.55rem;}
    .aq-file-list::-webkit-scrollbar{width:3px;}.aq-file-list::-webkit-scrollbar-thumb{background:var(--border);}
    .aq-file-item{display:flex;align-items:center;gap:.7rem;padding:.6rem .9rem;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s;user-select:none;}
    .aq-file-item:last-child{border-bottom:none;}
    .aq-file-item:hover{background:var(--primary-light);}
    .aq-file-item.selected{background:var(--primary-light);border-left:3px solid var(--primary);}
    .aq-file-cb{width:17px;height:17px;accent-color:var(--primary);flex-shrink:0;cursor:pointer;}
    .aq-file-icon{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;}
    .aq-file-icon.pdf{background:#fdecea;color:#c62828;}
    .aq-file-icon.pptx{background:#fff3e0;color:#e65100;}
    .aq-file-icon.docx{background:#e3f2fd;color:#1565c0;}
    .aq-file-icon.txt{background:#f3f4f6;color:#374151;}
    .aq-file-meta{flex:1;min-width:0;}
    .aq-file-name{font-size:.82rem;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .aq-file-sub{font-size:.68rem;color:var(--text-muted);margin-top:.05rem;}
    .aq-file-ext{font-size:.6rem;font-weight:700;text-transform:uppercase;padding:.1rem .38rem;border-radius:5px;background:var(--bg);color:var(--text-muted);border:1px solid var(--border);flex-shrink:0;}
    .aq-empty-search{text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.8rem;}
    /* Selected chips */
    .aq-selected-bar{display:flex;flex-wrap:wrap;gap:.4rem;padding:.55rem .75rem;background:var(--primary-light);border:1.5px solid var(--primary);border-radius:10px;min-height:44px;align-items:center;}
    .aq-sel-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.28rem .65rem .28rem .5rem;border-radius:20px;background:var(--primary);color:#fff;font-size:.75rem;font-weight:600;}
    .aq-sel-chip button{width:16px;height:16px;border:none;background:rgba(255,255,255,.25);color:#fff;border-radius:50%;cursor:pointer;font-size:.6rem;display:flex;align-items:center;justify-content:center;padding:0;transition:background .12s;}
    .aq-sel-chip button:hover{background:rgba(255,255,255,.5);}
    .aq-sel-empty{font-size:.78rem;color:var(--primary);font-style:italic;opacity:.7;}
    .aq-info-card{border:1.5px solid var(--border);border-radius:12px;padding:.85rem .95rem;background:var(--bg);}
    .aq-info-card strong{display:block;font-size:1.2rem;color:var(--primary);line-height:1.1;}
    .aq-info-card span{display:block;font-size:.73rem;color:var(--text-muted);margin-top:.15rem;}
    .aq-checkline{display:flex;align-items:center;gap:.55rem;font-size:.82rem;color:var(--text);}
    .aq-checkline input{width:17px;height:17px;accent-color:var(--primary);}
    .aq-helper-text{font-size:.72rem;color:var(--text-muted);line-height:1.45;}
    /* Upload direct */
    .aq-upload-zone{border:2px dashed var(--border);border-radius:10px;padding:.85rem 1rem;display:flex;align-items:center;gap:.75rem;cursor:pointer;transition:all .15s;background:var(--bg);}
    .aq-upload-zone:hover{border-color:var(--primary);background:var(--primary-light);}
    .aq-upload-zone.has-file{border-color:var(--primary);border-style:solid;background:var(--primary-light);}
    .aq-upload-icon{width:36px;height:36px;border-radius:10px;background:var(--surface);border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.9rem;color:var(--text-muted);flex-shrink:0;}
    .aq-upload-text{flex:1;min-width:0;}
    .aq-upload-text strong{font-size:.82rem;display:block;}
    .aq-upload-text span{font-size:.71rem;color:var(--text-muted);}
    .aq-upload-clear{width:26px;height:26px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:.75rem;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .aq-upload-clear:hover{background:#fdecea;color:var(--danger);}
    /* Question type cards */
    .aq-qtype-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
    .aq-qtype-card{border:2px solid var(--border);border-radius:12px;padding:.85rem 1rem;cursor:pointer;transition:all .15s;position:relative;background:var(--surface);}
    .aq-qtype-card:hover{border-color:var(--primary);background:var(--primary-light);}
    .aq-qtype-card.active{border-color:var(--primary);background:var(--primary-light);}
    .aq-qtype-card-head{display:flex;align-items:center;gap:.6rem;margin-bottom:.75rem;}
    .aq-qtype-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;}
    .aq-qtype-title{font-size:.85rem;font-weight:700;}
    .aq-qtype-sub{font-size:.72rem;color:var(--text-muted);}
    .aq-counter{display:flex;align-items:center;gap:.45rem;margin-top:.5rem;}
    .aq-counter-btn{width:30px;height:30px;border:1.5px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text);cursor:pointer;font-size:.95rem;font-weight:700;display:flex;align-items:center;justify-content:center;transition:all .12s;flex-shrink:0;}
    .aq-counter-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .aq-counter-val{width:44px;text-align:center;font-size:1.1rem;font-weight:800;color:var(--text);border:1.5px solid var(--border);border-radius:8px;padding:.28rem 0;background:var(--surface);font-family:inherit;}
    .aq-counter-val:focus{border-color:var(--primary);outline:none;}
    /* Points + difficulty row */
    .aq-settings-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
    .aq-setting-block label{display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.35rem;}
    .aq-diff-pills{display:flex;gap:.4rem;}
    .aq-diff-pill{flex:1;padding:.42rem .3rem;border-radius:8px;border:1.5px solid var(--border);background:var(--bg);font-size:.76rem;font-weight:600;font-family:inherit;color:var(--text-muted);cursor:pointer;text-align:center;transition:all .14s;}
    .aq-diff-pill:hover{border-color:var(--primary);color:var(--primary);}
    .aq-diff-pill.active[data-diff="easy"]{background:#e8f5e9;color:#2e7d32;border-color:#4caf50;}
    .aq-diff-pill.active[data-diff="balanced"]{background:var(--primary-light);color:var(--primary);border-color:var(--primary);}
    .aq-diff-pill.active[data-diff="hard"]{background:#fdecea;color:#c62828;border-color:#ef5350;}
    .aq-points-input{width:100%;padding:.5rem .8rem;border:1.5px solid var(--border);border-radius:8px;font-size:.95rem;font-weight:700;font-family:inherit;background:var(--surface);color:var(--text);text-align:center;}
    .aq-points-input:focus{border-color:var(--primary);outline:none;box-shadow:0 0 0 3px var(--primary-mid);}
    /* Footer */
    .aq-footer{padding:.85rem 1.35rem;border-top:1.5px solid var(--border);display:flex;align-items:center;gap:.75rem;flex-shrink:0;background:var(--surface);}
    .aq-total-badge{font-size:.78rem;font-weight:700;color:var(--text-muted);display:flex;align-items:center;gap:.4rem;flex:1;}
    .aq-total-badge strong{color:var(--primary);font-size:.95rem;}
    .aq-gen-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.5rem;border-radius:10px;border:none;background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;font-size:.9rem;font-weight:700;font-family:inherit;cursor:pointer;transition:all .18s;box-shadow:0 3px 14px rgba(26,158,120,.35);}
    .aq-gen-btn:hover:not(:disabled){opacity:.9;transform:translateY(-1px);box-shadow:0 5px 18px rgba(26,158,120,.45);}
    .aq-gen-btn:disabled{opacity:.45;cursor:not-allowed;transform:none;}
    /* Trigger button inside quiz section */
    .aq-open-btn{display:inline-flex;align-items:center;gap:.55rem;padding:.65rem 1.3rem;border-radius:12px;border:2px solid var(--primary);background:var(--primary-light);color:var(--primary);font-size:.88rem;font-weight:700;font-family:inherit;cursor:pointer;transition:all .18s;margin-bottom:1rem;}
    .aq-open-btn:hover{background:var(--primary);color:#fff;box-shadow:0 3px 12px rgba(26,158,120,.3);}
    .aq-open-btn i{font-size:.85rem;}
    @media(max-width:860px){
      .aq-modal{max-width:720px;}
      .aq-layout{grid-template-columns:1fr;}
      .aq-side-card{padding:.9rem;}
    }

    /* Quiz builder */
    .quiz-builder{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:1rem;margin-top:.75rem;}
    .quiz-builder-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;}
    .quiz-builder-title{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--primary);display:flex;align-items:center;gap:.4rem;}
    .quiz-question-card{background:var(--surface);border:1.5px solid var(--border);border-radius:10px;padding:.9rem 1rem;margin-bottom:.75rem;transition:border-color var(--trans);}
    .quiz-question-card:focus-within{border-color:var(--primary);}
    .qc-head{display:flex;align-items:center;gap:.6rem;margin-bottom:.7rem;}
    .q-num{width:26px;height:26px;border-radius:50%;background:var(--primary);color:#fff;font-size:.72rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .qc-head input{flex:1;padding:.42rem .75rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);}
    .qc-head input:focus{border-color:var(--primary);outline:none;box-shadow:0 0 0 2px var(--primary-mid);}
    .q-pts-wrap{display:flex;align-items:center;gap:.3rem;font-size:.75rem;color:var(--text-muted);flex-shrink:0;}
    .q-pts-wrap input{width:52px;padding:.32rem .5rem;border:1.5px solid var(--border);border-radius:6px;font-size:.78rem;font-family:inherit;text-align:center;background:var(--surface);color:var(--text);}
    .choices-list{display:flex;flex-direction:column;gap:.4rem;margin-bottom:.6rem;}
    .choice-row{display:flex;align-items:center;gap:.5rem;}
    .choice-letter{width:26px;height:26px;border-radius:50%;background:var(--bg);border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:var(--text-muted);flex-shrink:0;}
    .choice-row input[type="text"]{flex:1;padding:.38rem .7rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.85rem;font-family:inherit;background:var(--surface);color:var(--text);}
    .choice-row input[type="text"]:focus{border-color:var(--primary);outline:none;}
    .correct-radio{width:16px;height:16px;accent-color:var(--primary);cursor:pointer;flex-shrink:0;}
    .correct-radio:checked + .choice-letter{background:var(--primary);border-color:var(--primary);color:#fff;}
    .choice-rm{width:22px;height:22px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:.7rem;border-radius:6px;display:flex;align-items:center;justify-content:center;transition:all var(--trans);flex-shrink:0;}
    .choice-rm:hover{background:#fdecea;color:var(--danger);}
    .add-choice-btn{display:inline-flex;align-items:center;gap:.3rem;font-size:.75rem;font-weight:600;color:var(--primary);background:none;border:1.5px dashed var(--primary);border-radius:20px;padding:.25rem .75rem;cursor:pointer;transition:all var(--trans);}
    .add-choice-btn:hover{background:var(--primary-light);}
    .q-foot{display:flex;align-items:center;justify-content:space-between;margin-top:.5rem;}
    .del-q-btn{font-size:.73rem;color:var(--danger);background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:.25rem;padding:.2rem .4rem;border-radius:6px;transition:all var(--trans);}
    .del-q-btn:hover{background:#fdecea;}
    .add-q-btn{display:flex;align-items:center;gap:.4rem;width:100%;padding:.55rem;border:2px dashed var(--border);background:none;border-radius:10px;font-size:.82rem;font-weight:600;color:var(--text-muted);cursor:pointer;justify-content:center;transition:all var(--trans);font-family:inherit;}
    .add-q-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}

    /* Custom type modal */
    .ct-modal-back{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:600;align-items:center;justify-content:center;}
    .ct-modal-back.show{display:flex;}
    .ct-modal{background:var(--surface);border-radius:14px;width:100%;max-width:440px;margin:1rem;box-shadow:var(--shadow-lg);animation:popIn .2s ease;}
    @keyframes popIn{from{opacity:0;transform:scale(.96) translateY(8px);}to{opacity:1;transform:scale(1) translateY(0);}}
    .ct-modal-head{padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
    .ct-modal-head h3{font-size:.95rem;font-weight:700;}
    .ct-body{padding:1.1rem 1.25rem;}
    .ct-field{margin-bottom:.85rem;}
    .ct-label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.3rem;}
    .ct-input{width:100%;padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);}
    .ct-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);outline:none;}
    .ct-toggle-row{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:.25rem;}
    .ct-toggle{display:flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:500;cursor:pointer;user-select:none;}
    .ct-toggle input{accent-color:var(--primary);}
    .ct-foot{padding:.85rem 1.25rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.5rem;}
    .icon-grid{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.35rem;}
    .icon-opt{width:34px;height:34px;border-radius:8px;border:1.5px solid var(--border);background:var(--bg);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.88rem;color:var(--text-muted);transition:all var(--trans);}
    .icon-opt:hover,.icon-opt.selected{border-color:var(--primary);background:var(--primary-light);color:var(--primary);}


    .dd-quick-btn {
  padding:.28rem .6rem;border-radius:20px;border:1.5px solid var(--border);
  background:var(--bg);font-size:.72rem;font-weight:600;font-family:inherit;
  color:var(--text-muted);cursor:pointer;transition:all .15s;
    }
      .dd-quick-btn:hover { border-color:var(--primary);color:var(--primary);background:var(--primary-light); }
      .dd-quick-btn.active { background:var(--primary);color:#fff;border-color:var(--primary); }
    .dd2-modal{background:var(--surface);border-radius:16px;border:1px solid var(--border);width:100%;max-width:640px;overflow:hidden;box-shadow:var(--shadow-lg);margin:1rem;}
    .dd2-head{padding:.9rem 1.1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:.75rem;}
    .dd2-head-left{display:flex;align-items:center;gap:.6rem;}
    .dd2-head-icon{width:32px;height:32px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;color:var(--primary);}
    .dd2-title{font-size:1rem;font-weight:700;color:var(--text);}
    .dd2-close{width:28px;height:28px;border:none;background:none;border-radius:8px;cursor:pointer;color:var(--text-muted);}
    .dd2-close:hover{background:var(--bg);}
    .dd2-body{display:grid;grid-template-columns:1fr 220px;min-height:360px;}
    .dd2-left{padding:1rem 1.1rem;border-right:1px solid var(--border);}
    .dd2-right{padding:1rem;display:flex;flex-direction:column;gap:1rem;background:var(--bg);}
    .dd2-cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;}
    .dd2-nav-btn{width:28px;height:28px;border:none;background:none;border-radius:8px;cursor:pointer;color:var(--text-muted);}
    .dd2-nav-btn:hover{background:var(--bg);}
    .dd2-month-label{font-size:.95rem;font-weight:700;color:var(--text);}
    .dd2-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;}
    .dd2-dow div{text-align:center;font-size:.68rem;font-weight:700;color:var(--text-muted);padding:.25rem 0;}
    .dd2-day-grid{margin-top:.25rem;}
    .dd2-day{aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;border:none;border-radius:8px;background:transparent;font-size:.82rem;font-weight:600;color:var(--text);cursor:pointer;transition:all .12s;}
    .dd2-day:hover:not(.is-past):not(.is-selected){background:var(--primary-light);}
    .dd2-day.is-past{color:#c2c8d0;cursor:not-allowed;}
    .dd2-day.is-today{box-shadow:inset 0 0 0 1.5px var(--primary);color:var(--primary);}
    .dd2-day.is-selected{background:var(--primary);color:#fff;font-weight:700;}
    .dd2-day-empty{aspect-ratio:1/1;}
    .dd2-rc-label{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:.4rem;}
    .dd2-pill-col{display:flex;flex-wrap:wrap;gap:.35rem;}
    .dd2-time-row{display:flex;align-items:center;gap:.4rem;}
    .dd2-time-row i{font-size:.84rem;color:var(--primary);}
    .dd2-time-inp{width:44px;padding:.32rem .4rem;border:1.5px solid var(--border);border-radius:7px;font-size:.82rem;text-align:center;font-family:inherit;background:var(--surface);color:var(--text);}
    .dd2-time-sep{font-weight:700;color:var(--text-muted);}
    .dd2-ampm{padding:.32rem .45rem;border:1.5px solid var(--border);border-radius:7px;font-size:.8rem;background:var(--surface);color:var(--text);font-family:inherit;}
    .dd2-preview-box{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:.6rem .75rem;margin-top:auto;}
    .dd2-preview-label{font-size:.62rem;text-transform:uppercase;letter-spacing:.55px;color:var(--text-muted);margin-bottom:.2rem;font-weight:700;}
    .dd2-preview-val{font-size:.84rem;font-weight:700;color:var(--text);}
    .dd2-preview-empty{color:var(--text-muted);font-weight:500;}
    .dd2-foot{padding:.75rem 1.1rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:.5rem;}
    .dd2-btn{display:inline-flex;align-items:center;gap:.35rem;padding:6px 14px;border-radius:9px;font-size:.82rem;font-weight:700;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;}
    .dd2-btn:hover{background:var(--bg);}
    .dd2-btn-clear{color:var(--danger);border-color:#f7c1c1;background:#fcebeb;}
    .dd2-btn-clear:hover{background:#f7c1c1;}
    .dd2-btn-done{background:var(--primary);border-color:var(--primary-dark);color:#fff;}
    .dd2-btn-done:hover{background:var(--primary-dark);}
    @media(max-width:760px){
      .dd2-modal{max-width:96vw;}
      .dd2-body{grid-template-columns:1fr;}
      .dd2-left{border-right:none;border-bottom:1px solid var(--border);}
    }
    /* Post cards */
    .post-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;box-shadow:none;margin-bottom:1rem;overflow:hidden;transition:border-color var(--trans),background var(--trans);animation:fadeUp .25s ease;}
    @keyframes fadeUp{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
    .post-card:hover{border-color:rgba(26,158,120,.28);background:var(--surface);}
    .pc-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1rem .45rem;}
    .pc-avatar{
      width:36px;height:36px;border-radius:50%;flex-shrink:0;
      display:flex;align-items:center;justify-content:center;
      color:#fff;font-weight:800;font-size:.82rem;
    }
    .pc-meta{flex:1;min-width:0;}
    .pc-author{font-size:.9rem;font-weight:800;line-height:1.2;color:var(--text);}
    .pc-date{font-size:.75rem;color:var(--text-muted);margin-top:.12rem;font-weight:500;line-height:1.2;}
    .pc-type-badge{
      font-size:.625rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;
      padding:.18rem .45rem;border-radius:4px;display:flex;align-items:center;gap:.25rem;white-space:nowrap;
    }
    .pc-actions{display:flex;gap:.2rem;}
    .pc-act-btn{width:30px;height:30px;border:none;background:none;cursor:pointer;color:var(--text-muted);border-radius:8px;font-size:.78rem;display:flex;align-items:center;justify-content:center;transition:all var(--trans);}
    .pc-act-btn:hover{background:var(--bg);color:var(--text);}
    .pc-act-btn.del-btn:hover{background:#fdecea;color:var(--danger);}
    .pc-body{padding:.25rem 1rem .9rem;}
    .pc-title{font-size:1rem;font-weight:700;margin-bottom:.35rem;}
    .pc-text{font-size:.875rem;line-height:1.6;color:var(--text);opacity:.9;white-space:pre-wrap;}
    .pc-meta-row{display:flex;gap:1rem;font-size:.78rem;color:var(--text-muted);margin-top:.5rem;}
    .pc-meta-row span{display:flex;align-items:center;gap:.3rem;}
    .pc-attachments{padding:0 1.1rem 1rem;display:flex;flex-wrap:wrap;gap:.5rem;}
    .pa-chip{display:inline-flex;align-items:center;gap:.45rem;padding:.42rem .8rem;border-radius:var(--radius-sm);background:var(--bg);border:1.5px solid var(--border);font-size:.78rem;font-weight:500;color:var(--text);text-decoration:none;transition:all var(--trans);max-width:260px;cursor:pointer;}
    .pa-chip:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .pa-chip i{font-size:.8rem;flex-shrink:0;}
    .pa-chip span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .pa-yt{border-color:#ff0000;color:#ff0000;}
    .pa-yt:hover{background:#fff5f5;color:#c62828;}
    .quiz-preview{padding:0 1.1rem 1rem;}
    .quiz-q-item{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:.7rem .9rem;margin-bottom:.5rem;}
    .quiz-q-text{font-size:.88rem;font-weight:600;margin-bottom:.45rem;}
    .quiz-choices{display:flex;flex-direction:column;gap:.3rem;}
    .quiz-choice{display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:var(--text-muted);}
    .choice-dot{width:14px;height:14px;border-radius:50%;border:1.5px solid var(--border);flex-shrink:0;}
    .quiz-choice.correct .choice-dot{background:var(--primary);border-color:var(--primary);}
    .quiz-choice.correct{color:var(--primary);font-weight:600;}
    .quiz-total-pts{font-size:.75rem;color:var(--text-muted);margin-top:.5rem;font-style:italic;}
    .quiz-compact-summary{
  background:#fff;
  border:none;
  border-radius:14px;
  padding:.45rem .7rem;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:.55rem;
  flex-wrap:nowrap;
  color:var(--text);
  cursor:default;
}
    .quiz-compact-left{display:flex;align-items:center;gap:.5rem;min-width:0;flex-wrap:nowrap;flex:1;}
    .quiz-compact-main{display:flex;align-items:center;gap:.5rem;min-width:0;white-space:nowrap;overflow:hidden;}
    .quiz-compact-title{
      font-size:1.02rem;font-weight:800;color:#fff;white-space:nowrap;
      padding-right:.8rem;margin-right:.1rem;border-right:1px solid rgba(255,255,255,.2);
    }
    .quiz-compact-sub{
      font-size:.76rem;font-weight:600;color:var(--text-muted);white-space:nowrap;
      padding-left:.55rem;border-left:1px solid var(--border);
    }
    .quiz-compact-info{display:flex;align-items:center;gap:.35rem;flex-wrap:nowrap;padding-right:.2rem;margin-right:0;border-right:none;}
    .quiz-compact-info .pc-type-badge{font-size:.62rem;padding:.16rem .52rem;}
    .post-card.quiz-post{border:1px solid var(--border);background:var(--surface);}
    .post-card.quiz-post .pc-head{padding:.88rem 1.1rem .52rem;}
    .post-card.quiz-post .pc-avatar{
      width:38px;height:38px;border-radius:50%;
      display:flex;align-items:center;justify-content:center;
      font-size:1rem;font-weight:800;flex-shrink:0;
    }
    .post-card.quiz-post .pc-meta{display:flex;flex-direction:column;align-items:flex-start;gap:.15rem;min-width:0;flex:1;}
    .post-card.quiz-post .pc-author{font-size:1.08rem;color:var(--text);font-weight:800;line-height:1.15;flex:1;min-width:0;}
    .post-card.quiz-post .pc-date{font-size:.82rem;color:var(--text-muted);font-weight:700;line-height:1.2;margin-top:0;white-space:nowrap;}
    .post-card.quiz-post .pc-type-badge{margin-left:auto;}
    .post-card.quiz-post .pc-actions .pc-act-btn{color:var(--text-muted);border:1px solid var(--border);border-radius:10px;background:var(--bg);}
    .post-card.quiz-post .pc-actions .pc-act-btn:hover{background:var(--primary-light);color:var(--primary);}
    .post-card.quiz-post .pc-body{display:block;}
    .post-card.quiz-post .pc-comments{border-top:1px solid var(--border);}
    .post-card.quiz-post .pc-comments-toggle{color:var(--text-muted);}
    .post-card.quiz-post .pc-comments-toggle:hover{background:var(--primary-light);color:var(--primary);}
    .pc-manage-quiz-btn i { font-size: .78rem; }

    /* ── Publish button (draft quizzes) ── */
    .pc-publish-btn {
      display: inline-flex; align-items: center; gap: .4rem;
      padding: .38rem .85rem; border: 1.5px solid var(--warning); border-radius: 20px;
      background: #fff8ec; color: #b45309;
      font-family: inherit; font-size: .72rem; font-weight: 700; letter-spacing: .25px;
      cursor: pointer; white-space: nowrap; margin-right: .35rem;
      transition: all .18s;
    }
    body.dark .pc-publish-btn { background: rgba(245,124,0,.12); color: #ffb74d; border-color: rgba(245,124,0,.45); }
    .pc-publish-btn:hover {
      background: var(--warning); color: #fff; border-color: var(--warning);
      transform: translateY(-1px); box-shadow: 0 4px 14px rgba(245,124,0,.4);
    }
    .pc-publish-btn:active { transform: translateY(0); }
    .pc-publish-btn:disabled { opacity: .55; cursor: wait; }
    .pc-publish-btn i { font-size: .78rem; }

/* removed duplicate quiz compact overrides (kept unified style block above) */

.quiz-card-actions{
  display:flex;
  align-items:center;
  gap:.45rem;
  flex-wrap:wrap;
}

.quiz-view-btn{
  display:inline-flex;
  align-items:center;
  gap:.4rem;
  border:1.5px solid var(--border);
  background:var(--surface);
  color:var(--text);
  border-radius:20px;
  padding:.35rem .75rem;
  font-size:.76rem;
  font-weight:700;
  font-family:inherit;
  cursor:pointer;
  transition:all var(--trans);
}

.quiz-view-btn:hover{
  border-color:var(--primary);
  color:var(--primary);
  background:var(--primary-light);
}

.quiz-modal-list{
  text-align:left;
  max-height:60vh;
  overflow:auto;
  padding-right:.25rem;
}

.quiz-modal-item{
  border:1px solid #e8eaed;
  background:#f8f9fa;
  border-radius:10px;
  padding:.75rem .85rem;
  margin-bottom:.65rem;
}

.quiz-modal-question{
  font-weight:700;
  color:#1c2027;
  margin-bottom:.5rem;
  font-size:.9rem;
}

.quiz-modal-choice{
  display:flex;
  align-items:center;
  gap:.45rem;
  color:#5f6368;
  font-size:.84rem;
  margin:.28rem 0;
}

.quiz-modal-dot{
  width:13px;
  height:13px;
  border-radius:50%;
  border:1.5px solid #d0d5dd;
  flex-shrink:0;
}

.quiz-modal-answer{
  color:var(--primary);
  font-weight:800;
  margin-top:.35rem;
  font-size:.86rem;
}
  .answer-key-btn{
  margin-top:.65rem;
  display:inline-flex;
  align-items:center;
  gap:.4rem;
  border:1.5px solid var(--primary);
  background:var(--primary-light);
  color:var(--primary);
  border-radius:20px;
  padding:.35rem .75rem;
  font-size:.76rem;
  font-weight:700;
  font-family:inherit;
  cursor:pointer;
  transition:all var(--trans);
}

.answer-key-btn:hover{
  background:var(--primary);
  color:#fff;
}

.answer-key-panel{
  display:none;
  margin-top:.65rem;
  border:1px solid var(--border);
  background:var(--surface);
  border-radius:10px;
  padding:.75rem .85rem;
}

.answer-key-panel.show{
  display:block;
}

.answer-key-title{
  font-size:.78rem;
  font-weight:800;
  color:var(--primary);
  margin-bottom:.55rem;
  display:flex;
  align-items:center;
  gap:.4rem;
}

.answer-key-item{
  font-size:.82rem;
  color:var(--text);
  padding:.45rem 0;
  border-top:1px dashed var(--border);
}

.answer-key-item:first-of-type{
  border-top:none;
}

.answer-key-answer{
  margin-top:.2rem;
  color:var(--primary);
  font-weight:700;
}

/* removed legacy duplicate quiz compact block to prevent style conflicts */

.tl-quiz-popup{
  border-radius:16px !important;
  padding:1.2rem !important;
}

.tl-quiz-title{
  font-size:1.35rem !important;
  font-weight:800 !important;
  color:#1c2027 !important;
  padding:.3rem 0 1rem !important;
}

.tl-quiz-html{
  margin:0 !important;
  padding:0 !important;
}

.quiz-modal-list{
  text-align:left;
  max-height:58vh;
  overflow:auto;
  padding:.15rem .35rem .15rem 0;
}

.quiz-modal-item{
  border:1px solid #e8eaed;
  background:#f8f9fa;
  border-radius:12px;
  padding:.85rem .95rem;
  margin-bottom:.65rem;
}

.quiz-modal-question{
  font-weight:800;
  color:#1c2027;
  margin-bottom:.55rem;
  font-size:.88rem;
  line-height:1.35;
}

.quiz-modal-choice{
  display:flex;
  align-items:center;
  gap:.5rem;
  color:#5f6368;
  font-size:.84rem;
  line-height:1.3;
  margin:.3rem 0;
}

.quiz-modal-dot{
  width:13px;
  height:13px;
  border-radius:50%;
  border:1.5px solid #d0d5dd;
  flex-shrink:0;
}

.quiz-modal-answer{
  display:flex;
  align-items:flex-start;
  gap:.45rem;
  color:var(--primary);
  font-weight:800;
  margin-top:.35rem;
  font-size:.86rem;
  line-height:1.35;
}
    .empty-feed{text-align:center;padding:3.5rem 1rem;color:var(--text-muted);}
    .empty-feed .ef-icon{font-size:3rem;opacity:.25;margin-bottom:.8rem;}
    .empty-feed .ef-title{font-size:1.05rem;font-weight:600;margin-bottom:.3rem;}
    .empty-feed .ef-sub{font-size:.85rem;}
    .people-section{margin-bottom:1.5rem;}
    .people-section-head{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);padding-bottom:.6rem;border-bottom:2px solid var(--border);margin-bottom:.75rem;display:flex;align-items:center;justify-content:space-between;}
    .people-count{font-size:.72rem;font-weight:600;color:var(--primary);background:var(--primary-light);padding:.1rem .45rem;border-radius:20px;}
    .person-row{display:flex;align-items:center;gap:.75rem;padding:.65rem .5rem;border-bottom:1px solid var(--border);transition:background var(--trans);border-radius:var(--radius-sm);}
    .person-row:hover{background:var(--bg);}
    .person-row:last-child{border-bottom:none;}
    .person-avatar{width:34px;height:34px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.82rem;}
    .person-info{flex:1;}
    .person-name{font-size:.88rem;font-weight:600;}
    .person-email{font-size:.75rem;color:var(--text-muted);}
    .person-actions{display:flex;gap:.3rem;}
    .pa-btn{padding:.28rem .7rem;border-radius:6px;border:none;font-size:.73rem;font-weight:600;font-family:inherit;cursor:pointer;transition:all var(--trans);}
    .pa-approve{background:var(--primary-light);color:var(--primary);}
    .pa-approve:hover{background:var(--primary);color:#fff;}
    .pa-reject{background:#fdecea;color:var(--danger);}
    .pa-reject:hover{background:var(--danger);color:#fff;}
    .pa-remove{background:var(--bg);color:var(--text-muted);border:1px solid var(--border);}
    .pa-remove:hover{background:#fdecea;color:var(--danger);border-color:var(--danger);}
    .cw-topic-group{margin-bottom:1.5rem;}
    .cw-topic-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:.6rem;display:flex;align-items:center;gap:.5rem;}
    .cw-topic-label::after{content:'';flex:1;height:1px;background:var(--border);}
    .cw-period-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.8rem;margin-bottom:1.35rem;}
    .cw-period-box{min-width:0;border:1px solid var(--border);border-radius:12px;background:var(--surface);overflow:hidden;}
    .cw-period-head{display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.68rem .8rem;background:var(--primary-light);color:var(--primary-dark);font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.65px;}
    .cw-period-count{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 .35rem;border-radius:999px;background:var(--surface);color:var(--primary);font-size:.68rem;}
    .cw-period-body{padding:.7rem;}
    .cw-period-empty{padding:.7rem .2rem;color:var(--text-muted);font-size:.76rem;text-align:center;}
    .cw-period-box .cw-topic-group{margin-bottom:.8rem;}
    .cw-period-box .cw-topic-group:last-child{margin-bottom:0;}
    .cw-period-box .cw-topic-label{font-size:.64rem;margin-bottom:.35rem;}
    .cw-period-box .cw-item{padding:.55rem .6rem;}
    @media(max-width:980px){.cw-period-grid{grid-template-columns:1fr;}}
    .cw-item{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:.8rem 1.1rem;margin-bottom:.5rem;cursor:pointer;transition:all var(--trans);display:flex;align-items:center;gap:.75rem;}
    .cw-item:hover{border-color:var(--primary);box-shadow:var(--shadow);transform:translateX(3px);}
    .cw-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.88rem;flex-shrink:0;}
    .cw-title{font-size:.9rem;font-weight:600;flex:1;}
    .cw-date{font-size:.75rem;color:var(--text-muted);}
    .pc-comments{border-top:1px solid var(--border);}
    .pc-comments-toggle{width:100%;display:flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;border:none;background:none;font-size:.79rem;font-weight:600;font-family:inherit;color:var(--text-muted);cursor:pointer;text-align:left;transition:color var(--trans),background var(--trans);border-radius:0 0 var(--radius) var(--radius);}
    .pc-comments-toggle:hover{color:var(--primary);background:var(--primary-light);}
    .pc-comments-toggle .cmt-arrow{margin-left:auto;transition:transform .2s;}
    .pc-comments-toggle.open .cmt-arrow{transform:rotate(180deg);}
    .pc-comments-body{display:none;padding:.6rem 1.1rem 1rem;}
    .pc-comments-body.open{display:block;}
    .comment-list{display:flex;flex-direction:column;gap:.55rem;margin-bottom:.75rem;}
    .comment-item{display:flex;gap:.55rem;align-items:flex-start;animation:fadeUp .18s ease;}
    .cmt-av{width:28px;height:28px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#fff;background:linear-gradient(135deg,#667eea,#764ba2);}
    .cmt-av.is-faculty{background:linear-gradient(135deg,var(--primary),var(--accent));}
    .cmt-bubble{flex:1;background:var(--bg);border:1px solid var(--border);border-radius:2px 10px 10px 10px;padding:.42rem .72rem;font-size:.82rem;position:relative;min-width:0;}
    .cmt-meta{display:flex;align-items:baseline;gap:.4rem;flex-wrap:wrap;margin-bottom:.12rem;}
    .cmt-author{font-size:.74rem;font-weight:700;color:var(--text);}
    .cmt-author.is-faculty{color:var(--primary);}
    .cmt-faculty-badge{font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:.05rem .32rem;border-radius:20px;background:var(--primary-light);color:var(--primary);}
    .cmt-time{font-size:.67rem;color:var(--text-muted);}
    .cmt-text{line-height:1.55;color:var(--text);white-space:pre-wrap;word-break:break-word;}
    .cmt-del{position:absolute;top:.32rem;right:.38rem;width:20px;height:20px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:.65rem;border-radius:5px;display:flex;align-items:center;justify-content:center;transition:all var(--trans);opacity:0;}
    .cmt-bubble:hover .cmt-del{opacity:1;}
    .cmt-del:hover{background:#fdecea;color:var(--danger);}
    .cmt-empty{text-align:center;padding:.9rem;color:var(--text-muted);font-size:.79rem;font-style:italic;}
    .cmt-input-row{display:flex;gap:.45rem;align-items:flex-end;}
    .cmt-input{flex:1;padding:.42rem .78rem;border:1.5px solid var(--border);border-radius:18px;font-size:.82rem;font-family:inherit;background:var(--surface);color:var(--text);resize:none;min-height:34px;max-height:110px;overflow-y:auto;line-height:1.45;transition:border-color var(--trans),box-shadow var(--trans);}
    .cmt-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);outline:none;}
    .cmt-send{width:32px;height:32px;border:none;border-radius:50%;background:var(--primary);color:#fff;cursor:pointer;font-size:.8rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all var(--trans);box-shadow:0 2px 8px rgba(26,158,120,.28);}
    .cmt-send:hover:not(:disabled){background:var(--primary-dark);transform:scale(1.06);}
    .cmt-send:disabled{opacity:.38;cursor:not-allowed;transform:none;}

    /* ══ FILE VIEWER ══ */
    .fv-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:2000;flex-direction:column;align-items:center;justify-content:center;padding:1rem;}
    .fv-backdrop.show{display:flex;}
    .fv-shell{width:100%;max-width:1300px;height:92vh;display:flex;flex-direction:column;border-radius:14px;overflow:hidden;box-shadow:0 20px 55px rgba(0,0,0,.28);border:1px solid var(--border);}
    .fv-toolbar{flex-shrink:0;display:flex;align-items:center;gap:.5rem;padding:.6rem 1rem;background:var(--surface);border-bottom:1px solid var(--border);}
    .fv-filename{flex:1;font-size:.82rem;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .fv-badge{flex-shrink:0;font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;padding:.15rem .5rem;border-radius:20px;background:var(--bg);color:var(--text-muted);border:1px solid var(--border);}
    .fv-tb-btn{flex-shrink:0;width:32px;height:32px;border:1px solid var(--border);border-radius:7px;background:var(--bg);color:var(--text-muted);font-size:.85rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;text-decoration:none;}
    .fv-tb-btn:hover{background:var(--primary-light);color:var(--primary);border-color:var(--primary-mid);}
    .fv-tb-btn:disabled{opacity:.28;cursor:not-allowed;}
    .fv-tb-btn.danger{background:rgba(220,38,38,.18);color:#fc8181;}
    .fv-tb-btn.danger:hover{background:rgba(220,38,38,.42);color:#fff;}
    .fv-sep{width:1px;height:20px;background:var(--border);flex-shrink:0;}
    .fv-page-info{font-size:.72rem;color:var(--text-muted);white-space:nowrap;min-width:60px;text-align:center;flex-shrink:0;}
    .fv-zoom-label{font-size:.72rem;color:var(--text-muted);min-width:42px;text-align:center;flex-shrink:0;}
    .fv-layout{display:flex;flex:1;min-height:0;overflow:hidden;}
    #fvBody{flex:1;min-width:0;min-height:0;position:relative;background:#0f1520;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .fv-scroll{width:100%;height:100%;overflow:auto;display:flex;align-items:flex-start;justify-content:center;padding:1.25rem;}
    .fv-scroll canvas{border-radius:3px;box-shadow:0 6px 30px rgba(0,0,0,.5);flex-shrink:0;}
    .fv-iframe{width:100%;height:100%;border:none;background:#fff;}
    .fv-img-wrap{width:100%;height:100%;display:flex;align-items:center;justify-content:center;padding:1.25rem;}
    .fv-img-wrap img{max-width:100%;max-height:100%;object-fit:contain;border-radius:5px;box-shadow:0 6px 30px rgba(0,0,0,.5);}
    .fv-media-wrap{width:100%;height:100%;display:flex;align-items:center;justify-content:center;padding:1.5rem;}
    .fv-media-wrap video,.fv-media-wrap audio{max-width:100%;max-height:100%;border-radius:8px;outline:none;}
    .fv-text-wrap{width:100%;height:100%;overflow:auto;padding:1.5rem 2rem;color:var(--text);font-family:'DM Mono',monospace;font-size:.82rem;line-height:1.7;white-space:pre-wrap;word-break:break-all;background:var(--surface);}
    .fv-loading{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.75rem;color:var(--text-muted);font-size:.85rem;pointer-events:none;}
    .fv-spinner{width:36px;height:36px;border:3px solid rgba(255,255,255,.08);border-top-color:#1a9e78;border-radius:50%;animation:sp .7s linear infinite;}
    .fv-no-preview{text-align:center;color:#6b7fa0;padding:2rem;}
    .fv-no-preview .fv-np-icon{font-size:3.5rem;opacity:.3;margin-bottom:1rem;}
    .fv-no-preview h3{font-size:1rem;font-weight:600;color:#8da0b8;margin-bottom:.5rem;}
    .fv-no-preview p{font-size:.82rem;margin-bottom:1.5rem;}
    .fv-no-preview a{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.4rem;background:#1a9e78;color:#fff;border-radius:8px;text-decoration:none;font-size:.85rem;font-weight:600;transition:opacity .15s;}
    .fv-no-preview a:hover{opacity:.85;}
    .fv-side-panel{width:320px;flex-shrink:0;background:var(--surface);border-left:1px solid var(--border);display:flex;flex-direction:column;transition:width .25s ease,opacity .25s ease;}
    .fv-side-panel.collapsed{width:0;overflow:hidden;border-left:none;opacity:0;}
    .fv-panel-tabs{display:flex;flex-shrink:0;background:var(--bg);border-bottom:1px solid var(--border);align-items:center;padding:0 .6rem 0 1rem;}
    .fv-panel-header-title{flex:1;font-size:.78rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:.45rem;}
    .fv-panel-header-title i{color:#1a9e78;font-size:.75rem;}
    .fv-tab-badge{background:#f5b900;color:#141b2d;font-size:.58rem;font-weight:700;padding:.05rem .3rem;border-radius:20px;margin-left:.1rem;}
    .fv-panel-close{width:26px;height:26px;border:1px solid var(--border);background:var(--surface);color:var(--text-muted);border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.65rem;transition:all .15s;flex-shrink:0;margin:.45rem 0;}
    .fv-panel-close:hover{background:var(--primary-light);color:var(--primary);}
    .fv-panel-toggle-btn{display:flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:600;color:var(--text-muted);background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:.3rem .65rem;cursor:pointer;transition:all .15s;white-space:nowrap;}
    .fv-panel-toggle-btn:hover{background:var(--primary-light);color:var(--primary);border-color:var(--primary-mid);}
    .fv-panel-page-strip{padding:.38rem 1rem;background:var(--bg);border-bottom:1px solid var(--border);font-size:.68rem;color:var(--text-muted);display:flex;align-items:center;gap:.35rem;flex-shrink:0;}
    .fv-panel-page-strip i{font-size:.62rem;}
    .fv-private-badge{font-size:.56rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:.08rem .36rem;border-radius:20px;background:rgba(245,185,0,.13);color:#f5b900;border:1px solid rgba(245,185,0,.22);white-space:nowrap;margin-left:auto;display:flex;align-items:center;gap:.2rem;}
    .fv-notes-pane{display:flex;flex:1;flex-direction:column;min-height:0;}
    .fv-item-list{flex:1;overflow-y:auto;padding:.6rem .75rem;display:flex;flex-direction:column;gap:.45rem;}
    .fv-item-list::-webkit-scrollbar{width:3px;}
    .fv-item-list::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:3px;}
    .fv-empty-msg{text-align:center;padding:2rem .5rem;color:var(--text-muted);font-size:.73rem;font-style:italic;line-height:1.65;}
    .fv-empty-msg i{display:block;font-size:1.6rem;margin-bottom:.45rem;opacity:.35;}
    .fv-item-card{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:.5rem .65rem;position:relative;transition:border-color .15s;}
    .fv-item-card:hover{border-color:var(--primary-mid);}
    .fv-item-time{font-size:.6rem;color:var(--text-muted);margin-bottom:.2rem;}
    .fv-item-text{font-size:.78rem;line-height:1.55;color:var(--text);white-space:pre-wrap;word-break:break-word;}
    .fv-item-del{position:absolute;top:.28rem;right:.32rem;width:18px;height:18px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:.6rem;border-radius:4px;display:flex;align-items:center;justify-content:center;transition:all .15s;opacity:0;}
    .fv-item-card:hover .fv-item-del{opacity:1;}
    .fv-item-del:hover{background:rgba(220,38,38,.22);color:#fc8181;}
    .fv-input-area{padding:.6rem .75rem;border-top:1px solid var(--border);flex-shrink:0;}
    .fv-input-hint{font-size:.63rem;color:var(--text-muted);margin-bottom:.35rem;display:flex;align-items:center;gap:.28rem;}
    .fv-input-row{display:flex;gap:.38rem;align-items:flex-end;}
    .fv-textarea{flex:1;background:var(--surface);border:1.5px solid var(--border);border-radius:11px;color:var(--text);font-size:.76rem;font-family:inherit;padding:.35rem .6rem;resize:none;min-height:32px;max-height:88px;overflow-y:auto;line-height:1.4;transition:border-color .15s;}
    .fv-textarea:focus{border-color:#1a9e78;outline:none;}
    .fv-textarea::placeholder{color:var(--text-muted);}
    .fv-send-btn{width:28px;height:28px;border:none;border-radius:50%;background:#1a9e78;color:#fff;cursor:pointer;font-size:.7rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s;}
    .fv-send-btn:hover:not(:disabled){background:#0d7a5e;transform:scale(1.07);}
    .fv-send-btn:disabled{opacity:.3;cursor:not-allowed;transform:none;}

    /* Edit class modal */
    .modal-back{display:flex;position:fixed;inset:0;background:rgba(0,0,0,.52);z-index:400;align-items:center;justify-content:center;opacity:0;visibility:hidden;pointer-events:none;transition:opacity .22s ease,visibility 0s linear .22s;}
    .modal-back.show{opacity:1;visibility:visible;pointer-events:auto;transition-delay:0s;}
    .post-modal{background:var(--surface);border-radius:16px;width:100%;max-width:720px;max-height:92vh;overflow-y:auto;margin:1rem;box-shadow:0 30px 80px rgba(0,0,0,.22);opacity:0;transform:scale(.96) translateY(10px);transition:opacity .24s cubic-bezier(.16,1,.3,1),transform .24s cubic-bezier(.16,1,.3,1);}
    .modal-back.show .post-modal{opacity:1;transform:scale(1) translateY(0);}
    .pm-header{position:sticky;top:0;z-index:1;padding:1rem 1.4rem;background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
    .pm-title{font-size:1rem;font-weight:700;display:flex;align-items:center;gap:.6rem;}
    .pm-type-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;}
    .pm-close{width:30px;height:30px;border:none;background:var(--bg);color:var(--text-muted);border-radius:8px;cursor:pointer;font-size:.85rem;display:flex;align-items:center;justify-content:center;transition:all var(--trans);}
    .pm-close:hover{background:#fdecea;color:var(--danger);}
    .pm-body{padding:1.25rem 1.4rem;}
    .pm-field{margin-bottom:1rem;}
    .pm-label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.35rem;}
    .pm-input,.pm-textarea,.pm-select{width:100%;padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.88rem;font-family:inherit;background:var(--surface);color:var(--text);transition:border-color var(--trans),box-shadow var(--trans);}
    .pm-input:focus,.pm-textarea:focus,.pm-select:focus{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);outline:none;}
    .pm-textarea{resize:vertical;min-height:90px;}
    .pm-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
    .pm-footer{position:sticky;bottom:0;padding:.85rem 1.4rem;background:var(--surface);border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.6rem;z-index:1;}

    .btn{padding:.5rem 1.25rem;border-radius:var(--radius-sm);font-size:.86rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all var(--trans);display:inline-flex;align-items:center;gap:.4rem;}
    .btn-primary{background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;box-shadow:0 2px 10px rgba(26,158,120,.3);}
    .btn-primary:hover:not(:disabled){opacity:.9;transform:translateY(-1px);}
    .btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none;}
    .btn-ghost{background:var(--bg);color:var(--text);border:1.5px solid var(--border);}
    .btn-ghost:hover{border-color:var(--primary);color:var(--primary);}
    .btn-sm{padding:.32rem .85rem;font-size:.78rem;}
    .btn-danger{background:#fdecea;color:var(--danger);border:1.5px solid #f5c2c7;}
    .btn-danger:hover{background:var(--danger);color:#fff;}

    .lesson-week-backdrop{display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,.46);align-items:center;justify-content:center;padding:1rem;}
    .lesson-week-backdrop.show{display:flex;}
    .lesson-week-modal{width:min(520px,calc(100vw - 2rem));background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:0 26px 80px rgba(0,0,0,.28);overflow:hidden;animation:popIn .18s ease;}
    .lesson-week-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.15rem;border-bottom:1px solid var(--border);background:var(--surface);}
    .lesson-week-title{font-size:.98rem;font-weight:800;display:flex;align-items:center;gap:.5rem;color:var(--text);}
    .lesson-week-title i{color:var(--primary);}
    .lesson-week-close{width:30px;height:30px;border:none;border-radius:8px;background:var(--bg);color:var(--text-muted);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all var(--trans);}
    .lesson-week-close:hover{background:#fdecea;color:var(--danger);}
    .lesson-week-body{padding:1rem 1.15rem 1.05rem;}
    .lesson-week-summary{display:flex;align-items:center;gap:.45rem;min-height:38px;padding:.5rem .7rem;border:1.5px solid var(--primary-mid);border-radius:10px;background:var(--primary-light);color:var(--primary-dark);font-size:.82rem;font-weight:800;margin-bottom:.8rem;}
    .lesson-week-note{font-size:.72rem;color:var(--text-muted);line-height:1.45;margin-bottom:.75rem;}
    .lesson-week-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.48rem;}
    .lesson-week-cell{aspect-ratio:1/1;border:1.5px solid var(--border);border-radius:12px;background:var(--bg);color:var(--text);font-size:.92rem;font-weight:800;font-family:inherit;cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center;position:relative;}
    .lesson-week-cell:hover:not(:disabled){border-color:var(--primary);background:var(--primary-light);color:var(--primary);}
    .lesson-week-cell.selected{border-color:var(--primary);background:var(--primary);color:#fff;box-shadow:0 5px 16px rgba(26,158,120,.28);}
    .lesson-week-cell.occupied:not(.selected){background:#f3f4f6;color:#9ca3af;border-style:dashed;cursor:not-allowed;}
    .lesson-week-cell.occupied:not(.selected)::after{content:'';position:absolute;width:6px;height:6px;border-radius:50%;background:#cbd5e1;right:7px;top:7px;}
    .lesson-week-foot{display:flex;align-items:center;justify-content:space-between;gap:.55rem;padding:.85rem 1.15rem;border-top:1px solid var(--border);background:var(--bg);}
    .lesson-week-foot-left{display:flex;gap:.45rem;flex-wrap:wrap;}
    @media(max-width:520px){.lesson-week-grid{grid-template-columns:repeat(4,minmax(0,1fr));}.lesson-week-foot{align-items:stretch;flex-direction:column;}.lesson-week-foot-left{display:grid;grid-template-columns:1fr 1fr;}.lesson-week-foot .btn{justify-content:center;}}

    .ar-date-backdrop{display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,.46);align-items:center;justify-content:center;padding:1rem;}
    .ar-date-backdrop.show{display:flex;}
    .ar-date-modal{width:min(470px,calc(100vw - 2rem));background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:0 26px 80px rgba(0,0,0,.28);overflow:hidden;animation:popIn .18s ease;}
    .ar-date-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.15rem;border-bottom:1px solid var(--border);}
    .ar-date-title{font-size:.98rem;font-weight:800;display:flex;align-items:center;gap:.5rem;color:var(--text);}
    .ar-date-title i{color:var(--primary);}
    .ar-date-close{width:30px;height:30px;border:none;border-radius:8px;background:var(--bg);color:var(--text-muted);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all var(--trans);}
    .ar-date-close:hover{background:#fdecea;color:var(--danger);}
    .ar-date-body{padding:1rem 1.15rem 1.05rem;}
    .ar-date-summary{display:flex;align-items:center;gap:.45rem;min-height:38px;padding:.5rem .7rem;border:1.5px solid var(--primary-mid);border-radius:10px;background:var(--primary-light);color:var(--primary-dark);font-size:.82rem;font-weight:800;margin-bottom:.8rem;}
    .ar-date-nav{display:grid;grid-template-columns:36px 1fr 36px;align-items:center;gap:.6rem;margin-bottom:.7rem;}
    .ar-date-month{text-align:center;font-size:.9rem;font-weight:900;color:var(--text);}
    .ar-date-nav-btn{width:36px;height:36px;border:1.5px solid var(--border);border-radius:10px;background:var(--bg);color:var(--text);cursor:pointer;transition:all .16s;}
    .ar-date-nav-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .ar-date-weekdays,.ar-date-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:.35rem;}
    .ar-date-weekdays{margin-bottom:.35rem;}
    .ar-date-weekdays span{text-align:center;font-size:.66rem;font-weight:900;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);}
    .ar-date-cell{aspect-ratio:1/1;border:1.5px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);font-size:.82rem;font-weight:900;font-family:inherit;cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center;}
    .ar-date-cell:hover:not(:disabled){border-color:var(--primary);background:var(--primary-light);color:var(--primary);}
    .ar-date-cell:disabled{opacity:.32;cursor:default;background:var(--bg);}
    .ar-date-cell.is-start,.ar-date-cell.is-end{border-color:var(--primary);background:var(--primary);color:#fff;box-shadow:0 5px 16px rgba(26,158,120,.25);}
    .ar-date-cell.is-range{border-color:#b8deca;background:#dff3e8;color:#145c38;}
    .ar-date-foot{display:flex;align-items:center;justify-content:space-between;gap:.55rem;padding:.85rem 1.15rem;border-top:1px solid var(--border);background:var(--bg);}
    .ar-date-foot-left{display:flex;gap:.45rem;flex-wrap:wrap;}
    @media(max-width:520px){.ar-date-field-wrap{flex-direction:column;}.ar-date-pick-btn{width:100%;height:42px;}.ar-date-foot{align-items:stretch;flex-direction:column;}.ar-date-foot-left{display:grid;grid-template-columns:1fr 1fr;}.ar-date-foot .btn{justify-content:center;}}

    .tab-section{display:none;}.tab-section.active{display:block;}
    .pending-banner{background:#fff3e0;border:1px solid #ffe0b2;border-radius:10px;padding:.75rem 1rem;display:flex;align-items:center;gap:.6rem;margin-bottom:1rem;font-size:.85rem;color:#e65100;}
    .pending-banner i{font-size:1rem;flex-shrink:0;}
    .spin{display:inline-block;width:14px;height:14px;border:2.5px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:sp .65s linear infinite;}
    @keyframes sp{to{transform:rotate(360deg);}}
    @keyframes shimmer{to{background-position:-200% 0;}}
    @media(max-width:768px){
      .gc-type-nav{width:60px;}
      .gc-type-nav-label{display:none;}
      .gc-type-nav-item span{display:none;}
      .gc-type-nav-item{justify-content:center;padding:.6rem;}
      .gc-type-nav-add span{display:none;}
      .gc-type-nav-add{justify-content:center;padding:.6rem;}
      .gc-settings-panel{width:220px;}
      .gc-form-area{padding:1.25rem 1rem;}
      .gc-row{grid-template-columns:1fr;}
    }
    @media(max-width:600px){
      .pm-row{grid-template-columns:1fr;}
      .tabs-bar{overflow-x:auto;}
      .fv-side-panel{width:270px;}
      .gc-settings-panel{display:none;}
    }

        /* ══════════════════════════════════════════
       ATTENDANCE TAB
    ══════════════════════════════════════════ */
    .att-card{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
    .att-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.1rem 1.25rem .85rem;border-bottom:1px solid var(--border);flex-wrap:wrap;}
    .att-title{font-size:1.05rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:.55rem;}
    .att-title i{color:var(--primary);}
    .att-subtitle{font-size:.74rem;color:var(--text-muted);margin-top:.2rem;}
    .att-export-group{display:flex;gap:.45rem;flex-shrink:0;}
    .att-export-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.42rem .85rem;border-radius:8px;background:var(--bg);border:1.5px solid var(--border);color:var(--text-muted);font-family:inherit;font-size:.78rem;font-weight:600;cursor:pointer;transition:all var(--trans);}
    .att-export-btn:hover{background:var(--primary-light);color:var(--primary);border-color:var(--primary);}
    .att-export-btn i{font-size:.95rem;}

    .att-toolbar{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.85rem 1.25rem;border-bottom:1px solid var(--border);background:var(--bg);flex-wrap:wrap;}
    .att-nav{display:flex;align-items:center;gap:.5rem;}
    .att-nav-btn{width:32px;height:32px;border-radius:8px;border:1.5px solid var(--border);background:var(--surface);color:var(--text-muted);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all var(--trans);font-size:.78rem;}
    .att-nav-btn:hover:not(:disabled){background:var(--primary-light);color:var(--primary);border-color:var(--primary);}
    .att-nav-btn:disabled{opacity:.35;cursor:not-allowed;}
    .att-month-label{font-size:.95rem;font-weight:700;color:var(--text);min-width:160px;text-align:center;}
    .att-toolbar-right{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
    .att-today-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.42rem .85rem;border-radius:20px;background:var(--primary);color:#fff;border:none;font-family:inherit;font-size:.76rem;font-weight:700;cursor:pointer;transition:all var(--trans);box-shadow:0 2px 8px rgba(26,158,120,.3);}
    .att-today-btn:hover{background:var(--primary-dark);transform:translateY(-1px);}
    .att-search-wrap{position:relative;}
    .att-search-icon{position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.75rem;pointer-events:none;}
    .att-search-input{padding:.42rem .85rem .42rem 2rem;border:1.5px solid var(--border);border-radius:20px;font-size:.78rem;font-family:inherit;background:var(--surface);color:var(--text);width:160px;transition:all var(--trans);}
    .att-search-input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);}
    .att-search-input.invalid{border-color:var(--danger);box-shadow:0 0 0 3px rgba(217,48,37,.15);}
    .gb-info-banner{
      display:flex;align-items:flex-start;gap:.55rem;
      background:#e6f1fb;border:1px solid rgba(31,115,219,.22);
      border-radius:10px;padding:.62rem .85rem;margin:0 0 .75rem 0;
      font-size:.78rem;color:#1f73db;
    }
    .gb-info-banner i{margin-top:.06rem;flex-shrink:0;}
    .gb-sheet-head{border:1px solid var(--border);border-radius:12px;background:var(--surface);overflow:hidden;margin:0 0 .8rem 0;}
    .gb-school-name{text-align:center;font-weight:900;font-size:.98rem;letter-spacing:.02em;padding:.75rem 1rem .2rem;color:var(--text);}
    .gb-school-address{text-align:center;font-size:.74rem;color:var(--text-muted);padding:0 1rem .7rem;border-bottom:1px solid var(--border);}
    .gb-meta-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));border-bottom:1px solid var(--border);}
    .gb-meta-grid>div{min-height:34px;display:flex;align-items:center;gap:.45rem;padding:.42rem .65rem;border-right:1px solid var(--border);border-bottom:1px solid var(--border);font-size:.73rem;min-width:0;}
    .gb-meta-grid>div:nth-child(4n){border-right:0;}
    .gb-meta-grid>div:nth-last-child(-n+4){border-bottom:0;}
    .gb-meta-grid span{color:var(--text-muted);font-weight:800;white-space:nowrap;}
    .gb-meta-grid strong{color:var(--text);font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .gb-sheet-status{display:flex;flex-wrap:wrap;gap:.5rem;padding:.55rem .65rem;background:var(--bg);}
    .gb-sheet-status span{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .58rem;border:1px solid var(--border);border-radius:999px;background:var(--surface);font-size:.72rem;color:var(--text-muted);font-weight:700;}
    .gb-sheet-status strong{color:var(--primary);font-family:'DM Mono',monospace;}
    .summary-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.75rem;margin-bottom:1rem;}
    .scard{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.9rem 1rem;box-shadow:var(--shadow);}
    .scard-val{font-size:1.4rem;font-weight:800;font-family:'DM Mono',monospace;line-height:1;}
    .scard-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-top:.3rem;}
    .scard-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.85rem;margin-bottom:.5rem;}
    .icon-green{background:var(--primary-light);color:var(--primary-dark);}
    .icon-blue{background:#e6f1fb;color:#1f73db;}
    .icon-amber{background:#fff8e8;color:#92400e;}
    .icon-red{background:#fdecea;color:#d93025;}
    .filter-row{display:flex;align-items:center;gap:.5rem;margin-bottom:.9rem;flex-wrap:wrap;}
    .search-wrap{position:relative;flex:1;min-width:180px;}
    .search-wrap i{position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;}
    .search-inp{width:100%;padding:.5rem .8rem .5rem 2.1rem;border:1.5px solid var(--border);border-radius:8px;background:var(--surface);font-family:inherit;font-size:.82rem;color:var(--text);outline:none;transition:border-color .15s;}
    .search-inp:focus{border-color:var(--primary);}
    .filter-chips{display:flex;gap:.35rem;flex-wrap:wrap;}
    .fchip{padding:.3rem .8rem;border-radius:999px;font-size:.72rem;font-weight:700;border:1.5px solid var(--border);background:var(--surface);color:var(--text-muted);cursor:pointer;transition:all .15s;white-space:nowrap;}
    .fchip:hover{border-color:var(--primary);color:var(--primary);}
    .fchip.active{background:var(--primary);border-color:var(--primary-dark);color:#fff;}
    .fchip-all{border-color:#7dd3c0;color:#0f766e;background:#f0fdfa;}
    .fchip-quiz{border-color:#fcd34d;color:#92400e;background:#fffbeb;}
    .fchip-activities{border-color:#86efac;color:#166534;background:#f0fdf4;}
    .fchip-assignment{border-color:#fdba74;color:#9a3412;background:#fff7ed;}
    .fchip-exam{border-color:#fca5a5;color:#b91c1c;background:#fef2f2;}
    .fchip-score{border-color:#7dd3c0;color:#0f766e;background:#f0fdfa;}
    .fchip-all.active{background:#1a9e78;border-color:#0f6e56;color:#fff;}
    .fchip-quiz.active{background:#f59e0b;border-color:#d97706;color:#fff;}
    .fchip-activities.active{background:#16a34a;border-color:#15803d;color:#fff;}
    .fchip-assignment.active{background:#d97706;border-color:#b45309;color:#fff;}
    .fchip-exam.active{background:#dc2626;border-color:#b91c1c;color:#fff;}
    .fchip-score.active{background:#1a9e78;border-color:#0d7a5e;color:#fff;}
    .toggle-group{display:flex;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;margin-left:auto;}
    .toggle-btn{padding:.35rem .75rem;border:none;background:var(--surface);font-family:inherit;font-size:.75rem;font-weight:700;color:var(--text-muted);cursor:pointer;transition:all .15s;}
    .toggle-btn.active{background:var(--primary);color:#fff;}
    .table-scroll{overflow:auto;border:1px solid var(--border);border-radius:12px;background:var(--surface);max-height:70vh;}
    #tab-grades .table-scroll table{
      width:max-content;
      min-width:100%;
      table-layout:auto;
      border-collapse:separate;
      border-spacing:0;
    }
    #tab-grades .table-scroll th,
    #tab-grades .table-scroll td{
      white-space:nowrap;
      word-break:normal;
      border-right:1px solid var(--border);
      border-bottom:1px solid var(--border);
    }
    #tab-grades .table-scroll thead th{position:sticky;z-index:7;}
    #tab-grades .table-scroll .row-period th{top:0;}
    #tab-grades .table-scroll .row-group th{top:31px;}
    #tab-grades .table-scroll .row-head th{top:62px;}
    #tab-grades .table-scroll tbody td:nth-child(1),
    #tab-grades .table-scroll thead th.gb-sticky-1{position:sticky;left:0;z-index:8;}
    #tab-grades .table-scroll tbody td:nth-child(2),
    #tab-grades .table-scroll thead th.gb-sticky-2{position:sticky;left:48px;z-index:8;}
    #tab-grades .table-scroll tbody td:nth-child(3),
    #tab-grades .table-scroll thead th.gb-sticky-3{position:sticky;left:166px;z-index:8;}
    #tab-grades .table-scroll tbody td:nth-child(-n+3){background:var(--surface);}
    #tab-grades .table-scroll tbody tr:hover td{background:#f8fbfa;}
    #tab-grades .table-scroll tbody tr:hover td:nth-child(-n+3){background:#f2fbf7;}
    .row-period th,.row-group th{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;padding:.45rem .7rem;text-align:center;user-select:none;}
    .row-period th{background:#e5e7eb;color:#111827;}
    .rp-prelim{background:#e0f2fe!important;color:#075985!important;}
    .rp-midterm{background:#ede9fe!important;color:#5b21b6!important;}
    .rp-finals{background:#dcfce7!important;color:#166534!important;}
    .gh-empty{background:var(--bg);}
    .gh-quiz{background:#fffbeb;color:#92400e;border-color:#fcd34d!important;}
    .gh-activities{background:#dcfce7;color:#166534;}
    .gh-assignment{background:#fef9c3;color:#854d0e;}
    .gh-exam{background:#fef2f2;color:#b91c1c;border-color:#fecaca!important;}
    .gh-attendance{background:#e0f2fe;color:#0369a1;}
    .gh-period,.gh-score,.gh-final{background:var(--primary-light);color:var(--primary-dark);}
    .row-head th{font-size:.68rem;font-weight:800;color:var(--text-muted);padding:.48rem .65rem;background:var(--bg);text-align:center;user-select:none;max-width:160px;overflow:hidden;text-overflow:ellipsis;}
    .row-period th::selection,.row-group th::selection,.row-head th::selection{background:transparent;color:inherit;}
    .row-head th.hh-quiz{background:#fffbeb !important;color:#92400e !important;}
    .row-head th.hh-activities{background:#f0fdf4 !important;color:#166534 !important;}
    .row-head th.hh-assignment{background:#fff7ed !important;color:#9a3412 !important;}
    .row-head th.hh-exam{background:#fef2f2 !important;color:#b91c1c !important;}
    .row-head th.hh-score{background:var(--primary-light) !important;color:var(--primary-dark) !important;}
    .row-head th.hh-recitation{background:#f5f3ff !important;color:#5b21b6 !important;}
    .row-head th.hh-attendance{background:#f0f9ff !important;color:#0369a1 !important;}
    .row-head th.hh-period{background:var(--primary-light)!important;color:var(--primary-dark)!important;}
    .row-group th.hh-quiz{background:#fffbeb !important;color:#92400e !important;}
    .row-group th.hh-activities{background:#f0fdf4 !important;color:#166534 !important;}
    .row-group th.hh-assignment{background:#fff7ed !important;color:#9a3412 !important;}
    .row-group th.hh-exam{background:#fef2f2 !important;color:#b91c1c !important;}
    .row-group th.hh-score{background:var(--primary-light) !important;color:var(--primary-dark) !important;}
    .row-group th.hh-recitation{background:#f5f3ff !important;color:#5b21b6 !important;}
    .row-group th.hh-attendance{background:#f0f9ff !important;color:#0369a1 !important;}
    .row-group th.hh-period{background:var(--primary-light)!important;color:var(--primary-dark)!important;}
    .cell-pad{padding:.55rem .65rem;font-size:.78rem;}
    .gb-col-no{width:48px;min-width:48px;}
    .gb-col-id{width:118px;min-width:118px;}
    .gb-col-name{width:220px;min-width:220px;text-align:left!important;}
    .gb-col-reg{width:120px;min-width:120px;}
    .gb-col-small{width:82px;min-width:82px;}
    .gb-col-assessment{width:118px;min-width:118px;}
    .gb-col-summary{width:94px;min-width:94px;}
    .gb-name-cell{font-weight:800;max-width:220px;overflow:hidden;text-overflow:ellipsis;}
    .score-wrap{display:flex;align-items:center;justify-content:center;padding:.5rem .6rem;min-height:38px;}
    .score-miss{color:var(--text-muted);font-size:.72rem;font-style:italic;}
    .score-val{font-family:'DM Mono',monospace;font-weight:600;font-size:.82rem;}
    .score-max{font-size:.65rem;color:var(--text-muted);font-weight:400;margin-left:1px;}
    .s-perfect{color:#166534;}.s-good{color:#1e40af;}.s-avg{color:#854d0e;}.s-low{color:#d93025;}
    .score-cell-btn{display:flex;align-items:center;justify-content:center;width:100%;min-height:38px;padding:.5rem .6rem;cursor:pointer;gap:.35rem;}
    .view-icon{opacity:0;font-size:.7rem;color:#1f73db;transition:opacity .15s;}
    .score-cell-btn:hover .view-icon{opacity:1;}
    .final-wrap{padding:.5rem .6rem;text-align:center;}
    .final-val{font-family:'DM Mono',monospace;font-size:.88rem;font-weight:700;}
    .fv-high{color:#166534;}.fv-mid{color:#854d0e;}.fv-low{color:#d93025;}.fv-none{color:var(--text-muted);}
    .gb-equivalent{font-family:'DM Mono',monospace;font-weight:800;color:#374151;}
    .cluster-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.18rem .6rem;border-radius:999px;font-size:.68rem;font-weight:700;white-space:nowrap;}
    .cb-high{background:#dcfce7;color:#166534;border:1px solid #86efac;}
    .cb-avg{background:#fef9c3;color:#854d0e;border:1px solid #fde047;}
    .cb-risk{background:#fdecea;color:#d93025;border:1px solid #fca5a5;}

    .att-calendar{padding:.9rem 1rem 1rem;}
    .att-weekdays{display:grid;grid-template-columns:repeat(7,1fr);gap:.35rem;margin-bottom:.4rem;}
    .att-weekdays>div{text-align:center;font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;padding:.25rem 0;}
    .att-days{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:.35rem;}

    .att-day{position:relative;min-height:92px;background:var(--surface);border:1.5px solid var(--border);border-radius:12px;display:flex;flex-direction:column;align-items:flex-start;justify-content:space-between;padding:.7rem .72rem .65rem;font-size:.9rem;font-weight:600;color:var(--text);cursor:pointer;transition:all var(--trans);user-select:none;overflow:visible;}
    .att-day:hover:not(.att-day-disabled):not(.att-day-empty){border-color:var(--primary);transform:translateY(-2px);box-shadow:var(--shadow-md);}
    .att-day-head{display:flex;align-items:flex-start;justify-content:space-between;width:100%;}
    .att-day-num{font-size:1.12rem;line-height:1;font-weight:800;letter-spacing:-.03em;}
    .att-day-foot{width:100%;display:flex;align-items:flex-end;justify-content:flex-start;gap:.35rem;margin-top:.55rem;}
    .att-day-empty{color:var(--text-muted);opacity:.25;cursor:default;background:transparent;border-color:transparent;}
    .att-day-disabled{color:var(--text-muted);opacity:.4;cursor:not-allowed;background:repeating-linear-gradient(45deg,var(--surface),var(--surface) 4px,var(--bg) 4px,var(--bg) 8px);}
    .att-day-disabled:hover{transform:none;box-shadow:none;border-color:var(--border);}
    .att-day-today{border-color:var(--primary);border-width:2px;font-weight:700;}
    .att-day-today .att-day-num{color:var(--primary);}

    /* Recorded states (color tiers) */
    .att-day-recorded{color:#fff;border:none;}
    .att-day-recorded .att-day-num{color:#fff;font-weight:700;}
    .att-day-high{background:linear-gradient(135deg,#1a9e78,#0d7a5e);box-shadow:0 2px 10px rgba(26,158,120,.35);}
    .att-day-mid{background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 2px 10px rgba(245,158,11,.35);}
    .att-day-low{background:linear-gradient(135deg,#ef4444,#b91c1c);box-shadow:0 2px 10px rgba(239,68,68,.35);}
    .att-day-pct{display:inline-flex;align-items:center;justify-content:center;min-width:42px;max-width:100%;gap:.28rem;font-size:.68rem;font-weight:800;padding:.2rem .46rem;border-radius:999px;background:rgba(255,255,255,.18);color:inherit;white-space:nowrap;}
    .att-day-recorded .att-day-pct{background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.12);}

    /* Hover tooltip */
    .att-tooltip{position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%);background:#1c2027;color:#fff;font-size:.72rem;font-weight:500;padding:.5rem .75rem;border-radius:8px;white-space:nowrap;z-index:50;pointer-events:none;box-shadow:0 4px 16px rgba(0,0,0,.3);opacity:0;transition:opacity .15s;}
    .att-tooltip::after{content:'';position:absolute;top:100%;left:50%;transform:translateX(-50%);border:5px solid transparent;border-top-color:#1c2027;}
    .att-day:hover .att-tooltip{opacity:1;}

    /* Search highlight pulse */
    @keyframes attSearchPulse{0%,100%{transform:scale(1);}50%{transform:scale(1.12);box-shadow:0 0 0 6px var(--primary-mid);}}
    .att-day-searched{animation:attSearchPulse 1s ease 2;}

    /* Sidebar legend & stats */
    .att-legend{display:flex;flex-direction:column;gap:.55rem;}
    .att-legend-row{display:flex;align-items:center;gap:.6rem;font-size:.78rem;color:var(--text);}
    .att-dot{width:14px;height:14px;border-radius:4px;flex-shrink:0;}
    .att-dot-high{background:linear-gradient(135deg,#1a9e78,#0d7a5e);}
    .att-dot-mid{background:linear-gradient(135deg,#f59e0b,#d97706);}
    .att-dot-low{background:linear-gradient(135deg,#ef4444,#b91c1c);}
    .att-dot-none{background:var(--bg);border:1.5px solid var(--border);}
    .att-stats{display:flex;flex-direction:column;gap:.6rem;}
    .att-stat-row{display:flex;align-items:center;justify-content:space-between;font-size:.78rem;}
    .att-stat-label{color:var(--text-muted);}
    .att-stat-value{font-weight:700;color:var(--primary);font-size:.95rem;}
    #tab-report .att-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.55rem;}
    #tab-report .att-stat-row{
      flex-direction:column;
      align-items:flex-start;
      justify-content:flex-start;
      gap:.28rem;
      padding:.72rem .78rem;
      border-radius:12px;
      background:var(--bg);
      border:1px solid var(--border);
    }
    #tab-report .att-stat-value{font-size:1.02rem;line-height:1.1;}
    #tab-report #attMonthBestDay,
    #tab-report #attMonthLowestDay{font-size:.82rem;line-height:1.35;letter-spacing:0;}
    .att-overview-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;}
    .att-overview-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:.9rem .95rem;box-shadow:var(--shadow);}
    .att-overview-label{font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.45rem;}
    .att-overview-value{font-size:1.45rem;font-weight:800;color:var(--text);line-height:1;letter-spacing:-.04em;}
    .att-overview-value.good{color:var(--primary);}
    .att-overview-value.warn{color:#d97706;}
    .att-overview-value.risk{color:var(--danger);}
    .att-overview-meta{font-size:.72rem;color:var(--text-muted);margin-top:.38rem;line-height:1.4;}
    .att-side-sub{font-size:.73rem;color:var(--text-muted);margin:-.3rem 0 .8rem;line-height:1.5;}
    .att-student-bars,.att-absence-list{display:grid;gap:.8rem;}
    .att-student-bar{padding:.72rem .78rem;border-radius:12px;background:var(--bg);border:1px solid var(--border);}
    .att-student-bar-head{display:flex;align-items:flex-start;justify-content:space-between;gap:.65rem;margin-bottom:.45rem;}
    .att-student-name-wrap{min-width:0;}
    .att-student-bar-name{font-size:.8rem;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .att-student-bar-meta{font-size:.69rem;color:var(--text-muted);margin-top:.08rem;}
    .att-student-bar-value{font-size:.92rem;font-weight:800;color:var(--text);white-space:nowrap;}
    .att-student-track{width:100%;height:9px;border-radius:999px;background:var(--surface);overflow:hidden;border:1px solid var(--border);}
    .att-student-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--primary),var(--accent));}
    .att-student-fill.is-mid{background:linear-gradient(90deg,#f59e0b,#d97706);}
    .att-student-fill.is-low{background:linear-gradient(90deg,#ef4444,#b91c1c);}
    .att-absence-row{display:flex;align-items:center;justify-content:space-between;gap:.7rem;padding:.72rem .78rem;border-radius:12px;background:var(--bg);border:1px solid var(--border);}
    .att-absence-main{min-width:0;}
    .att-absence-name{font-size:.8rem;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .att-absence-meta{font-size:.69rem;color:var(--text-muted);margin-top:.08rem;}
    .att-absence-badge{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 .55rem;border-radius:10px;background:#fdecea;color:var(--danger);font-size:.9rem;font-weight:800;border:1px solid rgba(217,48,37,.18);flex-shrink:0;}
    .att-empty-state{text-align:center;padding:1rem .75rem;font-size:.78rem;color:var(--text-muted);border:1px dashed var(--border);border-radius:12px;background:var(--bg);}
    body.dark .att-absence-badge{background:rgba(217,48,37,.16);}

    /* Report subviews */
    .report-shell{max-width:var(--class-max);margin:0 auto;display:grid;grid-template-columns:208px minmax(0,1fr);gap:0;align-items:start;}
    .report-step-sidebar{position:sticky;top:calc(var(--nav-h) + 58px);align-self:start;border-right:1px solid var(--border);background:var(--surface);padding:1rem .5rem;min-height:calc(100vh - var(--nav-h) - 126px);}
    .report-step-kicker{font-size:.64rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);padding:.15rem .35rem .55rem;}
    .report-view-toggle{display:grid;gap:.3rem;}
    .report-view-btn{width:100%;border:none;background:transparent;color:var(--text-muted);font-family:inherit;font-size:.78rem;font-weight:800;padding:.62rem .58rem;cursor:pointer;display:grid;grid-template-columns:28px minmax(0,1fr) 20px;align-items:center;gap:.55rem;transition:all var(--trans);text-align:left;border-radius:10px;}
    .report-view-btn:hover{color:var(--text);background:var(--bg);}
    .report-view-btn.active{background:var(--primary-light);color:var(--primary);}
    .report-view-btn .report-step-icon{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:var(--bg);color:var(--text-muted);font-size:.76rem;}
    .report-view-btn.active .report-step-icon{background:var(--primary);color:#fff;}
    .report-step-copy{min-width:0;}
    .report-step-label{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.15;}
    .report-step-sub{display:block;font-size:.65rem;font-weight:700;color:var(--text-muted);margin-top:.12rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .report-step-num{display:inline-flex;align-items:center;justify-content:center;height:18px;min-width:18px;border-radius:6px;background:var(--bg);color:var(--text-muted);font-size:.6rem;font-weight:900;}
    .report-view-btn.active .report-step-num{background:rgba(26,158,120,.16);color:var(--primary);}
    .report-step-note{margin:.75rem .35rem .1rem;padding-top:.7rem;border-top:1px solid var(--border);font-size:.67rem;line-height:1.45;color:var(--text-muted);}
    .report-main-panel{min-width:0;}
    .report-switchbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;}
    .report-title{font-size:1.05rem;font-weight:800;color:var(--text);display:flex;align-items:center;gap:.5rem;}
    .report-title i{color:#1a6b3c;}
    .report-subtitle{font-size:.78rem;color:var(--text-muted);margin-top:.15rem;}
    .report-view{display:none;}
    .report-view.active{display:block;}
    .report-attendance-layout{max-width:1280px;}
    .att-log-btn{display:inline-flex;align-items:center;gap:.42rem;padding:.5rem .9rem;border-radius:10px;border:1.5px solid #1a6b3c;background:#1a6b3c;color:#fff;font-family:inherit;font-size:.78rem;font-weight:800;cursor:pointer;transition:all var(--trans);box-shadow:0 2px 10px rgba(26,107,60,.24);}
    .att-log-btn:hover{background:#155c33;transform:translateY(-1px);}

    .ra-grid{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);gap:1rem;align-items:start;}
    .ra-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);padding:1rem;}
    .ra-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;}
    .ra-title{font-size:1rem;font-weight:800;color:var(--text);display:flex;align-items:center;gap:.5rem;}
    .ra-title i{color:var(--primary);}
    .ra-sub{font-size:.75rem;color:var(--text-muted);margin-top:.15rem;}
    .ra-ai-pill{display:inline-flex;align-items:center;gap:.38rem;border-radius:10px;background:var(--primary-light);color:var(--primary);font-size:.72rem;font-weight:900;padding:.45rem .7rem;}
    .ra-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.7rem;margin-bottom:1rem;}
    .ra-summary-card{border:1px solid var(--border);background:var(--surface);border-radius:12px;padding:.8rem .85rem;}
    .ra-summary-label{font-size:.64rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);}
    .ra-summary-value{font-size:1.35rem;font-weight:900;line-height:1.1;margin-top:.25rem;}
    .ra-summary-sub{font-size:.68rem;color:var(--text-muted);margin-top:.1rem;}
    .ra-scatter{position:relative;height:360px;border:1px solid var(--border);border-radius:14px;background:linear-gradient(to right,rgba(95,99,104,.12) 1px,transparent 1px),linear-gradient(to top,rgba(95,99,104,.12) 1px,transparent 1px),var(--bg);background-size:20% 100%,100% 20%;overflow:hidden;padding:1rem;}
    .ra-axis-label{position:absolute;font-size:.67rem;font-weight:800;color:var(--text-muted);letter-spacing:.04em;text-transform:uppercase;}
    .ra-axis-x{left:50%;bottom:.55rem;transform:translateX(-50%);}
    .ra-axis-y{left:.55rem;top:50%;transform:translateY(-50%) rotate(-90deg);transform-origin:center;}
    .ra-dot{position:absolute;width:13px;height:13px;border-radius:50%;border:2px solid var(--surface);box-shadow:0 2px 8px rgba(0,0,0,.18);transform:translate(-50%,50%);cursor:pointer;transition:transform .16s,box-shadow .16s;}
    .ra-dot:hover{transform:translate(-50%,50%) scale(1.55);box-shadow:0 4px 12px rgba(0,0,0,.24);z-index:3;}
    .ra-high{background:#22c55e;}
    .ra-average{background:#f59e0b;}
    .ra-risk{background:#ef4444;}
    .ra-legend{display:flex;align-items:center;justify-content:center;gap:.85rem;flex-wrap:wrap;margin-top:.75rem;font-size:.72rem;color:var(--text-muted);font-weight:700;}
    .ra-legend-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:.3rem;vertical-align:-1px;}
    .ra-clusters{display:grid;gap:.8rem;}
    .ra-cluster-card{border:1px solid var(--border);border-radius:12px;background:var(--bg);padding:.82rem;}
    .ra-cluster-head{display:flex;align-items:center;justify-content:space-between;gap:.65rem;margin-bottom:.58rem;}
    .ra-cluster-label{font-size:.78rem;font-weight:900;color:var(--text);display:flex;align-items:center;gap:.45rem;}
    .ra-cluster-count{font-family:'DM Mono',monospace;font-size:.76rem;font-weight:900;color:var(--text-muted);}
    .ra-student-list{display:grid;gap:.5rem;}
    .ra-student-row{border:1px solid var(--border);border-radius:10px;background:var(--surface);padding:.58rem .65rem;}
    .ra-student-name{font-size:.78rem;font-weight:800;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .ra-student-meta{font-size:.68rem;color:var(--text-muted);margin-top:.18rem;display:flex;gap:.55rem;flex-wrap:wrap;}
    .ra-mini-track{height:6px;border-radius:99px;background:var(--bg);border:1px solid var(--border);overflow:hidden;margin-top:.4rem;}
    .ra-mini-fill{height:100%;border-radius:99px;}
    .ra-table{width:100%;border-collapse:collapse;font-size:.78rem;margin-top:1rem;overflow:hidden;border-radius:12px;}
    .ra-table th,.ra-table td{border-bottom:1px solid var(--border);padding:.65rem .7rem;text-align:left;}
    .ra-table th{font-size:.65rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);background:var(--bg);}
    .ra-empty{padding:2.2rem 1rem;text-align:center;color:var(--text-muted);font-size:.82rem;border:1px dashed var(--border);border-radius:12px;background:var(--bg);}

    .ar-form-grid{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(340px,.95fr);gap:1rem;align-items:start;}
    .ar-wizard{display:grid;gap:1rem;}
    .ar-wizard-head{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:1rem 1.05rem;box-shadow:var(--shadow);display:grid;gap:.85rem;}
    .ar-wizard-top{display:flex;align-items:center;justify-content:space-between;gap:1rem;}
    .ar-step-kicker{font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:var(--primary);}
    .ar-wizard-title{font-size:1rem;font-weight:900;color:var(--text);margin-top:.12rem;}
    .ar-wizard-count{font-family:'DM Mono',monospace;font-size:.8rem;font-weight:900;color:var(--primary);background:var(--primary-light);border:1px solid var(--primary-mid);border-radius:999px;padding:.32rem .62rem;white-space:nowrap;}
    .ar-progress-track{height:7px;background:var(--bg);border:1px solid var(--border);border-radius:999px;overflow:hidden;}
    .ar-progress-track span{display:block;height:100%;width:25%;background:linear-gradient(90deg,var(--primary),var(--primary-dark));border-radius:inherit;transition:width .22s ease;}
    .ar-step-list{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.55rem;}
    .ar-step-pill{border:1px solid var(--border);background:var(--surface);color:var(--text-muted);border-radius:12px;padding:.58rem .6rem;display:flex;align-items:center;gap:.48rem;font-family:inherit;font-size:.75rem;font-weight:900;cursor:pointer;transition:all .16s;min-width:0;}
    .ar-step-pill span{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--bg);color:var(--text-muted);font-family:'DM Mono',monospace;font-size:.72rem;flex-shrink:0;}
    .ar-step-pill b{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .ar-step-pill:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .ar-step-pill.is-active{border-color:var(--primary);background:var(--primary-light);color:var(--primary);}
    .ar-step-pill.is-active span{background:var(--primary);color:#fff;}
    .ar-step-pill.is-done{border-color:#b8deca;color:#1a6b3c;}
    .ar-step-pill.is-done span{background:#d6eee0;color:#1a6b3c;}
    .ar-wizard-step{display:none;}
    .ar-wizard-step.is-active{display:block;}
    .ar-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:1rem 1.05rem;box-shadow:var(--shadow);}
    .ar-card-title{font-size:.72rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);padding-bottom:.55rem;margin-bottom:.8rem;border-bottom:1px solid var(--border);}
    .ar-title-spaced{margin-top:1rem;}
    .ar-field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.72rem;}
    .ar-field{display:flex;flex-direction:column;gap:.32rem;min-width:0;}
    .ar-field span{font-size:.72rem;font-weight:700;color:var(--text-muted);}
    .ar-field-full{margin-top:.72rem;}
    .ar-input{width:100%;border:1.5px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);font-family:inherit;font-size:.82rem;padding:.62rem .72rem;outline:none;transition:border-color .16s,box-shadow .16s,background .16s;}
    .ar-input:focus{border-color:#1a6b3c;box-shadow:0 0 0 3px rgba(26,107,60,.12);}
    .ar-input[readonly]{background:var(--bg);color:var(--text-muted);font-weight:700;}
    .ar-input.ar-week-picker-input[readonly]{background:var(--surface);color:var(--text);cursor:pointer;}
    .ar-input.ar-week-picker-input[readonly]:hover{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);}
    .ar-input.ar-date-range-input[readonly]{background:var(--surface);color:var(--text);cursor:pointer;}
    .ar-input.ar-date-range-input[readonly]:hover{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);}
    .ar-date-field-wrap{display:flex;align-items:stretch;gap:.45rem;}
    .ar-date-field-wrap .ar-input{min-width:0;flex:1;}
    .ar-date-pick-btn{width:44px;border:1.5px solid var(--border);border-radius:10px;background:var(--bg);color:var(--primary);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .16s;}
    .ar-date-pick-btn:hover{border-color:var(--primary);background:var(--primary-light);}
    textarea.ar-input{resize:vertical;min-height:78px;line-height:1.45;}
    .ar-photo-upload{display:flex;align-items:center;justify-content:center;min-height:180px;border:1.5px dashed var(--border-strong);border-radius:12px;background:var(--bg);cursor:pointer;overflow:hidden;transition:border-color .16s,box-shadow .16s,background .16s;}
    .ar-photo-upload:hover{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);background:var(--surface);}
    .ar-photo-empty{text-align:center;color:var(--text-muted);font-size:.78rem;line-height:1.45;padding:1rem;}
    .ar-photo-empty i{display:block;font-size:1.4rem;color:var(--primary);margin-bottom:.45rem;}
    .ar-photo-preview{display:none;width:100%;max-height:260px;object-fit:contain;background:#fff;}
    .ar-photo-actions{display:flex;align-items:center;justify-content:space-between;gap:.7rem;flex-wrap:wrap;margin-top:.55rem;font-size:.72rem;color:var(--text-muted);}
    .ar-session-note{font-size:.75rem;color:var(--text-muted);line-height:1.45;padding:.65rem .75rem;border:1px dashed var(--border);border-radius:10px;background:var(--bg);margin-top:.65rem;}
    .ar-form-actions{display:flex;align-items:center;justify-content:flex-end;gap:.6rem;flex-wrap:wrap;margin-top:1rem;padding-top:.85rem;border-top:1px solid var(--border);}
    .ar-wizard-actions{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:1rem 1.05rem;margin-top:0;box-shadow:var(--shadow);}
    .ar-save-state{margin-right:auto;font-size:.72rem;color:var(--text-muted);}

    .ar-preview-toolbar{display:flex;align-items:center;justify-content:flex-end;gap:.6rem;margin-bottom:1rem;}
    .ar-print-stack{display:grid;gap:1rem;}
    .ar-print-page{display:block;}
    .ar-page-label{text-align:center;font-size:.7rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.35rem;}
    .ar-paper{width:min(100%,216mm);min-height:279.4mm;margin:0 auto;background:#fff;color:#111;border:1px solid #d7d7d7;border-radius:6px;padding:14mm 25mm 20mm;box-shadow:var(--shadow-md);font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.45;}
    .ar-doc-head{text-align:center;display:grid;gap:1px;margin-bottom:8px;}
    .ar-doc-head strong{font-size:12px;}
    .ar-doc-head span{font-size:10px;}
    .ar-doc-dept{text-align:center;background:#e5efe9;color:#1a6b3c;font-weight:800;padding:4px 6px;margin:7px 0 4px;text-transform:uppercase;border:1px solid #b8d1c1;}
    .ar-doc-title{text-align:center;font-size:12px;font-weight:800;margin:6px 0 8px;}
    .ar-doc-generated{text-align:center;font-size:10px;color:#444;margin:-3px 0 8px;}
    .ar-doc-section{font-size:10px;font-weight:800;margin:7px 0 4px;text-transform:uppercase;}
    .ar-doc-section-soft{background:#f1f5f3;border:1px solid #cfd8d3;padding:3px 5px;}
    .ar-doc-table{width:100%;border-collapse:collapse;margin-bottom:8px;font-size:10px;}
    .ar-doc-table th,.ar-doc-table td{border:1px solid #333;padding:4px 5px;vertical-align:top;text-align:left;}
    .ar-doc-table th{background:#f4f4f4;font-weight:700;width:18%;}
    .ar-doc-box{border:1px solid #333;min-height:44px;padding:5px;margin-bottom:8px;white-space:pre-wrap;}
    .ar-signature-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;font-size:10px;}
    .ar-signature-grid>div{border-top:1px solid #333;padding-top:5px;min-height:52px;}
    .ar-signature-table{width:100%;border-collapse:collapse;table-layout:fixed;margin-top:12px;font-size:10px;}
    .ar-signature-table th,.ar-signature-table td{border:1px solid #333;padding:5px 6px;text-align:left;vertical-align:top;}
    .ar-signature-table th{font-weight:800;background:#fff;text-transform:uppercase;}
    .ar-signature-table td{height:12mm;}
    .ar-signature-line{display:inline-block;min-width:28mm;border-bottom:1px solid #333;line-height:1;}
    .ar-cover-paper{height:279.4mm;display:flex;flex-direction:column;}
    .ar-cover-paper .ar-doc-head{margin-bottom:5px;}
    .ar-cover-paper .ar-doc-dept{margin:8px 0 5px;}
    .ar-cover-paper .ar-doc-title{margin:6px 0 9px;}
    .ar-cover-paper .ar-doc-table{margin-bottom:7px;}
    .ar-cover-paper .ar-doc-section{margin:7px 0 4px;}
    .ar-cover-paper .ar-cover-sync{height:43mm;}
    .ar-cover-paper .ar-cover-fill-box{flex:1 1 0;min-height:24mm;overflow:hidden;}
    .ar-cover-paper .ar-cover-signatures{margin-top:auto;min-height:24mm;}
    .ar-attendance-print th:nth-child(1),.ar-attendance-print th:nth-child(3),.ar-attendance-print td:nth-child(1),.ar-attendance-print td:nth-child(3){width:9%;text-align:center;}
    .ar-photo-label{font-size:10px;font-weight:800;margin-top:12px;}
    .ar-photo-box{height:92mm;border:1px dashed #777;margin-top:5px;display:flex;align-items:center;justify-content:center;text-align:center;color:#777;font-size:11px;overflow:hidden;}
    .ar-photo-box img{display:none;width:100%;height:100%;object-fit:contain;}
    .ar-photo-box.has-photo{border-style:solid;color:transparent;}
    .ar-photo-box.has-photo img{display:block;}
    .ar-doc-footer{font-size:10px;color:#444;margin-top:10px;border-top:1px solid #aaa;padding-top:5px;}
    .ar-cluster-high{color:#166534;font-weight:800;}
    .ar-cluster-average{color:#b45309;font-weight:800;}
    .ar-cluster-risk{color:#dc2626;font-weight:800;}

    /* ── ATTENDANCE MODAL ── */
    .att-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:600;animation:wl-overlay-in .22s ease;}
    .att-modal-overlay.show{display:block;}
    .att-modal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:560px;max-width:96vw;max-height:88vh;background:var(--surface);border-radius:16px;box-shadow:var(--shadow-lg);z-index:601;display:none;flex-direction:column;overflow:hidden;}
    .att-modal.open{display:flex;animation:invChipPop .22s ease;}
    .att-modal-head{padding:1.1rem 1.25rem;border-bottom:1.5px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:.75rem;background:linear-gradient(135deg,var(--primary-light),transparent);flex-shrink:0;}
    .att-modal-title{font-size:1.05rem;font-weight:700;color:var(--text);}
    .att-modal-sub{font-size:.74rem;color:var(--text-muted);margin-top:.18rem;}
    .att-modal-close{width:34px;height:34px;border:none;background:var(--bg);color:var(--text-muted);border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;}
    .att-modal-close:hover{background:#fdecea;color:var(--danger);}

    .att-modal-toolbar{padding:.7rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;background:var(--bg);flex-shrink:0;}
    .att-bulk-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .8rem;border-radius:20px;border:1.5px solid var(--primary);background:var(--surface);color:var(--primary);font-family:inherit;font-size:.74rem;font-weight:600;cursor:pointer;transition:all var(--trans);}
    .att-bulk-btn:hover{background:var(--primary);color:#fff;}
    .att-bulk-btn-danger{border-color:var(--danger);color:var(--danger);}
    .att-bulk-btn-danger:hover{background:var(--danger);color:#fff;}
    .att-modal-counts{margin-left:auto;display:flex;gap:.4rem;}
    .att-pill{display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .65rem;border-radius:20px;font-size:.7rem;font-weight:700;}
    .att-pill-present{background:var(--primary-light);color:var(--primary);}
    .att-pill-absent{background:#fdecea;color:var(--danger);}
    body.dark .att-pill-absent{background:rgba(217,48,37,.18);}

    .att-modal-body{flex:1;overflow-y:auto;padding:.5rem 0;}
    .att-modal-body::-webkit-scrollbar{width:5px;}
    .att-modal-body::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
    .att-student-row{display:flex;align-items:center;gap:.85rem;padding:.7rem 1.25rem;border-bottom:1px solid var(--border);transition:background .12s;}
    .att-student-row:hover{background:var(--bg);}
    .att-student-row:last-child{border-bottom:none;}
    .att-student-av{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.82rem;flex-shrink:0;}
    .att-student-info{flex:1;min-width:0;}
    .att-student-name{font-size:.85rem;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .att-student-meta{font-size:.7rem;color:var(--text-muted);margin-top:.1rem;}

    /* Toggle (Present / Absent) */
    .att-toggle{display:inline-flex;background:var(--bg);border:1.5px solid var(--border);border-radius:20px;padding:2px;flex-shrink:0;}
    .att-toggle-opt{padding:.32rem .85rem;border:none;background:none;font-family:inherit;font-size:.72rem;font-weight:700;color:var(--text-muted);cursor:pointer;border-radius:18px;transition:all var(--trans);display:inline-flex;align-items:center;gap:.3rem;}
    .att-toggle-opt.active.opt-present{background:var(--primary);color:#fff;box-shadow:0 1px 4px rgba(26,158,120,.4);}
    .att-toggle-opt.active.opt-absent{background:var(--danger);color:#fff;box-shadow:0 1px 4px rgba(217,48,37,.4);}

    .att-modal-footer{padding:.85rem 1.25rem;border-top:1.5px solid var(--border);display:flex;justify-content:flex-end;gap:.55rem;background:var(--bg);flex-shrink:0;}

    /* Mobile */
    @media (max-width:768px){
      .gb-meta-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
      .gb-meta-grid>div:nth-child(4n){border-right:1px solid var(--border);}
      .gb-meta-grid>div:nth-child(2n){border-right:0;}
      .gb-meta-grid>div:nth-last-child(-n+4){border-bottom:1px solid var(--border);}
      .gb-meta-grid>div:nth-last-child(-n+2){border-bottom:0;}
      .gb-sheet-status{display:grid;grid-template-columns:1fr;}
      .report-shell{display:block;}
      .report-step-sidebar{position:static;margin-bottom:1rem;}
      .report-view-toggle{grid-template-columns:repeat(3,minmax(0,1fr));}
      .report-view-btn{grid-template-columns:1fr;justify-items:center;text-align:center;gap:.3rem;padding:.65rem .35rem;}
      .report-view-btn .report-step-icon{width:30px;height:30px;}
      .report-step-label{font-size:.7rem;}
      .report-step-sub,.report-step-num,.report-step-note{display:none;}
      .ra-grid{grid-template-columns:1fr;}
      .ra-summary{grid-template-columns:repeat(2,minmax(0,1fr));}
      .ra-scatter{height:300px;}
      #tab-report .cr-layout{grid-template-columns:1fr;}
      #tab-report .cr-side{order:2;}
      .att-overview-grid{grid-template-columns:1fr 1fr;}
      #tab-report .att-stats{grid-template-columns:1fr;}
      .report-switchbar{align-items:flex-start;}
      .ar-form-grid{grid-template-columns:1fr;}
      .ar-wizard-top{align-items:flex-start;}
      .ar-step-list{grid-template-columns:repeat(2,minmax(0,1fr));}
      .ar-step-pill{padding:.55rem;font-size:.72rem;}
      .ar-field-grid,.ar-signature-grid{grid-template-columns:1fr;}
      .ar-form-actions{justify-content:stretch;}
      .ar-form-actions .btn{flex:1;}
      .ar-save-state{width:100%;margin-right:0;}
      .ar-paper{min-height:auto;padding:10mm;}
      .att-modal{width:96vw;max-height:92vh;}
      .att-calendar{padding:.8rem .85rem .9rem;}
      .att-days{gap:.3rem;}
      .att-day{min-height:76px;font-size:.78rem;padding:.55rem .55rem .5rem;}
      .att-day-num{font-size:.98rem;}
      .att-day-pct{font-size:.58rem;}
      .att-search-input{width:130px;}
      .att-month-label{min-width:120px;font-size:.85rem;}
    }

    body.report-preview-mode{overflow:hidden;background:var(--bg);}
    body.report-preview-mode .topbar,
    body.report-preview-mode .class-banner,
    body.report-preview-mode .tabs-bar,
    body.report-preview-mode .wl-bar,
    body.report-preview-mode .report-step-sidebar,
    body.report-preview-mode .report-switchbar{display:none!important;}
    body.report-preview-mode #tab-report{display:block!important;position:fixed;inset:0;z-index:5000;background:var(--bg);overflow:auto;}
    body.report-preview-mode .report-shell{display:block;max-width:none;min-height:100vh;margin:0;padding:0;}
    body.report-preview-mode .report-main-panel{min-height:100vh;padding:0;}
    body.report-preview-mode #reportViewPreview{min-height:100vh;padding:80px clamp(16px,4vw,48px) 42px;}
    body.report-preview-mode .ar-preview-toolbar{position:fixed;top:0;left:0;right:0;z-index:5010;height:64px;margin:0;padding:0 clamp(16px,4vw,48px);background:rgba(255,255,255,.92);border-bottom:1px solid var(--border);box-shadow:0 8px 30px rgba(15,23,42,.08);backdrop-filter:blur(14px);}
    body.report-preview-mode .ar-print-stack{max-width:min(100%,236mm);margin:0 auto;gap:1.25rem;}
    body.report-preview-mode .ar-paper{width:216mm;max-width:100%;}
    body.dark.report-preview-mode .ar-preview-toolbar{background:rgba(24,32,48,.92);}

    /* Dark mode tweaks */
    body.dark .att-day-empty{opacity:.18;}
    body.dark .att-day-disabled{background:repeating-linear-gradient(45deg,var(--surface),var(--surface) 4px,#0a0f1a 4px,#0a0f1a 8px);}
    body.dark .att-modal-head{background:linear-gradient(135deg,rgba(46,204,154,.12),transparent);}

    @media print{
      @page{size:Letter;margin:0;}
      body{background:#fff!important;}
      body.report-preview-mode{overflow:visible!important;}
      body.report-preview-mode #tab-report{position:static!important;inset:auto!important;overflow:visible!important;background:#fff!important;}
      body.report-preview-mode .report-shell,
      body.report-preview-mode .report-main-panel,
      body.report-preview-mode #reportViewPreview{display:block!important;min-height:0!important;padding:0!important;}
      body *{visibility:hidden!important;}
      #arPrintPreview,#arPrintPreview *{visibility:visible!important;}
      #arPrintPreview{position:absolute;left:0;top:0;width:100%;display:block!important;}
      .ar-preview-toolbar,.ar-page-label{display:none!important;}
      .ar-print-page{page-break-after:always;margin:0!important;padding:0!important;}
      .ar-print-page:last-child{page-break-after:auto;}
      .ar-paper{width:216mm;min-height:279.4mm;margin:0!important;border:0!important;border-radius:0!important;box-shadow:none!important;padding:14mm 25mm 20mm!important;font-size:11px!important;color:#111!important;}
      .ar-cover-paper{height:279.4mm!important;}
      .ar-doc-table{font-size:10px!important;}
    }
  
      /* ── PUBLISH / MANAGE QUIZ PILL (post card) ── */
    @keyframes pcPublishPulse{0%,100%{box-shadow:0 0 0 0 rgba(245,158,11,.45);}50%{box-shadow:0 0 0 6px rgba(245,158,11,0);}}
    @keyframes pcManagePulse{0%,100%{box-shadow:0 0 0 0 rgba(26,158,120,.5);}50%{box-shadow:0 0 0 7px rgba(26,158,120,0);}}
    .pc-quiz-actions{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-top:.7rem;}
    .pc-publish-btn,.pc-manage-quiz-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;border-radius:20px;font-family:inherit;font-size:.78rem;font-weight:700;cursor:pointer;border:1.5px solid;transition:all .18s;background:transparent;}
    .pc-publish-btn{color:#b45309;border-color:#f59e0b;background:#fffbf0;animation:pcPublishPulse 2.2s ease-in-out infinite;}
    .pc-publish-btn:hover{background:#fde68a;color:#451a03;transform:translateY(-1px);}
    .pc-manage-quiz-btn{color:#fff;border-color:var(--primary);background:linear-gradient(135deg,var(--primary),var(--primary-dark));animation:pcManagePulse 2.4s ease-in-out infinite;}
    .pc-manage-quiz-btn:hover{filter:brightness(1.08);transform:translateY(-1px);}
    .pc-manage-quiz-btn i,.pc-publish-btn i{font-size:.78rem;}
    .pc-status-chip{display:inline-flex;align-items:center;gap:.32rem;padding:.18rem .6rem;border-radius:20px;font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;border:1.5px solid;}
    .pc-status-draft{color:#6b7280;border-color:#d1d5db;background:#f3f4f6;}
    .pc-status-open{color:#065f46;border-color:#6ee7b7;background:#d1fae5;}
    .pc-status-closed{color:#92400e;border-color:#fcd34d;background:#fef3c7;}
    .pc-status-released{color:#5b21b6;border-color:#c4b5fd;background:#ede9fe;}
    body.dark .pc-publish-btn{background:rgba(245,158,11,.1);}
    body.dark .pc-status-draft{background:rgba(107,114,128,.18);color:#d1d5db;border-color:#4b5563;}
    body.dark .pc-status-open{background:rgba(16,185,129,.15);color:#6ee7b7;border-color:#065f46;}
    body.dark .pc-status-closed{background:rgba(245,158,11,.15);color:#fcd34d;border-color:#92400e;}
    body.dark .pc-status-released{background:rgba(139,92,246,.15);color:#c4b5fd;border-color:#5b21b6;}

    /* ── PUBLISH CONFIG DIALOG (SweetAlert form) ── */
    .pq-form{text-align:left;display:flex;flex-direction:column;gap:.85rem;font-family:'DM Sans',sans-serif;}
    .pq-row label{display:block;font-size:.78rem;font-weight:600;color:var(--text);margin-bottom:.35rem;}
    .pq-row .pq-hint{font-size:.7rem;color:var(--text-muted);font-weight:500;margin-top:.2rem;}
    .pq-input,.pq-select{width:100%;padding:.55rem .75rem;border:1.5px solid var(--border);border-radius:10px;font-size:.85rem;font-family:inherit;background:var(--surface);color:var(--text);transition:border-color .15s,box-shadow .15s;}
    .pq-input:focus,.pq-select:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);}
    .pq-radio-group{display:flex;flex-direction:column;gap:.4rem;}
    .pq-radio{display:flex;align-items:center;gap:.5rem;padding:.5rem .7rem;border:1.5px solid var(--border);border-radius:10px;cursor:pointer;font-size:.82rem;font-weight:500;transition:all .15s;}
    .pq-radio:hover{border-color:var(--primary);background:var(--primary-light);}
    .pq-radio input{accent-color:var(--primary);}
    .pq-radio.checked{border-color:var(--primary);background:var(--primary-light);color:var(--primary);font-weight:600;}
    .pq-time-wrap.disabled{opacity:.4;pointer-events:none;}

    .pc-monitor-btn {
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .4rem .85rem; border-radius: 20px; font-size: .76rem;
  font-weight: 700; font-family: inherit; cursor: pointer;
  background: var(--accent-light); color: var(--accent);
  border: 1.5px solid rgba(31,115,219,.25);
  text-decoration: none; transition: all .18s;
}
.pc-monitor-btn:hover { background: var(--accent); color: #fff; }


/* ══ ASSIGN QUIZ WIZARD ══ */
@keyframes wizSlideUp{from{opacity:0;transform:translateY(28px);}to{opacity:1;transform:translateY(0);}}
#wizOverlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.48);z-index:600;align-items:center;justify-content:center;padding:1rem;}
#wizPanel{animation:wizSlideUp .28s cubic-bezier(.4,0,.2,1);}
.wiz-dot{width:6px;height:6px;border-radius:50%;background:var(--border);transition:all .3s;display:inline-block;}
.wiz-dot.active{background:var(--primary);width:20px;border-radius:3px;}
.wiz-dot.done{background:rgba(26,158,120,.38);}
.wiz-mode-card{border:2px solid var(--border);border-radius:14px;padding:.82rem .75rem .75rem;cursor:pointer;transition:all .2s;background:var(--surface);display:flex;flex-direction:column;align-items:center;text-align:center;gap:.35rem;position:relative;overflow:hidden;user-select:none;min-height:142px;}
.wiz-mode-card:hover{transform:translateY(-2px);box-shadow:0 5px 20px rgba(0,0,0,.09);}
.wiz-mode-card.sel-live{border-color:var(--danger);background:#fff5f5;box-shadow:0 0 0 4px rgba(217,48,37,.1);}
.wiz-mode-card.sel-due{border-color:var(--accent);background:#f0f5ff;box-shadow:0 0 0 4px rgba(31,115,219,.1);}
.wiz-mode-check{position:absolute;top:.6rem;right:.6rem;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#fff;}
.wiz-mode-check.live{background:var(--danger);}
.wiz-mode-check.due{background:var(--accent);}
.wiz-mode-name{font-size:.98rem;font-weight:700;color:var(--text);}
.wiz-mode-desc{font-size:.72rem;color:var(--text-muted);line-height:1.32;}
.wiz-mode-badge{font-size:.62rem;font-weight:700;padding:.15rem .5rem;border-radius:20px;margin-top:.1rem;}
.wiz-time-card{border:2px solid var(--border);border-radius:12px;padding:.9rem 1rem;cursor:pointer;transition:all .2s;background:var(--surface);display:flex;align-items:center;gap:.85rem;user-select:none;}
.wiz-time-card:hover{border-color:var(--primary);}
.wiz-time-card.sel{border-color:var(--primary);background:var(--primary-light);box-shadow:0 0 0 4px rgba(26,158,120,.1);}
.wiz-time-icon{width:42px;height:42px;border-radius:10px;background:var(--bg);color:var(--text-muted);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .2s;font-size:1.15rem;}
.wiz-time-card.sel .wiz-time-icon{background:var(--primary);color:#fff;}
.wiz-time-name{font-size:.88rem;font-weight:700;color:var(--text);}
.wiz-time-desc{font-size:.7rem;color:var(--text-muted);margin-top:.1rem;}
.wiz-radio{width:20px;height:20px;border-radius:50%;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .2s;}
.wiz-time-card.sel .wiz-radio{border-color:var(--primary);}
.wiz-radio::after{content:'';display:none;width:10px;height:10px;border-radius:50%;background:var(--primary);}
.wiz-time-card.sel .wiz-radio::after{display:block;}
.wiz-rev-item{display:flex;align-items:center;gap:.85rem;padding:.85rem 1rem;cursor:pointer;transition:background .15s;}
.wiz-rev-item:hover{background:var(--primary-light);}
.wiz-rev-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;}
.wiz-rev-label{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted);}
.wiz-rev-val{font-size:.88rem;font-weight:700;color:var(--text);margin-top:.1rem;}
.wiz-step-btn{width:28px;height:28px;border:1.5px solid var(--border);border-radius:50%;background:var(--surface);cursor:pointer;font-size:.95rem;font-weight:700;color:var(--text-muted);display:inline-flex;align-items:center;justify-content:center;transition:all .15s;line-height:1;}
.wiz-step-btn:hover{border-color:var(--primary);color:var(--primary);}
.wiz-chip-btn{border:1px solid var(--border);background:var(--surface);color:var(--text-muted);border-radius:999px;padding:.22rem .55rem;font-size:.72rem;font-weight:700;cursor:pointer;transition:all .15s;}
.wiz-chip-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
.wiz-chip-btn.active{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
.wiz-quick-row{display:flex;gap:.35rem;flex-wrap:wrap;}
.wiz-date-pill{display:flex;align-items:center;justify-content:space-between;gap:.4rem;border:1.5px solid var(--border);background:var(--surface);padding:.48rem .62rem;border-radius:10px;cursor:pointer;font-weight:700;color:var(--text);font-size:.82rem;}
.wiz-date-pop{display:none;position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:30;background:var(--surface);border:1.5px solid var(--border);border-radius:12px;box-shadow:0 12px 30px rgba(0,0,0,.12);padding:.55rem;}
.wiz-date-pop.show{display:block;}
.wiz-date-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.35rem;}
.wiz-date-opt{border:1px solid var(--border);background:var(--bg);color:var(--text);border-radius:9px;padding:.4rem .42rem;font-size:.74rem;font-weight:700;cursor:pointer;text-align:center;transition:all .15s;}
.wiz-date-opt:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
  </style>
</head>
<body>

<nav class="topbar">
  <a href="facultyUI.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
  <a href="facultyUI.php" class="topbar-brand">
    <div class="blogo"><i class="fas fa-book-open"></i></div>TERELEARN
  </a>
  <span class="topbar-sep">›</span>
  <span class="topbar-class-name" id="topbarClassName">Loading…</span>
  <div class="topbar-right">
    <button class="icon-btn" id="darkToggle"><i class="fas fa-moon"></i></button>
    <button class="icon-btn" onclick="openEditClassModal()" title="Edit Class"><i class="fas fa-cog"></i></button>
  </div>
</nav>

<div class="class-banner">
  <div class="banner-bg" id="bannerBg"></div>
  <div class="banner-overlay"></div>
  <div class="banner-pattern" id="bannerPattern" aria-hidden="true"></div>
  <button class="banner-edit-btn" onclick="openEditClassModal()"><i class="fas fa-pencil-alt"></i> Edit Class</button>
  <div class="banner-content">
    <div class="banner-title" id="bannerTitle">Loading…</div>
    <div class="banner-sub" id="bannerSub"></div>
    <div class="banner-chips" id="bannerChips"></div>
  </div>
</div>

<div class="tabs-bar">
  <button class="tab-btn active" data-tab="stream"><i class="fas fa-stream" style="margin-right:.35rem;font-size:.8rem;"></i>Stream</button>
  <button class="tab-btn" data-tab="people" id="peopleTabBtn"><i class="fas fa-users" style="margin-right:.35rem;font-size:.8rem;"></i>People<span class="tab-count" id="peopleCount">0</span></button>
  <button class="tab-btn" data-tab="grades" id="gradesTabBtn"><i class="fas fa-table" style="margin-right:.35rem;font-size:.8rem;"></i>Grades</button>
  <button class="tab-btn" data-tab="report" id="reportTabBtn"><i class="fas fa-clipboard-list" style="margin-right:.35rem;font-size:.8rem;"></i>Report</button>
</div>

<!-- ══ WAITLIST NOTIFICATION BAR ══ -->
<div class="wl-bar" id="wlNotifBar">
  <div class="wl-pulse-wrap">
    <div class="wl-pulse-ring"></div>
    <div class="wl-pulse-dot"></div>
  </div>
  <div class="wl-bar-icon">
    <i class="fas fa-user-clock" style="color:#b45309;font-size:.9rem;"></i>
  </div>
  <div class="wl-bar-text">
    <div class="wl-bar-title">
      <span id="wlBarCount">0</span> student<span id="wlBarPlural">s</span> waiting to join
    </div>
    <div class="wl-bar-sub">Sent a join request — review and admit or decline</div>
  </div>
  <button class="wl-bar-btn" onclick="openWaitlistDrawer()">
    <i class="fas fa-users" style="font-size:.75rem;"></i>
    Review requests
    <span class="wl-bar-badge" id="wlBarBadge">0</span>
  </button>
</div>

<div class="tab-section active" id="tab-stream">
  <div class="cr-layout">
    <div class="cr-main">
      <div class="compose-box">
        <div class="compose-trigger">
          <div class="compose-avatar" id="composeAvatar">F</div>
          <div class="compose-placeholder" onclick="openPostModal(null)">Share with your class…</div>
        </div>
        <div class="type-picker-row" id="typePicker"></div>
      </div>
      <div class="stream-filter-row">
        <input id="streamSearch" class="stream-filter-ctl stream-filter-search" type="text" placeholder="Search post title..." oninput="onStreamFilterChange()">
        <span class="stream-filter-label"><i class="fas fa-filter"></i> Type</span>
        <select id="streamTypeFilter" class="stream-filter-ctl" onchange="onStreamFilterChange()">
          <option value="all">All post types</option>
        </select>
      </div>
      <div class="lesson-period-filter" id="lessonPeriodFilter">
        <span class="lesson-period-filter-label"><i class="fas fa-book-open"></i> Coverage</span>
        <label class="lesson-period-filter-option"><input type="radio" name="streamLessonPeriod" value="all" checked onchange="onLessonPeriodFilterChange()"> All</label>
        <label class="lesson-period-filter-option"><input type="radio" name="streamLessonPeriod" value="prelim" onchange="onLessonPeriodFilterChange()"> Prelim</label>
        <label class="lesson-period-filter-option"><input type="radio" name="streamLessonPeriod" value="midterm" onchange="onLessonPeriodFilterChange()"> Midterm</label>
        <label class="lesson-period-filter-option"><input type="radio" name="streamLessonPeriod" value="finals" onchange="onLessonPeriodFilterChange()"> Finals</label>
      </div>
      <div id="streamFeed">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;height:100px;background-image:linear-gradient(90deg,var(--border) 25%,var(--bg) 50%,var(--border) 75%);background-size:200% 100%;animation:shimmer 1.3s infinite;margin-bottom:.85rem;"></div>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;height:100px;background-image:linear-gradient(90deg,var(--border) 25%,var(--bg) 50%,var(--border) 75%);background-size:200% 100%;animation:shimmer 1.3s infinite;margin-bottom:.85rem;"></div>
      </div>
    </div>
    <div class="cr-side">
      <div class="side-card">
        <div class="side-card-title">Join Code
          <button class="join-code-refresh join-code-refresh-title" type="button" id="regenCodeBtn" title="Reset join code"><i class="fas fa-sync-alt"></i></button>
        </div>
        <div class="code-display" id="joinCodeDisplay">——</div>
        <div class="code-actions">
          <button class="code-btn" id="copyCodeBtn"><i class="fas fa-copy"></i> Code</button>
          
          <button class="code-btn" id="copyLinkBtn"><i class="fas fa-link"></i> Link</button>
        </div>
        <div class="link-row" id="joinLinkRow" style="display:none;">
          <span class="link-text" id="joinLinkText"></span>
          <button class="link-copy-btn" id="copyLinkSmall"><i class="fas fa-copy"></i></button>
        </div>
      </div>
       <!-- ══ GOOGLE MEET CARD ══ -->
<div class="side-card" id="meetSideCard">
  <div class="side-card-title">
    Google Meet
    <button class="icon-btn" style="width:24px;height:24px;font-size:.72rem;"
            id="meetEditBtn" title="Set Meet link">
      <i class="fas fa-pencil-alt"></i>
    </button>
  </div>

  <!-- Loading state -->
  <div id="meetSideLoading" style="display:flex;align-items:center;gap:.5rem;
       font-size:.78rem;color:var(--text-muted);padding:.4rem 0;">
    <i class="fas fa-circle-notch fa-spin" style="color:#1a73e8;font-size:.75rem;"></i>
    Loading…
  </div>

  <!-- Link is set -->
  <div id="meetSideReady" style="display:none;">
    <a id="meetSideLink" href="#" target="_blank"
       style="display:flex;align-items:center;justify-content:center;gap:.5rem;
              background:#1a73e8;color:#fff;border-radius:8px;padding:.55rem;
              font-size:.82rem;font-weight:700;text-decoration:none;
              margin-bottom:.6rem;box-shadow:0 2px 8px rgba(26,115,232,.35);
              transition:opacity .2s;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
        <path d="M20 4.5l-5 3.5V5a2 2 0 00-2-2H4a2 2 0 00-2 2v14a2 2 0 002 2h9a2 2 0 002-2v-3l5 3.5A1 1 0 0022 18V6a1 1 0 00-2-1.5z"/>
      </svg>
      Join Meet
    </a>
    <div class="code-actions">
      <button class="code-btn" id="meetSideCopyLink">
        <i class="fas fa-link"></i> Copy Link
      </button>
      <button class="code-btn" onclick="openMeetModal()">
        <i class="fas fa-pencil-alt"></i> Change
      </button>
    </div>
    <div class="link-row" style="margin-top:.5rem;">
      <span class="link-text" id="meetSideCodeDisplay"
            style="font-size:.7rem;color:var(--text-muted);"></span>
    </div>
  </div>

  <!-- No link set yet -->
  <div id="meetSideEmpty" style="display:none;text-align:center;padding:.25rem 0 .1rem;">
    <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.6rem;">
      No Meet link set yet.
    </p>
    <button class="code-btn" style="width:100%;" onclick="openMeetModal()">
      <i class="fas fa-plus" style="color:#1a73e8;"></i> Set Meet Link
    </button>
  </div>

  <!-- Error state -->
  <div id="meetSideError" style="display:none;font-size:.78rem;
       color:var(--text-muted);text-align:center;padding:.4rem 0;">
    Could not load Meet info
  </div>
</div>
      <div class="side-card">
        <div class="side-card-title">Class Info</div>
        <div style="font-size:.82rem;line-height:1.9;color:var(--text-muted);" id="classInfoList"></div>
      </div>
    </div>
  </div>
</div>

<div class="tab-section" id="tab-classwork">
  <div class="cr-layout">
    <div class="cr-main">
      <div style="display:flex;gap:.6rem;margin-bottom:1.25rem;flex-wrap:wrap;" id="cwTypeButtons"></div>
      <div id="classworkFeed"></div>
    </div>
    <div class="cr-side">
      <div class="side-card">
        <div class="side-card-title">Quick Post</div>
        <div id="cwQuickBtns"></div>
      </div>
    </div>
  </div>
</div>

<div class="tab-section" id="tab-people">
  <div class="cr-layout">
    <div class="cr-main">
      <div id="pendingSection"></div>
      <div class="people-section">
        <div class="people-section-head">Students <span class="people-count" id="enrolledCount">0</span></div>
        <div id="enrolledList"><div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin"></i> Loading…</div></div>
      </div>
    </div>
    <div class="cr-side">
        <div class="side-card people-add-card">
          <div class="side-card-title">ADD STUDENTS</div>
          <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;">Share the class code or invite link so students can join.</p>
          <button class="btn btn-primary" style="width:100%;margin-bottom:.75rem;" onclick="openInviteModal()">
            <i class="fas fa-paper-plane"></i> Invite Students
          </button>
        <div class="code-display" id="peopleCodeDisplay" style="font-size:1.5rem;">——</div>
        <div class="code-actions" style="margin-top:.6rem;">
          <button class="code-btn active" id="peopleCopyCode" data-people-code-mode="code" type="button"><i class="fas fa-copy"></i> Code</button>
          <button class="code-btn" id="peopleCopyLink" data-people-code-mode="link" type="button"><i class="fas fa-link"></i> Link</button>
        </div>
        <div class="link-row" id="peopleJoinLinkRow" style="display:none;margin-top:.6rem;">
          <span class="link-text" id="peopleJoinLinkText"></span>
          <button class="link-copy-btn" id="peopleCopyLinkSmall" type="button"><i class="fas fa-copy"></i></button>
        </div>
      </div>
      <!-- Waitlist mini-card in sidebar -->
      <div class="side-card" id="wlSideCard" style="display:none;">
        <div class="side-card-title" style="color:#b45309;">
          <i class="fas fa-user-clock" style="margin-right:.3rem;color:#f59e0b;"></i>
          Waiting to Join
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">
          <div>
            <span id="wlSideCount" style="font-size:1.6rem;font-weight:700;color:#f59e0b;display:block;line-height:1.1;">0</span>
            <span style="font-size:.75rem;color:var(--text-muted);">pending request<span id="wlSidePlural">s</span></span>
          </div>
          <button class="wl-bar-btn" style="font-size:.75rem;padding:.4rem .85rem;" onclick="openWaitlistDrawer()">
            Review <span class="wl-bar-badge" id="wlSideBadge">0</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     REPORT TAB (Accomplishment Report)
══════════════════════════════════════════ -->
<div class="tab-section" id="tab-report">
  <div class="report-shell">
    <aside class="report-step-sidebar">
      <div class="report-step-kicker">Report sections</div>
      <div class="report-view-toggle" role="tablist" aria-label="Report sections">
        <button class="report-view-btn active" type="button" data-report-view="attendance">
          <span class="report-step-icon"><i class="fas fa-calendar-check"></i></span>
          <span class="report-step-copy">
            <span class="report-step-label">Attendance</span>
            <span class="report-step-sub">Calendar records</span>
          </span>
          <span class="report-step-num">1</span>
        </button>
        <button class="report-view-btn" type="button" data-report-view="analytics">
          <span class="report-step-icon"><i class="fas fa-chart-line"></i></span>
          <span class="report-step-copy">
            <span class="report-step-label">Analytics</span>
            <span class="report-step-sub">K-Means clustering</span>
          </span>
          <span class="report-step-num">2</span>
        </button>
        <button class="report-view-btn" type="button" data-report-view="accomplishment">
          <span class="report-step-icon"><i class="fas fa-file-signature"></i></span>
          <span class="report-step-copy">
            <span class="report-step-label">Accomplishment</span>
            <span class="report-step-sub">Form and print preview</span>
          </span>
          <span class="report-step-num">3</span>
        </button>
      </div>
    </aside>

    <div class="report-main-panel">
    <div class="report-view active" id="reportViewAttendance">
      <div class="cr-layout report-attendance-layout">
        <div class="cr-main">
          <div class="att-card">
            <div class="att-header">
              <div>
                <div class="att-title"><i class="fas fa-calendar-check"></i> Attendance Calendar</div>
                <div class="att-subtitle" id="attSemesterLabel">Loading semester...</div>
              </div>
              <div class="att-export-group">
                <button class="att-log-btn" id="attLogSessionBtn" type="button" title="Log today's session">
                  <i class="fas fa-calendar-plus"></i> Log Session
                </button>
                <button class="att-export-btn" id="attExportPdf" type="button" title="Export to PDF">
                  <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button class="att-export-btn" id="attExportExcel" type="button" title="Export to Excel">
                  <i class="fas fa-file-excel"></i> Excel
                </button>
              </div>
            </div>

            <div class="att-toolbar">
              <div class="att-nav">
                <button class="att-nav-btn" id="attPrevMonth" type="button" title="Previous month"><i class="fas fa-chevron-left"></i></button>
                <div class="att-month-label" id="attMonthLabel">--</div>
                <button class="att-nav-btn" id="attNextMonth" type="button" title="Next month"><i class="fas fa-chevron-right"></i></button>
              </div>
              <div class="att-toolbar-right">
                <button class="att-today-btn" id="attTodayBtn" type="button"><i class="fas fa-calendar-day"></i> Today</button>
                <div class="att-search-wrap">
                  <i class="fas fa-search att-search-icon"></i>
                  <input type="text" class="att-search-input" id="attSearchInput" placeholder="MM/DD/YYYY" maxlength="10">
                </div>
              </div>
            </div>

            <div class="att-calendar">
              <div class="att-weekdays">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
              </div>
              <div class="att-days" id="attDaysGrid">
                <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted);">
                  <i class="fas fa-spinner fa-spin"></i> Loading calendar...
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="cr-side">
          <div class="side-card">
            <div class="side-card-title">Legend</div>
            <div class="att-legend">
              <div class="att-legend-row"><span class="att-dot att-dot-high"></span> >= 80% present</div>
              <div class="att-legend-row"><span class="att-dot att-dot-mid"></span> 50% - 79% present</div>
              <div class="att-legend-row"><span class="att-dot att-dot-low"></span> &lt; 50% present</div>
              <div class="att-legend-row"><span class="att-dot att-dot-none"></span> No record / outside semester</div>
            </div>
          </div>

          <div class="side-card">
            <div class="side-card-title">Month Snapshot</div>
            <div class="att-stats">
              <div class="att-stat-row">
                <span class="att-stat-label">Sessions recorded</span>
                <span class="att-stat-value" id="attMonthSessions">0</span>
              </div>
              <div class="att-stat-row">
                <span class="att-stat-label">Avg. attendance</span>
                <span class="att-stat-value" id="attMonthAvg">--</span>
              </div>
            </div>
          </div>

          <div class="side-card">
            <div class="side-card-title">Attendance Flags</div>
            <div class="att-stats">
              <div class="att-stat-row">
                <span class="att-stat-label">Absence marks</span>
                <span class="att-stat-value" id="attMonthAbsences">0</span>
              </div>
              <div class="att-stat-row">
                <span class="att-stat-label">Students at risk</span>
                <span class="att-stat-value" id="attMonthRiskCount">0</span>
              </div>
              <div class="att-stat-row">
                <span class="att-stat-label">Best day</span>
                <span class="att-stat-value" id="attMonthBestDay">--</span>
              </div>
              <div class="att-stat-row">
                <span class="att-stat-label">Lowest day</span>
                <span class="att-stat-value" id="attMonthLowestDay">--</span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="report-view" id="reportViewAnalytics">
      <div class="ra-header">
        <div>
          <div class="ra-title"><i class="fas fa-brain"></i> K-Means Analytics</div>
          <div class="ra-sub">Students are clustered by real attendance percentage and gradebook final score.</div>
        </div>
        <div class="ra-ai-pill"><i class="fas fa-chart-line"></i> AI-powered clustering</div>
      </div>

      <div class="ra-summary" id="raSummaryCards">
        <div class="ra-summary-card"><div class="ra-summary-label">Total students</div><div class="ra-summary-value">--</div><div class="ra-summary-sub">enrolled</div></div>
        <div class="ra-summary-card"><div class="ra-summary-label">High performers</div><div class="ra-summary-value">--</div><div class="ra-summary-sub">students</div></div>
        <div class="ra-summary-card"><div class="ra-summary-label">Average</div><div class="ra-summary-value">--</div><div class="ra-summary-sub">students</div></div>
        <div class="ra-summary-card"><div class="ra-summary-label">At risk</div><div class="ra-summary-value">--</div><div class="ra-summary-sub">students</div></div>
      </div>

      <div class="ra-grid">
        <div class="ra-card">
          <div class="ra-title" style="font-size:.92rem;margin-bottom:.75rem;"><i class="fas fa-bullseye"></i> Attendance x Grade Scatter Plot</div>
          <div class="ra-scatter" id="raScatterPlot">
            <div class="ra-empty">Open the Report tab to load analytics.</div>
          </div>
          <div class="ra-legend">
            <span><span class="ra-legend-dot ra-high"></span>High</span>
            <span><span class="ra-legend-dot ra-average"></span>Average</span>
            <span><span class="ra-legend-dot ra-risk"></span>At Risk</span>
          </div>
        </div>
        <div class="ra-clusters" id="raClusterCards"></div>
      </div>

      <div class="ra-card" style="margin-top:1rem;">
        <div class="ra-title" style="font-size:.92rem;margin-bottom:.65rem;"><i class="fas fa-table"></i> Cluster Stats</div>
        <div style="overflow:auto;">
          <table class="ra-table">
            <thead><tr><th>Cluster</th><th>Students</th><th>Avg attendance</th><th>Avg grade</th><th>Recommendation</th></tr></thead>
            <tbody id="raStatsRows"><tr><td colspan="5">No analytics loaded.</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="report-view" id="reportViewAccomplishment">
      <form id="accomplishmentReportForm" autocomplete="off">
        <input type="hidden" id="arReportId" name="report_id">
        <input type="hidden" id="arSessionId" name="session_id">
        <input type="hidden" id="arDateCoveredStart">
        <input type="hidden" id="arDateCoveredEnd">
        <div class="ar-wizard" data-ar-wizard>
          <div class="ar-wizard-head">
            <div class="ar-wizard-top">
              <div>
                <div class="ar-step-kicker" id="arWizardStepLabel">Step 1 of 4</div>
                <div class="ar-wizard-title" id="arWizardTitle">Session and class details</div>
              </div>
              <div class="ar-wizard-count" id="arWizardPercent">25%</div>
            </div>
            <div class="ar-progress-track" aria-hidden="true"><span id="arWizardBar"></span></div>
            <div class="ar-step-list" role="tablist" aria-label="Accomplishment report progress">
              <button class="ar-step-pill is-active" type="button" data-ar-step-target="0"><span>1</span><b>Session</b></button>
              <button class="ar-step-pill" type="button" data-ar-step-target="1"><span>2</span><b>Report</b></button>
              <button class="ar-step-pill" type="button" data-ar-step-target="2"><span>3</span><b>Activities</b></button>
              <button class="ar-step-pill" type="button" data-ar-step-target="3"><span>4</span><b>Approval</b></button>
            </div>
          </div>

          <section class="ar-card ar-wizard-step is-active" data-ar-step="0">
            <div class="ar-card-title">Session selector</div>
            <label class="ar-field ar-field-full"><span>Select session to report</span>
              <select class="ar-input" id="arSessionSelect" name="session_select">
                <option value="">Loading sessions...</option>
              </select>
            </label>
            <div class="ar-session-note" id="arSessionNote">Select a recorded attendance session.</div>

            <div class="ar-card-title ar-title-spaced">Class details</div>
            <div class="ar-field-grid">
              <label class="ar-field"><span>Faculty name</span><input class="ar-input" id="arFacultyName" name="faculty_name" readonly></label>
              <label class="ar-field"><span>Subject name</span><input class="ar-input" id="arSubjectName" name="subject_name" readonly></label>
              <label class="ar-field"><span>Section</span><input class="ar-input" id="arSection" name="section_label" readonly></label>
              <label class="ar-field"><span>Department name</span><input class="ar-input" id="arDepartmentName" name="department_name" readonly></label>
              <label class="ar-field"><span>Employment status</span><input class="ar-input" id="arEmploymentStatus" name="employment_status" readonly></label>
              <label class="ar-field"><span>Class size</span><input class="ar-input" id="arClassSize" name="class_size" readonly></label>
              <label class="ar-field"><span>No. of attendees</span><input class="ar-input" id="arAttendees" name="attendees_count" readonly></label>
              <label class="ar-field"><span>No. of absent</span><input class="ar-input" id="arAbsent" name="absent_count" readonly></label>
              <label class="ar-field"><span>Date conducted</span><input class="ar-input" id="arDateConducted" name="date_conducted" readonly></label>
              <label class="ar-field"><span>Semester and Academic Year</span><input class="ar-input" id="arSemesterAy" name="semester_ay" readonly></label>
            </div>
          </section>

          <section class="ar-card ar-wizard-step" data-ar-step="1">
            <div class="ar-card-title">Report details</div>
            <div class="ar-field-grid">
              <label class="ar-field"><span>Academic week number</span><input class="ar-input" id="arAcademicWeek" name="academic_week" placeholder="18th"></label>
              <label class="ar-field"><span>Units</span><input class="ar-input" id="arUnits" name="units" type="number" min="0" step="1" placeholder="3"></label>
              <label class="ar-field"><span>Date covered</span>
                <div class="ar-date-field-wrap">
                  <input class="ar-input" id="arDateCovered" name="date_covered" placeholder="Choose date range" readonly>
                  <button class="ar-date-pick-btn" id="arDateCoveredPickBtn" type="button" aria-label="Choose date covered"><i class="fas fa-calendar-days"></i></button>
                </div>
              </label>
              <label class="ar-field"><span>Time conducted</span><input class="ar-input" id="arTimeConducted" name="time_conducted" placeholder="10:30 - 1:30 PM"></label>
              <label class="ar-field"><span>Duration in hours</span><input class="ar-input" id="arDuration" name="duration" placeholder="3 hrs."></label>
            </div>
            <label class="ar-field ar-field-full"><span>Topics covered</span><textarea class="ar-input" id="arTopicsCovered" name="topics_covered" rows="3"></textarea></label>
          </section>

          <section class="ar-card ar-wizard-step" data-ar-step="2">
            <div class="ar-card-title">Activities and photo documentation</div>
            <label class="ar-field ar-field-full"><span>Synchronous activities conducted</span><textarea class="ar-input" id="arSyncActivities" name="sync_activities" rows="3"></textarea></label>
            <label class="ar-field ar-field-full"><span>Asynchronous activities</span><textarea class="ar-input" id="arAsyncActivities" name="async_activities" rows="3"></textarea></label>
            <label class="ar-field ar-field-full"><span>Laboratory activities</span><textarea class="ar-input" id="arLabActivities" name="lab_activities" rows="3"></textarea></label>
            <div class="ar-card-title ar-title-spaced">Photo documentation</div>
            <input type="hidden" id="arPhotoDocumentation" name="photo_documentation">
            <label class="ar-photo-upload" for="arPhotoInput">
              <input type="file" id="arPhotoInput" accept="image/*" hidden>
              <span class="ar-photo-empty" id="arPhotoEmpty"><i class="fas fa-image"></i>Upload class photo documentation</span>
              <img class="ar-photo-preview" id="arPhotoPreview" alt="Photo documentation preview">
            </label>
            <div class="ar-photo-actions">
              <span>Shown on Page 2 of the print preview.</span>
              <button class="btn btn-ghost btn-sm" id="arPhotoClearBtn" type="button" style="display:none;"><i class="fas fa-times"></i> Remove photo</button>
            </div>
          </section>

          <section class="ar-card ar-wizard-step" data-ar-step="3">
            <div class="ar-card-title">Approval section</div>
            <div class="ar-field-grid">
              <label class="ar-field"><span>Faculty signature</span><input class="ar-input" id="arFacultySignature" name="faculty_signature" placeholder="Over printed name"></label>
              <label class="ar-field"><span>Dean name</span><input class="ar-input" id="arDeanName" name="dean_name"></label>
              <label class="ar-field"><span>Date submitted</span><input class="ar-input" id="arDateSubmitted" name="date_submitted" type="date"></label>
              <label class="ar-field"><span>HRD received date</span><input class="ar-input" id="arHrdReceivedDate" name="hrd_received_date" type="date"></label>
            </div>
          </section>

          <div class="ar-form-actions ar-wizard-actions">
            <button class="btn btn-ghost" id="arWizardPrevBtn" type="button"><i class="fas fa-arrow-left"></i> Back</button>
            <button class="btn btn-primary" id="arWizardNextBtn" type="button">Next <i class="fas fa-arrow-right"></i></button>
            <span class="ar-save-state" id="arSaveState">Draft not saved</span>
            <button class="btn btn-ghost" id="arSaveDraftBtn" type="button"><i class="fas fa-save"></i> Save draft</button>
            <button class="btn btn-primary" id="arPreviewBtn" type="button"><i class="fas fa-print"></i> Preview & Print</button>
          </div>
        </div>
      </form>
    </div>

    <div class="report-view" id="reportViewPreview">
      <div class="ar-preview-toolbar">
        <button class="btn btn-ghost" id="arBackToFormBtn" type="button"><i class="fas fa-pen-to-square"></i> Edit report form</button>
        <button class="btn btn-primary" id="arPrintBtn" type="button"><i class="fas fa-file-pdf"></i> Export PDF</button>
      </div>

      <div class="ar-print-stack" id="arPrintPreview">
        <section class="ar-print-page">
          <div class="ar-page-label">Page 1 of 3 - Accomplishment Report Cover</div>
          <div class="ar-paper ar-cover-paper">
            <div class="ar-doc-head">
              <strong>COLEGIO DE STA. TERESA DE AVILA INC.</strong>
              <span>6 Kingfisher and Skylark Streets, Zabarte Subdivision, Novaliches, Quezon City</span>
              <span>Date of Affectivity: <span data-print="date_of_affectivity"></span></span>
            </div>
            <div class="ar-doc-dept" data-print="department_name"></div>
            <div class="ar-doc-title">ASYNCHRONOUS CLASSES ACCOMPLISHMENT REPORT</div>
            <table class="ar-doc-table ar-cover-meta">
              <tr><th>Academic Week</th><td data-print="academic_week"></td><th>Date Covered</th><td data-print="date_covered"></td></tr>
              <tr><th>Name</th><td data-print="faculty_name"></td><th>Subject</th><td data-print="subject_name"></td></tr>
              <tr><th>Unit</th><td data-print="units"></td><th>Section</th><td data-print="section_label"></td></tr>
              <tr><th>Status</th><td data-print="employment_status"></td><th>Class Size</th><td data-print="class_size"></td></tr>
            </table>
            <div class="ar-doc-section">ACTIVITIES</div>
            <div class="ar-doc-section ar-doc-section-soft">SYNCHRONOUS ACTIVITIES</div>
            <table class="ar-doc-table ar-cover-sync">
              <tr><th>No. of Attendees</th><td data-print="attendees_count"></td><td rowspan="3"><strong>Topics Covered:</strong><br><span data-print="topics_covered"></span></td></tr>
              <tr><th>No. of Absent</th><td data-print="absent_count"></td></tr>
              <tr><th>Date Conducted</th><td data-print="date_conducted"></td></tr>
              <tr><th>Time Conducted</th><td data-print="time_conducted"></td><td rowspan="2"><strong>Activities Conducted:</strong><br><span data-print="sync_activities"></span></td></tr>
              <tr><th>Duration</th><td data-print="duration"></td></tr>
            </table>
            <div class="ar-doc-section ar-doc-section-soft">ASYNCHRONOUS ACTIVITIES</div>
            <div class="ar-doc-box ar-cover-fill-box" data-print="async_activities"></div>
            <div class="ar-doc-section ar-doc-section-soft">LABORATORY ACTIVITIES</div>
            <div class="ar-doc-box ar-cover-fill-box" data-print="lab_activities"></div>
            <table class="ar-signature-table ar-cover-signatures">
              <thead>
                <tr>
                  <th>FOR COLLEGE DEPARTMENT</th>
                  <th>FOR HRD</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Dean: <span data-print="dean_name"></span></td>
                  <td>Date Received: <span data-print="hrd_received_date"></span></td>
                </tr>
                <tr>
                  <td>Date: <span data-print="date_submitted"></span></td>
                  <td>Checked by: <span class="ar-signature-line"></span></td>
                </tr>
                <tr>
                  <td></td>
                  <td>Noted by: <span class="ar-signature-line"></span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="ar-print-page">
          <div class="ar-page-label">Page 2 of 3 - Attendance Report</div>
          <div class="ar-paper">
            <div class="ar-doc-title">ATTENDANCE REPORT</div>
            <table class="ar-doc-table ar-attendance-print">
              <thead><tr><th>#</th><th>PRESENT</th><th>#</th><th>ABSENT</th></tr></thead>
              <tbody id="arPrintAttendanceRows"></tbody>
            </table>
            <div class="ar-photo-label">PHOTO DOCUMENTATION</div>
            <div class="ar-photo-box" id="arPrintPhotoBox"><span>No photo documentation attached.</span><img id="arPrintPhoto" alt="Photo documentation"></div>
          </div>
        </section>

        <section class="ar-print-page">
          <div class="ar-page-label">Page 3 of 3 - Analytics Summary</div>
          <div class="ar-paper">
            <div class="ar-doc-title">STUDENT PERFORMANCE ANALYTICS</div>
            <div class="ar-doc-generated">Generated by NEXLEARN &middot; Veriox &middot; Colegio de Sta. Teresa de Avila</div>
            <table class="ar-doc-table">
              <thead><tr><th>Student Name</th><th>Attendance Rate</th><th>Grade Avg.</th><th>Cluster</th><th>Remarks</th></tr></thead>
              <tbody id="arPrintAnalyticsRows"></tbody>
            </table>
            <div class="ar-doc-footer">Generated on <span data-print="generated_on"></span> | <span data-print="subject_name"></span> | <span data-print="section_label"></span> | <span data-print="semester_ay"></span></div>
          </div>
        </section>
      </div>
    </div>
    </div>
  </div>
</div>

<div class="tab-section" id="tab-grades">
  <div class="cr-layout">
    <div class="cr-main">
      <div class="att-card">
        <div class="att-header">
          <div>
            <div class="att-title"><i class="fas fa-book"></i> Gradebook</div>
            <div class="att-subtitle" id="gbSummaryLabel">Enrolled students and their recorded scores</div>
          </div>
          <div class="att-export-group">
            <button class="att-export-btn" id="gbExportPdf" title="Export to PDF">
              <i class="fas fa-file-pdf"></i> PDF
            </button>
            <button class="att-export-btn" id="gbExportExcel" title="Export to Excel">
              <i class="fas fa-file-excel"></i> Excel
            </button>
          </div>
        </div>
        <div class="gb-info-banner">
          <i class="fas fa-circle-info"></i>
          <span>
            Scores are <strong>read-only</strong> in Gradebook. Values are pulled from graded submissions and quiz attempts.
            To change a score, open the student submission in the related post and grade there.
          </span>
        </div>
        <div class="gb-sheet-head">
          <div class="gb-school-name">COLEGIO DE STA. TERESA DE AVILA</div>
          <div class="gb-school-address">1177 Quirino Hiway, Brgy. Kaligayahan, Novaliches, Quezon City</div>
          <div class="gb-meta-grid">
            <div><span>Instructor :</span><strong id="gbMetaInstructor">-</strong></div>
            <div><span>School Year :</span><strong id="gbMetaSchoolYear">-</strong></div>
            <div><span>Subject Code :</span><strong id="gbMetaSubjectCode">-</strong></div>
            <div><span>Semester :</span><strong id="gbMetaSemester">-</strong></div>
            <div><span>Description :</span><strong id="gbMetaDescription">-</strong></div>
            <div><span>Course :</span><strong id="gbMetaCourse">-</strong></div>
            <div><span>Units :</span><strong id="gbMetaUnits">-</strong></div>
            <div><span>Section :</span><strong id="gbMetaSection">-</strong></div>
          </div>
          <div class="gb-sheet-status">
            <span>Passed: <strong id="gbPassedCount">0</strong></span>
            <span>Failed: <strong id="gbFailedCount">0</strong></span>
            <span>Without Grade: <strong id="gbNoGradeCount">0</strong></span>
          </div>
        </div>
        <div class="filter-row">
          <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input class="search-inp" id="gbSearchInp" type="text" placeholder="Search by name or student #..." oninput="filterGradebookRows(this.value)">
          </div>
          <div class="filter-chips">
            <span class="fchip fchip-all active" onclick="setGbFilter('all',this)">All</span>
            <span class="fchip fchip-quiz" onclick="setGbFilter('quiz',this)">Quiz</span>
            <span class="fchip fchip-activities" onclick="setGbFilter('activities',this)">Activity</span>
            <span class="fchip fchip-assignment" onclick="setGbFilter('assignment',this)">Assignment</span>
            <span class="fchip fchip-exam" onclick="setGbFilter('exam',this)">Exam</span>
            <span class="fchip fchip-score" onclick="setGbFilter('score',this)">Score</span>
          </div>
          <div class="toggle-group">
            <button class="toggle-btn" id="tog-pct" onclick="toggleGbPctView()">%</button>
          </div>
        </div>
        <div class="table-scroll" id="tableScroll">
          <table class="table" id="gradebookTable" style="margin:0;">
            <thead>
              <tr class="row-period" id="periodRow"></tr>
              <tr class="row-group" id="groupRow"></tr>
              <tr class="row-head" id="headRow"></tr>
            </thead>
            <tbody id="gbBody">
              <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:1rem;">Open the Grades tab to load records.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     ATTENDANCE EDIT MODAL (per-date)
══════════════════════════════════════════ -->
<div class="att-modal-overlay" id="attModalOverlay"></div>
<div class="att-modal" id="attModal" style="display:none;">
  <div class="att-modal-head">
    <div>
      <div class="att-modal-title" id="attModalDate">—</div>
      <div class="att-modal-sub" id="attModalSub">Mark students absent. All start as Present.</div>
    </div>
    <button class="att-modal-close" id="attModalClose"><i class="fas fa-times"></i></button>
  </div>

  <div class="att-modal-toolbar">
    <button class="att-bulk-btn" id="attMarkAllPresent"><i class="fas fa-check-double"></i> Mark all Present</button>
    <button class="att-bulk-btn att-bulk-btn-danger" id="attMarkAllAbsent"><i class="fas fa-times-circle"></i> Mark all Absent</button>
    <div class="att-modal-counts">
      <span class="att-pill att-pill-present"><i class="fas fa-check"></i> <span id="attCountPresent">0</span> Present</span>
      <span class="att-pill att-pill-absent"><i class="fas fa-times"></i> <span id="attCountAbsent">0</span> Absent</span>
    </div>
  </div>

  <div class="att-modal-body" id="attModalBody">
    <!-- student rows injected by JS -->
  </div>

  <div class="att-modal-footer">
    <button class="btn btn-ghost" id="attCancelBtn">Cancel</button>
    <button class="btn btn-primary" id="attSaveBtn"><i class="fas fa-save"></i> Save Attendance</button>
  </div>
</div>

<!-- ══════════════════════════════════════════
     GOOGLE CLASSROOM STYLE POST MODAL
══════════════════════════════════════════ -->
<div class="gc-modal-overlay" id="gcModalOverlay"></div>
<div class="gc-modal-panel" id="gcModalPanel" style="display:none;">

  <!-- Top bar -->
  <div class="gc-topbar">
    <button class="gc-close-btn" onclick="closePostModal()"><i class="fas fa-times"></i></button>
    <div class="gc-type-icon-lg" id="gcTypeIconLg" style="background:#e8f5e9;color:#2e7d32;">
      <i class="fas fa-bullhorn"></i>
    </div>
    <span class="gc-modal-heading" id="gcModalHeading">New Post</span>
    <div class="gc-topbar-actions">
      <button class="btn btn-ghost" onclick="closePostModal()">Cancel</button>
      <button class="btn btn-primary" id="pmSubmitBtn" onclick="submitPost()">
        <i class="fas fa-paper-plane"></i>
        <span id="gcSubmitLabel">Post</span>
      </button>
    </div>
  </div>

  <!-- Body -->
  <div class="gc-body">

    <!-- Left: type nav + form -->
    <div class="gc-left">

      <!-- Type nav -->
      <div class="gc-type-nav" id="gcTypeNav">
        <div class="gc-type-nav-label">Post type</div>
        <!-- filled by JS -->
      </div>

      <!-- Form area -->
      <div class="gc-form-area" id="gcFormArea">
        <input type="hidden" id="pmPostId">
        <input type="hidden" id="pmPostType" value="announcement">
        <input type="hidden" id="pmPostTypeId" value="">

        <!-- Lesson uses a 50/50 Week No. and grading-period row -->
        <div class="gc-lesson-heading-row" id="gcLessonHeadingRow">
        <div class="gc-field" id="gcTitleField">
          <label class="gc-label" id="gcTitleLabel">Title <span style="color:var(--danger)">*</span></label>
          <div class="gc-week-picker-wrap" id="gcWeekPickerWrap">
            <input type="text" class="gc-input gc-input-lg" id="pmTitle" placeholder="Enter title…" autocomplete="off" spellcheck="false">
            <button type="button" class="gc-week-pick-btn" id="gcWeekPickerBtn" onclick="openLessonWeekPicker()">
              <i class="fas fa-calendar-week"></i> Choose
            </button>
          </div>
          <input type="hidden" id="pmLessonWeeks" value="">
          <div class="gc-week-hint" id="gcWeekHint" style="display:none;">Occupied weeks are dimmed. Select one week or multiple weeks for combined lesson coverage.</div>
        </div>
        <div class="gc-field" id="gcLessonPeriodField" style="display:none;">
          <label class="gc-label">Grading Period <span style="color:var(--danger)">*</span></label>
          <div class="gc-period-options">
            <label class="gc-period-option"><input type="radio" name="pmLessonPeriod" value="prelim"> Prelim</label>
            <label class="gc-period-option"><input type="radio" name="pmLessonPeriod" value="midterm"> Midterm</label>
            <label class="gc-period-option"><input type="radio" name="pmLessonPeriod" value="finals"> Finals</label>
          </div>
        </div>
        </div>
        <div class="gc-field" id="gcExamModeField" style="display:none;">
          <label class="gc-label">Exam Mode</label>
          <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
            <button type="button" id="examModeBtnQuiz" class="gc-attach-btn" onclick="setExamMode('questionnaire')"><i class="fas fa-list-check"></i> Questionnaire</button>
            <button type="button" id="examModeBtnFile" class="gc-attach-btn" onclick="setExamMode('file')"><i class="fas fa-file-upload"></i> File Submission</button>
          </div>
          <div id="gcExamModeHint" style="font-size:.72rem;color:var(--text-muted);margin-top:.35rem;">Choose how students take this exam.</div>
          <input type="hidden" id="pmExamMode" value="questionnaire">
        </div>

        <!-- Topic (hidden for announcements) -->
        <div class="gc-field" id="gcTopicField" style="display:none;">
          <label class="gc-label" id="gcTopicLabel">Topic <span style="font-size:.68rem;font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted);">(optional)</span></label>
          <input type="text" class="gc-input" id="pmTopic" placeholder="Enter topic.."
            oninput="let p=this.selectionStart;this.value=this.value.toUpperCase();this.setSelectionRange(p,p);">
        </div>

        <!-- Body / Message -->
        <div class="gc-field">
          <label class="gc-label" id="gcBodyLabel">Message</label>
          <textarea class="gc-textarea" id="pmBody" placeholder="Write something for your class…" style="min-height:120px;"></textarea>
        </div>

        

        <!-- Attachments -->
        <div class="gc-field">
          <label class="gc-label"><i class="fas fa-paperclip" style="margin-right:.3rem;"></i>Attachments</label>
          <div class="gc-attach-bar">
            <button type="button" class="gc-attach-btn" onclick="triggerFileUpload()"><i class="fas fa-file-upload"></i> Upload File</button>
            <button type="button" class="gc-attach-btn" onclick="addYouTube()"><i class="fab fa-youtube" style="color:#ff0000;"></i> YouTube</button>
            <button type="button" class="gc-attach-btn" onclick="addLink()"><i class="fas fa-link"></i> Link</button>
          </div>
          <input type="file" id="fileInput" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.txt">
          <div class="gc-attach-previews" id="attachPreviews"></div>
        </div>

       <!-- Quiz builder (shown only for quiz types) -->
        <div id="pmQuizSection" style="display:none;">

<!-- AI Generate trigger button -->
          <button type="button" class="aq-open-btn" onclick="openAqModal()">
            <i class="fas fa-wand-magic-sparkles"></i>
            Question Generator
            <span id="aqGeneratedBadge" style="display:none;background:var(--primary);color:#fff;font-size:.65rem;padding:.1rem .4rem;border-radius:20px;margin-left:.2rem;">0</span>
          </button>
          <div id="aiRegenerateWrap" style="display:none;margin-top:.6rem;display:none;align-items:center;gap:.6rem;flex-wrap:wrap;">
            <button type="button" onclick="generateQuizAI()"
              style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border-radius:8px;border:1.5px solid #f59e0b;background:#fffbf0;color:#92400e;font-family:inherit;font-size:.8rem;font-weight:700;cursor:pointer;transition:all .15s;">
              <i class="fas fa-rotate-right"></i> Retry Generation
            </button>
            <span style="font-size:.72rem;color:var(--text-muted);">Last attempt failed — retries with the same file &amp; settings</span>
          </div>

          <!-- Questions divider -->
          <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;">
            <div style="flex:1;height:1px;background:var(--border);"></div>
            <span style="font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Questions</span>
            <div style="flex:1;height:1px;background:var(--border);"></div>
            <span style="font-size:.72rem;color:var(--text-muted);" id="quizTotalPts">0 pts total</span>
          </div>
          <div id="quizBloomMix" style="display:flex;flex-wrap:wrap;gap:.35rem;margin:-.25rem 0 .75rem;"></div>

          <div id="quizQuestionsList"></div>
          <button type="button" class="add-q-btn" onclick="addQuestion()" style="margin-top:.5rem;"><i class="fas fa-plus"></i> Add Question Manually</button>
        </div>

      </div><!-- /gc-form-area -->
    </div><!-- /gc-left -->

    <!-- Right: settings panel -->
    <div class="gc-settings-panel" id="gcSettingsPanel">

      <!-- For (class name) -->
      <div class="gc-setting-card">
        <div class="gc-setting-label"><i class="fas fa-chalkboard"></i> For</div>
        <div class="gc-setting-val"><i class="fas fa-users"></i><span id="gcForClass">All students</span></div>
      </div>

      <!-- Due date + Points (shown for gradable types) -->
      <div id="gcGradableSettings" style="display:none;">
        <div class="gc-setting-card" id="gcPointsCard" style="margin-bottom:1rem;">
          <div class="gc-setting-label" id="gcPointsLabel"><i class="fas fa-star"></i> Points</div>
          <input type="number" class="gc-input" id="pmPoints" placeholder="e.g. 100" min="0" style="margin-top:.25rem;">
        </div>
       <div class="gc-setting-card" id="gcDueDateCard">
  <div class="gc-setting-label" id="gcDueDateLabel"><i class="fas fa-calendar-check"></i> Due Date</div>
  <div id="dueDateDisplay" onclick="openDueDateModal()"
       style="margin-top:.35rem;padding:.5rem .85rem;border-radius:10px;
              border:1.5px solid var(--border);background:var(--bg);
              font-size:.82rem;font-weight:600;color:var(--text-muted);
              cursor:pointer;display:flex;align-items:center;gap:.5rem;
              transition:all .2s;">
    <i class="fas fa-calendar-alt" style="color:var(--primary);font-size:.85rem;"></i>
    <span id="dueDateLabel">No due date</span>
    <i class="fas fa-chevron-right" style="margin-left:auto;font-size:.65rem;opacity:.5;"></i>
  </div>
  <input type="hidden" id="pmDueDate">
</div>
      </div>

      <!-- Quiz settings (shown only for quiz types) -->
      <div id="gcQuizSettings" style="display:none;">

        <div class="gc-setting-card" style="margin-bottom:1rem;">
          <div class="gc-setting-label"><i class="fas fa-door-open"></i> Open At</div>
          <input type="datetime-local" class="gc-input" id="pmOpenAt" style="margin-top:.25rem;">
          <div style="font-size:.7rem;color:var(--text-muted);margin-top:.25rem;">When students can start. Leave blank to open manually.</div>
        </div>

        <div class="gc-setting-card" style="margin-bottom:1rem;">
          <div class="gc-setting-label"><i class="fas fa-door-closed"></i> Close At</div>
          <input type="datetime-local" class="gc-input" id="pmCloseAt" style="margin-top:.25rem;">
          <div style="font-size:.7rem;color:var(--text-muted);margin-top:.25rem;">After this, no more submissions. Leave blank to close manually.</div>
        </div>

        <div class="gc-setting-card" style="margin-bottom:1rem;">
          <div class="gc-setting-label"><i class="fas fa-redo"></i> Max Attempts</div>
          <input type="number" class="gc-input" id="pmMaxAttempts" min="1" value="1" style="margin-top:.25rem;">
          <div style="font-size:.7rem;color:var(--text-muted);margin-top:.25rem;">Latest attempt counts as the score.</div>
        </div>

        <div class="gc-setting-card" style="margin-bottom:1rem;">
          <div class="gc-setting-label"><i class="fas fa-stopwatch"></i> Timing</div>
          <select class="gc-input" id="pmTimeMode" style="margin-top:.25rem;" onchange="onTimeModeChange()">
            <option value="none">No time limit</option>
            <option value="per_quiz">One timer for whole quiz</option>
            <option value="per_question">One timer per question</option>
          </select>

          <div id="pmTimePerQuizWrap" style="display:none;margin-top:.5rem;">
            <label style="font-size:.72rem;color:var(--text-muted);font-weight:600;">Total minutes</label>
            <input type="number" class="gc-input" id="pmTimePerQuizMinutes" min="1" placeholder="e.g. 30" style="margin-top:.2rem;">
          </div>

          <div id="pmTimePerQWrap" style="display:none;margin-top:.5rem;">
            <label style="font-size:.72rem;color:var(--text-muted);font-weight:600;">Default seconds per question</label>
            <input type="number" class="gc-input" id="pmTimePerQuestionSecs" min="1" step="1" inputmode="numeric" placeholder="e.g. 45" style="margin-top:.2rem;" oninput="onDefaultPerQuestionSecondsInput(this)">
            <div style="font-size:.7rem;color:var(--text-muted);margin-top:.25rem;">Default for all questions. You can change specific question seconds below.</div>
          </div>
        </div>

        <div class="gc-setting-card" style="margin-bottom:1rem;">
          <div class="gc-setting-label"><i class="fas fa-percent"></i> Passing Threshold</div>
          <input type="number" class="gc-input" id="pmPassingThreshold" min="0" max="100" step="0.01" placeholder="e.g. 75" style="margin-top:.25rem;">
          <div style="font-size:.7rem;color:var(--text-muted);margin-top:.25rem;">Percentage. Used in pass-rate analytics. Optional.</div>
        </div>

      </div>

      <!-- Semester info -->
      <div class="gc-setting-card">
        <div class="gc-setting-label"><i class="fas fa-calendar-alt"></i> Semester</div>
        <div class="gc-setting-val" style="font-size:.82rem;"><i class="fas fa-clock"></i><span id="gcSemesterInfo">—</span></div>
      </div>

    </div><!-- /gc-settings-panel -->

  </div><!-- /gc-body -->
</div><!-- /gc-modal-panel -->

<div class="lesson-week-backdrop" id="lessonWeekPickerBack" aria-hidden="true">
  <div class="lesson-week-modal" role="dialog" aria-modal="true" aria-labelledby="lessonWeekPickerTitle">
    <div class="lesson-week-head">
      <div class="lesson-week-title" id="lessonWeekPickerTitle">
        <i class="fas fa-calendar-week"></i>
        Select Lesson Week No.
      </div>
      <button type="button" class="lesson-week-close" onclick="closeLessonWeekPicker()" aria-label="Close week picker">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="lesson-week-body">
      <div class="lesson-week-summary" id="lessonWeekPickerSummary">
        <i class="fas fa-check-circle"></i>
        <span>Choose week no.</span>
      </div>
      <div class="lesson-week-note" id="lessonWeekPickerNote">
        Pick from the academic week count set in the admin panel.
      </div>
      <div class="lesson-week-grid" id="lessonWeekGrid"></div>
    </div>
    <div class="lesson-week-foot">
      <div class="lesson-week-foot-left">
        <button type="button" class="btn btn-ghost btn-sm" id="lessonWeekClearBtn" onclick="clearLessonWeekSelection()">Clear</button>
        <button type="button" class="btn btn-ghost btn-sm" id="lessonWeekNextBtn" onclick="selectNextAvailableLessonWeek()">Next available</button>
      </div>
      <button type="button" class="btn btn-primary btn-sm" id="lessonWeekDoneBtn" onclick="closeLessonWeekPicker(true)">
        <i class="fas fa-check"></i> Done
      </button>
    </div>
  </div>
</div>


<!-- ══ ASSIGN QUIZ WIZARD ══ -->
<div class="ar-date-backdrop" id="arDateRangeBack" aria-hidden="true">
  <div class="ar-date-modal" role="dialog" aria-modal="true" aria-labelledby="arDateRangeTitle">
    <div class="ar-date-head">
      <div class="ar-date-title" id="arDateRangeTitle">
        <i class="fas fa-calendar-days"></i>
        Select Date Covered
      </div>
      <button type="button" class="ar-date-close" id="arDateRangeCloseBtn" aria-label="Close date picker">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="ar-date-body">
      <div class="ar-date-summary" id="arDateRangeSummary">
        <i class="fas fa-calendar-days"></i>
        <span>Choose the first date, then the last date.</span>
      </div>
      <div class="ar-date-nav">
        <button type="button" class="ar-date-nav-btn" id="arDateRangePrevBtn" aria-label="Previous month"><i class="fas fa-chevron-left"></i></button>
        <div class="ar-date-month" id="arDateRangeMonth">Month YYYY</div>
        <button type="button" class="ar-date-nav-btn" id="arDateRangeNextBtn" aria-label="Next month"><i class="fas fa-chevron-right"></i></button>
      </div>
      <div class="ar-date-weekdays" aria-hidden="true">
        <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
      </div>
      <div class="ar-date-grid" id="arDateRangeGrid"></div>
    </div>
    <div class="ar-date-foot">
      <div class="ar-date-foot-left">
        <button type="button" class="btn btn-ghost btn-sm" id="arDateRangeClearBtn">Clear</button>
        <button type="button" class="btn btn-ghost btn-sm" id="arDateRangeTodayBtn">Session date</button>
      </div>
      <button type="button" class="btn btn-primary btn-sm" id="arDateRangeApplyBtn">
        <i class="fas fa-check"></i> Apply range
      </button>
    </div>
  </div>
</div>

<div id="wizOverlay">
  <div id="wizPanel" style="width:100%;max-width:640px;background:var(--surface);border-radius:22px;box-shadow:0 20px 70px rgba(0,0,0,.28);display:flex;flex-direction:column;max-height:90vh;overflow:hidden;">
    <div style="height:3px;background:var(--border);flex-shrink:0;">
      <div id="wizProgress" style="height:100%;background:linear-gradient(90deg,var(--primary),var(--accent));border-radius:3px;transition:width .4s;width:0%"></div>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem .85rem;flex-shrink:0;">
      <button id="wizBackBtn" onclick="wizBack()" style="width:34px;height:34px;border:none;background:var(--bg);border-radius:50%;cursor:pointer;color:var(--text-muted);display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;"><i class="fas fa-arrow-left"></i></button>
      <div style="flex:1;">
        <div id="wizStepLabel" style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--primary);margin-bottom:.12rem;"></div>
        <div id="wizStepTitle" style="font-size:1.05rem;font-weight:700;color:var(--text);"></div>
      </div>
      <button onclick="wizClose()" style="width:34px;height:34px;border:none;background:var(--bg);border-radius:50%;cursor:pointer;color:var(--text-muted);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-times"></i></button>
    </div>
    <div style="display:flex;align-items:center;gap:.35rem;justify-content:center;padding-bottom:.6rem;flex-shrink:0;">
      <div class="wiz-dot" id="wizDot1"></div>
      <div class="wiz-dot" id="wizDot2"></div>
      <div class="wiz-dot" id="wizDot3"></div>
    </div>
    <div id="wizBody" style="flex:1;overflow-y:auto;padding:.25rem 1.25rem 1rem;">
      <!-- Step 1: Mode -->
      <div id="wizStep1">
        <p style="font-size:.83rem;color:var(--text-muted);margin-bottom:1.1rem;line-height:1.55;">Choose how students will take this quiz.</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.85rem;">
          <div class="wiz-mode-card" id="wizCardLive" onclick="wizSelectMode('live')">
            <div class="wiz-mode-check live" id="wizCheckLive" style="display:none;"><i class="fas fa-check"></i></div>
            <div style="font-size:1.5rem;">⚡</div>
            <div class="wiz-mode-name">Live Mode</div>
            <div class="wiz-mode-desc">You control the start. Students join the lobby and begin together.</div>
            <span class="wiz-mode-badge" style="background:#fdecea;color:var(--danger);">Real-time</span>
          </div>
          <div class="wiz-mode-card" id="wizCardDue" onclick="wizSelectMode('due_date')">
            <div class="wiz-mode-check due" id="wizCheckDue" style="display:none;"><i class="fas fa-check"></i></div>
            <div style="font-size:1.5rem;"><i class="fas fa-calendar-day" aria-hidden="true"></i></div>
            <div class="wiz-mode-name">Scheduled Window</div>
            <div class="wiz-mode-desc">Students can only take the quiz within the allotted time window.</div>
            <span class="wiz-mode-badge" style="background:#e8f0fe;color:var(--accent);">Self-paced</span>
          </div>
        </div>
        <div id="wizDueDateWrap" style="display:none;background:linear-gradient(180deg,var(--bg),rgba(26,158,120,.03));border:1.5px solid var(--border);border-radius:14px;padding:.8rem .85rem;">
          <div style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.52px;color:var(--text-muted);margin-bottom:.55rem;"><i class="fas fa-clock" style="color:var(--accent);margin-right:.3rem;"></i> Scheduled Window</div>
          <div class="wiz-quick-row" style="margin-bottom:.45rem;">
            <button type="button" class="wiz-chip-btn" id="wizDatePresetToday" onclick="wizSetDatePreset(0)">Today</button>
            <button type="button" class="wiz-chip-btn" id="wizDatePresetTomorrow" onclick="wizSetDatePreset(1)">Tomorrow</button>
            <button type="button" class="wiz-chip-btn" id="wizDatePresetNext" onclick="wizSetDatePreset(2)">+2 days</button>
          </div>
          <div style="display:grid;grid-template-columns:1.1fr .9fr;gap:.6rem;">
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.55rem .6rem;">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.45rem;">
                <button type="button" onclick="wizSchedNavMonth(-1)" style="width:26px;height:26px;border:none;background:var(--bg);border-radius:8px;color:var(--text-muted);cursor:pointer;"><i class="fas fa-chevron-left"></i></button>
                <strong id="wizSchedMonthLabel" style="font-size:.78rem;color:var(--text);"></strong>
                <button type="button" onclick="wizSchedNavMonth(1)" style="width:26px;height:26px;border:none;background:var(--bg);border-radius:8px;color:var(--text-muted);cursor:pointer;"><i class="fas fa-chevron-right"></i></button>
              </div>
              <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;font-size:.62rem;font-weight:700;color:var(--text-muted);text-align:center;margin-bottom:.18rem;">
                <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
              </div>
              <div id="wizSchedDayGrid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;"></div>
              <input type="date" id="wizDateOnly" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" onchange="_wizSyncScheduleInputs();_wizRenderStep();">
            </div>
            <div>
              <label style="display:block;font-size:.7rem;font-weight:700;color:var(--text-muted);margin-bottom:.3rem;">Date</label>
              <div id="wizDateLabel" style="padding:.48rem .6rem;border:1px solid var(--border);border-radius:10px;background:var(--surface);font-size:.82rem;font-weight:700;color:var(--text);margin-bottom:.45rem;">Select date</div>
              <label for="wizStartTime" style="display:block;font-size:.7rem;font-weight:700;color:var(--text-muted);margin-bottom:.3rem;">Start Time</label>
              <select id="wizStartTime" class="gc-input" style="font-size:.86rem;padding:.5rem .65rem;" onchange="_wizSyncScheduleInputs();_wizRenderStep();"></select>
            </div>
          </div>
          <div style="margin-top:.55rem;">
            <label style="display:block;font-size:.7rem;font-weight:700;color:var(--text-muted);margin-bottom:.3rem;">Duration</label>
            <div class="wiz-quick-row">
              <button type="button" class="wiz-chip-btn" id="wizDurBtn05" onclick="wizSetDuration(0.5)">30m</button>
              <button type="button" class="wiz-chip-btn" id="wizDurBtn1" onclick="wizSetDuration(1)">1h</button>
              <button type="button" class="wiz-chip-btn" id="wizDurBtn2" onclick="wizSetDuration(2)">2h</button>
              <button type="button" class="wiz-chip-btn" id="wizDurBtn3" onclick="wizSetDuration(3)">3h</button>
              <button type="button" class="wiz-chip-btn" id="wizDurBtn4" onclick="wizSetDuration(4)">4h</button>
            </div>
            <input type="number" id="wizDurationHours" class="gc-input" min="0.5" step="0.5" max="12" value="1" style="display:none;" onchange="_wizSyncScheduleInputs();_wizRenderStep();">
            <div id="wizDurationLabel" style="margin-top:.35rem;font-size:.74rem;color:var(--text-muted);">
              Duration: <strong id="wizDurationLabelText" style="color:var(--text);font-weight:700;">1 hour</strong>
            </div>
          </div>
          <div style="margin-top:.45rem;padding:.45rem .6rem;border:1px dashed var(--border);border-radius:10px;background:var(--surface);font-size:.73rem;color:var(--text-muted);">
            <strong style="color:var(--text);font-weight:700;">Window:</strong>
            <span id="wizWindowPreview">Select date and start time</span>
          </div>
          <input type="hidden" id="wizOpenAt">
          <input type="hidden" id="wizDueDate">
        </div>
      </div>
      <!-- Step 2: Time -->
      <div id="wizStep2" style="display:none;">
        <p style="font-size:.83rem;color:var(--text-muted);margin-bottom:1.1rem;line-height:1.55;">Set a time limit for this quiz.</p>
        <div style="display:flex;flex-direction:column;gap:.6rem;">
          <div class="wiz-time-card" id="wizTc_none" onclick="wizSelectTime('none')">
            <div class="wiz-time-icon" id="wizTi_none">∞</div>
            <div style="flex:1;"><div class="wiz-time-name">No time limit</div><div class="wiz-time-desc">Students can take as long as they need.</div></div>
            <div class="wiz-radio" id="wizRad_none"></div>
          </div>
          <div class="wiz-time-card" id="wizTc_per_quiz" onclick="wizSelectTime('per_quiz')">
            <div class="wiz-time-icon" id="wizTi_per_quiz">⏱</div>
            <div style="flex:1;"><div class="wiz-time-name">Whole quiz timer</div><div class="wiz-time-desc">One countdown for all questions.</div></div>
            <div class="wiz-radio" id="wizRad_per_quiz"></div>
          </div>
          <div class="wiz-time-card" id="wizTc_per_question" onclick="wizSelectTime('per_question')">
            <div class="wiz-time-icon" id="wizTi_per_question">⏰</div>
            <div style="flex:1;"><div class="wiz-time-name">Per-question timer</div><div class="wiz-time-desc">Each question auto-advances when time is up.</div></div>
            <div class="wiz-radio" id="wizRad_per_question"></div>
          </div>
        </div>
        <div id="wizQuizMinWrap" style="display:none;margin-top:.75rem;background:var(--bg);border:1.5px solid var(--border);border-radius:12px;padding:1rem;">
          <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.55rem;">Total minutes for the whole quiz</div>
          <input type="number" id="wizTimeMinutes" class="gc-input" min="1" max="300" placeholder="e.g. 30" style="font-family:'DM Mono',monospace;font-size:1.05rem;font-weight:600;text-align:center;">
        </div>
        <div id="wizQSecsWrap" style="display:none;margin-top:.75rem;background:var(--bg);border:1.5px solid var(--border);border-radius:12px;padding:1rem;">
          <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.55rem;">Seconds per question</div>
          <input type="number" id="wizTimeSecs" class="gc-input" min="5" max="600" placeholder="e.g. 60" style="font-family:'DM Mono',monospace;font-size:1.05rem;font-weight:600;text-align:center;">
        </div>
      </div>
      <!-- Step 3: Review -->
      <div id="wizStep3" style="display:none;">
        <div style="text-align:center;padding:1rem 0 1.25rem;">
          <div style="width:64px;height:64px;border-radius:50%;background:var(--primary-light);border:2.5px solid var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto .8rem;">📤</div>
          <div style="font-size:1.05rem;font-weight:700;color:var(--text);">Ready to save!</div>
          <div style="font-size:.8rem;color:var(--text-muted);margin-top:.3rem;">Review settings, then click "Save Quiz" to save as draft.</div>
        </div>
        <div style="background:var(--bg);border:1.5px solid var(--border);border-radius:14px;overflow:hidden;">
          <div class="wiz-rev-item" onclick="wizGoTo(1)">
            <div class="wiz-rev-icon" id="wizRevModeIcon" style="background:#fdecea;color:var(--danger);">⚡</div>
            <div style="flex:1;"><div class="wiz-rev-label">Mode</div><div class="wiz-rev-val" id="wizRevMode">—</div></div>
            <i class="fas fa-pen" style="color:var(--text-muted);font-size:.72rem;"></i>
          </div>
          <div class="wiz-rev-item" onclick="wizGoTo(2)" style="border-top:1px solid var(--border);">
            <div class="wiz-rev-icon" style="background:var(--primary-light);color:var(--primary);">⏱</div>
            <div style="flex:1;"><div class="wiz-rev-label">Time</div><div class="wiz-rev-val" id="wizRevTime">—</div></div>
            <i class="fas fa-pen" style="color:var(--text-muted);font-size:.72rem;"></i>
          </div>
          <div class="wiz-rev-item" style="border-top:1px solid var(--border);cursor:default;">
            <div class="wiz-rev-icon" style="background:#eeedfe;color:#3c3489;">🔁</div>
            <div style="flex:1;">
              <div class="wiz-rev-label">Max Attempts <span style="font-size:.67rem;font-weight:400;color:var(--text-muted);">(0 = unlimited)</span></div>
              <div style="display:flex;align-items:center;gap:.5rem;margin-top:.3rem;">
                <button onclick="wizStepAttempts(-1)" class="wiz-step-btn">−</button>
                <span id="wizAttemptsVal" style="font-size:1rem;font-weight:700;font-family:'DM Mono',monospace;min-width:24px;text-align:center;">1</span>
                <button onclick="wizStepAttempts(1)" class="wiz-step-btn">+</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div style="padding:.85rem 1.25rem 1.1rem;border-top:1.5px solid var(--border);flex-shrink:0;background:var(--surface);">
      <button id="wizNextBtn" onclick="wizNext()" style="width:100%;padding:.8rem;border-radius:12px;border:none;font-family:inherit;font-size:.92rem;font-weight:700;cursor:pointer;background:var(--primary);color:#fff;box-shadow:0 3px 14px rgba(26,158,120,.3);transition:all .2s;display:flex;align-items:center;justify-content:center;gap:.5rem;">Continue <i class="fas fa-arrow-right"></i></button>
    </div>
  </div>
</div>

<!-- CUSTOM TYPE MODAL -->
<div class="ct-modal-back" id="ctModalBack">
  <div class="ct-modal">
    <div class="ct-modal-head">
      <h3><i class="fas fa-plus-circle" style="color:var(--primary);margin-right:.4rem;"></i> Create Custom Type</h3>
      <button class="pm-close" onclick="closeCtModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="ct-body">
      <div class="ct-field"><label class="ct-label">Label <span style="color:var(--danger)">*</span></label><input type="text" class="ct-input" id="ctLabel" placeholder="e.g. Exam – Midterm, Seatwork…"></div>
      <div class="ct-field"><label class="ct-label">Icon</label><div class="icon-grid" id="iconGrid"></div></div>
      <div class="ct-field"><label class="ct-label">Color</label><div style="display:flex;gap:.5rem;flex-wrap:wrap;" id="colorPicker"></div></div>
      <div class="ct-field">
        <label class="ct-label">Features</label>
        <div class="ct-toggle-row">
          <label class="ct-toggle"><input type="checkbox" id="ctIsGrad"> Has due date &amp; points</label>
          <label class="ct-toggle"><input type="checkbox" id="ctHasQuiz"> Has quiz (MCQ)</label>
          <label class="ct-toggle"><input type="checkbox" id="ctHasFile"> File submission</label>
        </div>
      </div>
    </div>
    <div class="ct-foot">
      <button class="btn btn-ghost btn-sm" onclick="closeCtModal()">Cancel</button>
      <button class="btn btn-primary btn-sm" onclick="saveCustomType()"><i class="fas fa-check"></i> Create</button>
    </div>
  </div>
</div>

<!-- EDIT CLASS MODAL -->
<div class="modal-back" id="editClassModalBack">
  <div class="post-modal" style="max-width:600px;">
    <div class="pm-header">
      <div class="pm-title">
        <div class="pm-type-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-cog"></i></div>
        <span>Edit Class</span>
      </div>
      <button class="pm-close" onclick="closeEditClassModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="pm-body">
      <div class="pm-row">
        <div class="pm-field"><label class="pm-label">Subject Code</label><input type="text" class="pm-input" id="ecSubjectCode" placeholder="e.g. DBSYS"></div>
        <div class="pm-field"><label class="pm-label">Subject Name</label><input type="text" class="pm-input" id="ecSubjectName" placeholder="e.g. Database Systems"></div>
      </div>
      <div class="pm-row">
        <div class="pm-field"><label class="pm-label">Year Level</label><input type="text" class="pm-input" id="ecYearLevel" placeholder="e.g. 3"></div>
        <div class="pm-field"><label class="pm-label">Section</label><input type="text" class="pm-input" id="ecSection" placeholder="e.g. 1"></div>
      </div>
      <div class="pm-row">
        <div class="pm-field"><label class="pm-label">Semester</label><input type="text" class="pm-input" id="ecSemester" placeholder="e.g. 1st Semester"></div>
        <div class="pm-field"><label class="pm-label">Course Code</label><input type="text" class="pm-input" id="ecCourseCode" placeholder="e.g. BSIT"></div>
      </div>
      <div class="pm-row">
        <div class="pm-field"><label class="pm-label">Class Days</label><input type="text" class="pm-input" id="ecDays" placeholder="e.g. Tue, Fri"></div>
        <div class="pm-field"><label class="pm-label">Schedule</label><input type="text" class="pm-input" id="ecSchedule" placeholder="07:30-13:00"></div>
      </div>
      <div class="pm-field">
        <label class="pm-label">Banner Color</label>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.35rem;" id="ecPalettePicker"></div>
      </div>
    </div>
    <div class="pm-footer" style="justify-content:space-between;">
      <button class="btn btn-danger btn-sm" onclick="confirmDeleteClass()"><i class="fas fa-trash"></i> Delete Class</button>
      <div style="display:flex;gap:.6rem;">
        <button class="btn btn-ghost" onclick="closeEditClassModal()">Cancel</button>
        <button class="btn btn-primary" id="ecSaveBtn" onclick="saveEditClass()"><i class="fas fa-check"></i> Save Changes</button>
      </div>
    </div>
  </div>
</div>
<!-- ══ SET MEET LINK MODAL ══ -->
<div class="modal-back" id="meetModalBack">
  <div class="post-modal" style="max-width:460px;">
    <div class="pm-header">
      <div class="pm-title">
        <div class="pm-type-icon" style="background:#e8f0fe;color:#1a73e8;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20 4.5l-5 3.5V5a2 2 0 00-2-2H4a2 2 0 00-2 2v14a2 2 0 002 2h9a2 2 0 002-2v-3l5 3.5A1 1 0 0022 18V6a1 1 0 00-2-1.5z"/>
          </svg>
        </div>
        <span>Set Google Meet Link</span>
      </div>
      <button class="pm-close" onclick="closeMeetModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="pm-body">
      <div class="pm-field">
        <label class="pm-label">Google Meet Link</label>
        <input type="url" class="pm-input" id="meetLinkInput"
               placeholder="https://meet.google.com/xxx-xxxx-xxx"
               style="font-family:'DM Mono',monospace;letter-spacing:.5px;">
        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.45rem;
                    display:flex;align-items:flex-start;gap:.35rem;line-height:1.5;">
          <i class="fas fa-info-circle" style="color:#1a73e8;margin-top:.1rem;flex-shrink:0;"></i>
          Paste the full Google Meet link.
        </div>
      </div>

      <!-- Live preview -->
      <div id="meetLinkPreview" style="display:none;padding:.55rem .85rem;
     background:var(--bg);border:1.5px solid #1a73e8;border-radius:var(--radius-sm);
     font-size:.75rem;color:var(--text-muted);word-break:break-all;
     align-items:center;gap:.4rem;">
        <i class="fas fa-link" style="color:#1a73e8;flex-shrink:0;"></i>
        <span id="meetLinkPreviewText"></span>
      </div>
    </div>

    <div class="pm-footer" style="justify-content:space-between;">
      <button class="btn btn-danger btn-sm" id="meetClearBtn"
              onclick="clearMeetLink()" style="display:none;">
        <i class="fas fa-trash"></i> Remove
      </button>
      <div style="display:flex;gap:.6rem;margin-left:auto;">
        <button class="btn btn-ghost" onclick="closeMeetModal()">Cancel</button>
        <button class="btn btn-primary" id="meetSaveBtn" onclick="saveMeetLink()">
          <i class="fas fa-check"></i> Save Link
        </button>
      </div>
    </div>
  </div>
</div>

<!-- FILE VIEWER MODAL -->
<div class="fv-backdrop" id="fvBackdrop">
  <div class="fv-shell">
    <div class="fv-toolbar">
      <div id="fvPageGroup" style="display:none;align-items:center;gap:.3rem;">
        <button class="fv-tb-btn" id="fvPrev"><i class="fas fa-chevron-left"></i></button>
        <span class="fv-page-info" id="fvPageInfo">1 / 1</span>
        <button class="fv-tb-btn" id="fvNext"><i class="fas fa-chevron-right"></i></button>
      </div>
      <div id="fvZoomGroup" style="display:none;align-items:center;gap:.3rem;">
        <div class="fv-sep"></div>
        <button class="fv-tb-btn" id="fvZoomOut"><i class="fas fa-search-minus"></i></button>
        <span class="fv-zoom-label" id="fvZoomLabel">100%</span>
        <button class="fv-tb-btn" id="fvZoomIn"><i class="fas fa-search-plus"></i></button>
      </div>
      <div class="fv-sep" id="fvSep1" style="display:none;"></div>
      <span class="fv-filename" id="fvFilename">File</span>
      <span class="fv-badge" id="fvBadge">FILE</span>
      <div class="fv-sep"></div>
      <button class="fv-panel-toggle-btn" id="fvPanelToggle">
        <i class="fas fa-sticky-note"></i> My Notes
        <span class="fv-tab-badge" id="fvNoteToolBadge">0</span>
      </button>
      <div class="fv-sep"></div>
      <a id="fvDownloadBtn" class="fv-tb-btn" target="_blank" download style="text-decoration:none;"><i class="fas fa-download"></i></a>
      <button class="fv-tb-btn danger" id="fvCloseBtn"><i class="fas fa-times"></i></button>
    </div>
    <div class="fv-layout">
      <div id="fvBody">
        <div class="fv-loading" id="fvLoading"><div class="fv-spinner"></div><span>Loading…</span></div>
      </div>
      <div class="fv-side-panel" id="fvSidePanel">
        <div class="fv-panel-tabs">
          <div class="fv-panel-header-title">
            <i class="fas fa-sticky-note"></i> My Notes
            <span class="fv-tab-badge" id="fvNoteTabBadge">0</span>
          </div>
          <button class="fv-panel-close" id="fvPanelClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="fv-panel-page-strip">
          <i class="fas fa-file-alt"></i>
          <span id="fvPageStripLabel">This file</span>
          <span class="fv-private-badge"><i class="fas fa-lock"></i> Only you</span>
        </div>
        <div class="fv-notes-pane">
          <div class="fv-item-list" id="fvNoteList">
            <div class="fv-empty-msg"><i class="fas fa-sticky-note"></i>No notes on this page yet.</div>
          </div>
          <div class="fv-input-area">
            <div class="fv-input-hint"><i class="fas fa-lock"></i> Private — only visible to you</div>
            <div class="fv-input-row">
              <textarea class="fv-textarea" id="fvNoteInput" placeholder="Add a note… (Enter to save)" rows="1"
                onkeydown="fvNoteKey(event)" oninput="fvTaResize(this)"></textarea>
              <button class="fv-send-btn" id="fvNoteSend"><i class="fas fa-check"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
let _ddYear    = new Date().getFullYear();
let _ddMonth   = new Date().getMonth();
let _ddDay     = null;
let _ddQuickDay = null;

const CLASS_ID          = <?= json_encode($class_id) ?>;
const SESSION_USER_ID   = <?= json_encode($_SESSION['user_id']) ?>;
const SESSION_USER_TYPE = 'faculty';
const BASE_URL = window.location.origin + window.location.pathname.replace('classRoom.php','');

let classData=null,allPosts=[],allPeople=[],allPending=[],postTypes=[];
let streamTypeFilter='all';
let streamSearchQuery='';
let streamLessonPeriodFilter='all';
let gradebookData=null;
let gbLoadedOnce = false;
let gbIsLoading = false;
let gbLastLoadedAt = 0;
let gbViewMode='score';
let gbActiveFilter='all';
let gbSearchQ='';
let gbAutoRefreshTimer = null;
let pendingFiles=[],pendingLinks=[],quizQuestions=[];
let activePmTypeId=null;
let userTypedTitle=false;
let _examMode='questionnaire';
let _crPollTimer = null;
let _skipDraftResume = false;
let _postDraftTimer = null;
let _postDraftRestoreInProgress = false;
let _postDraftSuspendSave = false;
let lessonWeekCount = 18;
let lessonWeekCountLoaded = false;
let selectedLessonWeeks = [];
let lessonWeekPickerMode = 'lesson';
let selectedReportWeek = null;
let reportWeekPickerCommit = null;

const PALETTE_GRADIENTS = {
  'b-forest': 'linear-gradient(135deg,#115e59 0%,#0f766e 48%,#0d9488 100%)',
  'b-ocean': 'linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 52%,#0284c7 100%)',
  'b-sunset': 'linear-gradient(135deg,#9a3412 0%,#c2410c 52%,#ea580c 100%)',
  'b-plum': 'linear-gradient(135deg,#581c87 0%,#6d28d9 52%,#7c3aed 100%)',
  'b-teal': 'linear-gradient(135deg,#164e63 0%,#0e7490 52%,#0891b2 100%)',
  'b-rose': 'linear-gradient(135deg,#881337 0%,#be123c 52%,#e11d48 100%)',
  'b-slate': 'linear-gradient(135deg,#1e293b 0%,#334155 56%,#475569 100%)',
  'b-indigo': 'linear-gradient(135deg,#312e81 0%,#4338ca 52%,#4f46e5 100%)'
};
const LEGACY_PALETTE_GRADIENTS = {
  'linear-gradient(135deg,#1a9e78,#0a5c45)': 'b-forest',
  'linear-gradient(135deg,#1f73db,#0d47a1)': 'b-ocean',
  'linear-gradient(135deg,#f57c00,#bf360c)': 'b-sunset',
  'linear-gradient(135deg,#7b1fa2,#4a148c)': 'b-plum',
  'linear-gradient(135deg,#00838f,#00474d)': 'b-teal',
  'linear-gradient(135deg,#c62828,#880e4f)': 'b-rose',
  'linear-gradient(135deg,#455a64,#263238)': 'b-slate',
  'linear-gradient(135deg,#3949ab,#1a237e)': 'b-indigo'
};
const PALETTE_KEYS = Object.keys(PALETTE_GRADIENTS);
const PALETTES = PALETTE_KEYS.map(key => PALETTE_GRADIENTS[key]);
const PALETTE_LOOKUP = {};
const compactPalette = value => String(value || '').toLowerCase().replace(/\s+/g, '');
Object.entries(PALETTE_GRADIENTS).forEach(([key, gradient]) => { PALETTE_LOOKUP[compactPalette(gradient)] = key; });
Object.entries(LEGACY_PALETTE_GRADIENTS).forEach(([gradient, key]) => { PALETTE_LOOKUP[compactPalette(gradient)] = key; });
let subjectPaletteMap = {};
let paletteMapLoaded = false;
const ICON_OPTS=['fa-book-open','fa-pencil-ruler','fa-question-circle','fa-clipboard-list','fa-file-signature','fa-bullhorn','fa-paperclip','fa-star','fa-flask','fa-laptop-code','fa-chalkboard','fa-chart-bar','fa-award','fa-user-graduate','fa-pen-nib'];
const COLOR_OPTS=[{bg:'#e8f5e9',text:'#2e7d32',label:'Green'},{bg:'#e3f2fd',text:'#1565c0',label:'Blue'},{bg:'#f3e5f5',text:'#6a1b9a',label:'Purple'},{bg:'#fff3e0',text:'#e65100',label:'Orange'},{bg:'#fce4ec',text:'#c62828',label:'Red'},{bg:'#e0f7fa',text:'#00695c',label:'Teal'},{bg:'#f3f4f6',text:'#374151',label:'Gray'},{bg:'#fffde7',text:'#f57f17',label:'Yellow'}];
let selectedIcon='fa-file-alt',selectedColor=COLOR_OPTS[0];

function _toastText(msg, type='success') {
  const s = String(msg || '').toLowerCase();
  if (type === 'error') {
    if (s.includes('network')) return 'Check connection and try again.';
    return String(msg || 'Please try again.');
  }
  if (type === 'info') return 'Update received.';
  if (s.includes('post')) return 'Successfully posted!';
  if (s.includes('create') || s.includes('created')) return 'Successfully created!';
  if (s.includes('save') || s.includes('saved') || s.includes('update') || s.includes('updated')) return 'Successfully saved!';
  if (s.includes('delete') || s.includes('removed')) return 'Successfully removed!';
  if (s.includes('invite')) return 'Successfully sent!';
  if (s.includes('upload')) return 'Successfully uploaded!';
  return 'Success.';
}
function toast(msg,type='success'){
  const cleanMsg = String(msg || '').trim();
  const isError = type === 'error';
  Swal.fire({
    toast:true,
    position:'top-end',
    icon:type,
    title:isError ? 'Action failed' : _toastText(cleanMsg,type),
    text:isError ? _toastText(cleanMsg,type) : undefined,
    showConfirmButton:false,
    timer:isError ? 3200 : 2200,
    timerProgressBar:true,
    showClass:{popup:'animate__animated animate__fadeInDown'},
    hideClass:{popup:'animate__animated animate__fadeOutUp'},
    didOpen:(el)=>{
      el.style.cursor = 'pointer';
      el.addEventListener('click', ()=>Swal.close());
      el.addEventListener('mouseenter', Swal.stopTimer);
      el.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
}

(function(){
  if(localStorage.getItem('tl_dark')==='1') document.body.classList.add('dark');
  const btn=document.getElementById('darkToggle');
  btn.addEventListener('click',()=>{const d=document.body.classList.toggle('dark');localStorage.setItem('tl_dark',d?'1':'0');btn.querySelector('i').className=d?'fas fa-sun':'fas fa-moon';});
  if(localStorage.getItem('tl_dark')==='1') btn.querySelector('i').className='fas fa-sun';
})();

document.querySelectorAll('.tab-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    // Avoid unnecessary tab teardown/rebuild when clicking the already active tab.
    if (btn.classList.contains('active')) return;
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-section').forEach(s=>s.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-'+btn.dataset.tab).classList.add('active');
  });
});

document.addEventListener('DOMContentLoaded',async()=>{
  await loadPaletteMap();
  await Promise.all([loadClassroom(),loadPostTypes(),loadLessonWeekCount()]);
  renderStream();
  renderClasswork();
  buildIconGrid();
  buildColorPicker();
  updateAiTotalQuestions();
  // Keep people/join-request counts fresh without full page refresh.
  _crPollTimer = setInterval(pollClassroom, 8000);

  // Auto-open tab from URL
  const urlTab = new URLSearchParams(location.search).get('tab');
  if (urlTab) {
    const resolvedTab = urlTab === 'attendance' ? 'report' : urlTab;
    const tabBtn = document.querySelector(`.tab-btn[data-tab="${resolvedTab}"]`);
    if (tabBtn) tabBtn.click();
  }
});

const gradesTabBtn = document.getElementById('gradesTabBtn');
if (gradesTabBtn) {
  gradesTabBtn.addEventListener('click', () => {
    // Load immediately on first open; then reuse current data and refresh quietly.
    const now = Date.now();
    if (!gbLoadedOnce) {
      loadGradebook({ showLoading: true, force: true });
    } else if (now - gbLastLoadedAt > 30000) {
      loadGradebook({ showLoading: false, force: false });
    } else {
      renderGradebook();
    }
    startGradebookAutoRefresh();
  });
}

function startGradebookAutoRefresh() {
  if (gbAutoRefreshTimer) return;
  gbAutoRefreshTimer = setInterval(() => {
    const pane = document.getElementById('tab-grades');
    if (!pane || !pane.classList.contains('active')) return;
    if (document.visibilityState !== 'visible') return;
    loadGradebook({ showLoading: false, force: false });
  }, 8000);
}

const gbExportPdfBtn = document.getElementById('gbExportPdf');
if (gbExportPdfBtn) {
  gbExportPdfBtn.addEventListener('click', () => {
    window.open(`API/facultyUI/classroom/gradebook/export_pdf.php?class_id=${encodeURIComponent(CLASS_ID)}`, '_blank');
  });
}
const gbExportExcelBtn = document.getElementById('gbExportExcel');
if (gbExportExcelBtn) {
  gbExportExcelBtn.addEventListener('click', () => {
    window.location.href = `API/facultyUI/classroom/gradebook/export_excel.php?class_id=${encodeURIComponent(CLASS_ID)}`;
  });
}
async function loadGradebook(opts = {}) {
  const { showLoading = true, force = false } = opts;
  const body = document.getElementById('gbBody');
  if (!body) return;
  if (gbIsLoading) return;
  gbIsLoading = true;
  if (showLoading && !gbLoadedOnce) {
    body.innerHTML = `<tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:1rem;"><i class="fas fa-spinner fa-spin"></i> Loading gradebook...</td></tr>`;
  }
  try {
    const url = force
      ? `API/facultyUI/classroom/gradebook/get_gradebook.php?class_id=${encodeURIComponent(CLASS_ID)}&_t=${Date.now()}`
      : `API/facultyUI/classroom/gradebook/get_gradebook.php?class_id=${encodeURIComponent(CLASS_ID)}`;
    const res = await fetch(url, { cache: force ? 'no-store' : 'default' });
    const data = await res.json();
    if (data.status !== 'success') {
      body.innerHTML = `<tr><td colspan="9" style="text-align:center;color:#c62828;padding:1rem;">${esc(data.message || 'Failed to load gradebook')}</td></tr>`;
      gbIsLoading = false;
      return;
    }
    gradebookData = data.gradebook || null;
    gbLoadedOnce = true;
    gbLastLoadedAt = Date.now();
    renderGradebook();
  } catch (e) {
    console.error(e);
    body.innerHTML = `<tr><td colspan="9" style="text-align:center;color:#c62828;padding:1rem;">Network error while loading gradebook.</td></tr>`;
  } finally {
    gbIsLoading = false;
  }
}

function renderGradebookLegacy() {
  const body = document.getElementById('gbBody');
  if (!body) return;
  const students = Array.isArray(gradebookData?.students) ? gradebookData.students : [];
  const assessments = Array.isArray(gradebookData?.assessments) ? gradebookData.assessments : [];
  const normalizeGbType = (v) => {
    const raw = (v && typeof v === 'object') ? (v.display_category || v.category || '') : v;
    const t = String(raw || '').toLowerCase();
    if (t === 'activity' || t === 'activities') return 'activities';
    if (t === 'assignment' || t === 'assignments') return 'assignment';
    if (t === 'quiz' || t === 'quizzes') return 'quiz';
    if (t === 'exam' || t === 'exams') return 'exam';
    if (t === 'recitation' || t === 'recitations') return 'recitation';
    return t || 'activities';
  };
  const assessmentDisplayTitle = (c, idx) => {
    const raw = String(c?.title || '').trim();
    const type = normalizeGbType(c);
    if (raw) {
      if (type === 'quiz' && !/^quiz\b/i.test(raw)) return `Quiz ${idx + 1}: ${raw}`;
      return raw;
    }
    if (type === 'quiz') return `Quiz ${idx + 1}`;
    if (type === 'activities') return `Activity ${idx + 1}`;
    if (type === 'assignment') return `Assignment ${idx + 1}`;
    if (type === 'exam') return `Exam ${idx + 1}`;
    return `Assessment ${idx + 1}`;
  };

  const scoreClass = (pct) => (pct >= 90 ? 's-perfect' : pct >= 75 ? 's-good' : pct >= 60 ? 's-avg' : 's-low');
  const finalClass = (grade) => (grade >= 85 ? 'fv-high' : grade >= 75 ? 'fv-mid' : grade > 0 ? 'fv-low' : 'fv-none');


  const summaryEl = document.getElementById('gbSummaryLabel');
  if (summaryEl) {
    summaryEl.textContent = `Generated: ${gradebookData?.generated_at || '—'} · Students: ${students.length} · Read-only from submissions`;
  }

  if (!students.length) {
    body.innerHTML = `<tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:1rem;">No student records yet.</td></tr>`;
    return;
  }
  const showScore = gbActiveFilter === 'all' || gbActiveFilter === 'score';
  const baseVisibleCols = assessments.filter(c => gbActiveFilter !== 'score' && (gbActiveFilter === 'all' || normalizeGbType(c) === gbActiveFilter));
  // In "All" view, keep same post types contiguous so headers (e.g., QUIZ) are merged once.
  const visibleCols = gbActiveFilter === 'all'
    ? baseVisibleCols
        .map((c, i) => ({ c, i }))
        .sort((a, b) => {
          const rank = { quiz: 1, activities: 2, assignment: 3, exam: 4, recitation: 5 };
          const ta = normalizeGbType(a.c);
          const tb = normalizeGbType(b.c);
          const ra = rank[ta] ?? 99;
          const rb = rank[tb] ?? 99;
          if (ra !== rb) return ra - rb;
          return a.i - b.i; // stable within same type
        })
        .map(x => x.c)
    : baseVisibleCols;
  const groupRow = document.getElementById('groupRow');
  const headRow = document.getElementById('headRow');
  if (!groupRow || !headRow) return;

  const showGroupHeader = gbActiveFilter === 'all';
  groupRow.innerHTML = `
    <th class="gh-empty" rowspan="${showGroupHeader ? '2' : '1'}">#</th>
    <th class="gh-empty" rowspan="${showGroupHeader ? '2' : '1'}">Student #</th>
    <th class="gh-empty" rowspan="${showGroupHeader ? '2' : '1'}">Name</th>`;

  if (showGroupHeader) {
    const groups = [];
    let cur = null;
    visibleCols.forEach(c => {
      const type = normalizeGbType(c);
      if (!cur || cur.type !== type) { cur = { type, count: 1 }; groups.push(cur); }
      else cur.count++;
    });
    const gLabels = { quiz:'QUIZ', activities:'ACTIVITY', assignment:'ASSIGNMENT', exam:'EXAM', recitation:'RECITATION' };
    groups.forEach(g => { groupRow.innerHTML += `<th colspan="${g.count}" class="gh-${g.type}">${gLabels[g.type] || g.type}</th>`; });
    if (showScore) groupRow.innerHTML += `<th class="gh-score">SCORE</th>`;
    if (!groups.length && !showScore) {
      groupRow.innerHTML += `<th class="gh-empty" rowspan="2">No assessments</th>`;
    }
  } else if (!visibleCols.length && !showScore) {
    groupRow.innerHTML += `<th class="gh-empty">No assessments</th>`;
  }

  headRow.innerHTML = '';
  if (showGroupHeader) {
    visibleCols.forEach((c, i) => {
      const t = normalizeGbType(c);
      headRow.innerHTML += `<th class="hh-${esc(t)}">${esc(assessmentDisplayTitle(c, i))}</th>`;
    });
    if (showScore) headRow.innerHTML += `<th class="hh-score">Final</th>`;
    headRow.style.display = '';
  } else {
    // In specific type view, hide the category group header row and show only post columns.
    visibleCols.forEach((c, i) => {
      const t = normalizeGbType(c);
      groupRow.innerHTML += `<th class="hh-${esc(t)}">${esc(assessmentDisplayTitle(c, i))}</th>`;
    });
    if (showScore) groupRow.innerHTML += `<th class="hh-score">Final</th>`;
    headRow.style.display = 'none';
  }

  const q = gbSearchQ.toLowerCase().trim();
  const filtered = students.filter(st => !q || String(st.student_name||'').toLowerCase().includes(q) || String(st.student_number||'').toLowerCase().includes(q));

  body.innerHTML = filtered.map((st, idx) => {
    let row = `<tr class="row-data"><td><div class="cell-pad">${idx+1}</div></td><td><div class="cell-pad">${esc(st.student_number||'—')}</div></td><td><div class="cell-pad" style="font-weight:700;">${esc(st.student_name||'Unknown')}</div></td>`;
    visibleCols.forEach(col => {
      const ss = col.scores?.[st.student_id] || null;
      if (!ss || ss.score === null || ss.score === undefined) {
        row += `<td><div class="score-wrap"><span class="score-miss">—</span></div></td>`;
      } else {
        const pct = Number(ss.percentage || 0);
        const display = gbViewMode === 'pct'
          ? `<span class="score-val ${scoreClass(pct)}">${Math.round(pct)}%</span>`
          : `<span class="score-val ${scoreClass(pct)}">${Number(ss.score).toFixed(2).replace(/\.00$/,'')}<span class="score-max">/${Number(col.max_score||0).toFixed(0)}</span></span>`;
        row += `<td><div class="score-cell-btn" onclick="openGradebookSubmissionDetail('${esc(st.student_id)}','${esc(col.post_id)}')" title="View submission details">${display}<i class="fa-solid fa-eye view-icon"></i></div></td>`;
      }
    });
    if (showScore) {
      const finalGrade = Number(st.final_grade || 0);
      const finalText = finalGrade > 0 ? `${finalGrade.toFixed(2).replace(/\.00$/,'')}%` : '—';
      row += `<td><div class="final-wrap"><span class="final-val ${finalClass(finalGrade)}">${finalText}</span></div></td>`;
    }
    row += `</tr>`;
    return row;
  }).join('');

}

function renderGradebook() {
  const body = document.getElementById('gbBody');
  const periodRow = document.getElementById('periodRow');
  const groupRow = document.getElementById('groupRow');
  const headRow = document.getElementById('headRow');
  if (!body || !periodRow || !groupRow || !headRow) return;

  const students = Array.isArray(gradebookData?.students) ? gradebookData.students : [];
  const assessments = Array.isArray(gradebookData?.assessments) ? gradebookData.assessments : [];
  const periods = [
    { key:'prelim', label:'PRELIM', short:'PG' },
    { key:'midterm', label:'MIDTERM', short:'MG' },
    { key:'finals', label:'FINALS', short:'FG' }
  ];
  const categoryRank = { quiz: 1, activities: 2, assignment: 3, recitation: 4, exam: 5 };
  const categoryLabels = {
    attendance:'ATTENDANCE',
    quiz:'QUIZ / SEATWORKS / LAB',
    activities:'ACTIVITY',
    assignment:'ASSIGNMENT',
    recitation:'RECITATION',
    exam:'EXAM'
  };
  const normalizeGbType = (v) => {
    const raw = (v && typeof v === 'object') ? (v.display_category || v.category || '') : v;
    const t = String(raw || '').toLowerCase();
    if (t === 'activity' || t === 'activities') return 'activities';
    if (t === 'assignment' || t === 'assignments') return 'assignment';
    if (t === 'quiz' || t === 'quizzes') return 'quiz';
    if (t === 'exam' || t === 'exams') return 'exam';
    if (t === 'recitation' || t === 'recitations') return 'recitation';
    return t || 'activities';
  };
  const assessmentDisplayTitle = (c, idx) => {
    const raw = String(c?.title || '').trim();
    const type = normalizeGbType(c);
    if (raw) {
      if (type === 'quiz' && !/^quiz\b/i.test(raw)) return `Quiz ${idx + 1}: ${raw}`;
      return raw;
    }
    if (type === 'quiz') return `Quiz ${idx + 1}`;
    if (type === 'activities') return `Activity ${idx + 1}`;
    if (type === 'assignment') return `Assignment ${idx + 1}`;
    if (type === 'exam') return `Exam ${idx + 1}`;
    return `Assessment ${idx + 1}`;
  };
  const explicitPeriod = (c) => {
    const p = String(c?.grading_period || c?.lesson_period || c?.period || '').toLowerCase().trim();
    if (['prelim','midterm','finals'].includes(p)) return p;
    const text = [c?.title, c?.type_label, c?.post_type, c?.category, c?.display_category].map(x => String(x || '').toLowerCase()).join(' ');
    if (/\bpre[\s-]*lim\b|\bprelim\b/.test(text)) return 'prelim';
    if (/\bmid[\s-]*term\b|\bmidterm\b/.test(text)) return 'midterm';
    if (/\bfinals?\b|\bfinal[\s-]*exam\b/.test(text)) return 'finals';
    return '';
  };
  const dated = assessments
    .map((c, i) => ({ c, i }))
    .sort((a, b) => {
      const da = new Date(a.c?.due_date || a.c?.date_created || 0).getTime() || 0;
      const db = new Date(b.c?.due_date || b.c?.date_created || 0).getTime() || 0;
      return da === db ? a.i - b.i : da - db;
    });
  const fallbackPeriod = new Map();
  dated.forEach((item, idx) => {
    const bucket = dated.length ? Math.min(2, Math.floor((idx / dated.length) * 3)) : 0;
    fallbackPeriod.set(String(item.c?.post_id || item.i), periods[bucket].key);
  });
  const allCols = assessments.map((c, i) => {
    const type = normalizeGbType(c);
    const postKey = String(c?.post_id || i);
    return {
      ...c,
      _idx: i,
      _type: type,
      _period: explicitPeriod(c) || fallbackPeriod.get(postKey) || 'prelim',
      _title: assessmentDisplayTitle(c, i)
    };
  });
  const gradeSheetWeights = {
    prelim: { attendance: 0.10, coursework: 0.40, recitation: 0.10, exam: 0.40 },
    midterm: { attendance: 0.05, coursework: 0.45, recitation: 0.10, exam: 0.40 },
    finals: { attendance: 0.10, coursework: 0.40, recitation: 0.10, exam: 0.40 }
  };
  const attendancePeriods = gradebookData?.attendance_periods || {};
  const scoreEquivalent = (score, maxScore) => {
    const raw = Number(score);
    const max = Number(maxScore);
    if (!Number.isFinite(raw) || !Number.isFinite(max) || max <= 0) return null;
    return Math.round((raw / max) * 50 + 50);
  };
  const componentAverage = (values) => {
    const valid = values.filter(v => Number.isFinite(Number(v)));
    if (!valid.length) return null;
    return valid.reduce((sum, v) => sum + Number(v), 0) / valid.length;
  };
  const attendanceEquivalentFor = (st, periodKey) => {
    const stats = attendancePeriods?.[st.student_id]?.[periodKey] || attendancePeriods?.[String(st.student_id)]?.[periodKey] || null;
    if (!stats) return null;
    const equivalent = Number(stats.equivalent);
    if (Number.isFinite(equivalent) && equivalent > 0) return equivalent;
    const percentage = Number(stats.percentage);
    if (Number.isFinite(percentage)) return Math.round((percentage * 0.5) + 50);
    return null;
  };
  const gradeComponentFor = (col) => {
    const type = col._type || normalizeGbType(col);
    if (type === 'exam') return 'exam';
    if (type === 'recitation') return 'recitation';
    return 'coursework';
  };
  const equivalentFor = (grade) => {
    const g = Number(grade || 0);
    if (g <= 0) return '-';
    if (g >= 98) return '1.00';
    if (g >= 95) return '1.25';
    if (g >= 92) return '1.50';
    if (g >= 89) return '1.75';
    if (g >= 86) return '2.00';
    if (g >= 83) return '2.25';
    if (g >= 80) return '2.50';
    if (g >= 77) return '2.75';
    if (g >= 75) return '3.00';
    return '5.00';
  };
  const fmtGrade = (grade) => {
    const n = Number(grade || 0);
    return n > 0 ? `${n.toFixed(2).replace(/\.00$/,'')}%` : '-';
  };
  const scoreClass = (pct) => (pct >= 90 ? 's-perfect' : pct >= 75 ? 's-good' : pct >= 60 ? 's-avg' : 's-low');
  const finalClass = (grade) => (grade >= 85 ? 'fv-high' : grade >= 75 ? 'fv-mid' : grade > 0 ? 'fv-low' : 'fv-none');
  const periodGradeFor = (st, key) => {
    const components = {
      attendance: [],
      coursework: [],
      recitation: [],
      exam: []
    };
    const attendanceEquivalent = attendanceEquivalentFor(st, key);
    if (attendanceEquivalent !== null) components.attendance.push(attendanceEquivalent);
    let hasAssessmentEquivalent = false;
    allCols.filter(c => c._period === key).forEach(col => {
      const ss = col.scores?.[st.student_id] || col.scores?.[String(st.student_id)] || null;
      if (!ss || ss.score === null || ss.score === undefined) return;
      const equivalent = scoreEquivalent(ss.score, col.max_score);
      if (equivalent === null) return;
      hasAssessmentEquivalent = true;
      components[gradeComponentFor(col)].push(equivalent);
    });
    if (!hasAssessmentEquivalent) return null;

    const profile = gradeSheetWeights[key] || gradeSheetWeights.prelim;
    let weighted = 0;
    let usedWeight = 0;
    Object.keys(profile).forEach(component => {
      const avg = componentAverage(components[component] || []);
      if (avg === null) return;
      weighted += avg * profile[component];
      usedWeight += profile[component];
    });
    if (!usedWeight) return null;
    return Math.round((weighted / usedWeight) * 100) / 100;
  };
  const sheetFinalFor = (st) => {
    const vals = periods.map(p => periodGradeFor(st, p.key)).filter(v => v !== null);
    if (vals.length) return Math.round((vals.reduce((sum, v) => sum + v, 0) / vals.length) * 100) / 100;
    return Number(st.final_grade || 0);
  };
  const setText = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value || '-'; };
  const c = classData || {};
  const instructor = [c.last_name, c.first_name].filter(Boolean).join(', ') || [c.first_name, c.last_name].filter(Boolean).join(' ');
  setText('gbMetaInstructor', instructor);
  setText('gbMetaSchoolYear', c.school_year || c.academic_year || (String(c.class_semester || '').match(/\d{4}\s*-\s*\d{4}/)?.[0] || '-'));
  setText('gbMetaSubjectCode', c.subject_code);
  setText('gbMetaSemester', c.class_semester);
  setText('gbMetaDescription', c.subject_name);
  setText('gbMetaCourse', c.course_code || c.course_name);
  setText('gbMetaUnits', c.units || c.subject_units || '-');
  setText('gbMetaSection', c.section || c.class_code);

  const enrichedStudents = students.map(st => {
    const periodGrades = Object.fromEntries(periods.map(p => [p.key, periodGradeFor(st, p.key)]));
    const finalGrade = sheetFinalFor(st);
    return { ...st, _periodGrades: periodGrades, _sheetFinal: finalGrade, _equivalent: equivalentFor(finalGrade) };
  });
  setText('gbPassedCount', String(enrichedStudents.filter(st => st._sheetFinal >= 75).length));
  setText('gbFailedCount', String(enrichedStudents.filter(st => st._sheetFinal > 0 && st._sheetFinal < 75).length));
  setText('gbNoGradeCount', String(enrichedStudents.filter(st => !st._sheetFinal).length));

  const summaryEl = document.getElementById('gbSummaryLabel');
  if (summaryEl) {
    summaryEl.textContent = `Generated: ${gradebookData?.generated_at || '-'} | Students: ${students.length} | Institutional grading-sheet format`;
  }

  const fixedHeaders = [
    ['#', 'gb-col-no gb-sticky-1'],
    ['Student Number', 'gb-col-id gb-sticky-2'],
    ['Student Name', 'gb-col-name gb-sticky-3'],
    ['Registration Form Number', 'gb-col-reg'],
    ['Equivalent', 'gb-col-small'],
    ['Final Grade', 'gb-col-small'],
    ['PG', 'gb-col-small'],
    ['MG', 'gb-col-small'],
    ['FG', 'gb-col-small']
  ];
  periodRow.innerHTML = fixedHeaders.map(([label, cls]) => `<th rowspan="3" class="gh-empty ${cls}">${label}</th>`).join('');
  groupRow.innerHTML = '';
  headRow.innerHTML = '';

  if (!students.length) {
    groupRow.style.display = 'none';
    headRow.style.display = 'none';
    body.innerHTML = `<tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:1rem;">No student records yet.</td></tr>`;
    return;
  }

  const showDetail = gbActiveFilter !== 'score';
  const visibleCols = showDetail
    ? allCols
        .filter(col => gbActiveFilter === 'all' || col._type === gbActiveFilter)
        .sort((a, b) => {
          const pa = periods.findIndex(p => p.key === a._period);
          const pb = periods.findIndex(p => p.key === b._period);
          if (pa !== pb) return pa - pb;
          const ra = categoryRank[a._type] ?? 99;
          const rb = categoryRank[b._type] ?? 99;
          return ra === rb ? a._idx - b._idx : ra - rb;
        })
    : [];
  const showAttendanceComponent = showDetail && gbActiveFilter === 'all';
  const blocks = showDetail
    ? periods.map(period => {
        const cols = visibleCols.filter(col => col._period === period.key);
        const groups = [];
        if (showAttendanceComponent) groups.push({ type:'attendance', count:1 });
        let cur = null;
        cols.forEach(col => {
          if (!cur || cur.type !== col._type) {
            cur = { type: col._type, count: 1 };
            groups.push(cur);
          } else cur.count++;
        });
        const componentCols = cols.length + (showAttendanceComponent ? 1 : 0);
        if (componentCols) groups.push({ type:'period', count:1 });
        return { ...period, cols, groups, hasAttendance: showAttendanceComponent, span: componentCols ? componentCols + 1 : 0 };
      }).filter(block => block.span > 0)
    : [];
  const showEmptyAssessmentColumn = showDetail && !blocks.length;

  if (blocks.length) {
    blocks.forEach(block => {
      periodRow.innerHTML += `<th colspan="${block.span}" class="rp-${block.key}">${block.label}</th>`;
      block.groups.forEach(g => {
        const label = g.type === 'period' ? `${block.short} GRADE` : (categoryLabels[g.type] || g.type);
        groupRow.innerHTML += `<th colspan="${g.count}" class="hh-${esc(g.type)}">${esc(label)}</th>`;
      });
      if (block.hasAttendance) {
        headRow.innerHTML += `<th class="hh-attendance gb-col-summary">Equivalent</th>`;
      }
      block.cols.forEach(col => {
        headRow.innerHTML += `<th class="hh-${esc(col._type)} gb-col-assessment" title="${esc(col._title)}">${esc(col._title)}</th>`;
      });
      headRow.innerHTML += `<th class="hh-period gb-col-summary">${block.short}</th>`;
    });
  } else if (showEmptyAssessmentColumn) {
    periodRow.innerHTML += `<th rowspan="3" class="gh-empty gb-col-summary">No assessments</th>`;
  }
  groupRow.style.display = blocks.length ? '' : 'none';
  headRow.style.display = blocks.length ? '' : 'none';

  const q = gbSearchQ.toLowerCase().trim();
  const filtered = enrichedStudents.filter(st =>
    !q ||
    String(st.student_name || '').toLowerCase().includes(q) ||
    String(st.student_number || '').toLowerCase().includes(q)
  );
  const totalCols = 9 + blocks.reduce((sum, block) => sum + block.span, 0) + (showEmptyAssessmentColumn ? 1 : 0);
  if (!filtered.length) {
    body.innerHTML = `<tr><td colspan="${totalCols}" style="text-align:center;color:var(--text-muted);padding:1rem;">No matching student records.</td></tr>`;
    return;
  }

  body.innerHTML = filtered.map((st, idx) => {
    let row = `<tr class="row-data">
      <td class="gb-col-no"><div class="cell-pad">${idx + 1}</div></td>
      <td class="gb-col-id"><div class="cell-pad">${esc(st.student_number || '-')}</div></td>
      <td class="gb-col-name"><div class="cell-pad gb-name-cell" title="${esc(st.student_name || 'Unknown')}">${esc(st.student_name || 'Unknown')}</div></td>
      <td class="gb-col-reg"><div class="cell-pad"><span class="score-miss">-</span></div></td>
      <td class="gb-col-small"><div class="final-wrap"><span class="gb-equivalent">${esc(st._equivalent)}</span></div></td>
      <td class="gb-col-small"><div class="final-wrap"><span class="final-val ${finalClass(st._sheetFinal)}">${fmtGrade(st._sheetFinal)}</span></div></td>
      <td class="gb-col-small"><div class="final-wrap"><span class="final-val ${finalClass(st._periodGrades.prelim || 0)}">${fmtGrade(st._periodGrades.prelim)}</span></div></td>
      <td class="gb-col-small"><div class="final-wrap"><span class="final-val ${finalClass(st._periodGrades.midterm || 0)}">${fmtGrade(st._periodGrades.midterm)}</span></div></td>
      <td class="gb-col-small"><div class="final-wrap"><span class="final-val ${finalClass(st._periodGrades.finals || 0)}">${fmtGrade(st._periodGrades.finals)}</span></div></td>`;
    blocks.forEach(block => {
      if (block.hasAttendance) {
        const att = attendancePeriods?.[st.student_id]?.[block.key] || attendancePeriods?.[String(st.student_id)]?.[block.key] || null;
        const attEq = attendanceEquivalentFor(st, block.key);
        const title = att && Number(att.total || 0) > 0
          ? `${Number(att.present || 0)} present | ${Number(att.absent || 0)} absent | ${Number(att.percentage || 0)}%`
          : 'No attendance record';
        const attClass = attEq === null ? 'score-miss' : `score-val ${scoreClass(attEq)}`;
        row += `<td class="gb-col-summary"><div class="score-wrap" title="${esc(title)}"><span class="${attClass}">${attEq === null ? '-' : esc(String(Math.round(attEq)))}</span></div></td>`;
      }
      block.cols.forEach(col => {
        const ss = col.scores?.[st.student_id] || col.scores?.[String(st.student_id)] || null;
        if (!ss || ss.score === null || ss.score === undefined) {
          row += `<td class="gb-col-assessment"><div class="score-wrap"><span class="score-miss">-</span></div></td>`;
        } else {
          const pct = Number(ss.percentage || 0);
          const display = gbViewMode === 'pct'
            ? `<span class="score-val ${scoreClass(pct)}">${Math.round(pct)}%</span>`
            : `<span class="score-val ${scoreClass(pct)}">${Number(ss.score).toFixed(2).replace(/\.00$/,'')}<span class="score-max">/${Number(col.max_score || 0).toFixed(0)}</span></span>`;
          row += `<td class="gb-col-assessment"><div class="score-cell-btn" onclick="openGradebookSubmissionDetail('${esc(st.student_id)}','${esc(col.post_id)}')" title="View submission details">${display}<i class="fa-solid fa-eye view-icon"></i></div></td>`;
        }
      });
      row += `<td class="gb-col-summary"><div class="final-wrap"><span class="final-val ${finalClass(st._periodGrades[block.key] || 0)}">${fmtGrade(st._periodGrades[block.key])}</span></div></td>`;
    });
    if (showEmptyAssessmentColumn) row += `<td class="gb-col-summary"><div class="score-wrap"><span class="score-miss">-</span></div></td>`;
    row += `</tr>`;
    return row;
  }).join('');
}

function setGbFilter(type, el){
  gbActiveFilter = type;
  document.querySelectorAll('#tab-grades .fchip').forEach(x => x.classList.remove('active'));
  if (el) el.classList.add('active');
  renderGradebook();
}
function setGbView(mode){
  gbViewMode = mode === 'pct' ? 'pct' : 'score';
  const p = document.getElementById('tog-pct');
  if (p) p.classList.toggle('active', gbViewMode === 'pct');
  renderGradebook();
}
function toggleGbPctView(){
  setGbView(gbViewMode === 'pct' ? 'score' : 'pct');
}
function filterGradebookRows(v){
  gbSearchQ = String(v || '');
  renderGradebook();
}
function openGradebookSubmissionDetail(studentId, postId){
  const fmtDt = (s) => !s ? '—' : new Date(String(s).replace(' ','T')).toLocaleString([], {month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'});

  const st = (gradebookData?.students || []).find(x => String(x.student_id) === String(studentId));
  const as = (gradebookData?.assessments || []).find(x => String(x.post_id) === String(postId));
  const ss = as?.scores?.[studentId] || null;
  Swal.fire({
    icon: 'info',
    title: as?.title || 'Submission',
    html: `
      <div style="text-align:left;font-size:.9rem;line-height:1.7;">
        <div><strong>Student:</strong> ${esc(st?.student_name || 'Unknown')}</div>
        <div><strong>Student #:</strong> ${esc(st?.student_number || '—')}</div>
        <div><strong>Category:</strong> ${esc(as?.category || '—')}</div>
        <div><strong>Score:</strong> ${ss?.score === null || ss?.score === undefined ? '—' : `${Number(ss.score).toFixed(2).replace(/\.00$/,'')} / ${Number(as?.max_score || 0).toFixed(0)}`}</div>
        <div><strong>Taken:</strong> ${ss?.taken_at ? esc(fmtDt(ss.taken_at)) : '—'}</div>
      </div>`,
    confirmButtonText: 'Close'
  });
}

document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') pollClassroom();
});

window.addEventListener('focus', () => { pollClassroom(); });

function updatePeopleBadgesNow() {
  const enrolled = Array.isArray(allPeople) ? allPeople : [];
  const pending = Array.isArray(allPending) ? allPending : [];
  const joinReqs = pending.filter(s => s && (s.source === 'join_request' || s.source === 'enrollment'));
  const pCountEl = document.getElementById('peopleCount');
  const eCountEl = document.getElementById('enrolledCount');
  if (pCountEl) pCountEl.textContent = enrolled.length;
  if (eCountEl) eCountEl.textContent = enrolled.length;
  ['wlBarCount','wlSideCount'].forEach(id=>{
    const el=document.getElementById(id); if(el) el.textContent=joinReqs.length;
  });
  ['wlBarPlural','wlSidePlural'].forEach(id=>{
    const el=document.getElementById(id); if(el) el.textContent=joinReqs.length===1?'':'s';
  });
}

async function loadClassroom(){
  try{
    const res=await fetch(`API/facultyUI/classroom/get_classroom.php?class_id=${encodeURIComponent(CLASS_ID)}&_t=${Date.now()}`, { cache:'no-store' });
    const raw=await res.text();
    let data;
    try{
      data=JSON.parse(raw);
    }catch(parseErr){
      console.error('get_classroom.php returned non-JSON:', raw);
      throw new Error((raw || 'Server returned invalid response.').trim().slice(0, 300));
    }
    if(data.status!=='success'){toast(data.message||'Error loading','error');return;}
    classData = data.class;
    allPosts  = Array.isArray(data.posts) ? data.posts : [];
    allPeople = Array.isArray(data.people) ? data.people : [];
    allPending = (Array.isArray(data.joinRequests) ? data.joinRequests : (Array.isArray(data.pending) ? data.pending : [])).map(s => ({...s, source:'join_request'}));
    window.allInvitations = (Array.isArray(data.invitations) ? data.invitations : []).map(s => ({...s, source:'invitation'}));
    // Update top badges immediately, even before any tab interaction.
    updatePeopleBadgesNow();
    buildStreamFilterOptions();
    renderBanner();renderJoinCode();renderClassInfo();renderPeople();loadClassroomMeet();
  }catch(e){toast(e?.message ? `Classroom load failed: ${e.message}` : 'Network error loading classroom','error');console.error(e);}
}

let _crReady=false;
async function pollClassroom(){
  const before=allPending.filter(s=>s.source==='join_request').length;
  await loadClassroom();
  const after=allPending.filter(s=>s.source==='join_request').length;
  if(_crReady && after>before){
    const n=after-before;
    toast(n===1?'A student is requesting to join!':n+' students are requesting to join!','info');
    const dot=document.querySelector('#peopleTabBtn .wl-tab-dot');
    if(dot){dot.classList.remove('pop');void dot.offsetWidth;dot.classList.add('pop');}
  }
  _crReady=true;
}

async function loadPostTypes(){
  try{
    const res=await fetch('API/facultyUI/classroom/get_post_types.php');
    const data=await res.json();
    if(data.status==='success'){
      postTypes=(data.types||[]).map(type=>({
        ...type,
        is_gradable:Number(type.is_gradable||0)===1,
        has_quiz:Number(type.has_quiz||0)===1,
        has_file:Number(type.has_file||0)===1
      }));
      buildTypePicker();buildCwButtons();buildStreamFilterOptions();
    }
  }catch(e){console.error('loadPostTypes',e);}
}

function normalisePaletteKey(str){
  return String(str ?? '').replace(/^\d+[-\u2013]\d+(?:[-\u2013]\d+)?\s+/,'').toLowerCase().trim();
}

function hashPaletteKey(str){
  let h=0;
  for(const ch of normalisePaletteKey(str)) h=((h<<5)-h)+ch.charCodeAt(0);
  return PALETTE_KEYS[Math.abs(h)%PALETTE_KEYS.length] || 'b-forest';
}

function paletteKeyFromStored(value){
  const raw=String(value || '').trim();
  if(!raw) return '';
  if(PALETTE_GRADIENTS[raw]) return raw;
  return PALETTE_LOOKUP[compactPalette(raw)] || '';
}

function sharedPaletteKeyFromStored(value){
  const raw=String(value || '').trim();
  return PALETTE_GRADIENTS[raw] ? raw : '';
}

function paletteToGradient(value){
  const key=paletteKeyFromStored(value);
  if(key) return PALETTE_GRADIENTS[key];
  const raw=String(value || '').trim();
  return /^linear-gradient/i.test(raw) ? raw : '';
}

function paletteFor(str){
  const key=normalisePaletteKey(str);
  if(paletteMapLoaded && subjectPaletteMap[key]){
    return paletteToGradient(subjectPaletteMap[key]) || PALETTE_GRADIENTS[subjectPaletteMap[key]] || PALETTE_GRADIENTS[hashPaletteKey(str)];
  }
  return PALETTE_GRADIENTS[hashPaletteKey(str)];
}

function classPaletteSeed(c,titleLine=''){
  return c?.subject_name || c?.subject_code || c?.course_name || titleLine || c?.class_name || c?.class_code || 'Class';
}

function classPaletteGradient(c,titleLine=''){
  const sharedKey=sharedPaletteKeyFromStored(c?.banner_palette);
  return sharedKey ? PALETTE_GRADIENTS[sharedKey] : paletteFor(classPaletteSeed(c,titleLine));
}

async function loadPaletteMap(){
  try{
    const res=await fetch('API/facultyUI/get_subject_pallete.php');
    const data=await res.json();
    if(data.status==='success'){
      subjectPaletteMap=data.map || {};
      paletteMapLoaded=true;
    }
  }catch(e){/* deterministic fallback stays active */}
}

function bannerPatternIcons(c){
  const text = [c?.subject_name,c?.subject_code,c?.course_name,c?.course_code,c?.class_code,c?.section].join(' ').toLowerCase();
  if(/mobile|android|ios|app/.test(text)) return ['fa-mobile-screen-button','fa-code','fa-layer-group','fa-bug'];
  if(/web|html|css|javascript|system/.test(text)) return ['fa-window-maximize','fa-code','fa-laptop-code','fa-diagram-project'];
  if(/data|database|dbms|sql/.test(text)) return ['fa-database','fa-server','fa-table','fa-chart-line'];
  if(/network|security|cyber/.test(text)) return ['fa-network-wired','fa-shield-halved','fa-lock','fa-server'];
  if(/tour|travel|hospitality/.test(text)) return ['fa-plane-departure','fa-location-dot','fa-suitcase-rolling','fa-ticket'];
  if(/math|stat|analytics/.test(text)) return ['fa-chart-simple','fa-square-root-variable','fa-calculator','fa-chart-pie'];
  return ['fa-book-open','fa-clipboard-list','fa-graduation-cap','fa-comments'];
}

function renderBannerPattern(c){
  const wrap=document.getElementById('bannerPattern');
  if(!wrap) return;
  const icons=bannerPatternIcons(c);
  const slots=[
    [2,8,96,68,-2,0],[17,50,118,86,0,1],[32,5,190,130,0,0],[45,58,126,96,-5,1],
    [58,18,110,78,2,0],[71,7,144,104,0,1],[82,45,164,116,3,0],[6,66,92,62,-4,1],
    [92,8,78,58,0,0],[29,70,82,60,0,1]
  ];
  wrap.innerHTML=slots.map((slot,i)=>{
    const [left,top,w,h,rot,ghost]=slot;
    const icon=icons[i % icons.length];
    return `<span class="banner-pattern-icon${ghost?' is-ghost':''}" style="left:${left}%;top:${top}%;width:${w}px;height:${h}px;font-size:${Math.max(24,Math.round(Math.min(w,h)*.46))}px;--r:${rot}deg;"><i class="fas ${icon}"></i></span>`;
  }).join('');
}

function renderBanner(){
  const c=classData;
  const titleLine = [c.section, c.subject_name].filter(Boolean).join(' ') || c.class_name || c.class_code || 'Class';
  const subLine = c.course_name || c.course_code || '';
  const bannerGradient = classPaletteGradient(c,titleLine);
  document.documentElement.style.setProperty('--class-gradient', bannerGradient);
  document.getElementById('topbarClassName').textContent=titleLine;
  document.getElementById('bannerTitle').textContent=titleLine;
  document.getElementById('bannerSub').textContent=subLine;
  document.getElementById('bannerBg').style.background=bannerGradient;
  renderBannerPattern(c);
  document.title=`${titleLine} - Tere Learn`;
  const chips=document.getElementById('bannerChips');chips.innerHTML='';
  const add=(icon,text)=>{if(!text)return;chips.innerHTML+=`<span class="banner-chip"><i class="fas fa-${icon}"></i> ${esc(text)}</span>`;};
  add('calendar-alt',c.class_semester);add('calendar-week',c.class_days);
  if(c.schedule){const p=c.schedule.split('-').map(t=>fmt12(t.trim()));add('clock',p.join(' - '));}
  const initials=((c.first_name?.[0]??'')+(c.last_name?.[0]??'')).toUpperCase()||'F';
  document.getElementById('composeAvatar').textContent=initials;
  /* update settings panel */
const className2=titleLine;
document.getElementById('gcForClass').textContent=className2;
  document.getElementById('gcSemesterInfo').textContent=c.class_semester||'-';
}

/* ══════════════════════════════════════════
   TYPE PICKER (compose box pills)
══════════════════════════════════════════ */
function buildTypePicker(){
  const picker=document.getElementById('typePicker');picker.innerHTML='';
  postTypes.forEach(t=>{
    const isCustom=t.faculty_id!==null&&t.faculty_id!=='';
    const wrap=document.createElement('div');wrap.style.cssText='position:relative;display:inline-flex;align-items:center;';
    const pill=document.createElement('button');pill.className='type-pill';pill.dataset.typeId=t.id;
    pill.style.paddingRight=isCustom?'1.8rem':'';
    pill.innerHTML=`<i class="fas ${t.icon}"></i> ${esc(t.type_label)}`;
    pill.addEventListener('click',()=>openPostModal(t));
    pill.addEventListener('mouseenter',()=>{pill.style.background=t.color_bg;pill.style.color=t.color_text;pill.style.borderColor=t.color_text;});
    pill.addEventListener('mouseleave',()=>{pill.style.background='';pill.style.color='';pill.style.borderColor='';});
    wrap.appendChild(pill);
    if(isCustom){
      const del=document.createElement('button');del.innerHTML='&times;';del.title='Delete this type';
      del.style.cssText='position:absolute;right:6px;top:50%;transform:translateY(-50%);width:16px;height:16px;border:none;background:none;cursor:pointer;font-size:.85rem;font-weight:700;color:#9ca3af;line-height:1;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:all .15s;z-index:2;padding:0;';
      del.addEventListener('mouseenter',()=>{del.style.background='#fdecea';del.style.color='#d93025';});
      del.addEventListener('mouseleave',()=>{del.style.background='none';del.style.color='#9ca3af';});
      del.addEventListener('click',(e)=>{e.stopPropagation();deleteCustomType(t);});
      wrap.appendChild(del);
    }
    picker.appendChild(wrap);
  });
  const addWrap=document.createElement('div');
  const addPill=document.createElement('button');addPill.className='type-pill type-pill-add';
  addPill.innerHTML='<i class="fas fa-plus"></i> Custom';addPill.addEventListener('click',openCtModal);
  addWrap.appendChild(addPill);picker.appendChild(addWrap);
}

function buildCwButtons(){
  const wrap=document.getElementById('cwTypeButtons');
  if (!wrap) return;
  wrap.innerHTML='';
  postTypes.forEach(t=>{
    const b=document.createElement('button');b.className='btn btn-ghost';b.style.fontSize='.82rem';
    b.innerHTML=`<i class="fas ${t.icon}" style="color:${t.color_text};"></i> ${esc(t.type_label)}`;
    b.addEventListener('click',()=>openPostModal(t));wrap.appendChild(b);
  });
  const bc=document.createElement('button');bc.className='btn btn-ghost';bc.style.cssText='font-size:.82rem;border-style:dashed;';
  bc.innerHTML=`<i class="fas fa-plus" style="color:var(--primary);"></i> Custom`;bc.addEventListener('click',openCtModal);wrap.appendChild(bc);
  const side=document.getElementById('cwQuickBtns');
  if (!side) return;
  side.innerHTML='';
  postTypes.forEach(t=>{
    const b=document.createElement('button');b.className='code-btn';b.style.cssText='width:100%;margin-bottom:.4rem;';
    b.innerHTML=`<i class="fas ${t.icon}" style="color:${t.color_text};"></i> ${esc(t.type_label)}`;
    b.addEventListener('click',()=>openPostModal(t));side.appendChild(b);
  });
}

/* ══════════════════════════════════════════
   GOOGLE CLASSROOM STYLE MODAL — OPEN/CLOSE
══════════════════════════════════════════ */
function openPostModal(typeObj){
  _postDraftSuspendSave = true;
  clearTimeout(_postDraftTimer);
  // Reset pending data
  pendingFiles = [];
  pendingLinks = [];
  quizQuestions = [];
  
  // Reset form inputs
  ['pmPostId','pmTitle','pmBody','pmDueDate','pmPoints','pmTopic','pmOpenAt','pmCloseAt','pmTimePerQuizMinutes','pmTimePerQuestionSecs','pmPassingThreshold'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  selectedLessonWeeks = [];
  const lessonWeeksEl = document.getElementById('pmLessonWeeks');
  if (lessonWeeksEl) lessonWeeksEl.value = '';
  closeLessonWeekPicker();
  document.querySelectorAll('input[name="pmLessonPeriod"]').forEach(input => { input.checked = false; });
  const _ma = document.getElementById('pmMaxAttempts'); if (_ma) _ma.value = '1';
  const _tm = document.getElementById('pmTimeMode');    if (_tm) _tm.value = 'none';
  const _w1 = document.getElementById('pmTimePerQuizWrap'); if (_w1) _w1.style.display = 'none';
  const _w2 = document.getElementById('pmTimePerQWrap');    if (_w2) _w2.style.display = 'none';
  userTypedTitle = false;
  // Auto-uppercase title on input
  const titleInput = document.getElementById('pmTitle');
  titleInput.oninput = function() {
    const pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
    userTypedTitle = true;
  };
  document.getElementById('attachPreviews').innerHTML = '';
  document.getElementById('quizQuestionsList').innerHTML = '';
  updateQuizPts();

  // Reset submission mode
  submissionMode = 'individual';
  groupBuilderData = [];
  unassignedPool = [];

  /* Build type navigation */
  buildGcTypeNav(typeObj);

  /* Show the modal panel */
  const panel = document.getElementById('gcModalPanel');
  const overlay = document.getElementById('gcModalOverlay');
  panel.style.display = 'flex';
  panel.classList.remove('closing');
  overlay.classList.add('show');
  document.body.style.overflow = 'hidden';

  /* Select post type */
  const defaultType = typeObj || postTypes.find(t => t.type_key === 'announcement') || postTypes[0];
  if (defaultType) selectGcType(defaultType);
  // Load lesson selector if quiz type
  if (defaultType && defaultType.has_quiz) {
    setTimeout(loadLessonSelectorForAI, 80);
  }

  // Set focus on appropriate field after opening modal
  setTimeout(() => {
    const f = document.getElementById('pmTitle');
    if (f && f.closest('[style*="display:none"]') === null) {
      f.focus();
    } else {
      document.getElementById('pmBody').focus();
    }
  }, 120);
}

function getPostDraftStorageKey(){
  return `terelearn_post_draft_${CLASS_ID}`;
}
function isPostModalVisible(){
  const panel = document.getElementById('gcModalPanel');
  return !!panel && panel.style.display !== 'none';
}
function getActivePostTypeObj(){
  return (postTypes || []).find(x => String(x.id) === String(activePmTypeId)) || null;
}
function getSelectedLessonPeriodValue(){
  return document.querySelector('input[name="pmLessonPeriod"]:checked')?.value || '';
}
function clampLessonWeekCount(value){
  const parsed = parseInt(value, 10);
  return Number.isFinite(parsed) ? Math.max(1, Math.min(30, parsed)) : 18;
}
async function loadLessonWeekCount(force = false){
  if (lessonWeekCountLoaded && !force) return lessonWeekCount;
  try {
    const res = await fetch('API/Admin/academic_week_setting.php?_t=' + Date.now(), { cache:'no-store' });
    const data = await res.json();
    if (data && data.status === 'success') {
      lessonWeekCount = clampLessonWeekCount(data.week_count);
    }
  } catch (e) {
    console.warn('Academic week count fallback used:', e);
  }
  lessonWeekCountLoaded = true;
  selectedLessonWeeks = normalizeLessonWeeks(selectedLessonWeeks);
  if (classData) renderClassInfo();
  if (lessonWeekPickerMode === 'lesson') syncLessonWeekDisplay({ skipDraft:true });
  renderLessonWeekGrid();
  return lessonWeekCount;
}
function normalizeLessonWeeks(weeks){
  const seen = new Set();
  (Array.isArray(weeks) ? weeks : String(weeks || '').split(/[,\s]+/)).forEach(value => {
    const n = parseInt(value, 10);
    if (Number.isFinite(n) && n >= 1 && n <= lessonWeekCount) seen.add(n);
  });
  return Array.from(seen).sort((a, b) => a - b);
}
function parseLessonWeeksFromText(text){
  const raw = String(text || '');
  if (!/\bweek\b/i.test(raw)) return [];
  const weeks = [];
  const rangeRe = /(\d+)\s*(?:-|to)\s*(\d+)/gi;
  let rangeMatch;
  while ((rangeMatch = rangeRe.exec(raw)) !== null) {
    const start = parseInt(rangeMatch[1], 10);
    const end = parseInt(rangeMatch[2], 10);
    if (!Number.isFinite(start) || !Number.isFinite(end)) continue;
    const low = Math.min(start, end);
    const high = Math.max(start, end);
    for (let i = low; i <= high; i++) weeks.push(i);
  }
  const numberRe = /\d+/g;
  let numberMatch;
  while ((numberMatch = numberRe.exec(raw)) !== null) {
    weeks.push(parseInt(numberMatch[0], 10));
  }
  return normalizeLessonWeeks(weeks);
}
function isLessonPostRecord(post){
  const typeObj = (postTypes || []).find(t => String(t.id) === String(post?.post_type_id || ''));
  const key = String(typeObj?.type_key || post?.post_type || '').toLowerCase();
  const label = String(typeObj?.type_label || post?.sub_label || '').toLowerCase();
  const title = String(post?.title || '').trim();
  return key === 'lesson' || label.includes('lesson') || /^week[\s\-_]?\d/i.test(title);
}
function getOccupiedLessonWeekSet(excludePostId = ''){
  const occupied = new Set();
  (allPosts || []).forEach(post => {
    if (excludePostId && String(post?.id || '') === String(excludePostId)) return;
    if (!isLessonPostRecord(post)) return;
    parseLessonWeeksFromText(post.title || '').forEach(week => occupied.add(week));
  });
  return occupied;
}
function getNextAvailableLessonWeek(excludePostId = ''){
  const occupied = getOccupiedLessonWeekSet(excludePostId);
  for (let week = 1; week <= lessonWeekCount; week++) {
    if (!occupied.has(week)) return week;
  }
  return null;
}
function humanJoinLabels(items){
  if (items.length <= 1) return items[0] || '';
  if (items.length === 2) return items[0] + ' and ' + items[1];
  return items.slice(0, -1).join(', ') + ', and ' + items[items.length - 1];
}
function formatLessonWeeks(weeks){
  const list = normalizeLessonWeeks(weeks);
  if (!list.length) return '';
  const runs = [];
  list.forEach(week => {
    const last = runs[runs.length - 1];
    if (last && week === last.end + 1) last.end = week;
    else runs.push({ start: week, end: week });
  });
  if (runs.length === 1) {
    const only = runs[0];
    return only.start === only.end ? `Week ${only.start}` : `Week ${only.start} - ${only.end}`;
  }
  return 'Week ' + humanJoinLabels(runs.map(run => run.start === run.end ? String(run.start) : `${run.start} - ${run.end}`));
}
function formatAcademicWeekOrdinal(week){
  const n = parseInt(week, 10);
  if (!Number.isFinite(n) || n < 1) return '';
  const mod100 = n % 100;
  const suffix = (mod100 >= 11 && mod100 <= 13) ? 'th' : ({1:'st',2:'nd',3:'rd'}[n % 10] || 'th');
  return `${n}${suffix}`;
}
function parseAcademicWeekOrdinal(text){
  const match = String(text || '').match(/\d+/);
  if (!match) return null;
  const normalized = normalizeLessonWeeks([parseInt(match[0], 10)]);
  return normalized[0] || null;
}
function setWeekPickerChrome(mode){
  const isReport = mode === 'report';
  const title = document.getElementById('lessonWeekPickerTitle');
  const clearBtn = document.getElementById('lessonWeekClearBtn');
  const nextBtn = document.getElementById('lessonWeekNextBtn');
  const doneBtn = document.getElementById('lessonWeekDoneBtn');
  if (title) {
    title.innerHTML = `<i class="fas fa-calendar-week"></i>${isReport ? 'Select Academic Week No.' : 'Select Lesson Week No.'}`;
  }
  if (clearBtn) clearBtn.textContent = 'Clear';
  if (nextBtn) nextBtn.style.display = isReport ? 'none' : '';
  if (doneBtn) doneBtn.innerHTML = `<i class="fas fa-check"></i> Done`;
}
function showWeekPickerBackdrop(){
  const back = document.getElementById('lessonWeekPickerBack');
  if (back) {
    back.classList.add('show');
    back.setAttribute('aria-hidden', 'false');
  }
}
function syncReportWeekPickerDisplay(){
  const summary = document.getElementById('lessonWeekPickerSummary');
  if (summary) {
    const label = selectedReportWeek ? formatAcademicWeekOrdinal(selectedReportWeek) : '';
    summary.innerHTML = `<i class="fas ${label ? 'fa-check-circle' : 'fa-calendar-week'}"></i><span>${esc(label || 'Choose academic week no.')}</span>`;
  }
}
function isActiveLessonType(){
  const active = getActivePostTypeObj();
  const key = String(active?.type_key || '').toLowerCase();
  const label = String(active?.type_label || '').toLowerCase();
  return key === 'lesson' || label.includes('lesson');
}
function syncLessonWeekDisplay(opts = {}){
  selectedLessonWeeks = normalizeLessonWeeks(selectedLessonWeeks);
  const label = formatLessonWeeks(selectedLessonWeeks);
  const hidden = document.getElementById('pmLessonWeeks');
  const titleField = document.getElementById('pmTitle');
  const summary = document.getElementById('lessonWeekPickerSummary');
  if (hidden) hidden.value = selectedLessonWeeks.join(',');
  if (titleField && isActiveLessonType()) {
    if (label || !opts.keepTitleWhenEmpty) titleField.value = label;
  }
  if (summary) {
    summary.innerHTML = `<i class="fas ${label ? 'fa-check-circle' : 'fa-calendar-week'}"></i><span>${esc(label || 'Choose week no.')}</span>`;
  }
  renderLessonWeekGrid();
  if (!opts.skipDraft) schedulePostDraftSave();
}
function setSelectedLessonWeeks(weeks, opts = {}){
  selectedLessonWeeks = normalizeLessonWeeks(weeks);
  syncLessonWeekDisplay(opts);
  if (opts.markTyped) userTypedTitle = true;
}
function renderLessonWeekGrid(){
  const grid = document.getElementById('lessonWeekGrid');
  if (!grid) return;
  const isReport = lessonWeekPickerMode === 'report';
  const currentPostId = document.getElementById('pmPostId')?.value || '';
  const occupied = isReport ? new Set() : getOccupiedLessonWeekSet(currentPostId);
  const selected = new Set(isReport && selectedReportWeek ? [selectedReportWeek] : selectedLessonWeeks);
  grid.innerHTML = '';
  for (let week = 1; week <= lessonWeekCount; week++) {
    const isSelected = selected.has(week);
    const isOccupied = !isReport && occupied.has(week);
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'lesson-week-cell' + (isSelected ? ' selected' : '') + (isOccupied ? ' occupied' : '');
    btn.textContent = week;
    btn.disabled = isOccupied && !isSelected;
    btn.title = isOccupied && !isSelected ? `Week ${week} already has a lesson` : `Week ${week}`;
    btn.addEventListener('click', () => toggleLessonWeek(week));
    grid.appendChild(btn);
  }
  const note = document.getElementById('lessonWeekPickerNote');
  if (note) {
    note.textContent = isReport
      ? `Choose the academic week number for this accomplishment report.`
      : `Choose from Week 1 to ${lessonWeekCount}. Dimmed weeks are already used by another lesson.`;
  }
}
async function openLessonWeekPicker(){
  lessonWeekPickerMode = 'lesson';
  selectedReportWeek = null;
  reportWeekPickerCommit = null;
  setWeekPickerChrome('lesson');
  await loadLessonWeekCount();
  if (!selectedLessonWeeks.length) autoSelectNextLessonWeek({ skipDraft:true, quiet:true });
  renderLessonWeekGrid();
  showWeekPickerBackdrop();
}
async function openReportAcademicWeekPicker(initialValue = '', onCommit = null){
  lessonWeekPickerMode = 'report';
  reportWeekPickerCommit = typeof onCommit === 'function' ? onCommit : null;
  setWeekPickerChrome('report');
  await loadLessonWeekCount();
  selectedReportWeek = parseAcademicWeekOrdinal(initialValue) || 1;
  syncReportWeekPickerDisplay();
  renderLessonWeekGrid();
  showWeekPickerBackdrop();
}
function closeLessonWeekPicker(commitReport = false){
  const back = document.getElementById('lessonWeekPickerBack');
  if (back) {
    back.classList.remove('show');
    back.setAttribute('aria-hidden', 'true');
  }
  if (lessonWeekPickerMode === 'report') {
    if (commitReport && reportWeekPickerCommit && selectedReportWeek) {
      reportWeekPickerCommit(selectedReportWeek);
    }
    selectedReportWeek = null;
    reportWeekPickerCommit = null;
    lessonWeekPickerMode = 'lesson';
    setWeekPickerChrome('lesson');
  }
}
function toggleLessonWeek(week){
  if (lessonWeekPickerMode === 'report') {
    selectedReportWeek = normalizeLessonWeeks([week])[0] || null;
    syncReportWeekPickerDisplay();
    renderLessonWeekGrid();
    if (reportWeekPickerCommit && selectedReportWeek) reportWeekPickerCommit(selectedReportWeek);
    closeLessonWeekPicker();
    return;
  }
  const currentPostId = document.getElementById('pmPostId')?.value || '';
  const occupied = getOccupiedLessonWeekSet(currentPostId);
  if (occupied.has(week) && !selectedLessonWeeks.includes(week)) return;
  const next = selectedLessonWeeks.includes(week)
    ? selectedLessonWeeks.filter(item => item !== week)
    : selectedLessonWeeks.concat(week);
  setSelectedLessonWeeks(next, { markTyped:true });
}
function clearLessonWeekSelection(){
  if (lessonWeekPickerMode === 'report') {
    selectedReportWeek = null;
    syncReportWeekPickerDisplay();
    renderLessonWeekGrid();
    if (reportWeekPickerCommit) reportWeekPickerCommit('');
    closeLessonWeekPicker();
    return;
  }
  setSelectedLessonWeeks([], { markTyped:true });
}
function autoSelectNextLessonWeek(opts = {}){
  const currentPostId = document.getElementById('pmPostId')?.value || '';
  const next = getNextAvailableLessonWeek(currentPostId);
  if (!next) {
    setSelectedLessonWeeks([], opts);
    if (!opts.quiet) toast('All academic weeks already have lessons.', 'error');
    return null;
  }
  setSelectedLessonWeeks([next], opts);
  return next;
}
function selectNextAvailableLessonWeek(){
  if (lessonWeekPickerMode === 'report') {
    selectedReportWeek = selectedReportWeek && selectedReportWeek < lessonWeekCount ? selectedReportWeek + 1 : 1;
    syncReportWeekPickerDisplay();
    renderLessonWeekGrid();
    return;
  }
  autoSelectNextLessonWeek({ markTyped:true });
}
function queueLessonWeekPickerForNewPost(){
  if ((document.getElementById('pmPostId')?.value || '').trim()) return;
  if (!_skipDraftResume && loadPostDraft()) return;
  setTimeout(() => {
    if (!isPostModalVisible()) return;
    if (!isActiveLessonType()) return;
    if ((document.getElementById('pmPostId')?.value || '').trim()) return;
    openLessonWeekPicker();
  }, 160);
}
function setLessonWeekMode(isLesson){
  const wrap = document.getElementById('gcWeekPickerWrap');
  const btn = document.getElementById('gcWeekPickerBtn');
  const hint = document.getElementById('gcWeekHint');
  const titleField = document.getElementById('pmTitle');
  if (wrap) wrap.classList.toggle('lesson-week-active', isLesson);
  if (btn) btn.style.display = isLesson ? 'inline-flex' : 'none';
  if (hint) hint.style.display = isLesson ? '' : 'none';
  if (titleField) {
    titleField.readOnly = !!isLesson;
    titleField.placeholder = isLesson ? 'Choose week no.' : 'Enter title...';
    titleField.onclick = isLesson ? openLessonWeekPicker : null;
    if (isLesson && !selectedLessonWeeks.length) {
      const parsed = parseLessonWeeksFromText(titleField.value);
      if (parsed.length) selectedLessonWeeks = parsed;
    }
  }
  if (!isLesson) {
    if (titleField && parseLessonWeeksFromText(titleField.value).length && !(document.getElementById('pmPostId')?.value || '').trim()) {
      titleField.value = '';
      userTypedTitle = false;
    }
    selectedLessonWeeks = [];
    const hidden = document.getElementById('pmLessonWeeks');
    if (hidden) hidden.value = '';
  } else {
    syncLessonWeekDisplay({ skipDraft:true, keepTitleWhenEmpty:true });
  }
}
function hasMeaningfulPostDraftData(draft){
  if (!draft || typeof draft !== 'object') return false;
  return !!(
    String(draft.title || '').trim() ||
    String(draft.body || '').trim() ||
    String(draft.topic || '').trim() ||
    String(draft.quiz_coverage || '').trim() ||
    (Array.isArray(draft.pending_links) && draft.pending_links.length) ||
    (Array.isArray(draft.quiz_questions) && draft.quiz_questions.length) ||
    (Array.isArray(draft.pending_file_names) && draft.pending_file_names.length)
  );
}
function collectPostDraftState(){
  if (!isPostModalVisible()) return null;
  if ((document.getElementById('pmPostId')?.value || '').trim() !== '') return null;
  const typeObj = getActivePostTypeObj();
  if (!typeObj) return null;
  const draft = {
    saved_at: new Date().toISOString(),
    class_id: CLASS_ID,
    post_type_id: String(typeObj.id || ''),
    post_type_key: String(typeObj.type_key || ''),
    post_type_label: String(typeObj.type_label || ''),
    title: document.getElementById('pmTitle')?.value || '',
    body: document.getElementById('pmBody')?.value || '',
    due_date: document.getElementById('pmDueDate')?.value || '',
    points: document.getElementById('pmPoints')?.value || '',
    topic: document.getElementById('pmTopic')?.value || '',
    quiz_coverage: document.getElementById('pmQuizCoverage')?.value || '',
    open_at: document.getElementById('pmOpenAt')?.value || '',
    close_at: document.getElementById('pmCloseAt')?.value || '',
    time_mode: document.getElementById('pmTimeMode')?.value || 'none',
    time_per_quiz_minutes: document.getElementById('pmTimePerQuizMinutes')?.value || '',
    time_per_question_secs: document.getElementById('pmTimePerQuestionSecs')?.value || '',
    passing_threshold: document.getElementById('pmPassingThreshold')?.value || '',
    max_attempts: document.getElementById('pmMaxAttempts')?.value || '1',
    lesson_period: getSelectedLessonPeriodValue(),
    lesson_weeks: selectedLessonWeeks.slice(),
    exam_mode: document.getElementById('pmExamMode')?.value || 'questionnaire',
    submission_mode: submissionMode || 'individual',
    pending_links: Array.isArray(pendingLinks) ? pendingLinks.map(link => ({ type: link.type || 'link', url: link.url || '' })).filter(link => link.url) : [],
    pending_file_names: Array.isArray(pendingFiles) ? pendingFiles.map(file => file?.name).filter(Boolean) : [],
    quiz_questions: Array.isArray(quizQuestions) ? quizQuestions.map((q, index) => ({
      question: q.question || '',
      answer: q.answer || '',
      points: parseFloat(q.points) || 1,
      cognitive_level: q.cognitive_level || '',
      time_limit_seconds: parseInt(q.time_limit_seconds || 0, 10) || '',
      choices: Array.isArray(q.choices) ? q.choices.map(choice => ({
        text: choice.text || '',
        is_correct: !!choice.is_correct
      })) : [],
      order_num: index
    })) : []
  };
  return hasMeaningfulPostDraftData(draft) ? draft : null;
}
function savePostDraftNow(force = false){
  if (_postDraftRestoreInProgress) return;
  if (!force && _postDraftSuspendSave) return;
  try {
    const draft = collectPostDraftState();
    const key = getPostDraftStorageKey();
    if (!draft) {
      localStorage.removeItem(key);
      return;
    }
    localStorage.setItem(key, JSON.stringify(draft));
  } catch (e) {
    console.warn('Post draft save skipped:', e);
  }
}
function schedulePostDraftSave(){
  if (_postDraftSuspendSave) return;
  clearTimeout(_postDraftTimer);
  _postDraftTimer = setTimeout(savePostDraftNow, 260);
}
function loadPostDraft(){
  try {
    const raw = localStorage.getItem(getPostDraftStorageKey());
    if (!raw) return null;
    const draft = JSON.parse(raw);
    return hasMeaningfulPostDraftData(draft) ? draft : null;
  } catch (e) {
    console.warn('Post draft load skipped:', e);
    return null;
  }
}
function clearPostDraft(){
  clearTimeout(_postDraftTimer);
  try { localStorage.removeItem(getPostDraftStorageKey()); } catch (e) {}
}
function rebuildDraftLinkPreviews(){
  const wrap = document.getElementById('attachPreviews');
  if (!wrap) return;
  wrap.innerHTML = '';
  (pendingLinks || []).forEach(item => {
    if (!item?.url) return;
    addAttachPreview(item.type || 'link', item.url, () => {
      pendingLinks = pendingLinks.filter(x => x.url !== item.url);
      schedulePostDraftSave();
    });
  });
}
function applyPostDraftState(draft){
  if (!draft) return;
  _postDraftRestoreInProgress = true;
  const typeObj =
    postTypes.find(t => String(t.id) === String(draft.post_type_id || '')) ||
    postTypes.find(t => String(t.type_key) === String(draft.post_type_key || '')) ||
    getActivePostTypeObj();
  if (typeObj) selectGcType(typeObj);

  const setVal = (id, value) => { const el = document.getElementById(id); if (el) el.value = value ?? ''; };
  setVal('pmTitle', draft.title || '');
  setVal('pmBody', draft.body || '');
  setVal('pmDueDate', draft.due_date || '');
  setVal('pmPoints', draft.points || '');
  setVal('pmTopic', draft.topic || '');
  setVal('pmQuizCoverage', draft.quiz_coverage || '');
  setVal('pmOpenAt', draft.open_at || '');
  setVal('pmCloseAt', draft.close_at || '');
  setVal('pmTimeMode', draft.time_mode || 'none');
  setVal('pmTimePerQuizMinutes', draft.time_per_quiz_minutes || '');
  setVal('pmTimePerQuestionSecs', draft.time_per_question_secs || '');
  setVal('pmPassingThreshold', draft.passing_threshold || '');
  setVal('pmMaxAttempts', draft.max_attempts || '1');

  document.querySelectorAll('input[name="pmLessonPeriod"]').forEach(input => {
    input.checked = input.value === String(draft.lesson_period || '');
  });
  const draftIsLesson = String(typeObj?.type_key || '').toLowerCase() === 'lesson'
    || String(typeObj?.type_label || '').toLowerCase().includes('lesson');
  if (draftIsLesson) {
    const draftWeeks = Array.isArray(draft.lesson_weeks) && draft.lesson_weeks.length
      ? draft.lesson_weeks
      : parseLessonWeeksFromText(draft.title || '');
    if (normalizeLessonWeeks(draftWeeks).length) {
      setSelectedLessonWeeks(draftWeeks, { skipDraft:true });
    } else {
      selectedLessonWeeks = [];
      syncLessonWeekDisplay({ skipDraft:true, keepTitleWhenEmpty:true });
    }
  }

  submissionMode = draft.submission_mode || 'individual';
  if (typeof renderSubModeRow === 'function') renderSubModeRow();

  if (typeof onTimeModeChange === 'function') onTimeModeChange();
  if (typeof setExamMode === 'function') setExamMode(draft.exam_mode || 'questionnaire');

  pendingFiles = [];
  pendingLinks = Array.isArray(draft.pending_links) ? draft.pending_links.map(link => ({ type: link.type || 'link', url: link.url || '' })).filter(link => link.url) : [];
  rebuildDraftLinkPreviews();

  quizQuestions = Array.isArray(draft.quiz_questions)
    ? draft.quiz_questions.map((q, index) => ({
        id: 'draft_' + Date.now() + '_' + index,
        question: q.question || '',
        answer: q.answer || '',
        points: parseFloat(q.points) || 1,
        cognitive_level: String(q.cognitive_level || '').toLowerCase(),
        time_limit_seconds: parseInt(q.time_limit_seconds || 0, 10) || '',
        choices: Array.isArray(q.choices) ? q.choices.map(choice => ({
          text: choice.text || '',
          is_correct: !!choice.is_correct
        })) : []
      }))
    : [];
  renderQuizBuilder();
  userTypedTitle = !!String(draft.title || '').trim();
  _postDraftRestoreInProgress = false;
  setTimeout(() => savePostDraftNow(), 20);
}
async function maybeResumePostDraft(existingDraft = null){
  if (_skipDraftResume) {
    _postDraftSuspendSave = false;
    return;
  }
  if ((document.getElementById('pmPostId')?.value || '').trim() !== '') {
    _postDraftSuspendSave = false;
    return;
  }
  const draft = existingDraft || loadPostDraft();
  if (!draft) {
    _postDraftSuspendSave = false;
    return;
  }

  const savedAt = draft.saved_at ? new Date(draft.saved_at) : null;
  const savedLabel = savedAt && !Number.isNaN(savedAt.getTime())
    ? savedAt.toLocaleString()
    : 'your last session';
  const draftType = draft.post_type_label || draft.post_type_key || 'post';
  const fileNote = Array.isArray(draft.pending_file_names) && draft.pending_file_names.length
    ? `<div style="margin-top:.55rem;padding:.55rem .7rem;background:#fff8e1;border-radius:8px;color:#8a5a00;font-size:.8rem;"><i class="fas fa-paperclip"></i> Attached files are not restored automatically after refresh. You may need to upload them again.</div>`
    : '';

  const r = await Swal.fire({
    title: 'Resume unsaved draft?',
    html: `<div style="text-align:left;font-size:.9rem;line-height:1.6;">
      <div><strong>${esc(draftType)}</strong> draft found from <strong>${esc(savedLabel)}</strong>.</div>
      <div style="margin-top:.35rem;color:#5f6368;">You can resume where you left off or start fresh.</div>
      ${fileNote}
    </div>`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Resume draft',
    cancelButtonText: 'Start fresh',
    reverseButtons: true,
    confirmButtonColor: '#1a9e78'
  });

  if (r.isConfirmed) {
    applyPostDraftState(draft);
    _postDraftSuspendSave = false;
    toast('Draft restored.');
  } else if (r.dismiss === Swal.DismissReason.cancel) {
    clearPostDraft();
    _postDraftSuspendSave = false;
    schedulePostDraftSave();
    toast('Draft discarded.');
  } else {
    _postDraftSuspendSave = false;
  }
}

function buildGcTypeNav(activeType){
  const nav=document.getElementById('gcTypeNav');
  nav.innerHTML='<div class="gc-type-nav-label">Post type</div>';

  postTypes.forEach(t=>{
    const isCustom=t.faculty_id!==null&&t.faculty_id!=='';
    const item=document.createElement('button');
    item.className='gc-type-nav-item';
    item.dataset.typeId=t.id;
    item.innerHTML=`
      <div class="gc-nav-icon" style="background:${t.color_bg};color:${t.color_text};">
        <i class="fas ${t.icon}"></i>
      </div>
      <span>${esc(t.type_label)}</span>
      ${isCustom?`<i class="fas fa-times" style="margin-left:auto;font-size:.65rem;color:#9ca3af;" onclick="event.stopPropagation();deleteCustomType(${JSON.stringify(t)})"></i>`:''}
    `;
    item.addEventListener('click',()=>selectGcType(t));
    nav.appendChild(item);
  });

  /* Divider + Add custom */
  const div=document.createElement('div');div.className='gc-type-nav-divider';nav.appendChild(div);
  const addBtn=document.createElement('button');
  addBtn.className='gc-type-nav-add';
  addBtn.innerHTML=`<i class="fas fa-plus" style="font-size:.8rem;"></i><span>Create custom type</span>`;
  addBtn.addEventListener('click',openCtModal);
  nav.appendChild(addBtn);
}

function getNextWeekNumber() {
  return getNextAvailableLessonWeek() || 1;
}

function getNextQuizNumber() {
  return getNextNumberForType('QUIZ');
}
function getNextExamPresetTitle() {
  const order = ['PRELIM EXAM', 'MIDTERM EXAM', 'FINAL EXAM'];
  const used = new Set((allPosts || []).map(p => String(p.title || '').trim().toUpperCase()));
  for (const t of order) if (!used.has(t)) return t;
  const finals = (allPosts || []).filter(p => /^FINAL EXAM\s+\d+$/i.test(String(p.title || '').trim())).length;
  return `FINAL EXAM ${finals + 2}`;
}
function isExamTypeObj(t){
  const key = String(t?.type_key || '').toLowerCase();
  const lbl = String(t?.type_label || '').toLowerCase();
  return key === 'exam' || lbl.includes('exam');
}
function setExamMode(mode){
  _examMode = (mode === 'file') ? 'file' : 'questionnaire';
  const hid = document.getElementById('pmExamMode');
  if (hid) hid.value = _examMode;
  const bQuiz = document.getElementById('examModeBtnQuiz');
  const bFile = document.getElementById('examModeBtnFile');
  if (bQuiz) {
    bQuiz.style.background = _examMode === 'questionnaire' ? 'var(--primary-light)' : '';
    bQuiz.style.borderColor = _examMode === 'questionnaire' ? 'var(--primary)' : '';
    bQuiz.style.color = _examMode === 'questionnaire' ? 'var(--primary)' : '';
  }
  if (bFile) {
    bFile.style.background = _examMode === 'file' ? 'var(--primary-light)' : '';
    bFile.style.borderColor = _examMode === 'file' ? 'var(--primary)' : '';
    bFile.style.color = _examMode === 'file' ? 'var(--primary)' : '';
  }
  const activeType = (postTypes || []).find(x => String(x.id) === String(activePmTypeId));
  const activeIsExam = isExamTypeObj(activeType);
  const quizSec = document.getElementById('pmQuizSection');
  if (quizSec && activeIsExam) quizSec.style.display = (_examMode === 'questionnaire') ? '' : 'none';
  const hint = document.getElementById('gcExamModeHint');
  if (hint) hint.textContent = _examMode === 'questionnaire'
    ? 'Students will answer exam questions in the app.'
    : 'Students will upload a file as their exam submission.';
  schedulePostDraftSave();
}

function getNextNumberForType(prefix) {
  const re = new RegExp('^' + prefix.toUpperCase().replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s+(\\d+)');
  let max = 0;
  (allPosts || []).forEach(p => {
    const m = (p.title || '').trim().toUpperCase().match(re);
    if (m) max = Math.max(max, parseInt(m[1]));
  });
  return max + 1;
}

function selectGcType(t){
  activePmTypeId = t.id;
  document.getElementById('pmPostType').value = t.type_key;
  document.getElementById('pmPostTypeId').value = t.id;

  /* Update top bar icon + heading */
  const iconEl = document.getElementById('gcTypeIconLg');
  iconEl.style.background = t.color_bg;
  iconEl.style.color = t.color_text;
  iconEl.innerHTML = `<i class="fas ${t.icon}"></i>`;
  document.getElementById('gcModalHeading').textContent = t.type_label;
  document.getElementById('gcSubmitLabel').textContent = t.type_key === 'announcement' ? 'Post' : 'Assign';

  /* Show/hide form fields based on type */
  const isAnn = t.type_key === 'announcement';
  const isQuiz = !!t.has_quiz;
  const isExam = isExamTypeObj(t);
  const isExamQuizMode = isExam && _examMode === 'questionnaire';
  const showQuizBuilder = isQuiz && (!isExam || isExamQuizMode);
  const isLesson = (t.type_key||'').toLowerCase() === 'lesson' || (t.type_label||'').toLowerCase().includes('lesson');

  // Update title label based on type
  const titleLabelEl = document.getElementById('gcTitleLabel');
  if (titleLabelEl) {
    if (isLesson) {
      titleLabelEl.innerHTML = 'WEEK NO. <span style="color:var(--danger)">*</span>';
    } else if (isQuiz) {
      titleLabelEl.innerHTML = 'QUIZ TITLE <span style="color:var(--danger)">*</span>';
    } else {
      titleLabelEl.innerHTML = 'Title <span style="color:var(--danger)">*</span>';
    }
  }
  document.getElementById('gcTitleField').style.display = isAnn ? 'none' : '';
  const lessonHeadingRow = document.getElementById('gcLessonHeadingRow');
  if (lessonHeadingRow) lessonHeadingRow.classList.toggle('lesson-mode', isLesson);
  setLessonWeekMode(isLesson);
  const lessonPeriodField = document.getElementById('gcLessonPeriodField');
  if (lessonPeriodField) lessonPeriodField.style.display = isLesson ? '' : 'none';
  document.getElementById('gcBodyLabel').textContent = isAnn ? 'Announcement' : (isQuiz ? 'Instructions / Notes' : 'Description / Instructions');
  // Hide global topic field for quizzes (quiz coverage is inside pmQuizSection)
  document.getElementById('gcTopicField').style.display = (isAnn || isQuiz) ? 'none' : '';
  const topicLabelEl = document.getElementById('gcTopicLabel');
  if (topicLabelEl) {
    topicLabelEl.innerHTML = isLesson
      ? 'Topic <span style="color:var(--danger)">*</span>'
      : 'Topic <span style="font-size:.68rem;font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted);">(optional)</span>';
  }
  const examModeField = document.getElementById('gcExamModeField');
  if (examModeField) examModeField.style.display = isExam ? '' : 'none';
  document.getElementById('pmQuizSection').style.display = showQuizBuilder ? '' : 'none';
if (showQuizBuilder) {
    document.getElementById('gcGradableSettings').style.display = 'none';
    document.getElementById('gcPointsCard').style.display = 'none';
    document.getElementById('gcDueDateCard').style.display = 'none';
    // quiz settings moved to assign wizard
} else {
    document.getElementById('gcGradableSettings').style.display = t.is_gradable ? '' : 'none';
    document.getElementById('gcPointsCard').style.display = '';
    document.getElementById('gcDueDateCard').style.display = t.is_gradable ? '' : 'none';
    document.getElementById('gcQuizSettings').style.display = 'none';
}
  // Hide submission mode row for quizzes
  if (showQuizBuilder) {
    const smRow = document.getElementById('pmSubModeRow');
    if (smRow) smRow.remove();
  }

  // ── Activity vs Assignment differentiation ─────────────────────────────────
  const _key   = (t.type_key   || '').toLowerCase();
  const _label = (t.type_label || '').toLowerCase();
  const isAssignment = _key === 'assignment' || _label.includes('assignment');
  const isActivity   = _key === 'activity'   || _label.includes('activity');

  const ptsLabel  = document.getElementById('gcPointsLabel');
  const ddLabel   = document.getElementById('gcDueDateLabel');
  const ptsInput  = document.getElementById('pmPoints');
  const ddDisplay = document.getElementById('dueDateDisplay');

  if (isAssignment) {
    // Required: red asterisk on both, highlight border
    if (ptsLabel)  ptsLabel.innerHTML  = '<i class="fas fa-star" style="color:#d93025"></i> Points <span style="color:#d93025;font-size:.7rem;font-weight:700;">*required</span>';
    if (ddLabel)   ddLabel.innerHTML   = '<i class="fas fa-calendar-check" style="color:#d93025"></i> Due Date <span style="color:#d93025;font-size:.7rem;font-weight:700;">*required</span>';
    if (ptsInput)  ptsInput.style.border = '1.5px solid #d93025';
    if (ddDisplay) ddDisplay.style.borderColor = '#d93025';
    // Make sure both panels are visible
    document.getElementById('gcGradableSettings').style.display = '';
    document.getElementById('gcPointsCard').style.display = '';
    document.getElementById('gcDueDateCard').style.display = '';
    // Helper text on body
    document.getElementById('gcBodyLabel').textContent = 'Description / Instructions';
  } else if (isActivity) {
    // Optional: muted labels
    if (ptsLabel)  ptsLabel.innerHTML  = '<i class="fas fa-star"></i> Points <span style="color:var(--text-muted);font-size:.7rem;">(optional)</span>';
    if (ddLabel)   ddLabel.innerHTML   = '<i class="fas fa-calendar-check"></i> Due Date <span style="color:var(--text-muted);font-size:.7rem;">(optional)</span>';
    if (ptsInput)  ptsInput.style.border = '';
    if (ddDisplay) ddDisplay.style.borderColor = '';
  } else {
    // Reset to defaults for other types
    if (ptsLabel)  ptsLabel.innerHTML  = '<i class="fas fa-star"></i> Points';
    if (ddLabel)   ddLabel.innerHTML   = '<i class="fas fa-calendar-check"></i> Due Date';
    if (ptsInput)  ptsInput.style.border = '';
    if (ddDisplay) ddDisplay.style.borderColor = '';
  }

  /* Highlight active nav item */
  document.querySelectorAll('.gc-type-nav-item').forEach(item => {
    const active = item.dataset.typeId == t.id;
    item.classList.toggle('active', active);
    item.style.background = active ? t.color_bg : '';
    item.style.color = active ? t.color_text : '';
  });
  
  // Auto-fill title only for NEW posts and only if user hasn't typed anything
  const isEditing = document.getElementById('pmPostId').value.trim() !== '';
  if (!isEditing && !userTypedTitle) {
    const titleField = document.getElementById('pmTitle');
    const key   = (t.type_key   || '').toLowerCase();
    const label = (t.type_label || '').toLowerCase();

    if (key === 'lesson' || label.includes('lesson')) {
      autoSelectNextLessonWeek({ skipDraft:true, quiet:true });
      queueLessonWeekPickerForNewPost();
    } else if (isExam) {
      titleField.value = getNextExamPresetTitle();
    } else if (t.has_quiz || key === 'quiz' || label.includes('quiz') || label.includes('activity') || label.includes('assignment')) {
      titleField.value = t.type_label.toUpperCase() + ' ' + getNextNumberForType(t.type_label.toUpperCase());
    }
  }

  if (isExam) {
    const saved = (document.getElementById('pmExamMode')?.value || 'questionnaire').toLowerCase();
    setExamMode(saved === 'file' ? 'file' : 'questionnaire');
  } else {
    setExamMode('questionnaire');
  }
  if (!showQuizBuilder) renderSubModeRow();
if (showQuizBuilder) setTimeout(loadLessonSelectorForAI, 80);
}

function onTimeModeChange(){
  const mode = document.getElementById('pmTimeMode').value;
  document.getElementById('pmTimePerQuizWrap').style.display = (mode === 'per_quiz')     ? '' : 'none';
  document.getElementById('pmTimePerQWrap').style.display    = (mode === 'per_question') ? '' : 'none';
  renderQuizBuilder();
}

function normalizeWholeSeconds(value, min = 1) {
  const raw = String(value ?? '').replace(/[^\d]/g, '');
  if (raw === '') return '';
  const parsed = parseInt(raw, 10);
  if (!Number.isFinite(parsed)) return '';
  return String(Math.max(min, parsed));
}

function getDefaultPerQuestionSeconds() {
  const mode = document.getElementById('pmTimeMode')?.value || 'none';
  if (mode !== 'per_question') return 0;
  const parsed = parseInt(document.getElementById('pmTimePerQuestionSecs')?.value || '0', 10);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
}

function onDefaultPerQuestionSecondsInput(el) {
  if (!el) return;
  el.value = normalizeWholeSeconds(el.value, 1);
  renderQuizBuilder();
  schedulePostDraftSave();
}

function getEffectiveQuestionSeconds(q) {
  const own = parseInt(q?.time_limit_seconds || 0, 10);
  if (Number.isFinite(own) && own > 0) return own;
  return getDefaultPerQuestionSeconds() || '';
}

function setQuestionTimeLimit(questionIndex, value) {
  if (!quizQuestions[questionIndex]) return;
  const normalized = normalizeWholeSeconds(value, 1);
  quizQuestions[questionIndex].time_limit_seconds = normalized === '' ? '' : parseInt(normalized, 10);
  schedulePostDraftSave();
}

/* ══ ASSIGN QUIZ WIZARD ══ */
let _wizStep=1,_wizMode=null,_wizTimeMode=null,_wizAttempts=1,_wizDone=false;
let _wizSchedYear = new Date().getFullYear();
let _wizSchedMonth = new Date().getMonth();

function _wizPad(n){ return String(n).padStart(2,'0'); }
function _wizDateToLocalInput(d){
  return `${d.getFullYear()}-${_wizPad(d.getMonth()+1)}-${_wizPad(d.getDate())}T${_wizPad(d.getHours())}:${_wizPad(d.getMinutes())}`;
}
function _wizDateFmt(d){
  return d.toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'});
}
function _wizDurationHuman(totalMinutes){
  const mins = Math.max(0, Math.round(Number(totalMinutes) || 0));
  const hrs = Math.floor(mins / 60);
  const rem = mins % 60;
  if (hrs <= 0) return `${rem} minute${rem === 1 ? '' : 's'}`;
  if (rem <= 0) return `${hrs} hour${hrs === 1 ? '' : 's'}`;
  return `${hrs} hour${hrs === 1 ? '' : 's'} and ${rem} minute${rem === 1 ? '' : 's'}`;
}
function _wizBuildDateTime(datePart,timePart){
  if(!datePart || !timePart) return null;
  const v = new Date(`${datePart}T${timePart}:00`);
  return Number.isNaN(v.getTime()) ? null : v;
}
function _wizFmtDateChip(d){
  return d.toLocaleDateString('en-US',{month:'short',day:'numeric'});
}
function _wizDateValue(d){
  return `${d.getFullYear()}-${_wizPad(d.getMonth()+1)}-${_wizPad(d.getDate())}`;
}
function wizToggleDateMenu(ev){
  if(ev){ ev.preventDefault(); ev.stopPropagation(); }
  const menu=document.getElementById('wizDateMenu');
  if(!menu) return;
  if(menu.classList.contains('show')){ menu.classList.remove('show'); return; }
  const base=new Date();
  base.setHours(0,0,0,0);
  let html='<div class="wiz-date-grid">';
  for(let i=0;i<12;i++){
    const d=new Date(base);
    d.setDate(base.getDate()+i);
    html += `<button type="button" class="wiz-date-opt" onclick="wizSelectDateValue('${_wizDateValue(d)}')">${i===0?'Today':i===1?'Tomorrow':_wizFmtDateChip(d)}</button>`;
  }
  html+='</div>';
  menu.innerHTML=html;
  menu.classList.add('show');
}
function wizSelectDateValue(v){
  const d=document.getElementById('wizDateOnly');
  if(!d) return;
  d.value=v;
  const sel = new Date(`${v}T00:00:00`);
  if (!Number.isNaN(sel.getTime())) {
    _wizSchedYear = sel.getFullYear();
    _wizSchedMonth = sel.getMonth();
  }
  const menu=document.getElementById('wizDateMenu');
  if(menu) menu.classList.remove('show');
  wizRenderSchedCalendar();
  _wizSyncScheduleInputs();
  _wizRenderStep();
}
function wizSetDatePreset(days){
  const base=new Date();
  base.setHours(0,0,0,0);
  base.setDate(base.getDate()+days);
  wizSelectDateValue(_wizDateValue(base));
  _wizSyncScheduleInputs();
  _wizRenderStep();
}
function wizSetDuration(hours){
  const el=document.getElementById('wizDurationHours');
  if(!el) return;
  el.value=hours;
  _wizSyncScheduleInputs();
  _wizRenderStep();
}
function wizSchedNavMonth(dir){
  _wizSchedMonth += dir;
  if (_wizSchedMonth > 11) { _wizSchedMonth = 0; _wizSchedYear++; }
  if (_wizSchedMonth < 0)  { _wizSchedMonth = 11; _wizSchedYear--; }
  wizRenderSchedCalendar();
}
function wizRenderSchedCalendar(){
  const grid = document.getElementById('wizSchedDayGrid');
  const lbl = document.getElementById('wizSchedMonthLabel');
  if(!grid || !lbl) return;
  const MONTHS=['January','February','March','April','May','June','July','August','September','October','November','December'];
  lbl.textContent = `${MONTHS[_wizSchedMonth]} ${_wizSchedYear}`;
  const today = new Date(); today.setHours(0,0,0,0);
  const firstDow = new Date(_wizSchedYear, _wizSchedMonth, 1).getDay();
  const daysIn = new Date(_wizSchedYear, _wizSchedMonth + 1, 0).getDate();
  const selected = (document.getElementById('wizDateOnly')?.value || '').trim();
  let html = '';
  for(let i=0;i<firstDow;i++) html += `<div style="aspect-ratio:1/1;"></div>`;
  for(let d=1; d<=daysIn; d++){
    const dateStr = `${_wizSchedYear}-${_wizPad(_wizSchedMonth+1)}-${_wizPad(d)}`;
    const dt = new Date(`${dateStr}T00:00:00`);
    const isPast = dt < today;
    const isToday = dt.getTime() === today.getTime();
    const isSel = selected === dateStr;
    const bg = isSel ? 'var(--primary)' : (isToday ? 'var(--primary-light)' : 'transparent');
    const color = isSel ? '#fff' : (isPast ? '#c2c8d0' : (isToday ? 'var(--primary)' : 'var(--text)'));
    const weight = (isSel || isToday) ? 700 : 600;
    const ring = (isToday && !isSel) ? 'inset 0 0 0 1.5px var(--primary)' : 'none';
    html += `<button type="button" ${isPast?'disabled':''} onclick="${isPast?'':`wizSelectDateValue('${dateStr}')`}" style="aspect-ratio:1/1;border:none;border-radius:8px;background:${bg};color:${color};font-size:.76rem;font-weight:${weight};box-shadow:${ring};cursor:${isPast?'not-allowed':'pointer'};">${d}</button>`;
  }
  grid.innerHTML = html;
}
function _wizSyncScheduleInputs(){
  const dateOnly=(document.getElementById('wizDateOnly')?.value||'').trim();
  const startTime=(document.getElementById('wizStartTime')?.value||'').trim();
  const startDt=_wizBuildDateTime(dateOnly,startTime);
  let endDt=null;
  if(startDt){
    const hrs=parseFloat(document.getElementById('wizDurationHours')?.value||'0');
    if(hrs>0){
      endDt=new Date(startDt.getTime() + Math.round(hrs*60*60*1000));
    }
  }
  // UI labels
  const dl=document.getElementById('wizDateLabel');
  if(dl){
    if(dateOnly){
      const parts=dateOnly.split('-');
      dl.textContent = `${parts[1]}/${parts[2]}/${parts[0]}`;
    } else {
      dl.textContent='Select date';
    }
  }
  const dur=parseFloat(document.getElementById('wizDurationHours')?.value||'0');
  const durMap={0.5:'wizDurBtn05',1:'wizDurBtn1',2:'wizDurBtn2',3:'wizDurBtn3',4:'wizDurBtn4'};
  ['wizDurBtn05','wizDurBtn1','wizDurBtn2','wizDurBtn3','wizDurBtn4'].forEach(id=>{
    const e=document.getElementById(id); if(e) e.classList.remove('active');
  });
  const activeDurBtn=document.getElementById(durMap[dur]);
  if(activeDurBtn) activeDurBtn.classList.add('active');
  const todayBtn=document.getElementById('wizDatePresetToday');
  const tomorrowBtn=document.getElementById('wizDatePresetTomorrow');
  const nextBtn=document.getElementById('wizDatePresetNext');
  [todayBtn,tomorrowBtn,nextBtn].forEach(b=>b&&b.classList.remove('active'));
  if(dateOnly){
    const now=new Date(); now.setHours(0,0,0,0);
    const dsel=new Date(`${dateOnly}T00:00:00`);
    const diff=Math.round((dsel-now)/(24*60*60*1000));
    if(diff===0&&todayBtn) todayBtn.classList.add('active');
    if(diff===1&&tomorrowBtn) tomorrowBtn.classList.add('active');
    if(diff===2&&nextBtn) nextBtn.classList.add('active');
  }

  const openField=document.getElementById('wizOpenAt');
  const closeField=document.getElementById('wizDueDate');
  if(openField) openField.value=startDt?_wizDateToLocalInput(startDt):'';
  if(closeField) closeField.value=endDt?_wizDateToLocalInput(endDt):'';

  const durLabel=document.getElementById('wizDurationLabelText');
  if(durLabel){
    if(startDt && endDt && endDt.getTime()>startDt.getTime()){
      const diffMin=Math.round((endDt.getTime()-startDt.getTime())/60000);
      durLabel.textContent=_wizDurationHuman(diffMin);
    } else if(dur>0){
      durLabel.textContent=_wizDurationHuman(Math.round(dur*60));
    } else {
      durLabel.textContent='—';
    }
  }
  wizRenderSchedCalendar();

  const preview=document.getElementById('wizWindowPreview');
  if(preview){
    if(startDt && endDt && endDt.getTime()>startDt.getTime()){
      const diffMin=Math.round((endDt.getTime()-startDt.getTime())/60000);
      preview.textContent=`${_wizDateFmt(startDt)} to ${_wizDateFmt(endDt)} (${_wizDurationHuman(diffMin)})`;
    } else if(startDt){
      preview.textContent=`Starts ${_wizDateFmt(startDt)} (set duration)`;
    } else {
      preview.textContent='Select date and start time';
    }
  }
}
function _wizInitTimeOptions(){
  const sel=document.getElementById('wizStartTime');
  if(!sel || sel.options.length) return;
  for(let h=0;h<24;h++){
    for(let m=0;m<60;m+=15){
      const val=`${_wizPad(h)}:${_wizPad(m)}`;
      const d=new Date(2000,0,1,h,m,0,0);
      const label=d.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'});
      const o=document.createElement('option');
      o.value=val; o.textContent=label;
      sel.appendChild(o);
    }
  }
}

document.addEventListener('click', function(e){
  const menu=document.getElementById('wizDateMenu');
  if(!menu || !menu.classList.contains('show')) return;
  if(menu.contains(e.target)) return;
  if(e.target.closest('.wiz-date-pill')) return;
  menu.classList.remove('show');
});

function openAssignWizard(){
  _wizStep=1;_wizMode=null;_wizTimeMode=null;_wizAttempts=1;
  _wizInitTimeOptions();
  // reset UI
  ['wizCardLive','wizCardDue'].forEach(id=>document.getElementById(id).className='wiz-mode-card');
  ['wizCheckLive','wizCheckDue'].forEach(id=>document.getElementById(id).style.display='none');
  document.getElementById('wizDueDateWrap').style.display='none';
  const now=new Date();
  now.setMinutes(now.getMinutes()+15);
  now.setSeconds(0,0);
  document.getElementById('wizDateOnly').value=`${now.getFullYear()}-${_wizPad(now.getMonth()+1)}-${_wizPad(now.getDate())}`;
  _wizSchedYear = now.getFullYear();
  _wizSchedMonth = now.getMonth();
  const roundedMin=Math.ceil(now.getMinutes()/15)*15;
  if(roundedMin>=60){ now.setHours(now.getHours()+1); now.setMinutes(0); }
  else { now.setMinutes(roundedMin); }
  document.getElementById('wizStartTime').value=`${_wizPad(now.getHours())}:${_wizPad(now.getMinutes())}`;
  document.getElementById('wizDurationHours').value='1';
  _wizSyncScheduleInputs();
  wizRenderSchedCalendar();
  ['none','per_quiz','per_question'].forEach(m=>document.getElementById('wizTc_'+m).className='wiz-time-card');
  document.getElementById('wizQuizMinWrap').style.display='none';
  document.getElementById('wizQSecsWrap').style.display='none';
  document.getElementById('wizAttemptsVal').textContent='1';
  _wizRenderStep();
  document.getElementById('wizOverlay').style.display='flex';
}

function wizClose(){
  document.getElementById('wizOverlay').style.display='none';
  const btn=document.getElementById('pmSubmitBtn');
  if(btn){btn.disabled=false;btn.innerHTML='<i class="fas fa-paper-plane"></i> <span id="gcSubmitLabel">Assign</span>';}
}

function wizSelectMode(mode){
  _wizMode=mode;
  document.getElementById('wizCardLive').className='wiz-mode-card'+(mode==='live'?' sel-live':'');
  document.getElementById('wizCardDue').className='wiz-mode-card'+(mode==='due_date'?' sel-due':'');
  document.getElementById('wizCheckLive').style.display=mode==='live'?'flex':'none';
  document.getElementById('wizCheckDue').style.display=mode==='due_date'?'flex':'none';
  document.getElementById('wizDueDateWrap').style.display=mode==='due_date'?'':'none';
  if(mode==='due_date') _wizSyncScheduleInputs();
  _wizRenderStep();
}

function wizSelectTime(mode){
  _wizTimeMode=mode;
  ['none','per_quiz','per_question'].forEach(m=>document.getElementById('wizTc_'+m).className='wiz-time-card'+(mode===m?' sel':''));
  document.getElementById('wizQuizMinWrap').style.display=(mode==='per_quiz')?'':'none';
  document.getElementById('wizQSecsWrap').style.display=(mode==='per_question')?'':'none';
  _wizRenderStep();
}

function wizStepAttempts(d){
  _wizAttempts=Math.max(0,Math.min(10,_wizAttempts+d));
  document.getElementById('wizAttemptsVal').textContent=_wizAttempts;
}

function wizGoTo(s){_wizStep=s;_wizRenderStep();}
function wizBack(){if(_wizStep>1){_wizStep--;_wizRenderStep();}}

function wizNext(){
  if(_wizStep===1){
    if(!_wizMode)return;
    if(_wizMode==='due_date'){
      _wizSyncScheduleInputs();
      const openAt=document.getElementById('wizOpenAt').value;
      const closeAt=document.getElementById('wizDueDate').value;
      if(!openAt){document.getElementById('wizDateOnly').focus();return;}
      if(!closeAt){document.getElementById('wizDurationHours').focus();return;}
      if(new Date(closeAt).getTime()<=new Date(openAt).getTime()){
        toast('End time must be later than start time.', 'error');
        document.getElementById('wizDurationHours').focus();
        return;
      }
    }
    _wizStep=2;_wizRenderStep();
  } else if(_wizStep===2){
    if(!_wizTimeMode)return;
    _wizBuildReview();
    _wizStep=3;_wizRenderStep();
  } else {
    _wizFinish();
  }
}

function _wizBuildReview(){
  const mi=document.getElementById('wizRevModeIcon');
  const mv=document.getElementById('wizRevMode');
  if(_wizMode==='live'){
    mi.innerHTML=' <i class="fas fa-bolt"></i>';mi.style.background='#fdecea';mi.style.color='var(--danger)';
    mv.innerHTML=' <span style="background:#fdecea;color:var(--danger);padding:.15rem .5rem;border-radius:20px;font-size:.76rem;font-weight:700;"><i class="fas fa-bolt" style="margin-right:.25rem;"></i>Live Mode</span>';
  } else {
    _wizSyncScheduleInputs();
    const ov=document.getElementById('wizOpenAt').value;
    const dv=document.getElementById('wizDueDate').value;
    const os=ov?new Date(ov).toLocaleString('en-US',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}):'-';
    const ds=dv?new Date(dv).toLocaleString('en-US',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}):'-';
    mi.innerHTML=' <i class="fas fa-calendar-day"></i>';mi.style.background='#e8f0fe';mi.style.color='var(--accent)';
    mv.innerHTML=`<span style="background:#e8f0fe;color:var(--accent);padding:.15rem .5rem;border-radius:20px;font-size:.76rem;font-weight:700;">Window: ${os} to ${ds}</span>`;
  }
  const tv=document.getElementById('wizRevTime');
  if(_wizTimeMode==='none')tv.textContent='No time limit';
  else if(_wizTimeMode==='per_quiz')tv.textContent=(document.getElementById('wizTimeMinutes').value||'-')+' min (whole quiz)';
  else tv.textContent=(document.getElementById('wizTimeSecs').value||'-')+'s per question';
}

function _wizRenderStep(){
  for(let i=1;i<=3;i++) document.getElementById('wizStep'+i).style.display=(_wizStep===i)?'':'none';
  document.getElementById('wizProgress').style.width=((_wizStep-1)/2*100)+'%';
  const bb=document.getElementById('wizBackBtn');bb.disabled=_wizStep===1;bb.style.opacity=_wizStep===1?'.35':'1';
  for(let i=1;i<=3;i++){
    const d=document.getElementById('wizDot'+i);
    d.className='wiz-dot'+(_wizStep===i?' active':_wizStep>i?' done':'');
  }
  const labels=['Step 1 of 3 - Delivery Mode','Step 2 of 3 - Time Limit','Step 3 of 3 - Review'];
  const titles=['How should students take this quiz?','Set a time limit','Ready to save!'];
  document.getElementById('wizStepLabel').textContent=labels[_wizStep-1];
  document.getElementById('wizStepTitle').textContent=titles[_wizStep-1];
  const canNext=_wizStep===1?!!_wizMode&&(_wizMode==='live'||(!!document.getElementById('wizOpenAt').value&&!!document.getElementById('wizDueDate').value&&new Date(document.getElementById('wizDueDate').value).getTime()>new Date(document.getElementById('wizOpenAt').value).getTime()))
               :_wizStep===2?!!_wizTimeMode:true;
  const nb=document.getElementById('wizNextBtn');
  nb.disabled=!canNext;nb.style.opacity=canNext?'1':'.45';nb.style.cursor=canNext?'pointer':'not-allowed';
  nb.innerHTML=_wizStep===3?'<i class="fas fa-paper-plane"></i> Save Quiz':'Continue <i class="fas fa-arrow-right"></i>';
}

async function _wizFinish(){
  _wizSyncScheduleInputs();
  // Write wizard values into existing hidden inputs
  document.getElementById('pmTimeMode').value=_wizTimeMode;
  document.getElementById('pmMaxAttempts').value=_wizAttempts;
  if(_wizTimeMode==='per_quiz') document.getElementById('pmTimePerQuizMinutes').value=document.getElementById('wizTimeMinutes').value;
  if(_wizTimeMode==='per_question') document.getElementById('pmTimePerQuestionSecs').value=document.getElementById('wizTimeSecs').value;
  if(_wizMode==='due_date'){
    document.getElementById('pmOpenAt').value=document.getElementById('wizOpenAt').value;
    document.getElementById('pmCloseAt').value=document.getElementById('wizDueDate').value;
  }
  // Store quiz_mode in a hidden field (created once)
  let qmf=document.getElementById('pmQuizMode');
  if(!qmf){qmf=document.createElement('input');qmf.type='hidden';qmf.id='pmQuizMode';document.getElementById('gcFormArea').appendChild(qmf);}
  qmf.value=_wizMode;
  document.getElementById('wizOverlay').style.display='none';
  _wizDone=true;
  await submitPost();
}

function closePostModal(){
  savePostDraftNow(true);
  const panel=document.getElementById('gcModalPanel');
  const overlay=document.getElementById('gcModalOverlay');
  panel.classList.add('closing');
  overlay.classList.remove('show');
  document.body.style.overflow='';
  setTimeout(()=>{panel.style.display='none';panel.classList.remove('closing');},220);
}

/* Close on overlay click */
document.getElementById('gcModalOverlay').addEventListener('click',closePostModal);
document.getElementById('lessonWeekPickerBack').addEventListener('click',e=>{if(e.target===document.getElementById('lessonWeekPickerBack'))closeLessonWeekPicker();});
document.getElementById('gcModalPanel').addEventListener('input', () => schedulePostDraftSave());
document.getElementById('gcModalPanel').addEventListener('change', () => schedulePostDraftSave());
window.addEventListener('beforeunload', () => savePostDraftNow(true));

/* ══════════════════════════════════════════
   QUIZ BUILDER
══════════════════════════════════════════ */
let qIdCounter=0;
function addQuestion(){
  qIdCounter++;const id='q_'+qIdCounter;
  quizQuestions.push({id,question:'',points:1,cognitive_level:'',time_limit_seconds:'',choices:[{text:'',is_correct:true},{text:'',is_correct:false},{text:'',is_correct:false},{text:'',is_correct:false}]});
  schedulePostDraftSave();
  renderQuizBuilder();setTimeout(()=>{const el=document.getElementById('qtext_'+id);if(el)el.focus();},50);
}
function removeQuestion(id){quizQuestions=quizQuestions.filter(q=>q.id!==id);schedulePostDraftSave();renderQuizBuilder();}
function addChoice(qId){const q=quizQuestions.find(x=>x.id===qId);if(!q||q.choices.length>=6)return;q.choices.push({text:'',is_correct:false});schedulePostDraftSave();renderQuizBuilder();}
function removeChoice(qId,cIdx){
  const q=quizQuestions.find(x=>x.id===qId);if(!q||q.choices.length<=2)return;
  q.choices.splice(cIdx,1);if(!q.choices.some(c=>c.is_correct))q.choices[0].is_correct=true;schedulePostDraftSave();renderQuizBuilder();
}
function fmtCognitiveLevel(level){
  const v = String(level || '').trim().toLowerCase();
  if (!v) return '';
  return v.charAt(0).toUpperCase() + v.slice(1);
}
function inferCognitiveLevelFromQuestion(questionText, choices = [], answerText = ''){
  const text = String(questionText || '').trim().toLowerCase();
  const answer = String(answerText || '').trim().toLowerCase();
  const combined = `${text} ${answer}`;
  if (!combined) return 'remembering';

  const hasAny = arr => arr.some(k => combined.includes(k));

  if (hasAny(['propose', 'create', 'design', 'develop', 'construct', 'formulate', 'compose', 'plan', 'build a', 'invent'])) {
    return 'creating';
  }
  if (hasAny(['evaluate', 'justify', 'defend', 'critique', 'assess', 'judge', 'recommend', 'which is better', 'best approach'])) {
    return 'evaluating';
  }
  if (hasAny(['analyze', 'compare', 'differentiate', 'distinguish', 'examine', 'why might', 'how might', 'break down', 'cause of'])) {
    return 'analyzing';
  }
  if (hasAny(['apply', 'use', 'demonstrate', 'solve', 'implement', 'calculate', 'what would happen', 'if you', 'scenario', 'specific app project'])) {
    return 'applying';
  }
  if (hasAny(['explain', 'describe', 'summarize', 'interpret', 'classify', 'discuss', 'in your own words', 'according to the'])) {
    return 'understanding';
  }
  return 'remembering';
}
function ensureQuestionCognitiveLevel(q){
  if (!q) return 'remembering';
  const inferred = inferCognitiveLevelFromQuestion(q.question || '', q.choices || [], q.answer || q.answer_key || '');
  const current = String(q.cognitive_level || '').trim().toLowerCase();
  const allowed = ['remembering','understanding','applying','analyzing','evaluating','creating'];
  q.cognitive_level = allowed.includes(current) ? current : inferred;
  return q.cognitive_level;
}
function getBloomBadgeStyle(level){
  const lv = String(level || '').toLowerCase();
  const map = {
    remembering: ['#e6f7f2','#0f8b72','rgba(15,139,114,.22)'],
    understanding: ['#e8f0fe','#1f73db','rgba(31,115,219,.22)'],
    applying: ['#edf7e8','#3b8d17','rgba(59,141,23,.22)'],
    analyzing: ['#fff4df','#b26a00','rgba(178,106,0,.22)'],
    evaluating: ['#fff0ec','#d84315','rgba(216,67,21,.22)'],
    creating: ['#f5edff','#7c3aed','rgba(124,58,237,.22)']
  };
  const [bg, fg, bd] = map[lv] || ['#f3f4f6','#4b5563','rgba(75,85,99,.22)'];
  return `background:${bg};color:${fg};border:1px solid ${bd};`;
}
function renderQuizBuilder(){
  const list = document.getElementById('quizQuestionsList');
  const letters = 'ABCDEF';

  list.innerHTML = quizQuestions.map((q, qi) => `
    <div class="quiz-question-card" id="qcard_${q.id}">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:.6rem;margin-bottom:.55rem;flex-wrap:wrap;">
        <span style="font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Bloom Taxonomy</span>
        <div style="display:inline-flex;align-items:center;gap:.45rem;flex-shrink:0;">
          <i class="fas fa-brain" style="color:#1f73db;font-size:.78rem;"></i>
          <span style="display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:.38rem .82rem;border-radius:999px;font-size:.8rem;font-weight:700;line-height:1;white-space:nowrap;${getBloomBadgeStyle(ensureQuestionCognitiveLevel(q))}">
            ${fmtCognitiveLevel(ensureQuestionCognitiveLevel(q))}
          </span>
        </div>
      </div>
      <div class="qc-head">
        <div class="q-num">${qi + 1}</div>
        <input type="text" id="qtext_${q.id}" class="gc-input" style="flex:1;" placeholder="Question ${qi + 1}…" value="${esc(q.question || '')}" oninput="quizQuestions[${qi}].question=this.value;quizQuestions[${qi}].cognitive_level=inferCognitiveLevelFromQuestion(this.value, quizQuestions[${qi}].choices || [], quizQuestions[${qi}].answer || '');updateQuizPts();renderQuizBuilder();">
        <div class="q-pts-wrap">
          <input type="number" value="${q.points}" min="0.5" step="0.5" style="width:52px;" oninput="quizQuestions[${qi}].points=parseFloat(this.value)||1;updateQuizPts()">
          <span>pts</span>
        </div>
        ${(document.getElementById('pmTimeMode')?.value || 'none') === 'per_question' ? `
          <div class="q-pts-wrap" style="min-width:120px;">
            <input type="number" value="${getEffectiveQuestionSeconds(q)}" min="1" step="1" inputmode="numeric" style="width:68px;" oninput="this.value=normalizeWholeSeconds(this.value,1);setQuestionTimeLimit(${qi}, this.value)">
            <span>sec</span>
          </div>
        ` : ''}
      </div>

      ${
        q.choices && q.choices.length
          ? `
            <div class="choices-list">
              ${q.choices.map((c, ci) => `
                <div class="choice-row">
                  <input type="radio" class="correct-radio" name="correct_${q.id}" ${c.is_correct ? 'checked' : ''} onchange="quizQuestions[${qi}].choices.forEach((x,i)=>x.is_correct=(i===${ci}));renderQuizBuilder()">
                  <div class="choice-letter" style="${c.is_correct ? 'background:var(--primary);border-color:var(--primary);color:#fff;' : ''}">${letters[ci]}</div>
                  <input type="text" value="${esc(c.text || '')}" placeholder="Choice ${letters[ci]}…" oninput="quizQuestions[${qi}].choices[${ci}].text=this.value;quizQuestions[${qi}].cognitive_level=inferCognitiveLevelFromQuestion(quizQuestions[${qi}].question || '', quizQuestions[${qi}].choices || [], quizQuestions[${qi}].answer || '');updateQuizPts();">
                  <button type="button" class="choice-rm" onclick="removeChoice('${q.id}',${ci})"><i class="fas fa-times"></i></button>
                </div>
              `).join('')}
            </div>
          `
          : `
            <div class="choices-list">
              <div class="choice-row">
                <div class="choice-letter" style="background:var(--primary);border-color:var(--primary);color:#fff;">
                  <i class="fas fa-key" style="font-size:.65rem;"></i>
                </div>
                <input type="text" value="${esc(q.answer || '')}" placeholder="Correct answer for identification…" oninput="quizQuestions[${qi}].answer=this.value;quizQuestions[${qi}].cognitive_level=inferCognitiveLevelFromQuestion(quizQuestions[${qi}].question || '', quizQuestions[${qi}].choices || [], this.value);updateQuizPts();renderQuizBuilder();">
              </div>
            </div>
          `
      }

      <div class="q-foot">
        ${
          q.choices && q.choices.length
            ? `<button type="button" class="add-choice-btn" onclick="addChoice('${q.id}')"><i class="fas fa-plus"></i> Add choice</button>`
            : `<span style="font-size:.75rem;color:var(--text-muted);">Identification answer key</span>`
        }
        <button type="button" class="del-q-btn" onclick="removeQuestion('${q.id}')"><i class="fas fa-trash"></i> Remove</button>
      </div>
    </div>
  `).join('');

  updateQuizPts();
}
function updateQuizPts(){
  const total=quizQuestions.reduce((s,q)=>s+(parseFloat(q.points)||0),0);
  const el=document.getElementById('quizTotalPts');
  if(el)el.textContent=total.toFixed(total%1===0?0:1)+' pts total';

  const mixEl = document.getElementById('quizBloomMix');
  if (!mixEl) return;
  const order = ['remembering','understanding','applying','analyzing','evaluating','creating'];
  const counts = {};
  order.forEach(k => counts[k] = 0);
  let unset = 0;
  (quizQuestions || []).forEach(q => {
    const lv = String(q.cognitive_level || '').toLowerCase();
    if (counts.hasOwnProperty(lv)) counts[lv] += 1;
    else unset += 1;
  });
  const chip = (label, n, colorBg, colorTx, border) =>
    `<span style="display:inline-flex;align-items:center;gap:.28rem;padding:.14rem .5rem;border-radius:14px;font-size:.66rem;font-weight:700;background:${colorBg};color:${colorTx};border:1px solid ${border};">${label}: ${n}</span>`;
  let html = order.filter(k => counts[k] > 0).map(k =>
    chip(fmtCognitiveLevel(k), counts[k], '#e8f0fe', '#1f73db', 'rgba(31,115,219,.25)')
  ).join('');
  if (unset > 0) html += chip('Not set', unset, '#f3f4f6', '#4b5563', 'rgba(75,85,99,.25)');
  mixEl.innerHTML = html || chip('No taxonomy yet', 0, '#f3f4f6', '#4b5563', 'rgba(75,85,99,.25)');
}
function updateAiTotalQuestions() {
  const mcqEl = document.getElementById('aiMcqCount');
  const identificationEl = document.getElementById('aiIdentificationCount');
  const label = document.getElementById('aiTotalQuestionsLabel');

  if (!mcqEl || !identificationEl || !label) return;

  const mcq = Math.max(0, parseInt(mcqEl.value || '0', 10));
  const identification = Math.max(0, parseInt(identificationEl.value || '0', 10));
  const total = mcq + identification;

  label.textContent = `${total} total question${total !== 1 ? 's' : ''}`;
}

let _lessonAttachments = []; // cache of lesson post attachments for this class

async function loadLessonSelectorForAI() {
  const validExts = ['pdf','pptx','docx','txt'];
  _lessonAttachments = [];

  try {
    const res  = await fetch(`API/facultyUI/classroom/get_classroom.php?class_id=${encodeURIComponent(CLASS_ID)}`);
    const data = await res.json();
    if (data.status !== 'success') return;

    const posts = data.posts || [];
    if (posts.length) allPosts = posts; // keep cache fresh

    posts.forEach(p => {
      const typeObj  = postTypes.find(t => t.id == p.post_type_id);
      const key      = (typeObj?.type_key   || p.post_type  || '').toLowerCase();
      const label    = (typeObj?.type_label  || p.sub_label  || '').toLowerCase();
      const titleRaw = (p.title || '').trim();

      // ── Broad lesson detection ──────────────────────────────────────────
      // Match: type key is "lesson", label contains "lesson", OR the title
      // looks like "WEEK N" (with or without space/dash).
      // We intentionally do NOT require a matching title — any post typed
      // as a lesson is eligible regardless of what the teacher named it.
const isLesson = key === 'lesson'
                    || label.includes('lesson')
                    || /^week[\s\-_]?\d/i.test(titleRaw)
                    || (p.sub_label || '').toLowerCase().includes('lesson');

      if (!isLesson) return;

      // Best display title: prefer explicit title, fall back to topic excerpt
      const postDisplayTitle = titleRaw
        || (p.topic ? p.topic.substring(0, 50) : '')
        || p.sub_label
        || 'Untitled Lesson';

      // Build a combined search key — title + topic + sub_label + all filenames
      // This lets "week 1" match files named "WEEK-1-Introduction.pptx"
      const titleNums = (titleRaw.match(/\d+/g) || []).join(' ');
      const fileNamesKey = (p.attachments || [])
        .map(a => (a.file_name || '').replace(/[-_]+/g, ' '))
        .join(' ');
      const searchKey = [
        titleRaw,
        p.topic      || '',
        p.sub_label  || '',
        titleNums,
        titleNums ? 'week ' + titleNums : '',
        fileNamesKey
      ].join(' ').toLowerCase();

      (p.attachments || []).forEach(a => {
        const aType = (a.attach_type || a.type || '').toLowerCase();
        if (aType === 'youtube' || aType === 'link') return;

        const fname = a.file_name || a.filename || a.original_name || '';
        const fpath = a.file_path || a.filepath  || a.url || '';
        if (!fname || !fpath) return;

        const ext = fname.split('.').pop().toLowerCase();
        if (!validExts.includes(ext)) return;

        _lessonAttachments.push({
          attach_id:  a.id || a.attach_id || '',
          file_name:  fname,
          file_path:  fpath,
          mime_type:  a.mime_type || '',
          post_title: postDisplayTitle,
          topic:      p.topic || '',
          week:       titleRaw,
          search_key: searchKey,  // ← combined search target
          ext
        });
      });
    });

  } catch(e) {
    console.error('[AQ] loadLessonSelectorForAI error:', e);
  }
}

async function generateQuizAI() {
  const selKeys = Object.keys(_aqSelectedFiles || {});
  const mcqCount = parseInt(document.getElementById('aqMcqCount')?.value || '0', 10);
  const pointsPerQuestion = parseFloat(document.getElementById('aqPointsPerQuestion')?.value || '0') || 1;

  if (!selKeys.length && !_aqDirectFile) {
    toast('Please select at least one lesson file or upload a file directly.', 'error');
    return;
  }
  if (mcqCount < 10) {
    toast('Please set at least 10 questions.', 'error');
    return;
  }

  const _regen = document.getElementById('aiRegenerateWrap');
  if (_regen) _regen.style.display = 'none';

  try {
    await runAqGenerate(mcqCount, 0, pointsPerQuestion, _aqDifficulty, selKeys);
  } catch (e) {
    console.error(e);
    toast('Network error while generating quiz.', 'error');
    const _regen = document.getElementById('aiRegenerateWrap');
    if (_regen) { _regen.style.display = 'flex'; }
  }
  schedulePostDraftSave();
}
/* ══════════════════════════════════════════
   ATTACHMENTS
══════════════════════════════════════════ */
function triggerFileUpload(){document.getElementById('fileInput').click();}
document.getElementById('fileInput').addEventListener('change',function(){
  Array.from(this.files).forEach(f=>{pendingFiles.push(f);addAttachPreview('file',f.name,()=>{pendingFiles=pendingFiles.filter(x=>x!==f);schedulePostDraftSave();});});this.value='';schedulePostDraftSave();
});
function addYouTube(){Swal.fire({title:'YouTube URL',input:'url',inputPlaceholder:'https://youtube.com/watch?v=…',showCancelButton:true,confirmButtonText:'Add',confirmButtonColor:'#ff0000'}).then(r=>{if(r.isConfirmed&&r.value){const url=r.value.trim();pendingLinks.push({type:'youtube',url});addAttachPreview('youtube',url,()=>{pendingLinks=pendingLinks.filter(x=>x.url!==url);schedulePostDraftSave();});schedulePostDraftSave();}});}
function addLink(){Swal.fire({title:'Add Link',input:'url',inputPlaceholder:'https://…',showCancelButton:true,confirmButtonText:'Add',confirmButtonColor:'#1a9e78'}).then(r=>{if(r.isConfirmed&&r.value){const url=r.value.trim();pendingLinks.push({type:'link',url});addAttachPreview('link',url,()=>{pendingLinks=pendingLinks.filter(x=>x.url!==url);schedulePostDraftSave();});schedulePostDraftSave();}});}
function addAttachPreview(type,label,onRemove){
  const icon=type==='youtube'?'fab fa-youtube':type==='link'?'fas fa-link':mimeIconFromName(label);
  const chip=document.createElement('div');chip.className='attach-chip';
  chip.innerHTML=`<i class="${icon}"></i><span class="chip-name" title="${esc(label)}">${esc(label)}</span><button class="chip-rm"><i class="fas fa-times"></i></button>`;
  chip.querySelector('.chip-rm').addEventListener('click',()=>{onRemove();chip.remove();schedulePostDraftSave();});
  document.getElementById('attachPreviews').appendChild(chip);
}

/* ══════════════════════════════════════════
   SUBMIT POST
══════════════════════════════════════════ */
async function submitPost(){
  const type=document.getElementById('pmPostType').value;
  const typeId=document.getElementById('pmPostTypeId').value;
  let title=document.getElementById('pmTitle').value.trim();
  const body=document.getElementById('pmBody').value.trim();
  const t=postTypes.find(x=>x.id==typeId)||{};
  const isExam = isExamTypeObj(t);
  const examMode = (document.getElementById('pmExamMode')?.value || 'questionnaire').toLowerCase();
  const isQuizFlow = !!t.has_quiz && (!isExam || examMode === 'questionnaire');
  const isLesson = (t.type_key || '').toLowerCase() === 'lesson' || (t.type_label || '').toLowerCase().includes('lesson');
  const postId = document.getElementById('pmPostId').value.trim();
  if (isLesson) {
    selectedLessonWeeks = normalizeLessonWeeks(selectedLessonWeeks.length ? selectedLessonWeeks : parseLessonWeeksFromText(title));
    title = formatLessonWeeks(selectedLessonWeeks);
    if (!title) {
      toast('Please select at least one week for this lesson.', 'error');
      openLessonWeekPicker();
      return;
    }
    const conflicts = selectedLessonWeeks.filter(week => getOccupiedLessonWeekSet(postId).has(week));
    if (conflicts.length) {
      toast(formatLessonWeeks(conflicts) + ' already has a lesson.', 'error');
      openLessonWeekPicker();
      return;
    }
    document.getElementById('pmTitle').value = title;
  }
  if(type!=='announcement'&&!title){toast('Please add a title.','error');return;}
  if(type==='announcement'&&!body&&!pendingFiles.length&&!pendingLinks.length){toast('Announcement cannot be empty.','error');return;}
  if(isQuizFlow&&quizQuestions.length===0){toast('Please add at least one question.','error');return;}
  if (isExam && examMode === 'file' && pendingFiles.length === 0) { toast('Please upload at least one exam file.','error'); return; }
  const lessonTopic = document.getElementById('pmTopic')?.value.trim() || '';
  if (isLesson && !lessonTopic) {
    toast('Lesson topic is required.', 'error');
    document.getElementById('pmTopic')?.focus();
    return;
  }
  const lessonPeriod = document.querySelector('input[name="pmLessonPeriod"]:checked')?.value || '';
  if (isLesson && !lessonPeriod) {
    toast('Please select a grading period for this lesson.', 'error');
    return;
  }

  // ── Assignment: require points + due date ──────────────────────────────────
  const _tKey = (t.type_key||'').toLowerCase(), _tLbl = (t.type_label||'').toLowerCase();
  if (_tKey === 'assignment' || _tLbl.includes('assignment')) {
    if (!document.getElementById('pmPoints').value) {
      toast('Assignment requires a points value.', 'error');
      document.getElementById('pmPoints').focus();
      return;
    }
    if (!document.getElementById('pmDueDate').value) {
      toast('Assignment requires a due date.', 'error');
      openDueDateModal();
      return;
    }
  }
  if(isQuizFlow && !_wizDone){ openAssignWizard(); return; }
  _wizDone = false;
  const btn=document.getElementById('pmSubmitBtn');btn.disabled=true;btn.innerHTML='<span class="spin"></span> '+(postId ? 'Saving…' : 'Posting…');
  const fd=new FormData();
  fd.append('post_id', postId);
  fd.append('class_id',CLASS_ID);
  fd.append('post_type',type);
  fd.append('post_type_id',typeId);
  fd.append('sub_label',t.type_label||'');
  fd.append('title', title); // ← only once
  fd.append('body', body);
  fd.append('due_date',document.getElementById('pmDueDate').value);
  fd.append('points',document.getElementById('pmPoints').value);
  const isQuizType = isQuizFlow;
  if (isQuizType) {
    const aiPts = document.getElementById('aiTotalPoints')?.value;
    if (aiPts) document.getElementById('pmPoints').value = aiPts;
  }
  const topicVal = isQuizType
    ? (document.getElementById('pmQuizCoverage')?.value.trim() || '')
    : (document.getElementById('pmTopic')?.value.trim() || '');
  fd.append('topic', topicVal);
  fd.append('lesson_period', isLesson ? lessonPeriod : '');
  fd.append('lesson_weeks', isLesson ? selectedLessonWeeks.join(',') : '');
  fd.append('links_json',JSON.stringify(pendingLinks));
  // ── Quiz config (post-level) ─────────────────────────────────
  if (isQuizType) {
    fd.append('quiz_mode',         document.getElementById('pmQuizMode')?.value || 'live');
    fd.append('open_at',           document.getElementById('pmOpenAt').value);
    fd.append('close_at',          document.getElementById('pmCloseAt').value);
    fd.append('max_attempts',      document.getElementById('pmMaxAttempts').value || '1');
    const _tmode = document.getElementById('pmTimeMode').value;
    fd.append('time_mode',         _tmode);
    if (_tmode === 'per_quiz') {
      const _mins = parseInt(document.getElementById('pmTimePerQuizMinutes').value || '0', 10);
      fd.append('time_limit_seconds', _mins > 0 ? (_mins * 60) : '');
    } else if (_tmode === 'per_question') {
      const _secs = parseInt(document.getElementById('pmTimePerQuestionSecs').value || '0', 10);
      fd.append('time_limit_seconds', _secs > 0 ? _secs : '');
    } else {
      fd.append('time_limit_seconds', '');
    }
    fd.append('passing_threshold', document.getElementById('pmPassingThreshold').value);
  }
  // Per-question seconds (broadcast to every question when per_question mode)
  const _perQSecs = (isQuizType && document.getElementById('pmTimeMode').value === 'per_question')
    ? parseInt(document.getElementById('pmTimePerQuestionSecs').value || '0', 10)
    : 0;
  fd.append('quiz_json', JSON.stringify((isQuizFlow ? quizQuestions : []).map(q => ({
    question: q.question,
    answer: q.answer || '',
    points: q.points,
    cognitive_level: q.cognitive_level || '',
    time_limit_seconds: (parseInt(q.time_limit_seconds || 0, 10) > 0)
      ? parseInt(q.time_limit_seconds || 0, 10)
      : (_perQSecs > 0 ? _perQSecs : null),
    choices: q.choices || []
  }))));
  pendingFiles.forEach(f=>fd.append('files[]',f));
  try {
    const res  = await fetch('API/facultyUI/classroom/save_post.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.status === 'success') {
      clearPostDraft();
      if (submissionMode === 'group' && classGroupsData.length > 0) {
        await fetch('API/facultyUI/classroom/save_post_groups.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            post_id:         data.post_id,
            submission_mode: 'group',
            groups: classGroupsData.map(g => ({
              group_number: g.group_number,
              student_ids:  g.students.map(s => s.student_id)
            }))
          })
        });
      } else {
        await fetch('API/facultyUI/classroom/save_post_groups.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            post_id:         data.post_id,
            submission_mode: 'individual',
            groups:          []
          })
        });
      }
      closePostModal();
      toast(data.message);
      await loadClassroom();
      renderStream();
      renderClasswork();
    } else toast(data.message||'Failed','error');
  } catch(e) { toast('Network error','error'); console.error(e); }
  finally { btn.disabled=false; btn.innerHTML='<i class="fas fa-paper-plane"></i> <span id="gcSubmitLabel">Post</span>'; }
}

/* ══════════════════════════════════════════
   CUSTOM TYPE MODAL
══════════════════════════════════════════ */
async function deleteCustomType(t){
  const r=await Swal.fire({title:`Delete "${t.type_label}"?`,text:'This only removes the type label.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Yes, delete',cancelButtonText:'Cancel'});
  if(!r.isConfirmed)return;
  try{const res=await fetch('API/facultyUI/classroom/save_post_type.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'delete',id:t.id})});const data=await res.json();if(data.status==='success'){toast(data.message);await loadPostTypes();}else toast(data.message,'error');}catch{toast('Network error','error');}
}
function buildIconGrid(){
  const g=document.getElementById('iconGrid');g.innerHTML='';
  ICON_OPTS.forEach(ic=>{const d=document.createElement('div');d.className='icon-opt'+(ic===selectedIcon?' selected':'');d.innerHTML=`<i class="fas ${ic}"></i>`;d.addEventListener('click',()=>{selectedIcon=ic;document.querySelectorAll('.icon-opt').forEach(x=>x.classList.remove('selected'));d.classList.add('selected');});g.appendChild(d);});
}
function buildColorPicker(){
  const p=document.getElementById('colorPicker');p.innerHTML='';
  COLOR_OPTS.forEach(c=>{const d=document.createElement('div');d.style.cssText=`width:28px;height:28px;border-radius:50%;background:${c.bg};border:3px solid ${c===selectedColor?c.text:'transparent'};cursor:pointer;transition:all .15s;`;d.title=c.label;d.addEventListener('click',()=>{selectedColor=c;buildColorPicker();});p.appendChild(d);});
}
function openCtModal(){
  document.getElementById('ctLabel').value='';
  document.getElementById('ctIsGrad').checked=false;
  document.getElementById('ctHasQuiz').checked=false;
  document.getElementById('ctHasFile').checked=false;
  selectedIcon=ICON_OPTS[0];
  selectedColor=COLOR_OPTS[0];
  buildIconGrid();
  buildColorPicker();
  document.getElementById('ctModalBack').classList.add('show');
  document.getElementById('ctLabel').focus();
}
function closeCtModal(){document.getElementById('ctModalBack').classList.remove('show');}
document.getElementById('ctModalBack').addEventListener('click',e=>{if(e.target===document.getElementById('ctModalBack'))closeCtModal();});
document.getElementById('ctHasQuiz').addEventListener('change',function(){
  if(this.checked) document.getElementById('ctIsGrad').checked=true;
});
async function saveCustomType(){
  const label=document.getElementById('ctLabel').value.trim();if(!label){toast('Label is required.','error');return;}
  const payload={action:'create',type_label:label,icon:selectedIcon,color_bg:selectedColor.bg,color_text:selectedColor.text,is_gradable:document.getElementById('ctIsGrad').checked?1:0,has_quiz:document.getElementById('ctHasQuiz').checked?1:0,has_file:document.getElementById('ctHasFile').checked?1:0};
  try{
    const res=await fetch('API/facultyUI/classroom/save_post_type.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const data=await res.json();
    if(data.status==='success'){
      closeCtModal();toast(data.message);
      document.getElementById('ctLabel').value='';
      document.getElementById('ctIsGrad').checked=false;
      document.getElementById('ctHasQuiz').checked=false;
      document.getElementById('ctHasFile').checked=false;
      await loadPostTypes();
      /* Rebuild type nav if modal is open */
      if(document.getElementById('gcModalPanel').style.display!=='none'){
        const newType=postTypes.find(x=>String(x.id)===String(data.id));
        buildGcTypeNav(newType||postTypes.find(x=>x.id==activePmTypeId)||null);
        if(newType) selectGcType(newType);
      }
    }else toast(data.message,'error');
  }catch{toast('Network error','error');}
}

/* ══════════════════════════════════════════
   EDIT CLASS MODAL
══════════════════════════════════════════ */
let _selectedPalette=null;
function buildEcPalette(){
  const wrap=document.getElementById('ecPalettePicker');wrap.innerHTML='';
  const current=paletteKeyFromStored(_selectedPalette)||paletteKeyFromStored(classData?.banner_palette)||hashPaletteKey(classPaletteSeed(classData,document.getElementById('bannerTitle').textContent));
  PALETTE_KEYS.forEach(key=>{
    const p=PALETTE_GRADIENTS[key];
    const d=document.createElement('div');
    d.style.cssText=`width:36px;height:36px;border-radius:10px;background:${p};cursor:pointer;border:3px solid ${key===current?'#fff':'transparent'};box-shadow:${key===current?'0 0 0 2px #1a9e78':'none'};transition:all .15s;flex-shrink:0;`;
    d.addEventListener('click',()=>{_selectedPalette=key;buildEcPalette();});
    wrap.appendChild(d);
  });
}
function openEditClassModal(){
  if(!classData)return;
  document.getElementById('ecSubjectCode').value=classData.subject_code||'';document.getElementById('ecSubjectName').value=classData.subject_name||'';
  document.getElementById('ecYearLevel').value=classData.year_level||'';document.getElementById('ecSection').value=classData.section||'';
  document.getElementById('ecSemester').value=classData.class_semester||'';document.getElementById('ecCourseCode').value=classData.course_code||'';
  document.getElementById('ecDays').value=classData.class_days||'';document.getElementById('ecSchedule').value=classData.schedule||'';
  _selectedPalette=paletteKeyFromStored(classData.banner_palette)||null;buildEcPalette();
  document.getElementById('editClassModalBack').classList.add('show');
  setTimeout(()=>document.getElementById('ecSubjectCode').focus(),120);
}
function closeEditClassModal(){document.getElementById('editClassModalBack').classList.remove('show');}
document.getElementById('editClassModalBack').addEventListener('click',e=>{if(e.target===document.getElementById('editClassModalBack'))closeEditClassModal();});
async function saveEditClass(){
  const btn=document.getElementById('ecSaveBtn');btn.disabled=true;btn.innerHTML='<span class="spin"></span> Saving…';
  try{
    const payload={class_id:CLASS_ID,subject_code:document.getElementById('ecSubjectCode').value.trim(),subject_name:document.getElementById('ecSubjectName').value.trim(),year_level:document.getElementById('ecYearLevel').value.trim(),section:document.getElementById('ecSection').value.trim(),class_semester:document.getElementById('ecSemester').value.trim(),course_code:document.getElementById('ecCourseCode').value.trim(),class_days:document.getElementById('ecDays').value.trim(),schedule:document.getElementById('ecSchedule').value.trim(),banner_palette:_selectedPalette||''};
    const res=await fetch('API/facultyUI/classroom/update_class.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const data=await res.json();
    if(data.status==='success'){Object.assign(classData,data.updated||payload);if(_selectedPalette)classData.banner_palette=_selectedPalette;renderBanner();renderClassInfo();closeEditClassModal();toast('Class updated successfully!');}
    else toast(data.message||'Failed to save changes.','error');
  }catch{toast('Network error','error');}
  finally{btn.disabled=false;btn.innerHTML='<i class="fas fa-check"></i> Save Changes';}
}
async function confirmDeleteClass(){
  const r=await Swal.fire({title:'Delete this class?',html:'<p style="color:#5f6368;font-size:.9rem;">All posts, attachments, and enrollments will be <strong>permanently deleted</strong>.</p>',icon:'warning',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Yes, delete class',cancelButtonText:'Cancel',reverseButtons:true});
  if(!r.isConfirmed)return;
  const r2=await Swal.fire({title:'Are you absolutely sure?',input:'text',inputPlaceholder:'Type DELETE to confirm',inputAttributes:{autocomplete:'off'},showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Delete permanently',preConfirm:(val)=>{if(val!=='DELETE'){Swal.showValidationMessage('Please type DELETE to confirm');return false;}return true;}});
  if(!r2.isConfirmed)return;
  try{const res=await fetch('API/facultyUI/classroom/delete_class.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({class_id:CLASS_ID})});const data=await res.json();if(data.status==='success'){toast('Class deleted.');setTimeout(()=>{window.location.href='facultyUI.php';},1500);}else toast(data.message||'Failed to delete class.','error');}catch{toast('Network error','error');}
}

async function startLiveQuiz(postId, btnEl) {
  if (!postId) return;
  const ok = await Swal.fire({
    icon: 'question',
    title: 'Start the live quiz?',
    html: '<div style="font-size:.87rem;color:#5f6368;">Joined students will start together once you start this live quiz.<br>Students in the lobby will be notified.</div>',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-play"></i> Yes, Start',
    confirmButtonColor: '#1a9e78',
  });
  if (!ok.isConfirmed) return;
  if (btnEl) { btnEl.disabled = true; btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting…'; }
  try {
    const fd = new FormData();
    fd.append('post_id', postId);
    fd.append('action', 'start');
    const res  = await fetch('API/facultyUI/classroom/quiz/start_live_quiz.php', { method:'POST', body:fd, credentials:'same-origin' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Start failed');

    // Swap button to "End Quiz"
    if (btnEl) {
      btnEl.className   = 'pc-start-quiz-btn pc-start-quiz-btn--live';
      btnEl.innerHTML   = '<i class="fa-solid fa-stop"></i> End Quiz';
      btnEl.disabled    = false;
      btnEl.title       = 'End the live quiz now';
      btnEl.onclick     = () => endLiveQuiz(postId, btnEl);
    }
    Swal.fire({ icon:'success', title:'Quiz is Live!', html:'<div style="font-size:.87rem;">Students who joined the lobby can now <strong>Take Quiz</strong>.</div>', timer:2000, showConfirmButton:false });
    if (typeof allPosts !== 'undefined') {
      const p = allPosts.find(x => x.id === postId);
      if (p) p.live_started_at = new Date().toISOString();
    }
  } catch (e) {
    if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = '<i class="fa-solid fa-play"></i> Start Quiz'; }
    Swal.fire({ icon:'error', title:'Could not start', text: String(e.message || e) });
  }
}

async function endLiveQuiz(postId, btnEl) {
  const ok = await Swal.fire({
    icon: 'warning', title: 'End quiz now?',
    text: 'Students in progress will be cut off.',
    showCancelButton: true, confirmButtonText: 'Yes, End', confirmButtonColor: '#d93025',
  });
  if (!ok.isConfirmed) return;
  if (btnEl) { btnEl.disabled = true; btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ending…'; }
  try {
    const fd = new FormData();
    fd.append('post_id', postId);
    fd.append('action', 'end');
    const res  = await fetch('API/facultyUI/classroom/quiz/start_live_quiz.php', { method:'POST', body:fd, credentials:'same-origin' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'End failed');
    if (btnEl) { btnEl.remove(); }
    Swal.fire({ icon:'success', title:'Quiz ended.', timer:1500, showConfirmButton:false });
    if (typeof allPosts !== 'undefined') {
      const p = allPosts.find(x => x.id === postId);
      if (p) { p.live_ended_at = new Date().toISOString(); p.is_force_closed = 1; }
    }
  } catch (e) {
    if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = '<i class="fa-solid fa-stop"></i> End Quiz'; }
    Swal.fire({ icon:'error', title:'Could not end', text: String(e.message || e) });
  }
}

/* ══════════════════════════════════════════
   STREAM + CLASSWORK RENDER
══════════════════════════════════════════ */
function renderStream(){
  const feed=document.getElementById('streamFeed');
  if(!allPosts.length){feed.innerHTML=`<div class="empty-feed"><div class="ef-icon"><i class="fas fa-stream"></i></div><div class="ef-title">No posts yet</div><div class="ef-sub">Post an announcement, lesson, or activity to get started.</div></div>`;return;}

  let posts = [...allPosts];
  if (streamTypeFilter !== 'all') {
    posts = posts.filter(p => String(p.post_type_id) === streamTypeFilter);
  }
  if (isLessonStreamTypeSelected() && streamLessonPeriodFilter !== 'all') {
    posts = posts.filter(p => String(p.lesson_period || '').toLowerCase() === streamLessonPeriodFilter);
  }
  if (streamSearchQuery) {
    posts = posts.filter(p => {
      const titleOnly = String(p.title || '').toLowerCase();
      return titleOnly.includes(streamSearchQuery);
    });
  }

  posts.sort((a,b) => {
    const ad = parseLocalDate(a.created_at)?.getTime() || 0;
    const bd = parseLocalDate(b.created_at)?.getTime() || 0;
    return bd - ad; // newest first
  });

  if(!posts.length){
    feed.innerHTML=`<div class="empty-feed"><div class="ef-icon"><i class="fas fa-filter"></i></div><div class="ef-title">No matching posts</div><div class="ef-sub">Try changing the filter, sort, or search.</div></div>`;
    return;
  }

  feed.innerHTML=posts.map(buildPostCard).join('');
}
function renderClasswork(){
  const cwPosts=allPosts.filter(p=>p.post_type!=='announcement');
  const feed=document.getElementById('classworkFeed');
  if(!cwPosts.length){feed.innerHTML=`<div class="empty-feed"><div class="ef-icon"><i class="fas fa-tasks"></i></div><div class="ef-title">No classwork yet</div><div class="ef-sub">Add lessons, activities, quizzes, or assignments.</div></div>`;return;}
  const isLessonPost=p=>{
    const t=postTypes.find(x=>String(x.id)===String(p.post_type_id))||{};
    return String(t.type_key||p.post_type||'').toLowerCase()==='lesson'
      || String(t.type_label||p.sub_label||'').toLowerCase().includes('lesson');
  };
  const renderTopicGroups=posts=>{
    const grouped={};
    posts.forEach(p=>{const topic=p.topic||'General';if(!grouped[topic])grouped[topic]=[];grouped[topic].push(p);});
    return Object.entries(grouped).map(([topic,items])=>`<div class="cw-topic-group"><div class="cw-topic-label">${esc(topic)}</div>${items.map(buildCwItem).join('')}</div>`).join('');
  };
  const lessons=cwPosts.filter(isLessonPost);
  const otherPosts=cwPosts.filter(p=>!isLessonPost(p));
  const periods=[
    ['prelim','Prelim'],
    ['midterm','Midterm'],
    ['finals','Finals']
  ];
  const periodBoxes=periods.map(([key,label])=>{
    const items=lessons.filter(p=>String(p.lesson_period||'').toLowerCase()===key);
    return `<div class="cw-period-box">
      <div class="cw-period-head"><span>${label}</span><span class="cw-period-count">${items.length}</span></div>
      <div class="cw-period-body">${items.length?renderTopicGroups(items):'<div class="cw-period-empty">No lessons yet.</div>'}</div>
    </div>`;
  }).join('');
  const unassigned=lessons.filter(p=>!['prelim','midterm','finals'].includes(String(p.lesson_period||'').toLowerCase()));
  const legacyLessons=unassigned.length?`<div class="cw-topic-group"><div class="cw-topic-label">Unassigned Lessons</div>${unassigned.map(buildCwItem).join('')}</div>`:'';
  const remaining=otherPosts.length?renderTopicGroups(otherPosts):'';
  feed.innerHTML=`<div class="cw-period-grid">${periodBoxes}</div>${legacyLessons}${remaining}`;
}
function buildCwItem(p){
  const t=postTypes.find(x=>x.id==p.post_type_id)||{icon:'fa-file-alt',color_bg:'#f3f4f6',color_text:'#374151'};
  const dateStr=p.due_date?`Due ${formatDate(p.due_date)}`:'';
  return `<div class="cw-item" onclick="focusPost('${esc(p.id)}')"><div class="cw-icon" style="background:${t.color_bg};color:${t.color_text};"><i class="fas ${t.icon}"></i></div><div class="cw-title">${esc(p.title||p.body?.substring(0,60)||'Untitled')}</div>${p.points!=null?`<span style="font-size:.75rem;font-weight:600;color:var(--primary);">${p.points} pts</span>`:''}<div class="cw-date">${esc(dateStr)}</div></div>`;
}
function focusPost(id){
  document.querySelector('[data-tab="stream"]').click();
  setTimeout(()=>{const el=document.querySelector(`[data-post-id="${id}"]`);if(el){el.scrollIntoView({behavior:'smooth',block:'center'});el.style.outline='2px solid var(--primary)';setTimeout(()=>el.style.outline='',1800);}},150);
}

function buildAttachChip(a){
  if(a.attach_type==='youtube')return`<a href="${esc(a.url)}" target="_blank" class="pa-chip pa-yt"><i class="fab fa-youtube"></i><span>${esc((a.url||'').replace('https://','').substring(0,40))}</span></a>`;
  if(a.attach_type==='link')return`<a href="${esc(a.url)}" target="_blank" class="pa-chip"><i class="fas fa-link"></i><span>${esc((a.url||'').replace('https://','').substring(0,40))}</span></a>`;
  const ext=(a.file_name||'').split('.').pop().toLowerCase();
  const NO_PREVIEW=new Set(['zip','rar','7z','tar','gz','exe','apk','dmg','iso']);
  const icon=`fas ${mimeIcon(a.mime_type||'')}`;
  const filePath=a.file_path||'';const fileName=a.file_name||'File';const fileMime=a.mime_type||'';
  if(NO_PREVIEW.has(ext))return`<a href="${esc(filePath)}" target="_blank" download class="pa-chip"><i class="${icon}"></i><span>${esc(fileName)}</span></a>`;
  return`<button type="button" class="pa-chip fv-trigger" data-url="${esc(filePath)}" data-name="${esc(fileName)}" data-mime="${esc(fileMime)}" data-attach-id="${esc(a.id||'')}"><i class="${icon}"></i><span>${esc(fileName)}</span><i class="fas fa-eye" style="font-size:.6rem;color:var(--primary);margin-left:.15rem;opacity:.7;"></i></button>`;
}
document.addEventListener('click',function(e){
  const btn=e.target.closest('.fv-trigger');
  if(btn)openFileViewer(btn.dataset.url,btn.dataset.name,btn.dataset.mime,btn.dataset.attachId||null);
});

function buildPostCard(p){
  const t=postTypes.find(x=>x.id==p.post_type_id)||null;
  const label=p.sub_label||(t?t.type_label:p.post_type)||'Post';
  const iconCls=t?t.icon:'fa-file-alt';const bg=t?t.color_bg:'#f3f4f6';const co=t?t.color_text:'#374151';
  const initials=(p.author_name||'F').split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
  let metaExtra='';
  if(p.due_date)metaExtra+=`<span><i class="fas fa-calendar-check"></i> Due ${formatDate(p.due_date)}</span>`;
  if(p.points!=null)metaExtra+=`<span><i class="fas fa-star"></i> ${p.points} pts</span>`;

  const groupsHtml = (p.groups && Object.keys(p.groups).length)
    ? buildGroupChips(p.groups, p.submission_mode || 'individual')
    : '';

  const attachHtml=(p.attachments&&p.attachments.length)?`<div class="pc-attachments">${p.attachments.map(a=>buildAttachChip(a)).join('')}</div>`:'';
  const isQuizPost = ((String(p.post_type||'').toLowerCase()==='quiz') || (String(label||'').toLowerCase()==='quiz')) && Array.isArray(p.questions) && p.questions.length>0;
  const headLeftLabel = `<i class="fas ${iconCls}"></i>`;
  const postTitle = esc(p.title || label || 'Untitled post');
  const authorName = esc(p.author_name || 'Faculty');
  const headMainLabel = postTitle;
  const headDateLabel = `${formatDate(p.created_at)} • ${authorName}`;
let quizHtml = '';

if (p.questions && p.questions.length) {
  const totalPoints = p.questions.reduce((s, q) => s + (parseFloat(q.points) || 0), 0);
  const modeRaw = String(p.quiz_mode || '').toLowerCase();
  const isLiveMode = ['live','live_mode'].includes(modeRaw);
  const isDueDateMode = ['due_date','due-date','scheduled'].includes(modeRaw) || !isLiveMode;
  const modeLabel = isLiveMode ? 'Live Mode' : 'Due Date Mode';
  const timeMode = String(p.time_mode || 'none').toLowerCase();
  let timerLabel = 'No Limit';
  if (timeMode === 'per_quiz' && Number(p.time_limit_seconds || 0) > 0) {
    timerLabel = `${Math.round(Number(p.time_limit_seconds) / 60)} min (Whole Quiz)`;
  } else if (timeMode === 'per_question') {
    const perQ = Array.isArray(p.questions) ? p.questions.find(q => Number(q.time_limit_seconds || 0) > 0) : null;
    timerLabel = perQ ? `${Math.round(Number(perQ.time_limit_seconds))} sec / question` : 'Per Question';
  }
  const qCountLabel = `${p.questions.length} question${p.questions.length !== 1 ? 's' : ''} created`;

  quizHtml = `
    <div class="quiz-preview">
      <div class="quiz-compact-summary">
        <div class="quiz-compact-left">
          <div class="quiz-compact-main">
            <div class="quiz-compact-info">
              <span class="pc-type-badge" style="background:var(--primary-light);color:var(--primary);"><i class="fas fa-star"></i> ${Number(totalPoints || 0)} pts</span>
              <span class="pc-type-badge" style="background:#fdecea;color:#d93025;"><i class="fas fa-stopwatch"></i> ${esc(modeLabel)}</span>
              <span class="pc-type-badge" style="background:#eef2ff;color:#334155;"><i class="fas fa-clock"></i> ${esc(timerLabel)}</span>
            </div>
            <div class="quiz-compact-sub">${esc(qCountLabel)}</div>
          </div>
        </div>
      </div>
    </div>
  `;
}

  const count=parseInt(p.comment_count,10)||0;
  const cLabel=count>0?`${count} comment${count!==1?'s':''}`:'Class comments';
  const commentSection=`<div class="pc-comments" id="cmtwrap_${esc(p.id)}"><button class="pc-comments-toggle" id="cmttog_${esc(p.id)}" onclick="toggleComments('${esc(p.id)}',this)"><i class="fas fa-comment-alt" style="font-size:.74rem;"></i><span class="cmt-count-lbl">${esc(String(cLabel))}</span><i class="fas fa-chevron-down cmt-arrow"></i></button><div class="pc-comments-body" id="cmtbody_${esc(p.id)}"><div class="comment-list" id="cmtlist_${esc(p.id)}"><div class="cmt-empty"><i class="fas fa-spinner fa-spin"></i> Loading…</div></div><div class="cmt-input-row"><textarea class="cmt-input" id="cmtin_${esc(p.id)}" placeholder="Add a class comment… (Enter to send)" rows="1" onkeydown="cmtKey(event,'${esc(p.id)}')" oninput="cmtResize(this)"></textarea><button class="cmt-send" id="cmtsend_${esc(p.id)}" onclick="sendComment('${esc(p.id)}')" title="Post comment"><i class="fas fa-paper-plane"></i></button></div></div></div>`;
  const bodyTitle = '';
  const bodyText = p.body ? `<div class="pc-text">${esc(p.body)}</div>` : '';
  const bodyMeta = metaExtra ? `<div class="pc-meta-row">${metaExtra}</div>` : '';
return`<div class="post-card ${isQuizPost?'quiz-post':''}" data-post-id="${esc(p.id)}" onclick="openPostManageFromEvent('${esc(p.id)}',event)"><div class="pc-head"><div class="pc-avatar" style="background:${bg};color:${co};">${headLeftLabel}</div><div class="pc-meta"><div class="pc-author">${headMainLabel}</div><div class="pc-date">${headDateLabel}</div></div><span class="pc-type-badge" style="background:${bg};color:${co};"><i class="fas ${iconCls}"></i> ${esc(label)}</span><div class="pc-actions"><button class="pc-act-btn" onclick="event.stopPropagation();editPost('${esc(p.id)}')" title="Edit"><i class="fas fa-pen"></i></button><button class="pc-act-btn del-btn" onclick="event.stopPropagation();deletePost('${esc(p.id)}')" title="Delete"><i class="fas fa-trash"></i></button></div></div><div class="pc-body">${bodyTitle}${bodyText}${bodyMeta}</div>${attachHtml}${quizHtml}${groupsHtml}${commentSection}</div>`;}

function buildStreamFilterOptions(){
  const sel = document.getElementById('streamTypeFilter');
  if (!sel) return;
  const prev = streamTypeFilter || sel.value || 'all';
  const options = ['<option value="all">All post types</option>'];
  const counts = new Map();
  (allPosts || []).forEach(p => {
    const id = String(p.post_type_id || '');
    if (!id) return;
    counts.set(id, (counts.get(id) || 0) + 1);
  });
  postTypes.forEach(t => {
    const id = String(t.id || '');
    const cnt = counts.get(id) || 0;
    const key = String(t.type_key || '').toLowerCase();
    if (!id || cnt <= 0 || key === 'announcement') return;
    options.push(`<option value="${esc(id)}">${esc(t.type_label || 'Post')}</option>`);
  });
  sel.innerHTML = options.join('');
  const hasPrev = Array.from(sel.options).some(o => o.value === prev);
  sel.value = hasPrev ? prev : 'all';
  streamTypeFilter = sel.value;
  syncLessonPeriodFilter();
}

function onStreamFilterChange(){
  const typeEl = document.getElementById('streamTypeFilter');
  const qEl = document.getElementById('streamSearch');
  streamTypeFilter = typeEl ? String(typeEl.value || 'all') : 'all';
  streamSearchQuery = qEl ? String(qEl.value || '').trim().toLowerCase() : '';
  syncLessonPeriodFilter();
  renderStream();
}

function isLessonStreamTypeSelected(){
  const type = postTypes.find(t => String(t.id) === String(streamTypeFilter));
  if (!type) return false;
  return String(type.type_key || '').toLowerCase() === 'lesson'
    || String(type.type_label || '').toLowerCase().includes('lesson');
}

function syncLessonPeriodFilter(){
  const wrap = document.getElementById('lessonPeriodFilter');
  if (!wrap) return;
  const visible = isLessonStreamTypeSelected();
  wrap.classList.toggle('show', visible);
  if (!visible) {
    streamLessonPeriodFilter = 'all';
    const allInput = wrap.querySelector('input[value="all"]');
    if (allInput) allInput.checked = true;
  }
}

function onLessonPeriodFilterChange(){
  streamLessonPeriodFilter = document.querySelector('input[name="streamLessonPeriod"]:checked')?.value || 'all';
  renderStream();
}


// ══════════════════════════════════════════════════
// PHASE 5 — MANAGE QUIZ MODAL: open/close + tab switching
// ══════════════════════════════════════════════════
let mqCurrentPostId = null;
let mqCurrentPostData = null;
let mqLivePollTimer = null;

function openManageQuiz(postId) {
  if (!postId) return;
  window.location.href = `post_manage.php?post_id=${encodeURIComponent(postId)}&class_id=${encodeURIComponent(CLASS_ID)}`;
}

function openPostManage(postId) {
  if (!postId) return;
  window.location.href = `post_manage.php?post_id=${encodeURIComponent(postId)}&class_id=${encodeURIComponent(CLASS_ID)}`;
}

function openPostManageFromEvent(postId, evt) {
  if (!postId) return;
  const t = evt && evt.target ? evt.target : null;
  if (t && t.closest('button,a,input,textarea,select,label,.pc-comments-body,.pc-comments-toggle,.quiz-card-actions,.cmt-input-row,[contenteditable="true"]')) {
    return;
  }
  openPostManage(postId);
}

function closeManageQuiz() {
  const modal = document.getElementById('mqModal');
  const overlay = document.getElementById('mqOverlay');
  if (modal) modal.classList.remove('show');
  if (overlay) overlay.classList.remove('show');
  document.body.style.overflow = '';
  // Stop live polling if active
  if (mqLivePollTimer) { clearInterval(mqLivePollTimer); mqLivePollTimer = null; }
  const liveToggle = document.getElementById('mqLiveToggle');
  const liveLabel  = document.getElementById('mqLiveLabel');
  if (liveToggle) liveToggle.checked = false;
  if (liveLabel)  liveLabel.classList.remove('active');
  // Also close the detail sub-modal if it's open
  const detailOverlay = document.getElementById('mqDetailOverlay');
  const detailModal   = document.getElementById('mqDetailModal');
  if (detailOverlay) detailOverlay.classList.remove('show');
  if (detailModal)   detailModal.classList.remove('show');
  mqCurrentPostId = null;
  mqCurrentPostData = null;
}

function switchMqTab(tab) {
  document.querySelectorAll('.mq-tab').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
  document.querySelectorAll('.mq-pane').forEach(p => p.classList.toggle('active', p.id === 'mqPane-' + tab));
  // Stop live polling when switching away from Submissions
  if (tab !== 'submissions' && mqLivePollTimer) {
    clearInterval(mqLivePollTimer); mqLivePollTimer = null;
    const lt = document.getElementById('mqLiveToggle'); if (lt) lt.checked = false;
    const ll = document.getElementById('mqLiveLabel');  if (ll) ll.classList.remove('active');
  }
  // Lazy loaders — defined in Steps 12, 13, 14
  if (tab === 'settings'    && typeof loadQuizSettings  === 'function') loadQuizSettings();
  if (tab === 'submissions' && typeof loadSubmissions   === 'function') loadSubmissions(false);
  if (tab === 'analytics'   && typeof loadQuizAnalytics === 'function') loadQuizAnalytics();
}

function setMqStatusPill(post) {
  const pill = document.getElementById('mqStatusPill');
  const txt  = document.getElementById('mqStatusText');
  if (!pill || !txt) return;
  pill.className = 'mq-status-pill';
  if (!post) { txt.textContent = '—'; return; }
  if (Number(post.is_published) !== 1)     { pill.classList.add('is-draft');    txt.textContent = 'Draft'; return; }
  if (post.results_released_at)            { pill.classList.add('is-released'); txt.textContent = 'Results Released'; return; }
  if (Number(post.is_force_closed) === 1)  { pill.classList.add('is-closed');   txt.textContent = 'Force Closed'; return; }
  if (Number(post.is_force_open) === 1)    { pill.classList.add('is-open');     txt.textContent = 'Force Open'; return; }
  // Schedule-based
  const now = Date.now();
  const parseDt = (s) => s ? new Date(String(s).replace(' ', 'T')).getTime() : null;
  const openAt  = parseDt(post.open_at);
  const closeAt = parseDt(post.close_at);
  if (closeAt && now > closeAt) { pill.classList.add('is-closed'); txt.textContent = 'Closed';    return; }
  if (openAt  && now < openAt)  { pill.classList.add('is-draft');  txt.textContent = 'Scheduled'; return; }
  pill.classList.add('is-open'); txt.textContent = 'Open';
}

// Legacy publish function removed. Single source of truth is window.openPublishDialog.

function getQuizQuestionText(q) {
  return q.question || q.question_text || q.title || '';
}
function getQuizAnswerText(q) {
  const choices = Array.isArray(q.choices) ? q.choices : [];

  const correctChoices = choices
    .filter(c => Number(c.is_correct) === 1)
    .map(c => c.choice_text || c.text || '')
    .filter(Boolean);

  if (correctChoices.length) {
    return correctChoices.join(', ');
  }

  return q.answer_key || q.answer || 'No answer key saved';
}

function openQuizQuestionsModal(postId) {
  const post = allPosts.find(p => String(p.id) === String(postId));

  if (!post || !Array.isArray(post.questions) || post.questions.length === 0) {
    toast('No questions found.', 'error');
    return;
  }

  const html = `
    <div class="quiz-modal-list">
      ${post.questions.map((q, qi) => {
        const choices = Array.isArray(q.choices) ? q.choices : [];
        const questionText = getQuizQuestionText(q);

        const choicesHtml = choices.length
          ? choices.map(c => `
              <div class="quiz-modal-choice">
                <span class="quiz-modal-dot"></span>
                <span>${esc(c.choice_text || c.text || '')}</span>
              </div>
            `).join('')
          : `
              <div class="quiz-modal-choice">
                <span class="quiz-modal-dot"></span>
                <span>Identification question</span>
              </div>
            `;

        return `
          <div class="quiz-modal-item">
            <div class="quiz-modal-question">${qi + 1}. ${esc(questionText)}</div>
            ${q.cognitive_level ? `<div style="margin:.3rem 0 .5rem;"><span style="display:inline-flex;align-items:center;gap:.35rem;font-size:.68rem;font-weight:700;padding:.18rem .55rem;border-radius:16px;background:#e8f0fe;color:#1f73db;border:1px solid rgba(31,115,219,.25);"><i class="fas fa-brain"></i> ${esc(fmtCognitiveLevel(q.cognitive_level))}</span></div>` : ''}
            ${choicesHtml}
          </div>
        `;
      }).join('')}
    </div>
  `;

  Swal.fire({
    title: post.title || 'Quiz Questions',
    html,
    width: 760,
    confirmButtonText: 'Close',
    confirmButtonColor: '#1a9e78',
    customClass: {
      popup: 'tl-quiz-popup',
      title: 'tl-quiz-title',
      htmlContainer: 'tl-quiz-html'
    }
  });
}

function openQuizAnswerKeyModal(postId) {
  const post = allPosts.find(p => String(p.id) === String(postId));

  if (!post || !Array.isArray(post.questions) || post.questions.length === 0) {
    toast('No questions found.', 'error');
    return;
  }

  const html = `
    <div class="quiz-modal-list">
      ${post.questions.map((q, qi) => {
        const questionText = getQuizQuestionText(q);
        const answer = getQuizAnswerText(q);

        return `
          <div class="quiz-modal-item">
            <div class="quiz-modal-question">${qi + 1}. ${esc(questionText)}</div>
            ${q.cognitive_level ? `<div style="margin:.3rem 0 .5rem;"><span style="display:inline-flex;align-items:center;gap:.35rem;font-size:.68rem;font-weight:700;padding:.18rem .55rem;border-radius:16px;background:#e8f0fe;color:#1f73db;border:1px solid rgba(31,115,219,.25);"><i class="fas fa-brain"></i> ${esc(fmtCognitiveLevel(q.cognitive_level))}</span></div>` : ''}
            <div class="quiz-modal-answer">
              <i class="fas fa-key"></i>
              <span>${esc(answer)}</span>
            </div>
          </div>
        `;
      }).join('')}
    </div>
  `;

  Swal.fire({
    title: 'Answer Key',
    html,
    width: 760,
    confirmButtonText: 'Close',
    confirmButtonColor: '#1a9e78',
    customClass: {
      popup: 'tl-quiz-popup',
      title: 'tl-quiz-title',
      htmlContainer: 'tl-quiz-html'
    }
  });
}
/* ══════════════════════════════════════════
   PEOPLE
══════════════════════════════════════════ */

function renderPeople() {
  const enrolled  = allPeople;
  const invited   = (window.allInvitations && window.allInvitations.length)
                      ? window.allInvitations
                      : allPending.filter(s => s.source === 'invitation');
  const joinReqs  = allPending.filter(s => s.source === 'join_request' || s.source === 'enrollment');
 
  /* ── update topbar counts ── */
  document.getElementById('peopleCount').textContent   = enrolled.length;
  document.getElementById('enrolledCount').textContent = enrolled.length;
 
  /* ── avatar colour pool (cycles) ── */
  const AV_PALETTES = [
    { bg:'#e1f5ee', color:'#085041' },
    { bg:'#e6f1fb', color:'#0c447c' },
    { bg:'#eeedfe', color:'#3c3489' },
    { bg:'#faeeda', color:'#633806' },
    { bg:'#faece7', color:'#712b13' },
    { bg:'#fbeaf0', color:'#72243e' },
    { bg:'#eaf3de', color:'#27500a' },
  ];
  function avStyle(idx) {
    const p = AV_PALETTES[idx % AV_PALETTES.length];
    return `background:${p.bg};color:${p.color};`;
  }
  function initials(name) {
    const p = (name||'').trim().split(/\s+/);
    return ((p[0]?.[0]??'')+(p[p.length-1]?.[0]??'')).toUpperCase() || '??';
  }
 
  /* ── tag helpers ── */
  const iconId = `<svg width="11" height="11" viewBox="0 0 16 16" fill="none"><rect x="2" y="4" width="12" height="8" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="M5 8h6M5 10.5h3" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/></svg>`;
  const iconCourse = `<svg width="11" height="11" viewBox="0 0 16 16" fill="none"><path d="M8 2L14 5.5v3L8 12 2 8.5v-3L8 2z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>`;
  const iconCheck = `<svg width="11" height="11" viewBox="0 0 16 16" fill="none"><path d="M2 8l5 5 7-7" stroke="#378add" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
  const iconTrash = `<svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M3 4h10M6 4V3h4v1M7 7v4M9 7v4M4 4l1 9h6l1-9" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
  const iconTick  = `<svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M3 8.5l3.5 3.5L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
  const iconX     = `<svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M4.5 4.5l7 7M11.5 4.5l-7 7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>`;
 
  function tags(s) {
    let t = '';
    if (s.course_code) t += `<span class="ptag ptag-course">${iconCourse}${esc(s.course_code)}</span>`;
    if (s.student_number) t += `<span class="ptag ptag-id">${iconId}${esc(s.student_number)}</span>`;
    return t ? `<div class="ptags">${t}</div>` : '';
  }
 
  /* ── card builder ── */
  function enrolledCard(s, idx) {
    const ini = initials(s.full_name);
    const name = formatLastFirst(s.full_name);
    const pic = s.profile_picture || s.student_profile_picture || s.avatar_url || '';
    return `
    <div class="pcard" id="prow_${esc(String(s.id))}" onclick="openPeopleProfile('${esc(String(s.id))}','enrolled')">
      <div class="pav" style="${avStyle(idx)}">
        ${pic
          ? `<img src="${esc(pic)}" alt="${esc(name)}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
          : ini}
        <div class="pdot pdot-green"></div>
      </div>
      <div class="pinfo">
        <div class="pname">${esc(name)}</div>
        <div class="pemail">${esc(s.email||'')}</div>
        ${tags(s)}
      </div>
      <div class="pright">
        <span class="pbadge pbadge-enrolled">Enrolled</span>
        <button class="pact pact-remove"
          onclick="event.stopPropagation();confirmRemoveStudent('${esc(String(s.id))}','${esc(name)}')"
          title="Remove student">
          ${iconTrash} Remove
        </button>
      </div>
    </div>`;
  }
 
  function invitedCard(s, idx) {
    const ini  = initials(s.full_name);
    const name = formatLastFirst(s.full_name);
    const pic = s.profile_picture || s.student_profile_picture || s.avatar_url || '';
    return `
    <div class="pcard pcard-invited" id="prow_${esc(String(s.id))}" onclick="openPeopleProfile('${esc(String(s.id))}','invited')">
      <div class="pav" style="${avStyle(idx)}">
        ${pic
          ? `<img src="${esc(pic)}" alt="${esc(name)}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
          : ini}
        <div class="pdot pdot-blue"></div>
      </div>
      <div class="pinfo">
        <div class="pname">${esc(name)}</div>
        <div class="pemail">${esc(s.email||'')}</div>
        ${tags(s)}
      </div>
      <div class="pright">
        <span class="pbadge pbadge-invited">Invitation sent</span>
        <span class="pinvite-hint">${iconCheck} Waiting for student</span>
        <button class="pact pact-cancel"
          onclick="event.stopPropagation();manageEnrollment('${esc(String(s.id))}','remove')"
          title="Cancel invitation">
          ${iconX} Cancel
        </button>
      </div>
    </div>`;
  }
 
  function joinCard(s, idx) {
    const ini  = initials(s.full_name);
    const name = formatLastFirst(s.full_name);
    const pic = s.profile_picture || s.student_profile_picture || s.avatar_url || '';
    return `
    <div class="pcard pcard-join" id="prow_${esc(String(s.id))}" onclick="openPeopleProfile('${esc(String(s.id))}','pending')">
      <div class="pav" style="${avStyle(idx)}">
        ${pic
          ? `<img src="${esc(pic)}" alt="${esc(name)}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
          : ini}
        <div class="pdot pdot-amber"></div>
      </div>
      <div class="pinfo">
        <div class="pname">${esc(name)}</div>
        <div class="pemail">${esc(s.email||'')}</div>
        ${tags(s)}
      </div>
      <div class="pright">
        <span class="pbadge pbadge-pending">Wants to join</span>
        <div class="pact-row">
          <button class="pact pact-approve"
            onclick="event.stopPropagation();manageEnrollment('${esc(String(s.id))}','approve')">
            ${iconTick} Approve
          </button>
          <button class="pact pact-decline"
            onclick="event.stopPropagation();manageEnrollment('${esc(String(s.id))}','remove')">
            ${iconX} Decline
          </button>
        </div>
      </div>
    </div>`;
  }
 
  /* ── section header ── */
  function secHead(label, count, pillClass) {
    return `
    <div class="psec-head">
      <span class="psec-label">${label}</span>
      <span class="ppill ${pillClass}">${count}</span>
    </div>`;
  }
 
  /* ── empty state ── */
  function emptyState(icon, title, sub) {
    return `<div class="pempty">
      <div class="pempty-icon"><i class="fas ${icon}"></i></div>
      <div class="pempty-title">${title}</div>
      <div class="pempty-sub">${sub}</div>
    </div>`;
  }
 
  /* ── assemble HTML ── */
  let html = `<style>
    .pcard{background:var(--surface);border:1px solid var(--border);border-radius:12px;
      padding:12px 16px;margin-bottom:8px;display:flex;align-items:center;gap:12px;
      transition:border-color .18s,background .18s;}
    .pcard:hover{border-color:rgba(26,158,120,.32);background:var(--surface);}
    .pcard-invited{border-color:#85b7eb;}
    .pcard-invited:hover{border-color:#378add;background:#e6f1fb;}
    .pcard-join{border-color:#fac775;}
    .pcard-join:hover{border-color:#ba7517;background:#faeeda;}
    .pav{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;
      justify-content:center;font-size:13px;font-weight:800;flex-shrink:0;position:relative;color:#fff;}
    .pdot{position:absolute;bottom:1px;right:1px;width:11px;height:11px;border-radius:50%;
      border:2px solid var(--surface);}
    .pdot-green{background:#1d9e75;}
    .pdot-blue{background:#378add;}
    .pdot-amber{background:#ef9f27;}
    .pinfo{flex:1;min-width:0;}
    .pname{font-size:.875rem;font-weight:700;color:var(--text);
      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .pemail{font-size:.75rem;color:var(--text-muted);margin-top:2px;
      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .ptags{display:flex;flex-wrap:wrap;gap:5px;margin-top:7px;}
    .ptag{font-size:10px;font-weight:600;padding:2px 7px;border-radius:6px;
      display:flex;align-items:center;gap:4px;}
    .ptag-id{background:var(--bg);color:var(--text-muted);border:1px solid var(--border);}
    .ptag-course{background:#e6f1fb;color:#185fa5;border:1px solid #85b7eb;}
    .pright{display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0;}
    .pbadge{font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;white-space:nowrap;border:1px solid transparent;}
    .pbadge-enrolled{background:#dcfce7;color:#15803d;border-color:#bbf7d0;}
    .pbadge-invited{background:#e6f1fb;color:#0c447c;}
    .pbadge-pending{background:#faeeda;color:#633806;}
    .pinvite-hint{font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:4px;}
    .pact{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:700;
      padding:3px 8px;border-radius:8px;border:1px solid var(--border);background:none;
      color:var(--text-muted);cursor:pointer;transition:all .15s;font-family:inherit;white-space:nowrap;}
    .pact-row{display:flex;gap:5px;}
    .pact-remove{background:#fff;color:#ef4444;border-color:#fecaca;}
    .pact-remove:hover{background:#fef2f2;color:#dc2626;border-color:#fca5a5;}
    .pact-cancel:hover{background:#fcebeb;color:#a32d2d;border-color:#f09595;}
    .pact-approve:hover{background:#e1f5ee;color:#0f6e56;border-color:#5dcaa5;}
    .pact-decline:hover{background:#fcebeb;color:#a32d2d;border-color:#f09595;}
    .psec-head{display:flex;align-items:center;justify-content:space-between;
      margin:0 0 10px;padding-top:4px;}
    .psec-label{font-size:11px;font-weight:700;letter-spacing:.7px;
      text-transform:uppercase;color:var(--text-muted);}
    .ppill{font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;}
    .ppill-green{background:#e1f5ee;color:#085041;}
    .ppill-amber{background:#faeeda;color:#633806;}
    .ppill-blue{background:#e6f1fb;color:#0c447c;}
    .psec-divider{height:1px;background:var(--border);margin:18px 0 14px;}
    .pempty{text-align:center;padding:2.5rem 1rem;color:var(--text-muted);}
    .pempty-icon{font-size:2rem;opacity:.2;margin-bottom:.5rem;}
    .pempty-title{font-size:.9rem;font-weight:600;margin-bottom:.25rem;}
    .pempty-sub{font-size:.8rem;}
  </style>`;
 
  /* enrolled */
  html += secHead('Students', enrolled.length, 'ppill-green');
  if (enrolled.length) {
    html += enrolled.map((s, i) => enrolledCard(s, i)).join('');
  } else {
    html += emptyState('fa-user-graduate', 'No students yet',
      'Invite students or share the class code so they can join.');
  }
 
  /* invited by faculty */
  if (invited.length) {
    html += `<div class="psec-divider"></div>`;
    html += secHead('Invited by you', invited.length, 'ppill-blue');
    html += invited.map((s, i) => invitedCard(s, enrolled.length + i)).join('');
  }
 
  /* self-join requests */
  if (joinReqs.length) {
    html += `<div class="psec-divider"></div>`;
    html += secHead('Join requests', joinReqs.length, 'ppill-amber');
    html += joinReqs.map((s, i) => joinCard(s, enrolled.length + invited.length + i)).join('');
  }
 
  document.getElementById('enrolledList').innerHTML = html;
 
/* hide old pending section — waitlist drawer handles this now */
  const pendSec = document.getElementById('pendingSection');
  if (pendSec) pendSec.style.display = 'none';

  /* ── waitlist notification bar ── */
  const wlCount = joinReqs.length;
  const bar = document.getElementById('wlNotifBar');
  if (bar) {
    if (wlCount > 0) { bar.classList.add('visible'); }
    else             { bar.classList.remove('visible'); }
  }
  ['wlBarBadge','wlSideBadge','wlDrawerBadge'].forEach(id=>{
    const el=document.getElementById(id); if(el) el.textContent=wlCount;
  });
  ['wlBarCount','wlSideCount'].forEach(id=>{
    const el=document.getElementById(id); if(el) el.textContent=wlCount;
  });
  ['wlBarPlural','wlSidePlural'].forEach(id=>{
    const el=document.getElementById(id); if(el) el.textContent=wlCount===1?'':'s';
  });
  const wlSide = document.getElementById('wlSideCard');
  if (wlSide) wlSide.style.display = wlCount > 0 ? '' : 'none';

  /* ── animated tab dot ── */
  const peopleTab = document.getElementById('peopleTabBtn');
  if (peopleTab) {
    let dot = peopleTab.querySelector('.wl-tab-dot');
    if (wlCount > 0) {
      if (!dot) {
        dot = document.createElement('span');
        dot.className = 'wl-tab-dot';
        peopleTab.appendChild(dot);
      }
      dot.textContent = wlCount;
    } else if (dot) { dot.remove(); }
  }

  /* ── re-render drawer if open ── */
  if (document.getElementById('wlDrawer').classList.contains('open')) {
    wlRenderList(joinReqs);
  }
}

function openPeopleProfileLegacy(studentId, sourceType = 'enrolled') {
  const sid = String(studentId || '');
  const source = String(sourceType || 'enrolled');
  let row = null;

  if (source === 'enrolled') {
    row = (allPeople || []).find(x => String(x.id) === sid);
  } else if (source === 'invited') {
    const invited = (window.allInvitations && window.allInvitations.length)
      ? window.allInvitations
      : (allPending || []).filter(s => s.source === 'invitation');
    row = invited.find(x => String(x.id) === sid);
  } else {
    row = (allPending || []).find(x => String(x.id) === sid);
  }

  if (!row) {
    toast('Student profile not found.', 'warning');
    return;
  }

  const fullName = formatLastFirst(row.full_name || row.name || 'Unknown');
  const email = row.email || row.user_email || '—';
  const studentNumber = row.student_number || '—';
  const courseCode = row.course_code || '—';
  const yearLevel = row.year_level ? `Year ${row.year_level}` : '—';
  const section = row.section || '—';
  const status = source === 'enrolled' ? 'Enrolled' : (source === 'invited' ? 'Invited' : 'Pending request');
  const initials = String(fullName).split(',').join(' ').trim().split(/\s+/).map(p => p[0] || '').join('').slice(0,2).toUpperCase() || 'ST';
  const pic = row.profile_picture || row.student_profile_picture || row.avatar_url || '';

  Swal.fire({
    title: 'Student Profile',
    html: `
      <div style="display:flex;align-items:center;gap:.8rem;margin-bottom:.9rem;padding:.75rem;border:1px solid var(--border);border-radius:12px;background:var(--bg);">
        <div style="width:52px;height:52px;border-radius:50%;overflow:hidden;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1rem;flex-shrink:0;">
          ${pic
            ? `<img src="${esc(pic)}" alt="${esc(fullName)}" style="width:100%;height:100%;object-fit:cover;">`
            : esc(initials)}
        </div>
        <div style="min-width:0;">
          <div style="font-size:1rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(fullName)}</div>
          <div style="font-size:.78rem;color:var(--text-muted);">${esc(email)}</div>
        </div>
      </div>
      <div style="text-align:left;display:grid;grid-template-columns:1fr 1fr;gap:.65rem 1rem;font-size:.9rem;">
        <div><strong>Name</strong><br>${esc(fullName)}</div>
        <div><strong>Status</strong><br>${esc(status)}</div>
        <div><strong>Student ID</strong><br>${esc(studentNumber)}</div>
        <div><strong>Course</strong><br>${esc(courseCode)}</div>
        <div><strong>Section</strong><br>${esc(yearLevel)} ${section !== '—' ? `• ${esc(section)}` : ''}</div>
      </div>
    `,
    confirmButtonText: 'Close',
    confirmButtonColor: '#1a9e78',
    width: 700
  });
}

/* ══ WAITLIST DRAWER FUNCTIONS ══ */
function openPeopleProfile(studentId, sourceType = 'enrolled') {
  const sid = String(studentId || '');
  const source = String(sourceType || 'enrolled');
  let row = null;

  if (source === 'enrolled') {
    row = (allPeople || []).find(x => String(x.id) === sid);
  } else if (source === 'invited') {
    const invited = (window.allInvitations && window.allInvitations.length)
      ? window.allInvitations
      : (allPending || []).filter(s => s.source === 'invitation');
    row = invited.find(x => String(x.id) === sid);
  } else {
    row = (allPending || []).find(x => String(x.id) === sid);
  }

  if (!row) {
    toast('Student profile not found.', 'warning');
    return;
  }

  const fullName = formatLastFirst(row.full_name || row.name || 'Unknown');
  const email = row.email || row.user_email || '-';
  const studentNumber = row.student_number || '-';
  const status = source === 'enrolled' ? 'Enrolled' : (source === 'invited' ? 'Invited' : 'Pending request');
  const initials = String(fullName).split(',').join(' ').trim().split(/\s+/).map(p => p[0] || '').join('').slice(0,2).toUpperCase() || 'ST';
  const pic = row.profile_picture || row.student_profile_picture || row.avatar_url || '';

  Swal.fire({
    title: 'Student Profile',
    html: `
      <div style="display:flex;align-items:center;gap:.8rem;margin-bottom:.9rem;padding:.75rem;border:1px solid var(--border);border-radius:12px;background:var(--bg);">
        <div style="width:52px;height:52px;border-radius:50%;overflow:hidden;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1rem;flex-shrink:0;">
          ${pic
            ? `<img src="${esc(pic)}" alt="${esc(fullName)}" style="width:100%;height:100%;object-fit:cover;">`
            : esc(initials)}
        </div>
        <div style="min-width:0;">
          <div style="font-size:1rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(fullName)}</div>
          <div style="font-size:.78rem;color:var(--text-muted);">${esc(email)}</div>
        </div>
      </div>
      <div style="text-align:left;display:grid;grid-template-columns:1fr 1fr;gap:.65rem 1rem;font-size:.9rem;">
        <div><strong>Status</strong><br>${esc(status)}</div>
        <div><strong>Student ID</strong><br>${esc(studentNumber)}</div>
      </div>
    `,
    confirmButtonText: 'Close',
    confirmButtonColor: '#1a9e78',
    width: 700
  });
}

function openWaitlistDrawer(){
  const overlay=document.getElementById('wlOverlay');
  const drawer=document.getElementById('wlDrawer');
  const joinReqs=allPending.filter(s=>s.source==='join_request');
  overlay.classList.add('show');
  drawer.classList.remove('closing');
  drawer.classList.add('open');
  document.body.style.overflow='hidden';
  document.getElementById('wlSearchInput').value='';
  wlRenderList(joinReqs);
  setTimeout(()=>document.getElementById('wlSearchInput').focus(),180);
}

function closeWaitlistDrawer(){
  const overlay=document.getElementById('wlOverlay');
  const drawer=document.getElementById('wlDrawer');
  overlay.classList.remove('show');
  drawer.classList.add('closing');
  document.body.style.overflow='';
  setTimeout(()=>{drawer.classList.remove('open','closing');},220);
}

document.addEventListener('keydown',e=>{
  if(e.key==='Escape'&&document.getElementById('wlDrawer').classList.contains('open'))closeWaitlistDrawer();
});

function wlRenderList(list){
  const el=document.getElementById('wlList');
  const footer=document.getElementById('wlDrawerFooter');
  const badge=document.getElementById('wlDrawerBadge');
  const admitLbl=document.getElementById('wlAdmitAllLabel');
  if(badge) badge.textContent=list.length;
  if(admitLbl) admitLbl.textContent=`Admit all ${list.length}`;
  if(footer){ if(list.length>1) footer.classList.add('visible'); else footer.classList.remove('visible'); }
  if(!list.length){
    el.innerHTML=`<div class="wl-empty-state"><div class="wl-ei"><i class="fas fa-inbox"></i></div><h3>No pending requests</h3><p>All join requests have been handled.</p></div>`;
    return;
  }
  const AV=['wl-av-0','wl-av-1','wl-av-2','wl-av-3','wl-av-4'];
  el.innerHTML=list.map((s,i)=>{
    const ini=((s.full_name||'??').trim().split(/\s+/).map(w=>w[0]||'').join('').substring(0,2)).toUpperCase();
    const name=formatLastFirst(s.full_name||s.email||'Unknown');
    let tagsHtml='';
    if(s.course_code) tagsHtml+=`<span class="wl-tag wl-tag-course"><i class="fas fa-graduation-cap" style="font-size:.55rem;"></i>${esc(s.course_code)}</span>`;
    if(s.year_level)  tagsHtml+=`<span class="wl-tag wl-tag-yr"><i class="fas fa-layer-group" style="font-size:.55rem;"></i>Yr ${esc(String(s.year_level))}${s.section?'-'+esc(s.section):''}</span>`;
    if(s.student_number) tagsHtml+=`<span class="wl-tag wl-tag-id"><i class="fas fa-id-badge" style="font-size:.55rem;"></i>${esc(s.student_number)}</span>`;
    return `<div class="wl-card" id="wlcard_${esc(String(s.id))}">
      <div class="wl-card-top">
        <div class="wl-av ${AV[i%AV.length]}">${ini}</div>
        <div class="wl-card-info">
          <div class="wl-card-name">${esc(name)}</div>
          <div class="wl-card-email">${esc(s.email||'')}</div>
        </div>
        <div class="wl-card-time">${s.requested_at?formatDate(s.requested_at):''}</div>
      </div>
      ${tagsHtml?`<div class="wl-card-tags">${tagsHtml}</div>`:''}
      <div class="wl-card-actions">
        <button class="wl-admit-btn" onclick="wlAdmitOne('${esc(String(s.id))}')"><i class="fas fa-check" style="font-size:.72rem;"></i> Admit</button>
        <button class="wl-decline-btn" onclick="wlDeclineOne('${esc(String(s.id))}')"><i class="fas fa-times" style="font-size:.72rem;"></i> Decline</button>
      </div>
    </div>`;
  }).join('');
}

function wlSearch(query){
  let list=allPending.filter(s=>s.source==='join_request');
  const q=(query||'').trim().toLowerCase();
  if(q.length>=1){
    list=list.filter(s=>(s.full_name||'').toLowerCase().includes(q)||(s.email||'').toLowerCase().includes(q)||(s.student_number||'').toLowerCase().includes(q)||(s.course_code||'').toLowerCase().includes(q));
  }
  const sort=document.getElementById('wlSortSelect')?.value||'newest';
  if(sort==='name') list.sort((a,b)=>(a.full_name||'').localeCompare(b.full_name||''));
  else if(sort==='course') list.sort((a,b)=>(a.course_code||'').localeCompare(b.course_code||''));
  wlRenderList(list);
}

async function wlAdmitOne(studentId){
  const card=document.getElementById('wlcard_'+studentId);
  if(card){card.style.opacity='.4';card.style.pointerEvents='none';}
  try{
    const res=await fetch('API/facultyUI/classroom/manage_enrollment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({class_id:CLASS_ID,student_id:studentId,action:'approve'})});
    const data=await res.json();
    if(data.status==='success'){toast(data.message);await loadClassroom();wlSearch(document.getElementById('wlSearchInput').value);}
    else{toast(data.message||'Failed','error');if(card){card.style.opacity='';card.style.pointerEvents='';}}
  }catch{toast('Network error','error');if(card){card.style.opacity='';card.style.pointerEvents='';}}
}

async function wlDeclineOne(studentId){
  const r=await Swal.fire({title:'Decline this request?',text:'The student will not be admitted.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Yes, decline',cancelButtonText:'Cancel'});
  if(!r.isConfirmed)return;
  const card=document.getElementById('wlcard_'+studentId);
  if(card){card.style.opacity='.4';card.style.pointerEvents='none';}
  try{
    const res=await fetch('API/facultyUI/classroom/manage_enrollment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({class_id:CLASS_ID,student_id:studentId,action:'remove'})});
    const data=await res.json();
    if(data.status==='success'){toast('Request declined.');await loadClassroom();wlSearch(document.getElementById('wlSearchInput').value);}
    else{toast(data.message||'Failed','error');if(card){card.style.opacity='';card.style.pointerEvents='';}}
  }catch{toast('Network error','error');if(card){card.style.opacity='';card.style.pointerEvents='';}}
}

async function wlAdmitAll(){
  const list=allPending.filter(s=>s.source==='join_request');
  if(!list.length)return;
  const r=await Swal.fire({title:`Admit all ${list.length} student${list.length>1?'s':''}?`,icon:'question',showCancelButton:true,confirmButtonColor:'#1a9e78',confirmButtonText:'<i class="fas fa-check-double"></i> Yes, admit all',cancelButtonText:'Cancel'});
  if(!r.isConfirmed)return;
  const btn=document.getElementById('wlAdmitAllBtn');
  if(btn){btn.disabled=true;btn.innerHTML='<span class="spin"></span> Admitting…';}
  try{
    await Promise.all(list.map(s=>fetch('API/facultyUI/classroom/manage_enrollment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({class_id:CLASS_ID,student_id:s.id,action:'approve'})})));
    toast(`${list.length} student${list.length>1?'s':''} admitted!`);
    await loadClassroom();
    closeWaitlistDrawer();
  }catch{toast('Network error','error');}
  finally{if(btn){btn.disabled=false;btn.innerHTML='<i class="fas fa-check-double"></i> <span id="wlAdmitAllLabel">Admit all</span>';}}
}

async function wlDeclineAll(){
  const list=allPending.filter(s=>s.source==='join_request');
  if(!list.length)return;
  const r=await Swal.fire({title:`Decline all ${list.length} request${list.length>1?'s':''}?`,text:'All pending join requests will be rejected.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Yes, decline all',cancelButtonText:'Cancel'});
  if(!r.isConfirmed)return;
  try{
    await Promise.all(list.map(s=>fetch('API/facultyUI/classroom/manage_enrollment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({class_id:CLASS_ID,student_id:s.id,action:'remove'})})));
    toast('All requests declined.');await loadClassroom();closeWaitlistDrawer();
  }catch{toast('Network error','error');}
}
 
/* ── Name helpers ── */
function formatLastFirst(fullName) {
  if (!fullName) return '—';
  const parts = fullName.trim().split(/\s+/);
  if (parts.length === 1) return fullName.toUpperCase();
  const last  = parts[parts.length - 1].toUpperCase();
  const first = parts[0];
  const midParts = parts.slice(1, parts.length - 1);
  const mid = midParts.length
    ? ' ' + midParts.map(p => (p[0] || '').toUpperCase() + '.').join(' ')
    : '';
  return `${last}, ${first}${mid}`;
}
 
function formatInitials(fullName) {
  if (!fullName) return '??';
  const parts = fullName.trim().split(/\s+/);
  return ((parts[0]?.[0] ?? '') + (parts[parts.length - 1]?.[0] ?? '')).toUpperCase() || '??';
}
 
/* ── Remove with confirmation ── */
async function confirmRemoveStudent(studentId, name) {
  const r = await Swal.fire({
    title: `Remove ${name}?`,
    html: `<p style="color:var(--text-muted);font-size:.88rem;line-height:1.6;">
             This student will be removed from the class.<br>
             They can be re-invited later.
           </p>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d93025',
    confirmButtonText: '<i class="fas fa-user-minus"></i> Yes, remove',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  });
 if (!r.isConfirmed) return;
  await manageEnrollment(studentId, 'remove', true);
}

async function manageEnrollment(studentId,action,skipConfirm=false){
  if(!skipConfirm){
    const r=await Swal.fire({title:`${action==='approve'?'Approve':'Remove'} student?`,icon:action==='approve'?'question':'warning',showCancelButton:true,confirmButtonColor:action==='approve'?'#1a9e78':'#d93025',confirmButtonText:`Yes, ${action}`,cancelButtonText:'Cancel'});
    if(!r.isConfirmed)return;
  }
  try{const res=await fetch('API/facultyUI/classroom/manage_enrollment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({class_id:CLASS_ID,student_id:studentId,action})});const data=await res.json();if(data.status==='success'){toast(data.message);await loadClassroom();}else toast(data.message,'error');}catch{toast('Network error','error');}
}

function toDatetimeLocal(value) {
  if (!value) return '';

  const s = String(value).trim();

  if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(s)) {
    return s.substring(0, 16);
  }

  if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/.test(s)) {
    return s.replace(' ', 'T').substring(0, 16);
  }

  const d = new Date(s);
  if (Number.isNaN(d.getTime())) return '';

  const pad = n => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function editPost(postId) {
  const p = allPosts.find(x => String(x.id) === String(postId));

  if (!p) {
    toast('Post not found.', 'error');
    return;
  }

  const typeObj =
    postTypes.find(t => String(t.id) === String(p.post_type_id)) ||
    postTypes.find(t => String(t.type_key) === String(p.post_type)) ||
    postTypes[0];

  _skipDraftResume = true;
  openPostModal(typeObj);
  _skipDraftResume = false;

  document.getElementById('pmPostId').value = p.id || '';
  document.getElementById('pmTitle').value = p.title || '';
  document.getElementById('pmBody').value = p.body || '';
  document.getElementById('pmDueDate').value = toDatetimeLocal(p.due_date || '');
  if (p.due_date) {
  const d = new Date(p.due_date);
  _ddYear  = d.getFullYear();
  _ddMonth = d.getMonth();
  _ddDay   = d.getDate();
  const h12 = d.getHours() % 12 || 12;
  const ampm = d.getHours() >= 12 ? 'PM' : 'AM';
  const pad = n => String(n).padStart(2,'0');
  document.getElementById('dueDateHour').value = h12;
  document.getElementById('dueDateMin').value  = pad(d.getMinutes());
  document.getElementById('dueDateAmpm').value = ampm;
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  document.getElementById('dueDateLabel').textContent =
    `${months[_ddMonth]} ${_ddDay}, ${_ddYear} · ${h12}:${pad(d.getMinutes())} ${ampm}`;
  document.getElementById('dueDateDisplay').style.color = 'var(--text)';
  document.getElementById('dueDateDisplay').style.borderColor = 'var(--primary)';
}
  document.getElementById('pmPoints').value = p.points ?? '';
  document.getElementById('pmTopic').value = p.topic || '';
  document.querySelectorAll('input[name="pmLessonPeriod"]').forEach(input => {
    input.checked = input.value === (p.lesson_period || '');
  });
  const coverageEl = document.getElementById('pmQuizCoverage');
  if (coverageEl) coverageEl.value = p.topic || '';

  // ── Populate quiz settings when editing a quiz ─────────────────
  const _setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };
  _setVal('pmOpenAt',           toDatetimeLocal(p.open_at  || ''));
  _setVal('pmCloseAt',          toDatetimeLocal(p.close_at || ''));
  _setVal('pmMaxAttempts',      p.max_attempts || 1);
  _setVal('pmTimeMode',         p.time_mode || 'none');
  _setVal('pmPassingThreshold', p.passing_threshold ?? '');

  if (p.time_mode === 'per_quiz' && p.time_limit_seconds) {
    _setVal('pmTimePerQuizMinutes', Math.round(p.time_limit_seconds / 60));
  } else {
    _setVal('pmTimePerQuizMinutes', '');
  }
  if (p.time_mode === 'per_question' && Array.isArray(p.questions) && p.questions.length > 0) {
    const _firstQ = p.questions.find(q => q.time_limit_seconds);
    _setVal('pmTimePerQuestionSecs', _firstQ ? _firstQ.time_limit_seconds : '');
  } else {
    _setVal('pmTimePerQuestionSecs', '');
  }
  if (typeof onTimeModeChange === 'function') onTimeModeChange();

 if (typeObj) {
    selectGcType(typeObj);
  }

  // Restore title AFTER selectGcType (which may auto-fill it)
  document.getElementById('pmTitle').value = p.title || '';
  const editIsLesson = String(typeObj?.type_key || '').toLowerCase() === 'lesson'
    || String(typeObj?.type_label || '').toLowerCase().includes('lesson');
  if (editIsLesson) {
    const editWeeks = parseLessonWeeksFromText(p.title || '');
    if (editWeeks.length) {
      setSelectedLessonWeeks(editWeeks, { skipDraft:true });
    } else {
      selectedLessonWeeks = [];
      syncLessonWeekDisplay({ skipDraft:true, keepTitleWhenEmpty:true });
    }
  }
  userTypedTitle = true; // lock — prevent any further auto-fill

  pendingFiles = [];
  pendingLinks = [];
  document.getElementById('attachPreviews').innerHTML = '';

  if (Array.isArray(p.attachments)) {
    p.attachments.forEach(a => {
      if (a.attach_type === 'link' || a.attach_type === 'youtube') {
        const item = {
          type: a.attach_type,
          url: a.url || ''
        };

        if (item.url) {
          pendingLinks.push(item);
          addAttachPreview(item.type, item.url, () => {
            pendingLinks = pendingLinks.filter(x => x.url !== item.url);
          });
        }
      } else {
        const chip = document.createElement('div');
        chip.className = 'attach-chip';
        chip.innerHTML = `<i class="${mimeIconFromName(a.file_name || '')}"></i><span class="chip-name" title="${esc(a.file_name || 'File')}">${esc(a.file_name || 'File')}</span><span style="font-size:.68rem;color:var(--text-muted);margin-left:.35rem;">saved</span>`;
        document.getElementById('attachPreviews').appendChild(chip);
      }
    });
  }

  quizQuestions = Array.isArray(p.questions)
    ? p.questions.map((q, index) => ({
        id: 'edit_' + Date.now() + '_' + index,
        question: q.question || '',
        answer: q.answer || q.answer_key || '',
        points: parseFloat(q.points) || 1,
        cognitive_level: String(q.cognitive_level || '').toLowerCase(),
        time_limit_seconds: parseInt(q.time_limit_seconds || 0, 10) || '',
        choices: Array.isArray(q.choices)
          ? q.choices.map(c => ({
              text: c.text || c.choice_text || '',
              is_correct: !!Number(c.is_correct)
            }))
          : []
      }))
    : [];

  renderQuizBuilder();

  submissionMode = p.submission_mode || 'individual';
  renderSubModeRow();
  if (submissionMode === 'group') {
    renderGroupBuilder();
  }

  userTypedTitle = true; // prevent auto-fill overwriting the existing title
  document.getElementById('gcModalHeading').textContent = 'Edit ' + (typeObj?.type_label || 'Post');
  document.getElementById('gcSubmitLabel').textContent = 'Save';
}

async function deletePost(postId){
  const r = await Swal.fire({
    title:'Delete this post?', icon:'warning', text:'This cannot be undone.',
    showCancelButton:true, confirmButtonColor:'#d93025',
    confirmButtonText:'Delete', cancelButtonText:'Cancel'
  });
  if (!r.isConfirmed) return;
  try {
    const res  = await fetch('API/facultyUI/classroom/delete_post.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({post_id: postId})
    });
    const data = await res.json();
    if (data.status === 'success') {
      toast('Post deleted.');
      // Remove card from DOM instantly — no full reload needed
      const card = document.querySelector(`.post-card[data-post-id="${postId}"]`);
      if (card) {
        card.style.transition = 'opacity .2s, transform .2s';
        card.style.opacity    = '0';
        card.style.transform  = 'scale(.97)';
        setTimeout(() => card.remove(), 200);
      }
      // Keep local cache in sync
      if (typeof allPosts !== 'undefined') allPosts = allPosts.filter(p => p.id !== postId);
    } else {
      toast(data.message, 'error');
    }
  } catch { toast('Network error', 'error'); }
}

/* ══════════════════════════════════════════
   JOIN CODE
══════════════════════════════════════════ */
function renderJoinCode(){
  const code  = classData.join_code || classData.class_code || '—';
  const token = classData.join_link_token || '';
  const link  = token ? `${BASE_URL}join.php?token=${token}` : '';
  const peopleLink = link || code;

  ['joinCodeDisplay','peopleCodeDisplay'].forEach(id=>{
    const el=document.getElementById(id); if(el) el.textContent=code;
  });
  if(link){
    const row=document.getElementById('joinLinkRow');
    const txt=document.getElementById('joinLinkText');
    if(row) row.style.display='flex';
    if(txt) txt.textContent=link;
  }
  const peopleLinkTxt = document.getElementById('peopleJoinLinkText');
  if (peopleLinkTxt) peopleLinkTxt.textContent = peopleLink;
  const setPeopleCodeMode = (mode) => {
    const isLink = mode === 'link';
    const row = document.getElementById('peopleJoinLinkRow');
    const codeBtn = document.getElementById('peopleCopyCode');
    const linkBtn = document.getElementById('peopleCopyLink');
    if (row) row.style.display = isLink ? 'flex' : 'none';
    if (codeBtn) codeBtn.classList.toggle('active', !isLink);
    if (linkBtn) linkBtn.classList.toggle('active', isLink);
  };
  const copyCode=()=>copyText(code,'Code copied!');
  const copyLink=()=>copyText(link||code,'Link copied!');
  ['copyCodeBtn'].forEach(id=>{const el=document.getElementById(id);if(el)el.onclick=copyCode;});
  ['copyLinkBtn','copyLinkSmall'].forEach(id=>{const el=document.getElementById(id);if(el)el.onclick=copyLink;});
  const peopleCodeBtn = document.getElementById('peopleCopyCode');
  const peopleLinkBtn = document.getElementById('peopleCopyLink');
  const peopleLinkSmall = document.getElementById('peopleCopyLinkSmall');
  if (peopleCodeBtn) peopleCodeBtn.onclick = () => { setPeopleCodeMode('code'); copyCode(); };
  if (peopleLinkBtn) peopleLinkBtn.onclick = () => { setPeopleCodeMode('link'); copyLink(); };
  if (peopleLinkSmall) peopleLinkSmall.onclick = copyLink;
  setPeopleCodeMode('code');
  const disp=document.getElementById('joinCodeDisplay');if(disp)disp.onclick=copyCode;

  const regenBtn = document.getElementById('regenCodeBtn');
  if (regenBtn) regenBtn.onclick=async()=>{
    const r=await Swal.fire({title:'Reset join code?',icon:'warning',text:'The old code and link will stop working.',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Reset',cancelButtonText:'Cancel'});
    if(!r.isConfirmed)return;
    regenBtn.disabled = true;
    regenBtn.classList.add('is-spinning');
    try{
      const res=await fetch('API/facultyUI/classroom/regenerate_code.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({class_id:CLASS_ID})});
      const data=await res.json();
      if(data.status==='success'){classData.join_code=data.join_code;classData.join_link_token=data.join_token;renderJoinCode();toast('Join code reset!');}
      else toast(data.message,'error');
    }catch{toast('Network error','error');}
    finally{
      regenBtn.disabled = false;
      regenBtn.classList.remove('is-spinning');
    }
  };
}
function renderClassInfo(){
  const c=classData;
  const exportRows=[
    ['Semester',c.class_semester],
    ['Year Level',c.year_level],
    ['Schedule',c.class_days],
    ['Time',c.schedule?c.schedule.split('-').map(t=>fmt12(t.trim())).join(' - '):null],
    ['Room',c.room||c.class_room||'-']
  ].filter(r=>r[1]);
  const weekTotal = Math.max(1, Math.min(30, Number(lessonWeekCount || 18)));
  const weekHtml = `<div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--border);">
    <p style="font-size:.625rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.5rem;">Academic Weeks</p>
    <div style="display:flex;align-items:center;justify-content:space-between;font-size:.75rem;margin-bottom:.35rem;">
      <span style="color:var(--text-muted);">Total weeks:</span>
      <span style="font-weight:800;color:var(--primary);">${weekTotal}</span>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:.25rem;">
      ${Array.from({length:weekTotal},(_,i)=>`<span style="font-size:.56rem;padding:.12rem .28rem;background:var(--primary-light);color:var(--primary);border-radius:4px;font-weight:700;">W${i+1}</span>`).join('')}
    </div>
  </div>`;
  document.getElementById('classInfoList').innerHTML=exportRows.map(([k,v])=>`<div style="display:flex;justify-content:space-between;gap:.6rem;"><span style="color:var(--text-muted);">${esc(k)}:</span><span style="font-weight:600;color:var(--text);text-align:right;">${esc(v)}</span></div>`).join('') + weekHtml;
  return;
  const rows=[['Semester',c.class_semester],['Year Level',c.year_level],['Section',c.section],['Days',c.class_days],['Schedule',c.schedule?c.schedule.split('-').map(t=>fmt12(t.trim())).join(' – '):null],['Subject',c.subject_name||c.subject_code],['Course',c.course_code]].filter(r=>r[1]);
  document.getElementById('classInfoList').innerHTML=rows.map(([k,v])=>`<div style="display:flex;gap:.4rem;"><span style="font-weight:600;color:var(--text);min-width:80px;">${esc(k)}:</span><span>${esc(v)}</span></div>`).join('');
}

/* ══════════════════════════════════════════
   COMMENTS
══════════════════════════════════════════ */
async function toggleComments(postId,btn){
  const bodyEl=document.getElementById('cmtbody_'+postId);const isOpen=bodyEl.classList.contains('open');
  btn.classList.toggle('open',!isOpen);bodyEl.classList.toggle('open',!isOpen);if(!isOpen)await loadComments(postId);
}
function toggleCommentsFromStrip(postId){
  const btn=document.getElementById('cmttog_'+postId);
  if(!btn) return;
  toggleComments(postId,btn);
}
async function loadComments(postId){
  const list=document.getElementById('cmtlist_'+postId);
  list.innerHTML='<div class="cmt-empty"><i class="fas fa-spinner fa-spin"></i> Loading…</div>';
  try{
    const res=await fetch(`API/facultyUI/classroom/get_comments.php?post_id=${encodeURIComponent(postId)}`);
    const data=await res.json();
    if(data.status==='success'){renderCommentList(postId,data.comments);updateCmtCount(postId,data.comments.length);}
    else list.innerHTML=`<div class="cmt-empty">${esc(data.message||'Could not load comments.')}</div>`;
  }catch(e){list.innerHTML='<div class="cmt-empty">Network error.</div>';console.error('loadComments',e);}
}
function renderCommentList(postId,comments){
  const list=document.getElementById('cmtlist_'+postId);
  if(!comments.length){list.innerHTML='<div class="cmt-empty">No comments yet — be the first!</div>';return;}
  list.innerHTML=comments.map(c=>buildCommentHTML(c)).join('');
}
function buildCommentHTML(c){
  const isFaculty=c.user_type==='faculty';const isOwn=c.user_id===String(SESSION_USER_ID);const canDel=SESSION_USER_TYPE==='faculty'||isOwn;
  const initials=(c.full_name||'?').split(' ').map(w=>w[0]||'').join('').substring(0,2).toUpperCase();
  return`<div class="comment-item" id="cmt_${esc(c.id)}"><div class="cmt-av ${isFaculty?'is-faculty':''}">${initials}</div><div class="cmt-bubble"><div class="cmt-meta"><span class="cmt-author ${isFaculty?'is-faculty':''}">${esc(c.full_name||'Unknown')}</span>${isFaculty?'<span class="cmt-faculty-badge">Faculty</span>':''}<span class="cmt-time">${formatDate(c.created_at)}</span></div><div class="cmt-text">${esc(c.comment_text)}</div>${canDel?`<button class="cmt-del" onclick="deleteComment('${esc(c.id)}','${esc(c.post_id)}')" title="Delete comment"><i class="fas fa-trash"></i></button>`:''}</div></div>`;
}
function cmtKey(e,postId){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendComment(postId);}}
function cmtResize(el){el.style.height='auto';el.style.height=Math.min(el.scrollHeight,110)+'px';}
async function sendComment(postId){
  const input=document.getElementById('cmtin_'+postId);
  const sendBtn=document.getElementById('cmtsend_'+postId);
  const text=input.value.trim();if(!text)return;
  sendBtn.disabled=true;sendBtn.innerHTML='<i class="fas fa-spinner fa-spin"></i>';
  try{
    const res=await fetch('API/facultyUI/classroom/save_comment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({post_id:postId,comment_text:text})});
    const contentType=res.headers.get('content-type')||'';
    if(!contentType.includes('application/json')){const raw=await res.text();console.error('save_comment non-JSON:',raw);toast('Server error — check console.','error');return;}
    const data=await res.json();
    if(data.status==='success'){input.value='';input.style.height='';appendComment(postId,data.comment);}
    else toast(data.message||'Failed to post comment.','error');
  }catch(e){toast('Network error: '+e.message,'error');console.error('sendComment error',e);}
  finally{sendBtn.disabled=false;sendBtn.innerHTML='<i class="fas fa-paper-plane"></i>';}
}
function appendComment(postId,c){
  const list=document.getElementById('cmtlist_'+postId);const empty=list.querySelector('.cmt-empty');if(empty)empty.remove();
  list.insertAdjacentHTML('beforeend',buildCommentHTML(c));const newEl=document.getElementById('cmt_'+c.id);if(newEl)newEl.scrollIntoView({behavior:'smooth',block:'nearest'});
  updateCmtCount(postId,list.querySelectorAll('.comment-item').length);
}
function updateCmtCount(postId,count){const lbl=document.querySelector(`#cmtwrap_${postId} .cmt-count-lbl`);if(lbl)lbl.textContent=count>0?`${count} comment${count!==1?'s':''}`:'Class comments';}
async function deleteComment(commentId,postId){
  const r=await Swal.fire({title:'Delete this comment?',icon:'warning',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Delete',cancelButtonText:'Cancel'});if(!r.isConfirmed)return;
  try{const res=await fetch('API/facultyUI/classroom/delete_comment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({comment_id:commentId})});const data=await res.json();if(data.status==='success'){const el=document.getElementById('cmt_'+commentId);if(el){el.style.transition='opacity .2s';el.style.opacity='0';setTimeout(()=>{el.remove();const list=document.getElementById('cmtlist_'+postId);if(list&&!list.querySelector('.comment-item'))list.innerHTML='<div class="cmt-empty">No comments yet — be the first!</div>';updateCmtCount(postId,list?list.querySelectorAll('.comment-item').length:0);},200);}toast('Comment deleted.');}else toast(data.message||'Failed','error');}catch{toast('Network error','error');}
}

/* ════════════════════════════════════════════════════════════════
   FILE VIEWER
════════════════════════════════════════════════════════════════ */
(function(){
  'use strict';
  const PDFJS_LIB   ='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
  const PDFJS_WORKER='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  let pdfDoc=null,curPage=1,totalPages=1,scale=1.5,rendering=false;
  let _attachId=null, _curPageNum=null, _panelOpen=true;
  const backdrop    =document.getElementById('fvBackdrop');
  const bodyEl      =document.getElementById('fvBody');
  const loadingEl   =document.getElementById('fvLoading');
  const filenameLbl =document.getElementById('fvFilename');
  const badgeLbl    =document.getElementById('fvBadge');
  const downloadBtn =document.getElementById('fvDownloadBtn');
  const closeBtn    =document.getElementById('fvCloseBtn');
  const pageGrp     =document.getElementById('fvPageGroup');
  const zoomGrp     =document.getElementById('fvZoomGroup');
  const sep1        =document.getElementById('fvSep1');
  const pageInfo    =document.getElementById('fvPageInfo');
  const zoomLabel   =document.getElementById('fvZoomLabel');
  const prevBtn     =document.getElementById('fvPrev');
  const nextBtn     =document.getElementById('fvNext');
  const zoomInBtn   =document.getElementById('fvZoomIn');
  const zoomOutBtn  =document.getElementById('fvZoomOut');
  const sidePanel   =document.getElementById('fvSidePanel');
  const panelToggle =document.getElementById('fvPanelToggle');
  const panelClose  =document.getElementById('fvPanelClose');
  const pageStripLbl=document.getElementById('fvPageStripLabel');
  const noteList    =document.getElementById('fvNoteList');
  const noteInput   =document.getElementById('fvNoteInput');
  const noteSend    =document.getElementById('fvNoteSend');
  const noteTabBadge=document.getElementById('fvNoteTabBadge');
  const noteToolBadge=document.getElementById('fvNoteToolBadge');
  function showLoad(){loadingEl.style.display='flex';}
  function hideLoad(){loadingEl.style.display='none';}
  function clearBody(){Array.from(bodyEl.children).forEach(c=>{if(c!==loadingEl)c.remove();});}
  function setControls(pdf){pageGrp.style.display=pdf?'flex':'none';zoomGrp.style.display=pdf?'flex':'none';sep1.style.display=pdf?'':'none';}
  panelToggle.addEventListener('click',()=>{_panelOpen=!_panelOpen;sidePanel.classList.toggle('collapsed',!_panelOpen);});
  panelClose.addEventListener('click',()=>{_panelOpen=false;sidePanel.classList.add('collapsed');});
  function updatePageStrip(){pageStripLbl.textContent=_curPageNum!==null?`Page ${_curPageNum} only`:'This file';}
  async function loadNotes(){
    if(!_attachId){noteList.innerHTML=`<div class="fv-empty-msg"><i class="fas fa-lock"></i>Not available for this file.</div>`;noteTabBadge.textContent='0';noteToolBadge.textContent='0';return;}
    noteList.innerHTML=`<div class="fv-empty-msg" style="padding:1rem 0;"><i class="fas fa-spinner fa-spin" style="font-size:.9rem;opacity:.6;display:inline;"></i></div>`;
    const pageParam=_curPageNum!==null?`&page_number=${_curPageNum}`:'';
    try{
      const res=await fetch(`API/facultyUI/classroom/get_annotations.php?attach_id=${encodeURIComponent(_attachId)}&tab=note${pageParam}`);
      const ct=res.headers.get('content-type')||'';
      if(!ct.includes('application/json')){const raw=await res.text();console.error('get_annotations non-JSON:',raw);noteList.innerHTML='<div class="fv-empty-msg">Server error — check console.</div>';return;}
      const data=await res.json();
      if(data.status==='success')renderNotes(data.annotations);
      else noteList.innerHTML=`<div class="fv-empty-msg">${esc(data.message||'Could not load.')}</div>`;
    }catch(e){noteList.innerHTML='<div class="fv-empty-msg">Network error.</div>';console.error('loadNotes',e);}
  }
  function renderNotes(items){
    noteTabBadge.textContent=items.length;noteToolBadge.textContent=items.length;
    if(!items.length){noteList.innerHTML=`<div class="fv-empty-msg"><i class="fas fa-sticky-note"></i>No notes on this page yet.<br><small>Only you can see these.</small></div>`;return;}
    noteList.innerHTML=items.map(n=>buildNoteCard(n)).join('');noteList.scrollTop=noteList.scrollHeight;
  }
  function buildNoteCard(n){return`<div class="fv-item-card" id="fvitem_${esc(n.id)}"><div class="fv-item-time">${formatDate(n.created_at)}</div><div class="fv-item-text">${esc(n.note_text)}</div><button class="fv-item-del" onclick="fvDeleteNote('${esc(n.id)}')" title="Delete"><i class="fas fa-trash"></i></button></div>`;}
  window.fvDeleteNote=async function(itemId){
    const r=await Swal.fire({title:'Delete this note?',icon:'warning',showCancelButton:true,confirmButtonColor:'#d93025',confirmButtonText:'Delete',cancelButtonText:'Cancel'});
    if(!r.isConfirmed)return;
    try{const res=await fetch('API/facultyUI/classroom/delete_annotation.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({annotation_id:itemId})});const data=await res.json();if(data.status==='success'){const el=document.getElementById('fvitem_'+itemId);if(el){el.style.opacity='0';el.style.transition='opacity .2s';setTimeout(()=>{el.remove();refreshNoteBadge();},200);}}else toast(data.message||'Failed','error');}catch(e){toast('Network error','error');console.error(e);}
  };
  function refreshNoteBadge(){const count=noteList.querySelectorAll('.fv-item-card').length;noteTabBadge.textContent=count;noteToolBadge.textContent=count;if(!count)noteList.innerHTML=`<div class="fv-empty-msg"><i class="fas fa-sticky-note"></i>No notes on this page yet.</div>`;}
  async function saveNote(){
    if(!_attachId){toast('Cannot save note for this file.','error');return;}
    const text=noteInput.value.trim();if(!text)return;
    noteSend.disabled=true;noteSend.innerHTML='<i class="fas fa-spinner fa-spin"></i>';
    try{
      const payload={attach_id:_attachId,note_text:text,tab:'note'};if(_curPageNum!==null)payload.page_number=_curPageNum;
      const res=await fetch('API/facultyUI/classroom/save_annotation.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
      const ct=res.headers.get('content-type')||'';
      if(!ct.includes('application/json')){const raw=await res.text();console.error('save_annotation non-JSON:',raw);toast('Server error — check console.','error');return;}
      const data=await res.json();
      if(data.status==='success'){noteInput.value='';noteInput.style.height='';const empty=noteList.querySelector('.fv-empty-msg');if(empty)empty.remove();noteList.insertAdjacentHTML('beforeend',buildNoteCard(data.annotation));noteList.scrollTop=noteList.scrollHeight;refreshNoteBadge();}
      else toast(data.message||'Failed to save note.','error');
    }catch(e){toast('Network error: '+e.message,'error');console.error('saveNote',e);}
    finally{noteSend.disabled=false;noteSend.innerHTML='<i class="fas fa-check"></i>';}
  }
  noteSend.addEventListener('click',saveNote);
  window.fvNoteKey=function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();saveNote();}};
  window.fvTaResize=function(el){el.style.height='auto';el.style.height=Math.min(el.scrollHeight,88)+'px';};
  function goToPage(n){if(!pdfDoc)return;curPage=n;_curPageNum=n;renderPage(getCanvas(),curPage);updatePageStrip();loadNotes();}
  prevBtn.addEventListener('click',()=>{if(curPage>1)goToPage(curPage-1);});
  nextBtn.addEventListener('click',()=>{if(curPage<totalPages)goToPage(curPage+1);});
  function loadPdf(url){
    const go=()=>{
      window.pdfjsLib.GlobalWorkerOptions.workerSrc=PDFJS_WORKER;
      const scroll=document.createElement('div');scroll.className='fv-scroll';
      const canvas=document.createElement('canvas');scroll.appendChild(canvas);bodyEl.appendChild(scroll);setControls(true);
      window.pdfjsLib.getDocument(url).promise.then(doc=>{pdfDoc=doc;totalPages=doc.numPages;_curPageNum=1;updatePageStrip();renderPage(canvas,curPage);loadNotes();}).catch(()=>showNoPreview(url,'PDF'));
    };
    if(window.pdfjsLib){go();return;}
    const s=document.createElement('script');s.src=PDFJS_LIB;s.onload=go;s.onerror=()=>showNoPreview(url,'PDF');document.head.appendChild(s);
  }
  function renderPage(canvas,num){
    if(rendering||!pdfDoc)return;rendering=true;showLoad();
    pdfDoc.getPage(num).then(page=>{const vp=page.getViewport({scale});const ctx=canvas.getContext('2d');canvas.width=vp.width;canvas.height=vp.height;page.render({canvasContext:ctx,viewport:vp}).promise.then(()=>{rendering=false;hideLoad();updatePdfNav();}).catch(()=>{rendering=false;hideLoad();});});
  }
  function getCanvas(){return bodyEl.querySelector('canvas');}
  function updatePdfNav(){pageInfo.textContent=`${curPage} / ${totalPages}`;prevBtn.disabled=curPage<=1;nextBtn.disabled=curPage>=totalPages;zoomLabel.textContent=Math.round((scale/1.5)*100)+'%';}
  zoomInBtn.addEventListener('click',()=>{if(scale<5){scale=Math.min(5,+(scale+.25).toFixed(2));renderPage(getCanvas(),curPage);}});
  zoomOutBtn.addEventListener('click',()=>{if(scale>.4){scale=Math.max(.4,+(scale-.25).toFixed(2));renderPage(getCanvas(),curPage);}});
  document.addEventListener('keydown',e=>{
    if(!backdrop.classList.contains('show'))return;
    if(e.key==='Escape')closeViewer();
    if(e.key==='ArrowRight'&&pdfDoc&&curPage<totalPages)goToPage(curPage+1);
    if(e.key==='ArrowLeft'&&pdfDoc&&curPage>1)goToPage(curPage-1);
    if(e.key==='+'&&pdfDoc)zoomInBtn.click();
    if(e.key==='-'&&pdfDoc)zoomOutBtn.click();
  });
  const EXT_IMG  =new Set(['jpg','jpeg','png','gif','webp','svg','bmp','ico','avif']);
  const EXT_VIDEO=new Set(['mp4','webm','ogg','mov','mkv','avi']);
  const EXT_AUDIO=new Set(['mp3','wav','flac','aac','m4a']);
  const EXT_TEXT =new Set(['txt','csv','json','xml','css','js','ts','py','java','c','cpp','h','md','yaml','yml','ini','log','sh','sql']);
  const EXT_OFFICE=new Set(['doc','docx','ppt','pptx','xls','xlsx','odt','ods','odp']);
  function getExt(name){return(name||'').split('.').pop().toLowerCase();}
  function showImage(url){const wrap=document.createElement('div');wrap.className='fv-img-wrap';const img=document.createElement('img');img.alt='';img.onload=hideLoad;img.onerror=()=>showNoPreview(url,'Image');img.src=url;wrap.appendChild(img);bodyEl.appendChild(wrap);}
  function showVideo(url,mime){const wrap=document.createElement('div');wrap.className='fv-media-wrap';const video=document.createElement('video');video.controls=true;video.autoplay=false;const src=document.createElement('source');src.src=url;src.type=mime;video.appendChild(src);video.oncanplay=hideLoad;video.onerror=()=>showNoPreview(url,'Video');wrap.appendChild(video);bodyEl.appendChild(wrap);}
  function showAudio(url,mime){const wrap=document.createElement('div');wrap.className='fv-media-wrap';const audio=document.createElement('audio');audio.controls=true;const src=document.createElement('source');src.src=url;src.type=mime;audio.appendChild(src);audio.oncanplay=hideLoad;audio.onerror=()=>showNoPreview(url,'Audio');wrap.appendChild(audio);bodyEl.appendChild(wrap);hideLoad();}
  function showText(url){fetch(url).then(r=>r.text()).then(txt=>{const pre=document.createElement('div');pre.className='fv-text-wrap';pre.textContent=txt;bodyEl.appendChild(pre);hideLoad();}).catch(()=>showNoPreview(url,'Text file'));}
  function showOffice(url){
    hideLoad();

    // Build a reliable absolute file URL from current app path (no hardcoded folder names).
    let absUrl='';
    try{
      absUrl=new URL(url, window.location.origin + window.location.pathname).href;
    }catch(_){
      absUrl=url;
    }

    const div=document.createElement('div');
    div.className='fv-no-preview';
    div.innerHTML=''
      + '<div class="fv-np-icon"><i class="fas fa-file-arrow-down"></i></div>'
      + '<h3>Preview not available for this file</h3>'
      + '<p>Open or download the file to view it.</p>'
      + '<div style="display:flex;gap:.6rem;justify-content:center;flex-wrap:wrap;">'
      + '<a href="'+esc(absUrl)+'" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> Open file</a>'
      + '<a href="'+esc(absUrl)+'" target="_blank" download><i class="fas fa-download"></i> Download file</a>'
      + '</div>';
    bodyEl.appendChild(div);
  }
  function showNoPreview(url,label){
    hideLoad();const div=document.createElement('div');div.className='fv-no-preview';
    div.innerHTML=`<div class="fv-np-icon"><i class="fas fa-file-slash"></i></div><h3>Preview not available</h3><p>${esc(label)} files cannot be previewed directly.</p><div style="display:flex;gap:.6rem;justify-content:center;flex-wrap:wrap;"><a href="${esc(url)}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> Open file</a><a href="${esc(url)}" target="_blank" download><i class="fas fa-download"></i> Download file</a></div>`;
    bodyEl.appendChild(div);
  }
  function openViewer(url,name,mime,attachId){
    pdfDoc=null;curPage=1;totalPages=1;scale=1.5;rendering=false;
    _attachId=(attachId&&String(attachId).trim()!=='')?attachId:null;_curPageNum=null;
    clearBody();showLoad();setControls(false);
    filenameLbl.textContent=name||'File';downloadBtn.href=url;
    badgeLbl.textContent=(getExt(name)||'file').toUpperCase();
    backdrop.classList.add('show');document.body.style.overflow='hidden';
    sidePanel.classList.remove('collapsed');_panelOpen=true;
    noteInput.value='';noteTabBadge.textContent='0';noteToolBadge.textContent='0';
    updatePageStrip();
    const ext=getExt(name);
    if(ext==='pdf'||(mime&&mime.includes('pdf'))){loadPdf(url);return;}
    if(EXT_IMG.has(ext)||(mime&&mime.startsWith('image/'))){showImage(url);}
    else if(EXT_VIDEO.has(ext)||(mime&&mime.startsWith('video/'))){showVideo(url,mime||'video/mp4');}
    else if(EXT_AUDIO.has(ext)||(mime&&mime.startsWith('audio/'))){showAudio(url,mime||'audio/mpeg');}
    else if(EXT_TEXT.has(ext)){showText(url);}
    else if(EXT_OFFICE.has(ext)){showOffice(url);}
    else{showNoPreview(url,name);}
    loadNotes();
  }
  window.openFileViewer=openViewer;
  function closeViewer(){backdrop.classList.remove('show');document.body.style.overflow='';clearBody();pdfDoc=null;setControls(false);_attachId=null;_curPageNum=null;const media=bodyEl.querySelector('video,audio');if(media){media.pause();media.src='';}}
  closeBtn.addEventListener('click',closeViewer);
  backdrop.addEventListener('click',e=>{if(e.target===backdrop)closeViewer();});
})();

/* ════ HELPERS ════ */
function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function fmt12(t){if(!t)return'';const[h,m]=t.split(':').map(Number);return`${h%12||12}:${String(m).padStart(2,'0')} ${h>=12?'PM':'AM'}`;}
function parseLocalDate(dtStr){
  if(!dtStr) return null;
  const s = String(dtStr).trim().replace(' ', 'T');
  const m = s.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::(\d{2}))?/);
  if (m) {
    const y = Number(m[1]), mo = Number(m[2]) - 1, d = Number(m[3]), h = Number(m[4]), mi = Number(m[5]), se = Number(m[6] || 0);
    return new Date(y, mo, d, h, mi, se);
  }
  const d = new Date(s);
  return isNaN(d) ? null : d;
}
function formatDate(dtStr){
  if(!dtStr) return '';
  const d = parseLocalDate(dtStr);
  if(!d) return '';
  const now = new Date();
  const diff = Math.max(0, (now - d) / 1000);
  if(diff < 30) return 'just now';
  if(diff < 90) return '1m ago';
  if(diff < 3600) {
    const mins = Math.floor(diff / 60);
    return `${mins}m ago`;
  }
  if(diff < 5400) return '1h ago';
  if(diff < 86400) {
    const hrs = Math.floor(diff / 3600);
    return `${hrs}h ago`;
  }
  if(diff < 172800) return '1d ago';
  if(diff < 604800) {
    const days = Math.floor(diff / 86400);
    return `${days}d ago`;
  }
  if(diff < 2592000) {
    const weeks = Math.floor(diff / 604800);
    return `${weeks}w ago`;
  }
  if(diff < 31536000) {
    const months = Math.floor(diff / 2592000);
    return `${months}mo ago`;
  }
  const years = Math.floor(diff / 31536000);
  if (years >= 1) return `${years}y ago`;
  return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
}
function mimeIcon(mime){if(!mime||mime==='0')return'fa-file';if(mime.includes('pdf'))return'fa-file-pdf';if(mime.includes('image'))return'fa-file-image';if(mime.includes('word'))return'fa-file-word';if(mime.includes('sheet')||mime.includes('excel'))return'fa-file-excel';if(mime.includes('presentation')||mime.includes('powerpoint'))return'fa-file-powerpoint';if(mime.includes('text'))return'fa-file-alt';return'fa-file';}
function mimeIconFromName(name){const ext=(name.split('.').pop()||'').toLowerCase();const map={pdf:'fa-file-pdf',jpg:'fa-file-image',jpeg:'fa-file-image',png:'fa-file-image',gif:'fa-file-image',webp:'fa-file-image',doc:'fa-file-word',docx:'fa-file-word',xls:'fa-file-excel',xlsx:'fa-file-excel',ppt:'fa-file-powerpoint',pptx:'fa-file-powerpoint',txt:'fa-file-alt'};return`fas ${map[ext]||'fa-file'}`;}
async function copyText(text,msg){try{await navigator.clipboard.writeText(text);toast(msg);}catch{toast('Copy failed — please copy manually','warning');}}
</script>
<script>
  /* ══ CLASSROOM MEET SIDEBAR ══ */
let _classroomMeet = null;

/* ── Open / close modal ── */
function openMeetModal() {
  const input      = document.getElementById('meetLinkInput');
  const clearBtn   = document.getElementById('meetClearBtn');
  const preview    = document.getElementById('meetLinkPreview');
  const previewTxt = document.getElementById('meetLinkPreviewText');

  if (_classroomMeet && _classroomMeet.meet_url) {
    input.value          = _classroomMeet.meet_url;
    previewTxt.textContent = _classroomMeet.meet_url;
    preview.style.display  = 'flex';
    clearBtn.style.display = 'inline-flex';
  } else {
    input.value            = '';
    preview.style.display  = 'none';
    clearBtn.style.display = 'none';
  }

  document.getElementById('meetModalBack').classList.add('show');
  setTimeout(() => input.select(), 120);
}

function closeMeetModal() {
  document.getElementById('meetModalBack').classList.remove('show');
}

/* Close on backdrop click */
document.getElementById('meetModalBack').addEventListener('click', e => {
  if (e.target === document.getElementById('meetModalBack')) closeMeetModal();
});

/* Live preview */
document.getElementById('meetLinkInput').addEventListener('input', function () {
  const val        = this.value.trim();
  const preview    = document.getElementById('meetLinkPreview');
  const previewTxt = document.getElementById('meetLinkPreviewText');
  if (val) {
    previewTxt.textContent = val;
    preview.style.display  = 'flex';
  } else {
    preview.style.display  = 'none';
  }
});

/* ── Save link ── */
async function saveMeetLink() {
  const raw = document.getElementById('meetLinkInput').value.trim();
  if (!raw) { toast('Please paste a Meet link.', 'error'); return; }
  if (!raw.startsWith('https://meet.google.com/')) {
    toast('Use a valid Google Meet link — https://meet.google.com/…', 'error');
    return;
  }

  const btn = document.getElementById('meetSaveBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spin"></span> Saving…';

  try {
    const res  = await fetch('API/facultyUI/create_meeting.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ class_id: CLASS_ID, meet_url: raw })
    });
    const data = await res.json();
    if (data.status === 'success') {
      _classroomMeet = data;
      applyClassroomMeet(data);
      closeMeetModal();
      toast('Meet link saved!');
    } else {
      toast(data.message || 'Failed to save', 'error');
    }
  } catch {
    toast('Network error', 'error');
  } finally {
    btn.disabled  = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Save Link';
  }
}

/* ── Remove link ── */
async function clearMeetLink() {
  const r = await Swal.fire({
    title: 'Remove Meet link?',
    text: 'Students will no longer see the Join Meet button.',
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#d93025', confirmButtonText: 'Yes, remove',
    cancelButtonText: 'Cancel'
  });
  if (!r.isConfirmed) return;

  try {
    const res  = await fetch('API/facultyUI/create_meeting.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ class_id: CLASS_ID, meet_url: '' })
    });
    const data = await res.json();
    if (data.status === 'success') {
      _classroomMeet = null;
      showMeetSideEmpty();
      closeMeetModal();
      toast('Meet link removed.');
    } else {
      toast(data.message || 'Failed', 'error');
    }
  } catch {
    toast('Network error', 'error');
  }
}

/* ── Sidebar state helpers ── */
function applyClassroomMeet(meet) {
  document.getElementById('meetSideLoading').style.display = 'none';
  document.getElementById('meetSideError').style.display   = 'none';
  document.getElementById('meetSideEmpty').style.display   = 'none';
  document.getElementById('meetSideReady').style.display   = 'block';
  document.getElementById('meetSideLink').href             = meet.meet_url;
  document.getElementById('meetSideCodeDisplay').textContent = meet.meet_url;
  document.getElementById('meetSideCopyLink').onclick =
    () => copyText(meet.meet_url, 'Meet link copied!');
}

function showMeetSideEmpty() {
  document.getElementById('meetSideLoading').style.display = 'none';
  document.getElementById('meetSideReady').style.display   = 'none';
  document.getElementById('meetSideError').style.display   = 'none';
  document.getElementById('meetSideEmpty').style.display   = 'block';
}

function showMeetSideError() {
  document.getElementById('meetSideLoading').style.display = 'none';
  document.getElementById('meetSideReady').style.display   = 'none';
  document.getElementById('meetSideEmpty').style.display   = 'none';
  document.getElementById('meetSideError').style.display   = 'block';
}

/* ── Load on page init (called from loadClassroom()) ── */
async function loadClassroomMeet() {
  try {
    const res  = await fetch(
      `API/facultyUI/get_meeting.php?class_id=${encodeURIComponent(CLASS_ID)}`
    );
    const data = await res.json();
    if (data.status === 'success' && data.meet_url) {
      _classroomMeet = data;
      applyClassroomMeet(data);
    } else {
      showMeetSideEmpty();
    }
  } catch {
    showMeetSideError();
  }
}

document.getElementById('meetEditBtn').addEventListener('click', openMeetModal);


/* ── State ── */
let submissionMode   = 'individual'; // 'individual' | 'group'
let groupBuilderData = [];           // [{ group_number, students:[{student_id,display_name}] }]
let unassignedPool   = [];           // students not yet in any group
let allEnrolledForGrouping = [];     // full enrolled list for the current class
let classGroupsData  = [];           // persistent class groups
let draggedMember    = null;         // { student_id, display_name, fromGroup } — null means unassigned
 
function renderSubModeRow() {
  document.getElementById('pmSubModeRow')?.remove();
 
  const t = postTypes.find(x => x.id == activePmTypeId);
  // Do not show Submission Mode for quiz types.
  if (!t || !t.is_gradable || !!t.has_quiz) return;
 
  const area = document.getElementById('gcFormArea');
  const row  = document.createElement('div');
  row.id     = 'pmSubModeRow';
  row.innerHTML = `
    <div class="gc-field" style="margin-bottom:.75rem;">
      <label class="gc-label">
        <i class="fas fa-users" style="margin-right:.3rem;"></i>Submission Mode
      </label>
      <div style="display:flex;gap:.5rem;margin-top:.35rem;">
        <button type="button" id="smBtnIndividual" onclick="setSubMode('individual')"
          style="display:inline-flex;align-items:center;gap:.45rem;
                 padding:.5rem 1.1rem;border-radius:20px;border:2px solid;
                 font-size:.84rem;font-weight:600;font-family:inherit;cursor:pointer;
                 transition:all .2s;
                 ${submissionMode==='individual'
                   ? 'background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 2px 8px rgba(26,158,120,.28);'
                   : 'background:var(--bg);color:var(--text-muted);border-color:var(--border);'}">
          <i class="fas fa-user" style="font-size:.78rem;"></i> Individual
        </button>
        <button type="button" id="smBtnGroup" onclick="setSubMode('group')"
          style="display:inline-flex;align-items:center;gap:.45rem;
                 padding:.5rem 1.1rem;border-radius:20px;border:2px solid;
                 font-size:.84rem;font-weight:600;font-family:inherit;cursor:pointer;
                 transition:all .2s;
                 ${submissionMode==='group'
                   ? 'background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 2px 8px rgba(26,158,120,.28);'
                   : 'background:var(--bg);color:var(--text-muted);border-color:var(--border);'}">
          <i class="fas fa-layer-group" style="font-size:.78rem;"></i> By Group
        </button>
      </div>
      <div id="pmGroupBuilder" style="display:none;"></div>
    </div>`;
 
  const titleField = document.getElementById('gcTitleField');
  if (titleField && titleField.nextSibling) {
    area.insertBefore(row, titleField.nextSibling);
  } else {
    area.prepend(row);
  }
 
  if (submissionMode === 'group') renderGroupBuilder();
}
 
function setSubMode(mode) {
  submissionMode = mode;

  const btnInd = document.getElementById('smBtnIndividual');
  const btnGrp = document.getElementById('smBtnGroup');

  const activeStyle = 'background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 2px 8px rgba(26,158,120,.28);';
  const inactiveStyle = 'background:var(--bg);color:var(--text-muted);border-color:var(--border);box-shadow:none;';

  // Keep the shared inline styles and just swap the state part
  const base = 'display:inline-flex;align-items:center;gap:.45rem;padding:.5rem 1.1rem;border-radius:20px;border:2px solid;font-size:.84rem;font-weight:600;font-family:inherit;cursor:pointer;transition:all .2s;';

  if (btnInd) btnInd.style.cssText = base + (mode === 'individual' ? activeStyle : inactiveStyle);
  if (btnGrp) btnGrp.style.cssText = base + (mode === 'group'      ? activeStyle : inactiveStyle);

  if (mode === 'group') {
    renderGroupBuilder();
  } else {
    document.getElementById('pmGroupBuilder').innerHTML = '';
  }
}
 
/* ── Group Builder ── */
async function renderGroupBuilder() {
  const el = document.getElementById('pmGroupBuilder');
  if (!el) return;
 
  el.innerHTML = `<div style="margin-top:.6rem;padding:.6rem .85rem;background:var(--primary-light);border:1.5px solid var(--primary);border-radius:10px;font-size:.82rem;color:var(--primary);display:flex;align-items:center;gap:.5rem;">
    <i class="fas fa-layer-group"></i>
    This activity will be submitted <strong>by group</strong> using your saved Class Groups.
    ${classGroupsData.length === 0 ? '<span style="color:var(--warning);margin-left:.25rem;"><i class="fas fa-exclamation-triangle"></i> No class groups set yet — create them first before using group submission.</span>' : ''}
  </div>`;
 
  // Load enrolled students if not already loaded
  if (!allEnrolledForGrouping.length) {
    document.getElementById('gbLoadHint').textContent = 'Loading students…';
    await loadEnrolledForGrouping();
    document.getElementById('gbLoadHint').textContent = '';
  }
 
  // If no groups generated yet, auto-generate
  if (!groupBuilderData.length) {
    generateGroups();
  } else {
    renderGroupColumns();
  }
}
 
async function loadEnrolledForGrouping() {
  try {
    const res  = await fetch(`API/facultyUI/classroom/get_classroom.php?class_id=${encodeURIComponent(CLASS_ID)}`);
    const data = await res.json();
    if (data.status === 'success') {
      allEnrolledForGrouping = (data.people || []).map(s => ({
        student_id:   s.id,
        display_name: formatNameLastFirst(s.full_name)
      }));
    }
  } catch (e) { console.error('loadEnrolledForGrouping', e); }
}
 
function formatNameLastFirst(full) {
  // Convert "John Renwel R. Lucero" → "LUCERO, John R."
  const parts = full.trim().split(/\s+/);
  if (parts.length < 2) return full.toUpperCase();
  const last  = parts[parts.length - 1].toUpperCase();
  const first = parts[0];
  const mid   = parts.length > 2 ? ' ' + parts[1][0] + '.' : '';
  return `${last}, ${first}${mid}`;
}
 
function generateGroups() {
  const numGroups = Math.max(1, parseInt(document.getElementById('gbNumGroups')?.value || 2, 10));
  const students  = [...allEnrolledForGrouping];
 
  // Split enrolled students evenly
  groupBuilderData = Array.from({ length: numGroups }, (_, i) => ({
    group_number: i + 1,
    students: []
  }));
  unassignedPool = [];
 
  students.forEach((s, i) => {
    const gIdx = i % numGroups;
    groupBuilderData[gIdx].students.push(s);
  });
 
  renderGroupColumns();
}
 
function shuffleGroups() {
  // Collect all students (from groups + unassigned)
  const all = [
    ...groupBuilderData.flatMap(g => g.students),
    ...unassignedPool
  ];
  // Fisher-Yates shuffle
  for (let i = all.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [all[i], all[j]] = [all[j], all[i]];
  }
  unassignedPool = [];
  const numGroups = groupBuilderData.length;
  groupBuilderData.forEach((g, i) => { g.students = []; });
  all.forEach((s, i) => {
    groupBuilderData[i % numGroups].students.push(s);
  });
  renderGroupColumns();
}
 
function loadClassGroups() {
  if (!classGroupsData.length) return;
  unassignedPool = [];
  groupBuilderData = classGroupsData.map(g => ({
    group_number: g.group_number,
    students: g.students.map(s => ({
      student_id:   s.student_id,
      display_name: s.display_name
    }))
  }));
  // Students enrolled but not in class groups go to unassigned
  const assigned = new Set(groupBuilderData.flatMap(g => g.students.map(s => s.student_id)));
  unassignedPool = allEnrolledForGrouping.filter(s => !assigned.has(s.student_id));
  renderGroupColumns();
}
 
function renderGroupColumns() {
  const wrap = document.getElementById('gbGroupColumns');
  if (!wrap) return;
 
  let html = '';
 
  // Unassigned pool (shown if any)
  if (unassignedPool.length) {
    html += `<div class="gb-unassigned" id="gbUnassigned"
               ondragover="gbDragOver(event,'unassigned')"
               ondrop="gbDrop(event,'unassigned')"
               ondragleave="gbDragLeave(event)">
      <div class="gb-unassigned-title">
        <i class="fas fa-exclamation-triangle"></i> Unassigned (${unassignedPool.length})
      </div>
      <div class="gb-member-list" id="gbMemberList_unassigned">
        ${unassignedPool.map(s => buildMemberChip(s, 'unassigned')).join('')}
      </div>
    </div>`;
  }
 
  html += `<div class="gb-columns">`;
  groupBuilderData.forEach(g => {
    html += `
      <div class="gb-group-card" id="gbGroup_${g.group_number}"
           ondragover="gbDragOver(event,${g.group_number})"
           ondrop="gbDrop(event,${g.group_number})"
           ondragleave="gbDragLeave(event)">
        <div class="gb-group-head">
          <div class="gb-group-title">
            <i class="fas fa-users" style="font-size:.65rem;"></i>
            Group ${g.group_number}
          </div>
          <span class="gb-group-count">${g.students.length} member${g.students.length !== 1 ? 's' : ''}</span>
        </div>
        <div class="gb-member-list" id="gbMemberList_${g.group_number}">
          ${g.students.map(s => buildMemberChip(s, g.group_number)).join('')}
        </div>
      </div>`;
  });
  html += `</div>`;
 
  wrap.innerHTML = html;
}
 
function buildMemberChip(student, fromGroup) {
  return `<div class="gb-member" draggable="true"
              data-sid="${esc(student.student_id)}"
              data-name="${esc(student.display_name)}"
              data-from="${fromGroup}"
              ondragstart="gbDragStart(event)"
              ondragend="gbDragEnd(event)">
    <i class="fas fa-grip-vertical gb-member-drag-icon"></i>
    <span class="gb-member-name" title="${esc(student.display_name)}">${esc(student.display_name)}</span>
    <button type="button" class="gb-member-move" title="Move to unassigned"
            onclick="gbMoveToUnassigned('${esc(student.student_id)}',${JSON.stringify(fromGroup)})">
      <i class="fas fa-times"></i>
    </button>
  </div>`;
}
 
/* ── Drag & Drop ── */
function gbDragStart(e) {
  const el = e.currentTarget;
  draggedMember = {
    student_id:   el.dataset.sid,
    display_name: el.dataset.name,
    fromGroup:    el.dataset.from === 'unassigned' ? 'unassigned' : parseInt(el.dataset.from, 10)
  };
  el.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';
}
function gbDragEnd(e)       { e.currentTarget.classList.remove('dragging'); draggedMember = null; }
function gbDragOver(e, gn)  { e.preventDefault(); e.dataTransfer.dropEffect = 'move';
  document.getElementById(gn === 'unassigned' ? 'gbUnassigned' : `gbGroup_${gn}`)?.classList.add('drag-over'); }
function gbDragLeave(e)     { e.currentTarget.classList.remove('drag-over'); }
 
function gbDrop(e, targetGroup) {
  e.preventDefault();
  e.currentTarget.classList.remove('drag-over');
  if (!draggedMember) return;
 
  const { student_id, display_name, fromGroup } = draggedMember;
  if (fromGroup === targetGroup) return;
 
  // Remove from source
  if (fromGroup === 'unassigned') {
    unassignedPool = unassignedPool.filter(s => s.student_id !== student_id);
  } else {
    const srcGrp = groupBuilderData.find(g => g.group_number === fromGroup);
    if (srcGrp) srcGrp.students = srcGrp.students.filter(s => s.student_id !== student_id);
  }
 
  // Add to target
  const student = { student_id, display_name };
  if (targetGroup === 'unassigned') {
    unassignedPool.push(student);
  } else {
    const tgtGrp = groupBuilderData.find(g => g.group_number === targetGroup);
    if (tgtGrp) tgtGrp.students.push(student);
  }
 
  renderGroupColumns();
}
 
function gbMoveToUnassigned(sid, fromGroup) {
  let student;
  if (fromGroup === 'unassigned') return;
  const grp = groupBuilderData.find(g => g.group_number === fromGroup);
  if (grp) {
    student = grp.students.find(s => s.student_id === sid);
    grp.students = grp.students.filter(s => s.student_id !== sid);
  }
  if (student) unassignedPool.push(student);
  renderGroupColumns();
}
 
/* ── Group popover on card ── */
let _popoverTimeout = null;
function showGroupChipPopover(chipEl, groupLabel, members) {
  clearTimeout(_popoverTimeout);
  const pop     = document.getElementById('grpPopover');
  const title   = document.getElementById('grpPopTitle');
  const list    = document.getElementById('grpPopList');
  title.textContent = groupLabel;
  list.innerHTML = members.map(m =>
    `<div class="grp-popover-member"><i class="fas fa-user" style="font-size:.62rem;color:var(--primary);"></i>${esc(m)}</div>`
  ).join('') || '<div class="grp-popover-member" style="color:var(--text-muted);font-style:italic;">Solo</div>';
 
  const rect = chipEl.getBoundingClientRect();
  pop.style.display = 'block';
  pop.style.left    = Math.min(rect.left, window.innerWidth - 320) + 'px';
  pop.style.top     = (rect.bottom + 6) + 'px';
}
function closeGrpPopover() {
  _popoverTimeout = setTimeout(() => {
    document.getElementById('grpPopover').style.display = 'none';
  }, 150);
}
document.addEventListener('click', e => {
  if (!e.target.closest('.group-chip') && !e.target.closest('#grpPopover')) {
    document.getElementById('grpPopover').style.display = 'none';
  }
});
 
/* ── Build group chips for post card ── */
function buildGroupChips(groups, submissionMode) {
  if (!groups || (!groups.length && submissionMode === 'individual')) {
    if (submissionMode === 'individual') {
      return `<div class="pc-groups">
        <span class="sub-mode-badge sub-mode-individual"><i class="fas fa-user"></i> Individual</span>
      </div>`;
    }
    return '';
  }
 
  const groupNums = Object.keys(groups).map(Number).sort((a, b) => a - b);
  if (!groupNums.length) return '';
 
  let chips = `<div class="pc-groups">
    <span class="sub-mode-badge sub-mode-group"><i class="fas fa-layer-group"></i> Group</span>`;
 
  groupNums.forEach(gn => {
    const members   = groups[gn] || [];
    const nameList  = members.map(m => esc(m.display_name || '—'));
    const preview   = nameList.slice(0, 2).join(' · ') + (nameList.length > 2 ? ` +${nameList.length - 2}` : '');
    const isSolo    = members.length === 1;
    const chipClass = isSolo ? 'group-chip group-chip-solo' : 'group-chip';
    const icon      = isSolo ? 'fa-user' : 'fa-users';
    // Encode members for the onclick
    const membersJson = JSON.stringify(nameList).replace(/"/g, '&quot;');
 
    chips += `<span class="${chipClass}"
        onclick="showGroupChipPopover(this,'Group ${gn}',JSON.parse(this.dataset.members))"
        data-members="${membersJson}">
      <i class="fas ${icon}"></i> Group ${gn}${isSolo ? ' · solo' : ` · ${members.length}`}
    </span>`;
  });
 
  chips += `</div>`;
  return chips;
}
 
/* ── Class Groups Panel ── */
async function loadClassGroupsPanel() {
  try {
    const res  = await fetch(`API/facultyUI/classroom/get_class_groups.php?class_id=${encodeURIComponent(CLASS_ID)}`);
    const data = await res.json();
    if (data.status === 'success') {
      classGroupsData = [];
      if (data.has_groups) {
        const g = data.groups;
        Object.keys(g).forEach(gn => {
          classGroupsData.push({ group_number: parseInt(gn, 10), students: g[gn] });
        });
      }
      renderClassGroupsDisplay();
    }
  } catch (e) { console.error('loadClassGroupsPanel', e); }
}
 
function renderClassGroupsDisplay() {
  const el = document.getElementById('classGroupsDisplay');
  if (!el) return;
  if (!classGroupsData.length) {
    el.innerHTML = `<div class="cg-empty">
      <div class="cg-empty-icon"><i class="fas fa-layer-group"></i></div>
      <div style="font-weight:600;margin-bottom:.3rem;">No class groups set</div>
      <div style="font-size:.82rem;">Click Edit Groups to create persistent groups for this class.</div>
    </div>`;
    return;
  }
  let html = `<div class="gb-columns">`;
  classGroupsData.forEach(g => {
    html += `<div class="gb-group-card">
      <div class="gb-group-head">
        <div class="gb-group-title"><i class="fas fa-users" style="font-size:.65rem;"></i> Group ${g.group_number}</div>
        <span class="gb-group-count">${g.students.length} member${g.students.length !== 1 ? 's' : ''}</span>
      </div>
      <div class="gb-member-list">
        ${g.students.map(s => `<div class="gb-member" style="cursor:default;">
          <i class="fas fa-user gb-member-drag-icon"></i>
          <span class="gb-member-name">${esc(s.display_name)}</span>
        </div>`).join('')}
      </div>
    </div>`;
  });
  html += `</div>`;
  el.innerHTML = html;
}
 
async function openClassGroupEditor() {
  // Load enrolled students first
  if (!allEnrolledForGrouping.length) await loadEnrolledForGrouping();
 
  // Pre-fill from saved class groups
  if (classGroupsData.length) {
    groupBuilderData = classGroupsData.map(g => ({
      group_number: g.group_number,
      students: g.students.map(s => ({ student_id: s.student_id, display_name: s.display_name }))
    }));
    const assigned = new Set(groupBuilderData.flatMap(g => g.students.map(s => s.student_id)));
    unassignedPool = allEnrolledForGrouping.filter(s => !assigned.has(s.student_id));
  } else {
    groupBuilderData = [];
    unassignedPool   = [...allEnrolledForGrouping];
  }
 
  // Show a Swal modal with the builder embedded
  const numGroups = groupBuilderData.length || 2;
  Swal.fire({
    title: 'Edit Class Groups',
    html: `
      <div style="text-align:left;">
        <div class="gb-controls" style="margin-bottom:.75rem;">
          <span class="gb-label">Groups:</span>
          <input type="number" class="gb-num-input" id="cgNumGroups" min="1" max="50" value="${numGroups}">
          <button type="button" class="gb-btn primary" onclick="cgGenerate()"><i class="fas fa-magic"></i> Generate</button>
          <button type="button" class="gb-btn" onclick="cgShuffle()"><i class="fas fa-random"></i> Shuffle</button>
        </div>
        <div id="cgBuilderWrap"></div>
      </div>`,
    width: 720,
    showCancelButton: true,
    confirmButtonColor: '#1a9e78',
    confirmButtonText: '<i class="fas fa-save"></i> Save Class Groups',
    cancelButtonText: 'Cancel',
    didOpen: () => { cgRenderColumns(); },
    preConfirm: async () => {
      await saveClassGroupsToServer();
      return true;
    }
  });
}
 
function cgGenerate() {
  const n = Math.max(1, parseInt(document.getElementById('cgNumGroups')?.value || 2, 10));
  const all = [...allEnrolledForGrouping];
  groupBuilderData = Array.from({ length: n }, (_, i) => ({ group_number: i + 1, students: [] }));
  unassignedPool   = [];
  all.forEach((s, i) => { groupBuilderData[i % n].students.push(s); });
  cgRenderColumns();
}
 
function cgShuffle() {
  const all = [
    ...groupBuilderData.flatMap(g => g.students),
    ...unassignedPool
  ];
  for (let i = all.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [all[i], all[j]] = [all[j], all[i]];
  }
  unassignedPool = [];
  groupBuilderData.forEach(g => { g.students = []; });
  all.forEach((s, i) => { groupBuilderData[i % groupBuilderData.length].students.push(s); });
  cgRenderColumns();
}
 
function cgRenderColumns() {
  const wrap = document.getElementById('cgBuilderWrap');
  if (!wrap) return;
  // Reuse the same renderGroupColumns() logic but target cgBuilderWrap
  let html = '';
  if (unassignedPool.length) {
    html += `<div class="gb-unassigned" style="margin-bottom:.65rem;">
      <div class="gb-unassigned-title"><i class="fas fa-exclamation-triangle"></i> Unassigned (${unassignedPool.length})</div>
      <div class="gb-member-list">${unassignedPool.map(s =>
        `<div class="gb-member" style="cursor:default;"><i class="fas fa-user gb-member-drag-icon"></i>
        <span class="gb-member-name">${esc(s.display_name)}</span></div>`
      ).join('')}</div>
    </div>`;
  }
  html += `<div class="gb-columns">`;
  groupBuilderData.forEach(g => {
    html += `<div class="gb-group-card">
      <div class="gb-group-head">
        <div class="gb-group-title"><i class="fas fa-users" style="font-size:.65rem;"></i> Group ${g.group_number}</div>
        <span class="gb-group-count">${g.students.length}</span>
      </div>
      <div class="gb-member-list">
        ${g.students.map(s => `<div class="gb-member" style="cursor:default;">
          <i class="fas fa-user gb-member-drag-icon"></i>
          <span class="gb-member-name">${esc(s.display_name)}</span>
        </div>`).join('')}
      </div>
    </div>`;
  });
  html += `</div>`;
  wrap.innerHTML = html;
}
 
async function saveClassGroupsToServer() {
  const groups = groupBuilderData.map(g => ({
    group_number: g.group_number,
    student_ids:  g.students.map(s => s.student_id)
  }));
  try {
    const res  = await fetch('API/facultyUI/classroom/save_class_groups.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ class_id: CLASS_ID, groups })
    });
    const data = await res.json();
    if (data.status === 'success') {
      classGroupsData = groupBuilderData.map(g => ({
        group_number: g.group_number,
        students: g.students.map(s => ({ student_id: s.student_id, display_name: s.display_name }))
      }));
      renderClassGroupsDisplay();
      toast('Class groups saved!');
    } else {
      toast(data.message || 'Failed to save class groups', 'error');
    }
  } catch {
    toast('Network error saving class groups', 'error');
  }
}


/* ══════════════════════════════════════════
   INVITE STUDENTS MODAL
══════════════════════════════════════════ */
let inviteSelected = {}; // { student_id: { id, full_name, student_number } }
let _inviteTimer   = null;

function openInviteModal() {
  inviteSelected = {};
  document.getElementById('inviteSearchInput').value = '';
  renderInviteEmptyView();
  renderInviteSelected();
  document.getElementById('inviteModalBack').classList.add('show');
  setTimeout(() => document.getElementById('inviteSearchInput').focus(), 120);
}

function closeInviteModal() {
  document.getElementById('inviteModalBack').classList.remove('show');
}

document.getElementById('inviteModalBack').addEventListener('click', e => {
  if (e.target === document.getElementById('inviteModalBack')) closeInviteModal();
});

function debounceInviteSearch() {
  clearTimeout(_inviteTimer);
  _inviteTimer = setTimeout(runInviteSearch, 320);
}

function clearInviteSearch() {
  document.getElementById('inviteSearchInput').value = '';
  renderInviteEmptyView();
  document.getElementById('inviteSearchInput').focus();
}

async function runInviteSearch() {
  const q   = document.getElementById('inviteSearchInput').value.trim();
  const el  = document.getElementById('inviteSearchResults');
  if (q.length < 2) {
    renderInviteEmptyView();
    return;
  }
  el.innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.82rem;"><i class="fas fa-spinner fa-spin"></i> Searching…</div>';
  try {
    const res  = await fetch(
      `API/facultyUI/classroom/search_students.php?class_id=${encodeURIComponent(CLASS_ID)}&q=${encodeURIComponent(q)}`
    );
    const data = await res.json();
    if (data.status !== 'success') { el.innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.82rem;">No results.</div>'; return; }
    renderInviteResults(data.students || []);
  } catch {
    el.innerHTML = '<div style="text-align:center;padding:1rem;color:var(--danger);font-size:.82rem;">Search failed. Try again.</div>';
  }
}

function renderInviteResults(students) {
  const el = document.getElementById('inviteSearchResults');
  if (!students.length) {
    el.innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.82rem;">No students found.</div>';
    return;
  }
  el.innerHTML = students.map(s => {
    const alreadySelected = !!inviteSelected[s.id];
    const isEnrolled      = s.in_class || s.enrollment_status === 'enrolled';
    const isInvited       = s.invitation_status === 'pending';
    const isJoinPending   = s.enrollment_status === 'pending'; // student-initiated request
    const blocked         = isEnrolled || isInvited || isJoinPending;

    // Right-side badge / action
    let rightHtml = '';
    if (isEnrolled) {
      rightHtml = `<span style="font-size:.65rem;font-weight:700;padding:.12rem .45rem;border-radius:20px;background:var(--primary-light);color:#1a9e78;border:1px solid #1a9e7844;white-space:nowrap;">Enrolled</span>`;
    } else if (isInvited) {
      rightHtml = `<button type="button" class="js-cancel-invite-pill"
                     data-student-id="${esc(s.id)}"
                     onclick="event.stopPropagation();cancelInvitationFromSearch(this);"
                     style="font-size:.65rem;font-weight:700;padding:.18rem .55rem;border-radius:20px;
                            background:#e6f1fb;color:#0c447c;border:1px solid #85b7eb;cursor:pointer;
                            white-space:nowrap;font-family:inherit;transition:all .15s;"
                     onmouseover="this.style.background='#fdecea';this.style.color='#b91c1c';this.style.borderColor='#f5c2c7';this.querySelector('.lbl').style.display='none';this.querySelector('.lbl-x').style.display='inline';"
                     onmouseout="this.style.background='#e6f1fb';this.style.color='#0c447c';this.style.borderColor='#85b7eb';this.querySelector('.lbl').style.display='inline';this.querySelector('.lbl-x').style.display='none';">
                     <span class="lbl">Invited</span><span class="lbl-x" style="display:none;">✕ Cancel</span>
                   </button>`;
    } else if (isJoinPending) {
      rightHtml = `<span style="font-size:.65rem;font-weight:700;padding:.12rem .45rem;border-radius:20px;background:#fff3e0;color:#f57c00;border:1px solid #f57c0044;white-space:nowrap;">Requested join</span>`;
    }

    return `<div id="inviteRow_${esc(s.id)}" style="display:flex;align-items:center;gap:.75rem;padding:.65rem .9rem;
                        border-bottom:1px solid var(--border);transition:background .15s;
                        ${blocked && !isInvited ? 'opacity:.55;' : ''}${blocked ? '' : 'cursor:pointer;'}
                        ${alreadySelected ? 'background:var(--primary-light);' : ''}"
                 ${blocked ? '' : `onclick="toggleInviteStudent('${esc(s.id)}','${esc(s.full_name)}','${esc(s.student_number)}')"` }
                 onmouseover="${blocked ? '' : "this.style.background='var(--bg)'"}"
                 onmouseout="${blocked ? '' : `this.style.background='${alreadySelected ? 'var(--primary-light)' : ''}'`}">
      <div style="width:32px;height:32px;border-radius:50%;flex-shrink:0;
                  background:${alreadySelected ? 'var(--primary)' : 'linear-gradient(135deg,#667eea,#764ba2)'};
                  display:flex;align-items:center;justify-content:center;
                  color:#fff;font-size:.75rem;font-weight:700;">
        ${alreadySelected ? '<i class="fas fa-check" style="font-size:.7rem;"></i>' : esc(s.full_name.substring(0, 2).toUpperCase())}
      </div>
      <div style="flex:1;min-width:0;">
        <div style="font-weight:600;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(s.full_name)}</div>
        <div style="font-size:.72rem;color:var(--text-muted);">${esc(s.student_number || '')}${s.course_code ? ' · ' + esc(s.course_code) : ''}${s.year_level ? ' · Yr' + esc(String(s.year_level)) : ''}${s.section ? '-' + esc(s.section) : ''}</div>
      </div>
      ${rightHtml}
    </div>`;
  }).join('');
}

function renderInviteEmptyView() {
  const el  = document.getElementById('inviteSearchResults');
  const ids = Object.keys(inviteSelected);

  if (ids.length) {
    // Show currently selected students for quick review
    el.innerHTML = `
      <div style="padding:.55rem .9rem;background:var(--primary-light);border-bottom:1px solid var(--border);
                  font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--primary);">
        <i class="fas fa-check-circle" style="font-size:.72rem;margin-right:.3rem;"></i>
        ${ids.length} student${ids.length > 1 ? 's' : ''} selected
        <span style="float:right;text-transform:none;font-weight:500;font-size:.68rem;color:var(--text-muted);">
          Tap to remove · type to search more
        </span>
      </div>` +
      ids.map(id => {
        const s = inviteSelected[id];
        return `
        <div onclick="toggleInviteStudent('${esc(id)}','${esc(s.full_name)}','${esc(s.student_number||'')}')"
             style="display:flex;align-items:center;gap:.75rem;padding:.65rem .9rem;
                    border-bottom:1px solid var(--border);cursor:pointer;
                    background:var(--primary-light);transition:all .18s;animation:invSlideIn .22s ease;"
             onmouseover="this.style.background='#fdecea';this.querySelector('.inv-act').style.color='#b91c1c';
                          this.querySelector('.inv-av').style.background='#d93025';
                          this.querySelector('.inv-av i').className='fas fa-times';"
             onmouseout="this.style.background='var(--primary-light)';this.querySelector('.inv-act').style.color='var(--primary)';
                          this.querySelector('.inv-av').style.background='var(--primary)';
                          this.querySelector('.inv-av i').className='fas fa-check';">
          <div class="inv-av" style="width:32px;height:32px;border-radius:50%;background:var(--primary);
                      display:flex;align-items:center;justify-content:center;color:#fff;
                      font-size:.72rem;transition:background .18s;">
            <i class="fas fa-check"></i>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:.85rem;color:var(--text);">${esc(s.full_name)}</div>
            <div style="font-size:.72rem;color:var(--text-muted);">${esc(s.student_number||'')}</div>
          </div>
          <span class="inv-act" style="font-size:.7rem;font-weight:700;color:var(--primary);
                  transition:color .18s;white-space:nowrap;">Selected</span>
        </div>`;
      }).join('');
    return;
  }

  // No selections → friendly empty hint + recently invited
  el.innerHTML = `
    <div style="text-align:center;padding:1.6rem 1rem .8rem;color:var(--text-muted);">
      <div style="width:48px;height:48px;border-radius:50%;background:var(--primary-light);
                  display:flex;align-items:center;justify-content:center;margin:0 auto .55rem;
                  color:var(--primary);font-size:1rem;animation:invFloat 2.4s ease-in-out infinite;">
        <i class="fas fa-user-plus"></i>
      </div>
      <div style="font-size:.85rem;font-weight:600;color:var(--text);margin-bottom:.15rem;">
        Find students to invite
      </div>
      <div style="font-size:.74rem;">
        Type a name or student number to search
      </div>
    </div>
    <div id="inviteRecentSlot"></div>`;
  loadRecentInvitations();
}

async function loadRecentInvitations() {
  const slot = document.getElementById('inviteRecentSlot');
  if (!slot) return;
  try {
    const res  = await fetch(`API/facultyUI/classroom/recent_invitations.php?class_id=${encodeURIComponent(CLASS_ID)}`);
    const data = await res.json();
    if (data.status !== 'success' || !data.students || !data.students.length) return;

    slot.innerHTML = `
      <div style="padding:.5rem .9rem;background:var(--bg);border-top:1px solid var(--border);
                  border-bottom:1px solid var(--border);font-size:.68rem;font-weight:700;
                  text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);">
        <i class="fas fa-history" style="margin-right:.35rem;"></i> Recently invited
      </div>` +
      data.students.map(s => `
        <div onclick="toggleInviteStudent('${esc(s.id)}','${esc(s.full_name)}','${esc(s.student_number||'')}')"
             style="display:flex;align-items:center;gap:.75rem;padding:.6rem .9rem;
                    border-bottom:1px solid var(--border);cursor:pointer;
                    transition:background .15s;animation:invSlideIn .22s ease;"
             onmouseover="this.style.background='var(--primary-light)';"
             onmouseout="this.style.background='';">
          <div style="width:32px;height:32px;border-radius:50%;flex-shrink:0;
                      background:linear-gradient(135deg,#667eea,#764ba2);
                      display:flex;align-items:center;justify-content:center;
                      color:#fff;font-size:.72rem;font-weight:700;">
            ${esc(s.full_name.substring(0,2).toUpperCase())}
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:.83rem;color:var(--text);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(s.full_name)}</div>
            <div style="font-size:.7rem;color:var(--text-muted);">${esc(s.student_number||'')}${s.course_code ? ' · ' + esc(s.course_code) : ''}</div>
          </div>
          <span style="font-size:.65rem;font-weight:600;padding:.15rem .5rem;border-radius:20px;
                       background:var(--accent-light);color:var(--accent);white-space:nowrap;">
            <i class="fas fa-plus" style="font-size:.6rem;"></i> Invite
          </span>
        </div>`).join('');
  } catch { /* silent fail — section just doesn't appear */ }
}

async function cancelInvitationFromSearch(btn) {
  const sid = btn.dataset.studentId;
  const ok = await Swal.fire({
    title: 'Cancel invitation?',
    text: 'You can invite this student again afterwards.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, cancel it',
    cancelButtonText: 'Keep invitation',
    confirmButtonColor: '#d93025'
  }).then(r => r.isConfirmed);
  if (!ok) return;

  btn.disabled = true;
  try {
    const res  = await fetch('API/facultyUI/classroom/cancel_invitation.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ class_id: CLASS_ID, student_id: sid })
    });
    const data = await res.json();
    if (data.status !== 'success') throw new Error(data.message || 'Failed');

    // Re-run the current search so the row flips back to "Invite"
    runInviteSearch();
    // Refresh the People tab so the Pending Invitations list updates
    if (typeof loadClassroom === 'function') loadClassroom();
    else if (typeof fetchClassroom === 'function') fetchClassroom();
  } catch (err) {
    Swal.fire('Error', err.message, 'error');
    btn.disabled = false;
  }
}

function toggleInviteStudent(id, name, stuNo) {
  if (inviteSelected[id]) {
    delete inviteSelected[id];
  } else {
    inviteSelected[id] = { id, full_name: name, student_number: stuNo };
  }
  renderInviteSelected();
  // Re-render to update checkmarks / selected list
  const q = document.getElementById('inviteSearchInput').value.trim();
  if (q.length >= 2) runInviteSearch();
  else renderInviteEmptyView();
}

function renderInviteSelected() {
  const ids    = Object.keys(inviteSelected);
  const wrap   = document.getElementById('inviteSelectedWrap');
  const chips  = document.getElementById('inviteSelectedChips');
  const sendBtn = document.getElementById('inviteSendBtn');
  document.getElementById('inviteSelectedCount').textContent = ids.length;
  wrap.style.display = 'none';   // hidden — selections show inside the result list
  sendBtn.disabled   = ids.length === 0;
  if (sendBtn && ids.length) sendBtn.innerHTML = `<i class="fas fa-paper-plane"></i> Send ${ids.length} Invitation${ids.length > 1 ? 's' : ''}`;
  else if (sendBtn) sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Invitations';
  chips.innerHTML = ids.map(id => {
    const s = inviteSelected[id];
    return `<div style="display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .6rem .25rem .5rem;
                        border-radius:20px;background:var(--primary-light);border:1.5px solid var(--primary);
                        font-size:.75rem;font-weight:600;color:var(--primary);">
      <i class="fas fa-user" style="font-size:.62rem;"></i>
      ${esc(s.full_name)}
      <button onclick="toggleInviteStudent('${esc(id)}','${esc(s.full_name)}','${esc(s.student_number)}')"
        style="width:16px;height:16px;border:none;background:none;cursor:pointer;color:var(--primary);
               font-size:.65rem;border-radius:50%;display:flex;align-items:center;justify-content:center;
               padding:0;margin-left:.1rem;" title="Remove">
        <i class="fas fa-times"></i>
      </button>
    </div>`;
  }).join('');
}

async function sendInvitations() {
  const ids = Object.keys(inviteSelected);
  if (!ids.length) return;
  const btn = document.getElementById('inviteSendBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spin"></span> Sending…';
  try {
    const res  = await fetch('API/facultyUI/classroom/invite_students.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ class_id: CLASS_ID, student_ids: ids })
    });
    const data = await res.json();
    if (data.status === 'success') {
      toast(data.message);
      closeInviteModal();
      await loadClassroom(); // Refresh people list
    } else {
      toast(data.message || 'Failed to send invitations', 'error');
    }
  } catch {
    toast('Network error', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Invitations';
  }
}





/* Quick-select a weekday name → compute the correct upcoming date */
function quickSelectDay(targetDow) {
  // targetDow: 0=Sun,1=Mon,...,6=Sat
  const today    = new Date();
  today.setHours(0,0,0,0);
  const todayDow = today.getDay();

  let daysAhead = targetDow - todayDow;

  // Same weekday as today OR already passed this week → push to NEXT week
  if (daysAhead <= 0) daysAhead += 7;

  const target = new Date(today);
  target.setDate(today.getDate() + daysAhead);

  _ddYear    = target.getFullYear();
  _ddMonth   = target.getMonth();
  _ddDay     = target.getDate();
  _ddQuickDay = targetDow;

  // Highlight quick button
  document.querySelectorAll('.dd-quick-btn').forEach((b, i) => {
    b.classList.toggle('active', i === targetDow);
  });

  renderDueDateCalendar();
  dueDateUpdateTime();

  // Close picker after short delay
  setTimeout(() => closeDueDateModal(), 280);
}

function dueDateNavMonth(dir) {
  _ddMonth += dir;
  if (_ddMonth > 11) { _ddMonth = 0; _ddYear++; }
  if (_ddMonth < 0)  { _ddMonth = 11; _ddYear--; }
  renderDueDateCalendar();
}

function renderDueDateCalendar() {
  const MONTHS = ['January','February','March','April','May','June',
                  'July','August','September','October','November','December'];
  document.getElementById('dueDateMonthLabel').textContent =
    `${MONTHS[_ddMonth]} ${_ddYear}`;

  const grid     = document.getElementById('dueDateDayGrid');
  const today    = new Date();
  today.setHours(0,0,0,0);
  const firstDow = new Date(_ddYear, _ddMonth, 1).getDay();
  const daysIn   = new Date(_ddYear, _ddMonth + 1, 0).getDate();

  let html = '';

  // Empty cells before first day
  for (let i = 0; i < firstDow; i++) {
    html += `<div class="dd2-day-empty"></div>`;
  }

  for (let d = 1; d <= daysIn; d++) {
    const thisDate = new Date(_ddYear, _ddMonth, d);
    const isPast   = thisDate < today;
    const isToday  = thisDate.getTime() === today.getTime();
    const isSel = (
  _ddDay === d &&
  _ddMonth === new Date(_ddYear, _ddMonth, d).getMonth() &&
  _ddYear  === new Date(_ddYear, _ddMonth, d).getFullYear()
);

    const cls = `dd2-day${isPast ? ' is-past' : ''}${isToday ? ' is-today' : ''}${isSel ? ' is-selected' : ''}`;
    html += `<button type="button" class="${cls}" ${isPast ? 'disabled' : ''} onclick="${isPast ? '' : `selectDueDay(${d})`}">${d}</button>`;
  }

  grid.innerHTML = html;
}

function selectDueDay(d) {
  _ddDay      = d;
  _ddQuickDay = null;
  document.querySelectorAll('.dd-quick-btn').forEach(b => b.classList.remove('active'));
  renderDueDateCalendar();
  dueDateUpdateTime();
}

function dueDateUpdateTime() {
  if (!_ddDay) return;

  let h    = parseInt(document.getElementById('dueDateHour').value) || 11;
  let m    = parseInt(document.getElementById('dueDateMin').value)  || 59;
  const ampm = document.getElementById('dueDateAmpm').value;

  // Clamp
  h = Math.min(12, Math.max(1, h));
  m = Math.min(59, Math.max(0, m));

  let h24 = h;
  if (ampm === 'PM' && h < 12)  h24 = h + 12;
  if (ampm === 'AM' && h === 12) h24 = 0;

  const pad   = n => String(n).padStart(2, '0');
  const month = pad(_ddMonth + 1);
  const day   = pad(_ddDay);
  const val   = `${_ddYear}-${month}-${day}T${pad(h24)}:${pad(m)}`;
  document.getElementById('pmDueDate').value = val;

  // Update display pill
  const MONTHS_SHORT = ['Jan','Feb','Mar','Apr','May','Jun',
                        'Jul','Aug','Sep','Oct','Nov','Dec'];
  const DAYS_SHORT   = ['Sunday','Monday','Tuesday','Wednesday',
                        'Thursday','Friday','Saturday'];
  const dow = new Date(_ddYear, _ddMonth, _ddDay).getDay();

  document.getElementById('dueDateLabel').textContent =
    `${DAYS_SHORT[dow]}, ${MONTHS_SHORT[_ddMonth]} ${_ddDay} · ${h}:${pad(m)} ${ampm}`;
  const prev = document.getElementById('dueDatePreviewVal');
  if (prev) {
    prev.textContent = `${MONTHS_SHORT[_ddMonth]} ${_ddDay}, ${_ddYear} · ${h}:${pad(m)} ${ampm}`;
    prev.classList.remove('dd2-preview-empty');
  }
  document.getElementById('dueDateDisplay').style.color       = 'var(--text)';
  document.getElementById('dueDateDisplay').style.borderColor = 'var(--primary)';
}

function clearDueDate() {
  _ddDay      = null;
  _ddQuickDay = null;
  _ddMonth    = new Date().getMonth();
  _ddYear     = new Date().getFullYear();
  document.getElementById('pmDueDate').value          = '';
  document.getElementById('dueDateLabel').textContent = 'No due date';
  const prev = document.getElementById('dueDatePreviewVal');
  if (prev) {
    prev.textContent = 'No date selected';
    prev.classList.add('dd2-preview-empty');
  }
  const disp = document.getElementById('dueDateDisplay');
  if (disp) { disp.style.color = ''; disp.style.borderColor = ''; }
  document.querySelectorAll('.dd-quick-btn').forEach(b => b.classList.remove('active'));
}

function resetDueDatePicker() {
  _ddDay      = null;
  _ddQuickDay = null;
  _ddMonth    = new Date().getMonth();
  _ddYear     = new Date().getFullYear();
  const el = document.getElementById('dueDateLabel');
  if (el) el.textContent = 'No due date';
  const disp = document.getElementById('dueDateDisplay');
  if (disp) { disp.style.color = ''; disp.style.borderColor = ''; }
  document.querySelectorAll('.dd-quick-btn').forEach(b => b.classList.remove('active'));
}

// Hook into openPostModal reset
const _origOpenPostModal = openPostModal;
openPostModal = function(typeObj) {
  const draftBeforeReset = (!_skipDraftResume && !(document.getElementById('pmPostId')?.value || '').trim())
    ? loadPostDraft()
    : null;
  _origOpenPostModal(typeObj);
  setTimeout(resetDueDatePicker, 50);
  setTimeout(() => { maybeResumePostDraft(draftBeforeReset); }, 120);
};

function openDueDateModal() {
  if (!_ddDay) {
    _ddMonth = new Date().getMonth();
    _ddYear  = new Date().getFullYear();
  }
  document.getElementById('dueDateModalBack').classList.add('show');
  renderDueDateCalendar();
  dueDateUpdateTime();
}

function closeDueDateModal() {
  document.getElementById('dueDateModalBack').classList.remove('show');
}
</script>
<!-- ══ INVITE STUDENTS MODAL ══ -->
<div class="modal-back" id="inviteModalBack">
  <div class="post-modal" style="max-width:580px;">
    <div class="pm-header">
      <div class="pm-title">
        <div class="pm-type-icon" style="background:#e8f0fe;color:#1f73db;">
          <i class="fas fa-paper-plane"></i>
        </div>
        <span>Invite Students</span>
      </div>
      <button class="pm-close" onclick="closeInviteModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="pm-body">
      <div class="pm-field">
        <label class="pm-label">Search Students</label>
        <div style="display:flex;gap:.5rem;">
          <input type="text" class="pm-input" id="inviteSearchInput"
            placeholder="Name, student number, or email…"
            oninput="debounceInviteSearch()" autocomplete="off" style="flex:1;">
          <button class="btn btn-ghost btn-sm" onclick="clearInviteSearch()">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      <div id="inviteSearchResults" style="max-height:260px;overflow-y:auto;border:1px solid var(--border);border-radius:var(--radius-sm);margin-top:.25rem;">
        <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.82rem;">
          Search to find students from the same course.
        </div>
      </div>
      <div id="inviteSelectedWrap" style="margin-top:.85rem;display:none;">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.4rem;">
          Selected <span id="inviteSelectedCount">0</span> student(s)
        </div>
        <div id="inviteSelectedChips" style="display:flex;flex-wrap:wrap;gap:.35rem;"></div>
      </div>
    </div>
    <div class="pm-footer">
      <button class="btn btn-ghost" onclick="closeInviteModal()">Cancel</button>
      <button class="btn btn-primary" id="inviteSendBtn" onclick="sendInvitations()" disabled>
        <i class="fas fa-paper-plane"></i> Send Invitations
      </button>
    </div>
  </div>
</div>

 
<!-- ══ WAITLIST OVERLAY + DRAWER ══ -->
<div class="wl-overlay" id="wlOverlay" onclick="closeWaitlistDrawer()"></div>
<div class="wl-drawer" id="wlDrawer">
  <div class="wl-drawer-head">
    <div class="wl-drawer-head-icon">
      <i class="fas fa-user-clock" style="color:#b45309;font-size:.9rem;"></i>
    </div>
    <div class="wl-drawer-head-info">
      <div class="wl-drawer-title">
        <span>Join Requests</span>
        <span class="wl-drawer-badge" id="wlDrawerBadge">0</span>
      </div>
      <div class="wl-drawer-sub">Review and admit or decline students</div>
    </div>
    <button class="wl-drawer-close" onclick="closeWaitlistDrawer()">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <div class="wl-drawer-search-row">
    <input type="text" class="wl-search-input" id="wlSearchInput"
      placeholder="Search by name, ID, or course…"
      oninput="wlSearch(this.value)">
    <select class="wl-sort-select" id="wlSortSelect" onchange="wlSearch(document.getElementById('wlSearchInput').value)">
      <option value="newest">Newest</option>
      <option value="name">Name</option>
      <option value="course">Course</option>
    </select>
  </div>
  <div class="wl-list" id="wlList">
    <div class="wl-empty-state">
      <div class="wl-ei"><i class="fas fa-inbox"></i></div>
      <h3>No pending requests</h3>
      <p>All join requests have been handled.</p>
    </div>
  </div>
  <div class="wl-drawer-footer" id="wlDrawerFooter">
    <button class="wl-admit-all-btn" id="wlAdmitAllBtn" onclick="wlAdmitAll()">
      <i class="fas fa-check-double"></i>
      <span id="wlAdmitAllLabel">Admit all</span>
    </button>
    <button class="wl-decline-all-btn" onclick="wlDeclineAll()">
      <i class="fas fa-times"></i> Decline all
    </button>
  </div>
</div>

<!-- GROUP DETAIL POPOVER (shared singleton) -->
<div id="grpPopover" class="grp-popover" style="display:none;">
  <button class="grp-popover-close" onclick="closeGrpPopover()"><i class="fas fa-times"></i></button>
  <div class="grp-popover-title" id="grpPopTitle"></div>
  <div class="grp-popover-list" id="grpPopList"></div>
</div>
<!-- DUE DATE MODAL -->
<div class="modal-back" id="dueDateModalBack" style="z-index:9999;">
  <div class="dd2-modal">
    <div class="dd2-head">
      <div class="dd2-head-left">
        <div class="dd2-head-icon"><i class="fas fa-calendar-alt"></i></div>
        <span class="dd2-title">Set due date</span>
      </div>
      <button class="dd2-close" onclick="closeDueDateModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="dd2-body">
      <div class="dd2-left">
        <div class="dd2-cal-nav">
          <button type="button" class="dd2-nav-btn" onclick="dueDateNavMonth(-1)"><i class="fas fa-chevron-left"></i></button>
          <span id="dueDateMonthLabel" class="dd2-month-label"></span>
          <button type="button" class="dd2-nav-btn" onclick="dueDateNavMonth(1)"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="dd2-cal-grid dd2-dow">
          <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
        </div>
        <div id="dueDateDayGrid" class="dd2-cal-grid dd2-day-grid"></div>
      </div>
      <div class="dd2-right">
        <div>
          <div class="dd2-rc-label">Quick select</div>
          <div class="dd2-pill-col">
            <button type="button" onclick="quickSelectDay(0)" class="dd-quick-btn">Sun</button>
            <button type="button" onclick="quickSelectDay(1)" class="dd-quick-btn">Mon</button>
            <button type="button" onclick="quickSelectDay(2)" class="dd-quick-btn">Tue</button>
            <button type="button" onclick="quickSelectDay(3)" class="dd-quick-btn">Wed</button>
            <button type="button" onclick="quickSelectDay(4)" class="dd-quick-btn">Thu</button>
            <button type="button" onclick="quickSelectDay(5)" class="dd-quick-btn">Fri</button>
            <button type="button" onclick="quickSelectDay(6)" class="dd-quick-btn">Sat</button>
          </div>
        </div>
        <div>
          <div class="dd2-rc-label">Time</div>
          <div class="dd2-time-row">
            <i class="fas fa-clock"></i>
            <input type="number" id="dueDateHour" min="1" max="12" value="11" class="dd2-time-inp" oninput="dueDateUpdateTime()">
            <span class="dd2-time-sep">:</span>
            <input type="number" id="dueDateMin" min="0" max="59" value="59" class="dd2-time-inp" oninput="dueDateUpdateTime()">
            <select id="dueDateAmpm" class="dd2-ampm" onchange="dueDateUpdateTime()">
              <option>AM</option>
              <option selected>PM</option>
            </select>
          </div>
        </div>
        <div class="dd2-preview-box">
          <div class="dd2-preview-label">Due date</div>
          <div class="dd2-preview-val dd2-preview-empty" id="dueDatePreviewVal">No date selected</div>
        </div>
      </div>
    </div>
    <div class="dd2-foot">
      <button class="dd2-btn dd2-btn-clear" onclick="clearDueDate();closeDueDateModal();"><i class="fas fa-times"></i> Clear</button>
      <div style="display:flex;gap:.45rem;">
        <button class="dd2-btn dd2-btn-done" onclick="closeDueDateModal()"><i class="fas fa-check"></i> Done</button>
      </div>
    </div>
  </div>
</div>
<!-- ══ AI QUIZ GENERATOR MODAL ══ -->
<div class="aq-backdrop" id="aqBackdrop">
  <div class="aq-modal" id="aqModal">

    <!-- Header -->
    <div class="aq-header">
      <div class="aq-header-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
      <div class="aq-header-text">
        <h3>Question Generator</h3>
        <p>Generate with AI or use Saved Questions from your existing quizzes</p>
      </div>
      <button class="aq-close" onclick="closeAqModal()"><i class="fas fa-times"></i></button>
    </div>

    <!-- Body -->
    <div class="aq-body">
      <div class="aq-source-tabs">
        <button type="button" class="aq-source-tab active" id="aqModeAiBtn" onclick="aqSetMode('ai')"><i class="fas fa-wand-magic-sparkles"></i> AI Generate</button>
        <button type="button" class="aq-source-tab" id="aqModeSavedBtn" onclick="aqSetMode('saved')"><i class="fas fa-database"></i> Saved Questions</button>
      </div>

      <div class="aq-layout">

        <div class="aq-col">
          <div id="aqAiSourceWrap">
            <div class="aq-section-label"><i class="fas fa-folder-open"></i> Source Files</div>

            <div class="aq-search-wrap">
              <i class="fas fa-search aq-search-icon"></i>
              <input type="text" class="aq-search-input" id="aqSearchInput"
                placeholder="Search by lesson name, week, topic, or filename…"
                oninput="aqFilterFiles(this.value)">
              <button class="aq-search-clear" onclick="aqClearSearch()" title="Clear"><i class="fas fa-times"></i></button>
            </div>

            <div class="aq-file-list" id="aqFileList">
              <div class="aq-empty-search"><i class="fas fa-spinner fa-spin"></i> Loading lesson files…</div>
            </div>

            <div style="margin-top:.65rem;">
              <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);margin-bottom:.35rem;">
                Selected Files <span id="aqSelCount" style="color:var(--primary);">0</span>
              </div>
              <div class="aq-selected-bar" id="aqSelectedBar">
                <span class="aq-sel-empty">No files selected yet — pick from above or upload directly</span>
              </div>
            </div>
          </div>

          <div id="aqSavedSourceWrap" style="display:none;">
            <div class="aq-section-label"><i class="fas fa-database"></i> Saved Questions</div>

            <div class="aq-search-wrap">
              <i class="fas fa-search aq-search-icon"></i>
              <input type="text" class="aq-search-input" id="aqSavedSearchInput"
                placeholder="Search by quiz title or topic…"
                oninput="aqFilterSavedSources(this.value)">
              <button class="aq-search-clear" onclick="aqClearSavedSearch()" title="Clear"><i class="fas fa-times"></i></button>
            </div>

            <div class="aq-file-list" id="aqSavedSourceList">
              <div class="aq-empty-search"><i class="fas fa-spinner fa-spin"></i> Loading saved questions…</div>
            </div>

            <div style="margin-top:.65rem;">
              <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);margin-bottom:.35rem;">
                Selected Sources <span id="aqSavedSelCount" style="color:var(--primary);">0</span>
              </div>
              <div class="aq-selected-bar" id="aqSavedSelectedBar">
                <span class="aq-sel-empty">No source selected — all saved questions in this class will be used</span>
              </div>
            </div>
          </div>

        </div>

        <div class="aq-col">
          <div class="aq-side-card">
            <div id="aqSavedSummaryWrap" style="display:none;">
              <div class="aq-section-label"><i class="fas fa-layer-group"></i> Saved Pool</div>
              <div class="aq-info-card">
                <strong id="aqSavedAvailableCount">0</strong>
                <span id="aqSavedAvailableText">saved questions available in this class</span>
              </div>
            </div>

            <div>
              <div class="aq-section-label"><i class="fas fa-list-ol"></i> <span id="aqQuestionCountHeading">Question Limit</span></div>
              <div class="aq-qtype-grid" style="grid-template-columns:1fr;">
                <div class="aq-qtype-card active" id="aqMcqCard">
                  <div class="aq-qtype-card-head">
                    <div class="aq-qtype-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-square"></i></div>
                    <div>
                      <div class="aq-qtype-title" id="aqQuestionCountTitle">Multiple Choice</div>
                      <div class="aq-qtype-sub" id="aqQuestionCountSub">4 options, 1 correct answer</div>
                    </div>
                  </div>
                  <div class="aq-counter">
                    <button type="button" class="aq-counter-btn" onclick="aqAdjust('mcq',-1)">−</button>
                    <input type="number" class="aq-counter-val" id="aqMcqCount" value="10" min="10" max="50" oninput="aqSyncCount('mcq',this.value)">
                    <button type="button" class="aq-counter-btn" onclick="aqAdjust('mcq',1)">+</button>
                    <span style="font-size:.72rem;color:var(--text-muted);margin-left:.25rem;">questions</span>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <div class="aq-section-label"><i class="fas fa-sliders-h"></i> Settings</div>
              <div class="aq-settings-row" style="grid-template-columns:1fr;">
                <div class="aq-setting-block">
                  <label>Points Per Question</label>
                  <input type="number" class="aq-points-input gc-input" id="aqPointsPerQuestion" value="1" min="0.5" step="0.5" placeholder="e.g. 2">
                </div>
              </div>
            </div>

            <div id="aqSavedShuffleWrap" style="display:none;">
              <div class="aq-helper-text" id="aqSavedLimitHint" style="margin-top:.15rem;">
                Answer choices will be randomized automatically. Choose a number that is not greater than the saved questions available.
              </div>
            </div>

            <div id="aqManualUploadWrap">
              <div class="aq-section-label"><i class="fas fa-file-upload"></i> Upload Manually</div>
              <div class="aq-upload-zone" id="aqUploadZone" onclick="document.getElementById('aqDirectFile').click()">
                <div class="aq-upload-icon" id="aqUploadIcon"><i class="fas fa-file-upload"></i></div>
                <div class="aq-upload-text">
                  <strong id="aqUploadLabel">Click to upload PDF, PPTX, DOCX, or TXT</strong>
                  <span id="aqUploadSub">Max 20MB — overrides selected lesson files</span>
                </div>
                <button class="aq-upload-clear" id="aqUploadClearBtn" style="display:none;" onclick="event.stopPropagation();aqClearDirectFile()" title="Remove file"><i class="fas fa-times"></i></button>
              </div>
              <input type="file" id="aqDirectFile" accept=".pdf,.pptx,.docx,.txt" style="display:none;" onchange="aqDirectFileChanged(this)">
            </div>
          </div>
        </div>

      </div>
    </div><!-- /aq-body -->

    <!-- Footer -->
    <div class="aq-footer">
      <div class="aq-total-badge">
        <i class="fas fa-layer-group" style="color:var(--primary);"></i>
        <strong id="aqTotalLabel">10</strong> questions total
        <span id="aqTotalPointsLabel" style="margin-left:.45rem;color:var(--text-muted);">• 10 pts total</span>
      </div>
      <button class="btn btn-ghost" onclick="closeAqModal()">Cancel</button>
      <button class="aq-gen-btn" id="aqGenBtn" onclick="aqConfirmGenerate()">
        <i class="fas fa-wand-magic-sparkles" id="aqGenBtnIcon"></i> <span id="aqGenBtnText">Generate Questions</span>
      </button>
    </div>

  </div>
</div>

<script>
/* ══ AI / SAVED QUESTIONS MODAL STATE ══ */
let _aqSelectedFiles = {}; // { idx: lessonAttachment }
let _aqDirectFile = null;
let _aqMode = 'ai';
let _aqSavedSources = [];
let _aqSelectedSourceIds = {};
const _aqDifficulty = 'balanced';

function aqSupportsSavedQuestions() {
  const activeType = (postTypes || []).find(x => String(x.id) === String(activePmTypeId));
  return isExamTypeObj(activeType);
}

function aqSetMode(mode) {
  const allowSaved = aqSupportsSavedQuestions();
  _aqMode = (allowSaved && mode === 'saved') ? 'saved' : 'ai';
  const aiBtn = document.getElementById('aqModeAiBtn');
  const savedBtn = document.getElementById('aqModeSavedBtn');
  const tabsWrap = document.querySelector('.aq-source-tabs');
  const headerText = document.querySelector('.aq-header-text p');
  if (aiBtn) aiBtn.classList.toggle('active', _aqMode === 'ai');
  if (savedBtn) savedBtn.classList.toggle('active', _aqMode === 'saved');
  if (savedBtn) savedBtn.style.display = allowSaved ? '' : 'none';
  if (tabsWrap) tabsWrap.style.display = allowSaved ? '' : 'none';
  if (headerText) {
    headerText.textContent = allowSaved
      ? 'Generate with AI or use Saved Questions from your existing quizzes'
      : 'Generate with AI from your lesson files';
  }

  const aiWrap = document.getElementById('aqAiSourceWrap');
  const savedWrap = document.getElementById('aqSavedSourceWrap');
  const savedSummaryWrap = document.getElementById('aqSavedSummaryWrap');
  const savedShuffleWrap = document.getElementById('aqSavedShuffleWrap');
  const manualUploadWrap = document.getElementById('aqManualUploadWrap');
  const questionHeading = document.getElementById('aqQuestionCountHeading');
  const questionTitle = document.getElementById('aqQuestionCountTitle');
  const questionSub = document.getElementById('aqQuestionCountSub');
  const genText = document.getElementById('aqGenBtnText');
  const genIcon = document.getElementById('aqGenBtnIcon');

  if (aiWrap) aiWrap.style.display = _aqMode === 'ai' ? '' : 'none';
  if (savedWrap) savedWrap.style.display = _aqMode === 'saved' ? '' : 'none';
  if (savedSummaryWrap) savedSummaryWrap.style.display = _aqMode === 'saved' ? '' : 'none';
  if (savedShuffleWrap) savedShuffleWrap.style.display = _aqMode === 'saved' ? '' : 'none';
  if (manualUploadWrap) manualUploadWrap.style.display = _aqMode === 'ai' ? '' : 'none';

  if (questionHeading) questionHeading.textContent = _aqMode === 'saved' ? 'How Many Questions' : 'Question Limit';
  if (questionTitle) questionTitle.textContent = _aqMode === 'saved' ? 'Random Pull' : 'Multiple Choice';
  if (questionSub) questionSub.textContent = _aqMode === 'saved'
    ? 'Randomly pull questions from your Saved Questions'
    : '4 options, 1 correct answer';
  if (genText) genText.textContent = _aqMode === 'saved' ? 'Load Questions' : 'Generate Questions';
  if (genIcon) genIcon.className = _aqMode === 'saved' ? 'fas fa-database' : 'fas fa-wand-magic-sparkles';

  aqUpdateTotal();
}

async function aqLoadSavedSources() {
  const list = document.getElementById('aqSavedSourceList');
  if (list) {
    list.innerHTML = '<div class="aq-empty-search"><i class="fas fa-spinner fa-spin"></i> Loading saved questions…</div>';
  }

  try {
    const res = await fetch(`API/facultyUI/classroom/get_saved_questions.php?class_id=${encodeURIComponent(CLASS_ID)}`);
    const data = await res.json();
    if (data.status !== 'success') {
      throw new Error(data.message || 'Failed to load saved questions.');
    }
    _aqSavedSources = Array.isArray(data.sources) ? data.sources : [];
    aqRenderSavedSources(_aqSavedSources);
    aqRenderSavedSourceChips();
    aqUpdateSavedAvailability();
  } catch (err) {
    console.error(err);
    _aqSavedSources = [];
    if (list) {
      list.innerHTML = '<div class="aq-empty-search"><i class="fas fa-circle-exclamation"></i> No saved questions available yet.</div>';
    }
    aqRenderSavedSourceChips();
    aqUpdateSavedAvailability();
  }
}

/* ── Open / Close ── */
async function openAqModal() {
  document.getElementById('aqBackdrop').classList.add('show');
  document.getElementById('aqSearchInput').value = '';
  const savedSearch = document.getElementById('aqSavedSearchInput');
  if (savedSearch) savedSearch.value = '';
  _aqSelectedFiles = {};
  _aqSelectedSourceIds = {};
  aqClearDirectFile();

  document.getElementById('aqFileList').innerHTML =
    '<div class="aq-empty-search"><i class="fas fa-spinner fa-spin"></i> Loading lesson files…</div>';
  const savedList = document.getElementById('aqSavedSourceList');
  if (savedList) {
    savedList.innerHTML = '<div class="aq-empty-search"><i class="fas fa-spinner fa-spin"></i> Loading saved questions…</div>';
  }

  const tasks = [loadLessonSelectorForAI()];
  if (aqSupportsSavedQuestions()) {
    tasks.push(aqLoadSavedSources());
  } else {
    _aqSavedSources = [];
    _aqSelectedSourceIds = {};
    aqRenderSavedSourceChips();
    aqUpdateSavedAvailability();
  }

  await Promise.all(tasks);

  aqRenderFileList(_lessonAttachments);
  aqRenderChips();
  aqSetMode(aqSupportsSavedQuestions() ? _aqMode : 'ai');
}
function closeAqModal() {
  document.getElementById('aqBackdrop').classList.remove('show');
}
document.getElementById('aqBackdrop').addEventListener('click', e => {
  if (e.target === document.getElementById('aqBackdrop')) closeAqModal();
});
document.getElementById('aqPointsPerQuestion')?.addEventListener('input', aqUpdateTotal);

function aqGetActiveSavedSources() {
  const selectedIds = Object.keys(_aqSelectedSourceIds);
  return selectedIds.length
    ? _aqSavedSources.filter(src => !!_aqSelectedSourceIds[src.source_post_id])
    : _aqSavedSources.slice();
}

function aqGetActiveSavedCount() {
  return aqGetActiveSavedSources().reduce((sum, src) => sum + (parseInt(src.question_count || 0, 10) || 0), 0);
}

function aqUpdateSavedAvailability() {
  const availableCount = aqGetActiveSavedCount();
  const selectedIds = Object.keys(_aqSelectedSourceIds);
  const countEl = document.getElementById('aqSavedAvailableCount');
  const textEl = document.getElementById('aqSavedAvailableText');
  const hintEl = document.getElementById('aqSavedLimitHint');
  if (countEl) countEl.textContent = String(availableCount);
  if (textEl) {
    textEl.textContent = selectedIds.length
      ? `saved question${availableCount === 1 ? '' : 's'} available from the selected source${selectedIds.length === 1 ? '' : 's'}`
      : `saved question${availableCount === 1 ? '' : 's'} available in this class`;
  }
  if (hintEl) {
    hintEl.textContent = availableCount > 0
      ? `You can pull up to ${availableCount} question${availableCount === 1 ? '' : 's'} from this saved pool.`
      : 'No saved questions available yet. Create a quiz first to build your Saved Questions pool.';
  }
}

/* ── File search / render ── */
function aqFilterFiles(query) {
  if (!query || !query.trim()) { aqRenderFileList(_lessonAttachments); return; }

  const qLower = query.trim().toLowerCase();
  const qFlat  = qLower.replace(/[\s\-_]+/g, '');

  function normalize(s)       { return (s || '').toLowerCase().replace(/[\s\-_]+/g, ''); }
  function normalizeSpaced(s) { return (s || '').toLowerCase().replace(/[\s\-_]+/g, ' ').trim(); }

  function matchesQuery(str) {
    if (!str) return false;
    const s       = str.toLowerCase();
    const sFlat   = normalize(str);
    const sSpaced = normalizeSpaced(str);
    return s.includes(qLower) || sFlat.includes(qFlat) || sSpaced.includes(qLower);
  }

  // Group-level match: if the lesson title/week/topic matches, include ALL files in that group
  const matchingGroupTitles = new Set();
  _lessonAttachments.forEach(f => {
    if (
      matchesQuery(f.post_title) ||
      matchesQuery(f.week)       ||
      matchesQuery(f.topic)      ||
      matchesQuery(f.search_key)
    ) {
      matchingGroupTitles.add(f.post_title);
    }
  });

  // File-level match: also include files whose own filename matches
  const filtered = _lessonAttachments.filter(f =>
    matchingGroupTitles.has(f.post_title) ||
    matchesQuery(f.file_name)
  );

  if (!filtered.length) {
    document.getElementById('aqFileList').innerHTML =
      '<div class="aq-empty-search"><i class="fas fa-search"></i> No lesson files found matching your search.</div>';
    return;
  }

  aqRenderFileList(filtered);
}

function aqClearSearch() {
  document.getElementById('aqSearchInput').value = '';
  aqRenderFileList(_lessonAttachments);
}

function aqFilterSavedSources(query) {
  const q = String(query || '').trim().toLowerCase();
  if (!q) {
    aqRenderSavedSources(_aqSavedSources);
    return;
  }
  const filtered = _aqSavedSources.filter(src => {
    const title = String(src.source_post_title || '').toLowerCase();
    const topic = String(src.source_topic || '').toLowerCase();
    const type = String(src.source_post_type || '').toLowerCase();
    return title.includes(q) || topic.includes(q) || type.includes(q);
  });
  aqRenderSavedSources(filtered);
}

function aqClearSavedSearch() {
  const input = document.getElementById('aqSavedSearchInput');
  if (input) input.value = '';
  aqRenderSavedSources(_aqSavedSources);
}

function aqFileIconClass(ext) { 
  if (ext === 'pdf')  return 'pdf';
  if (ext === 'pptx') return 'pptx';
  if (ext === 'docx') return 'docx';
  return 'txt';
}

function aqRenderFileList(files) {
  const el = document.getElementById('aqFileList');
  if (!files || !files.length) {
    el.innerHTML = '<div class="aq-empty-search"><i class="fas fa-search"></i> No lesson files found matching your search.</div>';
    return;
  }
  // Group by post_title
  const grouped = {};
  files.forEach((f, originalIdx) => {
    // find original index in _lessonAttachments
    const idx = _lessonAttachments.indexOf(f);
    if (!grouped[f.post_title]) grouped[f.post_title] = [];
    grouped[f.post_title].push({ ...f, _idx: idx });
  });

  let html = '';
  Object.entries(grouped).forEach(([title, items]) => {
    // Group header row (clicking it toggles all files under this lesson)
    // topic is shared across items in the same post_title group
    const groupTopic = items[0]?.topic || '';
    html += `<div style="padding:.45rem .9rem .3rem;background:var(--bg);border-bottom:1px solid var(--border);
                          display:flex;align-items:center;gap:.5rem;cursor:pointer;user-select:none;"
                  onclick="aqToggleGroup('${esc(title)}',${JSON.stringify(items.map(i=>i._idx))})">
      <i class="fas fa-folder" style="color:var(--primary);font-size:.72rem;flex-shrink:0;"></i>
      <div style="flex:1;min-width:0;">
        <div style="font-size:.76rem;font-weight:700;color:var(--primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(title)}</div>
        ${groupTopic ? `<div style="font-size:.65rem;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><i class="fas fa-tag" style="font-size:.55rem;margin-right:.2rem;"></i>${esc(groupTopic)}</div>` : ''}
      </div>
      <span style="font-size:.65rem;color:var(--text-muted);flex-shrink:0;">${items.length} file${items.length!==1?'s':''}</span>
      <i class="fas fa-check-double" style="margin-left:.4rem;font-size:.65rem;color:var(--primary);opacity:.5;flex-shrink:0;" title="Select all in lesson"></i>
    </div>`;
    items.forEach(f => {
      const sel = !!_aqSelectedFiles[f._idx];
      html += `<div class="aq-file-item${sel?' selected':''}" onclick="aqToggleFile(${f._idx})">
        <input type="checkbox" class="aq-file-cb" ${sel?'checked':''} onclick="event.stopPropagation();aqToggleFile(${f._idx})">
        <div class="aq-file-icon ${aqFileIconClass(f.ext)}">${(f.ext||'?').toUpperCase()}</div>
        <div class="aq-file-meta">
          <div class="aq-file-name">${esc(f.file_name)}</div>
          <div class="aq-file-sub">${esc(f.post_title)}</div>
        </div>
        <span class="aq-file-ext">${esc(f.ext||'')}</span>
      </div>`;
    });
  });
  el.innerHTML = html;
}

function aqToggleFile(idx) {
  if (_aqSelectedFiles[idx]) {
    delete _aqSelectedFiles[idx];
  } else {
    _aqSelectedFiles[idx] = _lessonAttachments[idx];
  }
  // Re-render list with current search
  aqFilterFiles(document.getElementById('aqSearchInput').value);
  aqRenderChips();
}

function aqToggleGroup(title, idxList) {
  const allSel = idxList.every(i => !!_aqSelectedFiles[i]);
  idxList.forEach(i => {
    if (allSel) {
      delete _aqSelectedFiles[i];
    } else {
      _aqSelectedFiles[i] = _lessonAttachments[i];
    }
  });
  aqFilterFiles(document.getElementById('aqSearchInput').value);
  aqRenderChips();
}

function aqRenderChips() {
  const bar = document.getElementById('aqSelectedBar');
  const cnt = document.getElementById('aqSelCount');
  const keys = Object.keys(_aqSelectedFiles);
  cnt.textContent = keys.length;
  if (!keys.length) {
    bar.innerHTML = '<span class="aq-sel-empty">No files selected yet — pick from above or upload directly</span>';
    return;
  }
  bar.innerHTML = keys.map(k => {
    const f = _aqSelectedFiles[k];
    return `<span class="aq-sel-chip">
      <i class="fas fa-file" style="font-size:.65rem;"></i>
      ${esc(f.file_name.length > 28 ? f.file_name.substring(0,26)+'…' : f.file_name)}
      <button onclick="aqToggleFile(${k})" title="Remove"><i class="fas fa-times"></i></button>
    </span>`;
  }).join('');
}

function aqRenderSavedSources(sources) {
  const el = document.getElementById('aqSavedSourceList');
  if (!el) return;
  if (!sources || !sources.length) {
    el.innerHTML = '<div class="aq-empty-search"><i class="fas fa-database"></i> No saved questions found yet.</div>';
    return;
  }

  el.innerHTML = sources.map(src => {
    const selected = !!_aqSelectedSourceIds[src.source_post_id];
    return `
      <div class="aq-file-item${selected ? ' selected' : ''}" onclick="aqToggleSavedSource('${esc(src.source_post_id)}')">
        <input type="checkbox" class="aq-file-cb" ${selected ? 'checked' : ''} onclick="event.stopPropagation();aqToggleSavedSource('${esc(src.source_post_id)}')">
        <div class="aq-file-icon txt" style="background:#e8f5e9;color:#1a9e78;"><i class="fas fa-list-check"></i></div>
        <div class="aq-file-meta">
          <div class="aq-file-name">${esc(src.source_post_title || 'Untitled Quiz')}</div>
          <div class="aq-file-sub">${esc(src.source_topic || 'No topic set')} · ${esc(String(src.source_post_type || 'quiz')).toUpperCase()}</div>
        </div>
        <span class="aq-file-ext">${parseInt(src.question_count || 0, 10)} Q</span>
      </div>
    `;
  }).join('');
}

function aqToggleSavedSource(sourcePostId) {
  if (_aqSelectedSourceIds[sourcePostId]) {
    delete _aqSelectedSourceIds[sourcePostId];
  } else {
    _aqSelectedSourceIds[sourcePostId] = true;
  }
  aqFilterSavedSources(document.getElementById('aqSavedSearchInput')?.value || '');
  aqRenderSavedSourceChips();
  aqUpdateTotal();
}

function aqRenderSavedSourceChips() {
  const bar = document.getElementById('aqSavedSelectedBar');
  const cnt = document.getElementById('aqSavedSelCount');
  if (!bar || !cnt) return;
  const selected = _aqSavedSources.filter(src => !!_aqSelectedSourceIds[src.source_post_id]);
  cnt.textContent = String(selected.length);
  if (!selected.length) {
    bar.innerHTML = '<span class="aq-sel-empty">No source selected — all saved questions in this class will be used</span>';
    return;
  }
  bar.innerHTML = selected.map(src => `
    <span class="aq-sel-chip">
      <i class="fas fa-database" style="font-size:.65rem;"></i>
      ${esc((src.source_post_title || 'Untitled Quiz').length > 26 ? (src.source_post_title || 'Untitled Quiz').substring(0, 24) + '…' : (src.source_post_title || 'Untitled Quiz'))}
      <button onclick="aqToggleSavedSource('${esc(src.source_post_id)}')" title="Remove"><i class="fas fa-times"></i></button>
    </span>
  `).join('');
}

/* ── Direct file upload ── */
function aqDirectFileChanged(input) {
  _aqDirectFile = input.files[0] || null;
  const zone  = document.getElementById('aqUploadZone');
  const icon  = document.getElementById('aqUploadIcon');
  const label = document.getElementById('aqUploadLabel');
  const sub   = document.getElementById('aqUploadSub');
  const clrBtn = document.getElementById('aqUploadClearBtn');
  if (_aqDirectFile) {
    zone.classList.add('has-file');
    icon.innerHTML = '<i class="fas fa-check-circle" style="color:var(--primary);"></i>';
    label.textContent = _aqDirectFile.name;
    sub.textContent   = ((_aqDirectFile.size/1024/1024).toFixed(1)) + ' MB · Ready to use';
    clrBtn.style.display = 'flex';
  }
}
function aqClearDirectFile() {
  _aqDirectFile = null;
  document.getElementById('aqDirectFile').value = '';
  const zone  = document.getElementById('aqUploadZone');
  const icon  = document.getElementById('aqUploadIcon');
  const label = document.getElementById('aqUploadLabel');
  const sub   = document.getElementById('aqUploadSub');
  const clrBtn = document.getElementById('aqUploadClearBtn');
  zone.classList.remove('has-file');
  icon.innerHTML = '<i class="fas fa-file-upload"></i>';
  label.textContent = 'Click to upload PDF, PPTX, DOCX, or TXT';
  sub.textContent   = 'Max 20MB — overrides selected lesson files';
  clrBtn.style.display = 'none';
}

/* ── Question type counters ── */
function aqAdjust(type, delta) {
  const id  = type === 'mcq' ? 'aqMcqCount' : 'aqIdCount';
  const el  = document.getElementById(id);
  const min = type === 'mcq' ? 10 : 0;
  const val = Math.max(min, Math.min(50, (parseInt(el.value)||0) + delta));
  el.value  = val;
  aqSyncCount(type, val);
}
function aqSyncCount(type, val) {
  const min = type === 'mcq' ? 10 : 0;
  const n = Math.max(min, Math.min(50, parseInt(val)||0));
  const id  = type === 'mcq' ? 'aqMcqCount' : 'aqIdCount';
  const input = document.getElementById(id);
  if (input) input.value = n;
  const cardId = type === 'mcq' ? 'aqMcqCard' : 'aqIdCard';
  document.getElementById(cardId).classList.toggle('active', n > 0);
  aqUpdateTotal();
}
function aqUpdateTotal() {
  const mcq = parseInt(document.getElementById('aqMcqCount')?.value||0);
  const lbl = document.getElementById('aqTotalLabel');
  if (lbl) lbl.textContent = mcq;
  const perQuestion = parseFloat(document.getElementById('aqPointsPerQuestion')?.value || '0') || 0;
  const totalPoints = mcq * perQuestion;
  const totalPtsLbl = document.getElementById('aqTotalPointsLabel');
  if (totalPtsLbl) totalPtsLbl.textContent = `• ${Number(totalPoints.toFixed(2)).toString().replace(/\.0+$/,'').replace(/(\.\d*[1-9])0+$/,'$1')} pts total`;
  const oldMcq = document.getElementById('aiMcqCount');
  if (oldMcq) oldMcq.value = mcq;
  const oldTotal = document.getElementById('aiTotalPoints');
  if (oldTotal) oldTotal.value = totalPoints > 0 ? String(totalPoints) : '';
  aqUpdateSavedAvailability();
  aqRefreshGenerateState();
}

function aqRefreshGenerateState() {
  const btn = document.getElementById('aqGenBtn');
  if (!btn) return;
  if (_aqMode !== 'saved') {
    btn.disabled = false;
    return;
  }
  const requested = parseInt(document.getElementById('aqMcqCount')?.value || '0', 10) || 0;
  const available = aqGetActiveSavedCount();
  btn.disabled = available <= 0 || requested > available;
}

function aqApplyGeneratedQuestions(questions, successMessage) {
  quizQuestions = (questions || []).map((q, index) => ({
    id: (_aqMode === 'saved' ? 'bank_' : 'ai_') + Date.now() + '_' + index,
    question: q.question || '',
    answer: q.answer || q.answer_key || '',
    points: parseFloat(q.points) || 1,
    cognitive_level: String(q.cognitive_level || '').toLowerCase(),
    time_limit_seconds: parseInt(q.time_limit_seconds || 0, 10) || '',
    choices: Array.isArray(q.choices)
      ? q.choices.map(c => ({ text: c.text || c.choice_text || '', is_correct: !!c.is_correct }))
      : []
  }));

  renderQuizBuilder();

  const badge = document.getElementById('aqGeneratedBadge');
  if (badge) { badge.textContent = quizQuestions.length; badge.style.display = ''; }

  const ptEl = document.getElementById('aiTotalPoints');
  const quizTotalPoints = quizQuestions.reduce((sum, q) => sum + (parseFloat(q.points) || 0), 0);
  if (ptEl) ptEl.value = quizTotalPoints;
  document.getElementById('pmPoints').value = quizTotalPoints;
  _postDraftSuspendSave = false;
  savePostDraftNow(true);

  toast(successMessage || `✅ ${quizQuestions.length} questions ready — review below before posting.`);
}

/* ── Confirm + Generate ── */
async function aqConfirmGenerate() {
  const mcq = parseInt(document.getElementById('aqMcqCount').value||0);
  const tot = mcq;
  const pointsPerQuestion = parseFloat(document.getElementById('aqPointsPerQuestion').value||0);
  const totalPoints = tot * pointsPerQuestion;
  const selKeys = Object.keys(_aqSelectedFiles);
  const selectedSourceIds = Object.keys(_aqSelectedSourceIds);
  const availableSavedCount = aqGetActiveSavedCount();
  const shuffleChoices = 1;

  if (_aqMode === 'ai' && !selKeys.length && !_aqDirectFile) {
    toast('Please select at least one lesson file or upload a file directly.', 'error'); return;
  }
  if (tot < 10) {
    toast('Please set at least 10 questions.', 'error'); return;
  }
  if (_aqMode === 'saved' && availableSavedCount <= 0) {
    toast('No saved questions are available yet.', 'error'); return;
  }
  if (_aqMode === 'saved' && tot > availableSavedCount) {
    toast(`You can only pull up to ${availableSavedCount} saved question${availableSavedCount === 1 ? '' : 's'}.`, 'error'); return;
  }

  const r = await Swal.fire({
    title: _aqMode === 'saved' ? 'Load Saved Questions?' : 'Generate Quiz?',
    html: `<div style="text-align:left;font-size:.88rem;line-height:1.7;">
      <div style="margin-bottom:.5rem;"><i class="fas fa-list-ol" style="color:var(--primary);margin-right:.4rem;"></i><strong>${tot} question${tot!==1?'s':''}</strong>${_aqMode === 'saved' ? ' will be pulled randomly' : '— Multiple Choice'}</div>
      <div style="margin-bottom:.5rem;"><i class="fas fa-star" style="color:var(--primary);margin-right:.4rem;"></i><strong>${pointsPerQuestion} point${pointsPerQuestion!==1?'s':''}</strong> per question</div>
      <div style="margin-bottom:.5rem;"><i class="fas fa-sigma" style="color:var(--primary);margin-right:.4rem;"></i><strong>${totalPoints} points</strong> total</div>
      ${
        _aqMode === 'saved'
          ? `<div style="margin-bottom:.5rem;"><i class="fas fa-database" style="color:var(--primary);margin-right:.4rem;"></i><strong>${availableSavedCount} saved question${availableSavedCount===1?'':'s'}</strong> available in the current pool</div>
             <div><i class="fas fa-shuffle" style="color:var(--primary);margin-right:.4rem;"></i>Answer choices will be shuffled automatically</div>`
          : `<div><i class="fas fa-file" style="color:var(--primary);margin-right:.4rem;"></i>
              ${_aqDirectFile ? `<strong>${esc(_aqDirectFile.name)}</strong> (uploaded)` : `<strong>${selKeys.length} lesson file${selKeys.length!==1?'s':''}</strong>`}
            </div>`
      }
      <div style="margin-top:.75rem;padding:.55rem .75rem;background:#fff3e0;border-radius:8px;font-size:.8rem;color:#e65100;">
        <i class="fas fa-info-circle"></i> Questions will appear for review before posting.
      </div>
    </div>`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#1a9e78',
    confirmButtonText: _aqMode === 'saved'
      ? '<i class="fas fa-database"></i> Yes, Load!'
      : '<i class="fas fa-wand-magic-sparkles"></i> Yes, Generate!',
    cancelButtonText: 'Review settings',
    reverseButtons: true
  });
  if (!r.isConfirmed) return;

  closeAqModal();
  if (_aqMode === 'saved') {
    await runSavedQuestionPull(mcq, pointsPerQuestion, selectedSourceIds, shuffleChoices);
    return;
  }
  await runAqGenerate(mcq, 0, pointsPerQuestion, _aqDifficulty, selKeys);
}

async function runAqGenerate(mcqCount, idCount, pointsPerQuestion, difficulty, selKeys) {
  // Loading toast
  const loadingId = Swal.fire({
    title: 'Generating Quiz…',
    html: '<div style="font-size:.88rem;color:#5f6368;margin-top:.5rem;">AI is reading your lesson and creating questions.<br><small>This usually takes 10–30 seconds.</small></div>',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => Swal.showLoading()
  });

  const fd = new FormData();
  fd.append('mcq_count', mcqCount);
  fd.append('identification_count', idCount);
  fd.append('points_per_question', pointsPerQuestion);
  fd.append('total_points', mcqCount * pointsPerQuestion);
  fd.append('difficulty', difficulty);
  const activeTypeForGeneration = (postTypes || []).find(x => String(x.id) === String(activePmTypeId));
  fd.append('assessment_kind', isExamTypeObj(activeTypeForGeneration) ? 'exam' : 'quiz');

  try {
    if (_aqDirectFile) {
      fd.append('source_file', _aqDirectFile);
    } else {
      const lessonFiles = selKeys.map(k => _lessonAttachments[parseInt(k)]);
      if (lessonFiles.length === 1) {
        const resp = await fetch(lessonFiles[0].file_path);
        const blob = await resp.blob();
        fd.append('source_file', blob, lessonFiles[0].file_name);
      } else {
        const blobs = await Promise.all(lessonFiles.map(f => fetch(f.file_path).then(r => r.blob())));
        fd.append('source_file', blobs[0], lessonFiles[0].file_name);
        for (let i = 1; i < blobs.length; i++) {
          fd.append('additional_files[]', blobs[i], lessonFiles[i].file_name);
        }
      }
    }

    const res  = await fetch('API/facultyUI/classroom/generate_quiz_ai.php', { method: 'POST', body: fd });
    const raw  = await res.text();
    let data;
    try { data = JSON.parse(raw); } catch(e) { Swal.close(); toast('Server returned invalid response.', 'error'); return; }

    Swal.close();
    if (data.status !== 'success') { toast(data.message || 'Failed to generate quiz.', 'error'); return; }

    aqApplyGeneratedQuestions(data.questions || [], `✅ ${data.questions.length} questions generated — review below before posting.`);
  } catch(e) {
    Swal.close();
    toast('Network error while generating quiz.', 'error');
    console.error(e);
  }
}

async function runSavedQuestionPull(questionCount, pointsPerQuestion, selectedSourceIds, shuffleChoices) {
  const loadingId = Swal.fire({
    title: 'Loading Saved Questions…',
    html: '<div style="font-size:.88rem;color:#5f6368;margin-top:.5rem;">Picking random questions from your Saved Questions pool.</div>',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => Swal.showLoading()
  });

  try {
    const fd = new FormData();
    fd.append('class_id', CLASS_ID);
    fd.append('question_count', questionCount);
    fd.append('points_per_question', pointsPerQuestion);
    fd.append('shuffle_choices', shuffleChoices ? '1' : '0');
    fd.append('source_post_ids', JSON.stringify(selectedSourceIds || []));

    const res = await fetch('API/facultyUI/classroom/pull_saved_questions.php', { method: 'POST', body: fd });
    const data = await res.json();
    Swal.close();
    if (data.status !== 'success') {
      toast(data.message || 'Failed to load saved questions.', 'error');
      return;
    }

    aqApplyGeneratedQuestions(data.questions || [], `✅ ${data.pulled_count} saved questions loaded — review below before posting.`);
  } catch (e) {
    Swal.close();
    toast('Network error while loading saved questions.', 'error');
    console.error(e);
  }
}

/* ── Keep old function names working (called from loadLessonSelectorForAI) ── */
function updateAiTotalQuestions() { aqUpdateTotal(); }
/* Stub out the old hidden inputs for backward compat */
function _ensureOldInputs() {
  ['aiMcqCount','aiIdentificationCount','aiTotalPoints','aiDifficulty'].forEach(id => {
    if (!document.getElementById(id)) {
      const el = document.createElement('input');
      el.type = 'hidden'; el.id = id;
      document.body.appendChild(el);
    }
  });
}
_ensureOldInputs();
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
// Pause/resume classroom polling on tab visibility
document.addEventListener('visibilitychange', () => {
  if (document.hidden) {
    Object.keys(_polls).forEach(k => { clearInterval(_polls[k]); delete _polls[k]; });
  } else {
    _polls['cr'] = setInterval(pollClassroom, 25000);
  }
});

/* ══════════════════════════════════════════
   REPORT SUBVIEW + ACCOMPLISHMENT REPORT MODULE
══════════════════════════════════════════ */
(function(){
  const API = 'API/facultyUI/classroom/report_ajax.php';
  let arInited = false;
  let arLoaded = false;
  let arLoading = false;
  let arDirty = false;
  let arAutoSaveTimer = null;
  let arState = { context:null, sessions:[], current:null, analytics:[], today:null };
  let arCurrentStep = 0;
  let arDatePickerMonth = null;
  let arDatePickerStart = '';
  let arDatePickerEnd = '';
  const arWizardSteps = [
    { title:'Session and class details', short:'Session' },
    { title:'Report details', short:'Report' },
    { title:'Activities and photo documentation', short:'Activities' },
    { title:'Approval and preview', short:'Approval' }
  ];

  const reportTabBtn = document.getElementById('reportTabBtn');
  if (reportTabBtn) {
    reportTabBtn.addEventListener('click', () => {
      if (!arInited) arInit();
    });
  }

  function arInit(){
    arInited = true;
    bindReportControls();
    loadReportBootstrap();
    if (!arAutoSaveTimer) {
      arAutoSaveTimer = setInterval(() => saveReportDraft({ silent:true, autosave:true }), 60000);
    }
  }

  function bindReportControls(){
    document.querySelectorAll('.report-view-btn').forEach(btn => {
      btn.addEventListener('click', () => switchReportView(btn.dataset.reportView));
    });

    const form = document.getElementById('accomplishmentReportForm');
    if (form) {
      form.querySelectorAll('input,textarea').forEach(el => {
        if (el.readOnly || el.type === 'hidden') return;
        el.addEventListener('input', () => markReportDirty());
        el.addEventListener('change', () => markReportDirty());
      });
    }
    bindReportWizardControls();

    const academicWeekInput = document.getElementById('arAcademicWeek');
    if (academicWeekInput) {
      academicWeekInput.readOnly = true;
      academicWeekInput.classList.add('ar-week-picker-input');
      academicWeekInput.placeholder = 'Choose week no.';
      const openWeekPicker = () => {
        openReportAcademicWeekPicker(academicWeekInput.value, week => {
          academicWeekInput.value = week ? formatAcademicWeekOrdinal(week) : '';
          markReportDirty();
          renderPrintPreview();
        });
      };
      academicWeekInput.addEventListener('click', openWeekPicker);
      academicWeekInput.addEventListener('keydown', e => {
        if (['Enter', ' ', 'ArrowDown'].includes(e.key)) {
          e.preventDefault();
          openWeekPicker();
        }
      });
    }
    bindReportDateRangeControls();
    bindReportPhotoControls();

    const sessionSelect = document.getElementById('arSessionSelect');
    if (sessionSelect) {
      sessionSelect.addEventListener('change', () => {
        if (sessionSelect.value) loadReportSession(sessionSelect.value);
      });
    }

    document.getElementById('arSaveDraftBtn')?.addEventListener('click', () => saveReportDraft({ silent:false }));
    document.getElementById('arPreviewBtn')?.addEventListener('click', async () => {
      if (!document.getElementById('arSessionId')?.value) {
        toast('Select a recorded session first.', 'error');
        return;
      }
      if (arDirty) {
        const ok = await saveReportDraft({ silent:true });
        if (!ok) return;
      }
      renderPrintPreview();
      switchReportView('preview');
    });
    document.getElementById('arBackToFormBtn')?.addEventListener('click', () => switchReportView('accomplishment'));
    document.getElementById('arPrintBtn')?.addEventListener('click', () => {
      renderPrintPreview();
      window.print();
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && document.getElementById('arDateRangeBack')?.classList.contains('show')) {
        closeReportDateRangePicker();
        return;
      }
      if (e.key === 'Escape' && document.body.classList.contains('report-preview-mode')) {
        switchReportView('accomplishment');
      }
    });

    document.addEventListener('attendance:saved', () => {
      arLoaded = false;
      loadReportBootstrap();
    });
  }

  function bindReportWizardControls(){
    document.querySelectorAll('[data-ar-step-target]').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = Number(btn.dataset.arStepTarget || 0);
        goReportWizardStep(target);
      });
    });
    document.getElementById('arWizardPrevBtn')?.addEventListener('click', () => goReportWizardStep(arCurrentStep - 1));
    document.getElementById('arWizardNextBtn')?.addEventListener('click', () => goReportWizardStep(arCurrentStep + 1));
    updateReportWizard();
  }

  function goReportWizardStep(target){
    const last = arWizardSteps.length - 1;
    const next = Math.max(0, Math.min(last, Number(target) || 0));
    if (next > arCurrentStep && !canAdvanceReportWizard()) return;
    arCurrentStep = next;
    updateReportWizard(true);
  }

  function canAdvanceReportWizard(){
    if (arCurrentStep === 0 && !valueOf('arSessionId')) {
      toast('Select a recorded session first.', 'error');
      document.getElementById('arSessionSelect')?.focus();
      return false;
    }
    return true;
  }

  function updateReportWizard(scrollIntoView = false){
    const last = arWizardSteps.length - 1;
    arCurrentStep = Math.max(0, Math.min(last, arCurrentStep));
    const percent = Math.round(((arCurrentStep + 1) / arWizardSteps.length) * 100);
    const meta = arWizardSteps[arCurrentStep] || arWizardSteps[0];

    document.getElementById('arWizardStepLabel') && (document.getElementById('arWizardStepLabel').textContent = `Step ${arCurrentStep + 1} of ${arWizardSteps.length}`);
    document.getElementById('arWizardTitle') && (document.getElementById('arWizardTitle').textContent = meta.title);
    document.getElementById('arWizardPercent') && (document.getElementById('arWizardPercent').textContent = `${percent}%`);
    const bar = document.getElementById('arWizardBar');
    if (bar) bar.style.width = `${percent}%`;

    document.querySelectorAll('.ar-wizard-step').forEach(panel => {
      panel.classList.toggle('is-active', Number(panel.dataset.arStep || 0) === arCurrentStep);
    });
    document.querySelectorAll('[data-ar-step-target]').forEach(btn => {
      const step = Number(btn.dataset.arStepTarget || 0);
      btn.classList.toggle('is-active', step === arCurrentStep);
      btn.classList.toggle('is-done', step < arCurrentStep);
      btn.setAttribute('aria-selected', step === arCurrentStep ? 'true' : 'false');
    });

    const prev = document.getElementById('arWizardPrevBtn');
    const next = document.getElementById('arWizardNextBtn');
    const preview = document.getElementById('arPreviewBtn');
    if (prev) prev.disabled = arCurrentStep === 0;
    if (next) next.style.display = arCurrentStep === last ? 'none' : '';
    if (preview) preview.style.display = arCurrentStep === last ? '' : 'none';

    if (scrollIntoView) {
      document.querySelector('.ar-wizard-head')?.scrollIntoView({ behavior:'smooth', block:'start' });
    }
  }

  function bindReportDateRangeControls(){
    const input = document.getElementById('arDateCovered');
    const pickBtn = document.getElementById('arDateCoveredPickBtn');
    const back = document.getElementById('arDateRangeBack');
    const openPicker = () => openReportDateRangePicker();
    if (input) {
      input.readOnly = true;
      input.classList.add('ar-date-range-input');
      input.addEventListener('click', openPicker);
      input.addEventListener('keydown', e => {
        if (['Enter', ' ', 'ArrowDown'].includes(e.key)) {
          e.preventDefault();
          openPicker();
        }
      });
    }
    pickBtn?.addEventListener('click', openPicker);
    document.getElementById('arDateRangeCloseBtn')?.addEventListener('click', closeReportDateRangePicker);
    document.getElementById('arDateRangePrevBtn')?.addEventListener('click', () => moveReportDateRangeMonth(-1));
    document.getElementById('arDateRangeNextBtn')?.addEventListener('click', () => moveReportDateRangeMonth(1));
    document.getElementById('arDateRangeClearBtn')?.addEventListener('click', clearReportDateRangeSelection);
    document.getElementById('arDateRangeTodayBtn')?.addEventListener('click', selectReportSessionDateRange);
    document.getElementById('arDateRangeApplyBtn')?.addEventListener('click', applyReportDateRange);
    back?.addEventListener('click', e => {
      if (e.target === back) closeReportDateRangePicker();
    });
  }

  function openReportDateRangePicker(){
    const fallbackIso = getReportSessionIsoDate();
    const parsed = parseReportDateRangeInput(valueOf('arDateCovered'), fallbackIso);
    arDatePickerStart = valueOf('arDateCoveredStart') || parsed.start || fallbackIso;
    arDatePickerEnd = valueOf('arDateCoveredEnd') || parsed.end || arDatePickerStart;
    const seed = parseIsoDateLocal(arDatePickerStart || fallbackIso || isoDateLocal(new Date()));
    arDatePickerMonth = new Date(seed.getFullYear(), seed.getMonth(), 1);
    renderReportDateRangePicker();
    const back = document.getElementById('arDateRangeBack');
    if (back) {
      back.classList.add('show');
      back.setAttribute('aria-hidden', 'false');
    }
  }

  function closeReportDateRangePicker(){
    const back = document.getElementById('arDateRangeBack');
    if (back) {
      back.classList.remove('show');
      back.setAttribute('aria-hidden', 'true');
    }
  }

  function moveReportDateRangeMonth(delta){
    const current = arDatePickerMonth || new Date();
    arDatePickerMonth = new Date(current.getFullYear(), current.getMonth() + delta, 1);
    renderReportDateRangePicker();
  }

  function clearReportDateRangeSelection(){
    arDatePickerStart = '';
    arDatePickerEnd = '';
    renderReportDateRangePicker();
  }

  function selectReportSessionDateRange(){
    const iso = getReportSessionIsoDate();
    if (!iso) return;
    arDatePickerStart = iso;
    arDatePickerEnd = iso;
    const seed = parseIsoDateLocal(iso);
    arDatePickerMonth = new Date(seed.getFullYear(), seed.getMonth(), 1);
    renderReportDateRangePicker();
  }

  function chooseReportDateCovered(iso){
    if (!arDatePickerStart || (arDatePickerStart && arDatePickerEnd)) {
      arDatePickerStart = iso;
      arDatePickerEnd = '';
    } else if (iso < arDatePickerStart) {
      arDatePickerEnd = arDatePickerStart;
      arDatePickerStart = iso;
    } else {
      arDatePickerEnd = iso;
    }
    renderReportDateRangePicker();
  }

  function applyReportDateRange(){
    if (!arDatePickerStart) {
      toast('Choose the first date covered.', 'error');
      return;
    }
    const end = arDatePickerEnd || arDatePickerStart;
    setReportDateCoveredRange(formatReportDateRange(arDatePickerStart, end), arDatePickerStart, end);
    markReportDirty();
    renderPrintPreview();
    closeReportDateRangePicker();
  }

  function renderReportDateRangePicker(){
    const grid = document.getElementById('arDateRangeGrid');
    const monthLabel = document.getElementById('arDateRangeMonth');
    const summary = document.getElementById('arDateRangeSummary');
    if (!grid || !monthLabel || !summary) return;
    const monthDate = arDatePickerMonth || new Date();
    const year = monthDate.getFullYear();
    const month = monthDate.getMonth();
    monthLabel.textContent = monthDate.toLocaleDateString('en-US', { month:'long', year:'numeric' });
    const start = arDatePickerStart || '';
    const end = arDatePickerEnd || '';
    const displayEnd = end || start;
    summary.innerHTML = `<i class="fas ${start ? 'fa-check-circle' : 'fa-calendar-days'}"></i><span>${start ? escapeHtml(formatReportDateRange(start, displayEnd)) : 'Choose the first date, then the last date.'}</span>`;

    grid.innerHTML = '';
    const firstDay = new Date(year, month, 1).getDay();
    const days = new Date(year, month + 1, 0).getDate();
    for (let i = 0; i < firstDay; i++) {
      const blank = document.createElement('button');
      blank.type = 'button';
      blank.className = 'ar-date-cell';
      blank.disabled = true;
      grid.appendChild(blank);
    }
    for (let day = 1; day <= days; day++) {
      const iso = isoDateLocal(new Date(year, month, day));
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ar-date-cell';
      if (iso === start) btn.classList.add('is-start');
      if (iso === end) btn.classList.add('is-end');
      if (start && end && iso > start && iso < end) btn.classList.add('is-range');
      btn.textContent = String(day);
      btn.title = new Date(year, month, day).toLocaleDateString('en-US', { month:'long', day:'numeric', year:'numeric' });
      btn.addEventListener('click', () => chooseReportDateCovered(iso));
      grid.appendChild(btn);
    }
  }

  function setReportDateCoveredRange(label, startIso = '', endIso = ''){
    setVal('arDateCovered', label || '');
    setVal('arDateCoveredStart', startIso || '');
    setVal('arDateCoveredEnd', endIso || startIso || '');
    arDatePickerStart = startIso || '';
    arDatePickerEnd = endIso || startIso || '';
  }

  function getReportSessionIsoDate(){
    return normalizeIsoDate(arState.current?.auto?.date_conducted_raw)
      || normalizeIsoDate(arState.current?.session?.attendance_date)
      || normalizeIsoDate(arState.today)
      || isoDateLocal(new Date());
  }

  function parseReportDateRangeInput(text, fallbackIso = ''){
    const fallback = normalizeIsoDate(fallbackIso);
    const clean = String(text || '').trim().replace(/\s+/g, ' ');
    if (!clean) return { start:fallback, end:fallback };

    let match = clean.match(/^([A-Za-z]+)\s+(\d{1,2})\s*-\s*(\d{1,2}),\s*(\d{4})$/);
    if (match) {
      const month = reportMonthIndex(match[1]);
      if (month >= 0) {
        return {
          start: isoDateLocal(new Date(Number(match[4]), month, Number(match[2]))),
          end: isoDateLocal(new Date(Number(match[4]), month, Number(match[3])))
        };
      }
    }

    match = clean.match(/^([A-Za-z]+)\s+(\d{1,2})\s*-\s*([A-Za-z]+)\s+(\d{1,2}),\s*(\d{4})$/);
    if (match) {
      const startMonth = reportMonthIndex(match[1]);
      const endMonth = reportMonthIndex(match[3]);
      if (startMonth >= 0 && endMonth >= 0) {
        return {
          start: isoDateLocal(new Date(Number(match[5]), startMonth, Number(match[2]))),
          end: isoDateLocal(new Date(Number(match[5]), endMonth, Number(match[4])))
        };
      }
    }

    match = clean.match(/^([A-Za-z]+)\s+(\d{1,2}),\s*(\d{4})$/);
    if (match) {
      const month = reportMonthIndex(match[1]);
      if (month >= 0) {
        const iso = isoDateLocal(new Date(Number(match[3]), month, Number(match[2])));
        return { start:iso, end:iso };
      }
    }

    return { start:fallback, end:fallback };
  }

  function formatReportDateRange(startIso, endIso){
    const start = parseIsoDateLocal(startIso);
    const end = parseIsoDateLocal(endIso || startIso);
    if (!startIso || isNaN(start) || isNaN(end)) return '';
    const month = d => d.toLocaleDateString('en-US', { month:'long' });
    const day = d => d.getDate();
    const year = d => d.getFullYear();
    if (startIso === (endIso || startIso)) {
      return `${month(start)} ${day(start)}, ${year(start)}`;
    }
    if (start.getFullYear() === end.getFullYear() && start.getMonth() === end.getMonth()) {
      return `${month(start)} ${day(start)} - ${day(end)}, ${year(start)}`;
    }
    if (start.getFullYear() === end.getFullYear()) {
      return `${month(start)} ${day(start)} - ${month(end)} ${day(end)}, ${year(start)}`;
    }
    return `${month(start)} ${day(start)}, ${year(start)} - ${month(end)} ${day(end)}, ${year(end)}`;
  }

  function reportMonthIndex(name){
    return ['january','february','march','april','may','june','july','august','september','october','november','december']
      .indexOf(String(name || '').toLowerCase());
  }

  function normalizeIsoDate(value){
    const text = String(value || '').trim();
    return /^\d{4}-\d{2}-\d{2}$/.test(text) ? text : '';
  }

  function parseIsoDateLocal(iso){
    const [y, m, d] = String(iso || '').split('-').map(Number);
    return new Date(y || 1970, (m || 1) - 1, d || 1);
  }

  function isoDateLocal(date){
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  function bindReportPhotoControls(){
    const input = document.getElementById('arPhotoInput');
    const clearBtn = document.getElementById('arPhotoClearBtn');
    if (input) {
      input.addEventListener('change', async () => {
        const file = input.files && input.files[0] ? input.files[0] : null;
        input.value = '';
        if (!file) return;
        if (!/^image\//i.test(file.type)) {
          toast('Choose an image file for photo documentation.', 'error');
          return;
        }
        try {
          const dataUrl = await resizeReportPhoto(file);
          setReportPhoto(dataUrl);
          markReportDirty();
          renderPrintPreview();
        } catch (err) {
          console.error(err);
          toast('Could not read the selected photo.', 'error');
        }
      });
    }
    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        setReportPhoto('');
        markReportDirty();
        renderPrintPreview();
      });
    }
  }

  function resizeReportPhoto(file){
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onerror = reject;
      reader.onload = () => {
        const img = new Image();
        img.onerror = reject;
        img.onload = () => {
          const maxSide = 1400;
          const scale = Math.min(1, maxSide / Math.max(img.width, img.height));
          const canvas = document.createElement('canvas');
          canvas.width = Math.max(1, Math.round(img.width * scale));
          canvas.height = Math.max(1, Math.round(img.height * scale));
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
          resolve(canvas.toDataURL('image/jpeg', 0.82));
        };
        img.src = String(reader.result || '');
      };
      reader.readAsDataURL(file);
    });
  }

  function setReportPhoto(dataUrl){
    const value = String(dataUrl || '');
    const hidden = document.getElementById('arPhotoDocumentation');
    const preview = document.getElementById('arPhotoPreview');
    const empty = document.getElementById('arPhotoEmpty');
    const clearBtn = document.getElementById('arPhotoClearBtn');
    if (hidden) hidden.value = value;
    if (preview) {
      preview.src = value || '';
      preview.style.display = value ? 'block' : 'none';
    }
    if (empty) empty.style.display = value ? 'none' : 'block';
    if (clearBtn) clearBtn.style.display = value ? 'inline-flex' : 'none';
  }

  function renderReportPhoto(){
    const value = valueOf('arPhotoDocumentation');
    const box = document.getElementById('arPrintPhotoBox');
    const img = document.getElementById('arPrintPhoto');
    if (!box || !img) return;
    box.classList.toggle('has-photo', !!value);
    img.src = value || '';
  }

  function switchReportView(view){
    const isPreview = view === 'preview';
    document.body.classList.toggle('report-preview-mode', isPreview);
    const navView = view === 'preview' ? 'accomplishment' : view;
    document.querySelectorAll('.report-view-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.reportView === navView);
    });
    document.querySelectorAll('.report-view').forEach(panel => {
      panel.classList.toggle('active', panel.id === `reportView${ucFirst(view)}`);
    });
    if ((view === 'accomplishment' || view === 'analytics' || view === 'preview') && !arLoaded && !arLoading) loadReportBootstrap();
    if (view === 'accomplishment') updateReportWizard();
    if (view === 'analytics') renderReportAnalytics();
    if (isPreview) {
      renderPrintPreview();
      const reportTab = document.getElementById('tab-report');
      if (reportTab) reportTab.scrollTo({ top:0, left:0 });
      window.scrollTo({ top:0, left:0 });
    }
  }

  async function loadReportBootstrap(){
    if (arLoading) return;
    arLoading = true;
    setSaveState('Loading report data...');
    try {
      const res = await fetch(`${API}?action=bootstrap&class_id=${encodeURIComponent(CLASS_ID)}&_t=${Date.now()}`, { cache:'no-store' });
      const data = await res.json();
      if (data.status !== 'success') throw new Error(data.message || 'Report data failed');
      arState.context = data.context || {};
      arState.sessions = Array.isArray(data.sessions) ? data.sessions : [];
      arState.analytics = Array.isArray(data.analytics) ? data.analytics : [];
      arState.today = data.today || null;
      arLoaded = true;
      renderReportContext();
      renderReportAnalytics();
      renderSessionOptions();
      if (arState.sessions.length) {
        const current = document.getElementById('arSessionSelect').value || arState.sessions[0].id;
        document.getElementById('arSessionSelect').value = current;
        await loadReportSession(current);
      } else {
        clearReportForm();
        setSaveState('No recorded sessions');
      }
    } catch (err) {
      console.error(err);
      setSaveState('Report data unavailable');
      toast('Network error loading report data', 'error');
    } finally {
      arLoading = false;
    }
  }

  function renderReportContext(){
    const c = arState.context || {};
    const label = [c.subject_name, c.section_label, c.semester_ay].filter(Boolean).join(' | ');
    const el = document.getElementById('reportContextLabel');
    if (el) el.textContent = label || 'Attendance and accomplishment report';
  }

  function renderSessionOptions(){
    const select = document.getElementById('arSessionSelect');
    if (!select) return;
    if (!arState.sessions.length) {
      select.innerHTML = '<option value="">No recorded sessions yet</option>';
      select.disabled = true;
      return;
    }
    select.disabled = false;
    select.innerHTML = arState.sessions.map(session => {
      const status = session.report_id ? ` - ${session.report_status || 'draft'}` : '';
      return `<option value="${escapeAttr(session.id)}">${escapeHtml(session.date_label)} - ${session.percentage}% present${status}</option>`;
    }).join('');
  }

  async function loadReportSession(sessionId){
    if (!sessionId) return;
    setSaveState('Loading session...');
    try {
      const res = await fetch(`${API}?action=get_session&class_id=${encodeURIComponent(CLASS_ID)}&session_id=${encodeURIComponent(sessionId)}&_t=${Date.now()}`, { cache:'no-store' });
      const data = await res.json();
      if (data.status !== 'success') throw new Error(data.message || 'Session failed');
      arState.current = data;
      arState.analytics = Array.isArray(data.analytics) ? data.analytics : arState.analytics;
      fillReportForm(data);
      arDirty = false;
      setSaveState(data.report ? 'Existing draft loaded' : 'New draft');
      renderReportAnalytics();
      renderPrintPreview();
    } catch (err) {
      console.error(err);
      toast('Network error loading session', 'error');
      setSaveState('Session unavailable');
    }
  }

  function clearReportForm(){
    ['arReportId','arSessionId','arFacultyName','arSubjectName','arSection','arDepartmentName','arEmploymentStatus','arClassSize','arAttendees','arAbsent','arDateConducted','arSemesterAy','arAcademicWeek','arUnits','arDateCovered','arDateCoveredStart','arDateCoveredEnd','arTimeConducted','arDuration','arTopicsCovered','arSyncActivities','arAsyncActivities','arLabActivities','arPhotoDocumentation','arFacultySignature','arDeanName','arDateSubmitted','arHrdReceivedDate'].forEach(id => setVal(id, ''));
    arDatePickerStart = '';
    arDatePickerEnd = '';
    setReportPhoto('');
  }

  function fillReportForm(data){
    const auto = data.auto || {};
    const report = data.report || {};
    const c = arState.context || {};
    setVal('arReportId', report.id || '');
    setVal('arSessionId', data.session?.id || '');
    setVal('arFacultyName', auto.faculty_name || c.faculty_name || '');
    setVal('arSubjectName', auto.subject_name || c.subject_name || '');
    setVal('arSection', auto.section_label || c.section_label || '');
    setVal('arDepartmentName', auto.department_name || c.department_name || '');
    setVal('arEmploymentStatus', auto.employment_status || c.employment_status || 'Not set');
    setVal('arClassSize', auto.class_size ?? c.class_size ?? '');
    setVal('arAttendees', auto.attendees_count ?? '');
    setVal('arAbsent', auto.absent_count ?? '');
    setVal('arDateConducted', auto.date_conducted || '');
    setVal('arSemesterAy', auto.semester_ay || c.semester_ay || '');

    setVal('arAcademicWeek', report.academic_week || '');
    setVal('arUnits', report.units || '');
    const coveredLabel = report.date_covered || auto.date_conducted || '';
    const coveredRange = parseReportDateRangeInput(coveredLabel, auto.date_conducted_raw || data.session?.attendance_date || '');
    setReportDateCoveredRange(coveredLabel, coveredRange.start, coveredRange.end);
    setVal('arTimeConducted', report.time_conducted || '');
    setVal('arDuration', report.duration || '');
    setVal('arTopicsCovered', report.topics_covered || '');
    setVal('arSyncActivities', report.sync_activities || '');
    setVal('arAsyncActivities', report.async_activities || '');
    setVal('arLabActivities', report.lab_activities || '');
    setReportPhoto(report.photo_documentation || '');
    setVal('arFacultySignature', report.faculty_signature || auto.faculty_name || c.faculty_name || '');
    setVal('arDeanName', report.dean_name || c.dean_name || '');
    setVal('arDateSubmitted', report.date_submitted || '');
    setVal('arHrdReceivedDate', report.hrd_received_date || '');

    const note = document.getElementById('arSessionNote');
    if (note) {
      const pct = data.session?.percentage ?? 0;
      const label = data.session?.date_label || 'Selected session';
      note.textContent = `${label} | ${pct}% present | ${auto.attendees_count || 0}/${auto.class_size || 0} attendees`;
    }
  }

  function renderReportAnalytics(){
    const rows = Array.isArray(arState.analytics) ? arState.analytics : [];
    const summaryEl = document.getElementById('raSummaryCards');
    const scatterEl = document.getElementById('raScatterPlot');
    const clusterEl = document.getElementById('raClusterCards');
    const statsEl = document.getElementById('raStatsRows');
    if (!summaryEl || !scatterEl || !clusterEl || !statsEl) return;

    const clusterDefs = {
      high: { label:'High Performers', dot:'ra-high', text:'#166534', icon:'fa-circle-check', recommendation:'Sustain enrichment and peer mentoring.' },
      average: { label:'Average', dot:'ra-average', text:'#92400e', icon:'fa-arrow-trend-up', recommendation:'Monitor progress and reinforce weak topics.' },
      at_risk: { label:'At Risk', dot:'ra-risk', text:'#b91c1c', icon:'fa-triangle-exclamation', recommendation:'Prioritize intervention and attendance follow-up.' }
    };
    const keyOf = (row) => {
      const raw = String(row.cluster_key || row.cluster || '').toLowerCase().replace(/\s+/g, '_');
      return raw === 'risk' ? 'at_risk' : (clusterDefs[raw] ? raw : 'average');
    };
    const grouped = { high:[], average:[], at_risk:[] };
    rows.forEach(row => grouped[keyOf(row)].push(row));

    const card = (label, value, sub, color='var(--text)') => `
      <div class="ra-summary-card">
        <div class="ra-summary-label">${label}</div>
        <div class="ra-summary-value" style="color:${color};">${value}</div>
        <div class="ra-summary-sub">${sub}</div>
      </div>`;
    summaryEl.innerHTML = [
      card('Total students', rows.length, 'enrolled'),
      card('High performers', grouped.high.length, 'students', '#166534'),
      card('Average', grouped.average.length, 'students', '#92400e'),
      card('At risk', grouped.at_risk.length, 'students', '#b91c1c')
    ].join('');

    if (!rows.length) {
      scatterEl.innerHTML = '<div class="ra-empty">No analytics available yet. Add attendance and graded submissions to populate clustering.</div>';
      clusterEl.innerHTML = '';
      statsEl.innerHTML = '<tr><td colspan="5">No analytics available.</td></tr>';
      return;
    }

    const clamp = (n) => Math.max(0, Math.min(100, Number(n || 0)));
    scatterEl.innerHTML = `
      <div class="ra-axis-label ra-axis-x">Attendance %</div>
      <div class="ra-axis-label ra-axis-y">Grade %</div>
      ${rows.map(row => {
        const key = keyOf(row);
        const x = clamp(row.attendance_rate);
        const y = clamp(row.grade_average);
        const title = `${row.student_name || 'Student'}: ${x}% attendance | ${y}% grade | ${row.cluster || clusterDefs[key].label}`;
        return `<span class="ra-dot ${clusterDefs[key].dot}" style="left:${x}%;bottom:${y}%;" title="${escapeAttr(title)}"></span>`;
      }).join('')}
    `;

    clusterEl.innerHTML = Object.entries(clusterDefs).map(([key, cfg]) => {
      const group = grouped[key];
      return `
        <div class="ra-cluster-card">
          <div class="ra-cluster-head">
            <div class="ra-cluster-label" style="color:${cfg.text};"><i class="fas ${cfg.icon}"></i> ${cfg.label}</div>
            <div class="ra-cluster-count">${group.length}</div>
          </div>
          <div class="ra-student-list">
            ${group.length ? group.slice(0, 5).map(row => {
              const att = clamp(row.attendance_rate);
              const grade = clamp(row.grade_average);
              return `
                <div class="ra-student-row">
                  <div class="ra-student-name">${escapeHtml(row.student_name || 'Unknown')}</div>
                  <div class="ra-student-meta"><span>${att}% att</span><span>${grade}% grade</span></div>
                  <div class="ra-mini-track"><div class="ra-mini-fill ${cfg.dot}" style="width:${Math.round((att + grade) / 2)}%;"></div></div>
                </div>`;
            }).join('') : '<div class="ra-empty" style="padding:.75rem;">No students in this cluster</div>'}
          </div>
        </div>`;
    }).join('');

    statsEl.innerHTML = Object.entries(clusterDefs).map(([key, cfg]) => {
      const group = grouped[key];
      const avgAtt = group.length ? Math.round(group.reduce((sum, row) => sum + clamp(row.attendance_rate), 0) / group.length) : 0;
      const avgGrade = group.length ? Math.round((group.reduce((sum, row) => sum + clamp(row.grade_average), 0) / group.length) * 10) / 10 : 0;
      return `<tr><td style="color:${cfg.text};font-weight:800;">${cfg.label}</td><td>${group.length}</td><td>${avgAtt}%</td><td>${avgGrade}%</td><td>${cfg.recommendation}</td></tr>`;
    }).join('');
  }

  function collectReportPayload(){
    return {
      action:'save_report',
      class_id:CLASS_ID,
      session_id:valueOf('arSessionId'),
      academic_week:valueOf('arAcademicWeek'),
      units:valueOf('arUnits'),
      date_covered:valueOf('arDateCovered'),
      time_conducted:valueOf('arTimeConducted'),
      duration:valueOf('arDuration'),
      topics_covered:valueOf('arTopicsCovered'),
      sync_activities:valueOf('arSyncActivities'),
      async_activities:valueOf('arAsyncActivities'),
      lab_activities:valueOf('arLabActivities'),
      photo_documentation:valueOf('arPhotoDocumentation'),
      faculty_signature:valueOf('arFacultySignature'),
      dean_name:valueOf('arDeanName'),
      date_submitted:valueOf('arDateSubmitted'),
      hrd_received_date:valueOf('arHrdReceivedDate'),
      status:'draft'
    };
  }

  async function saveReportDraft({ silent=false, autosave=false } = {}){
    const sessionId = valueOf('arSessionId');
    if (!sessionId) {
      if (!silent) toast('Select a session first.', 'error');
      return false;
    }
    if (autosave && !arDirty) return true;
    setSaveState(autosave ? 'Autosaving...' : 'Saving draft...');
    try {
      const res = await fetch(API, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify(collectReportPayload())
      });
      const data = await res.json();
      if (data.status !== 'success') throw new Error(data.message || 'Save failed');
      setVal('arReportId', data.report_id || data.report?.id || '');
      arState.sessions = Array.isArray(data.sessions) ? data.sessions : arState.sessions;
      renderSessionOptions();
      const select = document.getElementById('arSessionSelect');
      if (select) select.value = sessionId;
      arDirty = false;
      setSaveState(`Saved ${new Date().toLocaleTimeString([], {hour:'numeric', minute:'2-digit'})}`);
      if (!silent) toast('Draft saved', 'success');
      renderPrintPreview();
      return true;
    } catch (err) {
      console.error(err);
      setSaveState('Save failed');
      if (!silent) toast(err.message || 'Save failed', 'error');
      return false;
    }
  }

  function renderPrintPreview(){
    const current = arState.current || {};
    const auto = current.auto || {};
    const c = arState.context || {};
    const data = {
      date_of_affectivity: auto.date_of_affectivity || c.date_of_affectivity || '',
      department_name: valueOr('arDepartmentName', c.department_name || ''),
      academic_week: valueOf('arAcademicWeek'),
      date_covered: valueOf('arDateCovered'),
      faculty_name: valueOr('arFacultyName', c.faculty_name || ''),
      subject_name: valueOr('arSubjectName', c.subject_name || ''),
      units: valueOf('arUnits'),
      section_label: valueOr('arSection', c.section_label || ''),
      employment_status: valueOr('arEmploymentStatus', c.employment_status || 'Not set'),
      class_size: valueOf('arClassSize'),
      attendees_count: valueOf('arAttendees'),
      absent_count: valueOf('arAbsent'),
      date_conducted: valueOf('arDateConducted'),
      time_conducted: valueOf('arTimeConducted'),
      duration: valueOf('arDuration'),
      topics_covered: valueOf('arTopicsCovered'),
      sync_activities: valueOf('arSyncActivities'),
      async_activities: valueOf('arAsyncActivities'),
      lab_activities: valueOf('arLabActivities'),
      dean_name: valueOf('arDeanName'),
      date_submitted: formatDateForPrint(valueOf('arDateSubmitted')),
      hrd_received_date: formatDateForPrint(valueOf('arHrdReceivedDate')),
      semester_ay: valueOr('arSemesterAy', c.semester_ay || ''),
      generated_on: new Date().toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric'})
    };

    document.querySelectorAll('#arPrintPreview [data-print]').forEach(el => {
      const key = el.dataset.print;
      el.textContent = data[key] || '';
    });
    renderReportPhoto();

    const present = current.present || [];
    const absent = current.absent || [];
    const classSize = Number(data.class_size || 0);
    const rows = Math.max(classSize, present.length, absent.length, 1);
    const tbody = document.getElementById('arPrintAttendanceRows');
    if (tbody) {
      tbody.innerHTML = Array.from({length: rows}, (_, idx) => `
        <tr>
          <td>${idx + 1}</td>
          <td>${escapeHtml(present[idx]?.full_name || '')}</td>
          <td>${idx + 1}</td>
          <td>${escapeHtml(absent[idx]?.full_name || '')}</td>
        </tr>
      `).join('');
    }

    const analyticsBody = document.getElementById('arPrintAnalyticsRows');
    const analytics = arState.analytics || [];
    if (analyticsBody) {
      analyticsBody.innerHTML = analytics.length ? analytics.map(row => {
        const clusterClass = row.cluster === 'High' ? 'ar-cluster-high' : (row.cluster === 'Average' ? 'ar-cluster-average' : 'ar-cluster-risk');
        return `<tr><td>${escapeHtml(row.student_name || '')}</td><td>${Number(row.attendance_rate || 0).toFixed(1).replace('.0','')}%</td><td>${Number(row.grade_average || 0).toFixed(1).replace('.0','')}%</td><td class="${clusterClass}">${escapeHtml(row.cluster || '')}</td><td>${escapeHtml(row.remarks || '')}</td></tr>`;
      }).join('') : '<tr><td colspan="5">No analytics available.</td></tr>';
    }
  }

  function markReportDirty(){
    arDirty = true;
    setSaveState('Unsaved changes');
  }
  function setSaveState(text){ const el = document.getElementById('arSaveState'); if (el) el.textContent = text; }
  function setVal(id, value){ const el = document.getElementById(id); if (el) el.value = value == null ? '' : String(value); }
  function valueOf(id){ const el = document.getElementById(id); return el ? String(el.value || '').trim() : ''; }
  function valueOr(id, fallback){ const v = valueOf(id); return v || fallback || ''; }
  function ucFirst(s){ return String(s || '').charAt(0).toUpperCase() + String(s || '').slice(1); }
  function escapeHtml(s){ return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
  function escapeAttr(s){ return escapeHtml(s).replace(/`/g, '&#96;'); }
  function formatDateForPrint(s){
    if (!s) return '';
    const d = new Date(`${s}T00:00:00`);
    return isNaN(d) ? s : d.toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric'});
  }
})();

/* ══════════════════════════════════════════
   ATTENDANCE TAB MODULE
══════════════════════════════════════════ */
(function(){
  let attInited = false, attSemester = null, attToday = null;
  let attCurYear = null, attCurMonth = null;
  let attMonthData = {}, attMonthStudents = [], attModalState = null, attLoadingMonth = false;

  // Lazy-init when faculty first clicks the Attendance tab
  const tabBtn = document.getElementById('reportTabBtn');
  if (tabBtn) {
    tabBtn.addEventListener('click', () => {
      if (!attInited) { attInited = true; attInit(); }
    });
  }

  async function attInit(){
    bindControls();
    try {
      const r = await fetch(`API/facultyUI/classroom/attendance/get_semester_range.php?class_id=${encodeURIComponent(CLASS_ID)}`);
      const j = await r.json();
      if (j.status !== 'success') { toast(j.message || 'Failed to load semester', 'error'); return; }
      attSemester = j.semester;
      attToday    = j.today;
      document.getElementById('attSemesterLabel').textContent =
        `${attSemester.label} | ${formatLongDate(attSemester.start_date)} - ${formatLongDate(attSemester.end_date)}`;

      // Default month: June 2026 for the exported report design, then fall back to today/semester start.
      const td = parseDate(attToday), sd = parseDate(attSemester.start_date), ed = parseDate(attSemester.end_date);
      const june2026 = new Date(2026, 5, 1);
      const seed = (june2026 >= sd && june2026 <= ed) ? june2026 : ((td >= sd && td <= ed) ? td : sd);
      attCurYear  = seed.getFullYear();
      attCurMonth = seed.getMonth() + 1;
      await loadAndRenderMonth();
    } catch(e) {
      console.error(e); toast('Network error loading attendance', 'error');
    }
  }

  function bindControls(){
    document.getElementById('attPrevMonth').addEventListener('click', () => navMonth(-1));
    document.getElementById('attNextMonth').addEventListener('click', () => navMonth(+1));
    document.getElementById('attTodayBtn').addEventListener('click', goToToday);
    document.getElementById('attLogSessionBtn')?.addEventListener('click', () => {
      if (!attToday) return;
      openAttModal(attToday);
    });
    document.getElementById('attSearchInput').addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });
    document.getElementById('attSearchInput').addEventListener('input', formatSearchInput);
    document.getElementById('attModalClose').addEventListener('click', closeAttModal);
    document.getElementById('attCancelBtn').addEventListener('click', closeAttModal);
    document.getElementById('attModalOverlay').addEventListener('click', closeAttModal);
    document.getElementById('attMarkAllPresent').addEventListener('click', () => bulkMark('present'));
    document.getElementById('attMarkAllAbsent').addEventListener('click',  () => bulkMark('absent'));
    document.getElementById('attSaveBtn').addEventListener('click', saveAttendance);
    document.getElementById('attExportPdf').addEventListener('click',   () => toast('PDF export coming soon',   'info'));
    document.getElementById('attExportExcel').addEventListener('click', () => toast('Excel export coming soon', 'info'));

    // Keyboard shortcuts (only when Attendance tab is active)
    document.addEventListener('keydown', e => {
      if (!document.getElementById('tab-report').classList.contains('active')) return;
      if (e.key === 'Escape' && document.getElementById('attModal').classList.contains('open')) {
        closeAttModal(); return;
      }
      const tag = (e.target.tagName || '').toLowerCase();
      if (tag === 'input' || tag === 'textarea' || tag === 'select') return;
      if (e.key === 'ArrowLeft')  navMonth(-1);
      if (e.key === 'ArrowRight') navMonth(+1);
      if (e.key === 't' || e.key === 'T') goToToday();
    });
  }

  // ── Calendar ──
  async function loadAndRenderMonth(){
    if (attLoadingMonth) return;
    attLoadingMonth = true;
    document.getElementById('attMonthLabel').textContent = monthName(attCurMonth) + ' ' + attCurYear;
    document.getElementById('attDaysGrid').innerHTML =
      `<div style="grid-column:1/-1;text-align:center;padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin"></i> Loading...</div>`;
    try {
      const graphEl = document.getElementById('attStudentGraph');
      const absenceEl = document.getElementById('attAbsenceList');
      if (graphEl) graphEl.innerHTML = `<div class="att-empty-state"><i class="fas fa-spinner fa-spin"></i> Loading student monitoring...</div>`;
      if (absenceEl) absenceEl.innerHTML = `<div class="att-empty-state"><i class="fas fa-spinner fa-spin"></i> Preparing monthly absence data...</div>`;
      const r = await fetch(`API/facultyUI/classroom/attendance/get_month_attendance.php?class_id=${encodeURIComponent(CLASS_ID)}&year=${attCurYear}&month=${attCurMonth}`);
      const j = await r.json();
      attMonthData = (j.status === 'success') ? (j.days || {}) : {};
      attMonthStudents = (j.status === 'success' && Array.isArray(j.students)) ? j.students : [];
    } catch(e){ attMonthData = {}; attMonthStudents = []; console.error(e); }
    renderCalendarGrid();
    renderMonthStats();
    renderStudentInsights();
    attLoadingMonth = false;
  }

  function renderCalendarGrid(){
    const grid = document.getElementById('attDaysGrid');
    const firstDay = new Date(attCurYear, attCurMonth - 1, 1);
    const lastDay  = new Date(attCurYear, attCurMonth, 0);
    const firstWeekday = firstDay.getDay();
    const daysInMonth  = lastDay.getDate();
    const sd = parseDate(attSemester.start_date);
    const ed = parseDate(attSemester.end_date);
    const td = parseDate(attToday);

    let html = '';
    for (let i = 0; i < firstWeekday; i++) html += `<div class="att-day att-day-empty"></div>`;

    for (let d = 1; d <= daysInMonth; d++) {
      const dateObj = new Date(attCurYear, attCurMonth - 1, d);
      const iso     = isoDate(dateObj);
      const inSem   = dateObj >= sd && dateObj <= ed;
      const notFut  = dateObj <= td;
      const allowed = inSem && notFut;
      const isToday = iso === attToday;
      const data    = attMonthData[iso];

      let cls = 'att-day';
      if (!allowed) cls += ' att-day-disabled';
      if (isToday)  cls += ' att-day-today';
      if (data)     cls += ` att-day-recorded att-day-${data.tier}`;

      let inner = `<div class="att-day-head"><div class="att-day-num">${d}</div></div>`;
      if (data) {
        inner += `<div class="att-day-foot"><div class="att-day-pct">${data.percentage}%</div></div>`;
      }

      let tipText;
      if (data)         tipText = `${data.present} present | ${data.absent} absent | ${data.percentage}%`;
      else if (allowed) tipText = `No record yet - click to mark`;
      else if (!inSem)  tipText = `Outside semester`;
      else              tipText = `Future date`;

      html += `<div class="${cls}" data-date="${iso}">${inner}<div class="att-tooltip">${tipText}</div></div>`;
    }

    grid.innerHTML = html;
    grid.querySelectorAll('.att-day:not(.att-day-empty):not(.att-day-disabled)').forEach(cell => {
      cell.addEventListener('click', () => openAttModal(cell.dataset.date));
    });
  }

  function renderMonthStats(){
    const sessions = Object.keys(attMonthData).length;
    const avgEl = document.getElementById('attMonthAvg');
    const sessionsEl = document.getElementById('attMonthSessions');
    const bestDayEl = document.getElementById('attMonthBestDay');
    const lowestDayEl = document.getElementById('attMonthLowestDay');
    const absencesEl = document.getElementById('attMonthAbsences');
    const riskEl = document.getElementById('attMonthRiskCount');
    if (sessionsEl) sessionsEl.textContent = String(sessions);

    if (!sessions) {
      if (avgEl) avgEl.textContent = '--';
      if (bestDayEl) bestDayEl.textContent = '--';
      if (lowestDayEl) lowestDayEl.textContent = '--';
      if (absencesEl) absencesEl.textContent = '0';
      if (riskEl) riskEl.textContent = '0';
      return;
    }

    const dayValues = Object.entries(attMonthData);
    const sum = dayValues.reduce((a, [, b]) => a + Number(b.percentage || 0), 0);
    const totalAbsences = dayValues.reduce((a, [, b]) => a + Number(b.absent || 0), 0);
    const bestDay = [...dayValues].sort((a, b) => Number(b[1].percentage || 0) - Number(a[1].percentage || 0))[0];
    const lowestDay = [...dayValues].sort((a, b) => Number(a[1].percentage || 0) - Number(b[1].percentage || 0))[0];
    const riskCount = (attMonthStudents || []).filter(s => Number(s.total_records || 0) > 0 && Number(s.attendance_percentage || 0) < 80).length;

    if (avgEl) avgEl.textContent = Math.round(sum / sessions) + '%';
    if (bestDayEl) bestDayEl.textContent = `${formatShortDate(bestDay[0])} | ${bestDay[1].percentage}%`;
    if (lowestDayEl) lowestDayEl.textContent = `${formatShortDate(lowestDay[0])} | ${lowestDay[1].percentage}%`;
    if (absencesEl) absencesEl.textContent = String(totalAbsences);
    if (riskEl) riskEl.textContent = String(riskCount);
  }

  function renderStudentInsights(){
    const graphEl = document.getElementById('attStudentGraph');
    const absenceEl = document.getElementById('attAbsenceList');
    if (!graphEl || !absenceEl) return;

    const activeStudents = (attMonthStudents || [])
      .map(s => ({
        ...s,
        total_records: Number(s.total_records || 0),
        present_count: Number(s.present_count || 0),
        absent_count: Number(s.absent_count || 0),
        attendance_percentage: s.attendance_percentage == null ? null : Number(s.attendance_percentage)
      }))
      .filter(s => s.total_records > 0);

    if (!activeStudents.length) {
      graphEl.innerHTML = `<div class="att-empty-state">No student attendance records yet for this month.</div>`;
      absenceEl.innerHTML = `<div class="att-empty-state">Absence monitoring will appear after the first saved session.</div>`;
      return;
    }

    const topPresence = [...activeStudents]
      .sort((a, b) => (b.attendance_percentage - a.attendance_percentage) || (a.absent_count - b.absent_count))
      .slice(0, 6);

    graphEl.innerHTML = topPresence.map(student => {
      const pct = Math.max(0, Math.min(100, Math.round(student.attendance_percentage)));
      const fillClass = pct >= 80 ? '' : (pct >= 50 ? ' is-mid' : ' is-low');
      return `
        <div class="att-student-bar">
          <div class="att-student-bar-head">
            <div class="att-student-name-wrap">
              <div class="att-student-bar-name">${escapeHtml(student.full_name)}</div>
              <div class="att-student-bar-meta">${escapeHtml(student.student_number || 'No student number')} - ${student.present_count}/${student.total_records} present</div>
            </div>
            <div class="att-student-bar-value">${pct}%</div>
          </div>
          <div class="att-student-track">
            <div class="att-student-fill${fillClass}" style="width:${pct}%;"></div>
          </div>
        </div>`;
    }).join('');

    const mostAbsences = [...activeStudents]
      .filter(student => student.absent_count > 0)
      .sort((a, b) => (b.absent_count - a.absent_count) || (a.attendance_percentage - b.attendance_percentage))
      .slice(0, 6);

    if (!mostAbsences.length) {
      absenceEl.innerHTML = `<div class="att-empty-state">No absences recorded for this month.</div>`;
      return;
    }

    absenceEl.innerHTML = mostAbsences.map(student => `
      <div class="att-absence-row">
        <div class="att-absence-main">
          <div class="att-absence-name">${escapeHtml(student.full_name)}</div>
          <div class="att-absence-meta">${escapeHtml(student.student_number || 'No student number')} - ${Math.round(student.attendance_percentage || 0)}% attendance</div>
        </div>
        <div class="att-absence-badge">${student.absent_count}</div>
      </div>
    `).join('');
  }

  function navMonth(delta){
    let m = attCurMonth + delta, y = attCurYear;
    if (m < 1)  { m = 12; y--; }
    if (m > 12) { m = 1;  y++; }
    attCurMonth = m; attCurYear = y;
    loadAndRenderMonth();
  }

  function goToToday(){
    const td = parseDate(attToday);
    attCurYear = td.getFullYear();
    attCurMonth = td.getMonth() + 1;
    loadAndRenderMonth();
  }

  // ── Search ──
  function formatSearchInput(e){
    let v = e.target.value.replace(/[^\d]/g, '');
    if      (v.length >= 5) v = v.slice(0,2)+'/'+v.slice(2,4)+'/'+v.slice(4,8);
    else if (v.length >= 3) v = v.slice(0,2)+'/'+v.slice(2);
    e.target.value = v;
    e.target.classList.remove('invalid');
  }

  function doSearch(){
    const inp = document.getElementById('attSearchInput');
    const m = inp.value.trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!m) { inp.classList.add('invalid'); toast('Use MM/DD/YYYY format', 'warning'); return; }
    const mo = +m[1], dy = +m[2], yr = +m[3];
    const dateObj = new Date(yr, mo - 1, dy);
    if (dateObj.getFullYear() !== yr || dateObj.getMonth() !== mo - 1 || dateObj.getDate() !== dy) {
      inp.classList.add('invalid'); toast('Invalid date', 'warning'); return;
    }
    const iso = isoDate(dateObj);
    attCurYear = yr; attCurMonth = mo;
    loadAndRenderMonth().then(() => {
      const cell = document.querySelector(`.att-day[data-date="${iso}"]`);
      if (cell) {
        cell.classList.add('att-day-searched');
        cell.scrollIntoView({behavior:'smooth', block:'center'});
        setTimeout(() => cell.classList.remove('att-day-searched'), 2000);
      }
    });
  }

  // ── Modal ──
  async function openAttModal(date){
    const overlay = document.getElementById('attModalOverlay');
    const modal   = document.getElementById('attModal');
    document.getElementById('attModalDate').textContent = formatLongDate(date);
    document.getElementById('attModalSub').textContent  = 'Loading…';
    document.getElementById('attModalBody').innerHTML =
      `<div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin"></i></div>`;
    overlay.classList.add('show');
    modal.classList.add('open');
    modal.style.display = 'flex';

    try {
      const r = await fetch(`API/facultyUI/classroom/attendance/get_attendance_detail.php?class_id=${encodeURIComponent(CLASS_ID)}&date=${encodeURIComponent(date)}`);
      const j = await r.json();
      if (j.status !== 'success') { toast(j.message || 'Failed to load', 'error'); closeAttModal(); return; }
      attModalState = { date, students: j.students, can_edit: j.can_edit, has_session: !!j.session };
      renderModalBody();
      const sub = j.session
        ? `Last saved ${formatStamp(j.session.updated_at || j.session.created_at)} by ${j.session.updated_by_username || j.session.created_by_username || 'unknown'}`
        : (j.can_edit ? 'No record yet - all students start as Present. Toggle individuals to Absent.'
                      : 'Read-only (date is outside semester or in the future).');
      document.getElementById('attModalSub').textContent = sub;
      document.getElementById('attSaveBtn').disabled = !j.can_edit;
      document.getElementById('attMarkAllPresent').disabled = !j.can_edit;
      document.getElementById('attMarkAllAbsent').disabled  = !j.can_edit;
    } catch(e) { console.error(e); toast('Network error', 'error'); closeAttModal(); }
  }

  function renderModalBody(){
    const body = document.getElementById('attModalBody');
    if (!attModalState.students.length) {
      body.innerHTML = `<div style="text-align:center;padding:2rem;color:var(--text-muted);">No enrolled students.</div>`;
      updateModalCounts(); return;
    }
    const dis = !attModalState.can_edit;
    body.innerHTML = attModalState.students.map((s, idx) => {
      const initials = ((s.first_name||'?')[0] + (s.last_name||'')[0]).toUpperCase();
      const isP = s.status === 'present';
      return `
        <div class="att-student-row" data-idx="${idx}">
          <div class="att-student-av">${escapeHtml(initials)}</div>
          <div class="att-student-info">
            <div class="att-student-name">${escapeHtml(s.full_name)}</div>
            <div class="att-student-meta">${escapeHtml(s.student_number || '')}</div>
          </div>
          <div class="att-toggle">
            <button class="att-toggle-opt opt-present ${isP?'active':''}" data-status="present" ${dis?'disabled':''}><i class="fas fa-check"></i> Present</button>
            <button class="att-toggle-opt opt-absent  ${!isP?'active':''}" data-status="absent"  ${dis?'disabled':''}><i class="fas fa-times"></i> Absent</button>
          </div>
        </div>`;
    }).join('');
    body.querySelectorAll('.att-toggle-opt').forEach(btn => {
      btn.addEventListener('click', () => {
        if (dis) return;
        const row = btn.closest('.att-student-row');
        const idx = +row.dataset.idx;
        attModalState.students[idx].status = btn.dataset.status;
        row.querySelectorAll('.att-toggle-opt').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        updateModalCounts();
      });
    });
    updateModalCounts();
  }

  function updateModalCounts(){
    const p = attModalState.students.filter(s => s.status === 'present').length;
    const a = attModalState.students.filter(s => s.status === 'absent').length;
    document.getElementById('attCountPresent').textContent = p;
    document.getElementById('attCountAbsent').textContent  = a;
  }

  function bulkMark(status){
    if (!attModalState || !attModalState.can_edit) return;
    attModalState.students.forEach(s => s.status = status);
    renderModalBody();
  }

  function closeAttModal(){
    document.getElementById('attModalOverlay').classList.remove('show');
    document.getElementById('attModal').classList.remove('open');
    document.getElementById('attModal').style.display = 'none';
    attModalState = null;
  }

  async function saveAttendance(){
    if (!attModalState || !attModalState.can_edit) return;
    const btn = document.getElementById('attSaveBtn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Saving…`;
    try {
      const records = attModalState.students.map(s => ({student_id: s.id, status: s.status}));
      const r = await fetch('API/facultyUI/classroom/attendance/save_attendance.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({class_id: CLASS_ID, date: attModalState.date, records})
      });
      const j = await r.json();
      if (j.status !== 'success') { toast(j.message || 'Save failed', 'error'); btn.disabled=false; btn.innerHTML=orig; return; }
      toast(j.created ? 'Attendance saved' : 'Attendance updated', 'success');
      await loadAndRenderMonth();
      document.dispatchEvent(new CustomEvent('attendance:saved'));
      closeAttModal();
    } catch(e) { console.error(e); toast('Network error saving', 'error'); btn.disabled=false; btn.innerHTML=orig; }
  }

  // ── Helpers ──
  function parseDate(s){ const [y,m,d] = s.split('-').map(Number); return new Date(y, m-1, d); }
  function isoDate(d){ const y=d.getFullYear(), m=String(d.getMonth()+1).padStart(2,'0'), dy=String(d.getDate()).padStart(2,'0'); return `${y}-${m}-${dy}`; }
  function monthName(m){ return ['January','February','March','April','May','June','July','August','September','October','November','December'][m-1]; }
  function formatShortDate(s){ return parseDate(s).toLocaleDateString('en-US',{month:'short',day:'numeric'}); }
  function formatLongDate(s){ return parseDate(s).toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'}); }
  function formatStamp(s){ if(!s) return ''; const d=new Date(String(s).replace(' ','T')); return isNaN(d) ? s : d.toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'}); }
  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
})();
</script>

<!-- ══════════════════════════════════════════════════
     PHASE 5 — MANAGE QUIZ MODAL (3 tabs)
     ══════════════════════════════════════════════════ -->
<div class="mq-overlay" id="mqOverlay" onclick="if(event.target===this)closeManageQuiz()"></div>
<div class="mq-modal" id="mqModal" role="dialog" aria-modal="true" aria-labelledby="mqTitle" data-post-id="">
  <div class="mq-head">
    <div class="mq-head-left">
      <div class="mq-head-icon"><i class="fa-solid fa-clipboard-question"></i></div>
      <div style="min-width:0;">
        <div class="mq-head-title" id="mqTitle">Manage Quiz</div>
        <div class="mq-head-sub" id="mqSubtitle">—</div>
      </div>
    </div>
    <div class="mq-head-right">
      <span class="mq-status-pill" id="mqStatusPill"><i class="fa-solid fa-circle"></i> <span id="mqStatusText">—</span></span>
      <button class="mq-close" type="button" onclick="closeManageQuiz()" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
  </div>

  <div class="mq-tabs" role="tablist">
    <button class="mq-tab active" type="button" role="tab" data-tab="settings" onclick="switchMqTab('settings')">
      <i class="fa-solid fa-sliders"></i> Settings &amp; Controls
    </button>
    <button class="mq-tab" type="button" role="tab" data-tab="submissions" onclick="switchMqTab('submissions')">
      <i class="fa-solid fa-users"></i> Submissions <span class="mq-tab-badge" id="mqSubBadge">0</span>
    </button>
    <button class="mq-tab" type="button" role="tab" data-tab="analytics" onclick="switchMqTab('analytics')">
      <i class="fa-solid fa-chart-column"></i> Analytics
    </button>
  </div>

  <div class="mq-body">
    <!-- Tab 1: Settings & Controls -->
    <div class="mq-pane active" id="mqPane-settings" role="tabpanel">
      <div class="mq-loading" id="mqSettingsLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
      <div id="mqSettingsContent" style="display:none;"></div>
    </div>

    <!-- Tab 2: Submissions -->
    <div class="mq-pane" id="mqPane-submissions" role="tabpanel">
      <div class="mq-sub-toolbar">
        <div class="mq-sub-search-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" class="mq-sub-search" id="mqSubSearch" placeholder="Search by name or student #…" oninput="filterSubmissions()">
        </div>
        <label class="mq-live-toggle" id="mqLiveLabel">
          <input type="checkbox" id="mqLiveToggle" onchange="toggleLiveMonitor()">
          <span class="mq-live-dot"></span>
          <span>Live</span>
        </label>
        <button class="mq-refresh-btn" type="button" onclick="loadSubmissions(true)"><i class="fa-solid fa-rotate"></i> Refresh</button>
      </div>
      <div class="mq-loading" id="mqSubLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
      <div class="mq-table-wrap" id="mqSubTableWrap" style="display:none;">
        <table class="mq-table">
          <thead>
            <tr>
              <th>Student</th>
              <th>Status</th>
              <th>Attempts</th>
              <th>Score</th>
              <th>Submitted</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="mqSubTbody"></tbody>
        </table>
      </div>
      <div id="mqSubEmpty" style="display:none;" class="mq-empty">
        <i class="fa-solid fa-users-slash"></i>
        <h4>No enrolled students</h4>
        <p>Once students enroll, they'll appear here.</p>
      </div>
    </div>

    <!-- Tab 3: Analytics -->
    <div class="mq-pane" id="mqPane-analytics" role="tabpanel">
      <div class="mq-loading" id="mqAnLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
      <div id="mqAnContent" style="display:none;"></div>
    </div>
  </div>
</div>

<!-- ══ STUDENT SUBMISSION DETAIL SUB-MODAL ══ -->
<div class="mq-overlay mq-overlay-2" id="mqDetailOverlay" onclick="if(event.target===this)closeSubmissionDetail()"></div>
<div class="mq-modal mq-modal-detail" id="mqDetailModal" role="dialog" aria-modal="true" data-student-id="" data-post-id="">
  <div class="mq-head">
    <div class="mq-head-left">
      <div class="mq-head-icon mq-head-icon-detail"><i class="fa-solid fa-user"></i></div>
      <div style="min-width:0;">
        <div class="mq-head-title" id="mqDetailTitle">Student Submission</div>
        <div class="mq-head-sub" id="mqDetailSub">—</div>
      </div>
    </div>
    <button class="mq-close" type="button" onclick="closeSubmissionDetail()" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="mq-body">
    <div class="mq-loading" id="mqDetailLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
    <div id="mqDetailContent" style="display:none;"></div>
  </div>
  <div class="mq-detail-footer" id="mqDetailFooter" style="display:none;">
    <button class="mq-btn mq-btn-ghost" type="button" onclick="closeSubmissionDetail()">Cancel</button>
    <button class="mq-btn mq-btn-primary" type="button" onclick="saveSubmissionOverrides()"><i class="fa-solid fa-floppy-disk"></i> Save Overrides</button>
  </div>
</div>
<script>
/* ══════════════════════════════════════════════════════
   STEP 11 — Publish dialog + Manage Quiz button hook
   Depends on: SweetAlert2, jQuery, fetch
   Exposes: window.openPublishDialog(postId, postTitle)
            window.renderQuizPostActions(post)  -> HTML string
            window.refreshQuizPostPill(postId, fields)
   ══════════════════════════════════════════════════════ */
(function(){
  const API_PUBLISH = 'API/facultyUI/classroom/quiz/publish_quiz.php';
  const CURRENT_USER_ID = <?php echo json_encode($_SESSION['user_id']); ?>;

  // ── Status pill text/class derivation (single source of truth)
  window.deriveQuizStatus = function(post){
    const pub      = parseInt(post.is_published || 0, 10) === 1;
    const closed   = parseInt(post.is_force_closed || 0, 10) === 1;
    const released = !!post.results_released_at;
    if (!pub) return { key:'draft',  label:'Draft',  cls:'pc-status-draft'  };
    if (closed) return { key:'closed', label:'Closed', cls:'pc-status-closed', released };
    return { key:'open', label:'Open', cls:'pc-status-open', released };
  };

  // ── Render the action row for a quiz post card (call from your post renderer)
  window.renderQuizPostActions = function(post){
    if (String(post.author_id) !== String(CURRENT_USER_ID)) return '';
    if (parseInt(post.post_type_id, 10) !== 3) return '';

    const st = window.deriveQuizStatus(post);
    const pub = parseInt(post.is_published || 0, 10) === 1;
    const safeId = String(post.id).replace(/"/g,'&quot;');
    const safeTitle = String(post.title || 'Quiz').replace(/"/g,'&quot;');

    const btn = pub
      ? `<button type="button" class="pc-manage-quiz-btn" data-quiz-id="${safeId}" data-quiz-title="${safeTitle}">
           <i class="fa-solid fa-gear"></i> Manage Quiz
         </button>`
      : `<button type="button" class="pc-publish-btn" data-quiz-id="${safeId}" data-quiz-title="${safeTitle}">
           <i class="fa-solid fa-paper-plane"></i> Publish
         </button>`;

    const chip = `<span class="pc-status-chip ${st.cls}" data-status-for="${safeId}">${st.label}</span>`;
    const rel  = st.released
      ? `<span class="pc-status-chip pc-status-released" data-released-for="${safeId}"><i class="fa-solid fa-eye"></i> Released</span>`
      : '';

    return `<div class="pc-quiz-actions" data-quiz-actions="${safeId}">${btn}${chip}${rel}</div>`;
  };

  // ── Update a card's pill in place (no full re-render needed)
  window.refreshQuizPostPill = function(postId, fields){
    const wrap = document.querySelector(`[data-quiz-actions="${postId}"]`);
    if (!wrap) return;
    // Merge minimal post object for status derivation
    const merged = Object.assign({
      id: postId,
      author_id: CURRENT_USER_ID,
      post_type_id: 3,
      is_published: 0,
      is_force_closed: 0,
      results_released_at: null,
      title: wrap.querySelector('[data-quiz-id]')?.dataset.quizTitle || 'Quiz'
    }, fields || {});
    wrap.outerHTML = window.renderQuizPostActions(merged);
  };

  // ── Simple publish confirm (uses already-saved quiz settings)
  window.openPublishDialog = function(postId, postTitle){
    Swal.fire({
      title: `Publish "${postTitle}"`,
      text: 'Use the quiz settings you already assigned and publish now?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: '<i class="fa-solid fa-paper-plane"></i> Publish',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#1a9e78'
    }).then(res => {
      if (!res.isConfirmed) return;
      submitPublish(postId);
    });
  };

  function submitPublish(postId){
    const fd = new FormData();
    fd.append('post_id', postId);

    Swal.fire({ title:'Publishing…', didOpen: () => Swal.showLoading(), allowOutsideClick:false });

    fetch(API_PUBLISH, { method:'POST', body: fd, credentials:'same-origin' })
      .then(r => r.json())
      .then(j => {
        if (!j || j.status !== 'success') throw new Error(j?.message || 'Publish failed');
        Swal.fire({ icon:'success', title:'Published!', timer:1600, showConfirmButton:false });
        window.refreshQuizPostPill(postId, { is_published:1, is_force_closed:0, results_released_at:null });
        if (typeof allPosts !== 'undefined'){
          const p = allPosts.find(x => x.id === postId);
          if (p){ p.is_published = 1; }
        }
        // Force immediate card action refresh (Publish -> Manage Quiz) without manual reload.
        if (typeof loadClassroom === 'function') {
          loadClassroom().then(() => {
            if (typeof renderStream === 'function') renderStream();
            if (typeof renderClasswork === 'function') renderClasswork();
          });
        }
      })
      .catch(err => {
        Swal.fire({ icon:'error', title:'Could not publish', text: err.message || String(err) });
      });
  }

  // ── Delegated click handlers for the post-card buttons
  document.addEventListener('click', function(e){
    const pubBtn = e.target.closest('.pc-publish-btn');
    if (pubBtn) {
      e.preventDefault();
      window.openPublishDialog(pubBtn.dataset.quizId, pubBtn.dataset.quizTitle || 'Quiz');
      return;
    }
    const mgBtn = e.target.closest('.pc-manage-quiz-btn');
    if (mgBtn) {
      e.preventDefault();
      const postId = mgBtn.closest('[data-post-id]')?.dataset.postId;
      if (postId && typeof openManageQuiz === 'function') openManageQuiz(postId);
    }
  });
})();
</script>

<script>
/* ══ LIVE QUIZ LOBBY (faculty) ══════════════════════════════════════
   Wire-up: any element with [data-quiz-lobby="<post_id>"] opens the drawer.
   e.g.  <button class="lq-trigger" data-quiz-lobby=""
                 data-quiz-title=">
           <i class="fa-solid fa-users"></i> Lobby
           <span class="lq-trigger-count" data-lq-count0</span>
         </button>
═════════════════════════════════════════════════════════════════════ */
(function () {
  const overlay = document.getElementById('lqOverlay');
  const drawer  = document.getElementById('lqDrawer');
  const els = {
    title:      document.getElementById('lqTitle'),
    sub:        document.getElementById('lqSub'),
    modePill:   document.getElementById('lqModePill'),
    status:     document.getElementById('lqStatus'),
    statusText: document.getElementById('lqStatusText'),
    enrolled:   document.getElementById('lqStatEnrolled'),
    progress:   document.getElementById('lqStatProgress'),
    submitted:  document.getElementById('lqStatSubmitted'),
    list:       document.getElementById('lqList'),
    startBtn:   document.getElementById('lqStartBtn'),
    startLabel: document.getElementById('lqStartLabel'),
    endBtn:     document.getElementById('lqEndBtn'),
    closeBtn:   document.getElementById('lqClose'),
  };

  let currentPostId = null;
  let currentTitle  = '';
  let pollTimer     = null;

  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
  }

  function initials(f, l) {
    return ((f || '?').charAt(0) + (l || '').charAt(0)).toUpperCase();
  }

  function setMode(mode) {
    els.modePill.textContent = mode === 'live' ? 'Live' : 'Due Date';
    els.modePill.className   = 'lq-mode-pill ' + (mode === 'live' ? 'lq-mode-live' : 'lq-mode-due');
  }

  function setStatus(state, text) {
    els.status.className     = 'lq-status is-' + state;
    els.statusText.textContent = text;
  }

function renderList(rows) {
    if (!rows || rows.length === 0) {
        els.list.innerHTML =
            '<div class="lq-empty"><i class="fa-regular fa-clock"></i>' +
            '<h4>No students enrolled yet</h4>' +
            '<p>Once students apply for this quiz, they\'ll appear here.</p></div>';
        return;
    }
    els.list.innerHTML = rows.map(function (r) {
        const name = ((r.first_name || '') + ' ' + (r.last_name || '')).trim() || 'Student';
        const meta = r.student_number ? ('ID: ' + r.student_number) : '—';
        const scoreHtml = (r.score !== null && r.score !== undefined)
            ? '<span class="lq-row-tag is-submitted" style="margin-left:.3rem;">' + parseFloat(r.score).toFixed(1) + ' pts</span>'
            : '';
        let tag = '<span class="lq-row-tag">Waiting</span>';
        if (r.attempt_status === 'in_progress')    tag = '<span class="lq-row-tag is-progress">In Progress</span>';
        else if (r.attempt_status === 'submitted') tag = '<span class="lq-row-tag is-submitted">Submitted</span>' + scoreHtml;

        const canRemove = r.attempt_status !== 'in_progress';
        const removeBtn = canRemove
            ? '<button class="lq-remove-btn" type="button" title="Remove student" ' +
              'onclick="removeLobbyStudent(\'' + escapeHtml(currentPostId) + '\',\'' + escapeHtml(r.student_id) + '\',this)" ' +
              'style="margin-left:auto;background:none;border:1.5px solid #f5c2c7;color:#d93025;border-radius:8px;padding:.25rem .55rem;font-size:.72rem;font-weight:700;cursor:pointer;">' +
              '<i class="fa-solid fa-xmark"></i></button>'
            : '';

        return '<div class="lq-row">' +
            '<div class="lq-av">' + escapeHtml(initials(r.first_name, r.last_name)) + '</div>' +
            '<div class="lq-row-info">' +
                '<div class="lq-row-name">' + escapeHtml(name) + '</div>' +
                '<div class="lq-row-meta">' + escapeHtml(meta) + '</div>' +
            '</div>' +
            tag + removeBtn +
        '</div>';
    }).join('');
}

async function removeLobbyStudent(postId, studentId, btn) {
    const ok = await Swal.fire({
        icon: 'warning', title: 'Remove this student?',
        text: 'They will be unenrolled and must re-enroll if the quiz is still open.',
        showCancelButton: true, confirmButtonText: 'Yes, remove',
        confirmButtonColor: '#d93025'
    });
    if (!ok.isConfirmed) return;
    btn.disabled = true;
    try {
        const fd = new FormData();
        fd.append('post_id', postId);
        fd.append('student_id', studentId);
        const res = await fetch('API/facultyUI/classroom/quiz/remove_enrollment.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        const j = await res.json();
        if (!j || j.success === false) throw new Error(j.message || 'Failed');
        Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false });
        refresh();
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Could not remove', text: String(e.message || e) });
        btn.disabled = false;
    }
}

  async function refresh() {
    if (!currentPostId) return;
    try {
      const fd = new FormData();
      fd.append('post_id', currentPostId);
      const res = await fetch('API/facultyUI/classroom/quiz/get_enrollments.php', {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const j = await res.json();
      if (!j || j.success === false) throw new Error((j && j.message) || 'Failed');

      const data = j.data || j;
      const quiz = data.quiz || {};
      const rows = data.enrollments || [];
      const mode = quiz.quiz_mode || 'due_date';

      setMode(mode);
      els.enrolled.textContent  = (data.count != null ? data.count : rows.length);
      els.progress.textContent  = rows.filter(r => r.attempt_status === 'in_progress').length;
      els.submitted.textContent = rows.filter(r => r.attempt_status === 'submitted').length;

      const ended   = !!quiz.live_ended_at || parseInt(quiz.is_force_closed, 10) === 1;
      const started = !!quiz.live_started_at;

      if (ended) {
        setStatus('ended', mode === 'live' ? 'Live quiz ended' : 'Quiz closed');
        els.startBtn.disabled = true;
        els.endBtn.disabled   = true;
      } else if (mode === 'live') {
        if (started) {
          setStatus('live', 'Live · students are taking the quiz');
          els.startLabel.textContent = 'Live in progress';
          els.startBtn.disabled = true;
          els.endBtn.disabled   = false;
        } else {
          setStatus('waiting', 'Waiting for you to start (' + rows.length + ' enrolled)');
          els.startLabel.textContent = 'Start Live Quiz';
          els.startBtn.disabled = rows.length === 0;
          els.endBtn.disabled   = false;
        }
      } else {
        const due = quiz.due_date ? new Date(quiz.due_date).toLocaleString() : null;
        setStatus('waiting', due ? ('Due-date mode · closes ' + due) : 'Due-date mode');
        els.startLabel.textContent = 'Start (N/A)';
        els.startBtn.disabled = true;
        els.endBtn.disabled   = false;
      }

      els.sub.textContent = currentTitle + ' · ' + rows.length + ' enrolled';
      renderList(rows);

      // Update any badge counters on the page for this post
      document.querySelectorAll('[data-lq-count="' + currentPostId + '"]').forEach(function (n) {
        n.textContent = rows.length;
      });
    } catch (e) {
      setStatus('ended', 'Could not load lobby');
    }
  }

  function open(postId, title) {
    currentPostId = postId;
    currentTitle  = title || 'Quiz';
    els.title.textContent = currentTitle;
    els.sub.textContent   = 'Loading…';
    els.list.innerHTML    = '';
    overlay.classList.add('show');
    drawer.classList.remove('closing');
    drawer.classList.add('open');
    drawer.setAttribute('aria-hidden', 'false');
    refresh();
    pollTimer = setInterval(refresh, 4000);
  }

  function close() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    drawer.classList.remove('open');
    drawer.classList.add('closing');
    setTimeout(function () {
      drawer.classList.remove('closing');
      overlay.classList.remove('show');
      drawer.setAttribute('aria-hidden', 'true');
      currentPostId = null;
    }, 220);
  }

  // Open from any [data-quiz-lobby] trigger on the page
  document.addEventListener('click', function (ev) {
    const trig = ev.target.closest('[data-quiz-lobby]');
    if (trig) {
      ev.preventDefault();
      open(
        trig.getAttribute('data-quiz-lobby'),
        trig.getAttribute('data-quiz-title') || 'Quiz'
      );
    }
  });

  overlay.addEventListener('click', close);
  els.closeBtn.addEventListener('click', close);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && drawer.classList.contains('open')) close();
  });

  // ── Start Live Quiz ────────────────────────────────────────────
  els.startBtn.addEventListener('click', async function () {
    if (!currentPostId || els.startBtn.disabled) return;
    const ok = await Swal.fire({
      icon: 'question',
      title: 'Start the live quiz?',
      text: 'All enrolled students will be allowed to begin immediately.',
      showCancelButton: true,
      confirmButtonText: 'Yes, start now',
      confirmButtonColor: '#1a9e78'
    });
    if (!ok.isConfirmed) return;
    els.startBtn.disabled = true;
    try {
      const fd = new FormData();
      fd.append('post_id', currentPostId);
      const res = await fetch('API/facultyUI/classroom/quiz/start_live_quiz.php', {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const j = await res.json();
      if (!j || j.success === false) throw new Error((j && j.message) || 'Failed');
      Swal.fire({ icon: 'success', title: 'Live quiz started', timer: 1400, showConfirmButton: false });
      refresh();
    } catch (e) {
      Swal.fire({ icon: 'error', title: 'Could not start', text: String(e.message || e) });
      els.startBtn.disabled = false;
    }
  });

  // ── End Quiz (override) ────────────────────────────────────────
  els.endBtn.addEventListener('click', async function () {
    if (!currentPostId || els.endBtn.disabled) return;
    const ok = await Swal.fire({
      icon: 'warning',
      title: 'End this quiz now?',
      text: 'In-progress attempts will be auto-submitted.',
      showCancelButton: true,
      confirmButtonText: 'End quiz',
      confirmButtonColor: '#d93025'
    });
    if (!ok.isConfirmed) return;
    els.endBtn.disabled = true;
    try {
      const fd = new FormData();
      fd.append('post_id', currentPostId);
      const res = await fetch('API/facultyUI/classroom/quiz/end_quiz.php', {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const j = await res.json();
      if (!j || j.success === false) throw new Error((j && j.message) || 'Failed');
      Swal.fire({ icon: 'success', title: 'Quiz ended', timer: 1400, showConfirmButton: false });
      refresh();
    } catch (e) {
      Swal.fire({ icon: 'error', title: 'Could not end quiz', text: String(e.message || e) });
      els.endBtn.disabled = false;
    }
  });

  // Pre-load enrolled badge counts for any visible triggers
  function preloadCounts() {
    const ids = Array.from(new Set(
      Array.from(document.querySelectorAll('[data-lq-count]'))
        .map(n => n.getAttribute('data-lq-count'))
        .filter(Boolean)
    ));
    ids.forEach(async function (pid) {
      try {
        const fd = new FormData();
        fd.append('post_id', pid);
        const res = await fetch('API/facultyUI/classroom/quiz/get_enrollments.php', {
          method: 'POST', body: fd, credentials: 'same-origin'
        });
        const j = await res.json();
        const data = (j && (j.data || j)) || {};
        const c = data.count != null ? data.count : (data.enrollments ? data.enrollments.length : 0);
        document.querySelectorAll('[data-lq-count="' + pid + '"]').forEach(function (n) {
          n.textContent = c;
        });
      } catch (_) { /* ignore */ }
    });
  }
    if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', preloadCounts);
  } else {
    preloadCounts();
  }
  // Auto-refresh all Lobby badge counts every 5s, even when the drawer is closed.
  setInterval(preloadCounts, 5000);

  // Expose for inline callers
  window.openQuizLobby = open;
})();

</script>
<script>
  



/* ══════════════════════════════════════════════════════
   STEPS 12-14 — Manage Quiz: Settings, Submissions, Analytics
   ══════════════════════════════════════════════════════ */

/* Bridge: STEP 11 calls window.openManageQuizModal — resolve it here */
window.openManageQuizModal = function(postId) {
  if (typeof openManageQuiz === 'function') {
    openManageQuiz(postId);
  } else {
    console.error('openManageQuiz() not found');
  }
};

/* Shared HTML-escape helper used in template strings below */
function escHTML(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

async function loadQuizSettings() {
  const postId = mqCurrentPostId;
  const post   = mqCurrentPostData;
  if (!postId || !post) return;

  const loading = document.getElementById('mqSettingsLoading');
  const content = document.getElementById('mqSettingsContent');
  if (loading) loading.style.display = 'flex';
  if (content) content.style.display = 'none';

  // Fetch live state from get_enrollments to get quiz row + enrolled count
  try {
    const fd = new FormData();
    fd.append('post_id', postId);
    const res  = await fetch('API/facultyUI/classroom/quiz/get_enrollments.php', { method:'POST', body:fd, credentials:'same-origin' });
    const json = await res.json();

    const quiz        = (json.data || {}).quiz        || {};
    const enrollments = (json.data || {}).enrollments || [];
    const count       = enrollments.length;

    const isPublished    = parseInt(post.is_published || 0, 10) === 1;
    const isForceClosed  = parseInt(quiz.is_force_closed || 0, 10) === 1;
    const isStarted      = !!quiz.live_started_at;
    const isEnded        = !!quiz.live_ended_at || isForceClosed;
    const isReleased     = !!quiz.results_released_at;

    const qCount  = (post.questions || []).length;
    const totalPts = (post.questions || []).reduce((s,q)=>s+(parseFloat(q.points)||0),0);

    const waitingCount     = enrollments.filter(e => !e.attempt_status).length;
    const inProgressCount  = enrollments.filter(e => e.attempt_status === 'in_progress').length;
    const submittedCount   = enrollments.filter(e => e.attempt_status === 'submitted').length;

    // Build start/end/release buttons
    let controlsHtml = '';
    if (isPublished && !isEnded) {
      if (!isStarted) {
        controlsHtml += `
          <button class="mq-btn mq-btn-primary mq-ctrl-btn" type="button" onclick="quizAction('start')" ${count===0?'disabled title="No enrolled students yet"':''}>
            <i class="fa-solid fa-play"></i> Start Quiz for Enrolled Students
          </button>`;
      } else {
        controlsHtml += `
          <div class="mq-ctrl-started-info">
            <i class="fa-solid fa-circle-dot" style="color:#1a9e78;"></i>
            Quiz is <strong>live</strong> — started at ${quiz.live_started_at ? new Date(quiz.live_started_at.replace(' ','T')).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}) : '—'}
          </div>`;
      }
      controlsHtml += `
        <button class="mq-btn mq-btn-danger mq-ctrl-btn" type="button" onclick="quizAction('end')">
          <i class="fa-solid fa-stop"></i> End Quiz Now
        </button>`;
    }
    if (!isReleased && submittedCount > 0) {
      controlsHtml += `
        <button class="mq-btn mq-btn-primary mq-ctrl-btn" type="button" onclick="quizAction('release_results')">
          <i class="fa-solid fa-flag-checkered"></i> Release Results to Students
        </button>`;
    }
    if (isReleased) {
      controlsHtml += `<div class="mq-ctrl-started-info"><i class="fa-solid fa-check-circle" style="color:#1a9e78;"></i> Results already released.</div>`;
    }
    if (!controlsHtml) {
      controlsHtml = '<p style="color:#999;font-size:.85rem;margin:0;">No controls available.</p>';
    }

    content.innerHTML = `
      <div class="mq-settings-grid">
        <div class="mq-settings-section">
          <div class="mq-settings-label">Quiz Info</div>
          <div class="mq-settings-row"><span>Questions</span><strong>${qCount}</strong></div>
          <div class="mq-settings-row"><span>Total Points</span><strong>${totalPts}</strong></div>
          <div class="mq-settings-row"><span>Mode</span><strong>${(quiz.quiz_mode||'live').charAt(0).toUpperCase()+(quiz.quiz_mode||'live').slice(1)}</strong></div>
          ${quiz.time_limit_seconds ? `<div class="mq-settings-row"><span>Time Limit</span><strong>${Math.floor(quiz.time_limit_seconds/60)} min</strong></div>` : ''}
          ${quiz.due_date ? `<div class="mq-settings-row"><span>Due Date</span><strong>${new Date(quiz.due_date.replace(' ','T')).toLocaleDateString()}</strong></div>` : ''}
          ${quiz.max_attempts ? `<div class="mq-settings-row"><span>Max Attempts</span><strong>${quiz.max_attempts}</strong></div>` : ''}
        </div>
        <div class="mq-settings-section">
          <div class="mq-settings-label">Enrolled Students</div>
          <div class="mq-settings-row"><span>Total Enrolled</span><strong>${count}</strong></div>
          <div class="mq-settings-row"><span>Waiting</span><strong style="color:#888;">${waitingCount}</strong></div>
          <div class="mq-settings-row"><span>In Progress</span><strong style="color:#f59e0b;">${inProgressCount}</strong></div>
          <div class="mq-settings-row"><span>Submitted</span><strong style="color:#1a9e78;">${submittedCount}</strong></div>
        </div>
        <div class="mq-settings-section mq-settings-section--full">
          <div class="mq-settings-label">Controls</div>
          <div class="mq-ctrl-row">${controlsHtml}</div>
        </div>
      </div>`;

    if (loading) loading.style.display = 'none';
    if (content) content.style.display = 'block';

  } catch (e) {
    if (loading) loading.innerHTML = '<span style="color:#d93025;font-size:.85rem;">Failed to load settings. <button onclick="loadQuizSettings()" style="background:none;border:none;color:#1a9e78;cursor:pointer;font-weight:700;">Retry</button></span>';
    console.error('loadQuizSettings', e);
  }
}

async function quizAction(action) {
  const postId = mqCurrentPostId;
  if (!postId) return;

  const labels = {
    start:           { title: 'Start the quiz?',          text: 'Students who joined the lobby will start together.', confirmText: 'Yes, Start', color: '#1a9e78' },
    end:             { title: 'End the quiz now?',         text: 'Students currently in progress will be cut off.', confirmText: 'Yes, End',   color: '#d93025' },
    release_results: { title: 'Release results?',         text: 'All enrolled students will see their scores.',    confirmText: 'Release',    color: '#1a9e78' },
    force_close:     { title: 'Force close this quiz?',   text: 'No more submissions will be accepted.',          confirmText: 'Force Close',color: '#d93025' },
  };
  const lbl = labels[action] || { title: 'Confirm', text: '', confirmText: 'OK', color: '#1a9e78' };

  const ok = await Swal.fire({
    icon: 'question', title: lbl.title, text: lbl.text,
    showCancelButton: true, confirmButtonText: lbl.confirmText, confirmButtonColor: lbl.color,
  });
  if (!ok.isConfirmed) return;

  try {
    const fd = new FormData();
    fd.append('post_id', postId);
    fd.append('action', action);
    const res  = await fetch('API/facultyUI/classroom/quiz/start_live_quiz.php', { method:'POST', body:fd, credentials:'same-origin' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Action failed');

    Swal.fire({ icon:'success', title: json.message, timer:1800, showConfirmButton:false });

    // Refresh settings pane + update post cache
    loadQuizSettings();
    if (typeof allPosts !== 'undefined') {
      const p = allPosts.find(x => x.id === postId);
      if (p) {
        if (action === 'start')           { p.live_started_at = new Date().toISOString(); p.is_force_closed = 0; }
        if (action === 'end')             { p.live_ended_at   = new Date().toISOString(); p.is_force_closed = 1; }
        if (action === 'release_results') { p.results_released_at = new Date().toISOString(); }
        if (action === 'force_close')     { p.is_force_closed = 1; }
        setMqStatusPill(p);
      }
    }
  } catch (e) {
    Swal.fire({ icon:'error', title:'Failed', text: String(e.message || e) });
  }
}

/* ══ STEP 13: Submissions (enrolled students + scores) ══ */

let mqAllRows = [];
async function loadSubmissions(forceRefresh) {
  const postId = mqCurrentPostId
    || document.getElementById('mqModal')?.dataset?.postId
    || null;
  if (!postId) return;

  const loading = document.getElementById('mqSubLoading');
  const wrap    = document.getElementById('mqSubTableWrap');
  const empty   = document.getElementById('mqSubEmpty');
  const badge   = document.getElementById('mqSubBadge');
  const tbody   = document.getElementById('mqSubTbody');

  if (loading) loading.style.display = 'flex';
  if (wrap)    wrap.style.display    = 'none';
  if (empty)   empty.style.display   = 'none';

  try {
    const fd = new FormData();
    fd.append('post_id', postId);
    const res  = await fetch('API/facultyUI/classroom/quiz/get_enrollments.php', { method:'POST', body:fd, credentials:'same-origin' });
    const json = await res.json();
    const rows = (json.data || {}).enrollments || [];

    mqAllRows = rows;
    if (badge) badge.textContent = rows.length;

    if (loading) loading.style.display = 'none';

    if (rows.length === 0) {
      if (empty) empty.style.display = 'flex';
      return;
    }

    renderSubmissionsTable(rows);
    if (wrap) wrap.style.display = 'block';

  } catch (e) {
    if (loading) loading.innerHTML = '<span style="color:#d93025;font-size:.85rem;">Failed. <button onclick="loadSubmissions(true)" style="background:none;border:none;color:#1a9e78;cursor:pointer;font-weight:700;">Retry</button></span>';
    console.error('loadSubmissions', e);
  }
}

function renderSubmissionsTable(rows) {
  const tbody = document.getElementById('mqSubTbody');
  if (!tbody) return;

  tbody.innerHTML = rows.map(function(r) {
    const name    = ((r.first_name||'') + ' ' + (r.last_name||'')).trim() || 'Student';
    const meta    = r.student_number ? r.student_number : '—';
    const initStr = ((r.first_name||'').charAt(0) + (r.last_name||'').charAt(0)).toUpperCase() || '?';

    let statusHtml = '<span class="mq-chip mq-chip-wait">Enrolled</span>';
    if (r.attempt_status === 'in_progress') statusHtml = '<span class="mq-chip mq-chip-prog">In Progress</span>';
    if (r.attempt_status === 'submitted')   statusHtml = '<span class="mq-chip mq-chip-done">Submitted</span>';

    const scoreHtml  = (r.score !== null && r.score !== undefined) ? parseFloat(r.score).toFixed(1) + ' pts' : '—';
    const enrolledAt = r.enrolled_at ? new Date(r.enrolled_at.replace(' ','T')).toLocaleString([],{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}) : '—';

    const canRemove   = r.attempt_status !== 'in_progress';
    const viewDisabled = (r.score === null || r.score === undefined) ? 'disabled' : '';
    const removeBtn = canRemove
      ? `<button class="mq-row-btn mq-row-btn-remove" type="button" title="Remove student"
           onclick="mqRemoveStudent('${escHTML(r.student_id)}','${escHTML(name)}')">
           <i class="fa-solid fa-user-minus"></i>
         </button>`
      : `<button class="mq-row-btn" type="button" disabled title="Cannot remove while in progress"><i class="fa-solid fa-user-minus"></i></button>`;

    const viewBtn = `<button class="mq-row-btn mq-row-btn-view" type="button" title="View submission" ${viewDisabled}
        onclick="openSubmissionDetail('${escHTML(r.student_id)}')">
        <i class="fa-solid fa-eye"></i>
      </button>`;

    return `<tr data-student-id="${escHTML(r.student_id)}" data-name="${escHTML(name.toLowerCase())} ${escHTML(meta.toLowerCase())}">
      <td>
        <div style="display:flex;align-items:center;gap:.55rem;">
          <div class="mq-av">${escHTML(initStr)}</div>
          <div>
            <div style="font-weight:600;font-size:.85rem;">${escHTML(name)}</div>
            <div style="font-size:.73rem;color:#888;">${escHTML(meta)}</div>
          </div>
        </div>
      </td>
      <td>${statusHtml}</td>
      <td style="text-align:center;">1</td>
      <td style="font-weight:600;color:${r.score!==null?'#1a9e78':'#bbb'};">${escHTML(scoreHtml)}</td>
      <td style="font-size:.78rem;color:#888;">${escHTML(enrolledAt)}</td>
      <td style="white-space:nowrap;">${viewBtn} ${removeBtn}</td>
    </tr>`;
  }).join('');
}

function filterSubmissions() {
  const q = (document.getElementById('mqSubSearch')?.value || '').toLowerCase().trim();
  document.querySelectorAll('#mqSubTbody tr').forEach(function(tr) {
    const hay = tr.dataset.name || '';
    tr.style.display = hay.includes(q) ? '' : 'none';
  });
}

function toggleLiveMonitor() {
  const on = document.getElementById('mqLiveToggle')?.checked;
  const ll = document.getElementById('mqLiveLabel');
  if (on) {
    if (ll) ll.classList.add('active');
    if (mqLivePollTimer) clearInterval(mqLivePollTimer);
    mqLivePollTimer = setInterval(function() { loadSubmissions(true); }, 4000);
  } else {
    if (ll) ll.classList.remove('active');
    if (mqLivePollTimer) { clearInterval(mqLivePollTimer); mqLivePollTimer = null; }
  }
}

async function mqRemoveStudent(studentId, name) {
  const postId = mqCurrentPostId;
  if (!postId) return;

  const ok = await Swal.fire({
    icon:'warning', title:'Remove student?',
    html:`<span style="font-size:.88rem;">Remove <strong>${escHTML(name)}</strong> from this quiz?<br>They will need to re-enroll if the quiz is still open.</span>`,
    showCancelButton:true, confirmButtonText:'Yes, Remove', confirmButtonColor:'#d93025',
  });
  if (!ok.isConfirmed) return;

  try {
    const fd = new FormData();
    fd.append('post_id',    postId);
    fd.append('student_id', studentId);
    const res  = await fetch('API/facultyUI/classroom/quiz/remove_enrollment.php', { method:'POST', body:fd, credentials:'same-origin' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Remove failed');
    Swal.fire({ icon:'success', title:'Removed', timer:1200, showConfirmButton:false });
    loadSubmissions(true);
  } catch (e) {
    Swal.fire({ icon:'error', title:'Could not remove', text: String(e.message || e) });
  }
}

async function openSubmissionDetail(studentId) {
  const postId = mqCurrentPostId;
  if (!postId || !studentId) return;

  const row = mqAllRows.find(r => String(r.student_id) === String(studentId));
  if (!row) return;

  const name = ((row.first_name||'') + ' ' + (row.last_name||'')).trim() || 'Student';

  const detailOverlay = document.getElementById('mqDetailOverlay');
  const detailModal   = document.getElementById('mqDetailModal');
  const detailTitle   = document.getElementById('mqDetailTitle');
  const detailSub     = document.getElementById('mqDetailSub');
  const detailLoading = document.getElementById('mqDetailLoading');
  const detailContent = document.getElementById('mqDetailContent');

  if (!detailModal) return;

  detailModal.dataset.studentId = studentId;
  detailModal.dataset.postId    = postId;
  if (detailTitle)   detailTitle.textContent = name;
  if (detailSub)     detailSub.textContent   = row.student_number || '—';
  if (detailLoading) detailLoading.style.display = 'flex';
  if (detailContent) detailContent.style.display = 'none';
  if (detailOverlay) detailOverlay.classList.add('show');
  detailModal.classList.add('show');
  document.body.style.overflow = 'hidden';

  // Build a score summary from what we already have
  const scoreHtml = (row.score !== null && row.score !== undefined)
    ? `<div class="mq-detail-score"><span>Score</span><strong>${parseFloat(row.score).toFixed(1)} pts</strong></div>`
    : '<p style="color:#999;font-size:.85rem;">No score recorded yet.</p>';

  const statusHtml = {
    'in_progress': '<span class="mq-chip mq-chip-prog">In Progress</span>',
    'submitted':   '<span class="mq-chip mq-chip-done">Submitted</span>',
  }[row.attempt_status] || '<span class="mq-chip mq-chip-wait">Enrolled — Not yet started</span>';

  if (detailLoading) detailLoading.style.display = 'none';
  if (detailContent) {
    detailContent.innerHTML = `
      <div style="padding:.5rem 0 1rem;">
        <div style="margin-bottom:.75rem;">${statusHtml}</div>
        ${scoreHtml}
        <div class="mq-detail-row"><span>Enrolled at</span><strong>${row.enrolled_at ? new Date(row.enrolled_at.replace(' ','T')).toLocaleString() : '—'}</strong></div>
        <div class="mq-detail-row"><span>Student #</span><strong>${escHTML(row.student_number||'—')}</strong></div>
      </div>`;
    detailContent.style.display = 'block';
  }
}

function closeSubmissionDetail() {
  const detailOverlay = document.getElementById('mqDetailOverlay');
  const detailModal   = document.getElementById('mqDetailModal');
  if (detailOverlay) detailOverlay.classList.remove('show');
  if (detailModal)   detailModal.classList.remove('show');
  document.body.style.overflow = 'hidden'; // keep main modal scroll locked
}

function saveSubmissionOverrides() {
  Swal.fire({ icon:'info', title:'No overrides to save.', timer:1200, showConfirmButton:false });
}

/* ══ STEP 14: Analytics ══ */

async function loadQuizAnalytics() {
  const postId = mqCurrentPostId;
  if (!postId) return;

  const loading = document.getElementById('mqAnLoading');
  const content = document.getElementById('mqAnContent');
  if (loading) loading.style.display = 'flex';
  if (content) content.style.display = 'none';

  try {
    const fd = new FormData();
    fd.append('post_id', postId);
    const res  = await fetch('API/facultyUI/classroom/quiz/get_enrollments.php', { method:'POST', body:fd, credentials:'same-origin' });
    const json = await res.json();
    const enrollments = (json.data || {}).enrollments || [];
    const quiz        = (json.data || {}).quiz || {};

    const total       = enrollments.length;
    const submitted   = enrollments.filter(e => e.attempt_status === 'submitted').length;
    const inProg      = enrollments.filter(e => e.attempt_status === 'in_progress').length;
    const waiting     = total - submitted - inProg;
    const scores      = enrollments.map(e => e.score).filter(s => s !== null && s !== undefined);
    const avg         = scores.length ? (scores.reduce((a,b)=>a+b,0)/scores.length).toFixed(1) : '—';
    const highest     = scores.length ? Math.max(...scores).toFixed(1) : '—';
    const lowest      = scores.length ? Math.min(...scores).toFixed(1) : '—';

    const pct = total ? Math.round((submitted/total)*100) : 0;

    content.innerHTML = `
      <div class="mq-analytics-grid">
        <div class="mq-an-card"><div class="mq-an-val">${total}</div><div class="mq-an-lbl">Enrolled</div></div>
        <div class="mq-an-card"><div class="mq-an-val" style="color:#1a9e78;">${submitted}</div><div class="mq-an-lbl">Submitted</div></div>
        <div class="mq-an-card"><div class="mq-an-val" style="color:#f59e0b;">${inProg}</div><div class="mq-an-lbl">In Progress</div></div>
        <div class="mq-an-card"><div class="mq-an-val" style="color:#888;">${waiting}</div><div class="mq-an-lbl">Waiting</div></div>
        <div class="mq-an-card"><div class="mq-an-val">${avg}</div><div class="mq-an-lbl">Avg Score</div></div>
        <div class="mq-an-card"><div class="mq-an-val" style="color:#1a9e78;">${highest}</div><div class="mq-an-lbl">Highest</div></div>
        <div class="mq-an-card"><div class="mq-an-val" style="color:#d93025;">${lowest}</div><div class="mq-an-lbl">Lowest</div></div>
        <div class="mq-an-card">
          <div class="mq-an-val">${pct}%</div>
          <div class="mq-an-lbl">Completion</div>
          <div style="margin-top:.4rem;background:#f0f0f0;border-radius:6px;height:6px;overflow:hidden;">
            <div style="width:${pct}%;height:100%;background:#1a9e78;border-radius:6px;transition:width .4s;"></div>
          </div>
        </div>
      </div>`;

    if (loading) loading.style.display = 'none';
    if (content) content.style.display = 'block';

  } catch (e) {
    if (loading) loading.innerHTML = '<span style="color:#d93025;font-size:.85rem;">Failed to load analytics.</span>';
    console.error('loadQuizAnalytics', e);
  }
}

/* ── Helper: HTML-escape for use inside JS strings ── */
function escHTML(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ─────────────────────────────────────────────────────── */
/* END OF STEPS 12-14. Paste everything above this line.  */

</script>


<!-- ══ LIVE QUIZ LOBBY DRAWER ══ -->
<div class="lq-overlay" id="lqOverlay"></div>
<aside class="lq-drawer" id="lqDrawer" aria-hidden="true">
  <div class="lq-head">
    <div class="lq-head-icon"><i class="fa-solid fa-tower-broadcast"></i></div>
    <div class="lq-head-info">
      <div class="lq-title">
        <span id="lqTitle">Quiz Lobby</span>
        <span class="lq-mode-pill" id="lqModePill"></span>
      </div>
      <div class="lq-sub" id="lqSub">Loading…</div>
    </div>
    <button class="lq-close" id="lqClose" type="button" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
  </div>

  <div class="lq-status" id="lqStatus">
    <span class="lq-dot"></span><span id="lqStatusText">Loading…</span>
  </div>

  <div class="lq-stats">
    <div class="lq-stat">
      <div class="lq-stat-label">Enrolled</div>
      <div class="lq-stat-val" id="lqStatEnrolled">0</div>
    </div>
    <div class="lq-stat">
      <div class="lq-stat-label">In progress</div>
      <div class="lq-stat-val" id="lqStatProgress">0</div>
    </div>
    <div class="lq-stat">
      <div class="lq-stat-label">Submitted</div>
      <div class="lq-stat-val" id="lqStatSubmitted">0</div>
    </div>
  </div>

  <div class="lq-list" id="lqList"></div>

  <div class="lq-foot">
    <button class="lq-btn lq-btn-start" id="lqStartBtn" type="button">
      <i class="fa-solid fa-play"></i><span id="lqStartLabel">Start Live Quiz</span>
    </button>
    <button class="lq-btn lq-btn-end" id="lqEndBtn" type="button">
      <i class="fa-solid fa-stop"></i> End Quiz
    </button>
  </div>
</aside>

</body>
</html>

