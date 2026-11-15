<?php

namespace App\Infraestructura\Http\Solicitudes;

use App\Aplicacion\Inventario\ConsultarCapacidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsultarCapacidadSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alcance' => ['required', Rule::in(ConsultarCapacidad::ALCANCES)],
            'valor' => ['nullable', 'required_unless:alcance,total', 'string', 'max:30'],
        ];
    }
}
