<?php

namespace App\Helpers;

use App\Models\Facturas;
use App\Models\PlantillaMaestra;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CodigoDocumentoHelper
{
    /**
     * Genera un número de documento según la plantilla maestra configurada para el tipo y serie dados.
     *
     * Este método invoca el helper CodigoDocumentoHelper para construir dinámicamente el número de documento
     *
     * Parámetros:
     *  - Tipo: Tipo de documento que se está generando (factura, presupuesto, etc.).
     *  - Serie: Número de serie (1, 2, 5, 7, 11, etc.) que define el formato específico.
     *  - Canal de log: "documentosError" para registrar errores específicos en el canal de documentación.
     *
     * @param string    $tipo       Tipo de documento que se está generando.
     * @param int       $serie      Número de serie (1, 2, 5, 7, 11, etc.) que define el formato específico.
     * @param string    $canalLog   Canal de logs para registrar errores específicos en el canal de documentación.
     *
     * @return string   Número del documento generado, o mensaje de error en caso de fallo.
     */
    public static function generarNumero(string $tipo, int $serie, string $canalLog = 'documentosError'): string
    {
        try {
            // Paso 1: Cargar la plantilla activa correspondiente al tipo de documento y serie
            $plantilla = PlantillaMaestra::where('tipo', $tipo)->where('serie', $serie)->firstOrFail();

            // Paso 2: Obtener el año formateado según configuración de la plantilla (yy o yyyy)
            $year = match ($plantilla->year) {
                'yy' => date('y'),
                'yyyy' => date('Y'),
                default => date('Y'),
            };

            // Paso 3: Configuración base del código
            $digitos   = $plantilla->cantidad ?? 6;
            $simbolo1  = $plantilla->simbolo_1 ?? '-';
            $simbolo2  = $plantilla->simbolo_2 ?? '/';

            // Paso 4: Determinar el valor del campo opcional (puede ser dinámico o valor fijo)
            $nombreOpcional = match (false) {
                !$plantilla,
                ($plantilla->opcional ?? '') === 'EN_BLANCO' => '',
                default => $plantilla->opcional,
            };

            // Paso 5: Obtener el orden personalizado si existe, o usar el orden por defecto
            $orden = $plantilla->orden ?? ['letra', 'year', 'numeroSerieActivo', 'opcional', 'cantidad'];
            $ordenPorDefecto = ['letra', 'year', 'numeroSerieActivo', 'opcional', 'cantidad'];

            // === CASO A: ORDEN CLÁSICO (por defecto) ===
            if ($orden === $ordenPorDefecto) {
                // Construir la base del código (letra + símbolo1 + año)
                $base = $plantilla->letra !== null && $plantilla->letra !== ''
                    ? "{$plantilla->letra}{$simbolo1}{$year}"
                    : $year;

                // Añadir número de serie si está activado
                if ((int) $plantilla->numeroSerieActivo === 1) {
                    $base .= "{$simbolo1}{$serie}";
                }

                // Añadir el campo opcional precedido por símbolo2 si existe
                $segmentoOpcional = $nombreOpcional ? "{$simbolo2}{$nombreOpcional}" : '';



                    $like = "{$base}{$segmentoOpcional}{$simbolo2}%";
                    $regex = "/^" . preg_quote("{$base}{$segmentoOpcional}{$simbolo2}", '/')
                            . "(\d{{$digitos}})$/";

                // Parte fija del código antes del número correlativo
                $codigoSinNumero = "{$base}{$segmentoOpcional}{$simbolo2}";
            }
            // === CASO B: ORDEN PERSONALIZADO (dinámico definido en plantilla) ===
            else {
                // Construir el código base (con ###) y la regex sin delimitadores
                $codigo = self::construirCodigoYRegexBuilder($orden, [
                    'plantilla' => $plantilla,
                    'year'      => $year,
                    'serie'     => $serie,
                    'simbolo1'  => $simbolo1,
                    'simbolo2'  => $simbolo2,
                    'digitos'   => $digitos
                ]);

                // Preparar el LIKE, REGEX y el código sin número (removiendo ###)
                $base = self::prepararLikeRegexYCodigoSinNumero(
                    $codigo['codigoCompleto'],
                    $codigo['regexBuilder'],
                    $nombreOpcional,
                    $simbolo1,
                    $simbolo2
                );

                // Extraer partes necesarias
                $codigoCompleto  = $codigo['codigoCompleto'];
                $codigoSinNumero = $base['codigoSinNumero'];
                $like            = $base['like'];
                $regex           = $base['regex'];
            }

            // Paso 6: Calcular el siguiente número disponible basado en LIKE y REGEX
            $formatted = self::obtenerSiguienteNumero($like, $regex, $digitos);

            // Paso 7: Generar el código final reemplazando el marcador '###' o concatenando
            $codigoFinal = $orden === $ordenPorDefecto
                ? "{$codigoSinNumero}{$formatted}"
                : str_replace('###', $formatted, $codigoCompleto);

            // Paso 8: Limpiar símbolos duplicados, incorrectos o mal posicionados
            $simbolosPosibles = [$simbolo1, $simbolo2];

            // Paso 9: Devolver el código final limpio
            return self::limpiarCodigoFinal($codigoFinal, $simbolosPosibles);
        }
        catch (Exception $e) {
            Log::channel($canalLog)->error('Error al generar número de documento', [
                'Usuario' => Auth::user()?->name,
                'Rol'     => Auth::user()?->rol,
                'Correo'  => Auth::user()?->email,
                'Mensaje' => $e->getMessage(),
            ]);

            // Mensaje genérico para evitar exposición técnica en la interfaz
            return "Error al generar número de documento. Contacta al administrador.";
        }
    }

    /**
     * Construye el código completo y el patrón base de expresión regular (regexBuilder) según
     * el orden personalizado definido en la plantilla maestra y la configuración suministrada.
     *
     * Esta función genera:
     * - Un string `$codigoCompleto` con todos los segmentos ordenados según la plantilla (letra, año, serie, etc.),
     *   donde el campo 'cantidad' se marca como '###' para luego ser reemplazado por el número correlativo.
     * - Un string `$regexBuilder` que define el patrón regex base (sin delimitadores) para encontrar coincidencias exactas.
     *
     * @param array $orden  Array de claves que indica el orden de los componentes del código (ej: ['letra', 'year', 'cantidad']).
     * @param array $config Array asociativo con los datos necesarios para construir los segmentos:
     *                      - 'plantilla': instancia de PlantillaMaestra
     *                      - 'year'     : año formateado ('2025' o '25')
     *                      - 'serie'    : número de serie (int)
     *                      - 'simbolo1' : símbolo primario entre bloques (ej: '-')
     *                      - 'simbolo2' : símbolo secundario entre bloques (ej: '/')
     *                      - 'digitos'  : cantidad de dígitos para el número correlativo (int)
     *
     * @return array{
     *   codigoCompleto: string,   // Código generado con '###' como placeholder del número
     *   regexBuilder: string      // Expresión regular base sin delimitadores
     * }
     *
     * @throws Exception Si el orden incluye claves no válidas
     */
    private static function construirCodigoYRegexBuilder(array $orden, array $config): array
    {
        $segmentos = [];        // Acumulador para construir el código completo con separadores
        $regexBuilder = '';     // Acumulador para construir el patrón regex base (sin / delimitadores)
        $hasStarted = false;    // Flag para decidir cuándo incluir símbolo (no se pone al principio del primer bloque)

        // Recorremos cada clave del orden definido en la plantilla
        foreach ($orden as $clave) {
            $valor = '';      // Contendrá el valor concreto del segmento (ej: 'F', '2025', 'C1', etc.)
            $simbolo = '';    // Contendrá el símbolo que antecede a este valor (si aplica)

            // Determinar el valor y símbolo según la clave actual
            switch ($clave) {
                case 'letra':
                    // Si existe letra en la plantilla (por ejemplo 'F'), la limpiamos de espacios.
                    $valor = trim($config['plantilla']->letra ?? '');

                    // Solo usamos símbolo1 si no es el primer bloque y la letra no está vacía.
                    $simbolo = $hasStarted && $valor !== '' ? $config['simbolo1'] : '';
                    break;

                case 'year':
                    // Tomamos el año ya formateado (2025 o 25 según configuración).
                    $valor = $config['year'];

                    // Si no es el primer bloque, anteponemos símbolo1 (ej: '-').
                    $simbolo = $hasStarted ? $config['simbolo1'] : '';
                    break;

                case 'numeroSerieActivo':
                    // Solo añadimos el número de serie si la plantilla lo tiene activado.
                    if ((int) $config['plantilla']->numeroSerieActivo === 1) {
                        // Convertimos la serie a string para evitar problemas de tipo.
                        $valor = (string) $config['serie'];

                        // Si no es el primer bloque, anteponemos símbolo1.
                        $simbolo = $hasStarted ? $config['simbolo1'] : '';
                    }
                    break;

                case 'opcional':
                    // Solo usamos el campo opcional si no está vacío (centro u otro texto).
                    if (!empty($config['centro'])) {
                        // Limpiamos el valor del campo opcional (por ejemplo: "C1" o "Madrid").
                        $valor = trim($config['centro']);

                        // Si no es el primer bloque, anteponemos símbolo2 (ej: '/').
                        $simbolo = $hasStarted ? $config['simbolo2'] : '';
                    }
                    break;

                case 'cantidad':
                    // Usamos un marcador literal '###' para representar el número correlativo.
                    $valor = '###';

                    // Si no es el primer bloque, anteponemos símbolo2 antes del número.
                    $simbolo = $hasStarted ? $config['simbolo2'] : '';
                    break;

                default:
                    // Si aparece una clave desconocida en el orden, lanzamos excepción.
                    throw new Exception("Valor no reconocido de \$clave: $clave");
            }

            // Si hay un valor válido, lo agregamos al código y al regex
            if ($valor !== '') {
                // Agregamos el bloque completo al código, con símbolo incluido si corresponde
                $segmentos[] = $simbolo . $valor;

                // Construcción del patrón regex
                if ($valor === '###') {
                    // Para el número correlativo usamos un grupo capturable: (\d{N})
                    $regexBuilder .= preg_quote($simbolo, '/') . "(\d{{$config['digitos']}})";
                }
                else {
                    // Para otros valores fijos (letra, año, centro...) usamos valor literal escapado
                    $regexBuilder .= preg_quote($simbolo, '/') . preg_quote($valor, '/');
                }

                // A partir de ahora, todos los siguientes bloques deben llevar símbolo
                $hasStarted = true;
            }
        }

        // Retornamos el código generado y la regex base
        return [
            'codigoCompleto' => implode('', $segmentos),  // Ej: F-2025-1/C1/### o 0001/2025
            'regexBuilder'   => $regexBuilder                               // Ej: F\/2025\/1\/C1\/(\d{6})
        ];
    }

    /**
     * Prepara el like y el regex para buscar facturas que coincidan con el código de vista previa
     * de la plantilla maestra, pero sin el número de serie.
     *
     * @param string $codigoCompleto  Código de vista previa de la plantilla maestra
     * @param string $regexBuilder    Parte de la regex que se va a utilizar para buscar facturas
     * @param string $nombreOpcional  Valor del campo 'opcional' de la plantilla maestra
     * @param string $simbolo1        Símbolo 1 de la plantilla maestra
     * @param string $simbolo2        Símbolo 2 de la plantilla maestra
     *
     * @return array con los siguientes elementos:
     *  - 'codigoSinNumero': Código de vista previa sin el número de serie
     *  - 'like': LIKE para buscar facturas que coincidan con el código de vista previa
     *  - 'regex': Regex para buscar facturas que coincidan con el código de vista previa
     */
    private static function prepararLikeRegexYCodigoSinNumero( string $codigoCompleto, string $regexBuilder, string $nombreOpcional, string $simbolo1, string $simbolo2 ): array
    {
        // Buscar la posición del marcador '###' en el código generado
        $posNumero = strpos($codigoCompleto, '###');

        // Separar el código en dos partes: antes y después del número
        $codigoAntes = substr($codigoCompleto, 0, $posNumero);
        $codigoDespues = substr($codigoCompleto, $posNumero + 3);

        // Unir ambas partes para obtener el código base sin el número (ej: F-2025-1/C1/)
        $codigoSinNumero = $codigoAntes . $codigoDespues;

        // Construir el patrón LIKE para la búsqueda SQL (ej: %/2025)
        $like = "%{$codigoDespues}";

        // Construir la expresión regular completa con delimitadores (ej: /^F\/2025\/C\d{1,3}\/(\d{6})$/)
        $regex = "/^" . rtrim($regexBuilder, '/') . "$/";

        // Si el valor opcional es un centro tipo C1, C2... sustituirlo dinámicamente en LIKE y REGEX
        if (preg_match('/^C\d{1,3}$/i', $nombreOpcional)) {
            // LIKE: cambiar el nombre del centro por comodín C%
            $like = str_replace($nombreOpcional, 'C%', $like);

            // REGEX: reemplazar literal del centro por C\d{1,3} para que coincida con cualquier C-num
            $regexCentro = preg_quote($nombreOpcional, '/');
            $regex = str_replace($regexCentro, 'C\d{1,3}', $regex);
        }

        // Definir símbolos posibles a limpiar (duplicados, combinaciones inválidas)
        $simbolos = [$simbolo1, $simbolo2];

        // Limpiar visualmente los símbolos duplicados o incorrectos en LIKE y en el código base
        return [
            'codigoSinNumero' => self::limpiarSimbolosCodigo($codigoSinNumero, $simbolos),
            'like'            => self::limpiarSimbolosCodigo($like, $simbolos),
            'regex'           => $regex,
        ];
    }

    /**
     * Limpia los símbolos redundantes en un texto generado, eliminando duplicaciones y combinaciones erróneas.
     *
     * Esta función corrige errores visuales comunes en códigos generados como:
     * - Símbolos mezclados innecesarios (ej: "-/", "/-")
     * - Repeticiones del mismo símbolo (ej: "--", "//")
     * - Símbolos al principio o al final del código (ej: "/F-2025/", "F-2025-")
     *
     * @param string $texto     El texto a limpiar (normalmente códigoSinNumero o LIKE).
     * @param array  $simbolos  Array con los símbolos a procesar, típicamente [$simbolo1, $simbolo2].
     *
     * @return string Texto limpio y bien formateado.
     */
    private static function limpiarSimbolosCodigo(string $texto, array $simbolos): string
    {
        // Paso 1: Eliminar combinaciones de símbolos distintos mal ordenados (ej: -/, /-)
        foreach ($simbolos as $s1) {
            foreach ($simbolos as $s2) {
                if (!empty($s1) && !empty($s2) && $s1 !== $s2) {
                    // Si hay secuencias como "-/" las convertimos a "/"
                    $texto = str_replace("{$s1}{$s2}", $s2, $texto);

                    // Y viceversa ("/-" a "-")
                    $texto = str_replace("{$s2}{$s1}", $s1, $texto);
                }
            }
        }

        // Paso 2: Eliminar duplicados del mismo símbolo (ej: "--", "//")
        foreach ($simbolos as $simbolo) {
            if (!empty($simbolo)) {
                $duplicado = $simbolo . $simbolo;

                // Reemplazamos múltiples ocurrencias del mismo símbolo por una sola
                while (str_contains($texto, $duplicado)) {
                    $texto = str_replace($duplicado, $simbolo, $texto);
                }

                // Paso 3a: Eliminar símbolo si aparece al principio del texto
                if (str_starts_with($texto, $simbolo)) {
                    $texto = substr($texto, strlen($simbolo));
                }

                // Paso 3b: Eliminar símbolo si aparece al final del texto
                if (str_ends_with($texto, $simbolo)) {
                    $texto = substr($texto, 0, -strlen($simbolo));
                }
            }
        }

        // Retornar el texto limpio
        return $texto;
    }

    /**
     * Calcula el siguiente número correlativo que debe generarse, usando una búsqueda por patrón LIKE
     * y extracción de número mediante expresión regular.
     *
     * Este método:
     * - Consulta en la base de datos los números existentes que coinciden con el patrón base (`LIKE`).
     * - Aplica la expresión regular (`REGEX`) para extraer la parte numérica del código (grupo capturable).
     * - Encuentra el valor máximo de los números extraídos y suma uno.
     * - Devuelve el resultado formateado con ceros a la izquierda según los dígitos definidos.
     *
     * @param string $like    Patrón LIKE para buscar códigos en la base de datos (ej: '%/2025').
     * @param string $regex   Expresión regular para extraer el número dentro de cada resultado (ej: '/^F\/2025\/(\d{4})$/').
     * @param int    $digitos Cantidad de dígitos del número correlativo (ej: 4 → '0001').
     *
     * @return string Número siguiente disponible, formateado con ceros (ej: '0001', '0143', etc.).
     */
    private static function obtenerSiguienteNumero(string $like, string $regex, int $digitos): string
    {
        // Buscar todos los números de factura que coincidan con el patrón LIKE
        $numerosExistentes = Facturas::where('numFactura', 'LIKE', $like)
            ->pluck('numFactura')   // Obtener solo el campo numFactura
            ->toArray();                    // Convertir la colección en array simple

        // Aplicar el regex a cada resultado y extraer solo el número capturado (\d+)
        $secuencias = array_filter(array_map(function ($num) use ($regex) {
            // Si el número coincide con el patrón y tiene un grupo capturado, lo devolvemos como int
            return preg_match($regex, $num, $m) ? (int) $m[1] : null;
        }, $numerosExistentes));

        // Calcular el siguiente número correlativo: el mayor encontrado + 1, o 1 si no hay resultados
        $nextNumber = count($secuencias) > 0 ? max($secuencias) + 1 : 1;

        // Formatear el número con ceros a la izquierda para cumplir con la longitud requerida
        return str_pad($nextNumber, $digitos, '0', STR_PAD_LEFT);
    }

    /**
     * Limpia el código final generado, eliminando símbolos sobrantes o mal formateados.
     *
     * Esta función se aplica justo antes de devolver el código final completo del documento.
     * Su objetivo es corregir errores comunes de formato, como:
     * - Símbolos repetidos al inicio o al final (ej: "-F-2025-", "/C1/")
     * - Combinaciones incorrectas de símbolos consecutivos (ej: "-/", "/-")
     * - Duplicación del mismo símbolo (ej: "--", "//")
     *
     * @param string $codigo    Código generado que puede contener errores visuales de separación.
     * @param array  $simbolos  Array de símbolos a limpiar (por ejemplo: ['-', '/']).
     *
     * @return string Código limpio, sin errores de formato.
     */
    private static function limpiarCodigoFinal(string $codigo, array $simbolos): string
    {
        // Paso 1: Eliminar símbolo al inicio o al final del código, si existe
        foreach ($simbolos as $simbolo) {
            if (!empty($simbolo)) {
                // Si el código comienza con este símbolo, lo recortamos
                if (str_starts_with($codigo, $simbolo)) {
                    $codigo = substr($codigo, strlen($simbolo));
                }

                // Si el código termina con este símbolo, también lo recortamos
                if (str_ends_with($codigo, $simbolo)) {
                    $codigo = substr($codigo, 0, -strlen($simbolo));
                }
            }
        }

        // Paso 2: Eliminar combinaciones incorrectas entre símbolos distintos (ej: -/, /-)
        foreach ($simbolos as $s1) {
            foreach ($simbolos as $s2) {
                // Si ambos son válidos y diferentes
                if (!empty($s1) && !empty($s2) && $s1 !== $s2) {
                    // Reemplazar ocurrencias de s1 seguido de s2 por solo s2 (ej: -/ → /)
                    $codigo = str_replace("{$s1}{$s2}", $s2, $codigo);
                }
            }
        }

        // Paso 3: Eliminar duplicados iguales (ej: --, // → - o /)
        foreach ($simbolos as $simbolo) {
            if (!empty($simbolo)) {
                $codigo = str_replace("{$simbolo}{$simbolo}", $simbolo, $codigo);
            }
        }

        // Devolver el código limpio y listo para usarse
        return $codigo;
    }
}
