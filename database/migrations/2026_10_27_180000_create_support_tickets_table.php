<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tickets del salvavidas: el botón flotante que hay en todas las páginas.
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // El anónimo no tiene cuenta: sus tickets van atados a una cookie.
            $table->string('guest_token', 64)->nullable()->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('comment');
            $table->string('screenshot')->nullable();
            $table->json('attachments')->nullable();
            $table->string('page_url', 2048)->nullable();
            $table->string('page_title')->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('viewport', 32)->nullable();
            $table->string('status', 20)->default('abierto');   // abierto · en_curso · resuelto
            $table->text('reply')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('seen_at')->nullable();           // el usuario vio la respuesta
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
