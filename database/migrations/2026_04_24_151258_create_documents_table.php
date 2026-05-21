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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->string('document_type'); // 'payment_plan', 'purchase_promise', 'contract', 'id_document', etc.
            $table->string('title'); // Título descriptivo del documento
            $table->string('filename'); // Nombre del archivo
            $table->string('file_path'); // Path al archivo almacenado
            $table->enum('status', ['pending', 'generated', 'signed', 'approved', 'rejected'])->default('pending');
            $table->timestamp('generated_at')->nullable(); // Cuándo se generó
            $table->timestamp('signed_at')->nullable(); // Cuándo se firmó
            $table->timestamp('approved_at')->nullable(); // Cuándo se aprobó
            $table->foreignId('signed_by')->nullable()->constrained('users')->onDelete('set null'); // Quién firmó
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Quién aprobó
            $table->text('notes')->nullable(); // Notas adicionales
            $table->json('metadata')->nullable(); // Datos adicionales en JSON
            $table->timestamps();
            
            // Índices
            $table->index(['reservation_id', 'document_type']);
            $table->index(['status']);
            $table->index(['document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
