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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('AVAILABLE');
            $table->string('type');
            $table->decimal('price', 10, 2);
            $table->boolean('public')->default(true);
            $table->boolean('pre_arranged')->default(false);
            $table->integer('shortlisted_count')->default(0);
            $table->integer('images_count')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
