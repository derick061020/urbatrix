<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (! Schema::hasColumn('units', 'reserved_until')) {
                $table->timestamp('reserved_until')->nullable()->after('status');
            }
            if (! Schema::hasColumn('units', 'reserved_by_reservation_id')) {
                $table->foreignId('reserved_by_reservation_id')->nullable()
                    ->after('reserved_until')->constrained('reservations')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'reserved_by_reservation_id')) {
                $table->dropConstrainedForeignId('reserved_by_reservation_id');
            }
            if (Schema::hasColumn('units', 'reserved_until')) {
                $table->dropColumn('reserved_until');
            }
        });
    }
};
