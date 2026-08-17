{{--
    ============================================================================
    Componente de subida animado — "upload morph"
    ============================================================================

    Una línea de progreso corre al ras del borde inferior del botón; al llenarse
    esa misma línea crece hasta ocupar el componente y se convierte en el estado
    completado.

    Se incluye una sola vez por página (ya está en los 5 layouts). Expone
    window.UploadMorph, que no sube nada por sí mismo: sólo dibuja el estado.
    Cada punto de subida conserva su propia lógica y le va avisando.

        var m = UploadMorph.mount(inputFile);   // crea el componente
        m.select(file);                         // muestra nombre y peso
        m.start();                              // entra en "subiendo"
        m.progress(42);                         // 0..100 (suavizado por rAF)
        m.done('Documento subido');             // línea se eleva + check
        m.fail('No se pudo subir', onRetry);    // mismo camino, en rojo
        m.reset();

    El color se toma del token `brand` de Tailwind leyendo un probe, así el
    mismo archivo funciona en main/makai (#5c7c68) y landmass (#074540) sin
    tocar una línea.
--}}

@once
@push('styles')
<style>
:root{
  --upl-brand:#5c7c68;                                        /* lo pisa el probe */
  --upl-deep:color-mix(in srgb, var(--upl-brand) 58%, #0d120f);
  --upl-groove:rgba(255,255,255,.17);
  --upl-err:#fb3748;
  --upl-r:16px;
  --upl-h:68px;
  --upl-btn-m:7px;
  --upl-btn-w:116px;
  --upl-line-h:5px;
  --upl-ease:cubic-bezier(.22,1,.36,1);
  --upl-spring:cubic-bezier(.34,1.3,.5,1);
}

.upl{position:relative;width:100%;height:var(--upl-h);isolation:isolate;
     font-family:'Inter',system-ui,sans-serif;-webkit-font-smoothing:antialiased}

/* ---- capa 2: pill + botón ---- */
.upl-card{
  position:absolute;inset:0;z-index:2;
  background:#fff;border-radius:var(--upl-r);overflow:hidden;display:flex;align-items:center;
  box-shadow:0 0 0 1px #eaecf0,0 1px 2px rgba(10,13,20,.05),0 10px 26px -16px rgba(10,13,20,.35);
  transition:transform .5s var(--upl-ease),opacity .32s var(--upl-ease),
             box-shadow .3s var(--upl-ease),background .25s var(--upl-ease);
}
.upl.is-done .upl-card,
.upl.is-error .upl-card{transform:translateY(-12px) scale(.97);opacity:0;box-shadow:none}

.upl.is-drag .upl-card{
  background:#eef2ef;
  box-shadow:0 0 0 2px var(--upl-brand),0 1px 2px rgba(10,13,20,.05),
             0 14px 30px -16px rgba(10,13,20,.4);
}

.upl-meta{
  flex:1;min-width:0;display:flex;align-items:center;gap:13px;cursor:pointer;margin:0;
  padding-left:11px;
  /* el botón es absolute: sin este padding el nombre pasa por debajo
     y el text-overflow nunca se dispara */
  padding-right:calc(var(--upl-btn-w) + var(--upl-btn-m)*2 + 14px);
  transition:opacity .22s var(--upl-ease),transform .32s var(--upl-ease);
}
.upl.is-uploading .upl-meta{opacity:0;transform:translateX(-14px)}

.upl-icon{
  flex:0 0 auto;width:42px;height:42px;border-radius:12px;
  background:#eef2ef;color:var(--upl-brand);
  display:grid;place-items:center;
  font-size:9px;font-weight:700;letter-spacing:.03em;
  transition:background .25s var(--upl-ease),color .25s var(--upl-ease);
}
.upl-icon svg{width:19px;height:19px;stroke:currentColor;fill:none;
              stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.upl.is-empty .upl-icon{background:#f2f5f8;color:#99a0ae}

.upl-txt{min-width:0;display:flex;flex-direction:column;gap:2.5px}
.upl-name{
  font-size:14px;font-weight:500;color:#171717;letter-spacing:-.012em;line-height:1.25;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.upl.is-empty .upl-name{color:#525866}
.upl-sub{
  font-size:11.5px;color:#717784;font-variant-numeric:tabular-nums;line-height:1.3;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}

.upl-btn{
  position:absolute;top:var(--upl-btn-m);right:var(--upl-btn-m);bottom:var(--upl-btn-m);
  width:var(--upl-btn-w);
  border:0;border-radius:11px;background:var(--upl-brand);color:#fff;cursor:pointer;
  font-family:inherit;font-size:14px;font-weight:600;letter-spacing:-.012em;
  display:grid;place-items:center;
  box-shadow:0 1px 2px rgba(10,13,20,.14);
  transition:width .62s var(--upl-ease),top .62s var(--upl-ease),right .62s var(--upl-ease),
             bottom .62s var(--upl-ease),border-radius .5s var(--upl-ease),
             filter .22s var(--upl-ease),box-shadow .35s var(--upl-ease),
             transform .12s var(--upl-ease),opacity .22s var(--upl-ease);
}
.upl-btn:hover{filter:brightness(.9)}
.upl-btn:active{transform:scale(.975)}
.upl-btn:focus-visible{outline:2px solid #171717;outline-offset:2px}
.upl.is-empty .upl-btn{opacity:.4;pointer-events:none}
.upl.is-uploading .upl-btn{
  top:0;right:0;bottom:0;width:100%;border-radius:var(--upl-r);
  cursor:default;box-shadow:none;transform:none;filter:none;
}

.upl-lbl{grid-area:1/1;display:flex;align-items:center;gap:10px;
         transition:opacity .2s var(--upl-ease),transform .26s var(--upl-ease)}
.upl-lbl.b{opacity:0;transform:translateY(7px)}
.upl.is-uploading .upl-lbl.a{opacity:0;transform:translateY(-7px)}
.upl.is-uploading .upl-lbl.b{opacity:1;transform:translateY(-2px)}
.upl-pct{font-variant-numeric:tabular-nums;font-weight:600;min-width:40px;
         text-align:right;color:rgba(255,255,255,.68)}

.upl-cancel{
  position:absolute;z-index:4;top:50%;right:14px;transform:translateY(-50%) scale(.85);
  width:26px;height:26px;border:0;border-radius:8px;padding:0;
  background:rgba(255,255,255,.14);color:#fff;cursor:pointer;
  display:grid;place-items:center;opacity:0;pointer-events:none;
  transition:opacity .25s var(--upl-ease) .15s,background .2s var(--upl-ease),
             transform .25s var(--upl-ease) .15s;
}
.upl-cancel svg{width:13px;height:13px;stroke:currentColor;stroke-width:2.2;stroke-linecap:round;fill:none}
.upl-cancel:hover{background:rgba(255,255,255,.28)}
.upl.is-uploading .upl-cancel{opacity:1;pointer-events:auto;transform:translateY(-50%) scale(1)}
.upl.no-cancel .upl-cancel{display:none}

/* ---- capa 3: la línea ----
   El track ocupa todo el componente y comparte su radio; el clip-path deja
   visible sólo la franja inferior. Así hereda la curva exacta del botón:
   un elemento de 5px con border-radius:16px hace que el navegador escale
   todos los radios y la línea sobresale por los lados. */
.upl-track{
  position:absolute;inset:0;z-index:3;
  border-radius:var(--upl-r);overflow:hidden;
  background:var(--upl-groove);
  clip-path:inset(calc(var(--upl-h) - var(--upl-line-h)) 0 0 0);
  opacity:0;pointer-events:none;
  transition:clip-path .58s var(--upl-spring),opacity .25s var(--upl-ease);
}
.upl.is-uploading .upl-track{opacity:1}

.upl-fill{position:absolute;inset:0;width:0%;background:var(--upl-deep);
          transition:background .35s var(--upl-ease)}
.upl.is-error .upl-fill{background:var(--upl-err)}

.upl.is-done .upl-track,
.upl.is-error .upl-track{clip-path:inset(0 0 0 0);opacity:1;background:transparent}
.upl.is-done .upl-fill,
.upl.is-error .upl-fill{width:100% !important}

/* ---- capa 4: estado final ----
   el clip-path del track recorta su propia sombra, así que vive acá */
.upl-final{
  position:absolute;inset:0;z-index:4;border-radius:var(--upl-r);
  display:flex;align-items:center;justify-content:center;gap:9px;
  color:rgba(255,255,255,.9);font-size:14px;font-weight:500;letter-spacing:-.012em;
  opacity:0;pointer-events:none;padding:0 16px;text-align:center;
  transition:opacity .28s var(--upl-ease) .2s,box-shadow .5s var(--upl-ease) .18s;
}
.upl.is-done .upl-final,
.upl.is-error .upl-final{
  opacity:1;pointer-events:auto;
  box-shadow:0 2px 5px rgba(10,13,20,.09),0 18px 36px -20px rgba(10,13,20,.5);
}
.upl.is-done .upl-final>*,
.upl.is-error .upl-final>*{animation:upl-rise .5s var(--upl-ease) .24s backwards}
@keyframes upl-rise{from{opacity:0;transform:translateY(6px)}}

.upl-check{width:17px;height:17px;flex:0 0 auto;overflow:visible}
.upl-check path{
  fill:none;stroke:currentColor;stroke-width:2.1;stroke-linecap:round;stroke-linejoin:round;
  /* el trazo mide ~21.5; 24 lo cubre sin que el patrón se repita */
  stroke-dasharray:24;stroke-dashoffset:24;
}
.upl.is-done .upl-check path{animation:upl-draw .42s cubic-bezier(.65,0,.35,1) .26s forwards}
@keyframes upl-draw{to{stroke-dashoffset:0}}

.upl-again{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:rgba(255,255,255,.13);border:0;color:rgba(255,255,255,.92);cursor:pointer;
  font-family:inherit;font-size:12px;font-weight:600;letter-spacing:-.01em;
  padding:7px 12px;border-radius:9px;transition:background .2s var(--upl-ease);
}
.upl-again:hover{background:rgba(255,255,255,.26)}

@media (prefers-reduced-motion:reduce){
  .upl *,.upl{animation-duration:.01ms !important;transition-duration:.01ms !important}
}
</style>

{{-- Va en el <head>, no al final del body: los scripts inline de los modales
     y las vistas corren antes de @stack('scripts'), así que si UploadMorph se
     definiera allí, esos scripts no lo encontrarían. --}}
<script>
window.UploadMorph = (function () {
    'use strict';

    var CLIP = '<svg viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>';
    var registry = new WeakMap();

    function fmtSize(bytes) {
        if (!bytes && bytes !== 0) return '';
        var mb = bytes / (1024 * 1024);
        if (mb < 1) return Math.max(1, Math.round(bytes / 1024)) + ' KB';
        return mb.toFixed(1) + ' MB';
    }

    function extOf(name) {
        var parts = String(name || '').split('.');
        if (parts.length < 2) return '';
        return parts.pop().slice(0, 4).toUpperCase();
    }

    function build(opts) {
        var el = document.createElement('div');
        el.className = 'upl is-empty' + (opts.cancelable === false ? ' no-cancel' : '');
        el.innerHTML =
            '<div class="upl-card">' +
                '<label class="upl-meta">' +
                    '<span class="upl-icon">' + CLIP + '</span>' +
                    '<span class="upl-txt">' +
                        '<span class="upl-name"></span>' +
                        '<span class="upl-sub"></span>' +
                    '</span>' +
                '</label>' +
                '<button class="upl-btn" type="button">' +
                    '<span class="upl-lbl a"></span>' +
                    '<span class="upl-lbl b"><span class="upl-lbl-t"></span><span class="upl-pct">0%</span></span>' +
                '</button>' +
            '</div>' +
            '<button class="upl-cancel" type="button" aria-label="Cancelar">' +
                '<svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>' +
            '</button>' +
            '<div class="upl-track"><div class="upl-fill"></div></div>' +
            '<div class="upl-final"></div>';
        return el;
    }

    function Morph(input, opts) {
        opts = opts || {};
        var self = this;

        this.input = input;
        this.opts = opts;
        this.el = build(opts);

        this.emptyName = opts.emptyName || 'Seleccionar archivo';
        this.emptySub  = opts.emptySub  || 'o arrástralo aquí';
        this.labelIdle = opts.label     || 'Subir';
        this.labelBusy = opts.busyLabel || 'Subiendo';

        this.$name   = this.el.querySelector('.upl-name');
        this.$sub    = this.el.querySelector('.upl-sub');
        this.$icon   = this.el.querySelector('.upl-icon');
        this.$btn    = this.el.querySelector('.upl-btn');
        this.$idle   = this.el.querySelector('.upl-lbl.a');
        this.$busy   = this.el.querySelector('.upl-lbl-t');
        this.$pct    = this.el.querySelector('.upl-pct');
        this.$fill   = this.el.querySelector('.upl-fill');
        this.$final  = this.el.querySelector('.upl-final');
        this.$cancel = this.el.querySelector('.upl-cancel');
        this.$meta   = this.el.querySelector('.upl-meta');
        this.$card   = this.el.querySelector('.upl-card');

        this.$idle.textContent = this.labelIdle;
        this.$busy.textContent = this.labelBusy;

        this.file = null;
        this.raf = null;
        this.target = 0;
        this.shown = 0;

        this.$meta.addEventListener('click', function (e) {
            e.preventDefault();
            if (!self.el.classList.contains('is-uploading')) input.click();
        });

        this.$btn.addEventListener('click', function () {
            if (self.el.classList.contains('is-uploading')) return;
            if (!self.file) { input.click(); return; }
            if (typeof opts.onSubmit === 'function') opts.onSubmit(self.file, self);
        });

        this.$cancel.addEventListener('click', function () {
            if (typeof opts.onCancel === 'function') opts.onCancel(self);
            self.reset();
        });

        input.addEventListener('change', function () {
            var f = input.files && input.files[0];
            if (!f) return;
            self.select(f);
            if (typeof opts.onSelect === 'function') opts.onSelect(f, self);
        });

        ['dragenter', 'dragover'].forEach(function (ev) {
            self.$card.addEventListener(ev, function (e) {
                e.preventDefault();
                if (!self.el.classList.contains('is-uploading')) self.el.classList.add('is-drag');
            });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            self.$card.addEventListener(ev, function (e) {
                e.preventDefault();
                self.el.classList.remove('is-drag');
            });
        });
        this.$card.addEventListener('drop', function (e) {
            var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (!f) return;
            try {
                var dt = new DataTransfer();
                dt.items.add(f);
                input.files = dt.files;          // deja el input consistente para submits normales
            } catch (err) { /* navegadores viejos: seguimos con el File suelto */ }
            self.select(f);
            if (typeof opts.onSelect === 'function') opts.onSelect(f, self);
        });

        this.clear();
    }

    /* El progreso real llega a saltos (chunks de 512 KB). En vez de asignar el
       ancho, lo perseguimos: cada frame recorre el 12% de lo que falta. Eso
       convierte los escalones en movimiento continuo. */
    Morph.prototype._loop = function () {
        var self = this;
        this.shown += (this.target - this.shown) * 0.12;
        if (Math.abs(this.target - this.shown) < 0.05) this.shown = this.target;
        this.$fill.style.width = this.shown + '%';
        this.$pct.textContent = Math.round(this.shown) + '%';
        if (this.file) {
            this.$sub.textContent = fmtSize(this.file.size * this.shown / 100) +
                                    ' / ' + fmtSize(this.file.size);
        }
        this.raf = requestAnimationFrame(function () { self._loop(); });
    };
    Morph.prototype._start = function () { if (!this.raf) this._loop(); };
    Morph.prototype._stop  = function () {
        if (this.raf) { cancelAnimationFrame(this.raf); this.raf = null; }
    };

    Morph.prototype.select = function (file) {
        this.file = file;
        this.$name.textContent = file.name;
        this.$sub.textContent = fmtSize(file.size);
        var ext = extOf(file.name);
        if (ext) { this.$icon.textContent = ext; } else { this.$icon.innerHTML = CLIP; }
        this.el.className = 'upl' + (this.opts.cancelable === false ? ' no-cancel' : '');
        return this;
    };

    Morph.prototype.start = function () {
        if (!this.file) return this;
        this.target = this.shown = 0;
        this.$fill.style.width = '0%';
        this.el.classList.remove('is-done', 'is-error', 'is-empty');
        this.el.classList.add('is-uploading');
        this._start();
        return this;
    };

    Morph.prototype.progress = function (p) {
        this.target = Math.max(0, Math.min(100, Number(p) || 0));
        return this;
    };

    Morph.prototype.done = function (text, again) {
        var self = this;
        this.target = 100;
        this.$final.innerHTML =
            '<svg class="upl-check" viewBox="0 0 24 24" aria-hidden="true">' +
                '<path d="M4.5 12.6 L9.6 17.4 L19.5 6.8"/>' +
            '</svg><span></span>';
        this.$final.querySelector('span').textContent = text || 'Listo';
        if (again) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'upl-again';
            b.textContent = again.label || 'Subir otro';
            b.addEventListener('click', again.onClick || function () { self.clear(); });
            this.$final.appendChild(b);
        }
        // pausa: sin ella el ojo no registra que la línea llegó al final
        setTimeout(function () {
            self._stop();
            self.$fill.style.width = '100%';
            self.$pct.textContent = '100%';
            self.el.classList.remove('is-uploading');
            self.el.classList.add('is-done');
        }, 340);
        return this;
    };

    Morph.prototype.fail = function (msg, onRetry) {
        var self = this;
        this.target = 100;
        this.$final.innerHTML = '<span></span>';
        this.$final.querySelector('span').textContent = msg || 'No se pudo subir el archivo';
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'upl-again';
        b.textContent = 'Reintentar';
        b.addEventListener('click', function () {
            self.reset();
            if (typeof onRetry === 'function') setTimeout(onRetry, 160);
        });
        this.$final.appendChild(b);
        setTimeout(function () {
            self._stop();
            self.$fill.style.width = '100%';
            self.el.classList.remove('is-uploading');
            self.el.classList.add('is-error');
        }, 240);
        return this;
    };

    /* vuelve al estado "con archivo elegido" */
    Morph.prototype.reset = function () {
        this._stop();
        this.target = this.shown = 0;
        this.$fill.style.width = '0%';
        this.$pct.textContent = '0%';
        this.el.className = 'upl' + (this.file ? '' : ' is-empty') +
                            (this.opts.cancelable === false ? ' no-cancel' : '');
        if (this.file) this.$sub.textContent = fmtSize(this.file.size);
        return this;
    };

    /* vuelve al estado vacío */
    Morph.prototype.clear = function () {
        this.file = null;
        try { this.input.value = ''; } catch (e) {}
        this.$name.textContent = this.emptyName;
        this.$sub.textContent = this.emptySub;
        this.$icon.innerHTML = CLIP;
        this.reset();
        return this;
    };

    return {
        /* Inserta el componente donde estaba `input` (o donde diga opts.into)
           y esconde el markup viejo que se le pase en opts.replace. */
        mount: function (input, opts) {
            if (!input) return null;
            if (registry.has(input)) return registry.get(input);
            opts = opts || {};

            var m = new Morph(input, opts);
            var anchor = opts.into || opts.replace || input;

            if (opts.replace) {
                opts.replace.style.display = 'none';
                opts.replace.parentNode.insertBefore(m.el, opts.replace);
            } else if (opts.into) {
                opts.into.appendChild(m.el);
            } else {
                input.parentNode.insertBefore(m.el, input);
            }
            if (input.parentNode !== m.el) m.el.appendChild(input);
            input.hidden = true;
            input.style.display = 'none';

            registry.set(input, m);
            return m;
        },

        of: function (input) { return registry.get(input) || null; },

        /* Sube por chunks de 512 KB (evita el 413 de nginx) dibujando el
           progreso real. Devuelve la respuesta JSON del último chunk. */
        chunked: function (m, file, url, extra, token) {
            var chunkSize = 512 * 1024;
            var total = Math.ceil(file.size / chunkSize) || 1;
            var uploadId = Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
            var last = {};

            m.start();

            return (async function () {
                for (var i = 0; i < total; i++) {
                    var fd = new FormData();
                    fd.append('chunk', file.slice(i * chunkSize, (i + 1) * chunkSize));
                    fd.append('upload_id', uploadId);
                    fd.append('index', i);
                    fd.append('total', total);
                    fd.append('name', file.name);
                    if (token) fd.append('_token', token);
                    Object.keys(extra || {}).forEach(function (k) { fd.append(k, extra[k]); });

                    var res = await fetch(url, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd,
                        credentials: 'same-origin'
                    });
                    if (res.status === 413) {
                        throw new Error('El servidor rechazó el envío por tamaño.');
                    }
                    var d = await res.json().catch(function () { return {}; });
                    if (!res.ok || d.success === false) {
                        throw new Error(d.message || 'No se pudo subir el archivo.');
                    }
                    last = d;
                    m.progress(((i + 1) / total) * 100);
                }
                return last;
            })();
        },

        /* Postea un <form> entero por XHR (no fetch: sólo XHR expone
           upload.onprogress, o sea progreso real por bytes en vez de por chunk).
           No toca el servidor: con X-Requested-With, Laravel ya responde 422
           JSON en vez de redirigir cuando falla la validación. */
        form: function (m, formEl, opts) {
            opts = opts || {};
            var fd = new FormData(formEl);
            var url = opts.url || formEl.getAttribute('action') || window.location.href;

            if (m) m.start();

            return new Promise(function (resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open(formEl.getAttribute('method') || 'POST', url, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json, text/html;q=0.9');
                xhr.withCredentials = true;

                xhr.upload.addEventListener('progress', function (e) {
                    if (!m || !e.lengthComputable) return;
                    m.progress((e.loaded / e.total) * 100);
                });

                xhr.addEventListener('load', function () {
                    if (xhr.status >= 200 && xhr.status < 400) {
                        var data = {};
                        try { data = JSON.parse(xhr.responseText); } catch (err) {}
                        resolve(data);
                        return;
                    }
                    var msg = 'No se pudo subir el archivo.';
                    if (xhr.status === 413) {
                        msg = 'El servidor rechazó el envío por tamaño.';
                    } else if (xhr.status === 422) {
                        try {
                            var d = JSON.parse(xhr.responseText);
                            var first = d.errors && Object.keys(d.errors)[0];
                            msg = (first && d.errors[first][0]) || d.message || msg;
                        } catch (err) {}
                    }
                    reject(new Error(msg));
                });

                xhr.addEventListener('error', function () {
                    reject(new Error('Fallo de conexión al subir el archivo.'));
                });
                xhr.addEventListener('abort', function () {
                    reject(new Error('Subida cancelada.'));
                });

                if (m) m.xhr = xhr;
                xhr.send(fd);
            });
        },

        /* Cableado declarativo para los formularios que antes recargaban la
           página. Basta con marcar el <form data-morph-form> y poner un
           <div data-morph-slot> junto al input de archivo:

               data-morph-label   texto del botón del pill (default "Cambiar")
               data-morph-empty   texto cuando no hay archivo
               data-morph-hint    subtítulo cuando no hay archivo
               data-morph-done    texto del estado completado
               data-morph-reload  "0" para no recargar al terminar

           Es idempotente: se puede volver a llamar tras inyectar HTML por AJAX. */
        autowire: function (root) {
            var API = window.UploadMorph;
            (root || document).querySelectorAll('form[data-morph-form]').forEach(function (form) {
                if (form.dataset.morphWired) return;

                var input = form.querySelector('input[type=file]');
                var slot  = form.querySelector('[data-morph-slot]');
                if (!input || !slot) return;
                form.dataset.morphWired = '1';

                var m = API.mount(input, {
                    into: slot,
                    cancelable: false,
                    label: form.dataset.morphLabel || 'Cambiar',
                    emptyName: form.dataset.morphEmpty || 'Seleccionar archivo',
                    emptySub: form.dataset.morphHint || 'o arrástralo aquí',
                    onSubmit: function () { input.click(); }
                });

                form.addEventListener('submit', function (ev) {
                    ev.preventDefault();
                    if (!input.files || !input.files.length) { input.click(); return; }

                    var btn = form.querySelector('[type=submit]');
                    if (btn) btn.disabled = true;

                    API.form(m, form)
                        .then(function () {
                            m.done(form.dataset.morphDone || 'Archivo subido');
                            if (form.dataset.morphReload !== '0') {
                                // deja correr la elevación antes de recargar
                                setTimeout(function () { window.location.reload(); }, 1200);
                            } else if (btn) {
                                btn.disabled = false;
                            }
                        })
                        .catch(function (e) {
                            if (btn) btn.disabled = false;
                            m.fail(e.message, function () { form.requestSubmit
                                ? form.requestSubmit()
                                : form.dispatchEvent(new Event('submit', {cancelable: true})); });
                        });
                });
            });
        }
    };
})();

/* auto-cableado inicial */
(function () {
    function go() { if (window.UploadMorph) UploadMorph.autowire(document); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', go);
    else go();
})();

/* Toma el verde real del token `brand` de Tailwind desde el probe. Como el CDN
   compila async, reintentamos hasta que la clase exista; si nunca aparece,
   queda el default de :root. */
(function () {
    function go() {
        var p = document.getElementById('upl-brand-probe');
        if (!p) return;
        var tries = 0;
        (function tick() {
            var c = getComputedStyle(p).backgroundColor;
            if (c && c !== 'rgba(0, 0, 0, 0)' && c !== 'transparent') {
                document.documentElement.style.setProperty('--upl-brand', c);
                return;
            }
            if (++tries < 25) setTimeout(tick, 100);
        })();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', go);
    else go();
})();
</script>
@endpush

@push('scripts')
{{-- El probe va en el HTML para que Tailwind CDN genere la clase .bg-brand y
     podamos leer el verde de marca ya resuelto. --}}
<span id="upl-brand-probe" class="bg-brand" aria-hidden="true"
      style="position:fixed;left:-9999px;top:0;width:1px;height:1px;pointer-events:none"></span>
@endpush
@endonce
