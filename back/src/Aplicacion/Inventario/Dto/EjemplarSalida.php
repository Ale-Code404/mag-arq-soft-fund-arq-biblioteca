<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario\Dto;

use App\Aplicacion\Compartido\Salida;
use App\Dominio\Catalogo\Ejemplar;

final readonly class EjemplarSalida implements Salida
{
    public function __construct(
        public string $codigo,
        public string $isbn,
        public string $titulo,
        public string $estado,
        public ?string $estanteria,
        public ?int $nivel,
    ) {}

    public static function desde(Ejemplar $e, ?string $estanteria, ?int $nivel): self
    {
        return new self($e->codigo(), $e->libro()->isbn(), $e->libro()->titulo(), $e->estado()->value, $estanteria, $nivel);
    }

    public function aArreglo(): array
    {
        return get_object_vars($this);
    }
}
