<?php

namespace App\Http\Controllers;


use App\Models\Clientes;
use App\Models\Facturas;

use App\Models\Metodos_Pago;
use App\Models\Presupuestos;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;


class BorradoresController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::user()->id);

        $this->admin_rol();

        $facturas = Facturas::with('presupuesto', 'cliente')
            ->where('serie', 11)
            ->orderBy('fechaInicio', 'desc')
            ->get();

        $clientes = $facturas->pluck('cliente')->filter()->unique('id')->values();

        return Inertia::render('Cruds/BorradoresFacturas/Index', [
            'user' => $user,
            'facturas' => $facturas,
            'clientes' => $clientes,
        ]);

    }

    public function create()
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        $metodos = Metodos_Pago::all();
        $facturasController = new FacturasController();
        $facturaNumber = $facturasController->generateFacturaNumber(11);

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::where('estado', 2)->get();

        return Inertia::render('Cruds/BorradoresFacturas/Create', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'metodos' => $metodos,
            'numFactura' => $facturaNumber
        ]);
    }

    public function edit($id)
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        $factura = Facturas::findOrFail($id);
        $metodos = Metodos_Pago::all();

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::where('estado', 2)
            ->orWhere('id', $factura->presupuesto_id)
            ->get();

        return Inertia::render('Cruds/BorradoresFacturas/Update', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'metodos' => $metodos,
            'factura' => $factura,
        ]);
    }

    public function show($id)
    {
        $user = User::find(Auth::user()->id);

        $this->admin_rol();
        $factura = Facturas::findOrFail($id)->load('cliente', 'presupuesto', 'cobros', 'nativa');

        // Generate and save hash if it's null
        if (!$factura->hash) {
            $factura->hash = md5(json_encode($factura->toArray()));
            $factura->save();
        }

        // Delete old PDF if it exists
        if ($factura->pdf) {
            Storage::delete('public/' . $factura->pdf);
        }
        if (!$factura->qr_code) {
            $verifactuController = new VerifactuController();
            // $verifactuController->sendInvoice($factura);
        }

        $data = ['factura' => $factura];

        $pdfContent = Pdf::loadView('Documents.facturaPDF', $data)->output();


        if (($factura->totalCobros() >= $factura->total && $factura->estado == 5)) {
            $factura->estado = 2; // Mark as fully paid
        }
        // Update the factura's PDF path
        $factura->save();


        return Inertia::render('Cruds/BorradoresFacturas/Show', [
            'user' => $user,
            'factura' => $factura,
            'pdfContent' => base64_encode($pdfContent),
        ]);
    }

    public function generate_presupuesto($id)
    {
        $user = User::find(Auth::user()->id);

        $facturasController = new FacturasController();
        $numFactura = $facturasController->generateFacturaNumber(11);

        $presupuesto = Presupuestos::find($id);
        $metodos = Metodos_Pago::all();

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::where('estado', 2)->get();


        return Inertia::render('Cruds/BorradoresFacturas/GeneratePresupuesto', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'metodos' => $metodos,
            'presupuesto' => $presupuesto,
            'numFactura' => $numFactura
        ]);
    }

    public function generate($id)
    {
        $user = User::find(Auth::user()->id);
        $facturasController = new FacturasController();
        $numFactura = $facturasController->generateFacturaNumber(1);

        $factura = Facturas::findOrFail($id);
        $factura->estado = 0;
        $factura->serie = 1;
        $factura->presupuesto_id = null;

        $metodos = Metodos_Pago::all();

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::where('estado', 2)->get();


        return Inertia::render('Cruds/BorradoresFacturas/Generate', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'metodos' => $metodos,
            'factura' => $factura,
            'numFactura' => $numFactura
        ]);
    }
}
