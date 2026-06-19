<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    /* =================================================================
     * Gestión 2FA desde el modal de configuración (usuario autenticado)
     * ================================================================= */

    /**
     * Paso 1 — Genera un secreto (aún sin confirmar) y devuelve el secreto en
     * texto + la URI otpauth:// para pintar el QR en el navegador.
     */
    public function enable(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return response()->json(['message' => 'La 2FA ya está activada.'], 422);
        }

        $secret = Totp::generateSecret();

        // Se guarda el secreto pero sin confirmar (confirmed_at = null), de modo
        // que aún NO se exige en el login hasta que el usuario valide un código.
        $user->forceFill([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ])->save();

        return response()->json([
            'secret'      => $secret,
            'otpauth_uri' => Totp::otpauthUri($secret, $user->email, config('app.name', 'LaunchBase')),
        ]);
    }

    /**
     * Paso 2 — Verifica el código del authenticator. Si es correcto, confirma
     * la 2FA y genera los códigos de respaldo, que se devuelven una sola vez.
     */
    public function confirm(Request $request)
    {
        $data = $request->validate(['code' => 'required|string']);

        /** @var User $user */
        $user = $request->user();

        if (! $user->two_factor_secret) {
            return response()->json(['message' => 'Primero generá el código QR.'], 422);
        }
        if ($user->hasTwoFactorEnabled()) {
            return response()->json(['message' => 'La 2FA ya está confirmada.'], 422);
        }
        if (! Totp::verify($user->two_factor_secret, $data['code'])) {
            return response()->json(['message' => 'El código no es válido. Probá de nuevo.'], 422);
        }

        $codes = User::generateRecoveryCodes();
        $user->forceFill([
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at'   => now(),
        ])->save();

        return response()->json([
            'ok'             => true,
            'recovery_codes' => $codes,
        ]);
    }

    /** Desactiva la 2FA. Exige confirmar la contraseña actual. */
    public function disable(Request $request)
    {
        $data = $request->validate(['password' => 'required|string']);

        /** @var User $user */
        $user = $request->user();

        if ($user->password && ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'La contraseña no es correcta.'], 422);
        }

        $user->forceFill([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ])->save();

        return response()->json(['ok' => true]);
    }

    /** Devuelve los códigos de respaldo vigentes (2FA debe estar activa). */
    public function recoveryCodes(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return response()->json(['message' => 'Activá la 2FA para generar códigos de respaldo.'], 422);
        }

        return response()->json(['recovery_codes' => $user->recoveryCodes()]);
    }

    /** Regenera el lote de códigos de respaldo (invalida los anteriores). */
    public function regenerateRecoveryCodes(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return response()->json(['message' => 'Activá la 2FA para generar códigos de respaldo.'], 422);
        }

        $codes = User::generateRecoveryCodes();
        $user->forceFill(['two_factor_recovery_codes' => $codes])->save();

        return response()->json(['recovery_codes' => $codes]);
    }

    /* =================================================================
     * Desafío 2FA en el login (usuario aún NO autenticado del todo)
     * ================================================================= */

    public function challenge(Request $request)
    {
        if (! $request->session()->has('login.2fa.id')) {
            return redirect()->route('login');
        }
        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $pending = $request->session()->get('login.2fa');
        if (! $pending || empty($pending['id'])) {
            return redirect()->route('login')->withErrors(['email' => 'La sesión expiró. Iniciá sesión de nuevo.']);
        }

        $request->validate([
            'code'          => 'required_without:recovery_code|nullable|string',
            'recovery_code' => 'required_without:code|nullable|string',
        ]);

        /** @var User|null $user */
        $user = User::find($pending['id']);
        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget('login.2fa');
            return redirect()->route('login');
        }

        $passed = false;
        if ($request->filled('code')) {
            $passed = Totp::verify($user->two_factor_secret, $request->input('code'));
        } elseif ($request->filled('recovery_code')) {
            $passed = $user->consumeRecoveryCode($request->input('recovery_code'));
        }

        if (! $passed) {
            return back()->withErrors(['code' => 'El código no es válido.']);
        }

        $request->session()->forget('login.2fa');
        Auth::login($user, (bool) ($pending['remember'] ?? false));
        $request->session()->regenerate();
        $request->session()->put('activity_login_id', \App\Support\ActivityLogger::startSession($user->id));

        // Honra el link que el usuario intentaba ver antes de pasar por el 2FA.
        return redirect()->intended($user->postAuthRedirectPath());
    }
}
