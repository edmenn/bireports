<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MicrosoftController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $cfg = Configuracion::query()->first();
        if (! $cfg || empty($cfg->powerbi_client_id) || empty($cfg->powerbi_client_secret) || empty($cfg->powerbi_tenant_id)) {
            return redirect()->route('login')->with('status', 'Inicio de sesión con Microsoft no está configurado.');
        }

        $tenant = trim((string) $cfg->powerbi_tenant_id);
        $clientId = (string) $cfg->powerbi_client_id;
        $redirectUri = route('auth.microsoft.callback');

        // Guardar state y nonce en sesión para validar el callback
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
        $cfg = Configuracion::query()->first();
        if (! $cfg) {
            return redirect()->route('login')->with('status', 'Configuración ausente.');
        }

        // Validaciones básicas
        $state = $request->string('state')->toString();
        if (! $state || $state !== $request->session()->pull('ms_oauth_state')) {
            return redirect()->route('login')->with('status', 'Estado inválido en respuesta de Microsoft.');
        }
        if ($request->has('error')) {
            $msg = $request->string('error_description')->toString() ?: $request->string('error')->toString();
            return redirect()->route('login')->with('status', 'Inicio de sesión cancelado: ' . $msg);
        }

        $code = $request->string('code')->toString();
        if (! $code) {
            return redirect()->route('login')->with('status', 'Código de autorización faltante.');
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
                return redirect()->route('login')->with('status', 'No se recibió id_token de Microsoft.');
            }

            // Decodificar JWT (sin verificar firma) para extraer claims básicas
            $claims = $this->decodeJwt($idToken);
            if (! $claims) {
                return redirect()->route('login')->with('status', 'Token inválido.');
            }

            // Validar nonce si lo tenemos
            $nonceExpected = (string) $request->session()->pull('ms_oauth_nonce');
            if (! empty($claims['nonce']) && $nonceExpected && $claims['nonce'] !== $nonceExpected) {
                return redirect()->route('login')->with('status', 'Nonce inválido.');
            }

            $oid = (string) ($claims['oid'] ?? '');
            $email = (string) ($claims['preferred_username'] ?? ($claims['email'] ?? ''));
            $name = (string) ($claims['name'] ?? '');

            // Si el email no viene en el id_token, intentamos userinfo (requiere access_token)
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
                    // Continuar con lo que tengamos
                    Log::warning('Microsoft userinfo falló: ' . $e->getMessage());
                }
            }

            if (! $email) {
                return redirect()->route('login')->with('status', 'No se pudo determinar el email del usuario de Microsoft.');
            }

            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                $user = User::create([
                    'name' => $name ?: $email,
                    'email' => $email,
                    // Password aleatorio; no se usará al loguear por Microsoft
                    'password' => Str::password(32),
                ]);
                // Marcar email como verificado para pasar el middleware 'verified'
                $user->forceFill(['email_verified_at' => now()])->save();
                try {
                    if (method_exists($user, 'assignRole')) {
                        $user->assignRole('usuario');
                    }
                } catch (Throwable $e) {
                    Log::warning('No se pudo asignar rol por defecto al usuario: ' . $e->getMessage());
                }
            }

            Auth::login($user, true);
            return redirect()->intended(route('dashboards.index'));
        } catch (Throwable $e) {
            Log::error('Error en callback Microsoft: ' . $e->getMessage());
            return redirect()->route('login')->with('status', 'Error al procesar respuesta de Microsoft.');
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
}

