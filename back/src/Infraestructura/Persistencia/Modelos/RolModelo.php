<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RolModelo extends Model
{
    protected $table = 'roles';

    public $timestamps = false;

    protected $guarded = [];

    /** @return string[] */
    public function permisos(): array
    {
        return DB::table('rol_permiso')->where('rol_id', $this->id)->orderBy('permiso')->pluck('permiso')->all();
    }

    /** @param string[] $permisos */
    public function sincronizarPermisos(array $permisos): void
    {
        DB::table('rol_permiso')->where('rol_id', $this->id)->delete();
        DB::table('rol_permiso')->insert(array_map(fn (string $p) => ['rol_id' => $this->id, 'permiso' => $p], $permisos));
    }
}
