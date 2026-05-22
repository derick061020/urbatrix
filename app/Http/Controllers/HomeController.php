<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Display the home page with units data.
     */
    public function index()
    {
        // Auto-release any 48h holds that expired
        Unit::releaseExpiredHolds();

        // Only units explicitly toggled public are shown on home
        $units = Unit::with(['images' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->where('public', true)
            ->orderByRaw('display_on_home_page DESC')
            ->orderByRaw("CASE WHEN status IN ('sold', 'pending', 'reserved', 'SOLD', 'PENDING', 'RESERVED') THEN 0 ELSE 1 END")
            ->orderBy('custom_id')
            ->orderBy('id')
            ->get();

        // Calculate real units sold count (only sold status)
        $soldCount = Unit::where('public', true)
            ->where(function($query) {
                $query->where('status', 'sold')
                      ->orWhere('status', 'SOLD');
            })
            ->count();

        $totalUnits = Unit::where('public', true)->count();

        return view('home', compact('units', 'soldCount', 'totalUnits'));
    }

    /**
     * Get unit details for AJAX requests
     */
    public function getUnitDetails($unitId)
    {
        $unit = Unit::with(['images' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->where('public', true)
            ->findOrFail($unitId);

        return response()->json($unit);
    }

    /**
     * Log a view of the unit (called when the info modal opens).
     * Deduplicates within a 15-minute window per session/user to avoid spam.
     */
    public function recordView(Request $request, $unitId)
    {
        $unit = Unit::where('public', true)->findOrFail($unitId);

        $userId    = Auth::id();
        $sessionId = $request->session()->getId();

        $recent = UnitView::where('unit_id', $unit->id)
            ->where(function ($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('session_id', $sessionId);
                }
            })
            ->where('viewed_at', '>=', now()->subMinutes(15))
            ->exists();

        if (! $recent) {
            UnitView::create([
                'unit_id'    => $unit->id,
                'user_id'    => $userId,
                'session_id' => $sessionId,
                'ip'         => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'viewed_at'  => now(),
            ]);

            $unit->forceFill([
                'views_today' => DB::raw('COALESCE(views_today, 0) + 1'),
                'views_total' => DB::raw('COALESCE(views_total, 0) + 1'),
            ])->save();
        }

        return response()->json([
            'views_today' => (int) $unit->fresh()->views_today,
            'views_total' => (int) $unit->fresh()->views_total,
        ]);
    }

    /**
     * Filter units based on criteria
     */
    public function filterUnits(Request $request)
    {
        Unit::releaseExpiredHolds();

        $filters = $request->all();

        $query = Unit::with(['images' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->where('public', true);

        // Unit number filter
        if (!empty($filters['unitNumber'])) {
            $query->where(function($q) use ($filters) {
                $q->where('custom_id', 'like', '%' . $filters['unitNumber'] . '%')
                  ->orWhere('name', 'like', '%' . $filters['unitNumber'] . '%');
            });
        }

        // Price filter
        if (!empty($filters['minPrice'])) {
            $query->where('price', '>=', $filters['minPrice']);
        }
        if (!empty($filters['maxPrice'])) {
            $query->where('price', '<=', $filters['maxPrice']);
        }

        // Unit type filter (based on bedrooms)
        if (!empty($filters['types']) && is_array($filters['types'])) {
            $query->where(function($q) use ($filters) {
                foreach ($filters['types'] as $type) {
                    if ($type === 'Studio') {
                        $q->orWhere('bedrooms', 0);
                    } elseif ($type === '1 Bed') {
                        $q->orWhere('bedrooms', 1);
                    } elseif ($type === '2 Bed') {
                        $q->orWhere('bedrooms', 2);
                    } elseif ($type === '3 Bed') {
                        $q->orWhere('bedrooms', 3);
                    } elseif ($type === 'Penthouse') {
                        $q->orWhere('type', 'Penthouse');
                    }
                }
            });
        }

        // Direction filter
        if (!empty($filters['directions']) && is_array($filters['directions'])) {
            $query->whereIn('direction', $filters['directions']);
        }

        // Outlook filter
        if (!empty($filters['outlooks']) && is_array($filters['outlooks'])) {
            $query->whereIn('outlook', $filters['outlooks']);
        }

        // Floor filter
        if (!empty($filters['floors']) && is_array($filters['floors'])) {
            $query->where(function($q) use ($filters) {
                foreach ($filters['floors'] as $floor) {
                    if ($floor === 'Ground') {
                        $q->orWhere('floor', 'Ground')
                          ->orWhereNull('floor');
                    } else {
                        $q->orWhere('floor', $floor);
                    }
                }
            });
        }

        // Apply sorting
        $sortField = $filters['sort'] ?? 'custom_id';
        switch ($sortField) {
            case 'price-asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price-desc':
                $query->orderBy('price', 'desc');
                break;
            case 'size-asc':
                $query->orderBy('total_area', 'asc');
                break;
            case 'size-desc':
                $query->orderBy('total_area', 'desc');
                break;
            case 'bedrooms-asc':
                $query->orderBy('bedrooms', 'asc');
                break;
            case 'bedrooms-desc':
                $query->orderBy('bedrooms', 'desc');
                break;
            case 'custom_id':
            default:
                $query->orderByRaw('display_on_home_page DESC')
                      ->orderBy('custom_id')
                      ->orderBy('id');
                break;
        }

        $units = $query->get();
        $total = $units->count();

        return response()->json([
            'units' => $units,
            'total' => $total
        ]);
    }
}
