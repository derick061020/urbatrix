export const meta = { title: 'Avance de Obra', breadcrumb: 'Proyectos · Avance de obra' };

export function render(data) {
  const { projects, construction } = data;
  const activeProject = projects.find(p => p.status === 'active');
  const cdata = construction.makai;

  const phaseStatus = { done: { cls: 'var(--green-txt)', icon: '✓' }, active: { cls: 'var(--orange)', icon: '⟳' }, pending: { cls: 'var(--muted)', icon: '○' } };

  return `
<div class="view-container">
  <div class="view-header">
    <h1 class="view-title">Avance de Obra</h1>
    <div class="view-actions">
      <button class="btn btn-ghost btn-sm" onclick="openExportModal('Avance de Obra')">🔒 Exportar</button>
      <button class="btn btn-primary btn-sm" onclick="openPublicarReporteModal()">+ Publicar reporte</button>
    </div>
  </div>

  <!-- Selector de proyecto -->
  <div style="display:flex;gap:10px;margin-bottom:20px">
    ${projects.map(p => `
      <div style="background:${p.status === 'active' ? 'var(--bg-card)' : 'var(--bg-surface)'};border:1px solid ${p.status === 'active' ? p.color : 'var(--border)'};border-radius:8px;padding:10px 18px;cursor:pointer;opacity:${p.status !== 'active' ? '.45' : '1'}">
        <div style="font-size:12px;font-weight:500;color:var(--cream-dim)">${p.name}</div>
        <div style="font-size:10px;color:var(--sub);margin-top:2px">${p.status === 'active' ? 'Activo · En construcción' : 'En preparación'}</div>
      </div>
    `).join('')}
  </div>

  <!-- Main content -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

    <!-- Progreso general + fases -->
    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">${activeProject.name} — Progreso general</span>
      </div>

      <!-- Overall ring -->
      <div style="padding:24px;display:flex;align-items:center;gap:24px;border-bottom:1px solid var(--border)">
        <div style="position:relative;width:80px;height:80px;flex-shrink:0">
          <svg viewBox="0 0 80 80" style="transform:rotate(-90deg)">
            <circle cx="40" cy="40" r="32" fill="none" stroke="var(--bg-surface)" stroke-width="8"/>
            <circle cx="40" cy="40" r="32" fill="none" stroke="var(--green-txt)" stroke-width="8"
              stroke-dasharray="${2 * Math.PI * 32}"
              stroke-dashoffset="${2 * Math.PI * 32 * (1 - cdata.overall / 100)}"
              stroke-linecap="round"/>
          </svg>
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column">
            <span style="font-size:18px;font-weight:700;color:var(--cream)">${cdata.overall}%</span>
          </div>
        </div>
        <div>
          <div style="font-size:14px;font-weight:500;color:var(--cream-dim)">Avance global de obra</div>
          <div style="font-size:12px;color:var(--sub);margin-top:4px">102 unidades · ${activeProject.location}</div>
          <div style="font-size:11px;color:var(--muted);margin-top:6px">Entrega estimada Q4 2026</div>
        </div>
      </div>

      <!-- Fases -->
      <div style="padding:16px 20px">
        ${cdata.phases.map((phase, i) => {
          const ps = phaseStatus[phase.status];
          return `
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:${i < cdata.phases.length - 1 ? '14px' : '0'}">
            <div style="width:22px;height:22px;border-radius:50%;background:${phase.status === 'done' ? 'var(--green-txt)22' : phase.status === 'active' ? 'var(--orange)22' : 'var(--bg-surface)'};border:1px solid ${ps.cls};display:flex;align-items:center;justify-content:center;font-size:10px;color:${ps.cls};flex-shrink:0">${ps.icon}</div>
            <div style="flex:1">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                <span style="font-size:12px;color:${phase.status === 'pending' ? 'var(--muted)' : 'var(--cream-dim)'};font-weight:${phase.status === 'active' ? '500' : '400'}">${phase.name}</span>
                <div style="display:flex;align-items:center;gap:8px">
                  <span style="font-size:11px;color:var(--muted)">${phase.date}</span>
                  <span style="font-size:11px;font-weight:600;color:${ps.cls}">${phase.pct}%</span>
                </div>
              </div>
              ${phase.pct > 0 ? `
                <div style="height:4px;background:var(--bg-surface);border-radius:2px;overflow:hidden">
                  <div style="width:${phase.pct}%;height:100%;background:${phase.status === 'done' ? 'var(--green-txt)' : 'var(--orange)'};border-radius:2px;transition:width .3s"></div>
                </div>
              ` : `<div style="height:4px;background:var(--bg-surface);border-radius:2px"></div>`}
            </div>
          </div>`;
        }).join('')}
      </div>
    </div>

    <!-- Reportes publicados -->
    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">Reportes publicados</span>
        <span class="count-badge">${cdata.reports.length}</span>
      </div>
      <div style="padding:8px 0">
        ${cdata.reports.map((r, i) => `
          <div style="padding:16px 20px;border-bottom:1px solid var(--border2)">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
              <div style="flex:1">
                <div style="font-size:12px;font-weight:500;color:var(--cream-dim)">${r.period} — ${r.title}</div>
                <div style="font-size:11px;color:var(--sub);margin-top:4px;line-height:1.5">${r.desc}</div>
              </div>
              <span style="font-size:10px;color:var(--muted);white-space:nowrap;margin-top:2px">${r.date}</span>
            </div>
            <div style="display:flex;gap:8px;margin-top:10px">
              <span class="cell-link" style="font-size:11px" onclick="openVerReporteModal(${i})">Ver reporte →</span>
              <span class="cell-link" style="font-size:11px;color:var(--sub)" onclick="alert('Notificación reenviada a 22 compradores')">Notificar compradores</span>
            </div>
          </div>
        `).join('')}
        <div style="padding:16px 20px">
          <button class="btn btn-ghost btn-sm" style="width:100%">+ Agregar reporte de avance</button>
        </div>
      </div>
    </div>

  </div>

  <!-- Info bar -->
  <div style="margin-top:16px;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:14px 20px;display:flex;align-items:center;gap:12px">
    <span style="font-size:16px">ℹ️</span>
    <span style="font-size:12px;color:var(--sub)">Los reportes publicados generan notificaciones automáticas a todos los compradores activos del proyecto. Las actualizaciones se envían vía Email y WhatsApp según la configuración de plantillas.</span>
  </div>

</div>`;
}
