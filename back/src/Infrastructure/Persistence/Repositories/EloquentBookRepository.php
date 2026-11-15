<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Catalog\Book;
use App\Domain\Repositories\BookRepository;
use App\Infrastructure\Persistence\Models\BookModel;

class EloquentBookRepository implements BookRepository
{
    public function existsByIsbn(string $isbn): bool
    {
        return BookModel::where('isbn', $isbn)->exists();
    }

    public function findByIsbn(string $isbn): ?Book
    {
        $model = BookModel::where('isbn', $isbn)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Book $book): void
    {
        BookModel::updateOrCreate(['isbn' => $book->isbn()], $this->toModel($book));
    }

    private function toDomain(BookModel $m): Book
    {
        return DomainMapper::book($m);
    }

    private function toModel(Book $l): array
    {
        return [
            'title' => $l->title(),
            'author' => $l->author(),
            'publisher' => $l->publisher(),
            'year' => $l->year(),
            'genre' => $l->genre()->value,
            'height_cm' => $l->dimensions()->heightCm,
            'width_cm' => $l->dimensions()->widthCm,
            'thickness_cm' => $l->dimensions()->thicknessCm,
        ];
    }
}
