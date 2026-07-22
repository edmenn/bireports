<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Configuration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $cfg = Configuration::query()->first();
        $allowMicrosoft = $this->microsoftLoginAvailable($cfg);
        $allowLocal = $this->localLoginAvailable($cfg);

        return view('auth.login', compact('allowLocal', 'allowMicrosoft'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        abort_unless($this->localLoginAvailable(Configuration::query()->first()), 403);

        $request->authenticate();

        $request->session()->regenerate();

        $supported = config('app.supported_locales', ['en', 'es', 'pt']);
        $fallbackLocale = config('app.locale');

        $user = $request->user();
        $userLocale = $user && in_array($user->locale, $supported, true)
            ? $user->locale
            : null;

        $cookieLocale = $request->cookie('locale');
        if (! in_array($cookieLocale, $supported, true)) {
            $cookieLocale = null;
        }

        $locale = $userLocale ?? $cookieLocale ?? $fallbackLocale;

        $request->session()->put('locale', $locale);
        Cookie::queue('locale', $locale, 60 * 24 * 365);
        App::setLocale($locale);

        if (! $userLocale && $user && $cookieLocale === $locale && $locale !== $fallbackLocale) {
            $user->forceFill(['locale' => $locale])->save();
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function microsoftLoginAvailable(?Configuration $cfg): bool
    {
        return (bool) ($cfg?->microsoft_login_enabled
            && $cfg->powerbi_client_id
            && $cfg->powerbi_client_secret
            && $cfg->powerbi_tenant_id);
    }

    private function localLoginAvailable(?Configuration $cfg): bool
    {
        return (bool) config('auth.allow_local_login', false)
            || (bool) ($cfg?->local_login_enabled ?? true)
            || ! $this->microsoftLoginAvailable($cfg);
    }
}
