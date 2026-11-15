<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

enum CopyStatus: string
{
    case AVAILABLE = 'AVAILABLE';
    case ON_LOAN = 'ON_LOAN';
}
