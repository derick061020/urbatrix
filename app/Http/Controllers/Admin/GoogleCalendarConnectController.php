<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleCalendarConnectController extends Controller
{
    private const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const SCOPE     = 'https://www.googleapis.com/auth/calendar.events';

    public function connect(Request $request)
    {
        $state = Str::random(40);
        $request->session()->put('gcal_oauth_state', $state);

        $params = http_build_query([
            'client_id'              => config('services.google.client_id'),
            'redirect_uri'           => $this->redirectUri(),
            'response_type'          => 'code',
            'scope'                  => self::SCOPE,
            'access_type'            => 'offline',
            'prompt'                 => 'consent',
            'include_granted_scopes' => 'true',
            'state'                  => $state,
        ]);

        return redirect(self::AUTH_URL . '?' . $params);
    }

    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return response('Google devolvió error: ' . $request->input('error'), 400);
        }

        if ($request->input('state') !== $request->session()->pull('gcal_oauth_state')) {
            return response('State inválido. Volvé a /admin/google-calendar/connect', 400);
        }

        $code = $request->input('code');
        if (! $code) {
            return response('Falta ?code en el callback.', 400);
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'code'          => $code,
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri'  => $this->redirectUri(),
            'grant_type'    => 'authorization_code',
        ]);

        if ($response->failed()) {
            return response()
                ->view('admin.google_calendar_result', [
                    'ok'    => false,
                    'body'  => $response->body(),
                    'token' => null,
                ], 502);
        }

        $body = $response->json();
        $refresh = $body['refresh_token'] ?? null;

        return view('admin.google_calendar_result', [
            'ok'    => $refresh !== null,
            'body'  => json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'token' => $refresh,
        ]);
    }

    private function redirectUri(): string
    {
        return rtrim(config('app.url'), '/') . '/admin/google-calendar/callback';
    }
}
