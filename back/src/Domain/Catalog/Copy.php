<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Exceptions\BusinessRuleException;

final class Copy
{
    public function __construct(
        private string $code,
        private Book $book,
        private CopyStatus $status = CopyStatus::AVAILABLE,
    ) {
        if (trim($code) === '') {
            throw new BusinessRuleException('RN-08', 'El código del ejemplar es obligatorio.');
        }
    }

    /** RN-06: a copy occupies its thickness plus the configured clearance. */
    public function occupiedSpace(float $clearance): float
    {
        return $this->book->getThicknessCm() + $clearance;
    }

    public function isAvailable(): bool
    {
        return $this->status === CopyStatus::AVAILABLE;
    }

    public function markOnLoan(): void
    {
        if (! $this->isAvailable()) {
            throw new BusinessRuleException('RF-07', 'El ejemplar no está disponible.');
        }
        $this->status = CopyStatus::ON_LOAN;
    }

    public function markAvailable(): void
    {
        $this->status = CopyStatus::AVAILABLE;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function book(): Book
    {
        return $this->book;
    }

    public function status(): CopyStatus
    {
        return $this->status;
    }
}
