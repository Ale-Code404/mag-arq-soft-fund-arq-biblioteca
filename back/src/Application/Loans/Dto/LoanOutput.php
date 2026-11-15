<?php

declare(strict_types=1);

namespace App\Application\Loans\Dto;

use App\Application\Shared\Output;
use App\Domain\Loans\Loan;
use DateTimeImmutable;

final readonly class LoanOutput implements Output
{
    public function __construct(
        public ?int $id,
        public string $copyCode,
        public string $isbn,
        public string $title,
        public string $readerIdentification,
        public string $readerName,
        public string $loanedOn,
        public string $dueOn,
        public ?string $returnedOn,
        public int $renewals,
        public bool $active,
        public bool $overdue,
        public int $daysLate,
        public ?FineOutput $fine,
    ) {}

    public static function from(Loan $p, DateTimeImmutable $today): self
    {
        $book = $p->copy()->book();

        return new self(
            $p->id(),
            $p->copy()->code(),
            $book->isbn(),
            $book->title(),
            $p->reader()->identification(),
            $p->reader()->getName(),
            $p->loanedOn()->format('Y-m-d'),
            $p->dueOn()->format('Y-m-d'),
            $p->returnedOn()?->format('Y-m-d'),
            $p->renewals(),
            $p->isActive(),
            $p->isOverdue($today),
            $p->daysLate($p->returnedOn() ?? $today),
            $p->fine() ? FineOutput::from($p->fine()) : null,
        );
    }

    public function toArray(): array
    {
        return array_replace(get_object_vars($this), ['fine' => $this->fine?->toArray()]);
    }
}
