{{--
  Chatbot de Vexora (Izan), grupo «Izan Chatbot Landmass». Habla con
  n8n.vexoraia.com, que está en el connect-src de la CSP.
--}}
@php $vxPrivacidad = (string) config('company.privacy_url'); @endphp

{{-- El formulario de leads de Vexora se quitó a petición de Derick (26 oct); está en el historial de git (commit 6000327). --}}

<script src="/chatbot-bahiamar.js" data-privacidad="{{ $vxPrivacidad }}"></script>

<style>
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
