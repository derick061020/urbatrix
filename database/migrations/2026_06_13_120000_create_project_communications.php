<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Estado de comunicaciones por proyecto. Todo proyecto nace en silencio.
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'comms_active')) {
                $table->boolean('comms_active')->default(false)->after('description');
            }
            if (!Schema::hasColumn('projects', 'comms_start_date')) {
                $table->date('comms_start_date')->nullable()->after('comms_active');
            }
        });

        // Matriz de comunicaciones: por proyecto, por tipo y por canal.
        Schema::create('project_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('comm_code');   // ej. recibo_pago
            $table->string('channel');     // email | whatsapp | inapp
            $table->boolean('enabled')->default(false);
            $table->timestamps();

            $table->unique(['project_id', 'comm_code', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_communications');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['comms_active', 'comms_start_date']);
        });
    }
};
