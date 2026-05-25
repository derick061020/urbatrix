<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            $redirectPath = $user->role === 'admin' ? '/admin/crm/dashboard' : '/dashboard';
            return redirect()->intended($redirectPath);
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ])->onlyInput('email');
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

        // In production: send via Mail/SMS. For demo, return code so UI shows it.
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
            'redirect' => $user->role === 'admin' ? '/admin/crm/dashboard' : '/dashboard',
        ]);
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
