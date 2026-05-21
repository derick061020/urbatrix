@extends('layouts.main_admin')
@section('title', 'Detalle de Expediente - Admin Panel')
@php $activeRoute = 'crm.expedientes'; @endphp

@section('content')
<div class="w-full bg-[#f9f8f6] px-2 py-4 sm:p-10 overflow-auto min-h-screen">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-4xl font-semibold text-[#625441]">Expediente EXP-{{ str_pad($reservation->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <p class="text-[#625441] mt-1">{{ $reservation->first_name }} {{ $reservation->last_name }}</p>
        </div>
        <a href="{{ url()->previous() }}" class="bg-[#667b6a] text-white rounded px-4 py-2 text-sm font-semibold hover:bg-[#5a6d5e]">
            ← Volver a Expedientes
        </a>
    </div>

    <div class="relative flex items-center my-5 w-full px-5">
        <div class="absolute left-0 top-1/2 w-full border-t border-gray-200"></div>
    </div>

    <!-- Reservation Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Property Information -->
        <div class="border border-gray-200 rounded p-6 bg-white">
            <h3 class="text-lg font-semibold text-[#625441] mb-4">Información de la Unidad</h3>
            
            @if($reservation->unit && $reservation->unit->images->count() > 0)
                <div class="relative rounded-lg overflow-hidden mb-4">
                    <img src="{{ asset($reservation->unit->images->first()->path) }}" 
                         alt="{{ $reservation->unit_name ?? 'Unit ' . $reservation->unit_id }}" 
                         class="w-full h-48 object-cover">
                </div>
            @endif
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-[#806f56]">Unidad:</span>
                    <span class="text-sm font-medium text-[#625441]">{{ $reservation->unit_name ?? $reservation->unit->name ?? 'Unit ' . $reservation->unit_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-[#806f56]">Código:</span>
                    <span class="text-sm font-medium text-[#625441]">{{ $reservation->reservation_code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-[#806f56]">Estado:</span>
                    <span class="text-sm font-medium text-[#625441]">{{ $reservation->status ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-[#806f56]">Precio Total:</span>
                    <span class="text-sm font-medium text-[#625441]">{{ $reservation->formatted_price ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-[#806f56]">Plan de Pagos:</span>
                    <span class="text-sm font-medium text-[#625441]">{{ $reservation->payment_method ?? 'No definido' }}</span>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="border border-gray-200 rounded p-6 bg-white">
            <h3 class="text-lg font-semibold text-[#625441] mb-4">Información Personal</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-[#806f56]">Nombre:</span>
                    <span class="text-sm font-medium text-[#625441]">{{ $reservation->first_name }} {{ $reservation->last_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-[#806f56]">Email:</span>
                    <span class="text-sm font-medium text-[#625441]">{{ $reservation->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-[#806f56]">Teléfono:</span>
                    <span class="text-sm font-medium text-[#625441]">{{ $reservation->phone }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-[#806f56]">País:</span>
                    <span class="text-sm font-medium text-[#625441]">{{ $reservation->country }}</span>
                </div>
                @if($reservation->profession)
                    <div class="flex justify-between">
                        <span class="text-sm text-[#806f56]">Profesión:</span>
                        <span class="text-sm font-medium text-[#625441]">{{ $reservation->profession }}</span>
                    </div>
                @endif
            </div>
            
            <!-- Quick Communication Actions -->
            <div class="mt-6 pt-4 border-t border-gray-200">
                <h4 class="text-sm font-semibold text-[#625441] mb-3">Comunicación Rápida</h4>
                <div class="flex flex-wrap gap-2">
                    @if($reservation->email)
                        <a href="mailto:{{ $reservation->email }}" 
                           class="inline-flex items-center gap-2 px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Enviar Email
                        </a>
                    @endif
                    
                    @if($reservation->phone)
                        <a href="tel:{{ $reservation->phone }}" 
                           class="inline-flex items-center gap-2 px-3 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            Llamar
                        </a>
                        
                        @if(preg_match('/^\+?[\d\s\-\(\)]+$/', $reservation->phone))
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $reservation->phone) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.149-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                WhatsApp
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    <div class="border border-gray-200 rounded p-6 bg-white mb-6">
        <h3 class="text-lg font-semibold text-[#625441] mb-4">Documentos del Cliente</h3>
        
        @if($reservation->id_document_path)
            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-[#625441]">Documento de Identidad</h4>
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Subido</span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        @if(strtolower(pathinfo($reservation->id_document_path, PATHINFO_EXTENSION)) === 'pdf')
                            <div class="flex items-center gap-2 text-red-600">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M10,19H12V16H10V19M10,14H12V10H10V14M10,9H12V5H10V9Z"/>
                                </svg>
                                <span class="text-sm font-medium">PDF</span>
                            </div>
                        @else
                            <div class="w-16 h-16 border border-gray-300 rounded overflow-hidden bg-gray-100">
                                <img src="{{ asset($reservation->id_document_path) }}" 
                                     alt="Documento de Identidad" 
                                     class="w-full h-full object-cover"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTE0IDJINkEyIDIgMCAwIDAgNCA0VjIwQTIgMiAwIDAgMCA2IDIySDE4QTIgMiAwIDAgMCAyMCAyMFY4TDE0IDJNMjAgMjBINlY0SDEzVjlIMjBWMjBNMTAgMTlIMTJWMTZIMTBWMTlNMTAgMTRIMTJWMTBIMTBWMTRaIiBmaWxsPSIjOUNCOUI5Ii8+Cjwvc3ZnPgo='">
                            </div>
                        @endif
                        
                        <div class="flex-1">
                            <p class="text-sm text-gray-600 mb-2">
                                {{ basename($reservation->id_document_path) }}
                            </p>
                            
                            <div class="flex gap-2">
                                <a href="{{ asset($reservation->id_document_path) }}" 
                                   target="_blank"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Ver Documento
                                </a>
                                
                                <a href="{{ asset($reservation->id_document_path) }}" 
                                   download="{{ basename($reservation->id_document_path) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-gray-500 text-white text-sm rounded hover:bg-gray-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Descargar
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    @if($reservation->document_number || $reservation->id_type)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            @if($reservation->id_type)
                                <div>
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium text-[#625441] ml-2">{{ $reservation->id_type }}</span>
                                </div>
                            @endif
                            @if($reservation->document_number)
                                <div>
                                    <span class="text-gray-600">Número:</span>
                                    <span class="font-medium text-[#625441] ml-2">{{ $reservation->document_number }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center py-8 bg-gray-50 rounded-lg">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-500 text-sm">El cliente aún no ha subido su documento de identidad</p>
            </div>
        @endif
        
        <!-- Payment Plan Section -->
        <div class="mb-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-[#625441] mb-3">Plan de Pagos</h4>
            
            @php
                $paymentPlanDocument = \App\Models\Document::where('reservation_id', $reservation->id)
                    ->where('document_type', 'payment_plan')
                    ->first();
            @endphp
            
            @if($paymentPlanDocument)
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="text-sm font-semibold text-[#625441]">Plan de Pagos</h5>
                        <span class="px-2 py-1 text-xs 
                            @if($paymentPlanDocument->status == 'signed') bg-green-100 text-green-800 rounded-full
                            @elseif($paymentPlanDocument->status == 'generated') bg-blue-100 text-blue-800 rounded-full
                            @else bg-gray-100 text-gray-800 rounded-full @endif">
                            @if($paymentPlanDocument->status == 'signed') Firmado
                            @elseif($paymentPlanDocument->status == 'generated') Generado
                            @else Pendiente @endif
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded flex items-center justify-center text-xs font-medium bg-blue-100 text-blue-700">
                            📋
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-600 mb-2">
                                {{ $paymentPlanDocument->title ?? 'Plan de Pagos' }}
                            </p>
                            
                            <div class="flex gap-2">
                                @if($paymentPlanDocument->file_path)
                                    <a href="{{ route('documents.download', $paymentPlanDocument->id) }}" 
                                       target="_blank"
                                       class="inline-flex items-center gap-2 px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Ver
                                    </a>
                                    
                                    <a href="{{ route('documents.download', $paymentPlanDocument->id) }}" 
                                       class="inline-flex items-center gap-2 px-3 py-2 bg-gray-500 text-white text-sm rounded hover:bg-gray-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Descargar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($paymentPlanDocument->signed_at)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Estado:</span>
                                <span class="font-medium text-green-700 ml-2">Firmado</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Fecha de firma:</span>
                                <span class="font-medium text-[#625441] ml-2">{{ $paymentPlanDocument->signed_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($paymentPlanDocument->signed_by)
                            <div>
                                <span class="text-gray-600">Firmado por:</span>
                                <span class="font-medium text-[#625441] ml-2">Cliente</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">El plan de pagos aún no ha sido generado</p>
                </div>
            @endif
        </div>
        
        <!-- Contract Generation Section -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-[#625441] mb-3">Generación de Contrato</h4>
            
            @php
                $hasContract = \App\Models\Document::where('reservation_id', $reservation->id)
                    ->where('document_type', 'contract')
                    ->exists();
            @endphp
            
            @if($hasContract)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-green-800">Contrato generado</p>
                            <p class="text-xs text-green-600">El contrato ya está disponible para el cliente</p>
                        </div>
                    </div>
                    
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('admin.crm.contract.generate', $reservation->id) }}" 
                           target="_blank"
                           class="inline-flex items-center gap-2 px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Ver Contrato
                        </a>
                        
                        <a href="{{ route('admin.crm.contract.generate', $reservation->id) }}" 
                           download="contrato_{{ $reservation->reservation_code }}.pdf"
                           class="inline-flex items-center gap-2 px-3 py-2 bg-gray-500 text-white text-sm rounded hover:bg-gray-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Descargar
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M10,19H12V16H10V19M10,14H12V10H10V14Z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Contrato no generado</p>
                            <p class="text-xs text-yellow-600">Genera el contrato para que el cliente pueda revisarlo y firmar</p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('admin.crm.contract.generate', $reservation->id) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Generar Contrato
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Contract Observations Section -->
    @php
        $contract = $reservation->documents()->where('document_type', 'contract')->first();
        $hasObservations = $contract && !empty($contract->metadata['observaciones']);
        $isConforme = $contract && isset($contract->metadata['conforme']) && $contract->metadata['conforme'];
        $isSigned = $contract && $contract->status == 'signed';
    @endphp
    
    @if($hasObservations || $isConforme || $isSigned)
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Observaciones del Contrato
            </h3>
            
            <div class="space-y-4">
                @if($isConforme)
                    <div class="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-green-800">Cliente marcó como CONFORME</p>
                            @if(isset($contract->metadata['conforme_at']))
                                <p class="text-sm text-green-600">Fecha: {{ \Carbon\Carbon::parse($contract->metadata['conforme_at'])->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
                
                @if($hasObservations)
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-medium text-blue-800 mb-2">Observaciones del cliente:</h4>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $contract->metadata['observaciones'] }}</p>
                        @if(isset($contract->metadata['observaciones_at']))
                            <p class="text-sm text-blue-600 mt-2">Enviado: {{ \Carbon\Carbon::parse($contract->metadata['observaciones_at'])->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                @endif
                
                @if($isSigned)
                    <div class="flex items-center p-3 bg-purple-50 border border-purple-200 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-purple-800">Contrato Firmado</p>
                            <p class="text-sm text-purple-600">El cliente ha firmado el contrato exitosamente</p>
                            @if($contract->updated_at)
                                <p class="text-sm text-purple-600 mt-1">Fecha de firma: {{ $contract->updated_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="border border-green-200 bg-green-50 text-green-800 rounded p-3 text-sm mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="border border-red-200 bg-red-50 text-red-800 rounded p-3 text-sm mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Presupuesto Section -->
    @php
        $budgetStatus = $reservation->budget_status ?? 'pending';
        $statusBadge = [
            'pending' => ['label' => 'Sin configurar', 'bg' => '#f5f3ee', 'fg' => '#9b9b9b', 'border' => '#e0dccf'],
            'draft'   => ['label' => 'Borrador (no visible al cliente)', 'bg' => '#fef6e6', 'fg' => '#9b6f1d', 'border' => '#e9d3a4'],
            'sent'    => ['label' => 'Enviado al cliente', 'bg' => '#eaf6ec', 'fg' => '#3d8048', 'border' => '#bedcc4'],
        ][$budgetStatus] ?? ['label' => $budgetStatus, 'bg' => '#f5f3ee', 'fg' => '#9b9b9b', 'border' => '#e0dccf'];
        
        // Check if payment plan or contract is signed
        $paymentPlanDocument = \App\Models\Document::where('reservation_id', $reservation->id)
            ->where('document_type', 'payment_plan')
            ->first();
        $contractDocument = \App\Models\Document::where('reservation_id', $reservation->id)
            ->where('document_type', 'contract')
            ->first();
        
        $isAnyDocumentSigned = ($paymentPlanDocument && $paymentPlanDocument->status == 'signed') || 
                              ($contractDocument && $contractDocument->status == 'signed');

        $bInitial      = $reservation->payment_initial_percentage ?: 0;
        $bConstruction = $reservation->payment_construction_percentage ?: 0;
        $bDelivery     = $reservation->payment_delivery_percentage ?: 0;
        $bInstallments = $reservation->payment_installments ?: 0;
        $bLegal        = $reservation->legal_costs ?: 500;
        $bMethod       = $reservation->payment_method ?: 'A';
        $bNotes        = $reservation->budget_notes ?? '';

        $totalPrice    = floatval($reservation->unit_price);
        $previewInitial      = $totalPrice * $bInitial / 100;
        $previewConstruction = $totalPrice * $bConstruction / 100;
        $previewDelivery     = $totalPrice * $bDelivery / 100;
        $previewCuota        = $bInstallments > 0 ? $previewConstruction / $bInstallments : 0;
    @endphp

    <div class="border border-gray-200 rounded p-6 bg-white mb-6">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="text-lg font-semibold text-[#625441]">Presupuesto</h3>
            <span class="px-3 py-1 text-xs font-medium rounded"
                  style="background:{{ $statusBadge['bg'] }};color:{{ $statusBadge['fg'] }};border:1px solid {{ $statusBadge['border'] }};">
                {{ $statusBadge['label'] }}
                @if($reservation->budget_sent_at)
                    · Enviado el {{ $reservation->budget_sent_at->format('d/m/Y H:i') }}
                @endif
            </span>
        </div>

        @if($isAnyDocumentSigned)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Presupuesto bloqueado</p>
                        <p class="text-xs text-yellow-600">El presupuesto no puede editarse porque el cliente ya ha firmado el plan de pagos o el contrato.</p>
                    </div>
                </div>
            </div>
        @else
            <p class="text-sm text-[#806f56] mb-4">
                Configura el plan de pagos del cliente. El presupuesto solo se mostrará en el dashboard del cliente cuando uses
                <strong>Enviar al cliente</strong>.
            </p>
        @endif

        <form method="POST" action="{{ route('admin.crm.budget.save', $reservation->id) }}" class="space-y-4" @if($isAnyDocumentSigned) onsubmit="return false;" @endif>
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-[#625441] mb-1">Plan</label>
                    <select name="payment_method" id="bm_payment_method"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            @if($isAnyDocumentSigned) disabled @endif>
                        <option value="A"      @selected($bMethod === 'A')>Plan A — 80% inicial / 20% entrega</option>
                        <option value="B"      @selected($bMethod === 'B')>Plan B — 60% inicial / 40% entrega</option>
                        <option value="C"      @selected($bMethod === 'C')>Plan C — 25% inicial / 35% cuotas / 40% entrega</option>
                        <option value="custom" @selected($bMethod === 'custom')>Personalizado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-[#625441] mb-1">Costos legales (USD)</label>
                    <input type="number" step="0.01" min="0" name="legal_costs" value="{{ old('legal_costs', $bLegal) }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                           @if($isAnyDocumentSigned) disabled @endif>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-[#625441] mb-1">% Inicial</label>
                    <input type="number" step="0.01" min="0" max="100" name="payment_initial_percentage"
                           id="bm_initial" value="{{ old('payment_initial_percentage', $bInitial) }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                           @if($isAnyDocumentSigned) disabled @endif>
                </div>
                <div>
                    <label class="block text-sm text-[#625441] mb-1">% Construcción</label>
                    <input type="number" step="0.01" min="0" max="100" name="payment_construction_percentage"
                           id="bm_construction" value="{{ old('payment_construction_percentage', $bConstruction) }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                           @if($isAnyDocumentSigned) disabled @endif>
                </div>
                <div>
                    <label class="block text-sm text-[#625441] mb-1">% Entrega</label>
                    <input type="number" step="0.01" min="0" max="100" name="payment_delivery_percentage"
                           id="bm_delivery" value="{{ old('payment_delivery_percentage', $bDelivery) }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                           @if($isAnyDocumentSigned) disabled @endif>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-[#625441] mb-1">Cantidad de cuotas (mensuales)</label>
                    <input type="number" step="1" min="0" max="120" name="payment_installments"
                           id="bm_installments" value="{{ old('payment_installments', $bInstallments) }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                           @if($isAnyDocumentSigned) disabled @endif>
                </div>
                <div>
                    <label class="block text-sm text-[#625441] mb-1">Suma de porcentajes</label>
                    <div id="bm_sum_display"
                         class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50"
                         style="font-family:monospace;">{{ $bInitial + $bConstruction + $bDelivery }}%</div>
                    <p class="text-xs text-[#9b9b9b] mt-1">Debe sumar exactamente 100%.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm text-[#625441] mb-1">Notas internas (opcional)</label>
                <textarea name="budget_notes" rows="2"
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                          placeholder="Observaciones para el equipo"
                          @if($isAnyDocumentSigned) disabled @endif>{{ old('budget_notes', $bNotes) }}</textarea>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-semibold text-[#625441] mb-2">Vista previa para el cliente</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                    <div class="border border-gray-200 rounded p-3 bg-[#f9f8f6]">
                        <div class="text-xs text-[#9b9b9b]">Pago inicial (con legales)</div>
                        <div class="font-semibold text-[#625441]" id="bm_pv_initial">${{ number_format($previewInitial + $bLegal, 2) }}</div>
                    </div>
                    <div class="border border-gray-200 rounded p-3 bg-[#f9f8f6]">
                        <div class="text-xs text-[#9b9b9b]">Construcción</div>
                        <div class="font-semibold text-[#625441]" id="bm_pv_construction">${{ number_format($previewConstruction, 2) }}</div>
                    </div>
                    <div class="border border-gray-200 rounded p-3 bg-[#f9f8f6]">
                        <div class="text-xs text-[#9b9b9b]">Cuota mensual</div>
                        <div class="font-semibold text-[#625441]" id="bm_pv_cuota">${{ number_format($previewCuota, 2) }}</div>
                    </div>
                    <div class="border border-gray-200 rounded p-3 bg-[#f9f8f6]">
                        <div class="text-xs text-[#9b9b9b]">Entrega</div>
                        <div class="font-semibold text-[#625441]" id="bm_pv_delivery">${{ number_format($previewDelivery, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 pt-2">
                <button type="submit" name="action" value="save"
                        class="bg-white border border-[#667b6a] text-[#667b6a] rounded px-4 py-2 text-sm font-semibold hover:bg-[#f3f6f4]"
                        @if($isAnyDocumentSigned) disabled @endif>
                    Guardar borrador
                </button>
                <button type="submit" name="action" value="send"
                        class="bg-[#667b6a] text-white rounded px-4 py-2 text-sm font-semibold hover:bg-[#5a6d5e]"
                        onclick="return confirm('¿Enviar el presupuesto al cliente? Una vez enviado podrá verlo en su dashboard.');"
                        @if($isAnyDocumentSigned) disabled @endif>
                    Enviar al cliente
                </button>
            </div>
        </form>

        @if($budgetStatus === 'sent')
            <form method="POST" action="{{ route('admin.crm.budget.revert', $reservation->id) }}" class="mt-3">
                @csrf
                <button type="submit"
                        class="text-xs text-[#a83838] hover:underline"
                        onclick="return confirm('¿Revertir el presupuesto a borrador? El cliente dejará de verlo.');">
                    ← Revertir a borrador
                </button>
            </form>
        @endif
    </div>

    <script>
        (function() {
            const totalPrice = {{ $totalPrice }};
            const initEl     = document.getElementById('bm_initial');
            const consEl     = document.getElementById('bm_construction');
            const delEl      = document.getElementById('bm_delivery');
            const instEl     = document.getElementById('bm_installments');
            const legalEl    = document.querySelector('input[name="legal_costs"]');
            const planEl     = document.getElementById('bm_payment_method');
            const sumEl      = document.getElementById('bm_sum_display');
            const pvInit     = document.getElementById('bm_pv_initial');
            const pvCons     = document.getElementById('bm_pv_construction');
            const pvCuota    = document.getElementById('bm_pv_cuota');
            const pvDel      = document.getElementById('bm_pv_delivery');

            const planPresets = {
                A: { initial: 80, construction: 0,  delivery: 20, installments: 0 },
                B: { initial: 60, construction: 0,  delivery: 40, installments: 0 },
                C: { initial: 25, construction: 35, delivery: 40, installments: 36 },
            };

            function fmt(n) {
                return '$' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function refresh() {
                const i = parseFloat(initEl.value) || 0;
                const c = parseFloat(consEl.value) || 0;
                const d = parseFloat(delEl.value) || 0;
                const inst = parseInt(instEl.value) || 0;
                const legal = parseFloat(legalEl.value) || 0;

                const sum = (i + c + d).toFixed(2);
                sumEl.textContent = sum + '%';
                sumEl.style.color = Math.abs(sum - 100) < 0.01 ? '#3d8048' : '#a83838';

                pvInit.textContent  = fmt(totalPrice * i / 100 + legal);
                pvCons.textContent  = fmt(totalPrice * c / 100);
                pvDel.textContent   = fmt(totalPrice * d / 100);
                pvCuota.textContent = inst > 0 ? fmt((totalPrice * c / 100) / inst) : fmt(0);
            }

            planEl.addEventListener('change', function() {
                const preset = planPresets[planEl.value];
                if (!preset) return;
                initEl.value = preset.initial;
                consEl.value = preset.construction;
                delEl.value  = preset.delivery;
                instEl.value = preset.installments;
                refresh();
            });

            [initEl, consEl, delEl, instEl, legalEl].forEach(el => {
                el.addEventListener('input', refresh);
            });
        })();
    </script>


    <!-- Progress Section -->
    <div class="border border-gray-200 rounded p-6 bg-white mt-6">
        <h3 class="text-lg font-semibold text-[#625441] mb-4">Progreso del Expediente</h3>
        
        @php
            $totalDocs = max($reservation->documents->count(), 1);
            $approvedDocs = $reservation->documents->where('status', 'approved')->count();
            $pct = $totalDocs > 0 ? min(100, round(($approvedDocs / $totalDocs) * 100)) : 0;
            $etapa = $pct === 100 ? 'validado' : ($pct >= 60 ? 'en_tramite' : 'pendiente');
            $barColor = $pct === 100 ? '#3d8048' : ($pct > 60 ? '#9b6f1d' : '#a83838');
            $pctColor = $pct === 100 ? '#3d8048' : '#9b6f1d';
        @endphp
        
        <div class="flex items-center gap-3 mb-4">
            <div class="text-sm text-[#806f56] min-w-[140px]">Completitud documental</div>
            <div class="flex-1 bg-gray-200 rounded h-2 overflow-hidden">
                <div class="h-full transition-all duration-300" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
            </div>
            <span class="text-sm min-w-[32px]" style="font-family:monospace;color:{{ $pctColor }};">{{ $pct }}%</span>
        </div>
        
        <div class="flex items-center gap-2">
            <span class="text-sm text-[#806f56]">Estado actual:</span>
            @include('admin.crm._partials.badge', ['s' => $etapa])
        </div>
    </div>
</div>
@endsection
