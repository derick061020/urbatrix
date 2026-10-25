(function () {
  'use strict';

  var ENDPOINT = 'https://n8n.vexoraia.com/webhook/c8025d73-3192-41fc-a200-6b03b79e886d';

  var host = document.getElementById('landmass-formulario');
  if (!host || host.getAttribute('data-listo')) return;
  host.setAttribute('data-listo', '1');

  function attr(name) { return (host.getAttribute(name) || '').trim(); }
  function isFalta(v) { return v === '' || v.toUpperCase() === 'FALTA'; }

  var esPrueba = location.protocol === 'file:' || /^(localhost|127\.0\.0\.1|\[::1\])$/.test(location.hostname)
    // Añadido: la web de Bahía Mar aún es una demo interna y no hay política de
    // privacidad (Derick, 24 oct). La vista pone data-demo="1" mientras falte.
    || host.getAttribute('data-demo') === '1';

  var responsable = attr('data-responsable');
  var politica = attr('data-politica-url');
  var mercado = attr('data-mercado').toLowerCase() === 'rd' ? 'rd' : 'es';

  function leerProyecto(n) {
    var g = function (a) { return (n.getAttribute(a) || '').trim(); };
    return {
      id: g('data-cliente-id'),
      nombre: isFalta(g('data-proyecto')) ? '' : g('data-proyecto').slice(0, 80),
      tipologias: g('data-tipologias').split(',').map(function (t) { return t.trim(); }).filter(Boolean)
    };
  }
  var proyectos = attr('data-cliente-id') ? [leerProyecto(host)]
    : Array.prototype.map.call(host.querySelectorAll('[data-cliente-id]'), leerProyecto);
  var varios = proyectos.length > 1;
  var unico = varios ? null : proyectos[0];

  if (!proyectos.length || proyectos.length > 10) return;
  var vistos = {};
  for (var i = 0; i < proyectos.length; i++) {
    var pr = proyectos[i];
    if (!/^[A-Za-z0-9][A-Za-z0-9_-]{1,63}$/.test(pr.id)) return;
    if (vistos[pr.id.toLowerCase()]) return;
    vistos[pr.id.toLowerCase()] = true;
    if (!pr.tipologias.length || pr.tipologias.length > 12 ||
        pr.tipologias.some(function (t) { return t.length > 40 || t.toUpperCase() === 'FALTA'; })) {
      return;
    }
    if (varios && !pr.nombre) return;
  }
  if (isFalta(responsable)) return;
  var politicaOk = false;
  if (!isFalta(politica)) {
    try { politicaOk = new URL(politica).protocol === 'https:'; } catch (e) { politicaOk = false; }
    if (!politicaOk) return;
  }
  if (!politicaOk && !esPrueba) return;

  var respTxt = responsable.slice(0, 120);

  var FINALIDADES = [
    ['vivir', 'Para vivir'],
    ['inversion', 'Inversi\u00f3n'],
    ['segunda_residencia', 'Segunda residencia']
  ];
  var PLAZOS = [
    ['menos_6m', 'En menos de 6 meses'],
    ['6_12m', 'En 6-12 meses'],
    ['1_2a', 'En 1-2 a\u00f1os'],
    ['mas_2a', 'En m\u00e1s de 2 a\u00f1os'],
    ['sin_prisa', 'Sin prisa: puedo esperar a la entrega']
  ];
  var FORMAS_PAGO = mercado === 'rd' ? [
    ['contado', 'Al contado'],
    ['entrada_disponible', 'Tengo la inicial disponible y financiar\u00e9 el resto a la entrega'],
    ['entrada_meses', 'Tendr\u00e9 la inicial en los pr\u00f3ximos meses y financiar\u00e9 el resto a la entrega'],
    ['vender_otra', 'Depende de que venda otra propiedad'],
    ['no_se', 'A\u00fan no s\u00e9 c\u00f3mo pagar\u00e9']
  ] : [
    ['contado', 'Al contado'],
    ['entrada_disponible', 'Tengo la entrada disponible y pedir\u00e9 una hipoteca para el resto'],
    ['entrada_meses', 'Tendr\u00e9 la entrada en los pr\u00f3ximos meses y pedir\u00e9 una hipoteca'],
    ['vender_otra', 'Depende de que venda otra vivienda'],
    ['no_se', 'A\u00fan no s\u00e9 c\u00f3mo pagar\u00e9']
  ];
  var MOMENTOS = [
    ['reservar', 'Quiero reservar cuanto antes'],
    ['visitar', 'Quiero visitar el proyecto o tener una videollamada'],
    ['comparando', 'Estoy comparando opciones'],
    ['informando', 'Solo me estoy informando']
  ];
  var EJEMPLO_PRESUPUESTO = mercado === 'rd' ? 'Ej.: US$ 350,000' : 'Ej.: 250.000 \u20ac';

  var CSS = [
    ':host{display:block;max-width:560px;margin:0 auto}',
    '*{box-sizing:border-box}',
    '.wrap{font:inherit;color:inherit;line-height:1.45}',
    'h2{font-size:1.35em;line-height:1.25;margin:0 0 .25em}',
    '.sub{margin:0 0 1.25em;opacity:.8}',
    '.campo{margin:0 0 1.15em;padding:0;border:0;min-width:0}',
    'label,legend{display:block;font-weight:600;margin:0 0 .4em;padding:0}',
    'input[type=text],select{width:100%;font:inherit;font-size:16px;padding:.65em .75em;border:1px solid rgba(128,128,128,.6);border-radius:8px;background:var(--lm-field,#fff);color:var(--lm-field-text,#1a1a1a)}',
    '.ayuda{margin:.35em 0 0;font-size:.875em;opacity:.75}',
    'input:focus-visible,select:focus-visible,button:focus-visible{outline:3px solid var(--lm-accent,#1b5e5a);outline-offset:2px}',
    '.invalid input[type=text],.invalid select{border-color:#b3261e}',
    '.aviso{font-size:.8125em;opacity:.85;margin:0 0 .9em}',
    '.aviso a{color:inherit}',
    '.chk{display:flex;gap:.6em;align-items:flex-start;font-weight:400;margin:0}',
    '.chk input{margin-top:.3em;width:1.1em;height:1.1em;flex:none;accent-color:var(--lm-accent,#1b5e5a)}',
    'button{font:inherit;font-weight:600;width:100%;padding:.85em 1em;border:0;border-radius:8px;background:var(--lm-accent,#1b5e5a);color:var(--lm-accent-text,#fff);cursor:pointer}',
    'button[disabled]{opacity:.6;cursor:wait}',
    '.error{margin:0 0 1em;padding:.7em .9em;border-radius:8px;background:#fdecea;color:#7a1010}',
    '.ok{padding:1.2em;border:1px solid var(--lm-accent,#1b5e5a);border-radius:8px}',
    '.ok h3{margin:0 0 .3em}',
    '.ok p{margin:0}',
    '.trampa{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}',
    '[hidden]{display:none!important}'
  ].join('');

  function h(tag, props, kids) {
    var n = document.createElement(tag);
    if (props) Object.keys(props).forEach(function (k) {
      if (k === 'class') n.className = props[k];
      else if (k === 'text') n.textContent = props[k];
      else n.setAttribute(k, props[k]);
    });
    (kids || []).forEach(function (c) {
      if (c) n.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
    });
    return n;
  }
  function campo(id, etiqueta, control, ayuda) {
    return h('div', { class: 'campo', 'data-campo': id }, [
      h('label', { 'for': id, text: etiqueta }), control,
      ayuda ? h('p', { class: 'ayuda', text: ayuda }) : null
    ]);
  }
  function desplegable(id, etiqueta, opciones, marcador) {
    return campo(id, etiqueta, selectDe(id, marcador || 'Elige una opci\u00f3n', opciones));
  }
  function selectDe(id, marcador, opciones, extra) {
    var s = h('select', { id: id, name: id });
    s.appendChild(h('option', { value: '', text: marcador }));
    opciones.forEach(function (o) { s.appendChild(h('option', { value: o[0], text: o[1] })); });
    if (extra) s.appendChild(h('option', { value: extra[0], text: extra[1] }));
    return s;
  }

  var root = host.attachShadow ? host.attachShadow({ mode: 'open' }) : host;
  var wrap = h('div', { class: 'wrap' });
  var estiloListo = false;
  try {
    if (host.attachShadow && typeof CSSStyleSheet === 'function' && 'adoptedStyleSheets' in root) {
      var hoja = new CSSStyleSheet();
      hoja.replaceSync(CSS);
      root.adoptedStyleSheets = [hoja];
      estiloListo = true;
    }
  } catch (e) { estiloListo = false; }
  if (!estiloListo) root.appendChild(h('style', { text: CSS }));
  root.appendChild(wrap);

  var form = h('form', { novalidate: 'novalidate' });
  wrap.appendChild(form);
  form.appendChild(h('h2', { text: unico && unico.nombre ? 'Solicita informaci\u00f3n de ' + unico.nombre : 'Solicita informaci\u00f3n' }));
  form.appendChild(h('p', { class: 'sub', text: 'Cu\u00e9ntanos qu\u00e9 buscas y el equipo de ventas te contactar\u00e1. Los campos con * son obligatorios.' }));

  var nombre = h('input', { type: 'text', id: 'nombre', name: 'nombre', maxlength: '80', autocomplete: 'name' });
  var contacto = h('input', { type: 'text', id: 'contacto', name: 'contacto', maxlength: '120', autocomplete: 'email', inputmode: 'email' });
  var tipologia = h('select', { id: 'tipologia', name: 'tipologia' });
  function cargarTipologias(p) {
    while (tipologia.firstChild) tipologia.removeChild(tipologia.firstChild);
    if (!p) {
      tipologia.appendChild(h('option', { value: '', text: 'Elige primero un proyecto' }));
      tipologia.disabled = true;
      return;
    }
    tipologia.disabled = false;
    tipologia.appendChild(h('option', { value: '', text: 'Elige una opci\u00f3n' }));
    p.tipologias.forEach(function (t) { tipologia.appendChild(h('option', { value: t, text: t })); });
    tipologia.appendChild(h('option', { value: '__nose__', text: 'A\u00fan no lo s\u00e9' }));
  }
  cargarTipologias(unico);
  var plazo = selectDe('plazo_vivienda', 'Elige una opci\u00f3n', PLAZOS);
  var presupuesto = h('input', { type: 'text', id: 'presupuesto', name: 'presupuesto', maxlength: '60', placeholder: EJEMPLO_PRESUPUESTO });

  form.appendChild(campo('nombre', 'Nombre *', nombre));
  form.appendChild(campo('contacto', 'Email o tel\u00e9fono *', contacto, 'Usaremos este dato solo para responderte.'));
  if (varios) form.appendChild(desplegable('proyecto', '\u00bfQu\u00e9 proyecto te interesa? *', proyectos.map(function (p) { return [p.id, p.nombre]; }), 'Elige un proyecto'));
  form.appendChild(campo('tipologia', 'Tipolog\u00eda que te interesa *', tipologia));
  form.appendChild(desplegable('finalidad', '\u00bfPara qu\u00e9 buscas la propiedad? *', FINALIDADES));
  var campoPlazo = campo('plazo_vivienda', '\u00bfCu\u00e1ndo necesitas la vivienda? *', plazo);
  campoPlazo.hidden = true;
  form.appendChild(campoPlazo);
  form.appendChild(campo('presupuesto', 'Presupuesto aproximado', presupuesto, 'Opcional. Indica la moneda.'));
  form.appendChild(desplegable('forma_pago', '\u00bfC\u00f3mo piensas pagar? *', FORMAS_PAGO));
  form.appendChild(desplegable('momento', '\u00bfEn qu\u00e9 momento est\u00e1s? *', MOMENTOS));

  form.appendChild(h('div', { class: 'trampa', 'aria-hidden': 'true' }, [
    h('label', { 'for': 'website', text: 'No rellenar' }),
    h('input', { type: 'text', id: 'website', name: 'website', tabindex: '-1', autocomplete: 'off' })
  ]));

  var aviso = h('p', { class: 'aviso' }, [
    'Responsable del tratamiento: ' + respTxt + '. Usaremos tus datos (nombre, contacto y respuestas) para atender tu solicitud' +
    (unico && unico.nombre ? ' sobre ' + unico.nombre : (varios ? ' sobre el proyecto que elijas' : '')) + ' y contactarte. Un proveedor tecnol\u00f3gico que act\u00faa por encargo de ' + respTxt +
    ' gestiona este formulario, guarda tus datos y ordena las solicitudes con un sistema automatizado que no recibe tu nombre ni tu contacto. ' +
    'Conservaremos tus datos 12 meses. Puedes ejercer tus derechos de acceso, rectificaci\u00f3n y supresi\u00f3n como indica la pol\u00edtica de privacidad de ' + respTxt + '. ',
    politicaOk ? h('a', { href: politica, target: '_blank', rel: 'noopener noreferrer', text: 'Pol\u00edtica de privacidad' }) : null
  ]);
  form.appendChild(aviso);
  var acepta = h('input', { type: 'checkbox', id: 'acepta', name: 'acepta_privacidad' });
  form.appendChild(h('div', { class: 'campo', 'data-campo': 'acepta' }, [
    h('label', { class: 'chk', 'for': 'acepta' }, [acepta, h('span', { text: 'He le\u00eddo el aviso de privacidad y acepto el tratamiento de mis datos. *' })])
  ]));

  var errorBox = h('div', { class: 'error', role: 'alert', hidden: 'hidden' });
  form.appendChild(errorBox);
  var boton = h('button', { type: 'submit', text: 'Solicitar informaci\u00f3n' });
  form.appendChild(boton);

  function valor(name) {
    var s = form.querySelector('select[name="' + name + '"]');
    return s ? s.value : '';
  }
  function proyectoElegido() {
    if (!varios) return unico;
    var id = valor('proyecto');
    for (var k = 0; k < proyectos.length; k++) if (proyectos[k].id === id) return proyectos[k];
    return null;
  }
  form.addEventListener('change', function (ev) {
    if (ev.target && ev.target.name === 'proyecto') cargarTipologias(proyectoElegido());
    if (ev.target && ev.target.name === 'finalidad') {
      campoPlazo.hidden = valor('finalidad') !== 'vivir';
      if (campoPlazo.hidden) plazo.value = '';
    }
  });

  function alCorregir(ev) {
    var c = ev.target && ev.target.closest ? ev.target.closest('[data-campo]') : null;
    if (c && c.classList.contains('invalid')) {
      c.classList.remove('invalid');
      if (!form.querySelector('.invalid')) { errorBox.hidden = true; errorBox.textContent = ''; }
    }
  }
  form.addEventListener('change', alCorregir);
  form.addEventListener('input', alCorregir);

  function mostrarError(msg) { errorBox.textContent = msg; errorBox.hidden = false; }
  function limpiarErrores() {
    errorBox.hidden = true; errorBox.textContent = '';
    Array.prototype.forEach.call(form.querySelectorAll('.invalid'), function (n) { n.classList.remove('invalid'); });
  }
  function marcar(id) {
    var c = form.querySelector('[data-campo="' + id + '"]');
    if (c) c.classList.add('invalid');
  }

  function validar() {
    var fallos = [];
    var c = contacto.value.trim();
    var dig = c.replace(/\D/g, '');
    var esEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(c);
    var esTel = /^\+?[\d\s().-]+$/.test(c) && dig.length >= 8 && dig.length <= 15;
    if (!nombre.value.trim()) fallos.push(['nombre', 'Indica tu nombre.']);
    if (!c) fallos.push(['contacto', 'Indica un email o un tel\u00e9fono de contacto.']);
    else if (!esEmail && !esTel) fallos.push(['contacto', 'Revisa tu contacto: introduce un email o un tel\u00e9fono v\u00e1lido.']);
    if (varios && !proyectoElegido()) fallos.push(['proyecto', 'Elige el proyecto que te interesa.']);
    else if (!tipologia.value) fallos.push(['tipologia', 'Elige una tipolog\u00eda (o \u00abA\u00fan no lo s\u00e9\u00bb).']);
    if (!valor('finalidad')) fallos.push(['finalidad', 'Indica para qu\u00e9 buscas la propiedad.']);
    if (valor('finalidad') === 'vivir' && !plazo.value) fallos.push(['plazo_vivienda', 'Indica cu\u00e1ndo necesitas la vivienda.']);
    if (!valor('forma_pago')) fallos.push(['forma_pago', 'Indica c\u00f3mo piensas pagar.']);
    if (!valor('momento')) fallos.push(['momento', 'Indica en qu\u00e9 momento est\u00e1s.']);
    if (!acepta.checked) fallos.push(['acepta', 'Debes aceptar el aviso de privacidad para enviar el formulario.']);
    return fallos;
  }

  var enviando = false;
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (enviando) return;
    limpiarErrores();
    var fallos = validar();
    if (fallos.length) {
      fallos.forEach(function (f) { marcar(f[0]); });
      mostrarError(fallos[0][1]);
      var primero = form.querySelector('.invalid input, .invalid select');
      if (primero) primero.focus();
      return;
    }

    var finalidad = valor('finalidad');
    var datos = {
      cliente_id: proyectoElegido().id,
      nombre: nombre.value.trim(),
      contacto: contacto.value.trim(),
      tipologia: tipologia.value === '__nose__' ? '' : tipologia.value,
      finalidad: finalidad,
      presupuesto: presupuesto.value.trim(),
      forma_pago: valor('forma_pago'),
      momento: valor('momento'),
      acepta_privacidad: 'si',
      website: form.querySelector('#website').value
    };
    if (finalidad === 'vivir') datos.plazo_vivienda = plazo.value;

    enviando = true;
    boton.disabled = true;
    boton.textContent = 'Enviando\u2026';
    var ctl = ('AbortController' in window) ? new AbortController() : null;
    var corte = ctl ? setTimeout(function () { ctl.abort(); }, 20000) : null;

    function fin() { enviando = false; boton.disabled = false; boton.textContent = 'Solicitar informaci\u00f3n'; if (corte) clearTimeout(corte); }

    fetch(ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos),
      signal: ctl ? ctl.signal : undefined
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (cuerpo) {
        if (res.ok) {
          var msg = (cuerpo && typeof cuerpo.message === 'string' && cuerpo.message) ? cuerpo.message : 'Gracias, nos pondremos en contacto contigo.';
          form.hidden = true;
          wrap.appendChild(h('div', { class: 'ok', role: 'status' }, [h('h3', { text: '\u00a1Solicitud enviada!' }), h('p', { text: msg })]));
          return;
        }
        fin();
        if (res.status === 400 && cuerpo && typeof cuerpo.error === 'string' && cuerpo.error) mostrarError(cuerpo.error);
        else if (res.status === 404) mostrarError('Este formulario no est\u00e1 disponible en este momento.');
        else if (res.status === 429) mostrarError('Hemos recibido demasiados env\u00edos seguidos. Espera un minuto e int\u00e9ntalo de nuevo.');
        else mostrarError('No hemos podido enviar tu solicitud. Int\u00e9ntalo de nuevo en unos minutos.');
      });
    }).catch(function () {
      fin();
      mostrarError('No hemos podido enviar tu solicitud. Revisa tu conexi\u00f3n e int\u00e9ntalo de nuevo.');
    });
  });
})();
