<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('advisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(45);
            $table->string('google_event_id')->nullable();
            $table->string('google_meet_link')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['advisor_id', 'scheduled_at']);
            $table->index(['user_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
