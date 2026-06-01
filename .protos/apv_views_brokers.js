export const meta = { title: 'Brokers y Externos', breadcrumb: 'Equipo · Brokers y agencias' };

// ── Material data (admin-managed) ─────────────────────────────────────────
const FMT_CLS = { PDF:'badge-red', ZIP:'badge-blue', MP4:'badge-orange', XLSX:'badge-green' };

let ADMIN_MATERIALS = [
  { id:1, title:'Renders del proyecto',    desc:'18 imágenes de alta res. · Aprobadas',   fmt:'ZIP',  cat:'Imágenes',     size:'248 MB',  updated:'Abr 2026', visible:true,  icon:'img',  color:'var(--blue)'      },
  { id:2, title:'Planos de unidades',      desc:'Por tipología · PDF oficial',             fmt:'PDF',  cat:'Planos',       size:'12.4 MB', updated:'Mar 2026', visible:true,  icon:'pdf',  color:'var(--red)'       },
  { id:3, title:'Video tour 360°',         desc:'MP4 · 2 min 34s · Alta resolución',      fmt:'MP4',  cat:'Video',        size:'1.8 GB',  updated:'Abr 2026', visible:true,  icon:'mp4',  color:'var(--orange)'    },
  { id:4, title:'Ficha de inversión',      desc:'ROI, plan de pagos, comparativas',        fmt:'PDF',  cat:'Finanzas',     size:'3.2 MB',  updated:'May 2026', visible:true,  icon:'pdf',  color:'var(--red)'       },
  { id:5, title:'Brochure oficial',        desc:'24 páginas · Versión ES/EN/FR',           fmt:'PDF',  cat:'Presentación', size:'18.6 MB', updated:'Mar 2026', visible:true,  icon:'pdf',  color:'var(--red)'       },
  { id:6, title:'Kit redes sociales',      desc:'Stories + posts · Medidas aprobadas',     fmt:'ZIP',  cat:'Marketing',    size:'156 MB',  updated:'Abr 2026', visible:true,  icon:'img',  color:'var(--blue)'      },
  { id:7, title:'Fotos avance de obra',    desc:'Abril 2026 · 12 fotos verificadas',       fmt:'ZIP',  cat:'Obra',         size:'84 MB',   updated:'Abr 2026', visible:true,  icon:'img',  color:'var(--blue)'      },
  { id:8, title:'Calculadora de comisión', desc:'Excel interactivo con tasas aplicadas',   fmt:'XLSX', cat:'Finanzas',     size:'2.1 MB',  updated:'Ene 2026', visible:true,  icon:'xlsx', color:'var(--green-txt)' },
];

let nextMatId = 9;

const ICON_DEFS = {
  pdf:   { svg: (s=15)=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:${s}px;height:${s}px"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8L14 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`,        label:'Documento', color:'var(--red)'       },
  img:   { svg: (s=15)=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:${s}px;height:${s}px"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`,                                                                                                                     label:'Imágenes',  color:'var(--blue)'      },
  mp4:   { svg: (s=15)=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:${s}px;height:${s}px"><rect x="2" y="5" width="15" height="14" rx="2"/><path d="M17 9l5-3v12l-5-3V9z"/></svg>`,                                                                                                                                                             label:'Video',     color:'var(--orange)'   },
  xlsx:  { svg: (s=15)=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:${s}px;height:${s}px"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8L14 2z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12l4 4m0-4l-4 4"/></svg>`,                                                                                                  label:'Excel',     color:'var(--green-txt)'},
  zip:   { svg: (s=15)=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:${s}px;height:${s}px"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`, label:'Archivo',   color:'var(--blue)'     },
  chart: { svg: (s=15)=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:${s}px;height:${s}px"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>`,                                                                                   label:'Gráfica',   color:'var(--orange)'   },
  book:  { svg: (s=15)=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:${s}px;height:${s}px"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>`,                                                                                                                                 label:'Brochure',  color:'var(--cream-dim)'},
  calc:  { svg: (s=15)=>`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:${s}px;height:${s}px"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="8" y2="10"/><line x1="12" y1="10" x2="12" y2="10"/><line x1="16" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="8" y2="14"/><line x1="12" y1="14" x2="12" y2="14"/><line x1="16" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="16" y2="18"/></svg>`,  label:'Calculadora',color:'var(--green-txt)'},
};

function matIconSm(type) { return (ICON_DEFS[type] || ICON_DEFS.pdf).svg(15); }

export function render(data) {
  const { brokers } = data;

  const counts = {
    all:      brokers.length,
    active:   brokers.filter(b => b.status === 'active').length,
    pending:  brokers.filter(b => b.status === 'pending').length,
    expiring: brokers.filter(b => b.contract === 'expiring').length,
  };

  const totalClients     = brokers.reduce((s, b) => s + b.clients, 0);
  const totalCommissions = brokers
    .filter(b => b.commission !== '$0')
    .reduce((s, b) => s + parseFloat(b.commission.replace(/[$,]/g, '')), 0);

  const contractBadge = { active: 'green', expiring: 'orange', pending: 'gray' };
  const contractLabel = { active: 'Activo', expiring: 'Por vencer', pending: 'Pendiente' };
  const countryFlag   = { RD: '🇩🇴', USA: '🇺🇸', España: '🇪🇸', México: '🇲🇽', Colombia: '🇨🇴' };

  return `
<div class="view-container">

  <!-- Header -->
  <div class="view-header">
    <h1 class="view-title">Brokers y Externos</h1>
    <span style="font-size:11px;color:var(--sub)">${counts.all} colaboradores</span>
    <div class="view-actions">
      <div id="actions-brokers" style="display:flex;gap:8px">
        <button class="btn btn-ghost btn-sm" onclick="openExportModal('Brokers')">🔒 Exportar</button>
        <button class="btn btn-primary btn-sm" onclick="openNuevoBrokerModal()">+ Nuevo broker</button>
      </div>
      <div id="actions-material" style="display:none">
        <button class="btn btn-primary btn-sm" onclick="openAddMaterialModal()">+ Agregar recurso</button>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div style="display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:20px">
    <button class="broker-tab active" data-tab="brokers" style="padding:10px 18px;font-size:12px;font-weight:500;color:var(--cream);background:none;border:none;border-bottom:2px solid var(--green-txt);cursor:pointer;margin-bottom:-1px;transition:color .15s">
      Brokers <span style="font-size:10px;color:var(--sub);margin-left:4px">${counts.all}</span>
    </button>
    <button class="broker-tab" data-tab="material" style="padding:10px 18px;font-size:12px;font-weight:500;color:var(--sub);background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;margin-bottom:-1px;transition:color .15s">
      Material de ventas <span style="font-size:10px;color:var(--muted);margin-left:4px" id="mat-count-badge">${ADMIN_MATERIALS.length}</span>
    </button>
  </div>

  <!-- ── Tab: Brokers ───────────────────────────────────────── -->
  <div id="tab-brokers">

    <!-- Stats -->
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
      ${[
        { label: 'Brokers activos',      val: counts.active,                                    cls: 'var(--green-txt)' },
        { label: 'Pendientes',           val: counts.pending,                                   cls: 'var(--orange)'    },
        { label: 'Contratos por vencer', val: counts.expiring,                                  cls: 'var(--orange)'    },
        { label: 'Clientes referidos',   val: totalClients,                                     cls: 'var(--blue)'      },
        { label: 'Comisiones totales',   val: '$' + totalCommissions.toLocaleString('en-US'),   cls: 'var(--cream)'     },
      ].map(s => `
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:10px 16px;min-width:130px">
          <div style="font-size:20px;font-weight:600;color:${s.cls}">${s.val}</div>
          <div style="font-size:11px;color:var(--sub);margin-top:2px">${s.label}</div>
        </div>
      `).join('')}
    </div>

    <!-- Filtros -->
    <div class="filter-bar">
      <span class="filter-pill active" data-filter="all">Todos <span class="count">${counts.all}</span></span>
      <span class="filter-pill" data-filter="active">Activos <span class="count">${counts.active}</span></span>
      <span class="filter-pill" data-filter="pending">Pendientes <span class="count">${counts.pending}</span></span>
      <span class="filter-pill" data-filter="expiring">Por vencer <span class="count">${counts.expiring}</span></span>
    </div>

    <!-- Tabla brokers -->
    <div class="panel">
      <table class="data-table" id="brokers-table">
        <thead>
          <tr>
            <th>Broker / Agencia</th>
            <th>País</th>
            <th>Clientes ref.</th>
            <th>Comisión acum.</th>
            <th>Contrato</th>
            <th>Vencimiento</th>
            <th>Estado</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          ${brokers.map(b => `
            <tr data-status="${b.status}" data-contract="${b.contract}">
              <td>
                <div class="cell-name">${b.name}</div>
                <div class="cell-sub">${b.agency} · <a href="mailto:${b.email}" style="color:var(--muted);text-decoration:none">${b.email}</a></div>
              </td>
              <td>
                <span style="font-size:14px">${countryFlag[b.country] || '🌍'}</span>
                <span style="font-size:12px;color:var(--sub);margin-left:6px">${b.country}</span>
              </td>
              <td class="cell-num">${b.clients}</td>
              <td class="cell-price">${b.commission}</td>
              <td><span class="badge badge-${contractBadge[b.contract]}">${contractLabel[b.contract]}</span></td>
              <td>
                ${b.contractExpiry
                  ? `<span style="font-size:11px;color:${b.contract === 'expiring' ? 'var(--orange)' : 'var(--sub)'}">${b.contractExpiry}</span>`
                  : `<span style="font-size:11px;color:var(--muted)">—</span>`}
              </td>
              <td>
                <span class="badge badge-${b.status === 'active' ? 'green' : 'gray'}">${b.status === 'active' ? 'Activo' : 'Pendiente'}</span>
              </td>
              <td>
                <div style="display:flex;gap:8px">
                  <span class="cell-link" onclick="openBrokerModal(${b.id})">Ver</span>
                  ${b.status === 'pending' ? `<span class="cell-link" style="color:var(--green-txt)" onclick="activateBroker(${b.id})">Activar</span>` : ''}
                </div>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── Tab: Material ─────────────────────────────────────── -->
  <div id="tab-material" style="display:none">
    <div class="panel">
      <table class="data-table" id="admin-material-table">
        <thead>
          <tr>
            <th style="width:32px"></th>
            <th>Recurso</th>
            <th>Categoría</th>
            <th>Formato</th>
            <th>Tamaño</th>
            <th>Actualizado</th>
            <th>Visible</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          ${ADMIN_MATERIALS.map(m => `
            <tr data-mat-id="${m.id}">
              <td style="padding:10px 8px 10px 16px">
                <div style="width:30px;height:30px;border-radius:6px;background:var(--bg-card2);display:flex;align-items:center;justify-content:center;color:${m.color}">
                  ${matIconSm(m.icon)}
                </div>
              </td>
              <td>
                <div class="cell-name">${m.title}</div>
                <div class="cell-sub">${m.desc}</div>
              </td>
              <td><span class="badge badge-gray" style="font-size:10px">${m.cat}</span></td>
              <td><span class="badge ${FMT_CLS[m.fmt]}" style="font-size:10px">${m.fmt}</span></td>
              <td><span style="font-size:11px;color:var(--sub)">${m.size}</span></td>
              <td><span style="font-size:11px;color:var(--sub)">${m.updated}</span></td>
              <td>
                <label class="mat-toggle" title="${m.visible ? 'Ocultar' : 'Hacer visible'}">
                  <input type="checkbox" ${m.visible ? 'checked' : ''} onchange="toggleMaterialVisibility(${m.id}, this.checked)">
                  <span class="mat-toggle-slider"></span>
                </label>
              </td>
              <td>
                <div style="display:flex;gap:8px">
                  <span class="cell-link" onclick="openEditMaterialModal(${m.id})">Editar</span>
                  <span class="cell-link" style="color:var(--red)" onclick="deleteMaterial(${m.id})">Eliminar</span>
                </div>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  </div>

</div>`;
}

export function init() {
  // ── Tab switching ─────────────────────────────────────────────────────
  document.querySelectorAll('.broker-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.tab;

      // Update tab styles
      document.querySelectorAll('.broker-tab').forEach(t => {
        const isActive = t.dataset.tab === target;
        t.style.color        = isActive ? 'var(--cream)' : 'var(--sub)';
        t.style.borderBottom = isActive ? '2px solid var(--green-txt)' : '2px solid transparent';
        t.classList.toggle('active', isActive);
      });

      // Show/hide panels
      document.getElementById('tab-brokers').style.display  = target === 'brokers'  ? '' : 'none';
      document.getElementById('tab-material').style.display = target === 'material' ? '' : 'none';

      // Swap header actions
      document.getElementById('actions-brokers').style.display  = target === 'brokers'  ? 'flex' : 'none';
      document.getElementById('actions-material').style.display = target === 'material' ? ''     : 'none';
    });
  });

  // ── Broker table filters ──────────────────────────────────────────────
  document.querySelectorAll('.filter-pill[data-filter]').forEach(pill => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      const f = pill.dataset.filter;
      document.querySelectorAll('#brokers-table tbody tr').forEach(row => {
        if (f === 'all')      { row.style.display = ''; return; }
        if (f === 'expiring') { row.style.display = row.dataset.contract === 'expiring' ? '' : 'none'; return; }
        row.style.display = row.dataset.status === f ? '' : 'none';
      });
    });
  });

  // Toggle CSS for material visibility switch
  if (!document.getElementById('mat-toggle-style')) {
    const s = document.createElement('style');
    s.id = 'mat-toggle-style';
    s.textContent = `.mat-toggle{position:relative;display:inline-block;width:32px;height:18px;cursor:pointer}.mat-toggle input{opacity:0;width:0;height:0}.mat-toggle-slider{position:absolute;inset:0;background:var(--muted);border-radius:18px;transition:.2s}.mat-toggle-slider::before{content:'';position:absolute;width:12px;height:12px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s}.mat-toggle input:checked+.mat-toggle-slider{background:var(--green)}.mat-toggle input:checked+.mat-toggle-slider::before{transform:translateX(14px)}`;
    document.head.appendChild(s);
  }

  window.activateBroker = () => showToast('✓ Broker activado', 'var(--green-txt)');

  // ── Material management ───────────────────────────────────────────────
  window.toggleMaterialVisibility = (id, visible) => {
    const m = ADMIN_MATERIALS.find(x => x.id === id);
    if (m) m.visible = visible;
    showToast(visible ? '✓ Recurso visible para brokers' : '· Recurso ocultado', visible ? 'var(--green-txt)' : 'var(--sub)');
  };

  window.deleteMaterial = (id) => {
    if (!confirm('¿Eliminar este recurso? Los brokers ya no podrán descargarlo.')) return;
    ADMIN_MATERIALS = ADMIN_MATERIALS.filter(m => m.id !== id);
    document.querySelector(`#admin-material-table tr[data-mat-id="${id}"]`)?.remove();
    const badge = document.getElementById('mat-count-badge');
    if (badge) badge.textContent = `${ADMIN_MATERIALS.length} recursos`;
    showToast('Recurso eliminado', 'var(--sub)');
  };

  window.openAddMaterialModal  = () => openMatModal(null);
  window.openEditMaterialModal = (id) => openMatModal(ADMIN_MATERIALS.find(m => m.id === id));

  injectMatModal();
}

// ── Material modal (add / edit) ───────────────────────────────────────────
function injectMatModal() {
  if (document.getElementById('mat-admin-modal')) return;

  const overlay = document.createElement('div');
  overlay.id = 'mat-admin-modal';
  overlay.style.cssText = 'display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);align-items:center;justify-content:center';
  overlay.innerHTML = `
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;width:500px;max-width:92vw;max-height:90vh;overflow-y:auto;padding:28px;position:relative">
      <button id="mat-modal-close" style="position:absolute;top:16px;right:16px;background:none;border:none;color:var(--sub);cursor:pointer;font-size:18px;line-height:1">✕</button>
      <h3 id="mat-modal-title" style="font-size:15px;font-weight:600;color:var(--cream);margin-bottom:20px"></h3>

      <div style="display:flex;flex-direction:column;gap:14px">

        <div>
          <label style="font-size:11px;font-weight:500;color:var(--sub);display:block;margin-bottom:6px;letter-spacing:.04em;text-transform:uppercase">Nombre del recurso</label>
          <input id="mat-f-title" type="text" placeholder="Ej. Renders del proyecto"
            style="width:100%;background:var(--bg-card2);border:1px solid var(--border);border-radius:6px;padding:9px 12px;font-size:13px;color:var(--cream);outline:none;box-sizing:border-box"
            onfocus="this.style.borderColor='var(--green)'" onblur="this.style.borderColor='var(--border)'">
        </div>

        <div>
          <label style="font-size:11px;font-weight:500;color:var(--sub);display:block;margin-bottom:6px;letter-spacing:.04em;text-transform:uppercase">Descripción breve</label>
          <input id="mat-f-desc" type="text" placeholder="Ej. 18 imágenes de alta resolución · Aprobadas por Duna"
            style="width:100%;background:var(--bg-card2);border:1px solid var(--border);border-radius:6px;padding:9px 12px;font-size:13px;color:var(--cream);outline:none;box-sizing:border-box"
            onfocus="this.style.borderColor='var(--green)'" onblur="this.style.borderColor='var(--border)'">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label style="font-size:11px;font-weight:500;color:var(--sub);display:block;margin-bottom:6px;letter-spacing:.04em;text-transform:uppercase">Formato</label>
            <select id="mat-f-fmt" style="width:100%;background:var(--bg-card2);border:1px solid var(--border);border-radius:6px;padding:9px 12px;font-size:13px;color:var(--cream);outline:none;cursor:pointer">
              <option>PDF</option><option>ZIP</option><option>MP4</option><option>XLSX</option>
            </select>
          </div>
          <div>
            <label style="font-size:11px;font-weight:500;color:var(--sub);display:block;margin-bottom:6px;letter-spacing:.04em;text-transform:uppercase">Categoría</label>
            <select id="mat-f-cat" style="width:100%;background:var(--bg-card2);border:1px solid var(--border);border-radius:6px;padding:9px 12px;font-size:13px;color:var(--cream);outline:none;cursor:pointer">
              <option>Imágenes</option><option>Planos</option><option>Video</option><option>Finanzas</option><option>Presentación</option><option>Marketing</option><option>Obra</option>
            </select>
          </div>
        </div>

        <div>
          <label style="font-size:11px;font-weight:500;color:var(--sub);display:block;margin-bottom:8px;letter-spacing:.04em;text-transform:uppercase">Ícono</label>
          <div id="icon-picker" style="display:grid;grid-template-columns:repeat(8,1fr);gap:6px">
            ${Object.entries(ICON_DEFS).map(([key, def]) => `
              <div class="icon-opt" data-icon="${key}"
                style="display:flex;flex-direction:column;align-items:center;gap:5px;padding:8px 4px;border-radius:8px;border:1.5px solid var(--border);background:var(--bg-card2);cursor:pointer;transition:border-color .15s,background .15s"
                onmouseover="if(!this.classList.contains('selected')){this.style.borderColor='var(--sub)';this.style.background='var(--bg-card)'}"
                onmouseout="if(!this.classList.contains('selected')){this.style.borderColor='var(--border)';this.style.background='var(--bg-card2)'}">
                <span style="color:${def.color};display:flex">${def.svg(16)}</span>
                <span style="font-size:9px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;text-align:center">${def.label}</span>
              </div>
            `).join('')}
          </div>
        </div>

        <div>
          <label style="font-size:11px;font-weight:500;color:var(--sub);display:block;margin-bottom:6px;letter-spacing:.04em;text-transform:uppercase">Archivo</label>
          <div id="mat-upload-area" style="border:1.5px dashed var(--border);border-radius:8px;padding:24px;text-align:center;cursor:pointer;transition:border-color .15s"
            onclick="document.getElementById('mat-file-input').click()"
            ondragover="event.preventDefault();this.style.borderColor='var(--green)'"
            ondragleave="this.style.borderColor='var(--border)'"
            ondrop="event.preventDefault();handleMatDrop(event)">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--sub)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:24px;height:24px;margin:0 auto 8px;display:block"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <div id="mat-upload-label" style="font-size:12px;color:var(--sub)">Haz clic o arrastra el archivo aquí</div>
            <div style="font-size:10.5px;color:var(--muted);margin-top:4px">PDF, ZIP, MP4, XLSX — máx. 2 GB</div>
          </div>
          <input id="mat-file-input" type="file" style="display:none" onchange="handleMatFileSelect(this)">
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:var(--bg-card2);border-radius:8px">
          <div>
            <div style="font-size:12px;font-weight:500;color:var(--cream)">Visible para brokers activos</div>
            <div style="font-size:11px;color:var(--sub)">El recurso aparecerá en su portal de material</div>
          </div>
          <label class="mat-toggle">
            <input type="checkbox" id="mat-f-visible" checked>
            <span class="mat-toggle-slider"></span>
          </label>
        </div>

      </div>

      <div style="display:flex;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
        <button id="mat-save-btn" style="flex:1;padding:10px;background:var(--green);border:none;border-radius:8px;color:var(--cream);font-size:13px;font-weight:600;cursor:pointer">Guardar recurso</button>
        <button id="mat-cancel-btn" style="padding:10px 18px;background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;color:var(--sub);font-size:13px;cursor:pointer">Cancelar</button>
      </div>
    </div>`;

  document.body.appendChild(overlay);

  overlay.addEventListener('click', e => { if (e.target === overlay) closeMatModal(); });
  document.getElementById('mat-modal-close').addEventListener('click', closeMatModal);
  document.getElementById('mat-cancel-btn').addEventListener('click', closeMatModal);
  document.getElementById('mat-save-btn').addEventListener('click', saveMatResource);

  // Icon picker delegation
  document.getElementById('icon-picker').addEventListener('click', e => {
    const opt = e.target.closest('.icon-opt');
    if (!opt) return;
    selectIcon(opt.dataset.icon);
  });

  window.handleMatFileSelect = (input) => {
    if (input.files[0]) {
      document.getElementById('mat-upload-label').textContent = `✓ ${input.files[0].name}`;
      document.getElementById('mat-upload-label').style.color = 'var(--green-txt)';
    }
  };
  window.handleMatDrop = (e) => {
    const file = e.dataTransfer.files[0];
    if (file) {
      document.getElementById('mat-upload-label').textContent = `✓ ${file.name}`;
      document.getElementById('mat-upload-label').style.color = 'var(--green-txt)';
      document.getElementById('mat-upload-area').style.borderColor = 'var(--border)';
    }
  };
}

let _editingMatId = null;
let _selectedIcon = 'pdf';

function selectIcon(key) {
  _selectedIcon = key;
  document.querySelectorAll('#icon-picker .icon-opt').forEach(el => {
    const active = el.dataset.icon === key;
    el.classList.toggle('selected', active);
    el.style.borderColor = active ? 'var(--green-txt)' : 'var(--border)';
    el.style.background  = active ? 'rgba(74,94,63,.18)' : 'var(--bg-card2)';
  });
}

function openMatModal(existing) {
  _editingMatId = existing ? existing.id : null;
  document.getElementById('mat-modal-title').textContent = existing ? 'Editar recurso' : 'Nuevo recurso';
  document.getElementById('mat-f-title').value     = existing?.title   || '';
  document.getElementById('mat-f-desc').value      = existing?.desc    || '';
  document.getElementById('mat-f-fmt').value       = existing?.fmt     || 'PDF';
  document.getElementById('mat-f-cat').value       = existing?.cat     || 'Imágenes';
  document.getElementById('mat-f-visible').checked = existing ? existing.visible : true;
  document.getElementById('mat-upload-label').textContent = 'Haz clic o arrastra el archivo aquí';
  document.getElementById('mat-upload-label').style.color = 'var(--sub)';
  selectIcon(existing?.icon || 'pdf');
  document.getElementById('mat-admin-modal').style.display = 'flex';
}

function closeMatModal() {
  document.getElementById('mat-admin-modal').style.display = 'none';
}

function saveMatResource() {
  const title   = document.getElementById('mat-f-title').value.trim();
  const desc    = document.getElementById('mat-f-desc').value.trim();
  const fmt     = document.getElementById('mat-f-fmt').value;
  const cat     = document.getElementById('mat-f-cat').value;
  const visible = document.getElementById('mat-f-visible').checked;

  if (!title) {
    document.getElementById('mat-f-title').style.borderColor = 'var(--red)';
    return;
  }

  const iconColor = (ICON_DEFS[_selectedIcon] || ICON_DEFS.pdf).color;

  if (_editingMatId) {
    const m = ADMIN_MATERIALS.find(x => x.id === _editingMatId);
    if (m) { m.title = title; m.desc = desc; m.fmt = fmt; m.cat = cat; m.visible = visible; m.icon = _selectedIcon; m.color = iconColor; }
    const row = document.querySelector(`#admin-material-table tr[data-mat-id="${_editingMatId}"]`);
    if (row) {
      row.cells[0].querySelector('div').style.color = iconColor;
      row.cells[0].querySelector('div').innerHTML   = matIconSm(_selectedIcon);
      row.cells[1].innerHTML = `<div class="cell-name">${title}</div><div class="cell-sub">${desc}</div>`;
      row.cells[2].innerHTML = `<span class="badge badge-gray" style="font-size:10px">${cat}</span>`;
      row.cells[3].innerHTML = `<span class="badge ${FMT_CLS[fmt]||'badge-gray'}" style="font-size:10px">${fmt}</span>`;
    }
    showToast('✓ Recurso actualizado', 'var(--green-txt)');
  } else {
    const newMat = { id: nextMatId++, title, desc, fmt, cat, size:'—', updated:'Hoy', visible, icon: _selectedIcon, color: iconColor };
    ADMIN_MATERIALS.push(newMat);
    const tbody = document.querySelector('#admin-material-table tbody');
    const tr = document.createElement('tr');
    tr.dataset.matId = newMat.id;
    tr.innerHTML = `
      <td style="padding:10px 8px 10px 16px"><div style="width:30px;height:30px;border-radius:6px;background:var(--bg-card2);display:flex;align-items:center;justify-content:center;color:${newMat.color}">${matIconSm(newMat.icon)}</div></td>
      <td><div class="cell-name">${title}</div><div class="cell-sub">${desc}</div></td>
      <td><span class="badge badge-gray" style="font-size:10px">${cat}</span></td>
      <td><span class="badge ${FMT_CLS[fmt]||'badge-gray'}" style="font-size:10px">${fmt}</span></td>
      <td><span style="font-size:11px;color:var(--sub)">—</span></td>
      <td><span style="font-size:11px;color:var(--sub)">Hoy</span></td>
      <td><label class="mat-toggle"><input type="checkbox" ${visible?'checked':''} onchange="toggleMaterialVisibility(${newMat.id},this.checked)"><span class="mat-toggle-slider"></span></label></td>
      <td><div style="display:flex;gap:8px"><span class="cell-link" onclick="openEditMaterialModal(${newMat.id})">Editar</span><span class="cell-link" style="color:var(--red)" onclick="deleteMaterial(${newMat.id})">Eliminar</span></div></td>`;
    tbody.appendChild(tr);
    const badge = document.getElementById('mat-count-badge');
    if (badge) badge.textContent = `${ADMIN_MATERIALS.length} recursos`;
    showToast('✓ Recurso agregado', 'var(--green-txt)');
  }

  closeMatModal();
}

function showToast(msg, color = 'var(--green-txt)') {
  const e = document.getElementById('duna-toast');
  if (e) e.remove();
  const t = document.createElement('div');
  t.id = 'duna-toast';
  t.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:10px 16px;font-size:12px;font-weight:500;color:${color};box-shadow:var(--shadow)`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2500);
}
