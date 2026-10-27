<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ficha de alta del broker (Miguel, 26 oct: «Popup_Alta_Broker»): tipo
 * (broker / agencia), datos profesionales, datos fiscales y bancarios y los
 * documentos KYC. La verificación sigue siendo users.verification_status; aquí
 * va lo que el broker rellena y en qué punto está.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('kind', 20)->default('broker');      // broker | agency
            $table->json('data')->nullable();                     // datos personales y profesionales
            $table->text('fiscal')->nullable();                   // fiscales y bancarios, cifrados
            $table->json('docs')->nullable();                     // clave => ruta en el disco local
            $table->string('status', 20)->default('borrador');   // borrador | en_revision | verificado | rechazado
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_profiles');
    }
};
