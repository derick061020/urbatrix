/* ═══════════════════════════════════════════════
   CRM DUNA — App Entry Point
   Init dinámico: rol, sidebar, switcher, router
═══════════════════════════════════════════════ */

import { initRouter } from './router.js';
import { getRole, setRole, getRoleConfig, getAllRoles, getCurrentUser } from './role.js';
import {
  openModal, closeModal, showToast,
  modalDocumento, modalContrato, modalTransaccion, modalUnidad, modalBrokerDetail,
  modalNuevoExpediente, modalNuevoUsuario, modalRegistrarPago,
  modalSubirDocumento, modalNuevaUnidad, modalPublicarReporte, modalNuevaTarea,
  modalNuevaReserva, modalNuevoBroker, modalExportCodigo, modalExportAdmin,
  modalEditarBroker, modalEditarUsuario,
  modalCompradorDetail, modalProspectoDetail,
  modalEditarComprador, modalEditarProspecto,
  modalEditarPlantilla, modalEditarAutomacion,
  modalNuevoAnuncio, modalEditarAnuncio,
  modalVerReporte,
} from './modals.js';

// Exponer globalmente para onclick inline en vistas
window.closeModal = closeModal;
window.showToast  = showToast;
window.openDocModal   = (id) => { const d = window.DUNA_DATA.documents.find(x=>x.id===id);    if(d) modalDocumento(d); };
window.openContratoModal = (id) => { const c = window.DUNA_DATA.contracts.find(x=>x.id===id);  if(c) modalContrato(c); };
window.openTxModal    = (id) => { const t = window.DUNA_DATA.transactions.find(x=>x.id===id); if(t) modalTransaccion(t); };
window.openUnidadModal= (id) => { const u = window.DUNA_DATA.units.find(x=>x.id===id);        if(u) modalUnidad(u); };
window.openBrokerModal= (id) => { const b = window.DUNA_DATA.brokers.find(x=>x.id===id);      if(b) modalBrokerDetail(b); };
window.openNuevoExpedienteModal = () => modalNuevoExpediente(window.DUNA_DATA);
window.openNuevoUsuarioModal    = () => modalNuevoUsuario();
window.openRegistrarPagoModal   = (plan, onConfirm) => modalRegistrarPago(plan, onConfirm);
window.openSubirDocumentoModal  = () => modalSubirDocumento();
window.openNuevaUnidadModal     = () => modalNuevaUnidad();
window.openPublicarReporteModal = () => modalPublicarReporte();
window.openNuevaTareaModal      = () => modalNuevaTarea();
window.openNuevaReservaModal    = () => modalNuevaReserva(window.DUNA_DATA);
window.openNuevoBrokerModal     = () => modalNuevoBroker();
window.openExportModal          = (section) => {
  if (getRole() === 'admin') modalExportAdmin(section);
  else modalExportCodigo(section);
};
window.openEditarBrokerModal    = (id) => { const b = window.DUNA_DATA.brokers.find(x => x.id === id); if (b) modalEditarBroker(b); };
window.openEditarUsuarioModal   = (id) => { const u = window.DUNA_DATA.users.find(x => x.id === id);   if (u) modalEditarUsuario(u); };
window.openCompradorModal         = (client)   => modalCompradorDetail(client);
window.openProspectoModal         = (prospect) => modalProspectoDetail(prospect);
window.openEditarCompradorModal   = (client)   => modalEditarComprador(client);
window.openEditarProspectoModal   = (prospect) => modalEditarProspecto(prospect);
window.openEditarPlantillaModal   = (t, content)      => modalEditarPlantilla(t, content);
window.openEditarAutomacionModal  = (a, tpls, dly, trg) => modalEditarAutomacion(a, tpls, dly, trg);
window.openNuevoAnuncioModal      = () => modalNuevoAnuncio();
window.openEditarAnuncioModal     = (a) => modalEditarAnuncio(a);
window.openVerReporteModal        = (idx) => modalVerReporte(idx);

document.addEventListener('DOMContentLoaded', () => {
  applyShell();
  renderSidebar();
  renderUser();
  renderDevSwitcher();
  renderNotifications();
  initRouter();

  const dateEl = document.getElementById('header-date');
  if (dateEl) {
    const now = new Date();
    const days   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    dateEl.textContent = `${days[now.getDay()]}, ${now.getDate()} de ${months[now.getMonth()]} de ${now.getFullYear()}`;
  }
});

// ── Shell ──────────────────────────────────────
function applyShell() {
  const { shell } = getRoleConfig();
  const app = document.querySelector('.app');
  if (!app) return;
  app.dataset.shell = shell;
  app.dataset.role  = getRole();
}

// ── Sidebar dinámico ──────────────────────────
export function renderSidebar() {
  const nav = document.getElementById('sidebar-nav');
  if (!nav) return;
  const { nav: navConfig } = getRoleConfig();

  nav.innerHTML = navConfig.map(group => `
    <div class="nav-group">
      ${group.group ? `<div class="nav-group-label">${group.group}</div>` : ''}
      ${group.items.map(item => `
        <div class="nav-item" data-route="${item.route}">
          <span class="nav-icon">${item.icon}</span>
          <span class="nav-label">${item.label}</span>
          ${item.badge ? `<span class="nav-badge ${item.badgeCls}">${item.badge}</span>` : ''}
        </div>
      `).join('')}
    </div>
  `).join('');

  nav.querySelectorAll('.nav-item[data-route]').forEach(item => {
    item.addEventListener('click', () => {
      window.location.hash = item.dataset.route;
    });
  });
}

// ── User info ─────────────────────────────────
export function renderUser() {
  const el = document.getElementById('sidebar-user');
  if (!el) return;
  const user = getCurrentUser();
  el.innerHTML = `
    <div class="user-avatar">${user.initials}</div>
    <div class="user-info">
      <div class="user-name">${user.name}</div>
      <div class="user-role">${user.sub}</div>
    </div>
    <div class="user-online"></div>
  `;
}

// ── Dev Role Switcher ─────────────────────────
function renderDevSwitcher() {
  const el = document.getElementById('dev-switcher');
  if (!el) return;
  const current = getRole();
  const config  = getRoleConfig();
  const all     = getAllRoles();

  el.innerHTML = `
    <div class="dev-sw-bar" id="dev-sw-toggle">
      <span class="dev-sw-wrench">🔧</span>
      <span class="dev-sw-role">${config.icon} ${config.label}</span>
      <span class="dev-sw-caret">▾</span>
    </div>
    <div class="dev-sw-panel" id="dev-sw-panel">
      <div class="dev-sw-heading">Vista previa de rol</div>
      ${all.map(r => `
        <div class="dev-sw-item ${r.key === current ? 'is-active' : ''}" data-role="${r.key}">
          <span class="dev-sw-item-icon">${r.icon}</span>
          <span class="dev-sw-item-label">${r.label}</span>
          ${r.key === current ? '<span class="dev-sw-check">✓</span>' : ''}
        </div>
      `).join('')}
      <div class="dev-sw-note">⚠ Solo visible en prototipo</div>
    </div>
  `;

  const toggle = document.getElementById('dev-sw-toggle');
  const panel  = document.getElementById('dev-sw-panel');

  toggle.addEventListener('click', () => panel.classList.toggle('open'));

  // Cerrar al hacer click fuera
  document.addEventListener('click', e => {
    if (!el.contains(e.target)) panel.classList.remove('open');
  });

  el.querySelectorAll('.dev-sw-item').forEach(item => {
    item.addEventListener('click', () => {
      const role = item.dataset.role;
      if (role === getRole()) { panel.classList.remove('open'); return; }
      setRole(role);
      applyShell();
      renderSidebar();
      renderUser();
      renderDevSwitcher();
      _notifInit = false;
      renderNotifications();
      panel.classList.remove('open');
      // Navegar al default route del nuevo rol
      const { defaultRoute } = getRoleConfig();
      window.location.hash = defaultRoute;
    });
  });
}

// ── Notifications ─────────────────────────────
const NOTIFS = {
  admin: [
    { icon: '💳', bg: 'rgba(184,64,64,.15)',  color: '#e07070', title: 'Pago vencido',       desc: 'Roberto Silva · Cuota de mayo sin recibir',            time: 'hace 2 días', route: 'expediente/5', unread: true  },
    { icon: '💬', bg: 'rgba(201,124,64,.15)', color: 'var(--orange)', title: '2 mensajes sin leer', desc: 'Carlos Méndez · Respuesta pendiente',              time: 'hace 15 min', route: 'mensajes',     unread: true  },
    { icon: '⚠',  bg: 'rgba(201,124,64,.15)', color: 'var(--orange)', title: 'KYC pendiente',       desc: 'Carlos Méndez · Documentos sin revisar',          time: 'hace 10 min', route: 'expediente/1', unread: true  },
    { icon: '✍',  bg: 'rgba(184,64,64,.15)',  color: '#e07070', title: 'Firma urgente',       desc: 'Ana García · Promesa lista para firma',               time: 'hace 32 min', route: 'expediente/2', unread: true  },
    { icon: '⚠',  bg: 'rgba(201,124,64,.15)', color: 'var(--orange)', title: 'KYC pendiente',       desc: 'Sophie Martin · Documentos sin revisar',          time: 'hace 1 día',  route: 'expediente/6', unread: false },
    { icon: '📋', bg: 'rgba(58,122,189,.15)', color: 'var(--blue)',   title: 'Contrato generado',   desc: 'Luis Pérez · Pendiente revisión legal',            time: 'hace 1h',     route: 'expediente/3', unread: false },
    { icon: '✅', bg: 'rgba(74,94,63,.2)',    color: 'var(--green-txt)', title: 'Pago confirmado',  desc: 'María López · Cuota de mayo recibida · $2,688',   time: 'hace 3h',     route: 'expediente/4', unread: false },
    { icon: '🤝', bg: 'rgba(58,122,189,.12)', color: 'var(--blue)',   title: 'Nuevo broker',        desc: 'Agencia XYZ · Contrato pendiente de activación',  time: 'hace 2h',     route: 'aprobaciones', unread: false },
  ],
  senior_agent: [
    { icon: '💬', bg: 'rgba(201,124,64,.15)', color: 'var(--orange)', title: '2 mensajes sin leer', desc: 'Carlos Méndez · Respuesta pendiente',             time: 'hace 15 min', route: 'mensajes',     unread: true  },
    { icon: '⚠',  bg: 'rgba(201,124,64,.15)', color: 'var(--orange)', title: 'KYC pendiente',       desc: 'Carlos Méndez · Docs sin revisar',                time: 'hace 10 min', route: 'expedientes',  unread: true  },
    { icon: '💳', bg: 'rgba(184,64,64,.15)',  color: '#e07070', title: 'Pago vencido',       desc: 'Roberto Silva · Cuota no recibida',                    time: 'hace 2 días', route: 'expedientes',  unread: false },
    { icon: '📋', bg: 'rgba(58,122,189,.15)', color: 'var(--blue)',   title: 'Documento listo',     desc: 'Luis Pérez · Documentación en revisión',          time: 'hace 1h',     route: 'expedientes',  unread: false },
    { icon: '🗒', bg: 'rgba(255,255,255,.07)', color: 'var(--sub)',   title: 'Tarea asignada',      desc: 'Seguimiento a Sophie Martin · hoy',              time: 'hace 6h',     route: 'tareas',       unread: false },
  ],
  buyer: [
    { icon: '💳', bg: 'rgba(201,124,64,.15)', color: 'var(--orange)', title: 'Cuota próxima',       desc: 'Cuota #4 vence en 15 días · $2,688 USD',          time: 'Hoy',         route: 'mi-plan-pagos',unread: true  },
    { icon: '💬', bg: 'rgba(74,94,63,.2)',    color: 'var(--green-txt)', title: 'Mensaje de Ana',   desc: 'Tu asesora te envió un mensaje',                  time: 'hace 1h',     route: 'mensajes-comprador', unread: true },
    { icon: '✅', bg: 'rgba(74,94,63,.2)',    color: 'var(--green-txt)', title: 'Documento aprobado', desc: 'Tu declaración de ingresos fue aceptada',       time: 'hace 1 día',  route: 'mis-documentos-comprador', unread: false },
    { icon: '🏗', bg: 'rgba(58,122,189,.12)', color: 'var(--blue)',   title: 'Avance de obra',      desc: 'Mampostería al 52% · Actualización de mayo',      time: 'hace 3 días', route: 'avance-obra',  unread: false },
  ],
  broker: [
    { icon: '💰', bg: 'rgba(74,94,63,.2)',    color: 'var(--green-txt)', title: 'Comisión procesada', desc: 'Cierre Carlos Méndez · $8,620 USD transferidos', time: 'hace 1 día',  route: 'mis-comisiones', unread: true },
    { icon: '✍',  bg: 'rgba(201,124,64,.15)', color: 'var(--orange)', title: 'Cliente en promesa',  desc: 'Ana García · Lista para firma',                   time: 'hace 2 días', route: 'mis-clientes-broker', unread: false },
    { icon: '⚠',  bg: 'rgba(184,64,64,.12)', color: '#e07070', title: 'Contrato por vencer',  desc: 'Tu contrato vence en 45 días · revisar',             time: 'hace 2 días', route: 'mi-contrato-broker', unread: false },
  ],
};

let _notifInit = false;
let _notifOpen  = false;

function renderNotifications() {
  const btn   = document.getElementById('notif-btn');
  const panel = document.getElementById('notif-panel');
  const dot   = document.getElementById('notif-dot');
  if (!btn || !panel) return;

  function buildPanel() {
    const role   = getRole();
    const notifs = NOTIFS[role] || [];
    const unread = notifs.filter(n => n.unread).length;

    panel.className = 'notif-panel' + (_notifOpen ? ' open' : '');
    panel.innerHTML = `
      <div class="notif-panel-header">
        <div style="display:flex;align-items:center;gap:8px">
          <span style="font-size:13px;font-weight:600;color:var(--cream)">Notificaciones</span>
          ${unread > 0 ? `<span style="background:var(--red);color:#fff;border-radius:10px;font-size:9px;font-weight:700;padding:2px 7px">${unread} nuevas</span>` : ''}
        </div>
        <button class="btn btn-ghost btn-sm" id="notif-mark-all" style="font-size:10px">Marcar leídas</button>
      </div>
      <div class="notif-list">
        ${notifs.map((n, i) => `
        <div class="notif-item${n.unread ? ' unread' : ''}" data-notif-idx="${i}" data-route="${n.route}">
          <div class="notif-icon" style="background:${n.bg}">${n.icon}</div>
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2px">
              <span style="font-size:12px;font-weight:${n.unread?'600':'400'};color:${n.unread?'var(--cream)':'var(--cream-dim)'}">${n.title}</span>
              <span style="font-size:9px;color:var(--muted);white-space:nowrap;margin-left:8px">${n.time}</span>
            </div>
            <div style="font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${n.desc}</div>
          </div>
          ${n.unread ? `<div style="width:6px;height:6px;border-radius:50%;background:var(--orange);flex-shrink:0;margin-top:4px"></div>` : ''}
        </div>`).join('')}
      </div>
      <div class="notif-panel-footer">
        <span class="cell-link" style="font-size:11px" onclick="document.getElementById('notif-panel').classList.remove('open')">Cerrar</span>
      </div>`;

    // Update dot
    if (dot) dot.style.display = unread > 0 ? '' : 'none';

    // Mark all
    panel.querySelector('#notif-mark-all')?.addEventListener('click', () => {
      panel.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
      panel.querySelector('.notif-panel-header span[style*="background"]')?.remove();
      if (dot) dot.style.display = 'none';
    });

    // Item click → navigate
    panel.querySelectorAll('.notif-item[data-route]').forEach(el => {
      el.addEventListener('click', () => {
        el.classList.remove('unread');
        isOpen = false;
        panel.classList.remove('open');
        window.location.hash = el.dataset.route;
      });
    });
  }

  if (!_notifInit) {
    _notifInit = true;
    btn.addEventListener('click', e => {
      e.stopPropagation();
      _notifOpen = !_notifOpen;
      buildPanel();
    });
    document.addEventListener('click', e => {
      if (_notifOpen && !panel.contains(e.target) && e.target !== btn) {
        _notifOpen = false;
        panel.classList.remove('open');
      }
    });
  }

  buildPanel(); // init dot state
}

// Re-render notifications when role changes (called from renderDevSwitcher)
export function refreshNotifications() { renderNotifications(); }

// ── Highlight nav activo (llamado desde router) ──
export function setActiveNav(route) {
  document.querySelectorAll('.nav-item').forEach(item => {
    item.classList.toggle('active', item.dataset.route === route);
  });
}
