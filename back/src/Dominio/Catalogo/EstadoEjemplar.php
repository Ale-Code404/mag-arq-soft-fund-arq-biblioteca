<?php

declare(strict_types=1);

namespace App\Dominio\Catalogo;

enum EstadoEjemplar: string
{
    case DISPONIBLE = 'DISPONIBLE';
    case PRESTADO = 'PRESTADO';
}
