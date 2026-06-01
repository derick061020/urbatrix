<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Support\PaymentReceiptData;

class PaymentDocumentController extends Controller
{
    /**
     * Comprobante de pago imprimible (A4) — #10 del briefing.
     * Accesible por el admin o por el comprador dueño de la reserva.
     */
    public function receipt(Payment $payment)
    {
        $payment->load('reservation.unit.project', 'reservation.user', 'approver');
        abort_unless($payment->reservation, 404);
        $this->authorizeAccess($payment->reservation);

        return view('print.payment-receipt', [
            'd'       => PaymentReceiptData::build($payment),
            'company' => config('company'),
        ]);
    }

    /**
     * Hoja de datos para transferencia en USD (A4) — #12 del briefing.
     * El bloque de "referencia/concepto" se prellena con el código de reserva.
     */
    public function wireInstructions(Reservation $reservation)
    {
        $this->authorizeAccess($reservation);

        $reference = $reservation->reservation_code
            ? $reservation->reservation_code . ' · ' . trim(($reservation->first_name ?? '') . ' ' . ($reservation->last_name ?? ''))
            : trim(($reservation->first_name ?? '') . ' ' . ($reservation->last_name ?? ''));

        return view('print.wire-transfer', [
            'company'   => config('company'),
            'reference' => $reference,
        ]);
    }

    /** Solo admin o el comprador dueño de la reserva. */
    private function authorizeAccess(Reservation $reservation): void
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->is_admin || $reservation->user_id === $user->id),
            403
        );
    }
}
