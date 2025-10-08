<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $supported = config('app.supported_locales', ['en', 'es', 'pt']);

        $locale = $request->session()->get('locale');

        $userLocale = optional($request->user())->locale;
        if (! $this->isSupported($locale, $supported) && $this->isSupported($userLocale, $supported)) {
            $locale = $userLocale;
        }

        $cookieLocale = $request->cookie('locale');
        if (! $this->isSupported($locale, $supported) && $this->isSupported($cookieLocale, $supported)) {
            $locale = $cookieLocale;
        }

        if (! $this->isSupported($locale, $supported)) {
            $locale = config('app.locale');
        }

        if ($request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        App::setLocale($locale);

        return $next($request);
    }

    private function isSupported(?string $locale, array $supported): bool
    {
        return is_string($locale) && in_array($locale, $supported, true);
    }
}
