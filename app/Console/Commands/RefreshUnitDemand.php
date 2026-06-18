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
 *      completa con unidades disponibles que NO están hot ahora (rotación), para que
 *      la home siempre muestre algunas en "alta demanda".
 *   3) DEGRADACIÓN: cualquier unidad ya marcada que no quede seleccionada pierde el
 *      flag —incluidas las que dejaron de ser elegibles (reservadas/vendidas/no
 *      públicas) o las hot con pocas vistas que no se re-eligen— y recupera su
 *      conteo real de "vistas hoy".
 *
 * Se ejecuta de forma periódica (ver routes/console.php). Maneja is_high_demand y
 * views_today directamente, así que en cada corrida se reevalúa todo el set hot
 * (incluye lo marcado a mano en el admin).
 */
class RefreshUnitDemand extends Command
{
    protected $signature = 'units:refresh-demand
        {--min-pct=40 : % mínimo de las disponibles que quedan en hot}
        {--max-pct=50 : % máximo de las disponibles que quedan en hot}
        {--window=48 : Ventana en horas para contar vistas reales}
        {--threshold=5 : Vistas reales en la ventana para calificar como demanda real}
        {--views-min=8 : Piso de "vistas hoy" fake para una unidad hot}
        {--views-max=32 : Techo de "vistas hoy" fake para una unidad hot}';

    protected $description = 'Rota el estado HIGH DEMAND de las unidades disponibles (híbrido: vistas reales + relleno fake).';

    public function handle(): int
    {
        $minPct    = min(100, max(0, (int) $this->option('min-pct')));
        $maxPct    = min(100, max($minPct, (int) $this->option('max-pct')));
        $window    = max(1, (int) $this->option('window'));
        $threshold = max(1, (int) $this->option('threshold'));
        $viewsMin  = max(1, (int) $this->option('views-min'));
        $viewsMax  = max($viewsMin, (int) $this->option('views-max'));

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

        // Cupo: un % al azar (40–50% por defecto) de las disponibles, mínimo 1.
        $pct = $minPct === $maxPct ? $minPct : random_int($minPct, $maxPct);
        $target = max(1, (int) round($eligible->count() * $pct / 100));

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

        // 2) Relleno fake: completar el cupo. Se prefieren unidades que NO están hot
        //    ahora mismo, para forzar rotación y degradar las que ya estaban marcadas
        //    con pocas vistas. Si no alcanza, se completa con cualquier elegible.
        if (count($selected) < $target) {
            $remaining = $target - count($selected);

            $pool = $eligible->where('is_high_demand', false)->whereNotIn('id', $selected);
            if ($pool->count() < $remaining) {
                $pool = $eligible->whereNotIn('id', $selected);
            }

            $fill = $pool->shuffle()->take($remaining);
            $selected = array_merge($selected, $fill->pluck('id')->all());
        }

        $selectedIds = array_flip($selected);

        // Unidades hoy marcadas como hot (para degradar las que ya no califican, incluso
        // si dejaron de ser elegibles: pasaron a reservadas/vendidas/no públicas).
        $currentlyHot = Unit::where('is_high_demand', true)->get();

        // Conteo REAL de vistas de hoy por unidad (la card muestra views_today; el admin
        // calcula sus métricas desde la tabla UnitView, así que esto no afecta la analítica).
        $countIds = $eligible->pluck('id')->merge($currentlyHot->pluck('id'))->unique();
        $realToday = UnitView::query()
            ->whereIn('unit_id', $countIds)
            ->where('viewed_at', '>=', today())
            ->selectRaw('unit_id, COUNT(*) as c')
            ->groupBy('unit_id')
            ->pluck('c', 'unit_id');

        $eligibleIds = $eligible->pluck('id')->flip();

        // a) Degradar unidades hot que ya NO son elegibles (reservadas/vendidas/no
        //    públicas/stale): quitarles el flag y devolverles su conteo real de hoy.
        $stale = 0;
        foreach ($currentlyHot as $u) {
            if ($eligibleIds->has($u->id)) {
                continue; // las elegibles se resuelven abajo
            }
            $u->forceFill([
                'is_high_demand' => false,
                'views_today'    => (int) ($realToday[$u->id] ?? 0),
            ])->save();
            $stale++;
        }

        // b) Resolver las elegibles: las hot llevan el badge + un "vistas hoy" creíble
        //    (el real si ya supera el piso fake); las frías quedan con su conteo real,
        //    sin números fake colgados.
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
            'HIGH DEMAND actualizado: %d en hot (%d reales / %d fake), %d degradadas por no calificar, sobre %d disponibles.',
            count($selected),
            $realHot->count(),
            count($selected) - $realHot->count(),
            $stale,
            $eligible->count()
        ));

        return self::SUCCESS;
    }
}
