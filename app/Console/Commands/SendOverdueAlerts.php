<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\CrmDispatcher;
use Illuminate\Console\Command;

/**
 * Aviso interno de mora (E-10): por cada reserva con cuota(s) vencida(s) sin
 * pagar, notifica al equipo. Se envía una sola vez por cuota vencida (se marca
 * con overdue_notified_at), agrupando por reserva para no duplicar el aviso.
 */
class SendOverdueAlerts extends Command
{
    protected $signature = 'crm:send-overdue-alerts';
    protected $description = 'Notifica al equipo (E-10) las cuotas vencidas sin pagar.';

    public function handle(): int
    {
        $overdue = Payment::query()
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->whereNull('overdue_notified_at')
            ->with('reservation.unit')
            ->get()
            ->groupBy('reservation_id');

        $sent = 0;
        foreach ($overdue as $payments) {
            $reservation = $payments->first()->reservation;
            if ($reservation) {
                $sent += CrmDispatcher::event('payment_overdue', ['reservation' => $reservation]);
            }
            // Marca todas las cuotas vencidas de esa reserva como notificadas.
            Payment::whereIn('id', $payments->pluck('id'))->update(['overdue_notified_at' => now()]);
        }

        $this->info("Avisos de mora: {$overdue->count()} reservas con mora, {$sent} correos enviados.");

        return self::SUCCESS;
    }
}
