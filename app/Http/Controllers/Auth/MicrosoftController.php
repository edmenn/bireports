<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\User;
use App\Services\MicrosoftGraphService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MicrosoftController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $cfg = Configuration::query()->first();
        if (! $cfg || empty($cfg->powerbi_client_id) || empty($cfg->powerbi_client_secret) || empty($cfg->powerbi_tenant_id)) {
            return redirect()->route('login')->with('status', __('auth.microsoft.not_configured'));
        }

        $tenant = trim((string) $cfg->powerbi_tenant_id);
        $clientId = (string) $cfg->powerbi_client_id;
        $redirectUri = route('auth.microsoft.callback');

        // Store state and nonce in the session to validate the callback
        $state = Str::random(40);
        $nonce = Str::random(40);
        $request->session()->put('ms_oauth_state', $state);
        $request->session()->put('ms_oauth_nonce', $nonce);

        $authorizeUrl = sprintf('https://login.microsoftonline.com/%s/oauth2/v2.0/authorize', $tenant);
        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'response_mode' => 'query',
            'scope' => 'openid profile email offline_access',
            'state' => $state,
            'nonce' => $nonce,
        ];

        return redirect($authorizeUrl . '?' . http_build_query($params));
    }

    public function callback(Request $request): RedirectResponse
    {
        $cfg = Configuration::query()->first();
        if (! $cfg) {
            return redirect()->route('login')->with('status', __('auth.microsoft.config_missing'));
        }

        // Basic validation for the OAuth response
        $state = $request->string('state')->toString();
        if (! $state || $state !== $request->session()->pull('ms_oauth_state')) {
            return redirect()->route('login')->with('status', __('auth.microsoft.invalid_state'));
        }
        if ($request->has('error')) {
            $msg = $request->string('error_description')->toString() ?: $request->string('error')->toString();
            return redirect()->route('login')->with(
                'status',
                $msg !== ''
                    ? __('auth.microsoft.login_cancelled_with_reason', ['reason' => $msg])
                    : __('auth.microsoft.login_cancelled')
            );
        }

        $code = $request->string('code')->toString();
        if (! $code) {
            return redirect()->route('login')->with('status', __('auth.microsoft.missing_code'));
        }

        $tenant = trim((string) $cfg->powerbi_tenant_id);
        $clientId = (string) $cfg->powerbi_client_id;
        $clientSecret = (string) $cfg->powerbi_client_secret;
        $redirectUri = route('auth.microsoft.callback');

        $tokenUrl = sprintf('https://login.microsoftonline.com/%s/oauth2/v2.0/token', $tenant);

        try {
            $http = new \GuzzleHttp\Client(['timeout' => 10]);
            $resp = $http->post($tokenUrl, [
                'form_params' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $data = json_decode((string) $resp->getBody(), true) ?: [];
            $idToken = (string) ($data['id_token'] ?? '');
            $accessToken = (string) ($data['access_token'] ?? '');

            if (! $idToken) {
                return redirect()->route('login')->with('status', __('auth.microsoft.missing_id_token'));
            }

            // Decode the JWT (without verifying the signature) to read basic claims
            $claims = $this->decodeJwt($idToken);
            if (! $claims) {
                return redirect()->route('login')->with('status', __('auth.microsoft.invalid_token'));
            }

            // Validate nonce when we have it stored
            $nonceExpected = (string) $request->session()->pull('ms_oauth_nonce');
            if (! empty($claims['nonce']) && $nonceExpected && $claims['nonce'] !== $nonceExpected) {
                return redirect()->route('login')->with('status', __('auth.microsoft.invalid_nonce'));
            }

            $oid = (string) ($claims['oid'] ?? '');
            $email = (string) ($claims['preferred_username'] ?? ($claims['email'] ?? ''));
            $name = (string) ($claims['name'] ?? '');
            $givenFromToken = (string) ($claims['given_name'] ?? '');
            $surnameFromToken = (string) ($claims['family_name'] ?? '');

            // If the email is missing in the id_token, try the userinfo endpoint (requires access_token)
            if (! $email && $accessToken) {
                try {
                    $uiResp = $http->get('https://graph.microsoft.com/oidc/userinfo', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Accept' => 'application/json',
                        ],
                    ]);
                    $ui = json_decode((string) $uiResp->getBody(), true) ?: [];
                    $email = (string) ($ui['email'] ?? ($ui['preferred_username'] ?? ''));
                    if (! $name) {
                        $name = (string) ($ui['name'] ?? '');
                    }
                } catch (Throwable $e) {
                    // Continue with whatever data is available
                    Log::warning('Microsoft userinfo request failed: ' . $e->getMessage());
                }
            }

            if (! $email) {
                return redirect()->route('login')->with('status', __('auth.microsoft.missing_email'));
            }

            // Look up an existing user by any Entra alias (UPN, mail, proxyAddresses)
            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                $candidates = [$email];
                $externalId = null;
                $namePreferred = '';
                try {
                    /** @var MicrosoftGraphService $graph */
                    $graph = app(MicrosoftGraphService::class);
                    $found = $graph->findUserByAnyAddress($email);
                    if (is_array($found)) {
                        $externalId = (string)($found['id'] ?? '') ?: null;
                        // Prefer givenName + surname; fall back to displayName
                        $gn = trim((string)($found['givenName'] ?? ''));
                        $sn = trim((string)($found['surname'] ?? ''));
                        $namePreferred = trim($gn . ' ' . $sn);
                        if ($namePreferred === '') {
                            $namePreferred = trim((string)($found['displayName'] ?? ''));
                        }
                        $upn  = (string)($found['userPrincipalName'] ?? '');
                        $mail = (string)($found['mail'] ?? '');
                        if ($upn !== '')  { $candidates[] = strtolower($upn); }
                        if ($mail !== '') { $candidates[] = strtolower($mail); }
                        foreach ((array)($found['otherMails'] ?? []) as $alt) {
                            $alt = strtolower(trim((string)$alt)); if ($alt !== '') $candidates[] = $alt;
                        }
                        foreach ((array)($found['proxyAddresses'] ?? []) as $p) {
                            $p = trim((string)$p); if ($p === '') continue;
                            $parts = explode(':', $p, 2);
                            $addr  = isset($parts[1]) ? strtolower($parts[1]) : strtolower($parts[0]);
                            if ($addr !== '') $candidates[] = $addr;
                        }
                    }
                } catch (Throwable $gx) {
                    Log::warning('Microsoft callback: failed to query Graph for aliases: ' . $gx->getMessage());
                }

                $candidates = array_values(array_unique(array_map(static fn($v) => strtolower(trim((string)$v)), $candidates)));
                if (!empty($candidates)) {
                    $user = User::query()->whereIn('email', $candidates)->first();
                }

                if (! $user) {
                    // Create a new user when no aliases matched existing accounts
                    $user = User::create([
                        'name' => $name ?: $email,
                        'email' => $email,
                        // Random password; not used for Microsoft sign-in
                        'password' => Str::password(32),
                    ]);
                    $user->forceFill(['email_verified_at' => now()])->save();
                    // Normalize the name to givenName + surname when available
                    if (empty($namePreferred)) {
                        $namePreferred = trim(trim($givenFromToken ?? '') . ' ' . trim($surnameFromToken ?? ''));
                    }
                    if (empty($namePreferred)) {
                        $namePreferred = trim($name ?? '');
                    }
                    if (!empty($namePreferred) && $user->name !== $namePreferred) {
                        $user->name = $namePreferred;
                        $user->save();
                    }
                    try { if (method_exists($user, 'assignRole')) { $user->assignRole('viewer'); } } catch (Throwable $e) {
                        Log::warning('Failed to assign default role to user: ' . $e->getMessage());
                    }
                }

                // Ensure provider/external id are stored on the current account
                $changed = false;
                if (empty($user->auth_provider)) { $user->auth_provider = 'entra'; $changed = true; }
                if (!empty($externalId) && empty($user->external_id)) { $user->external_id = $externalId; $changed = true; }
                if ($changed) { $user->save(); }
            }

            // Abort when the local account is blocked
            if ((bool)($user->is_blocked ?? false)) {
                return redirect()->route('login')->with('status', __('auth.microsoft.account_blocked'));
            }

            // Attempt to sync name and status from Entra before logging in
            try {
                /** @var MicrosoftGraphService $graph */
                $graph = app(MicrosoftGraphService::class);
                $entry = null;
                if (!empty($user->external_id)) {
                    $entry = $graph->getUserById((string)$user->external_id);
                }
                if (!$entry) {
                    $entry = $graph->findUserByAnyAddress($email);
                }
                if (is_array($entry)) {
                    $accEnabled = (bool)($entry['accountEnabled'] ?? false);
                    if (!$accEnabled) {
                        // Block and stop the login flow
                        $user->forceFill(['is_blocked' => true])->save();
                        return redirect()->route('login')->with('status', __('auth.microsoft.account_disabled'));
                    }
                    // Sync the name if it differs (givenName + surname -> displayName)
                    $gn = trim((string)($entry['givenName'] ?? ''));
                    $sn = trim((string)($entry['surname'] ?? ''));
                    $preferred = trim($gn . ' ' . $sn);
                    if ($preferred === '') { $preferred = trim((string)($entry['displayName'] ?? '')); }
                    if ($preferred !== '' && $preferred !== (string)$user->name) {
                        $user->forceFill(['name' => $preferred])->save();
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Microsoft login: failed to sync name/state', ['error' => $e->getMessage()]);
            }

            Auth::login($user, true);
            $request->session()->regenerate();
            $this->applyLocalePreference($request, $user);
            return redirect()->intended(route('dashboards.index'));
        } catch (Throwable $e) {
            Log::error('Microsoft callback failed: ' . $e->getMessage());
            return redirect()->route('login')->with('status', __('auth.microsoft.process_error'));
        }
    }

    private function decodeJwt(string $jwt): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            return null;
        }
        $payload = $parts[1] ?? '';
        $payload = strtr($payload, '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
        $json = base64_decode($payload, true);
        if ($json === false) {
            return null;
        }
        return json_decode($json, true);
    }

    private function applyLocalePreference(Request $request, User $user): void
    {
        $supported = config('app.supported_locales', ['en', 'es', 'pt']);
        $fallbackLocale = config('app.locale');

        $userLocale = in_array($user->locale, $supported, true)
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

        if (! $userLocale && $cookieLocale === $locale && $locale !== $fallbackLocale) {
            $user->forceFill(['locale' => $locale])->save();
        }
    }
}
