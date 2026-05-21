<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('Vertical');
            $table->string('stage')->default('Preventa');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('color', 9)->default('#667b6a');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('responsible')->nullable();
            $table->string('area')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['alta', 'media', 'baja'])->default('media');
            $table->enum('status', ['pendiente', 'en_proceso', 'completada', 'vencida'])->default('pendiente');
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('requested_by')->nullable();
            $table->string('amount_or_condition')->nullable();
            $table->enum('priority', ['alta', 'media', 'baja'])->default('media');
            $table->enum('status', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('aftersales', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Entrega', 'Garantía', 'Escritura'])->default('Garantía');
            $table->string('client_name')->nullable();
            $table->string('unit_label')->nullable();
            $table->enum('status', ['programada', 'en_atencion', 'en_tramite', 'resuelta'])->default('programada');
            $table->date('scheduled_date')->nullable();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Seed: project for existing Makai units
        $projectId = \DB::table('projects')->insertGetId([
            'name'        => 'Makai Cap Cana',
            'type'        => 'Vertical',
            'stage'       => 'Construcción',
            'progress'    => 38,
            'color'       => '#C9A84C',
            'description' => 'Proyecto principal',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Add project_id to units if not present
        if (Schema::hasTable('units') && !Schema::hasColumn('units', 'project_id')) {
            Schema::table('units', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
            \DB::table('units')->whereNull('project_id')->update(['project_id' => $projectId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('units') && Schema::hasColumn('units', 'project_id')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropConstrainedForeignId('project_id');
            });
        }
        Schema::dropIfExists('aftersales');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('projects');
    }
};
