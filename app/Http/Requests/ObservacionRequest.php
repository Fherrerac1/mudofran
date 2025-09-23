<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ObservacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
        ];
    }
}
