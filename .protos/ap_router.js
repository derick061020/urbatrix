/* ═══════════════════════════════════════════════
   CRM DUNA — Router
   Hash-based SPA. Route guards por rol.
═══════════════════════════════════════════════ */

import { canAccess, getRoleConfig } from './role.js';
import { setActiveNav } from './app.js';

const ROUTES = {
  // ── Admin ──────────────────────────────────
  'dashboard':      () => import('./views/dashboard.js'),
  'expedientes':    () => import('./views/expedientes.js'),
  'documentos':     () => import('./views/documentos.js'),
  'contratos':      () => import('./views/contratos.js'),
  'transacciones':  () => import('./views/transacciones.js'),
  'proyectos':      () => import('./views/proyectos.js'),
  'unidades':       () => import('./views/unidades.js'),
  'avance-obra':    () => import('./views/avance-obra.js'),
  'modulo-financiero': () => import('./views/modulo-financiero.js'),
  'plantillas':     () => import('./views/plantillas.js'),
  'anuncios':       () => import('./views/anuncios.js'),
  'usuarios':       () => import('./views/usuarios.js'),
  'brokers':        () => import('./views/brokers.js'),
  'aprobaciones':   () => import('./views/aprobaciones.js'),
  'tareas':         () => import('./views/tareas.js'),
  'configuracion':  () => import('./views/configuracion.js'),
  'estadisticas':   () => import('./views/estadisticas.js'),
  // Detalles internos admin
  'expediente':     () => import('./views/expediente-detail.js'),
  'broker-detail':  () => import('./views/broker-detail.js'),
  'proyecto':       () => import('./views/proyecto-detail.js'),

  // Mensajes
  'mensajes':          () => import('./views/mensajes.js'),
  'mensajes-comprador':() => import('./views/portal/mensajes-comprador.js'),
  'mensajes-broker':   () => import('./views/portal/mensajes-broker.js'),

  // ── Portal Comprador ───────────────────────
  'mi-propiedad':             () => import('./views/portal/mi-propiedad.js'),
  'mis-documentos-comprador': () => import('./views/portal/mis-documentos-comprador.js'),
  'mi-plan-pagos':            () => import('./views/portal/mi-plan-pagos.js'),
  'mi-asesor':                () => import('./views/portal/mi-asesor.js'),

  // ── Portal Broker ──────────────────────────
  'dashboard-broker':   () => import('./views/portal/dashboard-broker.js'),
  'mis-clientes-broker':() => import('./views/portal/mis-clientes-broker.js'),
  'mis-comisiones':     () => import('./views/portal/mis-comisiones.js'),
  'mi-contrato-broker': () => import('./views/portal/mi-contrato-broker.js'),
  'material-broker':    () => import('./views/portal/material-broker.js'),
};

let currentRoute = null;

function getRouteFromHash() {
  const raw = window.location.hash.replace('#', '').trim();
  // Soporte para rutas con parámetro: #expediente/1
  return raw.split('/')[0] || getRoleConfig().defaultRoute;
}

export function getRouteParam() {
  const raw = window.location.hash.replace('#', '').trim();
  const parts = raw.split('/');
  return parts[1] || null;
}

async function navigate(route) {
  const content          = document.getElementById('content');
  const headerTitle      = document.getElementById('header-title');
  const headerBreadcrumb = document.getElementById('header-breadcrumb');
  if (!content) return;

  // Route guard — redirige si el rol no tiene acceso
  if (!canAccess(route)) {
    navigate(getRoleConfig().defaultRoute);
    return;
  }

  // Resuelve ruta (fallback al default del rol)
  const resolvedRoute = ROUTES[route] ? route : getRoleConfig().defaultRoute;

  // Loading state
  content.innerHTML = `
    <div class="view-container" style="display:flex;align-items:center;justify-content:center;min-height:300px;">
      <div style="text-align:center;color:var(--sub)">
        <div style="font-size:20px;margin-bottom:8px;opacity:.4">⟳</div>
        <div style="font-size:11px">Cargando...</div>
      </div>
    </div>`;

  try {
    const module = await ROUTES[resolvedRoute]();
    const html   = module.render(window.DUNA_DATA);
    content.innerHTML = html;

    if (module.meta) {
      if (headerTitle)      headerTitle.textContent      = module.meta.title      || '';
      if (headerBreadcrumb) headerBreadcrumb.textContent = module.meta.breadcrumb || '';
    }

    if (module.init) module.init(window.DUNA_DATA);

    // Auto-open modal from route param (e.g. #usuarios/actividad)
    const autoModal = getRouteParam();
    if (autoModal) {
      setTimeout(() => {
        if (autoModal === 'actividad') {
          const c = window.DUNA_DATA?.clients?.[0];
          if (c && window.verCompradorModal) {
            window.verCompradorModal(c.id);
            setTimeout(() => window.switchCompTab?.('cactividad'), 80);
          }
        } else if (autoModal === 'registrarPago') {
          window.openRegistrarPagoModal?.();
        }
      }, 100);
    }

    content.scrollTop = 0;

  } catch (e) {
    console.error('Error al cargar vista:', resolvedRoute, e);
    content.innerHTML = `
      <div class="view-container">
        <div class="placeholder-view">
          <div class="icon">⚠</div>
          <h2>Error al cargar la vista</h2>
          <p>${resolvedRoute} — ${e.message}</p>
        </div>
      </div>`;
  }

  setActiveNav(resolvedRoute);
  currentRoute = resolvedRoute;
}

export function initRouter() {
  window.addEventListener('hashchange', () => navigate(getRouteFromHash()));

  // Re-attach nav clicks después de cada renderSidebar (delegado en nav)
  document.getElementById('sidebar-nav')?.addEventListener('click', e => {
    const item = e.target.closest('[data-route]');
    if (item) window.location.hash = item.dataset.route;
  });

  navigate(getRouteFromHash());
}

export function goTo(route, param) {
  window.location.hash = param ? `${route}/${param}` : route;
}

export { navigate, currentRoute };
