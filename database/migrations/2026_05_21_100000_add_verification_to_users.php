<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'verification_status')) {
                $table->string('verification_status', 20)->default('approved')->after('role');
            }
            if (! Schema::hasColumn('users', 'kyc_id_document')) {
                $table->string('kyc_id_document', 500)->nullable()->after('verification_status');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country', 10)->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['verification_status', 'kyc_id_document', 'phone', 'country'] as $col) {
                if (Schema::hasColumn('users', $col)) $table->dropColumn($col);
            }
        });
    }
};
