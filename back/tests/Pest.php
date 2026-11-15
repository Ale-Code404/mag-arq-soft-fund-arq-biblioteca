<?php

use App\Domain\Catalog\Book;
use App\Domain\Catalog\Copy;
use App\Domain\Catalog\Dimensions;
use App\Domain\Catalog\Genre;
use App\Domain\Settings\LibrarySettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
| Domain factories for unit tests (no Laravel, no database).
*/

function settings(
    int $loanDays = 15,
    int $renewalDays = 15,
    int $maxRenewals = 1,
    int $dailyFineValue = 1000,
    float $clearanceCm = 0.4,
    float $defaultThicknessCm = 2.5,
): LibrarySettings {
    return new LibrarySettings($loanDays, $renewalDays, $maxRenewals, $dailyFineValue, $clearanceCm, $defaultThicknessCm);
}

function book(
    float $thicknessCm = 3.0,
    float $heightCm = 23.0,
    Genre $genre = Genre::NARRATIVE,
    string $isbn = '9789580000001',
): Book {
    return new Book($isbn, 'Cien años de soledad', 'Gabriel García Márquez', 'Sudamericana', 1967, $genre, new Dimensions($heightCm, 15.0, $thicknessCm));
}

function makeCopy(string $code = 'EJ-0001', ?Book $book = null): Copy
{
    return ($book ?? book())->addCopy($code);
}

function day(string $date): DateTimeImmutable
{
    return new DateTimeImmutable($date);
}
