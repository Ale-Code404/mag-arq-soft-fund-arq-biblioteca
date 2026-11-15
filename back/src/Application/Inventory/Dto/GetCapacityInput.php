<?php

declare(strict_types=1);

namespace App\Application\Inventory\Dto;

final readonly class GetCapacityInput
{
    public function __construct(
        public string $scope,
        public ?string $value,
        public int $userId,
    ) {}
}
