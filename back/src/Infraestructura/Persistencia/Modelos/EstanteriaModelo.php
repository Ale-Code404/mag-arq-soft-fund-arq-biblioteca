<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstanteriaModelo extends Model
{
    protected $table = 'estanterias';

    protected $guarded = [];

    public function niveles(): HasMany
    {
        return $this->hasMany(NivelModelo::class, 'estanteria_id')->orderBy('numero');
    }
}
