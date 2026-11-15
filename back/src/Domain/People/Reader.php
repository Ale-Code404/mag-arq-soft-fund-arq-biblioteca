<?php

declare(strict_types=1);

namespace App\Domain\People;

use App\Domain\Penalties\Fine;

final class Reader extends Person
{
    /** @param Fine[] $fines */
    public function __construct(
        string $identification,
        string $name,
        private ?string $address = null,
        private array $fines = [],
    ) {
        parent::__construct($identification, $name);
    }

    public function hasPendingFines(): bool
    {
        foreach ($this->fines as $m) {
            if ($m->isPending()) {
                return true;
            }
        }

        return false;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    /** @return Fine[] */
    public function fines(): array
    {
        return $this->fines;
    }
}
