<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AccessLogsController extends Controller
{
    public function index()
    {
        $this->admin_rol();
        $user = Auth::user();

        $activities = AccessLog::orderBy('created_at', 'desc')->get();


        return Inertia::render('Cruds/Actividades/Index', [
            'user' => $user,
            'activities' => $activities,
        ]);
    }


    // Function to delete all access logs
    public function destroy()
    {
        // Ensure the user is authenticated and has the admin role
        $this->admin_rol();

        try {
            // Delete all access logs
            AccessLog::truncate();

            // Optionally, return success message
            return redirect()->back()->with('success', 'Todos los registros de acceso han sido eliminados correctamente.');

        } catch (\Exception $e) {
            // Optionally, return error message if something goes wrong
            return redirect()->back()->with('error', 'No se pudieron eliminar los registros de acceso.');
        }
    }
}
