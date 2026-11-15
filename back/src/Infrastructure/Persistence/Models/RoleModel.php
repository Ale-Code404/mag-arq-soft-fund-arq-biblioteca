<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RoleModel extends Model
{
    protected $table = 'roles';

    public $timestamps = false;

    protected $guarded = [];

    /** @return string[] */
    public function permissions(): array
    {
        return DB::table('role_permission')->where('role_id', $this->id)->orderBy('permission')->pluck('permission')->all();
    }

    /** @param string[] $permissions */
    public function syncPermissions(array $permissions): void
    {
        DB::table('role_permission')->where('role_id', $this->id)->delete();
        DB::table('role_permission')->insert(array_map(fn (string $p) => ['role_id' => $this->id, 'permission' => $p], $permissions));
    }
}
