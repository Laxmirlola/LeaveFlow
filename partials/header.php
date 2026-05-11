<?php
require_once __DIR__ . '/../config/auth.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle ?? 'LeaveFlow') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --ink: #0f1117;
      --paper: #f4f6fb;
      --white: #ffffff;
      --accent: #2563eb;
      --accent-light: #eff6ff;
      --green: #059669;
      --green-bg: #ecfdf5;
      --red: #dc2626;
      --red-bg: #fef2f2;
      --amber: #d97706;
      --amber-bg: #fffbeb;
      --muted: #6b7280;
      --border: #e5e7eb;
      --sidebar-w: 240px;
      --nav-h: 64px;
    }
    body { font-family: 'DM Sans', sans-serif; background: var(--paper); color: var(--ink); min-height: 100vh; }

    /* TOPBAR */
    .topbar {
      position: fixed; top: 0; left: 0; right: 0; height: var(--nav-h);
      background: var(--white);
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 24px 0 calc(var(--sidebar-w) + 24px);
      z-index: 100;
    }
    .topbar-title { font-family: 'DM Serif Display', serif; font-size: 20px; }
    .topbar-user { display: flex; align-items: center; gap: 12px; }
    .topbar-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: var(--accent-light);
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }
    .topbar-info { line-height: 1.3; }
    .topbar-name { font-size: 14px; font-weight: 600; }
    .topbar-role { font-size: 12px; color: var(--muted); text-transform: capitalize; }
    .logout-btn {
      padding: 7px 14px; border: 1.5px solid var(--border);
      border-radius: 8px; background: none;
      font-family: 'DM Sans', sans-serif; font-size: 13px;
      color: var(--muted); cursor: pointer; transition: all 0.2s;
    }
    .logout-btn:hover { border-color: var(--red); color: var(--red); }

    /* SIDEBAR */
    .sidebar {
      position: fixed; top: 0; left: 0; bottom: 0;
      width: var(--sidebar-w);
      background: var(--ink);
      padding: 0 0 24px;
      z-index: 200;
      display: flex; flex-direction: column;
    }
    .sidebar-brand {
      padding: 20px 24px;
      font-family: 'DM Serif Display', serif;
      font-size: 22px; color: #fff;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .sidebar-brand span { color: #60a5fa; }
    .sidebar-nav { flex: 1; padding: 16px 12px; display: flex; flex-direction: column; gap: 2px; }
    .nav-item {
      display: flex; align-items: center; gap: 12px;
      padding: 11px 14px;
      border-radius: 10px;
      color: #94a3b8;
      text-decoration: none;
      font-size: 14px; font-weight: 500;
      transition: background 0.15s, color 0.15s;
    }
    .nav-item:hover { background: rgba(255,255,255,0.06); color: #fff; }
    .nav-item.active { background: var(--accent); color: #fff; }
    .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
    .nav-section {
      font-size: 10px; font-weight: 600; text-transform: uppercase;
      letter-spacing: 1px; color: #475569;
      padding: 16px 14px 6px;
    }

    /* MAIN */
    .main {
      margin-top: var(--nav-h);
      margin-left: var(--sidebar-w);
      padding: 28px 32px;
      min-height: calc(100vh - var(--nav-h));
    }

    /* CARDS */
    .card {
      background: var(--white);
      border-radius: 14px;
      padding: 24px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .card-title { font-family: 'DM Serif Display', serif; font-size: 20px; margin-bottom: 20px; }

    /* STATUS BADGES */
    .badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 10px; border-radius: 50px;
      font-size: 12px; font-weight: 600;
    }
    .badge-pending  { background: var(--amber-bg); color: var(--amber); }
    .badge-approved { background: var(--green-bg);  color: var(--green); }
    .badge-rejected { background: var(--red-bg);    color: var(--red); }

    /* BUTTONS */
    .btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 10px 18px; border-radius: 8px; border: none;
      font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 600;
      cursor: pointer; transition: opacity 0.2s;
    }
    .btn:hover { opacity: 0.85; }
    .btn-primary { background: var(--accent); color: #fff; }
    .btn-success { background: var(--green); color: #fff; }
    .btn-danger  { background: var(--red); color: #fff; }
    .btn-ghost   { background: var(--paper); color: var(--ink); border: 1.5px solid var(--border); }

    /* TOAST */
    #toast {
      position: fixed; bottom: 24px; right: 24px;
      padding: 14px 20px; border-radius: 12px;
      background: var(--ink); color: #fff;
      font-size: 14px; font-weight: 500;
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
      transform: translateY(80px); opacity: 0;
      transition: all 0.3s ease;
      z-index: 9999;
    }
    #toast.show { transform: translateY(0); opacity: 1; }
    #toast.toast-success { background: var(--green); }
    #toast.toast-error   { background: var(--red); }

    /* MODAL */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);
      z-index: 500; align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: var(--white); border-radius: 16px;
      padding: 32px; width: 100%; max-width: 480px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.2);
      animation: modalIn 0.2s ease;
    }
    @keyframes modalIn { from { transform: scale(0.95); opacity: 0; } }
    .modal h3 { font-family: 'DM Serif Display', serif; font-size: 22px; margin-bottom: 20px; }
    .form-field { margin-bottom: 18px; }
    .form-field label { display: block; font-size: 13px; font-weight: 500; color: var(--ink); margin-bottom: 6px; }
    .form-field input,
    .form-field select,
    .form-field textarea {
      width: 100%; padding: 11px 14px;
      border: 1.5px solid var(--border); border-radius: 8px;
      font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--ink);
      outline: none; background: #fff; transition: border-color 0.2s;
    }
    .form-field input:focus,
    .form-field select:focus,
    .form-field textarea:focus { border-color: var(--accent); }
    .form-field textarea { resize: vertical; min-height: 80px; }
    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }

    /* TABLE */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th {
      text-align: left; font-size: 12px; font-weight: 600;
      text-transform: uppercase; letter-spacing: 0.5px;
      color: var(--muted); padding: 10px 14px;
      border-bottom: 1px solid var(--border);
    }
    .data-table td { padding: 14px; border-bottom: 1px solid var(--border); font-size: 14px; vertical-align: middle; }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tr:hover td { background: var(--paper); }

    /* EMPTY STATE */
    .empty-state {
      text-align: center; padding: 48px 24px;
      color: var(--muted);
    }
    .empty-state .empty-icon { font-size: 48px; margin-bottom: 12px; }
    .empty-state p { font-size: 15px; }

    /* GRID */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }

    @media (max-width: 1024px) {
      .grid-4 { grid-template-columns: repeat(2,1fr); }
      .grid-3 { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>
<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">Leave<span>Flow</span></div>
  <nav class="sidebar-nav">
    <div class="nav-section">Main</div>
    <a href="dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>
    <a href="my-requests.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'my-requests.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      My Requests
    </a>
    <a href="calendar.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'calendar.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Leave Calendar
    </a>
    <?php if ($user['role'] === 'manager'): ?>
    <div class="nav-section">Manager</div>
    <a href="approvals.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'approvals.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      Approvals
    </a>
    <?php endif; ?>
  </nav>
</aside>

<!-- TOPBAR -->
<header class="topbar">
  <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
  <div class="topbar-user">
    <div class="topbar-avatar"><?= $user['avatar'] ?? '🧑' ?></div>
    <div class="topbar-info">
      <div class="topbar-name"><?= htmlspecialchars($user['name']) ?></div>
      <div class="topbar-role"><?= htmlspecialchars($user['role']) ?></div>
    </div>
    <a href="api/logout.php" class="logout-btn">Sign out</a>
  </div>
</header>

<!-- TOAST -->
<div id="toast"></div>

<script>
function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show toast-' + type;
  setTimeout(() => { t.className = ''; }, 3200);
}
</script>
