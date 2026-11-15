<?php

namespace Tests;

use App\Application\Ports\Clock;
use App\Infrastructure\Persistence\Models\UserModel;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Support\FixedClock;

abstract class TestCase extends BaseTestCase
{
    /** API tests start from the seeded library, settings, roles and users. */
    protected bool $seed = true;

    private static bool $testDatabaseReady = false;

    /** Creates the test database the first time, so no Docker init script is needed. */
    protected function beforeRefreshingDatabase()
    {
        if (self::$testDatabaseReady) {
            return;
        }
        $base = config('database.connections.pgsql.database');
        config(['database.connections.maintenance' => ['database' => 'postgres'] + config('database.connections.pgsql')]);
        if (! DB::connection('maintenance')->selectOne('select 1 from pg_database where datname = ?', [$base])) {
            DB::connection('maintenance')->statement("CREATE DATABASE \"{$base}\"");
        }
        DB::purge('maintenance');
        self::$testDatabaseReady = true;
    }

    protected function actingAsUser(string $login): UserModel
    {
        $user = UserModel::where('login', $login)->firstOrFail();
        Sanctum::actingAs($user);

        return $user;
    }

    protected function setToday(string $date): void
    {
        $this->app->instance(Clock::class, new FixedClock(new DateTimeImmutable($date)));
    }

    /** Shelf E-01 with two 90 × 32 cm levels and the book from section 9.2. */
    protected function prepareInventory(string $genre = 'NARRATIVE', float $thicknessCm = 3.2): void
    {
        $this->postJson('/api/shelves', [
            'code' => 'E-01',
            'levels' => [['number' => 1, 'lengthCm' => 90, 'heightCm' => 32], ['number' => 2, 'lengthCm' => 90, 'heightCm' => 32]],
        ])->assertCreated();
        $this->postJson('/api/books', [
            'isbn' => '9789580000001', 'title' => 'Cien años de soledad', 'author' => 'Gabriel García Márquez',
            'publisher' => 'Sudamericana', 'year' => 1967, 'genre' => $genre,
            'heightCm' => 23, 'widthCm' => 15, 'thicknessCm' => $thicknessCm,
        ])->assertCreated();
    }

    protected function registerCopy(string $code, int $level = 1, string $isbn = '9789580000001')
    {
        return $this->postJson("/api/books/{$isbn}/copies", ['code' => $code, 'shelf' => 'E-01', 'level' => $level]);
    }

    protected function registerReader(string $identification = '1020304050'): void
    {
        $this->postJson('/api/readers', ['identification' => $identification, 'name' => 'Ana María Ruiz', 'address' => 'Calle 10'])->assertCreated();
    }
}
