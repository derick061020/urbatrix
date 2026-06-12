<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    /** Fetch the reservation for the current user (or via ?reservation= override). */
    private function resolveReservation(Request $request): ?Reservation
    {
        $id = $request->query('reservation');

        $query = Reservation::with([
            'unit.images' => fn ($q) => $q->orderBy('sort_order'),
            'documents',
            'payments',
        ]);

        if ($id) {
            $r = $query->findOrFail($id);
            // admins can view any expediente; clients only their own
            if (Auth::user()?->role !== 'admin' && $r->user_id !== Auth::id()) abort(403);
            return $r;
        }

        // Only fetch the user's own reservation — no fallback to random records
        $r = $query->where('user_id', Auth::id())->latest()->first();

        return $r;
    }

    public function index(Request $request)
    {
        // If the user is an admin, redirect to admin dashboard
        if (Auth::user()?->role === 'admin') {
            return redirect()->route('admin.crm.dashboard');
        }

        // If the user is a broker, redirect to CRM
        if (Auth::user()?->role === 'broker') {
            return redirect()->route('admin.crm.dashboard');
        }

        $reservation = $this->resolveReservation($request);
        if (! $reservation) return view('dashboard.empty');

        return view('dashboard.mi-propiedad', [
            'activeRoute' => 'mi-propiedad',
            'reservation' => $reservation,
        ]);
    }

    public function progress(Request $request)
    {
        $reservation = $this->resolveReservation($request);
        $projectId = optional(optional($reservation)->unit)->project_id;

        $reportsQuery = \App\Models\ConstructionReport::published()->with('project');
        if ($projectId) {
            $reportsQuery->where(function ($q) use ($projectId) {
                $q->where('project_id', $projectId)->orWhereNull('project_id');
            });
        }
        $reports = $reportsQuery->orderByDesc('published_at')->get();

        return view('dashboard.obra', [
            'activeRoute' => 'progress',
            'reservation' => $reservation,
            'report'      => $reports->first(),
            'reports'     => $reports,
        ]);
    }

    public function documents(Request $request)
    {
        $reservation = $this->resolveReservation($request);
        return view('dashboard.documentos', [
            'activeRoute' => 'documents',
            'reservation' => $reservation,
        ]);
    }

    /**
     * Client uploads a file to fulfill a document the admin requested.
     * The placeholder Document (file_path='pending', metadata.requested=true)
     * gets the real file attached and goes back to "pendiente revisión".
     */
    public function uploadRequestedDocument(Request $request, \App\Models\Document $document)
    {
        // Must be a request belonging to the authenticated client's reservation
        $reservation = $document->reservation;
        if (! $reservation || $reservation->user_id !== Auth::id()) {
            abort(403);
        }
        if (! data_get($document->metadata, 'requested')) {
            abort(404);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $metadata = $document->metadata ?? [];
        $metadata['uploaded_by'] = Auth::id();
        $metadata['uploaded_at'] = now()->toDateTimeString();

        $document->update([
            'file_path'    => $path,
            'filename'     => $file->getClientOriginalName(),
            'status'       => 'pending',
            'generated_at' => now(),
            'approved_at'  => null,
            'approved_by'  => null,
            'metadata'     => $metadata,
        ]);

        \App\Support\ActivityLogger::log(Auth::id(), 'document_upload', 'Subió '.($document->title ?: 'un documento solicitado'), $document);

        return back()->with('success', 'Documento subido. Quedó en revisión por nuestro equipo.');
    }

    public function payments(Request $request)
    {
        $reservation = $this->resolveReservation($request);
        if (! $reservation) return view('dashboard.empty');
        return view('dashboard.pagos', [
            'activeRoute' => 'payments',
            'reservation' => $reservation,
        ]);
    }

    public function messages(Request $request)
    {
        $reservation = $this->resolveReservation($request);
        $messages = $reservation ? $reservation->messages()->with('sender')->get() : collect();

        // Mark admin messages as read by the client viewing them
        if ($reservation) {
            $reservation->messages()
                ->where('sender_role', 'admin')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return view('dashboard.mensajes', [
            'activeRoute' => 'messages',
            'reservation' => $reservation,
            'messages'    => $messages,
        ]);
    }

    /**
     * Client posts a message to their reservation thread.
     */
    public function sendMessage(Request $request)
    {
        $reservation = $this->resolveReservation($request);
        if (! $reservation) abort(404, 'No tenés un expediente activo.');
        if ($reservation->user_id !== Auth::id()) abort(403);

        $data = $request->validate(['body' => 'required|string|max:5000']);

        \App\Models\Message::create([
            'reservation_id' => $reservation->id,
            'sender_id'      => Auth::id(),
            'sender_role'    => 'client',
            'body'           => $data['body'],
            'channel'        => 'chat',
        ]);

        return $request->expectsJson() || $request->wantsJson()
            ? response()->json(['ok' => true])
            : back();
    }

    /**
     * Show the budget sent by admin to the client
     */
    public function showBudget(Reservation $reservation)
    {
        // Only the owner of the reservation can view
        if ($reservation->user_id !== Auth::id()) abort(403);

        if (!$reservation->isBudgetSent()) {
            return redirect()->route('dashboard')->with('error', 'Presupuesto aún no disponible.');
        }

        $breakdown = \App\Helpers\PaymentPlanHelper::calculatePaymentBreakdown($reservation);

        return view('dashboard.budget', compact('reservation', 'breakdown'));
    }

    /**
     * Client accepts the budget — triggers contract generation and payment creation
     */
    /**
     * Client sends an observation about the budget — admin sees it and can edit + resend.
     */
    public function submitBudgetObservation(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }
        if (! $reservation->isBudgetSent()) {
            return response()->json(['success' => false, 'message' => 'No hay un plan de pagos pendiente que observar.'], 422);
        }

        $data = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $observations = $reservation->budget_observations ?? [];
        $observations[] = [
            'from'    => 'client',
            'author'  => trim(($reservation->first_name ?? '').' '.($reservation->last_name ?? '')) ?: Auth::user()?->name,
            'message' => $data['message'],
            'at'      => now()->toIso8601String(),
        ];

        $reservation->update([
            'budget_observations' => $observations,
            'budget_client_response_at' => now(),
            // Revert to draft so admin can edit & resend (and client knows it's being revised)
            'budget_status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tu observación fue enviada. El asesor revisará el plan y te enviará una nueva propuesta.'
        ]);
    }

    /**
     * Client sends an observation about a contract document (purchase_promise / contract).
     * Behaves like submitBudgetObservation but at the Document level.
     */
    public function submitContractObservation(Request $request, \App\Models\Document $document)
    {
        $reservation = $document->reservation;
        if (! $reservation || $reservation->user_id !== Auth::id()) abort(403);
        if (! in_array($document->document_type, ['purchase_promise', 'contract'])) {
            abort(404, 'No es un contrato.');
        }

        $data = $request->validate(['message' => 'required|string|max:2000']);

        $meta = $document->metadata ?? [];
        $obs  = $meta['observations'] ?? [];
        $obs[] = [
            'from'    => 'client',
            'author'  => trim(($reservation->first_name ?? '').' '.($reservation->last_name ?? '')) ?: Auth::user()?->name,
            'message' => $data['message'],
            'at'      => now()->toIso8601String(),
        ];
        $meta['observations'] = $obs;
        $meta['client_response_at'] = now()->toIso8601String();
        // Reset the "client accepted" flag if it was set — admin must address the new feedback first
        unset($meta['accepted_at']);

        $document->update([
            'metadata' => $meta,
            'status'   => 'pending',
        ]);

        return back()->with('success', 'Tu observación fue enviada. Tu asesor revisará el contrato.');
    }

    /**
     * Client marks a contract as "conforme" — unlocks the Firmar button.
     */
    public function acceptContract(Request $request, \App\Models\Document $document)
    {
        $reservation = $document->reservation;
        if (! $reservation || $reservation->user_id !== Auth::id()) abort(403);
        if (! in_array($document->document_type, ['purchase_promise', 'contract'])) {
            abort(404, 'No es un contrato.');
        }
        // Gate: the payment plan must be signed before any contract can be accepted/signed
        $planDoc = $reservation->documents->firstWhere('document_type', 'payment_plan');
        if (! $planDoc || ! in_array($planDoc->status, ['signed', 'approved'])) {
            return back()->with('error', 'Primero tenés que firmar el plan de pagos.');
        }

        $meta = $document->metadata ?? [];
        $obs  = $meta['observations'] ?? [];
        $obs[] = [
            'from'    => 'client',
            'author'  => trim(($reservation->first_name ?? '').' '.($reservation->last_name ?? '')) ?: Auth::user()?->name,
            'message' => 'Conforme con el contrato. Listo para firmar.',
            'kind'    => 'accept',
            'at'      => now()->toIso8601String(),
        ];
        $meta['observations'] = $obs;
        $meta['accepted_at']  = now()->toIso8601String();

        $document->update([
            'metadata' => $meta,
            'status'   => 'generated',
        ]);

        return back()->with('success', 'Contrato aceptado. Podés proceder a firmarlo.');
    }

    public function acceptBudget(Request $request, Reservation $reservation)
    {
        // Only the owner of the reservation can accept
        if ($reservation->user_id !== Auth::id()) abort(403);

        if (!$reservation->isBudgetSent()) {
            return response()->json([
                'success' => false,
                'message' => 'Presupuesto aún no disponible.'
            ], 422);
        }

        try {
            // Append an explicit "client accepted" entry to the conversation log so the admin
            // sees a clear, attributed acceptance (and not a stray previous message).
            $observations = $reservation->budget_observations ?? [];
            $observations[] = [
                'from'    => 'client',
                'author'  => trim(($reservation->first_name ?? '').' '.($reservation->last_name ?? '')) ?: Auth::user()?->name,
                'message' => 'Acepto el plan de pagos.',
                'kind'    => 'accept',
                'at'      => now()->toIso8601String(),
            ];

            // Update budget_status to approved (but NOT contract_signed yet)
            $reservation->update([
                'budget_status' => 'approved',
                'budget_observations' => $observations,
                'budget_client_response_at' => now(),
            ]);

            // Initialize documents as pending
            try {
                DocumentService::initializeDocuments($reservation);
            } catch (\Exception $e) {
                // Already exists, continue
            }

            // Generate actual document files automatically
            try {
                $adminController = new \App\Http\Controllers\AdminController();

                // Generate payment plan
                try {
                    $adminController->generatePaymentPlan($reservation);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Could not generate payment plan: ' . $e->getMessage());
                }

                // Generate purchase promise
                try {
                    $adminController->generatePurchasePromise($reservation);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Could not generate purchase promise: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Could not generate documents: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Presupuesto aceptado. Bienvenido a Makai Residences.',
                'redirect' => route('dashboard')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar presupuesto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit payment proof for approval
     */
    public function submitPayment(Request $request, Reservation $reservation)
    {
        // Only the owner of the reservation can submit payments
        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'payment_method' => 'required|string',
            'paid_at' => 'required|date',
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $payment = \App\Models\Payment::findOrFail($request->payment_id);

            // Verify payment belongs to this reservation
            if ($payment->reservation_id !== $reservation->id) {
                return response()->json(['success' => false, 'message' => 'Pago no pertenece a esta reserva.'], 403);
            }

            // Handle file upload
            $path = null;
            if ($request->hasFile('receipt')) {
                $path = $request->file('receipt')->store('payment_receipts/' . $reservation->id, 'public');
            }

            $payment->update([
                'payment_method' => $request->payment_method,
                'paid_at' => $request->paid_at,
                'receipt_path' => $path,
                'notes' => $request->notes,
                'approval_status' => 'pending',
            ]);

            \App\Support\ActivityLogger::log(Auth::id(), 'payment', 'Envió comprobante de pago · '.($payment->label ?: 'Cuota'), $payment);

            return response()->json([
                'success' => true,
                'message' => 'Comprobante enviado para aprobación. Te notificaremos cuando sea revisado.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar comprobante: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ─────────────── Wishlist / Guardados ─────────────── */

    public function guardados()
    {
        $units = \App\Models\Unit::whereIn('id', function ($q) {
                $q->select('unit_id')->from('wishlists')->where('user_id', Auth::id());
            })
            ->with(['images' => fn($q) => $q->orderBy('sort_order')])
            ->where('public', true)
            ->orderByDesc('updated_at')
            ->get();

        return view('dashboard.guardados', [
            'units' => $units,
            'activeRoute' => 'guardados',
        ]);
    }

    /* ─────────────── Calendario ─────────────── */

    public function calendario(Request $request)
    {
        $reservation = $this->resolveReservation($request);
        $userId = Auth::id();

        // Build a unified event list from tasks/messages/payments related to this client
        $events = collect();

        // Meetings (videollamadas con Google Meet)
        if ($userId) {
            $meetings = \App\Models\Meeting::where('user_id', $userId)
                ->where('status', '!=', 'cancelled')
                ->get();
            foreach ($meetings as $m) {
                $events->push((object) [
                    'id'        => 'm-'.$m->id,
                    'title'     => 'Videollamada con asesor',
                    'start'     => \Carbon\Carbon::parse($m->scheduled_at),
                    'end'       => \Carbon\Carbon::parse($m->scheduled_at)->addMinutes((int) $m->duration_minutes),
                    'type'      => 'meeting',
                    'meta'      => $m->google_meet_link,
                    'meet_link' => $m->google_meet_link,
                ]);
            }
        }

        // Tasks assigned to or about this client's reservation
        if ($reservation) {
            $tasks = \App\Models\Task::where('reservation_id', $reservation->id)
                ->whereNotNull('due_date')
                ->get();
            foreach ($tasks as $t) {
                $events->push((object) [
                    'id'    => 't-'.$t->id,
                    'title' => $t->title ?? 'Tarea',
                    'start' => \Carbon\Carbon::parse($t->due_date),
                    'end'   => \Carbon\Carbon::parse($t->due_date)->addMinutes(30),
                    'type'  => 'task',
                    'meta'  => $t->status ?? 'pendiente',
                ]);
            }

            // Upcoming payments
            foreach ($reservation->payments->whereIn('status', ['pending', 'overdue']) as $p) {
                if (! $p->due_date) continue;
                $events->push((object) [
                    'id'    => 'p-'.$p->id,
                    'title' => 'Pago programado · $'.number_format((float) $p->amount, 0),
                    'start' => \Carbon\Carbon::parse($p->due_date)->startOfDay(),
                    'end'   => \Carbon\Carbon::parse($p->due_date)->startOfDay()->addHour(),
                    'type'  => 'payment',
                    'meta'  => $p->status,
                ]);
            }
        }

        $events = $events->sortBy('start')->values();

        // Reunión recién agendada desde la home (?meeting=ID): la resaltamos
        $highlightMeeting = null;
        if ($request->filled('meeting')) {
            $highlightMeeting = $events->firstWhere('id', 'm-'.$request->query('meeting'));
        }

        return view('dashboard.calendario', [
            'events'           => $events,
            'reservation'      => $reservation,
            'activeRoute'      => 'calendario',
            'highlightMeeting' => $highlightMeeting,
        ]);
    }

    /* ─────────────── Acuerdos ─────────────── */

    public function acuerdos(Request $request)
    {
        $reservation = $this->resolveReservation($request);
        if (! $reservation) {
            return view('dashboard.acuerdos', [
                'reservation' => null,
                'pending'     => collect(),
                'completed'   => collect(),
                'activeRoute' => 'acuerdos',
            ]);
        }

        $acuerdoTypes = ['budget', 'payment_plan', 'purchase_promise', 'contract'];
        $docs = $reservation->documents()
            ->whereIn('document_type', $acuerdoTypes)
            ->orderByDesc('created_at')
            ->get();
        // Exclude signed documents from both pending and completed - they should not appear in acuerdos
        $docs = $docs->filter(fn($d) => $d->status !== 'signed');

        // El payment_plan solo se muestra al cliente cuando el admin ya envió el plan.
        // Generar el .docx en el admin crea el Document con status='generated', pero eso
        // no implica que el cliente deba verlo todavía.
        $budgetSent = $reservation->isBudgetSent()
            || $reservation->budget_status === 'approved'
            || ! empty($reservation->budget_observations);
        if (! $budgetSent) {
            $docs = $docs->reject(fn($d) => $d->document_type === 'payment_plan');
        }

        $pending   = $docs->filter(fn($d) => in_array($d->status, ['pending', 'generated', 'awaiting_signature', 'in_review']));
        $completed = $docs->filter(fn($d) => in_array($d->status, ['approved', 'completed']));

        return view('dashboard.acuerdos', [
            'reservation' => $reservation,
            'pending'     => $pending,
            'completed'   => $completed,
            'activeRoute' => 'acuerdos',
        ]);
    }

    /* ─────────────── Profile (client) ─────────────── */

    public function editProfile()
    {
        return view('dashboard.profile', [
            'user' => Auth::user(),
            'activeRoute' => 'profile',
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

}
