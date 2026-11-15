<?php

namespace App\Infraestructura\Http\Solicitudes;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarEstanteriaSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:30'],
            'niveles' => ['required', 'array', 'min:1'],
            'niveles.*.numero' => ['required', 'integer', 'min:1', 'distinct'],
            'niveles.*.largoCm' => ['required', 'numeric', 'gt:0', 'max:10000'],
            'niveles.*.altoCm' => ['required', 'numeric', 'gt:0', 'max:1000'],
        ];
    }
}
