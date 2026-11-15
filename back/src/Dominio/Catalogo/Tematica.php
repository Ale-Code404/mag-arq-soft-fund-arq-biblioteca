<?php

declare(strict_types=1);

namespace App\Dominio\Catalogo;

enum Tematica: string
{
    case INFANTIL = 'INFANTIL';
    case NARRATIVA = 'NARRATIVA';
    case ENSAYO = 'ENSAYO';
    case POESIA = 'POESIA';
}
