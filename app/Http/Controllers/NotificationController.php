<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Document;
use App\Models\ExportAuthorization;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    private const SESSION_KEY = 'notifications_last_seen';

    /** GET /admin/notifications */
    public function admin()
    {
        /** @var \App\Models\User $u */
        $u = Auth::user();
        $isBroker = $u->isBroker();
        $brokerUnitIds = $isBroker
            ? $u->assignedUnits()->pluck('units.id')->map(fn ($i) => (string) $i)->all()
            : null;

        $items = collect();

        // Mensajes nuevos de clientes (no leídos)
        $msgQuery = Message::query()
            ->with(['reservation', 'sender'])
            ->where('sender_role', 'client')
            ->whereNull('read_at');
        if ($isBroker) {
            $msgQuery->whereHas('reservation', fn ($q) => $q->whereIn('unit_id', $brokerUnitIds));
        }
        foreach ($msgQuery->latest()->limit(10)->get() as $m) {
            $items->push([
                'id'         => 'msg-'.$m->id,
                'icon'       => 'pi-comment',
                'color'      => 'blue',
                'title'      => 'Nuevo mensaje de '.($m->sender->name ?? 'cliente'),
                'body'       => \Illuminate\Support\Str::limit((string) $m->body, 90),
                'created_at' => $m->created_at,
                'url'        => route('admin.communication'),
            ]);
        }

        // Pagos pendientes de aprobación
        $payQuery = Payment::query()
            ->with('reservation')
            ->where('approval_status', 'pending');
        if ($isBroker) {
            $payQuery->whereHas('reservation', fn ($q) => $q->whereIn('unit_id', $brokerUnitIds));
        }
        foreach ($payQuery->latest()->limit(10)->get() as $p) {
            $items->push([
                'id'         => 'pay-'.$p->id,
                'icon'       => 'pi-credit-card',
                'color'      => 'amber',
                'title'      => 'Pago pendiente de aprobación',
                'body'       => 'RD$ '.number_format((float) $p->amount, 2).' · '.($p->label ?: $p->payment_type),
                'created_at' => $p->updated_at ?: $p->created_at,
                'url'        => $p->reservation_id ? route('admin.crm.pagos', $p->reservation_id) : route('admin.crm.expedientes'),
            ]);
        }

        if (! $isBroker) {
            // Aprobaciones pendientes
            foreach (Approval::where('status', 'pendiente')->latest()->limit(10)->get() as $a) {
                $items->push([
                    'id'         => 'apr-'.$a->id,
                    'icon'       => 'pi-check-square',
                    'color'      => 'amber',
                    'title'      => 'Aprobación pendiente',
                    'body'       => $a->type.' · '.$a->priority,
                    'created_at' => $a->created_at,
                    'url'        => route('admin.crm.aprobaciones'),
                ]);
            }

            // Usuarios pendientes de verificación (KYC)
            if (Schema::hasColumn('users', 'verification_status')) {
                foreach (User::where('verification_status', 'pending')->latest()->limit(10)->get() as $usr) {
                    $items->push([
                        'id'         => 'kyc-'.$usr->id,
                        'icon'       => 'pi-id-card',
                        'color'      => 'amber',
                        'title'      => 'Verificación KYC pendiente',
                        'body'       => $usr->name.' — '.$usr->email,
                        'created_at' => $usr->updated_at ?: $usr->created_at,
                        'url'        => route('admin.profiles'),
                    ]);
                }
            }

            // Solicitudes de código de exportación pendientes
            $exportRequests = ExportAuthorization::with('requester')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest('id')
                ->limit(10)
                ->get();
            foreach ($exportRequests as $ex) {
                $resourceLabel = ucfirst($ex->resource);
                $items->push([
                    'id'         => 'exp-'.$ex->id,
                    'icon'       => 'pi-key',
                    'color'      => 'amber',
                    'title'      => 'Código de exportación · '.$ex->code,
                    'body'       => ($ex->requester->name ?? 'Usuario').' solicitó exportar '.$resourceLabel.'. Compártele este código.',
                    'created_at' => $ex->created_at,
                    'url'        => route('admin.crm.expedientes'),
                ]);
            }

            // Tareas vencidas o pendientes
            foreach (Task::whereIn('status', ['pendiente', 'vencida'])->latest()->limit(8)->get() as $t) {
                $items->push([
                    'id'         => 'task-'.$t->id,
                    'icon'       => 'pi-check',
                    'color'      => $t->status === 'vencida' ? 'red' : 'gray',
                    'title'      => $t->status === 'vencida' ? 'Tarea vencida' : 'Tarea pendiente',
                    'body'       => $t->title ?: ('Tarea #'.$t->id),
                    'created_at' => $t->created_at,
                    'url'        => route('admin.crm.tareas'),
                ]);
            }
        }

        return $this->respond($items);
    }

    /** GET /dashboard/notifications */
    public function client()
    {
        $userId = Auth::id();
        $items  = collect();

        // Mensajes del admin no leídos
        $unread = Message::query()
            ->with(['reservation', 'sender'])
            ->whereHas('reservation', fn ($q) => $q->where('user_id', $userId))
            ->where('sender_role', 'admin')
            ->whereNull('read_at')
            ->latest()->limit(10)->get();
        foreach ($unread as $m) {
            $items->push([
                'id'         => 'msg-'.$m->id,
                'icon'       => 'pi-comment',
                'color'      => 'blue',
                'title'      => 'Mensaje de '.($m->sender->name ?? 'Admin'),
                'body'       => \Illuminate\Support\Str::limit((string) $m->body, 90),
                'created_at' => $m->created_at,
                'url'        => route('dashboard.messages'),
            ]);
        }

        // Pagos pendientes
        $pendingPays = Payment::whereHas('reservation', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 'pending')->latest()->limit(8)->get();
        foreach ($pendingPays as $p) {
            $items->push([
                'id'         => 'pay-'.$p->id,
                'icon'       => 'pi-credit-card',
                'color'      => 'amber',
                'title'      => 'Pago pendiente',
                'body'       => 'RD$ '.number_format((float) $p->amount, 2).' · '.($p->label ?: $p->payment_type),
                'created_at' => $p->updated_at ?: $p->created_at,
                'url'        => route('dashboard.payments'),
            ]);
        }

        // Documentos pendientes de firma o revisión
        $reservationIds = Reservation::where('user_id', $userId)->pluck('id');
        if ($reservationIds->isNotEmpty()) {
            $pendingDocs = Document::whereIn('reservation_id', $reservationIds)
                ->whereIn('status', ['pending', 'generated', 'awaiting_signature', 'in_review'])
                ->latest()->limit(8)->get();
            foreach ($pendingDocs as $d) {
                $items->push([
                    'id'         => 'doc-'.$d->id,
                    'icon'       => 'pi-file',
                    'color'      => 'blue',
                    'title'      => 'Documento por revisar',
                    'body'       => $d->title ?: $d->filename,
                    'created_at' => $d->updated_at ?: $d->created_at,
                    'url'        => route('dashboard.acuerdos'),
                ]);
            }

            // Presupuesto enviado (budget_status = sent)
            $budgetReady = Reservation::where('user_id', $userId)
                ->where('budget_status', 'sent')
                ->latest()->limit(3)->get();
            foreach ($budgetReady as $r) {
                $items->push([
                    'id'         => 'budget-'.$r->id,
                    'icon'       => 'pi-file-edit',
                    'color'      => 'green',
                    'title'      => 'Presupuesto disponible',
                    'body'       => 'Revisa el presupuesto de tu reserva '.($r->reservation_code ? '#'.$r->reservation_code : ''),
                    'created_at' => $r->budget_sent_at ?: $r->updated_at,
                    'url'        => route('dashboard.budget', $r->id),
                ]);
            }
        }

        return $this->respond($items);
    }

    /** POST /(admin|dashboard)/notifications/read — marks all as read by stamping a session timestamp. */
    public function read(Request $request)
    {
        $request->session()->put(self::SESSION_KEY, now()->toIso8601String());
        return response()->json(['ok' => true]);
    }

    private function respond(Collection $items)
    {
        $lastSeen = session(self::SESSION_KEY);
        $lastSeenAt = $lastSeen ? Carbon::parse($lastSeen) : null;

        $items = $items
            ->sortByDesc(fn ($i) => $i['created_at'] ? Carbon::parse($i['created_at'])->getTimestamp() : 0)
            ->take(15)
            ->map(function ($i) use ($lastSeenAt) {
                $when = $i['created_at'] ? Carbon::parse($i['created_at']) : null;
                return [
                    'id'      => $i['id'],
                    'icon'    => $i['icon'],
                    'color'   => $i['color'],
                    'title'   => $i['title'],
                    'body'    => $i['body'],
                    'when'    => $when ? $when->locale('es')->diffForHumans() : '',
                    'url'     => $i['url'],
                    'unread'  => !$lastSeenAt || ($when && $when->gt($lastSeenAt)),
                ];
            })
            ->values()
            ->all();

        $unreadCount = collect($items)->where('unread', true)->count();

        return response()->json(['items' => $items, 'unread' => $unreadCount]);
    }
}
