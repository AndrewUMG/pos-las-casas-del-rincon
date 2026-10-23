<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class RolModel extends Model
{
    protected $table = 'roles';

    protected $guarded = [];

    public function permisos()
    {
        return $this->belongsToMany(PermisoModel::class, 'rol_permiso', 'rol_id', 'permiso_id');
    }
}
