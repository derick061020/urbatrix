<?php

/*
|--------------------------------------------------------------------------
| SignNow integration
|--------------------------------------------------------------------------
|
| Two auth modes are supported (set via SIGNNOW_AUTH_MODE):
|
|   1. "bearer"          — single API key used directly as a Bearer token.
|                          Easiest setup; you only need SIGNNOW_API_KEY.
|
|   2. "oauth_password"  — full OAuth password-grant flow. Requires
|                          CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD.
|
| Minimal .env for "bearer" mode:
|     SIGNNOW_ENABLED=true
|     SIGNNOW_API_KEY=9b560152bb5feabb9d94ba308413d5848c1362453b211665be6c47246ab172d5
|
*/

return [
    'enabled'  => (bool) env('SIGNNOW_ENABLED', false),
    'base_url' => env('SIGNNOW_BASE_URL', 'https://api.signnow.com'),
    'mode'     => env('SIGNNOW_AUTH_MODE', 'bearer'),

    // Single-key mode
    'api_key'  => env('SIGNNOW_API_KEY'),

    // OAuth password-grant mode (only used when SIGNNOW_AUTH_MODE=oauth_password)
    'client_id'     => env('SIGNNOW_CLIENT_ID'),
    'client_secret' => env('SIGNNOW_CLIENT_SECRET'),
    'username'      => env('SIGNNOW_USERNAME'),
    'password'      => env('SIGNNOW_PASSWORD'),

    // Webhook signature (optional — set if you configure one in SignNow)
    'webhook_secret' => env('SIGNNOW_WEBHOOK_SECRET'),

    // Where SignNow redirects the signer after they finish
    'redirect_url'   => env('SIGNNOW_REDIRECT_URL'),
];
