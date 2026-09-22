<?php

namespace App\Support;

/**
 * Hoja «Precios» del levantamiento de Bahía Mar: el precio de venta de cada
 * villa y la vista que le toca.
 *
 * La hoja calcula el precio por unidad a partir de las áreas y tres
 * parámetros ($/m² de lote, de interior y de terraza):
 *
 *     precio = área lote × $/m² lote
 *            + m² interior × $/m² interior
 *            + m² terraza × $/m² terraza
 *
 * así que dos villas de la misma tipología valen distinto según su lote.
 *
 * La usan villas:prices (baja precios y vistas sin tocar nada más) y
 * villas:import (para no recrear el catálogo con los precios estimados
 * provisionales cuando el Excel ya trae los reales).
 */
class VillaPriceList
{
    /** Nombre por defecto de la hoja de precios. */
    public const SHEET = 'Precios';

    /**
     * Vista del Excel → valor de UnitOptions::outlooks, para que el filtro de
     * vista del home las agrupe. «Sin vista» se guarda como null: la villa no
     * muestra chip de vista ni aparece bajo ningún filtro.
     */
    private const VISTAS = [
        'mar'          => 'ocean',
        'vista al mar' => 'ocean',
        'montana'      => 'mountain',
        'sin vista'    => null,
        ''             => null,
    ];

    /** ¿El archivo trae la hoja de precios? */
    public static function exists(string $file, string $sheet = self::SHEET): bool
    {
        foreach (XlsxSheet::sheetNames($file) as $name) {
            if (XlsxSheet::norm($name) === XlsxSheet::norm($sheet)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lee la hoja y devuelve [código de villa => datos]. La última aparición
     * de un código gana. Se descartan las filas de cierre ("TOTAL") y las que
     * no tengan un precio positivo.
     *
     * @return array<string, array{price: float, view: string, interior: float, terrace: float, plot: float, phase: string}>
     */
    public static function read(string $file, string $sheet = self::SHEET): array
    {
        $rows = XlsxSheet::rows($file, $sheet, [
            'code'     => ['nombre unidad'],
            'typo'     => ['codigo tipologia'],
            'phase'    => ['etapa'],
            'interior' => ['m interior'],
            'terrace'  => ['m terraza'],
            'plot'     => ['area lote'],
            'price'    => ['precio venta'],
            'view'     => ['vista'],
        ]);

        $out = [];
        foreach ($rows as $row) {
            $code = strtoupper(trim($row['code'] ?? ''));

            // Códigos de villa: A-001 … E-078.
            if (! preg_match('/^[A-Z]-\d+$/', $code)) {
                continue;
            }
            $price = XlsxSheet::num($row['price'] ?? '');
            if ($price <= 0) {
                continue;
            }

            $out[$code] = [
                'price'    => round($price, 2),
                'view'     => trim($row['view'] ?? ''),
                'interior' => XlsxSheet::num($row['interior'] ?? ''),
                'terrace'  => XlsxSheet::num($row['terrace'] ?? ''),
                'plot'     => XlsxSheet::num($row['plot'] ?? ''),
                'phase'    => trim($row['phase'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * «Mar» → 'ocean' · «Montaña» → 'mountain' · «Sin vista» → null.
     *
     * Una vista desconocida se devuelve tal cual (el home muestra el texto
     * crudo cuando no está en el catálogo de opciones) y se anota en
     * $unmapped para poder avisar.
     */
    public static function mapView(string $raw, ?array &$unmapped = null): ?string
    {
        $key = XlsxSheet::norm($raw);
        if (array_key_exists($key, self::VISTAS)) {
            return self::VISTAS[$key];
        }

        if ($unmapped !== null) {
            $unmapped[$raw] = ($unmapped[$raw] ?? 0) + 1;
        }

        return $raw !== '' ? $raw : null;
    }
}
