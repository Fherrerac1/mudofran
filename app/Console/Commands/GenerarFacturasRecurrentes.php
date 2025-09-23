<?php

namespace App\Console\Commands;

use App\Http\Controllers\FacturasController;
use App\Http\Controllers\VerifactuController;
use Illuminate\Console\Command;
use App\Models\Factura;
use App\Models\FacturaRecurrente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerarFacturasRecurrentes extends Command
{
    protected $signature = 'facturas:generar-recurrentes';
    protected $description = 'Genera nuevas facturas recurrentes según la configuración';

    public function handle()
    {
        $hoy = Carbon::today();

        // Fetch active recurrent invoices
        $facturasRecurrentes = FacturaRecurrente::whereDate('fechaInicio', '<=', $hoy)
            ->where(function ($query) use ($hoy) {
                $query->whereNull('fechaFin')->orWhere('fechaFin', '>=', $hoy);
            })
            ->get();

        foreach ($facturasRecurrentes as $recurrente) {
            $factura = $recurrente->factura;

            // Skip if the factura doesn't exist or if the recurrence has reached its limit
            if (
                !$factura ||
                ($recurrente->repeticiones && $recurrente->repeticiones_actuales >= $recurrente->repeticiones)
            ) {
                continue;
            }

            // Check if it's time to generate a new factura based on proxima_fecha
            $proximaFecha = Carbon::parse($recurrente->proxima_fecha);

            // Skip if the proxima_fecha is not today or in the future
            if ($proximaFecha->gt($hoy)) {
                continue;
            }

            // Create the factura number
            $facturaController = new FacturasController();
            $numFactura = $facturaController->generateFacturaNumber($factura->serie);

            // Clone the original invoice
            $nuevaFactura = $factura->replicate();
            $nuevaFactura->numFactura = $numFactura;
            $nuevaFactura->estado = 0;
            $factura->hash = $facturaController->calcularHuella($factura);
            $nuevaFactura->fechaInicio = $hoy;  // or $proximaFecha depending on logic
            $nuevaFactura->fechaFin = $hoy;     // example for monthly recurrence
            $nuevaFactura->save();

            // Send to Verifactu
            $verifactuController = new VerifactuController();
            $response = $verifactuController->sendInvoice($nuevaFactura->id);

            // If failed, delete factura and log error
            if (!$response || !$response->getData()->success) {
                Log::error('Error al enviar la factura a Verifactu', [
                    'factura_id' => $nuevaFactura->id,
                    'recurrente_id' => $recurrente->id,
                    'response' => $response ? $response->getData() : null,
                ]);

                $nuevaFactura->delete();
                continue;
            }

            // Calculate next recurrence date
            $nuevaFecha = $recurrente->calcularProximaFecha();

            // Update recurrence info
            $recurrente->update([
                'proxima_fecha' => $nuevaFecha,
                'repeticiones_actuales' => $recurrente->repeticiones_actuales + 1,
            ]);

            $this->info('Nueva factura generada con ID: ' . $nuevaFactura->id);
        }
    }
}
