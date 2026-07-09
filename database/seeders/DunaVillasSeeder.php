<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\UnitImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DunaVillasSeeder
 * =============================================================================
 * Datos de prueba para la home del comprador (bandas editoriales + masterplan),
 * con los 5 modelos y los renders que venían del Figma (node 943:2315).
 *
 * Los renders se sirven desde public/images/seed-villas/{modelo}.jpg y se
 * enlazan como UnitImage de cada unidad, así cada tarjeta/banda muestra la
 * imagen de su modelo.
 *
 * Genera 40 villas (una por lote), repartidas en los 5 modelos, con estados de
 * muestra (disponible / reservada / vendida / pending / alta demanda / 2ª
 * oportunidad). El campo `floor` se usa como "piso" = modelo, de modo que el
 * masterplan queda en 5 filas (una por modelo), tal como el mockup.
 *
 * Idempotente: borra primero las unidades que él mismo sembró (custom_id
 * DV-101 … DV-140) y sus imágenes, luego reinserta.
 *
 * Ejecutar:
 *   php artisan db:seed --class=DunaVillasSeeder
 * =============================================================================
 */
class DunaVillasSeeder extends Seeder
{
    public function run(): void
    {
        // --- Definición de los 5 modelos (datos del Figma) --------------------
        // total   = cuántas villas de ese modelo
        // res/vend/hot/second = cuántas de esas van en cada estado (el resto,
        //           disponibles). Un par se marcan pending/hot para poblar tabs.
        $modelos = [
            [
                'key' => 'coral', 'nombre' => 'Villa Coral', 'zona' => 'Frente a playa',
                'img' => 'coral.jpg', 'hab' => 2, 'ba' => 2.5, 'parking' => 2,
                'const' => 240, 'ext' => 280, 'lote' => 520, 'piscina' => true,
                'desde' => 850000, 'total' => 6,
                'res' => 1, 'vend' => 0, 'pending' => 0, 'hot' => 1, 'second' => 0,
            ],
            [
                'key' => 'marea', 'nombre' => 'Villa Marea', 'zona' => 'Vista al mar',
                'img' => 'marea.jpg', 'hab' => 3, 'ba' => 3.5, 'parking' => 2,
                'const' => 310, 'ext' => 290, 'lote' => 600, 'piscina' => true,
                'desde' => 720000, 'total' => 10,
                'res' => 2, 'vend' => 1, 'pending' => 1, 'hot' => 1, 'second' => 1,
            ],
            [
                'key' => 'palma', 'nombre' => 'Villa Palma', 'zona' => 'Frente a golf',
                'img' => 'palma.jpg', 'hab' => 4, 'ba' => 4.5, 'parking' => 3,
                'const' => 420, 'ext' => 360, 'lote' => 780, 'piscina' => true,
                'desde' => 980000, 'total' => 8,
                'res' => 1, 'vend' => 1, 'pending' => 0, 'hot' => 1, 'second' => 0,
            ],
            [
                'key' => 'arena', 'nombre' => 'Villa Arena', 'zona' => 'Jardín',
                'img' => 'arena.jpg', 'hab' => 2, 'ba' => 2.5, 'parking' => 1,
                'const' => 210, 'ext' => 220, 'lote' => 430, 'piscina' => false,
                'desde' => 595000, 'total' => 9,
                'res' => 1, 'vend' => 0, 'pending' => 0, 'hot' => 0, 'second' => 1,
            ],
            [
                'key' => 'brisa', 'nombre' => 'Villa Brisa', 'zona' => 'Interior',
                'img' => 'brisa.jpg', 'hab' => 3, 'ba' => 3.0, 'parking' => 2,
                'const' => 280, 'ext' => 240, 'lote' => 540, 'piscina' => false,
                'desde' => 640000, 'total' => 7,
                'res' => 1, 'vend' => 0, 'pending' => 0, 'hot' => 1, 'second' => 0,
            ],
        ];

        $directions = ['N', 'S', 'E', 'W'];
        $imgBase    = '/images/seed-villas/';

        DB::transaction(function () use ($modelos, $directions, $imgBase) {
            // --- Limpieza idempotente: borra lo que este seeder creó antes ----
            $seededIds = [];
            for ($i = 101; $i <= 140; $i++) {
                $seededIds[] = 'DV-' . $i;
            }
            $oldUnitIds = Unit::whereIn('custom_id', $seededIds)->pluck('id');
            if ($oldUnitIds->isNotEmpty()) {
                UnitImage::whereIn('unit_id', $oldUnitIds)->delete();
                Unit::whereIn('id', $oldUnitIds)->delete();
            }

            // --- Inserción ----------------------------------------------------
            $lot   = 101;   // número de lote correlativo (custom_id = DV-101 …)
            $floor = 0;     // "piso" = índice de modelo → 1 fila por modelo en el masterplan

            foreach ($modelos as $m) {
                $floor++;

                for ($i = 0; $i < $m['total']; $i++) {
                    // Reparte estados en orden: vendidas → pending → reservadas → resto disponibles
                    $status       = 'available';
                    $isHighDemand = false;
                    $isSecond     = false;
                    $reservedUntil = null;
                    $releasedAt    = null;

                    $vendUpto    = $m['vend'];
                    $pendUpto    = $vendUpto + $m['pending'];
                    $resUpto     = $pendUpto + $m['res'];

                    if ($i < $vendUpto) {
                        $status = 'sold';
                    } elseif ($i < $pendUpto) {
                        $status = 'pending';
                    } elseif ($i < $resUpto) {
                        $status = 'reserved';
                        $reservedUntil = now()->addHours(36); // temporizador visible en la tarjeta
                    } else {
                        // Disponibles: algunas marcadas como alta demanda / 2ª oportunidad
                        if ($m['hot'] > 0 && $i === $resUpto) {
                            $isHighDemand = true;
                        }
                        if ($m['second'] > 0 && $i === $resUpto + 1) {
                            $isSecond   = true;
                            $releasedAt = now()->subDays(2);
                        }
                    }

                    // Pequeña variación de precio y superficie por lote
                    $premium = (int) round(($i % 4) * 0.025 * $m['desde']);
                    $price   = $m['desde'] + $premium;

                    $unit = Unit::create([
                        'name'                 => $m['nombre'] . ' ' . $lot,
                        'custom_id'            => 'DV-' . $lot,
                        'status'               => $status,
                        'type'                 => 'Villa',
                        'price'                => $price,
                        'public'               => true,
                        'display_on_home_page' => true,
                        'description'          => $m['nombre'] . ' — ' . $m['zona'] . '. '
                            . ($m['piscina'] ? 'Con piscina privada.' : 'Con jardín privado.'),

                        // Especificaciones
                        'floor'         => (string) $floor,           // "piso" = modelo → fila del masterplan
                        'bedrooms'      => $m['hab'],
                        'bathrooms'     => $m['ba'],
                        'parking_bays'  => $m['parking'],
                        'pools'         => $m['piscina'] ? 1 : 0,
                        'direction'     => $directions[$i % count($directions)],
                        'outlook'       => $m['zona'],
                        'internal_area' => $m['const'],
                        'external_area' => $m['ext'],
                        'total_area'    => $m['const'] + $m['ext'],

                        // Flags de estado / demanda
                        'is_high_demand'  => $isHighDemand,
                        'is_second_chance' => $isSecond,
                        'reserved_until'  => $reservedUntil,
                        'released_at'     => $releasedAt,
                        'fully_furnished' => ($i % 3 === 0),

                        // Métricas de muestra
                        'shortlisted_count' => random_int(0, 12),
                        'views_today'       => random_int(2, 40),
                        'views_total'       => random_int(40, 600),
                        'images_count'      => 1,
                    ]);

                    // Render del modelo como imagen de la unidad
                    UnitImage::create([
                        'unit_id'    => $unit->id,
                        'category'   => 'property',
                        'name'       => $m['nombre'],
                        'path'       => $imgBase . $m['img'],
                        'sort_order' => 0,
                    ]);

                    $lot++;
                }
            }
        });

        $this->command?->info('DunaVillasSeeder: 40 villas de prueba creadas (DV-101 … DV-140) con renders del Figma.');
    }
}
