<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\Nomina;
use App\Models\Notificaciones;
use App\Models\User;
use App\Models\Viewers;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NominaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $users = User::where('rol', '!=', 'admin')->get();

        if ($user->rol === 'admin') {
            // Admin-specific code
            $nominas = Nomina::with('user')
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->get();
        } else {
            $nominas = Nomina::with('user')
                ->where('user_id', $user->id)
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->get();
        }


        $datosUsuarioNomina = [];

        $datosUsuarioNomina = Nomina::with('user')
            ->get()
            ->pluck('user')
            ->filter(fn($u) => $u && $u->dni && $u->name)
            ->unique('id')
            ->map(fn($u) => [
                'value' => $u->id,
                'text' => $u->name,
                'dni' => $u->dni,
            ])
            ->values();

        return Inertia::render('Cruds/RRHH/Nominas/Index', [
            'user' => $user,
            'users' => $users,
            'nominas' => $nominas,
            'datosUsuarioNomina' => $datosUsuarioNomina,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'mes' => 'required|string',
            'anio' => 'required|integer',
            'archivo' => 'required|file|mimes:pdf|max:2048',
        ]);

        $tenantId = session('tenant_id'); // current tenant
        $userId = $request->user_id;
        $year = $request->anio;

        // Store the file in tenant-specific folder: tenants/{tenant_id}/nominas/user_{user_id}/{year}
        $path = Storage::disk('public')->putFile(
            "tenants{$tenantId}/nominas/user_{$userId}/{$year}",
            $request->file('archivo')
        );

        // Update or create the payroll (nomina)
        $nomina = Nomina::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'mes' => $request->mes,
                'anio' => $request->anio,
            ],
            [
                'archivo' => $path,
            ]
        );

        // Create a notification for the user about the uploaded payroll
        $notificacion = new Notificaciones();
        $notificacion->title = 'Nómina subida';
        $notificacion->tipo = 1; // nomina
        $notificacion->post_id = $nomina->id;
        $notificacion->content = "Se ha subido la nómina correspondiente a {$request->mes}/{$request->anio}.";
        $notificacion->user_id = $request->user_id;
        $notificacion->save();

        // Log the update action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'CREAR Nómina ' . $nomina->id;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return redirect()->back()->with('success', 'Nómina subida correctamente.');
    }

    public function show($id)
    {
        $user = Auth::user();
        // Load related user if not already loaded
        $nomina = Nomina::find($id)->load('user');
        $notification = Notificaciones::where('post_id', $id)->where('tipo', 1)->first();
        if ($notification) {
            // Check if the viewer entry already exists for the user and notification
            $existingViewer = Viewers::where('user_id', $user->id)
                ->where('notification_id', $notification->id)
                ->exists();
            if (!$existingViewer) {
                $viewer = new Viewers();
                $viewer->user_id = $user->id;
                $viewer->notification_id = $notification->id;
                $viewer->save();
            }
        }
        // Example returning Inertia view:
        return Inertia::render('Cruds/RRHH/Nominas/Show', [
            'nomina' => $nomina,
            'user' => $user,
        ]);
    }

    public function destroy(Request $request, Nomina $nomina)
    {
        $user = Auth::user();
        // Delete the file if it exists
        if ($nomina->archivo && Storage::disk('public')->exists($nomina->archivo)) {
            Storage::disk('public')->delete($nomina->archivo);
        }

        // Delete notifications related to this nomina
        Notificaciones::where('post_id', $nomina->id)
            ->where('tipo', 1) // only notifications of type nomina
            ->delete();

        // Log the update action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ELIMINAR Nómina ' . $nomina->id;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        // Delete the nomina record
        $nomina->delete();

        return redirect()->back()->with('success', 'Nómina eliminada correctamente.');
    }

}
