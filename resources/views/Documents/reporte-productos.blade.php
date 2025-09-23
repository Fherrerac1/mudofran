<!DOCTYPE html>
<html lang="es">

<head>
    <title>Libro de productos</title>

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

        td {
            font-size: 8px;
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
            margin: 6pt;
            margin-bottom: 10px
        }

        .pdfContent {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .imgHeader img {
            max-height: 100px;
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
            </div>
            <div class="infoHeader2">
                <div style="text-align: right">
                    <p>
                        <b class="text-secondary" style="font-size: 16px">Libro de productos</b>
                    </p>
                </div>
            </div>
        </div>
        <hr>
        <div class="pdfContent">
            <table class="table" style="width:100%">
                <thead class="style_color">
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>Servicio</th>
                        <th>Observaciones</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($productos) && !empty($productos))
                        @foreach ($productos as $producto)
                            <tr>
                                <td>{{ $producto->updated_at->format('d-m-Y') }}</td>
                                <td>{{ $producto->nombre }}</td>
                                <td>{{ $producto->servicio->nombre ?? 'Sin servicio' }}</td>
                                <td>{{ $producto->observaciones }}</td>
                                <td>
                                    {{ number_format($producto->precio ?? 0, 2, ',', '.') }} €
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>

            <p>Creado en {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}</p>

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
        </div>
    </div>
</body>

</html>
