@extends('layouts.main_user')

@section('content')
<div class="flex-1 bg-gray-50 p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Portal del Cliente</h1>
        <p class="text-gray-600">Bienvenido a tu portal personal</p>
    </div>
        <!-- Alerts -->
    <div class="space-y-3 mb-6">
        <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 border-l-4 border-l-red-500 rounded-lg">
            <svg class="material-design-icon__svg text-red-600" width="20" height="20" viewBox="0 0 24 24">
                <path d="M13,14H11V10H13M13,18H11V16H13M1,21H23L12,2L1,21Z"></path>
            </svg>
            <div class="flex-1">
                <div class="text-sm font-medium text-red-900">2 documentos pendientes de tu firma</div>
            </div>
            <a href="/dashboard/documents" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                Firmar ahora
            </a>
        </div>
        <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 border-l-4 border-l-blue-500 rounded-lg">
            <svg class="material-design-icon__svg text-blue-600" width="20" height="20" viewBox="0 0 24 24">
                <path d="M13,9H18.5L13,3.5V9M6,2H14L20,8V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V4C4,2.89 4.89,2 6,2M15,18V16H6V18H15M18,14V12H6V14H18Z"></path>
            </svg>
            <div class="flex-1">
                <div class="text-sm font-medium text-blue-900">Tu borrador de promesa de compraventa está en revisión legal</div>
            </div>
            <a href="/dashboard/documents" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                Ver documento
            </a>
        </div>
    </div>

    <!-- My Reservations -->
    @if($reservations->count() > 0)
        @foreach($reservations as $reservation)
            <!-- Reservation Details with Photo -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Detalles de la Reserva</h3>
                    <div class="flex items-center space-x-2">
                        @if($reservation->status == 'pending')
                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Pendiente</span>
                        @elseif($reservation->status == 'confirmed')
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Confirmada</span>
                        @else
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">Cancelada</span>
                        @endif
                        @if($reservation->id_document_path)
                            <svg class="material-design-icon__svg text-green-600" width="16" height="16" viewBox="0 0 24 24">
                                <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"></path>
                            </svg>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Property Photo and Info -->
                    <div class="space-y-4">
                        @if($reservation->unit && $reservation->unit->images->count() > 0)
                            <div class="relative rounded-lg overflow-hidden">
                                <img src="{{ asset($reservation->unit->images->first()->path) }}" 
                                     alt="{{ $reservation->unit_name ?? 'Unit ' . $reservation->unit_id }}" 
                                     class="w-full h-48 object-cover">
                                <div class="absolute top-2 left-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-md">
                                    <span class="text-xs font-medium text-gray-900">{{ $reservation->unit_name ?? $reservation->unit->name ?? 'Unit ' . $reservation->unit_id }}</span>
                                </div>
                            </div>
                        @else
                            <div class="relative rounded-lg overflow-hidden bg-gray-200 h-48 flex items-center justify-center">
                                <svg class="material-design-icon__svg text-gray-400" width="48" height="48" viewBox="0 0 24 24">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                                </svg>
                                <div class="absolute top-2 left-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-md">
                                    <span class="text-xs font-medium text-gray-900">{{ $reservation->unit_name ?? $reservation->unit->name ?? 'Unit ' . $reservation->unit_id }}</span>
                                </div>
                            </div>
                        @endif
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-3">Información de la Unidad</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Nombre:</span>
                                    <span class="text-sm font-medium">{{ $reservation->unit_name ?? $reservation->unit->name ?? 'Unit ' . $reservation->unit_id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Código:</span>
                                    <span class="text-sm font-medium">{{ $reservation->reservation_code }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Precio Total:</span>
                                    <span class="text-sm font-medium">{{ $reservation->formatted_price }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Plan de Pagos:</span>
                                    <span class="text-sm font-medium">{{ $reservation->payment_method ?? 'No definido' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-3">Información Personal</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Nombre:</span>
                                    <span class="text-sm font-medium">{{ $reservation->first_name }} {{ $reservation->last_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Email:</span>
                                    <span class="text-sm font-medium">{{ $reservation->email }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Teléfono:</span>
                                    <span class="text-sm font-medium">{{ $reservation->phone }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">País:</span>
                                    <span class="text-sm font-medium">{{ $reservation->country }}</span>
                                </div>
                                @if($reservation->profession)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Profesión:</span>
                                        <span class="text-sm font-medium">{{ $reservation->profession }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($reservation->id_document_path)
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">Documento de Identidad</h4>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <svg class="material-design-icon__svg text-green-600" width="16" height="16" viewBox="0 0 24 24">
                                            <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"></path>
                                        </svg>
                                        <span class="text-sm text-green-800">Documento subido</span>
                                    </div>
                                    <a href="{{ asset($reservation->id_document_path) }}" 
                                       target="_blank" 
                                       class="text-sm text-blue-600 hover:text-blue-800 underline">
                                        Ver documento
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="text-center py-8">
                <svg class="material-design-icon__svg text-gray-400 mx-auto mb-4" width="48" height="48" viewBox="0 0 24 24">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tienes reservas</h3>
                <p class="text-gray-600 mb-4">Comienza reservando una unidad desde el home</p>
                <a href="/" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                    Ver Unidades
                </a>
            </div>
        </div>
    @endif



    

    <!-- Mini Process -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Estado del proceso</h3>
            <a href="/dashboard/progress" class="text-blue-600 text-sm hover:text-blue-700">
                Ver completo →
            </a>
        </div>
        <div class="flex gap-2 overflow-x-auto">
            @php
                $steps = [
                    ['id' => 1, 'label' => 'Formulario KYC', 'status' => 'done'],
                    ['id' => 2, 'label' => 'Sincronización CRM', 'status' => 'done'],
                    ['id' => 3, 'label' => 'Formulario de Reserva', 'status' => 'pending'],
                    ['id' => 4, 'label' => 'Plan de Pagos', 'status' => 'pending'],
                    ['id' => 5, 'label' => 'Promesa de Compraventa', 'status' => 'review'],
                    ['id' => 6, 'label' => 'Contrato Definitivo', 'status' => 'locked'],
                    ['id' => 7, 'label' => 'Copia Compulsada Notarial', 'status' => 'locked'],
                ];
            @endphp

            @foreach($steps as $i => $step)
                <div class="flex items-center flex-1 min-w-[60px]">
                    <div class="flex flex-col items-center flex-1">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm border-2
                            @if($step['status'] == 'done') bg-green-500 border-green-500 text-white
                            @elseif($step['status'] == 'pending') bg-yellow-500 border-yellow-500 text-white
                            @elseif($step['status'] == 'review') bg-blue-500 border-blue-500 text-white
                            @else bg-gray-300 border-gray-300 text-gray-600 @endif">
                            @if($step['status'] == 'done') ✓
                            @elseif($step['status'] == 'locked') ·
                            @else {{ $step['id'] }}
                            @endif
                        </div>
                        <div class="text-xs text-center mt-1 max-w-[60px] leading-tight
                            @if($step['status'] == 'locked') text-gray-500
                            @elseif($step['status'] == 'done') text-green-600
                            @elseif($step['status'] == 'pending') text-yellow-600
                            @else text-blue-600 @endif">
                            {{ Str::words($step['label'], 2) }}
                        </div>
                    </div>
                    @if($i < count($steps) - 1)
                        <div class="h-0.5 flex-0.5 mb-4
                            @if($step['status'] == 'done') bg-green-500
                            @else bg-gray-300 @endif">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
