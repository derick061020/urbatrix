<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Unit;
use App\Helpers\PaymentPlanHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Store a new reservation
     */
    public function store(Request $request)
    {
        // Debug logging
        \Log::info('Reservation store method called');
        \Log::info('Request data: ' . json_encode($request->all()));
        
        try {
            // When authenticated, only unit_id is required — user data comes from account
            $authed = Auth::check();
            $rules  = [
                'unit_id'    => 'required|exists:units,id',
                'first_name' => ($authed ? 'nullable' : 'required').'|string|max:255',
                'last_name'  => ($authed ? 'nullable' : 'required').'|string|max:255',
                'email'      => ($authed ? 'nullable' : 'required').'|email|max:255',
                'phone'      => 'nullable|string|max:20',
                'country'    => 'nullable|string|max:255',
            ];
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Auto-release any expired 48h holds before checking availability
        Unit::releaseExpiredHolds();

        // Get unit information
        $unit = Unit::findOrFail($validated['unit_id']);

        // Block if someone else already holds this unit (and the hold hasn't expired)
        if (in_array(strtoupper($unit->status), ['RESERVED', 'SOLD']) && $unit->isOnHold()) {
            $holderId = $unit->reserved_by_reservation_id;
            $myActive = $holderId
                ? Reservation::where('id', $holderId)->where('user_id', Auth::id())->exists()
                : false;
            if (! $myActive) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta unidad está reservada hasta '.$unit->reserved_until->format('Y-m-d H:i'),
                ], 409);
            }
        }

        // Generate unique reservation code
        do {
            $reservationCode = 'RES-' . strtoupper(Str::random(8));
        } while (Reservation::where('reservation_code', $reservationCode)->exists());

        // Backfill from the authenticated user
        $user   = Auth::user();
        $userId = $user?->id;
        $first  = $validated['first_name'] ?? $user?->first_name ?? (explode(' ', $user?->name ?? ' ')[0] ?? '');
        $parts  = preg_split('/\s+/', trim($user?->name ?? ''), 2);
        $last   = $validated['last_name']  ?? $user?->last_name  ?? ($parts[1] ?? '');
        $email  = $validated['email']      ?? $user?->email      ?? '';
        $phone  = $validated['phone']      ?? $user?->phone      ?? '';
        $country= $validated['country']    ?? $user?->country    ?? '';

        // Create reservation in "pending_payment" — the unit is NOT held yet.
        // It only becomes RESERVED after the $5,000 reservation fee is paid
        // (see confirmPayment). This gives the user time to pay without
        // blocking the unit for others on an unpaid intent.
        $reservation = Reservation::create([
            'first_name' => $first,
            'last_name'  => $last,
            'email'      => $email,
            'phone'      => $phone,
            'country'    => $country,
            'unit_id'    => $unit->id,
            'unit_name'  => $unit->custom_id ?? $unit->name,
            'unit_price' => $unit->price,
            'reservation_code' => $reservationCode,
            'status'     => 'pending_payment',
            'reservation_fee' => (float) config('services.stripe.reservation_fee', 5000),
            'expires_at' => Carbon::now()->addMinutes(30),
            'user_id'    => $userId,
        ]);

        // Store reservation in session for the checkout page
        session(['reservation' => $reservation]);

        // Return JSON response instead of redirect — go to checkout (payment) first
        return response()->json([
            'success' => true,
            'message' => 'Reservation created successfully',
            'reservation_code' => $reservation->reservation_code,
            'redirect_to' => '/checkout'
        ]);
    }

    /**
     * Resolve the pending-payment reservation for the current session/user.
     * Always returns the persisted DB row (not the session snapshot) so we can
     * safely mutate payment state. Returns null when missing/expired/foreign.
     */
    private function currentReservation()
    {
        $data = session('reservation');
        if (!$data) return null;

        $id = is_array($data) ? ($data['id'] ?? null) : ($data->id ?? null);
        $code = is_array($data) ? ($data['reservation_code'] ?? null) : ($data->reservation_code ?? null);

        $query = Reservation::query();
        if ($id) {
            $query->where('id', $id);
        } elseif ($code) {
            $query->where('reservation_code', $code);
        } else {
            return null;
        }

        $reservation = $query->first();
        if (!$reservation) return null;

        // Only the owner may operate on it
        if (Auth::check() && $reservation->user_id && $reservation->user_id !== Auth::id()) {
            return null;
        }
        return $reservation;
    }

    /**
     * Show the checkout (payment) page — collects the $5,000 reservation fee
     * via Stripe before the unit is held. Mirrors showForm()'s session guard.
     */
    public function showCheckout()
    {
        $reservation = $this->currentReservation();

        if (!$reservation) {
            return redirect('/')->with('error', 'No reservation found. Please start a new reservation.');
        }
        if ($reservation->isExpired() && $reservation->status === 'pending_payment') {
            return redirect('/')->with('error', 'Reservation expired. Please start a new reservation.');
        }
        // If it was already paid, skip straight to the KYC form
        if ($reservation->paid_at) {
            return redirect('/form');
        }

        $unit = Unit::find($reservation->unit_id);
        $stripeKey = config('services.stripe.key');

        return view('checkout', compact('reservation', 'unit', 'stripeKey'));
    }

    /**
     * Create (or reuse) a Stripe PaymentIntent for the reservation fee.
     * Re-checks unit availability before charging.
     */
    public function createPaymentIntent(Request $request)
    {
        $reservation = $this->currentReservation();
        if (!$reservation || $reservation->status !== 'pending_payment') {
            return response()->json(['success' => false, 'message' => 'Reserva no encontrada o ya procesada.'], 422);
        }

        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            return response()->json(['success' => false, 'message' => 'Stripe no está configurado. Falta STRIPE_SECRET.'], 500);
        }

        // Make sure the unit is still grabbable (not held by someone else / sold)
        Unit::releaseExpiredHolds();
        $unit = Unit::find($reservation->unit_id);
        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'La unidad ya no está disponible.'], 409);
        }
        if (in_array(strtoupper($unit->status), ['RESERVED', 'SOLD']) && $unit->isOnHold()) {
            $mine = $unit->reserved_by_reservation_id == $reservation->id;
            if (!$mine) {
                return response()->json(['success' => false, 'message' => 'Esta unidad acaba de ser reservada por otra persona.'], 409);
            }
        }

        try {
            \Stripe\Stripe::setApiKey($secret);

            $amount = (int) round(((float) ($reservation->reservation_fee ?: config('services.stripe.reservation_fee', 5000))) * 100);

            $intent = \Stripe\PaymentIntent::create([
                'amount'   => $amount,
                'currency' => 'usd',
                'description' => 'Reserva '.$reservation->reservation_code.' · Unidad '.$reservation->unit_name,
                'metadata' => [
                    'reservation_id'   => $reservation->id,
                    'reservation_code' => $reservation->reservation_code,
                    'unit_id'          => $reservation->unit_id,
                ],
                'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
            ]);

            $reservation->update(['stripe_payment_intent' => $intent->id]);

            return response()->json([
                'success'       => true,
                'client_secret' => $intent->client_secret,
                'amount'        => $amount,
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe PaymentIntent error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'No se pudo iniciar el pago. Intenta de nuevo.'], 500);
        }
    }

    /**
     * Confirm the payment server-side and ONLY THEN hold the unit.
     * Trusts Stripe's PaymentIntent.status, never the client.
     */
    public function confirmPayment(Request $request)
    {
        $reservation = $this->currentReservation();
        if (!$reservation) {
            return response()->json(['success' => false, 'message' => 'Reserva no encontrada.'], 422);
        }

        // Idempotent: already paid → just succeed
        if ($reservation->paid_at) {
            return response()->json([
                'success' => true,
                'reservation_code' => $reservation->reservation_code,
                'redirect_to' => '/form',
            ]);
        }

        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            return response()->json(['success' => false, 'message' => 'Stripe no está configurado.'], 500);
        }

        $intentId = $request->input('payment_intent_id') ?: $reservation->stripe_payment_intent;
        if (!$intentId) {
            return response()->json(['success' => false, 'message' => 'Falta el identificador de pago.'], 422);
        }

        try {
            \Stripe\Stripe::setApiKey($secret);
            $intent = \Stripe\PaymentIntent::retrieve($intentId);

            if (!$intent || $intent->status !== 'succeeded') {
                return response()->json(['success' => false, 'message' => 'El pago no se completó.'], 402);
            }
            // Guard: the intent must belong to this reservation
            if (($intent->metadata->reservation_id ?? null) != $reservation->id) {
                return response()->json(['success' => false, 'message' => 'El pago no corresponde a esta reserva.'], 422);
            }

            // Mark reservation paid and ready for KYC. Keep 'pending' so the
            // existing form update() flow can auto-confirm it afterwards.
            $reservation->update([
                'paid_at'               => now(),
                'status'                => 'pending',
                'stripe_payment_intent' => $intent->id,
                'expires_at'            => Carbon::now()->addHours(48),
            ]);

            // Record the reservation fee in the payments ledger
            try {
                $payment = \App\Models\Payment::create([
                    'reservation_id' => $reservation->id,
                    'payment_type'   => 'initial',
                    'label'          => 'Reserva (seña) — '.$reservation->reservation_code,
                    'amount'         => (float) ($reservation->reservation_fee ?: config('services.stripe.reservation_fee', 5000)),
                    'due_date'       => now()->toDateString(),
                    'status'         => 'paid',
                    'paid_at'        => now(),
                    'payment_method' => 'card',
                    'notes'          => 'Stripe PaymentIntent '.$intent->id,
                ]);

                // Comprobante de pago por correo (#11 del briefing)
                if ($to = ($reservation->email ?: optional($reservation->user)->email)) {
                    \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Mail\PaymentReceiptMail($payment));
                }
            } catch (\Throwable $e) {
                Log::warning('Could not record/notify reservation payment: '.$e->getMessage());
            }

            // NOW hold the unit — 48h, tied to this reservation
            $unit = Unit::find($reservation->unit_id);
            if ($unit) {
                $unit->update([
                    'status'                     => 'RESERVED',
                    'reserved_until'             => Carbon::now()->addHours(48),
                    'reserved_by_reservation_id' => $reservation->id,
                ]);
            }

            session(['reservation' => $reservation]);

            return response()->json([
                'success'          => true,
                'reservation_code' => $reservation->reservation_code,
                'redirect_to'      => '/form',
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe confirm error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'No se pudo verificar el pago.'], 500);
        }
    }

    /**
     * Show the form page with reservation data
     */
    public function showForm()
    {
        $reservationData = session('reservation');

        // Cliente logueado que vuelve a completar su KYC desde el panel: si no hay
        // reserva en sesión, usamos su última reserva persistida.
        if (!$reservationData && Auth::check()) {
            $reservationData = Reservation::where('user_id', Auth::id())->latest()->first();
        }

        if (!$reservationData) {
            return redirect('/')->with('error', 'No reservation found. Please start a new reservation.');
        }

        // Convert array to Reservation object if needed
        if (is_array($reservationData)) {
            $reservation = new Reservation();
            $reservation->fill($reservationData);
            // Set the expires_at as Carbon instance
            $reservation->expires_at = \Carbon\Carbon::parse($reservationData['expires_at']);
        } else {
            $reservation = $reservationData;
        }

        // La expiración solo bloquea reservas que aún están pendientes de confirmar;
        // una reserva confirmada (KYC en curso) sigue siendo accesible.
        if ($reservation->status === 'pending' && $reservation->isExpired()) {
            return redirect('/')->with('error', 'Reservation expired. Please start a new reservation.');
        }

        // Get unit information for display
        $unit = Unit::find($reservation->unit_id);

        // Skip the ID upload in the form if the user already uploaded it during register
        $user = Auth::user();
        $existingKycDoc = ($user && $user->hasKycDocument())
            ? [
                'path'   => $user->kyc_id_document,
                'url'    => \Storage::disk('public')->url($user->kyc_id_document),
                'name'   => basename($user->kyc_id_document),
                'status' => $user->verification_status ?? 'pending',
            ]
            : null;

        return view('form', compact('reservation', 'unit', 'existingKycDoc'));
    }

    /**
     * Parse JSON co_buyers payload into a sanitized array of buyer rows.
     */
    private function parseCoBuyers($raw)
    {
        if (empty($raw)) return null;
        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        if (!is_array($decoded) || empty($decoded)) return null;

        $allowed = ['first_name','last_name','email','phone','id_type','document_number','birth_date','nationality','relationship','ownership_pct'];
        $clean = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) continue;
            $r = [];
            foreach ($allowed as $k) {
                $v = $row[$k] ?? null;
                if (is_string($v)) $v = trim($v);
                if ($v !== '' && $v !== null) $r[$k] = $v;
            }
            // Require at least name + document
            if (!empty($r['first_name']) && !empty($r['document_number'])) {
                $clean[] = $r;
            }
        }
        return empty($clean) ? null : $clean;
    }

    /**
     * Update reservation with additional form data
     */
    public function update(Request $request)
    {
        // Debug logging
        \Log::info('Reservation update method called');
        \Log::info('Request data: ' . json_encode($request->all()));
        
        $validated = $request->validate([
            'reservation_code' => 'required|string|exists:reservations,reservation_code',
            'profession' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'economic_dependent' => 'nullable|string|in:Sí,No',
            'payment_method' => 'nullable|string',
            'terms_accepted' => 'required|accepted',
            'id_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:1024', // Max 1MB
            'expedition_date' => 'nullable|date',
            'expedition_place' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:150',
            'nationality' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'spouse_name' => 'nullable|string|max:255',
            'spouse_nationality' => 'nullable|string|max:255',
            'spouse_document' => 'nullable|string|max:255',
            'co_buyers' => 'nullable|string',
            'id_type' => 'nullable|string|max:255',
            'document_number' => 'nullable|string|max:255',
            // Address fields
            'address' => 'nullable|string|max:500',
            'province' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'apartment_number' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:20',
        ]);

        try {
            $reservation = Reservation::where('reservation_code', $validated['reservation_code'])->firstOrFail();
            
            // Handle file upload — accept fresh upload OR reuse the user's KYC doc from register
            $idDocumentPath = null;
            if ($request->hasFile('id_document')) {
                $file = $request->file('id_document');
                $filename = 'id_' . $reservation->reservation_code . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('documents'), $filename);
                $idDocumentPath = 'documents/' . $filename;
                \Log::info('ID document uploaded: ' . $idDocumentPath);

                // Replace user's KYC doc reference too (so future reservations also reuse the new one)
                if (Auth::check()) {
                    $auth = Auth::user();
                    if (\Schema::hasColumn('users', 'kyc_id_document')) {
                        // Copy into the public disk under onboarding so admin still sees it
                        $copyPath = 'onboarding/'.$auth->id.'/id_front.'.$file->getClientOriginalExtension();
                        \Storage::disk('public')->copy($idDocumentPath, $copyPath); // best-effort; ignore failure
                        $auth->update(['kyc_id_document' => $copyPath, 'verification_status' => 'pending']);
                    }
                }
            } elseif (Auth::check() && Auth::user()->hasKycDocument()) {
                // Reuse the existing KYC doc that the user uploaded during register
                $idDocumentPath = Auth::user()->kyc_id_document;
                \Log::info('Reusing existing KYC doc for reservation: ' . $idDocumentPath);
            }
            
            // Get payment plan configuration if payment method is selected
            $paymentPlanData = [];
            if (!empty($validated['payment_method'])) {
                $config = PaymentPlanHelper::getPlanConfiguration($validated['payment_method']);
                $paymentPlanData = [
                    'payment_initial_percentage' => $config['payment_initial_percentage'],
                    'payment_construction_percentage' => $config['payment_construction_percentage'],
                    'payment_delivery_percentage' => $config['payment_delivery_percentage'],
                    'legal_costs' => $config['legal_costs'],
                    'payment_installments' => $config['payment_installments'],
                ];
            }
            
            // Update additional fields
            $reservation->update(array_merge([
                'profession' => $validated['profession'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'economic_dependent' => $validated['economic_dependent'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'terms_accepted' => true,
                'id_document_path' => $idDocumentPath,
                'expedition_date' => $validated['expedition_date'] ?? null,
                'expedition_place' => $validated['expedition_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'age' => $validated['age'] ?? null,
                'nationality' => $validated['nationality'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'spouse_name' => $validated['spouse_name'] ?? null,
                'spouse_nationality' => $validated['spouse_nationality'] ?? null,
                'spouse_document' => $validated['spouse_document'] ?? null,
                'co_buyers' => $this->parseCoBuyers($validated['co_buyers'] ?? null),
                'id_type' => $validated['id_type'] ?? null,
                'document_number' => $validated['document_number'] ?? null,
                // Address fields
                'address' => $validated['address'] ?? null,
                'province' => $validated['province'] ?? null,
                'neighborhood' => $validated['neighborhood'] ?? null,
                'city' => $validated['city'] ?? null,
                'building_name' => $validated['building_name'] ?? null,
                'apartment_number' => $validated['apartment_number'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'updated_at' => now(),
            ], $paymentPlanData));

            // Update session with new data
            session(['reservation' => $reservation]);

            // Auto-confirm reservation when form is completed
            if ($reservation->status === 'pending') {
                $reservation->update(['status' => 'confirmed']);
                
                // Update unit status to 'reserved' when reservation is confirmed
                // and clear the 48h auto-release timer since the deal is moving forward
                $unit = Unit::find($reservation->unit_id);
                if ($unit) {
                    $unit->update([
                        'status' => 'RESERVED',
                        'reserved_until' => null,
                        'reserved_by_reservation_id' => $reservation->id,
                    ]);
                }
                
                // Create a consolidated 'kyc' Document row so admins can review
                // the full KYC dossier in one place from the expediente detail.
                try {
                    $kycMeta = [
                        'id_type'           => $reservation->id_type,
                        'document_number'   => $reservation->document_number,
                        'expedition_date'   => $reservation->expedition_date?->format('Y-m-d') ?? $reservation->expedition_date,
                        'expedition_place'  => $reservation->expedition_place,
                        'birth_date'        => $reservation->birth_date?->format('Y-m-d') ?? $reservation->birth_date,
                        'age'               => $reservation->age,
                        'nationality'       => $reservation->nationality,
                        'marital_status'    => $reservation->marital_status,
                        'spouse_name'       => $reservation->spouse_name,
                        'spouse_nationality'=> $reservation->spouse_nationality,
                        'spouse_document'   => $reservation->spouse_document,
                        'profession'        => $reservation->profession,
                        'occupation'        => $reservation->occupation,
                        'economic_dependent'=> $reservation->economic_dependent,
                        'address'           => $reservation->address,
                        'province'          => $reservation->province,
                        'neighborhood'      => $reservation->neighborhood,
                        'city'              => $reservation->city,
                        'building_name'     => $reservation->building_name,
                        'apartment_number'  => $reservation->apartment_number,
                        'postal_code'       => $reservation->postal_code,
                        'country'           => $reservation->country,
                        'co_buyers'         => $reservation->co_buyers ?? [],
                        // El documento de identidad se conserva como referencia; el KYC en sí
                        // es el formulario imprimible que se genera abajo.
                        'id_document_path'  => $idDocumentPath,
                    ];

                    // Generar el formulario KYC imprimible (HTML) con lo que cargó el cliente.
                    $kycHtmlPath = null;
                    try {
                        $kycHtmlPath = \App\Helpers\DocumentDataHelper::renderKycHtml($reservation);
                    } catch (\Throwable $e) {
                        \Log::warning('No se pudo generar el HTML del KYC: '.$e->getMessage());
                    }

                    \App\Models\Document::updateOrCreate(
                        ['reservation_id' => $reservation->id, 'document_type' => 'kyc'],
                        [
                            'title'        => 'KYC — '.trim(($reservation->first_name ?? '').' '.($reservation->last_name ?? '')),
                            'filename'     => $kycHtmlPath ? basename($kycHtmlPath) : ($idDocumentPath ? basename($idDocumentPath) : null),
                            'file_path'    => $kycHtmlPath ?: $idDocumentPath,
                            'status'       => 'pending',
                            'generated_at' => now(),
                            'metadata'     => $kycMeta,
                        ]
                    );
                    \Log::info('KYC document created for reservation: ' . $reservation->reservation_code);
                } catch (\Exception $e) {
                    \Log::warning('Could not create KYC document: ' . $e->getMessage());
                }

                // Auto-initialize documents (payment_plan + purchase_promise) for the reservation
                try {
                    \App\Services\DocumentService::initializeDocuments($reservation);
                    \Log::info('Documents auto-initialized for reservation: ' . $reservation->reservation_code);
                } catch (\Exception $docEx) {
                    \Log::warning('Could not initialize documents: ' . $docEx->getMessage());
                }
                
                \Log::info('Reservation auto-confirmed: ' . $reservation->reservation_code);
            }

            \Log::info('Reservation updated successfully: ' . $reservation->reservation_code);

            return response()->json([
                'success' => true,
                'message' => 'Reservation confirmed successfully',
                'reservation' => $reservation
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating reservation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm reservation
     */
    public function confirm(Request $request)
    {
        $reservationCode = $request->input('reservation_code');
        
        $reservation = Reservation::where('reservation_code', $reservationCode)
            ->where('status', 'pending')
            ->firstOrFail();

        // Check if expired
        if ($reservation->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation has expired'
            ]);
        }

        // Update status to confirmed
        $reservation->update([
            'status' => 'confirmed'
        ]);

        // Update unit status to 'reserved' when reservation is confirmed
        $unit = Unit::find($reservation->unit_id);
        if ($unit) {
            $unit->update(['status' => 'reserved']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reservation confirmed successfully',
            'reservation' => $reservation
        ]);
    }

    /**
     * Get reservation details by code
     */
    public function getByCode($code)
    {
        $reservation = Reservation::with('unit')
            ->where('reservation_code', $code)
            ->firstOrFail();

        return response()->json($reservation);
    }
}
