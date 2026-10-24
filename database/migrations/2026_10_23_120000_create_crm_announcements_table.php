<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anuncios internos del CRM. Hasta ahora la página enseñaba tres anuncios
 * escritos en la vista y sus botones no hacían nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->json('audience')->nullable();      // equipo · compradores · brokers
            $table->boolean('pinned')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_announcements');
    }
};
