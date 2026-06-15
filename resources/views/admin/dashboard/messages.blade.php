@extends('layouts.main_user')

@section('content')
<div class="flex-1 bg-gray-50 p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Mensajes') }}</h1>
        <p class="text-gray-600">{{ __('Comunícate con tu broker') }}</p>
    </div>

    <!-- Messages Section -->
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

                <div class="space-y-4">
                    @php
                        // Generar mensajes basados en el estado de la reserva
                        $messages = [
                            ['from' => 'broker', 'name' => 'Carlos M.', 'avatar' => 'CM', 'text' => 'Hola ' . Auth::user()->name . '! Tu reserva para la unidad ' . ($reservation->unit_name ?? 'Unit ' . $reservation->unit_id) . ' ha sido recibida. Te contactaré pronto con los siguientes pasos.', 'time' => $reservation->created_at->format('H:i')],
                        ];
                        
                        if ($reservation->profession) {
                            $messages[] = ['from' => 'broker', 'name' => 'Carlos M.', 'avatar' => 'CM', 'text' => 'Gracias por completar tu formulario. He recibido toda tu información y estamos procesando tu reserva.', 'time' => $reservation->updated_at->format('H:i')];
                        }
                        
                        if ($reservation->id_document_path) {
                            $messages[] = ['from' => 'broker', 'name' => 'Carlos M.', 'avatar' => 'CM', 'text' => 'He recibido tu documento de identidad. Todo está en orden para continuar con el proceso.', 'time' => $reservation->updated_at->format('H:i')];
                        }
                        
                        if ($reservation->status == 'confirmed') {
                            $messages[] = ['from' => 'broker', 'name' => 'Carlos M.', 'avatar' => 'CM', 'text' => '¡Excelente noticia! Tu reserva ha sido confirmada. Te enviaré los detalles del plan de pagos.', 'time' => $reservation->updated_at->format('H:i')];
                        }
                    @endphp

                    @foreach($messages as $msg)
                <div class="flex @if($msg['from'] == 'client') justify-end @else justify-start @endif">
                    <div class="max-w-xs @if($msg['from'] == 'client') bg-blue-600 text-white @else bg-gray-100 text-gray-900 @endif rounded-lg p-3">
                        <div class="flex items-center space-x-2 mb-1">
                            <div class="w-6 h-6 rounded-full @if($msg['from'] == 'client') bg-blue-700 @else bg-gray-300 @endif flex items-center justify-center text-xs font-medium">
                                @if($msg['from'] == 'client')
                                    <svg class="material-design-icon__svg" width="12" height="12" viewBox="0 0 24 24">
                                        <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"></path>
                                    </svg>
                                @else
                                    <svg class="material-design-icon__svg" width="12" height="12" viewBox="0 0 24 24">
                                        <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="text-xs @if($msg['from'] == 'client') text-blue-100 @else text-gray-600 @endif font-medium">
                                {{ $msg['name'] }}
                            </div>
                        </div>
                        <div class="text-sm">{{ $msg['text'] }}</div>
                        <div class="text-xs @if($msg['from'] == 'client') text-blue-100 @else text-gray-500 @endif mt-1">
                            {{ $msg['time'] }}
                        </div>
                    </div>
                </div>
                    @endforeach

                    <!-- Message Input -->
                    <div class="flex items-center space-x-2 pt-4 border-t">
                        <input type="text" placeholder="{{ __('Escribe un mensaje a Carlos...') }}" class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Enviar
                        </button>
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
