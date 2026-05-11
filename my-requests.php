<?php
$pageTitle = 'My Requests';
require_once 'partials/header.php';
?>
<main class="main">
  <div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
      <div class="card-title" style="margin:0">My Leave Requests</div>
      <button class="btn btn-primary" onclick="openModal()">+ New Request</button>
    </div>

    <!-- Filter bar -->
    <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
      <button class="filter-btn active" data-status="all" onclick="setFilter('all',this)">All</button>
      <button class="filter-btn" data-status="pending"  onclick="setFilter('pending',this)">Pending</button>
      <button class="filter-btn" data-status="approved" onclick="setFilter('approved',this)">Approved</button>
      <button class="filter-btn" data-status="rejected" onclick="setFilter('rejected',this)">Rejected</button>
    </div>

    <div style="overflow-x:auto">
      <table class="data-table" id="requests-table">
        <thead>
          <tr>
            <th>Leave Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Days</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Manager Note</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody id="requests-body">
          <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- NEW REQUEST MODAL -->
<div class="modal-overlay" id="modal">
  <div class="modal">
    <h3>New Leave Request</h3>
    <div class="form-field">
      <label>Leave Type *</label>
      <select id="m-type"><option value="">Select…</option></select>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div class="form-field">
        <label>Start Date *</label>
        <input type="date" id="m-start"/>
      </div>
      <div class="form-field">
        <label>End Date *</label>
        <input type="date" id="m-end"/>
      </div>
    </div>
    <div class="form-field">
      <label>Reason</label>
      <textarea id="m-reason" rows="3" placeholder="Brief reason for leave…"></textarea>
    </div>
    <div id="m-error" style="color:var(--red);font-size:13px;margin-top:-8px;margin-bottom:8px;display:none"></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="submitRequest()">Submit Request</button>
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
let allRequests = [];
let currentFilter = 'all';

const today = new Date().toISOString().split('T')[0];
document.getElementById('m-start').min = today;
document.getElementById('m-end').min   = today;
document.getElementById('m-start').addEventListener('change', function() {
  document.getElementById('m-end').min = this.value;
});

function openModal() {
  document.getElementById('modal').classList.add('open');
  document.getElementById('m-error').style.display = 'none';
}
function closeModal() {
  document.getElementById('modal').classList.remove('open');
}

async function loadTypes() {
  const res  = await fetch('api/leave_types.php');
  const data = await res.json();
  const sel  = document.getElementById('m-type');
  data.types.forEach(t => {
    const o = document.createElement('option');
    o.value = t.id;
    o.textContent = t.name;
    sel.appendChild(o);
  });
}

async function loadRequests() {
  const res  = await fetch('api/requests.php');
  const data = await res.json();
  allRequests = data.requests || [];
  renderTable();
}

function renderTable() {
  const tbody = document.getElementById('requests-body');
  const list  = currentFilter === 'all' ? allRequests : allRequests.filter(r => r.status === currentFilter);

  if (!list.length) {
    tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📋</div><p>No ${currentFilter === 'all' ? '' : currentFilter + ' '}requests found</p></div></td></tr>`;
    return;
  }

  tbody.innerHTML = list.map(r => `
    <tr>
      <td>
        <span style="display:flex;align-items:center;gap:8px">
          <span style="width:10px;height:10px;border-radius:50%;background:${r.color};flex-shrink:0;display:inline-block"></span>
          ${r.leave_type}
        </span>
      </td>
      <td>${r.start_date}</td>
      <td>${r.end_date}</td>
      <td><strong>${r.total_days}</strong></td>
      <td style="max-width:160px;color:var(--muted);font-size:13px">${r.reason || '—'}</td>
      <td><span class="badge badge-${r.status}">${r.status}</span></td>
      <td style="max-width:160px;font-size:13px;color:var(--muted)">${r.manager_comment || '—'}</td>
      <td style="font-size:13px;color:var(--muted)">${r.created_at.split(' ')[0]}</td>
    </tr>`).join('');
}

function setFilter(status, btn) {
  currentFilter = status;
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderTable();
}

async function submitRequest() {
  const errEl = document.getElementById('m-error');
  errEl.style.display = 'none';

  const payload = {
    leave_type_id: document.getElementById('m-type').value,
    start_date:    document.getElementById('m-start').value,
    end_date:      document.getElementById('m-end').value,
    reason:        document.getElementById('m-reason').value,
  };

  const res  = await fetch('api/requests.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const data = await res.json();

  if (data.success) {
    closeModal();
    showToast(`Request submitted — ${data.total_days} working day(s)`, 'success');
    loadRequests();
  } else {
    errEl.textContent = data.error || 'Submission failed.';
    errEl.style.display = 'block';
  }
}

loadTypes();
loadRequests();
</script>
