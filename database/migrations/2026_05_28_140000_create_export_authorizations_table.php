<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resource', 40);
            $table->string('format', 10)->default('csv');
            $table->string('range', 10)->default('3m');
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('seen_by_admin_at')->nullable();
            $table->timestamps();

            $table->index(['requester_id', 'used_at']);
            $table->index(['admin_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_authorizations');
    }
};
