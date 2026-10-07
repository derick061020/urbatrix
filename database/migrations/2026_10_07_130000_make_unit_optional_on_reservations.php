<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permite registrar un cliente sin unidad asignada todavía.
 *
 * El equipo comercial carga clientes que aún están decidiendo, y hasta ahora
 * el alta obligaba a elegir una unidad: se acababa asignando cualquiera «para
 * que deje guardar», lo que ensucia la disponibilidad y el reporte.
 *
 * Se tocan las tres columnas con DB::statement y no con el Blueprint porque
 * `unit_id` es varchar(255) heredado (no una FK), y change() requeriría
 * doctrine/dbal además de reescribir el tipo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        DB::statement('ALTER TABLE reservations MODIFY unit_id VARCHAR(255) NULL');
        DB::statement('ALTER TABLE reservations MODIFY unit_name VARCHAR(255) NULL');
        DB::statement('ALTER TABLE reservations MODIFY unit_price DECIMAL(12,2) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        // Las filas sin unidad romperían el NOT NULL: se rellenan antes.
        DB::table('reservations')->whereNull('unit_id')->update(['unit_id' => '']);
        DB::table('reservations')->whereNull('unit_name')->update(['unit_name' => '—']);
        DB::table('reservations')->whereNull('unit_price')->update(['unit_price' => 0]);

        DB::statement('ALTER TABLE reservations MODIFY unit_id VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE reservations MODIFY unit_name VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE reservations MODIFY unit_price DECIMAL(12,2) NOT NULL');
    }
};
