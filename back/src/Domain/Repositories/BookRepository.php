<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Catalog\Book;

interface BookRepository
{
    public function existsByIsbn(string $isbn): bool;

    public function findByIsbn(string $isbn): ?Book;

    public function save(Book $book): void;
}
