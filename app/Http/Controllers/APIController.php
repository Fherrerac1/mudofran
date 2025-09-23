<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Illuminate\Http\Request;

class APIController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function clienteFacturas($api_key)
    {
        $cliente = Clientes::where('api_key', $api_key)
            ->with(['facturas'])
            ->first();

        if (!$cliente) {
            return response()->json(['message' => 'cliente not found'], 404);
        }

        return response()->json($cliente->facturas, 200);
    }
}
