// Chatbot de Bahía Mar (Vexora · Izan, 21 oct 2026). Código del proveedor tal cual;
// sólo URL_PRIVACIDAD se lee del atributo data-privacidad de la etiqueta <script>.
(function () {
  var CONFIG = {
    CLIENTE_ID: "landmass_bahiamar",
    WEBHOOK_URL: "https://n8n.vexoraia.com/webhook/d70b09a5-8bac-4005-a955-09a8fbcab0ad",
    TITULO: "Bahía Mar · Información y ventas",
    SALUDO: "¡Hola! Bienvenido/a al chat de Bahía Mar, de Landmass Capital. ¿En qué puedo ayudarte hoy? 😊",
    COLOR: "#1a1a1a",
    COLOR_TEXTO: "#ffffff",
    URL_PRIVACIDAD: (document.currentScript && document.currentScript.getAttribute("data-privacidad")) || "",  // lo pone la vista desde company.privacy_url
    AVISO_PIE: "Precios y disponibilidad sujetos a confirmación del equipo de ventas.",
    POSICION: "derecha"        // "derecha" o "izquierda"
  };

  if (window.__vexoraChat) return;
  window.__vexoraChat = true;

  var MAX_CARACTERES = 500;
  var TIEMPO_MAX_MS = 30000;
  var CLAVE = "vx_chat_" + CONFIG.CLIENTE_ID;

  function leer(k) { try { return window.sessionStorage.getItem(k); } catch (e) { return null; } }
  function guardar(k, v) { try { window.sessionStorage.setItem(k, v); } catch (e) {} }
  function nuevaSesion() {
    try { if (window.crypto && window.crypto.randomUUID) return window.crypto.randomUUID(); } catch (e) {}
    return "vx" + Math.random().toString(36).slice(2) + Date.now().toString(36);
  }

  var CSS =
    ":host{all:initial}" +
    "*{box-sizing:border-box}" +
    ".vx{position:fixed;bottom:20px;right:20px;z-index:2147483000;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.45;color:#1a1a1a}" +
    ".vx.izq{right:auto;left:20px}" +
    ".lanzador{width:56px;height:56px;border-radius:50%;border:0;background:var(--c);color:var(--ct);cursor:pointer;box-shadow:0 6px 18px rgba(0,0,0,.25);display:flex;align-items:center;justify-content:center;padding:0}" +
    ".lanzador svg{width:26px;height:26px;fill:currentColor}" +
    ".lanzador:focus-visible,.cerrar:focus-visible,.enviar:focus-visible{outline:2px solid #2563eb;outline-offset:2px}" +
    ".panel{position:absolute;bottom:70px;right:0;width:360px;max-width:calc(100vw - 40px);height:520px;max-height:calc(100vh - 110px);background:#fff;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.22);display:none;flex-direction:column;overflow:hidden}" +
    ".vx.izq .panel{right:auto;left:0}" +
    ".panel.abierto{display:flex}" +
    ".cab{background:var(--c);color:var(--ct);padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:8px}" +
    ".cab-t{font-weight:600;font-size:15px}" +
    ".cab-s{font-size:12px;opacity:.85}" +
    ".cerrar{background:transparent;border:0;color:inherit;font-size:24px;line-height:1;cursor:pointer;padding:2px 6px}" +
    ".msgs{flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px;background:#f7f7f8}" +
    ".m{max-width:85%;padding:8px 12px;border-radius:12px;white-space:pre-wrap;overflow-wrap:anywhere}" +
    ".m.u{align-self:flex-end;background:var(--c);color:var(--ct);border-bottom-right-radius:4px}" +
    ".m.b{align-self:flex-start;background:#fff;border:1px solid #e5e5e5;border-bottom-left-radius:4px}" +
    ".m.b a{color:inherit;text-decoration:underline}" +
    ".esc span{display:inline-block;width:6px;height:6px;margin:0 2px;border-radius:50%;background:#999;animation:vxp 1s infinite ease-in-out}" +
    ".esc span:nth-child(2){animation-delay:.15s}.esc span:nth-child(3){animation-delay:.3s}" +
    "@keyframes vxp{0%,80%,100%{opacity:.3;transform:translateY(0)}40%{opacity:1;transform:translateY(-3px)}}" +
    "form{display:flex;border-top:1px solid #eee;background:#fff;margin:0}" +
    ".txt{flex:1;min-width:0;border:0;padding:12px;font:inherit;color:#1a1a1a;background:transparent;outline:none}" +
    ".enviar{border:0;background:transparent;padding:0 14px;cursor:pointer;color:var(--c);display:flex;align-items:center}" +
    ".enviar svg{width:20px;height:20px;fill:currentColor}" +
    ".txt:disabled,.enviar:disabled{opacity:.5;cursor:default}" +
    ".hp{position:absolute!important;left:-9999px!important;width:1px;height:1px;opacity:0}" +
    ".pie{font-size:11px;color:#6b6b6b;padding:6px 12px 8px;background:#fff;text-align:center}" +
    ".pie a{color:#6b6b6b}" +
    "@media (max-width:480px){.vx{bottom:16px;right:16px}.vx.izq{left:16px}" +
    ".panel{position:fixed;top:0;left:0;right:0;bottom:0;width:100%;max-width:100%;height:100%;max-height:100%;border-radius:0}" +
    ".cab{padding-top:calc(12px + env(safe-area-inset-top,0px))}" +
    ".pie{padding-bottom:calc(8px + env(safe-area-inset-bottom,0px))}" +
    ".txt{font-size:16px}}" +
    "@media (prefers-reduced-motion:reduce){.esc span{animation:none}}";

  var HTML =
    '<div class="vx">' +
      '<div class="panel" role="dialog" aria-label="Chat de atención al cliente">' +
        '<div class="cab"><div><div class="cab-t"></div><div class="cab-s">Asistente virtual con IA</div></div>' +
        '<button class="cerrar" type="button" aria-label="Cerrar chat">&times;</button></div>' +
        '<div class="msgs" aria-live="polite"></div>' +
        '<form novalidate>' +
          '<input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">' +
          '<input class="txt" type="text" maxlength="' + MAX_CARACTERES + '" placeholder="Escribe tu mensaje..." aria-label="Escribe tu mensaje" autocomplete="off">' +
          '<button class="enviar" type="submit" aria-label="Enviar mensaje"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg></button>' +
        '</form>' +
        '<div class="pie">Respuestas generadas por IA; pueden contener errores. No compartas datos sensibles.<span class="priv"></span></div>' +
      '</div>' +
      '<button class="lanzador" type="button" aria-label="Abrir chat" aria-expanded="false">' +
        '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H8l-4 4V6a2 2 0 0 1 2-2z"/></svg>' +
      '</button>' +
    '</div>';

  function iniciar() {
    var host = document.createElement("div");
    host.setAttribute("data-vexora-chat", "");
    document.body.appendChild(host);
    var root = host.attachShadow ? host.attachShadow({ mode: "open" }) : host;
    root.innerHTML = "<style>" + CSS + "</style>" + HTML;

    var vx = root.querySelector(".vx");
    var panel = root.querySelector(".panel");
    var lanzador = root.querySelector(".lanzador");
    var cerrarBtn = root.querySelector(".cerrar");
    var msgs = root.querySelector(".msgs");
    var form = root.querySelector("form");
    var entrada = root.querySelector(".txt");
    var boton = root.querySelector(".enviar");
    var trampa = root.querySelector(".hp");

    if (CONFIG.POSICION === "izquierda") vx.classList.add("izq");
    vx.style.setProperty("--c", CONFIG.COLOR);
    vx.style.setProperty("--ct", CONFIG.COLOR_TEXTO);
    root.querySelector(".cab-t").textContent = CONFIG.TITULO;
    if (CONFIG.AVISO_PIE) {
      root.querySelector(".pie").insertBefore(document.createTextNode(" " + CONFIG.AVISO_PIE), root.querySelector(".priv"));
    }
    if (CONFIG.URL_PRIVACIDAD) {
      var enlace = document.createElement("a");
      enlace.href = CONFIG.URL_PRIVACIDAD;
      enlace.target = "_blank";
      enlace.rel = "noopener noreferrer";
      enlace.textContent = "Privacidad";
      var priv = root.querySelector(".priv");
      priv.appendChild(document.createTextNode(" "));
      priv.appendChild(enlace);
    }

    var historial = [];
    try { historial = JSON.parse(leer(CLAVE + "_h") || "[]") || []; } catch (e) { historial = []; }
    var sesion = leer(CLAVE + "_s");
    var abierto = false, ocupado = false, pintado = false;

    function pintarTexto(nodo, texto) {
      var partes = String(texto).split(/(https?:\/\/[^\s<>"']+)/g);
      for (var i = 0; i < partes.length; i++) {
        var p = partes[i];
        if (!p) continue;
        if (/^https?:\/\//i.test(p)) {
          var url = p.replace(/[.,;:!?)\]]+$/, "");
          var a = document.createElement("a");
          a.href = url;
          a.target = "_blank";
          a.rel = "noopener noreferrer nofollow";
          a.textContent = url;
          nodo.appendChild(a);
          if (p.length > url.length) nodo.appendChild(document.createTextNode(p.slice(url.length)));
        } else {
          nodo.appendChild(document.createTextNode(p));
        }
      }
    }

    function agregar(texto, quien, persistir) {
      var d = document.createElement("div");
      d.className = "m " + quien;
      if (quien === "b") pintarTexto(d, texto); else d.textContent = texto;
      msgs.appendChild(d);
      msgs.scrollTop = msgs.scrollHeight;
      if (persistir) {
        historial.push({ q: quien, t: texto });
        if (historial.length > 40) historial = historial.slice(-40);
        guardar(CLAVE + "_h", JSON.stringify(historial));
      }
    }

    function escribiendo() {
      var d = document.createElement("div");
      d.className = "m b esc";
      d.setAttribute("aria-label", "Escribiendo");
      d.innerHTML = "<span></span><span></span><span></span>";
      msgs.appendChild(d);
      msgs.scrollTop = msgs.scrollHeight;
      return d;
    }

    function setOcupado(v) {
      ocupado = v;
      entrada.disabled = v;
      boton.disabled = v;
    }

    function textoRespuesta(estado, cuerpo) {
      var c = String(cuerpo || "").trim();
      var tecnico = !c || c.length > 2000 || /^[<{\[]/.test(c);
      if (!tecnico) return c;
      if (estado === 429) return "Has enviado muchos mensajes seguidos. Espera un minuto y vuelve a intentarlo.";
      return "Ahora mismo no puedo responder. Inténtalo de nuevo en unos minutos.";
    }

    function abrir() {
      abierto = true;
      panel.classList.add("abierto");
      lanzador.setAttribute("aria-expanded", "true");
      lanzador.setAttribute("aria-label", "Cerrar chat");
      if (!pintado) {
        pintado = true;
        agregar(CONFIG.SALUDO, "b", false);
        for (var i = 0; i < historial.length; i++) agregar(historial[i].t, historial[i].q, false);
      }
      setTimeout(function () { entrada.focus(); }, 50);
    }

    function cerrar() {
      abierto = false;
      panel.classList.remove("abierto");
      lanzador.setAttribute("aria-expanded", "false");
      lanzador.setAttribute("aria-label", "Abrir chat");
      lanzador.focus();
    }

    lanzador.addEventListener("click", function () { if (abierto) cerrar(); else abrir(); });
    cerrarBtn.addEventListener("click", cerrar);
    panel.addEventListener("keydown", function (e) { if (e.key === "Escape") cerrar(); });

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (ocupado) return;
      var texto = entrada.value.replace(/\s+/g, " ").trim();
      if (!texto) return;
      if (texto.length > MAX_CARACTERES) texto = texto.slice(0, MAX_CARACTERES);
      if (!sesion) { sesion = nuevaSesion(); guardar(CLAVE + "_s", sesion); }

      agregar(texto, "u", true);
      // Contexto para el asistente: los 6 turnos anteriores (sin el mensaje actual). El servidor descarta
      // cualquier respuesta del bot que no reconozca como suya.
      var previo = historial.slice(0, -1).slice(-6).map(function (h) { return { r: h.q, t: h.t }; });
      entrada.value = "";
      setOcupado(true);
      var indicador = escribiendo();
      var ctrl = window.AbortController ? new AbortController() : null;
      var temporizador = setTimeout(function () { if (ctrl) ctrl.abort(); }, TIEMPO_MAX_MS);

      function terminar() {
        clearTimeout(temporizador);
        if (indicador.parentNode) indicador.parentNode.removeChild(indicador);
        setOcupado(false);
        if (abierto) entrada.focus();
      }

      fetch(CONFIG.WEBHOOK_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ mensaje: texto, cliente_id: CONFIG.CLIENTE_ID, session_id: sesion, hp: trampa.value, historial: previo }),
        signal: ctrl ? ctrl.signal : undefined
      })
        .then(function (res) {
          return res.text().then(function (cuerpo) { return { estado: res.status, cuerpo: cuerpo }; });
        })
        .then(function (r) {
          terminar();
          var limpio = String(r.cuerpo || "").trim();
          var mostrar = textoRespuesta(r.estado, r.cuerpo);
          agregar(mostrar, "b", r.estado === 200 && mostrar === limpio);
        })
        .catch(function (err) {
          terminar();
          agregar(err && err.name === "AbortError"
            ? "La respuesta está tardando demasiado. Inténtalo de nuevo en un momento."
            : "No hay conexión con el asistente. Inténtalo de nuevo en unos minutos.", "b", false);
        });
    });
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", iniciar);
  else iniciar();
})();
