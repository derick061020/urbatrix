<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Flow chart CDD de Siebe Group: el depósito debería llegar a nombre del que
 * reserva; si no, se avisa a cumplimiento para investigarlo. Se anota aquí.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cdd_files', function (Blueprint $table) {
            $table->boolean('payment_name_ok')->nullable()->after('screened_by');
            $table->string('payment_notes', 2000)->nullable()->after('payment_name_ok');
            $table->timestamp('payment_checked_at')->nullable()->after('payment_notes');
        });
    }

    public function down(): void
    {
        Schema::table('cdd_files', function (Blueprint $table) {
            $table->dropColumn(['payment_name_ok', 'payment_notes', 'payment_checked_at']);
        });
    }
};
