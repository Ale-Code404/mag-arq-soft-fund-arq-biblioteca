<?php

declare(strict_types=1);

namespace App\Domain\Security;

final class Role
{
    /** @param Permission[] $permissions */
    public function __construct(
        private string $name,
        private array $permissions,
    ) {}

    public function includes(Permission $p): bool
    {
        return in_array($p, $this->permissions, true);
    }

    public function name(): string
    {
        return $this->name;
    }

    /** @return Permission[] */
    public function permissions(): array
    {
        return $this->permissions;
    }
}
