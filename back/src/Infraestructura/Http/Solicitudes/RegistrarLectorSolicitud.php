<?php

namespace App\Infraestructura\Http\Solicitudes;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarLectorSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identificacion' => ['required', 'string', 'max:30'],
            'nombre' => ['required', 'string', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
