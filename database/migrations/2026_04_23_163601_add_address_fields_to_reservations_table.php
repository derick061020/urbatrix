<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->text('address')->nullable()->after('country'); // Dirección principal (Calle/número)
            $table->string('province')->nullable()->after('address'); // Provincia / Estado
            $table->string('neighborhood')->nullable()->after('province'); // Barrio / Sector
            $table->string('city')->nullable()->after('neighborhood'); // Ciudad
            $table->string('building_name')->nullable()->after('city'); // Nombre del edificio / Torre
            $table->string('apartment_number')->nullable()->after('building_name'); // Nro. Apartamento / Residencia
            $table->string('postal_code')->nullable()->after('apartment_number'); // Código postal
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'province', 
                'neighborhood',
                'city',
                'building_name',
                'apartment_number',
                'postal_code'
            ]);
        });
    }
};
