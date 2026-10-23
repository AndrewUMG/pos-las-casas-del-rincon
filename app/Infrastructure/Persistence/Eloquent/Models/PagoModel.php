<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class PagoModel extends Model
{
    protected $table = 'pagos';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'monto' => 'float',
            'monto_recibido' => 'float',
            'cambio' => 'float',
            'fecha' => 'datetime',
        ];
    }
}
