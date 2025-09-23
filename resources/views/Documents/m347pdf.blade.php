<!DOCTYPE html>
<html lang="es">

<head>
    <link rel="manifest" href="/manifest.json">
    <!-- Chrome for Android theme color -->
    <meta name="theme-color" content="#000000">

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="Técnico" content="PWA">
    <link rel="icon" sizes="512x512" href="/images/icons/icon-512x512.png">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="PWA">
    <link rel="apple-touch-icon" href="/images/icons/icon-512x512.png">

    <link href="/images/icons/splash-640x1136.png"
        media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)"
        rel="apple-touch-startup-image" />
    <link href="/images/icons/splash-750x1334.png"
        media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)"
        rel="apple-touch-startup-image" />
    <link href="/images/icons/splash-1242x2208.png"
        media="(device-width: 621px) and (device-height: 1104px) and (-webkit-device-pixel-ratio: 3)"
        rel="apple-touch-startup-image" />
    <link href="/images/icons/splash-1125x2436.png"
        media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)"
        rel="apple-touch-startup-image" />
    <link href="/images/icons/splash-828x1792.png"
        media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)"
        rel="apple-touch-startup-image" />
    <link href="/images/icons/splash-1242x2688.png"
        media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)"
        rel="apple-touch-startup-image" />
    <link href="/images/icons/splash-1536x2048.png"
        media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2)"
        rel="apple-touch-startup-image" />
    <link href="/images/icons/splash-1668x2224.png"
        media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2)"
        rel="apple-touch-startup-image" />
    <link href="/images/icons/splash-1668x2388.png"
        media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)"
        rel="apple-touch-startup-image" />
    <link href="/images/icons/splash-2048x2732.png"
        media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)"
        rel="apple-touch-startup-image" />

    <!-- Tile for Win8 -->
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/images/icons/icon-512x512.png">
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
        <div id="table" class="mb-1">
            <div class="imgHeader">
                <img src="{{ public_path() . '/storage/' . \App\Helpers\ConfigHelper::get('main_logo') }}" alt="Responsive image">
            </div>
            <div class="infoHeader2">
                <div style="text-align: right">
                    <p>
                        <b class="text-secondary" style="font-size: 16px">Modelo 347</b>
                        <br>
                    <p><b style="font-size: 16px;"
                            class="text-weight-bold">{{ \App\Helpers\ConfigHelper::get('business_name') }}</b>
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
            <div class="descripcion  mb-2">Muy señores nuestros: <br>
                En cumplimiento del modelo 347, DECLARACIÓN ANUAL DE OPERACIONES CON TERCEROS, con saldos superiores
                a 3.005.06€, ponemos a su conocimiento que según nuestros registros contables, que obran en nuestro
                poder, del periodo año ({{ $year }}), figura Ud. con los siguientes datos:

            </div>
            <table class="table table-bordered" style="width:100%">
                <thead class="style_color">
                    <tr>
                        <th style="width: 16%;">N.I.F Cliente</th>
                        <th style="width: 12%;">Razon Social</th>
                        <th style="width: 14%;">Trimestre 1</th>
                        <th style="width: 14%;">Trimestre 2</th>
                        <th style="width: 14%;">Trimestre 3</th>
                        <th style="width: 14%;">Trimestre 4</th>
                        <th style="width: 16%;">Total Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($clientes) && !empty($clientes))
                        @foreach ($clientes as $indice => $cliente)
                            <tr>
                                <td>{{ $cliente->dni }}</td>
                                <td>{{ $cliente->nombre }}</td>
                                <td>{{ number_format($cliente->trimestre1, 2, ',', '.') }} €</td>
                                <td>{{ number_format($cliente->trimestre2, 2, ',', '.') }} €</td>
                                <td>{{ number_format($cliente->trimestre3, 2, ',', '.') }} €</td>
                                <td>{{ number_format($cliente->trimestre4, 2, ',', '.') }} €</td>
                                <td>{{ number_format($cliente->total, 2, ',', '.') }} €</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <div class="descripcion  mb-2">Agradecemos contacte con la mayor brevedad posible con el Departamento
                de Administración, para comunicarnos su conformidad, o en su caso, que se modificasen las posibles
                descripciones con el importe arriba resaltado a la siguiente dirección de correo:
                {{ \App\Helpers\ConfigHelper::get('email') }}


                Agradecemos de antemano su colaboración en este asunto, aprovechamos la ocasión para brindarle un
                saludo.
            </div>
        </div>
    </div>
