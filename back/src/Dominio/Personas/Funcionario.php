<?php

declare(strict_types=1);

namespace App\Dominio\Personas;

use App\Dominio\Excepciones\ReglaNegocioException;

final class Funcionario extends Persona
{
    public function __construct(string $identificacion, string $nombre, private string $cargo)
    {
        parent::__construct($identificacion, $nombre);
        $this->validarCargo($cargo);
    }

    public function actualizar(string $nombre, string $cargo): void
    {
        $this->validarCargo($cargo);
        $this->renombrar($nombre);
        $this->cargo = $cargo;
    }

    public function cargo(): string
    {
        return $this->cargo;
    }

    private function validarCargo(string $cargo): void
    {
        if (trim($cargo) === '') {
            throw new ReglaNegocioException('RF-12', 'El cargo es obligatorio.');
        }
    }
}
