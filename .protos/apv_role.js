/* ═══════════════════════════════════════════════
   CRM DUNA — Role System
   Estado de rol activo, configuración de nav y
   usuarios simulados por rol (dev-only switcher)
═══════════════════════════════════════════════ */

const ROLES = {

  admin: {
    label: 'Administrador',
    icon: '⚡',
    shell: 'dark',
    defaultRoute: 'dashboard',
    user: { name: 'Admin Duna', initials: 'A', sub: 'Administrador global' },
    nav: [
      { group: null, items: [
        { route: 'dashboard',    icon: '⊞', label: 'Dashboard' },
      ]},
      { group: 'Gestión', items: [
        { route: 'expedientes',  icon: '📁', label: 'Expedientes',          badge: '41', badgeCls: 'badge-nav-orange' },
        { route: 'documentos',   icon: '📄', label: 'Documentos',           badge: '5',  badgeCls: 'badge-nav-orange' },
        { route: 'contratos',    icon: '📝', label: 'Reservas y Contratos', badge: '1',  badgeCls: 'badge-nav-gray'   },
        { route: 'transacciones',icon: '💳', label: 'Transacciones' },
      ]},
      { group: 'Proyectos', items: [
        { route: 'proyectos',    icon: '🏗', label: 'Proyectos' },
        { route: 'unidades',     icon: '🏠', label: 'Unidades' },
        { route: 'avance-obra',  icon: '📊', label: 'Avance de Obra' },
      ]},
      { group: 'Comunicación', items: [
        { route: 'mensajes',     icon: '💬', label: 'Mensajes',             badge: '3',  badgeCls: 'badge-nav-red' },
        { route: 'plantillas',   icon: '✉',  label: 'Plantillas y Automát.' },
        { route: 'anuncios',     icon: '📢', label: 'Anuncios' },
      ]},
      { group: 'Equipo', items: [
        { route: 'usuarios',     icon: '👤', label: 'Compradores' },
        { route: 'brokers',      icon: '🤝', label: 'Brokers y Externos' },
        { route: 'aprobaciones', icon: '✅', label: 'Aprobaciones',         badge: '7',  badgeCls: 'badge-nav-red' },
        { route: 'tareas',       icon: '🗒', label: 'Tareas',               badge: '12', badgeCls: 'badge-nav-red' },
      ]},
      { group: 'Sistema', items: [
        { route: 'estadisticas', icon: '📈', label: 'Estadísticas' },
        { route: 'configuracion',icon: '⚙', label: 'Configuración' },
      ]},
    ],
  },

  senior_agent: {
    label: 'Agente Senior',
    icon: '⭐',
    shell: 'dark',
    defaultRoute: 'dashboard',
    user: { name: 'María Fernández', initials: 'MF', sub: 'Agente Senior' },
    nav: [
      { group: null, items: [
        { route: 'dashboard',    icon: '⊞', label: 'Dashboard' },
      ]},
      { group: 'Gestión', items: [
        { route: 'expedientes',  icon: '📁', label: 'Mis Expedientes' },
        { route: 'documentos',   icon: '📄', label: 'Documentos',     badge: '3', badgeCls: 'badge-nav-orange' },
        { route: 'aprobaciones', icon: '✅', label: 'Aprobaciones',   badge: '4', badgeCls: 'badge-nav-red'    },
      ]},
      { group: 'Comunicación', items: [
        { route: 'mensajes',     icon: '💬', label: 'Mensajes',       badge: '2', badgeCls: 'badge-nav-red' },
      ]},
      { group: 'Mi actividad', items: [
        { route: 'tareas',       icon: '🗒', label: 'Mis Tareas',     badge: '8', badgeCls: 'badge-nav-red' },
      ]},
    ],
  },

  buyer: {
    label: 'Comprador',
    icon: '👤',
    shell: 'light',
    defaultRoute: 'mi-propiedad',
    user: { name: 'Carlos Méndez', initials: 'CM', sub: 'Comprador · Makai Residences' },
    nav: [
      { group: null, items: [
        { route: 'mi-propiedad',             icon: '🏠', label: 'Mi Propiedad' },
        { route: 'mis-documentos-comprador', icon: '📄', label: 'Mis Documentos' },
        { route: 'mi-plan-pagos',            icon: '💳', label: 'Plan de Pagos' },
        { route: 'avance-obra',              icon: '📊', label: 'Avance de Obra' },
        { route: 'mensajes-comprador',       icon: '💬', label: 'Mensajes',      badge: '2', badgeCls: 'badge-nav-red' },
        { route: 'mi-asesor',                icon: '👤', label: 'Mi Asesor' },
      ]},
    ],
  },

  broker: {
    label: 'Broker',
    icon: '🤝',
    shell: 'dark',
    defaultRoute: 'dashboard-broker',
    user: { name: 'José Rodríguez', initials: 'JR', sub: 'Broker · JR Real Estate' },
    nav: [
      { group: null, items: [
        { route: 'dashboard-broker', icon: '⊞', label: 'Dashboard' },
      ]},
      { group: 'Mi cartera', items: [
        { route: 'mis-clientes-broker', icon: '📁', label: 'Mis Clientes' },
        { route: 'mis-comisiones',      icon: '💰', label: 'Mis Comisiones' },
        { route: 'mi-contrato-broker',  icon: '📝', label: 'Mi Contrato' },
        { route: 'material-broker',     icon: '📦', label: 'Material de ventas' },
        { route: 'mensajes-broker',     icon: '💬', label: 'Mensajes',     badge: '1', badgeCls: 'badge-nav-red' },
      ]},
    ],
  },

};

// ── Getters / setters ──────────────────────────
export function getRole() {
  const urlRole = new URLSearchParams(location.search).get('role');
  if (urlRole && ROLES[urlRole]) return urlRole;
  return localStorage.getItem('duna_role') || 'admin';
}
export function setRole(role)   { localStorage.setItem('duna_role', role); }
export function getRoleConfig() { return ROLES[getRole()] || ROLES.admin; }
export function getAllRoles()    { return Object.entries(ROLES).map(([key, v]) => ({ key, label: v.label, icon: v.icon })); }

// Detail routes that inherit access from their parent nav route
const SUBROUTES = {
  'expediente':        'expedientes',
  'broker-detail':     'brokers',
  'proyecto':          'proyectos',
  'modulo-financiero': 'contratos',
};

export function canAccess(route) {
  const { nav } = getRoleConfig();
  const check = SUBROUTES[route] || route;
  return nav.some(g => g.items.some(i => i.route === check));
}

export function canExport() { return getRole() === 'admin'; }

export function getCurrentUser() { return getRoleConfig().user; }
