<?php

declare(strict_types=1);

namespace App\Application\Inventory\Dto;

final readonly class RegisterBookInput
{
    public function __construct(
        public string $isbn,
        public string $title,
        public string $author,
        public string $publisher,
        public int $year,
        public string $genre,
        public float $heightCm,
        public float $widthCm,
        public float $thicknessCm,
        public int $userId,
    ) {}
}
