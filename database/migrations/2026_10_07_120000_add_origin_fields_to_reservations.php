<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen comercial de la reserva: proyecto, broker que trajo al cliente y
 * agencia para la que trabaja.
 *
 * Hasta ahora sólo existían en `users` (vienen de la importación del CRM
 * anterior), pero ahí describen al cliente, no a la operación: el mismo
 * cliente puede volver con otro broker o para otro proyecto. Se guardan por
 * reserva y se copian al usuario cuando él no los tenga, para no perder lo
 * que ya mostraba la ficha.
 *
 * Broker y agencia van como texto, no como relación: muchos brokers que traen
 * clientes no tienen cuenta en el sistema, y bloquear la carga por eso haría
 * que el dato se pierda. El formulario sugiere los que ya existen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'project_id')) {
                $table->foreignId('project_id')->nullable()->after('unit_id')
                      ->constrained('projects')->nullOnDelete();
            }
            if (! Schema::hasColumn('reservations', 'broker_name')) {
                $table->string('broker_name')->nullable()->after('project_id');
            }
            if (! Schema::hasColumn('reservations', 'agency')) {
                $table->string('agency')->nullable()->after('broker_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'project_id')) {
                $table->dropConstrainedForeignId('project_id');
            }
            foreach (['broker_name', 'agency'] as $col) {
                if (Schema::hasColumn('reservations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
