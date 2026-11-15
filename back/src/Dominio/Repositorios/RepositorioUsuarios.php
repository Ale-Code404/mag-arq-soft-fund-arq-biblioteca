<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios;

use App\Dominio\Seguridad\Rol;
use App\Dominio\Seguridad\Usuario;

interface RepositorioUsuarios
{
    public function buscarPorId(int $id): ?Usuario;

    public function existeLogin(string $login, ?int $exceptoId = null): bool;

    public function existeFuncionario(string $identificacion): bool;

    /** @param string[] $nombres @return Rol[] solo los que existen */
    public function rolesPorNombre(array $nombres): array;

    /** Inserta o actualiza. Un usuario inactivo pierde sus tokens de acceso. */
    public function guardar(Usuario $usuario): void;
}
