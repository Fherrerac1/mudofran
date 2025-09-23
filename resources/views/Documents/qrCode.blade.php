<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice QR Code</title>
</head>
<body>
    <h1>QR Code for Invoice</h1>
    @if($factura->qr_code)
        <img src="{{ $factura->qr_code }}" alt="Invoice QR Code">
    @else
        <p>QR Code could not be generated.</p>
    @endif
</body>
</html>
