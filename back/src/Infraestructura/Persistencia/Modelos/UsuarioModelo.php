<?php

namespace App\Infraestructura\Persistencia\Modelos;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class UsuarioModelo extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuarios';

    protected $guarded = [];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonaModelo::class, 'persona_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RolModelo::class, 'usuario_rol', 'usuario_id', 'rol_id')->orderBy('nombre');
    }

    /** @return string[] permisos efectivos; un usuario inactivo no tiene ninguno */
    public function permisos(): array
    {
        if (! $this->activo) {
            return [];
        }

        return DB::table('usuario_rol')
            ->join('rol_permiso', 'rol_permiso.rol_id', '=', 'usuario_rol.rol_id')
            ->where('usuario_rol.usuario_id', $this->id)
            ->distinct()
            ->orderBy('permiso')
            ->pluck('permiso')
            ->all();
    }
}
