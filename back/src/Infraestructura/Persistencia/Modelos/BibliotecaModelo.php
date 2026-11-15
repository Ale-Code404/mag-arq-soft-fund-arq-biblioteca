<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BibliotecaModelo extends Model
{
    protected $table = 'bibliotecas';

    protected $guarded = [];

    public function estanterias(): HasMany
    {
        return $this->hasMany(EstanteriaModelo::class, 'biblioteca_id')->orderBy('codigo');
    }
}
