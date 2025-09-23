<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class FaceB2BService
{
    protected \GuzzleHttp\Client $client;
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = env('FACEB2B_API_URL', 'https://api.faceb2b.com'); // Replace with actual API URL
        $this->apiKey = env('FACEB2B_API_KEY'); // Add your API key to .env
    }

    public function sendInvoice($factura)
    {
        // Define XML file path
        $xmlFilePath = "public/facturas/factura_" . $factura->hash . ".xsig";
        $fullPath = storage_path("app/" . $xmlFilePath);

        if (!file_exists($fullPath)) {
            return ['success' => false, 'message' => 'Factura XML file not found'];
        }

        try {
            $response = $this->client->request('POST', "{$this->apiUrl}/send-invoice", [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Accept' => 'application/json'
                ],
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($fullPath, 'r'),
                        'filename' => basename($fullPath)
                    ]
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            return ['success' => true, 'response' => $body];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function checkInvoiceStatus($invoiceId)
    {
        try {
            $response = $this->client->request('GET', "{$this->apiUrl}/invoice-status/{$invoiceId}", [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Accept' => 'application/json'
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            return ['success' => true, 'status' => $body];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
