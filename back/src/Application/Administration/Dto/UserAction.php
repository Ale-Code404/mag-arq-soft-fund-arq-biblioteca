<?php

declare(strict_types=1);

namespace App\Application\Administration\Dto;

enum UserAction: string
{
    case CREATE = 'CREATE';
    case EDIT = 'EDIT';
    case ASSIGN_ROLES = 'ASSIGN_ROLES';
}
