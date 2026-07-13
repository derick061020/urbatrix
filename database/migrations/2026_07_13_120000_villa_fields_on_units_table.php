<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapta la tabla `units` (diseñada para departamentos) al producto de villas
 * de Bahía Mar / Landmass.
 *
 *  · AGREGA campos propios de villa:
 *      - plot_area  → m² de parcela/lote (cada villa se vende con su terreno)
 *      - roof_area  → m² de roof / solárium
 *      - stories    → nº de plantas de la villa (2 niveles, 1 nivel…)
 *      - phase      → etapa/fase comercial (Fase 1–4)
 *
 *  · ELIMINA campos de departamento sin uso para villas (huella nula/mínima,
 *    todas las lecturas restantes degradan a null con fallback):
 *      levies, rates, expense_1..3, walk_score, school_proximity
 *
 *  `floor` y `layout` se CONSERVAN: son estructurales (contratos, PDF, filtros,
 *  planos por piso, importador) y otros proyectos de departamentos los usan.
 *  Sólo se ocultan del editor de villas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (! Schema::hasColumn('units', 'plot_area')) {
                $table->decimal('plot_area', 10, 2)->nullable()->after('total_area');
            }
            if (! Schema::hasColumn('units', 'roof_area')) {
                $table->decimal('roof_area', 10, 2)->nullable()->after('plot_area');
            }
            if (! Schema::hasColumn('units', 'stories')) {
                $table->unsignedTinyInteger('stories')->nullable()->after('roof_area');
            }
            if (! Schema::hasColumn('units', 'phase')) {
                $table->string('phase', 50)->nullable()->after('stories');
            }
        });

        $drop = array_values(array_filter(
            ['levies', 'rates', 'expense_1', 'expense_2', 'expense_3', 'walk_score', 'school_proximity'],
            fn ($col) => Schema::hasColumn('units', $col)
        ));

        if (! empty($drop)) {
            Schema::table('units', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->decimal('levies', 10, 2)->nullable();
            $table->decimal('rates', 10, 2)->nullable();
            $table->decimal('expense_1', 10, 2)->nullable();
            $table->decimal('expense_2', 10, 2)->nullable();
            $table->decimal('expense_3', 10, 2)->nullable();
            $table->integer('walk_score')->nullable();
            $table->string('school_proximity')->nullable();
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['plot_area', 'roof_area', 'stories', 'phase']);
        });
    }
};
