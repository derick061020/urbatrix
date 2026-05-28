<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 60)->default('otro');
            $table->string('icon', 40)->default('file');
            $table->json('channels');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('variables')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('crm_automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_event', 80);
            $table->json('trigger_conditions')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('crm_templates')->nullOnDelete();
            $table->unsignedInteger('delay_minutes')->default(0);
            $table->json('channels');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->unsignedInteger('run_count')->default(0);
            $table->timestamps();
        });

        Schema::create('crm_channel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 40)->unique();
            $table->boolean('enabled')->default(false);
            $table->json('config')->nullable();
            $table->timestamps();
        });

        $now = now();

        $templates = [
            ['name' => 'Bienvenida — Reserva confirmada', 'category' => 'bienvenida', 'icon' => 'file', 'channels' => ['email','whatsapp'],
             'subject' => '¡Bienvenido a Makai, {{cliente_nombre}}!',
             'body' => "Hola {{cliente_nombre}},\n\nTu reserva para la unidad {{unidad}} ha sido confirmada. Estamos felices de tenerte en la familia Makai.\n\nNuestro equipo se pondrá en contacto contigo en las próximas 24 horas para los siguientes pasos.\n\nSaludos,\nEquipo Makai",
             'variables' => ['cliente_nombre','unidad','proyecto','fecha_reserva']],

            ['name' => 'KYC — Documentos pendientes', 'category' => 'seguimiento', 'icon' => 'user', 'channels' => ['email','whatsapp'],
             'subject' => 'Documentos pendientes para tu expediente',
             'body' => "Hola {{cliente_nombre}},\n\nPara avanzar con tu reserva necesitamos completar tu KYC. Por favor sube los siguientes documentos:\n\n- Identificación oficial\n- Comprobante de domicilio\n- Comprobante de ingresos\n\nPuedes subirlos directamente en tu portal: {{portal_url}}\n\nGracias,\nEquipo Makai",
             'variables' => ['cliente_nombre','portal_url']],

            ['name' => 'Recordatorio de cuota', 'category' => 'pagos', 'icon' => 'eye', 'channels' => ['email','whatsapp'],
             'subject' => 'Recordatorio: cuota próxima a vencer',
             'body' => "Hola {{cliente_nombre}},\n\nTe recordamos que tu próxima cuota de {{monto}} vence el {{fecha_vencimiento}}.\n\nPuedes realizar el pago desde tu portal: {{portal_url}}\n\nSaludos,\nEquipo Makai",
             'variables' => ['cliente_nombre','monto','fecha_vencimiento','portal_url']],

            ['name' => 'Aviso pago vencido', 'category' => 'pagos', 'icon' => 'clock', 'channels' => ['email','whatsapp'],
             'subject' => 'Pago vencido - acción requerida',
             'body' => "Hola {{cliente_nombre}},\n\nDetectamos que tu cuota de {{monto}} con vencimiento el {{fecha_vencimiento}} aún no ha sido registrada.\n\nPor favor regulariza el pago a la brevedad para evitar cargos por mora.\n\nSi ya pagaste, ignora este mensaje.\n\nEquipo Makai",
             'variables' => ['cliente_nombre','monto','fecha_vencimiento']],

            ['name' => 'Promesa de compraventa lista', 'category' => 'legal', 'icon' => 'file-pdf', 'channels' => ['email','whatsapp'],
             'subject' => 'Tu promesa de compraventa está lista para firma',
             'body' => "Hola {{cliente_nombre}},\n\nTu promesa de compraventa para la unidad {{unidad}} ya está lista. Puedes revisarla y firmarla digitalmente desde tu portal: {{portal_url}}\n\nCualquier duda, contáctanos.\n\nEquipo Legal Makai",
             'variables' => ['cliente_nombre','unidad','portal_url']],

            ['name' => 'Actualización avance de obra', 'category' => 'proyectos', 'icon' => 'chart-line', 'channels' => ['email','whatsapp'],
             'subject' => 'Avance de obra — {{proyecto}}',
             'body' => "Hola {{cliente_nombre}},\n\nTe compartimos el último avance de obra de {{proyecto}}. Avance actual: {{avance}}%.\n\nMira las fotos y detalles en tu portal: {{portal_url}}\n\nEquipo Makai",
             'variables' => ['cliente_nombre','proyecto','avance','portal_url']],

            ['name' => 'Felicitación cierre de contrato', 'category' => 'seguimiento', 'icon' => 'check', 'channels' => ['email','whatsapp'],
             'subject' => '¡Felicidades por tu nueva propiedad!',
             'body' => "Hola {{cliente_nombre}},\n\n¡Felicidades! Tu contrato para la unidad {{unidad}} se ha cerrado exitosamente.\n\nTe acompañaremos en cada paso hasta la entrega.\n\nGracias por confiar en Makai.\n\nEquipo Makai",
             'variables' => ['cliente_nombre','unidad']],
        ];

        $insertedIds = [];
        foreach ($templates as $t) {
            $insertedIds[$t['name']] = DB::table('crm_templates')->insertGetId([
                'name' => $t['name'],
                'category' => $t['category'],
                'icon' => $t['icon'],
                'channels' => json_encode($t['channels']),
                'subject' => $t['subject'],
                'body' => $t['body'],
                'variables' => json_encode($t['variables']),
                'last_used_at' => $now->copy()->subDays(2),
                'usage_count' => rand(1, 50),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $automations = [
            ['name' => 'Bienvenida al confirmar reserva', 'description' => 'Envía mensaje de bienvenida automático cuando una reserva se confirma.',
             'trigger_event' => 'reservation_confirmed', 'template_name' => 'Bienvenida — Reserva confirmada', 'delay_minutes' => 0, 'channels' => ['email','whatsapp'], 'is_active' => true],
            ['name' => 'Recordatorio cuota 3 días antes', 'description' => 'Envía recordatorio 3 días antes del vencimiento de cuota.',
             'trigger_event' => 'payment_due_soon', 'template_name' => 'Recordatorio de cuota', 'delay_minutes' => 0, 'channels' => ['email','whatsapp'], 'is_active' => true],
            ['name' => 'Alerta pago vencido', 'description' => 'Notifica al cliente cuando un pago vence sin ser registrado.',
             'trigger_event' => 'payment_overdue', 'template_name' => 'Aviso pago vencido', 'delay_minutes' => 60, 'channels' => ['email','whatsapp'], 'is_active' => true],
            ['name' => 'Avance de obra mensual', 'description' => 'Envía resumen de avance de obra mensual a clientes activos.',
             'trigger_event' => 'progress_update', 'template_name' => 'Actualización avance de obra', 'delay_minutes' => 0, 'channels' => ['email','whatsapp'], 'is_active' => true],
        ];

        foreach ($automations as $a) {
            DB::table('crm_automations')->insert([
                'name' => $a['name'],
                'description' => $a['description'],
                'trigger_event' => $a['trigger_event'],
                'trigger_conditions' => null,
                'template_id' => $insertedIds[$a['template_name']] ?? null,
                'delay_minutes' => $a['delay_minutes'],
                'channels' => json_encode($a['channels']),
                'is_active' => $a['is_active'],
                'last_run_at' => $now->copy()->subDays(1),
                'run_count' => rand(5, 100),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('crm_channel_settings')->insert([
            ['channel' => 'email', 'enabled' => true, 'config' => json_encode(['from_name' => 'Makai CRM', 'from_email' => 'no-reply@makai.do', 'reply_to' => 'hola@makai.do']), 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'whatsapp', 'enabled' => true, 'config' => json_encode(['business_number' => '+1 809 555 0100', 'api_provider' => 'twilio']), 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'sms', 'enabled' => false, 'config' => json_encode(['provider' => '', 'sender_id' => '']), 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'push', 'enabled' => false, 'config' => json_encode(['app_key' => '']), 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_automations');
        Schema::dropIfExists('crm_templates');
        Schema::dropIfExists('crm_channel_settings');
    }
};
