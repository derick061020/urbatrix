<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // broker dueño
            $table->string('title');
            $table->string('category')->default('Contrato');  // Contrato | Anexo | Legal | …
            $table->string('format', 12)->default('PDF');      // PDF | DOCX | IMG | …
            $table->string('file_path');                       // storage/public
            $table->string('file_size')->nullable();           // "1.2 MB"
            $table->unsignedInteger('downloads')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_documents');
    }
};
