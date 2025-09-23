<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\Productos;
use App\Models\Servicios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ServiciosExport;



class ServiciosController extends Controller
{


    public function index()
    {
        // dd($request->all());
        $user = Auth::user();

        $servicios = Servicios::all()->load('productos');
        $productos = Productos::all()->load('servicio');

        return Inertia::render('Cruds/Servicios/Index', [
            'user' => $user,
            'servicios' => $servicios,
            'productos' => $productos,
        ]);
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required',
            'observaciones' => 'required|string',
        ]);
        $servicio = Servicios::create($request->all());

        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'CREAR Servicio ' . $servicio->nombre;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return redirect()->route('servicios.index');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required',
            'observaciones' => 'required|string',
        ]);

        // Buscar el servicio existente por ID
        $servicio = Servicios::findOrFail($id);

        // Actualizar los datos del servicio
        $servicio->update($request->all());

        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ACTUALIZAR Servicio ' . $servicio->nombre;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return redirect()->route('servicios.index');
    }
    public function getServiciosData($id)
    {
        $servicio = Servicios::findOrFail($id);
        return response()->json($servicio);
    }

    public function getAllServicios(Request $request)
    {
        $term = $request->input('q');
        $servicios = Servicios::where('nombre', 'LIKE', $term . '%')->get();
        return response()->json($servicios);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        // Find the service (Servicio) by ID
        $servicio = Servicios::findOrFail($id);

        // Get all related Productos
        $productos = $servicio->productos;

        // Delete associated documents for each Producto
        foreach ($productos as $producto) {
            if ($producto->documents) {
                $documents = $producto->documents;
                foreach ($documents as $documentPath) {
                    Storage::delete($documentPath); // Delete each document file
                }
            }
            // Delete the Producto record
            $producto->delete();
        }

        // Delete the Servicio (service) itself
        $servicio->delete();

        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ELIMINAR servicio ' . $servicio->nombre;
        $log->ip_address = request()->ip();
        $log->user_agent = request()->header('User-Agent');
        $log->save();

        return redirect()->route('servicios.index');
    }


    /**
     * Genera un informe de los servicios seleccionados en el formato especificado.
     */
    public function reportServicios(Request $request)
    {
        // Coge los IDs de los servicios seleccionados
        $servicios = Servicios::whereIn('id', $request->itemsIds)->get();

        // Verifica si se encontraron servicios
        if ($servicios->isEmpty()) {
            return response()->json(['message' => 'No se encontraron servicios'], 404);
        }

        $format = $request->input('format');

        if ($format === 'excel') {
            return Excel::download(new ServiciosExport($servicios), "informe_servicios.xlsx");
        }
        elseif ($format === 'pdf') {
            $pdf = PDF::loadView('Documents.reporte-servicios', ['servicios' => $servicios]);
            return $pdf->download("informe_servicios.pdf");
        }

        return response()->json(['message' => 'Formato no válido'], 400);
    }
}
