<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Firma manuscrita del comprador sobre el comprobante de pago.
     * Antes la firma sólo se dibujaba en el iframe del navegador y se perdía
     * al cerrar el modal; ahora queda registrada como constancia de recepción.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->longText('receipt_signature')->nullable()->after('notes');
            $table->string('receipt_signer_name')->nullable()->after('receipt_signature');
            $table->timestamp('receipt_signed_at')->nullable()->after('receipt_signer_name');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['receipt_signature', 'receipt_signer_name', 'receipt_signed_at']);
        });
    }
};
