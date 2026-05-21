<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SignNowDiagnose extends Command
{
    protected $signature = 'signnow:diagnose';
    protected $description = 'Probes the SignNow API key against both prod and eval endpoints and reports which one accepts it.';

    public function handle(): int
    {
        $key = (string) config('signnow.api_key');
        if (! $key) {
            $this->error('SIGNNOW_API_KEY no está configurado en .env');
            return self::FAILURE;
        }

        $this->line('API key: '.substr($key, 0, 8).'…');
        $this->line('Probing both endpoints…');
        $this->newLine();

        $hosts = ['https://api.signnow.com' => 'PROD', 'https://api-eval.signnow.com' => 'EVAL'];
        $matched = null;
        foreach ($hosts as $url => $label) {
            $resp = Http::withToken($key)->get($url.'/user');
            $status = $resp->status();
            if ($resp->successful()) {
                $email = $resp['primary_email'] ?? $resp['email'] ?? 'unknown';
                $this->info(sprintf('  ✓ %s (%s) → %d — account email: %s', $label, $url, $status, $email));
                $matched = $url;
            } else {
                $body = trim(substr($resp->body(), 0, 200));
                $this->warn(sprintf('  ✗ %s (%s) → %d — %s', $label, $url, $status, $body));
            }
        }
        $this->newLine();

        if (! $matched) {
            $this->error('Ningún entorno aceptó el API key.');
            $this->line('Verificá en signnow.com → API → que la key esté activa.');
            $this->line('Si la copiaste hace tiempo, podría haber expirado — regenerala.');
            return self::FAILURE;
        }

        $this->info('Configurá esto en .env para evitar el doble check:');
        $this->line('  SIGNNOW_BASE_URL='.$matched);
        return self::SUCCESS;
    }
}
