<?php

namespace App\Infraestructura\Servicios;

use App\Aplicacion\Puertos\Transaccion;
use Illuminate\Support\Facades\DB;

class TransaccionLaravel implements Transaccion
{
    public function ejecutar(callable $trabajo): mixed
    {
        return DB::transaction(fn () => $trabajo());
    }
}
