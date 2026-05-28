<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Unit;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class MeetingController extends Controller
{
    private const SLOT_OPTIONS = [
        '9:00 AM', '10:00 AM', '11:00 AM',
        '2:00 PM', '3:00 PM', '4:00 PM',
    ];

    public function __construct(private GoogleCalendarService $calendar) {}

    public function availability(Request $request)
    {
        $request->validate([
            'date'    => 'required|date_format:Y-m-d',
            'unit_id' => 'nullable',
        ]);

        $date = Carbon::parse($request->input('date'))->startOfDay();
        $unit = $this->resolveUnit($request->input('unit_id'));

        $taken = [];

        foreach (self::SLOT_OPTIONS as $slot) {
            $startsAt = $this->slotToDateTime($date, $slot);
            if (! $this->findAvailableAdvisor($unit, $startsAt)) {
                $taken[] = $slot;
            }
        }

        return response()->json([
            'date'  => $date->toDateString(),
            'taken' => $taken,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_id'        => 'nullable',
            'unit_label'     => 'nullable|string|max:120',
            'preferred_date' => 'required|date_format:Y-m-d',
            'preferred_time' => 'required|string|in:' . implode(',', self::SLOT_OPTIONS),
            'note'           => 'nullable|string|max:500',
        ]);

        $client = Auth::user();
        if (! $client) {
            return response()->json(['error' => 'Tenés que iniciar sesión para agendar.'], 401);
        }

        if (! $client->email) {
            return response()->json(['error' => 'Tu cuenta no tiene un email asociado.'], 422);
        }

        $unit      = $this->resolveUnit($request->input('unit_id'));
        $startsAt  = $this->slotToDateTime(
            Carbon::parse($request->input('preferred_date')),
            $request->input('preferred_time')
        );

        if ($startsAt->isPast()) {
            return response()->json(['error' => 'No podés agendar en una fecha pasada.'], 422);
        }

        $advisor = $this->findAvailableAdvisor($unit, $startsAt);
        if (! $advisor) {
            return response()->json([
                'error' => 'Ese horario ya no está disponible. Elegí otro horario o fecha.',
            ], 409);
        }

        if (! $this->calendar->isConfigured()) {
            return response()->json([
                'error' => 'La integración con Google Calendar no está configurada. Avisá al administrador.',
            ], 503);
        }

        $unitLabel = $request->input('unit_label')
            ?: ($unit ? ('Unit ' . ($unit->custom_id ?? $unit->id)) : 'Sin unidad');

        $summary = 'Videollamada con ' . ($client->name ?? $client->email) . ' · ' . $unitLabel;

        $descriptionLines = [
            'Cliente: ' . ($client->name ?? $client->email) . ' (' . $client->email . ')',
            'Asesor: ' . ($advisor->name ?? $advisor->email),
            'Propiedad: ' . $unitLabel,
        ];
        if ($request->filled('note')) {
            $descriptionLines[] = '';
            $descriptionLines[] = 'Nota del cliente:';
            $descriptionLines[] = $request->input('note');
        }
        $description = implode("\n", $descriptionLines);

        try {
            $event = $this->calendar->createMeetEvent(
                summary: $summary,
                description: $description,
                startsAt: $startsAt,
                durationMinutes: 45,
                attendeeEmails: array_filter([$client->email, $advisor->email]),
            );
        } catch (Throwable $e) {
            Log::error('Google Calendar createEvent failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'No pudimos crear la videollamada en Google. Probá de nuevo en unos minutos.',
            ], 502);
        }

        $meeting = Meeting::create([
            'user_id'          => $client->id,
            'advisor_id'       => $advisor->id,
            'unit_id'          => $unit?->id,
            'scheduled_at'     => $startsAt,
            'duration_minutes' => 45,
            'google_event_id'  => $event['event_id'] ?? null,
            'google_meet_link' => $event['meet_link'] ?? null,
            'status'           => 'confirmed',
            'notes'            => $request->input('note'),
        ]);

        return response()->json([
            'ok'      => true,
            'meeting' => [
                'id'           => $meeting->id,
                'scheduled_at' => $meeting->scheduled_at->toIso8601String(),
                'meet_link'    => $meeting->google_meet_link,
                'advisor'      => $advisor->name ?? $advisor->email,
            ],
        ]);
    }

    private function resolveUnit(string|int|null $unitInput): ?Unit
    {
        if (! $unitInput) return null;

        return Unit::where('id', $unitInput)
            ->orWhere('custom_id', $unitInput)
            ->first();
    }

    private function slotToDateTime(Carbon $date, string $slot): Carbon
    {
        $tz = config('app.timezone', 'UTC');
        return Carbon::parse($date->toDateString() . ' ' . $slot, $tz);
    }

    private function findAvailableAdvisor(?Unit $unit, Carbon $startsAt): ?User
    {
        $endsAt = $startsAt->copy()->addMinutes(45);

        $candidates = collect();

        if ($unit) {
            $candidates = $candidates->merge($unit->brokers()->get());
        }

        $admins = User::where('role', 'admin')->get();
        $candidates = $candidates->merge($admins)->unique('id');

        foreach ($candidates as $candidate) {
            if (! $this->hasConflict($candidate->id, $startsAt, $endsAt)) {
                return $candidate;
            }
        }

        return null;
    }

    private function hasConflict(int $advisorId, Carbon $startsAt, Carbon $endsAt): bool
    {
        $windowStart = $startsAt->copy()->subHours(4);
        $windowEnd   = $endsAt->copy()->addHours(1);

        return Meeting::where('advisor_id', $advisorId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->get(['scheduled_at', 'duration_minutes'])
            ->contains(function ($m) use ($startsAt, $endsAt) {
                $existingStart = Carbon::parse($m->scheduled_at);
                $existingEnd   = $existingStart->copy()->addMinutes((int) $m->duration_minutes);
                return $existingStart->lt($endsAt) && $existingEnd->gt($startsAt);
            });
    }
}
