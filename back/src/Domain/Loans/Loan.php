<?php

declare(strict_types=1);

namespace App\Domain\Loans;

use App\Domain\Catalog\Copy;
use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Penalties\Fine;
use App\Domain\Penalties\FinePolicy;
use App\Domain\People\Reader;
use App\Domain\Settings\LibrarySettings;
use DateTimeImmutable;
use ReflectionClass;

final class Loan
{
    private ?DateTimeImmutable $returnedOn = null;

    private int $renewals = 0;

    private ?Fine $fine = null;

    private DateTimeImmutable $dueOn;

    public function __construct(
        private ?int $id,
        private Copy $copy,
        private Reader $reader,
        private DateTimeImmutable $loanedOn,
        LibrarySettings $cfg,
    ) {
        if (! $copy->isAvailable()) {
            throw new BusinessRuleException('RF-07', 'El ejemplar no está disponible.');
        }
        $this->dueOn = $loanedOn->modify("+{$cfg->loanDays} days");   // RN-01
        $copy->markOnLoan();
    }

    /** Rebuilds a stored loan without repeating the constructor's side effects. */
    public static function reconstitute(
        int $id,
        Copy $copy,
        Reader $reader,
        DateTimeImmutable $loanedOn,
        DateTimeImmutable $dueOn,
        int $renewals,
        ?DateTimeImmutable $returnedOn = null,
        ?Fine $fine = null,
    ): self {
        $p = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();
        $p->id = $id;
        $p->copy = $copy;
        $p->reader = $reader;
        $p->loanedOn = $loanedOn;
        $p->dueOn = $dueOn;
        $p->renewals = $renewals;
        $p->returnedOn = $returnedOn;
        $p->fine = $fine;

        return $p;
    }

    /** RN-02 and RN-03. The new due date counts from the first due date. */
    public function renew(DateTimeImmutable $today, LibrarySettings $cfg): void
    {
        $this->ensureActive();
        if ($this->renewals >= $cfg->maxRenewals) {
            throw new BusinessRuleException('RN-02', 'El préstamo ya fue renovado.');
        }
        if ($this->isOverdue($today)) {
            throw new BusinessRuleException('RN-03', 'El préstamo está vencido.');
        }
        if ($this->reader->hasPendingFines()) {
            throw new BusinessRuleException('RN-03', 'El lector tiene multas pendientes.');
        }
        $this->dueOn = $this->dueOn->modify("+{$cfg->renewalDays} days");
        $this->renewals++;
    }

    /** RN-04 and CU-15: a late return creates a fine using the given policy. */
    public function returnCopy(DateTimeImmutable $today, FinePolicy $policy): ?Fine
    {
        $this->ensureActive();
        $this->returnedOn = $today;
        $this->copy->markAvailable();                       // back on its level (RN-09)
        $days = $this->daysLate($today);
        if ($days > 0) {
            $value = $policy->calculate($this, $today);              // polymorphism
            $this->fine = Fine::pending($value, $days, intdiv($value, $days));
        }

        return $this->fine;
    }

    public function daysLate(DateTimeImmutable $date): int
    {
        if ($date <= $this->dueOn) {
            return 0;
        }

        return (int) $this->dueOn->diff($date)->days;
    }

    public function isOverdue(DateTimeImmutable $today): bool
    {
        return $this->isActive() && $today > $this->dueOn;
    }

    public function isActive(): bool
    {
        return $this->returnedOn === null;
    }

    public function assignId(int $id): void
    {
        $this->id ??= $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function copy(): Copy
    {
        return $this->copy;
    }

    public function reader(): Reader
    {
        return $this->reader;
    }

    public function loanedOn(): DateTimeImmutable
    {
        return $this->loanedOn;
    }

    public function dueOn(): DateTimeImmutable
    {
        return $this->dueOn;
    }

    public function returnedOn(): ?DateTimeImmutable
    {
        return $this->returnedOn;
    }

    public function renewals(): int
    {
        return $this->renewals;
    }

    public function fine(): ?Fine
    {
        return $this->fine;
    }

    private function ensureActive(): void
    {
        if (! $this->isActive()) {
            throw new BusinessRuleException('RF-09', 'El préstamo ya fue devuelto.');
        }
    }
}
