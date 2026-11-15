<?php

namespace App\Infrastructure\Services;

use App\Application\Ports\CapacityCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Keys carry a version (capacity:v{n}:{scope}:{value}); invalidating bumps the version,
 * so all previous metrics become stale without scanning keys in Redis.
 */
class RedisCapacityCache implements CapacityCache
{
    public const TTL_SECONDS = 300;

    private const VERSION_KEY = 'capacity:version';

    public function get(string $key, callable $calculate): array
    {
        $version = (int) Cache::get(self::VERSION_KEY, 0);
        $versionedKey = preg_replace('/^capacity:/', "capacity:v{$version}:", $key);

        return Cache::remember($versionedKey, self::TTL_SECONDS, fn () => $calculate());
    }

    public function invalidate(): void
    {
        // After commit, so a concurrent read does not store stale data again
        DB::afterCommit(function () {
            Cache::add(self::VERSION_KEY, 0);
            Cache::increment(self::VERSION_KEY);
        });
    }
}
