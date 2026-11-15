<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

enum Genre: string
{
    case CHILDREN = 'CHILDREN';
    case NARRATIVE = 'NARRATIVE';
    case ESSAY = 'ESSAY';
    case POETRY = 'POETRY';
}
