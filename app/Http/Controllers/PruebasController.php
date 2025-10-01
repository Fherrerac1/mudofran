<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PruebasController extends Controller
{
    /**
     * Muestra la vista de pruebas
     */
    public function index()
    {
        return view('pruebas');
    }

    /**
     * Procesa la importación del archivo
     */
    public function importar(Request $request)
    {
        $request->validate([
            'formato' => 'required|string',
            'archivo' => 'required|file|max:10240', // máximo 10MB
        ]);

        $formato = $request->input('formato');
        $archivo = $request->file('archivo');

        // Aquí puedes procesar el archivo según el formato
        // Por ejemplo:
        // - Excel: usar PhpSpreadsheet o Laravel Excel
        // - CSV: usar fgetcsv() o League\Csv
        // - JSON: usar json_decode()

        return response()->json([
            'success' => true,
            'message' => 'Archivo importado correctamente',
            'formato' => $formato,
            'nombre_archivo' => $archivo->getClientOriginalName(),
        ]);
    }
}