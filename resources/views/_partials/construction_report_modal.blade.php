{{-- ============================================================
     Modal "Ver reporte" — replica la línea gráfica del email
     emails/construction-progress.blade.php (mail-shell).

     Espera: $reports (colección de App\Models\ConstructionReport).
     Botón disparador: onclick="openReportModal({{ $r->id }})"
     ============================================================ --}}
@php $rc = config('company'); @endphp
<style>
    .rpt-overlay {
        position: fixed; inset: 0; z-index: 3000;
        display: none; align-items: flex-start; justify-content: center;
        padding: 28px 16px; overflow-y: auto;
        background: rgba(11, 28, 10, 0.55);
        animation: rptFade .16s ease-out;
        font-family: 'Inter', Helvetica, Arial, sans-serif;
    }
    .rpt-overlay.open { display: flex; }
    @keyframes rptFade { from { opacity: 0; } to { opacity: 1; } }

    .rpt-card {
        position: relative;
        width: 100%; max-width: 600px;
        background: #ffffff; border-radius: 14px; overflow: hidden;
        box-shadow: 0 40px 90px -25px rgba(11, 28, 10, .6);
        animation: rptIn .2s ease-out;
        margin: auto;
    }
    @keyframes rptIn {
        from { opacity: 0; transform: translateY(14px) scale(.985); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .rpt-close {
        position: absolute; top: 14px; right: 14px; z-index: 5;
        width: 30px; height: 30px; border-radius: 999px;
        border: 1px solid rgba(241,237,227,0.25); background: rgba(241,237,227,0.10);
        color: #F1EDE3; cursor: pointer; font-size: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: background .15s;
    }
    .rpt-close:hover { background: rgba(241,237,227,0.22); }

    /* Header */
    .rpt-head { background: #0b1c0a; padding: 20px 36px 18px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .rpt-brand { font-size: 14px; font-weight: 600; color: #F1EDE3; letter-spacing: .22em; text-transform: uppercase; line-height: 1; }
    .rpt-group { font-size: 8px; font-weight: 400; color: rgba(241,237,227,.35); letter-spacing: .14em; text-transform: uppercase; margin-top: 4px; }
    .rpt-doclabel { font-size: 8px; font-weight: 500; color: rgba(241,237,227,.3); letter-spacing: .2em; text-transform: uppercase; white-space: nowrap; }
    .rpt-goldbar { background: #B8962E; height: 2px; font-size: 0; line-height: 0; }

    /* Hero */
    .rpt-hero { background: #0f2710; padding: 26px 36px 22px; }
    .rpt-eyebrow { margin: 0 0 7px 0; font-size: 9px; font-weight: 500; color: rgba(241,237,227,.4); letter-spacing: .24em; text-transform: uppercase; }
    .rpt-title { margin: 0; font-size: 22px; font-weight: 300; color: #F1EDE3; letter-spacing: -.02em; line-height: 1.15; }
    .rpt-title strong { font-weight: 600; }

    /* Body */
    .rpt-body { background: #fff; padding: 28px 36px 8px; }
    .rpt-period { margin: 0 0 14px 0; font-size: 9px; font-weight: 600; color: #8a8a84; letter-spacing: .22em; text-transform: uppercase; }

    .rpt-progress-block { background: #0b1c0a; padding: 20px 18px; border-radius: 8px; }
    .rpt-progress-cap { margin: 0 0 4px 0; font-size: 9px; font-weight: 500; color: rgba(184,150,46,.75); letter-spacing: .14em; text-transform: uppercase; }
    .rpt-progress-num { margin: 0; font-size: 30px; font-weight: 600; color: #F1EDE3; letter-spacing: -.01em; }
    .rpt-progress-num span.pct { font-size: 18px; }
    .rpt-progress-num span.lbl { font-size: 11px; font-weight: 400; color: rgba(241,237,227,.5); }
    .rpt-bar { margin-top: 12px; background: rgba(241,237,227,.12); border-radius: 3px; height: 6px; overflow: hidden; }
    .rpt-bar > span { display: block; height: 6px; background: #B8962E; border-radius: 3px; }

    .rpt-desc { margin: 14px 0 0 0; font-size: 12px; color: #6a6a64; line-height: 1.65; }

    .rpt-meta { display: flex; margin-top: 14px; border-top: 1px solid #e8e7e3; border-bottom: 1px solid #e8e7e3; }
    .rpt-meta-cell { flex: 1 1 0; padding: 14px 16px; }
    .rpt-meta-cell + .rpt-meta-cell { border-left: 1px solid #e8e7e3; }
    .rpt-meta-cap { margin: 0 0 3px 0; font-size: 9px; font-weight: 500; color: #8a8a84; letter-spacing: .12em; text-transform: uppercase; }
    .rpt-meta-val { margin: 0; font-size: 14px; font-weight: 600; color: #1a1a18; }

    /* Phases */
    .rpt-section-cap { margin: 22px 0 12px; font-size: 9px; font-weight: 600; color: #8a8a84; letter-spacing: .18em; text-transform: uppercase; }
    .rpt-phase { margin-bottom: 12px; }
    .rpt-phase-top { display: flex; align-items: center; justify-content: space-between; font-size: 12px; margin-bottom: 5px; }
    .rpt-phase-name { display: flex; align-items: center; gap: 8px; color: #1a1a18; font-weight: 600; }
    .rpt-phase-dot { width: 8px; height: 8px; border-radius: 999px; flex-shrink: 0; }
    .rpt-phase-meta { color: #8a8a84; font-size: 11px; }
    .rpt-phase-meta b { color: #1a1a18; }
    .rpt-phase-bar { background: #eceae4; border-radius: 3px; height: 5px; overflow: hidden; }
    .rpt-phase-bar > span { display: block; height: 5px; border-radius: 3px; }

    /* Photos */
    .rpt-photos { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .rpt-photos a { display: block; aspect-ratio: 1/1; border-radius: 8px; overflow: hidden; background: #eceae4; border: 1px solid #e8e7e3; }
    .rpt-photos img { width: 100%; height: 100%; object-fit: cover; display: block; }

    /* Signature */
    .rpt-sign { background: #f7f6f3; padding: 18px 36px; border-top: 1px solid #e8e7e3; }
    .rpt-sign p { margin: 0; }
    .rpt-sign .a { font-size: 11px; color: #4a4a46; margin-bottom: 2px; }
    .rpt-sign .b { font-size: 12px; font-weight: 600; color: #1a1a18; margin-bottom: 2px; }
    .rpt-sign .c { font-size: 10px; color: #8a8a84; }

    @media (max-width: 520px) {
        .rpt-head, .rpt-hero, .rpt-body, .rpt-sign { padding-left: 22px; padding-right: 22px; }
        .rpt-photos { grid-template-columns: repeat(3, 1fr); }
        .rpt-title { font-size: 19px; }
    }
</style>

@foreach($reports as $r)
    @php
        $rProject = optional($r->project)->name ?? $rc['project'];
        $rPct     = max(2, min(100, (int) $r->overall_progress));
        $rPhases  = $r->phases ?: [];
        $rPhaseColors = ['done' => '#1fc16b', 'active' => '#fa7319', 'pending' => '#cacfd8'];
    @endphp
    <div class="rpt-overlay" id="rptModal-{{ $r->id }}" role="dialog" aria-modal="true" aria-label="Reporte de avance — {{ $r->period }}">
        <div class="rpt-card">
            <button type="button" class="rpt-close" onclick="closeReportModal({{ $r->id }})" aria-label="Cerrar"><i class="pi pi-times"></i></button>

            {{-- Header --}}
            <div class="rpt-head">
                <div>
                    <div class="rpt-brand">{{ $rc['brand'] }}</div>
                    <div class="rpt-group">{{ $rc['group'] }}</div>
                </div>
                <div class="rpt-doclabel">Avance · E-04</div>
            </div>
            <div class="rpt-goldbar">&nbsp;</div>

            {{-- Hero --}}
            <div class="rpt-hero">
                <p class="rpt-eyebrow">Avance de obra · Reporte mensual</p>
                <p class="rpt-title">Novedades de <strong>{{ $rProject }}</strong></p>
            </div>

            {{-- Body --}}
            <div class="rpt-body">
                <p class="rpt-period">{{ $r->period }} — {{ $r->title }}</p>

                <div class="rpt-progress-block">
                    <p class="rpt-progress-cap">Progreso actual de obra</p>
                    <p class="rpt-progress-num">{{ (int) $r->overall_progress }}<span class="pct">%</span> <span class="lbl">completado</span></p>
                    <div class="rpt-bar"><span style="width:{{ $rPct }}%"></span></div>
                </div>

                @if($r->description)
                    <p class="rpt-desc">{{ $r->description }}</p>
                @endif

                <div class="rpt-meta">
                    <div class="rpt-meta-cell">
                        <p class="rpt-meta-cap">Período</p>
                        <p class="rpt-meta-val">{{ $r->period }}</p>
                    </div>
                    <div class="rpt-meta-cell">
                        <p class="rpt-meta-cap">Entrega estimada</p>
                        <p class="rpt-meta-val">{{ $r->estimated_delivery ?: 'Q4 2026' }}</p>
                    </div>
                </div>

                @if(!empty($rPhases))
                    <p class="rpt-section-cap">Avance por etapa</p>
                    @foreach($rPhases as $ph)
                        @php
                            $phStatus = $ph['status'] ?? 'pending';
                            $phColor  = $rPhaseColors[$phStatus] ?? '#cacfd8';
                            $phPct    = (int) ($ph['pct'] ?? 0);
                        @endphp
                        <div class="rpt-phase">
                            <div class="rpt-phase-top">
                                <span class="rpt-phase-name"><span class="rpt-phase-dot" style="background:{{ $phColor }}"></span>{{ $ph['name'] ?? '—' }}</span>
                                <span class="rpt-phase-meta">{{ $ph['date'] ?? '—' }} · <b style="color:{{ $phColor }}">{{ $phPct }}%</b></span>
                            </div>
                            <div class="rpt-phase-bar"><span style="background:{{ $phColor }};width:{{ $phPct }}%"></span></div>
                        </div>
                    @endforeach
                @endif

                @if($r->photos)
                    <p class="rpt-section-cap">Galería del avance</p>
                    <div class="rpt-photos">
                        @foreach($r->photos as $photo)
                            <a href="{{ asset('storage/'.$photo) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('storage/'.$photo) }}" alt="Foto de avance">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Signature --}}
            <div class="rpt-sign">
                <p class="a">Un cordial saludo,</p>
                <p class="b">Equipo {{ $rc['group'] }}</p>
                <p class="c">{{ $rc['support_email'] }} · {{ $rc['phone'] }}</p>
            </div>
        </div>
    </div>
@endforeach

<script>
(function(){
    if (window.__reportModalInit) return;
    window.__reportModalInit = true;
    let prevOverflow = '';

    window.openReportModal = function(id){
        const el = document.getElementById('rptModal-' + id);
        if (!el) return;
        prevOverflow = document.body.style.overflow;
        el.classList.add('open');
        document.body.style.overflow = 'hidden';
    };
    window.closeReportModal = function(id){
        const el = id ? document.getElementById('rptModal-' + id)
                      : document.querySelector('.rpt-overlay.open');
        if (el) el.classList.remove('open');
        document.body.style.overflow = prevOverflow;
    };

    document.addEventListener('click', function(e){
        if (e.target.classList && e.target.classList.contains('rpt-overlay')) {
            window.closeReportModal();
        }
    });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && document.querySelector('.rpt-overlay.open')) {
            window.closeReportModal();
        }
    });
})();
</script>
