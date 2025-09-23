<?php

namespace App\Http\Controllers;

use App\Models\Baja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BajaController extends Controller
{
    public function index()
    {
        $bajas = Baja::with('user')->latest()->get();
        return Inertia::render('Bajas/Index', [
            'bajas' => $bajas,
        ]);
    }

    public function create()
    {
        return Inertia::render('Bajas/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_inicio' => 'required|date',
            'user_id' => 'required|integer',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'motivo' => 'required|string|max:255',
            'documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
        ]);

        if ($request->hasFile('documento')) {
            $data['documento'] = $request->file('documento')->store('bajas');
        }

        Baja::create($data);

        return redirect()->route('bajas.index')->with('success', 'Baja enviada correctamente.');
    }

    public function destroy(Baja $baja)
    {
        if ($baja->documento) {
            Storage::delete($baja->documento);
        }

        $baja->delete();

        return redirect()->back()->with('success', 'Baja eliminada.');
    }
}
