<?php

declare(strict_types=1);

namespace App\Aplicacion\Compartido;

final readonly class ColeccionSalida implements Salida
{
    /** @param Salida[] $elementos */
    public function __construct(public array $elementos) {}

    public function aArreglo(): array
    {
        return array_map(fn (Salida $s) => $s->aArreglo(), $this->elementos);
    }
}
