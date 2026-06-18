<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Imports\CsvImporter;
use App\Support\Imports\ImportResource;
use App\Support\Imports\ImportResourceRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Importador de datos estilo "WP All Import": el admin sube un CSV, mapea las
 * columnas a los campos de la plataforma y los registros quedan importados.
 */
class DataImportController extends Controller
{
    public function __construct(private CsvImporter $importer)
    {
    }

    /** Pantalla inicial: elegir entidad + subir CSV. */
    public function index()
    {
        $resources = ImportResourceRegistry::all();

        return view('admin.data-import.index', compact('resources'));
    }

    /** Descarga un CSV de ejemplo con las cabeceras y una fila demo. */
    public function sample(string $resource)
    {
        $res = ImportResourceRegistry::findOrFail($resource);
        $fields = $res->fields();

        $headers = array_map(fn ($def) => $def['label'], $fields);
        $sample  = array_map(fn ($def) => $def['sample'] ?? '', $fields);

        $filename = 'ejemplo_' . $res->key() . '.csv';

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'w');
            // BOM para que Excel reconozca UTF-8
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            fputcsv($out, $sample);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Recibe el CSV, lo guarda temporalmente y muestra la pantalla de mapeo. */
    public function upload(Request $request, string $resource)
    {
        $res = ImportResourceRegistry::findOrFail($resource);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'file.mimes' => 'El archivo debe ser un CSV.',
            'file.max'   => 'El archivo no puede superar los 5 MB.',
        ]);

        // Guardar en storage/app/imports con un nombre único.
        $token = Str::uuid()->toString();
        $path  = $request->file('file')->storeAs('imports', $token . '.csv');
        $absolute = Storage::path($path);

        $headers = $this->importer->readHeaders($absolute);

        if (empty($headers)) {
            Storage::delete($path);

            return redirect()->route('admin.data-import')
                ->withErrors(['file' => 'El CSV está vacío o no se pudo leer.']);
        }

        $preview     = $this->importer->previewRows($absolute, 5);
        $suggestions = $this->suggestMapping($headers, $res);

        return view('admin.data-import.mapping', [
            'resource'    => $res,
            'token'       => $token,
            'headers'     => $headers,
            'preview'     => $preview,
            'suggestions' => $suggestions,
            'fields'      => $res->fields(),
        ]);
    }

    /** Ejecuta la importación con el mapeo elegido y muestra el resumen. */
    public function run(Request $request, string $resource)
    {
        $res = ImportResourceRegistry::findOrFail($resource);

        $validated = $request->validate([
            'token'       => ['required', 'string'],
            'mode'        => ['required', 'in:create,update,upsert'],
            'match_field' => ['nullable', 'string'],
            'mapping'     => ['required', 'array'],
        ]);

        $path = 'imports/' . basename($validated['token']) . '.csv';

        if (! Storage::exists($path)) {
            return redirect()->route('admin.data-import')
                ->withErrors(['file' => 'El archivo subido expiró. Volvé a subirlo.']);
        }

        // Limpiar el mapeo: ignorar columnas marcadas vacías o con campos inexistentes.
        $fields = $res->fields();
        $mapping = [];
        foreach ($validated['mapping'] as $colIndex => $field) {
            $mapping[(int) $colIndex] = ($field && isset($fields[$field])) ? $field : null;
        }

        $mode       = $validated['mode'];
        $matchField = $validated['match_field'] ?? null;

        // En modos que actualizan, el campo de coincidencia debe estar mapeado.
        if (in_array($mode, ['update', 'upsert'], true)) {
            if (! $matchField || ! in_array($matchField, $mapping, true)) {
                return back()->withErrors([
                    'match_field' => 'Elegí un campo de coincidencia que esté mapeado a una columna del CSV.',
                ])->withInput();
            }
        }

        $summary = $this->importer->import(
            $res,
            Storage::path($path),
            $mapping,
            $mode,
            $matchField,
        );

        Storage::delete($path);

        return view('admin.data-import.result', [
            'resource' => $res,
            'summary'  => $summary,
            'mode'     => $mode,
        ]);
    }

    /**
     * Sugiere, para cada columna del CSV, el campo destino más probable
     * comparando el nombre de la columna con el campo y su label.
     *
     * @param  array<int, string>  $headers
     * @return array<int, string|null>  índice de columna => campo sugerido
     */
    protected function suggestMapping(array $headers, ImportResource $res): array
    {
        $fields = $res->fields();

        $lookup = [];
        foreach ($fields as $field => $def) {
            $lookup[$this->normalize($field)] = $field;
            $lookup[$this->normalize($def['label'])] = $field;
        }

        $suggestions = [];
        foreach ($headers as $i => $header) {
            $suggestions[$i] = $lookup[$this->normalize($header)] ?? null;
        }

        return $suggestions;
    }

    protected function normalize(string $value): string
    {
        $value = Str::ascii($value);
        $value = strtolower($value);

        return preg_replace('/[^a-z0-9]/', '', $value) ?? $value;
    }
}
