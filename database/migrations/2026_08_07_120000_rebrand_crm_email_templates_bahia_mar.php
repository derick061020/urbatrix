<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Repinta los cuerpos de las plantillas del CRM a la identidad Bahía Mar.
 *
 * Los HTML de database/seeders/crm_emails/*.html se instalaron en su día con la
 * paleta de la marca anterior (verde oliva #0f2710/#0b1c0a, crema #F1EDE3, oro
 * #B8962E) y quedaron guardados en `crm_templates.body`. Los ficheros ya están
 * repintados; esta migración vuelca esa versión sobre las filas existentes.
 *
 * Se sustituye color por color sobre el cuerpo guardado en vez de volcar el
 * fichero entero: así las plantillas que el equipo haya editado desde el panel
 * conservan su texto y solo cambian de paleta. La sustitución es idempotente.
 */
return new class extends Migration
{
    /** Paleta anterior => paleta Bahía Mar. */
    private const PALETTE = [
        '#0f2710'                => '#053330',
        '#0b1c0a'                => '#074540',
        '#1a0e02'                => '#053330',
        '#5a4a2a'                => '#074540',
        '#F1EDE3'                => '#ffffff',
        '#f1ede3'                => '#ffffff',
        'rgba(241,237,227,'      => 'rgba(255,255,255,',
        '#B8962E'                => '#074540',
        '#b8962e'                => '#074540',
        '#4A5E3F'                => '#074540',
        '#4a5e3f'                => '#074540',
        'rgba(184,150,46,0.7)'   => 'rgba(255,255,255,0.45)',
        'rgba(184,150,46,0.6)'   => 'rgba(255,255,255,0.45)',
        'rgba(184,150,46,0.4)'   => 'rgba(255,255,255,0.4)',
        'rgba(184,150,46,0.3)'   => 'rgba(255,255,255,0.3)',
        '#1a1a18'                => '#171717',
        '#4a4a46'                => '#525866',
        '#6a6a64'                => '#525866',
        '#8a8a84'                => '#99a0ae',
        '#e8e7e3'                => '#eaecf0',
        '#f7f3e8'                => '#f5f7fa',
        '#e8dfc8'                => '#eaecf0',
        '#8a7a50'                => '#99a0ae',
        '#6a5a30'                => '#525866',
        // Rastros de la marca anterior en el copy.
        'hello@makairesidences.com' => '{{email_soporte}}',
        'Equipo Duna'               => 'Equipo {{proyecto}}',
    ];

    public function up(): void
    {
        $now = now();

        foreach (DB::table('crm_templates')->get() as $tpl) {
            $body = (string) $tpl->body;
            $new  = strtr($body, self::PALETTE);

            if ($new !== $body) {
                DB::table('crm_templates')->where('id', $tpl->id)->update([
                    'body'       => $new,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Repintado de identidad: no se revierte a la marca anterior.
    }
};
