<?php

namespace App\Support;

use App\Models\Unit;

/**
 * Disponibilidad por modelo de villa (tipología T1, T2, T3…).
 *
 * La tarjeta del home muestra "58 de 77 disponibles" con una barra de
 * segmentos: no es el estado de esa villa concreta sino cuántas quedan libres
 * de ese modelo. Como la tarjeta se pinta desde dos lados (el render inicial de
 * home.blade.php y el scroll infinito de {@see \App\Http\Controllers\HomeController::homeUnits()}),
 * el conteo se resuelve acá una sola vez por request y se cachea en memoria.
 */
class VillaSupply
{
    /** Estados que NO cuentan como disponible. */
    private const TAKEN = ['sold', 'reserved', 'pending'];

    /** @var array<string, array{total:int, available:int}>|null */
    private static ?array $counts = null;

    /**
     * Totales por tipología: ['T1' => ['total' => 77, 'available' => 74], …].
     *
     * @return array<string, array{total:int, available:int}>
     */
    public static function counts(): array
    {
        if (self::$counts !== null) {
            return self::$counts;
        }

        $taken = "'" . implode("','", self::TAKEN) . "'";

        return self::$counts = Unit::query()
            ->where('type', 'Villa')
            ->where('public', true)
            ->whereNotNull('layout')
            ->where('layout', '!=', '')
            ->groupBy('layout')
            ->select('layout')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN LOWER(status) IN ({$taken}) THEN 0 ELSE 1 END) as available")
            ->get()
            ->mapWithKeys(fn ($row) => [$row->layout => [
                'total'     => (int) $row->total,
                'available' => (int) $row->available,
            ]])
            ->all();
    }

    /**
     * Disponibilidad del modelo de una unidad, o null si no aplica (no es una
     * villa, no tiene tipología o es la única de su modelo).
     *
     * @return array{total:int, available:int, filled:int, segments:int}|null
     */
    public static function forUnit(Unit $unit, int $segments = 10): ?array
    {
        if (strcasecmp((string) ($unit->type ?? ''), 'Villa') !== 0) {
            return null;
        }

        $row = self::counts()[$unit->layout ?? ''] ?? null;

        if (! $row || $row['total'] < 1) {
            return null;
        }

        return $row + [
            'segments' => $segments,
            'filled'   => (int) max(0, min($segments, round($row['available'] / $row['total'] * $segments))),
        ];
    }

    /** Limpia la caché (tests / comandos que modifican unidades en el mismo proceso). */
    public static function flush(): void
    {
        self::$counts = null;
    }
}
