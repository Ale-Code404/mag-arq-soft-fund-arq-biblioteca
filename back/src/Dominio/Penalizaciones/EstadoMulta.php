<?php

declare(strict_types=1);

namespace App\Dominio\Penalizaciones;

enum EstadoMulta: string
{
    case PENDIENTE = 'PENDIENTE';
    case PAGADA = 'PAGADA';
}
