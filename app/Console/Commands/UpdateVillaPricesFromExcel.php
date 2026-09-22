<?php

namespace App\Console\Commands;

use App\Models\Unit;
use App\Support\VillaPriceList;
use App\Support\XlsxSheet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Baja a las villas la hoja «Precios» del levantamiento de Bahía Mar: el
 * precio de venta real de cada lote y la vista que le toca.
 *
 * Hasta ahora el catálogo salía con los precios estimados provisionales de
 * villas:import (uno por tipología, ~US$2.500/m² privativo) porque el Excel
 * no los traía. La hoja «Precios» los calcula por unidad:
 *
 *     precio = área lote × $/m² lote + m² interior × $/m² interior
 *                                     + m² terraza × $/m² terraza
 *
 * así que dos villas de la misma tipología valen distinto según su lote. La
 * hoja trae además la vista real de cada una (Mar / Montaña / Sin vista), que
 * hasta ahora era un texto libre igual para todas y por eso no funcionaba el
 * filtro de vista del home.
 *
 * Existe aparte de villas:import porque aquel recrea el catálogo entero
 * (borra y vuelve a crear las villas, con lo que se pierden reservas,
 * imágenes subidas a mano y los flags de publicación). Cuando lo único que
 * llega es una lista de precios nueva, esto toca `price` y `outlook` y nada
 * más.
 *
 * Por defecto sólo simula; hay que pasar --apply para escribir.
 *
 *   php artisan villas:prices --dry-run     # (por defecto) resumen de cambios
 *   php artisan villas:prices --apply
 *   php artisan villas:prices ruta/al.xlsx --apply --full
 */
class UpdateVillaPricesFromExcel extends Command
{
    protected $signature = 'villas:prices
                            {file? : Ruta al .xlsx (por defecto Bahia_Mar_Levantamiento_Datos.xlsx en la raíz)}
                            {--sheet=Precios : Hoja con la lista de precios}
                            {--apply : Escribir de verdad (sin esto sólo simula)}
                            {--keep-view : No tocar la vista, sólo los precios}
                            {--with-areas : Aplicar también las áreas de la hoja si difieren (por defecto sólo se avisan)}
                            {--full : Listar villa por villa en vez del resumen por tipología}';

    protected $description = 'Actualiza el precio (y la vista) de cada villa desde la hoja «Precios» del Excel';

    /** Excel por defecto, en la raíz del proyecto. */
    private const DEFAULT_FILE = 'Bahia_Mar_Levantamiento_Datos.xlsx';

    /** Valor de `type` que identifica a una villa. */
    private const UNIT_TYPE = 'Villa';

    public function handle(): int
    {
        $file = $this->argument('file') ?: base_path(self::DEFAULT_FILE);

        if (! is_readable($file)) {
            $this->error("No se puede leer el Excel: {$file}");

            return self::FAILURE;
        }

        try {
            $rows = VillaPriceList::read($file, (string) $this->option('sheet'));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($rows === []) {
            $this->error("La hoja «{$this->option('sheet')}» no tiene filas de villas.");

            return self::FAILURE;
        }

        $apply    = (bool) $this->option('apply');
        $keepView = (bool) $this->option('keep-view');
        $withArea = (bool) $this->option('with-areas');

        $this->info('Excel: ' . $file . ' · hoja «' . $this->option('sheet') . '» · '
            . count($rows) . ' villas' . ($apply ? '' : ' · SIMULACIÓN (usa --apply para escribir)'));
        $this->newLine();

        $villas = Unit::where('type', self::UNIT_TYPE)->get()->keyBy('custom_id');

        $changes   = [];
        $missing   = [];   // en el Excel y no en la base
        $mismatch  = [];   // áreas o fase que no cuadran
        $unmapped  = [];   // vistas que no sé mapear
        $same      = 0;

        foreach ($rows as $code => $row) {
            $villa = $villas->get($code);
            if (! $villa) {
                $missing[] = $code;
                continue;
            }

            $newPrice = $row['price'];
            $oldPrice = (float) $villa->price;

            $newView = $keepView ? $villa->outlook : VillaPriceList::mapView($row['view'], $unmapped);
            $oldView = $villa->outlook;

            // Las áreas deberían venir iguales que en la hoja «Unidades»; si no,
            // se avisa (y con --with-areas se aplican) en vez de pisarlas calladamente.
            $areaDiff = [];
            foreach ([['interior', 'internal_area'], ['terrace', 'external_area'], ['plot', 'plot_area']] as [$k, $col]) {
                if ($row[$k] > 0 && abs($row[$k] - (float) $villa->$col) > 0.02) {
                    $areaDiff[$col] = [(float) $villa->$col, $row[$k]];
                }
            }
            if ((string) $row['phase'] !== '' && $this->norm($row['phase']) !== $this->norm((string) $villa->custom_2)) {
                $areaDiff['custom_2'] = [(string) $villa->custom_2, $row['phase']];
            }
            if ($areaDiff !== []) {
                $mismatch[$code] = $areaDiff;
            }

            $priceMoved = abs($newPrice - $oldPrice) >= 0.005;
            $viewMoved  = $newView !== $oldView;

            if (! $priceMoved && ! $viewMoved && ! ($withArea && $areaDiff)) {
                $same++;
                continue;
            }

            $changes[] = [
                'villa'  => $villa,
                'code'   => $code,
                'typo'   => $villa->layout,
                'old'    => $oldPrice,
                'new'    => $newPrice,
                'oldV'   => $oldView,
                'newV'   => $newView,
                'price'  => $priceMoved,
                'view'   => $viewMoved,
                'areas'  => $withArea ? $areaDiff : [],
            ];
        }

        $this->render($changes, $missing, $mismatch, $unmapped, $same, $villas->count(), $withArea);

        if ($changes === []) {
            $this->info('No hay nada que cambiar.');

            return self::SUCCESS;
        }

        if (! $apply) {
            $this->newLine();
            $this->comment('Simulación: no se escribió nada. Para aplicar, repetí el comando con --apply.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes) {
            foreach ($changes as $c) {
                $attrs = [];
                if ($c['price']) {
                    $attrs['price'] = $c['new'];
                }
                if ($c['view']) {
                    $attrs['outlook'] = $c['newV'];
                }
                foreach ($c['areas'] as $col => [$old, $new]) {
                    $attrs[$col] = $new;
                }
                if ($attrs !== []) {
                    // forceFill + save: no se toca ninguna otra columna
                    // (public, imágenes, reservas, display_on_home_page…).
                    $c['villa']->forceFill($attrs)->save();
                }
            }
        });

        $this->newLine();
        $this->info('Aplicado: '
            . count(array_filter($changes, fn ($c) => $c['price'])) . ' precios · '
            . count(array_filter($changes, fn ($c) => $c['view'])) . ' vistas'
            . ($withArea ? ' · ' . count(array_filter($changes, fn ($c) => $c['areas'])) . ' áreas' : '')
            . '.');

        return self::SUCCESS;
    }

    private function norm(string $value): string
    {
        return XlsxSheet::norm($value);
    }

    private function render(array $changes, array $missing, array $mismatch, array $unmapped, int $same, int $total, bool $withArea): void
    {
        if ($changes !== []) {
            if ($this->option('full')) {
                $this->table(
                    ['Villa', 'Tip.', 'Precio actual', 'Precio nuevo', 'Δ', 'Vista'],
                    array_map(fn ($c) => [
                        $c['code'],
                        $c['typo'],
                        $c['price'] ? '$' . number_format($c['old']) : '',
                        $c['price'] ? '$' . number_format($c['new']) : '',
                        $c['price'] ? sprintf('%+s', number_format($c['new'] - $c['old'])) : '',
                        $c['view'] ? ($c['oldV'] ?: '—') . ' → ' . ($c['newV'] ?: '—') : '',
                    ], $changes)
                );
            } else {
                // Resumen por tipología: 233 filas no se leen, pero el rango de
                // precios por modelo sí dice si la lista entró bien.
                $byTypo = [];
                foreach ($changes as $c) {
                    $t = $c['typo'] ?: '—';
                    $byTypo[$t]['n']    = ($byTypo[$t]['n'] ?? 0) + 1;
                    $byTypo[$t]['old']  = $c['old'];
                    $byTypo[$t]['min']  = min($byTypo[$t]['min'] ?? INF, $c['new']);
                    $byTypo[$t]['max']  = max($byTypo[$t]['max'] ?? 0, $c['new']);
                    $byTypo[$t]['sum']  = ($byTypo[$t]['sum'] ?? 0) + $c['new'];
                }
                ksort($byTypo);
                $this->table(
                    ['Tipología', 'Villas', 'Precio actual', 'Nuevo: mínimo', 'máximo', 'promedio'],
                    array_map(fn ($t, $d) => [
                        $t,
                        $d['n'],
                        '$' . number_format($d['old']),
                        '$' . number_format($d['min']),
                        '$' . number_format($d['max']),
                        '$' . number_format($d['sum'] / $d['n']),
                    ], array_keys($byTypo), $byTypo)
                );
                $this->line('  (--full para ver villa por villa)');
            }

            $totalOld = array_sum(array_column($changes, 'old'));
            $totalNew = array_sum(array_column($changes, 'new'));
            $this->line('  Cartera: $' . number_format($totalOld) . ' → $' . number_format($totalNew)
                . '  (' . sprintf('%+.1f%%', $totalOld > 0 ? ($totalNew / $totalOld - 1) * 100 : 0) . ')');

            $views = [];
            foreach ($changes as $c) {
                if ($c['view']) {
                    $views[$c['newV'] ?: 'sin vista'] = ($views[$c['newV'] ?: 'sin vista'] ?? 0) + 1;
                }
            }
            if ($views !== []) {
                ksort($views);
                $this->line('  Vistas: ' . collect($views)->map(fn ($n, $v) => "{$v}: {$n}")->join(' · '));
            }
        }

        $this->newLine();
        $this->line("  Villas en la base: {$total} · sin cambios: {$same}");

        if ($missing !== []) {
            $this->newLine();
            $this->warn('  En el Excel pero no en la base (' . count($missing) . '): ' . implode(', ', array_slice($missing, 0, 15))
                . (count($missing) > 15 ? '…' : ''));
            $this->line('    Este comando no crea villas; para eso está villas:import.');
        }

        if ($mismatch !== []) {
            $this->newLine();
            $this->warn('  Áreas o fase distintas a la base (' . count($mismatch) . ')'
                . ($withArea ? ' · se aplican las del Excel:' : ' · NO se tocan (--with-areas para aplicarlas):'));
            foreach (array_slice($mismatch, 0, 10, true) as $code => $diff) {
                $txt = collect($diff)->map(fn ($v, $col) => "{$col}: {$v[0]} → {$v[1]}")->join(' · ');
                $this->line("    {$code}  {$txt}");
            }
            if (count($mismatch) > 10) {
                $this->line('    …y ' . (count($mismatch) - 10) . ' más.');
            }
        }

        if ($unmapped !== []) {
            $this->newLine();
            $this->warn('  Vistas que no sé mapear (se guardan tal cual): '
                . collect($unmapped)->map(fn ($n, $v) => "«{$v}» ×{$n}")->join(' · '));
        }
    }
}
