<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Catalog\Book;
use App\Domain\Catalog\Copy;
use App\Domain\Catalog\CopyStatus;
use App\Domain\Catalog\Dimensions;
use App\Domain\Catalog\Genre;
use App\Domain\Penalties\Fine;
use App\Domain\Penalties\FineStatus;
use App\Infrastructure\Persistence\Models\BookModel;
use App\Infrastructure\Persistence\Models\CopyModel;
use App\Infrastructure\Persistence\Models\FineModel;

/** Eloquent model to domain entity mappings shared by several repositories. */
final class DomainMapper
{
    public static function book(BookModel $m): Book
    {
        return new Book(
            $m->isbn, $m->title, $m->author, $m->publisher, $m->year,
            Genre::from($m->genre),
            new Dimensions($m->height_cm, $m->width_cm, $m->thickness_cm),
        );
    }

    public static function copy(CopyModel $m, ?Book $book = null): Copy
    {
        return new Copy($m->code, $book ?? self::book($m->book), CopyStatus::from($m->status));
    }

    public static function fine(FineModel $m): Fine
    {
        return new Fine(
            $m->id, $m->value, $m->days_late, $m->rate_applied,
            FineStatus::from($m->status), $m->paid_on,
        );
    }
}
