<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Facturas;
use Illuminate\Support\Facades\Http;

class VerifactuController extends Controller
{
    protected $soapClient;
    public function sendInvoice($id)
    {
        $factura = Facturas::with('cliente')->findOrFail($id);

        $confuguration = Configuration::where('tenant_id', $factura->tenant_id)->first();

        $ultimaFactura = Facturas::where('id', '<', (int) $factura->id)
            ->whereIn('serie', ['1', '2', '5'])
            ->orderBy('fechaInicio', 'desc')
            ->first();

        $payload = [
            'tax_id' => $confuguration->tax_id ?? \App\Helpers\ConfigHelper::get('tax_id'),
            'business_name' => $confuguration->business_name ?? \App\Helpers\ConfigHelper::get('business_name'),
            'api_key' => env('VERIFACTU_API_KEY'),
            'numFactura' => $factura->numFactura,
            'cliente_nombre' => trim($factura->cliente->nombre . ' ' . ($factura->cliente->apellido_1 ?? '')),
            'cliente_nif' => $factura->cliente->dni,
            'fechaInicio' => $factura->fechaInicio,
            'hash' => $factura->hash,
            'total_sin_iva' => $factura->total_sin_iva,
            'iva' => $factura->iva,
            'total_iva' => $factura->total_iva,
            'total' => $factura->total,
            'ultimaFactura' => $ultimaFactura ? [
                'numFactura' => $ultimaFactura->numFactura,
                'fechaInicio' => $ultimaFactura->fechaInicio,
                'hash' => $ultimaFactura->hash,
            ] : null,
        ];

        if (is_null($payload['ultimaFactura'])) {
            unset($payload['ultimaFactura']);
        }

        $response = Http::timeout(30)->post('https://app.blackcatsoluciones.es/api/verifactu/create', $payload);

        if (!$response->successful()) {
            abort($response->status(), 'Error al enviar la factura: ' . $response->body());
        }

        $responseData = $response->json();

        // Guardar el QR en la factura si está presente en la respuesta
        if (!empty($responseData['qr_code'])) {
            $factura->qr_code = $responseData['qr_code'];
            $factura->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Factura enviada correctamente.',
            'response' => $responseData,
        ]);
    }

    public function anularInvoice($id)
    {
        $factura = Facturas::with('cliente')->findOrFail($id);

        $confuguration = Configuration::where('tenant_id', $factura->tenant_id)->first();

        $ultimaFactura = Facturas::where('id', '<', (int) $factura->id)
            ->whereIn('serie', ['1', '2', '5'])
            ->orderBy('fechaInicio', 'desc')
            ->first();

        $payload = [
            'tax_id' => $confuguration->tax_id ?? \App\Helpers\ConfigHelper::get('tax_id'),
            'business_name' => $confuguration->business_name ?? \App\Helpers\ConfigHelper::get('business_name'),
            'api_key' => env('VERIFACTU_API_KEY'),
            'numFactura' => $factura->numFactura,
            'fechaInicio' => $factura->fechaInicio,
            'hash' => $factura->hash,
            'total' => $factura->total,
            'ultimaFactura' => $ultimaFactura ? [
                'numFactura' => $ultimaFactura->numFactura,
                'fechaInicio' => $ultimaFactura->fechaInicio,
                'hash' => $ultimaFactura->hash,
            ] : null,
        ];

        if (is_null($payload['ultimaFactura'])) {
            unset($payload['ultimaFactura']);
        }

        $response = Http::timeout(30)->post('https://app.blackcatsoluciones.es/api/verifactu/cancel', $payload);

        if (!$response->successful()) {
            abort($response->status(), 'Error al enviar la factura: ' . $response->body());
        }

        $responseData = $response->json();

        return response()->json([
            'success' => true,
            'message' => 'Factura Anulada correctamente.',
            'response' => $responseData,
        ]);
    }

    public function sendRectificacion($id)
    {
        $factura = Facturas::with('cliente')->findOrFail($id);
        $facturaOriginal = Facturas::find($factura->factura_nativa);

        $confuguration = Configuration::where('tenant_id', $factura->tenant_id)->first();

        if (!$facturaOriginal) {
            return response()->json(['error' => 'Factura original (factura_nativa) no encontrada'], 404);
        }

        $config = [
            'tax_id' => $confuguration->tax_id ?? \App\Helpers\ConfigHelper::get('tax_id'),
            'business_name' => $confuguration->business_name ?? \App\Helpers\ConfigHelper::get('business_name'),
            'api_key' => env('VERIFACTU_API_KEY'),
        ];

        $clienteNombre = trim($factura->cliente->nombre . ' ' . ($factura->cliente->apellido_1 ?? ''));

        $payload = array_merge($config, [
            'numFactura' => $factura->numFactura,
            'cliente_nombre' => $clienteNombre,
            'cliente_nif' => $factura->cliente->dni,
            'fechaInicio' => $factura->fechaInicio,
            'hash' => $factura->hash,
            'total_sin_iva' => $factura->total_sin_iva,
            'iva' => $factura->iva,
            'total_iva' => $factura->total_iva,
            'total' => $factura->total,
            'facturaOriginal' => [
                'numFactura' => $facturaOriginal->numFactura,
                'fechaInicio' => $facturaOriginal->fechaInicio,
                'hash' => $facturaOriginal->hash,
            ],
        ]);

        $response = Http::timeout(30)->post('https://app.blackcatsoluciones.es/api/verifactu/rectificate', $payload);

        if (!$response->successful()) {
            abort($response->status(), 'Error al enviar la factura: ' . $response->body());
        }

        $responseData = $response->json();

        if (!empty($responseData['qr_code'])) {
            $factura->update(['qr_code' => $responseData['qr_code']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Factura rectificada enviada correctamente.',
            'response' => $responseData,
        ]);
    }

}
