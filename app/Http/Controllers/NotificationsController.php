<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificaciones;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationsController extends Controller
{
    public function index()
    {
        // Getting the authenticated user
        $user = Auth::user();

        // Fetch all notifications
        $notificaciones = Notificaciones::all();

        // Fetch the latest 2 notifications
        $ultimos_notificaciones = Notificaciones::latest()->take(2)->get();

        // Return the view with the necessary data

        return Inertia::render('Cruds/Notifications/Index', [
            'notificaciones' => $notificaciones,
            'ultimos_notificaciones' => $ultimos_notificaciones,
            'user' => $user,
        ]);
    }

    public function liveNotificaciones()
    {
        $user = Auth::user();
        $lastMonth = Carbon::now()->subMonth();

        // Base query for notifications in last month
        $query = Notificaciones::where('updated_at', '>=', $lastMonth)
            ->whereDoesntHave('viewers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        if (in_array($user->rol, ['admin', 'gestor'])) {
            // Admins and gestores see notifications without a user_id (general notifications)
            // plus those assigned specifically to them
            $query->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            });
        } else {
            // Other users only see notifications assigned specifically to them
            $query->where('user_id', $user->id);
        }

        return $query->get();
    }


    public function store(Request $request)
    {
        // Validate and create a new notification
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            // Add other fields validation as needed
        ]);

        Notificaciones::create($validatedData);
        // Redirect to the notifications index page
        return redirect()->route('Notificaciones.index')->with('success', 'Notificación creada con éxito.');
    }

    public function updateToken(Request $request)
    {
        // Update the user's FCM token
        $request->user()->update(['fcm_token' => $request->token]);

        return response()->json(['success' => true]);

    }

    public function destroy($id)
    {
        // Find the notification by ID and delete it
        $notificacion = Notificaciones::findOrFail($id);
        $notificacion->delete();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Notificación eliminada con éxito.');
    }
}
