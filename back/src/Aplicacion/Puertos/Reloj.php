<?php

declare(strict_types=1);

namespace App\Aplicacion\Puertos;

use DateTimeImmutable;

interface Reloj
{
    public function hoy(): DateTimeImmutable;
}
