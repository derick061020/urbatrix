@extends('layouts.main_user')

@section('content')
<div class="flex-1 bg-gray-50 p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Progreso de tu Reserva') }}</h1>
        <p class="text-gray-600">{{ __('Sigue el estado de cada paso en el proceso') }}</p>
    </div>

    <!-- Progress Steps -->
    @if($reservations->count() > 0)
        @foreach($reservations as $reservation)
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $reservation->unit_name ?? 'Unit ' . $reservation->unit_id }}</h3>
                        <p class="text-sm text-gray-600">Código: {{ $reservation->reservation_code }}</p>
                    </div>
                    <div class="text-right">
                        @if($reservation->status == 'pending')
                            <span class="px-3 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full font-medium">{{ __('Pendiente') }}</span>
                        @elseif($reservation->status == 'confirmed')
                            <span class="px-3 py-1 text-xs bg-green-100 text-green-800 rounded-full font-medium">{{ __('Confirmada') }}</span>
                        @else
                            <span class="px-3 py-1 text-xs bg-gray-100 text-gray-600 rounded-full font-medium">{{ __('Cancelada') }}</span>
                        @endif
                    </div>
                </div>

                <div class="space-y-3">
                    @php
                        // Determinar pasos basados en información real de la base de datos
                        $steps = [];
                        
                        // Paso 1: Reserva Inicial (siempre completado)
                        $steps[] = [
                            'id' => 1, 
                            'label' => 'Reserva Inicial', 
                            'sublabel' => 'Unidad reservada', 
                            'status' => 'done', 
                            'date' => $reservation->created_at->format('d M Y'), 
                            'auto' => false,
                            'description' => 'Has reservado la unidad ' . ($reservation->unit_name ?? 'Unit ' . $reservation->unit_id) . ' con código ' . $reservation->reservation_code
                        ];
                        
                        // Paso 2: Formulario KYC Completado
                        $hasKycComplete = $reservation->first_name && $reservation->last_name && 
                                       $reservation->nationality && $reservation->marital_status && 
                                       $reservation->profession && $reservation->economic_dependent &&
                                       $reservation->address && $reservation->document_number;
                        $steps[] = [
                            'id' => 2, 
                            'label' => 'Formulario KYC', 
                            'sublabel' => 'Datos personales', 
                            'status' => $hasKycComplete ? 'done' : 'pending', 
                            'date' => $hasKycComplete ? $reservation->updated_at->format('d M Y') : null, 
                            'auto' => false,
                            'description' => $hasKycComplete ? 'Todos tus datos personales están completos' : 'Completa el formulario con tus datos personales'
                        ];
                        
                        // Paso 3: Documento de Identidad
                        $steps[] = [
                            'id' => 3, 
                            'label' => 'Documento de Identidad', 
                            'sublabel' => 'Identificación oficial', 
                            'status' => $reservation->id_document_path ? 'done' : 'pending', 
                            'date' => $reservation->id_document_path ? $reservation->updated_at->format('d M Y') : null, 
                            'auto' => false,
                            'description' => $reservation->id_document_path ? 'Documento de identidad subido exitosamente' : 'Sube tu documento de identidad (DNI, pasaporte, etc.)'
                        ];
                        
                        // Paso 4: Plan de Pagos (presupuesto enviado por el equipo)
                        $budgetSent = ($reservation->budget_status ?? 'pending') === 'sent';
                        $steps[] = [
                            'id' => 4,
                            'label' => 'Plan de Pagos',
                            'sublabel' => 'Presupuesto preparado por nuestro equipo',
                            'status' => $budgetSent ? 'done' : 'pending',
                            'date' => $budgetSent && $reservation->budget_sent_at ? $reservation->budget_sent_at->format('d M Y') : null,
                            'auto' => true,
                            'description' => $budgetSent
                                ? 'Plan ' . ($reservation->payment_method ?? '') . ' enviado. Revísalo en la sección Pagos.'
                                : 'Nuestro equipo está armando tu presupuesto. Lo verás aquí cuando esté listo.'
                        ];
                        
                        // Paso 5: Promesa de Compraventa
                        $isContractGenerated = $reservation->status == 'confirmed' || $reservation->status == 'approved';
                        $steps[] = [
                            'id' => 5, 
                            'label' => 'Promesa de Compraventa', 
                            'sublabel' => 'Contrato legal', 
                            'status' => $reservation->status == 'approved' ? 'done' : ($isContractGenerated ? 'review' : 'pending'), 
                            'date' => $isContractGenerated ? $reservation->updated_at->format('d M Y') : null, 
                            'auto' => true,
                            'description' => $reservation->status == 'approved' ? 'Contrato aprobado y listo para firmar' : ($isContractGenerated ? 'Contrato en revisión legal' : 'Genera tu contrato de compraventa')
                        ];
                        
                        // Paso 6: Firma Digital (solo si está aprobado)
                        if ($reservation->status == 'approved') {
                            $steps[] = [
                                'id' => 6, 
                                'label' => 'Firma Digital', 
                                'sublabel' => 'Firma electrónica', 
                                'status' => 'pending', 
                                'date' => null, 
                                'auto' => false,
                                'description' => 'Firma digitalmente tu contrato para finalizar el proceso'
                            ];
                        }
                    @endphp
                    
                    <!-- Current Step Info -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 mb-6 border border-blue-200">
                        @php
                            // Determinar paso actual
                            $currentStep = null;
                            $nextStep = null;
                            $progressPercentage = 0;
                            
                            foreach($steps as $index => $step) {
                                if ($step['status'] == 'pending' && !$currentStep) {
                                    $currentStep = $step;
                                    $nextStep = $steps[$index + 1] ?? null;
                                    break;
                                } elseif ($step['status'] == 'review') {
                                    $currentStep = $step;
                                    $nextStep = $steps[$index + 1] ?? null;
                                    break;
                                }
                            }
                            
                            if (!$currentStep) {
                                // Buscar el primer paso pendiente
                                foreach($steps as $step) {
                                    if ($step['status'] != 'done') {
                                        $currentStep = $step;
                                        break;
                                    }
                                }
                            }
                            
                            // Calcular progreso
                            $completedSteps = 0;
                            $totalSteps = count($steps);
                            foreach($steps as $step) {
                                if ($step['status'] == 'done') {
                                    $completedSteps++;
                                }
                            }
                            $progressPercentage = round(($completedSteps / $totalSteps) * 100);
                        @endphp
                        
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Paso Actual: {{ $currentStep['label'] ?? 'Proceso Completado' }}</h3>
                                <p class="text-sm text-gray-600">{{ $currentStep['description'] ?? '¡Felicidades! Has completado todos los pasos' }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-blue-600">{{ $progressPercentage }}%</div>
                                <div class="text-xs text-gray-500">{{ __('Completado') }}</div>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercentage }}%"></div>
                        </div>
                        
                        @if($nextStep)
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="material-design-icon__svg w-4 h-4 mr-2" viewBox="0 0 24 24">
                                    <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"></path>
                                </svg>
                                Siguiente paso: {{ $nextStep['label'] }}
                            </div>
                        @endif
                    </div>

                    @foreach($steps as $step)
                <div class="flex items-center justify-between p-4 border rounded-lg @if($step['status'] == 'done') border-green-200 bg-green-50 @elseif($step['status'] == 'review') border-yellow-200 bg-yellow-50 @elseif($step['status'] == 'locked') border-gray-200 bg-gray-50 @else border-blue-200 bg-blue-50 @endif">
                    <div class="flex items-center space-x-4">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm border-2
                            @if($step['status'] == 'done') bg-green-500 border-green-500 text-white
                            @elseif($step['status'] == 'pending') bg-yellow-500 border-yellow-500 text-white
                            @elseif($step['status'] == 'review') bg-blue-500 border-blue-500 text-white
                            @else bg-gray-300 border-gray-300 text-gray-600 @endif">
                            @if($step['status'] == 'done') ✓
                            @else {{ $step['id'] }}
                            @endif
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $step['label'] }}</div>
                            <div class="text-sm text-gray-600">{{ $step['sublabel'] }}</div>
                            @if($step['date'])
                                <div class="text-xs text-gray-500 mt-1">Completado: {{ $step['date'] }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($step['auto'])
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">{{ __('Automático') }}</span>
                        @else
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ __('Manual') }}</span>
                        @endif
                        @if($step['status'] == 'done')
                            <span class="px-3 py-1 text-xs bg-green-100 text-green-800 rounded-full font-medium">{{ __('Completado') }}</span>
                        @elseif($step['status'] == 'review')
                            <span class="px-3 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full font-medium">{{ __('En revisión') }}</span>
                        @elseif($step['status'] == 'locked')
                            <span class="px-3 py-1 text-xs bg-gray-100 text-gray-600 rounded-full font-medium">{{ __('Bloqueado') }}</span>
                        @else
                            <span class="px-3 py-1 text-xs bg-blue-100 text-blue-800 rounded-full font-medium">{{ __('Pendiente') }}</span>
                        @endif
                    </div>
                </div>
                    @endforeach
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
