<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();      // Renders, Planos, Brochure, etc.
            $table->string('format', 12)->default('PDF'); // PDF | ZIP | MP4 | XLSX | DOCX | IMG
            $table->string('file_path')->nullable();      // archivo subido (storage/public)
            $table->string('external_url')->nullable();   // o enlace externo
            $table->string('file_size')->nullable();      // "12.4 MB"
            $table->boolean('visible')->default(true);    // visible para brokers
            $table->unsignedInteger('downloads')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_materials');
    }
};
