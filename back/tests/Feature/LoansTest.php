<?php

use App\Infrastructure\Persistence\Models\CopyModel;
use App\Infrastructure\Persistence\Models\FineModel;
use App\Infrastructure\Persistence\Models\LoanModel;
use App\Infrastructure\Persistence\Models\PersonModel;
use Illuminate\Database\UniqueConstraintViolationException;

beforeEach(function () {
    $this->actingAsUser('librarian');
    $this->prepareInventory();
    $this->registerCopy('EJ-0001');
    $this->registerCopy('EJ-0002');
    $this->registerReader();
    $this->setToday('2026-11-01');
});

function lend(string $copy = 'EJ-0001', string $reader = '1020304050')
{
    return test()->postJson('/api/loans', ['readerIdentification' => $reader, 'copyCode' => $copy]);
}

/** Loan from November 1 returned on the 20th: 4 days late, fine of 4000. */
function generateFine(): int
{
    lend()->assertCreated();
    test()->setToday('2026-11-20');

    return test()->postJson('/api/returns', ['copyCode' => 'EJ-0001'])->json('fine.id');
}

it('CU-06 registers a reader and rejects duplicate identification', function () {
    $this->postJson('/api/readers', ['identification' => '1020304050', 'name' => 'Other'])
        ->assertUnprocessable()->assertJson(['rule' => 'RF-06']);
});

it('CU-07 lends a copy for 15 days and records who did it', function () {
    lend()->assertCreated()->assertJson([
        'copyCode' => 'EJ-0001', 'loanedOn' => '2026-11-01', 'dueOn' => '2026-11-16', 'active' => true,
    ]);

    expect(CopyModel::where('code', 'EJ-0001')->value('status'))->toBe('ON_LOAN')
        ->and(LoanModel::first()->registered_by)->toBe(auth()->id());
});

it('R-04 rejects a second loan of the same copy', function () {
    lend()->assertCreated();
    $this->postJson('/api/readers', ['identification' => '2', 'name' => 'Luis'])->assertCreated();

    lend(reader: '2')->assertUnprocessable()->assertJson(['rule' => 'RF-07']);
    expect(LoanModel::count())->toBe(1);
});

it('R-04 the partial unique index prevents two active loans even bypassing the domain', function () {
    lend()->assertCreated();
    $p = LoanModel::first();

    expect(fn () => LoanModel::create($p->only(['copy_id', 'reader_id', 'loaned_on', 'due_on'])))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('RN-05 rejects a loan to a reader with a pending fine', function () {
    generateFine();

    lend('EJ-0002')->assertUnprocessable()->assertJson(['rule' => 'RN-05']);
});

it('CU-08 renews once for 15 days from the first due date', function () {
    lend();
    $this->setToday('2026-11-10');

    $this->postJson('/api/loans/EJ-0001/renewal')->assertOk()->assertJson(['dueOn' => '2026-12-01', 'renewals' => 1]);
    $this->postJson('/api/loans/EJ-0001/renewal')->assertUnprocessable()->assertJson(['rule' => 'RN-02']);
});

it('CU-08 does not renew an overdue loan', function () {
    lend();
    $this->setToday('2026-11-17');

    $this->postJson('/api/loans/EJ-0001/renewal')->assertUnprocessable()
        ->assertExactJson(['rule' => 'RN-03', 'message' => 'El préstamo está vencido.']);
});

it('CU-08 does not renew if the reader has pending fines', function () {
    generateFine();
    $this->setToday('2026-11-20');
    // active loan created before the fine, inserted directly
    LoanModel::create([
        'copy_id' => CopyModel::where('code', 'EJ-0002')->value('id'),
        'reader_id' => PersonModel::where('identification', '1020304050')->value('id'),
        'loaned_on' => '2026-11-15', 'due_on' => '2026-11-30',
    ]);
    CopyModel::where('code', 'EJ-0002')->update(['status' => 'ON_LOAN']);

    $this->postJson('/api/loans/EJ-0002/renewal')->assertUnprocessable()
        ->assertJson(['rule' => 'RN-03', 'message' => 'El lector tiene multas pendientes.']);
});

it('CU-09 and CU-15 a late return creates a pending fine', function () {
    lend();
    $this->setToday('2026-11-20');

    $this->postJson('/api/returns', ['copyCode' => 'EJ-0001'])->assertOk()->assertJson([
        'loan' => ['returnedOn' => '2026-11-20', 'active' => false, 'daysLate' => 4],
        'fine' => ['value' => 4000, 'daysLate' => 4, 'rateApplied' => 1000, 'status' => 'PENDING'],
    ]);
    expect(CopyModel::where('code', 'EJ-0001')->value('status'))->toBe('AVAILABLE');
    $this->postJson('/api/returns', ['copyCode' => 'EJ-0001'])->assertNotFound();
});

it('CU-09 an on-time return creates no fine', function () {
    lend();
    $this->setToday('2026-11-16');

    $this->postJson('/api/returns', ['copyCode' => 'EJ-0001'])->assertOk()->assertJsonPath('fine', null);
    expect(FineModel::count())->toBe(0);
});

it('CU-10 registers a fine payment only once', function () {
    $id = generateFine();
    $this->setToday('2026-11-21');

    $this->postJson("/api/fines/{$id}/payment")->assertOk()->assertJson(['status' => 'PAID', 'paidOn' => '2026-11-21']);
    $this->postJson("/api/fines/{$id}/payment")->assertUnprocessable()->assertJson(['rule' => 'RF-10']);
    expect(FineModel::find($id)->payment_registered_by)->toBe(auth()->id());
    lend('EJ-0002')->assertCreated();
});

it('CU-11 gets the history with pending fines', function () {
    generateFine();
    $this->actingAsUser('director');

    $this->getJson('/api/readers/1020304050/history')->assertOk()
        ->assertJsonPath('reader.hasPendingFines', true)
        ->assertJsonPath('loans.0.fine.value', 4000)
        ->assertJsonPath('totalPendingFines', 4000);
    $this->getJson('/api/readers/999/history')->assertNotFound();
});
