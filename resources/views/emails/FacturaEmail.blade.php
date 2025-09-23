<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bienvenido a nuestra plataforma</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>

<body style="margin: 0;">
    <table cellspacing="0"
        style="
        font-family: sans-serif;
        width: fit-content;
        max-width: 595px;
        height: 842px;
        margin: auto;">
        <tr style="height: 76.46px;">
            <td style="padding: 50px 50px 20px 50px;">

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <a href="https://blackcat.es/" target="_blank"><img
                                src="{{ asset('/images/App_Logo.png') }}" width="280" height="auto"></a>

                    </div>
                    <div style="
                        text-align: end; font-size: 10px; text-transform: uppercase;">


                    </div>
                </div>
                <div>
                    <hr style="border: 2px solid rgb(22, 40, 127);">
                </div>



            </td>
        </tr>

        <tr style="height: 352px; font-size: 12px; font-weight: 400; line-height: 16px;">
            <td style="padding: 0px 50px;">
                <div>
                    <p>{{ $message }}</p>
                    <p style="font-size: 0.9em;">Nota: Este correo electrónico ha sido enviado desde una dirección de
                        correo que no
                        acepta
                        correos entrantes. Por favor, no respondas a este correo.</p>
                </div>
            </td>
        </tr>
        <tr style="height: 5px; background-color: #F2F2F2">
            <td style="padding: 20px;">
                <div style="color: #B0B0B0; font-weight: 600; font-size: 8px; line-height: 11px;">
                    <p style="font-size: 7px; text-align: justify">
                        En {{ \App\Helpers\ConfigHelper::get('business_name') }} de
                        {{ \App\Helpers\ConfigHelper::get('province') }}, nuestra principal misión es preservar la ética
                        profesional y salvaguardar los
                        derechos de los ciudadanos. Creemos en la importancia de la transparencia y la responsabilidad
                        en nuestra
                        profesión. Para garantizar que cualquier preocupación o denuncia se maneje de manera
                        confidencial y eficiente, hemos habilitado esta sección de comunicación anónima. Entendemos que,
                        en ocasiones, es necesario plantear inquietudes de manera discreta, y estamos aquí para
                        brindarte la plataforma adecuada.
                    </p>
            </td>
            </div>
        </tr>
        <tr style="text-align: center; background-color: rgb(22, 40, 127);height: 60px; color: white;">
            <td
                style="display: flex; justify-content: center; align-items: center;height: 100%; gap: 30px; font-size: 11px;">
                <p>{{ \App\Helpers\ConfigHelper::get('address') }},{{ \App\Helpers\ConfigHelper::get('town') }},{{ \App\Helpers\ConfigHelper::get('postal_code') }},{{ \App\Helpers\ConfigHelper::get('province') }}
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
