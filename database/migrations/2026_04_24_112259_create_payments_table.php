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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->string('payment_type'); // 'initial', 'construction', 'delivery', 'installment'
            $table->integer('installment_number')->nullable(); // Para cuotas (1, 2, 3...)
            $table->string('label'); // Descripción del pago
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable(); // 'transfer', 'cash', 'card', etc.
            $table->string('receipt_path')->nullable(); // Path al comprobante
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['reservation_id', 'status']);
            $table->index(['due_date']);
            $table->index(['payment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
