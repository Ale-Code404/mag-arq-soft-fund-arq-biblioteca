<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios;

use App\Dominio\Prestamos\Prestamo;

interface RepositorioPrestamos
{
    public function buscarActivoPorEjemplar(string $codigoEjemplar): ?Prestamo;

    /** Inserta o actualiza; al insertar asigna el id y registra quién lo hizo (RNF-06). */
    public function guardar(Prestamo $prestamo, int $usuarioId): void;

    /** @return Prestamo[] del más reciente al más antiguo */
    public function historialDeLector(string $identificacion): array;
}
