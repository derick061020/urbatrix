<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrokerMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = BrokerMaterial::orderBy('sort_order')->orderByDesc('created_at')->get();

        return view('admin.materials', compact('materials'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = Auth::id();
        $data['visible'] = $request->boolean('visible', true);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('broker-materials', 'public');
            $data['file_size'] = $this->humanSize($file->getSize());
            $data['format'] = strtoupper($file->getClientOriginalExtension());
        } elseif (! empty($data['external_url'])) {
            $data['format'] = $this->formatFromUrl($data['external_url']);
        }

        BrokerMaterial::create($data);

        return back()->with('success', 'Material agregado correctamente.');
    }

    public function update(Request $request, BrokerMaterial $material)
    {
        $data = $this->validateData($request);
        $data['visible'] = $request->boolean('visible', $material->visible);

        if ($request->hasFile('file')) {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $file = $request->file('file');
            $data['file_path'] = $file->store('broker-materials', 'public');
            $data['file_size'] = $this->humanSize($file->getSize());
            $data['format'] = strtoupper($file->getClientOriginalExtension());
        } elseif (! empty($data['external_url']) && $data['external_url'] !== $material->external_url) {
            $data['format'] = $this->formatFromUrl($data['external_url']);
        }

        $material->update($data);

        return back()->with('success', 'Material actualizado.');
    }

    public function toggleVisible(BrokerMaterial $material)
    {
        $material->update(['visible' => ! $material->visible]);

        return back()->with('success', $material->visible ? 'Material visible para brokers.' : 'Material oculto.');
    }

    public function destroy(BrokerMaterial $material)
    {
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }
        $material->delete();

        return back()->with('success', 'Material eliminado.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'        => 'required|string|max:160',
            'description'  => 'nullable|string|max:1000',
            'category'     => 'nullable|string|max:80',
            'icon'         => 'nullable|string|max:60',
            'external_url' => 'nullable|url|max:500',
            'file'         => 'nullable|file|max:2097152', // 2 GB
            'sort_order'   => 'nullable|integer|min:0',
        ]);
    }

    /** Deriva el formato (extensión en mayúsculas) desde la URL externa. */
    private function formatFromUrl(string $url): string
    {
        $ext = strtoupper(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        return $ext !== '' ? substr($ext, 0, 12) : 'LINK';
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
    }
}
