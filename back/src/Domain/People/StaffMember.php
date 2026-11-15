<?php

declare(strict_types=1);

namespace App\Domain\People;

use App\Domain\Exceptions\BusinessRuleException;

final class StaffMember extends Person
{
    public function __construct(string $identification, string $name, private string $position)
    {
        parent::__construct($identification, $name);
        $this->validatePosition($position);
    }

    public function updateDetails(string $name, string $position): void
    {
        $this->validatePosition($position);
        $this->rename($name);
        $this->position = $position;
    }

    public function position(): string
    {
        return $this->position;
    }

    private function validatePosition(string $position): void
    {
        if (trim($position) === '') {
            throw new BusinessRuleException('RF-12', 'El cargo es obligatorio.');
        }
    }
}
