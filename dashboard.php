<?php
$pageTitle = 'Dashboard';
require_once 'partials/header.php';
?>
<main class="main">

  <!-- STAT CARDS -->
  <div class="grid-4" id="balance-cards" style="margin-bottom:24px">
    <div class="card" style="animation: fadeUp 0.3s ease both">
      <div style="color:var(--muted);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Loading balances…</div>
    </div>
  </div>

  <div class="grid-2" style="gap:24px">
    <!-- RECENT REQUESTS -->
    <div class="card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div class="card-title" style="margin:0">Recent Requests</div>
        <a href="my-requests.php" style="font-size:13px;color:var(--accent);text-decoration:none;font-weight:500">View all →</a>
      </div>
      <div id="recent-requests"><div style="color:var(--muted);font-size:14px">Loading…</div></div>
    </div>

    <!-- QUICK REQUEST -->
    <div class="card">
      <div class="card-title">Quick Leave Request</div>
      <div class="form-field">
        <label>Leave Type</label>
        <select id="q-type"><option value="">Select type…</option></select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="form-field">
          <label>Start Date</label>
          <input type="date" id="q-start"/>
        </div>
        <div class="form-field">
          <label>End Date</label>
          <input type="date" id="q-end"/>
        </div>
      </div>
      <div class="form-field">
        <label>Reason <span style="color:var(--muted)">(optional)</span></label>
        <textarea id="q-reason" rows="3" placeholder="Briefly describe the reason for your leave…"></textarea>
      </div>
      <div id="q-error" style="color:var(--red);font-size:13px;margin-bottom:12px;display:none"></div>
      <button class="btn btn-primary" style="width:100%;justify-content:center" onclick="submitQuickRequest()">
        Submit Request
      </button>
    </div>
  </div>

</main>

<style>
@keyframes fadeUp { from { opacity:0; transform:translateY(12px); } }
.stat-card { animation: fadeUp 0.3s ease both; }
.balance-bar-bg {
  height: 6px; background: var(--border); border-radius: 50px; margin-top: 12px; overflow: hidden;
}
.balance-bar { height: 100%; border-radius: 50px; transition: width 0.8s ease; }
</style>

<script>
const today = new Date().toISOString().split('T')[0];
document.getElementById('q-start').min = today;
document.getElementById('q-end').min   = today;
document.getElementById('q-start').addEventListener('change', function() {
  document.getElementById('q-end').min = this.value;
});

// Load leave types
async function loadTypes() {
  const res  = await fetch('api/leave_types.php');
  const data = await res.json();
  const sel  = document.getElementById('q-type');
  data.types.forEach(t => {
    const o = document.createElement('option');
    o.value = t.id;
    o.textContent = t.name;
    sel.appendChild(o);
  });
}

// Load balances
async function loadBalances() {
  const res  = await fetch('api/balances.php');
  const data = await res.json();
  const container = document.getElementById('balance-cards');

  if (!data.balances || !data.balances.length) {
    container.innerHTML = '<div class="card" style="grid-column:1/-1;color:var(--muted);font-size:14px">No leave balances found.</div>';
    return;
  }

  container.innerHTML = data.balances.map((b, i) => {
    const pct = b.total_days > 0 ? ((b.used_days / b.total_days) * 100).toFixed(0) : 0;
    return `
    <div class="card stat-card" style="animation-delay:${i*0.07}s">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
        <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">${b.leave_type}</div>
        <div style="font-size:10px;color:var(--muted)">${pct}% used</div>
      </div>
      <div style="font-size:32px;font-weight:700;font-family:'DM Serif Display',serif">${b.remaining_days}</div>
      <div style="font-size:12px;color:var(--muted)">of ${b.total_days} days remaining</div>
      <div class="balance-bar-bg">
        <div class="balance-bar" style="width:${pct}%;background:${b.color}"></div>
      </div>
    </div>`;
  }).join('');
}

// Load recent requests
async function loadRecent() {
  const res  = await fetch('api/requests.php');
  const data = await res.json();
  const el   = document.getElementById('recent-requests');
  const list = (data.requests || []).slice(0, 5);

  if (!list.length) {
    el.innerHTML = '<div class="empty-state"><div class="empty-icon">📋</div><p>No leave requests yet</p></div>';
    return;
  }

  el.innerHTML = list.map(r => `
    <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border)">
      <div style="width:10px;height:10px;border-radius:50%;background:${r.color};flex-shrink:0"></div>
      <div style="flex:1">
        <div style="font-size:14px;font-weight:600">${r.leave_type}</div>
        <div style="font-size:12px;color:var(--muted)">${r.start_date} → ${r.end_date} · ${r.total_days} day(s)</div>
      </div>
      <span class="badge badge-${r.status}">${r.status}</span>
    </div>`).join('');
}

async function submitQuickRequest() {
  const errEl = document.getElementById('q-error');
  errEl.style.display = 'none';

  const payload = {
    leave_type_id: document.getElementById('q-type').value,
    start_date: document.getElementById('q-start').value,
    end_date:   document.getElementById('q-end').value,
    reason:     document.getElementById('q-reason').value,
  };

  const res  = await fetch('api/requests.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const data = await res.json();

  if (data.success) {
    showToast(`Leave request submitted — ${data.total_days} working day(s)`, 'success');
    document.getElementById('q-type').value   = '';
    document.getElementById('q-start').value  = '';
    document.getElementById('q-end').value    = '';
    document.getElementById('q-reason').value = '';
    loadBalances();
    loadRecent();
  } else {
    errEl.textContent = data.error || 'Submission failed.';
    errEl.style.display = 'block';
  }
}

loadTypes();
loadBalances();
loadRecent();
</script>
