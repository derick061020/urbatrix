<?php

namespace App\Support\Imports;

/**
 * Lógica de parseo e importación de CSV, independiente de HTTP.
 * Usa fgetcsv (PHP nativo), autodetecta el delimitador y limpia BOM/UTF-8.
 */
class CsvImporter
{
    /**
     * Cabeceras (primera fila) del CSV.
     *
     * @return array<int, string>
     */
    public function readHeaders(string $path): array
    {
        $delimiter = ',';
        $handle = $this->open($path, $delimiter);
        $headers = fgetcsv($handle, 0, $delimiter, '"', '') ?: [];
        fclose($handle);

        return array_map(fn ($h) => $this->clean((string) $h), $headers);
    }

    /**
     * Primeras $limit filas de datos (sin la cabecera) como arrays indexados.
     *
     * @return array<int, array<int, string>>
     */
    public function previewRows(string $path, int $limit = 5): array
    {
        $delimiter = ',';
        $handle = $this->open($path, $delimiter);
        fgetcsv($handle, 0, $delimiter, '"', ''); // saltar cabecera

        $rows = [];
        while (count($rows) < $limit && ($row = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }
            $rows[] = array_map(fn ($v) => $this->clean((string) $v), $row);
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Importa el CSV al modelo del recurso.
     *
     * @param  array<int, string|null>  $mapping  índice de columna del CSV => campo destino (o null para ignorar)
     * @param  string  $mode  create | update | upsert
     * @param  string|null  $matchField  campo destino usado para detectar duplicados (modos update/upsert)
     * @return array{created:int, updated:int, skipped:int, errors:array<int, array{row:int, message:string}>}
     */
    public function import(ImportResource $resource, string $path, array $mapping, string $mode, ?string $matchField): array
    {
        $fields = $resource->fields();
        $model  = $resource->model();

        $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        $delimiter = ',';
        $handle = $this->open($path, $delimiter);
        fgetcsv($handle, 0, $delimiter, '"', ''); // saltar cabecera

        $rowNumber = 1; // la cabecera es la fila 1
        while (($row = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $rowNumber++;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            // Construir atributos según el mapeo
            $attributes = [];
            $rowError = null;

            foreach ($mapping as $colIndex => $field) {
                if (! $field || ! isset($fields[$field])) {
                    continue;
                }
                $raw = $this->clean((string) ($row[$colIndex] ?? ''));

                if ($raw === '') {
                    continue;
                }

                $cast = $this->castValue($raw, $fields[$field]);
                if ($cast['error']) {
                    $rowError = $cast['error'];
                    break;
                }
                $attributes[$field] = $cast['value'];
            }

            if ($rowError) {
                $summary['errors'][] = ['row' => $rowNumber, 'message' => $rowError];
                $summary['skipped']++;
                continue;
            }

            // Validar requeridos
            foreach ($fields as $field => $def) {
                if (($def['required'] ?? false) && empty($attributes[$field])) {
                    $rowError = "Falta el campo obligatorio «{$def['label']}».";
                    break;
                }
            }

            if ($rowError) {
                $summary['errors'][] = ['row' => $rowNumber, 'message' => $rowError];
                $summary['skipped']++;
                continue;
            }

            // Resolver duplicado
            $existing = null;
            if ($matchField && isset($attributes[$matchField]) && in_array($mode, ['update', 'upsert'], true)) {
                $existing = $model::where($matchField, $attributes[$matchField])->first();
            }

            try {
                if ($existing) {
                    if ($mode === 'create') {
                        $summary['skipped']++;
                        continue;
                    }
                    $existing->fill($attributes)->save();
                    $summary['updated']++;
                } else {
                    if ($mode === 'update') {
                        // Sólo actualizar: sin coincidencia, se salta.
                        $summary['skipped']++;
                        continue;
                    }
                    $model::create(array_merge($resource->creationDefaults(), $attributes));
                    $summary['created']++;
                }
            } catch (\Throwable $e) {
                $summary['errors'][] = ['row' => $rowNumber, 'message' => 'Error al guardar: ' . $e->getMessage()];
                $summary['skipped']++;
            }
        }

        fclose($handle);

        return $summary;
    }

    /**
     * Castea y valida un valor según la definición del campo.
     *
     * @param  array<string, mixed>  $def
     * @return array{value: mixed, error: ?string}
     */
    protected function castValue(string $raw, array $def): array
    {
        $type = $def['type'] ?? 'string';

        switch ($type) {
            case 'number':
                $normalized = str_replace([' ', ','], ['', '.'], $raw);
                if (! is_numeric($normalized)) {
                    return ['value' => null, 'error' => "«{$def['label']}» debe ser un número (recibido: «{$raw}»)."];
                }
                return ['value' => (float) $normalized, 'error' => null];

            case 'integer':
                $normalized = str_replace([' ', ','], '', $raw);
                if (! is_numeric($normalized)) {
                    return ['value' => null, 'error' => "«{$def['label']}» debe ser un entero (recibido: «{$raw}»)."];
                }
                return ['value' => (int) $normalized, 'error' => null];

            case 'boolean':
                return ['value' => $this->toBool($raw), 'error' => null];

            case 'enum':
                $allowed = $def['enum'] ?? [];
                $upper = strtoupper($raw);
                if (! in_array($upper, array_map('strtoupper', $allowed), true)) {
                    return ['value' => null, 'error' => "«{$def['label']}» inválido: «{$raw}». Valores permitidos: " . implode(', ', $allowed) . '.'];
                }
                return ['value' => $upper, 'error' => null];

            default: // string
                return ['value' => $raw, 'error' => null];
        }
    }

    protected function toBool(string $raw): bool
    {
        return in_array(strtolower(trim($raw)), ['1', 'true', 'si', 'sí', 'yes', 'y', 'x', 'verdadero'], true);
    }

    /**
     * Abre el archivo y determina el delimitador por referencia.
     *
     * @param  string  $delimiter  (out)
     * @return resource
     */
    protected function open(string $path, &$delimiter = null)
    {
        $delimiter = $this->detectDelimiter($path);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            abort(500, 'No se pudo abrir el archivo CSV.');
        }

        return $handle;
    }

    /** Detecta el delimitador comparando la cantidad de columnas en la primera línea. */
    protected function detectDelimiter(string $path): string
    {
        $handle = fopen($path, 'r');
        $firstLine = fgets($handle) ?: '';
        fclose($handle);

        $firstLine = $this->clean($firstLine);

        $candidates = [',' => 0, ';' => 0, "\t" => 0, '|' => 0];
        foreach ($candidates as $delim => $_) {
            $candidates[$delim] = substr_count($firstLine, $delim);
        }
        arsort($candidates);

        $best = array_key_first($candidates);

        return $candidates[$best] > 0 ? $best : ',';
    }

    /** Limpia BOM y normaliza a UTF-8. */
    protected function clean(string $value): string
    {
        // Quitar BOM UTF-8
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        }

        return trim($value);
    }

    /** @param array<int, mixed> $row */
    protected function isEmptyRow(array $row): bool
    {
        return count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0;
    }
}
