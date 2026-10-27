<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        // «Soy broker» en el popup de acceso llega con ?perfil=broker: si se
        // registra con Google, la cuenta nueva tiene que nacer como broker y no
        // como cliente (Miguel, 26 oct: «me registré como broker y no me lleva
        // al área de broker»).
        $perfil = strtolower((string) request()->query('perfil', ''));
        if (in_array($perfil, ['broker', 'agency'], true)) {
            session(['google_perfil' => $perfil]);
        } else {
            session()->forget('google_perfil');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Download and store avatar locally
            $avatarPath = $this->downloadAvatar($googleUser->getAvatar(), $googleUser->getId());

            // Find or create user
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                // If user exists but doesn't have google_id, update it
                if (!$user->google_id) {
                    $user->google_id = $googleUser->getId();
                    $user->avatar = $avatarPath;
                    $user->save();
                }
            } else {
                // Create new user
                $perfil = session()->pull('google_perfil');
                $attrs = [
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $avatarPath,
                    'password' => bcrypt(Str::random(32)), // Random password
                    'email_verified_at' => now(),
                    'role' => $perfil ? 'broker' : 'user',
                ];
                if ($perfil && \Illuminate\Support\Facades\Schema::hasColumn('users', 'verification_status')) {
                    $attrs['verification_status'] = 'pending';
                }
                $user = User::create($attrs);
                if ($perfil) {
                    \App\Models\BrokerProfile::create([
                        'user_id' => $user->id,
                        'kind'    => $perfil,
                        'data'    => ['nombre' => $user->name, 'email' => $user->email],
                    ]);
                }
            }

            // Update last_seen
            $user->last_seen = now();
            $user->save();

            Auth::login($user);

            return redirect()->intended($user->postAuthRedirectPath());

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect('/login')->with('error', __('Error al autenticar con Google. Por favor intenta nuevamente.'));
        }
    }

    /**
     * Download avatar from Google and store locally
     */
    private function downloadAvatar($avatarUrl, $googleId)
    {
        try {
            $contents = file_get_contents($avatarUrl);
            $fileName = 'avatar_' . $googleId . '_' . time() . '.jpg';
            $path = 'avatars/' . $fileName;
            Storage::disk('public')->put($path, $contents);
            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to download avatar: ' . $e->getMessage());
            return null;
        }
    }
}
