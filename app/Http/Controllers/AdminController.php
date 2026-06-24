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
use App\Models\CrmAutomationStep;
use App\Models\CrmChannelSetting;
use App\Models\ProjectCommunication;
use App\Models\ExportAuthorization;
use App\Support\UnitOptions;
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
        $unitOptions = UnitOptions::all();
        $amenityIconKeys = array_keys(UnitOptions::amenityIcons());
        return view('admin.units.units', compact('units', 'unitOptions', 'amenityIconKeys'));
    }

    /**
     * Guarda las listas globales editables de unidades (tipos, plantas, vistas,
     * direcciones y amenidades). Alimenta el formulario de unidades y los
     * filtros de la home a través de App\Support\UnitOptions.
     */
    public function updateUnitOptions(Request $request)
    {
        foreach (UnitOptions::CATEGORIES as $category) {
            $rows = $request->input($category, []);
            if (!is_array($rows)) {
                continue;
            }

            $clean = [];
            foreach ($rows as $row) {
                $label = trim((string) ($row['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                $value = trim((string) ($row['value'] ?? ''));
                if ($value === '') {
                    // Genera un slug estable a partir de la etiqueta.
                    $value = \Illuminate\Support\Str::slug($label, '_') ?: \Illuminate\Support\Str::random(6);
                }

                $entry = ['value' => $value, 'label' => $label];
                if ($category === 'amenities') {
                    $entry['icon'] = trim((string) ($row['icon'] ?? 'check')) ?: 'check';
                }
                $clean[] = $entry;
            }

            UnitOptions::put($category, $clean);
        }

        return redirect()->route('admin.units')->with('success', 'Configuración de opciones guardada.');
    }

    /**
     * Guarda el mapa de imágenes de plano (planta) por piso. Cada piso de
     * UnitOptions('floors') puede tener su propia foto del plano, que se
     * muestra en la vista "plan" de la home al seleccionar ese piso.
     *
     * Los archivos ya se subieron por chunks (uploadFloorPlanChunk); aquí sólo
     * llega el mapa valorDelPiso => ruta pública (/storage/...). Se guarda en
     * Setting('floor_plan_images') y se borran los archivos que dejaron de usarse.
     */
    public function updateFloorPlans(Request $request)
    {
        $floors = collect(UnitOptions::get('floors'))
            ->pluck('value')->filter()->map(fn ($v) => (string) $v)->all();

        $data = $request->validate([
            'plans'   => ['nullable', 'array'],
            'plans.*' => ['nullable', 'string', 'max:255'],
        ]);

        $previous = \App\Models\Setting::get('floor_plan_images', []) ?: [];

        $clean = [];
        foreach ($data['plans'] ?? [] as $floorKey => $path) {
            $floorKey = (string) $floorKey;
            $path     = trim((string) $path);
            // Sólo aceptamos pisos conocidos y rutas que nosotros generamos.
            if (!in_array($floorKey, $floors, true)) {
                continue;
            }
            if ($path === '' || !str_starts_with($path, '/storage/units/floor-plans/')) {
                continue;
            }
            $clean[$floorKey] = $path;
        }

        // Borrar archivos que ya no se usan (reemplazados o quitados).
        foreach ($previous as $floorKey => $oldPath) {
            if (($clean[$floorKey] ?? null) !== $oldPath) {
                $this->deleteFloorPlanFile((string) $oldPath);
            }
        }

        \App\Models\Setting::put('floor_plan_images', $clean);

        return response()->json([
            'success' => true,
            'message' => 'Planos de pisos actualizados.',
            'plans'   => $clean,
        ]);
    }

    /**
     * Recibe la imagen de un plano en trozos (~512 KB) para esquivar el límite
     * client_max_body_size de nginx (413). Mismo patrón que el menú del cliente.
     * Devuelve la ruta pública /storage/... cuando recibe el último chunk.
     */
    public function uploadFloorPlanChunk(Request $request)
    {
        $request->validate([
            'chunk'     => ['required', 'file', 'max:5120'], // 5 MB máx por chunk
            'upload_id' => ['required', 'string', 'max:64'],
            'index'     => ['required', 'integer', 'min:0'],
            'total'     => ['required', 'integer', 'min:1', 'max:1000'],
            'name'      => ['required', 'string', 'max:255'],
        ]);

        $uploadId = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $request->input('upload_id'));
        $index    = (int) $request->input('index');
        $total    = (int) $request->input('total');

        if ($uploadId === '') {
            return response()->json(['success' => false, 'message' => 'Identificador de subida inválido.'], 422);
        }

        $tmpDir = storage_path('app/tmp-floor-plans');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        $tmpPath = $tmpDir . DIRECTORY_SEPARATOR . $uploadId . '.part';

        // Anexar los bytes del chunk al temporal.
        $in  = fopen($request->file('chunk')->getRealPath(), 'rb');
        $out = fopen($tmpPath, $index === 0 ? 'wb' : 'ab');
        if ($in === false || $out === false) {
            return response()->json(['success' => false, 'message' => 'No se pudo procesar la imagen.'], 500);
        }
        stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);

        // Tope de 20 MB acumulado.
        if (filesize($tmpPath) > 20971520) {
            @unlink($tmpPath);
            return response()->json(['success' => false, 'message' => 'La imagen supera los 20 MB.'], 422);
        }

        // Chunks intermedios: confirmar y esperar el siguiente.
        if ($index + 1 < $total) {
            return response()->json(['success' => true, 'done' => false]);
        }

        // Último chunk: validar extensión de imagen y mover al disco público.
        $ext     = strtolower(pathinfo((string) $request->input('name'), PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            @unlink($tmpPath);
            return response()->json(['success' => false, 'message' => 'Formato de imagen no permitido.'], 422);
        }

        $finalRel = 'units/floor-plans/' . $uploadId . '.' . $ext;
        $stream   = fopen($tmpPath, 'rb');
        Storage::disk('public')->put($finalRel, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        @unlink($tmpPath);

        return response()->json([
            'success' => true,
            'done'    => true,
            'path'    => '/storage/' . $finalRel,
            'name'    => $request->input('name'),
        ]);
    }

    /** Borra el archivo físico de un plano (best-effort). */
    private function deleteFloorPlanFile(string $publicPath): void
    {
        $relative = ltrim(str_replace('/storage/', '', $publicPath), '/');
        if ($relative !== '' && Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
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
            'is_high_demand', 'is_second_chance', 'fully_furnished',
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
            'fully_furnished'       => 'boolean',
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

    /**
     * Borrado en lote de unidades seleccionadas en la tabla.
     */
    public function bulkDeleteUnits(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:units,id',
        ]);

        $count = Unit::whereIn('id', $data['ids'])->delete();

        return redirect()->route('admin.units')
            ->with('success', "{$count} unidad(es) eliminada(s) correctamente.");
    }

    public function togglePublicUnit(Unit $unit)
    {
        $unit->update(['public' => ! (bool) $unit->public]);
        return response()->json(['ok' => true, 'public' => (bool) $unit->public]);
    }

    /**
     * Descuentos masivos (bulk actions) sobre unidades.
     *
     * Alcance ($scope):
     *   - all       → todas las unidades.
     *   - group     → un grupo (por estado o por tipo) definido en $group_by/$group_value.
     *   - selected  → solo las unidades marcadas ($unit_ids).
     *
     * Modo ($mode):
     *   - amount   → fija el descuento (monto en USD) = $value.
     *   - percent  → descuento = precio × ($value / 100), por unidad.
     *
     * Con $clear = true se elimina el descuento (lo pone en 0) ignorando el monto.
     */
    public function bulkDiscount(Request $request)
    {
        $data = $request->validate([
            'scope'       => 'required|in:all,group,selected',
            'group_by'    => 'required_if:scope,group|nullable|in:status,type',
            'group_value' => 'required_if:scope,group|nullable|string|max:100',
            'unit_ids'    => 'required_if:scope,selected|nullable|array',
            'unit_ids.*'  => 'integer|exists:units,id',
            'mode'        => 'required|in:amount,percent',
            'value'       => 'required_unless:clear,1|nullable|numeric|min:0',
            'clear'       => 'nullable|boolean',
        ]);

        $clear = $request->boolean('clear');
        $value = (float) ($data['value'] ?? 0);

        if ($data['mode'] === 'percent' && $value > 100) {
            return back()->with('error', 'El porcentaje de descuento no puede superar 100%.');
        }

        $query = Unit::query();
        if ($data['scope'] === 'group') {
            $query->where($data['group_by'], $data['group_value']);
        } elseif ($data['scope'] === 'selected') {
            $query->whereIn('id', $data['unit_ids'] ?? []);
        }

        if ($clear) {
            $affected = (clone $query)->update(['discount' => 0]);
        } elseif ($data['mode'] === 'amount') {
            $affected = (clone $query)->update(['discount' => round($value, 2)]);
        } else {
            // Porcentaje: depende del precio de cada unidad, se calcula por fila.
            $affected = 0;
            (clone $query)->select('id', 'price')->chunkById(200, function ($units) use ($value, &$affected) {
                foreach ($units as $u) {
                    $u->update(['discount' => round(((float) $u->price) * $value / 100, 2)]);
                    $affected++;
                }
            });
        }

        $msg = $clear
            ? "Descuento eliminado en {$affected} unidad(es)."
            : ($data['mode'] === 'amount'
                ? 'Descuento de $' . number_format($value, 2) . " aplicado a {$affected} unidad(es)."
                : "Descuento de {$value}% aplicado a {$affected} unidad(es).");

        return redirect()->route('admin.units')->with('success', $msg);
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

        // ───────── Pestaña "Ventas y plataforma" ─────────
        $closedStatuses = ['CLOSED', 'COMPLETED', 'PAID', 'WON', 'closed', 'completed', 'paid', 'won'];

        $salesMonth  = (float) Deal::whereIn('status', $closedStatuses)
            ->where('deal_date', '>=', $now->copy()->startOfMonth())->sum('deal_price');
        $unitsMonth  = (int) Deal::whereIn('status', $closedStatuses)
            ->where('deal_date', '>=', $now->copy()->startOfMonth())->count();
        $salesTotal  = (float) Deal::whereIn('status', $closedStatuses)->sum('deal_price');
        $unitsTotal  = (int) Deal::whereIn('status', $closedStatuses)->count();

        // Pipeline: reservas activas (no canceladas/cerradas) y su valor
        $pipelineValue = (float) Deal::whereNotIn('status', $closedStatuses)
            ->whereNotIn('status', ['CANCELLED', 'LOST', 'cancelled', 'lost'])->sum('deal_price');
        $pipelineCount = (int) Deal::whereNotIn('status', $closedStatuses)
            ->whereNotIn('status', ['CANCELLED', 'LOST', 'cancelled', 'lost'])->count();

        // Cobros (pagos confirmados)
        $collectedMonth = (float) Payment::where('status', 'paid')
            ->where('paid_at', '>=', $now->copy()->startOfMonth())->sum('amount');
        $receivables    = (float) Payment::whereIn('status', ['pending', 'PENDING'])->sum('amount');
        $overdueAmount  = (float) Payment::whereIn('status', ['pending', 'PENDING'])
            ->whereNotNull('due_date')->where('due_date', '<', $now)->sum('amount');
        $overdueCount   = (int) Payment::whereIn('status', ['pending', 'PENDING'])
            ->whereNotNull('due_date')->where('due_date', '<', $now)->count();
        $overduePct     = $receivables > 0 ? round($overdueAmount / $receivables * 100) : 0;

        // Inventario por proyecto (conteos reales por estado de unidad)
        $inventory = Project::withCount([
                'units',
                'units as sold_count'     => fn ($q) => $q->whereIn('status', ['SOLD', 'sold']),
                'units as reserved_count' => fn ($q) => $q->whereIn('status', ['RESERVED', 'reserved']),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Project $p) use ($closedStatuses) {
                $available = max(0, $p->units_count - $p->sold_count - $p->reserved_count);
                $soldValue = (float) Deal::whereIn('status', $closedStatuses)
                    ->whereHas('unit', fn ($q) => $q->where('project_id', $p->id))->sum('deal_price');
                return [
                    'name'      => $p->name,
                    'type'      => $p->type,
                    'total'     => $p->units_count,
                    'sold'      => $p->sold_count,
                    'reserved'  => $p->reserved_count,
                    'available' => $available,
                    'progress'  => (int) $p->progress,
                    'value'     => $soldValue,
                ];
            });

        $invTotals = [
            'total'     => $inventory->sum('total'),
            'sold'      => $inventory->sum('sold'),
            'reserved'  => $inventory->sum('reserved'),
            'available' => $inventory->sum('available'),
            'value'     => $inventory->sum('value'),
        ];

        // Morosidad: cuotas vencidas (gestión)
        $overduePayments = Payment::with('reservation.unit')
            ->whereIn('status', ['pending', 'PENDING'])
            ->whereNotNull('due_date')->where('due_date', '<', $now)
            ->orderBy('due_date')->take(5)->get();

        return view('admin.estadisticas', compact(
            'activeUsers', 'totalUsers', 'newThisMonth',
            'viewsTotal', 'viewsThisMonth', 'popularUnits',
            'recentUsers', 'byCountry', 'byCountryTotal', 'trend',
            'salesMonth', 'unitsMonth', 'salesTotal', 'unitsTotal',
            'pipelineValue', 'pipelineCount', 'collectedMonth', 'receivables',
            'overdueAmount', 'overdueCount', 'overduePct',
            'inventory', 'invTotals', 'overduePayments'
        ));
    }

    public function transactionsReport(Request $request)
    {
        $tab = $request->get('tab', 'todos');
        $search = $request->get('search');
        $unitId = $request->get('unit_id');
        $method = $request->get('method');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $baseQuery = Payment::query()->with('reservation.unit');
        $this->scopePaymentQueryForBroker($baseQuery);
        $this->applyPaymentFilters($baseQuery, $request);

        $paymentsQuery = clone $baseQuery;
        match ($tab) {
            'confirmados' => $paymentsQuery->where('status', 'paid'),
            'pendientes'  => $paymentsQuery->where('status', 'pending'),
            'vencidos'    => $paymentsQuery->where('status', 'overdue'),
            default       => null,
        };

        $payments = $paymentsQuery
            ->orderByRaw('COALESCE(paid_at, due_date, created_at) desc')
            ->paginate(40)
            ->withQueryString();

        $totalCobrado = (clone $baseQuery)->where('status', 'paid')->sum('amount');
        $pendienteCobro = (clone $baseQuery)->where('status', 'pending')->sum('amount');
        $pagosVencidos = (clone $baseQuery)->where('status', 'overdue')->sum('amount');
        $countPaid = (clone $baseQuery)->where('status', 'paid')->count();
        $countPending = (clone $baseQuery)->where('status', 'pending')->count();
        $countOverdue = (clone $baseQuery)->where('status', 'overdue')->count();

        $units = $this->crmUnitsForUser();
        $methodsQuery = Payment::query()
            ->whereNotNull('payment_method')
            ->distinct();
        $this->scopePaymentQueryForBroker($methodsQuery);
        $methods = $methodsQuery
            ->orderBy('payment_method')
            ->pluck('payment_method')
            ->filter()
            ->values();

        return view('admin.transactions-report', compact(
            'payments',
            'totalCobrado',
            'pendienteCobro',
            'pagosVencidos',
            'countPaid',
            'countPending',
            'countOverdue',
            'tab',
            'search',
            'unitId',
            'method',
            'dateFrom',
            'dateTo',
            'units',
            'methods'
        ));
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

        // ──────────────────────────────────────────────────────────────────
        // Escritorio mejorado: bandeja unificada, riesgo, vencimientos, carga.
        // Todo proviene de datos reales (Document/Approval/Task/Payment/Unit).
        // ──────────────────────────────────────────────────────────────────
        $contractTypes = ['payment_plan', 'purchase_promise', 'contract', 'promise'];

        // Un solo pase sobre los expedientes activos con sus relaciones, para
        // derivar "sin asesor", "carga por asesor" y "en riesgo" sin N+1.
        $activeReservations = (clone $reservationsBase)
            ->with(['unit.agent', 'documents', 'payments'])
            ->get();

        // Desglose de KPIs (el número de cabecera = suma exacta de su desglose)
        $stats['docs_revisar'] = (clone $docsBase)->whereIn('status', ['pending', 'generated'])
            ->where(fn ($q) => $q->whereNotIn('document_type', $contractTypes)->orWhereNull('document_type'))->count();
        $stats['docs_firmar'] = (clone $docsBase)->whereIn('status', ['pending', 'generated'])
            ->whereIn('document_type', $contractTypes)->count();

        $stats['aprob_kyc']      = $isBroker ? 0 : Approval::where('status', 'pendiente')->where('type', 'kyc')->count();
        $stats['aprob_contrato'] = $isBroker ? 0 : Approval::where('status', 'pendiente')->whereIn('type', ['contrato', 'promesa'])->count();
        $stats['aprob_broker']   = $isBroker ? 0 : Approval::where('status', 'pendiente')->whereIn('type', ['broker', 'comision'])->count();
        $stats['aprob_otros']    = max(0, ($stats['aprobaciones_cola'] ?? 0) - $stats['aprob_kyc'] - $stats['aprob_contrato'] - $stats['aprob_broker']);

        $stats['sin_asesor'] = $activeReservations->filter(fn ($r) => $r->unit && !$r->unit->agent_id)->count();

        // ── Bandeja de trabajo (cola priorizada por antigüedad) ──
        $bandeja = collect();

        // 1) Documentos pendientes (revisar / firmar / KYC)
        $pendingDocs = (clone $docsBase)->with('reservation.unit')
            ->whereIn('status', ['pending', 'generated'])
            ->orderBy('updated_at')->take(40)->get();
        foreach ($pendingDocs as $d) {
            $isKyc      = $d->document_type === 'kyc';
            $isContract = in_array($d->document_type, $contractTypes, true);
            $r          = $d->reservation;
            $cliente    = $r ? trim(($r->first_name ?? '').' '.($r->last_name ?? '')) : __('Cliente');
            $unidad     = optional($r?->unit)->name ?? optional($r?->unit)->custom_id ?? __('Sin unidad');
            $bandeja->push((object) [
                'cat'    => $isKyc ? 'kyc' : ($isContract ? 'contrato' : 'documento'),
                'ty'     => $isKyc ? 'KYC' : ($isContract ? 'Contrato' : 'Documento'),
                'title'  => ($d->title ?: ucfirst((string) ($d->document_type ?? __('Documento')))).' — '.($isKyc ? __('verificación de identidad') : __('por revisar')),
                'sub'    => trim($cliente.' · '.$unidad.($r?->reservation_code ? ' · '.$r->reservation_code : ''), ' ·'),
                'date'   => $d->updated_at ?? $d->created_at,
                'url'    => $r ? route('admin.crm.expediente.detalle', $r->id) : route('admin.crm.documentos'),
                'action' => __('Revisar'),
            ]);
        }

        // 2) Aprobaciones pendientes (broker / comisión / otras)
        if (!$isBroker) {
            foreach (Approval::with('reservation')->where('status', 'pendiente')->orderBy('created_at')->take(20)->get() as $a) {
                $t   = strtolower((string) ($a->type ?? ''));
                $cat = $t === 'kyc' ? 'kyc' : (in_array($t, ['contrato', 'promesa']) ? 'contrato' : (in_array($t, ['broker', 'comision']) ? 'broker' : 'documento'));
                $bandeja->push((object) [
                    'cat'    => $cat,
                    'ty'     => ['kyc' => 'KYC', 'contrato' => 'Contrato', 'broker' => 'Broker', 'documento' => 'Documento'][$cat],
                    'title'  => ($a->requested_by ?: __('Solicitud')).' — '.($a->amount_or_condition ?? ucfirst((string) ($a->type ?? __('aprobación')))),
                    'sub'    => $a->notes ?? __('Pendiente de aprobación'),
                    'date'   => $a->created_at,
                    'url'    => route('admin.crm.aprobaciones'),
                    'action' => __('Revisar'),
                ]);
            }
        }

        // 3) Expedientes sin asesor (la unidad no tiene agente asignado)
        foreach ($activeReservations->filter(fn ($r) => $r->unit && !$r->unit->agent_id)->sortByDesc('created_at')->take(6) as $r) {
            $cliente = trim(($r->first_name ?? '').' '.($r->last_name ?? '')) ?: __('Lead');
            $bandeja->push((object) [
                'cat'    => 'noadv',
                'ty'     => __('Sin asesor'),
                'title'  => $cliente.' — '.__('sin asesor asignado'),
                'sub'    => (optional($r->unit)->name ?? __('Sin unidad')).($r->reservation_code ? ' · '.$r->reservation_code : ''),
                'date'   => $r->created_at,
                'url'    => route('admin.crm.expediente.detalle', $r->id),
                'action' => __('Asignar'),
            ]);
        }

        // 4) Tareas vencidas / de hoy
        if (!$isBroker) {
            foreach (Task::with('reservation')->where('status', '!=', 'completada')
                        ->whereNotNull('due_date')->whereDate('due_date', '<=', today())
                        ->orderBy('due_date')->take(10)->get() as $t) {
                $bandeja->push((object) [
                    'cat'    => 'tarea',
                    'ty'     => __('Tarea'),
                    'title'  => $t->title,
                    'sub'    => trim(($t->responsible ? $t->responsible.' · ' : '').($t->area ?? __('Tarea')), ' ·'),
                    'date'   => $t->due_date,
                    'url'    => route('admin.crm.tareas'),
                    'action' => __('Resolver'),
                ]);
            }
        }

        $bandejaTotal = $bandeja->count();
        $bandeja = $bandeja->sortBy(fn ($i) => $i->date ?? now())->take(14)->values();

        // ── En riesgo (lo que se puede enfriar) ──
        $riesgo = collect();

        // Reservas a punto de vencer
        foreach ($activeReservations->filter(fn ($r) => $r->expires_at && $r->expires_at->isFuture()
                    && $r->expires_at->lte(now()->addDays(3))
                    && !in_array($r->status, ['completed', 'cancelled', 'expired']))
                ->sortBy('expires_at')->take(3) as $r) {
            $riesgo->push((object) [
                'level' => 'r1', 'icon' => 'pi-clock',
                'title' => __('Reserva de :name vence :when', ['name' => trim(($r->first_name ?? '').' '.($r->last_name ?? '')), 'when' => $r->expires_at->diffForHumans()]),
                'sub'   => (optional($r->unit)->name ?? '—').' · '.__('depósito sin confirmar'),
                'url'   => route('admin.crm.expediente.detalle', $r->id),
            ]);
        }

        // Pagos vencidos
        $overdueQuery = Payment::with('reservation.unit')->where('status', 'overdue');
        if ($isBroker) $overdueQuery->whereHas('reservation', fn ($q) => $q->whereIn('unit_id', $brokerUnitIds));
        foreach ($overdueQuery->orderBy('due_date')->take(3)->get() as $p) {
            $r = $p->reservation;
            $riesgo->push((object) [
                'level' => 'r1', 'icon' => 'pi-dollar',
                'title' => __('Cuota vencida de :name', ['name' => $r ? trim(($r->first_name ?? '').' '.($r->last_name ?? '')) : __('cliente')]),
                'sub'   => (optional($r?->unit)->name ?? '—').' · $'.number_format((float) $p->amount, 0).' · '.($p->label ?? __('pago')),
                'url'   => $r ? route('admin.crm.expediente.detalle', $r->id) : route('admin.crm.expedientes'),
            ]);
        }

        // Expedientes estancados (sin actividad ≥ 9 días)
        foreach ($activeReservations->filter(fn ($r) => $r->updated_at && $r->updated_at->lt(now()->subDays(9))
                    && !in_array($r->status, ['completed', 'cancelled']))
                ->sortBy('updated_at')->take(2) as $r) {
            $riesgo->push((object) [
                'level' => 'r2', 'icon' => 'pi-search',
                'title' => __('Expediente sin actividad :when', ['when' => $r->updated_at->diffForHumans()]),
                'sub'   => trim(($r->first_name ?? '').' '.($r->last_name ?? '')).' · '.(optional($r->unit)->name ?? '—'),
                'url'   => route('admin.crm.expediente.detalle', $r->id),
            ]);
        }
        $riesgo = $riesgo->take(5);

        // ── Próximos vencimientos (cuotas por cobrar) ──
        $vencQuery = Payment::with('reservation.unit')->where('status', 'pending')
            ->whereNotNull('due_date')->whereDate('due_date', '>=', today());
        if ($isBroker) $vencQuery->whereHas('reservation', fn ($q) => $q->whereIn('unit_id', $brokerUnitIds));
        $vencimientos = $vencQuery->orderBy('due_date')->take(5)->get();

        // ── Sin asesor + carga por asesor ──
        $sinAsesor = $activeReservations->filter(fn ($r) => $r->unit && !$r->unit->agent_id)
            ->sortByDesc('created_at')->take(4)->values();

        $loadByAgent   = $activeReservations->groupBy(fn ($r) => optional($r->unit)->agent_id);
        $cargaAsesores = ($isBroker ? collect() : Agent::where('active', true)->get())
            ->map(fn ($a) => (object) ['name' => $a->name, 'count' => optional($loadByAgent->get($a->id))->count() ?? 0])
            ->sortByDesc('count')->values();
        $maxCarga = max(1, (int) ($cargaAsesores->max('count') ?? 1));

        return view('admin.crm.dashboard', compact(
            'stats', 'proyectos', 'expedientesRecientes', 'aprobacionesUrgentes', 'tareasHoy',
            'bandeja', 'bandejaTotal', 'riesgo', 'vencimientos', 'sinAsesor', 'cargaAsesores', 'maxCarga'
        ));
    }

    public function crmExpedientes(Request $request)
    {
        $tab = $request->get('tab', 'todos');
        $search = $request->get('search');
        $unitId = $request->get('unit_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $query = Reservation::with(['unit', 'documents', 'payments']);

        $this->scopeReservationQueryForBroker($query);

        $reservations = $query
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('phone', 'like', "%$search%")
                      ->orWhere('reservation_code', 'like', "%$search%")
                      ->orWhereHas('unit', function ($u) use ($search) {
                          $u->where('name', 'like', "%$search%")
                            ->orWhere('custom_id', 'like', "%$search%");
                      });
                });
            })
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($tab === 'kyc', function ($q) {
                $q->whereHas('documents', function ($d) {
                    $d->where('document_type', 'kyc')
                      ->whereIn('status', ['pending', 'generated']);
                });
            })
            ->when($tab === 'firma', function ($q) {
                $q->whereHas('documents', function ($d) {
                    $d->whereIn('document_type', ['payment_plan', 'purchase_promise', 'contract', 'promise'])
                      ->whereIn('status', ['pending', 'generated']);
                });
            })
            ->when($tab === 'vencido', fn ($q) => $q->whereHas('payments', fn ($p) => $p->where('status', 'overdue')))
            ->when($tab === 'al-dia', fn ($q) => $q->whereDoesntHave('payments', fn ($p) => $p->where('status', 'overdue')))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $units   = $this->crmUnitsForUser();
        $clients = User::where('role', 'user')->orderBy('name')->get();
        $advisors = Agent::orderBy('name')->pluck('name', 'id');

        return view('admin.crm.expedientes', compact(
            'reservations',
            'tab',
            'search',
            'unitId',
            'dateFrom',
            'dateTo',
            'units',
            'clients',
            'advisors'
        ));
    }

    /**
     * Elimina un expediente (reserva). Pagos, documentos y mensajes se borran
     * en cascada por las claves foráneas de sus tablas.
     */
    public function deleteExpediente(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('admin.crm.expedientes')
            ->with('success', 'Expediente eliminado correctamente.');
    }

    /**
     * Borrado en lote de expedientes seleccionados en la tabla.
     */
    public function bulkDeleteExpedientes(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:reservations,id',
        ]);

        $count = Reservation::whereIn('id', $data['ids'])->delete();

        return redirect()->route('admin.crm.expedientes')
            ->with('success', "{$count} expediente(s) eliminado(s) correctamente.");
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

    public function crmContratos(Request $request)
    {
        $tab = $request->get('tab', 'todos');
        $search = $request->get('search');
        $unitId = $request->get('unit_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $contractTypes = ['contract', 'promise', 'purchase_promise'];

        $baseQuery = Reservation::query()->with(['unit', 'documents', 'payments']);
        $this->scopeReservationQueryForBroker($baseQuery);

        $kpiQuery = clone $baseQuery;

        $query = clone $baseQuery;
        $query
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('reservation_code', 'like', "%$search%")
                      ->orWhereHas('unit', function ($u) use ($search) {
                          $u->where('name', 'like', "%$search%")
                            ->orWhere('custom_id', 'like', "%$search%");
                      });
                });
            })
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($tab === 'reservas', fn ($q) => $q->whereDoesntHave('documents', fn ($d) => $d->whereIn('document_type', $contractTypes)))
            ->when($tab === 'contratos', fn ($q) => $q->whereHas('documents', fn ($d) => $d->whereIn('document_type', $contractTypes)))
            ->when($tab === 'por-firmar', fn ($q) => $q->whereHas('documents', fn ($d) => $d->whereIn('document_type', $contractTypes)->whereIn('status', ['pending', 'generated'])))
            ->when($tab === 'pago-vencido', fn ($q) => $q->whereHas('payments', fn ($p) => $p->where('status', 'overdue')));

        $reservations = $query
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        $reservasCount = (clone $kpiQuery)->count();
        $countContratos = (clone $kpiQuery)->whereHas('documents', fn ($d) => $d->whereIn('document_type', $contractTypes))->count();
        $porFirmar = (clone $kpiQuery)->whereHas('documents', fn ($d) => $d->whereIn('document_type', $contractTypes)->whereIn('status', ['pending', 'generated']))->count();
        $pagoVencido = (clone $kpiQuery)->whereHas('payments', fn ($p) => $p->where('status', 'overdue'))->count();
        $firmados = (clone $kpiQuery)->where(function ($q) use ($contractTypes) {
            $q->whereIn('status', ['contract_signed', 'signed'])
              ->orWhereHas('documents', fn ($d) => $d->whereIn('document_type', $contractTypes)->whereIn('status', ['signed', 'approved']));
        })->count();

        $units   = $this->crmUnitsForUser();
        $clients = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.crm.contratos', compact(
            'reservations',
            'tab',
            'search',
            'unitId',
            'dateFrom',
            'dateTo',
            'reservasCount',
            'countContratos',
            'porFirmar',
            'pagoVencido',
            'firmados',
            'units',
            'clients'
        ));
    }

    private function scopeReservationQueryForBroker($query)
    {
        if (Auth::user()->role === 'broker') {
            $unitIds = Auth::user()->assignedUnits()->pluck('units.id')->map(fn ($id) => (string) $id)->all();
            $query->whereIn('unit_id', $unitIds);
        }

        return $query;
    }

    private function scopePaymentQueryForBroker($query)
    {
        if (Auth::user()->role === 'broker') {
            $unitIds = Auth::user()->assignedUnits()->pluck('units.id')->all();
            $query->whereHas('reservation', fn ($q) => $q->whereIn('unit_id', $unitIds));
        }

        return $query;
    }

    private function crmUnitsForUser()
    {
        $query = Unit::query()->orderBy('custom_id');

        if (Auth::user()->role === 'broker') {
            $unitIds = Auth::user()->assignedUnits()->pluck('units.id')->all();
            $query->whereIn('id', $unitIds);
        }

        return $query->get();
    }

    private function applyPaymentFilters($query, Request $request)
    {
        $search = $request->get('search');

        return $query
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('label', 'like', "%$search%")
                      ->orWhere('payment_type', 'like', "%$search%")
                      ->orWhere('payment_method', 'like', "%$search%")
                      ->orWhere('notes', 'like', "%$search%")
                      ->orWhereHas('reservation', function ($r) use ($search) {
                          $r->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%")
                            ->orWhere('reservation_code', 'like', "%$search%")
                            ->orWhereHas('unit', function ($u) use ($search) {
                                $u->where('name', 'like', "%$search%")
                                  ->orWhere('custom_id', 'like', "%$search%");
                            });
                      });
                });
            })
            ->when($request->get('unit_id'), function ($q, $unitId) {
                $q->whereHas('reservation', fn ($r) => $r->where('unit_id', $unitId));
            })
            ->when($request->get('method'), fn ($q, $method) => $q->where('payment_method', $method))
            ->when($request->get('date_from'), function ($q, $dateFrom) {
                $q->whereDate(DB::raw('COALESCE(paid_at, due_date)'), '>=', $dateFrom);
            })
            ->when($request->get('date_to'), function ($q, $dateTo) {
                $q->whereDate(DB::raw('COALESCE(paid_at, due_date)'), '<=', $dateTo);
            });
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
            'payment_start_date' => 'nullable|date',
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
            'payment_start_date' => $validated['payment_start_date'] ?? null,
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

    /**
     * Accumulate one chunk of a manually-uploaded signed document into a temp file.
     * The front sends the file in ~512 KB parts to stay under nginx/PHP body limits
     * (avoids 413 / "Too Large"). Returns:
     *   ['status' => 'partial']                       → more chunks expected
     *   ['status' => 'error', 'message' => ...]       → validation/IO failure
     *   ['status' => 'complete', 'tmpPath','ext','name'] → last chunk assembled
     */
    private function receiveSignedDocChunk(Request $request): array
    {
        $request->validate([
            'chunk'     => ['required', 'file', 'max:5120'], // 5 MB máx por chunk
            'upload_id' => ['required', 'string', 'max:64'],
            'index'     => ['required', 'integer', 'min:0'],
            'total'     => ['required', 'integer', 'min:1', 'max:2000'],
            'name'      => ['required', 'string', 'max:255'],
        ]);

        $uploadId = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $request->input('upload_id'));
        $index    = (int) $request->input('index');
        $total    = (int) $request->input('total');

        if ($uploadId === '') {
            return ['status' => 'error', 'message' => 'Identificador de subida inválido.'];
        }

        $tmpDir = storage_path('app/tmp-signed-docs');
        if (! is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        $tmpPath = $tmpDir . DIRECTORY_SEPARATOR . $uploadId . '.part';

        // Anexar los bytes del chunk al temporal.
        $in  = fopen($request->file('chunk')->getRealPath(), 'rb');
        $out = fopen($tmpPath, $index === 0 ? 'wb' : 'ab');
        if ($in === false || $out === false) {
            return ['status' => 'error', 'message' => 'No se pudo procesar el archivo.'];
        }
        stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);

        // Tope de 50 MB acumulado.
        if (filesize($tmpPath) > 52428800) {
            @unlink($tmpPath);
            return ['status' => 'error', 'message' => 'El archivo supera los 50 MB.'];
        }

        // Chunks intermedios: esperar el siguiente.
        if ($index + 1 < $total) {
            return ['status' => 'partial'];
        }

        // Último chunk: validar extensión.
        $ext     = strtolower(pathinfo((string) $request->input('name'), PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (! in_array($ext, $allowed, true)) {
            @unlink($tmpPath);
            return ['status' => 'error', 'message' => 'Formato de archivo no permitido.'];
        }

        return ['status' => 'complete', 'tmpPath' => $tmpPath, 'ext' => $ext, 'name' => (string) $request->input('name')];
    }

    /**
     * Move the assembled temp file to the public "contracts" disk and return its path.
     */
    private function storeSignedDocFromTmp(string $tmpPath, string $finalRel): string
    {
        $stream = fopen($tmpPath, 'rb');
        \Illuminate\Support\Facades\Storage::disk('public')->put($finalRel, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        @unlink($tmpPath);

        return $finalRel;
    }

    /**
     * Admin uploads the signed payment plan manually (by chunks), skipping the client
     * confirmation flow. Creates the payment_plan document if needed, marks it signed,
     * and unlocks the rest of the pipeline (payment schedule, budget locked as approved).
     */
    public function uploadSignedPaymentPlan(Request $request, Reservation $reservation)
    {
        $res = $this->receiveSignedDocChunk($request);
        if ($res['status'] === 'error')   return response()->json(['success' => false, 'message' => $res['message']], 422);
        if ($res['status'] === 'partial') return response()->json(['success' => true, 'done' => false]);

        $finalRel = $this->storeSignedDocFromTmp(
            $res['tmpPath'],
            'contracts/payment_plan_'.$reservation->id.'_'.time().'.'.$res['ext'],
        );

        // Get or create the payment_plan document for this reservation.
        $document = \App\Services\DocumentService::generatePaymentPlan($reservation);

        $document->update([
            'file_path'    => $finalRel,
            'filename'     => $res['name'],
            'generated_at' => $document->generated_at ?? now(),
            'notes'        => json_encode([
                'signer_name'      => 'Subido manualmente por el asesor',
                'manual_upload'    => true,
                'uploaded_by'      => Auth::user()?->name ?? 'Asesor',
                'signed_server_at' => now()->toIso8601String(),
            ]),
        ]);

        // Mark as signed → triggers payment schedule generation and status promotion.
        \App\Services\DocumentService::signDocument($document, Auth::id(), $document->notes);

        // Ensure the purchase promise document exists so the contract card becomes available.
        \App\Services\DocumentService::generatePurchasePromise($reservation);

        // Lock the budget as accepted so the admin form closes and the client flow is bypassed.
        $observations = $reservation->budget_observations ?? [];
        $observations[] = [
            'from'    => 'admin',
            'author'  => Auth::user()?->name ?? 'Asesor',
            'message' => 'Subió el plan de pagos firmado manualmente (se saltó la confirmación del cliente).',
            'kind'    => 'accept',
            'at'      => now()->toIso8601String(),
        ];
        $reservation->update([
            'budget_status'        => 'approved',
            'budget_sent_at'       => $reservation->budget_sent_at ?? now(),
            'budget_observations'  => $observations,
        ]);

        return response()->json([
            'success' => true,
            'done'    => true,
            'message' => 'Plan de pagos firmado subido y aprobado.',
        ]);
    }

    /**
     * Admin uploads the signed contract / purchase promise manually (by chunks),
     * skipping the client confirmation flow, and marks it as approved.
     */
    public function uploadSignedContract(Request $request, Document $document)
    {
        if (! in_array($document->document_type, ['purchase_promise', 'contract'])) {
            return response()->json(['success' => false, 'message' => 'Tipo de documento inválido.'], 422);
        }

        $res = $this->receiveSignedDocChunk($request);
        if ($res['status'] === 'error')   return response()->json(['success' => false, 'message' => $res['message']], 422);
        if ($res['status'] === 'partial') return response()->json(['success' => true, 'done' => false]);

        $finalRel = $this->storeSignedDocFromTmp(
            $res['tmpPath'],
            'contracts/'.$document->document_type.'_'.$document->reservation_id.'_'.time().'.'.$res['ext'],
        );

        $meta = $document->metadata ?? [];
        $obs  = $meta['observations'] ?? [];
        $obs[] = [
            'from'    => 'admin',
            'author'  => Auth::user()?->name ?? 'Asesor',
            'message' => 'Subió la versión firmada manualmente (se saltó la confirmación del cliente): '.$res['name'],
            'kind'    => 'upload',
            'at'      => now()->toIso8601String(),
        ];
        $meta['observations'] = $obs;
        $meta['accepted_at']  = now()->toIso8601String();

        $document->update([
            'file_path'    => $finalRel,
            'filename'     => $res['name'],
            'generated_at' => $document->generated_at ?? now(),
            'status'       => 'approved',
            'signed_at'    => now(),
            'signed_by'    => Auth::id(),
            'approved_at'  => now(),
            'approved_by'  => Auth::id(),
            'metadata'     => $meta,
            'notes'        => json_encode([
                'signer_name'      => 'Subido manualmente por el asesor',
                'manual_upload'    => true,
                'uploaded_by'      => Auth::user()?->name ?? 'Asesor',
                'signed_server_at' => now()->toIso8601String(),
            ]),
        ]);

        // Promote the reservation once payment plan and purchase promise are both finalized.
        $reservation = $document->reservation;
        $planDone = (bool) $reservation->documents()
            ->where('document_type', 'payment_plan')
            ->whereIn('status', ['signed', 'approved'])->first();
        $promiseDone = (bool) $reservation->documents()
            ->where('document_type', 'purchase_promise')
            ->whereIn('status', ['signed', 'approved'])->first();
        if ($planDone && $promiseDone && ! in_array($reservation->status, ['contract_signed', 'signed'])) {
            $reservation->update(['status' => 'contract_signed']);
        }

        return response()->json([
            'success' => true,
            'done'    => true,
            'message' => 'Documento firmado subido y aprobado.',
        ]);
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

        $automations = CrmAutomation::with('template', 'steps.template')->orderBy('name')->get();

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
        $validated = $this->validateAutomation($request, true);

        $automation = null;
        DB::transaction(function () use ($validated, &$automation) {
            $automation = CrmAutomation::create($this->automationAttributes($validated));
            $this->syncAutomationSteps($automation, $validated['steps']);
        });

        return back()->with('success', 'Automatización creada.');
    }

    public function updateAutomation(Request $request, CrmAutomation $automation)
    {
        $validated = $this->validateAutomation($request, false);

        DB::transaction(function () use ($validated, $automation) {
            $automation->update($this->automationAttributes($validated));
            $this->syncAutomationSteps($automation, $validated['steps']);
        });

        return back()->with('success', 'Automatización actualizada.');
    }

    /** Valida nombre/disparador + la cadena de pasos del flujo. */
    private function validateAutomation(Request $request, bool $creating): array
    {
        return $request->validate([
            'name'                  => 'required|string|max:160',
            'description'           => 'nullable|string|max:1000',
            'trigger_event'         => 'required|string|max:80',
            'is_active'             => 'nullable|boolean',
            'steps'                 => 'required|array|min:1',
            'steps.*.template_id'   => 'nullable|exists:crm_templates,id',
            'steps.*.delay_minutes' => 'required|integer|min:0|max:43200',
            'steps.*.channels'      => 'required|array|min:1',
            'steps.*.channels.*'    => 'in:email,whatsapp,sms,push',
        ]);
    }

    /** Atributos planos del flujo; reflejan el primer paso para listados y compatibilidad. */
    private function automationAttributes(array $validated): array
    {
        $first = $validated['steps'][0] ?? [];

        return [
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'trigger_event' => $validated['trigger_event'],
            'is_active'     => (bool) ($validated['is_active'] ?? false),
            'template_id'   => $first['template_id'] ?? null,
            'delay_minutes' => (int) ($first['delay_minutes'] ?? 0),
            'channels'      => $first['channels'] ?? ['email'],
        ];
    }

    /** Recrea los pasos de la cadena respetando el orden enviado. */
    private function syncAutomationSteps(CrmAutomation $automation, array $steps): void
    {
        $automation->steps()->delete();

        $position = 1;
        foreach (array_values($steps) as $step) {
            $automation->steps()->create([
                'position'      => $position++,
                'template_id'   => $step['template_id'] ?? null,
                'delay_minutes' => (int) ($step['delay_minutes'] ?? 0),
                'channels'      => array_values($step['channels'] ?? ['email']),
            ]);
        }
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
        $steps = $automation->load('steps.template')->resolvedSteps()
            ->filter(fn ($s) => $s->template);

        if ($steps->isEmpty()) {
            return back()->with('error', 'El flujo no tiene ninguna fase con plantilla asignada.');
        }

        // Ejecución manual: envía una muestra de cada fase de la cadena al correo
        // del admin (no dispara envíos masivos a clientes reales). Respeta los
        // retrasos acumulados para reproducir la secuencia real.
        $to = Auth::user()->email;
        $cumulativeDelay = 0;
        $position = 0;

        try {
            foreach ($steps as $step) {
                $position++;
                $cumulativeDelay += (int) ($step->delay_minutes ?? 0);
                $template = $step->template;

                $rendered = \App\Support\CrmTemplateRenderer::render(
                    $template,
                    \App\Support\CrmTemplateRenderer::SAMPLE
                );
                $subject = "[FLUJO · Paso {$position}/{$steps->count()}] " . $rendered['subject'];
                $mail = new \App\Mail\CrmTemplateMail($subject, $rendered['html']);

                if ($cumulativeDelay > 0) {
                    \Illuminate\Support\Facades\Mail::to($to)->later(now()->addMinutes($cumulativeDelay), $mail);
                } else {
                    \Illuminate\Support\Facades\Mail::to($to)->send($mail);
                }

                $template->forceFill([
                    'last_used_at' => now(),
                    'usage_count'  => ($template->usage_count ?? 0) + 1,
                ])->save();
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo ejecutar el flujo: ' . $e->getMessage());
        }

        $automation->forceFill([
            'last_run_at' => now(),
            'run_count'   => ($automation->run_count ?? 0) + 1,
        ])->save();

        return back()->with('success', "Flujo ejecutado: {$steps->count()} fase(s) enviada(s) a {$to}.");
    }

    public function getAutomation(CrmAutomation $automation)
    {
        $automation->load('template', 'steps.template');

        $data = $automation->toArray();
        $data['steps'] = $automation->resolvedSteps()->map(fn ($s) => [
            'position'      => $s->position,
            'template_id'   => $s->template_id,
            'delay_minutes' => (int) ($s->delay_minutes ?? 0),
            'channels'      => $s->channels ?: ['email'],
        ])->values();

        return response()->json($data);
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

    /* =====================================================================
     | Control de comunicaciones por proyecto
     | Matriz proyecto × tipo × canal. Cada proyecto nace en silencio total.
     ===================================================================== */
    public function crmComunicaciones()
    {
        $catalog  = config('crm_communications');
        $projects = Project::orderBy('name')->get();

        // Mapa: [project_id][comm_code][channel] => bool
        $config = [];
        foreach ($projects as $project) {
            $config[$project->id] = [];
            foreach ($catalog['families'] as $family) {
                foreach ($family['types'] as $type) {
                    $config[$project->id][$type['code']] = [];
                    foreach ($type['ch'] as $channel) {
                        $config[$project->id][$type['code']][$channel] = false;
                    }
                }
            }
        }
        foreach (ProjectCommunication::all() as $row) {
            if (isset($config[$row->project_id][$row->comm_code][$row->channel])) {
                $config[$row->project_id][$row->comm_code][$row->channel] = $row->enabled;
            }
        }

        return view('admin.crm.comunicaciones', compact('catalog', 'projects', 'config'));
    }

    public function toggleCommunication(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'code'       => 'required|string',
            'channel'    => 'required|in:email,whatsapp,inapp',
            'enabled'    => 'required|boolean',
        ]);

        if (!$this->commTypeSupportsChannel($data['code'], $data['channel'])) {
            return response()->json(['ok' => false, 'message' => 'Canal no soportado para este tipo.'], 422);
        }

        ProjectCommunication::updateOrCreate(
            ['project_id' => $data['project_id'], 'comm_code' => $data['code'], 'channel' => $data['channel']],
            ['enabled' => $data['enabled']]
        );

        return response()->json(['ok' => true]);
    }

    public function updateCommunicationMaster(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'active'     => 'required|boolean',
        ]);

        Project::whereKey($data['project_id'])->update(['comms_active' => $data['active']]);

        return response()->json(['ok' => true, 'active' => (bool) $data['active']]);
    }

    public function updateCommunicationStart(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'date'       => 'nullable|date',
        ]);

        Project::whereKey($data['project_id'])->update(['comms_start_date' => $data['date'] ?: null]);

        return response()->json(['ok' => true]);
    }

    public function copyCommunicationConfig(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id|different:source_id',
            'source_id'  => 'required|exists:projects,id',
        ]);

        DB::transaction(function () use ($data) {
            ProjectCommunication::where('project_id', $data['project_id'])->delete();

            $rows = ProjectCommunication::where('project_id', $data['source_id'])
                ->get()
                ->map(fn ($r) => [
                    'project_id' => $data['project_id'],
                    'comm_code'  => $r->comm_code,
                    'channel'    => $r->channel,
                    'enabled'    => $r->enabled,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all();

            if ($rows) ProjectCommunication::insert($rows);

            Project::whereKey($data['project_id'])->update(['comms_active' => true]);
        });

        return response()->json(['ok' => true]);
    }

    private function commTypeSupportsChannel(string $code, string $channel): bool
    {
        foreach (config('crm_communications.families', []) as $family) {
            if (!empty($family['locked'])) continue;
            foreach ($family['types'] as $type) {
                if ($type['code'] === $code) {
                    return in_array($channel, $type['ch'], true);
                }
            }
        }
        return false;
    }
    public function crmProyectoDetalle($id)
    {
        $proyecto = Project::withCount([
            'units',
            'units as sold_count'      => fn($q) => $q->where('status', 'SOLD'),
            'units as reserved_count'  => fn($q) => $q->where('status', 'RESERVED'),
            'units as available_count' => fn($q) => $q->where('status', 'AVAILABLE'),
        ])->findOrFail($id);
        $units = $proyecto->units()->orderBy('custom_id')->orderBy('id')->get();

        // Avance de obra real: último reporte publicado del proyecto. De aquí
        // salen las fases, el % global y la entrega estimada (no se inventan).
        $latestReport = \App\Models\ConstructionReport::where('project_id', $proyecto->id)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->first();

        return view('admin.crm.proyecto_detalle', compact('proyecto', 'units', 'latestReport'));
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

        // Avisa al cliente: "Aprobado" o, si fue rechazado, que vuelva a subir sus documentos.
        if ($user->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(
                    new \App\Mail\KycStatusMail(
                        name: $user->first_name ?: \Illuminate\Support\Str::before((string) ($user->name ?? ''), ' ') ?: (string) $user->name,
                        status: $data['decision'],
                        reason: (string) $request->input('notes', ''),
                        actionUrl: $data['decision'] === 'rejected' ? url('/form') : '',
                    )
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo enviar el correo de estado de KYC: ' . $e->getMessage());
            }
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
     * Guarda la firma del proyecto (imagen manuscrita + nombre y entidad del
     * firmante). Esta firma se inyecta en el recuadro del Desarrollador /
     * Vendedora de la promesa de compraventa y el plan de pagos, de modo que
     * los contratos salgan ya firmados a nombre de Makai.
     */
    public function updateProjectSignature(Request $request)
    {
        $data = $request->validate([
            'signer_name'      => ['nullable', 'string', 'max:160'],
            'signer_entity'    => ['nullable', 'string', 'max:200'],
            'signature_image'  => ['nullable', 'string'],
            'remove_signature' => ['nullable', 'boolean'],
        ]);

        $current = \App\Models\Setting::get('project_signature', []);
        $current = is_array($current) ? $current : [];

        if ($request->boolean('remove_signature')) {
            $current['signature_image'] = null;
        } elseif (!empty($data['signature_image'])) {
            if (!preg_match('#^data:image/(png|jpe?g);base64,#', $data['signature_image'])) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Formato de firma no válido.'], 422);
                }
                return back()->withErrors(['signature_image' => 'Formato de firma no válido.']);
            }
            $current['signature_image'] = $data['signature_image'];
        }

        $current['signer_name']   = trim((string) ($data['signer_name'] ?? ''));
        $current['signer_entity'] = trim((string) ($data['signer_entity'] ?? ''));
        $current['updated_by']    = auth()->id();
        $current['updated_at']    = now()->toIso8601String();

        \App\Models\Setting::put('project_signature', $current);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Firma del proyecto guardada correctamente.',
                'has_image' => !empty($current['signature_image']),
            ]);
        }

        return back()->with('settings_success', 'Firma del proyecto guardada correctamente.');
    }

    /**
     * Guarda el menú del cliente: la lista de ítems configurables que se muestran
     * en el desplegable del navbar (enlaces externos y documentos descargables).
     *
     * El front envía `items` como JSON (label, type, url y la ruta del archivo
     * ya subido por chunks) más `site_url`. El ícono se deriva del tipo.
     */
    public function updateClientMenu(Request $request)
    {
        $incoming = json_decode($request->input('items', '[]'), true);
        $incoming = is_array($incoming) ? $incoming : [];

        $clean = [];

        foreach ($incoming as $idx => $it) {
            $label = trim((string) ($it['label'] ?? ''));
            if ($label === '') {
                continue; // un ítem sin nombre no se guarda
            }

            $type = in_array(($it['type'] ?? 'link'), ['link', 'document'], true) ? $it['type'] : 'link';
            // El ícono se deriva del tipo: documento → archivo, enlace → mundo.
            $icon = $type === 'document' ? 'file' : 'globe';
            $id   = preg_replace('/[^a-z0-9\-]/', '', strtolower((string) ($it['id'] ?? ''))) ?: ('item' . $idx);

            $row = [
                'id'    => $id,
                'label' => mb_substr($label, 0, 120),
                'type'  => $type,
                'icon'  => $icon,
            ];

            if ($type === 'link') {
                $row['url']  = trim((string) ($it['url'] ?? ''));
                $row['file'] = null;
            } else {
                // El archivo ya se subió por chunks (admin.client-menu.upload); aquí
                // sólo se recibe la ruta resultante.
                $row['file']   = $it['file'] ?? null;
                $row['format'] = $it['format'] ?? null;
                $row['url']    = null;
            }

            $clean[] = $row;
        }

        // Borrar archivos de documentos que ya no están referenciados.
        $previous = \App\Models\Setting::get('client_menu', []);
        if (is_array($previous)) {
            $stillUsed = array_filter(array_column($clean, 'file'));
            foreach ($previous as $old) {
                $oldFile = $old['file'] ?? null;
                if ($oldFile && !in_array($oldFile, $stillUsed, true)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldFile);
                }
            }
        }

        \App\Models\Setting::put('client_menu', $clean);

        // URL del ítem fijo "Sitio web".
        \App\Models\Setting::put('site_url', trim((string) $request->input('site_url', '')));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Menú del cliente guardado correctamente.',
                'items'   => $clean,
            ]);
        }

        return back()->with('settings_success', 'Menú del cliente guardado correctamente.');
    }

    /**
     * Recibe un documento del menú del cliente subido por trozos (chunks).
     *
     * El front envía el archivo en partes de ~1 MB para no superar el
     * post_max_size de PHP en archivos grandes. Cada chunk se anexa a un
     * archivo temporal; en el último se mueve al disco público y se devuelve
     * la ruta final para guardarla luego junto al resto del menú.
     */
    public function uploadClientMenuChunk(Request $request)
    {
        $request->validate([
            'chunk'     => ['required', 'file', 'max:5120'], // 5 MB máx por chunk
            'upload_id' => ['required', 'string', 'max:64'],
            'index'     => ['required', 'integer', 'min:0'],
            'total'     => ['required', 'integer', 'min:1', 'max:1000'],
            'name'      => ['required', 'string', 'max:255'],
        ]);

        $uploadId = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $request->input('upload_id'));
        $index    = (int) $request->input('index');
        $total    = (int) $request->input('total');

        if ($uploadId === '') {
            return response()->json(['success' => false, 'message' => 'Identificador de subida inválido.'], 422);
        }

        $tmpDir  = storage_path('app/tmp-client-menu');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        $tmpPath = $tmpDir . DIRECTORY_SEPARATOR . $uploadId . '.part';

        // Anexar los bytes del chunk al temporal.
        $in  = fopen($request->file('chunk')->getRealPath(), 'rb');
        $out = fopen($tmpPath, $index === 0 ? 'wb' : 'ab');
        if ($in === false || $out === false) {
            return response()->json(['success' => false, 'message' => 'No se pudo procesar el archivo.'], 500);
        }
        stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);

        // Tope de 50 MB acumulado.
        if (filesize($tmpPath) > 52428800) {
            @unlink($tmpPath);
            return response()->json(['success' => false, 'message' => 'El archivo supera los 50 MB.'], 422);
        }

        // Chunks intermedios: confirmar y esperar el siguiente.
        if ($index + 1 < $total) {
            return response()->json(['success' => true, 'done' => false]);
        }

        // Último chunk: validar extensión y mover al disco público.
        $ext     = strtolower(pathinfo((string) $request->input('name'), PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        if (!in_array($ext, $allowed, true)) {
            @unlink($tmpPath);
            return response()->json(['success' => false, 'message' => 'Formato de archivo no permitido.'], 422);
        }

        $finalRel = 'client-menu/' . $uploadId . '.' . $ext;
        $stream   = fopen($tmpPath, 'rb');
        \Illuminate\Support\Facades\Storage::disk('public')->put($finalRel, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        @unlink($tmpPath);

        return response()->json([
            'success' => true,
            'done'    => true,
            'path'    => $finalRel,
            'format'  => strtoupper($ext),
            'name'    => $request->input('name'),
        ]);
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
