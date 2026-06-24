<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Monto realmente abonado a la cuota. Permite pagos parciales
            // (deja saldo/deuda) o sobrepagos (excedente que se aplica a las
            // siguientes cuotas).
            $table->decimal('paid_amount', 10, 2)->default(0)->after('amount');
        });

        // Las cuotas ya marcadas como pagadas se consideran abonadas por completo.
        DB::table('payments')->where('status', 'paid')->update([
            'paid_amount' => DB::raw('amount'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
