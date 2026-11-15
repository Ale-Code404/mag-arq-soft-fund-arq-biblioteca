<?php

declare(strict_types=1);

namespace App\Application\Inventory;

use App\Application\Inventory\Dto\BookOutput;
use App\Application\Inventory\Dto\RegisterBookInput;
use App\Domain\Catalog\Book;
use App\Domain\Catalog\Dimensions;
use App\Domain\Catalog\Genre;
use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Repositories\BookRepository;

/** CU-02 Register a book (sequence D06). */
final class RegisterBook
{
    public function __construct(private BookRepository $books) {}

    public function execute(RegisterBookInput $e): BookOutput
    {
        if ($this->books->existsByIsbn($e->isbn)) {
            throw new BusinessRuleException('RF-02', 'Ya existe un libro con ese identificador.');
        }
        $genre = Genre::tryFrom($e->genre)
            ?? throw new BusinessRuleException('RF-02', "La temática {$e->genre} no existe.");

        $book = new Book(
            $e->isbn, $e->title, $e->author, $e->publisher, $e->year,
            $genre,
            new Dimensions($e->heightCm, $e->widthCm, $e->thicknessCm),   // valida rangos (R-02)
        );
        $this->books->save($book);

        return BookOutput::from($book);
    }
}
