<?php

namespace App\Infraestructura\Http\Solicitudes;

use Illuminate\Foundation\Http\FormRequest;

class EditarUsuarioSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['sometimes', 'string', 'max:50'],
            'clave' => ['sometimes', 'string', 'min:8', 'max:100'],
            'nombre' => ['sometimes', 'string', 'max:150'],
            'cargo' => ['sometimes', 'string', 'max:100'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
