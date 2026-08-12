<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;

/**
 * Los nombres de documentos se guardan en la base con el idioma que estaba
 * activo cuando se creó la fila ("Promesa de Compraventa - RES-XXXX",
 * "Payment Plan - RES-XXXX", "KYC — Juan Pérez"...). Este helper los vuelve a
 * traducir al idioma actual en el momento de mostrarlos, conservando intacta la
 * parte variable (código de reserva, nombre del cliente, nombre de archivo).
 *
 * Se usa en el escritorio, el CRM y el portal del cliente: cualquier lugar
 * donde se pinte el nombre de un documento debería pasar por aquí.
 */
class DocumentTitle
{
    /**
     * Separadores con los que se componen los títulos: "Etiqueta <sep> variable".
     * Se prueban en este orden sobre cada posición de corte candidata.
     */
    private const SEPARATORS = [' — ', ' – ', ' - '];

    /** Etiqueta canónica (en español) por tipo de documento. */
    private const TYPE_LABELS = [
        'payment_plan'     => 'Plan de Pagos',
        'purchase_promise' => 'Promesa de Compraventa',
        'promise'          => 'Promesa de Compraventa',
        'contract'         => 'Contrato',
        'kyc'              => 'KYC',
        'id_front'         => 'Documento de identidad (Frente)',
        'id_back'          => 'Documento de identidad (Reverso)',
        'rnc'              => 'Registro fiscal / RNC',
        'bank'             => 'Datos bancarios',
        'photo'            => 'Foto de perfil',
    ];

    /** Mapas cacheados: texto en cualquier idioma => clave canónica. */
    private static ?array $exact = null;
    private static ?array $lower = null;

    /**
     * Nombre del documento listo para mostrar, en el idioma activo.
     *
     * @param  string|null  $title  Título tal como está guardado.
     * @param  string|null  $type   document_type, usado como respaldo si no hay título.
     */
    public static function make(?string $title, ?string $type = null): string
    {
        $title = trim((string) $title);

        if ($title === '') {
            return self::forType($type);
        }

        // 1) El título completo es una etiqueta conocida ("Comprobante de ingresos").
        if ($translated = self::translateSegment($title)) {
            return $translated;
        }

        // 2) "Etiqueta <sep> parte variable": traducimos solo la etiqueta y
        //    dejamos el código de reserva / nombre del cliente tal cual.
        foreach (self::splitPoints($title) as [$label, $sep, $rest]) {
            if ($translated = self::translateSegment($label)) {
                return $translated.$sep.$rest;
            }
        }

        // Nombre libre (un archivo subido por el cliente): se muestra tal cual.
        return $title;
    }

    /** Etiqueta traducida a partir del document_type, sin título guardado. */
    public static function forType(?string $type): string
    {
        $key = self::TYPE_LABELS[$type] ?? null;

        return $key ? __($key) : __('Documento');
    }

    /**
     * Todas las formas de partir el título en "etiqueta + resto", de izquierda a
     * derecha, para que "Documento de identidad (Frente) — Ana - 2" pruebe
     * primero el corte más corto.
     */
    private static function splitPoints(string $title): array
    {
        $points = [];

        foreach (self::SEPARATORS as $sep) {
            $offset = 0;
            while (($pos = mb_strpos($title, $sep, $offset)) !== false) {
                $points[] = [
                    'pos'   => $pos,
                    'parts' => [
                        mb_substr($title, 0, $pos),
                        $sep,
                        mb_substr($title, $pos + mb_strlen($sep)),
                    ],
                ];
                $offset = $pos + mb_strlen($sep);
            }
        }

        usort($points, fn ($a, $b) => $a['pos'] <=> $b['pos']);

        return array_column($points, 'parts');
    }

    /** Traduce un fragmento exacto, o null si no es una etiqueta conocida. */
    private static function translateSegment(string $segment): ?string
    {
        $segment = trim($segment);

        if ($segment === '') {
            return null;
        }

        self::buildMaps();

        // La coincidencia exacta manda, para no perder las mayúsculas propias
        // de la etiqueta ("Plan de Pagos" y no "Plan de pagos").
        $key = self::$exact[$segment] ?? self::$lower[mb_strtolower($segment)] ?? null;

        return $key === null ? null : __($key);
    }

    /**
     * Índice inverso de los archivos de idioma: tanto las claves como sus
     * traducciones apuntan a la clave canónica, de modo que un título guardado
     * en inglés se reconozca igual que uno guardado en español.
     */
    private static function buildMaps(): void
    {
        if (self::$exact !== null) {
            return;
        }

        $exact = [];
        $lower = [];

        $add = function (string $text, string $key) use (&$exact, &$lower) {
            $exact[$text] ??= $key;
            $lower[mb_strtolower($text)] ??= $key;
        };

        foreach (self::TYPE_LABELS as $label) {
            $add($label, $label);
        }

        foreach (config('app.supported_locales', ['es', 'en']) as $locale) {
            try {
                $lines = Lang::getLoader()->load($locale, '*', '*');
            } catch (\Throwable) {
                continue;
            }

            // Primero las claves (idioma base) y después las traducciones, para
            // que la clave canónica gane cuando ambas coincidan en minúsculas.
            foreach ($lines as $key => $value) {
                if (is_string($key) && is_string($value)) {
                    $add($key, $key);
                }
            }

            foreach ($lines as $key => $value) {
                if (is_string($key) && is_string($value)) {
                    $add($value, $key);
                }
            }
        }

        self::$exact = $exact;
        self::$lower = $lower;
    }
}
