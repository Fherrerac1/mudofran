<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentoController extends Controller
{
    public function index()
    {
        $documentos = Documento::with('user')->latest()->get();
        $user = Auth::user();
        $users = User::where('rol', '!=', 'admin')->get();

        return Inertia::render('Cruds/RRHH/Documentos/Index', [
            'documentos' => $documentos,
            'user' => $user,
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'titulo' => 'required|string|max:255',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $path = $request->file('archivo')->store('documentos', 'public');

        Documento::create([
            'user_id' => $request->user_id,
            'titulo' => $request->titulo,
            'archivo' => $path
        ]);

        return redirect()->back()->with('success', 'Documento subido correctamente');
    }

    public function destroy(Documento $documento)
    {
        Storage::delete($documento->archivo);
        $documento->delete();

        return redirect()->back()->with('success', 'Documento eliminado');
    }
}
