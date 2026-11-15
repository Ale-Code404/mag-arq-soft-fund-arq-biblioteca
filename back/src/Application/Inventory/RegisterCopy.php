<?php

declare(strict_types=1);

namespace App\Application\Inventory;

use App\Application\Inventory\Dto\CopyOutput;
use App\Application\Inventory\Dto\PlaceCopyInput;
use App\Application\Inventory\Dto\RegisterCopyInput;
use App\Application\Ports\Transaction;
use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\Repositories\BookRepository;
use App\Domain\Repositories\CopyRepository;

/** CU-03 Register a copy and place it (includes CU-04) in a single transaction. */
final class RegisterCopy
{
    public function __construct(
        private BookRepository $books,
        private CopyRepository $copies,
        private PlaceCopy $placeCopy,
        private Transaction $transaction,
    ) {}

    public function execute(RegisterCopyInput $e): CopyOutput
    {
        $book = $this->books->findByIsbn($e->isbn)
            ?? throw new ResourceNotFoundException("No existe un libro con ISBN {$e->isbn}.");
        if ($this->copies->existsByCode($e->code)) {
            throw new BusinessRuleException('RN-08', "Ya existe un ejemplar con el código {$e->code}.");
        }

        return $this->transaction->execute(function () use ($book, $e) {
            $this->copies->save($book->addCopy($e->code));

            // If it does not fit on the level, the exception also rolls back the copy registration
            return $this->placeCopy->execute(new PlaceCopyInput($e->code, $e->shelf, $e->level, $e->userId));
        });
    }
}
