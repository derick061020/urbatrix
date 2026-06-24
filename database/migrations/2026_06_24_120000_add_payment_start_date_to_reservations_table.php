<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Fecha de inicio del plan de pagos. Puede ser anterior a hoy.
            // Si es null, el plan arranca en la fecha actual (comportamiento previo).
            $table->date('payment_start_date')->nullable()->after('payment_installments');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('payment_start_date');
        });
    }
};
