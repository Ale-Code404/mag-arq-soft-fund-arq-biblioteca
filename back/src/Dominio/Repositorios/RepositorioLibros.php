<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios;

use App\Dominio\Catalogo\Libro;

interface RepositorioLibros
{
    public function existePorIsbn(string $isbn): bool;

    public function buscarPorIsbn(string $isbn): ?Libro;

    public function guardar(Libro $libro): void;
}
