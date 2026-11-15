<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EjemplarModelo extends Model
{
    protected $table = 'ejemplares';

    protected $guarded = [];

    public function libro(): BelongsTo
    {
        return $this->belongsTo(LibroModelo::class, 'libro_id');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelModelo::class, 'nivel_id');
    }
}
