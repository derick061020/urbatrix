<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * A freshly scheduled installment has not been submitted for approval yet,
     * so its approval_status must be NULL — not 'pending'. The previous default
     * of 'pending' made every generated installment look "en revisión" and
     * flooded the admin approval queue. 'pending' now only means a client has
     * actually uploaded a receipt awaiting review.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY approval_status ENUM('pending','approved','rejected') NULL DEFAULT NULL");

        // Backfill: installments that were never submitted (no receipt) and are
        // not yet paid should not be sitting "en revisión".
        DB::table('payments')
            ->where('approval_status', 'pending')
            ->whereNull('receipt_path')
            ->where('status', '!=', 'paid')
            ->update(['approval_status' => null]);
    }

    public function down(): void
    {
        DB::table('payments')->whereNull('approval_status')->update(['approval_status' => 'pending']);
        DB::statement("ALTER TABLE payments MODIFY approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
    }
};
