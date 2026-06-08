<?php

namespace App\Support;

use App\Models\Payment;
use Illuminate\Support\Carbon;

/**
 * Arma los datos del comprobante de pago a partir de un Payment.
 * Fuente única para el PDF imprimible y el correo de comprobante.
 */
class PaymentReceiptData
{
    private const METHODS = [
        'cash'     => 'Efectivo',
        'transfer' => 'Transferencia bancaria',
        'card'     => 'Tarjeta',
        'check'    => 'Cheque',
        'stripe'   => 'Tarjeta (Stripe)',
    ];

    public static function build(Payment $payment): array
    {
        $payment->loadMissing('reservation.unit.project', 'reservation.user');
        $reservation = $payment->reservation;

        $unit    = $reservation->unit;
        $price   = (float) ($unit->price ?? $reservation->unit_price ?? 0);
        $paid    = (float) $reservation->payments()->where('status', 'paid')->sum('amount');
        $balance = max(0, $price - $paid);

        $next = $reservation->payments()
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();

        return [
            'numero_comprobante' => 'REC-' . ($reservation->reservation_code ?: $reservation->id) . '-' . str_pad((string) ($payment->installment_number ?: $payment->id), 3, '0', STR_PAD_LEFT),
            'fecha_emision'      => now()->format('d/m/Y'),
            'fecha_pago'         => optional($payment->paid_at ?? $payment->created_at)->format('d/m/Y'),

            'nombre_cliente'     => trim(($reservation->first_name ?? '') . ' ' . ($reservation->last_name ?? '')) ?: ($reservation->user->name ?? 'Cliente'),
            'documento_cliente'  => trim(($reservation->id_type ? $reservation->id_type . ' ' : '') . ($reservation->document_number ?? '')) ?: '—',
            'email_cliente'      => $reservation->email ?? $reservation->user->email ?? '—',
            'telefono_cliente'   => $reservation->phone ?? '—',
            'proyecto'           => $unit->project->name ?? config('company.project'),
            'unidad'             => $reservation->unit_name ?? $unit->custom_id ?? $unit->name ?? '—',
            'numero_contrato'    => $reservation->reservation_code ?? '—',
            'nombre_asesor'      => $reservation->budget_configured_by ?? '—',

            'moneda'             => 'USD',
            'monto'              => number_format((float) $payment->amount, 2, '.', ','),
            'monto_en_letras'    => SpanishNumber::money((float) $payment->amount),
            'concepto_pago'      => $payment->label ?: ($payment->payment_type === 'reservation' ? 'Reserva' : 'Cuota'),

            'metodo_pago'        => $payment->payment_method
                                        ? (self::METHODS[$payment->payment_method] ?? ucfirst($payment->payment_method))
                                        : '—',
            'referencia'         => $reservation->stripe_payment_intent && $payment->payment_type === 'reservation'
                                        ? $reservation->stripe_payment_intent
                                        : 'PAY-' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
            'cuenta_receptora'   => config('company.bank.account_holder') . ' · ' . config('company.bank.account_number'),

            'precio_total'       => number_format($price, 2, '.', ','),
            'total_pagado'       => number_format($paid, 2, '.', ','),
            'saldo_pendiente'    => number_format($balance, 2, '.', ','),
            'fecha_proxima_cuota'=> $next && $next->due_date ? Carbon::parse($next->due_date)->format('d/m/Y') : '—',
            'monto_proxima_cuota'=> $next ? number_format((float) $next->amount, 2, '.', ',') : '—',

            'link_comprobante'   => route('payments.receipt', $payment),
            'link_portal'        => route('dashboard.payments'),

            // Firma manuscrita del comprador (constancia de recepción), si ya firmó.
            'firma_cliente'      => $payment->receipt_signature,
            'firma_cliente_nombre' => $payment->receipt_signer_name,
            'firma_cliente_fecha'  => $payment->receipt_signed_at?->format('d/m/Y H:i'),
        ];
    }
}
