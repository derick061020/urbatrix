{{--
    ============================================================================
    Componente de descarga animado — "download morph"
    ============================================================================

    Hermano de partials/upload-morph: mismo lenguaje visual (línea al ras del
    borde inferior, pausa al llenarse, check dibujado por stroke-dashoffset),
    pero contando lo que baja.

    Los enlaces de descarga viven dentro de filas de tabla en 11 vistas, así que
    el componente no puede ocupar su lugar: aparece como tarjeta apilada abajo a
    la derecha, alimentada por un único handler delegado.

    Se engancha SOLO a los enlaces same-origin cuya ruta termina en "/download"
    (las tres rutas del proyecto: documents.download, broker.materials.download y
    broker.documents.download). Para excluir uno, marcalo data-no-morph.

    Un <a download> no reporta progreso, así que leemos el cuerpo con fetch +
    ReadableStream y comparamos contra Content-Length. Si algo no da (navegador
    viejo, sesión vencida, respuesta HTML), cae a navegación normal y la descarga
    ocurre igual, sin animación.
--}}

@once
@push('styles')
<style>
:root{
  --dl-brand:#5c7c68;                                          /* lo pisa el probe */
  --dl-deep:color-mix(in srgb, var(--dl-brand) 58%, #0d120f);
  --dl-line:color-mix(in srgb, var(--dl-brand) 62%, #ffffff);
  --dl-groove:rgba(255,255,255,.14);
  --dl-on:rgba(255,255,255,.9);
  --dl-err:#fb3748;
  --dl-r:16px;
  --dl-line-h:4px;
  --dl-ease:cubic-bezier(.22,1,.36,1);
  --dl-spring:cubic-bezier(.34,1.3,.5,1);
}

.dl-stack{
  position:fixed;right:20px;bottom:20px;z-index:80;
  display:flex;flex-direction:column-reverse;gap:10px;
  max-width:calc(100vw - 40px);pointer-events:none;
  font-family:'Inter',system-ui,sans-serif;-webkit-font-smoothing:antialiased;
}
.dl{
  position:relative;width:330px;max-width:100%;pointer-events:auto;
  background:var(--dl-deep);border-radius:var(--dl-r);overflow:hidden;
  box-shadow:0 2px 5px rgba(10,13,20,.12),0 20px 44px -22px rgba(10,13,20,.6);
  display:flex;align-items:center;gap:12px;padding:13px 14px 15px;
  transform:translateY(14px) scale(.97);opacity:0;
  transition:transform .5s var(--dl-spring),opacity .3s var(--dl-ease);
}
.dl.is-in{transform:translateY(0) scale(1);opacity:1}
.dl.is-out{transform:translateY(10px) scale(.97);opacity:0;
  transition:transform .34s var(--dl-ease),opacity .28s var(--dl-ease)}

.dl-ic{
  width:38px;height:38px;border-radius:11px;flex:0 0 auto;
  background:rgba(255,255,255,.1);color:var(--dl-on);
  display:grid;place-items:center;
  transition:background .3s var(--dl-ease);
}
.dl-ic svg{grid-area:1/1;width:19px;height:19px;stroke:currentColor;fill:none;
  stroke-width:2;stroke-linecap:round;stroke-linejoin:round;
  transition:opacity .28s var(--dl-ease),transform .38s var(--dl-spring)}

.dl-arrow{animation:dl-pulse 1.5s var(--dl-ease) infinite}
@keyframes dl-pulse{0%,100%{transform:translateY(-2px)}50%{transform:translateY(2px)}}
.dl.is-done .dl-arrow,
.dl.is-error .dl-arrow{opacity:0;transform:translateY(6px) scale(.8);animation:none}

.dl-check{opacity:0;transform:scale(.8)}
.dl.is-done .dl-check{opacity:1;transform:scale(1)}
.dl-check path{stroke-dasharray:24;stroke-dashoffset:24}
.dl.is-done .dl-check path{animation:dl-draw .42s cubic-bezier(.65,0,.35,1) .12s forwards}
@keyframes dl-draw{to{stroke-dashoffset:0}}
.dl.is-done .dl-ic{background:rgba(255,255,255,.16)}

.dl-tx{flex:1;min-width:0;display:flex;flex-direction:column;gap:2.5px}
.dl-nm{font-size:13.5px;font-weight:500;color:var(--dl-on);letter-spacing:-.012em;line-height:1.25;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dl-sb{font-size:11px;color:rgba(255,255,255,.55);font-variant-numeric:tabular-nums;
  line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

.dl-act{
  width:24px;height:24px;border:0;border-radius:7px;padding:0;flex:0 0 auto;
  background:transparent;color:rgba(255,255,255,.45);cursor:pointer;display:grid;place-items:center;
  transition:background .18s var(--dl-ease),color .18s var(--dl-ease);
}
.dl-act:hover{background:rgba(255,255,255,.14);color:var(--dl-on)}
.dl-act:focus-visible{outline:2px solid #fff;outline-offset:1px}
.dl-act svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round}

/* la línea, al ras del borde inferior — mismo lenguaje que la subida */
.dl-track{position:absolute;left:0;right:0;bottom:0;height:var(--dl-line-h);background:var(--dl-groove)}
.dl-fill{position:absolute;inset:0;width:0%;background:var(--dl-line);
  transition:background .3s var(--dl-ease)}
.dl.is-error .dl-fill{background:var(--dl-err)}

/* sin Content-Length no hay total contra qué medir: barra indeterminada
   en vez de inventar un porcentaje */
.dl.is-indeterminate .dl-fill{width:35% !important;animation:dl-slide 1.15s var(--dl-ease) infinite}
@keyframes dl-slide{0%{transform:translateX(-100%)}100%{transform:translateX(340%)}}

@media (max-width:560px){.dl{width:auto}}
@media (prefers-reduced-motion:reduce){
  .dl,.dl *{animation-duration:.01ms !important;transition-duration:.01ms !important}
}
</style>

{{-- En el <head>, igual que upload-morph: los scripts inline de las vistas
     corren antes de @stack('scripts'). --}}
<script>
window.DownloadMorph = (function () {
    'use strict';

    var stack = null;

    function ensureStack() {
        if (stack && document.body.contains(stack)) return stack;
        stack = document.createElement('div');
        stack.className = 'dl-stack';
        document.body.appendChild(stack);
        return stack;
    }

    function fmt(b) {
        if (!b && b !== 0) return '';
        if (b < 1024) return Math.round(b) + ' B';
        if (b < 1024 * 1024) return Math.round(b / 1024) + ' KB';
        return (b / (1024 * 1024)).toFixed(1) + ' MB';
    }

    /* Content-Disposition: prioriza filename*=UTF-8''… sobre filename="…" */
    function nameFromHeader(cd, fallback) {
        if (!cd) return fallback;
        var star = /filename\*\s*=\s*UTF-8''([^;]+)/i.exec(cd);
        if (star) {
            try { return decodeURIComponent(star[1].trim()); } catch (e) {}
        }
        var plain = /filename\s*=\s*"?([^";]+)"?/i.exec(cd);
        if (plain) return plain[1].trim();
        return fallback;
    }

    function card(name, total) {
        var el = document.createElement('div');
        el.className = 'dl' + (total ? '' : ' is-indeterminate');
        el.innerHTML =
            '<div class="dl-ic">' +
                '<svg class="dl-arrow" viewBox="0 0 24 24"><path d="M12 3v13m0 0l-5-5m5 5l5-5M4 21h16"/></svg>' +
                '<svg class="dl-check" viewBox="0 0 24 24"><path d="M4.5 12.6 L9.6 17.4 L19.5 6.8"/></svg>' +
            '</div>' +
            '<div class="dl-tx"><div class="dl-nm"></div><div class="dl-sb"></div></div>' +
            '<button class="dl-act dl-close" type="button" aria-label="Cerrar">' +
                '<svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>' +
            '</button>' +
            '<div class="dl-track"><div class="dl-fill"></div></div>';

        var $nm = el.querySelector('.dl-nm');
        var $sb = el.querySelector('.dl-sb');
        var $fill = el.querySelector('.dl-fill');

        $nm.textContent = name;
        $sb.textContent = total ? ('Iniciando… · ' + fmt(total)) : 'Descargando…';

        ensureStack().appendChild(el);
        requestAnimationFrame(function () { el.classList.add('is-in'); });

        var shown = 0, target = 0, raf = null, closed = false;

        /* mismo suavizado que la subida: perseguimos el objetivo en vez de
           asignarlo, para que los saltos del stream no se vean escalonados */
        function loop() {
            shown += (target - shown) * 0.14;
            if (Math.abs(target - shown) < 0.05) shown = target;
            $fill.style.width = shown + '%';
            if (total) {
                $sb.textContent = fmt(total * shown / 100) + ' / ' + fmt(total) +
                                  ' · ' + Math.round(shown) + '%';
            }
            raf = requestAnimationFrame(loop);
        }
        if (total) raf = requestAnimationFrame(loop);

        function stop() { if (raf) { cancelAnimationFrame(raf); raf = null; } }

        function dismiss() {
            if (closed) return;
            closed = true;
            stop();
            el.classList.add('is-out');
            setTimeout(function () { el.remove(); }, 360);
        }

        el.querySelector('.dl-close').addEventListener('click', dismiss);

        return {
            el: el,
            setName: function (n) { $nm.textContent = n; },
            setTotal: function (t) {
                total = t;
                if (t) { el.classList.remove('is-indeterminate'); if (!raf) raf = requestAnimationFrame(loop); }
            },
            progress: function (p) { target = Math.max(0, Math.min(100, p)); },
            done: function () {
                target = 100;
                el.classList.remove('is-indeterminate');
                // pausa: sin ella el ojo no registra que la línea llegó al final
                setTimeout(function () {
                    stop();
                    $fill.style.width = '100%';
                    el.classList.add('is-done');
                    $sb.textContent = total ? ('Descargado · ' + fmt(total)) : 'Descargado';
                    setTimeout(dismiss, 3400);
                }, 300);
            },
            fail: function (msg, retry) {
                stop();
                el.classList.remove('is-indeterminate');
                el.classList.add('is-error');
                $fill.style.width = '100%';
                $sb.textContent = msg || 'No se pudo descargar';
                if (retry) {
                    var b = document.createElement('button');
                    b.className = 'dl-act';
                    b.type = 'button';
                    b.title = 'Reintentar';
                    b.innerHTML = '<svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7M3 4v5h5"/></svg>';
                    b.addEventListener('click', function () { dismiss(); retry(); });
                    el.insertBefore(b, el.querySelector('.dl-close'));
                }
            },
            dismiss: dismiss
        };
    }

    function supported() {
        return typeof window.fetch === 'function' &&
               typeof window.ReadableStream === 'function' &&
               typeof window.Blob === 'function' &&
               typeof URL.createObjectURL === 'function';
    }

    function save(blob, filename) {
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename || 'descarga';
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(function () { URL.revokeObjectURL(url); }, 20000);
    }

    function start(url, fallbackName) {
        var guess = fallbackName ||
                    decodeURIComponent((url.split('?')[0].split('/').pop() || 'descarga'));
        var c = card(guess, 0);

        fetch(url, { credentials: 'same-origin' })
            .then(function (res) {
                if (!res.ok) throw new Error('El servidor respondió ' + res.status);

                // Sesión vencida: el redirect al login devuelve HTML 200. Guardar
                // eso como si fuera el documento sería peor que no animar nada.
                var type = res.headers.get('Content-Type') || '';
                if (type.indexOf('text/html') === 0) {
                    c.dismiss();
                    window.location.href = url;
                    return null;
                }

                var total = Number(res.headers.get('Content-Length')) || 0;
                var name = nameFromHeader(res.headers.get('Content-Disposition'), guess);
                c.setName(name);
                c.setTotal(total);

                if (!res.body || !res.body.getReader) {
                    return res.blob().then(function (b) { return { blob: b, name: name }; });
                }

                var reader = res.body.getReader();
                var chunks = [];
                var got = 0;

                return (function pump() {
                    return reader.read().then(function (r) {
                        if (r.done) {
                            return {
                                blob: new Blob(chunks, { type: type || 'application/octet-stream' }),
                                name: name
                            };
                        }
                        chunks.push(r.value);
                        got += r.value.length;
                        if (total) c.progress((got / total) * 100);
                        return pump();
                    });
                })();
            })
            .then(function (out) {
                if (!out) return;                       // ya se fue por navegación
                save(out.blob, out.name);
                c.done();
            })
            .catch(function (e) {
                c.fail(e.message || 'No se pudo descargar',
                       function () { start(url, fallbackName); });
            });
    }

    return {
        start: start,
        card: card,
        supported: supported,

        /* ¿este enlace es una descarga que debamos interceptar? */
        matches: function (a) {
            if (!a || a.hasAttribute('data-no-morph')) return false;
            if (a.target === '_blank') return false;
            var href = a.getAttribute('href') || '';
            if (!href || href.charAt(0) === '#' || /^(mailto|tel|javascript):/i.test(href)) return false;
            if (a.origin && a.origin !== window.location.origin) return false;
            return /\/download\/?$/.test(a.pathname || '');
        }
    };
})();

/* Un solo handler delegado sirve a los enlaces de las 11 vistas, incluidos los
   que se inyectan después por AJAX. */
document.addEventListener('click', function (ev) {
    if (ev.defaultPrevented || ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey) return;
    if (ev.button !== 0) return;

    var a = ev.target.closest ? ev.target.closest('a[href]') : null;
    if (!a || !window.DownloadMorph || !DownloadMorph.matches(a)) return;
    if (!DownloadMorph.__ok) return;                    // sin soporte, link normal

    ev.preventDefault();
    DownloadMorph.start(a.href, a.getAttribute('data-filename') || null);
});

/* Toma el verde real del token brand de Tailwind desde el probe de upload-morph
   (o el suyo propio si esta página no incluye el de subida). */
(function () {
    window.DownloadMorph.__ok = DownloadMorph.supported();

    function go() {
        var p = document.getElementById('upl-brand-probe') || document.getElementById('dl-brand-probe');
        if (!p) return;
        var tries = 0;
        (function tick() {
            var c = getComputedStyle(p).backgroundColor;
            if (c && c !== 'rgba(0, 0, 0, 0)' && c !== 'transparent') {
                document.documentElement.style.setProperty('--dl-brand', c);
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
{{-- Probe propio por si esta página no incluye upload-morph --}}
<span id="dl-brand-probe" class="bg-brand" aria-hidden="true"
      style="position:fixed;left:-9999px;top:0;width:1px;height:1px;pointer-events:none"></span>
@endpush
@endonce
