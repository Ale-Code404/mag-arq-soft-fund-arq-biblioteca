<?php

namespace App\Infraestructura\Http\Solicitudes;

use Illuminate\Foundation\Http\FormRequest;

class LoginSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:50'],
            'clave' => ['required', 'string', 'max:100'],
        ];
    }
}
