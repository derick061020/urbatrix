@extends('layouts.main_user')

@section('content')
<div class="flex-1 bg-gray-50 p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Documentos') }}</h1>
        <p class="text-gray-600">{{ __('Gestiona y firma tus documentos importantes') }}</p>
    </div>

    <!-- Documents Section -->
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
                        // Obtener documentos reales de la base de datos
                        $dbDocuments = $reservation->documents;
                        
                        // Documentos del sistema
                        $documents = [];
                        
                        // Formulario de Reserva (siempre presente)
                        $documents[] = [
                            'id' => 1, 
                            'name' => 'Formulario de Reserva', 
                            'type' => 'Formulario inicial', 
                            'status' => $reservation->first_name ? 'done' : 'sign', 
                            'updated' => $reservation->created_at->format('d M Y, H:i')
                        ];
                        
                        // Documento de Identidad
                        $documents[] = [
                            'id' => 2, 
                            'name' => 'Documento de Identidad', 
                            'type' => 'ID uploaded', 
                            'status' => $reservation->id_document_path ? 'done' : 'sign', 
                            'updated' => $reservation->id_document_path ? $reservation->updated_at->format('d M Y, H:i') : 'Pendiente'
                        ];
                        
                        // Formulario KYC
                        $documents[] = [
                            'id' => 3, 
                            'name' => 'Formulario KYC Completo', 
                            'type' => 'Datos personales', 
                            'status' => $reservation->profession ? 'done' : 'sign', 
                            'updated' => $reservation->profession ? $reservation->updated_at->format('d M Y, H:i') : 'Pendiente'
                        ];
                        
                        // Plan de Pagos (de la BD)
                        $paymentPlan = $dbDocuments->where('document_type', 'payment_plan')->first();
                        if ($paymentPlan) {
                            $documents[] = [
                                'id' => 4, 
                                'name' => 'Plan de Pagos', 
                                'type' => 'Payment schedule', 
                                'status' => $paymentPlan->status, 
                                'updated' => $paymentPlan->updated_at->format('d M Y, H:i'),
                                'document_id' => $paymentPlan->id,
                                'file_path' => $paymentPlan->file_path
                            ];
                        } else {
                            $documents[] = [
                                'id' => 4, 
                                'name' => 'Plan de Pagos', 
                                'type' => 'Payment schedule', 
                                'status' => $reservation->payment_method ? 'pending' : 'pending', 
                                'updated' => 'Pendiente'
                            ];
                        }
                        
                        // Contrato (de la BD)
                        $contract = $dbDocuments->where('document_type', 'contract')->first();
                        if ($contract) {
                            $documents[] = [
                                'id' => 5, 
                                'name' => 'Contrato', 
                                'type' => 'Documento legal', 
                                'status' => $contract->status, 
                                'updated' => $contract->updated_at->format('d M Y, H:i'),
                                'document_id' => $contract->id,
                                'file_path' => $contract->file_path
                            ];
                        } else {
                            $documents[] = [
                                'id' => 5, 
                                'name' => 'Contrato', 
                                'type' => 'Documento legal', 
                                'status' => 'pending', 
                                'updated' => 'Pendiente'
                            ];
                        }
                    @endphp

                    @foreach($documents as $doc)
                <div class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            @if($doc['status'] == 'signed' || $doc['status'] == 'done') bg-green-100 text-green-600
                            @elseif($doc['status'] == 'generated') bg-blue-100 text-blue-600
                            @elseif($doc['status'] == 'approved') bg-green-100 text-green-600
                            @elseif($doc['status'] == 'sign') bg-yellow-100 text-yellow-600
                            @elseif($doc['status'] == 'review') bg-blue-100 text-blue-600
                            @else bg-gray-100 text-gray-600 @endif">
                            @if($doc['status'] == 'signed')
                                <svg class="material-design-icon__svg" width="16" height="16" viewBox="0 0 24 24">
                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"></path>
                                </svg>
                            @elseif($doc['status'] == 'generated')
                                <svg class="material-design-icon__svg" width="16" height="16" viewBox="0 0 24 24">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                                </svg>
                            @elseif($doc['status'] == 'approved')
                                <svg class="material-design-icon__svg" width="16" height="16" viewBox="0 0 24 24">
                                    <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"></path>
                                </svg>
                            @elseif($doc['status'] == 'sign')
                                <svg class="material-design-icon__svg" width="16" height="16" viewBox="0 0 24 24">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                                </svg>
                            @elseif($doc['status'] == 'review')
                                <svg class="material-design-icon__svg" width="16" height="16" viewBox="0 0 24 24">
                                    <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"></path>
                                </svg>
                            @else
                                <svg class="material-design-icon__svg" width="16" height="16" viewBox="0 0 24 24">
                                    <path d="M12,17A2,2 0 0,0 14,15C14,13.89 13.1,13 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V10C4,8.89 4.9,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z"></path>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ $doc['name'] }}</div>
                            <div class="text-sm text-gray-600">{{ $doc['type'] }}</div>
                            <div class="text-xs text-gray-500 mt-1">Actualizado: {{ $doc['updated'] }}</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($doc['id'] == 4)
                            <!-- Botones para Plan de Pagos -->
                            @if(isset($doc['document_id']) && $doc['file_path'] && $doc['file_path'] != 'pending')
                                <a href="{{ route('documents.download', $doc['document_id']) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-black text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition-colors">
                                    <svg class="material-design-icon__svg w-4 h-4 mr-2" viewBox="0 0 24 24">
                                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"></path>
                                    </svg>
                                    Descargar Plan
                                </a>
                            @else
                                <!-- Cliente puede generar el plan de pagos -->
                                <button onclick="generatePaymentPlan({{ $reservation->id }})" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="material-design-icon__svg w-4 h-4 mr-2" viewBox="0 0 24 24">
                                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"></path>
                                    </svg>
                                    Generar Plan
                                </button>
                            @endif
                            @if(isset($doc['document_id']) && $doc['status'] == 'generated')
                                <button onclick="signDocument('{{ $doc['document_id'] }}', 'payment_plan')" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    <svg class="material-design-icon__svg w-4 h-4 mr-2" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"></path>
                                    </svg>
                                    Firmar Plan
                                </button>
                            @endif
                        @elseif($doc['id'] == 5)
                            <!-- Botón para Contrato -->
                            @if(isset($doc['document_id']) && $doc['status'] == 'signed')
                                <button disabled
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg cursor-not-allowed">
                                    <svg class="material-design-icon__svg w-4 h-4 mr-2" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"></path>
                                    </svg>
                                    Firmado
                                </button>
                            @elseif(isset($doc['document_id']) && $doc['status'] == 'generated')
                                @php
                                    $contract = $reservation->documents()->where('document_type', 'contract')->first();
                                    $isConforme = $contract && isset($contract->metadata['conforme']) && $contract->metadata['conforme'];
                                @endphp
                                
                                @if($isConforme)
                                    <!-- Cuando está conforme, mostrar solo descargar y firmar -->
                                    <div class="flex gap-2">
                                        <a href="{{ route('documents.download', $doc['document_id']) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-black text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition-colors">
                                            <svg class="material-design-icon__svg w-4 h-4 mr-2" viewBox="0 0 24 24">
                                                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"></path>
                                            </svg>
                                            Descargar
                                        </a>
                                        <button onclick="signDocument('{{ $doc['document_id'] }}', 'contract')" 
                                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                            <svg class="material-design-icon__svg w-4 h-4 mr-2" viewBox="0 0 24 24">
                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"></path>
                                            </svg>
                                            Firmar
                                        </button>
                                    </div>
                                @else
                                    <!-- Cuando no está conforme, mostrar ver detalles -->
                                    <button onclick="openPromesaModal({{ $reservation->id }})" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        <svg class="material-design-icon__svg w-4 h-4 mr-2" viewBox="0 0 24 24">
                                            <path d="M12 9a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0-3-3m0 8a5 5 0 0 1-5-5 5 5 0 0 1 5-5 5 5 0 0 1 5 5m0-12.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5z"></path>
                                            <path d="M12 9a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3m0 8a5 5 0 0 1-5-5 5 5 0 0 1 5-5 5 5 0 0 1 5 5 5 5 0 0 1-5 5m0-12.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5z"></path>
                                        </svg>
                                        Ver Detalles
                                    </button>
                                @endif
                            @else
                                <!-- Cuando no está generado, mostrar contrato no disponible -->
                                <div class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed">
                                    <svg class="material-design-icon__svg w-4 h-4 mr-2" viewBox="0 0 24 24">
                                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"></path>
                                    </svg>
                                    Contrato No Disponible
                                </div>
                            @endif
                        @else    <!-- Botones para otros documentos -->
                            @if($doc['status'] == 'sign')
                                <button class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                                    Firmar
                                </button>
                            @elseif($doc['status'] == 'review')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">{{ __('En revisión') }}</span>
                            @elseif($doc['status'] == 'done')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">{{ __('Completado') }}</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">{{ __('Pendiente') }}</span>
                            @endif
                        @endif
                    </div>
                </div>
                    @endforeach
                </div>
                
                <!-- Promesa Modal -->
                <div id="promesa-modal-{{ $reservation->id }}" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <!-- Header -->
                        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="material-design-icon__svg text-blue-600 w-4 h-4" viewBox="0 0 24 24">
                                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Contrato') }}</h3>
                                    <p class="text-sm text-gray-600">{{ __('Revisa los detalles de tu contrato') }}</p>
                                </div>
                            </div>
                            <button onclick="closePromesaModal({{ $reservation->id }})" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="p-6">
                            <!-- Contract Details -->
                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-2 h-2 bg-blue-600 rounded-full mr-2"></div>
                                    Detalles del Contrato
                                </h4>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">{{ __('Código de Reserva:') }}</span>
                                            <span class="text-gray-900 font-medium">{{ $reservation->reservation_code }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">Unidad:</span>
                                            <span class="text-gray-900 font-medium">{{ $reservation->unit_name ?? $reservation->unit->name ?? 'Unit ' . $reservation->unit_id }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">Precio:</span>
                                            <span class="text-gray-900 font-medium">{{ $reservation->formatted_price }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">Estado:</span>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                                @if($reservation->status == 'confirmed') bg-green-100 text-green-800
                                                @elseif($reservation->status == 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $reservation->status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Observaciones Section -->
                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-2 h-2 bg-blue-600 rounded-full mr-2"></div>
                                    Observaciones
                                </h4>
                                <textarea id="observaciones-{{ $reservation->id }}" 
                                          class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                          rows="4" 
                                          placeholder="{{ __('Ingrese cualquier observación o comentario sobre el contrato...') }}"></textarea>
                                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                                    <button onclick="savePromesaData({{ $reservation->id }})" 
                                            class="group relative inline-flex items-center px-4 py-2 bg-stone-700 text-white text-xs font-medium rounded-md hover:bg-stone-800 transition-all duration-200 border border-stone-600">
                                        <svg class="material-design-icon__svg w-3.5 h-3.5 mr-1.5" viewBox="0 0 24 24">
                                            <path d="M15,9H5V5H15M12,19A3,3 0 0,1 9,16C9,14 9,14 9,14H5V10H9L12,19Z"></path>
                                        </svg>
                                        Enviar
                                    </button>
                                </div>
                            </div>

                            <!-- Conformidad Section -->
                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-2 h-2 bg-blue-600 rounded-full mr-2"></div>
                                    Conformidad
                                </h4>
                                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" id="conforme-{{ $reservation->id }}" 
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 focus:ring-2">
                                        <span class="ml-3 text-sm text-gray-700">{{ __('Estoy conforme con los términos del contrato') }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                                @php
                                    // Verificar si el documento de contrato está marcado como conforme
                                    $contract = $reservation->documents()->where('document_type', 'contract')->first();
                                    $isConforme = $contract && isset($contract->metadata['conforme']) && $contract->metadata['conforme'];
                                @endphp
                                
                                @if(!$isConforme)
                                    <!-- Botones iniciales: Enviar observaciones o marcar como conforme -->
                                    <button onclick="savePromesaData({{ $reservation->id }})" 
                                            class="group relative inline-flex items-center px-4 py-2 bg-stone-700 text-white text-xs font-medium rounded-md hover:bg-stone-800 transition-all duration-200 border border-stone-600">
                                        <svg class="material-design-icon__svg w-3.5 h-3.5 mr-1.5" viewBox="0 0 24 24">
                                            <path d="M15,9H5V5H15M12,19A3,3 0 0,1 9,16C9,14 9,14 9,14H5V10H9L12,19Z"></path>
                                        </svg>
                                        Enviar Observaciones
                                    </button>
                                    <button onclick="markAsConforme({{ $reservation->id }})" 
                                            class="group relative inline-flex items-center px-4 py-2 bg-stone-600 text-white text-xs font-medium rounded-md hover:bg-stone-700 transition-all duration-200 border border-stone-500">
                                        <svg class="material-design-icon__svg w-3.5 h-3.5 mr-1.5" viewBox="0 0 24 24">
                                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"></path>
                                        </svg>
                                        Marcar como Conforme
                                    </button>
                                @else
                                    <!-- Botón de firmar cuando está conforme -->
                                    <button onclick="signPurchasePromise({{ $reservation->id }})" 
                                            class="group relative inline-flex items-center px-6 py-2.5 bg-black text-white text-sm font-semibold rounded-lg hover:bg-gray-900 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                        <svg class="material-design-icon__svg w-4 h-4 mr-2" viewBox="0 0 24 24">
                                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"></path>
                                        </svg>
                                        Firmar Contrato
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- JavaScript for Modal -->
                <script>
                function signDocument(documentId, documentType) {
                    if (confirm('{{ __("¿Estás seguro de que deseas firmar este documento?") }}')) {
                        fetch(`/documents/${documentId}/sign`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                notes: 'Firmado digitalmente desde el dashboard'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                // Reload page to show updated status
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('{{ __("Error al firmar el documento") }}');
                        });
                    }
                }

                function generatePaymentPlan(reservationId) {
                    if (confirm('{{ __("¿Estás seguro de que deseas generar el plan de pagos?") }}')) {
                        window.location.href = `/contract/${reservationId}/payment-plan`;
                    }
                }

                function openPromesaModal(reservationId) {
                    document.getElementById('promesa-modal-' + reservationId).classList.remove('hidden');
                }

                function closePromesaModal(reservationId) {
                    document.getElementById('promesa-modal-' + reservationId).classList.add('hidden');
                }

                function savePromesaData(reservationId) {
                    const observaciones = document.getElementById('observaciones-' + reservationId).value;
                    
                    // Save observations to backend via AJAX
                    fetch(`/reservations/${reservationId}/save-observations`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            observaciones: observaciones,
                            document_type: 'contract'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __("Error al guardar observaciones") }}');
                    });
                }

                function markAsConforme(reservationId) {
                    const conforme = document.getElementById('conforme-' + reservationId).checked;
                    const observaciones = document.getElementById('observaciones-' + reservationId).value;
                    
                    if (!conforme) {
                        alert('{{ __("Debe marcar la casilla de conformidad para continuar") }}');
                        return;
                    }
                    
                    // AJAX call to backend para marcar como conforme
                    fetch(`/reservations/${reservationId}/mark-conforme`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            observaciones: observaciones,
                            conforme: conforme
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            // Reload page to show updated status and firmar button
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __("Error al procesar la solicitud") }}');
                    });
                }

                function signPurchasePromise(reservationId) {
                    if (confirm('{{ __("¿Estás seguro de que deseas firmar el Contrato?") }}')) {
                        // Obtener el documento de contrato
                        fetch(`/reservations/${reservationId}/documents`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const contract = data.documents.find(doc => doc.document_type === 'contract');
                                    if (contract) {
                                        // Firmar el documento
                                        return fetch(`/documents/${contract.id}/sign`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify({
                                                notes: 'Firmado después de marcar como conforme'
                                            })
                                        });
                                    }
                                }
                                throw new Error('Documento no encontrado');
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    // Reload page to show updated status
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    alert('Error: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('{{ __("Error al firmar el documento") }}');
                            });
                    }
                }

                function updateFirmarButton(reservationId, showFirmar) {
                    // This would update the UI to show the firmar button
                    console.log('Actualizando botón firmar para reserva ' + reservationId + ':', showFirmar);
                }
                </script>
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
