<?php

declare(strict_types=1);

namespace App\Dominio\Inventario;

use App\Dominio\Catalogo\Ejemplar;
use App\Dominio\Excepciones\ReglaNegocioException;

final class Nivel
{
    /** @param Ejemplar[] $ejemplares */
    public function __construct(
        private int $numero,
        private float $largoCm,
        private float $altoCm,
        private array $ejemplares = [],
    ) {
        if ($numero < 1) {
            throw new ReglaNegocioException('RF-01', 'El número de nivel debe ser positivo.');
        }
        if ($largoCm <= 0 || $altoCm <= 0) {
            throw new ReglaNegocioException('RF-01', 'Las medidas del nivel deben ser positivas.');
        }
    }

    public function capacidadInstalada(): float
    {
        return $this->largoCm;
    }

    /** RN-09: los ejemplares prestados conservan su espacio. */
    public function espacioUsado(float $holgura): float
    {
        return array_sum(array_map(fn (Ejemplar $e) => $e->espacioOcupado($holgura), $this->ejemplares));
    }

    public function espacioDisponible(float $holgura): float
    {
        return $this->largoCm - $this->espacioUsado($holgura);
    }

    /** RN-07: cabe si no es más alto que el nivel y queda espacio para grosor + holgura. */
    public function admite(Ejemplar $e, float $holgura): bool
    {
        return $this->cabeEnAltura($e) && $this->cabeEnLargo($e, $holgura);
    }

    public function ubicar(Ejemplar $e, float $holgura): void
    {
        if (! $this->cabeEnAltura($e)) {
            throw new ReglaNegocioException('RN-07', "El libro mide {$e->libro()->getAltoCm()} cm de alto y el nivel solo {$this->altoCm} cm.");
        }
        if (! $this->cabeEnLargo($e, $holgura)) {
            throw new ReglaNegocioException('RN-07', 'El nivel no tiene espacio suficiente para el ejemplar.');
        }
        $this->ejemplares[] = $e;
    }

    public function contiene(string $codigoEjemplar): bool
    {
        foreach ($this->ejemplares as $e) {
            if ($e->codigo() === $codigoEjemplar) {
                return true;
            }
        }

        return false;
    }

    public function retirar(string $codigoEjemplar): ?Ejemplar
    {
        foreach ($this->ejemplares as $i => $e) {
            if ($e->codigo() === $codigoEjemplar) {
                array_splice($this->ejemplares, $i, 1);

                return $e;
            }
        }

        return null;
    }

    private function cabeEnAltura(Ejemplar $e): bool
    {
        return $e->libro()->getAltoCm() <= $this->altoCm;
    }

    private function cabeEnLargo(Ejemplar $e, float $holgura): bool
    {
        // Se redondea para que la suma de decimales (0,4 + 0,4...) no rechace un ajuste exacto
        return round($e->espacioOcupado($holgura) - $this->espacioDisponible($holgura), 6) <= 0;
    }

    public function numero(): int
    {
        return $this->numero;
    }

    public function largoCm(): float
    {
        return $this->largoCm;
    }

    public function altoCm(): float
    {
        return $this->altoCm;
    }

    /** @return Ejemplar[] */
    public function ejemplares(): array
    {
        return $this->ejemplares;
    }
}
