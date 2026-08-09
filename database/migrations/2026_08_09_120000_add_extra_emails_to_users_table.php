<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Correos adicionales del usuario (co-compradores, correo secundario…).
 * Son sólo dato de contacto: el correo principal sigue siendo el de acceso
 * y el destinatario de los correos del sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'extra_emails')) {
                $table->json('extra_emails')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'extra_emails')) {
                $table->dropColumn('extra_emails');
            }
        });
    }
};
