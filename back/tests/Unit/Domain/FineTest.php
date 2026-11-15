<?php

use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Penalties\Fine;

it('marks_paid_only_once', function () {
    $m = Fine::pending(2000, 2, 1000);
    $m->markPaid(day('2026-11-15'));

    expect($m->isPending())->toBeFalse()
        ->and(fn () => $m->markPaid(day('2026-11-16')))->toThrow(BusinessRuleException::class, 'La multa ya fue pagada.');
});
