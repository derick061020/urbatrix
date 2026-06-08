<?php

namespace App\Services;

use App\Mail\CrmTemplateMail;
use App\Models\CrmAutomation;
use App\Models\CrmChannelSetting;
use App\Models\CrmTemplate;
use App\Models\Reservation;
use App\Models\User;
use App\Support\CrmTemplateRenderer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Motor de automatizaciones del CRM: ante un evento del flujo, busca las
 * automatizaciones activas que lo escuchan, resuelve destinatarios según la
 * audiencia de la plantilla, renderiza y envía por los canales habilitados.
 */
class CrmDispatcher
{
    /**
     * Atajo de uso en los puntos del flujo:
     *   CrmDispatcher::event('reservation_confirmed', ['reservation' => $r]);
     */
    public static function event(string $event, array $models = []): int
    {
        return app(self::class)->fire($event, $models);
    }

    /**
     * Dispara todas las automatizaciones activas para $event.
     * Devuelve cuántos envíos (correos) se realizaron.
     */
    public function fire(string $event, array $models = []): int
    {
        $automations = CrmAutomation::with(['template', 'steps.template'])
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->get();

        if ($automations->isEmpty()) {
            return 0;
        }

        $vars = CrmTemplateRenderer::build($models);
        $emailEnabled = $this->channelEnabled('email');
        $sent = 0;

        foreach ($automations as $automation) {
            // Cadena de fases: cada paso espera "delay_minutes" respecto del paso
            // anterior, así que acumulamos el retraso desde el disparo del evento.
            $cumulativeDelay = 0;
            $delivered = false;

            foreach ($automation->resolvedSteps() as $step) {
                $cumulativeDelay += (int) ($step->delay_minutes ?? 0);

                $template = $step->template;
                if (! $template) {
                    continue;
                }

                $recipients = $this->recipientsFor($template, $models, $vars);
                $channels = $step->channels ?: $template->channels ?: ['email'];

                foreach ($channels as $channel) {
                    if ($channel === 'email') {
                        if (! $emailEnabled) {
                            continue;
                        }
                        foreach ($recipients as $to) {
                            if ($this->sendEmail($template, $vars, $to, $cumulativeDelay)) {
                                $sent++;
                                $delivered = true;
                            }
                        }
                    } else {
                        // WhatsApp / SMS / Push: sin proveedor configurado → se registra.
                        Log::info("[CRM] Canal '{$channel}' no implementado; paso de '{$automation->name}' omitido para ese canal.", [
                            'event'      => $event,
                            'template'   => $template->name,
                            'recipients' => $recipients,
                            'delay_min'  => $cumulativeDelay,
                        ]);
                    }
                }

                if ($delivered) {
                    $template->forceFill([
                        'last_used_at' => now(),
                        'usage_count'  => ($template->usage_count ?? 0) + 1,
                    ])->save();
                }
            }

            if ($delivered) {
                $automation->forceFill([
                    'last_run_at' => now(),
                    'run_count'   => ($automation->run_count ?? 0) + 1,
                ])->save();
            }
        }

        return $sent;
    }

    /** Renderiza y envía un correo. Devuelve true si se encoló/envió. */
    public function sendEmail(CrmTemplate $template, array $vars, ?string $to, int $delayMinutes = 0): bool
    {
        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $rendered = CrmTemplateRenderer::render($template, $vars);
            $mail = new CrmTemplateMail($rendered['subject'], $rendered['html']);

            if ($delayMinutes > 0) {
                Mail::to($to)->later(now()->addMinutes($delayMinutes), $mail);
            } else {
                Mail::to($to)->send($mail);
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('[CRM] No se pudo enviar plantilla "'.$template->name.'": '.$e->getMessage());
            return false;
        }
    }

    /** Resuelve los correos destinatarios según la audiencia de la plantilla. */
    private function recipientsFor(CrmTemplate $template, array $models, array $vars): array
    {
        $audience = $template->audience ?: 'client';
        $reservation = $models['reservation'] ?? null;

        return match ($audience) {
            'internal' => $this->internalRecipients($reservation),
            'broker'   => $this->brokerRecipients($models),
            default    => array_filter([$vars['cliente_email'] ?? ($reservation?->email)]),
        };
    }

    /** Admins del sistema + asesor de la unidad. */
    private function internalRecipients(?Reservation $reservation): array
    {
        $emails = User::where('role', 'admin')->pluck('email')->all();

        if ($reservation) {
            $reservation->loadMissing('unit.agent');
            if ($agentEmail = $reservation->unit?->agent?->email) {
                $emails[] = $agentEmail;
            }
        }

        return array_values(array_unique(array_filter($emails)));
    }

    /** Brokers asignados a la unidad de la reserva (o un broker explícito). */
    private function brokerRecipients(array $models): array
    {
        if (($b = $models['broker'] ?? null) instanceof User && $b->email) {
            return [$b->email];
        }

        $reservation = $models['reservation'] ?? null;
        if ($reservation) {
            $reservation->loadMissing('unit.brokers');
            return array_values(array_filter($reservation->unit?->brokers?->pluck('email')->all() ?? []));
        }

        return [];
    }

    private function channelEnabled(string $channel): bool
    {
        $setting = CrmChannelSetting::where('channel', $channel)->first();
        // Por defecto el email se considera habilitado si no hay registro.
        return $setting ? (bool) $setting->enabled : ($channel === 'email');
    }
}
