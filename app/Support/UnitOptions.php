<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Listas de opciones globales del proyecto para las unidades:
 * tipos, plantas, vistas (outlook) y amenidades.
 *
 * Por defecto devuelve los valores históricos (los que antes estaban
 * hardcodeados en el formulario y en el home). El admin puede sobreescribirlos
 * desde el modal "Configuración global" de la tabla de unidades, y tanto el
 * panel como el front (home / PDF) consumen estas mismas listas para mantenerse
 * sincronizados.
 */
class UnitOptions
{
    /** Claves de categorías editables. */
    public const CATEGORIES = ['types', 'floors', 'outlooks', 'amenities'];

    /** Prefijo de las claves guardadas en la tabla settings. */
    private const PREFIX = 'unit_options_';

    /**
     * Valores por defecto. Cada categoría es una lista de
     * ['value' => ..., 'label' => ...]; las amenidades agregan 'icon'.
     */
    public static function defaults(): array
    {
        return [
            'types' => [
                ['value' => '1_bed',           'label' => '1 Bed'],
                ['value' => '1_bed_family',    'label' => '1 Bed & Family Room'],
                ['value' => '1_bed_studio',    'label' => '1 Bed & Studio Lock-off'],
                ['value' => '2_bed',           'label' => '2 Bed'],
                ['value' => '3_bed',           'label' => '3 Bed'],
                ['value' => 'penthouse_1_bed', 'label' => 'Penthouse 1 Bed'],
                ['value' => 'penthouse_2_bed', 'label' => 'Penthouse 2 Bed'],
            ],
            'floors' => [
                ['value' => 'ground', 'label' => 'Planta baja'],
                ['value' => '1st',    'label' => '1°'],
                ['value' => '2nd',    'label' => '2°'],
                ['value' => '3rd',    'label' => '3°'],
                ['value' => '4th',    'label' => '4°'],
                ['value' => '5th',    'label' => '5°'],
                ['value' => '6th',    'label' => '6°'],
            ],
            'outlooks' => [
                ['value' => 'golf_course', 'label' => 'Vista al campo de golf'],
                ['value' => 'lake',        'label' => 'Vista al lago'],
                ['value' => 'ocean_lake',  'label' => 'Vista al mar y al lago'],
                ['value' => 'ocean',       'label' => 'Vista al mar'],
                ['value' => 'mountain',    'label' => 'Vista a la montaña'],
            ],
            'amenities' => [
                ['value' => 'pool',        'label' => 'Pool',          'icon' => 'pool'],
                ['value' => 'gym',         'label' => 'Gym',           'icon' => 'gym'],
                ['value' => 'beach_club',  'label' => 'Beach Club',    'icon' => 'beach_club'],
                ['value' => 'restaurant',  'label' => 'Restaurant',    'icon' => 'restaurant'],
                ['value' => 'spa',         'label' => 'Spa',           'icon' => 'spa'],
                ['value' => 'tennis',      'label' => 'Tennis Court',  'icon' => 'tennis'],
                ['value' => 'golf',        'label' => 'Golf Course',   'icon' => 'golf'],
                ['value' => 'security',    'label' => '24/7 Security', 'icon' => 'security'],
                ['value' => 'parking',     'label' => 'Parking',       'icon' => 'parking'],
                ['value' => 'concierge',   'label' => 'Concierge',     'icon' => 'concierge'],
                ['value' => 'playground',  'label' => 'Playground',    'icon' => 'playground'],
                ['value' => 'bbq',         'label' => 'BBQ Area',      'icon' => 'bbq'],
            ],
        ];
    }

    /**
     * Devuelve la lista de una categoría (override guardado o default).
     *
     * @return array<int, array{value:string,label:string,icon?:string}>
     */
    public static function get(string $category): array
    {
        $stored = Setting::get(self::PREFIX . $category);

        if (is_array($stored)) {
            return $stored;
        }

        return self::defaults()[$category] ?? [];
    }

    /** Guarda una categoría. */
    public static function put(string $category, array $rows): void
    {
        Setting::put(self::PREFIX . $category, array_values($rows));
    }

    /** Todas las categorías, listas para alimentar el modal de configuración. */
    public static function all(): array
    {
        $out = [];
        foreach (self::CATEGORIES as $category) {
            $out[$category] = self::get($category);
        }

        return $out;
    }

    /**
     * Mapa value => label de una categoría (cómodo para selects y para mostrar
     * la etiqueta legible a partir de un valor guardado).
     */
    public static function map(string $category): array
    {
        $map = [];
        foreach (self::get($category) as $row) {
            if (isset($row['value'])) {
                $map[$row['value']] = $row['label'] ?? $row['value'];
            }
        }

        return $map;
    }

    /**
     * Catálogo de íconos SVG para amenidades. La clave coincide con el campo
     * 'icon' de cada amenidad; si no hay match se usa 'check'.
     */
    public static function amenityIcons(): array
    {
        return [
            'pool'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20"/><path d="M4 12v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6"/><path d="M6 12V8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v4"/></svg>',
            'gym'         => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 6.5h11"/><path d="M6 20v-8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8"/><path d="M18 11V6a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v5"/></svg>',
            'beach_club'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22h20"/><path d="M12 2v20"/><path d="M4 12c0-4 3-7 8-7s8 3 8 7"/></svg>',
            'restaurant'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>',
            'spa'         => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c-5.5 0-10 4.5-10 10s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 2v20"/><path d="M2 12h20"/></svg>',
            'tennis'      => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2v20"/><path d="M4.93 4.93l14.14 14.14"/><path d="M19.07 4.93L4.93 19.07"/></svg>',
            'golf'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="10" r="3"/><path d="M12 13v8"/><path d="M9 6l3-4 3 4"/></svg>',
            'security'    => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
            'parking'     => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M7 15V9"/><path d="M17 15V9"/></svg>',
            'concierge'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            'playground'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
            'bbq'         => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16"/><path d="M6 12v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-4"/><path d="M8 12V8a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"/></svg>',
            'check'       => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>',
        ];
    }

    /** Devuelve el SVG de un ícono de amenidad, con fallback a 'check'. */
    public static function amenityIcon(?string $icon): string
    {
        $icons = self::amenityIcons();

        return $icons[$icon] ?? $icons['check'];
    }
}
