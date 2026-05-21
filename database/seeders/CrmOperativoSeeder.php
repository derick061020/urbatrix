<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Task;
use App\Models\Approval;
use App\Models\Aftersale;
use App\Models\Reservation;
use App\Models\Unit;

class CrmOperativoSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure additional projects exist
        $projects = [
            ['name' => 'Naviva Residences', 'type' => 'Horizontal', 'stage' => 'Construcción', 'progress' => 61, 'color' => '#2EBFA0'],
            ['name' => 'LIV at Cap Cana',   'type' => 'Vertical',   'stage' => 'Preventa',     'progress' => 12, 'color' => '#4A8FD4'],
            ['name' => 'Altum Corporate',   'type' => 'Mixto',      'stage' => 'Entrega',      'progress' => 94, 'color' => '#8B68D4'],
        ];
        foreach ($projects as $p) {
            Project::firstOrCreate(['name' => $p['name']], $p);
        }

        $reservations = Reservation::take(5)->get();

        // Tasks
        $taskTemplates = [
            ['title' => 'Validar KYC del cliente', 'area' => 'Admin',     'priority' => 'alta',  'status' => 'pendiente',  'due_date' => now()->toDateString()],
            ['title' => 'Enviar contrato firmado', 'area' => 'Legal',     'priority' => 'alta',  'status' => 'en_proceso', 'due_date' => now()->addDay()->toDateString()],
            ['title' => 'Checklist de entrega',     'area' => 'Postventa', 'priority' => 'media', 'status' => 'pendiente',  'due_date' => now()->addDays(3)->toDateString()],
            ['title' => 'Revisar planos del piso 3','area' => 'Proyectos','priority' => 'baja',  'status' => 'completada', 'due_date' => now()->addDays(5)->toDateString()],
            ['title' => 'Enviar docs a notaría',    'area' => 'Legal',     'priority' => 'alta',  'status' => 'vencida',    'due_date' => now()->subDays(2)->toDateString()],
        ];
        foreach ($taskTemplates as $i => $t) {
            $r = $reservations[$i % max($reservations->count(), 1)] ?? null;
            Task::firstOrCreate(
                ['title' => $t['title']],
                array_merge($t, [
                    'responsible'    => ['Ana M.', 'José R.', 'Luis P.'][$i % 3],
                    'reservation_id' => $r?->id,
                ])
            );
        }

        // Approvals
        $approvalTemplates = [
            ['type' => 'Descuento',   'amount_or_condition' => '5%',     'priority' => 'alta',  'requested_by' => 'Carlos Méndez'],
            ['type' => 'Prórroga',    'amount_or_condition' => '30 días','priority' => 'media', 'requested_by' => 'Luisa Vera'],
            ['type' => 'Cancelación', 'amount_or_condition' => '$5,000', 'priority' => 'alta',  'requested_by' => 'Marco Torres'],
            ['type' => 'Contrato',    'amount_or_condition' => 'Rev.4',  'priority' => 'baja',  'requested_by' => 'Ana Medina'],
            ['type' => 'Devolución',  'amount_or_condition' => '$2,000', 'priority' => 'alta',  'requested_by' => 'Carlos Méndez'],
        ];
        foreach ($approvalTemplates as $i => $a) {
            $r = $reservations[$i % max($reservations->count(), 1)] ?? null;
            Approval::firstOrCreate(
                ['type' => $a['type'], 'requested_by' => $a['requested_by']],
                array_merge($a, [
                    'status'         => 'pendiente',
                    'reservation_id' => $r?->id,
                    'created_at'     => now()->subHours(rand(1, 72)),
                ])
            );
        }

        // Aftersales
        $unit = Unit::first();
        $aftersaleTemplates = [
            ['type' => 'Entrega',   'status' => 'programada',  'scheduled_date' => now()->addDays(2)->toDateString()],
            ['type' => 'Garantía',  'status' => 'en_atencion', 'scheduled_date' => now()->subDays(8)->toDateString()],
            ['type' => 'Escritura', 'status' => 'en_tramite',  'scheduled_date' => now()->addDays(15)->toDateString()],
            ['type' => 'Garantía',  'status' => 'resuelta',    'scheduled_date' => now()->subDays(18)->toDateString()],
        ];
        foreach ($aftersaleTemplates as $i => $a) {
            $r = $reservations[$i % max($reservations->count(), 1)] ?? null;
            Aftersale::firstOrCreate(
                ['type' => $a['type'], 'scheduled_date' => $a['scheduled_date']],
                array_merge($a, [
                    'client_name'    => $r ? ($r->first_name . ' ' . $r->last_name) : 'Cliente prospecto',
                    'unit_label'     => $r->unit_name ?? optional($unit)->name ?? 'Unit 111',
                    'reservation_id' => $r?->id,
                    'unit_id'        => $unit?->id,
                ])
            );
        }
    }
}
