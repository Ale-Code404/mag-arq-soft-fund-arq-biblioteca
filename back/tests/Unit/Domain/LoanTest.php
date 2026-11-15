<?php

use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Loans\Loan;
use App\Domain\Penalties\Fine;
use App\Domain\Penalties\FinePolicy;
use App\Domain\Penalties\FineStatus;
use App\Domain\Penalties\FixedDailyRate;
use App\Domain\People\Reader;

function loan(string $from = '2026-11-01', ?Reader $reader = null): Loan
{
    return new Loan(null, makeCopy(), $reader ?? new Reader('1020304050', 'Ana Ruiz'), day($from), settings());
}

it('sets_due_date_to_15_days', function () {
    $p = loan('2026-11-01');

    expect($p->dueOn())->toEqual(day('2026-11-16'))
        ->and($p->copy()->isAvailable())->toBeFalse();
});

it('rejects_lending_a_loaned_copy', function () {
    $copy = makeCopy();
    new Loan(null, $copy, new Reader('1', 'Ana'), day('2026-11-01'), settings());
    new Loan(null, $copy, new Reader('2', 'Luis'), day('2026-11-01'), settings());
})->throws(BusinessRuleException::class, 'El ejemplar no está disponible.');

it('renews_once_from_first_due_date', function () {
    $p = loan('2026-11-01');
    $p->renew(day('2026-11-10'), settings());

    expect($p->dueOn())->toEqual(day('2026-12-01'))
        ->and($p->renewals())->toBe(1);
});

it('rejects_second_renewal', function () {
    $p = loan('2026-11-01');
    $p->renew(day('2026-11-10'), settings());
    $p->renew(day('2026-11-20'), settings());
})->throws(BusinessRuleException::class, 'El préstamo ya fue renovado.');

it('rejects_renewing_overdue', function () {
    loan('2026-11-01')->renew(day('2026-11-17'), settings());
})->throws(BusinessRuleException::class, 'El préstamo está vencido.');

it('rejects_renewal_with_fines', function () {
    $reader = new Reader('1020304050', 'Ana Ruiz', null, [Fine::pending(3000, 3, 1000)]);
    loan('2026-11-01', $reader)->renew(day('2026-11-10'), settings());
})->throws(BusinessRuleException::class, 'El lector tiene multas pendientes.');

it('late_return_generates_fine', function () {
    $p = loan('2026-11-01');
    $fine = $p->returnCopy(day('2026-11-20'), new FixedDailyRate(1000));

    expect($fine->value())->toBe(4000)
        ->and($fine->daysLate())->toBe(4)
        ->and($fine->rateApplied())->toBe(1000)
        ->and($fine->status())->toBe(FineStatus::PENDING)
        ->and($p->copy()->isAvailable())->toBeTrue();
});

it('late_return_uses_the_given_policy (polymorphism)', function () {
    $fake = new class implements FinePolicy
    {
        public function calculate(Loan $loan, DateTimeImmutable $date): int
        {
            return $loan->daysLate($date) * 2500;
        }
    };

    expect(loan('2026-11-01')->returnCopy(day('2026-11-18'), $fake)->value())->toBe(5000);
});

it('on_time_return_has_no_fine', function () {
    $p = loan('2026-11-01');

    expect($p->returnCopy(day('2026-11-16'), new FixedDailyRate(1000)))->toBeNull()
        ->and($p->isActive())->toBeFalse();
});

it('rejects_returning_twice', function () {
    $p = loan('2026-11-01');
    $p->returnCopy(day('2026-11-05'), new FixedDailyRate(1000));
    $p->returnCopy(day('2026-11-06'), new FixedDailyRate(1000));
})->throws(BusinessRuleException::class, 'El préstamo ya fue devuelto.');
