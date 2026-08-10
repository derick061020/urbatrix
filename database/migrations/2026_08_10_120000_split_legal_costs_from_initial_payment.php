<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Separa los costos legales de la cuota inicial en los planes ya generados.
 *
 * Hasta ahora la cuota "initial" agrupaba el porcentaje inicial + los costos
 * legales en un solo monto. A partir de PaymentService::generatePayments los
 * legales son una cuota propia (payment_type = 'legal'); este backfill hace lo
 * mismo con los expedientes existentes.
 *
 * Sólo toca cuotas iniciales intactas (sin ningún abono): si el cliente ya pagó
 * algo contra ese concepto, repartir el importe cambiaría el histórico, así que
 * se deja como está y el asesor puede corregirlo a mano desde el CRM.
 */
return new class extends Migration
{
    public function up(): void
    {
        $reservations = DB::table('reservations')
            ->select('id', 'legal_costs', 'payment_initial_percentage')
            ->where('legal_costs', '>', 0)
            ->get();

        foreach ($reservations as $reservation) {
            $legal = round((float) $reservation->legal_costs, 2);

            $alreadySplit = DB::table('payments')
                ->where('reservation_id', $reservation->id)
                ->where('payment_type', 'legal')
                ->exists();

            if ($alreadySplit) {
                continue;
            }

            $initial = DB::table('payments')
                ->where('reservation_id', $reservation->id)
                ->where('payment_type', 'initial')
                ->orderBy('id')
                ->first();

            if (! $initial
                || $initial->status === 'paid'
                || (float) $initial->paid_amount > 0
                || (float) $initial->amount <= $legal) {
                continue;
            }

            $pct = rtrim(rtrim(number_format((float) $reservation->payment_initial_percentage, 2, '.', ''), '0'), '.');

            DB::table('payments')->where('id', $initial->id)->update([
                'amount'     => round((float) $initial->amount - $legal, 2),
                'label'      => 'Pago inicial ('.$pct.'%)',
                'updated_at' => now(),
            ]);

            DB::table('payments')->insert([
                'reservation_id'     => $reservation->id,
                'payment_type'       => 'legal',
                'installment_number' => null,
                'label'              => 'Costos legales',
                'amount'             => $legal,
                'paid_amount'        => 0,
                'due_date'           => $initial->due_date,
                'status'             => $initial->status,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    public function down(): void
    {
        $legalPayments = DB::table('payments')
            ->where('payment_type', 'legal')
            ->where('paid_amount', 0)
            ->get();

        foreach ($legalPayments as $legal) {
            $initial = DB::table('payments')
                ->where('reservation_id', $legal->reservation_id)
                ->where('payment_type', 'initial')
                ->orderBy('id')
                ->first();

            if ($initial) {
                DB::table('payments')->where('id', $initial->id)->update([
                    'amount'     => round((float) $initial->amount + (float) $legal->amount, 2),
                    'updated_at' => now(),
                ]);
            }

            DB::table('payments')->where('id', $legal->id)->delete();
        }
    }
};
