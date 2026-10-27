<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expediente de diligencia debida (CDD) de una reserva, según el «Flow Chart
 * CDD Process» de Siebe Group (Miguel, 26 oct): screening, KYC del comprador,
 * perfil de riesgo, aprobación de cumplimiento y del director de ventas,
 * contrato, aviso a la FIU, revisión previa a la escritura y escritura.
 * Cada paso guarda quién y cuándo, para la auditoría (archivo de 10 años).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cdd_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('buyer_type', 10)->nullable();              // natural | legal

            $table->string('screening_status', 20)->default('pendiente'); // pendiente | limpio | coincidencia
            $table->text('screening_notes')->nullable();
            $table->timestamp('screened_at')->nullable();
            $table->foreignId('screened_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('kyc_status', 20)->default('no_solicitado'); // no_solicitado | solicitado | enviado | info_adicional | revisado
            $table->text('kyc_form')->nullable();                       // cifrado
            $table->json('kyc_docs')->nullable();
            $table->text('info_request')->nullable();
            $table->timestamp('kyc_requested_at')->nullable();
            $table->timestamp('kyc_submitted_at')->nullable();

            $table->string('risk_rating', 10)->nullable();             // bajo | medio | alto
            $table->text('risk_notes')->nullable();
            $table->string('cdd_decision', 20)->default('pendiente');  // pendiente | aprobado | rechazado
            $table->text('cdd_notes')->nullable();
            $table->timestamp('cdd_decided_at')->nullable();
            $table->foreignId('cdd_decided_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('manager_decision', 20)->default('pendiente');
            $table->text('manager_notes')->nullable();
            $table->text('deviation_note')->nullable();
            $table->timestamp('manager_decided_at')->nullable();
            $table->foreignId('manager_decided_by')->nullable()->constrained('users')->nullOnDelete();

            $table->date('spa_signed_at')->nullable();
            $table->date('fiu_reported_at')->nullable();
            $table->string('fiu_reference', 80)->nullable();
            $table->timestamp('predeed_reviewed_at')->nullable();
            $table->foreignId('predeed_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('predeed_notes')->nullable();
            $table->date('deed_date')->nullable();

            $table->json('log')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cdd_files');
    }
};
