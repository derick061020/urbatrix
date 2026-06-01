export const meta = { title: 'Mi Contrato', breadcrumb: 'Portal Broker · Mi Contrato' };

// ── helpers ──────────────────────────────────────────────────────────────────
const MONTHS = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

function fmtDate(iso) {
  const [y, m, d] = iso.split('-').map(Number);
  return `${d} ${MONTHS[m - 1]} ${y}`;
}

function nextPayDate(fromIso) {
  const ref = new Date(fromIso);
  const pay = ref.getDate() < 15
    ? new Date(ref.getFullYear(), ref.getMonth(), 15)
    : new Date(ref.getFullYear(), ref.getMonth() + 1, 15);
  return `${pay.getDate()} ${MONTHS[pay.getMonth()]} ${pay.getFullYear()}`;
}
// ─────────────────────────────────────────────────────────────────────────────

export function render(data) {
  const broker = data.brokers.find(b => b.id === 1);

  const REF_DATE = '2026-05-08';

  const terms = [
    { label: 'Renovación',          val: 'Automática anual · aviso 30 días' },
    { label: 'Frecuencia de pago',  val: 'Mensual · días 15 de cada mes' },
    { label: 'Proyectos incluidos', val: 'Makai Residences, Naviva Residences' },
    { label: 'Exclusividad',        val: 'No exclusivo · mercado internacional' },
    { label: 'Materiales de venta', val: 'Acceso a brochures y renders digitales' },
    { label: 'NDA',                 val: `Firmado el ${fmtDate('2026-01-10')}` },
  ];

  const contractDocs = [
    { name: 'Contrato de colaboración v2.1',     type: 'Contrato', date: '2026-01-15', status: 'signed',  size: '2.8 MB' },
    { name: 'Anexo A — Escala de comisiones',    type: 'Anexo',    date: '2026-01-15', status: 'signed',  size: '340 KB' },
    { name: 'NDA — Acuerdo de confidencialidad', type: 'Legal',    date: '2026-01-10', status: 'signed',  size: '1.1 MB' },
    { name: 'Addendum — Naviva Residences',      type: 'Anexo',    date: '2026-03-01', status: 'pending', size: '210 KB' },
  ];

  const daysUntilExpiry = Math.round(
    (new Date(broker.contractExpiry) - new Date(REF_DATE)) / (1000 * 60 * 60 * 24)
  );
  const expiryColor  = daysUntilExpiry < 60 ? 'var(--orange)' : 'var(--cream-dim)';
  const pendingCount = contractDocs.filter(d => d.status === 'pending').length;
  const defaultTab   = pendingCount > 0 ? 'documentos' : 'terminos';

  const tabs = [
    { id: 'terminos',   label: 'Términos'     },
    { id: 'documentos', label: 'Documentos', badge: pendingCount || null },
    { id: 'ejecutivo',  label: 'Tu ejecutivo' },
  ];

  const strip = [
    { label: 'Próximo pago',   val: nextPayDate(REF_DATE),       color: 'var(--green-txt)' },
    { label: 'Vencimiento',    val: fmtDate(broker.contractExpiry), color: expiryColor     },
    { label: 'Días restantes', val: daysUntilExpiry, sub: 'días', color: expiryColor       },
  ];

  return `
<div class="view-container">

  <!-- ── Hero ───────────────────────────────────────────────────────── -->
  <div style="
    margin:-20px -24px 28px;
    border-top:3px solid var(--green);
    border-bottom:1px solid var(--border);
    background:linear-gradient(160deg,var(--bg-card) 50%,#181d16 100%);
  ">

    <!-- Identity + commission -->
    <div style="padding:28px 32px;display:grid;grid-template-columns:1fr auto;gap:40px;align-items:center">
      <div>
        <div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.12em;margin-bottom:12px">
          Contrato de colaboración · #BR-2026-001
        </div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:500;color:var(--cream);line-height:1.2;margin-bottom:14px">
          JR Real Estate
          <span style="color:var(--green-txt);font-weight:300;margin:0 6px">×</span>
          Duna Development Group
        </div>
        <div style="display:flex;align-items:center;gap:10px">
          <span class="badge badge-green" style="font-size:11px;padding:4px 10px">Contrato activo</span>
          <span style="font-size:11px;color:var(--muted)">Firmado el ${fmtDate('2026-01-15')}</span>
        </div>
      </div>

      <div style="text-align:right;padding-right:4px">
        <div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px">Tu comisión</div>
        <div style="font-size:60px;font-weight:700;color:var(--green-txt);line-height:1;letter-spacing:-.02em">2%</div>
        <div style="font-size:11px;color:var(--muted);margin-top:6px">sobre pagos confirmados</div>
      </div>
    </div>

    <!-- Metrics strip: dates relevantes, no históricas -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--border)">
      ${strip.map((m, i) => `
        <div style="padding:14px 24px;${i < 2 ? 'border-right:1px solid var(--border)' : ''}">
          <div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">${m.label}</div>
          <div style="display:flex;align-items:baseline;gap:5px">
            <span style="font-size:16px;font-weight:600;color:${m.color}">${m.val}</span>
            ${m.sub ? `<span style="font-size:11px;color:var(--muted)">${m.sub}</span>` : ''}
          </div>
        </div>
      `).join('')}
    </div>

    ${daysUntilExpiry < 60 ? `
    <div style="margin:0 24px 20px;padding:11px 16px;background:rgba(201,124,64,.07);border:1px solid rgba(201,124,64,.18);border-radius:8px;display:flex;align-items:center;gap:12px">
      <span style="font-size:16px;flex-shrink:0">⚠</span>
      <div style="flex:1">
        <span style="font-size:12px;font-weight:600;color:var(--orange)">Tu contrato vence en ${daysUntilExpiry} días.</span>
        <span style="font-size:11px;color:var(--sub);margin-left:6px">Contacta a tu ejecutivo para gestionar la renovación.</span>
      </div>
      <button class="btn btn-ghost btn-sm" onclick="window._mcTab('ejecutivo')" style="border-color:var(--orange);color:var(--orange);flex-shrink:0">Contactar →</button>
    </div>` : ''}

  </div>
  <!-- ── /Hero ──────────────────────────────────────────────────────── -->


  <!-- ── Tabs ──────────────────────────────────────────────────────── -->
  <div style="max-width:820px;margin:0 auto">

    <div style="display:flex;border-bottom:1px solid var(--border);margin-bottom:20px">
      ${tabs.map(t => `
        <button
          id="mc-tab-btn-${t.id}"
          onclick="window._mcTab('${t.id}')"
          style="
            padding:9px 18px;background:none;border:none;font-family:inherit;cursor:pointer;
            white-space:nowrap;margin-bottom:-1px;display:flex;align-items:center;gap:7px;
            transition:color .12s;
            border-bottom:2px solid ${t.id === defaultTab ? 'var(--green-txt)' : 'transparent'};
            color:${t.id === defaultTab ? 'var(--cream-dim)' : 'var(--sub)'};
            font-size:13px;font-weight:${t.id === defaultTab ? '600' : '400'};
          ">
          ${t.label}
          ${t.badge ? `<span style="background:var(--orange);color:#fff;border-radius:10px;font-size:9px;padding:1px 6px;font-weight:700;line-height:16px">${t.badge}</span>` : ''}
        </button>
      `).join('')}
    </div>


    <!-- ── Tab: Términos ───────────────────────────── -->
    <div id="mc-panel-terminos" style="display:${defaultTab === 'terminos' ? 'block' : 'none'}">
      <div class="panel">
        <div style="display:grid;grid-template-columns:1fr 1fr">
          ${terms.map((t, i) => `
            <div style="
              padding:16px 20px;
              ${i < terms.length - 2 ? 'border-bottom:1px solid var(--border);' : ''}
              ${i % 2 === 0 ? 'border-right:1px solid var(--border);' : ''}
            ">
              <div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px">${t.label}</div>
              <div style="font-size:13px;color:var(--cream-dim);font-weight:500;line-height:1.35">${t.val}</div>
            </div>
          `).join('')}
        </div>
      </div>
    </div>


    <!-- ── Tab: Documentos ─────────────────────────── -->
    <div id="mc-panel-documentos" style="display:${defaultTab === 'documentos' ? 'block' : 'none'}">

      ${pendingCount > 0 ? `
      <div style="margin-bottom:12px;padding:12px 16px;background:rgba(201,124,64,.07);border:1px solid rgba(201,124,64,.18);border-radius:8px;display:flex;align-items:center;gap:10px">
        <span style="font-size:15px;flex-shrink:0">✍</span>
        <div style="flex:1">
          <span style="font-size:12px;font-weight:600;color:var(--orange)">
            ${pendingCount} documento${pendingCount !== 1 ? 's' : ''} pendiente${pendingCount !== 1 ? 's' : ''} de firma.
          </span>
          <span style="font-size:11px;color:var(--sub);margin-left:6px">
            ${pendingCount !== 1 ? 'Fírmalos' : 'Fírmalo'} para que ${pendingCount !== 1 ? 'entren' : 'entre'} en vigor.
          </span>
        </div>
      </div>` : ''}

      <div class="panel">
        <table class="data-table">
          <thead>
            <tr>
              <th>Documento</th>
              <th>Tipo</th>
              <th>Fecha</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            ${contractDocs.map(d => `
              <tr style="${d.status === 'pending' ? 'background:rgba(201,124,64,.04)' : ''}">
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <div style="
                      width:32px;height:32px;border-radius:6px;flex-shrink:0;
                      background:${d.status === 'pending' ? 'rgba(201,124,64,.12)' : 'var(--green-lite)'};
                      display:flex;align-items:center;justify-content:center;font-size:14px;
                    ">📄</div>
                    <div>
                      <div class="cell-name" style="${d.status === 'pending' ? 'color:var(--cream)' : ''}">${d.name}</div>
                      ${d.status === 'pending' ? `<div style="font-size:10px;color:var(--orange);margin-top:2px;font-weight:500">Requiere tu firma</div>` : ''}
                    </div>
                  </div>
                </td>
                <td><span style="font-size:11px;color:var(--sub)">${d.type}</span></td>
                <td><span style="font-size:11px;color:var(--sub)">${fmtDate(d.date)}</span></td>
                <td>
                  <span class="badge badge-${d.status === 'signed' ? 'green' : 'orange'}">
                    ${d.status === 'signed' ? 'Firmado' : 'Por firmar'}
                  </span>
                </td>
                <td>
                  <div style="display:flex;gap:8px;justify-content:flex-end">
                    ${d.status === 'pending'
                      ? `<button class="btn btn-ghost btn-sm" style="border-color:var(--orange);color:var(--orange)">✍ Firmar</button>`
                      : ''}
                    <span class="cell-link" style="color:var(--sub)">⬇ Descargar</span>
                  </div>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    </div>


    <!-- ── Tab: Tu ejecutivo ───────────────────────── -->
    <div id="mc-panel-ejecutivo" style="display:${defaultTab === 'ejecutivo' ? 'block' : 'none'}">
      <div class="panel">

        <div style="padding:24px;display:flex;align-items:center;gap:20px;border-bottom:1px solid var(--border)">
          <div style="
            width:52px;height:52px;border-radius:50%;flex-shrink:0;
            background:var(--green-lite);border:2px solid var(--green);
            display:flex;align-items:center;justify-content:center;font-size:22px;
          ">👤</div>
          <div style="flex:1">
            <div style="font-size:15px;font-weight:600;color:var(--cream);margin-bottom:3px">Valentina Reyes</div>
            <div style="font-size:12px;color:var(--sub)">Ejecutiva de cuentas · Duna Development Group</div>
          </div>
        </div>

        <div style="padding:20px 24px;display:flex;flex-wrap:wrap;gap:10px;border-bottom:1px solid var(--border)">
          <a href="https://wa.me/18095550000" target="_blank" rel="noopener"
             style="display:flex;align-items:center;gap:7px;background:#25d366;color:#fff;padding:9px 18px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:600">
            💬 WhatsApp
          </a>
          <a href="mailto:brokers@dunacapecana.com"
             style="display:flex;align-items:center;gap:7px;background:var(--green);color:#fff;padding:9px 18px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:600">
            ✉ Email
          </a>
          <a href="tel:+18095550100"
             style="display:flex;align-items:center;gap:7px;background:var(--bg-card2);border:1px solid var(--border);color:var(--cream-dim);padding:9px 18px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:500">
            📞 +1 (809) 555-0100
          </a>
        </div>

        <div style="padding:16px 24px">
          <div style="font-size:11px;color:var(--sub);line-height:1.7">
            Valentina es tu punto de contacto para cualquier consulta sobre tu contrato, renovaciones y operativa con Duna. Horario de atención: lunes a viernes, 9:00–18:00 (hora RD).
          </div>
        </div>

      </div>
    </div>


  </div>
  <!-- ── /Tabs ──────────────────────────────────────────────────────── -->

</div>`;
}

export function init() {
  const TAB_IDS = ['terminos', 'documentos', 'ejecutivo'];

  window._mcTab = (activeId) => {
    TAB_IDS.forEach(id => {
      const panel = document.getElementById(`mc-panel-${id}`);
      const btn   = document.getElementById(`mc-tab-btn-${id}`);
      const on    = id === activeId;
      if (panel) panel.style.display = on ? 'block' : 'none';
      if (btn) {
        btn.style.borderBottomColor = on ? 'var(--green-txt)' : 'transparent';
        btn.style.color             = on ? 'var(--cream-dim)' : 'var(--sub)';
        btn.style.fontWeight        = on ? '600' : '400';
      }
    });
  };
}
