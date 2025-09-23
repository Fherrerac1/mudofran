<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Configuration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    //api elayudante
    private function checkUser($numero_serie, $nif, $url_app)
    {
        // Define the parameters to be sent in the request
        $params = [
            'numero_serie' => $numero_serie,
            'nif' => $nif,
            'url_app' => $url_app,
        ];
        $params = json_encode($params);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.elayudante.es/api/check',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $configuration = Configuration::first();
        $response = $this->checkUser($configuration->serial_num, $configuration->tax_id, $configuration->url_app);
        if ($response->codigo == 0) {
            return back()->withErrors([
                'LICENCIA' => "LICENCIA ERRONEA O EXPIRADA, RENUEVA TU SUSCRIPCIÓN."
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->rol == 'super_admin') {
            return redirect()->intended(route('tenants.index', absolute: false));
        } elseif ($user->rol == 'admin' || $user->rol == 'gestor') {
            return redirect()->intended(route('dashboard', absolute: false));
        } else {
            return redirect()->intended(route('my.zone', absolute: false));
        }
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

    /**
     * Log in using an API token (magic link).
     */
    public function loginWithToken(Request $request, $email, $token)
    {
        // Find the tenant by api_token
        $tenant = \App\Models\Tenant::where('api_token', $token)->first();
        if (!$tenant) {
            return redirect()->route('login')->withErrors([
                'token' => 'Token inválido o expirado.'
            ]);
        }
        $configuration = Configuration::where('tenant_id', $tenant->id)->first();
        $response = $this->checkUser($configuration->serial_num, $configuration->tax_id, $configuration->url_app);
        if ($response->codigo == 0) {
            return response()->json([
                'error' => 'LICENCIA ERRONEA O EXPIRADA, RENUEVA TU SUSCRIPCIÓN.'
            ], 403); // 403 Forbidden
        }

        // If you have a user associated with the tenant
        $user = User::where('email', $email)->where('tenant_id', $tenant->id)->first();

        if (!$user) {
            return response()->json([
                'token' => 'Usuario no encontrado para este token.'
            ], 403); // 403 Forbidden

        }

        // Log in the user
        Auth::login($user);

        $request->session()->regenerate();

        // Redirect based on role
        if ($user->rol == 'admin' || $user->rol == 'gestor') {
            return redirect()->intended(route('dashboard', absolute: false));
        } else {
            return redirect()->intended(route('my.zone', absolute: false));
        }
    }

}
