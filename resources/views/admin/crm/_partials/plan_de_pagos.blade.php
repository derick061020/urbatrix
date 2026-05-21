{{--
    Admin-side Plan de Pagos card — placed in the Documentos tab.
    Lets the admin configure the plan, save as draft, send to client, and reply to client observations.
--}}
@php
    $r = $reservation;
    $observations = $r->budget_observations ?? [];
    $config = \App\Helpers\PaymentPlanHelper::getPlanConfiguration($r->payment_method ?? 'A');
    // Pull existing reservation values or fall back to plan defaults
    $initial      = $r->payment_initial_percentage      ?? $config['payment_initial_percentage'];
    $construction = $r->payment_construction_percentage ?? $config['payment_construction_percentage'];
    $delivery     = $r->payment_delivery_percentage     ?? $config['payment_delivery_percentage'];
    $installments = $r->payment_installments            ?? $config['payment_installments'];
    $legal        = $r->legal_costs                     ?? $config['legal_costs'];
    $isSent       = $r->isBudgetSent();
    // "Aceptado" covers both states: client clicked Conforme (budget_status='approved')
    // OR the contract is fully signed (status='contract_signed'/'signed').
    $isAccepted   = $r->budget_status === 'approved' || in_array($r->status, ['contract_signed', 'signed']);
    // The form is locked once the client accepted — admin can no longer edit.
    $isLocked     = $isAccepted;
    // Distinguish a real pending observation from a stale acceptance entry.
    $lastObs      = ! empty($observations) ? end($observations) : null;
    $awaitingAdmin = $lastObs && ($lastObs['from'] ?? '') === 'client' && ($lastObs['kind'] ?? null) !== 'accept';

    if ($isAccepted) {
        $stateLabel = ['Aceptado por el cliente', 'ok'];
    } elseif ($isSent && $awaitingAdmin) {
        $stateLabel = ['Cliente envió observación', 'warn'];
    } elseif ($isSent) {
        $stateLabel = ['Enviado · esperando respuesta del cliente', 'info'];
    } elseif ($awaitingAdmin) {
        $stateLabel = ['Cliente envió observación', 'warn'];
    } else {
        $stateLabel = ['Borrador', 'ink-500'];
    }
@endphp

<div class="crm-card overflow-hidden">
    <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-3">
        <i class="pi pi-calculator text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">Plan de pagos</div>
        <span class="crm-pill bg-{{ $stateLabel[1] }}-soft text-{{ $stateLabel[1] }} ml-2">{{ $stateLabel[0] }}</span>
        <span class="ml-auto text-[11px] text-ink-500">
            Total contrato: <b class="text-ink-900">${{ number_format((float) $r->unit_price, 0) }}</b>
        </span>
    </div>

    @if(! empty($observations))
        <div class="px-5 py-4 bg-warn-soft/30 border-b border-warn/20 space-y-2">
            <div class="text-[11px] uppercase tracking-wide font-semibold text-warn-dark">Conversación con el cliente</div>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @foreach($observations as $o)
                    @php
                        $fromAdmin = ($o['from'] ?? '') === 'admin';
                        $isAcceptEntry = ($o['kind'] ?? null) === 'accept';
                    @endphp
                    <div class="flex {{ $fromAdmin ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-xl px-3 py-2 text-[12px] {{ $isAcceptEntry ? 'bg-ok-soft border border-ok/30 text-ok-dark' : ($fromAdmin ? 'bg-brand text-white' : 'bg-white border border-ink-200 text-ink-800') }}">
                            <div class="text-[10px] uppercase tracking-wide opacity-70 mb-1 flex items-center gap-1">
                                @if($isAcceptEntry)<i class="pi pi-check-circle text-[10px]"></i>@endif
                                {{ $o['author'] ?? ($fromAdmin ? 'Asesor' : 'Cliente') }} · {{ \Carbon\Carbon::parse($o['at'] ?? now())->diffForHumans() }}
                                @if($isAcceptEntry)<span class="font-bold ml-1">Aceptación</span>@endif
                            </div>
                            <div>{{ $o['message'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.crm.budget.save', $r->id) }}" class="p-5 space-y-4 m-0">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Plan base</label>
                <select name="payment_method" class="crm-input pl-3 mt-1" {{ $isLocked ? 'disabled' : '' }}>
                    @foreach(['A' => 'Plan A — 30/40/30', 'B' => 'Plan B — 40/30/30 + cuotas', 'C' => 'Plan C — 50/20/30 + cuotas', 'custom' => 'Personalizado'] as $val => $label)
                        <option value="{{ $val }}" {{ ($r->payment_method ?? 'A') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">% Inicial</label>
                <div class="relative mt-1">
                    <input type="number" step="0.01" min="0" max="100" name="payment_initial_percentage" value="{{ $initial }}" {{ $isLocked ? 'disabled' : '' }} class="crm-input pl-3 pr-7">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-400">%</span>
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">% Construcción</label>
                <div class="relative mt-1">
                    <input type="number" step="0.01" min="0" max="100" name="payment_construction_percentage" value="{{ $construction }}" {{ $isLocked ? 'disabled' : '' }} class="crm-input pl-3 pr-7">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-400">%</span>
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">% Entrega</label>
                <div class="relative mt-1">
                    <input type="number" step="0.01" min="0" max="100" name="payment_delivery_percentage" value="{{ $delivery }}" {{ $isLocked ? 'disabled' : '' }} class="crm-input pl-3 pr-7">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-400">%</span>
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Cuotas (construcción)</label>
                <input type="number" min="0" max="120" name="payment_installments" value="{{ $installments }}" {{ $isLocked ? 'disabled' : '' }} class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Costos legales</label>
                <div class="relative mt-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                    <input type="number" step="0.01" min="0" name="legal_costs" value="{{ $legal }}" {{ $isLocked ? 'disabled' : '' }} class="crm-input pl-7">
                </div>
            </div>
        </div>

        <div>
            <label class="text-[12px] font-semibold text-ink-700">Notas (visible al cliente)</label>
            <textarea name="budget_notes" rows="2" {{ $isLocked ? 'disabled' : '' }} class="crm-input pl-3 pt-2 mt-1 h-auto resize-none" placeholder="Detalles, vencimientos, condiciones especiales…">{{ $r->budget_notes }}</textarea>
        </div>

        @if(! empty($observations) && ! $isLocked)
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Respuesta al cliente</label>
                <textarea name="admin_reply" rows="2" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none" placeholder="Tu respuesta a la última observación del cliente (opcional)…"></textarea>
            </div>
        @endif

        @if(! $isLocked)
            <div class="flex flex-wrap items-center gap-2 justify-end pt-2 border-t border-ink-100">
                <button type="submit" name="action" value="save" class="crm-btn crm-btn-ghost"><i class="pi pi-save"></i> Guardar borrador</button>
                <button type="submit" name="action" value="send" class="crm-btn crm-btn-primary"><i class="pi pi-send"></i> {{ $isSent ? 'Reenviar al cliente' : 'Enviar al cliente' }}</button>
                @if($isSent)
                    <form method="POST" action="{{ route('admin.crm.budget.revert', $r->id) }}" class="m-0" onclick="event.stopPropagation();">
                        @csrf
                        <button type="submit" class="crm-btn crm-btn-ghost text-err" onclick="return confirm('¿Revertir a borrador? El cliente dejará de verlo.');"><i class="pi pi-undo"></i> Revertir</button>
                    </form>
                @endif
            </div>
        @else
            <div class="text-[12px] text-ok-dark flex items-center gap-2 pt-2 border-t border-ink-100">
                <i class="pi pi-check-circle"></i>
                <span>Plan aceptado por el cliente. La pestaña "Plan de Pagos" ya está habilitada.</span>
            </div>
        @endif
    </form>
</div>
