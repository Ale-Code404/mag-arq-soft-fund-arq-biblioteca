<?php

declare(strict_types=1);

namespace App\Application\Administration\Dto;

final readonly class ManageUsersInput
{
    public function __construct(
        public UserAction $action,
        public int $performedBy,
        public ?int $userId = null,
        public ?string $login = null,
        public ?string $password = null,
        public ?string $identification = null,
        public ?string $name = null,
        public ?string $position = null,
        public ?bool $active = null,
        /** @var string[]|null names de role */
        public ?array $roles = null,
    ) {}
}
