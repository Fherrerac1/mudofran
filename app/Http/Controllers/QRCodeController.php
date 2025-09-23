<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QRCodeController extends Controller
{
    public function generateAndDisplayQrCode()
    {
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(400),
                new ImagickImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCode = $writer->writeString('Hello World!');

            // Return the QR code as a response with the appropriate content type
            return response($qrCode, 200, [
                'Content-Type' => 'image/png',
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions or errors here
            return response($e->getMessage(), 500);
        }
    }
}

