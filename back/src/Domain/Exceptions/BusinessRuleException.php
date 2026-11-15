<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use DomainException;

/** A business rule rejection; carries the rule code (RN-03, RF-07...). */
final class BusinessRuleException extends DomainException
{
    public function __construct(private string $rule, string $message)
    {
        parent::__construct($message);
    }

    public function rule(): string
    {
        return $this->rule;
    }
}
