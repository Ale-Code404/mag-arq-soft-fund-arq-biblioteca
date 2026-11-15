<?php

declare(strict_types=1);

namespace App\Domain\Security;

enum Permission: string
{
    case SHELF_MANAGE = 'SHELF_MANAGE';
    case BOOK_REGISTER = 'BOOK_REGISTER';
    case COPY_MANAGE = 'COPY_MANAGE';
    case READER_REGISTER = 'READER_REGISTER';
    case LOAN_MANAGE = 'LOAN_MANAGE';
    case LOAN_VIEW = 'LOAN_VIEW';
    case FINE_REGISTER_PAYMENT = 'FINE_REGISTER_PAYMENT';
    case CAPACITY_VIEW = 'CAPACITY_VIEW';
    case USER_MANAGE = 'USER_MANAGE';
    case SETTINGS_UPDATE = 'SETTINGS_UPDATE';
}
