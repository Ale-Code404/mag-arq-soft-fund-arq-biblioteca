<?php

declare(strict_types=1);

namespace App\Application\Administration\Dto;

use App\Application\Shared\Output;
use App\Domain\Settings\LibrarySettings;

final readonly class SettingsOutput implements Output
{
    public function __construct(
        public int $loanDays,
        public int $renewalDays,
        public int $maxRenewals,
        public int $dailyFineValue,
        public float $clearanceCm,
        public float $defaultThicknessCm,
    ) {}

    public static function from(LibrarySettings $c): self
    {
        return new self(...get_object_vars($c));
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
