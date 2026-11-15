<?php

declare(strict_types=1);

namespace App\Application\Inventory\Dto;

use App\Application\Shared\Output;
use App\Domain\Catalog\Genre;
use App\Domain\Inventory\Library;

/** Capacity metrics (section 6.4). Centimeters with one decimal, books rounded down (RNF-05). */
final readonly class CapacityOutput implements Output
{
    public function __construct(
        public string $scope,
        public ?string $value,
        public float $installedCm,
        public float $usedCm,
        public float $availableCm,
        public float $averageThicknessCm,
        public int $theoreticalBooks,
        public int $availableBooks,
        public bool $usesDefaultThickness,
    ) {}

    public static function from(string $scope, ?string $value, Library $b, ?Genre $t = null): self
    {
        return new self(
            $scope,
            $value,
            round($b->installedCapacity(), 1),
            round($b->usedCapacity(), 1),
            round($b->availableCapacity(), 1),
            round($b->averageThickness($t), 1),
            $b->theoreticalCapacity($t),
            $b->availableCapacityBooks($t),
            $b->usesDefaultThickness($t),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
