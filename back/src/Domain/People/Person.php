<?php

declare(strict_types=1);

namespace App\Domain\People;

use App\Domain\Exceptions\BusinessRuleException;

abstract class Person
{
    public function __construct(
        private string $identification,
        private string $name,
    ) {
        if (trim($identification) === '' || trim($name) === '') {
            throw new BusinessRuleException('RF-06', 'La identificación y el nombre son obligatorios.');
        }
    }

    public function identification(): string
    {
        return $this->identification;
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function rename(string $name): void
    {
        if (trim($name) === '') {
            throw new BusinessRuleException('RF-06', 'El nombre es obligatorio.');
        }
        $this->name = $name;
    }
}
