<!DOCTYPE html>
<html lang="es">

<head>
    <title>Modelo 347</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style>
        html {
            line-height: 1.15;
            margin: 0;
        }

        #table::after {
            content: "";
            clear: both;
            display: block;
            height: max-content;
        }


        .imgHeader,
        .infoHeader {
            float: left;
        }

        .d {
            font-size: 13px;
        }

        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        table,
        th,
        tr,
        td,
        p,
        div {
            line-height: 1.1;
        }

        td:nth-child(n+2) {
            text-align: center;
        }

        th {
            font-size: 13px;
        }

        th:nth-child(n+2) {
            text-align: center;
        }

        footer .amount {
            border-top: 2px solid #2e409c;
            border-bottom: 2px solid #2e409c;
        }



        body {
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #fff;
            font-size: 10px;
            margin: 36pt;
            margin-bottom: 10px
        }

        .pdfContent table.table {}

        .pdfContent {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .img-dev {
            position: relative;
            min-height: 100%;
            page-break-before: always;
        }

        .img-dev img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

        }

        .imgHeader img {
            max-width: 280px;
            height: auto;
            border-style: none;
            margin-top: -30px;
            margin-left: -30px;
        }

        .main-div {
            max-height: 90%;
            min-height: 90%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        footer {
            position: absolute;
            bottom: 0px;
        }

        .style_color {
            background-color: {{ \App\Helpers\ConfigHelper::get('style_color') }};
            color: {{ \App\Helpers\ConfigHelper::get('text_color') }};
        }
    </style>
</head>

<body>
    <div class="main-div">
        <div id="table" class="mb-1">
            <div class="imgHeader">
                <img src="{{ public_path() . '/storage/' . \App\Helpers\ConfigHelper::get('main_logo') }}" alt="Responsive image">
                <br>
                <div class="infoHeader">
                    <div><b>Cliente: {{ $cliente->nombre }}</b> <br> <b>NIF:</b>
                        {{ $cliente->dni }}</div>
                    <div><b>Teléfono: </b> {{ $cliente->telefono }}<br>
                        <b>Email: </b> {{ $cliente->email }}<br>
                        <b>Dirección:</b>
                        {{ $cliente->direccion }} , {{ $cliente->cp }}<br>
                        {{ $cliente->pais }}
                    </div>
                </div>
            </div>
            <div class="infoHeader2">
                <div style="text-align: right">
                    <p>
                        <b class="text-secondary" style="font-size: 16px">Modelo 347</b>
                        <br>
                    <p><b style="font-size: 16px;"
                            class="text-weight-bold">{{ \App\Helpers\ConfigHelper::get('business_name') }}</b> </p>
                    <b>NIF/CIF:</b>{{ \App\Helpers\ConfigHelper::get('tax_id') }}<br>
                    <b>Dirección:</b>{{ \App\Helpers\ConfigHelper::get('address') }} -
                    {{ \App\Helpers\ConfigHelper::get('town') }} <br>
                    {{ \App\Helpers\ConfigHelper::get('postal_code') }} ,
                    {{ \App\Helpers\ConfigHelper::get('province') }}<br>
                    <b>TEL:</b> {{ \App\Helpers\ConfigHelper::get('phone') }} <br>
                    <b>Email:</b> {{ \App\Helpers\ConfigHelper::get('email') }} <br>
                    </p>
                </div>
            </div>
        </div>
        <hr>
        <div id="table" class="my-2">
        </div>
        <div class="pdfContent">
            <table class="table" style="width:100%">
                <thead class="style_color">
                    <tr>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 18%;">Nº Factura</th>
                        <th style="width: 10%;">Presupuesto</th>
                        <th style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($cliente->facturas) && !empty($cliente->facturas))
                        @foreach ($cliente->facturas as $indice => $factura)
                            <tr>
                                <td>{{ $factura->fechaInicio }}</td>
                                <td>{{ $factura->numFactura }}</td>
                                <td>{{ $factura->presupuesto ? $factura->presupuesto->numPresupuesto : 'no asignado' }}
                                </td>
                                <td>{{ number_format($factura->total - $factura->Retention(), 2, ',', '.') }} €</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <div class="tptamAmount">
                <h4><span>TOTAL FACTURADO : {{ number_format($cliente->total_facturas, 2, ',', '.') }} €</span></h4>
                <hr>
            </div>
            <script type="text/php">
                if (isset($pdf) && $PAGE_COUNT > 1) {
                    $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Verdana");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width);
                    $y = $pdf->get_height() - 20;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
</body>

</html>
