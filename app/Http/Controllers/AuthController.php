<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    /* =================================================================
     * Single-page auth: login + multi-step register share auth.auth view
     * ================================================================= */

    public function showLogin()
    {
        return view('auth.auth', ['mode' => 'login']);
    }

    public function showRegister()
    {
        return view('auth.auth', ['mode' => 'register']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! $user->password || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden.',
            ])->onlyInput('email');
        }

        // Si el usuario tiene 2FA confirmada, NO lo autenticamos todavía:
        // guardamos su id en sesión y lo mandamos al desafío del segundo factor.
        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('login.2fa', [
                'id'       => $user->id,
                'remember' => $request->boolean('remember'),
            ]);
            return redirect()->route('2fa.challenge');
        }

        Auth::login($user, (bool) $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->put('activity_login_id', \App\Support\ActivityLogger::startSession($user->id));
        // Honra la URL que el usuario intentaba ver (p. ej. un link compartido /?unit=123);
        // si no hay ninguna, cae a la ruta por defecto según el rol.
        return redirect()->intended($user->postAuthRedirectPath());
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /* ---------- Multi-step register ---------- */

    /** Step 0: name + email + phone → store in session, issue 6-digit code. */
    public function registerInit(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'phone'     => 'required|string|max:30',
            'country'   => 'nullable|string|max:10',
            'terms'     => 'accepted',
        ]);

        $code = (string) random_int(100000, 999999);
        $request->session()->put('register', [
            'full_name' => $data['full_name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'country'   => $data['country'] ?? 'DO+1',
            'code'      => $code,
            'verified'  => false,
        ]);

        // Envía el código de verificación por correo.
        $this->sendCode($data['email'], $code, 'register', $data['full_name']);

        return response()->json([
            'ok'   => true,
            'code' => app()->environment('production') ? null : $code,
        ]);
    }

    public function registerResend(Request $request)
    {
        $reg = $request->session()->get('register');
        if (! $reg) return response()->json(['message' => 'Sesión expirada'], 422);

        $code = (string) random_int(100000, 999999);
        $reg['code'] = $code;
        $request->session()->put('register', $reg);

        // Reenvía el código por correo.
        $this->sendCode($reg['email'], $code, 'register', $reg['full_name'] ?? '');

        return response()->json([
            'ok'   => true,
            'code' => app()->environment('production') ? null : $code,
        ]);
    }

    /** Step 1: verify 6-digit code. */
    public function registerVerify(Request $request)
    {
        $data = $request->validate(['code' => 'required|digits:6']);
        $reg  = $request->session()->get('register');
        if (! $reg) return response()->json(['message' => 'Sesión expirada'], 422);

        if ($data['code'] !== $reg['code']) {
            return response()->json(['message' => 'Código incorrecto'], 422);
        }
        $reg['verified'] = true;
        $request->session()->put('register', $reg);

        return response()->json(['ok' => true]);
    }

    /** Final submit: validate everything, create user, save docs, login. */
    public function registerComplete(Request $request)
    {
        $reg = $request->session()->get('register');
        if (! $reg || ! ($reg['verified'] ?? false)) {
            return response()->json(['message' => 'Verifica tu email antes de continuar'], 422);
        }

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'phone'     => 'required|string|max:30',
            'country'   => 'nullable|string|max:10',
            'role'      => 'required|in:buyer,broker,agency',
            'password'  => 'required|string|min:8|confirmed',
            'docs'      => 'array',
            'docs.*'    => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        // Split full_name into first/last
        $parts     = preg_split('/\s+/', trim($data['full_name']), 2);
        $firstName = $parts[0] ?? '';
        $lastName  = $parts[1] ?? '';

        $hasDocs = $request->hasFile('docs');

        $userAttrs = [
            'name'     => $data['full_name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => $data['role'] === 'buyer' ? 'user' : 'broker',
        ];
        if (Schema::hasColumn('users', 'first_name'))          $userAttrs['first_name']          = $firstName;
        if (Schema::hasColumn('users', 'last_name'))           $userAttrs['last_name']           = $lastName;
        if (Schema::hasColumn('users', 'phone'))               $userAttrs['phone']               = $data['phone'];
        if (Schema::hasColumn('users', 'country'))             $userAttrs['country']             = $data['country'] ?? null;
        if (Schema::hasColumn('users', 'verification_status')) {
            // Brokers/agencies + anyone who uploaded docs → pending admin verification.
            // Buyers who skipped docs → approved immediately (no verification needed).
            $userAttrs['verification_status'] = ($hasDocs || $userAttrs['role'] === 'broker') ? 'pending' : 'approved';
        }

        $user = User::create($userAttrs);

        // Persist uploaded onboarding docs + remember id_front path on the user
        if ($hasDocs) {
            $folder = 'onboarding/'.$user->id;
            foreach ((array) $request->file('docs') as $key => $file) {
                if (! $file) continue;
                $stored = $file->storeAs($folder, $key.'.'.$file->getClientOriginalExtension(), 'public');

                // The KYC ID document (id_front) lets us skip re-asking in /form later
                if ($key === 'id_front' && Schema::hasColumn('users', 'kyc_id_document')) {
                    $user->update(['kyc_id_document' => $stored]);
                }
                // Remember the reverse side too, so /form can reuse it as well
                if ($key === 'id_back' && Schema::hasColumn('users', 'kyc_id_document_back')) {
                    $user->update(['kyc_id_document_back' => $stored]);
                }
            }

            // Create a Document row tied to the user (so admin sees it in CRM/Documentos)
            // — works even before the user has a reservation
            try {
                Storage::disk('public')->files($folder); // sanity check
                foreach ((array) $request->file('docs') as $key => $file) {
                    if (! $file) continue;
                    \App\Models\Document::create([
                        'reservation_id' => null,
                        'document_type'  => $key,
                        'title'          => $this->humanDocLabel($key, $user->name),
                        'filename'       => $file->getClientOriginalName(),
                        'file_path'      => $folder.'/'.$key.'.'.$file->getClientOriginalExtension(),
                        'status'         => 'pending',
                        'generated_at'   => now(),
                        'metadata'       => ['user_id' => $user->id, 'source' => 'register'],
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('Could not create Document rows on register: '.$e->getMessage());
            }
        }

        Auth::login($user);
        $request->session()->forget('register');
        $request->session()->regenerate();

        return response()->json([
            'ok'       => true,
            'pending'  => $user->isPendingVerification(),
            // Honra la URL guardada (link compartido /?unit=123) o cae a la ruta por rol.
            'redirect' => $request->session()->pull('url.intended', $user->postAuthRedirectPath()),
        ]);
    }

    /* =================================================================
     * Forgot password — 3 steps: email → 6-digit code → new password
     * ================================================================= */

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /** Step 1: receive email, generate a 6-digit code, store it (hashed). */
    public function forgotPasswordSend(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $user = User::where('email', $data['email'])->first();

        // To avoid leaking which emails exist, always respond ok.
        // In dev, we still return the code only when the user exists.
        if (! $user) {
            return response()->json(['ok' => true, 'code' => null]);
        }

        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $data['email']],
            ['token' => Hash::make($code), 'created_at' => now()]
        );

        $request->session()->put('password_reset', [
            'email'    => $data['email'],
            'verified' => false,
        ]);

        // Envía el código de restablecimiento por correo.
        $this->sendCode($data['email'], $code, 'reset', $user->name ?? '');

        return response()->json([
            'ok'   => true,
            'code' => app()->environment('production') ? null : $code,
        ]);
    }

    /** Step 2: verify the 6-digit code. */
    public function forgotPasswordVerify(Request $request)
    {
        $data = $request->validate(['code' => 'required|digits:6']);

        $reset = $request->session()->get('password_reset');
        if (! $reset || empty($reset['email'])) {
            return response()->json(['message' => 'Sesión expirada'], 422);
        }

        $row = DB::table('password_reset_tokens')->where('email', $reset['email'])->first();
        if (! $row) {
            return response()->json(['message' => 'Código inválido o expirado'], 422);
        }

        // Expire codes after 60 minutes (matches config/auth.php passwords.expire).
        if (now()->diffInMinutes($row->created_at) > 60) {
            return response()->json(['message' => 'El código ha expirado'], 422);
        }

        if (! Hash::check($data['code'], $row->token)) {
            return response()->json(['message' => 'Código incorrecto'], 422);
        }

        $reset['verified'] = true;
        $request->session()->put('password_reset', $reset);

        return response()->json(['ok' => true]);
    }

    /** Step 3: set new password. */
    public function forgotPasswordReset(Request $request)
    {
        $reset = $request->session()->get('password_reset');
        if (! $reset || empty($reset['verified'])) {
            return response()->json(['message' => 'Verifica tu email antes de continuar'], 422);
        }

        $data = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $reset['email'])->first();
        if (! $user) {
            return response()->json(['message' => 'Usuario no encontrado'], 422);
        }

        $user->update(['password' => bcrypt($data['password'])]);

        DB::table('password_reset_tokens')->where('email', $reset['email'])->delete();
        $request->session()->forget('password_reset');

        return response()->json([
            'ok'       => true,
            'redirect' => route('login'),
        ]);
    }

    /* =================================================================
     * Invitación de cuenta — un cliente registrado por el equipo desde
     * "Nueva reserva" recibe un enlace para crear su contraseña y activar
     * su cuenta. El token se guarda (hasheado) en password_reset_tokens.
     * ================================================================= */

    /** Días de validez del enlace de invitación. */
    public const INVITATION_DAYS = 7;

    /**
     * Crea (o renueva) un token de invitación para un email y devuelve la URL
     * de activación lista para enviar por correo. Centraliza la lógica para que
     * el flujo de "Nueva reserva" la reutilice.
     */
    public static function makeInvitationUrl(string $email): string
    {
        $raw = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($raw), 'created_at' => now()]
        );

        return route('invitation.show', ['token' => $raw]) . '?email=' . urlencode($email);
    }

    /** Pantalla de activación: valida el token y muestra el formulario de contraseña. */
    public function showInvitation(Request $request, string $token)
    {
        $email = (string) $request->query('email', '');
        $user  = User::where('email', $email)->first();
        $valid = $user && $this->invitationTokenIsValid($email, $token);

        return view('auth.invitation', [
            'valid' => $valid,
            'token' => $token,
            'email' => $email,
            'name'  => $valid ? ($user->first_name ?: $user->name) : '',
        ]);
    }

    /** Activa la cuenta: fija la contraseña, consume el token e inicia sesión. */
    public function acceptInvitation(Request $request, string $token)
    {
        $email = (string) $request->query('email', $request->input('email', ''));

        if (! $this->invitationTokenIsValid($email, $token)) {
            return response()->json(['message' => 'El enlace ha caducado o no es válido.'], 422);
        }

        $data = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $email)->first();
        if (! $user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 422);
        }

        $update = ['password' => bcrypt($data['password'])];
        if (Schema::hasColumn('users', 'verification_status')) {
            $update['verification_status'] = 'approved';
        }
        $user->update($update);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('activity_login_id', \App\Support\ActivityLogger::startSession($user->id));

        return response()->json([
            'ok'       => true,
            'redirect' => $request->session()->pull('url.intended', $user->postAuthRedirectPath()),
        ]);
    }

    /** Comprueba que el token de invitación coincide y no ha expirado. */
    private function invitationTokenIsValid(string $email, string $token): bool
    {
        if ($email === '' || $token === '') return false;

        $row = DB::table('password_reset_tokens')->where('email', $email)->first();
        if (! $row) return false;

        // Carbon 3 devuelve diferencias con signo; comparamos contra el umbral.
        if (\Illuminate\Support\Carbon::parse($row->created_at)->lt(now()->subDays(self::INVITATION_DAYS))) {
            return false;
        }

        return Hash::check($token, $row->token);
    }

    /**
     * Envía el código de verificación por correo. Nunca interrumpe el flujo:
     * si el correo falla, se registra y el proceso continúa.
     */
    private function sendCode(string $email, string $code, string $purpose, string $name = ''): void
    {
        try {
            Mail::to($email)->send(new \App\Mail\VerificationCodeMail($code, $purpose, $name, 60));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el código de verificación ('.$purpose.') a '.$email.': '.$e->getMessage());
        }
    }

    private function humanDocLabel(string $key, string $userName): string
    {
        $map = [
            'id_front' => 'Documento de identidad (Frente)',
            'id_back'  => 'Documento de identidad (Reverso)',
            'rnc'      => 'Registro fiscal / RNC',
            'bank'     => 'Datos bancarios',
            'photo'    => 'Foto de perfil',
        ];
        return ($map[$key] ?? ucfirst($key)).' — '.$userName;
    }
}
