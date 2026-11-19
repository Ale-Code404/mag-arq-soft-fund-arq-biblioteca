<?php

declare(strict_types=1);

namespace App\Application\Loans;

use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\People\Reader;
use App\Domain\Repositories\ReaderRepository;

use App\Application\Loans\Dto\{
    ReaderOutput,
    RegisterReaderInput
};

/** CU-06 Register a reader. */
final class RegisterReader
{
    public function __construct(private ReaderRepository $readers) {}

    public function execute(RegisterReaderInput $e): ReaderOutput
    {
        if ($this->readers->existsByIdentification($e->identification)) {
            throw new BusinessRuleException('RF-06', 'Ya existe un lector con esa identificación.');
        }

        $reader = new Reader($e->identification, $e->name, $e->address);
        $this->readers->save($reader);

        return ReaderOutput::from($reader);
    }
}
