<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Console\Command;

/**
 * Calcula dónde va la chincheta de cada unidad sobre el plano de su piso.
 *
 * La vista de planta pinta un marcador por unidad usando una lista fija de
 * puntos: doce coordenadas pensadas para el masterplan general. Pero el admin
 * sube un plano distinto por piso, con otra forma y otra proporción, así que
 * esos puntos caían donde no corresponde —uno quedaba sobre la piscina— y,
 * con 36 unidades por piso, se repetían de a tres en el mismo sitio.
 *
 * Esto mira cada plano subido y elige, sobre el dibujo mismo, tantos puntos
 * separados como unidades tenga el piso. No sabe qué apartamento es cuál: sólo
 * garantiza que cada chincheta cae sobre planta dibujada y no sobre el jardín,
 * la piscina o el blanco de alrededor. Para una correspondencia exacta haría
 * falta cargar la coordenada de cada unidad a mano.
 *
 * Hay que volver a correrlo cuando se cambie un plano o cambie el número de
 * unidades de un piso:
 *
 *     php artisan plan:anchors
 *     php artisan plan:anchors --floor=2nd --dry-run
 */
class BuildPlanAnchors extends Command
{
    protected $signature = 'plan:anchors
                            {--floor= : Sólo este piso (ground, 1st, 2nd, …)}
                            {--dry-run : Calcula y muestra, sin guardar}';

    protected $description = 'Recalcula las posiciones de los marcadores sobre los planos de planta';

    /** Clave del ajuste donde viven los planos subidos por el admin. */
    private const IMAGES_SETTING = 'floor_plan_images';

    /** Clave del ajuste donde se guardan los puntos calculados. */
    private const ANCHORS_SETTING = 'floor_plan_anchors';

    /** Ancho de la miniatura sobre la que se analiza el plano. */
    private const SAMPLE_WIDTH = 400;

    public function handle(): int
    {
        if (! function_exists('imagecreatefromstring')) {
            $this->error('Falta la extensión GD de PHP.');

            return self::FAILURE;
        }

        $images = Setting::get(self::IMAGES_SETTING, []) ?: [];
        if (! is_array($images) || $images === []) {
            $this->warn('No hay planos subidos ('.self::IMAGES_SETTING.' está vacío). Nada que calcular.');

            return self::SUCCESS;
        }

        $only = $this->option('floor');
        $counts = $this->unitsPerFloor();
        $anchors = Setting::get(self::ANCHORS_SETTING, []) ?: [];

        foreach ($images as $floorKey => $path) {
            if ($only && $only !== $floorKey) {
                continue;
            }

            $want = $counts[$floorKey] ?? 0;
            if ($want < 1) {
                $this->line("  {$floorKey}: sin unidades, se omite");
                continue;
            }

            $file = $this->resolve($path);
            if (! $file) {
                $this->warn("  {$floorKey}: no se encuentra la imagen ({$path})");
                continue;
            }

            $points = $this->pickPoints($file, $want);
            if ($points === []) {
                $this->warn("  {$floorKey}: no se pudo leer el plano");
                continue;
            }

            $anchors[$floorKey] = $points;
            $this->info(sprintf('  %-7s %d unidades → %d puntos', $floorKey, $want, count($points)));
        }

        if ($this->option('dry-run')) {
            $this->comment('--dry-run: no se guardó nada.');

            return self::SUCCESS;
        }

        Setting::put(self::ANCHORS_SETTING, $anchors);
        $this->info('Guardado en el ajuste '.self::ANCHORS_SETTING.'.');

        return self::SUCCESS;
    }

    /**
     * Cuántas unidades públicas hay por piso, con la misma clave que usan los
     * planos ('ground' para las que no tienen piso cargado).
     *
     * @return array<string,int>
     */
    private function unitsPerFloor(): array
    {
        $counts = [];

        foreach (Unit::where('public', true)->pluck('floor') as $floor) {
            $f = trim((string) $floor);
            $key = ($f === '' || strcasecmp($f, 'ground') === 0 || strcasecmp($f, 'pb') === 0) ? 'ground' : $f;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $counts;
    }

    /** Ruta en disco de un plano guardado como '/storage/…'. */
    private function resolve(string $path): ?string
    {
        $rel = ltrim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
        $file = public_path($rel);

        return is_file($file) ? $file : null;
    }

    /**
     * Elige `$want` puntos separados entre sí, sobre zonas con planta dibujada.
     *
     * El plano es dibujo oscuro sobre blanco: donde hay apartamentos la tinta
     * es densa (paredes, muebles, cotas) y alrededor es blanco. Se descarta el
     * verde —la cubierta de vidrio sobre la piscina— porque es la zona en la
     * que no debería caer ningún marcador.
     *
     * @return list<array{x: float, y: float, side: string}>
     */
    private function pickPoints(string $file, int $want): array
    {
        $raw = @file_get_contents($file);
        $img = $raw === false ? false : @imagecreatefromstring($raw);
        if (! $img) {
            return [];
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $tw = self::SAMPLE_WIDTH;
        $th = max(1, (int) round($h * $tw / $w));

        $small = imagescale($img, $tw, $th, IMG_BILINEAR_FIXED);
        imagedestroy($img);
        if (! $small) {
            return [];
        }

        // 1 donde hay dibujo de planta, 0 en el blanco y en el verde.
        $ink = [];
        for ($y = 0; $y < $th; $y++) {
            $row = [];
            for ($x = 0; $x < $tw; $x++) {
                $rgb = imagecolorat($small, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $lum = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                $green = $g > $r + 6 && $g > $b + 6;
                $row[$x] = ($lum < 232 && ! $green) ? 1 : 0;
            }
            $ink[] = $row;
        }
        imagedestroy($small);

        // Densidad local en una ventana de 9x9, vía suma acumulada 2D.
        $sum = [];
        for ($y = 0; $y <= $th; $y++) {
            $sum[$y] = array_fill(0, $tw + 1, 0);
        }
        for ($y = 0; $y < $th; $y++) {
            for ($x = 0; $x < $tw; $x++) {
                $sum[$y + 1][$x + 1] = $ink[$y][$x] + $sum[$y][$x + 1] + $sum[$y + 1][$x] - $sum[$y][$x];
            }
        }

        $r = 4;
        $cands = [];
        for ($y = 0; $y < $th; $y++) {
            $y0 = max(0, $y - $r);
            $y1 = min($th - 1, $y + $r);
            for ($x = 0; $x < $tw; $x++) {
                $x0 = max(0, $x - $r);
                $x1 = min($tw - 1, $x + $r);
                $area = ($y1 - $y0 + 1) * ($x1 - $x0 + 1);
                $tot = $sum[$y1 + 1][$x1 + 1] - $sum[$y0][$x1 + 1] - $sum[$y1 + 1][$x0] + $sum[$y0][$x0];
                $d = $tot / $area;
                if ($d >= 0.35) {
                    $cands[] = [$d, $x, $y];
                }
            }
        }

        if ($cands === []) {
            return [];
        }

        // Más denso primero: así los puntos caen en el centro de la planta y no
        // en el borde, donde la chincheta se saldría del dibujo.
        usort($cands, fn ($a, $b) => $b[0] <=> $a[0]);

        // Se empieza con una separación holgada y se afloja hasta que entren
        // todas las unidades del piso.
        $chosen = [];
        for ($gap = 14.0; $gap >= 3.0; $gap -= 1.0) {
            $chosen = [];
            foreach ($cands as [$d, $x, $y]) {
                $ok = true;
                foreach ($chosen as [$cx, $cy]) {
                    if (($x - $cx) ** 2 + ($y - $cy) ** 2 < $gap * $gap) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    $chosen[] = [$x, $y];
                    if (count($chosen) >= $want) {
                        break 2;
                    }
                }
            }
        }

        // De izquierda a derecha: el recorrido visual acompaña la numeración.
        usort($chosen, fn ($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        $points = [];
        foreach ($chosen as [$x, $y]) {
            $px = round($x / $tw * 100, 2);
            $py = round($y / $th * 100, 2);
            $points[] = [
                'x' => $px,
                'y' => $py,
                // La cola del globo apunta hacia dentro del plano.
                'side' => $px > 50 ? 'left' : 'right',
            ];
        }

        return $points;
    }
}
