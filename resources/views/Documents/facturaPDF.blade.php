<!DOCTYPE html>
<html lang="es">

<head>
    <title>Factura({{ $factura->numFactura }})</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                                <b class="text-secondary" style="font-size: 16px;">
                                    {{ in_array($factura->serie, [7, 11]) ? 'Proforma' : 'Factura' }}-{{ $factura->numFactura }}
                                </b>
                                <br>
                                <b>Fecha de la {{ in_array($factura->serie, [7, 11]) ? 'Proforma' : 'Factura' }}:
                                    {{ \Carbon\Carbon::parse($factura->fechaInicio)->format('d/m/Y') }}</b> <br><br>
                                <b style="font-size: 16px;"
                                    class="text-weight-bold">{{ \App\Helpers\ConfigHelper::get('business_name') }}</b><br><br>
                                <b>NIF/CIF:</b>{{ \App\Helpers\ConfigHelper::get('tax_id') }}<br>
                                <b>Dirección:</b>{{ \App\Helpers\ConfigHelper::get('address') }} -
                                {{ \App\Helpers\ConfigHelper::get('town') }} ,
                                {{ \App\Helpers\ConfigHelper::get('postal_code') }} ,
                                {{ \App\Helpers\ConfigHelper::get('province') }} <br>
                                <b>TEL:</b> {{ \App\Helpers\ConfigHelper::get('phone') }} <br>
                                <b>Email:</b> {{ \App\Helpers\ConfigHelper::get('email') }} <br>
                                <strong>Metodo de pago:</strong>
                                <span>{{ $factura->metodo_text }}</span><br>
                                <span>
                                    <strong>Fecha de vencimiento:</strong>
                                </span>
                                {{ \Carbon\Carbon::parse($factura->fechaFin)->format('d/m/Y') }}
                            </p>
                        </td>
                        <td colspan="4" style="text-align: right;">
                            <b style="font-size: 16px;" class="text-uppercase text-weight-bold">
                                {{ $factura->cliente_nombre ??
                                    ($factura->cliente
                                        ? trim(
                                            implode(
                                                ' ',
                                                array_filter([
                                                    $factura->cliente?->nombre,
                                                    $factura->cliente?->apellido_1,
                                                    $factura->cliente?->apellido_2,
                                                ]),
                                            ),
                                        )
                                        : 'blanco/vacio') }}
                            </b>
                            <br><br>
                            <b>NIF:</b> {{ $factura->cliente_dni ?? ($factura->cliente?->dni ?? 'blanco/vacio') }}<br>
                            <b>Teléfono: </b>
                            {{ $factura->cliente_telefono ??
                                ($factura->cliente?->telefono_fijo ?? ($factura->cliente?->telefono_mobile ?? 'blanco/vacio')) }}<br>
                            <b>Email: </b>
                            {{ $factura->cliente_email ?? ($factura->cliente?->email ?? 'blanco/vacio') }}<br>
                            <b>Dirección:</b>
                            {{ // Dirección (localidad, dirección, CP)
                                $direccion =
                                    $factura->cliente_localidad || $factura->cliente_direccion || $factura->cliente_cp
                                        ? implode(
                                            ', ',
                                            array_filter([
                                                $factura->cliente_localidad ?? null,
                                                $factura->cliente_direccion ?? null,
                                                $factura->cliente_cp ?? null,
                                            ]),
                                        )
                                        : ($factura->cliente
                                            ? implode(', ', [
                                                $factura->cliente->localidad ?? 'blanco/vacio',
                                                $factura->cliente->direccion ?? 'blanco/vacio',
                                                $factura->cliente->cp ?? 'blanco/vacio',
                                            ])
                                            : 'blanco/vacio') }}
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
                    @if (!empty($factura->servicios))
                        @foreach ($factura->servicios as $indice => $servicio)
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
                                            <p class="text-muted p-0 m-0" style="font-size:10px;">
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
                    {{-- Verificar si existe un presupuesto relacionado --}}
                    @php
                        $presupuestoRelacionado = '';
                    @endphp
                    @if ($factura->presupuesto)
                        @php
                            $presupuestoRelacionado =
                                'Esta factura está relacionada con el presupuesto : ' .
                                $factura->presupuesto->numPresupuesto;
                        @endphp
                    @endif

                    {{-- Verificar si existe un concepto --}}
                    @php
                        $concepto = '';
                    @endphp
                    @if ($factura->concepto !== null)
                        @php
                            $concepto = $factura->concepto;
                        @endphp
                    @endif

                    {{-- Buscar la factura nativa y verificar si existe --}}
                    @php
                        $factura_nativaRelacionado = '';
                        $factura_nativa = \App\Models\Facturas::find($factura->factura_nativa);
                        if ($factura_nativa !== null) {
                            $factura_nativaRelacionado =
                                'Esta factura está rectificada de la factura : ' . $factura_nativa->numFactura;
                        }
                    @endphp

                    <td colspan="5" class="border-top">
                        <div class="tptamAmount" style="font-size:10px;">
                            <p class="d-flex"><br>
                                Total sin IVA : {{ number_format($factura->total_sin_iva, 2, ',', '.') }}€<br>
                                IVA : {{ $factura->iva }}%
                                ({{ number_format(($factura->total_sin_iva * $factura->iva) / 100, 2, ',', '.') }}€)<br>
                                IRPF : {{ $factura->irpf }}%
                                ({{ number_format(($factura->total_sin_iva * $factura->irpf) / 100, 2, ',', '.') }}€)<br>
                                RETENCIONES : {{ $factura->RetentionPercentage() }}%
                                ({{ number_format($factura->Retention(), 2, ',', '.') }}€)<br>
                                <strong style="font-size:20px;">Total :
                                    {{ number_format($factura->total, 2, ',', '.') }}€</strong><br>
                                {{ $factura_nativaRelacionado }}<br>
                                @if ($factura->observaciones)
                                    <hr><b>Observaciones:</b><br><br> {{ $factura->observaciones }} <br><br><br>
                                @endif
                                @if ($factura->condiciones)
                                    <br><b>Pliegos y condiciones:</b><br>{{ $factura->condiciones }}
                                    <hr>
                                @endif
                                {{ $concepto }}
                            </p>
                            <hr>
                            @if ($factura->hash && !in_array($factura->serie, [7, 11]))
                                <br>
                                <p>Código de verificación de factura: {{ $factura->hash }}</p>
                                <hr>
                                <div class="position-relative" style="height: 100px;">
                                    <p class="me-2">QR de verificación:</p><br>
                                    <img class="position-absolute"
                                        src="data:image/svg+xml;base64, {{ $factura->qr_code }}" alt="QR Code"
                                        style="height:100px;width:100px;margin-left:90px;top:0;">
                                </div>
                                <hr>
                            @endif
                        </div>
                    </td>
                </tfoot>

            </table>
            <hr>


        </div>

    </div>

    <script type="text/php">
        if (isset($pdf) && $PAGE_COUNT > 0) {
            $footerText = \App\Helpers\ConfigHelper::get('footer_text');
            $fontSize = 6;
            $font = $fontMetrics->getFont("Verdana");
            $maxWidth = 1200; // Set the maximum width in pixels
            $x = 30;
            $initialY = $pdf->get_height() - 40; // Ajuste la posición inicial de Y según sea necesario

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
