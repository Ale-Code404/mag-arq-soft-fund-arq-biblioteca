<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Model;

class PersonaModelo extends Model
{
    public const LECTOR = 'LECTOR';

    public const FUNCIONARIO = 'FUNCIONARIO';

    protected $table = 'personas';

    protected $guarded = [];
}
