<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Proyecto por el que el cliente está interesado, editable desde su ficha.
 *
 * No se reutiliza `users.project`: esa columna viene de la importación del CRM
 * anterior y está mal mapeada —guarda ciudades (Montréal, Cali, MADRID)—,
 * igual que `broker` guarda nombres de proyecto y `agency` nacionalidades.
 * Mezclar el dato bueno ahí lo volvería inservible para siempre.
 *
 * Se deja aparte, como relación de verdad, para poder listar y filtrar por
 * proyecto más adelante. La columna vieja no se toca ni se borra: puede ser la
 * única copia de ese dato de origen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'project_id')) {
                $table->foreignId('project_id')->nullable()->after('agency')
                      ->constrained('projects')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'project_id')) {
                $table->dropConstrainedForeignId('project_id');
            }
        });
    }
};
