<?php

namespace Tests\Soporte;

use App\Aplicacion\Puertos\Reloj;
use DateTimeImmutable;

final class RelojFijo implements Reloj
{
    public function __construct(private DateTimeImmutable $hoy) {}

    public function hoy(): DateTimeImmutable
    {
        return $this->hoy;
    }
}
