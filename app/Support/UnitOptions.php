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
            // La «característica» de la lista de precios de Siebe (Miguel, 27 oct).
            // Antes estaban las vistas de Makai (golf, lago, mar), que aquí no existen.
            'outlooks' => [
                ['value' => 'garden_terrace', 'label' => 'Terraza jardín'],
                ['value' => 'garden_view',    'label' => 'Vista al jardín'],
                ['value' => 'lock_off',       'label' => 'Lock-off'],
                ['value' => 'lock_off_roof',  'label' => 'Lock-off con roof top'],
            ],
            // Las del edificio de Siebe, las de las fotos de amenidades (Miguel,
            // 27 oct: «agregar todas estas amenidades a todas las fichas»). Antes
            // era la lista de Makai (club de playa, golf, tenis…).
            'amenities' => [
                ['value' => 'pool',    'label' => 'Piscina en la azotea', 'icon' => 'pool'],
                ['value' => 'gym',     'label' => 'Gimnasio',             'icon' => 'gym'],
                ['value' => 'rooftop', 'label' => 'Terraza en la azotea', 'icon' => 'rooftop'],
                ['value' => 'bbq',     'label' => 'Zona de barbacoa',     'icon' => 'bbq'],
                ['value' => 'lobby',   'label' => 'Lobby',                'icon' => 'lobby'],
                ['value' => 'parking', 'label' => 'Aparcamiento',         'icon' => 'parking'],
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
                $map[$row['value']] = self::label($row['label'] ?? $row['value']);
            }
        }

        return $map;
    }

    /**
     * Etiqueta de una opción en el idioma activo.
     *
     * Estas listas se cargan una sola vez desde el admin, así que quedan en el
     * idioma de quien las escribió: las vistas en español («Vista al lago») y
     * los tipos en inglés («1 Bed & Family Room»). El resultado era que cada
     * versión del sitio enseñaba media interfaz en el otro idioma.
     *
     * Se pasan por el traductor: si la etiqueta tiene traducción, se usa; si es
     * una que el admin acaba de inventar, sale tal cual la escribió, que es el
     * comportamiento de siempre. Sólo se traduce al MOSTRAR (map): el editor de
     * opciones usa get() y sigue viendo el texto original, que es el que edita.
     */
    public static function label(string $label): string
    {
        $traducida = __($label);

        return is_string($traducida) ? $traducida : $label;
    }

    /**
     * Número de piso a partir del valor guardado ('2nd', '2', 'Piso 2'…).
     * Devuelve null para planta baja o valores sin dígitos.
     */
    public static function floorNumber($value): ?int
    {
        $v = trim((string) $value);

        if ($v === '' || strcasecmp($v, 'ground') === 0 || strcasecmp($v, 'pb') === 0) {
            return null;
        }

        return preg_match('/\d+/', $v, $m) ? (int) $m[0] : null;
    }

    /**
     * Etiqueta corta del piso: «Planta baja» / «Ground Floor», «2°».
     *
     * No se usa la etiqueta guardada cuando el valor trae número: en varios
     * proyectos quedó cargada en inglés ('2nd') y se colaba sin traducir en
     * páginas en español. Con número la armamos acá y queda igual en ambos
     * idiomas; la etiqueta del admin sólo se respeta para valores sin número.
     */
    public static function floorShort($value): string
    {
        $v = trim((string) $value);

        if ($v === '' || strcasecmp($v, 'ground') === 0 || strcasecmp($v, 'pb') === 0) {
            return __('Ground Floor');
        }

        $n = self::floorNumber($v);
        if ($n !== null) {
            return $n . '°';
        }

        $labels = self::map('floors');

        return $labels[$v] ?? $labels[strtolower($v)] ?? ucfirst($v);
    }

    /**
     * Etiqueta completa y traducida: «Piso 2» / «Floor 2».
     *
     * Reemplaza al viejo `$floorDisplay($f) . ' ' . __('Floor')`, que producía
     * «2nd Piso» cuando la tarjeta se pintaba por AJAX (ahí no llegaba el
     * helper del Blade y caía al valor crudo de la base).
     */
    public static function floorPhrase($value): string
    {
        $v = trim((string) $value);

        if ($v === '' || strcasecmp($v, 'ground') === 0 || strcasecmp($v, 'pb') === 0) {
            return __('Ground Floor');
        }

        $n = self::floorNumber($v);

        return $n !== null ? __('Floor :n', ['n' => $n]) : self::floorShort($v);
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
            'rooftop'     => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9h20"/><path d="M4 9V6a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v3"/><path d="M8 9v11"/><path d="M16 9v11"/><path d="M5 20h14"/></svg>',
            'lobby'       => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v17"/><path d="M12 3v18"/><path d="M9.5 12h.01"/><path d="M14.5 12h.01"/></svg>',
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
