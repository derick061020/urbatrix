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
        Schema::table('reservations', function (Blueprint $table) {
            $table->decimal('payment_initial_percentage', 5, 2)->default(0)->after('payment_method');
            $table->decimal('payment_construction_percentage', 5, 2)->default(0)->after('payment_initial_percentage');
            $table->decimal('payment_delivery_percentage', 5, 2)->default(0)->after('payment_construction_percentage');
            $table->decimal('legal_costs', 10, 2)->default(500)->after('payment_delivery_percentage');
            $table->integer('payment_installments')->default(0)->after('legal_costs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'payment_initial_percentage',
                'payment_construction_percentage', 
                'payment_delivery_percentage',
                'legal_costs',
                'payment_installments'
            ]);
        });
    }
};
