<?php
// post_manage.php - Faculty Post Management Page
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 2) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php');
    exit;
}
$post_id  = trim($_GET['post_id'] ?? '');
$class_id = trim($_GET['class_id'] ?? '');
if ($post_id === '' || $class_id === '') {
    header('Location: ' . TERELEARN_BASE_URL . 'facultyUI.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tere Learn — Post Manager</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #1a9e78; --primary-dark: #0d7a5e; --primary-light: #e6f7f2;
      --primary-mid: rgba(26,158,120,.15);
      --accent: #1f73db; --accent-light: #e8f0fe;
      --danger: #d93025; --warning: #f57c00;
      --border: #e8eaed; --text: #1c2027; --text-muted: #5f6368;
      --bg: #f4f6f9; --surface: #ffffff;
      --nav-h: 60px; --radius: 14px; --radius-sm: 8px;
      --shadow: 0 2px 12px rgba(0,0,0,.07);
      --shadow-md: 0 4px 20px rgba(0,0,0,.10);
      --shadow-lg: 0 10px 40px rgba(0,0,0,.14);
      --trans: .22s cubic-bezier(.4,0,.2,1);
    }
    body.dark {
      --primary: #2ecc9a; --primary-dark: #1a9e78; --primary-light: rgba(46,204,154,.12);
      --primary-mid: rgba(46,204,154,.10);
      --accent: #4d90e2; --accent-light: rgba(77,144,226,.14);
      --border: #2e3849; --text: #e4ecf7; --text-muted: #8a9ab5;
      --bg: #0f1724; --surface: #182030;
    }
    *,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); transition: background var(--trans), color var(--trans); }
    ::-webkit-scrollbar { width: 5px; } ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
    .swal2-container { z-index: 99999 !important; }

    /* ── TOPBAR ── */
    .topbar { position: fixed; inset: 0 0 auto 0; height: var(--nav-h); background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: .75rem; padding: 0 1.4rem; z-index: 200; box-shadow: var(--shadow); }
    .back-btn { width: 36px; height: 36px; border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: 1rem; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all var(--trans); text-decoration: none; flex-shrink: 0; }
    .back-btn:hover { background: var(--border); color: var(--text); }
    .topbar-brand { display: flex; align-items: center; gap: .55rem; font-size: .95rem; font-weight: 700; color: var(--text); text-decoration: none; white-space: nowrap; }
    .blogo { width: 32px; height: 32px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 9px; color: #fff; font-size: .82rem; display: flex; align-items: center; justify-content: center; }
    .topbar-sep { color: var(--border); font-size: 1.1rem; margin: 0 .1rem; }
    .topbar-title { font-size: .9rem; font-weight: 600; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 380px; }
    .topbar-right { margin-left: auto; display: flex; align-items: center; gap: .5rem; }
    .icon-btn { width: 36px; height: 36px; border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: 1rem; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all var(--trans); }
    .icon-btn:hover { background: var(--border); color: var(--text); }

    /* ── HERO ── */
    .pm-hero { margin-top: var(--nav-h); background: linear-gradient(135deg, #1a9e78 0%, #0d47a1 100%); padding: 1.05rem 1.4rem .95rem; position: relative; overflow: hidden; }
    .pm-hero::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,.18); }
    .pm-hero-inner { position: relative; z-index: 1; max-width: 1100px; margin: 0 auto; display: flex; align-items: flex-start; gap: 1.25rem; flex-wrap: wrap; }
    .pm-hero-icon { width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,.18); border: 1.5px solid rgba(255,255,255,.3); color: #fff; font-size: 1.3rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .pm-hero-text { flex: 1; min-width: 0; }
    .pm-hero-eyebrow { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: rgba(255,255,255,.65); margin-bottom: .35rem; }
    .pm-hero-title { font-size: 1.28rem; font-weight: 700; color: #fff; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pm-hero-sub { font-size: .8rem; color: rgba(255,255,255,.8); margin-top: .15rem; }
    .pm-hero-chips { display: flex; flex-wrap: wrap; gap: .45rem; margin-top: .75rem; }
    .pm-hero-chip { font-size: .72rem; font-weight: 600; padding: .22rem .65rem; border-radius: 20px; background: rgba(255,255,255,.18); color: #fff; display: flex; align-items: center; gap: .3rem; border: 1px solid rgba(255,255,255,.2); }
    .pm-action-row{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.55rem}
    .pm-act{border:1.5px solid rgba(255,255,255,.25);background:rgba(255,255,255,.16);color:#fff;padding:.35rem .7rem;border-radius:999px;font-size:.72rem;font-weight:700;cursor:pointer}
    .pm-act:hover{background:rgba(255,255,255,.28)}
    .pm-status-badge { font-size: .7rem; font-weight: 700; padding: .22rem .7rem; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; display: inline-flex; align-items: center; gap: .35rem; }
    .pm-status-badge.is-gradable { background: #d1fae5; color: #065f46; }
    .pm-status-badge.is-quiz { background: #fdecea; color: #d93025; }
    .pm-status-badge.is-none { background: rgba(255,255,255,.15); color: rgba(255,255,255,.9); }

    /* ── TABS ── */
    .tabs-bar { background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 1.5rem; position: sticky; top: var(--nav-h); z-index: 100; overflow-x: auto; }
    .tab-btn { padding: 1rem 1.25rem; border: none; background: none; font-size: .88rem; font-weight: 600; color: var(--text-muted); cursor: pointer; border-bottom: 3px solid transparent; transition: all var(--trans); white-space: nowrap; display: inline-flex; align-items: center; gap: .45rem; }
    .tab-btn:hover, .tab-btn.active { color: var(--primary); }
    .tab-btn.active { border-bottom-color: var(--primary); }
    .tab-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 20px; background: var(--primary); color: #fff; font-size: .65rem; font-weight: 700; }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; }

    /* ── PAGE BODY ── */
    .pm-page { max-width: 1180px; margin: 0 auto; padding: .7rem .85rem; }

    /* ── LOADING / EMPTY ── */
    .pm-loading { display: flex; align-items: center; justify-content: center; gap: .55rem; padding: 3rem 1rem; color: var(--text-muted); font-size: .88rem; }
    .pm-loading i { color: var(--primary); }
    .pm-empty {
      text-align: center;
      padding: 2.4rem 1rem 2.8rem;
      color: var(--text-muted);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: .35rem;
    }
    .pm-empty i {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      opacity: .55;
      color: #a4adb8;
      background: #eef2f7;
      margin-bottom: .15rem;
    }
    .pm-empty h4 { font-size: 1.12rem; font-weight: 800; color: var(--text); margin: 0; }
    .pm-empty p { font-size: .92rem; margin: 0; max-width: 620px; line-height: 1.45; }

    /* ── OVERVIEW STATS ── */
    .pm-stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .55rem; margin-bottom: .7rem; }
    .pm-stat-card { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius); padding: .85rem .95rem; display: flex; flex-direction: column; gap: .2rem; box-shadow: var(--shadow); transition: box-shadow var(--trans), border-color var(--trans); }
    .pm-stat-card:hover { box-shadow: var(--shadow-md); }
    .pm-stat-card-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .9rem; margin-bottom: .35rem; }
    .pm-stat-card-icon--green { background: var(--primary-light); color: var(--primary-dark); }
    .pm-stat-card-icon--blue { background: var(--accent-light); color: var(--accent); }
    .pm-stat-card-icon--amber { background: #fff8e8; color: #92400e; }
    .pm-stat-card-icon--gray { background: var(--bg); color: var(--text-muted); }
    .pm-stat-value { font-size: 1.85rem; font-weight: 800; color: var(--text); font-family: 'DM Mono', monospace; line-height: 1; }
    .pm-stat-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--text-muted); }

    /* ── INFO SECTION ── */
    .pm-section { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; margin-bottom: .85rem; }
    .pm-section-head { display: flex; align-items: center; gap: .6rem; padding: .7rem .95rem; border-bottom: 1px solid var(--border); background: var(--bg); }
    .pm-section-head-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: var(--text-muted); }
    .pm-section-body { padding: .25rem 0; }
    .pm-info-row { display: flex; justify-content: space-between; align-items: center; padding: .5rem .95rem; border-bottom: 1px solid var(--border); font-size: .82rem; }
    .pm-info-row:last-child { border-bottom: none; }
    .pm-info-row span { color: var(--text-muted); display: flex; align-items: center; gap: .4rem; }
    .pm-info-row strong { font-weight: 600; color: var(--text); font-family: 'DM Mono', monospace; font-size: .82rem; }

    /* ── LESSON MATERIALS ── */
    .pm-lesson-intro { display: flex; align-items: flex-start; gap: .8rem; padding: .95rem 1rem; margin-bottom: .8rem; border: 1px solid rgba(26,158,120,.22); border-radius: var(--radius); background: var(--primary-light); color: var(--primary-dark); }
    .pm-lesson-intro i { margin-top: .18rem; }
    .pm-lesson-intro h3 { font-size: .92rem; margin-bottom: .12rem; }
    .pm-lesson-intro p { font-size: .8rem; line-height: 1.45; color: var(--text-muted); }
    .pm-lesson-grid { display: grid; grid-template-columns: minmax(0,1fr) minmax(260px,.58fr); gap: .8rem; align-items: start; }
    .pm-lesson-stack { display: flex; flex-direction: column; gap: .8rem; }
    .pm-resource-copy { padding: 1rem; color: var(--text); font-size: .88rem; line-height: 1.65; white-space: pre-wrap; word-break: break-word; }
    .pm-resource-list { padding: .7rem; display: grid; gap: .55rem; }
    .pm-resource-card { width: 100%; display: flex; align-items: center; gap: .72rem; padding: .72rem; border: 1px solid var(--border); border-radius: 11px; color: var(--text); background: var(--surface); text-align: left; font-family: inherit; cursor: pointer; text-decoration: none; transition: border-color var(--trans), background var(--trans), transform var(--trans); }
    .pm-resource-card:hover { border-color: var(--primary); background: var(--primary-light); transform: translateY(-1px); }
    .pm-resource-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: var(--primary-light); color: var(--primary-dark); }
    .pm-resource-icon.is-youtube { background: #fff0f0; color: #e62117; }
    .pm-resource-meta { flex: 1; min-width: 0; }
    .pm-resource-title { color: var(--text); font-size: .84rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pm-resource-sub { color: var(--text-muted); font-size: .72rem; margin-top: .15rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pm-resource-open { color: var(--primary); font-size: .78rem; }
    .pm-reader-backdrop { display: none; position: fixed; inset: 0; z-index: 5000; padding: 0; background: rgba(15,23,42,.34); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); align-items: center; justify-content: center; }
    .pm-reader-backdrop.show { display: flex; }
    .pm-reader-shell { width: calc(100% - 2rem); height: calc(100vh - 2rem); display: flex; flex-direction: column; overflow: hidden; border-radius: 14px; border: 1px solid rgba(255,255,255,.18); background: var(--surface); box-shadow: var(--shadow-lg); }
    .pm-reader-toolbar { display: flex; align-items: center; gap: .55rem; padding: .62rem .8rem; border-bottom: 1px solid var(--border); background: var(--surface); }
    .pm-reader-title { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text); font-size: .84rem; font-weight: 700; }
    .pm-reader-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid var(--border); border-radius: 8px; color: var(--text-muted); background: var(--bg); text-decoration: none; cursor: pointer; }
    .pm-reader-btn:hover { color: var(--primary); border-color: var(--primary); }
    .pm-reader-btn:disabled { opacity: .35; cursor: not-allowed; }
    .pm-reader-body { flex: 1; min-height: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 1rem; background: rgba(15,23,42,.92); }
    .pm-reader-frame { width: 100%; height: 100%; border: 0; background: #fff; }
    .pm-reader-canvas-wrap { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; overflow: auto; }
    .pm-reader-canvas { display: block; max-width: 100%; max-height: 100%; width: auto; height: auto; background: #fff; box-shadow: 0 12px 34px rgba(0,0,0,.32); }
    .pm-reader-page-controls { display: none; align-items: center; gap: .35rem; flex-shrink: 0; }
    .pm-reader-page-controls.show { display: flex; }
    .pm-reader-page-label { min-width: 66px; color: var(--text-muted); text-align: center; font-size: .76rem; font-weight: 700; }
    .pm-reader-image { max-width: 100%; max-height: 100%; object-fit: contain; }
    .pm-reader-media { max-width: 100%; max-height: 100%; }
    .pm-reader-text { width: 100%; height: 100%; overflow: auto; padding: 1rem; color: var(--text); background: var(--surface); white-space: pre-wrap; word-break: break-word; font-family: 'DM Mono', monospace; font-size: .8rem; line-height: 1.65; }
    .pm-reader-empty { max-width: 520px; padding: 1.4rem; border-radius: 14px; color: #d9e2ef; text-align: center; background: rgba(255,255,255,.08); }
    .pm-reader-empty i { margin-bottom: .75rem; font-size: 2.6rem; opacity: .8; }
    .pm-reader-empty h3 { margin-bottom: .35rem; font-size: 1rem; }
    .pm-reader-empty p { margin-bottom: 1rem; color: #aab8ca; font-size: .82rem; line-height: 1.5; }
    .pm-reader-empty a { display: inline-flex; align-items: center; gap: .38rem; margin: .25rem; padding: .52rem .85rem; border-radius: 8px; color: #fff; background: var(--primary); text-decoration: none; font-size: .8rem; font-weight: 700; }
    @media(max-width:760px) { .pm-lesson-grid { grid-template-columns: 1fr; } }

    /* ── QUESTIONS TAB ── */
    .pm-q-list { display: flex; flex-direction: column; gap: .6rem; }
    .pm-q-card { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius); padding: .8rem .9rem; box-shadow: var(--shadow); display: flex; gap: .8rem; transition: box-shadow var(--trans); }
    .pm-q-card { cursor: pointer; }
    .pm-q-card:hover { box-shadow: var(--shadow-md); border-color: rgba(26,158,120,.3); }
    .pm-q-num { width: 32px; height: 32px; border-radius: 9px; background: var(--primary-light); color: var(--primary-dark); font-size: .78rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-family: 'DM Mono', monospace; }
    .pm-q-body { flex: 1; min-width: 0; }
    .pm-q-text { font-size: .88rem; color: var(--text); line-height: 1.5; }
    .pm-q-meta { display: flex; align-items: center; gap: .5rem; margin-top: .45rem; flex-wrap: wrap; }
    .pm-q-drop {
      margin-top: .7rem;
      border-top: 1px dashed var(--border);
      padding-top: .7rem;
      max-height: 0;
      opacity: 0;
      overflow: hidden;
      transform: translateY(-4px);
      transition: max-height .28s ease, opacity .22s ease, transform .22s ease;
      will-change: max-height, opacity, transform;
    }
    .pm-q-drop.show {
      opacity: 1;
      transform: translateY(0);
    }
    .pm-q-choice { display: flex; align-items: flex-start; gap: .45rem; font-size: .82rem; padding: .35rem .45rem; border-radius: 8px; }
    .pm-q-choice + .pm-q-choice { margin-top: .25rem; }
    .pm-q-choice.is-correct { background: var(--primary-light); color: var(--primary-dark); border: 1px solid rgba(26,158,120,.25); }
    .pm-q-answer { margin-top: .55rem; font-size: .78rem; color: var(--text-muted); }
    .pm-q-chevron { margin-left: auto; color: var(--text-muted); font-size: .85rem; }
    .pm-q-topline { display:flex; align-items:flex-start; gap:.5rem; width:100%; }
    .pm-q-topline-right { margin-left:auto; display:inline-flex; align-items:center; gap:.4rem; flex-shrink:0; }
    .pm-q-sidebox { flex-shrink:0; min-width:44px; height:30px; border-radius:10px; border:1px solid rgba(37,99,235,.28); background:#e7f0ff; color:#174ea6; box-shadow: inset 0 0 0 1px rgba(255,255,255,.45); display:inline-flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:800; padding:0 .45rem; }
    .pm-q-pts { font-size: .72rem; font-weight: 700; padding: .18rem .55rem; border-radius: 20px; background: var(--accent-light); color: var(--accent); display: inline-flex; align-items: center; gap: .3rem; }
    .pm-q-time { font-size: .72rem; font-weight: 700; padding: .18rem .55rem; border-radius: 20px; background: #eef6ff; color: #2563eb; display: inline-flex; align-items: center; gap: .3rem; border: 1px solid rgba(37,99,235,.18); }
    .pm-q-type { font-size: .72rem; font-weight: 700; padding: .18rem .55rem; border-radius: 20px; background: var(--bg); color: var(--text-muted); border: 1px solid var(--border); display: inline-flex; align-items: center; gap: .3rem; }
    .pm-q-bloom { font-size: .72rem; font-weight: 800; padding: .18rem .6rem; border-radius: 20px; border: 1px solid transparent; display: inline-flex; align-items: center; gap: .3rem; }
    .pm-q-bloom.is-remembering { background: #e8f7f0; color: #0f8b68; border-color: rgba(15,139,104,.22); }
    .pm-q-bloom.is-understanding { background: #e8f0fe; color: #1f73db; border-color: rgba(31,115,219,.24); }
    .pm-q-bloom.is-applying { background: #eef7e8; color: #3f8f2b; border-color: rgba(63,143,43,.24); }
    .pm-q-bloom.is-analyzing { background: #fff4e5; color: #b26a00; border-color: rgba(178,106,0,.24); }
    .pm-q-bloom.is-evaluating { background: #fdecec; color: #c53929; border-color: rgba(197,57,41,.24); }
    .pm-q-bloom.is-creating { background: #f3e8ff; color: #8b46c7; border-color: rgba(139,70,199,.24); }
    .pm-q-bloom.is-all { background: #eef2f7; color: #4b5563; border-color: rgba(75,85,99,.2); }
    .pm-bloom-summary { display:flex; flex-wrap:wrap; gap:.45rem; margin-bottom:.75rem; }
    .pm-bloom-chip-btn { cursor: pointer; user-select: none; transition: transform var(--trans), box-shadow var(--trans), opacity var(--trans); }
    .pm-bloom-chip-btn:hover { transform: translateY(-1px); box-shadow: var(--shadow); }
    .pm-bloom-chip-btn.is-active { box-shadow: 0 0 0 2px rgba(26,158,120,.18); }
    .pm-bloom-chip-btn.is-dim { opacity: .55; }

    /* ── SUBMISSIONS TAB ── */
    .pm-sub-toolbar { display: flex; align-items: center; gap: .55rem; margin-bottom: .7rem; flex-wrap: wrap; }
    .pm-ctrl-wrap { margin-bottom: .65rem; font-family: inherit; }
    .pm-ctrl-bar { background: var(--surface); border: .5px solid var(--border); border-radius: 12px; padding: .75rem 1rem; display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; box-shadow: var(--shadow); }
    .pm-ctrl-state-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 10px; font-size: 12px; font-weight: 600; white-space: nowrap; border: .5px solid; }
    .pm-ctrl-state-draft { background: #f1efe8; color: #2c2c2a; border-color: #d3d1c7; }
    .pm-ctrl-state-published { background: #eaf3de; color: #27500a; border-color: #c0dd97; }
    .pm-ctrl-state-live { background: #fcebeb; color: #501313; border-color: #f7c1c1; }
    .pm-ctrl-state-ended { background: #faeeda; color: #412402; border-color: #fac775; }
    .pm-ctrl-state-released { background: #e6f1fb; color: #042c53; border-color: #b5d4f4; }
    .pm-ctrl-live-dot { width: 7px; height: 7px; border-radius: 50%; background: #e24b4a; animation: pmCtrlPulse 1.2s ease-in-out infinite; flex-shrink: 0; }
    @keyframes pmCtrlPulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .5; transform: scale(.7); } }
    .pm-ctrl-divider { width: .5px; height: 28px; background: var(--border); flex-shrink: 0; }
    .pm-ctrl-btn { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 10px; font-size: 13px; font-weight: 600; border: .5px solid var(--border); background: var(--surface); color: var(--text); cursor: pointer; transition: background .15s, opacity .15s, color .15s, border-color .15s; white-space: nowrap; }
    .pm-ctrl-btn:hover:not(:disabled) { background: var(--bg); }
    .pm-ctrl-btn:disabled { opacity: .35; cursor: not-allowed; }
    .pm-ctrl-btn.primary { background: var(--primary); border-color: var(--primary-dark); color: #fff; }
    .pm-ctrl-btn.primary:hover:not(:disabled) { background: var(--primary-dark); }
    .pm-ctrl-btn.danger { background: #fcebeb; border-color: #f09595; color: #a32d2d; }
    .pm-ctrl-btn.danger:hover:not(:disabled) { background: #f7c1c1; }
    .pm-ctrl-btn.ghost { border: none; background: transparent; color: var(--text-muted); font-size: 12px; padding: 4px 8px; }
    .pm-ctrl-btn.ghost:hover:not(:disabled) { background: var(--bg); color: var(--text); }
    .pm-ctrl-btn i { font-size: 13px; }
    .pm-ctrl-spacer { flex: 1; }
    .pm-ctrl-confirm { background: var(--bg); border: .5px solid var(--border); border-radius: 10px; padding: .55rem .85rem; display: none; align-items: center; gap: .65rem; margin-top: .5rem; flex-wrap: wrap; }
    .pm-ctrl-confirm.show { display: flex; }
    .pm-ctrl-confirm p { font-size: 12px; color: var(--text-muted); flex: 1; min-width: 160px; }
    .pm-ctrl-confirm p strong { color: var(--text); font-weight: 700; }
    .pm-ctrl-hint { font-size: 12px; color: var(--text-muted); margin-top: .55rem; padding: 0 .25rem; }
    @media(max-width:760px){
      .pm-ctrl-bar { gap: .55rem; padding: .65rem .75rem; }
      .pm-ctrl-divider { display: none; }
      .pm-ctrl-btn { padding: 5px 10px; font-size: 12px; }
    }
    .pm-sub-search-wrap { flex: 1; min-width: 180px; position: relative; display: flex; align-items: center; }
    .pm-sub-search-wrap i { position: absolute; left: .75rem; color: var(--text-muted); font-size: .82rem; pointer-events: none; }
    .pm-sub-search { width: 100%; padding: .55rem .85rem .55rem 2.1rem; border: 1.5px solid var(--border); border-radius: 10px; background: var(--surface); font-family: inherit; font-size: .84rem; color: var(--text); outline: none; transition: border-color .15s; }
    .pm-sub-search:focus { border-color: var(--primary); }
    .pm-sort-wrap { position: relative; }
    .pm-sort-btn { display: inline-flex; align-items: center; gap: .35rem; padding: .5rem .75rem; border-radius: 10px; border: 1.5px solid var(--border); background: var(--surface); font-family: inherit; font-size: .8rem; font-weight: 700; color: var(--text-muted); cursor: pointer; transition: all .18s; }
    .pm-sort-btn:hover { border-color: var(--primary); color: var(--primary); }
    .pm-sort-menu { display: none; position: absolute; top: calc(100% + 6px); right: 0; min-width: 230px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-md); padding: .35rem; z-index: 120; }
    .pm-sort-menu.show { display: block; }
    .pm-sort-item { width: 100%; text-align: left; border: none; background: transparent; color: var(--text); font-family: inherit; font-size: .8rem; padding: .45rem .55rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: background .15s, color .15s; }
    .pm-sort-item:hover { background: var(--primary-light); color: var(--primary-dark); }
    .pm-sort-item.active { background: var(--primary-light); color: var(--primary-dark); font-weight: 700; }
    .pm-refresh-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .9rem; border-radius: 10px; border: 1.5px solid var(--border); background: var(--surface); font-family: inherit; font-size: .82rem; font-weight: 600; color: var(--text-muted); cursor: pointer; transition: all .18s; }
    .pm-refresh-btn:hover { border-color: var(--primary); color: var(--primary); }
    .pm-table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); }
    .pm-table { width: 100%; border-collapse: collapse; }
    .pm-table th { text-align: left; padding: .75rem 1rem; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted); background: var(--bg); border-bottom: 1.5px solid var(--border); }
    .pm-table td { padding: .85rem 1rem; font-size: .83rem; border-bottom: 1px solid var(--border); color: var(--text); vertical-align: middle; }
    .pm-table tr:last-child td { border-bottom: none; }
    .pm-table tr:hover td { background: var(--primary-light); }
    .pm-av { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; font-size: .78rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .pm-chip { display: inline-flex; align-items: center; gap: .3rem; padding: .18rem .6rem; border-radius: 20px; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; }
    .pm-chip-wait { background: var(--bg); color: var(--text-muted); border: 1px solid var(--border); }
    .pm-chip-prog { background: #fff8e8; color: #92400e; border: 1px solid #fcd34d; }
    .pm-chip-done { background: var(--primary-light); color: var(--primary-dark); border: 1px solid rgba(26,158,120,.3); }
    .pm-row-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text-muted); cursor: pointer; font-size: .8rem; display: inline-flex; align-items: center; justify-content: center; transition: all .15s; }
    .pm-row-btn:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }

    /* ── DETAIL MODAL ── */
    @keyframes pmFade { from { opacity: 0; } to { opacity: 1; } }
    @keyframes pmPop  { from { opacity: 0; transform: translate(-50%,-50%) scale(.94); } to { opacity: 1; transform: translate(-50%,-50%) scale(1); } }
    .pm-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.52); z-index: 900; }
    .pm-overlay.show { display: block; animation: pmFade .2s ease; }
    .pm-detail-modal { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); width: min(520px,94vw); max-height: 88vh; background: var(--surface); border-radius: 18px; box-shadow: var(--shadow-lg); z-index: 901; flex-direction: column; overflow: hidden; }
    .pm-detail-modal.show { display: flex; animation: pmPop .25s cubic-bezier(.4,0,.2,1); }
    .pm-detail-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1.1rem 1.25rem; border-bottom: 1px solid var(--border); flex-shrink: 0; background: linear-gradient(135deg, var(--primary-light), rgba(31,115,219,.08)); }
    .pm-detail-head-left { display: flex; align-items: center; gap: .75rem; }
    .pm-detail-icon { width: 40px; height: 40px; border-radius: 12px; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
    .pm-detail-title { font-size: .95rem; font-weight: 700; }
    .pm-detail-sub { font-size: .75rem; color: var(--text-muted); margin-top: .05rem; }
    .pm-detail-close { width: 32px; height: 32px; border: none; background: var(--bg); color: var(--text-muted); border-radius: 50%; cursor: pointer; font-size: .85rem; display: flex; align-items: center; justify-content: center; transition: all .15s; }
    .pm-detail-close:hover { background: #fdecea; color: var(--danger); }
    .pm-detail-body { padding: 1.25rem; overflow-y: auto; flex: 1; }
    .pm-detail-row { display: flex; justify-content: space-between; align-items: center; padding: .5rem 0; border-bottom: 1px solid var(--border); font-size: .85rem; }
    .pm-detail-row:last-child { border-bottom: none; }
    .pm-detail-row span { color: var(--text-muted); }
    .pm-detail-score { display: flex; justify-content: space-between; align-items: center; background: var(--primary-light); border: 1.5px solid rgba(26,158,120,.25); border-radius: 12px; padding: .8rem 1rem; margin-bottom: .75rem; font-size: .9rem; }
    .pm-detail-score strong { font-size: 1.3rem; color: var(--primary); font-family: 'DM Mono', monospace; }

    /* ── ANALYTICS ── */
    .pm-an-wrap { display: flex; flex-direction: column; gap: 1.1rem; }
    .pm-an-engage { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem 1.4rem; box-shadow: var(--shadow); }
    .pm-an-engage-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .9rem; }
    .pm-an-engage-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: var(--text-muted); }
    .pm-an-engage-pct { font-size: 1.5rem; font-weight: 700; color: var(--primary); font-family: 'DM Mono', monospace; }
    .pm-an-engage-pct span { font-size: .72rem; font-weight: 600; color: var(--text-muted); margin-left: .25rem; }
    .pm-an-bar { display: flex; height: 14px; border-radius: 10px; overflow: hidden; gap: 2px; background: var(--border); }
    .pm-an-bar-seg { height: 100%; transition: width .6s cubic-bezier(.4,0,.2,1); min-width: 0; }
    .pm-an-bar-seg--done { background: #1a9e78; }
    .pm-an-bar-seg--gray { background: #cbd5e1; }
    .pm-an-legend { display: flex; flex-wrap: wrap; gap: .65rem; margin-top: .7rem; }
    .pm-an-legend-item { display: flex; align-items: center; gap: .35rem; font-size: .76rem; color: var(--text-muted); }
    .pm-an-legend-dot { width: 9px; height: 9px; border-radius: 50%; }
    .pm-an-stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
    @media(max-width:540px) { .pm-an-stats-row { grid-template-columns: 1fr; } }
    .pm-an-stat-block { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.1rem 1.25rem; box-shadow: var(--shadow); }
    .pm-an-stat-block-title { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: var(--text-muted); margin-bottom: .75rem; display: flex; align-items: center; gap: .4rem; }
    .pm-an-stat-row { display: flex; justify-content: space-between; align-items: center; padding: .45rem 0; border-bottom: 1px solid var(--border); font-size: .85rem; }
    .pm-an-stat-row:last-child { border-bottom: none; }
    .pm-an-stat-row span { color: var(--text-muted); display: flex; align-items: center; gap: .4rem; }
    .pm-an-stat-row strong { font-weight: 700; font-family: 'DM Mono', monospace; }

    @keyframes spin { to { transform: rotate(360deg); } }
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
  <span class="topbar-title" id="pageTopbarTitle">Post Manager</span>
  <div class="topbar-right">
    <button class="icon-btn" onclick="toggleDark()" title="Toggle dark mode"><i class="fa-solid fa-moon" id="darkIcon"></i></button>
  </div>
</header>

<!-- HERO -->
<div class="pm-hero">
  <div class="pm-hero-inner">
    <div class="pm-hero-icon" id="heroIcon"><i class="fa-solid fa-file-lines"></i></div>
    <div class="pm-hero-text">
      <div class="pm-hero-eyebrow" id="heroEyebrow">Post</div>
      <div class="pm-hero-title" id="heroTitle">Loading…</div>
      <div class="pm-hero-sub" id="heroSub">—</div>
      <div class="pm-hero-chips">
        <span class="pm-status-badge" id="heroStatusBadge"><i class="fa-solid fa-circle-half-stroke"></i> <span id="heroStatusText">—</span></span>
        <span class="pm-hero-chip" id="chipQuestions"><i class="fa-solid fa-list-ol"></i> 0 questions</span>
        <span class="pm-hero-chip" id="chipPoints"><i class="fa-solid fa-star"></i> 0 pts</span>
      </div>
      <div class="pm-action-row" id="heroActions" style="display:none;"></div>
    </div>
  </div>
</div>

<!-- TABS -->
<div class="tabs-bar">
  <button class="tab-btn" data-tab="resources" id="tabResourcesBtn" onclick="switchTab('resources')" style="display:none;">
    <i class="fa-solid fa-book-open"></i> Lesson Materials
  </button>
  <button class="tab-btn" data-tab="overview" id="tabOverviewBtn" onclick="switchTab('overview')">
    <i class="fa-solid fa-chart-pie"></i> Overview
  </button>
  <button class="tab-btn active" data-tab="submissions" onclick="switchTab('submissions')">
    <i class="fa-solid fa-users"></i> Submissions <span class="tab-badge" id="subBadge">0</span>
  </button>
  <button class="tab-btn" data-tab="questions" onclick="switchTab('questions')">
    <i class="fa-solid fa-list-ol"></i> Questions <span class="tab-badge" id="qBadge">0</span>
  </button>
  <button class="tab-btn" data-tab="analytics" onclick="switchTab('analytics')">
    <i class="fa-solid fa-chart-column"></i> Analytics
  </button>
</div>

<!-- LESSON MATERIALS -->
<div class="tab-pane" id="pane-resources">
  <div class="pm-page">
    <div class="pm-loading" id="resourcesLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
    <div id="resourcesContent" style="display:none;"></div>
  </div>
</div>

<!-- OVERVIEW -->
<div class="tab-pane" id="pane-overview">
  <div class="pm-page">
    <div class="pm-loading" id="overviewLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
    <div id="overviewContent" style="display:none;"></div>
  </div>
</div>

<!-- QUESTIONS -->
<div class="tab-pane" id="pane-questions">
  <div class="pm-page">
    <div class="pm-loading" id="questionsLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
    <div id="questionsContent" style="display:none;"></div>
  </div>
</div>

<!-- SUBMISSIONS -->
<div class="tab-pane active" id="pane-submissions">
  <div class="pm-page">
    <div id="subLiveControls" style="display:none;"></div>
    <div class="pm-sub-toolbar">
      <div class="pm-sub-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" class="pm-sub-search" id="subSearch" placeholder="Search by name or student #…" oninput="filterSubmissions()">
      </div>
      <div class="pm-sort-wrap">
        <button class="pm-sort-btn" id="subSortBtn" type="button" onclick="toggleSortMenu()">
          <i class="fa-solid fa-sliders"></i> Sort
        </button>
        <div class="pm-sort-menu" id="subSortMenu">
          <button class="pm-sort-item active" type="button" data-sort="last_az" onclick="setSubSort('last_az')">
            By Last Name (A-Z) <i class="fa-solid fa-check"></i>
          </button>
          <button class="pm-sort-item" type="button" data-sort="last_za" onclick="setSubSort('last_za')">
            By Last Name (Z-A) <i class="fa-solid fa-check" style="opacity:0;"></i>
          </button>
          <button class="pm-sort-item" type="button" data-sort="score_desc" onclick="setSubSort('score_desc')">
            Highest Score <i class="fa-solid fa-check" style="opacity:0;"></i>
          </button>
          <button class="pm-sort-item" type="button" data-sort="score_asc" onclick="setSubSort('score_asc')">
            Lowest Score <i class="fa-solid fa-check" style="opacity:0;"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="pm-loading" id="subLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
    <div class="pm-table-wrap" id="subTableWrap" style="display:none;">
      <table class="pm-table">
        <thead>
          <tr>
            <th>Student</th>
            <th>Status</th>
            <th>Score</th>
            <th>Submitted</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="subTbody"></tbody>
      </table>
    </div>
    <div id="subEmpty" style="display:none;" class="pm-empty">
      <i class="fa-solid fa-users-slash"></i>
      <h4>No submissions yet</h4>
      <p>Student submissions will appear here once they start responding.</p>
    </div>
  </div>
</div>

<!-- ANALYTICS -->
<div class="tab-pane" id="pane-analytics">
  <div class="pm-page">
    <div class="pm-loading" id="anLoading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
    <div id="anContent" style="display:none;"></div>
  </div>
</div>

<!-- DETAIL MODAL -->
<div class="pm-overlay" id="detailOverlay" onclick="if(event.target===this)closeDetail()"></div>
<div class="pm-detail-modal" id="detailModal" role="dialog" aria-modal="true">
  <div class="pm-detail-head">
    <div class="pm-detail-head-left">
      <div class="pm-detail-icon"><i class="fa-solid fa-user"></i></div>
      <div>
        <div class="pm-detail-title" id="detailTitle">Student</div>
        <div class="pm-detail-sub" id="detailSub">—</div>
      </div>
    </div>
    <button class="pm-detail-close" onclick="closeDetail()" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="pm-detail-body" id="detailBody">
    <div class="pm-loading"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
  </div>
</div>

<!-- LESSON FILE READER -->
<div class="pm-reader-backdrop" id="lessonReaderBackdrop" onclick="if(event.target===this)closeLessonFileReader()">
  <div class="pm-reader-shell">
    <div class="pm-reader-toolbar">
      <i class="fa-solid fa-book-open-reader" style="color:var(--primary);"></i>
      <span class="pm-reader-title" id="lessonReaderTitle">Lesson file</span>
      <span class="pm-reader-page-controls" id="lessonReaderPageControls">
        <button class="pm-reader-btn" type="button" id="lessonReaderPrevious" title="Previous page"><i class="fa-solid fa-chevron-left"></i></button>
        <span class="pm-reader-page-label" id="lessonReaderPageLabel">1 / 1</span>
        <button class="pm-reader-btn" type="button" id="lessonReaderNext" title="Next page"><i class="fa-solid fa-chevron-right"></i></button>
      </span>
      <a class="pm-reader-btn" id="lessonReaderDownload" href="#" download title="Download file"><i class="fa-solid fa-download"></i></a>
      <button class="pm-reader-btn" type="button" onclick="closeLessonFileReader()" title="Close reader"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="pm-reader-body" id="lessonReaderBody"></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
const POST_ID  = <?= json_encode($post_id) ?>;
const CLASS_ID = <?= json_encode($class_id) ?>;

let allData     = {};
let allSubs     = [];
let currentTab  = 'submissions';
let pendingPublishTimer = null;
let pendingPublishEndsAt = 0;
let pendingReleaseTimer = null;
let pendingReleaseEndsAt = 0;
let pendingCtrlState = null;
let subActionBusy = false;
let subSortMode = 'last_az';
let subAutoRefreshTimer = null;
const SUB_AUTO_REFRESH_MS = 5000;
let pendingDueSchedule = null;
let questionBloomFilter = 'all';

/* ── helpers ── */
function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function fmtBloomLevel(level) {
  const key = String(level || '').trim().toLowerCase();
  const labels = {
    remembering: 'Remembering',
    understanding: 'Understanding',
    applying: 'Applying',
    analyzing: 'Analyzing',
    evaluating: 'Evaluating',
    creating: 'Creating'
  };
  return labels[key] || '';
}
function normBloomLevel(level) {
  const key = String(level || '').trim().toLowerCase();
  return ['remembering','understanding','applying','analyzing','evaluating','creating'].includes(key) ? key : '';
}
function buildBloomSummary(questions) {
  const order = ['remembering','understanding','applying','analyzing','evaluating','creating'];
  const counts = { remembering:0, understanding:0, applying:0, analyzing:0, evaluating:0, creating:0 };
  (questions || []).forEach(q => {
    const level = normBloomLevel(q.cognitive_level);
    if (Object.prototype.hasOwnProperty.call(counts, level)) counts[level] += 1;
  });
  const chips = order
    .filter(level => counts[level] > 0)
    .map(level => `<button type="button" class="pm-q-bloom pm-bloom-chip-btn is-${level} ${questionBloomFilter === level ? 'is-active' : 'is-dim'}" onclick="setQuestionBloomFilter('${level}')"><i class="fa-solid fa-brain" style="font-size:.6rem;"></i>${esc(fmtBloomLevel(level))}</button>`)
    .join('');
  return chips ? `<div class="pm-bloom-summary">${chips}</div>` : '';
}
function setQuestionBloomFilter(level) {
  const next = normBloomLevel(level) || 'all';
  questionBloomFilter = (questionBloomFilter === next) ? 'all' : next;
  renderQuestions();
}
function fmtDt(s) {
  if (!s) return '—';
  try {
    const d = new Date(String(s).replace(' ','T'));
    if (isNaN(d)) return s;
    return d.toLocaleString([], { month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
  } catch(e) { return s; }
}
function fmtWhole(n) {
  if (n === null || n === undefined || n === '') return '—';
  const v = Number(n);
  if (!Number.isFinite(v)) return '—';
  return String(Math.round(v));
}
function toDtLocalValue(s) {
  if (!s) return '';
  const d = new Date(String(s).replace(' ', 'T'));
  if (isNaN(d)) return '';
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  const hh = String(d.getHours()).padStart(2, '0');
  const mi = String(d.getMinutes()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
}
function getDueSchedule() {
  const p = allData?.post || {};
  return {
    open_at: pendingDueSchedule?.open_at || p.open_at || '',
    close_at: pendingDueSchedule?.close_at || p.close_at || ''
  };
}
async function ensureDueSchedule() {
  const curr = getDueSchedule();
  const { value } = await Swal.fire({
    title: 'Set schedule window',
    html: `
      <div style="display:grid;gap:.7rem;text-align:left;">
        <label style="font-size:.86rem;font-weight:600;color:#4b5563;">Start time</label>
        <input id="swalOpenAt" type="datetime-local" class="swal2-input" style="margin:0;" value="${esc(toDtLocalValue(curr.open_at))}">
        <label style="font-size:.86rem;font-weight:600;color:#4b5563;">End time</label>
        <input id="swalCloseAt" type="datetime-local" class="swal2-input" style="margin:0;" value="${esc(toDtLocalValue(curr.close_at))}">
      </div>`,
    showCancelButton: true,
    confirmButtonText: 'Save schedule',
    cancelButtonText: 'Cancel',
    focusConfirm: false,
    preConfirm: () => {
      const open = document.getElementById('swalOpenAt')?.value || '';
      const close = document.getElementById('swalCloseAt')?.value || '';
      if (!open || !close) {
        Swal.showValidationMessage('Start and end datetime are required.');
        return false;
      }
      if (new Date(close).getTime() <= new Date(open).getTime()) {
        Swal.showValidationMessage('End datetime must be later than start datetime.');
        return false;
      }
      return { open_at: open, close_at: close };
    }
  });
  if (!value) return false;
  pendingDueSchedule = value;
  if (allData?.post) {
    allData.post.open_at = value.open_at.replace('T', ' ');
    allData.post.close_at = value.close_at.replace('T', ' ');
  }
  if (currentTab === 'submissions' && allData?.post) renderSubmissionLiveControls(allData.post || {});
  return true;
}
function toggleDark() {
  document.body.classList.toggle('dark');
  document.getElementById('darkIcon').className = document.body.classList.contains('dark')
    ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
  localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
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
  if (tab === 'submissions') startSubAutoRefresh();
  else stopSubAutoRefresh();
  if (tab === 'overview')    renderOverview();
  if (tab === 'questions')   renderQuestions();
  if (tab === 'submissions') loadSubmissions(false);
  if (tab === 'analytics')   renderAnalytics();
  if (tab === 'resources')   renderLessonResources();
}

function isLessonPost(post) {
  const values = [post?.type_key, post?.post_type, post?.type_label, post?.sub_label]
    .map(value => String(value || '').toLowerCase());
  return values.some(value => value === 'lesson' || value.includes('lesson'));
}

function applyQuizTabMode(post, summary) {
  const isLesson = isLessonPost(post);
  const resourcesBtn = document.getElementById('tabResourcesBtn');
  const resourcesPane = document.getElementById('pane-resources');
  const standardTabs = document.querySelectorAll('.tab-btn:not([data-tab="resources"])');
  const standardPanes = document.querySelectorAll('.tab-pane:not(#pane-resources)');
  if (resourcesBtn && resourcesPane) {
    resourcesBtn.style.display = isLesson ? '' : 'none';
    resourcesPane.style.display = isLesson ? '' : 'none';
    standardTabs.forEach(tab => { tab.style.display = isLesson ? 'none' : ''; });
    standardPanes.forEach(pane => { pane.style.display = isLesson ? 'none' : ''; });
    if (isLesson && currentTab !== 'resources') {
      switchTab('resources');
      return;
    }
  }
  const isQuiz = Number(post?.type_has_quiz || 0) === 1;
  const qCount = Number(summary?.question_count || 0);
  const hasQuestions = qCount > 0;
  const overviewBtn = document.getElementById('tabOverviewBtn');
  const questionsBtn = document.querySelector('.tab-btn[data-tab="questions"]');
  const overviewPane = document.getElementById('pane-overview');
  const questionsPane = document.getElementById('pane-questions');
  if (!overviewBtn || !overviewPane || !questionsBtn || !questionsPane) return;

  // Quiz posts keep Questions tab, but hide Overview.
  // Non-quiz posts with zero questions hide both Overview and Questions.
  const hideOverview = isQuiz || !hasQuestions;
  const hideQuestions = !isQuiz && !hasQuestions;

  overviewBtn.style.display = hideOverview ? 'none' : '';
  overviewPane.style.display = hideOverview ? 'none' : '';
  questionsBtn.style.display = hideQuestions ? 'none' : '';
  questionsPane.style.display = hideQuestions ? 'none' : '';

  if ((hideOverview && currentTab === 'overview') || (hideQuestions && currentTab === 'questions')) {
    switchTab('submissions');
  }
}

async function switchToDefaultTab() {
  if (isLessonPost(allData?.post)) {
    switchTab('resources');
    return;
  }
  const isQuiz = Number(allData?.post?.type_has_quiz || 0) === 1;
  const qCount = Number(allData?.summary?.question_count || 0);
  if (isQuiz || qCount <= 0) {
    switchTab('submissions');
    return;
  }
  switchTab('overview');
}

function lessonResourceCard(attachment) {
  const type = String(attachment.attach_type || '').toLowerCase();
  const isLink = type === 'link' || type === 'youtube';
  const href = isLink ? attachment.url : (attachment.file_path || attachment.url || '#');
  const title = isLink
    ? (type === 'youtube' ? 'YouTube Review Link' : 'Review Link')
    : (attachment.file_name || 'Lesson File');
  const subtitle = isLink ? href : 'Open or download this lesson material';
  const icon = type === 'youtube' ? 'fa-brands fa-youtube' : (isLink ? 'fa-solid fa-link' : 'fa-solid fa-file-arrow-down');
  const openAttributes = isLink
    ? `href="${esc(href)}" target="_blank" rel="noopener"`
    : `type="button" data-reader-url="${esc(href)}" data-reader-name="${esc(title)}" data-reader-mime="${esc(attachment.mime_type || '')}" data-reader-attach-id="${esc(attachment.id || '')}" onclick="openLessonFileReaderFromButton(this)"`;
  return `
    <${isLink ? 'a' : 'button'} class="pm-resource-card" ${openAttributes}>
      <span class="pm-resource-icon ${type === 'youtube' ? 'is-youtube' : ''}"><i class="${icon}"></i></span>
      <span class="pm-resource-meta">
        <span class="pm-resource-title">${esc(title)}</span>
        <span class="pm-resource-sub">${esc(subtitle)}</span>
      </span>
      <i class="fa-solid fa-arrow-up-right-from-square pm-resource-open"></i>
    </${isLink ? 'a' : 'button'}>`;
}

function openLessonFileReaderFromButton(button) {
  openLessonFileReader(button.dataset.readerUrl, button.dataset.readerName, button.dataset.readerMime, button.dataset.readerAttachId);
}

function openLessonFileReader(url, name, mime, attachmentId) {
  const backdrop = document.getElementById('lessonReaderBackdrop');
  const body = document.getElementById('lessonReaderBody');
  const download = document.getElementById('lessonReaderDownload');
  const extension = String(name || '').split('.').pop().toLowerCase();
  const safeUrl = esc(url);
  resetLessonPdfReader();
  document.getElementById('lessonReaderTitle').textContent = name || 'Lesson file';
  download.href = url;
  body.innerHTML = '';
  if (extension === 'pdf' || String(mime).includes('pdf')) {
    renderLessonPdf(url, body);
  } else if (['ppt', 'pptx'].includes(extension) && attachmentId) {
    const previewUrl = `API/facultyUI/classroom/get_attachment_preview.php?attach_id=${encodeURIComponent(attachmentId)}`;
    renderLessonPdf(previewUrl, body);
  } else if (['jpg','jpeg','png','gif','webp','svg','bmp','avif'].includes(extension) || String(mime).startsWith('image/')) {
    body.innerHTML = `<img class="pm-reader-image" src="${safeUrl}" alt="${esc(name)}">`;
  } else if (['mp4','webm','ogg','mov'].includes(extension) || String(mime).startsWith('video/')) {
    body.innerHTML = `<video class="pm-reader-media" src="${safeUrl}" controls></video>`;
  } else if (['mp3','wav','aac','m4a'].includes(extension) || String(mime).startsWith('audio/')) {
    body.innerHTML = `<audio class="pm-reader-media" src="${safeUrl}" controls></audio>`;
  } else if (['txt','csv','json','xml','css','js','md','sql','log'].includes(extension)) {
    fetch(url).then(response => response.text()).then(text => {
      body.innerHTML = `<pre class="pm-reader-text">${esc(text)}</pre>`;
    }).catch(() => renderLessonReaderFallback(body, url, name));
  } else {
    renderLessonReaderFallback(body, url, name);
  }
  backdrop.classList.add('show');
  document.body.style.overflow = 'hidden';
}

const LESSON_PDFJS_URL = 'assets/vendor/pdfjs/pdf.min.js';
const LESSON_PDFJS_WORKER = 'assets/vendor/pdfjs/pdf.worker.js';
let lessonPdfDocument = null;
let lessonPdfPage = 1;
let lessonPdfRendering = false;

function resetLessonPdfReader() {
  lessonPdfDocument = null;
  lessonPdfPage = 1;
  lessonPdfRendering = false;
  document.getElementById('lessonReaderPageControls').classList.remove('show');
}

function renderLessonPdf(url, body) {
  body.innerHTML = `<div class="pm-reader-empty"><i class="fa-solid fa-spinner fa-spin"></i><h3>Preparing preview</h3><p>Loading one page at a time…</p></div>`;
  const start = () => {
    window.pdfjsLib.GlobalWorkerOptions.workerSrc = LESSON_PDFJS_WORKER;
    fetch(url, { credentials: 'same-origin', cache: 'no-store' })
      .then(response => {
        if (!response.ok) throw new Error(`Preview request failed (${response.status})`);
        return response.arrayBuffer();
      })
      .then(buffer => window.pdfjsLib.getDocument({ data: new Uint8Array(buffer) }).promise)
      .then(document => {
        lessonPdfDocument = document;
        document.getElementById('lessonReaderPageControls').classList.add('show');
        renderLessonPdfPage(1);
      })
      .catch(error => {
        console.error('Lesson PDF preview failed:', error);
        renderLessonReaderFallback(body, url, 'This document');
      });
  };
  if (window.pdfjsLib) {
    start();
    return;
  }
  const script = document.createElement('script');
  script.src = LESSON_PDFJS_URL;
  script.onload = start;
  script.onerror = () => renderLessonReaderFallback(body, url, 'This document');
  document.head.appendChild(script);
}

function renderLessonPdfPage(pageNumber) {
  if (!lessonPdfDocument || lessonPdfRendering) return;
  lessonPdfRendering = true;
  lessonPdfDocument.getPage(pageNumber).then(page => {
    const body = document.getElementById('lessonReaderBody');
    const viewport = page.getViewport({ scale: 1.8 });
    body.innerHTML = '<div class="pm-reader-canvas-wrap"><canvas class="pm-reader-canvas" id="lessonReaderCanvas"></canvas></div>';
    const canvas = document.getElementById('lessonReaderCanvas');
    const context = canvas.getContext('2d');
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    return page.render({ canvasContext: context, viewport }).promise;
  }).then(() => {
    lessonPdfPage = pageNumber;
    lessonPdfRendering = false;
    updateLessonPdfControls();
  }).catch(() => {
    lessonPdfRendering = false;
  });
}

function updateLessonPdfControls() {
  document.getElementById('lessonReaderPageLabel').textContent = `${lessonPdfPage} / ${lessonPdfDocument.numPages}`;
  document.getElementById('lessonReaderPrevious').disabled = lessonPdfPage <= 1;
  document.getElementById('lessonReaderNext').disabled = lessonPdfPage >= lessonPdfDocument.numPages;
}

document.getElementById('lessonReaderPrevious').addEventListener('click', () => {
  if (lessonPdfPage > 1) renderLessonPdfPage(lessonPdfPage - 1);
});
document.getElementById('lessonReaderNext').addEventListener('click', () => {
  if (lessonPdfDocument && lessonPdfPage < lessonPdfDocument.numPages) renderLessonPdfPage(lessonPdfPage + 1);
});

function renderLessonReaderFallback(body, url, name) {
  resetLessonPdfReader();
  body.innerHTML = `
    <div class="pm-reader-empty">
      <i class="fa-solid fa-file-circle-info"></i>
      <h3>Preview is not available inside the browser</h3>
      <p>${esc(name)} can still be opened manually or downloaded. The file will no longer download automatically when selected.</p>
      <a href="${esc(url)}" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open file</a>
      <a href="${esc(url)}" download><i class="fa-solid fa-download"></i> Download</a>
    </div>`;
}

function closeLessonFileReader() {
  const backdrop = document.getElementById('lessonReaderBackdrop');
  const body = document.getElementById('lessonReaderBody');
  body.querySelectorAll('video,audio').forEach(media => media.pause());
  body.innerHTML = '';
  resetLessonPdfReader();
  backdrop.classList.remove('show');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', event => {
  if (event.key === 'Escape' && document.getElementById('lessonReaderBackdrop').classList.contains('show')) {
    closeLessonFileReader();
  }
});

async function renderLessonResources() {
  const loading = document.getElementById('resourcesLoading');
  const content = document.getElementById('resourcesContent');
  loading.style.display = 'flex';
  content.style.display = 'none';
  try {
    const j = Object.keys(allData).length ? allData : await fetchPost();
    allData = j;
    const post = j.post || {};
    const attachments = Array.isArray(j.attachments) ? j.attachments : [];
    const files = attachments.filter(item => !['link', 'youtube'].includes(String(item.attach_type || '').toLowerCase()));
    const links = attachments.filter(item => ['link', 'youtube'].includes(String(item.attach_type || '').toLowerCase()));
    const empty = `<div class="pm-empty" style="padding:1.4rem .8rem;"><i class="fa-solid fa-folder-open"></i><p>No materials added yet.</p></div>`;
    content.innerHTML = `
      <div class="pm-lesson-intro">
        <i class="fa-solid fa-book-open-reader"></i>
        <div><h3>Lesson review materials</h3><p>Open the uploaded files and links below while reviewing this topic.</p></div>
      </div>
      <div class="pm-lesson-grid">
        <div class="pm-lesson-stack">
          <section class="pm-section">
            <div class="pm-section-head"><i class="fa-solid fa-bullseye" style="color:var(--primary);"></i><div class="pm-section-head-title">Topic</div></div>
            <div class="pm-resource-copy">${esc(post.topic || post.title || 'No topic added.')}</div>
          </section>
          <section class="pm-section">
            <div class="pm-section-head"><i class="fa-solid fa-align-left" style="color:var(--primary);"></i><div class="pm-section-head-title">Instructions</div></div>
            <div class="pm-resource-copy">${esc(post.body || 'No instructions added.')}</div>
          </section>
        </div>
        <div class="pm-lesson-stack">
          <section class="pm-section">
            <div class="pm-section-head"><i class="fa-solid fa-folder-open" style="color:var(--primary);"></i><div class="pm-section-head-title">Uploaded Files</div></div>
            ${files.length ? `<div class="pm-resource-list">${files.map(lessonResourceCard).join('')}</div>` : empty}
          </section>
          <section class="pm-section">
            <div class="pm-section-head"><i class="fa-solid fa-link" style="color:var(--primary);"></i><div class="pm-section-head-title">Review Links</div></div>
            ${links.length ? `<div class="pm-resource-list">${links.map(lessonResourceCard).join('')}</div>` : empty}
          </section>
        </div>
      </div>`;
    loading.style.display = 'none';
    content.style.display = 'block';
  } catch (e) {
    loading.innerHTML = `<span style="color:#d93025;font-size:.85rem;">Failed to load lesson materials. <button onclick="renderLessonResources()" style="background:none;border:none;color:#1a9e78;cursor:pointer;font-weight:700;">Retry</button></span>`;
  }
}

/* ── FETCH ── */
async function fetchPost() {
  const r = await fetch(`API/facultyUI/classroom/get_post_manage.php?class_id=${encodeURIComponent(CLASS_ID)}&post_id=${encodeURIComponent(POST_ID)}&_ts=${Date.now()}`, {
    cache: 'no-store'
  });
  const j = await r.json();
  if (j.status !== 'success') throw new Error(j.message || 'Load failed');
  return j;
}

function getQuizMode(p) {
  const qm = String((p && p.quiz_mode) || '').toLowerCase();
  if (qm === 'live' || qm === 'due_date') return qm;
  const tm = String((p && p.time_mode) || '').toLowerCase();
  if (tm === 'live') return 'live';
  return 'due_date';
}

function quizModeLabel(mode) {
  return mode === 'live' ? 'Live Mode' : 'Due-Date Mode';
}

function publishStateHtml(p) {
  if (pendingPublishTimer) {
    const remain = Math.max(0, Math.ceil((pendingPublishEndsAt - Date.now()) / 1000));
    return `<button class="pm-row-btn" style="width:auto;padding:0 .75rem;border-radius:999px;border-color:#f59e0b;color:#b45309;" onclick="cancelPendingPublish()"><i class="fa-solid fa-rotate-left"></i>&nbsp;Undo (${remain}s)</button>`;
  }
  const isPublished = Number((p && p.is_published) || 0) === 1;
  if (isPublished) {
    return `<button class="pm-row-btn" style="width:auto;padding:0 .75rem;border-radius:999px;border-color:#f8c9c5;color:#b42318;background:#fff5f4;" onclick="unpublishQuiz()"><i class="fa-solid fa-rotate-left"></i>&nbsp;Unpublish</button>`;
  }
  return `<button class="pm-row-btn" style="width:auto;padding:0 .75rem;border-radius:999px;" onclick="startPublishFlow()"><i class="fa-solid fa-paper-plane"></i>&nbsp;Publish</button>`;
}

/* ── HERO ── */
function updateHero(j) {
  const p = j.post, s = j.summary, c = j.class;
  applyQuizTabMode(p, s);
  const title = p.title || '(Untitled)';
  document.title = 'Post Manager — ' + title;
  document.getElementById('pageTopbarTitle').textContent = title;
  document.getElementById('heroEyebrow').textContent = p.type_label || p.post_type || 'Post';
  document.getElementById('heroTitle').textContent = title;
  document.getElementById('heroSub').textContent =
    ([c.subject_code, c.subject_name].filter(Boolean).join(' ') || p.body || '').trim() || '—';

  const qCount = Number(s.question_count || 0);
  const pts    = Number(s.question_points || 0);
  document.getElementById('chipQuestions').innerHTML = `<i class="fa-solid fa-list-ol"></i> ${qCount} question${qCount !== 1 ? 's' : ''}`;
  document.getElementById('chipPoints').innerHTML    = `<i class="fa-solid fa-star"></i> ${pts.toFixed(0)} pts`;
  document.getElementById('qBadge').textContent  = qCount;
  document.getElementById('subBadge').textContent = s.submission_count || 0;
  const isLesson = isLessonPost(p);
  document.getElementById('chipQuestions').style.display = isLesson ? 'none' : '';
  document.getElementById('chipPoints').style.display = isLesson ? 'none' : '';

  const badge = document.getElementById('heroStatusBadge');
  const isQuiz = p.type_has_quiz == 1;
  const isGradable = p.type_is_gradable == 1;
  let modeText, modeCls, modeIcon;
  if (isQuiz) {
    const qm = getQuizMode(p);
    modeText = quizModeLabel(qm);
    modeCls = 'is-quiz'; modeIcon = 'fa-clipboard-question';
  } else if (isGradable) {
    modeText = 'Gradable'; modeCls = 'is-gradable'; modeIcon = 'fa-check-circle';
  } else {
    modeText = 'Non-gradable'; modeCls = 'is-none'; modeIcon = 'fa-file-lines';
  }
  badge.className = 'pm-status-badge ' + modeCls;
  document.getElementById('heroStatusText').textContent = modeText;
  const icon = badge.querySelector('i'); if (icon) icon.className = 'fa-solid ' + modeIcon;

  const heroIcon = document.getElementById('heroIcon');
  heroIcon.innerHTML = `<i class="fa-solid ${isQuiz ? 'fa-clipboard-question' : (isGradable ? 'fa-file-pen' : 'fa-file-lines')}"></i>`;

  // Actions are rendered inside the Submissions controls for quiz posts.
  const actionWrap = document.getElementById('heroActions');
  if (actionWrap) actionWrap.innerHTML = '';
}

async function quizPublishLive() {
  const fd = new FormData();
  const post = allData?.post || {};
  const mode = getQuizMode(post);
  fd.append('post_id', POST_ID);
  fd.append('quiz_mode', mode);
  fd.append('max_attempts', '1');
  if (mode === 'due_date') {
    const sched = getDueSchedule();
    if (sched.open_at) fd.append('open_at', sched.open_at);
    if (sched.close_at) fd.append('due_date', sched.close_at);
  }
  try {
    subActionBusy = true;
    const res = await fetch('API/facultyUI/classroom/quiz/publish_quiz.php', { method: 'POST', body: fd, credentials:'same-origin' });
    const data = await res.json();
    if (data.status !== 'success') throw new Error(data.message || 'Publish failed');
    resetSubmissionTableView();
    if (currentTab === 'submissions') await loadSubmissions(true);
    else await switchToDefaultTab();
  } catch (e) {
    Swal.fire({ icon:'error', title:'Publish failed', text: String(e.message || e) });
  } finally {
    subActionBusy = false;
  }
}

async function startPublishFlow() {
  if (pendingPublishTimer) return;
  const mode = getQuizMode(allData?.post || {});
  if (mode === 'due_date') {
    const sched = getDueSchedule();
    const hasValid = sched.open_at && sched.close_at && (new Date(sched.close_at).getTime() > new Date(sched.open_at).getTime());
    if (!hasValid) {
      const okSet = await ensureDueSchedule();
      if (!okSet) return;
    }
  }
  const ok = await Swal.fire({
    icon: 'warning',
    title: 'Publish this quiz?',
    text: 'After 5 seconds, publish becomes final.',
    showCancelButton: true,
    confirmButtonText: 'Yes, publish',
    cancelButtonText: 'Cancel'
  });
  if (!ok.isConfirmed) return;

  pendingPublishEndsAt = Date.now() + 5000;
  const tick = async () => {
    if (!pendingPublishTimer) return;
    if (Date.now() >= pendingPublishEndsAt) {
      clearInterval(pendingPublishTimer);
      pendingPublishTimer = null;
      await quizPublishLive();
      return;
    }
    if (allData?.post) updateHero(allData);
    if (currentTab === 'submissions' && allData?.post) renderSubmissionLiveControls(allData.post || {});
    if (currentTab === 'overview') renderOverview();
  };
  pendingPublishTimer = setInterval(tick, 250);
  tick();
}

function cancelPendingPublish() {
  if (!pendingPublishTimer) return;
  clearInterval(pendingPublishTimer);
  pendingPublishTimer = null;
  pendingPublishEndsAt = 0;
  if (allData?.post) updateHero(allData);
  if (currentTab === 'submissions' && allData?.post) renderSubmissionLiveControls(allData.post || {});
  if (currentTab === 'overview') renderOverview();
}

async function quizAction(action) {
  if (action === 'release_results') {
    return startReleaseResultsFlow();
  }
  const fd = new FormData();
  fd.append('post_id', POST_ID);
  fd.append('action', action);
  try {
    subActionBusy = true;
    const res = await fetch('API/facultyUI/classroom/quiz/start_live_quiz.php', { method:'POST', body:fd, credentials:'same-origin' });
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Action failed');
    pendingReleaseEndsAt = 0;
    if (currentTab === 'submissions') await loadSubmissions(true);
    else await switchToDefaultTab();
  } catch (e) {
    Swal.fire({ icon:'error', title:'Action failed', text: String(e.message || e) });
  } finally {
    subActionBusy = false;
  }
}

async function doReleaseResultsNow() {
  const fd = new FormData();
  fd.append('post_id', POST_ID);
  fd.append('action', 'release_results');
  try {
    subActionBusy = true;
    const res = await fetch('API/facultyUI/classroom/quiz/start_live_quiz.php', { method:'POST', body:fd, credentials:'same-origin' });
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Action failed');
    if (currentTab === 'submissions') await loadSubmissions(true);
    else await switchToDefaultTab();
    Swal.fire({ icon:'success', title:'Results shared to students', timer:1500, showConfirmButton:false });
  } catch (e) {
    Swal.fire({ icon:'error', title:'Action failed', text: String(e.message || e) });
  } finally {
    subActionBusy = false;
  }
}

async function startReleaseResultsFlow() {
  if (pendingReleaseTimer) return;
  const ok = await Swal.fire({
    icon: 'warning',
    title: 'Share results to students?',
    text: 'Results will be released in 5 seconds. You can undo before it becomes final.',
    showCancelButton: true,
    confirmButtonText: 'Yes, share results',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#1a9e78'
  });
  if (!ok.isConfirmed) return;

  pendingReleaseEndsAt = Date.now() + 5000;
  const tick = async () => {
    if (!pendingReleaseTimer) return;
    if (Date.now() >= pendingReleaseEndsAt) {
      clearInterval(pendingReleaseTimer);
      pendingReleaseTimer = null;
      await doReleaseResultsNow();
      return;
    }
    if (currentTab === 'submissions' && allData?.post) renderSubmissionLiveControls(allData.post || {});
    if (currentTab === 'overview') renderOverview();
  };
  pendingReleaseTimer = setInterval(tick, 250);
  tick();
}

function cancelPendingReleaseResults() {
  if (!pendingReleaseTimer) return;
  clearInterval(pendingReleaseTimer);
  pendingReleaseTimer = null;
  pendingReleaseEndsAt = 0;
  if (currentTab === 'submissions' && allData?.post) renderSubmissionLiveControls(allData.post || {});
  if (currentTab === 'overview') renderOverview();
}

async function unpublishQuiz() {
  if (pendingPublishTimer) cancelPendingPublish();
  const confirm = await Swal.fire({
    icon: 'warning',
    title: 'Unpublish this quiz?',
    text: 'This will reset joined students, attempts, and scores. They can join and take the quiz again after republish.',
    showCancelButton: true,
    confirmButtonText: 'Yes, unpublish and reset',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#d93025'
  });
  if (!confirm.isConfirmed) return;

  const fd = new FormData();
  fd.append('post_id', POST_ID);
  fd.append('hard_reset', '1');
  try {
    subActionBusy = true;
    const res = await fetch('API/facultyUI/classroom/quiz/unpublish_quiz.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    });
    const data = await res.json();
    if (data.status !== 'success') throw new Error(data.message || 'Unpublish failed');
    resetSubmissionTableView();
    await loadSubmissions(true);
    Swal.fire({ icon: 'success', title: 'Unpublished and records reset', timer: 1400, showConfirmButton: false });
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Unpublish failed', text: String(e.message || e) });
  } finally {
    subActionBusy = false;
  }
}

/* ── OVERVIEW ── */
async function renderOverview() {
  const loading = document.getElementById('overviewLoading');
  const content = document.getElementById('overviewContent');
  loading.style.display = 'flex'; content.style.display = 'none';
  try {
    const j = await fetchPost();
    allData = j;
    updateHero(j);
    const p = j.post, s = j.summary, c = j.class;

    const enrolled   = Number(s.enrolled_count   || 0);
    const submitted  = Number(s.submission_count || 0);
    const graded     = Number(s.graded_count     || 0);
    const avgScore   = (s.average_score === null || s.average_score === undefined) ? null : Number(s.average_score);
    const qCount     = Number(s.question_count   || 0);
    const pts        = Number(s.question_points  || 0);

    content.innerHTML = `
      <div class="pm-stats-grid">
        <div class="pm-stat-card">
          <div class="pm-stat-card-icon pm-stat-card-icon--green"><i class="fa-solid fa-users"></i></div>
          <div class="pm-stat-value">${enrolled}</div>
          <div class="pm-stat-label">Enrolled</div>
        </div>
        <div class="pm-stat-card">
          <div class="pm-stat-card-icon pm-stat-card-icon--blue"><i class="fa-solid fa-file-export"></i></div>
          <div class="pm-stat-value">${submitted}</div>
          <div class="pm-stat-label">Submitted</div>
        </div>
        <div class="pm-stat-card">
          <div class="pm-stat-card-icon pm-stat-card-icon--amber"><i class="fa-solid fa-check-double"></i></div>
          <div class="pm-stat-value">${graded}</div>
          <div class="pm-stat-label">Graded</div>
        </div>
        <div class="pm-stat-card">
          <div class="pm-stat-card-icon pm-stat-card-icon--gray"><i class="fa-solid fa-calculator"></i></div>
          <div class="pm-stat-value">${avgScore === null ? '—' : Number(avgScore).toFixed(1)}</div>
          <div class="pm-stat-label">Avg Score</div>
        </div>
      </div>

      <div class="pm-section">
        <div class="pm-section-head">
          <i class="fa-solid fa-circle-info" style="color:var(--primary);font-size:.85rem;"></i>
          <div class="pm-section-head-title">Post Details</div>
        </div>
        <div class="pm-section-body">
          <div class="pm-info-row"><span><i class="fa-solid fa-tag"></i>Type</span><strong>${esc(p.type_label || p.post_type || '—')}</strong></div>
          ${(p.type_has_quiz == 1) ? `<div class="pm-info-row"><span><i class="fa-solid fa-sliders"></i>Mode</span><strong>${esc(quizModeLabel(getQuizMode(p)))}</strong></div>` : ''}
          ${(p.type_has_quiz == 1) ? `<div class="pm-info-row"><span><i class="fa-solid fa-bullhorn"></i>Publish</span><strong style="font-family:inherit;">${publishStateHtml(p)}</strong></div>` : ''}
          <div class="pm-info-row"><span><i class="fa-solid fa-list-ol"></i>Questions</span><strong>${qCount}</strong></div>
          <div class="pm-info-row"><span><i class="fa-solid fa-star"></i>Total Points</span><strong>${pts.toFixed(0)}</strong></div>
          ${p.body ? `<div class="pm-info-row"><span><i class="fa-solid fa-align-left"></i>Description</span><strong style="font-family:inherit;text-align:right;max-width:60%;word-break:break-word;">${esc(p.body)}</strong></div>` : ''}
          ${c.subject_code ? `<div class="pm-info-row"><span><i class="fa-solid fa-book"></i>Subject</span><strong>${esc(c.subject_code)} — ${esc(c.subject_name)}</strong></div>` : ''}
        </div>
      </div>`;

    loading.style.display = 'none'; content.style.display = 'block';
  } catch(e) {
    loading.innerHTML = `<span style="color:#d93025;font-size:.85rem;">Failed to load. <button onclick="renderOverview()" style="background:none;border:none;color:#1a9e78;cursor:pointer;font-weight:700;">Retry</button></span>`;
  }
}

/* ── QUESTIONS ── */
async function renderQuestions() {
  const loading = document.getElementById('questionsLoading');
  const content = document.getElementById('questionsContent');
  loading.style.display = 'flex'; content.style.display = 'none';
  try {
    const j = Object.keys(allData).length ? allData : await fetchPost();
    allData = j;
    const postData = j.post || {};
    const defaultPerQuestionSeconds = (String(postData.time_mode || '').toLowerCase() === 'per_question')
      ? Number(postData.quiz_time_limit_seconds || postData.time_limit_seconds || 0)
      : 0;
    const questions = (j.questions || []).map((q, idx) => ({ ...q, _renderIndex: idx }));
    const filteredQuestions = questionBloomFilter === 'all'
      ? questions
      : questions.filter(q => normBloomLevel(q.cognitive_level) === questionBloomFilter);

    if (!questions.length) {
      content.innerHTML = `
        <div class="pm-empty">
          <i class="fa-solid fa-list-ol"></i>
          <h4>No questions found</h4>
          <p>This post doesn't have any questions attached.</p>
        </div>`;
    } else if (!filteredQuestions.length) {
      content.innerHTML = `${buildBloomSummary(questions)}
        <div class="pm-empty">
          <i class="fa-solid fa-brain"></i>
          <h4>No ${esc(fmtBloomLevel(questionBloomFilter))} questions</h4>
          <p>Try another Bloom's taxonomy filter or click All to show the full quiz.</p>
        </div>`;
    } else {
      content.innerHTML = `${buildBloomSummary(questions)}<div class="pm-q-list">${filteredQuestions.map((q, i) => `
        <div class="pm-q-card" onclick="toggleQDrop(${q._renderIndex})">
          <div class="pm-q-num">Q${q._renderIndex + 1}</div>
            <div class="pm-q-body">
              <div class="pm-q-topline">
                <div class="pm-q-text">${esc(q.question || '(No question text)')}</div>
                <div class="pm-q-topline-right">
                  ${Number(q.time_limit_seconds || 0) > 0 && Number(q.time_limit_seconds || 0) !== defaultPerQuestionSeconds ? `<span class="pm-q-sidebox" title="${parseInt(q.time_limit_seconds, 10)} seconds">${parseInt(q.time_limit_seconds, 10)}</span>` : ''}
                  <i id="qchev_${q._renderIndex}" class="fa-solid fa-chevron-down pm-q-chevron"></i>
                </div>
              </div>
              <div class="pm-q-meta">
                <span class="pm-q-pts"><i class="fa-solid fa-star" style="font-size:.6rem;"></i>${Number(q.points || 0)} pts</span>
                ${fmtBloomLevel(q.cognitive_level) ? `<button type="button" class="pm-q-bloom pm-bloom-chip-btn is-${esc(String(q.cognitive_level || '').toLowerCase())}" onclick="event.stopPropagation(); setQuestionBloomFilter('${esc(String(q.cognitive_level || '').toLowerCase())}')"><i class="fa-solid fa-brain" style="font-size:.6rem;"></i>Bloom: ${esc(fmtBloomLevel(q.cognitive_level))}</button>` : ''}
                ${q.type ? `<span class="pm-q-type"><i class="fa-solid fa-shapes" style="font-size:.6rem;"></i>${esc(q.type)}</span>` : ''}
              </div>
              <div class="pm-q-drop" id="qdrop_${q._renderIndex}" onclick="event.stopPropagation()">
                ${(Array.isArray(q.choices) && q.choices.length) ? q.choices.map((c, ci) => `
                <div class="pm-q-choice ${Number(c.is_correct||0)===1 ? 'is-correct' : ''}">
                  <strong>${String.fromCharCode(65 + ci)}.</strong>
                  <span>${esc(c.choice_text || '')}</span>
                  ${Number(c.is_correct||0)===1 ? '<i class="fa-solid fa-check" style="margin-left:auto;"></i>' : ''}
                </div>
              `).join('') : '<div class="pm-q-answer">No choices attached.</div>'}
                ${(!Array.isArray(q.choices) || !q.choices.length) && q.answer_key ? `<div class="pm-q-answer"><strong>Answer:</strong> ${esc(q.answer_key)}</div>` : ''}
              </div>
            </div>
        </div>`).join('')}</div>`;
    }
    loading.style.display = 'none'; content.style.display = 'block';
  } catch(e) {
    loading.innerHTML = `<span style="color:#d93025;font-size:.85rem;">Failed to load questions. <button onclick="renderQuestions()" style="background:none;border:none;color:#1a9e78;cursor:pointer;font-weight:700;">Retry</button></span>`;
  }
}

/* ── SUBMISSIONS ── */
async function loadSubmissions(forceRefresh) {
  const loading = document.getElementById('subLoading');
  const wrap    = document.getElementById('subTableWrap');
  const empty   = document.getElementById('subEmpty');

  const isInitial = !wrap.dataset.loaded;
  if (isInitial || forceRefresh) {
    loading.style.display = 'flex';
    wrap.style.display    = 'none';
    empty.style.display   = 'none';
  }
  try {
    const j = await fetchPost();
    allData = j;
    updateHero(j);
    renderSubmissionLiveControls(j.post || {});
    const isQuizPost = Number(j?.post?.type_has_quiz || 0) === 1;
    const isPublished = Number(j?.post?.is_published || 0) === 1;
    // Only quiz submissions should be hidden when unpublished.
    // Activity/Assignment/Exam submission rows must still render.
    if (isQuizPost && !isPublished) {
      resetSubmissionTableView();
      loading.style.display = 'none';
      empty.style.display = 'flex';
      wrap.dataset.loaded = '1';
      return;
    }
    const subs = j.submissions || [];
    allSubs = subs;
    document.getElementById('subBadge').textContent = subs.length;

    loading.style.display = 'none';
    if (!subs.length) {
      empty.style.display = 'flex';
      wrap.dataset.loaded = '1';
      return;
    }
    applySubFilterSort();
    wrap.style.display = 'block';
    wrap.dataset.loaded = '1';
  } catch(e) {
    loading.innerHTML = `<span style="color:#d93025;font-size:.85rem;">Failed. <button onclick="loadSubmissions(true)" style="background:none;border:none;color:#1a9e78;cursor:pointer;font-weight:700;">Retry</button></span>`;
  }
}

function resetSubmissionTableView() {
  allSubs = [];
  const wrap = document.getElementById('subTableWrap');
  const empty = document.getElementById('subEmpty');
  const tbody = document.getElementById('subTbody');
  const badge = document.getElementById('subBadge');
  if (wrap) {
    wrap.style.display = 'none';
    delete wrap.dataset.loaded;
  }
  if (empty) empty.style.display = 'none';
  if (tbody) tbody.innerHTML = '';
  if (badge) badge.textContent = '0';
}

function renderSubTable(rows) {
  const tbody = document.getElementById('subTbody');
  if (!tbody) return;
  const isQuizPost = Number(allData?.post?.type_has_quiz || 0) === 1;
  tbody.innerHTML = rows.map(r => {
    const name   = esc(r.student_name || '—');
    const snum   = esc(r.student_number || '');
    const initL  = (r.student_name || '?').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
    const status = String(r.status || '').toLowerCase().replace(/\s+/g, '_');
    let statusHtml = '<span class="pm-chip pm-chip-wait">Not Submitted</span>';
    if (['in_progress', 'inprogress', 'started', 'ongoing'].includes(status)) statusHtml = '<span class="pm-chip pm-chip-prog"><span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#f59e0b;margin-right:.25rem;"></span>In Progress</span>';
    if (['submitted', 'graded', 'completed', 'finished', 'returned'].includes(status)) statusHtml = '<span class="pm-chip pm-chip-done"><i class="fa-solid fa-check" style="font-size:.62rem;"></i> ' + (status === 'graded' ? 'Graded' : 'Submitted') + '</span>';
    const isInProgress = ['in_progress', 'inprogress', 'started', 'ongoing'].includes(status);
    const scoreVal = (r.final_score !== null && r.final_score !== undefined)
      ? r.final_score
      : (isInProgress ? r.live_score : null);
    const scoreHtml = (scoreVal !== null && scoreVal !== undefined)
      ? `${esc(fmtWhole(scoreVal))}${r.max_score !== null ? ` / ${esc(fmtWhole(r.max_score))}` : ''}`
      : '—';
    const maxNum = Number(r.max_score ?? 0);
    const scoreNum = Number(scoreVal ?? NaN);
    const pct = (Number.isFinite(scoreNum) && maxNum > 0) ? (scoreNum / maxNum) * 100 : null;
    let scoreColor = '#bbb';
    if (pct !== null) {
      if (pct > 50) scoreColor = '#1a9e78';
      else if (pct === 50) scoreColor = '#f59e0b';
      else scoreColor = '#d93025';
    }
    const fileUrl = (r.file_path && !String(r.file_path).startsWith('http'))
      ? String(r.file_path).replace(/^\/+/, '')
      : (r.file_path || '');
    const fileBtn = (r.file_path || r.file_name)
      ? `<a class="pm-row-btn" title="View submitted file" href="${esc(fileUrl)}" target="_blank" rel="noopener" onclick="event.stopPropagation()"><i class="fa-solid fa-file-lines"></i></a>`
      : `<button class="pm-row-btn" disabled title="No file submitted"><i class="fa-solid fa-file-lines"></i></button>`;
    const nonQuizActionBtn = (r.submission_id && !isQuizPost)
      ? `<button class="pm-row-btn" title="View file and grade" onclick='event.stopPropagation(); openSubmissionGrader(${JSON.stringify(r)})'><i class="fa-solid fa-pen-to-square"></i></button>`
      : '';
    const rowClick = (!isQuizPost && r.submission_id)
      ? `onclick='openSubmissionGrader(${JSON.stringify(r)})' style="cursor:pointer;"`
      : '';
    return `<tr data-name="${name.toLowerCase()} ${snum.toLowerCase()}" ${rowClick}>
      <td>
        <div style="display:flex;align-items:center;gap:.6rem;">
          <div class="pm-av">${esc(initL)}</div>
          <div><div style="font-weight:600;">${name}</div><div style="font-size:.73rem;color:var(--text-muted);">${snum}</div></div>
        </div>
      </td>
      <td>${statusHtml}</td>
      <td style="font-weight:600;font-family:'DM Mono',monospace;color:${scoreColor};">${esc(scoreHtml)}</td>
      <td style="font-size:.78rem;color:var(--text-muted);">${esc(fmtDt(r.submitted_at))}</td>
      <td style="white-space:nowrap;">
        ${isQuizPost ? `<button class="pm-row-btn" title="View submission" onclick="openDetail(${JSON.stringify(r)})"><i class="fa-solid fa-eye"></i></button>` : `${fileBtn} ${nonQuizActionBtn}`}
      </td>
    </tr>`;
  }).join('');
}

async function openSubmissionGrader(row) {
  if (!row || !row.submission_id) return;
  const name = esc(row.student_name || 'Student');
  const fileName = esc(row.file_name || 'Submitted file');
  const fileUrl = (row.file_path && !String(row.file_path).startsWith('http'))
    ? String(row.file_path).replace(/^\/+/, '')
    : (row.file_path || '');
  const hasFile = Boolean(row.file_path || row.file_name);
  const currentScore = row.final_score !== null && row.final_score !== undefined ? String(fmtWhole(row.final_score)) : '';

  const result = await Swal.fire({
    title: `Grade: ${name}`,
    width: 820,
    showCancelButton: true,
    confirmButtonText: 'Save Score',
    cancelButtonText: 'Close',
    focusConfirm: false,
    html: `
      <div style="display:grid;grid-template-columns:1fr 260px;gap:14px;text-align:left;">
        <div style="border:1px solid var(--border);border-radius:12px;padding:12px;">
          <div style="font-size:.76rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Submitted File</div>
          ${hasFile
            ? `<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                 <i class="fa-solid fa-file-lines" style="color:var(--primary);"></i>
                 <span style="font-size:.88rem;word-break:break-word;">${fileName}</span>
               </div>
               <a href="${esc(fileUrl)}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:.42rem .7rem;border-radius:9px;border:1.5px solid var(--border);text-decoration:none;color:var(--text);font-weight:700;font-size:.8rem;">
                 <i class="fa-solid fa-up-right-from-square"></i> Open File
               </a>`
            : `<div style="font-size:.84rem;color:var(--text-muted);">No file submitted.</div>`
          }
          ${row.comment ? `<div style="margin-top:10px;font-size:.8rem;color:var(--text-muted);"><strong>Comment:</strong> ${esc(String(row.comment))}</div>` : ''}
        </div>
        <div style="border:1px solid var(--border);border-radius:12px;padding:12px;">
          <div style="font-size:.76rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Input Score</div>
          <input id="swal_submission_grade" type="number" min="0" step="1" value="${esc(currentScore)}" placeholder="Enter score"
            style="width:100%;padding:.55rem .65rem;border:1.5px solid var(--border);border-radius:10px;font-size:.95rem;">
        </div>
      </div>
    `,
    preConfirm: () => {
      const input = document.getElementById('swal_submission_grade');
      const grade = String(input?.value || '').trim();
      if (grade === '' || !/^\d+(\.\d+)?$/.test(grade)) {
        Swal.showValidationMessage('Please enter a valid score.');
        return false;
      }
      return grade;
    }
  });

  if (!result.isConfirmed) return;
  await saveSubmissionGrade(row.submission_id, row.student_name || 'Student', result.value);
}

async function saveSubmissionGrade(submissionId, studentName, directGrade) {
  const inlineInput = document.getElementById(`grade_${submissionId}`);
  const grade = String(
    directGrade !== undefined && directGrade !== null
      ? directGrade
      : (inlineInput ? inlineInput.value : '')
  ).trim();
  if (grade === '' || !/^\d+(\.\d+)?$/.test(grade)) {
    Swal.fire({ icon:'warning', title:'Invalid score', text:'Please enter a valid number.' });
    return;
  }
  try {
    const fd = new FormData();
    fd.append('submission_id', submissionId);
    fd.append('grade', grade);
    const res = await fetch('API/facultyUI/classroom/save_submission_grade.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    });
    const data = await res.json();
    if (data.status !== 'success') throw new Error(data.message || 'Failed to save');
    Swal.fire({ icon:'success', title:`Saved score for ${studentName}`, timer:1200, showConfirmButton:false });
    await loadSubmissions(true);
  } catch (e) {
    Swal.fire({ icon:'error', title:'Could not save score', text:String(e.message || e) });
  }
}

function filterSubmissions() {
  applySubFilterSort();
}

function toggleSortMenu() {
  const menu = document.getElementById('subSortMenu');
  if (!menu) return;
  menu.classList.toggle('show');
}

function closeSortMenu() {
  const menu = document.getElementById('subSortMenu');
  if (menu) menu.classList.remove('show');
}

function getLastName(name) {
  const clean = String(name || '').trim();
  if (!clean) return '';
  if (clean.includes(',')) return clean.split(',')[0].trim().toLowerCase();
  const parts = clean.split(/\s+/).filter(Boolean);
  return (parts[parts.length - 1] || '').toLowerCase();
}

function getStudentNoValue(studentNo) {
  const raw = String(studentNo || '');
  const digits = raw.replace(/\D/g, '');
  if (!digits) return Number.MAX_SAFE_INTEGER;
  const n = Number(digits);
  return Number.isFinite(n) ? n : Number.MAX_SAFE_INTEGER;
}

function compareSubs(a, b) {
  const aLive = Number(a?.live_score ?? NaN);
  const bLive = Number(b?.live_score ?? NaN);
  const aFinal = Number(a?.final_score ?? NaN);
  const bFinal = Number(b?.final_score ?? NaN);
  const aScore = Number.isFinite(aLive) ? aLive : (Number.isFinite(aFinal) ? aFinal : -1);
  const bScore = Number.isFinite(bLive) ? bLive : (Number.isFinite(bFinal) ? bFinal : -1);

  if (subSortMode === 'last_za') {
    const ln = getLastName(b.student_name).localeCompare(getLastName(a.student_name), undefined, { sensitivity: 'base' });
    return ln || String(b.student_name || '').localeCompare(String(a.student_name || ''), undefined, { sensitivity: 'base' });
  }
  if (subSortMode === 'score_desc') {
    if (bScore !== aScore) return bScore - aScore;
    const ln = getLastName(a.student_name).localeCompare(getLastName(b.student_name), undefined, { sensitivity: 'base' });
    return ln || String(a.student_name || '').localeCompare(String(b.student_name || ''), undefined, { sensitivity: 'base' });
  }
  if (subSortMode === 'score_asc') {
    if (aScore !== bScore) return aScore - bScore;
    const ln = getLastName(a.student_name).localeCompare(getLastName(b.student_name), undefined, { sensitivity: 'base' });
    return ln || String(a.student_name || '').localeCompare(String(b.student_name || ''), undefined, { sensitivity: 'base' });
  }
  const ln = getLastName(a.student_name).localeCompare(getLastName(b.student_name), undefined, { sensitivity: 'base' });
  return ln || String(a.student_name || '').localeCompare(String(b.student_name || ''), undefined, { sensitivity: 'base' });
}

function applySubFilterSort() {
  const q = (document.getElementById('subSearch')?.value || '').toLowerCase().trim();
  const filtered = (allSubs || []).filter(r => {
    const name = String(r.student_name || '').toLowerCase();
    const snum = String(r.student_number || '').toLowerCase();
    return !q || name.includes(q) || snum.includes(q);
  });
  filtered.sort(compareSubs);
  renderSubTable(filtered);
}

function setSubSort(mode) {
  subSortMode = mode;
  document.querySelectorAll('.pm-sort-item').forEach(btn => {
    const active = btn.dataset.sort === mode;
    btn.classList.toggle('active', active);
    const icon = btn.querySelector('i');
    if (icon) icon.style.opacity = active ? '1' : '0';
  });
  closeSortMenu();
  applySubFilterSort();
}

function toggleQDrop(i) {
  const drop = document.getElementById(`qdrop_${i}`);
  const chev = document.getElementById(`qchev_${i}`);
  if (!drop || !chev) return;
  const show = !drop.classList.contains('show');
  if (show) {
    drop.classList.add('show');
    drop.style.maxHeight = drop.scrollHeight + 'px';
  } else {
    drop.style.maxHeight = drop.scrollHeight + 'px';
    requestAnimationFrame(() => {
      drop.style.maxHeight = '0px';
      drop.classList.remove('show');
    });
  }
  chev.className = show ? 'fa-solid fa-chevron-up pm-q-chevron' : 'fa-solid fa-chevron-down pm-q-chevron';
}

function renderSubmissionLiveControls(p) {
  const host = document.getElementById('subLiveControls');
  if (!host) return;
  const isQuiz = Number(p?.type_has_quiz || 0) === 1;
  if (!isQuiz) {
    host.style.display = 'none';
    host.innerHTML = '';
    return;
  }
  const mode = getQuizMode(p);
  const isLiveMode = mode === 'live';

  const isPublished = Number(p?.is_published || 0) === 1;
  const liveStarted = !!p?.live_started_at;
  const liveEnded = !!p?.live_ended_at || Number(p?.is_force_closed || 0) === 1;
  const liveOpen = Number(p?.is_force_open || 0) === 1;
  const released = !!p?.results_released_at;
  const now = Date.now();
  const openAtMs = p?.open_at ? new Date(String(p.open_at).replace(' ', 'T')).getTime() : null;
  const closeAtMs = p?.close_at ? new Date(String(p.close_at).replace(' ', 'T')).getTime() : null;
  const dueIsWindowOpen = isPublished && Number.isFinite(openAtMs) && Number.isFinite(closeAtMs) && now >= openAtMs && now <= closeAtMs && !liveEnded;
  const dueIsWindowScheduled = isPublished && Number.isFinite(openAtMs) && now < openAtMs && !liveEnded;
  const dueIsWindowClosedByTime = isPublished && Number.isFinite(closeAtMs) && now > closeAtMs;

  let stateKey = 'draft';
  if (isLiveMode) {
    if (released) stateKey = 'released';
    else if (liveEnded) stateKey = 'ended';
    else if (liveStarted && liveOpen) stateKey = 'live';
    else if (liveStarted && !liveOpen) stateKey = 'paused';
    else if (isPublished) stateKey = 'published';
  } else {
    if (released) stateKey = 'released';
    else if (liveEnded || dueIsWindowClosedByTime) stateKey = 'ended';
    else if (dueIsWindowOpen) stateKey = 'live';
    else if (dueIsWindowScheduled) stateKey = 'published';
    else if (isPublished) stateKey = 'published';
  }

  const stateMap = {
    draft:     { icon:'fa-circle-half-stroke', label:'Draft', cls:'pm-ctrl-state-draft', hint:'Quiz is unpublished and only visible to faculty.' },
    published: { icon:'fa-circle-check', label:'Published', cls:'pm-ctrl-state-published', hint:'Students can see the quiz in their feed. Start it when ready.' },
    live:      { icon:'', label:'Live', cls:'pm-ctrl-state-live', hint:'Quiz is live. Students are answering now.' },
    paused:    { icon:'fa-pause', label:'Paused', cls:'pm-ctrl-state-ended', hint:'Quiz is paused. Student timers are frozen.' },
    ended:     { icon:'fa-lock', label:'Ended', cls:'pm-ctrl-state-ended', hint:'Quiz is closed. Release results when ready.' },
    released:  { icon:'fa-flag-checkered', label:'Results released', cls:'pm-ctrl-state-released', hint:'Results are already visible to students.' }
  };
  const st = stateMap[stateKey] || stateMap.draft;
  const stateIconHtml = stateKey === 'live'
    ? `<span class="pm-ctrl-live-dot"></span>`
    : `<i class="fa-solid ${st.icon}" aria-hidden="true"></i>`;
  const statePill = `<span class="pm-ctrl-state-pill ${st.cls}">${stateIconHtml} ${st.label}</span>`;

  let actions = '';
  if (stateKey === 'draft') {
    actions = pendingPublishTimer
      ? `<button class="pm-ctrl-btn" style="border-color:#f8c268;color:#b45309;background:#fff8e8;" onclick="pmCtrlDo('undo_publish')"><i class="fa-solid fa-rotate-left"></i> Undo (${Math.max(0, Math.ceil((pendingPublishEndsAt - Date.now()) / 1000))}s)</button>`
      : `<button class="pm-ctrl-btn primary" onclick="pmCtrlDo('publish')"><i class="fa-solid fa-paper-plane"></i> Publish</button>`;
    if (!isLiveMode) {
      actions += `<button class="pm-ctrl-btn" onclick="pmCtrlDo('set_schedule')"><i class="fa-solid fa-calendar-days"></i> Set schedule</button>`;
    }
  } else if (isLiveMode && stateKey === 'published') {
    actions = `<button class="pm-ctrl-btn primary" onclick="pmCtrlDo('start')"><i class="fa-solid fa-play"></i> Start quiz</button><span class="pm-ctrl-divider"></span><button class="pm-ctrl-btn ghost" onclick="pmCtrlDo('confirm_unpublish')"><i class="fa-solid fa-eye-slash"></i> Unpublish</button>`;
  } else if (isLiveMode && stateKey === 'live') {
    actions = `<button class="pm-ctrl-btn" onclick="pmCtrlDo('pause')"><i class="fa-solid fa-pause"></i> Pause</button><button class="pm-ctrl-btn danger" onclick="pmCtrlDo('end')"><i class="fa-solid fa-stop"></i> End quiz</button>`;
  } else if (isLiveMode && stateKey === 'paused') {
    actions = `<button class="pm-ctrl-btn primary" onclick="pmCtrlDo('resume')"><i class="fa-solid fa-play"></i> Resume</button><button class="pm-ctrl-btn danger" onclick="pmCtrlDo('end')"><i class="fa-solid fa-stop"></i> End quiz</button>`;
  } else if (stateKey === 'ended') {
    const releaseBtn = pendingReleaseTimer
      ? `<button class="pm-ctrl-btn" style="border-color:#f8c268;color:#b45309;background:#fff8e8;" onclick="pmCtrlDo('undo_release')"><i class="fa-solid fa-rotate-left"></i> Undo (${Math.max(0, Math.ceil((pendingReleaseEndsAt - Date.now()) / 1000))}s)</button>`
      : `<button class="pm-ctrl-btn primary" onclick="pmCtrlDo('release_results')"><i class="fa-solid fa-chart-line"></i> Release results</button>`;
    actions = `${releaseBtn}<span class="pm-ctrl-divider"></span><button class="pm-ctrl-btn ghost" onclick="pmCtrlDo('confirm_unpublish')"><i class="fa-solid fa-eye-slash"></i> Unpublish</button>`;
  } else if (!isLiveMode && (stateKey === 'published' || stateKey === 'live')) {
    const windowLabel = (Number.isFinite(openAtMs) && Number.isFinite(closeAtMs))
      ? `<span style="font-size:12px;color:var(--text-muted);">${fmtDt(p.open_at)} to ${fmtDt(p.close_at)}</span>`
      : '';
    actions = `
      ${windowLabel}
      <button class="pm-ctrl-btn danger" onclick="pmCtrlDo('force_close')"><i class="fa-solid fa-lock"></i> Close now</button>
      <button class="pm-ctrl-btn" onclick="pmCtrlDo('set_schedule')"><i class="fa-solid fa-calendar-days"></i> Schedule</button>
      <button class="pm-ctrl-btn ghost" onclick="pmCtrlDo('confirm_unpublish')"><i class="fa-solid fa-eye-slash"></i> Unpublish</button>`;
  } else if (stateKey === 'released') {
    actions = `<span style="font-size:13px;color:var(--text-muted);display:flex;align-items:center;gap:6px"><i class="fa-solid fa-circle-check" style="font-size:14px;color:#1d9e75"></i>All done</span>`;
  }

  host.style.display = 'block';
  host.innerHTML = `
    <div class="pm-ctrl-wrap">
      <div class="pm-ctrl-bar">
        ${statePill}
        <span class="pm-ctrl-divider"></span>
        ${actions}
        <span class="pm-ctrl-spacer"></span>
      </div>
      <div class="pm-ctrl-confirm" id="pmCtrlConfirmBar">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true" style="font-size:15px;color:#ba7517;flex-shrink:0"></i>
        <p id="pmCtrlConfirmMsg"><strong>Unpublish quiz?</strong> This will reset joined students, attempts, and scores.</p>
        <button class="pm-ctrl-btn danger" id="pmCtrlConfirmYes" onclick="pmCtrlConfirmAction()"><i class="fa-solid fa-rotate-left"></i> Yes, unpublish</button>
        <button class="pm-ctrl-btn ghost" onclick="pmCtrlCancelConfirm()">Cancel</button>
      </div>
      <p class="pm-ctrl-hint">${esc(st.hint)}</p>
    </div>`;
}

function pmCtrlDo(action) {
  pmCtrlCancelConfirm();
  if (action === 'confirm_unpublish') {
    pendingCtrlState = 'unpublish';
    const bar = document.getElementById('pmCtrlConfirmBar');
    if (bar) bar.classList.add('show');
    return;
  }
  if (action === 'undo_publish') return cancelPendingPublish();
  if (action === 'undo_release') return cancelPendingReleaseResults();
  if (action === 'set_schedule') return ensureDueSchedule();
  if (action === 'publish') return startPublishFlow();
  if (action === 'release_results') return quizAction('release_results');
  if (action === 'force_close') return quizAction('force_close');
  if (action === 'start' || action === 'pause' || action === 'resume' || action === 'end') return quizAction(action);
}

function pmCtrlConfirmAction() {
  if (pendingCtrlState === 'unpublish') {
    pendingCtrlState = null;
    pmCtrlCancelConfirm();
    return unpublishQuiz();
  }
  pmCtrlCancelConfirm();
}

function pmCtrlCancelConfirm() {
  pendingCtrlState = null;
  const bar = document.getElementById('pmCtrlConfirmBar');
  if (bar) bar.classList.remove('show');
}

/* ── DETAIL MODAL ── */
function openDetail(r) {
  document.getElementById('detailTitle').textContent = r.student_name || 'Student';
  document.getElementById('detailSub').textContent   = r.student_number || '—';
  const scoreHtml = (r.final_score !== null && r.final_score !== undefined)
    ? `<div class="pm-detail-score"><span>Score</span><strong>${esc(fmtWhole(r.final_score))}${r.max_score !== null ? ` / ${esc(fmtWhole(r.max_score))}` : ''} pts</strong></div>`
    : '<p style="color:#999;font-size:.85rem;margin-bottom:.75rem;">No score recorded yet.</p>';
  const statusMap = { in_progress:'<span class="pm-chip pm-chip-prog">In Progress</span>', submitted:'<span class="pm-chip pm-chip-done">Submitted</span>', graded:'<span class="pm-chip pm-chip-done">Graded</span>' };
  const statusHtml = statusMap[r.status] || '<span class="pm-chip pm-chip-wait">Not Submitted</span>';
  document.getElementById('detailBody').innerHTML = `
    <div style="margin-bottom:.75rem;">${statusHtml}</div>
    ${scoreHtml}
    <div class="pm-detail-row"><span>Student #</span><strong>${esc(r.student_number || '—')}</strong></div>
    <div class="pm-detail-row"><span>Submitted At</span><strong>${fmtDt(r.submitted_at)}</strong></div>`;
  document.getElementById('detailOverlay').classList.add('show');
  document.getElementById('detailModal').classList.add('show');
}
function closeDetail() {
  document.getElementById('detailOverlay').classList.remove('show');
  document.getElementById('detailModal').classList.remove('show');
}

function startSubAutoRefresh() {
  if (subAutoRefreshTimer) return;
  subAutoRefreshTimer = setInterval(() => {
    if (document.visibilityState !== 'visible') return;
    if (currentTab !== 'submissions') return;
    if (subActionBusy || pendingPublishTimer || pendingReleaseTimer || pendingCtrlState) return;
    // Silent refresh to avoid table flicker.
    loadSubmissions(false);
  }, SUB_AUTO_REFRESH_MS);
}

function stopSubAutoRefresh() {
  if (!subAutoRefreshTimer) return;
  clearInterval(subAutoRefreshTimer);
  subAutoRefreshTimer = null;
}

document.addEventListener('visibilitychange', function() {
  if (document.visibilityState === 'visible' && currentTab === 'submissions') {
    if (subActionBusy || pendingPublishTimer || pendingReleaseTimer || pendingCtrlState) return;
    loadSubmissions(false);
  }
});
window.addEventListener('focus', function() {
  if (currentTab === 'submissions') {
    if (subActionBusy || pendingPublishTimer || pendingReleaseTimer || pendingCtrlState) return;
    loadSubmissions(false);
  }
});

document.addEventListener('click', function(e) {
  const wrap = document.querySelector('.pm-sort-wrap');
  if (!wrap) return;
  if (!wrap.contains(e.target)) closeSortMenu();
});

/* ── ANALYTICS ── */
async function renderAnalytics() {
  const loading = document.getElementById('anLoading');
  const content = document.getElementById('anContent');
  loading.style.display = 'flex'; content.style.display = 'none';
  try {
    const j = Object.keys(allData).length ? allData : await fetchPost();
    allData = j;
    const s = j.summary || {};
    const subs = j.submissions || [];

    const enrolled  = Number(s.enrolled_count   || 0);
    const submitted = Number(s.submission_count || 0);
    const graded    = Number(s.graded_count     || 0);
    const waiting   = Math.max(0, enrolled - submitted);
    const pct       = enrolled ? Math.round((submitted / enrolled) * 100) : 0;
    const scores    = subs.map(r => r.final_score).filter(x => x !== null && x !== undefined).map(Number);
    const avg       = scores.length ? (scores.reduce((a,b)=>a+b,0)/scores.length).toFixed(1) : '—';
    const highest   = scores.length ? Math.max(...scores).toFixed(1) : '—';
    const lowest    = scores.length ? Math.min(...scores).toFixed(1) : '—';

    const doneW = enrolled ? (submitted/enrolled*100).toFixed(1) : 0;
    const waitW = enrolled ? (waiting/enrolled*100).toFixed(1)   : 100;

    content.innerHTML = `
      <div class="pm-an-wrap">
        <div class="pm-an-engage">
          <div class="pm-an-engage-header">
            <div class="pm-an-engage-title"><i class="fa-solid fa-users" style="color:var(--primary);"></i> &nbsp;Submission Progress</div>
            <div class="pm-an-engage-pct">${pct}%<span>submitted</span></div>
          </div>
          <div class="pm-an-bar">
            <div class="pm-an-bar-seg pm-an-bar-seg--done" style="width:${doneW}%;"></div>
            <div class="pm-an-bar-seg pm-an-bar-seg--gray" style="width:${waitW}%;"></div>
          </div>
          <div class="pm-an-legend">
            <div class="pm-an-legend-item"><div class="pm-an-legend-dot" style="background:#1a9e78;"></div> Submitted — <strong>${submitted}</strong></div>
            <div class="pm-an-legend-item"><div class="pm-an-legend-dot" style="background:#cbd5e1;"></div> Not Submitted — <strong>${waiting}</strong></div>
            <div class="pm-an-legend-item" style="margin-left:auto;"><i class="fa-solid fa-users" style="font-size:.72rem;"></i> Total: <strong>${enrolled}</strong></div>
          </div>
        </div>
        <div class="pm-an-stats-row">
          <div class="pm-an-stat-block">
            <div class="pm-an-stat-block-title"><i class="fa-solid fa-chart-bar" style="color:var(--primary);"></i> Participation</div>
            <div class="pm-an-stat-row"><span><i class="fa-solid fa-users"></i> Total Enrolled</span><strong style="font-size:1.1rem;">${enrolled}</strong></div>
            <div class="pm-an-stat-row"><span><i class="fa-solid fa-check-circle" style="color:#1a9e78;"></i> Submitted</span><strong style="color:#1a9e78;">${submitted}</strong></div>
            <div class="pm-an-stat-row"><span><i class="fa-solid fa-check-double" style="color:#f59e0b;"></i> Graded</span><strong style="color:#f59e0b;">${graded}</strong></div>
            <div class="pm-an-stat-row"><span><i class="fa-solid fa-clock" style="color:var(--text-muted);"></i> Not Submitted</span><strong style="color:var(--text-muted);">${waiting}</strong></div>
          </div>
          <div class="pm-an-stat-block">
            <div class="pm-an-stat-block-title"><i class="fa-solid fa-star" style="color:#f59e0b;"></i> Scores</div>
            <div class="pm-an-stat-row"><span><i class="fa-solid fa-calculator"></i> Average</span><strong style="font-size:1.1rem;">${avg}</strong></div>
            <div class="pm-an-stat-row"><span><i class="fa-solid fa-arrow-up" style="color:#1a9e78;"></i> Highest</span><strong style="color:#1a9e78;">${highest}</strong></div>
            <div class="pm-an-stat-row"><span><i class="fa-solid fa-arrow-down" style="color:#d93025;"></i> Lowest</span><strong style="color:#d93025;">${lowest}</strong></div>
            <div class="pm-an-stat-row"><span><i class="fa-solid fa-file-alt"></i> Scored</span><strong>${scores.length}</strong></div>
          </div>
        </div>
      </div>`;

    loading.style.display = 'none'; content.style.display = 'block';
  } catch(e) {
    loading.innerHTML = '<span style="color:#d93025;font-size:.85rem;">Failed to load analytics.</span>';
  }
}

/* ── BOOT ── */
(async function bootPostManage() {
  try {
    allData = await fetchPost();
    updateHero(allData);
    await switchToDefaultTab();
  } catch (e) {
    switchTab('submissions');
  }
})();
</script>
</body>
</html>

