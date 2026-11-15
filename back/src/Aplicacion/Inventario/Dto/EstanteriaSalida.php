<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario\Dto;

use App\Aplicacion\Compartido\Salida;
use App\Dominio\Inventario\Estanteria;
use App\Dominio\Inventario\Nivel;

final readonly class EstanteriaSalida implements Salida
{
    public function __construct(
        public string $codigo,
        public float $instaladaCm,
        public float $usadaCm,
        public float $disponibleCm,
        /** @var array<int, array{numero: int, largoCm: float, altoCm: float, usadoCm: float, disponibleCm: float, ejemplares: int}> */
        public array $niveles,
    ) {}

    public static function desde(Estanteria $e, float $holgura): self
    {
        return new self(
            $e->codigo(),
            round($e->capacidadInstalada(), 1),
            round($e->espacioUsado($holgura), 1),
            round($e->capacidadInstalada() - $e->espacioUsado($holgura), 1),
            array_map(fn (Nivel $n) => [
                'numero' => $n->numero(),
                'largoCm' => round($n->largoCm(), 1),
                'altoCm' => round($n->altoCm(), 1),
                'usadoCm' => round($n->espacioUsado($holgura), 1),
                'disponibleCm' => round($n->espacioDisponible($holgura), 1),
                'ejemplares' => count($n->ejemplares()),
            ], $e->niveles()),
        );
    }

    public function aArreglo(): array
    {
        return get_object_vars($this);
    }
}
