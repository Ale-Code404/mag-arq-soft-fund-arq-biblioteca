<?php

declare(strict_types=1);

namespace App\Dominio\Personas;

use App\Dominio\Penalizaciones\Multa;

final class Lector extends Persona
{
    /** @param Multa[] $multas */
    public function __construct(
        string $identificacion,
        string $nombre,
        private ?string $direccion = null,
        private array $multas = [],
    ) {
        parent::__construct($identificacion, $nombre);
    }

    public function tieneMultasPendientes(): bool
    {
        foreach ($this->multas as $m) {
            if ($m->estaPendiente()) {
                return true;
            }
        }

        return false;
    }

    public function direccion(): ?string
    {
        return $this->direccion;
    }

    /** @return Multa[] */
    public function multas(): array
    {
        return $this->multas;
    }
}
