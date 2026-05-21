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
            $table->string('profession')->nullable()->after('country');
            $table->string('occupation')->nullable()->after('profession');
            $table->string('economic_dependent')->nullable()->after('occupation');
            $table->string('payment_method')->nullable()->after('economic_dependent');
            $table->boolean('terms_accepted')->default(false)->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['profession', 'occupation', 'economic_dependent', 'payment_method', 'terms_accepted']);
        });
    }
};
