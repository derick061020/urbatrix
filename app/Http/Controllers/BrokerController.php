<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\BrokerMaterial;
use App\Models\Deal;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        return redirect()->route('broker.dashboard');
    }

    /**
     * Comisiones derivadas de los deals del agente (colección compartida por
     * el dashboard, la cartera y el estado de cuenta).
     */
    private function buildCommissions(?Agent $agent, float $rate)
    {
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

        return $deals->map(function (Deal $deal) use ($rate, $statusMap) {
            $base = (float) $deal->deal_price;
            return [
                'client'     => $deal->client_name,
                'unit'       => optional($deal->unit)->custom_id ?? optional($deal->unit)->name ?? '—',
                'project'    => optional(optional($deal->unit)->project)->name,
                'concept'    => $deal->deal_number ? 'Cierre · '.$deal->deal_number : 'Cierre de venta',
                'base'       => $base,
                'commission' => round($base * $rate / 100, 2),
                'date'       => $deal->deal_date,
                'status'     => $statusMap[strtoupper((string) $deal->status)] ?? 'pending',
            ];
        });
    }

    /**
     * Estado de cuenta / Mis comisiones (#13).
     */
    public function comisiones()
    {
        $agent = $this->resolveAgent();
        $rate  = (float) ($agent->commission_rate ?? 0);

        $commissions = $this->buildCommissions($agent, $rate);

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
     * Slug de referido del broker (para el enlace público y la atribución).
     */
    private function referralSlug(?Agent $agent): string
    {
        return $agent ? Str::slug($agent->name) : 'broker';
    }

    /**
     * Dashboard del broker — resumen de actividad, comisiones y progreso.
     */
    public function dashboard()
    {
        $agent = $this->resolveAgent();
        $rate  = (float) ($agent->commission_rate ?? 0);

        $commissions = $this->buildCommissions($agent, $rate);
        $deals = $agent
            ? Deal::with('unit')->where('agent_id', $agent->id)->orderByDesc('deal_date')->get()
            : collect();

        $startMonth = now()->startOfMonth();
        $closedStatuses = ['CLOSED', 'COMPLETED', 'PAID', 'WON'];

        $collectedMonth = $commissions
            ->where('status', 'paid')
            ->filter(fn ($c) => $c['date'] && \Carbon\Carbon::parse($c['date'])->gte($startMonth))
            ->sum('commission');

        $closedDeals = $deals->filter(fn ($d) => in_array(strtoupper((string) $d->status), $closedStatuses));
        $clientsTotal = $deals->pluck('client_email')->filter()->unique()->count() ?: $deals->count();

        $kpis = [
            'collected_month' => $collectedMonth,
            'accumulated'     => $commissions->where('status', 'paid')->sum('commission'),
            'pending'         => $commissions->where('status', 'pending')->sum('commission'),
            'clients'         => $clientsTotal,
            'closed'          => $closedDeals->count(),
            'conversion'      => $deals->count() ? round($closedDeals->count() / $deals->count() * 100) : 0,
        ];

        // Próximas liberaciones (comisiones pendientes con fecha más próxima)
        $upcoming = $commissions->where('status', 'pending')->sortBy('date')->take(4)->values();

        // Meta de trimestre (objetivo simple en nº de ventas cerradas)
        $goalTarget   = 5;
        $goalProgress = $closedDeals->filter(fn ($d) => $d->deal_date && \Carbon\Carbon::parse($d->deal_date)->gte(now()->startOfQuarter()))->count();

        return view('broker.dashboard', [
            'activeRoute'  => 'dashboard',
            'agent'        => $agent,
            'rate'         => $rate,
            'kpis'         => $kpis,
            'upcoming'     => $upcoming,
            'goalTarget'   => $goalTarget,
            'goalProgress' => $goalProgress,
            'referral'     => $this->referralSlug($agent),
            'previewAdmin' => $this->previewAdmin(),
        ]);
    }

    /**
     * Mi cartera de clientes — derivada de los deals del agente.
     */
    public function cartera()
    {
        $agent = $this->resolveAgent();
        $rate  = (float) ($agent->commission_rate ?? 0);

        $deals = $agent
            ? Deal::with('unit')->where('agent_id', $agent->id)->orderByDesc('deal_date')->get()
            : collect();

        $closedStatuses = ['CLOSED', 'COMPLETED', 'PAID', 'WON'];

        $clients = $deals->map(function (Deal $deal) use ($rate, $closedStatuses) {
            $base   = (float) $deal->deal_price;
            $closed = in_array(strtoupper((string) $deal->status), $closedStatuses);
            $st     = strtoupper((string) $deal->status);
            $state  = $closed ? 'cerr' : (in_array($st, ['PENDING', 'IN_PROGRESS']) ? 'neg' : 'lead');
            return [
                'name'       => $deal->client_name,
                'unit'       => optional($deal->unit)->custom_id ?? optional($deal->unit)->name ?? '—',
                'project'    => optional(optional($deal->unit)->project)->name,
                'state'      => $state,
                'commission' => round($base * $rate / 100, 2),
                'closed'     => $closed,
                'date'       => $deal->deal_date,
            ];
        });

        return view('broker.cartera', [
            'activeRoute'  => 'cartera',
            'agent'        => $agent,
            'clients'      => $clients,
            'previewAdmin' => $this->previewAdmin(),
        ]);
    }

    /**
     * Formulario para registrar/atribuirse un cliente.
     */
    public function registro()
    {
        $agent    = $this->resolveAgent();
        $projects = Project::orderBy('name')->get(['id', 'name']);
        $units    = Unit::orderBy('custom_id')->get(['id', 'custom_id', 'name', 'price', 'project_id']);

        return view('broker.registro', [
            'activeRoute'  => 'registro',
            'agent'        => $agent,
            'projects'     => $projects,
            'units'        => $units,
            'referral'     => $this->referralSlug($agent),
            'previewAdmin' => $this->previewAdmin(),
        ]);
    }

    /**
     * Guarda el cliente como un deal/lead atribuido al broker. Verifica
     * duplicados por email/teléfono: si ya existe se bloquea (no se revela
     * a quién está asignado).
     */
    public function registroStore(Request $request)
    {
        $agent = $this->resolveAgent();
        abort_unless($agent, 403, 'Tu usuario aún no está vinculado a un perfil de agente.');

        $data = $request->validate([
            'client_name'  => ['required', 'string', 'max:150'],
            'client_email' => ['required', 'email', 'max:150'],
            'client_phone' => ['required', 'string', 'max:50'],
            'unit_id'      => ['nullable', 'exists:units,id'],
            'stage'        => ['nullable', 'string', 'max:30'],
            'consent'      => ['accepted'],
        ]);

        // Duplicados: si el cliente ya está en la base, se bloquea el registro.
        $exists = Deal::where('client_email', $data['client_email'])
            ->orWhere('client_phone', $data['client_phone'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error',
                'Este cliente ya figura en la base de datos. El registro se bloquea; puedes solicitar revisión al administrador.');
        }

        $unit  = $data['unit_id'] ? Unit::find($data['unit_id']) : null;
        $stage = strtoupper($data['stage'] ?? 'LEAD');

        Deal::create([
            'deal_number' => 'LEAD-'.now()->format('ymd').'-'.Str::upper(Str::random(4)),
            'client_name'  => $data['client_name'],
            'client_email' => $data['client_email'],
            'client_phone' => $data['client_phone'],
            'unit_id'      => $unit?->id,
            'agent_id'     => $agent->id,
            'deal_price'   => $unit?->price ?? 0,
            'status'       => $stage === 'RESERVAR' ? 'PENDING' : 'OPEN',
            'deal_date'    => now(),
            'notes'        => 'Cliente registrado desde el Portal del Broker.',
        ]);

        return redirect()->route('broker.cartera')
            ->with('success', $data['client_name'].' quedó registrado y asignado a ti.');
    }

    /**
     * Inventario en vivo — unidades públicas con su disponibilidad real.
     */
    public function inventario()
    {
        $units = Unit::with('project')
            ->where('public', true)
            ->orderBy('project_id')
            ->orderBy('custom_id')
            ->get();

        return view('broker.inventario', [
            'activeRoute'  => 'inventario',
            'units'        => $units,
            'previewAdmin' => $this->previewAdmin(),
        ]);
    }

    /**
     * Herramientas de venta — material descargable, enlaces y propuestas.
     */
    public function herramientas()
    {
        $agent = $this->resolveAgent();

        $materials = BrokerMaterial::visible()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $clients = $agent
            ? Deal::where('agent_id', $agent->id)->orderByDesc('deal_date')->get(['client_name', 'client_email'])
            : collect();

        $units = Unit::where('public', true)->orderBy('custom_id')->get(['id', 'custom_id', 'name', 'price']);

        return view('broker.herramientas', [
            'activeRoute'  => 'herramientas',
            'materials'    => $materials,
            'clients'      => $clients,
            'units'        => $units,
            'referral'     => $this->referralSlug($agent),
            'previewAdmin' => $this->previewAdmin(),
        ]);
    }

    /**
     * Calculadora de comisión (cálculo en cliente; aquí solo defaults).
     */
    public function calculadora()
    {
        $agent = $this->resolveAgent();

        return view('broker.calculadora', [
            'activeRoute'  => 'calculadora',
            'rate'         => (float) ($agent->commission_rate ?? 7),
            'previewAdmin' => $this->previewAdmin(),
        ]);
    }

    /**
     * Simulador de cobro (fraccionamiento del inicial).
     */
    public function simulador()
    {
        $agent = $this->resolveAgent();

        return view('broker.simulador', [
            'activeRoute'  => 'simulador',
            'rate'         => (float) ($agent->commission_rate ?? 7),
            'previewAdmin' => $this->previewAdmin(),
        ]);
    }

    /**
     * Metas e incentivos — progreso del trimestre, nivel y ranking.
     */
    public function metas()
    {
        $agent = $this->resolveAgent();

        $deals = $agent
            ? Deal::where('agent_id', $agent->id)->get()
            : collect();
        $closedStatuses = ['CLOSED', 'COMPLETED', 'PAID', 'WON'];
        $closedThisQuarter = $deals->filter(fn ($d) =>
            in_array(strtoupper((string) $d->status), $closedStatuses)
            && $d->deal_date && \Carbon\Carbon::parse($d->deal_date)->gte(now()->startOfQuarter())
        )->count();

        // Ranking por cierres del trimestre entre todos los agentes
        $leaderboard = Agent::with(['deals'])->get()->map(function (Agent $a) use ($closedStatuses) {
            $count = $a->deals->filter(fn ($d) =>
                in_array(strtoupper((string) $d->status), $closedStatuses)
                && $d->deal_date && \Carbon\Carbon::parse($d->deal_date)->gte(now()->startOfQuarter())
            )->count();
            return ['name' => $a->name, 'id' => $a->id, 'sales' => $count];
        })->sortByDesc('sales')->values();

        return view('broker.metas', [
            'activeRoute'   => 'metas',
            'agent'         => $agent,
            'goalTarget'    => 5,
            'goalProgress'  => $closedThisQuarter,
            'levelTarget'   => 8,
            'leaderboard'   => $leaderboard,
            'previewAdmin'  => $this->previewAdmin(),
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

        // Documentos generales de contrato (materiales categoría "Contrato"/"Legal")
        $contractDocs = BrokerMaterial::visible()
            ->whereIn('category', ['Contrato', 'Legal', 'Anexo'])
            ->orderBy('sort_order')
            ->get();

        // Contratos propios subidos por el admin a este broker
        $user = Auth::user();
        $brokerUser = $user->role === 'broker'
            ? $user
            : User::where('email', $agent?->email)->where('role', 'broker')->first();
        $brokerDocs = $brokerUser ? $brokerUser->brokerDocuments : collect();

        return view('broker.contrato', [
            'activeRoute'  => 'contrato',
            'agent'        => $agent,
            'rate'         => $rate,
            'terms'        => $terms,
            'contractDocs' => $contractDocs,
            'brokerDocs'   => $brokerDocs,
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

    /**
     * Descarga de un documento/contrato propio del broker. Lo puede bajar
     * el admin o el broker dueño del documento.
     */
    public function downloadDocument(\App\Models\BrokerDocument $document)
    {
        $user = Auth::user();
        abort_unless($user->is_admin || $document->user_id === $user->id, 403);

        abort_unless($document->file_path && \Storage::disk('public')->exists($document->file_path), 404);

        $document->increment('downloads');

        return \Storage::disk('public')->download(
            $document->file_path,
            $document->title . '.' . strtolower($document->format)
        );
    }
}
