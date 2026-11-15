<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

use App\Aplicacion\Compartido\Salida;
use App\Dominio\Personas\Lector;

final readonly class LectorSalida implements Salida
{
    public function __construct(
        public string $identificacion,
        public string $nombre,
        public ?string $direccion,
        public bool $tieneMultasPendientes,
    ) {}

    public static function desde(Lector $l): self
    {
        return new self($l->identificacion(), $l->getNombre(), $l->direccion(), $l->tieneMultasPendientes());
    }

    public function aArreglo(): array
    {
        return get_object_vars($this);
    }
}
