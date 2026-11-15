<?php

declare(strict_types=1);

namespace App\Application\Administration;

use App\Application\Administration\Dto\UserOutput;
use App\Application\Shared\CollectionOutput;
use App\Application\Shared\QueryInput;
use App\Domain\Repositories\UserRepository;
use App\Domain\Security\User;

/** Supporting query for CU-12: users with their roles. */
final class ListUsers
{
    public function __construct(private UserRepository $users) {}

    public function execute(QueryInput $e): CollectionOutput
    {
        return new CollectionOutput(array_map(fn (User $u) => UserOutput::from($u), $this->users->all()));
    }
}
