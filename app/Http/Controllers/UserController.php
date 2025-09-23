<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $this->admin_rol();

        $user = User::find(Auth::user()->id);

        if ($user->rol === 'admin' || ($user->rol === 'gestor')) {
            $usuarios = User::whereNotIn('rol', ['super'])
                ->with(['timeRecords']) // Eager load relationships
                ->get();

            return Inertia::render('Cruds/Users/Index', [
                'usuarios' => $usuarios,
                'user' => $user,
                'puestos' => User::puestosDisponibles(),
                'roles' => User::rolesDisponibles(),
            ]);
        } else {
            $usuarios = User::whereNotIn('rol', ['super'])
                ->with(['timeRecords'])
                ->get();

            return Inertia::render('Cruds/Users/Index', [
                'usuarios' => $usuarios,
                'user' => $user,
                'puestos' => User::puestosDisponibles(),
            ]);
        }
    }

    public function create()
    {
        $this->admin_rol();
        $user = User::find(Auth::user()->id);

        return Inertia::render('Cruds/Users/Create', [
            'user' => $user,
        ]);
    }

    public function edit($id)
    {
        $this->admin_rol();
        $user = User::find(Auth::user()->id);
        $usuario = User::find($id);

        return Inertia::render('Cruds/Users/Update', [
            'usuario' => $usuario,
            'user' => $user,
        ]);
    }

    private function checkUser($numero_serie, $nif, $url_app)
    {
        $params = [
            'numero_serie' => $numero_serie,
            'nif' => $nif,
            'url_app' => $url_app,
        ];

        $response = Http::post('https://api.elayudante.es/api/check', $params);
        return $response->json();
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        // Validate request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:6|max:255|confirmed',
            'rol' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'localidad' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'cp' => 'nullable|integer',
            'coste_hora' => 'nullable|numeric',
            'dni' => 'nullable|string|unique:users|max:20',
            'img' => 'nullable|image|max:2048',
            'fecha_alta' => 'nullable|date',
            'fecha_nacimiento' => 'nullable|date',
            'num_seguridad' => 'nullable|string|max:20',
            'blocked' => 'nullable|boolean',
            'horario_semanal' => 'nullable|numeric',
            'dias_laborables' => 'nullable|numeric',
            'estado' => 'nullable|string|max:255',
        ]);

        // Hash the password
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Create user record
        $user = User::create($validatedData);

        // Handle image upload
        if ($request->hasFile('img')) {
            $uploadedFile = $request->file('img');
            $filePath = $uploadedFile->store('public/users_profiles'); // Store file
            $user->img = $filePath;
            $user->save();
        }

        // Log the creation action
        AccessLog::create([
            'user_id' => $authUser->id,
            'email' => $authUser->email,
            'action' => 'Creación de perfil de usuario con ID: ' . $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        // Redirect to the user index
        return Inertia::location(route('user.index'));
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();
        $user = User::findOrFail($id);

        // Validate the incoming request, excluding the password field
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id . '|max:255',
            'rol' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'localidad' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'cp' => 'nullable|integer',
            'coste_hora' => 'nullable|numeric',
            'dni' => 'nullable|string|unique:users,dni,' . $user->id . '|max:20',
            'fecha_alta' => 'nullable|date',
            'fecha_nacimiento' => 'nullable|date',
            'num_seguridad' => 'nullable|string|max:20',
            'horario_semanal' => 'nullable|numeric',
            'dias_laborables' => 'nullable|integer',
            'blocked' => 'nullable|boolean',
            'estado' => 'nullable',
        ]);

        // Update user details excluding 'img'
        $user->update($validatedData);

        // Handle image update or deletion
        if ($request->hasFile('img')) {
            // Delete the old image if it exists
            if ($user->img) {
                Storage::delete($user->img);
            }

            // Store the new image
            $uploadedFile = $request->file('img');
            $filePath = $uploadedFile->store('public/users_profiles');
            $user->img = $filePath;
        } elseif ($request->input('img') === null && $user->img) {
            // If `img` is explicitly null, delete the existing image
            Storage::delete($user->img);
            $user->img = null;
        }

        // Save changes to the user
        $user->save();

        // Log the update action
        AccessLog::create([
            'user_id' => $authUser->id,
            'email' => $authUser->email,
            'action' => 'Actualización de perfil de usuario con ID: ' . $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        // Redirect to the user index
        return Inertia::location(route('user.index'));
    }


    public function show($id)
    {
        // usuario autenticado
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        // usuario a mostrar
        $usuario = User::findOrFail($id);

        $hoursToday = $usuario->workedTodayForKpi();
        $hoursThisWeek = $usuario->hoursWorkedThisWeek();
        $dailyLast7Days = $usuario->dailyHoursWorkedLast7Days();
        $dailyForChart = $usuario->dailyHoursWorkedForChart();

        return Inertia::render('Cruds/Users/Show', [
            'user' => $user, // solo para el layout
            'usuario' => $usuario, // el que se visualiza
            'hoursWorkedToday' => $hoursToday['workedHoursDecimal'],
            'hoursWorkedThisWeek' => $hoursThisWeek,
            'dailyLast7Days' => $dailyLast7Days,
            'dailyChartData' => $dailyForChart,
        ]);
    }

    public function destroy($id)
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        $usuario = User::findOrFail($id);

        $usuario->delete();

        $log = new AccessLog([
            'user_id' => $user->id,
            'email' => $user->email,
            'action' => 'Eliminación de usuario con ID: ' . $id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
        $log->save();

        return redirect()->route('user.index');
    }
}
