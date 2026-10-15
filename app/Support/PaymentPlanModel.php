<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cómo se paga una unidad en este proyecto, deducido de lo que ya se firmó.
 *
 * El simulador de la ficha necesita saber en qué se reparte el precio: cuánto
 * va a la reserva, cuánto a la separación al firmar, cuánto en cuotas durante
 * la obra y cuánto contra entrega. Esos porcentajes no están configurados en
 * ningún sitio, pero sí están vividos: la tabla `payments` guarda el plan real
 * de cada reserva, con su tipo y su importe.
 *
 * Así que en vez de escribir números a mano se miran los planes ya hechos y se
 * toma la MEDIANA de cada tramo. La mediana y no el promedio porque hay planes
 * atípicos —uno a 48 cuotas, otro pagado casi al contado— que arrastrarían la
 * media hacia un plan que nadie ha firmado.
 *
 * Si no hay planes suficientes no se inventa nada: devuelve null y la ficha no
 * enseña el simulador. Hoy eso es lo que pasa en Bahía Mar y en Siebe, que
 * todavía no tienen ninguna reserva con plan.
 */
class PaymentPlanModel
{
    /** Por debajo de esto no hay de dónde deducir: mejor no enseñar nada. */
    private const MINIMO_PLANES = 5;

    /** Los tramos, en el orden en que los paga el cliente. */
    private const TRAMOS = ['reservation', 'initial', 'installment', 'delivery'];

    /**
     * Reparto típico del precio, o null si el proyecto aún no tiene historia.
     *
     * @return array{
     *     reserva: float, inicial: float, obra: float, entrega: float,
     *     reserva_monto: float, cuotas_min: int, cuotas_max: int,
     *     cuotas_tipicas: int, planes: int
     * }|null
     */
    public static function rules(): ?array
    {
        return Cache::remember('payment_plan_model', now()->addHours(12), function () {
            return self::deducir();
        });
    }

    /** Vacía la caché: útil tras cargar o corregir planes. */
    public static function forget(): void
    {
        Cache::forget('payment_plan_model');
    }

    private static function deducir(): ?array
    {
        if (! Schema::hasTable('payments')) {
            return null;
        }

        $porReserva = DB::table('payments')
            ->selectRaw('reservation_id, payment_type, SUM(amount) AS total, COUNT(*) AS n')
            ->whereIn('payment_type', self::TRAMOS)
            ->groupBy('reservation_id', 'payment_type')
            ->get()
            ->groupBy('reservation_id');

        $planes = [];
        foreach ($porReserva as $filas) {
            $tramos = $filas->keyBy('payment_type');
            $total  = (float) $filas->sum('total');

            // Un plan sirve si tiene importe real y cuotas de obra: sin cuotas
            // no hay nada que simular, es una compra al contado.
            if ($total < 1000 || ! isset($tramos['installment'])) {
                continue;
            }

            $planes[] = [
                'reserva'       => (float) ($tramos['reservation']->total ?? 0) / $total,
                'inicial'       => (float) ($tramos['initial']->total ?? 0) / $total,
                'obra'          => (float) ($tramos['installment']->total ?? 0) / $total,
                'entrega'       => (float) ($tramos['delivery']->total ?? 0) / $total,
                'reserva_monto' => (float) ($tramos['reservation']->total ?? 0),
                'cuotas'        => (int) $tramos['installment']->n,
            ];
        }

        if (count($planes) < self::MINIMO_PLANES) {
            return null;
        }

        $mediana = function (string $campo) use ($planes) {
            $v = array_column($planes, $campo);
            sort($v);
            $n = count($v);

            return $n % 2 ? $v[intdiv($n, 2)] : ($v[$n / 2 - 1] + $v[$n / 2]) / 2;
        };

        $cuotas = array_column($planes, 'cuotas');
        sort($cuotas);

        // Los extremos se recortan al percentil 10/90: el plan de una cuota y el
        // de cuarenta y ocho existen, pero no son la oferta que se enseña.
        $p = fn (float $q) => $cuotas[max(0, min(count($cuotas) - 1, (int) round($q * (count($cuotas) - 1))))];

        $entrega = round($mediana('entrega'), 4);

        // Lo que el cliente pone antes de empezar la obra: la reserva más la
        // separación al firmar. Es el tramo que el simulador deja mover.
        $entrada = (int) round(($mediana('reserva') + $mediana('inicial')) * 100);

        // El tope deja siempre algo en obra: si la entrada se comiera todo lo
        // que no es contra-entrega, no habría cuotas que simular.
        $topeReal = (int) floor((1 - $entrega) * 100) - 5;

        $cuotasMin = max(1, (int) $p(0.10));
        $cuotasMax = max($cuotasMin + 1, (int) $p(0.90));

        return [
            'reserva'        => round($mediana('reserva'), 4),
            'inicial'        => round($mediana('inicial'), 4),
            'obra'           => round($mediana('obra'), 4),
            'entrega'        => $entrega,
            'reserva_monto'  => round($mediana('reserva_monto'), 2),
            'entrada_tipica' => $entrada,
            'entrada_min'    => max(5, $entrada - 10),
            'entrada_max'    => max($entrada + 5, min(60, $topeReal)),
            'cuotas_min'     => $cuotasMin,
            'cuotas_max'     => $cuotasMax,
            'cuotas_tipicas' => min($cuotasMax, max($cuotasMin, (int) $mediana('cuotas'))),
            'planes'         => count($planes),
        ];
    }
}
