<?php

namespace App\Infraestructura\Http\Solicitudes;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarDevolucionSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigoEjemplar' => ['required', 'string', 'max:30'],
        ];
    }
}
