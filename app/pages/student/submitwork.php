<?php
/**
 * submitwork.php — Tere LEARN | Student Activity / Assignment Submission
 * Layout: Google Classroom-style (details left, Your Work panel right)
 * ?post_id=UUID&class_id=UUID
 */
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php'); exit;
}
$post_id  = trim($_GET['post_id']  ?? '');
$class_id = trim($_GET['class_id'] ?? '');
if (!$post_id) { header('Location: ' . TERELEARN_BASE_URL . 'student.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Post Details - Tere LEARN</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
:root{
  --primary:#1a9e78;--primary-dark:#0d7a5e;--primary-light:#e6f7f2;
  --primary-mid:rgba(26,158,120,.15);
  --accent:#1f73db;--accent-light:#e8f0fe;
  --danger:#d93025;--warning:#f57c00;
  --border:#e8eaed;--text:#1c2027;--text-muted:#5f6368;
  --bg:#f4f6f9;--surface:#fff;--nav-h:60px;
  --radius:14px;--radius-sm:8px;
  --shadow:0 1px 4px rgba(0,0,0,.08);--shadow-md:0 4px 20px rgba(0,0,0,.10);
  --trans:.2s cubic-bezier(.4,0,.2,1);
}
body.dark{
  --primary:#2ecc9a;--primary-dark:#1a9e78;--primary-light:rgba(46,204,154,.12);
  --primary-mid:rgba(46,204,154,.10);--accent:#4d90e2;--accent-light:rgba(77,144,226,.14);
  --border:#2e3849;--text:#e4ecf7;--text-muted:#8a9ab5;--bg:#0f1724;--surface:#182030;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}
button,input,textarea{font-family:inherit;}
.swal2-container{z-index:99999!important;}

/* ── TOP NAV ── */
.sw-nav{
  position:fixed;inset:0 0 auto 0;height:var(--nav-h);
  background:var(--surface);border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:.6rem;padding:0 1.25rem;
  z-index:200;box-shadow:var(--shadow);
}
.sw-nav-back{width:36px;height:36px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:1rem;display:flex;align-items:center;justify-content:center;border-radius:10px;transition:all var(--trans);text-decoration:none;flex-shrink:0;}
.sw-nav-back:hover{background:var(--border);color:var(--text);}
.sw-nav-brand{display:flex;align-items:center;gap:.45rem;font-size:.88rem;font-weight:700;color:var(--text);text-decoration:none;flex-shrink:0;}
.sw-nav-logo{width:28px;height:28px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:7px;color:#fff;font-size:.7rem;display:flex;align-items:center;justify-content:center;}
.sw-nav-crumb{font-size:.82rem;color:var(--text-muted);font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px;}
.sw-nav-crumb b{color:var(--text);}
.sw-nav-right{margin-left:auto;display:flex;align-items:center;gap:.35rem;}
.sw-icon-btn{width:34px;height:34px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:.95rem;display:flex;align-items:center;justify-content:center;border-radius:9px;transition:all var(--trans);}
.sw-icon-btn:hover{background:var(--border);color:var(--text);}
.sw-nav-progress{position:absolute;left:0;right:0;bottom:-1px;height:3px;background:transparent;overflow:hidden;pointer-events:none;}
.sw-nav-progress span{display:block;width:0;height:100%;background:linear-gradient(90deg,var(--primary),var(--accent));box-shadow:0 0 16px rgba(26,158,120,.45);opacity:0;transition:width .18s ease,opacity .18s ease;}
.sw-nav-progress.active span{opacity:1;}

/* ── PAGE GRID ── */
.sw-page{padding-top:calc(var(--nav-h) + 2rem);padding-bottom:3rem;max-width:1020px;margin:0 auto;padding-left:1.25rem;padding-right:1.25rem;display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;}

/* ── LEFT COLUMN ── */
.sw-post-header{display:flex;align-items:flex-start;gap:1rem;padding:1.5rem 0 1.25rem;border-bottom:1px solid var(--border);}
.sw-post-type-ico{width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.1rem;}
.sw-post-header-info{flex:1;min-width:0;display:grid;grid-template-columns:minmax(0,1fr) auto;column-gap:1rem;align-items:start;}
.sw-post-title,.sw-post-byline{grid-column:1;}
.sw-post-title{font-size:1.45rem;font-weight:800;line-height:1.25;margin-bottom:.4rem;}
.sw-post-byline{font-size:.82rem;color:var(--text-muted);margin-bottom:.5rem;}
.sw-post-pts-row{grid-column:2;grid-row:1 / span 2;display:flex;align-items:flex-end;justify-content:flex-start;flex-direction:column;flex-wrap:wrap;gap:.5rem;font-size:.84rem;font-weight:600;color:var(--text-muted);}
.sw-pts-badge{display:inline-flex;align-items:center;gap:.35rem;font-size:.82rem;font-weight:700;color:var(--text);}
.sw-due-badge{margin-left:auto;align-self:flex-start;display:inline-flex;align-items:center;gap:.55rem;padding:.55rem .8rem;border-radius:12px;border:1px solid rgba(121,85,72,.22);background:#fff8f3;color:#654337;font-size:.86rem;font-weight:900;white-space:nowrap;box-shadow:0 6px 18px rgba(68,45,36,.07);}
.sw-due-label{font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:#9b7b72;}
.sw-due-badge.overdue{background:#fdecea;border-color:#f5c2c7;color:var(--danger);}
.sw-due-badge.overdue .sw-due-label{color:var(--danger);font-size:.92rem;letter-spacing:.1em;}

.sw-post-description{padding:1.25rem 0;border-bottom:1px solid var(--border);font-size:.92rem;line-height:1.75;color:var(--text);white-space:pre-wrap;}
.sw-post-description:empty{display:none;}

.sw-fac-attachments{padding:1rem 0;border-bottom:1px solid var(--border);}
.sw-fac-attach-item{width:100%;display:flex;align-items:center;gap:.75rem;padding:.6rem .75rem;border-radius:10px;border:1px solid var(--border);background:var(--surface);margin-bottom:.45rem;text-decoration:none;color:var(--text);transition:all var(--trans);font-family:inherit;text-align:left;cursor:pointer;}
.sw-fac-attach-item:hover{border-color:var(--primary);background:var(--primary-light);color:var(--primary);}
.sw-fac-attach-ico{width:38px;height:38px;border-radius:9px;flex-shrink:0;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.95rem;}
.sw-fac-attach-info{flex:1;min-width:0;}
.sw-fac-attach-name{font-size:.84rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.sw-fac-attach-sub{font-size:.7rem;color:var(--text-muted);margin-top:.08rem;}

/* ── COMMENTS ── */
.sw-comments-section{padding:1.25rem 0;}
.sw-comments-title{display:flex;align-items:center;gap:.5rem;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);margin-bottom:.85rem;}
.sw-comment-list{display:flex;flex-direction:column;gap:.55rem;margin-bottom:.85rem;}
.sw-comment-item{display:flex;gap:.65rem;align-items:flex-start;}
.sw-comment-avatar{width:32px;height:32px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;font-size:.72rem;font-weight:700;display:flex;align-items:center;justify-content:center;}
.sw-comment-bubble{flex:1;min-width:0;background:var(--bg);border:1px solid var(--border);border-radius:0 12px 12px 12px;padding:.6rem .85rem;}
.sw-comment-meta{font-size:.72rem;font-weight:700;margin-bottom:.2rem;display:flex;align-items:center;gap:.5rem;}
.sw-comment-author{color:var(--text);}
.sw-comment-time{color:var(--text-muted);font-weight:500;}
.sw-comment-text{font-size:.84rem;line-height:1.55;color:var(--text);}
.sw-comment-input-row{display:flex;align-items:flex-end;gap:.55rem;}
.sw-comment-self-avatar{width:32px;height:32px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;font-size:.72rem;font-weight:700;display:flex;align-items:center;justify-content:center;}
.sw-comment-box{flex:1;border:1.5px solid var(--border);border-radius:24px;padding:.55rem 1rem;font-size:.88rem;line-height:1.4;background:var(--bg);color:var(--text);resize:none;transition:border-color var(--trans),box-shadow var(--trans);overflow:hidden;min-height:38px;max-height:120px;}
.sw-comment-box:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);}
.sw-comment-send{width:34px;height:34px;border-radius:50%;border:none;flex-shrink:0;background:var(--primary);color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.85rem;transition:all var(--trans);}
.sw-comment-send:hover{background:var(--primary-dark);}
.sw-comment-send:disabled{opacity:.4;cursor:not-allowed;}

/* ── RIGHT COLUMN ── */
.sw-right{position:sticky;top:calc(var(--nav-h) + 1.25rem);}
.sw-your-work{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-md);overflow:hidden;margin-bottom:1rem;}
.sw-yw-head{padding:.85rem 1.1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.sw-yw-title{font-size:.92rem;font-weight:700;}
.sw-yw-status{font-size:.72rem;font-weight:700;padding:.2rem .6rem;border-radius:20px;}
.sw-yw-status.not-sub{background:var(--bg);color:var(--text-muted);border:1px solid var(--border);}
.sw-yw-status.turned-in{background:var(--primary-light);color:var(--primary-dark);border:1px solid rgba(26,158,120,.3);}
.sw-yw-status.late{background:#fdecea;color:var(--danger);border:1px solid #f5c2c7;}
.sw-yw-status.graded{background:#fff8e8;color:#92400e;border:1px solid #fcd34d;}
.sw-yw-body{padding:1rem 1.1rem;}

.sw-sub-file-item{display:flex;align-items:center;gap:.65rem;padding:.6rem .75rem;border-radius:10px;background:var(--bg);border:1px solid var(--border);margin-bottom:.45rem;text-decoration:none;color:var(--text);transition:all var(--trans);}
.sw-sub-file-item:hover{border-color:var(--primary);background:var(--primary-light);}
.sw-sub-file-thumb{width:40px;height:40px;border-radius:8px;background:var(--surface);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;}
.sw-sub-file-info{flex:1;min-width:0;}
.sw-sub-file-name{font-size:.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.sw-sub-file-type{font-size:.68rem;color:var(--text-muted);margin-top:.05rem;}
.sw-sub-file-actions{display:flex;align-items:center;gap:.25rem;flex-shrink:0;}
.sw-sub-file-action{width:28px;height:28px;border:0;background:transparent;color:var(--text-muted);border-radius:7px;display:flex;align-items:center;justify-content:center;text-decoration:none;cursor:pointer;transition:all .15s;}
.sw-sub-file-action:hover{background:var(--surface);color:var(--primary);}
.sw-sub-file-action.danger:hover{background:#fdecea;color:var(--danger);}
.sw-sub-file-del{width:26px;height:26px;border:none;background:none;cursor:pointer;color:var(--text-muted);border-radius:6px;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;}
.sw-sub-file-del:hover{background:#fdecea;color:var(--danger);}

.sw-add-file-btn{width:100%;padding:.62rem .9rem;border-radius:10px;border:1.5px dashed var(--border);background:var(--bg);color:var(--text-muted);font-size:.84rem;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:all var(--trans);position:relative;margin-bottom:.55rem;}
.sw-add-file-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
.sw-add-file-btn input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}

.sw-text-toggle{width:100%;padding:.55rem .9rem;border-radius:10px;border:1.5px solid var(--border);background:var(--surface);color:var(--text-muted);font-size:.82rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.5rem;transition:all var(--trans);margin-bottom:.55rem;}
.sw-text-toggle:hover{border-color:var(--primary);color:var(--primary);}
.sw-text-area-wrap{display:none;margin-bottom:.55rem;}
.sw-text-area-wrap.show{display:block;}
.sw-text-area{width:100%;min-height:90px;padding:.65rem .85rem;border:1.5px solid var(--border);border-radius:10px;font-size:.88rem;line-height:1.55;background:var(--bg);color:var(--text);resize:vertical;transition:border-color var(--trans),box-shadow var(--trans);}
.sw-text-area:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);}

.sw-btn-submit{width:100%;padding:.75rem;border-radius:10px;background:var(--primary);color:#fff;border:none;font-size:.9rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.45rem;box-shadow:0 2px 10px rgba(26,158,120,.3);transition:all var(--trans);}
.sw-btn-submit:hover:not(:disabled){background:var(--primary-dark);transform:translateY(-1px);}
.sw-btn-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;}
.sw-btn-unsubmit{width:100%;padding:.65rem;border-radius:10px;margin-top:.5rem;background:var(--surface);color:var(--text-muted);border:1.5px solid var(--border);font-size:.84rem;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem;transition:all var(--trans);}
.sw-btn-unsubmit:hover{border-color:var(--danger);color:var(--danger);background:#fdecea;}

.sw-grade-display{display:flex;align-items:center;gap:.75rem;padding:.75rem;background:var(--primary-light);border-radius:10px;border:1px solid rgba(26,158,120,.2);margin-bottom:.75rem;}
.sw-grade-circle{width:52px;height:52px;border-radius:50%;border:3px solid var(--primary);background:var(--surface);flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;}
.sw-grade-num{font-size:1.1rem;font-weight:800;color:var(--primary);font-family:'DM Mono',monospace;line-height:1;}
.sw-grade-of{font-size:.55rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;}
.sw-grade-info-w{flex:1;min-width:0;}
.sw-grade-label{font-size:.84rem;font-weight:700;margin-bottom:.1rem;}
.sw-grade-sub{font-size:.72rem;color:var(--text-muted);}
.sw-feedback-box{padding:.6rem .8rem;border-radius:9px;background:var(--bg);border:1px solid var(--border);font-size:.82rem;line-height:1.55;color:var(--text);white-space:pre-wrap;margin-bottom:.75rem;}

.sw-info-panel{display:flex;flex-direction:column;gap:.75rem;}
.sw-info-row{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.65rem .75rem;border:1px solid var(--border);border-radius:10px;background:var(--bg);font-size:.82rem;}
.sw-info-row span{color:var(--text-muted);font-weight:700;}
.sw-info-row strong{color:var(--text);text-align:right;}
.sw-result-compact{gap:.7rem;}
.sw-result-summary{display:flex;align-items:center;gap:.75rem;padding:.8rem;border:1px solid var(--border);border-radius:12px;background:var(--bg);}
.sw-result-icon{width:38px;height:38px;border-radius:10px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.sw-result-title{font-size:.9rem;font-weight:800;color:var(--text);}
.sw-result-meta{margin-top:.1rem;font-size:.76rem;line-height:1.35;color:var(--text-muted);font-weight:700;}
.sw-action-btn{width:100%;padding:.75rem;border-radius:10px;border:none;background:var(--primary);color:#fff;font-size:.9rem;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.45rem;text-decoration:none;transition:all var(--trans);}
.sw-action-btn:hover{background:var(--primary-dark);transform:translateY(-1px);}
.sw-action-btn.secondary{background:var(--accent);}.sw-action-btn.secondary:hover{filter:brightness(.94);}
.sw-action-btn.ghost{background:var(--surface);color:var(--text-muted);border:1.5px solid var(--border);}
.sw-action-btn.ghost:hover{background:var(--bg);color:var(--text);transform:none;}
.sw-action-btn:disabled{opacity:.55;cursor:not-allowed;transform:none;}
.sw-note-box{padding:.75rem .85rem;border-radius:10px;background:var(--primary-light);border:1px solid rgba(26,158,120,.22);color:var(--primary-dark);font-size:.82rem;line-height:1.55;font-weight:600;}

/* Private comments */
.sw-private-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-md);overflow:hidden;}
.sw-private-head{padding:.8rem 1.1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.5rem;font-size:.82rem;font-weight:700;color:var(--text-muted);}
.sw-private-body{padding:.85rem 1.1rem;}
.sw-private-list{display:flex;flex-direction:column;gap:.5rem;margin-bottom:.7rem;}
.sw-private-item{display:flex;gap:.55rem;align-items:flex-start;}
.sw-private-avatar{width:28px;height:28px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;}
.sw-private-bubble{flex:1;min-width:0;}
.sw-private-meta{font-size:.68rem;font-weight:700;color:var(--text-muted);margin-bottom:.15rem;display:flex;gap:.4rem;}
.sw-private-author{color:var(--text);}
.sw-private-text{font-size:.8rem;line-height:1.5;color:var(--text);}
.sw-private-input-row{display:flex;align-items:center;gap:.4rem;}
.sw-private-box{flex:1;border:1.5px solid var(--border);border-radius:20px;padding:.45rem .9rem;font-size:.82rem;background:var(--bg);color:var(--text);transition:border-color var(--trans);}
.sw-private-box:focus{outline:none;border-color:var(--primary);}
.sw-private-box::placeholder{color:var(--text-muted);}
.sw-private-send{width:30px;height:30px;border-radius:50%;border:none;flex-shrink:0;background:var(--primary);color:#fff;cursor:pointer;font-size:.78rem;display:flex;align-items:center;justify-content:center;transition:all var(--trans);}
.sw-private-send:hover{background:var(--primary-dark);}
.sw-private-send:disabled{opacity:.4;cursor:not-allowed;}
.sw-private-note{font-size:.68rem;color:var(--text-muted);margin-top:.5rem;line-height:1.4;}

/* Loading / Error */
.sw-state{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:5rem 2rem;text-align:center;grid-column:1/-1;}
.sw-spinner{width:36px;height:36px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:sw-spin .7s linear infinite;margin-bottom:.85rem;}
@keyframes sw-spin{to{transform:rotate(360deg);}}
.sw-state-icon{font-size:2.5rem;margin-bottom:.75rem;opacity:.3;}
.sw-state-title{font-size:1.05rem;font-weight:700;margin-bottom:.3rem;}
.sw-state-sub{font-size:.84rem;color:var(--text-muted);max-width:320px;line-height:1.6;}

.sw-viewer-backdrop{position:fixed;inset:0;z-index:1000;display:none;align-items:stretch;justify-content:stretch;padding:0;background:rgba(9,17,30,.72);}
.sw-viewer-backdrop.show{display:flex;}
.sw-viewer-shell{width:100vw;height:100vh;display:flex;flex-direction:column;overflow:hidden;border-radius:0;background:#0b1220;box-shadow:none;}
.sw-viewer-toolbar{height:54px;display:flex;align-items:center;gap:.55rem;padding:.55rem .75rem;background:#121b2b;color:#fff;border-bottom:1px solid rgba(255,255,255,.1);}
.sw-viewer-title{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.9rem;font-weight:800;}
.sw-viewer-badge{flex-shrink:0;border:1px solid rgba(126,224,191,.28);border-radius:999px;background:rgba(26,158,120,.16);color:#7ee0bf;padding:.18rem .5rem;font-size:.68rem;font-weight:900;}
.sw-viewer-btn{width:34px;height:34px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.14);border-radius:9px;background:rgba(255,255,255,.06);color:#fff;text-decoration:none;cursor:pointer;}
.sw-viewer-btn:hover{background:rgba(255,255,255,.12);}
.sw-viewer-page-controls{display:none;align-items:center;gap:.45rem;margin-left:auto;}
.sw-viewer-page-controls.show{display:flex;}
.sw-viewer-page-label{min-width:72px;text-align:center;font-size:.82rem;font-weight:800;color:#e5e7eb;}
.sw-viewer-content{flex:1;min-height:0;display:grid;grid-template-columns:minmax(0,1fr) 340px;background:#0b1220;}
.sw-viewer-main{min-width:0;min-height:0;display:flex;overflow:hidden;background:#111;}
.sw-viewer-body{flex:1;min-height:0;display:flex;align-items:flex-start;justify-content:center;overflow:auto;background:#222;padding:1rem;}
.sw-viewer-canvas{display:block;background:#fff;box-shadow:0 6px 22px rgba(0,0,0,.35);max-width:none;}
.sw-viewer-frame{width:100%;height:100%;border:0;background:#fff;}
.sw-viewer-img{display:block;max-width:100%;max-height:100%;object-fit:contain;}
.sw-viewer-media{width:min(900px,92%);max-height:86%;}
.sw-viewer-text{align-self:stretch;width:100%;min-height:100%;margin:0;padding:1rem;overflow:auto;background:#fff;color:#1c2027;white-space:pre-wrap;font:500 .86rem/1.55 "DM Mono",Consolas,monospace;}
.sw-viewer-message{max-width:520px;padding:2rem;text-align:center;color:#cbd5e1;}
.sw-viewer-message i{font-size:2.2rem;color:#7ee0bf;margin-bottom:.8rem;}
.sw-viewer-message h3{font-size:1.05rem;color:#fff;margin-bottom:.35rem;}
.sw-viewer-message p{font-size:.9rem;line-height:1.5;margin-bottom:1rem;}
.sw-viewer-actions{display:flex;gap:.6rem;flex-wrap:wrap;justify-content:center;}
.sw-viewer-actions a{display:inline-flex;align-items:center;gap:.4rem;padding:.58rem .85rem;border-radius:9px;background:var(--primary);color:#fff;text-decoration:none;font-weight:800;font-size:.82rem;}
.sw-viewer-comments{min-width:0;min-height:0;display:flex;flex-direction:column;background:var(--surface);border-left:1px solid var(--border);}
.sw-viewer-comments-head{padding:1rem;border-bottom:1px solid var(--border);}
.sw-viewer-comments-title{font-size:.9rem;font-weight:900;color:var(--text);display:flex;align-items:center;gap:.45rem;}
.sw-viewer-comments-page{margin-top:.25rem;font-size:.76rem;color:var(--text-muted);font-weight:800;}
.sw-viewer-comment-list{flex:1;min-height:0;overflow:auto;padding:1rem;display:flex;flex-direction:column;gap:.65rem;}
.sw-viewer-comment-empty{margin:auto;text-align:center;color:var(--text-muted);font-size:.84rem;line-height:1.5;}
.sw-viewer-comment{padding:.65rem .75rem;border-radius:12px;background:var(--bg);border:1px solid var(--border);}
.sw-viewer-comment-meta{font-size:.68rem;font-weight:800;color:var(--text-muted);margin-bottom:.25rem;}
.sw-viewer-comment-text{font-size:.84rem;line-height:1.5;color:var(--text);white-space:pre-wrap;}
.sw-viewer-comment-form{padding:1rem;border-top:1px solid var(--border);display:flex;gap:.55rem;align-items:flex-end;background:var(--surface);}
.sw-viewer-comment-input{flex:1;min-height:42px;max-height:120px;resize:none;border:1.5px solid var(--border);border-radius:12px;padding:.65rem .75rem;background:var(--bg);color:var(--text);font-size:.84rem;line-height:1.35;}
.sw-viewer-comment-input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-mid);}
.sw-viewer-comment-send{width:38px;height:38px;border:0;border-radius:11px;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;}
.sw-viewer-comment-send:disabled{opacity:.45;cursor:not-allowed;}

@media(max-width:700px){
  .sw-page{grid-template-columns:1fr;}
  .sw-right{position:static;}
  .sw-nav-crumb{display:none;}
  .sw-post-header-info{display:block;}
  .sw-post-pts-row{display:flex;align-items:flex-start;flex-direction:row;}
  .sw-due-badge{width:100%;justify-content:space-between;margin-left:0;}
  .sw-viewer-content{grid-template-columns:1fr;grid-template-rows:minmax(0,1fr) 260px;}
  .sw-viewer-comments{border-left:0;border-top:1px solid var(--border);}
  .sw-viewer-title{font-size:.8rem;}
}
@media(max-width:480px){
  :root{--nav-h:54px;}
  .sw-post-title{font-size:1.2rem;}
  .sw-nav-brand span{display:none;}
}
</style>
</head>
<body>

<!-- TOP NAV -->
<nav class="sw-nav">
  <a class="sw-nav-back" id="swBackBtn" href="student.php"><i class="fas fa-arrow-left"></i></a>
  <a class="sw-nav-brand" href="student.php">
    <div class="sw-nav-logo"><i class="fas fa-graduation-cap"></i></div>
    <span>TERE<strong>LEARN</strong></span>
  </a>
  <span style="color:var(--border);font-size:1.1rem;flex-shrink:0;">›</span>
  <div class="sw-nav-crumb" id="swNavCrumb">Loading…</div>
  <div class="sw-nav-right">
    <button class="sw-icon-btn" onclick="toggleDark()" title="Dark mode"><i class="fas fa-moon"></i></button>
  </div>
  <div class="sw-nav-progress" id="swNavProgress"><span id="swNavProgressBar"></span></div>
</nav>

<!-- PAGE GRID -->
<div class="sw-page" id="swPage">

  <div id="swStateLoading" class="sw-state">
    <div class="sw-spinner"></div>
    <div class="sw-state-title">Loading…</div>
  </div>

  <div id="swStateError" class="sw-state" style="display:none">
    <div class="sw-state-icon"><i class="fas fa-triangle-exclamation"></i></div>
    <div class="sw-state-title">Could not load</div>
    <div class="sw-state-sub" id="swErrorMsg">Something went wrong.</div>
  </div>

  <!-- LEFT -->
  <div class="sw-left" id="swLeft" style="display:none">

    <div class="sw-post-header">
      <div class="sw-post-type-ico" id="swTypeIcon"><i class="fas fa-tasks"></i></div>
      <div class="sw-post-header-info">
        <div class="sw-post-title" id="swPostTitle">—</div>
        <div class="sw-post-byline" id="swPostByline"></div>
        <div class="sw-post-pts-row">
          <span class="sw-pts-badge" id="swPtsBadge" style="display:none">
            <i class="fas fa-star" style="color:var(--primary);font-size:.8rem;"></i>
            <span id="swPtsVal">—</span> points
          </span>
          <span class="sw-due-badge" id="swDueBadge" style="display:none">
            Due <span id="swDueVal">—</span>
          </span>
        </div>
      </div>
    </div>

    <div class="sw-post-description" id="swPostDesc"></div>

    <div class="sw-fac-attachments" id="swFacAttach" style="display:none"></div>

    <div class="sw-comments-section">
      <div class="sw-comments-title"><i class="fas fa-comments" style="font-size:.9rem;"></i> Class comments</div>
      <div class="sw-comment-list" id="swCommentList">
        <div style="font-size:.82rem;color:var(--text-muted);" id="swCommentEmpty">No comments yet.</div>
      </div>
      <div class="sw-comment-input-row">
        <div class="sw-comment-self-avatar" id="swSelfAvatar">ST</div>
        <textarea class="sw-comment-box" id="swCommentBox" placeholder="Add class comment…" rows="1"
          onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendComment();}"
          oninput="autoResize(this);document.getElementById('swCommentSend').disabled=!this.value.trim();"></textarea>
        <button class="sw-comment-send" id="swCommentSend" onclick="sendComment()" disabled>
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
    </div>

  </div>

  <!-- RIGHT -->
  <div class="sw-right" id="swRight" style="display:none">

    <!-- Your Work -->
    <div class="sw-your-work">
      <div class="sw-yw-head">
        <span class="sw-yw-title" id="swYwTitle">Your work</span>
        <span class="sw-yw-status not-sub" id="swYwStatus">Assigned</span>
      </div>
      <div class="sw-yw-body" id="swYwBody">

        <!-- Grade -->
        <div id="swGradeDisplay" style="display:none">
          <div class="sw-grade-display">
            <div class="sw-grade-circle">
              <div class="sw-grade-num" id="swGradeNum">—</div>
              <div class="sw-grade-of" id="swGradeOf">/ —</div>
            </div>
            <div class="sw-grade-info-w">
              <div class="sw-grade-label">Graded</div>
              <div class="sw-grade-sub" id="swGradePct"></div>
            </div>
          </div>
          <div id="swFeedbackSection" style="display:none;margin-bottom:.75rem;">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.3rem;">Feedback</div>
            <div class="sw-feedback-box" id="swFeedbackBox"></div>
          </div>
        </div>

        <!-- Submitted files -->
        <div id="swSubFiles"></div>
        <!-- Pending new files -->
        <div id="swNewFiles"></div>

        <!-- Add file / note (hidden when locked) -->
        <div id="swAddFileWrap">
          <button class="sw-add-file-btn">
            <input type="file" id="swFileInput"
              accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.txt,.mp4,.mp3,.wav,.zip"
              onchange="handleFiles(this.files)">
            <i class="fas fa-plus"></i> Add or create
          </button>
          <button class="sw-text-toggle" onclick="toggleTextArea()">
            <i class="fas fa-pen"></i> Add a note
          </button>
          <div class="sw-text-area-wrap" id="swTextAreaWrap">
            <textarea class="sw-text-area" id="swTextInput" placeholder="Write a note for your teacher…" rows="3"></textarea>
          </div>
        </div>

        <button class="sw-btn-submit" id="swSubmitBtn" onclick="submitWork()">
          <i class="fas fa-paper-plane"></i> <span id="swSubmitLabel">Turn in</span>
        </button>
        <button class="sw-btn-unsubmit" id="swUnsubmitBtn" style="display:none" onclick="enableResubmit()">
          <i class="fas fa-pen-to-square"></i> Edit submission
        </button>
        <button class="sw-btn-unsubmit" id="swDeleteSubmissionBtn" style="display:none" onclick="deleteSubmission()">
          <i class="fas fa-trash"></i> Delete submission
        </button>

      </div>
    </div>

    <!-- Private comments -->
    <div class="sw-private-card">
      <div class="sw-private-head"><i class="fas fa-user-lock"></i> Private comments</div>
      <div class="sw-private-body">
        <div class="sw-private-list" id="swPrivateList">
          <div style="font-size:.78rem;color:var(--text-muted);" id="swPrivateEmpty">No private comments yet.</div>
        </div>
        <div class="sw-private-input-row">
          <div class="sw-private-avatar" id="swPrivateAvatar">ST</div>
          <input class="sw-private-box" id="swPrivateBox" type="text"
            placeholder="Add private comment…"
            onkeydown="if(event.key==='Enter')sendPrivateComment()"
            oninput="document.getElementById('swPrivateSend').disabled=!this.value.trim()">
          <button class="sw-private-send" id="swPrivateSend" onclick="sendPrivateComment()" disabled>
            <i class="fas fa-paper-plane"></i>
          </button>
        </div>
        <div class="sw-private-note">Private comments are only visible to you and your teacher.</div>
      </div>
    </div>

  </div>
</div>

<div class="sw-viewer-backdrop" id="swFileViewer" aria-hidden="true">
  <div class="sw-viewer-shell" role="dialog" aria-modal="true" aria-labelledby="swViewerTitle">
    <div class="sw-viewer-toolbar">
      <span class="sw-viewer-title" id="swViewerTitle">File</span>
      <span class="sw-viewer-badge" id="swViewerBadge">FILE</span>
      <div class="sw-viewer-page-controls" id="swViewerPageControls">
        <button class="sw-viewer-btn" type="button" id="swViewerPrev" title="Previous page"><i class="fas fa-chevron-left"></i></button>
        <span class="sw-viewer-page-label" id="swViewerPageLabel">1 / 1</span>
        <button class="sw-viewer-btn" type="button" id="swViewerNext" title="Next page"><i class="fas fa-chevron-right"></i></button>
        <button class="sw-viewer-btn" type="button" id="swViewerZoomOut" title="Zoom out"><i class="fas fa-search-minus"></i></button>
        <button class="sw-viewer-btn" type="button" id="swViewerZoomIn" title="Zoom in"><i class="fas fa-search-plus"></i></button>
      </div>
      <a class="sw-viewer-btn" id="swViewerDownload" href="#" target="_blank" download title="Download file"><i class="fas fa-download"></i></a>
      <button class="sw-viewer-btn" type="button" id="swViewerClose" title="Close viewer"><i class="fas fa-times"></i></button>
    </div>
    <div class="sw-viewer-content">
      <div class="sw-viewer-main"><div class="sw-viewer-body" id="swViewerBody"></div></div>
      <aside class="sw-viewer-comments">
        <div class="sw-viewer-comments-head">
          <div class="sw-viewer-comments-title"><i class="fas fa-comments"></i> Page comments</div>
          <div class="sw-viewer-comments-page" id="swViewerCommentPage">Page 1</div>
        </div>
        <div class="sw-viewer-comment-list" id="swViewerCommentList">
          <div class="sw-viewer-comment-empty">No comments yet.</div>
        </div>
        <div class="sw-viewer-comment-form">
          <textarea class="sw-viewer-comment-input" id="swViewerCommentInput" rows="1" placeholder="Add page comment..."></textarea>
          <button class="sw-viewer-comment-send" type="button" id="swViewerCommentSend" disabled><i class="fas fa-paper-plane"></i></button>
        </div>
      </aside>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const POST_ID  = <?= json_encode($post_id) ?>;
const CLASS_ID = <?= json_encode($class_id) ?>;
const API      = 'API/student/studentClassroom/';
const swFileViewer = document.getElementById('swFileViewer');
const swViewerBody = document.getElementById('swViewerBody');
const swViewerTitle = document.getElementById('swViewerTitle');
const swViewerBadge = document.getElementById('swViewerBadge');
const swViewerDownload = document.getElementById('swViewerDownload');
const swViewerPageControls = document.getElementById('swViewerPageControls');
const swViewerPageLabel = document.getElementById('swViewerPageLabel');
const swViewerCommentPage = document.getElementById('swViewerCommentPage');
const swViewerCommentList = document.getElementById('swViewerCommentList');
const swViewerCommentInput = document.getElementById('swViewerCommentInput');
const swViewerCommentSend = document.getElementById('swViewerCommentSend');

let postData=null,submissionData=null,pendingFiles=[],isResubmitting=false,myInitials='ST';
let viewerState={attachId:'',page:1,pages:1,scale:1.2,pdf:null,commentsEnabled:false,renderToken:0};

function esc(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function fmtDate(d){if(!d)return'';const dt=new Date(d.includes('T')?d:d.replace(' ','T'));if(isNaN(dt))return d;return dt.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});}
function fmtDateTime(d){if(!d)return'';const dt=new Date(d.includes('T')?d:d.replace(' ','T'));if(isNaN(dt))return d;return dt.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})+', '+dt.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'});}
function fmtRelative(d){
  if(!d)return'';
  const dt=new Date(String(d).includes('T')?d:String(d).replace(' ','T'));
  if(isNaN(dt))return d;
  const seconds=Math.max(0,Math.floor((Date.now()-dt.getTime())/1000));
  const minute=60,hour=3600,day=86400,month=2592000,year=31536000;
  if(seconds<minute)return'1m';
  if(seconds<hour)return Math.max(1,Math.floor(seconds/minute))+'m';
  if(seconds<day)return Math.max(1,Math.floor(seconds/hour))+'h';
  if(seconds<month)return Math.max(1,Math.floor(seconds/day))+'d';
  if(seconds<year)return Math.max(1,Math.floor(seconds/month))+'mo';
  return Math.max(1,Math.floor(seconds/year))+'y';
}
function fmtDueCompact(d){
  if(!d)return'';
  const dt=new Date(String(d).includes('T')?d:String(d).replace(' ','T'));
  if(isNaN(dt))return d;
  const time=dt.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'});
  const date=dt.toLocaleDateString('en-US',{day:'numeric',month:'long'});
  return `${time} · ${date}`;
}
function isOverdue(d){if(!d)return false;return new Date()>new Date(d.includes('T')?d:d.replace(' ','T'));}
function fileIcon(n){const e=(n||'').split('.').pop().toLowerCase();const m={pdf:'fa-file-pdf',doc:'fa-file-word',docx:'fa-file-word',ppt:'fa-file-powerpoint',pptx:'fa-file-powerpoint',xls:'fa-file-excel',xlsx:'fa-file-excel',jpg:'fa-file-image',jpeg:'fa-file-image',png:'fa-file-image',gif:'fa-file-image',webp:'fa-file-image',mp4:'fa-file-video',mp3:'fa-file-audio',wav:'fa-file-audio',zip:'fa-file-zipper',txt:'fa-file-lines'};return'fas '+(m[e]||'fa-file');}
function fileColor(n){const e=(n||'').split('.').pop().toLowerCase();const c={pdf:'#e53935',doc:'#1565c0',docx:'#1565c0',ppt:'#e65100',pptx:'#e65100',xls:'#2e7d32',xlsx:'#2e7d32',jpg:'#6a1b9a',jpeg:'#6a1b9a',png:'#6a1b9a',gif:'#6a1b9a',mp4:'#0277bd',mp3:'#ad1457',zip:'#4e342e'};return c[e]||'var(--text-muted)';}
function fileTypeName(n){const e=(n||'').split('.').pop().toUpperCase();const l={PDF:'PDF Document',DOC:'Word Document',DOCX:'Word Document',PPT:'PowerPoint',PPTX:'PowerPoint',XLS:'Excel Spreadsheet',XLSX:'Excel Spreadsheet',JPG:'JPEG Image',JPEG:'JPEG Image',PNG:'PNG Image',GIF:'GIF Image',MP4:'MP4 Video',MP3:'MP3 Audio',ZIP:'ZIP Archive',TXT:'Text File'};return l[e]||(e+' File');}
function fmtSize(b){if(!b)return'';if(b<1024)return b+'B';if(b<1048576)return(b/1024).toFixed(1)+' KB';return(b/1048576).toFixed(1)+' MB';}
function initials(n){const p=(n||'').trim().split(/\s+/);return((p[0]?.[0]||'')+(p[1]?.[0]||'')).toUpperCase()||'?';}
function autoResize(el){el.style.height='auto';el.style.height=el.scrollHeight+'px';}
function fileExt(v){const clean=String(v||'').split('?')[0].split('#')[0];const part=clean.split('.').pop();return part&&part!==clean?part.toLowerCase():'';}
function setNavProgress(percent){
  const wrap=document.getElementById('swNavProgress');
  const bar=document.getElementById('swNavProgressBar');
  if(!wrap||!bar)return;
  const value=Math.max(0,Math.min(100,Number(percent)||0));
  wrap.classList.add('active');
  bar.style.width=value+'%';
}
function finishNavProgress(){
  setNavProgress(100);
  window.setTimeout(resetNavProgress,450);
}
function resetNavProgress(){
  const wrap=document.getElementById('swNavProgress');
  const bar=document.getElementById('swNavProgressBar');
  if(!wrap||!bar)return;
  wrap.classList.remove('active');
  bar.style.width='0%';
}
function postKind(p){
  const text=[p?.post_type,p?.type_key,p?.sub_label,p?.type_label].filter(Boolean).join(' ').toLowerCase();
  if(text.includes('lesson'))return'lesson';
  if(text.includes('activity'))return'activity';
  if(text.includes('quiz'))return'quiz';
  if(text.includes('exam'))return'exam';
  if(text.includes('assignment'))return'assignment';
  return'post';
}
function detailUrl(file, extra={}){
  const params=new URLSearchParams({post_id:POST_ID});
  if(CLASS_ID)params.set('class_id',CLASS_ID);
  Object.entries(extra).forEach(([k,v])=>{if(v!==undefined&&v!==null&&v!=='')params.set(k,v);});
  return file+'?'+params.toString();
}
function secondsLabel(seconds){
  const value=parseInt(seconds,10)||0;
  if(!value)return'No limit';
  if(value<60)return value+' sec';
  const mins=Math.floor(value/60), rest=value%60;
  return rest?`${mins}m ${rest}s`:`${mins} min`;
}
function titleCase(v){return String(v||'').replace(/[_-]+/g,' ').replace(/\b\w/g,c=>c.toUpperCase());}
function setWorkStatus(cls,text){const b=document.getElementById('swYwStatus');b.className='sw-yw-status '+cls;b.textContent=text;}
function infoRow(label,value){return value!==undefined&&value!==null&&String(value)!==''?`<div class="sw-info-row"><span>${esc(label)}</span><strong>${esc(value)}</strong></div>`:'';}
function parseTs(v){if(!v)return null;const d=new Date(String(v).includes('T')?v:String(v).replace(' ','T'));return isNaN(d)?null:d.getTime();}
function assessmentUrl(kind){return detailUrl('takequiz.php',{kind:String(kind||'quiz').toLowerCase()==='exam'?'exam':'quiz'});}
function resultUrl(){return detailUrl('quiz_result.php');}
function submittedFiles(sub){
  if(!sub)return[];
  const files=Array.isArray(sub.files)?sub.files.slice():[];
  if(sub.file_name){
    const sid=sub.submission_id||sub.id;
    files.push({file_name:sub.file_name,file_path:sid?`API/student/studentClassroom/download_submission.php?id=${encodeURIComponent(sid)}`:(sub.file_path||''),file_size:sub.file_size,mime_type:sub.mime_type});
  }
  return files;
}
function renderSubmittedFiles(canRemove=false){
  const files=submittedFiles(submissionData);
  const box=document.getElementById('swSubFiles');
  if(!box)return;
  box.innerHTML=files.length?files.map(f=>`
    <div class="sw-sub-file-item">
      <div class="sw-sub-file-thumb" style="color:${fileColor(f.file_name||'')}"><i class="${fileIcon(f.file_name||'')}"></i></div>
      <div class="sw-sub-file-info">
        <div class="sw-sub-file-name">${esc(f.file_name||'File')}</div>
        <div class="sw-sub-file-type">${fileTypeName(f.file_name||'')}</div>
      </div>
      <div class="sw-sub-file-actions">
        <a class="sw-sub-file-action" href="${esc(f.file_path||'')}" target="_blank" title="Download"><i class="fas fa-download"></i></a>
        ${canRemove?`<button type="button" class="sw-sub-file-action danger" title="Remove file" onclick="deleteSubmissionFile()"><i class="fas fa-xmark"></i></button>`:''}
      </div>
    </div>`).join(''):'';
}
function renderLessonPanel(p,atts){
  document.getElementById('swYwTitle').textContent='Lesson details';
  setWorkStatus('turned-in','Material');
  document.getElementById('swYwBody').innerHTML=`
    <div class="sw-info-panel">
      ${infoRow('Type','Lesson')}
      ${infoRow('Posted',fmtDateTime(p.created_at))}
      ${infoRow('Attachments',`${atts.length} file${atts.length===1?'':'s'} / link${atts.length===1?'':'s'}`)}
      ${p.lesson_period?infoRow('Coverage',titleCase(p.lesson_period)):''}
      <button class="sw-action-btn" type="button" onclick="openFirstLessonFile()">
        <i class="fas fa-eye"></i> View Lesson File
      </button>
      <div class="sw-note-box">Review the lesson material using the attachments in this post.</div>
    </div>`;
}
function renderAssessmentPanel(p,kind){
  const label=kind==='exam'?'Exam':'Quiz';
  document.getElementById('swYwTitle').textContent=label+' details';
  setWorkStatus('not-sub','Loading');
  document.getElementById('swYwBody').innerHTML='<div class="sw-info-panel"><div class="sw-note-box"><i class="fas fa-spinner fa-spin"></i> Loading assessment status...</div></div>';
  loadAssessmentState(kind);
}
async function loadAssessmentState(kind){
  try{
    const fd=new FormData();fd.append('post_id',POST_ID);
    const res=await fetch(API+'quiz/get_my_quiz_state.php',{method:'POST',body:fd,credentials:'same-origin'});
    const j=await res.json();
    if(!j||j.success!==true)throw new Error(j?.message||'Could not load assessment status.');
    renderAssessmentState(kind,j.data||{});
  }catch(e){
    setWorkStatus('late','Unavailable');
    document.getElementById('swYwBody').innerHTML=`<div class="sw-info-panel"><div class="sw-note-box">${esc(e.message||'Could not load assessment status.')}</div></div>`;
  }
}
function renderAssessmentState(kind,state){
  const p=postData,q=state.quiz||{},label=kind==='exam'?'Exam':'Quiz';
  if(!q.quiz_id){
    setWorkStatus('late','Unavailable');
    document.getElementById('swYwBody').innerHTML=`<div class="sw-info-panel">${infoRow('Type',label)}<div class="sw-note-box">This post does not have a published ${label.toLowerCase()} yet.</div></div>`;
    return;
  }
  const mode=String(q.quiz_mode||p.quiz_mode||'due_date').toLowerCase();
  const attempt=String(state.attempt_status||'').toLowerCase();
  const submitted=['submitted','completed','finished','graded','returned'].includes(attempt);
  const inProgress=['in_progress','started','ongoing'].includes(attempt);
  const released=!!(q.results_released_at||p.results_released_at);
  const forceClosed=Number(q.is_force_closed||p.is_force_closed||0)===1||!!q.live_ended_at;
  const forceOpen=Number(q.is_force_open||p.is_force_open||0)===1;
  const openAt=q.open_at||p.open_at||'';
  const closeAt=q.close_at||q.due_date||p.close_at||p.due_date||'';
  const now=Date.now();
  const beforeOpen=openAt&&parseTs(openAt)&&now<parseTs(openAt);
  const afterClose=closeAt&&parseTs(closeAt)&&now>parseTs(closeAt);
  const hasMakeup=!!state.has_makeup_access;
  let badgeCls='not-sub',badgeText='Ready';
  if(released&&submitted){badgeCls='graded';badgeText='Released';}
  else if(submitted){badgeCls='turned-in';badgeText='Submitted';}
  else if(inProgress){badgeCls='turned-in';badgeText='In progress';}
  else if(forceClosed||(afterClose&&!forceOpen&&!hasMakeup)){badgeCls='late';badgeText='Closed';}
  else if(beforeOpen&&!forceOpen){badgeText='Not open';}
  setWorkStatus(badgeCls,badgeText);
  const qCount=parseInt(p.question_count,10)||0;
  const pointTotal=parseFloat(p.question_points)||parseFloat(p.points)||0;
  const flags={submitted,inProgress,released,forceClosed,forceOpen,beforeOpen,afterClose,hasMakeup,mode,openAt,closeAt};
  if(released&&submitted){
    const meta=[
      pointTotal?`${pointTotal} point${pointTotal===1?'':'s'}`:'',
      qCount?`${qCount} question${qCount===1?'':'s'}`:'',
      mode==='live'?'Live mode':'Due-date mode'
    ].filter(Boolean).join(' / ');
    document.getElementById('swYwTitle').textContent='Results';
    document.getElementById('swYwBody').innerHTML=`
      <div class="sw-info-panel sw-result-compact">
        <div class="sw-result-summary">
          <div class="sw-result-icon"><i class="fas fa-file-lines"></i></div>
          <div>
            <div class="sw-result-title">Results are ready</div>
            <div class="sw-result-meta">${esc(meta||label)}</div>
          </div>
        </div>
        ${assessmentActionHtml(kind,state,flags)}
      </div>`;
    return;
  }
  document.getElementById('swYwBody').innerHTML=`
    <div class="sw-info-panel">
      ${infoRow('Type',label)}
      ${infoRow('Mode',mode==='live'?'Live mode':'Due-date mode')}
      ${infoRow('Questions',qCount?`${qCount} question${qCount===1?'':'s'}`:'Not set')}
      ${infoRow('Points',pointTotal?`${pointTotal} pts`:'Not set')}
      ${infoRow('Timer',secondsLabel(q.time_limit_seconds||p.time_limit_seconds))}
      ${infoRow('Attempts',q.max_attempts?`${q.max_attempts} max`:'Default')}
      ${mode==='live'?infoRow('Live start',q.live_started_at?fmtDateTime(q.live_started_at):'Waiting for faculty'):infoRow('Opens',openAt?fmtDateTime(openAt):'Open now')}
      ${mode==='live'?infoRow('Live end',q.live_ended_at?fmtDateTime(q.live_ended_at):'Not ended'):infoRow('Closes',closeAt?fmtDateTime(closeAt):'No close date')}
      ${state.makeup_valid_until?infoRow('Makeup access','Until '+fmtDateTime(state.makeup_valid_until)):''}
      ${infoRow('Status',badgeText)}
      ${assessmentActionHtml(kind,state,flags)}
      <div class="sw-note-box">Open, continue, or review this ${label.toLowerCase()} from this post page. Results appear only after the faculty releases them.</div>
    </div>`;
}
function assessmentActionHtml(kind,state,flags){
  const label=kind==='exam'?'Exam':'Quiz';
  const disabled=text=>`<button class="sw-action-btn ghost" disabled><i class="fas fa-lock"></i> ${esc(text)}</button>`;
  const link=(href,text,icon='fa-play')=>`<a class="sw-action-btn" href="${esc(href)}"><i class="fas ${icon}"></i> ${esc(text)}</a>`;
  if(flags.released&&flags.submitted)return link(resultUrl(),'See Results','fa-file-lines');
  if(flags.inProgress)return link(assessmentUrl(kind),'Continue '+label,'fa-play');
  if(flags.submitted)return disabled('Submitted - waiting for results');
  if(flags.forceClosed||(flags.afterClose&&!flags.forceOpen&&!flags.hasMakeup))return disabled(label+' closed');
  if(flags.beforeOpen&&!flags.forceOpen)return disabled('Opens '+fmtDateTime(flags.openAt));
  if(flags.mode==='live'&&!state.is_enrolled)return `<button class="sw-action-btn" onclick="joinAssessment('${esc(kind)}',this)"><i class="fas fa-right-to-bracket"></i> Join Waiting Room</button>`;
  return link(assessmentUrl(kind),(flags.mode==='live'?'Open Waiting Room':'Take '+label),'fa-up-right-from-square');
}
async function joinAssessment(kind,btn){
  if(btn){btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Joining...';}
  try{
    const fd=new FormData();fd.append('post_id',POST_ID);
    const res=await fetch(API+'quiz/enroll_quiz.php',{method:'POST',body:fd,credentials:'same-origin'});
    const j=await res.json();
    if(j&&j.success){window.location.href=assessmentUrl(kind);return;}
    throw new Error(j?.message||'Could not join assessment.');
  }catch(e){
    if(btn){btn.disabled=false;btn.innerHTML='<i class="fas fa-right-to-bracket"></i> Join Waiting Room';}
    Swal.fire({icon:'error',title:'Could not join',text:e.message||'Please try again.'});
  }
}

async function init(){
  try{
    const fd=new FormData();fd.append('post_id',POST_ID);
    const res=await fetch(API+'get_post_detail.php',{method:'POST',body:fd,credentials:'same-origin'});
    const j=await res.json();
    if(!j||j.status!=='success'){showError(j?.message||'Could not load post.');return;}
    postData=j.post;submissionData=j.submission||null;myInitials=j.student_initials||'ST';
    render();loadComments();loadPrivateComments();
  }catch(e){showError('Network error. Please try again.');}
}
function showError(msg){
  document.getElementById('swStateLoading').style.display='none';
  document.getElementById('swStateError').style.display='flex';
  document.getElementById('swErrorMsg').textContent=msg;
}

function render(){
  document.getElementById('swStateLoading').style.display='none';
  document.getElementById('swLeft').style.display='block';
  document.getElementById('swRight').style.display='block';
  const p=postData,sub=submissionData;
  const kind=postKind(p);
  const typeKey=(p.post_type||'').toLowerCase();
  const typeLabel=(p.type_label||p.sub_label||p.post_type||'Post');
  const isAssignment=typeKey==='assignment'||kind==='assignment'||typeLabel.toLowerCase().includes('assignment');
  const isActivity=typeKey==='activity'||kind==='activity'||typeLabel.toLowerCase().includes('activity');
  const due=p.due_date,overdue=isOverdue(due),hasSub=!!sub;
  const isGraded=hasSub&&sub.grade!==null&&sub.grade!==undefined&&sub.grade!=='';
  const isLate=hasSub&&(sub.is_late==1||sub.is_late==='1'||sub.status==='late');
  const isClosed=due&&overdue&&!hasSub&&isAssignment;

  if(CLASS_ID)document.getElementById('swBackBtn').href='studentClassRoom.php?class_id='+encodeURIComponent(CLASS_ID);
  document.getElementById('swNavCrumb').innerHTML='<b>'+esc(typeLabel)+'</b>';
  document.title=(p.title||'Submit Work')+' — Tere LEARN';

  const ico=document.getElementById('swTypeIcon');
  if(isAssignment){ico.style.cssText='background:#fff3e0;color:#e65100;width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;';ico.innerHTML='<i class="fas fa-file-lines"></i>';}
  else if(isActivity){ico.style.cssText='background:var(--accent-light);color:var(--accent);width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;';ico.innerHTML='<i class="fas fa-bolt"></i>';}
  else if(kind==='lesson'){ico.style.cssText='background:var(--primary-light);color:var(--primary);width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;';ico.innerHTML='<i class="fas fa-book-open"></i>';}
  else if(kind==='quiz'){ico.style.cssText='background:#f0e7e2;color:#795548;width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;';ico.innerHTML='<i class="fas fa-circle-question"></i>';}
  else if(kind==='exam'){ico.style.cssText='background:#fdecea;color:#d93025;width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;';ico.innerHTML='<i class="fas fa-clipboard-list"></i>';}
  else{ico.style.cssText='background:var(--primary-light);color:var(--primary);width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;';ico.innerHTML='<i class="fas fa-tasks"></i>';}

  document.getElementById('swPostTitle').textContent=p.title||'—';
  document.getElementById('swPostByline').textContent=(p.author_name||'Faculty')+' · '+fmtDate(p.created_at);
  if(p.points){document.getElementById('swPtsBadge').style.display='';document.getElementById('swPtsVal').textContent=p.points;}
  if(due){
    const dueBadge=document.getElementById('swDueBadge');
    dueBadge.style.display='';
    dueBadge.classList.toggle('overdue',overdue);
    dueBadge.innerHTML=`<span class="sw-due-label">${overdue?'LATE':'Due'}</span><span id="swDueVal">${esc(fmtDueCompact(due))}</span>`;
  }
  if(p.body)document.getElementById('swPostDesc').textContent=p.body;

  const atts=(p.attachments||[]).filter(a=>!a.student_id);
  if(atts.length){
    const w=document.getElementById('swFacAttach');w.style.display='block';
    w.innerHTML=atts.map(a=>{
      if(a.attach_type==='file')return`<button type="button" class="sw-fac-attach-item" data-file-trigger data-url="${esc(a.file_path||'')}" data-name="${esc(a.file_name||'File')}" data-mime="${esc(a.mime_type||'')}" data-attach-id="${esc(a.id||'')}"><div class="sw-fac-attach-ico" style="color:${fileColor(a.file_name||'')}"><i class="${fileIcon(a.file_name||'')}"></i></div><div class="sw-fac-attach-info"><div class="sw-fac-attach-name">${esc(a.file_name||'File')}</div><div class="sw-fac-attach-sub">${fileTypeName(a.file_name||'')} · ${fmtSize(a.file_size)}</div></div><i class="fas fa-eye" style="font-size:.78rem;color:var(--text-muted);flex-shrink:0;"></i></button>`;
      const isYT=a.attach_type==='youtube';
      return`<a class="sw-fac-attach-item" href="${esc(a.url||'')}" target="_blank"><div class="sw-fac-attach-ico" style="color:${isYT?'#d93025':'var(--accent)'}"><i class="${isYT?'fab fa-youtube':'fas fa-link'}"></i></div><div class="sw-fac-attach-info"><div class="sw-fac-attach-name">${esc(a.url||'Link')}</div><div class="sw-fac-attach-sub">${isYT?'YouTube Video':'External Link'}</div></div><i class="fas fa-arrow-up-right-from-square" style="font-size:.7rem;color:var(--text-muted);flex-shrink:0;"></i></a>`;
    }).join('');
  }

  document.getElementById('swSelfAvatar').textContent=myInitials;
  document.getElementById('swPrivateAvatar').textContent=myInitials;

  if(kind==='lesson'){
    renderLessonPanel(p,atts);
    return;
  }
  if(kind==='quiz'||kind==='exam'){
    renderAssessmentPanel(p,kind);
    return;
  }

  // Your Work status badge
  const badge=document.getElementById('swYwStatus');
  if(isGraded){badge.className='sw-yw-status graded';badge.textContent='Graded';}
  else if(hasSub&&isLate){badge.className='sw-yw-status late';badge.textContent='Turned in late';}
  else if(hasSub){badge.className='sw-yw-status turned-in';badge.textContent='Turned in';}
  else if(isClosed){badge.className='sw-yw-status late';badge.textContent='Closed';}
  else{badge.className='sw-yw-status not-sub';badge.textContent='Assigned';}

  // Grade
  if(isGraded){
    document.getElementById('swGradeDisplay').style.display='block';
    document.getElementById('swGradeNum').textContent=sub.grade;
    document.getElementById('swGradeOf').textContent=p.points?'/ '+p.points:'pts';
    document.getElementById('swGradePct').textContent=p.points?Math.round((sub.grade/p.points)*100)+'%':'';
    if(sub.feedback){document.getElementById('swFeedbackSection').style.display='block';document.getElementById('swFeedbackBox').textContent=sub.feedback;}
  }

  renderSubmittedFiles(false);
  if(hasSub&&sub.response_text){document.getElementById('swTextInput').value=sub.response_text;document.getElementById('swTextAreaWrap').classList.add('show');}
  if(hasSub&&!sub.response_text&&sub.comment){document.getElementById('swTextInput').value=sub.comment;document.getElementById('swTextAreaWrap').classList.add('show');}

  if(hasSub){
    lockForm(true);
    document.getElementById('swSubmitBtn').style.display='none';
    document.getElementById('swUnsubmitBtn').style.display=isGraded?'none':'flex';
    document.getElementById('swDeleteSubmissionBtn').style.display=isGraded?'none':'flex';
  }else if(isClosed){
    lockForm(true);
    document.getElementById('swSubmitBtn').disabled=true;
    document.getElementById('swSubmitBtn').innerHTML='<i class="fas fa-lock"></i> Submission closed';
  }else{
    document.getElementById('swSubmitLabel').textContent=isAssignment?'Turn in':'Submit';
  }
}

function handleFiles(fl){pendingFiles=Array.from(fl).slice(0,1);renderNewFiles();}
function renderNewFiles(){
  document.getElementById('swNewFiles').innerHTML=pendingFiles.map((f,i)=>`<div class="sw-sub-file-item"><div class="sw-sub-file-thumb" style="color:${fileColor(f.name)}"><i class="${fileIcon(f.name)}"></i></div><div class="sw-sub-file-info"><div class="sw-sub-file-name">${esc(f.name)}</div><div class="sw-sub-file-type">${fileTypeName(f.name)} · ${fmtSize(f.size)}</div></div><button class="sw-sub-file-del" onclick="removeFile(${i})"><i class="fas fa-xmark"></i></button></div>`).join('');
}
function removeFile(i){pendingFiles.splice(i,1);renderNewFiles();}
function toggleTextArea(){const w=document.getElementById('swTextAreaWrap');w.classList.toggle('show');if(w.classList.contains('show'))document.getElementById('swTextInput').focus();}
function lockForm(l){document.getElementById('swAddFileWrap').style.display=l?'none':'block';document.getElementById('swTextInput').disabled=l;}
function enableResubmit(){isResubmitting=true;lockForm(false);renderSubmittedFiles(true);document.getElementById('swUnsubmitBtn').style.display='none';document.getElementById('swSubmitBtn').style.display='flex';document.getElementById('swSubmitLabel').textContent='Resubmit';}

function uploadWithProgress(url, formData){
  return new Promise((resolve,reject)=>{
    const xhr=new XMLHttpRequest();
    xhr.open('POST',url,true);
    xhr.withCredentials=true;
    xhr.upload.onprogress=e=>{
      if(e.lengthComputable){
        setNavProgress(Math.max(6,Math.round((e.loaded/e.total)*96)));
      }else{
        setNavProgress(18);
      }
    };
    xhr.onload=()=>{
      try{
        const payload=JSON.parse(xhr.responseText||'{}');
        if(xhr.status>=200&&xhr.status<300)resolve(payload);
        else reject(new Error(payload.message||'Upload failed.'));
      }catch(e){
        reject(new Error('Upload failed.'));
      }
    };
    xhr.onerror=()=>reject(new Error('Network error.'));
    xhr.onabort=()=>reject(new Error('Upload cancelled.'));
    xhr.send(formData);
  });
}

async function deleteSubmission(){
  if(!submissionData)return;
  const ok=await Swal.fire({icon:'warning',title:'Delete submission?',text:'This removes your current submitted work for this post.',showCancelButton:true,confirmButtonText:'Delete',cancelButtonText:'Cancel',confirmButtonColor:'#d93025',reverseButtons:true});
  if(!ok.isConfirmed)return;
  const btn=document.getElementById('swDeleteSubmissionBtn');
  btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Deleting...';
  try{
    const fd=new FormData();fd.append('post_id',POST_ID);
    const res=await fetch(API+'delete_submission.php',{method:'POST',body:fd,credentials:'same-origin'});
    const j=await res.json();
    if(!j||j.status!=='success')throw new Error(j?.message||'Could not delete submission.');
    await Swal.fire({icon:'success',title:'Deleted',text:'Your submission was removed.',timer:1400,showConfirmButton:false});
    window.location.reload();
  }catch(e){
    btn.disabled=false;btn.innerHTML='<i class="fas fa-trash"></i> Delete submission';
    Swal.fire({icon:'error',title:'Could not delete',text:e.message||'Please try again.'});
  }
}

async function deleteSubmissionFile(){
  if(!submissionData)return;
  const ok=await Swal.fire({icon:'warning',title:'Remove sent file?',text:'This removes only the attached file. Your submission and note will stay.',showCancelButton:true,confirmButtonText:'Remove file',cancelButtonText:'Cancel',confirmButtonColor:'#d93025',reverseButtons:true});
  if(!ok.isConfirmed)return;
  try{
    const fd=new FormData();fd.append('post_id',POST_ID);
    const res=await fetch(API+'delete_submission_file.php',{method:'POST',body:fd,credentials:'same-origin'});
    const j=await res.json();
    if(!j||j.status!=='success')throw new Error(j?.message||'Could not remove file.');
    submissionData.file_name=null;
    submissionData.file_path=null;
    submissionData.file_size=null;
    submissionData.mime_type=null;
    submissionData.files=[];
    renderSubmittedFiles(isResubmitting);
    await Swal.fire({icon:'success',title:'File removed',timer:1300,showConfirmButton:false});
  }catch(e){
    Swal.fire({icon:'error',title:'Could not remove file',text:e.message||'Please try again.'});
  }
}

async function submitWork(){
  const isAssign=(postData.post_type||'').toLowerCase()==='assignment'||(postData.sub_label||'').toLowerCase().includes('assignment');
  const text=document.getElementById('swTextInput').value.trim();
  if(isAssign&&!pendingFiles.length&&!text&&!submissionData){Swal.fire({icon:'warning',title:'Nothing to turn in',text:'Please attach a file or add a note before submitting.'});return;}
  const ok=await Swal.fire({title:'Turn in?',html:`<div style="font-size:.86rem;line-height:1.7;text-align:left;">${pendingFiles.length?`<div><i class="fas fa-paperclip" style="color:#1a9e78;margin-right:.4rem;"></i>${pendingFiles.length} file${pendingFiles.length!==1?'s':''} attached</div>`:''} ${text?'<div><i class="fas fa-pen" style="color:#1f73db;margin-right:.4rem;"></i>Note included</div>':''}<div style="margin-top:.5rem;font-size:.78rem;color:#5f6368;">Your teacher will be notified. You can unsubmit to make changes.</div></div>`,icon:'question',showCancelButton:true,confirmButtonText:'<i class="fas fa-paper-plane"></i> Turn in',cancelButtonText:'Cancel',confirmButtonColor:'#1a9e78',reverseButtons:true});
  if(!ok.isConfirmed)return;
  const btn=document.getElementById('swSubmitBtn');
  btn.disabled=true;btn.innerHTML='<span style="width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:sw-spin .7s linear infinite;display:inline-block;"></span> Submitting…';
  try{
    const fd=new FormData();fd.append('post_id',POST_ID);fd.append('class_id',CLASS_ID);fd.append('comment',text);
    if(pendingFiles[0])fd.append('file',pendingFiles[0]);
    setNavProgress(4);
    const j=await uploadWithProgress(API+'submit_work.php',fd);
    if(!j||j.status!=='success'){resetNavProgress();btn.disabled=false;btn.innerHTML=`<i class="fas fa-paper-plane"></i> <span id="swSubmitLabel">${isResubmitting?'Resubmit':'Turn in'}</span>`;Swal.fire({icon:'error',title:'Failed',text:j?.message||'Please try again.'});return;}
    finishNavProgress();
    await Swal.fire({icon:'success',title:'Turned in!',text:isResubmitting?'Your submission has been updated.':'Your work has been submitted.',timer:2000,showConfirmButton:false});
    window.location.reload();
  }catch(e){resetNavProgress();btn.disabled=false;btn.innerHTML=`<i class="fas fa-paper-plane"></i> ${isResubmitting?'Resubmit':'Turn in'}`;Swal.fire({icon:'error',title:'Network error',text:'Could not submit. Please try again.'});}
}

async function loadComments(){
  try{const res=await fetch(API+'get_comments.php?post_id='+encodeURIComponent(POST_ID),{credentials:'same-origin'});const j=await res.json();if(j&&j.status==='success')renderComments(j.comments||[]);}catch(e){}
}
function renderComments(list){
  const el=document.getElementById('swCommentList');document.getElementById('swCommentEmpty').style.display=list.length?'none':'block';
  if(!list.length)return;
  el.innerHTML=list.map(c=>`<div class="sw-comment-item"><div class="sw-comment-avatar">${esc(initials(c.author_name||'?'))}</div><div class="sw-comment-bubble"><div class="sw-comment-meta"><span class="sw-comment-author">${esc(c.author_name||'User')}</span><span class="sw-comment-time">${fmtRelative(c.created_at)}</span></div><div class="sw-comment-text">${esc(c.comment_text||'')}</div></div></div>`).join('');
}
async function sendComment(){
  const box=document.getElementById('swCommentBox');const text=box.value.trim();if(!text)return;
  document.getElementById('swCommentSend').disabled=true;
  try{const res=await fetch(API+'save_comment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({post_id:POST_ID,comment_text:text}),credentials:'same-origin'});const j=await res.json();if(j&&j.status==='success'){box.value='';box.style.height='';await loadComments();}}catch(e){}
  document.getElementById('swCommentSend').disabled=!box.value.trim();
}

async function loadPrivateComments(){
  try{const res=await fetch(API+'get_private_comments.php?post_id='+encodeURIComponent(POST_ID),{credentials:'same-origin'});const j=await res.json();if(j&&j.status==='success')renderPrivateComments(j.comments||[]);}catch(e){}
}
function renderPrivateComments(list){
  const el=document.getElementById('swPrivateList');
  if(!list.length){el.innerHTML='<div style="font-size:.78rem;color:var(--text-muted);" id="swPrivateEmpty">No private comments yet.</div>';return;}
  el.innerHTML=list.map(c=>`<div class="sw-private-item"><div class="sw-private-avatar">${esc(initials(c.author_name||'?'))}</div><div class="sw-private-bubble"><div class="sw-private-meta"><span class="sw-private-author">${esc(c.author_name||'User')}</span><span>${fmtRelative(c.created_at)}</span></div><div class="sw-private-text">${esc(c.comment_text||'')}</div></div></div>`).join('');
}
async function sendPrivateComment(){
  const box=document.getElementById('swPrivateBox');const text=box.value.trim();if(!text)return;
  document.getElementById('swPrivateSend').disabled=true;
  try{
    const res=await fetch(API+'save_private_comment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({post_id:POST_ID,comment_text:text}),credentials:'same-origin'});
    const j=await res.json();
    if(j&&j.status==='success'){box.value='';await loadPrivateComments();}
    else{Swal.fire({icon:'error',title:'Could not send private comment',text:j?.message||'Please try again.'});}
  }catch(e){Swal.fire({icon:'error',title:'Could not send private comment',text:'Please check your connection and try again.'});}
  document.getElementById('swPrivateSend').disabled=!box.value.trim();
}

function lessonFiles(){
  return ((postData&&Array.isArray(postData.attachments))?postData.attachments:[])
    .filter(a=>!a.student_id&&a.attach_type==='file');
}
function openFirstLessonFile(){
  const file=lessonFiles()[0];
  if(!file){
    Swal.fire({icon:'info',title:'No file attached',text:'This lesson has no uploaded file yet.'});
    return;
  }
  openAttachmentViewer(file.file_path||'',file.file_name||'File',file.mime_type||'',file.id||'');
}
function viewerBusy(text='Loading preview'){
  swViewerBody.innerHTML=`<div class="sw-viewer-message"><i class="fas fa-spinner fa-spin"></i><h3>${esc(text)}</h3></div>`;
}
function resetViewerState(){
  viewerState={attachId:'',page:1,pages:1,scale:1.2,pdf:null,commentsEnabled:false,renderToken:(viewerState.renderToken||0)+1};
  swViewerPageControls.classList.remove('show');
  swViewerPageLabel.textContent='1 / 1';
  swViewerCommentPage.textContent='Comments unavailable';
  swViewerCommentList.innerHTML='<div class="sw-viewer-comment-empty">Open a page-based file to add page comments.</div>';
  swViewerCommentInput.value='';
  swViewerCommentInput.disabled=true;
  swViewerCommentSend.disabled=true;
}
function closeAttachmentViewer(){
  swFileViewer.classList.remove('show');
  swFileViewer.setAttribute('aria-hidden','true');
  document.body.style.overflow='';
  swViewerBody.innerHTML='';
  resetViewerState();
}
function showViewerFrame(src,title){
  swViewerPageControls.classList.remove('show');
  swViewerBody.innerHTML=`<iframe class="sw-viewer-frame" src="${esc(src)}" title="${esc(title||'File preview')}"></iframe>`;
}
function showViewerImage(src,label){
  swViewerPageControls.classList.remove('show');
  swViewerBody.innerHTML='';
  const img=document.createElement('img');
  img.className='sw-viewer-img';
  img.alt=label||'File preview';
  img.src=src;
  img.onerror=()=>showViewerFallback(src,label);
  swViewerBody.appendChild(img);
}
function showViewerMedia(src,type,mime){
  swViewerPageControls.classList.remove('show');
  swViewerBody.innerHTML='';
  const media=document.createElement(type);
  media.className='sw-viewer-media';
  media.controls=true;
  media.src=src;
  if(mime)media.type=mime;
  media.onerror=()=>showViewerFallback(src,type);
  swViewerBody.appendChild(media);
}
function showViewerText(src,label){
  swViewerPageControls.classList.remove('show');
  viewerBusy();
  fetch(src,{credentials:'same-origin'})
    .then(r=>{if(!r.ok)throw new Error('Preview unavailable');return r.text();})
    .then(text=>{
      const pre=document.createElement('pre');
      pre.className='sw-viewer-text';
      pre.textContent=text;
      swViewerBody.innerHTML='';
      swViewerBody.appendChild(pre);
    })
    .catch(()=>showViewerFallback(src,label));
}
function showViewerFallback(src,label){
  swViewerPageControls.classList.remove('show');
  swViewerBody.innerHTML=`<div class="sw-viewer-message">
    <i class="fas fa-file-arrow-down"></i>
    <h3>Preview not available</h3>
    <p>${esc(label||'This file')} cannot be previewed directly.</p>
    <div class="sw-viewer-actions">
      <a href="${esc(src||'#')}" target="_blank" rel="noopener"><i class="fas fa-up-right-from-square"></i> Open file</a>
      <a href="${esc(src||'#')}" download><i class="fas fa-download"></i> Download file</a>
    </div>
  </div>`;
}
function ensurePdfJs(){
  if(window.pdfjsLib)return Promise.resolve(window.pdfjsLib);
  return new Promise((resolve,reject)=>{
    const existing=document.querySelector('script[data-pdfjs]');
    if(existing){
      existing.addEventListener('load',()=>resolve(window.pdfjsLib),{once:true});
      existing.addEventListener('error',reject,{once:true});
      return;
    }
    const s=document.createElement('script');
    s.src='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
    s.dataset.pdfjs='1';
    s.onload=()=>resolve(window.pdfjsLib);
    s.onerror=reject;
    document.head.appendChild(s);
  });
}
async function showPdfDocument(src,label,fallbackSrc){
  viewerBusy();
  swViewerPageControls.classList.add('show');
  try{
    const pdfjs=await ensurePdfJs();
    pdfjs.GlobalWorkerOptions.workerSrc='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    viewerState.pdf=await pdfjs.getDocument(src).promise;
    viewerState.pages=viewerState.pdf.numPages||1;
    viewerState.page=1;
    viewerState.commentsEnabled=!!viewerState.attachId;
    await renderViewerPdfPage();
    await loadViewerComments();
  }catch(e){
    showViewerFallback(fallbackSrc||src,label);
  }
}
async function renderViewerPdfPage(){
  if(!viewerState.pdf)return;
  const token=++viewerState.renderToken;
  swViewerPageLabel.textContent=`${viewerState.page} / ${viewerState.pages}`;
  swViewerCommentPage.textContent=`Page ${viewerState.page}`;
  viewerBusy('Rendering page');
  const page=await viewerState.pdf.getPage(viewerState.page);
  if(token!==viewerState.renderToken)return;
  const viewport=page.getViewport({scale:viewerState.scale});
  const canvas=document.createElement('canvas');
  canvas.className='sw-viewer-canvas';
  canvas.width=viewport.width;
  canvas.height=viewport.height;
  swViewerBody.innerHTML='';
  swViewerBody.appendChild(canvas);
  await page.render({canvasContext:canvas.getContext('2d'),viewport}).promise;
  swViewerPageLabel.textContent=`${viewerState.page} / ${viewerState.pages}`;
}
async function goViewerPage(nextPage){
  if(!viewerState.pdf)return;
  viewerState.page=Math.max(1,Math.min(viewerState.pages,nextPage));
  await renderViewerPdfPage();
  await loadViewerComments();
}
async function loadViewerComments(){
  if(!viewerState.commentsEnabled){
    swViewerCommentList.innerHTML='<div class="sw-viewer-comment-empty">Comments are unavailable for this file.</div>';
    swViewerCommentInput.disabled=true;
    swViewerCommentSend.disabled=true;
    return;
  }
  swViewerCommentInput.disabled=false;
  swViewerCommentSend.disabled=!swViewerCommentInput.value.trim();
  swViewerCommentPage.textContent=`Page ${viewerState.page}`;
  swViewerCommentList.innerHTML='<div class="sw-viewer-comment-empty"><i class="fas fa-spinner fa-spin"></i></div>';
  try{
    const res=await fetch(`${API}get_annotations.php?attach_id=${encodeURIComponent(viewerState.attachId)}&page_number=${encodeURIComponent(viewerState.page)}`,{credentials:'same-origin'});
    const j=await res.json();
    const rows=j&&j.status==='success'?j.annotations||[]:[];
    renderViewerComments(rows);
  }catch(e){
    swViewerCommentList.innerHTML='<div class="sw-viewer-comment-empty">Could not load comments.</div>';
  }
}
function renderViewerComments(rows){
  if(!rows.length){
    swViewerCommentList.innerHTML='<div class="sw-viewer-comment-empty">No comments on this page yet.</div>';
    return;
  }
  swViewerCommentList.innerHTML=rows.map(r=>`
    <div class="sw-viewer-comment">
      <div class="sw-viewer-comment-meta">${esc(fmtRelative(r.created_at||''))}</div>
      <div class="sw-viewer-comment-text">${esc(r.note_text||'')}</div>
    </div>`).join('');
  swViewerCommentList.scrollTop=swViewerCommentList.scrollHeight;
}
async function saveViewerComment(){
  const text=swViewerCommentInput.value.trim();
  if(!text||!viewerState.attachId)return;
  swViewerCommentSend.disabled=true;
  try{
    const res=await fetch(`${API}save_annotation.php`,{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      credentials:'same-origin',
      body:JSON.stringify({attach_id:viewerState.attachId,note_text:text,page_number:viewerState.page})
    });
    const j=await res.json();
    if(!j||j.status!=='success')throw new Error(j?.message||'Could not save comment.');
    swViewerCommentInput.value='';
    await loadViewerComments();
  }catch(e){
    Swal.fire({icon:'error',title:'Could not save comment',text:e.message||'Please try again.'});
  }
  swViewerCommentSend.disabled=!swViewerCommentInput.value.trim();
}
function openAttachmentViewer(url,name,mime,attachId){
  const label=name||'File';
  const ext=fileExt(label)||fileExt(url);
  const mimeText=String(mime||'').toLowerCase();
  resetViewerState();
  viewerState.attachId=attachId||'';
  swViewerTitle.textContent=label;
  swViewerBadge.textContent=(ext||'file').toUpperCase();
  swViewerDownload.href=url||'#';
  swViewerDownload.setAttribute('download',label);
  viewerBusy();
  swFileViewer.classList.add('show');
  swFileViewer.setAttribute('aria-hidden','false');
  document.body.style.overflow='hidden';
  if(!url&&!attachId)showViewerFallback('#',label);
  else if(ext==='pdf'||mimeText.includes('pdf'))showPdfDocument(url,label,url);
  else if(['ppt','pptx'].includes(ext)&&attachId)showPdfDocument(`${API}get_attachment_preview.php?attach_id=${encodeURIComponent(attachId)}`,label,url);
  else if(['jpg','jpeg','png','gif','webp','svg','bmp','avif'].includes(ext)||mimeText.startsWith('image/'))showViewerImage(url,label);
  else if(['mp4','webm','ogg','mov'].includes(ext)||mimeText.startsWith('video/'))showViewerMedia(url,'video',mime);
  else if(['mp3','wav','aac','m4a','ogg'].includes(ext)||mimeText.startsWith('audio/'))showViewerMedia(url,'audio',mime);
  else if(['txt','csv','json','xml','css','js','md','sql','log'].includes(ext)||mimeText.startsWith('text/'))showViewerText(url,label);
  else showViewerFallback(url,label);
}

document.addEventListener('click',e=>{
  const trigger=e.target.closest('[data-file-trigger]');
  if(trigger)openAttachmentViewer(trigger.dataset.url||'',trigger.dataset.name||'',trigger.dataset.mime||'',trigger.dataset.attachId||'');
});
document.getElementById('swViewerClose').addEventListener('click',closeAttachmentViewer);
swFileViewer.addEventListener('click',e=>{if(e.target===swFileViewer)closeAttachmentViewer();});
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&swFileViewer.classList.contains('show'))closeAttachmentViewer();});
document.getElementById('swViewerPrev').addEventListener('click',()=>goViewerPage(viewerState.page-1));
document.getElementById('swViewerNext').addEventListener('click',()=>goViewerPage(viewerState.page+1));
document.getElementById('swViewerZoomOut').addEventListener('click',async()=>{viewerState.scale=Math.max(.5,viewerState.scale-.15);await renderViewerPdfPage();});
document.getElementById('swViewerZoomIn').addEventListener('click',async()=>{viewerState.scale=Math.min(3,viewerState.scale+.15);await renderViewerPdfPage();});
swViewerCommentInput.addEventListener('input',()=>{swViewerCommentSend.disabled=!swViewerCommentInput.value.trim()||!viewerState.attachId;autoResize(swViewerCommentInput);});
swViewerCommentInput.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();saveViewerComment();}});
swViewerCommentSend.addEventListener('click',saveViewerComment);

function toggleDark(){document.body.classList.toggle('dark');localStorage.setItem('darkMode',document.body.classList.contains('dark')?'1':'0');}
if(localStorage.getItem('darkMode')==='1')document.body.classList.add('dark');
init();
</script>
</body>
</html>
