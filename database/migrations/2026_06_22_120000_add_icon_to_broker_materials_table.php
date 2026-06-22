<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broker_materials', function (Blueprint $table) {
            $table->string('icon', 60)->nullable()->after('format'); // primeicon elegido manualmente (ej. pi-file-pdf)
        });
    }

    public function down(): void
    {
        Schema::table('broker_materials', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
