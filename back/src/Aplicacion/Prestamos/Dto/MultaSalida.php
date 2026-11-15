<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

use App\Aplicacion\Compartido\Salida;
use App\Dominio\Penalizaciones\Multa;

final readonly class MultaSalida implements Salida
{
    public function __construct(
        public ?int $id,
        public int $valor,
        public int $diasRetraso,
        public int $tarifaAplicada,
        public string $estado,
        public ?string $fechaPago,
    ) {}

    public static function desde(Multa $m): self
    {
        return new self($m->id(), $m->valor(), $m->diasRetraso(), $m->tarifaAplicada(), $m->estado()->value, $m->fechaPago()?->format('Y-m-d'));
    }

    public function aArreglo(): array
    {
        return get_object_vars($this);
    }
}
