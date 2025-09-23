<!DOCTYPE html>
<html lang="es">

<head>
    <title>Libro de Facturas</title>

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
            background-color:
                {{ \App\Helpers\ConfigHelper::get('style_color') }}
            ;
            color:
                {{ \App\Helpers\ConfigHelper::get('text_color') }}
            ;
        }
    </style>
</head>

<body>
    <div class="main-div">
        <div id="table" class="mb-1">
            <div class="imgHeader">
                <img src="{{ public_path() . '/storage/' . \App\Helpers\ConfigHelper::get('main_logo') }}"
                    alt="Responsive image">
            </div>
            <div class="infoHeader2">
                <div style="text-align: right">
                    <b class="text-secondary" style="font-size: 16px">Libro de facturas</b>
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
                        <th>Fecha</th>
                        <th>Nº Factura</th>
                        <th>Cliente</th>
                        <th>CIF/NIF</th>
                        <th>Base</th>
                        <th>IVA</th>
                        <th>Retención</th>
                        <th>Cobros Pendientes</th>
                        <th>Total</th>
                        <th>Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($facturas) && !empty($facturas))
                        @foreach ($facturas as $indice => $factura)
                            <tr>
                                <td>{{ $factura->fechaInicio }}</td>
                                <td>{{ $factura->numFactura }}</td>
                                <td>{{ $factura->cliente->nombre }}</td>
                                <td>{{ $factura->cliente->dni }}</td>
                                <td>{{ number_format($factura->total_sin_iva, 2, ',', '.') }}€</td>
                                <td>{{ $factura->iva }}%({{ number_format($factura->Iva(), 2, ',', '.') }}€)</td>
                                <td>{{ $factura->RetentionPercentage() }}%({{ number_format($factura->Retention(), 2, ',', '.') }}€)
                                </td>
                                <td>{{ number_format($factura->totalCobros() - $factura->total, 2, ',', '.') }}€
                                </td>
                                <td>{{ number_format($factura->total, 2, ',', '.') }}€</td>
                                <td>{{ $factura->estado_text }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>

            <table class="table" style="width:100%">
                <thead class="style_color">
                    <tr>
                        <th>IVA Tipo</th>
                        <th>Base</th>
                        <th>Cuota IVA</th>
                        <th>Retención</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalBase = 0;
                        $totalCuotaIVA = 0;
                        $totalRetencion = 0;
                        $totalGeneral = 0;
                        $totalCobrosPendientes = 0;
                    @endphp
                    @foreach ($facturas->sortByDesc('iva')->groupBy('iva') as $iva => $facturasPorIva)
                                        <tr>
                                            <td>{{ $facturasPorIva->first()->iva }}%</td>
                                            <td>{{ number_format($facturasPorIva->sum('total_sin_iva'), 2, ',', '.') }}€</td>
                                            <td>{{ $iva }}%({{ number_format($facturasPorIva->sum('total_iva'), 2, ',', '.') }}€)
                                            </td>
                                            <td>{{ number_format(
                            $facturasPorIva->sum(function ($factura) {
                                return $factura->Retention();
                            }),
                            2,
                            ',',
                            '.',
                        ) }}€
                                            </td>
                                            <td>{{ number_format(
                            $facturasPorIva->sum(function ($factura) {
                                return $factura->total;
                            }),
                            2,
                            ',',
                            '.',
                        ) }}€
                                            </td>
                                        </tr>
                                        @php
                                            $totalBase += $facturasPorIva->sum('total_sin_iva');
                                            $totalCuotaIVA += $facturasPorIva->sum('total_iva');
                                            $totalRetencion += $facturasPorIva->sum(function ($factura) {
                                                return $factura->Retention();
                                            });
                                            $totalGeneral += $facturasPorIva->sum(function ($factura) {
                                                return $factura->total;
                                            });

                                            // Calculate Cobros Pendientes (totalCobros - totalWithRetention)
                                            $totalCobrosPendientes += $facturasPorIva->sum(function ($factura) {
                                                return $factura->totalCobros() - $factura->total;
                                            });
                                        @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td><strong>{{ number_format($totalBase, 2, ',', '.') }}€</strong></td>
                        <td><strong>{{ number_format($totalCuotaIVA, 2, ',', '.') }}€</strong></td>
                        <td><strong>{{ number_format($totalRetencion, 2, ',', '.') }}€</strong></td>
                        <td><strong>{{ number_format($totalGeneral, 2, ',', '.') }}€</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Cobros Pendientes</strong></td>
                        <td colspan="3"><strong>Total de facturas pendientes:</strong>
                            {{ $facturas->where('estado', '!=', 2)->count() }}</td>
                        <td><strong>{{ number_format($totalCobrosPendientes, 2, ',', '.') }}€</strong></td>
                    </tr>
                </tfoot>
            </table>



        </div>
        Creado en {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}

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