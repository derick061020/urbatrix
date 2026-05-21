<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Payment;
use App\Helpers\PaymentPlanHelper;
use Carbon\Carbon;
use DateTime;
use DateInterval;

class PaymentService
{
    /**
     * Generate all payments for a reservation after contract approval
     */
    public static function generatePayments(Reservation $reservation)
    {
        // Delete existing payments for this reservation
        $reservation->payments()->delete();
        
        // Get payment breakdown
        $breakdown = PaymentPlanHelper::calculatePaymentBreakdown($reservation);
        
        $payments = [];
        
        // Use reservation creation date or today as fallback
        $startDate = $reservation->created_at ?? now();
        if (!($startDate instanceof \Carbon\Carbon)) {
            $startDate = \Carbon\Carbon::parse($startDate);
        }
        
        // 1. Pago Inicial
        $payments[] = [
            'payment_type' => 'initial',
            'installment_number' => null,
            'label' => 'Pago Inicial (' . $breakdown['porcentaje_inicial'] . '% + $' . number_format($breakdown['costos_legales'], 0) . ' legales)',
            'amount' => $breakdown['pago_inicial'],
            'due_date' => $startDate->format('Y-m-d'),
            'status' => 'pending',
        ];
        
        // 2. Pagos de Construcción (cuotas o pago único)
        if ($breakdown['cantidad_cuotas'] > 0) {
            // Generar cuotas individuales
            $fechaInicio = clone $startDate;
            for ($i = 1; $i <= $breakdown['cantidad_cuotas']; $i++) {
                $fecha = clone $fechaInicio;
                $fecha->add(new \DateInterval('P' . $i . 'M'));
                
                $payments[] = [
                    'payment_type' => 'installment',
                    'installment_number' => $i,
                    'label' => 'Cuota ' . $i . ' de ' . $breakdown['cantidad_cuotas'],
                    'amount' => $breakdown['cuota'],
                    'due_date' => $fecha->format('Y-m-d'),
                    'status' => 'pending',
                ];
            }
        } elseif ($breakdown['pago_construccion'] > 0) {
            // Pago único de construcción
            $dueDate = clone $startDate;
            $dueDate->addMonths(6);
            $payments[] = [
                'payment_type' => 'construction',
                'installment_number' => null,
                'label' => 'Pago en Construcción (' . $breakdown['porcentaje_construccion'] . '%)',
                'amount' => $breakdown['pago_construccion'],
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => 'pending',
            ];
        }
        
        // 3. Pago de Entrega
        if ($breakdown['pago_entrega'] > 0) {
            $dueDate = clone $startDate;
            $dueDate->addYear();
            $payments[] = [
                'payment_type' => 'delivery',
                'installment_number' => null,
                'label' => 'Pago en Entrega (' . $breakdown['porcentaje_entrega'] . '%)',
                'amount' => $breakdown['pago_entrega'],
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => 'pending',
            ];
        }
        
        // Create all payments
        foreach ($payments as $paymentData) {
            $reservation->payments()->create($paymentData);
        }
        
        return $reservation->payments()->count();
    }
    
    /**
     * Check if reservation can have payments (contract signed)
     */
    public static function canGeneratePayments(Reservation $reservation)
    {
        return in_array($reservation->status, ['contract_signed', 'signed']);
    }
    
    /**
     * Get payment status for display
     */
    public static function getPaymentStatus(Payment $payment)
    {
        if ($payment->isPaid()) {
            return 'paid';
        }
        
        if ($payment->isOverdue()) {
            return 'overdue';
        }
        
        if ($payment->due_date > now()) {
            return 'future';
        }
        
        return 'pending';
    }
    
    /**
     * Process payment upload
     */
    public static function processPayment(Payment $payment, $receiptFile, $paymentMethod = 'transfer')
    {
        if (!$receiptFile) {
            throw new \Exception('Debe adjuntar un comprobante de pago');
        }
        
        // Upload receipt
        $filename = 'receipt_' . $payment->id . '_' . time() . '.' . $receiptFile->getClientOriginalExtension();
        $receiptFile->move(public_path('receipts'), $filename);
        $receiptPath = 'receipts/' . $filename;
        
        // Mark payment as paid
        $payment->markAsPaid($paymentMethod, $receiptPath);
        
        return $payment;
    }
    
    /**
     * Check if all payments are paid
     */
    public static function isFullyPaid(Reservation $reservation)
    {
        $totalPayments = $reservation->payments()->count();
        $paidPayments = $reservation->payments()->where('status', 'paid')->count();
        
        return $totalPayments > 0 && $totalPayments === $paidPayments;
    }
    
    /**
     * Get payment summary
     */
    public static function getPaymentSummary(Reservation $reservation)
    {
        $payments = $reservation->payments;
        
        return [
            'total' => $payments->count(),
            'paid' => $payments->where('status', 'paid')->count(),
            'pending' => $payments->where('status', 'pending')->count(),
            'overdue' => $payments->where('status', 'overdue')->count(),
            'total_amount' => $payments->sum('amount'),
            'paid_amount' => $payments->where('status', 'paid')->sum('amount'),
            'pending_amount' => $payments->where('status', 'pending')->sum('amount'),
        ];
    }
}
