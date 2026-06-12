<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OTP Gateway</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/firebase/10.7.1/firebase-app-compat.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/firebase/10.7.1/firebase-auth-compat.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/firebase/10.7.1/firebase-firestore-compat.min.js"></script>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: system-ui, sans-serif; background: linear-gradient(135deg,#4f46e5,#7c3aed); min-height: 100vh; color: #fff; }
  .card  { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14); backdrop-filter: blur(14px); border-radius: 1rem; }
  .inp   { width: 100%; padding: .6rem .9rem; background: rgba(0,0,0,0.28); border: 1px solid rgba(255,255,255,0.15); border-radius: .5rem; color: #fff; outline: none; font-size: .9rem; }
  .inp:focus { border-color: #a78bfa; }
  .inp::placeholder { color: rgba(255,255,255,0.3); }
  .btn   { width: 100%; padding: .65rem 1rem; font-weight: 600; border-radius: .5rem; border: none; cursor: pointer; font-size: .9rem; transition: opacity .15s; }
  .btn:hover   { opacity: .85; }
  .btn:disabled { opacity: .45; cursor: not-allowed; }
  .btn-purple  { background: #7c3aed; color: #fff; }
  .btn-green   { background: #16a34a; color: #fff; }
  .btn-ghost   { background: rgba(255,255,255,0.1); color: #fff; }
  .spin { display: inline-block; width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin .75s linear infinite; vertical-align: middle; margin-left: 6px; }
  @keyframes spin { to { transform: rotate(360deg); } }
  .toast { position: fixed; bottom: 1.5rem; right: 1.5rem; padding: .75rem 1.1rem; border-radius: .75rem; font-size: .84rem; font-weight: 500; z-index: 999; opacity: 0; transform: translateY(.8rem); transition: all .28s; pointer-events: none; max-width: 300px; }
  .toast.show { opacity: 1; transform: translateY(0); }
  .log-row { display: flex; justify-content: space-between; font-size: .76rem; padding: .35rem .6rem; border-radius: .35rem; background: rgba(255,255,255,0.05); }
  .divider { border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: .75rem 0; }
  .badge { display: inline-block; font-size: .65rem; font-weight: 700; padding: .15rem .5rem; border-radius: 99px; letter-spacing: .05em; background: rgba(22,163,74,0.25); color: #4ade80; border: 1px solid rgba(74,222,128,0.3); }
</style>
</head>
<body>

<!-- TOAST -->
<div id="toast" class="toast card"></div>

<!-- NAV -->
<nav class="card" style="border-radius:0;border-left:none;border-right:none;border-top:none;padding:.8rem 1.5rem;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:40;">
  <span style="font-weight:700;">🔐 OTP Gateway</span>
  <div id="navUser" style="display:none;align-items:center;gap:.75rem;">
    <span id="navLabel" style="font-size:.78rem;opacity:.55;"></span>
    <button class="btn btn-ghost" style="width:auto;padding:.3rem .8rem;font-size:.78rem;" onclick="doLogout()">Logout</button>
  </div>
</nav>

<div style="max-width:520px;margin:0 auto;padding:2rem 1rem;">

  <!-- ── AUTH CARD ── -->
  <div id="authCard" class="card" style="padding:1.75rem;">
    <div style="display:flex;gap:.4rem;background:rgba(0,0,0,0.2);border-radius:.5rem;padding:.25rem;margin-bottom:1.5rem;">
      <button id="tabA" class="btn" style="background:rgba(255,255,255,0.16);color:#fff;font-size:.85rem;padding:.4rem;" onclick="tab('login')">Login</button>
      <button id="tabB" class="btn" style="background:transparent;color:rgba(255,255,255,.5);font-size:.85rem;padding:.4rem;" onclick="tab('register')">Register</button>
    </div>

    <!-- login -->
    <div id="paneLogin">
      <p style="font-weight:700;font-size:1.1rem;margin-bottom:1rem;">Welcome back</p>
      <div style="display:flex;flex-direction:column;gap:.75rem;">
        <input class="inp" id="lEmail" type="email"    placeholder="Email address">
        <input class="inp" id="lPass"  type="password" placeholder="Password">
        <button class="btn btn-purple" id="lBtn" onclick="doLogin()">Login</button>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:1rem;">
        <button onclick="magicLinkPrompt()" style="background:none;border:none;color:rgba(255,255,255,.5);font-size:.78rem;cursor:pointer;">📧 Magic link login</button>
        <button onclick="tab('register')"   style="background:none;border:none;color:rgba(255,255,255,.5);font-size:.78rem;cursor:pointer;">No account? Register →</button>
      </div>
    </div>

    <!-- register -->
    <div id="paneRegister" style="display:none;">
      <p style="font-weight:700;font-size:1.1rem;margin-bottom:1rem;">Create account</p>
      <div style="display:flex;flex-direction:column;gap:.75rem;">
        <input class="inp" id="rName"  type="text"     placeholder="Display name">
        <input class="inp" id="rEmail" type="email"    placeholder="Email address">
        <input class="inp" id="rPass"  type="password" placeholder="Password (min 6 chars)">
        <button class="btn btn-purple" id="rBtn" onclick="doRegister()">Create Account</button>
      </div>
    </div>
  </div>

  <!-- ── DASHBOARD ── -->
  <div id="dashboard" style="display:none;">

    <!-- SMS OTP card -->
    <div class="card" style="padding:1.5rem;margin-bottom:1rem;">
      <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.25rem;">
        <span style="font-size:1.5rem;">📱</span>
        <div>
          <div style="display:flex;align-items:center;gap:.5rem;">
            <span style="font-weight:700;">SMS OTP</span>
            <span class="badge">FREE</span>
          </div>
          <p style="font-size:.73rem;opacity:.4;margin-top:.1rem;">Firebase Phone Auth · no Twilio · no credits needed</p>
        </div>
      </div>

      <!-- step 1: enter phone -->
      <div id="stepPhone">
        <label style="font-size:.78rem;opacity:.5;display:block;margin-bottom:.4rem;">Phone number with country code</label>
        <input class="inp" id="smsPhone" type="tel" placeholder="+63 912 345 6789" style="margin-bottom:.75rem;">
        <div id="recaptcha-container" style="display:flex;justify-content:center;margin-bottom:.75rem;"></div>
        <button class="btn btn-green" id="smsBtn" onclick="sendSMS()">Send SMS OTP</button>
      </div>

      <!-- step 2: enter code -->
      <div id="stepVerify" style="display:none;">
        <hr class="divider">
        <p style="font-size:.78rem;color:#4ade80;margin-bottom:.5rem;">✅ Code sent! Check your phone.</p>
        <input class="inp" id="smsCode" type="text" maxlength="6" placeholder="_ _ _ _ _ _"
               style="text-align:center;font-size:1.4rem;letter-spacing:.5em;margin-bottom:.75rem;">
        <div style="display:flex;gap:.5rem;">
          <button class="btn btn-green" onclick="verifySMS()">Verify Code</button>
          <button class="btn btn-ghost" style="width:auto;padding:.65rem 1rem;" onclick="resetSMS()">↩ Resend</button>
        </div>
      </div>

      <!-- step 3: success -->
      <div id="stepDone" style="display:none;text-align:center;padding:1rem 0;">
        <div style="font-size:2.5rem;">✅</div>
        <p style="font-weight:700;margin-top:.5rem;">Phone Verified!</p>
        <p id="verifiedPhone" style="font-size:.8rem;opacity:.45;margin-top:.2rem;"></p>
        <button class="btn btn-ghost" style="margin-top:.75rem;" onclick="resetSMS()">Verify another number</button>
      </div>
    </div>

    <!-- Email Magic Link card -->
    <div class="card" style="padding:1.5rem;margin-bottom:1rem;">
      <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.25rem;">
        <span style="font-size:1.5rem;">📧</span>
        <div>
          <div style="display:flex;align-items:center;gap:.5rem;">
            <span style="font-weight:700;">Email Magic Link</span>
            <span class="badge">FREE</span>
          </div>
          <p style="font-size:.73rem;opacity:.4;margin-top:.1rem;">Firebase sends a one-click login link to any inbox</p>
        </div>
      </div>
      <label style="font-size:.78rem;opacity:.5;display:block;margin-bottom:.4rem;">Recipient email</label>
      <input class="inp" id="mlEmail" type="email" placeholder="user@example.com" style="margin-bottom:.75rem;">
      <button class="btn btn-purple" id="mlBtn" onclick="sendMagicLink()">Send Magic Link</button>
      <p style="font-size:.72rem;opacity:.35;margin-top:.6rem;text-align:center;">They click the link → instantly logged in. Zero password.</p>
    </div>

    <!-- Activity log -->
    <div class="card" style="padding:1.25rem;">
      <p style="font-weight:600;font-size:.85rem;margin-bottom:.75rem;">📋 Activity Log</p>
      <div id="logList" style="display:flex;flex-direction:column;gap:.4rem;max-height:160px;overflow-y:auto;">
        <p style="font-size:.75rem;opacity:.3;font-style:italic;">No activity yet.</p>
      </div>
    </div>

  </div><!-- /dashboard -->
</div><!-- /main -->

<script>
// ── FIREBASE ─────────────────────────────────────────────────────────
firebase.initializeApp({
  apiKey:            "AIzaSyC5UXf46-Q_F3TfuGmaaL_DTXL4C6wQwPo",
  authDomain:        "firm-otp-gateway.firebaseapp.com",
  projectId:         "firm-otp-gateway",
  storageBucket:     "firm-otp-gateway.firebasestorage.app",
  messagingSenderId: "219203112830",
  appId:             "1:219203112830:web:bd2fcb865becc3baaa2d2f"
});
const auth = firebase.auth();
const db   = firebase.firestore();

// ── TOAST ─────────────────────────────────────────────────────────────
function toast(msg, type='success') {
  const el = document.getElementById('toast');
  const bg = { success:'rgba(22,163,74,.92)', error:'rgba(220,38,38,.92)', info:'rgba(37,99,235,.92)' };
  el.textContent = msg;
  el.style.background = bg[type] || bg.success;
  el.classList.add('show');
  clearTimeout(el._t);
  el._t = setTimeout(() => el.classList.remove('show'), 3500);
}

// ── ACTIVITY LOG ──────────────────────────────────────────────────────
function log(msg, type='ok') {
  const list  = document.getElementById('logList');
  if (list.querySelector('p')) list.innerHTML = '';
  const color = { ok:'#4ade80', err:'#f87171', info:'#60a5fa' }[type] || '#4ade80';
  const row   = document.createElement('div');
  row.className = 'log-row';
  row.innerHTML = `<span style="color:${color}">● ${msg}</span><span style="opacity:.35">${new Date().toLocaleTimeString()}</span>`;
  list.prepend(row);
}

// ── TABS ──────────────────────────────────────────────────────────────
function tab(t) {
  const l = t === 'login';
  document.getElementById('paneLogin').style.display    = l ? '' : 'none';
  document.getElementById('paneRegister').style.display = l ? 'none' : '';
  document.getElementById('tabA').style.cssText = `background:${l?'rgba(255,255,255,.16)':'transparent'};color:${l?'#fff':'rgba(255,255,255,.5)'};font-size:.85rem;padding:.4rem;`;
  document.getElementById('tabB').style.cssText = `background:${!l?'rgba(255,255,255,.16)':'transparent'};color:${!l?'#fff':'rgba(255,255,255,.5)'};font-size:.85rem;padding:.4rem;`;
}

// ── BUTTON LOADING ────────────────────────────────────────────────────
function setBtn(id, loading, label) {
  const b = document.getElementById(id);
  b.disabled  = loading;
  b.innerHTML = loading ? `${label}<span class="spin"></span>` : label;
}

// ── REGISTER ──────────────────────────────────────────────────────────
async function doRegister() {
  const name  = document.getElementById('rName').value.trim();
  const email = document.getElementById('rEmail').value.trim();
  const pass  = document.getElementById('rPass').value;
  if (!name || !email || !pass) return toast('Fill all fields', 'error');
  setBtn('rBtn', true, 'Creating…');
  try {
    const c = await auth.createUserWithEmailAndPassword(email, pass);
    await c.user.updateProfile({ displayName: name });
    await db.collection('users').doc(c.user.uid).set({ name, email, createdAt: firebase.firestore.FieldValue.serverTimestamp() });
    toast('Account created ✅', 'success');
    log('Registered: ' + email);
    tab('login');
  } catch(e) { toast(e.message, 'error'); log(e.message, 'err'); }
  finally { setBtn('rBtn', false, 'Create Account'); }
}

// ── LOGIN ─────────────────────────────────────────────────────────────
async function doLogin() {
  const email = document.getElementById('lEmail').value.trim();
  const pass  = document.getElementById('lPass').value;
  if (!email || !pass) return toast('Enter email and password', 'error');
  setBtn('lBtn', true, 'Logging in…');
  try {
    await auth.signInWithEmailAndPassword(email, pass);
    log('Login: ' + email);
  } catch(e) { toast(e.message, 'error'); log(e.message, 'err'); }
  finally { setBtn('lBtn', false, 'Login'); }
}

function doLogout() { auth.signOut(); log('Logged out', 'info'); }

// ── AUTH STATE ────────────────────────────────────────────────────────
auth.onAuthStateChanged(user => {
  const in_ = !!user;
  document.getElementById('authCard').style.display  = in_ ? 'none' : '';
  document.getElementById('dashboard').style.display = in_ ? '' : 'none';
  document.getElementById('navUser').style.display   = in_ ? 'flex' : 'none';
  if (user) {
    document.getElementById('navLabel').textContent = user.displayName || user.email;
    setTimeout(initRecaptcha, 300);
    log('Session: ' + (user.displayName || user.email), 'info');
  }
});

// ── MAGIC LINK (login page shortcut) ──────────────────────────────────
function magicLinkPrompt() {
  const email = prompt('Enter your email for a magic login link:');
  if (!email) return;
  auth.sendSignInLinkToEmail(email, { url: location.href, handleCodeInApp: true })
    .then(() => { localStorage.setItem('mlPending', email); toast('Magic link sent to ' + email, 'success'); log('Magic link → ' + email, 'info'); })
    .catch(e => toast(e.message, 'error'));
}

// ── MAGIC LINK (dashboard) ────────────────────────────────────────────
async function sendMagicLink() {
  const email = document.getElementById('mlEmail').value.trim();
  if (!email) return toast('Enter an email address', 'error');
  setBtn('mlBtn', true, 'Sending…');
  try {
    await auth.sendSignInLinkToEmail(email, { url: location.href, handleCodeInApp: true });
    localStorage.setItem('mlPending', email);
    toast('Magic link sent to ' + email + ' ✅', 'success');
    log('Magic link → ' + email, 'ok');
    document.getElementById('mlEmail').value = '';
  } catch(e) { toast(e.message, 'error'); log(e.message, 'err'); }
  finally { setBtn('mlBtn', false, 'Send Magic Link'); }
}

// Handle magic link redirect when user clicks link in email
if (auth.isSignInWithEmailLink(location.href)) {
  const email = localStorage.getItem('mlPending') || prompt('Confirm your email:');
  auth.signInWithEmailLink(email, location.href)
    .then(() => { localStorage.removeItem('mlPending'); toast('Logged in via magic link ✅', 'success'); history.replaceState(null, '', location.pathname); })
    .catch(e => toast(e.message, 'error'));
}

// ── SMS OTP — Firebase Phone Auth (free, no Twilio) ───────────────────
let recaptchaVerifier, confirmResult;

function initRecaptcha() {
  if (recaptchaVerifier) return;
  recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
    size: 'normal',
    callback: () => {} // reCAPTCHA solved — user can tap Send
  });
  recaptchaVerifier.render();
}

async function sendSMS() {
  const phone = document.getElementById('smsPhone').value.trim();
  if (!phone) return toast('Enter a phone number with country code', 'error');
  setBtn('smsBtn', true, 'Sending…');
  try {
    confirmResult = await auth.signInWithPhoneNumber(phone, recaptchaVerifier);
    document.getElementById('stepPhone').style.display  = 'none';
    document.getElementById('stepVerify').style.display = '';
    toast('OTP sent to ' + phone, 'success');
    log('SMS OTP → ' + phone, 'ok');
  } catch(e) {
    toast(e.message, 'error');
    log('SMS error: ' + e.message, 'err');
    recaptchaVerifier.clear(); recaptchaVerifier = null;
    setTimeout(initRecaptcha, 300);
  } finally { setBtn('smsBtn', false, 'Send SMS OTP'); }
}

async function verifySMS() {
  const code  = document.getElementById('smsCode').value.trim();
  const phone = document.getElementById('smsPhone').value.trim();
  if (!code) return toast('Enter the 6-digit code', 'error');
  try {
    await confirmResult.confirm(code);
    document.getElementById('stepVerify').style.display = 'none';
    document.getElementById('stepDone').style.display   = '';
    document.getElementById('verifiedPhone').textContent = phone + ' is verified';
    toast('Phone verified ✅', 'success');
    log('SMS verified: ' + phone, 'ok');
    db.collection('smsLogs').add({ phone, verified: true, uid: auth.currentUser?.uid || null, ts: firebase.firestore.FieldValue.serverTimestamp() });
  } catch(e) {
    toast('Wrong code — please try again', 'error');
    log('SMS verify failed', 'err');
  }
}

function resetSMS() {
  ['stepPhone','stepVerify','stepDone'].forEach((id,i) => document.getElementById(id).style.display = i===0?'':'none');
  document.getElementById('smsPhone').value = '';
  document.getElementById('smsCode').value  = '';
  if (recaptchaVerifier) { recaptchaVerifier.clear(); recaptchaVerifier = null; }
  setTimeout(initRecaptcha, 300);
}
</script>
</body>
</html>