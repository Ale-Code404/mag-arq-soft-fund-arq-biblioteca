<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Inventory\Library;
use App\Domain\Inventory\Shelf;

interface LibraryRepository
{
    /** Loads the library with shelves, levels, placed copies and books, plus the current settings. */
    public function load(): Library;

    public function saveShelf(Shelf $shelf): void;

    public function saveLocation(string $copyCode, string $shelfCode, int $levelNumber): void;
}
