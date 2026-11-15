<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario\Dto;

use App\Aplicacion\Compartido\Salida;
use App\Dominio\Catalogo\Libro;

final readonly class LibroSalida implements Salida
{
    public function __construct(
        public string $isbn,
        public string $titulo,
        public string $autor,
        public string $editorial,
        public int $anio,
        public string $tematica,
        public float $altoCm,
        public float $anchoCm,
        public float $grosorCm,
    ) {}

    public static function desde(Libro $l): self
    {
        $d = $l->dimensiones();

        return new self($l->isbn(), $l->titulo(), $l->autor(), $l->editorial(), $l->anio(), $l->tematica()->value, $d->altoCm, $d->anchoCm, $d->grosorCm);
    }

    public function aArreglo(): array
    {
        return get_object_vars($this);
    }
}
