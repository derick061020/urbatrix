<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Google Calendar — Refresh Token</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 760px; margin: 40px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 22px; }
        .ok { color: #0a7a2f; }
        .err { color: #b00020; }
        code, pre {
            background: #f4f4f5; border: 1px solid #e4e4e7; border-radius: 6px;
            padding: 12px; display: block; white-space: pre-wrap; word-break: break-all;
            font-size: 13px;
        }
        .token { background: #eef9f1; border-color: #bfe3c8; font-weight: 600; }
        ol { line-height: 1.7; }
    </style>
</head>
<body>
    @if($ok)
        <h1 class="ok">✓ Listo. Tu refresh token de Google Calendar:</h1>
        <code class="token">{{ $token }}</code>
        <h3>Pasos finales:</h3>
        <ol>
            <li>Copiá ese valor.</li>
            <li>Pegalo en <code>.env</code> reemplazando <code>GOOGLE_CALENDAR_REFRESH_TOKEN=…</code></li>
            <li>Corré en la terminal:<br><code>php artisan cache:forget google_calendar_access_token &amp;&amp; php artisan config:clear</code></li>
            <li>Probá agendar una videollamada de nuevo.</li>
        </ol>
        <details>
            <summary>Respuesta completa de Google</summary>
            <pre>{{ $body }}</pre>
        </details>
    @else
        <h1 class="err">✗ No se pudo obtener el refresh token.</h1>
        @if($token === null && !empty($body))
            <p>Google respondió, pero sin <code>refresh_token</code>. Esto pasa cuando Google ya te concedió el consentimiento antes y reusó la sesión. Solución: ir a
                <a href="https://myaccount.google.com/permissions" target="_blank">myaccount.google.com/permissions</a>,
                eliminar el acceso de esta app y reintentar.
            </p>
        @endif
        <pre>{{ $body }}</pre>
    @endif
</body>
</html>
