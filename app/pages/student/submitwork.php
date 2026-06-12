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
<title>Submit Work — Tere LEARN</title>
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

/* ── PAGE GRID ── */
.sw-page{padding-top:calc(var(--nav-h) + 2rem);padding-bottom:3rem;max-width:1020px;margin:0 auto;padding-left:1.25rem;padding-right:1.25rem;display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;}

/* ── LEFT COLUMN ── */
.sw-post-header{display:flex;align-items:flex-start;gap:1rem;padding:1.5rem 0 1.25rem;border-bottom:1px solid var(--border);}
.sw-post-type-ico{width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.1rem;}
.sw-post-header-info{flex:1;min-width:0;}
.sw-post-title{font-size:1.45rem;font-weight:800;line-height:1.25;margin-bottom:.4rem;}
.sw-post-byline{font-size:.82rem;color:var(--text-muted);margin-bottom:.5rem;}
.sw-post-pts-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;font-size:.84rem;font-weight:600;color:var(--text-muted);}
.sw-pts-badge{display:inline-flex;align-items:center;gap:.35rem;font-size:.82rem;font-weight:700;color:var(--text);}
.sw-due-badge{display:inline-flex;align-items:center;gap:.35rem;font-size:.82rem;font-weight:600;color:var(--text-muted);}
.sw-due-badge.overdue{color:var(--danger);}

.sw-post-description{padding:1.25rem 0;border-bottom:1px solid var(--border);font-size:.92rem;line-height:1.75;color:var(--text);white-space:pre-wrap;}
.sw-post-description:empty{display:none;}

.sw-fac-attachments{padding:1rem 0;border-bottom:1px solid var(--border);}
.sw-fac-attach-item{display:flex;align-items:center;gap:.75rem;padding:.6rem .75rem;border-radius:10px;border:1px solid var(--border);background:var(--surface);margin-bottom:.45rem;text-decoration:none;color:var(--text);transition:all var(--trans);}
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

@media(max-width:700px){
  .sw-page{grid-template-columns:1fr;}
  .sw-right{position:static;}
  .sw-nav-crumb{display:none;}
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
        <span class="sw-yw-title">Your work</span>
        <span class="sw-yw-status not-sub" id="swYwStatus">Assigned</span>
      </div>
      <div class="sw-yw-body">

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
            <input type="file" id="swFileInput" multiple
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
          <i class="fas fa-rotate-left"></i> Unsubmit
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const POST_ID  = <?= json_encode($post_id) ?>;
const CLASS_ID = <?= json_encode($class_id) ?>;
const API      = 'API/student/studentClassroom/';

let postData=null,submissionData=null,pendingFiles=[],isResubmitting=false,myInitials='ST';

function esc(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function fmtDate(d){if(!d)return'';const dt=new Date(d.includes('T')?d:d.replace(' ','T'));if(isNaN(dt))return d;return dt.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});}
function fmtDateTime(d){if(!d)return'';const dt=new Date(d.includes('T')?d:d.replace(' ','T'));if(isNaN(dt))return d;return dt.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})+', '+dt.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'});}
function isOverdue(d){if(!d)return false;return new Date()>new Date(d.includes('T')?d:d.replace(' ','T'));}
function fileIcon(n){const e=(n||'').split('.').pop().toLowerCase();const m={pdf:'fa-file-pdf',doc:'fa-file-word',docx:'fa-file-word',ppt:'fa-file-powerpoint',pptx:'fa-file-powerpoint',xls:'fa-file-excel',xlsx:'fa-file-excel',jpg:'fa-file-image',jpeg:'fa-file-image',png:'fa-file-image',gif:'fa-file-image',webp:'fa-file-image',mp4:'fa-file-video',mp3:'fa-file-audio',wav:'fa-file-audio',zip:'fa-file-zipper',txt:'fa-file-lines'};return'fas '+(m[e]||'fa-file');}
function fileColor(n){const e=(n||'').split('.').pop().toLowerCase();const c={pdf:'#e53935',doc:'#1565c0',docx:'#1565c0',ppt:'#e65100',pptx:'#e65100',xls:'#2e7d32',xlsx:'#2e7d32',jpg:'#6a1b9a',jpeg:'#6a1b9a',png:'#6a1b9a',gif:'#6a1b9a',mp4:'#0277bd',mp3:'#ad1457',zip:'#4e342e'};return c[e]||'var(--text-muted)';}
function fileTypeName(n){const e=(n||'').split('.').pop().toUpperCase();const l={PDF:'PDF Document',DOC:'Word Document',DOCX:'Word Document',PPT:'PowerPoint',PPTX:'PowerPoint',XLS:'Excel Spreadsheet',XLSX:'Excel Spreadsheet',JPG:'JPEG Image',JPEG:'JPEG Image',PNG:'PNG Image',GIF:'GIF Image',MP4:'MP4 Video',MP3:'MP3 Audio',ZIP:'ZIP Archive',TXT:'Text File'};return l[e]||(e+' File');}
function fmtSize(b){if(!b)return'';if(b<1024)return b+'B';if(b<1048576)return(b/1024).toFixed(1)+' KB';return(b/1048576).toFixed(1)+' MB';}
function initials(n){const p=(n||'').trim().split(/\s+/);return((p[0]?.[0]||'')+(p[1]?.[0]||'')).toUpperCase()||'?';}
function autoResize(el){el.style.height='auto';el.style.height=el.scrollHeight+'px';}

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
  const typeKey=(p.post_type||'').toLowerCase();
  const typeLabel=(p.sub_label||p.post_type||'Post');
  const isAssignment=typeKey==='assignment'||typeLabel.toLowerCase().includes('assignment');
  const isActivity=typeKey==='activity'||typeLabel.toLowerCase().includes('activity');
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
  else{ico.style.cssText='background:var(--primary-light);color:var(--primary);width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;';ico.innerHTML='<i class="fas fa-tasks"></i>';}

  document.getElementById('swPostTitle').textContent=p.title||'—';
  document.getElementById('swPostByline').textContent=(p.author_name||'Faculty')+' · '+fmtDate(p.created_at);
  if(p.points){document.getElementById('swPtsBadge').style.display='';document.getElementById('swPtsVal').textContent=p.points;}
  if(due){document.getElementById('swDueBadge').style.display='';document.getElementById('swDueVal').textContent=fmtDateTime(due);if(overdue)document.getElementById('swDueBadge').classList.add('overdue');}
  if(p.body)document.getElementById('swPostDesc').textContent=p.body;

  const atts=(p.attachments||[]).filter(a=>!a.student_id);
  if(atts.length){
    const w=document.getElementById('swFacAttach');w.style.display='block';
    w.innerHTML=atts.map(a=>{
      if(a.attach_type==='file')return`<a class="sw-fac-attach-item" href="${esc(a.file_path||'')}" target="_blank"><div class="sw-fac-attach-ico" style="color:${fileColor(a.file_name||'')}"><i class="${fileIcon(a.file_name||'')}"></i></div><div class="sw-fac-attach-info"><div class="sw-fac-attach-name">${esc(a.file_name||'File')}</div><div class="sw-fac-attach-sub">${fileTypeName(a.file_name||'')} · ${fmtSize(a.file_size)}</div></div><i class="fas fa-arrow-up-right-from-square" style="font-size:.7rem;color:var(--text-muted);flex-shrink:0;"></i></a>`;
      const isYT=a.attach_type==='youtube';
      return`<a class="sw-fac-attach-item" href="${esc(a.url||'')}" target="_blank"><div class="sw-fac-attach-ico" style="color:${isYT?'#d93025':'var(--accent)'}"><i class="${isYT?'fab fa-youtube':'fas fa-link'}"></i></div><div class="sw-fac-attach-info"><div class="sw-fac-attach-name">${esc(a.url||'Link')}</div><div class="sw-fac-attach-sub">${isYT?'YouTube Video':'External Link'}</div></div><i class="fas fa-arrow-up-right-from-square" style="font-size:.7rem;color:var(--text-muted);flex-shrink:0;"></i></a>`;
    }).join('');
  }

  document.getElementById('swSelfAvatar').textContent=myInitials;
  document.getElementById('swPrivateAvatar').textContent=myInitials;

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

  // Submitted files
  if(hasSub&&sub.files&&sub.files.length){
    document.getElementById('swSubFiles').innerHTML=sub.files.map(f=>`<a class="sw-sub-file-item" href="${esc(f.file_path||'')}" target="_blank"><div class="sw-sub-file-thumb" style="color:${fileColor(f.file_name||'')}"><i class="${fileIcon(f.file_name||'')}"></i></div><div class="sw-sub-file-info"><div class="sw-sub-file-name">${esc(f.file_name||'File')}</div><div class="sw-sub-file-type">${fileTypeName(f.file_name||'')}</div></div><i class="fas fa-download" style="font-size:.72rem;color:var(--text-muted);flex-shrink:0;"></i></a>`).join('');
  }
  if(hasSub&&sub.response_text){document.getElementById('swTextInput').value=sub.response_text;document.getElementById('swTextAreaWrap').classList.add('show');}

  if(hasSub){
    lockForm(true);
    document.getElementById('swSubmitBtn').style.display='none';
    document.getElementById('swUnsubmitBtn').style.display='flex';
  }else if(isClosed){
    lockForm(true);
    document.getElementById('swSubmitBtn').disabled=true;
    document.getElementById('swSubmitBtn').innerHTML='<i class="fas fa-lock"></i> Submission closed';
  }else{
    document.getElementById('swSubmitLabel').textContent=isAssignment?'Turn in':'Submit';
  }
}

function handleFiles(fl){Array.from(fl).forEach(f=>{if(!pendingFiles.find(x=>x.name===f.name&&x.size===f.size))pendingFiles.push(f);});renderNewFiles();}
function renderNewFiles(){
  document.getElementById('swNewFiles').innerHTML=pendingFiles.map((f,i)=>`<div class="sw-sub-file-item"><div class="sw-sub-file-thumb" style="color:${fileColor(f.name)}"><i class="${fileIcon(f.name)}"></i></div><div class="sw-sub-file-info"><div class="sw-sub-file-name">${esc(f.name)}</div><div class="sw-sub-file-type">${fileTypeName(f.name)} · ${fmtSize(f.size)}</div></div><button class="sw-sub-file-del" onclick="removeFile(${i})"><i class="fas fa-xmark"></i></button></div>`).join('');
}
function removeFile(i){pendingFiles.splice(i,1);renderNewFiles();}
function toggleTextArea(){const w=document.getElementById('swTextAreaWrap');w.classList.toggle('show');if(w.classList.contains('show'))document.getElementById('swTextInput').focus();}
function lockForm(l){document.getElementById('swAddFileWrap').style.display=l?'none':'block';document.getElementById('swTextInput').disabled=l;}
function enableResubmit(){isResubmitting=true;lockForm(false);document.getElementById('swUnsubmitBtn').style.display='none';document.getElementById('swSubmitBtn').style.display='flex';document.getElementById('swSubmitLabel').textContent='Resubmit';}

async function submitWork(){
  const isAssign=(postData.post_type||'').toLowerCase()==='assignment'||(postData.sub_label||'').toLowerCase().includes('assignment');
  const text=document.getElementById('swTextInput').value.trim();
  if(isAssign&&!pendingFiles.length&&!text&&!submissionData){Swal.fire({icon:'warning',title:'Nothing to turn in',text:'Please attach a file or add a note before submitting.'});return;}
  const ok=await Swal.fire({title:'Turn in?',html:`<div style="font-size:.86rem;line-height:1.7;text-align:left;">${pendingFiles.length?`<div><i class="fas fa-paperclip" style="color:#1a9e78;margin-right:.4rem;"></i>${pendingFiles.length} file${pendingFiles.length!==1?'s':''} attached</div>`:''} ${text?'<div><i class="fas fa-pen" style="color:#1f73db;margin-right:.4rem;"></i>Note included</div>':''}<div style="margin-top:.5rem;font-size:.78rem;color:#5f6368;">Your teacher will be notified. You can unsubmit to make changes.</div></div>`,icon:'question',showCancelButton:true,confirmButtonText:'<i class="fas fa-paper-plane"></i> Turn in',cancelButtonText:'Cancel',confirmButtonColor:'#1a9e78',reverseButtons:true});
  if(!ok.isConfirmed)return;
  const btn=document.getElementById('swSubmitBtn');
  btn.disabled=true;btn.innerHTML='<span style="width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:sw-spin .7s linear infinite;display:inline-block;"></span> Submitting…';
  try{
    const fd=new FormData();fd.append('post_id',POST_ID);fd.append('class_id',CLASS_ID);fd.append('response_text',text);
    pendingFiles.forEach(f=>fd.append('files[]',f));
    const res=await fetch(API+'submit_work.php',{method:'POST',body:fd,credentials:'same-origin'});
    const j=await res.json();
    if(!j||j.status!=='success'){btn.disabled=false;btn.innerHTML='<i class="fas fa-paper-plane"></i> <span id="swSubmitLabel">Turn in</span>';Swal.fire({icon:'error',title:'Failed',text:j?.message||'Please try again.'});return;}
    await Swal.fire({icon:'success',title:'Turned in!',text:isResubmitting?'Your submission has been updated.':'Your work has been submitted.',timer:2000,showConfirmButton:false});
    window.location.reload();
  }catch(e){btn.disabled=false;btn.innerHTML='<i class="fas fa-paper-plane"></i> Turn in';Swal.fire({icon:'error',title:'Network error',text:'Could not submit. Please try again.'});}
}

async function loadComments(){
  try{const res=await fetch(API+'get_comments.php?post_id='+encodeURIComponent(POST_ID),{credentials:'same-origin'});const j=await res.json();if(j&&j.status==='success')renderComments(j.comments||[]);}catch(e){}
}
function renderComments(list){
  const el=document.getElementById('swCommentList');document.getElementById('swCommentEmpty').style.display=list.length?'none':'block';
  if(!list.length)return;
  el.innerHTML=list.map(c=>`<div class="sw-comment-item"><div class="sw-comment-avatar">${esc(initials(c.author_name||'?'))}</div><div class="sw-comment-bubble"><div class="sw-comment-meta"><span class="sw-comment-author">${esc(c.author_name||'User')}</span><span class="sw-comment-time">${fmtDate(c.created_at)}</span></div><div class="sw-comment-text">${esc(c.comment_text||'')}</div></div></div>`).join('');
}
async function sendComment(){
  const box=document.getElementById('swCommentBox');const text=box.value.trim();if(!text)return;
  document.getElementById('swCommentSend').disabled=true;
  try{const fd=new FormData();fd.append('post_id',POST_ID);fd.append('comment_text',text);const res=await fetch(API+'save_comment.php',{method:'POST',body:fd,credentials:'same-origin'});const j=await res.json();if(j&&j.status==='success'){box.value='';box.style.height='';await loadComments();}}catch(e){}
  document.getElementById('swCommentSend').disabled=!box.value.trim();
}

async function loadPrivateComments(){
  try{const res=await fetch(API+'get_private_comments.php?post_id='+encodeURIComponent(POST_ID),{credentials:'same-origin'});const j=await res.json();if(j&&j.status==='success')renderPrivateComments(j.comments||[]);}catch(e){}
}
function renderPrivateComments(list){
  const el=document.getElementById('swPrivateList');document.getElementById('swPrivateEmpty').style.display=list.length?'none':'block';
  if(!list.length)return;
  el.innerHTML=list.map(c=>`<div class="sw-private-item"><div class="sw-private-avatar">${esc(initials(c.author_name||'?'))}</div><div class="sw-private-bubble"><div class="sw-private-meta"><span class="sw-private-author">${esc(c.author_name||'User')}</span><span>${fmtDate(c.created_at)}</span></div><div class="sw-private-text">${esc(c.comment_text||'')}</div></div></div>`).join('');
}
async function sendPrivateComment(){
  const box=document.getElementById('swPrivateBox');const text=box.value.trim();if(!text)return;
  document.getElementById('swPrivateSend').disabled=true;
  try{const fd=new FormData();fd.append('post_id',POST_ID);fd.append('comment_text',text);fd.append('is_private','1');const res=await fetch(API+'save_comment.php',{method:'POST',body:fd,credentials:'same-origin'});const j=await res.json();if(j&&j.status==='success'){box.value='';await loadPrivateComments();}}catch(e){}
  document.getElementById('swPrivateSend').disabled=!box.value.trim();
}

function toggleDark(){document.body.classList.toggle('dark');localStorage.setItem('darkMode',document.body.classList.contains('dark')?'1':'0');}
if(localStorage.getItem('darkMode')==='1')document.body.classList.add('dark');
init();
</script>
</body>
</html>
