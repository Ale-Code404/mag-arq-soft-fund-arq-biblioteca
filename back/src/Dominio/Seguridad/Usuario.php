<?php

declare(strict_types=1);

namespace App\Dominio\Seguridad;

use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Personas\Funcionario;

final class Usuario
{
    /** @param Rol[] $roles */
    public function __construct(
        private ?int $id,
        private string $login,
        private string $claveHash,
        private bool $activo,
        private Funcionario $persona,
        private array $roles = [],
    ) {
        $this->validarLogin($login);
    }

    /** Un usuario inactivo no tiene permisos. */
    public function tienePermiso(Permiso $p): bool
    {
        if (! $this->activo) {
            return false;
        }
        foreach ($this->roles as $rol) {
            if ($rol->incluye($p)) {
                return true;
            }
        }

        return false;
    }

    /** @param Rol[] $roles */
    public function asignarRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function cambiarLogin(string $login): void
    {
        $this->validarLogin($login);
        $this->login = $login;
    }

    public function cambiarClave(string $claveHash): void
    {
        $this->claveHash = $claveHash;
    }

    public function activar(): void
    {
        $this->activo = true;
    }

    public function desactivar(): void
    {
        $this->activo = false;
    }

    public function asignarId(int $id): void
    {
        $this->id ??= $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function login(): string
    {
        return $this->login;
    }

    public function claveHash(): string
    {
        return $this->claveHash;
    }

    public function activo(): bool
    {
        return $this->activo;
    }

    public function persona(): Funcionario
    {
        return $this->persona;
    }

    /** @return Rol[] */
    public function roles(): array
    {
        return $this->roles;
    }

    private function validarLogin(string $login): void
    {
        if (! preg_match('/^[a-z0-9._-]{3,50}$/', $login)) {
            throw new ReglaNegocioException('RF-12', 'El login debe tener entre 3 y 50 caracteres: minúsculas, números, punto, guion o guion bajo.');
        }
    }
}
