<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Catalog\Copy;

interface CopyRepository
{
    public function existsByCode(string $code): bool;

    public function findByCode(string $code): ?Copy;

    /** Inserts or updates the copy (book and status) by its code. */
    public function save(Copy $copy): void;

    /** @return array{shelf: string, level: int}|null */
    public function locationOf(string $code): ?array;
}
