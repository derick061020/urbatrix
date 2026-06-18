<?php

namespace App\Console\Commands;

use App\Models\Unit;
use App\Models\UnitView;
use Illuminate\Console\Command;

/**
 * Rota el estado "HIGH DEMAND" (is_high_demand) de las unidades disponibles.
 *
 * Estrategia híbrida:
 *   1) Señal REAL: las unidades con suficientes vistas reales (UnitView) dentro de
 *      una ventana reciente se marcan como hot, ordenadas por cantidad de vistas.
 *   2) FAKE de relleno: si las vistas reales no alcanzan para llenar el cupo, se
 *      completa con unidades disponibles elegidas al azar, para que la home siempre
 *      muestre algunas en "alta demanda".
 *
 * Se ejecuta de forma periódica (ver routes/console.php). Maneja is_high_demand
 * directamente sobre las unidades disponibles, así que en cada corrida se reinicia
 * el flag de las que dejaron de calificar (incluye las marcadas a mano en el admin).
 */
class RefreshUnitDemand extends Command
{
    protected $signature = 'units:refresh-demand
        {--min=2 : Mínimo de unidades en hot por corrida}
        {--max=4 : Máximo de unidades en hot por corrida}
        {--window=48 : Ventana en horas para contar vistas reales}
        {--threshold=5 : Vistas reales en la ventana para calificar como demanda real}';

    protected $description = 'Rota el estado HIGH DEMAND de las unidades disponibles (híbrido: vistas reales + relleno fake).';

    public function handle(): int
    {
        $min       = max(0, (int) $this->option('min'));
        $max       = max($min, (int) $this->option('max'));
        $window    = max(1, (int) $this->option('window'));
        $threshold = max(1, (int) $this->option('threshold'));

        $target = $min === $max ? $min : random_int($min, $max);

        // Unidades elegibles: públicas, disponibles y sin reserva activa.
        $eligible = Unit::query()
            ->where('public', true)
            ->whereRaw('UPPER(status) = ?', ['AVAILABLE'])
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                  ->orWhere('reserved_until', '<', now());
            })
            ->get();

        if ($eligible->isEmpty()) {
            $this->info('No hay unidades disponibles para marcar en alta demanda.');
            return self::SUCCESS;
        }

        // 1) Señal real: vistas en la ventana reciente.
        $since = now()->subHours($window);
        $viewCounts = UnitView::query()
            ->whereIn('unit_id', $eligible->pluck('id'))
            ->where('viewed_at', '>=', $since)
            ->selectRaw('unit_id, COUNT(*) as c')
            ->groupBy('unit_id')
            ->pluck('c', 'unit_id');

        $realHot = $eligible
            ->filter(fn ($u) => ($viewCounts[$u->id] ?? 0) >= $threshold)
            ->sortByDesc(fn ($u) => $viewCounts[$u->id] ?? 0)
            ->take($target)
            ->values();

        $selected = $realHot->pluck('id')->all();

        // 2) Relleno fake: completar el cupo con unidades al azar.
        if (count($selected) < $target) {
            $fill = $eligible
                ->whereNotIn('id', $selected)
                ->shuffle()
                ->take($target - count($selected));

            $selected = array_merge($selected, $fill->pluck('id')->all());
        }

        // Aplicar: limpiar el flag en todas las elegibles y prenderlo en las seleccionadas.
        Unit::whereIn('id', $eligible->pluck('id'))->update(['is_high_demand' => false]);

        if (! empty($selected)) {
            Unit::whereIn('id', $selected)->update(['is_high_demand' => true]);
        }

        $this->info(sprintf(
            'HIGH DEMAND actualizado: %d unidad(es) en hot (%d reales por vistas, %d fake) sobre %d disponibles.',
            count($selected),
            $realHot->count(),
            count($selected) - $realHot->count(),
            $eligible->count()
        ));

        return self::SUCCESS;
    }
}
