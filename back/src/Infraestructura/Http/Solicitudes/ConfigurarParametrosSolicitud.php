<?php

namespace App\Infraestructura\Http\Solicitudes;

use Illuminate\Foundation\Http\FormRequest;

class ConfigurarParametrosSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'diasPrestamo' => ['required', 'integer', 'min:1', 'max:365'],
            'diasRenovacion' => ['required', 'integer', 'min:1', 'max:365'],
            'maxRenovaciones' => ['required', 'integer', 'min:1', 'max:10'],
            'valorMultaDiaria' => ['required', 'integer', 'min:1'],
            'holguraCm' => ['required', 'numeric', 'gt:0', 'max:10'],
            'grosorPorDefectoCm' => ['required', 'numeric', 'gt:0', 'max:15'],
        ];
    }
}
