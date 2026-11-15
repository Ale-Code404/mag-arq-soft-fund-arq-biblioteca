<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NivelModelo extends Model
{
    protected $table = 'niveles';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['numero' => 'integer', 'largo_cm' => 'float', 'alto_cm' => 'float'];
    }

    public function estanteria(): BelongsTo
    {
        return $this->belongsTo(EstanteriaModelo::class, 'estanteria_id');
    }

    public function ejemplares(): HasMany
    {
        return $this->hasMany(EjemplarModelo::class, 'nivel_id')->orderBy('id');
    }
}
