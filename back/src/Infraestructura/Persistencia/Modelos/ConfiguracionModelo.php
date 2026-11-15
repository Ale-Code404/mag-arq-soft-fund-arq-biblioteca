<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionModelo extends Model
{
    protected $table = 'configuraciones';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dias_prestamo' => 'integer',
            'dias_renovacion' => 'integer',
            'max_renovaciones' => 'integer',
            'valor_multa_diaria' => 'integer',
            'holgura_cm' => 'float',
            'grosor_por_defecto_cm' => 'float',
            'vigente_desde' => 'datetime',
        ];
    }
}
