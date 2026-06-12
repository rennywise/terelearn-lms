<?php
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php');
    exit;
}

$post_id  = trim($_GET['post_id'] ?? '');
$class_id = trim($_GET['class_id'] ?? '');
$kind = strtolower(trim($_GET['kind'] ?? 'quiz'));
if ($kind !== 'exam') {
    $kind = 'quiz';
}
$assessment_label = $kind === 'exam' ? 'Exam' : 'Quiz';
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
<title><?= htmlspecialchars($assessment_label, ENT_QUOTES, 'UTF-8') ?> Result - Tere LEARN</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{
  --primary:#1a9e78;--primary-dark:#0d7a5e;--primary-light:#e6f7f2;
  --accent:#1f73db;--danger:#d93025;--warning:#f59e0b;
  --border:#e8eaed;--text:#1c2027;--text-muted:#5f6368;
  --bg:#f4f6f9;--surface:#fff;--radius:12px;--shadow:0 2px 14px rgba(15,23,42,.08);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
button{font-family:inherit}
.topbar{position:sticky;top:0;z-index:20;background:var(--surface);border-bottom:1px solid var(--border);box-shadow:var(--shadow)}
.topbar-inner{max-width:1080px;margin:0 auto;padding:.8rem 1rem;display:flex;align-items:center;gap:.75rem}
.brand{display:flex;align-items:center;gap:.55rem;font-weight:800;color:var(--text);min-width:0}
.logo{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;display:flex;align-items:center;justify-content:center;font-size:.82rem;flex-shrink:0}
.title-wrap{min-width:0}
.eyebrow{font-size:.72rem;font-weight:800;text-transform:uppercase;color:var(--primary);letter-spacing:.04em}
.page-title{font-size:1rem;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.actions{margin-left:auto;display:flex;gap:.55rem;flex-wrap:wrap;justify-content:flex-end}
.btn{border:none;border-radius:10px;padding:.62rem .95rem;font-size:.85rem;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;gap:.45rem;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}
.btn-ghost{background:var(--bg);color:var(--text);border:1px solid var(--border)}
.shell{max-width:1080px;margin:0 auto;padding:1.2rem 1rem 2rem}
.summary{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:1rem;align-items:stretch;margin-bottom:1rem}
.summary-main,.score-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);padding:1rem}
.summary-main h1{font-size:1.35rem;line-height:1.25;margin-bottom:.35rem}
.meta{display:flex;gap:.6rem;flex-wrap:wrap;color:var(--text-muted);font-size:.84rem}
.pill{display:inline-flex;align-items:center;gap:.35rem;background:var(--bg);border:1px solid var(--border);border-radius:999px;padding:.32rem .65rem;font-weight:700}
.score-card{min-width:220px;text-align:center;display:flex;flex-direction:column;justify-content:center}
.score-label{font-size:.78rem;font-weight:800;color:var(--text-muted);text-transform:uppercase}
.score-value{font-size:2.2rem;font-weight:800;color:var(--primary);line-height:1.05;margin:.2rem 0}
.score-sub{font-size:.84rem;color:var(--text-muted);font-weight:700}
.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem;margin-bottom:1rem}
.stat{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:.85rem;box-shadow:var(--shadow)}
.stat strong{display:block;font-size:1.35rem}
.stat span{font-size:.8rem;color:var(--text-muted);font-weight:700}
.state{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:2rem 1rem;text-align:center;color:var(--text-muted);box-shadow:var(--shadow)}
.spinner{width:34px;height:34px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin .75s linear infinite;margin:0 auto .8rem}
@keyframes spin{to{transform:rotate(360deg)}}
.questions{display:grid;gap:.85rem}
.question-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;break-inside:avoid}
.question-head{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;padding:.95rem 1rem;border-bottom:1px solid var(--border)}
.question-title{display:flex;gap:.65rem;align-items:flex-start;line-height:1.45;font-weight:800}
.q-num{width:28px;height:28px;border-radius:8px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.82rem}
.badge{display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;padding:.28rem .65rem;font-size:.76rem;font-weight:800;white-space:nowrap}
.badge-correct{background:#e8f7ef;color:#0b7a4f}
.badge-wrong{background:#fdecea;color:#b42318}
.choices{padding:.8rem 1rem 1rem;display:grid;gap:.45rem}
.choice{display:grid;grid-template-columns:28px minmax(0,1fr) auto;align-items:center;gap:.6rem;border:1px solid var(--border);border-radius:10px;padding:.58rem .65rem;background:#fff}
.choice-letter{width:26px;height:26px;border-radius:50%;background:var(--bg);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.75rem}
.choice-text{font-size:.9rem;line-height:1.35}
.choice-mark{font-size:.72rem;font-weight:800}
.choice-correct{background:#e8f7ef;border-color:#66c39a}
.choice-selected:not(.choice-correct){background:#fff4e5;border-color:#f2b24f}
.choice-correct .choice-mark{color:#0b7a4f}
.choice-selected:not(.choice-correct) .choice-mark{color:#9a6700}
.answer-note{padding:0 1rem 1rem;color:var(--text-muted);font-size:.84rem}
@media (max-width:760px){
  .topbar-inner{align-items:stretch;flex-direction:column}
  .actions{margin-left:0;width:100%}
  .actions .btn{flex:1;justify-content:center}
  #backBtn{align-self:flex-start}
  .summary{grid-template-columns:1fr}
  .stats{grid-template-columns:1fr}
}
@media print{
  body{background:#fff;color:#111}
  .topbar,.btn,.state{display:none!important}
  .shell{max-width:none;padding:0}
  .summary,.stats{display:block}
  .summary-main,.score-card,.stat,.question-card{box-shadow:none;border-color:#ccc;margin-bottom:.6rem}
  .question-card{page-break-inside:avoid}
}
</style>
</head>
<body>
<div class="topbar">
  <div class="topbar-inner">
    <a class="btn btn-ghost" id="backBtn" href="<?= $class_id !== '' ? 'studentClassRoom.php?class_id=' . urlencode($class_id) : 'student.php' ?>">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <div class="brand">
      <div class="logo">TL</div>
      <div class="title-wrap">
        <div class="eyebrow" id="assessmentKind"><?= htmlspecialchars($assessment_label, ENT_QUOTES, 'UTF-8') ?> Review</div>
        <div class="page-title" id="topTitle">Loading result...</div>
      </div>
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="button" id="printBtn">
        <i class="fa-solid fa-file-pdf"></i> PDF / Print
      </button>
    </div>
  </div>
</div>

<main class="shell">
  <div id="loadingState" class="state">
    <div class="spinner"></div>
    Loading your result...
  </div>
  <div id="errorState" class="state" style="display:none;"></div>
  <section id="resultView" style="display:none;">
    <div class="summary">
      <div class="summary-main">
        <div class="eyebrow" id="summaryKind"><?= htmlspecialchars($assessment_label, ENT_QUOTES, 'UTF-8') ?> Result</div>
        <h1 id="summaryTitle">Result</h1>
        <div class="meta">
          <span class="pill"><i class="fa-regular fa-calendar-check"></i> Submitted: <span id="submittedAt">-</span></span>
          <span class="pill"><i class="fa-solid fa-flag-checkered"></i> Released: <span id="releasedAt">-</span></span>
        </div>
      </div>
      <div class="score-card">
        <div class="score-label">Score</div>
        <div class="score-value" id="scoreValue">0/0</div>
        <div class="score-sub" id="scorePercent">0%</div>
      </div>
    </div>
    <div class="stats">
      <div class="stat"><strong id="correctCount">0</strong><span>Correct answers</span></div>
      <div class="stat"><strong id="wrongCount">0</strong><span>Wrong answers</span></div>
      <div class="stat"><strong id="questionCount">0</strong><span>Total questions</span></div>
    </div>
    <div class="questions" id="questions"></div>
  </section>
</main>

<script>
const POST_ID = <?= json_encode($post_id) ?>;
const DEFAULT_KIND = <?= json_encode($kind) ?>;

function esc(value) {
  return String(value == null ? '' : value).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function fmtDate(value) {
  if (!value) return '-';
  const date = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toLocaleString([], { year:'numeric', month:'short', day:'numeric', hour:'numeric', minute:'2-digit' });
}

function letter(index) {
  return String.fromCharCode(65 + index);
}

function renderQuestions(rows) {
  const wrap = document.getElementById('questions');
  if (!rows.length) {
    wrap.innerHTML = '<div class="state">No questions to show.</div>';
    return;
  }
  wrap.innerHTML = rows.map((row, index) => {
    const isCorrect = row.is_correct === true;
    const selected = row.selected_text || 'No answer selected';
    const correct = row.correct_text || 'No answer key';
    const choices = Array.isArray(row.choices) ? row.choices : [];
    const choicesHtml = choices.map((choice, choiceIndex) => {
      const choiceCorrect = choice.is_correct === true;
      const choiceSelected = row.selected_choice_id && String(row.selected_choice_id) === String(choice.id);
      const classes = ['choice'];
      if (choiceCorrect) classes.push('choice-correct');
      if (choiceSelected) classes.push('choice-selected');
      let mark = '';
      if (choiceCorrect && choiceSelected) mark = '<span class="choice-mark"><i class="fa-solid fa-check"></i> Your correct answer</span>';
      else if (choiceCorrect) mark = '<span class="choice-mark"><i class="fa-solid fa-check"></i> Correct answer</span>';
      else if (choiceSelected) mark = '<span class="choice-mark"><i class="fa-solid fa-xmark"></i> Your answer</span>';
      return `<div class="${classes.join(' ')}">
        <div class="choice-letter">${letter(choiceIndex)}</div>
        <div class="choice-text">${esc(choice.text || '')}</div>
        ${mark}
      </div>`;
    }).join('');

    return `<article class="question-card">
      <div class="question-head">
        <div class="question-title"><span class="q-num">${index + 1}</span><span>${esc(row.question || '')}</span></div>
        <span class="badge ${isCorrect ? 'badge-correct' : 'badge-wrong'}">
          <i class="fa-solid ${isCorrect ? 'fa-check' : 'fa-xmark'}"></i> ${isCorrect ? 'Correct' : 'Wrong'}
        </span>
      </div>
      <div class="choices">${choicesHtml}</div>
      <div class="answer-note">
        <strong>Your answer:</strong> ${esc(selected)}<br>
        <strong>Correct answer:</strong> ${esc(correct)}
      </div>
    </article>`;
  }).join('');
}

async function loadResult() {
  const loading = document.getElementById('loadingState');
  const error = document.getElementById('errorState');
  const view = document.getElementById('resultView');
  try {
    const fd = new FormData();
    fd.append('post_id', POST_ID);
    const res = await fetch('API/student/studentClassroom/quiz/get_my_result.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    });
    const data = await res.json();
    if (!data || data.success === false) throw new Error(data && data.message ? data.message : 'Could not load result.');

    const post = data.post || {};
    const kind = String(post.post_type || DEFAULT_KIND).toLowerCase() === 'exam' ? 'Exam' : 'Quiz';
    const title = post.title || kind;
    const score = Number(data.score || 0);
    const max = Number(data.max_score || 0);
    const pct = max > 0 ? Math.round((score / max) * 100) : 0;
    const rows = Array.isArray(data.review) ? data.review : [];
    const correct = rows.filter(row => row.is_correct === true).length;

    document.title = `${title} Result - Tere LEARN`;
    document.getElementById('assessmentKind').textContent = `${kind} Review`;
    document.getElementById('summaryKind').textContent = `${kind} Result`;
    document.getElementById('topTitle').textContent = title;
    document.getElementById('summaryTitle').textContent = title;
    document.getElementById('submittedAt').textContent = fmtDate(data.submitted_at);
    document.getElementById('releasedAt').textContent = fmtDate(post.results_released_at);
    document.getElementById('scoreValue').textContent = `${Math.round(score)}/${Math.round(max)}`;
    document.getElementById('scorePercent').textContent = `${pct}%`;
    document.getElementById('correctCount').textContent = correct;
    document.getElementById('wrongCount').textContent = Math.max(0, rows.length - correct);
    document.getElementById('questionCount').textContent = rows.length;
    renderQuestions(rows);

    loading.style.display = 'none';
    view.style.display = 'block';
  } catch (err) {
    loading.style.display = 'none';
    error.style.display = 'block';
    error.innerHTML = `<strong>Result unavailable</strong><br>${esc(err.message || err)}`;
  }
}

document.getElementById('printBtn').addEventListener('click', () => window.print());
loadResult();
</script>
</body>
</html>
