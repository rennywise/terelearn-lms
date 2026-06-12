<?php
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php');
    exit;
}

$post_id = trim($_GET['post_id'] ?? '');
$class_id = trim($_GET['class_id'] ?? '');
if ($post_id === '') {
    header('Location: ' . TERELEARN_BASE_URL . 'student.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lesson - TERELEARN</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{
  --primary:#1a9e78;--primary-dark:#0d7a5e;--primary-light:#e6f7f2;--primary-mid:rgba(26,158,120,.15);
  --border:#e8eaed;--text:#1c2027;--text-muted:#5f6368;--bg:#f4f6f9;--surface:#fff;
  --shadow:0 2px 12px rgba(0,0,0,.07);--radius:14px;--nav-h:60px;--trans:.22s cubic-bezier(.4,0,.2,1);
}
body.dark{--primary:#2ecc9a;--primary-dark:#1a9e78;--primary-light:rgba(46,204,154,.12);--border:#2e3849;--text:#e4ecf7;--text-muted:#8a9ab5;--bg:#0f1724;--surface:#182030;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
.topbar{position:fixed;inset:0 0 auto 0;height:var(--nav-h);background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.75rem;padding:0 1.4rem;z-index:50;box-shadow:var(--shadow)}
.back-btn,.icon-btn{width:36px;height:36px;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:1rem;display:flex;align-items:center;justify-content:center;border-radius:10px;text-decoration:none}
.back-btn:hover,.icon-btn:hover{background:var(--border);color:var(--text)}
.brand{display:flex;align-items:center;gap:.55rem;font-size:.95rem;font-weight:800;color:var(--text);text-decoration:none;white-space:nowrap}
.blogo{width:32px;height:32px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:9px;color:#fff;font-size:.82rem;display:flex;align-items:center;justify-content:center}
.topbar-title{font-size:.9rem;font-weight:700;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0}
.topbar-right{margin-left:auto}
.hero{margin-top:var(--nav-h);background:linear-gradient(135deg,#1a9e78 0%,#0d47a1 100%);padding:1.5rem 1.4rem;position:relative;overflow:hidden}
.hero:before{content:'';position:absolute;inset:0;background:rgba(0,0,0,.18)}
.hero-inner{position:relative;z-index:1;max-width:1180px;margin:0 auto;display:flex;align-items:flex-start;gap:1.25rem}
.hero-icon{width:64px;height:64px;border-radius:16px;background:rgba(255,255,255,.18);border:1.5px solid rgba(255,255,255,.32);color:#fff;font-size:1.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.eyebrow{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.7);margin-bottom:.35rem}
.hero-title{font-size:1.55rem;font-weight:800;color:#fff;line-height:1.2}
.hero-sub{font-size:.9rem;color:rgba(255,255,255,.84);margin-top:.22rem}
.hero-chip{display:inline-flex;align-items:center;gap:.35rem;margin-top:.85rem;font-size:.74rem;font-weight:800;padding:.3rem .75rem;border-radius:999px;background:rgba(255,255,255,.18);color:#fff;text-transform:uppercase}
.tabs-bar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 1.5rem}
.tab-inner{max-width:1180px;margin:0 auto;display:flex}
.tab-btn{padding:1rem 1.25rem;border:none;background:none;font-size:.9rem;font-weight:800;color:var(--primary);border-bottom:3px solid var(--primary);display:inline-flex;align-items:center;gap:.45rem}
.page{max-width:1180px;margin:0 auto;padding:1.1rem .85rem 2rem}
.intro{display:flex;align-items:flex-start;gap:.8rem;padding:1rem;margin-bottom:1rem;border:1px solid rgba(26,158,120,.22);border-radius:var(--radius);background:var(--primary-light);color:var(--primary-dark)}
.intro h3{font-size:1rem;margin-bottom:.15rem}
.intro p{font-size:.9rem;line-height:1.45;color:var(--text-muted)}
.lesson-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,.58fr);gap:1rem;align-items:start}
.stack{display:flex;flex-direction:column;gap:1rem}
.section{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
.section-head{display:flex;align-items:center;gap:.6rem;padding:.8rem 1rem;border-bottom:1px solid var(--border);background:var(--bg)}
.section-head i{color:var(--primary)}
.section-title{font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted)}
.copy{padding:1rem;color:var(--text);font-size:.95rem;line-height:1.65;white-space:pre-wrap;word-break:break-word}
.resource-list{padding:.8rem;display:grid;gap:.65rem}
.resource-card{width:100%;display:flex;align-items:center;gap:.8rem;padding:.78rem;border:1px solid var(--border);border-radius:12px;color:var(--text);background:var(--surface);text-align:left;font-family:inherit;cursor:pointer;text-decoration:none;transition:border-color var(--trans),background var(--trans),transform var(--trans)}
.resource-card:hover{border-color:var(--primary);background:var(--primary-light);transform:translateY(-1px)}
.resource-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:var(--primary-light);color:var(--primary-dark)}
.resource-icon.is-youtube{background:#fff0f0;color:#e62117}
.resource-meta{flex:1;min-width:0}
.resource-title{font-size:.9rem;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.resource-sub{font-size:.78rem;color:var(--text-muted);margin-top:.12rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.resource-open{color:var(--primary);font-size:.8rem}
.empty{padding:1.5rem .8rem;text-align:center;color:var(--text-muted);font-size:.9rem}
.state{padding:3rem 1rem;text-align:center;color:var(--text-muted)}
.spinner{width:34px;height:34px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin .75s linear infinite;margin:0 auto .8rem}
@keyframes spin{to{transform:rotate(360deg)}}
@media(max-width:780px){.hero-inner{flex-direction:column}.lesson-grid{grid-template-columns:1fr}.topbar{padding:0 .8rem}.hero{padding:1.2rem .9rem}.tabs-bar{padding:0 .8rem}}
</style>
</head>
<body>
<nav class="topbar">
  <a href="<?= $class_id !== '' ? 'studentClassRoom.php?class_id=' . urlencode($class_id) : 'student.php' ?>" class="back-btn"><i class="fas fa-arrow-left"></i></a>
  <a href="student.php" class="brand"><span class="blogo"><i class="fas fa-book-open"></i></span> TERELEARN</a>
  <span style="color:var(--border)">&rsaquo;</span>
  <span class="topbar-title" id="topbarTitle">Lesson</span>
  <div class="topbar-right"><button class="icon-btn" id="darkToggle"><i class="fas fa-moon"></i></button></div>
</nav>

<header class="hero">
  <div class="hero-inner">
    <div class="hero-icon"><i class="fa-solid fa-file-lines"></i></div>
    <div>
      <div class="eyebrow">Lesson</div>
      <h1 class="hero-title" id="heroTitle">Loading lesson...</h1>
      <div class="hero-sub" id="heroSub">TERELEARN lesson materials</div>
      <span class="hero-chip"><i class="fa-solid fa-file-lines"></i> Non-gradable</span>
    </div>
  </div>
</header>

<div class="tabs-bar">
  <div class="tab-inner">
    <button class="tab-btn"><i class="fa-solid fa-book-open"></i> Lesson Materials</button>
  </div>
</div>

<main class="page">
  <div id="loadingState" class="state"><div class="spinner"></div>Loading lesson materials...</div>
  <div id="errorState" class="state" style="display:none;"></div>
  <section id="lessonContent" style="display:none;">
    <div class="intro">
      <i class="fa-solid fa-book-open-reader"></i>
      <div><h3>Lesson review materials</h3><p>Open the uploaded files and links below while reviewing this topic.</p></div>
    </div>
    <div class="lesson-grid">
      <div class="stack">
        <section class="section">
          <div class="section-head"><i class="fa-solid fa-bullseye"></i><div class="section-title">Topic</div></div>
          <div class="copy" id="topicText">-</div>
        </section>
        <section class="section">
          <div class="section-head"><i class="fa-solid fa-align-left"></i><div class="section-title">Instructions</div></div>
          <div class="copy" id="bodyText">-</div>
        </section>
      </div>
      <div class="stack">
        <section class="section">
          <div class="section-head"><i class="fa-solid fa-folder-open"></i><div class="section-title">Uploaded Files</div></div>
          <div id="fileList"></div>
        </section>
        <section class="section">
          <div class="section-head"><i class="fa-solid fa-link"></i><div class="section-title">Review Links</div></div>
          <div id="linkList"></div>
        </section>
      </div>
    </div>
  </section>
</main>

<script>
const POST_ID = <?= json_encode($post_id) ?>;
const darkToggle = document.getElementById('darkToggle');

function esc(value) {
  return String(value == null ? '' : value).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function shortUrl(url) {
  return String(url || '').replace(/^https?:\/\//, '').substring(0, 54);
}

function fileIcon(mime, name) {
  const text = `${mime || ''} ${name || ''}`.toLowerCase();
  if (text.includes('pdf')) return 'fa-file-pdf';
  if (text.includes('image') || /\.(png|jpe?g|gif|webp|svg)$/i.test(name || '')) return 'fa-file-image';
  if (text.includes('word') || /\.docx?$/i.test(name || '')) return 'fa-file-word';
  if (text.includes('excel') || /\.(xlsx?|csv)$/i.test(name || '')) return 'fa-file-excel';
  if (text.includes('powerpoint') || /\.(pptx?|ppsx?)$/i.test(name || '')) return 'fa-file-powerpoint';
  if (text.includes('video')) return 'fa-file-video';
  if (text.includes('audio')) return 'fa-file-audio';
  return 'fa-file';
}

function resourceCard(item) {
  const type = String(item.attach_type || '').toLowerCase();
  const isLink = type === 'link' || type === 'youtube';
  const href = item.url || item.file_path || '';
  const name = isLink ? shortUrl(href) : (item.file_name || item.name || 'File');
  const icon = type === 'youtube' ? 'fab fa-youtube' : `fas ${isLink ? 'fa-link' : fileIcon(item.mime_type || '', name)}`;
  const iconClass = type === 'youtube' ? 'resource-icon is-youtube' : 'resource-icon';
  const sub = isLink ? 'Open this review link' : 'Open or download this lesson material';
  return `<a class="resource-card" href="${esc(href)}" target="_blank" rel="noopener">
    <div class="${iconClass}"><i class="${icon}"></i></div>
    <div class="resource-meta"><div class="resource-title">${esc(name)}</div><div class="resource-sub">${esc(sub)}</div></div>
    <i class="fa-solid fa-up-right-from-square resource-open"></i>
  </a>`;
}

function renderList(el, items) {
  el.innerHTML = items.length
    ? `<div class="resource-list">${items.map(resourceCard).join('')}</div>`
    : '<div class="empty"><i class="fa-solid fa-folder-open"></i><br>No materials added yet.</div>';
}

async function loadLesson() {
  const loading = document.getElementById('loadingState');
  const error = document.getElementById('errorState');
  const content = document.getElementById('lessonContent');
  try {
    const fd = new FormData();
    fd.append('post_id', POST_ID);
    const res = await fetch('API/student/studentClassroom/get_post_detail.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    });
    const data = await res.json();
    if (!data || data.status !== 'success') throw new Error(data && data.message ? data.message : 'Could not load lesson.');
    const post = data.post || {};
    const title = post.title || post.topic || 'Lesson';
    const attachments = Array.isArray(post.attachments) ? post.attachments : [];
    const files = attachments.filter(item => !['link', 'youtube'].includes(String(item.attach_type || '').toLowerCase()));
    const links = attachments.filter(item => ['link', 'youtube'].includes(String(item.attach_type || '').toLowerCase()));

    document.title = `${title} - TERELEARN`;
    document.getElementById('topbarTitle').textContent = title;
    document.getElementById('heroTitle').textContent = title;
    document.getElementById('heroSub').textContent = post.topic || 'Lesson materials';
    document.getElementById('topicText').textContent = post.topic || title || 'No topic added.';
    document.getElementById('bodyText').textContent = post.body || 'No instructions added.';
    renderList(document.getElementById('fileList'), files);
    renderList(document.getElementById('linkList'), links);

    loading.style.display = 'none';
    content.style.display = 'block';
  } catch (err) {
    loading.style.display = 'none';
    error.style.display = 'block';
    error.innerHTML = `<strong>Lesson unavailable</strong><br>${esc(err.message || err)}`;
  }
}

function applyTheme() {
  const dark = localStorage.getItem('tl_dark') === '1';
  document.body.classList.toggle('dark', dark);
  darkToggle.innerHTML = dark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
}

darkToggle.addEventListener('click', () => {
  const next = document.body.classList.contains('dark') ? '0' : '1';
  localStorage.setItem('tl_dark', next);
  applyTheme();
});

applyTheme();
loadLesson();
</script>
</body>
</html>
