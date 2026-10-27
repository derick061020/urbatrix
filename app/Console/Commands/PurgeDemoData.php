<?php

namespace App\Console\Commands;

use App\Models\Aftersale;
use App\Models\Approval;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Quita los datos de ejemplo de CrmOperativoSeeder (tareas, aprobaciones y
 * casos de postventa con «Luisa Vera», «Ana Medina»…), que salían en el
 * escritorio del CRM de producción (Miguel, 26 oct: «eliminar data de prueba
 * del sistema, dejarlo limpio»).
 *
 * Sólo borra filas que coinciden exactamente con las plantillas del seeder.
 * Las cuentas de prueba sólo se listan: borrar un usuario arrastra sus
 * reservas y pagos, y eso lo decide una persona.
 *
 *   php artisan crm:purge-demo            → ensayo: lista lo que borraría
 *   php artisan crm:purge-demo --force    → borra
 */
class PurgeDemoData extends Command
{
    protected $signature = 'crm:purge-demo {--force : Borrar de verdad (sin esto sólo lista)}';

    protected $description = 'Borra los datos de ejemplo del seeder del CRM (tareas, aprobaciones, postventa)';

    private const TASKS = [
        'Validar KYC del cliente', 'Enviar contrato firmado', 'Checklist de entrega',
        'Revisar planos del piso 3', 'Enviar docs a notaría',
    ];
    private const TASK_PEOPLE = ['Ana M.', 'José R.', 'Luis P.'];

    private const APPROVALS = [
        ['Descuento', 'Carlos Méndez', '5%'],
        ['Prórroga', 'Luisa Vera', '30 días'],
        ['Cancelación', 'Marco Torres', '$5,000'],
        ['Contrato', 'Ana Medina', 'Rev.4'],
        ['Devolución', 'Carlos Méndez', '$2,000'],
    ];

    private const AFTERSALES = [
        ['Entrega', 'programada'], ['Garantía', 'en_atencion'], ['Escritura', 'en_tramite'], ['Garantía', 'resuelta'],
    ];

    public function handle(): int
    {
        $tasks = Task::whereIn('title', self::TASKS)->whereIn('responsible', self::TASK_PEOPLE)->get();

        $approvals = Approval::query()->where(function ($q) {
            foreach (self::APPROVALS as [$type, $by, $amount]) {
                $q->orWhere(fn ($w) => $w->where('type', $type)->where('requested_by', $by)->where('amount_or_condition', $amount));
            }
        })->get();

        $aftersales = Aftersale::query()->where(function ($q) {
            foreach (self::AFTERSALES as [$type, $status]) {
                $q->orWhere(fn ($w) => $w->where('type', $type)->where('status', $status));
            }
        })->get()->filter(fn ($a) => $a->created_at && $tasks->isNotEmpty()
            // Sólo los creados en la misma pasada del seeder que las tareas.
            ? abs($a->created_at->diffInMinutes($tasks->first()->created_at)) < 10
            : false);

        $this->table(['Tipo', 'Id', 'Detalle'], collect()
            ->merge($tasks->map(fn ($t) => ['Tarea', $t->id, $t->title . ' · ' . $t->responsible]))
            ->merge($approvals->map(fn ($a) => ['Aprobación', $a->id, $a->type . ' · ' . $a->requested_by . ' · ' . $a->amount_or_condition]))
            ->merge($aftersales->map(fn ($a) => ['Postventa', $a->id, $a->type . ' · ' . $a->status . ' · ' . $a->client_name]))
            ->all());

        $sospechosos = User::query()
            ->where(fn ($q) => $q->where('email', 'like', '%@example.com')->orWhere('email', 'like', '%@test.local')
                ->orWhere('email', 'like', 'test@%')->orWhere('name', 'like', 'Test %'))
            ->get(['id', 'name', 'email', 'role']);
        if ($sospechosos->isNotEmpty()) {
            $this->warn('Cuentas que parecen de prueba (NO se borran; revisarlas a mano):');
            $this->table(['Id', 'Nombre', 'Email', 'Rol'], $sospechosos->map->only(['id', 'name', 'email', 'role'])->all());
        }

        $total = $tasks->count() + $approvals->count() + $aftersales->count();
        if (! $this->option('force')) {
            $this->info("Ensayo: se borrarían {$total} filas. Repite con --force para borrarlas.");
            return self::SUCCESS;
        }

        DB::transaction(function () use ($tasks, $approvals, $aftersales) {
            Task::whereIn('id', $tasks->pluck('id'))->delete();
            Approval::whereIn('id', $approvals->pluck('id'))->delete();
            Aftersale::whereIn('id', $aftersales->pluck('id'))->delete();
        });
        $this->info("Borradas {$total} filas de ejemplo.");

        return self::SUCCESS;
    }
}
