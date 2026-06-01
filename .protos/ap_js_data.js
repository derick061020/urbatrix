/* ═══════════════════════════════════════════════
   CRM DUNA — Mock Data
   Datos de ejemplo para el prototipo del admin
═══════════════════════════════════════════════ */

window.DUNA_DATA = {

  // ── Proyectos ──────────────────────────────
  projects: [
    {
      id: 1, name: 'Makai Residences', slug: 'makai',
      location: 'Cap Cana · Punta Cana',
      status: 'active', statusLabel: 'Activo',
      total: 102, sold: 22, pending: 4, available: 76,
      progress: 22, salesProgress: 18,
      totalValue: '$41.99M',
      logo: 'MK', color: '#4A5E3F',
    },
    {
      id: 2, name: 'Naviva Residences', slug: 'naviva',
      location: 'Cap Cana · Punta Cana',
      status: 'prep', statusLabel: 'En prep.',
      total: null, sold: null, pending: null, available: null,
      progress: 0, salesProgress: 0,
      totalValue: 'TBD',
      logo: 'NV', color: '#3a7abd',
    },
    {
      id: 3, name: 'LIV at Cap Cana', slug: 'liv',
      location: 'Cap Cana · Punta Cana',
      status: 'prep', statusLabel: 'En prep.',
      total: null, sold: null, pending: null, available: null,
      progress: 0, salesProgress: 0,
      totalValue: 'TBD',
      logo: 'LV', color: '#c97c40',
    },
  ],

  // ── Expedientes / Clientes ─────────────────
  clients: [
    {
      id: 1, firstName: 'Carlos', lastName: 'Méndez', initials: 'CM',
      email: 'carlos@email.com', phone: '+1 809-555-0101', country: 'Rep. Dominicana',
      unit: 'Unidad 111', project: 'Makai Residences', projectSlug: 'makai',
      price: '$431,000', paid: '$21,550', paidPct: 5,
      status: 'kyc_pending', statusLabel: 'KYC Pendiente', statusColor: 'orange',
      step: 2, agent: 'Ana Rodríguez',
      createdAt: '2026-03-15', lastAction: 'hace 5 min',
      flags: ['kyc', 'docs'],
    },
    {
      id: 2, firstName: 'Ana', lastName: 'García', initials: 'AG',
      email: 'ana.garcia@email.com', phone: '+34 600-555-202', country: 'España',
      unit: 'Unidad 205', project: 'Makai Residences', projectSlug: 'makai',
      price: '$389,000', paid: '$19,450', paidPct: 5,
      status: 'signature_required', statusLabel: 'Firma requerida', statusColor: 'red',
      step: 3, agent: 'Carlos Ruiz',
      createdAt: '2026-03-22', lastAction: 'hace 28 min',
      flags: ['signature'],
    },
    {
      id: 3, firstName: 'Luis', lastName: 'Pérez', initials: 'LP',
      email: 'luis.perez@email.com', phone: '+1 646-555-0303', country: 'USA',
      unit: 'Unidad 308', project: 'Makai Residences', projectSlug: 'makai',
      price: '$415,000', paid: '$62,250', paidPct: 15,
      status: 'in_review', statusLabel: 'En revisión', statusColor: 'blue',
      step: 4, agent: 'Ana Rodríguez',
      createdAt: '2026-02-10', lastAction: 'hace 1h',
      flags: [],
    },
    {
      id: 4, firstName: 'María', lastName: 'López', initials: 'ML',
      email: 'maria.lopez@email.com', phone: '+52 55-555-0404', country: 'México',
      unit: 'Unidad 114', project: 'Makai Residences', projectSlug: 'makai',
      price: '$431,000', paid: '$215,500', paidPct: 50,
      status: 'completed', statusLabel: 'Al día', statusColor: 'green',
      step: 5, agent: 'Carlos Ruiz',
      createdAt: '2026-01-28', lastAction: 'hace 3h',
      flags: [],
    },
    {
      id: 5, firstName: 'Roberto', lastName: 'Silva', initials: 'RS',
      email: 'roberto.silva@email.com', phone: '+55 11-555-0505', country: 'Brasil',
      unit: 'Unidad 220', project: 'Makai Residences', projectSlug: 'makai',
      price: '$395,000', paid: '$19,750', paidPct: 5,
      status: 'payment_overdue', statusLabel: 'Pago vencido', statusColor: 'red',
      step: 3, agent: 'Ana Rodríguez',
      createdAt: '2026-04-01', lastAction: 'hace 2h',
      flags: ['payment'],
    },
    {
      id: 6, firstName: 'Sophie', lastName: 'Martin', initials: 'SM',
      email: 'sophie.martin@email.com', phone: '+33 6-55-55-0606', country: 'Francia',
      unit: 'Unidad 312', project: 'Makai Residences', projectSlug: 'makai',
      price: '$445,000', paid: '$89,000', paidPct: 20,
      status: 'kyc_pending', statusLabel: 'KYC Pendiente', statusColor: 'orange',
      step: 2, agent: 'Carlos Ruiz',
      createdAt: '2026-04-12', lastAction: 'hace 1 día',
      flags: ['kyc'],
    },
    {
      id: 7, firstName: 'James', lastName: 'Wilson', initials: 'JW',
      email: 'james.wilson@email.com', phone: '+1 305-555-0707', country: 'USA',
      unit: 'Unidad 401', project: 'Makai Residences', projectSlug: 'makai',
      price: '$480,000', paid: '$480,000', paidPct: 100,
      status: 'completed', statusLabel: 'Completado', statusColor: 'green',
      step: 6, agent: 'Ana Rodríguez',
      createdAt: '2025-11-15', lastAction: 'hace 5 días',
      flags: [],
    },
  ],

  // ── Aprobaciones (verificación documental y contractual) ──
  approvals: [
    {
      id: 1, type: 'KYC', typeColor: 'orange', clientType: 'cliente',
      client: 'Carlos Méndez', unit: 'Unidad 111 · Makai',
      description: 'Documentos KYC cargados — cédula y comprobante de ingresos pendientes de validación',
      requestedBy: 'Sistema', date: 'hace 10 min', priority: 'high',
    },
    {
      id: 2, type: 'Promesa', typeColor: 'red', clientType: 'cliente',
      client: 'Ana García', unit: 'Unidad 205 · Makai',
      description: 'Promesa de compraventa lista — revisar condiciones antes de enviar a firma del cliente',
      requestedBy: 'Admin', date: 'hace 32 min', priority: 'high',
    },
    {
      id: 3, type: 'Contrato', typeColor: 'blue', clientType: 'cliente',
      client: 'Luis Pérez', unit: 'Unidad 308 · Makai',
      description: 'Contrato de compraventa generado — pendiente revisión legal antes de enviar a firma',
      requestedBy: 'Admin', date: 'hace 1h', priority: 'medium',
    },
    {
      id: 4, type: 'Contrato Broker', typeColor: 'orange', clientType: 'broker',
      client: 'Agencia XYZ', unit: null,
      description: 'Contrato de colaboración nuevo — verificar términos y activar cuenta del broker',
      requestedBy: 'Sistema', date: 'hace 2h', priority: 'medium',
    },
    {
      id: 5, type: 'Renovación', typeColor: 'orange', clientType: 'broker',
      client: 'Laura Jiménez', unit: null,
      description: 'Contrato de colaboración vence el 2026-06-01 — requiere renovación antes del vencimiento',
      requestedBy: 'Sistema', date: 'hace 3h', priority: 'low',
    },
    {
      id: 6, type: 'KYC', typeColor: 'orange', clientType: 'cliente',
      client: 'Sophie Martin', unit: 'Unidad 312 · Makai',
      description: 'Actualización de KYC — documento de identidad vencido, re-carga pendiente de validación',
      requestedBy: 'Sistema', date: 'hace 4h', priority: 'medium',
    },
    {
      id: 7, type: 'Contrato', typeColor: 'blue', clientType: 'cliente',
      client: 'Roberto Silva', unit: 'Unidad 220 · Makai',
      description: 'Addendum al plan de pagos — requiere firma antes de la próxima cuota',
      requestedBy: 'Admin', date: 'hace 5h', priority: 'high',
    },
  ],

  // ── Tareas ─────────────────────────────────
  tasks: [
    { id: 1, text: 'Revisar KYC de Carlos Méndez', done: false, priority: 'high', client: 'Carlos Méndez', due: 'Hoy' },
    { id: 2, text: 'Enviar promesa de compraventa a Ana García', done: false, priority: 'high', client: 'Ana García', due: 'Hoy' },
    { id: 3, text: 'Confirmar pago cuota #3 — Luis Pérez', done: true, priority: 'medium', client: 'Luis Pérez', due: 'Completado' },
    { id: 4, text: 'Publicar avance de obra Q2 2026', done: false, priority: 'medium', client: null, due: 'Hoy' },
    { id: 5, text: 'Revisar descuento Makai — aprobación #1', done: false, priority: 'high', client: 'Carlos Méndez', due: 'Hoy' },
    { id: 6, text: 'Validar broker pendiente: José Rodríguez', done: true, priority: 'medium', client: null, due: 'Completado' },
    { id: 7, text: 'Notificación vencimiento doc. — Sophie Martin', done: false, priority: 'low', client: 'Sophie Martin', due: 'Mañana' },
    { id: 8, text: 'Actualizar plan de pagos Unidad 401', done: false, priority: 'medium', client: 'James Wilson', due: 'Esta semana' },
    { id: 9, text: 'Exportar reporte DGII brokers — Mayo 2026', done: false, priority: 'low', client: null, due: 'Esta semana' },
  ],

  // ── Actividad reciente ─────────────────────
  activity: [
    { dot: 'green', text: '<strong>Carlos Méndez</strong> firmó el KYC', time: 'hace 5 min' },
    { dot: 'orange', text: 'Nuevo expediente: <strong>Sophie Martin</strong>', time: 'hace 28 min' },
    { dot: 'red', text: 'Pago vencido: <strong>Roberto Silva</strong> — cuota #2', time: 'hace 1h' },
    { dot: 'blue', text: 'Avance de obra Q2 publicado por <strong>Ana R.</strong>', time: 'hace 2h' },
    { dot: 'green', text: 'Broker aprobado: <strong>José Rodríguez</strong>', time: 'hace 3h' },
    { dot: 'orange', text: 'Reserva confirmada: <strong>Unidad 312</strong>', time: 'hace 4h' },
    { dot: 'gray', text: 'Backup automático completado', time: 'hace 6h' },
  ],

  // ── Unidades (Makai) ───────────────────────
  units: [
    { id: 101, num: 'U-101', floor: 1, bed: 1, bath: 1, sqft: 870,  sqftTer: 180, price: 380000, status: 'available', statusLabel: 'Disponible',  view: 'Garden View',  type: 'Studio+' },
    { id: 102, num: 'U-102', floor: 1, bed: 1, bath: 1, sqft: 890,  sqftTer: 200, price: 395000, status: 'pending',   statusLabel: 'Reservada',    view: 'Garden View',  type: 'Studio+', reservationDeadline: '2026-05-20', reservedAt: '2026-04-20' },
    { id: 103, num: 'U-103', floor: 1, bed: 2, bath: 2, sqft: 1100, sqftTer: 220, price: 445000, status: 'available', statusLabel: 'Disponible',  view: 'Lake View',    type: '2 Bed' },
    { id: 104, num: 'U-104', floor: 1, bed: 2, bath: 2, sqft: 1050, sqftTer: 210, price: 431000, status: 'sold',      statusLabel: 'Vendida',      view: 'Lake View',    type: '2 Bed' },
    { id: 105, num: 'U-105', floor: 1, bed: 3, bath: 3, sqft: 1400, sqftTer: 300, price: 498000, status: 'available', statusLabel: 'Disponible',  view: 'Ocean View',   type: '3 Bed' },
    { id: 111, num: 'U-111', floor: 1, bed: 2, bath: 2, sqft: 959,  sqftTer: 207, price: 431000, status: 'sold',      statusLabel: 'Vendida',      view: 'Lake Facing',  type: '1 Bed+FR', client: 'Carlos Méndez', clientId: 1, brokerId: 1, reservedAt: '2026-03-15' },
    { id: 112, num: 'U-112', floor: 1, bed: 1, bath: 1, sqft: 820,  sqftTer: 170, price: 365000, status: 'available', statusLabel: 'Disponible',  view: 'Garden View',  type: '1 Bed' },
    { id: 201, num: 'U-201', floor: 2, bed: 1, bath: 1, sqft: 870,  sqftTer: 180, price: 388000, status: 'available', statusLabel: 'Disponible',  view: 'Garden View',  type: 'Studio+' },
    { id: 205, num: 'U-205', floor: 2, bed: 2, bath: 2, sqft: 1050, sqftTer: 210, price: 389000, status: 'pending',   statusLabel: 'Reservada',    view: 'Pool View',    type: '2 Bed', client: 'Ana García',     clientId: 2, brokerId: null, reservationDeadline: '2026-05-10', reservedAt: '2026-03-22' },
    { id: 308, num: 'U-308', floor: 3, bed: 2, bath: 2, sqft: 1100, sqftTer: 230, price: 415000, status: 'pending',   statusLabel: 'Reservada',    view: 'Lake View',    type: '2 Bed', client: 'Luis Pérez',     clientId: 3, brokerId: 1,    reservationDeadline: '2026-06-10', reservedAt: '2026-02-10' },
    { id: 312, num: 'U-312', floor: 3, bed: 3, bath: 3, sqft: 1380, sqftTer: 290, price: 445000, status: 'pending',   statusLabel: 'Reservada',    view: 'Ocean View',   type: '3 Bed', client: 'Sophie Martin',  clientId: 6, brokerId: 2,    reservationDeadline: '2026-06-01', reservedAt: '2026-04-12' },
    { id: 401, num: 'U-401', floor: 4, bed: 3, bath: 3, sqft: 1500, sqftTer: 350, price: 480000, status: 'sold',      statusLabel: 'Vendida',      view: 'Penthouse',    type: 'PH',    client: 'James Wilson',   clientId: 7, brokerId: null, reservedAt: '2025-11-15' },
  ],

  // ── Brokers ────────────────────────────────
  brokers: [
    { id: 1, name: 'José Rodríguez', agency: 'JR Real Estate', email: 'jose@jrre.com', country: 'RD', status: 'active', clients: 5, commission: '$8,620', contract: 'active', contractExpiry: '2027-01-15' },
    { id: 2, name: 'Michelle Torres', agency: 'Caribe Properties', email: 'michelle@caribe.com', country: 'USA', status: 'active', clients: 3, commission: '$5,200', contract: 'active', contractExpiry: '2026-12-01' },
    { id: 3, name: 'Agencia XYZ', agency: 'XYZ International', email: 'contact@xyz.com', country: 'España', status: 'pending', clients: 0, commission: '$0', contract: 'pending', contractExpiry: null },
    { id: 4, name: 'Laura Jiménez', agency: 'Premium RD', email: 'laura@premrd.com', country: 'RD', status: 'active', clients: 7, commission: '$14,300', contract: 'expiring', contractExpiry: '2026-06-01' },
  ],

  // ── Usuarios ───────────────────────────────
  users: [
    { id: 1, name: 'Admin Duna', email: 'admin@dunacapecana.com', role: 'admin', roleLabel: 'Administrador', status: 'active', lastLogin: 'Ahora' },
    { id: 2, name: 'Carlos Méndez', email: 'carlos@email.com', role: 'buyer', roleLabel: 'Comprador', status: 'active', lastLogin: 'hace 20 min' },
    { id: 3, name: 'Ana García', email: 'ana.garcia@email.com', role: 'buyer', roleLabel: 'Comprador', status: 'active', lastLogin: 'hace 1h' },
    { id: 4, name: 'José Rodríguez', email: 'jose@jrre.com', role: 'broker', roleLabel: 'Broker', status: 'active', lastLogin: 'hace 1 día' },
    { id: 5, name: 'Agencia XYZ', email: 'contact@xyz.com', role: 'broker', roleLabel: 'Broker', status: 'pending', lastLogin: 'Nunca' },
  ],

  // ── Documentos ────────────────────────────
  documents: [
    { id: 1, name: 'KYC — Carlos Méndez',          client: 'Carlos Méndez', type: 'KYC',            status: 'pending',   statusLabel: 'Pendiente revisión', statusCls: 'orange', uploaded: '2026-05-01', deadline: '2026-05-08', size: '2.4 MB' },
    { id: 2, name: 'Promesa — Ana García',          client: 'Ana García',   type: 'Promesa',         status: 'signature', statusLabel: 'Por firmar',         statusCls: 'red',    uploaded: '2026-05-03', deadline: '2026-05-06', size: '1.1 MB' },
    { id: 3, name: 'Plan de pagos — Luis Pérez',    client: 'Luis Pérez',   type: 'Plan de Pagos',   status: 'approved',  statusLabel: 'Aprobado',           statusCls: 'green',  uploaded: '2026-04-20', deadline: null,         size: '340 KB' },
    { id: 4, name: 'Contrato — María López',        client: 'María López',  type: 'Contrato',        status: 'signed',    statusLabel: 'Firmado',            statusCls: 'green',  uploaded: '2026-03-15', deadline: null,         size: '3.2 MB' },
    { id: 5, name: 'KYC — Sophie Martin',           client: 'Sophie Martin',type: 'KYC',             status: 'pending',   statusLabel: 'Pendiente revisión', statusCls: 'orange', uploaded: '2026-05-02', deadline: '2026-05-09', size: '1.8 MB' },
    { id: 6, name: 'Pasaporte — Roberto Silva',     client: 'Roberto Silva',type: 'Identificación',  status: 'expired',   statusLabel: 'Vencido',            statusCls: 'red',    uploaded: '2026-02-01', deadline: '2026-04-01', size: '890 KB' },
    { id: 7, name: 'Contrato — James Wilson',       client: 'James Wilson', type: 'Contrato',        status: 'signed',    statusLabel: 'Firmado',            statusCls: 'green',  uploaded: '2025-12-10', deadline: null,         size: '3.5 MB' },
    { id: 8, name: 'Reserva — Sophie Martin',       client: 'Sophie Martin',type: 'Reserva',         status: 'approved',  statusLabel: 'Aprobado',           statusCls: 'green',  uploaded: '2026-04-12', deadline: null,         size: '780 KB' },
  ],

  // ── Contratos ─────────────────────────────
  contracts: [
    { id: 1, client: 'Carlos Méndez', unit: 'Unidad 111', project: 'Makai', type: 'Reserva',  typeCls: 'blue',   status: 'active',            statusLabel: 'Activa',        statusCls: 'green',  paid: '$21,550',  total: '$431,000', signed: '2026-03-15', expires: null },
    { id: 2, client: 'Ana García',    unit: 'Unidad 205', project: 'Makai', type: 'Promesa',  typeCls: 'orange', status: 'pending_signature', statusLabel: 'Por firmar',    statusCls: 'red',    paid: '$19,450',  total: '$389,000', signed: null,         expires: '2026-05-10' },
    { id: 3, client: 'Luis Pérez',    unit: 'Unidad 308', project: 'Makai', type: 'Reserva',  typeCls: 'blue',   status: 'active',            statusLabel: 'Activa',        statusCls: 'green',  paid: '$62,250',  total: '$415,000', signed: '2026-02-10', expires: null },
    { id: 4, client: 'María López',   unit: 'Unidad 114', project: 'Makai', type: 'Contrato', typeCls: 'green',  status: 'active',            statusLabel: 'Vigente',       statusCls: 'green',  paid: '$215,500', total: '$431,000', signed: '2026-01-28', expires: null },
    { id: 5, client: 'Roberto Silva', unit: 'Unidad 220', project: 'Makai', type: 'Reserva',  typeCls: 'blue',   status: 'overdue',           statusLabel: 'Pago vencido',  statusCls: 'red',    paid: '$19,750',  total: '$395,000', signed: '2026-04-01', expires: null },
    { id: 6, client: 'Sophie Martin', unit: 'Unidad 312', project: 'Makai', type: 'Reserva',  typeCls: 'blue',   status: 'active',            statusLabel: 'Activa',        statusCls: 'green',  paid: '$89,000',  total: '$445,000', signed: '2026-04-12', expires: null },
    { id: 7, client: 'James Wilson',  unit: 'Unidad 401', project: 'Makai', type: 'Contrato', typeCls: 'green',  status: 'completed',         statusLabel: 'Completado',    statusCls: 'gray',   paid: '$480,000', total: '$480,000', signed: '2025-12-01', expires: null },
  ],

  // ── Transacciones ─────────────────────────
  transactions: [
    { id: 1, client: 'Carlos Méndez', unit: 'U-111', concept: 'Cuota inicial — Reserva',     amount: '$21,550',  status: 'confirmed', statusLabel: 'Confirmado', statusCls: 'green',  date: '2026-03-15', method: 'Wire Transfer' },
    { id: 2, client: 'Ana García',    unit: 'U-205', concept: 'Cuota inicial — Reserva',     amount: '$19,450',  status: 'confirmed', statusLabel: 'Confirmado', statusCls: 'green',  date: '2026-03-22', method: 'Wire Transfer' },
    { id: 3, client: 'Luis Pérez',    unit: 'U-308', concept: 'Cuota 1/24 — Plan de pagos',  amount: '$17,291',  status: 'confirmed', statusLabel: 'Confirmado', statusCls: 'green',  date: '2026-04-10', method: 'ACH' },
    { id: 4, client: 'Luis Pérez',    unit: 'U-308', concept: 'Cuota 2/24 — Plan de pagos',  amount: '$17,291',  status: 'pending',   statusLabel: 'Pendiente',  statusCls: 'orange', date: '2026-05-10', method: 'ACH' },
    { id: 5, client: 'María López',   unit: 'U-114', concept: 'Cuota 5/24 — Plan de pagos',  amount: '$17,958',  status: 'confirmed', statusLabel: 'Confirmado', statusCls: 'green',  date: '2026-04-28', method: 'Wire Transfer' },
    { id: 6, client: 'Roberto Silva', unit: 'U-220', concept: 'Cuota 1/24 — Plan de pagos',  amount: '$16,458',  status: 'overdue',   statusLabel: 'Vencido',    statusCls: 'red',    date: '2026-04-01', method: 'Wire Transfer' },
    { id: 7, client: 'Sophie Martin', unit: 'U-312', concept: 'Cuota inicial — Reserva',     amount: '$89,000',  status: 'confirmed', statusLabel: 'Confirmado', statusCls: 'green',  date: '2026-04-12', method: 'Wire Transfer' },
    { id: 8, client: 'James Wilson',  unit: 'U-401', concept: 'Pago total — Contrato',       amount: '$480,000', status: 'confirmed', statusLabel: 'Confirmado', statusCls: 'green',  date: '2025-12-01', method: 'Wire Transfer' },
  ],

  // ── Avance de obra ────────────────────────
  construction: {
    makai: {
      overall: 52,
      phases: [
        { name: 'Cimentación',   pct: 100, status: 'done',    date: 'Jun 2025' },
        { name: 'Estructura',    pct: 100, status: 'done',    date: 'Dic 2025' },
        { name: 'Mampostería',   pct: 75,  status: 'active',  date: 'En curso' },
        { name: 'Instalaciones', pct: 40,  status: 'active',  date: 'En curso' },
        { name: 'Acabados',      pct: 0,   status: 'pending', date: 'Q3 2026'  },
        { name: 'Entrega',       pct: 0,   status: 'pending', date: 'Q4 2026'  },
      ],
      reports: [
        { date: '2026-05-01', period: 'Mayo 2026',   title: 'Reporte mensual',      desc: 'Mampostería piso 3 al 80%. Instalaciones eléctricas en progreso en pisos 1–2.' },
        { date: '2026-04-01', period: 'Abril 2026',  title: 'Reporte mensual',      desc: 'Estructura completada. Inicio de mampostería en pisos 1 y 2.' },
        { date: '2026-01-15', period: 'Q4 2025',     title: 'Reporte trimestral',   desc: 'Estructura de pisos 3 y 4 completada al 100%. Inspección aprobada.' },
      ],
    },
  },

  // ── Plantillas ────────────────────────────
  templates: [
    { id: 1, name: 'Bienvenida — Reserva confirmada',  category: 'Bienvenida',   channel: 'Email + WhatsApp', lastUsed: 'hace 2 días',    uses: 23 },
    { id: 2, name: 'KYC — Documentos pendientes',      category: 'Seguimiento',  channel: 'Email',            lastUsed: 'hace 1 día',     uses: 41 },
    { id: 3, name: 'Recordatorio de cuota',            category: 'Pagos',        channel: 'WhatsApp',         lastUsed: 'hace 3h',        uses: 87 },
    { id: 4, name: 'Aviso pago vencido',               category: 'Pagos',        channel: 'Email + WhatsApp', lastUsed: 'hace 1 día',     uses: 34 },
    { id: 5, name: 'Promesa de compraventa lista',     category: 'Legal',        channel: 'Email',            lastUsed: 'hace 5 días',    uses: 18 },
    { id: 6, name: 'Actualización avance de obra',     category: 'Proyectos',    channel: 'Email',            lastUsed: 'hace 1 mes',     uses: 12 },
    { id: 7, name: 'Felicitación cierre de contrato',  category: 'Bienvenida',   channel: 'WhatsApp',         lastUsed: 'hace 2 semanas', uses: 9  },
    { id: 8, name: 'Solicitud documentos faltantes',   category: 'Seguimiento',  channel: 'Email + WhatsApp', lastUsed: 'hace 4 días',    uses: 29 },
  ],

  // ── Anuncios internos ─────────────────────
  announcements: [
    { id: 1, title: 'Actualización de precios Makai — Q2 2026',     body: 'A partir del 1 de junio entran en vigor los nuevos precios lista para Makai Residences. Todas las cotizaciones activas tienen 30 días de gracia.',            audience: 'Todos',          date: '2026-04-28', author: 'Admin Duna', pinned: true },
    { id: 2, title: 'Nuevo proceso de aprobación de descuentos',    body: 'Todo descuento mayor al 3% sobre precio lista requiere aprobación doble: Gerente Comercial + Administración. El flujo está activo en el CRM.',             audience: 'Equipo interno', date: '2026-04-15', author: 'Admin Duna', pinned: true },
    { id: 3, title: 'Capacitación CRM — Mayo 2026',                 body: 'Sesión de capacitación el jueves 9 de mayo a las 10 AM (hora RD). Se cubrirán los módulos de expedientes, documentos y aprobaciones. Asistencia obligatoria.', audience: 'Todos',          date: '2026-05-01', author: 'Admin Duna', pinned: false },
    { id: 4, title: 'Cierre de ventas Unidad 401 — James Wilson',   body: 'Felicitamos al equipo por el cierre exitoso de la Unidad 401 (Penthouse). Pago total de $480,000 USD confirmado.',                                            audience: 'Equipo interno', date: '2025-12-02', author: 'Admin Duna', pinned: false },
  ],

  // ── KPIs para el dashboard ─────────────────
  kpis: {
    expedientes: { value: 41, sub: '8 incompletos · 3 sin asesor', color: 'green' },
    documentos:  { value: 5,  sub: '3 sin revisar · 2 por firmar', color: 'orange' },
    aprobaciones:{ value: 7,  sub: '3 KYC · 2 contratos · 2 brokers', color: 'red' },
    tareas:      { value: 12, sub: '4 escaladas hoy · sin resolver', color: 'blue' },
  },

};
