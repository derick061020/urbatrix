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
        {--threshold=5 : Vistas reales en la ventana para calificar como demanda real}
        {--views-min=8 : Piso de "vistas hoy" fake para una unidad hot}
        {--views-max=32 : Techo de "vistas hoy" fake para una unidad hot}';

    protected $description = 'Rota el estado HIGH DEMAND de las unidades disponibles (híbrido: vistas reales + relleno fake).';

    public function handle(): int
    {
        $min       = max(0, (int) $this->option('min'));
        $max       = max($min, (int) $this->option('max'));
        $window    = max(1, (int) $this->option('window'));
        $threshold = max(1, (int) $this->option('threshold'));
        $viewsMin  = max(1, (int) $this->option('views-min'));
        $viewsMax  = max($viewsMin, (int) $this->option('views-max'));

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

        // Conteo REAL de vistas de hoy por unidad (la card muestra views_today; el admin
        // calcula sus métricas desde la tabla UnitView, así que esto no afecta la analítica).
        $realToday = UnitView::query()
            ->whereIn('unit_id', $eligible->pluck('id'))
            ->where('viewed_at', '>=', today())
            ->selectRaw('unit_id, COUNT(*) as c')
            ->groupBy('unit_id')
            ->pluck('c', 'unit_id');

        $selectedIds = array_flip($selected);

        // Aplicar por unidad: las hot llevan el badge y un "vistas hoy" creíble
        // (el real si ya supera el piso fake); las que se enfrían quedan con su
        // conteo real de hoy, sin números fake colgados.
        foreach ($eligible as $u) {
            $real = (int) ($realToday[$u->id] ?? 0);
            $isHot = isset($selectedIds[$u->id]);

            $views = $isHot
                ? max($real, random_int($viewsMin, $viewsMax))
                : $real;

            $u->forceFill([
                'is_high_demand' => $isHot,
                'views_today'    => $views,
            ])->save();
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
