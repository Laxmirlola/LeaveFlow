<?php
require_once 'config/auth.php';
startSession();
// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LeaveFlow — Sign In</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --ink: #0f1117;
      --paper: #faf9f7;
      --accent: #2563eb;
      --accent-light: #eff6ff;
      --muted: #6b7280;
      --border: #e5e7eb;
      --red: #ef4444;
    }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--paper);
      min-height: 100vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
    }
    /* LEFT PANEL */
    .left-panel {
      background: var(--ink);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 48px;
      position: relative;
      overflow: hidden;
    }
    .left-panel::before {
      content: '';
      position: absolute;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(37,99,235,0.3) 0%, transparent 70%);
      top: -100px; left: -100px;
      pointer-events: none;
    }
    .left-panel::after {
      content: '';
      position: absolute;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, transparent 70%);
      bottom: -50px; right: -50px;
      pointer-events: none;
    }
    .brand {
      font-family: 'DM Serif Display', serif;
      font-size: 28px;
      color: #fff;
      letter-spacing: -0.5px;
      position: relative;
      z-index: 1;
    }
    .brand span { color: #60a5fa; }
    .left-hero { position: relative; z-index: 1; }
    .left-hero h1 {
      font-family: 'DM Serif Display', serif;
      font-size: 52px;
      color: #fff;
      line-height: 1.1;
      margin-bottom: 20px;
    }
    .left-hero h1 em { color: #93c5fd; font-style: italic; }
    .left-hero p { color: #94a3b8; font-size: 16px; line-height: 1.7; max-width: 380px; }
    .left-features { position: relative; z-index: 1; display: flex; gap: 32px; }
    .feat { color: #cbd5e1; font-size: 13px; display: flex; align-items: center; gap: 8px; }
    .feat-dot { width: 8px; height: 8px; border-radius: 50%; background: #60a5fa; flex-shrink: 0; }
    /* RIGHT PANEL */
    .right-panel {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 48px;
    }
    .login-box { width: 100%; max-width: 400px; }
    .login-box h2 {
      font-family: 'DM Serif Display', serif;
      font-size: 32px;
      color: var(--ink);
      margin-bottom: 8px;
    }
    .login-box p { color: var(--muted); margin-bottom: 36px; font-size: 15px; }

    .demo-hint {
      background: var(--accent-light);
      border: 1px solid #bfdbfe;
      border-radius: 12px;
      padding: 14px 16px;
      margin-bottom: 28px;
      font-size: 13px;
      color: #1d4ed8;
      line-height: 1.6;
    }
    .demo-hint strong { display: block; margin-bottom: 4px; font-size: 13px; }
    .demo-hint .demo-accounts { display: flex; flex-direction: column; gap: 2px; }
    .demo-fill {
      cursor: pointer;
      text-decoration: underline;
      color: var(--accent);
      font-weight: 500;
    }
    .field { margin-bottom: 20px; }
    .field label { display: block; font-size: 13px; font-weight: 500; color: var(--ink); margin-bottom: 6px; }
    .field input {
      width: 100%;
      padding: 12px 16px;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-size: 15px;
      color: var(--ink);
      background: #fff;
      outline: none;
      transition: border-color 0.2s;
    }
    .field input:focus { border-color: var(--accent); }
    .error-msg {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: var(--red);
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 13px;
      margin-bottom: 16px;
      display: none;
    }
    .btn-login {
      width: 100%;
      padding: 14px;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity 0.2s, transform 0.1s;
      margin-top: 4px;
    }
    .btn-login:hover { opacity: 0.88; }
    .btn-login:active { transform: scale(0.98); }
    .btn-login:disabled { opacity: 0.6; cursor: not-allowed; }

    @media (max-width: 768px) {
      body { grid-template-columns: 1fr; }
      .left-panel { display: none; }
      .right-panel { padding: 32px 24px; }
    }
  </style>
</head>
<body>
  <!-- LEFT -->
  <div class="left-panel">
    <div class="brand">Leave<span>Flow</span></div>
    <div class="left-hero">
      <h1>Manage leave <em>effortlessly</em></h1>
      <p>A streamlined system for requesting, approving, and tracking employee leave — built for teams that value transparency.</p>
    </div>
    <div></div>
  </div>

  <!-- RIGHT -->
  <div class="right-panel">
    <div class="login-box">
      <h2>Welcome back</h2>
      <p>Sign in to your account to continue</p>

      <div class="demo-hint">
        <strong>Demo Accounts — Click an account name to auto-fill credentials</strong>
        <div class="demo-accounts">
          <span class="demo-fill" onclick="fillDemo('manager@company.com')">Sarah Johnson &nbsp;·&nbsp; Manager</span>
          <span class="demo-fill" onclick="fillDemo('alex@company.com')">Alex Thompson &nbsp;·&nbsp; Employee</span>
          <span class="demo-fill" onclick="fillDemo('priya@company.com')">Priya Patel &nbsp;·&nbsp; Employee</span>
        </div>
      </div>

      <div class="error-msg" id="error-msg"></div>

      <div class="field">
        <label for="email">Email address</label>
        <input type="email" id="email" placeholder="you@company.com" autocomplete="email"/>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" placeholder="••••••••" autocomplete="current-password"/>
      </div>
      <button class="btn-login" id="login-btn" onclick="doLogin()">Sign In</button>
    </div>
  </div>

<script>
  function fillDemo(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password123';
  }

  async function doLogin() {
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const errEl    = document.getElementById('error-msg');
    const btn      = document.getElementById('login-btn');

    errEl.style.display = 'none';

    if (!email || !password) {
      errEl.textContent = 'Please enter your email and password.';
      errEl.style.display = 'block';
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Signing in…';

    try {
      const res  = await fetch('api/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();

      if (data.success) {
        window.location.href = 'dashboard.php';
      } else {
        errEl.textContent = data.error || 'Login failed. Please try again.';
        errEl.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Sign In';
      }
    } catch (e) {
      errEl.textContent = 'Network error. Please try again.';
      errEl.style.display = 'block';
      btn.disabled = false;
      btn.textContent = 'Sign In';
    }
  }

  // Allow Enter key
  document.addEventListener('keydown', e => {
    if (e.key === 'Enter') doLogin();
  });
</script>
</body>
</html>
