<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (! Schema::hasColumn('units', 'is_high_demand')) {
                $table->boolean('is_high_demand')->default(false)->after('shortlisted_count');
            }
            if (! Schema::hasColumn('units', 'is_second_chance')) {
                $table->boolean('is_second_chance')->default(false)->after('is_high_demand');
            }
            if (! Schema::hasColumn('units', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('is_second_chance');
            }
            if (! Schema::hasColumn('units', 'views_today')) {
                $table->unsignedInteger('views_today')->default(0)->after('released_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            foreach (['is_high_demand', 'is_second_chance', 'released_at', 'views_today'] as $col) {
                if (Schema::hasColumn('units', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
