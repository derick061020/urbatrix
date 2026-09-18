<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Unit;
use App\Support\PriceListRow;
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
                    if ($f = PriceListRow::detectFloor($row)) {
                        $floor = $f;
                        continue;
                    }

                    // Datos válidos: número de unidad numérico + tipología presente.
                    if (! PriceListRow::isUnit($row)) {
                        continue;
                    }

                    $payload = PriceListRow::toPayload($row, $floor, $project->id, $public);

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
}
