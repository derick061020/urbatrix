@extends('layouts.client')
@section('title', 'Presupuesto — MAKAI')
@section('page_title', 'Presupuesto')
@section('page_breadcrumb', 'Mi Propiedad · Presupuesto')
@php $activeRoute = 'mi-propiedad'; @endphp

@section('content')
@php
    $unidad  = $reservation->unit->custom_id ?? $reservation->unit->name ?? 'Unidad';
    $precio  = (float) ($reservation->unit->price ?? 0);
    $totalConLegales = $breakdown['total_con_legales'];
    $isDraft = $reservation->budget_status === 'draft';
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-6">

    {{-- Header --}}
    <div class="cli-card overflow-hidden">
        <div class="p-7 text-white relative overflow-hidden" style="background:linear-gradient(135deg,#5c7c68 0%, #3f5848 100%)">
            <div class="absolute -top-20 -right-32 w-[440px] h-[440px] pointer-events-none opacity-25" style="background:
                radial-gradient(circle at center, transparent 47%, rgba(255,255,255,.5) 47.5%, rgba(255,255,255,.5) 48.5%, transparent 49%),
                radial-gradient(circle at center, transparent 35%, rgba(255,255,255,.45) 35.5%, rgba(255,255,255,.45) 36.5%, transparent 37%),
                radial-gradient(circle at center, transparent 22%, rgba(255,255,255,.4) 22.5%, rgba(255,255,255,.4) 23.5%, transparent 24%),
                radial-gradient(circle at center, transparent 9%, rgba(255,255,255,.35) 9.5%, rgba(255,255,255,.35) 11%, transparent 11.5%);"></div>
            <div class="relative z-10 flex items-center gap-5">
                <div class="w-14 h-14 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
                    <i class="pi pi-file text-white text-[22px]"></i>
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-[0.18em] font-semibold opacity-80">{{ __('Presupuesto') }}</div>
                    <div class="font-display text-[32px] font-medium leading-tight">{{ $unidad }}</div>
                    <div class="text-[13px] opacity-80">{{ __('Makai Residences · Cap Cana, Punta Cana') }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($isDraft)
        <div class="cli-card p-6 bg-warn-soft/30 border border-warn/20">
            <div class="flex items-start gap-3">
                <i class="pi pi-info-circle text-warn-dark text-[20px] mt-0.5"></i>
                <div>
                    <div class="text-[13px] font-bold text-warn-dark">{{ __('Tu asesor está revisando el plan de pagos') }}</div>
                    <div class="text-[12px] text-warn-dark mt-1">{{ __('Hemos recibido tu observación y estamos preparando una nueva propuesta. Te notificaremos cuando esté lista para tu revisión.') }}</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Información del presupuesto --}}
    <div class="cli-card p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-[15px] font-bold text-ink-950">{{ __('Plan de Pagos Seleccionado') }}</div>
                <div class="text-[12px] text-ink-500">Plan {{ $reservation->payment_method }} — Configurado por tu asesor</div>
            </div>
            <span class="crm-pill bg-ok-soft text-ok-dark text-[11px]">
                <i class="pi pi-check-circle text-[10px]"></i> Enviado {{ optional($reservation->budget_sent_at)->diffForHumans() }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="border border-ink-200 rounded-xl p-4">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400">{{ __('Precio del inmueble') }}</div>
                <div class="font-display text-[20px] font-bold text-ink-950 mt-2">${{ number_format($precio, 0) }}</div>
            </div>
            <div class="border border-ink-200 rounded-xl p-4">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400">{{ __('Costos legales') }}</div>
                <div class="font-display text-[20px] font-bold text-ink-950 mt-2">${{ number_format($breakdown['costos_legales'], 0) }}</div>
            </div>
            <div class="border border-ink-200 rounded-xl p-4">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400">{{ __('Total a pagar') }}</div>
                <div class="font-display text-[20px] font-bold text-ok-dark mt-2">${{ number_format($totalConLegales, 0) }}</div>
            </div>
        </div>

        {{-- Breakdown --}}
        <div class="space-y-3">
            <div class="text-[13px] font-bold text-ink-950">{{ __('Desglose del plan') }}</div>

            <div class="border border-ink-200 rounded-xl overflow-hidden">
                <table class="w-full">
                    <thead class="bg-ink-50">
                        <tr>
                            <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Concepto') }}</th>
                            <th class="text-center px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Porcentaje') }}</th>
                            <th class="text-right px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Monto') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        <tr>
                            <td class="px-4 py-3.5 text-[13px] text-ink-700">{{ __('Pago Inicial') }}</td>
                            <td class="px-4 py-3.5 text-center text-[13px] text-ink-700">{{ $breakdown['porcentaje_inicial'] }}%</td>
                            <td class="px-4 py-3.5 text-right text-[13px] font-semibold text-ink-950">${{ number_format($breakdown['pago_inicial'], 0) }}</td>
                        </tr>
                        @if($breakdown['pago_construccion'] > 0)
                        <tr>
                            <td class="px-4 py-3.5 text-[13px] text-ink-700">
                                Construcción
                                @if($breakdown['cantidad_cuotas'] > 0)
                                    <span class="text-[11px] text-ink-500"> ({{ $breakdown['cantidad_cuotas'] }} cuotas de ${{ number_format($breakdown['cuota'], 0) }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center text-[13px] text-ink-700">{{ $breakdown['porcentaje_construccion'] }}%</td>
                            <td class="px-4 py-3.5 text-right text-[13px] font-semibold text-ink-950">${{ number_format($breakdown['pago_construccion'], 0) }}</td>
                        </tr>
                        @endif
                        @if($breakdown['pago_entrega'] > 0)
                        <tr>
                            <td class="px-4 py-3.5 text-[13px] text-ink-700">{{ __('Pago a la Entrega') }}</td>
                            <td class="px-4 py-3.5 text-center text-[13px] text-ink-700">{{ $breakdown['porcentaje_entrega'] }}%</td>
                            <td class="px-4 py-3.5 text-right text-[13px] font-semibold text-ink-950">${{ number_format($breakdown['pago_entrega'], 0) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        @if($reservation->budget_notes)
        <div class="px-4 py-3 rounded-xl bg-ink-50 border border-ink-200">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-ink-500 mb-1">{{ __('Nota del asesor') }}</div>
            <div class="text-[13px] text-ink-700">{{ $reservation->budget_notes }}</div>
        </div>
        @endif

        {{-- Observations conversation --}}
        @php
            $observations = $reservation->budget_observations ?? [];
        @endphp
        @if(!empty($observations))
        <div class="px-4 py-3 rounded-xl bg-warn-soft/30 border border-warn/20 space-y-3">
            <div class="text-[11px] uppercase tracking-wide font-semibold text-warn-dark">{{ __('Conversación con tu asesor') }}</div>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @foreach($observations as $o)
                    @php $fromAdmin = ($o['from'] ?? '') === 'admin'; @endphp
                    <div class="flex {{ $fromAdmin ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-xl px-3 py-2 text-[12px] {{ $fromAdmin ? 'bg-brand text-white' : 'bg-white border border-ink-200 text-ink-800' }}">
                            <div class="text-[10px] uppercase tracking-wide opacity-70 mb-1">{{ $o['author'] ?? ($fromAdmin ? 'Asesor' : 'Tú') }} · {{ \Carbon\Carbon::parse($o['at'] ?? now())->diffForHumans() }}</div>
                            <div>{{ $o['message'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Send observation form --}}
        <div class="px-4 py-3 rounded-xl bg-ink-50 border border-ink-200">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-ink-500 mb-2">{{ __('¿Tienes alguna observación?') }}</div>
            <form id="observation-form" onsubmit="return sendObservation(event)" class="space-y-3">
                @csrf
                <textarea name="message" rows="3" class="w-full rounded-lg border border-ink-300 px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-none" placeholder="{{ __('Escribe tu observación sobre el plan de pagos...') }}"></textarea>
                <div class="flex items-center gap-2">
                    <button type="submit" id="observation-btn" class="cli-btn cli-btn-ghost text-[12px]">
                        <i class="pi pi-send"></i> Enviar observación
                    </button>
                    <span class="text-[11px] text-ink-500">{{ __('Tu asesor revisará y responderá') }}</span>
                </div>
            </form>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-4 border-t border-ink-200">
            <form id="accept-budget-form" onsubmit="return acceptBudget(event)" class="w-full">
                @csrf
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" id="terms-accept" required class="w-4 h-4 mt-0.5 accent-brand">
                        <span class="text-[12px] text-ink-600">
                            Acepto los términos del presupuesto y el plan de pagos detallado arriba.
                            Esto constituye mi compromiso de compra para la unidad {{ $unidad }}.
                        </span>
                    </label>
                    <button type="submit" id="accept-btn" class="cli-btn cli-btn-primary shrink-0">
                        <i class="pi pi-check"></i> Aceptar Presupuesto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
async function sendObservation(e) {
    e.preventDefault();
    const form = document.getElementById('observation-form');
    const btn = document.getElementById('observation-btn');
    const textarea = form.querySelector('textarea');
    const message = textarea.value.trim();

    if (!message) {
        alert('{{ __("Por favor escribe tu observación.") }}');
        return false;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="pi pi-spin pi-spinner"></i> Enviando…';

    try {
        const res = await fetch('{{ route("dashboard.budget.observation", $reservation) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message }),
        });
        const data = await res.json();
        if (data.success) {
            btn.innerHTML = '<i class="pi pi-check"></i> ¡Enviado!';
            setTimeout(() => { window.location.reload(); }, 1000);
        } else {
            alert(data.message || 'Error al enviar observación.');
            btn.disabled = false;
            btn.innerHTML = '<i class="pi pi-send"></i> Enviar observación';
        }
    } catch (err) {
        alert('{{ __("Error de red. Intenta de nuevo.") }}');
        btn.disabled = false;
        btn.innerHTML = '<i class="pi pi-send"></i> Enviar observación';
    }
    return false;
}

async function acceptBudget(e) {
    e.preventDefault();
    const btn = document.getElementById('accept-btn');
    const cb = document.getElementById('terms-accept');
    
    if (!cb.checked) {
        alert('{{ __("Debes aceptar los términos para continuar.") }}');
        return false;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="pi pi-spin pi-spinner"></i> Procesando…';

    try {
        const res = await fetch('{{ route("dashboard.budget.accept", $reservation) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        if (data.success) {
            btn.innerHTML = '<i class="pi pi-check-circle"></i> ¡Aceptado!';
            btn.className = 'cli-btn bg-ok text-white shrink-0';
            setTimeout(() => { window.location.href = data.redirect || '{{ route("dashboard") }}'; }, 1500);
        } else {
            alert(data.message || 'Error al aceptar presupuesto.');
            btn.disabled = false;
            btn.innerHTML = '<i class="pi pi-check"></i> Aceptar Presupuesto';
        }
    } catch (err) {
        alert('{{ __("Error de red. Intenta de nuevo.") }}');
        btn.disabled = false;
        btn.innerHTML = '<i class="pi pi-check"></i> Aceptar Presupuesto';
    }
    return false;
}
</script>
@endsection