export const meta = { title: 'Material de ventas', breadcrumb: 'Portal Broker · Recursos de venta' };

// ── Static material list (fed by admin) ──────────────────────────────────
const MATERIALS = [
  { id:1, title:'Renders del proyecto',    desc:'18 imágenes de alta resolución, aprobadas por Duna Development. Solo uso informativo con clientes.',   fmt:'ZIP',  size:'248 MB',  updated:'Abr 2026', icon:'img',  color:'var(--blue)'      },
  { id:2, title:'Planos de unidades',      desc:'Planos por tipología de unidad, versión oficial del proyecto. No modificar ni redistribuir.',           fmt:'PDF',  size:'12.4 MB', updated:'Mar 2026', icon:'pdf',  color:'var(--red)'       },
  { id:3, title:'Video tour 360°',         desc:'Recorrido virtual de 2 min 34s en alta resolución. Versión en español e inglés.',                       fmt:'MP4',  size:'1.8 GB',  updated:'Abr 2026', icon:'mp4',  color:'var(--orange)'    },
  { id:4, title:'Ficha de inversión',      desc:'ROI proyectado, plan de pagos completo y comparativa de mercado. Actualizado mayo 2026.',               fmt:'PDF',  size:'3.2 MB',  updated:'May 2026', icon:'pdf',  color:'var(--red)'       },
  { id:5, title:'Brochure oficial',        desc:'Presentación completa del proyecto en 24 páginas. Disponible en español, inglés y francés.',            fmt:'PDF',  size:'18.6 MB', updated:'Mar 2026', icon:'pdf',  color:'var(--red)'       },
  { id:6, title:'Kit redes sociales',      desc:'Pack de stories y posts con los formatos y medidas aprobadas por Duna. Versiones ES/EN.',               fmt:'ZIP',  size:'156 MB',  updated:'Abr 2026', icon:'img',  color:'var(--blue)'      },
  { id:7, title:'Fotos avance de obra',    desc:'12 fotografías verificadas del avance de construcción. Capturadas en abril 2026.',                      fmt:'ZIP',  size:'84 MB',   updated:'Abr 2026', icon:'img',  color:'var(--blue)'      },
  { id:8, title:'Calculadora de comisión', desc:'Excel interactivo precargado con tus tasas de comisión. Calcula ingresos estimados por unidad.',        fmt:'XLSX', size:'2.1 MB',  updated:'Ene 2026', icon:'xlsx', color:'var(--green-txt)' },
];

const FMT_CLS = { PDF:'badge-red', ZIP:'badge-blue', MP4:'badge-orange', XLSX:'badge-green' };

// ── Icons ─────────────────────────────────────────────────────────────────
function icon(type, size = 20) {
  const s = `width:${size}px;height:${size}px`;
  const base = `fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"`;
  const paths = {
    pdf:  `<svg viewBox="0 0 24 24" ${base} style="${s}"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8L14 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`,
    img:  `<svg viewBox="0 0 24 24" ${base} style="${s}"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`,
    mp4:  `<svg viewBox="0 0 24 24" ${base} style="${s}"><rect x="2" y="5" width="15" height="14" rx="2"/><path d="M17 9l5-3v12l-5-3V9z"/></svg>`,
    xlsx: `<svg viewBox="0 0 24 24" ${base} style="${s}"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8L14 2z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12l4 4m0-4l-4 4"/></svg>`,
    down: `<svg viewBox="0 0 24 24" ${base} style="${s}"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`,
    warn: `<svg viewBox="0 0 24 24" ${base} style="${s}"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    x:    `<svg viewBox="0 0 24 24" ${base} style="${s}"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
  };
  return paths[type] || paths.pdf;
}

// ── Render ────────────────────────────────────────────────────────────────
export function render() {
  return `
<div class="view-container">

  <div class="view-header" style="margin-bottom:6px">
    <h1 class="view-title">Material de ventas</h1>
  </div>
  <p style="font-size:12px;color:var(--sub);margin-bottom:20px;max-width:560px;line-height:1.6">
    Solo puedes usar material aprobado por Duna. No está permitido modificar renders, planos ni material publicitario (Art. 1 del Acuerdo).
  </p>

  <!-- Aviso legal -->
  <div style="display:flex;gap:10px;align-items:flex-start;padding:12px 16px;background:rgba(201,124,64,.07);border:1px solid rgba(201,124,64,.22);border-radius:8px;margin-bottom:28px">
    <span style="color:var(--orange);flex-shrink:0;margin-top:1px">${icon('warn', 15)}</span>
    <p style="font-size:11px;color:var(--sub);line-height:1.55;margin:0">
      Todo el material está registrado y marcado digitalmente. Su uso fuera de los términos del Acuerdo puede dar lugar a la terminación anticipada del contrato.
    </p>
  </div>

  <!-- Grid de recursos -->
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(256px,1fr));gap:14px">
    ${MATERIALS.map(m => `
      <div
        style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:20px;display:flex;flex-direction:column;gap:14px;cursor:pointer;transition:border-color .18s,box-shadow .18s"
        onmouseover="this.style.borderColor='var(--green-txt)';this.style.boxShadow='0 0 0 1px var(--green-txt)20'"
        onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='none'"
        onclick="openMatModal(${m.id})">

        <!-- Top row: icon + format badge -->
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
          <div style="width:42px;height:42px;border-radius:9px;background:var(--bg-card2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:${m.color}">
            ${icon(m.icon, 20)}
          </div>
          <span class="badge ${FMT_CLS[m.fmt]}" style="font-size:10px;margin-top:2px">${m.fmt}</span>
        </div>

        <!-- Title + desc -->
        <div>
          <div style="font-size:13px;font-weight:600;color:var(--cream);margin-bottom:5px;line-height:1.3">${m.title}</div>
          <div style="font-size:11px;color:var(--sub);line-height:1.5">${m.desc.split('.')[0]}.</div>
        </div>

        <!-- Footer: size + date + download hint -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:10px;border-top:1px solid var(--border)">
          <span style="font-size:10.5px;color:var(--muted)">${m.size} &middot; ${m.updated}</span>
          <span style="color:var(--green-txt);opacity:.7">${icon('down', 14)}</span>
        </div>

      </div>
    `).join('')}
  </div>

</div>

<!-- Modal de recurso -->
<div id="mat-modal-overlay" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);align-items:center;justify-content:center">
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;width:460px;max-width:92vw;padding:28px;position:relative">
    <button id="mat-close-btn" style="position:absolute;top:14px;right:14px;background:none;border:none;color:var(--sub);cursor:pointer;display:flex;align-items:center;justify-content:center;padding:4px;border-radius:4px;transition:color .15s" onmouseover="this.style.color='var(--cream)'" onmouseout="this.style.color='var(--sub)'">${icon('x', 16)}</button>
    <div id="mat-modal-body"></div>
  </div>
</div>
`;
}

// ── Init ──────────────────────────────────────────────────────────────────
export function init() {
  window.openMatModal = (id) => {
    const m = MATERIALS.find(x => x.id === id);
    if (!m) return;
    document.getElementById('mat-modal-body').innerHTML = `
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
        <div style="width:50px;height:50px;border-radius:10px;background:var(--bg-card2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:${m.color};flex-shrink:0">
          ${icon(m.icon, 22)}
        </div>
        <div>
          <div style="font-size:15px;font-weight:600;color:var(--cream);margin-bottom:4px">${m.title}</div>
          <div style="display:flex;gap:8px;align-items:center">
            <span class="badge ${FMT_CLS[m.fmt]}" style="font-size:10px">${m.fmt}</span>
            <span style="font-size:11px;color:var(--muted)">${m.size}</span>
            <span style="font-size:11px;color:var(--muted)">&middot; Actualizado ${m.updated}</span>
          </div>
        </div>
      </div>

      <p style="font-size:12px;color:var(--sub);line-height:1.65;margin-bottom:18px">${m.desc}</p>

      <div style="display:flex;gap:10px;align-items:flex-start;padding:10px 14px;background:rgba(201,124,64,.06);border:1px solid rgba(201,124,64,.18);border-radius:8px;margin-bottom:22px">
        <span style="color:var(--orange);flex-shrink:0">${icon('warn', 14)}</span>
        <p style="font-size:11px;color:var(--sub);margin:0;line-height:1.5">
          Uso exclusivo para presentación a clientes. Prohibida su reproducción, modificación o distribución fuera del Acuerdo de Colaboración.
        </p>
      </div>

      <div style="display:flex;gap:10px">
        <button onclick="simulateMatDownload('${m.title}')"
          style="flex:1;padding:11px;background:var(--green);border:none;border-radius:8px;color:var(--cream);font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .15s"
          onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
          ${icon('down', 14)}
          Descargar
        </button>
        <button id="mat-close-inner" style="padding:11px 18px;background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;color:var(--sub);font-size:13px;cursor:pointer">
          Cerrar
        </button>
      </div>
    `;
    document.getElementById('mat-modal-overlay').style.display = 'flex';
    document.getElementById('mat-close-inner').addEventListener('click', closeMatModal);
  };

  document.getElementById('mat-close-btn').addEventListener('click', closeMatModal);
  document.getElementById('mat-modal-overlay').addEventListener('click', e => {
    if (e.target === document.getElementById('mat-modal-overlay')) closeMatModal();
  });

  window.simulateMatDownload = (title) => {
    closeMatModal();
    showToast(`↓ Descargando "${title}"…`);
  };
}

function closeMatModal() {
  document.getElementById('mat-modal-overlay').style.display = 'none';
}

function showToast(msg) {
  const existing = document.getElementById('duna-toast');
  if (existing) existing.remove();
  const t = document.createElement('div');
  t.id = 'duna-toast';
  t.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:10px 16px;font-size:12px;font-weight:500;color:var(--green-txt);box-shadow:var(--shadow)`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2800);
}
