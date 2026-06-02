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
    $nextPay = $reservation->payments->where('status', 'pending')->sortBy('due_date')->first();

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
            <div class="text-[12px] text-ink-500">Makai Residences · Cap Cana, Punta Cana</div>
        </div>
        <button onclick="openWireTransferModal()" class="ml-auto shrink-0 inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-ink-200 bg-white text-[12px] font-semibold text-ink-700 hover:border-brand hover:text-brand transition-colors">
            <i class="pi pi-building-columns text-[12px]"></i> {{ __('Datos para transferencia') }}
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

    {{-- Progress --}}
    <div class="cli-card p-5">
        <div class="flex items-center justify-between text-[13px] mb-3">
            <span class="font-semibold text-ink-950">{{ __('Progreso del plan de pagos') }}</span>
            <span class="font-bold text-ok-dark text-[16px]">{{ $pct }}%</span>
        </div>
        <div class="relative h-2 rounded-full bg-ink-100 overflow-visible">
            <div class="absolute inset-y-0 left-0 rounded-full bg-ok transition-all" style="width:{{ $pct }}%"></div>
            {{-- Milestone markers --}}
            @foreach([['0%', 0], ['5% '.__('Reserva'), 5], ['20% '.__('Fin construcción'), 20], ['100% '.__('Entrega'), 100]] as [$label, $val])
                @php
                    $reached = $pct >= $val;
                    $clr = $reached ? '#1fc16b' : '#cacfd8';
                @endphp
                <div class="absolute top-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full border-2 border-white"
                     style="left:calc({{ $val }}% - 5px); background:{{ $clr }}; box-shadow:0 0 0 1px {{ $clr }};">
                </div>
            @endforeach
        </div>
        <div class="relative mt-3 text-[10px] uppercase tracking-wider font-semibold text-ink-400" style="height:14px;">
            @foreach([['0%', 0, 'left-0'], ['5% '.__('Reserva'), 5, ''], ['20% '.__('Fin construcción'), 20, ''], ['100% '.__('Entrega'), 100, 'right-0']] as $i => [$label, $val, $align])
                @php
                    $reached = $pct >= $val;
                    $transform = $val === 0 ? '' : ($val === 100 ? 'transform:translateX(-100%);' : 'transform:translateX(-50%);');
                @endphp
                <span class="absolute whitespace-nowrap {{ $reached ? 'text-ok-dark' : '' }}"
                      style="left:{{ $val }}%; {{ $transform }}">{{ $label }}</span>
            @endforeach
        </div>
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
                    $rows = $pagados->concat($vencidos)->concat($upcoming);
                @endphp
                @forelse($rows as $i => $p)
                    @php
                        $isOverdue = $p->status === 'overdue';
                        $isPaid    = $p->status === 'paid';
                        $isNext    = $nextPay && $p->id === $nextPay->id;
                        $rowBg     = $isNext ? 'bg-warn-soft/40' : '';
                        $bullet    = $isPaid ? 'bg-ok' : ($isOverdue ? 'bg-err' : ($isNext ? 'bg-warn' : 'bg-ink-200'));
                        $balance   = $isPaid ? 0 : $p->amount;
                    @endphp
                    <tr class="{{ $rowBg }}">
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
                            @elseif($isOverdue)
                                <span class="cli-pill bg-err-soft text-err">{{ __('VENCIDO') }}</span>
                            @else
                                <span class="cli-pill bg-warn-soft text-warn-dark">{{ __('PENDIENTE') }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3.5 text-right">
                            @if($isNext)
                                <button type="button" onclick="document.getElementById('modal-pagar').showModal()" class="cli-btn cli-btn-ghost text-[11px] py-1 px-3">{{ __('Pagar') }}</button>
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
                + {{ $more }} {{ __('cuotas pendientes restantes') }} · {{ __('Total') }}: ${{ number_format($totalMore, 0) }}
                <button class="text-brand font-semibold hover:underline ml-2">{{ __('Ver todos') }} <i class="pi pi-angle-down text-[10px]"></i></button>
            </div>
        @endif
    </div>

    {{-- Pagos en revisión --}}
    @if($enRevision->count() > 0)
    <div class="cli-card overflow-hidden">
        <div class="px-5 py-3 flex items-center gap-3 bg-warn-soft/40 border-b border-ink-100">
            <div class="w-8 h-8 rounded-full bg-warn-soft flex items-center justify-center text-warn-dark"><i class="pi pi-clock"></i></div>
            <div class="text-[14px] font-bold text-ink-950">{{ __('Pagos en revisión') }}</div>
            <span class="ml-auto text-[11px] text-warn-dark">{{ $enRevision->count() }} {{ __('en revisión') }}</span>
        </div>
        <table class="w-full">
            <thead class="bg-white">
                <tr>
                    <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Concepto') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Monto') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Fecha') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Estado') }}</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Comprobante') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach($enRevision as $p)
                    <tr>
                        <td class="px-5 py-3.5 text-[13px] font-semibold text-ink-950">{{ $p->label ?? __('Cuota') }}</td>
                        <td class="px-3 py-3.5 text-[13px] font-bold text-warn-dark">${{ number_format($p->amount, 0) }}</td>
                        <td class="px-3 py-3.5 text-[12px] text-ink-700">{{ optional($p->paid_at)->format('Y-m-d') }}</td>
                        <td class="px-3 py-3.5">
                            <span class="cli-pill bg-warn-soft text-warn-dark">{{ __('EN REVISIÓN') }}</span>
                        </td>
                        <td class="px-3 py-3.5 text-[12px] text-ink-500">
                            @if($p->receipt_path)
                                <a href="{{ asset('storage/'.$p->receipt_path) }}" target="_blank" class="text-brand font-semibold hover:underline">{{ __('Ver') }}</a>
                            @else — @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

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
                            <a href="{{ route('payments.receipt', $p) }}" target="_blank" class="text-brand font-semibold hover:underline">{{ __('Comprobante') }}</a>
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
    <form id="paymentForm" enctype="multipart/form-data" class="w-[520px] bg-white rounded-2xl overflow-hidden">
        @csrf
        <input type="hidden" name="payment_id" value="{{ $nextPay->id ?? '' }}">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-credit-card"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Subir comprobante de pago') }}</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-4">
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
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="openWireTransferModal()" class="cli-btn cli-btn-ghost text-brand"><i class="pi pi-building-columns"></i> {{ __('Ver datos de transferencia') }}</button>
            <button type="button" onclick="this.closest('dialog').close()" class="cli-btn cli-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" id="submitPaymentBtn" class="cli-btn cli-btn-primary"><i class="pi pi-check"></i> {{ __('Enviar para aprobación') }}</button>
        </div>
    </form>
</dialog>

{{-- Wire Transfer Modal --}}
<dialog id="modal-wire-transfer" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto max-w-4xl">
    <div class="bg-white rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-building-columns"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Datos para transferencia en USD') }}</div>
            <button type="button" onclick="document.getElementById('modal-wire-transfer').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6" id="wire-transfer-content">
            <div class="text-center py-8">
                <i class="pi pi-spin pi-spinner text-ink-400 text-[24px]"></i>
                <div class="text-[13px] text-ink-500 mt-2">{{ __('Cargando datos...') }}</div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="downloadWireTransferPDF()" class="cli-btn cli-btn-primary"><i class="pi pi-download"></i> {{ __('Descargar PDF') }}</button>
            <button type="button" onclick="document.getElementById('modal-wire-transfer').close()" class="cli-btn cli-btn-ghost">{{ __('Cerrar') }}</button>
        </div>
    </div>
</dialog>

<script>
const paymentSubmitUrl = "{{ route('dashboard.payments.submit', $reservation) }}";
const wireTransferUrl = "{{ route('reservations.wire', $reservation) }}";

// Open wire transfer modal
function openWireTransferModal() {
    const modal = document.getElementById('modal-wire-transfer');
    const content = document.getElementById('wire-transfer-content');
    
    modal.showModal();
    content.innerHTML = `
        <div class="text-center py-8">
            <i class="pi pi-spin pi-spinner text-ink-400 text-[24px]"></i>
            <div class="text-[13px] text-ink-500 mt-2">{{ __('Cargando datos...') }}</div>
        </div>
    `;
    
    fetch(wireTransferUrl)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const pageContent = doc.querySelector('.page');
            
            if (pageContent) {
                content.innerHTML = '';
                content.appendChild(pageContent.cloneNode(true));
                content.querySelector('.page').style.maxHeight = '60vh';
                content.querySelector('.page').style.overflowY = 'auto';
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center py-8">
                    <i class="pi pi-exclamation-circle text-err text-[24px]"></i>
                    <div class="text-[13px] text-ink-500 mt-2">{{ __('Error al cargar los datos') }}</div>
                </div>
            `;
        });
}

// Download wire transfer PDF
function downloadWireTransferPDF() {
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = wireTransferUrl;
    document.body.appendChild(iframe);
    
    iframe.onload = function() {
        iframe.contentWindow.print();
        setTimeout(() => document.body.removeChild(iframe), 1000);
    };
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
        alert('Error de red. Intenta de nuevo.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="pi pi-check"></i> Enviar para aprobación';
    }
});
</script>
@endsection
