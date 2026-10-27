{{--
  Pestaña «Cumplimiento» del expediente: el flujo CDD de Siebe Group, fase a
  fase. Cada formulario manda una «action» a CddController::update, que
  comprueba el orden (no se puede aprobar la venta sin KYC revisado, etc.).
--}}
@php
    $cdd   = $reservation->cdd ?? $reservation->cdd()->create([]);
    $F     = \App\Support\CddForm::class;
    $form  = (array) ($cdd->kyc_form ?? []);
    $docs  = (array) ($cdd->kyc_docs ?? []);
    $type  = $cdd->buyer_type === 'legal' ? 'legal' : 'natural';
    $phase = $cdd->phase();
    $who   = fn ($id) => $id ? (\App\Models\User::find($id)?->name ?? '—') : null;
    $fecha = fn ($d) => $d ? $d->translatedFormat('j M Y') : null;
    $awg   = $cdd->purchaseAwg();
    $steps = [
        1 => __('Screening'), 2 => __('KYC y riesgo'), 3 => __('Director de ventas'),
        4 => __('Contrato'), 5 => __('FIU'), 6 => __('Revisión previa'), 7 => __('Escritura'),
    ];
    $kycLabel = [
        'no_solicitado' => [__('Sin pedir'), 'bg-ink-100 text-ink-600'],
        'solicitado'    => [__('Pedido al comprador'), 'bg-info-soft text-info'],
        'enviado'       => [__('Enviado, por revisar'), 'bg-warn-soft text-warn'],
        'info_adicional'=> [__('Info adicional pedida'), 'bg-warn-soft text-warn'],
        'revisado'      => [__('Revisado'), 'bg-ok-soft text-ok-dark'],
    ][$cdd->kyc_status] ?? [$cdd->kyc_status, 'bg-ink-100 text-ink-600'];
    $riskTone = ['bajo' => 'bg-ok-soft text-ok-dark', 'medio' => 'bg-warn-soft text-warn', 'alto' => 'bg-err-soft text-err'];
    $show = function ($v) use ($F) { return is_array($v) ? implode(', ', $v) : (filled($v) ? $v : '—'); };
@endphp

<div class="space-y-4">

    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-err-soft text-err text-[12px]">{{ $errors->first() }}</div>
    @endif

    {{-- Estado general --}}
    <div class="crm-card p-5">
        <div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-4">
            <div class="flex-1">
                <div class="text-[15px] font-semibold text-ink-900">{{ __('Diligencia debida (CDD)') }}</div>
                <div class="text-[12px] text-ink-500">{{ __('Flujo de Siebe Group: identificación, screening, KYC, perfil de riesgo, aprobaciones, FIU y escritura. Todo queda registrado con fecha y persona.') }}</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="crm-pill {{ $kycLabel[1] }}">KYC: {{ $kycLabel[0] }}</span>
                @if($cdd->risk_rating)<span class="crm-pill {{ $riskTone[$cdd->risk_rating] }}">{{ __('Riesgo :r', ['r' => $cdd->risk_rating]) }}</span>@endif
                @if($cdd->fiuRequired())<span class="crm-pill bg-err-soft text-err">{{ __('Requiere informe FIU') }}</span>@endif
                <a href="{{ route('admin.crm.cdd.report', $reservation->id) }}" target="_blank" class="crm-btn crm-btn-ghost !py-1 !px-3 !text-[11.5px]"><i class="pi pi-print"></i> {{ __('Informe de riesgo (RRP)') }}</a>
            </div>
        </div>
        <div class="grid grid-cols-7 gap-1.5">
            @foreach($steps as $n => $label)
                <div class="text-center">
                    <div class="h-1.5 rounded-full {{ $n < $phase ? 'bg-ok' : ($n === $phase ? 'bg-brand' : 'bg-ink-100') }}"></div>
                    <div class="text-[10px] mt-1.5 leading-tight {{ $n === $phase ? 'text-ink-900 font-semibold' : 'text-ink-500' }} hidden sm:block">{{ $label }}</div>
                </div>
            @endforeach
        </div>
        <div class="sm:hidden text-[11px] text-ink-600 mt-2">{{ $phase <= 7 ? __('Fase :n de 7 · :l', ['n' => $phase, 'l' => $steps[$phase]]) : __('Expediente CDD completo') }}</div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 text-[12px]">
            <div class="border border-ink-200 rounded-lg px-3 py-2"><div class="text-[10px] uppercase tracking-wide text-ink-400 font-semibold">{{ __('Precio en AWG') }}</div><div class="font-semibold text-ink-900">ƒ {{ number_format($awg) }}</div></div>
            <div class="border border-ink-200 rounded-lg px-3 py-2"><div class="text-[10px] uppercase tracking-wide text-ink-400 font-semibold">{{ __('Umbral FIU') }}</div><div class="font-semibold text-ink-900">ƒ {{ number_format(config('company.cdd.fiu_threshold_awg')) }}</div></div>
            <div class="border border-ink-200 rounded-lg px-3 py-2"><div class="text-[10px] uppercase tracking-wide text-ink-400 font-semibold">{{ __('Archivo hasta') }}</div><div class="font-semibold text-ink-900">{{ $fecha($cdd->retentionUntil()) ?? __('10 años desde la escritura') }}</div></div>
        </div>
    </div>

    {{-- 1 · Screening --}}
    <div class="crm-card p-5">
        <div class="flex items-center gap-2 mb-1"><span class="crm-pill bg-ink-100 text-ink-700">1</span><div class="text-[14px] font-semibold text-ink-900">{{ __('Screening de sanciones, PEP y medios adversos') }}</div></div>
        <p class="text-[12px] text-ink-500 mb-3">{{ __('Antes de firmar la reserva o aceptar el depósito. Se verifica la identidad del comprador, de sus representantes y, si es una sociedad, de los beneficiarios finales.') }}</p>
        @php $idsReserva = (array) ($docs['reserva_id'] ?? []); @endphp
        <div class="flex flex-wrap items-center gap-2 mb-3 text-[12px] text-ink-700">
            <span>{{ __('Identificación de la reserva:') }} {{ trim($reservation->first_name.' '.$reservation->last_name) }} · {{ $reservation->nationality ?: $reservation->country ?: '—' }}</span>
            @forelse($idsReserva as $i => $p)
                <a href="{{ route('admin.crm.cdd.doc', [$reservation->id, 'reserva_id', $i]) }}" class="crm-btn crm-btn-ghost !py-0.5 !px-2 !text-[11px]"><i class="pi pi-id-card"></i> {{ __('Documento :n', ['n' => $i + 1]) }}</a>
            @empty
                <span class="text-ink-400">· {{ $reservation->id_document_path ? __('documento en la pestaña Documentos') : __('sin documento de identidad todavía') }}</span>
            @endforelse
        </div>
        @if($cdd->screening_status === 'pendiente')
            <form method="POST" action="{{ route('admin.crm.cdd.update', $reservation->id) }}" class="space-y-2 m-0">@csrf
                <input type="hidden" name="action" value="screening">
                <textarea name="screening_notes" rows="2" class="crm-input pl-3 py-2" placeholder="{{ __('Listas consultadas, fecha y resultado') }}"></textarea>
                <div class="flex flex-wrap gap-2">
                    <button name="screening_status" value="limpio" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> {{ __('Sin coincidencias') }}</button>
                    <button name="screening_status" value="coincidencia" class="crm-btn crm-btn-ghost text-err" onclick="return confirm({{ Js::from(__('¿Confirmas una coincidencia real? La venta no podrá seguir y hay que informar a la CBA y a la FIU.')) }})">{{ __('Coincidencia real') }}</button>
                </div>
            </form>
        @else
            <div class="text-[12.5px] {{ $cdd->screening_status === 'limpio' ? 'text-ok-dark' : 'text-err' }} font-semibold">
                {{ $cdd->screening_status === 'limpio' ? __('Sin coincidencias') : __('Coincidencia real: la venta no puede seguir. Informar a la CBA y a la FIU.') }}
            </div>
            <div class="text-[11px] text-ink-500 mt-1">{{ $who($cdd->screened_by) }} · {{ $fecha($cdd->screened_at) }} @if($cdd->screening_notes) · {{ $cdd->screening_notes }} @endif</div>
        @endif
    </div>

    {{-- 2 · KYC y riesgo --}}
    <div class="crm-card p-5 {{ $cdd->screeningCleared() ? '' : 'opacity-60' }}">
        <div class="flex items-center gap-2 mb-1"><span class="crm-pill bg-ink-100 text-ink-700">2</span><div class="text-[14px] font-semibold text-ink-900">{{ __('Formulario KYC, revisión y perfil de riesgo') }}</div></div>
        <p class="text-[12px] text-ink-500 mb-3">{{ __('El comprador rellena el formulario en su portal. Cumplimiento lo revisa, pide lo que falte y prepara el perfil de riesgo de la unidad.') }}</p>

        @if($cdd->screeningCleared() && in_array($cdd->kyc_status, ['no_solicitado'], true))
            <form method="POST" action="{{ route('admin.crm.cdd.update', $reservation->id) }}" class="flex flex-col sm:flex-row gap-2 m-0">@csrf
                <input type="hidden" name="action" value="request_kyc">
                <select name="buyer_type" class="crm-input pl-3 sm:w-64" required>
                    <option value="natural">{{ __('Persona física') }}</option>
                    <option value="legal">{{ __('Persona jurídica (sociedad)') }}</option>
                </select>
                <button class="crm-btn crm-btn-primary"><i class="pi pi-send"></i> {{ __('Pedir el formulario al comprador') }}</button>
            </form>
        @elseif(in_array($cdd->kyc_status, ['solicitado', 'info_adicional'], true))
            <div class="text-[12.5px] text-ink-700">{{ __('Esperando al comprador desde el :f.', ['f' => $fecha($cdd->kyc_requested_at)]) }} {{ $type === 'legal' ? __('Persona jurídica') : __('Persona física') }}.</div>
            @if($cdd->info_request)<div class="text-[12px] text-warn mt-1">{{ __('Pedido:') }} {{ $cdd->info_request }}</div>@endif
        @endif

        @if(in_array($cdd->kyc_status, ['enviado', 'revisado', 'info_adicional'], true) && $form)
            <details class="mt-3 border border-ink-200 rounded-lg" {{ $cdd->kyc_status === 'enviado' ? 'open' : '' }}>
                <summary class="px-4 py-2.5 text-[12.5px] font-semibold text-ink-800 cursor-pointer">{{ __('Formulario enviado el :f', ['f' => $fecha($cdd->kyc_submitted_at)]) }}</summary>
                <div class="px-4 pb-4 space-y-4 text-[12px]">
                    @if($type === 'natural')
                        @foreach([1, 2] as $n)
                            @continue($n === 2 && empty($form['has_buyer2']))
                            @php $b = (array) ($form['buyer'.$n] ?? []); @endphp
                            <div>
                                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400 mb-1">{{ __('Comprador :n', ['n' => $n]) }}</div>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                                    @foreach($F::buyerFields() as $f)
                                        <div class="flex justify-between gap-3 py-1 border-b border-ink-100"><dt class="text-ink-500">{{ __($f[1]) }}</dt><dd class="text-ink-900 text-right break-words">{{ isset($f[4]) ? __($f[4][$b[$f[0]] ?? ''] ?? '—') : $show($b[$f[0]] ?? null) }}</dd></div>
                                    @endforeach
                                    @php $it = $b['income_type'] ?? null; @endphp
                                    <div class="flex justify-between gap-3 py-1 border-b border-ink-100"><dt class="text-ink-500">{{ __('Fuente de ingresos') }}</dt><dd class="text-ink-900 text-right">{{ __($F::INCOME[$it] ?? '—') }}</dd></div>
                                    @foreach(($F::incomeFields()[$it] ?? []) as [$fk, $fl])
                                        <div class="flex justify-between gap-3 py-1 border-b border-ink-100"><dt class="text-ink-500">{{ __($fl) }}</dt><dd class="text-ink-900 text-right break-words">{{ $show($b['income'][$it][$fk] ?? null) }}</dd></div>
                                    @endforeach
                                </dl>
                            </div>
                        @endforeach
                        <div>
                            <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400 mb-1">{{ __('Información de pago') }}</div>
                            @foreach((array) ($form['payment']['methods'] ?? []) as $m)
                                <div class="py-1 border-b border-ink-100"><span class="font-semibold text-ink-800">{{ __($F::PAYMENT[$m] ?? $m) }}:</span>
                                    @foreach(($F::paymentFields()[$m] ?? []) as [$fk, $fl]) <span class="text-ink-600">{{ __($fl) }}: {{ $show($form['payment']['detail'][$m][$fk] ?? null) }}.</span> @endforeach
                                </div>
                            @endforeach
                        </div>
                    @else
                        @php $c = (array) ($form['company'] ?? []); @endphp
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                            @foreach($F::companyFields() as $f)
                                <div class="flex justify-between gap-3 py-1 border-b border-ink-100"><dt class="text-ink-500">{{ __($f[1]) }}</dt><dd class="text-ink-900 text-right break-words">{{ isset($f[4]) ? __($f[4][$c[$f[0]] ?? ''] ?? '—') : $show($c[$f[0]] ?? null) }}</dd></div>
                            @endforeach
                        </dl>
                        @foreach((array) ($form['ubo'] ?? []) as $i => $u)
                            @continue(empty(array_filter((array) $u)))
                            <div class="py-1 border-b border-ink-100"><span class="font-semibold text-ink-800">{{ __('Beneficiario final :n', ['n' => $i + 1]) }}:</span> {{ $u['name'] ?? '' }} · {{ $u['dob'] ?? '' }} · {{ $u['address'] ?? '' }} · {{ __('propiedad') }} {{ $u['econ'] ?? '—' }} % · {{ __('control') }} {{ $u['control'] ?? '—' }} %</div>
                        @endforeach
                        @foreach((array) ($form['entities'] ?? []) as $e)
                            @continue(empty(array_filter((array) $e)))
                            <div class="py-1 border-b border-ink-100"><span class="font-semibold text-ink-800">{{ __('Entidad') }}:</span> {{ $e['name'] ?? '' }} · {{ $e['type'] ?? '' }} · {{ $e['country'] ?? '' }} · {{ $e['econ'] ?? '—' }} % / {{ $e['control'] ?? '—' }} %</div>
                        @endforeach
                        <div class="py-1 border-b border-ink-100"><span class="font-semibold text-ink-800">{{ __('Motivos') }}:</span> {{ collect((array) ($form['reasons'] ?? []))->map(fn ($k) => __($F::REASONS[$k] ?? $k))->implode(', ') }} {{ $form['reasons_other'] ?? '' }}</div>
                        <div class="py-1 border-b border-ink-100"><span class="font-semibold text-ink-800">{{ __('Uso') }}:</span> {{ __($F::PURPOSE[$form['purpose'] ?? ''] ?? '—') }} {{ $form['purpose_other'] ?? '' }} {{ $form['description'] ?? '' }}</div>
                    @endif
                    <div>
                        <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400 mb-1">{{ __('Documentos') }}</div>
                        @foreach($F::docs($type) as $key => [$label, $req])
                            <div class="py-1.5 border-b border-ink-100 flex flex-wrap items-center gap-2">
                                <i class="pi {{ !empty($docs[$key]) ? 'pi-check-circle text-ok' : 'pi-circle text-ink-300' }}"></i>
                                <span class="flex-1 min-w-[200px] text-ink-800">{{ __($label) }}</span>
                                @foreach((array) ($docs[$key] ?? []) as $i => $p)
                                    <a href="{{ route('admin.crm.cdd.doc', [$reservation->id, $key, $i]) }}" class="crm-btn crm-btn-ghost !py-0.5 !px-2 !text-[11px]"><i class="pi pi-download"></i> {{ $i + 1 }}</a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <div class="text-[11px] text-ink-500">{{ __('Firmado por :n', ['n' => $form['decl']['name'] ?? '—']) }}</div>
                </div>
            </details>
        @endif

        @if(in_array($cdd->kyc_status, ['enviado', 'revisado'], true))
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mt-3">
                <form method="POST" action="{{ route('admin.crm.cdd.update', $reservation->id) }}" class="border border-ink-200 rounded-lg p-3 space-y-2 m-0">@csrf
                    <input type="hidden" name="action" value="request_info">
                    <div class="text-[12px] font-semibold text-ink-800">{{ __('Pedir información adicional') }}</div>
                    <textarea name="info_request" rows="2" required class="crm-input pl-3 py-2" placeholder="{{ __('Qué falta o qué hay que aclarar (lo verá el comprador)') }}"></textarea>
                    <button class="crm-btn crm-btn-ghost">{{ __('Enviar al comprador') }}</button>
                </form>
                <form method="POST" action="{{ route('admin.crm.cdd.update', $reservation->id) }}" class="border border-ink-200 rounded-lg p-3 space-y-2 m-0">@csrf
                    <input type="hidden" name="action" value="review">
                    <div class="text-[12px] font-semibold text-ink-800">{{ __('Perfil de riesgo y decisión de cumplimiento') }}</div>
                    <select name="risk_rating" class="crm-input pl-3" required>
                        @foreach(['bajo' => __('Riesgo bajo'), 'medio' => __('Riesgo medio'), 'alto' => __('Riesgo alto')] as $k => $l)<option value="{{ $k }}" @selected($cdd->risk_rating === $k)>{{ $l }}</option>@endforeach
                    </select>
                    <textarea name="risk_notes" rows="2" class="crm-input pl-3 py-2" placeholder="{{ __('Circunstancias del comprador, UBO, origen de fondos y resultado del screening') }}">{{ $cdd->risk_notes }}</textarea>
                    <input name="cdd_notes" class="crm-input pl-3" placeholder="{{ __('Nota de la decisión (opcional)') }}" value="{{ $cdd->cdd_notes }}">
                    <div class="flex flex-wrap gap-2">
                        <button name="cdd_decision" value="aprobado" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> {{ __('Aprobar comprador') }}</button>
                        <button name="cdd_decision" value="rechazado" class="crm-btn crm-btn-ghost text-err">{{ __('Rechazar') }}</button>
                    </div>
                    <div class="text-[11px] text-ink-500">{{ __('El rechazo es excepcional: coincidencia real en sanciones o falta de cooperación.') }}</div>
                </form>
            </div>
        @endif
        @if($cdd->cdd_decision !== 'pendiente')
            <div class="text-[12px] mt-3 {{ $cdd->cdd_decision === 'aprobado' ? 'text-ok-dark' : 'text-err' }} font-semibold">{{ __('Cumplimiento: comprador :d', ['d' => $cdd->cdd_decision]) }} · {{ __('riesgo :r', ['r' => $cdd->risk_rating]) }}</div>
            <div class="text-[11px] text-ink-500">{{ $who($cdd->cdd_decided_by) }} · {{ $fecha($cdd->cdd_decided_at) }} @if($cdd->cdd_notes) · {{ $cdd->cdd_notes }} @endif</div>
        @endif
    </div>

    {{-- 3 · Director de ventas --}}
    <div class="crm-card p-5 {{ $cdd->cdd_decision !== 'pendiente' ? '' : 'opacity-60' }}">
        <div class="flex items-center gap-2 mb-1"><span class="crm-pill bg-ink-100 text-ink-700">3</span><div class="text-[14px] font-semibold text-ink-900">{{ __('Aprobación final del director de ventas') }}</div></div>
        <p class="text-[12px] text-ink-500 mb-3">{{ __('Con el perfil de riesgo y el consejo de cumplimiento. Desviarse de ese consejo sólo con la consulta y aprobación de Osman y Zuleika, motivada y documentada.') }}</p>
        @if($cdd->cdd_decision !== 'pendiente' && $cdd->manager_decision === 'pendiente')
            <form method="POST" action="{{ route('admin.crm.cdd.update', $reservation->id) }}" class="space-y-2 m-0">@csrf
                <input type="hidden" name="action" value="manager">
                <input name="manager_notes" class="crm-input pl-3" placeholder="{{ __('Nota (opcional)') }}" value="{{ old('manager_notes') }}">
                <textarea name="deviation_note" rows="2" class="crm-input pl-3 py-2 {{ $errors->has('deviation_note') ? '!border-err' : '' }}" placeholder="{{ __('Sólo si te desvías del consejo de cumplimiento: motivo y consulta con Osman y Zuleika') }}">{{ old('deviation_note') }}</textarea>
                <div class="flex flex-wrap gap-2">
                    <button name="manager_decision" value="aprobado" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> {{ __('Aprobar la venta') }}</button>
                    <button name="manager_decision" value="rechazado" class="crm-btn crm-btn-ghost text-err">{{ __('Rechazar la venta') }}</button>
                </div>
            </form>
        @elseif($cdd->manager_decision !== 'pendiente')
            <div class="text-[12px] font-semibold {{ $cdd->manager_decision === 'aprobado' ? 'text-ok-dark' : 'text-err' }}">{{ __('Venta :d', ['d' => $cdd->manager_decision]) }}</div>
            <div class="text-[11px] text-ink-500">{{ $who($cdd->manager_decided_by) }} · {{ $fecha($cdd->manager_decided_at) }} @if($cdd->manager_notes) · {{ $cdd->manager_notes }} @endif</div>
            @if($cdd->deviation_note)<div class="text-[11px] text-warn mt-1">{{ __('Desviación motivada:') }} {{ $cdd->deviation_note }}</div>@endif
        @endif
    </div>

    {{-- 4-7 · Contrato, FIU, revisión previa y escritura --}}
    <div class="crm-card p-5 {{ $cdd->saleApproved() ? '' : 'opacity-60' }}">
        <div class="flex items-center gap-2 mb-3"><span class="crm-pill bg-ink-100 text-ink-700">4–7</span><div class="text-[14px] font-semibold text-ink-900">{{ __('Contrato, FIU, revisión previa y escritura') }}</div></div>
        <div class="divide-y divide-ink-100">
            <div class="py-3 flex flex-col sm:flex-row sm:items-center gap-2">
                <div class="flex-1 text-[12.5px]"><b>{{ __('Contrato de compraventa firmado') }}</b><div class="text-[11px] text-ink-500">{{ __('Cumplimiento debe recibir la copia firmada cuanto antes.') }}</div></div>
                @if($cdd->spa_signed_at)
                    <span class="text-[12px] text-ok-dark font-semibold">{{ $fecha($cdd->spa_signed_at) }}</span>
                @elseif($cdd->saleApproved())
                    <form method="POST" action="{{ route('admin.crm.cdd.update', $reservation->id) }}" class="flex gap-2 m-0">@csrf<input type="hidden" name="action" value="spa">
                        <input type="date" name="spa_signed_at" max="{{ today()->toDateString() }}" required class="crm-input pl-3 w-auto"><button class="crm-btn crm-btn-primary">{{ __('Registrar') }}</button></form>
                @endif
            </div>
            <div class="py-3 flex flex-col sm:flex-row sm:items-center gap-2">
                <div class="flex-1 text-[12.5px]"><b>{{ __('Informe a la FIU') }}</b>
                    <div class="text-[11px] text-ink-500">
                        @if($cdd->fiuRequired()) {{ __('Obligatorio: la venta supera los ƒ :t. El MLRO lo presenta en 5 días hábiles desde la firma.', ['t' => number_format(config('company.cdd.fiu_threshold_awg'))]) }}
                            @if($cdd->fiuDueDate() && ! $cdd->fiu_reported_at) <b class="{{ $cdd->fiuDueDate()->isPast() ? 'text-err' : 'text-warn' }}">{{ __('Vence el :f.', ['f' => $fecha($cdd->fiuDueDate())]) }}</b> @endif
                        @else {{ __('No hace falta: la venta no llega al umbral.') }} @endif
                    </div>
                </div>
                @if($cdd->fiu_reported_at)
                    <span class="text-[12px] text-ok-dark font-semibold">{{ $fecha($cdd->fiu_reported_at) }} {{ $cdd->fiu_reference }}</span>
                @elseif($cdd->fiuRequired() && $cdd->spa_signed_at)
                    <form method="POST" action="{{ route('admin.crm.cdd.update', $reservation->id) }}" class="flex flex-wrap gap-2 m-0">@csrf<input type="hidden" name="action" value="fiu">
                        <input type="date" name="fiu_reported_at" required class="crm-input pl-3 w-auto"><input name="fiu_reference" placeholder="{{ __('Referencia') }}" class="crm-input pl-3 w-32"><button class="crm-btn crm-btn-primary">{{ __('Registrar') }}</button></form>
                @endif
            </div>
            <div class="py-3 flex flex-col sm:flex-row sm:items-center gap-2">
                <div class="flex-1 text-[12.5px]"><b>{{ __('Revisión antes de la escritura') }}</b><div class="text-[11px] text-ink-500">{{ __('Todas las transacciones y cambios en el comprador, antes de ir al notario.') }}</div></div>
                @if($cdd->predeed_reviewed_at)
                    <span class="text-[12px] text-ok-dark font-semibold">{{ __('Luz verde') }} · {{ $fecha($cdd->predeed_reviewed_at) }}</span>
                @elseif($cdd->spa_signed_at && (! $cdd->fiuRequired() || $cdd->fiu_reported_at))
                    <form method="POST" action="{{ route('admin.crm.cdd.update', $reservation->id) }}" class="flex flex-wrap gap-2 m-0">@csrf<input type="hidden" name="action" value="predeed">
                        <input name="predeed_notes" placeholder="{{ __('Notas de la revisión') }}" class="crm-input pl-3 sm:w-64"><button class="crm-btn crm-btn-primary">{{ __('Dar luz verde') }}</button></form>
                @endif
            </div>
            <div class="py-3 flex flex-col sm:flex-row sm:items-center gap-2">
                <div class="flex-1 text-[12.5px]"><b>{{ __('Escritura de compraventa') }}</b><div class="text-[11px] text-ink-500">{{ __('Con la fecha, se genera el informe final de riesgo para el archivo (mínimo 10 años).') }}</div></div>
                @if($cdd->deed_date)
                    <span class="text-[12px] text-ok-dark font-semibold">{{ $fecha($cdd->deed_date) }}</span>
                @elseif($cdd->predeed_reviewed_at)
                    <form method="POST" action="{{ route('admin.crm.cdd.update', $reservation->id) }}" class="flex gap-2 m-0">@csrf<input type="hidden" name="action" value="deed">
                        <input type="date" name="deed_date" required class="crm-input pl-3 w-auto"><button class="crm-btn crm-btn-primary">{{ __('Registrar') }}</button></form>
                @endif
            </div>
        </div>
    </div>

    {{-- Registro --}}
    <div class="crm-card p-5">
        <div class="text-[13px] font-semibold text-ink-900 mb-2">{{ __('Registro de auditoría') }}</div>
        @forelse(array_reverse((array) ($cdd->log ?? [])) as $l)
            <div class="py-1.5 border-b border-ink-100 text-[12px] flex flex-wrap gap-x-3"><span class="text-ink-400 w-32 shrink-0">{{ \Carbon\Carbon::parse($l['at'])->translatedFormat('j M Y, H:i') }}</span><span class="text-ink-600 shrink-0">{{ $l['by'] ?? __('Comprador') }}</span><span class="text-ink-900 flex-1 min-w-[200px]">{{ $l['text'] }}</span></div>
        @empty
            <div class="text-[12px] text-ink-500">{{ __('Sin movimientos todavía.') }}</div>
        @endforelse
    </div>
</div>
