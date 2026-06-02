@extends('layouts.client')
@section('title', __('Mi Propiedad').' — MAKAI')
@section('page_title', __('Mi Propiedad'))
@section('page_breadcrumb', ($reservation->unit->custom_id ?? $reservation->unit->name ?? __('Tu unidad')) . ' · Makai Residences')
@php $activeRoute = 'mi-propiedad'; @endphp

@section('content')
@php
    $unidad   = $reservation->unit->custom_id ?? $reservation->unit->name ?? __('Unidad');
    $precio   = (float) ($reservation->unit->price ?? 0);
    $pagado   = (float) ($reservation->payments?->where('status', 'paid')->sum('amount') ?? 0);
    $pct      = $precio > 0 ? round(($pagado / $precio) * 100) : 0;

    $tipoBeds = $reservation->unit->bedrooms ?? 2;
    $tipoBath = $reservation->unit->bathrooms ?? 2;

    // KYC detection — consolidated 'kyc' document is created when /form is completed.
    // Also fall back to id_front/id_back uploaded at register for the same user.
    $userId = $reservation->user_id;
    $kycDoc = $reservation->documents->firstWhere('document_type', 'kyc');
    $kycSubmitted = (bool) $kycDoc || \App\Models\Document::whereIn('document_type', ['id_front', 'id_back'])
        ->where(function($q) use ($reservation, $userId) {
            $q->where('reservation_id', $reservation->id);
            if ($userId) $q->orWhere('metadata->user_id', $userId);
        })
        ->exists();
    $kycApproved = $kycDoc ? $kycDoc->status === 'approved' : false;
    $kycRejected = $kycDoc ? $kycDoc->status === 'rejected' : false;

    // Step indicator: Reserva, KYC, Promesa, Plan de Pago, Doc Pago, Contrato
    // stepCount = the step the client is CURRENTLY on (active). Earlier steps render as done.
    $promesaSigned  = $reservation->documents->where('document_type', 'purchase_promise')->where('status', 'signed')->isNotEmpty();
    $planApproved   = $reservation->documents->where('document_type', 'payment_plan')->where('status', 'approved')->isNotEmpty();
    $paymentPaid    = $reservation->payments->where('status', 'paid')->count() > 0;
    $contractSigned = in_array($reservation->status, ['contract_signed', 'signed']);

    // Sequential progression: a step only becomes active when EVERY previous step is complete.
    // The reservation deposit can be paid up-front, so a paid payment must NOT skip KYC/Promesa/Plan.
    $stepCount = 1;                                                  // Reserva
    if ($kycSubmitted)                                $stepCount = 2; // KYC (submitted, under review)
    if ($kycApproved || $reservation->isBudgetSent()) $stepCount = 3; // Promesa
    if ($stepCount >= 3 && $promesaSigned)            $stepCount = 4; // Plan de pago
    if ($stepCount >= 4 && $planApproved)             $stepCount = 5; // Doc. de pago
    if ($stepCount >= 5 && $paymentPaid)              $stepCount = 6; // Contrato
    if ($contractSigned)                              $stepCount = 6;

    $steps = [
        [__('Reserva'),      'check-circle'],
        [__('KYC'),          'id-card'],
        [__('Promesa'),      'file-edit'],
        [__('Plan de pago'), 'calculator'],
        [__('Doc. de pago'), 'credit-card'],
        [__('Contrato'),     'file'],
    ];

    $nextPay  = $reservation->payments->where('status', 'pending')->sortBy('due_date')->first();
    $advisor  = \App\Models\Agent::where('active', true)->orderBy('id')->first();
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    @php $contractSigned = in_array($reservation->status, ['contract_signed', 'signed']); @endphp

    {{-- Alert: presupuesto enviado — pending client acceptance --}}
    @if($reservation->isBudgetSent() && !$contractSigned)
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-ok-soft border border-ok/30 text-[13px] text-ink-700">
            <i class="pi pi-file-text text-ok"></i>
            <span><span class="font-bold text-ink-950">{{ __('Presupuesto disponible') }}</span> — {{ __('Tu asesor ha enviado el presupuesto. Revísalo y acéptalo para continuar con tu compra.') }}</span>
            <a href="{{ route('dashboard.budget', $reservation) }}" class="ml-auto text-brand font-semibold hover:underline flex items-center gap-1">{{ __('Ver presupuesto') }} <i class="pi pi-arrow-right text-[10px]"></i></a>
        </div>
    @endif

    {{-- Alert: KYC action required (only while not submitted) / KYC in review (while pending) --}}
    @if(! $kycSubmitted)
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-err-soft border border-err/20 text-[13px] text-ink-700">
            <i class="pi pi-exclamation-circle text-err"></i>
            <span><span class="font-bold text-ink-950">{{ __('Acción requerida: KYC') }}</span> — {{ __('Necesitamos verificar tus documentos de identidad para continuar con tu expediente.') }}</span>
            <a href="{{ route('dashboard.documents') }}" class="ml-auto text-brand font-semibold hover:underline flex items-center gap-1">{{ __('Ver documentos') }} <i class="pi pi-arrow-right text-[10px]"></i></a>
        </div>
    @elseif($kycSubmitted && ! $kycApproved && ! $kycRejected)
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-warn-soft border border-warn/30 text-[13px] text-ink-700">
            <i class="pi pi-clock text-warn"></i>
            <span><span class="font-bold text-ink-950">{{ __('KYC en revisión') }}</span> — {{ __('Tu documentación está siendo verificada por nuestro equipo. Te avisaremos cuando esté aprobada.') }}</span>
        </div>
    @elseif($kycRejected)
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-err-soft border border-err/20 text-[13px] text-ink-700">
            <i class="pi pi-exclamation-circle text-err"></i>
            <span><span class="font-bold text-ink-950">{{ __('KYC rechazado') }}</span> — {{ __('Tu documentación fue rechazada. Por favor contacta a tu asesor para más detalles.') }}</span>
        </div>
    @endif

    {{-- Hero card with property summary --}}
    <div class="cli-card overflow-hidden relative">
        <div class="p-7 text-white relative overflow-hidden" style="background:linear-gradient(135deg,#5c7c68 0%, #3f5848 100%)">
            {{-- Decorative concentric circles top-right --}}
            <div class="absolute -top-20 -right-32 w-[440px] h-[440px] pointer-events-none opacity-25" style="background:
                radial-gradient(circle at center, transparent 47%, rgba(255,255,255,.5) 47.5%, rgba(255,255,255,.5) 48.5%, transparent 49%),
                radial-gradient(circle at center, transparent 35%, rgba(255,255,255,.45) 35.5%, rgba(255,255,255,.45) 36.5%, transparent 37%),
                radial-gradient(circle at center, transparent 22%, rgba(255,255,255,.4) 22.5%, rgba(255,255,255,.4) 23.5%, transparent 24%),
                radial-gradient(circle at center, transparent 9%, rgba(255,255,255,.35) 9.5%, rgba(255,255,255,.35) 11%, transparent 11.5%);"></div>

            <div class="relative z-10">
                <div class="text-[11px] uppercase tracking-[0.18em] font-semibold opacity-80">{{ __('Tu propiedad') }}</div>
                <div class="font-display text-[48px] font-medium leading-tight mt-1">{{ $unidad }}</div>
                <div class="text-[13px] opacity-80 mt-1">Makai Residences · Cap Cana, Punta Cana</div>

                {{-- Quick stats pills --}}
                <div class="mt-6 inline-flex items-stretch rounded-2xl bg-white/10 backdrop-blur border border-white/15 overflow-hidden">
                    <div class="px-5 py-3 border-r border-white/15">
                        <div class="text-[10px] uppercase tracking-wider opacity-70">{{ __('Precio total') }}</div>
                        <div class="font-display text-[16px] font-semibold mt-1">${{ number_format($precio, 0) }} USD</div>
                    </div>
                    <div class="px-5 py-3 border-r border-white/15">
                        <div class="text-[10px] uppercase tracking-wider opacity-70">{{ __('Pagado') }}</div>
                        <div class="font-display text-[16px] font-semibold mt-1">${{ number_format($pagado, 0) }} USD</div>
                    </div>
                    <div class="px-5 py-3 border-r border-white/15">
                        <div class="text-[10px] uppercase tracking-wider opacity-70">{{ __('Avance compra') }}</div>
                        <div class="font-display text-[16px] font-semibold mt-1">{{ $pct }}%</div>
                    </div>
                    <div class="px-5 py-3">
                        <div class="text-[10px] uppercase tracking-wider opacity-70">{{ __('Tipo') }}</div>
                        <div class="font-display text-[16px] font-semibold mt-1">{{ $tipoBeds }} {{ __('Hab') }} · {{ $tipoBath }} {{ __('Bañ') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step indicator pinned at bottom of hero --}}
        <div class="px-7 py-5 flex items-center gap-2 overflow-x-auto bg-white">
            @foreach($steps as $i => $s)
                @php
                    $n = $i + 1;
                    $done   = $n < $stepCount;
                    $active = $n === $stepCount;
                @endphp
                <div class="flex flex-col items-center gap-1.5 shrink-0">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-[12px] font-bold
                        {{ $done ? 'bg-ok text-white' : ($active ? 'bg-warn text-white' : 'bg-ink-100 text-ink-400') }}">
                        @if($done)
                            <i class="pi pi-check text-[12px]"></i>
                        @else
                            {{ $n }}
                        @endif
                    </div>
                    <div class="text-[11px] {{ $done ? 'text-ok-dark' : ($active ? 'text-warn-dark font-semibold' : 'text-ink-400') }}">{{ $s[0] }}</div>
                </div>
                @if(! $loop->last)
                    <div class="flex-1 min-w-[16px] h-px {{ $done ? 'bg-ok' : 'bg-ink-200' }} mt-[-18px]"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Bottom: Detalles + Right rail --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Detalles de tu unidad --}}
        <div class="cli-card overflow-hidden lg:col-span-2">
            <div class="px-5 py-3 bg-ink-50/60 border-b border-ink-100 text-[14px] font-bold text-ink-950">{{ __('Detalles de tu unidad') }}</div>
            <div class="divide-y divide-ink-100">
                @php
                    $details = [
                        [__('Unidad'),    $unidad],
                        [__('Piso'),      __('Planta').' '.($reservation->unit->floor ?? '1')],
                        [__('Tipo'),      ($reservation->unit->layout ?? '1 Bed + Family Room')],
                        [__('Interior'),  ($reservation->unit->internal_area ?? '959').' sqft'],
                        [__('Terraza'),   ($reservation->unit->expense_1 ?? '207').' sqft'],
                        [__('Vista'),     ($reservation->unit->outlook ?? 'Lake Facing')],
                        [__('Proyecto'),  'Makai Residences'],
                        [__('Ubicación'), 'Cap Cana, Punta Cana · RD'],
                    ];
                @endphp
                @foreach($details as $d)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <span class="text-[12px] text-ink-500">{{ $d[0] }}</span>
                        <span class="text-[13px] font-semibold text-ink-950">{{ $d[1] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Right rail --}}
        <div class="space-y-4">
            {{-- Mis documentos card --}}
            <a href="{{ route('dashboard.documents') }}" class="cli-card p-4 block hover:shadow-card transition-shadow relative">
                @if(! $kycApproved)
                    <span class="absolute top-3 right-4 cli-pill bg-err-soft text-err"><i class="pi pi-exclamation-circle text-[10px]"></i> {{ __('Acción necesaria') }}</span>
                @endif
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-file"></i></div>
                    <div class="flex-1">
                        <div class="text-[14px] font-bold text-ink-950">{{ __('Mis documentos') }}</div>
                        <div class="text-[11px] text-ink-500">{{ __('Sube y gestiona tus docs') }}</div>
                    </div>
                </div>
                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400 mt-3 pt-3 border-t border-ink-100">{{ $kycApproved ? __('KYC completado') : __('Completa KYC') }}</div>
            </a>

            {{-- Plan de pagos card --}}
            <a href="{{ route('dashboard.payments') }}" class="cli-card p-4 block hover:shadow-card transition-shadow">
                <div class="text-right text-[10px] uppercase tracking-wider font-semibold text-ink-400">{{ __('Próximo pago') }}</div>
                <div class="text-right font-display text-[22px] font-bold text-ink-950 mt-1">${{ number_format($nextPay->amount ?? 44650, 2) }}</div>
                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-ink-100">
                    <div class="w-10 h-10 rounded-lg bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-credit-card"></i></div>
                    <div class="flex-1">
                        <div class="text-[14px] font-bold text-ink-950">{{ __('Plan de pagos') }}</div>
                        <div class="text-[11px] text-ink-500">{{ __('Consulta tus cuotas') }}</div>
                    </div>
                </div>
                <div class="text-[10px] uppercase tracking-wider font-semibold text-warn-dark mt-2">{{ __('Vence') }} {{ $nextPay && $nextPay->due_date ? $nextPay->due_date->locale(app()->getLocale())->isoFormat(app()->getLocale()==='es' ? 'D MMMM YYYY' : 'MMMM D, YYYY') : '31 Mayo 2026' }}</div>
            </a>

            {{-- Asesor card --}}
            <div class="cli-card p-4">
                <div class="flex items-center gap-3">
                    <div class="cli-avatar" style="background:#5c7c68">{{ strtoupper(substr($advisor->name ?? 'CM', 0, 2)) }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[14px] font-bold text-ink-950">{{ $advisor->name ?? 'Carlos Méndez' }}</div>
                        <div class="text-[11px] text-ok-dark flex items-center gap-1"><span class="dot bg-ok"></span> {{ __('Disponible ahora') }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-ink-100">
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $advisor->phone ?? '18095550101') }}" target="_blank" class="cli-btn cli-btn-ghost text-[12px] py-2"><i class="pi pi-whatsapp"></i> WhatsApp</a>
                    <a href="{{ route('dashboard.messages') }}" class="cli-btn cli-btn-primary text-[12px] py-2"><i class="pi pi-comment text-[12px]"></i> {{ __('Chat') }}</a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
