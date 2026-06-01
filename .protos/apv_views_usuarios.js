export const meta = { title: 'Usuarios', breadcrumb: 'Equipo · Usuarios del sistema' };

const FLAG = {
  'Rep. Dominicana':'🇩🇴', USA:'🇺🇸', España:'🇪🇸', México:'🇲🇽',
  Colombia:'🇨🇴', Brasil:'🇧🇷', Francia:'🇫🇷', UAE:'🇦🇪', Argentina:'🇦🇷',
};

const PROSPECTOS = [
  { id:'p1', firstName:'Michael',  lastName:'Chen',      initials:'MC', email:'m.chen@email.com',      phone:'+1 415-555-0801',  country:'USA',    source:'Web',       interest:'Makai',  createdAt:'2026-04-20', lastAction:'hace 2 días'  },
  { id:'p2', firstName:'Isabelle', lastName:'Dupont',    initials:'ID', email:'i.dupont@email.com',    phone:'+33 7-55-55-0802', country:'Francia', source:'Referido',  interest:'Makai',  createdAt:'2026-05-01', lastAction:'hace 5 horas' },
  { id:'p3', firstName:'Ahmed',    lastName:'Al-Rashid', initials:'AR', email:'ahmed.ar@email.com',    phone:'+971 50-555-0803', country:'UAE',     source:'Instagram', interest:'Naviva', createdAt:'2026-05-06', lastAction:'hace 1 día'   },
];

export function render(data) {
  const clients = data.clients || [];

  const urgent = clients.filter(c =>
    ['kyc_pending', 'signature_required', 'payment_overdue'].includes(c.status)
  ).length;

  return `
<div class="view-container">
  <div class="view-header">
    <h1 class="view-title">Usuarios</h1>
    <span style="font-size:11px;color:var(--sub)">${clients.length + PROSPECTOS.length} personas · ${clients.length} con unidad asignada</span>
    <div class="view-actions">
      <button class="btn btn-ghost btn-sm" onclick="openExportModal('Usuarios')">🔒 Exportar</button>
      <button class="btn btn-primary btn-sm" onclick="openNuevoUsuarioModal()">+ Nuevo usuario</button>
    </div>
  </div>

  <!-- Stats -->
  <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
    ${[
      { icon:'🏠', label:'Con unidad asignada', val: clients.length,       color:'var(--green-txt)'  },
      { icon:'⚠',  label:'Requieren gestión',   val: urgent,               color:'var(--orange)'     },
      { icon:'🔍', label:'Prospectos',           val: PROSPECTOS.length,    color:'var(--blue)'       },
      { icon:'👥', label:'Total personas',       val: clients.length + PROSPECTOS.length, color:'var(--cream-dim)' },
    ].map(s => `
      <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:14px 20px;display:flex;align-items:center;gap:12px;flex:1;min-width:150px">
        <span style="font-size:20px">${s.icon}</span>
        <div>
          <div style="font-size:22px;font-weight:700;color:${s.color};line-height:1">${s.val}</div>
          <div style="font-size:11px;color:var(--sub);margin-top:2px">${s.label}</div>
        </div>
      </div>
    `).join('')}
  </div>

  <!-- Filtros -->
  <div class="filter-bar">
    <span class="filter-pill active" data-filter="all">Todos <span class="count">${clients.length + PROSPECTOS.length}</span></span>
    <span class="filter-pill" data-filter="buyer">Con unidad <span class="count">${clients.length}</span></span>
    <span class="filter-pill" data-filter="urgent">Por gestionar <span class="count">${urgent}</span></span>
    <span class="filter-pill" data-filter="prospect">Prospectos <span class="count">${PROSPECTOS.length}</span></span>
    <span class="filter-spacer"></span>
    <div class="filter-search">
      <span class="search-ico">⌕</span>
      <input type="text" placeholder="Buscar comprador…" id="buyers-search">
    </div>
  </div>

  <!-- Tabla -->
  <div class="panel">
    <table class="data-table" id="buyers-table">
      <thead>
        <tr>
          <th>Comprador</th>
          <th>País</th>
          <th>Unidad · Proyecto</th>
          <th>Progreso pago</th>
          <th>Estado</th>
          <th>Agente</th>
          <th>Última actividad</th>
          <th></th>
        </tr>
      </thead>
      <tbody>

        ${clients.map(c => {
          const isUrgent = ['kyc_pending','signature_required','payment_overdue'].includes(c.status);
          return `
          <tr data-filter="buyer" data-urgent="${isUrgent}">
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:32px;height:32px;border-radius:50%;background:var(--green-lite);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:var(--green-txt);flex-shrink:0">${c.initials}</div>
                <div>
                  <div class="cell-name">${c.firstName} ${c.lastName}</div>
                  <div style="font-size:10px;color:var(--muted)">${c.email}</div>
                </div>
              </div>
            </td>
            <td><span style="font-size:12px">${FLAG[c.country] || '🌍'} ${c.country}</span></td>
            <td>
              <div style="font-size:12px;font-weight:500;color:var(--cream-dim)">${c.unit}</div>
              <div style="font-size:10px;color:var(--muted)">${c.project}</div>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
                <div style="flex:1;background:var(--bg-surface);border-radius:4px;height:5px;min-width:64px">
                  <div style="height:5px;border-radius:4px;width:${c.paidPct}%;background:${c.paidPct === 100 ? 'var(--green-txt)' : c.paidPct > 20 ? 'var(--blue)' : 'var(--orange)'}"></div>
                </div>
                <span style="font-size:11px;color:var(--sub);white-space:nowrap">${c.paidPct}%</span>
              </div>
              <div style="font-size:10px;color:var(--muted)">${c.paid} de ${c.price}</div>
            </td>
            <td><span class="badge badge-${c.statusColor}">${c.statusLabel}</span></td>
            <td><span style="font-size:11px;color:var(--sub)">${c.agent}</span></td>
            <td><span style="font-size:11px;color:var(--muted)">${c.lastAction}</span></td>
            <td>
              <div style="display:flex;gap:8px;white-space:nowrap">
                <span class="cell-link" onclick="verCompradorModal(${c.id})">Ver perfil</span>
                <span class="cell-link" style="color:var(--sub)" onclick="editarCompradorModal(${c.id})">Editar</span>
                <span class="cell-link" style="color:var(--sub)" onclick="window.location.hash='expediente/${c.id}'">Exp. →</span>
              </div>
            </td>
          </tr>`;
        }).join('')}

        ${PROSPECTOS.map(p => `
          <tr data-filter="prospect" data-urgent="false">
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(58,122,189,.15);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:var(--blue);flex-shrink:0">${p.initials}</div>
                <div>
                  <div class="cell-name">${p.firstName} ${p.lastName}</div>
                  <div style="font-size:10px;color:var(--muted)">${p.email}</div>
                </div>
              </div>
            </td>
            <td><span style="font-size:12px">${FLAG[p.country] || '🌍'} ${p.country}</span></td>
            <td>
              <div style="font-size:11px;color:var(--muted);font-style:italic">Sin unidad asignada</div>
              <div style="font-size:10px;color:var(--muted)">Interés: ${p.interest} · vía ${p.source}</div>
            </td>
            <td><span style="font-size:11px;color:var(--muted)">—</span></td>
            <td><span class="badge badge-blue">Prospecto</span></td>
            <td><span style="font-size:11px;color:var(--muted)">—</span></td>
            <td><span style="font-size:11px;color:var(--muted)">${p.lastAction}</span></td>
            <td>
              <div style="display:flex;gap:8px;white-space:nowrap">
                <span class="cell-link" onclick="verProspectoModal('${p.id}')">Ver perfil</span>
                <span class="cell-link" style="color:var(--sub)" onclick="editarProspectoModal('${p.id}')">Editar</span>
                <span class="cell-link" style="color:var(--green-txt)" onclick="openNuevoExpedienteModal()">+ Exp.</span>
              </div>
            </td>
          </tr>
        `).join('')}

      </tbody>
    </table>
  </div>
</div>`;
}

export function init(data) {
  document.querySelectorAll('.filter-pill[data-filter]').forEach(pill => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      filterRows(pill.dataset.filter);
    });
  });

  document.getElementById('buyers-search')?.addEventListener('input', e => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#buyers-table tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });

  window.verCompradorModal    = (id) => { const c = data.clients.find(x => x.id === id); if (c) window.openCompradorModal(c); };
  window.verProspectoModal    = (id) => { const p = PROSPECTOS.find(x => x.id === id);   if (p) window.openProspectoModal(p); };
  window.editarCompradorModal = (id) => { const c = data.clients.find(x => x.id === id); if (c) window.openEditarCompradorModal(c); };
  window.editarProspectoModal = (id) => { const p = PROSPECTOS.find(x => x.id === id);   if (p) window.openEditarProspectoModal(p); };
}

function filterRows(filter) {
  document.querySelectorAll('#buyers-table tbody tr').forEach(row => {
    if (filter === 'all')     { row.style.display = ''; return; }
    if (filter === 'urgent')  { row.style.display = row.dataset.urgent === 'true' ? '' : 'none'; return; }
    row.style.display = row.dataset.filter === filter ? '' : 'none';
  });
}
