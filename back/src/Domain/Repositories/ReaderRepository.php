<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\People\Reader;

interface ReaderRepository
{
    public function existsByIdentification(string $identification): bool;

    /** Returns the reader with their pending fines. */
    public function findByIdentification(string $identification): ?Reader;

    public function save(Reader $reader): void;
}
