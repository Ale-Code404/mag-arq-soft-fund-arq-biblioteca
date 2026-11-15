<?php

namespace App\Infrastructure\Services;

use App\Application\Ports\Transaction;
use Illuminate\Support\Facades\DB;

class LaravelTransaction implements Transaction
{
    public function execute(callable $work): mixed
    {
        return DB::transaction(fn () => $work());
    }
}
