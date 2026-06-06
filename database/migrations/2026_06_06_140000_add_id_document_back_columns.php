<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'id_document_back_path')) {
                $table->string('id_document_back_path')->nullable()->after('id_document_path');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'kyc_id_document_back')) {
                $table->string('kyc_id_document_back', 500)->nullable()->after('kyc_id_document');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'id_document_back_path')) {
                $table->dropColumn('id_document_back_path');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'kyc_id_document_back')) {
                $table->dropColumn('kyc_id_document_back');
            }
        });
    }
};
