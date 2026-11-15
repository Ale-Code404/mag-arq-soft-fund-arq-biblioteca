<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Exceptions\BusinessRuleException;

final class Book
{
    /** @var Copy[] */
    private array $copies = [];

    public function __construct(
        private string $isbn,
        private string $title,
        private string $author,
        private string $publisher,
        private int $year,
        private Genre $genre,
        private Dimensions $dimensions,
    ) {
        if (trim($isbn) === '' || trim($title) === '' || trim($author) === '') {
            throw new BusinessRuleException('RF-02', 'El ISBN, el título y el autor son obligatorios.');
        }
        if ($year < 1000 || $year > 9999) {
            throw new BusinessRuleException('RF-02', 'El año de publicación no es válido.');
        }
    }

    public function addCopy(string $code): Copy
    {
        foreach ($this->copies as $existing) {
            if ($existing->code() === $code) {
                throw new BusinessRuleException('RN-08', "El ejemplar {$code} ya existe.");
            }
        }
        $copy = new Copy($code, $this);
        $this->copies[] = $copy;

        return $copy;
    }

    public function getThicknessCm(): float
    {
        return $this->dimensions->thicknessCm;
    }

    public function getHeightCm(): float
    {
        return $this->dimensions->heightCm;
    }

    public function isbn(): string
    {
        return $this->isbn;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function author(): string
    {
        return $this->author;
    }

    public function publisher(): string
    {
        return $this->publisher;
    }

    public function year(): int
    {
        return $this->year;
    }

    public function genre(): Genre
    {
        return $this->genre;
    }

    public function dimensions(): Dimensions
    {
        return $this->dimensions;
    }

    /** @return Copy[] */
    public function copies(): array
    {
        return $this->copies;
    }
}
