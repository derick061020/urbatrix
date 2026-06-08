<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convierte las automatizaciones del CRM en cadenas de varias fases:
 *   trigger inicial → paso 1 (inmediato) → esperar X min → paso 2 → ...
 *
 * Cada paso (crm_automation_steps) envía una plantilla por sus canales, con un
 * retraso medido respecto del paso anterior (o del disparo, para el primero).
 * Las automatizaciones existentes se migran a un único paso para no perder nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_automation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained('crm_automations')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->foreignId('template_id')->nullable()->constrained('crm_templates')->nullOnDelete();
            // Retraso (en minutos) respecto del paso anterior (o del disparo, si es el primero).
            $table->unsignedInteger('delay_minutes')->default(0);
            $table->json('channels')->nullable();
            $table->timestamps();

            $table->index(['automation_id', 'position']);
        });

        // Migra cada automatización existente a un primer paso equivalente.
        $now = now();
        DB::table('crm_automations')->orderBy('id')->each(function ($auto) use ($now) {
            DB::table('crm_automation_steps')->insert([
                'automation_id' => $auto->id,
                'position'      => 1,
                'template_id'   => $auto->template_id,
                'delay_minutes' => $auto->delay_minutes ?? 0,
                'channels'      => $auto->channels, // ya viene como JSON en la columna origen
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_automation_steps');
    }
};
