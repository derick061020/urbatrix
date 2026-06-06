<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\CrmDispatcher;
use Illuminate\Console\Command;

/**
 * Recordatorio KYC (E-02): a los clientes que pagaron la seña pero aún no
 * completaron su formulario KYC pasados N días. Se envía una sola vez por
 * reserva (se marca con kyc_reminded_at).
 */
class SendKycReminders extends Command
{
    protected $signature = 'crm:send-kyc-reminders {--days=2 : Días tras el pago antes de recordar}';
    protected $description = 'Envía el recordatorio de KYC pendiente (E-02) a las reservas pagadas sin KYC completado.';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $reservations = Reservation::query()
            ->whereNotNull('paid_at')
            ->where('status', 'pending')          // pagada la seña, KYC aún sin completar
            ->whereNull('kyc_reminded_at')
            ->where('paid_at', '<=', now()->subDays($days))
            ->with('unit')
            ->get();

        $sent = 0;
        foreach ($reservations as $reservation) {
            $delivered = CrmDispatcher::event('kyc_pending', ['reservation' => $reservation]);
            $reservation->forceFill(['kyc_reminded_at' => now()])->save();
            $sent += $delivered;
        }

        $this->info("Recordatorios KYC: {$reservations->count()} reservas procesadas, {$sent} correos enviados.");

        return self::SUCCESS;
    }
}
