<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleCalendarService
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const EVENTS_URL = 'https://www.googleapis.com/calendar/v3/calendars/%s/events';

    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.calendar_refresh_token'));
    }

    public function createMeetEvent(
        string $summary,
        string $description,
        Carbon $startsAt,
        int $durationMinutes,
        array $attendeeEmails,
        ?string $organizerEmail = null
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Google Calendar no está configurado en .env');
        }

        $calendarId = config('services.google.calendar_id', 'primary');
        $timezone   = config('services.google.calendar_timezone', config('app.timezone', 'UTC'));
        $endsAt     = $startsAt->copy()->addMinutes($durationMinutes);

        $attendees = collect($attendeeEmails)
            ->filter()
            ->unique()
            ->map(fn ($email) => ['email' => $email])
            ->values()
            ->all();

        if ($organizerEmail) {
            $attendees[] = ['email' => $organizerEmail, 'organizer' => true];
        }

        $payload = [
            'summary'     => $summary,
            'description' => $description,
            'start'       => [
                'dateTime' => $startsAt->copy()->setTimezone($timezone)->toRfc3339String(),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $endsAt->copy()->setTimezone($timezone)->toRfc3339String(),
                'timeZone' => $timezone,
            ],
            'attendees'      => $attendees,
            'conferenceData' => [
                'createRequest' => [
                    'requestId'             => (string) Str::uuid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides'  => [
                    ['method' => 'email', 'minutes' => 24 * 60],
                    ['method' => 'popup', 'minutes' => 30],
                ],
            ],
        ];

        $url = sprintf(self::EVENTS_URL, urlencode($calendarId));

        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->post($url . '?conferenceDataVersion=1&sendUpdates=all', $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                'Google Calendar API error: ' . $response->status() . ' ' . $response->body()
            );
        }

        $body = $response->json();

        return [
            'event_id'  => $body['id'] ?? null,
            'meet_link' => $body['hangoutLink']
                ?? data_get($body, 'conferenceData.entryPoints.0.uri'),
            'html_link' => $body['htmlLink'] ?? null,
        ];
    }

    private function getAccessToken(): string
    {
        $cacheKey = 'google_calendar_access_token';

        return Cache::remember($cacheKey, 3300, function () {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id'     => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'refresh_token' => config('services.google.calendar_refresh_token'),
                'grant_type'    => 'refresh_token',
            ]);

            if ($response->failed()) {
                throw new RuntimeException(
                    'No se pudo refrescar el token de Google: ' . $response->body()
                );
            }

            $token = $response->json('access_token');
            if (! $token) {
                throw new RuntimeException('Respuesta inválida de Google al refrescar token.');
            }

            return $token;
        });
    }
}
