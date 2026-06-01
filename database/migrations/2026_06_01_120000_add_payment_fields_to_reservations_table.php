<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('reservations', 'stripe_payment_intent')) {
                $table->string('stripe_payment_intent')->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('reservations', 'reservation_fee')) {
                $table->decimal('reservation_fee', 10, 2)->default(5000)->after('stripe_payment_intent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            foreach (['paid_at', 'stripe_payment_intent', 'reservation_fee'] as $col) {
                if (Schema::hasColumn('reservations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
