<?php

namespace App\Http\Controllers;

use App\Models\CrmAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Anuncios internos del CRM: publicar, editar y archivar. */
class CrmAnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $archived = $request->boolean('archivados');
        $items = CrmAnnouncement::with('author')
            ->when($archived, fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->active())
            ->orderByDesc('pinned')->latest()->get();

        return view('admin.crm.anuncios', [
            'pinned'        => $items->where('pinned', true)->values(),
            'recent'        => $items->where('pinned', false)->values(),
            'archived'      => $archived,
            'archivedCount' => CrmAnnouncement::whereNotNull('archived_at')->count(),
        ]);
    }

    public function store(Request $request)
    {
        CrmAnnouncement::create($this->validated($request) + ['user_id' => $request->user()->id]);

        return back()->with('success', __('Anuncio publicado.'));
    }

    public function update(Request $request, CrmAnnouncement $announcement)
    {
        $announcement->update($this->validated($request));

        return back()->with('success', __('Anuncio actualizado.'));
    }

    public function archive(CrmAnnouncement $announcement)
    {
        $announcement->update(['archived_at' => $announcement->archived_at ? null : now()]);

        return back()->with('success', $announcement->archived_at ? __('Anuncio archivado.') : __('Anuncio recuperado.'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'      => 'required|string|max:160',
            'body'       => 'required|string|max:4000',
            'audience'   => 'nullable|array',
            'audience.*' => [Rule::in(CrmAnnouncement::AUDIENCES)],
        ]);
        $data['pinned'] = $request->boolean('pinned');
        $data['audience'] = array_values($data['audience'] ?? []);

        return $data;
    }
}
