<?php

declare(strict_types=1);

namespace App\Dominio\Personas;

use App\Dominio\Excepciones\ReglaNegocioException;

abstract class Persona
{
    public function __construct(
        private string $identificacion,
        private string $nombre,
    ) {
        if (trim($identificacion) === '' || trim($nombre) === '') {
            throw new ReglaNegocioException('RF-06', 'La identificación y el nombre son obligatorios.');
        }
    }

    public function identificacion(): string
    {
        return $this->identificacion;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    protected function renombrar(string $nombre): void
    {
        if (trim($nombre) === '') {
            throw new ReglaNegocioException('RF-06', 'El nombre es obligatorio.');
        }
        $this->nombre = $nombre;
    }
}
