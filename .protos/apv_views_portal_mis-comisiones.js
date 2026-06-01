export const meta = { title: 'Mis Comisiones', breadcrumb: 'Portal Broker · Mis Comisiones' };

// ── helpers ──────────────────────────────────────────────────────────────────
const MONTHS = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const fmtDate = iso => { const [y,m,d] = iso.split('-').map(Number); return `${d} ${MONTHS[m-1]} ${y}`; };
const toAmt   = str => parseFloat(str.replace(/[$,]/g, ''));
const fmt     = n   => '$' + n.toLocaleString('en-US', { minimumFractionDigits: 0 });
// ─────────────────────────────────────────────────────────────────────────────

const COMMISSIONS = [
  { id: 1, client: 'Carlos Méndez',  unit: 'U-111', concept: 'Cuota inicial — Reserva',   base: '$21,550',  commission: '$431',   date: '2026-03-15', status: 'paid'    },
  { id: 2, client: 'Ana García',     unit: 'U-205', concept: 'Cuota inicial — Reserva',   base: '$19,450',  commission: '$389',   date: '2026-03-22', status: 'paid'    },
  { id: 3, client: 'Luis Pérez',     unit: 'U-308', concept: 'Cuota 01/24 — Plan pagos',  base: '$17,291',  commission: '$346',   date: '2026-04-10', status: 'paid'    },
  { id: 4, client: 'Luis Pérez',     unit: 'U-308', concept: 'Cuota 02/24 — Plan pagos',  base: '$17,291',  commission: '$346',   date: '2026-05-10', status: 'pending' },
  { id: 5, client: 'María López',    unit: 'U-114', concept: 'Cuota 05/24 — Plan pagos',  base: '$17,958',  commission: '$359',   date: '2026-04-28', status: 'paid'    },
  { id: 6, client: 'Roberto Silva',  unit: 'U-220', concept: 'Cuota 01/24 — Plan pagos',  base: '$16,458',  commission: '$329',   date: '2026-04-01', status: 'overdue' },
  { id: 7, client: 'Sophie Martin',  unit: 'U-312', concept: 'Cuota inicial — Reserva',   base: '$89,000',  commission: '$1,780', date: '2026-04-12', status: 'paid'    },
  { id: 8, client: 'James Wilson',   unit: 'U-401', concept: 'Pago total — Contrato',     base: '$480,000', commission: '$9,600', date: '2025-12-01', status: 'paid'    },
];

const STATUS = {
  paid:    { label: 'Pagada',    badge: 'green',  color: 'var(--green-txt)' },
  pending: { label: 'Pendiente', badge: 'orange', color: 'var(--orange)'   },
  overdue: { label: 'Vencida',   badge: 'red',    color: 'var(--red)'      },
};

export function render(data) {
  const broker = data.brokers.find(b => b.id === 1);

  const paid    = COMMISSIONS.filter(c => c.status === 'paid');
  const pending = COMMISSIONS.filter(c => c.status === 'pending');
  const overdue = COMMISSIONS.filter(c => c.status === 'overdue');

  const paidTotal  = paid.reduce((s, c)    => s + toAmt(c.commission), 0);
  const pendTotal  = pending.reduce((s, c) => s + toAmt(c.commission), 0);
  const overTotal  = overdue.reduce((s, c) => s + toAmt(c.commission), 0);
  const grandTotal = paidTotal + pendTotal + overTotal;

  const kpis = [
    { label: 'Cobradas',          val: fmt(paidTotal),  sub: `${paid.length} liquidaciones`,  color: 'var(--green-txt)', accent: 'var(--green-txt)' },
    { label: 'Por cobrar',        val: fmt(pendTotal),  sub: `${pending.length} en proceso`,   color: 'var(--orange)',    accent: 'var(--orange)'    },
    { label: 'Vencidas',          val: fmt(overTotal),  sub: `${overdue.length} en mora`,      color: 'var(--red)',       accent: 'var(--red)'       },
    { label: 'Total acumulado',   val: fmt(grandTotal), sub: `${COMMISSIONS.length} pagos`,    color: 'var(--cream-dim)', accent: 'var(--border)'    },
  ];

  const actionable = [...overdue, ...pending].sort((a, b) => new Date(a.date) - new Date(b.date));

  return `
<div class="view-container">

  <div class="view-header">
    <h1 class="view-title">Mis Comisiones</h1>
    <span style="font-size:11px;color:var(--sub)">JR Real Estate · Tasa 2%</span>
    <div class="view-actions">
      <button class="btn btn-ghost btn-sm">⬇ Exportar</button>
    </div>
  </div>

  <!-- KPIs -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
    ${kpis.map(k => `
      <div style="
        background:var(--bg-card);border:1px solid var(--border);
        border-top:3px solid ${k.accent};
        border-radius:8px;padding:16px 20px;
      ">
        <div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px">${k.label}</div>
        <div style="font-size:22px;font-weight:700;color:${k.color};line-height:1;margin-bottom:6px">${k.val}</div>
        <div style="font-size:11px;color:var(--muted)">${k.sub}</div>
      </div>
    `).join('')}
  </div>

  <!-- Alerta acciones pendientes -->
  ${actionable.length > 0 ? `
  <div style="margin-bottom:16px;padding:12px 16px;background:rgba(201,124,64,.07);border:1px solid rgba(201,124,64,.18);border-radius:8px">
    <div style="font-size:12px;font-weight:600;color:var(--orange);margin-bottom:8px">
      ${overdue.length > 0 ? `${overdue.length} pago vencido · ` : ''}${pending.length > 0 ? `${pending.length} pendiente de cobro` : ''}
    </div>
    <div style="display:flex;flex-direction:column;gap:6px">
      ${actionable.map(c => `
        <div style="display:flex;align-items:center;gap:12px;font-size:11px">
          <span class="badge badge-${STATUS[c.status].badge}" style="flex-shrink:0">${STATUS[c.status].label}</span>
          <span style="color:var(--cream-dim);font-weight:500">${c.commission}</span>
          <span style="color:var(--sub)">${c.client} · ${c.concept}</span>
          <span style="color:var(--muted);margin-left:auto">Vence ${fmtDate(c.date)}</span>
        </div>
      `).join('')}
    </div>
  </div>` : ''}

  <!-- Filtros -->
  <div class="filter-bar" style="margin-bottom:12px">
    <span class="filter-pill active" data-filter="all">Todas <span class="count">${COMMISSIONS.length}</span></span>
    <span class="filter-pill" data-filter="paid">Cobradas <span class="count">${paid.length}</span></span>
    <span class="filter-pill" data-filter="pending">Pendientes <span class="count">${pending.length}</span></span>
    <span class="filter-pill" data-filter="overdue">Vencidas <span class="count">${overdue.length}</span></span>
  </div>

  <!-- Tabla -->
  <div class="panel">
    <table class="data-table" id="commissions-table">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Concepto</th>
          <th>Comisión</th>
          <th>Fecha</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        ${COMMISSIONS.map(c => {
          const s = STATUS[c.status];
          const rowBg = c.status === 'overdue'  ? 'background:rgba(184,64,64,.05)'
                      : c.status === 'pending'  ? 'background:rgba(201,124,64,.04)'
                      : '';
          return `
          <tr data-status="${c.status}" style="${rowBg}">
            <td>
              <div class="cell-name">${c.client}</div>
              <div class="cell-sub">${c.unit}</div>
            </td>
            <td>
              <div style="font-size:12px;color:var(--cream-dim)">${c.concept}</div>
              <div style="font-size:10px;color:var(--muted);margin-top:2px">base ${c.base}</div>
            </td>
            <td>
              <div style="font-size:15px;font-weight:700;color:${s.color}">${c.commission}</div>
            </td>
            <td>
              <span style="font-size:11px;color:var(--sub)">${fmtDate(c.date)}</span>
            </td>
            <td>
              <span class="badge badge-${s.badge}">${s.label}</span>
            </td>
          </tr>`;
        }).join('')}
      </tbody>
    </table>
  </div>

</div>`;
}

export function init() {
  document.querySelectorAll('.filter-pill[data-filter]').forEach(pill => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      const f = pill.dataset.filter;
      document.querySelectorAll('#commissions-table tbody tr').forEach(row => {
        row.style.display = (f === 'all' || row.dataset.status === f) ? '' : 'none';
      });
    });
  });
}
