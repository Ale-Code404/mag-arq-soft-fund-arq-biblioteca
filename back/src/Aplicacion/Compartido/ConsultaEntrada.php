<?php

declare(strict_types=1);

namespace App\Aplicacion\Compartido;

final readonly class ConsultaEntrada
{
    public function __construct(
        public int $usuarioId,
    ) {}
}
