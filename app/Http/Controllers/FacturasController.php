<?php

namespace App\Http\Controllers;

use App\Exports\FacturasExport;
use App\Helpers\ConfigHelper;
use App\Mail\ResponseEmail;
use App\Models\Metodos_Pago;
use App\Models\Clientes;
use App\Models\Presupuestos;
use App\Models\Facturas;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AccessLog;
use App\Models\Cobro;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use josemmo\Facturae\Facturae;
use josemmo\Facturae\FacturaeParty;
use josemmo\Facturae\FacturaeFile;
use josemmo\Facturae\Face\Faceb2bClient;
use josemmo\Facturae\FacturaeItem;
use App\Helpers\CodigoDocumentoHelper;
use App\Mail\FacturaEmail;
use Illuminate\Validation\Rule;
use josemmo\Facturae\FacturaeCentre;

class FacturasController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::id());
        $this->admin_rol();

        $facturas = Facturas::with('presupuesto', 'cliente', 'cobros')
            ->where('serie', '!=', 11)
            ->orderBy('fechaInicio', 'desc')
            ->get();

        $clientes = $facturas->pluck('cliente')->filter()->unique('id')->values();


        return Inertia::render('Cruds/Facturas/Index', [
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

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::where('estado', 2)->get();

        $facturaNumber = $this->generateFacturaNumber(1);

        return Inertia::render('Cruds/Facturas/Create', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'metodos' => $metodos,
            'numFactura' => $facturaNumber
        ]);
    }

    public function generate($id)
    {
        $user = User::find(Auth::user()->id);
        $metodos = Metodos_Pago::all();
        $presupuesto = Presupuestos::findOrFail($id);
        $numFactura = $this->generateFacturaNumber(1);

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::where('estado', 2)->get();


        return Inertia::render('Cruds/Facturas/Generate', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'metodos' => $metodos,
            'presupuesto' => $presupuesto,
            'numFactura' => $numFactura
        ]);
    }


    public function duplicate($id)
    {
        $user = User::find(Auth::user()->id);

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::where('estado', [2])->get();
        $factura = Facturas::find($id);
        $factura->estado = 0;
        $factura->presupuesto_id = null;
        $factura->hash = null;
        $metodos = Metodos_Pago::all();
        $numFactura = $this->generateFacturaNumber(1);

        return Inertia::render('Cruds/Facturas/Duplicate', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'metodos' => $metodos,
            'factura' => $factura,
            'numFactura' => $numFactura
        ]);
    }

    public function rectificate($id)
    {
        $user = User::find(Auth::user()->id);

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::where('estado', [2])->get();
        $factura = Facturas::find($id);
        $factura->estado = 0;
        $factura->hash = null;
        $factura->presupuesto_id = null;
        $metodos = Metodos_Pago::all();

        $factura->serie = 2;

        $factura->factura_nativa = $factura->id;

        $numFactura = $this->generateFacturaNumber(2);

        return Inertia::render('Cruds/Facturas/Rectificate', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'metodos' => $metodos,
            'factura' => $factura,
            'numFactura' => $numFactura
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numFactura' => 'required|string|max:50',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
            'estado' => 'nullable|integer',
            'serie' => 'required|integer',
            'retencion' => 'nullable|integer',
            'iva' => 'required|numeric|min:0',
            'tiempo' => 'required|integer|min:0',
            'total_sin_iva' => 'required|numeric|min:0',
            'total_iva' => 'nullable|numeric|min:0',
            'total_irpf' => 'nullable|numeric|min:0',
            'irpf' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'porcentaje' => 'nullable|numeric|min:0|max:100',
            'concepto' => 'nullable|string|max:500',

            'presupuesto_id' => 'nullable|exists:presupuestos,id',
            'cliente_id' => [
                'nullable',
                'exists:clientes,id',
                Rule::requiredIf(function () use ($request) {
                    return $request->serie != 11;
                }),
            ],
            'factura_nativa' => 'nullable|exists:facturas,id',

            'observaciones' => 'nullable|string',
            'condiciones' => 'nullable|string',
        ]);

        $user = User::find(Auth::id());

        // Ensure the user has admin privileges
        $this->admin_rol();

        // Validate required fields
        if (empty($request->input('servicios'))) {
            return back()->withErrors([
                'servicios' => "No se puede crear la factura, no hay elementos para crear."
            ]);
        }

        // Extract and prepare data
        $data = $request->except('fotos');
        $factura = new Facturas($data);
        $factura->hash = $this->calcularHuella($factura);
        $factura->save(); // Must save before Verifactu call (ID needed)

        try {
            // Update presupuesto estado if needed
            if (in_array($factura->serie, [1, 2, 5]) && $factura->presupuesto_id) {
                $presupuesto = Presupuestos::findOrFail($factura->presupuesto_id);
                $totalFacturas = $presupuesto->facturas()->sum('total');
                $presupuesto->estado = $totalFacturas >= $presupuesto->total ? 4 : 3;
                $presupuesto->save();
            }

            // Handle Verifactu logic
            if ($factura->factura_nativa && $factura->serie == 2) {
                $factura_nativa = Facturas::findOrFail($factura->factura_nativa);
                $factura_nativa->estado = 3;
                $factura_nativa->save();

                $verifactuController = new VerifactuController();
                $response = $verifactuController->sendRectificacion($factura->id);

                if (!$response || !$response->getData()->success) {
                    throw new Exception("Error al enviar la rectificación a Verifactu");
                }

            } elseif ($factura->serie == 1) {
                $verifactuController = new VerifactuController();
                $response = $verifactuController->sendInvoice($factura->id);

                if (!$response || !$response->getData()->success) {
                    throw new Exception("Error al enviar la factura a Verifactu");
                }
            }

            // Log the creation action
            AccessLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'action' => 'CREAR Factura ' . $factura->numFactura,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            // Redirect
            return $factura->serie == 11
                ? redirect()->route('facturas.borradores.index')
                : redirect()->route('facturas.index');

        } catch (Exception $e) {
            // Delete factura if Verifactu fails
            $factura->delete();

            return back()->withErrors([
                'verifactu' => "Error en Verifactu: " . $e->getMessage()
            ]);
        }
    }


    public function show($id)
    {
        $user = User::find(Auth::user()->id);

        $this->admin_rol();
        $factura = Facturas::findOrFail($id)->load('cliente', 'presupuesto', 'cobros', 'nativa');
        // Generate and save hash if it's null
        if (!$factura->hash) {
            $factura->hash = $this->calcularHuella($factura);
            $factura->save();
        }
        $data = ['factura' => $factura];

        // Regenerate and save new PDF
        $pdfContent = Pdf::loadView('Documents.facturaPDF', $data)->output();

        if (($factura->totalCobros() >= $factura->total && $factura->estado == 5)) {
            $factura->estado = 2; // Mark as fully paid
        }
        // Update the factura's PDF path
        return Inertia::render('Cruds/Facturas/Show', [
            'user' => $user,
            'factura' => $factura,
            'pdfContent' => base64_encode($pdfContent),
        ]);
    }

    public function read_qr($hash, $id)
    {
        $factura = Facturas::where('hash', $hash)->where('id', $id)->first();

        if (!$factura) {
            abort(404, 'Factura not found');
        }

        if ($factura && $factura->pdf) {
            // Construct the validation URL with the given parameters
            $urlBase = 'https://prewww2.aeat.es/wlpl/TIKE-CONT/ValidarQR?';
            $nif = ConfigHelper::get('tax_id'); // Assuming 'nif' is a column in your factura
            $numserie = $factura->numFactura; // Assuming 'numserie' is a column in your factura
            $fecha = Carbon::parse($factura->fechaInicio)->format('d-m-Y');
            $importe = $factura->total; // Assuming 'importe' is a column in your factura
            // Construct the full URL
            $validationUrl = $urlBase . "nif=$nif&numserie=$numserie&fecha=$fecha&importe=$importe";

            return redirect()->away($validationUrl);
        }
        return null;
    }

    public function edit($id)
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        $factura = Facturas::findOrFail($id);

        // ❌ Only allow editing if the factura is of serie 11 (borrador)
        if ($factura->serie != 11 && $factura->serie != 7) {
            return response()->json([
                'message' => 'Solo se pueden editar borradores.'
            ], 403); // 403 = Forbidden
        }

        $metodos = Metodos_Pago::all();

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::where('estado', 2)
            ->orWhere('id', $factura->presupuesto_id)
            ->get();


        return Inertia::render('Cruds/Facturas/Update', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'metodos' => $metodos,
            'factura' => $factura
        ]);
    }


    public function update(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);

        $this->admin_rol();

        $factura = Facturas::findOrFail($id)->load('presupuesto', 'cliente');

        $data = $request->all();

        if (empty($request->input('servicios'))) {
            return back()->withErrors([
                'servicios' => "No se puede actualizar la factura, no hay elementos para actualizar."
            ]);
        }

        $factura->fill($data);
        // Save updated factura
        $factura->save();

        new VerifactuController();
        // $verifactuController->sendInvoice($factura);
        // Log the update action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ACTUALIZAR Factura ' . $factura->numFactura;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        // Redirect back to the factura index or details page
        if ($factura->serie == 11) {
            return redirect()->route('facturas.borradores.index');
        } else {
            return redirect()->route('facturas.index');
        }
    }


    public function estado(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);

        $factura = Facturas::findOrFail($id);
        $estado = $request->input('estado'); // Get the estado value as a string

        // Map string value to corresponding integer value
        $estadoString = [
            0 => 'Pendiente',
            1 => 'Rechazada',
            2 => 'Aceptado',
        ];

        $factura->estado = $estado;
        if ($estado == 1) {
            $verifactuController = new VerifactuController();
            $verifactuController->anularInvoice($factura->id);
        }
        $factura->save();

        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'EDITAR ESTADO FACTURA ' . $factura->numFactura . ' a ' . $estadoString[$estado];
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return response()->json(['message' => 'Factura estado updated successfully']);
    }

    public function createCobro(Request $request, $id)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'fecha_cobro' => 'required|date',
            'total' => 'required|numeric',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail(Auth::id());

        // Find the factura by ID or fail
        $factura = Facturas::findOrFail($id);

        // Create a new Cobro record
        $cobro = new Cobro();
        $cobro->fecha_cobro = $validatedData['fecha_cobro'];
        $cobro->factura_id = $factura->id; // Corrected typo
        $cobro->observaciones = $validatedData['observaciones'] ?? null; // Handle nullable field
        $cobro->total = round($validatedData['total'], 2);
        $cobro->save();

        // Calculate the total amount of all cobros for the factura
        $totalCobros = $factura->cobros()->sum('total'); // Summing the 'total' of all related cobros

        // Update factura estado based on totalCobros
        if ($totalCobros >= $factura->total) {
            $factura->estado = 2; // Mark as fully paid
        } else {
            $factura->estado = 5; // Mark as partially paid
        }
        $factura->save();

        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'Agregar Cobro de FACTURA ' . $factura->numFactura;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        // Return a JSON response
        // return response()->json(['message' => 'Cobro created and Factura estado updated successfully']);
    }

    public function destroy($id)
    {
        // Ensure the user has admin privileges
        $this->admin_rol();

        // Find the factura or return 404 if not found
        $factura = Facturas::findOrFail($id);

        // Delete the associated PDF file from storage if it exists
        if ($factura->pdf && Storage::exists('public/' . $factura->pdf)) {
            Storage::delete('public/' . $factura->pdf);
        }

        // Redirect back with a success message
        // Redirect back to the factura index or details page
        if ($factura->serie == 11) {
            $factura->delete();
            return redirect()->route('facturas.borradores.index');
        } else {
            $verifactuController = new VerifactuController();
            $verifactuController->anularInvoice($factura->id);

            // Log the deletion action
            $log = new AccessLog();
            $log->user_id = Auth::id();
            $log->email = Auth::user()->email;
            $log->action = 'Anular Factura ' . $factura->numFactura;
            $log->ip_address = request()->ip();
            $log->user_agent = request()->header('User-Agent');
            $log->save();

            // Delete the factura record
            $factura->estado = 1;
            $factura->save();
            return redirect()->route('facturas.index');
        }
    }
    /**
     * Genera el número de factura utilizando la plantilla maestra correspondiente.
     *
     *
     * Parámetros:
     *  - Tipo: Siempre "Factura" en este contexto.
     *  - Serie: Número de serie (1, 2, 5, 7, 11, etc.) que define el formato específico.
     *  - Canal de log: "facturasError" para registrar errores específicos en el canal de facturación.
     *
     * @param int $serie   Número de serie de la factura.
     * @return string      Número de factura generado, o mensaje de error en caso de fallo.
     */
    public function generateFacturaNumber($serie)
    {
        return CodigoDocumentoHelper::generarNumero(
            'Factura',            // Tipo de documento: siempre 'Factura' en este caso
            $serie,              // La serie que recibe este método
            'facturasError'   // Canal de logs para errores de facturación
        );
    }

    public function libroDeFacturas(Request $request)
    {
        User::find(Auth::user()->id);

        $this->admin_rol(); // Ensure proper authorization

        // Validation rules
        $rules = [
            'startDate' => 'required|date',
            'finishDate' => 'required|date|after_or_equal:startDate',
        ];

        $messages = [
            'finishDate.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];

        // Validate the request
        $request->validate($rules, $messages);

        setlocale(LC_TIME, 'es_ES');

        $startDate = $request->input('startDate');
        $finishDate = $request->input('finishDate');
        $sortingOption = $request->input('sortingOption');
        $selectedSeries = $request->input('series', []);

        $facturasQuery = Facturas::whereIn('serie', $selectedSeries);

        if ($startDate && $finishDate) {
            $facturasQuery->whereBetween('fechaInicio', [$startDate, $finishDate]);
        }

        if ($sortingOption === 'fecha') {
            $facturasQuery->orderBy('fechaInicio');
        } elseif ($sortingOption === 'numFactura') {
            $facturasQuery->orderBy('numFactura');
        } elseif ($sortingOption === 'serie') {
            $facturasQuery->orderBy('serie');
        }

        $facturas = $facturasQuery->get();

        if ($facturas->isEmpty()) {
            return response()->json([
                'error' => 'No hay facturas en el rango de fechas proporcionado.'
            ], 404);
        }

        $exportType = $request->input('exportType', 'pdf');

        if ($exportType === 'pdf') {
            $pdf = Pdf::loadView('Documents.libro-de-facturas', ['facturas' => $facturas]);
            return response()->streamDownload(
                fn() => print ($pdf->output()),
                'libro-de-facturas.pdf'
            );
        } elseif ($exportType === 'excel') {
            return Excel::download(new FacturasExport($facturas), 'libro-de-facturas.xlsx');
        } else {
            return response()->json([
                'error' => 'Formato de exportación inválido'
            ], 400);
        }
    }

    public function reportFacturas(Request $request)
    {
        $facturasIds = collect($request->facturasIds);

        $facturas = Facturas::whereIn('id', $facturasIds)->get()->load('cliente');

        // Check if there are no facturas
        if ($facturas->isEmpty()) {
            return response()->json(['message' => 'No se encontraron facturas'], 404);
        }

        // Export based on the requested format
        $format = $request->input('format');
        if ($format === 'excel') {
            return Excel::download(new FacturasExport($facturas), "informe_factura.xlsx");
        } elseif ($format === 'pdf') {
            $pdf = PDF::loadView('Documents.libro-de-facturas', ['facturas' => $facturas]);
            $fileName = "informe_factura.pdf";
            return $pdf->download($fileName);
        }

        return response()->json(['message' => 'Formato no válido'], 400);
    }



    public function send_email(Request $request, $id)
    {
        $factura = facturas::findOrFail($id);
        $cliente = $factura->cliente;

        if (!$cliente) {
            return response()->json(['error' => 'Cliente not found for this factura.'], 404);
        }

        // Ensure 'emails' is provided and is an array
        if (!$request->has('emails') || !is_array($request->emails)) {
            return response()->json(['error' => 'Emails array is required.'], 400);
        }

        $pdfContent = Pdf::loadView('Documents.facturaPDF', ['factura' => $factura])->output();

        // Construct the email message
        $message = "Estimado/a " . $cliente->nombre . ",\n\n";
        $message .= "Adjuntamos el factura solicitado en formato PDF.\n\n";
        $message .= "Si tiene alguna consulta, no dude en contactarnos.\n\n";
        $message .= "Saludos cordiales,\n";
        $message .= "El equipo de " . config('app.name');


        // Call the email response function with multiple recipients
        $this->facturaEmail($request->emails, $message, $pdfContent);
        return null;
    }

    private function facturaEmail(array $emails, $message, $pdf)
    {
        $myEmail = ConfigHelper::get('email');

        // Merge the provided emails with your configured email
        $recipients = array_merge($emails, [$myEmail]);

        // Send the email to all recipients
        Mail::to($recipients)->send(new FacturaEmail($message, $pdf));
    }

    private function facturae($id, $certificado = null, $passphrase = 'passphrase', $FaceB2B = false)
    {
        $factura = Facturas::with('cliente')->findOrFail($id);
        $facturae = new Facturae();

        // Set invoice number and issue date
        $facturae->setNumber($factura->serie, $factura->numFactura);
        $facturae->setIssueDate($factura->fechaInicio);

        if (!$factura->cliente) {
            abort(404, 'Cliente no encontrado.');
        }

        // Set issuer (seller)
        $facturae->setSeller(new FacturaeParty([
            "taxNumber" => ConfigHelper::get('tax_id'),
            "name" => ConfigHelper::get('business_name'),
            "address" => ConfigHelper::get('address'),
            "postCode" => ConfigHelper::get('postal_code'),
            "town" => ConfigHelper::get('town'),
            "province" => ConfigHelper::get('province'),
        ]));

        // Set buyer
        if ($factura->cliente?->category === 'Empresa') {
            $facturae->setBuyer(new FacturaeParty([
                "isLegalEntity" => true,
                "taxNumber" => $factura->cliente->dni,
                "name" => $factura->cliente->nombre,
                "address" => $factura->cliente->direccion,
                "postCode" => $factura->cliente->cp,
                "town" => $factura->cliente->localidad,
                "province" => $factura->cliente->provincia,
                "countryCode" => "ESP",
                "centres" => [
                    new FacturaeCentre([
                        "role" => FacturaeCentre::ROLE_GESTOR,
                        "code" => $factura->cliente->dir3,
                        "name" => $factura->cliente->nombre,
                        "address" => $factura->cliente->direccion,
                        "postCode" => $factura->cliente->cp,
                        "town" => $factura->cliente->localidad,
                        "province" => $factura->cliente->provincia,
                    ]),
                    new FacturaeCentre([
                        "role" => FacturaeCentre::ROLE_TRAMITADOR,
                        "code" => $factura->cliente->dir3,
                        "name" => $factura->cliente->nombre,
                        "address" => $factura->cliente->direccion,
                        "postCode" => $factura->cliente->cp,
                        "town" => $factura->cliente->localidad,
                        "province" => $factura->cliente->provincia,
                    ]),
                    new FacturaeCentre([
                        "role" => FacturaeCentre::ROLE_CONTABLE,
                        "code" => $factura->cliente->dir3,
                        "name" => $factura->cliente->nombre,
                        "address" => $factura->cliente->direccion,
                        "postCode" => $factura->cliente->cp,
                        "town" => $factura->cliente->localidad,
                        "province" => $factura->cliente->provincia,
                    ])
                ]
            ]));
        } else {
            $facturae->setBuyer(new FacturaeParty([
                "isLegalEntity" => true,
                "taxNumber" => $factura->cliente->dni,
                "name" => $factura->cliente->nombre,
                "firstSurname" => $factura->cliente->apellido_1 ?? '.',
                "lastSurname" => $factura->cliente->apellido_2 ?? '',
                "address" => $factura->cliente->direccion,
                "postCode" => $factura->cliente->cp,
                "town" => $factura->cliente->localidad,
                "province" => $factura->cliente->provincia,
                "bookCode" => $factura->cliente->dir3,
            ]));
        }

        // Add invoice items
        if (!empty($factura->all_productos)) {
            foreach ($factura->all_productos as $producto) {
                $facturae->addItem(new FacturaeItem([
                    "name" => $producto['nombre'] ?? 'Producto',
                    "description" => $producto['descripcion'] ?? '',
                    "quantity" => $producto['cantidad'] ?? 1,
                    "unitPrice" => round(($producto['precio'] ?? 0) * (1 + ($factura->iva ?? 0) / 100), 2),
                    "discounts" => empty($producto['descuento']) ? [] : [
                        ["reason" => "Descuento del " . $producto['descuento'] . '%', "rate" => $producto['descuento']]
                    ],
                    "taxes" => array_filter([
                        Facturae::TAX_IVA => $factura->iva ?? 0,
                        Facturae::TAX_IRPF => $factura->irpf ?? 0
                    ], fn($v) => $v !== 0)
                ]));
            }
        }

        // Generate and sign XML
        $xmlFilePath = "facturas/factura_{$factura->hash}.xsig";
        Storage::makeDirectory('public/facturas');
        $fullPath = storage_path("app/public/{$xmlFilePath}");

        if ($certificado) {
            if ($facturae->sign($certificado, null, $passphrase)) {
                $facturae->export($fullPath);

                // Upload to FACE B2B if requested
                if ($FaceB2B === true || $FaceB2B === 'true') {
                    $this->uploadFaceB2B($facturae, $certificado, $passphrase);
                }
            } else {
                abort(403, 'El certificado no es válido o la passphrase es incorrecta.');
            }
        } else {
            $facturae->export($fullPath);
        }
    }


    private function uplaodFaceB2B($facturae, $certificado, $passphrase)
    {
        // Upload to FACeB2B
        $facturae->setSchemaVersion(Facturae::SCHEMA_3_2_2);
        $invoice = new FacturaeFile();
        $invoice->loadData($facturae->export(), "factura.xsig");
        $faceb2b = new Faceb2bClient($certificado, null, $passphrase);
        $faceb2b->setProduction(false); // Uncomment for testing mode

        $res = $faceb2b->sendInvoice($invoice);
        if ($res->resultStatus->code == 0) {
            dd($res);
        }

    }
    public function calcularHuella($factura)
    {
        $idEmisor = Config::get('tax_id');
        $numSerie = trim($factura->numFactura);
        $fechaExp = Carbon::parse($factura->fechaInicio)->format('d-m-Y');
        $tipoFactura = 'F1';
        $cuotaTotal = number_format($factura->total_iva, 2, '.', '');
        $importeTotal = number_format($factura->total, 2, '.', '');
        $fechaHoraGeneracion = now()->format('Y-m-d\TH:i:sP');

        // 🔁 Usar factura_nativa si es una rectificativa
        if ($factura->factura_nativa) {
            $facturaAnterior = Facturas::find($factura->factura_nativa);
        } else {
            $facturaAnterior = Facturas::where('id', '<', (int) $factura->id)
                ->whereIn('serie', ['1', '2', '5']) // <== strings, to match a VARCHAR/TEXT column
                ->orderBy('fechaInicio', 'desc')
                ->first();
        }

        // Obtener la huella anterior (o vacía si no hay anterior)
        $huellaAnterior = $facturaAnterior && $facturaAnterior->hash
            ? $facturaAnterior->hash
            : '';
        // Construir la cadena
        $cadena = "IDEmisorFactura={$idEmisor}" .
            "&NumSerieFactura={$numSerie}" .
            "&FechaExpedicionFactura={$fechaExp}" .
            "&TipoFactura={$tipoFactura}" .
            "&CuotaTotal={$cuotaTotal}" .
            "&ImporteTotal={$importeTotal}" .
            "&Huella={$huellaAnterior}" .
            "&FechaHoraHusoGenRegistro={$fechaHoraGeneracion}";

        // Calcular huella
        $hash = strtoupper(hash('sha256', $cadena));

        if (strlen($hash) !== 64) {
            dd('Huella con longitud inválida: ' . strlen($hash));
        }

        return $hash;
    }

    public function downloadFactura(Request $request, $facturaHash, $id)
    {
        $factura = Facturas::where('hash', $facturaHash)->where('id', $id)->first();
        if (!$factura) {
            abort(404, 'Factura not found');
        }

        $certificadoPath = null;
        $FaceB2B = $request->faceB2B;
        // Check if the user uploaded a certificate file
        if ($request->hasFile('certificado_pfx')) {
            $certificado = $request->file('certificado_pfx');
            // Validate that it's a .pfx file
            if ($certificado->getClientOriginalExtension() !== 'pfx') {
                return response()->json(['error' => 'Invalid certificate format. Please upload a .pfx file'], 400);
            }

            // Store the file temporarily
            $certificadoPath = $certificado->storeAs('temp', 'certificado.pfx');
            $certificadoPath = storage_path('app/' . $certificadoPath); // Get full system path
        }

        $passphrase = $request->passphrase;
        // Generate facturae with or without the certificate
        $this->facturae($factura->id, $certificadoPath, $passphrase, $FaceB2B);

        // Construct the file path
        $xmlFilePath = "facturas/factura_" . $facturaHash . ".xsig";
        $fullPath = storage_path('app/public/' . $xmlFilePath); // Full system path

        // Check if the file exists
        if (file_exists($fullPath)) {
            // Return the file as a response, triggering the download
            return response()->download($fullPath, basename($xmlFilePath));
        } else {
            // Log the error if the file doesn't exist
            Log::error("XML file not found for factura with hash: " . $facturaHash);
            return response()->json(['error' => 'File not found'], 404);
        }
    }

}
