<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (! Schema::hasColumn('units', 'for_investment_text')) {
                $table->text('for_investment_text')->nullable()->after('description');
            }
            if (! Schema::hasColumn('units', 'for_living_text')) {
                $table->text('for_living_text')->nullable()->after('for_investment_text');
            }
            if (! Schema::hasColumn('units', 'projected_value')) {
                $table->decimal('projected_value', 14, 2)->nullable()->after('for_living_text');
            }
            if (! Schema::hasColumn('units', 'projected_value_year')) {
                $table->string('projected_value_year', 10)->nullable()->after('projected_value');
            }
            if (! Schema::hasColumn('units', 'roi_percent')) {
                $table->decimal('roi_percent', 5, 2)->nullable()->after('projected_value_year');
            }
            if (! Schema::hasColumn('units', 'comparison_text')) {
                $table->string('comparison_text', 500)->nullable()->after('roi_percent');
            }
            if (! Schema::hasColumn('units', 'amenities_text')) {
                $table->string('amenities_text', 500)->nullable()->after('comparison_text');
            }
            if (! Schema::hasColumn('units', 'walk_score')) {
                $table->unsignedTinyInteger('walk_score')->nullable()->after('amenities_text');
            }
            if (! Schema::hasColumn('units', 'school_proximity')) {
                $table->string('school_proximity', 255)->nullable()->after('walk_score');
            }
            if (! Schema::hasColumn('units', 'views_total')) {
                $table->unsignedInteger('views_total')->default(0)->after('views_today');
            }
        });

        if (! Schema::hasTable('unit_views')) {
            Schema::create('unit_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('session_id', 64)->nullable();
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('viewed_at')->useCurrent();
                $table->timestamps();
                $table->index(['unit_id', 'viewed_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_views');

        Schema::table('units', function (Blueprint $table) {
            $cols = ['for_investment_text', 'for_living_text', 'projected_value', 'projected_value_year',
                     'roi_percent', 'comparison_text', 'amenities_text', 'walk_score', 'school_proximity', 'views_total'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('units', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
