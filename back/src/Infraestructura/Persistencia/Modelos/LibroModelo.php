<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibroModelo extends Model
{
    protected $table = 'libros';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['anio' => 'integer', 'alto_cm' => 'float', 'ancho_cm' => 'float', 'grosor_cm' => 'float'];
    }

    public function ejemplares(): HasMany
    {
        return $this->hasMany(EjemplarModelo::class, 'libro_id');
    }
}
