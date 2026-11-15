<?php

namespace App\Infraestructura\Http\Solicitudes;

use App\Dominio\Catalogo\Tematica;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarLibroSolicitud extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'isbn' => ['required', 'string', 'max:20'],
            'titulo' => ['required', 'string', 'max:255'],
            'autor' => ['required', 'string', 'max:255'],
            'editorial' => ['required', 'string', 'max:150'],
            'anio' => ['required', 'integer', 'between:1000,9999'],
            'tematica' => ['required', Rule::enum(Tematica::class)],
            // los rangos de medidas los valida el dominio (R-02)
            'altoCm' => ['required', 'numeric'],
            'anchoCm' => ['required', 'numeric'],
            'grosorCm' => ['required', 'numeric'],
        ];
    }
}
