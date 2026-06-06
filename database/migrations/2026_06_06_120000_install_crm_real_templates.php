<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Instala las plantillas HTML reales de Duna Development (E-01..E-12, sin las
 * que no tienen flujo de datos: E-06/E-07/E-08) y sus automatizaciones,
 * reemplazando las plantillas demo de texto plano. El cuerpo de cada plantilla
 * se lee de database/seeders/crm_emails/*.html (solo el contenido interno; el
 * chrome lo aporta el layout emails.crm.wrapper).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_templates', 'doc_label')) {
                $table->string('doc_label')->nullable()->after('variables');
            }
            if (! Schema::hasColumn('crm_templates', 'audience')) {
                $table->string('audience', 20)->default('client')->after('doc_label');
            }
        });

        // Fuera las plantillas/automatizaciones demo de texto plano.
        DB::table('crm_automations')->delete();
        DB::table('crm_templates')->delete();

        $now = now();
        $dir = database_path('seeders/crm_emails');

        // key => [file, name, category, icon, doc_label, audience, subject, channels]
        $templates = [
            'e01' => ['e-01-bienvenida-post-reserva', 'Bienvenida — Reserva confirmada', 'bienvenida', 'home', 'Confirmación · E-01', 'client',
                'Tu reserva en {{proyecto}} ha sido confirmada'],
            'e02' => ['e-02-recordatorio-kyc', 'Recordatorio KYC pendiente', 'seguimiento', 'user', 'Recordatorio · E-02', 'client',
                'Tu KYC está pendiente — {{proyecto}}'],
            'e03' => ['e-03-confirmacion-pago', 'Confirmación de pago', 'pagos', 'check', 'Confirmación · E-03', 'client',
                'Hemos recibido tu pago — {{proyecto}}'],
            'e04' => ['e-04-avance-de-obra', 'Avance de obra (mensual)', 'proyectos', 'chart-line', 'Reporte mensual · E-04', 'client',
                'Novedades de obra — {{proyecto}} · {{pct_obra}}% completado'],
            'e05' => ['e-05-comision-desbloqueada', 'Comisión desbloqueada', 'profesional', 'star', 'Profesional · E-05', 'broker',
                'Tu comisión de {{proyecto}} ha sido desbloqueada'],
            'e09' => ['e-09-nueva-reserva-interna', 'Nueva reserva (aviso interno)', 'interno', 'bell', 'Equipo interno · E-09', 'internal',
                'Nueva reserva — {{proyecto}} · Unidad {{unidad}} · {{nombre_cliente}}'],
            'e10' => ['e-10-mora-cuota-construccion', 'Mora de cuota (aviso interno)', 'interno', 'clock', 'Equipo interno · E-10', 'internal',
                'Mora acumulada — {{nombre_cliente}} · Unidad {{unidad}} · {{proyecto}}'],
            'e11' => ['e-11-comprobante-pago', 'Comprobante de pago', 'pagos', 'file-pdf', 'Comprobante · E-11', 'client',
                'Tu comprobante de pago — {{proyecto}}'],
            'e12' => ['e-12-nuevo-reporte-subido', 'Nuevo reporte disponible', 'proyectos', 'file', 'Avances de obra · E-12', 'client',
                'Nuevo reporte disponible — {{proyecto}} · {{mes_reporte}}'],
        ];

        $ids = [];
        foreach ($templates as $key => $t) {
            [$file, $name, $category, $icon, $docLabel, $audience, $subject] = $t;
            $body = @file_get_contents("{$dir}/{$file}.html");
            if ($body === false) {
                $body = '<tr><td style="padding:32px 36px;">Plantilla {{proyecto}}.</td></tr>';
            }

            $ids[$key] = DB::table('crm_templates')->insertGetId([
                'name'       => $name,
                'category'   => $category,
                'icon'       => $icon,
                'channels'   => json_encode(['email']),
                'subject'    => $subject,
                'body'       => $body,
                'variables'  => json_encode(self::tokensIn($subject.' '.$body)),
                'doc_label'  => $docLabel,
                'audience'   => $audience,
                'last_used_at' => null,
                'usage_count'  => 0,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // name, description, trigger_event, template key, delay_minutes, is_active
        $automations = [
            ['Bienvenida al confirmar reserva', 'Email de bienvenida al cliente cuando su reserva se confirma.',
                'reservation_confirmed', 'e01', 0, true],
            ['Aviso interno de nueva reserva', 'Notifica al equipo y al asesor cuando entra una nueva reserva.',
                'reservation_confirmed', 'e09', 0, true],
            ['Recordatorio de KYC pendiente', 'Recuerda al cliente completar su KYC. Ejecución manual o programada.',
                'kyc_pending', 'e02', 0, false],
            ['Confirmación de pago al cliente', 'Aviso simple de pago recibido. Desactivada por defecto (el comprobante E-11 ya lo cubre).',
                'payment_received', 'e03', 0, false],
            ['Comprobante de pago al cliente', 'Envía el comprobante detallado cada vez que se registra un pago.',
                'payment_received', 'e11', 0, true],
            ['Alerta interna de mora', 'Notifica al equipo cuando un cliente acumula mora. Ejecución manual o programada.',
                'payment_overdue', 'e10', 0, false],
            ['Avance de obra mensual', 'Envía el reporte de avance mensual a los compradores del proyecto.',
                'progress_update', 'e04', 0, true],
            ['Nuevo reporte de obra disponible', 'Avisa a los compradores cuando se publica un nuevo reporte de obra.',
                'report_uploaded', 'e12', 0, true],
            ['Comisión desbloqueada al broker', 'Notifica al broker cuando su comisión queda desbloqueada (contrato firmado).',
                'commission_unlocked', 'e05', 0, true],
        ];

        foreach ($automations as $a) {
            [$name, $description, $event, $tplKey, $delay, $active] = $a;
            DB::table('crm_automations')->insert([
                'name'               => $name,
                'description'        => $description,
                'trigger_event'      => $event,
                'trigger_conditions' => null,
                'template_id'        => $ids[$tplKey] ?? null,
                'delay_minutes'      => $delay,
                'channels'           => json_encode(['email']),
                'is_active'          => $active,
                'last_run_at'        => null,
                'run_count'          => 0,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // Asegura el canal email habilitado.
        DB::table('crm_channel_settings')->updateOrInsert(
            ['channel' => 'email'],
            ['enabled' => true, 'updated_at' => $now]
        );
    }

    public function down(): void
    {
        DB::table('crm_automations')->delete();
        DB::table('crm_templates')->delete();

        Schema::table('crm_templates', function (Blueprint $table) {
            foreach (['doc_label', 'audience'] as $col) {
                if (Schema::hasColumn('crm_templates', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /** Extrae los tokens {{var}} únicos de un texto. */
    private static function tokensIn(string $text): array
    {
        preg_match_all('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', $text, $m);
        return array_values(array_unique($m[1] ?? []));
    }
};
