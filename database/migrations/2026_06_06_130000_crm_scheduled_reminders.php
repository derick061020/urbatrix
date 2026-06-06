<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Soporte para los recordatorios automáticos del CRM:
 *  - reservations.kyc_reminded_at  → marca el envío del recordatorio KYC (E-02)
 *  - payments.overdue_notified_at  → marca el aviso de mora ya enviado (E-10)
 * Además activa las automatizaciones E-02 y E-10 para que el scheduler las dispare.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'kyc_reminded_at')) {
                $table->timestamp('kyc_reminded_at')->nullable()->after('paid_at');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'overdue_notified_at')) {
                $table->timestamp('overdue_notified_at')->nullable()->after('paid_at');
            }
        });

        // Activa las automatizaciones que ahora dispara el scheduler.
        DB::table('crm_automations')->whereIn('trigger_event', ['kyc_pending', 'payment_overdue'])
            ->update(['is_active' => true, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('crm_automations')->whereIn('trigger_event', ['kyc_pending', 'payment_overdue'])
            ->update(['is_active' => false, 'updated_at' => now()]);

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'kyc_reminded_at')) {
                $table->dropColumn('kyc_reminded_at');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'overdue_notified_at')) {
                $table->dropColumn('overdue_notified_at');
            }
        });
    }
};
