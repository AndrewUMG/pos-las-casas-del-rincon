<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventarioModel extends Model
{
    protected $table = 'movimientos_inventario';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
        ];
    }
}
