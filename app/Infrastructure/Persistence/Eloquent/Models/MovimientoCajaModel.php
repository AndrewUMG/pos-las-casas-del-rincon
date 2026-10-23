<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoCajaModel extends Model
{
    protected $table = 'movimientos_caja';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'monto' => 'float',
            'fecha' => 'datetime',
        ];
    }
}
