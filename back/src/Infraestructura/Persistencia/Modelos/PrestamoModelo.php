<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PrestamoModelo extends Model
{
    protected $table = 'prestamos';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fecha_prestamo' => 'immutable_date',
            'fecha_entrega_prevista' => 'immutable_date',
            'fecha_devolucion' => 'immutable_date',
            'renovaciones' => 'integer',
        ];
    }

    public function ejemplar(): BelongsTo
    {
        return $this->belongsTo(EjemplarModelo::class, 'ejemplar_id');
    }

    public function lector(): BelongsTo
    {
        return $this->belongsTo(PersonaModelo::class, 'lector_id');
    }

    public function multa(): HasOne
    {
        return $this->hasOne(MultaModelo::class, 'prestamo_id');
    }
}
