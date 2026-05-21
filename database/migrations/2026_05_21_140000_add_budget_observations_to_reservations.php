<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'budget_observations')) {
                $table->json('budget_observations')->nullable()->after('budget_notes');
            }
            if (! Schema::hasColumn('reservations', 'budget_client_response_at')) {
                $table->timestamp('budget_client_response_at')->nullable()->after('budget_observations');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            foreach (['budget_observations', 'budget_client_response_at'] as $col) {
                if (Schema::hasColumn('reservations', $col)) $table->dropColumn($col);
            }
        });
    }
};
