<?php

namespace App\Console\Commands;

use App\Models\Deal;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importa TODAS las villas del levantamiento de datos de Bahía Mar
 * (Bahia_Mar_Levantamiento_Datos.xlsx) y reemplaza las villas que hubiera
 * cargadas en la base.
 *
 * El Excel trae dos hojas relevantes:
 *
 *   · «Tipologias» → una fila por modelo de villa (T1 = VILLA A, T2 = VILLA B,
 *     T3 = VILLA C, …) con el programa (plantas, habitaciones, baños) y los m²
 *     (interior privativo, terraza, roof/solárium, piscina, picuzzi).
 *
 *   · «Unidades»   → una fila por villa real (A-001, B-020, C-078 …) con su
 *     tipología, etapa/fase, estatus y, en «Notas», el área del lote.
 *
 * Ojo con la hoja «Unidades»: sus columnas de m² y precio son VLOOKUPs contra
 * «Tipologias» con los índices desalineados (m² Total privativo trae en
 * realidad los m² de picuzzi, y $/m² trae la eficiencia arquitectónica). Por eso
 * este importador ignora esas columnas calculadas y resuelve todas las medidas
 * leyendo la tipología directamente.
 *
 * El Excel tampoco trae precios ($/m² objetivo está vacío), así que se aplican
 * los precios estimados provisionales de {@see self::PRECIOS_ESTIMADOS}
 * —editables luego desde el admin— o un $/m² uniforme con --price-per-m2.
 *
 * Uso:
 *   php artisan villas:import                    # borra las villas actuales e importa las 248
 *   php artisan villas:import --dry-run          # muestra el resumen sin tocar la base
 *   php artisan villas:import --keep-existing    # importa sin borrar (actualiza por custom_id)
 *   php artisan villas:import --price-per-m2=3500
 */
class ImportVillasFromExcel extends Command
{
    protected $signature = 'villas:import
                            {file? : Ruta al .xlsx (por defecto Bahia_Mar_Levantamiento_Datos.xlsx en la raíz)}
                            {--project= : Nombre del proyecto destino (por defecto las villas quedan sin proyecto)}
                            {--keep-existing : No borrar las villas actuales antes de importar}
                            {--price-per-m2= : Precio = $/m² × m² privativos, en vez de los estimados por tipología}
                            {--private : Importa las villas como NO públicas (no se ven en el home)}
                            {--force : No pedir confirmación al borrar}
                            {--dry-run : Muestra lo que haría sin escribir en la base}';

    protected $description = 'Importa todas las villas desde el Excel de levantamiento y reemplaza las existentes';

    /** Excel por defecto, en la raíz del proyecto. */
    private const DEFAULT_FILE = 'Bahia_Mar_Levantamiento_Datos.xlsx';

    /** Valor de `type` que identifica a una villa (y que se usa para borrarlas). */
    private const UNIT_TYPE = 'Villa';

    /**
     * Precios estimados provisionales por tipología (USD por unidad). El Excel
     * no trae precios; estos son de referencia para que el catálogo se vea
     * completo y se ajustan después desde el panel de unidades.
     */
    private const PRECIOS_ESTIMADOS = [
        'T1' => 1_100_000,
        'T2' => 750_000,
        'T3' => 620_000,
    ];

    public function handle(): int
    {
        $file = $this->argument('file') ?: base_path(self::DEFAULT_FILE);

        if (! is_readable($file)) {
            $this->error("No se puede leer el Excel: {$file}");

            return self::FAILURE;
        }

        $project = null;
        if ($name = $this->option('project')) {
            $project = Project::where('name', $name)->first();
            if (! $project) {
                $this->error("Proyecto «{$name}» no existe.");

                return self::FAILURE;
            }
        }

        // --- Lectura del Excel -------------------------------------------------
        try {
            $typologies = $this->readTypologies($file);
            $rows       = $this->readUnits($file);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if (empty($typologies)) {
            $this->error('La hoja «Tipologias» no tiene filas de datos.');

            return self::FAILURE;
        }
        if (empty($rows)) {
            $this->error('La hoja «Unidades» no tiene filas de datos.');

            return self::FAILURE;
        }

        $this->line('Tipologías leídas: ' . implode(', ', array_map(
            fn ($t) => "{$t['code']} ({$t['model']}, {$t['bedrooms']}h)",
            $typologies
        )));
        $this->line('Villas en el Excel: ' . count($rows));

        // --- Mapeo fila → atributos de unidad ----------------------------------
        $payloads = [];
        $skipped  = [];

        foreach ($rows as $row) {
            $code = strtoupper($row['code'] ?? '');
            if (! isset($typologies[$code])) {
                $skipped[] = ($row['unit'] ?? '?') . " (tipología «{$code}» no está en la hoja Tipologias)";
                continue;
            }
            $payloads[] = $this->mapVilla($row, $typologies[$code], $project?->id);
        }

        foreach ($skipped as $s) {
            $this->warn("Omitida: {$s}");
        }

        if (empty($payloads)) {
            $this->error('No quedó ninguna villa importable.');

            return self::FAILURE;
        }

        $this->summary($payloads);

        // --- Dry run ------------------------------------------------------------
        if ($this->option('dry-run')) {
            $existing = $this->existingVillasQuery()->count();
            $this->newLine();
            $this->info("[dry-run] Se borrarían {$existing} villas y se crearían " . count($payloads) . '. Nada fue escrito.');

            return self::SUCCESS;
        }

        // --- Borrado de las villas actuales -------------------------------------
        $deleted = 0;
        if (! $this->option('keep-existing')) {
            $existing = $this->existingVillasQuery()->count();

            if ($existing > 0 && ! $this->option('force')
                && ! $this->confirm("Se borrarán {$existing} villas existentes (type = «" . self::UNIT_TYPE . "»). ¿Continuar?")) {
                $this->info('Cancelado.');

                return self::SUCCESS;
            }

            $deleted = $this->deleteExistingVillas();
        }

        // --- Alta -----------------------------------------------------------------
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($payloads, &$created, &$updated) {
            foreach ($payloads as $payload) {
                $existing = Unit::where('custom_id', $payload['custom_id'])->first();

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    Unit::create($payload);
                    $created++;
                }
            }
        });

        $this->newLine();
        if ($deleted) {
            $this->warn("Villas anteriores eliminadas: {$deleted}");
        }
        $this->info("Listo. Creadas: {$created} · Actualizadas: {$updated}");
        $this->info('Total de villas en la base: ' . $this->existingVillasQuery()->count());

        if (! $this->option('price-per-m2')) {
            $this->comment('Precios cargados con los estimados provisionales por tipología: ajustalos desde el panel de unidades.');
        }

        return self::SUCCESS;
    }

    // =========================================================================
    // Mapeo
    // =========================================================================

    /** Construye los atributos de una unidad a partir de la fila + su tipología. */
    private function mapVilla(array $row, array $typ, ?int $projectId): array
    {
        $unitCode = trim($row['unit'] ?? '');
        $phase    = trim($row['phase'] ?? '') ?: null;
        $notes    = trim($row['notes'] ?? '');

        $interior = $typ['interior'];
        $terrace  = $typ['terrace'];
        $roof     = $typ['roof'];
        $total    = $typ['privative_total'] ?: ($interior + $terrace + $roof);

        // El nº de plantas puede venir en la fila; si no, el de la tipología.
        $stories = (int) round($this->num($row['level'] ?? '') ?: $typ['stories']);

        // "Villa A" + el número de lote → "Villa A 001" (custom_id conserva A-001).
        $lotNumber = ltrim(preg_replace('/^[A-Za-z]+[\s\-]*/', '', $unitCode)) ?: $unitCode;

        return [
            'project_id' => $projectId,
            'name'       => trim($typ['model'] . ' ' . $lotNumber),
            'custom_id'  => $unitCode,
            'type'       => self::UNIT_TYPE,
            'status'     => $this->mapStatus($row['status'] ?? ''),
            'price'      => $this->price($typ, $total),

            // Programa
            'layout'    => $typ['code'],
            'stories'   => $stories ?: null,
            'bedrooms'  => (int) round($this->num($row['bedrooms'] ?? '') ?: $typ['bedrooms']),
            'bathrooms' => $this->num($row['bathrooms'] ?? '') ?: $typ['bathrooms'],
            'pools'     => $typ['pool'] > 0 ? 1 : 0,
            'outlook'   => $typ['outlook'] ?: null,

            // Etapa comercial. `floor` es lo que agrupa las filas del masterplan
            // del home, así que se usa la fase para que cada fase sea una fila.
            'phase' => $phase,
            'floor' => $phase,

            // Dimensiones
            'internal_area' => $interior,
            'external_area' => $terrace,
            'roof_area'     => $roof ?: null,
            'total_area'    => $total,
            'plot_area'     => $this->plotArea($notes),

            // Información complementaria
            'description' => $typ['description'] ?: null,
            'custom_1'    => $typ['model'],
            'custom_2'    => $phase,
            'custom_3'    => $this->extras($typ),

            'public'               => ! $this->option('private'),
            'display_on_home_page' => false,
            'images_count'         => 0,
        ];
    }

    /** Precio de la villa: $/m² uniforme si se pasó la opción, si no el estimado. */
    private function price(array $typ, float $totalM2): float
    {
        if ($perM2 = (float) $this->option('price-per-m2')) {
            return round($perM2 * $totalM2, 2);
        }

        return (float) (self::PRECIOS_ESTIMADOS[$typ['code']] ?? 0);
    }

    /** «Área lote: 728.03 m² | …» → 728.03 (null si el lote está pendiente). */
    private function plotArea(string $notes): ?float
    {
        if (preg_match('/(?:area|área)\s+lote:\s*([\d.,]+)/iu', $notes, $m)) {
            return $this->num($m[1]) ?: null;
        }

        return null;
    }

    /** Detalle de piscina / picuzzi para el campo libre custom_3. */
    private function extras(array $typ): ?string
    {
        $parts = [];
        if ($typ['pool'] > 0) {
            $parts[] = 'Piscina ' . $this->m2($typ['pool']) . ' m²';
        }
        if ($typ['jacuzzi'] > 0) {
            $parts[] = 'Jacuzzi ' . $this->m2($typ['jacuzzi']) . ' m²';
        }
        if ($typ['roof'] > 0) {
            $parts[] = 'Roof/solárium ' . $this->m2($typ['roof']) . ' m²';
        }

        return $parts ? implode(' · ', $parts) : null;
    }

    /** Mapea el estatus del Excel al estado interno (en minúsculas, como el home). */
    private function mapStatus(string $raw): string
    {
        $s = Str::ascii(mb_strtolower(trim($raw)));

        return match (true) {
            str_contains($s, 'vendid') || str_contains($s, 'sold')     => 'sold',
            str_contains($s, 'reservad') || str_contains($s, 'reserv') => 'reserved',
            str_contains($s, 'bloquead') || str_contains($s, 'hold')   => 'pending',
            default                                                    => 'available',
        };
    }

    // =========================================================================
    // Borrado
    // =========================================================================

    /** Query de las villas ya cargadas (las que este comando reemplaza). */
    private function existingVillasQuery()
    {
        return Unit::where('type', self::UNIT_TYPE);
    }

    /**
     * Borra las villas actuales. `deals` referencia units con FK restrictiva
     * (sin cascade), así que se eliminan primero; el resto de relaciones
     * (reservations, imágenes, wishlists, broker_unit…) sí cae en cascada.
     */
    private function deleteExistingVillas(): int
    {
        return DB::transaction(function () {
            $ids   = $this->existingVillasQuery()->pluck('id');
            $count = $ids->count();

            if ($count === 0) {
                return 0;
            }

            Deal::whereIn('unit_id', $ids)->delete();
            UnitImage::whereIn('unit_id', $ids)->delete();
            Unit::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    // =========================================================================
    // Lectura del .xlsx (ZipArchive + SimpleXML, sin dependencias extra)
    // =========================================================================

    /** Hoja «Tipologias» → [código => datos del modelo]. */
    private function readTypologies(string $file): array
    {
        $rows = $this->sheetRows($file, 'Tipologias', [
            'code'            => ['codigo'],
            'model'           => ['nombre', 'tipologia'],
            'description'     => ['uso', 'descripcion'],
            'stories'         => ['plantas'],
            'bedrooms'        => ['hab'],
            'bathrooms'       => ['banos'],
            'outlook'         => ['vista'],
            'interior'        => ['interior', 'privativo'],
            'terrace'         => ['terraza'],
            'roof'            => ['roof'],
            'pool'            => ['piscina'],
            'jacuzzi'         => ['picuzzi'],
            'privative_total' => ['privativo', 'total'],
        ]);

        $out = [];

        foreach ($rows as $row) {
            $code = strtoupper(trim($row['code'] ?? ''));

            // La hoja cierra con una fila TOTAL y notas al pie: sólo T1, T2, …
            if (! preg_match('/^T\d+$/', $code)) {
                continue;
            }

            $out[$code] = [
                'code'            => $code,
                'model'           => Str::title(trim($row['model'] ?? '')) ?: $code,
                'description'     => trim($row['description'] ?? ''),
                'stories'         => $this->num($row['stories'] ?? ''),
                'bedrooms'        => $this->num($row['bedrooms'] ?? ''),
                'bathrooms'       => $this->num($row['bathrooms'] ?? ''),
                'outlook'         => trim($row['outlook'] ?? ''),
                'interior'        => $this->num($row['interior'] ?? ''),
                'terrace'         => $this->num($row['terrace'] ?? ''),
                'roof'            => $this->num($row['roof'] ?? ''),
                'pool'            => $this->num($row['pool'] ?? ''),
                'jacuzzi'         => $this->num($row['jacuzzi'] ?? ''),
                'privative_total' => $this->num($row['privative_total'] ?? ''),
            ];
        }

        return $out;
    }

    /** Hoja «Unidades» → filas de villas (sólo las columnas confiables). */
    private function readUnits(string $file): array
    {
        $rows = $this->sheetRows($file, 'Unidades', [
            'unit'      => ['nombre', 'unidad'],
            'code'      => ['codigo', 'tipologia'],
            'phase'     => ['etapa'],
            'level'     => ['nivel'],
            'bedrooms'  => ['hab'],
            'bathrooms' => ['banos'],
            'status'    => ['estatus'],
            'notes'     => ['notas'],
        ]);

        return array_values(array_filter(
            $rows,
            fn ($row) => trim($row['unit'] ?? '') !== '' && trim($row['code'] ?? '') !== ''
        ));
    }

    /**
     * Abre una hoja del .xlsx, localiza la fila de encabezados a partir de
     * $patterns y devuelve las filas siguientes ya mapeadas a esas claves.
     *
     * @param  array<string, array<int, string>>  $patterns  clave lógica => substrings que debe contener el encabezado
     * @return array<int, array<string, string>>
     */
    private function sheetRows(string $file, string $sheetName, array $patterns): array
    {
        $grid = $this->sheetGrid($file, $sheetName);

        // Fila de encabezados = la primera que resuelve al menos la mitad de las claves.
        $headerRow = null;
        $columns   = [];

        foreach ($grid as $rowNumber => $cells) {
            $map = $this->matchHeaders($cells, $patterns);
            if (count($map) >= (int) ceil(count($patterns) / 2)) {
                $headerRow = $rowNumber;
                $columns   = $map;
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
     * @return array<string, string> clave lógica => letra de columna
     */
    private function matchHeaders(array $cells, array $patterns): array
    {
        $map = [];

        foreach ($cells as $column => $value) {
            $header = $this->norm($value);
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
    private function sheetGrid(string $file, string $sheetName): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            throw new \RuntimeException("No se pudo abrir el .xlsx: {$file}");
        }

        try {
            $strings = $this->sharedStrings($zip);
            $path    = $this->sheetPath($zip, $sheetName);
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

    /** Ruta interna (xl/worksheets/sheetN.xml) de la hoja con ese nombre. */
    private function sheetPath(\ZipArchive $zip, string $sheetName): string
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
            if ($this->norm((string) $sheet['name']) !== $this->norm($sheetName)) {
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
    private function sharedStrings(\ZipArchive $zip): array
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

    // =========================================================================
    // Utilidades
    // =========================================================================

    /** Normaliza un texto para comparar encabezados (sin acentos, °, ², saltos). */
    private function norm(string $value): string
    {
        $value = str_replace(["\n", "\r", "\t"], ' ', $value);
        $value = strtr(mb_strtolower($value, 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n', '°' => '', 'º' => '', '²' => '',
        ]);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    /** Parsea un número del Excel ("1,166.00" → 1166.0; texto → 0.0). */
    private function num(string $raw): float
    {
        $clean = str_replace([',', ' ', "\u{a0}"], '', trim($raw));

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    /** Formatea m² sin decimales inútiles (21.28 → "21.28", 29.0 → "29"). */
    private function m2(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    /** Resumen por tipología / fase antes de escribir. */
    private function summary(array $payloads): void
    {
        $byTypology = [];
        $byPhase    = [];

        foreach ($payloads as $p) {
            $byTypology[$p['layout']]['count'] = ($byTypology[$p['layout']]['count'] ?? 0) + 1;
            $byTypology[$p['layout']]['model'] = $p['custom_1'];
            $byTypology[$p['layout']]['price'] = $p['price'];
            $byPhase[$p['phase'] ?: '—']       = ($byPhase[$p['phase'] ?: '—'] ?? 0) + 1;
        }

        ksort($byTypology);
        ksort($byPhase);

        $this->newLine();
        $this->table(
            ['Tipología', 'Modelo', 'Villas', 'Precio'],
            array_map(
                fn ($code, $data) => [$code, $data['model'], $data['count'], '$' . number_format($data['price'], 0)],
                array_keys($byTypology),
                $byTypology
            )
        );
        $this->line('Por fase: ' . implode(' · ', array_map(
            fn ($phase, $n) => "{$phase}: {$n}",
            array_keys($byPhase),
            $byPhase
        )));
    }
}
