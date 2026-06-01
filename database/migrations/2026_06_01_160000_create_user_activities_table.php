<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);                 // login, property_view, document_view, document_download, payment, kyc_upload…
            $table->string('description')->nullable();   // texto legible para el feed
            $table->nullableMorphs('subject');           // subject_type / subject_id (Unit, Document, Payment…)
            $table->json('meta')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable(); // sesiones (type=login)
            $table->timestamp('last_activity_at')->nullable();       // heartbeat de sesión
            $table->timestamps();

            $table->index(['user_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
