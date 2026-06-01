<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('construction_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period');                 // "Mayo 2026"
            $table->string('title');                  // "Mampostería y estructura"
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('overall_progress')->default(0);
            $table->string('estimated_delivery')->nullable(); // "Q4 2026"
            $table->json('phases')->nullable();       // [{name,status,date,pct}]
            $table->json('photos')->nullable();       // ["path1.jpg", ...]
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->unsignedInteger('notified_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('construction_reports');
    }
};
