@php
    $bank = config('company.bank');
    // El banco beneficiario viene como "Nombre · Dirección"; separamos para mostrarlo en dos líneas.
    $benefParts = array_map('trim', explode('·', $bank['beneficiary_bank'] ?? '', 2));
    $benefName  = $benefParts[0] ?? '';
    $benefAddr  = $benefParts[1] ?? '';
@endphp
{{-- Panel "Datos bancarios" (tema claro) — usado dentro del modal Registrar pago --}}
<div style="width:268px;flex-shrink:0;border-left:1px solid #eaecf0;padding:20px;background:#fafbfc">
    <div style="font-size:12px;font-weight:700;color:#222530;margin-bottom:3px">Datos bancarios</div>
    <div style="font-size:10px;color:#717784;margin-bottom:16px;letter-spacing:.02em">Wire transfer · USD</div>

    <div style="font-size:8px;font-weight:700;color:#5c7c68;letter-spacing:.18em;text-transform:uppercase;margin-bottom:8px">Banco Intermediario</div>
    <div style="font-size:10.5px;font-weight:600;color:#222530;margin-bottom:8px">{{ $bank['intermediary_name'] }}</div>
    <div style="display:flex;justify-content:space-between;gap:8px;padding:5px 0;border-bottom:1px solid #eaecf0;font-size:9.5px"><span style="color:#717784">Cuenta</span><span style="color:#222530;font-weight:600;font-family:monospace">{{ $bank['intermediary_account'] }}</span></div>
    <div style="display:flex;justify-content:space-between;gap:8px;padding:5px 0;border-bottom:1px solid #eaecf0;font-size:9.5px"><span style="color:#717784">Swift (BIC)</span><span style="color:#222530;font-weight:600;font-family:monospace">{{ $bank['swift'] }}</span></div>
    <div style="display:flex;justify-content:space-between;gap:8px;padding:5px 0;border-bottom:1px solid #eaecf0;font-size:9.5px"><span style="color:#717784">ABA Routing</span><span style="color:#222530;font-weight:600;font-family:monospace">{{ $bank['aba'] }}</span></div>
    <div style="padding:5px 0;border-bottom:1px solid #eaecf0;font-size:9px;color:#717784">{{ $bank['intermediary_address'] }}</div>

    <div style="font-size:8px;font-weight:700;color:#5c7c68;letter-spacing:.18em;text-transform:uppercase;margin-top:16px;margin-bottom:8px">Banco Beneficiario</div>
    <div style="font-size:10px;font-weight:500;color:#222530;margin-bottom:4px">{{ $benefName }}</div>
    @if($benefAddr)
        <div style="font-size:9px;color:#717784;border-bottom:1px solid #eaecf0;padding-bottom:10px">{{ $benefAddr }}</div>
    @endif

    <div style="font-size:8px;font-weight:700;color:#5c7c68;letter-spacing:.18em;text-transform:uppercase;margin-top:16px;margin-bottom:8px">Cuenta a Acreditar</div>
    <div style="font-size:9.5px;font-weight:500;color:#222530;margin-bottom:5px;line-height:1.4">{{ $bank['account_holder'] }}</div>
    <div style="font-size:14px;font-weight:700;color:#171717;font-family:monospace;letter-spacing:.04em;padding:8px 0;border-top:1px solid #eaecf0;border-bottom:1px solid #eaecf0">{{ $bank['account_number'] }}</div>

    <div style="margin-top:16px;padding:10px 12px;background:#fff3eb;border:1px solid #fbd3b4;border-radius:6px;font-size:9px;color:#b45309;line-height:1.55">
        <span style="font-weight:700">Referencia obligatoria:</span> Incluir nombre del cliente y número de unidad en el campo de referencia para evitar la devolución de fondos.
    </div>
</div>
