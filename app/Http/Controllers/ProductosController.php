<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\Productos;
use App\Models\Servicios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ProductosExport;
use Inertia\Inertia;

class ProductosController extends Controller
{

    public function index()
    {
        // dd($request->all());
        $user = Auth::user();

        $servicios = Servicios::all()->load('productos');
        $productos = Productos::all()->load('servicio');

        return Inertia::render('Cruds/Servicios/Index', [
            'user' => $user,
            'servicios' => $servicios,
            'productos' => $productos,
            'pageType' => 'productos_page',
        ]);
    }
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate the request data
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'medida' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:1000',
            'servicio_id' => 'required|exists:servicios,id', // Assuming 'servicios' is the related table
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Validation for file uploads (10MB max)
        ]);

        $data = $request->except('documents');
        $producto = new Productos($data);

        // Handle document uploads if present
        $documents = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                // Store the file and get the URL
                $path = $document->store('public/productos');
                $documents[] = $path;  // Save the file URL in the array
            }
        }

        $producto->documents = $documents; // Save the documents as a JSON array
        // Create a new "producto" entry
        $producto->save();

        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'CREAR producto ' . $producto->nombre;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return redirect()->route('productos.index');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Validate the request data
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'medida' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:1000',
            'servicio_id' => 'required|exists:servicios,id', // Assuming 'servicios' is the related table
        ]);

        // Find the existing product by ID
        $producto = Productos::findOrFail($id);

        // Get all input data except for 'documents'
        $data = $request->except('documents');

        $producto->update($data);
        // Initialize the documents array with existing documents
        $documents = [];
        // Handle document uploads if they exist in the request
        if ($request->has('documents')) {
            foreach ($request->documents as $index => $document) {
                // If the document is a string (no validation needed, just push to array)
                if (is_string($document)) {
                    $documents[] = $document; // Directly push the string (URL or path)
                }
                // If the document is a file (check if it's an instance of UploadedFile)
                elseif ($document instanceof \Illuminate\Http\UploadedFile) {
                    // Validate the document only if it's a file
                    $this->validate($request, [
                        "documents.{$index}" => 'mimes:jpg,jpeg,png,pdf,doc,docx|max:10240', // Validation for current file
                    ]);
                    // Store the file and get the path
                    $path = $document->store('public/productos');
                    $documents[] = $path;  // Save the file URL in the array
                }
            }
        }

        foreach ($producto->documents as $path) {
            if (!in_array($path, $request->documents) && Storage::exists($path)) {
                Storage::delete($path);
            }
        }

        // Update the documents in the "producto" (save as JSON in the database)
        $producto->documents = $documents;
        $producto->save();

        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ACTUALIZAR producto ' . $producto->nombre;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();

        return redirect()->route('productos.index');
    }

    public function show($id)
    {
        $user = Auth::user();
        $producto = Productos::findOrFail($id)->load('servicio');

        return Inertia::render('Cruds/Servicios/Productos/Show', [
            'user' => $user,
            'producto' => $producto,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        // Find the product by ID
        $producto = Productos::findOrFail($id);

        // Delete associated documents from storage (if any)
        if ($producto->documents) {
            $documents = $producto->documents;
            foreach ($documents as $documentPath) {
                Storage::delete($documentPath); // Delete each document file
            }
        }

        // Delete the product from the database
        $producto->delete();

        // Log the action
        $log = new AccessLog();
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->action = 'ELIMINAR producto ' . $producto->nombre;
        $log->ip_address = request()->ip();
        $log->user_agent = request()->header('User-Agent');
        $log->save();

        return redirect()->route('productos.index');
    }


    public function getProductosData($id)
    {
        $producto = Productos::findOrFail($id);
        return response()->json($producto);
    }

    public function getAllProductos(Request $request)
    {
        $term = $request->input('q');
        $query = Productos::query();

        if ($term) {
            $query->where('nombre', 'LIKE', $term . '%');
        }

        $productos = $query->limit(15)->get();

        return response()->json($productos);
    }

    /**
     * Genera un informe de los poductos seleccionados en el formato especificado.
     */
    public function reportProductos(Request $request)
    {
        // Coge los IDs de los poductos seleccionados
        $productos = Productos::whereIn('id', $request->itemsIds)->get();

        // Verifica si se encontraron poductos
        if ($productos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron servicios'], 404);
        }

        $format = $request->input('format');

        if ($format === 'excel') {
            return Excel::download(new ProductosExport($productos), "informe_poductos.xlsx");
        } elseif ($format === 'pdf') {
            $pdf = PDF::loadView('Documents.reporte-productos', ['productos' => $productos]);
            return $pdf->download("informe_poductos.pdf");
        }

        return response()->json(['message' => 'Formato no válido'], 400);
    }
}
