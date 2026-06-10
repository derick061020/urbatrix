<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite usuarios sin e-mail (contactos del CRM importados que no traen correo).
 * El índice único de `email` se conserva: en MySQL admite múltiples NULL, así que
 * varios registros sin correo conviven sin romper la unicidad de los que sí tienen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
