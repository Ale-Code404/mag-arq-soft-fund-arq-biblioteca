<?php

namespace App\Infraestructura\Http\Solicitudes;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarEjemplarSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:30'],
            'estanteria' => ['required', 'string', 'max:30'],
            'nivel' => ['required', 'integer', 'min:1'],
        ];
    }
}
