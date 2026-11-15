<?php

declare(strict_types=1);

namespace App\Dominio\Catalogo;

use App\Dominio\Excepciones\ReglaNegocioException;

final class Ejemplar
{
    public function __construct(
        private string $codigo,
        private Libro $libro,
        private EstadoEjemplar $estado = EstadoEjemplar::DISPONIBLE,
    ) {
        if (trim($codigo) === '') {
            throw new ReglaNegocioException('RN-08', 'El código del ejemplar es obligatorio.');
        }
    }

    /** RN-06: el ejemplar ocupa su grosor más la holgura configurada. */
    public function espacioOcupado(float $holgura): float
    {
        return $this->libro->getGrosorCm() + $holgura;
    }

    public function estaDisponible(): bool
    {
        return $this->estado === EstadoEjemplar::DISPONIBLE;
    }

    public function marcarPrestado(): void
    {
        if (! $this->estaDisponible()) {
            throw new ReglaNegocioException('RF-07', 'El ejemplar no está disponible.');
        }
        $this->estado = EstadoEjemplar::PRESTADO;
    }

    public function marcarDisponible(): void
    {
        $this->estado = EstadoEjemplar::DISPONIBLE;
    }

    public function codigo(): string
    {
        return $this->codigo;
    }

    public function libro(): Libro
    {
        return $this->libro;
    }

    public function estado(): EstadoEjemplar
    {
        return $this->estado;
    }
}
