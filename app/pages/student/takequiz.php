<?php
/**
 * takequiz.php — Tere LEARN | Student Quiz Page
 * ?post_id=UUID
 */
require_once dirname(__DIR__, 2) . '/config/paths.php';
session_start();
if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_level_id'] ?? 0) !== 3) {
    header('Location: ' . TERELEARN_BASE_URL . 'signin.php'); exit;
}
$post_id  = trim($_GET['post_id'] ?? '');
$class_id = trim($_GET['class_id'] ?? '');
$kind = strtolower(trim($_GET['kind'] ?? 'quiz'));
if ($kind !== 'exam') {
    $kind = 'quiz';
}
$assessment_label = $kind === 'exam' ? 'Exam' : 'Quiz';
$assessment_lower = strtolower($assessment_label);
if (!$post_id) { header('Location: ' . TERELEARN_BASE_URL . 'student.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Take <?= htmlspecialchars($assessment_label, ENT_QUOTES, 'UTF-8') ?> - Tere LEARN</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
:root{
  --primary:#1a9e78;--primary-dark:#0d7a5e;--primary-light:#e6f7f2;
  --accent:#1f73db;--danger:#d93025;--warning:#f57c00;
  --border:#e8eaed;--text:#1c2027;--text-muted:#5f6368;
  --bg:#f4f6f9;--surface:#ffffff;--nav-h:60px;
  --radius:14px;--shadow:0 2px 12px rgba(0,0,0,.07);
  --trans:.22s cubic-bezier(.4,0,.2,1);
}
body.dark{
  --primary:#2ecc9a;--primary-dark:#1a9e78;--primary-light:rgba(46,204,154,.12);
  --accent:#4d90e2;--border:#2e3849;--text:#e4ecf7;--text-muted:#8a9ab5;
  --bg:#0f1724;--surface:#182030;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{-webkit-text-size-adjust:100%;}
body{
  font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);
  min-height:100vh;min-height:100dvh;
  -webkit-tap-highlight-color:transparent;
}
.swal2-container{z-index:99999!important;}

/* ── TOP BAR ── */
.tq-topbar{
  position:fixed;inset:0 0 auto 0;height:var(--nav-h);
  background:var(--surface);border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:.75rem;
  padding:0 1.25rem;padding-left:max(1.25rem, env(safe-area-inset-left));
  padding-right:max(1.25rem, env(safe-area-inset-right));
  z-index:200;box-shadow:var(--shadow);
}
.tq-brand{
  display:flex;align-items:center;gap:.5rem;
  font-size:.9rem;font-weight:700;color:var(--text);flex-shrink:0;
}
.tq-logo{
  width:30px;height:30px;
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  border-radius:8px;color:#fff;font-size:.75rem;
  display:flex;align-items:center;justify-content:center;
}
.tq-quiz-name{
  font-size:.85rem;font-weight:600;color:var(--text-muted);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  flex:1;min-width:0;
}
.tq-right{margin-left:auto;display:flex;align-items:center;gap:.6rem;flex-shrink:0;}
.tq-timer{
  display:none;align-items:center;gap:.35rem;
  background:var(--primary-light);color:var(--primary);
  border:1.5px solid rgba(26,158,120,.3);border-radius:20px;
  padding:.28rem .8rem;font-size:.82rem;font-weight:700;
  font-family:'DM Mono',monospace;white-space:nowrap;
}
.tq-timer.urgent{background:#fdecea;color:var(--danger);border-color:#f5c2c7;}

/* ── PROGRESS ── */
.tq-progress-wrap{
  position:fixed;top:var(--nav-h);left:0;right:0;
  z-index:199;background:var(--border);
}
.tq-progress-track{
  height:5px;background:var(--border);
}
.tq-progress-bar{
  height:100%;background:linear-gradient(90deg,var(--primary),#2ecc9a);
  transition:width .4s cubic-bezier(.4,0,.2,1);
}
.tq-progress-label{
  text-align:right;padding:.18rem .85rem;
  font-size:.68rem;font-weight:700;color:var(--text-muted);
  font-family:'DM Mono',monospace;
  background:var(--bg);border-bottom:1px solid var(--border);
}

/* ── MAIN ── */
.tq-main{
  padding-top:calc(var(--nav-h) + 30px);
  min-height:100vh;min-height:100dvh;
  display:flex;flex-direction:column;
}
.tq-body{
  flex:1;display:flex;justify-content:center;align-items:flex-start;
  padding:1.5rem 1rem;
  padding-bottom:max(1.5rem, env(safe-area-inset-bottom));
}

/* ── QUIZ CARD ── */
.tq-card{
  width:100%;max-width:720px;
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--radius);box-shadow:var(--shadow);
  overflow:hidden;
}

/* Question header */
.tq-q-header{
  padding:.75rem 1.25rem;
  border-bottom:1px solid var(--border);
  background:var(--bg);
  display:flex;align-items:center;justify-content:space-between;
  gap:.5rem;
}
.tq-q-counter{
  font-size:.8rem;font-weight:700;color:var(--text-muted);
}
.tq-per-timer{
  display:none;align-items:center;gap:.3rem;
  color:var(--primary);font-weight:700;
  font-family:'DM Mono',monospace;font-size:.82rem;
}
.tq-per-timer.urgent{color:var(--danger);}

/* Question body */
.tq-q-body{padding:1.5rem 1.4rem 1.25rem;}
.tq-q-meta{display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;}
.tq-q-pts{
  display:inline-flex;align-items:center;gap:.3rem;
  font-size:.72rem;font-weight:700;color:var(--primary);
  background:var(--primary-light);border-radius:20px;
  padding:.18rem .6rem;
}
.tq-q-text{
  font-size:1.05rem;font-weight:700;line-height:1.65;
  color:var(--text);margin-bottom:1.35rem;
}

/* ── CHOICES — flat style like reference ── */
.tq-choices{display:flex;flex-direction:column;gap:.5rem;}

@keyframes tq-choice-in{
  from{opacity:0;transform:translateY(6px);}
  to{opacity:1;transform:translateY(0);}
}
.tq-choice{
  display:flex;align-items:center;
  padding:1rem 1.15rem;
  border-radius:10px;
  border:1.5px solid var(--border);
  background:var(--bg);
  cursor:pointer;transition:border-color .15s,background .15s,transform .1s,box-shadow .15s;
  text-align:left;width:100%;
  font-family:inherit;font-size:.94rem;font-weight:500;color:var(--text);
  animation:tq-choice-in .18s ease both;
  -webkit-user-select:none;user-select:none;
  min-height:52px;
}
.tq-choice:nth-child(1){animation-delay:.03s;}
.tq-choice:nth-child(2){animation-delay:.07s;}
.tq-choice:nth-child(3){animation-delay:.11s;}
.tq-choice:nth-child(4){animation-delay:.15s;}
.tq-choice:nth-child(5){animation-delay:.19s;}
.tq-choice:hover{
  border-color:var(--primary);
  background:var(--primary-light);
  box-shadow:0 2px 8px rgba(26,158,120,.12);
}
.tq-choice.selected{
  border-color:var(--primary);
  background:var(--primary-light);
  color:var(--primary);
  box-shadow:0 2px 10px rgba(26,158,120,.18);
}
.tq-choice.selected .tq-choice-text{font-weight:600;}

/* Flash feedback on tap */
@keyframes tq-choice-flash{
  0%{background:rgba(26,158,120,.35);}
  100%{background:var(--primary-light);}
}
.tq-choice.flash{animation:tq-choice-flash .25s ease;}

/* ── FOOTER ── */
.tq-footer{
  padding:.85rem 1.25rem;
  border-top:1px solid var(--border);
  background:var(--bg);
  display:flex;align-items:center;justify-content:center;
  gap:.5rem;
}
.tq-dot-nav{
  display:flex;gap:.3rem;flex-wrap:wrap;justify-content:center;
  max-width:320px;
}
.tq-dot{
  width:9px;height:9px;border-radius:50%;
  background:var(--border);border:none;cursor:default;
  transition:all .15s;padding:0;flex-shrink:0;
}
.tq-dot.answered{background:var(--primary);}
.tq-dot.current{
  background:var(--primary);
  box-shadow:0 0 0 3px rgba(26,158,120,.28);
  width:11px;height:11px;
}

/* Submit button in footer */
.tq-btn-submit{
  display:none;align-items:center;gap:.4rem;
  padding:.65rem 1.3rem;border-radius:10px;
  font-family:inherit;font-size:.88rem;font-weight:700;
  border:none;cursor:pointer;transition:all .15s;
  background:var(--danger);color:#fff;
  box-shadow:0 2px 8px rgba(217,48,37,.3);
}
.tq-btn-submit:hover:not(:disabled){background:#b1271c;}
.tq-btn-submit:disabled{opacity:.5;cursor:not-allowed;}

/* ── STATES ── */
.tq-state{
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:4rem 2rem;text-align:center;min-height:50vh;width:100%;
}
.tq-state-icon{font-size:3.2rem;margin-bottom:1rem;opacity:.35;}
.tq-state-title{font-size:1.15rem;font-weight:700;margin-bottom:.4rem;}
.tq-state-sub{font-size:.86rem;color:var(--text-muted);line-height:1.65;max-width:360px;}
.tq-pause-badge{
  display:none;align-items:center;gap:.4rem;
  margin-top:.85rem;padding:.32rem .7rem;border-radius:20px;
  background:#fff3cd;color:#92400e;border:1px solid #fcd34d;
  font-size:.75rem;font-weight:700;letter-spacing:.2px;
}

/* Result */
.tq-result{
  display:flex;flex-direction:column;align-items:center;
  padding:3rem 1.5rem;text-align:center;
}
.tq-score-ring{
  width:130px;height:130px;border-radius:50%;
  border:6px solid var(--primary);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  margin-bottom:1.25rem;
  background:var(--primary-light);
}
.tq-score-val{font-size:2.2rem;font-weight:800;color:var(--primary);font-family:'DM Mono',;}
.tq-score-lbl{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);}
.tq-result-title{font-size:1.35rem;font-weight:800;margin-bottom:.3rem;}
.tq-result-sub{font-size:.88rem;color:var(--text-muted);margin-bottom:1.75rem;line-height:1.6;}
.tq-back-btn{
  display:inline-flex;align-items:center;gap:.5rem;
  padding:.75rem 1.6rem;border-radius:10px;
  background:var(--primary);color:#fff;border:none;
  font-family:inherit;font-size:.92rem;font-weight:700;
  cursor:pointer;box-shadow:0 2px 10px rgba(26,158,120,.3);
  transition:all .15s;
}
.tq-back-btn:hover{background:var(--primary-dark);}

/* Loading spinner */
@keyframes tq-spin{to{transform:rotate(360deg);}}
.tq-spinner{
  width:38px;height:38px;border:3px solid var(--border);
  border-top-color:var(--primary);border-radius:50%;
  animation:tq-spin .7s linear infinite;
  margin:0 auto 1rem;
}

/* ── MOBILE ── */
@media(max-width:480px){
  :root{--nav-h:54px;}
  .tq-brand span{display:none;}
  .tq-quiz-name{font-size:.8rem;}
  .tq-body{padding:.75rem .5rem;}
  .tq-card{border-radius:12px;}
  .tq-q-header{padding:.65rem 1rem;}
  .tq-q-body{padding:1.15rem 1rem 1rem;}
  .tq-q-text{font-size:1rem;margin-bottom:1.1rem;}
  .tq-choice{
    padding:.95rem 1rem;font-size:.9rem;min-height:56px;
    border-radius:10px;
  }
  .tq-choices{gap:.45rem;}
  .tq-footer{padding:.7rem 1rem;}
  .tq-dot{width:8px;height:8px;}
  .tq-dot.current{width:10px;height:10px;}
  .tq-btn-submit{padding:.6rem 1.1rem;font-size:.84rem;}
  .tq-score-ring{width:110px;height:110px;}
  .tq-score-val{font-size:1.9rem;}
}
@media(max-width:360px){
  .tq-choice{font-size:.86rem;padding:.85rem .9rem;}
}
</style>
</head>
<body>

<!-- TOP BAR -->
<div class="tq-topbar">
  <div class="tq-brand">
    <div class="tq-logo"><i class="fas fa-graduation-cap"></i></div>
    <span>TERE<strong>LEARN</strong></span>
  </div>
  <div id="tqQuizName" class="tq-quiz-name">Loading quiz…</div>
  <div class="tq-right">
    <div class="tq-timer" id="tqTimer">
      <i class="fas fa-clock"></i>
      <span id="tqTimerLabel">--:--</span>
    </div>
  </div>
</div>

<!-- PROGRESS -->
<div class="tq-progress-wrap">
  <div class="tq-progress-track">
    <div class="tq-progress-bar" id="tqProgressBar" style="width:0%"></div>
  </div>
  <div class="tq-progress-label" id="tqProgressLabel">0%</div>
</div>

<!-- MAIN -->
<div class="tq-main">
  <div class="tq-body">

    <!-- Loading -->
    <div id="tqStateLoading" class="tq-state">
      <div class="tq-spinner"></div>
      <div class="tq-state-title">Loading your quiz…</div>
      <div class="tq-state-sub">Please wait while we prepare your questions.</div>
    </div>

    <!-- Error -->
    <div id="tqStateError" class="tq-state" style="display:none">
      <div class="tq-state-icon"><i class="fas fa-triangle-exclamation"></i></div>
      <div class="tq-state-title">Cannot load <?= htmlspecialchars($assessment_lower, ENT_QUOTES, 'UTF-8') ?></div>
      <div class="tq-state-sub" id="tqErrorMsg">Something went wrong.</div>
      <button class="tq-back-btn" style="margin-top:1.5rem" onclick="goBack()">
        <i class="fas fa-arrow-left"></i> Go back
      </button>
    </div>

    <!-- Waiting for faculty -->
    <div id="tqStateWaiting" class="tq-state" style="display:none">
      <div class="tq-spinner"></div>
      <div class="tq-state-title">Waiting for your professor to start</div>
      <div class="tq-state-sub">You're enrolled and in the queue. The quiz will begin automatically once your professor starts the live session. Keep this page open.</div>
      <div class="tq-pause-badge" id="tqPauseBadge"><i class="fas fa-pause"></i> PAUSED</div>
      <div style="display:flex;gap:.6rem;flex-wrap:wrap;justify-content:center;margin-top:1.5rem;">
        <button class="tq-back-btn" style="background:var(--text-muted);" onclick="goBack()">
          <i class="fas fa-arrow-left"></i> Back
        </button>
        <button class="tq-back-btn" style="background:var(--danger);" onclick="leaveQuizLobby()">
          <i class="fas fa-right-from-bracket"></i> Leave <?= htmlspecialchars($assessment_label, ENT_QUOTES, 'UTF-8') ?>
        </button>
      </div>
    </div>

    <!-- Quiz card -->
    <div id="tqCard" class="tq-card" style="display:none">
      <!-- Question header -->
      <div class="tq-q-header">
        <span class="tq-q-counter" id="tqQCounter">Question 1 of —</span>
      </div>

      <!-- Question body -->
      <div class="tq-q-body">
        <div class="tq-q-meta">
          <div class="tq-q-pts" id="tqQPts"><i class="fas fa-star"></i> — pts</div>
        </div>
        <div class="tq-q-text" id="tqQText"></div>
        <div class="tq-choices" id="tqChoices"></div>
      </div>

      <!-- Footer: dots + submit on last question -->
      <div class="tq-footer">
        <div class="tq-dot-nav" id="tqDotNav"></div>
        <button class="tq-btn-submit" id="tqSubmitBtn" onclick="confirmSubmit()">
          <i class="fas fa-paper-plane"></i> Submit
        </button>
      </div>
    </div>

    <!-- Result -->
    <div id="tqResult" class="tq-card tq-result" style="display:none">
      <div class="tq-score-ring">
        <div class="tq-score-val" id="tqScoreVal">—</div>
        <div class="tq-score-lbl"></div>
      </div>
      <div class="tq-result-title" id="tqResultTitle"><?= htmlspecialchars($assessment_label, ENT_QUOTES, 'UTF-8') ?> Submitted!</div>
      <div class="tq-result-sub" id="tqResultSub">Your answers have been recorded.</div>
      <button class="tq-back-btn" onclick="goBack()">
        <i class="fas fa-arrow-left"></i> Back to Classroom
      </button>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const POST_ID  = <?= json_encode($post_id) ?>;
const CLASS_ID = <?= json_encode($class_id) ?>;
const INITIAL_KIND = <?= json_encode($kind) ?>;
const API      = 'API/student/studentClassroom/quiz/';
let assessmentKind = INITIAL_KIND;

const state = {
  attemptId:       null,
  questions:       [],   // shuffled order, fixed for the attempt
  answers:         {},
  current:         0,
  submitting:      false,
  globalSecs:      0,
  globalRemaining: 0,
  globalTimer:     null,
  perQSecs:        0,
  perQRemaining:   0,
  perQTimer:       null,
};

function normalizeAssessmentKind(raw){
  return String(raw || '').toLowerCase() === 'exam' ? 'exam' : 'quiz';
}
function assessmentLabel(){
  return assessmentKind === 'exam' ? 'Exam' : 'Quiz';
}
function assessmentLower(){
  return assessmentKind === 'exam' ? 'exam' : 'quiz';
}
function setAssessmentKind(raw){
  assessmentKind = normalizeAssessmentKind(raw);
  applyAssessmentCopy();
}
function applyAssessmentCopy(){
  const label = assessmentLabel();
  const lower = assessmentLower();
  document.title = `Take ${label} - Tere LEARN`;
  const nameEl = document.getElementById('tqQuizName');
  if (nameEl && /^Loading /i.test(nameEl.textContent || '')) {
    nameEl.textContent = `Loading ${lower}...`;
  }
  const loadingTitle = document.querySelector('#tqStateLoading .tq-state-title');
  if (loadingTitle) loadingTitle.textContent = `Loading your ${lower}...`;
  const errorTitle = document.querySelector('#tqStateError .tq-state-title');
  if (errorTitle) errorTitle.textContent = `Cannot load ${lower}`;
  const waitingSub = document.querySelector('#tqStateWaiting .tq-state-sub');
  if (waitingSub && !_pausedInRuntime) {
    waitingSub.textContent = `You're enrolled and in the queue. The ${lower} will begin automatically once your professor starts the live session. Keep this page open.`;
  }
  const leaveBtn = document.querySelector('#tqStateWaiting button.tq-back-btn[style*="var(--danger)"]');
  if (leaveBtn) leaveBtn.innerHTML = `<i class="fas fa-right-from-bracket"></i> Leave ${label}`;
  const resultTitle = document.getElementById('tqResultTitle');
  if (resultTitle) resultTitle.textContent = `${label} Submitted!`;
}

function normalizeTimeMode(raw){
  const v = String(raw || '').toLowerCase().replace(/[\s-]+/g, '_');
  if (v === 'whole_quiz') return 'per_quiz';
  if (v === 'per_question' || v === 'per_quiz' || v === 'none') return v;
  return '';
}

// ── Fisher-Yates shuffle ──
function shuffle(arr) {
  const a = [...arr];
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

// ── Prevent accidental navigation ──
let quizActive = false;
window.addEventListener('beforeunload', e => {
  if (quizActive && !state.submitting) {
    e.preventDefault();
    e.returnValue = 'Your quiz is in progress. Are you sure you want to leave?';
  }
});

function esc(v){ return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function showState(id){
  ['tqStateLoading','tqStateError','tqStateWaiting','tqCard','tqResult'].forEach(s=>{
    const el=document.getElementById(s);
    if(el) el.style.display = (s===id ? (id==='tqCard'?'block':'flex') : 'none');
  });
}
function setPauseBadge(show){
  const b = document.getElementById('tqPauseBadge');
  if (b) b.style.display = show ? 'inline-flex' : 'none';
}

function setQuizChromeVisible(visible){
  const progressWrap = document.querySelector('.tq-progress-wrap');
  const topTimer = document.getElementById('tqTimer');
  if (progressWrap) progressWrap.style.display = visible ? 'block' : 'none';
  if (topTimer) topTimer.style.display = visible ? topTimer.style.display : 'none';
}

function goBack(){
  if (_WAITING || document.getElementById('tqStateWaiting')?.style.display !== 'none') {
    try { sessionStorage.setItem('quiz_waiting_left_' + POST_ID, '1'); } catch(e){}
  }
  quizActive = false;
  clearInterval(_liveStateTimer);
  _liveStateTimer = null;
  if (CLASS_ID) {
    window.location.href = 'studentClassRoom.php?class_id=' + encodeURIComponent(CLASS_ID);
  } else {
    window.location.href = 'student.php';
  }
}

// ── Waiting-for-faculty poll ──
const _WAITING = new URLSearchParams(window.location.search).get('waiting') === '1';
let _waitTimer = null;
let _startedNoticeShown = false;
let _liveStateTimer = null;
let _pausedInRuntime = false;

async function leaveQuizLobby() {
  const label = assessmentLabel();
  const ok = await Swal.fire({
    icon: 'question',
    title: `Leave this live ${assessmentLower()}?`,
    text: `You can still join again if the ${assessmentLower()} has not started yet.`,
    showCancelButton: true,
    confirmButtonText: `Leave ${label}`,
    confirmButtonColor: '#d93025'
  });
  if (!ok.isConfirmed) return;
  try {
    const fd = new FormData();
    fd.append('post_id', POST_ID);
    const res = await fetch(API + 'withdraw_quiz.php', { method:'POST', body:fd, credentials:'same-origin' });
    const j = await res.json();
    if (!j || j.success === false) throw new Error(j?.message || `Failed to leave ${assessmentLower()}.`);
    goBack();
  } catch (e) {
    Swal.fire({ icon:'error', title:`Could not leave ${assessmentLower()}`, text:String(e.message || e) });
  }
}

async function _checkStarted() {
  try {
    const fd = new FormData();
    fd.append('post_id', POST_ID);
    const res = await fetch(API + 'get_my_quiz_state.php', { method:'POST', body:fd, credentials:'same-origin' });
    const j   = await res.json();
    const data = (j && j.data) || {};
    const quiz = (j && j.data && j.data.quiz) || {};
    setAssessmentKind(data.post_type || quiz.post_type || assessmentKind);
    if (!data.is_enrolled) {
      clearInterval(_waitTimer);
      _waitTimer = null;
      showError(`You are no longer joined in this ${assessmentLower()} lobby.`);
      return;
    }
    const isRunning = !!quiz.live_started_at && parseInt(quiz.is_force_open || 0, 10) === 1 && !quiz.live_ended_at && parseInt(quiz.is_force_closed || 0, 10) !== 1;
    const isPaused = !!quiz.live_started_at && parseInt(quiz.is_force_open || 0, 10) !== 1 && !quiz.live_ended_at && parseInt(quiz.is_force_closed || 0, 10) !== 1;
    if (isPaused) {
      setPauseBadge(true);
      const sub = document.querySelector('#tqStateWaiting .tq-state-sub');
      if (sub) sub.textContent = `${assessmentLabel()} is paused by your professor. Keep this page open and wait for resume.`;
    } else {
      setPauseBadge(false);
    }
    if (isRunning && !_startedNoticeShown) {
      _startedNoticeShown = true;
      clearInterval(_waitTimer);
      _waitTimer = null;
      // While student is in waiting page, auto-enter directly without modal.
      _beginAttempt();
    }
  } catch(e) { /* keep polling */ }
}

// ── Load & start attempt ──
async function init(){
  if (_WAITING) {
    showState('tqStateWaiting');
    setPauseBadge(false);
    _waitTimer = setInterval(_checkStarted, 4000);
    _checkStarted();
    return;
  }
  _beginAttempt();
}

async function _beginAttempt(){
  showState('tqStateLoading');
  try {
    const fd = new FormData();
    fd.append('post_id', POST_ID);
    const res = await fetch(API + 'start_attempt.php', { method:'POST', body:fd, credentials:'same-origin' });
    const j   = await res.json();

    if (!j || j.status !== 'success') {
      const msg = String(j?.message || `Could not start ${assessmentLower()}.`);
      if (/paused/i.test(msg)) {
        showState('tqStateWaiting');
        setPauseBadge(true);
        const sub = document.querySelector('#tqStateWaiting .tq-state-sub');
        if (sub) sub.textContent = `${assessmentLabel()} is paused by your professor. Keep this page open and wait for resume.`;
        _waitTimer = setInterval(_checkStarted, 4000);
        _checkStarted();
        return;
      }
      showError(msg);
      return;
    }

    const { attempt, post, questions, answers } = j;
    setAssessmentKind(post?.post_type || assessmentKind);

    if (attempt.status === 'submitted') {
      showResult(attempt);
      return;
    }

    state.attemptId = attempt.id;
    // Shuffle questions once for the entire attempt
    state.questions = shuffle(questions || []);
    state.answers   = {};
    if (answers) {
      Object.keys(answers).forEach(qid => {
        state.answers[qid] = answers[qid].selected_choice_id || null;
      });
    }

    state.current = 0;

    document.getElementById('tqQuizName').textContent = post.title || assessmentLabel();
    document.title = (post.title || assessmentLabel()) + ' - Tere LEARN';

    // Timer setup
    const tmodeRaw = normalizeTimeMode(post.time_mode || '');
    const quizMode = String(post.quiz_mode || '').toLowerCase();
    const modeMissing = !tmodeRaw;
    const tlSecs = parseInt(post.time_limit_seconds, 10) || 0;
    const hasPerQFromData = (questions || []).some(q => (parseInt(q.time_limit_seconds, 10) || 0) > 0);
    const inferredTmode = tmodeRaw || (hasPerQFromData ? 'per_question' : (tlSecs > 0 ? 'per_quiz' : 'none'));
    const perQFromQuestion = hasPerQFromData
      ? Math.max(...(questions || []).map(q => parseInt(q.time_limit_seconds, 10) || 0))
      : 0;

    if (inferredTmode === 'per_quiz' && tlSecs > 0 && !(quizMode === 'live' && modeMissing)) {
      const elapsed = attempt.started_at
        ? Math.floor((Date.now() - new Date(attempt.started_at.replace(' ','T')).getTime()) / 1000)
        : 0;
      state.globalSecs      = tlSecs;
      state.globalRemaining = Math.max(0, tlSecs - elapsed);
      startGlobalTimer();
    } else if (inferredTmode === 'per_question' || (quizMode === 'live' && tlSecs > 0)) {
      // Live quizzes default to per-question countdown when a time limit is configured.
      state.perQSecs = perQFromQuestion || tlSecs;
    }

    quizActive = true;
    showState('tqCard');
    renderDots();
    renderQuestion(0);
    startLiveRuntimeMonitor();

  } catch(e) {
    console.error(e);
    showError('Network error. Please check your connection and try again.');
  }
}

function showError(msg){
  document.getElementById('tqErrorMsg').textContent = msg;
  showState('tqStateError');
}

// ── Global timer ──
function startGlobalTimer(){
  clearInterval(state.globalTimer);
  document.getElementById('tqTimer').style.display = 'flex';
  updateGlobalLabel();
  state.globalTimer = setInterval(() => {
    state.globalRemaining--;
    updateGlobalLabel();
    if (state.globalRemaining <= 0) {
      clearInterval(state.globalTimer);
      autoSubmit('Time is up!');
    }
  }, 1000);
}
function updateGlobalLabel(){
  const s = Math.max(0, state.globalRemaining);
  const m = Math.floor(s/60), ss = s%60;
  document.getElementById('tqTimerLabel').textContent =
    String(m).padStart(2,'0') + ':' + String(ss).padStart(2,'0');
  document.getElementById('tqTimer').classList.toggle('urgent', s < 60);
}

// ── Per-question timer ──
function startPerQTimer(){
  clearInterval(state.perQTimer);
  const topTimer = document.getElementById('tqTimer');
  if (!state.perQSecs) {
    topTimer.style.display = 'none';
    return;
  }
  state.perQRemaining = state.perQSecs;
  topTimer.style.display = 'flex';
  updatePerQLabel();
  state.perQTimer = setInterval(() => {
    state.perQRemaining--;
    updatePerQLabel();
    // Auto-advance slightly earlier so students never see a stale "0s" state.
    if (state.perQRemaining <= 1) {
      clearInterval(state.perQTimer);
      if (state.current < state.questions.length - 1) {
        goTo(state.current + 1);
      } else {
        autoSubmit('Time is up for the last question!');
      }
    }
  }, 1000);
}
function updatePerQLabel(){
  const s = Math.max(0, state.perQRemaining);
  const mm = Math.floor(s / 60);
  const ss = s % 60;
  const stamp = String(mm).padStart(2,'0') + ':' + String(ss).padStart(2,'0');
  document.getElementById('tqTimerLabel').textContent = stamp;
  document.getElementById('tqTimer').classList.toggle('urgent', s <= 5);
}

function startLiveRuntimeMonitor(){
  clearInterval(_liveStateTimer);
  _liveStateTimer = setInterval(async () => {
    try {
      const fd = new FormData();
      fd.append('post_id', POST_ID);
      const res = await fetch(API + 'get_my_quiz_state.php', { method:'POST', body:fd, credentials:'same-origin' });
      const j = await res.json();
      const data = (j && j.data) || {};
      const quiz = data.quiz || {};
      setAssessmentKind(data.post_type || quiz.post_type || assessmentKind);
      const running = !!quiz.live_started_at && parseInt(quiz.is_force_open || 0, 10) === 1 && !quiz.live_ended_at && parseInt(quiz.is_force_closed || 0, 10) !== 1;
      const paused = !!quiz.live_started_at && parseInt(quiz.is_force_open || 0, 10) !== 1 && !quiz.live_ended_at && parseInt(quiz.is_force_closed || 0, 10) !== 1;
      const ended = !!quiz.live_ended_at || parseInt(quiz.is_force_closed || 0, 10) === 1;

      if (ended && !state.submitting) {
        clearInterval(_liveStateTimer);
        _liveStateTimer = null;
        autoSubmit(`${assessmentLabel()} has ended`);
        return;
      }
      if (paused && !_pausedInRuntime) {
        _pausedInRuntime = true;
        clearInterval(state.globalTimer);
        clearInterval(state.perQTimer);
        showState('tqStateWaiting');
        setPauseBadge(true);
        const sub = document.querySelector('#tqStateWaiting .tq-state-sub');
        if (sub) sub.textContent = `${assessmentLabel()} is paused by your professor. Keep this page open and wait for resume.`;
      } else if (running && _pausedInRuntime) {
        _pausedInRuntime = false;
        setPauseBadge(false);
        showState('tqCard');
        if (state.globalSecs > 0) startGlobalTimer();
        if (state.perQSecs > 0) {
          const topTimer = document.getElementById('tqTimer');
          topTimer.style.display = 'flex';
          updatePerQLabel();
          state.perQTimer = setInterval(() => {
            state.perQRemaining--;
            updatePerQLabel();
            if (state.perQRemaining <= 1) {
              clearInterval(state.perQTimer);
              if (state.current < state.questions.length - 1) goTo(state.current + 1);
              else autoSubmit('Time is up for the last question!');
            }
          }, 1000);
        }
      }
    } catch(e){}
  }, 3000);
}

// ── Render question ──
function renderQuestion(idx){
  const q = state.questions[idx];
  if (!q) return;
  state.current = idx;

  const total = state.questions.length;
  const answered = Object.keys(state.answers).filter(k => state.answers[k]).length;
  const pct = Math.max(0, Math.min(100, Math.round((answered / total) * 100)));

  document.getElementById('tqProgressBar').style.width = pct + '%';
  document.getElementById('tqProgressLabel').textContent = pct + '%';
  document.getElementById('tqQCounter').textContent = `Question ${idx+1} of ${total}`;
  document.getElementById('tqQPts').innerHTML = `<i class="fas fa-star"></i> ${parseFloat(q.points)||1} pt${parseFloat(q.points)!==1?'s':''}`;
  document.getElementById('tqQText').textContent = q.question || q.question_text || '';

  // Shuffle choices every render
  const shuffledChoices = shuffle(q.choices || []);

  const choicesEl = document.getElementById('tqChoices');
  choicesEl.innerHTML = '';
  shuffledChoices.forEach((c) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    // Never visually preselect answers to avoid hinting on revisit/resume.
    btn.className = 'tq-choice';
    btn.innerHTML = `<span class="tq-choice-text">${esc(c.choice_text || c.text || '')}</span>`;
    btn.onclick = () => selectChoice(q.id, c.id, btn);
    choicesEl.appendChild(btn);
  });

  // Live flow: no manual submit button.
  document.getElementById('tqSubmitBtn').style.display = 'none';

  updateDots();
  startPerQTimer();
}

function renderDots(){
  const nav = document.getElementById('tqDotNav');
  nav.innerHTML = state.questions.map((_,i) =>
    `<div class="tq-dot" title="Question ${i+1}"></div>`
  ).join('');
}
function updateDots(){
  const dots = document.querySelectorAll('.tq-dot');
  dots.forEach((d,i) => {
    d.className = 'tq-dot';
    if (i === state.current) d.classList.add('current');
    else if (state.answers[state.questions[i]?.id]) d.classList.add('answered');
  });
}

function goTo(idx){
  if (idx < 0 || idx >= state.questions.length) return;
  renderQuestion(idx);
}

// ── Select choice + auto-advance ──
async function selectChoice(questionId, choiceId, btnEl){
  // Flash feedback
  if (btnEl) {
    btnEl.classList.add('selected', 'flash');
    setTimeout(() => btnEl.classList.remove('flash'), 260);
  }

  state.answers[questionId] = choiceId;
  updateDots();

  // Auto-advance after brief delay so user sees selection
  const isLast = state.current === state.questions.length - 1;

  // Save in background
  try {
    const fd = new FormData();
    fd.append('attempt_id',  state.attemptId);
    fd.append('question_id', questionId);
    fd.append('choice_id',   choiceId);
    fetch(API + 'save_answer.php', { method:'POST', body:fd, credentials:'same-origin' });
  } catch(e){ /* silent */ }

  if (!isLast) {
    // Brief pause so selection is visible, then advance
    await new Promise(r => setTimeout(r, 280));
    goTo(state.current + 1);
  } else {
    await new Promise(r => setTimeout(r, 280));
    submitQuiz();
  }
}

// ── Submit ──
async function confirmSubmit(){
  const label = assessmentLabel();
  const total     = state.questions.length;
  const answered  = Object.keys(state.answers).filter(k => state.answers[k]).length;
  const unanswered = total - answered;

  const result = await Swal.fire({
    title: `Submit ${label}?`,
    html: `<div style="font-size:.88rem;line-height:1.7;">
      <div><i class="fas fa-check-circle" style="color:var(--primary,#1a9e78);margin-right:.4rem;"></i>
        <strong>${answered}</strong> of <strong>${total}</strong> questions answered</div>
      ${unanswered > 0 ? `<div style="margin-top:.4rem;color:#f57c00;"><i class="fas fa-exclamation-triangle" style="margin-right:.4rem;"></i>${unanswered} unanswered question${unanswered!==1?'s':''}</div>` : ''}
      <div style="margin-top:.75rem;font-size:.8rem;color:#5f6368;">You cannot change your answers after submitting.</div>
    </div>`,
    icon: unanswered > 0 ? 'warning' : 'question',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-paper-plane"></i> Yes, Submit',
    cancelButtonText: 'Review first',
    confirmButtonColor: '#d93025',
    reverseButtons: true,
  });
  if (!result.isConfirmed) return;
  submitQuiz();
}

async function submitQuiz(){
  if (state.submitting) return;
  state.submitting = true;
  clearInterval(state.globalTimer);
  clearInterval(state.perQTimer);
  clearInterval(_liveStateTimer);
  _liveStateTimer = null;
  quizActive = false;

  Swal.fire({ title:'Submitting…', didOpen:()=>Swal.showLoading(), allowOutsideClick:false });

  try {
    // Last answer may submit immediately; force completion UI to 100%.
    document.getElementById('tqProgressBar').style.width = '100%';
    document.getElementById('tqProgressLabel').textContent = '100%';

    const fd = new FormData();
    fd.append('attempt_id', state.attemptId);
    fd.append('post_id',    POST_ID);
    const res = await fetch(API + 'submit_attempt.php', { method:'POST', body:fd, credentials:'same-origin' });
    const j   = await res.json();
    Swal.close();

    if (!j || j.status !== 'success') {
      state.submitting = false;
      quizActive = true;
      Swal.fire({ icon:'error', title:'Submission failed', text: j?.message || 'Please try again.' });
      return;
    }

    showResult(j.attempt || { score: j.score });
  } catch(e) {
    Swal.close();
    state.submitting = false;
    quizActive = true;
    Swal.fire({ icon:'error', title:'Network error', text:'Could not submit. Please try again.' });
  }
}

async function autoSubmit(reason){
  quizActive = false;
  clearInterval(state.globalTimer);
  clearInterval(state.perQTimer);
  clearInterval(_liveStateTimer);
  _liveStateTimer = null;
  await Swal.fire({
    icon:'warning', title: reason,
    text:`Your ${assessmentLower()} is being submitted automatically.`,
    timer:2500, showConfirmButton:false, allowOutsideClick:false
  });
  submitQuiz();
}

function showResult(attempt){
  quizActive = false;
  clearInterval(state.globalTimer);
  clearInterval(state.perQTimer);
  clearInterval(_liveStateTimer);
  _liveStateTimer = null;

  const score = attempt?.score ?? attempt?.total_score ?? null;
  const total = state.questions.reduce((s,q)=>s+(parseFloat(q.points)||1), 0) || null;
  const pct   = (score !== null && total) ? (Number(score) / Number(total)) * 100 : null;

  // Hide progress/timer chrome on final result screen.
  setQuizChromeVisible(false);
  document.getElementById('tqProgressBar').style.width = '100%';
  document.getElementById('tqProgressLabel').textContent = '100%';

  document.getElementById('tqScoreVal').textContent =
    score !== null && total !== null ? `${Math.round(Number(score))}/${Math.round(Number(total))}` : '—';
  const scoreEl = document.getElementById('tqScoreVal');
  const ringEl = document.querySelector('.tq-score-ring');
  if (scoreEl) {
    let col = 'var(--text-muted)';
    if (pct !== null) {
      if (pct > 50) col = '#1a9e78';
      else if (pct === 50) col = '#f59e0b';
      else col = '#d93025';
    }
    scoreEl.style.color = col;
    if (ringEl) ringEl.style.borderColor = col;
  }
  document.getElementById('tqResultTitle').textContent = `${assessmentLabel()} Submitted!`;
  document.getElementById('tqResultSub').textContent = 'Your answers have been recorded.';

  showState('tqResult');
}

// Dark mode
if (localStorage.getItem('darkMode') === '1') document.body.classList.add('dark');

// Start
applyAssessmentCopy();
setQuizChromeVisible(true);
init();
</script>
</body>
</html>
