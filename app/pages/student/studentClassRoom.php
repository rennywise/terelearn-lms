<?php
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();

if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php');
    exit;
}

$class_id = trim($_GET['class_id'] ?? '');

if ($class_id === '') {
    header('Location: ' . TERELEARN_BASE_URL . 'student.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TERELEARN - Student Classroom</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
:root {
    --primary: #795548;
    --primary-dark: #654337;
    --primary-light: #f0e7e2;
    --primary-mid: rgba(121,85,72,.16);
    --accent: #a98f85;
    --accent-light: #f3ebe7;
    --danger: #d93025;
    --warning: #f57c00;
    --border: #ded1ca;
    --text: #3f302b;
    --text-muted: #9b7b72;
    --bg: #eee8e5;
    --surface: #ffffff;
    --nav-h: 60px;
    --radius: 14px;
    --radius-sm: 8px;
    --shadow: 0 2px 8px rgba(68,45,36,.09);
    --shadow-md: 0 8px 22px rgba(68,45,36,.12);
    --shadow-lg: 0 16px 34px rgba(68,45,36,.15);
    --trans: .22s cubic-bezier(.4,0,.2,1);
    --trans-fast: .16s cubic-bezier(.4,0,.2,1);
    --trans-smooth: .28s cubic-bezier(.2,.8,.2,1);
}

body.dark {
    --primary: #c7a99c;
    --primary-dark: #a98f85;
    --primary-light: rgba(199,169,156,.14);
    --primary-mid: rgba(199,169,156,.10);
    --accent: #fbd990;
    --accent-light: rgba(251,217,144,.13);
    --border: #3d332f;
    --text: #f6eee9;
    --text-muted: #c1aaa1;
    --bg: #1b1411;
    --surface: #261d19;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    background-image:
        radial-gradient(circle at 8% 0%, rgba(250,203,140,.12), transparent 28%),
        linear-gradient(180deg, rgba(255,255,255,.52), transparent 220px);
    color: var(--text);
    transition: background var(--trans), color var(--trans);
    overflow-x: hidden;
}

button,
input,
textarea {
    font-family: inherit;
}

a,
button,
select,
input,
textarea,
.post-card,
.side-card,
.resource-card,
.pa-chip,
.pc-type-badge,
.tab-btn,
.student-pill,
.banner-chip,
.lesson-period-filter-option,
.submission-bar,
.sq-state,
.sq-btn {
    transition:
        background-color var(--trans-smooth),
        border-color var(--trans-smooth),
        box-shadow var(--trans-smooth),
        color var(--trans-smooth),
        opacity var(--trans-smooth),
        transform var(--trans-smooth),
        filter var(--trans-smooth);
}

.swal2-container {
    z-index: 99999 !important;
}

/* â•â• STUDENT QUIZ ENROLLMENT STATES â•â• */
@keyframes sq-pulse{0%,100%{transform:scale(1);opacity:1;}50%{transform:scale(.85);opacity:.6;}}
@keyframes sq-fade{from{opacity:0;transform:translateY(4px);}to{opacity:1;transform:translateY(0);}}
@keyframes softFade{from{opacity:0;}to{opacity:1;}}
@keyframes modalRise{from{opacity:0;transform:translateY(14px) scale(.98);}to{opacity:1;transform:translateY(0) scale(1);}}
@keyframes cardIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}

.sq-state{
  display:flex;align-items:center;gap:.7rem;
  padding:.7rem .9rem;border-radius:12px;
  font-size:.82rem;font-weight:600;margin-top:.6rem;
  animation:sq-fade .28s var(--trans-smooth);
}
.sq-state .sq-ico{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.78rem;flex-shrink:0;}
.sq-state .sq-text{flex:1;line-height:1.35;}
.sq-state .sq-text small{display:block;font-weight:500;font-size:.72rem;opacity:.85;margin-top:.1rem;}

.sq-not-enrolled{background:var(--accent-light);color:var(--accent);border:1px solid rgba(31,115,219,.25);}
.sq-not-enrolled .sq-ico{background:var(--accent);color:#fff;}

.sq-enrolled-waiting{background:#fff8e8;color:#92400e;border:1px solid #fcd34d;}
.sq-enrolled-waiting .sq-ico{background:#f59e0b;color:#fff;}
.sq-enrolled-waiting .sq-dot{width:8px;height:8px;border-radius:50%;background:#f59e0b;animation:sq-pulse 1.6s ease-in-out infinite;display:inline-block;margin-right:.4rem;}

.sq-live-now{background:#fdecea;color:var(--danger);border:1px solid #f5c2c7;}
.sq-live-now .sq-ico{background:var(--danger);color:#fff;}
.sq-live-now .sq-dot{width:8px;height:8px;border-radius:50%;background:var(--danger);animation:sq-pulse 1.1s ease-in-out infinite;display:inline-block;margin-right:.4rem;}

.sq-due{background:var(--primary-light);color:var(--primary);border:1px solid rgba(121,85,72,.25);}
.sq-due .sq-ico{background:var(--primary);color:#fff;}

.sq-closed{background:var(--bg);color:var(--text-muted);border:1px solid var(--border);}
.sq-closed .sq-ico{background:var(--text-muted);color:#fff;}

.sq-actions{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.6rem;justify-content:flex-end;}
.sq-slot{padding:0 1.1rem 1rem;}
.quiz-info-row{
  display:flex;
  flex-wrap:wrap;
  gap:.45rem;
  align-items:center;
  margin:.45rem 0 .65rem;
}
.quiz-info-chip{
  display:inline-flex;
  align-items:center;
  gap:.35rem;
  padding:.28rem .68rem;
  border-radius:999px;
  background:var(--bg);
  border:1px solid var(--border);
  color:var(--text-muted);
  font-size:.76rem;
  font-weight:800;
  line-height:1;
}
.quiz-info-chip i{font-size:.72rem;color:var(--primary);}
.quiz-info-chip.is-live{
  background:#fdecea;
  color:var(--danger);
  border-color:#f5c2c7;
}
.quiz-info-chip.is-live i{color:var(--danger);}
.sq-btn{
  display:inline-flex;align-items:center;justify-content:center;gap:.45rem;
  padding:.55rem 1.05rem;border-radius:10px;
  font-family:inherit;font-size:.84rem;font-weight:700;
  border:none;cursor:pointer;
}
.sq-btn:disabled{opacity:.55;cursor:not-allowed;}
.sq-btn-primary{background:var(--primary);color:#fff;box-shadow:0 2px 10px rgba(121,85,72,.24);}
.sq-btn-primary:hover:not(:disabled){background:var(--primary-dark);transform:translateY(-2px);}
.sq-btn-view{font-size:1rem;padding:.82rem 1.55rem;border-radius:12px;box-shadow:0 4px 16px rgba(121,85,72,.28);}
.sq-btn-danger{background:var(--danger);color:#fff;box-shadow:0 2px 10px rgba(217,48,37,.3);}
.sq-btn-danger:hover:not(:disabled){background:#b1271c;transform:translateY(-2px);}
.sq-btn-ghost{background:var(--bg);color:var(--text-muted);border:1.5px solid var(--border);}
.sq-btn-ghost:hover:not(:disabled){background:#fdecea;color:var(--danger);border-color:#f5c2c7;}
.sq-btn-take{background:var(--primary);color:#fff;box-shadow:0 2px 12px rgba(121,85,72,.26);font-size:.92rem;padding:.7rem 1.3rem;}
.sq-btn-take:hover:not(:disabled){background:var(--primary-dark);transform:translateY(-2px);box-shadow:0 4px 16px rgba(121,85,72,.32);}

.topbar {
    position: fixed;
    inset: 0 0 auto 0;
    height: var(--nav-h);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: 0 1.4rem;
    z-index: 200;
    box-shadow: var(--shadow);
}

.back-btn,
.icon-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: none;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    transition: all var(--trans);
    text-decoration: none;
    flex-shrink: 0;
}

.back-btn:hover,
.icon-btn:hover {
    background: var(--border);
    color: var(--text);
    transform: translateY(-1px);
}

.topbar-brand {
    display: flex;
    align-items: center;
    gap: .55rem;
    font-size: .95rem;
    font-weight: 700;
    color: var(--text);
    text-decoration: none;
    white-space: nowrap;
}

.blogo {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg,var(--primary),var(--primary-dark));
    border-radius: 9px;
    color: #fff;
    font-size: .82rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.topbar-class-name {
    font-size: .9rem;
    font-weight: 600;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 340px;
}

.topbar-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: .35rem;
}

.student-pill {
    font-size: .76rem;
    font-weight: 800;
    color: var(--accent);
    background: var(--accent-light);
    border: 1px solid rgba(31,115,219,.25);
    border-radius: 20px;
    padding: .24rem .78rem;
    white-space: nowrap;
}

.class-banner {
    margin-top: var(--nav-h);
    height: 210px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
}

.banner-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg,#795548,#291304);
}

.banner-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(0,0,0,.28), rgba(0,0,0,.08)), rgba(0,0,0,.18);
    z-index: 1;
}

.banner-pattern {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    opacity: .16;
    overflow: hidden;
}

.banner-pattern-icon {
    position: absolute;
    left: var(--x);
    top: var(--y);
    width: var(--s);
    height: var(--s);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,.62);
    background: rgba(255,255,255,.11);
    border: 1px solid rgba(255,255,255,.12);
    transform: translate(-50%, -50%) rotate(var(--r,0deg));
    box-shadow: 0 12px 26px rgba(15,23,42,.08);
}

.banner-pattern-icon.is-ghost {
    background: transparent;
    border-color: transparent;
    color: rgba(255,255,255,.38);
}

.banner-pattern-icon i {
    font-size: calc(var(--s) * .42);
    line-height: 1;
}

.banner-content {
    position: relative;
    z-index: 2;
    padding: 1.65rem 2rem;
    color: #fff;
    width: 100%;
}

.banner-row {
    display: flex;
    align-items: center;
    gap: 1.65rem;
    width: 100%;
}

.banner-main {
    min-width: 0;
    flex: 1;
}

.banner-title {
    font-size: clamp(1.55rem, 2.4vw, 2.05rem);
    font-weight: 800;
    line-height: 1.2;
}

.banner-sub {
    font-size: .95rem;
    opacity: .85;
    margin-top: .3rem;
}

.banner-chips {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .75rem;
}

.banner-chip {
    font-size: .74rem;
    font-weight: 700;
    padding: .24rem .68rem;
    border-radius: 20px;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(6px);
    color: #fff;
    display: flex;
    align-items: center;
    gap: .3rem;
}

.faculty-identity {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 136px;
    flex: 0 0 136px;
    color: #fff;
    text-align: center;
}

.faculty-avatar {
    width: 94px;
    height: 94px;
    border-radius: 50%;
    background: rgba(255,255,255,.18);
    border: 4px solid rgba(255,255,255,.55);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    font-weight: 800;
    overflow: hidden;
    box-shadow: 0 14px 34px rgba(0,0,0,.16);
}

.faculty-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.faculty-name {
    font-size: .86rem;
    font-weight: 800;
    line-height: 1.18;
    margin-top: .55rem;
    max-width: 100%;
}

.faculty-role {
    font-size: .76rem;
    font-weight: 800;
    line-height: 1.25;
    margin-top: .28rem;
    color: rgba(255,255,255,.7);
}

.tabs-bar {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    position: sticky;
    top: var(--nav-h);
    z-index: 100;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.tab-btn {
    padding: 1rem 1.25rem;
    border: none;
    background: none;
    font-size: .88rem;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all var(--trans);
    white-space: nowrap;
}

.tab-btn:hover,
.tab-btn.active {
    color: var(--primary);
}

.tab-btn:hover {
    transform: translateY(-1px);
}

.tab-btn.active {
    border-bottom-color: var(--primary);
}

.tab-count {
    display: inline-block;
    background: var(--primary);
    color: #fff;
    font-size: .62rem;
    font-weight: 700;
    padding: .08rem .38rem;
    border-radius: 20px;
    margin-left: .3rem;
}

.tab-section {
    display: none;
}

.tab-section.active {
    display: block;
}

.cr-layout {
    max-width: 1120px;
    margin: 0 auto;
    padding: 1.75rem 1.5rem;
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1.65rem;
}

.cr-main,
.cr-side {
    min-width: 0;
}

.cr-side {
    position: sticky;
    top: calc(var(--nav-h) + 78px);
    align-self: start;
}

.side-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.05rem 1.15rem;
    box-shadow: var(--shadow);
    margin-bottom: 1rem;
}

.side-card:hover {
    border-color: rgba(121,85,72,.18);
    box-shadow: var(--shadow-md);
}

.side-card-title {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: var(--text-muted);
    margin-bottom: .85rem;
    display: flex;
    align-items: center;
    gap: .42rem;
}

.meet-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    background: #1a73e8;
    color: #fff;
    border-radius: 8px;
    padding: .6rem;
    font-size: .82rem;
    font-weight: 700;
    text-decoration: none;
    margin-bottom: .6rem;
}

.meet-btn:hover {
    background: #1667d2;
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(26,115,232,.18);
}

.info-list {
    font-size: .82rem;
    line-height: 1.9;
    color: var(--text-muted);
}

.filter-row {
    display: flex;
    gap: .55rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: var(--shadow);
    padding: .8rem;
}

.cw-filter-wrap{
    display:flex;
    gap:.55rem;
    align-items:center;
    flex-wrap:wrap;
    width:100%;
}
.cw-filter-search{
    flex:1 1 auto;
    min-width:0;
    height:42px;
    border:1.5px solid var(--border);
    border-radius:12px;
    background:var(--surface);
    color:var(--text);
    font-size:.84rem;
    font-weight:600;
    padding:0 .85rem;
}
.cw-filter-search:focus,
.cw-filter-select:focus{
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 3px var(--primary-mid);
}
.cw-filter-label{
    font-size:.78rem;
    font-weight:800;
    color:var(--text-muted);
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    white-space:nowrap;
}
.cw-filter-select{
    flex:0 0 40%;
    min-width:130px;
    max-width:180px;
    height:42px;
    border:1.5px solid var(--border);
    border-radius:12px;
    background:var(--surface);
    color:var(--text);
    font-size:.84rem;
    font-weight:700;
    padding:0 .7rem;
}
.cw-filter-search:hover,
.cw-filter-select:hover{
    border-color:rgba(121,85,72,.35);
    box-shadow:0 6px 18px rgba(15,23,42,.06);
}
.lesson-period-filter{
    display:flex;
    align-items:center;
    gap:.42rem;
    flex-wrap:wrap;
    width:100%;
    margin-top:.25rem;
    padding-top:.55rem;
    border-top:1px solid var(--border);
    max-height:0;
    opacity:0;
    overflow:hidden;
    pointer-events:none;
    transform:translateY(-6px);
    transition:max-height var(--trans-smooth), opacity var(--trans-smooth), transform var(--trans-smooth), padding-top var(--trans-smooth), margin-top var(--trans-smooth);
}
.lesson-period-filter.show{
    max-height:80px;
    opacity:1;
    pointer-events:auto;
    transform:translateY(0);
}
.lesson-period-filter-label{
    font-size:.72rem;
    font-weight:800;
    color:var(--text-muted);
    text-transform:uppercase;
    letter-spacing:.45px;
    margin-right:.16rem;
}
.lesson-period-filter-option{
    display:inline-flex;
    align-items:center;
    gap:.32rem;
    padding:.34rem .68rem;
    border:1.5px solid var(--border);
    border-radius:999px;
    background:var(--surface);
    color:var(--text-muted);
    font-size:.72rem;
    font-weight:700;
    cursor:pointer;
    transition:all var(--trans);
}
.lesson-period-filter-option:has(input:checked){
    border-color:var(--primary);
    background:var(--primary-light);
    color:var(--primary-dark);
    box-shadow:0 6px 16px rgba(121,85,72,.12);
    transform:translateY(-1px);
}
.lesson-period-filter-option input{
    accent-color:var(--primary);
    margin:0;
}
@media(max-width:600px){
    .filter-row{margin-bottom:.75rem;}
    .cw-filter-wrap{gap:.45rem;}
    .cw-filter-search{height:36px;font-size:.82rem;padding:0 .72rem;}
    .cw-filter-label{font-size:.74rem;}
    .cw-filter-select{height:36px;font-size:.8rem;min-width:118px;max-width:42%;}
}
@media(max-width:360px){
    .cw-filter-wrap{flex-wrap:wrap;}
    .cw-filter-search,.cw-filter-select{max-width:100%;width:100%;}
}

.filter-btn {
    padding: .45rem .9rem;
    border-radius: 10px;
    border: 1.5px solid var(--border);
    background: var(--surface);
    font-size: .82rem;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: all var(--trans);
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}

.filter-btn.active,
.filter-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--primary-light);
    transform: translateY(-1px);
}

#streamFeed {
    transition: opacity var(--trans-smooth), transform var(--trans-smooth), filter var(--trans-smooth);
}

#streamFeed.is-updating {
    opacity: 0;
    transform: translateY(8px);
    filter: blur(2px);
}

.post-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: var(--shadow);
    margin-bottom: 1rem;
    overflow: hidden;
    transition: box-shadow var(--trans), transform var(--trans), border-color var(--trans);
}

#streamFeed.is-animating .post-card {
    animation: cardIn .24s var(--trans-smooth);
}

.post-card:hover {
    border-color: rgba(121,85,72,.22);
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.post-card:active {
    transform: translateY(-1px);
}

.pc-head {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: .78rem;
    padding: .88rem 1.1rem .52rem;
}

.pc-avatar {
    width: 38px;
    height: 38px;
    border-radius: 14px;
    flex-shrink: 0;
    font-size: 1rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pc-meta {
    flex: 1;
    min-width: 0;
    text-align: left;
    align-self: center;
}

.pc-author {
    font-size: 1.06rem;
    font-weight: 800;
    line-height: 1.15;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: left;
}

.pc-date {
    font-size: .8rem;
    color: var(--text-muted);
    margin-top: .12rem;
    font-weight: 600;
    line-height: 1.2;
    text-align: left;
}

.pc-type-badge {
    font-size: .66rem;
    font-weight: 800;
    letter-spacing: .48px;
    padding: .2rem .62rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    text-transform: uppercase;
    white-space: nowrap;
}

.pc-type-badge.is-clickable {
    border: 0;
    cursor: pointer;
}

.pc-type-badge.is-clickable:hover {
    filter: brightness(.96);
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(15,23,42,.08);
}

.pc-body {
    padding: .45rem 1.1rem .95rem;
}

.pc-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: .35rem;
}

.pc-text {
    font-size: .9rem;
    line-height: 1.65;
    color: var(--text-muted);
    white-space: pre-wrap;
}

.pc-meta-row {
    display: flex;
    gap: .55rem;
    flex-wrap: wrap;
    margin-top: .75rem;
    font-size: .75rem;
    font-weight: 600;
    color: var(--text-muted);
}

.pc-meta-row span {
    display: inline-flex;
    align-items: center;
    gap: .34rem;
    padding: .22rem .58rem;
    border-radius: 999px;
    background: var(--bg);
    border: 1px solid var(--border);
}

.pc-attachments {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    padding: 0 1.1rem 1rem;
}

.pa-chip {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .48rem .78rem;
    border-radius: 11px;
    background: var(--bg);
    border: 1px solid var(--border);
    font-size: .78rem;
    font-weight: 600;
    color: var(--text);
    text-decoration: none;
    cursor: pointer;
    max-width: 100%;
    min-width: 0;
    transition: all var(--trans);
}

.pa-chip:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--primary-light);
    transform: translateY(-1px);
}

.pa-chip:active {
    transform: translateY(0);
}

.pa-chip-name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1 1 auto;
}

.pa-chip-eye {
    margin-left: auto;
    flex: 0 0 auto;
    color: var(--primary);
}

.quiz-preview {
    margin: 0 1.1rem 1rem;
    padding: .85rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
}

.quiz-q-item {
    margin-bottom: .75rem;
}

.quiz-q-text {
    font-weight: 700;
    font-size: .86rem;
    margin-bottom: .4rem;
}

.quiz-choices {
    display: grid;
    gap: .3rem;
}

.quiz-choice {
    font-size: .8rem;
    padding: .35rem .55rem;
    border-radius: 8px;
    background: var(--surface);
    border: 1px solid var(--border);
}

.sub-mode-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: .18rem .55rem;
    border-radius: 20px;
    margin: 0 1.1rem 1rem;
}

.sub-mode-individual {
    background: #e0f7fa;
    color: #00695c;
    border: 1px solid #b2dfdb;
}

.sub-mode-group {
    background: var(--primary-light);
    color: var(--primary);
    border: 1px solid var(--primary);
}

.pc-comments {
    border-top: 1px solid var(--border);
}

.pc-comments-toggle {
    width: 100%;
    padding: .75rem 1.1rem;
    border: none;
    background: none;
    color: var(--text-muted);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: .45rem;
    cursor: pointer;
}

.pc-comments-toggle:hover {
    background: var(--bg);
    color: var(--primary);
}

.pc-comments-body {
    display: none;
    padding: .2rem 1.1rem 1rem;
}

.pc-comments-toggle.open .cmt-arrow {
    margin-left: auto;
    transform: rotate(180deg);
}

.pc-comments-toggle .cmt-arrow {
    margin-left: auto;
    transition: transform var(--trans);
}

.pc-comments-body.show {
    display: block;
}

.comment-list {
    display: flex;
    flex-direction: column;
    gap: .45rem;
    margin-bottom: .65rem;
}

.comment-card {
    display: flex;
    gap: .55rem;
    align-items: flex-start;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: .55rem .65rem;
}

.comment-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg,var(--accent),var(--primary));
    color: #fff;
    font-size: .7rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.comment-main {
    flex: 1;
}

.comment-top {
    display: flex;
    align-items: center;
    gap: .4rem;
    margin-bottom: .12rem;
}

.comment-name {
    font-size: .78rem;
    font-weight: 700;
}

.comment-role {
    font-size: .6rem;
    font-weight: 800;
    color: var(--primary);
    text-transform: uppercase;
}

.comment-time {
    font-size: .68rem;
    color: var(--text-muted);
    margin-left: auto;
}

.comment-text {
    font-size: .82rem;
    line-height: 1.45;
}

.comment-del {
    border: none;
    background: none;
    color: var(--text-muted);
    cursor: pointer;
    border-radius: 6px;
    width: 24px;
    height: 24px;
}

.comment-del:hover {
    background: #fdecea;
    color: var(--danger);
}

.cmt-input-row {
    display: flex;
    gap: .5rem;
    align-items: flex-end;
}

.cmt-input {
    flex: 1;
    min-height: 38px;
    max-height: 90px;
    resize: none;
    border: 1.5px solid var(--border);
    background: var(--surface);
    color: var(--text);
    border-radius: 20px;
    padding: .55rem .85rem;
    outline: none;
}

.cmt-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-mid);
}

.cmt-send {
    width: 38px;
    height: 38px;
    border: none;
    border-radius: 50%;
    background: var(--primary);
    color: #fff;
    cursor: pointer;
}

/* Left comment panel (Classroom-like) */
.cmt-panel-overlay{display:none;position:fixed;inset:0;background:rgba(12,18,28,.45);z-index:2600;}
.cmt-panel-overlay.show{display:block;}
.cmt-panel{
  position:fixed;left:0;top:0;height:100vh;width:min(420px,92vw);
  background:var(--surface);border-right:1px solid var(--border);box-shadow:0 14px 44px rgba(0,0,0,.22);
  z-index:2601;display:flex;flex-direction:column;transform:translateX(-102%);transition:transform var(--trans-smooth), box-shadow var(--trans-smooth);
}
.cmt-panel.show{transform:translateX(0);}
.cmt-panel-head{display:flex;align-items:center;gap:.55rem;padding:.9rem 1rem;border-bottom:1px solid var(--border);background:var(--bg);}
.cmt-panel-title{font-size:.9rem;font-weight:800;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.cmt-panel-close{width:34px;height:34px;border:none;border-radius:9px;background:var(--surface);color:var(--text-muted);cursor:pointer;}
.cmt-panel-close:hover{background:#fdecea;color:var(--danger);}
.cmt-panel-body{flex:1;overflow:auto;padding:.8rem 1rem;}
.cmt-panel-foot{padding:.75rem 1rem;border-top:1px solid var(--border);background:var(--surface);}

/* Post details modal with right comments (Classroom-like) */
.post-modal-overlay{display:none;position:fixed;inset:0;background:rgba(4,10,20,.7);z-index:2700;align-items:center;justify-content:center;padding:1rem;animation:softFade var(--trans-smooth);}
.post-modal{width:min(1040px,96vw);max-height:90vh;background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:0 24px 80px rgba(0,0,0,.35);display:flex;flex-direction:column;overflow:hidden;animation:modalRise var(--trans-smooth);}
.post-modal-head{display:flex;align-items:center;gap:.6rem;padding:.85rem 1rem;border-bottom:1px solid var(--border);background:var(--bg);}
.post-modal-title{font-size:.95rem;font-weight:800;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.post-modal-close{width:34px;height:34px;border:none;border-radius:9px;background:var(--surface);color:var(--text-muted);cursor:pointer;}
.post-modal-close:hover{background:#fdecea;color:var(--danger);}
.post-modal-body{display:grid;grid-template-columns:1fr 340px;min-height:0;flex:1;}
.post-modal-main{padding:1rem;overflow:auto;}
.post-modal-side{border-left:1px solid var(--border);display:flex;flex-direction:column;min-height:0;background:var(--bg);}
.post-modal-comments{flex:1;overflow:auto;padding:.8rem;}
.post-modal-foot{padding:.7rem .8rem;border-top:1px solid var(--border);background:var(--surface);}
@media(max-width:920px){.post-modal-body{grid-template-columns:1fr;}.post-modal-side{border-left:none;border-top:1px solid var(--border);max-height:42vh;}}

.empty-feed {
    text-align: center;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 3rem 1rem;
    color: var(--text-muted);
}

.ef-icon {
    font-size: 2.6rem;
    opacity: .28;
    margin-bottom: .7rem;
}

.ef-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: .25rem;
}

.resource-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: var(--shadow);
    margin-bottom: 1rem;
    padding: 1rem 1.1rem;
    overflow: hidden;
    transition: box-shadow var(--trans), transform var(--trans), border-color var(--trans);
}

.resource-card:hover {
    border-color: rgba(121,85,72,.22);
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
}

.resource-card:active {
    transform: translateY(0);
}

.resource-title {
    font-size: .95rem;
    font-weight: 700;
    margin-bottom: .2rem;
}

.resource-meta {
    font-size: .72rem;
    color: var(--text-muted);
    margin-bottom: .75rem;
}

.resource-group-title {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: var(--text-muted);
    margin: 1.25rem 0 .5rem;
    padding-left: .25rem;
    border-left: 3px solid var(--primary);
    padding-left: .6rem;
}

.fv-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 2000;
    background: rgba(4,10,18,.88);
}

.fv-backdrop.show {
    display: flex;
}

.fv-shell {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    background: #0f1520;
}

.fv-toolbar {
    height: 48px;
    display: flex;
    align-items: center;
    gap: .45rem;
    padding: .45rem .7rem;
    background: #141b2d;
    border-bottom: 1px solid rgba(255,255,255,.08);
}

.fv-filename {
    flex: 1;
    font-size: .82rem;
    font-weight: 600;
    color: #c9d6e8;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.fv-badge {
    font-size: .6rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: .15rem .5rem;
    border-radius: 20px;
    background: rgba(255,255,255,.1);
    color: #8da0b8;
}

.fv-tb-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 7px;
    background: rgba(255,255,255,.07);
    color: #8da0b8;
    font-size: .85rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.fv-tb-btn:hover {
    background: rgba(255,255,255,.18);
    color: #fff;
}

.fv-tb-btn:disabled {
    opacity: .28;
    cursor: not-allowed;
}

.fv-panel-toggle-btn {
    display: flex;
    align-items: center;
    gap: .35rem;
    padding: .25rem .65rem;
    border: none;
    border-radius: 7px;
    background: rgba(255,255,255,.07);
    color: #8da0b8;
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
}

.fv-panel-toggle-btn:hover {
    background: rgba(255,255,255,.18);
    color: #fff;
}

.fv-layout {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

#fvBody {
    flex: 1;
    position: relative;
    background: #0f1520;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.fv-scroll {
    width: 100%;
    height: 100%;
    overflow: auto;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 1.25rem;
}

.fv-scroll canvas {
    border-radius: 3px;
    box-shadow: 0 6px 30px rgba(0,0,0,.5);
}

.fv-img-wrap,
.fv-media-wrap {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
}

.fv-img-wrap img,
.fv-media-wrap video,
.fv-media-wrap audio {
    max-width: 100%;
    max-height: 100%;
    border-radius: 8px;
}

.fv-iframe {
    width: 100%;
    height: 100%;
    border: none;
    background: #fff;
}

.fv-text-wrap {
    width: 100%;
    height: 100%;
    overflow: auto;
    padding: 1.5rem 2rem;
    color: #c9d6e8;
    font-family: 'DM Mono', monospace;
    font-size: .82rem;
    line-height: 1.7;
    white-space: pre-wrap;
    word-break: break-all;
}

.fv-loading {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .75rem;
    color: #6b7fa0;
    font-size: .85rem;
    pointer-events: none;
}

.fv-spinner {
    width: 36px;
    height: 36px;
    border: 3px solid rgba(255,255,255,.08);
    border-top-color: #795548;
    border-radius: 50%;
    animation: sp .7s linear infinite;
}

@keyframes sp {
    to { transform: rotate(360deg); }
}

.fv-side-panel {
    width: 320px;
    background: #111927;
    border-left: 1px solid rgba(255,255,255,.08);
    display: flex;
    flex-direction: column;
    transition: width .2s;
}

.fv-side-panel.collapsed {
    width: 0;
    overflow: hidden;
}

.fv-panel-head {
    height: 44px;
    display: flex;
    align-items: center;
    gap: .45rem;
    padding: 0 .8rem;
    background: #141b2d;
    border-bottom: 1px solid rgba(255,255,255,.08);
    color: #c9d6e8;
    font-weight: 700;
    font-size: .78rem;
}

.fv-panel-close {
    margin-left: auto;
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 6px;
    background: none;
    color: #8da0b8;
    cursor: pointer;
    font-size: .75rem;
}

.fv-panel-close:hover {
    background: rgba(255,255,255,.1);
    color: #fff;
}

.fv-panel-page-strip {
    display: flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .8rem;
    font-size: .72rem;
    color: #8da0b8;
    background: rgba(255,255,255,.02);
    border-bottom: 1px solid rgba(255,255,255,.05);
}

.fv-notes-pane {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
}

.fv-private-badge {
    margin-left: auto;
    font-size: .56rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: .08rem .36rem;
    border-radius: 20px;
    background: rgba(245,185,0,.13);
    color: #f5b900;
    border: 1px solid rgba(245,185,0,.22);
}

.fv-note-list {
    flex: 1;
    overflow-y: auto;
    padding: .65rem .75rem;
    display: flex;
    flex-direction: column;
    gap: .45rem;
}

.fv-empty-msg {
    text-align: center;
    padding: 2rem .5rem;
    color: #4f6179;
    font-size: .73rem;
    font-style: italic;
    line-height: 1.65;
}

.fv-item-card {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 8px;
    padding: .5rem .65rem;
    position: relative;
}

.fv-item-time {
    font-size: .6rem;
    color: #64758d;
    margin-bottom: .2rem;
}

.fv-item-text {
    font-size: .78rem;
    line-height: 1.55;
    color: #b8c7dc;
    white-space: pre-wrap;
    word-break: break-word;
}

.fv-item-del {
    position: absolute;
    top: .28rem;
    right: .32rem;
    width: 18px;
    height: 18px;
    border: none;
    background: none;
    cursor: pointer;
    color: #64758d;
    font-size: .6rem;
}

.fv-input-area {
    padding: .6rem .75rem;
    border-top: 1px solid rgba(255,255,255,.065);
}

.fv-input-hint {
    font-size: .63rem;
    color: #64758d;
    margin-bottom: .35rem;
}

.fv-input-row {
    display: flex;
    gap: .38rem;
    align-items: flex-end;
}

.fv-textarea {
    flex: 1;
    background: rgba(255,255,255,.048);
    border: 1.5px solid rgba(255,255,255,.085);
    border-radius: 11px;
    color: #c9d6e8;
    font-size: .76rem;
    padding: .35rem .6rem;
    resize: none;
    min-height: 32px;
    max-height: 88px;
    outline: none;
}

.fv-send-btn {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 50%;
    background: #795548;
    color: #fff;
    cursor: pointer;
    font-size: .7rem;
}

.fv-no-preview {
    text-align: center;
    color: #6b7fa0;
    padding: 2rem;
}

.fv-no-preview i {
    font-size: 3rem;
    opacity: .35;
    margin-bottom: 1rem;
    display: block;
}

.fv-no-preview h3 {
    color: #c9d6e8;
    margin-bottom: .5rem;
}

.fv-no-preview p {
    font-size: .82rem;
    margin-bottom: 1rem;
}

.fv-no-preview a {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .55rem 1.4rem;
    background: #795548;
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-size: .85rem;
    font-weight: 600;
}

.cmt-empty {
    font-size: .82rem;
    color: var(--text-muted);
    font-style: italic;
    text-align: center;
    padding: .75rem 0;
}

.submission-bar {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .75rem 1.1rem;
    border-top: 1px solid var(--border);
    font-size: .8rem;
    font-weight: 600;
}

.submission-bar.sb-none {
    background: var(--bg);
    color: var(--text-muted);
}

.submission-bar.sb-submitted {
    background: #f0e7e2;
    color: #795548;
    border-top-color: #d8cac4;
}

.submission-bar.sb-graded {
    background: #e8f0fe;
    color: #1f73db;
    border-top-color: #c5d9fb;
}

.submission-bar.sb-returned {
    background: #fff3e0;
    color: #f57c00;
    border-top-color: #ffe0b2;
}

body.dark .submission-bar.sb-none {
    background: rgba(255,255,255,.04);
    color: var(--text-muted);
}

body.dark .submission-bar.sb-submitted {
    background: rgba(199,169,156,.14);
    color: #c7a99c;
    border-top-color: rgba(199,169,156,.3);
}

body.dark .submission-bar.sb-graded {
    background: rgba(31,115,219,.12);
    color: #4d90e2;
    border-top-color: rgba(31,115,219,.3);
}

body.dark .submission-bar.sb-returned {
    background: rgba(245,124,0,.12);
    color: #ffb74d;
    border-top-color: rgba(245,124,0,.3);
}

.sb-icon {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .68rem;
    flex-shrink: 0;
}

.sb-submitted .sb-icon { background: #795548; color: #fff; }
.sb-graded .sb-icon   { background: #1f73db; color: #fff; }
.sb-returned .sb-icon  { background: #f57c00; color: #fff; }
.sb-none .sb-icon      { background: var(--border); color: var(--text-muted); }

.sb-grade-pill {
    margin-left: auto;
    font-size: .72rem;
    font-weight: 700;
    padding: .15rem .55rem;
    border-radius: 20px;
    background: rgba(31,115,219,.15);
    color: #1f73db;
}

body.dark .sb-grade-pill {
    background: rgba(77,144,226,.2);
    color: #4d90e2;
}

.sb-time {
    font-size: .7rem;
    font-weight: 400;
    color: inherit;
    opacity: .75;
}

.sb-file-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .18rem .65rem;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 600;
    text-decoration: none;
    background: rgba(255,255,255,.4);
    border: 1px solid rgba(0,0,0,.1);
    color: inherit;
    max-width: 190px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: opacity var(--trans);
    margin-left: auto;
    flex-shrink: 0;
}

.sb-file-chip:hover { opacity: .7; }

body.dark .sb-file-chip {
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.12);
}

.due-countdown {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    font-size: .72rem;
    font-weight: 700;
    padding: .16rem .55rem;
    border-radius: 20px;
    white-space: nowrap;
}

.due-countdown.cd-ok      { background: #f0e7e2; color: #795548; }
.due-countdown.cd-warn    { background: #fff8e1; color: #f9a825; }
.due-countdown.cd-urgent  { background: #fff3e0; color: #f57c00; }
.due-countdown.cd-overdue { background: #fce8e6; color: #d93025; }

body.dark .due-countdown.cd-ok      { background: rgba(199,169,156,.15);  color: #c7a99c; }
body.dark .due-countdown.cd-warn    { background: rgba(249,168,37,.12);  color: #ffd54f; }
body.dark .due-countdown.cd-urgent  { background: rgba(245,124,0,.12);   color: #ffb74d; }
body.dark .due-countdown.cd-overdue { background: rgba(217,48,37,.12);   color: #ef5350; }

.sb-submit-btn {
    margin-left: auto;
    padding: .28rem .85rem;
    font-size: .75rem;
    font-weight: 700;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    transition: background var(--trans), transform var(--trans);
    white-space: nowrap;
}

.sb-none .sb-submit-btn {
    background: var(--primary);
    color: #fff;
}

.sb-none .sb-submit-btn:hover { background: var(--primary-dark); transform: scale(1.03); }

.sb-returned .sb-submit-btn {
    background: #f57c00;
    color: #fff;
}

.sb-returned .sb-submit-btn:hover { background: #e65100; transform: scale(1.03); }

.sb-submitted .sb-submit-btn {
    background: #1f73db;
    color: #fff;
}

.sb-file-chip .sb-file-name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: inline-block;
}

.sb-file-chip .sb-file-eye {
    margin-left: auto;
    flex: 0 0 auto;
    color: inherit;
    opacity: .8;
}

.sb-submitted .sb-submit-btn:hover { background: #155bb0; transform: scale(1.03); }

.sub-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity var(--trans);
}

.sub-modal-overlay.open {
    opacity: 1;
    pointer-events: all;
}

.sub-modal {
    background: var(--surface);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    width: 100%;
    max-width: 520px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: translateY(18px) scale(.97);
    transition: transform var(--trans);
}

.sub-modal-overlay.open .sub-modal { transform: none; }

.sub-modal-head {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: 1.1rem 1.4rem;
    border-bottom: 1px solid var(--border);
}

.sub-modal-head h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    flex: 1;
}

.sub-modal-close {
    background: none;
    border: none;
    font-size: 1.1rem;
    color: var(--text-muted);
    cursor: pointer;
    padding: .25rem;
    border-radius: 6px;
    transition: background var(--trans);
}

.sub-modal-close:hover { background: var(--bg); }

.sub-modal-body {
    padding: 1.3rem 1.4rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.sub-field label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: .4rem;
}

.sub-field textarea {
    width: 100%;
    min-height: 90px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    padding: .65rem .8rem;
    font-family: inherit;
    font-size: .87rem;
    color: var(--text);
    background: var(--bg);
    resize: vertical;
    transition: border-color var(--trans);
    box-sizing: border-box;
}

.sub-field textarea:focus { outline: none; border-color: var(--primary); }

body.dark .sub-field textarea { background: rgba(255,255,255,.04); color: var(--text); }

.sub-dropzone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-sm);
    padding: 1.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: border-color var(--trans), background var(--trans);
    position: relative;
}

.sub-dropzone:hover, .sub-dropzone.drag-over {
    border-color: var(--primary);
    background: var(--primary-light);
}

.sub-dropzone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}

.sub-dropzone-icon {
    font-size: 1.6rem;
    color: var(--primary);
    margin-bottom: .4rem;
}

.sub-dropzone p {
    margin: 0;
    font-size: .8rem;
    color: var(--text-muted);
    line-height: 1.4;
}

.sub-file-preview {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .6rem .9rem;
    background: var(--primary-light);
    border-radius: var(--radius-sm);
    font-size: .82rem;
    color: var(--primary-dark);
    font-weight: 600;
}

.sub-file-preview .sub-remove-file {
    margin-left: auto;
    background: none;
    border: none;
    color: var(--danger);
    cursor: pointer;
    font-size: .85rem;
    padding: 0;
}

.sub-modal-foot {
    display: flex;
    justify-content: flex-end;
    gap: .7rem;
    padding: 1rem 1.4rem;
    border-top: 1px solid var(--border);
}

.sub-btn-cancel {
    background: var(--bg);
    border: 1.5px solid var(--border);
    border-radius: 8px;
    padding: .5rem 1.2rem;
    font-size: .85rem;
    font-weight: 600;
    cursor: pointer;
    color: var(--text);
    transition: background var(--trans);
}

.sub-btn-cancel:hover { background: var(--border); }

.sub-btn-submit {
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .5rem 1.4rem;
    font-size: .85rem;
    font-weight: 700;
    cursor: pointer;
    transition: background var(--trans), opacity var(--trans);
    display: flex;
    align-items: center;
    gap: .4rem;
}

.sub-btn-submit:hover { background: var(--primary-dark); }
.sub-btn-submit:disabled { opacity: .6; cursor: not-allowed; }

/* Warm student stream layout */
body {
    background:
        radial-gradient(circle at 50% -18%, rgba(250,203,140,.18), transparent 34%),
        linear-gradient(180deg, #f8f5f2 0%, #eee8e5 280px, #eee8e5 100%);
}

#tab-stream {
    background: #eee8e5;
    min-height: calc(100vh - var(--nav-h));
}

#tab-stream .cr-layout {
    width: min(100%, 980px);
    max-width: 980px;
    grid-template-columns: minmax(0, 1fr);
    padding: 2rem clamp(1rem, 3vw, 2rem) 3rem;
    gap: 1rem;
}

#tab-stream .cr-side {
    display: none;
}

.tabs-bar {
    justify-content: center;
    background: #fffefd;
}

.tab-btn.active,
.tab-btn:hover {
    color: #795548;
}

.tab-btn.active {
    border-bottom-color: #795548;
}

.filter-row {
    background: transparent;
    border: 0;
    box-shadow: none;
    padding: 0;
    margin: 0 0 .9rem;
}

.cw-filter-wrap {
    gap: .55rem;
    flex-wrap: nowrap;
}

@media(min-width: 900px) {
    #tab-stream .cr-main {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
    }

    #tab-stream .filter-row {
        margin-bottom: 1.1rem;
    }

    #tab-stream .cw-filter-wrap {
        display: grid;
        grid-template-columns: minmax(330px, 1fr) minmax(190px, 240px) minmax(170px, 220px);
        align-items: center;
    }

    #tab-stream .cw-search-shell,
    #tab-stream .advanced-filter,
    #tab-stream .advanced-sort {
        width: 100%;
        min-width: 0;
    }

    #tab-stream .advanced-filter,
    #tab-stream .advanced-sort {
        flex: none;
    }

    #tab-stream .post-card {
        margin-bottom: .9rem;
    }
}

@media(min-width: 1280px) {
    #tab-stream .cr-layout {
        max-width: 1080px;
    }

    #tab-stream .cr-main {
        max-width: 940px;
    }
}

@media(max-width: 899px) {
    #tab-stream .cr-layout {
        width: min(100%, 704px);
        max-width: 704px;
        padding: 1.35rem 1rem 2.6rem;
    }
}

.cw-search-shell {
    position: relative;
    flex: 1 1 auto;
    min-width: 0;
}

.cw-search-shell i {
    position: absolute;
    left: .92rem;
    top: 50%;
    transform: translateY(-50%);
    color: #a98f85;
    font-size: .82rem;
    pointer-events: none;
}

.cw-search-shell .cw-filter-search {
    width: 100%;
    height: 40px;
    padding-left: 2.35rem;
    padding-right: 2.35rem;
    border-radius: 12px;
    border-color: #d8cac4;
    background: rgba(255,255,255,.82);
    color: #3f302b;
}

.cw-search-clear {
    position: absolute;
    right: .4rem;
    top: 50%;
    transform: translateY(-50%);
    width: 30px;
    height: 30px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #a98f85;
    cursor: pointer;
}

.cw-search-clear:hover {
    background: #f0e7e2;
    color: #795548;
}

.advanced-filter,
.advanced-sort {
    position: relative;
    flex: 0 0 190px;
    width: 190px;
}

.advanced-filter-toggle,
.advanced-sort-toggle {
    width: 100%;
    height: 40px;
    min-height: 40px;
    padding: .45rem .62rem;
    border: 1px solid #d8cac4;
    border-radius: 12px;
    background: rgba(255,255,255,.84);
    color: #3f302b;
    display: flex;
    align-items: center;
    gap: .65rem;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(68,45,36,.04);
}

.advanced-filter-toggle:hover,
.advanced-filter.open .advanced-filter-toggle,
.advanced-sort-toggle:hover,
.advanced-sort.open .advanced-sort-toggle {
    border-color: #bdaaa2;
    box-shadow: 0 7px 16px rgba(121,85,72,.12);
}

.advanced-filter-icon,
.advanced-sort-icon {
    width: 28px;
    height: 28px;
    border-radius: 9px;
    background: #f0e7e2;
    color: #795548;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.advanced-filter-copy,
.advanced-sort-copy {
    flex: 1;
    min-width: 0;
    text-align: left;
}

.advanced-filter-label,
.advanced-sort-label {
    display: block;
    color: #3f302b;
    font-size: .84rem;
    font-weight: 900;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.advanced-filter-sub,
.advanced-sort-sub {
    display: none;
    color: #a07f73;
    font-size: .72rem;
    font-weight: 700;
    margin-top: .08rem;
}

.advanced-filter-caret,
.advanced-sort-caret {
    color: #795548;
    transition: transform var(--trans);
}

.advanced-filter.open .advanced-filter-caret,
.advanced-sort.open .advanced-sort-caret {
    transform: rotate(180deg);
}

.advanced-filter-menu,
.advanced-sort-menu {
    display: none;
    position: absolute;
    left: auto;
    right: 0;
    width: min(310px, calc(100vw - 2rem));
    top: calc(100% + .45rem);
    z-index: 140;
    padding: .42rem;
    border: 1px solid #d8cac4;
    border-radius: 14px;
    background: #fffefd;
    box-shadow: 0 18px 42px rgba(68,45,36,.18);
}

.advanced-filter.open .advanced-filter-menu,
.advanced-sort.open .advanced-sort-menu {
    display: block;
}

.advanced-filter-option,
.advanced-sort-option {
    width: 100%;
    min-height: 46px;
    padding: .48rem .55rem;
    border: 0;
    border-radius: 10px;
    background: transparent;
    color: #3f302b;
    display: flex;
    align-items: center;
    gap: .62rem;
    cursor: pointer;
    text-align: left;
}

.advanced-filter-option:hover,
.advanced-sort-option:hover {
    background: #fbf7f5;
}

.advanced-filter-option.active,
.advanced-sort-option.active {
    background: #795548;
    color: #fff;
}

.advanced-filter-option.active .advanced-filter-sub,
.advanced-filter-option.active .filter-option-count,
.advanced-sort-option.active .advanced-sort-sub,
.advanced-sort-option.active .sort-option-hint {
    color: rgba(255,255,255,.78);
}

.advanced-filter-option.active .filter-option-icon,
.advanced-sort-option.active .sort-option-icon {
    background: rgba(255,255,255,.16);
    color: #fff;
}

.filter-option-icon,
.sort-option-icon {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: #f0e7e2;
    color: #795548;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.filter-option-copy,
.sort-option-copy {
    flex: 1;
    min-width: 0;
}

.filter-option-label,
.sort-option-label {
    display: block;
    font-size: .84rem;
    font-weight: 900;
}

.filter-option-count,
.sort-option-hint {
    color: #a07f73;
    font-size: .78rem;
    font-weight: 900;
    white-space: nowrap;
}

@media(max-width: 640px) {
    .cw-filter-wrap {
        flex-wrap: wrap;
    }

    .cw-search-shell,
    .advanced-filter,
    .advanced-sort {
        flex: 1 1 100%;
        width: 100%;
    }

    .advanced-filter-menu,
    .advanced-sort-menu {
        left: 0;
        width: 100%;
    }
}

.lesson-period-filter {
    background: rgba(255,255,255,.68);
    border: 1px solid #dfd3cd;
    border-radius: 14px;
    padding: 0 .65rem;
}

.lesson-period-filter.show {
    padding: .65rem;
    margin-top: .15rem;
}

.lesson-period-filter-option {
    border-color: #d8cac4;
    background: #fffefd;
    color: #795548;
}

.lesson-period-filter-option:has(input:checked) {
    border-color: #795548;
    background: #f0e7e2;
    color: #654337;
    box-shadow: none;
}

.post-card {
    border-color: #d8cac4;
    border-radius: 14px;
    box-shadow: 0 2px 7px rgba(68,45,36,.11);
    margin-bottom: .72rem;
    background: #fffefd;
    cursor: pointer;
}

.post-card:hover {
    border-color: #c5b4ac;
    box-shadow: 0 10px 24px rgba(68,45,36,.13);
    transform: translateY(-1px);
}

.pc-head {
    align-items: flex-start;
    padding: 1rem 1rem .5rem;
    gap: .72rem;
}

.pc-avatar {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #f0e7e2 !important;
    color: #654337 !important;
}

.pc-author {
    font-size: 1.02rem;
    color: #3f302b;
}

.pc-title-row {
    display: flex;
    align-items: center;
    gap: .45rem;
    flex-wrap: wrap;
}

.pc-date {
    font-size: .82rem;
    color: #a07f73;
}

.pc-points {
    margin-left: auto;
    text-align: right;
    color: #3f302b;
    font-weight: 900;
    line-height: 1;
}

.pc-card-status {
    margin-left: auto;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: .35rem;
    flex-shrink: 0;
}

.pc-card-status .pc-points {
    margin-left: 0;
}

.pc-open-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 42px;
    min-width: 36px;
    min-height: 42px;
    padding: 0;
    border: 0;
    background: transparent;
    color: #795548;
    box-shadow: none;
    font-size: .95rem;
    font-weight: 900;
    line-height: 1;
    opacity: .78;
    transition: color var(--trans), opacity var(--trans), transform var(--trans);
}

.pc-open-indicator i {
    font-size: .98rem;
    line-height: 1;
}

.post-card:hover .pc-open-indicator {
    color: #654337;
    opacity: 1;
    transform: translateX(3px);
}

.post-card:hover .pc-author {
    color: #654337;
}

.pc-left-status {
    display: flex;
    align-items: center;
    gap: .35rem;
    margin-top: .45rem;
    flex-wrap: wrap;
}

.pc-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 24px;
    padding: .25rem .65rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 900;
    letter-spacing: .04em;
}

.pc-status-badge.is-late {
    background: #fdecea;
    color: #b42318;
    border: 1px solid #f5c2c7;
}

.pc-status-badge.is-unavailable {
    background: #f0e7e2;
    color: #654337;
    border: 1px solid #d7c8c1;
    letter-spacing: 0;
    text-transform: none;
}

.pc-points span {
    display: block;
    margin-top: .18rem;
    color: #a07f73;
    font-size: .72rem;
    font-weight: 700;
}

.pc-type-badge,
.pc-meta-row span,
.quiz-info-chip,
.sub-mode-badge {
    border-color: #d7c8c1 !important;
    background: #f0e7e2 !important;
    color: #795548 !important;
}

.pc-body {
    padding: .2rem 1rem .65rem;
}

.pc-meta-row {
    margin-top: .35rem;
    gap: .48rem;
}

.pc-meta-row span,
.quiz-info-chip {
    padding: .34rem .72rem;
    font-size: .78rem;
    font-weight: 800;
}

.quiz-info-row {
    margin: .2rem 1rem .75rem;
}

.pc-attachments {
    padding: 0 1rem .75rem;
}

.sq-slot {
    padding: 0 1rem 1rem;
}

.submission-bar {
    display: block;
    padding: 0 1rem 1rem;
    border-top: 0;
    background: transparent !important;
    color: #795548;
}

.submission-bar:has(.sb-submit-btn) > span:not(.sb-icon):not(.sb-grade-pill),
.submission-bar:has(.sb-submit-btn) .sb-icon,
.submission-bar:has(.sb-submit-btn) .sb-file-chip,
.submission-bar:has(.sb-submit-btn) .sb-grade-pill {
    display: none;
}

.submission-bar:not(:has(.sb-submit-btn)) {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .75rem 1rem;
    border-top: 1px solid #e3d8d3;
    background: #fbf7f5 !important;
}

.sb-submit-btn,
.sq-btn-primary,
.sq-btn-take,
.sq-btn-view {
    display: flex;
    align-items: center;
    width: 100%;
    min-height: 44px;
    justify-content: center;
    border-radius: 9px;
    border: 0;
    background: #795548;
    color: #fff;
    font-size: .93rem;
    font-weight: 900;
    box-shadow: none;
}

.sb-submit-btn:hover,
.sq-btn-primary:hover:not(:disabled),
.sq-btn-take:hover:not(:disabled),
.sq-btn-view:hover:not(:disabled) {
    background: #654337;
    transform: translateY(-1px);
    box-shadow: none;
}

.post-card[data-kind="quiz"] .sb-submit-btn,
.post-card[data-kind="quiz"] .sq-btn-take,
.post-card[data-kind="quiz"] .sq-btn-primary,
.post-card[data-kind="exam"] .sb-submit-btn,
.post-card[data-kind="exam"] .sq-btn-take,
.post-card[data-kind="exam"] .sq-btn-primary {
    background: #a98f85;
}

.post-card[data-kind="quiz"] .sb-submit-btn:hover,
.post-card[data-kind="quiz"] .sq-btn-take:hover:not(:disabled),
.post-card[data-kind="quiz"] .sq-btn-primary:hover:not(:disabled),
.post-card[data-kind="exam"] .sb-submit-btn:hover,
.post-card[data-kind="exam"] .sq-btn-take:hover:not(:disabled),
.post-card[data-kind="exam"] .sq-btn-primary:hover:not(:disabled) {
    background: #96786e;
}

.post-card[data-result-released="1"] .sb-submit-btn,
.post-card[data-result-released="1"] .sq-btn-primary,
.post-card[data-result-released="1"] .sq-btn-view {
    background: #795548;
}

.pc-comments {
    border-top-color: #e3d8d3;
}

.pc-comments-toggle {
    color: #a98f85;
    padding: .78rem 1rem;
}

.pc-comments-toggle:hover {
    background: #f8f3ef;
    color: #795548;
}

.empty-feed {
    background: #fffefd;
    border: 1px solid #d8cac4;
    border-radius: 14px;
    box-shadow: 0 2px 7px rgba(68,45,36,.08);
}

.classwork-tools {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    margin-bottom: .9rem;
    color: #795548;
    font-size: .78rem;
    font-weight: 800;
}

.classwork-summary {
    display: inline-flex;
    align-items: center;
    gap: .42rem;
    padding: .4rem .72rem;
    border: 1px solid #d8cac4;
    border-radius: 999px;
    background: rgba(255,255,255,.72);
}

.cw-period-grid {
    display: grid;
    gap: .78rem;
    margin-bottom: .9rem;
}

.cw-period-box,
.cw-topic-group {
    background: #fffefd;
    border: 1px solid #d8cac4;
    border-radius: 14px;
    box-shadow: 0 2px 7px rgba(68,45,36,.08);
    overflow: hidden;
}

.cw-period-head,
.cw-topic-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .72rem .9rem;
    border-bottom: 1px solid #eadfd9;
    color: #3f302b;
    font-size: .82rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.cw-period-count {
    min-width: 24px;
    height: 24px;
    padding: 0 .45rem;
    border-radius: 999px;
    background: #f0e7e2;
    color: #795548;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .74rem;
}

.cw-period-empty {
    padding: .9rem;
    color: #a07f73;
    font-size: .82rem;
}

.cw-item {
    display: grid;
    grid-template-columns: 38px minmax(0, 1fr) auto;
    align-items: center;
    gap: .72rem;
    padding: .82rem .9rem;
    border-bottom: 1px solid #f0e7e2;
    cursor: pointer;
    background: #fffefd;
}

.cw-item:last-child {
    border-bottom: 0;
}

.cw-item:hover {
    background: #fbf7f5;
}

.cw-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0e7e2 !important;
    color: #795548 !important;
}

.cw-title {
    color: #3f302b;
    font-size: .9rem;
    font-weight: 900;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cw-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .36rem;
    margin-top: .22rem;
    color: #a07f73;
    font-size: .74rem;
    font-weight: 700;
}

.cw-points {
    color: #795548;
    font-size: .8rem;
    font-weight: 900;
    white-space: nowrap;
}

.cw-topic-group {
    margin-bottom: .78rem;
}

@media(max-width: 1200px) {
    .cr-layout {
        grid-template-columns: 1fr;
        padding: 1rem;
        gap: 1rem;
    }

    .cr-side {
        order: -1;
        position: static;
    }

    .cr-main,
    .cr-side {
        width: 100%;
        max-width: 100%;
    }

    .side-card {
        margin-bottom: .75rem;
    }
}

@media(max-width: 860px) {
    .cr-layout {
        grid-template-columns: 1fr;
        padding: .9rem;
        gap: .9rem;
    }

    .cr-side {
        order: -1;
        position: static;
    }

    .fv-side-panel {
        width: 270px;
    }
}

@media(max-width: 600px) {
    .topbar {
        padding: 0 .75rem;
        gap: .45rem;
    }

    .topbar-brand {
        gap: .45rem;
        font-size: .82rem;
        min-width: 0;
    }

    .blogo {
        width: 30px;
        height: 30px;
    }

    .topbar-class-name {
        max-width: 34vw;
        font-size: .82rem;
    }

    .icon-btn,
    .back-btn {
        width: 32px;
        height: 32px;
    }

    .tabs-bar {
        padding: 0 .4rem;
        gap: .1rem;
    }

    .tab-btn {
        padding: .86rem .65rem;
        font-size: .81rem;
    }

    .cr-layout {
        padding: .85rem;
        gap: .9rem;
    }

    #tab-stream .cr-layout {
        padding: 1rem .75rem 2.2rem;
    }

    .cw-item {
        grid-template-columns: 34px minmax(0, 1fr);
    }

    .cw-points {
        grid-column: 2;
        justify-self: start;
    }

    .classwork-tools {
        align-items: flex-start;
        flex-direction: column;
        gap: .45rem;
    }

    .pc-head {
        align-items: center;
        gap: .62rem;
    }

    .pc-card-status {
        align-self: stretch;
        justify-content: center;
    }

    .pc-open-indicator {
        width: 44px;
        height: 44px;
        min-width: 44px;
        min-height: 44px;
        color: #795548;
        opacity: .9;
    }

    .pc-open-indicator i {
        font-size: 1.05rem;
    }

    .class-banner {
        height: 190px;
    }

    .banner-row {
        align-items: center;
        gap: .85rem;
    }

    .faculty-identity {
        width: 86px;
        flex: 0 0 86px;
    }

    .faculty-avatar {
        width: 62px;
        height: 62px;
        border-width: 3px;
        font-size: 1rem;
    }

    .faculty-name {
        font-size: .68rem;
        margin-top: .34rem;
    }

    .faculty-role {
        font-size: .62rem;
        margin-top: .14rem;
    }

    .banner-title {
        font-size: 1.16rem;
        line-height: 1.2;
    }

    .banner-content {
        padding: .85rem .9rem;
    }

    .banner-sub {
        font-size: .8rem;
    }

    .banner-chips {
        gap: .3rem;
        margin-top: .55rem;
    }

    .banner-chip {
        font-size: .66rem;
        padding: .14rem .5rem;
    }

    .student-pill {
        display: none;
    }

    .side-card,
    .post-card,
    .quiz-preview,
    .resource-card,
    .comment-card {
        width: 100%;
        max-width: 100%;
    }

    .side-card {
        padding: .85rem .9rem;
    }

    .fv-side-panel {
        display: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
    }
}
</style>
</head>

<body>

<nav class="topbar">
    <a href="student.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
    </a>

    <a href="student.php" class="topbar-brand">
        <div class="blogo">
            <i class="fas fa-book-open"></i>
        </div>
        TERELEARN
    </a>

    <span style="color:var(--border)">&rsaquo;</span>
    <span class="topbar-class-name" id="topbarClassName">Loading...</span>

    <div class="topbar-right">
        <span class="student-pill" id="studentNamePill">Student</span>
        <button class="icon-btn" id="darkToggle">
            <i class="fas fa-moon"></i>
        </button>
    </div>
</nav>

<div class="class-banner">
    <div class="banner-bg" id="bannerBg"></div>
    <div class="banner-overlay"></div>
    <div class="banner-pattern" id="bannerPattern" aria-hidden="true"></div>

    <div class="banner-content">
        <div class="banner-row">
            <div class="faculty-identity" aria-label="Class faculty profile">
                <div class="faculty-avatar" id="facultyAvatar">F</div>
                <div class="faculty-name" id="facultyName">Loading...</div>
                <div class="faculty-role" id="facultyRole">Faculty</div>
            </div>
            <div class="banner-main">
                <div class="banner-title" id="bannerTitle">Loading...</div>
                <div class="banner-sub" id="bannerSub"></div>
                <div class="banner-chips" id="bannerChips"></div>
            </div>
        </div>
    </div>
</div>

<div class="tabs-bar">
    <button class="tab-btn active" data-tab="stream">
        <i class="fas fa-stream" style="margin-right:.35rem;font-size:.8rem"></i>Stream
    </button>

    <button class="tab-btn" data-tab="classwork">
        <i class="fas fa-tasks" style="margin-right:.35rem;font-size:.8rem"></i>Classwork
        <span class="tab-count" id="classworkCount">0</span>
    </button>

    <button class="tab-btn" data-tab="resources">
        <i class="fas fa-folder-open" style="margin-right:.35rem;font-size:.8rem"></i>Files
        <span class="tab-count" id="fileCount">0</span>
    </button>
</div>

<div class="tab-section active" id="tab-stream">
    <div class="cr-layout">
        <div class="cr-main">
            <div class="filter-row" id="typeFilters"></div>
            <div id="streamFeed" class="empty-feed">
                <i class="fas fa-spinner fa-spin"></i> Loading classroom...
            </div>
        </div>

        <div class="cr-side">
            <div class="side-card" id="meetCard">
                <div class="side-card-title">Google Meet</div>
                <div id="meetBox" style="font-size:.82rem;color:var(--text-muted)">Loading...</div>
            </div>

            <div class="side-card">
                <div class="side-card-title">Class Info</div>
                <div class="info-list" id="classInfoList"></div>
            </div>
        </div>
    </div>
</div>

<div class="tab-section" id="tab-classwork">
    <div class="cr-layout">
        <div class="cr-main">
            <div class="classwork-tools">
                <span class="classwork-summary"><i class="fas fa-layer-group"></i> Organized classwork</span>
                <span id="classworkSummaryText">Loading...</span>
            </div>
            <div id="classworkFeed"></div>
        </div>

        <div class="cr-side">
            <div class="side-card">
                <div class="side-card-title">Classwork View</div>
                <div class="info-list">
                    Lessons are grouped by coverage. Activities, quizzes, exams, and assignments stay grouped by topic.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tab-section" id="tab-resources">
    <div class="cr-layout">
        <div class="cr-main">
            <div id="resourceFeed"></div>
        </div>

        <div class="cr-side">
            <div class="side-card">
                <div class="side-card-title">Private Notes</div>
                <div class="info-list">
                    Notes inside the file viewer are private to your student account and can be saved per page for PDFs.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fv-backdrop" id="fvBackdrop">
  <div class="fv-shell">
    <div class="fv-toolbar">
      <div id="fvPageGroup" style="display:none;align-items:center;gap:.3rem;">
        <button class="fv-tb-btn" id="fvPrev"><i class="fas fa-chevron-left"></i></button>
        <span class="fv-page-info" id="fvPageInfo">1 / 1</span>
        <button class="fv-tb-btn" id="fvNext"><i class="fas fa-chevron-right"></i></button>
      </div>
      <div id="fvZoomGroup" style="display:none;align-items:center;gap:.3rem;">
        <button class="fv-tb-btn" id="fvZoomOut"><i class="fas fa-search-minus"></i></button>
        <span class="fv-zoom-label" id="fvZoomLabel">100%</span>
        <button class="fv-tb-btn" id="fvZoomIn"><i class="fas fa-search-plus"></i></button>
      </div>
      <div class="fv-sep" id="fvSep1" style="display:none;width:1px;height:20px;background:rgba(255,255,255,.1);"></div>
      <span class="fv-filename" id="fvFilename">File</span>
      <span class="fv-badge" id="fvBadge">FILE</span>
      <button class="fv-panel-toggle-btn" id="fvPanelToggle"><i class="fas fa-sticky-note"></i> My Notes <span class="fv-badge" id="fvNoteToolBadge">0</span></button>
      <a id="fvDownloadBtn" class="fv-tb-btn" target="_blank" download><i class="fas fa-download"></i></a>
      <button class="fv-tb-btn" id="fvCloseBtn" style="color:#e57373;"><i class="fas fa-times"></i></button>
    </div>
    <div class="fv-layout">
      <div id="fvBody">
        <div class="fv-loading" id="fvLoading"><div class="fv-spinner"></div><span>Loading...</span></div>
      </div>
      <div class="fv-side-panel" id="fvSidePanel">
        <div class="fv-panel-head">
          <i class="fas fa-sticky-note"></i> My Notes
          <span class="fv-badge" id="fvNoteTabBadge">0</span>
          <button class="fv-panel-close" id="fvPanelClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="fv-panel-page-strip">
          <i class="fas fa-file-alt"></i>
          <span id="fvPageStripLabel">This file</span>
          <span class="fv-private-badge"><i class="fas fa-lock"></i> Only you</span>
        </div>
        <div class="fv-notes-pane">
          <div class="fv-note-list" id="fvNoteList">
            <div class="fv-empty-msg"><i class="fas fa-sticky-note"></i><br>No notes yet.</div>
          </div>
          <div class="fv-input-area">
            <div class="fv-input-hint"><i class="fas fa-lock"></i> Private - only visible to you</div>
            <div class="fv-input-row">
              <textarea class="fv-textarea" id="fvNoteInput" placeholder="Add a note..." rows="1"></textarea>
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
const CLASS_ID = <?= json_encode($class_id) ?>;
const SESSION_USER_ID = <?= json_encode($_SESSION['user_id']) ?>;
const API_ROOT = 'API/student/studentClassroom';

let classData = null;
let allPosts = [];
let postTypes = [];
let meetingData = null;
let activeTypeFilter = 'all';
let streamLessonPeriodFilter = 'all';
let streamSearchQuery = '';
let activeStreamSort = 'smart';
const TYPE_FILTER_KEY = 'tl_classwork_filter';
const STREAM_SORT_KEY = 'tl_classwork_sort';

function _toastText(msg, type = 'success') {
    const s = String(msg || '').toLowerCase();
    if (type === 'error') {
        if (s.includes('network')) return 'Action failed. Check connection.';
        return 'Action failed.';
    }
    if (type === 'info') return 'Update received.';
    if (s.includes('post')) return 'Successfully posted!';
    if (s.includes('create') || s.includes('created')) return 'Successfully created!';
    if (s.includes('save') || s.includes('saved') || s.includes('update') || s.includes('updated')) return 'Successfully saved!';
    if (s.includes('delete') || s.includes('removed')) return 'Successfully removed!';
    if (s.includes('upload')) return 'Successfully uploaded!';
    return 'Success.';
}
function toast(msg, type = 'success') {
    Swal.fire({
        toast: true,
        position: 'bottom-end',
        icon: type,
        title: _toastText(msg, type),
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

function esc(v) {
    return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function fmtDate(v) {
    if (!v) return '';
    const d = new Date(v);
    if (isNaN(d)) return String(v);
    const diff = (new Date() - d) / 1000;
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function fmtTime(t) {
    if (!t) return '';
    const a = String(t).split(':').map(Number), h = a[0] || 0, m = a[1] || 0;
    return `${h % 12 || 12}:${String(m).padStart(2, '0')} ${h >= 12 ? 'PM' : 'AM'}`;
}

function countdownText(due) {
    if (!due) return null;
    const d    = new Date(due);
    if (isNaN(d)) return null;
    const diff = d - Date.now();
    if (diff < 0) {
        const abs  = -diff;
        const days = Math.floor(abs / 86400000);
        const hrs  = Math.floor(abs / 3600000);
        if (days > 0) return { text: `Overdue by ${days}d`, cls: 'cd-overdue' };
        return { text: hrs > 0 ? `Overdue by ${hrs}h` : 'Just overdue', cls: 'cd-overdue' };
    }
    const days = Math.floor(diff / 86400000);
    const hrs  = Math.floor((diff % 86400000) / 3600000);
    const mins = Math.floor((diff % 3600000)  / 60000);
    if (days > 1) return { text: `Due in ${days}d ${hrs}h`, cls: 'cd-ok' };
    if (days === 1) return { text: `Due in 1d ${hrs}h`,     cls: 'cd-warn' };
    if (hrs > 0)   return { text: `Due in ${hrs}h ${mins}m`, cls: 'cd-urgent' };
    if (mins > 0)  return { text: `Due in ${mins}m`,         cls: 'cd-urgent' };
    return { text: 'Due now', cls: 'cd-overdue' };
}

function isOverdue(due) {
    if (!due) return false;
    const d = new Date(String(due).includes('T') ? due : String(due).replace(' ', 'T'));
    return !Number.isNaN(d.getTime()) && d.getTime() < Date.now();
}

function refreshCountdowns() {
    document.querySelectorAll('.due-countdown[data-due]').forEach(el => {
        const r = countdownText(el.dataset.due);
        if (!r) return;
        el.className = 'due-countdown ' + r.cls;
        const txt = el.querySelector('.cd-text');
        if (txt) txt.textContent = r.text;
    });
}

function initials(n, f = 'S') {
    const p = String(n || '').trim().split(/\s+/).filter(Boolean);
    return p.length ? p.map(x => x[0]).join('').substring(0, 2).toUpperCase() : f;
}

function mediaUrl(path) {
    const value = String(path || '').trim();
    if (!value) return '';
    if (/^(https?:)?\/\//i.test(value) || value.startsWith('data:') || value.startsWith('/')) return value;
    return value.replace(/^(\.\/)+/, '');
}

const STUDENT_ROOM_PALETTE_GRADIENTS = {
    'b-forest': 'linear-gradient(135deg,#115e59 0%,#0f766e 48%,#0d9488 100%)',
    'b-ocean': 'linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 52%,#0284c7 100%)',
    'b-sunset': 'linear-gradient(135deg,#9a3412 0%,#c2410c 52%,#ea580c 100%)',
    'b-plum': 'linear-gradient(135deg,#581c87 0%,#6d28d9 52%,#7c3aed 100%)',
    'b-teal': 'linear-gradient(135deg,#164e63 0%,#0e7490 52%,#0891b2 100%)',
    'b-rose': 'linear-gradient(135deg,#881337 0%,#be123c 52%,#e11d48 100%)',
    'b-slate': 'linear-gradient(135deg,#1e293b 0%,#334155 56%,#475569 100%)',
    'b-indigo': 'linear-gradient(135deg,#312e81 0%,#4338ca 52%,#4f46e5 100%)'
};
const STUDENT_ROOM_LEGACY_PALETTES = {
    'linear-gradient(135deg,#1a9e78,#0a5c45)': 'b-forest',
    'linear-gradient(135deg,#1f73db,#0d47a1)': 'b-ocean',
    'linear-gradient(135deg,#f57c00,#bf360c)': 'b-sunset',
    'linear-gradient(135deg,#7b1fa2,#4a148c)': 'b-plum',
    'linear-gradient(135deg,#00838f,#00474d)': 'b-teal',
    'linear-gradient(135deg,#c62828,#880e4f)': 'b-rose',
    'linear-gradient(135deg,#455a64,#263238)': 'b-slate',
    'linear-gradient(135deg,#3949ab,#1a237e)': 'b-indigo'
};
const STUDENT_ROOM_PALETTE_KEYS = Object.keys(STUDENT_ROOM_PALETTE_GRADIENTS);
const studentRoomCompactPalette = value => String(value || '').toLowerCase().replace(/\s+/g, '');
const STUDENT_ROOM_PALETTE_LOOKUP = {};
Object.entries(STUDENT_ROOM_PALETTE_GRADIENTS).forEach(([key, gradient]) => {
    STUDENT_ROOM_PALETTE_LOOKUP[studentRoomCompactPalette(gradient)] = key;
});
Object.entries(STUDENT_ROOM_LEGACY_PALETTES).forEach(([gradient, key]) => {
    STUDENT_ROOM_PALETTE_LOOKUP[studentRoomCompactPalette(gradient)] = key;
});

function studentRoomHashPaletteKey(seed) {
    let h = 0;
    for (const ch of String(seed || 'class').toLowerCase()) h = ((h << 5) - h) + ch.charCodeAt(0);
    return STUDENT_ROOM_PALETTE_KEYS[Math.abs(h) % STUDENT_ROOM_PALETTE_KEYS.length];
}

function studentRoomSharedPaletteKey(raw) {
    const value = String(raw || '').trim();
    if (!value) return '';
    if (STUDENT_ROOM_PALETTE_GRADIENTS[value]) return value;
    return STUDENT_ROOM_PALETTE_LOOKUP[studentRoomCompactPalette(value)] || '';
}

function studentRoomBannerGradient(c, titleLine) {
    const sharedKey = studentRoomSharedPaletteKey(c.banner_palette || '');
    if (sharedKey) return STUDENT_ROOM_PALETTE_GRADIENTS[sharedKey];
    const seed = [c.subject_name, c.subject_code, c.course_name, c.course_code, c.class_code, titleLine].filter(Boolean).join('|');
    return STUDENT_ROOM_PALETTE_GRADIENTS[studentRoomHashPaletteKey(seed)];
}

function studentRoomPatternIconSet(c) {
    const text = [c.subject_name, c.subject_code, c.course_name, c.course_code, c.class_code, c.section].join(' ').toLowerCase();
    if (/mobile|android|ios|app/.test(text)) return ['fa-mobile-screen-button','fa-code','fa-layer-group','fa-bug'];
    if (/web|html|css|javascript|system/.test(text)) return ['fa-window-maximize','fa-code','fa-laptop-code','fa-diagram-project'];
    if (/data|database|dbms|sql/.test(text)) return ['fa-database','fa-server','fa-table','fa-chart-line'];
    if (/network|security|cyber/.test(text)) return ['fa-network-wired','fa-shield-halved','fa-lock','fa-server'];
    if (/tour|travel|hospitality/.test(text)) return ['fa-plane-departure','fa-location-dot','fa-suitcase-rolling','fa-ticket'];
    if (/math|stat|analytics/.test(text)) return ['fa-chart-simple','fa-square-root-variable','fa-calculator','fa-chart-pie'];
    return ['fa-book-open','fa-clipboard-list','fa-graduation-cap','fa-comments'];
}

function renderStudentRoomPattern(c) {
    const wrap = document.getElementById('bannerPattern');
    if (!wrap) return;
    const icons = studentRoomPatternIconSet(c || {});
    const slots = [
        [7, 28, 48, -2, true],
        [39, 43, 74, -7, false],
        [62, 33, 50, 2, false],
        [87, 63, 70, -10, true],
        [23, 72, 58, 7, false],
        [74, 78, 42, 4, true]
    ];
    wrap.innerHTML = slots.map((slot, i) => {
        const [x, y, size, rot, ghost] = slot;
        return `<span class="banner-pattern-icon${ghost ? ' is-ghost' : ''}" style="--x:${x}%;--y:${y}%;--s:${size}px;--r:${rot}deg;"><i class="fas ${icons[i % icons.length]}"></i></span>`;
    }).join('');
}

function facultyRoleLabel(c) {
    const rawRole = String(c.faculty_admin_role || '').trim().toLowerCase();
    const department = c.faculty_department_code || c.faculty_department_name || '';
    if (rawRole.includes('secretary')) return department ? `Secretary of ${department}` : 'Secretary';
    if (rawRole.includes('dean') || String(c.faculty_is_dean || '') === '1') return department ? `Dean of ${department}` : 'Dean';
    return 'Faculty';
}

function titleCase(s) {
    return String(s || '').replace(/[_-]/g, ' ').replace(/\b\w/g, m => m.toUpperCase());
}

async function apiFetch(url, opt) {
    const r = await fetch(url, opt);
    const text = await r.text();

    let data = null;
    try {
        data = text ? JSON.parse(text) : null;
    } catch (e) {
        throw new Error(text.substring(0, 300) || `Server error ${r.status}: empty response from ${url}`);
    }

    if (!r.ok) {
        throw new Error((data && data.message) ? data.message : `Server error ${r.status}`);
    }

    return data;
}
(function () {
    const dark = localStorage.getItem('tl_dark') === '1';
    if (dark) document.body.classList.add('dark');
    const b = document.getElementById('darkToggle'), i = b.querySelector('i');
    i.className = dark ? 'fas fa-sun' : 'fas fa-moon';
    b.onclick = () => {
        const d = document.body.classList.toggle('dark');
        localStorage.setItem('tl_dark', d ? '1' : '0');
        i.className = d ? 'fas fa-sun' : 'fas fa-moon';
    };
})();

document.querySelectorAll('.tab-btn').forEach(b => b.onclick = () => {
    document.querySelectorAll('.tab-btn').forEach(x => x.classList.remove('active'));
    document.querySelectorAll('.tab-section').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    document.getElementById('tab-' + b.dataset.tab).classList.add('active');
});

async function refreshStream() {
    if (isUserInteractingWithFilters()) return;
    try {
        await loadClassroom();
        renderFilters();
        renderStream();
        renderClasswork();
        renderResources();
    } catch (e) { }
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        const savedFilter = localStorage.getItem(TYPE_FILTER_KEY);
        if (savedFilter) activeTypeFilter = savedFilter;
        const savedSort = localStorage.getItem(STREAM_SORT_KEY);
        if (savedSort) activeStreamSort = savedSort;
    } catch (e) {}
    initPage();
    setInterval(refreshStream, 5000);
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) refreshStream();
    });
});
document.addEventListener('click', e => {
    if (!e.target.closest('#postTypeAdvancedFilter')) closeAdvancedFilter();
    if (!e.target.closest('#streamSortAdvancedFilter')) closeStreamSort();
    const b = e.target.closest('.fv-trigger');
    if (b) openFileViewer(b.dataset.url, b.dataset.name, b.dataset.mime, b.dataset.attachId || '');
});

async function initPage() {
    try {
        await Promise.all([loadSelf(), loadClassroom()]);
        renderBanner();
        renderClassInfo();
        renderMeet();
        renderFilters();
        renderStream();
        renderClasswork();
        renderResources();
        openInitialPostFromUrl();
        refreshCountdowns();
        setInterval(refreshCountdowns, 60000);
    } catch (e) {
        console.error(e);
        showError(e.message || 'Unable to load classroom.');
    }
}

async function loadSelf() {
    try {
        const d = await apiFetch('API/student/get_student_self.php');
        if (d.status === 'success') {
            const s = d.student || d.user || d;
            const name = s.full_name || [s.first_name, s.last_name].filter(Boolean).join(' ') || 'Student';
            document.getElementById('studentNamePill').textContent = name;
        }
    } catch (e) { }
}

async function loadClassroom() {
    const d = await apiFetch(`${API_ROOT}/get_classroom.php?class_id=${encodeURIComponent(CLASS_ID)}`);
    if (d.status !== 'success') throw new Error(d.message || 'Classroom could not be loaded.');

    classData = d.class || d.classroom || {};
    allPosts = Array.isArray(d.posts) ? d.posts : [];
    meetingData = d.meeting || null;

    if (Array.isArray(d.post_types) && d.post_types.length) {
        postTypes = d.post_types;
    } else {
        buildPostTypesFromPosts();
    }

    if (d.student && d.student.full_name) {
        document.getElementById('studentNamePill').textContent = d.student.full_name;
    }
}

function buildPostTypesFromPosts() {
    const m = new Map();
    allPosts.forEach(p => {
        const k = p.post_type_id || p.post_type || 'post';
        if (!m.has(String(k))) {
            m.set(String(k), {
                id: k,
                type_key: p.post_type,
                type_label: p.sub_label || titleCase(p.post_type || 'Post'),
                icon: iconForType(p.post_type),
                color_bg: '#f0e7e2',
                color_text: '#795548'
            });
        }
    });
    postTypes = [...m.values()];
}

function renderMeet() {
    const box = document.getElementById('meetBox');
    const u = meetingData ? (meetingData.meet_url || meetingData.meeting_url || meetingData.url || '') : '';
    if (u) {
        box.innerHTML = `<a class="meet-btn" href="${esc(u)}" target="_blank"><i class="fas fa-video"></i> Join Meet</a><div style="word-break:break-all;font-size:.74rem;line-height:1.45;">${esc(u)}</div>`;
    } else {
        box.textContent = 'No Meet link has been posted yet.';
    }
}

function renderBanner() {
    const c = classData;
    const subjectName = c.subject_name || c.course_name || c.subject_code || c.course_code || '';
    const n = [c.section || c.year_level, subjectName].filter(Boolean).join(' ') || c.class_name || c.class_code || 'Classroom';
    const sub = c.course_name || c.subject_name || c.course_code || c.subject_code || '';
    const faculty = c.faculty_name || [c.first_name, c.middle_name, c.last_name].filter(Boolean).join(' ') || 'Faculty';
    const facultyRole = facultyRoleLabel(c);
    const facultyPhoto = mediaUrl(c.faculty_profile_picture || c.profile_picture || '');
    document.getElementById('topbarClassName').textContent = n;
    document.getElementById('bannerTitle').textContent = n;
    document.getElementById('bannerSub').textContent = sub;
    document.getElementById('facultyName').textContent = faculty;
    document.getElementById('facultyRole').textContent = facultyRole;
    const facultyAvatar = document.getElementById('facultyAvatar');
    facultyAvatar.textContent = initials(faculty, 'F');
    if (facultyPhoto) {
        facultyAvatar.innerHTML = `<img src="${esc(facultyPhoto)}" alt="${esc(faculty)}">`;
        const img = facultyAvatar.querySelector('img');
        img.addEventListener('error', () => {
            facultyAvatar.textContent = initials(faculty, 'F');
        }, { once: true });
    }
    document.getElementById('bannerBg').style.background = studentRoomBannerGradient(c, n);
    renderStudentRoomPattern(c);
    document.title = n + ' - TERELEARN';
    const ch = document.getElementById('bannerChips');
    ch.innerHTML = '';
    [
        ['calendar-alt', c.class_semester],
        ['calendar-week', c.class_days],
        ['clock', c.schedule ? String(c.schedule).split('-').map(x => fmtTime(x.trim())).filter(Boolean).join(' - ') : ''],
        ['user-tie', faculty]
    ].forEach(x => {
        if (x[1]) ch.insertAdjacentHTML('beforeend', `<span class="banner-chip"><i class="fas fa-${x[0]}"></i> ${esc(x[1])}</span>`);
    });
}

function renderClassInfo() {
    const c = classData, a = [];
    if (c.subject_name || c.subject_code) a.push(`<div><strong>${esc(c.subject_name || c.subject_code)}</strong></div>`);
    if (c.course_name || c.course_code) a.push(`<div><i class="fas fa-graduation-cap"></i> ${esc(c.course_name || c.course_code)}</div>`);
    if (c.class_semester) a.push(`<div><i class="fas fa-calendar-alt"></i> ${esc(c.class_semester)}</div>`);
    if (c.class_days) a.push(`<div><i class="fas fa-calendar-week"></i> ${esc(c.class_days)}</div>`);
    if (c.schedule) a.push(`<div><i class="fas fa-clock"></i> ${esc(c.schedule)}</div>`);
    const faculty = c.faculty_name || [c.first_name, c.last_name].filter(Boolean).join(' ');
    if (faculty) a.push(`<div><i class="fas fa-user-tie"></i> ${esc(faculty)}</div>`);
    document.getElementById('classInfoList').innerHTML = a.join('') || '<div>No class details available.</div>';
}

function renderFilters() {
    const w = document.getElementById('typeFilters');
    const kinds = ['lesson', 'activity', 'quiz', 'assignment', 'exam'];
    const labels = { lesson: 'Lessons', activity: 'Activities', quiz: 'Quizzes', assignment: 'Assignments', exam: 'Exams' };
    const icons = { all: 'fa-layer-group', lesson: 'fa-book-open', activity: 'fa-pencil-ruler', quiz: 'fa-question-circle', assignment: 'fa-file-signature', exam: 'fa-clipboard-list' };
    const counts = new Map(kinds.map(k => [k, 0]));
    const streamPosts = allPosts.filter(p => postKind(p) !== 'announcement');
    streamPosts.forEach(p => {
        const k = postKind(p);
        if (counts.has(k)) counts.set(k, (counts.get(k) || 0) + 1);
    });
    const visibleKinds = ['activity', 'quiz', 'lesson'];
    ['assignment', 'exam'].forEach(k => {
        if ((counts.get(k) || 0) > 0) visibleKinds.push(k);
    });
    const validFilters = new Set(['all', ...visibleKinds]);
    if (activeTypeFilter !== 'all' && !validFilters.has(activeTypeFilter)) {
        activeTypeFilter = 'all';
        try { localStorage.setItem(TYPE_FILTER_KEY, activeTypeFilter); } catch (e) {}
    }

    const filters = [{ value: 'all', label: 'All post types', short: 'All', count: streamPosts.length, icon: icons.all }].concat(
        visibleKinds.map(k => ({ value: k, label: labels[k] || titleCase(k), short: labels[k] || titleCase(k), count: counts.get(k) || 0, icon: icons[k] || 'fa-file-alt' }))
    );
    const selected = filters.find(f => f.value === activeTypeFilter) || filters[0];
    const sortOptions = [
        { value: 'smart', label: 'Smart sort', short: 'Smart', hint: 'Late, due soon, newest', icon: 'fa-magic' },
        { value: 'newest', label: 'Newest first', short: 'Newest', hint: 'Recently posted', icon: 'fa-arrow-down-wide-short' },
        { value: 'due', label: 'Due soon', short: 'Due soon', hint: 'Nearest deadlines', icon: 'fa-clock' },
        { value: 'type', label: 'Post type', short: 'Type', hint: 'Activities, quizzes, lessons', icon: 'fa-layer-group' },
        { value: 'title', label: 'A-Z title', short: 'A-Z', hint: 'Alphabetical', icon: 'fa-arrow-down-a-z' }
    ];
    if (!sortOptions.some(o => o.value === activeStreamSort)) activeStreamSort = 'smart';
    const selectedSort = sortOptions.find(o => o.value === activeStreamSort) || sortOptions[0];
    const options = filters.map(f => {
        const countLabel = `${f.count} post${f.count !== 1 ? 's' : ''}`;
        return `<button type="button" class="advanced-filter-option${activeTypeFilter === f.value ? ' active' : ''}" onclick="setTypeFilter('${esc(f.value)}')" role="option" aria-selected="${activeTypeFilter === f.value ? 'true' : 'false'}">
            <span class="filter-option-icon"><i class="fas ${esc(f.icon)}"></i></span>
            <span class="filter-option-copy">
                <span class="filter-option-label">${esc(f.label)}</span>
                <span class="advanced-filter-sub">${esc(countLabel)}</span>
            </span>
            <span class="filter-option-count">${esc(String(f.count))}</span>
        </button>`;
    }).join('');
    const sortHtml = sortOptions.map(o => `
        <button type="button" class="advanced-sort-option${activeStreamSort === o.value ? ' active' : ''}" onclick="setStreamSort('${esc(o.value)}')" role="option" aria-selected="${activeStreamSort === o.value ? 'true' : 'false'}">
            <span class="sort-option-icon"><i class="fas ${esc(o.icon)}"></i></span>
            <span class="sort-option-copy">
                <span class="sort-option-label">${esc(o.label)}</span>
                <span class="advanced-sort-sub">${esc(o.hint)}</span>
            </span>
            <span class="sort-option-hint">${activeStreamSort === o.value ? 'On' : ''}</span>
        </button>
    `).join('');
    w.innerHTML = `
      <div class="cw-filter-wrap">
        <div class="cw-search-shell">
          <i class="fas fa-search"></i>
          <input id="streamSearch" class="cw-filter-search" type="search" placeholder="Search posts..." value="${esc(streamSearchQuery)}" oninput="onStreamFilterChange()">
          <button type="button" class="cw-search-clear" onclick="clearStreamSearch()" title="Clear search" aria-label="Clear search"><i class="fas fa-times"></i></button>
        </div>
        <div class="advanced-filter" id="postTypeAdvancedFilter">
          <button type="button" class="advanced-filter-toggle" onclick="toggleAdvancedFilter(event)" aria-haspopup="listbox" aria-expanded="false">
            <span class="advanced-filter-icon"><i class="fas ${esc(selected.icon)}"></i></span>
            <span class="advanced-filter-copy">
              <span class="advanced-filter-label">Type: ${esc(selected.short || selected.label || 'All')}</span>
            </span>
            <i class="fas fa-chevron-down advanced-filter-caret"></i>
          </button>
          <div class="advanced-filter-menu" role="listbox" aria-label="Post type filters">
            ${options}
          </div>
        </div>
        <div class="advanced-sort" id="streamSortAdvancedFilter">
          <button type="button" class="advanced-sort-toggle" onclick="toggleStreamSort(event)" aria-haspopup="listbox" aria-expanded="false" title="Sort posts">
            <span class="advanced-sort-icon"><i class="fas ${esc(selectedSort.icon)}"></i></span>
            <span class="advanced-sort-copy">
              <span class="advanced-sort-label">Sort: ${esc(selectedSort.short || selectedSort.label || 'Smart')}</span>
            </span>
            <i class="fas fa-chevron-down advanced-sort-caret"></i>
          </button>
          <div class="advanced-sort-menu" role="listbox" aria-label="Post sorting">
            ${sortHtml}
          </div>
        </div>
        <div class="lesson-period-filter" id="lessonPeriodFilter">
          <span class="lesson-period-filter-label"><i class="fas fa-book-open"></i> Coverage</span>
          <label class="lesson-period-filter-option"><input type="radio" name="streamLessonPeriod" value="all" ${streamLessonPeriodFilter === 'all' ? 'checked' : ''} onchange="onLessonPeriodFilterChange()"> All</label>
          <label class="lesson-period-filter-option"><input type="radio" name="streamLessonPeriod" value="prelim" ${streamLessonPeriodFilter === 'prelim' ? 'checked' : ''} onchange="onLessonPeriodFilterChange()"> Prelim</label>
          <label class="lesson-period-filter-option"><input type="radio" name="streamLessonPeriod" value="midterm" ${streamLessonPeriodFilter === 'midterm' ? 'checked' : ''} onchange="onLessonPeriodFilterChange()"> Midterm</label>
          <label class="lesson-period-filter-option"><input type="radio" name="streamLessonPeriod" value="finals" ${streamLessonPeriodFilter === 'finals' ? 'checked' : ''} onchange="onLessonPeriodFilterChange()"> Finals</label>
        </div>
      </div>`;
    syncLessonPeriodFilter();
}

function toggleAdvancedFilter(evt) {
    if (evt) evt.stopPropagation();
    closeStreamSort();
    const wrap = document.getElementById('postTypeAdvancedFilter');
    if (!wrap) return;
    const isOpen = wrap.classList.toggle('open');
    const btn = wrap.querySelector('.advanced-filter-toggle');
    if (btn) btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function closeAdvancedFilter() {
    const wrap = document.getElementById('postTypeAdvancedFilter');
    if (!wrap) return;
    wrap.classList.remove('open');
    const btn = wrap.querySelector('.advanced-filter-toggle');
    if (btn) btn.setAttribute('aria-expanded', 'false');
}

function toggleStreamSort(evt) {
    if (evt) evt.stopPropagation();
    closeAdvancedFilter();
    const wrap = document.getElementById('streamSortAdvancedFilter');
    if (!wrap) return;
    const isOpen = wrap.classList.toggle('open');
    const btn = wrap.querySelector('.advanced-sort-toggle');
    if (btn) btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function closeStreamSort() {
    const wrap = document.getElementById('streamSortAdvancedFilter');
    if (!wrap) return;
    wrap.classList.remove('open');
    const btn = wrap.querySelector('.advanced-sort-toggle');
    if (btn) btn.setAttribute('aria-expanded', 'false');
}

function isUserInteractingWithFilters() {
    const el = document.activeElement;
    const dropdown = document.getElementById('postTypeAdvancedFilter');
    const sortDropdown = document.getElementById('streamSortAdvancedFilter');
    if (dropdown && dropdown.classList.contains('open')) return true;
    if (sortDropdown && sortDropdown.classList.contains('open')) return true;
    if (!el) return false;
    const id = String(el.id || '');
    if (id === 'streamSearch' || id === 'streamTypeFilter') return true;
    if (el.closest && el.closest('#postTypeAdvancedFilter')) return true;
    if (el.closest && el.closest('#streamSortAdvancedFilter')) return true;
    const tag = String(el.tagName || '').toLowerCase();
    return tag === 'input' || tag === 'textarea' || tag === 'select';
}

function setTypeFilter(f) {
    activeTypeFilter = f || 'all';
    try { localStorage.setItem(TYPE_FILTER_KEY, activeTypeFilter); } catch (e) {}
    closeAdvancedFilter();
    renderFilters();
    renderStream(true);
}

function setStreamSort(sortKey) {
    activeStreamSort = sortKey || 'smart';
    try { localStorage.setItem(STREAM_SORT_KEY, activeStreamSort); } catch (e) {}
    closeStreamSort();
    renderFilters();
    renderStream(true);
}

function onStreamFilterChange() {
    const qEl = document.getElementById('streamSearch');
    streamSearchQuery = qEl ? String(qEl.value || '').trim().toLowerCase() : '';
    renderStream(true);
}

function clearStreamSearch() {
    streamSearchQuery = '';
    const qEl = document.getElementById('streamSearch');
    if (qEl) {
        qEl.value = '';
        qEl.focus();
    }
    renderStream(true);
}

function isLessonStreamTypeSelected() {
    return activeTypeFilter === 'lesson';
}

function syncLessonPeriodFilter() {
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

function onLessonPeriodFilterChange() {
    streamLessonPeriodFilter = document.querySelector('input[name="streamLessonPeriod"]:checked')?.value || 'all';
    renderStream(true);
}

function updateStreamFeed(feed, html, animate = false) {
    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (feed.innerHTML === html) {
        feed.classList.remove('is-updating', 'is-animating');
        return;
    }
    if (!animate || reduceMotion) {
        feed.classList.remove('is-updating', 'is-animating');
        feed.innerHTML = html;
        return;
    }
    feed.classList.add('is-updating');
    window.setTimeout(() => {
        feed.innerHTML = html;
        feed.classList.add('is-animating');
        requestAnimationFrame(() => feed.classList.remove('is-updating'));
        window.setTimeout(() => feed.classList.remove('is-animating'), 320);
    }, 150);
}

function postTimestamp(p, field = 'created_at') {
    const raw = p ? p[field] : '';
    const ts = new Date(String(raw || '').replace(' ', 'T')).getTime();
    return Number.isFinite(ts) ? ts : 0;
}

function dueTimestamp(p) {
    const ts = postTimestamp(p, 'due_date');
    return ts || Number.POSITIVE_INFINITY;
}

function postTitleText(p) {
    return String((p && (p.title || p.topic || p.sub_label)) || postKindLabel(postKind(p)) || '').toLowerCase();
}

function postSortWeight(p) {
    const kind = postKind(p);
    if ((kind === 'quiz' || kind === 'exam') && p.due_date && isOverdue(p.due_date)) return 0;
    if (kind !== 'lesson' && p.due_date && isOverdue(p.due_date)) return 1;
    if (p.due_date) return 2;
    if (kind === 'activity' || kind === 'assignment') return 3;
    if (kind === 'quiz' || kind === 'exam') return 4;
    if (kind === 'lesson') return 5;
    return 6;
}

function typeSortWeight(kind) {
    const order = { activity: 0, assignment: 1, quiz: 2, exam: 3, lesson: 4, post: 5 };
    return Object.prototype.hasOwnProperty.call(order, kind) ? order[kind] : 9;
}

function sortStreamPosts(posts) {
    const sorter = activeStreamSort || 'smart';
    posts.sort((a, b) => {
        if (sorter === 'due') {
            const dueDiff = dueTimestamp(a) - dueTimestamp(b);
            if (dueDiff) return dueDiff;
        } else if (sorter === 'type') {
            const typeDiff = typeSortWeight(postKind(a)) - typeSortWeight(postKind(b));
            if (typeDiff) return typeDiff;
            const titleDiff = postTitleText(a).localeCompare(postTitleText(b));
            if (titleDiff) return titleDiff;
        } else if (sorter === 'title') {
            const titleDiff = postTitleText(a).localeCompare(postTitleText(b));
            if (titleDiff) return titleDiff;
        } else if (sorter === 'smart') {
            const weightDiff = postSortWeight(a) - postSortWeight(b);
            if (weightDiff) return weightDiff;
            const dueDiff = dueTimestamp(a) - dueTimestamp(b);
            if (Number.isFinite(dueDiff) && dueDiff) return dueDiff;
        }
        return postTimestamp(b) - postTimestamp(a);
    });
    return posts;
}

function renderStream(animate = false) {
    const f = document.getElementById('streamFeed');
    let posts = allPosts.filter(x => postKind(x) !== 'announcement');
    const hadPosts = posts.length > 0;
    if (activeTypeFilter !== 'all') {
        posts = posts.filter(x => postKind(x) === activeTypeFilter);
    }
    if (isLessonStreamTypeSelected() && streamLessonPeriodFilter !== 'all') {
        posts = posts.filter(x => String(x.lesson_period || '').toLowerCase() === streamLessonPeriodFilter);
    }
    if (streamSearchQuery) {
        posts = posts.filter(x => postSearchText(x).includes(streamSearchQuery));
    }
    sortStreamPosts(posts);
    if (!posts.length) {
        const hasCriteria = activeTypeFilter !== 'all' || streamLessonPeriodFilter !== 'all' || !!streamSearchQuery;
        updateStreamFeed(
            f,
            hasCriteria && hadPosts
                ? empty('fa-filter', 'No matching posts', 'Try another search or post type filter.')
                : empty('fa-stream', 'No posts yet', 'Class updates and activities will appear here.'),
            animate
        );
        return;
    }
    updateStreamFeed(f, posts.map(postCard).join(''), animate);
}

function postSearchText(p) {
    const t = typeFor(p);
    return [
        p.title,
        p.topic,
        p.body,
        p.sub_label,
        p.post_type,
        t.type_label,
        t.type_key,
        p.author_name,
        p.faculty_name
    ].filter(Boolean).join(' ').toLowerCase();
}

function renderClasswork() {
    const f = document.getElementById('classworkFeed');
    if (!f) return;
    const posts = allPosts.filter(x => postKind(x) !== 'announcement');
    const countEl = document.getElementById('classworkCount');
    const summaryEl = document.getElementById('classworkSummaryText');
    if (countEl) countEl.textContent = posts.length;
    if (summaryEl) summaryEl.textContent = `${posts.length} item${posts.length !== 1 ? 's' : ''}`;
    if (!posts.length) {
        f.innerHTML = empty('fa-tasks', 'No classwork yet', 'Lessons, activities, quizzes, and assignments will appear here.');
        return;
    }

    const lessons = posts.filter(p => postKind(p) === 'lesson');
    const otherPosts = posts.filter(p => postKind(p) !== 'lesson');
    const periods = [
        ['prelim', 'Prelim'],
        ['midterm', 'Midterm'],
        ['finals', 'Finals']
    ];
    const periodBoxes = periods.map(([key, label]) => {
        const items = lessons
            .filter(p => String(p.lesson_period || '').toLowerCase() === key)
            .sort(postDateDesc);
        return `<div class="cw-period-box">
            <div class="cw-period-head"><span>${esc(label)}</span><span class="cw-period-count">${items.length}</span></div>
            <div class="cw-period-body">${items.length ? items.map(buildClassworkItem).join('') : '<div class="cw-period-empty">No lessons yet.</div>'}</div>
        </div>`;
    }).join('');
    const unassignedLessons = lessons
        .filter(p => !['prelim', 'midterm', 'finals'].includes(String(p.lesson_period || '').toLowerCase()))
        .sort(postDateDesc);
    const unassignedHtml = unassignedLessons.length
        ? `<div class="cw-topic-group"><div class="cw-topic-label">Other Lessons</div>${unassignedLessons.map(buildClassworkItem).join('')}</div>`
        : '';
    const groupedHtml = renderClassworkGroups(otherPosts.sort(postDateDesc));

    f.innerHTML = `<div class="cw-period-grid">${periodBoxes}</div>${unassignedHtml}${groupedHtml}`;
}

function renderClassworkGroups(posts) {
    if (!posts.length) return '';
    const groups = new Map();
    posts.forEach(p => {
        const key = p.topic || postKindLabel(postKind(p)) || 'General';
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(p);
    });
    return Array.from(groups.entries()).map(([topic, items]) => `
        <div class="cw-topic-group">
            <div class="cw-topic-label">${esc(topic)}</div>
            ${items.map(buildClassworkItem).join('')}
        </div>
    `).join('');
}

function buildClassworkItem(p) {
    const t = typeFor(p);
    const kind = postKind(p);
    const points = kind !== 'lesson' && p.points !== undefined && p.points !== null && p.points !== ''
        ? `<span class="cw-points">${esc(p.points)} pts</span>`
        : '';
    const due = p.due_date ? `<span><i class="fas fa-calendar-check"></i> Due ${esc(fmtDate(p.due_date))}</span>` : '';
    const mode = (kind === 'quiz' || kind === 'exam') ? `<span><i class="fas fa-circle-question"></i> ${esc(quizModeLabel(p))}</span>` : '';
    const coverage = kind === 'lesson' && p.lesson_period ? `<span><i class="fas fa-bookmark"></i> ${esc(titleCase(p.lesson_period))}</span>` : '';
    return `<div class="cw-item" onclick="openPostDetail('${esc(p.id)}')">
        <div class="cw-icon" style="background:${esc(t.color_bg || '#f0e7e2')};color:${esc(t.color_text || '#795548')};"><i class="fas ${esc(t.icon || iconForType(kind))}"></i></div>
        <div>
            <div class="cw-title">${esc(p.title || p.topic || p.sub_label || postKindLabel(kind))}</div>
            <div class="cw-meta">
                <span>${esc(postKindLabel(kind))}</span>
                ${due}
                ${coverage}
                ${mode}
            </div>
        </div>
        ${points}
    </div>`;
}

function postDetailUrl(postId) {
    return `submitwork.php?post_id=${encodeURIComponent(postId)}&class_id=${encodeURIComponent(CLASS_ID)}`;
}

function openPostDetail(postId) {
    if (!postId) return;
    window.location.href = postDetailUrl(postId);
}

function focusPost(id) {
    openPostDetail(id);
}

function openInitialPostFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const postId = params.get('post_id') || params.get('task_id');
    if (!postId) return;

    const exists = allPosts.some(p => String(p.id) === String(postId));
    if (!exists) {
        showToast('That post is no longer available in this class.', 'warning');
        return;
    }

    setTimeout(() => openPostDetail(postId), 80);
}

function postDateDesc(a, b) {
    const ad = new Date(String(a.created_at || '').replace(' ', 'T')).getTime() || 0;
    const bd = new Date(String(b.created_at || '').replace(' ', 'T')).getTime() || 0;
    return bd - ad;
}

function postKindLabel(kind) {
    const labels = {
        lesson: 'Lesson',
        activity: 'Activity',
        quiz: 'Quiz',
        exam: 'Exam',
        assignment: 'Assignment',
        post: 'Post'
    };
    return labels[kind] || titleCase(kind || 'Post');
}

function quizModeLabel(p) {
    const mode = String(p.quiz_mode || p.mode || '').toLowerCase();
    return mode === 'live' ? 'Live mode' : 'Self-paced';
}

function renderResources() {
    const f = document.getElementById('resourceFeed');
    const postsWithFiles = allPosts.filter(x => Array.isArray(x.attachments) && x.attachments.length > 0);
    const totalFiles = postsWithFiles.reduce((s, x) => s + x.attachments.length, 0);

    document.getElementById('fileCount').textContent = totalFiles;

    if (!totalFiles) {
        f.innerHTML = empty('fa-folder-open', 'No files yet', 'Files and links shared by your teacher will appear here.');
        return;
    }

    f.innerHTML = postsWithFiles.map(x => `
        <div class="resource-card">
            <div class="resource-title">${esc(x.title || x.sub_label || 'Class resource')}</div>
            <div class="resource-meta">${esc(fmtDate(x.created_at))}</div>
            <div class="pc-attachments" style="padding:0;">${x.attachments.map(attachChip).join('')}</div>
        </div>
    `).join('');
}

function empty(icon, title, sub) {
    return `<div class="empty-feed"><div class="ef-icon"><i class="fas ${icon}"></i></div><div class="ef-title">${esc(title)}</div><div>${esc(sub)}</div></div>`;
}

function showError(m) {
    ['streamFeed', 'classworkFeed', 'resourceFeed'].forEach(id => {
        const e = document.getElementById(id);
        if (e) e.innerHTML = empty('fa-exclamation-circle', 'Could not load classroom', m);
    });
}

function iconForType(t) {
    t = String(t || '').toLowerCase();
    if (t.includes('quiz')) return 'fa-question-circle';
    if (t.includes('assignment')) return 'fa-file-signature';
    if (t.includes('exam')) return 'fa-clipboard-list';
    if (t.includes('lesson')) return 'fa-book-open';
    if (t.includes('activity')) return 'fa-pencil-ruler';
    if (t.includes('announcement')) return 'fa-bullhorn';
    return 'fa-file-alt';
}

function typeFor(p) {
    return postTypes.find(x => String(x.id) === String(p.post_type_id))
        || postTypes.find(x => String(x.type_key) === String(p.post_type))
        || { icon: iconForType(p.post_type), color_bg: '#f0e7e2', color_text: '#795548', type_label: titleCase(p.post_type || 'Post') };
}

function postCard(p) {
    const t = typeFor(p);
    const kind = postKind(p);
    if ((kind === 'quiz' || kind === 'exam') && parseInt(p.is_published, 10) !== 1) {
        return ''; // Hide unpublished quizzes/exams from students completely.
    }
    const a = p.author_name || p.faculty_name || 'Faculty';
    const pointsValue = kind !== 'lesson' && p.points !== undefined && p.points !== null && p.points !== ''
        ? String(p.points)
        : '';
    const assessmentMode = String(p.quiz_mode || p.mode || '').toLowerCase();
    const isExpiredDueAssessment = (kind === 'quiz' || kind === 'exam') && assessmentMode !== 'live' && p.due_date && isOverdue(p.due_date);
    const at = '';
    const q = (kind === 'quiz' || kind === 'exam') && !isExpiredDueAssessment
        ? quizInfoChips(p, kind)
        : '';
    const sb = '';
    const typeLabel = esc(p.sub_label || t.type_label || titleCase(kind));
    const typeStyle = `background:${esc(t.color_bg || '#f0e7e2')};color:${esc(t.color_text || '#795548')};`;
    const typeIcon = `<i class="fas ${esc(t.icon || iconForType(kind))}"></i>`;
    const postTitle = esc(p.title || p.topic || p.sub_label || t.type_label || titleCase(kind));
    const headDateLabel = `${esc(fmtDate(p.created_at))} &bull; ${esc(a)}`;
    const releasedAssessment = (kind === 'quiz' || kind === 'exam') && !!p.results_released_at;
    const typeBadge = `<span class="pc-type-badge" style="${typeStyle}">${typeIcon} ${typeLabel}</span>`;
    const attempt = p.quiz_attempt || p.exam_attempt || p.attempt || null;
    const earnedScore = attempt?.score ?? attempt?.auto_score ?? p.score ?? p.result_score ?? '';
    const maxScore = attempt?.max_score ?? p.max_score ?? pointsValue;
    const pointBox = releasedAssessment && earnedScore !== '' && maxScore !== ''
        ? `<div class="pc-points">${esc(earnedScore)}/${esc(maxScore)}<span>score</span></div>`
        : (pointsValue ? `<div class="pc-points">${esc(pointsValue)}<span>pts</span></div>` : '');
    const statusBadge = isExpiredDueAssessment
        ? `<div class="pc-left-status"><span class="pc-status-badge is-unavailable">Unable to Take ${kind === 'exam' ? 'Exam' : 'Quiz'}</span></div>`
        : (kind !== 'lesson' && p.due_date && isOverdue(p.due_date)
            ? `<div class="pc-left-status"><span class="pc-status-badge is-late">LATE</span></div>`
            : '');
    const openIndicator = `<div class="pc-open-indicator" aria-label="Open post"><i class="fas fa-angle-right"></i></div>`;
    const rightStatus = `<div class="pc-card-status">${pointBox}${openIndicator}</div>`;
    const bodyContent = `
        ${p.body ? `<div class="pc-text" style="font-size:.88rem;line-height:1.65;color:var(--text-muted);margin-bottom:.35rem;">${esc(p.body)}</div>` : ''}
    `.trim();
    return `<div class="post-card" data-post-id="${esc(p.id)}" data-kind="${esc(kind)}" data-result-released="${releasedAssessment ? '1' : '0'}" data-assessment-closed="${isExpiredDueAssessment ? '1' : '0'}">
        <div class="pc-head">
            <div class="pc-avatar" style="${typeStyle}">${typeIcon}</div>
            <div class="pc-meta">
                <div class="pc-title-row"><div class="pc-author">${postTitle}</div>${typeBadge}</div>
                <div class="pc-date">${headDateLabel}</div>
                ${statusBadge}
            </div>
            ${rightStatus}
        </div>
        ${bodyContent ? `<div class="pc-body">${bodyContent}</div>` : ''}
        ${at}${q}${sb}
    </div>`;
}
function postKind(p) {
    const t = typeFor(p);
    const text = [
        p.post_type,
        p.type_key,
        p.sub_label,
        t.type_key,
        t.type_label
    ].filter(Boolean).join(' ').toLowerCase();
    if (text.includes('lesson')) return 'lesson';
    if (text.includes('activity')) return 'activity';
    if (text.includes('quiz')) return 'quiz';
    if (text.includes('exam')) return 'exam';
    if (text.includes('assignment')) return 'assignment';
    if (text.includes('announcement')) return 'announcement';
    return 'post';
}

function secondsLabel(seconds) {
    const value = parseInt(seconds, 10) || 0;
    if (!value) return 'No limit';
    if (value < 60) return `${value} sec`;
    const minutes = Math.floor(value / 60);
    const rest = value % 60;
    return rest ? `${minutes}m ${rest}s` : `${minutes} min`;
}

function quizInfoChips(p, kind) {
    const questionCount = Array.isArray(p.questions) ? p.questions.length : (parseInt(p.question_count, 10) || 0);
    const mode = String(p.quiz_mode || p.mode || '').toLowerCase();
    const timeMode = String(p.time_mode || p.time_limit_mode || p.timer_mode || '').toLowerCase();
    const firstQuestionTime = Array.isArray(p.questions)
        ? (p.questions.find(q => parseInt(q.time_limit_seconds, 10) > 0)?.time_limit_seconds || 0)
        : 0;
    const timeLimit = parseInt(p.time_limit_seconds, 10) || parseInt(p.seconds_per_question, 10) || parseInt(firstQuestionTime, 10) || 0;
    const isPerQuestion = timeMode.includes('question') || parseInt(p.is_per_question_timer, 10) === 1 || (!!firstQuestionTime && !parseInt(p.time_limit_seconds, 10));
    const timing = isPerQuestion ? `${secondsLabel(timeLimit)} / question` : `${secondsLabel(timeLimit)} total`;
    const chips = [];
    chips.push(`<span class="quiz-info-chip ${mode === 'live' ? 'is-live' : ''}"><i class="fas ${mode === 'live' ? 'fa-stopwatch' : 'fa-unlock'}"></i> ${mode === 'live' ? 'Live mode' : 'Self-paced'}</span>`);
    chips.push(`<span class="quiz-info-chip"><i class="fas fa-clock"></i> ${esc(timing)}</span>`);
    chips.push(`<span class="quiz-info-chip"><i class="fas fa-circle-question"></i> ${esc(questionCount)} question${questionCount !== 1 ? 's' : ''}</span>`);
    return `<div class="quiz-info-row" aria-label="${esc(kind === 'exam' ? 'Exam' : 'Quiz')} information">${chips.join('')}</div>`;
}

function isLateSubmission(p, sub) {
    if (!sub) return false;
    if (sub.is_late === 1 || sub.is_late === '1' || sub.status === 'late') {
        return true;
    }
    if (!p.due_date || !sub.submitted_at) {
        return false;
    }
    const due = new Date(String(p.due_date).replace(' ', 'T'));
    const submitted = new Date(String(sub.submitted_at).replace(' ', 'T'));
    if (Number.isNaN(due.getTime()) || Number.isNaN(submitted.getTime())) {
        return false;
    }
    return submitted.getTime() > due.getTime();
}
function submissionBar(p) {
    const sub = p.submission || null;
    const pid = esc(p.id);
    const ptitle = esc(p.title || p.sub_label || 'this activity');
if (!sub) {
    return `<div class="submission-bar sb-none">
        <span class="sb-icon"><i class="fas fa-paper-plane"></i></span>
        <span>Not yet submitted</span>
        <button class="sb-submit-btn" onclick="openPostDetail('${pid}')">
            <i class="fas fa-upload"></i> Submit Work
        </button>
    </div>`;
}
    const status = sub.status || 'submitted';
    const late = isLateSubmission(p, sub);
    const lateBadge = late
        ? `<span class="sb-grade-pill" style="background:#fdecea;color:#d93025;"><i class="fas fa-clock" style="font-size:.6rem;"></i> Late</span>`
        : `<span class="sb-grade-pill" style="background:#f0e7e2;color:#795548;"><i class="fas fa-check" style="font-size:.6rem;"></i> On time</span>`;
    const fileChip = sub.file_name
        ? `<a class="sb-file-chip" href="API/student/studentClassroom/download_submission.php?id=${esc(sub.submission_id)}" target="_blank" title="${esc(sub.file_name)}"><i class="fas fa-paperclip"></i><span class="sb-file-name">${esc(sub.file_name)}</span><i class="fas fa-eye sb-file-eye"></i></a>`
        : '';
    if (status === 'graded') {
        const grade = sub.grade !== null && sub.grade !== undefined ? sub.grade : null;
        const maxPts = p.points !== undefined && p.points !== null && p.points !== '' ? p.points : null;
        const gradeText = grade !== null
            ? (maxPts !== null ? `${grade} / ${maxPts} pts` : `${grade} pts`)
            : 'Graded';
        return `<div class="submission-bar sb-graded">
            <span class="sb-icon"><i class="fas fa-check-double"></i></span>
            <span>Graded <span class="sb-time">${esc(fmtDate(sub.submitted_at))}</span></span>
            ${fileChip}
            ${lateBadge}
            <span class="sb-grade-pill"><i class="fas fa-star" style="font-size:.6rem;"></i> ${esc(gradeText)}</span>
        </div>`;
    }
    if (status === 'returned') {
        return `<div class="submission-bar sb-returned">
            <span class="sb-icon"><i class="fas fa-undo"></i></span>
            <span>Returned <span class="sb-time">${esc(fmtDate(sub.submitted_at))}</span></span>
            ${fileChip}
            ${lateBadge}
            <button class="sb-submit-btn" onclick="openPostDetail('${pid}')">
                <i class="fas fa-upload"></i> Resubmit
            </button>
        </div>`;
    }
    return `<div class="submission-bar sb-submitted">
        <span class="sb-icon"><i class="fas fa-check"></i></span>
        <span>Submitted <span class="sb-time">${esc(fmtDate(sub.submitted_at))}</span></span>
        ${fileChip}
        ${lateBadge}
        <button class="sb-submit-btn" onclick="openPostDetail('${pid}')">
            <i class="fas fa-pen-to-square"></i> Edit / Resubmit
        </button>
    </div>`;
}
function quizActionBar(p, kind) {
    const label = kind === 'exam' ? 'Exam' : 'Quiz';
    const lower = label.toLowerCase();


// Always show status slot for published quiz/exam posts.
if (kind === 'quiz' || kind === 'exam') {
    if (parseInt(p.is_published, 10) === 0) {
        return ''; // quiz/exam draft
    }
    const released = !!p.results_released_at;
    const info = quizInfoChips(p, kind);
    return `
      <div class="sq-slot" data-sq-kind="${esc(kind)}">
        ${info}
        <div class="sq-actions">
          ${released
            ? `<button type="button" class="sq-btn sq-btn-primary sq-btn-view" onclick="openPostDetail('${esc(p.id)}')">
                 <i class="fa-solid fa-file-lines"></i> Open Results
               </button>`
            : `<button type="button" class="sq-btn sq-btn-take" onclick="openPostDetail('${esc(p.id)}')">
                 <i class="fa-solid fa-up-right-from-square"></i> Open ${esc(label)} Details
               </button>`}
        </div>
      </div>`;
}


    const attempt = p.quiz_attempt || p.exam_attempt || p.attempt || null;
    const attemptStatus = attempt ? String(attempt.status || '').toLowerCase() : '';
    const isAlreadyStarted = attempt && ['started', 'in_progress', 'ongoing'].includes(attemptStatus);
    const isAlreadySubmitted = attempt && ['submitted', 'completed', 'finished', 'graded'].includes(attemptStatus);
    const approvalStatus = String(
        p.quiz_approval_status ||
        p.exam_approval_status ||
        p.approval_status ||
        p.take_status ||
        ''
    ).toLowerCase();
    const isOpen =
        p.quiz_is_open === 1 ||
        p.quiz_is_open === '1' ||
        p.exam_is_open === 1 ||
        p.exam_is_open === '1' ||
        p.is_open === 1 ||
        p.is_open === '1' ||
        approvalStatus === 'approved' ||
        approvalStatus === 'allowed' ||
        approvalStatus === 'open';
    const isStopped =
        p.quiz_is_stopped === 1 ||
        p.quiz_is_stopped === '1' ||
        p.exam_is_stopped === 1 ||
        p.exam_is_stopped === '1' ||
        p.is_stopped === 1 ||
        p.is_stopped === '1' ||
        approvalStatus === 'stopped' ||
        approvalStatus === 'closed' ||
        approvalStatus === 'blocked';
    if (isAlreadySubmitted) {
        return `<div class="submission-bar sb-submitted">
            <span class="sb-icon"><i class="fas fa-check"></i></span>
            <span>${esc(label)} submitted <span class="sb-time">${esc(fmtDate(attempt.submitted_at || attempt.finished_at || attempt.updated_at))}</span></span>
        </div>`;
    }
    if (isAlreadyStarted) {
        return `<div class="submission-bar sb-none">
            <span class="sb-icon"><i class="fas fa-pen"></i></span>
            <span>You already started this ${esc(lower)}. You may continue even if the teacher closes it while you are taking it.</span>
            <button class="sb-submit-btn" onclick="takeQuiz('${esc(p.id)}','${esc(kind)}')">
                <i class="fas fa-play"></i> Continue ${esc(label)}
            </button>
        </div>`;
    }
    if (isStopped) {
        return `<div class="submission-bar sb-returned">
            <span class="sb-icon"><i class="fas fa-lock"></i></span>
            <span>${esc(label)} is currently closed by faculty.</span>
        </div>`;
    }
    if (!isOpen) {
        return `<div class="submission-bar sb-none">
            <span class="sb-icon"><i class="fas fa-hourglass-half"></i></span>
            <span>Waiting for faculty approval before you can take this ${esc(lower)}.</span>
        </div>`;
    }
    return `<div class="submission-bar sb-none">
        <span class="sb-icon"><i class="fas fa-clipboard-question"></i></span>
        <span>${esc(label)} is open.</span>
        <button class="sb-submit-btn" onclick="takeQuiz('${esc(p.id)}','${esc(kind)}')">
            <i class="fas fa-play"></i> Take ${esc(label)}
        </button>
    </div>`;
}
function takeQuiz(postId, kind) {
    const normalizedKind = String(kind || '').toLowerCase() === 'exam' ? 'exam' : 'quiz';
    window.location.href = `takequiz.php?post_id=${encodeURIComponent(postId)}&class_id=${encodeURIComponent(CLASS_ID)}&kind=${encodeURIComponent(normalizedKind)}`;
}

function lessonViewUrl(postId) {
    return postDetailUrl(postId);
}

function fileIcon(m, n) {
    const s = (m + ' ' + n).toLowerCase();
    if (s.includes('pdf')) return 'fa-file-pdf';
    if (s.includes('image') || /\.(png|jpe?g|gif|webp|svg)$/i.test(n)) return 'fa-file-image';
    if (s.includes('word') || /\.docx?$/i.test(n)) return 'fa-file-word';
    if (s.includes('excel') || /\.(xlsx?|csv)$/i.test(n)) return 'fa-file-excel';
    if (s.includes('video')) return 'fa-file-video';
    if (s.includes('audio')) return 'fa-file-audio';
    return 'fa-file';
}

function short(u) {
    return String(u || '').replace(/^https?:\/\//, '').substring(0, 46);
}

function attachChip(a) {
    if (a.attach_type === 'youtube') {
        return `<a href="${esc(a.url)}" target="_blank" class="pa-chip"><i class="fab fa-youtube" style="color:#ff0000;"></i><span class="pa-chip-name">${esc(short(a.url))}</span><i class="fas fa-eye pa-chip-eye"></i></a>`;
    }
    if (a.attach_type === 'link') {
        return `<a href="${esc(a.url)}" target="_blank" class="pa-chip"><i class="fas fa-link"></i><span class="pa-chip-name">${esc(short(a.url))}</span><i class="fas fa-eye pa-chip-eye"></i></a>`;
    }
    const path = a.file_path || a.url || '';
    const name = a.file_name || a.name || 'File';
    const mime = a.mime_type || '';
    const ext = name.split('.').pop().toLowerCase();
    const noPreview = new Set(['zip', 'rar', '7z', 'tar', 'gz', 'exe', 'apk', 'dmg', 'iso']);
    if (noPreview.has(ext)) {
        return `<a href="${esc(path)}" target="_blank" download class="pa-chip"><i class="fas ${fileIcon(mime, name)}"></i><span class="pa-chip-name">${esc(name)}</span><i class="fas fa-download pa-chip-eye"></i></a>`;
    }
    return `<button type="button" class="pa-chip fv-trigger" data-url="${esc(path)}" data-name="${esc(name)}" data-mime="${esc(mime)}" data-attach-id="${esc(a.id || '')}"><i class="fas ${fileIcon(mime, name)}"></i><span class="pa-chip-name">${esc(name)}</span><i class="fas fa-eye pa-chip-eye"></i></button>`;
}

function commentBox(p) {
    const count = parseInt(p.comment_count, 10) || 0;
    const label = count ? `${count} comment${count !== 1 ? 's' : ''}` : 'Class comments';
    return `<div class="pc-comments" id="cmtwrap_${esc(p.id)}">
        <button type="button" class="pc-comments-toggle" id="cmttog_${esc(p.id)}" onclick="toggleComments('${esc(p.id)}', this)">
            <i class="fas fa-comment-alt" style="font-size:.74rem;"></i>
            <span class="cmt-count-lbl">${esc(label)}</span>
            <i class="fas fa-chevron-down cmt-arrow"></i>
        </button>
        <div class="pc-comments-body" id="cmtbody_${esc(p.id)}">
            <div class="comment-list" id="cmtlist_${esc(p.id)}">
                <div class="cmt-empty"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
            </div>
            <div class="cmt-input-row">
                <textarea class="cmt-input" id="cmtin_${esc(p.id)}" placeholder="Add a class comment... (Enter to send)" rows="1" onkeydown="cmtKey(event,'${esc(p.id)}')" oninput="cmtResize(this)"></textarea>
                <button type="button" class="cmt-send" id="cmtsend_${esc(p.id)}" onclick="sendComment('${esc(p.id)}')" title="Post comment"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>`;
}
function postMainContent(p) {
    const kind = postKind(p);
    const m = [];
    if (p.due_date && kind !== 'lesson') m.push(`<span><i class="fas fa-calendar-check"></i> Due ${esc(fmtDate(p.due_date))}</span>`);
    if (kind !== 'lesson' && p.points !== undefined && p.points !== null && p.points !== '') m.push(`<span><i class="fas fa-star"></i> ${esc(p.points)} pts</span>`);
    const at = Array.isArray(p.attachments) && p.attachments.length ? `<div class="pc-attachments" style="padding:0;margin-top:.8rem;">${p.attachments.map(attachChip).join('')}</div>` : '';
    return `
      ${(p.title || p.topic) ? `<div class="pc-title">${esc(p.title || p.topic)}</div>` : ''}
      ${p.body ? `<div class="pc-text">${esc(p.body)}</div>` : ''}
      ${m.length ? `<div class="pc-meta-row" style="margin-top:.6rem;">${m.join('')}</div>` : ''}
      ${at}
    `;
}
let currentCommentPostId = null;
function commentListFor(id, surface = 'inline') {
    if (surface === 'panel') return document.getElementById('cmtPanelList');
    if (surface === 'modal') return document.getElementById('postModalCommentList');
    return document.getElementById('cmtlist_' + id)
        || document.getElementById('postModalCommentList')
        || document.getElementById('cmtPanelList');
}

function commentInputFor(id, surface = 'inline') {
    if (surface === 'panel') return document.getElementById('cmtPanelInput');
    if (surface === 'modal') return document.getElementById('postModalCommentInput');
    return document.getElementById('cmtin_' + id)
        || document.getElementById('postModalCommentInput')
        || document.getElementById('cmtPanelInput');
}

function commentSendFor(id, surface = 'inline') {
    if (surface === 'panel') return document.getElementById('cmtPanelSend');
    if (surface === 'modal') return document.getElementById('postModalCommentSend');
    return document.getElementById('cmtsend_' + id)
        || document.getElementById('postModalCommentSend')
        || document.getElementById('cmtPanelSend');
}

async function toggleComments(id, btn) {
    currentCommentPostId = id;
    const body = document.getElementById('cmtbody_' + id);
    if (!body) return;
    const willOpen = !body.classList.contains('show');
    body.classList.toggle('show', willOpen);
    if (btn) btn.classList.toggle('open', willOpen);
    if (willOpen && !body.dataset.loaded) {
        body.dataset.loaded = '1';
        await loadComments(id, 'inline');
    }
}

function openCommentsPanel(id, title) {
    currentCommentPostId = id;
    document.getElementById('cmtPanelTitle').textContent = title || 'Comments';
    const overlay = document.getElementById('cmtPanelOverlay');
    const panel = document.getElementById('cmtPanel');
    overlay.classList.add('show');
    panel.classList.add('show');
    document.getElementById('cmtPanelInput').value = '';
    loadComments(id, 'panel');
}
function closeCommentsPanel() {
    document.getElementById('cmtPanelOverlay').classList.remove('show');
    document.getElementById('cmtPanel').classList.remove('show');
}

async function loadComments(id, surface = 'inline') {
    const l = commentListFor(id, surface);
    if (!l) return;
    l.innerHTML = '<div class="cmt-empty"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    try {
        const d = await apiFetch(`${API_ROOT}/get_comments.php?post_id=${encodeURIComponent(id)}`);
        if (d.status === 'success') {
            renderComments(id, d.comments || [], surface);
        } else {
            l.innerHTML = `<div class="cmt-empty">${esc(d.message || 'Could not load comments.')}</div>`;
        }
    } catch (e) {
        console.error(e);
        l.innerHTML = '<div class="cmt-empty">Comments are unavailable right now.</div>';
    }
}

function renderComments(id, items, surface = 'inline') {
    const l = commentListFor(id, surface);
    if (!l) return;
    l.innerHTML = items.length ? items.map(commentCard).join('') : '<div class="cmt-empty">No comments yet - be the first.</div>';
    updateCommentCount(id, items.length);
}

function commentCard(c) {
    const own = String(c.user_id) === String(SESSION_USER_ID);
    const fac = c.user_type === 'faculty';
    return `<div class="comment-card" id="cmt_${esc(c.id)}">
        <div class="comment-avatar">${esc(initials(c.full_name || c.author_name, '?'))}</div>
        <div class="comment-main">
            <div class="comment-top">
                <span class="comment-name">${esc(c.full_name || c.author_name || 'Unknown')}</span>
                ${fac ? '<span class="comment-role">Faculty</span>' : ''}
                <span class="comment-time">${esc(fmtDate(c.created_at))}</span>
                ${own ? `<button class="comment-del" onclick="deleteComment('${esc(c.id)}','${esc(c.post_id)}')"><i class="fas fa-trash"></i></button>` : ''}
            </div>
            <div class="comment-text">${esc(c.comment_text || '')}</div>
        </div>
    </div>`;
}

function cmtKey(e, id) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendComment(id); }
}

function cmtResize(x) {
    x.style.height = 'auto';
    x.style.height = Math.min(x.scrollHeight, 90) + 'px';
}

async function sendComment(id) {
    id = id || currentCommentPostId;
    const active = document.activeElement;
    const surface = active && active.closest?.('#postModalOverlay') ? 'modal'
        : (active && active.closest?.('#cmtPanel') ? 'panel' : 'inline');
    const input = commentInputFor(id, surface);
    const btn = commentSendFor(id, surface);
    if (!id) return;
    if (!input || !btn) return;
    const text = input.value.trim();
    if (!text) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    try {
        const d = await apiFetch(`${API_ROOT}/save_comment.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: id, comment_text: text })
        });
        if (d.status === 'success') {
            input.value = '';
            input.style.height = '';
            const l = commentListFor(id, surface);
            const empty = l.querySelector('.cmt-empty');
            if (empty) empty.remove();
            l.insertAdjacentHTML('beforeend', commentCard(d.comment));
            updateCommentCount(id, l.querySelectorAll('.comment-card').length);
        } else {
            toast(d.message || 'Failed to post comment.', 'error');
        }
    } catch (e) {
        console.error(e);
        toast('Could not post comment.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    }
}

async function deleteComment(cid, pid) {
    pid = pid || currentCommentPostId;
    const r = await Swal.fire({ title: 'Delete this comment?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete', confirmButtonColor: '#d93025' });
    if (!r.isConfirmed) return;
    try {
        const d = await apiFetch(`${API_ROOT}/delete_comment.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: cid })
        });
        if (d.status === 'success') {
            const el = document.getElementById('cmt_' + cid);
            if (el) el.remove();
            const surface = document.getElementById('postModalOverlay')?.style.display === 'flex' ? 'modal'
                : (document.getElementById('cmtPanel')?.classList.contains('show') && !document.getElementById('cmtlist_' + pid) ? 'panel' : 'inline');
            const l = commentListFor(pid, surface);
            if (!l) return;
            const n = l.querySelectorAll('.comment-card').length;
            if (!n) l.innerHTML = '<div class="cmt-empty">No comments yet - be the first.</div>';
            updateCommentCount(pid, n);
        } else {
            toast(d.message || 'Failed to delete comment.', 'error');
        }
    } catch (e) {
        toast('Could not delete comment.', 'error');
    }
}

function updateCommentCount(id, n) {
    const w = document.getElementById('cmtwrap_' + id);
    const l = w ? w.querySelector('.cmt-count-lbl') : null;
    if (l) l.textContent = n ? `${n} comment${n !== 1 ? 's' : ''}` : 'Class comments';
}

function openPostModal(postId) {
    const p = allPosts.find(x => String(x.id) === String(postId));
    if (!p) return;
    const modal = document.getElementById('postModalOverlay');
    document.getElementById('postModalTitle').textContent = p.title || p.sub_label || 'Post details';
    document.getElementById('postModalMain').innerHTML = postMainContent(p);
    currentCommentPostId = postId;
    document.getElementById('postModalCommentInput').value = '';
    document.getElementById('postModalCommentList').innerHTML = '<div class="cmt-empty"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.style.display = 'flex';
    loadPostModalComments(postId);
}
function closePostModal() {
    document.getElementById('postModalOverlay').style.display = 'none';
}
async function loadPostModalComments(id) {
    const l = document.getElementById('postModalCommentList');
    try {
        const d = await apiFetch(`${API_ROOT}/get_comments.php?post_id=${encodeURIComponent(id)}`);
        if (d.status === 'success') {
            const items = d.comments || [];
            l.innerHTML = items.length ? items.map(commentCard).join('') : '<div class="cmt-empty">No comments yet ï¿½ be the first.</div>';
        } else {
            l.innerHTML = `<div class="cmt-empty">${esc(d.message || 'Could not load comments.')}</div>`;
        }
    } catch (e) {
        l.innerHTML = '<div class="cmt-empty">Comments are unavailable right now.</div>';
    }
}

document.addEventListener('click', function(e){
    const t = e.target.closest('.post-card .pc-head, .post-card .pc-body, .post-card .pc-card-status');
    if (!t) return;
    if (e.target.closest('button,a,input,textarea,.fv-trigger,.sb-submit-btn,.sq-btn,.pa-chip')) return;
    const card = t.closest('.post-card');
    if (!card) return;
    const pid = card.getAttribute('data-post-id');
    if (!pid) return;
    if (card.getAttribute('data-assessment-closed') === '1') {
        const kind = card.getAttribute('data-kind') === 'exam' ? 'Exam' : 'Quiz';
        Swal.fire({
            icon: 'info',
            title: `Unable to Take ${kind}`,
            text: `The due date has passed. Please contact your professor for assistance.`,
            confirmButtonText: 'OK',
            confirmButtonColor: '#795548'
        });
        return;
    }
    openPostDetail(pid);
});

(function () {
    const PDFJS = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
    const WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    let pdf = null, page = 1, pages = 1, scale = 1.5, attachId = '', pageNo = null;

    const back = document.getElementById('fvBackdrop');
    const body = document.getElementById('fvBody');
    const load = document.getElementById('fvLoading');
    const fn = document.getElementById('fvFilename');
    const badge = document.getElementById('fvBadge');
    const down = document.getElementById('fvDownloadBtn');
    const pg = document.getElementById('fvPageGroup');
    const zg = document.getElementById('fvZoomGroup');
    const sep = document.getElementById('fvSep1');
    const pinfo = document.getElementById('fvPageInfo');
    const zlbl = document.getElementById('fvZoomLabel');
    const notes = document.getElementById('fvNoteList');
    const ni = document.getElementById('fvNoteInput');
    const ns = document.getElementById('fvNoteSend');
    const nt = document.getElementById('fvNoteTabBadge');
    const nb = document.getElementById('fvNoteToolBadge');
    const strip = document.getElementById('fvPageStripLabel');
    const side = document.getElementById('fvSidePanel');

    function ext(n) { return String(n || '').split('.').pop().toLowerCase(); }
    function busy(x) { load.style.display = x ? 'flex' : 'none'; }
    function clear() { Array.from(body.children).forEach(c => { if (c !== load) c.remove(); }); }
    function controls(x) { pg.style.display = x ? 'flex' : 'none'; zg.style.display = x ? 'flex' : 'none'; sep.style.display = x ? '' : 'none'; }
    function badges(n) { nt.textContent = n; nb.textContent = n; }
    function stripText() { strip.textContent = pageNo !== null ? `Page ${pageNo} only` : 'This file'; }

    document.getElementById('fvCloseBtn').onclick = closeViewer;
    document.getElementById('fvPanelToggle').onclick = () => side.classList.toggle('collapsed');
    document.getElementById('fvPanelClose').onclick = () => side.classList.add('collapsed');
    back.onclick = e => { if (e.target === back) closeViewer(); };
    document.getElementById('fvPrev').onclick = () => { if (page > 1) go(page - 1); };
    document.getElementById('fvNext').onclick = () => { if (page < pages) go(page + 1); };
    document.getElementById('fvZoomIn').onclick = () => { scale = Math.min(5, scale + .25); renderPdf(); };
    document.getElementById('fvZoomOut').onclick = () => { scale = Math.max(.4, scale - .25); renderPdf(); };
    ns.onclick = saveNote;
    ni.onkeydown = e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); saveNote(); } };

    window.openFileViewer = (url, name, mime, id) => {
        attachId = id || '';
        pageNo = null;
        page = 1; pages = 1; scale = 1.5; pdf = null;
        fn.textContent = name || 'File';
        badge.textContent = (ext(name) || 'file').toUpperCase();
        down.href = url;
        clear(); busy(true); controls(false);
        side.classList.remove('collapsed');
        ni.value = ''; badges(0); stripText();
        back.classList.add('show');
        document.body.style.overflow = 'hidden';
        const e = ext(name);
        if (e === 'pdf' || String(mime || '').includes('pdf')) openPdf(url);
        else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif'].includes(e) || String(mime || '').startsWith('image/')) showImage(url);
        else if (['mp4', 'webm', 'ogg', 'mov'].includes(e) || String(mime || '').startsWith('video/')) showMedia(url, 'video', mime);
        else if (['mp3', 'wav', 'aac', 'm4a'].includes(e) || String(mime || '').startsWith('audio/')) showMedia(url, 'audio', mime);
        else if (['txt', 'csv', 'json', 'xml', 'css', 'js', 'md', 'sql', 'log'].includes(e)) showText(url);
        else if (['ppt', 'pptx'].includes(e) && attachId) showPowerPoint(url, name);
        else if (['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'].includes(e)) showOffice(url);
        else noPreview(url, name);
        loadNotes();
    };

    function closeViewer() {
        back.classList.remove('show');
        document.body.style.overflow = '';
        clear(); controls(false);
        pdf = null; attachId = '';
    }

    function openPdf(url, fallbackUrl = url, fallbackLabel = 'PDF') {
        const go = () => {
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER;
            const wrap = document.createElement('div');
            wrap.className = 'fv-scroll';
            const c = document.createElement('canvas');
            wrap.appendChild(c);
            body.appendChild(wrap);
            controls(true);
            window.pdfjsLib.getDocument(url).promise.then(d => {
                pdf = d; pages = d.numPages; pageNo = 1; stripText();
                renderPdf(); loadNotes();
            }).catch(() => noPreview(fallbackUrl, fallbackLabel));
        };
        if (window.pdfjsLib) go();
        else {
            const s = document.createElement('script');
            s.src = PDFJS;
            s.onload = go;
            s.onerror = () => noPreview(fallbackUrl, fallbackLabel);
            document.head.appendChild(s);
        }
    }

    function renderPdf() {
        if (!pdf) return;
        const c = body.querySelector('canvas');
        busy(true);
        pdf.getPage(page).then(p => {
            const v = p.getViewport({ scale });
            c.width = v.width; c.height = v.height;
            return p.render({ canvasContext: c.getContext('2d'), viewport: v }).promise;
        }).then(() => {
            busy(false);
            pinfo.textContent = `${page} / ${pages}`;
            zlbl.textContent = Math.round((scale / 1.5) * 100) + '%';
        });
    }

    function go(n) { page = n; pageNo = n; stripText(); renderPdf(); loadNotes(); }

    function showImage(url) {
        const w = document.createElement('div');
        w.className = 'fv-img-wrap';
        const i = document.createElement('img');
        i.src = url;
        i.onload = () => busy(false);
        i.onerror = () => noPreview(url, 'Image');
        w.appendChild(i); body.appendChild(w);
    }

    function showMedia(url, t, m) {
        const w = document.createElement('div');
        w.className = 'fv-media-wrap';
        const el = document.createElement(t);
        el.controls = true; el.src = url;
        el.oncanplay = () => busy(false);
        el.onerror = () => noPreview(url, t);
        w.appendChild(el); body.appendChild(w);
    }

    function showText(url) {
        fetch(url).then(r => r.text()).then(t => {
            const d = document.createElement('div');
            d.className = 'fv-text-wrap';
            d.textContent = t;
            body.appendChild(d); busy(false);
        }).catch(() => noPreview(url, 'Text'));
    }

    function showPowerPoint(url, name) {
        const preview = `${API_ROOT}/get_attachment_preview.php?attach_id=${encodeURIComponent(attachId)}`;
        openPdf(preview, url, name || 'PowerPoint');
    }

    function showOffice(url) {
        busy(false);
        const abs = url.startsWith('http') ? url : location.origin + '/' + url.replace(/^\//, '');
        const d = document.createElement('div');
        d.className = 'fv-no-preview';
        d.innerHTML = `
          <i class="fas fa-file-arrow-down"></i>
          <h3>Preview is limited on this device</h3>
          <p>Open or download this file to view it.</p>
          <div style="display:flex;gap:.6rem;flex-wrap:wrap;justify-content:center;">
            <a href="${esc(abs)}" target="_blank" rel="noopener"><i class="fas fa-up-right-from-square"></i> Open file</a>
            <a href="${esc(abs)}" download><i class="fas fa-download"></i> Download file</a>
          </div>`;
        body.appendChild(d);
    }

    function noPreview(url, label) {
        busy(false);
        const d = document.createElement('div');
        d.className = 'fv-no-preview';
        d.innerHTML = `<i class="fas fa-file-slash"></i><h3>Preview not available</h3><p>${esc(label || 'This file')} cannot be previewed directly.</p><div style="display:flex;gap:.6rem;flex-wrap:wrap;justify-content:center;"><a href="${esc(url)}" target="_blank" rel="noopener"><i class="fas fa-up-right-from-square"></i> Open file</a><a href="${esc(url)}" target="_blank" download><i class="fas fa-download"></i> Download file</a></div>`;
        body.appendChild(d);
    }

    async function loadNotes() {
        if (!attachId) {
            notes.innerHTML = '<div class="fv-empty-msg"><i class="fas fa-lock"></i><br>Notes are not available for this file.</div>';
            badges(0);
            return;
        }
        notes.innerHTML = '<div class="fv-empty-msg"><i class="fas fa-spinner fa-spin"></i></div>';
        try {
            const pp = pageNo !== null ? '&page_number=' + pageNo : '';
            const d = await apiFetch(`${API_ROOT}/get_annotations.php?attach_id=${encodeURIComponent(attachId)}&tab=note${pp}`);
            renderNotes(d.status === 'success' ? (d.annotations || []) : []);
        } catch (e) {
            notes.innerHTML = '<div class="fv-empty-msg">Notes are unavailable right now.</div>';
            badges(0);
        }
    }

    function renderNotes(a) {
        badges(a.length);
        notes.innerHTML = a.length ? a.map(noteCard).join('') : '<div class="fv-empty-msg"><i class="fas fa-sticky-note"></i><br>No notes on this page yet.</div>';
        notes.scrollTop = notes.scrollHeight;
    }

    function noteCard(n) {
        return `<div class="fv-item-card" id="fvitem_${esc(n.id)}">
            <div class="fv-item-time">${esc(fmtDate(n.created_at))}</div>
            <div class="fv-item-text">${esc(n.note_text || '')}</div>
            <button class="fv-item-del" onclick="deleteFileNote('${esc(n.id)}')"><i class="fas fa-trash"></i></button>
        </div>`;
    }

    window.deleteFileNote = async id => {
        const r = await Swal.fire({ title: 'Delete this note?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete', confirmButtonColor: '#d93025' });
        if (!r.isConfirmed) return;
        try {
            const d = await apiFetch(`${API_ROOT}/delete_annotation.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ annotation_id: id })
            });
            if (d.status === 'success') loadNotes();
            else toast(d.message || 'Failed to delete note.', 'error');
        } catch (e) {
            toast('Could not delete note.', 'error');
        }
    };

    async function saveNote() {
        const text = ni.value.trim();
        if (!attachId) { toast('Notes are unavailable for this file.', 'error'); return; }
        if (!text) return;
        ns.disabled = true;
        ns.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        try {
            const payload = { attach_id: attachId, note_text: text, tab: 'note' };
            if (pageNo !== null) payload.page_number = pageNo;
            const d = await apiFetch(`${API_ROOT}/save_annotation.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (d.status === 'success') { ni.value = ''; loadNotes(); }
            else toast(d.message || 'Failed to save note.', 'error');
        } catch (e) {
            toast('Could not save note.', 'error');
        } finally {
            ns.disabled = false;
            ns.innerHTML = '<i class="fas fa-check"></i>';
        }
    }
})();

/* â”€â”€ Submission modal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
document.addEventListener('DOMContentLoaded', function () {
    let _postId = null;
    let _file   = null;

    const overlay  = document.getElementById('sub-modal-overlay');
    const titleEl  = document.getElementById('sub-modal-title');
    const textarea = document.getElementById('sub-comment');
    const dropzone = document.getElementById('sub-dropzone');
    const fileInput= document.getElementById('sub-file-input');
    const preview  = document.getElementById('sub-file-preview');
    const previewName = document.getElementById('sub-file-name');
    const existingWrap = document.getElementById('sub-existing-file');
    const existingLink = document.getElementById('sub-existing-file-link');
    const submitBtn= document.getElementById('sub-btn-submit');
    const subLabel = document.getElementById('sub-btn-label');

    function setFile(f) {
        _file = f || null;
        if (_file) {
            preview.style.display = 'flex';
            dropzone.style.display = 'none';
            previewName.textContent = _file.name + ' (' + fmtBytes(_file.size) + ')';
        } else {
            preview.style.display = 'none';
            dropzone.style.display = '';
            fileInput.value = '';
        }
    }

    function fmtBytes(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
        return (b / 1048576).toFixed(1) + ' MB';
    }

    window.openSubmitModal = function (postId, postTitle, isResubmit) {
        const post = allPosts.find(x => String(x.id) === String(postId)) || null;
        const sub = post && post.submission ? post.submission : null;
        _postId = postId;
        _file   = null;
        textarea.value = sub && sub.comment ? String(sub.comment) : '';
        setFile(null);
        titleEl.textContent = isResubmit ? 'Resubmit Work' : 'Submit Work';
        subLabel.textContent = isResubmit ? 'Resubmit' : 'Submit';
        if (sub && sub.file_name && sub.submission_id) {
            existingLink.href = `API/student/studentClassroom/download_submission.php?id=${encodeURIComponent(sub.submission_id)}`;
            existingLink.textContent = sub.file_name;
            existingWrap.style.display = '';
        } else {
            existingWrap.style.display = 'none';
            existingLink.href = '#';
            existingLink.textContent = '';
        }
        submitBtn.disabled = false;
        overlay.classList.add('open');
        textarea.focus();
    };

    function closeModal() {
        overlay.classList.remove('open');
        _postId = null;
        _file   = null;
    }

    document.getElementById('sub-modal-close').addEventListener('click', closeModal);
    document.getElementById('sub-btn-cancel').addEventListener('click', closeModal);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

    fileInput.addEventListener('change', () => setFile(fileInput.files[0] || null));

    dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        setFile(e.dataTransfer.files[0] || null);
    });

    document.getElementById('sub-remove-file').addEventListener('click', () => setFile(null));

    document.getElementById('sub-form').addEventListener('submit', async e => {
        e.preventDefault();
        const comment = textarea.value.trim();
        if (!comment && !_file) {
            toast('Please add a comment or attach a file before submitting.', 'error');
            return;
        }
        const currentPostId = _postId;
        const isResubmit    = subLabel.textContent === 'Resubmit';

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

        const fd = new FormData();
        fd.append('post_id', currentPostId);
        if (comment) fd.append('comment', comment);
        if (_file)   fd.append('file', _file);

        try {
            const resp = await fetch(`${API_ROOT}/submit_work.php`, { method: 'POST', body: fd });
            const raw = await resp.text();
            let d = null;
            try {
                d = JSON.parse(raw);
            } catch (_e) {
                throw new Error(raw ? `Server response error: ${raw.slice(0, 120)}` : 'Invalid server response.');
            }
if (d.status === 'success') {
    closeModal();

    const submittedAt = d.submission && d.submission.submitted_at
        ? d.submission.submitted_at
        : new Date().toISOString();

    const post = allPosts.find(x => String(x.id) === String(currentPostId));
    const isLate = post
        ? isLateSubmission(post, {
            submitted_at: submittedAt,
            is_late: d.submission ? d.submission.is_late : 0
        })
        : false;

    toast(
        isLate ? 'Work submitted, but marked as late.' : 'Work submitted successfully!',
        isLate ? 'warning' : 'success'
    );

    if (post) {
        post.submission = {
            submission_id: d.submission ? d.submission.submission_id : '',
            status: isLate ? 'late' : 'submitted',
            submitted_at: submittedAt,
            file_name: d.submission ? d.submission.file_name : '',
            comment: comment
        };
    }

    const card = document.querySelector(`.post-card[data-post-id="${currentPostId}"]`);
    if (card && post) {
        const bar = card.querySelector('.submission-bar');
        if (bar) {
            bar.outerHTML = submissionBar(post);
        }
    }
} else {
                toast(d.message || 'Submission failed. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<i class="fas fa-paper-plane"></i> <span id="sub-btn-label">${isResubmit ? 'Resubmit' : 'Submit'}</span>`;
            }
        } catch (err) {
            toast(err?.message || 'Network error. Please check your connection.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fas fa-paper-plane"></i> <span id="sub-btn-label">${isResubmit ? 'Resubmit' : 'Submit'}</span>`;
        }
});
});
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
</script>

<!-- Submission modal -->
<div class="sub-modal-overlay" id="sub-modal-overlay">
    <div class="sub-modal" role="dialog" aria-modal="true" aria-labelledby="sub-modal-title">
        <div class="sub-modal-head">
            <i class="fas fa-paper-plane" style="color:var(--primary);font-size:1.1rem;"></i>
            <h3 id="sub-modal-title">Submit Work</h3>
            <button class="sub-modal-close" id="sub-modal-close" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <form id="sub-form">
            <div class="sub-modal-body">
                <div class="sub-field">
                    <label for="sub-comment">Comment / Notes <span style="font-weight:400;opacity:.6;">(optional)</span></label>
                    <textarea id="sub-comment" placeholder="Add a note for your teacher..."></textarea>
                </div>
                <div class="sub-field">
                    <label>Attachment <span style="font-weight:400;opacity:.6;">(optional)</span></label>
                    <div id="sub-existing-file" style="display:none;margin-bottom:.5rem;font-size:.78rem;color:var(--text-muted);">
                        Current file:
                        <a id="sub-existing-file-link" href="#" target="_blank" style="font-weight:700;color:var(--accent);text-decoration:none;"></a>
                    </div>
                    <div class="sub-dropzone" id="sub-dropzone">
                        <input type="file" id="sub-file-input" accept="*/*">
                        <div class="sub-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <p><strong>Click to browse</strong> or drag &amp; drop a file here</p>
                        <p style="margin-top:.25rem;font-size:.72rem;">Any file type - max 20 MB</p>
                    </div>
                    <div class="sub-file-preview" id="sub-file-preview" style="display:none;">
                        <i class="fas fa-paperclip"></i>
                        <span id="sub-file-name"></span>
                        <button type="button" class="sub-remove-file" id="sub-remove-file" aria-label="Remove file"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            </div>
            <div class="sub-modal-foot">
                <button type="button" class="sub-btn-cancel" id="sub-btn-cancel">Cancel</button>
                <button type="submit" class="sub-btn-submit" id="sub-btn-submit">
                    <i class="fas fa-paper-plane"></i> <span id="sub-btn-label">Submit</span>
                </button>
            </div>
        </form>
    </div>
</div>
<script>
/* â•â• STUDENT QUIZ ENROLLMENT CONTROLLER â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   Wire-up: any element with [data-sq-state="<post_id>"] is auto-rendered
   with the right CTA (Enroll / Waiting / Take Quiz / Closed) and polls
   live state every 4s. The block re-renders itself in place.
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
(function () {
  const POLL_MS = 4000;
  const slots = new Map(); // post_id -> {el, timer}
  const startNotified = new Set();

  function escapeHtml(s){return String(s==null?'':s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
  function normalizeAssessmentKind(raw){ return String(raw || '').toLowerCase() === 'exam' ? 'exam' : 'quiz'; }
  function assessmentLabel(kind){ return normalizeAssessmentKind(kind) === 'exam' ? 'Exam' : 'Quiz'; }
  function assessmentUrl(postId, classId, kind, waiting = false) {
    const params = new URLSearchParams({
      post_id: postId,
      class_id: classId || '',
      kind: normalizeAssessmentKind(kind)
    });
    if (waiting) params.set('waiting', '1');
    return 'takequiz.php?' + params.toString();
  }

  async function fetchState(postId) {
    const fd = new FormData();
    fd.append('post_id', postId);
    const res = await fetch('API/student/studentClassroom/quiz/get_my_quiz_state.php', {
      method: 'POST', body: fd, credentials: 'same-origin'
    });
    return res.json();
  }

  function render(postId) {
    const slot = slots.get(postId);
    if (!slot) return;
    if (slot.inflight) return;
    slot.inflight = true;

    fetchState(postId).then(j => {
      const data = (j && (j.data || j)) || {};
      const quiz = data.quiz || null;
      const kind = normalizeAssessmentKind(slot.el.getAttribute('data-sq-kind') || data.post_type || quiz?.post_type || 'quiz');
      const label = assessmentLabel(kind);
      const lower = label.toLowerCase();
      if (!quiz || !quiz.quiz_id) {
        const html = `<div class="sq-state sq-closed">
          <span class="sq-ico"><i class="fa-solid fa-lock"></i></span>
          <span class="sq-text">${label} is not published yet.</span>
        </div>`;
        if (slot.lastHtml !== html) {
          slot.el.innerHTML = html;
          slot.lastHtml = html;
        }
        slot.lastSig = 'not_published';
        slot.inflight = false;
        return;
      }
      const mode = quiz.quiz_mode || 'due_date';
      const isEnrolled = !!data.is_enrolled;
      const hasMakeupAccess = !!data.has_makeup_access;
      const makeupValidUntil = data.makeup_valid_until || null;
      const ended = (!hasMakeupAccess) && (!!quiz.live_ended_at || parseInt(quiz.is_force_closed,10) === 1);
      const started = !!quiz.live_started_at;
      const paused = started && !ended && parseInt(quiz.is_force_open || 0, 10) !== 1;
      const waitingLeftKey = 'quiz_waiting_left_' + postId;
      let waitingLeft = false;
      try { waitingLeft = sessionStorage.getItem(waitingLeftKey) === '1'; } catch(e) {}
      const nowTs = Date.now();
      const openTs = quiz.open_at ? new Date(String(quiz.open_at).replace(' ','T')).getTime() : null;
      const closeTs = quiz.close_at ? new Date(String(quiz.close_at).replace(' ','T')).getTime() : null;
      const dueTs = quiz.due_date ? new Date(String(quiz.due_date).replace(' ','T')).getTime() : null;
      const windowStarted = (openTs == null) || (openTs <= nowTs);
      const windowNotEnded = (closeTs != null) ? (closeTs > nowTs) : ((dueTs == null) || (dueTs > nowTs));
      const dueOk = hasMakeupAccess || (windowStarted && windowNotEnded);
      const attemptStatus = String(data.attempt_status || '').toLowerCase();
      const isSubmittedAttempt = ['submitted','completed','finished','graded'].includes(attemptStatus);

      const sig = [
        mode, attemptStatus, hasMakeupAccess ? '1':'0', makeupValidUntil || '',
        quiz.results_released_at || '', quiz.is_force_closed || 0, quiz.is_force_open || 0,
        quiz.live_started_at || '', quiz.live_ended_at || '',
        quiz.open_at || '', quiz.close_at || '', quiz.due_date || ''
      ].join('|');
      if (slot.lastSig === sig) {
        // Even when UI signature is same, allow one-shot start modal trigger.
        if (mode === 'live' && isEnrolled && started && !paused && waitingLeft && !isSubmittedAttempt && !startNotified.has(postId)) {
          startNotified.add(postId);
          try { sessionStorage.removeItem(waitingLeftKey); } catch(e) {}
          Swal.fire({
            icon: 'success',
            title: `${label} Started`,
            text: `Your faculty started the ${lower}. You can take it now.`,
            showCancelButton: true,
            confirmButtonText: `Take ${label} Now`,
            cancelButtonText: 'Later'
          }).then((r) => {
            if (r.isConfirmed) {
              const cid = <?= json_encode((string)$class_id) ?>;
              window.location.href = assessmentUrl(postId, cid, kind);
            }
          });
        }
        slot.inflight = false;
        return;
      }

      let html = '';
      const infoHtml = '';
      const released = !!quiz.results_released_at;
      const isLive = mode === 'live';

      if (released) {
        html = `
          ${infoHtml}
          <div class="sq-actions">
            <button type="button" class="sq-btn sq-btn-primary sq-btn-view" data-sq-viewscore="${escapeHtml(postId)}">
              <i class="fa-solid fa-file-lines"></i> Review Results
            </button>
          </div>`;
      } else if (isSubmittedAttempt) {
        html = `
          ${infoHtml}
          <div class="sq-state sq-enrolled-waiting">
            <span class="sq-ico"><i class="fa-solid fa-hourglass-half"></i></span>
            <span class="sq-text">Result is not yet released by your professor.</span>
          </div>`;
      } else if (ended) {
        html = `${infoHtml}<div class="sq-state sq-closed">
          <span class="sq-ico"><i class="fa-solid fa-lock"></i></span>
          <span class="sq-text">${label} is closed.</span>
        </div>`;
      } else if (isLive) {
        if (!isEnrolled) {
          html = `
            ${infoHtml}
            <div class="sq-actions">
            <button type="button" class="sq-btn sq-btn-primary" data-sq-enroll="${escapeHtml(postId)}">
                <i class="fa-solid fa-user-plus"></i> Join ${label}
              </button>
            </div>`;
        } else if (!started) {
          html = `
            ${infoHtml}
            <div class="sq-state sq-enrolled-waiting">
              <span class="sq-ico"><i class="fa-solid fa-hourglass-half"></i></span>
              <span class="sq-text"><span class="sq-dot"></span>Waiting for faculty to start the ${lower}.</span>
            </div>
            <div class="sq-actions">
              <button type="button" class="sq-btn sq-btn-ghost" data-sq-openwait="${escapeHtml(postId)}">
                <i class="fa-solid fa-person-booth"></i> Open Waiting Room
              </button>
              <button type="button" class="sq-btn sq-btn-danger" data-sq-withdraw="${escapeHtml(postId)}">
                <i class="fa-solid fa-right-from-bracket"></i> Leave ${label}
              </button>
            </div>`;
        } else if (paused) {
          html = `
            ${infoHtml}
            <div class="sq-state sq-enrolled-waiting">
              <span class="sq-ico"><i class="fa-solid fa-pause"></i></span>
              <span class="sq-text"><span class="sq-dot"></span>${label} is paused. Wait for faculty to resume.</span>
            </div>
            <div class="sq-actions">
              <button type="button" class="sq-btn sq-btn-ghost" data-sq-openwait="${escapeHtml(postId)}">
                <i class="fa-solid fa-person-booth"></i> Open Waiting Room
              </button>
              <button type="button" class="sq-btn sq-btn-danger" data-sq-withdraw="${escapeHtml(postId)}">
                <i class="fa-solid fa-right-from-bracket"></i> Leave ${label}
              </button>
            </div>`;
        } else {
          html = `
            ${infoHtml}
            <div class="sq-state sq-live-now">
              <span class="sq-ico"><i class="fa-solid fa-bolt"></i></span>
              <span class="sq-text"><span class="sq-dot"></span>Faculty already started. Take the ${lower} now.</span>
            </div>
            <div class="sq-actions">
              <button type="button" class="sq-btn sq-btn-take" data-sq-take="${escapeHtml(postId)}">
                <i class="fa-solid fa-play"></i> Take ${label} Now
              </button>
            </div>`;
        }
      } else {
        if (!dueOk) {
          html = `${infoHtml}<div class="sq-state sq-closed">
            <span class="sq-ico"><i class="fa-solid fa-lock"></i></span>
            <span class="sq-text">${label} is not currently available.</span>
          </div>`;
        } else {
          html = `
            ${infoHtml}
            <div class="sq-actions">
              <button type="button" class="sq-btn sq-btn-take" data-sq-take="${escapeHtml(postId)}">
                <i class="fa-solid fa-play"></i> Take ${label}
              </button>
            </div>`;
        }
      }

      if (slot.lastHtml !== html) {
        slot.el.innerHTML = html;
        slot.lastHtml = html;
      }
      slot.lastSig = sig;

      if (mode === 'live' && isEnrolled && started && !paused && waitingLeft && !isSubmittedAttempt && !startNotified.has(postId)) {
        startNotified.add(postId);
        try { sessionStorage.removeItem(waitingLeftKey); } catch(e) {}
        Swal.fire({
          icon: 'success',
          title: `${label} Started`,
          text: `Your faculty started the ${lower}. You can take it now.`,
          showCancelButton: true,
          confirmButtonText: `Take ${label} Now`,
          cancelButtonText: 'Later'
        }).then((r) => {
          if (r.isConfirmed) {
            const cid = <?= json_encode((string)$class_id) ?>;
            window.location.href = assessmentUrl(postId, cid, kind);
          }
        });
      }

      slot.inflight = false;
    }).catch(() => {
      // Keep last known UI on transient polling errors to avoid flicker.
      // But if first load failed and UI is empty, show a safe fallback action.
      if (!slot.lastHtml) {
        const fallbackKind = normalizeAssessmentKind(slot.el.getAttribute('data-sq-kind') || 'quiz');
        const fallbackLabel = assessmentLabel(fallbackKind);
        const html = `
          <div class="sq-state sq-closed">
            <span class="sq-ico"><i class="fa-solid fa-circle-info"></i></span>
            <span class="sq-text">Unable to load ${fallbackLabel.toLowerCase()} state right now.</span>
          </div>`;
        slot.el.innerHTML = html;
        slot.lastHtml = html;
      }
      slot.inflight = false;
    });
  }

  function attach(el) {
    const postId = el.getAttribute('data-sq-state');
    if (!postId) return;
    if (slots.has(postId)) {
      // Stream refresh can recreate the same quiz card node. Rebind to new node.
      const slot = slots.get(postId);
      slot.el = el;
      render(postId);
      return;
    }
    const timer = setInterval(() => render(postId), POLL_MS);
    slots.set(postId, { el, timer, lastHtml:'', lastSig:'', inflight:false });
    render(postId);
  }

  function scan(root) {
    (root || document).querySelectorAll('[data-sq-state]').forEach(attach);
  }

  // Re-scan whenever feed/posts are re-rendered
  const mo = new MutationObserver(muts => {
    muts.forEach(m => m.addedNodes.forEach(n => {
      if (n.nodeType === 1) {
        if (n.matches && n.matches('[data-sq-state]')) attach(n);
        scan(n);
      }
    }));
    // Clean up timers for slots whose elements were removed
    slots.forEach((slot, postId) => {
      if (!document.body.contains(slot.el)) {
        clearInterval(slot.timer);
        slots.delete(postId);
      }
    });
  });
  mo.observe(document.body, { childList: true, subtree: true });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => scan(document));
  } else {
    scan(document);
  }

  // â”€â”€ Click handlers (delegated) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
  document.addEventListener('click', async function (ev) {
    const enrollBtn   = ev.target.closest('[data-sq-enroll]');
    const withdrawBtn = ev.target.closest('[data-sq-withdraw]');
    const takeBtn     = ev.target.closest('[data-sq-take]');
    const openWaitBtn = ev.target.closest('[data-sq-openwait]');
    const viewScoreBtn = ev.target.closest('[data-sq-viewscore]');

    if (enrollBtn) {
      ev.preventDefault();
      const pid = enrollBtn.getAttribute('data-sq-enroll');
      enrollBtn.disabled = true;
      try {
        const fd = new FormData(); fd.append('post_id', pid);
        const r = await fetch('API/student/studentClassroom/quiz/enroll_quiz.php', { method:'POST', body: fd, credentials:'same-origin' });
        const j = await r.json();
        if (!j || j.success === false) throw new Error((j && j.message) || 'Failed');
        const enrolledMode = (j.data && j.data.quiz_mode) || 'live';
        const slotEl = document.querySelector(`[data-sq-state="${CSS.escape(pid)}"]`);
        const enrolledKind = normalizeAssessmentKind((j.data && j.data.post_type) || (slotEl ? slotEl.getAttribute('data-sq-kind') : 'quiz'));
        const enrolledLabel = assessmentLabel(enrolledKind);
        if (enrolledMode === 'live') {
          // Redirect to takequiz.php - the waiting screen polls until faculty starts
          const cid = (typeof CLASS_ID !== 'undefined') ? CLASS_ID : '';
          window.location.href = assessmentUrl(pid, cid, enrolledKind, true);
        } else {
          Swal.fire({ icon:'success', title:'Joined', text:`You can now take the ${enrolledLabel.toLowerCase()}.`, timer:1600, showConfirmButton:false });
          render(pid);
        }
      } catch (e) {
        const msg = String(e.message || e || '');
        if (/already\s*enroll/i.test(msg) || /already\s*joined/i.test(msg)) {
          render(pid);
          const slotEl = document.querySelector(`[data-sq-state="${CSS.escape(pid)}"]`);
          const currentLabel = assessmentLabel(slotEl ? slotEl.getAttribute('data-sq-kind') : 'quiz');
          Swal.fire({
            icon: 'info',
            title: 'Already joined',
            text: `You already joined this ${currentLabel.toLowerCase()}. If results are not released yet, please wait for your professor.`
          });
        } else {
          Swal.fire({ icon:'error', title:'Could not join', text: msg });
          enrollBtn.disabled = false;
        }
      }
    }

    if (withdrawBtn) {
      ev.preventDefault();
      const pid = withdrawBtn.getAttribute('data-sq-withdraw');
      const slotEl = document.querySelector(`[data-sq-state="${CSS.escape(pid)}"]`);
      const currentLabel = assessmentLabel(slotEl ? slotEl.getAttribute('data-sq-kind') : 'quiz');
      const ok = await Swal.fire({
        icon:'question', title:`Leave this ${currentLabel.toLowerCase()}?`,
        text:`You can join again later as long as the ${currentLabel.toLowerCase()} is still open.`,
        showCancelButton:true, confirmButtonText:'Yes, withdraw', confirmButtonColor:'#d93025'
      });
      if (!ok.isConfirmed) return;
      withdrawBtn.disabled = true;
      try {
        const fd = new FormData(); fd.append('post_id', pid);
        const r = await fetch('API/student/studentClassroom/quiz/withdraw_quiz.php', { method:'POST', body: fd, credentials:'same-origin' });
        const j = await r.json();
        if (!j || j.success === false) throw new Error((j && j.message) || 'Failed');
        render(pid);
      } catch (e) {
        Swal.fire({ icon:'error', title:'Could not leave', text:String(e.message||e) });
        withdrawBtn.disabled = false;
      }
    }

    if (openWaitBtn) {
      ev.preventDefault();
      const pid = openWaitBtn.getAttribute('data-sq-openwait');
      const cid = (typeof CLASS_ID !== 'undefined') ? CLASS_ID : '';
      const slotEl = document.querySelector(`[data-sq-state="${CSS.escape(pid)}"]`);
      window.location.href = assessmentUrl(pid, cid, slotEl ? slotEl.getAttribute('data-sq-kind') : 'quiz', true);
    }

    if (takeBtn) {
      ev.preventDefault();
      const pid = takeBtn.getAttribute('data-sq-take');
      const cid = (typeof CLASS_ID !== 'undefined') ? CLASS_ID : '';
      const slotEl = document.querySelector(`[data-sq-state="${CSS.escape(pid)}"]`);
      window.location.href = assessmentUrl(pid, cid, slotEl ? slotEl.getAttribute('data-sq-kind') : 'quiz');
    }

    if (viewScoreBtn) {
      ev.preventDefault();
      const pid = viewScoreBtn.getAttribute('data-sq-viewscore');
      const cid = (typeof CLASS_ID !== 'undefined') ? CLASS_ID : '';
      const slotEl = document.querySelector(`[data-sq-state="${CSS.escape(pid)}"]`);
      const kind = viewScoreBtn.getAttribute('data-sq-kind') || (slotEl ? slotEl.getAttribute('data-sq-kind') : 'quiz');
      const params = new URLSearchParams({ post_id: pid });
      if (cid) params.set('class_id', cid);
      params.set('kind', normalizeAssessmentKind(kind));
      window.location.href = 'quiz_result.php?' + params.toString();
    }
  });
})();
</script>


<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     QUIZ MODAL - paste this block just before </body> in studentClassroom.php
     Replaces the fallback in takeQuiz() / openQuizAttempt()
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->

<!-- Left Comments Panel -->
<div id="cmtPanelOverlay" class="cmt-panel-overlay" onclick="closeCommentsPanel()"></div>
<aside id="cmtPanel" class="cmt-panel" role="dialog" aria-modal="true" aria-label="Class comments">
  <div class="cmt-panel-head">
    <i class="fas fa-comments" style="color:var(--primary);"></i>
    <div class="cmt-panel-title" id="cmtPanelTitle">Comments</div>
    <button class="cmt-panel-close" type="button" onclick="closeCommentsPanel()"><i class="fas fa-times"></i></button>
  </div>
  <div class="cmt-panel-body">
    <div class="comment-list" id="cmtPanelList">
      <div class="cmt-empty">Select a post to view comments.</div>
    </div>
  </div>
  <div class="cmt-panel-foot">
    <div class="cmt-input-row">
      <textarea class="cmt-input" id="cmtPanelInput" placeholder="Add a class commentï¿½" rows="1" onkeydown="cmtKey(event,currentCommentPostId)" oninput="cmtResize(this)"></textarea>
      <button class="cmt-send" id="cmtPanelSend" onclick="sendComment(currentCommentPostId)"><i class="fas fa-paper-plane"></i></button>
    </div>
  </div>
</aside>
<!-- Post Details Modal with Right Comments -->
<div id="postModalOverlay" class="post-modal-overlay" onclick="if(event.target===this)closePostModal()">
  <div class="post-modal">
    <div class="post-modal-head">
      <i class="fas fa-file-lines" style="color:var(--primary);"></i>
      <div class="post-modal-title" id="postModalTitle">Post</div>
      <button class="post-modal-close" type="button" onclick="closePostModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="post-modal-body">
      <div class="post-modal-main" id="postModalMain"></div>
      <aside class="post-modal-side">
        <div class="post-modal-comments">
          <div class="comment-list" id="postModalCommentList">
            <div class="cmt-empty">No comments yet ï¿½ be the first.</div>
          </div>
        </div>
        <div class="post-modal-foot">
          <div class="cmt-input-row">
            <textarea class="cmt-input" id="postModalCommentInput" placeholder="Add a class commentï¿½" rows="1" onkeydown="cmtKey(event,currentCommentPostId)" oninput="cmtResize(this)"></textarea>
            <button class="cmt-send" id="postModalCommentSend" onclick="sendComment(currentCommentPostId)"><i class="fas fa-paper-plane"></i></button>
          </div>
        </div>
      </aside>
    </div>
  </div>
</div>
<!-- Quiz Modal Overlay -->
<div id="quizModalOverlay" style="
  display:none;position:fixed;inset:0;z-index:3000;
  background:rgba(4,10,20,.82);backdrop-filter:blur(4px);
  align-items:center;justify-content:center;padding:1rem;
">
  <div id="quizModal" style="
    background:var(--surface);border-radius:18px;
    box-shadow:0 24px 80px rgba(0,0,0,.35);
    width:min(820px,80vw);max-height:82vh;
    display:flex;flex-direction:column;overflow:hidden;
    border:1px solid var(--border);
    transform:translateY(20px) scale(.97);
    transition:transform .25s cubic-bezier(.4,0,.2,1);
  ">

    <!-- Header -->
    <div id="qmHeader" style="
      display:flex;align-items:center;gap:.75rem;
      padding:.9rem 1.3rem;
      background:linear-gradient(135deg,var(--primary),var(--primary-dark));
      color:#fff;flex-shrink:0;
    ">
      <div style="width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fas fa-clipboard-question" style="font-size:.9rem;"></i>
      </div>
      <div style="flex:1;min-width:0;">
        <div id="qmTitle" style="font-size:1rem;font-weight:800;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
        <div id="qmMeta" style="font-size:.7rem;opacity:.82;margin-top:.1rem;"></div>
      </div>
      <!-- Whole-quiz timer (minutes) -->
      <div id="qmGlobalTimer" style="display:none;align-items:center;gap:.4rem;background:rgba(255,255,255,.15);border-radius:10px;padding:.35rem .75rem;font-size:.88rem;font-weight:700;">
        <i class="fas fa-clock" style="font-size:.78rem;opacity:.8;"></i>
        <span id="qmGlobalTimerLabel">--:--</span>
      </div>
      <button id="qmCloseBtn" style="
        width:30px;height:30px;border:none;border-radius:8px;
        background:rgba(255,255,255,.15);color:#fff;cursor:pointer;font-size:.85rem;
        display:flex;align-items:center;justify-content:center;
        transition:background .15s;flex-shrink:0;
      " title="Close"><i class="fas fa-times"></i></button>
    </div>

    <!-- Progress bar -->
    <div style="height:3px;background:var(--border);flex-shrink:0;">
      <div id="qmProgressBar" style="height:100%;background:var(--primary);transition:width .3s;width:0%;"></div>
    </div>

    <!-- Question counter + per-question timer -->
    <div id="qmSubBar" style="
      display:flex;align-items:center;justify-content:space-between;
      padding:.55rem 1.3rem;background:var(--bg);border-bottom:1px solid var(--border);
      font-size:.78rem;font-weight:600;color:var(--text-muted);flex-shrink:0;
    ">
      <span id="qmQCounter">Question 1 of -</span>
      <div id="qmPerQTimer" style="display:none;align-items:center;gap:.35rem;color:var(--primary);font-weight:700;">
        <i class="fas fa-stopwatch" style="font-size:.72rem;"></i>
        <span id="qmPerQTimerLabel">--s</span>
      </div>
    </div>

    <!-- Body: question + choices -->
    <div id="qmBody" style="flex:1;overflow-y:auto;padding:1.4rem 1.5rem;">

      <!-- Loading state -->
      <div id="qmLoading" style="text-align:center;padding:3rem 0;color:var(--text-muted);">
        <div style="width:36px;height:36px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:sp .7s linear infinite;margin:0 auto 1rem;"></div>
        <div style="font-size:.88rem;">Loading quiz...</div>
      </div>

      <!-- Question -->
      <div id="qmQuestion" style="display:none;">
        <div id="qmQText" style="
          font-size:1.05rem;font-weight:700;line-height:1.55;
          color:var(--text);margin-bottom:1.1rem;
        "></div>
        <div id="qmChoices" style="display:flex;flex-direction:column;gap:.55rem;"></div>
        <div id="qmSkipNote" style="
          margin-top:.85rem;font-size:.72rem;color:var(--text-muted);
          display:flex;align-items:center;gap:.35rem;
        ">
          <i class="fas fa-info-circle"></i> You can leave a question blank and come back later.
        </div>
      </div>

      <!-- Result screen -->
      <div id="qmResult" style="display:none;text-align:center;padding:1.5rem 0;">
        <div id="qmResultIcon" style="font-size:3rem;margin-bottom:.75rem;"></div>
        <div id="qmResultTitle" style="font-size:1.3rem;font-weight:800;margin-bottom:.3rem;"></div>
        <div id="qmResultSub" style="font-size:.88rem;color:var(--text-muted);margin-bottom:1.2rem;"></div>
        <div id="qmScoreBox" style="
          display:inline-block;background:var(--primary-light);
          border:2px solid rgba(121,85,72,.3);border-radius:16px;
          padding:1rem 2.5rem;margin-bottom:1rem;
        ">
          <div style="font-size:2.4rem;font-weight:800;color:var(--primary);" id="qmScoreVal"></div>
          <div style="font-size:.75rem;font-weight:600;color:var(--text-muted);">SCORE</div>
        </div>
        <div id="qmLateTag" style="display:none;margin-top:.4rem;">
          <span style="background:#fdecea;color:#d93025;border:1px solid #f5c2c7;border-radius:20px;padding:.2rem .75rem;font-size:.75rem;font-weight:700;">
            <i class="fas fa-clock"></i> Submitted Late
          </span>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div id="qmFooter" style="
      display:flex;align-items:center;justify-content:space-between;
      padding:.85rem 1.3rem;border-top:1px solid var(--border);
      background:var(--surface);flex-shrink:0;gap:.6rem;
    ">
      <button id="qmPrevBtn" style="
        padding:.55rem 1.1rem;border-radius:10px;
        border:1.5px solid var(--border);background:var(--bg);
        color:var(--text-muted);font-size:.84rem;font-weight:700;cursor:pointer;
        display:flex;align-items:center;gap:.4rem;transition:all .15s;
      "><i class="fas fa-chevron-left"></i> Prev</button>

      <!-- Dot navigator -->
      <div id="qmDots" style="display:flex;gap:.35rem;flex-wrap:wrap;justify-content:center;flex:1;max-width:420px;"></div>

      <div style="display:flex;gap:.5rem;align-items:center;">
        <button id="qmNextBtn" style="
          padding:.55rem 1.1rem;border-radius:10px;
          background:var(--primary);color:#fff;
          border:none;font-size:.84rem;font-weight:700;cursor:pointer;
          display:flex;align-items:center;gap:.4rem;transition:all .15s;
          box-shadow:0 2px 10px rgba(121,85,72,.28);
        ">Next <i class="fas fa-chevron-right"></i></button>

        <button id="qmSubmitBtn" style="
          display:none;padding:.55rem 1.3rem;border-radius:10px;
          background:#d93025;color:#fff;
          border:none;font-size:.84rem;font-weight:700;cursor:pointer;
          display:none;align-items:center;gap:.4rem;transition:all .15s;
          box-shadow:0 2px 10px rgba(217,48,37,.3);
        "><i class="fas fa-paper-plane"></i> Submit Quiz</button>
      </div>
    </div>

  </div>
</div>

<script>
/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   QUIZ MODAL CONTROLLER
   â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
(function () {
  const QM = {
    overlay:     document.getElementById('quizModalOverlay'),
    modal:       document.getElementById('quizModal'),
    title:       document.getElementById('qmTitle'),
    meta:        document.getElementById('qmMeta'),
    closeBtn:    document.getElementById('qmCloseBtn'),
    progressBar: document.getElementById('qmProgressBar'),
    qCounter:    document.getElementById('qmQCounter'),
    body:        document.getElementById('qmBody'),
    loading:     document.getElementById('qmLoading'),
    question:    document.getElementById('qmQuestion'),
    qText:       document.getElementById('qmQText'),
    choices:     document.getElementById('qmChoices'),
    skipNote:    document.getElementById('qmSkipNote'),
    result:      document.getElementById('qmResult'),
    prevBtn:     document.getElementById('qmPrevBtn'),
    nextBtn:     document.getElementById('qmNextBtn'),
    submitBtn:   document.getElementById('qmSubmitBtn'),
    dots:        document.getElementById('qmDots'),
    // timers
    globalTimerWrap:  document.getElementById('qmGlobalTimer'),
    globalTimerLabel: document.getElementById('qmGlobalTimerLabel'),
    perQTimerWrap:    document.getElementById('qmPerQTimer'),
    perQTimerLabel:   document.getElementById('qmPerQTimerLabel'),
  };

  let state = {
    attemptId: null,
    postId: null,
    questions: [],       // [{id, question, points, time_limit_seconds, choices:[]}]
    answers: {},         // {question_id: choice_id}
    current: 0,
    submitting: false,
    // global timer
    globalSecs: 0,       // 0 = no limit
    globalRemaining: 0,
    globalTimer: null,
    // per-question timer
    perQSecs: 0,         // per-question default seconds (0 = no limit)
    perQRemaining: 0,
    perQTimer: null,
  };

  const API = 'API/student/studentClassroom/quiz/';

  /* â”€â”€ helpers â”€â”€ */
  function esc(v){ return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  function fmtSecs(s) {
    // for global timer: show MM:SS
    s = Math.max(0, Math.round(s));
    const m = Math.floor(s / 60);
    const ss = s % 60;
    return String(m).padStart(2,'0') + ':' + String(ss).padStart(2,'0');
  }

  function fmtPerQ(s) {
    // for per-question timer: show Xs
    s = Math.max(0, Math.round(s));
    return s + 's';
  }

  /* â”€â”€ open â”€â”€ */
  window.openQuizAttempt = async function(postId) {
    state.postId = postId;
    resetState();
    showOverlay(true);
    showScreen('loading');

    try {
      const fd = new FormData();
      fd.append('post_id', postId);
      const res = await fetch(API + 'start_attempt.php', { method:'POST', body: fd, credentials:'same-origin' });
      const j   = await res.json();

      if (!j || j.status !== 'success') {
        Swal.fire({ icon:'error', title:'Cannot start quiz', text: j?.message || 'Failed to start.' });
        closeModal();
        return;
      }

      const { attempt, post, questions, answers } = j;

      // Already submitted - block retake
      if (attempt.status === 'submitted') {
        Swal.fire({ icon:'info', title:'Already submitted', text:'You have already submitted this quiz. Retakes are not allowed.' });
        closeModal();
        return;
      }

      state.attemptId = attempt.id;
      state.questions = questions || [];
      state.answers   = {};
      // Restore saved answers
      if (answers) {
        Object.keys(answers).forEach(qid => {
          state.answers[qid] = answers[qid].selected_choice_id || null;
        });
      }
      state.current = 0;
      // Seek to first unanswered on resume
      const firstUnanswered = state.questions.findIndex(q => !state.answers[q.id]);
      if (firstUnanswered > 0) state.current = firstUnanswered;

      QM.title.textContent = post.title || 'Quiz';

      // â”€â”€ Time mode â”€â”€
      // time_mode: 'no_limit' | 'whole_quiz' | 'per_question'
      // time_limit_seconds: for whole_quiz = minutes * 60, for per_question = seconds per q
      const tmode = post.time_mode || 'no_limit';
      const tlSecs = parseInt(post.time_limit_seconds, 10) || 0;

      state.globalSecs = 0;
      state.perQSecs   = 0;

      if (tmode === 'whole_quiz' && tlSecs > 0) {
        // tlSecs here is total seconds (stored as minutes*60 by faculty)
        state.globalSecs = tlSecs;
        const elapsed = attempt.started_at
          ? Math.floor((Date.now() - new Date(attempt.started_at).getTime()) / 1000)
          : 0;
        state.globalRemaining = Math.max(0, tlSecs - elapsed);
        QM.meta.textContent = `${state.questions.length} questions Â· ${Math.round(tlSecs/60)} min limit`;
        startGlobalTimer();
      } else if (tmode === 'per_question' && tlSecs > 0) {
        // tlSecs is seconds per question
        state.perQSecs = tlSecs;
        QM.meta.textContent = `${state.questions.length} questions Â· ${tlSecs}s per question`;
      } else {
        QM.meta.textContent = `${state.questions.length} questions Â· No time limit`;
      }

      renderDots();
      showScreen('question');
      renderQuestion(state.current);

    } catch(e) {
      console.error(e);
      Swal.fire({ icon:'error', title:'Error', text:'Could not load quiz. Please try again.' });
      closeModal();
    }
  };

  /* â”€â”€ global timer (whole-quiz, minutes displayed as MM:SS) â”€â”€ */
  function startGlobalTimer() {
    clearInterval(state.globalTimer);
    QM.globalTimerWrap.style.display = 'flex';
    updateGlobalTimerLabel();
    state.globalTimer = setInterval(() => {
      state.globalRemaining--;
      updateGlobalTimerLabel();
      if (state.globalRemaining <= 0) {
        clearInterval(state.globalTimer);
        autoSubmit('Time is up!');
      }
    }, 1000);
  }

  function updateGlobalTimerLabel() {
    const s = state.globalRemaining;
    QM.globalTimerLabel.textContent = fmtSecs(s);
    // Turn red when < 60s
    QM.globalTimerWrap.style.background = s < 60
      ? 'rgba(217,48,37,.35)'
      : 'rgba(255,255,255,.15)';
  }

  /* â”€â”€ per-question timer (seconds) â”€â”€ */
  function startPerQTimer() {
    clearInterval(state.perQTimer);
    if (!state.perQSecs) { QM.perQTimerWrap.style.display = 'none'; return; }
    state.perQRemaining = state.perQSecs;
    QM.perQTimerWrap.style.display = 'flex';
    updatePerQLabel();
    state.perQTimer = setInterval(() => {
      state.perQRemaining--;
      updatePerQLabel();
      if (state.perQRemaining <= 0) {
        clearInterval(state.perQTimer);
        // Auto-advance to next question (or submit if last)
        if (state.current < state.questions.length - 1) {
          goTo(state.current + 1);
        } else {
          autoSubmit('Time is up for the last question!');
        }
      }
    }, 1000);
  }

  function updatePerQLabel() {
    const s = state.perQRemaining;
    QM.perQTimerLabel.textContent = fmtPerQ(s);
    QM.perQTimerWrap.style.color = s <= 5 ? '#d93025' : 'var(--primary)';
  }

  /* â”€â”€ render â”€â”€ */
  function renderQuestion(idx) {
    const q = state.questions[idx];
    if (!q) return;

    state.current = idx;

    // progress
    const pct = state.questions.length > 1
      ? Math.round((idx / (state.questions.length - 1)) * 100)
      : 100;
    QM.progressBar.style.width = pct + '%';
    QM.qCounter.textContent = `Question ${idx + 1} of ${state.questions.length}`;

    QM.qText.textContent = q.question;

    // choices
    QM.choices.innerHTML = '';
    (q.choices || []).forEach(c => {
      const selected = state.answers[q.id] === c.id;
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.dataset.choiceId = c.id;
      btn.style.cssText = `
        width:100%;text-align:left;padding:.75rem 1rem;
        border-radius:12px;font-family:inherit;font-size:.9rem;font-weight:500;cursor:pointer;
        border:2px solid ${selected ? 'var(--primary)' : 'var(--border)'};
        background:${selected ? 'var(--primary-light)' : 'var(--surface)'};
        color:${selected ? 'var(--primary)' : 'var(--text)'};
        transition:all .15s;display:flex;align-items:center;gap:.75rem;
      `;
      const circle = document.createElement('span');
      circle.style.cssText = `
        width:20px;height:20px;border-radius:50%;flex-shrink:0;
        border:2px solid ${selected ? 'var(--primary)' : 'var(--border)'};
        background:${selected ? 'var(--primary)' : 'transparent'};
        display:flex;align-items:center;justify-content:center;transition:all .15s;
      `;
      if (selected) {
        circle.innerHTML = '<i class="fas fa-check" style="font-size:.52rem;color:#fff;"></i>';
      }
      btn.appendChild(circle);
      const label = document.createElement('span');
      label.textContent = c.choice_text;
      btn.appendChild(label);

      btn.onclick = () => selectChoice(q.id, c.id);
      QM.choices.appendChild(btn);
    });

    // nav buttons
    QM.prevBtn.style.display = idx > 0 ? 'flex' : 'none';
    const isLast = idx === state.questions.length - 1;
    QM.nextBtn.style.display = isLast ? 'none' : 'flex';
    QM.submitBtn.style.display = isLast ? 'flex' : 'none';

    updateDots();
    startPerQTimer();
  }

  function selectChoice(qid, cid) {
    const prev = state.answers[qid];
    state.answers[qid] = prev === cid ? null : cid; // toggle off if same
    // Save to server (fire-and-forget)
    const fd = new FormData();
    fd.append('attempt_id', state.attemptId);
    fd.append('question_id', qid);
    fd.append('choice_id', state.answers[qid] || '');
    fetch(API + 'save_answer.php', { method:'POST', body: fd, credentials:'same-origin' });

    renderQuestion(state.current); // re-render for visual update
  }

  function renderDots() {
    QM.dots.innerHTML = '';
    state.questions.forEach((q, i) => {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.dataset.idx = i;
      dot.title = `Question ${i+1}`;
      dot.style.cssText = `
        width:26px;height:26px;border-radius:50%;border:2px solid var(--border);
        background:var(--bg);font-size:.65rem;font-weight:700;cursor:pointer;
        transition:all .15s;color:var(--text-muted);
      `;
      dot.textContent = i + 1;
      dot.onclick = () => goTo(i);
      QM.dots.appendChild(dot);
    });
  }

  function updateDots() {
    QM.dots.querySelectorAll('button').forEach((dot, i) => {
      const q = state.questions[i];
      const answered = !!state.answers[q?.id];
      const isCurrent = i === state.current;
      dot.style.background   = isCurrent ? 'var(--primary)' : answered ? 'var(--primary-light)' : 'var(--bg)';
      dot.style.color        = isCurrent ? '#fff' : answered ? 'var(--primary)' : 'var(--text-muted)';
      dot.style.borderColor  = isCurrent ? 'var(--primary)' : answered ? 'var(--primary)' : 'var(--border)';
      dot.style.transform    = isCurrent ? 'scale(1.15)' : 'scale(1)';
    });
  }

  function goTo(idx) {
    clearInterval(state.perQTimer);
    renderQuestion(idx);
  }

  /* â”€â”€ submit â”€â”€ */
  async function doSubmit() {
    if (state.submitting) return;
    state.submitting = true;
    clearInterval(state.globalTimer);
    clearInterval(state.perQTimer);

    const unanswered = state.questions.filter(q => !state.answers[q.id]).length;
    const total = state.questions.length;

    if (unanswered > 0) {
      const ok = await Swal.fire({
        icon: 'warning',
        title: `${unanswered} unanswered question${unanswered > 1 ? 's' : ''}`,
        text: `You have ${unanswered} of ${total} questions unanswered. Submit anyway?`,
        showCancelButton: true,
        confirmButtonText: 'Submit anyway',
        confirmButtonColor: '#d93025',
        cancelButtonText: 'Go back'
      });
      if (!ok.isConfirmed) {
        state.submitting = false;
        // Restart timers if needed
        if (state.globalSecs) startGlobalTimer();
        if (state.perQSecs)   startPerQTimer();
        return;
      }
    }

    QM.submitBtn.disabled = true;
    QM.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    try {
      const fd = new FormData();
      fd.append('attempt_id', state.attemptId);
      const res = await fetch(API + 'submit_attempt.php', { method:'POST', body: fd, credentials:'same-origin' });
      const j   = await res.json();

      if (j && j.status === 'success') {
        showResultScreen(j.auto_score, j.max_score, j.is_late);
        // Update the post card in classroom so it shows submitted
        refreshPostCard(state.postId);
      } else {
        throw new Error(j?.message || 'Submit failed');
      }
    } catch(e) {
      Swal.fire({ icon:'error', title:'Submission failed', text: e.message });
      QM.submitBtn.disabled = false;
      QM.submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Quiz';
      state.submitting = false;
    }
  }

  function autoSubmit(msg) {
    Swal.fire({ icon:'warning', title:msg, text:'Your quiz is being submitted automatically.', timer:2200, showConfirmButton:false });
    setTimeout(doSubmit, 2200);
  }

  function showResultScreen(score, max, isLate) {
    showScreen('result');
    const pct = (Number(max) > 0) ? (Number(score) / Number(max)) * 100 : null;
    document.getElementById('qmResultIcon').innerHTML = '<i class="fas fa-clipboard-check"></i>';
    document.getElementById('qmResultTitle').textContent = 'Quiz submitted!';
    document.getElementById('qmResultSub').textContent = 'Your answers have been recorded.';
    document.getElementById('qmScoreVal').textContent = `${Math.round(Number(score || 0))}/${Math.round(Number(max || 0))}`;
    const qmScoreEl = document.getElementById('qmScoreVal');
    const qmScoreBox = document.getElementById('qmScoreBox');
    if (qmScoreEl) {
      let col = 'var(--text-muted)';
      if (pct !== null) {
        if (pct > 50) col = '#795548';
        else if (pct === 50) col = '#f59e0b';
        else col = '#d93025';
      }
      qmScoreEl.style.color = col;
      if (qmScoreBox) qmScoreBox.style.borderColor = col;
    }
    if (isLate) document.getElementById('qmLateTag').style.display = 'block';

    // Footer: only close button
    QM.prevBtn.style.display   = 'none';
    QM.nextBtn.style.display   = 'none';
    QM.submitBtn.style.display = 'none';
    const closeResultBtn = document.createElement('button');
    closeResultBtn.style.cssText = `
      padding:.55rem 1.5rem;border-radius:10px;
      background:var(--primary);color:#fff;
      border:none;font-size:.85rem;font-weight:700;cursor:pointer;
      margin-left:auto;
    `;
    closeResultBtn.innerHTML = '<i class="fas fa-check"></i> Done';
    closeResultBtn.onclick = closeModal;
    document.getElementById('qmFooter').appendChild(closeResultBtn);
  }

  function refreshPostCard(postId) {
    // Trigger a classroom refresh so the card updates to "submitted"
    if (typeof refreshStream === 'function') {
      setTimeout(refreshStream, 600);
    }
  }

  /* â”€â”€ screens â”€â”€ */
  function showScreen(s) {
    QM.loading.style.display  = s === 'loading'  ? 'block' : 'none';
    QM.question.style.display = s === 'question' ? 'block' : 'none';
    QM.result.style.display   = s === 'result'   ? 'block' : 'none';
    if (s === 'loading') {
      QM.prevBtn.style.display   = 'none';
      QM.nextBtn.style.display   = 'none';
      QM.submitBtn.style.display = 'none';
      QM.dots.innerHTML = '';
    }
  }

  function showOverlay(yes) {
    QM.overlay.style.display = yes ? 'flex' : 'none';
    if (yes) {
      document.body.style.overflow = 'hidden';
      setTimeout(() => { QM.modal.style.transform = 'none'; }, 10);
    } else {
      QM.modal.style.transform = 'translateY(20px) scale(.97)';
      document.body.style.overflow = '';
    }
  }

  function closeModal() {
    clearInterval(state.globalTimer);
    clearInterval(state.perQTimer);
    showOverlay(false);
    // Remove any injected done-button
    const doneBtn = document.getElementById('qmFooter')?.querySelector('button:last-child');
    if (doneBtn && doneBtn.textContent.includes('Done')) doneBtn.remove();
  }

  function resetState() {
    clearInterval(state.globalTimer);
    clearInterval(state.perQTimer);
    QM.globalTimerWrap.style.display = 'none';
    QM.perQTimerWrap.style.display   = 'none';
    QM.globalTimerLabel.textContent  = '--:--';
    QM.perQTimerLabel.textContent    = '--s';
    QM.progressBar.style.width = '0%';
    QM.dots.innerHTML = '';
    QM.meta.textContent = '';
    state = { ...state,
      attemptId: null, questions: [], answers: {}, current: 0,
      submitting: false,
      globalSecs: 0, globalRemaining: 0,
      perQSecs: 0,   perQRemaining: 0,
    };
    // remove stale done button
    const footer = document.getElementById('qmFooter');
    footer.querySelectorAll('button').forEach(b => {
      if (b.textContent.includes('Done')) b.remove();
    });
    QM.submitBtn.disabled = false;
    QM.submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Quiz';
  }

  /* â”€â”€ nav wiring â”€â”€ */
  QM.prevBtn.onclick   = () => { if (state.current > 0) goTo(state.current - 1); };
  QM.nextBtn.onclick   = () => { if (state.current < state.questions.length - 1) goTo(state.current + 1); };
  QM.submitBtn.onclick = doSubmit;
  QM.closeBtn.onclick  = async () => {
    if (state.submitting) return;
    if (state.attemptId && state.questions.length > 0 && document.getElementById('qmResult').style.display === 'none') {
      const ok = await Swal.fire({
        icon:'question', title:'Leave quiz?',
        text:'Your progress is saved. You can resume later.',
        showCancelButton:true,
        confirmButtonText:'Leave',
        confirmButtonColor:'#d93025'
      });
      if (!ok.isConfirmed) return;
    }
    closeModal();
  };
  QM.overlay.onclick = e => { if (e.target === QM.overlay) QM.closeBtn.click(); };

})();
</script>


<!-- Score Viewer Modal -->
<div id="scoreModalOverlay" style="display:none;position:fixed;inset:0;z-index:3100;background:rgba(4,10,20,.82);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:1rem;">
  <div id="scoreModal" style="background:var(--surface);border-radius:18px;box-shadow:0 24px 80px rgba(0,0,0,.35);width:min(760px,96vw);max-height:88vh;display:flex;flex-direction:column;overflow:hidden;border:1px solid var(--border);transform:translateY(20px) scale(.97);transition:transform .25s cubic-bezier(.4,0,.2,1);">
    <div style="display:flex;align-items:center;gap:.75rem;padding:.9rem 1.3rem;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;">
      <div style="width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-star" style="font-size:.9rem;"></i></div>
      <div style="flex:1;font-size:1rem;font-weight:800;">Your Quiz Result</div>
      <button id="scoreModalClose" style="width:30px;height:30px;border:none;border-radius:8px;background:rgba(255,255,255,.15);color:#fff;cursor:pointer;font-size:.85rem;display:flex;align-items:center;justify-content:center;"><i class="fas fa-times"></i></button>
    </div>

    <div id="scoreModalBody" style="padding:1rem 1rem 0;overflow:auto;">
      <div id="scoreModalLoading" style="color:var(--text-muted);font-size:.9rem;padding:2rem 1rem;text-align:center;">
        <div style="width:32px;height:32px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:sp .7s linear infinite;margin:0 auto 1rem;"></div>
        Loading your score...
      </div>
      <div id="scoreModalError" style="display:none;color:#d93025;font-size:.88rem;padding:1rem 0;text-align:center;"></div>
      <div id="scoreModalResult" style="display:none;">
        <div style="text-align:center;padding:.5rem 0 1rem;">
          <div id="scoreModalIcon" style="font-size:2.2rem;margin-bottom:.35rem;"></div>
          <div id="scoreModalTitle" style="font-size:1.08rem;font-weight:800;margin-bottom:.15rem;"></div>
          <div id="scoreModalPct" style="font-size:.88rem;color:var(--text-muted);margin-bottom:.6rem;"></div>
          <div id="scoreModalBox" style="display:inline-block;background:var(--primary-light);border:2px solid rgba(121,85,72,.3);border-radius:14px;padding:.75rem 1.8rem;">
            <div id="scoreModalVal" style="font-size:1.9rem;font-weight:800;color:var(--primary);"></div>
            <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);"></div>
          </div>
        </div>

        <div style="display:flex;justify-content:center;padding:.2rem 0 .65rem;">
          <div style="display:flex;align-items:center;justify-content:center;gap:.55rem;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:24px;padding:.7rem .75rem;width:min(100%,680px);overflow:visible;">
            <button id="scorePrevBtn" type="button" aria-label="Previous question" style="width:40px;height:40px;border:none;border-radius:10px;background:transparent;color:#111827;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-angle-left"></i></button>
            <div style="display:flex;align-items:center;justify-content:center;gap:.45rem;white-space:nowrap;min-width:0;flex:1;">
              <span style="font-size:1.05rem;font-weight:800;color:#111827;">Question</span>
              <input id="scoreSearchInput" type="text" inputmode="numeric" pattern="[0-9]*" aria-label="Question number" style="width:70px;height:38px;border:2px solid #60a5fa;border-radius:12px;background:#fff;text-align:center;font-size:1.35rem;font-weight:700;color:#111827;outline:none;">
            </div>
            <button id="scoreNextBtn" type="button" aria-label="Next question" style="width:40px;height:40px;border:none;border-radius:10px;background:transparent;color:#111827;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-angle-right"></i></button>
          </div>
        </div>
        <div id="scoreReviewCard" style="border:1px solid var(--border);border-radius:12px;background:var(--bg);padding:.8rem;"></div>
      </div>
    </div>

    <div style="padding:.85rem 1.3rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;">
      <button id="scoreModalDoneBtn" style="padding:.55rem 1.5rem;border-radius:10px;background:var(--primary);color:#fff;border:none;font-size:.85rem;font-weight:700;cursor:pointer;"><i class="fas fa-check"></i> Done</button>
    </div>
  </div>
</div>

<script>
(function () {
  const overlay  = document.getElementById('scoreModalOverlay');
  const modal    = document.getElementById('scoreModal');
  const loading  = document.getElementById('scoreModalLoading');
  const result   = document.getElementById('scoreModalResult');
  const errorEl  = document.getElementById('scoreModalError');
  const searchEl = document.getElementById('scoreSearchInput');
  const prevBtn  = document.getElementById('scorePrevBtn');
  const nextBtn  = document.getElementById('scoreNextBtn');
  const cardEl   = document.getElementById('scoreReviewCard');
  let reviewRows = [];
  let reviewFiltered = [];
  let reviewIdx = 0;

  function esc(s){return String(s==null?'':s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
  function idxSafe(n, max){ return Math.max(0, Math.min(max, n)); }

  function renderReviewCard() {
    if (!reviewFiltered.length) {
      if (searchEl) searchEl.value = '';
      cardEl.innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:1.2rem 0;">No question to display.</div>';
      prevBtn.disabled = true; nextBtn.disabled = true;
      return;
    }
    reviewIdx = idxSafe(reviewIdx, reviewFiltered.length - 1);
    const row = reviewFiltered[reviewIdx];
    const isCorrect = row.is_correct === true;
    const statusBg = isCorrect ? '#e8f7ef' : '#fdecea';
    const statusColor = isCorrect ? '#0b7a4f' : '#b42318';
    const statusLabel = isCorrect ? 'Correct' : 'Wrong';
    if (searchEl) searchEl.value = String(reviewIdx + 1);
    prevBtn.disabled = reviewIdx <= 0;
    nextBtn.disabled = reviewIdx >= reviewFiltered.length - 1;

    const choicesHtml = (row.choices || []).map((c, i) => {
      const isSel = row.selected_choice_id && String(c.id) === String(row.selected_choice_id);
      const ok = !!c.is_correct;
      const bg = ok ? '#e8f7ef' : (isSel ? '#fff4e5' : 'var(--surface)');
      const bd = ok ? '#66c39a' : (isSel ? '#f2b24f' : 'var(--border)');
      const mk = ok ? '<span style="font-size:.7rem;color:#0b7a4f;font-weight:700;">Correct</span>' : (isSel ? '<span style="font-size:.7rem;color:#9a6700;font-weight:700;">Your answer</span>' : '');
      return `<div style="display:flex;align-items:center;gap:.55rem;padding:.5rem .6rem;border:1px solid ${bd};border-radius:8px;background:${bg};margin-top:.35rem;">
        <div style="width:22px;height:22px;border-radius:50%;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;">${String.fromCharCode(65+i)}</div>
        <div style="flex:1;font-size:.84rem;">${esc(c.text || '')}</div>${mk}
      </div>`;
    }).join('');

    cardEl.innerHTML = `
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.6rem;flex-wrap:wrap;">
        <div style="font-size:.92rem;font-weight:700;line-height:1.45;">${esc(row.question || '')}</div>
        <span style="display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:999px;background:${statusBg};color:${statusColor};font-size:.73rem;font-weight:800;">${statusLabel}</span>
      </div>
      <div style="margin-top:.6rem;">${choicesHtml}</div>
      </div>`;
  }

  function close() {
    overlay.style.display = 'none';
    modal.style.transform = 'translateY(20px) scale(.97)';
    if (searchEl) searchEl.value = '';
    reviewRows = [];
    reviewFiltered = [];
    reviewIdx = 0;
  }

  document.getElementById('scoreModalClose').onclick = close;
  document.getElementById('scoreModalDoneBtn').onclick = close;
  overlay.onclick = e => { if (e.target === overlay) close(); };

  window.openScoreModal = async function (postId) {
    overlay.style.display = 'flex';
    setTimeout(() => { modal.style.transform = 'none'; }, 10);
    loading.style.display = 'block';
    result.style.display  = 'none';
    errorEl.style.display = 'none';
    errorEl.textContent = '';

    try {
      const fd = new FormData();
      fd.append('post_id', postId);
      const res = await fetch('API/student/studentClassroom/quiz/get_my_result.php', {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const j = await res.json();

      if (!j || j.success === false) throw new Error(j?.message || 'Could not load result.');

      const score = parseFloat(j.score ?? 0);
      const max   = parseFloat(j.max_score ?? 0);
      const pct   = (max > 0) ? (score / max) * 100 : null;
      reviewRows = Array.isArray(j.review) ? j.review : [];
      reviewFiltered = reviewRows.slice();
      reviewIdx = 0;

      document.getElementById('scoreModalIcon').innerHTML  = '<i class="fas fa-clipboard-check" style="color:#6b7280;"></i>';
      document.getElementById('scoreModalTitle').textContent = 'Quiz Result';
      document.getElementById('scoreModalVal').textContent   = `${Math.round(Number(score || 0))}/${Math.round(Number(max || 0))}`;
      document.getElementById('scoreModalPct').textContent   = '';
      const smVal = document.getElementById('scoreModalVal');
      const smBox = document.getElementById('scoreModalBox');
      if (smVal) {
        let col = 'var(--text-muted)';
        if (pct !== null) {
          if (pct > 50) col = '#795548';
          else if (pct === 50) col = '#f59e0b';
          else col = '#d93025';
        }
        smVal.style.color = col;
        if (smBox) smBox.style.borderColor = col;
      }
      renderReviewCard();

      loading.style.display = 'none';
      result.style.display  = 'block';
    } catch (e) {
      loading.style.display  = 'none';
      errorEl.style.display  = 'block';
      errorEl.textContent    = e.message;
    }
  };

  searchEl.addEventListener('input', function () {
    const raw = String(searchEl.value || '').replace(/[^\d]/g, '');
    const question = parseInt(raw || '1', 10);
    if (!reviewFiltered.length) return;
    reviewIdx = idxSafe(question - 1, reviewFiltered.length - 1);
    renderReviewCard();
  });
  prevBtn.onclick = function(){ if (reviewIdx > 0) { reviewIdx--; renderReviewCard(); } };
  nextBtn.onclick = function(){ if (reviewIdx < reviewFiltered.length - 1) { reviewIdx++; renderReviewCard(); } };
})();
</script>

</body>
</html>





