<?php

declare(strict_types=1);

namespace App\Application\Ports;

interface PermissionCache
{
    /** Discards the user's cached permissions after their roles or status change (RNF-03). */
    public function invalidate(int $userId): void;
}
