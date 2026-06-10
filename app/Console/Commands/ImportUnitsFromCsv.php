<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Importa unidades de Makai desde las listas de precios en CSV
 * (database/data/makai_etapa_*.csv).
 *
 * Estructura esperada del CSV (17 columnas, generadas desde el Excel original):
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
 *   16 Status                      → status (RESERVAD*→RESERVED, BLOQUEAD*→HELD, vacío→AVAILABLE)
 *
 * La planta (floor) se deduce de las filas separadoras de sección
 * ("1ER NIVEL / 1ST FLOOR", "6TO NIVEL / PENTHOUSE", …).
 */
class ImportUnitsFromCsv extends Command
{
    protected $signature = 'units:import
                            {files?* : Rutas a los CSV (por defecto database/data/makai_etapa_*.csv)}
                            {--project=Makai Cap Cana : Nombre del proyecto destino}
                            {--fresh : Borra todas las unidades del proyecto antes de importar}
                            {--public : Marca las unidades como públicas (visibles en el home)}
                            {--force : No pedir confirmación al borrar}';

    protected $description = 'Importa/recrea las unidades de un proyecto desde las listas de precios en CSV';

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

    public function handle(): int
    {
        $files = $this->argument('files');
        if (empty($files)) {
            $files = glob(database_path('data/makai_etapa_*.csv'));
            sort($files);
        }

        if (empty($files)) {
            $this->error('No se encontraron CSV para importar.');
            return self::FAILURE;
        }

        $project = Project::where('name', $this->option('project'))->first();
        if (! $project) {
            $this->error("Proyecto «{$this->option('project')}» no existe.");
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $count = $project->units()->count();
            if ($count > 0 && ! $this->option('force')
                && ! $this->confirm("Se borrarán {$count} unidades existentes de «{$project->name}». ¿Continuar?")) {
                $this->info('Cancelado.');
                return self::SUCCESS;
            }
            // `deals` referencia units con FK restrictiva (sin cascade); el resto
            // de relaciones (reservations, imágenes, historiales, broker_unit…) sí
            // cae en cascada. Borramos los deals de estas unidades primero.
            $unitIds = $project->units()->pluck('id');
            $deals = \App\Models\Deal::whereIn('unit_id', $unitIds)->delete();
            $project->units()->delete();
            $this->warn("Eliminadas {$count} unidades previas de «{$project->name}» (y {$deals} deals asociados).");
        }

        $public = $this->option('public');
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($files, $project, $public, &$created, &$updated) {
            foreach ($files as $file) {
                if (! is_readable($file)) {
                    $this->error("No se puede leer: {$file}");
                    continue;
                }
                $this->line("→ {$file}");
                $floor = null;
                $handle = fopen($file, 'r');

                while (($row = fgetcsv($handle)) !== false) {
                    // ¿Fila separadora de sección? Actualiza la planta actual.
                    if ($f = $this->detectFloor($row)) {
                        $floor = $f;
                        continue;
                    }

                    $unitNo = trim($row[0] ?? '');
                    $typo   = trim($row[1] ?? '');

                    // Datos válidos: número de unidad numérico + tipología presente.
                    if ($typo === '' || ! ctype_digit($unitNo)) {
                        continue;
                    }

                    $payload = $this->mapRow($row, $floor, $project->id, $public);

                    $existing = Unit::where('project_id', $project->id)
                        ->where('name', $payload['name'])->first();

                    if ($existing) {
                        $existing->update($payload);
                        $updated++;
                    } else {
                        Unit::create($payload);
                        $created++;
                    }
                }
                fclose($handle);
            }
        });

        $this->newLine();
        $this->info("Listo. Creadas: {$created} · Actualizadas: {$updated}");
        $this->info("Total en «{$project->name}»: " . $project->units()->count());

        return self::SUCCESS;
    }

    /** Devuelve el valor de floor si la fila es un separador de sección. */
    private function detectFloor(array $row): ?string
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

    /** Construye el array de atributos de la unidad a partir de la fila. */
    private function mapRow(array $row, ?string $floor, int $projectId, bool $public): array
    {
        $get = fn (int $i) => isset($row[$i]) ? trim($row[$i]) : '';

        $bedroomsRaw = $get(3);
        $feature     = $get(6);
        $views       = $get(5);

        return [
            'project_id'    => $projectId,
            'name'          => $get(0),
            'layout'        => $typo = $get(1),
            'type'          => $this->mapType($bedroomsRaw, $feature, $floor),
            'status'        => $this->mapStatus($get(16)),
            'floor'         => $floor,
            'outlook'       => $this->mapOutlook($views),
            'bedrooms'      => (int) filter_var($bedroomsRaw, FILTER_SANITIZE_NUMBER_INT),
            'bathrooms'     => (float) str_replace(',', '.', $get(4)) ?: 0,
            'internal_area' => $this->num($get(8)),
            'external_area' => $this->num($get(10)),
            'total_area'    => $this->num($get(12)),
            'price'         => $this->money($get(15)),
            'custom_1'      => ($feature !== '' && $feature !== '-') ? $feature : null,
            'custom_2'      => 'Etapa ' . ($get(2) ?: '?'),
            'custom_3'      => $get(14) !== '' ? 'Rooftop/Jardín ' . $get(14) . ' m²' : null,
            'public'        => $public,
            'description'   => trim("{$typo} · {$views}", ' ·'),
        ];
    }

    /** Mapea bedrooms + feature + planta al valor de `type` (UnitOptions::types). */
    private function mapType(string $bedroomsRaw, string $feature, ?string $floor): string
    {
        $beds    = (int) filter_var($bedroomsRaw, FILTER_SANITIZE_NUMBER_INT);
        $feat    = strtolower($feature);
        $isPent  = $floor === '6th';

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
            $beds >= 3 => '3_bed',
            $beds === 2 => '2_bed',
            default     => '1_bed',
        };
    }

    /** Mapea la columna Status al estado interno. */
    private function mapStatus(string $raw): string
    {
        $s = strtoupper($raw);
        return match (true) {
            str_contains($s, 'RESERVAD') => 'RESERVED',
            str_contains($s, 'BLOQUEAD') => 'HELD',
            str_contains($s, 'VENDID') || str_contains($s, 'SOLD') => 'SOLD',
            default => 'AVAILABLE',
        };
    }

    /** Mapea la vista al valor de outlook (UnitOptions::outlooks). */
    private function mapOutlook(string $views): ?string
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
    private function num(string $raw): float
    {
        $clean = str_replace([',', ' '], '', $raw);
        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    /** Parsea un precio ("$442,000.00" → 442000.0, vacío → 0). */
    private function money(string $raw): float
    {
        $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '', $raw));
        return is_numeric($clean) ? (float) $clean : 0.0;
    }
}
