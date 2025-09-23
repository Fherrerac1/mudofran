<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeRecord;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FichajesExport;
use App\Models\AccessLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Models\Notificaciones;
use Illuminate\Support\Str;
use App\Models\Viewers;


use Inertia\Inertia;

class TimeController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::user()->id);

        $recordHoy = TimeRecord::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();


        $users = User::whereNotIn('rol', ['admin', 'super'])
            ->where('blocked', 0)
            ->get();

        $puestos = User::puestosDisponibles();
        $estados = TimeRecord::getEstados();


        if ($user->rol === 'admin' || $user->rol === 'gestor') {
            $horarios = TimeRecord::orderBy('created_at', 'desc')
                ->get()
                ->load('user');

            return Inertia::render('Cruds/ControlHorario/Index', [
                'user' => $user,
                'horarios' => $horarios,
                'users' => $users,
                'puestos' => $puestos,
                'estados' => $estados,
            ]);
        }

        $horarios = TimeRecord::whereHas('user', function ($query) use ($user) {
            $query;
        })->orderBy('created_at', 'desc')->get()->load('user');

        return Inertia::render('Cruds/ControlHorario/Index', [
            'user' => $user,
            'horarios' => $horarios,
            'users' => $users,
            'puestos' => $puestos,
            'estados' => $estados,
            'observacionHoy' => $recordHoy?->observaciones ?? null,

        ]);
    }

    public function fichar(Request $request)
    {
        // Get the current authenticated user's ID
        $userId = Auth::id();

        // Get the current time in the required format
        $currentTime = Carbon::now()->format('H:i:s');

        // Check if there is an active time record (without a time_out) for the user
        $activeRecord = TimeRecord::where('user_id', $userId)
            ->whereNull('time_out')
            ->where('estado', 1)
            ->first();



        if ($activeRecord) {
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            // Mark the current time as time_out and update the estado
            $activeRecord->time_out = $currentTime;
            $activeRecord->latitude_out = $latitude;
            $activeRecord->longitude_out = $longitude;
            $activeRecord->estado = 0; // Mark as inactive (finished)
            $activeRecord->save();


            return response()->json([
                'message' => 'SALIDA FICHADA',
                'time_out' => $activeRecord->time_out,
            ]);
        } else {
            // Optionally check for GPS coordinates if required for clock-in
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            if (!$latitude || !$longitude) {
                return response()->json([
                    'message' => 'Se requiere la ubicación para fichar la entrada.',
                ], 400);
            }

            // Create a new record with time_in set to the current time
            TimeRecord::create([
                'user_id' => $userId,
                'time_in' => $currentTime,
                'estado' => 1, // Mark as active
                'latitude' => $latitude,  // Store the GPS latitude (if required)
                'longitude' => $longitude, // Store the GPS longitude (if required)
            ]);

            return response()->json([
                'message' => 'ENTRADA FICHADA',
                'time_in' => $currentTime,
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);

        $timeRecord = TimeRecord::find($id);

        if (!$timeRecord) {
            return response()->json([
                'message' => 'Registro no encontrado.',
            ], 404);
        }

        $timeRecord->update($request->all());

        // Log the update action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ACTUALIZAR Horario ' . $timeRecord->id;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return response()->json([
            'message' => 'Registro actualizado con éxito.',
        ]);
    }

    public function approve(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);

        $timeRecord = TimeRecord::find($id);

        if (!$timeRecord) {
            return response()->json([
                'message' => 'Registro no encontrado.',
            ], 404);
        }

        $timeRecord->estado = 2;
        $timeRecord->save();

        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'Aprobar Horario ' . $timeRecord->id;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return response()->json([
            'message' => 'Registro Aprobado con éxito.',
        ]);
    }

    public function acceptAll(Request $request)
    {
        $user = User::find(Auth::user()->id);

        foreach ($request->ids as $id) {

            $timeRecord = TimeRecord::find($id);

            if (!$timeRecord) {
                return response()->json([
                    'message' => 'Registro no encontrado.',
                ], 404);
            }

            $timeRecord->estado = 2;
            $timeRecord->save();

            $log = new AccessLog();
            $log->user_id = $user->id;
            $log->email = $user->email;
            $log->action = 'Aprobar Horario ' . $timeRecord->id;
            $log->ip_address = $request->ip();
            $log->user_agent = $request->header('User-Agent');
            $log->save();
        }

        return response()->json([
            'message' => 'Registro Aprobado con éxito.',
        ]);
    }

    public function destroy($id)
    {
        $timeRecord = TimeRecord::find($id);

        if (!$timeRecord) {
            return response()->json([
                'message' => 'Registro no encontrado.',
            ], 404);
        }

        $timeRecord->delete();

        return response()->json([
            'message' => 'Registro eliminado con éxito.',
        ]);
    }

    public function getStatus()
    {
        $user = Auth::user();

        $record = TimeRecord::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $hasFinalizadoHoy = $record && $record->time_in !== null && $record->time_out !== null;

        return response()->json([
            'record' => $record && $record->estado === 1 && $record->time_out === null ? 1 : 0,
            'pause_started_at' => $record?->pause_started_at,
            'pause_total_seconds' => $record?->pause_total_seconds ?? 0,
            'meal_started_at' => $record?->meal_started_at,
            'meal_total_seconds' => $record?->meal_total_seconds ?? 0,
            'time_in' => $record?->time_in,
            'time_out' => $record?->time_out,
            'elapsed_seconds' => $record ? now()->diffInSeconds(Carbon::parse($record->time_in)) - ($record->meal_total_seconds ?? 0) : 0,
            'hasFinalizadoHoy' => $hasFinalizadoHoy,
            'observaciones' => $record?->observaciones ? json_decode($record->observaciones, true) : null,

        ]);
    }

    public function live_fichaje()
    {
        try {
            // Obtener el ID del usuario autenticado
            $user = User::findOrFail(Auth::user()->id);

            // Obtener el último registro de tiempo del usuario para el día actual
            $latestRecord = TimeRecord::where('user_id', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->where('estado', 1)
                ->latest('created_at')
                ->first();

            if (!$latestRecord) {
                return response()->json(['message' => 'No hay registros de fichaje para hoy.'], 404);
            }

            // Si aún no se ha registrado la salida, usar el tiempo actual
            $endTime = Carbon::now();

            $totalSeconds = abs($endTime->diffInSeconds(Carbon::parse($latestRecord->time_in)));
            $hours = intdiv($totalSeconds, 3600);
            $minutes = intdiv($totalSeconds % 3600, 60);
            $seconds = $totalSeconds % 60;

            return response()->json([
                'current' => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
            ]);
        } catch (\Exception $e) {
            // Manejar errores
            return response()->json(['message' => 'Ocurrió un error inesperado.'], 500);
        }
    }


    /**
     * Exporta los horarios seleccionados en la solicitud
     */
    public function descargaFichajes(Request $request)
    {
        // Obtenemos los horarios seleccionados buscando por sus IDs en la base de datos
        $horarios = TimeRecord::whereIn('id', $request->horarios)->get();

        // Export based on the requested format
        $format = $request->input('format');

        if ($format === 'excel') {
            return Excel::download(new FichajesExport($horarios), "Control Horario.xlsx");
        } elseif ($format === 'pdf') {
            $pdf = PDF::loadView('Documents.fichajesPdf', ['horarios' => $horarios])->setPaper('a4', 'landscape');
            $fileName = 'Control Horario.pdf';
            return $pdf->download($fileName);
        }

        return response()->json(['message' => 'Formato no válido'], 400);
    }

    public function togglePause(Request $request)
    {
        $user = Auth::user();

        $record = TimeRecord::where('user_id', $user->id)
            ->whereNull('time_out')
            ->where('estado', 1)
            ->latest()
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'No se encontró un fichaje activo.',
            ], 404);
        }

        if ($record->pause_started_at) {
            $pausedSeconds = Carbon::parse($record->pause_started_at)->diffInSeconds(now());

            $record->pause_total_seconds = ($record->pause_total_seconds ?? 0) + $pausedSeconds;
            $record->pause_started_at = null;
            $record->save();

            return response()->json([
                'message' => 'Reanudado',
                'pause_total' => gmdate('H:i:s', $record->pause_total_seconds),
            ]);
        } else {
            $record->pause_started_at = now();
            $record->save();

            return response()->json([
                'message' => 'Pausado',
                'pause_started_at' => $record->pause_started_at->toDateTimeString(),
            ]);
        }
    }

    public function toggleMeal(Request $request)
    {
        $user = Auth::user();

        $record = TimeRecord::where('user_id', $user->id)
            ->whereNull('time_out')
            ->where('estado', 1)
            ->latest()
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'No se encontró un fichaje activo.',
            ], 404);
        }

        // Evitar más de una comida por jornada
        if ($record->meal_total_seconds > 0) {
            return response()->json([
                'message' => 'Comida ya realizada',
            ], 200);
        }

        if ($record->meal_started_at) {
            // Terminar comida (pero conservar el timestamp)
            $mealSeconds = Carbon::parse($record->meal_started_at)->diffInSeconds(now());
            $record->meal_total_seconds = ($record->meal_total_seconds ?? 0) + $mealSeconds;
            // NO borrar meal_started_at
            $record->save();

            return response()->json([
                'message' => 'Reanudado',
                'meal_total' => gmdate('H:i:s', $record->meal_total_seconds),
            ]);
        } else {
            // Iniciar comida
            $record->meal_started_at = now();
            $record->save();

            return response()->json([
                'message' => 'Comida iniciada',
                'meal_started_at' => $record->meal_started_at->toDateTimeString(),
            ]);
        }
    }

    public function updateMeal(Request $request, $id)
    {
        $horario = TimeRecord::findOrFail($id);

        $request->validate([
            'meal_started_at' => 'required|date',
            'meal_total_seconds' => 'required|integer|min:0',
        ]);

        $horario->meal_started_at = $request->meal_started_at;
        $horario->meal_total_seconds = $request->meal_total_seconds;

        $horario->save();

        return response()->json(['message' => 'Comida actualizada correctamente.']);
    }

    public function deleteMeal($id)
    {
        $horario = TimeRecord::findOrFail($id);

        $horario->meal_started_at = null;
        $horario->meal_total_seconds = 0;

        $horario->save();

        return response()->json(['message' => 'Comida eliminada correctamente.']);
    }

    public function finalizarJornada($id)
    {
        $horario = TimeRecord::findOrFail($id);

        if ($horario->time_out) {
            return response()->json([
                'message' => 'La jornada ya está finalizada.'
            ], 400);
        }

        $horario->time_out = now()->format('H:i:s');

        $horario->estado = 0;

        $horario->save();

        return response()->json([
            'message' => 'Jornada finalizada correctamente.',
            'time_in' => $horario->time_in,
            'time_out' => $horario->time_out,
            'estado' => $horario->estado
        ]);
    }

    public function guardarObservacion(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000', // límite duro
        ]);

        $user = Auth::user();

        $record = TimeRecord::where('user_id', $user->id)
            ->whereNull('time_out')
            ->where('estado', 1)
            ->latest()
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'No tienes una jornada activa para añadir/editar observaciones.'
            ], 400);
        }

        $yaExiste = !empty($record->observaciones);


        // UNA observación por jornada (día): guardamos un único objeto JSON
        $record->observaciones = json_encode([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha' => now()->toDateTimeString(),
        ]);



        $record->save();

        $descripcionCorta = Str::limit($request->descripcion, 40);
        $accion = $yaExiste ? 'modificó' : 'creó';


        // Definir destinatario (admin del mismo  o primer admin)
        $destinatario = User::where('rol', 'admin')
            ->first();

        if ($destinatario) {
            // Eliminar notificación antigua del mismo post_id y tipo
            Notificaciones::where('post_id', $record->id)
                ->where('tipo', 4)
                ->where('user_id', $destinatario->id)
                ->delete();

            // Crear la nueva notificación
            $notificacion = new Notificaciones();
            $notificacion->title = 'Observación';
            $notificacion->tipo = 4;
            $notificacion->post_id = $record->id;
            $notificacion->content = "{$user->name} {$accion} una observación: \"{$descripcionCorta}\"";
            $notificacion->user_id = $destinatario->id;
            $notificacion->save();
        }


        return response()->json([
            'message' => 'Observación guardada correctamente.',
            'observacion' => json_decode($record->observaciones, true),
        ]);
    }


    public function mostrarObservacion($id)
    {
        $user = Auth::user();

        // Buscar el TimeRecord con relaciones necesarias
        $record = TimeRecord::with('user')->find($id);

        if (!$record || !$record->observaciones) {
            return redirect()->route('clock.index')
                ->with('error', 'No hay observación asociada a este fichaje.');
        }

        // Buscar la notificación relacionada a este TimeRecord (tipo 4 = observación)
        $notification = Notificaciones::where('post_id', $id)
            ->where('tipo', 4)
            ->first();

        if ($notification) {
            $alreadyViewed = Viewers::where('user_id', $user->id)
                ->where('notification_id', $notification->id)
                ->exists();

            if (!$alreadyViewed) {
                Viewers::create([
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                ]);
            }
        }

        return Inertia::render('Cruds/Notifications/MostrarObservacion', [
            'user' => $user,
            'record' => $record,
            'observacion' => json_decode($record->observaciones, true),
        ]);
    }
}
