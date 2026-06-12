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
    /**
     * How many unit cards/rows are rendered server-side on first paint. The
     * rest are streamed in on scroll via {@see homeUnits()} to keep the
     * initial DOM light (the public catalog can hold hundreds of units).
     */
    public const HOME_PAGE_SIZE = 25;

    /**
     * Base query for the public home catalog — single source of truth for the
     * ordering so the server-rendered first page and the AJAX pages line up.
     */
    private function publicUnitsQuery()
    {
        return Unit::with(['images' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->where('public', true)
            ->orderByRaw('display_on_home_page DESC')
            ->orderByRaw("CASE WHEN status IN ('sold', 'pending', 'reserved', 'SOLD', 'PENDING', 'RESERVED') THEN 1 ELSE 0 END")
            ->orderBy('custom_id')
            ->orderBy('id');
    }

    public function index()
    {
        // Auto-release any 48h holds that expired
        Unit::releaseExpiredHolds();

        // Full ordered set — still needed for the plan view (all floor markers)
        // and the list status-tab badge counts.
        $units = $this->publicUnitsQuery()->get();

        // Only the first page is rendered as heavy grid cards / list rows; the
        // remainder loads on scroll (see resources/views/home.blade.php).
        $gridUnits = $units->take(self::HOME_PAGE_SIZE);

        // Calculate real units sold count (only sold status)
        $soldCount = Unit::where('public', true)
            ->where(function($query) {
                $query->where('status', 'sold')
                      ->orWhere('status', 'SOLD');
            })
            ->count();

        $totalUnits = Unit::where('public', true)->count();

        // Wishlisted unit IDs for the current user (empty array for guests)
        $wishlistIds = Auth::check()
            ? \App\Models\Wishlist::where('user_id', Auth::id())->pluck('unit_id')->all()
            : [];

        return view('home', compact('units', 'gridUnits', 'soldCount', 'totalUnits', 'wishlistIds'));
    }

    /**
     * Stream the next page (or all remaining) public units as rendered HTML
     * for the grid cards and list rows. Keeps the initial home DOM small while
     * preserving the exact Blade markup (no client-side template duplication).
     */
    public function homeUnits(Request $request)
    {
        $offset = max(0, (int) $request->query('offset', 0));
        $all    = $request->boolean('all');

        $total = $this->publicUnitsQuery()->count();

        $units = $this->publicUnitsQuery()
            ->skip($offset)
            ->take($all ? PHP_INT_MAX : self::HOME_PAGE_SIZE)
            ->get();

        $outlookLabels = \App\Support\UnitOptions::map('outlooks');
        $wishlistIds = Auth::check()
            ? \App\Models\Wishlist::where('user_id', Auth::id())->pluck('unit_id')->all()
            : [];

        $cards = '';
        $rows  = '';
        foreach ($units as $unit) {
            $cards .= view('partials.home-unit-card', compact('unit', 'outlookLabels', 'wishlistIds'))->render();
            $rows  .= view('partials.home-unit-row',  compact('unit', 'outlookLabels'))->render();
        }

        return response()->json([
            'cards'   => $cards,
            'rows'    => $rows,
            'offset'  => $offset + $units->count(),
            'hasMore' => ($offset + $units->count()) < $total,
            'total'   => $total,
        ]);
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
     * Render a printable property PDF for the given unit. Opens in a new tab
     * and auto-triggers the browser print dialog so the user can "Save as PDF".
     */
    public function propertyPdf($unitId)
    {
        $unit = Unit::with([
                'images' => function ($q) { $q->orderBy('sort_order'); },
                'project',
            ])
            ->where('public', true)
            ->findOrFail($unitId);

        return view('property-pdf', compact('unit'));
    }

    /**
     * Toggle the current user's wishlist for a unit.
     * Returns the new state + total count for the user.
     */
    public function toggleWishlist(Request $request, $unitId)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Necesitás iniciar sesión.'], 401);
        }

        $unit = Unit::where('public', true)->findOrFail($unitId);
        $row  = \App\Models\Wishlist::where('user_id', Auth::id())->where('unit_id', $unit->id)->first();

        if ($row) {
            $row->delete();
            Unit::where('id', $unit->id)->where('shortlisted_count', '>', 0)->decrement('shortlisted_count');
            $state = false;
        } else {
            \App\Models\Wishlist::create(['user_id' => Auth::id(), 'unit_id' => $unit->id]);
            Unit::where('id', $unit->id)->increment('shortlisted_count');
            $state = true;
        }

        return response()->json([
            'success'     => true,
            'wishlisted'  => $state,
            'total'       => \App\Models\Wishlist::where('user_id', Auth::id())->count(),
            'unit_count'  => (int) Unit::where('id', $unit->id)->value('shortlisted_count'),
        ]);
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

            if ($userId) {
                $unitLabel = $unit->custom_id ?? $unit->name ?? ('Unidad '.$unit->id);
                \App\Support\ActivityLogger::log($userId, 'property_view', 'Visitó '.$unitLabel, $unit);
            }
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
