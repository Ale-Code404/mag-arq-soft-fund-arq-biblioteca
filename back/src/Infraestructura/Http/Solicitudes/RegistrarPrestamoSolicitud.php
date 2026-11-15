<?php

namespace App\Infraestructura\Http\Solicitudes;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarPrestamoSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identificacionLector' => ['required', 'string', 'max:30'],
            'codigoEjemplar' => ['required', 'string', 'max:30'],
        ];
    }
}
