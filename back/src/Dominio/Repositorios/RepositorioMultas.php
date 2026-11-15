<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios;

use App\Dominio\Penalizaciones\Multa;

interface RepositorioMultas
{
    public function lectorTienePendientes(string $identificacion): bool;

    public function buscarPorId(int $id): ?Multa;

    public function agregar(Multa $multa, int $prestamoId): void;

    /** Guarda el pago y quién lo registró (RNF-06). */
    public function registrarPago(Multa $multa, int $usuarioId): void;
}
