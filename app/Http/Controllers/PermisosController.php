<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PermisosController extends Controller
{
    public function chmod()
    {
        // Asegúrate de obtener la ruta al directorio public
        $rutaPublic = base_path();

        // Comando para cambiar permisos de todos los directorios a 755 de manera recursiva
        $comandoDirectorios = "find '{$rutaPublic}' -type d -exec chmod 755 {} +";
        // dd($comandoDirectorios);
        // Comando para cambiar permisos de todos los archivos a 644 de manera recursiva
        $comandoArchivos = "find '{$rutaPublic}' -type f -exec chmod 644 {} +";

        $this->procesarPermisos($comandoDirectorios);
        $this->procesarPermisos($comandoArchivos);
    }

    private function procesarPermisos($comando)
    {
        // Ejecutar el comando
        $output = shell_exec($comando);

        // Verificar la salida del comando
        if ($output === '' || $output === '0' || $output === false || $output === null) {
            // echo "<p>Permisos aplicados correctamente en la carpeta public.</p>";
        } else {
            // echo "<p>Error en la ejecución del comando:</p>";
            // echo "<pre>$output</pre>";
        }
    }
}
