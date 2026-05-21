{{--
    Client-side Plan de Pagos card.
    Shows the proposed payment plan (read-only), the conversation with admin,
    and two actions: "Conforme" (accept) and "Enviar observación".
--}}
@php
    $r = $reservation;
    $obs = $r->budget_observations ?? [];
    // Client accepted the plan (budget_status='approved') or contract already signed
    $accepted = $r->budget_status === 'approved' || in_array($r->status, ['contract_signed', 'signed']);
    $isSent   = $r->isBudgetSent();
    $lastObs  = ! empty($obs) ? end($obs) : null;
    // Show "waiting on admin" only when client sent a real observation (not an accept entry)
    $needsAdminAction = $lastObs
        && ($lastObs['from'] ?? '') === 'client'
        && ($lastObs['kind'] ?? null) !== 'accept'
        && ! $isSent
        && ! $accepted;
    $breakdown = \App\Helpers\PaymentPlanHelper::calculatePaymentBreakdown($r);
    $totalPrice = (float) $r->unit_price;
@endphp

<div class="cli-card overflow-hidden">
    <div class="px-5 py-3 flex items-center gap-3 bg-ok-soft/40 border-b border-ok/20">
        <div class="w-8 h-8 rounded-full bg-ok-soft border border-ok/30 flex items-center justify-center text-ok-dark"><i class="pi pi-calculator"></i></div>
        <div class="flex-1">
            <div class="text-[14px] font-bold text-ink-950">Plan de pagos propuesto</div>
            <div class="text-[12px] text-ink-500">
                @if($accepted)
                    Aceptaste este plan. Estamos preparando los siguientes pasos.
                @elseif($needsAdminAction)
                    Tu asesor está revisando tu observación.
                @elseif($isSent)
                    Revisalo, escribí tus observaciones o marcalo como conforme para continuar.
                @endif
            </div>
        </div>
        @if($isSent && ! $accepted)
            <span class="cli-pill bg-info-soft text-info">Pendiente de tu respuesta</span>
        @elseif($needsAdminAction)
            <span class="cli-pill bg-warn-soft text-warn">Revisión por asesor</span>
        @elseif($accepted)
            <span class="cli-pill bg-ok-soft text-ok">Aceptado</span>
        @endif
    </div>

    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="border border-ink-100 rounded-lg px-3 py-2.5">
            <div class="text-[10px] uppercase font-semibold text-ink-400 tracking-wide">Pago inicial</div>
            <div class="text-[16px] font-bold text-ink-950 mt-0.5">${{ number_format($breakdown['pago_inicial'] ?? 0) }}</div>
            <div class="text-[11px] text-ink-500 mt-0.5">{{ $r->payment_initial_percentage }}% + ${{ number_format((float) $r->legal_costs) }} legales</div>
        </div>
        <div class="border border-ink-100 rounded-lg px-3 py-2.5">
            <div class="text-[10px] uppercase font-semibold text-ink-400 tracking-wide">Durante construcción</div>
            <div class="text-[16px] font-bold text-ink-950 mt-0.5">${{ number_format($breakdown['pago_construccion'] ?? 0) }}</div>
            <div class="text-[11px] text-ink-500 mt-0.5">
                {{ $r->payment_construction_percentage }}%
                @if(($r->payment_installments ?? 0) > 0) · {{ $r->payment_installments }} cuotas de ${{ number_format($breakdown['cuota'] ?? 0) }} @endif
            </div>
        </div>
        <div class="border border-ink-100 rounded-lg px-3 py-2.5">
            <div class="text-[10px] uppercase font-semibold text-ink-400 tracking-wide">A la entrega</div>
            <div class="text-[16px] font-bold text-ink-950 mt-0.5">${{ number_format($breakdown['pago_entrega'] ?? 0) }}</div>
            <div class="text-[11px] text-ink-500 mt-0.5">{{ $r->payment_delivery_percentage }}%</div>
        </div>
        <div class="sm:col-span-2 lg:col-span-3 border border-ink-100 rounded-lg px-3 py-2.5 flex items-center justify-between">
            <div>
                <div class="text-[10px] uppercase font-semibold text-ink-400 tracking-wide">Total contrato</div>
                <div class="text-[18px] font-bold text-ink-950">${{ number_format($totalPrice) }}</div>
            </div>
            @if(! empty($r->budget_notes))
                <div class="max-w-[60%] text-[12px] text-ink-700">
                    <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">Notas de tu asesor</div>
                    <div class="mt-0.5">{{ $r->budget_notes }}</div>
                </div>
            @endif
        </div>
    </div>

    @if(! empty($obs))
        <div class="px-5 py-4 bg-ink-50 border-t border-ink-100">
            <div class="text-[11px] uppercase tracking-wide font-semibold text-ink-500 mb-2">Conversación con tu asesor</div>
            <div class="space-y-2 max-h-56 overflow-y-auto">
                @foreach($obs as $o)
                    @php $fromClient = ($o['from'] ?? '') === 'client'; @endphp
                    <div class="flex {{ $fromClient ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-xl px-3 py-2 text-[12px] {{ $fromClient ? 'bg-brand text-white' : 'bg-white border border-ink-200 text-ink-800' }}">
                            <div class="text-[10px] uppercase tracking-wide opacity-70 mb-1">{{ $o['author'] ?? ($fromClient ? 'Vos' : 'Asesor') }} · {{ \Carbon\Carbon::parse($o['at'] ?? now())->diffForHumans() }}</div>
                            <div>{{ $o['message'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($isSent && ! $accepted)
        <div class="px-5 py-4 border-t border-ink-100 bg-white space-y-3">
            <div class="flex flex-wrap items-center gap-2 justify-between">
                <div class="text-[12px] text-ink-700">¿Estás conforme con este plan?</div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('plan-obs-form').classList.toggle('hidden')" class="cli-btn cli-btn-ghost text-[12px]"><i class="pi pi-comments text-[10px]"></i> Enviar observación</button>
                    <form method="POST" action="{{ route('dashboard.budget.accept', $r) }}" class="m-0" onsubmit="event.preventDefault(); acceptPlan(this);">
                        @csrf
                        <button type="submit" class="cli-btn cli-btn-primary text-[12px]"><i class="pi pi-check text-[10px]"></i> Marcar como conforme</button>
                    </form>
                </div>
            </div>

            <form id="plan-obs-form" method="POST" action="{{ route('dashboard.budget.observation', $r) }}" class="hidden space-y-2 m-0">
                @csrf
                <textarea name="message" rows="3" required maxlength="2000" placeholder="Contale a tu asesor qué te gustaría ajustar (ej. más cuotas, menor inicial, etc.)…" class="cli-input w-full pl-3 pt-2 h-auto resize-none"></textarea>
                <div class="flex items-center gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('plan-obs-form').classList.add('hidden')" class="cli-btn cli-btn-ghost text-[12px]">Cancelar</button>
                    <button type="submit" class="cli-btn cli-btn-primary text-[12px]"><i class="pi pi-send text-[10px]"></i> Enviar observación</button>
                </div>
            </form>
        </div>
    @endif
</div>

@once
    @push('scripts')
    <script>
    function acceptPlan(form) {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) window.location.href = data.redirect || '/dashboard';
            else alert(data.message || 'No se pudo aceptar el plan.');
        })
        .catch(() => alert('Error al aceptar el plan. Intentá de nuevo.'));
    }
    </script>
    @endpush
@endonce
