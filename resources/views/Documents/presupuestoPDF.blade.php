<!DOCTYPE html>
<html lang="es">

<head>

    <title>Presupuesto({{ $presupuesto->numPresupuesto }})</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style>
        html {
            line-height: 1.15;
            margin: 0;
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

        footer {
            position: absolute;
            bottom: 0px;
            width: 90%;
            margin: auto;
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
            margin-top: -30px;
            margin-left: -30px;
        }

        .main-div {
            max-height: 80%;
            position: relative;
            margin-bottom: 30px;
        }

        .style_color {
            background-color: {
                    {
                    \App\Helpers\ConfigHelper: :get('style_color')
                }
            }

            ;

            color: {
                    {
                    \App\Helpers\ConfigHelper: :get('text_color')
                }
            }

            ;
        }

        .separar {
            display: flex;
            /* Aplicando Flexbox */
            justify-content: space-between;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="main-div">

        <div class="pdfContent">


            <table class="table-borderless" style="width:100%">
                <thead>
                    <tr>
                        <td colspan="5">
                            <div class="separar w-100" style="display: flex;">
                                <div class="mt-3" style="flex: 1;">
                                    <img class="pl-4" style="width:280px;margin-left: -30px;margin-top: -50px;"
                                        src="{{ public_path() . '/storage/' . \App\Helpers\ConfigHelper::get('main_logo') }}"
                                        alt="Responsive image">
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="1" style="width:65%">
                            <p style="font-size: 10px;">
                                <b class="text-secondary"
                                    style="font-size: 16px;">Presupuesto-{{ $presupuesto->numPresupuesto }}</b><br>
                                <b>Fecha del presupuesto:
                                    {{ \Carbon\Carbon::parse($presupuesto->fechaInicio)->format('d/m/Y') }}</b><br><br>
                                <b style="font-size: 16px;"
                                    class="text-weight-bold">{{ \App\Helpers\ConfigHelper::get('business_name') }}</b><br><br>
                                <b>NIF/CIF:</b>{{ \App\Helpers\ConfigHelper::get('tax_id') }}<br>
                                <b>Dirección:</b>{{ \App\Helpers\ConfigHelper::get('address') }} -
                                {{ \App\Helpers\ConfigHelper::get('town') }} ,
                                {{ \App\Helpers\ConfigHelper::get('postal_code') }} ,
                                {{ \App\Helpers\ConfigHelper::get('province') }} <br>
                                <b>TEL:</b> {{ \App\Helpers\ConfigHelper::get('phone') }} <br>
                                <b>Email:</b> {{ \App\Helpers\ConfigHelper::get('email') }} <br>
                                <strong>Metodo de pago:</strong> <span>{{ $presupuesto->metodo_text }}</span><br>
                                <span><strong>Fecha de vencimiento:</strong></span>
                                {{ \Carbon\Carbon::parse($presupuesto->fechaFin)->format('d/m/Y') }}
                            </p>

                        </td>
                        <td colspan="4" style="text-align: right;">
                            @php
                                // Nombre completo
                                $nombreCompleto = $presupuesto->cliente_nombre
                                    ?? ($presupuesto->cliente
                                        ? trim(implode(' ', array_filter([
                                            $presupuesto->cliente->nombre ?? null,
                                            $presupuesto->cliente->apellido_1 ?? null,
                                            $presupuesto->cliente->apellido_2 ?? null,
                                        ])))
                                        : ($presupuesto->contacto->nombre ?? 'blanco/vacio'));

                                // DNI
                                $dni = $presupuesto->cliente_dni
                                    ?? ($presupuesto->cliente->dni ?? 'blanco/vacio');

                                // Teléfono
                                $telefono = $presupuesto->cliente_telefono
                                    ?? $presupuesto->cliente->telefono_fijo
                                    ?? $presupuesto->cliente->telefono_mobile
                                    ?? $presupuesto->contacto->telefono_mobile
                                    ?? 'blanco/vacio';

                                // Email
                                $email = $presupuesto->cliente_email
                                    ?? $presupuesto->cliente->email
                                    ?? $presupuesto->contacto->email
                                    ?? 'blanco/vacio';

                                // Dirección (localidad, dirección, CP)
                                $direccion = $presupuesto->cliente_localidad || $presupuesto->cliente_direccion || $presupuesto->cliente_cp
                                    ? implode(', ', array_filter([
                                        $presupuesto->cliente_localidad ?? null,
                                        $presupuesto->cliente_direccion ?? null,
                                        $presupuesto->cliente_cp ?? null,
                                    ]))
                                    : ($presupuesto->cliente
                                        ? implode(', ', [
                                            $presupuesto->cliente->localidad ?? 'blanco/vacio',
                                            $presupuesto->cliente->direccion ?? 'blanco/vacio',
                                            $presupuesto->cliente->cp ?? 'blanco/vacio',
                                        ])
                                        : 'blanco/vacio');

                            @endphp

                            <b style="font-size: 16px;" class="text-uppercase text-weight-bold">
                                {{ $nombreCompleto }}
                            </b>
                            <br><br>

                            @if ($presupuesto->cliente)
                                <b>NIF:</b> {{ $dni }}<br>
                            @endif

                            <b>Teléfono:</b> {{ $telefono }}<br>
                            <b>Email:</b> {{ $email }}<br>
                            <b>Dirección:</b> {{ $direccion }}
                        </td>
                    </tr>

                    <tr class="style_color">
                        <th class="p-2" scope="col-8">Concepto</th>
                        <th class="p-2" scope="col-1">Cantidad</th>
                        <th class="p-2" scope="col-1">€/un.</th>
                        <th class="p-2" scope="col-1">Dto.</th>
                        <th class="p-2" scope="col-1">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($presupuesto->servicios))
                        @foreach ($presupuesto->servicios as $indice => $servicio)
                            <tr class="m-1" style="border-top: 1px solid rgb(194, 194, 194)">
                                <td>
                                    <b style="font-size: 12px;">{{ $servicio['contenido'] }}</b>
                                    <p class="text-muted p-0 m-0" style="font-size: 10px;">
                                        {{ $servicio['descripcion'] }}
                                    </p>
                                </td>
                            </tr>

                            {{-- Loop productos if any --}}
                            @if (!empty($servicio['productos']))
                                @foreach ($servicio['productos'] as $producto)
                                    <tr class="p-0 m-0">
                                        <td style="width: 70%; padding-left: 20px;">
                                            <b style="font-size: 12px;">* {{ $producto['nombre'] }}</b>
                                            <p class="text-muted p-0 m-0" style="font-size: 10px;">
                                                {{ $producto['descripcion'] ?? '' }}
                                            </p>
                                        </td>
                                        <td style="width: 10%;">{{ $producto['cantidad'] ?? '' }}</td>
                                        <td style="width: 10%;">{{ $producto['precio'] ?? '' }}</td>
                                        <td style="width: 10%;">
                                            @if (!empty($producto['descuento']) && $producto['descuento'] > 0)
                                                {{ $producto['descuento'] }}%
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            @if (!empty($producto['precio_sin_iva']) && $producto['precio_sin_iva'] != 0)
                                                {{ $producto['precio_sin_iva'] }}€
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                        @endforeach
                    @endif
                </tbody>

                <tfoot>
                    <td colspan="5">
                        <div class="tptamAmount" style="font-size: 10px;">
                            {{-- <br>{{ $notes }} --}}
                            <br>Total sin IVA : {{ number_format($presupuesto->total_sin_iva, 2, ',', '.') }}€<br>
                            IVA : {{ $presupuesto->iva }}%
                            ({{ number_format(($presupuesto->total_sin_iva * $presupuesto->iva) / 100, 2, ',', '.') }}€)
                            <br>IRPF : {{ $presupuesto->irpf }}%
                            ({{ number_format(($presupuesto->total_sin_iva * $presupuesto->irpf) / 100, 2, ',', '.') }}€)<br>
                            <strong style="font-size:20px;">Total :
                                {{ number_format($presupuesto->total, 2, ',', '.') }}€</strong>
                            <br>
                            @if ($presupuesto->observaciones)
                                <hr><b>Observaciones:</b><br><br> {{ $presupuesto->observaciones }} <br><br><br>
                            @endif
                            @if ($presupuesto->condiciones)
                                <br><b>Pliegos y condiciones:</b><br>{{ $presupuesto->condiciones }}
                                <hr>
                            @endif

                            <br>
                            <p class="mb-0"><b>Estado:</b></p>
                            @if ($presupuesto->estado == 0 || $presupuesto->estado === 'Pendiente')
                                <p>El presupuesto está en estado pendiente.</p>
                            @elseif ($presupuesto->estado == '1' || $presupuesto->estado === 'Rechazado')
                                <p>El presupuesto ha sido rechazado.</p>
                            @elseif ($presupuesto->estado == '2' || $presupuesto->estado === 'Aceptado')
                                <p>El presupuesto ha sido firmado el
                                    {{ \Carbon\Carbon::parse($presupuesto->firmado)->format('d/m/Y H:i') }}.
                                </p>
                            @endif
                        </div>
                    </td>
                </tfoot>
            </table>
            <hr>
        </div>

    </div>
    <div class="imagenes">
        @foreach ($presupuesto->fotos as $imagen)
            @if (substr($imagen, -4) !== '.pdf')
                <!-- Check if the file doesn't end with .pdf -->
                <div class="w-100 img-dev">
                    <img style="max-height:100%; width:100%; object-fit:contain;"
                        src="{{ public_path() . '/storage/' . $imagen }}" class="d-block" alt="Foto">
                </div>
            @endif
        @endforeach
    </div>

    <script type="text/php">
        if (isset($pdf) && $PAGE_COUNT > 0) {
            $footerText = \App\Helpers\ConfigHelper::get('footer_text');
            $fontSize = 6;
            $font = $fontMetrics->getFont("Verdana");
            $maxWidth = 1200; // Set the maximum width in pixels
            $x = 30;
            $initialY = $pdf->get_height() - 50; // Ajuste la posición inicial de Y según sea necesario

            // Divide el texto en fragmentos para que se ajuste dentro del ancho máximo
            $chunks = wordwrap($footerText, ceil($maxWidth / $fontSize), "\n", true);

            // Muestra cada fragmento en una nueva línea dentro del área especificada
            $lines = explode("\n", $chunks);
            $y = $initialY; // Inicia desde la posición inicial de Y
            foreach ($lines as $line) {
                $pdf->page_text($x, $y, $line, $font, $fontSize);
                $y += $fontSize + 2; // Ajusta el espaciado entre líneas según sea necesario
            }
        }
    </script>




    <script type="text/php">
        if (isset($pdf) && $PAGE_COUNT > 1) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 10;
            $font = $fontMetrics->getFont("Verdana");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width);
            $y = $pdf->get_height() + 20 - $pdf->get_height();
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>

</html>