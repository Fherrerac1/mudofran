<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AccessLog;
use App\Models\Configuration;
use App\Models\CosteKilometro;
use App\Models\Dieta;
use App\Models\Metodos_Pago;
use App\Models\PlantillaMaestra;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function myZone()
    {
        $user = User::find(Auth::user()->id)->load('timeRecords', 'timeOffs');

        // Check if the user is an admin
        if ($user->rol === 'admin') {
            $metodos = Metodos_Pago::all();
            $configuration = Configuration::first();
            $plantilla = PlantillaMaestra::where('tipo', 'Factura')->get();

            return Inertia::render(
                'Profile/Admin',
                [
                    'user' => $user,
                    'configuration' => $configuration,
                    'metodos' => $metodos,
                    'plantilla' => $plantilla,
                ]
            );
        }
        else {
            // If the user is blocked, log them out
            if ($user->blocked) {
                return Redirect::route('logout');
            }

            $hoursToday = $user->workedTodayForKpi();
            $hoursThisWeek = $user->hoursWorkedThisWeek();
            $dailyLast7Days = $user->dailyHoursWorkedLast7Days();
            $dailyForChart = $user->dailyHoursWorkedForChart();

            return Inertia::render('Profile/User', [
                'user' => $user,
                'hoursWorkedToday' => $hoursToday['workedHoursDecimal'],
                'hoursWorkedThisWeek' => $hoursThisWeek,
                'dailyLast7Days' => $dailyLast7Days,
                'dailyChartData' => $dailyForChart,
            ]);
        }
    }


    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function store_metodo(Request $request)
    {
        $this->admin_rol(); // Ensure the user has the necessary role
        $user = Auth::user(); // Get the currently authenticated user

        // Define validation rules
        $reglas = [
            'metodo' => 'required|unique:metodos_pago',
            'bank_name' => 'required',
            'concepto' => 'required',
        ];

        // Validate the request
        $request->validate($reglas);

        // Create and save the new Metodos_Pago record, including the user_id
        $metodo = new Metodos_Pago($request->all());
        $metodo->user_id = $user->id; // Add the authenticated user's ID
        $metodo->save();

        return redirect()->back()->with('success', 'Método de pago agregado exitosamente');
    }

    public function updateMetodo(Request $request, $id)
    {
        $this->admin_rol(); // Validar rol si aplica

        $request->validate([
            'metodo' => 'required',
            'bank_name' => 'required',
            'concepto' => 'required',
        ]);

        $metodo = Metodos_Pago::findOrFail($id);
        $metodo->metodo = $request->metodo;
        $metodo->bank_name = $request->bank_name;
        $metodo->concepto = $request->concepto;
        $metodo->save();

        return redirect()->back()->with('success', 'Método de pago actualizado correctamente');
    }




    public function destroyMetodo($id)
    {
        // Find the Metodos_Pago record by its ID and delete it
        $metodo = Metodos_Pago::findOrFail($id);
        $metodo->delete();

        return redirect()->back();
    }

    public function updatePasswordByAdmin(Request $request, $id)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->rol, ['admin', 'gestor'])) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $request->validate([
            'password' => ['required', 'string', 'confirmed'],
        ]);

        $user = User::findOrFail($id);
        $user->password = bcrypt($request->get('password'));
        $user->save();

        AccessLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'action' => 'Cambio de contraseña por responsable para el usuario con ID: ' . $id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);

        return Redirect::back()->with('success', 'Contraseña actualizada correctamente');
    }
}
