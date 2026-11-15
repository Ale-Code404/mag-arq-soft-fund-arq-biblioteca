<?php

declare(strict_types=1);

namespace App\Domain\Penalties;

use App\Domain\Exceptions\BusinessRuleException;
use DateTimeImmutable;

final class Fine
{
    public function __construct(
        private ?int $id,
        private int $value,
        private int $daysLate,
        private int $rateApplied,
        private FineStatus $status = FineStatus::PENDING,
        private ?DateTimeImmutable $paidOn = null,
    ) {
        if ($value <= 0 || $daysLate <= 0 || $rateApplied <= 0) {
            throw new BusinessRuleException('RN-04', 'La multa debe tener valor, días de retraso y tarifa positivos.');
        }
        if (($status === FineStatus::PAID) !== ($paidOn !== null)) {
            throw new BusinessRuleException('RN-04', 'Una multa pagada debe tener fecha de pago y una pendiente no.');
        }
    }

    /** CU-15: new fine for a late return. */
    public static function pending(int $value, int $daysLate, int $rateApplied): self
    {
        return new self(null, $value, $daysLate, $rateApplied);
    }

    public function markPaid(DateTimeImmutable $date): void
    {
        if (! $this->isPending()) {
            throw new BusinessRuleException('RF-10', 'La multa ya fue pagada.');
        }
        $this->status = FineStatus::PAID;
        $this->paidOn = $date;
    }

    public function isPending(): bool
    {
        return $this->status === FineStatus::PENDING;
    }

    public function assignId(int $id): void
    {
        $this->id ??= $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function daysLate(): int
    {
        return $this->daysLate;
    }

    public function rateApplied(): int
    {
        return $this->rateApplied;
    }

    public function status(): FineStatus
    {
        return $this->status;
    }

    public function paidOn(): ?DateTimeImmutable
    {
        return $this->paidOn;
    }
}
