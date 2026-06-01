<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\BrokerMaterial;
use App\Models\Deal;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class BrokerController extends Controller
{
    /**
     * Resuelve el Agent ligado al usuario broker logueado (por email).
     * El admin en vista previa cae al primer agente disponible.
     */
    private function resolveAgent(): ?Agent
    {
        $user = Auth::user();
        $agent = Agent::where('email', $user->email)->first();

        if (! $agent && $user->is_admin) {
            $agent = Agent::query()->first();
        }

        return $agent;
    }

    private function previewAdmin(): bool
    {
        $user = Auth::user();
        return $user->is_admin && $user->role !== 'broker';
    }

    public function index()
    {
        return redirect()->route('broker.comisiones');
    }

    /**
     * Mis comisiones (#13) — derivadas de los deals del agente.
     */
    public function comisiones()
    {
        $agent = $this->resolveAgent();
        $rate  = (float) ($agent->commission_rate ?? 0);

        $deals = $agent
            ? Deal::with('unit')->where('agent_id', $agent->id)->orderByDesc('deal_date')->get()
            : collect();

        // Mapa estado del deal -> estado de comisión
        $statusMap = [
            'CLOSED'    => 'paid',    'COMPLETED' => 'paid', 'PAID' => 'paid', 'WON' => 'paid',
            'PENDING'   => 'pending', 'OPEN'      => 'pending', 'IN_PROGRESS' => 'pending',
            'OVERDUE'   => 'overdue', 'LATE'      => 'overdue',
            'CANCELLED' => 'overdue', 'LOST'      => 'overdue',
        ];

        $commissions = $deals->map(function (Deal $deal) use ($rate, $statusMap) {
            $base = (float) $deal->deal_price;
            return [
                'client'     => $deal->client_name,
                'unit'       => optional($deal->unit)->custom_id ?? optional($deal->unit)->name ?? '—',
                'concept'    => $deal->deal_number ? 'Cierre · '.$deal->deal_number : 'Cierre de venta',
                'base'       => $base,
                'commission' => round($base * $rate / 100, 2),
                'date'       => $deal->deal_date,
                'status'     => $statusMap[strtoupper((string) $deal->status)] ?? 'pending',
            ];
        });

        $sumBy = fn ($s) => $commissions->where('status', $s)->sum('commission');

        $kpis = [
            'paid'    => ['total' => $sumBy('paid'),    'count' => $commissions->where('status', 'paid')->count()],
            'pending' => ['total' => $sumBy('pending'), 'count' => $commissions->where('status', 'pending')->count()],
            'overdue' => ['total' => $sumBy('overdue'), 'count' => $commissions->where('status', 'overdue')->count()],
            'total'   => ['total' => $commissions->sum('commission'), 'count' => $commissions->count()],
        ];

        return view('broker.comisiones', [
            'activeRoute'  => 'comisiones',
            'agent'        => $agent,
            'rate'         => $rate,
            'commissions'  => $commissions,
            'kpis'         => $kpis,
            'previewAdmin' => $this->previewAdmin(),
        ]);
    }

    /**
     * Mi contrato (#14) — términos, documentos y ejecutivo de cuenta.
     */
    public function contrato()
    {
        $agent = $this->resolveAgent();
        $rate  = (float) ($agent->commission_rate ?? 0);

        $projects = Project::orderBy('name')->pluck('name')->implode(', ');

        $terms = [
            ['Renovación',          'Automática anual · aviso 30 días'],
            ['Frecuencia de pago',  'Mensual · día 15 de cada mes'],
            ['Proyectos incluidos', $projects ?: config('company.project')],
            ['Exclusividad',        'No exclusivo · mercado internacional'],
            ['Materiales de venta', 'Acceso a brochures y renders digitales'],
            ['Tasa de comisión',    $rate.'% sobre el valor de cada cierre'],
        ];

        // Documentos de contrato disponibles (materiales categoría "Contrato"/"Legal")
        $contractDocs = BrokerMaterial::visible()
            ->whereIn('category', ['Contrato', 'Legal', 'Anexo'])
            ->orderBy('sort_order')
            ->get();

        return view('broker.contrato', [
            'activeRoute'  => 'contrato',
            'agent'        => $agent,
            'rate'         => $rate,
            'terms'        => $terms,
            'contractDocs' => $contractDocs,
            'previewAdmin' => $this->previewAdmin(),
        ]);
    }

    /**
     * Material de ventas (#15) — recursos visibles aprobados por Duna.
     */
    public function material()
    {
        $materials = BrokerMaterial::visible()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return view('broker.material', [
            'activeRoute'  => 'material',
            'materials'    => $materials,
            'previewAdmin' => $this->previewAdmin(),
        ]);
    }

    /**
     * Descarga de material (broker o admin). Cuenta la descarga.
     */
    public function download(BrokerMaterial $material)
    {
        abort_unless($material->visible || Auth::user()->is_admin, 403);

        if ($material->external_url) {
            return redirect($material->external_url);
        }

        abort_unless($material->file_path && \Storage::disk('public')->exists($material->file_path), 404);

        $material->increment('downloads');

        return \Storage::disk('public')->download($material->file_path, $material->title . '.' . strtolower($material->format));
    }
}
