@extends('layouts.main_user')

@section('content')
<div class="flex-1 bg-gray-50 p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Plan de Pagos') }}</h1>
        <p class="text-gray-600">{{ __('Consulta tu calendario de pagos y montos') }}</p>
    </div>

    <!-- Payments Section -->
    @if($reservations->count() > 0)
        @foreach($reservations as $reservation)
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $reservation->unit_name ?? 'Unit ' . $reservation->unit_id }}</h3>
                        <p class="text-sm text-gray-600">Código: {{ $reservation->reservation_code }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-semibold text-gray-900">{{ $reservation->formatted_price }}</div>
                        @if($reservation->status == 'pending')
                            <span class="px-3 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full font-medium">{{ __('Pendiente') }}</span>
                        @elseif($reservation->status == 'confirmed')
                            <span class="px-3 py-1 text-xs bg-green-100 text-green-800 rounded-full font-medium">{{ __('Confirmada') }}</span>
                        @else
                            <span class="px-3 py-1 text-xs bg-gray-100 text-gray-600 rounded-full font-medium">{{ __('Cancelada') }}</span>
                        @endif
                    </div>
                </div>

                @if(($reservation->budget_status ?? 'pending') !== 'sent')
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                        <svg class="material-design-icon__svg text-blue-500 mx-auto mb-3" width="40" height="40" viewBox="0 0 24 24">
                            <path d="M12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22C6.47,22 2,17.5 2,12A10,10 0 0,1 12,2M12.5,7V12.25L17,14.92L16.25,16.15L11,13V7H12.5Z"></path>
                        </svg>
                        <h4 class="font-semibold text-blue-900 mb-1">{{ __('Tu presupuesto se está preparando') }}</h4>
                        <p class="text-sm text-blue-800">
                            Nuestro equipo está armando el plan de pagos para tu unidad.
                            En cuanto esté listo, lo verás acá con todas las cuotas y vencimientos.
                        </p>
                    </div>
                </div>
                @continue
                @endif

                <!-- Payment Plan Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-blue-900 mb-1">{{ __('Plan de Pagos Seleccionado') }}</h4>
                            <div class="text-sm text-blue-800">
                                @php
                                    $paymentMethod = $reservation->payment_method ?? 'A';
                                    if ($paymentMethod == 'A') {
                                        echo 'Plan A: 80% al firmar + $500 legales, 20% al entregar';
                                    } elseif ($paymentMethod == 'B') {
                                        echo 'Plan B: 60% al firmar + $500 legales, 40% al entregar';
                                    } elseif ($paymentMethod == 'C') {
                                        echo 'Plan C: 25% al firmar + $500 legales, 35% en cuotas, 40% al entregar';
                                    } else {
                                        echo 'Plan Personalizado: Configuración flexible';
                                    }
                                @endphp
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-blue-900">{{ $paymentMethod }}</div>
                            <div class="text-xs text-blue-600">{{ __('Plan seleccionado') }}</div>
                        </div>
                    </div>
                </div>

                @php
                    // Verificar si los documentos están firmados
                    $contractSigned = \App\Services\DocumentService::allDocumentsSigned($reservation);
                    $breakdown = App\Helpers\PaymentPlanHelper::calculatePaymentBreakdown($reservation);
                    $totalPrice = $breakdown['total_sin_legales'];
                    
                    if ($contractSigned) {
                        // Usar pagos reales de la base de datos
                        $payments = $reservation->payments()->orderBy('due_date')->get();
                    } else {
                        // Mostrar vista previa sin botones de pago
                        $payments = collect([]);
                        
                        // Pago inicial
                        $payments[] = (object)[
                            'label' => 'Pago Inicial (' . $breakdown['porcentaje_inicial'] . '% + $' . number_format($breakdown['costos_legales'], 0) . ' legales)',
                            'amount' => $breakdown['pago_inicial'],
                            'due_date' => $reservation->created_at,
                            'status' => 'pending',
                            'payment_type' => 'initial',
                            'formatted_amount' => '$' . number_format($breakdown['pago_inicial'], 2, '.', ','),
                            'receipt_path' => null,
                            'status_label' => '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ __('Pendiente') }}</span>'
                        ];
                        
                        // Pagos de construcción si hay
                        if ($breakdown['cantidad_cuotas'] > 0) {
                            $fechaInicio = new DateTime();
                            for ($i = 1; $i <= $breakdown['cantidad_cuotas']; $i++) {
                                $fecha = clone $fechaInicio;
                                $fecha->add(new DateInterval('P' . $i . 'M'));
                                
                                $payments[] = (object)[
                                    'label' => 'Cuota ' . $i . ' de ' . $breakdown['cantidad_cuotas'],
                                    'amount' => $breakdown['cuota'],
                                    'due_date' => $fecha,
                                    'status' => 'future',
                                    'payment_type' => 'installment',
                                    'installment_number' => $i,
                                    'formatted_amount' => '$' . number_format($breakdown['cuota'], 2, '.', ','),
                                    'receipt_path' => null,
                                    'status_label' => '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ __('Pendiente') }}</span>'
                                ];
                            }
                        } elseif ($breakdown['pago_construccion'] > 0) {
                            $payments[] = (object)[
                                'label' => 'Pago en Construcción (' . $breakdown['porcentaje_construccion'] . '%)',
                                'amount' => $breakdown['pago_construccion'],
                                'due_date' => $reservation->created_at->addMonths(6),
                                'status' => 'future',
                                'payment_type' => 'construction',
                                'formatted_amount' => '$' . number_format($breakdown['pago_construccion'], 2, '.', ','),
                                'receipt_path' => null,
                                'status_label' => '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ __('Pendiente') }}</span>'
                            ];
                        }
                        
                        // Pago de entrega si hay
                        if ($breakdown['pago_entrega'] > 0) {
                            $payments[] = (object)[
                                'label' => 'Pago en Entrega (' . $breakdown['porcentaje_entrega'] . '%)',
                                'amount' => $breakdown['pago_entrega'],
                                'due_date' => $reservation->created_at->addYear(),
                                'status' => 'future',
                                'payment_type' => 'delivery',
                                'formatted_amount' => '$' . number_format($breakdown['pago_entrega'], 2, '.', ','),
                                'receipt_path' => null,
                                'status_label' => '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ __('Pendiente') }}</span>'
                            ];
                        }
                    }
                @endphp

                @if(!$contractSigned)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <svg class="material-design-icon__svg text-yellow-600 mr-3" width="20" height="20" viewBox="0 0 24 24">
                                <path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M11,16.5L6.5,12L7.91,10.59L11,13.67L16.59,8.09L18,9.5L11,16.5Z"></path>
                            </svg>
                            <div>
                                <div class="font-medium text-yellow-800">{{ __('Documentos pendientes de firma') }}</div>
                                <div class="text-sm text-yellow-700">{{ __('Los botones de pago se habilitarán después de firmar el Plan de Pagos y la Promesa de Compraventa') }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-3">
                    @foreach($payments as $payment)
                <div class="p-4 border rounded-lg @if($payment->status == 'paid') border-green-200 bg-green-50 @elseif($payment->status == 'pending') border-blue-200 bg-blue-50 @else border-gray-200 bg-gray-50 @endif">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center
                                @if($payment->status == 'paid') bg-green-100 text-green-600
                                @elseif($payment->status == 'pending') bg-yellow-100 text-yellow-600
                                @else bg-gray-100 text-gray-600 @endif">
                                @if($payment->status == 'paid')
                                    <svg class="material-design-icon__svg" width="16" height="16" viewBox="0 0 24 24">
                                        <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"></path>
                                    </svg>
                                @elseif($payment->status == 'pending')
                                    <svg class="material-design-icon__svg" width="16" height="16" viewBox="0 0 24 24">
                                        <path d="M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4M20,18H4V12H20V18M20,8H4V6H20V8Z"></path>
                                    </svg>
                                @else
                                    <svg class="material-design-icon__svg" width="16" height="16" viewBox="0 0 24 24">
                                        <path d="M12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22C6.47,22 2,17.5 2,12A10,10 0 0,1 12,2Z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $payment->label }}</div>
                                <div class="text-sm text-gray-600">Vencimiento: {{ $payment->due_date->format('d M Y') }}</div>
                                @if($payment->receipt_path)
                                    <div class="text-xs text-green-600">{{ __('✓ Comprobante adjunto') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">{{ $payment->formatted_amount ?? '$' . number_format($payment->amount, 2, '.', ',') }}</div>
                            {!! $payment->status_label !!}
                            
                            @if($payment->status != 'paid')
                                @if($contractSigned)
                                    <button class="mt-2 px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors" onclick="openPaymentModal('{{ $payment->id }}')">
                                        Pagar Ahora
                                    </button>
                                @else
                                    <button class="mt-2 px-3 py-1 bg-gray-400 text-white text-xs rounded cursor-not-allowed" disabled>
                                        Pagar Ahora
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                    @endforeach
                </div>

                <!-- Total Summary -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-600 mb-1">{{ __('Total Pagado/Pagar') }}</div>
                            <div class="text-lg font-bold text-gray-900">${{ number_format($breakdown['pago_inicial'] + $breakdown['pago_construccion'] + $breakdown['pago_entrega'], 2, '.', ',') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">{{ __('Precio Propiedad') }}</div>
                            <div class="text-lg font-bold text-gray-900">${{ number_format($breakdown['total_sin_legales'], 2, '.', ',') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">{{ __('Total Final') }}</div>
                            <div class="text-lg font-bold text-gray-900">${{ number_format($breakdown['total_con_legales'], 2, '.', ',') }}</div>
                        </div>
                    </div>
                </div>

                    </div>
            </div>
        @endforeach
    @else
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-center py-8">
                <svg class="material-design-icon__svg text-gray-400 mx-auto mb-4" width="48" height="48" viewBox="0 0 24 24">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No tienes reservas') }}</h3>
                <p class="text-gray-600 mb-4">{{ __('Comienza reservando una unidad desde el home') }}</p>
                <a href="/" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                    Ver Unidades
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
