<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * El salvavidas: botón flotante de todas las páginas para mandar un ticket con
 * una captura de la zona que falla, un comentario y archivos. El script vive en
 * public/salvavidas y lo mete en cada página el middleware InjectSalvavidas.
 */
class SupportTicketController extends Controller
{
    private const COOKIE = 'salvavidas';
    private const DIR = 'salvavidas';

    public function store(Request $request)
    {
        $guest = ! Auth::check();
        $data = $request->validate([
            'comment'       => 'required|string|max:5000',
            'name'          => ($guest ? 'nullable' : 'prohibited').'|string|max:255',
            'email'         => ($guest ? 'required' : 'prohibited').'|email|max:255',
            'screenshot'    => 'nullable|file|mimetypes:image/png,image/jpeg,image/webp|max:12288',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,webp,gif,heic,pdf,doc,docx,xls,xlsx,csv,txt,mp4,mov,webm,zip',
            'page_url'      => 'nullable|string|max:2048',
            'page_title'    => 'nullable|string|max:255',
            'viewport'      => 'nullable|string|max:32',
        ]);

        $token = $guest ? ($request->cookie(self::COOKIE) ?: Str::random(40)) : null;
        $user = Auth::user();

        $ticket = new SupportTicket([
            'user_id'     => $user?->id,
            'guest_token' => $token,
            'name'        => $user?->name ?? ($data['name'] ?? null),
            'email'       => $user?->email ?? $data['email'],
            'comment'     => $data['comment'],
            'page_url'    => $data['page_url'] ?? null,
            'page_title'  => $data['page_title'] ?? null,
            'viewport'    => $data['viewport'] ?? null,
            'user_agent'  => Str::limit((string) $request->userAgent(), 500, ''),
            'status'      => 'abierto',
        ]);
        $ticket->save();

        $dir = self::DIR.'/'.$ticket->id;
        if ($request->hasFile('screenshot')) {
            $ticket->screenshot = $request->file('screenshot')->store($dir, 'local');
        }
        $files = [];
        foreach ((array) $request->file('attachments', []) as $file) {
            $files[] = [
                'path' => $file->store($dir, 'local'),
                'name' => Str::limit($file->getClientOriginalName(), 180, ''),
                'size' => $file->getSize(),
            ];
        }
        $ticket->attachments = $files ?: null;
        $ticket->save();

        $response = response()->json(['success' => true, 'code' => $ticket->code]);
        return $token ? $response->withCookie(cookie(self::COOKIE, $token, 60 * 24 * 365)) : $response;
    }

    /** «Mis tickets»: JSON para el panel del salvavidas. Marca las respuestas como vistas. */
    public function mine(Request $request)
    {
        $tickets = SupportTicket::ownedBy(Auth::user(), $request->cookie(self::COOKIE))
            ->latest()->take(50)->get();

        $out = $tickets->map(fn (SupportTicket $t) => [
            'code'        => $t->code,
            'status'      => $t->status,
            'status_label'=> $t->statusLabel(),
            'comment'     => $t->comment,
            'page_url'    => $t->page_url,
            'created_at'  => $t->created_at->translatedFormat('j M Y, H:i'),
            'reply'       => $t->reply,
            'replied_at'  => $t->replied_at?->translatedFormat('j M Y, H:i'),
            'new_reply'   => $t->hasUnseenReply(),
            'screenshot'  => $t->screenshot ? route('salvavidas.file', [$t, 'captura']) : null,
            'attachments' => collect($t->attachments ?? [])->map(fn ($a, $i) => [
                'name' => $a['name'], 'url' => route('salvavidas.file', [$t, $i]),
            ])->values(),
        ]);

        SupportTicket::whereIn('id', $tickets->filter->hasUnseenReply()->pluck('id'))->update(['seen_at' => now()]);

        return response()->json(['tickets' => $out]);
    }

    /** Número de respuestas sin leer, para el punto del botón. */
    public function unread(Request $request)
    {
        $n = SupportTicket::ownedBy(Auth::user(), $request->cookie(self::COOKIE))
            ->whereNotNull('replied_at')
            ->where(fn ($q) => $q->whereNull('seen_at')->orWhereColumn('seen_at', '<', 'replied_at'))
            ->count();
        return response()->json(['unread' => $n]);
    }

    /** Captura o adjunto: solo para el dueño del ticket o el equipo. */
    public function file(Request $request, SupportTicket $ticket, string $key)
    {
        $user = Auth::user();
        $staff = $user && ! in_array($user->role, ['user', 'broker'], true);
        $owner = SupportTicket::ownedBy($user, $request->cookie(self::COOKIE))->whereKey($ticket->id)->exists();
        abort_unless($staff || $owner, 403);

        if ($key === 'captura') {
            $path = $ticket->screenshot;
            $name = $ticket->code.'-captura.'.pathinfo((string) $path, PATHINFO_EXTENSION);
        } else {
            $a = ($ticket->attachments ?? [])[(int) $key] ?? null;
            $path = $a['path'] ?? null;
            $name = $a['name'] ?? null;
        }
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, $name, [], $request->boolean('descargar') ? 'attachment' : 'inline');
    }

    // ── Admin ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $filtro = $request->query('estado', 'pendientes');
        $q = SupportTicket::with('user', 'replier')->latest();
        if ($filtro === 'pendientes') {
            $q->where('status', '!=', 'resuelto');
        } elseif (isset(SupportTicket::STATUSES[$filtro])) {
            $q->where('status', $filtro);
        }

        $items = $q->paginate(30)->withQueryString();
        $stats = SupportTicket::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status');

        return view('admin.crm.tickets', compact('items', 'stats', 'filtro'));
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(SupportTicket::STATUSES)),
            'reply'  => 'nullable|string|max:5000',
        ]);

        $ticket->status = $data['status'];
        $reply = trim((string) ($data['reply'] ?? ''));
        if ($reply !== '' && $reply !== (string) $ticket->reply) {
            $ticket->reply = $reply;
            $ticket->replied_by = Auth::id();
            $ticket->replied_at = now();
        }
        $ticket->save();

        return back()->with('success', __('Ticket :c actualizado.', ['c' => $ticket->code]));
    }
}
