<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\UsuariosImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ExcelImportController extends Controller
{
    // Mostrar el formulario
    public function showForm()
    {
        return view('import');
    }

    // Procesar el Excel
    public function import(Request $request)
    {
        // Validar que sea un Excel. Formatos permitidos
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            // Crear instancia de la importación
            $import = new UsersImport();

            // Importar el archivo Excel
            Excel::import($import, $request->file('file'));

            // Si es correcto
            return back()->with('success', 'Importado correctamente.');

        } catch (ValidationException $e) {
            // Captura los errores de validación
            $failures = $e->failures();

            // Preparar mensajes de error
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = 'Fila ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }

            // Volver con los errores al formulario
            return back()->withErrors($errorMessages);
        } catch (\Exception $e) {
            // Otros errores 
            return back()->withErrors(['Error al procesar archivo: ' . $e->getMessage()]);
        }
    }
}