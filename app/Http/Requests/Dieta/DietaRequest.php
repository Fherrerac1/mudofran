<?php

namespace App\Http\Requests\Dieta;

use Illuminate\Foundation\Http\FormRequest;

class DietaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        // Puedes poner control de permisos si quieres.
        return true;
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'media_dieta' => ['required', 'numeric', 'min:0'],
            'dieta' => ['required', 'numeric', 'min:0'],
            'plus_noche' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Mensajes personalizados (opcional).
     */
    public function messages(): array
    {
        return [
            'media_dieta.required' => 'El campo media dieta es obligatorio.',
            'dieta.required' => 'El campo dieta completa es obligatorio.',
            'plus_noche.required' => 'El campo plus noche es obligatorio.',
        ];
    }
}
