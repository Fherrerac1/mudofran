<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlantillaMaestra\UpdatePlantillaMaestraRequest;
use App\Models\AccessLog;
use App\Models\PlantillaMaestra;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PlantillaMaestraController extends Controller
{
    public function storeDefault(Request $request)
    {
        try {
            $plantillas = [
                ['tipo' => 'Factura', 'letra' => 'F', 'serie' => 1],
                ['tipo' => 'Factura', 'letra' => 'P', 'serie' => 7],
                ['tipo' => 'Factura', 'letra' => 'R', 'serie' => 2],
                ['tipo' => 'Factura', 'letra' => 'F', 'serie' => 5],
                ['tipo' => 'Factura', 'letra' => 'F', 'serie' => 11],
            ];

            $ordenPorDefecto = ['letra', 'year', 'numeroSerieActivo', 'opcional', 'cantidad'];

            $count = 0;

            foreach ($plantillas as $p) {
                $exists = PlantillaMaestra::where('tipo', $p['tipo'])->where('serie', $p['serie'])->exists();

                if (!$exists) {
                    PlantillaMaestra::create([
                        'tipo' => $p['tipo'],
                        'letra' => $p['letra'],
                        'serie' => $p['serie'],
                        'year' => 'yy',
                        'cantidad' => 6,
                        'simbolo_1' => '-',
                        'simbolo_2' => '/',
                        'opcional' => 'C1',
                        'numeroSerieActivo' => true,
                        'orden' => $ordenPorDefecto,
                        'updated_by' => Auth::id(),
                    ]);
                    $count++;
                }
            }

            return response()->json([
                'message' => $count > 0
                    ? "Se han creado {$count} plantilla(s) por defecto."
                    : "Las plantillas ya existen. No se ha creado ninguna nueva.",
                'success' => true,
            ]);
        } catch (Exception $e) {
            Log::channel('plantillaError')->error('Error al crear plantillas por defecto: ', [
                'Usuario' => Auth::user()?->name,
                'Correo' => Auth::user()?->email,
                'Mensaje' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al crear las plantillas. Intente nuevamente o contacte al administrador.',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Actualiza o crea una plantilla maestra de tipo "Factura" con la serie proporcionada.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePlantillaMaestraRequest $request)
    {
        try {
            // Buscar la plantilla maestra del tipo "Factura" y con la serie proporcionada
            $plantilla = PlantillaMaestra::where('tipo', $request->tipo)
                ->where('serie', $request->serie)
                ->first();

            $esNueva = false;

            // Si no existe, crear una nueva plantilla
            if (!$plantilla) {
                $plantilla = new PlantillaMaestra();
                $plantilla->tipo = 'Factura';
                $plantilla->serie = $request->serie;
                $esNueva = true;
            }

            // Definir el tipo de mensaje según la serie
            $mensajeSerie = match ((int) $request->serie) {
                1 => 'Normal',
                11 => 'Borrador',
                2 => 'Rectificada',
                default => 'Desconocida',
            };

            // Asignar o actualizar los campos de la plantilla
            $plantilla->letra = $request->letra;
            $plantilla->year = $request->year;
            $plantilla->cantidad = $request->cantidad;
            $plantilla->simbolo_1 = $request->simbolo_1 ?? '';
            $plantilla->simbolo_2 = $request->simbolo_2 ?? '';
            $plantilla->opcional = empty($request->opcional) ? 'EN_BLANCO' : $request->opcional;
            $plantilla->numeroSerieActivo = (int) $request->numeroSerieActivo === 1;
            $plantilla->orden = $request->orden;
            $plantilla->updated_by = Auth::id();
            $plantilla->save();

            // Registrar la acción en el AccessLog
            $log = new AccessLog();
            $log->user_id = Auth::user()->id;
            $log->email = Auth::user()->email;
            $log->action = ($esNueva ? "Creado" : "Actualizado") . " Plantilla Maestra de tipo Factura con la serie '{$request->serie}'";
            $log->ip_address = $request->ip();
            $log->user_agent = $request->header('User-Agent');
            $log->save();

            // Devolver respuesta exitosa en formato JSON
            return response()->json([
                'message' => "Plantilla Maestra {$mensajeSerie} se ha " . ($esNueva ? 'creado' : 'actualizado') . " correctamente.",
                'success' => true,
            ]);
        } catch (Exception $e) {
            // En caso de error, registrar en el canal 'plantillaError'
            Log::channel('plantillaError')->error('Error al crear o modificar la plantilla: ', [
                'Usuario' => Auth::user()->name,
                'Rol' => Auth::user()->rol,
                'Correo' => Auth::user()->email,
                'Mensaje' => $e->getMessage(),
            ]);

            // Devolver respuesta de error en formato JSON
            return response()->json([
                'message' => 'Hubo un error al guardar la plantilla. Intente nuevamente o contacte al administrador.',
                'success' => false,
            ], 500);
        }
    }
}
