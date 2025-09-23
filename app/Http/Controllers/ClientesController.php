<?php

namespace App\Http\Controllers;

use App\Exports\ClienteModelo347Export;
use App\Exports\ClientesTrimestresExport;
use App\Models\Clientes;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\AccessLog;
use App\Models\Contacto;
use App\Models\Presupuestos;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientesController extends Controller
{
    public function index()
    {
        $this->admin_rol();
        $user = User::find(Auth::user()->id);


        $clientes = Clientes::all();

        return Inertia::render('Cruds/Clientes/Index', [
            'user' => $user,
            'clientes' => $clientes
        ]);

    }

    public function create(Request $request)
    {
        $this->admin_rol();

        $user = User::find(Auth::user()->id);
        $contacto = $request->all();

        return Inertia::render('Cruds/Clientes/Create', [
            'user' => $user,
            'contacto' => $contacto,
        ]);
    }

    public function edit($id)
    {
        $this->admin_rol();

        $user = User::find(Auth::user()->id);
        $cliente = Clientes::find($id);


        return Inertia::render('Cruds/Clientes/Update', [
            'cliente' => $cliente,
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        // Define validation rules
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido_1' => 'nullable|string|max:255',
            'apellido_2' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:clientes,email',
            'telefono_mobile' => 'nullable|regex:/^[0-9]{9}$/',
            'telefono_fijo' => 'nullable|regex:/^[0-9]{9}$/',
            'dni' => 'required|regex:/^([a-zA-Z0-9])[0-9]{7}([a-zA-Z0-9])$/|unique:clientes,dni',
            'direccion' => 'nullable|string|max:255',
            'localidad' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
            'num_cuenta' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'cp' => 'nullable|digits:5',
            'category' => 'required|in:Personal,Empresa',
            'share_data' => 'boolean',
            'api_key' => 'nullable|string|max:255',
            'dir3' => 'nullable|string|max:255',
            'contacto_id' => 'nullable|exists:contactos,id',
        ];
        // Validate the input data
        $validatedData = $request->validate($rules);

        // Create a new client using validated data
        $cliente = Clientes::create($validatedData);
        if (!empty($validatedData['contacto_id'])) {
            $presupuetos = Presupuestos::where('contacto_id', $validatedData['contacto_id'])->get();
            foreach ($presupuetos as $presupueto) {
                $presupueto->contacto_id = null;
                $presupueto->cliente_id = $cliente->id;
                $presupueto->save();
            }
            $contacto = Contacto::find($validatedData['contacto_id']);
            $contacto->delete();
        }
        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'CREAR CLIENTE: ' . $cliente['nombre'];
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return redirect()->route('clientes.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();

        // Find the client by ID
        $cliente = Clientes::findOrFail($id);

        // Define validation rules
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido_1' => 'nullable|string|max:255',
            'apellido_2' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:clientes,email,' . $cliente->id, // Exclude current client
            'telefono_mobile' => 'nullable|regex:/^[0-9]{9}$/',
            'telefono_fijo' => 'nullable|regex:/^[0-9]{9}$/',
            'dni' => 'required|regex:/^([a-zA-Z0-9])[0-9]{7}([a-zA-Z0-9])$/|unique:clientes,dni,' . $cliente->id, // Exclude current client
            'direccion' => 'nullable|string|max:255',
            'localidad' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
            'cp' => 'nullable|digits:5',
            'num_cuenta' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'category' => 'required|in:Personal,Empresa',
            'trabajador_id' => 'nullable|exists:users,id',
            'share_data' => 'boolean',
            'api_key' => 'nullable|string|max:255',
            'dir3' => 'nullable|string|max:255',
        ];

        // Validate the input data
        $validatedData = $request->validate($rules);

        // Update the client with validated data
        $cliente->update($validatedData);

        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ACTUALIZAR CLIENTE: ' . $cliente['nombre'];
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);

        $this->admin_rol();
        $cliente = Clientes::findOrFail($id);

        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ELIMINAR CLIENTE ' . $cliente->dni;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();


        $cliente->delete();
        return redirect()->route('clientes.index');
    }
    public function show($id)
    {
        $user = User::find(Auth::user()->id);
        $this->admin_rol();
        $cliente = clientes::findOrFail($id)
            ->load('facturas');

        // Fetch all items with estado = 1
        $facturas = $cliente->facturas;
        // Calculate totals
        $facturas_total = $facturas->sum('total');

        // Retrieve data for charts (raw format) and filter by current year
        $datos_facturas = $this->getTotalsByDate('facturas', 'fechaInicio');

        $facturasFormatted = $this->formatChartData($datos_facturas);

        // Return data to Vue
        return Inertia::render('Cruds/Clientes/Show', [
            'cliente' => $cliente,
            'facturas' => $facturas,
            'user' => $user,
            'totals' => [
                'facturas_total' => $facturas_total,
            ],
            'chartData' => [
                'facturas' => $facturasFormatted,
            ],
        ]);
    }

    public function getTotalsByDate($table, $dateColumn, $amountColumn = 'total')
    {
        return DB::table($table)
            ->select(DB::raw('SUM(' . $amountColumn . ') as total_mes, YEAR(' . $dateColumn . ') as year, MONTH(' . $dateColumn . ') as mes'))
            ->groupBy('year', 'mes')
            ->get()
            ->groupBy('year');
    }

    private function formatChartData($data)
    {
        // Fixed month labels (static) for the chart
        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $dataset = array_fill(0, 12, 0);  // Start with an array of 12 zeros (for each month)

        // Initialize totals for the current month and the same month from the last year
        $currentMonthTotal = 0;
        $lastYearMonthTotal = 0;

        // Fill the dataset with totals by month (for chart purposes)
        foreach ($data as $year => $months) {
            foreach ($months as $month) {
                $monthIndex = $month->mes - 1; // Month is 1-based (Jan = 1, Dec = 12)

                if ($year == date('Y')) { // If it's the current year
                    $dataset[$monthIndex] = $month->total_mes; // Fill data for chart
                    if ($month->mes == date('n')) { // Check if it's the current month
                        $currentMonthTotal = $month->total_mes; // Set current month total
                    }
                } elseif ($year == (date('Y') - 1)) { // If it's the last year
                    if ($month->mes == date('n')) { // Check if it's the same month last year
                        $lastYearMonthTotal = $month->total_mes; // Set last year's current month total
                    }
                }
            }
        }

        // Calculate the percentage difference between the current month and last year
        $percentageDifference = 0;
        if ($lastYearMonthTotal > 0) {
            $percentageDifference = (($currentMonthTotal - $lastYearMonthTotal) / $lastYearMonthTotal) * 100;
        }

        // Format the percentage as a string with a plus or minus sign
        $percentageString = round($percentageDifference, 2) . '%';

        return [
            'labels' => $labels, // Fixed month names for chart
            'data' => $dataset, // Monthly data (even if year total is used)
            'percentageDifference' => $percentageString, // Send the formatted percentage difference
        ];
    }

    public function descargarPDF(Request $request)
    {
        $currentYear = $request->year;
        $type = $request['format'];

        $clientesArray = Clientes::all()->map(function ($cliente) use ($currentYear) {
            $cliente->total_facturas = $cliente->facturasThisYearTotal($currentYear);

            if ($cliente->total_facturas <= 3005.06) {
                return null; // Skip clients with low total invoices
            }

            $facturas = $cliente->facturasThisYear($currentYear);
            if ($facturas->isEmpty()) {
                return null; // Skip clients with no invoices
            }

            // Calculate trimester totals
            $trimestres = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            foreach ($facturas as $factura) {
                $trimestre = ceil((new DateTime($factura->fechaInicio))->format('n') / 3);
                $trimestres[$trimestre] += $factura->total;
            }

            // Assign calculated values
            $cliente->trimestre1 = $trimestres[1];
            $cliente->trimestre2 = $trimestres[2];
            $cliente->trimestre3 = $trimestres[3];
            $cliente->trimestre4 = $trimestres[4];
            $cliente->currentYear = $currentYear;
            $cliente->total = array_sum($trimestres);

            return $cliente;
        })->filter()->values(); // Remove null values and reindex

        // Handle the case where all clients have invoices below 3006 euros
        if ($clientesArray->isEmpty()) {
            return response()->json([
                'error' => 'Todos los clientes tienen un monto inferior a 3006 euros o no tienen facturas.',
            ], 400);
        }

        // Generate PDF or Excel file
        if ($type == 'pdf') {
            return Pdf::loadView('Documents.m347pdf', ['clientes' => $clientesArray, 'year' => $currentYear])->stream();
        } elseif ($type == 'excel') {
            return Excel::download(new ClientesTrimestresExport($clientesArray), 'Modelo_347_' . Carbon::now()->format('d-m-Y') . '.xlsx');
        }
        return null;
    }
    public function modelo347PDF(Request $request, $id)
    {
        $currentYear = $request->year;
        $type = $request['format'];

        $cliente = Clientes::findOrFail($id);
        $cliente->total_facturas = $cliente->facturasThisYearTotal($currentYear);
        $cliente->facturas = $cliente->facturasThisYear($currentYear);

        $message = "El total de las facturas del año $currentYear es inferior a 3005.06. No se pudo generar el Modelo 347.";

        if ($cliente->facturasThisYearTotal($currentYear) > 3005.06) {
            if ($type === 'pdf') {
                $pdf = Pdf::loadView('Documents.m347pdf_cliente', ['cliente' => $cliente]);
                return $pdf->stream();
            } elseif ($type === 'excel') {
                return Excel::download(new ClienteModelo347Export($cliente), "Modelo347_$currentYear.xlsx");
            }
        }

        // Returning the message as a JSON response for Vue
        return response()->json([
            'error' => true,
            'message' => $message
        ], 400); // You can adjust the status code if needed
    }
}
