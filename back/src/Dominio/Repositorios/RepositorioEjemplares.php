<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios;

use App\Dominio\Catalogo\Ejemplar;

interface RepositorioEjemplares
{
    public function existePorCodigo(string $codigo): bool;

    public function buscarPorCodigo(string $codigo): ?Ejemplar;

    /** Inserta o actualiza el ejemplar (libro y estado) según su código. */
    public function guardar(Ejemplar $ejemplar): void;

    /** @return array{estanteria: string, nivel: int}|null */
    public function ubicacionDe(string $codigo): ?array;
}
