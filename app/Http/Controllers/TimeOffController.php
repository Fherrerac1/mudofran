<?php

namespace App\Http\Controllers;

use App\Mail\TimeOffEmail;
use App\Models\Notificaciones;
use App\Models\TimeOff;
use App\Models\User;
use App\Models\Viewers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class TimeOffController extends Controller
{
    // List all requests (optionally filtered)
    public function index()
    {
        $user = Auth::user();

        $timeOffs = TimeOff::with(['user', 'acceptedBy'])->latest()->get();
        $users = User::where('rol', '!=', 'admin')->get();

        return Inertia::render('Cruds/RRHH/TimeOff/Index', [
            'user' => $user,
            'users' => $users,
            'timeOffs' => $timeOffs,
        ]);
    }

    // Store a new request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|date|before_or_equal:to',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $timeOff = TimeOff::create([
            'user_id' => Auth::id(),
            'from' => $validated['from'],
            'to' => $validated['to'],
            'estado' => 0, // Pendiente
        ]);

        // Create notification for admin or HR
        $notificacion = new Notificaciones();
        $notificacion->title = 'Nueva solicitud de permiso';
        $notificacion->tipo = 3; // Tipo para permisos / time off
        $notificacion->post_id = $timeOff->id;
        $notificacion->content = 'El usuario ' . Auth::user()->name .
            ' ha solicitado un permiso del ' .
            \Carbon\Carbon::parse($validated['from'])->format('d/m/Y') .
            ' al ' . \Carbon\Carbon::parse($validated['to'])->format('d/m/Y') . '.';

        // You can assign this to a specific user (e.g., admin) or leave null if it's general
        // $notificacion->user_id = AdminUser::first()->id; // Example: send to admin
        $notificacion->user_id = null; // General notification (broadcast)

        $notificacion->save();

        return redirect()->back()->with('success', 'Solicitud creada con éxito.');
    }

    // Approve or reject a request
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:1,2,3', // 1 = aprobado, 2 = rechazado, 3 = cancelado
        ]);

        $timeOff = TimeOff::findOrFail($id);

        if ($validated['estado'] == TimeOff::ESTADO_CANCELADO && $timeOff->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado para cancelar esta solicitud'], 403);
        }

        $timeOff->estado = $validated['estado'];
        $timeOff->accepted_by = $validated['estado'] == 1 ? Auth::id() : null;
        $timeOff->save();

        // Delete old notifications for this request
        Notificaciones::where('post_id', $timeOff->id)
            ->where('tipo', 3)
            ->delete();

        // Create new notification and send email if estado is aprobado or rechazado
        if (in_array($validated['estado'], [TimeOff::ESTADO_APROBADO, TimeOff::ESTADO_RECHAZADO])) {
            $userMail = User::find($timeOff->user_id);

            if ($userMail) {
                // Send email
                Mail::to($userMail->email)->send(new TimeOffEmail($timeOff, $userMail));

                // Create notification
                $estadoLabel = $timeOff->estado == 1 ? 'aprobada' : 'rechazada';

                $notificacion = new Notificaciones();
                $notificacion->title = 'Tu solicitud de permiso ha sido ' . $estadoLabel;
                $notificacion->tipo = 3; // Tipo: permisos
                $notificacion->post_id = $timeOff->id;
                $notificacion->content = 'Tu solicitud del ' .
                    \Carbon\Carbon::parse($timeOff->from)->format('d/m/Y') . ' al ' .
                    \Carbon\Carbon::parse($timeOff->to)->format('d/m/Y') .
                    ' ha sido ' . strtoupper($estadoLabel) . '.';

                $notificacion->user_id = $timeOff->user_id;
                $notificacion->save();
            }
        } elseif ($validated['estado'] == TimeOff::ESTADO_CANCELADO) {
            // Optional: create a notification if canceled
            $notificacion = new Notificaciones();
            $notificacion->title = 'Tu solicitud de permiso ha sido cancelada';
            $notificacion->tipo = 3;
            $notificacion->post_id = $timeOff->id;
            $notificacion->content = 'Tu solicitud del ' .
                \Carbon\Carbon::parse($timeOff->from)->format('d/m/Y') . ' al ' .
                \Carbon\Carbon::parse($timeOff->to)->format('d/m/Y') .
                ' ha sido CANCELADA.';
            $notificacion->user_id = $timeOff->user_id;
            $notificacion->save();
        }

        return redirect()->back()->with('success', 'Solicitud actualizada con éxito.');
    }


    public function show($id)
    {
        $user = Auth::user();
        $timeOff = TimeOff::with(['user', 'acceptedBy'])->findOrFail($id);
        $notification = Notificaciones::where('post_id', $id)->where('tipo', 3)->first();
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
        return Inertia::render('Cruds/RRHH/TimeOff/Show', [
            'user' => Auth::user(),
            'timeOff' => $timeOff,
        ]);
    }


    // Delete a request (optional)
    public function destroy($id)
    {
        $timeOff = TimeOff::findOrFail($id);

        // Delete notifications related to this TimeOff
        Notificaciones::where('post_id', $timeOff->id)
            ->where('tipo', 3) // Assuming tipo 3 = time off notifications
            ->delete();

        $timeOff->delete();

        return response()->json(['message' => 'Request and related notifications deleted']);
    }

}
