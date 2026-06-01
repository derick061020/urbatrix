<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ActiveUserController extends Controller
{
    /**
     * Get the count of active users.
     */
    public function getActiveUsersCount()
    {
        $activeUsers = Cache::remember('active_users_count', 30, function () {
            $activeGuests = Cache::get('active_guests', []);
            
            // Remove sessions older than 5 minutes
            $activeGuests = array_filter($activeGuests, function($timestamp) {
                return $timestamp > (now()->subMinutes(5)->timestamp);
            });
            
            Cache::put('active_guests', $activeGuests, 300);
            
            return count($activeGuests);
        });

        return response()->json(['count' => $activeUsers]);
    }

    /**
     * Update user's last seen timestamp.
     */
    public function updateLastSeen(Request $request)
    {
        $sessionId = session()->getId();
        $activeGuests = Cache::get('active_guests', []);
        
        // Update or add this session
        $activeGuests[$sessionId] = now()->timestamp;
        
        // Remove sessions older than 5 minutes
        $activeGuests = array_filter($activeGuests, function($timestamp) {
            return $timestamp > (now()->subMinutes(5)->timestamp);
        });
        
        Cache::put('active_guests', $activeGuests, 300);

        $totalActive = count($activeGuests);

        // Mantén viva la sesión de actividad del usuario logueado y su last_seen
        if (auth()->check()) {
            auth()->user()->forceFill(['last_seen' => now()])->saveQuietly();
            \App\Support\ActivityLogger::touchSession($request->session()->get('activity_login_id'));
        }

        return response()->json(['success' => true, 'count' => $totalActive]);
    }
}
