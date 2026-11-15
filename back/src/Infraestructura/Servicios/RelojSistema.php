<?php

namespace App\Infraestructura\Servicios;

use App\Aplicacion\Puertos\Reloj;
use DateTimeImmutable;

class RelojSistema implements Reloj
{
    public function hoy(): DateTimeImmutable
    {
        return new DateTimeImmutable('today');
    }
}
