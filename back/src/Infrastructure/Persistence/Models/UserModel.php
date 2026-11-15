<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';

    protected $guarded = [];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(PersonModel::class, 'person_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'user_role', 'user_id', 'role_id')->orderBy('name');
    }

    /** @return string[] effective permissions; an inactive user has none */
    public function permissions(): array
    {
        if (! $this->active) {
            return [];
        }

        return DB::table('user_role')
            ->join('role_permission', 'role_permission.role_id', '=', 'user_role.role_id')
            ->where('user_role.user_id', $this->id)
            ->distinct()
            ->orderBy('permission')
            ->pluck('permission')
            ->all();
    }
}
