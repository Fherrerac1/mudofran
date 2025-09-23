<!DOCTYPE html>
<html lang="es">

<head>

    <title>Control Horario</title>

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
            margin-left: 0px;
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
        <!-- Cabecera con logo y datos de empresa -->
        <div id="table" class="mb-1">
            <div class="imgHeader">
                <img src="{{ public_path() . '/storage/' . \App\Helpers\ConfigHelper::get('main_logo') }}" alt="Logo" >
            </div>

            <div class="infoHeader2">
                <div style="text-align: right">
                    <p>
                        <b class="text-secondary" style="font-size: 16px">Control Horario</b><br>
                        <p>
                            <b style="font-size: 16px;" class="text-weight-bold">{{ \App\Helpers\ConfigHelper::get('business_name') }}</b>
                        </p>
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

        <div class="pdfContent">
            <!-- Tabla principal con los fichajes -->
            <table class="table table-bordered" style="width:100%">
                <thead class="style_color">
                    <tr>
                        <th style="width: 90px; word-wrap: break-word; white-space: normal;">Usuario</th>
                        <th>Puesto</th>
                        <th>Email</th>
                        <th style="width: 70px; word-wrap: break-word; white-space: normal;">Fecha</th>
                        <th>Inicio</th>
                        <th>Final</th>
                        <th>Pausa</th>
                        <th>Comida</th>
                        <th>Tiempo Trabajado</th>
                        <th>Tiempo Total</th>
                        <th>Observaciones</th>

                    </tr>
                </thead>
                <tbody>
                    @if (isset($horarios) && !empty($horarios))
                        @php
                            $totalHours = 0; // Variable to store total hours
                            $totalMinutes = 0; // Variable to store total minutes
                        @endphp

                        @foreach ($horarios as $horario)
                        @php
                            if (isset($horario->total) && $horario->total) {
                                $timeParts = explode(':', $horario->total);
                                $totalHours += (int) $timeParts[0];
                                $totalMinutes += (int) $timeParts[1];
                            }

                            $pausa = $horario->pause_total_formatted ?? null;
                            $comida = $horario->meal_total_formatted ?? null;
                            $trabajado = $horario->worked_time_formatted ?? null;
                            $efectivo = $horario->effective_time_formatted ?? null;

                            $pausa = ($pausa && $pausa !== '00:00:00') ? $pausa : '-';
                            $comida = ($comida && $comida !== '00:00:00') ? $comida : '-';
                            $trabajado = $trabajado ?? '-';
                            $efectivo = $efectivo ?? '-';
                        @endphp
                        <tr>
                            <td style="width: 90px; word-wrap: break-word; white-space: normal;">{{ $horario->user['name'] }}</td>
                            <td>{{ $horario->user['position'] ?? '-' }}</td>
                            <td>{{ $horario->user['email'] ?? '-' }}</td>
                            <td style="width: 70px; word-wrap: break-word; white-space: normal;">{{ \Carbon\Carbon::parse($horario->created_at)->format('d-m-Y') }}</td>
                            <td>{{ $horario->start }}</td>
                            <td>{{ $horario->out ?? 'en curso' }}</td>
                            <td>{{ $comida }}</td>
                            <td>{{ $pausa }}</td>
                            <td>{{ $trabajado }}</td>
                            <td>{{ $horario->total ?? '-' }}</td>
                            <td>{{ \App\Helpers\BackupHelper::parseObservaciones($horario->observaciones) ?? '-' }}</td>

                        </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    @php
                        // Acumuladores en segundos
                        $pausaSegundos = 0;
                        $comidaSegundos = 0;
                        $trabajadoSegundos = 0;
                        $efectivoSegundos = 0;
                        $totalSegundos = 0;

                        foreach ($horarios as $horario) {
                            // Total
                            if (!empty($horario->total)) {
                                [$h, $m] = explode(':', $horario->total);
                                $totalSegundos += ((int) $h * 3600) + ((int) $m * 60);
                            }

                            // Pausa
                            if (!empty($horario->pause_total_formatted) && $horario->pause_total_formatted !== '00:00:00') {
                                [$h, $m, $s] = explode(':', $horario->pause_total_formatted);
                                $pausaSegundos += ((int) $h * 3600) + ((int) $m * 60) + (int) $s;
                            }

                            // Comida
                            if (!empty($horario->meal_total_formatted) && $horario->meal_total_formatted !== '00:00:00') {
                                [$h, $m, $s] = explode(':', $horario->meal_total_formatted);
                                $comidaSegundos += ((int) $h * 3600) + ((int) $m * 60) + (int) $s;
                            }

                            // Trabajado
                            if (!empty($horario->worked_time_formatted)) {
                                [$h, $m] = explode(':', $horario->worked_time_formatted);
                                $trabajadoSegundos += ((int) $h * 3600) + ((int) $m * 60);
                            }

                            // Efectivo
                            if (!empty($horario->effective_time_formatted)) {
                                [$h, $m] = explode(':', $horario->effective_time_formatted);
                                $efectivoSegundos += ((int) $h * 3600) + ((int) $m * 60);
                            }
                        }

                        // Formateador de HH:MM
                        function formatSegundos($totalSegundos) {
                            $horas = floor($totalSegundos / 3600);
                            $minutos = floor(($totalSegundos % 3600) / 60);
                            return str_pad($horas, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutos, 2, '0', STR_PAD_LEFT);
                        }
                    @endphp

                    <tr>
                        <th colspan="6" class="text-right"></th>
                        <th>{{ formatSegundos($comidaSegundos) }}</th>
                        <th>{{ formatSegundos($pausaSegundos) }}</th>
                        <th>{{ formatSegundos($trabajadoSegundos) }}</th>
                        <th>{{ formatSegundos($totalSegundos) }}</th>
                    </tr>
                </tfoot>
            </table>

            <div>
                {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}
            </div>

        </div>
    </div>
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
