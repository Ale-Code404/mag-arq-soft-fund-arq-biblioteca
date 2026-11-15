<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Security\Role;
use App\Domain\Security\User;

interface UserRepository
{
    public function findById(int $id): ?User;

    public function loginExists(string $login, ?int $exceptId = null): bool;

    public function staffMemberExists(string $identification): bool;

    /** @param string[] $names @return Role[] solo los que existen */
    public function rolesByName(array $names): array;

    /** Inserts or updates. An inactive user loses their access tokens. */
    public function save(User $user): void;
}
