@extends('layouts.client')
@section('title', __('Plan de pagos').' — MAKAI')
@section('page_title', __('Plan de pagos'))
@section('page_breadcrumb', __('Mi propiedad').' · '.__('Plan de pagos'))
@php $activeRoute = 'payments'; @endphp

@section('content')
@php
    $unidad  = $reservation->unit->custom_id ?? $reservation->unit->name ?? __('Unidad');
    $precio  = (float) ($reservation->unit->price ?? 0);
    $pagado  = (float) ($reservation->payments?->where('status', 'paid')->sum('amount') ?? 0);
    $saldo   = max(0, $precio - $pagado);
    $pct     = $precio > 0 ? round(($pagado / $precio) * 100) : 0;
    $nextPay = $reservation->payments->where('status', 'pending')->where('approval_status', '!=', 'pending')->sortBy('due_date')->first();

    $pendientes = $reservation->payments->where('status', 'pending')->where('approval_status', '!=', 'pending')->sortBy('due_date');
    $vencidos   = $reservation->payments->where('status', 'overdue')->sortBy('due_date');
    $pagados    = $reservation->payments->where('status', 'paid')->sortByDesc('paid_at');
    $enRevision = $reservation->payments->where('approval_status', 'pending')->sortByDesc('created_at');

    $upcoming = $pendientes->take(5);
    $more     = max(0, $pendientes->count() - 5);
    $totalMore = $pendientes->skip(5)->sum('amount');
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    {{-- Sub header --}}
    <div class="px-4 py-3 rounded-xl bg-ink-100/60 border border-ink-200 flex items-center gap-3">
        <div class="min-w-0">
            <div class="text-[15px] font-bold text-ink-950">{{ $unidad }}</div>
            <div class="text-[12px] text-ink-500">{{ __('Makai Residences · Cap Cana, Punta Cana') }}</div>
        </div>
        <button onclick="downloadBankData()" class="ml-auto shrink-0 inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-ink-200 bg-white text-[12px] font-semibold text-ink-700 hover:border-brand hover:text-brand transition-colors">
            <i class="pi pi-download text-[12px]"></i> {{ __('Descargar datos bancarios') }}
        </button>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $kpis = [
            [__('Precio total'),     '$'.number_format($precio, 0),   'text-ink-950'],
            [__('Total pagado'),     '$'.number_format($pagado, 0),   'text-ink-950'],
            [__('Balance pendiente'),'$'.number_format($saldo, 0),    'text-ink-950'],
            [__('Próxima cuota'),    '$'.number_format($nextPay->amount ?? 2694, 0),  'text-ok-dark'],
        ]; @endphp
        @foreach($kpis as $k)
            <div class="cli-card p-4">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400">{{ $k[0] }}</div>
                <div class="font-display text-[26px] font-bold {{ $k[2] }} leading-tight mt-2">{{ $k[1] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Progreso del plan de pagos — stepper por etapas reales del plan.
         Cada etapa agrupa los pagos por payment_type: reserva → inicial →
         construcción (cuotas) → entrega. Las etapas se distribuyen de forma
         equidistante sin importar su % o monto, y el conector se rellena en
         verde a medida que se completan. --}}
    @php
        $allPayments = $reservation->payments;

        // Definición de etapas en orden, con los payment_type que agrupa cada una.
        $phaseDefs = [
            ['key' => 'reservation', 'types' => ['reservation'],               'label' => __('Reserva'),      'icon' => 'pi-bookmark-fill'],
            ['key' => 'initial',     'types' => ['initial'],                   'label' => __('Pago inicial'), 'icon' => 'pi-flag-fill'],
            ['key' => 'construction','types' => ['construction', 'installment'],'label' => __('Construcción'), 'icon' => 'pi-building'],
            ['key' => 'delivery',    'types' => ['delivery'],                  'label' => __('Entrega'),      'icon' => 'pi-key'],
        ];

        $steps = [];
        foreach ($phaseDefs as $def) {
            $group = $allPayments->whereIn('payment_type', $def['types']);
            if ($group->isEmpty()) {
                continue; // la etapa no aplica a este plan
            }
            $total = $group->count();
            $paidN = $group->where('status', 'paid')->count();
            $steps[] = [
                'label'   => $def['label'],
                'icon'    => $def['icon'],
                'total'   => $total,
                'paidN'   => $paidN,
                'amount'  => (float) $group->sum('amount'),
                'paidAmt' => (float) $group->where('status', 'paid')->sum('amount'),
                'done'    => $total > 0 && $paidN === $total,
                'partial' => $paidN > 0 && $paidN < $total,
            ];
        }

        // Fallback: si no hay fila de reserva pero existe seña configurada, mostrarla.
        if (! collect($steps)->firstWhere('label', __('Reserva')) && (float) ($reservation->reservation_fee ?? 0) > 0) {
            array_unshift($steps, [
                'label' => __('Reserva'), 'icon' => 'pi-bookmark-fill',
                'total' => 1, 'paidN' => 0, 'amount' => (float) $reservation->reservation_fee,
                'paidAmt' => 0, 'done' => false, 'partial' => false,
            ]);
        }

        $stepsTotal = count($steps);

        // --- Barra proporcional ---------------------------------------------
        // Cada etapa ocupa un tramo de la barra proporcional a su monto sobre el
        // total del plan. El relleno verde representa lo efectivamente pagado.
        $planTotal = collect($steps)->sum('amount') ?: 1;
        $paidTotal = collect($steps)->sum('paidAmt');
        $barPct    = (int) round($paidTotal / $planTotal * 100);

        $markers   = [];
        $cumStart  = 0;
        foreach ($steps as $s) {
            $width  = $s['amount'] / $planTotal * 100;       // ancho del tramo
            $center = $cumStart + $width / 2;                 // centro del tramo
            $cumStart += $width;                             // límite acumulado
            $markers[] = [
                'label'  => $s['label'],
                'center' => round($center, 2),
                'end'    => round($cumStart, 2),
                'done'   => $s['done'],
            ];
        }
    @endphp

    <div class="cli-card p-5">
        <div class="flex items-center justify-between text-[13px] mb-3">
            <span class="font-semibold text-ink-950">{{ __('Progreso del plan de pagos') }}</span>
            <span class="font-bold text-ok-dark text-[16px]">{{ $barPct }}%</span>
        </div>

        @if($stepsTotal > 0)
            <div class="relative h-2 rounded-full bg-ink-100 overflow-visible">
                <div class="absolute inset-y-0 left-0 rounded-full bg-ok transition-all" style="width:{{ $barPct }}%"></div>
                {{-- Marcadores de etapa en el límite de cada tramo --}}
                @foreach($markers as $m)
                    @php $clr = $m['done'] ? '#1fc16b' : '#cacfd8'; @endphp
                    <div class="absolute top-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full border-2 border-white"
                         style="left:calc({{ $m['end'] }}% - 5px); background:{{ $clr }}; box-shadow:0 0 0 1px {{ $clr }};"></div>
                @endforeach
            </div>
            {{-- Etiquetas centradas bajo cada tramo (espaciado proporcional al monto) --}}
            <div class="relative mt-3 text-[10px] uppercase tracking-wider font-semibold text-ink-400" style="height:14px;">
                @foreach($markers as $m)
                    @php
                        // Clamp en los extremos para que la etiqueta no se corte.
                        if ($m['center'] <= 10) {
                            $style = 'left:0;';
                        } elseif ($m['center'] >= 90) {
                            $style = 'right:0;';
                        } else {
                            $style = 'left:'.$m['center'].'%; transform:translateX(-50%);';
                        }
                    @endphp
                    <span class="absolute whitespace-nowrap {{ $m['done'] ? 'text-ok-dark' : '' }}"
                          style="{{ $style }}">{{ $m['label'] }}</span>
                @endforeach
            </div>
        @else
            <div class="text-[12px] text-ink-500 text-center py-2">{{ __('Aún no hay etapas del plan de pagos generadas.') }}</div>
        @endif
    </div>

    {{-- Calendario de pagos --}}
    <div class="cli-card overflow-hidden">
        <div class="px-5 py-3 flex items-center gap-3 bg-ink-50/60 border-b border-ink-100">
            <div class="w-8 h-8 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-calendar"></i></div>
            <div class="text-[14px] font-bold text-ink-950">{{ __('Calendario de pagos') }}</div>
            <div class="ml-auto flex items-center gap-4 text-[11px] text-ink-500">
                <span class="flex items-center gap-1.5"><span class="dot bg-ok"></span> {{ __('Pagado') }}</span>
                <span class="flex items-center gap-1.5"><span class="dot bg-warn"></span> {{ __('Próximo') }}</span>
                <span class="flex items-center gap-1.5"><span class="dot bg-err"></span> {{ __('Vencido') }}</span>
                @if($nextPay)
                    <button onclick="document.getElementById('modal-pagar').showModal()" class="cli-btn cli-btn-primary text-[11px] py-1.5 px-3"><i class="pi pi-plus text-[10px]"></i> {{ __('Registrar pago') }}</button>
                @endif
            </div>
        </div>
        <table class="w-full">
            <thead class="bg-white">
                <tr>
                    <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Concepto') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Fecha') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Monto programado') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Pagado') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Saldo') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Estado') }}</th>
                    <th class="px-3 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @php
                    // Show paid + in-review + overdue first, then ALL pending cuotas.
                    // The pending ones beyond the 5th are rendered hidden and revealed by "Ver todos".
                    $rows = $pagados->concat($enRevision)->concat($vencidos)->concat($pendientes);
                    $pendingSeen = 0;
                @endphp
                @forelse($rows as $i => $p)
                    @php
                        $isPaid       = $p->status === 'paid';
                        $inReview     = ! $isPaid && $p->approval_status === 'pending';
                        $isOverdue    = ! $inReview && $p->status === 'overdue';
                        $isNext       = ! $inReview && $nextPay && $p->id === $nextPay->id;
                        $isPendingRow = ! $isPaid && ! $inReview && ! $isOverdue;
                        $isExtra      = false;
                        if ($isPendingRow) { $pendingSeen++; $isExtra = $pendingSeen > 5; }
                        $rowBg        = $isNext ? 'bg-warn-soft/40' : '';
                        $bullet       = $isPaid ? 'bg-ok' : ($inReview ? 'bg-info' : ($isOverdue ? 'bg-err' : ($isNext ? 'bg-warn' : 'bg-ink-200')));
                        $balance      = $isPaid ? 0 : $p->amount;
                    @endphp
                    <tr class="{{ $rowBg }} {{ $isExtra ? 'pay-extra-row hidden' : '' }}">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="dot {{ $bullet }} shrink-0"></span>
                                <div>
                                    <div class="text-[13px] font-semibold text-ink-950">{{ $p->label ?? __('Reserva (5%)') }}</div>
                                    <div class="text-[11px] text-ink-500">{{ __('Pago inicial de reserva') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3.5 text-[12px] {{ $isOverdue || $isNext ? 'text-warn-dark font-semibold' : 'text-ink-700' }}">
                            {{ optional($p->paid_at ?? $p->due_date)->locale(app()->getLocale())->isoFormat(app()->getLocale()==='es' ? 'D MMM YYYY' : 'MMM D, YYYY') }}
                        </td>
                        <td class="px-3 py-3.5 text-[13px] font-semibold text-ink-950">${{ number_format($p->amount, 0) }}</td>
                        <td class="px-3 py-3.5 text-[13px] font-semibold {{ $isPaid ? 'text-ok-dark' : 'text-ink-400' }}">
                            {{ $isPaid ? '$'.number_format($p->amount, 0) : '—' }}
                        </td>
                        <td class="px-3 py-3.5 text-[13px]">
                            @if($isPaid)
                                <i class="pi pi-check text-ok"></i>
                            @else
                                <span class="font-semibold text-ink-700">${{ number_format($balance, 0) }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3.5">
                            @if($isPaid)
                                <span class="cli-pill bg-ok-soft text-ok-dark">{{ __('PAGADO') }}</span>
                            @elseif($inReview)
                                <span class="cli-pill bg-info-soft text-info">{{ __('EN REVISIÓN') }}</span>
                            @elseif($isOverdue)
                                <span class="cli-pill bg-err-soft text-err">{{ __('VENCIDO') }}</span>
                            @else
                                <span class="cli-pill bg-warn-soft text-warn-dark">{{ __('PENDIENTE') }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3.5 text-right">
                            @if($isNext)
                                <button type="button" onclick="document.getElementById('modal-pagar').showModal()" class="cli-btn bg-warn text-white border-warn hover:bg-warn-dark text-[11px] py-1 px-3">{{ __('Pagar') }}</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-[12px] text-ink-500">{{ __('No hay cuotas registradas todavía.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($more > 0)
            <div class="px-5 py-3 text-center text-[12px] text-ink-500 bg-ink-50/60 border-t border-ink-100">
                <span id="pay-more-info">+ {{ $more }} {{ __('cuotas pendientes restantes') }} · {{ __('Total') }}: ${{ number_format($totalMore, 0) }}</span>
                <button type="button" id="pay-toggle-all" onclick="togglePayAll(this)" class="text-brand font-semibold hover:underline ml-2">{{ __('Ver todos') }} <i class="pi pi-angle-down text-[10px]"></i></button>
            </div>
        @endif
    </div>

    {{-- Pagos confirmados --}}
    <div class="cli-card overflow-hidden">
        <div class="px-5 py-3 flex items-center gap-3 bg-ink-50/60 border-b border-ink-100">
            <div class="w-8 h-8 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-receipt"></i></div>
            <div class="text-[14px] font-bold text-ink-950">{{ __('Pagos confirmados') }}</div>
            <span class="ml-auto text-[11px] text-ink-500">{{ $pagados->count() }} {{ __('transacciones') }}</span>
        </div>
        <table class="w-full">
            <thead class="bg-white">
                <tr>
                    <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Concepto') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Pagado') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Fecha') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Método') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Comprobante') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse($pagados as $p)
                    <tr>
                        <td class="px-5 py-3.5 text-[13px] font-semibold text-ink-950">{{ $p->label ?? __('Cuota inicial — Reserva') }}</td>
                        <td class="px-3 py-3.5 text-[13px] font-bold text-ok-dark">${{ number_format($p->amount, 0) }}</td>
                        <td class="px-3 py-3.5 text-[12px] text-ink-700">{{ optional($p->paid_at)->format('Y-m-d') }}</td>
                        <td class="px-3 py-3.5 text-[12px] text-ink-700">{{ $p->payment_method ?? 'Wire Transfer' }}</td>
                        <td class="px-3 py-3.5 text-[12px] text-ink-500">
                            <button type="button" onclick="openReceiptModal('{{ route('payments.receipt', $p) }}', '{{ route('payments.receipt.sign', $p) }}', {{ $p->receipt_signed_at ? 'true' : 'false' }})" class="text-brand font-semibold hover:underline">{{ __('Comprobante') }}</button>
                            @if($p->receipt_path)
                                <span class="text-ink-300 mx-1">·</span>
                                <a href="{{ asset('storage/'.$p->receipt_path) }}" target="_blank" class="text-ink-600 hover:underline">{{ __('Adjunto') }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-[12px] text-ink-500">{{ __('Sin pagos confirmados todavía.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal pagar (reuses admin modal markup) --}}
<dialog id="modal-pagar" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form id="paymentForm" enctype="multipart/form-data" class="w-[824px] max-w-[95vw] bg-white rounded-2xl overflow-hidden">
        @csrf
        <input type="hidden" name="payment_id" value="{{ $nextPay->id ?? '' }}">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-credit-card"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Subir comprobante de pago') }}</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="flex items-stretch">
        <div class="flex-1 min-w-0 p-6 space-y-4">
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Concepto') }}</label>
                <input type="text" name="label" required value="{{ $nextPay->label ?? __('Cuota') }}" class="cli-input pl-3 mt-1" readonly>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Monto') }}</label>
                <div class="relative mt-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                    <input type="number" step="0.01" name="amount" required value="{{ $nextPay->amount ?? 0 }}" class="cli-input pl-7" readonly>
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Método de pago') }}</label>
                <select name="payment_method" required class="cli-input pl-3 mt-1">
                    <option value="">{{ __('Seleccionar...') }}</option>
                    <option value="wire">{{ __('Transferencia bancaria') }}</option>
                    <option value="ach">ACH</option>
                    <option value="card">{{ __('Tarjeta') }}</option>
                    <option value="cash">{{ __('Efectivo') }}</option>
                    <option value="check">{{ __('Cheque') }}</option>
                </select>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Fecha de pago') }}</label>
                <input type="date" name="paid_at" required value="{{ now()->toDateString() }}" class="cli-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Comprobante de pago') }} *</label>
                <div id="receiptDropzone" class="border-2 border-dashed border-ink-200 rounded-xl py-6 px-4 text-center cursor-pointer hover:border-brand transition-colors" onclick="this.querySelector('input').click()">
                    <i class="pi pi-cloud-upload text-ink-400 text-[22px]"></i>
                    <div id="receiptPlaceholder" class="text-[13px] font-semibold text-ink-700 mt-2">{{ __('Sube tu comprobante') }}</div>
                    <div id="receiptFileName" class="text-[13px] font-semibold text-brand mt-2 hidden"></div>
                    <div class="text-[11px] text-ink-500 mt-1">{{ __('PDF, JPG o PNG · máx. 4 MB') }}</div>
                    <input type="file" name="receipt" id="receiptInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden" required>
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Notas (opcional)') }}</label>
                <textarea name="notes" rows="2" class="cli-input pl-3 pt-2 mt-1 h-auto resize-none" placeholder="{{ __('Referencia, descripción, etc.') }}"></textarea>
            </div>
        </div>
        @include('_partials.bank_panel')
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="downloadBankData()" class="cli-btn cli-btn-ghost text-brand"><i class="pi pi-download"></i> {{ __('Descargar datos bancarios') }}</button>
            <button type="button" onclick="this.closest('dialog').close()" class="cli-btn cli-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" id="submitPaymentBtn" class="cli-btn cli-btn-primary"><i class="pi pi-check"></i> {{ __('Enviar para aprobación') }}</button>
        </div>
    </form>
</dialog>

{{-- Estilos del lienzo de firma del comprobante (mismo look que "Acuerdos") --}}
<style>
    #receipt-sig-canvas {
        width: 100%; height: 110px;
        background:#fff; border:1px dashed #cacfd8; border-radius:10px;
        cursor: crosshair; touch-action: none;
        display:block;
    }
    .rcs-canvas-wrap { position:relative; }
    .rcs-canvas-wrap.has-stroke .rcs-empty-canvas { display:none; }
    .rcs-canvas-wrap.has-stroke #receipt-sig-canvas { border-style:solid; border-color:#5c7c68; }
    .rcs-empty-canvas {
        position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
        color:#a3a3a3; font-size:11px; pointer-events:none; font-style:italic;
    }
</style>

{{-- Receipt Modal --}}
<dialog id="modal-receipt" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <div class="bg-white rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-receipt"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Comprobante de pago') }}</div>
            <button type="button" onclick="document.getElementById('modal-receipt').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div id="receipt-content" style="width:794px;max-width:90vw;background:#f0efec">
            <div class="text-center py-8">
                <i class="pi pi-spin pi-spinner text-ink-400 text-[24px]"></i>
                <div class="text-[13px] text-ink-500 mt-2">{{ __('Cargando comprobante...') }}</div>
            </div>
        </div>

        {{-- Firma obligatoria: el cliente debe firmar antes de poder descargar el comprobante.
             Mismo diseño que el panel de firma de "Acuerdos". --}}
        <div id="receipt-sign-panel" class="px-6 py-4 border-t border-ink-100 bg-white" style="width:794px;max-width:90vw">
            <div class="flex items-center gap-2 text-[13px] font-bold text-ink-900"><i class="pi pi-pencil text-brand"></i> {{ __('Firma el comprobante para descargarlo') }}</div>
            <div class="text-[12px] text-ink-700 mt-2 mb-3">
                {{ __('Tu firma equivale a una firma manuscrita y se incorpora al comprobante como constancia de recepción.') }}
            </div>

            <label class="text-[11px] uppercase font-bold tracking-wider text-ink-500">{{ __('Nombre completo') }}</label>
            <input type="text" id="receipt-sig-name" class="w-full mt-1 rounded-xl border border-ink-200 px-3 py-2 text-[13px] outline-none focus:border-brand focus:ring-2 focus:ring-brand/20" placeholder="{{ __('Tal como aparece en tu documento') }}">

            <label class="text-[11px] uppercase font-bold tracking-wider text-ink-500 mt-3 block">{{ __('Firma') }}</label>
            <div class="rcs-canvas-wrap mt-1" id="receipt-canvas-wrap">
                <canvas id="receipt-sig-canvas"></canvas>
                <div class="rcs-empty-canvas" id="receipt-sig-empty">{{ __('Firma aquí con el mouse o el dedo') }}</div>
            </div>
            <div class="flex items-center justify-between mt-1.5">
                <button type="button" onclick="receiptClearSig()" class="text-[11px] text-ink-500 hover:text-ink-900 font-semibold inline-flex items-center gap-1"><i class="pi pi-refresh text-[10px]"></i> {{ __('Limpiar') }}</button>
                <span class="text-[10px] text-ink-400">{{ __('Trazo manuscrito · obligatorio') }}</span>
            </div>

            <label class="mt-4 flex items-start gap-2 text-[12px] text-ink-700 cursor-pointer">
                <input type="checkbox" id="receipt-sig-accept" class="mt-0.5 accent-brand">
                <span>{{ __('Confirmo la recepción de este comprobante. Entiendo que esta firma electrónica es legalmente vinculante.') }}</span>
            </label>

            <button type="button" id="receipt-sig-confirm" onclick="receiptSign()" class="cli-btn cli-btn-primary w-full mt-4 inline-flex items-center justify-center gap-2 py-2.5">
                <i class="pi pi-check text-[11px]"></i> {{ __('Firmar comprobante') }}
            </button>
        </div>

        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <span id="receipt-sign-hint" class="text-[11px] text-ink-500 mr-auto inline-flex items-center gap-1"><i class="pi pi-info-circle text-[10px]"></i> {{ __('Firma el comprobante para habilitar la descarga.') }}</span>
            <button type="button" id="receipt-download-btn" onclick="printReceipt()" disabled class="cli-btn cli-btn-primary opacity-50 cursor-not-allowed"><i class="pi pi-download"></i> {{ __('Descargar PDF') }}</button>
            <button type="button" onclick="document.getElementById('modal-receipt').close()" class="cli-btn cli-btn-ghost">{{ __('Cerrar') }}</button>
        </div>
    </div>
</dialog>

<script>
const paymentSubmitUrl = "{{ route('dashboard.payments.submit', $reservation) }}";
const wireTransferUrl = "{{ route('reservations.wire', $reservation) }}";

// Auto-abrir el modal de pago cuando se llega con ?pay=1 (ej. desde el calendario)
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('pay') === '1') {
        const modal = document.getElementById('modal-pagar');
        if (modal && typeof modal.showModal === 'function') {
            modal.showModal();
        }
        // Limpiar el parámetro de la URL para que no reabra al recargar
        params.delete('pay');
        const clean = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', clean);
    }
});

// Descargar datos bancarios — sin modal: cargamos la hoja de transferencia en un
// iframe oculto y disparamos la impresión / guardar como PDF directamente.
function downloadBankData() {
    let frame = document.getElementById('bank-data-frame');
    if (frame) frame.remove();
    frame = document.createElement('iframe');
    frame.id = 'bank-data-frame';
    frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden';
    frame.src = wireTransferUrl;
    frame.onload = function() {
        try {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        } catch (e) {
            window.open(wireTransferUrl, '_blank');
        }
    };
    document.body.appendChild(frame);
}

// "Ver todos" — muestra/oculta las cuotas pendientes extra del calendario.
function togglePayAll(btn) {
    const rows = document.querySelectorAll('.pay-extra-row');
    if (!rows.length) return;
    const willShow = rows[0].classList.contains('hidden');
    rows.forEach(r => r.classList.toggle('hidden', !willShow));
    btn.innerHTML = willShow
        ? `{{ __('Ver menos') }} <i class="pi pi-angle-up text-[10px]"></i>`
        : `{{ __('Ver todos') }} <i class="pi pi-angle-down text-[10px]"></i>`;
    const info = document.getElementById('pay-more-info');
    if (info) info.style.display = willShow ? 'none' : '';
}

// ---- Comprobante: firma obligatoria antes de descargar ----
let receiptSigCtx = null, receiptSigDrawing = false, receiptSigHasStroke = false, receiptSigned = false;
let receiptSignUrl = null;

function getCsrfToken() { return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'; }

// Open receipt modal — render the payment receipt inside an iframe so its own
// styles are preserved. The download stays locked until the client signs.
// `alreadySigned` viene del backend: si el pago ya tiene firma persistida, el
// comprobante ya la muestra dentro del iframe y sólo habilitamos la descarga.
function openReceiptModal(url, signUrl, alreadySigned) {
    const modal = document.getElementById('modal-receipt');
    const content = document.getElementById('receipt-content');

    // Reset firma state on each open
    receiptSignUrl = signUrl || null;
    receiptSigned = !!alreadySigned;
    receiptSigHasStroke = false;
    const dlBtn = document.getElementById('receipt-download-btn');
    const panel = document.getElementById('receipt-sign-panel');
    document.getElementById('receipt-sig-name').value = '';
    const accept = document.getElementById('receipt-sig-accept');
    if (accept) accept.checked = false;
    document.getElementById('receipt-canvas-wrap')?.classList.remove('has-stroke');

    if (receiptSigned) {
        // Ya firmado: ocultar el panel y habilitar la descarga directa.
        panel.style.display = 'none';
        dlBtn.disabled = false;
        dlBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        const hint = document.getElementById('receipt-sign-hint');
        hint.innerHTML = `<i class="pi pi-check-circle text-[10px] text-ok"></i> {{ __('Comprobante firmado. Ya podés descargarlo.') }}`;
    } else {
        panel.style.display = '';
        panel.classList.remove('opacity-50', 'pointer-events-none');
        dlBtn.disabled = true;
        dlBtn.classList.add('opacity-50', 'cursor-not-allowed');
        document.getElementById('receipt-sign-hint').style.display = '';
    }

    modal.showModal();
    content.innerHTML = `<iframe id="receipt-iframe" src="${url}" title="{{ __('Comprobante de pago') }}" style="width:794px;max-width:90vw;height:72vh;border:0;display:block;background:#fff"></iframe>`;
    if (!receiptSigned) setTimeout(() => { receiptInitCanvas(); receiptClearSig(); }, 60);
}

function receiptInitCanvas() {
    const canvas = document.getElementById('receipt-sig-canvas');
    if (!canvas) return;
    const rect = canvas.getBoundingClientRect();
    canvas.width  = rect.width  || 320;
    canvas.height = rect.height || 96;
    receiptSigCtx = canvas.getContext('2d');
    receiptSigCtx.strokeStyle = '#171717';
    receiptSigCtx.lineWidth = 2;
    receiptSigCtx.lineCap = 'round';
    receiptSigCtx.lineJoin = 'round';

    const pos = (e) => {
        const r = canvas.getBoundingClientRect();
        const t = e.touches ? e.touches[0] : e;
        return { x: t.clientX - r.left, y: t.clientY - r.top };
    };
    const start = (e) => { e.preventDefault(); receiptSigDrawing = true; const p = pos(e); receiptSigCtx.beginPath(); receiptSigCtx.moveTo(p.x, p.y); };
    const move  = (e) => {
        if (!receiptSigDrawing) return;
        e.preventDefault();
        const p = pos(e);
        receiptSigCtx.lineTo(p.x, p.y);
        receiptSigCtx.stroke();
        if (!receiptSigHasStroke) { receiptSigHasStroke = true; document.getElementById('receipt-canvas-wrap')?.classList.add('has-stroke'); }
    };
    const end = () => { receiptSigDrawing = false; };
    canvas.onmousedown = start;
    canvas.onmousemove = move;
    window.addEventListener('mouseup', end);
    canvas.ontouchstart = start;
    canvas.ontouchmove = move;
    canvas.ontouchend = end;
}

function receiptClearSig() {
    const canvas = document.getElementById('receipt-sig-canvas');
    if (receiptSigCtx && canvas) receiptSigCtx.clearRect(0, 0, canvas.width, canvas.height);
    receiptSigHasStroke = false;
    document.getElementById('receipt-canvas-wrap')?.classList.remove('has-stroke');
}

// Incrusta la firma en el recuadro "Recibido por" del comprobante dentro del iframe.
function receiptEmbedSignature(sigData) {
    try {
        const frame = document.getElementById('receipt-iframe');
        const doc = frame && frame.contentDocument;
        if (!doc) return;
        const boxes = doc.querySelectorAll('.sig-box');
        const box = boxes[boxes.length - 1]; // último = cliente
        if (!box) return;
        box.style.position = 'relative';
        const img = doc.createElement('img');
        img.src = sigData;
        img.style.cssText = 'max-height:44px;max-width:200px;object-fit:contain;position:absolute;left:0;bottom:2px';
        box.innerHTML = '';
        box.appendChild(img);
    } catch (e) { /* same-origin esperado; si falla, igual habilitamos la descarga */ }
}

function receiptSign() {
    const name = document.getElementById('receipt-sig-name').value.trim();
    const accept = document.getElementById('receipt-sig-accept');
    if (name.length < 3) { alert("{{ __('Escribí tu nombre completo.') }}"); return; }
    if (!receiptSigHasStroke) { alert("{{ __('Falta tu firma manuscrita.') }}"); return; }
    if (accept && !accept.checked) { alert("{{ __('Confirmá la recepción para continuar.') }}"); return; }
    if (!receiptSignUrl) { alert("{{ __('No se pudo registrar la firma.') }}"); return; }

    const canvas = document.getElementById('receipt-sig-canvas');
    const sigData = canvas.toDataURL('image/png');

    const btn = document.getElementById('receipt-sig-confirm');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="pi pi-spin pi-spinner text-[11px]"></i> {{ __('Firmando…') }}';

    const fd = new FormData();
    fd.append('signer_name', name);
    fd.append('signature_image', sigData);
    fd.append('_token', getCsrfToken());

    fetch(receiptSignUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: fd,
        credentials: 'same-origin',
    })
    .then(r => r.json().catch(() => ({})))
    .then(d => {
        if (d.success !== false) {
            // Firma persistida en el servidor: la incrustamos también en el iframe actual.
            receiptEmbedSignature(sigData);
            receiptSigned = true;
            const dlBtn = document.getElementById('receipt-download-btn');
            dlBtn.disabled = false;
            dlBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            document.getElementById('receipt-sign-panel').style.display = 'none';
            const hint = document.getElementById('receipt-sign-hint');
            hint.innerHTML = `<i class="pi pi-check-circle text-[10px] text-ok"></i> {{ __('Comprobante firmado. Ya podés descargarlo.') }}`;
        } else {
            alert(d.message || "{{ __('No se pudo registrar la firma.') }}");
            btn.disabled = false; btn.innerHTML = original;
        }
    })
    .catch(() => { alert("{{ __('Error de red al intentar firmar.') }}"); btn.disabled = false; btn.innerHTML = original; });
}

// Download/print the receipt — only after signing.
function printReceipt() {
    if (!receiptSigned) { alert("{{ __('Firmá el comprobante antes de descargarlo.') }}"); return; }
    const frame = document.getElementById('receipt-iframe');
    if (frame && frame.contentWindow) {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    } else if (frame) {
        window.open(frame.src, '_blank');
    }
}

// Show selected file name
document.getElementById('receiptInput').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    const placeholder = document.getElementById('receiptPlaceholder');
    const fileNameDisplay = document.getElementById('receiptFileName');
    const dropzone = document.getElementById('receiptDropzone');
    
    if (fileName) {
        placeholder.classList.add('hidden');
        fileNameDisplay.textContent = fileName;
        fileNameDisplay.classList.remove('hidden');
        dropzone.classList.remove('border-ink-200');
        dropzone.classList.add('border-brand', 'bg-brand-soft/20');
    } else {
        placeholder.classList.remove('hidden');
        fileNameDisplay.classList.add('hidden');
        dropzone.classList.add('border-ink-200');
        dropzone.classList.remove('border-brand', 'bg-brand-soft/20');
    }
});

document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('submitPaymentBtn');
    const formData = new FormData(this);
    
    btn.disabled = true;
    btn.innerHTML = '<i class="pi pi-spin pi-spinner"></i> Enviando...';
    
    try {
        const response = await fetch(paymentSubmitUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            document.getElementById('modal-pagar').close();
            this.reset();
            
            // Reset file display
            document.getElementById('receiptPlaceholder').classList.remove('hidden');
            document.getElementById('receiptFileName').classList.add('hidden');
            document.getElementById('receiptDropzone').classList.add('border-ink-200');
            document.getElementById('receiptDropzone').classList.remove('border-brand', 'bg-brand-soft/20');
            
            location.reload();
        } else {
            alert(data.message || 'Error al enviar el pago');
        }
    } catch (error) {
        alert('{{ __("Error de red. Intenta de nuevo.") }}');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="pi pi-check"></i> Enviar para aprobación';
    }
});
</script>
@endsection
