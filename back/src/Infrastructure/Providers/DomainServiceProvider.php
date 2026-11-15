<?php

namespace App\Infrastructure\Providers;

use App\Application\Ports\CapacityCache;
use App\Application\Ports\Clock;
use App\Application\Ports\PasswordHasher;
use App\Application\Ports\PermissionCache;
use App\Application\Ports\Transaction;
use App\Domain\Repositories\BookRepository;
use App\Domain\Repositories\CopyRepository;
use App\Domain\Repositories\FineRepository;
use App\Domain\Repositories\LibraryRepository;
use App\Domain\Repositories\LoanRepository;
use App\Domain\Repositories\ReaderRepository;
use App\Domain\Repositories\SettingsRepository;
use App\Domain\Repositories\UserRepository;
use App\Infrastructure\Persistence\Repositories\EloquentBookRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCopyRepository;
use App\Infrastructure\Persistence\Repositories\EloquentFineRepository;
use App\Infrastructure\Persistence\Repositories\EloquentLibraryRepository;
use App\Infrastructure\Persistence\Repositories\EloquentLoanRepository;
use App\Infrastructure\Persistence\Repositories\EloquentReaderRepository;
use App\Infrastructure\Persistence\Repositories\EloquentSettingsRepository;
use App\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\Infrastructure\Services\LaravelPasswordHasher;
use App\Infrastructure\Services\LaravelTransaction;
use App\Infrastructure\Services\RedisCapacityCache;
use App\Infrastructure\Services\RedisPermissionCache;
use App\Infrastructure\Services\SystemClock;
use Illuminate\Support\ServiceProvider;

/** Binds Domain interfaces and Application ports to their implementations (DA-07). */
class DomainServiceProvider extends ServiceProvider
{
    public array $bindings = [
        LibraryRepository::class => EloquentLibraryRepository::class,
        SettingsRepository::class => EloquentSettingsRepository::class,
        CopyRepository::class => EloquentCopyRepository::class,
        ReaderRepository::class => EloquentReaderRepository::class,
        BookRepository::class => EloquentBookRepository::class,
        FineRepository::class => EloquentFineRepository::class,
        LoanRepository::class => EloquentLoanRepository::class,
        UserRepository::class => EloquentUserRepository::class,
        Transaction::class => LaravelTransaction::class,
        Clock::class => SystemClock::class,
        CapacityCache::class => RedisCapacityCache::class,
        PermissionCache::class => RedisPermissionCache::class,
        PasswordHasher::class => LaravelPasswordHasher::class,
    ];
}
