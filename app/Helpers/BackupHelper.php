<?php

namespace App\Helpers;

class BackupHelper
{

    // HELPER 
    /**
     * Convierte "null" (string) DE EXCEL , cadena vacía, etc. en null de PHP real.
     */
    public static function normalizeNull($value)
    {
        $trimmed = strtolower(trim($value ?? ''));

        $nullStrings = ['null', '', 'blanco/vacio', 'ninguno', 'sin dato'];

        return in_array($trimmed, $nullStrings) ? null : $value;
    }

    public static function normalizeInteger($value): ?int
    {
        $value = self::normalizeNull($value);
        return is_numeric($value) ? (int)$value : null;
    }


    public static function transformToListArray($data)
    {
        if (!is_array($data)) {
            return [];
        }

        // Si es un diccionario con claves numéricas como "1", "2", etc.
        if (array_keys($data) !== range(0, count($data) - 1)) {
            return array_values($data);
        }

        return $data;
    }

    public static function parseJsonColumn($raw)
    {
        $cleaned = self::normalizeNull($raw);
        if (is_string($cleaned)) {
            $decoded = json_decode($cleaned, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return self::transformToListArray($decoded);
            }
        }
        return null;
    }

    public static function parseObservaciones(?string $raw): ?string
    {
        $cleaned = self::normalizeNull($raw);

        if (!$cleaned) {
            return null;
        }

        try {
            $decoded = json_decode($cleaned, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $titulo = $decoded['titulo'] ?? '';
                $descripcion = $decoded['descripcion'] ?? '';

                // Concatenamos título y descripción con separador
                return trim($titulo . ' - ' . $descripcion, ' -');
            }
        } catch (\Exception $e) {
            // fallback en caso de error
        }

        // Si no es JSON válido devolvemos el string tal cual
        return $cleaned;
    }
}
