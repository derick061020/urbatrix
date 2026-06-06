<?php

namespace App\Support;

use App\Models\ConstructionReport;
use App\Models\CrmTemplate;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;

/**
 * Renderiza las plantillas del CRM: sustituye los tokens {{var}} en asunto y
 * cuerpo, y envuelve el cuerpo en el layout compartido (emails.crm.wrapper).
 *
 * También construye el mapa de variables a partir de los modelos del flujo
 * (Reservation / Payment / ConstructionReport / User) + config/company.php,
 * reutilizando App\Support\PaymentReceiptData y App\Support\SpanishNumber.
 */
class CrmTemplateRenderer
{
    /** Valores de ejemplo para vista previa y envíos de prueba. */
    public const SAMPLE = [
        'nombre_cliente'         => 'Juan Pérez',
        'cliente_email'          => 'cliente@ejemplo.com',
        'nombre_profesional'     => 'María Broker',
        'nombre_asesor'          => 'Ana Gómez',
        'tel_asesor'             => '+1 809 555 0100',
        'proyecto'               => 'Makai Residences',
        'unidad'                 => 'A-204',
        'precio_venta'           => 'USD 285,000.00',
        'monto_reserva'          => 'USD 15,000.00',
        'monto_downpayment'      => 'USD 57,000.00',
        'monto_comision'         => 'USD 8,550.00',
        'monto'                  => '5,000.00',
        'moneda'                 => 'USD',
        'monto_en_letras'        => 'Cinco mil dólares con 00/100',
        'concepto_pago'          => 'Cuota de construcción',
        'metodo_pago'            => 'Transferencia bancaria',
        'referencia_transaccion' => 'PAY-000123',
        'fecha_pago'             => '06/06/2026',
        'fecha_vencimiento'      => '20/06/2026',
        'fecha_entrega'          => 'Diciembre 2027',
        'total_pagado'           => '72,000.00',
        'saldo_pendiente'        => '213,000.00',
        'pct_obra'               => '45',
        'mes_reporte'            => 'Mayo 2026',
        'num_fotos'              => '12',
        'hitos_actualizados'     => '3',
        'link_portal'            => 'https://makairesidences.com/dashboard',
        'link_comprobante'       => 'https://makairesidences.com/dashboard/payments',
    ];

    /**
     * Sustituye tokens y envuelve en el layout. Devuelve ['subject' => ..., 'html' => ...].
     */
    public static function render(CrmTemplate $template, array $vars = []): array
    {
        $subject = self::interpolate($template->subject ?? '', $vars);
        $content = self::interpolate($template->body ?? '', $vars);

        $html = View::make('emails.crm.wrapper', [
            'docLabel'  => $template->doc_label ?? '',
            'preheader' => $subject,
            'content'   => $content,
        ])->render();

        return ['subject' => $subject ?: config('company.project'), 'html' => $html];
    }

    /** Reemplaza {{token}} por su valor; tokens sin valor se dejan vacíos. */
    public static function interpolate(string $text, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', function ($m) use ($vars) {
            $key = $m[1];
            return array_key_exists($key, $vars) ? (string) $vars[$key] : '';
        }, $text);
    }

    /**
     * Construye el mapa de variables a partir de los modelos disponibles.
     * $models acepta llaves: reservation, payment, report, user, broker.
     */
    public static function build(array $models = []): array
    {
        $vars = self::company();

        if (($r = $models['reservation'] ?? null) instanceof Reservation) {
            $vars = array_merge($vars, self::fromReservation($r));
        }
        if (($p = $models['payment'] ?? null) instanceof Payment) {
            $vars = array_merge($vars, self::fromPayment($p));
        }
        if (($rep = $models['report'] ?? null) instanceof ConstructionReport) {
            $vars = array_merge($vars, self::fromReport($rep));
        }
        if (($b = $models['broker'] ?? null) instanceof User) {
            $vars['nombre_profesional'] = $b->name ?: trim(($b->first_name ?? '').' '.($b->last_name ?? ''));
        }
        if (array_key_exists('extra', $models) && is_array($models['extra'])) {
            $vars = array_merge($vars, $models['extra']);
        }

        return array_filter($vars, fn ($v) => $v !== null);
    }

    private static function company(): array
    {
        return [
            'proyecto'    => config('company.project'),
            'link_portal' => self::route('dashboard'),
        ];
    }

    private static function fromReservation(Reservation $r): array
    {
        $r->loadMissing('unit.project', 'unit.agent', 'unit.brokers', 'user');
        $unit  = $r->unit;
        $agent = $unit?->agent;
        $price = (float) ($unit->price ?? $r->unit_price ?? 0);

        $reservationPayment = $r->payments()
            ->where('payment_type', 'reservation')->first()
            ?? $r->payments()->orderBy('id')->first();

        $broker = $unit?->brokers?->first();
        $rate   = (float) ($agent->commission_rate ?? 0);
        $commission = $rate > 0 ? $price * $rate / 100 : 0;

        return [
            'nombre_cliente'     => trim(($r->first_name ?? '').' '.($r->last_name ?? '')) ?: ($r->user->name ?? 'Cliente'),
            'cliente_email'      => $r->email ?: optional($r->user)->email,
            'proyecto'           => $unit->project->name ?? config('company.project'),
            'unidad'             => $r->unit_name ?? $unit->custom_id ?? $unit->name ?? '—',
            'precio_venta'       => 'USD '.number_format($price, 2, '.', ','),
            'monto_reserva'      => $reservationPayment ? 'USD '.number_format((float) $reservationPayment->amount, 2, '.', ',') : '—',
            'nombre_asesor'      => $agent->name ?? ($r->budget_configured_by ?: 'tu asesor'),
            'tel_asesor'         => $agent->phone ?? config('company.phone'),
            'nombre_profesional' => $broker->name ?? ($broker ? trim(($broker->first_name ?? '').' '.($broker->last_name ?? '')) : 'estimado/a'),
            'monto_comision'     => $commission > 0 ? 'USD '.number_format($commission, 2, '.', ',') : '—',
        ];
    }

    private static function fromPayment(Payment $p): array
    {
        $d = PaymentReceiptData::build($p);

        return [
            'nombre_cliente'         => $d['nombre_cliente'],
            'cliente_email'          => $d['email_cliente'] !== '—' ? $d['email_cliente'] : null,
            'proyecto'               => $d['proyecto'],
            'unidad'                 => $d['unidad'],
            'moneda'                 => $d['moneda'],
            'monto'                  => $d['monto'],
            'monto_downpayment'      => $d['moneda'].' '.$d['monto'],
            'monto_en_letras'        => $d['monto_en_letras'],
            'concepto_pago'          => $d['concepto_pago'],
            'metodo_pago'            => $d['metodo_pago'],
            'referencia_transaccion' => $d['referencia'],
            'fecha_pago'             => $d['fecha_pago'],
            'total_pagado'           => $d['total_pagado'],
            'saldo_pendiente'        => $d['saldo_pendiente'],
            'nombre_asesor'          => $d['nombre_asesor'] !== '—' ? $d['nombre_asesor'] : 'tu asesor',
            'link_comprobante'       => $d['link_comprobante'],
            'link_portal'            => $d['link_portal'],
        ];
    }

    private static function fromReport(ConstructionReport $rep): array
    {
        $rep->loadMissing('project');
        $delivery = $rep->estimated_delivery
            ? Carbon::parse($rep->estimated_delivery)->locale('es')->isoFormat('MMMM YYYY')
            : '—';

        return [
            'proyecto'           => $rep->project->name ?? config('company.project'),
            'pct_obra'           => (string) ($rep->overall_progress ?? 0),
            'fecha_entrega'      => ucfirst($delivery),
            'mes_reporte'        => $rep->period ?: optional($rep->published_at)->locale('es')->isoFormat('MMMM YYYY'),
            'num_fotos'          => (string) (is_array($rep->photos) ? count($rep->photos) : 0),
            'hitos_actualizados' => (string) (is_array($rep->phases) ? count($rep->phases) : 0),
        ];
    }

    /** Genera una ruta con nombre de forma segura (devuelve '' si no existe en consola/migración). */
    private static function route(string $name, $params = []): string
    {
        try {
            return route($name, $params);
        } catch (\Throwable $e) {
            return config('app.url').'/'.ltrim(str_replace('.', '/', $name), '/');
        }
    }
}
