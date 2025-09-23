<?php

namespace App\Http\Controllers;

use App\Exports\PresupuestosExport;
use App\Helpers\ConfigHelper;
use App\Mail\PresupuestoEmail;
use App\Mail\ResponseEmail;
use App\Models\Metodos_Pago;
use App\Models\Clientes;
use Illuminate\Support\Facades\Mail;
use App\Models\Presupuestos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AccessLog;
use App\Models\Contacto;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class PresupuestosController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::id());

        if ($user->rol === 'admin' || $user->rol === 'gestor') {
            $presupuestos = Presupuestos::with('cliente', 'contacto')
                ->orderBy('fechaInicio', 'desc')
                ->get();

            $clientes = $presupuestos
                ->pluck('cliente')
                ->filter()
                ->unique('id')
                ->values();

            return Inertia::render('Cruds/Presupuestos/Index', [
                'user' => $user,
                'presupuestos' => $presupuestos,
                'clientes' => $clientes,
            ]);
        } else {
            $presupuestos = Presupuestos::with('cliente', 'contacto')
                ->orderBy('fechaInicio', 'desc')
                ->get();

            $clientes = $presupuestos
                ->pluck('cliente')
                ->filter()
                ->unique('id')
                ->values();

            return Inertia::render('Cruds/Presupuestos/Index', [
                'user' => $user,
                'presupuestos' => $presupuestos,
                'clientes' => $clientes,
            ]);
        }
    }

    public function create()
    {
        $user = User::find(Auth::user()->id);
        $contactos = Contacto::all();
        $metodos = Metodos_Pago::all();

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::all();

        $numPresupuesto = $this->generatePresupuestoNumber();

        return Inertia::render('Cruds/Presupuestos/Create', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'contactos' => $contactos,
            'clientes' => $clientes,
            'metodos' => $metodos,
            'numPresupuesto' => $numPresupuesto,
        ]);
    }

    public function duplicate($id)
    {
        $user = User::find(Auth::user()->id);
        $contactos = Contacto::all();
        $metodos = Metodos_Pago::all();
        $presupuesto = Presupuestos::findOrFail($id);
        $presupuesto->estado = 0; // se reinicia el estado del duplicado
        $numPresupuesto = $this->generatePresupuestoNumber();

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::all();


        return Inertia::render('Cruds/Presupuestos/Duplicate', [
            'user' => $user,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'contactos' => $contactos,
            'metodos' => $metodos,
            'presupuesto' => $presupuesto,
            'numPresupuesto' => $numPresupuesto
        ]);
    }

    public function store(Request $request)
    {
        $user = User::find(Auth::user()->id);

        // Validate the request
        $validatedData = $request->validate([
            'servicios' => 'required|array|min:1',
            'fotos.*' => 'nullable', // Optional validation for photos
        ]);

        // Create the presupuesto
        $presupuesto = new Presupuestos($request->all());
        $presupuesto->hash = md5(json_encode($validatedData));
        $presupuesto->save();

        // Handle file uploads
        if ($request->hasFile('fotos')) {
            $fotos = [];
            $tenantId = session('tenant_id'); // get current tenant
            $year = date('Y'); // current year, e.g., 2024

            foreach ($request->file('fotos') as $foto) {
                $path = Storage::disk('public')->putFile(
                    // Folder: tenants/{tenant_id}/{year}/presupuestos/presupuesto_{id}
                    'tenants' . $tenantId . '/' . $year . '/presupuestos/presupuesto_' . $presupuesto->id,
                    $foto
                );
                $fotos[] = $path; // store relative path
            }

            $presupuesto->fotos = $fotos;
            $presupuesto->save();
        }
        // Log the action
        AccessLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'action' => 'CREAR PRESUPUESTO ' . $presupuesto->numPresupuesto,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        // Redirect to the presupuestos index
        return redirect()->route('presupuestos.index');
    }

    public function show($id)
    {
        $user = User::find(Auth::user()->id);

        // Retrieve the Presupuesto with related data
        $presupuesto = Presupuestos::findOrFail($id)->load('cliente', 'contacto', 'anexo', 'facturas');
        $clientes = Clientes::all();

        $pdfContent = Pdf::loadView('Documents.presupuestoPDF', ['presupuesto' => $presupuesto])->output();
        // Update the presupuesto with the new PDF path

        return Inertia::render('Cruds/Presupuestos/Show', [
            'user' => $user,
            'presupuesto' => $presupuesto,
            'clientes' => $clientes,
            'pdfContent' => base64_encode($pdfContent),
        ]);
    }

    public function edit($id)
    {
        $user = Auth::user();
        $presupuesto = Presupuestos::findOrFail($id);
        $contactos = Contacto::all();
        $metodos = Metodos_Pago::all();

        $clientes = Clientes::all();
        $presupuestos = Presupuestos::all();


        return Inertia::render('Cruds/Presupuestos/Update', [
            'user' => $user,
            'presupuesto' => $presupuesto,
            'presupuestos' => $presupuestos,
            'clientes' => $clientes,
            'contactos' => $contactos,
            'metodos' => $metodos,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);

        // Find the Presupuesto by ID
        $presupuesto = Presupuestos::find($id);

        // If the Presupuesto does not exist, return an error
        if (!$presupuesto) {
            return back()->withErrors([
                'error' => "El Presupuesto no se encuentra en el sistema."
            ]);
        }

        // Extract request data excluding 'fotos'
        $data = $request->except('fotos');

        // Validate that 'servicios' is not empty
        if (empty($request->input('servicios'))) {
            return back()->withErrors([
                'servicios' => "No se puede actualizar el Presupuesto, no hay elementos para actualizar."
            ]);
        }

        // Update the presupuesto with new data
        $presupuesto->fill($data);


        $fotos = [];
        $tenantId = session('tenant_id'); // get current tenant
        $year = date('Y'); // current year

        // Handle new and existing files
        if ($request->has('fotos')) {
            foreach ($request->fotos as $index => $foto) {
                if (is_string($foto)) {
                    // Keep existing path
                    if (!in_array($foto, $fotos)) {
                        $fotos[] = $foto;
                    }
                } elseif ($foto instanceof \Illuminate\Http\UploadedFile) {
                    // Validate
                    $this->validate($request, [
                        "fotos.{$index}" => 'mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
                    ]);

                    // Store in tenant/year folder
                    $path = Storage::disk('public')->putFile(
                        "tenants{$tenantId}/{$year}/presupuestos/presupuesto_{$presupuesto->id}",
                        $foto
                    );
                    $fotos[] = $path;
                }
            }
        }


        // Delete old files that are no longer in the request
        if ($presupuesto->fotos) {
            foreach ($presupuesto->fotos as $existingPath) {
                // If the existing file is not in the new request, delete it
                if (!in_array($existingPath, $request->fotos ?? [])) {
                    if (Storage::disk('public')->exists($existingPath)) {
                        Storage::disk('public')->delete($existingPath);
                    }
                }
            }
        }

        // Update presupuesto with new files
        $presupuesto->fotos = $fotos;
        $presupuesto->save();

        // Log the update action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ACTUALIZAR PRESUPUESTO ' . $presupuesto->numPresupuesto;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        // Redirect to the presupuestos index with a success message
        return redirect()->route('presupuestos.index')->with('success', 'Presupuesto actualizado correctamente.');
    }

    public function estado(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);
        $presupuesto = Presupuestos::findOrFail($id);
        $estado = $request->input('estado'); // Get the estado value as a string

        $presupuesto->estado = $estado;
        $presupuesto->save();

        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'CAMBIAR ESTADO PRESUPUESTO ' . $presupuesto->numPresupuesto . ' a ' . $presupuesto->estado_text;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        // return redirect()->back()->with('success', 'Presupuesto actualizado correctamente.');
    }


    public function destroy(Request $request, $id)
    {
        $user = Auth::user();

        // Ensure the user has admin privileges
        $this->admin_rol();

        // Find the Presupuesto by ID
        $presupuesto = Presupuestos::find($id);

        // If the Presupuesto does not exist, return an error
        if (!$presupuesto) {
            return back()->withErrors([
                'error' => "El Presupuesto no se encuentra en el sistema."
            ]);
        }

        // Remove associated photos if they exist
        if (!empty($presupuesto->fotos)) {
            foreach ($presupuesto->fotos as $foto) {
                // Check if file exists in the public disk
                if (Storage::disk('public')->exists($foto)) {
                    Storage::disk('public')->delete($foto);
                }
            }
        }

        // Log the deletion action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ELIMINAR PRESUPUESTO ' . $presupuesto->numPresupuesto;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        // Delete the Presupuesto
        $presupuesto->delete();

        // Redirect to the presupuestos index with a success message
        return redirect()->route('presupuestos.index')->with('success', 'Presupuesto eliminado correctamente.');
    }

    public function generatePresupuestoNumber()
    {
        $existingNumbers = Presupuestos::pluck('numPresupuesto')->toArray();
        $currentYear = date('y');

        $sequences = array_map(function ($numPresupuesto) use ($currentYear) {
            preg_match("/^P-$currentYear-8\/(\d+)$/", $numPresupuesto, $matches);
            return isset($matches[1]) ? (int) $matches[1] : null;
        }, $existingNumbers);

        $sequences = array_filter($sequences);

        $nextNumber = count($sequences) > 0 ? max($sequences) + 1 : 1;

        $formattedNumber = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        return "P-$currentYear-8/$formattedNumber";
    }



    public function send_email(Request $request, $id)
    {
        $presupuesto = Presupuestos::findOrFail($id);
        $cliente = $presupuesto->cliente;

        if (!$cliente) {
            return response()->json(['error' => 'Cliente not found for this presupuesto.'], 404);
        }

        // Ensure 'emails' is provided and is an array
        if (!$request->has('emails') || !is_array($request->emails)) {
            return response()->json(['error' => 'Emails array is required.'], 400);
        }

        $pdfContent = Pdf::loadView('Documents.presupuestoPDF', ['presupuesto' => $presupuesto])->output();

        // Construct the email message
        $message = "Estimado/a " . $cliente->nombre . ",\n\n";
        $message .= "Adjuntamos el presupuesto solicitado en formato PDF.\n\n";
        $message .= "Si tiene alguna consulta, no dude en contactarnos.\n\n";
        $message .= "Saludos cordiales,\n";
        $message .= "El equipo de " . config('app.name');


        // Call the email response function with multiple recipients
        $this->PresupuestoEmail($request->emails, $message, $pdfContent);
        return null;
    }
    public function reportPresupuestos(Request $request)
    {
        $presupuestosIds = collect($request->presupuestosIds);

        $presupuestos = Presupuestos::whereIn('id', $presupuestosIds)->get()->load('cliente');

        // Check if there are no presupuestos
        if ($presupuestos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron presupuestos'], 404);
        }

        // Export based on the requested format
        $format = $request->input('format');
        if ($format === 'excel') {
            return Excel::download(new PresupuestosExport($presupuestos), "informe_presupuestos.xlsx");
        } elseif ($format === 'pdf') {
            $pdf = PDF::loadView('Documents.libro-de-presupuestos', ['presupuestos' => $presupuestos]);
            $fileName = "informe_presupuestos.pdf";
            return $pdf->download($fileName);
        }

        return response()->json(['message' => 'Formato no válido'], 400);
    }

    private function PresupuestoEmail(array $emails, $message, $pdf)
    {
        $myEmail = ConfigHelper::get('email');

        // Merge the provided emails with your configured email
        $recipients = array_merge($emails, [$myEmail]);

        // Send the email to all recipients
        Mail::to($recipients)->send(new PresupuestoEmail($message, $pdf));
    }

}
