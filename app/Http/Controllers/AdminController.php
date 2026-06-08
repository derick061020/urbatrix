<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitImage;
use App\Models\Agent;
use App\Models\BrokerDocument;
use App\Models\Deal;
use App\Models\Reservation;
use App\Models\Document;
use App\Models\Project;
use App\Models\Task;
use App\Models\Approval;
use App\Models\Aftersale;
use App\Models\Payment;
use App\Models\User;
use App\Models\CrmTemplate;
use App\Models\CrmAutomation;
use App\Models\CrmChannelSetting;
use App\Models\ExportAuthorization;
use App\Helpers\PaymentPlanHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (Auth::check() && Auth::user()->role === 'broker') {
            return $this->crmDashboard();
        }

        $stats = [
            'total_units' => Unit::count(),
            'available_units' => Unit::where('status', 'AVAILABLE')->count(),
            'total_agents' => Agent::where('active', true)->count(),
            'total_deals' => Deal::count(),
            'pending_deals' => Deal::where('status', 'PENDING')->count(),
            'completed_deals' => Deal::where('status', 'COMPLETED')->count(),
        ];

        $recentDeals = Deal::with(['unit', 'agent'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentDeals'));
    }

    public function units()
    {
        Unit::releaseExpiredHolds();
        $units = Unit::orderBy('created_at', 'desc')->get();
        return view('admin.units.units', compact('units'));
    }

    public function editUnit(Unit $unit)
    {
        $unit->load(['images', 'histories', 'dealHistories', 'paymentHistories']);
        $agents = Agent::where('active', true)->orderBy('name')->get();
        $recentViews = \App\Models\UnitView::with('user')
            ->where('unit_id', $unit->id)
            ->orderByDesc('viewed_at')
            ->limit(25)
            ->get();
        $viewStats = [
            'today'  => \App\Models\UnitView::where('unit_id', $unit->id)->whereDate('viewed_at', today())->count(),
            'week'   => \App\Models\UnitView::where('unit_id', $unit->id)->where('viewed_at', '>=', now()->subDays(7))->count(),
            'month'  => \App\Models\UnitView::where('unit_id', $unit->id)->where('viewed_at', '>=', now()->subDays(30))->count(),
            'total'  => \App\Models\UnitView::where('unit_id', $unit->id)->count(),
        ];
        return view('admin.units.edit', compact('unit', 'agents', 'recentViews', 'viewStats'));
    }

    public function createUnit()
    {
        $agents = Agent::where('active', true)->orderBy('name')->get();
        return view('admin.units.create', compact('agents'));
    }

    public function storeUnit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'type'   => 'required|string|max:50',
            'price'  => 'required|numeric|min:0',
            'status' => 'required|in:AVAILABLE,SOLD,PENDING,RESERVED,HELD',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $unit = Unit::create($request->except(['_token', '_method']));

        return redirect()->route('admin.units.edit', $unit->id)
            ->with('success', 'Unidad creada. Ahora podés subir imágenes y completar el resto de la información.');
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        $booleanFields = [
            'public', 'pre_arranged', 'plot', 'guaranteed_rental', 'override_action',
            'aircon', 'bypass_launch_date', 'display_on_home_page', 'show_enquire_button',
            'set_discount_globally', 'hide_original_price', 'show_price_alternative',
            'is_high_demand', 'is_second_chance',
        ];

        foreach ($booleanFields as $field) {
            $request->merge([$field => $request->boolean($field)]);
        }

        $validated = $request->validate([
            // Existing
            'name'                  => 'required|string|max:255',
            'status'                => 'required|in:AVAILABLE,PENDING,RESERVED,HELD,SOLD',
            'type'                  => 'nullable|string|max:50',
            'price'                 => 'nullable|numeric|min:0',
            'public'                => 'boolean',
            'pre_arranged'          => 'boolean',
            'description'           => 'nullable|string',

            // Reservation Details
            'discount'              => 'nullable|numeric',
            'additional_parking'    => 'nullable|integer|min:0',
            'price_adjustment'      => 'nullable|numeric',
            'purchase_price'        => 'nullable|numeric|min:0',

            // Reservation Customer
            'first_name'            => 'nullable|string|max:255',
            'last_name'             => 'nullable|string|max:255',
            'contact_number'        => 'nullable|string|max:50',
            'email'                 => 'nullable|email|max:255',

            // Agent
            'agent_id'              => 'nullable|exists:agents,id',

            // Unit General
            'plot'                  => 'boolean',
            'address'               => 'nullable|string|max:255',
            'custom_id'             => 'nullable|string|max:100',
            'price_wording'         => 'nullable|string|max:255',
            'levies'                => 'nullable|numeric|min:0',
            'rates'                 => 'nullable|numeric|min:0',
            'est_rental'            => 'nullable|numeric|min:0',
            'guaranteed_rental'     => 'boolean',
            'override_action'       => 'boolean',

            // Unit Specifications
            'floor'                 => 'nullable|string|max:50',
            'layout'                => 'nullable|string|max:100',
            'bedrooms'              => 'nullable|integer|min:0',
            'bathrooms'             => 'nullable|numeric|min:0',
            'parking_bays'          => 'nullable|integer|min:0',
            'pools'                 => 'nullable|integer|min:0',
            'direction'             => 'nullable|string|max:10',
            'outlook'               => 'nullable|string|max:50',
            'aircon'                => 'boolean',

            // Unit Monthly Expenses
            'expense_1'             => 'nullable|numeric|min:0',
            'expense_2'             => 'nullable|numeric|min:0',
            'expense_3'             => 'nullable|numeric|min:0',

            // Unit Custom Information
            'custom_1'              => 'nullable|string|max:255',
            'custom_2'              => 'nullable|string|max:255',
            'custom_3'              => 'nullable|string|max:255',

            // Unit Dimensions
            'internal_area'         => 'nullable|numeric|min:0',
            'external_area'         => 'nullable|numeric|min:0',
            'total_area'            => 'nullable|numeric|min:0',

            // Unit Settings
            'bypass_launch_date'    => 'boolean',
            'display_on_home_page'  => 'boolean',
            'show_enquire_button'   => 'boolean',
            'set_discount_globally' => 'boolean',
            'hide_original_price'   => 'boolean',
            'show_price_alternative'=> 'boolean',

            // Availability & demand
            'reserved_until'        => 'nullable|date',
            'released_at'           => 'nullable|date',
            'views_today'           => 'nullable|integer|min:0',
            'is_high_demand'        => 'boolean',
            'is_second_chance'      => 'boolean',

            // For Investment / For Living content
            'for_investment_text'   => 'nullable|string|max:5000',
            'for_living_text'       => 'nullable|string|max:5000',
            'projected_value'       => 'nullable|numeric|min:0',
            'projected_value_year'  => 'nullable|string|max:10',
            'roi_percent'           => 'nullable|numeric|min:0|max:999',
            'comparison_text'       => 'nullable|string|max:500',
            'amenities'             => 'nullable|array',
            'amenities.*'           => 'string|in:pool,gym,beach_club,restaurant,spa,tennis,golf,security,parking,concierge,playground,bbq',
            'amenities_text'        => 'nullable|string|max:500',
            'walk_score'            => 'nullable|integer|min:0|max:100',
            'school_proximity'      => 'nullable|string|max:255',
        ]);

        if (array_key_exists('agent_id', $validated) && $validated['agent_id'] === '') {
            $validated['agent_id'] = null;
        }

        $unit->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Unit updated successfully!',
                'saved_at' => now()->format('H:i:s'),
            ]);
        }

        return redirect()->route('admin.units.edit', $unit->id)
            ->with('success', 'Unit updated successfully!');
    }

    public function deleteUnit(Unit $unit)
    {
        $unit->delete();
        return redirect()->route('admin.units')
            ->with('success', 'Unit deleted successfully!');
    }

    public function togglePublicUnit(Unit $unit)
    {
        $unit->update(['public' => ! (bool) $unit->public]);
        return response()->json(['ok' => true, 'public' => (bool) $unit->public]);
    }

    public function deleteUnitImage(Unit $unit, UnitImage $image)
    {
        abort_if($image->unit_id !== $unit->id, 404);
        $image->delete();
        return response()->json(['ok' => true]);
    }

    public function reorderUnitImages(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:unit_images,id',
        ]);

        DB::transaction(function () use ($data, $unit) {
            foreach ($data['order'] as $position => $id) {
                UnitImage::where('id', $id)
                    ->where('unit_id', $unit->id)
                    ->update(['sort_order' => $position + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function uploadUnitImages(Request $request, Unit $unit)
    {
        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120'
        ]);

        $uploadedImages = [];

        DB::transaction(function () use ($request, $unit, &$uploadedImages) {
            $maxSortOrder = $unit->images()->max('sort_order') ?? 0;

            foreach ($request->file('images') as $index => $image) {
                if ($image->isValid()) {
                    $filename = time() . '_' . $index . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('units/images', $filename, 'public');

                    $unitImage = UnitImage::create([
                        'unit_id' => $unit->id,
                        'name' => $image->getClientOriginalName(),
                        'path' => '/storage/' . $path,
                        'sort_order' => $maxSortOrder + $index + 1
                    ]);

                    $uploadedImages[] = [
                        'id' => $unitImage->id,
                        'name' => $unitImage->name,
                        'path' => $unitImage->path,
                        'sort_order' => $unitImage->sort_order
                    ];
                }
            }
        });

        return response()->json([
            'success' => true,
            'images' => $uploadedImages
        ]);
    }

    public function agents()
    {
        $brokers = User::where('role', 'broker')
            ->with(['assignedUnits:id,custom_id,name,price,status', 'brokerDocuments'])
            ->orderBy('created_at', 'desc')
            ->get();
        $units = Unit::orderBy('custom_id')->get(['id', 'custom_id', 'name', 'status']);

        // Tasa de comisión por broker (vive en el Agent vinculado por email)
        $rates = Agent::whereIn('email', $brokers->pluck('email'))->pluck('commission_rate', 'email');

        return view('admin.agents', compact('brokers', 'units', 'rates'));
    }

    public function storeAgent(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'phone'    => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8',
            'active'   => 'nullable|boolean',
            'unit_ids' => 'nullable|array',
            'unit_ids.*' => 'integer|exists:units,id',
        ]);

        $parts = preg_split('/\s+/', trim($data['name']), 2);
        $tempPassword = $data['password'] ?? \Illuminate\Support\Str::random(10);

        $user = User::create([
            'name'       => $data['name'],
            'first_name' => $parts[0] ?? '',
            'last_name'  => $parts[1] ?? '',
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'password'   => Hash::make($tempPassword),
            'role'       => 'broker',
            'verification_status' => ($data['active'] ?? true) ? 'approved' : 'pending',
        ]);

        if (! empty($data['unit_ids'])) {
            $user->assignedUnits()->sync($data['unit_ids']);
        }

        return redirect()->route('admin.agents')
            ->with('success', "Broker creado. Contraseña temporal: {$tempPassword}");
    }

    public function updateAgent(Request $request, $agent)
    {
        $broker = User::where('role', 'broker')->findOrFail($agent);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($broker->id)],
            'phone'    => 'nullable|string|max:30',
            'active'   => 'nullable|boolean',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'unit_ids' => 'nullable|array',
            'unit_ids.*' => 'integer|exists:units,id',
        ]);

        $parts = preg_split('/\s+/', trim($data['name']), 2);
        $oldEmail = $broker->email;

        $broker->update([
            'name'       => $data['name'],
            'first_name' => $parts[0] ?? '',
            'last_name'  => $parts[1] ?? '',
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'verification_status' => ($data['active'] ?? true) ? 'approved' : 'pending',
        ]);

        if ($request->has('unit_ids')) {
            $broker->assignedUnits()->sync($data['unit_ids'] ?? []);
        }

        // Tasa de comisión: vive en el Agent vinculado por email.
        if ($request->filled('commission_rate')) {
            $agent = Agent::where('email', $oldEmail)->orWhere('email', $data['email'])->first();
            if ($agent) {
                $agent->update(['email' => $data['email'], 'name' => $data['name'], 'commission_rate' => $data['commission_rate']]);
            } else {
                Agent::create([
                    'name'            => $data['name'],
                    'email'           => $data['email'],
                    'phone'           => $data['phone'] ?? null,
                    'commission_rate' => $data['commission_rate'],
                    'active'          => true,
                ]);
            }
        }

        return redirect()->route('admin.agents')
            ->with('success', 'Broker actualizado.');
    }

    public function deleteAgent($agent)
    {
        $broker = User::where('role', 'broker')->findOrFail($agent);
        $broker->delete();
        return redirect()->route('admin.agents')
            ->with('success', 'Broker eliminado.');
    }

    public function assignBrokerUnits(Request $request, $agent)
    {
        $broker = User::where('role', 'broker')->findOrFail($agent);
        $data = $request->validate([
            'unit_ids' => 'nullable|array',
            'unit_ids.*' => 'integer|exists:units,id',
        ]);

        $broker->assignedUnits()->sync($data['unit_ids'] ?? []);

        return redirect()->route('admin.agents')
            ->with('success', "Unidades asignadas a {$broker->name}.");
    }

    public function storeBrokerDocument(Request $request, $agent)
    {
        $broker = User::where('role', 'broker')->findOrFail($agent);

        $data = $request->validate([
            'title'    => 'required|string|max:160',
            'category' => 'nullable|string|max:40',
            'file'     => 'required|file|max:51200', // 50 MB
        ]);

        $file = $request->file('file');
        $bytes = $file->getSize();
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;

        $broker->brokerDocuments()->create([
            'title'      => $data['title'],
            'category'   => $data['category'] ?: 'Contrato',
            'format'     => strtoupper($file->getClientOriginalExtension()),
            'file_path'  => $file->store('broker-documents', 'public'),
            'file_size'  => round($bytes / (1024 ** $i), 1) . ' ' . $units[$i],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.agents')
            ->with('success', "Documento agregado a {$broker->name}.");
    }

    public function destroyBrokerDocument($agent, $document)
    {
        $broker = User::where('role', 'broker')->findOrFail($agent);
        $doc = $broker->brokerDocuments()->findOrFail($document);

        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();

        return redirect()->route('admin.agents')
            ->with('success', 'Documento eliminado.');
    }

    public function deals()
    {
        $deals = Deal::with(['unit', 'agent'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.deals', compact('deals'));
    }

    public function storeDeal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_phone' => 'nullable|string|max:20',
            'unit_id' => 'required|exists:units,id',
            'agent_id' => 'required|exists:agents,id',
            'deal_price' => 'required|numeric|min:0',
            'status' => 'required|in:PENDING,COMPLETED,CANCELLED',
            'deal_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dealData = $request->all();
        $dealData['deal_number'] = 'DEAL-' . strtoupper(uniqid());

        Deal::create($dealData);

        return redirect()->route('admin.deals')
            ->with('success', 'Deal created successfully!');
    }

    public function updateDeal(Request $request, Deal $deal)
    {
        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_phone' => 'nullable|string|max:20',
            'unit_id' => 'required|exists:units,id',
            'agent_id' => 'required|exists:agents,id',
            'deal_price' => 'required|numeric|min:0',
            'status' => 'required|in:PENDING,COMPLETED,CANCELLED',
            'deal_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $deal->update($request->all());

        return redirect()->route('admin.deals')
            ->with('success', 'Deal updated successfully!');
    }

    public function deleteDeal(Deal $deal)
    {
        $deal->delete();
        return redirect()->route('admin.deals')
            ->with('success', 'Deal deleted successfully!');
    }

    public function profiles()
    {
        return view('admin.profiles');
    }

    /**
     * Detalle de usuario para el modal de Usuarios (#3 del briefing):
     * Información, Propiedad, Documentos y Actividad — datos reales.
     * Devuelve el partial renderizado (se inyecta vía fetch).
     */
    public function userDetail($userId)
    {
        $user = User::findOrFail($userId);

        $reservation = Reservation::with(['unit.project', 'documents', 'payments'])
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $unit  = $reservation?->unit;
        $price = (float) ($unit->price ?? $reservation->unit_price ?? 0);
        $paid  = $reservation ? (float) $reservation->payments->where('status', 'paid')->sum('amount') : 0;
        $pct   = $price > 0 ? min(100, round($paid / $price * 100)) : 0;

        // ── Actividad real (user_activities + unit_views + last_seen) ──
        $startMonth = now()->startOfMonth();

        $sessionsThisMonth = \App\Models\UserActivity::where('user_id', $user->id)
            ->where('type', 'login')->where('created_at', '>=', $startMonth)->count();

        $avgSeconds = (int) \App\Models\UserActivity::where('user_id', $user->id)
            ->where('type', 'login')->whereNotNull('duration_seconds')
            ->where('created_at', '>=', $startMonth)->avg('duration_seconds');
        $avgSession = $avgSeconds > 0 ? sprintf('%dm %02ds', intdiv($avgSeconds, 60), $avgSeconds % 60) : '—';

        $docsViewed = \App\Models\UserActivity::where('user_id', $user->id)
            ->whereIn('type', ['document_view', 'document_download'])
            ->where('created_at', '>=', $startMonth)->count();

        $distinctUnits = \App\Models\UnitView::where('user_id', $user->id)->distinct('unit_id')->count('unit_id');

        $topViewed = \App\Models\UnitView::with('unit')
            ->where('user_id', $user->id)
            ->selectRaw('unit_id, COUNT(*) as total, MAX(viewed_at) as last_viewed')
            ->groupBy('unit_id')
            ->orderByDesc('total')
            ->take(4)
            ->get();

        $recentActions = \App\Models\UserActivity::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        $activityCount = \App\Models\UserActivity::where('user_id', $user->id)->where('created_at', '>=', $startMonth)->count();
        $platform = $activityCount >= 20 ? ['Actividad alta', 'ok']
                  : ($activityCount >= 8 ? ['Actividad media', 'warn'] : ['Actividad baja', 'info']);

        $docsCount = $reservation ? $reservation->documents->count() : 0;

        // ── Estado / etapa del proceso ──
        $verif = $user->verification_status ?? 'approved';
        $approvedDocs = $reservation ? $reservation->documents->where('status', 'approved')->count() : 0;
        if (! $reservation) {
            $stage = ['1 / 6', 'Registro']; $estado = ['Sin unidad', 'info'];
        } elseif ($verif === 'pending') {
            $stage = ['2 / 6', 'KYC / Docs']; $estado = ['KYC pendiente', 'warn'];
        } elseif ($verif === 'rejected') {
            $stage = ['2 / 6', 'KYC / Docs']; $estado = ['Rechazado', 'err'];
        } elseif ($docsCount === 0) {
            $stage = ['3 / 6', 'Documentación']; $estado = ['Documentación', 'warn'];
        } elseif ($reservation->budget_status === 'sent') {
            $stage = ['4 / 6', 'Presupuesto']; $estado = ['Presupuesto enviado', 'info'];
        } elseif ($approvedDocs > 0 && $approvedDocs < $docsCount) {
            $stage = ['5 / 6', 'Contrato']; $estado = ['En revisión', 'info'];
        } elseif ($docsCount > 0 && $approvedDocs === $docsCount) {
            $stage = ['6 / 6', 'Entrega']; $estado = ['Al día', 'ok'];
        } else {
            $stage = ['3 / 6', 'Documentación']; $estado = ['En revisión', 'info'];
        }

        $alerts = [];
        if ($verif === 'pending') { $alerts[] = 'KYC'; }
        if ($reservation && $docsCount === 0) { $alerts[] = 'DOCS'; }

        $html = view('admin._partials.user_detail', compact(
            'user', 'reservation', 'unit', 'price', 'paid', 'pct',
            'sessionsThisMonth', 'avgSession', 'docsViewed', 'distinctUnits',
            'topViewed', 'recentActions', 'docsCount', 'platform', 'stage', 'estado', 'alerts'
        ))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Estadísticas de plataforma (#2 del briefing) — conectadas a datos reales:
     * usuarios activos, visitas (unit_views), propiedades más vistas y distribución por país.
     */
    public function estadisticas()
    {
        $now = now();

        $activeUsers  = User::where('last_seen', '>=', $now->copy()->subMinutes(5))->count();
        $totalUsers   = User::where('role', 'user')->count();
        $newThisMonth = User::where('role', 'user')->where('created_at', '>=', $now->copy()->startOfMonth())->count();

        $viewsTotal     = (int) \App\Models\UnitView::count();
        $viewsThisMonth = (int) \App\Models\UnitView::where('created_at', '>=', $now->copy()->startOfMonth())->count();

        $popularUnits = Unit::orderByDesc('views_total')->take(7)->get(['id', 'custom_id', 'name', 'views_total', 'project_id']);

        $recentUsers = User::where('role', 'user')
            ->whereNotNull('last_seen')
            ->orderByDesc('last_seen')
            ->take(6)
            ->get(['id', 'name', 'email', 'last_seen', 'country']);

        // Distribución por país (de las reservas con país definido)
        $byCountry = Reservation::selectRaw('country, COUNT(*) as total')
            ->whereNotNull('country')->where('country', '!=', '')
            ->groupBy('country')->orderByDesc('total')->take(7)->get();
        $byCountryTotal = max(1, $byCountry->sum('total'));

        // Tendencia de registros (últimos 6 meses)
        $trend = collect(range(5, 0))->map(function ($i) use ($now) {
            $month = $now->copy()->subMonths($i);
            return [
                'label' => $month->translatedFormat('M'),
                'count' => User::whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->count(),
            ];
        });

        return view('admin.estadisticas', compact(
            'activeUsers', 'totalUsers', 'newThisMonth',
            'viewsTotal', 'viewsThisMonth', 'popularUnits',
            'recentUsers', 'byCountry', 'byCountryTotal', 'trend'
        ));
    }

    public function transactionsReport()
    {
        $dealsQuery = Deal::with(['unit', 'agent']);
        $totalQuery = Deal::where('status', 'COMPLETED');
        $pendingQuery = Deal::where('status', 'PENDING');

        if (Auth::user()->role === 'broker') {
            $unitIds = Auth::user()->assignedUnits()->pluck('units.id')->all();
            $dealsQuery->whereIn('unit_id', $unitIds);
            $totalQuery->whereIn('unit_id', $unitIds);
            $pendingQuery->whereIn('unit_id', $unitIds);
        }

        $deals = $dealsQuery->orderBy('deal_date', 'desc')->get();
        $totalRevenue = $totalQuery->sum('deal_price');
        $pendingRevenue = $pendingQuery->sum('deal_price');

        return view('admin.transactions-report', compact('deals', 'totalRevenue', 'pendingRevenue'));
    }

    public function communication()
    {
        return view('admin.communication');
    }

    public function communicationConversation($id)
    {
        $active = \App\Models\Reservation::with(['unit', 'documents', 'payments'])->findOrFail($id);
        $threadMessages = $active->messages()->with('sender')->get();

        $active->messages()
            ->where('sender_role', 'client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $avBg = ['#7cb8e7','#f3b04f','#a5b0c5','#d6a3c6','#d56a6a','#cdd6df','#a6c5b3'];

        return response()->json([
            'id'     => $active->id,
            'thread' => view('admin._partials.communication_thread', compact('active', 'threadMessages', 'avBg'))->render(),
            'rail'   => view('admin._partials.communication_rail', compact('active'))->render(),
        ]);
    }

    public function extras()
    {
        return view('admin.extras');
    }

    public function dataExport()
    {
        return view('admin.data-export');
    }

    public function emailTemplates()
    {
        return view('admin.email-templates');
    }

    public function registrationFields()
    {
        return view('admin.registration-fields');
    }

    public function menu()
    {
        return view('admin.menu');
    }

    public function landing()
    {
        return view('admin.landing');
    }

    public function socialChat()
    {
        return view('admin.social-chat');
    }

    public function survey()
    {
        return view('admin.survey');
    }

    public function ctaCards()
    {
        return view('admin.cta-cards');
    }

    public function theme()
    {
        return view('admin.theme');
    }

    public function account()
    {
        return view('admin.account');
    }

    /* ───── CRM Operativo ───── */

    public function crmDashboard()
    {
        // Auto-mark overdue tasks
        Task::where('status', '!=', 'completada')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->update(['status' => 'vencida']);

        $isBroker = Auth::user()->role === 'broker';
        $brokerUnitIds = $isBroker
            ? Auth::user()->assignedUnits()->pluck('units.id')->map(fn($i) => (string) $i)->all()
            : [];

        $reservationsBase = Reservation::query();
        if ($isBroker) $reservationsBase->whereIn('unit_id', $brokerUnitIds);

        $docsBase = Document::query();
        if ($isBroker) {
            $docsBase->whereHas('reservation', fn($q) => $q->whereIn('unit_id', $brokerUnitIds));
        }

        $pendingProfilesCount = (!$isBroker && \Illuminate\Support\Facades\Schema::hasColumn('users', 'verification_status'))
            ? User::where('verification_status', 'pending')->count()
            : 0;

        $stats = [
            'expedientes_activos'     => (clone $reservationsBase)->count(),
            'expedientes_incompletos' => (clone $reservationsBase)
                ->whereDoesntHave('documents', fn($q) => $q->where('status', 'approved'))->count(),
            'docs_pendientes'         => (clone $docsBase)->whereIn('status', ['pending', 'generated'])->count(),
            'docs_rechazados'         => (clone $docsBase)->where('status', 'rejected')->count(),
            'aprobaciones_cola'       => $isBroker ? 0 : Approval::where('status', 'pendiente')->count(),
            'aprobaciones_alta'       => $isBroker ? 0 : Approval::where('status', 'pendiente')->where('priority', 'alta')->count(),
            'tareas_vencidas'         => $isBroker ? 0 : Task::where('status', 'vencida')->count(),
            'tareas_hoy'              => $isBroker ? 0 : Task::whereDate('due_date', today())->where('status', '!=', 'completada')->count(),
            'perfiles_pendientes'     => $pendingProfilesCount,
        ];

        $proyectos = Project::all();

        $expedientesRecientes = (clone $reservationsBase)
            ->with(['unit', 'documents'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $aprobacionesUrgentes = $isBroker ? collect() : Approval::where('status', 'pendiente')
            ->where('priority', 'alta')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $tareasHoy = $isBroker ? collect() : Task::with('reservation')
            ->where('status', '!=', 'completada')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [today()->subDay(), today()->addDay()])
            ->orderBy('due_date')
            ->get();

        return view('admin.crm.dashboard', compact('stats', 'proyectos', 'expedientesRecientes', 'aprobacionesUrgentes', 'tareasHoy'));
    }

    public function crmExpedientes(Request $request)
    {
        $search = $request->get('search');
        $query = Reservation::with(['unit', 'documents', 'payments']);

        if (Auth::user()->role === 'broker') {
            $unitIds = Auth::user()->assignedUnits()->pluck('units.id')->map(fn($id) => (string) $id)->all();
            $query->whereIn('unit_id', $unitIds);
        }

        $reservations = $query
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('reservation_code', 'like', "%$search%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $units   = Unit::orderBy('custom_id')->get();
        $clients = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.crm.expedientes', compact('reservations', 'search', 'units', 'clients'));
    }

    public function crmDocumentos(Request $request)
    {
        $tab = $request->get('estado', 'todos');
        $query = Document::with('reservation');
        if ($tab !== 'todos') {
            $query->where('status', $tab);
        }
        if (Auth::user()->role === 'broker') {
            $unitIds = Auth::user()->assignedUnits()->pluck('units.id')->map(fn($id) => (string) $id)->all();
            $query->whereHas('reservation', function ($q) use ($unitIds) {
                $q->whereIn('unit_id', $unitIds);
            });
        }
        $documents = $query->orderBy('updated_at', 'desc')->paginate(30);

        $tabs = ['todos', 'pending', 'generated', 'signed', 'approved', 'rejected'];

        if ($request->ajax() || $request->wantsJson()) {
            return view('admin.crm._partials.documentos_list', compact('documents', 'tab', 'tabs'));
        }

        return view('admin.crm.documentos', compact('documents', 'tab', 'tabs'));
    }

    public function crmContratos()
    {
        $query = Reservation::with(['unit', 'documents']);
        if (Auth::user()->role === 'broker') {
            $unitIds = Auth::user()->assignedUnits()->pluck('units.id')->map(fn($id) => (string) $id)->all();
            $query->whereIn('unit_id', $unitIds);
        }
        $reservations = $query->orderBy('created_at', 'desc')->take(50)->get();

        $units   = Unit::orderBy('custom_id')->get();
        $clients = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.crm.contratos', compact('reservations', 'units', 'clients'));
    }

    public function crmProyectos()
    {
        $proyectos = Project::withCount([
            'units',
            'units as sold_count'      => fn($q) => $q->where('status', 'SOLD'),
            'units as reserved_count'  => fn($q) => $q->where('status', 'RESERVED'),
            'units as available_count' => fn($q) => $q->where('status', 'AVAILABLE'),
        ])
            // Active projects (have units) first, then alphabetical.
            ->orderByRaw('CASE WHEN (SELECT COUNT(*) FROM units WHERE units.project_id = projects.id) > 0 THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->get();

        $selected = Project::withCount([
            'units',
            'units as sold_count'      => fn($q) => $q->where('status', 'SOLD'),
            'units as reserved_count'  => fn($q) => $q->where('status', 'RESERVED'),
            'units as available_count' => fn($q) => $q->where('status', 'AVAILABLE'),
        ])->where('id', request('project_id', $proyectos->first()?->id))->first();

        $units = $selected ? $selected->units()->orderBy('custom_id')->orderBy('id')->get(['id', 'custom_id', 'name', 'status']) : collect();

        return view('admin.crm.proyectos', compact('proyectos', 'selected', 'units'));
    }

    public function crmPostventa()
    {
        $items = Aftersale::with(['reservation', 'unit'])
            ->orderBy('scheduled_date', 'desc')
            ->get();

        $stats = [
            'programadas' => Aftersale::where('status', 'programada')->count(),
            'garantias'   => Aftersale::where('type', 'Garantía')->whereIn('status', ['en_atencion'])->count(),
            'tramite'     => Aftersale::where('status', 'en_tramite')->count(),
            'resueltas'   => Aftersale::where('status', 'resuelta')
                                ->whereMonth('updated_at', now()->month)->count(),
        ];

        $reservations = Reservation::orderBy('created_at', 'desc')->take(200)->get(['id', 'first_name', 'last_name']);

        return view('admin.crm.postventa', compact('items', 'stats', 'reservations'));
    }

    public function crmAprobaciones()
    {
        $items = Approval::with('reservation')->orderBy('created_at', 'desc')->get();

        $pendingUsers = \Illuminate\Support\Facades\Schema::hasColumn('users', 'verification_status')
            ? User::where('verification_status', 'pending')->orderBy('created_at', 'desc')->get()
            : collect();

        // Pending KYC documents (one per reservation) — show as approval rows with 3 actions
        $pendingKycDocs = Document::with('reservation')
            ->where('document_type', 'kyc')
            ->where('status', 'pending')
            ->whereNotNull('reservation_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'pendientes' => Approval::where('status', 'pendiente')->count() + $pendingUsers->count() + $pendingKycDocs->count(),
            'alta'       => Approval::where('status', 'pendiente')->where('priority', 'alta')->count(),
            'aprobadas'  => Approval::where('status', 'aprobada')->whereDate('decided_at', today())->count()
                          + Document::where('document_type', 'kyc')->where('status', 'approved')->whereDate('approved_at', today())->count(),
        ];

        $reservations = Reservation::orderBy('created_at', 'desc')->take(200)->get(['id', 'first_name', 'last_name']);

        return view('admin.crm.aprobaciones', compact('items', 'stats', 'reservations', 'pendingUsers', 'pendingKycDocs'));
    }

    /* ───── Presupuesto del expediente ───── */

    public function saveBudget(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:A,B,C,custom',
            'payment_initial_percentage' => 'required|numeric|min:0|max:100',
            'payment_construction_percentage' => 'required|numeric|min:0|max:100',
            'payment_delivery_percentage' => 'required|numeric|min:0|max:100',
            'payment_installments' => 'required|integer|min:0|max:120',
            'legal_costs' => 'required|numeric|min:0',
            'budget_notes' => 'nullable|string|max:2000',
            'admin_reply' => 'nullable|string|max:2000',
            'action' => 'required|string|in:save,send',
        ]);

        try {
            PaymentPlanHelper::validatePercentages(
                $validated['payment_initial_percentage'],
                $validated['payment_construction_percentage'],
                $validated['payment_delivery_percentage'],
            );
        } catch (\Exception $e) {
            return back()->withErrors(['percentages' => $e->getMessage()])->withInput();
        }

        if ($validated['payment_construction_percentage'] == 0 && $validated['payment_installments'] > 0) {
            return back()->withErrors([
                'payment_installments' => 'No puede haber cuotas si el porcentaje de construcción es 0%.',
            ])->withInput();
        }

        $isSending = $validated['action'] === 'send';

        // Append admin reply to the observation thread (if provided)
        $observations = $reservation->budget_observations ?? [];
        if (! empty($validated['admin_reply'])) {
            $observations[] = [
                'from'    => 'admin',
                'author'  => Auth::user()?->name ?? 'Asesor',
                'message' => $validated['admin_reply'],
                'at'      => now()->toIso8601String(),
            ];
        }

        $reservation->update([
            'payment_method' => $validated['payment_method'],
            'payment_initial_percentage' => $validated['payment_initial_percentage'],
            'payment_construction_percentage' => $validated['payment_construction_percentage'],
            'payment_delivery_percentage' => $validated['payment_delivery_percentage'],
            'payment_installments' => $validated['payment_installments'],
            'legal_costs' => $validated['legal_costs'],
            'budget_notes' => $validated['budget_notes'] ?? null,
            'budget_observations' => $observations,
            'budget_status' => $isSending ? 'sent' : 'draft',
            'budget_sent_at' => $isSending ? now() : $reservation->budget_sent_at,
            'budget_configured_by' => Auth::id(),
        ]);

        $message = $isSending
            ? 'Plan de pagos enviado al cliente correctamente.'
            : 'Borrador del plan de pagos guardado.';

        return back()->with('success', $message);
    }

    /**
     * Admin uploads a modified contract (replaces file_path) and optionally adds an admin
     * reply to the observation thread. The doc returns to "pending" so the client can review.
     */
    public function uploadModifiedContract(Request $request, Document $document)
    {
        if (! in_array($document->document_type, ['purchase_promise', 'contract'])) {
            return back()->with('error', 'Tipo de documento inválido.');
        }

        $data = $request->validate([
            'file'        => 'required|file|mimes:pdf,doc,docx|max:10240',
            'admin_reply' => 'nullable|string|max:2000',
        ]);

        $file = $request->file('file');
        $ext  = $file->getClientOriginalExtension();
        $filename = $document->document_type.'_'.$document->reservation_id.'_'.time().'.'.$ext;
        $stored = $file->storeAs('contracts', $filename, 'public');

        $meta = $document->metadata ?? [];
        $obs  = $meta['observations'] ?? [];

        if (! empty($data['admin_reply'])) {
            $obs[] = [
                'from'    => 'admin',
                'author'  => Auth::user()?->name ?? 'Asesor',
                'message' => $data['admin_reply'],
                'at'      => now()->toIso8601String(),
            ];
        }
        $obs[] = [
            'from'    => 'admin',
            'author'  => Auth::user()?->name ?? 'Asesor',
            'message' => 'Subió una versión modificada del contrato: '.$file->getClientOriginalName(),
            'kind'    => 'upload',
            'at'      => now()->toIso8601String(),
        ];
        $meta['observations'] = $obs;
        // Clear acceptance — client has to review again
        unset($meta['accepted_at']);

        $document->update([
            'file_path' => $stored,
            'filename'  => $file->getClientOriginalName(),
            'status'    => 'pending',
            'metadata'  => $meta,
        ]);

        return back()->with('success', 'Contrato modificado subido. El cliente lo revisará nuevamente.');
    }

    /**
     * Admin replies to a contract observation without uploading a new file.
     */
    public function replyContractObservation(Request $request, Document $document)
    {
        if (! in_array($document->document_type, ['purchase_promise', 'contract'])) {
            return back()->with('error', 'Tipo de documento inválido.');
        }

        $data = $request->validate(['admin_reply' => 'required|string|max:2000']);

        $meta = $document->metadata ?? [];
        $obs  = $meta['observations'] ?? [];
        $obs[] = [
            'from'    => 'admin',
            'author'  => Auth::user()?->name ?? 'Asesor',
            'message' => $data['admin_reply'],
            'at'      => now()->toIso8601String(),
        ];
        $meta['observations'] = $obs;
        $document->update(['metadata' => $meta]);

        return back()->with('success', 'Mensaje enviado al cliente.');
    }

    public function revertBudget(Reservation $reservation)
    {
        $reservation->update([
            'budget_status' => 'draft',
            'budget_sent_at' => null,
        ]);

        return back()->with('success', 'Presupuesto revertido a borrador. El cliente ya no lo verá.');
    }

    public function crmTareas(Request $request)
    {
        // Auto-mark overdue
        Task::where('status', '!=', 'completada')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->update(['status' => 'vencida']);

        $filtro = $request->get('filtro', 'todas');
        $query = Task::with(['reservation', 'project']);
        if ($filtro !== 'todas') {
            if (in_array($filtro, ['alta', 'media', 'baja'])) {
                $query->where('priority', $filtro);
            } else {
                $query->where('status', $filtro);
            }
        }

        $items = $query->orderByRaw("CASE status WHEN 'vencida' THEN 1 WHEN 'pendiente' THEN 2 WHEN 'en_proceso' THEN 3 ELSE 4 END")
                       ->orderBy('due_date')
                       ->get();

        $reservations = Reservation::orderBy('created_at', 'desc')->take(200)->get(['id', 'first_name', 'last_name', 'reservation_code']);
        $projects = Project::orderBy('name')->get(['id', 'name']);

        return view('admin.crm.tareas', compact('items', 'filtro', 'reservations', 'projects'));
    }

    public function crmPagos($id)
    {
        $reservation = Reservation::with(['unit', 'payments'])->findOrFail($id);

        if (Auth::user()->role === 'broker') {
            $allowed = Auth::user()->assignedUnits()->pluck('units.id')->map(fn($i) => (string) $i)->all();
            if (! in_array((string) $reservation->unit_id, $allowed, true)) {
                abort(403, 'No tienes acceso a este expediente.');
            }
        }

        $payments = $reservation->payments()->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $paidCount = $payments->where('status', 'paid')->count();
        $pendingCount = $payments->where('status', 'pending')->count();
        $overdueCount = $payments->where('status', 'overdue')->count() + 
                         $payments->where('status', 'pending')->filter(function($p) {
                             return $p->due_date < now();
                         })->count();
        
        $totalCollected = $payments->where('status', 'paid')->sum('amount');
        
        return view('admin.crm.pagos', compact('reservation', 'payments', 'paidCount', 'pendingCount', 'overdueCount', 'totalCollected'));
    }

    /* ───── CRM nuevas vistas (sólo UI, sin backend) ───── */
    public function crmAvanceObra()
    {
        $projects = Project::orderByDesc('progress')->get();
        $activeProject = $projects->firstWhere('stage', 'active')
            ?? $projects->firstWhere('progress', '>', 0)
            ?? $projects->first();

        $reports = \App\Models\ConstructionReport::with('project', 'author')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        $latest = $reports->first();

        return view('admin.crm.avance_obra', compact('projects', 'activeProject', 'reports', 'latest'));
    }

    /**
     * Publica un nuevo reporte de avance y notifica a los compradores (#5, E-12).
     */
    public function storeConstructionReport(Request $request)
    {
        $data = $request->validate([
            'project_id'         => 'nullable|exists:projects,id',
            'period'             => 'required|string|max:120',
            'title'              => 'required|string|max:160',
            'description'        => 'nullable|string|max:2000',
            'overall_progress'   => 'required|integer|min:0|max:100',
            'estimated_delivery' => 'nullable|string|max:60',
            'phases'             => 'nullable|array',
            'photos.*'           => 'nullable|image|max:8192',
            'notify'             => 'nullable|boolean',
        ]);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('construction-reports', 'public');
            }
        }

        $report = \App\Models\ConstructionReport::create([
            'project_id'         => $data['project_id'] ?? null,
            'period'             => $data['period'],
            'title'              => $data['title'],
            'description'        => $data['description'] ?? null,
            'overall_progress'   => $data['overall_progress'],
            'estimated_delivery' => $data['estimated_delivery'] ?? null,
            'phases'             => $data['phases'] ?? [],
            'photos'             => $photoPaths,
            'created_by'         => Auth::id(),
            'published_at'       => now(),
        ]);

        // Sincroniza el progreso global del proyecto
        if ($report->project_id) {
            Project::where('id', $report->project_id)->update(['progress' => $report->overall_progress]);
        }

        $notified = 0;
        if ($request->boolean('notify', true)) {
            // Nuevo reporte publicado (E-12).
            $notified = $this->notifyConstructionReport($report, 'report_uploaded');
            $report->update(['notified_at' => now(), 'notified_count' => $notified]);
        }

        return back()->with('success', "Reporte publicado." . ($notified ? " Notificados {$notified} compradores." : ''));
    }

    /**
     * Reenvía el reporte como "avance mensual" (E-04) a los compradores del proyecto.
     */
    public function notifyConstructionReportMonthly(\App\Models\ConstructionReport $report)
    {
        $notified = $this->notifyConstructionReport($report, 'progress_update');
        $report->update(['notified_at' => now(), 'notified_count' => $notified]);

        return back()->with('success', "Avance mensual enviado a {$notified} compradores.");
    }

    /**
     * Dispara la automatización del CRM ($event) por cada comprador activo del
     * proyecto del reporte. Devuelve la cantidad de correos enviados.
     */
    private function notifyConstructionReport(\App\Models\ConstructionReport $report, string $event): int
    {
        $query = Reservation::with('unit', 'user')->whereNotNull('paid_at');
        if ($report->project_id) {
            $query->whereHas('unit', fn ($q) => $q->where('project_id', $report->project_id));
        }

        $sent = 0;
        foreach ($query->get() as $reservation) {
            $sent += \App\Services\CrmDispatcher::event($event, [
                'report'      => $report,
                'reservation' => $reservation,
            ]);
        }

        return $sent;
    }
    public function crmPlantillas(Request $request)
    {
        $tab = $request->get('tab', 'plantillas');
        $filter = $request->get('cat', 'todas');

        $templatesQuery = CrmTemplate::query()->orderBy('name');
        if ($filter !== 'todas') {
            $templatesQuery->where('category', $filter);
        }
        $templates = $templatesQuery->get();
        $templatesAll = CrmTemplate::all();

        $automations = CrmAutomation::with('template')->orderBy('name')->get();

        $channels = CrmChannelSetting::all()->keyBy('channel');

        $counts = [
            'templates'   => CrmTemplate::count(),
            'automations' => CrmAutomation::where('is_active', true)->count(),
            'by_category' => CrmTemplate::selectRaw('category, COUNT(*) as c')->groupBy('category')->pluck('c', 'category')->all(),
        ];

        return view('admin.crm.plantillas', compact('templates', 'templatesAll', 'automations', 'channels', 'counts', 'tab', 'filter'));
    }

    /* ───── CRM: Templates CRUD ───── */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:160',
            'category'  => 'required|string|max:60',
            'icon'      => 'nullable|string|max:40',
            'channels'  => 'required|array|min:1',
            'channels.*'=> 'in:email,whatsapp,sms,push',
            'subject'   => 'nullable|string|max:255',
            'body'      => 'required|string',
            'variables' => 'nullable|array',
        ]);
        $validated['icon'] = $validated['icon'] ?: 'file';
        $validated['is_active'] = true;
        CrmTemplate::create($validated);
        return back()->with('success', 'Plantilla creada.');
    }

    public function updateTemplate(Request $request, CrmTemplate $template)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:160',
            'category'  => 'required|string|max:60',
            'icon'      => 'nullable|string|max:40',
            'channels'  => 'required|array|min:1',
            'channels.*'=> 'in:email,whatsapp,sms,push',
            'subject'   => 'nullable|string|max:255',
            'body'      => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['icon'] = $validated['icon'] ?: 'file';
        $template->update($validated);
        return back()->with('success', 'Plantilla actualizada.');
    }

    public function deleteTemplate(CrmTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Plantilla eliminada.');
    }

    public function duplicateTemplate(CrmTemplate $template)
    {
        $copy = $template->replicate();
        $copy->name = $template->name . ' (copia)';
        $copy->last_used_at = null;
        $copy->usage_count = 0;
        $copy->save();
        return back()->with('success', 'Plantilla duplicada.');
    }

    public function getTemplate(CrmTemplate $template)
    {
        return response()->json($template);
    }

    /**
     * Vista previa HTML de la plantilla con datos de ejemplo (para el iframe del modal).
     */
    public function previewTemplate(CrmTemplate $template)
    {
        $rendered = \App\Support\CrmTemplateRenderer::render(
            $template,
            \App\Support\CrmTemplateRenderer::SAMPLE
        );

        return response($rendered['html'])->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function sendTestTemplate(Request $request, CrmTemplate $template)
    {
        $validated = $request->validate([
            'to' => 'required|email|max:160',
        ]);

        $rendered = \App\Support\CrmTemplateRenderer::render(
            $template,
            \App\Support\CrmTemplateRenderer::SAMPLE
        );

        try {
            \Illuminate\Support\Facades\Mail::to($validated['to'])
                ->send(new \App\Mail\CrmTemplateMail('[PRUEBA] ' . $rendered['subject'], $rendered['html']));
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo enviar la prueba: ' . $e->getMessage());
        }

        $template->forceFill([
            'last_used_at' => now(),
            'usage_count'  => ($template->usage_count ?? 0) + 1,
        ])->save();

        return back()->with('success', 'Prueba enviada a ' . $validated['to'] . '.');
    }

    /* ───── CRM: Automations CRUD ───── */
    public function storeAutomation(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:160',
            'description'    => 'nullable|string|max:1000',
            'trigger_event'  => 'required|string|max:80',
            'template_id'    => 'nullable|exists:crm_templates,id',
            'delay_minutes'  => 'required|integer|min:0|max:43200',
            'channels'       => 'required|array|min:1',
            'channels.*'     => 'in:email,whatsapp,sms,push',
            'is_active'      => 'nullable|boolean',
        ]);
        $validated['is_active'] = (bool)($validated['is_active'] ?? true);
        CrmAutomation::create($validated);
        return back()->with('success', 'Automatización creada.');
    }

    public function updateAutomation(Request $request, CrmAutomation $automation)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:160',
            'description'    => 'nullable|string|max:1000',
            'trigger_event'  => 'required|string|max:80',
            'template_id'    => 'nullable|exists:crm_templates,id',
            'delay_minutes'  => 'required|integer|min:0|max:43200',
            'channels'       => 'required|array|min:1',
            'channels.*'     => 'in:email,whatsapp,sms,push',
            'is_active'      => 'nullable|boolean',
        ]);
        $validated['is_active'] = (bool)($validated['is_active'] ?? false);
        $automation->update($validated);
        return back()->with('success', 'Automatización actualizada.');
    }

    public function toggleAutomation(CrmAutomation $automation)
    {
        $automation->update(['is_active' => !$automation->is_active]);
        return back()->with('success', $automation->is_active ? 'Automatización activada.' : 'Automatización pausada.');
    }

    public function deleteAutomation(CrmAutomation $automation)
    {
        $automation->delete();
        return back()->with('success', 'Automatización eliminada.');
    }

    public function runAutomation(CrmAutomation $automation)
    {
        $template = $automation->template;
        if (! $template) {
            return back()->with('error', 'La automatización no tiene plantilla asignada.');
        }

        // Ejecución manual: envía una muestra de la plantilla al correo del admin
        // (no dispara envíos masivos a clientes reales).
        $to = Auth::user()->email;
        $rendered = \App\Support\CrmTemplateRenderer::render(
            $template,
            \App\Support\CrmTemplateRenderer::SAMPLE
        );

        try {
            \Illuminate\Support\Facades\Mail::to($to)
                ->send(new \App\Mail\CrmTemplateMail('[FLUJO] ' . $rendered['subject'], $rendered['html']));
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo ejecutar el flujo: ' . $e->getMessage());
        }

        $automation->forceFill([
            'last_run_at' => now(),
            'run_count'   => ($automation->run_count ?? 0) + 1,
        ])->save();
        $template->forceFill([
            'last_used_at' => now(),
            'usage_count'  => ($template->usage_count ?? 0) + 1,
        ])->save();

        return back()->with('success', 'Flujo ejecutado: muestra enviada a ' . $to . '.');
    }

    public function getAutomation(CrmAutomation $automation)
    {
        return response()->json($automation->load('template'));
    }

    /* ───── CRM: Channel settings ───── */
    public function updateChannels(Request $request)
    {
        $validated = $request->validate([
            'channels'                  => 'required|array',
            'channels.*.enabled'        => 'nullable|in:0,1,on,true,false',
            'channels.*.config'         => 'nullable|array',
        ]);

        foreach ($validated['channels'] as $channelKey => $data) {
            if (!array_key_exists($channelKey, CrmChannelSetting::$CHANNELS)) continue;
            $setting = CrmChannelSetting::firstOrCreate(['channel' => $channelKey]);
            $setting->enabled = in_array($data['enabled'] ?? '0', ['1','on','true',true,1], true);
            $setting->config  = $data['config'] ?? [];
            $setting->save();
        }

        return back()->with('success', 'Configuración de canales guardada.');
    }
    public function crmAnuncios()        { return view('admin.crm.anuncios'); }
    public function crmProyectoDetalle($id)
    {
        $proyecto = Project::withCount([
            'units',
            'units as sold_count'      => fn($q) => $q->where('status', 'SOLD'),
            'units as reserved_count'  => fn($q) => $q->where('status', 'RESERVED'),
            'units as available_count' => fn($q) => $q->where('status', 'AVAILABLE'),
        ])->findOrFail($id);
        $units = $proyecto->units()->orderBy('custom_id')->orderBy('id')->get();
        return view('admin.crm.proyecto_detalle', compact('proyecto', 'units'));
    }
    public function crmExpedienteDetalle($id)
    {
        $reservation = Reservation::with(['unit', 'documents', 'payments'])->findOrFail($id);

        if (Auth::user()->role === 'broker') {
            $allowed = Auth::user()->assignedUnits()->pluck('units.id')->map(fn($i) => (string) $i)->all();
            if (! in_array((string) $reservation->unit_id, $allowed, true)) {
                abort(403, 'No tienes acceso a este expediente.');
            }
        }

        $tab = request('tab', 'resumen');
        return view('admin.crm.expediente_detalle_v2', compact('reservation', 'tab'));
    }

    /* ───── KYC verification (admin approves/rejects users) ───── */
    public function verifyUserKyc(Request $request, $userId)
    {
        $data = $request->validate(['decision' => 'required|in:approved,rejected']);
        $user = User::findOrFail($userId);

        if (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'verification_status')) {
            return back()->with('error', 'verification_status column is missing.');
        }

        $user->update(['verification_status' => $data['decision']]);

        // Also approve/reject any pending Documents from the registration
        if ($user->kyc_id_document) {
            Document::where('file_path', 'like', 'onboarding/'.$user->id.'/%')
                ->whereIn('status', ['pending', 'generated'])
                ->update([
                    'status'      => $data['decision'] === 'approved' ? 'approved' : 'rejected',
                    'approved_at' => $data['decision'] === 'approved' ? now() : null,
                    'approved_by' => $data['decision'] === 'approved' ? auth()->id() : null,
                ]);
        }

        return back()->with('success', "Usuario {$user->name} marcado como {$data['decision']}.");
    }

    /* ───── Acciones rápidas desde modales del CRM ───── */
    public function createReservationQuick(Request $request)
    {
        $data = $request->validate([
            'client_mode'    => 'required|in:new,existing',
            'user_id'        => 'required_if:client_mode,existing|nullable|exists:users,id',
            'cliente_nombre' => 'required_if:client_mode,new|nullable|string|max:255',
            'cliente_email'  => 'required_if:client_mode,new|nullable|email|max:255',
            'unit_id'        => 'required|exists:units,id',
            'fecha'          => 'required|date',
            'monto'          => 'required|numeric|min:0',
        ]);

        $unit = Unit::find($data['unit_id']);

        // Resuelve (o crea) la cuenta del cliente y obtiene sus datos.
        // En modo "new" se crea el usuario y se le envía un correo de invitación
        // para que active su cuenta y cree su contraseña.
        [$client, $invited] = $this->resolveReservationClient($data, $unit);

        $reservationData = [
            'first_name'       => $client['first_name'],
            'last_name'        => $client['last_name'],
            'email'            => $client['email'],
            'phone'            => $client['phone'] ?? '',
            'country'          => $client['country'] ?? '',
            'user_id'          => $client['user_id'],
            'unit_id'          => $data['unit_id'],
            'reservation_code' => strtoupper(\Illuminate\Support\Str::random(8)),
            'status'           => 'pending',
            'created_at'       => $data['fecha'],
        ];
        // Backfill any not-null columns the legacy schema requires
        $required = ['unit_name', 'unit_price', 'unit_developer'];
        foreach ($required as $col) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('reservations', $col)) {
                $reservationData[$col] = match ($col) {
                    'unit_name'      => $unit?->name ?? $unit?->custom_id ?? '—',
                    'unit_price'     => (string) ($unit?->price ?? 0),
                    'unit_developer' => 'Makai',
                    default          => '',
                };
            }
        }

        $reservation = Reservation::create($reservationData);

        if ($data['monto'] > 0) {
            Payment::create([
                'reservation_id' => $reservation->id,
                'payment_type'   => 'reservation',
                'label'          => 'Cuota inicial — Reserva',
                'amount'         => $data['monto'],
                'due_date'       => $data['fecha'],
                'paid_at'        => $data['fecha'],
                'status'         => 'paid',
                'payment_method' => 'wire',
            ]);
        }

        $msg = $invited
            ? 'Reserva creada. Se envió una invitación a ' . $client['email'] . ' para que active su cuenta.'
            : 'Reserva creada correctamente.';

        return back()->with('success', $msg);
    }

    /**
     * Resuelve el cliente de una reserva creada desde "Nueva reserva".
     *
     * - Modo "existing": vincula a un usuario ya existente (por user_id).
     * - Modo "new": si el email ya pertenece a un usuario lo vincula; si no,
     *   crea la cuenta (sin contraseña) y le envía un correo de invitación para
     *   que la active. Reutiliza el diseño de correos de marca.
     *
     * @return array{0: array, 1: bool}  [datos del cliente, ¿se envió invitación?]
     */
    private function resolveReservationClient(array $data, ?Unit $unit): array
    {
        if ($data['client_mode'] === 'existing') {
            $user = User::findOrFail($data['user_id']);
            return [[
                'user_id'    => $user->id,
                'email'      => $user->email,
                'first_name' => $user->first_name ?: (\Illuminate\Support\Str::before($user->name ?? '', ' ') ?: $user->name),
                'last_name'  => $user->last_name ?: \Illuminate\Support\Str::after($user->name ?? '', ' '),
                'phone'      => $user->phone,
                'country'    => $user->country,
            ], false];
        }

        // Modo "new": parte del nombre completo.
        $parts = preg_split('/\s+/', trim($data['cliente_nombre']), 2);
        $first = $parts[0] ?? '';
        $last  = $parts[1] ?? '';

        $existing = User::where('email', $data['cliente_email'])->first();
        if ($existing) {
            // El correo ya tiene cuenta: vincúlala sin reinvitar.
            return [[
                'user_id'    => $existing->id,
                'email'      => $existing->email,
                'first_name' => $existing->first_name ?: $first,
                'last_name'  => $existing->last_name ?: $last,
                'phone'      => $existing->phone,
                'country'    => $existing->country,
            ], false];
        }

        // Crea la cuenta nueva (sin contraseña hasta que active la invitación).
        $userAttrs = [
            'name'  => trim($data['cliente_nombre']),
            'email' => $data['cliente_email'],
            'role'  => 'user',
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'first_name'))          $userAttrs['first_name']          = $first;
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'last_name'))           $userAttrs['last_name']           = $last;
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'verification_status')) $userAttrs['verification_status'] = 'approved';

        $user = User::create($userAttrs);

        // Genera el enlace de activación y envía la invitación. No interrumpe
        // el alta de la reserva si el correo falla.
        $invited = false;
        try {
            $url = \App\Http\Controllers\AuthController::makeInvitationUrl($user->email);
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\InvitationMail(
                    name: $first ?: $user->name,
                    actionUrl: $url,
                    unitName: $unit?->name ?? $unit?->custom_id ?? '',
                    days: \App\Http\Controllers\AuthController::INVITATION_DAYS,
                )
            );
            $invited = true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo enviar la invitación de cuenta a ' . $user->email . ': ' . $e->getMessage());
        }

        return [[
            'user_id'    => $user->id,
            'email'      => $user->email,
            'first_name' => $first,
            'last_name'  => $last,
            'phone'      => '',
            'country'    => '',
        ], $invited];
    }

    public function uploadDocumentQuick(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'document_type'  => 'required|string',
            'title'          => 'required|string|max:255',
            'file'           => 'required|file|max:4096|mimes:pdf,jpg,jpeg,png',
            'status'         => 'nullable|string',
            'generated_at'   => 'nullable|date',
        ]);

        $path = $request->file('file')->store('documents', 'public');

        Document::create([
            'reservation_id' => $data['reservation_id'],
            'document_type'  => $data['document_type'],
            'title'          => $data['title'],
            'filename'       => $request->file('file')->getClientOriginalName(),
            'file_path'      => $path,
            'status'         => $data['status'] ?? 'pending',
            'generated_at'   => $data['generated_at'] ?? now(),
        ]);

        return back()->with('success', 'Documento subido correctamente.');
    }

    /**
     * Admin requests a document from the client. Creates a placeholder Document
     * (no file yet) that shows up as "requerido" in the client's documents tab,
     * where the client can upload the file. Uses file_path='pending' as the
     * "no file yet" sentinel and metadata.requested=true to flag it.
     */
    public function requestDocument(Request $request, Reservation $reservation)
    {
        if (Auth::user()->role === 'broker') {
            $allowed = Auth::user()->assignedUnits()->pluck('units.id')->map(fn($i) => (string) $i)->all();
            if (! in_array((string) $reservation->unit_id, $allowed, true)) {
                abort(403, 'No tienes acceso a este expediente.');
            }
        }

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'due_date'    => 'nullable|date',
        ]);

        Document::create([
            'reservation_id' => $reservation->id,
            'document_type'  => 'requested',
            'title'          => $data['title'],
            'filename'       => '',
            'file_path'      => 'pending',
            'status'         => 'pending',
            'metadata'       => [
                'requested'    => true,
                'description'  => $data['description'] ?? null,
                'due_date'     => $data['due_date'] ?? null,
                'requested_by' => Auth::id(),
                'requested_at' => now()->toDateTimeString(),
            ],
        ]);

        return back()->with('success', 'Documento solicitado al cliente.');
    }

    /**
     * Remove a document (or a pending request) from an expediente. Form-based
     * counterpart to the JSON documents.delete endpoint, so it can redirect back.
     */
    public function deleteDocumentQuick(Document $document)
    {
        $reservation = $document->reservation;
        if ($reservation && Auth::user()->role === 'broker') {
            $allowed = Auth::user()->assignedUnits()->pluck('units.id')->map(fn($i) => (string) $i)->all();
            if (! in_array((string) $reservation->unit_id, $allowed, true)) {
                abort(403, 'No tienes acceso a este expediente.');
            }
        }

        \App\Services\DocumentService::deleteDocument($document);

        return back()->with('success', 'Documento eliminado.');
    }

    public function createPaymentQuick(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'amount'         => 'required|numeric|min:0',
            'paid_at'        => 'required|date',
            'payment_method' => 'required|string',
            'label'          => 'required|string|max:255',
            'currency'       => 'nullable|string|size:3',
            'receipt'        => 'nullable|file|max:4096|mimes:pdf,jpg,jpeg,png',
            'notes'          => 'nullable|string|max:200',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        Payment::create([
            'reservation_id' => $data['reservation_id'],
            'payment_type'   => 'installment',
            'label'          => $data['label'],
            'amount'         => $data['amount'],
            'due_date'       => $data['paid_at'],
            'paid_at'        => $data['paid_at'],
            'status'         => 'paid',
            'payment_method' => $data['payment_method'],
            'receipt_path'   => $receiptPath,
            'notes'          => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Pago registrado correctamente.');
    }

    /** Lista de recursos válidos para exportación con su etiqueta. */
    private const EXPORT_RESOURCES = [
        'expedientes' => 'Expedientes',
        'documentos'  => 'Documentos',
        'contratos'   => 'Contratos',
        'transacciones' => 'Transacciones',
        'unidades'    => 'Unidades',
    ];

    public function exportResource(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->is_admin) {
            abort(403, 'Solo el administrador puede exportar sin código de autorización.');
        }

        $resource = $request->get('resource', 'expedientes');
        $format   = $request->get('format', 'csv');
        $range    = $request->get('range', '3m');

        return $this->streamResourceExport($resource, $format, $range);
    }

    /** Genera un código de 6 dígitos y notifica al admin. */
    public function requestExportCode(Request $request)
    {
        $data = $request->validate([
            'resource' => 'required|string|in:'.implode(',', array_keys(self::EXPORT_RESOURCES)),
            'format'   => 'nullable|string|in:csv,xlsx,pdf',
            'range'    => 'nullable|string|in:3m,6m,1y,all',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->is_admin) {
            return response()->json([
                'ok' => false,
                'message' => 'El administrador no necesita código de autorización.',
            ], 422);
        }

        // Invalida códigos previos no usados para este usuario+recurso
        ExportAuthorization::where('requester_id', $user->id)
            ->where('resource', $data['resource'])
            ->whereNull('used_at')
            ->update(['expires_at' => now()->subSecond()]);

        $admin = User::where('role', 'admin')->orderBy('id')->first();

        $auth = ExportAuthorization::create([
            'requester_id'  => $user->id,
            'admin_id'      => $admin?->id,
            'resource'      => $data['resource'],
            'format'        => $data['format'] ?? 'csv',
            'range'         => $data['range'] ?? '3m',
            'code'          => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at'    => now()->addMinutes(10),
        ]);

        return response()->json([
            'ok'            => true,
            'id'            => $auth->id,
            'admin_email'   => $admin ? $this->maskEmail($admin->email) : null,
            'expires_at'    => $auth->expires_at->toIso8601String(),
            'expires_in'    => 600,
        ]);
    }

    /** Reenvía un código nuevo si el anterior caducó o se perdió. */
    public function resendExportCode(Request $request)
    {
        return $this->requestExportCode($request);
    }

    /** Valida el código y entrega el archivo de exportación. */
    public function verifyExportCode(Request $request)
    {
        $data = $request->validate([
            'resource' => 'required|string|in:'.implode(',', array_keys(self::EXPORT_RESOURCES)),
            'code'     => 'required|string|size:6',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $auth = ExportAuthorization::where('requester_id', $user->id)
            ->where('resource', $data['resource'])
            ->where('code', $data['code'])
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $auth) {
            return response()->json([
                'ok'      => false,
                'message' => 'El código es inválido o ya caducó. Solicita uno nuevo.',
            ], 422);
        }

        $auth->update(['used_at' => now()]);

        return $this->streamResourceExport($auth->resource, $auth->format, $auth->range);
    }

    private function streamResourceExport(string $resource, string $format, string $range)
    {
        $cutoff = match ($range) {
            '6m'  => now()->subMonths(6),
            '1y'  => now()->subYear(),
            'all' => null,
            default => now()->subMonths(3),
        };

        [$filename, $headers, $rows] = match ($resource) {
            'documentos'   => $this->exportDocumentos($cutoff),
            'contratos'    => $this->exportContratos($cutoff),
            'transacciones'=> $this->exportTransacciones($cutoff),
            'unidades'     => $this->exportUnidades(),
            default        => $this->exportExpedientes($cutoff),
        };

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        }, $filename.'-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function maskEmail(?string $email): string
    {
        if (! $email || ! str_contains($email, '@')) return '***';
        [$local, $domain] = explode('@', $email, 2);
        $first = mb_substr($local, 0, 1);
        return $first.str_repeat('*', max(3, mb_strlen($local) - 1)).'@'.$domain;
    }

    private function exportExpedientes($cutoff)
    {
        $q = Reservation::with('unit');
        if ($cutoff) $q->where('created_at', '>=', $cutoff);
        $rows = $q->get()->map(fn($r) => [
            $r->reservation_code, $r->first_name, $r->last_name, $r->email,
            $r->phone, $r->country, $r->unit->name ?? '', $r->unit->price ?? 0, $r->created_at,
        ])->all();
        return ['expedientes', ['Código','Nombre','Apellido','Email','Teléfono','País','Unidad','Precio','Fecha'], $rows];
    }
    private function exportDocumentos($cutoff)
    {
        $q = Document::with('reservation');
        if ($cutoff) $q->where('updated_at', '>=', $cutoff);
        $rows = $q->get()->map(fn($d) => [$d->title, $d->document_type, $d->status, optional($d->reservation)->first_name.' '.optional($d->reservation)->last_name, $d->updated_at])->all();
        return ['documentos', ['Título','Tipo','Estado','Cliente','Actualizado'], $rows];
    }
    private function exportContratos($cutoff)
    {
        return $this->exportExpedientes($cutoff);
    }
    private function exportTransacciones($cutoff)
    {
        $q = Payment::with('reservation');
        if ($cutoff) $q->where('created_at', '>=', $cutoff);
        $rows = $q->get()->map(fn($p) => [
            optional($p->reservation)->first_name.' '.optional($p->reservation)->last_name,
            $p->label, $p->amount, $p->status, $p->payment_method, $p->paid_at,
        ])->all();
        return ['transacciones', ['Cliente','Concepto','Monto','Estado','Método','Pagado'], $rows];
    }
    private function exportUnidades()
    {
        $rows = Unit::orderBy('custom_id')->get()->map(fn($u) => [
            $u->custom_id ?? $u->name, $u->type, $u->floor, $u->bedrooms, $u->bathrooms,
            $u->internal_area, $u->price, $u->status,
        ])->all();
        return ['unidades', ['Unidad','Tipo','Piso','Camas','Baños','Sqft Int.','Precio','Estado'], $rows];
    }

    public function sendMessageQuick(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'channel'        => 'nullable|in:chat,email,whatsapp,sms',
            'message'        => 'required|string|max:5000',
        ]);

        \App\Models\Message::create([
            'reservation_id' => $data['reservation_id'],
            'sender_id'      => Auth::id(),
            'sender_role'    => 'admin',
            'body'           => $data['message'],
            'channel'        => $data['channel'] ?? 'chat',
        ]);

        return back()->with('success', 'Mensaje enviado.');
    }

    // API Methods for Payment Management
    public function getReservation($id)
    {
        $reservation = Reservation::with(['unit'])->findOrFail($id);
        return response()->json($reservation);
    }

    public function getReservationPayments($id)
    {
        $reservation = Reservation::findOrFail($id);
        $payments = $reservation->payments()->orderBy('created_at', 'desc')->get();
        return response()->json($payments);
    }

    public function createPayment(Request $request, $reservationId)
    {
        $validator = Validator::make($request->all(), [
            'payment_type' => 'required|in:initial,installment,construction,delivery,extra',
            'installment_number' => 'nullable|integer|min:1',
            'label' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $reservation = Reservation::findOrFail($reservationId);
            
            $payment = new Payment($request->all());
            $payment->reservation_id = $reservationId;
            
            // Auto-set paid_at if status is paid
            if ($payment->status === 'paid' && !$payment->paid_at) {
                $payment->paid_at = now();
            }
            
            $payment->save();

            return response()->json($payment, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating payment: ' . $e->getMessage()], 500);
        }
    }

    public function updatePayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_type' => 'required|in:initial,installment,construction,delivery,extra',
            'installment_number' => 'nullable|integer|min:1',
            'label' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'payment_method' => 'nullable|in:cash,transfer,check,card,other',
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $payment = Payment::findOrFail($id);
            
            // Auto-set paid_at if status changed to paid
            if ($request->status === 'paid' && !$request->paid_at && !$payment->paid_at) {
                $request->merge(['paid_at' => now()]);
            }
            
            $payment->update($request->all());

            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating payment: ' . $e->getMessage()], 500);
        }
    }

    public function deletePayment($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $payment->delete();

            return response()->json(['message' => 'Payment deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting payment: ' . $e->getMessage()], 500);
        }
    }

    public function markPaymentAsPaid($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            
            if ($payment->status === 'paid') {
                return response()->json(['message' => 'Payment is already marked as paid'], 400);
            }

            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $this->sendPaymentReceipt($payment);

            return response()->json(['message' => 'Payment marked as paid successfully', 'payment' => $payment]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error marking payment as paid: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Envía el comprobante de pago por correo al comprador (#11 del briefing).
     * Silencioso: nunca interrumpe el flujo de confirmación de pago.
     */
    private function sendPaymentReceipt(Payment $payment): void
    {
        try {
            $payment->loadMissing('reservation.user');
            $reservation = $payment->reservation;
            if (! $reservation) {
                return;
            }
            // Comprobante de pago (E-11) vía automatizaciones del CRM.
            \App\Services\CrmDispatcher::event('payment_received', [
                'payment'     => $payment,
                'reservation' => $reservation,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('No se pudo enviar el comprobante de pago: '.$e->getMessage());
        }
    }

    /**
     * Approve or reject a payment submitted by client
     */
    public function approvePayment(Request $request, Payment $payment)
    {
        $request->validate([
            'decision' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        try {
            if ($payment->approval_status !== 'pending') {
                return back()->with('error', 'Este pago ya fue procesado.');
            }

            if ($request->decision === 'approved') {
                $payment->update([
                    'approval_status' => 'approved',
                    'status' => 'paid',
                    'paid_at' => now(),
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
                $this->sendPaymentReceipt($payment);
                return back()->with('success', 'Pago aprobado exitosamente.');
            } else {
                $payment->update([
                    'approval_status' => 'rejected',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'rejection_reason' => $request->rejection_reason,
                ]);
                return back()->with('success', 'Pago rechazado.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Generate contract for reservation (admin version)
     */
    public function generateContract(Reservation $reservation)
    {
        if (!$reservation->isBudgetSent()) {
            return back()->with('error', 'El presupuesto aún no fue enviado. No se puede generar el contrato.');
        }

        try {
            // Load template
            $templatePath = storage_path('app/templates/contract_template.docx');
            
            if (!file_exists($templatePath)) {
                return back()->with('error', 'Plantilla de contrato no encontrada');
            }

            // Create TemplateProcessor
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

            // Prepare replacements with translations
            $replacements = $this->getContractReplacements($reservation);

            // Replace variables in template
            foreach ($replacements as $search => $replace) {
                $templateProcessor->setValue($search, $replace);
            }

            // Ensure temp directory exists
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Ensure documents directory exists
            $documentsDir = storage_path('app/public/documents');
            if (!is_dir($documentsDir)) {
                mkdir($documentsDir, 0755, true);
            }
            
            // Generate filename and paths
            $filename = 'contract_' . $reservation->reservation_code . '_' . date('Y-m-d') . '.docx';
            $permanentPath = 'documents/' . $filename;
            $outputPath = storage_path('app/temp/' . $filename);
            
            // Save to temporary file first
            $templateProcessor->saveAs($outputPath);
            
            // Copy temporary file to permanent location
            copy($outputPath, storage_path('app/public/' . $permanentPath));
            
            // Create or update document record
            $document = \App\Services\DocumentService::getDocumentByType($reservation, 'contract');
            if ($document) {
                $document->update([
                    'file_path' => $permanentPath,
                    'filename' => $filename,
                    'status' => 'generated',
                    'generated_at' => now(),
                ]);
            } else {
                \App\Services\DocumentService::createDocument(
                    $reservation,
                    'contract',
                    'Contrato - ' . $reservation->reservation_code,
                    $permanentPath,
                    $filename
                )->markAsGenerated();
            }

            // Download file
            return response()->download($outputPath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el contrato: ' . $e->getMessage());
        }
    }

    /**
     * Generate payment plan for reservation (admin version)
     */
    public function generatePaymentPlan(Reservation $reservation)
    {
        // No status gate: generation works at any stage (draft / sent / approved / signed
        // edits to the plan). The data needed lives on the reservation.
        try {
            // Render the printable HTML view (open in browser → "Descargar PDF")
            return \App\Helpers\DocumentDataHelper::renderAndStore($reservation, 'payment_plan');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el plan de pagos: ' . $e->getMessage());
        }
    }

    /**
     * Generate purchase promise for reservation (admin version)
     */
    public function generatePurchasePromise(Reservation $reservation)
    {
        // Same as generatePaymentPlan: no status gate. Generation always works.
        try {
            // Render the printable HTML view (open in browser → "Descargar PDF")
            return \App\Helpers\DocumentDataHelper::renderAndStore($reservation, 'purchase_promise');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar la promesa de compraventa: ' . $e->getMessage());
        }
    }

    /**
     * Prepare contract replacements (admin version)
     */
    private function getContractReplacements(Reservation $reservation)
    {
        return [
            // Original Spanish variables (exact format from template)
            '${nombre_del_comprador}' => $reservation->first_name . ' ' . $reservation->last_name,
            '${tipo_de comprador_es}' => $this->translateBuyerType($reservation->economic_dependent == 'No' ? 'Individuo' : 'Empresa'),
            '${identificacion_de comprador}' => $reservation->document_number ?? 'N/A',
            '${identificacion_de_empresa}' => 'N/A',
            '${nacionalidad}' => $reservation->nationality ?? 'N/A',
            '${estado_civil_es}' => $this->translateMaritalStatus($reservation->marital_status ?? 'Soltero'),
            '${direccion}' => $this->formatAddress($reservation),
            '${email}' => $reservation->email,
            '${ocupacion}' => $reservation->profession ?? $reservation->occupation ?? 'N/A',

            // English translations (_en versions)
            '${nombre_del_comprador_en}' => $reservation->first_name . ' ' . $reservation->last_name,
            '${tipo_de comprador_en}' => $this->translateBuyerTypeToEnglish($reservation->economic_dependent == 'No' ? 'Individuo' : 'Empresa'),
            '${identificacion_de comprador_en}' => $reservation->document_number ?? 'N/A',
            '${identificacion_de_empresa_en}' => 'N/A',
            '${nacionalidad_en}' => $reservation->nationality ?? 'N/A',
            '${estado_civil_en}' => $this->translateMaritalStatusToEnglish($reservation->marital_status ?? 'Soltero'),
            '${direccion_en}' => $this->formatAddress($reservation),
            '${email_en}' => $reservation->email,
            '${ocupacion_en}' => $reservation->profession ?? $reservation->occupation ?? 'N/A',

            // Additional common variables
            '${codigo_reserva}' => $reservation->reservation_code,
            '${nombre_unidad}' => $reservation->unit_name ?? $reservation->unit->name ?? 'Unit ' . $reservation->unit_id,
            '${precio_total}' => $reservation->formatted_price,
            '${fecha_actual}' => now()->format('d/m/Y'),
            '${fecha_actual_en}' => now()->format('m/d/Y'),
            
            // Unit-specific variables
            '${unit_name}' => $reservation->unit_name ?? $reservation->unit->name ?? 'Unit ' . $reservation->unit_id,
            '${unit_level}' => $reservation->unit->floor ?? 'N/A',
            '${area}' => $reservation->unit->total_area ?? $reservation->unit->internal_area ?? 'N/A',
            '${numero_dormitorios_es}' => $this->formatNumberInSpanish($reservation->unit->bedrooms ?? 0),
            '${numero_baños_es}' => $this->formatNumberInSpanish($reservation->unit->bathrooms ?? 0),
            '${numero_estacionamientos_es}' => $this->formatNumberInSpanish($reservation->unit->parking_bays ?? 0),
            '${numero_dormitorios_en}' => $this->formatNumberInEnglish($reservation->unit->bedrooms ?? 0),
            '${numero_baños_en}' => $this->formatNumberInEnglish($reservation->unit->bathrooms ?? 0),
            '${numero_estacionamientos_en}' => $this->formatNumberInEnglish($reservation->unit->parking_bays ?? 0),
            
            // Price and payment plan variables
            '${price_literal_es}' => $this->convertNumberToWords($reservation->unit_price, 'es'),
            '${price_literal_en}' => $this->convertNumberToWords($reservation->unit_price, 'en'),
            '${price}' => number_format($reservation->unit_price, 2, '.', ','),
            '${plan_de_pagos_es}' => $this->getPaymentPlanDescription($reservation, 'es'),
            '${plan_de_pagos_en}' => $this->getPaymentPlanDescription($reservation, 'en'),
        ];
    }

    // Helper methods for contract generation (simplified versions)
    private function translateBuyerType($type) { return $type ?? 'Individuo'; }
    private function translateBuyerTypeToEnglish($type) { return $type == 'Individuo' ? 'Individual' : 'Company'; }
    private function translateMaritalStatus($status) { return $status ?? 'Soltero/a'; }
    private function translateMaritalStatusToEnglish($status) { return 'Single'; }
    
    private function formatAddress(Reservation $reservation)
    {
        $addressParts = [];
        if ($reservation->address) $addressParts[] = $reservation->address;
        if ($reservation->neighborhood) $addressParts[] = $reservation->neighborhood;
        if ($reservation->city) $addressParts[] = $reservation->city;
        if ($reservation->province) $addressParts[] = $reservation->province;
        if ($reservation->country) $addressParts[] = $reservation->country;
        return empty($addressParts) ? 'N/A' : implode(', ', $addressParts);
    }
    
    private function formatNumberInSpanish($number) { return $number . ' (' . $number . ')'; }
    private function formatNumberInEnglish($number) { return $number . ' (' . $number . ')'; }
    private function convertNumberToWords($number, $language = 'es') { return number_format($number, 0, '.', ','); }
    private function getPaymentPlanDescription(Reservation $reservation, $language = 'es') { return 'Plan de pagos según método ' . $reservation->payment_method; }

    /* ───── CRM: Tareas CRUD ───── */

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'responsible'    => 'nullable|string|max:120',
            'area'           => 'nullable|string|max:120',
            'due_date'       => 'nullable|date',
            'priority'       => 'required|in:alta,media,baja',
            'reservation_id' => 'nullable|exists:reservations,id',
            'project_id'     => 'nullable|exists:projects,id',
            'notes'          => 'nullable|string|max:2000',
        ]);
        $validated['status'] = 'pendiente';
        Task::create($validated);
        return back()->with('success', 'Tarea creada.');
    }

    public function updateTaskStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:pendiente,en_proceso,completada,vencida',
        ]);
        $task->update(['status' => $validated['status']]);
        return back()->with('success', 'Tarea actualizada.');
    }

    public function completeTask(Task $task)
    {
        $task->update(['status' => 'completada']);
        return back()->with('success', 'Tarea completada.');
    }

    public function deleteTask(Task $task)
    {
        $task->delete();
        return back()->with('success', 'Tarea eliminada.');
    }

    /* ───── CRM: Aprobaciones CRUD ───── */

    public function storeApproval(Request $request)
    {
        $validated = $request->validate([
            'type'                => 'required|string|max:120',
            'requested_by'        => 'nullable|string|max:120',
            'amount_or_condition' => 'nullable|string|max:255',
            'priority'            => 'required|in:alta,media,baja',
            'reservation_id'      => 'nullable|exists:reservations,id',
            'notes'               => 'nullable|string|max:2000',
        ]);
        $validated['status'] = 'pendiente';
        Approval::create($validated);
        return back()->with('success', 'Solicitud creada.');
    }

    public function decideApproval(Request $request, Approval $approval)
    {
        $validated = $request->validate([
            'decision' => 'required|in:aprobada,rechazada',
        ]);
        $approval->update([
            'status'      => $validated['decision'],
            'decided_at'  => now(),
        ]);
        return back()->with('success', 'Solicitud ' . $validated['decision'] . '.');
    }

    public function deleteApproval(Approval $approval)
    {
        $approval->delete();
        return back()->with('success', 'Solicitud eliminada.');
    }

    /* ───── CRM: Postventa CRUD ───── */

    public function storeAftersale(Request $request)
    {
        $validated = $request->validate([
            'type'           => 'required|in:Entrega,Garantía,Escritura',
            'client_name'    => 'nullable|string|max:255',
            'unit_label'     => 'nullable|string|max:120',
            'status'         => 'required|in:programada,en_atencion,en_tramite,resuelta',
            'scheduled_date' => 'nullable|date',
            'reservation_id' => 'nullable|exists:reservations,id',
            'unit_id'        => 'nullable|exists:units,id',
            'notes'          => 'nullable|string|max:2000',
        ]);
        Aftersale::create($validated);
        return back()->with('success', 'Caso de postventa creado.');
    }

    public function updateAftersale(Request $request, Aftersale $aftersale)
    {
        $validated = $request->validate([
            'status'         => 'required|in:programada,en_atencion,en_tramite,resuelta',
            'scheduled_date' => 'nullable|date',
            'notes'          => 'nullable|string|max:2000',
        ]);
        $aftersale->update($validated);
        return back()->with('success', 'Caso actualizado.');
    }

    public function deleteAftersale(Aftersale $aftersale)
    {
        $aftersale->delete();
        return back()->with('success', 'Caso eliminado.');
    }

    /* ───── CRM: Proyectos CRUD ───── */

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:60',
            'stage'       => 'required|string|max:60',
            'location'    => 'nullable|string|max:160',
            'progress'    => 'required|integer|min:0|max:100',
            'color'       => 'nullable|string|max:9',
            'icon_path'   => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);
        Project::create($validated);
        return back()->with('success', 'Proyecto creado.');
    }

    public function updateProject(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:60',
            'stage'       => 'required|string|max:60',
            'location'    => 'nullable|string|max:160',
            'progress'    => 'required|integer|min:0|max:100',
            'color'       => 'nullable|string|max:9',
            'icon_path'   => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);
        $project->update($validated);
        return back()->with('success', 'Proyecto actualizado.');
    }

    public function deleteProject(Project $project)
    {
        $project->delete();
        return back()->with('success', 'Proyecto eliminado.');
    }

    /* ─────────────── Profile (admin) ─────────────── */

    public function editProfile()
    {
        return view('admin.crm.profile', [
            'user' => Auth::user(),
            'activeRoute' => 'crm.profile',
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validate([
            'first_name' => ['nullable', 'string', 'max:80'],
            'last_name'  => ['nullable', 'string', 'max:80'],
            'name'       => ['nullable', 'string', 'max:160'],
            'email'      => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'      => ['nullable', 'string', 'max:30'],
            'country'    => ['nullable', 'string', 'max:10'],
            'avatar'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
            'current_password' => ['nullable', 'required_with:password', 'string'],
            'locale'   => ['nullable', Rule::in(config('app.supported_locales', ['es', 'en']))],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        if (!empty($data['password'])) {
            if (!$user->password || !Hash::check($data['current_password'] ?? '', $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.'])->withInput();
            }
            $user->password = $data['password'];
        }

        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->first_name = $data['first_name'] ?? $user->first_name;
        $user->last_name  = $data['last_name']  ?? $user->last_name;
        $user->email      = $data['email'];
        $user->phone      = $data['phone']   ?? $user->phone;
        $user->country    = $data['country'] ?? $user->country;

        $composed = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $user->name = !empty($data['name']) ? $data['name'] : ($composed !== '' ? $composed : $user->name);

        $user->save();

        // Idioma y región: persistir en sesión + cookie (mismo mecanismo que LocaleController)
        // para que el middleware SetLocale lo aplique en el próximo request.
        if (!empty($data['locale'])) {
            $request->session()->put('locale', $data['locale']);
            \Illuminate\Support\Facades\Cookie::queue('app_locale', $data['locale'], 60 * 24 * 365);
        }
        if (!empty($data['timezone'])) {
            $request->session()->put('timezone', $data['timezone']);
            \Illuminate\Support\Facades\Cookie::queue('app_timezone', $data['timezone'], 60 * 24 * 365);
        }

        $flash = $request->boolean('redirect_settings') ? 'settings_success' : 'success';
        return back()->with($flash, 'Perfil actualizado correctamente.');
    }

    /**
     * Update another user's profile from the Usuarios (admin.profiles) page.
     * Mirrors the fields of editProfile() but targets an arbitrary user by id;
     * the admin can reset the password without knowing the current one.
     */
    public function updateUser(Request $request, $userId)
    {
        /** @var \App\Models\User $user */
        $user = User::findOrFail($userId);

        $data = $request->validate([
            'first_name' => ['nullable', 'string', 'max:80'],
            'last_name'  => ['nullable', 'string', 'max:80'],
            'name'       => ['nullable', 'string', 'max:160'],
            'email'      => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'      => ['nullable', 'string', 'max:30'],
            'country'    => ['nullable', 'string', 'max:10'],
            'role'       => ['nullable', 'in:user,admin'],
            'avatar'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
            'password'   => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (!empty($data['password'])) {
            $user->password = $data['password']; // hashed via casts
        }

        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->first_name = $data['first_name'] ?? $user->first_name;
        $user->last_name  = $data['last_name']  ?? $user->last_name;
        $user->email      = $data['email'];
        $user->phone      = $data['phone']   ?? $user->phone;
        $user->country    = $data['country'] ?? $user->country;
        if (!empty($data['role'])) {
            $user->role = $data['role'];
        }

        $composed = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $user->name = !empty($data['name']) ? $data['name'] : ($composed !== '' ? $composed : $user->name);

        $user->save();

        return back()->with('success', "Usuario {$user->name} actualizado correctamente.");
    }
}
