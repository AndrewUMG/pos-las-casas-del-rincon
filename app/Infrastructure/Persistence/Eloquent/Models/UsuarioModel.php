<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Modelo de autenticación de Laravel (adaptador): la validación de credenciales
 * la hace el caso de uso IniciarSesion; este modelo solo mantiene la sesión.
 */
class UsuarioModel extends Authenticatable
{
    protected $table = 'usuarios';

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function rol()
    {
        return $this->belongsTo(RolModel::class, 'rol_id');
    }
}
