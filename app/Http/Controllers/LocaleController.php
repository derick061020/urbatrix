<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    /**
     * Cambia el idioma de la sesión actual.
     * Acepta JSON o form-post: { locale: 'es' | 'en' }
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'locale' => 'required|string|in:'.implode(',', config('app.supported_locales', ['es', 'en'])),
        ]);

        $request->session()->put('locale', $data['locale']);

        $cookie = Cookie::make('app_locale', $data['locale'], 60 * 24 * 365);

        if ($request->expectsJson() || $request->ajax()) {
            return response()
                ->json(['ok' => true, 'locale' => $data['locale']])
                ->withCookie($cookie);
        }

        return back()->withCookie($cookie);
    }
}
