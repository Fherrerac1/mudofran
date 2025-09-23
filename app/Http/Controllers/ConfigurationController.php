<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConfigurationController extends Controller
{
    public function index()
    {
        // Obtener la configuración o crear una nueva instancia si no existe
        $configuration = Configuration::first() ?? new Configuration;


        return Inertia::render('Configuration', [
            'old_configuration' => $configuration,
        ]);
    }


    public function update(Request $request)
    {
        $user = Auth::user();
        if ($user->rol !== 'super') {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized access');
        }
        $data = $request->validate([
            'fecha_fin' => 'required|date',
            'series' => 'nullable',
            'series_types' => 'nullable',
            'factura_mode' => 'nullable',
            'tecnicos' => 'nullable',
            'serial_num' => 'required|string',
            'url_app' => 'required|string',
            'footer_text' => 'nullable|string',
            'business_name' => 'nullable|string',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'phone' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'town' => 'nullable|string',
            'style_color' => 'nullable|string',
            'text_color' => 'nullable|string',
            'province' => 'nullable|string',
            'email' => 'nullable|string',
            'business_type' => 'nullable|in:individual,corporate',
            'video' => 'nullable|mimes:mp4',
            'App_Logo' => 'nullable|image|mimes:png',
            'logo_white' => 'nullable|image|mimes:png',
            'favicon' => 'nullable|mimes:ico',
            'sidebar_image' => 'nullable|image|mimes:png',
            'color' => 'nullable|string',
        ]);

        // Procesamiento de subida de archivos
        foreach (['App_Logo', 'logo_white', 'sidebar_image', 'favicon'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                // Asigna un nombre específico al archivo
                $filename = $fileKey === 'favicon' ? 'favicon.ico' : $fileKey . '.png';
                // Define la ruta completa donde se guardará el archivo
                $destinationPath = public_path('images');
                // Mueve el archivo a la carpeta 'public/images'
                $request->file($fileKey)->move($destinationPath, $filename);
                // Guarda la ruta del archivo en el array de datos
                // Aquí, ajusta la ruta según cómo desees acceder a los archivos
                $data[$fileKey] = 'images/' . $filename;
            }
        }

        foreach (['video'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $filename = $fileKey . '.mp4';
                $destinationPath = public_path('video');
                $request->file($fileKey)->move($destinationPath, $filename);
                $data[$fileKey] = 'video/' . $filename;
            }
        }

        // Obtener o crear una nueva configuración
        $configuration = Configuration::first() ?? new Configuration;
        // Actualización de la configuración
        $configuration->fill($data);
        $configuration->save();

    }
}
