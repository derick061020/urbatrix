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
 *   php artisan villas:import                    # reemplaza las villas actuales por las del Excel
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
                            {--no-images : No asignar el render de la tipología a cada villa}
                            {--refresh-images : Rehacer las galerías del repo de las villas existentes (respeta las subidas a mano)}
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
        // T4/T5 (Villa D/E) entraron en el levantamiento de septiembre. Mismo
        // criterio que los tres de arriba: ~US$2.500 por m² privativo.
        'T4' => 490_000,   // 195,6 m²
        'T5' => 600_000,   // 238,6 m²
    ];

    /**
     * Galería por tipología. Los renders definitivos del estudio (agosto 2026)
     * viven en public/images/villas/{carpeta}/: los archivos sueltos van como
     * imágenes de la villa (categoría «property», en orden alfabético, así que
     * el nombre manda el orden) y los de la subcarpeta planos/ como «plans».
     *
     * Es data-driven a propósito: para cambiar la galería de una tipología basta
     * con reemplazar archivos en su carpeta y volver a importar. Se respeta
     * cualquier imagen ya cargada a mano desde el panel.
     *
     * Si la carpeta no existe se cae a los placeholders de seed-villas, que son
     * los que usaba el home antes de tener renders.
     */
    private const GALLERY_DIRS = [
        'T1' => 'villa-a',
        'T2' => 'villa-b',
        'T3' => 'villa-c',
        'T4' => 'villa-d',
        'T5' => 'villa-e',
    ];

    /**
     * Nombre comercial de cada tipología. El Excel las llama «Villa A» … «Villa
     * E»; el nombre con el que se venden es este. La letra sigue viva en
     * custom_id (A-001) y en custom_1 («Villa A · Horizonte») para el panel.
     */
    private const NOMBRES = [
        'T1' => 'Horizonte',
        'T2' => 'Esencia',
        'T3' => 'Armonía',
        'T4' => 'Ámbar',
        'T5' => 'Remanso',
    ];

    /** Placeholders anteriores, sólo como respaldo si no hay galería real. */
    private const RENDERS = [
        'T1' => 'palma.jpg',
        'T2' => 'marea.jpg',
        'T3' => 'coral.jpg',
        'T4' => 'brisa.jpg',
        'T5' => 'arena.jpg',
    ];

    private const GALLERY_DIR = '/images/villas/';
    private const RENDER_DIR  = '/images/seed-villas/';

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
            $prot = $this->protectedVillasQuery()->count();
            $this->info("[dry-run] Se borrarían " . ($existing - $prot) . " villas (se conservarían {$prot} con reserva o no disponibles) y se crearían/actualizarían " . count($payloads) . '. Nada fue escrito.');

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
        $images  = 0;

        $withImages = ! $this->option('no-images');
        $refresh    = $withImages && $this->option('refresh-images');

        DB::transaction(function () use ($payloads, $withImages, $refresh, &$created, &$updated, &$images) {
            foreach ($payloads as $payload) {
                $existing = Unit::where('custom_id', $payload['custom_id'])->first();

                if ($existing) {
                    // Datos físicos sí; estado, precio y visibilidad no, que son
                    // los que se gestionan desde el panel y pueden tener reserva.
                    $existing->update(array_diff_key($payload, array_flip(['status', 'price', 'public'])));
                    $unit = $existing;
                    $updated++;
                } else {
                    $unit = Unit::create($payload);
                    $created++;
                }

                if ($refresh) {
                    $this->dropRepoImages($unit);
                }
                if ($withImages && $this->attachRender($unit)) {
                    $images++;
                }
            }
        });

        $this->newLine();
        if ($deleted) {
            $this->warn("Villas anteriores eliminadas: {$deleted}");
        }
        $this->info("Listo. Creadas: {$created} · Actualizadas: {$updated}" . ($withImages ? " · Villas con galería: {$images}" : ''));
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
            'custom_1'    => $typ['code_label'] !== $typ['model']
                ? $typ['code_label'] . ' · ' . $typ['model']
                : $typ['model'],
            'custom_2'    => $phase,
            'custom_3'    => $this->extras($typ),

            'public'               => ! $this->option('private'),
            'display_on_home_page' => false,
            'images_count'         => 0,
        ];
    }

    /**
     * Asigna el render de la tipología como imagen de la villa. Si la unidad ya
     * tiene imágenes (renders reales subidos desde el panel) no se toca.
     *
     * @return bool si se creó la imagen
     */
    private function attachRender(Unit $unit): bool
    {
        $existing = UnitImage::where('unit_id', $unit->id)->count();
        if ($existing > 0) {
            // Ya tiene imágenes (del panel o de una importación previa): se
            // respetan, pero el contador se deja consistente.
            if ((int) $unit->images_count !== $existing) {
                $unit->forceFill(['images_count' => $existing])->save();
            }

            return false;
        }

        $rows = $this->galleryFor($unit->layout);
        if ($rows === []) {
            return false;
        }

        // Todas las villas de una tipología comparten galería, así que en el
        // grid las 32 de Horizonte abrían con el mismo render. La portada rota
        // entre los exteriores según el número de lote: 001 → R1, 002 → R2 …
        // Es determinista, así que reimportar no baraja las portadas.
        $rows = $this->rotateCover($rows, $unit->custom_id);

        foreach ($rows as $i => [$category, $path]) {
            UnitImage::create([
                'unit_id'    => $unit->id,
                'category'   => $category,
                'name'       => ($unit->custom_1 ?: $unit->name) . ($i ? " ({$i})" : ''),
                'path'       => $path,
                'sort_order' => $i,
            ]);
        }

        $unit->forceFill(['images_count' => count($rows)])->save();

        return true;
    }

    /**
     * [[categoría, ruta pública], …] para una tipología, en el orden en que se
     * van a mostrar. Se memoriza por layout porque se llama una vez por villa.
     *
     * @return array<int, array{0:string,1:string}>
     */
    private array $galleryCache = [];

    private function galleryFor(?string $layout): array
    {
        if ($layout === null) {
            return [];
        }
        if (isset($this->galleryCache[$layout])) {
            return $this->galleryCache[$layout];
        }

        $rows = [];
        $dir  = self::GALLERY_DIRS[$layout] ?? null;
        $abs  = $dir ? public_path(self::GALLERY_DIR . $dir) : null;

        if ($abs && is_dir($abs)) {
            foreach ($this->imageFiles($abs) as $f) {
                $rows[] = ['property', self::GALLERY_DIR . $dir . '/' . $f];
            }
            if (is_dir($abs . '/planos')) {
                foreach ($this->imageFiles($abs . '/planos') as $f) {
                    $rows[] = ['plans', self::GALLERY_DIR . $dir . '/planos/' . $f];
                }
            }
        }

        // Sin galería real: el placeholder de siempre, para que la tarjeta no quede vacía.
        if ($rows === []) {
            $render = self::RENDERS[$layout] ?? null;
            if ($render && is_file(public_path(self::RENDER_DIR . $render))) {
                $rows[] = ['property', self::RENDER_DIR . $render];
            }
        }

        return $this->galleryCache[$layout] = $rows;
    }

    /** Rota el bloque de exteriores para que la portada dependa del lote. */
    private function rotateCover(array $rows, ?string $customId): array
    {
        $ext = array_values(array_filter($rows, fn ($r) => $r[0] === 'property' && str_contains($r[1], '-exterior-')));
        if (count($ext) < 2 || ! preg_match('/(\d+)\s*$/', (string) $customId, $m)) {
            return $rows;
        }
        $shift = ((int) $m[1] - 1) % count($ext);
        if ($shift === 0) {
            return $rows;
        }
        $rotated = array_merge(array_slice($ext, $shift), array_slice($ext, 0, $shift));
        $rest    = array_filter($rows, fn ($r) => ! ($r[0] === 'property' && str_contains($r[1], '-exterior-')));

        return array_values(array_merge($rotated, $rest));
    }

    /**
     * Borra las imágenes que puso este importador (galerías del repo y
     * placeholders), dejando intactas las subidas a mano desde el panel.
     */
    private function dropRepoImages(Unit $unit): void
    {
        UnitImage::where('unit_id', $unit->id)
            ->where(function ($q) {
                $q->where('path', 'like', self::GALLERY_DIR . '%')
                  ->orWhere('path', 'like', self::RENDER_DIR . '%');
            })
            ->delete();
    }

    /** Imágenes web de una carpeta, ordenadas por nombre (sin recursión). */
    private function imageFiles(string $dir): array
    {
        $out = [];
        foreach (scandir($dir) ?: [] as $f) {
            if ($f[0] === '.' || ! is_file("$dir/$f")) {
                continue;
            }
            if (preg_match('/\.(webp|jpe?g|png)$/i', $f)) {
                $out[] = $f;
            }
        }
        natcasesort($out);

        return array_values($out);
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
            // Nunca se borra una villa con compromiso comercial: `reservations`
            // cae en cascada al borrar la unidad, así que borrarla sería borrar
            // la reserva del cliente. Esas quedan y se actualizan por custom_id.
            $protected = $this->protectedVillasQuery()->pluck('custom_id', 'id');
            $ids       = $this->existingVillasQuery()
                ->whereNotIn('id', $protected->keys())
                ->pluck('id');
            $count = $ids->count();

            if ($protected->isNotEmpty()) {
                $this->warn('Con reserva o no disponibles, se conservan: ' . $protected->values()->sort()->implode(', '));
            }
            if ($count === 0) {
                return 0;
            }

            Deal::whereIn('unit_id', $ids)->delete();
            UnitImage::whereIn('unit_id', $ids)->delete();
            Unit::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    /** Villas que tienen reserva, deal, o no están disponibles. */
    private function protectedVillasQuery()
    {
        return $this->existingVillasQuery()->where(function ($q) {
            // La app guarda el estado tanto en mayúsculas como en minúsculas.
            $q->whereRaw('UPPER(status) <> ?', ['AVAILABLE'])
              ->orWhereHas('reservations')
              ->orWhereExists(function ($d) {
                  $d->selectRaw('1')->from('deals')->whereColumn('deals.unit_id', 'units.id');
              });
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

            $excelModel = Str::title(trim($row['model'] ?? '')) ?: $code;

            $out[$code] = [
                'code'            => $code,
                'model'           => self::NOMBRES[$code] ?? $excelModel,
                'code_label'      => $excelModel,   // «Villa A», para custom_1
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
            $map   = $this->matchHeaders($cells, $patterns);
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
