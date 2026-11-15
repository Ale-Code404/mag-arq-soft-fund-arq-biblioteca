<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MultaModelo extends Model
{
    protected $table = 'multas';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'valor' => 'integer',
            'dias_retraso' => 'integer',
            'tarifa_aplicada' => 'integer',
            'fecha_pago' => 'immutable_date',
        ];
    }

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(PrestamoModelo::class, 'prestamo_id');
    }
}
