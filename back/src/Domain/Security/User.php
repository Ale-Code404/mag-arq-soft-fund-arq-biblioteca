<?php

declare(strict_types=1);

namespace App\Domain\Security;

use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\People\StaffMember;

final class User
{
    /** @param Role[] $roles */
    public function __construct(
        private ?int $id,
        private string $login,
        private string $passwordHash,
        private bool $active,
        private StaffMember $person,
        private array $roles = [],
    ) {
        $this->validateLogin($login);
    }

    /** Un user inactivo no tiene permissions. */
    public function hasPermission(Permission $p): bool
    {
        if (! $this->active) {
            return false;
        }
        foreach ($this->roles as $role) {
            if ($role->includes($p)) {
                return true;
            }
        }

        return false;
    }

    /** @param Role[] $roles */
    public function assignRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function changeLogin(string $login): void
    {
        $this->validateLogin($login);
        $this->login = $login;
    }

    public function changePassword(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function activate(): void
    {
        $this->active = true;
    }

    public function deactivate(): void
    {
        $this->active = false;
    }

    public function assignId(int $id): void
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

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function person(): StaffMember
    {
        return $this->person;
    }

    /** @return Role[] */
    public function roles(): array
    {
        return $this->roles;
    }

    private function validateLogin(string $login): void
    {
        if (! preg_match('/^[a-z0-9._-]{3,50}$/', $login)) {
            throw new BusinessRuleException('RF-12', 'El login debe tener entre 3 y 50 caracteres: minúsculas, números, punto, guion o guion bajo.');
        }
    }
}
