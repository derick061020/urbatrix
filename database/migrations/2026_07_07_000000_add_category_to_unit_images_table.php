<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_images', function (Blueprint $table) {
            // 'property' (PROPIEDAD) o 'amenities' (AMENIDADES). Las imágenes
            // existentes se agrupan como propiedad por defecto.
            $table->string('category')->default('property')->after('unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('unit_images', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
