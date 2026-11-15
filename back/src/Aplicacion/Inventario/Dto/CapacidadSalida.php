<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario\Dto;

use App\Aplicacion\Compartido\Salida;
use App\Dominio\Catalogo\Tematica;
use App\Dominio\Inventario\Biblioteca;

/** Métricas de capacidad (sección 6.4). Centímetros con un decimal y libros hacia abajo (RNF-05). */
final readonly class CapacidadSalida implements Salida
{
    public function __construct(
        public string $alcance,
        public ?string $valor,
        public float $instaladaCm,
        public float $usadaCm,
        public float $disponibleCm,
        public float $grosorPromedioCm,
        public int $teoricaLibros,
        public int $disponibleLibros,
        public bool $usaGrosorPorDefecto,
    ) {}

    public static function desde(string $alcance, ?string $valor, Biblioteca $b, ?Tematica $t = null): self
    {
        return new self(
            $alcance,
            $valor,
            round($b->capacidadInstalada(), 1),
            round($b->capacidadUsada(), 1),
            round($b->capacidadDisponible(), 1),
            round($b->grosorPromedio($t), 1),
            $b->capacidadTeorica($t),
            $b->capacidadDisponibleLibros($t),
            $b->usaGrosorPorDefecto($t),
        );
    }

    public static function desdeArreglo(array $datos): self
    {
        return new self(...$datos);
    }

    public function aArreglo(): array
    {
        return get_object_vars($this);
    }
}
