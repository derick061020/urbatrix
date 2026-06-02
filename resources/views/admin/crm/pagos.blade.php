@extends('layouts.main_admin')
@section('title', 'Selector de Pagos - Admin Panel')
@php 
    $activeRoute = 'crm.pagos'; 
    
    // Calculate payment plan using the helper
    $paymentBreakdown = \App\Helpers\PaymentPlanHelper::calculatePaymentBreakdown($reservation);
    
    // Create payment plan structure
    $paymentPlan = [];
    
    // Initial payment
    if ($paymentBreakdown['pago_inicial'] > 0) {
        $paymentPlan[] = [
            'type' => 'initial',
            'label' => 'Pago Inicial',
            'amount' => $paymentBreakdown['pago_inicial'],
            'percentage' => $paymentBreakdown['porcentaje_inicial'],
            'description' => 'Pago inicial con costos legales incluidos',
            'installment_number' => null,
            'due_date' => $reservation->created_at->addDays(7)->format('Y-m-d'),
            'order' => 1
        ];
    }
    
    // Construction installments
    if ($paymentBreakdown['cantidad_cuotas'] > 0) {
        for ($i = 1; $i <= $paymentBreakdown['cantidad_cuotas']; $i++) {
            $paymentPlan[] = [
                'type' => 'installment',
                'label' => "Cuota {$i} de Construcción",
                'amount' => $paymentBreakdown['cuota'],
                'percentage' => round($paymentBreakdown['porcentaje_construccion'] / $paymentBreakdown['cantidad_cuotas'], 2),
                'description' => "Cuota mensual de construcción",
                'installment_number' => $i,
                'due_date' => $reservation->created_at->addMonths($i)->format('Y-m-d'),
                'order' => 10 + $i
            ];
        }
    }
    
    // Delivery payment
    if ($paymentBreakdown['pago_entrega'] > 0) {
        $paymentPlan[] = [
            'type' => 'delivery',
            'label' => 'Pago de Entrega',
            'amount' => $paymentBreakdown['pago_entrega'],
            'percentage' => $paymentBreakdown['porcentaje_entrega'],
            'description' => 'Pago final de entrega',
            'installment_number' => null,
            'due_date' => $reservation->created_at->addMonths($paymentBreakdown['cantidad_cuotas'] + 6)->format('Y-m-d'),
            'order' => 100
        ];
    }
    
    // Sort by order
    usort($paymentPlan, function($a, $b) {
        return $a['order'] <=> $b['order'];
    });
    
    // Get existing payments and map them to plan
    $existingPayments = $payments->keyBy(function($payment) {
        return $payment->payment_type . '_' . ($payment->installment_number ?? '0');
    });
    
    // Calculate statistics
    $totalPlanAmount = array_sum(array_column($paymentPlan, 'amount'));
    $paidAmount = $payments->where('status', 'paid')->sum('amount');
    $pendingAmount = $totalPlanAmount - $paidAmount;
    $paidCount = $payments->where('status', 'paid')->count();
    $pendingCount = $payments->where('status', 'pending')->count();
    $overdueCount = $payments->where('status', 'overdue')->count() + 
                   $payments->where('status', 'pending')->filter(function($p) {
                       return $p->due_date < now();
                   })->count();
    
@endphp

@section('content')
<div class="w-full bg-[#f9f8f6] px-2 py-4 sm:p-10 overflow-auto min-h-screen">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-4xl font-semibold text-[#625441]">Selector de Pagos</h1>
                <p class="text-[#625441] mt-1">Administración de pagos según plan de financiamiento</p>
            </div>
            <div class="flex gap-3">
                <a href="/admin/crm/expedientes" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
        
        <!-- Expediente Info -->
        <div class="bg-white rounded-lg p-4 border border-gray-200 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div>
                        <span class="text-xs text-[#9b9b9b]" style="font-family:monospace;">EXP-{{ str_pad($reservation->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <h2 class="text-xl font-semibold text-[#625441]">{{ $reservation->first_name }} {{ $reservation->last_name }}</h2>
                        <div class="text-sm text-[#806f56]" style="font-family:monospace;">
                            {{ $reservation->unit_name ?? optional($reservation->unit)->name ?? 'Sin unidad' }}
                            @if($reservation->reservation_code) · Código: {{ $reservation->reservation_code }} @endif
                            · Precio: ${{ number_format($reservation->unit_price ?? 0, 2, '.', ',') }}
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="openWireTransferModal()" class="border border-[#667b6a] text-[#667b6a] rounded px-3 py-1.5 text-xs font-semibold hover:bg-[#667b6a]/10">Datos transferencia</button>
                    <a href="/dashboard?reservation={{ $reservation->id }}" class="bg-[#667b6a] text-white rounded px-3 py-1.5 text-xs font-semibold hover:bg-[#5a6d5e]">Ver Expediente</a>
                </div>
            </div>
        </div>
        
        <!-- Payment Plan Summary -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 mb-6">
            <h3 class="text-lg font-semibold text-[#625441] mb-4">Resumen del Plan de Pagos</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-[#667b6a]">{{ $paymentBreakdown['porcentaje_inicial'] }}%</div>
                    <div class="text-sm text-[#806f56]">Inicial</div>
                    <div class="text-xs text-gray-500">${{ number_format($paymentBreakdown['pago_inicial'], 2, '.', ',') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-[#667b6a]">{{ $paymentBreakdown['cantidad_cuotas'] }}</div>
                    <div class="text-sm text-[#806f56]">Cuotas</div>
                    <div class="text-xs text-gray-500">${{ number_format($paymentBreakdown['cuota'], 2, '.', ',') }} c/u</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-[#667b6a]">{{ $paymentBreakdown['porcentaje_entrega'] }}%</div>
                    <div class="text-sm text-[#806f56]">Entrega</div>
                    <div class="text-xs text-gray-500">${{ number_format($paymentBreakdown['pago_entrega'], 2, '.', ',') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ round(($paidAmount / $totalPlanAmount) * 100, 1) }}%</div>
                    <div class="text-sm text-[#806f56]">Progreso</div>
                    <div class="text-xs text-gray-500">${{ number_format($paidAmount, 2, '.', ',') }} pagados</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-600">Pagados</p>
                    <p class="text-lg font-semibold text-green-900">{{ $paidCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-600">Pendientes</p>
                    <p class="text-lg font-semibold text-blue-900">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-600">Vencidos</p>
                    <p class="text-lg font-semibold text-red-900">{{ $overdueCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-purple-600">Total Recaudado</p>
                    <p class="text-lg font-semibold text-purple-900">${{ number_format($paidAmount, 2, '.', ',') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Timeline -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Plan de Pagos</h3>
                <div class="flex gap-2">
                    <button onclick="expandAllPayments()" class="text-sm bg-gray-200 text-gray-700 px-3 py-1 rounded hover:bg-gray-300">Expandir Todos</button>
                    <button onclick="collapseAllPayments()" class="text-sm bg-gray-200 text-gray-700 px-3 py-1 rounded hover:bg-gray-300">Contraer Todos</button>
                    <button onclick="exportPayments()" class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">Exportar</button>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Progreso del Plan</span>
                    <span>{{ round(($paidAmount / $totalPlanAmount) * 100, 1) }}% Completado</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-3 rounded-full transition-all duration-500" style="width: {{ round(($paidAmount / $totalPlanAmount) * 100, 1) }}%"></div>
                </div>
            </div>
            
            <!-- Payment Items -->
            <div class="space-y-4">
                @foreach($paymentPlan as $index => $paymentItem)
                    @php
                        $paymentKey = $paymentItem['type'] . '_' . ($paymentItem['installment_number'] ?? '0');
                        $existingPayment = $existingPayments->get($paymentKey);
                        $isPaid = $existingPayment && $existingPayment->status === 'paid';
                        $isPending = $existingPayment && $existingPayment->status === 'pending';
                        $isOverdue = $existingPayment && ($existingPayment->status === 'overdue' || ($existingPayment->status === 'pending' && $existingPayment->due_date < now()));
                        $statusColor = $isPaid ? 'green' : ($isOverdue ? 'red' : 'blue');
                        $statusIcon = $isPaid ? 'check-circle' : ($isOverdue ? 'exclamation-circle' : 'clock');
                    @endphp
                    
                    <div class="payment-item border border-gray-200 rounded-lg overflow-hidden {{ $isPaid ? 'bg-green-50' : ($isOverdue ? 'bg-red-50' : 'bg-white') }}" data-payment-id="{{ $existingPayment->id ?? 0 }}">
                        <div class="p-4">
                            <div class="flex items-center justify-between cursor-pointer" onclick="togglePaymentDetails({{ $index }})">
                                <div class="flex items-center gap-4">
                                    <!-- Status Icon -->
                                    <div class="w-10 h-10 bg-{{ $statusColor }}-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-{{ $statusColor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    
                                    <!-- Payment Info -->
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <h4 class="font-semibold text-gray-800">{{ $paymentItem['label'] }}</h4>
                                            <span class="px-2 py-1 text-xs bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 rounded-full">
                                                {{ $isPaid ? 'Pagado' : ($isOverdue ? 'Vencido' : 'Pendiente') }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            {{ $paymentItem['description'] }} · {{ $paymentItem['percentage'] }}% del total
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Amount and Actions -->
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-gray-800">${{ number_format($paymentItem['amount'], 2, '.', ',') }}</div>
                                        <div class="text-sm text-gray-500">Vence: {{ \Carbon\Carbon::parse($paymentItem['due_date'])->format('d/m/Y') }}</div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex gap-2">
                                        @if($isPaid && $existingPayment)
                                            <a href="{{ route('payments.receipt', $existingPayment) }}" target="_blank" class="bg-[#667b6a] text-white px-3 py-1 rounded text-sm hover:bg-[#5a6d5e]">
                                                Comprobante
                                            </a>
                                        @endif
                                        @if(!$isPaid)
                                            <button onclick="markAsPaid({{ $existingPayment->id ?? 0 }}, '{{ $paymentItem['type'] }}', '{{ $paymentItem['installment_number'] ?? '' }}', {{ $paymentItem['amount'] }}, '{{ $paymentItem['due_date'] }}')" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                                Marcar Pagado
                                            </button>
                                        @endif
                                        
                                        <button onclick="editPayment({{ $existingPayment->id ?? 0 }})" class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700">
                                            Editar
                                        </button>
                                        
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Expandable Details -->
                            <div id="payment-details-{{ $index }}" class="hidden mt-4 pt-4 border-t border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h5 class="font-semibold text-gray-700 mb-2">Detalles del Pago</h5>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Tipo:</span>
                                                <span class="font-medium">{{ ucfirst($paymentItem['type']) }}</span>
                                            </div>
                                            @if($paymentItem['installment_number'])
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Cuota N°:</span>
                                                    <span class="font-medium">{{ $paymentItem['installment_number'] }}</span>
                                                </div>
                                            @endif
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Monto:</span>
                                                <span class="font-medium">${{ number_format($paymentItem['amount'], 2, '.', ',') }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Porcentaje:</span>
                                                <span class="font-medium">{{ $paymentItem['percentage'] }}%</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Vencimiento:</span>
                                                <span class="font-medium">{{ \Carbon\Carbon::parse($paymentItem['due_date'])->format('d/m/Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($existingPayment)
                                        <div>
                                            <h5 class="font-semibold text-gray-700 mb-2">Estado del Pago</h5>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Estado:</span>
                                                    <span class="font-medium">{{ $existingPayment->getStatusLabel() }}</span>
                                                </div>
                                                @if($existingPayment->payment_method)
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600">Método:</span>
                                                        <span class="font-medium">{{ $existingPayment->getPaymentMethodLabel() }}</span>
                                                    </div>
                                                @endif
                                                @if($existingPayment->paid_at)
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600">Fecha Pago:</span>
                                                        <span class="font-medium">{{ \Carbon\Carbon::parse($existingPayment->paid_at)->format('d/m/Y H:i') }}</span>
                                                    </div>
                                                @endif
                                                @if($existingPayment->receipt_path)
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600">Comprobante:</span>
                                                        <a href="{{ $existingPayment->receipt_path }}" target="_blank" class="text-blue-600 hover:underline">Ver Comprobante</a>
                                                    </div>
                                                @endif
                                                @if($existingPayment->notes)
                                                    <div>
                                                        <span class="text-gray-600">Notas:</span>
                                                        <p class="mt-1 text-gray-700">{{ $existingPayment->notes }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white" id="modalTitle">Registrar Pago</h3>
                <button onclick="closePaymentModal()" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="flex items-stretch">
            <div class="flex-1 min-w-0 p-6">
                <form id="paymentForm" class="space-y-4">
                    <input type="hidden" name="payment_id" id="paymentId">
                    <input type="hidden" name="payment_type" id="paymentType">
                    <input type="hidden" name="installment_number" id="installmentNumber">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <input type="text" name="label" id="paymentLabel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monto</label>
                            <input type="number" name="amount" id="paymentAmount" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Vencimiento</label>
                            <input type="date" name="due_date" id="paymentDueDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="status" id="paymentStatus" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending">Pendiente</option>
                                <option value="paid">Pagado</option>
                                <option value="overdue">Vencido</option>
                                <option value="cancelled">Cancelado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                            <select name="payment_method" id="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccionar...</option>
                                <option value="cash">Efectivo</option>
                                <option value="transfer">Transferencia</option>
                                <option value="check">Cheque</option>
                                <option value="card">Tarjeta</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Pago</label>
                            <input type="datetime-local" name="paid_at" id="paymentPaidAt" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                        <textarea name="notes" id="paymentNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Notas adicionales..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Boleta/Comprobante de Pago</label>
                        <input type="file" name="payment_receipt" id="paymentReceipt" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Formatos aceptados: PDF, JPG, PNG. Máximo 5MB.</p>
                    </div>
                    
                    @if(isset($existingPayment) && $existingPayment->receipt_path)
                    <div class="bg-gray-50 p-3 rounded-md">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Comprobante Actual</label>
                        <div class="flex items-center gap-3">
                            <a href="{{ asset($existingPayment->receipt_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm underline">
                                Ver comprobante
                            </a>
                            <button type="button" onclick="removeReceipt()" class="text-red-600 hover:text-red-800 text-sm">
                                Eliminar
                            </button>
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="openWireTransferModal()" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Datos Transferencia
                        </button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Guardar Pago</button>
                        <button type="button" onclick="closePaymentModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">Cancelar</button>
                    </div>
                </form>
            </div>
            @include('_partials.bank_panel')
            </div>
        </div>
    </div>
</div>

<!-- Wire Transfer Modal -->
<div id="wireTransferModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Datos para Transferencia en USD</h3>
                <button onclick="closeWireTransferModal()" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="wireTransferContent" style="width:794px;max-width:90vw;background:#f0efec">
                <div class="text-center py-8">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <div class="text-sm text-gray-500 mt-2">Cargando datos...</div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2 bg-gray-50">
                <button onclick="downloadWireTransferPDF()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Descargar PDF
                </button>
                <button onclick="closeWireTransferModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentReservationId = {{ $reservation->id }};
let paymentPlan = @json($paymentPlan);
let existingPayments = @json($existingPayments);
let wireTransferUrl = "{{ route('reservations.wire', $reservation) }}";

// Open wire transfer modal — render the print sheet inside an iframe so its own
// CSS (defined in the document <head>) is preserved and the design shows correctly.
function openWireTransferModal() {
    const modal = document.getElementById('wireTransferModal');
    const content = document.getElementById('wireTransferContent');

    modal.classList.remove('hidden');
    content.innerHTML = `<iframe id="wire-iframe" src="${wireTransferUrl}" title="Datos para transferencia en USD" style="width:794px;max-width:90vw;height:72vh;border:0;display:block;background:#fff"></iframe>`;
}

// Close wire transfer modal
function closeWireTransferModal() {
    document.getElementById('wireTransferModal').classList.add('hidden');
}

// Download wire transfer PDF — print the already-loaded iframe.
function downloadWireTransferPDF() {
    const frame = document.getElementById('wire-iframe');
    if (frame && frame.contentWindow) {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    } else {
        window.open(wireTransferUrl, '_blank');
    }
}

// Toggle payment details
function togglePaymentDetails(index) {
    const details = document.getElementById(`payment-details-${index}`);
    const arrow = event.currentTarget.querySelector('svg');
    
    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        details.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

// Expand/collapse all
function expandAllPayments() {
    document.querySelectorAll('[id^="payment-details-"]').forEach(el => {
        el.classList.remove('hidden');
    });
    document.querySelectorAll('.payment-item svg').forEach(el => {
        el.classList.add('rotate-180');
    });
}

function collapseAllPayments() {
    document.querySelectorAll('[id^="payment-details-"]').forEach(el => {
        el.classList.add('hidden');
    });
    document.querySelectorAll('.payment-item svg').forEach(el => {
        el.classList.remove('rotate-180');
    });
}

// Payment modal functions
function markAsPaid(paymentId, type, installmentNumber, amount, dueDate) {
    if (paymentId === 0) {
        // Create new payment
        document.getElementById('modalTitle').textContent = 'Registrar Pago';
        document.getElementById('paymentId').value = '';
        document.getElementById('paymentType').value = type;
        document.getElementById('installmentNumber').value = installmentNumber || '';
        document.getElementById('paymentLabel').value = paymentPlan.find(p => p.type === type && (p.installment_number || 0) == (installmentNumber || 0))?.label || '';
        document.getElementById('paymentAmount').value = amount;
        document.getElementById('paymentDueDate').value = dueDate;
        document.getElementById('paymentStatus').value = 'paid';
        document.getElementById('paymentPaidAt').value = new Date().toISOString().slice(0, 16);
    } else {
        // Update existing payment
        document.getElementById('modalTitle').textContent = 'Actualizar Pago';
        const payment = existingPayments.find(p => p.id == paymentId);
        if (payment) {
            document.getElementById('paymentId').value = paymentId;
            document.getElementById('paymentType').value = payment.payment_type;
            document.getElementById('installmentNumber').value = payment.installment_number || '';
            document.getElementById('paymentLabel').value = payment.label;
            document.getElementById('paymentAmount').value = payment.amount;
            document.getElementById('paymentDueDate').value = payment.due_date;
            document.getElementById('paymentStatus').value = payment.status;
            document.getElementById('paymentMethod').value = payment.payment_method || '';
            document.getElementById('paymentPaidAt').value = payment.paid_at ? new Date(payment.paid_at).toISOString().slice(0, 16) : '';
            document.getElementById('paymentNotes').value = payment.notes || '';
        }
    }
    
    document.getElementById('paymentModal').classList.remove('hidden');
}

function editPayment(paymentId) {
    markAsPaid(paymentId, '', '', 0, '');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentForm').reset();
}

function removeReceipt() {
    if (confirm('¿Está seguro de eliminar el comprobante de pago?')) {
        // Add logic to remove receipt via AJAX
        fetch('/payments/remove-receipt', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                payment_id: document.getElementById('paymentId').value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al eliminar comprobante: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar comprobante');
        });
    }
}

// Form submission
document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    const paymentId = data.payment_id;
    delete data.payment_id;
    
    try {
        const url = paymentId ? `/admin/api/payments/${paymentId}` : `/admin/api/reservations/${currentReservationId}/payments`;
        const method = paymentId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            closePaymentModal();
            location.reload();
            showNotification('Pago guardado exitosamente', 'success');
        } else {
            const error = await response.json();
            showNotification('Error: ' + error.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al guardar pago', 'error');
    }
});

// Export function
function exportPayments() {
    const payments = [];
    
    paymentPlan.forEach(item => {
        const key = item.type + '_' + (item.installment_number || '0');
        const existing = existingPayments[key];
        
        payments.push({
            'Tipo': item.label,
            'Descripción': item.description,
            'Monto': item.amount,
            'Porcentaje': item.percentage + '%',
            'Vencimiento': item.due_date,
            'Estado': existing ? (existing.status === 'paid' ? 'Pagado' : 'Pendiente') : 'No registrado',
            'Fecha Pago': existing?.paid_at || '',
            'Método': existing?.payment_method || ''
        });
    });
    
    let csv = 'Tipo,Descripción,Monto,Porcentaje,Vencimiento,Estado,Fecha Pago,Método\n';
    payments.forEach(p => {
        csv += `"${p.Tipo}","${p.Descripción}","${p.Monto}","${p.Porcentaje}","${p.Vencimiento}","${p.Estado}","${p['Fecha Pago']}","${p.Método}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `plan_pagos_${currentReservationId}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    
    showNotification('Plan de pagos exportado', 'success');
}

// Notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Close modal on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePaymentModal();
    }
});
</script>
@stop
