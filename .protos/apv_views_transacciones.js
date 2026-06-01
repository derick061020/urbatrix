export const meta = { title: 'Transacciones', breadcrumb: 'Gestión · Transacciones y pagos' };

export function render(data) {
  const { transactions } = data;

  const confirmed = transactions.filter(t => t.status === 'confirmed');
  const pending   = transactions.filter(t => t.status === 'pending');
  const overdue   = transactions.filter(t => t.status === 'overdue');

  const sumAmt = arr => arr.reduce((acc, t) => {
    const n = parseFloat(t.amount.replace(/[$,]/g, ''));
    return acc + n;
  }, 0);

  const fmt = n => '$' + n.toLocaleString('en-US', { minimumFractionDigits: 0 });

  const methodIcon = { 'Wire Transfer': '🏦', 'ACH': '⇄', 'Stripe': '💳' };

  return `
<div class="view-container">
  <div class="view-header">
    <h1 class="view-title">Transacciones</h1>
    <span style="font-size:11px;color:var(--sub)">${transactions.length} movimientos</span>
    <div class="view-actions">
      <button class="btn btn-ghost btn-sm" onclick="openExportModal('Transacciones')">🔒 Exportar</button>
      <button class="btn btn-primary btn-sm" onclick="openRegistrarPagoModal()">+ Registrar pago</button>
    </div>
  </div>

  <!-- KPIs financieros -->
  <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-left:3px solid var(--green-txt);border-radius:8px;padding:14px 20px;min-width:180px">
      <div style="font-size:11px;color:var(--sub);margin-bottom:4px">Total cobrado</div>
      <div style="font-size:22px;font-weight:600;color:var(--green-txt);font-family:'Inter',sans-serif">${fmt(sumAmt(confirmed))}</div>
      <div style="font-size:11px;color:var(--muted);margin-top:2px">${confirmed.length} transacciones</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border);border-left:3px solid var(--orange);border-radius:8px;padding:14px 20px;min-width:180px">
      <div style="font-size:11px;color:var(--sub);margin-bottom:4px">Pendiente de cobro</div>
      <div style="font-size:22px;font-weight:600;color:var(--orange);font-family:'Inter',sans-serif">${fmt(sumAmt(pending))}</div>
      <div style="font-size:11px;color:var(--muted);margin-top:2px">${pending.length} pagos próximos</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border);border-left:3px solid var(--red);border-radius:8px;padding:14px 20px;min-width:180px">
      <div style="font-size:11px;color:var(--sub);margin-bottom:4px">Pagos vencidos</div>
      <div style="font-size:22px;font-weight:600;color:var(--red);font-family:'Inter',sans-serif">${fmt(sumAmt(overdue))}</div>
      <div style="font-size:11px;color:var(--muted);margin-top:2px">${overdue.length} en mora</div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="filter-bar">
    <span class="filter-pill active" data-filter="all">Todos <span class="count">${transactions.length}</span></span>
    <span class="filter-pill" data-filter="confirmed">Confirmados <span class="count">${confirmed.length}</span></span>
    <span class="filter-pill" data-filter="pending">Pendientes <span class="count">${pending.length}</span></span>
    <span class="filter-pill" data-filter="overdue">Vencidos <span class="count">${overdue.length}</span></span>
  </div>

  <!-- Tabla -->
  <div class="panel">
    <table class="data-table" id="tx-table">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Unidad</th>
          <th>Concepto</th>
          <th>Monto</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th>Método</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        ${transactions.map(t => `
          <tr data-status="${t.status}">
            <td><div class="cell-name">${t.client}</div></td>
            <td><span style="font-size:12px;color:var(--sub)">${t.unit}</span></td>
            <td>
              <div style="font-size:12px;color:var(--cream-dim);max-width:220px">${t.concept}</div>
            </td>
            <td>
              <div class="cell-price" style="${t.status === 'overdue' ? 'color:var(--red)' : t.status === 'pending' ? 'color:var(--orange)' : ''}">${t.amount}</div>
            </td>
            <td><span class="badge badge-${t.statusCls}">${t.statusLabel}</span></td>
            <td><span style="font-size:11px;color:var(--sub)">${t.date}</span></td>
            <td>
              <span style="font-size:11px;color:var(--muted)">${methodIcon[t.method] || ''} ${t.method}</span>
            </td>
            <td>
              <div style="display:flex;gap:8px">
                <span class="cell-link" onclick="openTxModal(${t.id})">Ver</span>
                ${t.status === 'overdue' ? `<span class="cell-link" style="color:var(--orange)" onclick="openTxModal(${t.id})">Recordar</span>` : ''}
              </div>
            </td>
          </tr>
        `).join('')}
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
      document.querySelectorAll('#tx-table tbody tr').forEach(row => {
        row.style.display = (f === 'all' || row.dataset.status === f) ? '' : 'none';
      });
    });
  });
}
