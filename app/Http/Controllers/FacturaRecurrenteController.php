<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FacturaRecurrente;
use App\Models\Facturas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FacturaRecurrenteController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        $facturasRecurrentes = FacturaRecurrente::with('factura.cliente')
            ->orderBy('fechaInicio', 'desc')
            ->get();


        $nombresFacturas = $facturasRecurrentes
            ->pluck('factura.numFactura', 'id')
            ->filter()
            ->toArray();

        $nombresClientes = $facturasRecurrentes
            ->pluck('factura.cliente')
            ->filter()
            ->unique('id')
            ->mapWithKeys(fn($cliente) => [
                $cliente->id => "{$cliente->nombre} {$cliente->apellido_1} ({$cliente->dni})"
            ])
            ->toArray();

        $frecuencias = FacturaRecurrente::getFrecuencias();

        $facturas = Facturas::query()->whereIn('serie', [5, 1])->get();

        return Inertia::render('Cruds/Recurrentes/Index', [
            'user' => $user,
            'facturasRecurrentes' => $facturasRecurrentes,
            'nombresFacturas' => $nombresFacturas,
            'nombresClientes' => $nombresClientes,
            'frecuencias' => $frecuencias,
            'facturas' => $facturas,
        ]);
    }

    public function create(Request $request)
    {
        $user = User::find(Auth::user()->id);

        $factura = Facturas::find($request->id);

        if ($factura->facturas_recurrente) {
            $facturas_recurrente = FacturaRecurrente::where('factura_id', $factura->id)->first()->load('factura');
            return Inertia::render('Cruds/Recurrentes/Update', [
                'user' => $user,
                'facturas_recurrente' => $facturas_recurrente,
            ]);
        } else {
            return Inertia::render('Cruds/Recurrentes/Create', [
                'user' => $user,
                'factura' => $factura,
            ]);
        }

    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'factura_id' => 'required|exists:facturas,id|unique:facturas_recurrentes,factura_id',
            'frecuencia' => 'required|in:diaria,semanal,mensual,trimestral,semestral,anual',
            'fechaInicio' => 'required|date',
            'proxima_fecha' => 'required|date',
            'fechaFin' => 'nullable|date',
            'repeticiones' => 'nullable|integer|min:1',
        ]);

        // Create the recurring invoice record
        FacturaRecurrente::create([
            'factura_id' => $validated['factura_id'],
            'frecuencia' => $validated['frecuencia'],
            'fechaInicio' => $validated['fechaInicio'],
            'proxima_fecha' => $validated['proxima_fecha'],
            'fechaFin' => $validated['fechaFin'] ?? null,
            'repeticiones' => $validated['repeticiones'] ?? null,
            'repeticiones_actuales' => 0,
        ]);

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Recurrence saved successfully!');
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'factura_id' => 'required|exists:facturas,id|unique:facturas_recurrentes,factura_id,' . $id,
            'frecuencia' => 'required|in:diaria,semanal,mensual,trimestral,semestral,anual',
            'fechaInicio' => 'required|date',
            'proxima_fecha' => 'required|date',
            'fechaFin' => 'nullable|date',
            'repeticiones' => 'nullable|integer|min:1',
        ]);

        // Find the existing recurring invoice record
        $facturaRecurrente = FacturaRecurrente::findOrFail($id);

        // Update the record with the new values
        $facturaRecurrente->update([
            'factura_id' => $validated['factura_id'],
            'frecuencia' => $validated['frecuencia'],
            'fechaInicio' => $validated['fechaInicio'],
            'proxima_fecha' => $validated['proxima_fecha'],
            'fechaFin' => $validated['fechaFin'] ?? null,
            'repeticiones' => $validated['repeticiones'] ?? null,
            'repeticiones_actuales' => 0,
        ]);

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Recurrence updated successfully!');
    }

    public function destroy($id)
    {
        // Find the recurring invoice record
        $facturaRecurrente = FacturaRecurrente::findOrFail($id);

        // Delete the record
        $facturaRecurrente->delete();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Recurrence deleted successfully!');
    }

}
