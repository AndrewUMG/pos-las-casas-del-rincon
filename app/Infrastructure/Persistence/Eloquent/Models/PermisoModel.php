<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoModel extends Model
{
    protected $table = 'permisos';

    protected $guarded = [];

    public $timestamps = false;
}
