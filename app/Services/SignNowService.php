<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * SignNow integration service.
 *
 * When SIGNNOW_ENABLED is true and credentials are filled in `config/signnow.php`,
 * documents are uploaded to SignNow and the signer is redirected to SignNow's signing UI.
 *
 * When SignNow is not configured (default for dev), the service falls back to the
 * existing local-signing flow (Document::markAsSigned) so the workflow still completes
 * end-to-end.
 */
class SignNowService
{
    public static function isConfigured(): bool
    {
        if (! config('signnow.enabled')) return false;
        if (config('signnow.mode') === 'bearer') {
            return (bool) config('signnow.api_key');
        }
        return (bool) config('signnow.client_id')
            && (bool) config('signnow.client_secret')
            && (bool) config('signnow.username')
            && (bool) config('signnow.password');
    }

    /**
     * Resolves the SignNow base URL that actually accepts the configured token.
     * Tries the configured base URL first; on invalid_token (1537) it transparently
     * tries the eval/prod counterpart. Result is cached.
     *
     * Returns the working base URL (no trailing slash).
     */
    public static function workingBaseUrl(): string
    {
        $cached = Cache::get('signnow.working_base_url');
        if ($cached) return $cached;

        $primary = self::baseUrl();
        $alt = str_contains($primary, 'api-eval')
            ? 'https://api.signnow.com'
            : 'https://api-eval.signnow.com';

        foreach ([$primary, $alt] as $candidate) {
            $resp = Http::withToken((string) config('signnow.api_key'))->get($candidate.'/user');
            if ($resp->successful()) {
                Cache::put('signnow.working_base_url', $candidate, now()->addDay());
                return $candidate;
            }
            Log::info('SignNow base URL probe failed', [
                'url' => $candidate.'/user', 'status' => $resp->status(), 'body' => substr($resp->body(), 0, 200),
            ]);
        }
        // Both failed — surface a precise error
        throw new \RuntimeException(
            'SignNow no acepta el API key. Probá: (1) verificar que el key sea correcto en signnow.com, '
            .'(2) confirmar si tu cuenta es eval o producción y ajustar SIGNNOW_BASE_URL, '
            .'(3) regenerar el token desde SignNow.'
        );
    }

    /**
     * Send a document to SignNow and return a signing URL the client should open.
     * Returns ['signing_url' => string, 'signnow_document_id' => string]
     * or throws \RuntimeException on failure.
     */
    public static function sendForSignature(Document $document, string $signerEmail, string $signerName): array
    {
        if (! self::isConfigured()) {
            throw new \RuntimeException('SignNow no está configurado en este entorno.');
        }

        $token = self::accessToken();
        $base  = self::workingBaseUrl();
        $absolutePath = self::resolveLocalPath($document);
        if (! $absolutePath) {
            throw new \RuntimeException('No se encontró el archivo local del documento para enviarlo a firmar.');
        }

        // 1. Upload the file to SignNow (no field extraction — we'll use a freeform invite
        //    where the signer places signatures themselves, so the doc needs no preset fields)
        $uploaded = Http::withToken($token)
            ->attach('file', file_get_contents($absolutePath), basename($absolutePath))
            ->post($base.'/document');

        if (! $uploaded->successful() || ! ($uploaded['id'] ?? null)) {
            Log::warning('SignNow upload failed', ['body' => $uploaded->body()]);
            throw new \RuntimeException('Falló la subida del documento a SignNow.');
        }

        $signnowDocId = (string) $uploaded['id'];

        // 2. Send an email invite to the signer. SignNow handles the rest —
        //    the recipient receives an email with a secure signing link that
        //    works without login. We do NOT mark the doc as signed yet — that
        //    happens only when SignNow confirms the signature via webhook or
        //    when the admin presses "Sincronizar firma".
        $fromEmail = (string) (config('signnow.username') ?: self::accountEmail($token));
        $invite = Http::withToken($token)->post(
            $base.'/document/'.$signnowDocId.'/invite',
            [
                'to'   => $signerEmail,
                'from' => $fromEmail,
            ]
        );

        if (! $invite->successful()) {
            Log::warning('SignNow invite failed', ['body' => $invite->body()]);
            throw new \RuntimeException('Falló el envío del correo de firma: '.$invite->body());
        }

        // Persist the SignNow document id in metadata so we can retrieve status later
        $meta = $document->metadata ?? [];
        $meta['signnow'] = [
            'document_id'  => $signnowDocId,
            'sent_at'      => now()->toIso8601String(),
            'signer_email' => $signerEmail,
            'invite_only'  => true,   // we only sent the email; no direct redirect URL
        ];
        $document->update(['metadata' => $meta]);

        return [
            'email_sent'          => true,
            'signer_email'        => $signerEmail,
            'signnow_document_id' => $signnowDocId,
        ];
    }

    /**
     * Download the latest version of a document from SignNow (typically the signed one).
     * Returns the absolute path where the signed file was stored, or null on failure.
     */
    public static function downloadSignedFile(Document $document): ?string
    {
        $signnowDocId = (string) data_get($document->metadata, 'signnow.document_id');
        if (! $signnowDocId || ! self::isConfigured()) return null;

        $token = self::accessToken();
        $resp = Http::withToken($token)
            ->get(self::workingBaseUrl().'/document/'.$signnowDocId.'/download', [
                'type' => 'collapsed',
            ]);

        if (! $resp->successful()) {
            Log::warning('SignNow download failed', ['status' => $resp->status()]);
            return null;
        }

        $relPath = 'signed/'.$signnowDocId.'.pdf';
        Storage::disk('public')->put($relPath, $resp->body());

        $document->update([
            'file_path' => $relPath,
            'filename'  => 'firmado_'.($document->filename ?: $signnowDocId.'.pdf'),
            'status'    => 'signed',
            'signed_at' => $document->signed_at ?? now(),
        ]);

        return storage_path('app/public/'.$relPath);
    }

    /**
     * Return a Bearer token suitable for the SignNow API.
     *
     * - "bearer" mode (default): the configured api_key IS the access token. No round-trip.
     * - "oauth_password" mode: exchange client credentials + username/password for a token,
     *   cached for 30 minutes.
     */
    private static function accessToken(): string
    {
        if (config('signnow.mode') === 'bearer') {
            $key = (string) config('signnow.api_key');
            if ($key === '') {
                throw new \RuntimeException('SIGNNOW_API_KEY no está configurado.');
            }
            return $key;
        }

        // For oauth_password mode, clear cache to force fresh token on each call
        // This prevents invalid_token errors from cached expired tokens
        Cache::forget('signnow.access_token');
        
        $clientId     = (string) config('signnow.client_id');
        $clientSecret = (string) config('signnow.client_secret');
        $resp = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Basic '.base64_encode($clientId.':'.$clientSecret),
            ])
            ->post(self::workingBaseUrl().'/oauth2/token', [
                'grant_type' => 'password',
                'username'   => (string) config('signnow.username'),
                'password'   => (string) config('signnow.password'),
                'scope'      => '*',
            ]);
        if (! $resp->successful() || ! ($resp['access_token'] ?? null)) {
            Log::error('SignNow token error', ['body' => $resp->body()]);
            throw new \RuntimeException('No se pudo autenticar contra SignNow.');
        }
        return (string) $resp['access_token'];
    }

    private static function baseUrl(): string
    {
        return rtrim((string) config('signnow.base_url', 'https://api.signnow.com'), '/');
    }

    /**
     * Resolve the email of the SignNow account that owns the current token.
     * SignNow requires this as the "from" field on any invite. Cached for an hour.
     */
    private static function accountEmail(string $token): string
    {
        return Cache::remember('signnow.account_email', now()->addHour(), function () use ($token) {
            $resp = Http::withToken($token)->get(self::workingBaseUrl().'/user');
            if (! $resp->successful()) {
                Log::warning('SignNow /user lookup failed', ['status' => $resp->status(), 'body' => $resp->body()]);
                throw new \RuntimeException('No se pudo obtener el email de la cuenta SignNow (revisá el API key).');
            }
            // SignNow returns email in different shapes depending on plan;
            // try the common ones in order of preference.
            $email = $resp['primary_email']
                   ?? $resp['email']
                   ?? data_get($resp->json(), 'emails.0');
            if (! $email) {
                Log::error('SignNow /user response had no email', ['body' => $resp->body()]);
                throw new \RuntimeException('La cuenta SignNow no tiene un email asociado.');
            }
            return (string) $email;
        });
    }

    private static function resolveLocalPath(Document $document): ?string
    {
        if (! $document->file_path) return null;
        $candidates = [
            storage_path('app/public/'.ltrim($document->file_path, '/')),
            public_path(ltrim($document->file_path, '/')),
        ];
        foreach ($candidates as $c) if (is_file($c)) return $c;
        return null;
    }
}
