<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Importa contactos exportados de Bitrix24 (CSV "BBDD_BITRIX_…") como usuarios
 * de la app, con rol `user` (clientes) y SIN contraseña.
 *
 * El CSV trae ~95 columnas; sólo usamos nombre, apellido, e-mail y teléfono.
 * El e-mail y el teléfono se detectan por contenido (no por índice fijo) para
 * tolerar variaciones del export. Deduplicación: por e-mail cuando existe (si ya
 * hay un usuario con ese correo NO se modifica, para no pisar admins/brokers/
 * clientes reales); las filas sin e-mail también se guardan, deduplicando por
 * teléfono. Las filas sin e-mail NI teléfono se crean igual (no se deduplican).
 *
 * Uso:
 *   php artisan clients:import                         # database/data/clientes*.csv
 *   php artisan clients:import ruta/al/archivo.csv
 *   php artisan clients:import archivo.csv --role=user --force
 */
class ImportClientsFromCsv extends Command
{
    protected $signature = 'clients:import
                            {files?* : Rutas a los CSV (por defecto database/data/clientes*.csv)}
                            {--role=user : Rol a asignar a los usuarios importados}
                            {--force : No pedir confirmación}';

    protected $description = 'Importa contactos del CRM (Bitrix CSV) como usuarios sin contraseña';

    public function handle(): int
    {
        $files = $this->argument('files');
        if (empty($files)) {
            $files = glob(database_path('data/clientes*.csv'));
            sort($files);
        }
        if (empty($files)) {
            $this->error('No se encontraron CSV. Pasá la ruta o colocá el archivo en database/data/clientes.csv');
            return self::FAILURE;
        }

        $role = $this->option('role');

        if (! $this->option('force')
            && ! $this->confirm("Se importarán los contactos de:\n  - " . implode("\n  - ", $files) . "\ncomo usuarios con rol «{$role}» y sin contraseña. ¿Continuar?")) {
            $this->info('Cancelado.');
            return self::SUCCESS;
        }

        $created = 0;
        $existing = 0;
        $rows = 0;
        $seenEmail = [];
        $seenPhone = [];

        DB::transaction(function () use ($files, $role, &$created, &$existing, &$rows, &$seenEmail, &$seenPhone) {
            foreach ($files as $file) {
                if (! is_readable($file)) {
                    $this->error("No se puede leer: {$file}");
                    continue;
                }
                $this->line("→ {$file}");
                $handle = fopen($file, 'r');
                $first = true;

                while (($row = fgetcsv($handle)) !== false) {
                    // Saltar la fila de cabecera.
                    if ($first) {
                        $first = false;
                        $header = $this->fix($row[0] ?? '') . ' ' . $this->fix($row[3] ?? '');
                        if (stripos($header, 'ID') !== false || stripos($header, 'Nombre') !== false) {
                            continue;
                        }
                    }

                    $rows++;
                    $email = $this->extractEmail($row);
                    $phone = $this->extractPhone($row);

                    if ($email !== null) {
                        // Dedupe por e-mail (dentro del CSV y contra la BD).
                        if (isset($seenEmail[$email]) || User::where('email', $email)->exists()) {
                            $existing++;
                            continue;
                        }
                        $seenEmail[$email] = true;
                    } elseif ($phone !== null) {
                        // Sin e-mail: deduplicamos por teléfono.
                        if (isset($seenPhone[$phone]) || User::where('phone', $phone)->exists()) {
                            $existing++;
                            continue;
                        }
                        $seenPhone[$phone] = true;
                    }
                    // Sin e-mail ni teléfono: se crea igual (no se puede deduplicar).

                    $firstName = $this->fix($row[3] ?? '');
                    $lastName  = trim($this->fix($row[4] ?? '') . ' ' . $this->fix($row[5] ?? ''));
                    $name      = trim("{$firstName} {$lastName}");
                    if ($name === '') {
                        $name = $email !== null ? strtok($email, '@') : 'Sin nombre';
                    }

                    User::create([
                        'name'                => mb_substr($name, 0, 255),
                        'first_name'          => $firstName !== '' ? mb_substr($firstName, 0, 255) : null,
                        'last_name'           => $lastName !== '' ? mb_substr($lastName, 0, 255) : null,
                        'email'               => $email,
                        'phone'               => $phone,
                        'role'                => $role,
                        'password'            => null,
                        'verification_status' => 'approved',
                    ]);
                    $created++;
                }
                fclose($handle);
            }
        });

        $this->newLine();
        $this->info("Listo. Creados: {$created} · Duplicados/ya existían: {$existing}");
        $this->line("Filas de datos procesadas: {$rows}");

        return self::SUCCESS;
    }

    /** Primer e-mail válido de la fila (las celdas de correo pueden traer varios). */
    private function extractEmail(array $row): ?string
    {
        foreach ($row as $cell) {
            $cell = $this->fix($cell);
            if ($cell === '' || strpos($cell, '@') === false) {
                continue;
            }
            foreach (preg_split('/[,;\s]+/', $cell) as $candidate) {
                $candidate = strtolower(trim($candidate, " \t\n\r\0\x0B<>\"'"));
                if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                    return $candidate;
                }
            }
        }
        return null;
    }

    /** Primer teléfono "razonable" (>=7 dígitos) de las columnas de teléfono. */
    private function extractPhone(array $row): ?string
    {
        // Móvil, Tel. trabajo, Tel. casa, SMS, Otro número.
        foreach ([13, 12, 15, 17, 18] as $i) {
            $raw = $this->fix($row[$i] ?? '');
            if ($raw === '') {
                continue;
            }
            $first = trim((string) preg_split('/[,;]/', $raw)[0]);
            if (preg_match_all('/\d/', $first) >= 7) {
                return mb_substr($first, 0, 30);
            }
        }
        return null;
    }

    /**
     * Corrige el mojibake típico del export (UTF-8 leído como Latin-1):
     * "CumpleaÃ±os" → "Cumpleaños", "JosÃ©" → "José".
     */
    private function fix(?string $s): string
    {
        $s = trim((string) $s);
        if ($s !== '' && (str_contains($s, 'Ã') || str_contains($s, 'Â'))) {
            $converted = @mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
            if ($converted !== false && $converted !== '') {
                $s = $converted;
            }
        }
        return $s;
    }
}
