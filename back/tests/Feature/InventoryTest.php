<?php

use App\Infrastructure\Persistence\Models\CopyModel;
use App\Infrastructure\Persistence\Models\LevelModel;

beforeEach(fn () => $this->actingAsUser('librarian'));

it('CU-01 registers and lists shelves with their levels', function () {
    $this->postJson('/api/shelves', [
        'code' => 'E-01',
        'levels' => [['number' => 1, 'lengthCm' => 90, 'heightCm' => 32], ['number' => 2, 'lengthCm' => 90, 'heightCm' => 32]],
    ])->assertCreated()->assertJson(['code' => 'E-01', 'installedCm' => 180.0]);

    $this->getJson('/api/shelves')->assertOk()->assertJsonCount(1)->assertJsonPath('0.levels.1.number', 2);
});

it('CU-01 rejects duplicate code and invalid measurements', function () {
    $this->prepareInventory();

    $this->postJson('/api/shelves', ['code' => 'E-01', 'levels' => [['number' => 1, 'lengthCm' => 50, 'heightCm' => 30]]])
        ->assertUnprocessable()->assertJson(['rule' => 'RF-01']);
    $this->postJson('/api/shelves', ['code' => 'E-02', 'levels' => [['number' => 1, 'lengthCm' => 0, 'heightCm' => 30]]])
        ->assertUnprocessable()->assertJsonValidationErrorFor('levels.0.lengthCm', 'errors');
});

it('CU-02 registers a book and rejects duplicate ISBN and out-of-range dimensions', function () {
    $this->prepareInventory();
    $book = ['isbn' => '9789580000001', 'title' => 'Other', 'author' => 'Other', 'publisher' => 'X', 'year' => 2000, 'genre' => 'ESSAY', 'heightCm' => 23, 'widthCm' => 15, 'thicknessCm' => 2];

    $this->postJson('/api/books', $book)->assertUnprocessable()->assertJson(['rule' => 'RF-02']);
    $this->postJson('/api/books', ['isbn' => '9789580000099', 'heightCm' => 61] + $book)->assertUnprocessable()->assertJson(['rule' => 'R-02']);
});

it('CU-03 registers a copy and places it in the same step', function () {
    $this->prepareInventory();

    $this->registerCopy('EJ-0001')->assertCreated()
        ->assertJson(['code' => 'EJ-0001', 'status' => 'AVAILABLE', 'shelf' => 'E-01', 'level' => 1]);
    $this->registerCopy('EJ-0001')->assertUnprocessable()->assertJson(['rule' => 'RN-08']);
});

it('CU-03 does not keep the copy if it does not fit on the level (transaction)', function () {
    $this->prepareInventory();
    LevelModel::where('number', 2)->update(['height_cm' => 20]);

    $this->registerCopy('EJ-0001', level: 2)->assertUnprocessable()->assertJson(['rule' => 'RN-07']);
    expect(CopyModel::where('code', 'EJ-0001')->exists())->toBeFalse();
});

it('CU-04 relocates a copy to another level', function () {
    $this->prepareInventory();
    $this->registerCopy('EJ-0001');

    $this->putJson('/api/copies/EJ-0001/location', ['shelf' => 'E-01', 'level' => 2])->assertOk()->assertJson(['level' => 2]);
    $this->putJson('/api/copies/EJ-0001/location', ['shelf' => 'E-09', 'level' => 1])->assertNotFound();
    $this->putJson('/api/copies/EJ-9999/location', ['shelf' => 'E-01', 'level' => 1])->assertNotFound();
});

it('CU-05 calculates capacity by genre as in contract 9.2', function () {
    $this->prepareInventory('POETRY', 3.2);
    $this->registerCopy('EJ-0001');

    $this->getJson('/api/capacity?scope=genre&value=POETRY')->assertOk()->assertExactJson([
        'scope' => 'genre', 'value' => 'POETRY', 'installedCm' => 180.0, 'usedCm' => 3.6, 'availableCm' => 176.4,
        'averageThicknessCm' => 3.2, 'theoreticalBooks' => 50, 'availableBooks' => 49, 'usesDefaultThickness' => false,
    ]);
    expect($this->getJson('/api/capacity?scope=total')->getContent())->toContain('"installedCm":180.0');
    $this->getJson('/api/capacity?scope=shelf&value=E-01')->assertOk()->assertJson(['installedCm' => 180.0]);
    $this->getJson('/api/capacity?scope=genre&value=ESSAY')->assertOk()->assertJson(['averageThicknessCm' => 2.5, 'usesDefaultThickness' => true]);
    $this->getJson('/api/capacity?scope=genre')->assertUnprocessable();
});

it('CU-05 answers from cache and is invalidated when a copy is placed', function () {
    $this->prepareInventory();
    $this->getJson('/api/capacity?scope=total')->assertJson(['installedCm' => 180.0, 'usedCm' => 0.0]);

    // A change outside the use cases is not visible: the second query comes from cache
    LevelModel::where('number', 1)->update(['length_cm' => 100]);
    $this->getJson('/api/capacity?scope=total')->assertJson(['installedCm' => 180.0]);

    $this->registerCopy('EJ-0001')->assertCreated();
    $this->getJson('/api/capacity?scope=total')->assertJson(['installedCm' => 190.0, 'usedCm' => 3.6]);
});
