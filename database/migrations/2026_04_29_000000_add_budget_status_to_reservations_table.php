<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('budget_status', 20)->default('pending')->after('payment_installments');
            $table->timestamp('budget_sent_at')->nullable()->after('budget_status');
            $table->unsignedBigInteger('budget_configured_by')->nullable()->after('budget_sent_at');
            $table->text('budget_notes')->nullable()->after('budget_configured_by');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'budget_status',
                'budget_sent_at',
                'budget_configured_by',
                'budget_notes',
            ]);
        });
    }
};
