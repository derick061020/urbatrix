<?php

namespace App\Support;

/**
 * Lector mínimo de .xlsx (ZipArchive + SimpleXML, sin dependencias extra).
 *
 * Vive aparte porque lo usan los dos comandos que leen el levantamiento de
 * Bahía Mar: villas:import, que recrea el catálogo entero, y villas:prices,
 * que sólo baja los precios de la hoja «Precios». Teniendo la lectura en un
 * solo sitio no pueden divergir.
 *
 * Lee los valores cacheados de las celdas, así que las columnas calculadas
 * (las de la hoja «Precios» son fórmulas) llegan ya resueltas siempre que el
 * archivo se haya guardado desde Excel/LibreOffice.
 */
class XlsxSheet
{
    /**
     * Devuelve las filas de datos de una hoja como [clave lógica => valor].
     *
     * $patterns es [clave => [substrings que debe contener el encabezado]];
     * ver matchHeaders() para cómo se resuelve cada columna.
     *
     * @param  array<string, array<int, string>>  $patterns
     * @return array<int, array<string, string>>
     */
    public static function rows(string $file, string $sheetName, array $patterns): array
    {
        $grid = self::grid($file, $sheetName);

        // Fila de encabezados = la que resuelve MÁS claves (las hojas empiezan
        // con títulos e instrucciones en una sola celda que también contienen
        // los nombres de las columnas: "Llenar: N° de unidad, Código de
        // tipología, Etapa, Nivel…"). Por eso se exige además que las claves
        // caigan en columnas distintas y se cubra al menos la mitad.
        $headerRow = null;
        $columns   = [];
        $best      = 0;
        $minimum   = (int) ceil(count($patterns) / 2);

        foreach ($grid as $rowNumber => $cells) {
            $map   = self::matchHeaders($cells, $patterns);
            $score = count($map);

            if ($score > $best && $score >= $minimum && count(array_unique($map)) > 1) {
                $best      = $score;
                $headerRow = $rowNumber;
                $columns   = $map;
            }

            // Encabezado completo: no hace falta seguir buscando.
            if ($best === count($patterns)) {
                break;
            }
        }

        if ($headerRow === null) {
            throw new \RuntimeException("No se encontró la fila de encabezados en la hoja «{$sheetName}».");
        }

        $out = [];
        foreach ($grid as $rowNumber => $cells) {
            if ($rowNumber <= $headerRow) {
                continue;
            }
            $row = [];
            foreach ($columns as $key => $column) {
                $row[$key] = $cells[$column] ?? '';
            }
            if (implode('', $row) !== '') {
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * Asocia cada clave lógica con la columna cuyo encabezado contiene todos
     * sus substrings. Gana la coincidencia más corta (el encabezado más
     * específico) para evitar que "m² Interior privativo" y "m² Interior
     * totales proy." se pisen.
     *
     * @param  array<string, string>  $cells
     * @param  array<string, array<int, string>>  $patterns
     * @return array<string, string>
     */
    public static function matchHeaders(array $cells, array $patterns): array
    {
        $map = [];

        foreach ($cells as $column => $value) {
            $header = self::norm($value);
            if ($header === '') {
                continue;
            }

            foreach ($patterns as $key => $needles) {
                foreach ($needles as $needle) {
                    if (! str_contains($header, $needle)) {
                        continue 2;
                    }
                }
                if (! isset($map[$key]) || strlen($header) < $map[$key]['len']) {
                    $map[$key] = ['column' => $column, 'len' => strlen($header)];
                }
            }
        }

        return array_map(fn ($hit) => $hit['column'], $map);
    }

    /**
     * Devuelve la hoja como [nº de fila => [letra de columna => valor]].
     *
     * @return array<int, array<string, string>>
     */
    public static function grid(string $file, string $sheetName): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            throw new \RuntimeException("No se pudo abrir el .xlsx: {$file}");
        }

        try {
            $strings = self::sharedStrings($zip);
            $path    = self::sheetPath($zip, $sheetName);
            $xml     = simplexml_load_string($zip->getFromName($path));

            if ($xml === false) {
                throw new \RuntimeException("No se pudo leer la hoja «{$sheetName}».");
            }

            $grid = [];

            foreach ($xml->sheetData->row as $row) {
                $rowNumber = (int) $row['r'];

                foreach ($row->c as $cell) {
                    $ref    = (string) $cell['r'];
                    $column = preg_replace('/\d+/', '', $ref);
                    $type   = (string) $cell['t'];

                    if ($type === 'inlineStr') {
                        $value = (string) $cell->is->t;
                    } elseif ($type === 's') {
                        $value = $strings[(int) $cell->v] ?? '';
                    } else {
                        $value = isset($cell->v) ? (string) $cell->v : '';
                    }

                    $grid[$rowNumber][$column] = trim($value);
                }
            }

            ksort($grid);

            return $grid;
        } finally {
            $zip->close();
        }
    }

    /** Nombres de las hojas del archivo, en el orden del libro. */
    public static function sheetNames(string $file): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            throw new \RuntimeException("No se pudo abrir el .xlsx: {$file}");
        }

        try {
            $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
            if ($workbook === false) {
                throw new \RuntimeException('El archivo no parece un .xlsx válido (falta workbook.xml).');
            }

            $names = [];
            foreach ($workbook->sheets->sheet as $sheet) {
                $names[] = (string) $sheet['name'];
            }

            return $names;
        } finally {
            $zip->close();
        }
    }

    /** Ruta interna (xl/worksheets/sheetN.xml) de la hoja con ese nombre. */
    private static function sheetPath(\ZipArchive $zip, string $sheetName): string
    {
        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $rels     = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));

        if ($workbook === false || $rels === false) {
            throw new \RuntimeException('El archivo no parece un .xlsx válido (falta workbook.xml).');
        }

        $targets = [];
        foreach ($rels->Relationship as $rel) {
            $targets[(string) $rel['Id']] = ltrim((string) $rel['Target'], '/');
        }

        $rns = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        foreach ($workbook->sheets->sheet as $sheet) {
            if (self::norm((string) $sheet['name']) !== self::norm($sheetName)) {
                continue;
            }
            $rid    = (string) $sheet->attributes($rns)['id'];
            $target = $targets[$rid] ?? null;

            if (! $target) {
                break;
            }

            return str_starts_with($target, 'xl/') ? $target : 'xl/' . $target;
        }

        throw new \RuntimeException("El .xlsx no tiene una hoja llamada «{$sheetName}».");
    }

    /** Tabla de cadenas compartidas del .xlsx. */
    private static function sharedStrings(\ZipArchive $zip): array
    {
        $raw = $zip->getFromName('xl/sharedStrings.xml');
        if ($raw === false) {
            return [];
        }

        $xml = simplexml_load_string($raw);
        if ($xml === false) {
            return [];
        }

        $strings = [];
        foreach ($xml->si as $si) {
            // Texto plano (<t>) + texto con formato mixto (<r><t>…).
            $text = isset($si->t) ? (string) $si->t : '';
            foreach ($si->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /** Normaliza un texto para comparar encabezados (sin acentos, °, ², saltos). */
    public static function norm(string $value): string
    {
        $value = str_replace(["\n", "\r", "\t"], ' ', $value);
        $value = strtr(mb_strtolower($value, 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n', '°' => '', 'º' => '', '²' => '',
        ]);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    /** Parsea un número del Excel ("1,166.00" → 1166.0; texto → 0.0). */
    public static function num(string $raw): float
    {
        $clean = str_replace([',', ' ', "\u{a0}"], '', trim($raw));

        return is_numeric($clean) ? (float) $clean : 0.0;
    }
}
