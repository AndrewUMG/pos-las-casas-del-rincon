<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaModel extends Model
{
    protected $table = 'facturas';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'total' => 'float',
        ];
    }

    public function venta()
    {
        return $this->belongsTo(VentaModel::class, 'venta_id');
    }
}
