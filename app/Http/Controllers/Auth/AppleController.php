<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AppleController extends Controller
{
    /**
     * Redirect the user to Apple's authentication page.
     */
    public function redirect()
    {
        $this->ensureClientSecret();

        return Socialite::driver('apple')->redirect();
    }

    /**
     * Handle the callback from Apple.
     *
     * Apple posts back to this URL (response_mode=form_post) when the
     * `name`/`email` scopes are requested, so this route accepts POST.
     */
    public function callback()
    {
        try {
            $this->ensureClientSecret();

            $appleUser = Socialite::driver('apple')->user();

            // Apple only sends the name on the *first* authorization. On later
            // logins getName() is null, so we fall back to the email handle.
            $name = $appleUser->getName()
                ?: ($appleUser->getEmail() ? Str::before($appleUser->getEmail(), '@') : 'Usuario');

            // Find or create the user. Match by apple_id first, then by email so
            // an existing Google/password account gets linked instead of duped.
            $user = User::where('apple_id', $appleUser->getId())
                ->orWhere('email', $appleUser->getEmail())
                ->first();

            if ($user) {
                if (! $user->apple_id) {
                    $user->apple_id = $appleUser->getId();
                    $user->save();
                }
            } else {
                $user = User::create([
                    'name'              => $name,
                    'email'             => $appleUser->getEmail(),
                    'apple_id'          => $appleUser->getId(),
                    'password'          => bcrypt(Str::random(32)),
                    'email_verified_at' => now(),
                    'role'              => 'user',
                ]);
            }

            $user->last_seen = now();
            $user->save();

            Auth::login($user);

            return redirect($user->postAuthRedirectPath());
        } catch (\Throwable $e) {
            Log::error('Apple OAuth Error: ' . $e->getMessage());

            return redirect('/login')->with('error', 'Error al autenticar con Apple. Por favor intenta nuevamente.');
        }
    }

    /**
     * Apple's "client secret" is a short-lived ES256 JWT signed with the .p8
     * key. We mint it on the fly so it never expires from the operator's POV.
     * If APPLE_CLIENT_SECRET is set explicitly, we leave it untouched.
     */
    private function ensureClientSecret(): void
    {
        if (config('services.apple.client_secret')) {
            return;
        }

        $privateKey = config('services.apple.private_key');
        if (! $privateKey && config('services.apple.private_key_path')) {
            $privateKey = @file_get_contents(config('services.apple.private_key_path'));
        }

        $teamId   = config('services.apple.team_id');
        $keyId    = config('services.apple.key_id');
        $clientId = config('services.apple.client_id');

        if (! $privateKey || ! $teamId || ! $keyId || ! $clientId) {
            throw new \RuntimeException('Apple Sign In is not fully configured (team_id, key_id, client_id and private key are required).');
        }

        $now = time();
        $secret = JWT::encode([
            'iss' => $teamId,
            'iat' => $now,
            'exp' => $now + 3600,
            'aud' => 'https://appleid.apple.com',
            'sub' => $clientId,
        ], $privateKey, 'ES256', $keyId);

        config(['services.apple.client_secret' => $secret]);
    }
}
