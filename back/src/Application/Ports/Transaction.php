<?php

declare(strict_types=1);

namespace App\Application\Ports;

interface Transaction
{
    /** Runs the work atomically (RNF-04) and returns its result. */
    public function execute(callable $work): mixed;
}
