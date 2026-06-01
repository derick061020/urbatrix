/* ═══════════════════════════════════════════════
   Vista: Dashboard
   Vista principal del admin — KPIs + proyectos +
   expedientes + aprobaciones + tareas + actividad
═══════════════════════════════════════════════ */

export const meta = {
  title: 'Dashboard',
  breadcrumb: 'Vista global · todos los proyectos',
};

export function render(data) {
  const { kpis, projects, clients, approvals, tasks, activity } = data;

  const kpiCards = [
    { label: 'Expedientes activos', value: kpis.expedientes.value, sub: kpis.expedientes.sub, cls: 'k-green', route: 'expedientes' },
    { label: 'Documentos pendientes', value: kpis.documentos.value, sub: kpis.documentos.sub, cls: 'k-orange', route: 'documentos' },
    { label: 'Aprobaciones en cola', value: kpis.aprobaciones.value, sub: kpis.aprobaciones.sub, cls: 'k-red', route: 'aprobaciones' },
    { label: 'Tareas vencidas', value: kpis.tareas.value, sub: kpis.tareas.sub, cls: 'k-blue', route: 'tareas' },
  ];

  return `
<div class="view-container">

  <!-- Alert banner -->
  <div class="alert-banner">
    <div class="alert-dot"></div>
    <div class="alert-text">
      <strong>24 alertas activas</strong> —
      7 verificaciones pendientes · 12 tareas vencidas · 5 documentos sin gestionar.
      Requieren atención inmediata.
    </div>
    <span class="alert-action" onclick="window.location.hash='aprobaciones'">Ver todas →</span>
  </div>

  <!-- Page title -->
  <div class="view-header">
    <h1 class="view-title">Panel de Control</h1>
  </div>

  <!-- KPI Cards -->
  <div class="kpi-grid">
    ${kpiCards.map(k => `
      <div class="kpi-card ${k.cls}" style="cursor:pointer" onclick="window.location.hash='${k.route}'">
        <div class="kpi-label">${k.label}</div>
        <div class="kpi-value">${k.value}</div>
        <div class="kpi-sub">${k.sub}</div>
      </div>
    `).join('')}
  </div>

  <!-- Main Grid: Proyectos + Expedientes -->
  <div class="main-grid">

    <!-- Proyectos -->
    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">Proyectos activos</span>
        <span class="panel-action" onclick="window.location.hash='proyectos'">Ver todos →</span>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>Proyecto</th>
            <th>Estado</th>
            <th>Total</th>
            <th>Vendidas</th>
            <th>Avance</th>
            <th>Valor total</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          ${projects.map(p => `
            <tr>
              <td>
                <div class="cell-name">${p.name}</div>
                <div class="cell-sub">${p.location}</div>
              </td>
              <td><span class="badge badge-${p.status === 'active' ? 'green' : 'gray'}">${p.statusLabel}</span></td>
              <td class="cell-num">${p.total || '—'}</td>
              <td>
                ${p.sold !== null ? `<span class="cell-num">${p.sold}</span><div class="cell-sub">${p.salesProgress}%</div>` : '<span class="cell-muted">—</span>'}
              </td>
              <td>
                ${p.total ? `
                  <div class="prog-wrap">
                    <div class="prog-bar"><div class="prog-fill" style="width:${p.salesProgress}%"></div></div>
                    <span class="prog-val">${p.salesProgress}%</span>
                  </div>
                ` : '<span class="cell-muted">—</span>'}
              </td>
              <td class="${p.status === 'active' ? 'cell-price' : 'cell-muted'}">${p.totalValue}</td>
              <td><span class="cell-link" onclick="window.location.hash='proyectos'">Ver →</span></td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>

    <!-- Expedientes recientes -->
    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">Expedientes recientes</span>
        <span class="panel-action" onclick="window.location.hash='expedientes'">Ver todos →</span>
      </div>
      ${clients.slice(0, 5).map(c => `
        <div class="exp-item">
          <div class="exp-avatar" style="background:${avatarColor(c.id)}">${c.initials}</div>
          <div class="exp-info">
            <div class="exp-name">${c.firstName} ${c.lastName}</div>
            <div class="exp-detail">${c.unit} · ${c.project.replace(' Residences','')}</div>
          </div>
          <div class="exp-right">
            <span class="exp-status" style="color:${statusColor(c.statusColor)}">${c.statusLabel}</span>
            <div class="cell-sub">${c.lastAction}</div>
          </div>
        </div>
      `).join('')}
    </div>
  </div>

  <!-- Bottom Grid: Aprobaciones + Tareas + Actividad -->
  <div class="bottom-grid">

    <!-- Aprobaciones urgentes -->
    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">Verificaciones pendientes</span>
        <span class="count-badge count-red">${approvals.length}</span>
        <span class="panel-action" onclick="window.location.hash='aprobaciones'">Ver todas →</span>
      </div>
      <div style="padding: 4px 0;">
        ${approvals.slice(0, 5).map(a => `
          <div style="padding: 10px 16px; border-bottom: 1px solid var(--border2); display:flex; flex-direction:column; gap:6px;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
              <span style="font-size:12px; font-weight:500; color:var(--cream-dim)">${a.client}</span>
              <span class="badge badge-${a.typeColor}">${a.type}</span>
            </div>
            <div style="font-size:11px; color:var(--sub)">${a.description}</div>
            <div style="display:flex; align-items:center; justify-content:space-between;">
              <span style="font-size:10px; color:var(--muted)">${a.date} · ${a.requestedBy}</span>
              <div class="action-btns">
                <button class="btn-approve" onclick="approveItem(${a.id})">✓ Verificar</button>
                <button class="btn-reject" onclick="rejectItem(${a.id})">✗ Rechazar</button>
              </div>
            </div>
          </div>
        `).join('')}
      </div>
    </div>

    <!-- Tareas del día -->
    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">Tareas del día</span>
        <span class="count-badge count-red">${tasks.filter(t=>!t.done).length}</span>
        <span class="panel-action" onclick="window.location.hash='tareas'">Ver todas →</span>
      </div>
      <div style="padding: 8px 16px;">
        <ul class="task-list">
          ${tasks.map(t => `
            <li class="task-item">
              <div class="task-check ${t.done ? 'done' : ''}" onclick="toggleTask(${t.id})"></div>
              <div class="task-prio ${t.priority === 'high' ? 'prio-high' : t.priority === 'medium' ? 'prio-medium' : 'prio-low'}"></div>
              <div class="task-text ${t.done ? 'done' : ''}">${t.text}</div>
              <div class="task-meta">${t.due}</div>
            </li>
          `).join('')}
        </ul>
      </div>
    </div>

    <!-- Actividad reciente -->
    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">Actividad reciente</span>
      </div>
      <div style="padding: 8px 16px;">
        <div class="activity-list">
          ${activity.map((a, i) => `
            <div class="act-item">
              <div class="act-dot-col">
                <div class="act-dot" style="background:${dotColor(a.dot)}"></div>
                ${i < activity.length - 1 ? '<div class="act-line"></div>' : ''}
              </div>
              <div class="act-info">
                <div class="act-text">${a.text}</div>
                <div class="act-time">${a.time}</div>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    </div>

  </div><!-- /bottom-grid -->

</div><!-- /view-container -->
  `;
}

export function init() {
  // Approve / Reject buttons
  window.approveItem = (id) => {
    const el = event.target.closest('[style*="padding: 10px"]');
    if (el) { el.style.opacity = '.4'; el.style.pointerEvents = 'none'; }
    showToast('✓ Aprobación procesada', 'green');
  };
  window.rejectItem = (id) => {
    const el = event.target.closest('[style*="padding: 10px"]');
    if (el) { el.style.opacity = '.4'; el.style.pointerEvents = 'none'; }
    showToast('✗ Solicitud rechazada', 'red');
  };
  window.toggleTask = (id) => {
    const checkbox = event.target;
    checkbox.classList.toggle('done');
    const textEl = checkbox.closest('li').querySelector('.task-text');
    if (textEl) textEl.classList.toggle('done');
  };
}

// ── Helpers ──────────────────────────────────
function avatarColor(id) {
  const colors = ['#4A5E3F','#3a7abd','#c97c40','#b84040','#6b5b8a','#2a7a6a','#8a5c2a'];
  return colors[id % colors.length];
}
function statusColor(c) {
  return c === 'green' ? 'var(--green-txt)' :
         c === 'orange' ? 'var(--orange)' :
         c === 'red' ? '#d06060' :
         c === 'blue' ? 'var(--blue)' : 'var(--sub)';
}
function dotColor(c) {
  return c === 'green' ? 'var(--green-txt)' :
         c === 'orange' ? 'var(--orange)' :
         c === 'red' ? 'var(--red)' :
         c === 'blue' ? 'var(--blue)' : 'var(--muted)';
}

// ── Toast global ─────────────────────────────
function showToast(msg, color) {
  const existing = document.getElementById('duna-toast');
  if (existing) existing.remove();
  const t = document.createElement('div');
  t.id = 'duna-toast';
  t.style.cssText = `
    position:fixed; bottom:24px; right:24px; z-index:9999;
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 8px; padding: 10px 16px;
    font-size: 12px; font-weight: 500;
    color: ${color === 'green' ? 'var(--green-txt)' : '#d06060'};
    box-shadow: 0 4px 20px rgba(0,0,0,.4);
    animation: fadeIn .15s ease;
  `;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2500);
}
