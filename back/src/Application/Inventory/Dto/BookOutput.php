<?php

declare(strict_types=1);

namespace App\Application\Inventory\Dto;

use App\Application\Shared\Output;
use App\Domain\Catalog\Book;

final readonly class BookOutput implements Output
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
    ) {}

    public static function from(Book $l): self
    {
        $d = $l->dimensions();

        return new self($l->isbn(), $l->title(), $l->author(), $l->publisher(), $l->year(), $l->genre()->value, $d->heightCm, $d->widthCm, $d->thicknessCm);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
