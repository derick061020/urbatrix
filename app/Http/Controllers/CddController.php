<?php

namespace App\Http\Controllers;

use App\Mail\CddMail;
use App\Models\CddFile;
use App\Models\Reservation;
use App\Support\CddForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Diligencia debida (CDD) de Siebe Group: el admin avanza las fases desde la
 * pestaña «Cumplimiento» del expediente y el comprador rellena su KYC en
 * /dashboard/cumplimiento cuando se le pide. Ver App\Models\CddFile.
 */
class CddController extends Controller
{
    private function file(Reservation $reservation): CddFile
    {
        abort_unless(CddFile::enabled(), 404);

        return $reservation->cdd ?? $reservation->cdd()->create([]);
    }

    /* ───────────────────────────── Admin ───────────────────────────── */

    public function update(Request $request, Reservation $reservation)
    {
        $cdd    = $this->file($reservation);
        $action = $request->input('action');
        $uid    = Auth::id();

        switch ($action) {
            case 'screening':
                $d = $request->validate([
                    'screening_status' => 'required|in:limpio,coincidencia',
                    'screening_notes'  => 'nullable|string|max:2000',
                ]);
                $cdd->fill($d + ['screened_at' => now(), 'screened_by' => $uid]);
                // Reserva web a la espera del screening: ya puede pagar el depósito.
                if ($d['screening_status'] === 'limpio' && ! CddFile::kycAtReservation() && $reservation->status === 'pending_payment' && ! $reservation->paid_at) {
                    $reservation->update(['expires_at' => now()->addHours(48)]);
                    $this->notify($reservation, 'cleared', '', url('/checkout'));
                }
                $cdd->addLog($d['screening_status'] === 'limpio'
                    ? 'Screening de sanciones, PEP y medios adversos: sin coincidencias.'
                    : 'Screening: coincidencia real. La venta no puede seguir; informar a CBA y FIU.');
                break;

            // El depósito, a nombre del que reserva; si no, cumplimiento lo investiga.
            case 'payment':
                $d = $request->validate([
                    'payment_name_ok' => 'required|boolean',
                    'payment_notes'   => 'nullable|string|max:2000',
                ]);
                $cdd->fill($d + ['payment_checked_at' => now()]);
                $cdd->addLog($d['payment_name_ok']
                    ? 'Depósito recibido a nombre del comprador.'
                    : 'Alerta: el depósito no llegó a nombre del comprador. Cumplimiento lo investiga.');
                break;

            case 'request_kyc':
                $d = $request->validate(['buyer_type' => 'required|in:natural,legal']);
                abort_unless($cdd->screeningCleared(), 422, __('Primero hay que completar el screening sin coincidencias.'));
                $cdd->fill($d + ['kyc_status' => 'solicitado', 'kyc_requested_at' => now(), 'info_request' => null]);
                $cdd->addLog('Formulario KYC solicitado al comprador (' . ($d['buyer_type'] === 'legal' ? 'persona jurídica' : 'persona física') . ').');
                $this->notify($reservation, 'request');
                break;

            case 'request_info':
                $d = $request->validate(['info_request' => 'required|string|max:2000']);
                $cdd->fill($d + ['kyc_status' => 'info_adicional']);
                $cdd->addLog('Información adicional pedida: ' . $d['info_request']);
                $this->notify($reservation, 'info', $d['info_request']);
                break;

            case 'review':
                $d = $request->validate([
                    'risk_rating'  => 'required|in:bajo,medio,alto',
                    'risk_notes'   => 'nullable|string|max:4000',
                    'cdd_decision' => 'required|in:aprobado,rechazado',
                    'cdd_notes'    => 'nullable|string|max:2000',
                ]);
                abort_unless(in_array($cdd->kyc_status, ['enviado', 'revisado'], true), 422, __('El comprador aún no ha enviado el formulario KYC.'));
                abort_unless($cdd->screeningCleared(), 422, __('Primero hay que completar el screening sin coincidencias.'));
                $cdd->fill($d + ['kyc_status' => 'revisado', 'cdd_decided_at' => now(), 'cdd_decided_by' => $uid]);
                $cdd->addLog('Cumplimiento: perfil de riesgo ' . $d['risk_rating'] . ', comprador ' . $d['cdd_decision'] . '.');
                // «Approve/reject the potential buyer(s) through the portal and via email.»
                if ($cdd->getOriginal('cdd_decision') !== $d['cdd_decision']) {
                    $this->notify($reservation, $d['cdd_decision'] === 'aprobado' ? 'approved' : 'rejected');
                }
                break;

            case 'manager':
                $d = $request->validate([
                    'manager_decision' => 'required|in:aprobado,rechazado',
                    'manager_notes'    => 'nullable|string|max:2000',
                    'deviation_note'   => 'nullable|string|max:2000',
                ]);
                abort_unless($cdd->cdd_decision !== 'pendiente', 422, __('Falta el consejo de cumplimiento.'));
                // Ir contra el consejo de cumplimiento sólo motivado (Osman y Zuleika).
                if ($d['manager_decision'] !== $cdd->cdd_decision && blank($d['deviation_note'] ?? null)) {
                    return back()->withErrors(['deviation_note' => __('Para desviarte del consejo de cumplimiento hay que motivarlo y documentar la consulta.')])->withInput();
                }
                $cdd->fill($d + ['manager_decided_at' => now(), 'manager_decided_by' => $uid]);
                $cdd->addLog('Director de ventas: venta ' . ($d['manager_decision'] === 'aprobado' ? 'aprobada' : 'rechazada') . '.' . (filled($d['deviation_note'] ?? null) ? ' Desviación motivada.' : ''));
                break;

            case 'spa':
                $d = $request->validate([
                    'spa_signed_at' => 'required|date|before_or_equal:today',
                    'spa_file'      => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png',
                ]);
                abort_unless($cdd->saleApproved(), 422, __('El contrato sólo se firma con el KYC aprobado por cumplimiento y por el director de ventas.'));
                $cdd->fill(['spa_signed_at' => $d['spa_signed_at']]);
                // Subir la copia después reenvía la misma fecha: sin repetir la entrada.
                if ($cdd->isDirty('spa_signed_at')) $cdd->addLog('Contrato de compraventa firmado el ' . $d['spa_signed_at'] . '.');
                $this->storeDoc($cdd, $request, 'spa_file', 'contrato_firmado');
                break;

            case 'fiu':
                $d = $request->validate(['fiu_reported_at' => 'required|date', 'fiu_reference' => 'nullable|string|max:80']);
                $cdd->fill($d);
                $cdd->addLog('Informe a la FIU presentado el ' . $d['fiu_reported_at'] . '.');
                break;

            case 'predeed':
                $d = $request->validate(['predeed_notes' => 'nullable|string|max:2000']);
                abort_unless($cdd->spa_signed_at, 422);
                $cdd->fill($d + ['predeed_reviewed_at' => now(), 'predeed_reviewed_by' => $uid]);
                $cdd->addLog('Revisión de transacciones y del comprador antes de la escritura: luz verde.');
                break;

            case 'deed':
                $d = $request->validate([
                    'deed_date' => 'required|date',
                    'deed_file' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png',
                ]);
                abort_unless($cdd->predeed_reviewed_at, 422);
                $cdd->fill(['deed_date' => $d['deed_date']]);
                if ($cdd->isDirty('deed_date')) $cdd->addLog('Escritura firmada el ' . $d['deed_date'] . '. Expediente CDD archivado.');
                $this->storeDoc($cdd, $request, 'deed_file', 'escritura');
                break;

            default:
                abort(422);
        }

        $cdd->save();

        return redirect()->route('admin.crm.expediente.detalle', ['id' => $reservation->id, 'tab' => 'cumplimiento'])
            ->with('success', __('Cumplimiento actualizado.'));
    }

    /** Copia firmada del contrato o de la escritura, que cumplimiento tiene que archivar. */
    private function storeDoc(CddFile $cdd, Request $request, string $input, string $key): void
    {
        if (! $request->hasFile($input)) return;
        $docs = (array) ($cdd->kyc_docs ?? []);
        $docs[$key][] = $request->file($input)->store('cdd/' . $cdd->reservation_id . '/' . $key, 'local');
        $cdd->kyc_docs = $docs;
        $cdd->addLog('Copia archivada: ' . ($key === 'escritura' ? 'escritura' : 'contrato firmado') . '.');
    }

    public function doc(Reservation $reservation, string $key, int $i = 0)
    {
        $path = $this->file($reservation)->kyc_docs[$key][$i] ?? null;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $key . '-' . ($i + 1) . '-' . Str::slug($reservation->first_name . ' ' . $reservation->last_name) . '.' . pathinfo($path, PATHINFO_EXTENSION));
    }

    /** Informe de perfil de riesgo (RRP), imprimible: inicial antes de la escritura, final después. */
    public function report(Reservation $reservation)
    {
        $cdd = $this->file($reservation);
        $reservation->loadMissing('unit');

        return view('print.cdd-rrp', compact('reservation', 'cdd'));
    }

    /* ───────────────────────────── Cliente ───────────────────────────── */

    private function clientReservation(Request $request): ?Reservation
    {
        $q = Reservation::with('unit', 'cdd')->where('user_id', Auth::id());
        if ($id = $request->query('reservation')) $q->where('id', $id);

        return $q->latest()->first();
    }

    public function clientShow(Request $request)
    {
        abort_unless(CddFile::enabled(), 404);
        $reservation = $this->clientReservation($request);
        // Reserva web sin depósito: el propio comprador elige si compra como
        // persona física o a través de una sociedad, mientras no lo haya enviado.
        $tipo = $request->query('tipo');
        if ($reservation?->cdd && in_array($tipo, ['natural', 'legal'], true)
            && $reservation->cdd->kyc_status === 'solicitado' && ! $reservation->cdd->kyc_submitted_at) {
            $reservation->cdd->update(['buyer_type' => $tipo]);
        }

        return view('dashboard.cumplimiento', [
            'reservation' => $reservation,
            'cdd'         => $reservation?->cdd,
            'activeRoute' => 'cumplimiento',
        ]);
    }

    public function clientSubmit(Request $request)
    {
        abort_unless(CddFile::enabled(), 404);
        $reservation = $this->clientReservation($request);
        $cdd = $reservation?->cdd;
        abort_unless($cdd && in_array($cdd->kyc_status, ['solicitado', 'info_adicional'], true), 403);

        $type  = $cdd->buyer_type === 'legal' ? 'legal' : 'natural';
        $have  = (array) ($cdd->kyc_docs ?? []);
        $rules = [
            'decl.name'   => 'required|string|max:150',
            'decl.accept' => 'accepted',
        ];
        $req = fn (bool $r) => $r ? 'required' : 'nullable';

        if ($type === 'natural') {
            $second = $request->boolean('has_buyer2');
            foreach ([1, 2] as $n) {
                if ($n === 2 && ! $second) continue;
                foreach (CddForm::buyerFields() as $f) {
                    $rules["buyer$n.{$f[0]}"] = [$req($f[3]), 'string', 'max:255'];
                }
                $rules["buyer$n.income_type"] = ['required', 'in:' . implode(',', array_keys(CddForm::INCOME))];
                $rules["buyer$n.income"]      = ['nullable', 'array'];
                $rules["buyer$n.income.*.*"]  = ['nullable', 'string', 'max:255'];
            }
            $rules['payment.methods']   = ['required', 'array', 'min:1'];
            $rules['payment.methods.*'] = ['in:' . implode(',', array_keys(CddForm::PAYMENT))];
            $rules['payment.detail']    = ['nullable', 'array'];
            $rules['payment.detail.*.*'] = ['nullable', 'string', 'max:255'];
        } else {
            foreach (CddForm::companyFields() as $f) {
                $rules["company.{$f[0]}"] = [$req($f[3]), 'string', 'max:2000'];
            }
            foreach (CddForm::uboFields() as $f) {
                $rules["ubo.0.{$f[0]}"] = [$req($f[3]), 'string', 'max:255'];
            }
            // validate() sólo devuelve lo que tiene regla: sin estas, se perdían
            // los beneficiarios 2-4 y las entidades.
            foreach (array_merge(\App\Support\CddForm::uboFields(), [['sex']]) as $f) {
                $rules["ubo.*.{$f[0]}"] ??= ['nullable', 'string', 'max:255'];
            }
            foreach (CddForm::entityFields() as $f) {
                $rules["entities.*.{$f[0]}"] = ['nullable', 'string', 'max:255'];
            }
            $rules['ubo']             = ['array', 'max:4'];
            $rules['entities']        = ['nullable', 'array', 'max:6'];
            $rules['reasons']         = ['required', 'array', 'min:1'];
            $rules['reasons.*']       = ['in:' . implode(',', array_keys(CddForm::REASONS))];
            $rules['reasons_other']   = ['nullable', 'string', 'max:500'];
            $rules['purpose']         = ['required', 'in:' . implode(',', array_keys(CddForm::PURPOSE))];
            $rules['purpose_other']   = ['nullable', 'string', 'max:500'];
            $rules['description']     = ['nullable', 'string', 'max:2000'];
        }
        foreach (CddForm::docs($type) as $key => [$label, $required]) {
            $rules["docs.$key"]   = [$required && empty($have[$key]) ? 'required' : 'nullable', 'array', 'max:6'];
            $rules["docs.$key.*"] = ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'];
        }

        $names = collect(CddForm::docs($type))->mapWithKeys(fn ($d, $k) => ["docs.$k" => __($d[0])])->all();
        $data  = $request->validate($rules, [], $names);

        foreach (CddForm::docs($type) as $key => $d) {
            foreach ((array) $request->file("docs.$key", []) as $file) {
                $have[$key][] = $file->store('cdd/' . $reservation->id . '/' . $key, 'local');
            }
        }

        $form = collect($data)->except(['docs'])->all();
        foreach (['ubo', 'entities'] as $k) {
            if (isset($form[$k])) $form[$k] = array_values(array_filter((array) $form[$k], fn ($row) => array_filter((array) $row)));
        }
        if ($type === 'natural') $form['has_buyer2'] = $request->boolean('has_buyer2');

        $cdd->fill([
            'kyc_form'         => $form,
            'kyc_docs'         => $have,
            'kyc_status'       => 'enviado',
            'kyc_submitted_at' => now(),
        ]);
        $cdd->addLog('El comprador ha enviado el formulario KYC.');
        $cdd->save();
        if (CddFile::kycAtReservation()) $this->holdUnit($reservation, $cdd);
        self::alertCompliance($reservation, 'Formulario KYC enviado: hay que revisarlo y preparar el perfil de riesgo.');

        return redirect()->route('dashboard.cumplimiento')->with('success', __('Formulario enviado. El equipo de cumplimiento lo revisará y te avisará si necesita algo más.'));
    }

    /**
     * Sin depósito, la reserva se hace con el KYC: al enviarlo, la unidad queda
     * apartada mientras cumplimiento lo revisa (si nadie la tenía ya).
     */
    private function holdUnit(Reservation $reservation, CddFile $cdd): void
    {
        $unit = $reservation->unit;
        if (! $unit) return;
        $days = (int) config('company.cdd.kyc_hold_days', 7);
        $status = strtoupper((string) $unit->status);
        $free = $status === 'AVAILABLE'
            || ($status === 'RESERVED' && (int) $unit->reserved_by_reservation_id === (int) $reservation->id);
        if ($free) {
            $unit->update([
                'status'                     => 'RESERVED',
                'reserved_until'             => now()->addDays($days),
                'reserved_by_reservation_id' => $reservation->id,
            ]);
            $cdd->addLog("Unidad apartada $days días mientras cumplimiento revisa el KYC.");
        } else {
            $cdd->addLog('Ojo: la unidad ya estaba apartada para otra reserva; no se ha bloqueado.');
        }
        $cdd->save();
        if ($reservation->status === 'pending_payment') {
            $reservation->update(['status' => 'pending', 'expires_at' => now()->addDays($days)]);
        }
    }

    /**
     * Aviso al equipo de cumplimiento («the CDD Associate is alerted»). Además
     * de la campana del CRM, un email a COMPANY_CDD_EMAIL si está puesto.
     */
    public static function alertCompliance(Reservation $reservation, string $text): void
    {
        $to = config('company.cdd.compliance_email');
        if (! $to) return;
        $who = trim($reservation->first_name . ' ' . $reservation->last_name) . ' · ' . ($reservation->unit_name ?? $reservation->unit?->name ?? '');
        $url = route('admin.crm.expediente.detalle', ['id' => $reservation->id, 'tab' => 'cumplimiento']);
        try {
            Mail::raw("$text\n\n$who\n$url", fn ($m) => $m->to($to)->subject('Cumplimiento · ' . $who));
        } catch (\Throwable $e) {
            Log::warning('No se pudo avisar a cumplimiento: ' . $e->getMessage());
        }
    }

    private function notify(Reservation $reservation, string $kind, string $note = '', ?string $url = null): void
    {
        if (! $reservation->email) return;
        try {
            Mail::to($reservation->email)->send(new CddMail(
                name: (string) ($reservation->first_name ?: 'cliente'),
                kind: $kind,
                note: $note,
                url: $url ?? route('dashboard.cumplimiento'),
            ));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el aviso de KYC: ' . $e->getMessage());
        }
    }
}
