<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Unit;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    private const ADMIN_PAGES = [
        ['label' => 'Dashboard',              'route' => 'admin.crm.dashboard',      'icon' => 'pi-th-large'],
        ['label' => 'Expedientes',            'route' => 'admin.crm.expedientes',    'icon' => 'pi-folder'],
        ['label' => 'Documentos',             'route' => 'admin.crm.documentos',     'icon' => 'pi-file'],
        ['label' => 'Reservas y Contratos',   'route' => 'admin.crm.contratos',      'icon' => 'pi-id-card'],
        ['label' => 'Transacciones',          'route' => 'admin.transactions-report','icon' => 'pi-credit-card'],
        ['label' => 'Proyectos',              'route' => 'admin.crm.proyectos',      'icon' => 'pi-building'],
        ['label' => 'Unidades',               'route' => 'admin.units',              'icon' => 'pi-home'],
        ['label' => 'Avance de Obra',         'route' => 'admin.crm.avance-obra',    'icon' => 'pi-chart-line'],
        ['label' => 'Mensajes',               'route' => 'admin.communication',      'icon' => 'pi-comments'],
        ['label' => 'Plantillas',             'route' => 'admin.crm.plantillas',     'icon' => 'pi-envelope'],
        ['label' => 'Anuncios',               'route' => 'admin.crm.anuncios',       'icon' => 'pi-megaphone'],
        ['label' => 'Usuarios',               'route' => 'admin.profiles',           'icon' => 'pi-user'],
        ['label' => 'Brokers',                'route' => 'admin.agents',             'icon' => 'pi-briefcase'],
        ['label' => 'Aprobaciones',           'route' => 'admin.crm.aprobaciones',   'icon' => 'pi-check-square'],
        ['label' => 'Tareas',                 'route' => 'admin.crm.tareas',         'icon' => 'pi-check'],
    ];

    private const CLIENT_PAGES = [
        ['label' => 'Mi propiedad',  'route' => 'dashboard',              'icon' => 'pi-home'],
        ['label' => 'Mis documentos','route' => 'dashboard.documents',    'icon' => 'pi-folder-open'],
        ['label' => 'Acuerdos',      'route' => 'dashboard.acuerdos',     'icon' => 'pi-check-square'],
        ['label' => 'Plan de pagos', 'route' => 'dashboard.payments',     'icon' => 'pi-credit-card'],
        ['label' => 'Guardados',     'route' => 'dashboard.guardados',    'icon' => 'pi-heart'],
        ['label' => 'Mensajes',      'route' => 'dashboard.messages',     'icon' => 'pi-comments'],
        ['label' => 'Avance de Obra','route' => 'dashboard.progress',     'icon' => 'pi-chart-line'],
        ['label' => 'Calendario',    'route' => 'dashboard.calendario',   'icon' => 'pi-calendar'],
        ['label' => 'Mi perfil',     'route' => 'dashboard.profile.edit', 'icon' => 'pi-user'],
    ];

    /** Admin/broker live search across users, expedientes and units. */
    public function admin(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['groups' => []]);
        }

        $like  = '%'.$q.'%';
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $isBroker = $authUser->isBroker();
        $brokerUnitIds = $isBroker
            ? $authUser->assignedUnits()->pluck('units.id')->map(fn ($i) => (string) $i)->all()
            : null;

        $groups = [];

        /* --------- Clientes (users) --------- */
        if (! $isBroker) {
            $users = User::query()
                ->where('role', '!=', 'admin')
                ->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                      ->orWhere('email', 'like', $like)
                      ->orWhere('first_name', 'like', $like)
                      ->orWhere('last_name', 'like', $like)
                      ->orWhere('phone', 'like', $like);
                })
                ->limit(6)
                ->get(['id', 'name', 'email', 'avatar']);

            $clientItems = $users->map(fn ($u) => [
                'label'   => $u->name ?: $u->email,
                'sub'     => $u->email,
                'icon'    => 'pi-user',
                'url'     => route('admin.profiles').'?q='.urlencode($u->name ?: $u->email),
            ])->all();
            if ($clientItems) $groups[] = ['title' => 'Clientes', 'items' => $clientItems];
        }

        /* --------- Expedientes (reservations) --------- */
        $resQuery = Reservation::query()
            ->where(function ($w) use ($like) {
                $w->where('first_name', 'like', $like)
                  ->orWhere('last_name', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('reservation_code', 'like', $like)
                  ->orWhere('unit_name', 'like', $like);
            });
        if ($isBroker) $resQuery->whereIn('unit_id', $brokerUnitIds);

        $reservations = $resQuery->limit(6)
            ->get(['id', 'first_name', 'last_name', 'email', 'reservation_code', 'unit_name']);

        $resItems = $reservations->map(function ($r) {
            $name = trim($r->first_name.' '.$r->last_name) ?: $r->email;
            return [
                'label' => ($r->reservation_code ? '#'.$r->reservation_code.' · ' : '').$name,
                'sub'   => $r->unit_name ?: $r->email,
                'icon'  => 'pi-folder',
                'url'   => route('admin.crm.expediente.detalle', $r->id),
            ];
        })->all();
        if ($resItems) $groups[] = ['title' => 'Expedientes', 'items' => $resItems];

        /* --------- Unidades --------- */
        $unitQuery = Unit::query()
            ->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('plot', 'like', $like)
                  ->orWhere('type', 'like', $like);
            });
        if ($isBroker) $unitQuery->whereIn('id', $brokerUnitIds);

        $units = $unitQuery->limit(6)->get(['id', 'name', 'plot', 'type', 'status']);

        $unitItems = $units->map(fn ($u) => [
            'label' => $u->name ?: ('Unidad #'.$u->id),
            'sub'   => trim(($u->type ? ucfirst($u->type) : '').($u->plot ? ' · '.$u->plot : '')) ?: ($u->status ?? ''),
            'icon'  => 'pi-home',
            'url'   => $isBroker ? route('admin.crm.contratos') : route('admin.units.edit', $u->id),
        ])->all();
        if ($unitItems) $groups[] = ['title' => 'Unidades', 'items' => $unitItems];

        /* --------- Páginas (nav) --------- */
        $pages = $this->filterPages(self::ADMIN_PAGES, $q, $isBroker);
        if ($pages) $groups[] = ['title' => 'Páginas', 'items' => $pages];

        return response()->json(['groups' => $groups]);
    }

    /** Client live search across their own reservation, documents, payments, wishlist + nav. */
    public function client(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['groups' => []]);
        }

        $userId = Auth::id();
        $like   = '%'.$q.'%';
        $groups = [];

        /* --------- Mi reserva (expediente) --------- */
        $reservation = Reservation::where('user_id', $userId)
            ->where(function ($w) use ($like) {
                $w->where('reservation_code', 'like', $like)
                  ->orWhere('unit_name', 'like', $like)
                  ->orWhere('first_name', 'like', $like)
                  ->orWhere('last_name', 'like', $like);
            })
            ->first();
        if ($reservation) {
            $groups[] = ['title' => 'Mi reserva', 'items' => [[
                'label' => 'Reserva '.($reservation->reservation_code ? '#'.$reservation->reservation_code : '').' — '.($reservation->unit_name ?: ''),
                'sub'   => 'Ir a Mi propiedad',
                'icon'  => 'pi-key',
                'url'   => route('dashboard'),
            ]]];
        }

        /* --------- Documentos del usuario --------- */
        $docs = Document::query()
            ->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)
                  ->orWhere('filename', 'like', $like)
                  ->orWhere('document_type', 'like', $like);
            })
            ->where(function ($w) use ($userId) {
                $w->whereHas('reservation', fn ($q) => $q->where('user_id', $userId));
                if (Schema::hasColumn('documents', 'metadata')) {
                    $w->orWhereJsonContains('metadata->user_id', $userId);
                }
            })
            ->limit(5)
            ->get(['id', 'title', 'filename', 'document_type']);

        $docItems = $docs->map(fn ($d) => [
            'label' => $d->title ?: $d->filename,
            'sub'   => $d->document_type,
            'icon'  => 'pi-file',
            'url'   => route('dashboard.documents'),
        ])->all();
        if ($docItems) $groups[] = ['title' => 'Documentos', 'items' => $docItems];

        /* --------- Pagos --------- */
        $payments = Payment::query()
            ->whereHas('reservation', fn ($q) => $q->where('user_id', $userId))
            ->where(function ($w) use ($like) {
                $w->where('label', 'like', $like)
                  ->orWhere('payment_type', 'like', $like)
                  ->orWhere('notes', 'like', $like)
                  ->orWhere('status', 'like', $like)
                  ->orWhere('amount', 'like', $like);
            })
            ->limit(5)
            ->get(['id', 'label', 'payment_type', 'amount', 'status']);

        $paymentItems = $payments->map(fn ($p) => [
            'label' => $p->label ?: ($p->payment_type ?: ('Pago #'.$p->id)),
            'sub'   => 'RD$ '.number_format((float) $p->amount, 2).' · '.$p->status,
            'icon'  => 'pi-credit-card',
            'url'   => route('dashboard.payments'),
        ])->all();
        if ($paymentItems) $groups[] = ['title' => 'Pagos', 'items' => $paymentItems];

        /* --------- Guardados (wishlist) --------- */
        $wishlistUnitIds = Wishlist::where('user_id', $userId)->pluck('unit_id');
        if ($wishlistUnitIds->isNotEmpty()) {
            $savedUnits = Unit::whereIn('id', $wishlistUnitIds)
                ->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                      ->orWhere('type', 'like', $like)
                      ->orWhere('plot', 'like', $like);
                })
                ->limit(5)
                ->get(['id', 'name', 'type', 'plot', 'price']);

            $savedItems = $savedUnits->map(fn ($u) => [
                'label' => $u->name ?: ('Unidad #'.$u->id),
                'sub'   => trim(($u->type ? ucfirst($u->type) : '').($u->plot ? ' · '.$u->plot : '')),
                'icon'  => 'pi-heart',
                'url'   => route('dashboard.guardados'),
            ])->all();
            if ($savedItems) $groups[] = ['title' => 'Guardados', 'items' => $savedItems];
        }

        /* --------- Páginas (nav) --------- */
        $pages = $this->filterPages(self::CLIENT_PAGES, $q, false);
        if ($pages) $groups[] = ['title' => 'Páginas', 'items' => $pages];

        return response()->json(['groups' => $groups]);
    }

    private function filterPages(array $pages, string $q, bool $isBroker): array
    {
        $needle = $this->normalize($q);
        $brokerHidden = ['admin.communication','admin.crm.plantillas','admin.crm.anuncios','admin.profiles','admin.agents','admin.crm.aprobaciones','admin.crm.tareas','admin.crm.proyectos','admin.units','admin.crm.avance-obra'];

        return collect($pages)
            ->filter(fn ($p) => str_contains($this->normalize($p['label']), $needle))
            ->reject(fn ($p) => $isBroker && in_array($p['route'], $brokerHidden, true))
            ->map(fn ($p) => [
                'label' => $p['label'],
                'sub'   => 'Ir a la sección',
                'icon'  => $p['icon'],
                'url'   => route($p['route']),
            ])
            ->take(6)
            ->values()
            ->all();
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower($s);
        return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
    }
}
