<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccessLog;
use App\Models\Contacto;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ContactosController extends Controller
{
    public function index()
    {
        $this->admin_rol();
        $user = User::find(Auth::user()->id);

        $contactos = Contacto::all();

        return Inertia::render('Cruds/Contactos/Index', [
            'user' => $user,
            'contactos' => $contactos
        ]);
    }

    public function create()
    {
        $this->admin_rol();

        $user = User::find(Auth::user()->id);


        return Inertia::render('Cruds/Contactos/Create', [
            'user' => $user,
        ]);
    }

    public function edit($id)
    {
        $this->admin_rol();

        $user = User::find(Auth::user()->id);
        $contacto = Contacto::find($id);

        return Inertia::render('Cruds/Contactos/Update', [
            'contacto' => $contacto,
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        // Define validation rules
        $rules = [
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|unique:contactos,email',
            'telefono_mobile' => 'nullable|regex:/^[0-9]{9}$/',
            'dni' => 'nullable|regex:/^([a-zA-Z0-9])[0-9]{7}([a-zA-Z0-9])$/|unique:contactos,dni', // Exclude current client
        ];

        // Validate the input data
        $validatedData = $request->validate($rules);

        // Create a new client using validated data
        $contacto = Contacto::create($validatedData);

        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'CREAR CONTACTO  : ' . $contacto['nombre'];
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return redirect()->route('contactos.index')->with('success', 'Contacto creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        // Find the client by ID
        $contacto = Contacto::findOrFail($id);

        // Define validation rules
        $rules = [
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|unique:contactos,email,' . $contacto->id, // Exclude current client
            'telefono_mobile' => 'nullable|regex:/^[0-9]{9}$/',
            'dni' => 'nullable|regex:/^([a-zA-Z0-9])[0-9]{7}([a-zA-Z0-9])$/|unique:contactos,dni,' . $contacto->id, // Exclude current client
        ];

        // Validate the input data
        $validatedData = $request->validate($rules);

        // Update the client with validated data
        $contacto->update($validatedData);

        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ACTUALIZAR CONTACTO: ' . $contacto['nombre'];
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return redirect()->route('contactos.index')->with('success', 'Contacto actualizado exitosamente.');
    }

    public function destroy(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);

        $this->admin_rol();
        $contacto = Contacto::findOrFail($id);

        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ELIMINAR CONTACTO ' . $contacto->dni;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();


        $contacto->delete();
        return redirect()->route('contactos.index');
    }
}
