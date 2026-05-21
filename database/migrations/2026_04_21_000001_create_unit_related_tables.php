<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('unit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->dateTime('datetime');
            $table->string('action');
            $table->string('author');
            $table->string('author_role');
            $table->timestamps();
        });

        Schema::create('deal_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('datetime');
            $table->string('action');
            $table->string('author');
            $table->string('author_role');
            $table->timestamps();
        });

        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('created_at_event')->nullable();
            $table->string('created_by')->nullable();
            $table->dateTime('modified_at_event')->nullable();
            $table->string('modified_by')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('INITIATED');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
        Schema::dropIfExists('deal_histories');
        Schema::dropIfExists('unit_histories');
        Schema::dropIfExists('unit_images');
    }
};
