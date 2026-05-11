<?php
$pageTitle = 'Leave Calendar';
require_once 'partials/header.php';
?>
<main class="main">
  <div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
      <div class="card-title" style="margin:0" id="cal-heading">Leave Calendar</div>
      <div style="display:flex;align-items:center;gap:12px">
        <button class="btn btn-ghost" style="padding:8px 14px" onclick="changeMonth(-1)">← Prev</button>
        <button class="btn btn-ghost" style="padding:8px 14px" onclick="changeMonth(1)">Next →</button>
      </div>
    </div>

    <!-- Calendar Grid -->
    <div id="calendar-grid"></div>

    <!-- Legend -->
    <div id="legend" style="display:flex;flex-wrap:wrap;gap:16px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)"></div>
  </div>
</main>

<style>
.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
}
.cal-day-name {
  text-align: center;
  font-size: 12px;
  font-weight: 600;
  color: var(--muted);
  padding: 8px 0;
  text-transform: uppercase;
  letter-spacing: .5px;
}
.cal-cell {
  min-height: 88px;
  padding: 8px;
  border-radius: 8px;
  background: var(--paper);
  position: relative;
}
.cal-cell.today { background: var(--accent-light); border: 1.5px solid #bfdbfe; }
.cal-cell.other-month { opacity: 0.35; }
.cal-cell.weekend { background: #f9fafb; }
.cal-date {
  font-size: 13px;
  font-weight: 600;
  color: var(--ink);
  margin-bottom: 4px;
}
.cal-cell.today .cal-date {
  background: var(--accent);
  color: #fff;
  width: 24px; height: 24px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px;
}
.cal-event {
  font-size: 11px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 4px;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: #fff;
}
</style>

<script>
let curMonth = new Date().getMonth() + 1;
let curYear  = new Date().getFullYear();

const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const DAYS   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

async function loadCalendar() {
  const res  = await fetch(`api/calendar.php?month=${curMonth}&year=${curYear}`);
  const data = await res.json();
  renderCalendar(data.events || []);

  // Legend
  const colors = {};
  (data.events || []).forEach(e => { colors[e.leave_type] = e.color; });
  const leg = document.getElementById('legend');
  leg.innerHTML = Object.entries(colors).map(([name, color]) => `
    <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
      <span style="width:12px;height:12px;border-radius:3px;background:${color};display:inline-block"></span>
      ${name}
    </div>`).join('');
  if (!leg.innerHTML) leg.innerHTML = '<span style="color:var(--muted);font-size:13px">No approved leave this month</span>';
}

function renderCalendar(events) {
  document.getElementById('cal-heading').textContent = `${MONTHS[curMonth - 1]} ${curYear}`;

  const firstDay = new Date(curYear, curMonth - 1, 1).getDay();
  const daysInMonth = new Date(curYear, curMonth, 0).getDate();
  const daysInPrev  = new Date(curYear, curMonth - 1, 0).getDate();
  const today = new Date();

  // Build event map: date string -> array of events
  const eventMap = {};
  events.forEach(e => {
    let d = new Date(e.start_date + 'T00:00:00');
    const end = new Date(e.end_date + 'T00:00:00');
    while (d <= end) {
      const key = d.toISOString().split('T')[0];
      if (!eventMap[key]) eventMap[key] = [];
      eventMap[key].push(e);
      d.setDate(d.getDate() + 1);
    }
  });

  let html = '<div class="cal-grid">';
  DAYS.forEach(d => { html += `<div class="cal-day-name">${d}</div>`; });

  // Prev month padding
  for (let i = 0; i < firstDay; i++) {
    const day = daysInPrev - firstDay + i + 1;
    html += `<div class="cal-cell other-month"><div class="cal-date">${day}</div></div>`;
  }

  // Current month
  for (let day = 1; day <= daysInMonth; day++) {
    const dateStr  = `${curYear}-${String(curMonth).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
    const dow      = new Date(dateStr + 'T00:00:00').getDay();
    const isToday  = today.getDate() === day && today.getMonth() + 1 === curMonth && today.getFullYear() === curYear;
    const isWeekend = dow === 0 || dow === 6;
    const dayEvents = eventMap[dateStr] || [];

    let cls = 'cal-cell';
    if (isToday)   cls += ' today';
    if (isWeekend) cls += ' weekend';

    const eventsHtml = dayEvents.slice(0, 2).map(e =>
      `<div class="cal-event" style="background:${e.color}" title="${e.employee_name} - ${e.leave_type}">${e.employee_name.split(' ')[0]}</div>`
    ).join('');
    const moreHtml = dayEvents.length > 2 ? `<div style="font-size:10px;color:var(--muted)">+${dayEvents.length - 2} more</div>` : '';

    html += `<div class="${cls}">
      <div class="cal-date">${day}</div>
      ${eventsHtml}${moreHtml}
    </div>`;
  }

  // Next month padding
  const totalCells = firstDay + daysInMonth;
  const remaining  = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
  for (let i = 1; i <= remaining; i++) {
    html += `<div class="cal-cell other-month"><div class="cal-date">${i}</div></div>`;
  }

  html += '</div>';
  document.getElementById('calendar-grid').innerHTML = html;
}

function changeMonth(dir) {
  curMonth += dir;
  if (curMonth > 12) { curMonth = 1;  curYear++; }
  if (curMonth < 1)  { curMonth = 12; curYear--; }
  loadCalendar();
}

loadCalendar();
</script>
