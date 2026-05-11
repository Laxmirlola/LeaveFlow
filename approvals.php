<?php
$pageTitle = 'Approvals';
require_once 'partials/header.php';
if ($user['role'] !== 'manager') {
    header('Location: dashboard.php');
    exit;
}
?>
<main class="main">

  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:24px">
    <div class="card" style="text-align:center">
      <div style="font-size:32px;font-weight:700;font-family:'DM Serif Display',serif;color:var(--amber)" id="count-pending">—</div>
      <div style="font-size:13px;color:var(--muted);margin-top:4px">Pending Approval</div>
    </div>
    <div class="card" style="text-align:center">
      <div style="font-size:32px;font-weight:700;font-family:'DM Serif Display',serif;color:var(--green)" id="count-approved">—</div>
      <div style="font-size:13px;color:var(--muted);margin-top:4px">Approved This Month</div>
    </div>
    <div class="card" style="text-align:center">
      <div style="font-size:32px;font-weight:700;font-family:'DM Serif Display',serif;color:var(--red)" id="count-rejected">—</div>
      <div style="font-size:13px;color:var(--muted);margin-top:4px">Rejected This Month</div>
    </div>
  </div>

  <div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
      <div class="card-title" style="margin:0">All Leave Requests</div>
      <div style="display:flex;gap:8px">
        <button class="filter-btn active" onclick="setFilter('all',this)">All</button>
        <button class="filter-btn" onclick="setFilter('pending',this)">Pending</button>
        <button class="filter-btn" onclick="setFilter('approved',this)">Approved</button>
        <button class="filter-btn" onclick="setFilter('rejected',this)">Rejected</button>
      </div>
    </div>

    <div style="overflow-x:auto">
      <table class="data-table">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>Dates</th>
            <th>Days</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="requests-body">
          <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- REVIEW MODAL -->
<div class="modal-overlay" id="review-modal">
  <div class="modal">
    <h3 id="review-title">Review Leave Request</h3>
    <div id="review-info" style="background:var(--paper);border-radius:10px;padding:16px;margin-bottom:20px;font-size:14px;line-height:1.8"></div>
    <div class="form-field">
      <label>Manager Comment <span style="color:var(--muted)">(optional)</span></label>
      <textarea id="review-comment" rows="3" placeholder="Add a comment for the employee…"></textarea>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeReview()">Cancel</button>
      <button class="btn btn-danger"  onclick="submitReview('rejected')">✕ Reject</button>
      <button class="btn btn-success" onclick="submitReview('approved')">✓ Approve</button>
    </div>
  </div>
</div>

<style>
.filter-btn {
  padding: 7px 16px; border-radius: 50px; border: 1.5px solid var(--border);
  background: none; font-family: 'DM Sans', sans-serif; font-size: 13px;
  font-weight: 500; cursor: pointer; color: var(--muted); transition: all 0.15s;
}
.filter-btn:hover { border-color: var(--accent); color: var(--accent); }
.filter-btn.active { background: var(--accent); border-color: var(--accent); color: #fff; }
</style>

<script>
let allRequests   = [];
let currentFilter = 'all';
let reviewingId   = null;

async function loadRequests() {
  const res  = await fetch('api/requests.php');
  const data = await res.json();
  allRequests = data.requests || [];

  const now   = new Date();
  const month = now.getMonth();
  const year  = now.getFullYear();

  document.getElementById('count-pending').textContent =
    allRequests.filter(r => r.status === 'pending').length;
  document.getElementById('count-approved').textContent =
    allRequests.filter(r => {
      if (r.status !== 'approved') return false;
      const d = new Date(r.reviewed_at);
      return d.getMonth() === month && d.getFullYear() === year;
    }).length;
  document.getElementById('count-rejected').textContent =
    allRequests.filter(r => {
      if (r.status !== 'rejected') return false;
      const d = new Date(r.reviewed_at);
      return d.getMonth() === month && d.getFullYear() === year;
    }).length;

  renderTable();
}

function renderTable() {
  const tbody = document.getElementById('requests-body');
  const list  = currentFilter === 'all' ? allRequests : allRequests.filter(r => r.status === currentFilter);

  if (!list.length) {
    tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📋</div><p>No requests found</p></div></td></tr>`;
    return;
  }

  tbody.innerHTML = list.map(r => `
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:10px">
          <div style="font-size:22px">${r.avatar}</div>
          <div>
            <div style="font-weight:600;font-size:14px">${r.employee_name}</div>
            <div style="font-size:12px;color:var(--muted)">${r.department}</div>
          </div>
        </div>
      </td>
      <td>
        <span style="display:flex;align-items:center;gap:7px">
          <span style="width:9px;height:9px;border-radius:50%;background:${r.color};display:inline-block"></span>
          ${r.leave_type}
        </span>
      </td>
      <td style="font-size:13px">${r.start_date}<br><span style="color:var(--muted)">to ${r.end_date}</span></td>
      <td><strong>${r.total_days}</strong></td>
      <td style="max-width:160px;font-size:13px;color:var(--muted)">${r.reason || '—'}</td>
      <td><span class="badge badge-${r.status}">${r.status}</span></td>
      <td style="font-size:13px;color:var(--muted)">${r.created_at.split(' ')[0]}</td>
      <td>
        ${r.status === 'pending'
          ? `<button class="btn btn-primary" style="padding:7px 14px;font-size:13px" onclick='openReview(${JSON.stringify(r)})'>Review</button>`
          : `<span style="font-size:12px;color:var(--muted)">${r.reviewed_by_name ? 'By ' + r.reviewed_by_name : '—'}</span>`
        }
      </td>
    </tr>`).join('');
}

function setFilter(status, btn) {
  currentFilter = status;
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderTable();
}

function openReview(r) {
  reviewingId = r.id;
  document.getElementById('review-title').textContent = `Review: ${r.employee_name}'s Request`;
  document.getElementById('review-info').innerHTML = `
    <strong>${r.leave_type}</strong><br>
    📅 ${r.start_date} → ${r.end_date} (${r.total_days} day(s))<br>
    📝 ${r.reason || 'No reason provided'}
  `;
  document.getElementById('review-comment').value = '';
  document.getElementById('review-modal').classList.add('open');
}

function closeReview() {
  document.getElementById('review-modal').classList.remove('open');
  reviewingId = null;
}

async function submitReview(action) {
  const comment = document.getElementById('review-comment').value;
  const res = await fetch('api/requests.php', {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: reviewingId, action, comment })
  });
  const data = await res.json();
  if (data.success) {
    closeReview();
    showToast(`Request ${action} successfully`, action === 'approved' ? 'success' : 'error');
    loadRequests();
  } else {
    showToast(data.error || 'Action failed', 'error');
  }
}

loadRequests();
</script>
