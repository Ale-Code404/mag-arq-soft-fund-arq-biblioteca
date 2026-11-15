<?php

namespace App\Infrastructure\Services;

use App\Application\Ports\PermissionCache;
use App\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Support\Facades\Cache;

class RedisPermissionCache implements PermissionCache
{
    public const TTL_SECONDS = 600;

    /** @return string[] */
    public function get(UserModel $user): array
    {
        return Cache::remember(self::key($user->id), self::TTL_SECONDS, fn () => $user->permissions());
    }

    public function invalidate(int $userId): void
    {
        Cache::forget(self::key($userId));
    }

    private static function key(int $userId): string
    {
        return "permissions:{$userId}";
    }
}
