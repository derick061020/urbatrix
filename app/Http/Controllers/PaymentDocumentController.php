<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Support\PaymentReceiptData;
use Illuminate\Http\Request;

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
     * Registra la firma manuscrita del comprador sobre el comprobante.
     * Queda persistida en el pago para que no se pierda al recargar y para
     * dejar constancia (con fecha y nombre) de la recepción del comprobante.
     */
    public function signReceipt(Request $request, Payment $payment)
    {
        $payment->load('reservation');
        abort_unless($payment->reservation, 404);
        $this->authorizeAccess($payment->reservation);

        $data = $request->validate([
            'signer_name'     => ['required', 'string', 'min:3', 'max:120'],
            'signature_image' => ['required', 'string'],
        ]);

        // La firma llega como data URL PNG (base64). Validamos el formato básico.
        if (! preg_match('/^data:image\/png;base64,/', $data['signature_image'])) {
            return response()->json([
                'success' => false,
                'message' => __('La firma no tiene un formato válido.'),
            ], 422);
        }

        $payment->forceFill([
            'receipt_signature'   => $data['signature_image'],
            'receipt_signer_name' => $data['signer_name'],
            'receipt_signed_at'   => now(),
        ])->save();

        return response()->json([
            'success'   => true,
            'message'   => __('Comprobante firmado.'),
            'signed_at' => $payment->receipt_signed_at->format('d/m/Y H:i'),
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
