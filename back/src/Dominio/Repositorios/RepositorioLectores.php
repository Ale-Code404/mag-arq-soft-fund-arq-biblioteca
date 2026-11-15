<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios;

use App\Dominio\Personas\Lector;

interface RepositorioLectores
{
    public function existePorIdentificacion(string $identificacion): bool;

    /** Devuelve el lector con sus multas pendientes. */
    public function buscarPorIdentificacion(string $identificacion): ?Lector;

    public function guardar(Lector $lector): void;
}
