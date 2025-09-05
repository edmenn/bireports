<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $cfg = \App\Models\Configuracion::query()->first();

        $allowMicrosoft = (bool) ($cfg
            && $cfg->microsoft_login_enabled
            && $cfg->powerbi_client_id
            && $cfg->powerbi_client_secret
            && $cfg->powerbi_tenant_id);

        $envOverrideLocal = (bool) config('auth.allow_local_login', false);
        $allowLocal = (bool) ($envOverrideLocal || ($cfg->local_login_enabled ?? true));

        if (! $allowLocal && ! $allowMicrosoft) {
            $allowLocal = true; // fallback para evitar bloqueo total
        }

        return view('auth.login', compact('allowLocal', 'allowMicrosoft'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

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
}
