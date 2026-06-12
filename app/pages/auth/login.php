<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TereLearn — Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green:      #4caf50;
      --green-dark: #2e7d32;
      --red:        #ef4444;
      --yellow:     #f59e0b;
      --text:       #1a202c;
      --muted:      #718096;
      --border:     #e2e8f0;
      --bg:         #f5f7fa;
      --white:      #ffffff;
      --radius:     10px;
      --shadow:     0 10px 40px rgba(0,0,0,.09);
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    /* ── container ── */
    .login-container {
      display: flex;
      background: white;
      border-radius: 14px;
      box-shadow: var(--shadow);
      overflow: hidden;
      max-width: 900px;
      width: 100%;
      animation: fadeUp .55s cubic-bezier(.23,1,.32,1);
    }
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(28px); }
      to   { opacity:1; transform:translateY(0); }
    }

    /* ── left branding ── */
    .branding {
      flex: 1;
      background: linear-gradient(135deg, var(--green), var(--green-dark));
      padding: 56px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: white;
      position: relative;
      overflow: hidden;
    }
    .branding::before {
      content: '';
      position: absolute; top:-50%; right:-50%;
      width:200%; height:200%;
      background: radial-gradient(circle, rgba(255,255,255,.08) 1px, transparent 1px);
      background-size: 48px 48px;
      animation: drift 22s linear infinite;
    }
    @keyframes drift { to { transform: translate(48px,48px); } }

    .logo-box {
      width:110px; height:110px;
      background: rgba(255,255,255,.15);
      border: 2px solid rgba(255,255,255,.3);
      border-radius: 22px;
      display: flex; align-items: center; justify-content: center;
      font-size: 56px;
      margin: 0 auto 22px;
      backdrop-filter: blur(8px);
      position: relative; z-index:1;
      transition: transform .3s;
    }
    .logo-box:hover { transform: translateY(-4px) scale(1.05); }
    .logo-box img { width:90px; }

    .branding h1 { font-size:2.2rem; font-weight:700; position:relative; z-index:1; }
    .branding p  { opacity:.88; font-weight:300; margin-top:6px; position:relative; z-index:1; }

    .pills {
      display:flex; gap:10px; flex-wrap:wrap; justify-content:center;
      margin-top:26px; position:relative; z-index:1;
    }
    .pill {
      font-size:.8rem; padding:6px 12px;
      background:rgba(255,255,255,.12);
      border:1px solid rgba(255,255,255,.22);
      border-radius:8px; backdrop-filter:blur(6px);
      transition: all .25s;
    }
    .pill:hover { background:rgba(255,255,255,.22); transform:translateY(-2px); }

    /* ── right form ── */
    .form-side {
      flex: 1;
      padding: 56px 44px;
      display: flex; flex-direction:column; justify-content:center;
    }

    .form-side h2 { font-size:1.75rem; font-weight:700; color:var(--text); }
    .form-side .sub { color:var(--muted); font-size:.92rem; margin-top:4px; margin-bottom:32px; }

    /* alert */
    .alert-box {
      display:none; padding:13px 15px; border-radius:var(--radius);
      margin-bottom:18px; font-size:.88rem; font-weight:500;
      animation: slideDown .28s ease;
    }
    @keyframes slideDown { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
    .alert-box.error   { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
    .alert-box.success { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
    .alert-box.warning { background:#fffbeb; color:#92400e; border:1px solid #fde68a; }
    .alert-box.info    { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }

    /* form controls */
    .form-group { margin-bottom:20px; }
    .form-group label {
      display:block; margin-bottom:7px;
      font-weight:600; font-size:.88rem; color:var(--text);
      transition:color .2s;
    }
    .form-group:focus-within label { color:var(--green); }

    .inp-wrap { position:relative; }
    .form-control {
      width:100%; padding:11px 14px;
      border:2px solid var(--border); border-radius:var(--radius);
      font-size:.92rem; background:#f9fafb;
      font-family:inherit; transition:all .25s;
    }
    .form-control:focus {
      border-color:var(--green); background:white;
      box-shadow:0 0 0 3px rgba(76,175,80,.12); outline:none;
    }
    .form-control::placeholder { color:#cbd5e0; }
    .form-control:disabled { opacity:.6; }

    .eye-btn {
      position:absolute; right:12px; top:50%; transform:translateY(-50%);
      background:none; border:none; cursor:pointer; color:var(--muted);
      font-size:1.1rem; transition:color .2s;
    }
    .eye-btn:hover { color:var(--green); }

    /* primary button */
    .btn-primary-login {
      width:100%; padding:12px;
      background:linear-gradient(135deg, var(--green), var(--green-dark));
      color:white; border:none; border-radius:var(--radius);
      font-weight:700; font-size:.95rem; cursor:pointer;
      position:relative; overflow:hidden;
      transition:all .25s;
    }
    .btn-primary-login::before {
      content:''; position:absolute; top:50%; left:50%;
      width:0; height:0; background:rgba(255,255,255,.25);
      border-radius:50%; transform:translate(-50%,-50%);
      transition:width .5s, height .5s;
    }
    .btn-primary-login:hover:not(:disabled)::before { width:300px; height:300px; }
    .btn-primary-login:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 10px 22px rgba(76,175,80,.3); }
    .btn-primary-login:disabled { opacity:.55; cursor:not-allowed; }
    .btn-primary-login span { position:relative; z-index:1; display:flex; align-items:center; justify-content:center; gap:8px; }

    .btn-ghost {
      width:100%; padding:11px;
      background:var(--bg); border:2px solid var(--border);
      border-radius:var(--radius); font-weight:600; font-size:.88rem;
      color:var(--text); cursor:pointer; transition:all .2s;
    }
    .btn-ghost:hover:not(:disabled) { border-color:var(--green); color:var(--green); }
    .btn-ghost:disabled { opacity:.5; cursor:not-allowed; }

    /* spinner */
    .spin {
      display:inline-block; width:15px; height:15px;
      border:2px solid rgba(255,255,255,.35); border-top-color:white;
      border-radius:50%; animation:spin .75s linear infinite;
    }
    @keyframes spin { to { transform:rotate(360deg); } }
    .spin-green {
      border-color:rgba(76,175,80,.3); border-top-color:var(--green);
    }

    /* footer link */
    .form-footer { margin-top:18px; text-align:center; font-size:.85rem; color:var(--muted); }
    .form-footer a { color:var(--green); font-weight:600; text-decoration:none; transition:opacity .2s; }
    .form-footer a:hover { opacity:.7; }

    /* ── OTP step ── */
    #otpStep { display:none; }

    .otp-info {
      background:#f0fdf4; border:1px solid #bbf7d0;
      border-radius:var(--radius); padding:14px;
      font-size:.85rem; color:#15803d; margin-bottom:20px;
      line-height:1.5;
    }
    .otp-info strong { font-weight:700; }

    .otp-input {
      text-align:center; font-size:1.8rem; letter-spacing:.6em;
      font-weight:700; padding:14px;
    }

    .resend-row {
      display:flex; justify-content:space-between; align-items:center;
      margin-top:10px; font-size:.82rem;
    }
    .resend-btn {
      background:none; border:none; cursor:pointer;
      color:var(--green); font-weight:600; font-size:.82rem;
      transition:opacity .2s; padding:0;
    }
    .resend-btn:disabled { opacity:.45; cursor:not-allowed; }
    #cooldownTimer { color:var(--muted); }

    /* ── modal base ── */
    .modal-overlay {
      display:none; position:fixed; inset:0;
      background:rgba(0,0,0,.45); z-index:1000;
      align-items:center; justify-content:center;
    }
    .modal-overlay.active { display:flex; }
    .modal-box {
      background:white; border-radius:14px; padding:40px;
      max-width:500px; width:95%;
      box-shadow:0 20px 60px rgba(0,0,0,.25);
      animation:popIn .28s ease;
      max-height:92vh; overflow-y:auto;
    }
    @keyframes popIn { from{opacity:0;transform:scale(.92)} to{opacity:1;transform:scale(1)} }
    .modal-box h3 { font-size:1.4rem; font-weight:700; color:var(--text); margin-bottom:8px; }
    .modal-box .desc { color:var(--muted); font-size:.9rem; margin-bottom:24px; }
    .modal-btns { display:flex; gap:12px; }
    .modal-btn {
      flex:1; padding:12px; border:none; border-radius:var(--radius);
      font-weight:700; cursor:pointer; font-size:.9rem; transition:all .2s;
    }
    .modal-btn.primary { background:var(--green); color:white; }
    .modal-btn.primary:hover:not(:disabled) { background:var(--green-dark); transform:translateY(-1px); }
    .modal-btn.primary:disabled { background:var(--border); cursor:not-allowed; }
    .modal-btn.secondary { background:var(--bg); color:var(--text); border:2px solid var(--border); }
    .modal-btn.secondary:hover { border-color:var(--green); color:var(--green); }

    /* ── password strength ── */
    .strength-bar { height:5px; background:var(--border); border-radius:3px; margin:10px 0 6px; overflow:hidden; }
    .strength-fill { height:100%; width:0; border-radius:3px; transition:all .3s; }
    .strength-fill.weak      { width:20%; background:#ef4444; }
    .strength-fill.fair      { width:40%; background:#f97316; }
    .strength-fill.good      { width:60%; background:#eab308; }
    .strength-fill.strong    { width:80%; background:#84cc16; }
    .strength-fill.excellent { width:100%; background:#22c55e; }
    .strength-label { font-size:.8rem; font-weight:700; }
    .strength-label.weak      { color:#ef4444; }
    .strength-label.fair      { color:#f97316; }
    .strength-label.good      { color:#eab308; }
    .strength-label.strong    { color:#84cc16; }
    .strength-label.excellent { color:#22c55e; }

    .req-list { background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:14px; margin-top:14px; }
    .req-list-title { font-size:.82rem; font-weight:700; color:var(--text); margin-bottom:10px; }
    .req-item { display:flex; align-items:center; gap:8px; font-size:.82rem; color:var(--muted); margin-bottom:7px; transition:color .25s; }
    .req-item:last-child { margin-bottom:0; }
    .req-check { width:17px; height:17px; border-radius:50%; background:var(--border); display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; flex-shrink:0; transition:all .25s; }
    .req-item.met { color:#22c55e; }
    .req-item.met .req-check { background:#22c55e; color:white; }

    /* ── attempted-login alert modal ── */
    #suspiciousModal .modal-box { border-top:4px solid var(--yellow); }
    .suspicious-icon { font-size:2.5rem; text-align:center; margin-bottom:12px; }

    /* ── sms alt row ── */
    .alt-row { display:flex; align-items:center; gap:8px; margin:14px 0 0; }
    .alt-row hr { flex:1; border:none; border-top:1px solid var(--border); }
    .alt-row span { font-size:.78rem; color:var(--muted); white-space:nowrap; }
    .btn-sms {
      width:100%; margin-top:10px; padding:11px;
      background:none; border:2px solid var(--border);
      border-radius:var(--radius); font-weight:600; font-size:.85rem;
      color:var(--text); cursor:pointer; transition:all .2s;
      display:flex; align-items:center; justify-content:center; gap:7px;
    }
    .btn-sms:hover:not(:disabled) { border-color:#16a34a; color:#16a34a; }
    .btn-sms:disabled { opacity:.45; cursor:not-allowed; }

    @media (max-width:768px) {
      .login-container { flex-direction:column; }
      .branding { padding:36px 28px; }
      .form-side { padding:36px 28px; }
    }
  </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════
     MAIN LOGIN CARD
════════════════════════════════════════════════ -->
<div class="login-container">

  <!-- LEFT: branding -->
  <div class="branding">
    <div class="logo-box">
      <img src="dist/img/goldtere.png" alt="TereLearn" onerror="this.replaceWith(document.createTextNode('📚'))">
    </div>
    <div style="position:relative;z-index:1">
      <h1>TereLearn</h1>
      <p>Education that transcends</p>
      <div class="pills">
        <div class="pill">✨ Perseverance</div>
        <div class="pill">🎯 Integrity</div>
        <div class="pill">🙏 Humility</div>
      </div>
    </div>
  </div>

  <!-- RIGHT: form -->
  <div class="form-side">

    <!-- ── STEP 1: credentials ── -->
    <div id="credStep">
      <h2>Welcome Back</h2>
      <p class="sub">Sign in to your account</p>

      <div class="alert-box" id="credAlert"></div>

      <div class="form-group">
        <label for="username">Username or Email</label>
        <input type="text" id="username" class="form-control" placeholder="Enter your username" autocomplete="off">
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="inp-wrap">
          <input type="password" id="password" class="form-control" placeholder="Enter your password" autocomplete="new-password">
          <button class="eye-btn" type="button" onclick="toggleEye('password',this)">👁️</button>
        </div>
      </div>

      <button class="btn-primary-login" id="loginBtn" onclick="doLogin()">
        <span><span id="loginBtnText">Login</span></span>
      </button>

      <div class="form-footer">
        Forgot password? <a href="#" onclick="openForgot(event)">Reset it</a>
      </div>
    </div>

    <!-- ── STEP 2: OTP verification ── -->
    <div id="otpStep" style="display:none">
      <h2>Verify It's You</h2>
      <p class="sub" id="otpSubtitle">Check your registered email for the code.</p>

      <div class="alert-box" id="otpAlert"></div>

      <div class="otp-info" id="otpInfo">
        A <strong>6-digit code</strong> was sent to <strong id="otpDestDisplay"></strong>.<br>
        The code expires in <strong>10 minutes</strong>.
      </div>

      <div class="form-group">
        <label>Verification Code</label>
        <input type="text" id="otpCode" class="form-control otp-input"
               maxlength="6" placeholder="——————"
               oninput="this.value=this.value.replace(/\D/g,'')">
      </div>

      <button class="btn-primary-login" id="otpVerifyBtn" onclick="verifyOTP()">
        <span><span id="otpBtnText">Verify Code</span></span>
      </button>

      <!-- SMS alternative -->
      <div class="alt-row"><hr><span>didn't receive it?</span><hr></div>
      <div class="resend-row">
        <button class="resend-btn" id="resendEmailBtn" onclick="resendOTP('email')">Resend to email</button>
        <span id="cooldownTimer"></span>
      </div>
      <button class="btn-sms" id="smsFallbackBtn" onclick="resendOTP('sms')">
        📱 Send code via SMS instead
      </button>
      <div style="margin-top:14px;">
        <button class="btn-ghost" onclick="backToLogin()">← Back to Login</button>
      </div>
    </div>

  </div><!-- /form-side -->
</div><!-- /login-container -->


<!-- ═══════════════════════════════════════════════
     MODAL: SUSPICIOUS LOGIN ALERT
════════════════════════════════════════════════ -->
<div class="modal-overlay" id="suspiciousModal">
  <div class="modal-box">
    <div class="suspicious-icon">⚠️</div>
    <h3>Suspicious Login Detected</h3>
    <p class="desc">
      Someone is repeatedly attempting to log in using your credentials.<br><br>
      <strong id="suspiciousDetail"></strong><br><br>
      If this wasn't you, consider changing your password immediately.
    </p>
    <div class="modal-btns">
      <button class="modal-btn secondary" onclick="closeSuspicious()">Dismiss</button>
      <button class="modal-btn primary" onclick="openForgot(null, true)">Change My Password</button>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════
     MODAL: ROLE SELECTION (Dean)
════════════════════════════════════════════════ -->
<div class="modal-overlay" id="roleModal">
  <div class="modal-box">
    <h3>Select Your Role</h3>
    <p class="desc">You have faculty privileges. How would you like to proceed?</p>
    <div class="modal-btns">
      <button class="modal-btn primary" onclick="selectRole('faculty')">👨‍🏫 Professor</button>
      <button class="modal-btn primary" onclick="selectRole('admin')">⚙️ Sub-Admin</button>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════
     MODAL: CHANGE PASSWORD (first login)
════════════════════════════════════════════════ -->
<div class="modal-overlay" id="pwdModal">
  <div class="modal-box">
    <h3>🔐 Set Your Password</h3>
    <p class="desc">For your security, please create a strong password before continuing.</p>

    <div class="alert-box" id="pwdAlert"></div>

    <div class="form-group">
      <label>New Password</label>
      <div class="inp-wrap">
        <input type="password" id="newPwd" class="form-control" placeholder="Enter a strong password" autocomplete="new-password">
        <button class="eye-btn" type="button" onclick="toggleEye('newPwd',this)">👁️</button>
      </div>
      <div class="strength-bar"><div class="strength-fill" id="sBar"></div></div>
      <div class="strength-label" id="sLabel">Strength: —</div>
      <div class="req-list">
        <div class="req-list-title">Requirements:</div>
        <div class="req-item" id="r-len"><div class="req-check">✓</div>At least 12 characters</div>
        <div class="req-item" id="r-up"><div class="req-check">✓</div>One uppercase letter (A-Z)</div>
        <div class="req-item" id="r-lo"><div class="req-check">✓</div>One lowercase letter (a-z)</div>
        <div class="req-item" id="r-num"><div class="req-check">✓</div>One number (0-9)</div>
        <div class="req-item" id="r-sp"><div class="req-check">✓</div>One special character (!@#$%^&*)</div>
      </div>
    </div>

    <div class="form-group" id="confirmGroup" style="display:none; margin-top:20px;">
      <label>Confirm Password</label>
      <div class="inp-wrap">
        <input type="password" id="confirmPwd" class="form-control" placeholder="Re-enter your password" autocomplete="new-password">
        <button class="eye-btn" type="button" onclick="toggleEye('confirmPwd',this)">👁️</button>
      </div>
      <div id="matchMsg" style="font-size:.82rem;margin-top:6px;font-weight:600;color:#cbd5e0;"></div>
    </div>

    <div style="margin-top:22px;">
      <button class="btn-primary-login" id="savePwdBtn" disabled onclick="saveNewPassword()">
        <span><span id="savePwdText">Update Password</span></span>
      </button>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════
     MODAL: FORGOT / RECOVERY
════════════════════════════════════════════════ -->
<div class="modal-overlay" id="forgotModal">
  <div class="modal-box">
    <h3>Account Recovery</h3>
    <p class="desc">Enter your username or email. We'll send a recovery code via email, or SMS if unavailable.</p>
    <div class="alert-box" id="forgotAlert"></div>
    <div class="form-group">
      <label>Username or Email</label>
      <input type="text" id="forgotInput" class="form-control" placeholder="Enter username or email">
    </div>
    <div class="modal-btns">
      <button class="modal-btn secondary" onclick="document.getElementById('forgotModal').classList.remove('active')">Cancel</button>
      <button class="modal-btn primary" id="forgotBtn" onclick="sendRecovery()">Send Recovery Code</button>
    </div>
  </div>
</div>


<script>
/* ═══════════════════════════════════════════
   STATE
═══════════════════════════════════════════ */
let currentUser   = null;
let isFirstLogin  = false;
let otpChannel    = 'email';   // 'email' | 'sms'
let otpDest       = '';        // masked destination shown to user
let resendTimer   = null;
let cooldownLeft  = 0;
let failedAttempts = 0;
const MAX_FAILS   = 5;

/* ═══════════════════════════════════════════
   HELPERS
═══════════════════════════════════════════ */
function showAlert(id, msg, type) {
  const el = document.getElementById(id);
  el.textContent = msg;
  el.className   = `alert-box ${type}`;
  el.style.display = 'block';
}
function hideAlert(id) {
  document.getElementById(id).style.display = 'none';
}
function setBtn(id, loading, label) {
  const b  = document.getElementById(id);
  b.disabled = loading;
  const txt  = b.querySelector('span span') || b;
  if (txt.tagName !== 'BUTTON') txt.innerHTML = loading ? `${label} <span class="spin"></span>` : label;
  else b.innerHTML = loading ? `<span><span>${label} <span class="spin"></span></span></span>` : `<span><span>${label}</span></span>`;
}
function toggleEye(id, btn) {
  const inp = document.getElementById(id);
  inp.type  = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁️' : '🙈';
}

/* ═══════════════════════════════════════════
   STEP 1 — CREDENTIALS
═══════════════════════════════════════════ */
document.addEventListener('keydown', e => {
  if (e.key === 'Enter') {
    if (!document.getElementById('credStep').style.display || document.getElementById('credStep').style.display !== 'none') doLogin();
    else verifyOTP();
  }
});

async function doLogin() {
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;
  if (!username || !password) { showAlert('credAlert','Please fill in all fields','error'); return; }

  hideAlert('credAlert');
  setBtn('loginBtn', true, 'Checking…');

  try {
    const res  = await fetch('API/authenticate.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ username, password })
    });
    const data = await res.json();

    if (data.success) {
      failedAttempts = 0;
      currentUser    = data.user;
      isFirstLogin   = Boolean(data.first_login);

      // Trigger OTP step
      setBtn('loginBtn', false, 'Login');
      await triggerOTP('email');

    } else {
      failedAttempts++;
      setBtn('loginBtn', false, 'Login');
      showAlert('credAlert', data.message || 'Invalid username or password.', 'error');

      // After MAX_FAILS — show suspicious modal to the ACCOUNT OWNER
      if (failedAttempts >= MAX_FAILS && data.owner_email) {
        await notifySuspicious(username, data.owner_email, data.owner_phone);
      }
    }
  } catch(e) {
    setBtn('loginBtn', false, 'Login');
    showAlert('credAlert', 'Connection error. Please try again.', 'error');
  }
}

/* ═══════════════════════════════════════════
   TRIGGER OTP SEND
═══════════════════════════════════════════ */
async function triggerOTP(channel) {
  otpChannel = channel;
  try {
    const res  = await fetch('API/send_otp.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ user_id: currentUser.id, channel })
    });
    const data = await res.json();

    if (data.success) {
      otpDest = data.destination;                         // masked "j***@gmail.com" or "+63*****6789"
      document.getElementById('otpDestDisplay').textContent = otpDest;
      document.getElementById('otpSubtitle').textContent =
        channel === 'email'
          ? 'Check your registered email for the code.'
          : 'Check your phone — we sent you an SMS.';

      // switch panels
      document.getElementById('credStep').style.display = 'none';
      document.getElementById('otpStep').style.display  = '';
      hideAlert('otpAlert');
      startCooldown(60);
    } else {
      showAlert('credAlert', data.message || 'Could not send OTP.', 'error');
    }
  } catch(e) {
    showAlert('credAlert', 'Failed to send OTP. Please retry.', 'error');
  }
}

/* ═══════════════════════════════════════════
   RESEND OTP
═══════════════════════════════════════════ */
async function resendOTP(channel) {
  if (cooldownLeft > 0) return;
  hideAlert('otpAlert');

  const btn = channel === 'email'
    ? document.getElementById('resendEmailBtn')
    : document.getElementById('smsFallbackBtn');
  btn.disabled = true;

  try {
    const res  = await fetch('API/send_otp.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ user_id: currentUser.id, channel })
    });
    const data = await res.json();
    if (data.success) {
      otpChannel = channel;
      otpDest    = data.destination;
      document.getElementById('otpDestDisplay').textContent = otpDest;
      document.getElementById('otpSubtitle').textContent =
        channel === 'email' ? 'Check your registered email for the code.'
                            : 'Check your phone — we sent you an SMS.';
      showAlert('otpAlert', `Code re-sent via ${channel === 'email' ? 'email' : 'SMS'}.`, 'success');
      startCooldown(60);
    } else {
      showAlert('otpAlert', data.message || 'Resend failed.', 'error');
    }
  } catch(e) {
    showAlert('otpAlert', 'Resend failed. Try again.', 'error');
  } finally {
    btn.disabled = false;
  }
}

/* ── 60-second cooldown timer ── */
function startCooldown(seconds) {
  cooldownLeft = seconds;
  const resendE = document.getElementById('resendEmailBtn');
  const resendS = document.getElementById('smsFallbackBtn');
  const timer   = document.getElementById('cooldownTimer');
  resendE.disabled = true;
  resendS.disabled = true;

  clearInterval(resendTimer);
  resendTimer = setInterval(() => {
    cooldownLeft--;
    timer.textContent = cooldownLeft > 0 ? `Wait ${cooldownLeft}s` : '';
    if (cooldownLeft <= 0) {
      clearInterval(resendTimer);
      resendE.disabled = false;
      resendS.disabled = false;
    }
  }, 1000);
}

/* ═══════════════════════════════════════════
   STEP 2 — VERIFY OTP
═══════════════════════════════════════════ */
async function verifyOTP() {
  const code = document.getElementById('otpCode').value.trim();
  if (code.length !== 6) { showAlert('otpAlert','Enter the 6-digit code','error'); return; }

  hideAlert('otpAlert');
  setBtn('otpVerifyBtn', true, 'Verifying…');

  try {
    const res  = await fetch('API/verify_otp.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ user_id: currentUser.id, code })
    });
    const data = await res.json();

    if (data.success) {
      setBtn('otpVerifyBtn', false, 'Verify Code');
      clearInterval(resendTimer);
      proceedAfterOTP();
    } else {
      setBtn('otpVerifyBtn', false, 'Verify Code');
      showAlert('otpAlert', data.message || 'Invalid or expired code.', 'error');
    }
  } catch(e) {
    setBtn('otpVerifyBtn', false, 'Verify Code');
    showAlert('otpAlert', 'Verification error. Try again.', 'error');
  }
}

/* ═══════════════════════════════════════════
   POST-OTP ROUTING
═══════════════════════════════════════════ */
function proceedAfterOTP() {
  if (isFirstLogin) {
    // Show password change modal
    document.getElementById('otpStep').style.display = 'none';
    document.getElementById('pwdModal').classList.add('active');
    initStrength();
    return;
  }
  routeUser(currentUser);
}

function routeUser(user) {
  const lvl  = user.user_level_id;
  const dean = user.is_dean;
  if      (lvl === 1) { showAlert('credAlert','✓ Welcome, System Admin!','success'); setTimeout(()=> location.href='subadmin.php',  1400); }
  else if (lvl === 2) {
    if (dean === 1) { document.getElementById('roleModal').classList.add('active'); }
    else            { showAlert('credAlert','✓ Welcome, Professor!','success'); setTimeout(()=> location.href='professor.php', 1400); }
  }
  else if (lvl === 3) { showAlert('credAlert','✓ Welcome!','success'); setTimeout(()=> location.href='student.php', 1400); }
  else                { showAlert('credAlert','Unknown role','error'); }
}

function selectRole(role) {
  document.getElementById('roleModal').classList.remove('active');
  if (role === 'faculty') { showAlert('credAlert','✓ Welcome, Professor!','success'); setTimeout(()=> location.href='professor.php', 1400); }
  else                    { showAlert('credAlert','✓ Welcome, Sub-Admin!','success'); setTimeout(()=> location.href='analytics.php', 1400); }
}

function backToLogin() {
  document.getElementById('otpStep').style.display = 'none';
  document.getElementById('credStep').style.display = '';
  document.getElementById('otpCode').value = '';
  clearInterval(resendTimer);
  hideAlert('otpAlert');
}

/* ═══════════════════════════════════════════
   SUSPICIOUS LOGIN NOTIFICATION
═══════════════════════════════════════════ */
async function notifySuspicious(attemptedUser, ownerEmail, ownerPhone) {
  // Call backend to send notification email/SMS to the real account owner
  try {
    await fetch('API/notify_suspicious.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ username: attemptedUser, owner_email: ownerEmail, owner_phone: ownerPhone })
    });
  } catch(e) { /* silent */ }

  // Show modal on the current screen
  document.getElementById('suspiciousDetail').textContent =
    `Account: ${attemptedUser} — ${MAX_FAILS} failed attempts detected. A notification has been sent to the account owner.`;
  document.getElementById('suspiciousModal').classList.add('active');
}

function closeSuspicious() {
  document.getElementById('suspiciousModal').classList.remove('active');
  failedAttempts = 0;
}

/* ═══════════════════════════════════════════
   PASSWORD STRENGTH (first-login modal)
═══════════════════════════════════════════ */
const POLICY = {
  length:    p => p.length >= 12,
  uppercase: p => /[A-Z]/.test(p),
  lowercase: p => /[a-z]/.test(p),
  number:    p => /[0-9]/.test(p),
  special:   p => /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(p)
};

function initStrength() {
  const newPwd = document.getElementById('newPwd');
  const conPwd = document.getElementById('confirmPwd');
  newPwd.addEventListener('input', () => {
    const p      = newPwd.value;
    const checks = Object.keys(POLICY).map(k => POLICY[k](p));
    const allMet = checks.every(Boolean);
    const met    = checks.filter(Boolean).length;

    document.getElementById('r-len').classList.toggle('met', POLICY.length(p));
    document.getElementById('r-up').classList.toggle('met',  POLICY.uppercase(p));
    document.getElementById('r-lo').classList.toggle('met',  POLICY.lowercase(p));
    document.getElementById('r-num').classList.toggle('met', POLICY.number(p));
    document.getElementById('r-sp').classList.toggle('met',  POLICY.special(p));

    let lvl='', lbl='Strength: —';
    if (!p.length)   { lvl=''; lbl='Strength: —'; }
    else if (allMet) { lvl='excellent'; lbl='Strength: Excellent'; }
    else if (met>=4) { lvl='strong';    lbl='Strength: Strong'; }
    else if (met>=3) { lvl='good';      lbl='Strength: Good'; }
    else if (met>=2) { lvl='fair';      lbl='Strength: Fair'; }
    else             { lvl='weak';      lbl='Strength: Weak'; }

    const sb = document.getElementById('sBar');
    const sl = document.getElementById('sLabel');
    sb.className = 'strength-fill ' + lvl;
    sl.className = 'strength-label ' + lvl;
    sl.textContent = lbl;

    document.getElementById('confirmGroup').style.display = allMet ? '' : 'none';
    if (!allMet) conPwd.value = '';
    evalSave();
  });
  conPwd.addEventListener('input', evalSave);
}

function evalSave() {
  const p  = document.getElementById('newPwd').value;
  const c  = document.getElementById('confirmPwd').value;
  const ok = Object.keys(POLICY).every(k => POLICY[k](p));
  const match = p === c && c !== '';
  const mm    = document.getElementById('matchMsg');
  mm.textContent = c === '' ? '' : (match ? '✓ Passwords match' : '✗ Passwords do not match');
  mm.style.color = match ? '#22c55e' : '#ef4444';
  document.getElementById('savePwdBtn').disabled = !(ok && match);
}

async function saveNewPassword() {
  const p = document.getElementById('newPwd').value;
  const c = document.getElementById('confirmPwd').value;
  if (!Object.keys(POLICY).every(k => POLICY[k](p))) { showAlert('pwdAlert','Password does not meet all requirements.','error'); return; }
  if (p !== c) { showAlert('pwdAlert','Passwords do not match.','error'); return; }

  setBtn('savePwdBtn', true, 'Saving…');
  try {
    const res  = await fetch('API/change_password.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ user_id: currentUser.id, new_password: p })
    });
    const data = await res.json();
    if (data.success) {
      showAlert('pwdAlert','✓ Password changed! Redirecting…','success');
      setTimeout(() => {
        document.getElementById('pwdModal').classList.remove('active');
        routeUser(currentUser);
      }, 1400);
    } else {
      showAlert('pwdAlert', data.message || 'Update failed.', 'error');
      setBtn('savePwdBtn', false, 'Update Password');
    }
  } catch(e) {
    showAlert('pwdAlert','Connection error.','error');
    setBtn('savePwdBtn', false, 'Update Password');
  }
}

/* ═══════════════════════════════════════════
   FORGOT / RECOVERY
═══════════════════════════════════════════ */
function openForgot(e, fromSuspicious = false) {
  if (e) e.preventDefault();
  if (fromSuspicious) document.getElementById('suspiciousModal').classList.remove('active');
  document.getElementById('forgotModal').classList.add('active');
}

async function sendRecovery() {
  const val = document.getElementById('forgotInput').value.trim();
  if (!val) { showAlert('forgotAlert','Enter your username or email','error'); return; }
  setBtn('forgotBtn', true, 'Sending…');
  try {
    const res  = await fetch('API/send_otp.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ lookup: val, channel: 'recovery' })
    });
    const data = await res.json();
    if (data.success) {
      showAlert('forgotAlert', `Recovery code sent to ${data.destination}.`, 'success');
    } else {
      showAlert('forgotAlert', data.message || 'Account not found.', 'error');
    }
  } catch(e) {
    showAlert('forgotAlert','Error sending recovery. Try again.','error');
  } finally {
    setBtn('forgotBtn', false, 'Send Recovery Code');
  }
}

/* ── clear fields on load ── */
window.addEventListener('load', () => {
  document.getElementById('username').value = '';
  document.getElementById('password').value = '';
}, { once:true });
</script>
</body>
</html>