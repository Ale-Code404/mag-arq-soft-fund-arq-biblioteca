<?php

declare(strict_types=1);

namespace App\Dominio\Seguridad;

final class Rol
{
    /** @param Permiso[] $permisos */
    public function __construct(
        private string $nombre,
        private array $permisos,
    ) {}

    public function incluye(Permiso $p): bool
    {
        return in_array($p, $this->permisos, true);
    }

    public function nombre(): string
    {
        return $this->nombre;
    }

    /** @return Permiso[] */
    public function permisos(): array
    {
        return $this->permisos;
    }
}
