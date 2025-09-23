<?php

namespace App\Http\Requests\PlantillaMaestra;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlantillaMaestraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Valida los campos de la plantilla maestra.
     *
     * @return array Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'tipo' => ['nullable', 'string'],
            'letra' => ['nullable', 'string', 'max:20'],
            'serie' => ['integer', 'min:1'],
            'year' => ['required', 'in:yy,yyyy'],
            'cantidad' => ['required', 'integer', 'min:3', 'max:20'],
            'simbolo_1' => ['nullable', 'in:-,/,_', 'string'],
            'simbolo_2' => ['nullable', 'in:-,/,_', 'string'],
            'opcional' => ['nullable', 'string'],
            'numeroSerieActivo' => ['required', 'integer', 'in:0,1'],
            'orden' => ['required', 'array', 'min:1'],
            'orden.*' => ['string', 'in:letra,year,numeroSerieActivo,opcional,cantidad'],
        ];
    }

    /**
     * Mensajes personalizados de validación.
     */
    public function messages(): array
    {
        return [
            '*.required' => 'El campo ":attribute" es obligatorio.',
            '*.string' => 'El campo ":attribute" debe ser un texto.',
            '*.integer' => 'El campo ":attribute" debe ser un número entero.',
            '*.min' => 'El campo ":attribute" debe tener al menos :min caracter.',
            '*.max' => 'El campo ":attribute" no puede tener más de :max caracteres.',
            '*.in' => 'El campo ":attribute" contiene un valor no válido.',
            'orden.array' => 'El campo "orden" debe ser un arreglo.',
        ];
    }

    /**
     * Nombres legibles para los atributos.
     */
    public function attributes(): array
    {
        return [
            'tipo' => 'tipo',
            'letra' => 'letra',
            'serie' => 'serie',
            'year' => 'formato de año',
            'cantidad' => 'cantidad de dígitos',
            'simbolo_1' => 'símbolo 1',
            'simbolo_2' => 'símbolo 2',
            'opcional' => 'campo opcional',
            'numeroSerieActivo' => '¿mostrar número de serie?',
            'orden' => 'orden de campos',
            'orden.*' => 'elemento del orden',
        ];
    }
}
