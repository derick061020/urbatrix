<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Unit;
use App\Support\PriceListRow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Actualiza los precios de las unidades desde una lista de precios nueva
 * (database/data/makai_etapa_*.csv, el mismo formato que usa units:import).
 *
 * Existe aparte de units:import porque aquel reescribe la unidad entera:
 * pisa `public` (deja fuera del home a las 6 unidades ocultas, o publica las
 * que no lo estaban), el status del CRM, áreas y descripción. Cuando el
 * estudio manda una lista nueva lo único que hay que mover son los precios,
 * así que este comando toca `price` y nada más, salvo que se pida --with-status.
 *
 * Por defecto sólo simula; hay que pasar --apply para escribir.
 *
 *   php artisan units:prices database/data/makai_etapa_2.csv
 *   php artisan units:prices database/data/makai_etapa_2.csv --apply
 *   php artisan units:prices database/data/makai_etapa_2.csv --apply --with-status
 */
class UpdateUnitPricesFromCsv extends Command
{
    protected $signature = 'units:prices
                            {files* : CSV con la lista de precios nueva (p. ej. database/data/makai_etapa_2.csv)}
                            {--project=Makai Cap Cana : Proyecto destino}
                            {--apply : Escribir de verdad (sin esto sólo simula)}
                            {--with-status : Aplicar también la columna Status (RESERVADA/BLOQUEADA/…)}
                            {--zero-blank : Poner precio 0 donde el CSV lo trae vacío (por defecto se respeta el de producción)}
                            {--create-missing : Crear las unidades del CSV que no existan en el proyecto (públicas)}
                            {--adopt-orphans : Si una unidad del CSV existe sin proyecto (creada a mano), asignarla al proyecto en vez de crear otra}';

    protected $description = 'Actualiza sólo los precios de las unidades desde la lista de precios en CSV';

    public function handle(): int
    {
        // A propósito no hay glob por defecto: una lista nueva llega por etapa,
        // y pasar las dos reescribiría la otra etapa con el CSV viejo del repo
        // (que ya quedó atrás respecto a los precios editados en el panel).
        $files = $this->argument('files');

        $project = Project::where('name', $this->option('project'))->first();
        if (! $project) {
            $this->error("Proyecto «{$this->option('project')}» no existe.");

            return self::FAILURE;
        }

        $apply      = (bool) $this->option('apply');
        $withStatus = (bool) $this->option('with-status');
        $zeroBlank  = (bool) $this->option('zero-blank');
        $create     = (bool) $this->option('create-missing');
        $adopt      = (bool) $this->option('adopt-orphans');

        $this->info("Proyecto: {$project->name}" . ($apply ? '' : ' · SIMULACIÓN (usa --apply para escribir)'));
        foreach ($files as $f) {
            $this->line("→ {$f}");
        }
        $this->newLine();

        $rows = $this->read($files);
        if ($rows === []) {
            $this->error('Los CSV no traen ninguna fila de unidad válida.');

            return self::FAILURE;
        }

        $units = $project->units()->get()->keyBy('name');

        $changes = [];   // filas de la tabla
        $missing = [];   // en el CSV pero no en el proyecto
        $blank   = [];   // sin precio en el CSV
        $adopted = [];   // huérfanas asignadas al proyecto
        $same    = 0;

        foreach ($rows as $name => $row) {
            $unit = $units->get($name);

            // Una unidad creada a mano desde el panel queda con project_id
            // NULL pero sigue saliendo en el home. Con --adopt-orphans se
            // asigna al proyecto y se trata como existente (se le mueve el
            // precio); sin la opción se reporta y no se toca.
            if (! $unit && $adopt && ($orphan = Unit::whereNull('project_id')->where('name', $name)->first())) {
                $payload = PriceListRow::toPayload($row['row'], $row['floor'], $project->id, (bool) $orphan->public);
                if ($apply) {
                    $orphan->forceFill(['project_id' => $project->id, 'custom_2' => $payload['custom_2']])->save();
                }
                $adopted[] = $name;
                $unit = $orphan;
            }

            if (! $unit) {
                $missing[$name] = $row;
                continue;
            }

            $oldPrice = (float) $unit->price;
            $newPrice = $row['price'];
            $oldSt    = (string) $unit->status;
            $newSt    = PriceListRow::mapStatus($row['status']);

            if ($newPrice === null && ! $zeroBlank) {
                $blank[] = [$name, $oldPrice, $oldSt, $newSt];
                $newPrice = $oldPrice;   // se respeta el de producción
            }
            $newPrice ??= 0.0;

            $priceMoved  = abs($newPrice - $oldPrice) >= 0.005;
            $statusMoved = $withStatus && $newSt !== $oldSt;

            if (! $priceMoved && ! $statusMoved) {
                $same++;
                continue;
            }

            $changes[] = [
                'unit'   => $unit,
                'name'   => $name,
                'old'    => $oldPrice,
                'new'    => $newPrice,
                'oldSt'  => $oldSt,
                'newSt'  => $newSt,
                'price'  => $priceMoved,
                'status' => $statusMoved,
            ];
        }

        $this->render($changes, $blank, $missing, $same, $withStatus, $zeroBlank, $create);

        if ($adopted !== []) {
            $this->newLine();
            $this->info(($apply ? '  Asignadas' : '  Se asignarían') . " al proyecto «{$project->name}» "
                . count($adopted) . ' unidades que estaban sin proyecto: ' . implode(', ', $adopted) . '.');
        }

        if ($create && $missing !== [] && $apply) {
            $made = [];
            DB::transaction(function () use ($missing, $project, &$made) {
                foreach ($missing as $name => $row) {
                    // Guarda contra duplicados: la unidad puede existir fuera
                    // del proyecto (creada a mano desde el panel queda con
                    // project_id NULL) y seguiría saliendo en el home, así que
                    // crear otra dejaría dos unidades con el mismo número.
                    if (Unit::where('name', $name)->exists()) {
                        continue;
                    }
                    Unit::create(PriceListRow::toPayload($row['row'], $row['floor'], $project->id, true));
                    $made[] = $name;
                }
            });

            $skipped = array_diff(array_keys($missing), $made);
            if ($made !== []) {
                $this->info('Creadas ' . count($made) . ' unidades nuevas: ' . implode(', ', $made) . '.');
            }
            foreach ($skipped as $name) {
                $other = Unit::where('name', $name)->first();
                $this->warn("  ⚠ {$name} ya existe fuera de «{$project->name}» "
                    . '(id ' . $other->id . ', proyecto ' . ($other->project_id ?? 'ninguno')
                    . ', precio ' . number_format((float) $other->price) . '): no se creó para no duplicarla.');
                $this->line('    Si está sin proyecto, --adopt-orphans la asigna en vez de crear otra.');
            }
        }

        if ($changes === []) {
            $this->info('No hay precios ni estados que cambiar.');

            return self::SUCCESS;
        }

        if (! $apply) {
            $this->newLine();
            $this->comment('Simulación: no se escribió nada. Para aplicar, repetí el comando con --apply.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes, $withStatus) {
            foreach ($changes as $c) {
                $attrs = [];
                if ($c['price']) {
                    $attrs['price'] = $c['new'];
                }
                if ($withStatus && $c['status']) {
                    $attrs['status'] = $c['newSt'];
                }
                if ($attrs !== []) {
                    // forceFill + save: nada de mass-assignment sorpresa, y no
                    // se toca ninguna otra columna (public, áreas, imágenes…).
                    $c['unit']->forceFill($attrs)->save();
                }
            }
        });

        $this->newLine();
        $this->info('Aplicado: ' . count(array_filter($changes, fn ($c) => $c['price'])) . ' precios'
            . ($withStatus ? ' · ' . count(array_filter($changes, fn ($c) => $c['status'])) . ' estados' : '')
            . '.');

        return self::SUCCESS;
    }

    /**
     * Lee los CSV y devuelve [nombre de unidad => ['price' => ?float, 'status' => string]].
     * La última aparición gana, igual que en units:import.
     */
    private function read(array $files): array
    {
        $out = [];
        foreach ($files as $file) {
            if (! is_readable($file)) {
                $this->error("No se puede leer: {$file}");
                continue;
            }
            $h = fopen($file, 'r');
            $floor = null;
            while (($row = fgetcsv($h)) !== false) {
                if ($f = PriceListRow::detectFloor($row)) {
                    $floor = $f;
                    continue;
                }
                if (! PriceListRow::isUnit($row)) {
                    continue;
                }
                $name = PriceListRow::name($row);
                $out[$name] = [
                    'price'  => PriceListRow::money(trim($row[PriceListRow::COL_PRICE] ?? '')),
                    'status' => trim($row[PriceListRow::COL_STATUS] ?? ''),
                    'row'    => $row,
                    'floor'  => $floor,
                ];
            }
            fclose($h);
        }

        return $out;
    }

    private function render(array $changes, array $blank, array $missing, int $same, bool $withStatus, bool $zeroBlank, bool $create = false): void
    {
        if ($changes !== []) {
            $rows = [];
            $totalOld = $totalNew = 0.0;
            foreach ($changes as $c) {
                $delta = $c['new'] - $c['old'];
                $pct   = $c['old'] > 0 ? sprintf('%+.1f%%', ($c['new'] / $c['old'] - 1) * 100) : '—';
                $rows[] = [
                    $c['name'],
                    $c['price'] ? number_format($c['old']) : '',
                    $c['price'] ? number_format($c['new']) : '',
                    $c['price'] ? sprintf('%+s', number_format($delta)) : '',
                    $c['price'] ? $pct : '',
                    $c['status'] ? "{$c['oldSt']} → {$c['newSt']}" : ($withStatus ? '' : ''),
                ];
                $totalOld += $c['old'];
                $totalNew += $c['new'];
            }
            $this->table(['Unidad', 'Precio actual', 'Precio nuevo', 'Δ', '%', 'Estado'], $rows);
            $this->line('  Cartera: ' . number_format($totalOld) . ' → ' . number_format($totalNew)
                . '  (' . sprintf('%+.1f%%', $totalOld > 0 ? ($totalNew / $totalOld - 1) * 100 : 0) . ')');
        }

        $this->newLine();
        $this->line("  Sin cambios: {$same}");

        if ($blank !== []) {
            $this->newLine();
            $this->warn('  Sin precio en el CSV (' . count($blank) . '): se respeta el de producción.');
            $conPrecio = array_filter($blank, fn ($b) => $b[1] > 0);
            if ($conPrecio !== []) {
                $this->line('    Con precio vigente que se conserva: '
                    . collect($conPrecio)->map(fn ($b) => $b[0] . ' (' . number_format($b[1]) . ')')->join(', '));
                $this->line('    Para ponerlas en 0 como el CSV: --zero-blank');
            }
        }

        if ($missing !== []) {
            $this->newLine();
            $this->warn('  En el CSV pero no en el proyecto (' . count($missing) . '): ' . implode(', ', array_keys($missing)));
            $orphans = Unit::whereNull('project_id')->whereIn('name', array_keys($missing))->pluck('name')->all();
            if ($orphans !== []) {
                $this->line('    Existen sin proyecto (creadas a mano): ' . implode(', ', $orphans) . ' → --adopt-orphans las asigna.');
            }
            $this->line($create
                ? '    Las que no existan en ningún lado se crearán como públicas, con los datos del CSV.'
                : '    No se crean; para crearlas pasá --create-missing.');
        }

        if (! $withStatus) {
            $this->newLine();
            $this->line('  Los estados del CSV se ignoran (--with-status para aplicarlos).');
        }
    }
}
