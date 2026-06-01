/* ═══════════════════════════════════════════════
   CRM DUNA — Modal System
   Helpers globales: openModal, closeModal, toast
   Builders para cada tipo de modal
═══════════════════════════════════════════════ */

// ── Core ──────────────────────────────────────
export function openModal(html) {
  const overlay = document.getElementById('modal-overlay');
  overlay.innerHTML = html;
  overlay.style.display = 'flex';
  overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); }, { once: true });
}

export function closeModal() {
  const overlay = document.getElementById('modal-overlay');
  overlay.style.display = 'none';
  overlay.innerHTML = '';
}

export function showToast(msg, color = 'var(--green-txt)') {
  const e = document.getElementById('duna-toast');
  if (e) e.remove();
  const t = document.createElement('div');
  t.id = 'duna-toast';
  t.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:10px 16px;font-size:12px;font-weight:500;color:${color};box-shadow:var(--shadow);animation:fadeIn .15s ease`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2500);
}

// Exponer globales para onclick inline
window.closeModal = closeModal;
window.showToast  = showToast;

// ── Modal: Documento ──────────────────────────
export function modalDocumento(doc) {
  const typeIcon = { KYC:'🪪', Contrato:'📝', Promesa:'📋', Reserva:'🔖', 'Plan de Pagos':'📊', Identificación:'🪪' };
  const icon = typeIcon[doc.type] || '📄';
  const isPropertyDoc = ['Contrato', 'Promesa', 'Reserva', 'Plan de Pagos'].includes(doc.type);

  const client      = window.DUNA_DATA.clients.find(c => `${c.firstName} ${c.lastName}` === doc.client);
  const relatedUnit = client ? window.DUNA_DATA.units.find(u => u.clientId === client.id) : null;

  const hasSigLines = doc.status === 'signature' || doc.status === 'signed';
  const docPreview  = `
    <div style="background:#f4efe6;border-radius:8px;padding:16px 18px;border:1px solid rgba(0,0,0,.1);position:relative;overflow:hidden;height:180px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:7px;border-bottom:2px solid #4A5E3F">
        <span style="font-family:'Cormorant Garamond',serif;font-size:11px;font-weight:700;color:#1a1a18;letter-spacing:.04em">DUNA DEVELOPMENT</span>
        <span style="font-size:8px;color:#666;background:rgba(74,94,63,.15);padding:2px 7px;border-radius:8px;letter-spacing:.04em">${doc.type.toUpperCase()}</span>
      </div>
      <div style="font-size:10px;font-weight:600;color:#2a2a28;margin-bottom:9px">${doc.name}</div>
      <div style="display:flex;flex-direction:column;gap:5px">
        ${[95, 80, 88, 72, 84].map(w => `<div style="height:6px;background:rgba(0,0,0,.11);border-radius:3px;width:${w}%"></div>`).join('')}
      </div>
      ${hasSigLines ? `
      <div style="position:absolute;bottom:14px;left:18px;right:18px">
        <div style="height:1px;background:#bbb;margin-bottom:10px"></div>
        <div style="display:flex;justify-content:space-between">
          <div><div style="height:1px;background:#aaa;width:90px;margin-bottom:3px"></div><div style="font-size:7px;color:#aaa">Comprador</div></div>
          <div><div style="height:1px;background:#aaa;width:90px;margin-bottom:3px"></div><div style="font-size:7px;color:#aaa;text-align:right">Duna Dev. Group</div></div>
        </div>
      </div>` : `<div style="position:absolute;bottom:0;left:0;right:0;height:44px;background:linear-gradient(transparent,#f4efe6)"></div>`}
    </div>`;

  // ── Simple: KYC / Identificación ───────────────────────────────
  if (!isPropertyDoc) {
    openModal(`
    <div class="modal">
      <div class="modal-header">
        <span class="modal-title">${icon} ${doc.name}</span>
        <div class="modal-close" onclick="closeModal()">✕</div>
      </div>
      <div class="modal-body">
        ${docPreview}
        <div style="margin-top:14px">
          ${kv([
            ['Tipo',    doc.type],
            ['Cliente', doc.client],
            ['Subido',  doc.uploaded],
            ['Peso',    doc.size],
            ['Estado',  `<span class="badge badge-${doc.statusCls}">${doc.statusLabel}</span>`],
            ...(doc.deadline ? [['Vence', `<span style="color:var(--orange)">${doc.deadline}</span>`]] : []),
          ])}
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cerrar</button>
        <button class="btn btn-ghost btn-sm" onclick="showToast('Descargando…','var(--blue)')">⬇ Descargar</button>
        ${doc.status === 'pending' ? `<button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✓ Documento aprobado','var(--green-txt)')">✓ Aprobar</button>` : ''}
        ${doc.status === 'expired' ? `<button class="btn btn-ghost btn-sm" style="color:var(--orange);border-color:rgba(201,124,64,.3)" onclick="closeModal();showToast('✉ Solicitud enviada al cliente','var(--orange)')">📤 Solicitar nuevo</button>` : ''}
      </div>
    </div>`);
    return;
  }

  // ── Rich: documentos de propiedad ──────────────────────────────
  window._openFirmaFromDoc = () => modalFirmaDocumento(doc, client);

  openModal(`
  <div class="modal modal-lg" style="padding:0;overflow:hidden">

    <!-- Header -->
    <div style="background:var(--bg-card2);border-bottom:1px solid var(--border);padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-size:15px;font-weight:600;color:var(--cream)">${icon} ${doc.name}</div>
        <div style="font-size:11px;color:var(--sub);margin-top:2px">${doc.type} · ${doc.client}${client ? ' · ' + client.unit : ''}</div>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <span class="badge badge-${doc.statusCls}">${doc.statusLabel}</span>
        <div onclick="closeModal()" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:6px;color:var(--sub);font-size:13px;background:var(--bg-surface)">✕</div>
      </div>
    </div>

    <!-- Body: 2 columnas -->
    <div style="display:grid;grid-template-columns:1fr 1fr;max-height:480px;overflow:hidden">

      <!-- Izquierda: previsualización + meta -->
      <div style="padding:18px 16px 18px 20px;border-right:1px solid var(--border);overflow-y:auto">
        ${docPreview}
        <div style="margin-top:14px">
          ${kv([
            ['Subido',  doc.uploaded],
            ['Peso',    doc.size],
            ...(doc.deadline ? [['Fecha límite', `<span style="color:var(--orange)">${doc.deadline}</span>`]] : []),
          ])}
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
          <button class="btn btn-ghost btn-sm" onclick="showToast('Descargando…','var(--blue)')">⬇ Descargar</button>
          <button class="btn btn-ghost btn-sm" onclick="showToast('Abriendo PDF…','var(--blue)')">👁 Ver PDF</button>
        </div>
      </div>

      <!-- Derecha: contexto + acciones -->
      <div style="padding:18px 20px 18px 16px;display:flex;flex-direction:column;gap:12px;overflow-y:auto">

        ${client ? `
        <!-- Propiedad vinculada -->
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:12px 14px">
          <div style="font-size:9px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px">Propiedad vinculada</div>
          <div style="font-family:'Cormorant Garamond',serif;font-size:16px;color:var(--cream);margin-bottom:2px">${client.unit}</div>
          <div style="font-size:11px;color:var(--muted);margin-bottom:10px">${client.project}</div>
          <div style="display:flex;gap:6px;flex-wrap:wrap">
            ${relatedUnit ? `<button class="btn btn-ghost btn-sm" style="font-size:10px" onclick="closeModal();openUnidadModal(${relatedUnit.id})">Ver unidad →</button>` : ''}
            <button class="btn btn-ghost btn-sm" style="font-size:10px" onclick="closeModal();window.location.hash='expediente/${client.id}'">Expediente →</button>
          </div>
        </div>

        <!-- Agente -->
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:50%;background:rgba(58,122,189,.15);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--blue);flex-shrink:0">${client.agent.split(' ').map(w => w[0]).join('').slice(0, 2)}</div>
          <div style="flex:1;min-width:0">
            <div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:1px">Agente responsable</div>
            <div style="font-size:12px;font-weight:600;color:var(--cream-dim)">${client.agent}</div>
          </div>
          <button class="btn btn-ghost btn-sm" style="font-size:10px" onclick="showToast('Perfil de agente — próximamente','var(--blue)')">Ver →</button>
        </div>` : ''}

        ${doc.status === 'pending' ? `
        <div style="background:rgba(201,124,64,.08);border:1px solid rgba(201,124,64,.25);border-radius:8px;padding:14px">
          <div style="font-size:11px;font-weight:600;color:var(--orange);margin-bottom:5px">⏳ Pendiente de revisión</div>
          <div style="font-size:11px;color:var(--sub);margin-bottom:10px">Descarga el documento y revísalo antes de aprobar.</div>
          <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✓ Documento aprobado','var(--green-txt)')">✓ Aprobar documento</button>
        </div>` : ''}

        ${doc.status === 'signature' ? `
        <div style="background:rgba(58,122,189,.07);border:2px solid rgba(58,122,189,.35);border-radius:8px;padding:14px">
          <div style="font-size:11px;font-weight:600;color:var(--blue);margin-bottom:5px">✍ Requiere firma</div>
          <div style="font-size:11px;color:var(--sub);line-height:1.6;margin-bottom:12px">Pendiente de firma por el comprador y el representante de Duna.</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="btn btn-primary btn-sm" onclick="_openFirmaFromDoc()">✍ Firmar en app</button>
            <button class="btn btn-ghost btn-sm" onclick="closeModal();showToast('✉ Enviado al cliente para firma','var(--blue)')">✉ Enviar a cliente</button>
          </div>
        </div>` : ''}

        ${doc.status === 'signed' ? `
        <div style="background:rgba(74,94,63,.1);border:1px solid rgba(130,184,112,.25);border-radius:8px;padding:14px">
          <div style="font-size:11px;font-weight:600;color:var(--green-txt);margin-bottom:4px">✓ Firmado por todas las partes</div>
          <div style="font-size:11px;color:var(--sub)">Firmado el ${doc.uploaded} — documento legalmente vinculante.</div>
        </div>` : ''}

        ${doc.status === 'approved' ? `
        <div style="background:rgba(74,94,63,.1);border:1px solid rgba(130,184,112,.25);border-radius:8px;padding:14px">
          <div style="font-size:11px;font-weight:600;color:var(--green-txt);margin-bottom:4px">✓ Aprobado</div>
          <div style="font-size:11px;color:var(--sub)">Aprobado el ${doc.uploaded}</div>
        </div>` : ''}

        ${doc.status === 'expired' ? `
        <div style="background:rgba(184,64,64,.08);border:1px solid rgba(184,64,64,.25);border-radius:8px;padding:14px">
          <div style="font-size:11px;font-weight:600;color:var(--red);margin-bottom:5px">⚠ Documento vencido</div>
          <div style="font-size:11px;color:var(--sub);margin-bottom:10px">Este documento ha expirado. Solicita uno actualizado al cliente.</div>
          <button class="btn btn-ghost btn-sm" style="color:var(--orange);border-color:rgba(201,124,64,.35)" onclick="closeModal();showToast('✉ Solicitud enviada al cliente','var(--orange)')">📤 Solicitar nuevo</button>
        </div>` : ''}

      </div>
    </div>
  </div>`);
}

// ── Modal: Firma de documento ─────────────────
function modalFirmaDocumento(doc, client) {
  let firmaSigned = false;

  window._generateFirma = () => {
    const pad = document.getElementById('firma-pad-area');
    if (!pad) return;
    pad.style.cursor = 'default';
    pad.innerHTML = `
      <svg viewBox="0 0 220 60" xmlns="http://www.w3.org/2000/svg" style="width:200px;height:54px">
        <path d="M12,46 C24,18 42,8 58,32 C72,52 84,12 102,24 C118,36 124,16 142,28 C158,40 168,14 186,26 C196,33 205,27 212,22"
          stroke="var(--cream-dim)" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M12,52 L70,52" stroke="var(--green-txt)" stroke-width="1" opacity=".5"/>
      </svg>`;
    firmaSigned = true;
    const signBtn  = document.getElementById('firma-sign-btn');
    const clearBtn = document.getElementById('firma-clear-btn');
    if (signBtn)  signBtn.style.display  = 'none';
    if (clearBtn) clearBtn.style.display = 'flex';
  };

  window._clearFirma = () => {
    const pad = document.getElementById('firma-pad-area');
    if (!pad) return;
    pad.style.cursor = 'pointer';
    pad.innerHTML = `
      <div style="font-size:28px;color:var(--muted);margin-bottom:8px">✍</div>
      <div style="font-size:12px;color:var(--sub)">Haz clic para generar tu firma</div>`;
    firmaSigned = false;
    const signBtn  = document.getElementById('firma-sign-btn');
    const clearBtn = document.getElementById('firma-clear-btn');
    if (signBtn)  signBtn.style.display  = 'flex';
    if (clearBtn) clearBtn.style.display = 'none';
  };

  window._confirmFirma = () => {
    if (!firmaSigned) { showToast('⚠ Genera tu firma primero', 'var(--orange)'); return; }
    if (!document.getElementById('firma-checkbox')?.checked) {
      showToast('⚠ Confirma que has leído el documento', 'var(--orange)'); return;
    }
    closeModal();
    showToast('✓ Documento firmado con éxito', 'var(--green-txt)');
  };

  const parties = [
    { role: 'Comprador', name: client ? `${client.firstName} ${client.lastName}` : doc.client },
    { role: 'Rep. Duna Development', name: 'Ana Rodríguez' },
  ];

  openModal(`
  <div class="modal modal-lg" style="padding:0;overflow:hidden">

    <!-- Header -->
    <div style="background:var(--bg-card2);border-bottom:1px solid var(--border);padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-size:15px;font-weight:600;color:var(--cream)">✍ Firma electrónica</div>
        <div style="font-size:11px;color:var(--sub);margin-top:2px">${doc.name}</div>
      </div>
      <div onclick="closeModal()" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:6px;color:var(--sub);font-size:13px;background:var(--bg-surface)">✕</div>
    </div>

    <!-- Body -->
    <div style="padding:20px;max-height:480px;overflow-y:auto">

      <!-- Resumen del documento -->
      <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:14px">
        <div style="font-size:26px">📝</div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:600;color:var(--cream-dim)">${doc.name}</div>
          <div style="font-size:11px;color:var(--muted);margin-top:2px">${doc.type}${client ? ' · ' + client.unit + ' · ' + client.project : ''}</div>
        </div>
        <span class="badge badge-blue">Por firmar</span>
      </div>

      <!-- Partes involucradas -->
      <div style="display:flex;gap:8px;margin-bottom:16px">
        ${parties.map(p => `
          <div style="flex:1;background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:10px 12px;text-align:center">
            <div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">${p.role}</div>
            <div style="font-size:11px;font-weight:600;color:var(--cream-dim)">${p.name}</div>
            <div style="font-size:9px;color:var(--orange);margin-top:4px">Firma pendiente</div>
          </div>
        `).join('')}
      </div>

      <!-- Pad de firma -->
      <div style="margin-bottom:14px">
        <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Tu firma (administrador)</div>
        <div id="firma-pad-area"
          style="border:2px dashed var(--border);border-radius:8px;background:var(--bg-surface);min-height:100px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;transition:border-color .15s"
          onmouseover="this.style.borderColor='rgba(255,255,255,.14)'"
          onmouseout="this.style.borderColor='var(--border)'"
          onclick="_generateFirma()">
          <div style="font-size:28px;color:var(--muted);margin-bottom:8px">✍</div>
          <div style="font-size:12px;color:var(--sub)">Haz clic para generar tu firma</div>
        </div>
        <div style="display:flex;gap:8px;margin-top:8px">
          <button id="firma-sign-btn" class="btn btn-ghost btn-sm" style="font-size:10px" onclick="_generateFirma()">Generar firma automática</button>
          <button id="firma-clear-btn" class="btn btn-ghost btn-sm" style="font-size:10px;display:none" onclick="_clearFirma()">✕ Borrar</button>
        </div>
      </div>

      <!-- Confirmación -->
      <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;background:var(--bg-surface);border:1px solid var(--border);border-radius:8px">
        <input type="checkbox" id="firma-checkbox" style="margin-top:2px;cursor:pointer;accent-color:var(--green-txt);flex-shrink:0">
        <span style="font-size:11px;color:var(--cream-dim);line-height:1.6">
          Confirmo haber leído y comprendido el documento <strong>${doc.name}</strong> y acepto que esta firma electrónica tiene plena validez legal conforme a la legislación dominicana.
        </span>
      </label>

    </div>

    <!-- Footer -->
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-ghost btn-sm" onclick="closeModal();showToast('✉ Enviado al cliente para firma','var(--blue)')">✉ Enviar a cliente</button>
      <button class="btn btn-primary btn-sm" onclick="_confirmFirma()">✓ Confirmar firma</button>
    </div>
  </div>`);
}

// ── Modal: Contrato ───────────────────────────
export function modalContrato(contract) {
  openModal(`
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">📝 ${contract.type} — ${contract.client}</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
        <div>
          <div style="font-size:11px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px">Partes</div>
          ${kv([
            ['Comprador',  contract.client],
            ['Proyecto',   contract.project],
            ['Unidad',     contract.unit],
          ])}
        </div>
        <div>
          <div style="font-size:11px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px">Términos</div>
          ${kv([
            ['Precio total', contract.total],
            ['Pagado',       contract.paid],
            ['Estado',       `<span class="badge badge-${contract.statusCls}">${contract.statusLabel}</span>`],
            ['Fecha firma',  contract.signed || '—'],
          ])}
        </div>
      </div>
      <div style="background:var(--bg-surface);border-radius:8px;padding:16px;font-size:12px;color:var(--sub);line-height:1.7">
        <strong style="color:var(--cream-dim)">Vista previa del contrato</strong><br>
        El documento completo está disponible para descarga. Haz clic en "Ver PDF" para revisar el contrato completo antes de enviarlo a firma.
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cerrar</button>
      <button class="btn btn-ghost btn-sm">📄 Ver PDF</button>
      <button class="btn btn-ghost btn-sm">⬇ Descargar</button>
      ${contract.status === 'pending_signature'
        ? `<button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✉ Enviado a firma vía DocuSign','var(--blue)')">✍ Enviar a firma</button>`
        : ''}
    </div>
  </div>`);
}

// ── Modal: Transacción ────────────────────────
export function modalTransaccion(tx) {
  const methodIcon = { 'Wire Transfer':'🏦', ACH:'⇄', Stripe:'💳' };
  openModal(`
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">💳 Detalle de transacción</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div style="text-align:center;padding:16px 0 20px;border-bottom:1px solid var(--border);margin-bottom:18px">
        <div style="font-size:28px;font-weight:700;color:${tx.status==='overdue'?'var(--red)':tx.status==='pending'?'var(--orange)':'var(--green-txt)'}">
          ${tx.amount}
        </div>
        <span class="badge badge-${tx.statusCls}" style="margin-top:8px;display:inline-block">${tx.statusLabel}</span>
      </div>
      ${kv([
        ['Cliente',  tx.client],
        ['Unidad',   tx.unit],
        ['Concepto', tx.concept],
        ['Fecha',    tx.date],
        ['Método',   `${methodIcon[tx.method]||''} ${tx.method}`],
      ])}
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cerrar</button>
      <button class="btn btn-ghost btn-sm">🧾 Ver comprobante</button>
      ${tx.status === 'overdue' ? `<button class="btn btn-primary btn-sm" onclick="closeModal();showToast('💬 Recordatorio enviado','var(--orange)')">Enviar recordatorio</button>` : ''}
    </div>
  </div>`);
}

// ── Modal: Unidad ─────────────────────────────
export function modalUnidad(unit) {
  const statusCls   = { available:'green', pending:'orange', sold:'gray' };
  const statusLabel = { available:'Disponible', pending:'Reservada', sold:'Vendida' };
  const viewIcon    = { 'Garden View':'🌿', 'Lake View':'🏞', 'Ocean View':'🌊', 'Pool View':'🏊', 'Lake Facing':'🏞', Penthouse:'👑' };

  const client = unit.clientId
    ? window.DUNA_DATA.clients.find(c => c.id === unit.clientId)
    : unit.client
      ? window.DUNA_DATA.clients.find(c => `${c.firstName} ${c.lastName}` === unit.client)
      : null;

  const broker = unit.brokerId
    ? window.DUNA_DATA.brokers.find(b => b.id === unit.brokerId)
    : null;

  const AMENITIES = [
    ['🏊', 'Piscina infinity'],  ['🏋', 'Gimnasio equipado'],
    ['🛎', 'Concierge 24/7'],   ['🅿', 'Estacionamiento'],
    ['🏖', 'Acceso a beach club'], ['🔒', 'Seguridad privada'],
    ['🏌', 'Golf Cap Cana'],    ['⛵', 'Marina Cap Cana'],
    ['🌿', 'Áreas verdes'],
  ];

  let deadlineDays = null;
  if (unit.status === 'pending' && unit.reservationDeadline) {
    deadlineDays = Math.ceil(
      (new Date(unit.reservationDeadline) - new Date('2026-05-09')) / 86400000
    );
  }
  const deadlineColor = deadlineDays === null ? 'var(--green-txt)'
    : deadlineDays <= 5  ? 'var(--red)'
    : deadlineDays <= 10 ? 'var(--orange)'
    : 'var(--green-txt)';

  const showReservaTab = unit.status !== 'available';
  const reservaLabel   = unit.status === 'sold' ? 'Propietario' : 'Reserva';

  window._openClientFromUnit = (clientId) => {
    const c = window.DUNA_DATA.clients.find(x => x.id === clientId);
    if (c) { closeModal(); window.openCompradorModal(c); }
  };

  openModal(`
  <div class="modal modal-lg" style="padding:0;overflow:hidden">

    <!-- Header -->
    <div style="background:var(--bg-card2);border-bottom:1px solid var(--border);padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:600;color:var(--cream);line-height:1">Unidad ${unit.num}</div>
        <div style="font-size:11px;color:var(--sub);margin-top:3px">${unit.type} · Makai Residences · Planta ${unit.floor}</div>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <span class="badge badge-${statusCls[unit.status]}">${statusLabel[unit.status]}</span>
        <div onclick="closeModal()" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:6px;color:var(--sub);font-size:13px;background:var(--bg-surface)">✕</div>
      </div>
    </div>

    <!-- Hero -->
    <div style="height:150px;background:linear-gradient(135deg,var(--bg-card) 0%,#1e2e1a 100%);position:relative;overflow:hidden;border-bottom:1px solid var(--border)">
      <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(12,12,11,.88) 0%,transparent 65%)"></div>
      <div style="position:absolute;inset:0;opacity:.04;background-image:repeating-linear-gradient(0deg,transparent,transparent 29px,rgba(255,255,255,.5) 30px),repeating-linear-gradient(90deg,transparent,transparent 29px,rgba(255,255,255,.5) 30px)"></div>
      <div style="position:absolute;bottom:14px;left:20px">
        <div style="font-size:26px;font-weight:700;color:#fff;font-family:'Cormorant Garamond',serif;letter-spacing:-.01em">$${unit.price.toLocaleString('en-US')}</div>
        <div style="font-size:12px;color:rgba(255,255,255,.55);margin-top:2px">${viewIcon[unit.view] || '🏙'} ${unit.view} · Cap Cana, Rep. Dom.</div>
      </div>
      <div style="position:absolute;top:12px;right:14px;background:rgba(0,0,0,.5);border-radius:5px;padding:4px 10px;font-size:10px;color:rgba(255,255,255,.5)">Imagen referencial</div>
    </div>

    <!-- Stats strip -->
    <div style="display:flex;background:var(--bg-card);border-bottom:1px solid var(--border)">
      ${[
        { icon:'🛏', val: unit.bed,                    sub: unit.bed === 1 ? 'Recámara' : 'Recámaras' },
        { icon:'🚿', val: unit.bath,                   sub: unit.bath === 1 ? 'Baño' : 'Baños' },
        { icon:'📐', val: unit.sqft.toLocaleString(),  sub: 'ft² interior' },
        { icon:'🌅', val: unit.sqftTer,                sub: 'ft² terraza' },
        { icon:'🏢', val: 'P' + unit.floor,            sub: 'Planta' },
      ].map(s => `
        <div style="flex:1;text-align:center;padding:10px 4px;border-right:1px solid var(--border)">
          <div style="font-size:14px;margin-bottom:2px">${s.icon}</div>
          <div style="font-size:13px;font-weight:700;color:var(--cream)">${s.val}</div>
          <div style="font-size:9px;color:var(--sub);margin-top:1px">${s.sub}</div>
        </div>
      `).join('')}
    </div>

    <!-- Tab bar -->
    <div style="display:flex;border-bottom:1px solid var(--border);padding:0 20px;background:var(--bg-card)" id="unit-tab-bar">
      <div class="unit-modal-tab" data-tab="detalle" onclick="switchUnitModalTab('detalle')"
        style="padding:10px 14px;font-size:12px;font-weight:500;cursor:pointer;border-bottom:2px solid var(--green-txt);color:var(--green-txt);user-select:none;white-space:nowrap">Detalle</div>
      ${showReservaTab ? `
      <div class="unit-modal-tab" data-tab="reserva" onclick="switchUnitModalTab('reserva')"
        style="padding:10px 14px;font-size:12px;font-weight:500;cursor:pointer;border-bottom:2px solid transparent;color:var(--sub);user-select:none;white-space:nowrap">${reservaLabel}</div>` : ''}
    </div>

    <!-- Tab content -->
    <div style="max-height:300px;overflow-y:auto">

      <!-- Tab: Detalle -->
      <div id="utab-detalle" style="padding:18px 20px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
          <div>
            <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Especificaciones</div>
            ${[
              ['Tipo',          unit.type],
              ['Vista',         `${viewIcon[unit.view] || '🏙'} ${unit.view}`],
              ['Planta',        unit.floor + 'º piso'],
              ['Recámaras',     unit.bed],
              ['Baños',         unit.bath],
              ['Área interior', unit.sqft.toLocaleString() + ' ft²'],
              ['Terraza',       unit.sqftTer + ' ft²'],
              ['Proyecto',      'Makai Residences'],
            ].map(([k, v]) => `
              <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:11px">
                <span style="color:var(--sub)">${k}</span>
                <span style="color:var(--cream-dim);font-weight:500">${v}</span>
              </div>
            `).join('')}
          </div>
          <div>
            <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Amenidades Makai</div>
            ${AMENITIES.map(([ic, name]) => `
              <div style="display:flex;align-items:center;gap:8px;padding:5px 0;font-size:11px;color:var(--cream-dim)">
                <span style="font-size:13px;width:18px;text-align:center;flex-shrink:0">${ic}</span>
                <span>${name}</span>
              </div>
            `).join('')}
          </div>
        </div>
      </div>

      <!-- Tab: Reserva / Propietario -->
      ${showReservaTab ? `
      <div id="utab-reserva" style="display:none;padding:18px 20px">

        ${deadlineDays !== null ? `
        <div style="background:${deadlineDays <= 5 ? 'rgba(184,64,64,.12)' : 'rgba(201,124,64,.1)'};border:1px solid ${deadlineDays <= 5 ? 'rgba(184,64,64,.3)' : 'rgba(201,124,64,.25)'};border-radius:8px;padding:14px 18px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between">
          <div>
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Tiempo restante — vencimiento de reserva</div>
            <div style="font-size:28px;font-weight:700;color:${deadlineColor};line-height:1">${deadlineDays} días</div>
            <div style="font-size:11px;color:var(--sub);margin-top:4px">Fecha límite: ${unit.reservationDeadline}</div>
          </div>
          <div style="width:56px;height:56px;border-radius:50%;border:3px solid ${deadlineColor};display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:${deadlineColor};flex-shrink:0">${deadlineDays}d</div>
        </div>` : ''}

        ${client ? `
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:14px 16px;margin-bottom:12px">
          <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Cliente ${unit.status === 'sold' ? '· Propietario' : ''}</div>
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
            <div style="width:42px;height:42px;border-radius:50%;background:var(--green-lite);border:2px solid rgba(130,184,112,.3);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;color:var(--green-txt);flex-shrink:0">${client.initials}</div>
            <div style="flex:1;min-width:0">
              <div style="font-size:13px;font-weight:600;color:var(--cream)">${client.firstName} ${client.lastName}</div>
              <div style="font-size:11px;color:var(--muted);margin-top:1px">${client.email}</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-end">
              <button class="btn btn-ghost btn-sm" style="font-size:10px" onclick="_openClientFromUnit(${client.id})">Ver perfil</button>
              <span class="cell-link" style="font-size:10px" onclick="closeModal();window.location.hash='expediente/${client.id}'">Expediente →</span>
            </div>
          </div>
          <div style="display:flex;gap:0;padding-top:10px;border-top:1px solid var(--border)">
            <div style="flex:1;padding-right:12px">
              <div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px">Pagado</div>
              <div style="font-size:14px;font-weight:700;color:var(--green-txt)">${client.paid}</div>
            </div>
            <div style="flex:1;border-left:1px solid var(--border);padding-left:12px;padding-right:12px">
              <div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px">Total</div>
              <div style="font-size:14px;font-weight:700;color:var(--cream-dim)">${client.price}</div>
            </div>
            <div style="flex:1;border-left:1px solid var(--border);padding-left:12px">
              <div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px">Progreso</div>
              <div style="font-size:14px;font-weight:700;color:${client.paidPct === 100 ? 'var(--green-txt)' : client.paidPct > 20 ? 'var(--blue)' : 'var(--orange)'}">${client.paidPct}%</div>
            </div>
          </div>
          <div style="margin-top:8px;height:4px;background:var(--bg-card);border-radius:4px;overflow:hidden">
            <div style="height:4px;border-radius:4px;background:${client.paidPct === 100 ? 'var(--green-txt)' : client.paidPct > 20 ? 'var(--blue)' : 'var(--orange)'};width:${client.paidPct}%"></div>
          </div>
        </div>

        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:12px 16px;margin-bottom:12px;display:flex;align-items:center;gap:12px">
          <div style="width:36px;height:36px;border-radius:50%;background:rgba(58,122,189,.15);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--blue);flex-shrink:0">${client.agent.split(' ').map(w => w[0]).join('').slice(0, 2)}</div>
          <div style="flex:1;min-width:0">
            <div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px">Agente asignado</div>
            <div style="font-size:12px;font-weight:600;color:var(--cream-dim)">${client.agent}</div>
          </div>
          <button class="btn btn-ghost btn-sm" style="font-size:10px" onclick="showToast('Perfil de agente — próximamente','var(--blue)')">Ver →</button>
        </div>` : `
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:24px;text-align:center;margin-bottom:12px">
          <div style="font-size:12px;color:var(--muted)">Sin datos de cliente aún</div>
        </div>`}

        ${broker ? `
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:12px 16px;display:flex;align-items:center;gap:12px">
          <div style="width:36px;height:36px;border-radius:50%;background:rgba(201,124,64,.15);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--orange);flex-shrink:0">${broker.name.split(' ').map(w => w[0]).join('').slice(0, 2)}</div>
          <div style="flex:1;min-width:0">
            <div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px">Broker</div>
            <div style="font-size:12px;font-weight:600;color:var(--cream-dim)">${broker.name}</div>
            <div style="font-size:10px;color:var(--muted)">${broker.agency}</div>
          </div>
          <button class="btn btn-ghost btn-sm" style="font-size:10px" onclick="closeModal();window.location.hash='broker-detail/${broker.id}'">Ver ficha →</button>
        </div>` : `
        <div style="font-size:11px;color:var(--muted);padding:4px 0">Sin broker asociado — venta directa</div>`}

      </div>` : ''}

    </div>

    <!-- Footer -->
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cerrar</button>
      ${unit.status !== 'available' && client ? `<button class="btn btn-ghost btn-sm" onclick="closeModal();window.location.hash='expediente/${client.id}'">Ver expediente →</button>` : ''}
      ${unit.status === 'available' ? `<button class="btn btn-primary btn-sm" onclick="closeModal();window.openNuevaReservaModal?.() || showToast('Iniciando reserva…','var(--green-txt)')">+ Reservar unidad</button>` : ''}
    </div>
  </div>`);

  window.switchUnitModalTab = (tab) => {
    ['detalle', 'reserva'].forEach(t => {
      const el = document.getElementById(`utab-${t}`);
      if (el) el.style.display = t === tab ? '' : 'none';
    });
    document.querySelectorAll('#unit-tab-bar .unit-modal-tab').forEach(btn => {
      const active = btn.dataset.tab === tab;
      btn.style.borderBottomColor = active ? 'var(--green-txt)' : 'transparent';
      btn.style.color = active ? 'var(--green-txt)' : 'var(--sub)';
    });
  };
}

// ── Modal: Broker detail (inline) ─────────────
export function modalBrokerDetail(broker) {
  const flag = { RD:'🇩🇴', USA:'🇺🇸', España:'🇪🇸', México:'🇲🇽', Colombia:'🇨🇴' };
  const contractBadge = { active:'green', expiring:'orange', pending:'gray' };
  const contractLabel = { active:'Contrato vigente', expiring:'Contrato por vencer', pending:'Sin contrato' };
  const initials = broker.name.split(' ').map(w => w[0]).join('').slice(0, 2);

  const brokerClients = [
    { name:'Carlos Méndez',  unit:'U-111', project:'Makai', status:'Activo',     cls:'green'  },
    { name:'María López',    unit:'U-114', project:'Makai', status:'Activo',     cls:'green'  },
    { name:'Luis Pérez',     unit:'U-308', project:'Makai', status:'En proceso', cls:'orange' },
    { name:'Sophie Martin',  unit:'U-312', project:'Makai', status:'En proceso', cls:'orange' },
    { name:'Roberto Silva',  unit:'U-220', project:'Makai', status:'Vencido',    cls:'red'    },
  ].slice(0, broker.clients);

  openModal(`
  <div class="modal modal-lg" style="padding:0;overflow:hidden" id="broker-modal">

    <!-- Header rico -->
    <div style="background:var(--bg-card2);border-bottom:1px solid var(--border);padding:20px 24px;position:relative">
      <div class="modal-close" onclick="closeModal()" style="position:absolute;top:14px;right:14px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:6px;color:var(--sub);font-size:14px;background:var(--bg-surface)">✕</div>
      <div style="display:flex;align-items:center;gap:16px">
        <div style="width:54px;height:54px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#fff;flex-shrink:0">${initials}</div>
        <div style="flex:1;min-width:0">
          <div style="font-size:18px;font-weight:600;color:var(--cream);font-family:'Cormorant Garamond',serif;line-height:1.2">${broker.name}</div>
          <div style="font-size:12px;color:var(--sub);margin-top:3px">${broker.agency} · ${flag[broker.country]||'🌍'} ${broker.country}</div>
          <div style="font-size:11px;margin-top:2px"><a href="mailto:${broker.email}" style="color:var(--muted);text-decoration:none">${broker.email}</a></div>
        </div>
        <div style="display:flex;gap:6px;flex-direction:column;align-items:flex-end">
          <span class="badge badge-${broker.status === 'active' ? 'green' : 'gray'}">${broker.status === 'active' ? 'Activo' : 'Pendiente'}</span>
          <span class="badge badge-${contractBadge[broker.contract]}">${contractLabel[broker.contract]}</span>
        </div>
      </div>
      <div style="display:flex;gap:28px;margin-top:16px;padding-top:14px;border-top:1px solid var(--border)">
        ${[
          { label:'Clientes',            val: broker.clients },
          { label:'Comisión acumulada',  val: broker.commission },
          { label:'Vencimiento',         val: broker.contractExpiry || '—' },
          { label:'Tasa de comisión',    val: '4%' },
        ].map(m => `
          <div>
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">${m.label}</div>
            <div style="font-size:15px;font-weight:600;color:var(--cream-dim)">${m.val}</div>
          </div>
        `).join('')}
      </div>
    </div>

    <!-- Tab bar -->
    <div style="display:flex;border-bottom:1px solid var(--border);padding:0 24px;background:var(--bg-card)" id="broker-tab-bar">
      ${[['perfil','Perfil'],['contrato','Contrato'],['clientes','Clientes']].map(([t,l], i) => `
        <div class="broker-tab" data-tab="${t}" onclick="switchBrokerTab('${t}')" style="padding:11px 14px;font-size:12px;font-weight:500;cursor:pointer;border-bottom:2px solid ${i===0?'var(--green-txt)':'transparent'};color:${i===0?'var(--green-txt)':'var(--sub)'};margin-right:2px;user-select:none;transition:color .15s">${l}</div>
      `).join('')}
    </div>

    <!-- Tab content -->
    <div style="padding:20px 24px;max-height:340px;overflow-y:auto">

      <!-- Perfil -->
      <div id="btab-perfil">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px">
          <div>
            <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Datos de contacto</div>
            ${kv([
              ['Nombre',    broker.name],
              ['Agencia',   broker.agency],
              ['Email',     broker.email],
              ['País',      `${flag[broker.country]||'🌍'} ${broker.country}`],
            ])}
          </div>
          <div>
            <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Actividad comercial</div>
            ${kv([
              ['Clientes referidos', broker.clients],
              ['Comisión generada',  broker.commission],
              ['Tasa comisión',      '4%'],
              ['Proyectos',          'Makai Residences'],
              ['Incorporación',      'Enero 2026'],
            ])}
          </div>
        </div>
        <div style="padding:12px 14px;background:var(--bg-surface);border-radius:8px;font-size:12px;color:var(--sub);line-height:1.7">
          <strong style="color:var(--cream-dim);display:block;margin-bottom:4px">Notas internas</strong>
          Broker con buen historial de referencias internacionales. Canal principal: compradores de EE.UU. y Europa. Preferencia por comunicación vía email.
        </div>
      </div>

      <!-- Contrato -->
      <div id="btab-contrato" style="display:none">
        <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:14px">
          <div style="background:var(--bg-card2);padding:12px 16px;display:flex;align-items:center;justify-content:space-between">
            <div>
              <div style="font-size:12px;font-weight:600;color:var(--cream-dim)">Contrato de Comisiones — ${broker.agency}</div>
              <div style="font-size:11px;color:var(--muted);margin-top:2px">Duna Development Group · ${broker.contractExpiry ? 'Vigente hasta ' + broker.contractExpiry : 'Pendiente de activación'}</div>
            </div>
            <span class="badge badge-${contractBadge[broker.contract]}">${contractLabel[broker.contract]}</span>
          </div>
          <div style="padding:14px 16px">
            ${kv([
              ['Partes',             `Duna Development Group &amp; ${broker.agency}`],
              ['Tasa de comisión',   '4% sobre precio de venta neto'],
              ['Proyectos cubiertos','Makai Residences, Naviva (cuando aplique)'],
              ['Forma de pago',      'Transferencia bancaria — 30 días tras cierre'],
              ['Vigencia',           broker.contractExpiry ? `Hasta ${broker.contractExpiry}` : 'Pendiente de firma'],
              ['Exclusividad',       'No exclusivo — múltiples canales permitidos'],
              ['Renovación',         'Automática salvo aviso 60 días antes'],
            ])}
          </div>
          <div style="background:var(--bg-surface);padding:10px 16px;border-top:1px solid var(--border);font-size:11px;color:var(--muted);line-height:1.65">
            <strong style="color:var(--cream-dim)">Cláusulas clave — </strong>El broker se compromete a referir clientes con capacidad financiera verificada. Comisiones no aplican sobre descuentos > 3% negociados por la desarrolladora. Disputas bajo legislación dominicana.
          </div>
        </div>
        <div style="display:flex;gap:8px">
          <button class="btn btn-ghost btn-sm">⬇ Descargar PDF</button>
          ${broker.contract === 'expiring' ? `<button class="btn btn-primary btn-sm" onclick="closeModal();showToast('📋 Renovación iniciada','var(--green-txt)')">🔄 Renovar contrato</button>` : ''}
          ${broker.contract === 'pending'  ? `<button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✉ Contrato enviado a firma','var(--blue)')">✍ Enviar a firma</button>` : ''}
        </div>
      </div>

      <!-- Clientes -->
      <div id="btab-clientes" style="display:none">
        ${brokerClients.length === 0
          ? `<div style="text-align:center;padding:32px;font-size:12px;color:var(--muted)">Sin clientes referidos aún</div>`
          : brokerClients.map(c => `
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)">
              <div style="width:30px;height:30px;border-radius:50%;background:var(--green-lite);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:var(--green-txt);flex-shrink:0">${c.name.split(' ').map(w=>w[0]).join('').slice(0,2)}</div>
              <div style="flex:1;min-width:0">
                <div style="font-size:12px;font-weight:500;color:var(--cream-dim)">${c.name}</div>
                <div style="font-size:10px;color:var(--muted)">${c.unit} · ${c.project}</div>
              </div>
              <span class="badge badge-${c.cls}" style="font-size:9px">${c.status}</span>
              <span class="cell-link" style="font-size:11px;flex-shrink:0" onclick="closeModal();window.location.hash='expedientes'">Ver →</span>
            </div>
          `).join('')}
      </div>

    </div>

    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cerrar</button>
      <button class="btn btn-ghost btn-sm" onclick="closeModal();window.location.hash='broker-detail/${broker.id}'">Ver ficha completa →</button>
      <button class="btn btn-ghost btn-sm" onclick="openEditarBrokerModal(${broker.id})">✏ Editar perfil</button>
      ${broker.status === 'pending' ? `<button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✓ Broker activado','var(--green-txt)')">✓ Activar</button>` : ''}
    </div>
  </div>`);

  window.switchBrokerTab = (tab) => {
    ['perfil','contrato','clientes'].forEach(t => {
      const el = document.getElementById(`btab-${t}`);
      if (el) el.style.display = t === tab ? '' : 'none';
    });
    document.querySelectorAll('#broker-tab-bar .broker-tab').forEach(btn => {
      const active = btn.dataset.tab === tab;
      btn.style.borderBottomColor = active ? 'var(--green-txt)' : 'transparent';
      btn.style.color = active ? 'var(--green-txt)' : 'var(--sub)';
    });
  };
}

// ── Modal: Editar broker ──────────────────────
export function modalEditarBroker(broker) {
  openModal(`
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">✏ Editar broker — ${broker.name}</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Nombre completo</label>
          <input class="field-input" value="${broker.name}">
        </div>
        <div class="field-group">
          <label class="field-label">Agencia</label>
          <input class="field-input" value="${broker.agency}">
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Email</label>
          <input class="field-input" type="email" value="${broker.email}">
        </div>
        <div class="field-group">
          <label class="field-label">País</label>
          <input class="field-input" value="${broker.country}">
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Tasa de comisión (%)</label>
          <input class="field-input" type="number" value="4" min="0" max="20">
        </div>
        <div class="field-group">
          <label class="field-label">Estado</label>
          <select class="field-select">
            <option value="active"     ${broker.status === 'active'  ? 'selected' : ''}>Activo</option>
            <option value="pending"    ${broker.status === 'pending' ? 'selected' : ''}>Pendiente</option>
            <option value="suspended">Suspendido</option>
          </select>
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Inicio de contrato</label>
          <input class="field-input" type="date" value="2026-01-15">
        </div>
        <div class="field-group">
          <label class="field-label">Vencimiento de contrato</label>
          <input class="field-input" type="date" value="${broker.contractExpiry || ''}">
        </div>
      </div>
      <div class="field-group">
        <label class="field-label">Notas internas</label>
        <textarea class="field-textarea" rows="3" placeholder="Canal preferido, historial, observaciones…">Broker con buen historial de referencias internacionales. Canal principal: compradores de EE.UU. y Europa.</textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-ghost btn-sm" style="color:var(--red);border-color:rgba(184,64,64,.3)" onclick="closeModal();showToast('Usuario suspendido','var(--orange)')">Suspender</button>
      <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✓ Broker actualizado','var(--green-txt)')">Guardar cambios</button>
    </div>
  </div>`);
}

// ── Modal: Editar usuario ─────────────────────
export function modalEditarUsuario(user) {
  const roleColor = { admin:'#4A5E3F', senior_agent:'#3a7abd', buyer:'#5a5a5a', broker:'#c97c40' };
  const initials  = user.name.split(' ').map(n => n[0]).join('').slice(0, 2);

  openModal(`
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">✏ Editar usuario — ${user.name}</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div style="display:flex;align-items:center;gap:14px;padding:14px;background:var(--bg-surface);border-radius:8px;margin-bottom:18px">
        <div style="width:44px;height:44px;border-radius:50%;background:${roleColor[user.role]||'#5a5a5a'};display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#fff;flex-shrink:0">${initials}</div>
        <div>
          <div style="font-size:13px;font-weight:600;color:var(--cream-dim)">${user.name}</div>
          <div style="font-size:11px;color:var(--sub)">${user.roleLabel} · Último acceso: ${user.lastLogin}</div>
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Nombre completo</label>
          <input class="field-input" value="${user.name}">
        </div>
        <div class="field-group">
          <label class="field-label">Email</label>
          <input class="field-input" type="email" value="${user.email}">
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Rol</label>
          <select class="field-select" ${user.role === 'admin' ? 'disabled' : ''}>
            <option value="buyer"        ${user.role === 'buyer'        ? 'selected' : ''}>Comprador</option>
            <option value="senior_agent" ${user.role === 'senior_agent' ? 'selected' : ''}>Agente Senior</option>
            <option value="broker"       ${user.role === 'broker'       ? 'selected' : ''}>Broker</option>
          </select>
        </div>
        <div class="field-group">
          <label class="field-label">Estado</label>
          <select class="field-select">
            <option value="active"    ${user.status === 'active'  ? 'selected' : ''}>Activo</option>
            <option value="pending"   ${user.status === 'pending' ? 'selected' : ''}>Pendiente</option>
            <option value="suspended">Suspendido</option>
          </select>
        </div>
      </div>
      ${user.role !== 'admin' ? `
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Teléfono</label>
          <input class="field-input" placeholder="+1 809-555-0000">
        </div>
        <div class="field-group">
          <label class="field-label">País</label>
          <input class="field-input" placeholder="Rep. Dominicana">
        </div>
      </div>` : ''}
      <div class="field-group">
        <label class="field-label">Notas internas</label>
        <textarea class="field-textarea" rows="2" placeholder="Observaciones sobre este usuario…"></textarea>
      </div>
      ${user.role !== 'admin' ? `
      <div style="margin-top:14px;padding:12px;background:var(--bg-surface);border-radius:8px;border:1px solid rgba(184,64,64,.2)">
        <div style="font-size:11px;font-weight:600;color:var(--red);margin-bottom:8px">⚠ Zona de riesgo</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn btn-ghost btn-sm" style="font-size:11px;color:var(--orange);border-color:rgba(201,124,64,.3)" onclick="closeModal();showToast('🔒 Contraseña reseteada — email enviado','var(--orange)')">Resetear contraseña</button>
          <button class="btn btn-ghost btn-sm" style="font-size:11px;color:var(--red);border-color:rgba(184,64,64,.3)" onclick="closeModal();showToast('Cuenta desactivada','var(--red)')">Desactivar cuenta</button>
        </div>
      </div>` : ''}
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✓ Usuario actualizado','var(--green-txt)')">Guardar cambios</button>
    </div>
  </div>`);
}

// ── Modal: Comprador detail ───────────────────
export function modalCompradorDetail(client) {
  const FLAG  = { 'Rep. Dominicana':'🇩🇴', USA:'🇺🇸', España:'🇪🇸', México:'🇲🇽', Colombia:'🇨🇴', Brasil:'🇧🇷', Francia:'🇫🇷', UAE:'🇦🇪' };
  const sBadge = { kyc_pending:'orange', signature_required:'red', in_review:'blue', completed:'green', payment_overdue:'red' };
  const steps  = ['', 'Contacto', 'KYC / Docs', 'Reserva', 'Promesa', 'Contrato', 'Completado'];

  window._activeComprador = client;

  openModal(`
  <div class="modal modal-lg" style="padding:0;overflow:hidden" id="comprador-modal">

    <!-- Header -->
    <div style="background:var(--bg-card2);border-bottom:1px solid var(--border);padding:20px 24px;position:relative">
      <div class="modal-close" onclick="closeModal()" style="position:absolute;top:14px;right:14px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:6px;color:var(--sub);font-size:14px;background:var(--bg-surface)">✕</div>

      <div style="display:flex;align-items:center;gap:16px">
        <div style="width:54px;height:54px;border-radius:50%;background:var(--green-lite);border:2px solid rgba(130,184,112,.3);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:var(--green-txt);flex-shrink:0">${client.initials}</div>
        <div style="flex:1;min-width:0">
          <div style="font-size:18px;font-weight:600;color:var(--cream);font-family:'Cormorant Garamond',serif;line-height:1.2">${client.firstName} ${client.lastName}</div>
          <div style="font-size:12px;color:var(--sub);margin-top:3px">${FLAG[client.country] || '🌍'} ${client.country} · <a href="mailto:${client.email}" style="color:var(--muted);text-decoration:none">${client.email}</a></div>
          <div style="font-size:11px;color:var(--muted);margin-top:1px">${client.phone}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px">
          <span class="badge badge-${sBadge[client.status] || 'gray'}">${client.statusLabel}</span>
          <span style="font-size:10px;color:var(--muted)">Agente: ${client.agent}</span>
        </div>
      </div>

      <!-- Métricas -->
      <div style="display:flex;gap:0;margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">
        ${[
          { label:'Unidad',       val: client.unit,       color:'var(--cream-dim)' },
          { label:'Proyecto',     val: client.project.replace(' Residences',''), color:'var(--cream-dim)' },
          { label:'Precio total', val: client.price,      color:'var(--cream-dim)' },
          { label:'Pagado',       val: client.paid,       color:'var(--green-txt)' },
          { label:'Progreso',     val: client.paidPct + '%', color: client.paidPct === 100 ? 'var(--green-txt)' : client.paidPct > 20 ? 'var(--blue)' : 'var(--orange)' },
        ].map((m, i) => `
          <div style="flex:1;${i > 0 ? 'border-left:1px solid var(--border);padding-left:14px;' : ''}">
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">${m.label}</div>
            <div style="font-size:13px;font-weight:700;color:${m.color}">${m.val}</div>
          </div>
        `).join('')}
      </div>
      <!-- Barra de progreso -->
      <div style="margin-top:10px;background:var(--bg-surface);border-radius:4px;height:5px">
        <div style="height:5px;border-radius:4px;background:${client.paidPct === 100 ? 'var(--green-txt)' : client.paidPct > 20 ? 'var(--blue)' : 'var(--orange)'};width:${client.paidPct}%"></div>
      </div>
    </div>

    <!-- Tabs -->
    <div style="display:flex;border-bottom:1px solid var(--border);padding:0 24px;background:var(--bg-card)" id="comp-tab-bar">
      ${[['cinfo','Información'],['cpropiedad','Propiedad'],['cdocs','Documentos'],['cactividad','Actividad']].map(([t, l], i) => `
        <div class="comp-tab" data-tab="${t}" onclick="switchCompTab('${t}')"
          style="padding:11px 14px;font-size:12px;font-weight:500;cursor:pointer;border-bottom:2px solid ${i === 0 ? 'var(--green-txt)' : 'transparent'};color:${i === 0 ? 'var(--green-txt)' : 'var(--sub)'};user-select:none;margin-right:2px">${l}</div>
      `).join('')}
    </div>

    <!-- Contenido de tabs -->
    <div style="padding:18px 24px;max-height:320px;overflow-y:auto">

      <!-- Información -->
      <div id="ctab-cinfo">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
          <div>
            <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Contacto</div>
            ${kvP('Nombre',          `${client.firstName} ${client.lastName}`)}
            ${kvP('Email',           `<a href="mailto:${client.email}" style="color:var(--blue);text-decoration:none">${client.email}</a>`)}
            ${kvP('Teléfono',        client.phone)}
            ${kvP('País',            `${FLAG[client.country] || '🌍'} ${client.country}`)}
            ${kvP('Registro',        client.createdAt)}
            ${kvP('Agente asignado', client.agent)}
          </div>
          <div>
            <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Estado del proceso</div>
            ${kvP('Estado actual',  `<span class="badge badge-${sBadge[client.status] || 'gray'}">${client.statusLabel}</span>`)}
            ${kvP('Etapa',          `${client.step} / 6 — ${steps[client.step] || '?'}`)}
            ${kvP('Última acción',  client.lastAction)}
            ${client.flags.length ? kvP('Alertas', client.flags.map(f =>
              `<span class="badge badge-orange" style="font-size:9px;margin-right:3px">${f.toUpperCase()}</span>`
            ).join('')) : ''}
          </div>
        </div>
      </div>

      <!-- Propiedad -->
      <div id="ctab-cpropiedad" style="display:none">
        <div style="background:var(--bg-surface);border-radius:8px;padding:14px;margin-bottom:14px">
          <div style="font-family:'Cormorant Garamond',serif;font-size:16px;color:var(--cream);margin-bottom:12px">${client.unit} · ${client.project}</div>
          ${kvP('Precio total',         `<strong style="color:var(--cream-dim)">${client.price}</strong>`)}
          ${kvP('Monto pagado',         `<strong style="color:var(--green-txt)">${client.paid}</strong>`)}
          ${kvP('Porcentaje pagado',    client.paidPct + '%')}
          ${kvP('Plan de pago',         '24 cuotas mensuales')}
          ${kvP('Próxima cuota',        client.paidPct < 100 ? 'Cuota ' + (Math.ceil(client.paidPct / 4) + 1) + ' — vence 10 jun 2026' : '—')}
          ${kvP('Entrega estimada',     'Q4 2026')}
        </div>
      </div>

      <!-- Documentos -->
      <div id="ctab-cdocs" style="display:none">
        ${[
          { icon:'🪪', label:'Identificación / Pasaporte', ok: !client.flags.includes('kyc'),     warn: client.flags.includes('kyc') },
          { icon:'💰', label:'Comprobante de ingresos',    ok: client.step >= 3,                   warn: client.step < 3              },
          { icon:'📋', label:'Reserva / Promesa firmada',  ok: client.step >= 3,                   warn: client.status === 'signature_required' },
          { icon:'📝', label:'Contrato de compraventa',    ok: client.step >= 5,                   warn: false                        },
          { icon:'💳', label:'Plan de pagos activo',       ok: client.step >= 4,                   warn: client.status === 'payment_overdue'    },
        ].map(d => `
          <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)">
            <span style="font-size:16px;flex-shrink:0">${d.icon}</span>
            <div style="flex:1;font-size:12px;color:var(--cream-dim)">${d.label}</div>
            ${d.ok
              ? `<span style="font-size:11px;font-weight:500;color:var(--green-txt)">✓ OK</span>`
              : d.warn
                ? `<span style="font-size:11px;font-weight:500;color:var(--orange)">⚠ Pendiente</span>`
                : `<span style="font-size:11px;color:var(--muted)">N/A</span>`}
          </div>
        `).join('')}
      </div>

      <!-- Actividad -->
      <div id="ctab-cactividad" style="display:none">
        ${(() => {
          // Mock activity data seeded from client step/pct
          const sessions  = 12 + client.step * 5 + Math.round(client.paidPct / 8);
          const avgMin    = 3 + client.step;
          const docsViews = client.step * 3 + 4;
          const lastLogin = client.step >= 4 ? 'hace 2 horas' : client.step >= 2 ? 'ayer' : 'hace 3 días';

          const recentProps = [
            { name: `${client.project.replace(' Residences','')} ${client.unit}`, views: 18, last: 'hoy' },
            { name: `${client.project.replace(' Residences','')} 2A`,             views: 11, last: 'ayer' },
            { name: 'Naviva Suite 1B',                                             views: 7,  last: 'hace 3 días' },
            { name: 'LIV Studio 12C',                                              views: 4,  last: 'hace 1 sem.' },
          ];
          const recentViews = [
            { icon:'🏠', desc:`Visitó ${client.unit} · ${client.project}`,        time:'Hoy, 10:24' },
            { icon:'📄', desc:'Descargó ficha técnica de Makai 2A',                time:'Ayer, 17:05' },
            { icon:'💬', desc:'Abrió chat con su asesor',                          time:'Ayer, 16:48' },
            { icon:'🏠', desc:'Visitó Naviva Suite 1B',                            time:'hace 3 días' },
            { icon:'📋', desc:'Revisó su plan de pagos',                           time:'hace 4 días' },
          ];
          const maxPropViews = recentProps[0].views;

          return `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:16px">
              <div>
                <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Resumen de actividad</div>
                ${kvP('Sesiones este mes',    `<strong style="color:var(--cream)">${sessions}</strong>`)}
                ${kvP('Última conexión',      lastLogin)}
                ${kvP('Sesión promedio',      `${avgMin}m ${Math.round(Math.random()*59)}s`)}
                ${kvP('Documentos visitados', docsViews)}
                ${kvP('Propiedades vistas',   recentProps.length)}
                ${kvP('Plataforma',           client.step >= 3 ? '<span style="color:var(--green-txt)">Alta actividad</span>' : '<span style="color:var(--orange)">Actividad media</span>')}
              </div>
              <div>
                <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Propiedades más vistas</div>
                ${recentProps.map(p => `
                  <div style="margin-bottom:9px">
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px">
                      <span style="font-size:11px;color:var(--cream-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px">${p.name}</span>
                      <span style="font-size:10px;font-weight:700;color:var(--cream);flex-shrink:0;margin-left:6px">${p.views} vis.</span>
                    </div>
                    <div style="background:var(--bg-surface);border-radius:3px;height:4px">
                      <div style="height:4px;border-radius:3px;background:var(--green);width:${Math.round((p.views/maxPropViews)*100)}%"></div>
                    </div>
                    <div style="font-size:9px;color:var(--muted);margin-top:1px">Última visita: ${p.last}</div>
                  </div>
                `).join('')}
              </div>
            </div>
            <div>
              <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Últimas acciones en plataforma</div>
              ${recentViews.map((v, i) => `
                <div style="display:flex;align-items:flex-start;gap:10px;padding:7px 0;${i < recentViews.length-1 ? 'border-bottom:1px solid var(--border)' : ''}">
                  <span style="font-size:14px;flex-shrink:0;margin-top:1px">${v.icon}</span>
                  <div style="flex:1;font-size:11px;color:var(--cream-dim)">${v.desc}</div>
                  <div style="font-size:10px;color:var(--muted);white-space:nowrap;flex-shrink:0">${v.time}</div>
                </div>
              `).join('')}
            </div>`;
        })()}
      </div>

    </div>

    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cerrar</button>
      <button class="btn btn-ghost btn-sm" onclick="openEditarCompradorModal(window._activeComprador)">✏ Editar</button>
      <button class="btn btn-ghost btn-sm" onclick="closeModal();window.location.hash='expediente/${client.id}'">Ver expediente →</button>
      <button class="btn btn-primary btn-sm" onclick="closeModal();window.location.hash='mensajes'">💬 Mensaje</button>
    </div>
  </div>`);

  window.switchCompTab = (tab) => {
    ['cinfo','cpropiedad','cdocs','cactividad'].forEach(t => {
      const el = document.getElementById(`ctab-${t}`);
      if (el) el.style.display = t === tab ? '' : 'none';
    });
    document.querySelectorAll('#comp-tab-bar .comp-tab').forEach(btn => {
      const active = btn.dataset.tab === tab;
      btn.style.borderBottomColor = active ? 'var(--green-txt)' : 'transparent';
      btn.style.color = active ? 'var(--green-txt)' : 'var(--sub)';
    });
  };
}

// ── Modal: Prospecto detail ───────────────────
export function modalProspectoDetail(prospect) {
  const FLAG = { 'Rep. Dominicana':'🇩🇴', USA:'🇺🇸', España:'🇪🇸', México:'🇲🇽', Francia:'🇫🇷', UAE:'🇦🇪' };

  openModal(`
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">🔍 Prospecto — ${prospect.firstName} ${prospect.lastName}</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div style="display:flex;align-items:center;gap:14px;padding:14px;background:var(--bg-surface);border-radius:8px;margin-bottom:18px">
        <div style="width:44px;height:44px;border-radius:50%;background:rgba(58,122,189,.15);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:var(--blue);flex-shrink:0">${prospect.initials}</div>
        <div style="flex:1">
          <div style="font-size:14px;font-weight:600;color:var(--cream-dim)">${prospect.firstName} ${prospect.lastName}</div>
          <div style="display:flex;align-items:center;gap:8px;margin-top:4px">
            <span class="badge badge-blue">Prospecto</span>
            <span style="font-size:11px;color:var(--muted)">Interés: ${prospect.interest}</span>
          </div>
        </div>
      </div>
      ${kvP('Email',           `<a href="mailto:${prospect.email}" style="color:var(--blue);text-decoration:none">${prospect.email}</a>`)}
      ${kvP('Teléfono',        prospect.phone)}
      ${kvP('País',            `${FLAG[prospect.country] || '🌍'} ${prospect.country}`)}
      ${kvP('Canal de origen', prospect.source)}
      ${kvP('Proyecto de interés', prospect.interest)}
      ${kvP('Registro',        prospect.createdAt)}
      ${kvP('Último contacto', prospect.lastAction)}
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cerrar</button>
      <button class="btn btn-ghost btn-sm" onclick="closeModal();showToast('✉ Correo enviado','var(--blue)')">✉ Enviar email</button>
      <button class="btn btn-primary btn-sm" onclick="closeModal();openNuevoExpedienteModal()">+ Crear expediente</button>
    </div>
  </div>`);
}

// ── Builders de formularios de creación ───────

export function modalNuevoExpediente(data) {
  const availUnits = data.units.filter(u => u.status === 'available');
  const brokers    = data.brokers.filter(b => b.status === 'active');

  const stepDot = (n, label) => `
    <div class="step-item">
      <div class="step-dot" id="nexp-dot-${n}">${n}</div>
      <span class="step-label" id="nexp-lbl-${n}">${label}</span>
    </div>`;

  const unitCard = u => `
    <div id="nexp-ucard-${u.id}" onclick="_nexpSelectUnit(${u.id})"
      style="background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:12px;cursor:pointer;transition:border-color .12s">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
        <span style="font-size:13px;font-weight:600;color:var(--cream-dim)">${u.num}</span>
        <span style="font-size:9px;color:var(--green-txt);background:var(--green-lite);padding:2px 7px;border-radius:10px">Disponible</span>
      </div>
      <div style="font-size:11px;color:var(--sub);margin-bottom:3px">${u.type} · Planta ${u.floor}</div>
      <div style="font-size:11px;color:var(--sub);margin-bottom:6px">${u.bed}b · ${u.bath}ba · ${u.sqft.toLocaleString()} sqft</div>
      <div style="font-size:14px;font-weight:600;color:var(--green-txt)">$${u.price.toLocaleString()}</div>
    </div>`;

  openModal(`
  <div class="modal modal-lg" style="max-width:780px">
    <div class="modal-header">
      <span class="modal-title">📁 Nuevo Expediente</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body" style="padding-top:8px">

      <!-- Step bar -->
      <div class="step-bar" style="margin-bottom:24px">
        ${stepDot(1,'Cliente')}
        <div class="step-line"></div>
        ${stepDot(2,'Unidad')}
        <div class="step-line"></div>
        ${stepDot(3,'Plan de pago')}
        <div class="step-line"></div>
        ${stepDot(4,'Confirmar')}
      </div>

      <!-- ── Step 1: Cliente ── -->
      <div id="nexp-s1">
        <div class="field-row">
          <div class="field-group"><label class="field-label">Nombre *</label><input class="field-input" id="nexp-nombre" placeholder="Carlos"></div>
          <div class="field-group"><label class="field-label">Apellido *</label><input class="field-input" id="nexp-apellido" placeholder="Méndez"></div>
        </div>
        <div class="field-row">
          <div class="field-group"><label class="field-label">Email *</label><input class="field-input" type="email" id="nexp-email" placeholder="correo@email.com"></div>
          <div class="field-group"><label class="field-label">Teléfono</label><input class="field-input" id="nexp-tel" placeholder="+1 809-555-0000"></div>
        </div>
        <div class="field-row">
          <div class="field-group">
            <label class="field-label">País de origen</label>
            <select class="field-select" id="nexp-pais">
              <option value="">Seleccionar…</option>
              ${['Rep. Dominicana','USA','España','México','Colombia','Brasil','Francia','UAE','Argentina','Otro'].map(p=>`<option>${p}</option>`).join('')}
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Fuente</label>
            <select class="field-select" id="nexp-fuente">
              <option value="">Seleccionar…</option>
              ${['Web','Instagram','Referido','Feria inmobiliaria','Llamada directa','Otro'].map(f=>`<option>${f}</option>`).join('')}
            </select>
          </div>
        </div>
        <div class="field-row">
          <div class="field-group">
            <label class="field-label">Agente asignado *</label>
            <select class="field-select" id="nexp-agente">
              <option value="">Seleccionar…</option>
              <option>Ana Rodríguez</option>
              <option>Carlos Ruiz</option>
              <option>María Fernández</option>
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Broker / Externo</label>
            <select class="field-select" id="nexp-broker">
              <option value="">Ninguno</option>
              ${brokers.map(b=>`<option value="${b.id}">${b.name} · ${b.agency}</option>`).join('')}
            </select>
          </div>
        </div>
      </div>

      <!-- ── Step 2: Unidad ── -->
      <div id="nexp-s2" style="display:none">
        <div style="display:flex;gap:16px">
          <div style="flex:1;overflow-y:auto;max-height:320px;display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:10px;padding-right:4px">
            ${availUnits.map(u => unitCard(u)).join('')}
          </div>
          <div id="nexp-udetail" style="width:210px;flex-shrink:0;background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:16px;display:flex;align-items:center;justify-content:center">
            <div style="text-align:center;color:var(--muted)">
              <div style="font-size:28px;margin-bottom:8px">↑</div>
              <div style="font-size:11px">Selecciona una unidad</div>
            </div>
          </div>
        </div>
        <div id="nexp-uselected" style="margin-top:12px;display:none;background:var(--green-lite);border:1px solid rgba(130,184,112,.3);border-radius:6px;padding:8px 12px;font-size:12px;color:var(--green-txt)"></div>
      </div>

      <!-- ── Step 3: Plan de pago ── -->
      <div id="nexp-s3" style="display:none">
        <div id="nexp-unit-recap" style="background:var(--bg-card2);border:1px solid var(--border);border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:var(--sub)">Sin unidad seleccionada</div>
        <div style="display:flex;gap:20px">
          <div style="flex:1">
            <div class="field-row">
              <div class="field-group">
                <label class="field-label">Descuento (%)</label>
                <input class="field-input" type="number" id="nexp-descuento" value="0" min="0" max="20" oninput="_nexpUpdateCalc()">
              </div>
              <div class="field-group">
                <label class="field-label">% Reserva inicial</label>
                <input class="field-input" type="number" id="nexp-reserva-pct" value="5" min="1" max="30" oninput="_nexpUpdateCalc()">
              </div>
            </div>
            <div class="field-row">
              <div class="field-group">
                <label class="field-label">Cuotas mensuales</label>
                <select class="field-select" id="nexp-cuotas" onchange="_nexpUpdateCalc()">
                  <option value="12">12 meses</option>
                  <option value="24" selected>24 meses</option>
                  <option value="36">36 meses</option>
                  <option value="48">48 meses</option>
                </select>
              </div>
              <div class="field-group">
                <label class="field-label">Primera cuota mensual</label>
                <input class="field-input" type="date" id="nexp-fecha-inicio">
              </div>
            </div>
          </div>
          <div style="width:210px;flex-shrink:0;background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:16px">
            <div style="font-size:11px;font-weight:600;color:var(--cream-dim);margin-bottom:12px;text-transform:uppercase;letter-spacing:.05em">Resumen financiero</div>
            <div id="nexp-calc" style="display:flex;flex-direction:column;gap:7px">
              <div style="display:flex;justify-content:space-between;font-size:11px"><span style="color:var(--sub)">Precio lista</span><span id="nc-lista" style="color:var(--cream-dim)">—</span></div>
              <div style="display:flex;justify-content:space-between;font-size:11px"><span style="color:var(--sub)">Descuento</span><span id="nc-desc" style="color:var(--orange)">—</span></div>
              <div style="border-top:1px solid var(--border);margin:4px 0"></div>
              <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600"><span style="color:var(--cream)">Precio final</span><span id="nc-final" style="color:var(--green-txt)">—</span></div>
              <div style="border-top:1px solid var(--border);margin:4px 0"></div>
              <div style="display:flex;justify-content:space-between;font-size:11px"><span style="color:var(--sub)">Cuota reserva</span><span id="nc-reserva" style="color:var(--cream-dim)">—</span></div>
              <div style="display:flex;justify-content:space-between;font-size:11px"><span style="color:var(--sub)">Saldo a financiar</span><span id="nc-saldo" style="color:var(--cream-dim)">—</span></div>
              <div style="display:flex;justify-content:space-between;font-size:11px"><span style="color:var(--sub)">Cuota mensual</span><span id="nc-cuota" style="color:var(--cream-dim)">—</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Step 4: Confirmar ── -->
      <div id="nexp-s4" style="display:none">
        <div style="display:flex;gap:16px">
          <div style="flex:1;background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:16px">
            <div style="font-size:11px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px">Datos del cliente</div>
            <div id="nexp-confirm-cliente" style="display:flex;flex-direction:column;gap:7px;font-size:12px"></div>
          </div>
          <div style="flex:1;background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:16px">
            <div style="font-size:11px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px">Unidad &amp; Plan de pago</div>
            <div id="nexp-confirm-unidad" style="display:flex;flex-direction:column;gap:7px;font-size:12px"></div>
          </div>
        </div>
        <div style="margin-top:14px;background:var(--green-lite);border:1px solid rgba(130,184,112,.25);border-radius:8px;padding:12px 16px;font-size:12px;color:var(--green-txt)">
          ✓ Al confirmar se creará el expediente, se asignará la unidad como <strong>Reservada</strong> y se notificará al agente seleccionado.
        </div>
      </div>

    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-ghost btn-sm" id="nexp-btn-back" style="display:none" onclick="_nexpBack()">← Atrás</button>
      <button class="btn btn-primary btn-sm" id="nexp-btn-next" onclick="_nexpNext()">Siguiente →</button>
    </div>
  </div>`);

  // ── State ──
  let currentStep = 1;
  let selectedUnitId = null;

  const fmt = n => '$' + Math.round(n).toLocaleString('en-US');

  function goTo(step) {
    for (let i = 1; i <= 4; i++) {
      const s = document.getElementById(`nexp-s${i}`);
      if (s) s.style.display = i === step ? '' : 'none';
      const dot = document.getElementById(`nexp-dot-${i}`);
      const lbl = document.getElementById(`nexp-lbl-${i}`);
      if (dot) {
        dot.classList.toggle('active', i <= step);
        dot.classList.toggle('done', i < step);
      }
      if (lbl) lbl.classList.toggle('active', i === step);
    }
    const btnBack = document.getElementById('nexp-btn-back');
    const btnNext = document.getElementById('nexp-btn-next');
    if (btnBack) btnBack.style.display = step > 1 ? '' : 'none';
    if (btnNext) btnNext.textContent = step === 4 ? '✓ Crear expediente' : 'Siguiente →';

    if (step === 3) updateRecap();
    if (step === 4) buildConfirm();
    currentStep = step;
  }

  function updateRecap() {
    const unit = availUnits.find(u => u.id === selectedUnitId);
    const recap = document.getElementById('nexp-unit-recap');
    if (recap && unit) {
      recap.innerHTML = `<strong style="color:var(--cream-dim)">${unit.num}</strong> · ${unit.type} · Planta ${unit.floor} · ${unit.bed}b ${unit.bath}ba · <strong style="color:var(--green-txt)">$${unit.price.toLocaleString()}</strong>`;
    }
    updateCalc();
  }

  function updateCalc() {
    const unit = availUnits.find(u => u.id === selectedUnitId);
    if (!unit) return;
    const descPct  = parseFloat(document.getElementById('nexp-descuento')?.value)    || 0;
    const resPct   = parseFloat(document.getElementById('nexp-reserva-pct')?.value)  || 5;
    const cuotas   = parseInt(document.getElementById('nexp-cuotas')?.value)         || 24;
    const precio   = unit.price;
    const descMon  = precio * descPct / 100;
    const final    = precio - descMon;
    const reserva  = final * resPct / 100;
    const saldo    = final - reserva;
    const cuota    = saldo / cuotas;
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set('nc-lista',  fmt(precio));
    set('nc-desc',   descPct > 0 ? `-${fmt(descMon)}` : '—');
    set('nc-final',  fmt(final));
    set('nc-reserva',fmt(reserva));
    set('nc-saldo',  fmt(saldo));
    set('nc-cuota',  fmt(cuota) + ` × ${cuotas}`);
  }

  function buildConfirm() {
    const unit     = availUnits.find(u => u.id === selectedUnitId);
    const nombre   = document.getElementById('nexp-nombre')?.value   || '—';
    const apellido = document.getElementById('nexp-apellido')?.value || '—';
    const email    = document.getElementById('nexp-email')?.value    || '—';
    const tel      = document.getElementById('nexp-tel')?.value      || '—';
    const pais     = document.getElementById('nexp-pais')?.value     || '—';
    const fuente   = document.getElementById('nexp-fuente')?.value   || '—';
    const agente   = document.getElementById('nexp-agente')?.value   || '—';
    const brokerEl = document.getElementById('nexp-broker');
    const brokerTxt = brokerEl?.options[brokerEl.selectedIndex]?.text || 'Ninguno';
    const descPct  = document.getElementById('nexp-descuento')?.value  || '0';
    const resPct   = document.getElementById('nexp-reserva-pct')?.value || '5';
    const cuotas   = document.getElementById('nexp-cuotas')?.value      || '24';
    const fInicio  = document.getElementById('nexp-fecha-inicio')?.value || '—';

    const row = (l, v) => `<div style="display:flex;justify-content:space-between;gap:8px"><span style="color:var(--sub)">${l}</span><span style="color:var(--cream-dim);font-weight:500;text-align:right">${v}</span></div>`;

    const cc = document.getElementById('nexp-confirm-cliente');
    if (cc) cc.innerHTML = [
      row('Nombre',  `${nombre} ${apellido}`),
      row('Email',   email),
      row('Teléfono',tel),
      row('País',    pais),
      row('Fuente',  fuente),
      row('Agente',  agente),
      row('Broker',  brokerTxt),
    ].join('');

    const cu = document.getElementById('nexp-confirm-unidad');
    if (cu && unit) cu.innerHTML = [
      row('Unidad',   unit.num),
      row('Tipo',     unit.type),
      row('Precio',   `$${unit.price.toLocaleString()}`),
      row('Descuento',`${descPct}%`),
      row('Reserva',  `${resPct}% al firmar`),
      row('Plan',     `${cuotas} cuotas mensuales`),
      row('Inicio',   fInicio),
    ].join('');
  }

  window._nexpGoTo      = (s) => goTo(s);
  window._nexpBack      = ()  => goTo(currentStep - 1);
  window._nexpUpdateCalc = ()  => updateCalc();

  window._nexpSelectUnit = (id) => {
    selectedUnitId = id;
    availUnits.forEach(u => {
      const card = document.getElementById(`nexp-ucard-${u.id}`);
      if (card) card.style.borderColor = u.id === id ? 'var(--green-txt)' : 'var(--border)';
    });
    const unit   = availUnits.find(u => u.id === id);
    const detail = document.getElementById('nexp-udetail');
    if (detail && unit) {
      detail.innerHTML = `
        <div>
          <div style="font-size:14px;font-weight:600;color:var(--cream-dim);margin-bottom:4px">${unit.num}</div>
          <div style="font-size:11px;color:var(--sub);margin-bottom:10px">${unit.type} · Planta ${unit.floor}</div>
          <div style="display:flex;flex-direction:column;gap:5px;font-size:11px">
            <div style="display:flex;justify-content:space-between"><span style="color:var(--sub)">Camas</span><span style="color:var(--cream-dim)">${unit.bed}</span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:var(--sub)">Baños</span><span style="color:var(--cream-dim)">${unit.bath}</span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:var(--sub)">Int.</span><span style="color:var(--cream-dim)">${unit.sqft.toLocaleString()} sqft</span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:var(--sub)">Terraza</span><span style="color:var(--cream-dim)">${unit.sqftTer} sqft</span></div>
          </div>
          <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--border)">
            <div style="font-size:16px;font-weight:700;color:var(--green-txt)">$${unit.price.toLocaleString()}</div>
          </div>
        </div>`;
    }
    const sel = document.getElementById('nexp-uselected');
    if (sel) { sel.style.display = ''; sel.textContent = `✓ Seleccionada: ${unit?.num}`; }
  };

  window._nexpNext = () => {
    if (currentStep === 1) {
      const nombre = document.getElementById('nexp-nombre')?.value.trim();
      const email  = document.getElementById('nexp-email')?.value.trim();
      const agente = document.getElementById('nexp-agente')?.value;
      if (!nombre || !email || !agente) { showToast('Completa nombre, email y agente', 'var(--orange)'); return; }
      goTo(2);
    } else if (currentStep === 2) {
      if (!selectedUnitId) { showToast('Selecciona una unidad', 'var(--orange)'); return; }
      goTo(3);
    } else if (currentStep === 3) {
      goTo(4);
    } else if (currentStep === 4) {
      closeModal();
      showToast('✓ Expediente creado', 'var(--green-txt)');
    }
  };

  goTo(1);
}

export function modalNuevoUsuario() {
  openModal(`
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">👤 Nuevo usuario</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div class="field-row">
        <div class="field-group"><label class="field-label">Nombre *</label><input class="field-input" id="nu-nombre" placeholder="Carlos"></div>
        <div class="field-group"><label class="field-label">Apellido *</label><input class="field-input" id="nu-apellido" placeholder="Méndez"></div>
      </div>
      <div class="field-row">
        <div class="field-group"><label class="field-label">Email *</label><input class="field-input" type="email" id="nu-email" placeholder="correo@email.com"></div>
        <div class="field-group"><label class="field-label">Teléfono</label><input class="field-input" id="nu-tel" placeholder="+1 809-555-0000"></div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">País</label>
          <select class="field-select" id="nu-pais">
            <option value="">Seleccionar…</option>
            ${['Rep. Dominicana','USA','España','México','Colombia','Brasil','Francia','UAE','Argentina','Otro'].map(p=>`<option>${p}</option>`).join('')}
          </select>
        </div>
        <div class="field-group">
          <label class="field-label">Rol *</label>
          <select class="field-select" id="nu-rol">
            <option value="">Seleccionar…</option>
            <option value="buyer">Comprador</option>
            <option value="broker">Broker / Externo</option>
            <option value="senior_agent">Agente Senior</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Fuente</label>
          <select class="field-select" id="nu-fuente">
            <option value="">Seleccionar…</option>
            ${['Web','Instagram','Referido','Feria inmobiliaria','Llamada directa','Otro'].map(f=>`<option>${f}</option>`).join('')}
          </select>
        </div>
        <div class="field-group">
          <label class="field-label">Proyecto de interés</label>
          <select class="field-select" id="nu-proyecto">
            <option value="">Ninguno</option>
            <option>Makai Residences</option>
            <option>Naviva Residences</option>
            <option>LIV at Cap Cana</option>
          </select>
        </div>
      </div>
      <div class="field-group">
        <label class="field-label">Notas</label>
        <textarea class="field-input" id="nu-notas" rows="2" placeholder="Observaciones opcionales…" style="resize:vertical"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="
        const n=document.getElementById('nu-nombre')?.value.trim();
        const e=document.getElementById('nu-email')?.value.trim();
        const r=document.getElementById('nu-rol')?.value;
        if(!n||!e||!r){showToast('Completa nombre, email y rol','var(--orange)');return;}
        closeModal();showToast('✓ Usuario creado','var(--green-txt)');">
        Crear usuario
      </button>
    </div>
  </div>`);
}

export function modalRegistrarPago(plan, onConfirm) {
  const fmt          = n => '$' + Math.round(n).toLocaleString('en-US');
  const next         = plan ? plan.nextRow : null;
  const suggestedAmt = next ? next.scheduled : 0;
  const monthly      = plan ? plan.monthly   : 0;
  const clients      = window.DUNA_DATA?.clients || [];

  window._comprobante = null;

  openModal(`
  <div class="modal" style="max-width:900px">
    <div class="modal-header">
      <span class="modal-title">💳 Registrar pago</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div style="display:flex">
    <div style="flex:1;min-width:0;padding:20px 24px">

      ${!plan ? `
      <div class="field-group" style="margin-bottom:16px">
        <label class="field-label">Cliente *</label>
        <select class="field-select" id="reg-cliente">
          <option value="">Seleccionar cliente…</option>
          ${clients.map(c => `<option value="${c.id}">${c.firstName} ${c.lastName} · ${c.unit}</option>`).join('')}
        </select>
      </div>` : ''}

      ${next ? `
      <div style="background:var(--bg-surface);border-radius:8px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between">
        <div>
          <div style="font-size:10px;color:var(--sub);margin-bottom:2px">Próxima cuota programada</div>
          <div style="font-size:13px;font-weight:600;color:var(--cream-dim)">${next.label} · ${next.date}</div>
        </div>
        <div style="font-size:18px;font-weight:700;color:var(--orange);font-family:'Inter',sans-serif">${fmt(next.scheduled)}</div>
      </div>` : ''}

      <div class="field-row">
        <div class="field-group" style="flex:1.4">
          <label class="field-label">Monto recibido (USD)</label>
          <input class="field-input" type="number" id="reg-monto" value="${suggestedAmt || ''}" min="1"
            oninput="previewExtraPago(this.value, ${suggestedAmt}, ${monthly})"
            placeholder="0.00"
            style="font-size:16px;font-weight:600">
          <div style="display:flex;gap:6px;margin-top:6px">
            ${suggestedAmt ? `<button type="button" class="btn btn-ghost btn-xs" onclick="document.getElementById('reg-monto').value=${suggestedAmt};previewExtraPago(${suggestedAmt},${suggestedAmt},${monthly})">Exacto</button>` : ''}
            ${suggestedAmt ? `<button type="button" class="btn btn-ghost btn-xs" onclick="document.getElementById('reg-monto').value=${suggestedAmt * 2};previewExtraPago(${suggestedAmt * 2},${suggestedAmt},${monthly})">×2 cuotas</button>` : ''}
            ${suggestedAmt ? `<button type="button" class="btn btn-ghost btn-xs" onclick="document.getElementById('reg-monto').value=${suggestedAmt * 3};previewExtraPago(${suggestedAmt * 3},${suggestedAmt},${monthly})">×3 cuotas</button>` : ''}
          </div>
        </div>
        <div class="field-group">
          <label class="field-label">Fecha</label>
          <input class="field-input" type="date" id="reg-fecha" value="${new Date().toISOString().split('T')[0]}">
        </div>
      </div>

      <div id="extra-preview" style="display:none;padding:10px 14px;background:rgba(61,110,42,.06);border:1px solid rgba(61,110,42,.15);border-radius:8px;margin-bottom:12px;font-size:11px;color:var(--sub)">
        <div style="font-weight:600;color:var(--green-txt);margin-bottom:4px">Pago extra — distribución:</div>
        <div id="extra-preview-body"></div>
      </div>

      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Método de pago</label>
          <select class="field-select" id="reg-metodo">
            <option>Wire Transfer</option>
            <option>ACH</option>
            <option>Stripe</option>
            <option>Efectivo</option>
          </select>
        </div>
        <div class="field-group">
          <label class="field-label">Concepto</label>
          <input class="field-input" type="text" id="reg-concepto" value="${next ? next.label + ' — Plan de pagos' : 'Pago plan de pagos'}">
        </div>
      </div>

      <div class="field-group">
        <label class="field-label">Comprobante de pago <span style="color:var(--red)">*</span></label>
        <label id="comp-dropzone"
          style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;border:1.5px dashed var(--border);border-radius:8px;padding:18px 16px;cursor:pointer;transition:border-color .15s,background .15s"
          ondragover="event.preventDefault();this.style.borderColor='var(--green-txt)';this.style.background='rgba(61,110,42,.05)'"
          ondragleave="this.style.borderColor='var(--border)';this.style.background=''"
          ondrop="event.preventDefault();this.style.borderColor='var(--border)';this.style.background='';handleComprobanteUpload(event.dataTransfer.files[0])">
          <div id="comp-empty" style="display:flex;flex-direction:column;align-items:center;gap:4px">
            <span style="font-size:28px;opacity:.35">📎</span>
            <div style="font-size:12px;color:var(--sub)">Arrastra aquí o haz clic para seleccionar</div>
            <div style="font-size:10px;color:var(--muted)">PDF, JPG o PNG · máx. 10 MB</div>
          </div>
          <div id="comp-preview" style="display:none;width:100%;align-items:center;gap:12px;padding:4px 0">
            <span id="comp-icon" style="font-size:26px;flex-shrink:0">📄</span>
            <div style="flex:1;min-width:0">
              <div id="comp-name" style="font-size:12px;font-weight:600;color:var(--cream-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"></div>
              <div id="comp-size" style="font-size:10px;color:var(--muted);margin-top:1px"></div>
            </div>
            <button type="button" onclick="event.preventDefault();clearComprobante()"
              style="flex-shrink:0;background:transparent;border:1px solid var(--border);border-radius:4px;color:var(--muted);cursor:pointer;padding:2px 6px;font-size:11px">✕</button>
          </div>
          <input type="file" id="reg-comprobante" accept=".pdf,.jpg,.jpeg,.png,image/*" style="display:none"
            onchange="handleComprobanteUpload(this.files[0])">
        </label>
        <div id="comp-error" style="display:none;font-size:11px;color:var(--red);margin-top:4px">⚠ El comprobante es obligatorio para registrar el pago.</div>
      </div>

      <div class="field-group">
        <label class="field-label">Notas (opcional)</label>
        <textarea class="field-textarea" id="reg-notas" placeholder="Referencia bancaria, número de comprobante…" rows="2"></textarea>
      </div>
    </div>

    <div style="width:268px;flex-shrink:0;border-left:1px solid var(--border);padding:20px;background:rgba(255,255,255,.015)">
      <div style="font-size:11px;font-weight:600;color:var(--cream-dim);margin-bottom:3px">Datos bancarios</div>
      <div style="font-size:9.5px;color:var(--sub);margin-bottom:16px;letter-spacing:.02em">Wire transfer · USD</div>

      <div style="font-size:8px;font-weight:600;color:var(--green-txt);letter-spacing:.18em;text-transform:uppercase;margin-bottom:8px">Banco Intermediario</div>
      <div style="font-size:10.5px;font-weight:600;color:var(--cream-dim);margin-bottom:8px">Citibank N.A. — New York Branch</div>
      <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border);font-size:9.5px"><span style="color:var(--sub)">Cuenta</span><span style="color:var(--cream-dim);font-weight:600;font-family:monospace">36265334</span></div>
      <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border);font-size:9.5px"><span style="color:var(--sub)">Swift (BIC)</span><span style="color:var(--cream-dim);font-weight:600;font-family:monospace">CITIUS33XXX</span></div>
      <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border);font-size:9.5px"><span style="color:var(--sub)">ABA Routing</span><span style="color:var(--cream-dim);font-weight:600;font-family:monospace">021000089</span></div>
      <div style="padding:5px 0;border-bottom:1px solid var(--border);font-size:9px;color:var(--sub)">111 Wall St, New York, USA 10043</div>

      <div style="font-size:8px;font-weight:600;color:var(--green-txt);letter-spacing:.18em;text-transform:uppercase;margin-top:16px;margin-bottom:8px">Banco Beneficiario</div>
      <div style="font-size:10px;font-weight:500;color:var(--cream-dim);margin-bottom:4px">Banco Múltiple López de Haro, S.A.</div>
      <div style="font-size:9px;color:var(--sub);border-bottom:1px solid var(--border);padding-bottom:10px">Ave. Sarasota No. 20, Santo Domingo, Rep. Dom.</div>

      <div style="font-size:8px;font-weight:600;color:var(--green-txt);letter-spacing:.18em;text-transform:uppercase;margin-top:16px;margin-bottom:8px">Cuenta a Acreditar</div>
      <div style="font-size:9.5px;font-weight:500;color:var(--cream-dim);margin-bottom:5px;line-height:1.4">IGUANAS LAKE CONDO &amp; RESIDENCE SRL</div>
      <div style="font-size:14px;font-weight:700;color:var(--cream);font-family:monospace;letter-spacing:.04em;padding:8px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">4010388162</div>

      <div style="margin-top:16px;padding:10px 12px;background:rgba(201,124,64,.08);border:1px solid rgba(201,124,64,.2);border-radius:6px;font-size:9px;color:var(--orange);line-height:1.55">
        <span style="font-weight:700">Referencia obligatoria:</span> Incluir nombre del cliente y número de unidad en el campo de referencia para evitar la devolución de fondos.
      </div>
    </div>

    </div><!-- /flex wrapper -->
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="confirmarPago()">✓ Confirmar pago</button>
    </div>
  </div>`);

  window.handleComprobanteUpload = (file) => {
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) { alert('El archivo supera los 10 MB permitidos.'); return; }
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['pdf','jpg','jpeg','png'].includes(ext)) { alert('Solo se permiten archivos PDF, JPG o PNG.'); return; }
    window._comprobante = file;
    const icon = ext === 'pdf' ? '📄' : '🖼';
    const size = file.size < 1024 * 1024 ? (file.size / 1024).toFixed(0) + ' KB' : (file.size / 1024 / 1024).toFixed(1) + ' MB';
    document.getElementById('comp-empty').style.display   = 'none';
    document.getElementById('comp-preview').style.display = 'flex';
    document.getElementById('comp-icon').textContent      = icon;
    document.getElementById('comp-name').textContent      = file.name;
    document.getElementById('comp-size').textContent      = `${size} · ${ext.toUpperCase()}`;
    document.getElementById('comp-error').style.display   = 'none';
    document.getElementById('comp-dropzone').style.borderColor = 'var(--green-txt)';
    document.getElementById('comp-dropzone').style.background  = 'rgba(61,110,42,.04)';
  };

  window.clearComprobante = () => {
    window._comprobante = null;
    const inp = document.getElementById('reg-comprobante');
    if (inp) inp.value = '';
    document.getElementById('comp-empty').style.display   = '';
    document.getElementById('comp-preview').style.display = 'none';
    document.getElementById('comp-dropzone').style.borderColor = 'var(--border)';
    document.getElementById('comp-dropzone').style.background  = '';
  };

  window.previewExtraPago = (amt, scheduled, mon) => {
    const extra   = parseFloat(amt) - scheduled;
    const preview = document.getElementById('extra-preview');
    const body    = document.getElementById('extra-preview-body');
    if (!preview || !body) return;
    if (extra <= 0 || !mon) { preview.style.display = 'none'; return; }
    const cuotasExtra = Math.floor(extra / mon);
    const rem         = extra % mon;
    let html = '';
    if (cuotasExtra > 0) html += `<div>· Cubre <strong>${cuotasExtra}</strong> cuota${cuotasExtra > 1 ? 's' : ''} adicional${cuotasExtra > 1 ? 'es' : ''} completa${cuotasExtra > 1 ? 's' : ''} ($${Math.round(cuotasExtra * mon).toLocaleString('en-US')})</div>`;
    if (rem > 0)          html += `<div>· Abono parcial de <strong>$${Math.round(rem).toLocaleString('en-US')}</strong> en la cuota siguiente</div>`;
    body.innerHTML = html;
    preview.style.display = 'block';
  };

  window.confirmarPago = () => {
    const amt      = parseFloat(document.getElementById('reg-monto')?.value || 0);
    const concept  = document.getElementById('reg-concepto')?.value  || 'Pago registrado';
    const method   = document.getElementById('reg-metodo')?.value    || 'Wire Transfer';
    if (!amt || amt <= 0) { alert('Ingresa un monto válido'); return; }
    if (!window._comprobante) {
      document.getElementById('comp-error').style.display   = 'block';
      document.getElementById('comp-dropzone').style.borderColor = 'var(--red)';
      return;
    }
    const comprobante = window._comprobante.name;
    window._comprobante = null;
    if (onConfirm) onConfirm(amt, concept, method, comprobante);
    closeModal();
    showToast(`✓ Pago de $${Math.round(amt).toLocaleString('en-US')} registrado`, 'var(--green-txt)');
  };
}

export function modalSubirDocumento() {
  const clients = window.DUNA_DATA?.clients || [];
  openModal(`
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">📎 Subir documento</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Tipo *</label>
          <select class="field-select" id="sd-tipo" onchange="_sdAutoName()">
            <option value="">Seleccionar…</option>
            ${['KYC','Contrato','Promesa','Reserva','Plan de Pagos','Identificación','Otro'].map(t=>`<option>${t}</option>`).join('')}
          </select>
        </div>
        <div class="field-group">
          <label class="field-label">Cliente</label>
          <select class="field-select" id="sd-cliente" onchange="_sdAutoName()">
            <option value="">Sin cliente (general)</option>
            ${clients.map(c=>`<option value="${c.id}">${c.firstName} ${c.lastName}</option>`).join('')}
          </select>
        </div>
      </div>
      <div class="field-group">
        <label class="field-label">Nombre del documento *</label>
        <input class="field-input" id="sd-nombre" placeholder="KYC — Carlos Méndez">
      </div>
      <div class="field-group">
        <label class="field-label">Archivo *</label>
        <label id="sd-dropzone"
          style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;border:1.5px dashed var(--border);border-radius:8px;padding:22px 16px;cursor:pointer;transition:border-color .15s,background .15s"
          ondragover="event.preventDefault();this.style.borderColor='var(--green-txt)';this.style.background='rgba(61,110,42,.05)'"
          ondragleave="this.style.borderColor='var(--border)';this.style.background=''"
          ondrop="event.preventDefault();this.style.borderColor='var(--border)';this.style.background='';_sdHandleFile(event.dataTransfer.files[0])">
          <div id="sd-empty" style="display:flex;flex-direction:column;align-items:center;gap:4px">
            <span style="font-size:32px;opacity:.3">📁</span>
            <div style="font-size:12px;color:var(--sub)">Arrastra aquí o haz clic para seleccionar</div>
            <div style="font-size:10px;color:var(--muted)">PDF, JPG, PNG, DOCX · máx. 20 MB</div>
          </div>
          <div id="sd-preview" style="display:none;width:100%;align-items:center;gap:12px;padding:4px 0">
            <span id="sd-ficon" style="font-size:26px;flex-shrink:0">📄</span>
            <div style="flex:1;min-width:0">
              <div id="sd-fname" style="font-size:12px;font-weight:600;color:var(--cream-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"></div>
              <div id="sd-fsize" style="font-size:10px;color:var(--muted);margin-top:1px"></div>
            </div>
            <button type="button" onclick="event.preventDefault();_sdClearFile()"
              style="flex-shrink:0;background:transparent;border:1px solid var(--border);border-radius:4px;color:var(--muted);cursor:pointer;padding:2px 6px;font-size:11px">✕</button>
          </div>
          <input type="file" id="sd-file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display:none" onchange="_sdHandleFile(this.files[0])">
        </label>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Vencimiento</label>
          <input class="field-input" type="date" id="sd-vence">
        </div>
        <div class="field-group">
          <label class="field-label">Estado inicial</label>
          <select class="field-select" id="sd-status">
            <option value="pending">Pendiente revisión</option>
            <option value="approved">Aprobado</option>
            <option value="signature">Por firmar</option>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="_sdConfirmar()">📎 Subir documento</button>
    </div>
  </div>`);

  window._sdFile = null;

  window._sdAutoName = () => {
    const tipo      = document.getElementById('sd-tipo')?.value;
    const clientSel = document.getElementById('sd-cliente');
    const nombre    = document.getElementById('sd-nombre');
    if (!nombre || nombre.value) return;
    const clientTxt = clientSel?.options[clientSel.selectedIndex]?.text;
    if (tipo && clientTxt && clientSel?.value) nombre.value = `${tipo} — ${clientTxt}`;
    else if (tipo) nombre.value = tipo;
  };

  window._sdHandleFile = (file) => {
    if (!file) return;
    if (file.size > 20 * 1024 * 1024) { alert('El archivo supera los 20 MB permitidos.'); return; }
    window._sdFile = file;
    const ext   = file.name.split('.').pop().toLowerCase();
    const icons = { pdf:'📄', jpg:'🖼', jpeg:'🖼', png:'🖼', doc:'📝', docx:'📝' };
    const size  = file.size < 1024 * 1024 ? (file.size / 1024).toFixed(0) + ' KB' : (file.size / 1024 / 1024).toFixed(1) + ' MB';
    document.getElementById('sd-empty').style.display   = 'none';
    document.getElementById('sd-preview').style.display = 'flex';
    document.getElementById('sd-ficon').textContent     = icons[ext] || '📄';
    document.getElementById('sd-fname').textContent     = file.name;
    document.getElementById('sd-fsize').textContent     = `${size} · ${ext.toUpperCase()}`;
    document.getElementById('sd-dropzone').style.borderColor = 'var(--green-txt)';
    const nombre = document.getElementById('sd-nombre');
    if (nombre && !nombre.value) nombre.value = file.name.replace(/\.[^.]+$/, '');
  };

  window._sdClearFile = () => {
    window._sdFile = null;
    const inp = document.getElementById('sd-file');
    if (inp) inp.value = '';
    document.getElementById('sd-empty').style.display   = '';
    document.getElementById('sd-preview').style.display = 'none';
    document.getElementById('sd-dropzone').style.borderColor = 'var(--border)';
  };

  window._sdConfirmar = () => {
    const tipo   = document.getElementById('sd-tipo')?.value;
    const nombre = document.getElementById('sd-nombre')?.value.trim();
    if (!tipo)          { showToast('Selecciona el tipo de documento', 'var(--orange)'); return; }
    if (!nombre)        { showToast('Ingresa un nombre para el documento', 'var(--orange)'); return; }
    if (!window._sdFile){ showToast('Selecciona un archivo', 'var(--orange)'); return; }
    closeModal();
    showToast('✓ Documento subido correctamente', 'var(--green-txt)');
  };
}

export function modalNuevaUnidad() {
  openModal(`
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">🏠 Nueva unidad</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div style="background:var(--bg-surface);border-radius:6px;padding:8px 12px;margin-bottom:16px;font-size:11px;color:var(--sub)">
        Proyecto: <strong style="color:var(--cream-dim)">Makai Residences</strong> · Cap Cana, Punta Cana
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Número de unidad *</label>
          <input class="field-input" id="nu2-num" placeholder="ej. 501">
        </div>
        <div class="field-group">
          <label class="field-label">Tipo *</label>
          <select class="field-select" id="nu2-tipo">
            <option value="">Seleccionar…</option>
            <option>Studio</option>
            <option>1 Bedroom</option>
            <option>2 Bedrooms</option>
            <option>3 Bedrooms</option>
            <option>Penthouse</option>
          </select>
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Planta</label>
          <input class="field-input" type="number" id="nu2-planta" placeholder="5" min="1">
        </div>
        <div class="field-group">
          <label class="field-label">Estado inicial</label>
          <select class="field-select" id="nu2-status">
            <option value="available">Disponible</option>
            <option value="pending">Reservada</option>
          </select>
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Camas</label>
          <input class="field-input" type="number" id="nu2-bed" placeholder="2" min="0" max="6">
        </div>
        <div class="field-group">
          <label class="field-label">Baños</label>
          <input class="field-input" type="number" id="nu2-bath" placeholder="2" min="0" max="6">
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">sqft interior</label>
          <input class="field-input" type="number" id="nu2-sqft" placeholder="1200" min="1">
        </div>
        <div class="field-group">
          <label class="field-label">sqft terraza</label>
          <input class="field-input" type="number" id="nu2-sqftter" placeholder="350" min="0">
        </div>
      </div>
      <div class="field-group">
        <label class="field-label">Precio lista (USD) *</label>
        <input class="field-input" type="number" id="nu2-precio" placeholder="380000" min="1"
          style="font-size:16px;font-weight:600" oninput="
            const v=parseInt(this.value)||0;
            const el=document.getElementById('nu2-precio-preview');
            if(el) el.textContent=v>0?'$'+v.toLocaleString('en-US'):'';
          ">
        <div id="nu2-precio-preview" style="font-size:13px;color:var(--green-txt);font-weight:600;margin-top:4px;height:16px"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="
        const num   = document.getElementById('nu2-num')?.value.trim();
        const tipo  = document.getElementById('nu2-tipo')?.value;
        const precio= parseFloat(document.getElementById('nu2-precio')?.value||0);
        if(!num)   { showToast('Ingresa el número de unidad','var(--orange)'); return; }
        if(!tipo)  { showToast('Selecciona el tipo','var(--orange)'); return; }
        if(!precio){ showToast('Ingresa el precio','var(--orange)'); return; }
        closeModal(); showToast('✓ Unidad '+num+' creada','var(--green-txt)');
      ">Crear unidad</button>
    </div>
  </div>`);
}

export function modalPublicarReporte() {
  const phases  = window.DUNA_DATA?.construction?.makai?.phases || [];
  const overall = window.DUNA_DATA?.construction?.makai?.overall || 0;

  openModal(`
  <div class="modal modal-lg" style="max-width:640px">
    <div class="modal-header">
      <span class="modal-title">📊 Publicar reporte de avance</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div style="background:var(--bg-surface);border-radius:6px;padding:8px 12px;margin-bottom:16px;font-size:11px;color:var(--sub)">
        Proyecto: <strong style="color:var(--cream-dim)">Makai Residences</strong> · Cap Cana, Punta Cana
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Período *</label>
          <input class="field-input" id="pr-periodo" placeholder="ej. Junio 2026">
        </div>
        <div class="field-group">
          <label class="field-label">Tipo de reporte</label>
          <select class="field-select" id="pr-tipo">
            <option>Mensual</option>
            <option>Trimestral</option>
            <option>Especial</option>
          </select>
        </div>
      </div>
      <div class="field-group">
        <label class="field-label">Título</label>
        <input class="field-input" id="pr-titulo" value="Reporte mensual">
      </div>

      <!-- Avance por fase -->
      <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:14px 16px;margin-bottom:14px">
        <div style="font-size:11px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px">Avance por fase</div>
        ${phases.map((p, i) => `
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:${i < phases.length-1 ? '10px' : '0'}">
            <div style="width:110px;font-size:11px;color:${p.status==='done'?'var(--green-txt)':p.status==='active'?'var(--cream-dim)':'var(--muted)'}">
              ${p.status==='done'?'✓ ':''}${p.name}
            </div>
            ${p.status === 'done' ? `
              <div style="flex:1;height:5px;background:var(--green-txt);border-radius:3px;opacity:.4"></div>
              <span style="font-size:11px;color:var(--green-txt);width:36px;text-align:right">100%</span>
            ` : `
              <div style="flex:1;position:relative">
                <input type="range" id="pr-phase-${i}" min="0" max="100" value="${p.pct}"
                  style="width:100%;accent-color:var(--green-txt)"
                  oninput="document.getElementById('pr-phase-val-${i}').textContent=this.value+'%';_prUpdateOverall()">
              </div>
              <span id="pr-phase-val-${i}" style="font-size:11px;color:var(--cream-dim);width:36px;text-align:right">${p.pct}%</span>
            `}
          </div>`).join('')}
        <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
          <span style="font-size:11px;color:var(--sub)">Avance global calculado</span>
          <span id="pr-overall-val" style="font-size:16px;font-weight:700;color:var(--green-txt)">${overall}%</span>
        </div>
      </div>

      <div class="field-group">
        <label class="field-label">Descripción / resumen del período *</label>
        <textarea class="field-input" id="pr-desc" rows="3" placeholder="Describe los avances más importantes de este período…" style="resize:vertical"></textarea>
      </div>

      <!-- Fotos del avance -->
      <div class="field-group">
        <label class="field-label" style="display:flex;justify-content:space-between;align-items:center">
          <span>Fotos del avance</span>
          <span id="pr-foto-count" style="font-size:10px;color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0">0 / 8 fotos</span>
        </label>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:8px" id="pr-foto-grid">
          ${Array(8).fill(0).map((_, fi) => `
            <div id="pr-slot-${fi}"
              onclick="_prSelectFoto(${fi})"
              onmouseover="if(!this.dataset.uploaded)this.style.borderColor='rgba(130,184,112,.4)'"
              onmouseout="if(!this.dataset.uploaded)this.style.borderColor='rgba(255,255,255,.08)'"
              style="aspect-ratio:1;background:var(--bg-surface);border:1.5px dashed rgba(255,255,255,.08);border-radius:6px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;cursor:pointer;transition:all .12s">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.22)" stroke-width="1.5" stroke-linecap="round">
                <rect x="2" y="8" width="20" height="14" rx="2"/>
                <path d="M16 8V6a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                <circle cx="12" cy="15" r="2"/>
              </svg>
              <span style="font-size:9px;color:var(--muted)">Subir</span>
            </div>
          `).join('')}
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:10px;color:var(--muted)">JPG, PNG, WebP · Máx. 10 MB por foto</span>
          <button class="btn btn-ghost btn-sm" style="font-size:10px;padding:3px 10px" onclick="alert('Importar desde Google Drive…')">🔗 Drive</button>
        </div>
      </div>

      <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px 12px;background:var(--green-lite);border-radius:8px;border:1px solid rgba(130,184,112,.2)">
        <input type="checkbox" id="pr-notificar" checked style="width:14px;height:14px;accent-color:var(--green-txt)">
        <div>
          <div style="font-size:12px;font-weight:500;color:var(--green-txt)">Notificar a compradores activos</div>
          <div style="font-size:10px;color:var(--sub)">Se enviará por Email y WhatsApp según configuración de plantillas</div>
        </div>
      </label>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="_prConfirmar()">📤 Publicar reporte</button>
    </div>
  </div>`);

  window._prUpdateOverall = () => {
    const phases2 = window.DUNA_DATA?.construction?.makai?.phases || [];
    let total = 0;
    phases2.forEach((p, i) => {
      const el = document.getElementById(`pr-phase-${i}`);
      total += el ? parseFloat(el.value) : p.pct;
    });
    const avg = Math.round(total / Math.max(phases2.length, 1));
    const el  = document.getElementById('pr-overall-val');
    if (el) el.textContent = avg + '%';
  };

  window._prConfirmar = () => {
    const periodo = document.getElementById('pr-periodo')?.value.trim();
    const desc    = document.getElementById('pr-desc')?.value.trim();
    if (!periodo) { showToast('Ingresa el período del reporte', 'var(--orange)'); return; }
    if (!desc)    { showToast('Agrega una descripción del período', 'var(--orange)'); return; }
    const fotos    = document.querySelectorAll('#pr-foto-grid [data-uploaded="true"]').length;
    const notificar = document.getElementById('pr-notificar')?.checked;
    closeModal();
    const fotoTxt = fotos > 0 ? ` · ${fotos} foto${fotos > 1 ? 's' : ''} adjunta${fotos > 1 ? 's' : ''}` : '';
    showToast(notificar ? `✓ Reporte publicado y compradores notificados${fotoTxt}` : `✓ Reporte publicado${fotoTxt}`, 'var(--green-txt)');
  };

  const _prGradients = [
    'linear-gradient(135deg,#3d4a2e,#2a3620)',
    'linear-gradient(135deg,#2a3a4a,#1a2a3a)',
    'linear-gradient(135deg,#4a3d2e,#3a2d1e)',
    'linear-gradient(135deg,#3a3d2a,#2a2d1a)',
    'linear-gradient(135deg,#2e3d4a,#1e2d3a)',
    'linear-gradient(135deg,#3d2e4a,#2d1e3a)',
    'linear-gradient(135deg,#4a4a2e,#3a3a1e)',
    'linear-gradient(135deg,#2e4a3d,#1e3a2d)',
  ];

  window._prSelectFoto = (idx) => {
    const slot = document.getElementById('pr-slot-' + idx);
    if (!slot) return;
    if (slot.dataset.uploaded === 'true') {
      slot.dataset.uploaded = 'false';
      slot.style.background = 'var(--bg-surface)';
      slot.style.borderStyle = 'dashed';
      slot.style.borderColor = 'rgba(255,255,255,.08)';
      slot.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.22)" stroke-width="1.5" stroke-linecap="round">
          <rect x="2" y="8" width="20" height="14" rx="2"/>
          <path d="M16 8V6a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
          <circle cx="12" cy="15" r="2"/>
        </svg>
        <span style="font-size:9px;color:var(--muted)">Subir</span>`;
    } else {
      slot.dataset.uploaded = 'true';
      slot.style.background = _prGradients[idx % _prGradients.length];
      slot.style.borderStyle = 'solid';
      slot.style.borderColor = 'rgba(130,184,112,.5)';
      slot.innerHTML = `
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.85)" stroke-width="3" stroke-linecap="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        <span style="font-size:8px;color:rgba(255,255,255,.55)">foto_${idx + 1}.jpg</span>`;
    }
    const count = document.querySelectorAll('#pr-foto-grid [data-uploaded="true"]').length;
    const el = document.getElementById('pr-foto-count');
    if (el) el.textContent = count + ' / 8 fotos';
  };
}

export function modalVerReporte(idx) {
  const cdata   = window.DUNA_DATA?.construction?.makai;
  const reports = cdata?.reports || [];
  const phases  = cdata?.phases  || [];
  const overall = cdata?.overall || 52;
  const r = reports[idx] ?? reports[0];
  if (!r) return;

  const mockPhotos = [
    { label: 'Fachada principal',          gradient: 'linear-gradient(150deg,#3a4c2f 0%,#222f1a 100%)', icon: '🏗' },
    { label: 'Estructura nivel 3',         gradient: 'linear-gradient(150deg,#253444 0%,#141e28 100%)', icon: '🏗' },
    { label: 'Interior unidad 301',        gradient: 'linear-gradient(150deg,#44332a 0%,#2a1e17 100%)', icon: '🏠' },
    { label: 'Mampostería sector A',       gradient: 'linear-gradient(150deg,#38402a 0%,#22281a 100%)', icon: '🧱' },
    { label: 'Instalaciones eléctricas',   gradient: 'linear-gradient(150deg,#263444 0%,#161e28 100%)', icon: '⚡' },
    { label: 'Área común — acceso',        gradient: 'linear-gradient(150deg,#3a2a40 0%,#221828 100%)', icon: '🌿' },
  ];

  const phaseStatus = {
    done:    { color: 'var(--green-txt)', bg: 'var(--green-lite)',  check: true  },
    active:  { color: 'var(--orange)',    bg: 'var(--orange-lite)', check: false },
    pending: { color: 'var(--muted)',     bg: 'transparent',        check: false },
  };

  openModal(`
  <div class="modal modal-lg" style="max-width:780px">
    <div class="modal-header" style="align-items:flex-start">
      <div>
        <div style="font-size:10px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--sub);margin-bottom:4px">
          Makai Residences Cap Cana · Reporte de avance
        </div>
        <span class="modal-title">${r.period} — ${r.title}</span>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
        <span style="display:inline-block;padding:3px 10px;border-radius:4px;font-size:10px;font-weight:600;background:var(--green-lite);color:var(--green-txt)">✓ Publicado</span>
        <div class="modal-close" onclick="closeModal()">✕</div>
      </div>
    </div>

    <div class="modal-body" style="max-height:70vh;overflow-y:auto;display:flex;flex-direction:column;gap:16px">

      <!-- Resumen de progreso -->
      <div style="display:flex;align-items:center;gap:20px;background:var(--bg-surface);border:1px solid var(--border2);border-radius:10px;padding:18px 20px">
        <div style="position:relative;width:68px;height:68px;flex-shrink:0">
          <svg viewBox="0 0 68 68" style="transform:rotate(-90deg)">
            <circle cx="34" cy="34" r="27" fill="none" stroke="var(--border)" stroke-width="6"/>
            <circle cx="34" cy="34" r="27" fill="none" stroke="var(--green-txt)" stroke-width="6"
              stroke-dasharray="${(2 * Math.PI * 27).toFixed(1)}"
              stroke-dashoffset="${(2 * Math.PI * 27 * (1 - overall / 100)).toFixed(1)}"
              stroke-linecap="round"/>
          </svg>
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
            <span style="font-size:15px;font-weight:700;color:var(--cream)">${overall}%</span>
          </div>
        </div>
        <div style="flex:1">
          <div style="font-size:14px;font-weight:600;color:var(--cream);margin-bottom:3px">Avance global de obra</div>
          <div style="font-size:11px;color:var(--sub);margin-bottom:10px">Publicado el ${r.date} · 22 compradores notificados por Email y WhatsApp</div>
          <div style="height:5px;background:var(--border);border-radius:3px;overflow:hidden">
            <div style="width:${overall}%;height:100%;background:linear-gradient(90deg,var(--green),var(--green-txt));border-radius:3px"></div>
          </div>
          <div style="display:flex;justify-content:space-between;margin-top:5px">
            <span style="font-size:10px;color:var(--muted)">Inicio: Ene 2026</span>
            <span style="font-size:10px;color:var(--muted)">Entrega: Q4 2026</span>
          </div>
        </div>
      </div>

      <!-- Avance por fase -->
      <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:10px;padding:16px 18px">
        <div style="font-size:10px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--sub);margin-bottom:14px">Avance por fase</div>
        ${phases.map((p, i) => {
          const ps = phaseStatus[p.status] || phaseStatus.pending;
          return `
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:${i < phases.length - 1 ? '12px' : '0'}">
            <div style="width:16px;height:16px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;
              background:${ps.bg};border:1.5px solid ${ps.color}">
              ${ps.check ? `<svg width="7" height="7" viewBox="0 0 24 24" fill="none" stroke="${ps.color}" stroke-width="4" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>` : ''}
            </div>
            <span style="width:108px;font-size:12px;color:${p.status === 'pending' ? 'var(--muted)' : 'var(--cream-dim)'};flex-shrink:0">${p.name}</span>
            <div style="flex:1;height:4px;background:var(--border);border-radius:2px;overflow:hidden">
              <div style="width:${p.pct}%;height:100%;border-radius:2px;background:${p.status === 'done' ? 'var(--green-txt)' : p.status === 'active' ? 'var(--orange)' : 'transparent'}"></div>
            </div>
            <span style="font-size:11px;font-weight:600;color:${ps.color};width:32px;text-align:right;flex-shrink:0">${p.pct}%</span>
            <span style="font-size:10px;color:var(--muted);width:56px;text-align:right;flex-shrink:0">${p.date}</span>
          </div>`;
        }).join('')}
      </div>

      <!-- Novedades del período -->
      <div>
        <div style="font-size:10px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--sub);margin-bottom:8px">Novedades del período</div>
        <div style="background:var(--bg-surface);border:1px solid var(--border2);border-radius:8px;padding:14px 16px;font-size:13px;color:var(--text);line-height:1.7">${r.desc}</div>
      </div>

      <!-- Galería fotográfica -->
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
          <div style="font-size:10px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--sub)">Galería fotográfica</div>
          <span style="font-size:11px;color:var(--muted)">${mockPhotos.length} fotos adjuntas</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
          ${mockPhotos.map(ph => `
            <div style="border-radius:8px;overflow:hidden;cursor:pointer;position:relative;aspect-ratio:4/3"
              onclick="alert('Ver foto: ${ph.label}')"
              onmouseover="this.querySelector('.vr-overlay').style.opacity='1'"
              onmouseout="this.querySelector('.vr-overlay').style.opacity='0'">
              <div style="width:100%;height:100%;background:${ph.gradient};display:flex;align-items:flex-end">
                <div style="padding:6px 10px;width:100%;background:linear-gradient(to top,rgba(0,0,0,.65) 0%,transparent 100%)">
                  <div style="font-size:10px;color:rgba(255,255,255,.8)">${ph.label}</div>
                </div>
              </div>
              <div class="vr-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,.25);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .14s">
                <div style="background:rgba(0,0,0,.5);border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                  </svg>
                </div>
              </div>
            </div>
          `).join('')}
        </div>
      </div>

    </div>

    <div class="modal-footer" style="justify-content:space-between">
      <button class="btn btn-ghost btn-sm" onclick="alert('Notificación reenviada a 22 compradores')">
        Reenviar notificación
      </button>
      <div style="display:flex;gap:8px">
        <button class="btn btn-ghost btn-sm" onclick="openPublicarReporteModal()">Editar reporte</button>
        <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cerrar</button>
      </div>
    </div>
  </div>`);
}

export function modalNuevaTarea() {
  const clients = window.DUNA_DATA?.clients || [];
  openModal(`
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">✓ Nueva tarea</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div class="field-group">
        <label class="field-label">Descripción *</label>
        <textarea class="field-input" id="nt-texto" rows="2" placeholder="Describe la tarea…" style="resize:vertical"></textarea>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Prioridad</label>
          <select class="field-select" id="nt-prioridad">
            <option value="high">🔴 Alta</option>
            <option value="medium" selected>🟡 Media</option>
            <option value="low">🟢 Baja</option>
          </select>
        </div>
        <div class="field-group">
          <label class="field-label">Fecha límite</label>
          <input class="field-input" type="date" id="nt-fecha" value="${new Date().toISOString().split('T')[0]}">
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Cliente relacionado</label>
          <select class="field-select" id="nt-cliente">
            <option value="">Sin cliente</option>
            ${clients.map(c=>`<option value="${c.firstName} ${c.lastName}">${c.firstName} ${c.lastName}</option>`).join('')}
          </select>
        </div>
        <div class="field-group">
          <label class="field-label">Asignar a</label>
          <select class="field-select" id="nt-asignar">
            <option>Ana Rodríguez</option>
            <option>Carlos Ruiz</option>
            <option>María Fernández</option>
            <option>Admin Duna</option>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="
        const texto = document.getElementById('nt-texto')?.value.trim();
        if(!texto){ showToast('Escribe la descripción de la tarea','var(--orange)'); return; }
        const prio  = document.getElementById('nt-prioridad')?.value || 'medium';
        const fecha = document.getElementById('nt-fecha')?.value || 'Hoy';
        const cliente = document.getElementById('nt-cliente')?.value || '';
        const prioCls = prio==='high'?'prio-high':prio==='medium'?'prio-medium':'prio-low';
        const ul = document.getElementById('tasks-pending');
        if(ul){
          const li = document.createElement('li');
          li.className='task-item'; li.dataset.priority=prio;
          li.innerHTML=\`<div class='task-check' onclick='toggleTask(0)'></div><div class='task-prio \${prioCls}'></div><div style='flex:1'><div class='task-text'>\${texto}</div>\${cliente?'<div class=\\'task-meta\\'>👤 '+cliente+'</div>':''}</div><div style='text-align:right'><div class='task-meta'>\${fecha}</div></div>\`;
          ul.prepend(li);
        }
        closeModal(); showToast('✓ Tarea creada','var(--green-txt)');
      ">Crear tarea</button>
    </div>
  </div>`);
}

export function modalNuevaReserva(data) {
  const units   = data.units.filter(u => u.status === 'available');
  const clients = data.clients || [];

  // ── Wizard state ──────────────────────────────────────────────────────
  let nrStep = 1;
  const STEPS = ['Plan de pagos', 'Titulares', 'Pack bienvenida', 'Resumen global'];

  // ── Helpers ───────────────────────────────────────────────────────────
  const lbl = t => `<div style="font-size:10px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--sub);margin-bottom:5px">${t}</div>`;
  const inp = (id, val, ph = '', type = 'text', ro = false) =>
    `<input id="${id}" type="${type}" class="field-input" style="font-size:12px" value="${val}" placeholder="${ph}" ${ro ? 'readonly' : ''} oninput="nrCalc()">`;
  const sel = (id, opts) =>
    `<select id="${id}" class="field-select" style="font-size:12px">${opts.map(o => `<option>${o}</option>`).join('')}</select>`;
  const moneyInp = (id, val, ro = false) =>
    `<div style="display:flex;align-items:center;gap:4px;background:var(--bg-surface);border:1px solid var(--border);border-radius:5px;padding:7px 10px${ro?';opacity:.6':''}">
      <span style="color:var(--sub);font-size:12px;flex-shrink:0">$</span>
      <input id="${id}" type="number" style="background:none;border:none;outline:none;color:var(--cream);font-size:13px;font-family:var(--font-ui);width:100%" value="${val}" ${ro?'readonly':''} oninput="nrCalc()">
    </div>`;
  const pctInp = (id, val) =>
    `<div style="display:flex;align-items:center;gap:4px;background:var(--bg-surface);border:1px solid var(--border);border-radius:5px;padding:7px 10px">
      <input id="${id}" type="number" min="0" max="100" style="background:none;border:none;outline:none;color:var(--cream);font-size:13px;font-family:var(--font-ui);width:100%" value="${val}" oninput="nrCalc()">
      <span style="color:var(--sub);font-size:12px;flex-shrink:0">%</span>
    </div>`;
  const block = (accent, title, sub, body) =>
    `<div style="border:1px solid var(--border);border-left:3px solid ${accent};border-radius:8px;padding:16px 18px;background:var(--bg-card2)">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
        <div><div style="font-size:12px;font-weight:600;color:var(--cream-dim)">${title}</div><div style="font-size:11px;color:var(--sub);margin-top:2px">${sub}</div></div>
      </div>${body}</div>`;

  // ── Quarterly cuota dates ─────────────────────────────────────────────
  const mes = ['ene','feb','mar','abr','may','jun','jul','ago','sept','oct','nov','dic'];
  const cuotaDates = Array.from({length:13},(_,i)=>{ const d=new Date(2026,5,23); d.setMonth(d.getMonth()+(i+1)*3); return `${d.getDate()} ${mes[d.getMonth()]} ${d.getFullYear()}`; });

  // ── Step renders ──────────────────────────────────────────────────────
  const stepPlanPagos = () => `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
      <div class="field-group" style="margin:0">
        ${lbl('Cliente')}
        ${sel('nr-cliente', ['Seleccionar cliente…', ...clients.map(c=>`${c.firstName} ${c.lastName}`)])}
      </div>
      <div class="field-group" style="margin:0">
        ${lbl('Unidad')}
        ${sel('nr-unidad', ['Seleccionar unidad…', ...units.map(u=>`${u.num} — ${u.type} · $${u.price.toLocaleString('en-US')}`)])}
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:6px">
      <div>${lbl('Precio de lista (USD)')}${moneyInp('nr-lista',326000)}</div>
      <div>${lbl('Descuento (USD)')}${moneyInp('nr-desc',20000)}</div>
      <div>${lbl('Precio de venta')}${moneyInp('nr-venta',306000,true)}</div>
    </div>
    <div style="font-size:10px;color:var(--sub);margin-bottom:14px" id="nr-desc-pct">Descuento: 6.1% del precio de lista</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px">
      <div>${lbl('Down Payment %')}${pctInp('nr-dp-pct',30)}</div>
      <div>${lbl('Construcción %')}${pctInp('nr-co-pct',35)}</div>
      <div>${lbl('Cierre y Entrega %')}<div style="display:flex;align-items:center;gap:4px;background:var(--bg-surface);border:1px solid var(--border);border-radius:5px;padding:7px 10px;opacity:.6"><input id="nr-ce-pct" readonly style="background:none;border:none;outline:none;color:var(--orange);font-size:13px;font-family:var(--font-ui);width:100%" value="35%"></div></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
      ${block('var(--orange)','Down Payment','Inicial + cuotas',`
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
          <div>${lbl('Reserva inicial')}${moneyInp('nr-reserva',2000)}</div>
          <div>${lbl('Gastos registrales')}${moneyInp('nr-gastos',500)}</div>
          <div>${lbl('Saldo DP')}<div style="display:flex;align-items:center;background:var(--bg-surface);border:1px solid var(--border);border-radius:5px;padding:7px 10px;opacity:.6"><span style="color:var(--sub);font-size:12px;margin-right:4px">$</span><span id="nr-dp-saldo" style="font-size:13px;color:var(--cream)">89,500.00</span></div></div>
        </div>
        <div style="margin-top:10px">
          ${lbl('Fecha de pago')}${inp('nr-dp-fecha','2026-06-23','','date')}
        </div>
      `)}
      ${block('var(--blue)','Cuotas de construcción','13 cuotas trimestrales',`
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px">
          <div>${lbl('Monto total')}${moneyInp('nr-co-total',107100,true)}</div>
          <div>${lbl('Cuota mensual')}<div style="display:flex;align-items:center;background:var(--bg-surface);border:1px solid var(--border);border-radius:5px;padding:7px 10px;opacity:.6"><span style="color:var(--sub);font-size:12px;margin-right:4px">$</span><span id="nr-co-cuota" style="font-size:13px;color:var(--cream)">8,238.46</span></div></div>
        </div>
        <div style="max-height:120px;overflow-y:auto;display:flex;flex-direction:column;gap:4px">
          ${cuotaDates.map((f,i)=>`
            <div style="display:flex;justify-content:space-between;align-items:center;padding:4px 8px;background:var(--bg-surface);border-radius:4px">
              <span style="font-size:10px;color:var(--sub)">Cuota ${i+1}</span>
              <span style="font-size:10px;color:var(--muted)">${f}</span>
              <span style="font-size:10px;color:var(--cream-dim)" id="nr-cuota-${i}">$8,238.46</span>
            </div>`).join('')}
        </div>
      `)}
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
      ${block('var(--green-txt)','Cierre y Entrega','Al recibir escrituras',`
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          <div>${lbl('Monto total')}${moneyInp('nr-ce-total',107100,true)}</div>
          <div>${lbl('Fecha estimada')}${inp('nr-ce-fecha','2026-12-31','','date')}</div>
        </div>
      `)}
      <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:16px 18px">
        ${lbl('Verificación del plan')}
        <div style="display:flex;flex-direction:column;gap:6px;margin-top:6px">
          <div style="display:flex;justify-content:space-between;font-size:12px"><span style="color:var(--sub)">Precio de venta</span><span id="nr-v-venta" style="color:var(--cream-dim)">$306,000.00</span></div>
          <div style="display:flex;justify-content:space-between;font-size:12px"><span style="color:var(--sub)">Down Payment</span><span id="nr-v-dp" style="color:var(--cream-dim)">$91,800.00</span></div>
          <div style="display:flex;justify-content:space-between;font-size:12px"><span style="color:var(--sub)">Construcción</span><span id="nr-v-co" style="color:var(--cream-dim)">$107,100.00</span></div>
          <div style="display:flex;justify-content:space-between;font-size:12px"><span style="color:var(--sub)">Cierre y Entrega</span><span id="nr-v-ce" style="color:var(--cream-dim)">$107,100.00</span></div>
          <div style="height:1px;background:var(--border);margin:4px 0"></div>
          <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600"><span style="color:var(--sub)">Total</span><span id="nr-v-total" style="color:var(--green-txt)">$306,000.00 ✓</span></div>
        </div>
        <div id="nr-verif" style="margin-top:10px;font-size:10px;color:var(--green-txt)">✓ 30% + 35% + 35% = 100%</div>
      </div>
    </div>`;

  const stepTitulares = () => `
    <div style="margin-bottom:16px">
      <div style="font-size:11px;color:var(--sub);margin-bottom:12px">Titular principal y cotitulares de la propiedad. Todos deben ser incluidos en el contrato.</div>
      <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:16px 18px;margin-bottom:12px">
        <div style="font-size:11px;font-weight:600;color:var(--green-txt);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">Titular principal</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
          <div>${lbl('Nombre completo')}${inp('nr-t1-nombre','','Nombre y apellidos')}</div>
          <div>${lbl('Fecha de nacimiento')}${inp('nr-t1-dob','','','date')}</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:10px">
          <div>${lbl('Nacionalidad')}${inp('nr-t1-nac','','Italiana, Española…')}</div>
          <div>${lbl('Tipo de documento')}${sel('nr-t1-doc-tipo',['Pasaporte','Cédula','ID Nacional'])}</div>
          <div>${lbl('Número de documento')}${inp('nr-t1-doc','','AB 1234567')}</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div>${lbl('Email')}${inp('nr-t1-email','','email@ejemplo.com','email')}</div>
          <div>${lbl('Teléfono')}${inp('nr-t1-tel','','+1 809 000 0000')}</div>
        </div>
      </div>
      <div id="nr-titulares-extra"></div>
      <button onclick="nrAddTitular()" class="btn btn-ghost btn-sm" style="width:100%;margin-top:4px">+ Agregar cotitular</button>
    </div>
    <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:14px 18px">
      ${lbl('Porcentaje de participación')}
      <div style="font-size:11px;color:var(--sub);margin-top:4px">Si hay más de un titular, indica el % de participación de cada uno. El total debe sumar 100%.</div>
      <div style="display:flex;gap:8px;margin-top:10px;align-items:center">
        <div style="flex:1">${lbl('Titular 1')}${pctInp('nr-p1',100)}</div>
        <div id="nr-p-extra" style="flex:1;display:none">${lbl('Cotitular')}${pctInp('nr-p2',0)}</div>
        <div style="margin-top:16px;font-size:12px;color:var(--sub)">= 100%</div>
      </div>
    </div>`;

  const docCard = (icon, title, desc, trigger, signatories, idx) => `
    <div style="border:1px solid var(--border);border-radius:10px;padding:18px 20px;background:var(--bg-card2);display:flex;align-items:flex-start;gap:16px">
      <div style="width:38px;height:38px;border-radius:8px;background:var(--bg-surface);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">${icon}</div>
      <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:6px">
          <div>
            <div style="font-size:13px;font-weight:600;color:var(--cream-dim)">${title}</div>
            <div style="font-size:11px;color:var(--sub);margin-top:3px;line-height:1.5">${desc}</div>
          </div>
          <button onclick="showToast('📨 Enviando ${title} para firma…','var(--blue)')" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:6px;color:var(--cream-dim);font-size:11px;font-weight:500;padding:6px 12px;cursor:pointer;white-space:nowrap;flex-shrink:0">Enviar para firma</button>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:8px">
          <span style="background:rgba(201,124,64,.12);color:var(--orange);border:1px solid rgba(201,124,64,.25);border-radius:4px;font-size:10px;font-weight:600;padding:3px 8px">⏱ Pendiente</span>
          <span style="background:var(--bg-surface);border:1px solid var(--border);border-radius:4px;font-size:10px;color:var(--muted);padding:3px 8px">◷ Trigger: ${trigger}</span>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px">
          ${signatories.map(s=>`<span style="background:var(--bg-surface);border:1px solid var(--border);border-radius:4px;font-size:10px;color:var(--sub);padding:3px 8px">✍ ${s}</span>`).join('')}
        </div>
      </div>
    </div>`;

  const checkItem = (id, text) => `
    <div id="nr-chk-${id}" onclick="nrToggleChk('${id}')" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border2);cursor:pointer;transition:background .1s" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
      <div id="nr-chk-box-${id}" style="width:18px;height:18px;border-radius:4px;border:1.5px solid var(--muted);background:none;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s"></div>
      <span id="nr-chk-txt-${id}" style="font-size:12px;color:var(--cream-dim)">${text}</span>
    </div>`;

  const stepPack = () => `
    <div>
      <div style="margin-bottom:16px">
        <div style="font-size:13px;font-weight:600;color:var(--cream-dim);margin-bottom:4px">Pack de bienvenida</div>
        <div style="font-size:11px;color:var(--sub)">Documentos que el cliente debe firmar digitalmente para iniciar el proceso de compra.</div>
      </div>

      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px">
        ${docCard('👤','KYC — Conoce Tu Cliente','El cliente completa el formulario online. El sistema genera el PDF para firma digital de todos los titulares.','Disponible tras confirmación de reserva',['Giuseppe Gangemi'],0)}
        ${docCard('📋','Promesa de Compraventa','Contrato de promesa generado automáticamente con los datos del cliente, unidad y condiciones pactadas. Firma de todos los titulares.','Disponible tras KYC completado',['Giuseppe Gangemi'],1)}
        ${docCard('💰','Plan de Pagos','El plan de pagos configurado en este módulo se genera como PDF para firma y aceptación del cliente.','Disponible tras KYC completado',['Giuseppe Gangemi'],2)}
      </div>

      <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:10px;overflow:hidden">
        <div style="padding:14px 16px;border-bottom:1px solid var(--border)">
          <div style="font-size:12px;font-weight:600;color:var(--cream-dim)">Checklist para iniciar el proceso</div>
          <div style="font-size:11px;color:var(--sub);margin-top:2px">Todos los puntos deben estar completados para marcar el proceso como «Iniciado»</div>
        </div>
        ${checkItem('reserva','Reserva pagada y justificante subido')}
        ${checkItem('kyc','KYC completado y firmado por todos los titulares')}
        ${checkItem('promesa','Promesa de Compraventa firmada')}
        ${checkItem('plan','Plan de pagos firmado')}
        ${checkItem('docs','2 documentos por titular subidos')}
        <div style="padding:14px 16px">
          <button id="nr-iniciar-btn" disabled style="background:var(--muted);color:var(--sub);border:none;border-radius:6px;font-size:11px;font-weight:600;padding:10px 20px;cursor:not-allowed;opacity:.5;letter-spacing:.04em">✦ Marcar proceso como INICIADO</button>
        </div>
      </div>
    </div>`;

  const stepResumen = () => `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
      <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:16px 18px">
        <div style="font-size:11px;font-weight:600;color:var(--green-txt);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">Cliente y unidad</div>
        ${[
          ['Cliente','—'],['Unidad','—'],['Proyecto','Makai Residences'],['Tipo','—'],
        ].map(([k,v])=>`<div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:7px"><span style="color:var(--sub)">${k}</span><span style="color:var(--cream-dim);font-weight:500">${v}</span></div>`).join('')}
      </div>
      <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:16px 18px">
        <div style="font-size:11px;font-weight:600;color:var(--green-txt);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">Plan financiero</div>
        ${[
          ['Precio de venta','$306,000.00'],['Down Payment (30%)','$91,800.00'],
          ['Construcción (35%)','$107,100.00'],['Cierre y Entrega (35%)','$107,100.00'],
        ].map(([k,v])=>`<div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:7px"><span style="color:var(--sub)">${k}</span><span style="color:var(--cream-dim);font-weight:500">${v}</span></div>`).join('')}
      </div>
    </div>
    <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:16px 18px;margin-bottom:12px">
      <div style="font-size:11px;font-weight:600;color:var(--green-txt);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">Documentos que se generarán automáticamente</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
        ${['Promesa de compraventa','Plan de pagos firmado','Carta de bienvenida','Acceso al portal del comprador','Factura de reserva','Cronograma de hitos'].map(d=>`
          <div style="display:flex;align-items:center;gap:6px;background:var(--bg-surface);border:1px solid var(--border);border-radius:5px;padding:8px 10px">
            <span style="color:var(--green-txt);font-size:12px">📄</span>
            <span style="font-size:10px;color:var(--cream-dim)">${d}</span>
          </div>`).join('')}
      </div>
    </div>
    <div style="background:rgba(130,184,112,.08);border:1px solid rgba(130,184,112,.2);border-radius:8px;padding:14px 18px;display:flex;align-items:flex-start;gap:10px">
      <span style="font-size:16px;flex-shrink:0">✓</span>
      <div>
        <div style="font-size:12px;font-weight:600;color:var(--green-txt);margin-bottom:4px">Listo para confirmar</div>
        <div style="font-size:11px;color:var(--sub);line-height:1.6">Al confirmar se creará el expediente del cliente, se generarán los documentos, se activará el portal del comprador y se enviará el pack de bienvenida configurado.</div>
      </div>
    </div>`;

  // ── Render step indicator ─────────────────────────────────────────────
  const stepIndicator = () => STEPS.map((s,i)=>`
    <div style="display:flex;align-items:center;gap:6px;opacity:${i+1===nrStep?'1':i+1<nrStep?'.7':'.35'}">
      <div style="width:22px;height:22px;border-radius:50%;${i+1<nrStep?'background:var(--green-txt);':'border:1.5px solid '+(i+1===nrStep?'var(--green-txt)':'var(--border)')+';}display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:'+(i+1<nrStep?'#0c0c0b':'var(--cream-dim)')};flex-shrink:0">
        ${i+1<nrStep?'✓':i+1}
      </div>
      <span style="font-size:11px;font-weight:${i+1===nrStep?'600':'400'};color:${i+1===nrStep?'var(--cream-dim)':'var(--sub)'}">${s}</span>
    </div>
    ${i<STEPS.length-1?`<div style="width:24px;height:1px;background:var(--border);flex-shrink:0"></div>`:''}
  `).join('');

  const stepBody = () => nrStep===1 ? stepPlanPagos() : nrStep===2 ? stepTitulares() : nrStep===3 ? stepPack() : stepResumen();
  const footerBtns = () => `
    <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
    <div style="display:flex;gap:8px">
      ${nrStep > 1 ? `<button class="btn btn-ghost btn-sm" onclick="nrPrev()">← Anterior</button>` : ''}
      ${nrStep < 4
        ? `<button class="btn btn-primary btn-sm" onclick="nrNext()">Siguiente →</button>`
        : `<button class="btn btn-primary btn-sm" onclick="nrConfirmar()">✓ Confirmar reserva</button>`}
    </div>`;

  const renderModal = () => `
    <div class="modal modal-lg" style="max-width:860px;width:95vw">
      <div class="modal-header" style="flex-direction:column;align-items:flex-start;gap:12px;padding-bottom:14px">
        <div style="display:flex;align-items:center;justify-content:space-between;width:100%">
          <span class="modal-title">Nueva Reserva</span>
          <div class="modal-close" onclick="closeModal()">✕</div>
        </div>
        <div style="display:flex;align-items:center;gap:0">${stepIndicator()}</div>
      </div>
      <div class="modal-body" id="nr-body" style="max-height:68vh;overflow-y:auto">${stepBody()}</div>
      <div class="modal-footer" id="nr-footer">${footerBtns()}</div>
    </div>`;

  const rerender = () => {
    document.getElementById('nr-body').innerHTML = stepBody();
    document.getElementById('nr-footer').innerHTML = footerBtns();
    // re-paint step indicator
    const hdr = document.querySelector('.modal-header div[style*="display:flex;align-items:center;gap:0"]');
    if (hdr) hdr.innerHTML = stepIndicator();
    if (nrStep===1) nrCalc();
    if (nrStep===3) nrSelectPack('basico');
  };

  // ── Global functions ──────────────────────────────────────────────────
  window.nrNext = () => { if (nrStep < 4) { nrStep++; rerender(); } };
  window.nrPrev = () => { if (nrStep > 1) { nrStep--; rerender(); } };

  window.nrCalc = () => {
    const g = id => parseFloat(document.getElementById(id)?.value) || 0;
    const s = (id, v) => { const e = document.getElementById(id); if(e) e.textContent = v; };
    const sv = (id, v) => { const e = document.getElementById(id); if(e) e.value = v; };
    const fmtV = n => '$' + (+n).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    const lista = g('nr-lista'), desc = g('nr-desc'), dp = g('nr-dp-pct'), co = g('nr-co-pct');
    const venta = lista - desc, ce = 100 - dp - co;
    const dpAmt = venta*dp/100, coAmt = venta*co/100, ceAmt = venta*ce/100;
    sv('nr-venta', venta.toFixed(2));
    sv('nr-ce-pct', ce + '%');
    const pctLbl = lista ? (desc/lista*100).toFixed(1) : '0';
    s('nr-desc-pct', `Descuento: ${pctLbl}% del precio de lista`);
    const reserva = g('nr-reserva'), gastos = g('nr-gastos');
    s('nr-dp-saldo', (dpAmt - reserva - gastos).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    sv('nr-co-total', coAmt.toFixed(2));
    sv('nr-ce-total', ceAmt.toFixed(2));
    const cuota = coAmt / 13;
    s('nr-co-cuota', cuota.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    for(let i=0;i<13;i++) s(`nr-cuota-${i}`, '$'+cuota.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
    s('nr-v-venta', fmtV(venta)); s('nr-v-dp', fmtV(dpAmt)); s('nr-v-co', fmtV(coAmt)); s('nr-v-ce', fmtV(ceAmt));
    s('nr-v-total', venta > 0 && Math.abs(dpAmt+coAmt+ceAmt-venta)<1 ? fmtV(venta)+' ✓' : fmtV(dpAmt+coAmt+ceAmt));
    const ok = Math.abs(dp+co+ce-100)<0.1;
    const ve = document.getElementById('nr-verif'); if(ve){ ve.textContent = ok?`✓ ${dp}% + ${co}% + ${ce}% = 100%`:`⚠ ${dp}% + ${co}% + ${ce}% ≠ 100%`; ve.style.color = ok?'var(--green-txt)':'var(--orange)'; }
  };

  let nrTitularN = 0;
  window.nrAddTitular = () => {
    nrTitularN++;
    const c = document.getElementById('nr-titulares-extra'); if(!c) return;
    const div = document.createElement('div');
    div.style.cssText = 'background:var(--bg-card2);border:1px solid var(--border);border-radius:8px;padding:16px 18px;margin-top:10px';
    div.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
        <div style="font-size:11px;font-weight:600;color:var(--orange);text-transform:uppercase;letter-spacing:.06em">Cotitular ${nrTitularN}</div>
        <button onclick="this.parentElement.parentElement.remove()" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:14px">✕</button>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
        <div>${lbl('Nombre completo')}<input type="text" class="field-input" style="font-size:12px" placeholder="Nombre y apellidos"></div>
        <div>${lbl('Nacionalidad')}<input type="text" class="field-input" style="font-size:12px" placeholder="Italiana, Española…"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>${lbl('Email')}<input type="email" class="field-input" style="font-size:12px" placeholder="email@ejemplo.com"></div>
        <div>${lbl('Teléfono')}<input type="text" class="field-input" style="font-size:12px" placeholder="+1 809 000 0000"></div>
      </div>`;
    c.appendChild(div);
    const pe = document.getElementById('nr-p-extra'); if(pe) pe.style.display='flex';
  };

  window.nrToggleChk = (id) => {
    const box = document.getElementById(`nr-chk-box-${id}`);
    const txt = document.getElementById(`nr-chk-txt-${id}`);
    const row = document.getElementById(`nr-chk-${id}`);
    if (!box) return;
    const done = box.dataset.done === '1';
    if (done) {
      box.dataset.done = '0';
      box.style.background = 'none';
      box.style.borderColor = 'var(--muted)';
      box.innerHTML = '';
      if (txt) { txt.style.color = 'var(--cream-dim)'; txt.style.textDecoration = 'none'; }
    } else {
      box.dataset.done = '1';
      box.style.background = 'var(--green-txt)';
      box.style.borderColor = 'var(--green-txt)';
      box.innerHTML = '<svg width="10" height="10" viewBox="0 0 10 10"><polyline points="1.5,5 4,7.5 8.5,2.5" fill="none" stroke="#0c0c0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
      if (txt) { txt.style.color = 'var(--sub)'; txt.style.textDecoration = 'line-through'; }
    }
    const all = document.querySelectorAll('[id^="nr-chk-box-"]');
    const allDone = [...all].every(b => b.dataset.done === '1');
    const btn = document.getElementById('nr-iniciar-btn');
    if (btn) {
      btn.disabled = !allDone;
      btn.style.background = allDone ? 'var(--green-txt)' : 'var(--muted)';
      btn.style.color = allDone ? '#0c0c0b' : 'var(--sub)';
      btn.style.cursor = allDone ? 'pointer' : 'not-allowed';
      btn.style.opacity = allDone ? '1' : '.5';
    }
  };

  window.nrConfirmar = () => { closeModal(); showToast('✓ Reserva creada — expediente generado','var(--green-txt)'); };

  openModal(renderModal());
  setTimeout(()=>{ nrCalc(); }, 50);
}

export function modalNuevoBroker() {
  openModal(`
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">🤝 Nuevo Broker</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div class="field-row">
        <div class="field-group"><label class="field-label">Nombre completo</label><input class="field-input" placeholder="José Rodríguez"></div>
        <div class="field-group"><label class="field-label">Agencia</label><input class="field-input" placeholder="JR Real Estate"></div>
      </div>
      <div class="field-row">
        <div class="field-group"><label class="field-label">Email</label><input class="field-input" type="email" placeholder="jose@agencia.com"></div>
        <div class="field-group"><label class="field-label">País</label><input class="field-input" placeholder="Rep. Dominicana"></div>
      </div>
      <div class="field-group">
        <label class="field-label">Comisión (%)</label>
        <input class="field-input" type="number" placeholder="4" min="0" max="20">
      </div>
      <div class="field-group">
        <label class="field-label">Notas del contrato</label>
        <textarea class="field-textarea" placeholder="Términos especiales, observaciones…" rows="3"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✓ Broker añadido — pendiente activación','var(--orange)')">Añadir broker</button>
    </div>
  </div>`);
}

export function modalExportCodigo(section, onSuccess) {
  const adminEmail = 'a***@dunadevelopment.com';
  const digits = [1,2,3,4,5,6];

  openModal(`
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">🔒 Exportación protegida</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">

      <!-- Step 1: Explicación -->
      <div id="exp-step-1">
        <div style="text-align:center;padding:8px 0 20px">
          <div style="width:56px;height:56px;border-radius:50%;background:rgba(201,124,64,.12);border:1px solid rgba(201,124,64,.3);display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 16px">🔒</div>
          <div style="font-size:14px;font-weight:600;color:var(--cream-dim);margin-bottom:8px">Exportar ${section}</div>
          <div style="font-size:12px;color:var(--sub);line-height:1.7;margin-bottom:20px">
            Esta exportación contiene datos sensibles y requiere<br>autorización del administrador del sistema.
          </div>
        </div>
        <div style="background:var(--bg-surface);border-radius:8px;padding:14px 16px;margin-bottom:18px;border:1px solid var(--border)">
          <div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Cómo funciona</div>
          <div style="display:flex;flex-direction:column;gap:8px">
            ${[
              ['1', 'Solicitas el código de autorización'],
              ['2', 'El admin recibe un código de 6 dígitos en su correo'],
              ['3', 'El admin te comparte el código'],
              ['4', 'Ingresas el código y se descarga la exportación'],
            ].map(([n, t]) => `
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:20px;height:20px;border-radius:50%;background:var(--bg-card2);border:1px solid var(--border);font-size:9px;font-weight:700;color:var(--sub);display:flex;align-items:center;justify-content:center;flex-shrink:0">${n}</div>
                <span style="font-size:11px;color:var(--sub)">${t}</span>
              </div>
            `).join('')}
          </div>
        </div>
        <div style="background:rgba(201,124,64,.08);border:1px solid rgba(201,124,64,.2);border-radius:8px;padding:10px 14px;margin-bottom:18px;font-size:11px;color:var(--orange);line-height:1.6">
          ⚠ El código caduca en <strong>10 minutos</strong>. Solo puede ser usado una vez.
        </div>
        <button class="btn btn-primary btn-sm" style="width:100%" onclick="sendExportRequest()">Solicitar código de autorización →</button>
      </div>

      <!-- Step 2: Ingresar código -->
      <div id="exp-step-2" style="display:none">
        <div style="text-align:center;padding:8px 0 20px">
          <div style="width:56px;height:56px;border-radius:50%;background:rgba(58,122,189,.12);border:1px solid rgba(58,122,189,.3);display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 16px">📨</div>
          <div style="font-size:13px;font-weight:600;color:var(--cream-dim);margin-bottom:6px">Código enviado</div>
          <div style="font-size:12px;color:var(--sub);line-height:1.6;margin-bottom:6px">
            Se envió un código de 6 dígitos al correo del administrador:
          </div>
          <div style="font-size:12px;font-weight:500;color:var(--blue);margin-bottom:20px">${adminEmail}</div>
        </div>
        <div style="margin-bottom:8px;font-size:11px;color:var(--sub);text-align:center;text-transform:uppercase;letter-spacing:.04em">Ingresa el código</div>
        <div class="code-input" style="margin-bottom:6px">
          ${digits.map(i => `<input class="code-digit" id="cd-${i}" maxlength="1" oninput="nextDigit(${i})" onkeydown="prevDigit(event,${i})">`).join('')}
        </div>
        <div style="font-size:10px;color:var(--muted);text-align:center;margin-bottom:20px">El código caduca en <span id="exp-timer" style="color:var(--orange);font-weight:500">10:00</span></div>
        <button class="btn btn-primary btn-sm" style="width:100%;margin-bottom:10px" onclick="verifyExportCode()">Verificar y exportar</button>
        <button class="btn btn-ghost btn-sm" style="width:100%;font-size:11px" onclick="resendExportCode()">Reenviar código</button>
      </div>

    </div>
  </div>`);

  // Timer countdown
  let timerSecs = 600;
  let timerInterval = null;
  const startTimer = () => {
    timerSecs = 600;
    timerInterval = setInterval(() => {
      timerSecs--;
      const el = document.getElementById('exp-timer');
      if (el) {
        const m = Math.floor(timerSecs / 60), s = timerSecs % 60;
        el.textContent = `${m}:${s.toString().padStart(2,'0')}`;
        el.style.color = timerSecs < 60 ? 'var(--red)' : 'var(--orange)';
      }
      if (timerSecs <= 0) clearInterval(timerInterval);
    }, 1000);
  };

  window.sendExportRequest = () => {
    document.getElementById('exp-step-1').style.display = 'none';
    document.getElementById('exp-step-2').style.display = '';
    document.getElementById('cd-1')?.focus();
    startTimer();
    showToast('📨 Código enviado al correo del administrador', 'var(--blue)');
  };
  window.resendExportCode = () => {
    digits.forEach(i => { const el = document.getElementById(`cd-${i}`); if (el) el.value = ''; });
    document.getElementById('cd-1')?.focus();
    clearInterval(timerInterval);
    startTimer();
    showToast('📨 Código reenviado', 'var(--blue)');
  };
  window.nextDigit = (i) => {
    const next = document.getElementById(`cd-${i + 1}`);
    if (next) next.focus();
  };
  window.prevDigit = (e, i) => {
    if (e.key === 'Backspace' && !document.getElementById(`cd-${i}`)?.value) {
      const prev = document.getElementById(`cd-${i - 1}`);
      if (prev) { prev.value = ''; prev.focus(); }
    }
  };
  window.verifyExportCode = () => {
    const code = digits.map(i => document.getElementById(`cd-${i}`)?.value || '').join('');
    if (code.length < 6) { showToast('⚠ Ingresa el código completo', 'var(--orange)'); return; }
    clearInterval(timerInterval);
    closeModal();
    showToast('✓ Exportación autorizada — descargando…', 'var(--green-txt)');
    if (onSuccess) onSuccess();
  };
}

// ── Modal: Exportar (admin directo) ──────────
export function modalExportAdmin(section, onSuccess) {
  openModal(`
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">⬇ Exportar ${section}</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--green-lite);border-radius:8px;border:1px solid rgba(130,184,112,.2);margin-bottom:18px">
        <span style="font-size:18px">✓</span>
        <div style="font-size:12px;color:var(--green-txt);line-height:1.5">Como administrador puedes exportar directamente sin autorización adicional.</div>
      </div>
      <div class="field-group" style="margin-bottom:14px">
        <label class="field-label">Formato de exportación</label>
        <select class="field-select">
          <option>CSV — valores separados por coma</option>
          <option>Excel (.xlsx)</option>
          <option>PDF — reporte formateado</option>
        </select>
      </div>
      <div class="field-group">
        <label class="field-label">Rango de datos</label>
        <select class="field-select">
          <option>Todo el historial</option>
          <option>Últimos 30 días</option>
          <option>Últimos 90 días</option>
          <option>Año actual (2026)</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('⬇ Descargando ${section}…','var(--green-txt)')">⬇ Descargar</button>
    </div>
  </div>`);
}

// ── Helper interno ────────────────────────────
function kv(pairs) {
  return pairs.map(([k, v]) => `
    <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--border2);font-size:12px">
      <span style="color:var(--sub)">${k}</span>
      <span style="color:var(--cream-dim);font-weight:500;text-align:right">${v}</span>
    </div>
  `).join('');
}

// ── Modal: Editar comprador ───────────────────
export function modalEditarComprador(client) {
  const agents = ['Ana Rodríguez', 'Carlos Ruiz', 'María Santos', 'Pedro Álvarez'];
  openModal(`
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">✏ Editar comprador — ${client.firstName} ${client.lastName}</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg-surface);border-radius:8px;margin-bottom:18px">
        <div style="width:38px;height:38px;border-radius:50%;background:var(--green-lite);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--green-txt);flex-shrink:0">${client.initials}</div>
        <div>
          <div style="font-size:13px;font-weight:600;color:var(--cream-dim)">${client.firstName} ${client.lastName}</div>
          <div style="font-size:11px;color:var(--sub)">${client.unit} · ${client.project} · ${client.statusLabel}</div>
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Nombre</label>
          <input class="field-input" value="${client.firstName}">
        </div>
        <div class="field-group">
          <label class="field-label">Apellido</label>
          <input class="field-input" value="${client.lastName}">
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Email</label>
          <input class="field-input" type="email" value="${client.email}">
        </div>
        <div class="field-group">
          <label class="field-label">Teléfono</label>
          <input class="field-input" value="${client.phone}">
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">País de origen</label>
          <input class="field-input" value="${client.country}">
        </div>
        <div class="field-group">
          <label class="field-label">Agente asignado</label>
          <select class="field-select">
            ${agents.map(a => `<option ${a === client.agent ? 'selected' : ''}>${a}</option>`).join('')}
          </select>
        </div>
      </div>
      <div class="field-group">
        <label class="field-label">Notas internas</label>
        <textarea class="field-textarea" rows="2" placeholder="Observaciones sobre este comprador…"></textarea>
      </div>
      <div style="margin-top:14px;padding:12px;background:var(--bg-surface);border-radius:8px;border:1px solid rgba(184,64,64,.2)">
        <div style="font-size:11px;font-weight:600;color:var(--red);margin-bottom:8px">⚠ Zona de riesgo</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn btn-ghost btn-sm" style="font-size:11px;color:var(--orange);border-color:rgba(201,124,64,.3)" onclick="closeModal();showToast('🔒 Contraseña reseteada — email enviado','var(--orange)')">Resetear contraseña</button>
          <button class="btn btn-ghost btn-sm" style="font-size:11px;color:var(--red);border-color:rgba(184,64,64,.3)" onclick="closeModal();showToast('Cuenta desactivada','var(--red)')">Desactivar cuenta</button>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✓ Comprador actualizado','var(--green-txt)')">Guardar cambios</button>
    </div>
  </div>`);
}

// ── Modal: Editar prospecto ───────────────────
export function modalEditarProspecto(prospect) {
  openModal(`
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">✏ Editar prospecto — ${prospect.firstName} ${prospect.lastName}</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body">
      <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg-surface);border-radius:8px;margin-bottom:18px">
        <div style="width:38px;height:38px;border-radius:50%;background:rgba(58,122,189,.15);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--blue);flex-shrink:0">${prospect.initials}</div>
        <div>
          <div style="font-size:13px;font-weight:600;color:var(--cream-dim)">${prospect.firstName} ${prospect.lastName}</div>
          <div style="font-size:11px;color:var(--sub)">Prospecto · Interés: ${prospect.interest} · via ${prospect.source}</div>
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Nombre</label>
          <input class="field-input" value="${prospect.firstName}">
        </div>
        <div class="field-group">
          <label class="field-label">Apellido</label>
          <input class="field-input" value="${prospect.lastName}">
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Email</label>
          <input class="field-input" type="email" value="${prospect.email}">
        </div>
        <div class="field-group">
          <label class="field-label">Teléfono</label>
          <input class="field-input" value="${prospect.phone}">
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">País</label>
          <input class="field-input" value="${prospect.country}">
        </div>
        <div class="field-group">
          <label class="field-label">Canal de origen</label>
          <select class="field-select">
            ${['Web','Instagram','Facebook','Referido','Evento','Otro'].map(s => `<option ${s === prospect.source ? 'selected' : ''}>${s}</option>`).join('')}
          </select>
        </div>
      </div>
      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Proyecto de interés</label>
          <select class="field-select">
            ${['Makai','Naviva','LIV at Cap Cana'].map(p => `<option ${p === prospect.interest ? 'selected' : ''}>${p}</option>`).join('')}
          </select>
        </div>
        <div class="field-group">
          <label class="field-label">Prioridad</label>
          <select class="field-select">
            <option>Alta</option>
            <option selected>Media</option>
            <option>Baja</option>
          </select>
        </div>
      </div>
      <div class="field-group">
        <label class="field-label">Notas</label>
        <textarea class="field-textarea" rows="2" placeholder="Presupuesto estimado, motivación, observaciones…"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-ghost btn-sm" onclick="closeModal();showToast('Prospecto archivado','var(--muted)')">Archivar</button>
      <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✓ Prospecto actualizado','var(--green-txt)')">Guardar cambios</button>
    </div>
  </div>`);
}

function kvP(label, val) {
  return `
  <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--border);font-size:12px;gap:12px">
    <span style="color:var(--sub);flex-shrink:0">${label}</span>
    <span style="color:var(--cream-dim);font-weight:500;text-align:right">${val}</span>
  </div>`;
}

// ── Modal: Editar plantilla ───────────────────
export function modalEditarPlantilla(template, content) {
  const isNew = template.id === 0;
  const ch = template.channel || 'Email';
  const hasEmail = ch.includes('Email');
  const hasWA    = ch.includes('WhatsApp');
  const cont = content || { subject: '', body: 'Hola {{nombre}},\n\n', wa: 'Hola {{nombre}} 👋\n\n' };

  const VARS       = ['{{nombre}}', '{{unidad}}', '{{proyecto}}', '{{agente}}', '{{monto}}', '{{fecha_vencimiento}}', '{{num_cuota}}'];
  const CATEGORIES = ['Bienvenida', 'Seguimiento', 'Pagos', 'Legal', 'Proyectos'];
  const CHANNELS   = ['Email', 'WhatsApp', 'Email + WhatsApp'];

  const emailEdDisp  = hasEmail ? 'flex' : 'none';
  const waEdDisp     = !hasEmail && hasWA ? 'flex' : 'none';
  const emailTabDisp = hasEmail ? '' : 'none';
  const waTabDisp    = hasWA ? '' : 'none';
  const emailTabBdr  = hasEmail ? 'var(--green-txt)' : 'transparent';
  const emailTabClr  = hasEmail ? 'var(--green-txt)' : 'var(--sub)';
  const waTabBdr     = !hasEmail && hasWA ? 'var(--green-txt)' : 'transparent';
  const waTabClr     = !hasEmail && hasWA ? 'var(--green-txt)' : 'var(--sub)';
  const prevEmailDisp = hasEmail ? 'block' : 'none';
  const prevWaDisp    = !hasEmail && hasWA ? 'flex' : 'none';

  const varChips = (fieldId) => VARS.map(v =>
    `<span style="background:var(--bg-surface);border:1px solid var(--border);border-radius:4px;padding:2px 7px;font-size:10px;font-family:monospace;color:var(--blue);cursor:pointer" onclick="insertTplVar('${v}','${fieldId}')">${v}</span>`
  ).join('');

  openModal(`
  <div class="modal" style="width:min(880px,94vw);max-width:none;padding:0;overflow:hidden">
    <div class="modal-header" style="padding:16px 22px">
      <span class="modal-title">${isNew ? '+ Nueva plantilla' : `✏ Editar — ${template.name}`}</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;height:520px;overflow:hidden">

      <!-- LEFT: editor -->
      <div style="border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden">

        <div style="padding:14px 20px 12px;border-bottom:1px solid var(--border);background:var(--bg-card2);display:flex;flex-direction:column;gap:10px;flex-shrink:0">
          <div class="field-group">
            <label class="field-label">Nombre de la plantilla</label>
            <input class="field-input" id="tpl-name" value="${template.name}">
          </div>
          <div style="display:flex;gap:10px">
            <div class="field-group" style="flex:1">
              <label class="field-label">Categoría</label>
              <select class="field-select" id="tpl-cat">
                ${CATEGORIES.map(c => `<option ${c === template.category ? 'selected' : ''}>${c}</option>`).join('')}
              </select>
            </div>
            <div class="field-group" style="flex:1">
              <label class="field-label">Canal</label>
              <select class="field-select" id="tpl-channel" onchange="switchTplChannel(this.value)">
                ${CHANNELS.map(c => `<option ${c === ch ? 'selected' : ''}>${c}</option>`).join('')}
              </select>
            </div>
          </div>
        </div>

        <div style="display:flex;border-bottom:1px solid var(--border);padding:0 20px;background:var(--bg-card);flex-shrink:0">
          <div id="tpl-tab-email" onclick="switchTplTab('email')"
            style="display:${emailTabDisp};padding:8px 12px;font-size:11px;font-weight:500;cursor:pointer;border-bottom:2px solid ${emailTabBdr};color:${emailTabClr};user-select:none">✉ Email</div>
          <div id="tpl-tab-wa" onclick="switchTplTab('wa')"
            style="display:${waTabDisp};padding:8px 12px;font-size:11px;font-weight:500;cursor:pointer;border-bottom:2px solid ${waTabBdr};color:${waTabClr};user-select:none">💬 WhatsApp</div>
        </div>

        <div id="tpl-email-editor" style="display:${emailEdDisp};flex-direction:column;gap:10px;overflow-y:auto;padding:14px 20px;flex:1">
          <div class="field-group">
            <label class="field-label">Asunto del email</label>
            <input class="field-input" id="tpl-subject" oninput="updateTplPreview()" placeholder="Asunto del correo…">
          </div>
          <div class="field-group" style="flex:1">
            <label class="field-label">Cuerpo del mensaje</label>
            <textarea class="field-textarea" id="tpl-body" rows="8" oninput="updateTplPreview()" style="font-family:monospace;font-size:11px;resize:vertical"></textarea>
          </div>
          <div style="flex-shrink:0">
            <div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">Variables</div>
            <div style="display:flex;gap:5px;flex-wrap:wrap">${varChips('tpl-body')}</div>
          </div>
        </div>

        <div id="tpl-wa-editor" style="display:${waEdDisp};flex-direction:column;gap:10px;overflow-y:auto;padding:14px 20px;flex:1">
          <div class="field-group" style="flex:1">
            <label class="field-label">Mensaje de WhatsApp</label>
            <textarea class="field-textarea" id="tpl-wa" rows="10" oninput="updateTplWaPreview()" style="font-family:monospace;font-size:11px;resize:vertical"></textarea>
          </div>
          <div style="flex-shrink:0">
            <div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">Variables</div>
            <div style="display:flex;gap:5px;flex-wrap:wrap">${varChips('tpl-wa')}</div>
          </div>
        </div>

      </div>

      <!-- RIGHT: preview -->
      <div style="background:#141412;display:flex;flex-direction:column;overflow:hidden">

        <div style="padding:10px 16px;border-bottom:1px solid var(--border);background:var(--bg-card2);display:flex;justify-content:space-between;align-items:center;flex-shrink:0">
          <span style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.05em">Vista previa</span>
          <div style="display:flex;gap:2px">
            <button id="prev-btn-email" onclick="switchTplPreview('email')" style="background:var(--bg-card);border:1px solid var(--border);border-radius:4px;padding:3px 8px;font-size:10px;cursor:pointer;color:var(--sub)">✉ Email</button>
            <button id="prev-btn-wa"    onclick="switchTplPreview('wa')"    style="background:none;border:1px solid transparent;border-radius:4px;padding:3px 8px;font-size:10px;cursor:pointer;color:var(--muted)">💬 WA</button>
          </div>
        </div>

        <div id="tpl-prev-email" style="display:${prevEmailDisp};flex:1;overflow-y:auto;padding:14px">
          <div style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.5);max-width:360px;margin:0 auto;font-family:Arial,sans-serif">
            <div style="background:#0c0c0b;padding:16px 22px;text-align:center">
              <div style="font-family:'Georgia',serif;font-size:17px;color:#F1EDE3;letter-spacing:.1em">DUNA</div>
              <div style="font-size:8px;color:rgba(241,237,227,.35);text-transform:uppercase;letter-spacing:.18em;margin-top:2px">Development Group</div>
            </div>
            <div style="background:#f8f6f2;padding:8px 18px;border-bottom:1px solid #e8e4de">
              <div style="font-size:9px;color:#999;margin-bottom:2px">Asunto:</div>
              <div id="prev-subject" style="font-size:11px;color:#333;font-weight:600"></div>
            </div>
            <div style="padding:18px 22px;background:#ffffff;min-height:80px">
              <div id="prev-body" style="font-size:11px;color:#444;line-height:1.75;white-space:pre-wrap"></div>
            </div>
            <div style="background:#f8f6f2;padding:12px 22px;text-align:center;border-top:1px solid #e8e4de">
              <div style="font-size:9px;color:#aaa;line-height:1.7">Duna Development Group · Cap Cana, Rep. Dominicana<br><a href="#" style="color:#4A5E3F">Cancelar suscripción</a></div>
            </div>
          </div>
        </div>

        <div id="tpl-prev-wa" style="display:${prevWaDisp};flex:1;overflow-y:auto;padding:20px;align-items:flex-start;justify-content:center;background:#0b141a">
          <div style="width:100%;max-width:300px">
            <div style="background:#202c33;border-radius:8px;padding:6px 12px;margin-bottom:8px;font-size:10px;color:rgba(241,241,242,.4);text-align:center">Hoy</div>
            <div style="background:#005c4b;border-radius:0 8px 8px 8px;padding:10px 14px;font-size:12px;color:#e9edef;line-height:1.65;white-space:pre-wrap;max-width:85%" id="prev-wa-bubble"></div>
            <div style="font-size:10px;color:rgba(241,241,242,.3);text-align:right;margin-top:3px">10:23 ✓✓</div>
          </div>
        </div>

      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-ghost btn-sm" onclick="showToast('✉ Email de prueba enviado','var(--blue)')">✉ Enviar prueba</button>
      <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✓ Plantilla guardada','var(--green-txt)')">Guardar plantilla</button>
    </div>
  </div>`);

  // Set values via JS to avoid HTML escaping issues
  const subjectEl = document.getElementById('tpl-subject');
  const bodyEl    = document.getElementById('tpl-body');
  const waEl      = document.getElementById('tpl-wa');
  if (subjectEl) subjectEl.value = cont.subject || '';
  if (bodyEl)    bodyEl.value    = cont.body    || '';
  if (waEl)      waEl.value      = cont.wa      || '';

  const ps = document.getElementById('prev-subject');
  const pb = document.getElementById('prev-body');
  const pw = document.getElementById('prev-wa-bubble');
  if (ps) ps.textContent = cont.subject || '';
  if (pb) pb.textContent = cont.body    || '';
  if (pw) pw.textContent = cont.wa      || '';

  window.updateTplPreview = () => {
    const s = document.getElementById('tpl-subject')?.value || '';
    const b = document.getElementById('tpl-body')?.value    || '';
    const ps2 = document.getElementById('prev-subject');
    const pb2 = document.getElementById('prev-body');
    if (ps2) ps2.textContent = s || 'Sin asunto';
    if (pb2) pb2.textContent = b;
  };
  window.updateTplWaPreview = () => {
    const w = document.getElementById('tpl-wa')?.value || '';
    const bbl = document.getElementById('prev-wa-bubble');
    if (bbl) bbl.textContent = w;
  };
  window.insertTplVar = (v, fieldId) => {
    const el = document.getElementById(fieldId);
    if (!el) return;
    const s2 = el.selectionStart, e2 = el.selectionEnd;
    el.value = el.value.slice(0, s2) + v + el.value.slice(e2);
    el.selectionStart = el.selectionEnd = s2 + v.length;
    el.focus();
    if (fieldId === 'tpl-body') window.updateTplPreview();
    else window.updateTplWaPreview();
  };
  window.switchTplTab = (tab) => {
    const emailEd = document.getElementById('tpl-email-editor');
    const waEd    = document.getElementById('tpl-wa-editor');
    if (emailEd) emailEd.style.display = tab === 'email' ? 'flex' : 'none';
    if (waEd)    waEd.style.display    = tab === 'wa'    ? 'flex' : 'none';
    ['email','wa'].forEach(t => {
      const el = document.getElementById('tpl-tab-' + t);
      if (!el) return;
      const active = t === tab;
      el.style.borderBottomColor = active ? 'var(--green-txt)' : 'transparent';
      el.style.color = active ? 'var(--green-txt)' : 'var(--sub)';
    });
    window.switchTplPreview(tab);
  };
  window.switchTplPreview = (side) => {
    const ep = document.getElementById('tpl-prev-email');
    const wp = document.getElementById('tpl-prev-wa');
    const be = document.getElementById('prev-btn-email');
    const bw = document.getElementById('prev-btn-wa');
    if (ep) ep.style.display = side === 'email' ? 'block' : 'none';
    if (wp) wp.style.display = side === 'wa'    ? 'flex'  : 'none';
    const setBtn = (btn, active) => {
      if (!btn) return;
      btn.style.background  = active ? 'var(--bg-card)' : 'none';
      btn.style.borderColor = active ? 'var(--border)' : 'transparent';
      btn.style.color       = active ? 'var(--sub)'    : 'var(--muted)';
    };
    setBtn(be, side === 'email');
    setBtn(bw, side === 'wa');
  };
  window.switchTplChannel = (newCh) => {
    const hasE = newCh.includes('Email');
    const hasW = newCh.includes('WhatsApp');
    const tabE = document.getElementById('tpl-tab-email');
    const tabW = document.getElementById('tpl-tab-wa');
    if (tabE) tabE.style.display = hasE ? '' : 'none';
    if (tabW) tabW.style.display = hasW ? '' : 'none';
    if (hasE) window.switchTplTab('email');
    else if (hasW) window.switchTplTab('wa');
  };
}

// ── Modal: Editar automatización ─────────────
export function modalEditarAutomacion(automation, templates, delays, triggers) {
  const isNew        = automation.id === 0;
  const templateNames = templates.map(t => t.name);

  window._autoSteps = automation.steps.map(s => ({ ...s }));

  const renderSteps = () => {
    const container = document.getElementById('auto-steps-list');
    if (!container) return;
    if (window._autoSteps.length === 0) {
      container.innerHTML = `<div style="text-align:center;padding:20px;font-size:12px;color:var(--muted)">Sin pasos. Haz clic en "+ Añadir paso" para comenzar.</div>`;
      return;
    }
    container.innerHTML = window._autoSteps.map((s, i) => `
      <div style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:var(--bg-surface);border-radius:8px;border:1px solid var(--border);margin-bottom:8px">
        <div style="width:24px;height:24px;border-radius:50%;background:var(--bg-card2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:var(--sub);flex-shrink:0;margin-top:4px">${i + 1}</div>
        <div style="flex:1;display:grid;grid-template-columns:1fr 1fr 2fr;gap:8px">
          <div class="field-group">
            <label class="field-label">Delay</label>
            <select class="field-select" onchange="updateAutoStep(${i},'delay',this.value)">
              ${delays.map(d => `<option ${d === s.delay ? 'selected' : ''}>${d}</option>`).join('')}
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Canal</label>
            <select class="field-select" onchange="updateAutoStep(${i},'channel',this.value)">
              ${['Email','WhatsApp','Email + WhatsApp'].map(c => `<option ${c === s.channel ? 'selected' : ''}>${c}</option>`).join('')}
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Plantilla</label>
            <select class="field-select" onchange="updateAutoStep(${i},'template',this.value)">
              ${templateNames.map(n => `<option ${n === s.template ? 'selected' : ''}>${n}</option>`).join('')}
            </select>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:3px;flex-shrink:0">
          ${i > 0 ? `<button class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:12px" onclick="moveAutoStep(${i},-1)">↑</button>` : `<div style="height:27px"></div>`}
          ${i < window._autoSteps.length - 1 ? `<button class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:12px" onclick="moveAutoStep(${i},1)">↓</button>` : `<div style="height:27px"></div>`}
          <button class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:12px;color:var(--red);border-color:rgba(184,64,64,.3)" onclick="removeAutoStep(${i})">✕</button>
        </div>
      </div>
    `).join('');
  };

  openModal(`
  <div class="modal modal-lg" style="padding:0;overflow:hidden">
    <div class="modal-header" style="padding:16px 22px">
      <span class="modal-title">${isNew ? '+ Nuevo flujo de automatización' : `✏ Editar flujo — ${automation.name}`}</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div style="padding:20px 24px;max-height:520px;overflow-y:auto;display:flex;flex-direction:column;gap:16px">

      <div>
        <div style="font-size:10px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px">Configuración del flujo</div>
        <div class="field-row">
          <div class="field-group">
            <label class="field-label">Nombre del flujo</label>
            <input class="field-input" id="auto-name" value="${automation.name}" placeholder="Ej: Bienvenida post-reserva">
          </div>
          <div class="field-group">
            <label class="field-label">Estado</label>
            <select class="field-select" id="auto-status">
              <option value="active"  ${automation.status === 'active'  ? 'selected' : ''}>Activo</option>
              <option value="paused"  ${automation.status === 'paused'  ? 'selected' : ''}>Pausado</option>
            </select>
          </div>
        </div>
        <div class="field-row">
          <div class="field-group">
            <label class="field-label">Trigger (disparador)</label>
            <select class="field-select" id="auto-trigger">
              ${triggers.map(t => `<option ${t === automation.trigger ? 'selected' : ''}>${t}</option>`).join('')}
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Audiencia</label>
            <input class="field-input" id="auto-audience" value="${automation.audience}" placeholder="Ej: Compradores nuevos">
          </div>
        </div>
      </div>

      <div style="display:flex;align-items:center;gap:10px">
        <div style="flex:1;height:1px;background:var(--border)"></div>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:20px;padding:5px 14px;font-size:11px;color:var(--green-txt);white-space:nowrap">⚡ Pasos del flujo</div>
        <div style="flex:1;height:1px;background:var(--border)"></div>
      </div>

      <div id="auto-steps-list"></div>

      <button class="btn btn-ghost btn-sm" style="align-self:flex-start" onclick="addAutoStep()">+ Añadir paso</button>

    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('✓ Flujo guardado','var(--green-txt)')">Guardar flujo</button>
    </div>
  </div>`);

  window.updateAutoStep = (i, field, val) => { if (window._autoSteps[i]) window._autoSteps[i][field] = val; };
  window.moveAutoStep   = (i, dir) => {
    const arr = window._autoSteps, j = i + dir;
    if (j < 0 || j >= arr.length) return;
    [arr[i], arr[j]] = [arr[j], arr[i]];
    renderSteps();
  };
  window.removeAutoStep = (i) => { window._autoSteps.splice(i, 1); renderSteps(); };
  window.addAutoStep    = () => {
    window._autoSteps.push({ delay: delays[0], channel: 'Email', template: templateNames[0] || '', icon: '✉' });
    renderSteps();
  };

  renderSteps();
}

// ── Modal: Anuncio (nuevo / editar) ───────────
export function modalNuevoAnuncio()            { _openAnuncioModal(null); }
export function modalEditarAnuncio(announcement) { _openAnuncioModal(announcement); }

function _openAnuncioModal(announcement) {
  const isNew = !announcement;

  const SEGMENTS = [
    { id: 'buyers_active',  label: 'Compradores activos',    count: 7, icon: '🏠' },
    { id: 'buyers_process', label: 'Compradores en proceso', count: 3, icon: '⏳' },
    { id: 'prospects',      label: 'Prospectos registrados', count: 3, icon: '🔍' },
    { id: 'brokers',        label: 'Brokers activos',        count: 3, icon: '🤝' },
    { id: 'team',           label: 'Equipo interno',         count: 2, icon: '🔒' },
  ];

  const initSegs = isNew                         ? new Set()
    : Array.isArray(announcement.segments)       ? new Set(announcement.segments)
    : announcement.audience === 'Todos'          ? new Set(SEGMENTS.map(s => s.id))
    : announcement.audience === 'Equipo interno' ? new Set(['team'])
    : new Set(['buyers_active']);
  window._annSegs = new Set(initSegs);

  const countReach = () => { let n = 0; window._annSegs.forEach(id => { const s = SEGMENTS.find(x => x.id === id); if (s) n += s.count; }); return n; };

  const renderSegs = () => {
    const container = document.getElementById('ann-segments');
    const countEl   = document.getElementById('ann-reach-count');
    if (!container) return;
    container.innerHTML = SEGMENTS.map(s => {
      const sel = window._annSegs.has(s.id);
      return `
      <div onclick="toggleAnnSeg('${s.id}')" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;border:1px solid ${sel ? 'rgba(130,184,112,.4)' : 'var(--border)'};background:${sel ? 'var(--green-lite)' : 'var(--bg-surface)'};cursor:pointer;transition:all .15s">
        <span style="font-size:15px">${s.icon}</span>
        <div style="flex:1">
          <div style="font-size:12px;font-weight:500;color:${sel ? 'var(--green-txt)' : 'var(--cream-dim)'}">${s.label}</div>
          <div style="font-size:10px;color:var(--muted)">${s.count} personas</div>
        </div>
        <div style="width:16px;height:16px;border-radius:3px;border:1px solid ${sel ? 'var(--green-txt)' : 'var(--border)'};background:${sel ? 'var(--green-txt)' : 'transparent'};display:flex;align-items:center;justify-content:center;font-size:9px;color:#fff;flex-shrink:0">${sel ? '✓' : ''}</div>
      </div>`;
    }).join('');
    if (countEl) countEl.textContent = countReach();
  };

  const saveLabel = isNew ? 'Publicar anuncio' : 'Guardar cambios';
  const saveMsg   = isNew ? '✓ Anuncio publicado' : '✓ Anuncio actualizado';

  openModal(`
  <div class="modal modal-lg" style="padding:0;overflow:hidden">
    <div class="modal-header" style="padding:16px 22px">
      <span class="modal-title">${isNew ? '📢 Nuevo anuncio' : `✏ Editar — ${announcement.title}`}</span>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;overflow:hidden">

      <!-- LEFT: contenido -->
      <div style="border-right:1px solid var(--border);padding:20px;overflow-y:auto;max-height:460px;display:flex;flex-direction:column;gap:12px">
        <div class="field-group">
          <label class="field-label">Título del anuncio</label>
          <input class="field-input" id="ann-title" placeholder="Ej: Actualización importante de avance de obra">
        </div>
        <div class="field-group" style="flex:1">
          <label class="field-label">Mensaje</label>
          <textarea class="field-textarea" id="ann-body" rows="7" placeholder="Escribe el contenido del anuncio…"></textarea>
        </div>
        <div class="field-group">
          <label class="field-label">Envío</label>
          <select class="field-select" id="ann-schedule" onchange="toggleAnnSchedule(this.value)">
            <option value="now">Enviar ahora</option>
            <option value="scheduled">Programar envío</option>
          </select>
        </div>
        <div id="ann-schedule-picker" style="display:none">
          <div class="field-row">
            <div class="field-group">
              <label class="field-label">Fecha</label>
              <input class="field-input" type="date" value="2026-05-10">
            </div>
            <div class="field-group">
              <label class="field-label">Hora</label>
              <input class="field-input" type="time" value="09:00">
            </div>
          </div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12px;color:var(--sub)">
          <input type="checkbox" ${!isNew && announcement.pinned ? 'checked' : ''}> Fijar este anuncio en el portal
        </label>
      </div>

      <!-- RIGHT: audiencia -->
      <div style="padding:20px;overflow-y:auto;max-height:460px;display:flex;flex-direction:column;gap:12px">
        <div>
          <div style="font-size:11px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Audiencia</div>
          <div style="font-size:11px;color:var(--muted);margin-bottom:12px">Combina segmentos — el anuncio llegará a todos los seleccionados</div>
          <div id="ann-segments" style="display:flex;flex-direction:column;gap:6px"></div>
        </div>

        <div style="background:var(--bg-surface);border-radius:8px;padding:14px;border:1px solid var(--border)">
          <div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">Alcance estimado</div>
          <div style="display:flex;align-items:baseline;gap:6px">
            <span style="font-size:26px;font-weight:700;color:var(--cream-dim)" id="ann-reach-count">0</span>
            <span style="font-size:12px;color:var(--muted)">personas recibirán este anuncio</span>
          </div>
        </div>

        <div style="padding:10px 12px;background:var(--bg-surface);border-radius:8px;border-left:2px solid var(--blue)">
          <div style="font-size:11px;color:var(--sub);line-height:1.6">Los anuncios se publican en el portal web de cada rol. Los compradores lo ven en su panel personal.</div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancelar</button>
      ${!isNew ? `<button class="btn btn-ghost btn-sm" style="color:var(--red);border-color:rgba(184,64,64,.3)" onclick="closeModal();showToast('Anuncio archivado','var(--muted)')">Archivar</button>` : ''}
      <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('${saveMsg}','var(--green-txt)')">${saveLabel}</button>
    </div>
  </div>`);

  // Set initial field values
  const titleEl = document.getElementById('ann-title');
  const bodyEl  = document.getElementById('ann-body');
  if (titleEl && !isNew) titleEl.value = announcement.title;
  if (bodyEl  && !isNew) bodyEl.value  = announcement.body;

  window.toggleAnnSeg = (id) => {
    if (window._annSegs.has(id)) window._annSegs.delete(id);
    else window._annSegs.add(id);
    renderSegs();
  };
  window.toggleAnnSchedule = (val) => {
    const p = document.getElementById('ann-schedule-picker');
    if (p) p.style.display = val === 'scheduled' ? '' : 'none';
  };

  renderSegs();
}
