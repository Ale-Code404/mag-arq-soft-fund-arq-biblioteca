<?php

declare(strict_types=1);

namespace App\Application\Administration\Dto;

use App\Application\Shared\Output;
use App\Domain\Security\Role;
use App\Domain\Security\User;

final readonly class UserOutput implements Output
{
    /** @param string[] $roles */
    public function __construct(
        public ?int $id,
        public string $login,
        public bool $active,
        public string $identification,
        public string $name,
        public string $position,
        public array $roles,
    ) {}

    public static function from(User $u): self
    {
        $p = $u->person();

        return new self($u->id(), $u->login(), $u->active(), $p->identification(), $p->getName(), $p->position(), array_map(fn (Role $r) => $r->name(), $u->roles()));
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
