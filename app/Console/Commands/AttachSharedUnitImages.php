<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Cuelga en todas las unidades del proyecto las imágenes compartidas que viven
 * en el repo (public/images/siebe/): las áreas comunes van a la pestaña
 * «Amenidades» y los renders genéricos de interiores a «Propiedad».
 *
 * Es idempotente: una imagen ya colgada (misma ruta) no se repite, así que se
 * puede volver a correr cada vez que se sumen fotos al MANIFEST. Las imágenes
 * subidas a mano desde el panel no se tocan y conservan la portada (las del
 * repo se agregan siempre al final de su categoría).
 *
 * Algunas fotos aplican sólo a ciertas unidades según lo que indicó el estudio
 * en el nombre del archivo (cocina «todos menos T2, T3, T8 y T9», terraza
 * privada de los PH…); ver la columna `only` del MANIFEST.
 */
class AttachSharedUnitImages extends Command
{
    protected $signature = 'units:shared-images
                            {--project= : Nombre del proyecto (por defecto el único que tenga unidades)}
                            {--dry-run : Sólo mostrar qué haría}
                            {--remove : Quitar de las unidades las imágenes del repo en vez de agregarlas}';

    protected $description = 'Agrega a todas las unidades las imágenes compartidas de public/images/siebe (áreas comunes y renders genéricos)';

    private const BASE = '/images/siebe/';

    /**
     * [ruta relativa a BASE, categoría, nombre visible, regla].
     *
     * Reglas (`only`): null → todas las unidades · '1_bed' → unidades de 1
     * habitación · 'not:T2,T3,T8,T9' → toda tipología cuyo layout no empiece
     * por esas · 'penthouse' → planta 6 / tipo penthouse.
     *
     * Si el archivo no existe en el repo se avisa y se salta, así se puede
     * dejar reservado el hueco de una foto que todavía no llegó.
     */
    private const MANIFEST = [
        // ── Interiores (pestaña Propiedad) ────────────────────────────────
        // Van todos a todas las unidades, pero el orden ROTA por unidad, así
        // que cada tarjeta del grid abre con una imagen distinta en vez de
        // repetir el mismo salón 18 veces. Ver rotate().
        ['propiedades/01-salon.jpg',                 'property',  'Salón',                   null],
        ['propiedades/02-salon-editorial.jpg',       'property',  'Salón · vista al jardín', null],
        ['propiedades/03-salon-luz.jpg',             'property',  'Salón · luz natural',     null],
        ['propiedades/04-salon-cocina.jpg',          'property',  'Salón y cocina',          null],
        ['propiedades/05-salon-cocina-editorial.jpg','property',  'Cocina abierta',          null],
        ['propiedades/06-cocina.jpg',                'property',  'Cocina',                  null],
        ['propiedades/07-cocina-editorial.jpg',      'property',  'Cocina · detalle',        null],
        ['propiedades/08-dormitorio.jpg',            'property',  'Dormitorio',              null],
        ['propiedades/09-dormitorio-editorial.jpg',  'property',  'Dormitorio principal',    null],
        ['propiedades/10-dormitorio-luz.jpg',        'property',  'Dormitorio · luz natural',null],
        ['propiedades/11-bano.jpg',                  'property',  'Baño',                    null],
        ['propiedades/12-bano-editorial.jpg',        'property',  'Baño · detalle',          null],

        // ── Planos del estudio (pestaña Planos), uno por planta ───────────
        ['planos/planta-1.jpg',   'plans', 'Planta 1 · plano general',        'floor:1st'],
        ['planos/planta-2-3.jpg', 'plans', 'Plantas 2 y 3 · plano general',   'floor:2nd,3rd'],
        ['planos/planta-4.jpg',   'plans', 'Planta 4 · plano general',        'floor:4th'],
        ['planos/azotea.jpg',     'plans', 'Azotea y emplazamiento',          'floor:4th'],

        // ── Edificio y entorno (pestaña Amenidades) ────────────────────────
        ['comunes/01-fachada-atardecer.jpg',    'amenities', 'Fachada al atardecer', null],
        ['comunes/02-fachada-hora-azul.jpg',    'amenities', 'Fachada · hora azul',  null],
        ['comunes/03-fachada-diurna.jpg',       'amenities', 'Fachada de día',       null],
        ['comunes/04-entorno.jpg',              'amenities', 'Entorno',              null],
    ];

    public function handle(): int
    {
        $project = $this->resolveProject();
        if (! $project) {
            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $this->info("Proyecto: {$project->name} · {$project->units()->count()} unidades" . ($dry ? ' · SIMULACIÓN' : ''));

        if ($this->option('remove')) {
            return $this->remove($project, $dry);
        }

        // Sólo los archivos que de verdad están en el repo.
        $rows = [];
        foreach (self::MANIFEST as [$file, $category, $name, $only]) {
            if (! is_file(public_path(self::BASE . $file))) {
                $this->warn("  · falta {$file}: se salta");
                continue;
            }
            $rows[] = ['path' => self::BASE . $file, 'category' => $category, 'name' => $name, 'only' => $only, 'units' => 0];
        }
        if ($rows === []) {
            $this->error('No hay ninguna imagen en public' . self::BASE);
            return self::FAILURE;
        }

        $created = 0;
        $touched = 0;

        DB::transaction(function () use ($project, $dry, &$rows, &$created, &$touched) {
            $units = $project->units()->orderBy('name')->get();

            foreach ($units as $unit) {
                $have = UnitImage::where('unit_id', $unit->id)->pluck('path')->flip();
                $next = [];   // siguiente sort_order por categoría
                $new  = 0;

                foreach ($this->rotate($rows, $unit) as $i => $row) {
                    if (! $this->applies($row['only'], $unit) || isset($have[$row['path']])) {
                        continue;
                    }
                    $rows[$i]['units']++;
                    $new++;
                    if ($dry) {
                        continue;
                    }
                    $next[$row['category']] ??= $this->nextSortOrder($unit, $row['category']);

                    UnitImage::create([
                        'unit_id'    => $unit->id,
                        'category'   => $row['category'],
                        'name'       => $row['name'],
                        'path'       => $row['path'],
                        'sort_order' => $next[$row['category']]++,
                    ]);
                }

                if ($new > 0) {
                    $touched++;
                    $created += $new;
                    if (! $dry) {
                        $unit->forceFill(['images_count' => UnitImage::where('unit_id', $unit->id)->count()])->save();
                    }
                }
            }
        });

        $this->table(
            ['Imagen', 'Pestaña', 'Aplica a', 'Unidades'],
            array_map(fn ($r) => [basename($r['path']), $r['category'], $this->describe($r['only']), $r['units']], $rows)
        );
        $this->info(($dry ? 'Se agregarían' : 'Agregadas') . " {$created} imágenes en {$touched} unidades.");

        return self::SUCCESS;
    }

    /** Quita las imágenes del repo (sólo las de BASE; las subidas a mano quedan). */
    private function remove(Project $project, bool $dry): int
    {
        $q = UnitImage::whereIn('unit_id', $project->units()->select('id'))
            ->where('path', 'like', self::BASE . '%');
        $n = $q->count();

        if (! $dry) {
            DB::transaction(function () use ($q, $project) {
                $q->delete();
                foreach ($project->units()->get() as $unit) {
                    $unit->forceFill(['images_count' => UnitImage::where('unit_id', $unit->id)->count()])->save();
                }
            });
        }
        $this->info(($dry ? 'Se quitarían' : 'Quitadas') . " {$n} imágenes del repo.");

        return self::SUCCESS;
    }

    /**
     * La portada de la tarjeta es `images->first()` ordenado sólo por
     * sort_order, sin mirar la categoría. Las de propiedad siguen a las que ya
     * tenga la unidad en esa pestaña; las amenidades arrancan por encima de
     * todo lo demás para que nunca le ganen la portada a un render.
     */
    private function nextSortOrder(Unit $unit, string $category): int
    {
        $q = UnitImage::where('unit_id', $unit->id);
        if ($category === 'property') {
            $q->where('category', 'property');
        }

        return (int) $q->max('sort_order') + 1;
    }

    /**
     * Rota el bloque de interiores para que la portada dependa de la unidad.
     * Todas comparten el mismo juego de renders (el acabado es igual en las
     * 18), así que sin esto el grid entero abría con el mismo salón. El
     * desplazamiento sale del número de unidad, de forma determinista: volver
     * a correr el comando no baraja nada.
     *
     * Sólo se mueven las de categoría 'property'; planos y exteriores
     * conservan su orden.
     */
    private function rotate(array $rows, Unit $unit): array
    {
        $idx = [];
        foreach ($rows as $i => $r) {
            if ($r['category'] === 'property') {
                $idx[] = $i;
            }
        }
        $n = count($idx);
        if ($n < 2 || ! preg_match('/(\d+)\s*$/', (string) $unit->name, $m)) {
            return $rows;
        }
        // 101, 205, 403… el último dígito da suficiente dispersión por planta.
        $shift = ((int) $m[1]) % $n;
        if ($shift === 0) {
            return $rows;
        }

        $pool = array_map(fn ($i) => $rows[$i], $idx);
        $pool = array_merge(array_slice($pool, $shift), array_slice($pool, 0, $shift));
        foreach ($idx as $k => $i) {
            $rows[$i] = $pool[$k];
        }

        return $rows;
    }

    private function applies(?string $only, Unit $unit): bool
    {
        if ($only === null) {
            return true;
        }
        if ($only === '1_bed') {
            return (int) $unit->bedrooms === 1;
        }
        if ($only === 'penthouse') {
            return str_starts_with((string) $unit->type, 'penthouse') || $unit->floor === '6th';
        }
        // 'floor:2nd,3rd' → sólo las unidades de esas plantas.
        if (str_starts_with($only, 'floor:')) {
            return in_array((string) $unit->floor, explode(',', substr($only, 6)), true);
        }
        if (str_starts_with($only, 'not:')) {
            $layout = strtoupper(trim((string) $unit->layout));
            foreach (explode(',', substr($only, 4)) as $typo) {
                // T2 excluye T2, T2A, T2-piso 2… pero no T20.
                if (preg_match('/^' . preg_quote(strtoupper($typo), '/') . '(?![0-9])/', $layout)) {
                    return false;
                }
            }
            return true;
        }

        return true;
    }

    private function describe(?string $only): string
    {
        return match (true) {
            $only === null                 => 'todas',
            $only === '1_bed'              => '1 habitación',
            $only === 'penthouse'          => 'penthouses',
            str_starts_with($only, 'floor:') => 'planta ' . str_replace(',', ', ', substr($only, 6)),
            str_starts_with($only, 'not:') => 'todas menos ' . str_replace(',', ', ', substr($only, 4)),
            default                        => $only,
        };
    }

    private function resolveProject(): ?Project
    {
        if ($name = $this->option('project')) {
            $p = Project::where('name', $name)->first();
            if (! $p) {
                $this->error("Proyecto «{$name}» no existe.");
            }
            return $p;
        }

        $candidates = Project::has('units')->get();
        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        $this->error($candidates->isEmpty()
            ? 'Ningún proyecto tiene unidades.'
            : 'Hay varios proyectos con unidades; indica cuál con --project=: ' . $candidates->pluck('name')->join(', '));

        return null;
    }
}
