{{--
  Chatbot y formulario de leads de Vexora (Izan), grupo «Izan Chatbot Landmass».
  Los dos hablan con n8n.vexoraia.com, que está en el connect-src de la CSP.
  El formulario sólo sale con company.privacy_url: sin política su script no se
  pinta fuera de localhost y la sección quedaría vacía.
--}}
@php $vxPrivacidad = (string) config('company.privacy_url'); @endphp

@if ($vxPrivacidad !== '')
<section class="vx-leads" id="solicita-informacion">
  <div class="vx-leads-in">
    <div id="landmass-formulario"
         data-cliente-id="landmass_capital_bahia_mar"
         data-responsable="Landmass Capital"
         data-politica-url="{{ $vxPrivacidad }}"
         data-tipologias="Villa A,Villa B,Villa C,Villa D,Villa E"
         data-mercado="rd"
         data-proyecto="Bahía Mar"></div>
  </div>
</section>
<script src="/landmass-formulario.js" defer></script>
@endif

<script src="/chatbot-bahiamar.js" data-privacidad="{{ $vxPrivacidad }}"></script>

<style>
  /* Como la lista y el footer: por encima del hero fijo (lujo.css, 3c) y con
     el mismo papel de fondo; z 2 deja la barra Grid/List (z 3) encima. */
  .vx-leads { position: relative; z-index: 2; background: var(--lx-paper, #f0eee8); padding: 56px 16px 24px; }
  .vx-leads-in {
    max-width: 640px; margin: 0 auto; padding: 32px 28px;
    background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(7,69,64,.08);
    color: #1a1a1a;
    --lm-accent: #074540; --lm-accent-text: #fff;
  }
  @media (max-width: 480px) { .vx-leads-in { padding: 24px 18px; } }
  /* El lanzador del chat ocupa la esquina inferior derecha. Mientras no haya
     número, el botón de WhatsApp es un círculo apagado que no hace nada: fuera.
     Con número, sube encima del chat (en móvil, a la izquierda, porque encima
     está el botón del mapa). */
@if (!empty($waOff))
  .fg-wa-float { display: none !important; }
@else
  .fg-wa-float { bottom: calc(1.5rem + 68px) !important; }
  @media (max-width: 480px) { .fg-wa-float { bottom: 16px !important; right: auto !important; left: 16px !important; } }
@endif
</style>
