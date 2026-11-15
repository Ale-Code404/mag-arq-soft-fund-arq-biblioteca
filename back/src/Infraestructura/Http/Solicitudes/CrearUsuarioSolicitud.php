<?php

namespace App\Infraestructura\Http\Solicitudes;

use Illuminate\Foundation\Http\FormRequest;

class CrearUsuarioSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:50'],
            'clave' => ['required', 'string', 'min:8', 'max:100'],
            'identificacion' => ['required', 'string', 'max:30'],
            'nombre' => ['required', 'string', 'max:150'],
            'cargo' => ['required', 'string', 'max:100'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'max:50'],
        ];
    }
}
