<?php

declare(strict_types=1);

namespace App\Dominio\Inventario;

use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Excepciones\ReglaNegocioException;

final class Estanteria
{
    /** @var Nivel[] */
    private array $niveles = [];

    /** @param Nivel[] $niveles */
    public function __construct(private string $codigo, array $niveles)
    {
        if (trim($codigo) === '') {
            throw new ReglaNegocioException('RF-01', 'El código de la estantería es obligatorio.');
        }
        if ($niveles === []) {
            throw new ReglaNegocioException('RF-01', 'La estantería debe tener al menos un nivel.');
        }
        foreach ($niveles as $n) {
            $this->agregarNivel($n);
        }
    }

    public function agregarNivel(Nivel $n): void
    {
        foreach ($this->niveles as $existente) {
            if ($existente->numero() === $n->numero()) {
                throw new ReglaNegocioException('RF-01', "El nivel {$n->numero()} está repetido en la estantería {$this->codigo}.");
            }
        }
        $this->niveles[] = $n;
    }

    public function capacidadInstalada(): float
    {
        return array_sum(array_map(fn (Nivel $n) => $n->capacidadInstalada(), $this->niveles));
    }

    public function espacioUsado(float $holgura): float
    {
        return array_sum(array_map(fn (Nivel $n) => $n->espacioUsado($holgura), $this->niveles));
    }

    public function nivel(int $numero): Nivel
    {
        foreach ($this->niveles as $n) {
            if ($n->numero() === $numero) {
                return $n;
            }
        }
        throw new RecursoNoEncontradoException("La estantería {$this->codigo} no tiene el nivel {$numero}.");
    }

    public function codigo(): string
    {
        return $this->codigo;
    }

    /** @return Nivel[] */
    public function niveles(): array
    {
        return $this->niveles;
    }
}
