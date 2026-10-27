{{--
  Informe de perfil de riesgo (Risk Rating Profile) de una unidad, para el
  archivo de cumplimiento. Inicial mientras no hay escritura; final cuando ya
  la hay. Se imprime a PDF desde el navegador.
--}}
@php
    $F     = \App\Support\CddForm::class;
    $form  = (array) ($cdd->kyc_form ?? []);
    $docs  = (array) ($cdd->kyc_docs ?? []);
    $type  = $cdd->buyer_type === 'legal' ? 'legal' : 'natural';
    $who   = fn ($id) => $id ? (\App\Models\User::find($id)?->name ?? '—') : '—';
    $f     = fn ($d) => $d ? $d->translatedFormat('j M Y') : '—';
    $final = (bool) $cdd->deed_date;
    $buyers = $type === 'legal'
        ? [($form['company']['legal_name'] ?? '—')]
        : array_filter([$form['buyer1']['full_name'] ?? null, ! empty($form['has_buyer2']) ? ($form['buyer2']['full_name'] ?? null) : null]);
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('Informe de perfil de riesgo') }} · {{ $reservation->unit?->custom_id ?? $reservation->unit_name }}</title>
<style>
    @page { size: A4; margin: 16mm; }
    * { box-sizing: border-box; }
    body { font-family: Inter, Helvetica, Arial, sans-serif; color: #1a1a18; font-size: 12px; margin: 0; padding: 24px; background: #fff; }
    .sheet { max-width: 780px; margin: 0 auto; }
    h1 { font-size: 20px; margin: 0 0 4px; }
    .muted { color: #6a6a64; }
    .band { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #2b2b1a; padding-bottom: 10px; margin-bottom: 16px; gap: 12px; flex-wrap: wrap; }
    .tag { display: inline-block; padding: 3px 10px; border-radius: 99px; font-weight: 700; font-size: 11px; background: #f1efe7; }
    .tag.alto { background: #ffebec; color: #b3261e; } .tag.medio { background: #fff3eb; color: #a75100; } .tag.bajo { background: #e8f7ee; color: #18794e; }
    h2 { font-size: 12px; text-transform: uppercase; letter-spacing: .12em; color: #6a6a64; margin: 20px 0 8px; }
    table { width: 100%; border-collapse: collapse; }
    td { padding: 6px 8px; border-bottom: 1px solid #eceae3; vertical-align: top; }
    td:first-child { color: #6a6a64; width: 42%; }
    .btn { position: fixed; top: 14px; right: 14px; background: #2b2b1a; color: #fff; border: 0; border-radius: 8px; padding: 9px 16px; font-weight: 600; cursor: pointer; }
    @media (max-width: 640px) { .btn { position: static; display: block; margin: 0 0 14px auto; } }
    @media print { .btn { display: none; } body { padding: 0; } }
</style>
</head>
<body>
<button class="btn" onclick="window.print()">{{ __('Imprimir / PDF') }}</button>
<div class="sheet">
    <div class="band">
        <div>
            <div class="muted">{{ config('company.group') }} · {{ __('Cumplimiento') }}</div>
            <h1>{{ $final ? __('Informe final de perfil de riesgo') : __('Informe de perfil de riesgo') }}</h1>
            <div class="muted">{{ __('Unidad :u · expediente :c', ['u' => $reservation->unit?->custom_id ?? $reservation->unit_name ?? '—', 'c' => $reservation->reservation_code ?? $reservation->id]) }}</div>
        </div>
        <div>@if($cdd->risk_rating)<span class="tag {{ $cdd->risk_rating }}">{{ __('Riesgo :r', ['r' => $cdd->risk_rating]) }}</span>@endif</div>
    </div>

    <h2>{{ __('Comprador') }}</h2>
    <table>
        <tr><td>{{ __('Tipo') }}</td><td>{{ $type === 'legal' ? __('Persona jurídica') : __('Persona física') }}</td></tr>
        <tr><td>{{ __('Nombre') }}</td><td>{{ implode(' · ', $buyers) ?: trim($reservation->first_name.' '.$reservation->last_name) }}</td></tr>
        @if($type === 'legal')
            <tr><td>{{ __('Beneficiarios finales') }}</td><td>{{ collect((array) ($form['ubo'] ?? []))->filter(fn ($u) => filled($u['name'] ?? null))->map(fn ($u) => $u['name'].' ('.($u['econ'] ?? '—').' %)')->implode(' · ') ?: '—' }}</td></tr>
        @else
            <tr><td>{{ __('Fuente de ingresos') }}</td><td>{{ __($F::INCOME[$form['buyer1']['income_type'] ?? ''] ?? '—') }}</td></tr>
            <tr><td>{{ __('Método de pago') }}</td><td>{{ collect((array) ($form['payment']['methods'] ?? []))->map(fn ($m) => __($F::PAYMENT[$m] ?? $m))->implode(', ') ?: '—' }}</td></tr>
        @endif
        <tr><td>{{ __('Precio de compra') }}</td><td>${{ number_format((float) ($reservation->unit?->price ?? $reservation->unit_price ?? 0)) }} · ƒ {{ number_format($cdd->purchaseAwg()) }}</td></tr>
        <tr><td>{{ __('Documentos entregados') }}</td><td>{{ count(array_filter(array_intersect_key($docs, $F::docs($type)))) }} / {{ count($F::docs($type)) }}</td></tr>
    </table>

    <h2>{{ __('Evaluación') }}</h2>
    <table>
        <tr><td>{{ __('Screening (sanciones, PEP, medios adversos)') }}</td><td>{{ $cdd->screening_status }} · {{ $who($cdd->screened_by) }} · {{ $f($cdd->screened_at) }}<br><span class="muted">{{ $cdd->screening_notes }}</span></td></tr>
        <tr><td>{{ __('Depósito a nombre del comprador') }}</td><td>{{ $cdd->payment_checked_at ? ($cdd->payment_name_ok ? __('Sí') : __('No: en investigación')).' · '.$f($cdd->payment_checked_at) : '—' }}@if($cdd->payment_notes)<br><span class="muted">{{ $cdd->payment_notes }}</span>@endif</td></tr>
        <tr><td>{{ __('Perfil de riesgo') }}</td><td>{{ $cdd->risk_rating ?? '—' }}<br><span class="muted">{{ $cdd->risk_notes }}</span></td></tr>
        <tr><td>{{ __('Consejo de cumplimiento') }}</td><td>{{ $cdd->cdd_decision }} · {{ $who($cdd->cdd_decided_by) }} · {{ $f($cdd->cdd_decided_at) }}<br><span class="muted">{{ $cdd->cdd_notes }}</span></td></tr>
        <tr><td>{{ __('Director de ventas') }}</td><td>{{ $cdd->manager_decision }} · {{ $who($cdd->manager_decided_by) }} · {{ $f($cdd->manager_decided_at) }}@if($cdd->deviation_note)<br><span class="muted">{{ __('Desviación motivada:') }} {{ $cdd->deviation_note }}</span>@endif</td></tr>
    </table>

    <h2>{{ __('Operación') }}</h2>
    <table>
        <tr><td>{{ __('Contrato de compraventa firmado') }}</td><td>{{ $f($cdd->spa_signed_at) }}</td></tr>
        <tr><td>{{ __('Informe a la FIU') }}</td><td>{{ $cdd->fiuRequired() ? ($cdd->fiu_reported_at ? $f($cdd->fiu_reported_at).' '.$cdd->fiu_reference : __('Pendiente (vence el :f)', ['f' => $f($cdd->fiuDueDate())])) : __('No requerido') }}</td></tr>
        <tr><td>{{ __('Revisión antes de la escritura') }}</td><td>{{ $f($cdd->predeed_reviewed_at) }} · {{ $who($cdd->predeed_reviewed_by) }}<br><span class="muted">{{ $cdd->predeed_notes }}</span></td></tr>
        <tr><td>{{ __('Escritura') }}</td><td>{{ $f($cdd->deed_date) }}</td></tr>
        <tr><td>{{ __('Conservar hasta') }}</td><td>{{ $f($cdd->retentionUntil()) }}</td></tr>
    </table>
    <p class="muted" style="margin-top:22px">{{ __('Generado el :f por :n.', ['f' => now()->translatedFormat('j M Y, H:i'), 'n' => auth()->user()?->name]) }}</p>
</div>
</body>
</html>
