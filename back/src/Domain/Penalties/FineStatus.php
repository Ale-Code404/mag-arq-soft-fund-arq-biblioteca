<?php

declare(strict_types=1);

namespace App\Domain\Penalties;

enum FineStatus: string
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
}
