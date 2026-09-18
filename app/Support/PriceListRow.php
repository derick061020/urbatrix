<?php

namespace App\Support;

/**
 * Lectura de una fila de las listas de precios en CSV
 * (database/data/makai_etapa_*.csv).
 *
 * Vive aparte porque la usan dos comandos: units:import, que recrea las
 * unidades desde cero, y units:prices, que sólo mueve los precios cuando el
 * estudio manda una lista nueva. Teniendo el mapeo en un solo sitio no pueden
 * divergir.
 *
 * Columnas esperadas (17, generadas desde el Excel original):
 *   0  Apartamento / Unit          → name
 *   1  Tipologia / Typo            → layout (T1A, T6A-ST, …)
 *   2  Etapa / Phase               → custom_2 ("Etapa N")
 *   3  Habitaciones / Bedrooms     → bedrooms
 *   4  Banos / Baths               → bathrooms
 *   5  Vistas / Views              → outlook
 *   6  Caracteristica / Feature    → custom_1 (Family Room, Lock-Off, Rooftop, …)
 *   7  (vacío)
 *   8  m2 Interior                 → internal_area
 *   9  sqft interior               (ignorado)
 *   10 m2 Terraza                  → external_area
 *   11 sqft terraza                (ignorado)
 *   12 Total m2                    → total_area
 *   13 Total sqft                  (ignorado)
 *   14 Jardin / Rooftop m2         → custom_3
 *   15 Precio / Price              → price
 *   16 Status                      → status
 *
 * La planta (floor) no está en la fila: se deduce de las filas separadoras de
 * sección ("1ER NIVEL / 1ST FLOOR", "6TO NIVEL / PENTHOUSE", …) con detectFloor().
 */
class PriceListRow
{
    public const COL_UNIT = 0;
    public const COL_TYPO = 1;
    public const COL_PRICE = 15;
    public const COL_STATUS = 16;

    /** Mapa de palabras clave de sección → valor de floor (UnitOptions::floors). */
    private const FLOORS = [
        'PENTHOUSE'   => '6th',
        'GROUND'      => 'ground',
        '1ST FLOOR'   => '1st',
        '2ND FLOOR'   => '2nd',
        '3RD FLOOR'   => '3rd',
        '4TH FLOOR'   => '4th',
        '5TH FLOOR'   => '5th',
    ];

    /** Devuelve el valor de floor si la fila es un separador de sección. */
    public static function detectFloor(array $row): ?string
    {
        $text = strtoupper(implode(' ', array_map('strval', $row)));
        if (! str_contains($text, 'NIVEL') && ! str_contains($text, 'FLOOR') && ! str_contains($text, 'PLANTA')) {
            return null;
        }
        foreach (self::FLOORS as $needle => $value) {
            if (str_contains($text, $needle)) {
                return $value;
            }
        }

        return null;
    }

    /** ¿Es una fila de unidad? (número de unidad numérico + tipología presente) */
    public static function isUnit(array $row): bool
    {
        return trim($row[self::COL_TYPO] ?? '') !== ''
            && ctype_digit(trim($row[self::COL_UNIT] ?? ''));
    }

    public static function name(array $row): string
    {
        return trim($row[self::COL_UNIT] ?? '');
    }

    /** Construye el array de atributos de la unidad a partir de la fila. */
    public static function toPayload(array $row, ?string $floor, int $projectId, bool $public): array
    {
        $get = fn (int $i) => isset($row[$i]) ? trim($row[$i]) : '';

        $bedroomsRaw = $get(3);
        $feature     = $get(6);
        $views       = $get(5);

        return [
            'project_id'    => $projectId,
            'name'          => $get(self::COL_UNIT),
            'layout'        => $typo = $get(self::COL_TYPO),
            'type'          => self::mapType($bedroomsRaw, $feature, $floor),
            'status'        => self::mapStatus($get(self::COL_STATUS)),
            'floor'         => $floor,
            'outlook'       => self::mapOutlook($views),
            'bedrooms'      => (int) filter_var($bedroomsRaw, FILTER_SANITIZE_NUMBER_INT),
            'bathrooms'     => (float) str_replace(',', '.', $get(4)) ?: 0,
            'internal_area' => self::num($get(8)),
            'external_area' => self::num($get(10)),
            'total_area'    => self::num($get(12)),
            'price'         => self::money($get(self::COL_PRICE)) ?? 0.0,
            'custom_1'      => ($feature !== '' && $feature !== '-') ? $feature : null,
            'custom_2'      => 'Etapa ' . ($get(2) ?: '?'),
            'custom_3'      => $get(14) !== '' ? 'Rooftop/Jardín ' . $get(14) . ' m²' : null,
            'public'        => $public,
            'description'   => trim("{$typo} · {$views}", ' ·'),
        ];
    }

    /** Mapea bedrooms + feature + planta al valor de `type` (UnitOptions::types). */
    public static function mapType(string $bedroomsRaw, string $feature, ?string $floor): string
    {
        $beds   = (int) filter_var($bedroomsRaw, FILTER_SANITIZE_NUMBER_INT);
        $feat   = strtolower($feature);
        $isPent = $floor === '6th';

        if ($isPent) {
            return $beds >= 2 ? 'penthouse_2_bed' : 'penthouse_1_bed';
        }
        if (str_contains($feat, 'family')) {
            return '1_bed_family';
        }
        if (str_contains($feat, 'lock')) {
            return '1_bed_studio';
        }

        return match (true) {
            $beds >= 3  => '3_bed',
            $beds === 2 => '2_bed',
            default     => '1_bed',
        };
    }

    /** Mapea la columna Status al estado interno. */
    public static function mapStatus(string $raw): string
    {
        $s = strtoupper($raw);

        return match (true) {
            str_contains($s, 'RESERVAD')                           => 'RESERVED',
            str_contains($s, 'BLOQUEAD')                           => 'HELD',
            str_contains($s, 'VENDID') || str_contains($s, 'SOLD') => 'SOLD',
            default                                                => 'AVAILABLE',
        };
    }

    /** Mapea la vista al valor de outlook (UnitOptions::outlooks). */
    public static function mapOutlook(string $views): ?string
    {
        $v = strtolower($views);

        return match (true) {
            str_contains($v, 'ocean and lake') || str_contains($v, 'mar y lago') => 'ocean_lake',
            str_contains($v, 'golf')                                             => 'golf_course',
            str_contains($v, 'lake') || str_contains($v, 'lago')                 => 'lake',
            str_contains($v, 'ocean') || str_contains($v, 'mar')                 => 'ocean',
            default                                                              => null,
        };
    }

    /** Parsea un decimal con separador de miles ("1,166.00" → 1166.00). */
    public static function num(string $raw): float
    {
        $clean = str_replace([',', ' '], '', $raw);

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    /**
     * Precio: "$474,100" → 474100.0. Devuelve null si la celda viene vacía,
     * que no es lo mismo que un precio de 0: en las listas del estudio las
     * unidades reservadas o bloqueadas van sin precio.
     */
    public static function money(string $raw): ?float
    {
        $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '', $raw));

        return is_numeric($clean) ? (float) $clean : null;
    }
}
